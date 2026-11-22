<?php // app/Imports/Catalogs/CatalogImportPersister.php

namespace App\Imports\Catalogs;

use App\Models\Catalog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

class CatalogImportPersister
{
    public function __construct(
        private readonly CatalogImportRowValidator $validator,
    ) {}

    /**
     * Persist valid, non-duplicate rows from a staged file into the catalogs table.
     *
     * @return array{
     *   imported_count: int,
     *   skipped_count: int,
     *   error_count: int,
     *   messages: array<int, string>
     * }
     */
    public function persistStagedFile(string $stagedPath): array
    {
        $report = $this->validator->validateStagedFile($stagedPath);

        if (empty($report['headers_valid'])) {
            return [
                'imported_count' => 0,
                'skipped_count'  => 0,
                'error_count'    => max(1, (int) ($report['error_count'] ?? 0)),
                'messages'       => $report['header_errors'] ?: ['Spreadsheet headers do not match the template.'],
            ];
        }

        $definitions = CatalogImportColumns::definitions();
        $catalogColumns = $this->getCatalogTableColumns();
        $existingKeys = $this->loadExistingCompositeKeys($definitions);

        $importedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;
        $messages = [];

        DB::transaction(function () use (
            $report,
            $definitions,
            $catalogColumns,
            &$existingKeys,
            &$importedCount,
            &$skippedCount,
            &$errorCount,
            &$messages,
        ): void {
            foreach ($report['rows'] as $row) {
                $rowNumber = (int) ($row['row_number'] ?? 0);

                if (empty($row['is_valid'])) {
                    $errorCount++;
                    continue;
                }

                $data = is_array($row['data'] ?? null) ? $row['data'] : [];
                $resolvedIds = is_array($row['resolved_ids'] ?? null) ? $row['resolved_ids'] : [];

                $compositeKey = $this->buildCompositeKeyFromRow($data, $definitions, $resolvedIds);
                if (isset($existingKeys[$compositeKey])) {
                    $skippedCount++;
                    $messages[] = "Row #{$rowNumber} skipped: catalog entry already exists in database.";
                    continue;
                }

                $payload = $this->buildCatalogPayload($data, $resolvedIds, $catalogColumns);
                Catalog::query()->create($payload);

                $existingKeys[$compositeKey] = true;
                $importedCount++;
            }
        });

        if (Storage::disk('local')->exists($stagedPath)) {
            try {
                Storage::disk('local')->delete($stagedPath);
            } catch (Throwable) {
                // Keep import result even if Windows file lock delays temp cleanup
            }
        }

        return [
            'imported_count' => $importedCount,
            'skipped_count'  => $skippedCount,
            'error_count'    => $errorCount,
            'messages'       => $messages,
        ];
    }

    /**
     * @param  array<string, array<string, mixed>>  $definitions
     * @return array<string, bool>
     */
    protected function loadExistingCompositeKeys(array $definitions): array
    {
        $keys = [];

        try {
            $catalogs = Catalog::query()->get();
            foreach ($catalogs as $catalog) {
                $parts = [];
                foreach (CatalogImportColumns::UNIQUE_KEY_COLUMNS as $uniqueCol) {
                    $def = $definitions[$uniqueCol] ?? null;
                    if (($def['type'] ?? null) === 'lookup' && ! empty($def['column'])) {
                        $fkCol = (string) $def['column'];
                        $parts[] = 'id:' . strtolower(trim((string) ($catalog->{$fkCol} ?? '')));
                    } else {
                        $parts[] = 'val:' . strtolower(trim((string) ($catalog->{$uniqueCol} ?? '')));
                    }
                }
                $keys[implode('|', $parts)] = true;
            }
        } catch (Throwable) {
            // If table/columns are still evolving, avoid breaking validation
        }

        return $keys;
    }

    /**
     * @param  array<string, string>  $data
     * @param  array<string, array<string, mixed>>  $definitions
     * @param  array<string, int|string>  $resolvedIds
     */
    protected function buildCompositeKeyFromRow(array $data, array $definitions, array $resolvedIds): string
    {
        $parts = [];

        foreach (CatalogImportColumns::UNIQUE_KEY_COLUMNS as $uniqueCol) {
            $def = $definitions[$uniqueCol] ?? null;
            if (($def['type'] ?? null) === 'lookup' && ! empty($def['column'])) {
                $fkCol = (string) $def['column'];
                $parts[] = 'id:' . strtolower(trim((string) ($resolvedIds[$fkCol] ?? '')));
            } else {
                $parts[] = 'val:' . strtolower(trim((string) ($data[$uniqueCol] ?? '')));
            }
        }

        return implode('|', $parts);
    }

    /**
     * @param  array<string, string>  $data
     * @param  array<string, int|string>  $resolvedIds
     * @param  array<int, string>  $catalogColumns
     * @return array<string, mixed>
     */
    protected function buildCatalogPayload(array $data, array $resolvedIds, array $catalogColumns): array
    {
        $payload = [];

        foreach ($resolvedIds as $column => $id) {
            $payload[$column] = $id;
        }

        $nullableFields = ['author', 'isbn_issn', 'edition', 'description'];

        foreach ($data as $field => $value) {
            if (isset($payload[$field])) {
                continue;
            }

            if ($field === 'publication_year') {
                $payload[$field] = $value !== '' ? (int) $value : null;
                continue;
            }

            if ($value === '' && in_array($field, $nullableFields, true)) {
                $payload[$field] = null;
                continue;
            }

            $payload[$field] = $value;
        }

        if (empty($catalogColumns)) {
            return $payload;
        }

        return array_intersect_key($payload, array_flip($catalogColumns));
    }

    /**
     * @return array<int, string>
     */
    protected function getCatalogTableColumns(): array
    {
        try {
            return Schema::getColumnListing(CatalogImportColumns::TABLE);
        } catch (Throwable) {
            return [];
        }
    }
}

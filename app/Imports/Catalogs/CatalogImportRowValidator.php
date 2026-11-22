<?php // app/Imports/Catalogs/CatalogImportRowValidator.php

namespace App\Imports\Catalogs;

use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;
use Throwable;

class CatalogImportRowValidator
{
    /**
     * Parse and validate a staged .xlsx or .csv file against CatalogImportColumns.
     *
     * @return array{
     *   headers_valid: bool,
     *   header_errors: array<int, string>,
     *   total_rows: int,
     *   valid_count: int,
     *   error_count: int,
     *   rows: array<int, array<string, mixed>>
     * }
     */
    public function validateStagedFile(string $stagedPath): array
    {
        $definitions = CatalogImportColumns::definitions();
        $expectedHeadings = CatalogImportColumns::headings();

        $fullPath = Storage::disk('local')->path($stagedPath);
        if (! is_file($fullPath)) {
            throw new RuntimeException('Staged import file does not exist on disk: ' . $stagedPath);
        }

        $reader = new class implements ToArray {
            /** @param array<int, array<int, mixed>> $array */
            public function array(array $array): void
            {
                // Handled directly via Excel::toArray() return value
            }
        };

        $sheets = Excel::toArray($reader, $fullPath);
        $rawRows = $sheets[0] ?? [];

        if (empty($rawRows)) {
            return [
                'headers_valid' => false,
                'header_errors' => ['The spreadsheet is empty or has no data rows.'],
                'total_rows'    => 0,
                'valid_count'   => 0,
                'error_count'   => 0,
                'rows'          => [],
            ];
        }

        $rawHeaderRow = array_shift($rawRows);
        $normalizedHeaders = array_map(
            static fn ($cell) => strtolower(trim((string) ($cell ?? ''))),
            is_array($rawHeaderRow) ? $rawHeaderRow : [],
        );

        $headerErrors = [];
        if ($normalizedHeaders !== $expectedHeadings) {
            $missing = array_diff($expectedHeadings, $normalizedHeaders);
            $extra = array_diff($normalizedHeaders, $expectedHeadings);

            if (! empty($missing)) {
                $headerErrors[] = 'Missing required column(s): ' . implode(', ', $missing) . '.';
            }
            if (! empty($extra)) {
                $headerErrors[] = 'Unexpected column(s): ' . implode(', ', $extra) . '.';
            }
            if (empty($headerErrors)) {
                $headerErrors[] = 'Column order does not match the template: ' . implode(', ', $expectedHeadings) . '.';
            }
        }

        $lookupCaches = $this->buildLookupCaches($definitions);
        $seenCompositeKeys = [];
        $parsedRows = [];
        $validCount = 0;
        $errorCount = 0;

        foreach ($rawRows as $index => $rawRow) {
            $excelRowNumber = $index + 2; // Row 1 is headings
            $cells = is_array($rawRow) ? $rawRow : [];

            $rowData = [];
            foreach ($expectedHeadings as $colIdx => $heading) {
                $val = $cells[$colIdx] ?? null;
                $rowData[$heading] = $val === null ? '' : trim((string) $val);
            }

            // Skip completely blank trailing rows
            if ($this->isRowEmpty($rowData)) {
                continue;
            }

            $rowErrors = [];
            $resolvedIds = [];

            $isNonAuthorAssetType = CatalogImportColumns::isAuthorOptionalForAssetType($rowData['asset_type'] ?? null);

            foreach ($definitions as $key => $def) {
                $value = $rowData[$key] ?? '';
                $label = (string) ($def['label'] ?? $key);
                $required = (bool) ($def['required'] ?? false);

                if ($key === 'author') {
                    $required = ! $isNonAuthorAssetType;
                }

                if ($value === '') {
                    if ($required) {
                        $rowErrors[$key][] = "{$label} is required.";
                    }
                    continue;
                }

                $type = (string) ($def['type'] ?? 'string');

                if ($type === 'string') {
                    if (! empty($def['min_length']) && mb_strlen($value) < (int) $def['min_length']) {
                        $rowErrors[$key][] = "{$label} must be at least {$def['min_length']} characters.";
                    }
                    if (! empty($def['max_length']) && mb_strlen($value) > (int) $def['max_length']) {
                        $rowErrors[$key][] = "{$label} must not exceed {$def['max_length']} characters.";
                    }
                } elseif ($type === 'digits_string') {
                    if (! preg_match('/^\d+$/', $value)) {
                        $rowErrors[$key][] = "{$label} must contain digits only.";
                    } else {
                        $len = strlen($value);
                        if (! empty($def['min_length']) && $len < (int) $def['min_length']) {
                            $rowErrors[$key][] = "{$label} must be at least {$def['min_length']} digits.";
                        }
                        if (! empty($def['max_length']) && $len > (int) $def['max_length']) {
                            $rowErrors[$key][] = "{$label} must not exceed {$def['max_length']} digits.";
                        }
                    }
                } elseif ($type === 'year') {
                    $digits = (int) ($def['digits'] ?? 4);
                    $minYear = (int) ($def['min_value'] ?? 1900);
                    $maxYear = (int) ($def['max_value'] ?? (int) date('Y'));

                    if (! preg_match('/^\d{' . $digits . '}$/', $value)) {
                        $rowErrors[$key][] = "{$label} must be a {$digits}-digit year.";
                    } else {
                        $yearInt = (int) $value;
                        if ($yearInt < $minYear || $yearInt > $maxYear) {
                            $rowErrors[$key][] = "{$label} must be between {$minYear} and {$maxYear}.";
                        }
                    }
                } elseif ($type === 'lookup' && ! empty($def['lookup_must_exist'])) {
                    $normalizedLookup = strtolower($value);
                    $matchedId = $lookupCaches[$key][$normalizedLookup] ?? null;

                    if ($matchedId === null) {
                        $rowErrors[$key][] = "{$label} \"{$value}\" was not found in the system.";
                    } elseif (! empty($def['column'])) {
                        $resolvedIds[(string) $def['column']] = $matchedId;
                    }
                }
            }

            // Composite uniqueness check within the uploaded file
            $compositeParts = [];
            foreach (CatalogImportColumns::UNIQUE_KEY_COLUMNS as $uniqueCol) {
                $compositeParts[] = strtolower(trim((string) ($rowData[$uniqueCol] ?? '')));
            }
            $compositeKey = implode('|', $compositeParts);

            if (isset($seenCompositeKeys[$compositeKey])) {
                $firstRow = $seenCompositeKeys[$compositeKey];
                $rowErrors['_composite'][] = "Duplicate catalog entry in file (matches row {$firstRow} on unique key columns).";
            } else {
                $seenCompositeKeys[$compositeKey] = $excelRowNumber;
            }

            $isValid = empty($headerErrors) && empty($rowErrors);
            if ($isValid) {
                $validCount++;
            } else {
                $errorCount++;
            }

            $parsedRows[] = [
                'row_number'   => $excelRowNumber,
                'data'         => $rowData,
                'resolved_ids' => $resolvedIds,
                'errors'       => $rowErrors,
                'is_valid'     => $isValid,
            ];
        }

        return [
            'headers_valid' => empty($headerErrors),
            'header_errors' => $headerErrors,
            'total_rows'    => count($parsedRows),
            'valid_count'   => $validCount,
            'error_count'   => $errorCount,
            'rows'          => $parsedRows,
        ];
    }

    /**
     * @param  array<string, array<string, mixed>>  $definitions
     * @return array<string, array<string, int|string>>
     */
    protected function buildLookupCaches(array $definitions): array
    {
        $caches = [];

        foreach ($definitions as $key => $def) {
            if (($def['type'] ?? null) !== 'lookup' || empty($def['lookup_model']) || empty($def['lookup_column'])) {
                continue;
            }

            $modelClass = $def['lookup_model'];
            $column = (string) $def['lookup_column'];
            $map = [];

            try {
                $records = $modelClass::query()->get(['id', $column]);
                foreach ($records as $record) {
                    $name = strtolower(trim((string) ($record->{$column} ?? '')));
                    if ($name !== '') {
                        $map[$name] = $record->id;
                    }
                }
            } catch (Throwable) {
                $map = [];
            }

            $caches[$key] = $map;
        }

        return $caches;
    }

    /**
     * @param  array<string, string>  $rowData
     */
    protected function isRowEmpty(array $rowData): bool
    {
        foreach ($rowData as $value) {
            if (trim($value) !== '') {
                return false;
            }
        }

        return true;
    }
}

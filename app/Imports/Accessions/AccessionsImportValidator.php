<?php

namespace App\Imports\Accessions;

use App\Models\Accession;
use App\Models\Acquisition;

class AccessionsImportValidator
{
    /**
     * @var array<string, int|null>
     */
    private array $acquisitionIdCache = [];

    /**
     * @var array<int, int>
     */
    private array $acquisitionCapacityCache = [];

    /**
     * @var array<int, int>
     */
    private array $plannedNewByAcquisition = [];

    /**
     * @var array<string, int>
     */
    private array $seenAccessionNumbers = [];

    public function resetRunState(): void
    {
        $this->acquisitionIdCache = [];
        $this->acquisitionCapacityCache = [];
        $this->plannedNewByAcquisition = [];
        $this->seenAccessionNumbers = [];
    }

    public function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== null && trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    public function isTotalsOrFooterRow(array $row): bool
    {
        $firstNonEmpty = null;

        foreach ($row as $value) {
            $trimmed = trim((string) ($value ?? ''));
            if ($trimmed !== '') {
                $firstNonEmpty = strtolower($trimmed);
                break;
            }
        }

        if ($firstNonEmpty === null) {
            return false;
        }

        return str_starts_with($firstNonEmpty, 'total')
            || str_starts_with($firstNonEmpty, 'grand total')
            || str_starts_with($firstNonEmpty, 'subtotal')
            || str_starts_with($firstNonEmpty, 'footer')
            || str_starts_with($firstNonEmpty, 'summary')
            || str_starts_with($firstNonEmpty, 'note:')
            || str_starts_with($firstNonEmpty, 'notes:');
    }

    /**
     * Validate a mapped row keyed by internal field name.
     *
     * @param  array<string, mixed>  $mappedRow
     * @param  int  $rowNumber
     * @return array<int, array{column: string, reason: string}>
     */
    public function validateRow(array $mappedRow, int $rowNumber = 0): array
    {
        $errors = [];
        $definitions = AccessionImportColumns::definitions();

        foreach ($definitions as $field => $def) {
            $heading = $def['heading'];
            $rawValue = $mappedRow[$field] ?? null;
            $value = is_scalar($rawValue) ? trim((string) $rawValue) : '';

            if ($def['required'] && $value === '') {
                $errors[] = [
                    'column' => $heading,
                    'reason' => "{$heading} is required and cannot be blank.",
                ];

                continue;
            }

            if ($value === '') {
                continue;
            }

            if ($def['max_length'] !== null && mb_strlen($value) > $def['max_length']) {
                $errors[] = [
                    'column' => $heading,
                    'reason' => "{$heading} exceeds the {$def['max_length']}-character limit.",
                ];
            }

            if ($field === 'accession_number') {
                $normalizedCode = strtoupper($value);
                if (isset($this->seenAccessionNumbers[$normalizedCode])) {
                    $firstRow = $this->seenAccessionNumbers[$normalizedCode];
                    $errors[] = [
                        'column' => $heading,
                        'reason' => "Duplicate {$heading} \"{$value}\" in this file (already appears on row {$firstRow}).",
                    ];
                } elseif ($rowNumber > 0) {
                    $this->seenAccessionNumbers[$normalizedCode] = $rowNumber;
                }
            }

            if ($def['allowed_values'] !== null) {
                $normalized = strtolower($value);
                if (! in_array($normalized, $def['allowed_values'], true)) {
                    $errors[] = [
                        'column' => $heading,
                        'reason' => "Invalid {$heading} \"{$value}\". Must be one of: " . implode(', ', $def['allowed_values']) . '.',
                    ];
                }
            }

            if ($field === 'acquisition_number' && $def['lookup'] === 'acquisitions.acquisition_number') {
                $acqId = $this->resolveAcquisitionId($value);
                if (! $acqId) {
                    $errors[] = [
                        'column' => $heading,
                        'reason' => "No acquisition found with {$heading} \"{$value}\".",
                    ];
                }
            }
        }

        if ($errors === []) {
            $accessionNumber = is_scalar($mappedRow['accession_number'] ?? null)
                ? trim((string) $mappedRow['accession_number'])
                : '';
            $acqRef = is_scalar($mappedRow['acquisition_number'] ?? null)
                ? trim((string) $mappedRow['acquisition_number'])
                : '';

            $acqId = $this->resolveAcquisitionId($acqRef);
            $isExistingAccession = $accessionNumber !== ''
                && Accession::query()->where('accession_number', $accessionNumber)->exists();

            if ($acqId && ! $isExistingAccession) {
                $maxQty = $this->getAcquisitionReceivedQty($acqId);
                $planned = $this->plannedNewByAcquisition[$acqId] ?? 0;

                if (($planned + 1) > $maxQty) {
                    $errors[] = [
                        'column' => AccessionImportColumns::definitions()['acquisition_number']['heading'],
                        'reason' => "Acquisition \"{$acqRef}\" already has all {$maxQty} received copy/copies accessioned.",
                    ];
                } else {
                    $this->plannedNewByAcquisition[$acqId] = $planned + 1;
                }
            }
        }

        return $errors;
    }

    private function resolveAcquisitionId(string $acquisitionNumber): ?int
    {
        $key = strtoupper($acquisitionNumber);

        if (array_key_exists($key, $this->acquisitionIdCache)) {
            return $this->acquisitionIdCache[$key];
        }

        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';
        $id = Acquisition::query()
            ->where('acquisition_number', $likeOperator, $acquisitionNumber)
            ->value('id');

        $resolved = $id ? (int) $id : null;
        $this->acquisitionIdCache[$key] = $resolved;

        return $resolved;
    }

    private function getAcquisitionReceivedQty(int $acquisitionId): int
    {
        if (array_key_exists($acquisitionId, $this->acquisitionCapacityCache)) {
            return $this->acquisitionCapacityCache[$acquisitionId];
        }

        $acquisition = Acquisition::query()->find($acquisitionId);
        $qty = $acquisition ? (int) $acquisition->quantity_received : 0;
        $this->acquisitionCapacityCache[$acquisitionId] = $qty;

        return $qty;
    }
}

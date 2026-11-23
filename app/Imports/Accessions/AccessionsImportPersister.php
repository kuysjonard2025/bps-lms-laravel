<?php

namespace App\Imports\Accessions;

use App\Models\Accession;
use App\Models\Acquisition;
use Illuminate\Support\Facades\DB;

class AccessionsImportPersister
{
    public int $createdCount = 0;

    public int $updatedCount = 0;

    /**
     * @var array<string, int>
     */
    private array $acquisitionIdCache = [];

    /**
     * @var array<int, int>
     */
    private array $nextCopySequenceByAcquisition = [];

    public function reset(): void
    {
        $this->createdCount = 0;
        $this->updatedCount = 0;
        $this->acquisitionIdCache = [];
        $this->nextCopySequenceByAcquisition = [];
    }

    /**
     * Persist a single validated mapped row.
     *
     * @param  array<string, mixed>  $mappedRow
     */
    public function persistRow(array $mappedRow): bool
    {
        $accessionNumber = $this->sanitizeText($mappedRow['accession_number'] ?? null);
        $acquisitionNumber = $this->sanitizeText($mappedRow['acquisition_number'] ?? null);
        $condition = strtolower((string) $this->sanitizeText($mappedRow['condition'] ?? ''));
        $status = strtolower((string) $this->sanitizeText($mappedRow['status'] ?? ''));
        $remarks = $this->sanitizeText($mappedRow['remarks'] ?? null);

        if ($acquisitionNumber === null) {
            return false;
        }

        $acquisitionId = $this->resolveAcquisitionId($acquisitionNumber);
        if (! $acquisitionId) {
            return false;
        }

        return DB::transaction(function () use (
            $accessionNumber,
            $acquisitionNumber,
            $acquisitionId,
            $condition,
            $status,
            $remarks
        ): bool {
            $acquisition = Acquisition::query()->where('id', $acquisitionId)->lockForUpdate()->first();
            if (! $acquisition) {
                return false;
            }

            if ($accessionNumber !== null) {
                $existing = Accession::query()
                    ->where('accession_number', $accessionNumber)
                    ->first();

                if ($existing) {
                    $existing->update([
                        'acquisition_id' => $acquisitionId,
                        'condition'      => $condition,
                        'status'         => $status,
                        'remarks'        => $remarks,
                    ]);

                    $this->updatedCount++;

                    return true;
                }
            }

            $currentCount = Accession::query()->where('acquisition_id', $acquisitionId)->count();
            $maxAllowed = (int) $acquisition->quantity_received;

            if ($currentCount >= $maxAllowed) {
                return false;
            }

            $codeToSave = $accessionNumber ?? $this->generateNextAccessionNumber($acquisitionId);

            Accession::create([
                'acquisition_id'   => $acquisitionId,
                'accession_number' => $codeToSave,
                'condition'        => $condition,
                'status'           => $status,
                'remarks'          => $remarks,
            ]);

            $this->createdCount++;

            return true;
        });
    }

    private function sanitizeText(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $cleaned = trim(strip_tags((string) $value));

        return $cleaned === '' ? null : $cleaned;
    }

    private function resolveAcquisitionId(string $acquisitionNumber): ?int
    {
        $cacheKey = strtoupper($acquisitionNumber);

        if (array_key_exists($cacheKey, $this->acquisitionIdCache)) {
            return $this->acquisitionIdCache[$cacheKey] > 0
                ? $this->acquisitionIdCache[$cacheKey]
                : null;
        }

        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';
        $id = Acquisition::query()
            ->where('acquisition_number', $likeOperator, $acquisitionNumber)
            ->value('id');

        $resolvedId = $id ? (int) $id : 0;
        $this->acquisitionIdCache[$cacheKey] = $resolvedId;

        return $resolvedId > 0 ? $resolvedId : null;
    }

    private function generateNextAccessionNumber(int $acquisitionId): string
    {
        if (! isset($this->nextCopySequenceByAcquisition[$acquisitionId])) {
            $latestCopy = Accession::query()
                ->where('acquisition_id', $acquisitionId)
                ->pluck('accession_number')
                ->map(static function (string $code): int {
                    preg_match('/-(\d+)$/', $code, $matches);

                    return isset($matches[1]) ? (int) $matches[1] : 0;
                })
                ->max() ?? 0;

            $this->nextCopySequenceByAcquisition[$acquisitionId] = ((int) $latestCopy) + 1;
        }

        $year = date('Y');
        $batchStr = str_pad((string) $acquisitionId, 3, '0', STR_PAD_LEFT);
        $copyNum = $this->nextCopySequenceByAcquisition[$acquisitionId];

        do {
            $copyStr = str_pad((string) $copyNum, 2, '0', STR_PAD_LEFT);
            $candidate = "ACC-{$year}-{$batchStr}-{$copyStr}";
            $copyNum++;
        } while (Accession::query()->where('accession_number', $candidate)->exists());

        $this->nextCopySequenceByAcquisition[$acquisitionId] = $copyNum;

        return $candidate;
    }
}

<?php // app/Livewire/InventoryManagement.php

namespace App\Livewire;

use App\Livewire\Helpers\SanitizesInputs;
use App\Models\Accession;
use App\Models\Acquisition;
use App\Models\AssetType;
use App\Models\Catalog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator as ConcreteLengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class InventoryManagement extends Component
{
    use WithPagination, SanitizesInputs;

    public string $search = '';
    public string $assetTypeFilter = 'all';
    public string $assetTypeSearch = '';

    private function likeOperator(): string
    {
        return DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
    }

    public function updatedSearch(): void
    {
        $this->search = trim(strip_tags($this->search));
        if (mb_strlen($this->search) > 100) {
            $this->search = mb_substr($this->search, 0, 100);
        }
        $this->resetPage();
    }

    public function updatedAssetTypeSearch(mixed $value): void
    {
        $clean = trim(strip_tags((string) $value));
        if (mb_strlen($clean) > 100) {
            $clean = mb_substr($clean, 0, 100);
        }
        $this->assetTypeSearch = $clean;

        if ($clean === '' || strtoupper($clean) === 'ALL ASSET TYPES') {
            $this->assetTypeFilter = 'all';
            $this->resetPage();

            return;
        }

        try {
            $matched = AssetType::query()
                ->where('name', $this->likeOperator(), $clean)
                ->first();

            if ($matched) {
                $this->assetTypeFilter = (string) $matched->id;
                $this->resetPage();
            }
        } catch (Throwable $e) {
            Log::error('Error matching asset type search: ' . $e->getMessage());
        }
    }

    public function selectAssetType(string $typeId): void
    {
        if ($typeId === 'all' || $typeId === '') {
            $this->assetTypeFilter = 'all';
            $this->assetTypeSearch = '';
            $this->resetPage();

            return;
        }

        try {
            $type = AssetType::query()->find($typeId);
            if ($type) {
                $this->assetTypeFilter = (string) $type->id;
                $this->assetTypeSearch = strtoupper((string) $type->name);
                $this->resetPage();
            }
        } catch (Throwable $e) {
            Log::error('Error selecting asset type filter: ' . $e->getMessage());
            $this->dispatch('toast', message: 'UNABLE TO APPLY ASSET TYPE FILTER.', type: 'error');
        }
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->assetTypeFilter = 'all';
        $this->assetTypeSearch = '';
        $this->resetErrorBag();
        $this->resetPage();
    }

    private function validateFilters(): void
    {
        $rules = [
            'search'          => ['nullable', 'string', 'max:100'],
            'assetTypeFilter' => ['required', 'string', 'max:50'],
            'assetTypeSearch' => ['nullable', 'string', 'max:100'],
        ];

        $messages = [
            'search.max'          => 'Search keyword cannot exceed 100 characters.',
            'assetTypeSearch.max' => 'Asset type search cannot exceed 100 characters.',
        ];

        $attributes = [
            'search'          => 'Inventory Search',
            'assetTypeFilter' => 'Asset Type Filter',
            'assetTypeSearch' => 'Asset Type Search',
        ];

        $this->validate($rules, $messages, $attributes);
    }

    private function baseAccessionsForCatalog(string $tableAlias = 'acc')
    {
        return Accession::from('accessions as ' . $tableAlias)
            ->join('acquisitions as acq', $tableAlias . '.acquisition_id', '=', 'acq.id')
            ->whereColumn('acq.catalog_id', 'catalogs.id');
    }

    private function getInventoryQuery(): Builder
    {
        $likeOperator = $this->likeOperator();
        $damagedConditions = ['minor damaged', 'minor damage', 'severe damaged', 'damaged'];
        $term = '%' . addcslashes(trim($this->search), '%_\\') . '%';

        return Catalog::query()
            ->with(['author', 'assetType'])
            ->select('catalogs.*')
            ->selectSub(
                $this->baseAccessionsForCatalog('acc_phys')->selectRaw('count(*)'),
                'accessioned_copies'
            )
            ->selectSub(
                $this->baseAccessionsForCatalog('acc_avail')->where('acc_avail.status', 'available')->selectRaw('count(*)'),
                'available_copies'
            )
            ->selectSub(
                $this->baseAccessionsForCatalog('acc_loan')->where('acc_loan.status', 'on loan')->selectRaw('count(*)'),
                'on_loan_copies'
            )
            ->selectSub(
                $this->baseAccessionsForCatalog('acc_stored')->where('acc_stored.status', 'stored')->selectRaw('count(*)'),
                'stored_copies'
            )
            ->selectSub(
                $this->baseAccessionsForCatalog('acc_maint')->where('acc_maint.status', 'under maintenance')->selectRaw('count(*)'),
                'maintenance_copies'
            )
            ->selectSub(
                $this->baseAccessionsForCatalog('acc_dmg')
                    ->where(function (Builder $sub) use ($damagedConditions): void {
                        $sub->whereIn('acc_dmg.condition', $damagedConditions)
                            ->orWhereIn('acc_dmg.status', $damagedConditions);
                    })
                    ->selectRaw('count(*)'),
                'damaged_copies'
            )
            ->selectSub(
                $this->baseAccessionsForCatalog('acc_lost')
                    ->where(function (Builder $sub): void {
                        $sub->whereIn('acc_lost.condition', ['lost', 'missing'])
                            ->orWhereIn('acc_lost.status', ['lost', 'missing']);
                    })
                    ->selectRaw('count(*)'),
                'lost_copies'
            )
            ->selectSub(
                $this->baseAccessionsForCatalog('acc_dump')
                    ->where(function (Builder $sub): void {
                        $sub->where('acc_dump.condition', 'dumped')
                            ->orWhere('acc_dump.status', 'dumped');
                    })
                    ->selectRaw('count(*)'),
                'dumped_copies'
            )
            ->selectSub(
                $this->baseAccessionsForCatalog('acc_batch')->selectRaw('count(distinct acc_batch.acquisition_id)'),
                'acquisition_batches'
            )
            ->selectSub(
                Acquisition::from('acquisitions as acq_sum')
                    ->whereColumn('acq_sum.catalog_id', 'catalogs.id')
                    ->selectRaw('coalesce(sum(acq_sum.quantity_received), 0)'),
                'total_received_quantity'
            )
            ->when($this->assetTypeFilter !== 'all', fn (Builder $q) => $q->where('catalogs.asset_type_id', $this->assetTypeFilter))
            ->when(trim($this->search) !== '', function (Builder $q) use ($likeOperator, $term): void {
                $q->where(function (Builder $sub) use ($likeOperator, $term): void {
                    $sub->where('catalogs.title', $likeOperator, $term)
                        ->orWhere('catalogs.isbn_issn', $likeOperator, $term)
                        ->orWhereHas('author', fn (Builder $a) => $a->where('name', $likeOperator, $term))
                        ->orWhereHas('acquisitions.accessions', fn (Builder $acc) => $acc->where('call_number', $likeOperator, $term)
                            ->orWhere('accession_number', $likeOperator, $term));
                });
            })
            ->latest('catalogs.created_at');
    }

    private function getSystemStats(): array
    {
        $damagedConditions = ['minor damaged', 'minor damage', 'severe damaged', 'damaged'];
        $totalReceived = (int) Acquisition::query()->sum('quantity_received');
        $totalAccessioned = (int) Accession::query()->count();

        return [
            'total_accessioned'   => $totalAccessioned,
            'total_unaccessioned' => max(0, $totalReceived - $totalAccessioned),
            'total_available'     => Accession::query()->where('status', 'available')->count(),
            'total_on_loan'       => Accession::query()->where('status', 'on loan')->count(),
            'total_stored'        => Accession::query()->where('status', 'stored')->count(),
            'total_maintenance'   => Accession::query()->where('status', 'under maintenance')->count(),
            'total_damaged'       => Accession::query()->where(function (Builder $q) use ($damagedConditions): void {
                $q->whereIn('condition', $damagedConditions)
                    ->orWhereIn('status', $damagedConditions);
            })->count(),
            'total_lost'          => Accession::query()->where(function (Builder $q): void {
                $q->whereIn('condition', ['lost', 'missing'])
                    ->orWhereIn('status', ['lost', 'missing']);
            })->count(),
            'total_dumped'        => Accession::query()->where(function (Builder $q): void {
                $q->where('condition', 'dumped')
                    ->orWhere('status', 'dumped');
            })->count(),
        ];
    }

    public function exportExcel(): ?StreamedResponse
    {
        $this->validateFilters();

        try {
            $filename = 'inventory-summary-' . now()->format('Y-m-d') . '.csv';

            $catalogs = $this->getInventoryQuery()->get();
            $stats = $this->getSystemStats();
            $searchFilterText = trim($this->search) !== ''
                ? strtoupper("TITLE/AUTHOR/ISBN/ACCESSION: '" . trim($this->search) . "'")
                : 'NONE (ALL APPLIED)';
            $assetTypeText = $this->assetTypeFilter === 'all'
                ? 'ALL'
                : strtoupper((string) (AssetType::query()->find($this->assetTypeFilter)?->name ?? 'UNKNOWN'));
            $generatedBy = strtoupper((string) (Auth::user()?->name ?? 'SYSTEM USER'));

            return response()->stream(function () use ($catalogs, $stats, $searchFilterText, $assetTypeText, $generatedBy): void {
                $file = fopen('php://output', 'w');
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

                fputcsv($file, ['TFVC LIBRARY MANAGEMENT SYSTEM']);
                fputcsv($file, ['INVENTORY SUMMARY REPORT']);
                fputcsv($file, ['GENERATED ON: ' . strtoupper(now()->format('F d, Y h:i A')), '', '', '', '', '', '', '', '', 'GENERATED BY: ' . $generatedBy]);
                fputcsv($file, ['SEARCH FILTER: ' . $searchFilterText, '', '', '', '', '', '', '', '', 'ASSET TYPE FILTER: ' . $assetTypeText]);
                fputcsv($file, []);

                fputcsv($file, ['OVERVIEW STATISTICS']);
                fputcsv($file, ['TOTAL ACCESSIONED', 'TOTAL UNACCESSIONED', 'AVAILABLE', 'ON LOAN', 'STORED', 'MAINTENANCE', 'DAMAGED', 'LOST', 'DUMPED']);
                fputcsv($file, [
                    $stats['total_accessioned'],
                    $stats['total_unaccessioned'],
                    $stats['total_available'],
                    $stats['total_on_loan'],
                    $stats['total_stored'],
                    $stats['total_maintenance'],
                    $stats['total_damaged'],
                    $stats['total_lost'],
                    $stats['total_dumped'],
                ]);
                fputcsv($file, []);

                fputcsv($file, ['TITLE', 'AUTHOR', 'ASSET TYPE', 'TOTAL STOCK', 'ACCESSIONED', 'UNACCESSIONED', 'AVAILABLE', 'ON LOAN', 'STORED', 'MAINTENANCE', 'DUMPED', 'DAMAGED', 'LOST', 'BATCHES']);

                foreach ($catalogs as $catalog) {
                    $accessionedCopies = (int) ($catalog->accessioned_copies ?? 0);
                    $receivedQty = (int) ($catalog->total_received_quantity ?? 0);
                    $totalStock = max($receivedQty, $accessionedCopies);
                    $unaccessionedCopies = max(0, $totalStock - $accessionedCopies);

                    fputcsv($file, [
                        strtoupper(html_entity_decode(strip_tags((string) $catalog->title . ($catalog->isbn_issn ? ' (ISBN: ' . $catalog->isbn_issn . ')' : '')))),
                        strtoupper(html_entity_decode(strip_tags((string) ($catalog->author?->name ?? 'UNKNOWN AUTHOR')))),
                        strtoupper(html_entity_decode(strip_tags((string) ($catalog->assetType?->name ?? 'STANDARD')))),
                        $totalStock,
                        $accessionedCopies,
                        $unaccessionedCopies,
                        $catalog->available_copies,
                        $catalog->on_loan_copies,
                        $catalog->stored_copies,
                        $catalog->maintenance_copies,
                        $catalog->dumped_copies,
                        $catalog->damaged_copies,
                        $catalog->lost_copies,
                        $catalog->acquisition_batches,
                    ]);
                }

                fclose($file);
            }, 200, [
                'Content-type'        => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename={$filename}",
                'Pragma'              => 'no-cache',
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
                'Expires'             => '0',
            ]);
        } catch (Throwable $e) {
            Log::error('Error exporting inventory CSV: ' . $e->getMessage());
            $this->dispatch('toast', message: 'UNABLE TO EXPORT INVENTORY CSV REPORT.', type: 'error');

            return null;
        }
    }

    public function exportPdf(): ?StreamedResponse
    {
        $this->validateFilters();

        try {
            $catalogs = $this->getInventoryQuery()->get();
            $stats = $this->getSystemStats();
            $searchFilterText = trim($this->search) !== ''
                ? strtoupper("TITLE/AUTHOR/ISBN/ACCESSION: '" . trim($this->search) . "'")
                : 'NONE (ALL APPLIED)';

            $pdf = Pdf::loadView('pdf.inventory-summary', [
                'catalogs' => $catalogs,
                'stats'    => $stats,
                'search'   => $searchFilterText,
            ])->setPaper('a4', 'landscape');

            $filename = 'inventory-summary-' . now()->format('Y-m-d_H-i-s') . '.pdf';

            return response()->streamDownload(function () use ($pdf): void {
                echo $pdf->output();
            }, $filename, [
                'Content-Type' => 'application/pdf',
            ]);
        } catch (Throwable $e) {
            Log::error('Error exporting inventory PDF: ' . $e->getMessage());
            $this->dispatch('toast', message: 'UNABLE TO EXPORT INVENTORY PDF REPORT.', type: 'error');

            return null;
        }
    }

    #[Layout('components.layouts.app')]
    #[Title('Inventory Summary')]
    public function render()
    {
        $catalogs = new ConcreteLengthAwarePaginator(collect(), 0, 15);
        $assetTypes = new Collection();
        $stats = [
            'total_accessioned'   => 0,
            'total_unaccessioned' => 0,
            'total_available'     => 0,
            'total_on_loan'       => 0,
            'total_stored'        => 0,
            'total_maintenance'   => 0,
            'total_damaged'       => 0,
            'total_lost'          => 0,
            'total_dumped'        => 0,
        ];

        try {
            $catalogs = $this->getInventoryQuery()->paginate(15);
            $assetTypes = AssetType::query()->orderBy('name')->get();
            $stats = $this->getSystemStats();
        } catch (Throwable $e) {
            Log::error('Error loading inventory management data: ' . $e->getMessage());
            $this->dispatch('toast', message: 'UNABLE TO LOAD INVENTORY SUMMARY DATA.', type: 'error');
        }

        return view('livewire.inventory-management', [
            'catalogs'   => $catalogs,
            'assetTypes' => $assetTypes,
            'stats'      => $stats,
        ]);
    }
}

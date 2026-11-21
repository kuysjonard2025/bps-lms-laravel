<?php

namespace App\Livewire;

use App\Livewire\Helpers\SanitizesInputs;
use App\Models\Accession;
use App\Models\Acquisition;
use App\Models\AssetType;
use App\Models\Catalog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
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
        return config('database.default') === 'pgsql' ? 'ilike' : 'like';
    }

    public function updatedSearch(): void
    {
        $this->search = trim(strip_tags($this->search));
        $this->resetPage();
    }

    public function updatedAssetTypeSearch(string $value): void
    {
        $clean = trim(strip_tags($value));
        $this->assetTypeSearch = $clean;

        if ($clean === '' || strtoupper($clean) === 'ALL ASSET TYPES') {
            $this->assetTypeFilter = 'all';
            $this->resetPage();
            return;
        }

        $matched = AssetType::where('name', $this->likeOperator(), $clean)->first();
        if ($matched) {
            $this->assetTypeFilter = (string) $matched->id;
            $this->resetPage();
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

        $type = AssetType::find($typeId);
        if ($type) {
            $this->assetTypeFilter = (string) $type->id;
            $this->assetTypeSearch = strtoupper((string) $type->name);
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->assetTypeFilter = 'all';
        $this->assetTypeSearch = '';
        $this->resetPage();
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

        return Catalog::with(['author', 'assetType'])
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
                    ->where(function ($sub) use ($damagedConditions) {
                        $sub->whereIn('acc_dmg.condition', $damagedConditions)
                            ->orWhereIn('acc_dmg.status', $damagedConditions);
                    })
                    ->selectRaw('count(*)'),
                'damaged_copies'
            )
            ->selectSub(
                $this->baseAccessionsForCatalog('acc_lost')
                    ->where(function ($sub) {
                        $sub->whereIn('acc_lost.condition', ['lost', 'missing'])
                            ->orWhereIn('acc_lost.status', ['lost', 'missing']);
                    })
                    ->selectRaw('count(*)'),
                'lost_copies'
            )
            ->selectSub(
                $this->baseAccessionsForCatalog('acc_dump')
                    ->where(function ($sub) {
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
            ->when($this->assetTypeFilter !== 'all', fn ($q) => $q->where('catalogs.asset_type_id', $this->assetTypeFilter))
            ->when($this->search !== '', function ($q) use ($likeOperator) {
                $term = "%{$this->search}%";
                $q->where(function ($sub) use ($likeOperator, $term) {
                    $sub->where('catalogs.title', $likeOperator, $term)
                        ->orWhere('catalogs.isbn_issn', $likeOperator, $term)
                        ->orWhereHas('author', fn ($a) => $a->where('name', $likeOperator, $term))
                        ->orWhereHas('acquisitions.accessions', fn ($acc) => $acc->where('call_number', $likeOperator, $term)
                            ->orWhere('accession_number', $likeOperator, $term));
                });
            })
            ->latest('catalogs.created_at');
    }

    private function getSystemStats(): array
    {
        $damagedConditions = ['minor damaged', 'minor damage', 'severe damaged', 'damaged'];
        $totalReceived = (int) Acquisition::sum('quantity_received');
        $totalAccessioned = (int) Accession::count();

        return [
            'total_accessioned'   => $totalAccessioned,
            'total_unaccessioned' => max(0, $totalReceived - $totalAccessioned),
            'total_available'     => Accession::where('status', 'available')->count(),
            'total_on_loan'       => Accession::where('status', 'on loan')->count(),
            'total_stored'        => Accession::where('status', 'stored')->count(),
            'total_damaged'       => Accession::where(function ($q) use ($damagedConditions) {
                $q->whereIn('condition', $damagedConditions)
                    ->orWhereIn('status', $damagedConditions);
            })->count(),
            'total_lost'          => Accession::where(function ($q) {
                $q->whereIn('condition', ['lost', 'missing'])
                    ->orWhereIn('status', ['lost', 'missing']);
            })->count(),
            'total_dumped'        => Accession::where(function ($q) {
                $q->where('condition', 'dumped')
                    ->orWhere('status', 'dumped');
            })->count(),
        ];
    }

    public function exportExcel(): ?StreamedResponse
    {
        try {
            $filename = 'inventory-summary-' . now()->format('Y-m-d') . '.csv';

            $catalogs = $this->getInventoryQuery()->get();
            $stats = $this->getSystemStats();
            $searchFilterText = $this->search !== ''
                ? strtoupper("TITLE/AUTHOR/ISBN/ACCESSION: '{$this->search}'")
                : 'NONE (ALL APPLIED)';
            $assetTypeText = $this->assetTypeFilter === 'all'
                ? 'ALL'
                : strtoupper((string) (AssetType::find($this->assetTypeFilter)?->name ?? 'UNKNOWN'));

            return response()->stream(function () use ($catalogs, $stats, $searchFilterText, $assetTypeText) {
                $file = fopen('php://output', 'w');
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

                fputcsv($file, ['TFVC LIBRARY MANAGEMENT SYSTEM']);
                fputcsv($file, ['INVENTORY SUMMARY REPORT']);
                fputcsv($file, ['GENERATED ON: ' . strtoupper(now()->format('F d, Y h:i A')), '', '', '', '', '', '', '', '', 'GENERATED BY: ' . strtoupper((string) (auth()->user()?->name ?? 'SYSTEM USER'))]);
                fputcsv($file, ['SEARCH FILTER: ' . $searchFilterText, '', '', '', '', '', '', '', '', 'ASSET TYPE FILTER: ' . $assetTypeText]);
                fputcsv($file, []);

                fputcsv($file, ['OVERVIEW STATISTICS']);
                fputcsv($file, ['TOTAL ACCESSIONED', 'TOTAL UNACCESSIONED', 'AVAILABLE', 'ON LOAN', 'STORED', 'DAMAGED', 'LOST', 'DUMPED']);
                fputcsv($file, [$stats['total_accessioned'], $stats['total_unaccessioned'], $stats['total_available'], $stats['total_on_loan'], $stats['total_stored'], $stats['total_damaged'], $stats['total_lost'], $stats['total_dumped']]);
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
        try {
            $catalogs = $this->getInventoryQuery()->get();
            $stats = $this->getSystemStats();
            $searchFilterText = $this->search !== ''
                ? strtoupper("TITLE/AUTHOR/ISBN/ACCESSION: '{$this->search}'")
                : 'NONE (ALL APPLIED)';

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.inventory-summary', [
                'catalogs' => $catalogs,
                'stats'    => $stats,
                'search'   => $searchFilterText,
            ])->setPaper('a4', 'landscape');

            $filename = 'inventory-summary-' . now()->format('Y-m-d_H-i-s') . '.pdf';

            return response()->streamDownload(function () use ($pdf) {
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
        try {
            $catalogs = $this->getInventoryQuery()->paginate(15);
            $assetTypes = AssetType::orderBy('name')->get();
            $stats = $this->getSystemStats();
        } catch (Throwable $e) {
            Log::error('Error loading inventory management data: ' . $e->getMessage());
            $this->dispatch('toast', message: 'UNABLE TO LOAD INVENTORY SUMMARY DATA.', type: 'error');

            $catalogs = new \Illuminate\Pagination\LengthAwarePaginator(collect(), 0, 15);
            $assetTypes = new Collection();
            $stats = [
                'total_accessioned'   => 0,
                'total_unaccessioned' => 0,
                'total_available'     => 0,
                'total_on_loan'       => 0,
                'total_stored'        => 0,
                'total_damaged'       => 0,
                'total_lost'          => 0,
                'total_dumped'        => 0,
            ];
        }

        return view('livewire.inventory-management', [
            'catalogs'   => $catalogs,
            'assetTypes' => $assetTypes,
            'stats'      => $stats,
        ]);
    }
}

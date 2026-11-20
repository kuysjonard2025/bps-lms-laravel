<?php

namespace App\Livewire;

use App\Models\Accession;
use App\Models\Acquisition;
use App\Models\AssetType;
use App\Models\Catalog;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InventoryManagement extends Component
{
    use WithPagination;

    public string $search = '';
    public string $assetTypeFilter = 'all';
    public string $assetTypeSearch = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingAssetTypeFilter(): void
    {
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

    public function updatedAssetTypeFilter(string $value): void
    {
        if ($value === 'all') {
            $this->assetTypeSearch = '';
        } else {
            $type = AssetType::find($value);
            $this->assetTypeSearch = $type ? strtoupper($type->name) : '';
        }

        $this->resetPage();
    }

    private function likeOperator(): string
    {
        return config('database.default') === 'pgsql' ? 'ilike' : 'like';
    }

    private function baseAccessionsForCatalog(string $tableAlias = 'acc')
    {
        return Accession::from('accessions as ' . $tableAlias)
            ->join('acquisitions as acq', $tableAlias . '.acquisition_id', '=', 'acq.id')
            ->whereColumn('acq.catalog_id', 'catalogs.id');
    }

    private function getInventoryQuery()
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
            ->when($this->search, function ($q) use ($likeOperator) {
                $q->where(function ($sub) use ($likeOperator) {
                    $sub->where('catalogs.title', $likeOperator, "%{$this->search}%")
                        ->orWhere('catalogs.isbn_issn', $likeOperator, "%{$this->search}%")
                        ->orWhereHas('author', fn ($a) => $a->where('name', $likeOperator, "%{$this->search}%"))
                        ->orWhereHas('acquisitions.accessions', fn ($acc) => $acc->where('call_number', $likeOperator, "%{$this->search}%")
                            ->orWhere('accession_number', $likeOperator, "%{$this->search}%"));
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

    public function exportExcel(): StreamedResponse
    {
        $filename = 'inventory-summary-' . date('Y-m-d') . '.csv';

        $catalogs = $this->getInventoryQuery()->get();
        $stats = $this->getSystemStats();
        $searchFilterText = $this->search ? strtoupper("TITLE/AUTHOR/ISBN/ACCESSION: '{$this->search}'") : 'NONE (ALL APPLIED)';
        $assetTypeText = $this->assetTypeFilter === 'all'
            ? 'ALL'
            : strtoupper(AssetType::find($this->assetTypeFilter)->name ?? 'UNKNOWN');

        return response()->stream(function () use ($catalogs, $stats, $searchFilterText, $assetTypeText) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, ['BPS LIBRARY MANAGEMENT SYSTEM']);
            fputcsv($file, ['INVENTORY SUMMARY REPORT']);
            fputcsv($file, ['GENERATED ON: ' . strtoupper(date('F d, Y h:i A')), '', '', '', '', '', '', '', '', 'GENERATED BY: ' . strtoupper(auth()->user()->name ?? 'SYSTEM USER')]);
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
                    strtoupper(html_entity_decode(strip_tags($catalog->title . ($catalog->isbn_issn ? ' (ISBN: ' . $catalog->isbn_issn . ')' : '')))),
                    strtoupper(html_entity_decode(strip_tags($catalog->author->name ?? 'UNKNOWN AUTHOR'))),
                    strtoupper(html_entity_decode(strip_tags($catalog->assetType->name ?? 'STANDARD'))),
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
    }

    public function exportPdf()
    {
        $catalogs = $this->getInventoryQuery()->get();
        $stats = $this->getSystemStats();
        $searchFilterText = $this->search ? strtoupper("TITLE/AUTHOR/ISBN/ACCESSION: '{$this->search}'") : 'NONE (ALL APPLIED)';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.inventory-summary', [
            'catalogs' => $catalogs,
            'stats'    => $stats,
            'search'   => $searchFilterText,
        ])->setPaper('a4', 'landscape');

        $filename = 'inventory-summary-' . date('Y-m-d_H-i-s') . '.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    #[Layout('components.layouts.app')]
    #[Title('Inventory Summary')]
    public function render()
    {
        return view('livewire.inventory-management', [
            'catalogs'   => $this->getInventoryQuery()->paginate(15),
            'assetTypes' => AssetType::orderBy('name')->get(),
            'stats'      => $this->getSystemStats(),
        ]);
    }
}

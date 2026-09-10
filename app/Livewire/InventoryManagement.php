<?php

namespace App\Livewire;

use App\Models\Catalog;
use App\Models\AssetType;
use App\Models\Accession;
use App\Models\Circulation;
use Illuminate\Support\Facades\DB;
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

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingAssetTypeFilter(): void { $this->resetPage(); }

    private function getInventoryQuery()
    {
        $likeOperator = config('database.default') === 'pgsql' ? 'ilike' : 'like';

        return Catalog::with(['author', 'assetType'])
            ->withCount([
                'accessions as total_copies',
                'accessions as available_copies' => fn ($q) => $q->where('status', 'available'),
                'accessions as on_loan_copies' => fn ($q) => $q->whereHas('circulations', fn ($c) => $c->where('status', 'borrowed')->whereNull('returned_at')),
                'accessions as reserved_copies' => fn ($q) => $q->where('status', 'reserved'),
                'accessions as maintenance_copies' => fn ($q) => $q->where('status', 'under maintenance'),
                'accessions as damaged_copies' => fn ($q) => $q->where(function ($sub) {
                    $sub->whereIn('condition', ['damaged', 'Damaged'])
                        ->orWhereIn('status', ['damaged', 'Damaged', 'under maintenance'])
                        ->orWhereHas('circulations', fn ($c) => $c->where('condition', 'damaged'));
                }),
                'accessions as lost_copies' => fn ($q) => $q->where(function ($sub) {
                    $sub->whereIn('condition', ['lost', 'Lost', 'missing'])
                        ->orWhereIn('status', ['lost', 'Lost', 'missing'])
                        ->orWhereHas('circulations', fn ($c) => $c->where('condition', 'lost'));
                }),
                'accessions as acquisition_batches' => fn ($q) => $q->select(DB::raw('count(distinct(acquisition_id))')),
            ])
            ->when($this->assetTypeFilter !== 'all', fn ($q) => $q->where('asset_type_id', $this->assetTypeFilter))
            ->when($this->search, function ($q) use ($likeOperator) {
                $q->where(function ($sub) use ($likeOperator) {
                    $sub->where('title', $likeOperator, "%{$this->search}%")
                        ->orWhere('isbn_issn', $likeOperator, "%{$this->search}%")
                        ->orWhereHas('author', fn ($a) => $a->where('name', $likeOperator, "%{$this->search}%"))
                        ->orWhereHas('accessions', fn ($acc) => $acc->where('call_number', $likeOperator, "%{$this->search}%"));
                });
            })
            ->latest();
    }

    private function getSystemStats()
    {
        return [
            'total_items'     => Accession::count(),
            'total_available' => Accession::where('status', 'available')->count(),
            'total_on_loan'   => Circulation::where('status', 'borrowed')->whereNull('returned_at')->count(),
            'total_damaged'   => Accession::where(function ($q) {
                                     $q->whereIn('condition', ['damaged', 'Damaged'])
                                       ->orWhereIn('status', ['damaged', 'Damaged'])
                                       ->orWhereHas('circulations', fn ($c) => $c->where('condition', 'damaged'));
                                 })->count(),
            'total_lost'      => Accession::where(function ($q) {
                                     $q->whereIn('condition', ['lost', 'Lost', 'missing'])
                                       ->orWhereIn('status', ['lost', 'Lost', 'missing'])
                                       ->orWhereHas('circulations', fn ($c) => $c->where('condition', 'lost'));
                                 })->count(),
        ];
    }

    public function exportExcel(): StreamedResponse
    {
        $filename = 'inventory-summary-' . date('Y-m-d') . '.csv';

        $catalogs = $this->getInventoryQuery()->get();
        $stats = $this->getSystemStats();
        $searchFilterText = $this->search ? "Title/Author/ISBN: '{$this->search}'" : 'None (All applied)';
        $assetTypeText = $this->assetTypeFilter === 'all' ? 'All' : (AssetType::find($this->assetTypeFilter)->name ?? 'Unknown');

        return response()->stream(function() use ($catalogs, $stats, $searchFilterText, $assetTypeText) {
            $file = fopen('php://output', 'w');

            // Add UTF-8 BOM for proper Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['BPS LIBRARY MANAGEMENT SYSTEM']);
            fputcsv($file, ['Inventory Summary Report']);
            fputcsv($file, ['Generated On: ' . date('F d, Y h:i A'), '', '', '', '', '', '', 'Generated By: ' . (auth()->user()->name ?? 'System User')]);
            fputcsv($file, ['Search Filter: ' . $searchFilterText, '', '', '', '', '', '', 'Asset Type Filter: ' . $assetTypeText]);
            fputcsv($file, []);

            fputcsv($file, ['OVERVIEW STATISTICS']);
            fputcsv($file, ['Total Physical Copies', 'Available Copies', 'Currently on Loan', 'Damaged Copies', 'Lost Copies']);
            fputcsv($file, [$stats['total_items'], $stats['total_available'], $stats['total_on_loan'], $stats['total_damaged'], $stats['total_lost']]);
            fputcsv($file, []);

            fputcsv($file, ['Title', 'Author', 'Asset Type', 'Total', 'Available', 'On Loan', 'Reserved', 'Maintenance', 'Damaged', 'Lost', 'Batches']);

            foreach ($catalogs as $catalog) {
                fputcsv($file, [
                    html_entity_decode(strip_tags($catalog->title . ($catalog->isbn_issn ? ' (ISBN: ' . $catalog->isbn_issn . ')' : ''))),
                    html_entity_decode(strip_tags($catalog->author->name ?? 'Unknown Author')),
                    html_entity_decode(strip_tags($catalog->assetType->name ?? 'Standard')),
                    $catalog->total_copies,
                    $catalog->available_copies,
                    $catalog->on_loan_copies,
                    $catalog->reserved_copies,
                    $catalog->maintenance_copies,
                    $catalog->damaged_copies,
                    $catalog->lost_copies,
                    $catalog->acquisition_batches,
                ]);
            }
            fclose($file);
        }, 200, [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ]);
    }

    public function exportPdf()
    {
        $catalogs = $this->getInventoryQuery()->get();
        $stats = $this->getSystemStats();
        $searchFilterText = $this->search ? "Title/Author/ISBN: '{$this->search}'" : 'None (All applied)';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.inventory-summary', [
            'catalogs' => $catalogs,
            'stats'    => $stats,
            'search'   => $searchFilterText
        ])->setPaper('a4', 'landscape');

        $filename = 'inventory-summary-' . date('Y-m-d_H-i-s') . '.pdf';

        // Return a direct raw Symfony Response to prevent Livewire from intercepting/JSON-encoding the binary stream
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

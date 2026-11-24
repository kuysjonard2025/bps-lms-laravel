<?php

// app/Exports/PatronsExcelExport.php

namespace App\Exports;

use App\Models\Patron;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PatronsExcelExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private readonly string $search = '',
    ) {}

    private function likeOperator(): string
    {
        return DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
    }

    private function query(): Builder
    {
        $operator = $this->likeOperator();
        $term = trim($this->search);

        return Patron::with(['patronType', 'gradeLevel', 'section', 'department', 'strand', 'adviser'])
            ->when($term !== '', function ($query) use ($operator, $term) {
                $query->where(function ($q) use ($operator, $term) {
                    $q->where('school_id', $operator, "%{$term}%")
                        ->orWhere('rfid_tag', $operator, "%{$term}%")
                        ->orWhere('first_name', $operator, "%{$term}%")
                        ->orWhere('middle_name', $operator, "%{$term}%")
                        ->orWhere('last_name', $operator, "%{$term}%")
                        ->orWhere('email', $operator, "%{$term}%")
                        ->orWhere('address', $operator, "%{$term}%")
                        ->orWhereHas('gradeLevel', fn ($g) => $g->where('name', $operator, "%{$term}%")->orWhere('code', $operator, "%{$term}%"))
                        ->orWhereHas('section', fn ($s) => $s->where('name', $operator, "%{$term}%"))
                        ->orWhereHas('department', fn ($d) => $d->where('name', $operator, "%{$term}%")->orWhere('code', $operator, "%{$term}%"))
                        ->orWhereHas('strand', fn ($st) => $st->where('name', $operator, "%{$term}%")->orWhere('code', $operator, "%{$term}%"))
                        ->orWhereHas('adviser', fn ($a) => $a->where('first_name', $operator, "%{$term}%")->orWhere('last_name', $operator, "%{$term}%"));
                });
            })
            ->latest();
    }

    public function collection(): Collection
    {
        return $this->query()->get();
    }

    public function headings(): array
    {
        return [
            'Student/Employee #',
            'RFID Tag',
            'Full Name',
            'Type',
            'Department',
            'Grade Level',
            'Section',
            'Adviser',
            'Strand',
            'Contact #',
            'Email',
            'Address',
            'Status',
        ];
    }

    /**
     * @param  Patron  $patron
     */
    public function map($patron): array
    {
        $adviserName = $patron->adviser?->full_name
            ?? $patron->adviser?->name
            ?? trim(($patron->adviser?->last_name ? $patron->adviser->last_name.', ' : '')
                .($patron->adviser?->first_name ?? '').' '
                .($patron->adviser?->middle_name ?? '').' '
                .($patron->adviser?->suffix ?? ''));

        return [
            strtoupper((string) ($patron->school_id ?? '')),
            strtoupper((string) ($patron->rfid_tag ?? '')),
            Str::title(trim("{$patron->last_name}, {$patron->first_name} {$patron->middle_name} {$patron->suffix}")),
            $patron->patronType?->name ? strtoupper($patron->patronType->name) : 'N/A',
            $patron->department?->name ? strtoupper($patron->department->name) : '—',
            $patron->gradeLevel?->name ? Str::title($patron->gradeLevel->name) : '—',
            $patron->section?->name ? Str::title($patron->section->name) : '—',
            $adviserName ? Str::title($adviserName) : '—',
            $patron->strand?->code ? strtoupper($patron->strand->code) : ($patron->strand?->name ? Str::title($patron->strand->name) : '—'),
            (string) ($patron->contact_number ?? ''),
            (string) ($patron->email ?? ''),
            $patron->address ? Str::title($patron->address) : 'N/A',
            strtoupper((string) $patron->status),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => '1E293B']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'EEF5FF'],
                ],
            ],
        ];
    }
}

<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TrendSheet implements FromCollection, WithTitle, WithHeadings, WithStyles, WithColumnWidths, WithMapping
{
    public function __construct(private Collection $trend) {}

    public function title(): string
    {
        return 'Sales Tren';
    }

    public function collection(): Collection
    {
        return $this->trend;
    }

    public function headings(): array
    {
        return ['Period', 'Total Orders', 'Revenue (Rp)', 'Success', 'Pending', 'Cancelled'];
    }

    public function map($row): array
    {
        return [
            $row['period'],
            $row['total_orders'],
            $row['revenue'],
            $row['success_count'],
            $row['pending_count'],
            $row['cancelled_count'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'  => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'  => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2F75B6']],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 20, 'B' => 15, 'C' => 20, 'D' => 12, 'E' => 12, 'F' => 12];
    }
}
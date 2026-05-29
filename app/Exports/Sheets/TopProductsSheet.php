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

class TopProductsSheet implements FromCollection, WithTitle, WithHeadings, WithStyles, WithColumnWidths, WithMapping
{
    public function __construct(private Collection $products) {}

    public function title(): string
    {
        return 'Top Products';
    }

    public function collection(): Collection
    {
        return $this->products;
    }

    public function headings(): array
    {
        return ['#', 'Product Name', 'Qty Terjual', 'Total Revenue (Rp)', 'Total Orders'];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;
        return [
            $no,
            $row->product,
            $row->total_qty,
            $row->total_revenue,
            $row->total_orders,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'  => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'  => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF375623']],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 5, 'B' => 40, 'C' => 15, 'D' => 20, 'E' => 15];
    }
}
<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ProductReportExport implements WithMultipleSheets
{
    public function __construct(
        protected $products,
        protected array $period
    ) {}

    public function sheets(): array
    {
        return [
            'Performa Produk' => new ProductReportMainSheet($this->products, $this->period),
        ];
    }
}

// ─── Sheet 1: Performa Produk ─────────────────────────────────────

class ProductReportMainSheet implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithTitle,
    ShouldAutoSize
{
    public function __construct(
        protected $products,
        protected array $period
    ) {}

    public function title(): string
    {
        return 'Performa Produk';
    }

    public function collection()
    {
        return $this->products;
    }

    public function headings(): array
    {
        return [
            // Row 1: Info periode (akan di-merge via styles)
            ['Laporan Performa Produk — ' . ($this->period['label'] ?? '') .
             '  (' . ($this->period['from'] ?? '') . ' s/d ' . ($this->period['to'] ?? '') . ')'],
            // Row 2: kosong
            [''],
            // Row 3: header kolom
            [
                '#',
                'Nama Produk',
                'Kategori',
                'Qty Terjual',
                'Jumlah Order',
                'Total Revenue (Rp)',
            ],
        ];
    }

    public function map($row): array
    {
        static $i = 0;
        $i++;
        return [
            $i,
            $row->product_name,
            $row->category ?? '-',
            (int) $row->total_qty,
            (int) $row->total_orders,
            (float) $row->total_revenue,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Merge baris judul di row 1
        $sheet->mergeCells('A1:F1');

        return [
            // Judul
            1 => [
                'font'      => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFED1F24']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
            // Header kolom (row 3)
            3 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF374151']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}
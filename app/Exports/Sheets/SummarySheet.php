<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class SummarySheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(private array $summary, private array $period) {}

    public function title(): string
    {
        return 'Summary';
    }

    public function array(): array
    {
        $s = $this->summary;
        $p = $this->period;

        return [
            ['SALES REPORT - SUMMARY'],
            ['Period', "{$p['label']} ({$p['from']} s/d {$p['to']})"],
            [''],
            ['KPI', 'Value', 'vs Periode Sebelumnya'],
            ['Total Orders',    $s['total_orders'],     $this->growthLabel($s['orders_growth'])],
            ['Total Revenue',   $s['total_revenue'],    $this->growthLabel($s['revenue_growth'])],
            ['Avg Order Value', $s['avg_order_value'],  '—'],
            ['Total Subtotal',  $s['total_subtotal'],   '—'],
            ['Total Shipping',  $s['total_shipping'],   '—'],
            [''],
            ['Status Breakdown', '', ''],
            ['Success',   $s['total_success'],   $s['total_orders'] > 0 ? round($s['total_success']  / $s['total_orders'] * 100, 1) . '%' : '0%'],
            ['Pending',   $s['total_pending'],   $s['total_orders'] > 0 ? round($s['total_pending']  / $s['total_orders'] * 100, 1) . '%' : '0%'],
            ['Cancelled', $s['total_cancelled'], $s['total_orders'] > 0 ? round($s['total_cancelled'] / $s['total_orders'] * 100, 1) . '%' : '0%'],
            [''],
            ['Previous Period', "{$p['prev_from']} s/d {$p['prev_to']}", ''],
            ['Prev Revenue', $s['prev_revenue'], ''],
            ['Prev Orders', $s['prev_orders'], ''],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F3864']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            4 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2F75B6']],
            ],
            11 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2F75B6']],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 25, 'B' => 20, 'C' => 25];
    }

    private function growthLabel(?float $growth): string
    {
        if ($growth === null) return 'N/A';
        return ($growth >=  0 ? '▲ ' : '▼ ') . abs($growth) . '%';
    }
}
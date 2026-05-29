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

class OrdersSheet implements FromCollection, WithTitle, WithHeadings, WithStyles, WithColumnWidths, WithMapping
{
    public function __construct(private Collection $orders) {}

    public function title(): string
    {
        return 'Orders';
    }

    public function collection(): collection
    {
        return $this->orders;
    }

    public function headings(): array
    {
        return ['Invoice', 'Customer', 'Courier', 'Subtotal (Rp)', 'Shipping (Rp)', 'Total (Rp)', 'Status', 'Date'];
    }

    public function map($order): array
    {
        return [
            $order->invoice_number,
            $order->customer_name,
            $order->shipping_courier,
            $order->subtotal,
            $order->shipping_cost,
            $order->total_price,
            strtoupper($order->status),
            $order->created_at->format('Y-m-d H:i'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF7B3F00']],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 20, 'B' => 25, 'C' => 15, 'D' => 18, 'F' => 18, 'G' => 12, 'H' => 18];
    }
}
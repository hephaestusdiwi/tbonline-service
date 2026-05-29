<?php

namespace App\Exports;

use App\Exports\Sheets\SummarySheet;
use App\Exports\Sheets\TrendSheet;
use App\Exports\Sheets\TopProductsSheet;
use App\Exports\Sheets\OrdersSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Collection;

class SalesReportExport implements WithMultipleSheets
{
    public function __construct(
        private array       $summary,
        private array       $period,
        private Collection  $trend,
        private Collection  $topProducts,
        private Collection  $orders
    ) {}

    public function sheets(): array
    {
        return [
            new SummarySheet($this->summary, $this->period),
            new TrendSheet($this->trend),
            new TopProductsSheet($this->topProducts),
            new OrdersSheet($this->orders),
        ];
    }
}
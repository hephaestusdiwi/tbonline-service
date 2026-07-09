<?php

namespace App\Exports;

use App\Services\Chat\ChatReportService;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ChatStaffReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    public function __construct(
        private Carbon $from,
        private Carbon $to,
    ) {}

    public function collection()
    {
        $result = app(ChatReportService::class)->staffReport($this->from, $this->to);

        return collect($result['rows']);
    }

    public function headings(): array
    {
        return [
            'Staff',
            'Total Sesi',
            'Avg Waktu Ambil Chat (detik)',
            'Avg First Response (detik)',
            'Avg Response (detik)',
            'Rating',
        ];
    }

    public function map($row): array
    {
        return [
            $row['agent_name'],
            $row['total_sessions'],
            $row['avg_time_to_assign_seconds'] ?? '-',
            $row['avg_first_response_seconds'] ?? '-',
            $row['avg_response_seconds'] ?? '-',
            $row['avg_rating'] ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'ED1F24'],
            ]],
        ];
    }

    public function title(): string
    {
        return 'KPI Staff ' . $this->from->toDateString() . ' - ' . $this->to->toDateString();
    }
}
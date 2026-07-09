<?php

namespace App\Http\Controllers\Api\V1\Chat;

use App\Http\Controllers\Controller;
use App\Services\Chat\ChatReportService;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Carbon;

class ChatReportController extends Controller
{
    public function __construct(private ChatReportService $reportService) {}

    public function staff(Request $request): JsonResponse
    {
        $data = $request->validate([
            'preset' => 'nullable|in:today,yesterday,week,month',
            'from'   => 'nullable|date',
            'to'     => 'nullable|date|after_or_equal:from',
        ]);

        [$from, $to] = $this->resolveRange($data);
        $result      = $this->reportService->staffReport($from, $to);

        return response()->json([
            'data'    => $result['rows'],
            'summary' => $result['summary'],
            'range'   => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
        ]);
    }

    private function resolveRange(array $data): array
    {
        if (!empty($data['from']) && !empty($data['to'])) {
            return [Carbon::parse($data['from'])->startOfDay(), Carbon::parse($data['to'])->endOfDay()];
        }

        return match ($data['preset'] ?? 'today') {
            'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            'week'      => [now()->startOfWeek(), now()->endOfWeek()],
            'month'     => [now()->startOfMonth(), now()->endOfMonth()],
            default     => [now()->startOfDay(), now()->endOfDay()],
        };
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyPoint;
use Illuminate\Http\Request;

class LoyaltyPointController extends Controller
{
    public function check(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|min:8|max:20',
        ]);

        $phone      = LoyaltyPoint::normalizePhone($request->phone);
        $balance    = LoyaltyPoint::getBalance($phone);
        $history    = LoyaltyPoint::getHistory($phone, 10);

        $expiringSoon = LoyaltyPoint::where('phone', $phone)
        ->where('type', 'earn')
        ->where('expired_at', '>', now())
        ->where('expired_at', '<=', now()->addDays(30))
        ->sum('points');

        return response()-> json([
            'phone'          => $phone,
            'balance'        => $balance,
            'balance_rupiah' => $balance, // 1 point = Rp 1
            'expiring_soon'  => (int) $expiringSoon, // point yang kadaluarsa dalam 30 hari
            'history'        => $history,
            'earn_per_100k'  => LoyaltyPoint::EARN_POINTS_PER_THRESHOLD, // 3000 point per Rp 100.000
            'expiry_months'  => LoyaltyPoint::EXPIRY_MONTHS,   // 3 bulan
        ]);
    }

    public function preview(Request $request)
    {
        $request->validate([
            'subtotal' => 'required|integer|min:0',
        ]);

        $points = LoyaltyPoint::calculateEarnPoints((int) $request->subtotal);

        return response()->json([
            'subtotal'      => (int) $request->subtotal,
            'points_earned' => $points,
            'earn_per_100k' => LoyaltyPoint::EARN_POINTS_PER_THRESHOLD,
            'expired_at'    => now()->addMonths(LoyaltyPoint::EXPIRY_MONTHS)->toDateString(),
        ]);
    }

    public function history(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|min:8|max:20',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $phone   = LoyaltyPoint::normalizePhone($request->phone);
        $limit   = (int) ($request->limit ?? 20);
        $balance = LoyaltyPoint::getBalance($phone);
        $history = LoyaltyPoint::getHistory($phone, $limit);

        return response()->json([
            'phone'   => $phone,
            'balance' => $balance,
            'total'   => $history->count(),
            'history' => $history,
        ]);
    }

    public function deduct(Request $request)
    {
        $request->validate([
            'phone'       => 'required|string|min:8|max:20',
            'points'      => 'required|integer|min:1',
            'description' => 'nullable|string|max:255',
        ]);

        $phone    = LoyaltyPoint::normalizePhone($request->phone);
        $balance  = LoyaltyPoint::getBalance($phone);

        if ($request->points > $balance) {
            return response()->json([
                'message' => "Saldo tidak cukup. Saldo saat ini: {$balance} point.",
            ], 422);
        }

        $record = LoyaltyPoint::create([
            'phone'       => $phone,
            'points'      => -$request->points, // negatif
            'type'        => 'deduct',
            'order_id'    => null,
            'description' => $request->description ?? 'Redeem manual oleh admin',
            'expired_at'  => null,
        ]);

        return response()->json([
            'message'         => 'Point berhasil dipotong.',
            'deducted_points' => $request->points,
            'remaining'       => LoyaltyPoint::getBalance($phone),
            'record_id'       => $record->id,
        ]);
    }

    public function stats()
    {
        return response()->json([
            'total_customers'       => LoyaltyPoint::distinct('phone')->count('phone'),
            'total_points_active'   => LoyaltyPoint::where('type', 'earn')
                                        ->where(fn($q) => $q->whereNull('expired_at')->orWhere('expired_at', '>', now()))
                                        ->sum('points'),
            'total_points_earned'   => LoyaltyPoint::where('type', 'earn')->sum('points'),
            'total_points_deducted' => abs(LoyaltyPoint::where('type', 'deduct')->sum('points')),
        ]);
    }

    public function recent()
    {
        $data = LoyaltyPoint::latest()->limit(50)->get()
            ->map(fn($r) => [
                'id'            => $r->id,
                'phone'         => $r->phone,
                'points'        => $r->points,
                'type'          => $r->type,
                'description'   => $r->description,
                'order_invoice' => $r->order?->invoice_number,
                'expired_at'    => $r->expired_at?->toDateString(),
                'is_expired'    => $r->expired_at?->isPast(),
                'created_at'    => $r->created_at->format('d M Y H:i'),
            ]);

        return response()->json(['data' => $data]);
    }
}

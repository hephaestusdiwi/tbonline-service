<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class LoyaltyPoint extends Model
{
    protected $fillable = [
        'phone',
        'points',
        'type',
        'order_id',
        'description',
        'expired_at',
    ];

    protected $casts = [
        'expired_at' => 'datetime',
        'points'     => 'integer',
    ];

    const EARN_THRESHOLD            = 100000;
    const EARN_POINTS_PER_THRESHOLD = 3000;
    const EXPIRY_MONTHS             = 3;      // fix: MOTHS → MONTHS

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public static function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }
        return '+' . ltrim($phone, '+');
    }

    public static function calculateEarnPoints(int $subtotal): int
    {
        $multiplier = (int) floor($subtotal / self::EARN_THRESHOLD);
        return $multiplier * self::EARN_POINTS_PER_THRESHOLD;
    }

    public static function earn(string $phone, int $subtotal, int $orderId, string $invoiceNumber): self
    {
        $points = self::calculateEarnPoints($subtotal);

        return self::create([
            'phone'       => self::normalizePhone($phone),
            'points'      => $points,
            'type'        => 'earn',
            'order_id'    => $orderId,
            'description' => "Point dari order #{$invoiceNumber}",
            'expired_at'  => Carbon::now()->addMonths(self::EXPIRY_MONTHS),
        ]);
    }

    public static function expireByOrder(int $orderId): void
    {
        $earnRecords = self::where('order_id', $orderId)
            ->where('type', 'earn')
            ->get();

        foreach ($earnRecords as $earn) {   // fix: $record → $earn
            $alreadyExpired = self::where('order_id', $orderId)
                ->where('type', 'expire')
                ->where('description', 'like', "%#{$earn->id}%")
                ->exists();

            if ($alreadyExpired) continue;

            self::create([
                'phone'       => $earn->phone,
                'points'      => -$earn->points,
                'type'        => 'expire',
                'order_id'    => $orderId,
                'description' => "Point hangus — order dibatalkan (ref earn #{$earn->id})",
                'expired_at'  => null,
            ]);
        }
    }

    public static function getBalance(string $phone): int
    {
        $normalized = self::normalizePhone($phone);

        $earned = self::where('phone', $normalized)
            ->where('type', 'earn')
            ->where(function ($q) {
                $q->whereNull('expired_at')
                  ->orWhere('expired_at', '>', Carbon::now());
            })
            ->sum('points');

        $deducted = self::where('phone', $normalized)
            ->whereIn('type', ['expire', 'deduct'])
            ->sum('points');

        return max(0, (int) $earned + (int) $deducted);
    }

    public static function getHistory(string $phone, int $limit = 20): \Illuminate\Support\Collection
    {
        return self::where('phone', self::normalizePhone($phone))
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn ($record) => [
                'id'          => $record->id,
                'points'      => $record->points,
                'type'        => $record->type,
                'description' => $record->description,
                'expired_at'  => $record->expired_at?->toDateString(),
                'is_expired'  => $record->expired_at && $record->expired_at->isPast(),
                'created_at'  => $record->created_at->format('d M Y H:i'),
            ]);
    }
}
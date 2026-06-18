<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\PromoCode;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PromoCodeController extends Controller
{
    // get /api/promo-codes
    public function index()
    {
        return response()->json([
            'data'  => PromoCode::latest()->get()
        ]);
    }

    // post /api/promo-codes
    public function store(Request $request)
    {
        $data = $request->validate([
            'code'           => 'required|string|max:50|unique:promo_codes,code',
            'description'    => 'nullable|string|max:255',
            'discount_type'  => 'required|in:percentage,fixed,free_shipping',
            'discount_value' => 'required_unless:discount_type,free_shipping|numeric|min:0',
            'min_purchase'   => 'nullable|integer|min:0',
            'max_usage'      => 'nullable|integer|min:1',
            'is_active'      => 'boolean',
            'expired_at'     => 'nullable|date|after:now',
            'show_popup'     => 'boolean',
            'popup_label'    => 'nullable|string|max:100',
        ]);

        $data['code'] = strtoupper(trim($data['code']));
        $data['discount_value'] = $data['discount_value'] ?? 0;
        $data['min_purchase']   = $data['min_purchase'] ?? 0;

        $promo = Promocode::create($data);
        return response()->json(['data' => $promo], 201);
    }

    // put /api/promo-codes{$id}
    public function update(Request $request, PromoCode $promoCode)
    {
        $data = $request->validate([
            'code'           => 'string|max:50|unique:promo_codes,code,' . $promoCode->id,
            'description'    => 'nullable|string|max:255',
            'discount_type'  => 'in:percentage,fixed,free_shipping',
            'discount_value' => 'numeric|min:0',
            'min_purchase'   => 'nullable|integer|min:0',
            'max_usage'      => 'nullable|integer|min:1',
            'is_active'      => 'boolean',
            'expired_at'     => 'nullable|date',
            'show_popup'     => 'boolean',
            'popup_label'    => 'nullable|string|max:100',
        ]);

        if (isset($data['code'])) {
            $data['code'] = strtoupper(trim($data['code']));
        }

        $promoCode->update($data);
        return response()->json(['data' => $promoCode]);
    }

    // delete /api/promo-codes{$id}
    public function destroy(PromoCode $promoCode)
    {
        $promoCode->delete();
        return response()->json(['message' => 'Promo code deleted']);
    }

    // post /api/promo-codes/validate dipanggil dari CheckoutPage
    public function validateCode(Request $request)
    {
        $request->validate([
            'code'              => 'required|string',
            'subtotal'          => 'required|integer|min:0',
            'shipping_cost'     => 'nullable|integer|min:0', 
            'phone'             => 'nullable|string',  
        ]);

        $promo = PromoCode::where('code', strtoupper(trim($request->code)))->first();

        if (!$promo) {
            return response()->json(['valid' => false, 'message' => 'Kode promo tidak ditemukan'], 404);
        }

        // Debug
        \Log::info('Promo debug', [
            'promo'        => $promo->toArray(),
            'subtotal'     => $request->subtotal,
            'phone'        => $request->phone,
            'is_active'    => $promo->is_active,
            'expired_at'   => $promo->expired_at,
            'min_purchase' => $promo->min_purchase,
            'max_usage'    => $promo->max_usage,
            'used_count'   => $promo->used_count,
        ]);

        $check = $promo->isValid($request->subtotal, $request->phone);

        // TAMBAH INI SEMENTARA ↓
        \Log::info('Check result', $check);

        if (!$check['valid']) {
            return response()->json($check, 422);
        }

        $discount = $promo->calculateDiscount($request->subtotal, $request->shipping_cost);

        return response()->json([
            'valid'             => true,
            'message'           => $check['message'],
            'code'              => $promo->code,
            'discount_type'     => $promo->discount_type,
            'discount_value'    => $promo->discount_value,
            'discount_amount'   => $discount,
            'description'       => $promo->description,
        ]);
    }

    public function popupCodes()
    {
        $promos = PromoCode::where('show_popup', true)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expired_at')
                ->orWhere('expired_at', '>', now());
            })
            ->where(function ($q) {
                $q->whereNull('max_usage')
                ->orWhereColumn('used_count', '<', 'max_usage');
            })
            ->orderByRaw('popup_label IS NOT NULL DESC') // yang punya label khusus duluan
            ->get(['id', 'code', 'description', 'discount_type', 'discount_value',
                'min_purchase', 'popup_label']);

        return response()->json(['data' => $promos]);
    }
}

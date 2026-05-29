<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDeleteRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class OrderDeleteRequestController extends Controller
{
    /**
     * GET /api/order-delete-requests
     * Daftar semua request — hanya admin (can:orders_delete)
     */
    public function index(Request $request)
    {
        $query = OrderDeleteRequest::with([
            'order:id,invoice_number,customer_name,total_price,status',
            'requester:id,name,role',
            'reviewer:id,name,role',
        ])->latest();

        if ($request->status && in_array($request->status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $request->status);
        }

        // Search by invoice // customer name / requester name
        if ($request->search) {
            $q = $request->search;
            $query->where(function ($q2) use ($q) {
                $q2->whereHas('order', fn($o) =>
                    $o->where('invoice_number', 'like', "%{$q}%")
                      ->orWhere('customer_name', 'like', "%{$q}%")
                )->orWhereHas('requester', fn($u) =>
                    $u->where('name', 'like', "%{$q}%")
                );
            });
        }

        $results = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'data' => $results->items(),
            'meta' => [
                'total'        => $results->total(),
                'per_page'     => $results->perPage(),
                'current_page' => $results->currentPage(),
                'last_page'    => $results->lastPage(),
            ],
            'counts' => [
                'pending'   => OrderDeleteRequest::where('status', 'pending')->count(),
                'approved'  => OrderDeleteRequest::where('status', 'approved')->count(),
                'rejected'  => OrderDeleteRequest::where('status', 'rejected')->count(),
            ],
        ]);
    }
    /**
     * POST /api/orders/{orderId}/request-delete
     * Staff / Manager mengajukan request hapus
     */
    public function store(Request $request, $orderId)
    {
        $request->validate([
            'reason' => 'required|string|min:10|max:500',
        ], [
            'reason.required' => 'Alasan pengajuan wajib diisi',
            'reason.min'      => 'Alasan minimal 10 karakter',
            'reason.max'      => 'Alasan maksimal 500 karakter',
        ]);

        $order = Order::findOrFail($orderId);

        // check apakah sudah ada request pending untuk order
        $existing = OrderDeleteRequest::where('order_id', $order->id)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Order ini sudah memiliki pengajuan hapus yang sedang menunggu persetujuan admin',
            ], 422);
        }

        $deleteRequest = OrderDeleteRequest::create([
            'order_id'      => $order->id,
            'requested_by'  => auth()->id(),
            'reason'        => $request->reason,
            'status'        => 'pending',
        ]);

        // Pending count cache
        Cache::forget('delete_requests_pending_count');

        return response()->json([
            'message'   => 'Pengajuan hapus berhasil dikirim. Menunggu persetujuan admin',
            'date'      => $deleteRequest->load(['order:id,invoice_number,customer_name', 'requester:id,name,role']),
        ], 201);
    }

    /**
     * PATCH /api/order-delete-requests/{id}/review
     * Admin approve atau reject request
     */
    public function review(Request $request, $id)
    {
        $request->validate([
            'action'        => 'required|in:approved,rejected',
            'review_note'   => 'nullable|string|max:500',
        ], [
            'action.required' => 'Aksi wajib dipilih',
            'action.in'       => 'Aksi tidak valid',
        ]);

        $deleteRequest = OrderDeleteRequest::with('order')->findOrFail($id);

        if ($deleteRequest->status !== 'pending') {
            return response()->json([
                'message' => "Request ini sudah di-{$deleteRequest->status}, tidak bisa diubah lagi",
            ], 422);
        }

        $deleteRequest->update([
            'status'      => $request->action,
            'reviewed_by' => auth()->id(),
            'review_note' => $request->review_note ?? null,
            'reviewed_at' => now(),
        ]);

        // jika disetujui -> hapus order sekarang
        if ($request->action === 'approved') {
            $order = $deleteRequest->order;
            if ($order) {
                $order->delete();
                Cache::forget('orders_pending_count');
            }
        }

        Cache::forget('delete_requests_pending_count');

        $label = $request->action === 'approved' ? 'disetujui dan order telah dihapus' : 'ditolak';

        return response()->json([
            'message'   => "Pengajuan berhasil {$label}",
            'data'      => $deleteRequest->load(['order', 'requester:id,name,role', 'reviewer:id,name,role']),
        ]);
    }

    /**
     * GET /api/order-delete-requests/pending-count
     * Jumlah request pending — badge notifikasi di sidebar
     */
    public function pendingCount()
    {
        $count = Cache::remember('delete_requests_pending_count', 30, function () {
            return OrderDeleteRequest::where('status', 'pending')->count();
        });

        return response()->json(['count' => $count]);
    }
}

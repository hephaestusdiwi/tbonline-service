<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    public function index(Request $request)
    {
        $query = Complaint::with(['session', 'resolver'])
            ->latest();

        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('customer_name', 'like', "%{$request->search}%")
                  ->orWhere('customer_phone', 'like', "%{$request->search}%")
                  ->orWhere('complaint_text', 'like', "%{$request->search}%");
            });
        }

        return response()->json($query->paginate(10));
    }

    public function updateStatus(Request $request, Complaint $complaint)
    {
        $request->validate([
            'status'          => 'required|in:open,in_progress,resolved',
            'resolution_note' => 'nullable|string',
        ]);

        $complaint->update([
            'status'          => $request->status,
            'resolved_at'     => $request->status === 'resolved' ? now() : null,
            'resolved_by'     => $request->status === 'resolved' ? auth()->id() : null,
            'resolution_note' => $request->resolution_note ?? $complaint->resolution_note,
        ]);

        return response()->json($complaint->load('resolver'));
    }
}

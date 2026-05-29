<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Navigation;
use Illuminate\Http\Request;

class NavigationController extends Controller
{
    // GET /api/navigations (public)
    public function index()
    {
        $navigations = Navigation::where('is_active', true)
                                 ->orderBy('order')
                                 ->get();

        return response()->json($navigations);
    }

    // GET /api/admin/navigations (admin - semua data)
    public function adminIndex()
    {
        $navigations = Navigation::orderBy('order')->get();
        return response()->json($navigations);
    }

    // POST /api/admin/navigations
    public function store(Request $request)
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'url'   => 'required|string|max:255',
            'order' => 'nullable|integer',
        ]);

        $navigation = Navigation::create([
            'label'     => $request->label,
            'url'       => $request->url,
            'order'     => $request->order ?? 0,
            'is_active' => true,
        ]);

        return response()->json($navigation, 201);
    }

    // PUT /api/admin/navigations/{id}
    public function update(Request $request, $id)
    {
        $navigation = Navigation::findOrFail($id);

        $request->validate([
            'label'     => 'required|string|max:255',
            'url'       => 'required|string|max:255',
            'order'     => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $navigation->update([
            'label'     => $request->label,
            'url'       => $request->url,
            'order'     => $request->order ?? $navigation->order,
            'is_active' => $request->is_active ?? $navigation->is_active,
        ]);

        return response()->json($navigation);
    }

    // DELETE /api/admin/navigations/{id}
    public function destroy($id)
    {
        $navigation = Navigation::findOrFail($id);
        $navigation->delete();

        return response()->json(['message' => 'Navigasi berhasil dihapus']);
    }
}
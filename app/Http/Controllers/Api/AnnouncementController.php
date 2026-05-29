<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index()
    {
        return response()->json(
            Announcement::active()->get(['id', 'text', 'link_url', 'link_label', 'sort_order'])
        );
    }

    public function adminIndex()
    {
        return response()->json(
            Announcement::orderBy('sort_order')->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'text'        => 'required|string|max:255',
            'link_url'    => 'nullable|url|max:255',
            'link_label'  => 'nullable|string|max:100',
            'sort_order'  => 'integer|min:0',
            'is_active'   => 'boolean',
        ]);

        $announcement = Announcement::create($data);

        return response()->json($announcement, 201);
    }

    public function update(Request $request, Announcement $announcement)
    {
        $data = $request->validate([
            'text'        => 'required|string|max:255',
            'link_url'    => 'nullable|url|max:255',
            'link_label'  => 'nullable|string|max:100',
            'sort_order'  => 'integer|min:0',
            'is_active'   => 'boolean',
        ]);

        $announcement->update($data);

        return response()->json($announcement);
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return response()->json(['message' => 'Berhasil dihapus']);
    }

    public function reorder(Request $request)
    {
        $request->validate(['ids' => 'required|array']);

        foreach ($request->ids as $order => $id) {
            Announcement::where('id', $id)->update(['sort_order' => $order]);
        }

        return response()->json(['messages' => 'Urutan disimpan']);
    }
}

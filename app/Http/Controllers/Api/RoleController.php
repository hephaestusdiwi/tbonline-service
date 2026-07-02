<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::with('permissions')
            ->get()
            ->map(function ($role) {
                return [
                    'id'          => $role->id,
                    'name'        => $role->name,
                    'description' => $role->description ?? '',
                    'is_system'   => in_array($role->name, ['admin']),
                    'permissions' => $role->permissions->pluck('name'),
                    'users_count' => $role->users()->count(),
                ];
            });

        return response()->json($roles);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|unique:roles,name',
            'description'   => 'nullable|string',
            'permissions'   => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = Role::create([
            'name'          => $request->name,
            'guard_name'    => 'web',
            'description'   => $request->description,
        ]);

        if ($request->filled('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json($role->load('permissions'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $role = Role::with('permissions')->findOrFail($id);

        return response()->json([
            'id'            => $role->id,
            'name'          => $role->name,
            'description'   => $role->description ?? '',
            'is_system'     => in_array($role->name, ['admin']),
            'permissions'   => $role->permissions->pluck('name'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name'          => 'required|string|unique:roles,name,' . $role->id,
            'description'   => 'nullable|string',
            'permissions'   => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role->update([
            'name'          => $request->name,
            'description'   => $request->description,
        ]);

        $role->syncPermissions($request->permissions ?? []);

        return response()->json([
            'id'            => $role->id,
            'name'          => $role->name,
            'description'   => $role->description,
            'permissions'   => $role->permissions->pluck('name'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $role = Role::findOrFail($id);

        if (in_array($role->name, ['admin'])) {
            return response()->json(['message'  => 'Role system tidak bisa dihapus'], 403);
        }

        if ($role->users()->count() > 0) {
            return response()->json(['message' => 'Role masih digunakan oleh ' . $role->users()->count() . ' user.'], 422);
        }

        $role->delete();

        return response()->json(['message' => 'Role berhasil dihapus']);
    }


    public function permissions()
    {
        $compoundPrefixes = [
            'store_locator',
            'promo_codes',
            'homepage_sections',
            'footer_links',
            'visitor_stats',
            'settings_couriers',
        ];

        $permissions = Permission::all()->groupBy(function ($p) use ($compoundPrefixes) {
            $name = $p->name;

            if (str_contains($name, '.')) {
                return explode('.', $name)[0];
            }

            // Cocokkan prefix yang lebih panjang terlebih dahulu
            foreach ($compoundPrefixes as $prefix) {
                if (str_starts_with($name, $prefix . '_')) {
                    return $prefix;
                }
            }

            // Default: prefix sebelum underscore pertama
            return explode('_', $name)[0];
        })->map(fn($group) => $group->pluck('name'));

        return response()->json($permissions);
    }
}

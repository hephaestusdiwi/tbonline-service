<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'users_view', 'users_create', 'users_edit', 'users_delete',
            'roles_view', 'roles_create', 'roles_edit', 'roles_delete',
            'products_view', 'products_create', 'products_edit', 'products_delete',
            'orders_view', 'orders_update_status', 'orders_export', 'orders_delete',
            'reports_view', 'reports_export',
            'settings_view', 'settings_edit',

            // ── Chat ──────────────────────────────────────────────────────
            'chat_view',       // lihat sesi chat (semua role)
            'chat_manage',     // handle chat, online/offline, reply (staff & manager)
            'chat_admin',      // lihat queue, assign manual, laporan chat (admin & manager)
            'chat_close',      // tutup sesi chat
            'chat_reopen',     // buka kembali sesi yang closed
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Admin — semua permission termasuk chat
        $admin = Role::firstOrCreate([
            'name'          => 'admin',
            'guard_name'    => 'web',
            'description'   => 'Akses ke seluruh sistem',
        ]);
        $admin->syncPermissions($permissions);

        // Manager
        $manager = Role::firstOrCreate([
            'name'          => 'manager',
            'guard_name'    => 'web',
            'description'   => 'Mengelola operasional harian',
        ]);
        $manager->syncPermissions([
            'users_view',
            'products_view', 'products_create', 'products_edit',
            'orders_view', 'orders_update_status', 'orders_export',
            'reports_view',

            // chat
            'chat_view',
            'chat_manage',   // bisa handle chat langsung
            'chat_admin',    // bisa lihat queue & assign manual
            'chat_close',
            'chat_reopen',
        ]);

        // Staff
        $staff = Role::firstOrCreate([
            'name'          => 'staff',
            'guard_name'    => 'web',
            'description'   => 'Akses dasar operasional',
        ]);
        $staff->syncPermissions([
            'products_view',
            'orders_view', 'orders_update_status',

            // chat
            'chat_view',
            'chat_manage',   // handle chat yang di-assign ke mereka
            'chat_close',
        ]);
    }
}
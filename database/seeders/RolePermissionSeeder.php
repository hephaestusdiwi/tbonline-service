<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Permissions yang dikelola:
     *   content.viewAny | content.view | content.create
     *   content.update  | content.publish | content.delete
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Content (yang lama)
            'content.viewAny', 'content.view', 'content.create',
            'content.update', 'content.publish', 'content.delete',
            'content.restore', 'content.forceDelete',

            // Users
            'users_view', 'users_create', 'users_edit', 'users_delete',

            // Products
            'products_view', 'products_create', 'products_edit', 'products_delete',

            // Orders
            'orders_view', 'orders_create', 'orders_edit', 'orders_delete',
            'orders_update_status', 'orders_revise',

            // Roles
            'roles_view', 'roles_create', 'roles_edit', 'roles_delete',

            // Settings
            'settings_view', 'settings_edit',

            // Chat
            'chat_view', 'chat_close', 'chat_reopen', 'chat_manage', 'chat_admin',

            'orders_export',
            'reports_view',
            'reports_export',
            'orders_revise_price',
            'orders_revise_courier',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Admin: full access semua permission
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions($permissions);

        // Manager
        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $manager->syncPermissions([
            'content.viewAny', 'content.view', 'content.create', 'content.update', 'content.publish',
            'products_view', 'products_create', 'products_edit',
            'orders_view', 'orders_update_status', 'orders_revise',
            'settings_view',
            'chat_view', 'chat_close', 'chat_manage',
        ]);

        // Staff
        $staff = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);
        $staff->syncPermissions([
            'content.viewAny', 'content.view', 'content.create', 'content.update',
            'products_view',
            'orders_view',
            'chat_view',
        ]);

        $this->createDemoUser('Admin Demo',   'admin@demo.test',   'admin');
        $this->createDemoUser('Manager Demo', 'manager@demo.test', 'manager');
        $this->createDemoUser('Staff Demo',   'staff@demo.test',   'staff');

        $this->command->info('✅  Roles, permissions & demo users seeded.');
    }

    private function createDemoUser(string $name, string $email, string $role): void
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name'     => $name,
                'password' => Hash::make('password'),
            ]
        );

        $user->syncRoles([$role]);
    }
}
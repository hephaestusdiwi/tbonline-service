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

        $permissions = array_merge($permissions, [
            'sliders_view', 'sliders_create', 'sliders_edit', 'sliders_delete',
            'faq_view', 'faq_create', 'faq_edit', 'faq_delete',
            'announcement_view', 'announcement_create', 'announcement_edit', 'announcement_delete', 'announcement_reorder',
            'store_locator_view', 'store_locator_create', 'store_locator_edit', 'store_locator_delete',
            'import_view', 'import_run',
            'dashboard_view',
            'navigations_view', 'navigations_create', 'navigations_edit', 'navigations_delete',
            'branches_create', 'branches_edit', 'branches_delete', 'branches_bulk_delete',
            'promo_codes_view', 'promo_codes_create', 'promo_codes_edit', 'promo_codes_delete',
            'homepage_sections_view', 'homepage_sections_create', 'homepage_sections_edit',
            'homepage_sections_delete', 'homepage_sections_reorder',
            'footer_links_manage',
            'loyalty_manage',
            'promotions_view', 'promotions_create', 'promotions_edit', 'promotions_delete',
            'contents_view', 'contents_create', 'contents_edit', 'contents_delete', 'contents_publish',
            'media_upload',
            'visitor_stats_view',
            'complaints_view', 'complaints_manage',
            'settings_couriers_view', 'settings_couriers_edit',
        ]);

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
            'chat_close',
            'chat_reopen',
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
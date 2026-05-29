<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class OrderRevisionPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'orders_revise',
            'orders_revise_price',
            'orders_revise_courier',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // staff : bisa revisi item saja
        $staff = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);
        $staff->givePermissionTo(['orders_revise', 'orders_revise_price', 'orders_revise_courier']);


        // manager : bisa semua termasuk override harga
        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $manager->givePermissionTo(['orders_revise', 'orders_revise_price', 'orders_revise_courier']);

        // admin : sudah punya semua wildcard dan eksplisit
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->givePermissionTo(['orders_revise', 'orders_revise_price', 'orders_revise_courier']);

        $this->command->info('Order revision permissions seeded success');
    }
}

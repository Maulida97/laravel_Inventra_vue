<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Master Data (Item, Unit, Category, Department, Supplier)
            'master.view',
            'master.create',
            'master.update',
            'master.delete',
            
            // Items
            'item.view',
            'item.create',
            'item.update',
            'item.delete',

            // Warehouse & Locations
            'warehouse.view',
            'warehouse.create',
            'warehouse.update',
            'warehouse.delete',

            // Stock In
            'stock-in.view',
            'stock-in.create',

            // Stock Out
            'stock-out.view',
            'stock-out.create',
            'stock-out.cancel',

            // Stock Opname
            'stock-opname.view',
            'stock-opname.create',
            'stock-opname.approve',

            // Approvals
            'approval.view',
            'approval.approve',
            'approval.reject',

            // Audit
            'audit.view',

            // Settings & RBAC
            'setting.view',
            'setting.update',
            'role.view',
            'role.create',
            'role.update',
            'role.delete',
            'user.view',
            'user.create',
            'user.update',
            'user.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}

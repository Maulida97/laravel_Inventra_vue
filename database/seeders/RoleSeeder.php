<?php

/**
 * File: RoleSeeder.php
 * Module: RBAC
 * Layer: Seeder
 *
 * Purpose:
 * Mendefinisikan role dasar (SUPER_ADMIN, ADMIN, WAREHOUSE_MANAGER, 
 * WAREHOUSE_STAFF, VIEWER) dan memasangkannya dengan permission.
 *
 * Responsibilities:
 * - Membuat default roles jika belum ada.
 * - Melakukan mapping permissions ke roles.
 * - Men-generate akun dummy untuk keperluan testing.
 *
 * Related Documentation:
 * - docs/sprints/SPRINT-02-RBAC.md
 * - docs/07_PERMISSION_MATRIX.md
 */

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ============================================================
        // Initialization
        // ============================================================
        $allPermissions = Permission::all();

        // ============================================================
        // Roles Creation
        // ============================================================

        // 1. Super Admin
        $superAdmin = Role::firstOrCreate([
            'name' => 'SUPER_ADMIN',
            'description' => 'Super Administrator with all permissions',
        ]);
        $superAdmin->permissions()->sync($allPermissions->pluck('id'));

        // 2. Admin
        $adminRole = Role::firstOrCreate([
            'name' => 'ADMIN',
            'description' => 'Administrator with almost all permissions except system settings',
        ]);
        $adminPermissions = $allPermissions->filter(function ($permission) {
            return !in_array($permission->name, ['setting.update', 'role.delete']);
        });
        $adminRole->permissions()->sync($adminPermissions->pluck('id'));

        // 3. Warehouse Manager
        $warehouseManager = Role::firstOrCreate([
            'name' => 'WAREHOUSE_MANAGER',
            'description' => 'Warehouse Manager',
        ]);
        $managerPermissions = $allPermissions->filter(function ($permission) {
            return in_array(explode('.', $permission->name)[0], [
                'item', 'warehouse', 'stock-in', 'stock-out', 'stock-opname', 'approval'
            ]);
        });
        $warehouseManager->permissions()->sync($managerPermissions->pluck('id'));

        // 4. Warehouse Staff
        $warehouseStaff = Role::firstOrCreate([
            'name' => 'WAREHOUSE_STAFF',
            'description' => 'Warehouse Staff',
        ]);
        $staffPermissions = $allPermissions->filter(function ($permission) {
            return in_array($permission->name, [
                'item.view',
                'warehouse.view',
                'stock-in.view',
                'stock-in.create',
                'stock-out.view',
                'stock-out.create',
                'stock-opname.view',
                'stock-opname.create',
            ]);
        });
        $warehouseStaff->permissions()->sync($staffPermissions->pluck('id'));

        // 5. Viewer
        $viewer = Role::firstOrCreate([
            'name' => 'VIEWER',
            'description' => 'Read-only access',
        ]);
        $viewerPermissions = $allPermissions->filter(function ($permission) {
            return str_ends_with($permission->name, '.view');
        });
        $viewer->permissions()->sync($viewerPermissions->pluck('id'));

        // ============================================================
        // Dummy Users Assignment
        // ============================================================

        $usersToCreate = [
            [
                'name' => 'Super Administrator',
                'email' => 'admin@inventra.com',
                'role' => $superAdmin->id,
            ],
            [
                'name' => 'Super Administrator Backup',
                'email' => 'superadmin@inventra.com',
                'role' => $superAdmin->id,
            ],
            [
                'name' => 'Administrator',
                'email' => 'administrator@inventra.com',
                'role' => $adminRole->id,
            ],
            [
                'name' => 'Warehouse Manager',
                'email' => 'manager@inventra.com',
                'role' => $warehouseManager->id,
            ],
            [
                'name' => 'Warehouse Staff',
                'email' => 'staff@inventra.com',
                'role' => $warehouseStaff->id,
            ],
            [
                'name' => 'Viewer Only',
                'email' => 'viewer@inventra.com',
                'role' => $viewer->id,
            ]
        ];

        foreach ($usersToCreate as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => bcrypt('password'),
                ]
            );

            if (!$user->roles->contains($userData['role'])) {
                $user->roles()->attach($userData['role']);
            }
        }
    }
}

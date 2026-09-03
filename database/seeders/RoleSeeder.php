<?php

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
        // Get all permissions
        $allPermissions = Permission::all();

        // 1. Super Admin
        $superAdmin = Role::firstOrCreate([
            'name' => 'SUPER_ADMIN',
            'description' => 'Super Administrator with all permissions',
        ]);
        $superAdmin->permissions()->sync($allPermissions->pluck('id'));

        // 2. Admin
        $admin = Role::firstOrCreate([
            'name' => 'ADMIN',
            'description' => 'Administrator with almost all permissions except system settings',
        ]);
        $adminPermissions = $allPermissions->filter(function ($permission) {
            return !in_array($permission->name, ['setting.update', 'role.delete']);
        });
        $admin->permissions()->sync($adminPermissions->pluck('id'));

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

        // Assign default user to SUPER_ADMIN
        $defaultUser = User::where('email', 'admin@inventra.com')->first();
        if ($defaultUser) {
            if (!$defaultUser->hasRole('SUPER_ADMIN')) {
                $defaultUser->roles()->attach($superAdmin->id);
            }
        } else {
            // Create default user if not exists
            $defaultUser = User::factory()->create([
                'name' => 'Super Administrator',
                'email' => 'admin@inventra.com',
                'password' => bcrypt('password'),
            ]);
            $defaultUser->roles()->attach($superAdmin->id);
        }
    }
}

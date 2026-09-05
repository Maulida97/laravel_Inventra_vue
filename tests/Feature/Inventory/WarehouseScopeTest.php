<?php

namespace Tests\Feature\Inventory;

use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarehouseScopeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_super_admin_has_access_to_all_warehouses(): void
    {
        $superAdmin = User::where('email', 'admin@inventra.com')->first();
        $totalWarehousesCount = Warehouse::count();

        $accessibleCount = Warehouse::accessibleByUser($superAdmin)->count();

        $this->assertEquals($totalWarehousesCount, $accessibleCount);
        $this->assertTrue($superAdmin->hasWarehouseAccess(Warehouse::first()));
    }

    public function test_regular_user_can_only_access_assigned_warehouses(): void
    {
        $staffUser = User::factory()->create();
        $warehouse1 = Warehouse::first();
        $warehouse2 = Warehouse::skip(1)->first();

        // Assign only warehouse1 to staffUser
        $staffUser->warehouses()->sync([$warehouse1->id]);

        $accessibleWarehouses = Warehouse::accessibleByUser($staffUser)->get();

        $this->assertCount(1, $accessibleWarehouses);
        $this->assertEquals($warehouse1->id, $accessibleWarehouses->first()->id);

        $this->assertTrue($staffUser->hasWarehouseAccess($warehouse1));
        $this->assertFalse($staffUser->hasWarehouseAccess($warehouse2));
    }
}

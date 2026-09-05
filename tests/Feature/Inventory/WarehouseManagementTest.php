<?php

namespace Tests\Feature\Inventory;

use App\Models\Location;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\Inventory\WarehouseService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class WarehouseManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_user_can_view_warehouses_index(): void
    {
        $user = User::where('email', 'admin@inventra.com')->first();
        $response = $this->actingAs($user)->get(route('warehouses.index'));

        $response->assertStatus(200);
    }

    public function test_can_create_warehouse_and_assign_user_scope(): void
    {
        $admin = User::where('email', 'admin@inventra.com')->first();
        $staffUser = User::factory()->create();

        $data = [
            'code' => 'WH-TEST-99',
            'name' => 'Test Warehouse 99',
            'address' => 'Jl. Test No. 99',
            'status' => 'ACTIVE',
            'user_ids' => [$staffUser->id],
        ];

        $response = $this->actingAs($admin)->post(route('warehouses.store'), $data);
        $response->assertRedirect(route('warehouses.index'));

        $this->assertDatabaseHas('warehouses', [
            'code' => 'WH-TEST-99',
            'name' => 'Test Warehouse 99',
        ]);

        $warehouse = Warehouse::where('code', 'WH-TEST-99')->first();
        $this->assertCount(1, $warehouse->users);
        $this->assertEquals($staffUser->id, $warehouse->users->first()->id);
    }

    public function test_can_create_hierarchical_locations(): void
    {
        $admin = User::where('email', 'admin@inventra.com')->first();
        $warehouse = Warehouse::first();

        // 1. Create Parent Aisle
        $aisleResponse = $this->actingAs($admin)->post(route('locations.store'), [
            'warehouse_id' => $warehouse->id,
            'code' => 'TEST-AISLE-01',
            'name' => 'Test Aisle 01',
            'status' => 'ACTIVE',
        ]);
        $aisleResponse->assertRedirect(route('locations.index'));

        $aisle = Location::where('code', 'TEST-AISLE-01')->first();
        $this->assertNotNull($aisle);

        // 2. Create Child Rack under Aisle
        $rackResponse = $this->actingAs($admin)->post(route('locations.store'), [
            'warehouse_id' => $warehouse->id,
            'parent_id' => $aisle->id,
            'code' => 'TEST-RAK-01',
            'name' => 'Test Rak 01',
            'status' => 'ACTIVE',
        ]);
        $rackResponse->assertRedirect(route('locations.index'));

        $rack = Location::where('code', 'TEST-RAK-01')->first();
        $this->assertEquals($aisle->id, $rack->parent_id);
    }

    public function test_prevents_circular_reference_in_location_hierarchy(): void
    {
        $warehouse = Warehouse::first();
        $service = app(WarehouseService::class);

        $aisle = Location::create([
            'warehouse_id' => $warehouse->id,
            'code' => 'CIRCULAR-AISLE',
            'name' => 'Circular Aisle',
            'status' => 'ACTIVE',
        ]);

        $rack = Location::create([
            'warehouse_id' => $warehouse->id,
            'parent_id' => $aisle->id,
            'code' => 'CIRCULAR-RAK',
            'name' => 'Circular Rak',
            'status' => 'ACTIVE',
        ]);

        // Attempt to make aisle a child of rack (circular dependency)
        $this->expectException(InvalidArgumentException::class);
        $service->updateLocation($aisle, [
            'warehouse_id' => $warehouse->id,
            'parent_id' => $rack->id,
            'code' => 'CIRCULAR-AISLE',
            'name' => 'Circular Aisle',
            'status' => 'ACTIVE',
        ]);
    }
}

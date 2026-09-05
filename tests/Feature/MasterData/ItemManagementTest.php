<?php

namespace Tests\Feature\MasterData;

use App\Models\Category;
use App\Models\Department;
use App\Models\Item;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed RBAC and Master Data so permissions and admin user exist
        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_can_view_items_list(): void
    {
        $user = User::where('email', 'admin@inventra.com')->first();

        $response = $this->actingAs($user)->get(route('items.index'));

        $response->assertStatus(200);
    }

    public function test_admin_can_create_an_item_with_category_unit_and_department(): void
    {
        $user = User::where('email', 'admin@inventra.com')->first();
        $category = Category::first();
        $unit = Unit::first();
        $department1 = Department::first();
        $department2 = Department::skip(1)->first();

        $itemData = [
            'category_id' => $category->id,
            'code' => 'TEST-ITM-001',
            'sku' => 'SKU-001',
            'barcode' => '8990000001',
            'name' => 'Test Item',
            'description' => 'Test Description',
            'brand' => 'Test Brand',
            'item_type' => 'quantity',
            'base_unit_id' => $unit->id,
            'minimum_stock' => 10,
            'status' => 'ACTIVE',
            'department_ids' => [$department1->id, $department2->id]
        ];

        $response = $this->actingAs($user)->post(route('items.store'), $itemData);

        $response->assertRedirect(route('items.index'));
        $this->assertDatabaseHas('items', [
            'code' => 'TEST-ITM-001',
            'name' => 'Test Item',
            'minimum_stock' => 10,
            'status' => 'ACTIVE'
        ]);

        $item = Item::where('code', 'TEST-ITM-001')->first();
        $this->assertEquals($category->id, $item->category_id);
        $this->assertEquals($unit->id, $item->base_unit_id);
        $this->assertCount(2, $item->departments);
    }

    public function test_admin_can_update_an_item_and_its_departments(): void
    {
        $user = User::where('email', 'admin@inventra.com')->first();
        $item = Item::first();
        
        // Ensure initial sync is clean
        $item->departments()->sync([]);
        $this->assertCount(0, $item->departments);

        $department = Department::first();

        $updateData = [
            'category_id' => $item->category_id,
            'code' => $item->code,
            'name' => 'Updated Item Name',
            'item_type' => $item->item_type,
            'base_unit_id' => $item->base_unit_id,
            'minimum_stock' => 50, // updated
            'status' => 'INACTIVE', // updated
            'department_ids' => [$department->id] // updated
        ];

        $response = $this->actingAs($user)->put(route('items.update', $item->id), $updateData);
        $response->assertRedirect(route('items.index'));

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'name' => 'Updated Item Name',
            'minimum_stock' => 50,
            'status' => 'INACTIVE'
        ]);

        $item->refresh();
        $this->assertCount(1, $item->departments);
        $this->assertEquals($department->id, $item->departments->first()->id);
    }
}

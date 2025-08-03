<?php

namespace Tests\Feature\Api;

use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class InventoryApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(); // Seed the database
    }

    public function test_can_get_all_inventory_records()
    {
        $response = $this->getJson('/api/v1/inventory');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'product_id',
                        'product_name',
                        'quantity_change',
                        'type',
                        'reference',
                        'notes',
                        'unit_cost',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'links',
                'meta'
            ]);
    }

    public function test_can_get_inventory_by_id()
    {
        $inventory = Inventory::first();

        $response = $this->getJson("/api/v1/inventory/{$inventory->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $inventory->id,
                    'product_id' => $inventory->product_id,
                    'quantity_change' => $inventory->quantity_change,
                    'type' => $inventory->type
                ]
            ]);
    }

    public function test_can_filter_inventory_by_product()
    {
        $productId = Product::first()->id;

        $response = $this->getJson("/api/v1/inventory?product_id={$productId}");

        $response->assertStatus(200)
            ->assertJsonPath('data.0.product_id', $productId);
    }

    public function test_can_filter_inventory_by_type()
    {
        $type = Inventory::first()->type;

        $response = $this->getJson("/api/v1/inventory?type={$type}");

        $response->assertStatus(200)
            ->assertJsonPath('data.0.type', $type);
    }

    public function test_can_create_inventory_record()
    {
        $product = Product::first();
        $initialStock = $product->getCurrentStock();

        $inventoryData = [
            'product_id' => $product->id,
            'quantity_change' => -2,
            'type' => 'sale',
            'reference' => 'INV-' . $this->faker->randomNumber(5),
            'notes' => 'Sale to Customer ABC',
            'unit_cost' => $product->price
        ];

        $response = $this->postJson('/api/v1/inventory', $inventoryData);

        $response->assertStatus(201)
            ->assertJsonPath('data.product_id', $inventoryData['product_id'])
            ->assertJsonPath('data.quantity_change', $inventoryData['quantity_change'])
            ->assertJsonPath('data.type', $inventoryData['type']);

        $this->assertDatabaseHas('inventories', [
            'product_id' => $inventoryData['product_id'],
            'quantity_change' => $inventoryData['quantity_change'],
            'type' => $inventoryData['type']
        ]);

        // Verify that product stock is updated
        $updatedProduct = Product::find($product->id);
        $this->assertEquals($initialStock - 2, $updatedProduct->getCurrentStock());
    }

    public function test_cannot_create_inventory_with_invalid_product()
    {
        $invalidProductId = Product::max('id') + 1000; // Non-existent product ID

        $inventoryData = [
            'product_id' => $invalidProductId,
            'quantity_change' => 5,
            'type' => 'purchase',
            'reference' => 'PO-12345',
            'notes' => 'Purchase from supplier',
            'unit_cost' => 49.99
        ];

        $response = $this->postJson('/api/v1/inventory', $inventoryData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_id']);
    }

    public function test_cannot_reduce_inventory_below_zero()
    {
        $product = Product::first();
        $currentStock = $product->getCurrentStock();

        $inventoryData = [
            'product_id' => $product->id,
            'quantity_change' => -($currentStock + 10), // Try to reduce more than available
            'type' => 'sale',
            'reference' => 'INV-12345',
            'notes' => 'Sale to Customer XYZ',
            'unit_cost' => $product->price
        ];

        $response = $this->postJson('/api/v1/inventory', $inventoryData);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors'
            ]);
    }

    public function test_can_update_inventory_record()
    {
        $inventory = Inventory::where('type', 'initial')->first(); // Use an initial record for safety
        $product = Product::find($inventory->product_id);
        $initialStock = $product->getCurrentStock();

        $originalQuantity = $inventory->quantity_change;
        $newQuantity = $originalQuantity + 5; // Increase by 5

        $updatedData = [
            'quantity_change' => $newQuantity,
            'type' => $inventory->type,
            'reference' => $inventory->reference,
            'notes' => 'Updated inventory record',
            'unit_cost' => $inventory->unit_cost
        ];

        $response = $this->putJson("/api/v1/inventory/{$inventory->id}", $updatedData);

        $response->assertStatus(200)
            ->assertJsonPath('data.quantity_change', $newQuantity)
            ->assertJsonPath('data.notes', $updatedData['notes']);

        $this->assertDatabaseHas('inventories', [
            'id' => $inventory->id,
            'quantity_change' => $newQuantity,
            'notes' => $updatedData['notes']
        ]);

        // Verify that product stock is updated
        $updatedProduct = Product::find($product->id);
        $stockDifference = $newQuantity - $originalQuantity;
        $this->assertEquals($initialStock + $stockDifference, $updatedProduct->getCurrentStock());
    }

    public function test_can_delete_inventory_record()
    {
        // Create a test inventory record to delete
        $product = Product::first();
        $initialStock = $product->getCurrentStock();

        $inventory = Inventory::create([
            'product_id' => $product->id,
            'quantity_change' => 5,
            'type' => 'purchase',
            'reference' => 'TEST-DELETE',
            'notes' => 'Test record to be deleted',
            'unit_cost' => $product->cost
        ]);

        // Refresh product to get updated stock
        $product->refresh();
        $stockBeforeDelete = $product->getCurrentStock();

        $response = $this->deleteJson("/api/v1/inventory/{$inventory->id}");

        $response->assertStatus(204);

        $this->assertSoftDeleted('inventories', [
            'id' => $inventory->id
        ]);

        // Verify that product stock is updated after deletion
        $updatedProduct = Product::find($product->id);
        $this->assertEquals($stockBeforeDelete - 5, $updatedProduct->getCurrentStock());
    }

    public function test_can_get_inventory_valuation_report()
    {
        $response = $this->getJson("/api/v1/inventory-reports/valuation");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'product_id',
                        'product_name',
                        'sku',
                        'category',
                        'current_stock',
                        'average_cost',
                        'total_value'
                    ]
                ],
                'total_inventory_value'
            ]);
    }

    public function test_can_get_inventory_movements_report()
    {
        $product = Product::first();
        $dateFrom = date('Y-m-d', strtotime('-1 week'));
        $dateTo = date('Y-m-d');

        $response = $this->getJson("/api/v1/inventory-reports/movements?date_from={$dateFrom}&date_to={$dateTo}&product_id={$product->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'date',
                        'product_id',
                        'product_name',
                        'total_in',
                        'total_out',
                        'net_change',
                        'details' => [
                            '*' => [
                                'id',
                                'type',
                                'quantity_change',
                                'reference',
                                'notes',
                                'created_at'
                            ]
                        ]
                    ]
                ]
            ]);
    }
}

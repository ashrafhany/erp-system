<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(); // Seed the database
    }

    public function test_can_get_all_products()
    {
        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'sku',
                        'price',
                        'cost',
                        'category',
                        'tax_rate',
                        'status',
                        'image_url',
                        'current_stock',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'links',
                'meta'
            ]);
    }

    public function test_can_get_product_by_id()
    {
        $product = Product::first();

        $response = $this->getJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku
                ]
            ]);
    }

    public function test_can_filter_products_by_category()
    {
        $category = Product::first()->category;

        $response = $this->getJson("/api/v1/products?category={$category}");

        $response->assertStatus(200)
            ->assertJsonPath('data.0.category', $category);
    }

    public function test_can_search_products()
    {
        $product = Product::first();
        $searchTerm = substr($product->name, 0, 5); // Use part of the name for search

        $response = $this->getJson("/api/v1/products?search={$searchTerm}");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_create_product()
    {
        $productData = [
            'name' => 'Test Product',
            'description' => 'This is a test product',
            'sku' => $this->faker->unique()->regexify('[A-Z]{3}-[0-9]{4}'),
            'price' => 99.99,
            'cost' => 49.99,
            'category' => 'Test',
            'tax_rate' => 7.5,
            'status' => 'active',
            'image_url' => null,
            'initial_stock' => 10
        ];

        $response = $this->postJson('/api/v1/products', $productData);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', $productData['name'])
            ->assertJsonPath('data.sku', $productData['sku'])
            ->assertJsonPath('data.current_stock', $productData['initial_stock']);

        $this->assertDatabaseHas('products', [
            'name' => $productData['name'],
            'sku' => $productData['sku']
        ]);

        // Verify that an inventory record was created with the initial stock
        $this->assertDatabaseHas('inventories', [
            'product_id' => $response->json('data.id'),
            'quantity_change' => $productData['initial_stock'],
            'type' => 'initial'
        ]);
    }

    public function test_cannot_create_product_with_duplicate_sku()
    {
        $existingProduct = Product::first();

        $productData = [
            'name' => 'Duplicate SKU Product',
            'description' => 'This product has a duplicate SKU',
            'sku' => $existingProduct->sku, // Use existing SKU to cause validation error
            'price' => 99.99,
            'cost' => 49.99,
            'category' => 'Test',
            'tax_rate' => 7.5,
            'status' => 'active',
            'image_url' => null
        ];

        $response = $this->postJson('/api/v1/products', $productData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sku']);
    }

    public function test_can_update_product()
    {
        $product = Product::first();

        $updatedData = [
            'name' => 'Updated Product Name',
            'description' => 'This product has been updated',
            'sku' => $product->sku,
            'price' => 109.99,
            'cost' => 59.99,
            'category' => $product->category,
            'tax_rate' => $product->tax_rate,
            'status' => $product->status,
            'image_url' => $product->image_url
        ];

        $response = $this->putJson("/api/v1/products/{$product->id}", $updatedData);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', $updatedData['name'])
            ->assertJsonPath('data.price', $updatedData['price']);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => $updatedData['name'],
            'price' => $updatedData['price']
        ]);
    }

    public function test_can_delete_product()
    {
        // Create a product to delete
        $product = Product::factory()->create();

        $response = $this->deleteJson("/api/v1/products/{$product->id}");

        $response->assertStatus(204);

        $this->assertSoftDeleted('products', [
            'id' => $product->id
        ]);
    }

    public function test_can_get_product_inventory()
    {
        $product = Product::first();

        $response = $this->getJson("/api/v1/products/{$product->id}/inventory");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'product_id',
                        'quantity_change',
                        'type',
                        'reference',
                        'notes',
                        'unit_cost',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ]);
    }

    public function test_can_adjust_product_inventory()
    {
        $product = Product::first();
        $initialStock = $product->getCurrentStock();

        $adjustmentData = [
            'quantity_change' => 5,
            'type' => 'purchase',
            'reference' => 'PO-' . $this->faker->randomNumber(5),
            'notes' => 'Purchased from Supplier XYZ',
            'unit_cost' => $product->cost
        ];

        $response = $this->postJson("/api/v1/products/{$product->id}/inventory", $adjustmentData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'product_id',
                    'quantity_change',
                    'type',
                    'reference',
                    'notes',
                    'unit_cost',
                    'created_at',
                    'updated_at'
                ]
            ])
            ->assertJsonPath('data.quantity_change', $adjustmentData['quantity_change']);

        // Verify that product stock is updated
        $updatedProduct = Product::find($product->id);
        $this->assertEquals($initialStock + 5, $updatedProduct->getCurrentStock());
    }

    public function test_can_get_low_stock_products()
    {
        // Ensure we have a product with low stock
        $product = Product::first();
        $threshold = $product->getCurrentStock() + 5;

        $response = $this->getJson("/api/v1/products-low-stock?threshold={$threshold}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'sku',
                        'current_stock'
                    ]
                ]
            ]);
    }
}

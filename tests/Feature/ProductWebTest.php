<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Database\Seeders\WebAccessSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(WebAccessSeeder::class);
        $this->actingAs(User::findOrFail(1));
    }

    private function payload(array $overrides = []): array
    {
        return $overrides + [
            'name' => 'منتج تجريبي', 'sku' => 'SKU-1', 'category' => 'عام',
            'price' => 100, 'cost' => 60, 'tax_rate' => 14, 'status' => 'active',
        ];
    }

    public function test_product_crud_with_initial_stock(): void
    {
        $this->get('/products')->assertOk()->assertSee('المنتجات والمخزون');
        $this->post('/products', $this->payload(['initial_stock' => 25]))->assertRedirect();
        $product = Product::firstOrFail();
        $this->assertSame(25, $product->getCurrentStock());
        $this->assertDatabaseHas('inventories', ['product_id' => $product->id, 'type' => 'initial', 'quantity_change' => 25]);

        $this->post('/products', $this->payload())->assertSessionHasErrors('sku');
        $this->put('/products/'.$product->id, $this->payload(['name' => 'اسم جديد']))->assertRedirect('/products/'.$product->id);
        $this->assertSame('اسم جديد', $product->fresh()->name);
        $this->get('/products/'.$product->id)->assertOk()->assertSee('رصيد افتتاحي');
        $this->get('/products/'.$product->id.'/edit')->assertOk();

        $this->delete('/products/'.$product->id)->assertRedirect('/products');
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_inventory_movements_enforce_direction_and_never_go_negative(): void
    {
        $this->post('/products', $this->payload(['initial_stock' => 5]));
        $product = Product::firstOrFail();
        $url = '/products/'.$product->id.'/inventory';

        $this->post($url, ['type' => 'purchase', 'quantity_change' => 10])->assertSessionHasNoErrors();
        $this->assertSame(15, $product->getCurrentStock());

        $this->post($url, ['type' => 'purchase', 'quantity_change' => -1])->assertSessionHasErrors('quantity_change');
        $this->post($url, ['type' => 'sale', 'quantity_change' => 3])->assertSessionHasErrors('quantity_change');
        $this->post($url, ['type' => 'sale', 'quantity_change' => -16])->assertSessionHasErrors('quantity_change');
        $this->post($url, ['type' => 'adjustment', 'quantity_change' => -16])->assertSessionHasErrors('quantity_change');
        $this->assertSame(15, $product->getCurrentStock());

        $this->post($url, ['type' => 'sale', 'quantity_change' => -15])->assertSessionHasNoErrors();
        $this->assertSame(0, $product->getCurrentStock());
    }

    public function test_product_with_movements_cannot_be_deleted(): void
    {
        $this->post('/products', $this->payload(['initial_stock' => 5]));
        $product = Product::firstOrFail();
        $this->post('/products/'.$product->id.'/inventory', ['type' => 'sale', 'quantity_change' => -1]);

        $this->delete('/products/'.$product->id)->assertSessionHas('error');
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_low_stock_filter(): void
    {
        $this->post('/products', $this->payload(['name' => 'قليل', 'sku' => 'LOW', 'initial_stock' => 2]));
        $this->post('/products', $this->payload(['name' => 'كثير', 'sku' => 'HIGH', 'initial_stock' => 50]));
        $this->post('/products', $this->payload(['name' => 'بدون رصيد', 'sku' => 'NONE']));

        $this->get('/products?low_stock=1')->assertOk()->assertSee('قليل')->assertSee('بدون رصيد')->assertDontSee('كثير');
        $this->get('/products?search=HIGH')->assertOk()->assertSee('كثير')->assertDontSee('قليل');
    }

    public function test_permissions_are_enforced_per_action(): void
    {
        $role = Role::create(['name' => 'viewer', 'guard_name' => 'admin']);
        $role->givePermissionTo('products.view');
        $user = User::create(['name' => 'مشاهد', 'email' => 'v@example.com', 'password' => Hash::make('secret123')]);
        $user->assignRole($role);
        $product = Product::create($this->payload());

        $this->actingAs($user);
        $this->get('/products')->assertOk()->assertDontSee('إضافة منتج جديد');
        $this->get('/products/'.$product->id)->assertOk()->assertDontSee('تسجيل حركة مخزون');
        $this->get('/products/create')->assertForbidden();
        $this->post('/products/'.$product->id.'/inventory', ['type' => 'purchase', 'quantity_change' => 1])->assertForbidden();
        $this->delete('/products/'.$product->id)->assertForbidden();
    }
}

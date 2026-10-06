<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public const LOW_STOCK_THRESHOLD = 10;

    public function __construct()
    {
        $this->requireCrudPermissions('products');
        $this->middleware('permission:products.adjust')->only('adjustInventory');
    }

    public function index(Request $request)
    {
        $query = Product::withStock();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"));
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->boolean('low_stock')) {
            $query->lowStock(self::LOW_STOCK_THRESHOLD);
        }

        return view('products.index', [
            'products' => $query->orderBy('name')->paginate(15),
            'categories' => Product::distinct()->orderBy('category')->pluck('category'),
            'threshold' => self::LOW_STOCK_THRESHOLD,
        ]);
    }

    public function create()
    {
        return view('products.form', ['product' => new Product(['status' => 'active', 'tax_rate' => 0])]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules() + ['initial_stock' => ['nullable', 'integer', 'min:0']]);
        $initialStock = (int) ($data['initial_stock'] ?? 0);
        unset($data['initial_stock']);
        $data['tax_rate'] ??= 0;

        $product = DB::transaction(function () use ($data, $initialStock) {
            $product = Product::create($data);
            if ($initialStock > 0) {
                $product->inventoryRecords()->create([
                    'quantity_change' => $initialStock,
                    'type' => 'initial',
                    'notes' => 'رصيد افتتاحي',
                    'unit_cost' => $product->cost,
                ]);
            }
            return $product;
        });

        return redirect()->route('products.show', $product)->with('success', 'تمت إضافة المنتج.');
    }

    public function show(Product $product)
    {
        return view('products.show', [
            'product' => $product,
            'stock' => $product->getCurrentStock(),
            'movements' => $product->inventoryRecords()->latest('id')->paginate(15),
            'threshold' => self::LOW_STOCK_THRESHOLD,
        ]);
    }

    public function edit(Product $product)
    {
        return view('products.form', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate($this->rules($product));
        $data['tax_rate'] ??= 0;
        $product->update($data);

        return redirect()->route('products.show', $product)->with('success', 'تم تحديث المنتج.');
    }

    public function destroy(Product $product)
    {
        if ($product->inventoryRecords()->where('type', '!=', 'initial')->exists()) {
            return back()->with('error', 'لا يمكن حذف منتج له حركات مخزون. غيّر حالته إلى غير نشط بدلاً من الحذف.');
        }
        $product->delete();

        return redirect()->route('products.index')->with('success', 'تم حذف المنتج.');
    }

    public function adjustInventory(Request $request, Product $product)
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(['purchase', 'sale', 'return', 'adjustment'])],
            'quantity_change' => ['required', 'integer', 'not_in:0'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $quantity = (int) $data['quantity_change'];
        if (in_array($data['type'], ['purchase', 'return'], true) && $quantity < 0) {
            throw ValidationException::withMessages(['quantity_change' => 'الكمية يجب أن تكون موجبة لهذا النوع.']);
        }
        if ($data['type'] === 'sale' && $quantity > 0) {
            throw ValidationException::withMessages(['quantity_change' => 'الكمية يجب أن تكون سالبة لعملية البيع.']);
        }

        DB::transaction(function () use ($product, $data, $quantity) {
            Product::whereKey($product->id)->lockForUpdate()->firstOrFail();
            if ($product->getCurrentStock() + $quantity < 0) {
                throw ValidationException::withMessages(['quantity_change' => 'الرصيد الحالي لا يكفي لهذه العملية.']);
            }
            Inventory::create([
                'product_id' => $product->id,
                'quantity_change' => $quantity,
                'type' => $data['type'],
                'unit_cost' => $data['unit_cost'] ?? $product->cost,
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
        });

        return back()->with('success', 'تم تسجيل حركة المخزون.');
    }

    private function rules(?Product $product = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($product?->id)],
            'category' => ['required', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'cost' => ['required', 'numeric', 'min:0'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'description' => ['nullable', 'string'],
            'image_url' => ['nullable', 'url', 'max:255'],
        ];
    }
}

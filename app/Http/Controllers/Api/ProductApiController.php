<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Product;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class ProductApiController extends BaseApiController
{
    /**
     * Display a listing of the products.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::query();

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search by name or SKU
        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%');
            });
        }

        // Sort products
        $sortField = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $perPage = $request->input('per_page', 15);
        $products = $query->paginate($perPage);

        return $this->sendResponse($products, 'Products retrieved successfully');
    }

    /**
     * Store a newly created product in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:50|unique:products',
            'price' => 'required|numeric|min:0',
            'cost' => 'required|numeric|min:0',
            'category' => 'required|string|max:100',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'image_url' => 'nullable|url',
            'initial_stock' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        $product = new Product();
        $product->name = $request->name;
        $product->description = $request->description;
        $product->sku = $request->sku;
        $product->price = $request->price;
        $product->cost = $request->cost;
        $product->category = $request->category;
        $product->tax_rate = $request->tax_rate ?? 0;
        $product->status = $request->status;
        $product->image_url = $request->image_url;
        $product->save();

        // Add initial stock if provided
        if ($request->has('initial_stock') && $request->initial_stock > 0) {
            $inventory = new Inventory();
            $inventory->product_id = $product->id;
            $inventory->quantity_change = $request->initial_stock;
            $inventory->type = 'initial';
            $inventory->notes = 'Initial stock';
            $inventory->unit_cost = $request->cost;
            $inventory->save();
        }

        return $this->sendResponse($product, 'Product created successfully', 201);
    }

    /**
     * Display the specified product.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id): JsonResponse
    {
        $product = Product::findOrFail($id);

        // Include current stock information
        $product->current_stock = $product->getCurrentStock();

        return $this->sendResponse($product, 'Product retrieved successfully');
    }

    /**
     * Update the specified product in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:50|unique:products,sku,' . $id,
            'price' => 'required|numeric|min:0',
            'cost' => 'required|numeric|min:0',
            'category' => 'required|string|max:100',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'image_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        $product->name = $request->name;
        $product->description = $request->description;
        $product->sku = $request->sku;
        $product->price = $request->price;
        $product->cost = $request->cost;
        $product->category = $request->category;
        $product->tax_rate = $request->tax_rate ?? 0;
        $product->status = $request->status;
        $product->image_url = $request->image_url;
        $product->save();

        return $this->sendResponse($product, 'Product updated successfully');
    }

    /**
     * Remove the specified product from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $product = Product::findOrFail($id);

        // Delete related inventory records
        $product->inventoryRecords()->delete();

        // Delete the product
        $product->delete();

        return $this->sendResponse(null, 'Product deleted successfully', 204);
    }

    /**
     * Get inventory history for a product.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function inventory($id): JsonResponse
    {
        $product = Product::findOrFail($id);

        $inventoryRecords = $product->inventoryRecords()
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return $this->sendResponse([
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'current_stock' => $product->getCurrentStock(),
            ],
            'inventory' => $inventoryRecords
        ], 'Inventory records retrieved successfully');
    }

    /**
     * Adjust product inventory.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function adjustInventory(Request $request, $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'quantity_change' => 'required|integer|not_in:0',
            'type' => 'required|in:purchase,sale,adjustment,return',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'unit_cost' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        // Prevent negative stock unless it's a specific adjustment
        if ($request->type !== 'adjustment') {
            $newStock = $product->getCurrentStock() + $request->quantity_change;
            if ($newStock < 0) {
                return $this->sendError('Insufficient stock for this operation.', [], 422);
            }
        }

        $inventory = new Inventory();
        $inventory->product_id = $product->id;
        $inventory->quantity_change = $request->quantity_change;
        $inventory->type = $request->type;
        $inventory->reference = $request->reference;
        $inventory->notes = $request->notes;
        $inventory->unit_cost = $request->unit_cost ?? $product->cost;
        $inventory->save();

        return $this->sendResponse([
            'inventory_record' => $inventory,
            'current_stock' => $product->getCurrentStock(),
        ], 'Inventory adjusted successfully');
    }

    /**
     * Get low stock products.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function lowStock(Request $request): JsonResponse
    {
        $threshold = $request->input('threshold', 10);

        $lowStockProducts = Product::where('status', 'active')
            ->withCount(['inventoryRecords as stock' => function ($query) {
                $query->select(\DB::raw('SUM(quantity_change)'));
            }])
            ->having('stock', '<', $threshold)
            ->orderBy('stock', 'asc')
            ->paginate(15);

        return $this->sendResponse($lowStockProducts, 'Low stock products retrieved successfully');
    }
}

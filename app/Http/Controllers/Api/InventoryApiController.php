<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class InventoryApiController extends BaseApiController
{
    /**
     * Display a listing of the inventory records.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Inventory::with('product');

        // Filter by product
        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sort records
        $sortField = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $perPage = $request->input('per_page', 15);
        $inventoryRecords = $query->paginate($perPage);

        return $this->sendResponse($inventoryRecords, 'Inventory records retrieved successfully');
    }

    /**
     * Store a newly created inventory record in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity_change' => 'required|integer|not_in:0',
            'type' => 'required|in:purchase,sale,adjustment,return',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'unit_cost' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        $product = Product::findOrFail($request->product_id);

        // Prevent negative stock unless it's a specific adjustment
        if ($request->type !== 'adjustment') {
            $newStock = $product->getCurrentStock() + $request->quantity_change;
            if ($newStock < 0) {
                return $this->sendError('Insufficient stock for this operation.', [], 422);
            }
        }

        $inventory = new Inventory();
        $inventory->product_id = $request->product_id;
        $inventory->quantity_change = $request->quantity_change;
        $inventory->type = $request->type;
        $inventory->reference = $request->reference;
        $inventory->notes = $request->notes;
        $inventory->unit_cost = $request->unit_cost ?? $product->cost;
        $inventory->save();

        return $this->sendResponse([
            'inventory_record' => $inventory,
            'current_stock' => $product->getCurrentStock(),
        ], 'Inventory record created successfully', 201);
    }

    /**
     * Display the specified inventory record.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id): JsonResponse
    {
        $inventoryRecord = Inventory::with('product')->findOrFail($id);

        return $this->sendResponse($inventoryRecord, 'Inventory record retrieved successfully');
    }

    /**
     * Update the specified inventory record in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $inventoryRecord = Inventory::findOrFail($id);

        // Prevent editing old inventory records (e.g., older than 24 hours)
        $hoursAgo = now()->subHours(24);
        if ($inventoryRecord->created_at < $hoursAgo) {
            return $this->sendError('Cannot edit inventory records older than 24 hours.', [], 403);
        }

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

        $product = $inventoryRecord->product;

        // Calculate stock change and check if it would result in negative stock
        $originalChange = $inventoryRecord->quantity_change;
        $newChange = $request->quantity_change;
        $netChange = $newChange - $originalChange;

        if ($request->type !== 'adjustment') {
            $newStock = $product->getCurrentStock() + $netChange;
            if ($newStock < 0) {
                return $this->sendError('Insufficient stock for this operation.', [], 422);
            }
        }

        $inventoryRecord->quantity_change = $request->quantity_change;
        $inventoryRecord->type = $request->type;
        $inventoryRecord->reference = $request->reference;
        $inventoryRecord->notes = $request->notes;

        if ($request->has('unit_cost')) {
            $inventoryRecord->unit_cost = $request->unit_cost;
        }

        $inventoryRecord->save();

        return $this->sendResponse([
            'inventory_record' => $inventoryRecord,
            'current_stock' => $product->getCurrentStock(),
        ], 'Inventory record updated successfully');
    }

    /**
     * Remove the specified inventory record from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $inventoryRecord = Inventory::findOrFail($id);

        // Prevent deleting old inventory records (e.g., older than 24 hours)
        $hoursAgo = now()->subHours(24);
        if ($inventoryRecord->created_at < $hoursAgo) {
            return $this->sendError('Cannot delete inventory records older than 24 hours.', [], 403);
        }

        $product = $inventoryRecord->product;

        // Check if deleting this record would result in negative stock
        if ($inventoryRecord->quantity_change > 0) {
            $newStock = $product->getCurrentStock() - $inventoryRecord->quantity_change;
            if ($newStock < 0) {
                return $this->sendError('Cannot delete this record. It would result in negative stock.', [], 422);
            }
        }

        $inventoryRecord->delete();

        return $this->sendResponse([
            'current_stock' => $product->getCurrentStock(),
        ], 'Inventory record deleted successfully');
    }

    /**
     * Get inventory valuation report.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function valuation(): JsonResponse
    {
        $products = Product::where('status', 'active')->get();

        $valuationData = [];
        $totalValue = 0;

        foreach ($products as $product) {
            $stock = $product->getCurrentStock();
            $value = $stock * $product->cost;

            $valuationData[] = [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'stock' => $stock,
                'cost' => $product->cost,
                'value' => $value,
            ];

            $totalValue += $value;
        }

        return $this->sendResponse([
            'products' => $valuationData,
            'total_value' => $totalValue,
            'generated_at' => now(),
        ], 'Inventory valuation retrieved successfully');
    }

    /**
     * Get inventory movement report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function movements(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'product_id' => 'nullable|exists:products,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        $query = Inventory::with('product')
            ->whereDate('created_at', '>=', $request->date_from)
            ->whereDate('created_at', '<=', $request->date_to);

        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $movements = $query->orderBy('created_at', 'asc')->get();

        $summary = [
            'total_in' => $movements->where('quantity_change', '>', 0)->sum('quantity_change'),
            'total_out' => abs($movements->where('quantity_change', '<', 0)->sum('quantity_change')),
            'by_type' => [
                'purchase' => $movements->where('type', 'purchase')->sum('quantity_change'),
                'sale' => $movements->where('type', 'sale')->sum('quantity_change'),
                'adjustment' => $movements->where('type', 'adjustment')->sum('quantity_change'),
                'return' => $movements->where('type', 'return')->sum('quantity_change'),
            ],
        ];

        return $this->sendResponse([
            'movements' => $movements,
            'summary' => $summary,
            'period' => [
                'from' => $request->date_from,
                'to' => $request->date_to,
            ],
        ], 'Inventory movements retrieved successfully');
    }
}

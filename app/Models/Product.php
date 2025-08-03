<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'sku',
        'price',
        'cost',
        'category',
        'tax_rate',
        'status',
        'image_url',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'float',
        'cost' => 'float',
        'tax_rate' => 'float',
    ];

    /**
     * Get the inventory records for the product.
     */
    public function inventoryRecords(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    /**
     * Get the invoice items for the product.
     */
    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Get current stock level.
     *
     * @return int
     */
    public function getCurrentStock(): int
    {
        return $this->inventoryRecords()->sum('quantity_change');
    }

    /**
     * Check if product is in stock.
     *
     * @param int $quantity
     * @return bool
     */
    public function isInStock(int $quantity = 1): bool
    {
        return $this->getCurrentStock() >= $quantity;
    }
}

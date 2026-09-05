<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'code',
        'sku',
        'barcode',
        'name',
        'description',
        'brand',
        'item_type',
        'base_unit_id',
        'minimum_stock',
        'status',
    ];

    /**
     * Get the category that owns the item.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the base unit of the item.
     */
    public function baseUnit()
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    /**
     * Get the departments that are allowed to use this item.
     */
    public function departments()
    {
        return $this->belongsToMany(Department::class);
    }

    /**
     * Scope a query to search items by code, sku, barcode, or name.
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (!$search) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($search) {
            $q->where('code', 'ilike', "%{$search}%")
              ->orWhere('name', 'ilike', "%{$search}%")
              ->orWhere('sku', 'ilike', "%{$search}%")
              ->orWhere('barcode', 'ilike', "%{$search}%");
        });
    }
}

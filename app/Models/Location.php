<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id',
        'parent_id',
        'code',
        'name',
        'description',
        'status',
    ];

    /**
     * Get the warehouse owning this location.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the parent location.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'parent_id');
    }

    /**
     * Get child locations.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Location::class, 'parent_id');
    }

    /**
     * Recursive relationship for eager loading nested children.
     */
    public function allChildren(): HasMany
    {
        return $this->children()->with('allChildren');
    }

    /**
     * Scope search query by code or name.
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (!$search) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($search) {
            $q->where('code', 'ilike', "%{$search}%")
              ->orWhere('name', 'ilike', "%{$search}%");
        });
    }
}

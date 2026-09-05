<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'address',
        'status',
    ];

    /**
     * Get the locations associated with the warehouse.
     */
    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    /**
     * Get the users assigned to this warehouse.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Get the items assigned to this warehouse with primary_location_id.
     */
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'item_warehouse')
                    ->withPivot('primary_location_id')
                    ->withTimestamps();
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
              ->orWhere('name', 'ilike', "%{$search}%")
              ->orWhere('address', 'ilike', "%{$search}%");
        });
    }

    /**
     * Scope query to limit warehouses available to a specific user.
     * SUPER_ADMIN automatically sees all warehouses.
     */
    public function scopeAccessibleByUser(Builder $query, User $user): Builder
    {
        if ($user->hasRole('SUPER_ADMIN')) {
            return $query;
        }

        return $query->whereHas('users', function ($q) use ($user) {
            $q->where('users.id', $user->id);
        });
    }
}

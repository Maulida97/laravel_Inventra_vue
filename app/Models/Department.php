<?php

/**
 * File: Department.php
 * Module: Master Data
 * Layer: Model
 *
 * Purpose:
 * Merepresentasikan entitas Department di dalam Master Data.
 * 
 * Related Documentation:
 * - docs/sprints/SPRINT-03-MASTER-DATA.md
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'status',
    ];

    /**
     * Scope a query to search departments by code or name.
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


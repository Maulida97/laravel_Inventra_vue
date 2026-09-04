<?php

/**
 * File: Permission.php
 * Module: RBAC
 * Layer: Model
 *
 * Purpose:
 * Merepresentasikan Hak Akses terkecil (atomic action) dalam sistem.
 *
 * Responsibilities:
 * - Menyimpan definisi permission (misal: 'item.create', 'stock-in.view').
 * - Menghubungkan permission ke banyak roles (belongsToMany).
 *
 * Related Documentation:
 * - docs/sprints/SPRINT-02-RBAC.md
 * - docs/07_PERMISSION_MATRIX.md
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get the roles that belong to the permission.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }
}

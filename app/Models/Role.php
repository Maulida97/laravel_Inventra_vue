<?php

/**
 * File: Role.php
 * Module: RBAC
 * Layer: Model
 *
 * Purpose:
 * Merepresentasikan Role atau jabatan akses pengguna di dalam sistem.
 *
 * Responsibilities:
 * - Menyimpan definisi role (SUPER_ADMIN, dll).
 * - Menghubungkan role ke banyak permissions (belongsToMany).
 * - Menghubungkan role ke banyak users (belongsToMany).
 *
 * Related Documentation:
 * - docs/sprints/SPRINT-02-RBAC.md
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get the permissions that belong to the role.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    /**
     * Get the users that belong to the role.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}

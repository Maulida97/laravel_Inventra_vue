<?php

/**
 * File: CheckPermission.php
 * Module: RBAC
 * Layer: Middleware
 *
 * Purpose:
 * Melakukan pengecekan hak akses (permission) pada level rute (HTTP request).
 *
 * Responsibilities:
 * - Mengintersepsi request.
 * - Mengecek apakah authenticated user memiliki permission yang dibutuhkan.
 * - Mengembalikan respon 403 Forbidden jika tidak berhak.
 *
 * Related Documentation:
 * - docs/sprints/SPRINT-02-RBAC.md
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!$request->user() || !$request->user()->hasPermission($permission)) {
            abort(403, 'Anda tidak memiliki hak akses untuk tindakan ini.');
        }

        return $next($request);
    }
}

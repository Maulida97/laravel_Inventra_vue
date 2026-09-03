<?php
/*
 * Berkas: web.php
 * Jalur: routes/web.php
 * Tujuan: Mendefinisikan seluruh rute web utama aplikasi Inventra.
 * Digunakan untuk: Menangani rute halaman publik, dashboard otentikasi, dan rute pengelolaan profil pengguna.
 * Referensi: PRD & Struktur Arsitektur Modul Monolit
 */

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    // return Inertia::render('Welcome', [
    //     'canLogin' => Route::has('login'),
    //     'canRegister' => Route::has('register'),
    //     'laravelVersion' => Application::VERSION,
    //     'phpVersion' => PHP_VERSION,
    // ]);
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Master Data Routes
    Route::middleware('permission:master.view')->group(function () {
        Route::resource('departments', \App\Http\Controllers\MasterData\DepartmentController::class);
        Route::resource('categories', \App\Http\Controllers\MasterData\CategoryController::class);
        Route::resource('units', \App\Http\Controllers\MasterData\UnitController::class);
        Route::resource('suppliers', \App\Http\Controllers\MasterData\SupplierController::class);
    });
});

require __DIR__ . '/auth.php';

<?php

/**
 * File: 2026_09_04_000001_create_company_profiles_table.php
 * Module: Master Data / Settings
 * Layer: Database Migration
 *
 * Purpose:
 * Membuat tabel company_profiles untuk menyimpan identitas dan konfigurasi profil perusahaan.
 *
 * Related Documentation:
 * - docs/sprints/SPRINT-03-MASTER-DATA.md
 * - docs/LISTSPRINT.md
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('company_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->text('address')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('currency', 10)->default('IDR');
            $table->string('timezone', 50)->default('Asia/Jakarta');
            $table->tinyInteger('fiscal_year_start')->default(1);
            $table->string('logo_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_profiles');
    }
};

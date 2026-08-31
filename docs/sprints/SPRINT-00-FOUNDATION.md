# Inventra

## Sprint 00 — Foundation & Environment Setup

**Sprint:** SPRINT-00
**Name:** Foundation & Environment Setup
**Project:** Inventra
**Status:** Planned
**Branch:** `feature/fnd-001-project-setup`

---

# 1. Sprint Overview

Sprint ini bertujuan untuk membangun fondasi teknis dan menyiapkan lingkungan pengembangan proyek Inventra. Sprint ini sangat penting karena menyediakan infrastruktur dasar tempat seluruh fitur utama pada sprint-sprint berikutnya akan dibangun.

Flow dasar arsitektur:

```text
       Browser (Inertia SPA / Vue 3 / Tailwind CSS)
                           │
                           ▼
          Vite / Web Server (Routing & Asset)
                           │
                           ▼
             Laravel Backend Application
                           │
                           ▼
               PostgreSQL Database (Docker)
```

---

# 2. Objective

Membangun fondasi teknologi yang:
- Terintegrasi dengan lancar antara Laravel (Backend), Inertia.js (Bridge), dan Vue 3 (Frontend SPA).
- Terhubung ke database PostgreSQL yang berjalan di dalam Docker container.
- Mengadopsi tokens warna, tipografi, dan tata letak *Design System* resmi dari template `inventra_admin_dashboard.html`.
- Menerapkan arsitektur monolitik modular (*Modular Monolith*) sesuai standar arsitektur sistem.
- Menyiapkan standar logging dan error handling global.

---

# 3. Scope

### Included
```text
Laravel Core Project Setup
Inertia.js Server-side & Client-side Setup
Vue 3 & Vite Integration
Tailwind CSS Setup & Color/Font Configuration
PostgreSQL Docker Database Connection
Base Layouts (Authenticated & Guest layouts)
Base Routing Setup
Local Logging & Global Exception Handler base
Code Documentation Standard Integration
```

### Excluded (Will be built in SPRINT-01+)
```text
Session Authentication System
Access Control / RBAC Management
Application Master Data
Core Inventory Ledger Business Logic
REST API Endpoints
```

---

# 4. Technical Stack

Sistem fondasi dikembangkan menggunakan:

* **Backend**: Laravel 11/12 (PHP >= 8.2)
* **SPA Bridge**: Inertia.js (Inertia Vue adapter)
* **Frontend**: Vue.js 3 (Composition API, Script Setup), Vite, Tailwind CSS
* **Database**: PostgreSQL 17 (berjalan di Docker Container)
* **UI Base Tokens**: Plus Jakarta Sans, Custom Colors (Neutral #F5F6F8, Secondary #14324F, Accent #2563EB)

---

# 5. Database Connection

Konfigurasi koneksi PostgreSQL pada `.env` harus sinkron dengan database di Docker (`compose.yaml`):

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=inventory_management
DB_USERNAME=inventory_app
DB_PASSWORD=inventory_app_password
```

---

# 6. Directory Structure

Struktur direktori utama yang terbentuk setelah inisialisasi:

```text
laravel_Inventra_vue/
├── app/
│   ├── Http/
│   │   └── Middleware/
│   │       └── HandleInertiaRequests.php  # Inertia shared data
│   └── Providers/
│       └── AppServiceProvider.php
├── config/
│   └── database.php
├── database/
│   └── migrations/
├── docs/
│   └── sprints/
│       └── SPRINT-00-FOUNDATION.md
├── resources/
│   ├── css/
│   │   └── app.css                        # Tailwind directives
│   ├── js/
│   │   ├── Pages/                         # Vue Pages
│   │   │   └── Welcome.vue
│   │   ├── Layouts/                       # Vue Layouts
│   │   │   ├── AuthenticatedLayout.vue
│   │   │   └── GuestLayout.vue
│   │   └── app.js                         # Inertia client setup
│   └── views/
│       └── app.blade.php                  # Entry point HTML
├── compose.yaml                           # Docker Compose for PostgreSQL
└── vite.config.js                         # Vite config for Vue & Inertia
```

---

# 7. Verification Checklist

### Setup
- [ ] Inisialisasi kerangka kerja Laravel 11/12
- [ ] Konfigurasi `.env` untuk PostgreSQL database
- [ ] Install dependencies NPM (Inertia, Vue, Tailwind, Vite plugins)
- [ ] Konfigurasi `vite.config.js` dan Tailwind CSS tokens

### Layout & UI Setup
- [ ] Impor font Google "Plus Jakarta Sans"
- [ ] Daftarkan palet warna resmi (`bg`, `surface`, `secondary`, `accent`, `text`) ke `tailwind.config.js`
- [ ] Buat base layouts (`AuthenticatedLayout` dan `GuestLayout`)
- [ ] Buat halaman sambutan (`Welcome.vue`) yang menerapkan UI token

### Database & Testing
- [ ] Verifikasi migrasi database berhasil (`php artisan migrate`)
- [ ] Verifikasi kompilasi aset frontend berhasil (`npm run build`)
- [ ] Uji fungsionalitas rute Inertia dasar di browser

---

# 8. Definition of Done

Sprint 00 dianggap selesai jika:

```text
Code
    ✓ Kerangka Laravel, Inertia, Vue, dan Tailwind terintegrasi
    ✓ Database PostgreSQL terhubung dan migrasi awal berhasil dijalankan
    ✓ Desain token warna & font diterapkan di Tailwind Configuration

Git
    ✓ Seluruh perubahan di-commit dan didorong ke branch: staging
    ✓ Mengikuti penamaan branch: feature/fnd-001-project-setup
```

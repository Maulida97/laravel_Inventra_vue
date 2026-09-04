# GITHUB ISSUES STAGING — INVENTRA

Dokumen ini memetakan seluruh item backlog dan rencana pengerjaan ke dalam tiket **GitHub Issues** yang dinomori secara berurutan sesuai dengan strategi **8-Phase Sprint Roadmap**.

---

## MILESTONE 0: Phase 0 — Foundation Setup (Sprint 00)

### Issue #1: [FND-001] Setup Laravel Project & PostgreSQL Connection
* **Milestone**: Phase 0 — Foundation Setup
* **Labels**: `foundation`, `backend`, `P0`
* **User Story**: 
  > Sebagai developer, saya ingin menginisialisasi proyek Laravel dan menghubungkannya ke PostgreSQL agar saya memiliki framework backend yang berjalan stabil.
* **Tasks**:
  - [x] Buat proyek Laravel 11/12 di folder lokal.
  - [x] Konfigurasi `.env` untuk menghubungkan database ke container `inventory-postgres`.
  - [x] Uji fungsionalitas CLI database (`php artisan db:show`).
* **Acceptance Criteria**:
  - Aplikasi Laravel berjalan pada rute default di port `8000`.
  - Koneksi database terverifikasi sukses tanpa error.

### Issue #2: [FND-002] Configure Inertia.js, Vue 3, & Vite
* **Milestone**: Phase 0 — Foundation Setup
* **Labels**: `foundation`, `frontend`, `P0`
* **User Story**:
  > Sebagai user, saya ingin berpindah halaman secara cepat tanpa reload (Single Page Application) menggunakan antarmuka Vue 3 berbasis Inertia.js.
* **Tasks**:
  - [x] Install Inertia.js server-side adapter pada Laravel.
  - [x] Setup Inertia middleware `HandleInertiaRequests` pada rute web.
  - [x] Install NPM dependencies (`vue`, `@inertiajs/vue3`, `@vitejs/plugin-vue`).
  - [x] Konfigurasi `vite.config.js` untuk Vue 3 dan Inertia.
* **Acceptance Criteria**:
  - Kompilasi aset `npm run dev` & `npm run build` sukses.
  - Halaman dikirim secara asinkron lewat Inertia response.

### Issue #3: [FND-003] Setup Tailwind CSS with Custom Design Tokens
* **Milestone**: Phase 0 — Foundation Setup
* **Labels**: `foundation`, `frontend`, `styling`, `P0`
* **User Story**:
  > Sebagai frontend developer, saya ingin mengintegrasikan Tailwind CSS dengan token desain resmi agar tampilan konsisten.
* **Tasks**:
  - [x] Install Tailwind CSS dan buat file `tailwind.config.js`.
  - [x] Masukkan token warna (`bg`, `surface`, `secondary`, `accent`, `text`) dari template `inventra_admin_dashboard.html`.
  - [x] Impor font Google "Plus Jakarta Sans" dan tetapkan sebagai default font family.
* **Acceptance Criteria**:
  - Tailwind CSS terkompilasi melalui Vite.
  - Utilitas styling warna custom bekerja dengan benar pada template Vue.

### Issue #4: [FND-004] Create Base App Layout & Dashboard Welcome Page
* **Milestone**: Phase 0 — Foundation Setup
* **Labels**: `foundation`, `frontend`, `UI`, `P0`
* **User Story**:
  > Sebagai user, saya ingin melihat tata letak halaman yang seragam dan responsif sejak pertama kali membuka aplikasi.
* **Tasks**:
  - [x] Buat layout layout Guest (`GuestLayout.vue`) dan Authenticated (`AuthenticatedLayout.vue`).
  - [x] Buat halaman sambutan awal (`Welcome.vue`) yang bersih dan menggunakan token visual Inventra.
* **Acceptance Criteria**:
  - Base layout responsif di desktop maupun perangkat mobile.

---

## MILESTONE 1: Phase 1 — Identity & Access Control (Sprint 01 - 02)

### Issue #5: [AUTH-001] Implement Session-based Authentication
* **Milestone**: Phase 1 — Identity & Access Control
* **Labels**: `auth`, `backend`, `frontend`, `P0`
* **Tasks**:
  - [x] Setup skema user login, password hashing, dan session management.
  - [x] Buat halaman `Login.vue` berbasis Inertia.
  - [x] Buat Middleware pembatas rute terautentikasi dan login rate limiting.
  - [x] Implementasikan pencatatan audit log untuk kejadian login/logout.

### Issue #6: [RBAC-001] Setup Multi-level Role & Permission System
* **Milestone**: Phase 1 — Identity & Access Control
* **Labels**: `rbac`, `backend`, `P0`
* **Tasks**:
  - [x] Buat tabel `roles`, `permissions`, dan tabel relasinya.
  - [x] Buat fungsi otorisasi backend (Custom Policies / Gates).
  - [x] Impor matriks hak akses standard dari `07_PERMISSION_MATRIX.md`.

### Issue #7: [RBAC-002] Implement Scoped Data Authorization
* **Milestone**: Phase 1 — Identity & Access Control
* **Labels**: `rbac`, `backend`, `P0`
* **Tasks**:
  - [ ] Implementasikan konsep pembatasan data berdasarkan Department Scope, Warehouse Scope, dan Location Scope.
  - [ ] Buat query filter global helper untuk menyaring query model berbasis scope user saat ini.

---

## MILESTONE 2: Phase 2 — Master Data & Catalog (Sprint 03 - 05)

### Issue #8: [MST-001] Setup Master Data Management
* **Milestone**: Phase 2 — Master Data & Catalog
* **Labels**: `master-data`, `backend`, `frontend`, `P0`
* **Tasks**:
  - [x] Buat CRUD & validasi untuk Department, Category, Unit, dan Supplier.
  - [x] Implementasikan deaktivasi / status *is_active* (soft deletion) untuk master data.

### Issue #9: [ITEM-001] Implement Item Catalog & Unit Conversion Logic
* **Milestone**: Phase 2 — Master Data & Catalog
* **Labels**: `item`, `backend`, `frontend`, `P0`
* **Tasks**:
  - [ ] Buat CRUD & kode barang unik otomatis untuk katalog Item.
  - [ ] Setup tipe item (Quantity vs Serialized Asset).
  - [ ] Terapkan konversi unit dinamis di form transaksi (Content per Unit & Equivalent Qty).

### Issue #10: [WH-001] Implement Warehouse & Location Hierarchy Management
* **Milestone**: Phase 2 — Master Data & Catalog
* **Labels**: `warehouse`, `backend`, `frontend`, `P0`
* **Tasks**:
  - [ ] Buat CRUD Warehouse.
  - [ ] Buat manajemen hierarki lokasi (Warehouse -> Rack -> Shelf).
  - [ ] Batasi hak akses CRUD berdasarkan data scope wilayah user.

---

## MILESTONE 3: Phase 3 — Core Inventory & Ledger (Sprint 06 - 08)

### Issue #11: [INV-001] Implement Stock In & Inventory Ledger Posting
* **Milestone**: Phase 3 — Core Inventory & Ledger
* **Labels**: `inventory`, `ledger`, `P0`
* **Tasks**:
  - [ ] Buat transaksi Stock In (Draft -> Submitted -> Approved).
  - [ ] Implementasikan penulisan immutable ledger ke tabel `inventory_ledgers` saat approved.
  - [ ] Update saldo stok fisik real-time pada tabel `stock_balances`.

### Issue #12: [INV-002] Implement Stock Out with Inventory Balance Validation
* **Milestone**: Phase 3 — Core Inventory & Ledger
* **Labels**: `inventory`, `ledger`, `P0`
* **Tasks**:
  - [ ] Buat form transaksi Stock Out tujuan Departemen tertentu.
  - [ ] Buat validasi stok fisik real-time di backend (mencegah kuantitas minus).
  - [ ] Terapkan database row locking saat melakukan kalkulasi & update stok.

### Issue #13: [OPN-001] Implement Stock Opname & Auto-Adjustment Calculation
* **Milestone**: Phase 3 — Core Inventory & Ledger
* **Labels**: `inventory`, `opname`, `P0`
* **Tasks**:
  - [ ] Buat transaksi Stock Opname per lokasi fisik.
  - [ ] Hitung varian selisih kuantitas sistem dengan hitungan fisik secara otomatis.
  - [ ] Generate entry adjustment pada ledger secara otomatis saat disetujui.

---

## MILESTONE 4: Phase 4 — Asset & Governance (Sprint 09, 10, 20)

### Issue #14: [AST-001] Implement Serialized Asset Registration & Custody Assignment
* **Milestone**: Phase 4 — Asset & Governance
* **Labels**: `asset`, `P1`
* **Tasks**:
  - [ ] Registrasi barang non-consumable ke nomor seri / Asset Tag khusus.
  - [ ] Buat transaksi peminjaman (Assignment) dan pengembalian (Return) dari Custodian.
  - [ ] Catat riwayat aset secara berurutan di tabel `asset_histories`.

### Issue #15: [APR-001] Implement Multi-step Approval Workflow Engine
* **Milestone**: Phase 4 — Asset & Governance
* **Labels**: `approval`, `backend`, `P0`
* **Tasks**:
  - [ ] Bangun engine alur persetujuan bertingkat dinamis (Workflow steps).
  - [ ] Cegah self-approval (pembuat pengajuan menyetujui dokumen sendiri).
  - [ ] Integrasikan ke form pengajuan Stock Out, Stock Opname, dan Procurement.

### Issue #16: [PRC-001] Implement Procurement Lifecycle
* **Milestone**: Phase 4 — Asset & Governance
* **Labels**: `procurement`, `P0`
* **Tasks**:
  - [ ] Buat modul Purchase Request (PR) terikat pada departemen peminta.
  - [ ] Buat modul Purchase Order (PO) yang menunjuk supplier.
  - [ ] Buat formulir Penerimaan Barang (Receiving) terintegrasi otomatis ke mutasi masuk (Stock In).

---

## MILESTONE 5: Phase 5 — History, Analytics & Audit (Sprint 11 - 14)

### Issue #17: [TRX-001] Implement Unified Transaction History & Ledger Tracing
* **Milestone**: Phase 5 — History, Analytics & Audit
* **Labels**: `history`, `P0`
* **Tasks**:
  - [ ] Buat visualisasi mutasi logistik terpadu berdasarkan Reference Number.
  - [ ] Sediakan fitur pelacakan perjalanan barang dari asal-usul masuk hingga posisi saat ini.

### Issue #18: [REP-001] Implement Scope-based Operational Reporting
* **Milestone**: Phase 5 — History, Analytics & Audit
* **Labels**: `reporting`, `P1`
* **Tasks**:
  - [ ] Buat laporan stok, laporan pergerakan (fast/slow moving), dan laporan low-stock.
  - [ ] Wajib menyaring hasil laporan sesuai hak wilayah (scope) departemen / gudang pengguna.

### Issue #19: [DASH-001] Implement Role-based Summary Dashboards
* **Milestone**: Phase 5 — History, Analytics & Audit
* **Labels**: `dashboard`, `P1`
* **Tasks**:
  - [ ] Integrasikan design widget dari `inventra_admin_dashboard.html`.
  - [ ] Bedakan konten dashboard: Ringkasan operasional untuk gudang, persetujuan untuk manager, dan analisis makro untuk management.

### Issue #20: [AUD-001] Implement Detailed System Audit Logging
* **Milestone**: Phase 5 — History, Analytics & Audit
* **Labels**: `audit`, `P1`
* **Tasks**:
  - [ ] Gunakan middleware global untuk mencatat setiap aksi modifikasi model Eloquent.
  - [ ] Simpan payload *before* dan *after* modifikasi secara terperinci.

---

## MILESTONE 6: Phase 6 — REST API & Export (Sprint 15 - 16)

### Issue #21: [API-001] Implement Token-based REST API /api/v1 Endpoints
* **Milestone**: Phase 6 — REST API & Export
* **Labels**: `api`, `P1`
* **Tasks**:
  - [ ] Setup token bearer authentication (Laravel Sanctum/Passport).
  - [ ] Sediakan endpoints RESTful untuk Item, Stok, dan Riwayat Mutasi.

### Issue #22: [EXP-001] Implement Scope-based Excel/PDF Exporting
* **Milestone**: Phase 6 — REST API & Export
* **Labels**: `export`, `P1`
* **Tasks**:
  - [ ] Setup library PDF & Excel (Laravel Excel / DomPDF).
  - [ ] Buat fitur ekspor data laporan yang menghormati permission & scope pengguna.

---

## MILESTONE 7: Phase 7 — Hardening, QA & Release (Sprint 17 - 19)

### Issue #23: [SEC-001] Perform Security Hardening & Penetration Testing
* **Milestone**: Phase 7 — Hardening, QA & Release
* **Labels**: `security`, `P0`
* **Tasks**:
  - [ ] Cek celah manipulasi parameter ID (IDOR) pada endpoint edit data.
  - [ ] Setup limitasi laju request (Rate Limiting) pada seluruh API dan form input.
  - [ ] Validasi keamanan sanitasi upload dokumen/gambar.

### Issue #24: [QA-001] Write Automated Tests
* **Milestone**: Phase 7 — Hardening, QA & Release
* **Labels**: `testing`, `P0`
* **Tasks**:
  - [ ] Tulis unit tests kalkulasi ledger (mencegah bias pembulatan kuantitas).
  - [ ] Tulis feature tests pengujian RBAC rute dan menu.

### Issue #25: [DEP-001] Dockerize Production Build & Setup Deployment
* **Milestone**: Phase 7 — Hardening, QA & Release
* **Labels**: `deployment`, `P1`
* **Tasks**:
  - [ ] Buat file konfigurasi production Dockerfile & Docker Compose teroptimasi.
  - [ ] Jalankan seeder data dasar sistem siap pakai.

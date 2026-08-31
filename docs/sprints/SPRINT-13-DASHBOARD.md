# Inventra

## Sprint 13 — Dashboard

**Sprint:** SPRINT-13
**Name:** Dashboard
**Project:** Inventra
**Status:** Planned
**Branch:** `feature/dashboard`

---

# 1. Sprint Overview

Dashboard adalah halaman utama untuk memberikan gambaran cepat mengenai kondisi inventory, asset, dan aktivitas Inventra.

Dashboard berfungsi sebagai:

```text
Monitoring
Quick Overview
KPI
Operational Summary
```

Dashboard **tidak menjadi tempat utama untuk melakukan transaksi**.

---

# 2. Objective

Dashboard harus membantu user mengetahui:

```text
Berapa total item?
Berapa stock?
Berapa asset?
Apa yang baru terjadi?
Apakah ada stock rendah?
Bagaimana aktivitas inventory?
```

User tidak perlu membuka setiap module untuk mendapatkan gambaran umum.

---

# 3. Scope

### Included

```text
Dashboard Overview
Stock KPI
Asset KPI
Transaction KPI
Low Stock
Recent Transactions
Stock Movement Summary
Asset Status Summary
Warehouse Summary
Quick Navigation
```

### Not Included

```text
Advanced BI
Predictive Analytics
Forecasting
Real-time Streaming
Complex Charting
Custom Dashboard Builder
```

---

# 4. Dashboard Principle

Dashboard bersifat:

```text
READ ONLY
```

Flow:

```text
Database
   ↓
Dashboard Query / Service
   ↓
Summary
   ↓
Inertia
   ↓
Vue
```

Dashboard tidak boleh:

```text
Create Transaction
Update Stock
Delete Asset
Approve Transaction
```

---

# 5. Dashboard by Role

Dashboard dapat menampilkan informasi sesuai permission user.

Contoh:

```text
Admin
→ Global Overview

Warehouse Manager
→ Warehouse Overview

Warehouse Staff
→ Operational Overview
```

User hanya melihat data yang memang berada dalam scope-nya.

---

# 6. Main KPI

Minimal KPI:

```text
Total Items
Total Stock
Low Stock Items
Total Assets
Assigned Assets
Available Assets
Today's Transactions
```

Contoh:

```text
┌──────────────┐ ┌──────────────┐
│ Total Items  │ │ Total Stock  │
│     245      │ │    8,430     │
└──────────────┘ └──────────────┘

┌──────────────┐ ┌──────────────┐
│ Low Stock    │ │ Total Assets │
│      12      │ │     350      │
└──────────────┘ └──────────────┘
```

---

# 7. KPI Source

Setiap KPI harus mempunyai sumber data yang jelas.

```text
Total Items
→ Items

Total Stock
→ Inventory / Stock Balance

Low Stock
→ Inventory + Item minimum stock

Total Assets
→ Assets

Asset Status
→ Assets

Today's Transactions
→ Transaction History
```

---

# 8. Stock Overview

Menampilkan ringkasan stock:

```text
Total Stock
Stock In
Stock Out
Adjustment
Low Stock
```

Untuk periode tertentu:

```text
Today
This Week
This Month
```

---

# 9. Stock Movement Chart

Dashboard dapat menampilkan:

```text
Stock In
vs
Stock Out
```

Contoh:

```text
Quantity

500 │       █
400 │   █   █
300 │   █   █
200 │ █ █   █
100 │ █ █ █ █
    └────────────
      Mon Tue Wed
```

Data berasal dari:

```text
Inventory Ledger
```

---

# 10. Time Range

Default:

```text
This Month
```

Pilihan:

```text
Today
This Week
This Month
Last Month
Custom Range
```

Untuk KPI current-state seperti:

```text
Total Stock
Total Assets
```

periode tidak diperlukan.

---

# 11. Low Stock

Menampilkan item yang berada di bawah minimum stock.

Contoh:

```text
Item           Stock    Minimum
--------------------------------
Laptop            3        10
Mouse             5        20
Keyboard          2        10
```

Priority:

```text
Critical
Low
```

Business rule mengikuti module inventory/item management.

Dashboard hanya membaca hasilnya.

---

# 12. Low Stock Navigation

User dapat membuka item terkait.

Flow:

```text
Low Stock Item
      ↓
Click
      ↓
Item Detail
```

Dashboard tidak mengubah stock.

---

# 13. Recent Transactions

Menampilkan transaksi terbaru.

Contoh:

```text
TRX-001   STOCK_IN       Laptop     +20
TRX-002   STOCK_OUT      Mouse       -5
TRX-003   ADJUSTMENT     Monitor     -1
```

Default:

```text
Last 10 transactions
```

Sumber:

```text
Transaction History
```

---

# 14. Recent Activity

Dashboard dapat menampilkan aktivitas penting:

```text
Stock In
Stock Out
Stock Adjustment
Asset Assignment
Asset Transfer
Asset Return
```

Namun:

```text
Audit Log
```

tetap berada di module Audit Log.

Dashboard tidak menggantikan Audit Log.

---

# 15. Asset Overview

Menampilkan:

```text
Total Assets
Available
Assigned
Maintenance
Damaged
Lost
Disposed
```

Contoh:

```text
AVAILABLE       120
ASSIGNED         95
MAINTENANCE      10
DAMAGED           5
LOST              2
DISPOSED          8
```

Sumber:

```text
Assets
```

---

# 16. Asset Status Chart

Contoh:

```text
Asset Status

Available       ███████████
Assigned        █████████
Maintenance     ██
Damaged         █
Lost            ▏
Disposed        █
```

Chart hanya untuk visualisasi.

---

# 17. Warehouse Overview

Jika user memiliki akses beberapa warehouse:

```text
Warehouse       Stock
----------------------
WH-JKT           4500
WH-BDG           2200
WH-SBY           1730
```

Klik:

```text
WH-JKT
```

dapat membuka:

```text
Warehouse Detail
```

---

# 18. Warehouse Scope

Dashboard harus mengikuti warehouse scope.

Contoh:

```text
User
→ WH-JKT
```

Maka:

```text
Total Stock
```

hanya menghitung:

```text
WH-JKT
```

Bukan seluruh database.

---

# 19. Admin Dashboard

Admin dapat melihat:

```text
Global KPI
Warehouse Summary
Stock Summary
Asset Summary
Transaction Summary
```

sesuai permission.

---

# 20. Warehouse Dashboard

Warehouse user dapat melihat:

```text
Warehouse Stock
Low Stock
Recent Stock Transactions
Asset Summary
```

sesuai warehouse scope.

---

# 21. Dashboard Query Architecture

Jangan membuat satu query monster.

Hindari:

```text
DashboardController
    └── 500 lines SQL
```

Gunakan service terpisah:

```text
DashboardService
├── getKpis()
├── getStockMovement()
├── getLowStock()
├── getRecentTransactions()
├── getAssetSummary()
└── getWarehouseSummary()
```

---

# 22. Backend Structure

```text
app/
├── Http/
│   └── Controllers/
│       └── DashboardController.php
│
└── Services/
    └── Dashboard/
        ├── DashboardService.php
        ├── DashboardKpiService.php
        ├── StockDashboardService.php
        ├── AssetDashboardService.php
        └── TransactionDashboardService.php
```

---

# 23. Frontend Structure

```text
resources/js/
├── Pages/
│   └── Dashboard/
│       └── Index.vue
│
└── Components/
    └── Dashboard/
        ├── KpiCard.vue
        ├── StockOverview.vue
        ├── StockMovementChart.vue
        ├── LowStockTable.vue
        ├── RecentTransactions.vue
        ├── AssetOverview.vue
        └── WarehouseOverview.vue
```

---

# 24. Dashboard Controller

Flow:

```text
Request
   ↓
Authentication
   ↓
Authorization
   ↓
Warehouse Scope
   ↓
Dashboard Service
   ↓
Queries
   ↓
Inertia Response
```

Controller hanya menjadi orchestrator.

---

# 25. Performance

Dashboard dibuka sangat sering.

Karena itu query harus efisien.

Gunakan:

```text
Indexes
Aggregation
Select specific columns
Query scopes
Caching jika diperlukan
```

Hindari:

```text
SELECT *
```

dan:

```text
Load entire transaction history
```

---

# 26. Dashboard Query Strategy

Contoh Total Stock:

```text
SUM(current_stock)
```

Contoh Transaction Today:

```text
COUNT(*)
WHERE transaction_date = today
```

Contoh Low Stock:

```text
WHERE current_stock <= minimum_stock
```

Aggregation sebaiknya dilakukan di database.

---

# 27. N+1 Protection

Dashboard tidak boleh menghasilkan:

```text
1 query KPI
+
N query item
+
N query warehouse
+
N query user
```

Gunakan:

```text
Aggregation
Eager Loading
Join
Grouped Query
```

sesuai kebutuhan.

---

# 28. Caching

Dashboard dapat menggunakan caching untuk data yang mahal dihitung.

Contoh:

```text
Warehouse Summary
Asset Summary
Monthly Movement
```

Namun V1:

```text
Cache hanya jika profiling menunjukkan kebutuhan.
```

Jangan menambahkan caching kompleks tanpa alasan.

---

# 29. Cache Invalidation

Jika caching digunakan, perubahan data penting harus mempertimbangkan invalidation.

Contoh:

```text
Stock Out
 ↓
Stock berubah
 ↓
Dashboard Stock KPI
 ↓
Cache invalidated / refreshed
```

---

# 30. Loading State

Dashboard harus memiliki loading state.

Contoh:

```text
Loading KPI...
Loading Stock Movement...
Loading Recent Transactions...
```

Jangan menampilkan angka palsu seperti:

```text
0
```

ketika data sebenarnya masih loading.

---

# 31. Empty State

Jika belum ada data:

```text
No transactions yet.
```

bukan:

```text
Error
```

Contoh:

```text
No stock movement found
for selected period.
```

---

# 32. Error State

Jika salah satu widget gagal:

```text
Stock Movement
Unable to load data.
[Retry]
```

Tidak harus membuat seluruh dashboard gagal.

---

# 33. Responsive Design

Dashboard harus usable pada:

```text
Desktop
Tablet
Mobile
```

Layout:

```text
Desktop
→ Multi-column

Tablet
→ Reduced columns

Mobile
→ Stacked cards
```

---

# 34. Quick Actions

Dashboard dapat menyediakan shortcut:

```text
+ Stock In
+ Stock Out
+ Stock Opname
+ Add Asset
```

Namun button hanya muncul jika user mempunyai permission.

Contoh:

```text
stock-in.create
```

tanpa permission:

```text
Button tidak ditampilkan.
```

Backend authorization tetap wajib.

---

# 35. Dashboard Navigation

Contoh:

```text
Dashboard
    │
    ├── Stock
    │     ├── Stock Balance
    │     ├── Stock In
    │     └── Stock Out
    │
    ├── Assets
    │
    ├── Transactions
    │
    └── Reports
```

Dashboard menjadi entry point, bukan pengganti module.

---

# 36. Security

Dashboard harus dilindungi dari:

```text
Unauthorized Access
IDOR
Warehouse Scope Bypass
Data Leakage
Permission Bypass
```

Semua data harus difilter di backend.

---

# 37. Frontend Security Principle

Jangan hanya:

```text
v-if="user.isAdmin"
```

untuk menganggap data aman.

Frontend hanya mengontrol UI.

Security:

```text
Backend
→ Source of truth
```

---

# 38. Report Integration

Dashboard menggunakan data yang sama dengan Reporting.

```text
Dashboard
     │
     ├── KPI
     ├── Summary
     └── Quick Metrics
          │
          ▼
       Same Source
          │
          ▼
      Reporting Data
```

Namun Dashboard tidak perlu menggunakan halaman Report secara langsung.

---

# 39. Transaction Integration

Recent Transactions:

```text
Dashboard
 ↓
Transaction Query
 ↓
Transaction History
```

Jika user klik:

```text
TRX-001
```

navigasi ke:

```text
Transaction Detail
```

---

# 40. Stock Integration

Stock KPI:

```text
Dashboard
 ↓
Inventory
```

Stock movement:

```text
Dashboard
 ↓
Inventory Ledger
```

Jangan menghitung stock movement dari transaction history jika ledger merupakan source of truth untuk movement.

---

# 41. Asset Integration

Asset KPI:

```text
Dashboard
 ↓
Assets
```

Asset status:

```text
Dashboard
 ↓
Asset Status
```

---

# 42. Database Index Considerations

Query dashboard harus dievaluasi menggunakan:

```text
EXPLAIN
```

Index yang relevan dapat mencakup:

```text
inventory.warehouse_id
inventory.item_id
inventory.current_stock

inventory_ledger.warehouse_id
inventory_ledger.item_id
inventory_ledger.transaction_date

inventory_transactions.warehouse_id
inventory_transactions.transaction_date
inventory_transactions.transaction_type

assets.warehouse_id
assets.status
assets.condition
```

Index final mengikuti query aktual.

---

# 43. Testing

### KPI

```text
[ ] Total Items correct
[ ] Total Stock correct
[ ] Low Stock correct
[ ] Total Assets correct
[ ] Asset Status correct
[ ] Transaction count correct
```

### Scope

```text
[ ] Warehouse scope applied
[ ] Unauthorized data excluded
[ ] Admin can see allowed global data
```

### Widgets

```text
[ ] Recent transactions correct
[ ] Stock movement correct
[ ] Low stock correct
[ ] Asset summary correct
[ ] Warehouse summary correct
```

---

# 44. Performance Testing

```text
[ ] No N+1
[ ] Aggregation optimized
[ ] Pagination where required
[ ] Relevant indexes evaluated
[ ] EXPLAIN reviewed
```

Target awal:

```text
Dashboard initial load
→ < 2 seconds
```

pada dataset operasional normal.

Ini adalah engineering target, bukan production SLA.

---

# 45. Maintenance Guide

### "Saya ingin mengubah KPI."

Cari:

```text
DashboardKpiService.php
```

dan:

```text
KpiCard.vue
```

---

### "Angka stock salah."

Trace:

```text
KpiCard
 ↓
DashboardKpiService
 ↓
Inventory
```

---

### "Chart stock movement salah."

Trace:

```text
StockMovementChart.vue
 ↓
StockDashboardService
 ↓
Inventory Ledger
```

---

### "Recent transaction salah."

Trace:

```text
RecentTransactions.vue
 ↓
TransactionDashboardService
 ↓
Transaction History
```

---

### "User melihat data warehouse lain."

Trace:

```text
DashboardController
 ↓
Authorization
 ↓
Warehouse Scope
 ↓
Dashboard Service
 ↓
Query
```

---

# 46. Code Understanding Map

```text
Dashboard.vue
      ↓
Inertia
      ↓
DashboardController
      ↓
Authorization
      ↓
Warehouse Scope
      ↓
Dashboard Services
      ↓
Database Queries
      ↓
Inventory / Ledger / Transaction / Asset
```

Jika ingin memahami dashboard tanpa vibe coding:

```text
Start:
resources/js/Pages/Dashboard/Index.vue

↓
Cari data yang digunakan

↓
Lihat Controller

↓
Lihat Service

↓
Lihat Query

↓
Identifikasi Source of Truth
```

---

# 47. Code Documentation

Setiap file mengikuti:

```text
docs/code-guide/00_CODE_DOCUMENTATION_STANDARD.md
```

Contoh:

```php
/**
 * Dashboard KPI Service
 *
 * Purpose:
 * Generate dashboard KPI data.
 *
 * Data Sources:
 * - Items
 * - Inventory
 * - Assets
 * - Transactions
 *
 * Responsibility:
 * Read and aggregate dashboard data.
 *
 * Important:
 * This service must not mutate business data.
 *
 * Security:
 * All queries must respect the user's
 * warehouse scope.
 */
```

---

# 48. Acceptance Criteria

Sprint selesai apabila:

```text
1. Dashboard tersedia.

2. Dashboard dapat diakses berdasarkan permission.

3. KPI inventory tersedia.

4. KPI stock tersedia.

5. KPI asset tersedia.

6. KPI transaction tersedia.

7. Low Stock tersedia.

8. Recent Transactions tersedia.

9. Stock Movement Summary tersedia.

10. Asset Status Summary tersedia.

11. Warehouse Summary tersedia jika user memiliki akses.

12. Quick Actions tersedia sesuai permission.

13. Warehouse scope diterapkan.

14. IDOR protection tersedia.

15. Backend menjadi source of truth untuk authorization.

16. Dashboard bersifat read-only.

17. Query aggregation dilakukan secara efisien.

18. N+1 query dihindari.

19. Index relevan dievaluasi.

20. EXPLAIN digunakan untuk query penting.

21. Loading state tersedia.

22. Empty state tersedia.

23. Error state tersedia.

24. Responsive layout tersedia.

25. Automated tests berhasil.

26. Security tests berhasil.

27. Performance tests berhasil.

28. Code documentation mengikuti standard Inventra.

29. Developer dapat tracing Dashboard dari Vue → Laravel → Database.
```

---

# 49. Expected Files

```text
app/
├── Http/
│   └── Controllers/
│       └── DashboardController.php
│
└── Services/
    └── Dashboard/
        ├── DashboardService.php
        ├── DashboardKpiService.php
        ├── StockDashboardService.php
        ├── AssetDashboardService.php
        └── TransactionDashboardService.php

resources/js/
├── Pages/
│   └── Dashboard/
│       └── Index.vue
│
└── Components/
    └── Dashboard/
        ├── KpiCard.vue
        ├── StockOverview.vue
        ├── StockMovementChart.vue
        ├── LowStockTable.vue
        ├── RecentTransactions.vue
        ├── AssetOverview.vue
        └── WarehouseOverview.vue

tests/
└── Feature/
    └── Dashboard/
```

---

# 50. Git Branch

```text
feature/dashboard
```

Dependency:

```text
SPRINT-11 Transaction History
              ↓
SPRINT-12 Reporting
              ↓
SPRINT-13 Dashboard
```

Dashboard menggunakan data dari module yang sudah ada.

---

# 51. Suggested Commits

```text
feat(dashboard): add dashboard module
feat(dashboard): add dashboard layout
feat(dashboard): add inventory kpis
feat(dashboard): add stock kpis
feat(dashboard): add asset kpis
feat(dashboard): add transaction kpis
feat(dashboard): add low stock widget
feat(dashboard): add recent transactions widget
feat(dashboard): add stock movement widget
feat(dashboard): add asset status widget
feat(dashboard): add warehouse summary
feat(dashboard): add role-based dashboard access
feat(dashboard): add warehouse scope
feat(dashboard): add dashboard loading states
feat(dashboard): add dashboard empty states
feat(dashboard): add dashboard error states
perf(dashboard): optimize dashboard queries
perf(dashboard): add dashboard indexes
test(dashboard): add dashboard feature tests
test(dashboard): add dashboard security tests
docs(dashboard): document dashboard code flow
```

---

# 52. Definition of Done

```text
Dashboard
    ✓ KPI
    ✓ Stock Overview
    ✓ Low Stock
    ✓ Recent Transactions
    ✓ Stock Movement
    ✓ Asset Overview
    ✓ Warehouse Overview

Security
    ✓ RBAC
    ✓ Warehouse Scope
    ✓ IDOR Protection

Performance
    ✓ Aggregation
    ✓ No N+1
    ✓ Index Review
    ✓ EXPLAIN

UX
    ✓ Loading
    ✓ Empty State
    ✓ Error State
    ✓ Responsive

Testing
    ✓ Functional
    ✓ Security
    ✓ Performance

Documentation
    ✓ Code Comments
    ✓ Maintenance Guide
    ✓ Data Flow

Git
    ✓ feature/dashboard
```

---

# 53. Final Dashboard Architecture

```text
                    INVENTRA
                       │
                  DASHBOARD
                       │
        ┌──────────────┼──────────────┐
        ▼              ▼              ▼
      Stock          Asset        Transaction
        │              │              │
        ▼              ▼              ▼
   Inventory        Assets        History
   + Ledger
        │              │              │
        └──────────────┼──────────────┘
                       ▼
               Dashboard Services
                       │
                       ▼
                  Inertia + Vue
                       │
        ┌──────────────┼──────────────┐
        ▼              ▼              ▼
       KPI           Charts          Tables
```

---

# 54. Key Principle

Dashboard adalah **"cockpit" Inventra**.

```text
Module
→ melakukan pekerjaan

Ledger / Transaction
→ mencatat pekerjaan

Reporting
→ memberikan laporan detail

Dashboard
→ memberikan gambaran cepat
```

Jadi jangan membuat business logic baru di Dashboard.

```text
❌ Dashboard menghitung ulang stock
❌ Dashboard mengubah inventory
❌ Dashboard membuat transaction

✅ Dashboard membaca
✅ Dashboard merangkum
✅ Dashboard mengarahkan user ke module terkait
```

Dengan prinsip ini, ketika nanti ada masalah:

```text
"Angka dashboard stock salah"
```

kita tidak asal memperbaiki Dashboard.

Kita trace:

```text
Dashboard
   ↓
Dashboard Service
   ↓
Inventory / Ledger
   ↓
Business Transaction
```

dan menemukan **source of truth** yang sebenarnya.

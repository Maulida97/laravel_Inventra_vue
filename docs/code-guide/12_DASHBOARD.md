# Inventra

## Dashboard Code Guide

**Document:** Dashboard Code Guide
**Version:** V1.0
**Status:** Draft

---

# 1. Purpose

Dashboard memberikan ringkasan kondisi Inventra secara cepat.

Dashboard digunakan untuk:

- Melihat kondisi inventory.
- Melihat aktivitas transaksi.
- Melihat stock movement.
- Melihat alert.
- Melihat KPI.
- Melihat trend.
- Membantu user menentukan tindakan.

Dashboard bersifat **summary-oriented**, bukan pengganti report.

---

# 2. Dashboard vs Reporting

### Dashboard

```text
Summary
KPI
Chart
Alert
Trend
Quick Action
```

### Reporting

```text
Detailed Data
Filter
Search
Pagination
Export
```

Contoh:

```text
Dashboard

Total Items       1,250
Current Stock    15,800
Stock In           950
Stock Out          720
Low Stock           32
```

Sedangkan report:

```text
Item
Warehouse
Quantity
Transaction
Date
Reference
User
```

---

# 3. Dashboard Architecture

```text
Database
   ↓
Dashboard Query
   ↓
Aggregation
   ↓
Dashboard Service
   ↓
Controller
   ↓
Inertia
   ↓
Vue
   ↓
Cards / Charts / Alerts
```

---

# 4. Dashboard Principle

Dashboard harus:

```text
Fast
Simple
Actionable
Accurate
Role-aware
```

Jangan membuat dashboard menjadi halaman report yang penuh tabel.

---

# 5. Dashboard Structure

Concept:

```text
resources/js/Pages/
└── Dashboard.vue
```

Jika dashboard semakin kompleks:

```text
resources/js/Components/Dashboard/
├── KpiCard.vue
├── StockSummary.vue
├── StockMovementChart.vue
├── LowStockAlert.vue
├── RecentTransactions.vue
└── ActivitySummary.vue
```

---

# 6. Backend Structure

Concept:

```text
app/
├── Http/
│   └── Controllers/
│       └── DashboardController.php
│
└── Services/
    └── Dashboard/
        └── DashboardService.php
```

Jika query semakin kompleks:

```text
app/Queries/Dashboard/
├── InventorySummaryQuery.php
├── StockMovementQuery.php
└── ActivityQuery.php
```

Tidak perlu membuat semua file sejak awal.

---

# 7. Controller Responsibility

Controller hanya mengatur flow:

```text
Request
 ↓
Authorization
 ↓
DashboardService
 ↓
Inertia
```

Contoh concept:

```php id="8h0v8e"
public function index()
{
    $data = $this->dashboardService->getData();

    return Inertia::render(
        'Dashboard',
        $data
    );
}
```

Jangan menaruh seluruh query dashboard di controller.

---

# 8. Dashboard Service

DashboardService bertanggung jawab terhadap:

```text
KPI
Summary
Chart Data
Alerts
Recent Activity
```

Concept:

```text
DashboardService
├── getKpis()
├── getStockSummary()
├── getStockMovement()
├── getAlerts()
└── getRecentTransactions()
```

Atau satu orchestration method:

```text
getDashboardData()
```

yang memanggil query-query khusus.

---

# 9. KPI

KPI adalah angka penting yang ingin diketahui user dengan cepat.

Inventra dapat memiliki:

```text
Total Items
Current Stock
Stock In Today
Stock Out Today
Low Stock Items
Pending Approvals
Total Assets
```

Tidak semua KPI harus tampil untuk semua role.

---

# 10. KPI Calculation

Contoh:

```text
Current Stock
=
SUM(current_inventory.quantity)
```

Stock In:

```text
Stock In Today
=
SUM(stock_in.quantity)
```

Stock Out:

```text
Stock Out Today
=
SUM(stock_out.quantity)
```

Aggregation sebaiknya dilakukan di database.

---

# 11. Avoid PHP Aggregation

Jangan:

```text
Database
 ↓
ambil 100.000 rows
 ↓
PHP foreach
 ↓
SUM
```

Jika database bisa:

```sql
SUM(quantity)
```

gunakan database.

---

# 12. KPI Query

Contoh concept:

```php id="f7h5jt"
$totalItems = Item::query()
    ->where('is_active', true)
    ->count();
```

Current stock:

```php id="p74j8k"
$currentStock = InventoryBalance::query()
    ->sum('quantity');
```

Query harus tetap mempertimbangkan authorization/scope.

---

# 13. Role-Based Dashboard

Dashboard berbeda berdasarkan role.

Contoh:

```text
Admin
→ System-wide summary

Warehouse Staff
→ Warehouse summary

Supervisor
→ Operational summary

Auditor
→ Audit / activity summary
```

Jangan hanya menyembunyikan card di Vue.

Data yang tidak boleh diakses tidak boleh dikirim dari backend.

---

# 14. Dashboard Scope

Misalnya user hanya memiliki akses:

```text
WH-001
WH-002
```

maka:

```text
Current Stock
```

harus dihitung:

```text
WH-001 + WH-002
```

bukan seluruh warehouse.

---

# 15. Scope Flow

```text
Authenticated User
       ↓
Permission
       ↓
Warehouse Scope
       ↓
Dashboard Query
       ↓
KPI
```

---

# 16. Chart

Dashboard dapat menggunakan chart untuk:

```text
Stock Movement
Stock In vs Stock Out
Inventory Trend
Asset Status
Transaction Trend
```

Contoh:

```text
Date
 │
 │       ╭──╮
 │   ╭───╯  ╰──╮
 │───╯         ╰──
 └─────────────────
```

Chart hanya menampilkan data yang diperlukan.

---

# 17. Chart Data

Backend sebaiknya mengirim data terstruktur.

Contoh:

```json id="8k3jyi"
{
  "labels": ["Aug 01", "Aug 02", "Aug 03"],
  "datasets": [
    {
      "label": "Stock In",
      "data": [10, 20, 15]
    },
    {
      "label": "Stock Out",
      "data": [5, 12, 9]
    }
  ]
}
```

Vue bertanggung jawab terhadap visualisasi.

---

# 18. Backend vs Frontend Responsibility

Backend:

```text
Query
Aggregation
Filtering
Authorization
Data Preparation
```

Frontend:

```text
Display
Chart
Interaction
Loading
Empty State
Responsive Layout
```

Jangan memindahkan business calculation ke Vue hanya untuk mempermudah tampilan.

---

# 19. Date Range

Dashboard chart dapat menggunakan:

```text
Today
7 Days
30 Days
This Month
Custom
```

Default sebaiknya sederhana, misalnya:

```text
Last 7 Days
```

untuk trend operasional.

---

# 20. Dashboard Refresh

Dashboard dapat menggunakan:

```text
Manual Refresh
Page Refresh
Auto Refresh
```

Auto refresh hanya jika memang diperlukan.

Jangan melakukan polling terlalu agresif.

---

# 21. Real-Time Consideration

Tidak semua dashboard membutuhkan WebSocket.

Untuk V1:

```text
Request
 ↓
Query latest data
 ↓
Render
```

cukup.

Real-time dapat ditambahkan jika requirement memang membutuhkan.

---

# 22. Caching

Dashboard cocok menggunakan cache jika query mahal.

Contoh:

```text
Dashboard KPI
 ↓
Cache 30–60 seconds
```

Tetapi jangan cache data yang harus selalu real-time tanpa memahami konsekuensinya.

---

# 23. Cache Key

Jika dashboard bergantung pada user scope, cache harus memperhitungkan scope.

Jangan:

```text
dashboard:kpi
```

untuk semua user jika datanya berbeda.

Lebih aman secara konsep:

```text
dashboard:kpi:user:{id}:scope:{scope}
```

atau cache berdasarkan parameter yang menentukan data.

---

# 24. Cache Invalidation

Jika data inventory berubah sangat sering, pertimbangkan:

```text
Short TTL
```

daripada membuat invalidation logic yang terlalu kompleks.

V1:

```text
Simple cache
+
Short expiration
```

dapat lebih mudah dipelihara.

---

# 25. Alerts

Dashboard dapat menampilkan alert:

```text
Low Stock
Pending Approval
Expired Asset
Overdue Transaction
Stock Variance
```

Contoh:

```text
LOW STOCK

5 items below minimum stock
```

Alert harus memiliki link ke halaman detail yang relevan.

---

# 26. Low Stock

Concept:

```text
current_quantity <= minimum_stock
```

Contoh:

```text
Laptop
Current: 3
Minimum: 5

Status:
LOW STOCK
```

Query harus dilakukan di database.

---

# 27. Pending Approval Widget

Dashboard dapat menampilkan:

```text
Pending Approvals: 8
```

Tetapi jumlah harus hanya berasal dari request yang user memang boleh approve.

```text
User
 ↓
Approval Permission
 ↓
Approval Scope
 ↓
COUNT pending requests
```

---

# 28. Recent Transactions

Widget:

```text
Recent Transactions

SO-00021   Stock Out    10 mins ago
SI-00035   Stock In     15 mins ago
SO-00020   Stock Out    30 mins ago
```

Batasi jumlah:

```text
LIMIT 5
```

atau:

```text
LIMIT 10
```

Tidak perlu mengambil seluruh history.

---

# 29. Dashboard Query Optimization

Karena dashboard sering dibuka, query harus efisien.

Periksa:

```text
Index
Aggregation
Date filtering
JOIN
Selected columns
Cache
Number of queries
```

Hindari satu dashboard menghasilkan puluhan query yang tidak diperlukan.

---

# 30. Query Parallelization

Jika beberapa widget independen:

```text
KPI
Chart
Recent Transaction
Alert
```

secara konsep dapat diambil melalui query/service terpisah.

Namun jangan melakukan optimasi kompleks sebelum ada masalah performa.

Prioritas:

```text
Correctness
→ Simplicity
→ Measure
→ Optimize
```

---

# 31. N+1 Problem

Recent transactions:

```text
100 transactions
 ↓
100 query item
```

adalah N+1.

Gunakan eager loading:

```php id="y3j8i5"
Transaction::with([
    'item',
    'warehouse',
])
```

atau gunakan JOIN/query khusus jika lebih sesuai untuk read model.

---

# 32. Dashboard Loading State

Vue harus menangani:

```text
Loading
Loaded
Empty
Error
```

Contoh:

```text
Loading dashboard...
```

Jangan membuat halaman terasa rusak ketika query membutuhkan waktu.

---

# 33. Empty State

Contoh:

```text
No stock movement
for selected period.
```

atau:

```text
No pending approvals.
```

Empty state adalah kondisi normal, bukan error.

---

# 34. Error Handling

Jika salah satu widget gagal:

```text
Dashboard
├── KPI       ✓
├── Chart     ✓
├── Alerts    ✕
└── Recent    ✓
```

Idealnya seluruh dashboard tidak selalu harus gagal jika satu widget tidak tersedia.

Implementasi granular dapat digunakan jika kompleksitas memang diperlukan.

---

# 35. Security

Dashboard harus melindungi:

```text
Unauthorized KPI
Unauthorized warehouse data
Unauthorized asset data
Unauthorized transaction data
Sensitive information
```

Backend adalah security boundary.

---

# 36. Common Mistakes

### Mistake 1 — Semua data dikirim ke frontend

Dashboard bukan database viewer.

---

### Mistake 2 — Business logic di Vue

Contoh:

```javascript id="j4q53d"
totalStock = stockIn - stockOut;
```

jika perhitungan sebenarnya harus mengikuti inventory ledger/business rule.

Perhitungan authoritative sebaiknya berasal dari backend/domain logic.

---

### Mistake 3 — Cache tanpa scope

User warehouse A melihat data warehouse B.

---

### Mistake 4 — Query semua transaction

Dashboard hanya membutuhkan summary/recent data.

---

### Mistake 5 — Polling terlalu sering

Misalnya:

```text
Request setiap 1 detik
```

tanpa kebutuhan real-time.

---

### Mistake 6 — Terlalu banyak widget

Dashboard harus membantu user, bukan membuat informasi overload.

---

# 37. Maintenance Guide

### "Saya mau mengubah tampilan KPI."

Cari:

```text
resources/js/Components/Dashboard/
```

misalnya:

```text
KpiCard.vue
```

---

### "Saya mau mengubah angka KPI."

Cari:

```text
DashboardController
 ↓
DashboardService
 ↓
KPI Query
```

Perubahan calculation dilakukan di backend.

---

### "Saya mau menambah KPI baru."

Flow:

```text
Requirement
 ↓
Dashboard Query
 ↓
Dashboard Service
 ↓
Controller
 ↓
Inertia Props
 ↓
Dashboard Component
```

---

### "Saya mau mengubah chart."

Cari:

```text
resources/js/Components/Dashboard/
```

Kemudian periksa sumber datanya:

```text
DashboardService
 ↓
Chart Query
```

---

### "Chart menampilkan angka salah."

Periksa:

```text
[ ] Date range
[ ] Aggregation
[ ] Transaction type
[ ] Warehouse scope
[ ] Duplicate JOIN
[ ] Timezone
[ ] Source table
```

---

### "Dashboard lambat."

Periksa:

```text
[ ] Number of queries
[ ] N+1
[ ] Aggregation
[ ] Index
[ ] Date filter
[ ] Dataset size
[ ] Cache
[ ] EXPLAIN
```

---

### "User melihat KPI warehouse yang bukan miliknya."

Periksa:

```text
[ ] Permission
[ ] Policy
[ ] Warehouse scope
[ ] Dashboard query
[ ] Cache key
```

---

# 38. Code Reading Flow

Untuk memahami dashboard:

```text
Dashboard.vue
 ↓
Dashboard Component
 ↓
Inertia Props
 ↓
DashboardController
 ↓
DashboardService
 ↓
Query
 ↓
Database
```

Untuk memahami sebuah KPI:

```text
KpiCard.vue
 ↓
Prop
 ↓
DashboardController
 ↓
DashboardService
 ↓
KPI Query
 ↓
Database
```

Untuk memahami chart:

```text
Chart.vue
 ↓
Chart Props
 ↓
DashboardService
 ↓
Chart Query
 ↓
Aggregation
 ↓
Database
```

---

# 39. Debugging Checklist

Jika KPI salah:

```text
[ ] Source table
[ ] Query
[ ] Aggregation
[ ] Filter
[ ] Scope
[ ] Date range
[ ] Timezone
```

Jika chart kosong:

```text
[ ] Date range
[ ] Query result
[ ] Chart data structure
[ ] Vue props
[ ] Chart library
```

Jika dashboard lambat:

```text
[ ] Query count
[ ] N+1
[ ] Index
[ ] Aggregation
[ ] Cache
[ ] Dataset
```

Jika user melihat data yang salah:

```text
[ ] Authorization
[ ] Scope
[ ] Query
[ ] Cache
```

---

# 40. Testing

Minimal:

```text
[ ] Dashboard loads
[ ] KPI calculation correct
[ ] Chart data correct
[ ] Date filter works
[ ] Warehouse scope works
[ ] Role-based data works
[ ] Pending approval count correct
[ ] Low stock alert correct
[ ] Recent transaction correct
[ ] Empty state works
[ ] Error state works
[ ] Unauthorized data blocked
[ ] Cache does not leak data
```

---

# 41. Definition of Done

```text
[ ] KPI
[ ] Summary
[ ] Charts
[ ] Alerts
[ ] Recent transactions
[ ] Role-based visibility
[ ] Scope filtering
[ ] Query optimization
[ ] Loading state
[ ] Empty state
[ ] Error handling
[ ] Optional caching
[ ] Authorization
[ ] Tests
[ ] Documentation
```

---

# 42. Final Dashboard Architecture

```text
                         USER
                           │
                           ▼
                          VUE
                           │
                           ▼
                        INERTIA
                           │
                           ▼
                     DASHBOARD CONTROLLER
                           │
                    ┌──────┴──────┐
                    ▼             ▼
              AUTHORIZATION     SERVICE
                                  │
                 ┌────────────────┼────────────────┐
                 ▼                ▼                ▼
                KPI             CHART            ALERT
                 │                │                │
                 └────────────────┼────────────────┘
                                  ▼
                              QUERY LAYER
                                  │
                                  ▼
                               DATABASE
                                  │
                                  ▼
                            AGGREGATION
                                  │
                                  ▼
                            DASHBOARD DATA
                                  │
                                  ▼
                                INERTIA
                                  │
                                  ▼
                                  VUE
```

---

# 43. Key Principle

Dashboard Inventra mengikuti prinsip:

```text
QUERY
→ AGGREGATE
→ SUMMARIZE
→ AUTHORIZE
→ PRESENT
```

Untuk memahami kode:

```text
UI
 ↓
Inertia Props
 ↓
Controller
 ↓
Dashboard Service
 ↓
Query
 ↓
Database
```

Untuk mengubah tampilan:

```text
Vue Component
```

Untuk mengubah angka:

```text
Dashboard Service / Query
```

Untuk mengubah siapa yang dapat melihat data:

```text
Policy
+
Permission
+
Scope
```

Untuk mengatasi dashboard lambat:

```text
Query
→ EXPLAIN
→ Index
→ Aggregation
→ N+1
→ Cache
```

**Dashboard adalah read-only presentation layer yang mengambil data terotorisasi dari backend dan menyajikannya dalam bentuk ringkasan yang cepat dipahami.**

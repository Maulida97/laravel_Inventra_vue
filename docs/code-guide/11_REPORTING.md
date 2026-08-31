# Inventra

## Reporting Code Guide

**Document:** Reporting Code Guide
**Version:** V1.0
**Status:** Draft

---

# 1. Purpose

Reporting digunakan untuk menyajikan data Inventra menjadi informasi yang dapat digunakan untuk:

- Monitoring inventory.
- Melihat transaksi.
- Melihat stock movement.
- Melihat stock variance.
- Melihat asset.
- Melihat aktivitas user.
- Mendukung pengambilan keputusan.

Prinsip:

```text
Database
   ↓
Query
   ↓
Filter
   ↓
Aggregation
   ↓
Report Data
   ↓
Inertia
   ↓
Vue
   ↓
Report UI
```

---

# 2. Report vs Dashboard

Reporting:

```text
Detailed Data
+
Filter
+
Search
+
Date Range
+
Export
```

Dashboard:

```text
Summary
+
KPI
+
Chart
+
Quick Insight
```

Contoh:

```text
Report:
1000 transaksi stock movement

Dashboard:
Stock In
1,250

Stock Out
950

Current Stock
12,500
```

---

# 3. Reporting Responsibility

Reporting bertanggung jawab terhadap:

```text
Report Query
Filtering
Sorting
Aggregation
Pagination
Summary
Export Preparation
Authorization
Performance
```

Reporting **tidak boleh mengubah inventory atau asset**.

Report bersifat read-only.

---

# 4. Report Architecture

```text
                         DATABASE
                            │
                            ▼
                       QUERY BUILDER
                            │
                            ▼
                         FILTER
                            │
                            ▼
                       AGGREGATION
                            │
                            ▼
                       REPORT SERVICE
                            │
                            ▼
                       CONTROLLER
                            │
                            ▼
                         INERTIA
                            │
                            ▼
                           VUE
```

---

# 5. Report Categories

Minimal Inventra:

```text
Inventory Reports
Transaction Reports
Stock Opname Reports
Asset Reports
Audit Reports
```

Contoh:

```text
Inventory
→ Current Stock

Transaction
→ Stock Movement

Stock Opname
→ Variance Report

Asset
→ Asset Register

Audit
→ User Activity
```

---

# 6. Current Stock Report

Menampilkan kondisi stock saat ini.

Contoh:

```text
Item
Warehouse
Location
Quantity
Unit
```

Concept:

```text
Item A
WH-001
A-01
100 PCS
```

Query menggunakan current inventory balance, bukan menghitung ulang seluruh transaction history jika balance table tersedia.

---

# 7. Stock Movement Report

Menampilkan movement:

```text
Date
Item
Warehouse
Location
Type
Quantity
Reference
User
```

Contoh:

```text
2026-08-30
Laptop
WH-001
Stock In
+10
SI-00001
Budi
```

Source utama:

```text
Inventory Ledger
```

---

# 8. Stock Opname Report

Menampilkan:

```text
Opname Number
Warehouse
Item
System Quantity
Physical Quantity
Variance
Reason
Status
```

Contoh:

```text
SO-00001
Laptop
System: 100
Physical: 95
Variance: -5
Reason: Missing
```

---

# 9. Asset Report

Asset Register:

```text
Asset Code
Item
Serial Number
Status
Condition
Location
Department
Assigned User
```

Contoh:

```text
AST-IT-00001
Dell Latitude
SN123
ASSIGNED
GOOD
IT Office
IT
Budi
```

---

# 10. Audit Report

Menampilkan:

```text
Timestamp
User
Action
Module
Resource
Reference
```

Contoh:

```text
2026-08-30 10:20
Budi
CREATE
Stock Out
SO-00001
```

---

# 11. Reporting Service

Business/report query logic dapat ditempatkan di service.

Concept:

```text
app/Services/Reporting/
├── CurrentStockReportService.php
├── StockMovementReportService.php
├── StockOpnameReportService.php
├── AssetReportService.php
└── AuditReportService.php
```

Tidak harus membuat semua service jika report masih sedikit.

---

# 12. Controller Responsibility

Controller:

```text
Request
 ↓
Authorize
 ↓
Validate
 ↓
Report Service
 ↓
Inertia Response
```

Jangan membuat query report kompleks langsung di controller.

Tidak ideal:

```php
public function index()
{
    $data = DB::table(...)
        ->join(...)
        ->where(...)
        ->groupBy(...)
        ->orderBy(...)
        ->get();

    return Inertia::render(...);
}
```

Lebih baik:

```text
Controller
 ↓
ReportService
```

---

# 13. Query Layer

Jika query sangat kompleks, dapat dipisahkan:

```text
Report Service
 ↓
Report Query
 ↓
Database
```

Concept:

```text
app/Queries/Reports/
├── CurrentStockQuery.php
├── StockMovementQuery.php
└── AssetReportQuery.php
```

Gunakan pendekatan ini ketika query sudah cukup kompleks.

Jangan membuat abstraction berlebihan untuk query sederhana.

---

# 14. Filters

Report biasanya membutuhkan:

```text
Date From
Date To
Warehouse
Location
Item
Category
Status
User
Transaction Type
```

Contoh:

```text
Warehouse = WH-001
Date From = 2026-08-01
Date To = 2026-08-30
```

Backend harus menerima filter tersebut melalui validated request.

---

# 15. Filter Flow

```text
Vue
 ↓
Filter Form
 ↓
Inertia Request
 ↓
Controller
 ↓
Form Request
 ↓
Report Service
 ↓
Query
 ↓
Database
```

---

# 16. Dynamic Query

Gunakan conditional query.

Concept:

```php
$query->when(
    $filters['warehouse_id'] ?? null,
    fn ($query, $warehouseId) =>
        $query->where('warehouse_id', $warehouseId)
);
```

Dengan demikian filter hanya diterapkan jika digunakan.

---

# 17. Search

Search harus dibatasi pada field yang relevan.

Contoh:

```text
Item Name
SKU
Asset Code
Serial Number
Document Number
```

Jangan melakukan search ke seluruh kolom database tanpa alasan.

---

# 18. Sorting

Report dapat menyediakan:

```text
Date ASC
Date DESC
Quantity ASC
Quantity DESC
Item Name ASC
```

Backend harus melakukan whitelist.

Contoh konsep:

```php
$allowedSorts = [
    'created_at',
    'quantity',
    'item_name',
];
```

Jangan menerima nama column mentah dari user tanpa validasi.

---

# 19. Pagination

Report detail sebaiknya menggunakan pagination.

Contoh:

```text
Page 1
20 records

Page 2
20 records
```

Laravel:

```php
->paginate(20)
```

Jangan:

```php
->get()
```

untuk dataset besar jika seluruh data tidak diperlukan.

---

# 20. Pagination vs Export

UI:

```text
paginate(20)
```

Export:

```text
stream/chunk
```

Jangan memuat 500.000 record sekaligus ke memory hanya untuk export.

---

# 21. Aggregation

Report sering membutuhkan:

```text
COUNT
SUM
AVG
MIN
MAX
GROUP BY
```

Contoh:

```text
Total Stock Out
=
SUM(quantity)
```

atau:

```text
Transactions per Warehouse
=
COUNT(transaction)
GROUP BY warehouse
```

Aggregation sebaiknya dilakukan di database jika memungkinkan.

---

# 22. Database Aggregation

Lebih baik:

```sql
SELECT SUM(quantity)
FROM inventory_ledgers
WHERE ...
```

daripada:

```text
ambil semua record
 ↓
loop di PHP
 ↓
hitung SUM
```

Database lebih cocok untuk operasi aggregation.

---

# 23. N+1 Problem

Contoh buruk:

```text
Get 100 transactions

Transaction 1 → query item
Transaction 2 → query item
Transaction 3 → query item
...
```

Hasil:

```text
101 queries
```

Gunakan eager loading jika menggunakan Eloquent:

```php
Transaction::with([
    'item',
    'warehouse',
    'creator',
])
```

---

# 24. Query Optimization

Reporting sering menjadi bagian paling berat karena membaca banyak data.

Gunakan:

```text
Proper Index
Selective Columns
Filtering
Pagination
Aggregation
Eager Loading
Chunking
```

Jangan:

```text
SELECT *
```

jika hanya membutuhkan beberapa kolom.

---

# 25. Select Only Required Columns

Contoh:

```php
$query->select([
    'id',
    'item_id',
    'warehouse_id',
    'quantity',
    'created_at',
]);
```

Ini membantu mengurangi data yang dikirim dari database.

---

# 26. Index

Index mengikuti pola query.

Contoh Stock Movement:

```text
inventory_ledgers.item_id
inventory_ledgers.warehouse_id
inventory_ledgers.location_id
inventory_ledgers.created_at
inventory_ledgers.reference_type
inventory_ledgers.reference_id
```

Jika sering:

```text
warehouse_id
+
created_at
```

dapat dipertimbangkan composite index.

---

# 27. Composite Index

Contoh query:

```text
WHERE warehouse_id = ?
AND created_at BETWEEN ? AND ?
```

Index:

```text
(warehouse_id, created_at)
```

dapat lebih sesuai dibanding hanya:

```text
warehouse_id
```

atau:

```text
created_at
```

Keputusan final berdasarkan query pattern dan query plan.

---

# 28. EXPLAIN

Jika query lambat:

```text
EXPLAIN
```

digunakan untuk melihat bagaimana database menjalankan query.

Periksa:

```text
Index usage
Rows scanned
Join strategy
Sort
Temporary table
```

Jangan menambahkan index secara asal.

---

# 29. Date Filtering

Gunakan range date yang jelas.

Contoh:

```text
2026-08-01
sampai
2026-08-30
```

Backend harus menentukan apakah `to` bersifat inclusive atau exclusive.

Rekomendasi:

```text
from >= start
created_at < next_day(to)
```

untuk menghindari masalah timestamp.

---

# 30. Timezone

Timestamp disimpan dengan aturan yang konsisten.

Report kemudian menampilkan berdasarkan timezone aplikasi/user.

Contoh:

```text
Database
→ UTC

Application
→ Asia/Jakarta
```

Keputusan final mengikuti konfigurasi aplikasi Inventra.

---

# 31. Report Security

Report tetap membutuhkan authorization.

User tidak otomatis boleh melihat semua data.

Contoh:

```text
Warehouse Staff
→ Warehouse tertentu

Supervisor
→ Warehouse scope tertentu

Admin
→ All
```

Backend harus menerapkan scope.

---

# 32. Scope Filtering

Jangan hanya:

```php
where('warehouse_id', $request->warehouse_id)
```

Karena user dapat mengganti parameter.

Harus ada:

```text
Authenticated User
 ↓
Allowed Warehouse Scope
 ↓
Requested Warehouse
 ↓
Intersection
```

---

# 33. Export

Report dapat diexport:

```text
CSV
XLSX
PDF
```

Namun export adalah proses terpisah dari UI table.

```text
Report Query
 ↓
Export Formatter
 ↓
File
```

---

# 34. Export Architecture

```text
User
 ↓
Export Button
 ↓
Export Route
 ↓
Authorization
 ↓
Report Query
 ↓
Chunk
 ↓
Writer
 ↓
Download
```

Untuk dataset besar:

```text
Query
 ↓
Chunk
 ↓
Write
```

bukan:

```text
Query all
 ↓
Memory
 ↓
Generate
```

---

# 35. Export Filter Consistency

Jika user memilih:

```text
Warehouse = WH-001
Date = August
```

maka:

```text
View Report
```

dan:

```text
Export Report
```

harus menggunakan filter yang sama.

Idealnya keduanya menggunakan query/service yang sama.

---

# 36. Report DTO / Resource

Jika data report perlu format khusus:

```text
Report Query
 ↓
DTO / Resource
 ↓
Inertia
```

Contoh:

```text
{
    item_name,
    warehouse_name,
    quantity,
    formatted_date
}
```

Jangan mencampur formatting presentation terlalu banyak ke query database.

---

# 37. Frontend Structure

Concept:

```text
resources/js/Pages/Reports/
├── Index.vue
├── CurrentStock.vue
├── StockMovement.vue
├── StockOpname.vue
├── Assets.vue
└── Audit.vue
```

Components:

```text
resources/js/Components/Reports/
├── ReportFilters.vue
├── ReportTable.vue
├── ReportSummary.vue
├── ReportPagination.vue
└── ExportButton.vue
```

---

# 38. Report UI Flow

```text
Report Page
 ↓
Filter
 ↓
Submit
 ↓
Server Query
 ↓
Inertia Response
 ↓
Table Update
```

Inertia tidak berarti seluruh dataset harus diambil ke browser.

Server tetap dapat melakukan filtering/pagination.

---

# 39. Report Table

Table sebaiknya menangani:

```text
Column
Sorting
Pagination
Empty State
Loading State
```

Contoh:

```text
Item | Warehouse | Qty | Date
```

Jangan membuat table terlalu pintar jika logic sebenarnya berada di backend.

---

# 40. Empty State

Jika tidak ada data:

```text
No data found.
```

Bukan:

```text
500 Error
```

Contoh:

```text
Filter:
WH-001
August 2026

Result:
0 records
```

---

# 41. Report Caching

Report tertentu dapat menggunakan cache jika:

```text
Query expensive
Data tidak harus real-time
Request sangat sering
```

Contoh:

```text
Dashboard summary
```

Namun untuk:

```text
Current stock
```

harus dipertimbangkan kebutuhan real-time.

Jangan melakukan caching hanya karena query terlihat lambat.

---

# 42. Materialized / Summary Data

Jika dataset sangat besar, dapat menggunakan summary table.

Contoh:

```text
inventory_daily_summary
```

Daripada menghitung jutaan ledger setiap request.

Namun untuk V1 Inventra:

```text
Start simple
 ↓
Measure performance
 ↓
Optimize when needed
```

Jangan premature optimization.

---

# 43. Report Read Model

Jika reporting semakin kompleks:

```text
Transactional Database
        ↓
Read Model
        ↓
Reporting
```

Read model dapat dioptimalkan khusus untuk query laporan.

Ini bukan kebutuhan awal Inventra jika database masih manageable.

---

# 44. Read-Only Principle

Report tidak boleh:

```text
UPDATE
DELETE
INSERT
```

ke business data.

Report hanya:

```text
SELECT
```

kecuali proses khusus seperti membuat export job record.

---

# 45. Security

Perhatikan:

```text
SQL Injection
Unauthorized data access
IDOR
Mass data exposure
Sensitive information exposure
Export abuse
Large query abuse
```

Gunakan:

```text
Laravel Query Builder / Eloquent
Validated Filters
Authorization
Pagination
Scoped Queries
```

---

# 46. SQL Injection Protection

Jangan membuat query:

```php
DB::raw("ORDER BY {$request->sort}");
```

tanpa whitelist.

Gunakan:

```php
$allowedSorts = [
    'created_at',
    'quantity',
];

$sort = in_array(
    $request->sort,
    $allowedSorts,
    true
)
    ? $request->sort
    : 'created_at';
```

---

# 47. Report Permission

Concept:

```text
report.current-stock.view
report.stock-movement.view
report.stock-opname.view
report.asset.view
report.audit.view
```

Export:

```text
report.export
```

dapat dipisahkan jika diperlukan.

---

# 48. Common Mistakes

### Mistake 1 — Query besar di Controller

Pisahkan ke service/query.

---

### Mistake 2 — Ambil semua data

```php
->get()
```

untuk jutaan rows.

Gunakan pagination/chunking.

---

### Mistake 3 — Filtering hanya frontend

Data yang tidak boleh diakses jangan pernah dikirim ke browser hanya untuk kemudian disembunyikan.

---

### Mistake 4 — Tidak ada index

Report bekerja pada data besar tanpa index.

---

### Mistake 5 — Export memakai query berbeda

Hasil table dan export menjadi tidak sama.

---

### Mistake 6 — Caching tanpa memahami freshness

Report menunjukkan data lama tanpa disadari.

---

### Mistake 7 — Menghitung aggregation di PHP

Jika database dapat melakukan:

```text
SUM
COUNT
GROUP BY
```

gunakan database.

---

# 49. Maintenance Guide

### "Saya mau mengubah kolom report."

Cari:

```text
resources/js/Pages/Reports/
```

Kemudian cari sumber data:

```text
Controller
 ↓
Report Service
 ↓
Query
```

Tambahkan field di backend terlebih dahulu jika field belum tersedia.

---

### "Saya mau menambahkan filter warehouse."

Cari:

```text
ReportFilters.vue
```

kemudian:

```text
Form Request
 ↓
Report Service
 ↓
Query
```

Pastikan warehouse scope tetap diterapkan.

---

### "Report terlalu lambat."

Periksa:

```text
[ ] Query
[ ] EXPLAIN
[ ] Index
[ ] WHERE conditions
[ ] JOIN
[ ] N+1
[ ] Selected columns
[ ] Pagination
[ ] Aggregation
[ ] Dataset size
```

Jangan langsung menambahkan cache.

---

### "Export lambat / memory habis."

Periksa:

```text
[ ] get() terlalu besar
[ ] chunking
[ ] streaming
[ ] selected columns
[ ] export library
```

---

### "User bisa melihat data warehouse lain."

Periksa:

```text
[ ] Policy
[ ] Permission
[ ] Warehouse scope
[ ] Query scope
[ ] Export scope
```

Ini termasuk security issue.

---

# 50. Code Reading Flow

Untuk memahami report:

```text
Reports/StockMovement.vue
 ↓
Filter
 ↓
Route
 ↓
Controller
 ↓
Form Request
 ↓
Report Service
 ↓
Query
 ↓
Database
 ↓
Inertia Response
 ↓
Vue Table
```

Untuk memahami report lambat:

```text
Vue
 ↓
Controller
 ↓
Service
 ↓
Query
 ↓
SQL
 ↓
EXPLAIN
 ↓
Index
```

Untuk memahami export:

```text
Export Button
 ↓
Export Route
 ↓
Authorization
 ↓
Report Service
 ↓
Query
 ↓
Chunk
 ↓
Writer
 ↓
File
```

---

# 51. Debugging Checklist

Jika report kosong:

```text
[ ] Filter
[ ] Date range
[ ] Warehouse scope
[ ] Query condition
[ ] Relationship
[ ] Database data
```

Jika angka salah:

```text
[ ] Source table
[ ] Aggregation
[ ] Duplicate JOIN
[ ] Date range
[ ] Transaction type
[ ] Unit conversion
```

Jika report lambat:

```text
[ ] EXPLAIN
[ ] Index
[ ] JOIN
[ ] N+1
[ ] Pagination
[ ] Aggregation
[ ] Dataset size
```

Jika export berbeda dengan table:

```text
[ ] Same filters
[ ] Same query
[ ] Same authorization scope
[ ] Same date range
```

---

# 52. Testing

Minimal:

```text
[ ] Report loads
[ ] Empty result works
[ ] Filter works
[ ] Date filter works
[ ] Warehouse filter works
[ ] Search works
[ ] Sorting works
[ ] Pagination works
[ ] Aggregation correct
[ ] No duplicate rows
[ ] Authorization enforced
[ ] Warehouse scope enforced
[ ] Export uses same filters
[ ] Large dataset handled
[ ] SQL injection protected
```

---

# 53. Definition of Done

```text
[ ] Report query
[ ] Filters
[ ] Search
[ ] Sorting
[ ] Pagination
[ ] Aggregation
[ ] Authorization
[ ] Warehouse / scope filtering
[ ] Query optimization
[ ] Index review
[ ] Export
[ ] Empty state
[ ] Error handling
[ ] Tests
[ ] Documentation
```

---

# 54. Final Reporting Architecture

```text
                         USER
                           │
                           ▼
                          VUE
                           │
                    FILTER / SEARCH
                           │
                           ▼
                        INERTIA
                           │
                           ▼
                       CONTROLLER
                           │
                    ┌──────┴──────┐
                    ▼             ▼
                VALIDATION    AUTHORIZATION
                                  │
                                  ▼
                            REPORT SERVICE
                                  │
                                  ▼
                              QUERY LAYER
                                  │
                                  ▼
                               DATABASE
                                  │
                       ┌──────────┴──────────┐
                       ▼                     ▼
                    RESULTS              AGGREGATION
                       │                     │
                       └──────────┬──────────┘
                                  ▼
                             PAGINATION
                                  │
                                  ▼
                               INERTIA
                                  │
                                  ▼
                                VUE
                                  │
                         ┌────────┴────────┐
                         ▼                 ▼
                       TABLE            EXPORT
```

---

# 55. Key Principle

Reporting di Inventra mengikuti prinsip:

```text
READ
→ FILTER
→ QUERY
→ AGGREGATE
→ PRESENT
→ EXPORT
```

Bukan:

```text
Report
→ Modify Inventory
```

Untuk memahami kode report, gunakan urutan:

```text
UI
 ↓
Route
 ↓
Controller
 ↓
Authorization
 ↓
Report Service
 ↓
Query
 ↓
Database
```

Sedangkan ketika report lambat:

```text
Query
 ↓
EXPLAIN
 ↓
Index
 ↓
JOIN
 ↓
Aggregation
 ↓
Pagination
```

Dan prinsip paling penting:

> **Report harus membaca source of truth yang sama dengan sistem transaksi, tetapi tidak boleh mengubah source of truth tersebut.**

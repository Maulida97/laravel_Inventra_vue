# Inventra

## Sprint 12 — Reporting

**Sprint:** SPRINT-12
**Name:** Reporting
**Project:** Inventra
**Status:** Planned
**Branch:** `feature/reporting`

---

# 1. Sprint Overview

Reporting menyediakan laporan operasional dari data Inventra.

Contoh:

```text id="rpt001"
Stock Movement
Stock In
Stock Out
Stock Adjustment
Stock Balance
Asset
Transaction
```

Reporting **tidak membuat data bisnis baru**.

```text id="rpt002"
Business Data
     ↓
Ledger / Transaction / Asset
     ↓
Reporting
```

---

# 2. Objective

Reporting harus membantu user menjawab:

```text id="rpt003"
Berapa stock saat ini?
Apa saja transaksi periode tertentu?
Berapa stock masuk?
Berapa stock keluar?
Item apa yang paling banyak bergerak?
Asset apa yang dimiliki?
Asset berada di mana?
```

---

# 3. Scope

### Included

```text id="rpt004"
Stock Balance Report
Stock Movement Report
Stock In Report
Stock Out Report
Stock Adjustment Report
Asset Report
Transaction Report
Filtering
Date Range
Warehouse Filter
Item Filter
Export-ready Dataset
```

### Not Included

```text id="rpt005"
Advanced BI
Predictive Analytics
Forecasting
Financial Accounting
Data Warehouse
Complex Visualization
Scheduled Reports
```

Export akan diimplementasikan lebih lengkap pada:

```text id="rpt006"
SPRINT-16-EXPORT
```

---

# 4. Reporting Principle

Reporting adalah **read-only**.

```text id="rpt007"
Report
 ↓
Read Data
 ↓
Aggregate
 ↓
Display
```

Report tidak boleh:

```text id="rpt008"
Update Stock
Create Transaction
Delete Transaction
Change Asset
```

---

# 5. Data Sources

Reporting menggunakan:

```text id="rpt009"
Inventory
Inventory Ledger
Transaction History
Assets
Warehouses
Items
Users
```

Sumber utama harus dipilih berdasarkan jenis report.

---

# 6. Report vs Dashboard

### Reporting

Digunakan untuk:

```text id="rpt010"
Detail
Filtering
Date Range
Operational Review
Export
```

### Dashboard

Digunakan untuk:

```text id="rpt011"
Summary
KPI
Quick Overview
Monitoring
```

Dashboard akan dibuat pada:

```text id="rpt012"
SPRINT-13-DASHBOARD
```

---

# 7. Stock Balance Report

Menampilkan stock saat ini.

Contoh:

```text id="rpt013"
Warehouse     Item       Balance
--------------------------------
WH-JKT        Laptop       20
WH-JKT        Monitor      15
WH-BDG        Laptop       10
```

Filter:

```text id="rpt014"
Warehouse
Item
Category
```

---

# 8. Stock Movement Report

Menampilkan perubahan stock dalam periode tertentu.

Contoh:

```text id="rpt015"
Date        Type             Item       Qty
--------------------------------------------
01 Aug      STOCK_IN         Laptop     +20
05 Aug      STOCK_OUT        Laptop      -5
10 Aug      ADJUSTMENT       Laptop      -1
```

Filter:

```text id="rpt016"
Date Range
Warehouse
Item
Transaction Type
```

---

# 9. Stock In Report

Menampilkan seluruh stock masuk.

```text id="rpt017"
Date
Transaction
Warehouse
Item
Quantity
Performed By
Reference
```

Contoh:

```text id="rpt018"
TRX-001
Laptop
+20
WH-JKT
Budi
```

---

# 10. Stock Out Report

Menampilkan stock keluar.

```text id="rpt019"
Date
Transaction
Warehouse
Item
Quantity
Destination
Performed By
Reference
```

---

# 11. Stock Adjustment Report

Menampilkan adjustment yang berasal dari:

```text id="rpt020"
Stock Opname
Correction
Adjustment
```

Contoh:

```text id="rpt021"
System:
100

Physical:
97

Adjustment:
-3
```

---

# 12. Asset Report

Menampilkan kondisi asset.

```text id="rpt022"
Asset Code
Item
Serial Number
Warehouse
Location
Status
Condition
Assigned User
```

Filter:

```text id="rpt023"
Warehouse
Status
Condition
Assigned User
Item
```

---

# 13. Asset Status Report

Summary:

```text id="rpt024"
AVAILABLE        100
ASSIGNED          75
MAINTENANCE       10
DAMAGED            5
LOST               2
DISPOSED           8
```

---

# 14. Transaction Report

Menggunakan:

```text id="rpt025"
Transaction History
```

Menampilkan:

```text id="rpt026"
Transaction Number
Type
Date
Warehouse
User
Reference
```

Filter:

```text id="rpt027"
Type
Warehouse
User
Date Range
```

---

# 15. Report Filtering

Semua report harus mendukung filter yang relevan.

Contoh:

```text id="rpt028"
Date:
01-08-2026 → 30-08-2026

Warehouse:
WH-JKT

Item:
Laptop

Type:
STOCK_OUT
```

Backend harus menerima filter tersebut dan menghasilkan query yang sesuai.

---

# 16. Date Range

Default:

```text id="rpt029"
Current Month
```

User dapat memilih:

```text id="rpt030"
Today
This Week
This Month
Last Month
Custom Range
```

Untuk report yang memang bersifat current-state seperti Stock Balance, date range tidak diperlukan.

---

# 17. Warehouse Scope

Report harus mengikuti permission user.

Contoh:

```text id="rpt031"
User A
→ WH-JKT

User B
→ WH-BDG
```

User A tidak boleh melihat:

```text id="rpt032"
WH-BDG
```

meskipun request mengirim:

```text id="rpt033"
warehouse_id = WH-BDG
```

---

# 18. Report Query Principle

Jangan mengambil semua data lalu melakukan filtering di Vue.

Buruk:

```text id="rpt034"
Database
 ↓
10,000 records
 ↓
Vue filter
```

Gunakan:

```text id="rpt035"
Filter
 ↓
Backend Query
 ↓
Database
 ↓
Filtered Result
 ↓
Vue
```

---

# 19. Query Optimization

Reporting dapat menghasilkan query besar.

Gunakan:

```text id="rpt036"
Indexes
Select only required columns
Pagination
Aggregation
EXPLAIN
```

Hindari:

```text id="rpt037"
SELECT *
```

jika hanya membutuhkan beberapa kolom.

---

# 20. Aggregation

Contoh stock movement:

```text id="rpt038"
SUM(quantity)
GROUP BY item_id
```

Database sebaiknya melakukan aggregation.

Bukan:

```text id="rpt039"
Load all records
 ↓
Loop in PHP
 ↓
Calculate
```

jika aggregation dapat dilakukan langsung di database.

---

# 21. N+1 Protection

Report tidak boleh menghasilkan query:

```text id="rpt040"
1 query transaction
+
N query item
+
N query warehouse
+
N query user
```

Gunakan:

```text id="rpt041"
Eager Loading
```

atau query join/aggregation yang sesuai.

---

# 22. Pagination

Report detail menggunakan pagination.

Contoh:

```text id="rpt042"
20 / 50 / 100 records
```

Default:

```text id="rpt043"
20 records
```

Summary report tidak selalu membutuhkan pagination.

---

# 23. Report Service

Structure:

```text id="rpt044"
app/Services/Reporting/
├── StockBalanceReportService.php
├── StockMovementReportService.php
├── AssetReportService.php
└── TransactionReportService.php
```

Masing-masing service bertanggung jawab terhadap query/report tertentu.

---

# 24. Controller

```text id="rpt045"
ReportController
```

Flow:

```text id="rpt046"
Request
 ↓
Authorization
 ↓
Validation
 ↓
Report Service
 ↓
Query
 ↓
Response
```

Controller tidak berisi query report yang panjang.

---

# 25. Frontend Structure

```text id="rpt047"
resources/js/
├── Pages/
│   └── Reports/
│       ├── Index.vue
│       ├── StockBalance.vue
│       ├── StockMovement.vue
│       ├── StockIn.vue
│       ├── StockOut.vue
│       ├── StockAdjustment.vue
│       ├── Assets.vue
│       └── Transactions.vue
│
└── Components/
    └── Reports/
        ├── ReportFilters.vue
        ├── ReportTable.vue
        ├── ReportSummary.vue
        └── DateRangeFilter.vue
```

---

# 26. Report Navigation

Concept:

```text id="rpt048"
Reports
│
├── Stock
│   ├── Stock Balance
│   ├── Stock Movement
│   ├── Stock In
│   ├── Stock Out
│   └── Stock Adjustment
│
├── Assets
│   └── Asset Report
│
└── Transactions
    └── Transaction Report
```

---

# 27. Report Access

Permission minimal:

```text id="rpt049"
report.view
```

Jika diperlukan module-specific:

```text id="rpt050"
report.stock.view
report.asset.view
report.transaction.view
```

Authorization tetap mengikuti:

```text id="rpt051"
RBAC
+
Warehouse Scope
```

---

# 28. Report Security

Lindungi dari:

```text id="rpt052"
IDOR
Unauthorized Report Access
Warehouse Scope Bypass
Filter Manipulation
Data Leakage
```

---

# 29. Data Leakage

Contoh:

```text id="rpt053"
User hanya boleh melihat WH-JKT
```

Request:

```text id="rpt054"
?warehouse_id=WH-BDG
```

tidak boleh menghasilkan data WH-BDG.

Backend harus menambahkan scope secara otomatis.

---

# 30. Report Accuracy

Report harus mengambil data dari source of truth yang benar.

### Stock Balance

```text id="rpt055"
Inventory / Inventory Balance
```

### Stock Movement

```text id="rpt056"
Inventory Ledger
```

### Transaction Report

```text id="rpt057"
Transaction History
```

### Asset Report

```text id="rpt058"
Assets
```

---

# 31. Report Consistency

Contoh:

```text id="rpt059"
Stock Balance = 100
```

Stock Movement tidak boleh menghasilkan angka yang secara logic bertentangan tanpa alasan bisnis.

Jika terjadi perbedaan:

```text id="rpt060"
Trace:
Inventory
 ↓
Ledger
 ↓
Transaction
 ↓
Correction
```

---

# 32. Report Performance

Target awal:

```text id="rpt061"
Normal report query
→ < 2 seconds
```

untuk dataset operasional normal.

Untuk query lambat:

```text id="rpt062"
EXPLAIN
 ↓
Check Index
 ↓
Check Join
 ↓
Check Aggregation
 ↓
Optimize
```

Target ini adalah engineering guideline, bukan SLA production.

---

# 33. Large Dataset

Jika data sudah besar:

```text id="rpt063"
Do not load everything.
```

Gunakan:

```text id="rpt064"
Pagination
Chunking
Cursor Pagination
Aggregation
Indexes
```

Export dataset besar akan ditangani pada:

```text id="rpt065"
SPRINT-16-EXPORT
```

---

# 34. Report Read Model

Untuk V1:

```text id="rpt066"
Use existing tables
```

Tidak perlu langsung membuat database reporting terpisah.

Architecture:

```text id="rpt067"
Application DB
       ↓
Reporting Query
       ↓
Report
```

Jika volume meningkat signifikan, read model/materialized view dapat dipertimbangkan kemudian.

---

# 35. Report Caching

V1:

```text id="rpt068"
No aggressive caching
```

Prioritaskan query yang benar.

Caching dapat digunakan untuk:

```text id="rpt069"
Expensive summary
Frequently requested report
Stable data
```

setelah performance profiling.

---

# 36. Frontend Responsibility

Frontend bertanggung jawab:

```text id="rpt070"
Filter UI
Table
Pagination
Summary
Loading State
Empty State
Error State
```

Frontend tidak bertanggung jawab menghitung report utama.

---

# 37. Backend Responsibility

Backend:

```text id="rpt071"
Authorization
Scope
Validation
Query
Aggregation
Pagination
Data Formatting
```

---

# 38. Maintenance Guide

### "Saya ingin mengubah Stock Movement Report."

Cari:

```text id="rpt072"
StockMovement.vue
```

kemudian:

```text id="rpt073"
StockMovementReportService.php
```

---

### "Angka Stock Balance salah."

Trace:

```text id="rpt074"
StockBalance.vue
 ↓
ReportController
 ↓
StockBalanceReportService
 ↓
Inventory / Balance
```

Jangan langsung mengubah Vue.

---

### "Report lambat."

Trace:

```text id="rpt075"
Report Service
 ↓
Generated SQL
 ↓
EXPLAIN
 ↓
Index
 ↓
Join
 ↓
Aggregation
```

---

### "User melihat warehouse yang bukan miliknya."

Trace:

```text id="rpt076"
ReportController
 ↓
Policy
 ↓
Warehouse Scope
 ↓
Query Scope
```

---

# 39. Code Understanding Map

```text id="rpt077"
Vue
 ↓
Inertia
 ↓
Report Route
 ↓
ReportController
 ↓
Authorization
 ↓
Report Service
 ↓
Query Builder / Eloquent
 ↓
Database
 ↓
Report Data
 ↓
Vue
```

---

# 40. Testing

### Stock Balance

```text id="rpt078"
[ ] Balance correct
[ ] Warehouse filter works
[ ] Item filter works
[ ] Unauthorized warehouse excluded
```

### Stock Movement

```text id="rpt079"
[ ] Movement correct
[ ] Date filter works
[ ] Type filter works
[ ] Warehouse filter works
```

### Asset Report

```text id="rpt080"
[ ] Asset data correct
[ ] Status filter works
[ ] Condition filter works
[ ] Assigned user filter works
```

### Transaction Report

```text id="rpt081"
[ ] Transaction data correct
[ ] Type filter works
[ ] Date filter works
[ ] Warehouse scope works
```

### Performance

```text id="rpt082"
[ ] No N+1
[ ] Pagination works
[ ] Required indexes exist
[ ] Slow queries evaluated with EXPLAIN
```

---

# 41. Security Testing

```text id="rpt083"
[ ] Unauthorized report blocked
[ ] IDOR blocked
[ ] Warehouse scope enforced
[ ] Manipulated warehouse filter blocked
[ ] User cannot access restricted data
```

---

# 42. Acceptance Criteria

Sprint selesai apabila:

```text id="rpt084"
1. Reporting module tersedia.

2. Stock Balance Report tersedia.

3. Stock Movement Report tersedia.

4. Stock In Report tersedia.

5. Stock Out Report tersedia.

6. Stock Adjustment Report tersedia.

7. Asset Report tersedia.

8. Transaction Report tersedia.

9. Filter tersedia.

10. Date range tersedia untuk report yang relevan.

11. Warehouse scope diterapkan.

12. RBAC diterapkan.

13. IDOR protection tersedia.

14. Report bersifat read-only.

15. Pagination tersedia.

16. Query tidak menggunakan SELECT * secara tidak perlu.

17. N+1 query dihindari.

18. Aggregation dilakukan di database jika sesuai.

19. Index relevan tersedia.

20. Query dapat dianalisis menggunakan EXPLAIN.

21. Report mengambil data dari source of truth yang tepat.

22. Report tidak mengubah business data.

23. Automated tests berhasil.

24. Security tests berhasil.

25. Code documentation mengikuti standard Inventra.

26. Developer dapat tracing Report dari Vue → Laravel → Query → Database.
```

---

# 43. Expected Files

```text id="rpt085"
app/
├── Http/
│   └── Controllers/
│       └── ReportController.php
│
├── Policies/
│   └── ReportPolicy.php
│
└── Services/
    └── Reporting/
        ├── StockBalanceReportService.php
        ├── StockMovementReportService.php
        ├── StockInReportService.php
        ├── StockOutReportService.php
        ├── StockAdjustmentReportService.php
        ├── AssetReportService.php
        └── TransactionReportService.php

resources/js/
├── Pages/
│   └── Reports/
│
└── Components/
    └── Reports/

tests/
└── Feature/
    └── Reporting/
```

---

# 44. Code Documentation

Setiap file mengikuti:

```text id="rpt086"
docs/code-guide/00_CODE_DOCUMENTATION_STANDARD.md
```

Contoh:

```php id="rpt087"
/**
 * Stock Movement Report Service
 *
 * Purpose:
 * Generate stock movement reports.
 *
 * Data Source:
 * Inventory Ledger.
 *
 * Flow:
 * Request Filter
 * → Authorization
 * → Warehouse Scope
 * → Query Ledger
 * → Aggregate / Paginate
 * → Report Result
 *
 * Important:
 * This service is read-only.
 *
 * It must never modify inventory,
 * ledger, or transaction data.
 */
```

---

# 45. Git Branch

```text id="rpt088"
feature/reporting
```

Dependency:

```text id="rpt089"
feature/transaction-history
        ↓
feature/reporting
```

Reporting bergantung pada data transaction/ledger/asset yang sudah tersedia.

---

# 46. Suggested Commits

```text id="rpt090"
feat(reporting): add reporting module
feat(reporting): add stock balance report
feat(reporting): add stock movement report
feat(reporting): add stock in report
feat(reporting): add stock out report
feat(reporting): add stock adjustment report
feat(reporting): add asset report
feat(reporting): add transaction report
feat(reporting): add report filters
feat(reporting): add date range filtering
feat(reporting): add warehouse scope
feat(reporting): add report authorization
feat(reporting): add report pagination
perf(reporting): optimize report queries
perf(reporting): add reporting indexes
test(reporting): add stock report tests
test(reporting): add asset report tests
test(reporting): add transaction report tests
test(reporting): add report security tests
docs(reporting): document report code flow
```

---

# 47. Definition of Done

```text id="rpt091"
Reports
    ✓ Stock Balance
    ✓ Stock Movement
    ✓ Stock In
    ✓ Stock Out
    ✓ Stock Adjustment
    ✓ Asset
    ✓ Transaction

Filtering
    ✓ Warehouse
    ✓ Item
    ✓ Type
    ✓ Date Range
    ✓ Status / Condition where relevant

Security
    ✓ RBAC
    ✓ Warehouse Scope
    ✓ IDOR Protection

Performance
    ✓ Pagination
    ✓ No N+1
    ✓ Relevant Index
    ✓ EXPLAIN Review

Architecture
    ✓ Read-only
    ✓ Correct Source of Truth
    ✓ No Business Mutation

Testing
    ✓ Functional Tests
    ✓ Security Tests
    ✓ Query Tests

Documentation
    ✓ Code Comments
    ✓ Maintenance Guide
    ✓ Query Flow

Git
    ✓ feature/reporting
```

---

# 48. Final Reporting Architecture

```text id="rpt092"
                    INVENTRA DATA
                         │
        ┌────────────────┼────────────────┐
        ▼                ▼                ▼
   Inventory Ledger  Transactions      Assets
        │                │                │
        └────────────────┼────────────────┘
                         ▼
                  REPORTING SERVICE
                         │
              ┌──────────┼──────────┐
              ▼          ▼          ▼
            Stock       Asset    Transaction
            Reports     Report     Report
              │          │          │
              └──────────┼──────────┘
                         ▼
                    Inertia + Vue
```

---

# 49. Key Principle

Reporting **tidak boleh menjadi sumber kebenaran baru**.

```text
Inventory
→ Source of inventory state

Ledger
→ Source of stock movement

Transaction History
→ Source of business transaction history

Assets
→ Source of asset state

Reporting
→ Read and present those sources
```

Kalau suatu hari report menampilkan angka salah, cara berpikirnya:

```text id="rpt093"
Report
 ↓
Report Service
 ↓
SQL Query
 ↓
Source of Truth
 ↓
Ledger / Inventory / Transaction / Asset
```

**Bukan langsung memperbaiki angka di halaman report.**

Dengan begitu arsitektur Inventra tetap bersih: **business module menghasilkan data → ledger/history menyimpan jejak → reporting membaca data tersebut → dashboard nantinya merangkum hasil reporting.**

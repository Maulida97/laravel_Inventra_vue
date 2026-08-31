# Inventra

## Sprint 08 — Stock Opname

**Sprint:** SPRINT-08
**Name:** Stock Opname
**Project:** Inventra
**Status:** Planned
**Branch:** `feature/stock-opname`

---

# 1. Sprint Overview

Stock Opname digunakan untuk membandingkan:

```text
Physical Stock
      ↓
System Stock
      ↓
Difference
      ↓
Adjustment
```

Contoh:

```text
System Stock     = 100
Physical Stock   = 97

Difference       = -3
```

Setelah adjustment:

```text
System Stock     = 97
```

Seluruh perubahan tetap tercatat pada:

```text
Inventory Ledger
Audit Log
Stock Opname
```

---

# 2. Objective

Membangun Stock Opname yang:

- Membuat sesi stock opname.
- Menentukan warehouse.
- Mencatat item yang dihitung.
- Menyimpan system quantity.
- Menyimpan physical quantity.
- Menghitung variance.
- Mendukung approval.
- Melakukan stock adjustment.
- Menggunakan inventory ledger.
- Menjaga histori transaksi.
- Mencegah duplicate adjustment.
- Memiliki audit trail.

---

# 3. Scope

### Included

```text
Stock Opname
Stock Opname Items
Warehouse
Item
System Quantity
Physical Quantity
Variance
Approval
Adjustment
Inventory Ledger
Stock Balance
Audit Log
```

### Not Included

```text
Barcode Scanner
Mobile Scanner App
Advanced Cycle Counting
Accounting Integration
External ERP Integration
```

---

# 4. Core Concept

Stock Opname tidak sekadar:

```text
Edit Stock = Physical Stock
```

Tetapi:

```text
System Quantity
       ↓
Physical Count
       ↓
Calculate Variance
       ↓
Approval
       ↓
Adjustment Ledger
       ↓
Update Balance
```

Dengan demikian histori tetap dapat ditelusuri.

---

# 5. Stock Opname Structure

```text
Stock Opname
│
├── Item A
│   ├── System: 100
│   ├── Physical: 97
│   └── Variance: -3
│
├── Item B
│   ├── System: 50
│   ├── Physical: 55
│   └── Variance: +5
│
└── Item C
    ├── System: 20
    ├── Physical: 20
    └── Variance: 0
```

---

# 6. Stock Opname Header

Concept:

```text
stock_opnames
├── id
├── number
├── warehouse_id
├── opname_date
├── status
├── notes
├── created_by
├── approved_by
├── approved_at
├── posted_at
├── created_at
└── updated_at
```

Struktur final mengikuti:

```text
docs/05_DATABASE.md
```

---

# 7. Stock Opname Detail

Concept:

```text
stock_opname_items
├── id
├── stock_opname_id
├── item_id
├── system_quantity
├── physical_quantity
├── variance
└── notes
```

Formula:

```text
variance = physical_quantity - system_quantity
```

---

# 8. Variance

Contoh:

```text
System     Physical     Variance

100        97           -3
100        100           0
100        105          +5
```

Interpretasi:

```text
Negative
→ Physical < System

Zero
→ Physical = System

Positive
→ Physical > System
```

---

# 9. Status Lifecycle

```text
DRAFT
 ↓
COUNTING
 ↓
SUBMITTED
 ↓
APPROVED
 ↓
POSTED
```

Rejected:

```text
SUBMITTED
 ↓
REJECTED
```

Cancelled:

```text
DRAFT / COUNTING
 ↓
CANCELLED
```

---

# 10. Important Principle

Stock Opname tidak langsung mengubah stock ketika:

```text
DRAFT
COUNTING
SUBMITTED
APPROVED
```

Adjustment baru terjadi:

```text
POSTED
```

Flow:

```text
Physical Count
 ↓
Variance
 ↓
Approval
 ↓
POST
 ↓
Adjustment
```

---

# 11. Snapshot System Quantity

Saat opname dimulai, system quantity perlu dicatat sebagai snapshot.

Contoh:

```text
Saat mulai:

System Quantity = 100
```

Kemudian warehouse melakukan penghitungan:

```text
Physical Quantity = 97
```

System quantity tidak boleh berubah hanya karena stock berubah setelah opname dimulai.

Ini penting agar hasil opname memiliki baseline yang jelas.

---

# 12. Stock Movement During Opname

Misalnya:

```text
System at start = 100
```

Kemudian terjadi Stock Out:

```text
-10
```

System current:

```text
90
```

Tetapi snapshot opname:

```text
100
```

Karena itu business rule harus menentukan bagaimana transaksi yang terjadi selama opname ditangani.

Untuk V1, gunakan pendekatan sederhana:

```text
Warehouse/item yang sedang dihitung
→ movement harus dikontrol/dibatasi selama counting
```

atau opname dilakukan dalam periode yang ditentukan.

Detail implementasi final mengikuti keputusan bisnis Inventra.

---

# 13. Physical Quantity

User memasukkan:

```text
Physical Quantity
```

Contoh:

```text
Item: Mouse
System: 100

Physical:
[ 97 ]
```

Backend tetap melakukan validation.

Frontend bukan security boundary.

---

# 14. Quantity Validation

Physical quantity:

```text
>= 0
```

Tidak boleh:

```text
-10
```

Contoh valid:

```text
0
1
10
100
```

Jika item menggunakan decimal:

```text
10.5 KG
```

tetap diperbolehkan sesuai unit item.

---

# 15. Variance Calculation

Backend menghitung:

```text
variance =
physical_quantity - system_quantity
```

Contoh:

```text
system = 100
physical = 97

variance = 97 - 100
variance = -3
```

Jangan mempercayai variance yang dikirim frontend.

Frontend boleh mengirim:

```text
physical_quantity
```

Backend menghitung ulang variance.

---

# 16. Adjustment

Variance menjadi inventory adjustment.

Jika:

```text
variance = +5
```

ledger:

```text
+5
```

Jika:

```text
variance = -3
```

ledger:

```text
-3
```

Jika:

```text
variance = 0
```

tidak perlu membuat movement adjustment.

---

# 17. Adjustment Flow

```text
Stock Opname
      ↓
Variance
      ↓
Approval
      ↓
POST
      ↓
Inventory Adjustment
      ↓
Ledger
      ↓
Stock Balance
```

---

# 18. Example

Initial:

```text
Stock = 100
```

Opname:

```text
System = 100
Physical = 97
Variance = -3
```

Post:

```text
Ledger = -3
```

Balance:

```text
100 - 3 = 97
```

---

# 19. Positive Adjustment

Initial:

```text
Stock = 100
```

Physical:

```text
105
```

Variance:

```text
+5
```

Post:

```text
Ledger = +5
```

Balance:

```text
105
```

---

# 20. Zero Variance

```text
System = 100
Physical = 100
Variance = 0
```

Tidak perlu adjustment ledger.

Namun event opname tetap dapat dicatat:

```text
STOCK_OPNAME_POSTED
```

dan audit tetap tersedia.

---

# 21. Atomic Posting

Posting Stock Opname harus atomic:

```text
BEGIN TRANSACTION
        ↓
Validate Status
        ↓
Lock Stock Balance
        ↓
Re-check Current Stock
        ↓
Calculate Adjustment
        ↓
Create Ledger
        ↓
Update Balance
        ↓
Mark POSTED
        ↓
Audit
        ↓
COMMIT
```

Jika gagal:

```text
ROLLBACK
```

---

# 22. Re-check Current Stock

Karena stock dapat berubah setelah opname dibuat, sebelum posting:

```text
Current Balance
```

perlu diperiksa kembali.

Contoh:

```text
Snapshot = 100
Physical = 97
Variance = -3
```

Tetapi setelah opname:

```text
Stock Out = -10
Current = 90
```

Jika langsung menerapkan:

```text
90 - 3 = 87
```

hasilnya mungkin tidak sesuai business rule opname.

Karena itu sistem harus memiliki aturan jelas untuk movement selama opname.

---

# 23. Recommended V1 Rule

Untuk menyederhanakan implementasi:

```text
Saat item sedang dalam proses Stock Opname:

Stock In / Stock Out
→ tidak boleh dilakukan untuk item/warehouse tersebut
```

Setelah opname:

```text
POST
atau
CANCEL
```

baru transaksi inventory dapat berjalan normal.

Ini membuat perhitungan lebih deterministic.

---

# 24. Concurrency

Posting opname harus menangani race condition.

Gunakan:

```text
Database Transaction
+
Row Lock / Atomic Update
```

Concept:

```text
BEGIN
 ↓
Lock Stock Balance
 ↓
Validate
 ↓
Apply Variance
 ↓
Create Ledger
 ↓
COMMIT
```

---

# 25. Duplicate Posting Protection

Tidak boleh:

```text
POST SO-OP-001
POST SO-OP-001
```

dua kali.

Backend harus memastikan:

```text
status != POSTED
```

dan menggunakan protection terhadap duplicate request.

---

# 26. Idempotency

Concept:

```text
Request
 ↓
Idempotency Check
 ↓
Already Processed?
 ├── YES → Return Existing Result
 └── NO
       ↓
     Process
```

Melindungi dari:

```text
Double Click
Network Retry
API Retry
Browser Retry
```

---

# 27. Approval

Contoh:

```text
Warehouse Staff
 ↓
Create
 ↓
Count
 ↓
Submit

Warehouse Manager
 ↓
Approve

Authorized User
 ↓
Post
```

Permission final:

```text
docs/07_PERMISSION_MATRIX.md
```

---

# 28. Large Variance

Variance besar dapat membutuhkan approval tambahan.

Contoh:

```text
System = 10,000
Physical = 8,000
Variance = -2,000
```

Untuk V1:

```text
Standard approval workflow
```

Dapat dikembangkan kemudian menjadi:

```text
Variance Threshold
 ↓
Additional Approval
```

Tidak perlu dimasukkan ke implementasi awal kecuali diperlukan.

---

# 29. Warehouse Validation

```text
Warehouse exists?
 ↓
Active?
 ↓
User has access?
```

Jika gagal:

```text
Reject
```

---

# 30. Item Validation

```text
Item exists?
 ↓
Active?
 ↓
Valid unit?
```

Item inactive tidak boleh ditambahkan ke opname baru kecuali diperlukan untuk historical opname.

---

# 31. Duplicate Item

Tidak boleh:

```text
Mouse → count 10
Mouse → count 15
```

dalam satu opname.

Untuk V1:

```text
Reject duplicate item.
```

---

# 32. Frontend Structure

```text
resources/js/Pages/StockOpname/
├── Index.vue
├── Create.vue
├── Show.vue
└── Edit.vue
```

Components:

```text
resources/js/Components/StockOpname/
├── StockOpnameForm.vue
├── StockOpnameItems.vue
├── StockOpnameStatusBadge.vue
├── StockOpnameCountTable.vue
└── StockOpnameApproval.vue
```

---

# 33. Backend Structure

```text
app/
├── Models/
│   ├── StockOpname.php
│   ├── StockOpnameItem.php
│   ├── InventoryLedger.php
│   └── StockBalance.php
│
├── Http/
│   ├── Controllers/
│   │   └── StockOpnameController.php
│   │
│   └── Requests/
│       └── StockOpname/
│
├── Policies/
│   └── StockOpnamePolicy.php
│
└── Services/
    └── Inventory/
        └── StockOpnameService.php
```

---

# 34. Stock Opname Service

Business logic:

```text
StockOpnameService
 ↓
Create
 ↓
Start Counting
 ↓
Capture Snapshot
 ↓
Submit
 ↓
Approve
 ↓
Post
 ↓
Calculate Variance
 ↓
Create Adjustment Ledger
 ↓
Update Balance
 ↓
Audit
```

Controller hanya:

```text
Request
 ↓
Authorization
 ↓
Service
 ↓
Response
```

---

# 35. Request Flow — Create

```text
Create.vue
 ↓
POST /stock-opname
 ↓
Authentication
 ↓
Authorization
 ↓
Validation
 ↓
StockOpnameController
 ↓
StockOpnameService
 ↓
Create DRAFT
 ↓
Audit
```

Tidak ada inventory impact.

---

# 36. Request Flow — Start Counting

```text
START
 ↓
Validate DRAFT
 ↓
Capture System Quantity
 ↓
COUNTING
 ↓
Audit
```

System quantity menjadi snapshot.

---

# 37. Request Flow — Count

```text
User
 ↓
Input Physical Quantity
 ↓
Validation
 ↓
Save Count
 ↓
Calculate Variance
```

Variance dapat ditampilkan:

```text
System    Physical    Variance
100       97          -3
```

Tetapi backend tetap menghitung ulang saat proses penting.

---

# 38. Request Flow — Submit

```text
Submit
 ↓
Authorization
 ↓
Validate COUNTING
 ↓
Validate Items
 ↓
SUBMITTED
 ↓
Audit
```

Tidak ada adjustment.

---

# 39. Request Flow — Approve

```text
Approve
 ↓
Authorization
 ↓
Policy
 ↓
Validate SUBMITTED
 ↓
APPROVED
 ↓
Audit
```

Tidak ada adjustment.

---

# 40. Request Flow — Post

```text
POST
 ↓
Authorization
 ↓
Validate APPROVED
 ↓
BEGIN TRANSACTION
 ↓
Lock Balance
 ↓
Calculate Variance
 ↓
Create Adjustment Ledger
 ↓
Update Balance
 ↓
Mark POSTED
 ↓
Audit
 ↓
COMMIT
```

---

# 41. Permission

Minimal:

```text
stock-opname.view
stock-opname.create
stock-opname.update
stock-opname.count
stock-opname.submit
stock-opname.approve
stock-opname.post
stock-opname.cancel
```

---

# 42. Permission Concept

```text
                    VIEW  CREATE  COUNT  APPROVE  POST
Warehouse Staff      ✓      ✓       ✓       ✕       ✕
Warehouse Manager    ✓      ✓       ✓       ✓       ✓
Admin                ✓      ✓       ✓       ✓       ✓
Viewer               ✓      ✕       ✕       ✕       ✕
```

Final:

```text
docs/07_PERMISSION_MATRIX.md
```

---

# 43. Warehouse Scope

User hanya dapat membuat/melakukan opname pada warehouse yang diizinkan.

```text
User
 ↓
Warehouse Scope
 ↓
Stock Opname Warehouse
```

Backend harus melakukan pengecekan.

---

# 44. IDOR Protection

Request:

```text
POST /stock-opname/123/post
```

harus melalui:

```text
Authentication
 ↓
Permission
 ↓
Warehouse Scope
 ↓
Policy
 ↓
Status Validation
```

bukan hanya:

```text
isAuthenticated()
```

---

# 45. Audit Log

Minimal:

```text
STOCK_OPNAME_CREATED
STOCK_OPNAME_STARTED
STOCK_OPNAME_UPDATED
STOCK_OPNAME_SUBMITTED
STOCK_OPNAME_APPROVED
STOCK_OPNAME_REJECTED
STOCK_OPNAME_POSTED
STOCK_OPNAME_CANCELLED
```

Adjustment juga harus dapat ditelusuri ke Stock Opname asal.

---

# 46. Database Index

Potential indexes:

```text
stock_opnames.number
stock_opnames.warehouse_id
stock_opnames.status
stock_opnames.opname_date

stock_opname_items.stock_opname_id
stock_opname_items.item_id

inventory_ledger.item_id
inventory_ledger.warehouse_id
inventory_ledger.transaction_id
inventory_ledger.created_at

stock_balances.item_id
stock_balances.warehouse_id
```

Gunakan:

```text
EXPLAIN
```

untuk mengevaluasi query yang berat.

---

# 47. Database Constraints

Minimal:

```text
stock_opnames.number
→ UNIQUE

stock_opname_items.stock_opname_id
→ FOREIGN KEY

stock_opname_items.item_id
→ FOREIGN KEY

stock_opnames.warehouse_id
→ FOREIGN KEY

physical_quantity
→ CHECK >= 0
```

Untuk satu item dalam satu opname:

```text
(stock_opname_id, item_id)
→ UNIQUE
```

---

# 48. Frontend Responsibility

Frontend menangani:

```text
Form
Item Selection
Physical Count
Variance Display
Validation Feedback
Status
Approval
Confirmation
```

Frontend boleh menampilkan:

```text
System: 100
Physical: 97
Variance: -3
```

tetapi backend tetap menjadi source of truth.

---

# 49. Backend Responsibility

Backend menangani:

```text
Authentication
Authorization
Warehouse Scope
Snapshot
Validation
Variance Calculation
Approval
Posting
Ledger
Stock Balance
Concurrency
Idempotency
Audit
```

---

# 50. Maintenance Guide

### "Saya ingin mengubah tampilan tabel opname."

Cari:

```text
resources/js/Components/StockOpname/StockOpnameCountTable.vue
```

---

### "Saya ingin mengubah perhitungan variance."

Cari:

```text
StockOpnameService
```

Cari logic:

```text
physical_quantity - system_quantity
```

---

### "Saya ingin tahu kapan stock berubah."

Trace:

```text
Post
 ↓
StockOpnameController
 ↓
StockOpnameService
 ↓
InventoryLedger
 ↓
StockBalance
```

---

### "Variance tampil salah."

Periksa:

```text
System Quantity Snapshot
 ↓
Physical Quantity
 ↓
Variance Calculation
```

Jangan hanya memperbaiki calculation di Vue.

---

### "Stock berubah dua kali."

Periksa:

```text
Idempotency
 ↓
Transaction Status
 ↓
Ledger
 ↓
Balance Update
```

---

### "Stock berubah meskipun opname belum selesai."

Periksa:

```text
StockOpnameService
 ↓
Status
 ↓
Posting Logic
```

Pastikan hanya:

```text
POSTED
```

yang menghasilkan adjustment.

---

# 51. Code Understanding Map

Flow utama:

```text
Vue
 ↓
Inertia
 ↓
Route
 ↓
Authentication
 ↓
Authorization
 ↓
Form Request
 ↓
Controller
 ↓
StockOpnameService
 ↓
Snapshot
 ↓
Variance
 ↓
Approval
 ↓
Database Transaction
 ├── Inventory Ledger
 ├── Stock Balance
 └── Audit
 ↓
POSTED
 ↓
Response
 ↓
Vue
```

---

# 52. Inventory Understanding

Stock Opname berbeda dari Stock In/Out.

Stock In:

```text
Movement = +
```

Stock Out:

```text
Movement = -
```

Stock Opname:

```text
Movement = Physical - System
```

Contoh:

```text
System = 100
Physical = 97

Adjustment = -3
```

---

# 53. Debugging Flow

### Physical stock berbeda

```text
System Snapshot
 ↓
Physical Count
 ↓
Variance
```

### Adjustment salah

```text
Variance
 ↓
Ledger Quantity
 ↓
Balance Update
```

### Stock berubah dua kali

```text
POST Request
 ↓
Idempotency
 ↓
Ledger
 ↓
Balance
```

### User tidak dapat approve

```text
Authentication
 ↓
Permission
 ↓
Warehouse Scope
 ↓
Policy
 ↓
Status
```

---

# 54. Testing

### CRUD

```text
[ ] Stock Opname can be created
[ ] Stock Opname can be viewed
[ ] Draft can be updated
[ ] Posted cannot be edited
```

### Snapshot

```text
[ ] System quantity captured when counting starts
[ ] Snapshot does not unexpectedly change
```

### Validation

```text
[ ] Warehouse required
[ ] Warehouse active
[ ] User has warehouse access
[ ] Item exists
[ ] Item active
[ ] Physical quantity >= 0
[ ] Duplicate item rejected
```

### Workflow

```text
[ ] Draft → Counting
[ ] Counting → Submitted
[ ] Submitted → Approved
[ ] Submitted → Rejected
[ ] Approved → Posted
[ ] Invalid transitions rejected
```

### Inventory

```text
[ ] Draft does not affect stock
[ ] Counting does not affect stock
[ ] Submitted does not affect stock
[ ] Approved does not affect stock
[ ] Posted applies variance
[ ] Zero variance does not create adjustment
[ ] Positive variance increases stock
[ ] Negative variance decreases stock
```

### Security

```text
[ ] Unauthorized user cannot create
[ ] Unauthorized user cannot approve
[ ] Unauthorized user cannot post
[ ] Warehouse scope enforced
[ ] IDOR blocked
```

### Concurrency

```text
[ ] Concurrent posting handled safely
[ ] Balance cannot become corrupted
[ ] Duplicate POST does not duplicate adjustment
[ ] Transaction rolls back on failure
```

---

# 55. Acceptance Criteria

Sprint selesai apabila:

```text
1. Stock Opname dapat dibuat.

2. Warehouse dapat ditentukan.

3. Item dapat ditambahkan.

4. System quantity dapat di-snapshot.

5. Physical quantity dapat dimasukkan.

6. Variance dihitung oleh backend.

7. Workflow tersedia.

8. Approval tersedia.

9. Draft tidak mengubah stock.

10. Counting tidak mengubah stock.

11. Submitted tidak mengubah stock.

12. Approved tidak mengubah stock.

13. Posted menghasilkan adjustment.

14. Positive variance menambah stock.

15. Negative variance mengurangi stock.

16. Zero variance tidak menghasilkan movement.

17. Inventory ledger dibuat.

18. Stock balance diperbarui.

19. Negative stock tetap tidak diperbolehkan.

20. Posting atomic.

21. Concurrency protection tersedia.

22. Duplicate posting terlindungi.

23. Warehouse scope diterapkan.

24. Audit log tersedia.

25. Posted transaction tidak dapat diedit secara destructive.

26. Database constraints tersedia.

27. Index relevan tersedia.

28. Automated tests berhasil.

29. Code documentation mengikuti standard Inventra.

30. Developer dapat tracing Stock Opname dari Vue → Laravel → Database → Ledger → Balance.
```

---

# 56. Expected Files

```text
app/
├── Models/
│   ├── StockOpname.php
│   ├── StockOpnameItem.php
│   ├── InventoryLedger.php
│   └── StockBalance.php
│
├── Http/
│   ├── Controllers/
│   │   └── StockOpnameController.php
│   │
│   └── Requests/
│       └── StockOpname/
│
├── Policies/
│   └── StockOpnamePolicy.php
│
└── Services/
    └── Inventory/
        └── StockOpnameService.php

database/
└── migrations/
    ├── xxxx_create_stock_opnames_table.php
    └── xxxx_create_stock_opname_items_table.php

resources/js/
├── Pages/
│   └── StockOpname/
│
└── Components/
    └── StockOpname/

tests/
└── Feature/
    └── StockOpname/
```

---

# 57. Code Documentation

Setiap file mengikuti:

```text
docs/code-guide/00_CODE_DOCUMENTATION_STANDARD.md
```

Contoh Service:

```php
/**
 * Stock Opname Service
 *
 * Purpose:
 * Handle stock counting and inventory adjustment.
 *
 * Main Flow:
 * Create
 * → Snapshot
 * → Count
 * → Submit
 * → Approve
 * → Calculate Variance
 * → Apply Adjustment
 * → Audit
 *
 * Important:
 * Inventory adjustment must run inside
 * a database transaction.
 *
 * Variance is calculated by the backend.
 *
 * Related:
 * - StockOpname
 * - StockOpnameItem
 * - InventoryLedger
 * - StockBalance
 */
```

---

# 58. Git Branch

```text
feature/stock-opname
```

Dependency:

```text
feature/stock-in
        ↓
feature/stock-out
        ↓
feature/stock-opname
```

---

# 59. Suggested Commits

```text
feat(stock-opname): add stock opname models and migrations
feat(stock-opname): add stock opname CRUD
feat(stock-opname): add stock snapshot
feat(stock-opname): add physical count
feat(stock-opname): add variance calculation
feat(stock-opname): add stock opname workflow
feat(stock-opname): add approval
feat(stock-opname): add inventory adjustment
feat(stock-opname): add ledger integration
feat(stock-opname): add stock balance update
feat(stock-opname): add concurrency protection
feat(stock-opname): add idempotency protection
feat(stock-opname): add audit logging
test(stock-opname): add stock opname workflow tests
test(stock-opname): add variance tests
test(stock-opname): add adjustment tests
docs(stock-opname): document stock opname code flow
```

---

# 60. Definition of Done

```text
Code
    ✓ Stock Opname
    ✓ Stock Opname Items
    ✓ Snapshot
    ✓ Physical Count
    ✓ Variance
    ✓ Approval
    ✓ Posting

Inventory
    ✓ Adjustment
    ✓ Ledger
    ✓ Stock Balance
    ✓ Atomic update
    ✓ Concurrency protection
    ✓ Negative stock protection

Backend
    ✓ Validation
    ✓ Authorization
    ✓ Warehouse scope
    ✓ Business rules

Frontend
    ✓ List
    ✓ Create
    ✓ Count
    ✓ Detail
    ✓ Approval
    ✓ Variance display
    ✓ Status display

Security
    ✓ IDOR protection
    ✓ Permission enforcement
    ✓ Warehouse scope
    ✓ Duplicate posting protection

Audit
    ✓ Lifecycle audited
    ✓ Adjustment traceable

Testing
    ✓ Workflow tests
    ✓ Snapshot tests
    ✓ Variance tests
    ✓ Adjustment tests
    ✓ Concurrency tests

Documentation
    ✓ Code comments
    ✓ Maintenance guide
    ✓ Request flow
    ✓ Inventory flow

Git
    ✓ feature/stock-opname
```

---

# 61. Final Stock Opname Architecture

```text
                       STOCK OPNAME
                            │
                            ▼
                         CREATE
                            │
                            ▼
                       SNAPSHOT STOCK
                            │
                            ▼
                         COUNTING
                            │
                            ▼
                    PHYSICAL QUANTITY
                            │
                            ▼
                         VARIANCE
                            │
                            ▼
                         SUBMIT
                            │
                            ▼
                         APPROVE
                            │
                            ▼
                           POST
                            │
                  ┌─────────┴─────────┐
                  ▼                   ▼
             LEDGER +/-          BALANCE +/-
                  │                   │
                  └─────────┬─────────┘
                            ▼
                          AUDIT
                            │
                            ▼
                          POSTED
```

---

# 62. Key Principle

Stock Opname menjawab:

```text
"Apakah stok fisik sesuai dengan stok sistem?"
```

Formula utamanya:

```text
Variance
=
Physical Quantity
-
System Quantity
```

Kemudian:

```text
Variance
     ↓
Adjustment
     ↓
Inventory Ledger
     ↓
Stock Balance
```

Sehingga tiga transaksi inventory utama Inventra sekarang memiliki pola yang jelas:

```text
STOCK IN
→ + quantity

STOCK OUT
→ - quantity

STOCK OPNAME
→ physical - system
```

Dan semuanya mengikuti prinsip yang sama:

```text
Vue
 ↓
Inertia
 ↓
Laravel
 ↓
Authorization
 ↓
Validation
 ↓
Service
 ↓
Database Transaction
 ↓
Inventory Ledger
 ↓
Stock Balance
 ↓
Audit Log
```

Dengan memahami tiga flow ini, kamu sudah punya fondasi penting untuk memahami **inventory engine Inventra**, bukan sekadar tahu cara menjalankan kodenya.

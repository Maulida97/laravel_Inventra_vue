# Inventra

## Sprint 07 — Stock Out

**Sprint:** SPRINT-07
**Name:** Stock Out
**Project:** Inventra
**Status:** Planned
**Branch:** `feature/stock-out`

---

# 1. Sprint Overview

Stock Out menangani proses **barang keluar dari warehouse**.

Contoh:

```text
Stock Out
 ↓
Warehouse
 ↓
Item
 ↓
Quantity
 ↓
Approval
 ↓
Post
 ↓
Inventory Ledger (-)
 ↓
Stock Balance (-)
 ↓
Audit Log
```

---

# 2. Objective

Membangun Stock Out yang:

- Mencatat barang keluar.
- Terhubung dengan Item.
- Terhubung dengan Warehouse.
- Memvalidasi quantity.
- Memastikan stock mencukupi.
- Mendukung approval.
- Menghasilkan inventory ledger.
- Mengurangi stock balance.
- Mencegah negative stock.
- Aman terhadap duplicate posting.
- Aman terhadap race condition.
- Memiliki audit trail.

---

# 3. Scope

### Included

```text
Stock Out
Stock Out Detail
Item
Warehouse
Quantity
Reference Number
Source / Destination
Approval
Posting
Inventory Ledger
Stock Balance
Validation
Authorization
Audit Log
```

### Not Included

```text
Warehouse Transfer
Stock Opname
Purchasing
Accounting
Delivery Management
```

---

# 4. Stock Out Structure

Satu Stock Out dapat memiliki banyak item:

```text
SO-0001
│
├── Laptop     1 UNIT
├── Mouse      5 PCS
└── Keyboard   5 PCS
```

Relationship:

```text
stock_outs
    │
    │ 1:N
    ▼
stock_out_items
```

---

# 5. Stock Out Header

Concept:

```text
stock_outs
├── id
├── number
├── warehouse_id
├── transaction_date
├── destination
├── reference_number
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

# 6. Stock Out Detail

```text
stock_out_items
├── id
├── stock_out_id
├── item_id
├── quantity
├── unit_id
└── notes
```

Quantity:

```text
quantity > 0
```

---

# 7. Transaction Number

Format:

```text
SO-20260830-0001
SO-20260830-0002
SO-20260830-0003
```

Nomor harus:

```text
UNIQUE
```

Digunakan untuk:

```text
Search
Reference
Audit
Reporting
Troubleshooting
```

---

# 8. Status Lifecycle

```text
DRAFT
 ↓
SUBMITTED
 ↓
APPROVED
 ↓
POSTED
```

Alternative state:

```text
SUBMITTED
 ↓
REJECTED
```

atau:

```text
DRAFT / SUBMITTED
 ↓
CANCELLED
```

---

# 9. Important Principle

Stock tidak berkurang ketika transaksi masih:

```text
DRAFT
SUBMITTED
APPROVED
```

Stock berkurang ketika:

```text
POSTED
```

Flow:

```text
DRAFT
 ↓
SUBMITTED
 ↓
APPROVED
 ↓
POSTED
 ↓
Stock - Quantity
```

---

# 10. Stock Availability

Sebelum posting:

```text
Current Stock >= Requested Quantity
```

Contoh:

```text
Stock = 10
Request = 7

10 >= 7
✓ Allowed
```

Jika:

```text
Stock = 10
Request = 15

10 < 15
✕ Reject
```

---

# 11. Negative Stock Protection

Inventra tidak boleh menghasilkan:

```text
Stock = -5
```

kecuali nanti secara eksplisit ada business rule yang mengizinkan negative stock.

Untuk V1:

```text
Negative Stock
→ NOT ALLOWED
```

---

# 12. Stock Validation Flow

```text
POST
 ↓
Transaction exists?
 ↓
Status APPROVED?
 ↓
Warehouse active?
 ↓
User has warehouse access?
 ↓
Item active?
 ↓
Quantity valid?
 ↓
Stock available?
 ↓
Post
```

---

# 13. Inventory Ledger

Stock Out menghasilkan ledger negatif.

Contoh:

```text
Current Stock
20

Stock Out
5

Ledger
-5

Balance
15
```

Concept:

```text
Stock Out
 ↓
Inventory Ledger
 ↓
Stock Balance
```

---

# 14. Inventory Ledger Example

```text
Item: Mouse
Warehouse: WH-001

Opening
100

Stock In
+20

Stock Out
-15

Current
105
```

Ledger mencatat movement.

Balance digunakan untuk membaca current quantity.

---

# 15. Atomic Posting

Posting Stock Out harus atomic:

```text
BEGIN
 ↓
Check stock
 ↓
Lock stock balance
 ↓
Check stock again
 ↓
Decrease balance
 ↓
Create ledger
 ↓
Mark Stock Out POSTED
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

# 16. Kenapa Check Stock Harus di Dalam Transaction?

Misalnya stock:

```text
10
```

Dua request datang bersamaan:

```text
Request A → Out 8
Request B → Out 7
```

Jika hanya melakukan:

```text
SELECT stock
```

tanpa locking:

```text
A sees 10
B sees 10

A → 2
B → 3
```

Padahal total keluar:

```text
8 + 7 = 15
```

yang seharusnya tidak diperbolehkan.

Karena itu stock harus dikontrol dengan concurrency mechanism.

---

# 17. Row Lock / Atomic Update

Concept:

```text
BEGIN TRANSACTION
 ↓
SELECT balance FOR UPDATE
 ↓
Check quantity
 ↓
Update balance
 ↓
Create ledger
 ↓
COMMIT
```

Dengan demikian hanya satu proses yang dapat memodifikasi balance tersebut pada satu waktu.

Implementasi final mengikuti database dan ORM yang digunakan.

---

# 18. Duplicate Posting Protection

Tidak boleh:

```text
POST SO-001
POST SO-001
```

dua kali.

Backend harus memastikan:

```text
status != POSTED
```

sebelum melakukan posting.

---

# 19. Idempotency

Flow:

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
Double click
Network retry
Browser retry
API retry
```

---

# 20. Approval

Contoh:

```text
Warehouse Staff
 ↓
Create
 ↓
Submit

Warehouse Manager
 ↓
Approve

System / Authorized User
 ↓
Post
```

Permission final mengikuti:

```text
docs/07_PERMISSION_MATRIX.md
```

---

# 21. Segregation of Duties

Jika diperlukan:

```text
Creator ≠ Approver
```

Contoh:

```text
Budi
→ Create Stock Out

Andi
→ Approve Stock Out
```

Budi tidak dapat approve transaksi yang dia buat sendiri.

Rule ini harus ditentukan melalui authorization/business rule.

---

# 22. Rejection

Approver dapat menolak:

```text
SUBMITTED
 ↓
REJECTED
```

Reject wajib memiliki alasan:

```text
rejection_reason
```

Contoh:

```text
"Quantity tidak sesuai permintaan."
```

---

# 23. Posting

Posting:

```text
APPROVED
 ↓
POST
 ↓
Check Stock
 ↓
Ledger -
 ↓
Balance -
 ↓
POSTED
```

Setelah posted:

```text
Transaction
→ Immutable
```

---

# 24. Correction

Jika Stock Out POSTED ternyata salah:

```text
Jangan edit quantity langsung.
```

Gunakan:

```text
Original Stock Out
 ↓
Reversal
 ↓
Correct Stock Out
```

Contoh:

```text
Original
-10

Reversal
+10

Correct
-7
```

Final net:

```text
-7
```

Histori tetap dapat dilacak.

---

# 25. Warehouse Validation

```text
Warehouse exists?
 ↓
Warehouse active?
 ↓
User has access?
```

Jika tidak:

```text
403 / Validation Error
```

sesuai kondisi.

---

# 26. Item Validation

Setiap item:

```text
Item exists?
 ↓
Item active?
 ↓
Unit valid?
 ↓
Quantity > 0?
```

---

# 27. Duplicate Item

Contoh:

```text
Mouse → 5
Mouse → 3
```

Untuk V1:

```text
Reject duplicate item.
```

User harus memasukkan:

```text
Mouse → 8
```

---

# 28. Quantity Precision

Quantity mengikuti unit.

```text
PCS
→ integer

KG
→ decimal
```

Gunakan numeric/decimal yang sesuai.

Hindari floating point untuk quantity inventory yang membutuhkan precision.

---

# 29. Destination

Stock Out perlu mengetahui tujuan barang.

Contoh:

```text
Internal Department
Customer
Project
Production
Other
```

Untuk V1 dapat menggunakan:

```text
destination
```

sebagai text terkontrol atau master data jika kebutuhan berkembang.

---

# 30. Reference Number

Contoh:

```text
REQ-00123
PO-00123
PROJECT-001
```

Reference number membantu tracing ke dokumen lain.

---

# 31. Frontend Structure

```text
resources/js/Pages/StockOut/
├── Index.vue
├── Create.vue
├── Show.vue
└── Edit.vue
```

Components:

```text
resources/js/Components/StockOut/
├── StockOutForm.vue
├── StockOutItems.vue
├── StockOutStatusBadge.vue
└── StockOutApproval.vue
```

---

# 32. Backend Structure

```text
app/
├── Models/
│   ├── StockOut.php
│   ├── StockOutItem.php
│   ├── InventoryLedger.php
│   └── StockBalance.php
│
├── Http/
│   ├── Controllers/
│   │   └── StockOutController.php
│   │
│   └── Requests/
│       └── StockOut/
│
├── Policies/
│   └── StockOutPolicy.php
│
└── Services/
    └── Inventory/
        └── StockOutService.php
```

---

# 33. Stock Out Service

Service menangani business logic:

```text
StockOutService
 ↓
Validate
 ↓
Check Stock
 ↓
Begin Transaction
 ↓
Lock Balance
 ↓
Update Balance
 ↓
Create Ledger
 ↓
Mark Posted
 ↓
Audit
 ↓
Commit
```

Controller:

```text
Request
 ↓
Authorization
 ↓
Service
 ↓
Response
```

Controller tidak menangani seluruh inventory logic.

---

# 34. Request Flow — Create

```text
Create.vue
 ↓
POST /stock-out
 ↓
Authentication
 ↓
Authorization
 ↓
Validation
 ↓
StockOutController
 ↓
StockOutService
 ↓
Create DRAFT
 ↓
Audit
 ↓
Response
```

Tidak ada perubahan stock.

---

# 35. Request Flow — Submit

```text
Submit
 ↓
Authentication
 ↓
Permission
 ↓
Policy
 ↓
Validate Status
 ↓
SUBMITTED
 ↓
Audit
```

Stock tetap sama.

---

# 36. Request Flow — Approve

```text
Approve
 ↓
Authentication
 ↓
Permission
 ↓
Policy
 ↓
Validate Status
 ↓
APPROVED
 ↓
Audit
```

Stock tetap sama.

---

# 37. Request Flow — Post

```text
POST
 ↓
Authentication
 ↓
Permission
 ↓
Policy
 ↓
Validate APPROVED
 ↓
BEGIN TRANSACTION
 ↓
Lock Balance
 ↓
Check Stock
 ↓
Decrease Balance
 ↓
Create Ledger (-)
 ↓
Mark POSTED
 ↓
Audit
 ↓
COMMIT
```

---

# 38. Permission

Minimal:

```text
stock-out.view
stock-out.create
stock-out.update
stock-out.submit
stock-out.approve
stock-out.post
stock-out.cancel
```

Tidak semua role memiliki semua permission.

---

# 39. Authorization Matrix Concept

```text
                    VIEW  CREATE  SUBMIT  APPROVE  POST
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

# 40. Warehouse Scope

Setiap Stock Out harus berada pada warehouse yang dapat diakses user.

```text
User
 ↓
Warehouse Scope
 ↓
Stock Out Warehouse
```

Jika:

```text
User → WH-001
Stock Out → WH-002
```

maka:

```text
✕ Forbidden
```

---

# 41. Security Boundary

Frontend:

```text
Hide WH-002
```

bukan security.

Backend:

```text
Reject access to WH-002
```

adalah security.

Semua resource harus tetap diverifikasi di backend.

---

# 42. IDOR Protection

Request:

```text
POST /stock-out/123/post
```

tidak otomatis boleh dilakukan hanya karena user login.

Backend:

```text
Authentication
 ↓
Permission
 ↓
Warehouse Scope
 ↓
Policy
 ↓
Transaction State
```

baru:

```text
ALLOW
```

---

# 43. Audit Log

Minimal:

```text
STOCK_OUT_CREATED
STOCK_OUT_UPDATED
STOCK_OUT_SUBMITTED
STOCK_OUT_APPROVED
STOCK_OUT_REJECTED
STOCK_OUT_POSTED
STOCK_OUT_CANCELLED
```

Untuk posting:

```text
user
transaction
warehouse
items
quantity
timestamp
```

harus dapat ditelusuri melalui audit/history yang sesuai.

---

# 44. Database Index

Potential indexes:

```text
stock_outs.number
stock_outs.warehouse_id
stock_outs.status
stock_outs.transaction_date

stock_out_items.stock_out_id
stock_out_items.item_id

inventory_ledger.item_id
inventory_ledger.warehouse_id
inventory_ledger.transaction_id
inventory_ledger.created_at

stock_balances.item_id
stock_balances.warehouse_id
```

Final index berdasarkan query pattern.

Gunakan:

```text
EXPLAIN
```

untuk mengukur query performance.

---

# 45. Database Constraints

Minimal:

```text
stock_outs.number
→ UNIQUE

stock_out_items.stock_out_id
→ FOREIGN KEY

stock_out_items.item_id
→ FOREIGN KEY

stock_outs.warehouse_id
→ FOREIGN KEY

quantity
→ CHECK > 0
```

Jika menggunakan unique combination:

```text
stock_balances
(item_id, warehouse_id)
→ UNIQUE
```

agar satu item + warehouse memiliki satu balance record.

---

# 46. Frontend Responsibility

Frontend bertanggung jawab untuk:

```text
Form
Item Selection
Quantity Input
Stock Information
Validation Feedback
Status Display
Approval UI
Confirmation
```

Frontend boleh menampilkan:

```text
Available Stock: 50
```

tetapi nilai tersebut **bukan security boundary**.

---

# 47. Backend Responsibility

Backend bertanggung jawab atas:

```text
Authentication
Authorization
Warehouse Scope
Validation
Stock Availability
Concurrency
Ledger
Balance
Transaction
Idempotency
Audit
```

---

# 48. Stock Availability UI

Saat memilih item:

```text
Item: Mouse

Available Stock:
25 PCS

Request:
10 PCS

Remaining:
15 PCS
```

Ini membantu user.

Tetapi ketika POST:

```text
Backend MUST check again.
```

Karena stock dapat berubah setelah halaman dibuka.

---

# 49. Maintenance Guide

### "Saya ingin mengubah tampilan form."

Cari:

```text
resources/js/Pages/StockOut/Create.vue
```

atau:

```text
resources/js/Components/StockOut/StockOutForm.vue
```

---

### "Saya ingin mengubah validasi quantity."

Cari:

```text
app/Http/Requests/StockOut/
```

dan:

```text
StockOutService.php
```

jika validasinya merupakan business rule.

---

### "Saya ingin tahu kenapa stock berkurang."

Trace:

```text
Post Button
 ↓
Route
 ↓
Controller
 ↓
StockOutService
 ↓
StockBalance
 ↓
InventoryLedger
```

---

### "Stock bisa minus."

Periksa:

```text
StockOutService
 ↓
Stock Availability
 ↓
Transaction
 ↓
Row Lock / Atomic Update
```

---

### "Stock berkurang dua kali."

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

### "User bisa Stock Out dari warehouse lain."

Periksa:

```text
StockOutPolicy
 ↓
Warehouse Scope
```

---

### "POSTED transaction ingin diedit."

Jangan edit langsung.

Gunakan:

```text
Reversal
 ↓
Correction
```

---

# 50. Code Understanding Map

Alur utama:

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
StockOutService
 ↓
Database Transaction
 ├── Check Stock
 ├── Lock Balance
 ├── Update Balance
 ├── Create Ledger
 ├── Mark POSTED
 └── Audit
 ↓
Response
 ↓
Vue
```

Ini merupakan salah satu flow paling penting di Inventra.

---

# 51. Debugging Flow

### Stock tidak berkurang

```text
POST berhasil?
 ↓
Status POSTED?
 ↓
Ledger dibuat?
 ↓
Balance updated?
 ↓
Transaction committed?
```

### Stock menjadi minus

```text
Availability Check
 ↓
Row Lock
 ↓
Atomic Update
 ↓
Concurrency
```

### Stock berkurang dua kali

```text
Duplicate Request
 ↓
Idempotency
 ↓
Transaction Status
 ↓
Ledger
 ↓
Balance
```

### User tidak dapat posting

```text
Authentication
 ↓
Permission
 ↓
Warehouse Scope
 ↓
Policy
 ↓
Transaction Status
 ↓
Stock Availability
```

---

# 52. Testing

### CRUD

```text
[ ] Stock Out can be created
[ ] Stock Out can be viewed
[ ] Draft can be updated
[ ] Posted transaction cannot be edited
```

### Validation

```text
[ ] Warehouse required
[ ] Warehouse must be active
[ ] User must have warehouse access
[ ] Item must exist
[ ] Item must be active
[ ] Quantity must be > 0
[ ] Duplicate item rejected
```

### Workflow

```text
[ ] Draft can be submitted
[ ] Submitted can be approved
[ ] Submitted can be rejected
[ ] Approved can be posted
[ ] Invalid state transition rejected
```

### Inventory

```text
[ ] Draft does not change stock
[ ] Submitted does not change stock
[ ] Approved does not change stock
[ ] Posted decreases stock
[ ] Ledger is created
[ ] Balance is decreased
```

### Stock Protection

```text
[ ] Cannot issue more than available stock
[ ] Cannot create negative stock
[ ] Stock checked again during posting
```

### Security

```text
[ ] Unauthorized user cannot create
[ ] Unauthorized user cannot approve
[ ] Unauthorized user cannot post
[ ] Warehouse scope enforced
[ ] IDOR blocked
[ ] Privilege escalation blocked
```

### Concurrency

```text
[ ] Concurrent Stock Out does not corrupt balance
[ ] Concurrent requests cannot create negative stock
[ ] Duplicate request does not decrease stock twice
[ ] Transaction rolls back on failure
```

---

# 53. Acceptance Criteria

Sprint selesai apabila:

```text
1. Stock Out dapat dibuat.

2. Stock Out memiliki detail item.

3. Stock Out terhubung dengan warehouse.

4. Transaction number unik.

5. Lifecycle status tersedia.

6. Draft tidak mengubah stock.

7. Posted transaction mengurangi stock.

8. Inventory ledger dibuat dengan quantity negatif.

9. Stock balance diperbarui.

10. Stock tidak boleh menjadi negatif.

11. Stock availability dicek saat posting.

12. Posting bersifat atomic.

13. Concurrency protection tersedia.

14. Duplicate posting terlindungi.

15. Approval workflow tersedia.

16. Warehouse scope diterapkan.

17. Posted transaction tidak dapat diedit secara destructive.

18. Reversal/correction principle tersedia.

19. Audit log tersedia.

20. Database constraints tersedia.

21. Index relevan tersedia.

22. Automated tests berhasil.

23. Code documentation mengikuti standard Inventra.

24. Developer dapat tracing Stock Out dari Vue → Laravel → Database → Ledger → Balance.
```

---

# 54. Expected Files

```text
app/
├── Models/
│   ├── StockOut.php
│   ├── StockOutItem.php
│   ├── InventoryLedger.php
│   └── StockBalance.php
│
├── Http/
│   ├── Controllers/
│   │   └── StockOutController.php
│   │
│   └── Requests/
│       └── StockOut/
│
├── Policies/
│   └── StockOutPolicy.php
│
└── Services/
    └── Inventory/
        └── StockOutService.php

database/
└── migrations/
    ├── xxxx_create_stock_outs_table.php
    └── xxxx_create_stock_out_items_table.php

resources/js/
├── Pages/
│   └── StockOut/
│
└── Components/
    └── StockOut/

tests/
└── Feature/
    └── StockOut/
```

---

# 55. Code Documentation

Setiap file mengikuti:

```text
docs/code-guide/00_CODE_DOCUMENTATION_STANDARD.md
```

Contoh Service:

```php
/**
 * Stock Out Service
 *
 * Purpose:
 * Handle Stock Out business operations.
 *
 * Main Flow:
 * Validate
 * → Check Stock
 * → Lock Balance
 * → Decrease Stock
 * → Create Ledger
 * → Mark Posted
 * → Audit
 *
 * Important:
 * Stock-changing operations must run
 * inside a database transaction.
 *
 * Negative stock is not allowed.
 *
 * Related:
 * - StockOut
 * - StockOutItem
 * - InventoryLedger
 * - StockBalance
 */
```

---

# 56. Git Branch

```text
feature/stock-out
```

Dependency:

```text
feature/master-data
        ↓
feature/item-management
        ↓
feature/warehouse
        ↓
feature/stock-in
        ↓
feature/stock-out
```

---

# 57. Suggested Commits

```text
feat(stock-out): add stock out models and migrations
feat(stock-out): add stock out CRUD
feat(stock-out): add stock out validation
feat(stock-out): add stock out authorization
feat(stock-out): add stock out workflow
feat(stock-out): add stock availability validation
feat(stock-out): add inventory ledger integration
feat(stock-out): add atomic stock posting
feat(stock-out): add concurrency protection
feat(stock-out): add idempotency protection
feat(stock-out): add stock out audit logging
test(stock-out): add stock out workflow tests
test(stock-out): add negative stock tests
test(stock-out): add concurrency tests
docs(stock-out): document stock out code flow
```

---

# 58. Definition of Done

```text
Code
    ✓ Stock Out
    ✓ Stock Out Items
    ✓ Transaction lifecycle
    ✓ Approval
    ✓ Posting

Inventory
    ✓ Availability check
    ✓ Negative stock protection
    ✓ Ledger
    ✓ Stock Balance
    ✓ Atomic update
    ✓ Concurrency protection

Backend
    ✓ Validation
    ✓ Authorization
    ✓ Warehouse scope
    ✓ Business rules

Frontend
    ✓ List
    ✓ Create
    ✓ Detail
    ✓ Edit Draft
    ✓ Stock availability display
    ✓ Approval UI
    ✓ Status display

Security
    ✓ IDOR protection
    ✓ Permission enforcement
    ✓ Scope enforcement
    ✓ Duplicate posting protection

Audit
    ✓ Transaction lifecycle audited
    ✓ Inventory movement traceable

Testing
    ✓ Workflow tests pass
    ✓ Stock availability tests pass
    ✓ Negative stock tests pass
    ✓ Concurrency tests pass

Documentation
    ✓ Code comments
    ✓ Maintenance guide
    ✓ Request flow
    ✓ Inventory flow

Git
    ✓ feature/stock-out
```

---

# 59. Final Stock Out Architecture

```text
                         STOCK OUT
                             │
                             ▼
                        VALIDATION
                             │
                             ▼
                       AUTHORIZATION
                             │
                             ▼
                         WORKFLOW
                             │
                             ▼
                          APPROVED
                             │
                             ▼
                            POST
                             │
                     ┌───────┴───────┐
                     ▼               ▼
                CHECK STOCK       LOCK BALANCE
                     │               │
                     └───────┬───────┘
                             ▼
                       DECREASE STOCK
                             │
                 ┌───────────┴───────────┐
                 ▼                       ▼
              LEDGER (-)            BALANCE (-)
                 │                       │
                 └───────────┬───────────┘
                             ▼
                           AUDIT
                             │
                             ▼
                           POSTED
```

---

# 60. Key Principle

Stock Out menjawab:

```text
"What inventory left the warehouse?"
```

Prinsip paling penting:

```text
DRAFT
→ No stock impact

SUBMITTED
→ No stock impact

APPROVED
→ No stock impact

POSTED
→ Stock decreases
```

Tetapi saat POST:

```text
Check Stock
      ↓
Lock
      ↓
Decrease Balance
      ↓
Create Ledger (-)
      ↓
POSTED
```

Jadi ketika nanti kamu membuka kode dan bertanya:

> "Kenapa Stock Out tidak boleh membuat stock minus?"

kamu bisa tracing:

```text
Post Button
 ↓
StockOutController
 ↓
StockOutService
 ↓
Database Transaction
 ↓
Lock Stock Balance
 ↓
Check Available Quantity
 ↓
Decrease Balance
 ↓
Create Ledger
 ↓
Commit
```

Dan jika ingin memahami **kenapa stock berubah**, fokus utama ada pada:

```text
StockOutService
       ↓
StockBalance
       ↓
InventoryLedger
```

Bukan pada `Create.vue`.

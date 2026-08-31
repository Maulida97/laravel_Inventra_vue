# Inventra

## Sprint 06 — Stock In

**Sprint:** SPRINT-06
**Name:** Stock In
**Project:** Inventra
**Status:** Planned
**Branch:** `feature/stock-in`

---

# 1. Sprint Overview

Stock In menangani seluruh proses **barang masuk ke warehouse**.

Contoh sumber:

```text id="xq3s5e"
Purchase
Return
Adjustment
Other Incoming
```

Flow utama:

```text id="i2q5te"
Stock In
 ↓
Validation
 ↓
Approval (jika diperlukan)
 ↓
Post Transaction
 ↓
Inventory Ledger
 ↓
Stock Balance
 ↓
Audit Log
```

---

# 2. Objective

Membangun Stock In yang:

- Mencatat barang masuk.
- Terhubung dengan Item.
- Terhubung dengan Warehouse.
- Mendukung quantity.
- Mendukung unit.
- Memiliki reference number.
- Memiliki status transaksi.
- Mendukung approval workflow.
- Menghasilkan inventory ledger.
- Menjaga stock balance tetap konsisten.
- Aman terhadap duplicate submission.
- Memiliki audit trail.

---

# 3. Scope

### Included

```text id="m2p1c6"
Stock In
Stock In Detail
Item
Warehouse
Quantity
Reference Number
Date
Reason / Source
Status
Approval
Inventory Ledger
Stock Balance
Validation
Authorization
Audit Log
```

### Not Included

```text id="e4u6r9"
Stock Out
Stock Opname
Warehouse Transfer
Advanced Purchasing
Accounting Integration
```

---

# 4. Stock In Structure

Satu Stock In memiliki banyak detail.

```text id="6w3n8f"
Stock In
 ├── Item A → 10 PCS
 ├── Item B → 5 PCS
 └── Item C → 20 PCS
```

Relationship:

```text id="n8d8m4"
stock_ins
     │
     │ 1:N
     ▼
stock_in_items
```

---

# 5. Stock In Header

Minimal:

```text id="e5qmbt"
stock_ins
├── id
├── number
├── warehouse_id
├── transaction_date
├── source
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

Nama field final mengikuti `05_DATABASE.md`.

---

# 6. Stock In Detail

```text id="5imq8d"
stock_in_items
├── id
├── stock_in_id
├── item_id
├── quantity
├── unit_id
└── notes
```

Quantity harus:

```text id="jup2s6"
> 0
```

---

# 7. Transaction Number

Stock In memiliki nomor transaksi unik.

Contoh:

```text id="q6ip8r"
SI-20260830-0001
SI-20260830-0002
SI-20260830-0003
```

Nomor transaksi digunakan untuk:

```text id="f5zjly"
Search
Reference
Audit
Reporting
Troubleshooting
```

Nomor transaksi harus unik di database.

---

# 8. Stock In Status

Gunakan lifecycle yang jelas:

```text id="7kjfaw"
DRAFT
 ↓
SUBMITTED
 ↓
APPROVED
 ↓
POSTED
```

Alternative:

```text id="7l0t09"
DRAFT
 ↓
SUBMITTED
 ↓
APPROVED
 ↓
POSTED
```

Rejected:

```text id="b3n2i0"
SUBMITTED
 ↓
REJECTED
```

Cancelled:

```text id="xq6s8r"
DRAFT / SUBMITTED
 ↓
CANCELLED
```

Status final mengikuti approval workflow Inventra.

---

# 9. Important Principle

**Stock In tidak langsung mengubah stock ketika masih Draft.**

```text id="f1j0cj"
DRAFT
 ↓
No inventory impact
```

Setelah transaction benar-benar dipost:

```text id="n9o7bp"
POSTED
 ↓
Inventory impact
```

Ini sangat penting agar stock tidak berubah hanya karena user membuat draft.

---

# 10. Inventory Ledger

Setiap Stock In yang dipost menghasilkan ledger entry.

Contoh:

```text id="j9zy60"
Stock In
SI-0001
Item: Mouse
Qty: +10
Warehouse: WH-001
```

Ledger:

```text id="z48c4s"
+10
```

Concept:

```text id="82w9ct"
Transaction
 ↓
Ledger
 ↓
Stock Balance
```

---

# 11. Inventory Ledger Principle

Ledger adalah **source of truth untuk movement**.

Contoh:

```text id="kv0w6x"
Initial
0

Stock In
+10

Stock Out
-3

Stock Opname
+2

Current
9
```

Jangan menjadikan `items.stock` sebagai satu-satunya sumber histori movement.

---

# 12. Stock Balance

Stock balance dapat digunakan untuk membaca quantity saat ini.

Concept:

```text id="s1t7me"
Inventory Ledger
       ↓
Stock Balance
```

Balance dapat berupa:

```text id="s39zzh"
item_id
warehouse_id
quantity
```

Stock In:

```text id="g1v2o8"
balance += quantity
```

Tetapi perubahan harus tetap dilakukan secara atomic.

---

# 13. Atomic Transaction

Posting Stock In harus menggunakan database transaction.

```text id="0kjd3t"
BEGIN TRANSACTION
        │
        ├── Validate
        ├── Create/Update Transaction
        ├── Create Ledger
        ├── Update Stock Balance
        └── Create Audit
        │
       COMMIT
```

Jika salah satu gagal:

```text id="3j10ez"
ROLLBACK
```

Tujuan:

```text id="95ftkl"
Transaction
Ledger
Stock Balance
Audit
```

tidak boleh berada dalam kondisi setengah berhasil.

---

# 14. Duplicate Posting Protection

User tidak boleh melakukan:

```text id="c3pjsk"
POST SI-0001
POST SI-0001
```

dua kali.

Backend harus memeriksa:

```text id="n9a1bm"
Status != POSTED
```

dan database/business logic harus menjaga idempotency.

---

# 15. Idempotency

Posting request dapat memiliki protection terhadap duplicate request.

Concept:

```text id="q4o4tc"
Request
 ↓
Idempotency Check
 ↓
Already Processed?
 ├── YES → Return existing result
 └── NO
       ↓
     Process
```

Ini penting ketika:

```text id="j9a5un"
User double-click
Network retry
Browser retry
API retry
```

---

# 16. Warehouse Validation

Sebelum Stock In:

```text id="70eq9n"
Warehouse exists?
        ↓
Active?
        ↓
User has access?
```

Jika salah:

```text id="9p7d4x"
Reject
```

---

# 17. Item Validation

Setiap detail:

```text id="0b83tc"
Item exists?
 ↓
Item active?
 ↓
Unit valid?
 ↓
Quantity > 0?
```

Item inactive tidak boleh digunakan untuk transaksi baru.

---

# 18. Stock In Validation

Minimal:

```text id="u6ukxk"
warehouse_id
→ required
→ exists
→ active
→ within user scope

transaction_date
→ required
→ valid date

source
→ required

reference_number
→ nullable

items
→ required
→ array
→ min 1

quantity
→ required
→ numeric
→ > 0
```

---

# 19. Duplicate Items

Request:

```text id="wtx7b4"
Mouse → 5
Mouse → 10
```

Sebaiknya tidak dibuat menjadi dua detail untuk item yang sama.

Pilihan:

```text id="q1yp4k"
Reject duplicate
```

atau:

```text id="0mtx6k"
Merge quantity
```

Untuk V1, lebih sederhana:

```text id="w3c9nw"
Reject duplicate item
```

---

# 20. Quantity Precision

Quantity harus mengikuti jenis item.

Contoh:

```text id="w12g2x"
PCS
→ integer

KG
→ decimal
```

Karena itu database quantity sebaiknya menggunakan tipe numeric/decimal yang sesuai kebutuhan.

Jangan menggunakan floating-point untuk quantity inventory jika precision penting.

---

# 21. Stock In Source

Contoh:

```text id="t02v5v"
PURCHASE
RETURN
ADJUSTMENT
OTHER
```

Source harus menggunakan enum/value yang konsisten.

Jangan menggunakan string bebas:

```text id="9tx3yl"
"beli"
"pembelian"
"Purchase"
"PURCHASE"
```

Semua harus memiliki standard value.

---

# 22. Approval

Jika Stock In membutuhkan approval:

```text id="j7y5ps"
DRAFT
 ↓
SUBMITTED
 ↓
APPROVED
 ↓
POSTED
```

User yang membuat transaksi tidak otomatis menjadi approver jika segregation of duties diterapkan.

---

# 23. Approval Rule

Contoh:

```text id="w0o4vr"
Warehouse Staff
→ Create
→ Submit

Warehouse Manager
→ Approve

System
→ Post
```

Role/permission final mengikuti `07_PERMISSION_MATRIX.md`.

---

# 24. Rejection

Jika approver menolak:

```text id="x8x2sh"
SUBMITTED
 ↓
REJECTED
```

Alasan reject wajib disimpan:

```text id="j8c7ly"
rejection_reason
```

User kemudian dapat memperbaiki dan submit kembali jika business rule mengizinkan.

---

# 25. Posting

Posting adalah titik ketika inventory berubah.

```text id="o7ypo5"
APPROVED
 ↓
POST
 ↓
Ledger +
 ↓
Balance +
 ↓
POSTED
```

Setelah POSTED:

```text id="1tdq4e"
Transaction
→ immutable
```

Jika terjadi kesalahan, gunakan mekanisme reversal/correction, bukan mengubah histori secara sembarangan.

---

# 26. Reversal Principle

Jangan:

```text id="r9m8gc"
Edit POSTED transaction
```

Lebih aman:

```text id="j9x4jp"
Original Transaction
 ↓
Reversal
 ↓
Correct Transaction
```

Contoh:

```text id="p5j1q6"
Stock In +10
 ↓
Reversal -10
 ↓
Correct Stock In +8
```

Dengan demikian histori tetap dapat dilacak.

---

# 27. Frontend Structure

```text id="8m9d2f"
resources/js/Pages/StockIn/
├── Index.vue
├── Create.vue
├── Show.vue
└── Edit.vue
```

Component:

```text id="k4i6cv"
resources/js/Components/StockIn/
├── StockInForm.vue
├── StockInItems.vue
├── StockInStatusBadge.vue
└── StockInApproval.vue
```

Gunakan component hanya jika memang reusable.

---

# 28. Backend Structure

```text id="m0a8cs"
app/
├── Models/
│   ├── StockIn.php
│   ├── StockInItem.php
│   ├── InventoryLedger.php
│   └── StockBalance.php
│
├── Http/
│   ├── Controllers/
│   │   └── StockInController.php
│   │
│   └── Requests/
│       └── StockIn/
│
├── Policies/
│   └── StockInPolicy.php
│
└── Services/
    └── Inventory/
        └── StockInService.php
```

Karena posting Stock In memiliki business logic kompleks, penggunaan Service sangat disarankan.

---

# 29. Stock In Service

Service bertanggung jawab terhadap business process:

```text id="ql9pbb"
StockInService
 ↓
Validate business rules
 ↓
Create transaction
 ↓
Create ledger
 ↓
Update balance
 ↓
Audit
```

Controller tidak seharusnya memiliki seluruh logic tersebut.

Controller fokus pada:

```text id="h27e4h"
Request
→ Call Service
→ Response
```

---

# 30. Request Flow — Draft

```text id="7d1j6b"
Create.vue
 ↓
POST /stock-in
 ↓
Authentication
 ↓
Authorization
 ↓
Validation
 ↓
StockInController
 ↓
StockInService
 ↓
Create DRAFT
 ↓
Audit
 ↓
Response
```

Belum ada perubahan inventory.

---

# 31. Request Flow — Submit

```text id="6v5w9d"
Submit
 ↓
Authorization
 ↓
Validate Status
 ↓
SUBMITTED
 ↓
Audit
```

Belum ada perubahan stock.

---

# 32. Request Flow — Approve

```text id="xoq58x"
Approve
 ↓
Authorization
 ↓
Validate Status
 ↓
APPROVED
 ↓
Audit
```

Stock belum berubah jika posting dilakukan terpisah.

---

# 33. Request Flow — Post

```text id="w8i0ko"
POST
 ↓
Authorization
 ↓
Validate APPROVED
 ↓
BEGIN TRANSACTION
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

Jika error:

```text id="r1at0z"
ROLLBACK
```

---

# 34. Permission

Minimal:

```text id="6ctzml"
stock-in.view
stock-in.create
stock-in.update
stock-in.submit
stock-in.approve
stock-in.post
stock-in.cancel
```

Tidak semua role memiliki seluruh permission.

---

# 35. Authorization Matrix Concept

Contoh:

```text id="w8e9b3"
                    VIEW  CREATE  SUBMIT  APPROVE  POST
Warehouse Staff      ✓      ✓       ✓       ✕       ✕
Warehouse Manager    ✓      ✓       ✓       ✓       ✓
Admin                ✓      ✓       ✓       ✓       ✓
Viewer               ✓      ✕       ✕       ✕       ✕
```

Final permission mengikuti:

```text id="l4ub1o"
07_PERMISSION_MATRIX.md
```

---

# 36. Audit Log

Minimal:

```text id="czx0fj"
STOCK_IN_CREATED
STOCK_IN_UPDATED
STOCK_IN_SUBMITTED
STOCK_IN_APPROVED
STOCK_IN_REJECTED
STOCK_IN_POSTED
STOCK_IN_CANCELLED
```

Posting harus menyimpan informasi siapa yang melakukan action.

---

# 37. Database Index

Index penting kemungkinan:

```text id="7ftn7p"
stock_ins.number
stock_ins.warehouse_id
stock_ins.status
stock_ins.transaction_date

stock_in_items.stock_in_id
stock_in_items.item_id

inventory_ledger.item_id
inventory_ledger.warehouse_id
inventory_ledger.transaction_id
inventory_ledger.created_at
```

Final index mengikuti query pattern.

Gunakan:

```text id="r71i4w"
EXPLAIN
```

untuk mengevaluasi query.

---

# 38. Database Constraints

Minimal:

```text id="ex5cxg"
stock_ins.number UNIQUE

stock_in_items.stock_in_id
→ FOREIGN KEY

stock_in_items.item_id
→ FOREIGN KEY

stock_ins.warehouse_id
→ FOREIGN KEY

quantity
→ CHECK > 0
```

Jika database mendukung dan cocok dengan desain, gunakan database constraint untuk menjaga integrity.

---

# 39. Concurrency

Stock adalah data yang sensitif terhadap race condition.

Contoh:

```text id="g52t8d"
Request A
Stock = 10
Add +5

Request B
Stock = 10
Add +7
```

Jangan sampai hasil akhir salah karena kedua request membaca balance lama.

Gunakan:

```text id="j7m3k9"
Database Transaction
+
Appropriate Row Lock / Atomic Update
```

sesuai implementasi final.

---

# 40. Stock Balance Update

Concept:

```text id="h7y1i8"
BEGIN
 ↓
Lock balance row
 ↓
Read current quantity
 ↓
Add incoming quantity
 ↓
Save balance
 ↓
Create ledger
 ↓
COMMIT
```

Urutan implementasi harus konsisten untuk mencegah race condition.

---

# 41. Inventory Ledger Structure

Concept:

```text id="0g87n8"
inventory_ledger
├── id
├── item_id
├── warehouse_id
├── transaction_type
├── transaction_id
├── quantity
├── balance_after
├── transaction_date
└── created_at
```

Contoh:

```text id="smg6fs"
Item: Mouse
Warehouse: WH-001

+10
Balance After: 10
```

`balance_after` hanya digunakan jika memang menjadi bagian desain ledger.

---

# 42. Stock In List

Minimal:

```text id="y8k8e4"
Stock In
------------------------------------------------

Search: [ SI-2026 ]

Warehouse: [ All ]
Status:    [ All ]
Date:      [ From - To ]

Number       Warehouse    Date        Status
SI-000001    WH-001       30/08/26    POSTED
SI-000002    WH-001       30/08/26    DRAFT
```

---

# 43. Stock In Detail Page

Menampilkan:

```text id="m4m7kq"
Transaction Number
Warehouse
Date
Source
Reference
Created By
Approved By
Status
Notes

Items
--------------------------------
Item       Qty     Unit
Mouse      10      PCS
Keyboard   5       PCS
```

Jika POSTED:

```text id="f2u9s6"
Ledger Impact
```

dapat ditampilkan.

---

# 44. Frontend Responsibility

Frontend:

```text id="2n8s0y"
Form
Item Selection
Quantity Input
Validation Feedback
Status Display
Approval UI
Confirmation
```

Backend:

```text id="x5e4pi"
Authorization
Validation
Business Rules
Transaction Lifecycle
Ledger
Balance
Concurrency
Audit
```

---

# 45. Security

Stock In harus melindungi:

```text id="k3s1i7"
Unauthorized posting
Unauthorized approval
Warehouse scope bypass
Duplicate posting
Quantity manipulation
IDOR
Mass assignment
Race condition
```

Contoh:

```text id="q2m8r5"
User mengubah:
quantity = 10
```

menjadi:

```text id="93u4hl"
quantity = 10000
```

backend tetap melakukan validation dan authorization.

---

# 46. IDOR Protection

Jangan percaya:

```text id="7k5k9m"
POST /stock-in/123/post
```

hanya karena user authenticated.

Backend harus memeriksa:

```text id="7xk4pi"
User
 ↓
Permission
 ↓
Warehouse Scope
 ↓
Stock In Resource
 ↓
Allowed?
```

---

# 47. Maintenance Guide

### "Saya ingin mengubah tampilan form Stock In."

Cari:

```text id="6n7h2g"
resources/js/Pages/StockIn/Create.vue
```

atau:

```text id="v5ow5b"
Components/StockIn/StockInForm.vue
```

---

### "Saya ingin mengubah aturan quantity."

Cari:

```text id="1z5rj4"
StockInRequest
```

dan business rule pada:

```text id="8x9g4r"
StockInService
```

---

### "Saya ingin tahu kapan stock berubah."

Cari:

```text id="09b2o4"
StockInService
 ↓
POST
 ↓
InventoryLedger
 ↓
StockBalance
```

---

### "Stock bertambah dua kali."

Periksa:

```text id="h5t8i9"
Duplicate Request
 ↓
Idempotency
 ↓
Transaction Status
 ↓
Ledger
 ↓
Balance Update
```

---

### "User bisa posting Stock In warehouse lain."

Periksa:

```text id="3w0l6f"
StockInPolicy
 ↓
Warehouse Scope
```

---

### "Stock balance salah."

Trace:

```text id="k3j9a2"
Stock In
 ↓
Ledger
 ↓
Balance Update
 ↓
Database Transaction
 ↓
Concurrency / Lock
```

Jangan langsung mengubah angka balance secara manual.

---

### "Transaksi POSTED ingin diubah."

Jangan langsung mengubah record.

Periksa:

```text id="j9x1po"
Reversal / Correction Flow
```

agar histori inventory tetap konsisten.

---

# 48. Code Understanding Map

Untuk memahami Stock In:

```text id="2nq9i4"
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
StockInService
 ↓
Database Transaction
 ├── Stock In
 ├── Stock In Items
 ├── Inventory Ledger
 ├── Stock Balance
 └── Audit Log
 ↓
Response
 ↓
Vue
```

Ini adalah flow penting yang harus kamu pahami karena Stock Out dan Stock Opname nantinya menggunakan konsep yang mirip.

---

# 49. Debugging Flow

Jika Stock In tidak menambah stock:

```text id="0jsfpo"
Transaction status
 ↓
POST berhasil?
 ↓
Ledger dibuat?
 ↓
Balance updated?
 ↓
Transaction committed?
```

Jika stock bertambah dua kali:

```text id="9i5v1v"
Duplicate request?
 ↓
Idempotency?
 ↓
Ledger duplicate?
 ↓
Balance update duplicate?
```

Jika user tidak bisa approve:

```text id="o5t6lq"
Authentication
 ↓
Permission
 ↓
Role
 ↓
Policy
 ↓
Transaction status
```

---

# 50. Testing

### CRUD

```text id="tvb8m1"
[ ] Stock In can be created
[ ] Stock In can be viewed
[ ] Draft can be updated
[ ] Posted transaction cannot be edited
```

### Validation

```text id="8i3x6m"
[ ] Warehouse required
[ ] Warehouse must be active
[ ] User must have warehouse access
[ ] Item must exist
[ ] Item must be active
[ ] Quantity must be > 0
[ ] Duplicate item rejected
```

### Workflow

```text id="a4i8om"
[ ] Draft can be submitted
[ ] Submitted can be approved
[ ] Submitted can be rejected
[ ] Approved can be posted
[ ] Invalid status transition rejected
```

### Inventory

```text id="b5y2n9"
[ ] Draft does not affect stock
[ ] Submitted does not affect stock
[ ] Approved does not affect stock
[ ] Posted increases stock
[ ] Ledger created
[ ] Balance updated
```

### Security

```text id="m8n2sd"
[ ] Unauthorized user cannot create
[ ] Unauthorized user cannot approve
[ ] Unauthorized user cannot post
[ ] Warehouse scope enforced
[ ] IDOR blocked
[ ] Privilege escalation blocked
```

### Concurrency

```text id="0u1b7e"
[ ] Concurrent posting does not corrupt balance
[ ] Duplicate request does not duplicate stock
[ ] Database transaction rolls back on failure
```

---

# 51. Acceptance Criteria

Sprint selesai apabila:

```text id="w1c8xn"
1. Stock In dapat dibuat.

2. Stock In memiliki detail item.

3. Stock In terhubung dengan warehouse.

4. Transaction number unik.

5. Lifecycle status tersedia.

6. Draft tidak mengubah inventory.

7. Posted transaction mengubah inventory.

8. Inventory ledger dibuat.

9. Stock balance diperbarui.

10. Posting bersifat atomic.

11. Duplicate posting terlindungi.

12. Warehouse scope diterapkan.

13. Approval workflow tersedia.

14. Posted transaction tidak dapat diedit secara destructive.

15. Audit log tersedia.

16. Query penting memiliki index yang relevan.

17. Concurrency protection tersedia.

18. Automated tests berhasil.

19. Code documentation mengikuti standard Inventra.

20. Developer dapat tracing Stock In dari Vue → Laravel → Database → Ledger → Balance.
```

---

# 52. Expected Files

```text id="z2q3m5"
app/
├── Models/
│   ├── StockIn.php
│   ├── StockInItem.php
│   ├── InventoryLedger.php
│   └── StockBalance.php
│
├── Http/
│   ├── Controllers/
│   │   └── StockInController.php
│   │
│   └── Requests/
│       └── StockIn/
│
├── Policies/
│   └── StockInPolicy.php
│
└── Services/
    └── Inventory/
        └── StockInService.php

database/
└── migrations/
    ├── xxxx_create_stock_ins_table.php
    ├── xxxx_create_stock_in_items_table.php
    ├── xxxx_create_inventory_ledger_table.php
    └── xxxx_create_stock_balances_table.php

resources/js/
├── Pages/
│   └── StockIn/
│
└── Components/
    └── StockIn/

tests/
└── Feature/
    └── StockIn/
```

---

# 53. Code Documentation

Setiap file wajib mengikuti:

```text id="9p3q0j"
docs/code-guide/00_CODE_DOCUMENTATION_STANDARD.md
```

Contoh Service:

```php id="1l4v4n"
/**
 * Stock In Service
 *
 * Purpose:
 * Handle Stock In business operations.
 *
 * Main Flow:
 * Validate
 * → Create Transaction
 * → Create Ledger
 * → Update Stock Balance
 * → Audit
 *
 * Important:
 * Inventory-changing operations must run
 * inside a database transaction.
 *
 * Related:
 * - StockIn
 * - StockInItem
 * - InventoryLedger
 * - StockBalance
 */
```

---

# 54. Git Branch

```text id="e7zq4p"
feature/stock-in
```

Dependency:

```text id="e9l6sq"
feature/master-data
        ↓
feature/item-management
        ↓
feature/warehouse
        ↓
feature/stock-in
```

---

# 55. Suggested Commits

```text id="2f4z5x"
feat(stock-in): add stock in models and migrations
feat(stock-in): add stock in CRUD
feat(stock-in): add stock in validation
feat(stock-in): add stock in authorization
feat(stock-in): add stock in workflow
feat(stock-in): add inventory ledger
feat(stock-in): add stock balance update
feat(stock-in): add atomic stock posting
feat(stock-in): add idempotency protection
feat(stock-in): add stock in audit logging
test(stock-in): add stock in workflow tests
test(stock-in): add inventory concurrency tests
docs(stock-in): document stock in code flow
```

---

# 56. Definition of Done

```text id="a8s1b6"
Code
    ✓ Stock In
    ✓ Stock In Items
    ✓ Transaction lifecycle
    ✓ Approval
    ✓ Posting

Inventory
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
    ✓ Approval UI
    ✓ Status display

Security
    ✓ IDOR protection
    ✓ Mass assignment protection
    ✓ Permission enforcement
    ✓ Scope enforcement
    ✓ Duplicate posting protection

Audit
    ✓ Transaction lifecycle audited
    ✓ Inventory changes traceable

Testing
    ✓ Workflow tests pass
    ✓ Inventory tests pass
    ✓ Concurrency tests pass

Documentation
    ✓ Code comments
    ✓ Maintenance guide
    ✓ Request flow
    ✓ Inventory flow

Git
    ✓ feature/stock-in
```

---

# 57. Final Stock In Architecture

```text id="dz3t7y"
                         STOCK IN
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
                ┌───────────┴───────────┐
                ▼                       ▼
             APPROVED                REJECTED
                │
                ▼
              POST
                │
        ┌───────┼────────┐
        ▼       ▼        ▼
      LEDGER  BALANCE   AUDIT
        │       │
        └───────┼────────┘
                ▼
             POSTED
```

---

# 58. Key Principle

Stock In menjawab:

```text id="6i8gq4"
"What inventory entered the warehouse?"
```

Tetapi yang paling penting:

```text id="p7y0oa"
DRAFT
→ no stock impact

SUBMITTED
→ no stock impact

APPROVED
→ no stock impact

POSTED
→ inventory changes
```

Dan ketika POST:

```text id="m0o9w8"
Stock In
   │
   ├── Transaction
   ├── Ledger (+)
   ├── Stock Balance
   └── Audit
```

semuanya harus diproses secara **atomic**.

Jadi ketika nanti kamu membuka kode Stock In dan ingin memahami:

> "Kenapa setelah klik Post stock bertambah?"

kamu bisa tracing:

```text id="l7v5r2"
Post Button
 ↓
POST /stock-in/{id}/post
 ↓
StockInController
 ↓
StockInService
 ↓
DB Transaction
 ├── InventoryLedger
 ├── StockBalance
 └── StockIn → POSTED
```

Itulah alur inti yang harus kamu pahami sebelum masuk ke implementasi sebenarnya.

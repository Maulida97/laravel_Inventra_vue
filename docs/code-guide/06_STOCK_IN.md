# Inventra

## Stock In Code Guide

**Document:** Stock In Code Guide
**Version:** V1.0
**Status:** Draft

---

# 1. Purpose

Stock In digunakan untuk mencatat barang yang masuk ke inventory.

Flow utama:

```text
Supplier / Source
      ↓
Stock In
      ↓
Warehouse
      ↓
Location
      ↓
Item
      ↓
Quantity
      ↓
Inventory Ledger
      ↓
Current Stock
```

Stock In harus menghasilkan perubahan inventory yang konsisten dan dapat diaudit.

---

# 2. Stock In Responsibility

Stock In bertanggung jawab terhadap:

- Pencatatan barang masuk.
- Item yang masuk.
- Quantity.
- Unit.
- Unit conversion.
- Warehouse tujuan.
- Location tujuan.
- Reference/source.
- Validasi.
- Authorization.
- Inventory ledger.
- Audit trail.

Stock In **tidak boleh mengubah stock secara sembarangan dari frontend**.

---

# 3. Stock In Architecture

```text
                    STOCK IN
                       │
                       ▼
                    Request
                       │
             ┌─────────┴─────────┐
             ▼                   ▼
        Validation           Authorization
             │                   │
             └─────────┬─────────┘
                       ▼
                    Service
                       │
                       ▼
                Database Transaction
                       │
             ┌─────────┴─────────┐
             ▼                   ▼
        Stock In Record      Stock Ledger
             │                   │
             └─────────┬─────────┘
                       ▼
                    Audit Log
```

---

# 4. Stock In Document

Setiap Stock In memiliki document identity.

Contoh:

```text
SI-2026-00001
```

Conceptual:

```text
Stock In
├── ID
├── Document Number
├── Date
├── Warehouse
├── Reference
├── Supplier
├── Status
├── Created By
└── Items
```

---

# 5. Stock In Status

Status harus menggambarkan lifecycle transaction.

Minimal:

```text
DRAFT
POSTED
CANCELLED
```

Flow:

```text
DRAFT
  ↓
POST
  ↓
POSTED
```

Cancellation:

```text
POSTED
  ↓
CANCEL
  ↓
CANCELLED
```

Business rule dapat menentukan apakah cancellation membutuhkan approval.

---

# 6. DRAFT

DRAFT berarti Stock In belum memengaruhi stock final.

```text
DRAFT
 ↓
No Ledger Effect
 ↓
No Stock Increase
```

User masih dapat melakukan perubahan sesuai permission.

---

# 7. POSTED

POSTED berarti transaction sudah resmi diproses.

```text
POSTED
 ↓
Create Ledger Entry
 ↓
Increase Inventory
```

Setelah posted, data transaction sebaiknya tidak diedit secara bebas.

---

# 8. CANCELLED

Cancellation harus menjaga historical record.

Jangan:

```text
DELETE Stock In
```

Gunakan:

```text
Stock In
 ↓
CANCELLED
```

Jika transaction sebelumnya sudah posted, pembatalan harus menghasilkan reversing movement sesuai business rule.

---

# 9. Stock In Header

Header menyimpan informasi transaction.

Contoh:

```text
stock_ins
├── id
├── document_number
├── transaction_date
├── warehouse_id
├── supplier_id
├── reference_number
├── notes
├── status
├── created_by
├── posted_at
└── posted_by
```

Tidak semua field harus ada jika belum diperlukan oleh PRD.

---

# 10. Stock In Detail

Satu Stock In dapat memiliki banyak item.

```text
Stock In
├── Item A
├── Item B
└── Item C
```

Concept:

```text
stock_in
   │
   └── stock_in_items
          ├── item
          ├── location
          ├── quantity
          └── unit
```

Relationship:

```text
StockIn
 └── hasMany
       ↓
 StockInItem
```

---

# 11. Stock In Quantity

Quantity terdiri dari:

```text
Input Quantity
Input Unit
Base Quantity
Base Unit
```

Contoh:

```text
Input:
6 BOX

Conversion:
1 BOX = 100 PCS

Result:
600 PCS
```

Inventory menggunakan base quantity sesuai desain inventory ledger.

---

# 12. Quantity Conversion Flow

```text
User Input
6 BOX
   ↓
Validate Unit
   ↓
Get Conversion
   ↓
Calculate
   ↓
600 PCS
   ↓
Create Ledger
```

Calculation dilakukan backend.

---

# 13. Floating Point / Precision

Quantity tidak boleh menggunakan tipe data yang dapat menghasilkan masalah precision untuk kebutuhan yang membutuhkan ketelitian.

Contoh:

```text
Decimal
```

digunakan sesuai kebutuhan item.

Jangan mengandalkan JavaScript floating point sebagai sumber kebenaran quantity.

---

# 14. Warehouse Validation

Sebelum Stock In diproses:

```text
Warehouse
 ↓
Exists?
 ↓
ACTIVE?
 ↓
User Authorized?
```

Jika gagal:

```text
DENY
```

---

# 15. Location Validation

Location harus:

```text
Exists
+
ACTIVE
+
Belongs to Selected Warehouse
```

Contoh:

```text
Warehouse:
WH-001

Location:
A-01
```

harus memenuhi:

```text
A-01.warehouse_id = WH-001.id
```

---

# 16. Item Validation

Setiap item harus:

```text
Exists
+
ACTIVE
+
Valid Unit
```

Jika item inactive:

```text
Stock In
 ↓
Inactive Item
 ↓
DENY
```

Historical Stock In tetap dapat menampilkan item tersebut.

---

# 17. Supplier Validation

Jika supplier digunakan:

```text
Supplier
 ↓
Exists?
 ↓
ACTIVE?
```

Stock In kemudian menyimpan reference supplier.

---

# 18. Authorization

Stock In membutuhkan:

```text
Authentication
+
Permission
+
Warehouse Scope
+
Policy
```

Contoh:

```text
Warehouse Staff
+
stock.in.create
+
WH-001
=
ALLOW
```

---

# 19. Stock In Policy

Concept:

```text
StockInPolicy
├── view
├── create
├── update
├── post
└── cancel
```

Permission dan policy memiliki tanggung jawab berbeda.

```text
Permission
→ Apakah user memiliki kemampuan?

Policy / Scope
→ Apakah user boleh melakukan action terhadap resource ini?
```

---

# 20. Create Stock In Flow

```text
Vue
 ↓
Inertia POST
 ↓
Route
 ↓
Form Request
 ↓
Authorization
 ↓
StockInService
 ↓
Create DRAFT
 ↓
Database
 ↓
Audit
```

Pada tahap create draft:

```text
No Stock Change
```

---

# 21. Post Stock In Flow

Posting adalah operasi penting.

```text
User
 ↓
POST
 ↓
Authorization
 ↓
Validate Status
 ↓
Validate Items
 ↓
Validate Warehouse
 ↓
Database Transaction
 ↓
Create Ledger Entries
 ↓
Update Inventory
 ↓
POSTED
 ↓
Audit
```

---

# 22. Database Transaction

Posting Stock In harus menggunakan database transaction.

Concept:

```text
BEGIN TRANSACTION

Create / validate Stock In
        ↓
Create Stock In Items
        ↓
Create Ledger Entries
        ↓
Update Inventory
        ↓
Update Status = POSTED
        ↓
Create Audit

COMMIT
```

Jika salah satu gagal:

```text
ROLLBACK
```

Tujuannya mencegah kondisi:

```text
Stock In = POSTED
Ledger = missing
```

atau:

```text
Ledger = created
Stock In = still DRAFT
```

---

# 23. Why Atomic Transaction Matters

Contoh Stock In:

```text
Item A +100
Item B +50
Item C +25
```

Jika Item C gagal:

```text
Without DB Transaction:
A = +100
B = +50
C = failed
```

Stock menjadi tidak konsisten.

Dengan transaction:

```text
A = rollback
B = rollback
C = rollback
```

Semua operasi gagal bersama.

---

# 24. Inventory Ledger

Stock In menghasilkan ledger entry.

Concept:

```text
Stock In
 ↓
Ledger
 ↓
IN +100
```

Ledger adalah historical movement.

Contoh:

```text
2026-08-30
ITM-001
WH-001
A-01
IN
+100 PCS
Reference: SI-2026-00001
```

---

# 25. Ledger Principle

Ledger sebaiknya bersifat append-oriented.

Artinya:

```text
Historical Entry
       ↓
Do not casually overwrite
```

Jika terjadi koreksi:

```text
New Movement
```

lebih aman daripada mengubah historical movement secara langsung.

---

# 26. Current Inventory

Current inventory dapat direpresentasikan sebagai aggregate state.

Concept:

```text
Ledger
├── +100
├── -20
├── +50
└── -10

Current Stock
=
120
```

Tergantung implementasi Inventra, current inventory dapat:

```text
Calculated from ledger
```

atau menggunakan:

```text
Inventory balance table
```

yang diperbarui secara atomic.

Yang penting:

```text
Ledger
+
Current Balance
```

harus tetap konsisten.

---

# 27. Stock Increase

Stock In:

```text
Previous Stock
+
Base Quantity
=
New Stock
```

Contoh:

```text
Previous:
500 PCS

Stock In:
100 PCS

New:
600 PCS
```

---

# 28. Negative Stock

Stock In sendiri tidak menghasilkan negative stock.

Namun sistem harus tetap memiliki inventory invariant:

```text
Stock balance
>=
0
```

untuk item yang tidak mengizinkan negative stock.

Negative stock policy harus ditentukan di Inventory/Stock Out layer.

---

# 29. Duplicate Posting Protection

User dapat mengklik Post dua kali.

Contoh:

```text
Click Post
 ↓
Request 1

Click Post
 ↓
Request 2
```

Sistem harus mencegah:

```text
+100
+
+100
=
+200
```

padahal hanya satu Stock In.

Gunakan kombinasi:

```text
Status validation
+
Database transaction
+
Concurrency control / idempotency strategy
```

---

# 30. Status Transition

Valid transition:

```text
DRAFT → POSTED
DRAFT → CANCELLED
POSTED → CANCELLED
```

Namun:

```text
CANCELLED → POSTED
```

harus ditolak kecuali business rule secara eksplisit mendukungnya.

---

# 31. Posting Service

Business logic posting berada di service/domain layer.

Contoh conceptual:

```text
StockInService
├── create()
├── update()
├── post()
└── cancel()
```

`post()` menangani:

```text
Validation
+
Ledger
+
Inventory
+
Status
+
Audit
```

dalam satu atomic operation.

---

# 32. Controller Responsibility

Controller:

```text
Receive Request
 ↓
Authorize
 ↓
Validate
 ↓
Call Service
 ↓
Return Response
```

Controller tidak melakukan:

```text
Loop Item
 ↓
Update Stock
 ↓
Insert Ledger
```

secara langsung jika logic tersebut merupakan business operation.

---

# 33. Form Request

Contoh validation:

```text
warehouse_id
→ required
→ exists

supplier_id
→ exists

transaction_date
→ valid date

items
→ required
→ array

items.*.item_id
→ exists

items.*.location_id
→ exists

items.*.quantity
→ positive

items.*.unit_id
→ exists
```

Business rules tambahan dilakukan di service/policy.

---

# 34. Backend Revalidation

Frontend dapat melakukan:

```text
Disable Post button
```

tetapi backend tetap harus memvalidasi:

```text
Status
Warehouse
Location
Item
Quantity
Unit
Permission
Scope
```

Frontend bukan security boundary.

---

# 35. Stock In UI

Conceptual pages:

```text
resources/js/Pages/StockIns/
├── Index.vue
├── Create.vue
├── Edit.vue
├── Show.vue
└── Components/
```

Components dapat berisi:

```text
StockInForm
StockInItemTable
ItemSelector
WarehouseSelector
LocationSelector
```

---

# 36. Dependent Selection

UI dapat menggunakan dependency:

```text
Warehouse
 ↓
Location
```

Ketika warehouse berubah:

```text
Location list
 ↓
Reload
```

Hanya location milik warehouse tersebut yang ditampilkan.

Tetapi backend tetap melakukan validation.

---

# 37. Item Selection

Flow:

```text
Select Item
 ↓
Select Unit
 ↓
Enter Quantity
 ↓
Calculate Preview
```

Frontend boleh menampilkan preview:

```text
6 BOX
=
600 PCS
```

Tetapi final calculation dilakukan backend.

---

# 38. Draft Editing

DRAFT dapat diedit sesuai permission.

Contoh:

```text
DRAFT
 ↓
Edit quantity
 ↓
Edit item
 ↓
Edit location
```

Setelah POSTED:

```text
POSTED
 ↓
Read-only
```

atau menggunakan reversal/correction workflow.

---

# 39. Cancellation

Jika DRAFT:

```text
Cancel
 ↓
CANCELLED
```

tidak ada ledger reversal karena belum pernah posted.

Jika POSTED:

```text
POSTED
 ↓
Cancel
 ↓
Reversal
 ↓
CANCELLED
```

Reversal harus tercatat sebagai movement baru jika business rule mengizinkan cancellation.

---

# 40. Audit

Event penting:

```text
Stock In Created
Stock In Updated
Stock In Posted
Stock In Cancelled
```

Audit mencatat minimal:

```text
Actor
Action
Resource
Timestamp
Reference
```

---

# 41. Reference Number

Stock In dapat memiliki external reference.

Contoh:

```text
Supplier Invoice:
INV-2026-001
```

atau:

```text
Delivery Note:
DN-00123
```

Reference number membantu traceability.

---

# 42. Search & Filter

Stock In list dapat difilter berdasarkan:

```text
Document Number
Date
Warehouse
Supplier
Status
Created By
```

Gunakan:

```text
Pagination
+
Indexed Columns
+
Scoped Query
```

---

# 43. Query Optimization

Stock In list dapat membutuhkan relationship:

```text
Stock In
 ├── Warehouse
 ├── Supplier
 └── Creator
```

Jika ditampilkan bersama:

```php id="w3r7j2"
StockIn::with([
    'warehouse',
    'supplier',
    'creator',
])->paginate(20);
```

Gunakan eager loading untuk menghindari N+1 query.

---

# 44. Index Recommendation

Kolom yang kemungkinan sering digunakan:

```text
stock_ins.document_number
stock_ins.transaction_date
stock_ins.warehouse_id
stock_ins.supplier_id
stock_ins.status
stock_ins.created_by
```

Detail:

```text
stock_in_items.stock_in_id
stock_in_items.item_id
stock_in_items.location_id
```

Ledger:

```text
inventory_ledgers.item_id
inventory_ledgers.warehouse_id
inventory_ledgers.location_id
inventory_ledgers.created_at
```

Index final tetap harus berdasarkan query pattern dan execution plan.

---

# 45. Concurrency

Stock In dapat diproses bersamaan.

Contoh:

```text
User A → +100
User B → +50
```

Sistem harus menghasilkan:

```text
Final Stock
=
Previous + 150
```

bukan kehilangan salah satu update.

Gunakan database transaction dan mekanisme locking/concurrency yang sesuai ketika memperbarui balance.

---

# 46. Race Condition Example

Misalnya stock:

```text
500
```

Request A:

```text
500 + 100
```

Request B:

```text
500 + 50
```

Jika keduanya membaca `500` lalu menulis:

```text
600
550
```

hasil terakhir dapat salah.

Maka update inventory harus atomic atau menggunakan locking yang tepat.

---

# 47. Security

Stock In harus mencegah:

```text
Unauthorized Warehouse
Unauthorized Item
Unauthorized Location
Invalid Quantity
Duplicate Posting
Tampering Posted Transaction
```

Server adalah security boundary.

---

# 48. Common Mistakes

### Mistake 1 — Update Stock dari Vue

```text
Vue
 ↓
stock = stock + quantity
```

Tidak boleh.

---

### Mistake 2 — Update Stock Tanpa DB Transaction

```text
Create Stock In
 ↓
Update Stock
 ↓
Ledger failed
```

dapat menghasilkan inconsistency.

---

### Mistake 3 — Tidak Memvalidasi Location

Location harus dipastikan milik warehouse yang dipilih.

---

### Mistake 4 — Post Dua Kali

Harus ada protection terhadap duplicate posting.

---

### Mistake 5 — Mengedit POSTED Transaction secara langsung

Historical movement harus tetap traceable.

---

### Mistake 6 — Mengandalkan Frontend Validation

Frontend validation hanya UX.

Backend wajib melakukan validasi ulang.

---

# 49. Maintenance Guide

### "Saya mau mengubah tampilan Stock In."

Cari:

```text
resources/js/Pages/StockIns/
```

---

### "Saya mau mengubah form Stock In."

Cari:

```text
StockIns/Create.vue
StockInForm.vue
```

---

### "Saya mau mengubah validation."

Cari:

```text
StoreStockInRequest
UpdateStockInRequest
```

---

### "Saya mau mengubah siapa yang boleh membuat Stock In."

Cari:

```text
StockInPolicy
+
stock.in.create permission
+
Warehouse Scope
```

---

### "Saya mau mengubah proses Post."

Cari:

```text
StockInService::post()
```

Kemudian ikuti:

```text
post()
 ↓
Ledger
 ↓
Inventory
 ↓
Status
 ↓
Audit
```

---

### "Stock tidak bertambah setelah Stock In."

Periksa:

```text
[ ] Stock In status?
[ ] post() berhasil?
[ ] Database transaction?
[ ] Ledger created?
[ ] Inventory balance updated?
[ ] Item correct?
[ ] Warehouse correct?
[ ] Location correct?
[ ] Quantity conversion correct?
```

---

### "Stock bertambah dua kali."

Periksa:

```text
[ ] Duplicate POST?
[ ] Status transition?
[ ] Retry?
[ ] Idempotency?
[ ] Ledger duplicated?
[ ] Inventory update duplicated?
```

---

# 50. Code Reading Flow

Untuk memahami Stock In:

```text
Index.vue
 ↓
Create.vue
 ↓
Route
 ↓
Controller
 ↓
Form Request
 ↓
Policy
 ↓
StockInService
 ↓
StockIn Model
 ↓
StockInItem Model
 ↓
Inventory/Ledger Service
 ↓
Database
 ↓
Audit
```

Untuk memahami kenapa stock berubah:

```text
POST
 ↓
StockInService
 ↓
Database Transaction
 ↓
Ledger Entry
 ↓
Inventory Balance
```

Untuk memahami authorization:

```text
User
 ↓
Role
 ↓
Permission
 ↓
Warehouse Scope
 ↓
StockInPolicy
```

---

# 51. Debugging Checklist

Jika Stock In gagal dibuat:

```text
[ ] Authentication
[ ] Permission
[ ] Warehouse scope
[ ] Policy
[ ] Validation
[ ] Item exists
[ ] Item active
[ ] Unit valid
[ ] Location valid
[ ] Location belongs to warehouse
```

Jika Stock In berhasil tetapi stock tidak berubah:

```text
[ ] Status POSTED?
[ ] post() executed?
[ ] Ledger exists?
[ ] Inventory balance updated?
[ ] Transaction committed?
```

Jika stock bertambah dua kali:

```text
[ ] Duplicate posting
[ ] Duplicate ledger
[ ] Retry request
[ ] Concurrency handling
[ ] Idempotency
```

---

# 52. Testing

Minimal:

```text
[ ] Create Stock In draft
[ ] Update draft
[ ] Post Stock In
[ ] Cancel draft
[ ] Cancel posted Stock In if supported
[ ] Stock increases correctly
[ ] Unit conversion works
[ ] Invalid item rejected
[ ] Inactive item rejected
[ ] Invalid warehouse rejected
[ ] Invalid location rejected
[ ] Cross-warehouse location rejected
[ ] Unauthorized warehouse rejected
[ ] Duplicate posting prevented
[ ] Ledger created exactly once
[ ] Audit created
[ ] Historical transaction remains accessible
[ ] Concurrent Stock In handled correctly
```

---

# 53. Definition of Done

```text
[ ] Stock In CRUD
[ ] Draft lifecycle
[ ] Posting lifecycle
[ ] Cancellation rule
[ ] Validation
[ ] Authorization
[ ] Warehouse scope
[ ] Location validation
[ ] Unit conversion
[ ] Inventory ledger
[ ] Current stock update
[ ] Database transaction
[ ] Concurrency handling
[ ] Duplicate posting protection
[ ] Audit
[ ] Index reviewed
[ ] Query optimized
[ ] Tests
[ ] Documentation
```

---

# 54. Final Stock In Flow

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
                         ROUTE
                           │
                           ▼
                      CONTROLLER
                           │
                  ┌────────┴────────┐
                  ▼                 ▼
             VALIDATION        AUTHORIZATION
                                    │
                          ┌─────────┴─────────┐
                          ▼                   ▼
                     PERMISSION            POLICY
                                                │
                                                ▼
                                             SCOPE
                          └─────────┬─────────┘
                                    ▼
                              STOCK IN SERVICE
                                    │
                              POST OPERATION
                                    │
                                    ▼
                         DATABASE TRANSACTION
                                    │
                    ┌───────────────┼───────────────┐
                    ▼               ▼               ▼
                Stock In        Ledger          Inventory
                    │               │               │
                    └───────────────┼───────────────┘
                                    ▼
                                  AUDIT
                                    │
                                    ▼
                                  COMMIT
                                    │
                                    ▼
                                   VUE
```

---

# 55. Key Principle

Stock In bukan sekadar:

```text
"Tambah angka stock"
```

Stock In adalah:

```text
Business Transaction
        +
Inventory Movement
        +
Historical Record
        +
Auditability
```

Prinsip utama Inventra:

```text
DRAFT
→ No stock effect

POSTED
→ Create inventory movement

CANCELLED
→ Preserve history

LEDGER
→ Record movement

INVENTORY
→ Represent current balance

AUDIT
→ Record accountability
```

Dan yang paling penting:

```text
Frontend
→ User Interface

Service
→ Business Logic

Database Transaction
→ Atomicity

Ledger
→ Historical Movement

Inventory
→ Current State
```

Jangan menaruh logic perubahan stock di Vue atau controller.

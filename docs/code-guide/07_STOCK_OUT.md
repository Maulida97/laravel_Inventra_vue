# Inventra

## Stock Out Code Guide

**Document:** Stock Out Code Guide
**Version:** V1.0
**Status:** Draft

---

# 1. Purpose

Stock Out digunakan untuk mencatat barang yang keluar dari inventory.

Contoh:

- Barang digunakan oleh Department IT.
- Barang diberikan kepada Department QC.
- Barang dikeluarkan untuk kebutuhan operasional.
- Barang dikeluarkan berdasarkan request yang sudah disetujui.

Flow utama:

```text
Request / Department
        ↓
Stock Out
        ↓
Warehouse
        ↓
Location
        ↓
Item
        ↓
Quantity
        ↓
Validate Available Stock
        ↓
Inventory Ledger
        ↓
Current Stock
        ↓
Audit Log
```

---

# 2. Stock Out Responsibility

Stock Out bertanggung jawab terhadap:

- Pencatatan barang keluar.
- Item.
- Quantity.
- Unit.
- Warehouse source.
- Location source.
- Destination/recipient.
- Department/requester.
- Stock availability.
- Authorization.
- Approval jika diperlukan.
- Inventory ledger.
- Audit trail.

Stock Out **tidak boleh langsung mengurangi stock dari frontend**.

---

# 3. Stock Out Architecture

```text
                     STOCK OUT
                         │
                         ▼
                      Request
                         │
              ┌──────────┴──────────┐
              ▼                     ▼
         Validation            Authorization
              │                     │
              └──────────┬──────────┘
                         ▼
                    StockOutService
                         │
                         ▼
                Database Transaction
                         │
              ┌──────────┴──────────┐
              ▼                     ▼
        Stock Out Record        Stock Ledger
                                    │
                                    ▼
                              Inventory Balance
                                    │
                                    ▼
                                Audit Log
```

---

# 4. Stock Out Document

Setiap Stock Out memiliki document number.

Contoh:

```text
SO-2026-00001
```

Conceptual:

```text
Stock Out
├── ID
├── Document Number
├── Transaction Date
├── Warehouse
├── Requester
├── Department
├── Destination
├── Reason
├── Status
├── Created By
├── Posted By
└── Items
```

---

# 5. Stock Out Status

Minimal:

```text
DRAFT
PENDING_APPROVAL
APPROVED
POSTED
REJECTED
CANCELLED
```

Tidak semua Stock Out harus melewati approval.

Flow dapat berbeda berdasarkan business rule.

Tanpa approval:

```text
DRAFT
  ↓
POSTED
```

Dengan approval:

```text
DRAFT
  ↓
PENDING_APPROVAL
  ↓
APPROVED
  ↓
POSTED
```

Rejected:

```text
PENDING_APPROVAL
  ↓
REJECTED
```

---

# 6. DRAFT

DRAFT berarti transaction belum final.

```text
DRAFT
 ↓
No Stock Reduction
```

User dapat mengubah detail selama masih diperbolehkan.

---

# 7. PENDING_APPROVAL

Jika approval diperlukan:

```text
DRAFT
 ↓
Submit
 ↓
PENDING_APPROVAL
```

Pada tahap ini:

```text
No final stock reduction
```

Stock belum dianggap keluar secara final.

---

# 8. APPROVED

Approval berarti request telah mendapatkan otorisasi.

```text
APPROVED
 ↓
Ready for Posting
```

Approval **tidak otomatis berarti stock sudah berkurang**, kecuali business rule secara eksplisit mendefinisikannya demikian.

Dalam desain Inventra:

```text
APPROVED
→ Authorization

POSTED
→ Inventory Movement
```

---

# 9. POSTED

POSTED adalah titik ketika stock benar-benar berkurang.

```text
POSTED
 ↓
Ledger OUT
 ↓
Inventory decreases
```

Contoh:

```text
Current Stock = 100 PCS

Stock Out = 20 PCS

New Stock = 80 PCS
```

---

# 10. REJECTED

REJECTED berarti approval tidak diberikan.

```text
PENDING_APPROVAL
 ↓
REJECTED
```

Tidak ada stock movement.

```text
REJECTED
→ No Stock Reduction
```

---

# 11. CANCELLED

Cancellation harus mempertahankan historical record.

Jangan:

```text
DELETE Stock Out
```

Gunakan status:

```text
CANCELLED
```

Jika Stock Out sudah POSTED, cancellation harus menggunakan reversal movement sesuai business rule.

---

# 12. Stock Out Header

Conceptual:

```text
stock_outs
├── id
├── document_number
├── transaction_date
├── warehouse_id
├── requester_id
├── department_id
├── destination
├── reason
├── reference_number
├── status
├── created_by
├── approved_by
├── approved_at
├── posted_by
└── posted_at
```

Tidak semua field harus digunakan jika belum dibutuhkan oleh PRD.

---

# 13. Stock Out Detail

Satu Stock Out dapat memiliki banyak item.

```text
Stock Out
├── Item A
├── Item B
└── Item C
```

Concept:

```text
stock_out_items
├── stock_out_id
├── item_id
├── location_id
├── quantity
└── unit_id
```

---

# 14. Quantity

Quantity mengikuti konsep base unit.

```text
Input Quantity
+
Input Unit
        ↓
Conversion
        ↓
Base Quantity
```

Contoh:

```text
2 BOX
1 BOX = 100 PCS

Base Quantity = 200 PCS
```

Stock yang dikurangi:

```text
-200 PCS
```

---

# 15. Quantity Validation

Quantity harus:

```text
> 0
```

Invalid:

```text
0
-10
null
```

Selain validation format, backend harus memvalidasi ketersediaan stock.

---

# 16. Warehouse Validation

Sebelum Stock Out:

```text
Warehouse
 ↓
Exists?
 ↓
ACTIVE?
 ↓
User authorized?
```

Jika gagal:

```text
DENY
```

---

# 17. Location Validation

Location harus:

```text
Exists
+
ACTIVE
+
Belongs to Warehouse
```

Contoh:

```text
Warehouse = WH-001
Location = A-01
```

harus:

```text
A-01.warehouse_id = WH-001.id
```

---

# 18. Item Validation

Item harus:

```text
Exists
+
ACTIVE
+
Valid Unit
```

Inactive item tidak boleh digunakan untuk transaksi baru.

Historical transaction tetap dapat menampilkan item tersebut.

---

# 19. Available Stock

Sebelum posting:

```text
Available Stock
        ↓
Compare
        ↓
Requested Quantity
```

Contoh:

```text
Available = 100
Request   = 30

ALLOW
```

Contoh:

```text
Available = 100
Request   = 120

DENY
```

---

# 20. Negative Stock

Default Inventra:

```text
Negative Stock = NOT ALLOWED
```

Contoh:

```text
Stock = 50

Stock Out = 60

Result:
REJECT
```

Jangan menghasilkan:

```text
-10
```

kecuali business rule tertentu memang secara eksplisit mengizinkannya.

---

# 21. Important: Stock Validation Is Not Enough

Kesalahan umum:

```text
Check stock
 ↓
If enough
 ↓
Update stock
```

Ini belum aman terhadap concurrent request.

Contoh:

```text
Stock = 100

User A requests 80
User B requests 50
```

Keduanya dapat membaca:

```text
Available = 100
```

Kemudian keduanya lolos validation.

Hasil:

```text
100 - 80 - 50
=
-30
```

Padahal negative stock dilarang.

---

# 22. Concurrency Protection

Stock validation dan stock update harus berada dalam database transaction.

Concept:

```text
BEGIN TRANSACTION

Lock inventory row
        ↓
Read current stock
        ↓
Validate quantity
        ↓
Create ledger
        ↓
Update inventory
        ↓
Mark Stock Out POSTED

COMMIT
```

Jika gagal:

```text
ROLLBACK
```

---

# 23. Row Locking

Saat mengambil stock, inventory row yang relevan dapat dikunci selama transaction.

Concept Laravel:

```php
$inventory = Inventory::query()
    ->where('item_id', $itemId)
    ->where('warehouse_id', $warehouseId)
    ->where('location_id', $locationId)
    ->lockForUpdate()
    ->first();
```

Tujuannya mencegah dua transaction mengubah balance yang sama secara tidak aman.

---

# 24. Why `lockForUpdate()` Matters

Contoh:

```text
Stock = 100
```

Request A:

```text
Lock row
 ↓
Read 100
 ↓
Take 80
 ↓
Stock = 20
 ↓
Commit
```

Request B menunggu lock.

Kemudian:

```text
Read 20
 ↓
Request 50
 ↓
Insufficient Stock
 ↓
Rollback
```

Hasil akhir:

```text
Stock = 20
```

Tidak menjadi negative.

---

# 25. Database Transaction

Posting Stock Out harus atomic.

```text
BEGIN

Validate status
 ↓
Lock inventory
 ↓
Validate stock
 ↓
Create ledger
 ↓
Update inventory
 ↓
Update Stock Out = POSTED
 ↓
Create audit

COMMIT
```

Jika salah satu gagal:

```text
ROLLBACK
```

---

# 26. Stock Out Posting

Business logic berada di service.

Concept:

```text
StockOutService
├── create()
├── update()
├── submit()
├── approve()
├── reject()
├── post()
└── cancel()
```

`post()` adalah operation paling sensitif.

---

# 27. Posting Algorithm

Conceptual:

```text
1. Check authorization
2. Check status
3. Begin DB transaction
4. Load inventory
5. Lock inventory row
6. Validate available stock
7. Convert quantity to base unit
8. Create OUT ledger
9. Decrease inventory balance
10. Mark Stock Out POSTED
11. Create audit
12. Commit
```

Jika langkah 5–11 gagal:

```text
Rollback everything
```

---

# 28. Ledger Entry

Stock Out menghasilkan:

```text
OUT
```

Contoh:

```text
2026-08-30
ITM-001
WH-001
A-01
OUT
-20 PCS
Reference:
SO-2026-00001
```

Ledger menjadi historical source untuk movement.

---

# 29. Inventory Balance

Sebelum:

```text
100 PCS
```

Stock Out:

```text
20 PCS
```

Sesudah:

```text
80 PCS
```

Concept:

```text
Previous Balance
-
Base Quantity
=
New Balance
```

---

# 30. Inventory Ledger vs Inventory Balance

Ledger:

```text
Apa yang terjadi?
```

Balance:

```text
Berapa stock sekarang?
```

Contoh:

```text
Ledger
+100
-20
+50
-10
```

Balance:

```text
120
```

Keduanya harus konsisten.

---

# 31. Department Destination

Stock Out dapat memiliki destination department.

Contoh:

```text
Stock Out
 ↓
IT Department
```

Atau:

```text
Stock Out
 ↓
QC Department
```

Namun **department destination tidak otomatis berarti semua staff department tersebut boleh melakukan Stock Out**.

Authorization tetap mengikuti permission dan scope.

---

# 32. Department-Specific Access

Sesuai business rule Inventra:

```text
Department
+
Item Category / Item Scope
```

dapat digunakan untuk menentukan siapa yang boleh request/menggunakan item tertentu.

Contoh:

```text
IT
→ IT Equipment

QC
→ QC Equipment
```

Tetapi jangan melakukan authorization hanya di frontend.

Backend harus memeriksa scope tersebut.

---

# 33. Requester vs Operator

Pisahkan:

```text
Requester
```

dan:

```text
Created By / Operator
```

Contoh:

```text
Requester:
Andi - IT

Created By:
Warehouse Staff
```

Artinya:

```text
Andi meminta barang
Warehouse Staff memproses transaksi
```

Ini penting untuk audit.

---

# 34. Approval

Jika Stock Out membutuhkan approval:

```text
Requester
 ↓
Create DRAFT
 ↓
Submit
 ↓
Approver
 ↓
Approve
 ↓
Warehouse Staff
 ↓
Post
```

Approver tidak harus sama dengan operator.

---

# 35. Approval Authorization

Approver harus divalidasi:

```text
User
+
Approval Permission
+
Relevant Department/Scope
```

Tidak cukup hanya:

```text
is_admin = true
```

gunakan permission/policy yang jelas.

---

# 36. Approval Does Not Equal Posting

Pisahkan:

```text
APPROVED
```

dengan:

```text
POSTED
```

Karena:

```text
Approved
→ Business authorization

Posted
→ Inventory changed
```

Ini membuat audit dan debugging lebih mudah.

---

# 37. Duplicate Posting Protection

Masalah:

```text
User clicks POST twice
```

Jangan sampai:

```text
-20
-20
=
-40
```

padahal hanya satu transaction.

Backend harus memeriksa status:

```text
DRAFT / APPROVED
```

sebelum posting.

Kemudian gunakan:

```text
Database Transaction
+
Row Lock
+
Status Guard
+
Idempotency strategy where appropriate
```

---

# 38. Status Guard

Contoh:

```text
if status != APPROVED:
    reject
```

atau jika approval tidak digunakan:

```text
if status != DRAFT:
    reject
```

Setelah POSTED:

```text
POSTED
→ cannot post again
```

---

# 39. Cancellation

### DRAFT

```text
DRAFT
 ↓
CANCELLED
```

Tidak ada stock effect.

### APPROVED

```text
APPROVED
 ↓
CANCELLED
```

Tidak ada stock effect.

### POSTED

```text
POSTED
 ↓
Reversal
 ↓
CANCELLED
```

Reversal harus dicatat sebagai movement baru.

---

# 40. Reversal Example

Stock awal:

```text
100
```

Stock Out:

```text
-20
```

Stock:

```text
80
```

Cancellation:

```text
+20
```

Stock:

```text
100
```

Ledger:

```text
+100
-20
+20
```

Historical record tetap ada.

---

# 41. Controller Responsibility

Controller hanya mengatur flow request.

```text
Request
 ↓
Authorize
 ↓
Validate
 ↓
Service
 ↓
Response
```

Jangan memasukkan seluruh logic stock ke controller.

---

# 42. Form Request

Contoh validation:

```text
warehouse_id
→ required
→ exists

items
→ required
→ array

items.*.item_id
→ required
→ exists

items.*.location_id
→ required
→ exists

items.*.quantity
→ required
→ numeric
→ > 0

items.*.unit_id
→ required
→ exists
```

Business validation tetap dilakukan di service.

---

# 43. Backend Validation

Frontend:

```text
Quantity > 0
```

Backend:

```text
Quantity > 0
+
Item valid
+
Warehouse valid
+
Location valid
+
Unit valid
+
Permission valid
+
Scope valid
+
Stock sufficient
```

Frontend bukan security boundary.

---

# 44. Stock Out UI

Conceptual:

```text
resources/js/Pages/StockOuts/
├── Index.vue
├── Create.vue
├── Edit.vue
├── Show.vue
└── Components/
```

Components:

```text
StockOutForm
StockOutItemTable
ItemSelector
WarehouseSelector
LocationSelector
RequesterSelector
```

---

# 45. Dependent Warehouse → Location

UI:

```text
Warehouse
 ↓
Location
```

Ketika warehouse berubah:

```text
Reload Locations
```

Hanya location warehouse tersebut yang ditampilkan.

Backend tetap melakukan validation.

---

# 46. Available Stock Display

UI dapat menampilkan:

```text
Available:
100 PCS

Requested:
20 PCS
```

Setelah user mengubah quantity:

```text
Remaining:
80 PCS
```

Tetapi angka pada UI hanya preview.

Saat POST:

```text
Backend
 ↓
Lock
 ↓
Read latest stock
 ↓
Validate
```

---

# 47. Search & Filter

Stock Out list dapat difilter:

```text
Document Number
Date
Warehouse
Department
Requester
Status
Created By
```

Gunakan pagination.

---

# 48. Query Optimization

Jika list menampilkan:

```text
Stock Out
+
Warehouse
+
Requester
+
Department
```

gunakan eager loading sesuai kebutuhan.

Concept:

```php
StockOut::with([
    'warehouse',
    'requester',
    'department',
])
->paginate(20);
```

Hindari N+1 query.

---

# 49. Index Recommendation

Stock Out:

```text
stock_outs.document_number
stock_outs.transaction_date
stock_outs.warehouse_id
stock_outs.department_id
stock_outs.requester_id
stock_outs.status
stock_outs.created_by
```

Detail:

```text
stock_out_items.stock_out_id
stock_out_items.item_id
stock_out_items.location_id
```

Inventory:

```text
inventory.item_id
inventory.warehouse_id
inventory.location_id
```

Ledger:

```text
inventory_ledgers.item_id
inventory_ledgers.warehouse_id
inventory_ledgers.location_id
inventory_ledgers.created_at
```

Index final harus mengikuti query pattern aktual dan execution plan.

---

# 50. Security

Stock Out harus mencegah:

```text
Unauthorized warehouse
Unauthorized department
Unauthorized item
Unauthorized location
Insufficient stock
Negative stock
Duplicate posting
Tampering
Unauthorized cancellation
Unauthorized approval
```

Security harus berada di backend.

---

# 51. Common Mistakes

### Mistake 1 — Mengurangi stock dari Vue

```text
Vue
 ↓
stock -= quantity
```

Tidak boleh.

---

### Mistake 2 — Check stock tanpa locking

```text
Check
 ↓
Update
```

rentan race condition.

---

### Mistake 3 — Tidak menggunakan database transaction

Ledger dan balance dapat menjadi tidak konsisten.

---

### Mistake 4 — Menganggap approval = stock keluar

Approval dan posting adalah dua event berbeda.

---

### Mistake 5 — Menghapus POSTED transaction

Historical transaction harus tetap traceable.

---

### Mistake 6 — Hanya melakukan filter authorization di frontend

User dapat memanipulasi request.

Backend wajib melakukan authorization.

---

# 52. Maintenance Guide

### "Saya mau mengubah tampilan Stock Out."

Cari:

```text
resources/js/Pages/StockOuts/
```

---

### "Saya mau mengubah form Stock Out."

Cari:

```text
StockOuts/Create.vue
StockOutForm.vue
```

---

### "Saya mau mengubah validation."

Cari:

```text
StoreStockOutRequest
UpdateStockOutRequest
```

---

### "Saya mau mengubah siapa yang boleh melakukan Stock Out."

Periksa:

```text
StockOutPolicy
+
Permission
+
Warehouse Scope
+
Department / Item Scope
```

---

### "Saya mau mengubah proses pengurangan stock."

Cari:

```text
StockOutService::post()
```

Kemudian ikuti:

```text
post()
 ↓
Inventory Lock
 ↓
Stock Validation
 ↓
Ledger
 ↓
Inventory Balance
 ↓
Status
 ↓
Audit
```

---

### "Stock menjadi minus."

Periksa:

```text
[ ] Stock validation
[ ] lockForUpdate()
[ ] Database transaction
[ ] Quantity conversion
[ ] Duplicate posting
[ ] Concurrent requests
```

---

### "Stock berkurang dua kali."

Periksa:

```text
[ ] Duplicate request
[ ] Status guard
[ ] Ledger duplicated
[ ] Inventory update duplicated
[ ] Retry
[ ] Concurrency
```

---

# 53. Code Reading Flow

Untuk memahami Stock Out:

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
StockOutService
 ↓
Inventory Service
 ↓
Ledger Service
 ↓
Model
 ↓
Database
 ↓
Audit
```

Untuk memahami kenapa stock berkurang:

```text
POST
 ↓
StockOutService::post()
 ↓
DB Transaction
 ↓
Lock Inventory
 ↓
Validate Stock
 ↓
Create OUT Ledger
 ↓
Decrease Inventory
 ↓
POSTED
 ↓
Audit
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
Department / Item Scope
 ↓
StockOutPolicy
```

---

# 54. Debugging Checklist

Jika Stock Out gagal:

```text
[ ] Authentication
[ ] Permission
[ ] Warehouse scope
[ ] Department scope
[ ] Policy
[ ] Validation
[ ] Item exists
[ ] Item active
[ ] Unit valid
[ ] Location valid
[ ] Location belongs to warehouse
[ ] Stock available
```

Jika stock tidak berkurang:

```text
[ ] Status POSTED?
[ ] post() executed?
[ ] Ledger created?
[ ] Inventory updated?
[ ] Transaction committed?
```

Jika stock menjadi minus:

```text
[ ] Stock validation
[ ] Row locking
[ ] Transaction
[ ] Duplicate request
[ ] Concurrency
```

---

# 55. Testing

Minimal:

```text
[ ] Create draft
[ ] Update draft
[ ] Submit
[ ] Approve
[ ] Reject
[ ] Post
[ ] Cancel draft
[ ] Cancel approved transaction
[ ] Cancel posted transaction if supported
[ ] Stock decreases correctly
[ ] Unit conversion works
[ ] Insufficient stock rejected
[ ] Negative stock prevented
[ ] Invalid warehouse rejected
[ ] Invalid location rejected
[ ] Cross-warehouse location rejected
[ ] Unauthorized warehouse rejected
[ ] Unauthorized department rejected
[ ] Unauthorized item rejected
[ ] Duplicate posting prevented
[ ] Ledger created exactly once
[ ] Inventory updated exactly once
[ ] Audit created
[ ] Concurrent Stock Out handled correctly
```

---

# 56. Definition of Done

```text
[ ] Stock Out CRUD
[ ] Draft lifecycle
[ ] Approval lifecycle
[ ] Posting lifecycle
[ ] Cancellation rule
[ ] Validation
[ ] Authorization
[ ] Warehouse scope
[ ] Department / Item scope
[ ] Location validation
[ ] Unit conversion
[ ] Available stock validation
[ ] Negative stock protection
[ ] Inventory locking
[ ] Database transaction
[ ] Duplicate posting protection
[ ] Inventory ledger
[ ] Current stock update
[ ] Audit
[ ] Index reviewed
[ ] Query optimized
[ ] Tests
[ ] Documentation
```

---

# 57. Final Stock Out Flow

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
                           ┌────────┴────────┐
                           ▼                 ▼
                      PERMISSION          POLICY
                                               │
                                     ┌─────────┴─────────┐
                                     ▼                   ▼
                                Warehouse Scope    Department/Item
                                                       Scope
                           └─────────┬─────────┘
                                     ▼
                              STOCK OUT SERVICE
                                     │
                                  POST()
                                     │
                                     ▼
                          DATABASE TRANSACTION
                                     │
                                     ▼
                             LOCK INVENTORY
                                     │
                                     ▼
                          CHECK AVAILABLE STOCK
                                     │
                          ┌──────────┴──────────┐
                          │                     │
                       INSUFFICIENT          SUFFICIENT
                          │                     │
                       ROLLBACK                ▼
                                            LEDGER OUT
                                                │
                                                ▼
                                         DECREASE BALANCE
                                                │
                                                ▼
                                           POSTED
                                                │
                                                ▼
                                              AUDIT
                                                │
                                                ▼
                                             COMMIT
```

---

# 58. Key Principle

Stock Out bukan sekadar:

```text
"Kurangi angka stock"
```

Stock Out adalah:

```text
Business Transaction
+
Authorization
+
Stock Availability
+
Concurrency Control
+
Inventory Movement
+
Auditability
```

Prinsip utama:

```text
DRAFT
→ No stock effect

PENDING_APPROVAL
→ Waiting authorization

APPROVED
→ Authorized, but stock not yet changed

POSTED
→ Stock reduced

CANCELLED
→ Historical record preserved

LEDGER
→ Records movement

INVENTORY
→ Represents current balance
```

Dan bagian paling penting:

```text
Stock Out
     ↓
Lock Inventory
     ↓
Check Latest Stock
     ↓
Create Ledger
     ↓
Update Balance
     ↓
POSTED
```

Semua operasi perubahan stock harus berada dalam **database transaction**.

Dengan pola ini, ketika nanti kamu menemukan bug seperti **"stock minus", "stock berkurang dua kali", atau "user bisa mengambil barang dari warehouse yang bukan scope-nya"**, kamu sudah tahu bagian kode mana yang harus ditelusuri tanpa harus mengandalkan vibe coding.

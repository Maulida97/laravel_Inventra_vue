# Inventra

## Data Flow

**Document:** Data Flow
**Version:** V1.0
**Status:** Draft

---

# 1. Purpose

Dokumen ini menjelaskan bagaimana data bergerak di dalam Inventra dari:

```text
User Input
   ↓
Frontend
   ↓
Request
   ↓
Validation
   ↓
Authorization
   ↓
Business Logic
   ↓
Database
   ↓
Ledger
   ↓
Stock Balance
   ↓
Audit
```

Tujuan utama adalah agar developer dapat memahami **alur data sebenarnya**, bukan hanya struktur tabel atau tampilan UI.

---

# 2. General Request Flow

Semua request web mengikuti pola:

```text
User
 ↓
Vue.js
 ↓
Inertia.js
 ↓
Laravel Route
 ↓
Middleware
 ↓
Authentication
 ↓
Authorization
 ↓
Form Request
 ↓
Controller
 ↓
Service
 ↓
Model / Database
 ↓
Response
 ↓
Inertia.js
 ↓
Vue.js
```

---

# 3. General API Flow

REST API mengikuti:

```text
External Client
 ↓
API Endpoint
 ↓
API Middleware
 ↓
Authentication
 ↓
Authorization
 ↓
Validation
 ↓
Controller
 ↓
Service
 ↓
Database
 ↓
API Resource
 ↓
JSON Response
```

Web dan API menggunakan business logic yang sama.

---

# 4. Master Data Flow

Master data digunakan sebagai referensi oleh module lain.

Contoh:

```text
Admin
 ↓
Create Category
 ↓
Validation
 ↓
Authorization
 ↓
Category Service
 ↓
categories
```

Kemudian:

```text
Category
 ↓
Item
 ↓
Inventory
```

Master data tidak boleh dibuat ulang secara manual di setiap transaction jika datanya memang merupakan reference data.

---

# 5. Item Creation Flow

Contoh pembuatan item:

```text
User
 ↓
Item Form
 ↓
Item Name
Item Code
Category
Brand
Item Type
Base Unit
Minimum Stock
 ↓
Validation
 ↓
Authorization
 ↓
Item Service
 ↓
items
```

Contoh:

```text
Item:
Hydraulic Oil

Item Code:
HYD-OIL-001

Base Unit:
LITER

Type:
Consumable
```

---

# 6. Unit & Content Per Unit Concept

Inventra **tidak menggunakan conversion table global sebagai sumber conversion otomatis**.

Karena isi package dapat berbeda berdasarkan:

- Brand.
- Supplier.
- Packaging.
- Product variant.
- Physical package.

Contoh:

```text
Paku Brand A
1 BOX = 100 PCS

Paku Brand B
1 BOX = 200 PCS
```

Karena itu informasi package dicatat secara manual pada transaksi.

---

# 7. Stock In — Basic Flow

Stock In:

```text
User
 ↓
Stock In Form
 ↓
Input Items
 ↓
Validation
 ↓
Authorization
 ↓
StockInService
 ↓
Inventory Transaction
 ↓
Inventory Ledger
 ↓
Stock Balance
 ↓
Audit Log
 ↓
Response
```

---

# 8. Stock In — Multiple Items

Satu Stock In dapat memiliki lebih dari satu item.

Contoh:

```text
STI-20260830-00001

Item 1:
Hydraulic Oil
1 Drum
200 Liter

Item 2:
Paku Brand A
6 Box
100 PCS / Box

Item 3:
Bolt
5 Box
50 PCS / Box
```

Data transaction:

```text
Stock In Header
       │
       ├── Item Detail 1
       ├── Item Detail 2
       └── Item Detail 3
```

Header menyimpan informasi umum.

Detail menyimpan item masing-masing.

---

# 9. Stock In — Quantity Flow

Untuk setiap item:

```text
Input Quantity
      +
Content Per Unit
      ↓
Equivalent Quantity
```

Contoh:

```text
6 BOX
×
100 PCS / BOX
=
600 PCS
```

Equivalent quantity digunakan untuk inventory calculation berdasarkan base unit.

---

# 10. Hydraulic Oil Example

Item:

```text
Hydraulic Oil
Base Unit: LITER
```

Input:

```text
1 DRUM
```

User memasukkan:

```text
Content Per Unit:
200 LITER
```

System menghitung:

```text
1 DRUM × 200 LITER
=
200 LITER
```

Stock balance bertambah:

```text
+200 LITER
```

---

# 11. Manual Unit Change

User dapat memilih unit physical package yang sesuai.

Contoh:

```text
DRUM
BOTTLE
BOX
PACK
PCS
```

Input:

```text
2 BOTTLE
```

Content:

```text
1 BOTTLE = 1 LITER
```

Equivalent:

```text
2 LITER
```

Jika physical packaging berubah:

```text
1 DRUM = 200 LITER
```

menjadi:

```text
1 BOTTLE = 1 LITER
```

transaction baru menggunakan informasi package yang baru.

History transaction lama tidak berubah.

---

# 12. Content Per Unit Storage

Informasi package disimpan bersama transaction detail.

Concept:

```text
Transaction Detail
├── input_quantity
├── input_unit
├── content_per_unit
├── content_unit
└── equivalent_quantity
```

Contoh:

```text
input_quantity:
6

input_unit:
BOX

content_per_unit:
100

content_unit:
PCS

equivalent_quantity:
600
```

Dengan demikian historical transaction tetap dapat dipahami meskipun informasi package berubah di kemudian hari.

---

# 13. Content Per Unit Is Not Global Conversion

Tidak dibuat aturan:

```text
BOX → PCS = 100
```

secara global.

Karena:

```text
Brand A:
1 BOX = 100 PCS

Brand B:
1 BOX = 200 PCS
```

Informasi package adalah **transaction-level information**.

---

# 14. Equivalent Quantity Validation

Sebelum inventory diproses:

```text
Input Quantity
       ↓
Check Unit
       ↓
Check Content
       ↓
Calculate Equivalent
       ↓
Validate Result
```

Contoh:

```text
6 BOX
1 BOX = 100 PCS
```

Result:

```text
600 PCS
```

Jika content per unit kosong dan diperlukan untuk menentukan base quantity:

```text
Transaction
↓
Needs clarification / validation error
```

Tidak boleh system menebak angka tanpa aturan yang ditentukan.

---

# 15. Stock In Database Flow

Conceptual flow:

```text
stock_in
    ↓
stock_in_items
    ↓
inventory_transactions
    ↓
inventory_ledger
    ↓
stock_balances
```

Audit:

```text
stock operation
      ↓
audit_logs
```

Actual table design mengikuti `05_DATABASE.md`.

---

# 16. Stock Balance Calculation

Stock balance merupakan current state.

Concept:

```text
Previous Balance
       +
Ledger Movement
       ↓
New Balance
```

Contoh:

```text
Opening:
500 L

Stock In:
+200 L

Balance:
700 L
```

Kemudian:

```text
Stock Out:
-150 L

Balance:
550 L
```

---

# 17. Inventory Ledger Flow

Setiap stock movement menghasilkan ledger entry.

```text
Stock In
 ↓
Ledger:
+200 L
```

Stock Out:

```text
Stock Out
 ↓
Ledger:
-150 L
```

Transfer:

```text
Source Ledger:
-100 L

Destination Ledger:
+100 L
```

---

# 18. Ledger Immutability

Ledger tidak digunakan untuk mengedit history lama.

Jika terjadi kesalahan:

```text
Wrong Transaction
       ↓
Correction Transaction
```

Bukan:

```text
Wrong Transaction
       ↓
UPDATE old ledger
```

Prinsip:

```text
History remains traceable.
```

---

# 19. Stock Out Flow

```text
User
 ↓
Stock Out Form
 ↓
Select Department
 ↓
Select Item
 ↓
Input Quantity
 ↓
Check Stock
 ↓
Submit
 ↓
Approval
 ↓
Approved
 ↓
Inventory Service
 ↓
Ledger
 ↓
Stock Balance
 ↓
Audit
```

---

# 20. Stock Availability Flow

Sebelum Stock Out:

```text
Requested Quantity
        ↓
Current Stock
        ↓
Compare
```

Contoh:

```text
Available:
500 PCS

Request:
200 PCS

Result:
ALLOWED
```

Jika:

```text
Available:
100 PCS

Request:
200 PCS

Result:
REJECTED
```

Tidak boleh membuat stock menjadi negatif kecuali business rule secara eksplisit mengizinkannya.

---

# 21. Stock Out Multiple Items

Satu transaction dapat berisi beberapa item:

```text
STO-20260830-00001

├── Paku
│   200 PCS
│
├── Bolt
│   50 PCS
│
└── Hydraulic Oil
    20 LITER
```

Stock availability harus divalidasi untuk seluruh item sebelum transaction final dijalankan.

---

# 22. Transaction Atomicity

Multi-item transaction harus atomic.

```text
BEGIN
   ↓
Validate Item 1
   ↓
Validate Item 2
   ↓
Validate Item 3
   ↓
Create Transaction
   ↓
Create Ledger
   ↓
Update Balance
   ↓
Audit
   ↓
COMMIT
```

Jika salah satu item gagal:

```text
ROLLBACK
```

Tidak boleh:

```text
Item 1 berhasil
Item 2 berhasil
Item 3 gagal
```

sementara transaction dianggap berhasil seluruhnya.

---

# 23. Stock Transfer Flow

```text
User
 ↓
Transfer Form
 ↓
Source Warehouse
 ↓
Source Location
 ↓
Destination Warehouse
 ↓
Destination Location
 ↓
Quantity
 ↓
Validation
 ↓
Approval if required
 ↓
Inventory Service
 ↓
Source Ledger -
 ↓
Destination Ledger +
 ↓
Stock Balance Update
 ↓
Audit
```

Total quantity inventory tidak berubah.

---

# 24. Stock Return Flow

```text
Original Stock Out
       ↓
Return Request
       ↓
Reference Original Transaction
       ↓
Validate Return Quantity
       ↓
Approval if required
       ↓
Stock In
       ↓
Ledger +
       ↓
Stock Balance
       ↓
Audit
```

Return quantity tidak boleh melebihi quantity yang masih dapat dikembalikan berdasarkan business rule.

---

# 25. Stock Opname Flow

```text
Create Opname
      ↓
Select Warehouse
      ↓
Select Location
      ↓
System Quantity
      ↓
Physical Count
      ↓
Calculate Difference
```

Contoh:

```text
System:
500 PCS

Physical:
480 PCS

Difference:
-20 PCS
```

Kemudian:

```text
Difference
 ↓
Approval
 ↓
Adjustment Transaction
 ↓
Ledger
 ↓
Stock Balance
 ↓
Audit
```

---

# 26. Purchase Request Flow

```text
Department Staff
       ↓
Create PR
       ↓
Select Allowed Item
       ↓
Input Quantity
       ↓
Submit
       ↓
Approval
       ↓
Approved / Rejected
```

Item selection harus memperhatikan department scope.

Contoh:

```text
IT
 ↓
Allowed:
Laptop
Keyboard
Mouse

QC
 ↓
Allowed:
Testing Equipment
QC Consumables
```

---

# 27. Purchase Order Flow

```text
Approved PR
     ↓
Create PO
     ↓
Select Supplier
     ↓
PO
     ↓
Supplier
```

PO tidak langsung menambah stock.

---

# 28. Receiving Flow

```text
PO
 ↓
Receiving
 ↓
Physical Verification
 ↓
Received Quantity
 ↓
Receiving Confirmation
 ↓
Stock In
 ↓
Inventory Ledger
 ↓
Stock Balance
```

Jika quantity actual berbeda dengan PO:

```text
PO Quantity
≠
Received Quantity
```

perbedaan harus dicatat dan mengikuti business rule receiving.

---

# 29. Approval Data Flow

Approval merupakan flow terpisah dari execution.

```text
Transaction
     ↓
Approval Request
     ↓
Workflow
     ↓
Approver
     ↓
Approve / Reject
```

Jika approved:

```text
Approval
 ↓
Execute Transaction
```

Jika rejected:

```text
Approval
 ↓
Rejected
 ↓
No Inventory Movement
```

---

# 30. Asset Data Flow

Asset dapat berasal dari inventory.

```text
Stock In
 ↓
Asset Registration
 ↓
Asset Record
 ↓
Asset Tag
 ↓
Assignment
 ↓
Custodian
 ↓
Location
```

Kemudian:

```text
Return
Maintenance
Disposal
```

dicatat sebagai lifecycle event.

---

# 31. Transaction Reference Flow

Setiap transaction memiliki unique reference number.

Contoh:

```text
STI-20260830-00001
```

Reference digunakan untuk:

```text
Stock In
Stock Out
Transfer
Return
Stock Opname
Purchase Request
Purchase Order
Asset Transaction
```

Reference menjadi salah satu entry point utama untuk tracing.

---

# 32. Location Flow

Physical location:

```text
Warehouse
 ↓
Rack
 ↓
Shelf / Location
```

Contoh:

```text
WH-001
 ↓
RACK-A
 ↓
A-01
```

Transaction menyimpan location terkait sehingga physical stock dapat ditelusuri.

---

# 33. Numbering Flow

Identifier memiliki fungsi berbeda.

```text
Item Code
→ Identitas barang

Location Code
→ Identitas lokasi

Transaction Reference
→ Identitas transaksi

Asset Tag
→ Identitas asset individual
```

Contoh:

```text
ITM-000123
WH-001 / RACK-A / A-01
STI-20260830-00001
AST-000045
```

---

# 34. Audit Data Flow

Setelah business operation berhasil:

```text
Business Action
       ↓
Audit Service
       ↓
Audit Log
```

Contoh:

```text
User:
Budi

Action:
STOCK_OUT_CREATED

Reference:
STO-20260830-00001

Timestamp:
2026-08-30 14:30
```

Untuk perubahan data penting:

```text
Before
+
After
```

dapat disimpan jika diperlukan.

---

# 35. Reporting Data Flow

Reporting bersifat read-oriented.

```text
Operational Data
      ↓
Query
      ↓
Filter
      ↓
Scope
      ↓
Report
```

Contoh:

```text
Inventory Ledger
      ↓
Stock Movement Report
```

Report tidak mengubah inventory.

---

# 36. Dashboard Data Flow

Dashboard:

```text
User
 ↓
Dashboard Request
 ↓
Authorization
 ↓
Scope
 ↓
Aggregate Queries
 ↓
Dashboard Data
 ↓
Vue
```

Contoh:

```text
Stock Summary
Low Stock
Pending Approval
Recent Transaction
```

Data dashboard harus mengikuti scope user.

---

# 37. API Data Flow

Contoh:

```text
GET /api/v1/items
        ↓
Authentication
        ↓
Authorization
        ↓
Scope
        ↓
Item Query
        ↓
API Resource
        ↓
JSON
```

API tidak boleh melewati authorization hanya karena request berasal dari API.

---

# 38. Error Data Flow

Validation error:

```text
Request
 ↓
Validation
 ↓
Error
 ↓
HTTP Response
 ↓
Vue
 ↓
Display Error
```

Business error:

```text
Service
 ↓
Business Exception
 ↓
Exception Handler
 ↓
Response
```

Contoh:

```text
Insufficient Stock
```

tidak dianggap sebagai system crash.

---

# 39. Database Transaction Boundary

Operasi yang mengubah inventory menggunakan database transaction.

Contoh:

```text
Stock Out
│
├── Create Transaction
├── Create Transaction Detail
├── Create Ledger
├── Update Balance
└── Create Audit
```

Semua bagian yang wajib konsisten berada dalam transaction boundary yang sama.

---

# 40. Complete Stock In Data Flow

```text
                    USER
                      │
                      ▼
              Stock In Form
                      │
                      ▼
                 Vue.js
                      │
                      ▼
                 Inertia.js
                      │
                      ▼
                Laravel Route
                      │
                      ▼
                 Middleware
                      │
                      ▼
              Authentication
                      │
                      ▼
              Authorization
                      │
                      ▼
            StoreStockInRequest
                      │
                      ▼
             StockInController
                      │
                      ▼
              StockInService
                      │
                      ▼
        Calculate Equivalent Quantity
                      │
                      ▼
              Database Transaction
                      │
          ┌───────────┼───────────┐
          ▼           ▼           ▼
     Transaction    Ledger     Stock Balance
          │           │           │
          └───────────┼───────────┘
                      ▼
                  Audit Log
                      │
                      ▼
                   COMMIT
                      │
                      ▼
                 Inertia Response
                      │
                      ▼
                  Vue.js UI
```

---

# 41. Complete Stock Out Data Flow

```text
USER
 │
 ▼
Stock Out Form
 │
 ▼
Vue + Inertia
 │
 ▼
Laravel
 │
 ├── Authentication
 │
 ├── Authorization
 │
 └── Validation
        │
        ▼
   StockOutService
        │
        ▼
   Check Stock
        │
        ▼
     Approval
        │
        ▼
      Approved
        │
        ▼
 Database Transaction
        │
   ┌────┼───────────┐
   ▼    ▼           ▼
Transaction Ledger Balance
   │    │           │
   └────┼───────────┘
        ▼
     Audit Log
        │
        ▼
      COMMIT
```

---

# 42. Data Traceability

Setiap perubahan inventory harus dapat ditelusuri:

```text
Transaction Reference
        ↓
Transaction
        ↓
Transaction Detail
        ↓
Item
        ↓
Warehouse
        ↓
Location
        ↓
Ledger
        ↓
Stock Balance
        ↓
User
        ↓
Audit Log
```

Contoh pertanyaan:

> "Kenapa stock Hydraulic Oil sekarang 550 Liter?"

Trace:

```text
550 L
 ↓
Stock Balance
 ↓
Ledger
 ↓
STI-20260830-00001  +200 L
STO-20260830-00002  -150 L
 ↓
Transaction Details
 ↓
User + Location
```

---

# 43. Data Ownership

| Data          | Owner            |
| ------------- | ---------------- |
| User          | Authentication   |
| Role          | RBAC             |
| Permission    | RBAC             |
| Department    | Master Data      |
| Item          | Item Management  |
| Unit          | Master Data      |
| Warehouse     | Warehouse        |
| Location      | Warehouse        |
| Transaction   | Inventory        |
| Ledger        | Inventory        |
| Stock Balance | Inventory        |
| Approval      | Approval         |
| PR/PO         | Procurement      |
| Asset         | Asset Management |
| Audit Log     | Audit            |
| Reports       | Reporting        |

Module lain menggunakan data melalui relationship/service yang sesuai.

---

# 44. Data Consistency Rules

Inventra mengikuti aturan:

### Rule 1

Stock hanya berubah melalui inventory transaction.

### Rule 2

Setiap inventory movement menghasilkan ledger entry.

### Rule 3

Transaction penting memiliki reference number.

### Rule 4

Stock operation menggunakan database transaction.

### Rule 5

History tidak diubah untuk memperbaiki kesalahan.

### Rule 6

Correction dilakukan menggunakan transaction baru.

### Rule 7

Authorization diperiksa sebelum business operation.

### Rule 8

Scope diperiksa pada data yang diakses.

### Rule 9

Historical `content_per_unit` tetap mengikuti transaction saat dibuat.

---

# 45. Data Flow & Maintenance

Ketika developer ingin mengubah suatu behavior, gunakan flow untuk menemukan lokasi kode.

### Mengubah tampilan

```text
Vue Page
```

### Mengubah validasi input

```text
Form Request
```

### Mengubah permission

```text
Policy / RBAC
```

### Mengubah business rule

```text
Service
```

### Mengubah struktur data

```text
Migration / Model
```

### Mengubah stock calculation

```text
Inventory Service
```

### Mengubah history stock

```text
Inventory Ledger
```

### Mengubah laporan

```text
Reporting Query / Service
```

---

# 46. Data Flow Principle

Prinsip utama data flow Inventra:

```text
Input
 ↓
Validate
 ↓
Authorize
 ↓
Process
 ↓
Persist
 ↓
Ledger
 ↓
Balance
 ↓
Audit
 ↓
Response
```

Untuk inventory:

> **Ledger menjelaskan apa yang terjadi, Stock Balance menjelaskan kondisi sekarang, dan Transaction menjelaskan konteks business operation.**

Dengan pemisahan tersebut, data Inventra dapat ditrace dari UI sampai database dan kembali lagi ke UI.

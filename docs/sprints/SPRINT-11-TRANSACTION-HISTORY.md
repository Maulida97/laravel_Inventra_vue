# Inventra

## Sprint 11 — Transaction History

**Sprint:** SPRINT-11
**Name:** Transaction History
**Project:** Inventra
**Status:** Planned
**Branch:** `feature/transaction-history`

---

# 1. Sprint Overview

Transaction History menyediakan histori aktivitas bisnis yang berhubungan dengan inventory dan asset.

Contoh:

```text
Stock In
Stock Out
Stock Opname
Asset Assignment
Asset Transfer
Asset Return
Asset Disposal
```

Tujuan utamanya adalah membuat setiap perubahan bisnis dapat ditelusuri.

---

# 2. Objective

Sistem harus dapat menjawab:

```text
Apa yang terjadi?
Kapan terjadi?
Pada item/asset apa?
Berapa quantity?
Dari mana?
Ke mana?
Siapa yang melakukan?
Apa referensinya?
```

---

# 3. Transaction History vs Audit Log

Ini harus dipahami dengan jelas.

### Transaction History

Berfokus pada:

```text
Business Event
```

Contoh:

```text
Laptop
Stock Out
Quantity = 2
Warehouse A → Department IT
```

### Audit Log

Berfokus pada:

```text
System Activity
```

Contoh:

```text
Budi
POST /stock-outs/15
2026-08-30 10:20
```

Hubungannya:

```text
Business Transaction
        │
        ▼
Transaction History
        │
        ▼
Audit Log
```

Keduanya tidak boleh digabung menjadi satu tabel.

---

# 4. Scope

### Included

```text
Transaction History
Transaction Type
Reference
Quantity
Item
Warehouse
User
Transaction Date
Transaction Detail
Filtering
Search
Pagination
```

### Not Included

```text
Accounting Journal
Financial Ledger
General Ledger
Advanced BI
Data Warehouse
```

---

# 5. Transaction Types

Minimal:

```text
STOCK_IN
STOCK_OUT
STOCK_ADJUSTMENT
ASSET_ASSIGNMENT
ASSET_RETURN
ASSET_TRANSFER
ASSET_DISPOSAL
```

Future:

```text
STOCK_TRANSFER
PURCHASE_RECEIPT
RETURN_TO_VENDOR
```

---

# 6. Transaction Structure

Concept:

```text
Transaction
├── ID
├── Type
├── Reference
├── Date
├── User
├── Warehouse
├── Item
├── Quantity
└── Metadata
```

Transaction history harus menjadi **read-oriented record** untuk pelacakan.

---

# 7. Transaction Header

Concept:

```text
inventory_transactions
├── id
├── transaction_number
├── transaction_type
├── reference_type
├── reference_id
├── warehouse_id
├── performed_by
├── transaction_date
├── notes
├── created_at
└── updated_at
```

---

# 8. Transaction Number

Setiap transaksi memiliki identifier.

Contoh:

```text
TRX-20260830-000001
TRX-20260830-000002
TRX-20260830-000003
```

Constraint:

```text
transaction_number
→ UNIQUE
```

Transaction number digunakan untuk:

```text
Search
Reference
Report
Audit
Support
```

---

# 9. Transaction Detail

Untuk transaksi yang melibatkan item:

```text
inventory_transaction_items
├── id
├── transaction_id
├── item_id
├── quantity
├── unit
├── warehouse_id
└── notes
```

Contoh:

```text
TRX-00001

Laptop × 5
Mouse × 10
Keyboard × 5
```

Satu transaction dapat memiliki banyak item.

---

# 10. Header vs Detail

Gunakan:

```text
Transaction Header
        │
        ├── Item A
        ├── Item B
        └── Item C
```

Bukan:

```text
Transaction A
Transaction B
Transaction C
```

untuk satu aktivitas bisnis yang sama.

Ini membuat grouping transaksi lebih jelas.

---

# 11. Reference

Transaction harus dapat menunjuk ke sumber transaksi.

Contoh:

```text
STOCK_IN
→ StockIn #15
```

```text
STOCK_OUT
→ StockOut #27
```

```text
ASSET_DISPOSAL
→ AssetDisposal #8
```

Concept:

```text
reference_type
reference_id
```

---

# 12. Transaction Flow

Contoh Stock In:

```text
Stock In
   ↓
Validation
   ↓
Database Transaction
   ├── Update Stock
   ├── Create Ledger
   ├── Create Transaction History
   └── Audit
   ↓
COMMIT
```

Semua harus berhasil bersama.

---

# 13. Stock In History

Contoh:

```text
TRX-20260830-00001
Type: STOCK_IN

Warehouse:
WH-JKT

Items:
Laptop     +10
Monitor    +5

Performed By:
Budi

Date:
2026-08-30 09:30
```

---

# 14. Stock Out History

Contoh:

```text
TRX-20260830-00002
Type: STOCK_OUT

Warehouse:
WH-JKT

Items:
Laptop     -2
Monitor    -1

Destination:
IT Department

Performed By:
Andi
```

---

# 15. Stock Adjustment History

Stock Opname dapat menghasilkan:

```text
STOCK_ADJUSTMENT
```

Contoh:

```text
System Stock:
100

Physical:
97

Adjustment:
-3
```

History:

```text
TRX-00003
STOCK_ADJUSTMENT
Mouse
-3
```

---

# 16. Asset Transaction History

Asset juga menghasilkan transaction history.

Contoh:

```text
ASSET_ASSIGNMENT
```

```text
AST-00001
Budi
```

atau:

```text
ASSET_TRANSFER
```

```text
Warehouse A
      ↓
Warehouse B
```

---

# 17. Asset History vs Transaction History

Asset History:

```text
AST-00001
ASSIGNED
RETURNED
TRANSFERRED
```

Transaction History:

```text
TRX-001
ASSET_ASSIGNMENT

TRX-002
ASSET_RETURN

TRX-003
ASSET_TRANSFER
```

Asset History lebih spesifik terhadap lifecycle asset.

Transaction History memberikan perspektif transaksi bisnis secara keseluruhan.

---

# 18. Immutable Principle

Transaction history sebaiknya:

```text
CREATE
ONLY
```

Setelah transaksi berhasil:

```text
UPDATE
DELETE
```

tidak boleh dilakukan secara normal.

Jika ada kesalahan:

```text
Correction Transaction
```

bukan mengedit history lama.

---

# 19. Correction Principle

Contoh salah input:

```text
Stock In
Laptop +100
```

seharusnya:

```text
+10
```

Jangan:

```text
UPDATE transaction
quantity = 10
```

Gunakan correction:

```text
Original
+100

Correction
-90
```

Hasil:

```text
+10
```

Dengan demikian history tetap dapat diaudit.

---

# 20. Transaction Timeline

User dapat melihat:

```text
Inventory
   ↓
Transaction History
```

Contoh:

```text
2026-08-25
STOCK_IN
+100

2026-08-27
STOCK_OUT
-20

2026-08-29
STOCK_ADJUSTMENT
-3

Current Balance
77
```

---

# 21. Item Timeline

Untuk satu item:

```text
Laptop
```

timeline:

```text
Stock In
+10

Stock Out
-2

Stock Out
-1

Adjustment
-1
```

Current:

```text
6
```

---

# 22. Warehouse Timeline

Untuk warehouse:

```text
WH-JKT
```

dapat melihat:

```text
Stock In
Stock Out
Adjustment
Transfer
```

dengan filtering berdasarkan:

```text
Date
Type
Item
User
Reference
```

---

# 23. Search

Transaction History harus dapat dicari berdasarkan:

```text
Transaction Number
Reference
Item
Asset
User
```

Contoh:

```text
Search:
TRX-20260830
```

atau:

```text
Search:
Laptop
```

---

# 24. Filter

Minimal filter:

```text
Transaction Type
Warehouse
Item
User
Date Range
```

Contoh:

```text
Type:
STOCK_OUT

Warehouse:
WH-JKT

Date:
2026-08-01 → 2026-08-30
```

---

# 25. Pagination

Jangan mengambil seluruh transaction history sekaligus.

Gunakan:

```text
Pagination
```

Contoh:

```text
20 records / page
```

Query harus memiliki:

```text
ORDER BY transaction_date DESC
```

atau cursor pagination jika volume sudah besar.

---

# 26. Sorting

Default:

```text
Newest → Oldest
```

Contoh:

```text
2026-08-30
2026-08-29
2026-08-28
```

Sorting tambahan dapat diberikan kemudian.

---

# 27. Transaction Detail Page

Menampilkan:

```text
Transaction Number
Transaction Type
Date
Reference
Warehouse
Performed By
Items
Quantity
Notes
```

Contoh:

```text
TRX-00001

STOCK IN
Warehouse: WH-JKT
Date: 30 Aug 2026
User: Budi

Items
------------------
Laptop       10
Monitor       5
Keyboard      5
```

---

# 28. Frontend Structure

```text
resources/js/
├── Pages/
│   └── Transactions/
│       ├── Index.vue
│       └── Show.vue
│
└── Components/
    └── Transactions/
        ├── TransactionFilters.vue
        ├── TransactionTable.vue
        ├── TransactionStatus.vue
        └── TransactionTimeline.vue
```

---

# 29. Backend Structure

```text
app/
├── Models/
│   ├── InventoryTransaction.php
│   └── InventoryTransactionItem.php
│
├── Http/
│   └── Controllers/
│       └── TransactionController.php
│
├── Policies/
│   └── TransactionPolicy.php
│
└── Services/
    └── Transaction/
        └── TransactionService.php
```

---

# 30. Transaction Service

Responsibilities:

```text
Create Transaction
Generate Number
Create Details
Retrieve History
Validate Reference
```

Tidak bertanggung jawab untuk:

```text
Stock Calculation
Asset Assignment
Approval
```

Business module masing-masing tetap bertanggung jawab terhadap proses bisnisnya.

---

# 31. Example Architecture

Stock Out:

```text
StockOutService
      ↓
Update Stock
      ↓
Create Ledger
      ↓
TransactionService
      ↓
Create Transaction History
      ↓
AuditService
```

Asset Transfer:

```text
AssetService
      ↓
Update Asset
      ↓
Create Asset History
      ↓
TransactionService
      ↓
Transaction History
      ↓
AuditService
```

---

# 32. Transaction and Ledger

Transaction History:

```text
"What happened?"
```

Inventory Ledger:

```text
"How did stock balance change?"
```

Contoh:

```text
Transaction:
STOCK_OUT
Laptop -2
```

Ledger:

```text
Previous Balance: 10
Movement: -2
New Balance: 8
```

Keduanya memiliki tujuan berbeda.

---

# 33. Source of Truth

Untuk stock:

```text
Inventory Ledger
```

merupakan sumber perhitungan movement/balance.

Transaction History:

```text
Business History
```

digunakan untuk navigasi dan traceability.

Jangan menghitung current stock hanya dari UI transaction history tanpa mempertimbangkan ledger/business rules.

---

# 34. Transaction Security

Lindungi dari:

```text
Unauthorized Access
IDOR
Warehouse Scope Bypass
History Modification
History Deletion
Reference Manipulation
```

---

# 35. Read Authorization

User hanya boleh melihat transaction yang berada dalam scope-nya.

Flow:

```text
Authentication
 ↓
Permission
 ↓
Warehouse Scope
 ↓
Transaction Policy
 ↓
View
```

---

# 36. IDOR Protection

Request:

```text
GET /transactions/123
```

tidak berarti user otomatis boleh melihatnya.

Backend harus memeriksa:

```text
Transaction exists?
 ↓
User allowed?
 ↓
Warehouse accessible?
 ↓
View
```

---

# 37. Immutability Protection

Tidak menyediakan endpoint normal:

```text
PUT /transactions/{id}
DELETE /transactions/{id}
```

Jika correction diperlukan:

```text
Create Correction Transaction
```

---

# 38. Database Index

Potential:

```text
inventory_transactions.transaction_number
inventory_transactions.transaction_type
inventory_transactions.reference_type
inventory_transactions.reference_id
inventory_transactions.warehouse_id
inventory_transactions.performed_by
inventory_transactions.transaction_date

inventory_transaction_items.transaction_id
inventory_transaction_items.item_id
```

Query utama:

```text
WHERE warehouse_id = ?
ORDER BY transaction_date DESC
```

harus dievaluasi menggunakan:

```text
EXPLAIN
```

Composite index dapat dipertimbangkan berdasarkan query nyata.

---

# 39. Database Constraints

Minimal:

```text
transaction_number
→ UNIQUE

inventory_transaction_items.transaction_id
→ FOREIGN KEY

inventory_transaction_items.item_id
→ FOREIGN KEY

inventory_transactions.warehouse_id
→ FOREIGN KEY

inventory_transactions.performed_by
→ FOREIGN KEY
```

---

# 40. Transaction Atomicity

Contoh:

```text
Stock Out
```

harus:

```text
BEGIN
 ↓
Validate Stock
 ↓
Lock Stock
 ↓
Decrease Stock
 ↓
Create Ledger
 ↓
Create Transaction
 ↓
Create Transaction Items
 ↓
Audit
 ↓
COMMIT
```

Jika transaction history gagal:

```text
ROLLBACK
```

Jangan sampai:

```text
Stock berkurang
+
Transaction History tidak ada
```

---

# 41. Concurrency

Kasus:

```text
Stock = 10

User A → Stock Out 7
User B → Stock Out 5
```

Tidak boleh menghasilkan:

```text
Stock = -2
```

Transaction harus menggunakan mekanisme concurrency yang sesuai.

Contoh:

```text
Transaction
+
Row Lock
+
Stock Validation
```

---

# 42. Frontend Responsibility

Frontend hanya:

```text
Display
Search
Filter
Pagination
Navigation
```

Frontend tidak menghitung:

```text
Final Stock
```

dan tidak menentukan:

```text
Transaction Validity
```

---

# 43. Maintenance Guide

### "Saya ingin mengubah tampilan Transaction History."

Cari:

```text
resources/js/Pages/Transactions/
```

---

### "Saya ingin mengubah filter."

Cari:

```text
TransactionFilters.vue
```

dan:

```text
TransactionController
```

---

### "Saya ingin mengubah query history."

Cari:

```text
TransactionService
```

atau query layer yang digunakan oleh module tersebut.

---

### "Transaction history tidak muncul."

Trace:

```text
Vue
 ↓
Inertia
 ↓
TransactionController
 ↓
TransactionService
 ↓
InventoryTransaction
 ↓
Database
```

---

### "Stock berubah tetapi history tidak ada."

Trace:

```text
Stock Service
 ↓
Database Transaction
 ├── Stock
 ├── Ledger
 └── Transaction History
```

Kemungkinan masalah:

```text
Transaction boundary
atau
TransactionService
```

---

# 44. Code Understanding Map

```text
Vue
 ↓
Inertia
 ↓
Route
 ↓
TransactionController
 ↓
TransactionPolicy
 ↓
TransactionService
 ↓
InventoryTransaction
 ↓
InventoryTransactionItem
 ↓
Database
```

Untuk transaksi write:

```text
Business Service
 ↓
Database Transaction
 ├── Inventory
 ├── Ledger
 ├── Transaction History
 └── Audit Log
```

---

# 45. Testing

### Transaction Creation

```text
[ ] Transaction number generated
[ ] Transaction type stored
[ ] Reference stored
[ ] Warehouse stored
[ ] User stored
[ ] Details stored
```

### History

```text
[ ] History can be viewed
[ ] Search works
[ ] Filter works
[ ] Date range works
[ ] Pagination works
[ ] Detail page works
```

### Security

```text
[ ] Unauthorized transaction cannot be viewed
[ ] IDOR blocked
[ ] Warehouse scope enforced
```

### Immutability

```text
[ ] Transaction cannot be edited normally
[ ] Transaction cannot be deleted normally
[ ] Correction uses new transaction
```

### Atomicity

```text
[ ] Stock + Ledger + History succeed together
[ ] Failure causes rollback
```

### Concurrency

```text
[ ] Concurrent stock movements handled
[ ] Negative stock prevented according to business rule
```

---

# 46. Acceptance Criteria

Sprint selesai apabila:

```text
1. Transaction history tersedia.

2. Transaction number unik.

3. Transaction type tersedia.

4. Reference transaction tersedia.

5. User tercatat.

6. Warehouse tercatat.

7. Transaction detail tersedia.

8. Item dan quantity dapat ditampilkan.

9. Search tersedia.

10. Filter tersedia.

11. Date range tersedia.

12. Pagination tersedia.

13. Transaction detail page tersedia.

14. Transaction history tidak dapat diedit secara normal.

15. Transaction history tidak dapat dihapus secara normal.

16. Correction menggunakan transaction baru.

17. Warehouse scope diterapkan.

18. IDOR protection tersedia.

19. Transaction history terhubung dengan business transaction.

20. Inventory ledger tetap menjadi sumber perhitungan stock movement.

21. Transaction menggunakan database transaction jika diperlukan.

22. Concurrency ditangani.

23. Database constraints tersedia.

24. Index relevan tersedia.

25. Automated tests berhasil.

26. Code documentation mengikuti standard Inventra.

27. Developer dapat tracing Transaction dari Vue → Laravel → Database.

28. Developer memahami perbedaan Transaction History, Inventory Ledger, dan Audit Log.
```

---

# 47. Expected Files

```text
app/
├── Models/
│   ├── InventoryTransaction.php
│   └── InventoryTransactionItem.php
│
├── Http/
│   └── Controllers/
│       └── TransactionController.php
│
├── Policies/
│   └── TransactionPolicy.php
│
└── Services/
    └── Transaction/
        └── TransactionService.php

database/
└── migrations/
    ├── xxxx_create_inventory_transactions_table.php
    └── xxxx_create_inventory_transaction_items_table.php

resources/js/
├── Pages/
│   └── Transactions/
│
└── Components/
    └── Transactions/

tests/
└── Feature/
    └── Transaction/
```

---

# 48. Code Documentation

Setiap file mengikuti:

```text
docs/code-guide/00_CODE_DOCUMENTATION_STANDARD.md
```

Contoh:

```php
/**
 * Transaction Service
 *
 * Purpose:
 * Handle inventory business transaction history.
 *
 * Main Flow:
 * Business Service
 * → Database Transaction
 * → TransactionService
 * → Transaction History
 *
 * Important:
 * Transaction history is immutable.
 *
 * Transaction History is not the source
 * of truth for current inventory balance.
 *
 * Related:
 * - InventoryTransaction
 * - InventoryTransactionItem
 * - InventoryLedger
 * - AuditLog
 */
```

---

# 49. Git Branch

```text
feature/transaction-history
```

Dependency:

```text
feature/stock-in
        ↓
feature/stock-out
        ↓
feature/stock-opname
        ↓
feature/asset-management
        ↓
feature/transaction-history
```

---

# 50. Suggested Commits

```text
feat(transaction): add transaction models and migrations
feat(transaction): add transaction number generation
feat(transaction): add transaction creation service
feat(transaction): add transaction details
feat(transaction): add transaction history page
feat(transaction): add transaction search
feat(transaction): add transaction filters
feat(transaction): add transaction pagination
feat(transaction): add transaction detail page
feat(transaction): add warehouse scope
feat(transaction): add transaction authorization
feat(transaction): enforce transaction immutability
feat(transaction): integrate stock in history
feat(transaction): integrate stock out history
feat(transaction): integrate stock adjustment history
feat(transaction): integrate asset transactions
test(transaction): add transaction history tests
test(transaction): add transaction authorization tests
test(transaction): add transaction integrity tests
docs(transaction): document transaction code flow
```

---

# 51. Definition of Done

```text
Code
    ✓ Transaction Model
    ✓ Transaction Item
    ✓ Transaction Service
    ✓ Transaction Controller
    ✓ Transaction Policy

History
    ✓ Search
    ✓ Filter
    ✓ Date Range
    ✓ Pagination
    ✓ Detail

Integration
    ✓ Stock In
    ✓ Stock Out
    ✓ Stock Adjustment
    ✓ Asset Transactions

Security
    ✓ Authorization
    ✓ Warehouse Scope
    ✓ IDOR Protection
    ✓ Immutability

Data Integrity
    ✓ Transaction Number
    ✓ Foreign Keys
    ✓ Atomicity
    ✓ Concurrency

Testing
    ✓ History Tests
    ✓ Security Tests
    ✓ Integrity Tests

Documentation
    ✓ Code Comments
    ✓ Maintenance Guide
    ✓ Flow Documentation

Git
    ✓ feature/transaction-history
```

---

# 52. Final Architecture

```text
                 BUSINESS ACTION
                       │
        ┌──────────────┼──────────────┐
        ▼              ▼              ▼
     Stock In      Stock Out      Asset Action
        │              │              │
        └──────────────┼──────────────┘
                       ▼
              DATABASE TRANSACTION
                       │
          ┌────────────┼────────────┐
          ▼            ▼            ▼
       Inventory     Ledger      Transaction
                       │           History
                       │              │
                       └──────┬───────┘
                              ▼
                          Audit Log
```

---

# 53. Key Principle

Ingat tiga hal ini:

```text
Inventory Ledger
→ Bagaimana stock berubah?

Transaction History
→ Transaksi bisnis apa yang terjadi?

Audit Log
→ Siapa melakukan aksi apa di sistem?
```

Contoh:

```text
Budi melakukan Stock Out
Laptop -2
```

Maka:

```text
Ledger
→ Stock -2

Transaction History
→ STOCK_OUT TRX-001

Audit Log
→ Budi executed Stock Out
```

Ketiganya **saling berhubungan tetapi tidak boleh dicampur**.

Jika nanti kamu melakukan maintenance dan melihat:

```text
StockOutService
```

kamu sudah tahu kemungkinan alurnya:

```text
StockOutService
 ↓
Inventory update
 ↓
Ledger
 ↓
Transaction History
 ↓
Audit Log
```

Jadi kamu tidak perlu mengandalkan vibe coding untuk menebak-nebak bagian mana yang harus diubah.

# Inventra

## Sprint 04 — Item Management

**Sprint:** SPRINT-04
**Name:** Item Management
**Project:** Inventra
**Status:** Planned
**Branch:** `feature/item-management`

---

# 1. Sprint Overview

Item Management mengelola seluruh barang yang dapat disimpan dan diproses oleh Inventra.

Concept:

```text
Item
 ├── Category
 ├── Unit
 ├── Supplier (optional)
 └── Inventory
```

Item menjadi referensi utama untuk:

```text
Stock In
Stock Out
Stock Opname
Inventory Ledger
Reporting
Dashboard
```

---

# 2. Objective

Membangun Item Management yang:

- Memiliki identitas item yang unik.
- Terhubung dengan Category.
- Terhubung dengan Unit.
- Dapat memiliki Supplier default.
- Mendukung status aktif/nonaktif.
- Mendukung search dan filter.
- Memiliki validasi.
- Memiliki authorization.
- Memiliki audit trail.
- Siap digunakan oleh modul transaksi inventory.

---

# 3. Scope

### Included

```text
Item CRUD
SKU / Item Code
Category
Unit
Supplier
Status
Search
Filter
Pagination
Item Detail
Validation
Authorization
Audit Log
Database Constraints
```

### Not Included

```text
Stock In
Stock Out
Stock Opname
Inventory Calculation
Asset Management
```

Fitur tersebut memiliki sprint masing-masing.

---

# 4. Item Concept

Contoh:

```text
SKU
ITM-0001

Name
Laptop Lenovo ThinkPad

Category
Elektronik

Unit
PCS

Supplier
PT ABC

Status
ACTIVE
```

---

# 5. Item Data Structure

Minimal:

```text
items
├── id
├── sku
├── name
├── category_id
├── unit_id
├── supplier_id
├── description
├── minimum_stock
├── status
├── created_at
└── updated_at
```

`minimum_stock` digunakan sebagai dasar low-stock detection.

---

# 6. SKU / Item Code

SKU harus unik.

Contoh:

```text
ITM-000001
ITM-000002
ITM-000003
```

SKU digunakan sebagai identifier bisnis.

Jangan menjadikan nama item sebagai identifier.

Contoh buruk:

```text
Laptop Lenovo
```

karena nama dapat berubah.

Lebih baik:

```text
SKU: ITM-000001
```

---

# 7. SKU Principle

SKU:

```text
UNIQUE
STABLE
SEARCHABLE
```

Jika nama berubah:

```text
Laptop Lenovo ThinkPad
        ↓
Laptop Lenovo ThinkPad E14
```

SKU tetap:

```text
ITM-000001
```

---

# 8. Category Relationship

```text
Category
   │
   │ 1:N
   ▼
 Item
```

Satu Category dapat memiliki banyak Item.

Contoh:

```text
Elektronik
 ├── Laptop
 ├── Monitor
 └── Keyboard
```

---

# 9. Unit Relationship

```text
Unit
 │
 │ 1:N
 ▼
Item
```

Contoh:

```text
PCS
 ├── Laptop
 ├── Mouse
 └── Keyboard
```

Satu item memiliki satu base unit.

---

# 10. Supplier Relationship

```text
Supplier
 │
 │ 1:N
 ▼
Item
```

Supplier default bersifat optional jika business requirement memungkinkan satu item memiliki banyak supplier.

Supplier pada Item merupakan **default/reference**, bukan histori supplier transaksi.

Histori supplier transaksi tetap berasal dari transaksi Stock In.

---

# 11. Minimum Stock

Item dapat memiliki:

```text
minimum_stock
```

Contoh:

```text
Item:
Mouse Logitech

Current Stock:
8

Minimum Stock:
10
```

Maka:

```text
8 < 10
 ↓
LOW STOCK
```

Perhitungan inventory aktual tidak dilakukan di sprint ini.

---

# 12. Status

Item minimal memiliki:

```text
ACTIVE
INACTIVE
```

Item `INACTIVE`:

```text
✓ masih dapat muncul dalam histori
✕ tidak dapat digunakan untuk transaksi baru
```

kecuali business rule tertentu mengizinkannya.

---

# 13. Delete Strategy

Item sebaiknya tidak langsung dihapus jika sudah digunakan transaksi.

Contoh:

```text
Item
 ↓
Stock In
 ↓
Stock Out
 ↓
Stock Opname
```

Jika sudah memiliki histori:

```text
DELETE
   ↓
REJECT / DEACTIVATE
```

Lebih aman menggunakan status atau soft delete sesuai desain database final.

---

# 14. Item CRUD

Flow:

```text
List
 ↓
Create Item
 ↓
Validation
 ↓
Save
 ↓
Audit
 ↓
Item List
```

Update:

```text
List
 ↓
Edit Item
 ↓
Validation
 ↓
Update
 ↓
Audit
 ↓
Item List
```

---

# 15. Item Detail

Item detail minimal menampilkan:

```text
SKU
Name
Category
Unit
Supplier
Minimum Stock
Status
Created At
Updated At
```

Inventory quantity dapat ditampilkan setelah Inventory Ledger tersedia.

---

# 16. List Page

Minimal:

```text
Items
------------------------------------------------
Search: [ laptop                  ]

Category: [ All ]
Status:   [ Active ]

SKU          Name             Category
ITM-000001   Laptop ThinkPad  Elektronik
ITM-000002   Mouse Logitech   Elektronik
```

Action:

```text
View
Edit
Deactivate
```

Create hanya muncul jika user memiliki permission.

---

# 17. Search

Search dapat berdasarkan:

```text
SKU
Name
```

Concept:

```text
User
 ↓
Search
 ↓
Laravel
 ↓
Database
 ↓
Filtered Items
 ↓
Inertia
 ↓
Vue
```

Jangan mengambil seluruh item ke browser untuk kemudian filtering.

---

# 18. Filtering

Minimal:

```text
Category
Status
```

Dapat dikembangkan menjadi:

```text
Supplier
Low Stock
```

setelah inventory calculation tersedia.

---

# 19. Pagination

Gunakan database pagination.

Concept:

```text
Database
 ↓
Query
 ↓
Pagination
 ↓
Laravel
 ↓
Inertia
 ↓
Vue
```

Untuk data yang sangat besar, cursor pagination dapat dipertimbangkan.

---

# 20. Query Performance

Index yang relevan:

```text
items.sku
items.name
items.category_id
items.unit_id
items.supplier_id
items.status
```

Tidak berarti semua field harus selalu diberi index.

Index ditentukan berdasarkan query pattern.

Gunakan:

```text
EXPLAIN
```

untuk mengevaluasi query penting.

---

# 21. Validation

Minimal:

```text
sku
→ required
→ unique

name
→ required

category_id
→ required
→ exists

unit_id
→ required
→ exists

supplier_id
→ nullable
→ exists

minimum_stock
→ numeric
→ >= 0

status
→ valid enum/value
```

---

# 22. Active Reference Validation

Ketika membuat Item:

```text
Category
 ↓
ACTIVE?
```

Jika inactive:

```text
Reject
```

Begitu juga:

```text
Unit
 ↓
ACTIVE?
```

dan:

```text
Supplier
 ↓
ACTIVE?
```

untuk supplier default.

Jangan hanya memeriksa:

```text
exists
```

Jika business rule mensyaratkan reference aktif.

---

# 23. Backend Authorization

Permission:

```text
item.view
item.create
item.update
item.delete
```

Contoh flow:

```text
POST /items
 ↓
auth
 ↓
item.create
 ↓
Validation
 ↓
Create Item
```

---

# 24. Frontend Permission

Frontend menggunakan permission hanya untuk UX.

Contoh:

```text
can('item.create')
```

maka:

```text
[ + Add Item ]
```

ditampilkan.

Tetapi backend tetap memeriksa:

```text
item.create
```

---

# 25. Database Integrity

Database harus melindungi:

```text
SKU UNIQUE
Category FK
Unit FK
Supplier FK
minimum_stock valid
```

Jika reference dihapus:

```text
Category
 ↓
Item
```

database harus mengikuti foreign key strategy yang sudah ditentukan pada:

```text
05_DATABASE.md
```

---

# 26. Concurrency

Item creation harus aman ketika dua request datang bersamaan.

Contoh:

```text
Request A → ITM-000001
Request B → ITM-000001
```

Application validation saja tidak cukup.

Database:

```text
UNIQUE(sku)
```

menjadi final protection.

---

# 27. Audit Log

Audit minimal:

```text
ITEM_CREATED
ITEM_UPDATED
ITEM_DEACTIVATED
```

Audit mencatat:

```text
who
what
target
when
```

Jika update penting:

```text
old value
new value
```

dapat disimpan sesuai kebutuhan audit system.

---

# 28. Business Rule

### Rule 1

SKU harus unik.

### Rule 2

Item harus memiliki Category.

### Rule 3

Item harus memiliki Unit.

### Rule 4

Supplier default bersifat optional.

### Rule 5

Reference yang digunakan untuk transaksi harus aktif.

### Rule 6

Item yang sudah memiliki transaksi tidak boleh dihapus secara destructive.

### Rule 7

Item inactive tidak digunakan untuk transaksi baru.

---

# 29. Frontend Structure

Concept:

```text
resources/js/Pages/Items/
├── Index.vue
├── Create.vue
├── Edit.vue
└── Show.vue
```

Component reusable jika diperlukan:

```text
resources/js/Components/Items/
├── ItemForm.vue
├── ItemFilters.vue
└── ItemTable.vue
```

Jangan membuat component abstraction terlalu dini jika hanya digunakan sekali.

---

# 30. Backend Structure

Concept:

```text
app/
├── Models/
│   └── Item.php
│
├── Http/
│   ├── Controllers/
│   │   └── ItemController.php
│   │
│   └── Requests/
│       └── Item/
│           ├── StoreItemRequest.php
│           └── UpdateItemRequest.php
│
└── Policies/
    └── ItemPolicy.php
```

Service hanya digunakan jika business logic mulai kompleks.

---

# 31. Request Flow — Create Item

```text
Create.vue
 ↓
POST /items
 ↓
Route
 ↓
Authentication
 ↓
Authorization
 ↓
StoreItemRequest
 ↓
ItemController
 ↓
Item Model / Service
 ↓
Database
 ↓
Audit Log
 ↓
Redirect
 ↓
Index.vue
```

---

# 32. Request Flow — Update Item

```text
Edit.vue
 ↓
PUT/PATCH /items/{item}
 ↓
Route Model Binding
 ↓
Authentication
 ↓
Authorization
 ↓
UpdateItemRequest
 ↓
Controller
 ↓
Model / Service
 ↓
Database
 ↓
Audit
 ↓
Index.vue
```

---

# 33. Maintenance Guide

### "Saya ingin mengubah tampilan tabel Item."

Cari:

```text
resources/js/Pages/Items/Index.vue
```

atau:

```text
resources/js/Components/Items/ItemTable.vue
```

jika component digunakan.

---

### "Saya ingin mengubah form Item."

Cari:

```text
resources/js/Pages/Items/Create.vue
resources/js/Pages/Items/Edit.vue
```

Jika form reusable:

```text
resources/js/Components/Items/ItemForm.vue
```

---

### "Saya ingin menambah field Item."

Ikuti:

```text
Migration
 ↓
Model
 ↓
Form Request
 ↓
Controller / Service
 ↓
Vue Form
 ↓
Vue List / Detail
 ↓
Tests
```

---

### "Saya ingin mengubah validasi."

Cari:

```text
app/Http/Requests/Item/
```

---

### "Saya ingin mengubah siapa yang boleh membuat Item."

Cari:

```text
ItemPolicy
+
item.create
```

Jangan hanya mengubah button Vue.

---

### "Item inactive masih bisa dipakai Stock Out."

Masalah kemungkinan berada di:

```text
Stock Out validation
 ↓
Item status check
```

Bukan hanya di Item Management.

---

# 34. Code Understanding Map

```text
Item Page
 ↓
Inertia Request
 ↓
Route
 ↓
Middleware
 ↓
Policy
 ↓
Form Request
 ↓
Controller
 ↓
Model / Service
 ↓
Database
 ↓
Audit Log
 ↓
Inertia Response
 ↓
Vue
```

Untuk memahami hubungan database:

```text
Item
 ├── category_id → categories.id
 ├── unit_id → units.id
 └── supplier_id → suppliers.id
```

---

# 35. Debugging Flow

Jika Item tidak dapat dibuat:

```text
UI
 ↓
Request
 ↓
Route
 ↓
403?
 ↓
422?
 ↓
Controller
 ↓
Business Logic
 ↓
Database
```

### 403

Periksa:

```text
Authentication
Authorization
Policy
Permission
```

### 422

Periksa:

```text
Validation
Category
Unit
Supplier
SKU
```

### Database error

Periksa:

```text
Foreign Key
Unique Constraint
NOT NULL
Data Type
```

---

# 36. Testing

Minimal:

```text
[ ] Item can be created
[ ] Item can be updated
[ ] Item can be viewed
[ ] Item can be listed
[ ] Duplicate SKU rejected
[ ] Invalid category rejected
[ ] Invalid unit rejected
[ ] Invalid supplier rejected
[ ] Inactive category rejected
[ ] Inactive unit rejected
[ ] Unauthorized create rejected
[ ] Unauthorized update rejected
[ ] Inactive item cannot be used for new transaction
[ ] Item with transaction history cannot be destructively deleted
[ ] Search works
[ ] Filter works
[ ] Pagination works
[ ] Audit log created
```

---

# 37. Acceptance Criteria

Sprint selesai apabila:

```text
1. Item CRUD tersedia.

2. SKU unik.

3. Item memiliki Category.

4. Item memiliki Unit.

5. Supplier dapat digunakan sebagai reference.

6. Minimum stock tersedia.

7. Item memiliki status.

8. Search tersedia.

9. Filter tersedia.

10. Pagination tersedia.

11. Authorization berjalan.

12. Reference validation berjalan.

13. Item yang sudah digunakan transaksi terlindungi dari destructive delete.

14. Audit log tersedia.

15. Database constraints diterapkan.

16. Query penting memiliki index yang relevan.

17. Automated tests berhasil.

18. Code documentation mengikuti standard Inventra.

19. Developer dapat tracing flow Item dari Vue → Laravel → Database.
```

---

# 38. Expected Files

```text
app/
├── Models/
│   └── Item.php
│
├── Http/
│   ├── Controllers/
│   │   └── ItemController.php
│   │
│   └── Requests/
│       └── Item/
│           ├── StoreItemRequest.php
│           └── UpdateItemRequest.php
│
└── Policies/
    └── ItemPolicy.php

database/
└── migrations/
    └── xxxx_create_items_table.php

resources/js/
├── Pages/
│   └── Items/
│       ├── Index.vue
│       ├── Create.vue
│       ├── Edit.vue
│       └── Show.vue
│
└── Components/
    └── Items/

tests/
└── Feature/
    └── Items/
        └── ItemManagementTest.php
```

---

# 39. Code Documentation

Setiap file wajib mengikuti:

```text
docs/code-guide/00_CODE_DOCUMENTATION_STANDARD.md
```

Contoh:

```php
/**
 * Item Controller
 *
 * Purpose:
 * Handle Item management requests.
 *
 * Main Flow:
 * Request
 * → Authorization
 * → Validation
 * → Item Operation
 * → Audit
 * → Inertia Response
 *
 * Related:
 * - Item
 * - Category
 * - Unit
 * - Supplier
 * - ItemPolicy
 */
```

---

# 40. Git Branch

```text
feature/item-management
```

Dependency:

```text
feature/master-data
        ↓
feature/item-management
```

Item Management menggunakan:

```text
Category
Unit
Supplier
```

yang sudah tersedia dari Sprint 03.

---

# 41. Suggested Commits

```text
feat(item): add item model and migration
feat(item): add item CRUD
feat(item): add item validation
feat(item): add item authorization
feat(item): add item search and filters
feat(item): add item pagination
feat(item): add item dependency protection
feat(item): add item audit logging
test(item): add item management tests
docs(item): document item code flow
```

---

# 42. Definition of Done

```text
Code
    ✓ Item CRUD
    ✓ SKU
    ✓ Category relationship
    ✓ Unit relationship
    ✓ Supplier relationship
    ✓ Minimum stock
    ✓ Status

Backend
    ✓ Validation
    ✓ Authorization
    ✓ Dependency protection
    ✓ Audit

Frontend
    ✓ List
    ✓ Create
    ✓ Edit
    ✓ Detail
    ✓ Search
    ✓ Filter
    ✓ Pagination

Database
    ✓ Foreign keys
    ✓ Unique SKU
    ✓ Appropriate indexes

Security
    ✓ Backend authorization
    ✓ Mass assignment protection
    ✓ Input validation

Testing
    ✓ Feature tests pass

Documentation
    ✓ Code comments
    ✓ Maintenance guide
    ✓ Flow documented

Git
    ✓ feature/item-management
```

---

# 43. Final Item Architecture

```text
                         ITEM
                          │
          ┌───────────────┼───────────────┐
          ▼               ▼               ▼
      CATEGORY           UNIT          SUPPLIER
          │               │               │
          └───────────────┼───────────────┘
                          ▼
                         ITEM
                          │
             ┌────────────┼────────────┐
             ▼            ▼            ▼
          STOCK IN    STOCK OUT    STOCK OPNAME
```

Request:

```text
Vue
 ↓
Inertia
 ↓
Route
 ↓
Auth
 ↓
Authorization
 ↓
Validation
 ↓
Controller
 ↓
Service / Model
 ↓
Database
 ↓
Audit
 ↓
Response
 ↓
Vue
```

---

# 44. Key Principle

Item Management hanya bertanggung jawab terhadap:

```text
"What is this item?"
```

Bukan:

```text
"How much stock does it currently have?"
```

Informasi identitas:

```text
SKU
Name
Category
Unit
Supplier
Minimum Stock
Status
```

berada di Item Management.

Sedangkan pergerakan quantity:

```text
Stock In
Stock Out
Stock Opname
```

akan dikelola oleh modul inventory.

Dengan pemisahan ini, Item tidak menjadi tempat menyimpan seluruh logika inventory dan arsitektur Inventra tetap mudah dipahami serta dipelihara.

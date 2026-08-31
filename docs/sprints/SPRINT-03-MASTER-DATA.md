# Inventra

## Sprint 03 — Master Data

**Sprint:** SPRINT-03
**Name:** Master Data
**Project:** Inventra
**Status:** Planned
**Branch:** `feature/master-data`

---

# 1. Sprint Overview

Master Data adalah data referensi yang digunakan oleh berbagai modul Inventra.

Contoh:

```text
Item
 ↓
Category
Unit
Supplier
```

Master Data harus dibuat terlebih dahulu sebelum transaksi inventory karena transaksi akan menggunakan data tersebut sebagai referensi.

---

# 2. Objective

Membangun pengelolaan master data yang:

- Terstruktur.
- Konsisten.
- Dapat digunakan lintas modul.
- Memiliki validasi.
- Memiliki authorization.
- Memiliki audit trail.
- Menghindari duplikasi data.
- Siap digunakan oleh Item Management dan transaksi inventory.

---

# 3. Scope

### Included

```text
Category
Unit
Supplier
Status Management
CRUD
Validation
Search
Filter
Pagination
Soft Delete jika diperlukan
Audit Log
Authorization
```

### Related but Separate

```text
Warehouse
```

Warehouse memiliki sprint tersendiri:

```text
SPRINT-05-WAREHOUSE
```

---

# 4. Master Data Relationship

Concept:

```text
                    MASTER DATA
                         │
          ┌──────────────┼──────────────┐
          ▼              ▼              ▼
      Category          Unit         Supplier
          │              │              │
          └──────────────┼──────────────┘
                         ▼
                        ITEM
```

Item akan menggunakan master data tersebut sebagai foreign key/reference.

---

# 5. Category

Category digunakan untuk mengelompokkan item.

Contoh:

```text
Elektronik
ATK
Peralatan
Sparepart
Consumable
```

Field minimal:

```text
id
code
name
description
status
created_at
updated_at
```

Code harus unik.

---

# 6. Unit

Unit digunakan untuk menentukan satuan item.

Contoh:

```text
PCS
BOX
KG
LITER
METER
SET
```

Field:

```text
id
code
name
description
status
created_at
updated_at
```

Contoh:

```text
PCS
→ Pieces

KG
→ Kilogram
```

Unit yang sudah digunakan transaksi tidak boleh sembarangan dihapus.

---

# 7. Supplier

Supplier menyimpan informasi pemasok.

Field minimal:

```text
id
code
name
phone
email
address
contact_person
status
created_at
updated_at
```

Supplier dapat digunakan oleh Stock In.

---

# 8. Status

Master data menggunakan status:

```text
ACTIVE
INACTIVE
```

Contoh:

```text
Category
→ ACTIVE

Supplier
→ INACTIVE
```

Data inactive tidak boleh digunakan untuk transaksi baru jika business rule melarangnya.

---

# 9. CRUD Flow

Standard flow:

```text
List
 ↓
Create
 ↓
Validate
 ↓
Save
 ↓
Audit
 ↓
List
```

Update:

```text
List
 ↓
Edit
 ↓
Validate
 ↓
Update
 ↓
Audit
 ↓
List
```

Delete:

```text
Delete Request
 ↓
Authorization
 ↓
Check Dependency
 ↓
Soft Delete / Reject
 ↓
Audit
```

---

# 10. Dependency Protection

Sebelum menghapus master data:

```text
Category
 ↓
Dipakai Item?
```

Jika ya:

```text
REJECT DELETE
```

Begitu juga:

```text
Unit
 ↓
Dipakai Item?
```

dan:

```text
Supplier
 ↓
Dipakai Stock In?
```

Jangan menghapus data referensi yang masih dibutuhkan transaksi.

---

# 11. Soft Delete

Untuk master data tertentu, gunakan soft delete jika diperlukan.

Contoh:

```text
Supplier
```

daripada:

```text
DELETE supplier
```

dapat menjadi:

```text
Supplier
 ↓
deleted_at
```

Dengan demikian histori transaksi tetap memiliki referensi yang valid.

---

# 12. Unique Constraint

Database harus membantu mencegah duplikasi.

Contoh:

```text
category.code UNIQUE
unit.code UNIQUE
supplier.code UNIQUE
```

Jika nama harus unik berdasarkan business rule:

```text
category.name UNIQUE
```

juga dapat diterapkan.

Validasi aplikasi **dan** database digunakan bersama.

---

# 13. Backend Structure

Concept:

```text
app/
├── Models/
│   ├── Category.php
│   ├── Unit.php
│   └── Supplier.php
│
├── Http/
│   ├── Controllers/
│   │   └── MasterData/
│   │
│   └── Requests/
│       └── MasterData/
│
└── Services/
    └── MasterData/
```

Tidak harus memaksakan Service untuk CRUD sederhana.

Gunakan Service ketika terdapat business logic yang memang membutuhkan abstraction.

---

# 14. Frontend Structure

Concept:

```text
resources/js/Pages/
└── MasterData/
    ├── Categories/
    │   ├── Index.vue
    │   ├── Create.vue
    │   └── Edit.vue
    │
    ├── Units/
    │   ├── Index.vue
    │   ├── Create.vue
    │   └── Edit.vue
    │
    └── Suppliers/
        ├── Index.vue
        ├── Create.vue
        └── Edit.vue
```

Struktur dapat disesuaikan dengan UI architecture Inventra.

---

# 15. Routes

Concept:

```text
/categories
/categories/create
/categories/{category}/edit

/units
/units/create
/units/{unit}/edit

/suppliers
/suppliers/create
/suppliers/{supplier}/edit
```

Semua route protected oleh:

```text
auth
+
authorization
```

---

# 16. Authorization

Contoh permissions:

```text
category.view
category.create
category.update
category.delete

unit.view
unit.create
unit.update
unit.delete

supplier.view
supplier.create
supplier.update
supplier.delete
```

Authorization tetap dilakukan backend.

---

# 17. List Page

Setiap master data memiliki:

```text
Search
Filter
Pagination
Create Button
Edit
Delete / Deactivate
Status
```

Contoh:

```text
Category
--------------------------------
Search: [ ATK             ]

Code      Name       Status
CAT-001   ATK        ACTIVE
CAT-002   Elektronik ACTIVE
```

---

# 18. Search

Search harus dilakukan dengan query database, bukan mengambil seluruh data lalu filtering di Vue.

Concept:

```text
User
 ↓
Search Request
 ↓
Laravel
 ↓
Database Query
 ↓
Filtered Result
 ↓
Inertia
 ↓
Vue
```

---

# 19. Pagination

Jangan melakukan:

```text
SELECT * FROM categories
```

kemudian melakukan pagination di frontend.

Gunakan database pagination.

Concept:

```text
Database
 ↓
LIMIT
OFFSET / cursor
 ↓
Laravel
 ↓
Inertia
```

Untuk dataset besar, cursor pagination dapat dipertimbangkan.

---

# 20. Query Performance

Master data harus memiliki index pada field yang sering digunakan.

Contoh:

```text
code
name
status
```

Index hanya dibuat berdasarkan kebutuhan query.

Jangan membuat index pada semua kolom secara otomatis.

Prinsip:

```text
Query Pattern
 ↓
EXPLAIN
 ↓
Determine Index
 ↓
Measure
```

---

# 21. Validation

Category:

```text
code
→ required
→ unique

name
→ required
```

Unit:

```text
code
→ required
→ unique

name
→ required
```

Supplier:

```text
code
→ required
→ unique

name
→ required

email
→ nullable
→ valid email
```

Business validation tetap berada di backend.

---

# 22. Code vs Database Validation

Keduanya diperlukan.

Application:

```text
Laravel Validation
```

Database:

```text
UNIQUE
FOREIGN KEY
NOT NULL
CHECK jika diperlukan
```

Tujuannya:

```text
Application
→ User-friendly validation

Database
→ Final data integrity
```

---

# 23. Audit Log

Perubahan master data harus dapat dilacak.

Contoh:

```text
CATEGORY_CREATED
CATEGORY_UPDATED
CATEGORY_DEACTIVATED

UNIT_CREATED
UNIT_UPDATED
UNIT_DEACTIVATED

SUPPLIER_CREATED
SUPPLIER_UPDATED
SUPPLIER_DEACTIVATED
```

Audit menyimpan:

```text
who
what
when
target
```

Jangan menyimpan data sensitif yang tidak diperlukan.

---

# 24. Transaction Handling

CRUD sederhana tidak selalu membutuhkan database transaction eksplisit.

Tetapi gunakan transaction jika satu action mengubah beberapa data yang harus berhasil/gagal bersama.

Contoh:

```text
Create Supplier
 ↓
Supplier
 ↓
Related Data
 ↓
Audit Log
```

Jika seluruh proses harus atomic:

```text
BEGIN
 ↓
Operation
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

# 25. Frontend Responsibility

Vue/Inertia bertanggung jawab terhadap:

```text
Form
UI
Loading
Validation Feedback
Search
Filter
Pagination UI
Confirmation Dialog
```

Backend bertanggung jawab terhadap:

```text
Authorization
Validation
Business Rules
Data Integrity
Database
Audit
```

---

# 26. Inertia Flow

Contoh Create Category:

```text
Category/Create.vue
        │
        │ POST
        ▼
      Route
        │
        ▼
    Controller
        │
        ▼
   Form Request
        │
        ▼
     Database
        │
        ▼
    Audit Log
        │
        ▼
      Inertia
        │
        ▼
 Category/Index.vue
```

---

# 27. Maintenance Guide

### "Saya ingin mengubah tampilan Category."

Buka:

```text
resources/js/Pages/MasterData/Categories/
```

---

### "Saya ingin mengubah validasi Category."

Cari:

```text
app/Http/Requests/MasterData/
```

---

### "Saya ingin mengubah business rule Category."

Cari:

```text
Controller
→ Service jika digunakan
→ Model / Policy
```

Jangan langsung mengubah Vue.

---

### "Saya ingin menambah field Supplier."

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

### "Supplier tidak bisa dihapus."

Periksa:

```text
Supplier
 ↓
Dependency Check
 ↓
Stock In / Transaction
```

Kemungkinan supplier masih digunakan.

---

# 28. Code Understanding Map

Untuk memahami CRUD:

```text
Vue Page
 ↓
Inertia Request
 ↓
Route
 ↓
Controller
 ↓
Form Request
 ↓
Model / Service
 ↓
Database
 ↓
Audit Log
 ↓
Response
 ↓
Vue
```

Untuk debugging:

```text
UI
 ↓
Request
 ↓
Route
 ↓
Authorization
 ↓
Validation
 ↓
Business Logic
 ↓
Query
 ↓
Database
```

Cari titik pertama yang menghasilkan error.

---

# 29. Database Understanding

Jika Category tidak tersimpan:

```text
Category/Create.vue
 ↓
POST
 ↓
CategoryController
 ↓
CategoryRequest
 ↓
Category Model
 ↓
categories table
```

Jika database menolak:

```text
Validation?
 ↓
Constraint?
 ↓
Unique?
 ↓
Foreign Key?
 ↓
Database Error?
```

---

# 30. Security

Master Data harus terlindungi dari:

```text
Unauthorized CRUD
Mass Assignment
SQL Injection
Duplicate Data
Invalid Foreign Key
Privilege Escalation
```

Laravel Query Builder/Eloquent digunakan dengan parameter binding.

Jangan membuat query SQL menggunakan string concatenation dari input user.

---

# 31. Testing

Minimal:

```text
[ ] Category can be created
[ ] Category can be updated
[ ] Category can be listed
[ ] Duplicate category code rejected
[ ] Unauthorized user cannot create category
[ ] Unauthorized user cannot update category
[ ] Unauthorized user cannot delete category

[ ] Unit can be created
[ ] Unit can be updated
[ ] Duplicate unit code rejected

[ ] Supplier can be created
[ ] Supplier can be updated
[ ] Duplicate supplier code rejected

[ ] Used master data cannot be incorrectly deleted
[ ] Search works
[ ] Pagination works
[ ] Audit log created
```

---

# 32. Acceptance Criteria

Sprint selesai apabila:

```text
1. Category CRUD tersedia.

2. Unit CRUD tersedia.

3. Supplier CRUD tersedia.

4. Data memiliki validation.

5. Duplicate code ditolak.

6. Authorization berjalan.

7. Search tersedia.

8. Pagination tersedia.

9. Dependency protection berjalan.

10. Audit perubahan tersedia.

11. Database memiliki constraint yang sesuai.

12. Query menggunakan index yang relevan.

13. Automated tests berhasil.

14. Code documentation mengikuti standard Inventra.

15. Developer dapat tracing CRUD dari Vue → Laravel → Database.
```

---

# 33. Expected Files

Conceptual:

```text
app/
├── Models/
│   ├── Category.php
│   ├── Unit.php
│   └── Supplier.php
│
├── Http/
│   ├── Controllers/MasterData/
│   └── Requests/MasterData/

database/
├── migrations/
└── seeders/

resources/js/
└── Pages/
    └── MasterData/
        ├── Categories/
        ├── Units/
        └── Suppliers/

tests/
└── Feature/
    └── MasterData/
```

---

# 34. Code Documentation

Setiap file mengikuti:

```text
docs/code-guide/00_CODE_DOCUMENTATION_STANDARD.md
```

Minimal menjelaskan:

```text
Purpose
Responsibility
Input
Output
Main Flow
Related Files
```

Contoh Controller:

```php
/**
 * Category Controller
 *
 * Purpose:
 * Handle Category CRUD operations.
 *
 * Flow:
 * Request
 * → Authorization
 * → Validation
 * → Database
 * → Audit
 * → Inertia Response
 *
 * Related:
 * - Category
 * - CategoryRequest
 * - Category Policy
 */
```

---

# 35. Git Branch

```text
feature/master-data
```

Branch ini mencakup:

```text
Category
Unit
Supplier
```

karena ketiganya merupakan satu kelompok pekerjaan Master Data.

---

# 36. Suggested Commits

```text
feat(master-data): add category management
feat(master-data): add unit management
feat(master-data): add supplier management
feat(master-data): add master data validation
feat(master-data): add master data authorization
feat(master-data): add search and pagination
feat(master-data): add dependency protection
test(master-data): add master data tests
docs(master-data): document master data flow
```

---

# 37. Definition of Done

```text
Code
    ✓ Category implemented
    ✓ Unit implemented
    ✓ Supplier implemented

Database
    ✓ Tables implemented
    ✓ Constraints implemented
    ✓ Relevant indexes implemented

Backend
    ✓ CRUD
    ✓ Validation
    ✓ Authorization
    ✓ Dependency protection

Frontend
    ✓ List
    ✓ Create
    ✓ Edit
    ✓ Search
    ✓ Filter
    ✓ Pagination

Security
    ✓ Backend authorization
    ✓ Mass assignment protection
    ✓ Input validation

Audit
    ✓ Changes recorded

Testing
    ✓ Feature tests pass

Documentation
    ✓ Code documented
    ✓ Maintenance flow documented

Git
    ✓ feature/master-data
```

---

# 38. Final Master Data Flow

```text
                       MASTER DATA
                            │
          ┌─────────────────┼─────────────────┐
          ▼                 ▼                 ▼
       CATEGORY            UNIT            SUPPLIER
          │                 │                 │
          └─────────────────┼─────────────────┘
                            ▼
                           ITEM
                            │
                            ▼
                    INVENTORY TRANSACTION
```

CRUD request:

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
Service / Model
 ↓
Database
 ↓
Audit Log
 ↓
Response
 ↓
Vue
```

---

# 39. Key Principle

Master Data adalah **reference data**, bukan transaction data.

Contoh:

```text
Category
Unit
Supplier
```

menjelaskan **data apa yang digunakan**.

Sedangkan:

```text
Stock In
Stock Out
Stock Opname
```

menjelaskan **apa yang terjadi terhadap inventory**.

Karena itu Master Data harus:

```text
Stable
Consistent
Validated
Referenced
Auditable
```

dan perubahan master data harus mempertimbangkan dampaknya terhadap transaksi yang sudah ada.

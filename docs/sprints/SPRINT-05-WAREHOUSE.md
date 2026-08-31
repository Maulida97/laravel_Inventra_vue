# Inventra

## Sprint 05 — Warehouse

**Sprint:** SPRINT-05
**Name:** Warehouse Management
**Project:** Inventra
**Status:** Planned
**Branch:** `feature/warehouse`

---

# 1. Sprint Overview

Warehouse Management mengelola lokasi penyimpanan inventory dalam Inventra.

Concept:

```text
Warehouse
 ├── Code
 ├── Name
 ├── Location
 ├── Manager / PIC
 └── Status
```

Warehouse nantinya menjadi scope utama untuk:

```text
Stock In
Stock Out
Stock Opname
Inventory
Reporting
```

---

# 2. Objective

Membangun Warehouse Management yang:

- Memiliki identitas warehouse yang unik.
- Mendukung banyak warehouse.
- Memiliki status aktif/nonaktif.
- Memiliki informasi lokasi.
- Memiliki PIC/manager.
- Terintegrasi dengan authorization scope.
- Dapat digunakan oleh transaksi inventory.
- Memiliki audit log.
- Memiliki database constraint dan index yang sesuai.

---

# 3. Scope

### Included

```text
Warehouse CRUD
Warehouse Code
Warehouse Name
Location
PIC / Manager
Status
Search
Filter
Pagination
Authorization
Warehouse Scope
Validation
Audit Log
```

### Not Included

```text
Stock In
Stock Out
Stock Opname
Inventory Calculation
Warehouse Transfer
```

Warehouse Transfer dapat ditambahkan sebagai fitur tersendiri jika dibutuhkan.

---

# 4. Warehouse Concept

Contoh:

```text
WH-001
Gudang Utama
Depok
PIC: Budi
ACTIVE
```

Warehouse harus memiliki code yang stabil.

---

# 5. Data Structure

Concept:

```text
warehouses
├── id
├── code
├── name
├── location
├── description
├── manager_id
├── status
├── created_at
└── updated_at
```

`manager_id` dapat mengarah ke user jika business rule menggunakan user sebagai PIC/manager.

Struktur final mengikuti `05_DATABASE.md`.

---

# 6. Warehouse Code

Code harus unik.

Contoh:

```text
WH-001
WH-002
WH-003
```

Gunakan code sebagai business identifier.

Jangan menggunakan nama warehouse sebagai identifier.

---

# 7. Warehouse Status

Minimal:

```text
ACTIVE
INACTIVE
```

Warehouse inactive:

```text
✓ Tetap tersedia untuk histori
✕ Tidak dapat digunakan untuk transaksi baru
```

---

# 8. Warehouse Scope

Warehouse bukan hanya master data.

Warehouse juga berfungsi sebagai **authorization scope**.

Contoh:

```text
Budi
 ↓
WAREHOUSE_MANAGER
 ↓
Warehouse A
```

Budi dapat:

```text
✓ melihat inventory Warehouse A
✓ membuat Stock In Warehouse A
✓ membuat Stock Out Warehouse A
```

Tetapi:

```text
✕ tidak otomatis memiliki akses Warehouse B
```

---

# 9. Role + Permission + Scope

Authorization Inventra:

```text
USER
 ↓
ROLE
 ↓
PERMISSION
 ↓
WAREHOUSE SCOPE
 ↓
RESOURCE
```

Contoh:

```text
Budi
 ↓
WAREHOUSE_MANAGER
 ↓
stock-out.create
 ↓
WH-001
 ↓
Stock Out
```

---

# 10. Scope Assignment

Untuk role/user yang membutuhkan pembatasan warehouse:

```text
User
 ↓
Assigned Warehouse
```

Conceptual relationship:

```text
users
   │
   │ many-to-many
   ▼
warehouses
```

Tidak semua role harus menggunakan scope.

Contoh:

```text
SUPER_ADMIN
→ All warehouses

WAREHOUSE_MANAGER
→ Assigned warehouses

WAREHOUSE_STAFF
→ Assigned warehouse

VIEWER
→ Depends on configuration
```

---

# 11. Warehouse Access Rule

Backend harus memastikan:

```text
Has Permission?
        +
Has Warehouse Access?
```

keduanya harus terpenuhi.

Contoh:

```text
stock-out.create
```

saja belum cukup jika user tidak memiliki akses ke warehouse tersebut.

---

# 12. CRUD Flow

Create:

```text
Warehouse Form
 ↓
Authorization
 ↓
Validation
 ↓
Create Warehouse
 ↓
Audit
 ↓
Redirect
```

Update:

```text
Edit Warehouse
 ↓
Authorization
 ↓
Validation
 ↓
Update
 ↓
Audit
```

Deactivate:

```text
Deactivate
 ↓
Check Dependencies
 ↓
Update Status
 ↓
Audit
```

---

# 13. Dependency Protection

Warehouse yang sudah digunakan transaksi tidak boleh dihapus secara destructive.

Contoh:

```text
Warehouse
 ↓
Stock In
 ↓
Stock Out
 ↓
Stock Opname
```

Jika sudah digunakan:

```text
DELETE
 ↓
REJECT
```

atau:

```text
INACTIVE
```

---

# 14. Active Warehouse Validation

Ketika membuat transaksi:

```text
Warehouse
 ↓
Exists?
 ↓
Active?
 ↓
User Has Access?
```

Semua harus terpenuhi.

Flow:

```text
Transaction
 ↓
Warehouse Validation
 ↓
Authorization Scope
 ↓
Continue
```

---

# 15. List Page

Minimal:

```text
Warehouses
------------------------------------------------

Search: [ warehouse ]

Status: [ Active ]

Code      Name             Location       Status
WH-001    Gudang Utama     Depok          ACTIVE
WH-002    Gudang Cabang    Bogor          ACTIVE
```

Action:

```text
View
Edit
Deactivate
```

---

# 16. Search

Search berdasarkan:

```text
code
name
location
```

Flow:

```text
Vue
 ↓
Inertia
 ↓
Laravel
 ↓
Database Query
 ↓
Result
```

Filtering dilakukan di database.

---

# 17. Filter

Minimal:

```text
Status
```

Dapat dikembangkan:

```text
Manager
Location
```

jika dibutuhkan.

---

# 18. Pagination

Gunakan database pagination.

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

Jangan mengambil seluruh warehouse ke frontend jika jumlah data sudah besar.

---

# 19. Query Performance

Index yang mungkin diperlukan:

```text
warehouses.code
warehouses.name
warehouses.status
warehouses.manager_id
```

Gunakan berdasarkan query pattern.

Evaluasi query menggunakan:

```text
EXPLAIN
```

Jangan menambahkan index secara membabi buta.

---

# 20. Validation

Minimal:

```text
code
→ required
→ unique

name
→ required

location
→ nullable

manager_id
→ nullable
→ exists

status
→ valid value
```

Ketika update:

```text
code
→ unique except current warehouse
```

---

# 21. Manager / PIC

Jika warehouse memiliki manager:

```text
warehouse.manager_id
        ↓
users.id
```

User tersebut harus valid.

Jika manager dinonaktifkan:

```text
User
 ↓
Warehouse
```

business rule perlu menentukan apakah:

```text
Manager diganti
```

atau:

```text
Warehouse boleh tanpa manager sementara
```

Jangan membiarkan foreign key menjadi invalid.

---

# 22. Frontend Structure

Concept:

```text
resources/js/Pages/Warehouses/
├── Index.vue
├── Create.vue
├── Edit.vue
└── Show.vue
```

Reusable components jika diperlukan:

```text
resources/js/Components/Warehouses/
├── WarehouseForm.vue
├── WarehouseTable.vue
└── WarehouseFilters.vue
```

---

# 23. Backend Structure

```text
app/
├── Models/
│   └── Warehouse.php
│
├── Http/
│   ├── Controllers/
│   │   └── WarehouseController.php
│   │
│   └── Requests/
│       └── Warehouse/
│           ├── StoreWarehouseRequest.php
│           └── UpdateWarehouseRequest.php
│
└── Policies/
    └── WarehousePolicy.php
```

---

# 24. Permission

Minimal:

```text
warehouse.view
warehouse.create
warehouse.update
warehouse.delete
```

Jika scope assignment memiliki UI sendiri:

```text
warehouse.assign
```

dapat digunakan.

---

# 25. Authorization Flow

```text
Request
 ↓
Authentication
 ↓
Permission
 ↓
Warehouse Scope
 ↓
Policy
 ↓
Controller
```

Contoh:

```text
GET /warehouses/WH-001
```

Backend memeriksa:

```text
User authenticated?
 ↓
warehouse.view?
 ↓
User can access WH-001?
 ↓
Allow
```

---

# 26. Scope Query

Jika user hanya boleh melihat warehouse tertentu, query harus mempertimbangkan scope.

Concept:

```text
User
 ↓
Allowed Warehouse IDs
 ↓
WHERE warehouse_id IN (...)
 ↓
Database
```

Jangan mengambil seluruh data lalu menyaring di Vue.

---

# 27. Security Boundary

Frontend:

```text
Hide Warehouse B
```

bukan security.

Backend:

```text
User cannot query Warehouse B
```

adalah security.

Contoh attack:

```text
GET /warehouses/2
```

User mencoba mengganti ID secara manual.

Backend harus tetap memeriksa scope.

---

# 28. IDOR Protection

Inventra harus mencegah:

```text
User A
 ↓
Menebak ID resource
 ↓
Mengakses Warehouse B
```

Perlindungan:

```text
Authentication
+
Permission
+
Policy
+
Scope
```

---

# 29. Audit Log

Minimal:

```text
WAREHOUSE_CREATED
WAREHOUSE_UPDATED
WAREHOUSE_DEACTIVATED
WAREHOUSE_SCOPE_CHANGED
```

Contoh:

```text
Admin
 ↓
Assign Budi
 ↓
Warehouse WH-001
```

harus dapat dilacak.

---

# 30. Transaction Handling

Jika pembuatan warehouse juga melakukan operasi lain yang harus atomic:

```text
BEGIN
 ↓
Create Warehouse
 ↓
Assign Scope
 ↓
Audit
 ↓
COMMIT
```

Jika gagal:

```text
ROLLBACK
```

Untuk operasi sederhana yang hanya memodifikasi satu record, transaction eksplisit tidak selalu diperlukan.

---

# 31. Business Rules

### Rule 1

Warehouse code harus unik.

### Rule 2

Warehouse harus memiliki nama.

### Rule 3

Warehouse inactive tidak digunakan untuk transaksi baru.

### Rule 4

Warehouse yang memiliki histori transaksi tidak boleh dihapus secara destructive.

### Rule 5

User hanya dapat mengakses warehouse sesuai scope.

### Rule 6

Super Admin dapat memiliki akses seluruh warehouse.

### Rule 7

Permission dan scope harus diperiksa di backend.

---

# 32. Request Flow — Create

```text
Create.vue
 ↓
POST /warehouses
 ↓
Authentication
 ↓
Authorization
 ↓
Validation
 ↓
WarehouseController
 ↓
Warehouse Model
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

# 33. Request Flow — Scoped Access

```text
User
 ↓
Request
 ↓
Authentication
 ↓
Permission
 ↓
Warehouse Scope
 ↓
Policy
 ↓
Resource
```

Jika scope gagal:

```text
403 Forbidden
```

---

# 34. Maintenance Guide

### "Saya ingin mengubah tampilan Warehouse."

Cari:

```text
resources/js/Pages/Warehouses/
```

---

### "Saya ingin mengubah form."

Cari:

```text
Create.vue
Edit.vue
```

atau:

```text
Components/Warehouses/WarehouseForm.vue
```

---

### "Saya ingin mengubah validasi."

Cari:

```text
app/Http/Requests/Warehouse/
```

---

### "Saya ingin mengubah siapa yang dapat mengakses warehouse."

Cari:

```text
Authorization
 ↓
Policy
 ↓
Warehouse Scope
```

---

### "User bisa melihat warehouse yang bukan miliknya."

Periksa:

```text
Warehouse Scope
 ↓
Policy
 ↓
Query
```

Jangan memperbaikinya hanya di Vue.

---

### "Warehouse tidak bisa dinonaktifkan."

Periksa:

```text
Dependency
 ↓
Stock In
Stock Out
Stock Opname
```

Kemungkinan warehouse masih memiliki dependency yang harus dipertahankan.

---

# 35. Code Understanding Map

Untuk CRUD:

```text
Warehouse Page
 ↓
Inertia Request
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
Model / Service
 ↓
Database
 ↓
Audit
 ↓
Response
```

Untuk access control:

```text
User
 ↓
Role
 ↓
Permission
 ↓
Warehouse Scope
 ↓
Policy
 ↓
Resource
```

---

# 36. Debugging Flow

Jika user tidak bisa mengakses warehouse:

```text
403
 ↓
Authenticated?
 ↓
Permission?
 ↓
Warehouse Scope?
 ↓
Policy?
```

Jika data warehouse salah:

```text
Vue
 ↓
Request
 ↓
Controller
 ↓
Validation
 ↓
Model
 ↓
Database
```

Jika user melihat warehouse yang salah:

```text
Query
 ↓
Scope Filter
 ↓
Policy
```

---

# 37. Testing

Minimal:

```text
[ ] Warehouse can be created
[ ] Warehouse can be updated
[ ] Warehouse can be viewed
[ ] Warehouse can be listed
[ ] Duplicate code rejected
[ ] Invalid manager rejected
[ ] Unauthorized create rejected
[ ] Unauthorized update rejected
[ ] Unauthorized delete rejected

[ ] Inactive warehouse cannot be used for new transaction
[ ] Warehouse with transaction history cannot be destructively deleted

[ ] User can access assigned warehouse
[ ] User cannot access unassigned warehouse
[ ] Super Admin can access all warehouses

[ ] Search works
[ ] Filter works
[ ] Pagination works

[ ] Warehouse changes are audited
[ ] Scope changes are audited
```

---

# 38. Acceptance Criteria

Sprint selesai apabila:

```text
1. Warehouse CRUD tersedia.

2. Warehouse code unik.

3. Status active/inactive tersedia.

4. Manager/PIC dapat digunakan.

5. Search tersedia.

6. Filter tersedia.

7. Pagination tersedia.

8. Authorization tersedia.

9. Warehouse scope tersedia.

10. User tidak dapat mengakses warehouse di luar scope.

11. Inactive warehouse tidak digunakan untuk transaksi baru.

12. Warehouse dengan histori terlindungi dari destructive delete.

13. Audit log tersedia.

14. Database constraints tersedia.

15. Query penting memiliki index yang relevan.

16. Automated tests berhasil.

17. Code documentation tersedia.

18. Developer dapat tracing flow Warehouse dari Vue → Laravel → Database.
```

---

# 39. Expected Files

```text
app/
├── Models/
│   └── Warehouse.php
│
├── Http/
│   ├── Controllers/
│   │   └── WarehouseController.php
│   │
│   └── Requests/
│       └── Warehouse/
│           ├── StoreWarehouseRequest.php
│           └── UpdateWarehouseRequest.php
│
└── Policies/
    └── WarehousePolicy.php

database/
└── migrations/
    └── xxxx_create_warehouses_table.php

resources/js/
└── Pages/
    └── Warehouses/
        ├── Index.vue
        ├── Create.vue
        ├── Edit.vue
        └── Show.vue

tests/
└── Feature/
    └── Warehouse/
        └── WarehouseManagementTest.php
```

---

# 40. Code Documentation

Setiap file mengikuti:

```text
docs/code-guide/00_CODE_DOCUMENTATION_STANDARD.md
```

Contoh:

```php
/**
 * Warehouse Controller
 *
 * Purpose:
 * Manage warehouse resources.
 *
 * Main Flow:
 * Request
 * → Authentication
 * → Authorization
 * → Validation
 * → Warehouse Operation
 * → Audit
 * → Inertia Response
 *
 * Important:
 * Warehouse access is controlled by
 * permission + warehouse scope.
 *
 * Related:
 * - Warehouse
 * - WarehousePolicy
 * - User
 */
```

---

# 41. Git Branch

```text
feature/warehouse
```

Dependency:

```text
feature/master-data
        ↓
feature/item-management
        ↓
feature/warehouse
```

Warehouse dapat menggunakan:

```text
User
Role
Permission
```

dari sprint sebelumnya.

---

# 42. Suggested Commits

```text
feat(warehouse): add warehouse model and migration
feat(warehouse): add warehouse CRUD
feat(warehouse): add warehouse validation
feat(warehouse): add warehouse authorization
feat(warehouse): add warehouse scope
feat(warehouse): add warehouse search and filters
feat(warehouse): add warehouse pagination
feat(warehouse): add dependency protection
feat(warehouse): add warehouse audit logging
test(warehouse): add warehouse management tests
docs(warehouse): document warehouse code flow
```

---

# 43. Definition of Done

```text
Code
    ✓ Warehouse CRUD
    ✓ Warehouse code
    ✓ Status
    ✓ Manager/PIC

Backend
    ✓ Validation
    ✓ Authorization
    ✓ Scope
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
    ✓ Unique constraint
    ✓ Appropriate indexes

Security
    ✓ Backend authorization
    ✓ Scope enforcement
    ✓ IDOR protection
    ✓ Mass assignment protection

Testing
    ✓ Feature tests pass

Documentation
    ✓ Code comments
    ✓ Maintenance guide
    ✓ Request flow documented

Git
    ✓ feature/warehouse
```

---

# 44. Final Warehouse Architecture

```text
                         USER
                           │
                           ▼
                          ROLE
                           │
                           ▼
                       PERMISSION
                           │
                           ▼
                    WAREHOUSE SCOPE
                           │
                           ▼
                       WAREHOUSE
                           │
              ┌────────────┼────────────┐
              ▼            ▼            ▼
           STOCK IN    STOCK OUT    STOCK OPNAME
```

Warehouse menjadi batas penting untuk inventory:

```text
User
 ↓
Can perform action?
 ↓
Can access this warehouse?
 ↓
Can access this resource?
 ↓
ALLOW
```

---

# 45. Key Principle

Warehouse Management menjawab:

```text
"Where is the inventory stored?"
```

Sedangkan:

```text
Item Management
→ What is the item?

Warehouse
→ Where is it stored?

Stock In
→ What entered?

Stock Out
→ What left?

Stock Opname
→ What is the actual quantity?
```

Dengan pemisahan ini, setiap modul memiliki tanggung jawab yang jelas dan lebih mudah ditrace ketika maintenance tanpa bantuan vibe coding.

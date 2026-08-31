# Inventra

## Warehouse Code Guide

**Document:** Warehouse Code Guide
**Version:** V1.0
**Status:** Draft

---

# 1. Purpose

Warehouse Management mengatur lokasi fisik penyimpanan inventory di Inventra.

Warehouse terdiri dari:

```text id="m8zq0a"
Warehouse
 ↓
Location
 ↓
Inventory
 ↓
Stock Transaction
```

Contoh:

```text id="h4f8qz"
Central Warehouse
├── Rack A
│   ├── Shelf A-01
│   └── Shelf A-02
│
└── Rack B
    ├── Shelf B-01
    └── Shelf B-02
```

---

# 2. Warehouse Responsibility

Warehouse module bertanggung jawab terhadap:

- Warehouse.
- Warehouse status.
- Warehouse location.
- Location hierarchy.
- Warehouse access scope.
- Location access scope.
- Warehouse validation.
- Warehouse-related authorization.

Warehouse module **tidak menghitung stock secara langsung**.

Stock quantity berasal dari Inventory/Stock module.

---

# 3. Warehouse Architecture

```text id="0p2q2b"
Warehouse
   │
   └── Locations
          │
          └── Inventory
                 │
                 └── Transactions
```

Contoh:

```text id="l6n2d8"
WH-001
 ↓
Rack A
 ↓
Shelf A-01
 ↓
Item ITM-001
 ↓
Stock
```

---

# 4. Warehouse Identity

Warehouse memiliki:

```text id="3m8pr5"
ID
Code
Name
Status
```

Contoh:

```text id="2v9x7f"
ID:
internal database identifier

Code:
WH-001

Name:
Central Warehouse
```

---

# 5. Warehouse Code

Warehouse code harus unique.

Contoh:

```text id="kqj4a8"
WH-001
WH-002
WH-QC-001
```

Database:

```text id="f1ob6k"
warehouses.code
→ UNIQUE
```

Code sebaiknya stabil dan tidak berubah hanya karena nama warehouse berubah.

---

# 6. Warehouse Status

Minimal:

```text id="k7jv7y"
ACTIVE
INACTIVE
```

ACTIVE:

```text id="0slf3q"
Can be used for new transactions
```

INACTIVE:

```text id="8n8f7g"
Cannot be selected for new transactions
```

Historical transaction tetap dapat menampilkan warehouse tersebut.

---

# 7. Warehouse Deactivation

Jika warehouse sudah memiliki historical transaction:

```text id="c87r8w"
Warehouse
 ↓
Has Transaction
 ↓
Deactivate
```

Jangan langsung:

```text id="4k2z8a"
DELETE Warehouse
```

karena dapat merusak historical relationship.

---

# 8. Location

Location merupakan lokasi penyimpanan di dalam warehouse.

Contoh:

```text id="xwqv4g"
WH-001
 ↓
Rack A
 ↓
Shelf A-01
```

Location harus selalu memiliki parent warehouse.

---

# 9. Location Identity

Location memiliki:

```text id="f2r0c6"
ID
Code
Name
Warehouse
Status
Parent Location
```

Contoh:

```text id="dkw6hv"
Warehouse:
WH-001

Code:
A-01

Name:
Shelf A-01
```

---

# 10. Location Hierarchy

Location dapat memiliki hierarchy jika diperlukan.

Contoh:

```text id="d7qkqv"
Warehouse
└── Rack A
    ├── Shelf A-01
    ├── Shelf A-02
    └── Shelf A-03
```

Concept:

```text id="pj9v5c"
Location
 ↓
Parent Location
 ↓
Child Location
```

---

# 11. Location Validation

Location harus berada di warehouse yang benar.

Invalid:

```text id="f8k0d2"
Warehouse = WH-002
Location = A-01
```

padahal:

```text id="zmxj4c"
A-01 belongs to WH-001
```

Request harus ditolak.

---

# 12. Warehouse Access Scope

Warehouse dapat digunakan sebagai authorization scope.

Contoh:

```text id="r6z2e9"
Warehouse Staff
 ↓
WH-001
```

User hanya dapat mengakses:

```text id="y5v5yr"
WH-001
```

dan tidak:

```text id="1w3v8y"
WH-002
```

---

# 13. Warehouse Scope Flow

```text id="y8l6mm"
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

Contoh:

```text id="9m6f7h"
Warehouse Staff
+
stock.out.create
+
WH-001
=
ALLOW
```

---

# 14. Department vs Warehouse Scope

Keduanya berbeda.

Department:

```text id="f21dvn"
Who / organizational unit?
```

Warehouse:

```text id="h4sm9m"
Where can inventory be accessed?
```

Contoh:

```text id="1xj49r"
IT Staff
Department = IT

Warehouse Staff
Warehouse = WH-001
```

Seorang user dapat memiliki kombinasi scope sesuai business requirement.

---

# 15. Warehouse and Transaction

Warehouse digunakan oleh:

```text id="6q0g8p"
Stock In
Stock Out
Stock Transfer
Stock Opname
Inventory
Reporting
```

Contoh:

```text id="kz4zup"
Stock In
 ↓
WH-001
 ↓
Location A-01
```

---

# 16. Stock In

Stock In membutuhkan warehouse destination.

```text id="p9aqyr"
Supplier
 ↓
Stock In
 ↓
Warehouse
 ↓
Location
 ↓
Inventory
```

Backend harus memastikan:

```text id="5n3qpy"
Warehouse ACTIVE
Location ACTIVE
Location belongs to Warehouse
User authorized
```

---

# 17. Stock Out

Stock Out membutuhkan source warehouse/location.

```text id="bx8n96"
Inventory
 ↓
Warehouse
 ↓
Location
 ↓
Stock Out
```

User tidak boleh Stock Out dari warehouse di luar scope.

---

# 18. Stock Transfer

Stock Transfer memiliki:

```text id="j7jz8y"
Source Warehouse
Source Location

Destination Warehouse
Destination Location
```

Flow:

```text id="z9f8hf"
WH-001 / A-01
       ↓
   Transfer
       ↓
WH-002 / B-01
```

Authorization dapat membutuhkan akses terhadap source dan destination.

---

# 19. Transfer Authorization

Contoh:

User memiliki:

```text id="z2xj4g"
WH-001
```

tetapi tidak:

```text id="4h0f7c"
WH-002
```

Maka transfer:

```text id="p5g1pi"
WH-001 → WH-002
```

tidak otomatis boleh dilakukan.

Policy harus memeriksa scope sesuai business rule.

---

# 20. Stock Opname

Stock Opname menggunakan warehouse/location scope.

```text id="af7l4x"
Stock Opname
 ↓
Warehouse
 ↓
Location
 ↓
Count Inventory
```

User hanya boleh melakukan opname pada area yang diizinkan.

---

# 21. Warehouse CRUD Flow

Create:

```text id="c2b0m8"
Vue
 ↓
Inertia
 ↓
Route
 ↓
Form Request
 ↓
Policy
 ↓
Warehouse Service
 ↓
Database
 ↓
Audit
```

Update:

```text id="b3h5un"
Vue
 ↓
Validation
 ↓
Policy
 ↓
Service
 ↓
Database
 ↓
Audit
```

Deactivate:

```text id="70n2mz"
User
 ↓
Policy
 ↓
Check dependencies
 ↓
Deactivate
 ↓
Audit
```

---

# 22. Location CRUD Flow

```text id="6k1m9n"
Location Form
 ↓
Validation
 ↓
Warehouse Validation
 ↓
Policy
 ↓
Location Service
 ↓
Database
 ↓
Audit
```

---

# 23. Controller Responsibility

Controller:

```text id="3rc7b8"
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

Jangan memasukkan seluruh warehouse business logic ke controller.

---

# 24. Warehouse Service

Conceptual:

```text id="g6w3lq"
WarehouseService
├── create()
├── update()
├── deactivate()
└── restore()
```

Location:

```text id="i7n8av"
LocationService
├── create()
├── update()
├── deactivate()
└── restore()
```

---

# 25. Warehouse Policy

Policy menangani authorization.

Contoh:

```text id="d1a0ik"
WarehousePolicy
├── view
├── create
├── update
└── deactivate
```

Resource scope:

```text id="m3b7xk"
User
 ↓
Warehouse Scope
 ↓
WarehousePolicy
```

---

# 26. Query Scoping

Untuk list warehouse:

```text id="z7f4nt"
User
 ↓
Allowed Warehouse IDs
 ↓
Database Query
 ↓
Only Authorized Warehouses
```

Conceptual:

```php id="u8j2j3"
Warehouse::query()
    ->forUser($user)
    ->active()
    ->paginate();
```

---

# 27. Never Filter Only in Vue

Buruk:

```text id="p3s5jy"
Backend
 ↓
All Warehouses
 ↓
Vue hides unauthorized warehouse
```

Yang benar:

```text id="y2b1a9"
User Scope
 ↓
Database Query
 ↓
Authorized Data
 ↓
Vue
```

Frontend filtering hanya untuk UX.

---

# 28. Location Query

Ketika memilih location:

```text id="x5d7qa"
Selected Warehouse
 ↓
Load Locations
 ↓
Only locations belonging to warehouse
```

Backend tetap melakukan validation.

---

# 29. Prevent Cross-Warehouse Location

Request:

```text id="gj0m0m"
warehouse_id = WH-002
location_id = A-01
```

Backend:

```text id="y2k1a7"
Find Location A-01
 ↓
Check warehouse_id
 ↓
Mismatch
 ↓
DENY
```

Jangan hanya mengandalkan dropdown frontend.

---

# 30. Database Relationships

Concept:

```text id="cm5z3b"
Warehouse
  │
  └── hasMany
          ↓
       Location
```

Location:

```text id="q1g5f6"
Location
  │
  └── belongsTo
          ↓
       Warehouse
```

Jika hierarchy:

```text id="x7r0g5"
Location
 ├── parent
 └── children
```

---

# 31. Foreign Keys

Minimal:

```text id="j3g5h0"
locations.warehouse_id
→ FOREIGN KEY
```

Jika parent location digunakan:

```text id="s8p4y2"
locations.parent_id
→ FOREIGN KEY
```

Foreign key membantu menjaga integrity.

---

# 32. Unique Location Code

Location code sebaiknya unique sesuai scope yang ditentukan.

Contoh jika code hanya unik dalam warehouse:

```text id="b5g3o2"
WH-001 + A-01
WH-002 + A-01
```

keduanya dapat valid.

Database dapat menggunakan composite unique:

```text id="v8t1m6"
UNIQUE (
    warehouse_id,
    code
)
```

Jika business rule membutuhkan globally unique location code, gunakan unique global.

---

# 33. Indexing

Index yang perlu dipertimbangkan:

```text id="8w5t9s"
warehouses.code
warehouses.status

locations.warehouse_id
locations.parent_id
locations.status
```

Jika composite query sering digunakan:

```text id="q7e6x1"
(warehouse_id, code)
```

dapat menggunakan composite index/unique constraint.

---

# 34. Query Performance

Warehouse dan Location sering digunakan sebagai filter transaksi.

Perhatikan:

```text id="a8r2yf"
Foreign Key Index
Eager Loading
Pagination
Scoped Query
```

Contoh:

```php id="z3j1xp"
Warehouse::with('locations')
    ->active()
    ->paginate(20);
```

Gunakan eager loading jika memang diperlukan oleh response.

---

# 35. Search

Warehouse:

```text id="5j3b6n"
Code
Name
```

Location:

```text id="5w2g8k"
Code
Name
Warehouse
```

Search dilakukan di database.

---

# 36. Active / Inactive Filtering

Default transaction selection:

```text id="7r3p9d"
ACTIVE only
```

Historical reporting:

```text id="q4b7n5"
ACTIVE + INACTIVE
```

Contoh:

```text id="8q0f1a"
Stock Out lama
→ WH-OLD
→ tetap dapat dilihat
```

---

# 37. Warehouse Deactivation Safety

Sebelum deactivate:

```text id="2u0c3w"
Check:
├── Existing inventory?
├── Open transactions?
├── Pending transfer?
└── Other dependencies?
```

Business rule menentukan apakah:

```text id="r4k9p6"
ALLOW
```

atau:

```text id="j5s3n8"
DENY
```

---

# 38. Security

Warehouse authorization harus menggunakan:

```text id="4t6b8c"
Authentication
+
Permission
+
Warehouse Scope
+
Policy
+
Resource Validation
```

Contoh:

```text id="4x0q9a"
User
 ↓
stock.out.create
 ↓
WH-001 scope
 ↓
StockOutPolicy
 ↓
ALLOW
```

---

# 39. Common Mistakes

### Mistake 1

User hanya punya akses WH-001 tetapi dapat request:

```text id="e6k8h2"
warehouse_id = WH-002
```

Solusi:

```text id="q2s5n7"
Backend scope validation
```

---

### Mistake 2

Location tidak dicek terhadap warehouse.

Solusi:

```text id="m4r7c1"
location.warehouse_id === selected warehouse
```

---

### Mistake 3

Menghapus warehouse yang memiliki historical transaction.

Solusi:

```text id="p8v2n6"
Deactivate
```

---

### Mistake 4

Mengirim semua warehouse ke frontend.

Solusi:

```text id="d9x5r3"
Scoped database query
```

---

# 40. Maintenance Guide

### "Saya mau mengubah tampilan daftar warehouse."

Cari:

```text id="s4n7w2"
resources/js/Pages/Warehouses/Index.vue
```

---

### "Saya mau menambah field Warehouse."

Ikuti:

```text id="j3m6p8"
Migration
 ↓
Model
 ↓
Form Request
 ↓
Service
 ↓
Vue Form
 ↓
Resource/Response
```

---

### "Saya mau menambah Location."

Cari:

```text id="n8c2f4"
LocationController
LocationService
Location Model
Location Vue pages
```

---

### "User tertentu tidak boleh melihat warehouse tertentu."

Cari:

```text id="h6r3v9"
Warehouse Scope
+
WarehousePolicy
+
Scoped Query
```

---

### "Location A-01 muncul di warehouse yang salah."

Periksa:

```text id="y7k2m5"
locations.warehouse_id
+
relationship
+
query filter
```

---

### "Stock Out dari warehouse tertentu harus dilarang."

Jangan mengubah `Warehouse.vue`.

Cari:

```text id="b4p8q1"
StockOutPolicy
+
Warehouse Scope
```

---

# 41. Code Reading Flow

Untuk memahami Warehouse:

```text id="z5m8r2"
Index.vue
 ↓
Route
 ↓
Controller
 ↓
Form Request
 ↓
Policy
 ↓
Service
 ↓
Model
 ↓
Relationship
 ↓
Migration
 ↓
Database
```

Untuk memahami akses:

```text id="c9v4n7"
User
 ↓
Role
 ↓
Permission
 ↓
Warehouse Scope
 ↓
Policy
```

Untuk memahami Location:

```text id="w2q6s8"
Warehouse
 ↓
Location
 ↓
Inventory
 ↓
Transaction
```

---

# 42. Debugging Checklist

Jika warehouse tidak muncul:

```text id="x1k4p7"
[ ] Warehouse exists?
[ ] Status active?
[ ] User has permission?
[ ] User has warehouse scope?
[ ] Query scope?
[ ] Search/filter?
[ ] Pagination?
```

Jika location tidak muncul:

```text id="m7q2x5"
[ ] Location exists?
[ ] Location active?
[ ] Correct warehouse?
[ ] Query filter?
[ ] User authorized?
```

Jika transaction gagal:

```text id="v4c8n1"
[ ] Warehouse active?
[ ] Location active?
[ ] Location belongs to warehouse?
[ ] User has scope?
[ ] Policy passed?
```

---

# 43. Testing

Minimal:

```text id="f3j8q2"
[ ] Create warehouse
[ ] Update warehouse
[ ] Deactivate warehouse
[ ] Duplicate warehouse code rejected
[ ] Create location
[ ] Update location
[ ] Location belongs to correct warehouse
[ ] Invalid warehouse-location combination rejected
[ ] Unauthorized warehouse access rejected
[ ] Unauthorized location access rejected
[ ] Inactive warehouse rejected for new transaction
[ ] Historical warehouse remains accessible
[ ] Warehouse scope works
[ ] Pagination works
[ ] Search works
```

---

# 44. Definition of Done

```text id="q8w3m6"
[ ] Warehouse CRUD
[ ] Location CRUD
[ ] Active/Inactive
[ ] Warehouse scope
[ ] Policy
[ ] Validation
[ ] Foreign keys
[ ] Unique constraints
[ ] Index reviewed
[ ] Scoped queries
[ ] Audit
[ ] Tests
[ ] Documentation
```

---

# 45. Final Warehouse Flow

```text id="r7m2k5"
                       USER
                        │
                        ▼
                 AUTHENTICATION
                        │
                        ▼
                      RBAC
                        │
                 ┌──────┴──────┐
                 ▼             ▼
             Permission      Scope
                               │
                               ▼
                          WAREHOUSE
                               │
                               ▼
                           LOCATION
                               │
                               ▼
                          INVENTORY
                               │
                               ▼
                         TRANSACTION
```

---

# 46. Key Principle

Warehouse menjawab:

> **"Di mana inventory berada?"**

RBAC menjawab:

> **"Siapa yang boleh mengakses warehouse tersebut?"**

Inventory menjawab:

> **"Berapa quantity item di sana?"**

Transaction menjawab:

> **"Apa yang terjadi terhadap inventory tersebut?"**

Jangan mencampurkan keempat responsibility tersebut.

```text id="n6r1v8"
Warehouse
→ Physical Structure

RBAC
→ Access Control

Inventory
→ Current Quantity

Transaction
→ Historical Event
```

Dengan pemisahan ini, ketika kamu nanti ingin mengubah tampilan, authorization, struktur lokasi, atau logic stock, kamu dapat mengikuti dependency yang benar tanpa perlu melakukan perubahan secara acak.

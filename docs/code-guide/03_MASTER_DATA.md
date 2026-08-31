# Inventra

## Master Data Code Guide

**Document:** Master Data Code Guide
**Version:** V1.0
**Status:** Draft

---

# 1. Purpose

Master Data adalah data referensi utama yang digunakan oleh module lain.

Master Data harus:

```text
Consistent
Validated
Reusable
Traceable
Controlled
```

Perubahan Master Data tidak boleh merusak historical transaction.

---

# 2. Master Data Scope

Master Data Inventra meliputi:

```text
Master Data
├── Users
├── Departments
├── Roles
├── Permissions
├── Warehouses
├── Locations
├── Item Categories
├── Item Units
├── Items
├── Suppliers
└── Other reference data
```

Tidak semua data di atas memiliki ownership yang sama.

Contoh:

```text
RBAC
→ Roles / Permissions

Warehouse
→ Warehouses / Locations

Item Management
→ Items / Categories / Units
```

---

# 3. Master Data Architecture

```text
                    MASTER DATA
                         │
        ┌────────────────┼────────────────┐
        ▼                ▼                ▼
    Organization       Inventory       Security
        │                │                │
   Department         Item             Role
   Warehouse          Category         Permission
   Location           Unit
                     Supplier
        │                │
        └────────────────┼────────────────┘
                         ▼
                    Transactions
```

Master Data menjadi referensi, sedangkan transaction menyimpan historical event.

---

# 4. Master Data Principle

Prinsip utama:

```text
Master Data
     ↓
Reference
     ↓
Transaction
     ↓
Historical Record
```

Contoh:

```text
Item
 ↓
Stock In
 ↓
Stock Ledger
```

Jika nama item berubah:

```text
Item Name:
Laptop Lenovo
        ↓
Laptop Lenovo ThinkPad
```

historical transaction tidak boleh kehilangan identitas item yang digunakan saat transaksi.

---

# 5. Master Data vs Transaction

### Master Data

Contoh:

```text
Item
Warehouse
Department
Supplier
Unit
```

### Transaction

Contoh:

```text
Stock In
Stock Out
Stock Transfer
Stock Opname
Purchase Request
Asset Assignment
```

Transaction memiliki timestamp dan historical context.

---

# 6. Soft Delete

Master Data yang sudah digunakan transaction sebaiknya tidak langsung dihapus secara physical.

Contoh:

```text
Item
 ↓
Stock In
 ↓
Stock Out
```

Kemudian item tidak digunakan lagi.

Lebih aman:

```text
Item
 ↓
Inactive
```

daripada:

```text
DELETE Item
```

Tujuannya menjaga referential integrity dan historical data.

---

# 7. Active / Inactive

Master Data menggunakan status jika diperlukan.

Contoh:

```text
ACTIVE
INACTIVE
```

Data inactive:

```text
Cannot be selected for new transaction
```

tetapi:

```text
Existing historical transaction
remains accessible
```

---

# 8. Master Data Validation

Semua Master Data harus divalidasi server-side.

Contoh:

```text
Item
├── Name
├── Code
├── Category
├── Unit
└── Status
```

Validation:

```text
Required
Format
Uniqueness
Relationship
Business Rule
```

---

# 9. Unique Code

Master Data penting menggunakan business identifier.

Contoh:

```text
ITEM-00001
WH-001
LOC-A01
SUP-0001
```

Code harus memiliki uniqueness constraint pada database jika memang menjadi identifier unik.

---

# 10. Database Constraints

Application validation bukan satu-satunya protection.

Database juga harus memiliki constraint yang diperlukan:

```text
PRIMARY KEY
FOREIGN KEY
UNIQUE
NOT NULL
CHECK
```

Contoh:

```text
items.code
→ UNIQUE
```

Dengan demikian duplicate data tetap ditolak meskipun request masuk secara bersamaan.

---

# 11. Master Data Request Flow

Create:

```text
Vue
 ↓
Inertia
 ↓
Route
 ↓
Form Request
 ↓
Authorization
 ↓
Service
 ↓
Database
 ↓
Audit Log
 ↓
Response
```

Update:

```text
Vue
 ↓
Inertia
 ↓
Validation
 ↓
Authorization
 ↓
Business Rule
 ↓
Database
 ↓
Audit Log
```

---

# 12. Controller Responsibility

Controller hanya menjadi entry point.

```text
Controller
 ↓
Validate
 ↓
Authorize
 ↓
Call Service
 ↓
Response
```

Jangan menaruh seluruh CRUD logic di controller jika logic mulai kompleks.

---

# 13. Service Responsibility

Service menangani business operation.

Contoh:

```text
ItemService
DepartmentService
WarehouseService
LocationService
SupplierService
```

Contoh:

```php
$item = $this->itemService->create(
    $validatedData,
    $user
);
```

Service dapat menangani:

- Business rule.
- Relationship.
- Transaction.
- Audit event.

---

# 14. Model Responsibility

Model menangani:

```text
Database Representation
Relationships
Casts
Scopes
Model-specific behavior
```

Contoh:

```text
Item
├── Category
├── Unit
└── Supplier
```

---

# 15. Item Master

Item adalah salah satu Master Data utama.

Conceptual:

```text
Item
├── Code
├── Name
├── Category
├── Base Unit
├── Status
└── Other Attributes
```

Item digunakan oleh:

```text
Stock In
Stock Out
Stock Transfer
Stock Opname
Purchase Request
Asset
Reporting
```

---

# 16. Item Code

Item code harus stable.

Contoh:

```text
ITM-LAP-001
```

Sebaiknya code tidak berubah hanya karena:

```text
Name changed
Category changed
Supplier changed
```

Historical transaction menggunakan item identity.

---

# 17. Item Category

Category membantu pengelompokan:

```text
IT Equipment
Office Supplies
QC Equipment
Maintenance
Consumable
Asset
```

Category dapat digunakan untuk:

```text
Filtering
Reporting
Authorization Scope
```

---

# 18. Unit

Unit adalah referensi quantity.

Contoh:

```text
PCS
BOX
PACK
LITER
KG
SET
```

Base unit:

```text
PCS
```

Package:

```text
BOX
```

dapat memiliki content:

```text
1 BOX = 100 PCS
```

---

# 19. Content Per Unit

Inventra mendukung konsep package content.

Contoh:

```text
1 BOX = 100 PCS
```

Jika transaction:

```text
6 BOX
```

maka equivalent quantity:

```text
6 × 100
=
600 PCS
```

Perhitungan harus dilakukan di backend.

Frontend tidak boleh menjadi sumber kebenaran stock calculation.

---

# 20. Historical Package Information

Package information yang digunakan dalam transaction harus dapat direproduksi.

Contoh:

```text
Transaction:
6 BOX

Content:
100 PCS / BOX

Equivalent:
600 PCS
```

Jika supplier kemudian mengubah packaging:

```text
1 BOX = 120 PCS
```

historical transaction tetap:

```text
6 BOX = 600 PCS
```

bukan berubah menjadi:

```text
720 PCS
```

---

# 21. Department

Department digunakan untuk organization scope.

Contoh:

```text
IT
QC
Finance
HR
Warehouse
```

Department dapat berhubungan dengan:

```text
User
Purchase Request
Item Scope
Approval
```

---

# 22. Department-Item Scope

Department dapat memiliki item restriction.

Concept:

```text
Department
     │
     ▼
Allowed Items
```

Contoh:

```text
IT
├── Laptop
├── Monitor
└── Network Equipment
```

QC:

```text
QC
├── Testing Equipment
└── QC Consumables
```

Mapping ini digunakan oleh authorization layer.

---

# 23. Warehouse

Warehouse menyimpan inventory physical location.

Concept:

```text
Warehouse
├── Code
├── Name
├── Status
└── Locations
```

Contoh:

```text
WH-001
Central Warehouse
```

---

# 24. Location

Location berada di dalam warehouse.

```text
WH-001
 ↓
Rack A
 ↓
Shelf A-01
```

Location membantu inventory tracking.

---

# 25. Supplier

Supplier adalah Master Data untuk sumber barang.

Contoh:

```text
Supplier
├── Code
├── Name
├── Contact
└── Status
```

Supplier dapat digunakan oleh:

```text
Purchase Request
Purchase Order (future)
Stock In
Reporting
```

---

# 26. Master Data Relationships

Conceptual:

```text
Department
   │
   ├── Users
   └── Allowed Items

Warehouse
   │
   └── Locations

Item
   ├── Category
   ├── Unit
   └── Supplier

Transaction
   ├── Item
   ├── Warehouse
   ├── Location
   └── User
```

---

# 27. Referential Integrity

Jika Item digunakan transaction:

```text
Item
 ↓
Stock Transaction
```

Item tidak boleh dihapus secara sembarangan.

Database foreign key menjaga relationship.

---

# 28. Master Data Authorization

Tidak semua user boleh mengubah Master Data.

Contoh:

```text
Admin
→ Manage all

Warehouse Manager
→ Manage warehouse-related data

Department Manager
→ Manage department-related configuration

Warehouse Staff
→ View required master data
```

Authorization tetap mengikuti:

```text
Permission
+
Scope
+
Policy
```

---

# 29. Master Data Create

Contoh Item:

```text
User
 ↓
item.create
 ↓
ItemPolicy
 ↓
Validation
 ↓
ItemService
 ↓
Database
 ↓
Audit
```

---

# 30. Master Data Update

```text
User
 ↓
item.update
 ↓
ItemPolicy
 ↓
Load Item
 ↓
Validate
 ↓
Business Rules
 ↓
Update
 ↓
Audit
```

---

# 31. Master Data Deactivation

Untuk data yang sudah digunakan:

```text
Active
 ↓
Deactivate
 ↓
Inactive
```

New transaction:

```text
Inactive Item
 ↓
DENY
```

Historical:

```text
Old Transaction
 ↓
Still Accessible
```

---

# 32. Prevent Invalid References

Sebelum transaction dibuat:

```text
Item
 ↓
Exists?
 ↓
Active?
 ↓
Allowed?
```

Warehouse:

```text
Warehouse
 ↓
Exists?
 ↓
Active?
 ↓
User has scope?
```

Location:

```text
Location
 ↓
Exists?
 ↓
Belongs to Warehouse?
 ↓
Allowed?
```

---

# 33. Query Scopes

Model dapat memiliki reusable query scope.

Contoh:

```php
Item::active()
```

atau:

```php
Warehouse::active()
```

Tujuan:

```text
Consistent Filtering
Readable Query
Less Duplication
```

---

# 34. Eager Loading

Ketika menampilkan relationship:

```text
Item
 ↓
Category
 ↓
Supplier
```

gunakan eager loading bila diperlukan.

Contoh:

```php
Item::with([
    'category',
    'supplier',
])->paginate();
```

Tujuan utama:

```text
Prevent N+1 Query
```

---

# 35. Indexing

Kolom yang sering digunakan untuk:

```text
Search
Filter
Join
Foreign Key
Unique Lookup
```

perlu dipertimbangkan untuk indexing.

Contoh:

```text
items.code
items.category_id
items.status

warehouses.code
locations.warehouse_id
```

Index harus dibuat berdasarkan query pattern nyata, bukan asal menambahkan index.

---

# 36. Search

Master Data dapat menyediakan search.

Contoh:

```text
Search Item
 ↓
Code
Name
Category
```

Search dilakukan di database.

Untuk dataset besar:

```text
Pagination
+
Proper Index
```

digunakan untuk menjaga performance.

---

# 37. Pagination

Jangan mengambil seluruh Master Data sekaligus jika dataset dapat besar.

Buruk:

```php
Item::all();
```

untuk halaman yang bisa memiliki ribuan item.

Lebih baik:

```php
Item::paginate(20);
```

atau jumlah yang sesuai kebutuhan UI.

---

# 38. Audit

Create/update/deactivate Master Data penting dicatat.

Contoh:

```text
Item Created
Item Updated
Item Deactivated
Warehouse Created
Warehouse Updated
Department Scope Changed
```

Audit mencatat:

```text
Actor
Action
Resource
Timestamp
Relevant Changes
```

---

# 39. Common Mistakes

### Mistake 1 — Hard Delete

```text
DELETE Item
```

padahal item sudah digunakan transaction.

Gunakan:

```text
INACTIVE
```

jika sesuai business rule.

---

### Mistake 2 — Business Logic di Vue

Contoh:

```text
Vue menghitung stock
```

Tidak boleh.

Calculation harus dilakukan backend.

---

### Mistake 3 — Tidak Memvalidasi Relationship

Contoh:

```text
Location = A-01
Warehouse = WH-002
```

padahal A-01 milik WH-001.

Request harus ditolak.

---

### Mistake 4 — Tidak Ada Database Constraint

Application validation dapat memiliki race condition.

Gunakan database constraint untuk invariant yang memang harus selalu benar.

---

# 40. Code Reading Flow

Ketika mempelajari Master Data:

```text
Page
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
Migration
 ↓
Database
 ↓
Audit
```

Pertanyaan:

```text
Data masuk dari mana?
Siapa yang boleh mengubah?
Validation berada di mana?
Business rule berada di mana?
Relationship apa yang digunakan?
Database constraint apa yang digunakan?
Audit dibuat di mana?
```

---

# 41. Maintenance Guide

### "Saya mau mengubah tampilan Item."

Cari:

```text
resources/js/Pages/Items/
```

---

### "Saya mau menambah field Item."

Periksa:

```text
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
Vue Table/Detail
```

---

### "Saya mau mengubah validation Item."

Cari:

```text
StoreItemRequest
UpdateItemRequest
```

---

### "Saya mau mengubah siapa yang boleh membuat Item."

Cari:

```text
ItemPolicy
+
item.create permission
```

---

### "Saya mau menambah kategori."

Cari:

```text
ItemCategory
```

dan module/category management.

---

### "Saya mau mengubah konversi BOX ke PCS."

Cari:

```text
Unit / Package Content
+
Inventory / Transaction Service
```

Jangan hanya mengubah Vue.

---

### "Saya mau menonaktifkan Item."

Cari:

```text
ItemService
+
ItemPolicy
```

dan pastikan transaction baru menolak item inactive.

---

# 42. Testing

Minimal:

```text
[ ] Create master data
[ ] Update master data
[ ] Deactivate master data
[ ] Duplicate code rejected
[ ] Invalid relationship rejected
[ ] Unauthorized user rejected
[ ] Inactive item cannot be used for new transaction
[ ] Historical transaction remains accessible
[ ] Department-item scope works
[ ] Warehouse-location relationship works
```

---

# 43. Security Testing

```text
[ ] User cannot modify unauthorized master data
[ ] User cannot access another department's restricted data
[ ] User cannot access unauthorized warehouse
[ ] User cannot bypass inactive status
[ ] User cannot manipulate created_by
[ ] User cannot bypass database constraints
```

---

# 44. Definition of Done

Master Data feature dianggap selesai jika:

```text
[ ] CRUD implemented where applicable
[ ] Validation implemented
[ ] Authorization implemented
[ ] Scope implemented where required
[ ] Database constraints implemented
[ ] Index reviewed
[ ] Audit implemented
[ ] Tests implemented
[ ] UI implemented
[ ] Documentation updated
[ ] Maintenance path documented
```

---

# 45. Final Master Data Flow

```text
                     MASTER DATA
                          │
                          ▼
                         PAGE
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
             VALIDATION       AUTHORIZATION
                 │                 │
                 └────────┬────────┘
                          ▼
                       SERVICE
                          │
                          ▼
                        MODEL
                          │
                          ▼
                       DATABASE
                          │
                 ┌────────┴────────┐
                 ▼                 ▼
              AUDIT             RESPONSE
                                   │
                                   ▼
                                  VUE
```

---

# 46. Key Principle

Master Data adalah **foundation**, bukan tempat menyimpan historical transaction.

Prinsip Inventra:

```text
Master Data
=
Current Reference

Transaction
=
Historical Event

Ledger
=
Inventory History

Audit
=
Accountability
```

Perubahan Master Data tidak boleh mengubah arti historical transaction yang sudah terjadi.

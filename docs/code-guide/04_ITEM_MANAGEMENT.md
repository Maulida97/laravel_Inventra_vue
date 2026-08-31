# Inventra

## Item Management Code Guide

**Document:** Item Management Code Guide
**Version:** V1.0
**Status:** Draft

---

# 1. Purpose

Item Management mengatur seluruh data dan aturan mengenai barang yang dikelola Inventra.

Item menjadi referensi utama untuk:

```text
Stock In
Stock Out
Stock Transfer
Stock Opname
Purchase Request
Asset Management
Reporting
```

---

# 2. Item Architecture

```text
Item
├── Item Code
├── Name
├── Category
├── Unit
├── Package / Conversion
├── Supplier
├── Status
└── Other Attributes
```

Relationship:

```text
Category
     │
     ▼
    Item
     │
 ┌───┼────────┐
 ▼   ▼        ▼
Unit Supplier Status
```

---

# 3. Item Responsibility

Item Management bertanggung jawab terhadap:

- Item CRUD.
- Item code.
- Item category.
- Unit.
- Package conversion.
- Item status.
- Item search.
- Item filtering.
- Item activation/deactivation.
- Department-item eligibility.

Item Management **tidak menghitung stock secara langsung**.

Stock quantity ditangani oleh Inventory/Stock module.

---

# 4. Item Identity

Setiap item memiliki identity yang stabil.

Contoh:

```text
ITEM CODE
ITM-LAP-001
```

dan:

```text
ITEM ID
UUID / BIGINT
```

Keduanya memiliki fungsi berbeda.

```text
ID
→ Database relationship

CODE
→ Business identifier
```

---

# 5. Item Code

Item code harus unique.

Contoh:

```text
ITM-LAP-001
ITM-MON-001
ITM-QC-001
```

Database harus memberikan unique constraint.

```text
items.code
→ UNIQUE
```

Jangan hanya mengandalkan validation Laravel.

---

# 6. Item Name

Nama item digunakan untuk display.

Contoh:

```text
Laptop Lenovo ThinkPad E14
```

Nama dapat berubah.

Contoh:

```text
Laptop Lenovo E14
        ↓
Laptop Lenovo ThinkPad E14
```

Item identity tetap sama selama item code/ID tetap sama.

---

# 7. Item Category

Category digunakan untuk klasifikasi.

Contoh:

```text
IT Equipment
Office Supplies
QC Equipment
Maintenance
Consumable
```

Relationship:

```text
Category
   │
   └── Items
```

Category juga dapat digunakan sebagai authorization scope.

---

# 8. Item Category Scope

Department dapat dibatasi berdasarkan category.

Contoh:

```text
IT Department
 ↓
IT Equipment
```

QC:

```text
QC Department
 ↓
QC Equipment
```

Dengan demikian Department Staff tidak otomatis dapat membuat PR untuk semua item.

---

# 9. Item Status

Item minimal memiliki:

```text
ACTIVE
INACTIVE
```

Optional future:

```text
DISCONTINUED
```

Untuk V1:

```text
ACTIVE
INACTIVE
```

sudah cukup jika business requirement belum membutuhkan status tambahan.

---

# 10. Active Item

Item ACTIVE:

```text
Can be selected
Can be used in new transactions
```

Contoh:

```text
Stock In
Stock Out
Purchase Request
```

tergantung permission dan business rule.

---

# 11. Inactive Item

Item INACTIVE:

```text
Cannot be used for new transaction
```

tetapi:

```text
Historical transaction
        ↓
Still accessible
```

Contoh:

```text
Laptop XYZ
 ↓
INACTIVE
```

Stock Out lama tetap menampilkan:

```text
Laptop XYZ
```

---

# 12. Item Deletion

Physical delete sebaiknya tidak digunakan jika item sudah memiliki historical reference.

Gunakan:

```text
Deactivate
```

daripada:

```text
DELETE
```

Contoh:

```text
Item
 ↓
Has Transactions
 ↓
Cannot Hard Delete
 ↓
Deactivate
```

---

# 13. Unit

Item memiliki base unit.

Contoh:

```text
Laptop
→ PCS

Cable
→ METER

Paper
→ PACK
```

Base unit digunakan sebagai dasar quantity inventory.

---

# 14. Base Unit

Base unit adalah unit penyimpanan quantity utama.

Contoh:

```text
Item:
Cable

Base Unit:
METER
```

Stock:

```text
500 METER
```

---

# 15. Package Unit

Item dapat memiliki package unit.

Contoh:

```text
Base Unit = PCS

Package:
BOX
```

Conversion:

```text
1 BOX = 100 PCS
```

---

# 16. Unit Conversion

Concept:

```text
Transaction Quantity
        ↓
Conversion
        ↓
Base Quantity
```

Contoh:

```text
6 BOX
×
100 PCS
=
600 PCS
```

Perhitungan dilakukan backend.

---

# 17. Conversion Data

Conceptual:

```text
Item
 ├── Base Unit
 └── Unit Conversion
       ├── From Unit
       ├── To Unit
       └── Conversion Factor
```

Contoh:

```text
BOX → PCS
Factor = 100
```

---

# 18. Historical Conversion

Conversion yang sudah digunakan dalam transaction harus dapat dipertahankan secara historical.

Contoh:

```text
Transaction
6 BOX
Conversion
1 BOX = 100 PCS
Result
600 PCS
```

Jika master conversion berubah:

```text
1 BOX = 120 PCS
```

transaction lama tetap:

```text
600 PCS
```

---

# 19. Item Supplier

Item dapat memiliki supplier reference.

Concept:

```text
Item
 ↓
Supplier
```

Jika satu item dapat dibeli dari banyak supplier:

```text
Item
 ├── Supplier A
 ├── Supplier B
 └── Supplier C
```

gunakan relationship many-to-many jika memang diperlukan oleh business requirement.

---

# 20. Item Form

Frontend:

```text
resources/js/Pages/Items/
├── Index.vue
├── Create.vue
├── Edit.vue
└── Show.vue
```

Conceptual fields:

```text
Code
Name
Category
Base Unit
Supplier
Status
```

Package conversion dapat dikelola pada bagian terpisah jika UI menjadi kompleks.

---

# 21. Item Create Flow

```text
Create Item
 ↓
Vue Form
 ↓
Inertia POST
 ↓
Route
 ↓
Form Request
 ↓
Authorization
 ↓
Item Service
 ↓
Database
 ↓
Audit Log
 ↓
Response
```

---

# 22. Item Update Flow

```text
Edit Item
 ↓
Vue
 ↓
Inertia PUT/PATCH
 ↓
Validation
 ↓
Authorization
 ↓
Item Service
 ↓
Database
 ↓
Audit
```

---

# 23. Item Deactivate Flow

```text
User
 ↓
Deactivate Item
 ↓
Permission Check
 ↓
Item Policy
 ↓
Check Existing Usage
 ↓
Update Status
 ↓
Audit
```

Jika item memiliki transaction:

```text
DO NOT DELETE
```

---

# 24. Item Policy

Policy menentukan apakah user boleh melakukan action terhadap item.

Contoh:

```text
ItemPolicy
├── view
├── create
├── update
└── deactivate
```

Authorization:

```text
Permission
+
Scope
+
Policy
```

---

# 25. Department Item Authorization

Purchase Request menggunakan item authorization.

Flow:

```text
Department Staff
 ↓
Create PR
 ↓
Select Item
 ↓
Check Department Scope
 ↓
Check Item / Category
 ↓
Policy
 ↓
ALLOW / DENY
```

---

# 26. Item Eligibility

Sebelum item digunakan dalam PR:

```text
Item
 ↓
Exists?
 ↓
Active?
 ↓
Department allowed?
 ↓
Permission?
 ↓
ALLOW
```

Jika salah satu gagal:

```text
DENY
```

---

# 27. Backend Must Revalidate Item

Frontend boleh hanya menampilkan item yang allowed.

Tetapi backend harus memvalidasi ulang.

Buruk:

```text
Vue
 ↓
Only show IT items
 ↓
Trust submitted item_id
```

Karena user dapat memanipulasi request.

Yang benar:

```text
Request
 ↓
Backend
 ↓
Validate Item
 ↓
Validate Department Scope
 ↓
Create PR
```

---

# 28. Item Search

Item list harus mendukung search.

Contoh:

```text
Search:
Laptop
```

mencari berdasarkan:

```text
Code
Name
```

Optional:

```text
Category
Supplier
```

---

# 29. Item Filter

Filter dapat berdasarkan:

```text
Category
Status
Supplier
Unit
```

Untuk department user:

```text
Department Scope
```

harus diterapkan di backend.

---

# 30. Pagination

Item list menggunakan pagination.

Contoh:

```php
Item::query()
    ->paginate(20);
```

Jangan menggunakan:

```php
Item::all();
```

untuk dataset besar.

---

# 31. Query Optimization

Item merupakan data yang sering digunakan transaction.

Query harus memperhatikan:

```text
Index
Eager Loading
Pagination
Search Pattern
```

Contoh:

```php
Item::query()
    ->with(['category', 'unit'])
    ->active()
    ->paginate(20);
```

---

# 32. N+1 Query

Buruk:

```text
Get 100 Items
 ↓
For each Item
 ↓
Get Category
```

dapat menghasilkan banyak query.

Lebih baik:

```text
Item
 ↓
with(category)
 ↓
Load Relationship Efficiently
```

---

# 33. Index Recommendation

Index yang perlu dipertimbangkan:

```text
items.code
items.name
items.category_id
items.status
items.supplier_id
```

Tidak berarti semuanya harus langsung di-index.

Gunakan query pattern dan execution plan untuk memastikan index memang membantu.

---

# 34. Database Constraints

Minimal:

```text
items.id
→ PRIMARY KEY

items.code
→ UNIQUE

items.category_id
→ FOREIGN KEY

items.unit_id
→ FOREIGN KEY
```

Jika supplier mandatory:

```text
items.supplier_id
→ FOREIGN KEY
```

sesuai business rule.

---

# 35. Item Service

Service menangani business operation.

Contoh:

```text
ItemService
├── create()
├── update()
├── deactivate()
└── restore() [optional]
```

Contoh:

```php
$itemService->create(
    $validatedData,
    $user
);
```

---

# 36. Transaction Boundary

Untuk operation yang mengubah beberapa data:

```text
Item
+
Conversion
+
Audit
```

gunakan database transaction jika perubahan harus atomic.

Concept:

```text
BEGIN
 ↓
Create Item
 ↓
Create Conversion
 ↓
Create Audit
 ↓
COMMIT
```

Jika gagal:

```text
ROLLBACK
```

---

# 37. Item API / Inertia Response

Data yang dikirim ke frontend harus hanya data yang diperlukan.

Contoh:

```text
id
code
name
category
unit
status
```

Jangan mengirim data internal yang tidak diperlukan.

---

# 38. Item Resource / DTO

Jika response mulai kompleks, gunakan Resource/DTO untuk mengontrol output.

Concept:

```text
Model
 ↓
Resource / DTO
 ↓
Frontend
```

Tujuannya:

```text
Stable Response
+
Controlled Data Exposure
```

---

# 39. Validation

Create Item:

```text
Code
→ required
→ unique

Name
→ required

Category
→ exists

Unit
→ exists

Status
→ valid enum/value
```

Update Item:

```text
Code
→ unique except current item
```

---

# 40. Concurrency

Dua user dapat mencoba membuat item dengan code sama.

Contoh:

```text
User A → ITM-001
User B → ITM-001
```

Application validation saja tidak cukup.

Database:

```text
UNIQUE(items.code)
```

menjadi final protection.

---

# 41. Audit

Perubahan penting:

```text
Item Created
Item Updated
Item Deactivated
Category Changed
Unit Changed
Conversion Changed
Department Item Scope Changed
```

dapat dicatat di Audit Log.

---

# 42. Security

Item Management harus menerapkan:

```text
Authentication
 ↓
Permission
 ↓
Policy
 ↓
Scope
 ↓
Validation
```

User tidak boleh:

```text
Modify unauthorized item
Bypass inactive status
Use unauthorized item in PR
Modify another department's configuration
```

---

# 43. Common Mistakes

### Mistake 1

Menghitung stock di Item model.

```text
Item
→ stock = ...
```

Stock sebaiknya berasal dari inventory/ledger.

---

### Mistake 2

Hard delete item.

```text
DELETE Item
```

padahal item memiliki historical transaction.

---

### Mistake 3

Mengandalkan frontend untuk department restriction.

```text
Vue hides unauthorized item
```

Backend harus memvalidasi.

---

### Mistake 4

Mengubah conversion lalu mengubah historical transaction.

Historical transaction harus tetap konsisten.

---

### Mistake 5

Mengambil semua item.

```php
Item::all();
```

Gunakan:

```text
Pagination
Search
Filter
```

---

# 44. Maintenance Guide

### "Saya mau mengubah tampilan tabel Item."

Cari:

```text
resources/js/Pages/Items/Index.vue
```

---

### "Saya mau menambah field Item."

Periksa urutan:

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
Vue List/Detail
 ↓
Resource/DTO
```

---

### "Saya mau mengubah validation."

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

### "Saya mau membatasi IT hanya boleh PR item tertentu."

Cari:

```text
Department Item Scope
+
PurchaseRequestPolicy
```

---

### "Saya mau mengubah BOX menjadi PCS."

Cari:

```text
Unit Conversion
+
Transaction Service
```

---

### "Saya mau mengubah tampilan status Active/Inactive."

Cari:

```text
Items/Index.vue
Items/Show.vue
Items/Edit.vue
```

Logic status tetap di backend.

---

### "Item sudah tidak digunakan dan ingin dihapus."

Periksa:

```text
Existing Transaction
 ↓
If referenced
 ↓
Deactivate
```

---

# 45. Code Reading Flow

Untuk memahami Item Management tanpa vibe coding:

```text
1. Index.vue
      ↓
2. Route
      ↓
3. Controller
      ↓
4. Request
      ↓
5. Policy
      ↓
6. Service
      ↓
7. Model
      ↓
8. Relationship
      ↓
9. Migration
      ↓
10. Database
```

Untuk memahami conversion:

```text
Item
 ↓
Unit
 ↓
Conversion
 ↓
Transaction Service
 ↓
Base Quantity
```

Untuk memahami department restriction:

```text
User
 ↓
Department
 ↓
Permission
 ↓
Item Scope
 ↓
Policy
 ↓
Purchase Request
```

---

# 46. Debugging Checklist

Jika Item tidak muncul:

```text
[ ] Item exists?
[ ] Item active?
[ ] Query filter?
[ ] Search parameter?
[ ] Pagination?
[ ] Department scope?
[ ] Warehouse scope?
[ ] Permission?
```

Jika Item tidak bisa disimpan:

```text
[ ] Validation?
[ ] Duplicate code?
[ ] Foreign key?
[ ] Policy?
[ ] Service error?
[ ] Database constraint?
```

Jika item bisa dipilih tetapi PR gagal:

```text
[ ] Item active?
[ ] Department allowed?
[ ] Permission?
[ ] Policy?
[ ] Business rule?
```

---

# 47. Testing

Minimal:

```text
[ ] Create item
[ ] Update item
[ ] Deactivate item
[ ] Duplicate code rejected
[ ] Invalid category rejected
[ ] Invalid unit rejected
[ ] Inactive item cannot be used for new transaction
[ ] Historical item remains accessible
[ ] Department item scope works
[ ] Unauthorized item modification rejected
[ ] Search works
[ ] Pagination works
[ ] Unit conversion works
[ ] Historical conversion remains correct
```

---

# 48. Definition of Done

```text
[ ] Item CRUD implemented
[ ] Validation implemented
[ ] Authorization implemented
[ ] Department scope implemented
[ ] Category implemented
[ ] Unit implemented
[ ] Conversion implemented if required
[ ] Active/Inactive implemented
[ ] Database constraints implemented
[ ] Index reviewed
[ ] Query optimized
[ ] Audit implemented
[ ] Tests implemented
[ ] Documentation updated
```

---

# 49. Final Item Flow

```text
                    ITEM MANAGEMENT
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
                         ┌──────────┴──────────┐
                         ▼                     ▼
                    PERMISSION              POLICY
                                               │
                                               ▼
                                             SCOPE
                         └──────────┬──────────┘
                                    ▼
                                  SERVICE
                                    │
                         ┌──────────┴──────────┐
                         ▼                     ▼
                       MODEL               BUSINESS RULE
                         │
                         ▼
                      DATABASE
                         │
                         ▼
                       AUDIT
                         │
                         ▼
                      RESPONSE
                         │
                         ▼
                         VUE
```

---

# 50. Key Principle

Item Management harus memisahkan:

```text
ITEM
=
What is the item?

UNIT
=
How is it measured?

CONVERSION
=
How does one unit relate to another?

CATEGORY
=
What type of item is it?

SCOPE
=
Who can use/request it?

STATUS
=
Can it be used for new transactions?

INVENTORY
=
How much stock exists?

TRANSACTION
=
What happened to the item?
```

Jangan mencampurkan semua logic tersebut ke dalam `Item` model atau `Item.vue`.

Dengan pemisahan ini, ketika maintenance dilakukan tanpa vibe coding, kamu bisa mengikuti **request flow → authorization → service → model → database** dan tahu bagian mana yang harus diubah tanpa merusak module lain.

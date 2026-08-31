# Inventra

## RBAC Code Guide

**Document:** RBAC Code Guide
**Version:** V1.0
**Status:** Draft

---

# 1. Purpose

RBAC (**Role-Based Access Control**) mengatur apa yang boleh dilakukan user di Inventra.

RBAC Inventra tidak berhenti pada Role.

Arsitekturnya:

```text
User
 ↓
Role
 ↓
Permission
 ↓
Scope
 ↓
Policy
 ↓
Allow / Deny
```

Contoh:

```text
Budi
 ↓
Warehouse Staff
 ↓
stock.out.create
 ↓
Warehouse WH-001
 ↓
StockOutPolicy
 ↓
ALLOW
```

---

# 2. RBAC Responsibility

RBAC bertanggung jawab terhadap:

- Role.
- Permission.
- User-role assignment.
- Role-permission assignment.
- Department scope.
- Warehouse scope.
- Location scope.
- Resource access.
- Authorization rules.

RBAC tidak bertanggung jawab terhadap:

- Password.
- Login.
- Session authentication.
- Business calculation.
- Database transaction.

Authentication dibahas pada:

```text
docs/code-guide/01_AUTHENTICATION.md
```

---

# 3. Core Authorization Model

Inventra menggunakan model:

```text
WHO?
 ↓
User / Role

WHAT?
 ↓
Permission

WHERE?
 ↓
Scope

WHICH RESOURCE?
 ↓
Policy

RESULT
 ↓
ALLOW / DENY
```

Contoh:

```text
User:
Warehouse Staff

Permission:
stock.out.create

Scope:
WH-001

Resource:
Stock Out for WH-001

Result:
ALLOW
```

---

# 4. Role

Role adalah kumpulan permission.

Contoh:

```text
Admin
Warehouse Manager
Warehouse Staff
Department Staff
Department Manager
Asset Staff
Auditor
```

Role tidak seharusnya digunakan sebagai hardcoded authorization di seluruh aplikasi.

Hindari:

```php
if ($user->role === 'admin') {
    ...
}
```

Untuk authorization yang lebih granular, gunakan permission/policy.

---

# 5. Permission

Permission adalah kemampuan melakukan action tertentu.

Format:

```text
module.action
```

atau untuk action yang lebih spesifik:

```text
module.resource.action
```

Contoh:

```text
inventory.view
inventory.create
inventory.update

stock.in.create
stock.out.create
stock.transfer.create

stock.opname.create
stock.opname.approve

purchase.request.create
purchase.request.approve

asset.view
asset.create
asset.assign
asset.dispose
```

---

# 6. Permission Naming

Nama permission harus:

```text
Predictable
Consistent
Specific
```

Contoh:

```text
stock.out.create
stock.out.view
stock.out.approve
stock.out.cancel
```

Hindari:

```text
can_stock
manage_stock
stockStuff
```

karena tidak jelas action-nya.

---

# 7. Permission vs Role

Role:

```text
Warehouse Staff
```

Permission:

```text
stock.in.create
stock.out.create
inventory.view
```

Relationship:

```text
Role
 ├── Permission A
 ├── Permission B
 └── Permission C
```

User tidak perlu diberikan setiap permission satu per satu jika permission sudah melekat pada role.

---

# 8. User → Role

Conceptual:

```text
User
 ↓
Role
```

Contoh:

```text
Budi
 ↓
Warehouse Staff
```

Jika role berubah:

```text
Warehouse Staff
 ↓
Warehouse Manager
```

permission user ikut berubah berdasarkan role.

---

# 9. Multiple Roles

Jika business requirement membutuhkan, user dapat memiliki lebih dari satu role.

Contoh:

```text
User
├── Department Staff
└── Asset Staff
```

Effective permissions:

```text
Role A permissions
+
Role B permissions
```

Namun multiple role harus digunakan secara hati-hati agar authorization tidak sulit dipahami.

---

# 10. Effective Permission

Permission efektif user merupakan hasil role assignment.

Concept:

```text
User
 ↓
Assigned Roles
 ↓
Role Permissions
 ↓
Effective Permissions
```

Contoh:

```text
Warehouse Staff
├── inventory.view
├── stock.in.create
└── stock.out.create
```

Maka user dapat menjalankan ketiga action tersebut.

---

# 11. Permission Check

Contoh conceptual:

```php
$user->can('stock.out.create');
```

Result:

```text
true
```

atau:

```text
false
```

Tetapi permission check saja belum cukup untuk data yang memiliki scope.

---

# 12. Permission + Scope

Contoh:

```text
Permission:
stock.out.create

Scope:
WH-001
```

User memiliki permission:

```text
YES
```

Tetapi user mencoba:

```text
WH-002
```

Result:

```text
DENY
```

Karena:

```text
Permission ≠ Resource Access
```

---

# 13. Scope Architecture

Scope digunakan untuk membatasi **di mana** permission berlaku.

Inventra menggunakan scope sesuai kebutuhan:

```text
Company
 ↓
Department
 ↓
Warehouse
 ↓
Location
 ↓
Resource
```

Tidak semua feature harus menggunakan seluruh hierarchy.

---

# 14. Department Scope

Department scope digunakan ketika akses berkaitan dengan department.

Contoh:

```text
IT Department
QC Department
Finance Department
HR Department
```

User:

```text
IT Staff
```

memiliki department scope:

```text
IT
```

---

# 15. Warehouse Scope

Warehouse scope membatasi warehouse yang dapat diakses.

Contoh:

```text
User
 ↓
Warehouse Staff
 ↓
WH-001
```

User tidak dapat mengakses:

```text
WH-002
```

jika tidak memiliki scope.

---

# 16. Location Scope

Location scope digunakan jika inventory perlu dibatasi sampai lokasi tertentu.

Contoh:

```text
WH-001
 ↓
Rack A
 ↓
Shelf A-01
```

User dapat memiliki:

```text
Warehouse Scope = WH-001
Location Scope = Rack A
```

---

# 17. Department Staff — Item Restriction

Salah satu business rule penting Inventra:

> Department Staff tertentu tidak otomatis dapat melakukan PR untuk semua barang.

Contoh:

```text
IT Staff
 ↓
Allowed Item Category
 ↓
IT Equipment
```

QC:

```text
QC Staff
 ↓
Allowed Item Category
 ↓
QC Equipment
```

---

# 18. Purchase Request Authorization

Flow:

```text
Department Staff
 ↓
purchase.request.create
 ↓
Department Scope
 ↓
Item Scope
 ↓
Policy
 ↓
ALLOW / DENY
```

Contoh:

```text
IT Staff
+
IT Laptop
=
ALLOW
```

Tetapi:

```text
IT Staff
+
QC Equipment
=
DENY
```

---

# 19. Department-to-Item Mapping

Access restriction dapat menggunakan mapping:

```text
Department
    │
    ├── Allowed Item
    ├── Allowed Item Category
    └── Allowed Item Type
```

Contoh:

```text
IT
├── Laptop
├── Monitor
└── Network Equipment

QC
├── Testing Equipment
└── QC Consumables
```

Mapping harus disimpan sebagai data/configuration yang dapat dikelola, bukan hardcoded di Vue.

---

# 20. Scope Should Be Data-Driven

Hindari:

```php
if ($department === 'IT') {
    // allow laptop
}
```

karena ketika department bertambah, code harus terus diubah.

Lebih baik:

```text
Department
        ↓
Department Item Scope
        ↓
Allowed Item
```

Dengan demikian administrator dapat mengatur mapping tanpa mengubah source code jika business requirement memungkinkan.

---

# 21. Policy

Policy digunakan untuk authorization terhadap resource tertentu.

Contoh:

```text
ItemPolicy
WarehousePolicy
StockInPolicy
StockOutPolicy
PurchaseRequestPolicy
AssetPolicy
StockOpnamePolicy
```

Policy menjawab:

> Apakah user ini boleh melakukan action terhadap resource ini?

---

# 22. Authorization Flow

```text
Request
 ↓
Authentication
 ↓
Permission
 ↓
Scope
 ↓
Policy
 ↓
Business Validation
 ↓
Service
```

Jika gagal:

```text
DENY
 ↓
403 Forbidden
```

---

# 23. Policy Example

Conceptual:

```php
public function create(User $user, Warehouse $warehouse): bool
{
    return
        $user->can('stock.out.create')
        && $user->hasWarehouseAccess($warehouse);
}
```

Artinya:

```text
Has Permission?
        +
Has Warehouse Scope?
        =
ALLOW
```

---

# 24. Resource Authorization

Authorization harus memeriksa resource yang sebenarnya.

Contoh:

```text
GET /stock-out/100
```

User memiliki permission:

```text
stock.out.view
```

Tetapi transaction `100` berada di:

```text
WH-002
```

sedangkan user hanya punya:

```text
WH-001
```

Result:

```text
DENY
```

---

# 25. IDOR Protection

Jangan hanya memeriksa:

```php
$user->can('stock.out.view');
```

Kemudian langsung:

```php
StockOut::find($id);
```

Karena user mungkin dapat mengganti ID.

Harus ada resource authorization:

```text
Permission
+
Resource Scope
```

---

# 26. Query-Level Scoping

Untuk data collection:

```text
GET /stock-out
```

query harus dibatasi sesuai scope.

Concept:

```php
StockOut::query()
    ->forUser($user)
    ->latest()
    ->paginate();
```

Sehingga user hanya menerima data yang memang boleh dilihat.

---

# 27. Do Not Filter Only in Vue

Buruk:

```text
Backend
 ↓
Return ALL warehouses
 ↓
Vue hides unauthorized warehouses
```

Ini bukan security.

User tetap dapat melihat data melalui:

```text
Browser DevTools
Network
API Request
```

Yang benar:

```text
Database Query
 ↓
Scope Filter
 ↓
Authorized Data
 ↓
Vue
```

---

# 28. UI Permission Check

Frontend boleh menggunakan permission untuk UX.

Contoh:

```text
User has stock.out.create
        ↓
Show "Create Stock Out"
```

Jika tidak:

```text
Hide button
```

Tetapi backend tetap melakukan check.

---

# 29. Frontend vs Backend Authorization

```text
Vue
 ↓
UX
```

```text
Laravel
 ↓
Security Boundary
```

Jadi:

```text
Button hidden
≠
Permission granted
```

dan:

```text
Button visible
≠
Request automatically allowed
```

---

# 30. Permission Matrix

Permission harus terdokumentasi.

Contoh:

| Role               | Inventory View | Stock In | Stock Out | PR Create | PR Approve |
| ------------------ | -------------: | -------: | --------: | --------: | ---------: |
| Admin              |              ✓ |        ✓ |         ✓ |         ✓ |          ✓ |
| Warehouse Manager  |              ✓ |        ✓ |         ✓ |         - |          - |
| Warehouse Staff    |              ✓ |        ✓ |         ✓ |         - |          - |
| Department Staff   |              ✓ |        - |         - |       ✓\* |          - |
| Department Manager |              ✓ |        - |         - |         ✓ |        ✓\* |

`*` tetap tunduk pada department/item scope dan approval rule.

---

# 31. Permission Hierarchy

Jangan menganggap permission otomatis hierarchical.

Contoh:

```text
stock.out.view
```

tidak otomatis berarti:

```text
stock.out.create
stock.out.approve
stock.out.delete
```

Setiap permission harus diberikan secara eksplisit.

---

# 32. Sensitive Permissions

Permission berikut harus memiliki kontrol ketat:

```text
user.manage
role.manage
permission.manage

stock.adjust
stock.opname.approve

purchase.request.approve

asset.dispose

audit.view
audit.delete
```

Khusus audit:

```text
Audit Log
```

sebaiknya immutable bagi user biasa.

---

# 33. Role Management

Jika Inventra memiliki UI untuk mengelola role:

```text
Admin
 ↓
Role Management
 ↓
Select Role
 ↓
Assign Permissions
 ↓
Save
 ↓
Audit Log
```

Perubahan role/permission harus dicatat.

---

# 34. Permission Management Security

Tidak semua administrator harus dapat mengubah permission.

Contoh:

```text
System Administrator
 ↓
permission.manage
```

sedangkan:

```text
Warehouse Manager
 ↓
NO permission.manage
```

---

# 35. Role Assignment Security

Perubahan role user merupakan sensitive action.

Flow:

```text
Admin
 ↓
Authorize
 ↓
Change User Role
 ↓
Validate
 ↓
Save
 ↓
Audit Log
```

Audit:

```text
Who
Changed Which User
Old Role
New Role
Timestamp
```

---

# 36. Scope Assignment Security

Scope juga merupakan sensitive authorization data.

Contoh:

```text
User A
 ↓
WH-001
```

diubah menjadi:

```text
WH-001 + WH-002
```

Perubahan tersebut dapat memperluas akses user.

Karena itu perubahan scope harus:

```text
Authorized
Validated
Audited
```

---

# 37. Cache Consideration

Jika permission atau role menggunakan cache:

```text
Role / Permission
 ↓
Cache
 ↓
Authorization
```

maka perubahan permission harus menangani cache invalidation.

Jangan sampai:

```text
Permission revoked
 ↓
Old permission remains cached
 ↓
User still authorized
```

---

# 38. Authorization Order

Untuk critical operation, gunakan urutan:

```text
1. Authentication
2. Permission
3. Resource Scope
4. Policy
5. Request Validation
6. Business Validation
7. Service
8. Database Transaction
9. Audit
```

Urutan implementation dapat sedikit berbeda sesuai framework, tetapi prinsip security tetap sama.

---

# 39. Common Mistakes

### Mistake 1

```php
if ($user->role === 'admin')
```

digunakan di seluruh application.

Solusi:

```text
Permission
+
Policy
```

---

### Mistake 2

Hanya menyembunyikan button di Vue.

Solusi:

```text
Backend Authorization
```

---

### Mistake 3

Permission tanpa scope.

Contoh:

```text
stock.out.create
```

tetapi tidak memeriksa warehouse.

Solusi:

```text
Permission
+
Warehouse Scope
```

---

### Mistake 4

Mengambil semua data lalu filtering di frontend.

Solusi:

```text
Filter at database/query level.
```

---

### Mistake 5

Mengambil `user_id` dari request.

Solusi:

```php
$request->user()->id
```

---

# 40. Testing RBAC

Minimal test:

```text
[ ] User without permission is denied
[ ] User with permission is allowed
[ ] User with wrong department is denied
[ ] User with wrong warehouse is denied
[ ] User with wrong location is denied
[ ] User cannot access another user's resource
[ ] User cannot bypass UI authorization
[ ] Role changes update effective permission
[ ] Scope changes update accessible resources
```

---

# 41. Example — Stock Out

User:

```text
Warehouse Staff
```

Permission:

```text
stock.out.create
```

Scope:

```text
WH-001
```

Request:

```text
Stock Out
Warehouse = WH-001
```

Flow:

```text
Authenticated
 ↓
stock.out.create
 ↓
WH-001 allowed
 ↓
StockOutPolicy
 ↓
ALLOW
```

Jika request:

```text
Warehouse = WH-002
```

maka:

```text
Permission = YES
Scope = NO
 ↓
DENY
```

---

# 42. Example — Purchase Request

User:

```text
IT Staff
```

Permission:

```text
purchase.request.create
```

Scope:

```text
Department = IT
```

Request:

```text
Laptop
```

Jika Laptop diizinkan untuk IT:

```text
ALLOW
```

Jika:

```text
QC Equipment
```

dan item tersebut tidak termasuk scope IT:

```text
DENY
```

---

# 43. Code Reading Flow

Saat kamu membuka RBAC code, jangan membaca semua file sekaligus.

Ikuti:

```text
1. User
 ↓
2. Role
 ↓
3. Permission
 ↓
4. Scope
 ↓
5. Policy
 ↓
6. Route/Controller
 ↓
7. Query
```

Pertanyaan yang harus bisa kamu jawab:

```text
User ini siapa?
Role-nya apa?
Permission-nya apa?
Scope-nya apa?
Resource yang diminta apa?
Policy memeriksa apa?
Data yang dikembalikan sudah scoped?
```

---

# 44. Maintenance Guide

### "Saya mau menambah permission."

Cari:

```text
Permission definition / seeder
```

dan update:

```text
Role-Permission mapping
```

---

### "Saya mau membuat role baru."

Cari:

```text
Role model / Role seeder
Role management
```

kemudian tentukan permission.

---

### "Saya mau mengubah siapa yang boleh Stock Out."

Cari:

```text
StockOutPolicy
+
stock.out.create permission
+
warehouse scope
```

---

### "Saya mau mengubah department mana yang boleh meminta item."

Cari:

```text
Department Item Scope
+
PurchaseRequestPolicy
```

bukan `Login.vue`.

---

### "Saya mau mengubah akses warehouse."

Cari:

```text
Warehouse Scope
+
WarehousePolicy
```

---

### "Button Create Stock Out tidak muncul."

Periksa:

```text
1. User role
2. Permission
3. Frontend permission check
4. Props/shared auth data
```

---

### "Button muncul tetapi request mendapat 403."

Periksa:

```text
1. Permission
2. Policy
3. Warehouse scope
4. Department scope
5. Resource ownership/scope
```

---

# 45. Security Principle

RBAC Inventra menggunakan:

```text
Least Privilege
+
Deny by Default
+
Server-Side Authorization
+
Resource-Level Authorization
+
Scope-Based Access
+
Auditability
```

Jangan pernah menganggap:

```text
User is logged in
=
User can access everything
```

---

# 46. Final Authorization Model

```text
                         USER
                           │
                           ▼
                    AUTHENTICATION
                           │
                           ▼
                          ROLE
                           │
                           ▼
                      PERMISSION
                           │
                           ▼
                         SCOPE
                           │
             ┌─────────────┼─────────────┐
             ▼             ▼             ▼
         Department     Warehouse      Location
             │             │             │
             └─────────────┼─────────────┘
                           ▼
                         POLICY
                           │
                           ▼
                  RESOURCE AUTHORIZATION
                           │
                    ┌──────┴──────┐
                    ▼             ▼
                  ALLOW          DENY
                    │             │
                    ▼             ▼
                 SERVICE         403
                    │
                    ▼
               DATABASE
```

---

# 47. Key Principle

RBAC Inventra bukan:

```text
User → Role → Access
```

tetapi:

```text
User
 ↓
Role
 ↓
Permission
 ↓
Scope
 ↓
Policy
 ↓
Resource
 ↓
Allow / Deny
```

Dengan model ini, Inventra dapat menangani kebutuhan seperti:

```text
IT Staff
→ boleh membuat PR
→ hanya untuk item yang diizinkan IT

QC Staff
→ boleh membuat PR
→ hanya untuk item yang diizinkan QC

Warehouse Staff
→ boleh Stock Out
→ hanya pada warehouse yang menjadi scope-nya
```

Tanpa harus membuat logic authorization hardcoded di setiap halaman.

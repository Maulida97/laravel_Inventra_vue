# Inventra

## Sprint 02 — Role-Based Access Control (RBAC)

**Sprint:** SPRINT-02
**Name:** RBAC
**Project:** Inventra
**Status:** Planned
**Branch:** `feature/rbac`

---

# 1. Sprint Overview

Sprint ini membangun sistem **Role-Based Access Control (RBAC)** untuk menentukan akses user terhadap fitur Inventra.

Authentication menjawab:

```text
WHO ARE YOU?
```

RBAC menjawab:

```text
WHAT CAN YOU DO?
```

Flow:

```text
User
 ↓
Role
 ↓
Permission
 ↓
Policy / Authorization
 ↓
Allowed / Denied
```

---

# 2. Objective

Membangun authorization system yang:

- Mendukung Role.
- Mendukung Permission.
- Menghubungkan User dengan Role.
- Membatasi akses menu.
- Membatasi akses route.
- Membatasi business action.
- Mendukung authorization di backend.
- Mendukung permission-aware UI.
- Dapat dikembangkan tanpa mengubah banyak kode.

---

# 3. Scope

### Included

```text
Role
Permission
User-Role
Role-Permission
Authorization Middleware
Policy
Permission Check
Menu Visibility
Backend Authorization
Frontend Permission Awareness
```

### Not Included

```text
Multi-tenant authorization
Attribute Based Access Control kompleks
External IAM
SSO
Advanced organization hierarchy
```

Hal tersebut dapat ditambahkan pada fase berikutnya jika diperlukan.

---

# 4. Concept

Contoh:

```text
User: Budi
Role: Warehouse Staff

Permissions:
- item.view
- stock-in.create
- stock-out.create
- stock-opname.view
```

Budi dapat:

```text
✓ Melihat item
✓ Membuat Stock In
✓ Membuat Stock Out
```

Tetapi:

```text
✕ Menghapus user
✕ Mengubah role
✕ Melihat audit log
```

---

# 5. RBAC Architecture

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
             AUTHORIZATION
              ┌──────┴──────┐
              ▼             ▼
             ALLOW         DENY
```

---

# 6. Data Relationship

Concept:

```text
users
  │
  │ many-to-many
  ▼
roles
  │
  │ many-to-many
  ▼
permissions
```

Relationship:

```text
User
 └── Roles
      └── Permissions
```

---

# 7. Permission Naming Convention

Gunakan format:

```text
module.action
```

Contoh:

```text
item.view
item.create
item.update
item.delete

warehouse.view
warehouse.create
warehouse.update
warehouse.delete

stock-in.view
stock-in.create

stock-out.view
stock-out.create
stock-out.cancel

stock-opname.view
stock-opname.create
stock-opname.approve
```

Gunakan vocabulary yang konsisten.

---

# 8. Permission Principle

Permission harus mewakili **business capability**, bukan tombol UI.

Jangan membuat:

```text
button-stock-out-create
```

Gunakan:

```text
stock-out.create
```

Karena permission harus tetap valid walaupun UI berubah.

---

# 9. Role

Contoh role Inventra:

```text
SUPER_ADMIN
ADMIN
WAREHOUSE_MANAGER
WAREHOUSE_STAFF
VIEWER
```

Role final mengikuti kebutuhan bisnis Inventra.

Jangan membuat role hanya berdasarkan jabatan jika sebenarnya kebutuhan aksesnya sama.

---

# 10. Role vs Permission

Role:

```text
WAREHOUSE_STAFF
```

Permission:

```text
stock-in.view
stock-in.create
stock-out.view
stock-out.create
```

Jadi:

```text
Role
 ↓
Collection of Permissions
```

User tidak seharusnya membutuhkan permission satu per satu secara manual jika role sudah digunakan sebagai mekanisme utama.

---

# 11. Super Admin

Super Admin dapat memiliki akses paling tinggi.

Namun jangan menyebarkan pengecekan:

```php
if ($user->is_super_admin)
```

ke seluruh aplikasi.

Lebih baik authorization memiliki satu mekanisme terpusat.

Concept:

```text
User
 ↓
Authorization Layer
 ↓
Permission
```

Jika Super Admin memiliki bypass, implementasikan pada authorization layer.

---

# 12. Backend Authorization

Backend adalah **security boundary**.

Contoh:

```text
POST /stock-out
        │
        ▼
Authentication
        │
        ▼
Authorization
        │
   ┌────┴────┐
   ▼         ▼
 ALLOW      DENY
   │
   ▼
Business Service
```

Frontend tidak boleh menjadi satu-satunya tempat permission diperiksa.

---

# 13. Middleware

Permission dapat dilindungi melalui middleware.

Concept:

```text
Route
 ↓
auth
 ↓
permission:stock-out.create
 ↓
Controller
```

Jika tidak memiliki permission:

```text
403 Forbidden
```

---

# 14. Policy

Gunakan Policy untuk authorization yang bergantung pada resource.

Contoh:

```text
User boleh melihat Stock Out
+
Stock Out berada di warehouse yang menjadi scope user
```

Maka:

```text
Permission
+
Resource Policy
+
Scope
```

dapat digunakan.

---

# 15. Permission vs Policy

### Permission

Menjawab:

```text
"Apakah role ini boleh melakukan aksi tersebut?"
```

Contoh:

```text
stock-out.create
```

### Policy

Menjawab:

```text
"Apakah user ini boleh melakukan aksi tersebut terhadap resource ini?"
```

Contoh:

```text
Boleh edit Stock Out SO-001?
```

---

# 16. Authorization Flow

```text
Request
 ↓
Authentication
 ↓
User
 ↓
Role
 ↓
Permission
 ↓
Policy
 ↓
Resource Scope
 ↓
Allow / Deny
```

Tidak semua endpoint membutuhkan seluruh layer.

Gunakan layer sesuai kebutuhan.

---

# 17. Permission Tables

Conceptual:

```text
roles
├── id
├── name
├── description
└── timestamps

permissions
├── id
├── name
├── description
└── timestamps

role_user
├── role_id
└── user_id

permission_role
├── permission_id
└── role_id
```

Nama pivot table dapat mengikuti convention/implementasi package yang digunakan.

---

# 18. Permission Seeding

Permission awal sebaiknya dibuat melalui seeder.

Concept:

```text
database/seeders/
└── PermissionSeeder.php
```

Contoh:

```text
item.view
item.create
item.update
item.delete

stock-in.view
stock-in.create

stock-out.view
stock-out.create
stock-out.cancel
```

Jangan memasukkan permission manual melalui database production.

---

# 19. Role Seeding

Role awal juga dibuat melalui seeder.

Concept:

```text
RoleSeeder.php
```

Contoh:

```text
SUPER_ADMIN
ADMIN
WAREHOUSE_MANAGER
WAREHOUSE_STAFF
VIEWER
```

Kemudian assign permission:

```text
WAREHOUSE_STAFF
 ↓
stock-in.view
stock-in.create
stock-out.view
stock-out.create
```

---

# 20. Permission Management

Untuk V1, permission dapat dikelola oleh system/database melalui seeder.

UI CRUD permission tidak wajib.

Fokus utama:

```text
Role Management
+
Role Permission Assignment
```

Jika dibutuhkan kemudian, permission management dapat menjadi enhancement.

---

# 21. Role Management

Admin yang berwenang dapat:

```text
View Roles
Create Role
Update Role
Assign Permissions
```

Delete role harus diperhatikan karena role mungkin sedang digunakan oleh banyak user.

---

# 22. User Role Assignment

Flow:

```text
Admin
 ↓
User Management
 ↓
Select User
 ↓
Assign Role
 ↓
Save
```

Backend:

```text
Authorization
 ↓
Validate Role
 ↓
Assign Role
 ↓
Audit Log
```

---

# 23. Authorization Audit

Perubahan security-sensitive harus diaudit.

Contoh:

```text
ROLE_CREATED
ROLE_UPDATED
ROLE_ASSIGNED
ROLE_REMOVED
PERMISSION_CHANGED
```

Contoh:

```text
Budi
ASSIGN_ROLE
User: Andi
Role: WAREHOUSE_MANAGER
```

---

# 24. Frontend Permission Awareness

Frontend boleh menggunakan permission untuk UX.

Contoh:

```text
User has:
stock-out.create
```

Maka:

```text
[ + Create Stock Out ]
```

ditampilkan.

Jika tidak:

```text
Create button
→ hidden
```

Tetapi ini **bukan security**.

Backend tetap melakukan authorization.

---

# 25. Menu Visibility

Sidebar dapat menggunakan permission.

Contoh:

```text
Dashboard
Items
Warehouse
Stock In
Stock Out
Stock Opname
Reports
Audit Log
```

Jika user tidak memiliki:

```text
audit.view
```

menu Audit Log tidak ditampilkan.

Namun route tetap harus protected.

---

# 26. Permission Shared Data

Inertia dapat menerima authorization information.

Concept:

```text
Backend
 ↓
User Permissions
 ↓
Inertia Shared Props
 ↓
Vue
```

Contoh:

```text
auth.user
auth.permissions
```

Jangan mengirim data authorization yang tidak diperlukan.

---

# 27. Avoid Hardcoded Permission Logic

Hindari:

```javascript
if (user.role === "admin") {
  showButton();
}
```

Lebih baik:

```javascript
can("stock-out.create");
```

Karena role dapat berubah.

Contoh:

```text
Admin
 ↓
stock-out.create

Warehouse Manager
 ↓
stock-out.create
```

UI tidak perlu mengetahui role mana yang memberikan permission tersebut.

---

# 28. Authorization Helper

Frontend dapat memiliki helper:

```text
resources/js/
└── Composables/
    └── useCan.js
```

Concept:

```text
can('item.create')
can('item.update')
can('stock-out.create')
```

Implementasi final mengikuti struktur frontend Inventra.

---

# 29. Backend Permission Helper

Backend juga membutuhkan abstraction yang konsisten.

Contoh concept:

```php
$user->can('stock-out.create');
```

atau mekanisme authorization Laravel yang setara.

Hindari membuat banyak helper custom yang memiliki perilaku berbeda-beda.

---

# 30. Scope

RBAC saja belum tentu cukup.

Contoh:

```text
Warehouse Manager
```

memiliki:

```text
stock-out.view
```

Tetapi mungkin hanya boleh melihat:

```text
Warehouse A
```

bukan:

```text
Warehouse B
```

Maka:

```text
RBAC
+
Scope
```

diperlukan.

---

# 31. Scope Flow

```text
User
 ↓
Role
 ↓
Permission
 ↓
Warehouse Scope
 ↓
Resource
```

Contoh:

```text
Budi
 ↓
WAREHOUSE_MANAGER
 ↓
stock-out.view
 ↓
Warehouse A
 ↓
SO-0001
```

---

# 32. Authorization Decision

Authorization dapat diringkas:

```text
Authenticated?
    │
   YES
    ↓
Has Permission?
    │
   YES
    ↓
Pass Policy?
    │
   YES
    ↓
Within Scope?
    │
   YES
    ↓
   ALLOW
```

Jika salah satu gagal:

```text
DENY
```

---

# 33. HTTP Responses

Gunakan response yang sesuai.

```text
Unauthenticated
→ 401 / redirect login

Authenticated but unauthorized
→ 403 Forbidden

Resource not found
→ 404
```

Jangan menggunakan `404` hanya untuk menyembunyikan semua authorization error kecuali memang ada security reason yang jelas.

---

# 34. Security Rules

### Rule 1

Jangan percaya frontend.

### Rule 2

Authorization selalu diperiksa backend.

### Rule 3

Permission tidak sama dengan role.

### Rule 4

Role tidak sama dengan resource ownership.

### Rule 5

Sensitive authorization changes harus diaudit.

### Rule 6

User tidak boleh menaikkan privilege dirinya sendiri.

---

# 35. Privilege Escalation Protection

Contoh attack:

```text
Warehouse Staff
 ↓
Request
 ↓
Assign role = SUPER_ADMIN
```

Backend harus menolak.

Jangan hanya menyembunyikan field role di frontend.

---

# 36. Mass Assignment Protection

Data role/permission harus divalidasi secara eksplisit.

Jangan menerima seluruh request:

```text
$request->all()
```

kemudian langsung digunakan untuk update user.

Gunakan field yang memang diperbolehkan.

---

# 37. Caching Consideration

Permission dapat di-cache jika diperlukan.

Tetapi jika menggunakan cache:

```text
Role Permission Changed
 ↓
Cache Invalidation
 ↓
New Permission
```

harus diperhatikan.

Jangan sampai permission lama tetap aktif karena cache stale.

Untuk V1, gunakan pendekatan sederhana terlebih dahulu dan optimasi berdasarkan kebutuhan.

---

# 38. Testing

Minimal:

```text
[ ] User with valid role can access allowed feature
[ ] User without permission receives 403
[ ] Guest cannot access protected route
[ ] Role can be assigned
[ ] Role can be removed
[ ] Permissions correctly assigned
[ ] Permission changes take effect
[ ] User cannot assign role without permission
[ ] User cannot escalate privilege
[ ] Policy blocks unauthorized resource
[ ] Warehouse scope is enforced
[ ] Menu visibility follows permission
[ ] Backend remains protected even if frontend is manipulated
[ ] RBAC changes are audited
```

---

# 39. Acceptance Criteria

Sprint selesai jika:

```text
1. Role tersedia.

2. Permission tersedia.

3. Role dapat memiliki banyak permission.

4. User dapat memiliki role.

5. Permission dapat diperiksa backend.

6. Unauthorized request menghasilkan 403.

7. Protected routes menggunakan authorization.

8. Policy dapat digunakan untuk resource-level authorization.

9. Frontend dapat mengetahui permission user.

10. Menu dapat disesuaikan berdasarkan permission.

11. User tidak dapat melakukan privilege escalation.

12. Perubahan role/permission diaudit.

13. Warehouse/resource scope dapat diterapkan.

14. Automated tests berhasil.
```

---

# 40. Expected Files

Conceptual structure:

```text
app/
├── Models/
│   ├── Role.php
│   └── Permission.php
│
├── Policies/
│   └── ...
│
├── Http/
│   └── Middleware/
│       └── ...
│
└── Services/
    └── Authorization/

database/
└── seeders/
    ├── RoleSeeder.php
    └── PermissionSeeder.php

resources/js/
├── Composables/
│   └── useCan.js
│
└── Pages/
    └── Roles/

tests/
└── Feature/
    └── Authorization/
```

Jika menggunakan package RBAC Laravel, struktur dapat berbeda.

---

# 41. Code Understanding Map

Untuk memahami authorization request:

```text
Browser
 ↓
Inertia Request
 ↓
Route
 ↓
auth middleware
 ↓
permission middleware / Policy
 ↓
Controller
 ↓
Service
 ↓
Database
```

Untuk memahami permission:

```text
User
 ↓
Role
 ↓
Permission
```

Untuk memahami resource access:

```text
User
 ↓
Permission
 ↓
Policy
 ↓
Scope
 ↓
Resource
```

---

# 42. Maintenance Guide

### "Saya ingin user tertentu bisa mengakses fitur."

Jangan langsung mengubah Vue.

Cari:

```text
Role
 ↓
Permission
```

Kemudian tentukan permission yang diperlukan.

---

### "Saya ingin menambah permission."

Cari:

```text
database/seeders/PermissionSeeder.php
```

Tambahkan permission dengan naming convention:

```text
module.action
```

Kemudian assign ke role yang sesuai.

---

### "Saya ingin mengubah siapa yang boleh melakukan action."

Cari:

```text
Permission
+
Policy
+
Scope
```

Jangan hanya mengubah button.

---

### "Button masih muncul walaupun tidak punya permission."

Periksa:

```text
useCan()
 ↓
auth.permissions
 ↓
shared Inertia data
 ↓
component
```

---

### "Button sudah hilang tetapi API/route masih bisa diakses."

Ini masalah security.

Periksa:

```text
Route
 ↓
Middleware
 ↓
Policy
 ↓
Controller
```

Backend authorization harus ditambahkan/diperbaiki.

---

### "Warehouse A bisa melihat data Warehouse B."

Periksa:

```text
Policy
 ↓
Scope
 ↓
Query
```

Jangan hanya memperbaiki frontend filtering.

---

### "User bisa mengubah role menjadi Super Admin."

Periksa:

```text
Role assignment endpoint
 ↓
Authorization
 ↓
Validation
 ↓
Allowed roles
```

---

# 43. Debugging Flow

Jika user mendapat:

```text
403 Forbidden
```

ikuti:

```text
Request
 ↓
Authenticated?
 ↓
Permission exists?
 ↓
Role assigned?
 ↓
Policy?
 ↓
Scope?
```

Jika menu tidak muncul:

```text
Inertia Shared Props
 ↓
auth.permissions
 ↓
useCan()
 ↓
Vue Component
```

Jika menu muncul tetapi request 403:

```text
Frontend Permission
        ≠
Backend Authorization
```

Periksa backend.

---

# 44. Git Branch

Branch:

```text
feature/rbac
```

Base:

```text
main
 ↓
feature/authentication
 ↓
feature/rbac
```

Branch RBAC bergantung pada Authentication.

---

# 45. Suggested Commits

```text
feat(rbac): add role and permission structure
feat(rbac): add permission seeders
feat(rbac): assign roles to users
feat(rbac): add authorization middleware
feat(rbac): add policies
feat(rbac): add permission-aware navigation
feat(rbac): add role management
test(rbac): add authorization tests
docs(rbac): document authorization flow
```

---

# 46. Definition of Done

```text
Code
    ✓ Role implemented
    ✓ Permission implemented
    ✓ User-role relationship implemented
    ✓ Role-permission relationship implemented

Authorization
    ✓ Middleware
    ✓ Policy
    ✓ Permission checking
    ✓ Scope handling

Frontend
    ✓ Permission-aware UI
    ✓ Permission-aware navigation

Security
    ✓ Backend authorization
    ✓ Privilege escalation protection
    ✓ Mass assignment protection

Audit
    ✓ RBAC changes audited

Testing
    ✓ Authorization tests pass

Documentation
    ✓ Code documented
    ✓ Maintenance flow documented

Git
    ✓ feature/rbac
```

---

# 47. Final RBAC Architecture

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
                     AUTHORIZATION
                           │
                 ┌─────────┴─────────┐
                 ▼                   ▼
              POLICY               SCOPE
                 │                   │
                 └─────────┬─────────┘
                           ▼
                       RESOURCE
                           │
                    ┌──────┴──────┐
                    ▼             ▼
                  ALLOW          DENY
```

Frontend:

```text
User
 ↓
Inertia Shared Props
 ↓
auth.permissions
 ↓
useCan()
 ↓
UI Visibility
```

Backend:

```text
Request
 ↓
Authentication
 ↓
Permission
 ↓
Policy
 ↓
Scope
 ↓
Business Logic
```

---

# 48. Key Principle

RBAC Inventra harus mengikuti:

```text
Authentication
→ Who are you?

Role
→ What group are you in?

Permission
→ What action can you perform?

Policy
→ Can you perform it on this resource?

Scope
→ Which data/resource can you access?
```

Jangan pernah menjadikan:

```text
Vue
```

sebagai security boundary.

**Security boundary tetap berada di backend.**

Alur utama yang harus kamu pahami saat maintenance:

```text
User
 ↓
Role
 ↓
Permission
 ↓
Middleware / Policy
 ↓
Controller
 ↓
Service
 ↓
Database
```

Jika suatu hari kamu ditanya:

> "Kenapa user ini tidak bisa membuat Stock Out?"

kamu tidak perlu vibe-code lagi. Kamu bisa tracing:

```text
User
 ↓
Role apa?
 ↓
Role punya stock-out.create?
 ↓
Middleware lolos?
 ↓
Policy lolos?
 ↓
Warehouse scope benar?
 ↓
Controller
 ↓
Service
```

Itulah tujuan utama sprint RBAC ini.

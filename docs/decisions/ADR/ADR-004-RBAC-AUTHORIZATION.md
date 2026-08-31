# ADR-004 — RBAC Authorization

**Project:** Inventra
**Status:** Accepted
**Date:** 2026-08-30

---

# 1. Context

Inventra digunakan oleh beberapa jenis user dengan tanggung jawab berbeda.

Contoh:

```text
Admin
Staff
Manager
```

Tidak semua user boleh melakukan seluruh aktivitas.

Contoh:

```text
Staff
├── View Inventory       ✓
├── Create Stock In      ✓
├── Approve Stock In     ✗
├── Manage User          ✗
└── Manage Permission    ✗
```

Selain role, Inventra juga memiliki konsep **Warehouse Scope**.

Seorang user dapat memiliki akses terhadap warehouse tertentu dan tidak boleh otomatis melihat atau mengubah data warehouse lainnya.

---

# 2. Problem

Authorization yang hanya berdasarkan role tidak cukup.

Contoh:

```text
User
 ↓
Role = Staff
 ↓
Access Inventory
```

Masih belum menjawab:

```text
Inventory Warehouse A
atau
Inventory Warehouse B?
```

Karena itu Inventra membutuhkan dua lapisan:

```text
Role / Permission
+
Data Scope
```

---

# 3. Decision

Inventra menggunakan **RBAC (Role-Based Access Control)** sebagai dasar authorization.

Struktur:

```text
User
 ↓
Role
 ↓
Permissions
```

Kemudian authorization terhadap data menggunakan scope:

```text
User
 ↓
Role / Permission
 ↓
Warehouse Scope
 ↓
Resource
```

---

# 4. Authorization Model

Konsep utama:

```text
User
  │
  ▼
Role
  │
  ▼
Permission
  │
  ▼
Policy / Scope
  │
  ▼
Resource
```

Contoh:

```text
User: Budi
Role: Staff

Permission:
inventory.view
inventory.create

Warehouse Scope:
Warehouse A
Warehouse B
```

Budi dapat mengakses inventory pada warehouse tersebut sesuai permission.

---

# 5. Role

Role digunakan untuk mengelompokkan tanggung jawab user.

Contoh awal:

```text
Admin
Staff
Manager
```

Role bukan berarti otomatis memiliki akses ke seluruh data.

Role menentukan permission yang dimiliki.

---

# 6. Permission

Permission dibuat granular.

Contoh:

```text
inventory.view
inventory.create
inventory.update

stock-in.view
stock-in.create
stock-in.approve

stock-out.view
stock-out.create
stock-out.approve

asset.view
asset.create
asset.update

report.view
report.export

user.manage
role.manage
```

Nama permission final mengikuti module Inventra.

---

# 7. Why Permission-Based

Hindari logic seperti:

```php
if ($user->role === 'admin') {
    // allow
}
```

di seluruh application.

Lebih baik:

```text
Permission
    ↓
Policy / Authorization
    ↓
Allow / Deny
```

Dengan demikian perubahan role tidak mengharuskan perubahan logic pada setiap controller.

---

# 8. Policy

Laravel Policy digunakan untuk authorization terhadap resource tertentu.

Contoh:

```text
InventoryPolicy
StockInPolicy
StockOutPolicy
AssetPolicy
WarehousePolicy
```

Policy menjawab:

```text
Can this user perform this action
on this resource?
```

---

# 9. Gate vs Policy

Gunakan pendekatan sesuai kebutuhan.

### Policy

Untuk resource:

```text
Item
Warehouse
Transaction
Asset
Inventory
```

### Gate / Permission Check

Untuk permission atau action yang tidak selalu terikat pada satu model.

Contoh:

```text
user.manage
report.export
system.settings
```

---

# 10. Warehouse Scope

Warehouse scope merupakan lapisan authorization data.

Contoh:

```text
User A
Role = Staff
Warehouse Scope = WH-A
```

Ketika membuka inventory:

```text
User A
 ↓
Permission: inventory.view
 ↓
Warehouse Scope: WH-A
 ↓
Inventory WH-A
```

Data:

```text
WH-B
```

tidak boleh ikut terkirim hanya karena user memiliki permission `inventory.view`.

---

# 11. Scope Enforcement

Warehouse scope harus diterapkan di backend.

Bukan hanya:

```text
Vue
 ↓
Hide Warehouse B
```

Frontend hiding bukan security mechanism.

Backend harus memastikan:

```text
Request
 ↓
Authentication
 ↓
Permission
 ↓
Warehouse Scope
 ↓
Query
```

---

# 12. Query Scoping

Query harus membatasi data sesuai authorization scope.

Konsep:

```text
User Scope
    ↓
Warehouse IDs
    ↓
Database Query
```

Contoh konseptual:

```text
Inventory
WHERE warehouse_id IN allowed_warehouse_ids
```

Bukan:

```text
SELECT * FROM inventory
```

kemudian menyaring data di frontend.

---

# 13. Direct Object Access

Inventra harus mencegah IDOR.

Contoh:

```text
User memiliki akses:
WH-A
```

Kemudian mencoba:

```text
/inventory/WH-B-item-001
```

Backend harus melakukan authorization.

Result:

```text
403 Forbidden
```

atau:

```text
404 Not Found
```

sesuai security design yang diterapkan.

---

# 14. API Authorization

REST API mengikuti authorization yang sama.

```text
API Request
    ↓
Authentication
    ↓
Permission
    ↓
Scope
    ↓
Policy
    ↓
Controller / Service
```

Tidak boleh membuat API bypass RBAC yang digunakan web application.

---

# 15. Frontend Authorization

Frontend boleh menggunakan permission untuk UX.

Contoh:

```text
if can('stock-out.approve')
    Show Approve Button
```

Namun ini hanya:

```text
UX Optimization
```

Bukan security boundary.

Backend tetap melakukan authorization.

---

# 16. Authentication vs Authorization

Authentication:

```text
"Siapa kamu?"
```

Authorization:

```text
"Apa yang boleh kamu lakukan?"
```

Inventra memisahkan kedua konsep tersebut.

```text
Login
 ↓
Authentication
 ↓
User Identity
 ↓
Authorization
 ↓
Permission + Scope
```

---

# 17. Admin

Admin memiliki permission administratif yang lebih luas.

Contoh:

```text
User Management
Role Management
Permission Management
Master Data
System Configuration
Audit Access
```

Namun prinsip least privilege tetap berlaku.

Jangan memberikan permission yang tidak diperlukan hanya karena user merupakan admin.

---

# 18. Staff

Staff umumnya menjalankan operational activity.

Contoh:

```text
View Item
View Inventory
Create Stock In
Create Stock Out
Stock Opname
Asset Operation
```

Approval permission diberikan hanya jika business requirement mengizinkan.

---

# 19. Manager

Manager dapat memiliki permission seperti:

```text
View Inventory
View Reports
Approve Stock In
Approve Stock Out
Approve Stock Opname
Review Transactions
```

Permission final mengikuti Permission Matrix Inventra.

---

# 20. Least Privilege

Inventra mengikuti prinsip:

```text
Give Minimum Access
Required For The Job
```

Contoh:

```text
Staff
```

tidak otomatis mendapatkan:

```text
user.manage
role.manage
permission.manage
```

---

# 21. Separation of Duties

Untuk workflow tertentu, pemisahan tugas dapat digunakan.

Contoh:

```text
Staff
 ↓
Create Stock Out
 ↓
Manager
 ↓
Approve
```

Dengan demikian orang yang membuat transaction tidak selalu menjadi orang yang menyetujui transaction tersebut jika business rule membutuhkan separation of duties.

---

# 22. Approval Authorization

Approval merupakan authorization khusus.

Contoh:

```text
stock-out.approve
```

harus diverifikasi pada backend.

User tanpa permission:

```text
POST /stock-outs/{id}/approve
```

mendapatkan:

```text
403 Forbidden
```

jika memang tidak memiliki hak tersebut.

---

# 23. Permission Changes

Perubahan permission merupakan aktivitas sensitif.

Contoh:

```text
Admin
 ↓
Change User Role
```

harus:

```text
Authorized
Audited
Traceable
```

Perubahan tersebut dicatat pada Audit Log sesuai design Inventra.

---

# 24. Role Changes

Role change dapat mengubah akses user secara signifikan.

Karena itu:

```text
Role Change
 ↓
Authorization Change
 ↓
Audit Log
```

harus dapat ditelusuri.

---

# 25. Cache Consideration

Jika permission atau role di-cache:

```text
Permission Cache
```

harus memiliki invalidation strategy.

Contoh:

```text
Role Changed
 ↓
Invalidate Permission Cache
```

Tujuannya mencegah user mempertahankan permission lama lebih lama dari yang seharusnya.

---

# 26. Database Integrity

Relasi authorization harus memiliki constraint yang sesuai.

Contoh:

```text
users
roles
permissions
role_permissions
user_roles
user_warehouses
```

Struktur final mengikuti:

```text
docs/05_DATABASE.md
```

---

# 27. Authorization Failure

Gunakan response yang konsisten.

Contoh:

```text
Not Authenticated
→ 401

Authenticated but Not Authorized
→ 403
```

Untuk resource yang tidak boleh diketahui keberadaannya, application dapat menggunakan pendekatan 404 sesuai security policy.

---

# 28. Audit

Event authorization sensitif dapat dicatat.

Contoh:

```text
Role Changed
Permission Changed
User Warehouse Scope Changed
Unauthorized Sensitive Action
```

Detail audit mengikuti:

```text
ADR-008 — Audit Log
```

---

# 29. Alternatives Considered

### Role-Only Authorization

Tidak dipilih karena tidak cukup untuk warehouse-level data isolation.

### Frontend Authorization

Tidak dipilih sebagai security mechanism karena client dapat dimanipulasi.

### Hardcoded Role Checks

Tidak dipilih karena sulit dipelihara dan menyebabkan authorization logic tersebar.

### Full Attribute-Based Access Control

Tidak digunakan sebagai primary model karena kebutuhan V1 Inventra dapat dipenuhi dengan:

```text
RBAC
+
Policy
+
Warehouse Scope
```

---

# 30. Consequences

### Positive

```text
+ Centralized authorization
+ Granular permission
+ Warehouse-level isolation
+ Easier role management
+ Better security
+ Easier auditing
+ Scalable authorization model
```

### Negative

```text
- More authorization logic
- Permission management becomes more complex
- Scope queries must be carefully implemented
- Cache invalidation may be required
- Requires consistent Policy usage
```

---

# 31. Implementation Principle

Authorization selalu mengikuti:

```text
Authentication
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
```

Tidak boleh:

```text
Frontend
 ↓
Hide Button
 ↓
Assume Secure
```

---

# 32. Maintenance Guide

Jika user dapat melihat warehouse yang seharusnya tidak dapat diakses:

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
Query
```

Periksa seluruh chain tersebut.

---

## Jika user dapat melakukan action tanpa permission

Periksa:

```text
Route
 ↓
Middleware
 ↓
Controller
 ↓
Policy
 ↓
Service
```

Pastikan authorization tidak dilewati oleh endpoint tertentu.

---

# 33. Related Decisions

```text
ADR-002 — Inertia + Vue
ADR-005 — Approval Workflow
ADR-008 — Audit Log
```

Dokumen terkait:

```text
07_PERMISSION_MATRIX.md
01_AUTHENTICATION.md
02_RBAC.md
10_APPROVAL_WORKFLOW.md
```

---

# 34. Final Decision

**Accepted**

Inventra menggunakan:

```text
RBAC
+
Granular Permissions
+
Laravel Policies / Authorization
+
Warehouse Data Scope
```

sebagai authorization architecture.

Prinsip utamanya:

```text
Role
    ↓
Permission
    ↓
Scope
    ↓
Policy
    ↓
Resource
```

Authorization selalu ditegakkan di **backend**. Frontend hanya digunakan untuk meningkatkan UX.

Dengan model ini, Inventra dapat membatasi bukan hanya **apa yang boleh dilakukan user**, tetapi juga **data warehouse mana yang boleh mereka akses**.

# Inventra

## Sprint 09 — Asset Management

**Sprint:** SPRINT-09
**Name:** Asset Management
**Project:** Inventra
**Status:** Planned
**Branch:** `feature/asset-management`

---

# 1. Sprint Overview

Asset Management digunakan untuk mengelola barang yang perlu dilacak secara individual.

Contoh:

```text id="0p5v1w"
Laptop
Desktop
Monitor
Printer
Handheld Device
Equipment
```

Berbeda dengan stock biasa:

```text id="8w8v1b"
Inventory Item
→ quantity based
```

Asset:

```text id="x4o7w9"
Individual Asset
→ identity based
```

---

# 2. Objective

Membangun Asset Management yang dapat:

- Mencatat asset.
- Menghubungkan asset dengan item.
- Memberikan asset code unik.
- Menyimpan serial number.
- Menyimpan warehouse/location.
- Menyimpan kondisi asset.
- Menyimpan status asset.
- Mencatat assignment ke user.
- Mencatat transfer asset.
- Mencatat maintenance status.
- Menyimpan histori perubahan.
- Menyediakan audit trail.

---

# 3. Scope

### Included

```text id="z5w4n3"
Asset
Asset Code
Serial Number
Item
Warehouse
Location
Condition
Status
Assignment
Transfer
Asset History
Audit Log
```

### Not Included

```text id="0p6v8k"
Depreciation Accounting
Fixed Asset Accounting
Purchase Order
Maintenance Scheduling
Vendor Management
Warranty Automation
```

Fitur tersebut dapat dikembangkan kemudian.

---

# 4. Inventory vs Asset

Ini harus dipahami dengan jelas.

### Inventory

```text id="4p6c3q"
Mouse
Quantity = 100
```

Tidak harus mengetahui identitas setiap mouse.

### Asset

```text id="9n2s7w"
Laptop
Asset Code = AST-00001
Serial Number = ABC123
Assigned To = Budi
Condition = Good
```

Satu asset memiliki identity sendiri.

---

# 5. Asset Structure

```text id="r6x2a1"
Asset
├── Asset Code
├── Item
├── Serial Number
├── Warehouse
├── Location
├── Condition
├── Status
├── Assigned User
└── History
```

---

# 6. Asset Header

Concept:

```text id="5r2k3x"
assets
├── id
├── asset_code
├── item_id
├── serial_number
├── warehouse_id
├── location_id
├── condition
├── status
├── assigned_to
├── purchase_date
├── notes
├── created_at
└── updated_at
```

Struktur final mengikuti:

```text id="8m5n9v"
docs/05_DATABASE.md
```

---

# 7. Asset Code

Setiap asset harus memiliki identifier unik.

Contoh:

```text id="4z7n3q"
AST-000001
AST-000002
AST-000003
```

Constraint:

```text id="r4f7x2"
asset_code
→ UNIQUE
```

Asset code digunakan untuk:

```text id="9s5m2q"
Search
Identification
Label
Audit
Assignment
Reporting
```

---

# 8. Serial Number

Jika asset memiliki serial number:

```text id="7y6w4t"
Serial Number
→ UNIQUE
```

Tetapi tidak semua asset harus memiliki serial number.

Contoh:

```text id="e4v9c2"
Laptop
→ Serial required

Office Chair
→ Serial optional
```

Rule dapat mengikuti jenis item.

---

# 9. Asset Status

Contoh status:

```text id="q3f6p8"
AVAILABLE
ASSIGNED
IN_MAINTENANCE
DAMAGED
LOST
DISPOSED
```

Lifecycle:

```text id="j5d3q9"
AVAILABLE
   │
   ▼
ASSIGNED
   │
   ├──► IN_MAINTENANCE
   │          │
   │          ▼
   │      AVAILABLE
   │
   ▼
DAMAGED / LOST
   │
   ▼
DISPOSED
```

Tidak semua status dapat berpindah bebas.

---

# 10. Asset Condition

Contoh:

```text id="h5j7s3"
NEW
GOOD
FAIR
DAMAGED
```

Condition berbeda dengan status.

Contoh:

```text id="2x6q8p"
Status:
ASSIGNED

Condition:
GOOD
```

Asset bisa:

```text id="6w4j1e"
ASSIGNED + DAMAGED
```

---

# 11. Status vs Condition

### Status

Menjawab:

```text id="8t2v6k"
"What is the lifecycle state?"
```

### Condition

Menjawab:

```text id="4q7y1m"
"What is the physical condition?"
```

Jangan menggabungkan keduanya menjadi satu field.

---

# 12. Asset Assignment

Asset dapat diberikan kepada user.

Contoh:

```text id="s3d9q4"
Laptop
AST-00001

Assigned To:
Budi
```

Assignment harus memiliki histori.

Jangan hanya overwrite:

```text id="8k2p5z"
assigned_to = new_user
```

tanpa menyimpan siapa pemegang sebelumnya.

---

# 13. Assignment History

Contoh:

```text id="7q4m2a"
Laptop AST-001

Budi
2026-01-01
    ↓
Andi
2026-06-01
    ↓
Warehouse
2026-08-01
```

Histori membantu menjawab:

```text id="3v7x5n"
Siapa yang pernah memegang asset ini?
Kapan?
Dari mana?
Dipindahkan ke mana?
```

---

# 14. Asset Assignment Structure

Concept:

```text id="m4x9z1"
asset_assignments
├── id
├── asset_id
├── assigned_to
├── assigned_at
├── returned_at
├── notes
└── created_by
```

Assignment history sebaiknya immutable.

---

# 15. Asset Transfer

Asset dapat berpindah:

```text id="5g3k8q"
Warehouse A
      ↓
Warehouse B
```

atau:

```text id="4r6p9m"
User A
      ↓
User B
```

Setiap transfer menghasilkan history.

---

# 16. Asset Location

Location dapat berupa:

```text id="6x7v3n"
Warehouse
Room
Office
Department
Other
```

Untuk V1, asset minimal terhubung dengan:

```text id="p9f2q4"
Warehouse
```

dan location jika diperlukan.

---

# 17. Asset Lifecycle

Flow sederhana:

```text id="h8r3c6"
CREATE
 ↓
AVAILABLE
 ↓
ASSIGN
 ↓
ASSIGNED
 ↓
RETURN
 ↓
AVAILABLE
```

Maintenance:

```text id="m5t8q2"
ASSIGNED
 ↓
IN_MAINTENANCE
 ↓
AVAILABLE
```

Disposal:

```text id="k3p7z5"
DAMAGED / LOST
 ↓
DISPOSED
```

---

# 18. Asset Creation

Asset dapat dibuat dari:

```text id="6m4n8v"
Stock In
```

atau dibuat secara manual jika business rule mengizinkan.

Contoh:

```text id="7x3r9q"
Stock In
Laptop × 3
      ↓
Create 3 Assets
      ↓
AST-000001
AST-000002
AST-000003
```

Untuk serialized item, setiap unit menjadi asset individual.

---

# 19. Serialized vs Non-Serialized

Konsep:

```text id="j2x8p5"
Serialized Item
→ setiap unit punya identity

Non-Serialized Item
→ quantity based
```

Contoh:

```text id="6q9r3w"
Laptop
3 units
→ 3 assets

Mouse
100 units
→ inventory quantity
```

Rule ini sebaiknya ditentukan pada master item.

---

# 20. Asset and Inventory Relationship

Asset bukan sistem inventory terpisah.

Asset tetap berhubungan dengan inventory.

```text id="8f3m6x"
Item
 ↓
Inventory
 ↓
Asset
```

Contoh:

```text id="y4t7q2"
Item:
Laptop

Stock:
10

Assets:
AST-001
AST-002
...
AST-010
```

Business rule harus menjaga konsistensi antara jumlah asset dan inventory jika item bersifat serialized.

---

# 21. Asset Assignment Does Not Mean Stock Movement

Jika:

```text id="n3p8v6"
Warehouse
 ↓
User
```

tidak selalu berarti:

```text id="f4j2k8"
Stock Out
```

Asset assignment adalah perubahan ownership/custody.

Inventory movement dan asset custody harus dibedakan.

---

# 22. Asset Return

Ketika user mengembalikan:

```text id="4m8q2s"
ASSIGNED
 ↓
RETURN
 ↓
AVAILABLE
```

Asset kembali ke lokasi/warehouse yang ditentukan.

Condition dapat diperbarui:

```text id="9x5v3n"
GOOD
```

atau:

```text id="2k7q4p"
DAMAGED
```

---

# 23. Asset Maintenance

V1 cukup menyediakan status:

```text id="v6x9m3"
IN_MAINTENANCE
```

Flow:

```text id="f8q2s5"
AVAILABLE / ASSIGNED
 ↓
IN_MAINTENANCE
 ↓
AVAILABLE
```

Detail maintenance dapat menjadi fitur terpisah di masa depan.

---

# 24. Asset Disposal

Disposal:

```text id="q5m8x2"
Asset
 ↓
DISPOSED
```

Setelah disposed:

```text id="v7p4n9"
Cannot Assign
Cannot Transfer
Cannot Return
```

kecuali terdapat reversal process yang secara eksplisit diizinkan.

---

# 25. Approval

Untuk action tertentu:

```text id="6x8r2m"
Assignment
Transfer
Disposal
```

dapat membutuhkan approval.

Untuk V1, minimal:

```text id="h3q7v5"
Disposal
→ Approval required
```

Detail final mengikuti approval workflow Inventra.

---

# 26. Asset Disposal Flow

```text id="x7m2q8"
AVAILABLE / DAMAGED
 ↓
DISPOSAL REQUEST
 ↓
SUBMITTED
 ↓
APPROVED
 ↓
DISPOSED
```

Audit wajib mencatat:

```text id="4n6p8s"
Who
When
Asset
Reason
Approver
```

---

# 27. Frontend Structure

```text id="8x4q6m"
resources/js/Pages/Assets/
├── Index.vue
├── Create.vue
├── Show.vue
└── Edit.vue
```

Components:

```text id="m7p3x9"
resources/js/Components/Assets/
├── AssetForm.vue
├── AssetStatusBadge.vue
├── AssetAssignment.vue
├── AssetHistory.vue
└── AssetTransfer.vue
```

---

# 28. Backend Structure

```text id="6q8v2p"
app/
├── Models/
│   ├── Asset.php
│   ├── AssetAssignment.php
│   └── AssetHistory.php
│
├── Http/
│   ├── Controllers/
│   │   └── AssetController.php
│   │
│   └── Requests/
│       └── Asset/
│
├── Policies/
│   └── AssetPolicy.php
│
└── Services/
    └── Asset/
        └── AssetService.php
```

---

# 29. Asset Service

Business logic:

```text id="p4x7m2"
AssetService
 ↓
Create Asset
 ↓
Assign
 ↓
Return
 ↓
Transfer
 ↓
Maintenance
 ↓
Dispose
 ↓
History
 ↓
Audit
```

Controller:

```text id="q8v3n5"
Request
 ↓
Authorization
 ↓
Service
 ↓
Response
```

---

# 30. Asset History

Setiap perubahan penting harus dapat ditelusuri.

Contoh:

```text id="5n7q2x"
ASSET_CREATED
ASSET_ASSIGNED
ASSET_RETURNED
ASSET_TRANSFERRED
ASSET_MAINTENANCE_STARTED
ASSET_MAINTENANCE_COMPLETED
ASSET_CONDITION_CHANGED
ASSET_DISPOSAL_REQUESTED
ASSET_DISPOSED
```

---

# 31. Asset History Example

```text id="8m3p6v"
AST-00001

2026-01-01
Created

2026-01-03
Assigned → Budi

2026-05-10
Condition → DAMAGED

2026-05-11
Maintenance Started

2026-05-20
Maintenance Completed

2026-05-21
Assigned → Andi
```

---

# 32. Asset Security

Asset harus dilindungi dari:

```text id="p6x4m9"
Unauthorized Assignment
Unauthorized Transfer
Unauthorized Disposal
IDOR
Mass Assignment
Warehouse Scope Bypass
Status Manipulation
History Tampering
```

---

# 33. IDOR Protection

Request:

```text id="a3n7q8"
POST /assets/123/assign
```

tidak boleh langsung dipercaya.

Backend:

```text id="h5m2x7"
Authentication
 ↓
Permission
 ↓
Warehouse Scope
 ↓
Asset Policy
 ↓
Status Validation
 ↓
Action
```

---

# 34. Status Transition Protection

Tidak semua transition diperbolehkan.

Contoh:

```text id="k8q4m1"
DISPOSED
 ↓
ASSIGNED
```

harus ditolak.

Valid:

```text id="r6x2p9"
AVAILABLE
 ↓
ASSIGNED
```

Invalid:

```text id="j3v7n5"
DISPOSED
 ↓
AVAILABLE
```

---

# 35. Assignment Validation

Sebelum assign:

```text id="0q7x4m"
Asset exists?
 ↓
Asset active?
 ↓
Asset status = AVAILABLE?
 ↓
Target user valid?
 ↓
User active?
 ↓
User allowed?
```

Jika valid:

```text id="9m5p2k"
ASSIGN
```

---

# 36. Return Validation

```text id="6x4q8n"
Asset exists?
 ↓
Status = ASSIGNED?
 ↓
Current assignment exists?
 ↓
Return
```

Kemudian:

```text id="m2p7v5"
Status = AVAILABLE
```

atau:

```text id="f8x3q6"
Status = DAMAGED
```

berdasarkan condition.

---

# 37. Transfer Validation

```text id="q4n8m2"
Asset exists?
 ↓
Not DISPOSED?
 ↓
Destination valid?
 ↓
User has access?
 ↓
Transfer
```

History dibuat setelah transfer berhasil.

---

# 38. Transaction Safety

Assignment/transfer yang mengubah beberapa record harus menggunakan database transaction.

Contoh assignment:

```text id="w7x3p5"
BEGIN
 ↓
Validate Asset
 ↓
Lock Asset
 ↓
Create Assignment
 ↓
Update Asset Status
 ↓
Create History
 ↓
Audit
 ↓
COMMIT
```

---

# 39. Concurrency

Contoh:

```text id="8q3m7v"
User A → Assign Asset
User B → Assign Asset
```

Keduanya tidak boleh berhasil.

Gunakan:

```text id="p5x8n2"
Transaction
+
Row Lock
```

agar hanya satu assignment yang dapat mengubah asset pada satu waktu.

---

# 40. Database Constraints

Minimal:

```text id="7m2q9x"
asset_code
→ UNIQUE

serial_number
→ UNIQUE when applicable

asset_assignments.asset_id
→ FOREIGN KEY

assets.item_id
→ FOREIGN KEY

assets.warehouse_id
→ FOREIGN KEY
```

Assignment aktif dapat memiliki constraint/business rule agar satu asset tidak mempunyai dua holder aktif.

---

# 41. Database Index

Potential:

```text id="x5n8q3"
assets.asset_code
assets.serial_number
assets.item_id
assets.warehouse_id
assets.status
assets.assigned_to

asset_assignments.asset_id
asset_assignments.assigned_to
asset_assignments.assigned_at

asset_history.asset_id
asset_history.created_at
```

Gunakan:

```text id="q2m7v4"
EXPLAIN
```

untuk mengevaluasi query.

---

# 42. Frontend Responsibility

Frontend:

```text id="p8x3m5"
Asset List
Asset Form
Assignment UI
Transfer UI
History
Status
Condition
Confirmation
```

Frontend tidak menentukan apakah action diperbolehkan.

---

# 43. Backend Responsibility

Backend:

```text id="m6q2x9"
Authentication
Authorization
Warehouse Scope
Validation
Status Transition
Assignment
Transfer
History
Concurrency
Audit
```

---

# 44. Maintenance Guide

### "Saya ingin mengubah tampilan asset."

Cari:

```text id="v3q8m1"
resources/js/Pages/Assets/
```

---

### "Saya ingin mengubah tampilan history."

Cari:

```text id="n5x2q7"
resources/js/Components/Assets/AssetHistory.vue
```

---

### "Saya ingin mengubah aturan assignment."

Cari:

```text id="q7m3x8"
AssetPolicy
```

dan:

```text id="p2n6v4"
AssetService
```

---

### "Asset tidak bisa di-assign."

Trace:

```text id="6x8q3m"
Assign Button
 ↓
Route
 ↓
Controller
 ↓
Policy
 ↓
AssetService
 ↓
Status Validation
 ↓
Assignment
```

---

### "Asset punya dua user."

Periksa:

```text id="m4q8x2"
Concurrency
 ↓
Lock
 ↓
Active Assignment
 ↓
Status
```

---

### "Asset disposed masih bisa diassign."

Periksa:

```text id="7n3p5x"
AssetPolicy
 ↓
AssetService
 ↓
Status Transition
```

---

# 45. Code Understanding Map

```text id="8x5m2q"
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
AssetService
 ↓
Database Transaction
 ├── Asset
 ├── Assignment
 ├── History
 └── Audit
 ↓
Response
 ↓
Vue
```

---

# 46. Asset Assignment Flow

```text id="3m7x9q"
Assign
 ↓
Policy
 ↓
Validate Asset
 ↓
Lock Asset
 ↓
Create Assignment
 ↓
Update Asset
 ↓
Create History
 ↓
Audit
 ↓
Commit
```

---

# 47. Asset Transfer Flow

```text id="6q2m8x"
Transfer
 ↓
Policy
 ↓
Validate Source
 ↓
Validate Destination
 ↓
Lock Asset
 ↓
Update Location
 ↓
Create History
 ↓
Audit
 ↓
Commit
```

---

# 48. Asset Disposal Flow

```text id="p5x3n7"
Request Disposal
 ↓
SUBMITTED
 ↓
Approval
 ↓
APPROVED
 ↓
Dispose
 ↓
DISPOSED
 ↓
History
 ↓
Audit
```

---

# 49. Testing

### CRUD

```text id="x8m4q2"
[ ] Asset can be created
[ ] Asset can be viewed
[ ] Asset can be updated
[ ] Disposed asset cannot be normally edited
```

### Assignment

```text id="n3p7x5"
[ ] Available asset can be assigned
[ ] Assigned asset cannot be assigned again
[ ] Assignment history created
[ ] Return works
[ ] Current holder is correct
```

### Transfer

```text id="q6m2x8"
[ ] Transfer works
[ ] Destination validated
[ ] Transfer history created
[ ] Unauthorized warehouse transfer rejected
```

### Status

```text id="7x4p9m"
[ ] Valid transitions work
[ ] Invalid transitions rejected
[ ] Disposed asset cannot be assigned
[ ] Disposed asset cannot be transferred
```

### Security

```text id="m8q3x5"
[ ] IDOR blocked
[ ] Permission enforced
[ ] Warehouse scope enforced
[ ] Unauthorized disposal blocked
[ ] Mass assignment protected
```

### Concurrency

```text id="p4x7m2"
[ ] Two users cannot assign same asset
[ ] Concurrent transfer handled safely
[ ] Transaction rolls back on failure
```

---

# 50. Acceptance Criteria

Sprint selesai apabila:

```text id="q7m3x8"
1. Asset dapat dibuat.

2. Asset memiliki asset code unik.

3. Serial number dapat disimpan.

4. Asset terhubung dengan Item.

5. Asset terhubung dengan Warehouse.

6. Status asset tersedia.

7. Condition asset tersedia.

8. Asset dapat di-assign.

9. Asset dapat dikembalikan.

10. Asset dapat ditransfer.

11. Assignment memiliki history.

12. Transfer memiliki history.

13. Status transition divalidasi.

14. Disposed asset tidak dapat digunakan kembali secara normal.

15. Disposal memiliki approval.

16. Assignment menggunakan transaction.

17. Transfer menggunakan transaction.

18. Concurrency protection tersedia.

19. Warehouse scope diterapkan.

20. IDOR protection tersedia.

21. Audit log tersedia.

22. Database constraints tersedia.

23. Index relevan tersedia.

24. Automated tests berhasil.

25. Code documentation mengikuti standard Inventra.

26. Developer dapat tracing Asset dari Vue → Laravel → Database → History → Audit.
```

---

# 51. Expected Files

```text id="8m4q6x"
app/
├── Models/
│   ├── Asset.php
│   ├── AssetAssignment.php
│   └── AssetHistory.php
│
├── Http/
│   ├── Controllers/
│   │   └── AssetController.php
│   │
│   └── Requests/
│       └── Asset/
│
├── Policies/
│   └── AssetPolicy.php
│
└── Services/
    └── Asset/
        └── AssetService.php

database/
└── migrations/
    ├── xxxx_create_assets_table.php
    ├── xxxx_create_asset_assignments_table.php
    └── xxxx_create_asset_histories_table.php

resources/js/
├── Pages/
│   └── Assets/
│
└── Components/
    └── Assets/

tests/
└── Feature/
    └── Asset/
```

---

# 52. Code Documentation

Setiap file mengikuti:

```text id="q3x7m5"
docs/code-guide/00_CODE_DOCUMENTATION_STANDARD.md
```

Contoh:

```php id="m8p2q4"
/**
 * Asset Service
 *
 * Purpose:
 * Handle asset lifecycle and custody operations.
 *
 * Main Flow:
 * Create
 * → Assign
 * → Return
 * → Transfer
 * → Maintenance
 * → Dispose
 *
 * Important:
 * Asset status transitions must be validated.
 *
 * Assignment and transfer operations must
 * use database transactions.
 *
 * Related:
 * - Asset
 * - AssetAssignment
 * - AssetHistory
 */
```

---

# 53. Git Branch

```text id="x6q3m8"
feature/asset-management
```

Dependency:

```text id="5m8p2q"
feature/warehouse
        ↓
feature/stock-in
        ↓
feature/asset-management
```

---

# 54. Suggested Commits

```text id="q7x3m5"
feat(asset): add asset models and migrations
feat(asset): add asset CRUD
feat(asset): add asset code generation
feat(asset): add asset assignment
feat(asset): add asset return
feat(asset): add asset transfer
feat(asset): add asset history
feat(asset): add asset condition management
feat(asset): add asset status workflow
feat(asset): add asset disposal workflow
feat(asset): add asset authorization
feat(asset): add asset concurrency protection
feat(asset): add asset audit logging
test(asset): add asset lifecycle tests
test(asset): add asset assignment tests
test(asset): add asset concurrency tests
docs(asset): document asset code flow
```

---

# 55. Definition of Done

```text id="m5q8x2"
Code
    ✓ Asset
    ✓ Assignment
    ✓ Return
    ✓ Transfer
    ✓ History
    ✓ Disposal

Lifecycle
    ✓ Status
    ✓ Condition
    ✓ Valid transitions

Security
    ✓ Authorization
    ✓ Warehouse scope
    ✓ IDOR protection
    ✓ Mass assignment protection

Concurrency
    ✓ Assignment protected
    ✓ Transfer protected

Audit
    ✓ Lifecycle audited
    ✓ Assignment traceable
    ✓ Transfer traceable
    ✓ Disposal traceable

Testing
    ✓ Lifecycle tests
    ✓ Assignment tests
    ✓ Transfer tests
    ✓ Security tests
    ✓ Concurrency tests

Documentation
    ✓ Code comments
    ✓ Maintenance guide
    ✓ Request flow
    ✓ Lifecycle flow

Git
    ✓ feature/asset-management
```

---

# 56. Final Asset Architecture

```text id="9q3m7x"
                         ASSET
                           │
                           ▼
                         CREATE
                           │
                           ▼
                       AVAILABLE
                           │
             ┌─────────────┼─────────────┐
             ▼             ▼             ▼
          ASSIGN        TRANSFER      MAINTENANCE
             │             │             │
             ▼             ▼             ▼
          ASSIGNED       LOCATION     IN_MAINTENANCE
             │                         │
             └────────────┬────────────┘
                          ▼
                       AVAILABLE
                          │
                          ▼
                      DISPOSAL
                          │
                          ▼
                       APPROVAL
                          │
                          ▼
                       DISPOSED
```

---

# 57. Key Principle

Asset Management menjawab:

```text id="3x8m5q"
"Barang individual ini sekarang berada di mana,
siapa yang memegangnya, bagaimana kondisinya,
dan apa histori perubahannya?"
```

Perbedaan utama dengan inventory:

```text id="q5m8x2"
Inventory
→ Quantity

Asset
→ Identity + Custody + Location + Condition + History
```

Dan ketika kamu ingin memahami kode Asset Management:

```text id="m3q7x9"
Vue
 ↓
Inertia
 ↓
Laravel Route
 ↓
Policy
 ↓
Controller
 ↓
AssetService
 ↓
Database Transaction
 ├── Asset
 ├── Assignment
 ├── History
 └── Audit
 ↓
Response
 ↓
Vue
```

Jadi kalau suatu hari kamu ditanya:

> **"Kalau laptop dipindahkan dari Budi ke Andi, kode mana yang harus kamu cek?"**

alur berpikirmu:

```text id="8x4m2q"
Transfer UI
 ↓
AssetController
 ↓
AssetPolicy
 ↓
AssetService
 ↓
Asset + Assignment
 ↓
AssetHistory
 ↓
AuditLog
```

Bukan langsung mengubah database secara manual. Itu yang membuat kamu benar-benar memahami sistem, bukan sekadar bisa membuatnya dengan vibe coding.

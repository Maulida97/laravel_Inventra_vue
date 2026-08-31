# Inventra

## Asset Management Code Guide

**Document:** Asset Management Code Guide
**Version:** V1.0
**Status:** Draft

---

# 1. Purpose

Asset Management digunakan untuk mengelola barang yang memiliki **identitas individual dan lifecycle**, bukan hanya quantity.

Contoh asset:

- Laptop
- PC
- Monitor
- Printer
- Kendaraan
- Peralatan tertentu
- Perangkat dengan serial number

Berbeda dengan inventory biasa:

```text
Inventory
→ Quantity-based
```

Sedangkan asset:

```text
Asset
→ Individual-based
```

Contoh:

```text
Laptop Dell
Asset Code: AST-IT-00001
Serial Number: SN123456
Assigned To: Budi
Department: IT
Condition: GOOD
Status: ASSIGNED
```

---

# 2. Inventory vs Asset

Perbedaan utama:

```text
INVENTORY

Cable LAN
Quantity = 100 PCS
```

Tidak perlu mengetahui identitas setiap kabel.

Sedangkan:

```text
ASSET

Laptop
AST-IT-00001
Serial = ABC123

Laptop
AST-IT-00002
Serial = ABC124
```

Setiap unit memiliki identitas sendiri.

---

# 3. Asset Responsibility

Asset Management bertanggung jawab terhadap:

- Asset code.
- Item reference.
- Serial number.
- Asset category.
- Purchase information jika digunakan.
- Acquisition date.
- Warehouse/location.
- Current holder.
- Department.
- Condition.
- Status.
- Assignment.
- Transfer.
- Return.
- Maintenance status jika dibutuhkan.
- Disposal.
- Audit trail.

---

# 4. Asset Architecture

```text
                       ASSET
                         │
                         ▼
                    Asset Master
                         │
              ┌──────────┼──────────┐
              ▼          ▼          ▼
            Item      Location    Owner
              │          │          │
              └──────────┼──────────┘
                         ▼
                       Status
                         │
              ┌──────────┼───────────┐
              ▼          ▼           ▼
          Assignment   Transfer    Maintenance
              │          │           │
              └──────────┼───────────┘
                         ▼
                       Return
                         │
                         ▼
                      Disposal
                         │
                         ▼
                       Audit
```

---

# 5. Asset Lifecycle

Lifecycle utama:

```text
PROCURED
   ↓
RECEIVED
   ↓
IN_STOCK
   ↓
ASSIGNED
   ↓
TRANSFERRED
   ↓
RETURNED
   ↓
MAINTENANCE
   ↓
IN_STOCK
   ↓
DISPOSED
```

Tidak semua asset harus melewati seluruh state.

---

# 6. Asset Status

Minimal:

```text
IN_STOCK
ASSIGNED
IN_TRANSIT
MAINTENANCE
LOST
DAMAGED
DISPOSED
```

Status harus merepresentasikan **kondisi lifecycle asset**, bukan sekadar kondisi fisik.

---

# 7. Condition

Condition dipisahkan dari status.

Contoh:

```text
Condition:
GOOD
FAIR
DAMAGED
CRITICAL
```

Sedangkan:

```text
Status:
ASSIGNED
```

Contoh:

```text
Status:
ASSIGNED

Condition:
FAIR
```

Artinya asset sedang digunakan tetapi kondisinya tidak sempurna.

---

# 8. Why Status and Condition Must Be Separate

Jangan:

```text
status = DAMAGED
```

untuk semua kondisi.

Karena:

```text
Status
→ Apa lifecycle asset?

Condition
→ Bagaimana kondisi fisiknya?
```

Contoh:

```text
Laptop A
Status = ASSIGNED
Condition = GOOD
```

atau:

```text
Laptop B
Status = MAINTENANCE
Condition = DAMAGED
```

---

# 9. Asset Code

Setiap asset memiliki unique asset code.

Contoh:

```text
AST-IT-00001
AST-IT-00002
AST-QC-00001
```

Asset code harus:

```text
UNIQUE
NOT NULL
IMMUTABLE
```

Sebaiknya tidak digunakan sebagai foreign key utama.

Gunakan internal numeric/UUID ID sebagai primary key.

---

# 10. Serial Number

Serial number digunakan jika manufacturer memberikan serial number.

Contoh:

```text
Asset Code:
AST-IT-00001

Serial Number:
CN123456789
```

Serial number dapat memiliki unique constraint sesuai business rule.

Jangan menganggap semua asset memiliki serial number.

Contoh:

```text
Asset
AST-001

Serial:
NULL
```

masih valid jika asset tersebut memang tidak memiliki serial number.

---

# 11. Asset Master

Conceptual:

```text
assets
├── id
├── asset_code
├── item_id
├── serial_number
├── status
├── condition
├── warehouse_id
├── location_id
├── department_id
├── assigned_user_id
├── acquisition_date
├── purchase_reference
├── notes
├── created_by
├── updated_by
├── created_at
└── updated_at
```

Tidak semua field harus dipakai jika belum diperlukan oleh PRD.

---

# 12. Item vs Asset

Item:

```text
Laptop Dell Latitude
```

Asset:

```text
AST-IT-00001
AST-IT-00002
AST-IT-00003
```

Relationship:

```text
Item
  │
  ├── Asset A
  ├── Asset B
  └── Asset C
```

Satu item dapat memiliki banyak asset individual.

---

# 13. Asset Creation

Asset dapat dibuat ketika barang diterima.

Flow:

```text
Stock In
 ↓
Item Received
 ↓
Asset Required?
 ↓
YES
 ↓
Create Asset
```

Contoh:

```text
Stock In:
5 Laptop

System:
Create 5 asset records
```

Hasil:

```text
AST-IT-00001
AST-IT-00002
AST-IT-00003
AST-IT-00004
AST-IT-00005
```

---

# 14. Asset Identification

Asset dapat memiliki:

```text
Asset Code
Serial Number
Barcode
QR Code
```

Barcode/QR tidak harus menjadi primary key.

Contoh:

```text
QR
 ↓
AST-IT-00001
 ↓
Asset Detail
```

---

# 15. Asset Assignment

Assignment berarti asset diberikan kepada user atau department.

Contoh:

```text
Asset
AST-IT-00001

From:
Warehouse

To:
Budi - IT
```

Status:

```text
IN_STOCK
 ↓
ASSIGNED
```

---

# 16. Assignment Record

Jangan hanya mengubah:

```text
assigned_user_id = 10
```

Tanpa historical record.

Buat assignment history.

Concept:

```text
asset_assignments
├── id
├── asset_id
├── assigned_to_user_id
├── department_id
├── assigned_at
├── returned_at
├── assigned_by
├── returned_by
└── notes
```

Dengan demikian kita dapat mengetahui:

```text
Siapa pernah menggunakan asset?
Kapan?
Berapa lama?
```

---

# 17. Assignment Flow

```text
Asset
 ↓
Validate Asset
 ↓
Validate User
 ↓
Validate Department
 ↓
Create Assignment
 ↓
Update Asset
 ↓
Audit
```

Semua perubahan penting harus transaction-safe.

---

# 18. Current Assignment

Asset dapat memiliki maksimal satu active assignment.

Concept:

```text
Asset
 ↓
Current Assignment
```

Previous assignments:

```text
returned_at != NULL
```

Active assignment:

```text
returned_at IS NULL
```

Business rule:

```text
One Asset
→ Maximum One Active Assignment
```

---

# 19. Return Asset

Return berarti asset dikembalikan dari user/department.

```text
ASSIGNED
 ↓
RETURN
 ↓
IN_STOCK
```

Contoh:

```text
Budi
 ↓
Return Laptop
 ↓
Warehouse
```

Assignment history ditutup:

```text
returned_at = now()
```

---

# 20. Return Condition

Saat return, condition dapat diperbarui.

Contoh:

```text
Before:
GOOD

After:
DAMAGED
```

Flow:

```text
Return
 ↓
Inspect
 ↓
Update Condition
 ↓
Update Status
```

Jika rusak:

```text
DAMAGED
 ↓
MAINTENANCE
```

---

# 21. Transfer Asset

Asset dapat dipindahkan.

Contoh:

```text
IT
 ↓
QC
```

atau:

```text
WH-A
 ↓
WH-B
```

Transfer harus memiliki historical record.

Concept:

```text
asset_transfers
├── id
├── asset_id
├── from_warehouse_id
├── from_location_id
├── from_user_id
├── from_department_id
├── to_warehouse_id
├── to_location_id
├── to_user_id
├── to_department_id
├── transferred_at
├── transferred_by
└── notes
```

Tidak semua field harus digunakan sekaligus.

---

# 22. Transfer Flow

```text
Asset
 ↓
Validate Current Owner
 ↓
Validate Destination
 ↓
Create Transfer Record
 ↓
Update Asset Location / Assignment
 ↓
Audit
```

---

# 23. Transfer Authorization

User tidak boleh memindahkan asset sembarangan.

Backend harus memeriksa:

```text
User
+
Permission
+
Current Scope
+
Destination Scope
```

Contoh:

```text
asset.transfer
```

harus diberikan hanya kepada role yang sesuai.

---

# 24. Asset Location

Asset dapat berada di:

```text
Warehouse
Location
Department
User
```

Contoh:

```text
Warehouse:
WH-001

Location:
IT-RACK-01

Assigned To:
Budi
```

Saat asset assigned, business rule harus menentukan apakah warehouse/location tetap menyimpan current physical location atau dikosongkan.

Konsistensi model harus dijaga.

---

# 25. Asset Location Rule

Rekomendasi:

```text
Asset memiliki current location
+
current assignment
```

Contoh:

```text
Asset:
AST-001

Location:
IT Office

Assigned:
Budi
```

Jadi assignment tidak menghilangkan kemampuan tracking lokasi.

---

# 26. Maintenance

Jika asset mengalami kerusakan:

```text
ASSIGNED
 ↓
RETURNED
 ↓
MAINTENANCE
```

Maintenance record:

```text
asset_maintenances
├── id
├── asset_id
├── started_at
├── completed_at
├── problem
├── action
├── vendor
├── cost
├── result
└── created_by
```

Maintenance tidak harus langsung dibuat kompleks pada V1.

---

# 27. Maintenance Completion

Setelah selesai:

```text
MAINTENANCE
 ↓
Inspection
 ↓
Condition Update
 ↓
IN_STOCK / ASSIGNED
```

Contoh:

```text
Condition:
DAMAGED

Repair

Condition:
GOOD
```

---

# 28. Lost Asset

Asset dapat ditandai:

```text
LOST
```

Flow:

```text
ASSIGNED
 ↓
LOST
```

Asset tidak langsung dihapus.

History tetap dipertahankan.

---

# 29. Found Asset

Jika ditemukan:

```text
LOST
 ↓
FOUND
 ↓
Inspection
 ↓
IN_STOCK / ASSIGNED
```

Jika business rule membutuhkan status `FOUND`, tambahkan sebagai status tersendiri.

---

# 30. Disposal

Disposal berarti asset sudah tidak digunakan lagi.

Contoh:

```text
DAMAGED
 ↓
DISPOSAL
 ↓
DISPOSED
```

Disposal harus memiliki record.

Concept:

```text
asset_disposals
├── id
├── asset_id
├── disposal_date
├── reason
├── method
├── approved_by
├── disposed_by
├── notes
└── created_at
```

---

# 31. Disposal Authorization

Disposal merupakan operasi sensitif.

Minimal:

```text
asset.disposal.request
asset.disposal.approve
asset.disposal.execute
```

Separation of duties dapat digunakan:

```text
Requester
≠
Approver
```

dan jika diperlukan:

```text
Approver
≠
Executor
```

---

# 32. Asset Lifecycle History

Semua perubahan lifecycle harus traceable.

Contoh:

```text
AST-IT-00001

2026-01-10
IN_STOCK

2026-01-15
ASSIGNED → Budi

2026-04-10
TRANSFERRED → Andi

2026-06-01
RETURNED

2026-06-02
MAINTENANCE

2026-06-05
IN_STOCK
```

Jangan hanya menyimpan current status.

---

# 33. Asset History

Concept:

```text
asset_histories
├── id
├── asset_id
├── event_type
├── from_status
├── to_status
├── from_user_id
├── to_user_id
├── from_location_id
├── to_location_id
├── reference_type
├── reference_id
├── notes
└── created_at
```

History dapat menjadi sumber audit lifecycle.

---

# 34. Asset State Transition

Gunakan state transition yang jelas.

Contoh:

```text
IN_STOCK
 ├── ASSIGN
 │      ↓
 │   ASSIGNED
 │
 ├── TRANSFER
 │      ↓
 │   IN_TRANSIT
 │
 └── DISPOSE
        ↓
     DISPOSED
```

Dari `DISPOSED`:

```text
No normal transition
```

Asset disposed dianggap final.

---

# 35. Invalid State Transition

Contoh invalid:

```text
DISPOSED
 ↓
ASSIGNED
```

Backend harus menolak.

Contoh:

```text
if asset.status === DISPOSED:
    reject
```

State transition harus dikontrol di backend.

---

# 36. Asset Service

Business logic berada di service.

Concept:

```text
AssetService
├── create()
├── assign()
├── return()
├── transfer()
├── markMaintenance()
├── completeMaintenance()
├── markLost()
├── recover()
├── requestDisposal()
├── approveDisposal()
└── dispose()
```

Tidak semua method harus dibuat sekaligus.

---

# 37. Assignment Service

Jika kompleksitas meningkat, pisahkan:

```text
AssetAssignmentService
├── assign()
└── return()
```

Flow:

```text
AssetController
 ↓
AssetAssignmentService
 ↓
Asset
+
Assignment History
+
Audit
```

---

# 38. Transfer Service

```text
AssetTransferService
└── transfer()
```

Responsibility:

```text
Validate
 ↓
Authorization
 ↓
Transaction
 ↓
Transfer History
 ↓
Update Asset
 ↓
Audit
```

---

# 39. Disposal Service

```text
AssetDisposalService
├── request()
├── approve()
└── execute()
```

Disposal execution harus transaction-safe.

---

# 40. Controller Responsibility

Controller:

```text
Request
 ↓
Authorize
 ↓
Validate
 ↓
Service
 ↓
Response
```

Jangan:

```text
Controller
 ↓
Update Asset
 ↓
Create History
 ↓
Create Assignment
 ↓
Audit
```

Semua business logic tersebut sebaiknya berada di service.

---

# 41. Form Request

Contoh assignment:

```text
asset_id
→ required
→ exists

user_id
→ required
→ exists

department_id
→ required
→ exists

notes
→ nullable
→ string
```

Backend tetap memvalidasi:

```text
Asset status
User active
Department valid
Scope
Permission
```

---

# 42. Frontend Structure

Concept:

```text
resources/js/Pages/Assets/
├── Index.vue
├── Create.vue
├── Edit.vue
├── Show.vue
├── Assign.vue
├── Transfer.vue
├── Maintenance.vue
└── Components/
```

Components:

```text
AssetForm
AssetStatusBadge
AssetAssignment
AssetTransfer
AssetHistory
AssetTimeline
AssetCondition
```

---

# 43. Asset Detail Page

Detail page sebaiknya menampilkan:

```text
Asset Information
├── Asset Code
├── Item
├── Serial Number
├── Status
├── Condition
├── Location
├── Current Holder
└── Department

Lifecycle
├── Assignment History
├── Transfer History
├── Maintenance History
├── Disposal History
└── Audit
```

---

# 44. QR / Barcode

Jika asset memiliki QR:

```text
Scan QR
 ↓
Asset Code
 ↓
Asset Detail
```

QR hanya menjadi shortcut menuju asset.

Jangan menjadikan QR sebagai satu-satunya security mechanism.

User tetap harus memiliki authorization untuk melihat/mengubah data.

---

# 45. Search & Filter

Asset list:

```text
Asset Code
Serial Number
Item
Category
Status
Condition
Warehouse
Location
Department
Assigned User
```

Gunakan:

```text
Pagination
Filtering
Sorting
Search
```

---

# 46. Query Optimization

Asset list dapat membutuhkan relationship:

```text
Asset
├── Item
├── Warehouse
├── Location
├── Department
└── Assigned User
```

Gunakan eager loading:

```php id="x1d2qv"
Asset::with([
    'item',
    'warehouse',
    'location',
    'department',
    'assignedUser',
])
->paginate(20);
```

Hindari N+1 query.

---

# 47. Index Recommendation

Assets:

```text id="m1f9zz"
assets.asset_code
assets.serial_number
assets.item_id
assets.status
assets.condition
assets.warehouse_id
assets.location_id
assets.department_id
assets.assigned_user_id
```

Assignment:

```text id="x5e7j2"
asset_assignments.asset_id
asset_assignments.assigned_to_user_id
asset_assignments.department_id
asset_assignments.returned_at
```

Transfer:

```text id="i9gh3r"
asset_transfers.asset_id
asset_transfers.transferred_at
```

History:

```text id="qv4y6d"
asset_histories.asset_id
asset_histories.event_type
asset_histories.created_at
```

Index final mengikuti query pattern aktual.

---

# 48. Database Transaction

Operasi berikut harus transaction-safe:

```text id="6q46lb"
Assignment
Return
Transfer
Maintenance Completion
Disposal
```

Contoh:

```text id="0ez4ly"
BEGIN

Update Asset
 ↓
Create History
 ↓
Create Assignment / Transfer
 ↓
Audit

COMMIT
```

Jika gagal:

```text id="m4w7ar"
ROLLBACK
```

---

# 49. Concurrency

Contoh:

```text id="4e70vc"
User A
Assign Asset

User B
Assign Asset
```

Keduanya tidak boleh berhasil untuk asset yang sama jika hanya satu active assignment yang diperbolehkan.

Gunakan:

```text id="j9t8c5"
Transaction
+
Row Lock
+
Unique Business Constraint
```

Concept:

```php id="j8qf6y"
$asset = Asset::query()
    ->whereKey($assetId)
    ->lockForUpdate()
    ->first();
```

Kemudian validasi current status.

---

# 50. Duplicate Assignment Protection

Sebelum assignment:

```text id="i8h1v2"
Asset Status
+
Active Assignment
```

Jika asset sudah assigned:

```text id="p0f7au"
Reject
```

Jangan membuat:

```text id="k1y1m8"
Assignment A → Budi
Assignment B → Andi
```

keduanya aktif.

---

# 51. Security

Asset Management harus mencegah:

```text id="5c2o3j"
Unauthorized assignment
Unauthorized transfer
Unauthorized disposal
Unauthorized maintenance update
Unauthorized warehouse access
Unauthorized department access
Duplicate assignment
Invalid state transition
Historical data tampering
```

Backend menjadi security boundary.

---

# 52. Common Mistakes

### Mistake 1 — Menganggap asset sebagai quantity

```text id="0hcz7h"
Laptop = 10
```

Tidak cukup jika setiap laptop harus dilacak individual.

---

### Mistake 2 — Hanya menyimpan current owner

```text id="4k3j2s"
assigned_user_id
```

tanpa history.

Akibatnya tidak tahu siapa yang pernah menggunakan asset.

---

### Mistake 3 — Menghapus asset

```text id="9a5r7f"
DELETE FROM assets
```

Tidak disarankan untuk asset historical.

Gunakan lifecycle:

```text id="u0m5yr"
DISPOSED
```

---

### Mistake 4 — Assignment langsung mengubah status tanpa history

Setiap perubahan penting harus traceable.

---

### Mistake 5 — Tidak memisahkan status dan condition

```text id="o4g2l8"
DAMAGED
```

tidak selalu berarti asset lifecycle-nya sama.

---

### Mistake 6 — Disposal tanpa approval

Disposal merupakan operasi high-risk.

---

# 53. Maintenance Guide

### "Saya mau mengubah tampilan asset."

Cari:

```text id="c0h8wt"
resources/js/Pages/Assets/
```

---

### "Saya mau mengubah proses assign asset."

Cari:

```text id="i1lq3u"
AssetAssignmentService::assign()
```

ikuti:

```text id="n5j0qg"
assign()
 ↓
Authorization
 ↓
Lock Asset
 ↓
Validate Status
 ↓
Create Assignment
 ↓
Update Asset
 ↓
History
 ↓
Audit
```

---

### "Saya mau mengubah proses transfer."

Cari:

```text id="z4a8cv"
AssetTransferService::transfer()
```

---

### "Saya mau mengubah disposal."

Cari:

```text id="3m3q9p"
AssetDisposalService
```

---

### "Asset bisa di-assign dua orang."

Periksa:

```text id="0b1qyp"
[ ] Current asset status
[ ] Active assignment
[ ] Row locking
[ ] Unique constraint
[ ] Transaction
```

---

### "History asset hilang."

Periksa:

```text id="j6z8r0"
[ ] Assignment history
[ ] Transfer history
[ ] Asset history
[ ] Soft delete / lifecycle rule
```

---

# 54. Code Reading Flow

Untuk memahami Asset Management:

```text id="c7e4yr"
Index.vue
 ↓
Show.vue
 ↓
Assign.vue / Transfer.vue
 ↓
Route
 ↓
Controller
 ↓
Form Request
 ↓
Policy
 ↓
Asset Service
 ↓
Assignment / Transfer Service
 ↓
Model
 ↓
Database
```

Untuk memahami assignment:

```text id="1r9x0d"
Assign.vue
 ↓
Controller
 ↓
Policy
 ↓
AssetAssignmentService
 ↓
Transaction
 ↓
Lock Asset
 ↓
Validate Status
 ↓
Create Assignment
 ↓
Update Asset
 ↓
History
 ↓
Audit
```

Untuk memahami disposal:

```text id="4q8p9x"
Request Disposal
 ↓
Approval
 ↓
Execute Disposal
 ↓
Transaction
 ↓
Asset Status = DISPOSED
 ↓
Disposal Record
 ↓
History
 ↓
Audit
```

---

# 55. Debugging Checklist

Jika asset tidak bisa di-assign:

```text id="ax4k9y"
[ ] Asset exists
[ ] Asset status
[ ] Active assignment
[ ] User exists
[ ] User active
[ ] Department valid
[ ] Permission
[ ] Policy
[ ] Scope
```

Jika transfer salah:

```text id="f0k7s4"
[ ] Current location
[ ] Current owner
[ ] Destination
[ ] Permission
[ ] Transaction
[ ] Transfer history
```

Jika disposal salah:

```text id="p8v3qn"
[ ] Approval
[ ] Status transition
[ ] Authorization
[ ] Disposal record
[ ] History
[ ] Audit
```

---

# 56. Testing

Minimal:

```text id="8cv8t0"
[ ] Create asset
[ ] Unique asset code
[ ] Serial number validation
[ ] Assign asset
[ ] Return asset
[ ] Transfer asset
[ ] Maintenance
[ ] Maintenance completion
[ ] Mark lost
[ ] Recover asset
[ ] Disposal request
[ ] Disposal approval
[ ] Disposal execution
[ ] Invalid state transition rejected
[ ] Duplicate assignment prevented
[ ] Unauthorized assignment rejected
[ ] Unauthorized transfer rejected
[ ] Unauthorized disposal rejected
[ ] Warehouse scope enforced
[ ] Department scope enforced
[ ] Assignment history created
[ ] Transfer history created
[ ] Lifecycle history created
[ ] Audit created
[ ] Concurrent assignment handled
[ ] Concurrent transfer handled
```

---

# 57. Definition of Done

```text id="d4r1bz"
[ ] Asset CRUD
[ ] Asset code
[ ] Serial number
[ ] Status
[ ] Condition
[ ] Warehouse
[ ] Location
[ ] Assignment
[ ] Return
[ ] Transfer
[ ] Maintenance
[ ] Lost / Recovery
[ ] Disposal
[ ] Lifecycle history
[ ] Assignment history
[ ] Transfer history
[ ] Authorization
[ ] Warehouse scope
[ ] Department scope
[ ] Database transaction
[ ] Concurrency protection
[ ] Duplicate assignment protection
[ ] Audit
[ ] Query optimization
[ ] Index review
[ ] Tests
[ ] Documentation
```

---

# 58. Final Asset Lifecycle

```text id="w9jv6r"
                       ASSET CREATED
                             │
                             ▼
                         IN_STOCK
                             │
                    ┌────────┴────────┐
                    ▼                 ▼
                 ASSIGN             TRANSFER
                    │                 │
                    ▼                 ▼
                ASSIGNED          IN_TRANSIT
                    │                 │
          ┌─────────┴─────────┐       │
          ▼                   ▼       │
       RETURN              LOST       │
          │                   │       │
          ▼                   ▼       │
      IN_STOCK             RECOVER    │
          │                   │       │
          └──────────┬────────┘       │
                     ▼                │
                MAINTENANCE           │
                     │                │
                     ▼                │
                 INSPECTION           │
                     │                │
             ┌───────┴───────┐        │
             ▼               ▼        │
          IN_STOCK         DISPOSE     │
             │               │        │
             │               ▼        │
             │           DISPOSED     │
             │                        │
             └────────────────────────┘
```

---

# 59. Key Principle

Asset Management bukan sekadar:

```text id="4r4u5f"
"Barang yang punya serial number."
```

Asset Management adalah:

```text id="8h5h9n"
Individual Identity
+
Current State
+
Current Location
+
Current Holder
+
Lifecycle
+
Historical Events
+
Authorization
+
Audit Trail
```

Prinsip utama:

```text id="4n3j3p"
ASSET
→ Individual identity

ASSIGNMENT
→ Who is responsible?

TRANSFER
→ Where / to whom did it move?

RETURN
→ Asset comes back

MAINTENANCE
→ Asset condition/lifecycle changes

DISPOSAL
→ Asset permanently leaves active lifecycle

HISTORY
→ What happened to the asset?

AUDIT
→ Who performed the action?
```

Dan ketika maintenance nanti dibutuhkan, pola pembacaan kodenya adalah:

```text id="y7z9j3"
UI
 ↓
Controller
 ↓
Policy
 ↓
Service
 ↓
Transaction
 ↓
Asset
 ↓
History
 ↓
Audit
```

Jadi ketika kamu nanti ingin mengubah sesuatu **tanpa vibe coding**, kamu tidak perlu menghafal seluruh codebase. Kamu cukup mengetahui **alur responsibility** dan mencari service/policy/component yang bertanggung jawab terhadap perubahan tersebut.

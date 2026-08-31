# Inventra

## Stock Opname Code Guide

**Document:** Stock Opname Code Guide
**Version:** V1.0
**Status:** Draft

---

# 1. Purpose

Stock Opname digunakan untuk mencocokkan **stock fisik di warehouse** dengan **stock yang tercatat di sistem**.

Tujuan:

- Mengetahui stock aktual.
- Membandingkan physical stock dengan system stock.
- Mengidentifikasi selisih.
- Mencatat alasan variance.
- Melakukan adjustment secara terkontrol.
- Menjaga audit trail.

Flow utama:

```text
System Stock
     +
Physical Count
     ↓
Comparison
     ↓
Variance
     ↓
Review / Approval
     ↓
Adjustment
     ↓
Inventory Ledger
     ↓
Audit Log
```

---

# 2. Stock Opname Responsibility

Stock Opname bertanggung jawab terhadap:

- Membuat sesi opname.
- Menentukan warehouse/location.
- Menentukan item yang dihitung.
- Mengambil snapshot system stock.
- Mencatat physical count.
- Menghitung variance.
- Menentukan alasan variance.
- Approval adjustment.
- Membuat adjustment movement.
- Audit trail.

Stock Opname **tidak boleh mengubah system stock hanya karena user memasukkan hasil count**.

---

# 3. Important Concept

Stock Opname memiliki tiga angka penting:

```text
System Quantity
Physical Quantity
Variance
```

Rumus:

```text
Variance
=
Physical Quantity
-
System Quantity
```

Contoh:

```text
System = 100
Physical = 95

Variance = -5
```

Artinya terdapat kekurangan 5 unit.

---

# 4. Opname Architecture

```text
                       STOCK OPNAME
                            │
                            ▼
                         Session
                            │
                            ▼
                     Stock Snapshot
                            │
                            ▼
                      Physical Count
                            │
                            ▼
                       Comparison
                            │
                            ▼
                         Variance
                            │
                    ┌───────┴────────┐
                    ▼                ▼
               No Variance       Has Variance
                    │                │
                    │                ▼
                    │          Review / Approval
                    │                │
                    │                ▼
                    │             Adjust
                    │                │
                    └────────┬───────┘
                             ▼
                       Inventory Ledger
                             │
                             ▼
                         Audit Log
```

---

# 5. Stock Opname Session

Opname dilakukan dalam sebuah session.

Contoh:

```text
SO-OPN-2026-00001
```

Conceptual:

```text
Stock Opname
├── ID
├── Document Number
├── Warehouse
├── Date
├── Status
├── Created By
├── Started At
├── Completed At
└── Items
```

---

# 6. Opname Status

Minimal:

```text
DRAFT
IN_PROGRESS
PENDING_REVIEW
APPROVED
COMPLETED
CANCELLED
```

Flow:

```text
DRAFT
 ↓
IN_PROGRESS
 ↓
PENDING_REVIEW
 ↓
APPROVED
 ↓
COMPLETED
```

Jika tidak membutuhkan approval:

```text
DRAFT
 ↓
IN_PROGRESS
 ↓
COMPLETED
```

---

# 7. DRAFT

DRAFT berarti session belum dimulai.

```text
DRAFT
 ↓
Configuration
```

User dapat menentukan:

```text
Warehouse
Location
Item Scope
```

Belum ada adjustment.

---

# 8. IN_PROGRESS

Opname dimulai:

```text
DRAFT
 ↓
IN_PROGRESS
```

Pada tahap ini user melakukan physical counting.

Contoh:

```text
Item A
System = 100
Physical = 97
```

---

# 9. PENDING_REVIEW

Setelah semua count selesai:

```text
IN_PROGRESS
 ↓
PENDING_REVIEW
```

Supervisor/reviewer dapat memeriksa:

```text
System Qty
Physical Qty
Variance
Reason
```

Belum melakukan adjustment jika approval diperlukan.

---

# 10. APPROVED

Jika variance sudah disetujui:

```text
PENDING_REVIEW
 ↓
APPROVED
```

Sistem siap membuat adjustment.

Approval bukan sekadar mengubah status.

Approval memberikan authorization untuk melakukan inventory adjustment.

---

# 11. COMPLETED

Setelah adjustment selesai:

```text
APPROVED
 ↓
Adjustment
 ↓
COMPLETED
```

Session tidak boleh dihitung ulang secara sembarangan setelah completed.

---

# 12. CANCELLED

Session dapat dibatalkan jika business rule mengizinkan.

```text
DRAFT
 ↓
CANCELLED
```

atau:

```text
IN_PROGRESS
 ↓
CANCELLED
```

Session historical sebaiknya tetap disimpan.

---

# 13. Snapshot System Stock

Ini adalah konsep yang sangat penting.

Ketika opname dimulai, sistem dapat mengambil snapshot:

```text
System Stock at Opname Start
```

Contoh:

```text
Item A
System Snapshot = 100
```

Kemudian physical count:

```text
Physical = 95
```

Variance:

```text
95 - 100 = -5
```

---

# 14. Why Snapshot Matters

Bayangkan:

```text
08:00
System Stock = 100

09:00
Stock In +20

10:00
Physical Count = 95
```

Jika menggunakan stock saat ini:

```text
95 - 120 = -25
```

Padahal stock ketika opname dimulai adalah:

```text
100
```

Karena itu snapshot membantu menjaga konteks opname.

---

# 15. Stock Movement During Opname

Idealnya transaksi inventory selama opname dikontrol.

Contoh:

```text
Stock Opname
     ↓
WH-001 / A-01
     ↓
Counting
```

Kemudian ada:

```text
Stock Out
-20
```

Sistem harus memiliki business rule untuk menangani kondisi tersebut.

Pilihan:

```text
Block transaction
```

atau:

```text
Allow transaction
+
Recalculate / reconcile
```

Untuk V1 Inventra, lebih aman jika area yang sedang dihitung **dikunci dari transaksi stock yang dapat mengubah hasil opname**, atau proses opname dilakukan dengan aturan cutoff yang jelas.

---

# 16. Counting Scope

Opname dapat berdasarkan:

```text
Warehouse
Location
Item
Item Category
```

Contoh:

```text
Warehouse:
WH-001

Location:
A-01

Items:
All
```

atau:

```text
Warehouse:
WH-001

Location:
A-01

Items:
IT Equipment
```

---

# 17. Opname Item

Setiap item yang dihitung memiliki:

```text
Item
Location
System Quantity
Physical Quantity
Variance
Unit
Reason
```

Conceptual:

```text
stock_opname_items
├── stock_opname_id
├── item_id
├── location_id
├── system_quantity
├── physical_quantity
├── variance
├── unit_id
└── variance_reason_id
```

---

# 18. System Quantity

System quantity adalah quantity yang menjadi baseline opname.

Contoh:

```text
System Quantity
=
100 PCS
```

Nilai ini harus dapat ditelusuri.

Jangan hanya mengambil angka dari UI.

---

# 19. Physical Quantity

Physical quantity adalah hasil counting.

Contoh:

```text
Physical Quantity
=
97 PCS
```

Input harus divalidasi:

```text
>= 0
```

Negative physical quantity tidak masuk akal untuk physical count.

---

# 20. Variance

Rumus:

```text
Variance
=
Physical
-
System
```

Contoh surplus:

```text
System = 100
Physical = 105

Variance = +5
```

Contoh shortage:

```text
System = 100
Physical = 95

Variance = -5
```

Tidak ada variance:

```text
System = 100
Physical = 100

Variance = 0
```

---

# 21. Variance Types

Concept:

```text
SURPLUS
SHORTAGE
MATCHED
```

Mapping:

```text
Variance > 0
→ SURPLUS

Variance < 0
→ SHORTAGE

Variance = 0
→ MATCHED
```

---

# 22. Variance Reason

Variance sebaiknya memiliki reason.

Contoh:

```text
Wrong Entry
Damaged
Missing
Unrecorded Stock In
Unrecorded Stock Out
Counting Error
Unknown
Other
```

Reason dapat dikembangkan sebagai master data.

---

# 23. Reason Is Important

Jangan hanya menyimpan:

```text
variance = -5
```

Tetapi:

```text
variance = -5
reason = Missing
```

Karena laporan dapat menjawab:

> Mengapa stock sering mengalami shortage?

---

# 24. Adjustment

Variance tidak langsung mengubah stock.

Contoh:

```text
System = 100
Physical = 95
Variance = -5
```

Setelah adjustment:

```text
Inventory
100
 ↓
-5
 ↓
95
```

Adjustment harus menghasilkan ledger movement.

---

# 25. Adjustment Direction

### Shortage

```text
System = 100
Physical = 95

Adjustment:
-5
```

### Surplus

```text
System = 100
Physical = 105

Adjustment:
+5
```

### Matched

```text
System = 100
Physical = 100

Adjustment:
0
```

Tidak perlu membuat movement jika variance = 0.

---

# 26. Adjustment Ledger

Shortage:

```text
OPNAME_ADJUSTMENT
-5
```

Surplus:

```text
OPNAME_ADJUSTMENT
+5
```

Reference:

```text
SO-OPN-2026-00001
```

Dengan demikian adjustment dapat ditelusuri ke session opname.

---

# 27. Adjustment Must Be Atomic

Adjustment harus menggunakan database transaction.

```text
BEGIN TRANSACTION

Validate Opname
      ↓
Lock Inventory
      ↓
Validate Current State
      ↓
Create Adjustment Ledger
      ↓
Update Inventory
      ↓
Mark Opname Completed
      ↓
Create Audit

COMMIT
```

Jika gagal:

```text
ROLLBACK
```

---

# 28. Important: Recheck Current Stock

Snapshot bukan berarti kita boleh langsung mengurangi stock berdasarkan snapshot.

Contoh:

```text
Snapshot = 100
Physical = 95
Variance = -5
```

Namun setelah opname dimulai:

```text
Stock Out = -10
```

Current stock sekarang:

```text
90
```

Jika langsung adjustment:

```text
90 - 5 = 85
```

bisa salah.

Karena itu saat finalization sistem harus memiliki rule untuk menangani movement yang terjadi setelah snapshot.

---

# 29. Safer Adjustment Strategy

Saat finalization:

```text
Snapshot
+
Movement Since Snapshot
+
Physical Count
```

Sistem dapat menentukan expected current quantity berdasarkan transaction movement.

Concept:

```text
Expected Current
=
Snapshot
+
Movements Since Snapshot
```

Kemudian:

```text
Adjustment
=
Physical Count
-
Expected Current
```

Contoh:

```text
Snapshot = 100
Stock Out = -10

Expected Current = 90

Physical = 95

Adjustment = +5
```

Hasil:

```text
90 + 5 = 95
```

Ini jauh lebih aman jika transaksi selama opname diperbolehkan.

---

# 30. Alternative: Freeze

Alternatif yang lebih sederhana:

```text
Start Opname
 ↓
Freeze Location
 ↓
No Stock Movement
 ↓
Physical Count
 ↓
Finalize
```

Keuntungannya:

```text
Simple
Predictable
Easier to Audit
```

Kekurangannya:

```text
Operational disruption
```

Untuk warehouse yang aktif, keputusan ini harus mengikuti business requirement.

---

# 31. Concurrency

Saat adjustment:

```text
Stock Opname
+
Stock Out
```

dapat terjadi bersamaan jika tidak di-freeze.

Maka:

```text
Inventory Row
 ↓
lockForUpdate()
```

digunakan pada finalization.

---

# 32. Authorization

Stock Opname membutuhkan:

```text
Authentication
+
Permission
+
Warehouse Scope
+
Policy
```

Contoh:

```text
stock.opname.create
stock.opname.count
stock.opname.review
stock.opname.approve
stock.opname.adjust
```

Tidak semua role memiliki semua permission.

---

# 33. Separation of Duties

Untuk kontrol internal, sebaiknya:

```text
Counter
≠
Approver
```

Contoh:

```text
Warehouse Staff
→ Count

Supervisor
→ Review / Approve
```

Hal ini meningkatkan accountability.

---

# 34. Stock Opname Policy

Conceptual:

```text
StockOpnamePolicy
├── view
├── create
├── update
├── count
├── submit
├── review
├── approve
├── reject
├── adjust
└── cancel
```

Tidak semua method harus dibuat jika tidak dibutuhkan.

---

# 35. Controller Responsibility

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

Jangan menaruh adjustment logic di controller.

---

# 36. Stock Opname Service

Concept:

```text
StockOpnameService
├── create()
├── start()
├── updateCount()
├── submit()
├── approve()
├── reject()
├── finalize()
└── cancel()
```

`finalize()` menangani adjustment.

---

# 37. Counting Service

Jika ingin memisahkan responsibility:

```text
StockCountService
├── record()
└── update()
```

Tugasnya hanya menangani physical count.

Jangan langsung:

```text
record()
 ↓
Update Inventory
```

karena count belum tentu approved.

---

# 38. Adjustment Service

Adjustment dapat dipisahkan:

```text
InventoryAdjustmentService
└── applyOpnameAdjustment()
```

Flow:

```text
StockOpnameService
        ↓
InventoryAdjustmentService
        ↓
Ledger
        ↓
Inventory
```

Ini membuat logic lebih reusable.

---

# 39. Frontend Structure

Concept:

```text
resources/js/Pages/StockOpnames/
├── Index.vue
├── Create.vue
├── Show.vue
├── Count.vue
└── Components/
```

Components:

```text
OpnameHeader
OpnameItemTable
CountInput
VarianceDisplay
VarianceReasonSelector
ApprovalPanel
```

---

# 40. Counting UI

Contoh:

```text
Item:
Laptop Dell

System:
10

Physical:
9

Variance:
-1

Reason:
Missing
```

Frontend dapat menghitung preview:

```text
Physical - System
```

Tetapi backend menghitung ulang.

---

# 41. Do Not Trust Frontend Variance

User dapat mengirim:

```text
physical = 95
variance = 0
```

Backend harus mengabaikan `variance` dari client atau memvalidasinya.

Backend menghitung:

```text
variance = physical - system
```

Server adalah source of truth.

---

# 42. Batch Counting

Untuk warehouse besar, counting dapat dilakukan batch.

```text
Warehouse
 ↓
Location
 ↓
Batch 1
Batch 2
Batch 3
```

Setiap batch dapat memiliki:

```text
Counter
Timestamp
Status
```

Namun seluruh session tetap memiliki parent:

```text
Stock Opname
```

---

# 43. Double Counting Protection

Untuk menghindari item dihitung dua kali:

```text
Opname
+
Location
+
Item
```

harus memiliki uniqueness sesuai business rule.

Contoh:

```text
UNIQUE (
    stock_opname_id,
    location_id,
    item_id
)
```

Jika satu item boleh muncul beberapa kali karena batch/count cycle, struktur harus disesuaikan.

---

# 44. Recount

Jika hasil dianggap mencurigakan:

```text
First Count
 ↓
Variance
 ↓
Recount
 ↓
Final Count
```

Contoh:

```text
Count 1 = 95
Count 2 = 97
```

Business rule menentukan apakah:

```text
97
```

menjadi final count atau membutuhkan approval tambahan.

---

# 45. Large Variance

Sistem dapat memiliki threshold.

Contoh:

```text
Variance > 20%
```

maka:

```text
Additional Review
```

Contoh:

```text
System = 1000
Physical = 700

Variance = -300
```

dapat memerlukan approval khusus.

Threshold sebaiknya configurable jika memang dibutuhkan.

---

# 46. Audit

Event penting:

```text
Opname Created
Opname Started
Count Recorded
Count Updated
Opname Submitted
Opname Approved
Opname Rejected
Adjustment Applied
Opname Completed
Opname Cancelled
```

Audit mencatat:

```text
Actor
Action
Resource
Timestamp
Reference
```

---

# 47. Historical Integrity

Setelah completed:

```text
Stock Opname
 ↓
Historical Record
```

Jangan mengubah:

```text
System Snapshot
Physical Count
Variance
Approval
Adjustment
```

secara sembarangan.

Jika ada koreksi:

```text
New Adjustment / Correction
```

harus dibuat melalui workflow yang jelas.

---

# 48. Search & Filter

Stock Opname list:

```text
Document Number
Warehouse
Location
Date
Status
Created By
Counter
```

Variance report:

```text
Item
Warehouse
Location
Variance Type
Reason
Date
```

---

# 49. Query Optimization

Untuk detail opname:

```text
Stock Opname
 ├── Warehouse
 ├── Creator
 └── Items
      ├── Item
      ├── Location
      └── Reason
```

Gunakan eager loading sesuai kebutuhan.

Contoh:

```php
StockOpname::with([
    'warehouse',
    'creator',
    'items.item',
    'items.location',
    'items.varianceReason',
])->paginate(20);
```

Hindari N+1 query.

---

# 50. Index Recommendation

Stock Opname:

```text
stock_opnames.document_number
stock_opnames.warehouse_id
stock_opnames.status
stock_opnames.created_by
stock_opnames.created_at
```

Detail:

```text
stock_opname_items.stock_opname_id
stock_opname_items.item_id
stock_opname_items.location_id
```

Ledger:

```text
inventory_ledgers.item_id
inventory_ledgers.warehouse_id
inventory_ledgers.location_id
inventory_ledgers.reference_type
inventory_ledgers.reference_id
```

Final index mengikuti query pattern aktual.

---

# 51. Security

Stock Opname harus mencegah:

```text
Unauthorized warehouse access
Unauthorized counting
Unauthorized approval
Unauthorized adjustment
Fake physical quantity
Duplicate adjustment
Editing completed opname
Tampering with historical count
```

---

# 52. Common Mistakes

### Mistake 1 — Count langsung mengubah stock

```text
Physical Count
 ↓
Update Inventory
```

Tidak boleh.

---

### Mistake 2 — Tidak menggunakan snapshot

Stock dapat berubah selama opname sehingga variance menjadi tidak akurat.

---

### Mistake 3 — Tidak menangani movement selama opname

Stock In/Out dapat membuat baseline berubah.

---

### Mistake 4 — Tidak menggunakan transaction saat adjustment

Ledger dan inventory dapat menjadi tidak konsisten.

---

### Mistake 5 — Trust variance dari frontend

Backend harus menghitung ulang.

---

### Mistake 6 — Menghapus historical opname

Gunakan status/workflow correction.

---

### Mistake 7 — Counter melakukan approval sendiri

Jika business control membutuhkan separation of duties, gunakan role berbeda.

---

# 53. Maintenance Guide

### "Saya mau mengubah tampilan halaman opname."

Cari:

```text
resources/js/Pages/StockOpnames/
```

---

### "Saya mau mengubah input physical count."

Cari:

```text
Count.vue
+
CountInput.vue
```

---

### "Saya mau mengubah perhitungan variance."

Cari:

```text
StockOpnameService
+
StockOpnameItem logic
```

Cari formula:

```text
physical_quantity
-
system_quantity
```

---

### "Saya mau mengubah alasan variance."

Cari:

```text
VarianceReason
```

dan master data terkait.

---

### "Saya mau mengubah proses adjustment."

Cari:

```text
StockOpnameService::finalize()
```

Kemudian ikuti:

```text
finalize()
 ↓
InventoryAdjustmentService
 ↓
Inventory Lock
 ↓
Ledger
 ↓
Inventory Balance
 ↓
Audit
```

---

### "Stock hasil opname salah."

Periksa:

```text
[ ] Snapshot
[ ] Physical Count
[ ] Movement During Opname
[ ] Variance Formula
[ ] Adjustment
[ ] Inventory Lock
[ ] Ledger
```

---

### "Adjustment terjadi dua kali."

Periksa:

```text
[ ] Opname status
[ ] Duplicate request
[ ] Adjustment ledger
[ ] Transaction
[ ] Idempotency
[ ] Concurrency
```

---

# 54. Code Reading Flow

Untuk memahami halaman:

```text
Index.vue
 ↓
Show.vue
 ↓
Count.vue
 ↓
Route
 ↓
Controller
 ↓
Form Request
 ↓
Policy
 ↓
StockOpnameService
```

Untuk memahami variance:

```text
System Snapshot
 ↓
Physical Count
 ↓
Variance Calculation
 ↓
Review
```

Untuk memahami adjustment:

```text
Finalize
 ↓
InventoryAdjustmentService
 ↓
DB Transaction
 ↓
Lock Inventory
 ↓
Create Ledger
 ↓
Update Inventory
 ↓
Complete Opname
 ↓
Audit
```

---

# 55. Debugging Checklist

Jika variance salah:

```text
[ ] System snapshot correct?
[ ] Physical count correct?
[ ] Unit correct?
[ ] Conversion correct?
[ ] Formula correct?
[ ] Movement during opname?
```

Jika adjustment salah:

```text
[ ] Expected current stock?
[ ] Current inventory?
[ ] Lock applied?
[ ] Correct variance?
[ ] Ledger created?
[ ] Inventory updated?
[ ] Transaction committed?
```

Jika user tidak bisa melakukan opname:

```text
[ ] Authentication
[ ] Permission
[ ] Warehouse scope
[ ] Policy
```

---

# 56. Testing

Minimal:

```text
[ ] Create opname
[ ] Start opname
[ ] Snapshot created
[ ] Record physical count
[ ] Update count
[ ] Variance calculated
[ ] Surplus detected
[ ] Shortage detected
[ ] Matched detected
[ ] Invalid physical quantity rejected
[ ] Invalid item rejected
[ ] Invalid location rejected
[ ] Unauthorized warehouse rejected
[ ] Submit opname
[ ] Approve opname
[ ] Reject opname
[ ] Adjustment applied
[ ] Shortage adjustment correct
[ ] Surplus adjustment correct
[ ] Zero variance creates no unnecessary movement
[ ] Duplicate adjustment prevented
[ ] Concurrent adjustment handled
[ ] Audit created
[ ] Completed opname protected
```

---

# 57. Definition of Done

```text
[ ] Opname session
[ ] Warehouse scope
[ ] Location scope
[ ] Item scope
[ ] System snapshot
[ ] Physical count
[ ] Variance calculation
[ ] Variance reason
[ ] Recount support if required
[ ] Review
[ ] Approval
[ ] Adjustment
[ ] Inventory ledger
[ ] Current balance update
[ ] Database transaction
[ ] Concurrency protection
[ ] Duplicate adjustment protection
[ ] Audit
[ ] Query optimization
[ ] Index review
[ ] Tests
[ ] Documentation
```

---

# 58. Final Stock Opname Flow

```text
                         USER
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
                           ┌────────┴────────┐
                           ▼                 ▼
                      PERMISSION          POLICY
                                               │
                                               ▼
                                           WAREHOUSE
                                             SCOPE
                           └─────────┬─────────┘
                                     ▼
                              OPNAME SERVICE
                                     │
                                     ▼
                              CREATE SESSION
                                     │
                                     ▼
                              SYSTEM SNAPSHOT
                                     │
                                     ▼
                              PHYSICAL COUNT
                                     │
                                     ▼
                              CALCULATE VARIANCE
                                     │
                              ┌──────┴──────┐
                              ▼             ▼
                           MATCHED       VARIANCE
                              │             │
                              │             ▼
                              │        REVIEW / APPROVAL
                              │             │
                              └──────┬──────┘
                                     ▼
                                  FINALIZE
                                     │
                                     ▼
                            DATABASE TRANSACTION
                                     │
                                     ▼
                               LOCK INVENTORY
                                     │
                                     ▼
                              CREATE ADJUSTMENT
                                     │
                            ┌────────┴────────┐
                            ▼                 ▼
                         LEDGER          INVENTORY
                            │                 │
                            └────────┬────────┘
                                     ▼
                                   AUDIT
                                     │
                                     ▼
                                 COMPLETED
```

---

# 59. Key Principle

Stock Opname bukan:

```text
"Masukkan stock fisik lalu ubah angka stock."
```

Stock Opname adalah:

```text
System Snapshot
+
Physical Count
+
Variance Analysis
+
Review / Approval
+
Controlled Adjustment
+
Audit Trail
```

Prinsip utama:

```text
COUNT
→ Record physical reality

VARIANCE
→ Difference between physical and system

APPROVAL
→ Authorization for correction

ADJUSTMENT
→ Actual inventory movement

LEDGER
→ Historical record

AUDIT
→ Accountability
```

Dan alur terpenting:

```text
Snapshot
   ↓
Physical Count
   ↓
Variance
   ↓
Review
   ↓
Approval
   ↓
Adjustment
   ↓
Ledger
   ↓
Inventory
   ↓
Audit
```

**Jangan pernah menjadikan hasil input physical count sebagai alasan langsung untuk mengubah inventory.** Perubahan inventory harus terjadi melalui adjustment yang terkontrol dan tercatat di ledger.

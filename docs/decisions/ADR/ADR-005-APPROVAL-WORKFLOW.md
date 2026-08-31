# ADR-005 — Approval Workflow

**Project:** Inventra
**Status:** Accepted
**Date:** 2026-08-30

---

# 1. Context

Beberapa aktivitas inventory Inventra dapat memiliki dampak langsung terhadap stock dan membutuhkan kontrol sebelum diproses.

Contoh:

```text
Stock In
Stock Out
Stock Opname
Adjustment
```

Tidak semua transaksi seharusnya langsung mengubah inventory ketika user membuatnya.

Contoh:

```text
Staff
 ↓
Create Stock Out
 ↓
Stock langsung berkurang
```

Jika transaction tersebut ternyata salah, inventory sudah berubah sebelum diperiksa.

Inventra membutuhkan mekanisme untuk memisahkan:

```text
Membuat transaksi
```

dengan:

```text
Mengesahkan transaksi
```

---

# 2. Problem

Tanpa approval workflow:

```text
Create Transaction
       ↓
Inventory Changed
```

Hal ini dapat menyebabkan:

```text
Incorrect Stock
Unauthorized Transaction
Difficult Investigation
Weak Separation of Duties
```

Karena itu transaction yang membutuhkan approval harus memiliki lifecycle yang jelas.

---

# 3. Decision

Inventra menggunakan **status-based approval workflow**.

Lifecycle utama:

```text
Draft
  ↓
Submitted
  ↓
Approved
  ↓
Processed
```

atau:

```text
Draft
  ↓
Submitted
  ↓
Rejected
```

Konsep:

```text
Create
  ↓
Submit
  ↓
Review
  ↓
Approve / Reject
```

---

# 4. Transaction Lifecycle

Status minimum:

```text
DRAFT
SUBMITTED
APPROVED
REJECTED
CANCELLED
```

Status `PROCESSED` dapat digunakan jika diperlukan untuk membedakan approval dengan execution.

Final status mengikuti kebutuhan module masing-masing.

---

# 5. Draft

Draft merupakan transaction yang masih dapat disiapkan oleh creator.

Contoh:

```text
Stock Out
Status = DRAFT
```

Pada tahap ini:

```text
Inventory
   ↓
Tidak berubah
```

User dapat:

```text
Edit
Add Detail
Remove Detail
Save
```

selama user memiliki permission.

---

# 6. Submit

Ketika transaction siap diperiksa:

```text
DRAFT
  ↓
SUBMIT
  ↓
SUBMITTED
```

Setelah submit, transaction dianggap siap untuk approval.

Validation penting dilakukan sebelum submit.

Contoh:

```text
Item Valid
Warehouse Valid
Quantity Valid
Required Fields Complete
```

---

# 7. Submitted

Pada status `SUBMITTED`:

```text
Creator
   ↓
Tidak boleh melakukan perubahan normal
```

Transaction menunggu reviewer/approver.

Approver dapat:

```text
Review
Approve
Reject
```

---

# 8. Approved

Ketika transaction disetujui:

```text
SUBMITTED
    ↓
APPROVED
```

Approval harus menyimpan informasi:

```text
approved_by
approved_at
```

dan informasi tambahan yang diperlukan untuk audit.

---

# 9. Rejected

Jika transaction ditolak:

```text
SUBMITTED
    ↓
REJECTED
```

Alasan rejection harus disimpan jika business process membutuhkannya.

Contoh:

```text
Rejection Reason:
Quantity tidak sesuai dokumen.
```

Rejected transaction tidak boleh memengaruhi inventory.

---

# 10. Cancelled

Transaction dapat dibatalkan sesuai aturan module.

Contoh:

```text
DRAFT
  ↓
CANCELLED
```

atau workflow tertentu:

```text
SUBMITTED
  ↓
CANCELLED
```

Cancellation harus mengikuti authorization dan dicatat pada audit log.

---

# 11. Inventory Impact

Prinsip utama:

```text
Draft
    ↓
NO Inventory Impact

Submitted
    ↓
NO Inventory Impact

Rejected
    ↓
NO Inventory Impact

Approved
    ↓
Inventory Processing
```

Untuk transaksi yang memang menggunakan approval.

Dengan demikian:

```text
Approval
   ↓
Authorized Business Event
   ↓
Inventory Ledger
   ↓
Inventory Balance
```

---

# 12. Approval and Inventory Ledger

Approval tidak langsung berarti mengubah angka stock dengan query sederhana.

Setelah transaction approved:

```text
Approved Transaction
       ↓
Business Service
       ↓
Database Transaction
       ↓
Inventory Ledger
       ↓
Inventory Balance
       ↓
Audit Log
```

Hal ini menjaga konsistensi dengan:

```text
ADR-003 — Inventory Ledger
```

---

# 13. Atomic Processing

Approval dan inventory processing harus aman terhadap kegagalan.

Contoh:

```text
BEGIN
   ↓
Mark Transaction Approved
   ↓
Create Ledger
   ↓
Update Inventory
   ↓
Create Audit Log
   ↓
COMMIT
```

Jika proses critical gagal:

```text
ROLLBACK
```

Tujuannya mencegah:

```text
Transaction = Approved
BUT
Inventory = Not Updated
```

atau:

```text
Inventory Updated
BUT
Transaction = Not Approved
```

---

# 14. Approval Permission

Approval membutuhkan permission khusus.

Contoh:

```text
stock-in.approve
stock-out.approve
stock-opname.approve
```

User tanpa permission tidak dapat melakukan approval.

Authorization mengikuti:

```text
ADR-004 — RBAC Authorization
```

---

# 15. Separation of Duties

Jika business rule membutuhkan separation of duties:

```text
Creator
   ≠
Approver
```

Contoh:

```text
Staff A
 ↓
Create Stock Out

Manager B
 ↓
Approve
```

Staff A tidak boleh approve transaction yang dibuat sendiri jika aturan tersebut diterapkan pada transaction tersebut.

---

# 16. Warehouse Scope

Approver juga harus memiliki akses terhadap warehouse transaction.

Contoh:

```text
Transaction
Warehouse A

Approver
Permission:
stock-out.approve

Scope:
Warehouse A
```

Result:

```text
ALLOW
```

Jika:

```text
Approver Scope:
Warehouse B
```

Result:

```text
DENY
```

Approval tidak boleh digunakan untuk melewati warehouse authorization.

---

# 17. Approval History

Approval history harus dapat ditelusuri.

Contoh:

```text
Transaction
   ↓
Approval History
   ├── Submitted By
   ├── Submitted At
   ├── Approved By
   ├── Approved At
   └── Rejection Reason
```

Jika workflow berkembang menjadi multi-level approval, struktur harus dapat menampung beberapa approval step.

---

# 18. Multi-Level Approval

V1 Inventra tidak perlu membuat workflow engine yang terlalu kompleks.

Namun architecture harus memungkinkan pengembangan:

```text
Submitted
    ↓
Supervisor Approval
    ↓
Manager Approval
    ↓
Approved
```

Jika kebutuhan tersebut muncul, implementasinya dapat diperluas tanpa mengubah prinsip dasar transaction lifecycle.

---

# 19. Approval Status vs Transaction Status

Approval status dan transaction status dapat dipisahkan jika diperlukan.

Contoh:

```text
Transaction Status:
PROCESSING

Approval Status:
APPROVED
```

Namun untuk module sederhana, satu lifecycle status dapat digunakan agar tidak menambah kompleksitas yang tidak diperlukan.

Prinsipnya:

> Jangan membuat dua status system jika satu status sudah cukup untuk menjelaskan lifecycle.

---

# 20. Re-Submission

Jika transaction ditolak dan business process mengizinkan perbaikan:

```text
REJECTED
   ↓
Edit
   ↓
Submit Again
   ↓
SUBMITTED
```

History rejection sebelumnya tetap dipertahankan.

Jangan menghapus history hanya karena transaction diajukan kembali.

---

# 21. Modification Rules

Aturan umum:

```text
DRAFT
    → Editable

SUBMITTED
    → Locked

APPROVED
    → Locked

REJECTED
    → Editable if allowed

CANCELLED
    → Locked
```

Jika perubahan diperlukan setelah approval, gunakan correction/amendment mechanism, bukan mengubah history approval secara langsung.

---

# 22. Idempotency

Approval endpoint harus mencegah approval diproses dua kali.

Contoh:

```text
Request A
Approve Transaction #100
```

Kemudian request yang sama dikirim kembali.

System harus memastikan:

```text
Ledger
    ↓
Created Once
```

bukan:

```text
Ledger
    ↓
Created Twice
```

---

# 23. Concurrent Approval

Dua approver dapat membuka transaction yang sama.

Contoh:

```text
Approver A → Approve
Approver B → Approve
```

System harus menangani kondisi tersebut sehingga hanya satu proses approval yang menjadi valid.

Database transaction dan concurrency control digunakan sesuai kebutuhan implementasi.

---

# 24. Notification

Approval workflow dapat menghasilkan notification.

Contoh:

```text
Transaction Submitted
       ↓
Notify Approver
```

dan:

```text
Approved
   ↓
Notify Creator
```

Notification merupakan supporting feature dan tidak menjadi source of truth untuk approval state.

---

# 25. Audit

Event penting harus dicatat:

```text
Created
Submitted
Approved
Rejected
Cancelled
Resubmitted
```

Minimal informasi yang relevan:

```text
Actor
Timestamp
Action
Transaction
Reason / Metadata
```

Audit mengikuti:

```text
ADR-008 — Audit Log
```

---

# 26. Alternatives Considered

### Immediate Processing

```text
Create
 ↓
Inventory Change
```

Tidak dipilih untuk transaction yang membutuhkan approval karena tidak memberikan control yang cukup.

---

### Manual Approval Outside System

Contoh:

```text
WhatsApp
Email
Excel
```

Tidak dipilih sebagai source of truth karena approval history dan authorization sulit dijaga secara konsisten.

---

### Full Workflow Engine

Tidak digunakan untuk V1 karena kebutuhan Inventra masih dapat ditangani dengan status-based workflow.

Workflow engine dapat dipertimbangkan jika kebutuhan approval menjadi jauh lebih kompleks.

---

# 27. Consequences

### Positive

```text
+ Controlled inventory changes
+ Clear transaction lifecycle
+ Separation of duties
+ Better auditability
+ Prevents unauthorized approval
+ Supports future multi-level approval
```

### Negative

```text
- More transaction states
- More validation rules
- Requires approval UI
- Requires concurrency handling
- Requires careful state transition design
```

---

# 28. State Transition Rules

Transition harus eksplisit.

Contoh:

```text
DRAFT
 ├── SUBMIT → SUBMITTED
 └── CANCEL → CANCELLED

SUBMITTED
 ├── APPROVE → APPROVED
 ├── REJECT  → REJECTED
 └── CANCEL  → CANCELLED (if allowed)

REJECTED
 ├── EDIT → DRAFT
 └── CANCEL → CANCELLED

APPROVED
 └── PROCESS → inventory impact
```

Tidak boleh sembarang:

```text
DRAFT
 ↓
APPROVED
```

tanpa melalui aturan workflow yang ditentukan.

---

# 29. Implementation Principle

Approval harus diproses melalui business service, bukan langsung dari controller dengan banyak logic.

Konsep:

```text
Controller
    ↓
Approval Service
    ↓
Validate Transition
    ↓
Authorize
    ↓
Database Transaction
    ↓
Update State
    ↓
Inventory Processing
    ↓
Audit
```

---

# 30. Maintenance Guide

Jika transaction tidak dapat di-approve, periksa:

```text
1. Current Status
2. User Authentication
3. Permission
4. Warehouse Scope
5. Policy
6. State Transition
7. Business Validation
8. Concurrency
```

Jika transaction sudah `APPROVED` tetapi stock belum berubah:

```text
1. Check Transaction
2. Check Approval
3. Check Inventory Ledger
4. Check Inventory Balance
5. Check Database Transaction
6. Check Audit Log
```

Jangan langsung mengubah stock secara manual.

---

# 31. Related Decisions

```text
ADR-003 — Inventory Ledger
ADR-004 — RBAC Authorization
ADR-008 — Audit Log
```

Dokumen terkait:

```text
07_PERMISSION_MATRIX.md
06_API.md
10_APPROVAL_WORKFLOW.md
06_STOCK_IN.md
07_STOCK_OUT.md
08_STOCK_OPNAME.md
```

---

# 32. Final Decision

**Accepted**

Inventra menggunakan **status-based approval workflow** untuk transaksi yang membutuhkan persetujuan.

Prinsip utama:

```text
Create
  ↓
Submit
  ↓
Review
  ↓
Approve / Reject
  ↓
Approved Transaction
  ↓
Inventory Processing
  ↓
Ledger + Balance + Audit
```

Approval harus:

```text
Authorized
Traceable
Atomic
Concurrency-safe
```

dan tidak boleh digunakan untuk melewati RBAC, warehouse scope, maupun inventory ledger.

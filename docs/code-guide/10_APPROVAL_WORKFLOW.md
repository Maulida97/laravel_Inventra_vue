# Inventra

## Approval Workflow Code Guide

**Document:** Approval Workflow Code Guide
**Version:** V1.0
**Status:** Draft

---

# 1. Purpose

Approval Workflow digunakan untuk mengontrol transaksi yang membutuhkan persetujuan sebelum dapat dilanjutkan.

Contoh:

```text
Stock Out
Stock Opname Adjustment
Asset Disposal
Asset Transfer
```

Konsep utama:

```text
REQUEST
   ↓
REVIEW
   ↓
APPROVE / REJECT
   ↓
EXECUTE
```

Approval **bukan sekadar mengubah status menjadi APPROVED**.

Approval memberikan authorization bahwa suatu action boleh dilanjutkan.

---

# 2. Core Principle

Pisahkan:

```text
Business Transaction
        +
Approval
        +
Execution
```

Contoh Stock Out:

```text
Stock Out Request
       ↓
Approval
       ↓
Approved
       ↓
Stock Out Execution
       ↓
Inventory Ledger
```

Jangan:

```text
User Submit
 ↓
Update Stock
 ↓
Approval
```

Karena stock sudah berubah sebelum approval.

---

# 3. Generic Approval Flow

```text
DRAFT
  ↓
SUBMITTED
  ↓
PENDING_APPROVAL
  ↓
┌───────────────┐
│               │
▼               ▼
APPROVED      REJECTED
  │
  ▼
EXECUTED
  │
  ▼
COMPLETED
```

Tidak semua modul membutuhkan semua status.

---

# 4. Approval Status

Minimal:

```text
DRAFT
PENDING_APPROVAL
APPROVED
REJECTED
CANCELLED
```

Jika membutuhkan execution:

```text
EXECUTED
COMPLETED
```

---

# 5. Approval vs Transaction Status

Jangan mencampur:

```text
approval_status
```

dengan:

```text
transaction_status
```

Contoh:

```text
Stock Out

transaction_status:
PENDING

approval_status:
APPROVED
```

Artinya approval sudah selesai tetapi transaksi belum dieksekusi.

Model ini lebih aman untuk transaksi kompleks.

---

# 6. Simple Model

Untuk workflow sederhana:

```text
transaction.status
```

dapat digunakan:

```text
DRAFT
PENDING_APPROVAL
APPROVED
REJECTED
COMPLETED
```

Namun untuk workflow yang berkembang, gunakan:

```text
transaction.status
+
approval status
```

Keputusan final mengikuti kebutuhan modul.

---

# 7. Approval Request

Conceptual:

```text
approval_requests
├── id
├── requestable_type
├── requestable_id
├── status
├── submitted_by
├── submitted_at
├── decided_by
├── decided_at
├── decision_note
├── created_at
└── updated_at
```

Polymorphic reference dapat digunakan agar satu approval system dapat digunakan beberapa modul.

Contoh:

```text
requestable_type:
StockOut

requestable_id:
123
```

atau:

```text
requestable_type:
AssetDisposal

requestable_id:
50
```

---

# 8. Approval History

Jangan hanya menyimpan keputusan terakhir.

Simpan history:

```text
approval_actions
├── id
├── approval_request_id
├── action
├── actor_id
├── note
└── created_at
```

Contoh:

```text
SUBMITTED
APPROVED
```

atau:

```text
SUBMITTED
REJECTED
RESUBMITTED
APPROVED
```

---

# 9. Approval Action

Minimal:

```text
SUBMIT
APPROVE
REJECT
CANCEL
```

Jika diperlukan:

```text
RETURN
REQUEST_CHANGES
```

---

# 10. Submit

Flow:

```text
DRAFT
 ↓
Validate
 ↓
Authorize
 ↓
SUBMIT
 ↓
PENDING_APPROVAL
```

Saat submit:

```text
submitted_by
submitted_at
```

dicatat.

---

# 11. Approve

Flow:

```text
PENDING_APPROVAL
 ↓
Validate Approver
 ↓
Approve
 ↓
APPROVED
```

Sistem harus memastikan:

```text
Request masih pending
Approver memiliki permission
Approver memiliki scope
Approver tidak melanggar separation of duties
```

---

# 12. Reject

Flow:

```text
PENDING_APPROVAL
 ↓
Reject
 ↓
REJECTED
```

Reject sebaiknya memiliki alasan.

Contoh:

```text
decision_note:
"Quantity tidak sesuai dokumen."
```

---

# 13. Resubmission

Jika business rule mengizinkan:

```text
REJECTED
 ↓
Edit
 ↓
RESUBMIT
 ↓
PENDING_APPROVAL
```

Approval history tetap:

```text
SUBMITTED
REJECTED
RESUBMITTED
APPROVED
```

Jangan menghapus history sebelumnya.

---

# 14. Request Changes

Alternatif reject:

```text
PENDING_APPROVAL
 ↓
REQUEST_CHANGES
 ↓
DRAFT / REVISION_REQUIRED
```

User dapat memperbaiki data lalu submit ulang.

Ini cocok untuk transaksi yang sering memerlukan koreksi.

---

# 15. Approval Levels

Untuk V1, gunakan single-level approval jika cukup.

```text
Requester
   ↓
Approver
```

Jika dibutuhkan:

```text
Requester
   ↓
Supervisor
   ↓
Manager
```

atau:

```text
Level 1
 ↓
Level 2
 ↓
Level 3
```

Jangan membuat multi-level approval kompleks jika business requirement belum membutuhkannya.

---

# 16. Approval Rule

Conceptual:

```text
Approval Rule
├── module
├── threshold
├── required_role
├── level
└── active
```

Contoh:

```text
Stock Out
< 100 units
→ Supervisor

Stock Out
>= 100 units
→ Manager
```

Rule harus configurable hanya jika memang diperlukan.

---

# 17. Approver Resolution

Sistem perlu menentukan siapa yang boleh approve.

Contoh:

```text
Transaction
 ↓
Warehouse
 ↓
Department
 ↓
Role
 ↓
Approver
```

Jangan mengambil approver dari input frontend.

Backend menentukan approver berdasarkan rule.

---

# 18. Authorization

Approval harus menggunakan:

```text
Authentication
+
Permission
+
Policy
+
Scope
+
Workflow State
```

Contoh:

```text
stock-out.approve
```

belum cukup.

Sistem juga memeriksa:

```text
User memiliki akses warehouse tersebut?
Transaction masih pending?
User boleh approve transaksi ini?
User adalah requester yang sama?
```

---

# 19. Separation of Duties

Prinsip penting:

```text
Requester
≠
Approver
```

Contoh:

```text
Budi
→ Create Stock Out

Andi
→ Approve Stock Out
```

Budi tidak boleh approve request yang dibuat sendiri jika business rule mengharuskan separation of duties.

---

# 20. Self Approval Protection

Backend:

```text
if requester_id === approver_id:
    reject
```

Tetapi jangan hanya mengandalkan frontend untuk menyembunyikan button.

Authorization harus berada di backend.

---

# 21. Approval Policy

Concept:

```text
ApprovalPolicy
├── submit()
├── approve()
├── reject()
├── cancel()
└── resubmit()
```

Policy bertanggung jawab terhadap:

```text
Who can perform the action?
```

Service bertanggung jawab terhadap:

```text
How is the action executed?
```

---

# 22. Controller Responsibility

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

Contoh:

```text
ApprovalController
 ↓
approve()
 ↓
ApprovalService
```

Controller jangan menangani seluruh workflow.

---

# 23. Approval Service

Concept:

```text
ApprovalService
├── submit()
├── approve()
├── reject()
├── cancel()
└── resubmit()
```

Contoh:

```text
approve()
 ↓
Validate state
 ↓
Resolve approver
 ↓
Check permission
 ↓
Check SoD
 ↓
Update approval
 ↓
Create history
 ↓
Audit
```

---

# 24. Execution After Approval

Approval tidak selalu berarti langsung execute.

Contoh:

```text
APPROVED
   ↓
Execution Service
   ↓
Inventory Movement
```

Untuk Stock Out:

```text
ApprovalService
      ↓
APPROVED
      ↓
StockOutService::execute()
      ↓
Inventory Ledger
```

Untuk Asset Disposal:

```text
ApprovalService
      ↓
APPROVED
      ↓
AssetDisposalService::execute()
      ↓
DISPOSED
```

---

# 25. Transaction Boundary

Approval update harus transaction-safe.

```text
BEGIN
 ↓
Validate State
 ↓
Update Approval
 ↓
Create Approval History
 ↓
Create Audit
COMMIT
```

Execution transaction terpisah jika memang dilakukan setelah approval.

---

# 26. Approval + Execution Atomicity

Jika business rule mengharuskan approval langsung melakukan execution:

```text
BEGIN
 ↓
Approve
 ↓
Execute
 ↓
Ledger / Asset Update
 ↓
Approval History
 ↓
Audit
COMMIT
```

Jika execution gagal:

```text
ROLLBACK
```

Approval tidak boleh terlihat sukses sementara execution gagal.

---

# 27. Idempotency

Request approve dapat terkirim dua kali.

Contoh:

```text
User click Approve
 ↓
Network retry
 ↓
Approve lagi
```

Sistem harus menolak duplicate action.

Check:

```text
status === PENDING_APPROVAL
```

Jika sudah:

```text
APPROVED
```

maka request kedua ditolak atau dianggap idempotent sesuai API design.

---

# 28. Concurrency

Dua approver dapat membuka halaman yang sama.

```text
Approver A → Approve
Approver B → Approve
```

Hanya satu yang boleh berhasil.

Gunakan:

```text
Database Transaction
+
Row Lock
+
State Validation
```

Concept:

```php
$approval = ApprovalRequest::query()
    ->whereKey($id)
    ->lockForUpdate()
    ->first();
```

Kemudian:

```text
if status != PENDING_APPROVAL
    reject
```

---

# 29. Approval Notification

Jika notification digunakan:

```text
Submit
 ↓
Notify Approver
```

Setelah approval:

```text
Approve
 ↓
Notify Requester
```

Setelah reject:

```text
Reject
 ↓
Notify Requester
```

Notification tidak boleh menjadi sumber kebenaran workflow.

Database tetap source of truth.

---

# 30. Approval UI

Concept:

```text
Approval Panel

Request:
SO-2026-00001

Requester:
Budi

Warehouse:
WH-001

Status:
PENDING APPROVAL

Summary:
...

[ Approve ]
[ Reject ]
```

Button hanya ditampilkan jika user memiliki authorization.

Tetapi backend tetap melakukan authorization.

---

# 31. Approval Timeline

Detail page dapat menampilkan:

```text
2026-08-30 09:00
Budi
Submitted

2026-08-30 10:15
Andi
Approved
```

Jika reject:

```text
2026-08-30 10:15
Andi
Rejected

Reason:
Quantity tidak sesuai.
```

---

# 32. Approval UI Structure

Concept:

```text
resources/js/Components/Approval/
├── ApprovalPanel.vue
├── ApprovalTimeline.vue
├── ApprovalStatus.vue
├── ApprovalActions.vue
└── ApprovalHistory.vue
```

Dengan demikian approval component dapat digunakan oleh:

```text
Stock Out
Stock Opname
Asset Disposal
```

---

# 33. Backend Structure

Concept:

```text
app/
├── Models/
│   ├── ApprovalRequest.php
│   └── ApprovalAction.php
│
├── Services/
│   └── Approval/
│       └── ApprovalService.php
│
├── Policies/
│   └── ApprovalPolicy.php
│
└── Http/
    ├── Controllers/
    │   └── ApprovalController.php
    └── Requests/
        ├── ApproveRequest.php
        └── RejectRequest.php
```

Struktur final mengikuti arsitektur project.

---

# 34. Generic Approval vs Module Logic

Approval system sebaiknya generic.

```text
ApprovalService
```

menangani:

```text
Submit
Approve
Reject
History
Authorization
```

Sedangkan module service menangani:

```text
StockOutService
StockOpnameService
AssetDisposalService
```

Contoh:

```text
Stock Out
   ↓
ApprovalService
   ↓
StockOutService
```

---

# 35. Do Not Put Business Logic in ApprovalService

Jangan:

```text
ApprovalService
 ↓
if StockOut
   update inventory

if AssetDisposal
   dispose asset

if StockOpname
   adjust inventory
```

Ini membuat service menjadi terlalu besar.

Lebih baik:

```text
Approval
 ↓
Module-specific execution
```

---

# 36. State Transition Validation

Setiap workflow memiliki allowed transition.

Contoh:

```text
DRAFT
 → SUBMITTED

PENDING_APPROVAL
 → APPROVED
 → REJECTED

REJECTED
 → RESUBMITTED

APPROVED
 → EXECUTED
```

Tidak boleh:

```text
COMPLETED
 → APPROVED
```

atau:

```text
REJECTED
 → EXECUTED
```

---

# 37. Approval Matrix

Conceptual:

| Action  | Permission     | State         | Separation |
| ------- | -------------- | ------------- | ---------- |
| Submit  | module.submit  | DRAFT         | -          |
| Approve | module.approve | PENDING       | Required   |
| Reject  | module.approve | PENDING       | Required   |
| Cancel  | module.cancel  | DRAFT/PENDING | Policy     |

Matrix final mengikuti `07_PERMISSION_MATRIX.md`.

---

# 38. Security

Approval Workflow harus mencegah:

```text
Unauthorized approval
Self approval
Approval after completion
Duplicate approval
Approval outside warehouse scope
Approval outside department scope
Forged approval request
Tampering with approval history
Unauthorized execution
```

---

# 39. Common Mistakes

### Mistake 1 — Approval hanya frontend

```text
Hide Approve Button
```

tidak cukup.

Backend harus authorize.

---

### Mistake 2 — Approver berasal dari request frontend

Jangan:

```text
approver_id = request->approver_id
```

tanpa validasi.

---

### Mistake 3 — Self approval

Requester tidak otomatis boleh approve request sendiri.

---

### Mistake 4 — Menghapus rejection history

History harus immutable.

---

### Mistake 5 — Approval langsung update banyak tabel tanpa transaction

Gunakan database transaction.

---

### Mistake 6 — Generic approval service terlalu pintar

Jangan memasukkan semua business logic modul ke satu service.

---

# 40. Maintenance Guide

### "Saya mau mengubah tampilan approval."

Cari:

```text
resources/js/Components/Approval/
```

---

### "Saya mau mengubah siapa yang boleh approve."

Periksa:

```text
Policy
+
Permission
+
Approval Rule
```

---

### "Saya mau mengubah alur approve."

Cari:

```text
ApprovalService::approve()
```

---

### "Saya mau mengubah apa yang terjadi setelah approve."

Jangan langsung mengubah `ApprovalService`.

Cari module-specific execution:

```text
StockOutService
StockOpnameService
AssetDisposalService
```

---

### "Approve berhasil tapi transaksi tidak berubah."

Periksa:

```text
[ ] Approval status
[ ] Execution service
[ ] Transaction
[ ] Module state
[ ] Ledger / Asset update
[ ] Exception
[ ] Audit
```

---

### "User bisa approve request sendiri."

Periksa:

```text
[ ] requester_id
[ ] approver_id
[ ] Policy
[ ] Separation of duties
[ ] Backend authorization
```

---

# 41. Code Reading Flow

Untuk memahami approval:

```text
Approval Button
 ↓
Route
 ↓
Controller
 ↓
Policy
 ↓
Form Request
 ↓
ApprovalService
 ↓
Approval Model
 ↓
Approval History
 ↓
Audit
```

Untuk memahami apa yang terjadi setelah approval:

```text
Approve
 ↓
ApprovalService
 ↓
APPROVED
 ↓
Module Execution Service
 ↓
Business Transaction
```

Contoh Stock Out:

```text
Approve
 ↓
ApprovalService
 ↓
APPROVED
 ↓
StockOutService
 ↓
Inventory Ledger
 ↓
Inventory Balance
 ↓
Audit
```

---

# 42. Debugging Checklist

Jika user tidak bisa approve:

```text
[ ] Authentication
[ ] Permission
[ ] Policy
[ ] Warehouse scope
[ ] Department scope
[ ] Workflow state
[ ] Self approval
```

Jika approval berubah dua kali:

```text
[ ] State validation
[ ] Row lock
[ ] Transaction
[ ] Duplicate request
```

Jika approval berhasil tetapi business transaction gagal:

```text
[ ] Execution service
[ ] Transaction boundary
[ ] Module validation
[ ] Inventory / Asset state
[ ] Exception
```

---

# 43. Testing

Minimal:

```text
[ ] Submit approval
[ ] Approve valid request
[ ] Reject valid request
[ ] Reject requires reason
[ ] Unauthorized approval rejected
[ ] Self approval rejected
[ ] Invalid state transition rejected
[ ] Duplicate approval prevented
[ ] Concurrent approval handled
[ ] Approval history created
[ ] Audit created
[ ] Resubmission works if enabled
[ ] Correct module execution after approval
[ ] Execution failure handled
```

---

# 44. Definition of Done

```text
[ ] Approval request
[ ] Submit workflow
[ ] Approve
[ ] Reject
[ ] Optional resubmit
[ ] Approval history
[ ] Approval policy
[ ] Permission integration
[ ] Scope validation
[ ] Separation of duties
[ ] State transition validation
[ ] Database transaction
[ ] Concurrency protection
[ ] Idempotency / duplicate protection
[ ] Audit
[ ] Reusable frontend components
[ ] Reusable approval service
[ ] Module-specific execution
[ ] Tests
[ ] Documentation
```

---

# 45. Final Approval Architecture

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
                  ┌──────┴──────┐
                  ▼             ▼
             VALIDATION     AUTHORIZATION
                                │
                         ┌──────┴──────┐
                         ▼             ▼
                      POLICY       PERMISSION
                                        │
                                        ▼
                                  SCOPE CHECK
                                        │
                                        ▼
                                  APPROVAL SERVICE
                                        │
                             ┌──────────┴──────────┐
                             ▼                     ▼
                          APPROVE                REJECT
                             │                     │
                             ▼                     ▼
                         APPROVED              REJECTED
                             │
                             ▼
                     MODULE EXECUTION
                             │
              ┌──────────────┼──────────────┐
              ▼              ▼              ▼
          STOCK OUT      STOCK OPNAME    ASSET DISPOSAL
              │              │              │
              ▼              ▼              ▼
           LEDGER         ADJUSTMENT      ASSET UPDATE
              │              │              │
              └──────────────┼──────────────┘
                             ▼
                           AUDIT
```

---

# 46. Key Principle

Approval Workflow di Inventra mengikuti prinsip:

```text
REQUEST
   ↓
VALIDATE
   ↓
AUTHORIZE
   ↓
APPROVE / REJECT
   ↓
EXECUTE
   ↓
AUDIT
```

Yang paling penting untuk dipahami ketika maintenance:

```text
Policy
→ Siapa yang boleh?

ApprovalService
→ Bagaimana approval diproses?

Module Service
→ Apa yang terjadi setelah approved?

Transaction
→ Bagaimana menjaga konsistensi?

Audit
→ Siapa melakukan apa dan kapan?
```

Jadi jika nanti kamu ditanya:

> "Kenapa setelah approve Stock Out stock berubah?"

Kamu bisa mengikuti code flow:

```text
StockOut/Show.vue
 ↓
Approve Action
 ↓
Approval Controller
 ↓
Approval Policy
 ↓
ApprovalService
 ↓
StockOut Execution
 ↓
Inventory Ledger
 ↓
Inventory Balance
 ↓
Audit Log
```

Bukan mencari-cari semua file sampai bingung.

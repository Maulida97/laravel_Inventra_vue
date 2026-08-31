# Inventra

## Sprint 10 — Approval Workflow

**Sprint:** SPRINT-10
**Name:** Approval Workflow
**Project:** Inventra
**Status:** Planned
**Branch:** `feature/approval-workflow`

---

# 1. Sprint Overview

Approval Workflow menyediakan mekanisme persetujuan untuk transaksi atau aktivitas yang membutuhkan otorisasi tambahan.

Contoh penggunaan:

```text
Stock Opname
    ↓
Submit
    ↓
Approval
    ↓
Post
```

```text
Asset Disposal
    ↓
Submit
    ↓
Approval
    ↓
Dispose
```

Approval dibuat sebagai **reusable system** sehingga fitur lain dapat menggunakannya.

---

# 2. Objective

Membangun workflow yang dapat:

- Membuat approval request.
- Menentukan requester.
- Menentukan approver.
- Menyimpan status approval.
- Approve.
- Reject.
- Menyimpan alasan rejection.
- Menyimpan timestamp.
- Mencegah unauthorized approval.
- Mencegah self-approval jika business rule melarang.
- Menyimpan approval history.
- Menyediakan audit trail.
- Digunakan oleh berbagai module.

---

# 3. Scope

### Included

```text
Approval Request
Approval Status
Approver
Approval Action
Reject Reason
Approval History
Authorization
Audit
Reusable Workflow
```

### Not Included

```text
Multi-level Approval Engine
Dynamic Approval Rules
Approval by Email
External Approval
Digital Signature
Complex Delegation
```

Fitur tersebut dapat dikembangkan kemudian.

---

# 4. Core Principle

Approval bukan sekadar:

```text
approved = true
```

Tetapi:

```text
REQUEST
   ↓
SUBMITTED
   ↓
PENDING APPROVAL
   ↓
APPROVED / REJECTED
```

Setiap perubahan harus memiliki:

```text
Who
When
Action
Reason
```

---

# 5. Generic Approval Concept

Approval harus dapat digunakan oleh:

```text
Stock Opname
Asset Disposal
Future Transactions
```

Concept:

```text
Business Entity
      ↓
Approval Request
      ↓
Approver
      ↓
Decision
      ↓
Business Action
```

Contoh:

```text
Stock Opname
      ↓
Approval
      ↓
Approved
      ↓
Post Stock Adjustment
```

---

# 6. Approval Status

Gunakan status:

```text
PENDING
APPROVED
REJECTED
CANCELLED
```

Lifecycle:

```text
PENDING
   ├──► APPROVED
   ├──► REJECTED
   └──► CANCELLED
```

Status terminal:

```text
APPROVED
REJECTED
CANCELLED
```

Tidak dapat diubah secara normal.

---

# 7. Approval Request Structure

Concept:

```text
approval_requests
├── id
├── approvable_type
├── approvable_id
├── requester_id
├── status
├── submitted_at
├── decided_at
├── decided_by
├── rejection_reason
├── created_at
└── updated_at
```

`approvable_type` dan `approvable_id` memungkinkan approval digunakan oleh beberapa module.

Contoh:

```text
approvable_type = StockOpname
approvable_id   = 15
```

atau:

```text
approvable_type = AssetDisposal
approvable_id   = 20
```

---

# 8. Approval History

Jangan hanya menyimpan status terakhir.

Sediakan history:

```text
approval_histories
├── id
├── approval_request_id
├── action
├── actor_id
├── reason
└── created_at
```

Contoh:

```text
PENDING
   ↓
SUBMITTED
   ↓
REJECTED
   ↓
RESUBMITTED
   ↓
APPROVED
```

Jika business rule memungkinkan resubmission, seluruh history tetap tersedia.

---

# 9. Approval Actions

Contoh:

```text
SUBMITTED
APPROVED
REJECTED
CANCELLED
```

Setiap action memiliki:

```text
Actor
Timestamp
Reason
```

Untuk rejection:

```text
Reason = required
```

---

# 10. Requester

Requester adalah user yang mengajukan approval.

Contoh:

```text
Requester:
Warehouse Staff

Approver:
Warehouse Manager
```

Sistem harus mengetahui:

```text
Siapa yang meminta?
```

---

# 11. Approver

Approver adalah user yang mempunyai permission untuk memberikan keputusan.

Contoh:

```text
Warehouse Manager
```

Tetapi permission harus berasal dari:

```text
RBAC
+
Business Rule
+
Warehouse Scope
```

Bukan hanya role name.

---

# 12. Self Approval

Untuk V1:

```text
Requester != Approver
```

Contoh:

```text
Budi membuat Stock Opname
```

Budi tidak dapat:

```text
Approve Stock Opname miliknya sendiri
```

Ini mengurangi risiko approval tanpa separation of duties.

---

# 13. Approval Permission

Minimal:

```text
approval.view
approval.approve
approval.reject
approval.cancel
```

Module-specific permission tetap dapat diterapkan.

Contoh:

```text
stock-opname.approve
asset-disposal.approve
```

Approval engine tidak boleh otomatis memberikan permission ke semua entity.

---

# 14. Warehouse Scope

Approval harus mengikuti scope entity.

Contoh:

```text
Stock Opname
Warehouse A
```

Approver hanya dapat approve jika memiliki akses:

```text
Warehouse A
```

Flow:

```text
User
 ↓
Approval Permission
 ↓
Entity Permission
 ↓
Warehouse Scope
 ↓
Approve
```

---

# 15. Approval Request Flow

```text
Business Feature
       ↓
Create Approval Request
       ↓
PENDING
       ↓
Approver opens request
       ↓
Approve / Reject
       ↓
Business Feature continues
```

Approval engine tidak seharusnya melakukan seluruh business operation sendiri.

---

# 16. Important Separation

Approval:

```text
"Apakah tindakan ini disetujui?"
```

Business service:

```text
"Bagaimana tindakan tersebut dijalankan?"
```

Contoh:

```text
ApprovalService
→ approve request
```

Sedangkan:

```text
StockOpnameService
→ apply stock adjustment
```

Ini membuat architecture lebih mudah dipahami.

---

# 17. Approval + Stock Opname

Flow:

```text
Stock Opname
 ↓
Submit
 ↓
Approval Request
 ↓
PENDING
 ↓
Manager Approves
 ↓
APPROVED
 ↓
StockOpnameService
 ↓
POST
 ↓
Ledger
```

Approval tidak langsung mengubah stock.

---

# 18. Approval + Asset Disposal

Flow:

```text
Asset Disposal
 ↓
Submit
 ↓
Approval Request
 ↓
PENDING
 ↓
Manager Approves
 ↓
APPROVED
 ↓
AssetService
 ↓
DISPOSED
```

---

# 19. Reject Flow

```text
Submit
 ↓
PENDING
 ↓
Reject
 ↓
REJECTED
```

Rejection harus memiliki alasan.

Contoh:

```text
"Physical count needs verification."
```

---

# 20. Rejection Handling

Business entity tidak boleh dianggap approved.

Contoh:

```text
Stock Opname
status = SUBMITTED

Approval
status = REJECTED
```

Stock Opname:

```text
cannot POST
```

User dapat:

```text
Edit
↓
Resubmit
```

jika business rule mengizinkan.

---

# 21. Resubmission

V1 dapat mendukung:

```text
REJECTED
   ↓
EDIT
   ↓
RESUBMIT
   ↓
PENDING
```

Approval history tetap:

```text
REQUESTED
REJECTED
RESUBMITTED
APPROVED
```

Jangan menghapus history sebelumnya.

---

# 22. Cancel Flow

Request dapat dibatalkan sebelum keputusan:

```text
PENDING
 ↓
CANCELLED
```

Requester dapat melakukan cancel jika memiliki permission.

Approver tidak otomatis dapat cancel kecuali permission diberikan.

---

# 23. Terminal State Protection

Jika:

```text
APPROVED
```

maka:

```text
REJECT
```

harus ditolak.

Jika:

```text
REJECTED
```

maka:

```text
APPROVE
```

harus ditolak.

Jika:

```text
CANCELLED
```

maka:

```text
APPROVE
```

harus ditolak.

---

# 24. Atomic Approval

Approval harus menggunakan transaction jika approval memicu perubahan entity.

Contoh:

```text
BEGIN TRANSACTION
       ↓
Lock Approval Request
       ↓
Validate Status
       ↓
Validate Permission
       ↓
Approve
       ↓
Update Business Entity
       ↓
Create Approval History
       ↓
Audit
       ↓
COMMIT
```

Jika gagal:

```text
ROLLBACK
```

---

# 25. Concurrency

Kasus:

```text
Manager A → Approve
Manager B → Approve
```

Tidak boleh keduanya melakukan approval sebagai dua action terpisah.

Gunakan:

```text
Transaction
+
Row Lock
+
Status Check
```

Flow:

```text
Lock
 ↓
status = PENDING?
 ↓
YES
 ↓
Approve
```

Request kedua:

```text
status != PENDING
 ↓
Reject
```

---

# 26. Idempotency

Jika browser mengirim request dua kali:

```text
POST /approval/123/approve
POST /approval/123/approve
```

hanya satu yang boleh berhasil sebagai approval action.

Protection:

```text
Idempotency
+
Status Validation
+
Transaction
```

---

# 27. Approval Service

Structure:

```text
app/Services/Approval/
└── ApprovalService.php
```

Responsibilities:

```text
Create Request
Submit
Approve
Reject
Cancel
Validate Transition
Create History
```

Tidak bertanggung jawab terhadap:

```text
Stock Adjustment
Asset Disposal
```

Business service masing-masing tetap menangani logic tersebut.

---

# 28. Approval Controller

```text
ApprovalController
```

menangani:

```text
HTTP Request
 ↓
Authorization
 ↓
Validation
 ↓
ApprovalService
 ↓
Response
```

Jangan memasukkan business logic panjang ke controller.

---

# 29. Frontend Structure

```text
resources/js/
├── Pages/
│   └── Approvals/
│       ├── Index.vue
│       └── Show.vue
│
└── Components/
    └── Approval/
        ├── ApprovalStatusBadge.vue
        ├── ApprovalTimeline.vue
        ├── ApprovalActions.vue
        └── RejectionDialog.vue
```

---

# 30. Approval Inbox

Halaman:

```text
Approvals
```

menampilkan request yang dapat diproses user.

Contoh:

```text
┌─────────────────────────────────────┐
│ Approval Inbox                      │
├─────────────────────────────────────┤
│ Stock Opname   SO-001   PENDING     │
│ Asset Disposal AST-002 PENDING      │
└─────────────────────────────────────┘
```

---

# 31. Approval Detail

Menampilkan:

```text
Request
Requester
Entity
Warehouse
Status
Submitted At
Timeline
```

Action:

```text
Approve
Reject
```

---

# 32. Approval Timeline

Contoh:

```text
2026-08-30 09:00
Budi
Submitted

2026-08-30 10:30
Andi
Rejected

Reason:
Physical count needs verification.

2026-08-30 13:00
Budi
Resubmitted

2026-08-30 14:00
Andi
Approved
```

Timeline membantu developer maupun user memahami lifecycle.

---

# 33. Backend Security

Approval harus dilindungi dari:

```text
IDOR
Unauthorized Approval
Self Approval
Warehouse Scope Bypass
Mass Assignment
Status Manipulation
Duplicate Approval
History Tampering
```

---

# 34. IDOR Protection

Request:

```text
POST /approvals/123/approve
```

harus melakukan:

```text
Authentication
 ↓
Permission
 ↓
Approval Policy
 ↓
Approver Scope
 ↓
Entity Scope
 ↓
Status Validation
 ↓
Approve
```

---

# 35. Mass Assignment Protection

User tidak boleh mengirim:

```json
{
  "status": "APPROVED"
}
```

untuk memaksa approval.

Status hanya boleh berubah melalui:

```text
ApprovalService
```

---

# 36. Frontend Security Principle

Button:

```text
Approve
```

boleh disembunyikan berdasarkan permission.

Tetapi:

```text
Hidden Button != Security
```

Backend tetap harus memvalidasi semuanya.

---

# 37. Database Index

Potential:

```text
approval_requests.status
approval_requests.requester_id
approval_requests.decided_by
approval_requests.approvable_type
approval_requests.approvable_id

approval_histories.approval_request_id
approval_histories.actor_id
approval_histories.created_at
```

Untuk query:

```text
Pending approvals
```

index:

```text
status
```

dapat membantu.

Gunakan:

```text
EXPLAIN
```

untuk memverifikasi query performance.

---

# 38. Database Constraints

Minimal:

```text
approval_requests.requester_id
→ FOREIGN KEY

approval_requests.decided_by
→ FOREIGN KEY

approval_histories.approval_request_id
→ FOREIGN KEY

approval_histories.actor_id
→ FOREIGN KEY
```

Combination:

```text
approvable_type
+
approvable_id
```

harus dapat mengidentifikasi business entity secara jelas.

---

# 39. Maintenance Guide

### "Saya ingin mengubah tampilan approval."

Cari:

```text
resources/js/Pages/Approvals/
```

---

### "Saya ingin mengubah tombol Approve."

Cari:

```text
ApprovalActions.vue
```

---

### "Saya ingin mengubah siapa yang boleh approve."

Trace:

```text
ApprovalController
 ↓
ApprovalPolicy
 ↓
RBAC
 ↓
Warehouse Scope
```

---

### "Approval berhasil tapi Stock Opname tidak berubah."

Trace:

```text
ApprovalService
 ↓
Approval status
 ↓
StockOpnameService
 ↓
Post
 ↓
Inventory Ledger
```

---

### "User bisa approve request miliknya sendiri."

Periksa:

```text
ApprovalPolicy
 ↓
Requester ID
 ↓
Current User ID
```

---

### "Approval bisa dilakukan dua kali."

Periksa:

```text
Transaction
 ↓
Lock
 ↓
Status Check
 ↓
Idempotency
```

---

# 40. Code Understanding Map

```text
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
ApprovalController
 ↓
ApprovalPolicy
 ↓
ApprovalService
 ↓
Database Transaction
 ├── Approval Request
 ├── Approval History
 └── Audit Log
 ↓
Business Service
 ├── StockOpnameService
 └── AssetService
 ↓
Business Result
```

---

# 41. Example — Stock Opname

```text
StockOpnameController
 ↓
Submit
 ↓
StockOpnameService
 ↓
Create Approval Request
 ↓
PENDING
```

Manager:

```text
ApprovalController
 ↓
Approve
 ↓
ApprovalService
 ↓
APPROVED
 ↓
StockOpnameService
 ↓
POST
 ↓
Ledger
 ↓
Balance
```

---

# 42. Example — Asset Disposal

```text
AssetService
 ↓
Request Disposal
 ↓
Approval Request
 ↓
PENDING
```

Manager:

```text
ApprovalService
 ↓
APPROVE
 ↓
AssetService
 ↓
DISPOSED
 ↓
Asset History
 ↓
Audit
```

---

# 43. Testing

### Approval Creation

```text
[ ] Approval request can be created
[ ] Requester stored correctly
[ ] Entity stored correctly
[ ] Initial status = PENDING
```

### Approval

```text
[ ] Authorized approver can approve
[ ] Unauthorized user cannot approve
[ ] Requester cannot self-approve
[ ] Approved timestamp stored
[ ] Approver stored
```

### Rejection

```text
[ ] Authorized approver can reject
[ ] Rejection reason required
[ ] Rejection timestamp stored
[ ] Reject reason stored
```

### Transition

```text
[ ] PENDING → APPROVED
[ ] PENDING → REJECTED
[ ] PENDING → CANCELLED
[ ] APPROVED cannot be rejected
[ ] REJECTED cannot be approved
[ ] CANCELLED cannot be approved
```

### Resubmission

```text
[ ] Rejected request can be resubmitted
[ ] Previous history retained
[ ] New approval request state is PENDING
```

### Security

```text
[ ] IDOR blocked
[ ] Permission enforced
[ ] Warehouse scope enforced
[ ] Self approval blocked
[ ] Mass assignment blocked
```

### Concurrency

```text
[ ] Concurrent approvals handled safely
[ ] Duplicate approval rejected
[ ] Transaction rollback works
```

---

# 44. Acceptance Criteria

Sprint selesai apabila:

```text
1. Approval request dapat dibuat.

2. Requester tercatat.

3. Approver dapat ditentukan berdasarkan permission/scope.

4. Status approval tersedia.

5. Approve tersedia.

6. Reject tersedia.

7. Reject membutuhkan reason.

8. Cancel tersedia sesuai permission.

9. Approval history tersedia.

10. Approval tidak dapat dilakukan oleh unauthorized user.

11. Self approval diblokir.

12. Warehouse scope diterapkan.

13. IDOR protection tersedia.

14. Mass assignment protection tersedia.

15. Invalid status transition ditolak.

16. Concurrent approval ditangani.

17. Duplicate approval terlindungi.

18. Approval menggunakan transaction jika memicu business action.

19. Audit log tersedia.

20. Stock Opname dapat menggunakan approval.

21. Asset Disposal dapat menggunakan approval.

22. Approval engine tidak mengambil alih business logic module.

23. Database constraints tersedia.

24. Index relevan tersedia.

25. Automated tests berhasil.

26. Code documentation mengikuti standard Inventra.

27. Developer dapat tracing Approval dari Vue → Laravel → Approval Service → Business Service → Database.
```

---

# 45. Expected Files

```text
app/
├── Models/
│   ├── ApprovalRequest.php
│   └── ApprovalHistory.php
│
├── Http/
│   ├── Controllers/
│   │   └── ApprovalController.php
│   │
│   └── Requests/
│       └── Approval/
│
├── Policies/
│   └── ApprovalPolicy.php
│
└── Services/
    └── Approval/
        └── ApprovalService.php

database/
└── migrations/
    ├── xxxx_create_approval_requests_table.php
    └── xxxx_create_approval_histories_table.php

resources/js/
├── Pages/
│   └── Approvals/
│
└── Components/
    └── Approval/

tests/
└── Feature/
    └── Approval/
```

---

# 46. Code Documentation

Setiap file mengikuti:

```text
docs/code-guide/00_CODE_DOCUMENTATION_STANDARD.md
```

Contoh:

```php
/**
 * Approval Service
 *
 * Purpose:
 * Handle reusable approval lifecycle.
 *
 * Main Flow:
 * Create
 * → Pending
 * → Approve / Reject
 * → History
 * → Audit
 *
 * Important:
 * ApprovalService handles approval state.
 *
 * Business services remain responsible for
 * the actual business operation.
 *
 * Related:
 * - ApprovalRequest
 * - ApprovalHistory
 * - ApprovalPolicy
 */
```

---

# 47. Git Branch

```text
feature/approval-workflow
```

Dependency:

```text
feature/rbac
        ↓
feature/stock-opname
        ↓
feature/asset-management
        ↓
feature/approval-workflow
```

---

# 48. Suggested Commits

```text
feat(approval): add approval models and migrations
feat(approval): add approval request creation
feat(approval): add approval status workflow
feat(approval): add approve action
feat(approval): add reject action
feat(approval): add cancellation
feat(approval): add approval history
feat(approval): add approval authorization
feat(approval): add self approval protection
feat(approval): add warehouse scope validation
feat(approval): add concurrency protection
feat(approval): add idempotency protection
feat(approval): integrate stock opname approval
feat(approval): integrate asset disposal approval
feat(approval): add audit logging
test(approval): add approval workflow tests
test(approval): add authorization tests
test(approval): add concurrency tests
docs(approval): document approval code flow
```

---

# 49. Definition of Done

```text
Code
    ✓ Approval Request
    ✓ Approval History
    ✓ Approval Service
    ✓ Approval Controller
    ✓ Approval Policy

Workflow
    ✓ Pending
    ✓ Approve
    ✓ Reject
    ✓ Cancel
    ✓ Resubmit

Security
    ✓ RBAC
    ✓ Warehouse Scope
    ✓ IDOR Protection
    ✓ Self Approval Protection
    ✓ Mass Assignment Protection

Concurrency
    ✓ Row Lock
    ✓ Transaction
    ✓ Duplicate Protection

Integration
    ✓ Stock Opname
    ✓ Asset Disposal

Audit
    ✓ Approval Actions
    ✓ Decision History

Testing
    ✓ Approval tests
    ✓ Security tests
    ✓ Concurrency tests
    ✓ Integration tests

Documentation
    ✓ Code comments
    ✓ Maintenance guide
    ✓ Request flow
    ✓ Approval flow

Git
    ✓ feature/approval-workflow
```

---

# 50. Final Approval Architecture

```text
                    BUSINESS MODULE
                          │
             ┌────────────┴────────────┐
             ▼                         ▼
       Stock Opname              Asset Disposal
             │                         │
             └────────────┬────────────┘
                          ▼
                  APPROVAL REQUEST
                          │
                          ▼
                       PENDING
                          │
                    ┌─────┴─────┐
                    ▼           ▼
                 APPROVE      REJECT
                    │           │
                    ▼           ▼
                APPROVED     REJECTED
                    │
                    ▼
             BUSINESS SERVICE
                    │
          ┌─────────┴─────────┐
          ▼                   ▼
      Stock Ledger         Asset State
          │                   │
          └─────────┬─────────┘
                    ▼
                 AUDIT LOG
```

---

# 51. Key Principle

Approval Workflow menjawab:

> **"Apakah user yang berwenang sudah menyetujui tindakan ini?"**

Tetapi bukan:

> **"Bagaimana transaksi bisnis tersebut dijalankan?"**

Karena itu:

```text
ApprovalService
→ Mengelola approval

StockOpnameService
→ Mengelola stock adjustment

AssetService
→ Mengelola asset disposal
```

Ini adalah keputusan architecture yang penting untuk Inventra.

Kalau nanti kamu melihat kode:

```text
$approvalService->approve($request);
```

kamu tahu bahwa kode tersebut **belum tentu mengubah stock atau asset secara langsung**.

Kamu perlu tracing:

```text
ApprovalService
       ↓
Business Service
       ↓
Business Transaction
```

Dengan pola ini, kamu akan lebih mudah maintenance tanpa vibe coding karena kamu tahu **file mana yang mengatur approval dan file mana yang benar-benar menjalankan efek bisnisnya**.

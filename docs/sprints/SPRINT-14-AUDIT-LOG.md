# Inventra

## Sprint 14 — Audit Log

**Sprint:** SPRINT-14
**Name:** Audit Log
**Project:** Inventra
**Status:** Planned
**Branch:** `feature/audit-log`

---

# 1. Sprint Overview

Audit Log mencatat aktivitas penting yang terjadi di dalam sistem Inventra.

Tujuan utamanya:

```text
Who?
What?
When?
Where?
Before?
After?
```

Contoh:

```text
Budi
→ mengubah stock
→ 30 Aug 2026 10:30
→ WH-JKT
→ 100 → 95
```

Audit Log bersifat **append-only**.

---

# 2. Objective

Audit Log harus memungkinkan administrator mengetahui:

```text
Siapa melakukan aktivitas?
Aktivitas apa yang dilakukan?
Kapan dilakukan?
Data apa yang berubah?
Nilai sebelum perubahan?
Nilai setelah perubahan?
Dari mana request berasal?
```

---

# 3. Scope

### Included

```text
Authentication Activity
CRUD Activity
Stock Activity
Asset Activity
User / RBAC Changes
Approval Activity
Important System Actions
Before / After Data
IP Address
User Agent
Timestamp
Audit Log Viewer
Filtering
Detail Audit Log
```

### Not Included

```text
Full application logging
Server logs
Laravel debug logs
Infrastructure monitoring
SIEM
Real-time security monitoring
```

---

# 4. Audit vs Application Log

Jangan menyamakan:

```text
Application Log
```

dengan:

```text
Audit Log
```

### Application Log

Untuk:

```text
Error
Exception
Debugging
System Diagnostics
```

### Audit Log

Untuk:

```text
Business Activity
User Activity
Data Changes
Security-sensitive Actions
```

---

# 5. Audit Principle

Audit Log:

```text
WRITE
   ↓
APPEND ONLY
   ↓
READ
```

Tidak boleh:

```text
UPDATE Audit Log
DELETE Audit Log
```

melalui aplikasi normal.

---

# 6. Audit Events

Minimal event:

```text
LOGIN
LOGOUT

CREATE
UPDATE
DELETE

STOCK_IN
STOCK_OUT
STOCK_ADJUSTMENT

STOCK_OPNAME

ASSET_CREATE
ASSET_UPDATE
ASSET_ASSIGN
ASSET_RETURN
ASSET_TRANSFER

APPROVAL
REJECTION

USER_CREATE
USER_UPDATE

ROLE_CREATE
ROLE_UPDATE
ROLE_ASSIGN

PERMISSION_CHANGE
```

Event final mengikuti implementasi module.

---

# 7. Audit Log Data

Minimal field:

```text
id
user_id
event
auditable_type
auditable_id
old_values
new_values
url
ip_address
user_agent
created_at
```

---

# 8. Example

Misalnya user mengubah item:

```text
Item:
Laptop Lenovo

Before:
minimum_stock = 10

After:
minimum_stock = 20
```

Audit:

```text
User:
Budi

Event:
UPDATE

Entity:
Item

Old:
{
    "minimum_stock": 10
}

New:
{
    "minimum_stock": 20
}
```

---

# 9. Before / After Values

Untuk UPDATE:

```text
old_values
+
new_values
```

Untuk CREATE:

```text
old_values = null
new_values = created data
```

Untuk DELETE:

```text
old_values = deleted data
new_values = null
```

Tidak semua field harus disimpan jika tidak relevan.

---

# 10. Sensitive Data

Jangan menyimpan:

```text
Password
Password Hash
API Secret
Access Token
Refresh Token
Private Key
Sensitive Credentials
```

ke dalam audit log.

Gunakan masking atau exclusion.

Contoh:

```text
password
→ [REDACTED]
```

---

# 11. Authentication Audit

Catat aktivitas:

```text
LOGIN_SUCCESS
LOGIN_FAILED
LOGOUT
```

Jika diperlukan:

```text
PASSWORD_CHANGED
PASSWORD_RESET
```

Jangan mencatat password.

---

# 12. Failed Login

Failed login dapat dicatat:

```text
User Identifier
Event
Timestamp
IP Address
User Agent
```

Namun jangan menyimpan password yang dicoba.

---

# 13. CRUD Audit

Data penting yang mengalami:

```text
CREATE
UPDATE
DELETE
```

harus dapat diaudit.

Contoh:

```text
Item
Warehouse
Asset
User
Role
```

---

# 14. Stock Audit

Stock mutation harus mempunyai jejak.

```text
STOCK_IN
STOCK_OUT
ADJUSTMENT
STOCK_OPNAME
```

Contoh:

```text
User:
Budi

Event:
STOCK_OUT

Item:
Laptop

Quantity:
5

Warehouse:
WH-JKT
```

Audit Log menunjuk ke transaction/ledger terkait.

---

# 15. Asset Audit

Aktivitas asset penting:

```text
CREATE
UPDATE
ASSIGN
RETURN
TRANSFER
STATUS_CHANGE
```

Contoh:

```text
Asset:
AST-0001

Before:
Assigned → Budi

After:
Assigned → Andi
```

---

# 16. RBAC Audit

Perubahan permission harus diaudit.

Contoh:

```text
User:
Admin

Action:
ROLE_ASSIGN

Target:
Budi

Old Role:
Staff

New Role:
Warehouse Manager
```

Ini termasuk aktivitas high-risk.

---

# 17. Approval Audit

Catat:

```text
SUBMITTED
APPROVED
REJECTED
CANCELLED
```

Contoh:

```text
Transaction:
TRX-001

Action:
APPROVED

By:
Manager

At:
30 Aug 2026 11:30
```

---

# 18. Audit Actor

Audit harus mengetahui siapa pelakunya.

```text
user_id
```

Jika system melakukan aktivitas:

```text
user_id = null
actor_type = system
```

Contoh:

```text
SYSTEM
→ scheduled process
→ automatic operation
```

---

# 19. Audit Entity

Audit harus mengetahui object yang berubah.

```text
auditable_type
auditable_id
```

Contoh:

```text
Item
ID: 123
```

atau:

```text
InventoryTransaction
ID: 456
```

---

# 20. Request Metadata

Simpan jika relevan:

```text
URL
IP Address
User Agent
```

Tujuannya membantu investigation.

---

# 21. Timestamp

Gunakan timestamp server/database.

Jangan bergantung pada waktu dari browser.

Format aplikasi mengikuti timezone yang telah ditentukan oleh architecture Inventra.

---

# 22. Database Structure

Contoh:

```text
audit_logs
├── id
├── user_id
├── event
├── auditable_type
├── auditable_id
├── old_values
├── new_values
├── url
├── ip_address
├── user_agent
└── created_at
```

`old_values` dan `new_values` dapat menggunakan JSON/JSONB sesuai keputusan database Inventra.

---

# 23. Index

Audit Log dapat menjadi tabel besar.

Index minimal dipertimbangkan untuk:

```text
user_id
event
auditable_type
auditable_id
created_at
```

Composite index mengikuti query aktual.

Gunakan:

```text
EXPLAIN
```

untuk query yang sering digunakan.

---

# 24. Audit Log Retention

V1:

```text
Tidak menghapus audit log melalui UI.
```

Retention policy production dapat ditentukan kemudian berdasarkan:

```text
Business Requirement
Storage
Compliance
Security Policy
```

---

# 25. Append-only Rule

Audit Log:

```text
INSERT
```

boleh.

```text
UPDATE
DELETE
```

tidak boleh melalui application UI/service normal.

Jika ada kebutuhan administratif khusus, harus melalui mekanisme terkontrol dan terdokumentasi.

---

# 26. Audit Service

Gunakan service terpusat.

```text
app/Services/Audit/
├── AuditLogService.php
└── AuditContext.php
```

Contoh flow:

```text
Business Action
      ↓
AuditLogService
      ↓
Create Audit Record
```

---

# 27. Controller Responsibility

Controller tidak perlu membuat audit secara manual di banyak tempat jika dapat ditangani oleh service/event/listener yang konsisten.

Contoh:

```text
Controller
 ↓
Business Service
 ↓
Mutation
 ↓
Audit Event
 ↓
AuditLogService
```

---

# 28. Audit Events

Jika menggunakan event-driven approach:

```text
AuditCreated
StockOutCompleted
AssetAssigned
RoleChanged
```

Listener dapat menangani pembuatan audit.

Tetapi jangan membuat architecture terlalu kompleks untuk event yang sederhana.

---

# 29. Frontend Structure

```text
resources/js/
├── Pages/
│   └── AuditLogs/
│       ├── Index.vue
│       └── Show.vue
│
└── Components/
    └── AuditLogs/
        ├── AuditFilters.vue
        ├── AuditTable.vue
        ├── AuditDetail.vue
        └── ValueDiff.vue
```

---

# 30. Audit Log Page

Table:

```text
Date
User
Event
Module
Record
IP
```

Contoh:

```text
30 Aug 10:30
Budi
STOCK_OUT
Inventory
TRX-001
192.xxx.xxx.xxx
```

---

# 31. Audit Detail

Ketika user membuka audit:

```text
Audit Detail
```

Tampilkan:

```text
Actor
Event
Date
Entity
Entity ID
IP Address
URL
User Agent
Old Values
New Values
```

---

# 32. Value Diff

Untuk UPDATE:

```text
Before
minimum_stock: 10

After
minimum_stock: 20
```

UI dapat menampilkan perubahan field secara jelas.

Contoh:

```text
minimum_stock
10 → 20
```

---

# 33. Filtering

Minimal:

```text
Date Range
User
Event
Module / Entity
Entity ID
```

Contoh:

```text
Event:
STOCK_OUT

User:
Budi

Date:
01 Aug → 30 Aug
```

---

# 34. Pagination

Audit Log wajib menggunakan pagination.

Default:

```text
50 records
```

Pilihan:

```text
25
50
100
```

Jangan load seluruh audit log.

---

# 35. Sorting

Default:

```text
created_at DESC
```

Artinya:

```text
Newest
→
Oldest
```

---

# 36. Access Control

Audit Log bukan data yang boleh dilihat semua user.

Permission:

```text
audit-log.view
```

Biasanya diberikan kepada:

```text
Admin
Authorized Manager
```

sesuai RBAC.

---

# 37. Audit Scope

Jika diperlukan warehouse-scoped audit:

```text
User
 ↓
Warehouse Scope
 ↓
Audit Query
```

Namun administrator yang memiliki global access dapat melihat audit lintas warehouse.

---

# 38. Security

Lindungi Audit Log dari:

```text
Unauthorized Access
IDOR
Log Tampering
Sensitive Data Exposure
Privilege Escalation
Warehouse Scope Bypass
```

---

# 39. IDOR Protection

Jangan hanya:

```text
/audit-logs/123
```

lalu langsung:

```text
AuditLog::find(123)
```

Gunakan authorization:

```text
Authorization
 ↓
Scope
 ↓
Find Record
```

---

# 40. Audit Tampering

Application user tidak boleh:

```text
Edit audit
Delete audit
Change actor
Change timestamp
Change old_values
Change new_values
```

Audit record setelah dibuat dianggap immutable.

---

# 41. Performance

Audit query harus efisien.

Gunakan:

```text
Indexes
Pagination
Specific Columns
Date Filtering
Query Scopes
```

Hindari:

```text
SELECT *
```

jika tidak diperlukan.

---

# 42. Large Audit Dataset

Jika audit mencapai jutaan record:

```text
Pagination
Cursor Pagination
Partitioning
Archiving
Retention Policy
```

dapat dipertimbangkan.

Tidak perlu diterapkan pada V1 kecuali dataset memang membutuhkan.

---

# 43. Code Documentation

Setiap file mengikuti:

```text
docs/code-guide/00_CODE_DOCUMENTATION_STANDARD.md
```

Contoh:

```php
/**
 * Audit Log Service
 *
 * Purpose:
 * Create immutable audit records for important
 * business and security activities.
 *
 * Responsibility:
 * - Capture actor
 * - Capture event
 * - Capture entity
 * - Capture old values
 * - Capture new values
 * - Capture request metadata
 *
 * Security:
 * Sensitive credentials must never be stored.
 *
 * Important:
 * Audit records must not be modified or deleted
 * through normal application flows.
 */
```

---

# 44. Testing

### Create Audit

```text
[ ] Audit created
[ ] Actor recorded
[ ] Event recorded
[ ] Entity recorded
[ ] Timestamp recorded
```

### Update Audit

```text
[ ] Old value recorded
[ ] New value recorded
[ ] Changed fields identifiable
```

### Security

```text
[ ] Password excluded
[ ] Token excluded
[ ] Secret excluded
[ ] Unauthorized user blocked
[ ] IDOR blocked
```

### Immutability

```text
[ ] Audit cannot be updated
[ ] Audit cannot be deleted
```

---

# 45. Business Event Testing

Test:

```text
[ ] Stock In creates audit
[ ] Stock Out creates audit
[ ] Stock Adjustment creates audit
[ ] Asset assignment creates audit
[ ] Asset transfer creates audit
[ ] Role change creates audit
[ ] Approval creates audit
```

---

# 46. Acceptance Criteria

Sprint selesai apabila:

```text
1. Audit Log module tersedia.

2. Audit record dapat dibuat.

3. Actor dapat dicatat.

4. Event dapat dicatat.

5. Entity dapat dicatat.

6. Old values dapat dicatat.

7. New values dapat dicatat.

8. Request metadata dapat dicatat.

9. Authentication activity dapat diaudit.

10. CRUD activity penting dapat diaudit.

11. Stock activity dapat diaudit.

12. Asset activity dapat diaudit.

13. Approval activity dapat diaudit.

14. RBAC changes dapat diaudit.

15. Sensitive credentials tidak disimpan.

16. Audit Log bersifat append-only.

17. Audit tidak dapat diedit melalui aplikasi.

18. Audit tidak dapat dihapus melalui aplikasi.

19. Audit Log memiliki permission khusus.

20. IDOR protection tersedia.

21. Warehouse scope diterapkan jika relevan.

22. Filtering tersedia.

23. Pagination tersedia.

24. Sorting tersedia.

25. Audit detail tersedia.

26. Value diff tersedia.

27. Index relevan dievaluasi.

28. EXPLAIN digunakan untuk query penting.

29. Automated tests berhasil.

30. Security tests berhasil.

31. Code documentation mengikuti standard Inventra.

32. Developer dapat tracing aktivitas → AuditLogService → database.
```

---

# 47. Expected Files

```text
app/
├── Http/
│   └── Controllers/
│       └── AuditLogController.php
│
├── Models/
│   └── AuditLog.php
│
├── Services/
│   └── Audit/
│       ├── AuditLogService.php
│       └── AuditContext.php
│
└── Policies/
    └── AuditLogPolicy.php

resources/js/
├── Pages/
│   └── AuditLogs/
│       ├── Index.vue
│       └── Show.vue
│
└── Components/
    └── AuditLogs/
        ├── AuditFilters.vue
        ├── AuditTable.vue
        ├── AuditDetail.vue
        └── ValueDiff.vue

database/
└── migrations/
    └── xxxx_create_audit_logs_table.php

tests/
└── Feature/
    └── AuditLog/
```

---

# 48. Git Branch

```text
feature/audit-log
```

Dependency:

```text
SPRINT-01 → Authentication
SPRINT-02 → RBAC
SPRINT-03+ → Business Modules
SPRINT-14 → Audit Log
```

Audit Log dapat mulai digunakan setelah business actions mulai tersedia.

---

# 49. Suggested Commits

```text
feat(audit): add audit log model
feat(audit): add audit logs migration
feat(audit): add audit log service
feat(audit): add audit context
feat(audit): add audit event tracking
feat(audit): add authentication audit
feat(audit): add CRUD audit
feat(audit): add stock audit
feat(audit): add asset audit
feat(audit): add RBAC audit
feat(audit): add approval audit
feat(audit): add audit log authorization
feat(audit): add audit log filters
feat(audit): add audit log pagination
feat(audit): add audit detail
feat(audit): add audit value diff
perf(audit): optimize audit queries
perf(audit): add audit indexes
test(audit): add audit log tests
test(audit): add audit security tests
docs(audit): document audit log flow
```

---

# 50. Maintenance Guide

### "Saya ingin tahu siapa yang mengubah stock."

Trace:

```text
Stock Transaction
 ↓
Business Service
 ↓
AuditLogService
 ↓
audit_logs
```

---

### "Audit tidak tercatat."

Trace:

```text
Business Action
 ↓
Event / Service
 ↓
AuditLogService
 ↓
Database
```

Periksa apakah mutation berhasil dan audit hook/event dipanggil.

---

### "Saya ingin menambahkan audit untuk module baru."

Gunakan:

```text
AuditLogService
```

Jangan membuat mekanisme audit baru.

Flow:

```text
New Business Action
       ↓
Define Audit Event
       ↓
Capture Actor
       ↓
Capture Entity
       ↓
Capture Relevant Changes
       ↓
Write Audit Log
```

---

### "Audit Log lambat."

Trace:

```text
AuditLogs/Index.vue
 ↓
AuditLogController
 ↓
Audit Query
 ↓
EXPLAIN
 ↓
Index
 ↓
Pagination
```

---

# 51. Code Understanding Map

```text
Business Action
      ↓
Service / Event
      ↓
AuditLogService
      ↓
AuditLog Model
      ↓
audit_logs
      ↓
AuditLogController
      ↓
Inertia
      ↓
AuditLogs/Index.vue
```

Jika ingin memahami Audit Log tanpa vibe coding:

```text
Start:
AuditLogs/Index.vue

↓
Cari endpoint / route

↓
AuditLogController

↓
Cari query

↓
AuditLog Model

↓
Cari AuditLogService

↓
Lihat bagaimana business action
menghasilkan audit
```

---

# 52. Final Architecture

```text
                    USER ACTION
                         │
                         ▼
                 BUSINESS SERVICE
                         │
              ┌──────────┴──────────┐
              ▼                     ▼
          Business DB          AuditLogService
              │                     │
              │                     ▼
              │                 audit_logs
              │                     │
              └──────────┬──────────┘
                         │
                         ▼
                    Audit Viewer
                         │
                         ▼
                     Admin/User
```

---

# 53. Key Principle

Audit Log harus menjawab:

```text
WHO
WHAT
WHEN
WHICH DATA
BEFORE
AFTER
FROM WHERE
```

dan prinsip terpenting:

```text
Business Data
→ boleh berubah sesuai business process

Audit Data
→ tidak boleh diubah untuk menghilangkan jejak
```

Audit Log menjadi **jejak investigasi**, bukan sekadar tabel history biasa.

# ADR-008 — Audit Log

**Project:** Inventra
**Status:** Accepted
**Date:** 2026-08-30

---

# 1. Context

Inventra merupakan sistem inventory yang menangani aktivitas penting seperti:

```text
Stock In
Stock Out
Stock Opname
Asset Management
Approval
User Management
Role & Permission
Master Data
```

Perubahan pada data tersebut perlu dapat ditelusuri.

Contoh pertanyaan yang harus dapat dijawab:

```text
Siapa yang mengubah data?
Apa yang diubah?
Kapan perubahan terjadi?
Data apa yang terkena?
Dari mana perubahan dilakukan?
```

---

# 2. Problem

Tanpa audit log, sistem hanya mengetahui kondisi data saat ini.

Contoh:

```text
Stock = 80
```

Tetapi tidak diketahui:

```text
Siapa mengubahnya?
Kapan berubah?
Transaksi apa yang menyebabkan perubahan?
```

Hal ini menyulitkan:

```text
Investigation
Accountability
Security Monitoring
Data Correction
Compliance
```

---

# 3. Decision

Inventra menggunakan **Audit Log** untuk mencatat aktivitas penting yang memiliki dampak terhadap:

```text
Business Data
Inventory
Authorization
Security
System Configuration
```

Audit Log bersifat **append-oriented**.

Artinya:

```text
Event terjadi
    ↓
Audit Record dibuat
```

dan history tidak diubah untuk memperbaiki kesalahan.

---

# 4. Audit Log vs Application Log

Keduanya memiliki tujuan berbeda.

### Application Log

Digunakan untuk:

```text
Error
Exception
Debugging
Performance
System Event
```

### Audit Log

Digunakan untuk:

```text
Who
Did What
To Which Resource
When
```

Contoh:

```text
Application Log
"SQL timeout occurred"

Audit Log
"User 15 approved Stock Out #100"
```

---

# 5. Auditable Events

Tidak semua request harus menjadi audit event.

Event penting yang harus dapat diaudit antara lain:

```text
Authentication
User Management
Role Changes
Permission Changes
Master Data Changes
Stock In
Stock Out
Stock Opname
Adjustment
Approval
Rejection
Cancellation
Asset Changes
Security-sensitive Actions
```

Detail final mengikuti kebutuhan masing-masing module.

---

# 6. Authentication Audit

Event authentication yang relevan dapat dicatat.

Contoh:

```text
LOGIN_SUCCESS
LOGIN_FAILED
LOGOUT
PASSWORD_CHANGED
```

Event yang bersifat sangat sensitif harus mempertimbangkan volume log dan privacy.

---

# 7. Authorization Audit

Perubahan authorization harus diaudit.

Contoh:

```text
ROLE_ASSIGNED
ROLE_CHANGED
PERMISSION_CHANGED
WAREHOUSE_SCOPE_CHANGED
```

Contoh:

```text
User A
Role:
Staff → Manager
```

harus dapat ditelusuri.

---

# 8. Inventory Audit

Aktivitas inventory harus dapat ditelusuri melalui kombinasi:

```text
Transaction
+
Inventory Ledger
+
Audit Log
```

Contoh:

```text
Stock Out #100
     ↓
Approved by User B
     ↓
Ledger Movement -20
     ↓
Audit Event
```

Audit Log tidak menggantikan Inventory Ledger.

---

# 9. Transaction Audit

Event transaction:

```text
CREATED
UPDATED
SUBMITTED
APPROVED
REJECTED
CANCELLED
```

dapat dicatat sesuai kebutuhan module.

Contoh:

```text
Stock Out #100
Created by User A
Submitted by User A
Approved by User B
```

---

# 10. Actor

Audit record harus menyimpan actor jika tersedia.

Contoh:

```text
user_id
```

Namun system-generated events juga dapat terjadi.

Contoh:

```text
System
Background Job
Integration
```

Karena itu actor dapat berupa:

```text
User
System
Integration
```

sesuai design database.

---

# 11. Resource

Audit event harus mengidentifikasi resource yang terkena perubahan.

Contoh:

```text
resource_type
resource_id
```

Contoh:

```text
resource_type = StockOut
resource_id   = 100
```

Hal ini memungkinkan pencarian:

```text
Semua aktivitas pada Stock Out #100
```

---

# 12. Action

Action harus menggambarkan aktivitas.

Contoh:

```text
CREATE
UPDATE
DELETE
SUBMIT
APPROVE
REJECT
CANCEL
LOGIN
LOGOUT
```

Gunakan naming convention yang konsisten.

---

# 13. Before and After

Untuk perubahan data tertentu, audit dapat menyimpan:

```text
old_values
new_values
```

Contoh:

```json
{
  "quantity": 10
}
```

menjadi:

```json
{
  "quantity": 15
}
```

Audit record dapat menunjukkan perubahan tersebut.

Namun tidak semua event membutuhkan before/after data.

---

# 14. Sensitive Data

Audit Log tidak boleh menyimpan sensitive data secara sembarangan.

Jangan menyimpan:

```text
Password
Password Hash
Access Token
API Secret
Private Key
```

dan credential sensitif lainnya.

Jika sebuah field sensitif perlu diaudit, gunakan metadata yang aman, bukan nilai rahasianya.

---

# 15. Audit Metadata

Audit event dapat memiliki metadata tambahan.

Contoh:

```text
IP Address
User Agent
Request ID
Source
Reason
```

Metadata yang disimpan harus mempertimbangkan:

```text
Privacy
Storage
Security
Retention
```

---

# 16. Request ID

Request ID dapat digunakan untuk menghubungkan:

```text
API Request
Application Log
Audit Event
```

Contoh:

```text
Request ID:
req-abc-123
```

Kemudian:

```text
API Log
     ↓
Audit Log
     ↓
Application Log
```

dapat ditelusuri.

---

# 17. Audit Immutability

Audit history tidak boleh diedit melalui application normal.

Tidak diperbolehkan:

```text
UPDATE audit_logs
```

untuk mengubah history bisnis.

Jika terjadi kesalahan pada audit generation, perbaikannya dilakukan melalui mekanisme administratif/database yang terkontrol.

---

# 18. Delete Policy

Audit Log tidak boleh dihapus sebagai bagian dari CRUD normal.

Contoh:

```text
User
 ↓
Delete Audit Log
```

tidak diperbolehkan.

Jika retention diperlukan:

```text
Audit Log
   ↓
Retention Policy
   ↓
Archive / Controlled Deletion
```

harus menjadi proses yang terkontrol.

---

# 19. Audit and Transaction Atomicity

Untuk business-critical event, audit dapat dibuat dalam database transaction yang sama.

Contoh Stock Out:

```text
BEGIN
   ↓
Update Transaction
   ↓
Create Ledger
   ↓
Update Balance
   ↓
Create Audit
   ↓
COMMIT
```

Jika operation gagal:

```text
ROLLBACK
```

Dengan demikian business change dan audit tidak mudah menjadi tidak sinkron.

---

# 20. Audit Failure

Tidak semua audit event harus memiliki failure behavior yang sama.

Untuk business-critical audit:

```text
Audit Failure
    ↓
Transaction Failure
```

dapat digunakan jika audit merupakan bagian dari integrity requirement.

Untuk event non-critical:

```text
Audit Failure
    ↓
Log Error
    ↓
Business Operation May Continue
```

Keputusan final ditentukan berdasarkan criticality event.

---

# 21. Audit Query

Audit Log harus dapat dicari berdasarkan:

```text
User
Action
Resource Type
Resource ID
Date Range
Request ID
```

Contoh:

```text
Who changed Item #10?
```

atau:

```text
What did User A do today?
```

---

# 22. Pagination

Audit Log dapat menjadi sangat besar.

Karena itu query audit harus menggunakan pagination.

Contoh:

```text
GET /api/v1/audit-logs?page=1
```

Untuk dataset yang sangat besar, cursor pagination dapat dipertimbangkan.

---

# 23. Indexing

Index kandidat:

```text
user_id
action
resource_type
resource_id
created_at
request_id
```

Composite index dapat dibuat berdasarkan query pattern.

Contoh:

```text
(resource_type, resource_id, created_at)
```

atau:

```text
(user_id, created_at)
```

Index final mengikuti workload aktual dan dianalisis menggunakan:

```text
EXPLAIN
EXPLAIN ANALYZE
```

sesuai:

```text
ADR-007 — Database Performance
```

---

# 24. High Volume Events

Audit event dapat memiliki volume tinggi.

Karena itu jangan mencatat setiap aktivitas teknis secara berlebihan.

Contoh yang tidak perlu:

```text
Mouse movement
Frontend render
Every API read
Every database SELECT
```

Fokus pada event yang memiliki nilai audit.

---

# 25. Read Audit

Tidak semua read operation harus dicatat.

Namun read terhadap resource yang sangat sensitif dapat diaudit jika business/security requirement mengharuskannya.

Contoh:

```text
Sensitive Report Viewed
Sensitive Data Exported
```

Keputusan tersebut ditentukan berdasarkan security requirement.

---

# 26. Export Audit

Export data merupakan aktivitas yang berpotensi sensitif.

Contoh:

```text
EXPORT_REPORT
EXPORT_INVENTORY
EXPORT_TRANSACTION
```

dapat dicatat:

```text
User
Report
Filter
Timestamp
```

Jangan menyimpan seluruh file hasil export sebagai audit metadata kecuali memang dibutuhkan.

---

# 27. Failed Actions

Failed security-sensitive actions dapat dicatat.

Contoh:

```text
Unauthorized Approval Attempt
Unauthorized Warehouse Access
Failed Login
```

Namun volume dan retention harus dipertimbangkan.

---

# 28. Audit UI

Admin atau user yang memiliki permission dapat melihat audit log.

Contoh:

```text
Audit Log
├── Timestamp
├── User
├── Action
├── Resource
├── Description
└── Details
```

Audit UI harus tetap mengikuti RBAC.

Tidak semua user boleh melihat seluruh audit history.

---

# 29. Audit Access Control

Contoh:

```text
audit.view
audit.export
```

dapat digunakan untuk mengontrol akses.

Warehouse scope juga dapat diterapkan jika audit event berhubungan dengan warehouse-specific data.

---

# 30. Security Consideration

Audit Log sendiri merupakan data sensitif.

Karena dapat mengandung:

```text
User Activity
Business Activity
Security Events
Operational Information
```

maka akses terhadap audit harus dibatasi.

Audit Log tidak boleh menjadi tempat untuk mengekspos credential atau secret.

---

# 31. Performance

Audit writing tidak boleh menjadi bottleneck utama business transaction.

Karena itu:

```text
Simple Insert
Proper Index
Minimal Payload
```

digunakan sebagai baseline.

Jika volume sangat besar di masa depan, asynchronous processing atau dedicated audit infrastructure dapat dipertimbangkan.

Namun bukan default V1.

---

# 32. Alternatives Considered

### No Audit Log

Tidak dipilih karena Inventra membutuhkan traceability untuk inventory dan authorization.

### Application Log Only

Tidak dipilih karena application log tidak dirancang sebagai business audit trail.

### Full Event Sourcing

Tidak digunakan untuk V1 karena kompleksitas lebih tinggi dari kebutuhan Inventra.

### Audit via Frontend

Tidak dipilih karena frontend tidak dapat dipercaya sebagai security boundary.

---

# 33. Consequences

### Positive

```text
+ Better accountability
+ Easier investigation
+ Security visibility
+ Transaction traceability
+ Supports compliance requirements
+ Easier debugging of business changes
```

### Negative

```text
- Additional database storage
- Additional write operations
- Requires access control
- Requires retention strategy
- Requires careful handling of sensitive data
```

---

# 34. Implementation Principle

Audit flow:

```text
Business Action
      ↓
Authorization
      ↓
Business Operation
      ↓
Audit Event
      ↓
Persist
```

Untuk critical transaction:

```text
BEGIN
 ↓
Business Change
 ↓
Audit Event
 ↓
COMMIT
```

Audit harus menjawab:

```text
Who?
What?
When?
Which Resource?
Why / Context?
```

jika informasi tersebut relevan dengan event.

---

# 35. Maintenance Guide

Jika perubahan bisnis tidak muncul di audit:

```text
1. Check Event Definition
2. Check Service
3. Check Database Transaction
4. Check Audit Creation
5. Check Rollback
6. Check Audit Table
```

Jika audit terlalu besar:

```text
1. Identify High Volume Event
2. Remove Unnecessary Events
3. Review Payload
4. Review Indexes
5. Review Retention
```

Jika audit berisi sensitive data:

```text
1. Identify Source
2. Remove Sensitive Field
3. Review Audit Serializer
4. Review Existing Records
5. Review Access Permission
```

---

# 36. Related Decisions

```text
ADR-003 — Inventory Ledger
ADR-004 — RBAC Authorization
ADR-005 — Approval Workflow
ADR-006 — API Architecture
ADR-007 — Database Performance
```

Dokumen terkait:

```text
07_PERMISSION_MATRIX.md
13_AUDIT_LOG.md
architecture/SECURITY_ARCHITECTURE.md
```

---

# 37. Final Decision

**Accepted**

Inventra menggunakan **Audit Log** sebagai business audit trail untuk aktivitas penting.

Model utama:

```text
Actor
  ↓
Action
  ↓
Resource
  ↓
Timestamp
  ↓
Metadata
```

Untuk business-critical transaction:

```text
Transaction
   +
Inventory Ledger
   +
Inventory Balance
   +
Audit Log
```

harus diproses dengan consistency dan atomicity yang sesuai.

Audit Log bersifat **append-oriented**, dilindungi RBAC, tidak menyimpan credential sensitif, dan dioptimalkan untuk kebutuhan traceability tanpa mencatat aktivitas teknis yang tidak memiliki nilai audit.

# ADR-006 — API Architecture

**Project:** Inventra
**Status:** Accepted
**Date:** 2026-08-30

---

# 1. Context

Inventra menggunakan:

```text
Laravel
+
Inertia.js
+
Vue.js
```

sebagai web application architecture.

Namun Inventra juga membutuhkan kemungkinan integrasi dengan:

```text
External System
Mobile Application
Automation
Third-party Application
Future Client
```

Karena itu diperlukan REST API dengan aturan yang konsisten.

---

# 2. Problem

Tanpa API architecture yang jelas, endpoint dapat berkembang menjadi tidak konsisten.

Contoh masalah:

```text
/api/items
/api/getItems
/api/item-list
/api/items/all
```

atau response berbeda-beda:

```json
{
  "data": []
}
```

dan:

```json
{
  "items": []
}
```

dan:

```json
{
  "result": []
}
```

Inventra membutuhkan convention yang konsisten agar API mudah digunakan dan dipelihara.

---

# 3. Decision

Inventra menggunakan **REST API** sebagai interface untuk kebutuhan external integration dan client non-Inertia.

Web application utama tetap menggunakan:

```text
Laravel
    ↓
Inertia
    ↓
Vue
```

Sedangkan external client menggunakan:

```text
Client
   ↓
REST API
   ↓
Laravel
   ↓
Application / Business Logic
   ↓
PostgreSQL
```

---

# 4. API Responsibility

REST API bertanggung jawab untuk:

```text
Authentication
Authorization
Request Validation
Resource Access
Business Operation
Response Formatting
Error Handling
Pagination
Filtering
Sorting
```

API tidak boleh memiliki business logic yang berbeda dari web application.

---

# 5. Shared Business Logic

Web dan API harus menggunakan business service yang sama jika menjalankan business operation yang sama.

Contoh:

```text
Inertia Request
      ↓
StockOutService
      ↓
Inventory Ledger


API Request
      ↓
StockOutService
      ↓
Inventory Ledger
```

Jangan membuat:

```text
Web Stock Logic
+
API Stock Logic
```

yang berbeda.

Hal tersebut dapat menyebabkan hasil transaksi berbeda antara web dan API.

---

# 6. API Versioning

API menggunakan versioning.

Format:

```text
/api/v1/...
```

Contoh:

```text
/api/v1/items
/api/v1/warehouses
/api/v1/inventory
/api/v1/stock-ins
/api/v1/stock-outs
```

Versioning digunakan untuk menjaga compatibility ketika API berubah secara breaking.

---

# 7. Resource Naming

Gunakan nama resource berbentuk plural.

Contoh:

```text
/api/v1/items
/api/v1/warehouses
/api/v1/assets
/api/v1/stock-ins
/api/v1/stock-outs
```

Hindari naming yang tidak konsisten seperti:

```text
/api/v1/getItems
/api/v1/createItem
```

HTTP method digunakan untuk membedakan operation.

---

# 8. HTTP Methods

Convention:

```text
GET
    Read

POST
    Create / Action

PUT / PATCH
    Update

DELETE
    Delete
```

Contoh:

```text
GET    /api/v1/items
GET    /api/v1/items/{id}
POST   /api/v1/items
PATCH  /api/v1/items/{id}
DELETE /api/v1/items/{id}
```

Untuk state transition khusus:

```text
POST /api/v1/stock-outs/{id}/submit
POST /api/v1/stock-outs/{id}/approve
POST /api/v1/stock-outs/{id}/reject
```

---

# 9. Authentication

API harus melakukan authentication sebelum mengakses protected resource.

Konsep:

```text
API Request
    ↓
Authentication
    ↓
Authenticated User
```

Authentication mechanism mengikuti security architecture Inventra dan implementasi Laravel yang dipilih.

Endpoint public hanya diperbolehkan jika memang dibutuhkan.

---

# 10. Authorization

Authentication tidak berarti user memiliki akses.

Flow:

```text
Request
  ↓
Authentication
  ↓
Permission
  ↓
Warehouse Scope
  ↓
Policy
  ↓
Resource
```

API mengikuti:

```text
ADR-004 — RBAC Authorization
```

API tidak boleh menjadi jalan untuk bypass authorization web application.

---

# 11. Request Validation

Semua input dari API harus divalidasi.

Contoh:

```text
POST /api/v1/stock-outs
```

Request:

```json
{
  "warehouse_id": "...",
  "items": [
    {
      "item_id": "...",
      "quantity": 10
    }
  ]
}
```

Validation harus dilakukan backend.

Frontend/client validation hanya untuk UX.

---

# 12. Response Format

Response API menggunakan format yang konsisten.

Success:

```json
{
  "data": {}
}
```

Collection:

```json
{
  "data": [],
  "meta": {}
}
```

Error:

```json
{
  "message": "Validation failed.",
  "errors": {}
}
```

Format final dapat berkembang, tetapi harus konsisten di seluruh API.

---

# 13. HTTP Status Codes

Gunakan HTTP status code sesuai kondisi.

Contoh:

```text
200 OK
201 Created
204 No Content

400 Bad Request
401 Unauthorized
403 Forbidden
404 Not Found
409 Conflict
422 Unprocessable Entity
429 Too Many Requests

500 Internal Server Error
```

Tidak semua endpoint harus menggunakan seluruh status code tersebut.

Gunakan status code yang paling sesuai dengan kondisi sebenarnya.

---

# 14. Validation Error

Validation error menggunakan:

```text
422 Unprocessable Entity
```

Contoh:

```json
{
  "message": "Validation failed.",
  "errors": {
    "quantity": ["The quantity must be greater than zero."]
  }
}
```

---

# 15. Authorization Error

Jika user authenticated tetapi tidak memiliki permission:

```text
403 Forbidden
```

Contoh:

```text
POST /api/v1/stock-outs/100/approve
```

tanpa permission:

```text
stock-out.approve
```

menghasilkan:

```text
403 Forbidden
```

---

# 16. Resource Not Found

Jika resource tidak ditemukan:

```text
404 Not Found
```

Namun untuk resource yang keberadaannya tidak boleh dibocorkan karena security policy, application dapat memilih response yang lebih aman sesuai security architecture.

---

# 17. Conflict

Gunakan:

```text
409 Conflict
```

untuk kondisi conflict yang relevan.

Contoh:

```text
Concurrent Update
Duplicate Business Resource
Invalid State Transition
```

Contoh:

```text
Stock Out
Status = APPROVED
```

kemudian client mencoba approve lagi.

System dapat mengembalikan:

```text
409 Conflict
```

jika sesuai dengan API contract.

---

# 18. Pagination

Collection besar harus menggunakan pagination.

Contoh:

```text
GET /api/v1/transactions?page=1&per_page=20
```

Response:

```json
{
  "data": [],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 100
  }
}
```

Jangan mengembalikan ribuan atau jutaan record dalam satu request tanpa alasan yang jelas.

---

# 19. Filtering

Resource dapat mendukung filtering.

Contoh:

```text
/api/v1/inventory?warehouse_id=WH-001
```

atau:

```text
/api/v1/stock-outs?status=SUBMITTED
```

Filter harus dibatasi pada field yang memang diperbolehkan.

Jangan memberikan arbitrary database filtering kepada client.

---

# 20. Sorting

Sorting dapat digunakan jika dibutuhkan.

Contoh:

```text
/api/v1/items?sort=name
```

atau:

```text
/api/v1/transactions?sort=-created_at
```

Field sorting harus di-whitelist.

---

# 21. Search

Search menggunakan parameter yang konsisten.

Contoh:

```text
/api/v1/items?search=laptop
```

Search harus menggunakan query yang teroptimasi.

Untuk dataset besar, index dapat diperlukan sesuai query pattern.

Gunakan:

```text
EXPLAIN
EXPLAIN ANALYZE
```

untuk query yang menjadi bottleneck.

---

# 22. API Resource

API tidak boleh langsung mengekspos database model tanpa kontrol.

Gunakan resource/transformer untuk menentukan data yang dikirim.

Konsep:

```text
Database Model
      ↓
API Resource
      ↓
JSON Response
```

Tujuannya:

```text
Data Control
Security
Consistency
API Contract
```

---

# 23. Sensitive Data

API tidak boleh mengembalikan field sensitif jika tidak diperlukan.

Contoh:

```text
Password Hash
Internal Security Data
Sensitive Configuration
Private Metadata
```

Response harus mengikuti principle:

> Return only what the client needs.

---

# 24. Transaction API

Transaction seperti Stock In dan Stock Out tidak boleh hanya melakukan CRUD sederhana jika memiliki business process.

Contoh:

```text
POST /stock-outs
```

berarti membuat transaction.

Sedangkan:

```text
POST /stock-outs/{id}/submit
POST /stock-outs/{id}/approve
POST /stock-outs/{id}/reject
```

menjalankan state transition.

State transition harus melewati business service.

---

# 25. Idempotency

Operation yang berpotensi dipanggil ulang harus dirancang agar tidak menghasilkan duplicate business effect.

Terutama pada:

```text
Approval
Payment-like operations
External integration
Retryable requests
```

Untuk operation tertentu, API dapat menggunakan idempotency key.

Contoh konsep:

```text
Idempotency-Key: unique-request-id
```

Implementasi final ditentukan berdasarkan kebutuhan endpoint.

---

# 26. Rate Limiting

API dapat menggunakan rate limiting untuk mencegah:

```text
Abuse
Brute Force
Accidental Request Flood
```

Terutama pada:

```text
Authentication
Sensitive Endpoint
Public Endpoint
External Integration
```

---

# 27. API Logging

Request penting harus dapat ditelusuri.

Namun jangan menyimpan:

```text
Password
Access Token
Sensitive Credential
```

ke log.

API logging harus memperhatikan:

```text
Security
Privacy
Storage
Retention
```

---

# 28. Audit vs API Log

API log dan Audit Log bukan hal yang sama.

### API Log

Menjawab:

```text
Request apa yang terjadi?
```

### Audit Log

Menjawab:

```text
Perubahan bisnis apa yang dilakukan?
Siapa yang melakukannya?
```

Contoh:

```text
API Log
POST /api/v1/stock-outs/100/approve

Audit Log
User B approved Stock Out #100
```

Audit mengikuti:

```text
ADR-008 — Audit Log
```

---

# 29. Error Handling

API harus memberikan error yang dapat dimengerti client tanpa membocorkan internal system.

Production response tidak boleh menampilkan:

```text
SQL Query
Stack Trace
Database Credential
Internal Path
Secret
```

Error internal dicatat melalui application logging.

Client menerima error yang aman dan konsisten.

---

# 30. API Documentation

API harus terdokumentasi.

Dokumentasi minimal mencakup:

```text
Endpoint
HTTP Method
Authentication
Permission
Request
Response
Validation
Possible Errors
```

Jika API berkembang signifikan, OpenAPI/Swagger dapat digunakan sebagai machine-readable contract.

---

# 31. API and Web Consistency

Web dan API harus mengikuti business rule yang sama.

Contoh Stock Out:

```text
Web
 ↓
StockOutService
 ↓
Ledger

API
 ↓
StockOutService
 ↓
Ledger
```

Bukan:

```text
Web
 ↓
Service A

API
 ↓
Service B
```

dengan aturan inventory yang berbeda.

---

# 32. Performance

API harus memperhatikan:

```text
Query Count
N+1 Query
Payload Size
Pagination
Index
Caching
Database Load
```

Untuk endpoint yang lambat:

```text
Request
 ↓
Application Profiling
 ↓
Query Analysis
 ↓
EXPLAIN ANALYZE
 ↓
Optimization
```

Jangan melakukan optimization berdasarkan asumsi saja.

---

# 33. API Security Principles

API mengikuti:

```text
Authentication
Authorization
Input Validation
Least Privilege
Rate Limiting
Secure Error Handling
Sensitive Data Protection
Auditability
```

Security boundary tetap berada di backend.

---

# 34. Alternatives Considered

### GraphQL

Tidak digunakan untuk V1 karena kebutuhan resource Inventra dapat ditangani dengan REST dan REST lebih sederhana untuk kebutuhan saat ini.

### Full API-First Architecture

Tidak digunakan sebagai primary web architecture karena web application menggunakan Inertia.

### No API

Tidak dipilih karena Inventra berpotensi membutuhkan integrasi dan client tambahan.

---

# 35. Consequences

### Positive

```text
+ Consistent API contract
+ Easy external integration
+ Future mobile support
+ Clear resource structure
+ Shared business logic
+ Easier testing
```

### Negative

```text
- API maintenance overhead
- Requires versioning
- Requires documentation
- Requires security controls
- Requires backward compatibility considerations
```

---

# 36. Implementation Principle

API layer:

```text
Request
   ↓
Authentication
   ↓
Authorization
   ↓
Validation
   ↓
Controller
   ↓
Service
   ↓
Model / Database
   ↓
Resource
   ↓
JSON Response
```

Business-critical operations menggunakan database transaction jika diperlukan.

---

# 37. Maintenance Guide

Jika API menghasilkan data yang berbeda dari web:

```text
Check
1. Route
2. Controller
3. Request Validation
4. Authorization
5. Service
6. Query
7. Resource Transformer
```

Jika hanya API yang dapat bypass security:

```text
Check
1. Authentication
2. Permission
3. Policy
4. Warehouse Scope
5. Route Middleware
```

Jika API lambat:

```text
Check
1. Payload
2. N+1
3. Query Count
4. Index
5. EXPLAIN ANALYZE
6. Database Load
```

---

# 38. Related Decisions

```text
ADR-001 — PostgreSQL
ADR-002 — Inertia + Vue
ADR-004 — RBAC Authorization
ADR-005 — Approval Workflow
ADR-008 — Audit Log
```

Dokumen terkait:

```text
06_API.md
07_PERMISSION_MATRIX.md
architecture/SYSTEM_ARCHITECTURE.md
architecture/SECURITY_ARCHITECTURE.md
```

---

# 39. Final Decision

**Accepted**

Inventra menggunakan **REST API versioned (`/api/v1`)** sebagai interface resmi untuk external client dan integration.

Web application utama tetap menggunakan:

```text
Laravel
   ↓
Inertia
   ↓
Vue
```

REST API menggunakan business logic yang sama dengan web application dan mengikuti:

```text
Authentication
→ Authorization
→ Validation
→ Business Logic
→ Database
→ Resource
→ JSON Response
```

API harus konsisten, secure, versioned, documented, dan tidak boleh menjadi jalan untuk melewati business rule atau authorization Inventra.

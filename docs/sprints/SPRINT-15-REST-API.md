# Inventra

## Sprint 15 — REST API

**Sprint:** SPRINT-15
**Name:** REST API
**Project:** Inventra
**Status:** Planned
**Branch:** `feature/rest-api`

---

# 1. Sprint Overview

REST API menyediakan interface terstruktur untuk mengakses data dan fungsi Inventra dari aplikasi lain.

Contoh client:

```text
Inventra Web
Mobile App
External System
Third-party Integration
Future Frontend
```

Architecture:

```text
Client
   ↓
REST API
   ↓
Laravel Application
   ↓
Business Services
   ↓
Database
```

---

# 2. Objective

API harus menyediakan:

```text
Authentication
Authorization
Inventory Data
Warehouse Data
Item Data
Stock Data
Asset Data
Transaction Data
Reporting Data
```

sesuai kebutuhan dan permission.

---

# 3. Important Principle

API **tidak boleh membuat business logic baru**.

Gunakan business service yang sama dengan aplikasi utama.

```text
Inertia
   ↓
Business Service
   ↓
Database

REST API
   ↓
Business Service
   ↓
Database
```

Bukan:

```text
Inertia → Logic A
API     → Logic B
```

Hal ini mencegah perbedaan behavior antara Web dan API.

---

# 4. Scope

### Included

```text
REST API Structure
API Versioning
Authentication
Authorization
Request Validation
Resource Transformation
Pagination
Filtering
Sorting
Error Response
Rate Limiting
API Documentation
API Testing
```

### Initial Resources

```text
Users
Items
Warehouses
Inventory
Stock
Assets
Transactions
Reports
```

Endpoint final mengikuti kebutuhan module.

---

# 5. API Versioning

Gunakan versioning:

```text
/api/v1
```

Contoh:

```text
GET /api/v1/items
```

Bukan:

```text
GET /api/items
```

Tujuan:

```text
v1
 ↓
future v2
```

sehingga perubahan besar tidak langsung merusak client lama.

---

# 6. Base URL

Production:

```text
https://domain-inventra.com/api/v1
```

Development:

```text
http://localhost/api/v1
```

Actual domain ditentukan saat deployment.

---

# 7. REST Convention

Gunakan resource-oriented endpoint.

Benar:

```text
GET    /items
POST   /items
GET    /items/{id}
PUT    /items/{id}
PATCH  /items/{id}
DELETE /items/{id}
```

Hindari:

```text
/getItems
/createItem
/updateItem
/deleteItem
```

---

# 8. HTTP Methods

### GET

Read data.

```text
GET /items
GET /items/123
```

### POST

Create resource/action yang memang membutuhkan POST.

```text
POST /items
```

### PUT

Replace resource.

```text
PUT /items/123
```

### PATCH

Partial update.

```text
PATCH /items/123
```

### DELETE

Delete resource jika business rule mengizinkan.

```text
DELETE /items/123
```

---

# 9. Authentication

API membutuhkan authentication.

Untuk first-party / token-based access gunakan mekanisme token yang dipilih dalam implementation architecture Inventra.

Contoh:

```text
Authorization: Bearer <token>
```

Token tidak boleh:

```text
URL
Query String
Logs
Audit Payload
```

---

# 10. Authentication Flow

```text
Client
   ↓
POST /api/v1/auth/login
   ↓
Validate Credentials
   ↓
Authenticate
   ↓
Issue Token
   ↓
Client
```

Request berikutnya:

```text
Client
   ↓
Bearer Token
   ↓
API
   ↓
Authenticate User
```

---

# 11. Logout

Endpoint:

```text
POST /api/v1/auth/logout
```

Token harus dicabut/revoked sesuai mekanisme authentication yang digunakan.

---

# 12. Current User

Endpoint:

```text
GET /api/v1/auth/me
```

Response memberikan informasi user yang diperlukan client.

Jangan mengembalikan:

```text
password
password_hash
tokens
secrets
```

---

# 13. Authorization

Authentication hanya menjawab:

```text
"Who are you?"
```

Authorization menjawab:

```text
"What are you allowed to do?"
```

API harus menggunakan RBAC Inventra.

Contoh:

```text
items.view
items.create
items.update
items.delete
```

---

# 14. Warehouse Scope

API harus menerapkan warehouse scope.

Contoh:

```text
GET /api/v1/inventory?warehouse_id=2
```

Jika user tidak memiliki akses warehouse tersebut:

```text
403 Forbidden
```

atau hasil tidak boleh membocorkan data tersebut, sesuai authorization design.

Backend tetap menjadi source of truth.

---

# 15. IDOR Protection

Jangan:

```php
Item::find($id);
```

kemudian langsung mengembalikan data.

Gunakan:

```text
Authentication
 ↓
Authorization
 ↓
Scope
 ↓
Resource
```

Contoh:

```text
GET /api/v1/items/123
```

harus memastikan user memang boleh melihat item tersebut.

---

# 16. Request Validation

Semua input API harus divalidasi.

Contoh:

```text
POST /items
```

Validation:

```text
name
sku
category_id
minimum_stock
unit
```

Invalid request:

```text
422 Unprocessable Entity
```

---

# 17. Form Request

Gunakan Laravel Form Request untuk validation.

Contoh:

```text
app/Http/Requests/API/
├── StoreItemRequest.php
├── UpdateItemRequest.php
├── StoreWarehouseRequest.php
└── ...
```

Controller tidak berisi validation panjang.

---

# 18. API Resources

Gunakan Laravel API Resource / transformer.

Contoh:

```text
ItemResource
WarehouseResource
InventoryResource
AssetResource
TransactionResource
```

Tujuannya menjaga response konsisten.

---

# 19. Response Structure

Contoh successful response:

```json
{
  "data": {
    "id": 123,
    "name": "Laptop",
    "sku": "ITM-001"
  }
}
```

List:

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

Structure final dapat disesuaikan dengan Laravel resource implementation.

---

# 20. Error Response

Gunakan format konsisten.

Contoh:

```json
{
  "message": "Validation failed.",
  "errors": {
    "sku": ["The SKU has already been taken."]
  }
}
```

---

# 21. HTTP Status Codes

Gunakan status code yang sesuai.

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

---

# 22. Items API

Minimal:

```text
GET    /api/v1/items
POST   /api/v1/items
GET    /api/v1/items/{id}
PATCH  /api/v1/items/{id}
DELETE /api/v1/items/{id}
```

Permission mengikuti RBAC.

---

# 23. Warehouses API

```text
GET    /api/v1/warehouses
POST   /api/v1/warehouses
GET    /api/v1/warehouses/{id}
PATCH  /api/v1/warehouses/{id}
DELETE /api/v1/warehouses/{id}
```

Warehouse scope tetap berlaku.

---

# 24. Inventory API

Read-only inventory:

```text
GET /api/v1/inventory
GET /api/v1/inventory/{id}
```

Inventory mutation tidak boleh dilakukan sembarangan.

Stock harus berubah melalui business process:

```text
Stock In
Stock Out
Adjustment
Stock Opname
```

---

# 25. Stock API

Contoh:

```text
GET /api/v1/stock
GET /api/v1/stock/balance
GET /api/v1/stock/movement
```

Stock movement mengambil source of truth dari inventory ledger.

---

# 26. Stock In API

Jika API diperbolehkan membuat Stock In:

```text
POST /api/v1/stock-in
GET  /api/v1/stock-in/{id}
```

Business logic tetap menggunakan service yang sama dengan Web.

Flow:

```text
API
 ↓
Request Validation
 ↓
Authorization
 ↓
StockInService
 ↓
Inventory
 ↓
Ledger
 ↓
Transaction
 ↓
Audit
```

---

# 27. Stock Out API

```text
POST /api/v1/stock-out
GET  /api/v1/stock-out/{id}
```

Flow:

```text
API
 ↓
StockOutService
 ↓
Validate Stock
 ↓
Business Transaction
 ↓
Inventory Ledger
 ↓
Audit Log
```

API tidak boleh langsung:

```text
UPDATE inventory SET stock = ...
```

tanpa business service.

---

# 28. Asset API

```text
GET   /api/v1/assets
POST  /api/v1/assets
GET   /api/v1/assets/{id}
PATCH /api/v1/assets/{id}
```

Asset actions mengikuti business rules:

```text
Assign
Return
Transfer
Status Change
```

---

# 29. Transaction API

```text
GET /api/v1/transactions
GET /api/v1/transactions/{id}
```

Transaction history bersifat read-only.

---

# 30. Reports API

Reporting dapat diekspos sebagai read-only API.

Contoh:

```text
GET /api/v1/reports/stock-balance
GET /api/v1/reports/stock-movement
GET /api/v1/reports/assets
GET /api/v1/reports/transactions
```

Report menggunakan service dari Reporting module.

---

# 31. Filtering

Contoh:

```text
GET /api/v1/items?category_id=10
```

atau:

```text
GET /api/v1/transactions?type=STOCK_OUT
```

Filter harus divalidasi.

User tidak boleh menggunakan filter untuk melewati permission.

---

# 32. Date Filtering

Contoh:

```text
GET /api/v1/reports/stock-movement
    ?from=2026-08-01
    &to=2026-08-30
```

Backend harus:

```text
Validate Date
Apply Scope
Query Database
```

---

# 33. Pagination

Default:

```text
20 records
```

Client dapat meminta:

```text
?page=2&per_page=50
```

Maximum:

```text
100
```

atau batas lain sesuai implementation.

Jangan mengizinkan:

```text
per_page=1000000
```

---

# 34. Sorting

Contoh:

```text
?sort=created_at
```

Descending:

```text
?sort=-created_at
```

Hanya field yang di-whitelist boleh digunakan.

Jangan memasukkan input sorting mentah ke SQL.

---

# 35. Search

Contoh:

```text
GET /api/v1/items?search=laptop
```

Search field harus ditentukan oleh endpoint.

Jangan membuat search:

```text
WHERE every_column LIKE ...
```

tanpa alasan.

---

# 36. API Rate Limiting

API harus memiliki rate limit.

Contoh konsep:

```text
Authenticated
→ higher limit

Unauthenticated
→ lower limit
```

Limit final ditentukan saat implementation dan load testing.

Jika limit terlampaui:

```text
429 Too Many Requests
```

---

# 37. API Security

Protect against:

```text
Authentication Bypass
Authorization Bypass
IDOR
Mass Assignment
Injection
Sensitive Data Exposure
Token Leakage
Rate Abuse
```

---

# 38. Mass Assignment Protection

Gunakan:

```text
fillable
validated input
DTO / Request validation
```

Jangan:

```php
Model::create($request->all());
```

gunakan data yang sudah divalidasi dan diizinkan.

---

# 39. Sensitive Response

API tidak boleh mengembalikan:

```text
Password
Password Hash
API Secret
Token
Private Key
Internal Security Data
```

kecuali memang diperlukan oleh authentication flow dan tetap mengikuti security design.

---

# 40. Transaction Integrity

Untuk business operation kompleks:

```text
DB Transaction
```

harus digunakan di business service.

Contoh Stock Out:

```text
BEGIN
 ↓
Validate
 ↓
Update Inventory
 ↓
Create Ledger
 ↓
Create Transaction
 ↓
Create Audit
 ↓
COMMIT
```

Jika gagal:

```text
ROLLBACK
```

---

# 41. API + Business Service

Architecture:

```text
                ┌── Inertia Web
                │
Client ─────────┼── REST API
                │
                └── Future Mobile
                       │
                       ▼
                Business Services
                       │
                       ▼
                    Database
```

Business rule berada di:

```text
Service / Domain Layer
```

bukan di controller.

---

# 42. Controller Structure

```text
app/Http/Controllers/API/V1/
├── AuthController.php
├── ItemController.php
├── WarehouseController.php
├── InventoryController.php
├── StockController.php
├── AssetController.php
├── TransactionController.php
└── ReportController.php
```

---

# 43. API Resources

```text
app/Http/Resources/API/V1/
├── ItemResource.php
├── WarehouseResource.php
├── InventoryResource.php
├── AssetResource.php
└── TransactionResource.php
```

---

# 44. Routes

```text
routes/
└── api.php
```

Concept:

```php
Route::prefix('v1')->group(function () {
    // API routes
});
```

Actual routes harus mengikuti authorization dan module structure.

---

# 45. API Documentation

Dokumentasikan:

```text
Endpoint
Method
Authentication
Permission
Parameters
Request Body
Response
Error
Example
```

Contoh:

```text
GET /api/v1/items
```

```text
Authentication:
Bearer Token

Permission:
items.view

Query:
search
category_id
page
per_page
sort
```

---

# 46. OpenAPI

Gunakan OpenAPI/Swagger jika diperlukan untuk dokumentasi interaktif.

Dokumentasi harus mencerminkan API yang benar-benar tersedia.

Jangan membuat dokumentasi endpoint yang belum diimplementasikan.

---

# 47. API Testing

Minimal test:

```text
Authentication
Authorization
CRUD
Validation
Pagination
Filtering
Sorting
Warehouse Scope
IDOR
Rate Limit
Error Response
```

---

# 48. Authentication Testing

```text
[ ] Valid token accepted
[ ] Invalid token rejected
[ ] Expired/revoked token rejected
[ ] Missing token rejected
```

---

# 49. Authorization Testing

```text
[ ] Allowed permission succeeds
[ ] Missing permission blocked
[ ] Warehouse scope enforced
[ ] Admin access works
[ ] Staff restrictions work
```

---

# 50. IDOR Testing

Contoh:

```text
User A
→ GET /items/123
```

Jika item 123 bukan miliknya/scope-nya:

```text
→ Access denied
```

Test juga:

```text
Assets
Transactions
Warehouses
Reports
```

---

# 51. Validation Testing

Test:

```text
[ ] Missing required field
[ ] Invalid data type
[ ] Invalid ID
[ ] Duplicate SKU
[ ] Invalid date
[ ] Invalid filter
[ ] Invalid pagination
```

---

# 52. Performance Testing

Periksa:

```text
[ ] No N+1
[ ] Pagination
[ ] Index usage
[ ] Query time
[ ] Large dataset
```

Gunakan:

```text
EXPLAIN
```

untuk query penting.

---

# 53. API Logging

Application log dapat mencatat:

```text
Request ID
Endpoint
Method
Status
Duration
Exception
```

Jangan mencatat:

```text
Authorization Token
Password
Secrets
Sensitive Request Body
```

---

# 54. Request ID

Gunakan request identifier untuk debugging.

Contoh:

```text
X-Request-ID
```

Flow:

```text
Client
 ↓
Request ID
 ↓
API
 ↓
Application Log
```

Memudahkan tracing request tanpa menyimpan credential.

---

# 55. API Documentation Standard

Setiap endpoint harus menjelaskan:

```text
Purpose
Authentication
Permission
Input
Validation
Response
Errors
Business Rule
Example
```

Dokumentasi API tidak boleh hanya berisi URL.

---

# 56. Code Documentation

Setiap file mengikuti:

```text
docs/code-guide/00_CODE_DOCUMENTATION_STANDARD.md
```

Contoh:

```php
/**
 * Item API Controller
 *
 * Purpose:
 * Expose item resources through REST API v1.
 *
 * Responsibility:
 * - Receive API request
 * - Validate request
 * - Authorize user
 * - Call Item Service
 * - Return API Resource
 *
 * Important:
 * Business rules must remain inside
 * the business/service layer.
 *
 * Security:
 * All item access must respect RBAC
 * and warehouse scope where applicable.
 */
```

---

# 57. Maintenance Guide

### "Saya ingin menambahkan endpoint."

Trace:

```text
routes/api.php
 ↓
API Controller
 ↓
Form Request
 ↓
Business Service
 ↓
API Resource
```

---

### "API menghasilkan data berbeda dengan Web."

Periksa:

```text
API Controller
 ↓
Business Service
```

Pastikan API tidak mempunyai business logic duplikat.

---

### "API bisa mengakses data warehouse lain."

Trace:

```text
Authentication
 ↓
Authorization
 ↓
Warehouse Scope
 ↓
Query
```

---

### "Response API ingin diubah."

Cari:

```text
API Resource
```

bukan langsung mengubah model database.

---

# 58. Code Understanding Map

```text
Client
  ↓
routes/api.php
  ↓
API Controller
  ↓
Form Request
  ↓
Authorization
  ↓
Business Service
  ↓
Model / Query
  ↓
Database
  ↓
API Resource
  ↓
JSON Response
```

Untuk memahami endpoint:

```text
1. Cari route
2. Cari controller
3. Cari validation
4. Cari authorization
5. Cari service
6. Cari query
7. Cari resource
8. Lihat response
```

---

# 59. Acceptance Criteria

Sprint selesai apabila:

```text
1. REST API v1 tersedia.

2. API versioning tersedia.

3. Authentication tersedia.

4. Logout tersedia.

5. Current user endpoint tersedia.

6. RBAC diterapkan.

7. Warehouse scope diterapkan.

8. IDOR protection tersedia.

9. Request validation tersedia.

10. API Resource tersedia.

11. Error response konsisten.

12. HTTP status code digunakan dengan benar.

13. Pagination tersedia.

14. Filtering tersedia.

15. Sorting tersedia.

16. Search tersedia untuk resource yang relevan.

17. Rate limiting tersedia.

18. Sensitive data tidak dikembalikan.

19. Mass assignment protection tersedia.

20. Business logic menggunakan service yang sama dengan Web.

21. Stock mutation mengikuti business process.

22. Transaction integrity diterapkan.

23. API documentation tersedia.

24. OpenAPI/Swagger tersedia jika dipilih pada implementation.

25. Authentication tests berhasil.

26. Authorization tests berhasil.

27. IDOR tests berhasil.

28. Validation tests berhasil.

29. Performance tests berhasil.

30. API query tidak memiliki N+1 yang tidak perlu.

31. Query penting telah dievaluasi dengan EXPLAIN.

32. Code documentation mengikuti standard Inventra.

33. Developer dapat tracing Client → Route → Controller → Service → Database → Resource.
```

---

# 60. Expected Files

```text
app/
├── Http/
│   ├── Controllers/
│   │   └── API/
│   │       └── V1/
│   │           ├── AuthController.php
│   │           ├── ItemController.php
│   │           ├── WarehouseController.php
│   │           ├── InventoryController.php
│   │           ├── StockController.php
│   │           ├── AssetController.php
│   │           ├── TransactionController.php
│   │           └── ReportController.php
│   │
│   ├── Requests/
│   │   └── API/
│   │
│   └── Resources/
│       └── API/
│           └── V1/
│
routes/
└── api.php

tests/
└── Feature/
    └── API/
        └── V1/
```

---

# 61. Git Branch

```text
feature/rest-api
```

Dependency:

```text
SPRINT-01 → Authentication
SPRINT-02 → RBAC
SPRINT-03+ → Business Modules
SPRINT-11 → Transaction History
SPRINT-12 → Reporting
SPRINT-14 → Audit Log
SPRINT-15 → REST API
```

---

# 62. Suggested Commits

```text
feat(api): add api v1 structure
feat(api): add authentication endpoints
feat(api): add item endpoints
feat(api): add warehouse endpoints
feat(api): add inventory endpoints
feat(api): add stock endpoints
feat(api): add asset endpoints
feat(api): add transaction endpoints
feat(api): add report endpoints
feat(api): add api resources
feat(api): add request validation
feat(api): add api authorization
feat(api): add warehouse scope
feat(api): add api pagination
feat(api): add api filtering
feat(api): add api sorting
feat(api): add api rate limiting
feat(api): add api error handling
docs(api): add api documentation
test(api): add authentication tests
test(api): add authorization tests
test(api): add idor tests
test(api): add validation tests
perf(api): optimize api queries
```

---

# 63. Definition of Done

```text
API
    ✓ Versioned
    ✓ Authenticated
    ✓ Authorized
    ✓ Validated
    ✓ Documented

Resources
    ✓ Items
    ✓ Warehouses
    ✓ Inventory
    ✓ Stock
    ✓ Assets
    ✓ Transactions
    ✓ Reports

Security
    ✓ RBAC
    ✓ Warehouse Scope
    ✓ IDOR Protection
    ✓ Rate Limit
    ✓ Sensitive Data Protection
    ✓ Mass Assignment Protection

Performance
    ✓ Pagination
    ✓ No N+1
    ✓ Index Review
    ✓ EXPLAIN

Architecture
    ✓ Shared Business Services
    ✓ No Duplicate Business Logic
    ✓ Transaction Integrity

Testing
    ✓ Authentication
    ✓ Authorization
    ✓ Validation
    ✓ IDOR
    ✓ Performance

Documentation
    ✓ Endpoint Documentation
    ✓ Code Comments
    ✓ Maintenance Guide

Git
    ✓ feature/rest-api
```

---

# 64. Final API Architecture

```text
                         CLIENT
                           │
                ┌──────────┴──────────┐
                │                     │
             Web App              External
                │                   Client
                │                     │
             Inertia               REST API
                │                     │
                └──────────┬──────────┘
                           ▼
                  Laravel Application
                           │
                 ┌─────────┴─────────┐
                 ▼                   ▼
           Authorization        Validation
                 │                   │
                 └─────────┬─────────┘
                           ▼
                  Business Services
                           │
             ┌─────────────┼─────────────┐
             ▼             ▼             ▼
         Inventory       Assets      Transactions
             │             │             │
             └─────────────┼─────────────┘
                           ▼
                        Database
                           │
                           ▼
                     API Resource
                           │
                           ▼
                        JSON API
```

---

# 65. Key Principle

REST API Inventra adalah **interface**, bukan business layer baru.

```text
Web
 ↓
Business Logic

API
 ↓
Business Logic

Mobile
 ↓
Business Logic
```

Semua harus berakhir pada logic yang sama.

Dengan begitu:

```text
Stock Out dari Web
```

dan:

```text
Stock Out dari API
```

harus menghasilkan business result yang konsisten:

```text
Inventory
+
Ledger
+
Transaction
+
Audit Log
```

Bukan masing-masing mempunyai implementasi berbeda.

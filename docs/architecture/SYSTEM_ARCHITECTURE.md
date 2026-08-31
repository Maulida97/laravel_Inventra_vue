# Inventra

## System Architecture

**Document:** System Architecture
**Version:** V1.0
**Status:** Draft

---

# 1. Architecture Overview

Inventra menggunakan **modular layered architecture** dengan Laravel sebagai backend utama, Inertia.js sebagai bridge antara Laravel dan Vue.js, serta PostgreSQL sebagai database.

```text
                    USER
                     │
                     ▼
                Vue.js UI
                     │
                     ▼
                 Inertia.js
                     │
                     ▼
                  Laravel
                     │
          ┌──────────┼──────────┐
          ▼          ▼          ▼
      Policies    Services    Form Requests
          │          │          │
          └──────────┼──────────┘
                     ▼
              Domain Logic
                     │
                     ▼
              Eloquent ORM
                     │
                     ▼
                PostgreSQL
```

REST API menggunakan backend Laravel yang sama:

```text
External Client
      │
      ▼
   REST API
      │
      ▼
    Laravel
      │
      ▼
 Business Logic
      │
      ▼
 PostgreSQL
```

---

# 2. Technology Stack

| Layer             | Technology                                       |
| ----------------- | ------------------------------------------------ |
| Backend           | Laravel                                          |
| Frontend          | Vue.js                                           |
| Bridge            | Inertia.js                                       |
| Database          | PostgreSQL                                       |
| ORM               | Laravel Eloquent                                 |
| API               | Laravel REST API                                 |
| Authentication    | Laravel Authentication                           |
| Authorization     | Policies / Gates + RBAC                          |
| Containerization  | Docker                                           |
| Testing           | Laravel Testing + PHPUnit/Pest + Browser Testing |
| Version Control   | Git                                              |
| API Documentation | OpenAPI                                          |
| Frontend Build    | Vite                                             |

---

# 3. Architecture Style

Inventra menggunakan kombinasi:

```text
Layered Architecture
+
Modular Architecture
+
Service-oriented Business Logic
```

Tujuannya bukan membuat architecture yang terlalu kompleks, tetapi menjaga agar:

```text
UI
≠
Business Logic
≠
Database
```

Setiap layer memiliki tanggung jawab yang jelas.

---

# 4. Application Layers

## 4.1 Presentation Layer

Bertanggung jawab terhadap:

- UI
- Form
- Table
- Filter
- Modal
- User interaction
- Display validation error

Technology:

```text
Vue.js
Inertia.js
```

Contoh:

```text
resources/js/Pages/StockIn/Index.vue
resources/js/Pages/StockIn/Create.vue
```

Presentation layer tidak boleh menjadi tempat utama business logic inventory.

---

# 5. HTTP Layer

HTTP layer menangani request dari browser/API.

Contoh:

```text
Controller
Form Request
Middleware
Policy
```

Flow:

```text
Request
   ↓
Middleware
   ↓
Authentication
   ↓
Authorization
   ↓
Form Request Validation
   ↓
Controller
```

Controller harus tetap tipis.

---

# 6. Controller Responsibility

Controller bertugas sebagai orchestrator HTTP.

Contoh:

```text
StockInController
```

bertanggung jawab:

```text
Receive Request
      ↓
Validate
      ↓
Authorize
      ↓
Call Service
      ↓
Return Response
```

Controller tidak boleh menangani seluruh proses inventory secara langsung.

Hindari:

```php
public function store()
{
    // 200+ lines of stock logic
}
```

Lebih baik:

```text
Controller
    ↓
StockInService
    ↓
InventoryService
```

---

# 7. Form Request

Form Request digunakan untuk validation.

Contoh:

```text
StoreStockInRequest
```

Menangani:

```text
quantity
unit_id
content_per_unit
warehouse_id
location_id
```

Flow:

```text
HTTP Request
     ↓
Form Request
     ↓
Validation
     ↓
Controller
```

Validation format tidak dicampur dengan business logic.

---

# 8. Authorization Layer

Authorization menggunakan:

```text
Role
+
Permission
+
Scope
+
Policy
```

Flow:

```text
Authenticated User
       ↓
Role
       ↓
Permission
       ↓
Policy
       ↓
Scope
       ↓
Resource
```

Contoh:

```text
Warehouse Staff
       ↓
stock.out
       ↓
Warehouse Policy
       ↓
WH-001 Scope
       ↓
Allowed
```

---

# 9. Business Logic Layer

Business logic diletakkan pada Service/Domain layer.

Contoh:

```text
StockInService
StockOutService
StockTransferService
StockOpnameService
ApprovalService
AssetService
PurchaseRequestService
```

Tujuan:

```text
Controller
    ↓
Service
    ↓
Business Rule
```

Business logic dapat digunakan oleh:

```text
Web
API
Jobs
Console Commands
```

tanpa duplikasi logic.

---

# 10. Inventory Service

Inventory merupakan core system Inventra.

Struktur konsep:

```text
InventoryService
       │
       ├── Stock In
       ├── Stock Out
       ├── Transfer
       ├── Return
       └── Adjustment
```

Semua perubahan stock harus melewati inventory business logic.

Tidak boleh:

```text
Controller
 ↓
UPDATE stock_balances
```

secara langsung tanpa proses inventory.

---

# 11. Inventory Ledger Architecture

Stock Balance bukan sumber history utama.

Arsitektur:

```text
Inventory Transaction
        ↓
Inventory Ledger
        ↓
Stock Balance
```

Ledger mencatat perubahan stock.

Contoh:

```text
STOCK_IN
+600 L

STOCK_OUT
-200 L

TRANSFER
-100 L
```

Current balance:

```text
300 L
```

---

# 12. Transaction Integrity

Inventory operation menggunakan database transaction.

Contoh Stock In:

```text
BEGIN TRANSACTION
        ↓
Validate
        ↓
Create Inventory Transaction
        ↓
Create Ledger
        ↓
Update Stock Balance
        ↓
Create Audit Log
        ↓
COMMIT
```

Jika terjadi error:

```text
ROLLBACK
```

Tidak boleh terjadi kondisi:

```text
Ledger berhasil
+
Stock Balance gagal
```

---

# 13. Database Architecture

PostgreSQL digunakan sebagai primary relational database.

Conceptual structure:

```text
Users
Roles
Permissions
Departments
Items
Categories
Units
Warehouses
Locations
Suppliers
        │
        ▼
Inventory Transactions
        │
        ├── Inventory Ledger
        │
        └── Stock Balance
```

Relational integrity menggunakan:

```text
Primary Key
Foreign Key
Unique Constraint
Check Constraint
Index
Composite Index
```

---

# 14. Database Performance

Query performance diperhatikan sejak design.

Teknik utama:

```text
Index
Composite Index
Foreign Key Index
Pagination
Eager Loading
Avoid N+1 Query
Select Required Columns
Query Optimization
EXPLAIN / EXPLAIN ANALYZE
```

Index dibuat berdasarkan pola query aktual, bukan seluruh kolom secara otomatis.

---

# 15. Frontend Architecture

Vue.js digunakan untuk presentation layer.

Struktur konseptual:

```text
resources/js/

├── Pages/
├── Components/
├── Layouts/
├── Composables/
└── Types/
```

Contoh:

```text
Pages/
└── StockIn/
    ├── Index.vue
    ├── Create.vue
    └── Show.vue
```

Reusable UI ditempatkan di:

```text
Components/
```

---

# 16. Inertia Architecture

Inertia digunakan sebagai bridge.

```text
Browser
   ↓
Vue Page
   ↓
Inertia Request
   ↓
Laravel Route
   ↓
Controller
   ↓
Service
   ↓
Database
   ↓
Inertia Response
   ↓
Vue Page
```

Inventra tidak perlu membangun SPA API layer khusus untuk setiap halaman internal.

---

# 17. REST API Architecture

REST API tersedia untuk integration dan external client.

```text
External Client
       ↓
/api/v1
       ↓
API Middleware
       ↓
API Controller
       ↓
Service
       ↓
Database
```

API tidak memiliki business logic inventory yang berbeda dari web.

---

# 18. Web vs API

```text
                 BUSINESS LOGIC
                 /            \
                /              \
             WEB                API
              │                  │
          Inertia             REST API
              │                  │
              └────────┬─────────┘
                       ▼
                    Laravel
                       │
                       ▼
                  PostgreSQL
```

Contoh:

```text
Stock Out via Web
        ↓
StockOutService
```

dan:

```text
Stock Out via API
        ↓
StockOutService
```

Keduanya menjalankan business rule yang sama.

---

# 19. Authentication Architecture

Authentication flow:

```text
User
 ↓
Login
 ↓
Credential Validation
 ↓
Authenticated Session
 ↓
User
```

API menggunakan authentication mechanism yang sesuai untuk API client.

Password tidak pernah disimpan dalam plain text.

---

# 20. Authorization Architecture

Authorization flow:

```text
Request
 ↓
Authentication
 ↓
Role
 ↓
Permission
 ↓
Scope
 ↓
Policy
 ↓
Business Rule
 ↓
Action
```

Default:

```text
DENY
```

jika permission/scope tidak memenuhi requirement.

---

# 21. Scope Architecture

Inventra menggunakan scope untuk membatasi resource.

### Department Scope

```text
User
 ↓
Department
 ↓
Allowed Items
```

### Warehouse Scope

```text
User
 ↓
Warehouse
 ↓
Location
```

### Location Scope

```text
User
 ↓
Warehouse
 ↓
Location
```

Scope diperiksa di backend.

---

# 22. Procurement Architecture

Procurement flow:

```text
Department Staff
       ↓
Purchase Request
       ↓
Approval
       ↓
Purchase Order
       ↓
Supplier
       ↓
Receiving
       ↓
Stock In
       ↓
Inventory Ledger
```

Setiap tahap memiliki status dan authorization masing-masing.

---

# 23. Approval Architecture

Approval dibuat sebagai reusable subsystem.

```text
Business Transaction
       ↓
Approval Request
       ↓
Workflow
       ↓
Approval Step
       ↓
Approver
       ↓
Approve / Reject
```

Dapat digunakan oleh:

```text
Purchase Request
Stock Out
Stock Adjustment
Stock Opname
Asset Disposal
```

jika business rule membutuhkan approval.

---

# 24. Asset Architecture

Asset berbeda dari consumable inventory.

```text
Inventory
   ↓
Asset Registration
   ↓
Asset
   ↓
Assignment
   ↓
Custodian
   ↓
Return / Maintenance
   ↓
Disposal
```

Asset memiliki lifecycle dan history tersendiri.

---

# 25. Audit Architecture

Aktivitas penting menghasilkan Audit Log.

```text
User Action
     ↓
Business Operation
     ↓
Audit Log
```

Informasi dapat mencakup:

```text
User
Action
Resource
Resource ID
Before
After
Timestamp
IP
User Agent
```

Audit log bersifat append-oriented dan tidak dapat diedit oleh user biasa.

---

# 26. Error Handling

Error handling menggunakan beberapa level:

```text
Validation Error
       ↓
422

Authentication Error
       ↓
401

Authorization Error
       ↓
403

Not Found
       ↓
404

Business Conflict
       ↓
409

Server Error
       ↓
500
```

Error response tidak boleh membocorkan internal implementation.

---

# 27. Logging

Application logging digunakan untuk:

```text
Error
Warning
Important System Event
Integration Error
Security Event
```

Log berbeda dengan Audit Log.

```text
Application Log
→ Technical/System

Audit Log
→ User/Business Activity
```

---

# 28. Queue & Background Processing

Jika diperlukan, pekerjaan berat dapat dipindahkan ke queue.

Contoh:

```text
Large Export
Report Generation
Notification
Bulk Processing
```

Flow:

```text
Request
 ↓
Job
 ↓
Queue
 ↓
Worker
 ↓
Result
```

Queue tidak digunakan untuk business logic yang membutuhkan immediate transaction consistency tanpa alasan yang jelas.

---

# 29. File Storage

Jika Inventra membutuhkan attachment:

```text
Purchase Request
Receiving
Asset
Audit Evidence
```

file disimpan menggunakan storage abstraction Laravel.

Database menyimpan metadata:

```text
filename
path
mime_type
size
uploaded_by
```

Bukan binary file besar secara langsung di tabel utama.

---

# 30. Docker Architecture

Development environment:

```text
Docker Compose
       │
       ├── App Container
       │      └── Laravel
       │
       ├── Database Container
       │      └── PostgreSQL
       │
       └── Supporting Services
```

Tujuannya agar environment development konsisten.

---

# 31. Environment Separation

Inventra memiliki environment:

```text
Local
Development
Testing
Production
```

Configuration sensitif menggunakan environment variables.

Contoh:

```text
APP_KEY
DB_DATABASE
DB_USERNAME
DB_PASSWORD
API_KEYS
```

Credentials tidak disimpan di repository.

---

# 32. Security Boundary

Security layer berada di beberapa titik:

```text
Browser
   ↓
HTTPS
   ↓
Authentication
   ↓
Authorization
   ↓
Validation
   ↓
Business Logic
   ↓
Database
```

Security bukan hanya responsibility frontend.

---

# 33. Code Organization

Backend secara konseptual:

```text
app/
├── Http/
│   ├── Controllers/
│   ├── Requests/
│   └── Resources/
│
├── Models/
├── Policies/
├── Services/
├── Actions/
├── Jobs/
└── Support/
```

Tidak semua folder harus dibuat sejak awal.

Folder dibuat ketika memang memiliki kebutuhan.

---

# 34. Code Documentation

Setiap feature harus mengikuti:

```text
docs/code-guide/
```

Standard utama:

```text
docs/code-guide/00_CODE_DOCUMENTATION_STANDARD.md
```

Dokumentasi harus membantu developer memahami:

```text
Purpose
Flow
Files
Business Logic
Database
Authorization
Validation
Error Handling
Maintenance
```

---

# 35. Code Comment Standard

Komentar digunakan pada bagian yang membutuhkan konteks.

Contoh:

```php
// Convert received quantity into the item's base unit.
// Content per unit is manually entered because package contents
// may differ between brands or physical packages.
```

Komentar tidak digunakan untuk menjelaskan hal yang sudah obvious:

```php
// Set quantity to quantity
$quantity = $quantity;
```

Tidak diperlukan.

---

# 36. Feature Traceability

Setiap feature harus dapat ditrace:

```text
PRD
 ↓
Module
 ↓
User Flow
 ↓
API
 ↓
Permission
 ↓
Database
 ↓
Code
 ↓
Test
 ↓
Sprint
```

Contoh Stock In:

```text
INV-001
 ↓
Stock In
 ↓
StockInController
 ↓
StockInService
 ↓
InventoryTransaction
 ↓
InventoryLedger
 ↓
StockBalance
 ↓
AuditLog
```

---

# 37. Maintenance Principle

Developer harus dapat menemukan source of behavior.

Contoh:

> "Bagaimana Stock Out mengurangi stock?"

Trace:

```text
Stock Out Page
      ↓
Route
      ↓
Controller
      ↓
StockOutService
      ↓
InventoryService
      ↓
Ledger
      ↓
Stock Balance
```

Dokumentasi dan naming harus membuat flow tersebut mudah ditemukan.

---

# 38. Architecture Decision Principle

Keputusan architecture yang penting dicatat pada:

```text
docs/decisions/ADR/
```

Contoh:

```text
ADR-001-POSTGRESQL.md
ADR-002-INERTIA-VUE.md
ADR-003-INVENTORY-LEDGER.md
```

Tujuannya menjelaskan:

```text
Why?
Alternatives?
Decision?
Trade-offs?
```

Bukan hanya:

```text
What technology?
```

---

# 39. Scalability Principle

Inventra tidak melakukan premature optimization.

Prioritas:

```text
Correctness
 ↓
Maintainability
 ↓
Security
 ↓
Performance
 ↓
Scalability
```

Optimasi dilakukan berdasarkan evidence:

```text
Slow Query
 ↓
Measure
 ↓
EXPLAIN ANALYZE
 ↓
Optimize
 ↓
Test Again
```

---

# 40. System Architecture Summary

```text
                         INVENTRA
                            │
              ┌─────────────┴─────────────┐
              │                           │
           WEB APP                     REST API
              │                           │
           Inertia                     JSON
              │                           │
           Vue.js                    Laravel API
              │                           │
              └─────────────┬─────────────┘
                            │
                       Laravel Core
                            │
          ┌─────────────────┼─────────────────┐
          │                 │                 │
    Authorization      Business Logic      Audit
          │                 │                 │
          └─────────────────┼─────────────────┘
                            │
                       Eloquent ORM
                            │
                            ▼
                       PostgreSQL
                            │
                            ▼
                    Inventory Data
```

Core principle:

```text
UI
 ↓
HTTP Layer
 ↓
Authorization
 ↓
Business Logic
 ↓
Database
 ↓
Audit
```

Inventra dirancang agar setiap perubahan inventory dapat ditelusuri, setiap akses dapat dikontrol, dan setiap feature dapat dipahami serta dipelihara tanpa bergantung pada AI-generated code.

# MODULE ARCHITECTURE

**Project:** Inventra
**Document:** Module Architecture
**Version:** 1.0
**Status:** Accepted

---

# 1. Purpose

Dokumen ini menjelaskan bagaimana modul-modul utama Inventra disusun, bagaimana hubungan antar modul, serta batas tanggung jawab masing-masing modul.

Dokumen ini menjadi acuan ketika:

- Membuat module baru
- Membuat service
- Membuat controller
- Membuat model
- Membuat policy
- Membuat API
- Membuat frontend page/component
- Melakukan maintenance
- Menentukan dependency antar module

Dokumen ini fokus pada **struktur internal aplikasi**, bukan detail implementasi setiap fitur.

---

# 2. Architecture Overview

Inventra menggunakan modular monolith architecture.

Konsep utama:

```text
                    INVENTRA
                       │
        ┌──────────────┴──────────────┐
        │                             │
     Frontend                     Backend
        │                             │
    Inertia + Vue                 Laravel
                                      │
                     ┌────────────────┼────────────────┐
                     │                │                │
                 Domain           Application       Infrastructure
                     │                │                │
                     └────────────────┼────────────────┘
                                      │
                                  PostgreSQL
```

Pada V1, seluruh module berada dalam satu application.

Tidak menggunakan microservices.

---

# 3. Module Architecture Principle

Setiap module harus memiliki:

```text
Responsibility
Business Rules
Input Validation
Authorization
Data Access
Application Flow
```

Module harus memiliki batas tanggung jawab yang jelas.

Prinsip:

```text
High Cohesion
Low Coupling
Single Responsibility
Explicit Dependency
Reusable Service
```

---

# 4. High-Level Modules

Inventra memiliki modul utama:

```text
Authentication
RBAC
Master Data
Item Management
Warehouse
Inventory
Stock In
Stock Out
Stock Opname
Asset Management
Approval Workflow
Transaction History
Reporting
Dashboard
Audit Log
```

---

# 5. Module Dependency Overview

Secara konseptual:

```text
                    Authentication
                          │
                          ▼
                         RBAC
                          │
             ┌────────────┼────────────┐
             ▼            ▼            ▼
        Master Data     Users       Permissions
             │
             ▼
       Item Management
             │
             ▼
          Warehouse
             │
             ▼
         Inventory
             │
       ┌─────┼─────┐
       ▼     ▼     ▼
   Stock In Stock Out Stock Opname
       │     │     │
       └─────┼─────┘
             ▼
      Inventory Ledger
             │
             ▼
       Transaction History
             │
       ┌─────┴─────┐
       ▼           ▼
   Reporting    Dashboard

Approval Workflow
       │
       └── Stock Transactions

Audit Log
   ↑
   └── Important Business Events
```

Diagram tersebut menunjukkan dependency konseptual, bukan berarti setiap module boleh mengakses module lain secara langsung.

---

# 6. Authentication Module

## Responsibility

Authentication bertanggung jawab terhadap:

```text
Login
Logout
Session
Authentication State
Password Management
```

## Depends On

```text
User
RBAC
```

## Tidak bertanggung jawab terhadap

```text
Business Authorization
Inventory
Transaction
Approval
```

Authentication menjawab:

> "Siapa user ini?"

Authorization menjawab:

> "Apa yang boleh dilakukan user ini?"

---

# 7. RBAC Module

## Responsibility

```text
Roles
Permissions
User Roles
Role Permissions
Resource Scope
Authorization
```

## Depends On

```text
Authentication
User
```

## Digunakan Oleh

Hampir seluruh business module.

```text
Inventory
Stock In
Stock Out
Stock Opname
Assets
Reports
Approval
Audit
```

RBAC menjadi **cross-cutting authorization module**.

---

# 8. Master Data Module

## Responsibility

Mengelola data referensi yang digunakan oleh module lain.

Contoh:

```text
Category
Unit
Supplier
Warehouse
Location
```

## Dependency

Master Data menjadi dependency dari:

```text
Item
Inventory
Stock In
Stock Out
Stock Opname
Asset
Reporting
```

Master data harus stabil dan memiliki validation.

---

# 9. Item Management Module

## Responsibility

Mengelola item inventory.

Contoh:

```text
Item
Item Code
Item Name
Category
Unit
Status
```

## Depends On

```text
Master Data
RBAC
```

## Used By

```text
Inventory
Stock In
Stock Out
Stock Opname
Reporting
```

Item Management tidak bertanggung jawab terhadap perubahan quantity.

Quantity merupakan responsibility Inventory.

---

# 10. Warehouse Module

## Responsibility

Mengelola:

```text
Warehouse
Location
Warehouse Scope
```

## Depends On

```text
Master Data
RBAC
```

## Used By

```text
Inventory
Stock In
Stock Out
Stock Opname
Asset
Reporting
```

Warehouse menentukan **di mana inventory berada**.

---

# 11. Inventory Module

Inventory merupakan salah satu core module Inventra.

## Responsibility

```text
Inventory Balance
Stock Availability
Inventory Movement
Inventory Ledger
Stock Validation
```

Konsep:

```text
Inventory Balance
        +
Inventory Ledger
```

Inventory Balance menunjukkan current state.

Inventory Ledger menunjukkan historical movement.

---

# 12. Inventory Ledger

Ledger menjadi sumber historical movement inventory.

Contoh:

```text
Stock In
   ↓
+100

Stock Out
   ↓
-20

Adjustment
   ↓
-5
```

Ledger:

```text
+100
-20
-5
```

Current balance:

```text
75
```

## Important Rule

Stock transaction tidak boleh mengubah quantity secara sembarangan.

Perubahan inventory harus melalui mekanisme inventory yang konsisten.

---

# 13. Stock In Module

## Responsibility

Mengelola penerimaan stock.

Workflow:

```text
DRAFT
  ↓
SUBMITTED
  ↓
APPROVED
  ↓
POSTED
```

atau:

```text
SUBMITTED
  ↓
REJECTED
```

## Depends On

```text
Authentication
RBAC
Item
Warehouse
Inventory
Approval
Audit
```

## Main Flow

```text
Create
 ↓
Validate
 ↓
Submit
 ↓
Approval
 ↓
Inventory Movement
 ↓
Ledger
 ↓
Audit
```

---

# 14. Stock Out Module

## Responsibility

Mengelola pengeluaran stock.

Workflow:

```text
DRAFT
  ↓
SUBMITTED
  ↓
APPROVED
  ↓
POSTED
```

atau:

```text
SUBMITTED
  ↓
REJECTED
```

## Depends On

```text
Authentication
RBAC
Item
Warehouse
Inventory
Approval
Audit
```

## Main Flow

```text
Create
 ↓
Validate
 ↓
Submit
 ↓
Approval
 ↓
Check Stock
 ↓
Inventory Movement
 ↓
Ledger
 ↓
Audit
```

Stock validation harus dilakukan pada titik yang tepat untuk mencegah invalid stock akibat concurrent transaction.

---

# 15. Stock Opname Module

## Responsibility

Mengelola physical stock counting.

Flow:

```text
Create Opname
      ↓
Counting
      ↓
System Quantity
      ↓
Physical Quantity
      ↓
Variance
      ↓
Adjustment
```

Contoh:

```text
System = 100
Physical = 95

Variance = -5
```

## Depends On

```text
Inventory
Item
Warehouse
RBAC
Approval
Audit
```

---

# 16. Asset Management Module

## Responsibility

Mengelola asset yang membutuhkan tracking individual.

Contoh:

```text
Asset Code
Asset Name
Serial Number
Location
Status
Condition
Assigned To
```

Asset tidak selalu diperlakukan sama dengan consumable inventory.

Karena itu asset memiliki module tersendiri.

---

# 17. Approval Workflow Module

Approval merupakan reusable workflow module.

## Responsibility

```text
Submit
Approve
Reject
Cancel
State Transition
Approval History
```

Contoh:

```text
DRAFT
 ↓
SUBMITTED
 ↓
APPROVED
```

atau:

```text
SUBMITTED
 ↓
REJECTED
```

## Used By

```text
Stock In
Stock Out
Stock Opname Adjustment
Asset Request
```

jika membutuhkan approval.

Approval module tidak bertanggung jawab terhadap perubahan inventory.

Ia hanya menentukan:

> Apakah sebuah request boleh berpindah state?

Business module tetap bertanggung jawab terhadap efek bisnisnya.

---

# 18. Transaction History Module

Transaction History menyediakan historical view dari business transaction.

Contoh:

```text
Stock In
Stock Out
Stock Opname
Asset Transaction
Approval
```

History dapat digunakan oleh:

```text
Dashboard
Reporting
Audit Investigation
User
Manager
Admin
```

Transaction History tidak boleh mengubah business transaction.

---

# 19. Reporting Module

## Responsibility

Reporting menggabungkan data untuk kebutuhan laporan.

Contoh:

```text
Inventory Report
Stock Movement
Stock In Report
Stock Out Report
Stock Opname Report
Asset Report
Transaction Report
```

Reporting bersifat **read-oriented**.

Tidak boleh menggunakan reporting service sebagai tempat utama business mutation.

---

# 20. Dashboard Module

Dashboard bertanggung jawab menyediakan summary.

Contoh:

```text
Total Items
Total Stock
Low Stock
Pending Approval
Recent Transactions
Stock Movement
```

Dashboard menggunakan data dari:

```text
Inventory
Transactions
Approval
Reporting
```

Dashboard harus menghindari business logic yang terlalu kompleks.

Business calculation utama tetap berada pada domain/application service yang sesuai.

---

# 21. Audit Log Module

Audit Log merupakan cross-cutting module.

## Responsibility

Mencatat:

```text
Who
What
When
Which Resource
Context
```

Contoh:

```text
User A
APPROVE
Stock Out #100
2026-08-31 10:30
```

Audit Log digunakan oleh:

```text
Authentication
RBAC
Stock In
Stock Out
Stock Opname
Assets
Approval
Master Data
```

Audit Log tidak menggantikan Inventory Ledger.

---

# 22. Cross-Cutting Modules

Beberapa capability digunakan oleh banyak module.

```text
Authentication
Authorization
Audit
Validation
Notification
Exception Handling
Logging
```

Secara konseptual:

```text
                Cross-Cutting
                     │
       ┌─────────────┼─────────────┐
       ▼             ▼             ▼
   Business A    Business B    Business C
```

Cross-cutting capability tidak boleh mengambil alih business logic module.

---

# 23. Domain vs Application Responsibility

Business rule utama harus berada pada domain/application layer yang tepat.

Contoh:

```text
StockOutService
```

bertanggung jawab terhadap:

```text
Stock Out Business Flow
```

bukan Controller.

Controller hanya menangani:

```text
HTTP Request
Validation Trigger
Service Call
HTTP Response
```

---

# 24. Controller Responsibility

Controller harus tipis.

Contoh flow:

```text
HTTP Request
     ↓
Controller
     ↓
Form Request
     ↓
Authorization
     ↓
Service
     ↓
Domain Logic
     ↓
Repository / Model
     ↓
Response
```

Hindari:

```text
Controller
    ↓
50+ lines business logic
```

---

# 25. Service Responsibility

Service digunakan untuk application/business workflow.

Contoh:

```text
StockInService
StockOutService
StockOpnameService
ApprovalService
AssetService
```

Service bertanggung jawab mengorkestrasi proses.

Contoh:

```text
StockOutService
   ↓
Validate Business Rule
   ↓
Check Permission
   ↓
Check Stock
   ↓
Create Transaction
   ↓
Create Ledger
   ↓
Create Audit
```

---

# 26. Model Responsibility

Model bertanggung jawab terhadap:

```text
Entity Representation
Relationships
Attribute Casting
Scopes
Persistence Rules
```

Jangan meletakkan seluruh business workflow di Model.

---

# 27. Policy Responsibility

Policy digunakan untuk authorization.

Contoh:

```text
ItemPolicy
StockInPolicy
StockOutPolicy
StockOpnamePolicy
AssetPolicy
ReportPolicy
AuditLogPolicy
```

Policy menjawab:

```text
Can User Create?
Can User Update?
Can User Delete?
Can User Approve?
Can User View?
```

---

# 28. Repository / Query Responsibility

Jika digunakan, repository/query layer bertanggung jawab terhadap data access yang kompleks.

Contoh:

```text
InventoryQuery
ReportQuery
AuditLogQuery
TransactionQuery
```

Gunakan repository hanya ketika memang memberikan value.

Jangan membuat repository untuk setiap Model tanpa kebutuhan.

---

# 29. Database Boundary

Semua module menggunakan database utama:

```text
PostgreSQL
```

Namun module tidak boleh mengakses table module lain secara sembarangan.

Contoh yang buruk:

```text
StockOutService
   ↓
langsung memodifikasi
10 tabel internal module lain
```

Lebih baik:

```text
StockOutService
   ↓
Inventory Service
   ↓
Inventory Operation
```

untuk operation yang memang menjadi responsibility Inventory.

---

# 30. Module Communication

Module berkomunikasi melalui:

```text
Application Service
Domain Service
Events
Queries
```

sesuai kebutuhan.

Hindari coupling langsung yang tidak diperlukan.

Contoh:

```text
Stock Out
    ↓
Inventory Service
    ↓
Inventory Ledger
```

bukan:

```text
Stock Out
    ↓
langsung manipulasi
semua tabel Inventory
```

---

# 31. Transaction Boundary

Operation inventory critical harus menggunakan database transaction.

Contoh:

```text
BEGIN
  ↓
Create Stock Out
  ↓
Update Inventory
  ↓
Create Ledger
  ↓
Create Audit
  ↓
COMMIT
```

Jika salah satu operation gagal:

```text
ROLLBACK
```

---

# 32. Event-Driven Communication

Event dapat digunakan untuk aktivitas yang tidak perlu synchronous.

Contoh:

```text
StockOutApproved
      ↓
Audit
      ↓
Notification
```

Namun V1 tidak perlu menggunakan event untuk semua operasi.

Gunakan event ketika memberikan manfaat nyata.

---

# 33. Dependency Rules

Aturan dependency:

```text
Controller
    ↓
Application Service
    ↓
Domain / Data Access
```

Bukan:

```text
Controller
    ↓
langsung Database
```

Business module tidak boleh bergantung pada UI.

```text
Frontend
    ↓
API / Inertia
    ↓
Application
```

Backend tidak boleh bergantung pada implementation detail frontend.

---

# 34. Frontend Module Structure

Frontend mengikuti domain/module.

Contoh:

```text
resources/js/
│
├── Pages/
│   ├── Dashboard/
│   ├── Items/
│   ├── StockIn/
│   ├── StockOut/
│   ├── StockOpname/
│   ├── Assets/
│   ├── Reports/
│   └── AuditLogs/
│
├── Components/
│   ├── UI/
│   ├── Forms/
│   ├── Tables/
│   └── Layout/
│
└── Composables/
```

Frontend tidak harus mengikuti struktur backend secara identik, tetapi naming dan domain concept harus konsisten.

---

# 35. UI Permission Boundary

Frontend boleh melakukan:

```text
Hide Button
Hide Menu
Disable Action
Show Permission-aware UI
```

Namun backend tetap melakukan:

```text
Authorization
Validation
Business Rule
```

Contoh:

```text
Frontend
Approve Button hidden
```

tetapi jika request manual dikirim:

```text
POST /stock-outs/100/approve
```

backend tetap harus menolak user yang tidak memiliki permission.

---

# 36. Error Handling

Error harus diproses pada layer yang tepat.

```text
Validation Error
→ Form Request

Authorization Error
→ Policy / Authorization

Business Rule Error
→ Domain/Application Service

Database Error
→ Infrastructure/Application Boundary

Unexpected Error
→ Global Exception Handler
```

Jangan menampilkan raw database exception kepada user.

---

# 37. Module Boundary Example

Contoh Stock Out:

```text
Stock Out Module
│
├── Controller
├── Request
├── Policy
├── Service
├── Model
└── Query
        │
        ▼
Inventory Module
│
├── Inventory Service
├── Ledger Service
└── Inventory Query
```

Stock Out tidak boleh memiliki business logic Inventory yang duplikat.

---

# 38. Reporting Boundary

Reporting boleh membaca data dari berbagai module:

```text
Inventory
Stock In
Stock Out
Stock Opname
Assets
```

Tetapi:

```text
Reporting
    X
    ↓
Modify Inventory
```

tidak diperbolehkan.

Reporting bersifat read-oriented.

---

# 39. Audit Boundary

Audit Log dapat menerima event dari berbagai module:

```text
Authentication
RBAC
Inventory
Stock In
Stock Out
Stock Opname
Approval
Assets
```

Tetapi business module tidak boleh bergantung pada audit UI.

Dependency:

```text
Business Module
      ↓
Audit Service
```

bukan:

```text
Business Module
      ↓
Audit Controller
```

---

# 40. Scalability Strategy

V1 menggunakan:

```text
Modular Monolith
```

Jika scale meningkat:

```text
Modular Monolith
        ↓
Identify Bottleneck
        ↓
Measure
        ↓
Optimize
        ↓
Extract Service Only If Needed
```

Jangan langsung menggunakan microservices.

---

# 41. Testing Boundary

Testing mengikuti module.

Contoh:

```text
tests/
├── Feature/
│   ├── Authentication/
│   ├── StockIn/
│   ├── StockOut/
│   ├── StockOpname/
│   └── Approval/
│
└── Unit/
    ├── Inventory/
    ├── Approval/
    └── Services/
```

Setiap module harus memiliki test terhadap business-critical behavior.

---

# 42. Maintenance Rules

Ketika mengubah sebuah module:

```text
1. Identify Module
2. Read Module Documentation
3. Check Dependencies
4. Check Feature Decisions
5. Check Database Impact
6. Check API Impact
7. Check Permission Impact
8. Check Audit Impact
9. Update Tests
10. Update Documentation
```

Jangan melakukan perubahan lintas module tanpa memahami dependency.

---

# 43. Adding New Module

Module baru harus memiliki:

```text
Purpose
Responsibility
Dependencies
Public Interface
Database Impact
Authorization
Audit Requirement
Testing Strategy
```

Sebelum module baru dibuat, pastikan fitur tersebut memang tidak dapat ditempatkan pada module yang sudah ada.

---

# 44. Module Dependency Rule

Gunakan prinsip:

```text
                    Shared Infrastructure
                           ↑
                           │
                    Application Services
                           ↑
                           │
                     Domain Modules
                           ↑
                           │
                    Presentation Layer
```

Dependency harus mengarah ke abstraction/responsibility yang benar.

Hindari circular dependency:

```text
Module A → Module B
Module B → Module A
```

Jika terjadi, evaluasi kembali boundary kedua module.

---

# 45. Final Architecture

Struktur konseptual final:

```text
                         INVENTRA
                            │
                 ┌──────────┴──────────┐
                 │                     │
             Frontend               Backend
           Inertia + Vue            Laravel
                                      │
        ┌─────────────────────────────┼──────────────────────────┐
        │                             │                          │
        ▼                             ▼                          ▼
 Authentication                    RBAC                    Master Data
                                      │                          │
                                      └──────────────┬───────────┘
                                                     ▼
                                              Item Management
                                                     │
                                                     ▼
                                                 Warehouse
                                                     │
                                                     ▼
                                                 Inventory
                                                     │
                         ┌───────────────────────────┼───────────────────────┐
                         ▼                           ▼                       ▼
                     Stock In                   Stock Out              Stock Opname
                         │                           │                       │
                         └───────────────────────────┼───────────────────────┘
                                                     ▼
                                              Approval Workflow
                                                     │
                                                     ▼
                                            Transaction History
                                                     │
                                  ┌──────────────────┴──────────────────┐
                                  ▼                                     ▼
                              Reporting                              Dashboard

                              Asset Management

                         Cross-Cutting Capabilities
                  ┌────────────┬────────────┬────────────┐
                  ▼            ▼            ▼            ▼
               Audit       Validation   Notification   Logging
```

---

# 46. Final Principles

Architecture module Inventra mengikuti prinsip:

```text
1. Modular Monolith
2. Clear Module Boundary
3. Low Coupling
4. High Cohesion
5. Thin Controller
6. Business Logic in Service/Domain Layer
7. Authorization via Policy/RBAC
8. Inventory Mutation through Inventory Boundary
9. Audit Important Business Events
10. Reporting is Read-Oriented
11. Critical Operations use Transactions
12. Frontend is not Security Boundary
13. Avoid Unnecessary Abstraction
14. Avoid Premature Microservices
15. Measure Before Optimizing
```

---

# 47. Related Documents

Dokumen terkait:

```text
docs/00_PRD.md
docs/02_FEATURE_DECISIONS.md
docs/03_MODULES.md
docs/04_USER_FLOW.md
docs/05_DATABASE.md
docs/06_API.md
docs/07_PERMISSION_MATRIX.md

docs/architecture/SYSTEM_ARCHITECTURE.md
docs/architecture/DATA_FLOW.md
docs/architecture/SECURITY_ARCHITECTURE.md

docs/code-guide/
docs/sprints/

docs/decisions/ADR/
```

---

# 48. Final Decision

Inventra menggunakan **Modular Monolith Architecture** dengan module boundary berdasarkan business capability.

Core architecture:

```text
Presentation
     ↓
Application
     ↓
Domain / Module
     ↓
Infrastructure
     ↓
PostgreSQL
```

Module tidak dibuat sebagai microservice terpisah pada V1.

Setiap module harus memiliki responsibility yang jelas dan berkomunikasi melalui boundary yang terdefinisi.

Tujuan utama architecture ini adalah:

```text
Maintainability
Consistency
Testability
Security
Scalability
Developer Productivity
```

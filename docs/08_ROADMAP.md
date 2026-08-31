# Inventra

## Product Roadmap

**Document:** Product Roadmap
**Version:** V1.0
**Status:** Draft

---

# 1. Roadmap Objective

Roadmap Inventra digunakan untuk menentukan urutan pengembangan berdasarkan dependency antar fitur.

Prinsip utama:

```text
Foundation
   ↓
Core Master Data
   ↓
Inventory
   ↓
Procurement
   ↓
Asset
   ↓
Reporting
   ↓
API
   ↓
Security & QA
   ↓
Deployment
```

Fitur tidak dikembangkan hanya berdasarkan tampilan, tetapi berdasarkan **dependency dan business flow**.

---

# 2. Technology Foundation

Tahap awal menyiapkan:

```text
Laravel
Inertia.js
Vue.js
PostgreSQL
Docker
Git
REST API
```

Termasuk:

- Project structure
- Environment configuration
- Database connection
- Authentication foundation
- Basic frontend layout
- Error handling
- Logging
- Code documentation standard

---

# 3. Phase 1 — Authentication

### Objective

Membangun fondasi akses pengguna.

### Scope

- Login
- Logout
- Session
- Password handling
- User status
- Authentication middleware

### Output

```text
User
 ↓
Login
 ↓
Authenticated Session
 ↓
Application
```

---

# 4. Phase 2 — RBAC & Scope

### Objective

Membangun authorization.

### Scope

- Roles
- Permissions
- Role-Permission
- Department Scope
- Warehouse Scope
- Location Scope
- Backend authorization
- Default deny

### Output

```text
User
 ↓
Role
 ↓
Permission
 ↓
Scope
 ↓
Allowed Action
```

---

# 5. Phase 3 — Master Data

### Objective

Membangun data dasar yang digunakan seluruh module.

### Scope

- Company
- Department
- User
- Category
- Unit
- Item
- Supplier
- Warehouse
- Location

### Output

Seluruh transaksi dapat menggunakan master data terpusat.

---

# 6. Phase 4 — Item Management

### Objective

Membangun pengelolaan item.

### Scope

- Item CRUD
- Category
- Brand
- Item Type
- Base Unit
- Minimum Stock
- Item status
- Department allowed item

### Special Rule

Content per unit tidak menggunakan conversion table global.

Contoh:

```text
6 BOX
1 BOX = 100 PCS

Equivalent = 600 PCS
```

Nilai tersebut disimpan sebagai bagian dari transaksi.

---

# 7. Phase 5 — Warehouse & Location

### Objective

Membangun physical inventory structure.

### Scope

```text
Warehouse
   ↓
Location
   ↓
Item
```

Mendukung:

- Warehouse
- Rack
- Shelf/location hierarchy
- Warehouse scope
- Location scope

### Physical Location Principle

Untuk V1:

```text
1 Item
   ↓
1 Warehouse
   ↓
1 Primary Location
```

Tujuannya memudahkan:

- Physical checking
- Stock Opname
- Stock tracing

---

# 8. Phase 6 — Stock In

### Objective

Membangun proses barang masuk.

### Scope

- Manual Stock In
- Receiving-based Stock In
- Unit input
- Content per Unit
- Equivalent Quantity
- Warehouse
- Location
- Inventory Transaction
- Inventory Ledger
- Stock Balance
- Reference Number
- Audit Log

Flow:

```text
Receiving
   ↓
Stock In
   ↓
Ledger
   ↓
Stock Balance
```

---

# 9. Phase 7 — Stock Out

### Objective

Membangun proses barang keluar.

### Scope

- Stock Out
- Department destination
- Quantity
- Manual content
- Stock availability
- Approval
- Inventory Ledger
- Stock Balance
- Transaction History
- Audit Log

Flow:

```text
Request
   ↓
Approval
   ↓
Stock Out
   ↓
Ledger
   ↓
Stock Balance
```

---

# 10. Phase 8 — Stock Opname

### Objective

Menyamakan system stock dengan physical stock.

### Scope

- Create Stock Opname
- Select Warehouse
- Select Location
- Physical Count
- Difference calculation
- Approval
- Adjustment
- Audit

Flow:

```text
System Stock
     ↓
Physical Count
     ↓
Difference
     ↓
Adjustment
     ↓
Ledger
```

---

# 11. Phase 9 — Stock Transfer

### Objective

Memindahkan stock antar lokasi/warehouse.

### Scope

- Source warehouse
- Source location
- Destination warehouse
- Destination location
- Quantity validation
- Approval
- Ledger
- Stock Balance

Flow:

```text
Source
  ↓
Transfer
  ↓
Destination
```

---

# 12. Phase 10 — Stock Return

### Objective

Menangani pengembalian barang.

### Scope

- Return request
- Reference transaction
- Return quantity
- Warehouse verification
- Stock In
- Ledger
- Audit

Flow:

```text
Stock Out
   ↓
Return
   ↓
Stock In
```

---

# 13. Phase 11 — Procurement

### Objective

Membangun procurement lifecycle.

### Scope

- Purchase Request
- Department restriction
- Approval
- Purchase Order
- Supplier
- Receiving

Flow:

```text
Department
   ↓
PR
   ↓
Approval
   ↓
PO
   ↓
Supplier
   ↓
Receiving
   ↓
Stock In
```

---

# 14. Phase 12 — Asset Management

### Objective

Mengelola item yang bersifat individual/asset.

### Scope

- Asset registration
- Asset Tag
- Serial Number
- Location
- Department
- Custodian
- Assignment
- Return
- Maintenance
- Disposal
- Asset History

Flow:

```text
Stock In
   ↓
Asset Registration
   ↓
Assignment
   ↓
Tracking
   ↓
Return / Maintenance
   ↓
Disposal
```

---

# 15. Phase 13 — Approval Workflow

### Objective

Membangun approval engine yang dapat digunakan berbagai module.

### Scope

- Workflow
- Workflow steps
- Approver
- Approval request
- Approve
- Reject
- Approval history
- Self-approval prevention

Model:

```text
Transaction
     ↓
Approval Workflow
     ↓
Step 1
     ↓
Step 2
     ↓
Approved
```

Approval dipisahkan dari execution.

---

# 16. Phase 14 — Transaction History

### Objective

Memberikan kemampuan tracing seluruh transaksi.

### Scope

- Transaction reference
- Transaction detail
- User
- Item
- Warehouse
- Location
- Department
- Approval
- Ledger
- Related transaction

Target:

```text
Reference Number
       ↓
Transaction
       ↓
Item
       ↓
Warehouse
       ↓
Location
       ↓
Stock Change
       ↓
User
```

---

# 17. Phase 15 — Reporting

### Objective

Menyediakan informasi operasional dan management.

### Scope

- Stock Report
- Stock Movement
- Low Stock
- Stock Opname
- Transaction Report
- Procurement Report
- Asset Report

Semua report menerapkan:

```text
Permission
+
Scope
```

---

# 18. Phase 16 — Dashboard

### Objective

Menyediakan overview kondisi sistem.

### Scope

```text
Stock Summary
Low Stock
Pending Approval
Recent Transactions
Asset Summary
Stock Movement
```

Dashboard bersifat role-aware.

Contoh:

```text
Warehouse Manager
→ Warehouse information

Department Manager
→ Department information

Management
→ Management overview
```

---

# 19. Phase 17 — REST API

### Objective

Menyediakan interface untuk external client/integration.

### Scope

```text
/api/v1
```

Meliputi:

- Authentication
- Items
- Stock
- Transactions
- Procurement
- Assets
- Approval
- Reports

API menggunakan business logic yang sama dengan web application.

---

# 20. Phase 18 — Export

### Objective

Menyediakan data untuk kebutuhan operasional.

### Format

```text
Excel
PDF
```

Export mengikuti:

```text
User Permission
+
User Scope
+
Report Filter
```

---

# 21. Phase 19 — Security Hardening

### Objective

Melakukan security review sebelum deployment.

### Scope

- Authentication security
- Authorization review
- Scope enforcement
- Input validation
- CSRF
- Rate limiting
- SQL injection protection
- XSS protection
- File upload validation
- Sensitive data protection
- Audit review
- Error handling
- API security

Prinsip:

```text
Frontend restriction ≠ Security
Backend enforcement = Security
```

---

# 22. Phase 20 — Testing & QA

### Objective

Memastikan sistem bekerja sesuai business rules.

### Testing

```text
Unit Test
Feature Test
Integration Test
Authorization Test
API Test
Inventory Test
Browser Test
```

Fokus utama:

```text
Stock Calculation
Permission
Scope
Approval
Ledger
Transaction Integrity
```

---

# 23. Phase 21 — Docker Deployment

### Objective

Menjalankan Inventra secara reproducible.

### Environment

```text
Docker
 ├── Laravel Application
 ├── PostgreSQL
 └── Supporting Services
```

Development:

```text
Docker Compose
```

Deployment configuration disiapkan setelah application dan testing stabil.

---

# 24. Roadmap Dependency

Dependency utama:

```text
Authentication
      ↓
RBAC
      ↓
Master Data
      ↓
Item Management
      ↓
Warehouse
      ↓
Inventory
      ↓
Procurement
      ↓
Asset
      ↓
Reporting
      ↓
API
      ↓
Security
      ↓
Testing
      ↓
Deployment
```

Approval Workflow dapat mulai setelah RBAC dan transaction model tersedia.

---

# 25. MVP Boundary

MVP Inventra mencakup:

```text
Authentication
RBAC
Master Data
Item Management
Warehouse
Location
Stock In
Stock Out
Stock Opname
Stock Transfer
Stock Return
Purchase Request
Approval
Transaction History
Audit Log
Dashboard Basic
```

Fitur berikutnya:

```text
Purchase Order
Receiving enhancement
Asset Management
Reporting
Export
REST API
Advanced Security
Advanced Dashboard
```

Urutan final mengikuti hasil dependency dan backlog.

---

# 26. Definition of Roadmap Completion

Sebuah phase dianggap selesai apabila:

```text
Feature Implemented
       ↓
Business Rule Implemented
       ↓
Permission Implemented
       ↓
Validation Implemented
       ↓
Audit Implemented
       ↓
Tested
       ↓
Documentation Updated
       ↓
Sprint Completed
```

---

# 27. Development Philosophy

Inventra dikembangkan dengan prinsip:

> **Build → Understand → Test → Document → Commit**

Bukan:

> **Generate Code → Copy → Finish**

Setiap fitur harus memiliki:

```text
Business Flow
Code Documentation
Test
Sprint Documentation
Git Branch
```

Developer harus dapat menjelaskan:

```text
Apa yang dilakukan fitur?
Mengapa dibuat seperti itu?
File mana yang menjalankan proses?
Data apa yang berubah?
Permission apa yang digunakan?
Bagaimana error ditangani?
Bagaimana cara maintenance?
```

---

# 28. Roadmap Summary

```text
PHASE 1
Foundation
   ↓
PHASE 2
Authentication + RBAC
   ↓
PHASE 3
Master Data
   ↓
PHASE 4
Inventory Core
   ↓
PHASE 5
Procurement
   ↓
PHASE 6
Asset
   ↓
PHASE 7
Reporting + Dashboard
   ↓
PHASE 8
API + Export
   ↓
PHASE 9
Security + QA
   ↓
PHASE 10
Docker Deployment
```

Roadmap ini menjadi dasar penyusunan:

```text
09_BACKLOG.md
sprints/
code-guide/
architecture/
```

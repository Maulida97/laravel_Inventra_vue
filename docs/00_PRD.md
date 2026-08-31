# Inventra

## Inventory & Asset Management System

**Document:** Product Requirements Document
**Version:** V1.0
**Status:** Draft
**Product:** Inventra
**Architecture:** Single Company
**Primary Purpose:** Inventory & Asset Management

---

# 1. Product Overview

**Inventra** adalah sistem Inventory & Asset Management untuk membantu perusahaan mengelola barang, stock, asset, warehouse, transaksi, approval, dan histori aktivitas secara terkontrol dan dapat diaudit.

V1 dirancang untuk **satu perusahaan** dengan struktur beberapa department dan warehouse.

Sistem dibuat dengan prinsip:

- Stock tidak dapat diubah secara sembarangan.
- Pergerakan stock harus melalui transaksi yang valid.
- Transaksi penting dapat membutuhkan approval.
- Setiap perubahan penting dapat ditelusuri.
- Hak akses dibatasi berdasarkan role, permission, dan scope.
- Setiap stock dapat ditelusuri sampai warehouse dan lokasi fisiknya.

---

# 2. Goals

Inventra bertujuan untuk:

1. Mengelola master data barang secara terpusat.
2. Mengelola stock berdasarkan warehouse dan lokasi fisik.
3. Mencatat seluruh pergerakan stock.
4. Mendukung proses Stock Opname.
5. Mengelola asset individual yang membutuhkan tracking.
6. Mengontrol transaksi melalui approval workflow.
7. Menyediakan histori transaksi dan audit trail.
8. Menyediakan dashboard dan reporting.
9. Menyediakan REST API untuk integrasi.
10. Menyediakan export data ke Excel/PDF.

---

# 3. V1 Scope

## Included

### Authentication & Access Control

- Login
- User management
- Role
- Permission
- Scope
- Department access
- Warehouse access

### Master Data

- Department
- Warehouse
- Location
- Item
- Category
- Unit
- Supplier
- Asset-related master data

### Inventory

- Stock In
- Stock Out
- Stock Adjustment
- Stock Return
- Stock Transfer
- Opening Balance
- Stock Balance
- Inventory Ledger
- Transaction History

### Stock Opname

- Create Stock Opname
- Physical count
- System quantity
- Difference
- Adjustment
- Stock Opname history

### Asset Management

- Asset registration
- Asset tag
- Serial number
- Assignment
- Return
- Asset location
- Asset status
- Asset history

### Procurement Workflow

- Purchase Request
- Approval
- Purchase Order
- Receiving

### Approval

- Configurable approval workflow
- Multiple approval steps
- Approval status
- Approval history

### Reporting

- Inventory report
- Stock movement report
- Stock Opname report
- Asset report
- Transaction report

### Dashboard

- Stock summary
- Transaction summary
- Asset summary
- Pending approval
- Stock alerts

### Audit & Integration

- Audit Log
- REST API
- Excel export
- PDF export

---

# 4. Product Users

Inventra menggunakan role-based access control dengan scope.

Role utama:

| Role               | Primary Responsibility                |
| ------------------ | ------------------------------------- |
| System Admin       | System configuration & administration |
| Warehouse Staff    | Warehouse & inventory operations      |
| Warehouse Manager  | Warehouse monitoring & approval       |
| Department Staff   | Request inventory/asset               |
| Department Manager | Department approval                   |
| Procurement        | PR, PO & supplier process             |
| Asset Manager      | Asset lifecycle                       |
| Management         | Monitoring & reporting                |

Satu user dapat memiliki **lebih dari satu role**.

Permission menentukan aksi yang dapat dilakukan, sedangkan scope membatasi data yang dapat diakses.

Contoh:

`Warehouse Manager + WH-001`

dapat melakukan approval untuk `WH-001`, tetapi tidak otomatis dapat melakukan approval untuk warehouse lain.

---

# 5. Organization Structure

V1 menggunakan satu perusahaan.

```text
Company
│
├── Departments
│   ├── IT
│   ├── QC
│   ├── Finance
│   └── ...
│
└── Warehouses
    ├── WH-001
    ├── WH-002
    └── ...
```

Department dapat memiliki akses ke beberapa warehouse dan memiliki satu **default warehouse**.

Default warehouse digunakan sebagai warehouse utama, bukan sebagai satu-satunya warehouse yang dapat diakses.

---

# 6. Warehouse & Location

Warehouse menggunakan struktur lokasi fisik yang fleksibel.

Tidak ada kewajiban setiap warehouse memiliki Zone, Rack, Shelf, dan Bin.

Contoh sederhana:

```text
WH-001
├── RACK-A
└── RACK-B
```

Contoh kompleks:

```text
WH-002
├── ZONE-A
│   ├── RACK-A01
│   │   ├── SHELF-01
│   │   └── SHELF-02
│   └── RACK-A02
│
└── ZONE-B
    └── RACK-B01
```

Location menggunakan struktur parent-child.

---

# 7. Item & Stock

Item dan stock merupakan konsep yang berbeda.

```text
Item
│
├── Quantity Tracking
│   └── Stock Balance
│
└── Serial Tracking
    └── Asset
```

### Quantity Item

Contoh:

```text
Hydraulic Oil
Base Unit: Liter
Stock: 500 Liter
Location: WH-001 / A-R01-S02
```

### Serial Item

Contoh:

```text
Laptop Lenovo
Asset Tag: AST-000123
Serial Number: ABC123
```

---

# 8. Manual Unit Content

Inventra tidak menggunakan conversion table yang menganggap seluruh barang memiliki isi unit yang sama.

Content per unit dapat diinput berdasarkan kondisi atau informasi barang.

Contoh:

```text
Item:
Paku

Brand:
Brand A

Purchase Unit:
Box

Content per Unit:
1 Box = 100 pcs

Transaction:
6 Box

Equivalent:
600 pcs
```

Nilai content per unit dapat berbeda berdasarkan barang/brand dan dapat diinput secara manual.

Tujuannya adalah agar quantity transaksi tetap dapat ditelusuri tanpa mengasumsikan bahwa semua `Box`, `Drum`, `Pack`, dan unit lainnya memiliki isi yang sama.

---

# 9. Inventory Transaction

Seluruh perubahan stock harus melalui inventory transaction.

Jenis transaksi V1:

```text
Opening Balance
Stock In
Stock Out
Transfer
Adjustment
Return
```

Flow umum:

```text
Transaction
    ↓
Validation
    ↓
Authorization
    ↓
Approval (jika diperlukan)
    ↓
Execution
    ↓
Inventory Ledger
    ↓
Stock Balance
    ↓
Audit Log
```

User tidak diperbolehkan mengubah stock balance secara langsung melalui proses normal aplikasi.

---

# 10. Inventory Ledger

Inventory Ledger menjadi **source of truth** untuk histori perubahan stock.

Contoh:

```text
Opening Balance     +500
Stock In            +200
Stock Out           -100
Adjustment           -20
-------------------------
Current Balance      580
```

Stock Balance menyimpan kondisi stock saat ini untuk kebutuhan operasional dan performa akses.

Ledger digunakan untuk melakukan tracing terhadap perubahan stock.

---

# 11. Stock Opname

Stock Opname digunakan untuk membandingkan:

```text
System Quantity
       vs
Physical Quantity
```

Contoh:

```text
System : 500 L
Physical: 480 L
Difference: -20 L
```

Selisih tidak langsung mengubah stock.

Adjustment harus mengikuti workflow yang ditentukan dan menghasilkan inventory transaction serta ledger.

Stock Opname dilakukan berdasarkan warehouse dan lokasi fisik agar proses pengecekan dapat ditelusuri.

---

# 12. Procurement Flow

Inventra mendukung alur procurement:

```text
Purchase Request
       ↓
Approval
       ↓
Purchase Order
       ↓
Receiving
       ↓
Stock In
```

Purchase Request dapat dibuat oleh department yang memiliki akses.

Hak membuat/request barang dapat dibatasi berdasarkan department dan permission.

Receiving menghasilkan transaksi stock setelah barang benar-benar diterima.

---

# 13. Approval Workflow

Approval menggunakan workflow yang dapat dikonfigurasi.

Workflow dapat memiliki beberapa approval step.

Contoh:

```text
Stock Adjustment
       ↓
Department Manager
       ↓
Warehouse Manager
       ↓
Approved
       ↓
Execute
```

Approval history harus menyimpan:

- Approver
- Action
- Status
- Timestamp
- Catatan jika ada

Approval tidak boleh hanya bergantung pada tampilan frontend. Authorization harus divalidasi di backend.

---

# 14. Asset Management

Asset digunakan untuk barang yang membutuhkan tracking individual.

Contoh:

```text
Laptop
│
├── Asset Tag
├── Serial Number
├── Location
├── Department
├── Custodian
└── Status
```

Lifecycle dasar:

```text
Register
   ↓
Available
   ↓
Assigned
   ↓
Returned
   ↓
Maintenance / Available
   ↓
Disposed
```

Asset memiliki histori sehingga perubahan status, lokasi, dan assignment dapat ditelusuri.

---

# 15. Transaction History

Inventra menyediakan histori transaksi untuk menelusuri:

- Jenis transaksi
- Item
- Quantity
- Warehouse
- Location
- User
- Status
- Reference number
- Timestamp

Contoh reference:

```text
PR-000001
PO-000001
GR-000001
SI-000001
SO-000001
ADJ-000001
SO-000001
```

Nomor transaksi harus unik dan dapat digunakan untuk tracing antar proses.

---

# 16. Dashboard

Dashboard memberikan ringkasan operasional.

Informasi utama:

```text
Total Items
Total Stock
Low Stock
Pending Approval
Stock Movement
Asset Summary
Recent Transactions
```

Dashboard mengikuti permission dan scope user.

User tidak boleh melihat data yang berada di luar scope yang dimilikinya.

---

# 17. Reporting

Reporting menyediakan informasi operasional berdasarkan permission dan scope.

Report utama:

- Stock report
- Stock movement
- Stock Opname
- Asset
- Transaction
- Procurement
- Audit

Report dapat difilter berdasarkan parameter seperti:

```text
Date
Department
Warehouse
Location
Item
Category
Transaction Type
Status
```

---

# 18. Audit Log

Audit Log mencatat aktivitas penting user.

Contoh:

```text
User:
Budi

Action:
APPROVE

Entity:
Stock Out

Reference:
SO-000123

Before:
Pending

After:
Approved

Timestamp:
...
```

Audit Log digunakan untuk menjawab:

> Siapa melakukan apa, terhadap data apa, dan kapan?

Audit Log berbeda dengan Inventory Ledger.

```text
Inventory Ledger
→ Bagaimana stock berubah?

Audit Log
→ Siapa melakukan perubahan?
```

---

# 19. REST API

Inventra menyediakan REST API dengan versioning.

Contoh:

```text
/api/v1/items
/api/v1/stock
/api/v1/stock-in
/api/v1/stock-out
/api/v1/assets
/api/v1/reports
```

API digunakan untuk:

- External integration
- Mobile application
- Automation
- Future clients

Web application tetap menggunakan Laravel + Inertia.js + Vue.

---

# 20. Export

Reporting dan data tertentu dapat diexport ke:

```text
Excel
PDF
```

Export mengikuti permission dan scope user.

User tidak dapat melakukan export terhadap data yang tidak boleh mereka akses.

---

# 21. Security Requirements

Inventra harus menerapkan:

- Authentication
- Role-Based Access Control
- Permission
- Scope-based authorization
- Backend authorization
- Input validation
- CSRF protection
- Secure password handling
- Database transactions untuk operasi penting
- Audit logging
- Protection terhadap unauthorized data access

Business rule tidak boleh hanya diimplementasikan pada frontend.

---

# 22. Technology Stack

### Backend

```text
Laravel
PHP
REST API
```

### Frontend

```text
Vue 3
Inertia.js
Vite
Tailwind CSS
```

### Database

```text
PostgreSQL
```

### Infrastructure

```text
Docker
Nginx
```

### Testing

```text
Pest
Vitest
Playwright
```

---

# 23. High-Level Architecture

```text
                    Browser
                       │
                       ▼
                 Vue 3 + Inertia
                       │
                       ▼
                    Nginx
                       │
                       ▼
                    Laravel
                       │
          ┌────────────┼────────────┐
          │            │            │
       Policy      Services      REST API
          │            │            │
          └────────────┼────────────┘
                       │
                PostgreSQL
                       │
          ┌────────────┼────────────┐
          │            │            │
     Stock Balance   Ledger     Audit Log
```

Detail architecture akan dijelaskan di:

```text
docs/architecture/
```

---

# 24. Documentation & Maintainability

Inventra harus dikembangkan dengan dokumentasi yang memungkinkan developer memahami dan melakukan maintenance tanpa bergantung sepenuhnya pada AI.

Setiap file penting harus memiliki dokumentasi yang menjelaskan:

- Purpose
- Responsibility
- Related components
- Main flow

Section code yang kompleks harus memiliki komentar yang menjelaskan **alasan/business rule**, bukan sekadar menerjemahkan kode.

Setiap fitur memiliki Code Guide di:

```text
docs/code-guide/
```

Code Guide minimal mencakup:

- Feature purpose
- File map
- Code flow
- Business rules
- Dependencies
- Database relationship
- How to modify
- Testing
- Troubleshooting

---

# 25. Development Method

Pengembangan Inventra dilakukan berdasarkan **Sprint per Feature**.

Setiap feature memiliki:

```text
Requirement
    ↓
Design
    ↓
Implementation
    ↓
Testing
    ↓
Code Walkthrough
    ↓
Documentation
    ↓
Review
    ↓
Done
```

Setiap feature dikembangkan pada branch Git tersendiri.

Contoh:

```text
feature/authentication
feature/rbac
feature/item-management
feature/stock-in
feature/stock-out
```

Git push dan merge dilakukan secara manual oleh developer.

---

# 26. Definition of Done

Sebuah feature dianggap selesai apabila:

- Requirement telah diimplementasikan.
- Authorization telah diterapkan.
- Validation telah diterapkan.
- Database telah sesuai.
- Business logic telah diuji.
- Feature test tersedia jika diperlukan.
- UI telah diverifikasi.
- Tidak terdapat critical error.
- Code telah dijelaskan melalui Code Walkthrough.
- Code Guide telah diperbarui.
- Sprint documentation telah diperbarui.

---

# 27. V1 Non-Goals

Untuk menjaga scope V1 tetap terkendali, fitur berikut tidak menjadi fokus utama:

- Multi-company / multi-tenant
- Multi-location physical stock untuk item yang sama dalam satu warehouse
- Mobile native application
- Advanced AI features
- Complex forecasting
- Automated unit conversion berdasarkan database global
- Integrasi ERP eksternal sebagai fitur inti

Arsitektur tetap dibuat agar beberapa kemampuan tersebut dapat dikembangkan pada versi berikutnya.

---

# 28. Future Direction

V2 dapat dikembangkan menuju:

```text
Inventra V1
Single Company
      ↓
Inventra V2
Multi Company
      ↓
Advanced Inventory
      ↓
Mobile Application
      ↓
External Integrations
```

Pengembangan V2 tidak boleh merusak prinsip utama V1:

> **Traceable, controlled, auditable inventory management.**

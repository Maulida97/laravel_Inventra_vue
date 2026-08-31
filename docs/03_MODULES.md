# Inventra

## Modules

**Document:** Module Definition
**Version:** V1.0
**Status:** Draft

---

# 1. Module Overview

Inventra V1 terdiri dari modul berikut:

```text
Inventra
│
├── 01 Authentication
├── 02 User & RBAC
├── 03 Master Data
├── 04 Item Management
├── 05 Warehouse & Location
├── 06 Inventory
│   ├── Stock In
│   ├── Stock Out
│   ├── Transfer
│   ├── Adjustment
│   └── Return
│
├── 07 Stock Opname
├── 08 Procurement
│   ├── Purchase Request
│   ├── Approval
│   ├── Purchase Order
│   └── Receiving
│
├── 09 Asset Management
├── 10 Approval Workflow
├── 11 Transaction History
├── 12 Reporting
├── 13 Dashboard
├── 14 Audit Log
├── 15 REST API
└── 16 Export
```

---

# 2. Authentication

### Responsibility

Mengelola identitas dan session pengguna.

### Features

- Login
- Logout
- Authentication session
- Password management
- User status

### Dependency

```text
Authentication
      ↓
User & RBAC
```

---

# 3. User & RBAC

### Responsibility

Mengontrol siapa yang dapat melakukan suatu tindakan dan data apa yang dapat mereka akses.

### Components

```text
User
 ↓
Role
 ↓
Permission
 ↓
Scope
```

### Scope

Scope dapat membatasi akses berdasarkan:

- Department
- Warehouse
- Location
- Resource tertentu

### Example

```text
Warehouse Staff
 + Permission: Stock Out
 + Scope: WH-001
```

User tersebut dapat melakukan Stock Out pada warehouse yang diizinkan.

---

# 4. Master Data

### Responsibility

Menyediakan data referensi yang digunakan oleh modul lain.

### Components

- Company
- Department
- Category
- Unit
- Supplier
- Status
- Other configurable master data

Master data harus dikelola secara terkontrol karena digunakan oleh banyak modul.

---

# 5. Item Management

### Responsibility

Mengelola identitas dan karakteristik barang.

### Components

- Item
- Item Code
- Item Name
- Category
- Brand
- Base Unit
- Item Type
- Minimum Stock
- Content per Unit
- Item Status

### Item Type

```text
Quantity Item
Serial/Asset Item
```

Item Management tidak menyimpan histori perubahan stock.

Histori stock dikelola oleh Inventory.

---

# 6. Warehouse & Location

### Responsibility

Mengelola warehouse dan lokasi fisik barang.

### Structure

```text
Warehouse
    ↓
Location
    ↓
Child Location
```

Location mendukung parent-child hierarchy.

Contoh:

```text
Warehouse
└── Zone A
    └── Rack A01
        └── Shelf 01
```

Struktur dapat dibuat sederhana:

```text
Warehouse
└── Rack A
```

Tidak semua warehouse wajib menggunakan seluruh level hierarchy.

---

# 7. Inventory

### Responsibility

Mengelola jumlah dan pergerakan stock.

### Transactions

```text
Opening Balance
Stock In
Stock Out
Transfer
Adjustment
Return
```

### Core Components

```text
Inventory Transaction
        ↓
Inventory Ledger
        ↓
Stock Balance
```

Inventory menjadi modul inti Inventra.

---

# 8. Stock In

### Responsibility

Mencatat barang yang masuk ke warehouse.

### Sources

- Receiving
- Opening Balance
- Return
- Adjustment

Stock In harus mencatat:

- Item
- Quantity
- Unit
- Warehouse
- Location
- Reference
- User
- Timestamp

---

# 9. Stock Out

### Responsibility

Mengontrol pengeluaran barang dari warehouse.

### Flow

```text
Request
   ↓
Validation
   ↓
Approval
   ↓
Stock Out
   ↓
Inventory Ledger
   ↓
Stock Balance
```

Stock tidak boleh menjadi negatif apabila business rule item tidak mengizinkannya.

---

# 10. Stock Transfer

### Responsibility

Memindahkan stock dari satu lokasi/warehouse ke lokasi/warehouse lain.

```text
Source
   ↓
Transfer
   ↓
Destination
```

Transfer menghasilkan histori pergerakan yang dapat ditelusuri.

---

# 11. Stock Adjustment

### Responsibility

Melakukan koreksi stock berdasarkan alasan yang valid.

Adjustment tidak dilakukan dengan mengubah `Stock Balance` secara langsung.

```text
Adjustment
    ↓
Approval
    ↓
Inventory Transaction
    ↓
Ledger
    ↓
Stock Balance
```

Adjustment harus memiliki alasan/reference yang dapat diaudit.

---

# 12. Stock Return

### Responsibility

Mencatat pengembalian barang.

Contoh:

```text
Department
     ↓
Return
     ↓
Warehouse
     ↓
Stock In
```

Return tetap menghasilkan inventory transaction dan ledger.

---

# 13. Stock Opname

### Responsibility

Membandingkan stock sistem dengan kondisi fisik.

### Flow

```text
Select Warehouse
       ↓
Select Location
       ↓
Physical Count
       ↓
System Quantity
       ↓
Calculate Difference
       ↓
Review
       ↓
Adjustment
```

Stock Opname memiliki histori agar hasil pengecekan fisik dapat ditelusuri.

---

# 14. Procurement

### Responsibility

Mengelola proses permintaan dan pembelian barang.

### Flow

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

### Purchase Request

Request dapat dibuat oleh department yang memiliki permission.

Hak request dapat dibatasi berdasarkan department.

Contoh:

```text
IT
 ↓
PR barang IT

QC
 ↓
PR barang QC
```

---

# 15. Asset Management

### Responsibility

Mengelola barang yang membutuhkan tracking secara individual.

### Asset Identity

```text
Asset
├── Asset Tag
├── Serial Number
├── Item
├── Location
├── Department
├── Custodian
└── Status
```

### Lifecycle

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

Asset memiliki histori perubahan.

---

# 16. Approval Workflow

### Responsibility

Mengelola approval terhadap transaksi yang membutuhkan otorisasi.

Workflow mendukung:

- Approval step
- Approver
- Status
- Approval history
- Rejection
- Notes

Contoh:

```text
Stock Adjustment
       ↓
Department Manager
       ↓
Warehouse Manager
       ↓
Approved
```

Approval workflow harus dapat digunakan oleh beberapa modul.

---

# 17. Transaction History

### Responsibility

Menyediakan histori transaksi operasional.

Informasi utama:

- Transaction number
- Transaction type
- Item
- Quantity
- Warehouse
- Location
- User
- Status
- Reference
- Timestamp

Transaction History bersifat **operational history**.

---

# 18. Reporting

### Responsibility

Menyediakan informasi untuk monitoring dan analisis operasional.

### Reports

```text
Stock Report
Stock Movement
Stock Opname
Asset Report
Transaction Report
Procurement Report
Audit Report
```

Report mengikuti permission dan scope pengguna.

---

# 19. Dashboard

### Responsibility

Menyediakan ringkasan kondisi sistem.

### Information

```text
Stock Summary
Low Stock
Stock Movement
Pending Approval
Asset Summary
Recent Transactions
```

Dashboard bersifat role/scope aware.

---

# 20. Audit Log

### Responsibility

Mencatat aktivitas penting yang dilakukan pengguna.

Contoh:

```text
User
 ↓
Action
 ↓
Entity
 ↓
Before
 ↓
After
 ↓
Timestamp
```

Audit Log berbeda dari Inventory Ledger.

```text
Inventory Ledger
→ Perubahan stock

Audit Log
→ Aktivitas user terhadap sistem
```

---

# 21. REST API

### Responsibility

Menyediakan interface untuk integrasi eksternal.

API menggunakan versioning:

```text
/api/v1/
```

Modul yang diekspos melalui API ditentukan berdasarkan kebutuhan dan permission.

---

# 22. Export

### Responsibility

Menghasilkan output data dalam format:

```text
Excel
PDF
```

Export mengikuti filter, permission, dan scope pengguna.

---

# 23. Module Dependency

High-level dependency:

```text
Authentication
      ↓
User & RBAC
      ↓
Master Data
      ↓
Item Management
      ↓
Warehouse & Location
      ↓
Inventory
   ┌──┼────┬──────┐
   ↓  ↓    ↓      ↓
 In  Out Transfer Adjustment
   │  │    │      │
   └──┴────┴──────┘
            ↓
      Inventory Ledger
            ↓
       Stock Balance
            ↓
      Transaction History
```

Modul lain menggunakan Inventory sebagai sumber transaksi:

```text
Procurement
    ↓
Receiving
    ↓
Stock In
```

```text
Stock Opname
    ↓
Adjustment
    ↓
Inventory
```

```text
Asset Management
    ↓
Asset History
```

Sedangkan:

```text
Approval Workflow
       ↓
Multiple Transaction Modules
```

dan:

```text
Audit Log
       ↓
Multiple Modules
```

---

# 24. Module Design Principle

Setiap modul harus memiliki **single responsibility** yang jelas.

Contoh:

```text
Item Management
→ Apa barangnya?

Warehouse
→ Di mana barang berada?

Inventory
→ Berapa dan bagaimana stock bergerak?

Asset Management
→ Barang individual tersebut milik/dipegang siapa?

Approval
→ Siapa yang mengizinkan transaksi?

Audit Log
→ Siapa melakukan apa?

Reporting
→ Bagaimana data disajikan?
```

Dengan pemisahan tersebut, perubahan pada satu modul tidak boleh secara sembarangan mengubah business logic modul lain.

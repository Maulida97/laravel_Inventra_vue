# Inventra

## User Flow

**Document:** User Flow
**Version:** V1.0
**Status:** Draft

---

# 1. User Flow Overview

Alur utama Inventra:

```text
Login
  ↓
Dashboard
  ↓
User melakukan aktivitas sesuai Role + Permission + Scope
  ↓
Transaction / Management
  ↓
Approval (jika diperlukan)
  ↓
Execution
  ↓
Inventory / Asset Update
  ↓
Transaction History
  ↓
Audit Log
```

Tidak semua aktivitas membutuhkan approval.

---

# 2. Authentication Flow

```text
User
 ↓
Login
 ↓
Validate Credentials
 ↓
Authentication Success?
 ├── No → Error
 └── Yes
       ↓
   Load Role
       ↓
   Load Permission
       ↓
   Load Scope
       ↓
   Dashboard
```

User yang tidak terautentikasi tidak dapat mengakses halaman internal Inventra.

---

# 3. Dashboard Flow

Setelah login:

```text
Login
 ↓
Dashboard
 ├── Stock Summary
 ├── Low Stock
 ├── Pending Approval
 ├── Recent Transactions
 ├── Asset Summary
 └── Stock Movement
```

Data dashboard mengikuti permission dan scope user.

---

# 4. Item Management Flow

```text
Item Management
      ↓
Create / Edit Item
      ↓
Input Item Information
      ↓
Validate
      ↓
Save
      ↓
Item Available
```

Informasi item dapat mencakup:

```text
Item Code
Item Name
Category
Brand
Item Type
Base Unit
Minimum Stock
Content per Unit
Status
```

Perubahan master item tidak mengubah histori stock yang telah terjadi.

---

# 5. Manual Content per Unit Flow

Untuk barang yang memiliki kemasan berbeda-beda:

```text
Create Transaction
      ↓
Select Item
      ↓
Select Transaction Unit
      ↓
Input Quantity
      ↓
Input Content per Unit
      ↓
Calculate Equivalent Quantity
      ↓
Review
      ↓
Submit
```

Contoh:

```text
Item       : Paku Brand A
Unit       : Box
Quantity   : 6 Box
Content    : 1 Box = 100 pcs

Equivalent:
6 × 100 = 600 pcs
```

Content per unit tidak menggunakan conversion table global karena isi setiap kemasan dapat berbeda berdasarkan item/brand.

---

# 6. Purchase Request Flow

Department Staff membuat permintaan:

```text
Department Staff
      ↓
Create PR
      ↓
Select Item
      ↓
Input Quantity
      ↓
Input Reason
      ↓
Submit
      ↓
Approval
```

Department Staff hanya dapat membuat request sesuai permission dan department scope.

Contoh:

```text
IT Staff
 ↓
PR barang yang diizinkan untuk IT
```

```text
QC Staff
 ↓
PR barang yang diizinkan untuk QC
```

---

# 7. Purchase Request Approval Flow

```text
PR Submitted
     ↓
Pending Approval
     ↓
Approver
     ├── Approve
     │      ↓
     │   Next Step?
     │      ├── Yes → Next Approver
     │      └── No → Approved
     │
     └── Reject
            ↓
         Rejected
```

Approval history disimpan.

Jika ditolak, request tidak dapat dilanjutkan ke proses berikutnya tanpa workflow yang sesuai.

---

# 8. Purchase Order Flow

```text
Approved PR
     ↓
Procurement
     ↓
Create PO
     ↓
Select Supplier
     ↓
Input Order
     ↓
Review
     ↓
Submit
     ↓
PO Created
```

PO mengacu pada Purchase Request yang telah disetujui apabila proses berasal dari PR.

---

# 9. Receiving Flow

```text
Purchase Order
      ↓
Barang Datang
      ↓
Receiving
      ↓
Check Received Quantity
      ↓
Check Item
      ↓
Check Condition
      ↓
Confirm Receiving
      ↓
Stock In
```

Receiving dapat menghasilkan jumlah yang berbeda dari jumlah PO.

Seluruh hasil receiving harus tercatat.

---

# 10. Stock In Flow

```text
Stock In
   ↓
Select Item
   ↓
Input Quantity
   ↓
Input Unit / Content
   ↓
Select Warehouse
   ↓
Select Location
   ↓
Validate
   ↓
Approval?
 ├── Yes → Approval → Execute
 └── No  → Execute
                 ↓
          Inventory Ledger
                 ↓
           Stock Balance
                 ↓
          Transaction History
                 ↓
             Audit Log
```

---

# 11. Stock Out Flow

```text
Request
   ↓
Select Item
   ↓
Input Quantity
   ↓
Select Warehouse
   ↓
Select Destination / Department
   ↓
Reason
   ↓
Submit
   ↓
Validation
   ↓
Approval
   ↓
Approved
   ↓
Execute Stock Out
   ↓
Inventory Ledger
   ↓
Stock Balance
   ↓
Transaction History
   ↓
Audit Log
```

Stock Out tidak dapat dieksekusi apabila stock tidak mencukupi, kecuali business rule item secara khusus mengizinkannya.

---

# 12. Stock Transfer Flow

```text
Create Transfer
      ↓
Select Source Warehouse
      ↓
Select Source Location
      ↓
Select Item
      ↓
Input Quantity
      ↓
Select Destination Warehouse
      ↓
Select Destination Location
      ↓
Validate
      ↓
Approval?
 ├── Yes → Approval
 └── No
      ↓
Execute Transfer
      ↓
Decrease Source
      ↓
Increase Destination
      ↓
Inventory Ledger
      ↓
Stock Balance
```

Source dan destination harus dapat diakses oleh user berdasarkan scope.

---

# 13. Stock Adjustment Flow

Adjustment digunakan untuk koreksi stock.

```text
Create Adjustment
       ↓
Select Item
       ↓
Select Warehouse
       ↓
Select Location
       ↓
Input Adjustment
       ↓
Input Reason
       ↓
Submit
       ↓
Approval
       ↓
Approved
       ↓
Execute Adjustment
       ↓
Inventory Ledger
       ↓
Stock Balance
       ↓
Audit Log
```

Stock Balance tidak diedit secara langsung.

---

# 14. Stock Return Flow

```text
Department / User
       ↓
Return Request
       ↓
Select Previous Transaction
       ↓
Select Item
       ↓
Input Return Quantity
       ↓
Warehouse Verification
       ↓
Approve / Confirm
       ↓
Stock In
       ↓
Inventory Ledger
       ↓
Stock Balance
```

Return harus memiliki referensi terhadap transaksi asal jika transaksi tersebut tersedia.

---

# 15. Stock Opname Flow

```text
Create Stock Opname
       ↓
Select Warehouse
       ↓
Select Location
       ↓
System generates item list
       ↓
Physical Count
       ↓
Input Actual Quantity
       ↓
System calculates Difference
       ↓
Review
       ↓
Submit
       ↓
Approval
       ↓
Adjustment
       ↓
Inventory Ledger
       ↓
Stock Balance
```

Contoh:

```text
System Quantity : 500
Physical Count  : 480
Difference      : -20
```

Selisih tidak langsung mengubah stock sebelum proses adjustment diselesaikan.

---

# 16. Asset Registration Flow

```text
Asset Received
      ↓
Register Asset
      ↓
Select Item
      ↓
Input Asset Tag
      ↓
Input Serial Number
      ↓
Set Location
      ↓
Set Department
      ↓
Set Status
      ↓
Asset Created
```

Asset Tag harus unik.

Serial Number digunakan untuk membantu identifikasi asset individual.

---

# 17. Asset Assignment Flow

```text
Available Asset
      ↓
Assignment
      ↓
Select Employee / Custodian
      ↓
Select Department
      ↓
Confirm
      ↓
Asset Status = Assigned
      ↓
Create Asset History
      ↓
Audit Log
```

---

# 18. Asset Return Flow

```text
Assigned Asset
      ↓
Return
      ↓
Verify Asset
      ↓
Check Condition
      ↓
Update Location
      ↓
Update Status
      ↓
Create Asset History
      ↓
Audit Log
```

Asset dapat kembali menjadi:

```text
Available
Maintenance
```

tergantung kondisi asset.

---

# 19. Approval General Flow

Approval digunakan oleh transaksi yang membutuhkan kontrol tambahan.

```text
Draft
 ↓
Submitted
 ↓
Pending Approval
 ↓
 ┌───────────────┐
 │               │
Approve         Reject
 │               │
 ↓               ↓
Next Step       Rejected
 │
 ↓
Final Approval
 ↓
Approved
 ↓
Execute
```

Approval tidak otomatis berarti transaksi sudah mengubah stock.

**Approval** dan **Execution** merupakan dua tahap berbeda.

---

# 20. Transaction History Flow

Setiap transaksi yang berhasil dieksekusi:

```text
Transaction
    ↓
Generate Reference Number
    ↓
Execute
    ↓
Record Transaction
    ↓
Record Inventory Ledger
    ↓
Update Stock Balance
    ↓
Available in Transaction History
```

User dapat menggunakan reference number untuk melakukan tracing.

---

# 21. Audit Log Flow

Aktivitas penting:

```text
User Action
    ↓
Authorization
    ↓
Business Operation
    ↓
Audit Event
    ↓
Audit Log
```

Audit Log minimal dapat menjawab:

```text
Who?
What?
Which Entity?
When?
What Changed?
```

---

# 22. Reporting Flow

```text
User
 ↓
Select Report
 ↓
Set Filter
 ↓
System checks Permission + Scope
 ↓
Generate Report
 ↓
View
 ├── Screen
 ├── Excel
 └── PDF
```

User hanya mendapatkan data yang berada dalam scope yang diizinkan.

---

# 23. REST API Flow

External client:

```text
Client
 ↓
Authentication
 ↓
API Request
 ↓
Validation
 ↓
Authorization
 ↓
Business Logic
 ↓
Response
```

API menggunakan:

```text
/api/v1/
```

API mengikuti business rules yang sama dengan web application.

Business logic tidak boleh dibuat berbeda hanya karena request berasal dari API.

---

# 24. Error Flow

Jika proses gagal:

```text
User Action
     ↓
Validation / Authorization / Business Rule
     ↓
Error
     ↓
Transaction Rolled Back
     ↓
No Partial Stock Update
     ↓
User receives error message
```

Operasi inventory yang melibatkan beberapa perubahan data harus menggunakan transaction boundary yang memastikan tidak terjadi partial update.

---

# 25. Cross-Module Flow

Alur paling penting Inventra:

```text
Department
    ↓
Purchase Request
    ↓
Approval
    ↓
Purchase Order
    ↓
Receiving
    ↓
Stock In
    ↓
Inventory
    ↓
Stock Balance
    ↓
Stock Out
    ↓
Department
```

Untuk physical control:

```text
Inventory
    ↓
Stock Opname
    ↓
Physical Count
    ↓
Difference
    ↓
Adjustment
    ↓
Inventory
```

Untuk asset:

```text
Stock In
    ↓
Asset Registration
    ↓
Assignment
    ↓
Asset Tracking
    ↓
Return
    ↓
Maintenance / Available
    ↓
Disposal
```

Untuk audit:

```text
User Action
     ↓
Module
     ↓
Transaction / Data Change
     ↓
Audit Log
```

---

# 26. Core Business Flow

Secara keseluruhan:

```text
                         ┌──────────────┐
                         │ Department   │
                         └──────┬───────┘
                                │
                                ▼
                       Purchase Request
                                │
                                ▼
                           Approval
                                │
                                ▼
                        Purchase Order
                                │
                                ▼
                           Receiving
                                │
                                ▼
                            Stock In
                                │
                                ▼
                    ┌─────────────────────┐
                    │      Inventory      │
                    │                     │
                    │ Stock Balance       │
                    │ Inventory Ledger    │
                    └──────┬──────┬───────┘
                           │      │
                 ┌─────────┘      └─────────┐
                 ▼                          ▼
             Stock Out                  Transfer
                 │                          │
                 ▼                          ▼
            Department                 Warehouse
                                            │
                                            ▼
                                      Stock Opname
                                            │
                                            ▼
                                       Adjustment

Inventory
    │
    └──────────────► Asset Management

Semua aktivitas penting
    │
    └──────────────► Audit Log
```

---

# 27. Flow Principles

User Flow Inventra mengikuti prinsip:

1. **Authorization before operation**
2. **Approval before controlled execution**
3. **Stock changes through inventory transactions**
4. **Inventory Ledger records stock movement**
5. **Stock Balance represents current operational balance**
6. **Physical differences are handled through Stock Opname and Adjustment**
7. **Important user actions are auditable**
8. **Every transaction should have a traceable reference**
9. **Department and warehouse scope must be enforced**
10. **API and Web follow the same business rules**
11. **Failed inventory operations must not produce partial updates**
12. **Approval does not automatically mean execution**

---

# 28. User Flow Completion Criteria

User Flow dianggap lengkap apabila setiap proses utama dapat ditelusuri dari:

```text
Actor
  ↓
Action
  ↓
Validation
  ↓
Authorization
  ↓
Approval (if required)
  ↓
Execution
  ↓
Data / Inventory Change
  ↓
History
  ↓
Audit
```

Flow detail implementasi akan diturunkan ke:

```text
docs/05_DATABASE.md
docs/06_API.md
docs/07_PERMISSION_MATRIX.md
docs/architecture/
docs/sprints/
docs/code-guide/
```

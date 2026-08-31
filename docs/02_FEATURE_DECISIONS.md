# 02 — Feature Decisions

**Project:** Inventra
**Status:** Accepted
**Purpose:** Menjadi sumber keputusan utama mengenai perilaku, aturan, dan scope fitur Inventra.

---

# 1. Purpose

Dokumen ini mencatat keputusan yang telah disepakati mengenai bagaimana fitur Inventra harus bekerja.

Dokumen ini digunakan sebagai acuan untuk:

* Product development
* UI/UX
* Backend implementation
* Frontend implementation
* Database design
* API development
* Testing
* Maintenance

Jika terdapat perbedaan antara implementasi dan keputusan di dokumen ini, keputusan fitur harus menjadi acuan sebelum melakukan perubahan.

---

# 2. General Feature Principles

Inventra mengikuti prinsip:

```text
Simple
Consistent
Traceable
Secure
Permission-based
Transaction-safe
```

Setiap fitur harus:

* Memiliki tujuan bisnis yang jelas.
* Mengikuti RBAC dan permission.
* Tidak boleh melewati business rule.
* Memiliki validation di backend.
* Memiliki state yang jelas jika menggunakan workflow.
* Mencatat aktivitas penting melalui Audit Log.
* Menggunakan Inventory Ledger untuk perubahan stock.
* Mempertimbangkan concurrency untuk transaksi inventory.

---

# 3. Authentication

### Decisions

1. User harus melakukan authentication untuk mengakses aplikasi.
2. User yang belum authenticated tidak dapat mengakses protected page.
3. Session/authentication dikelola oleh backend.
4. Password tidak boleh disimpan dalam bentuk plaintext.
5. Logout harus mengakhiri session secara aman.
6. Authentication failure tidak boleh membocorkan informasi sensitif.
7. Aktivitas authentication penting dapat dicatat dalam Audit Log.

### Scope

```text
Login
Logout
Session
Authentication State
Password Management
```

---

# 4. RBAC & Permission

Inventra menggunakan:

```text
Role
+
Permission
+
Resource Scope
```

### Decisions

1. Permission menjadi sumber kontrol akses fitur.
2. Role digunakan untuk mengelompokkan permission.
3. User dapat memiliki role tertentu sesuai kebutuhan.
4. Akses warehouse dapat dibatasi berdasarkan scope.
5. UI menyembunyikan action yang tidak dimiliki user.
6. Backend tetap wajib melakukan authorization.
7. UI tidak dianggap sebagai security boundary.

Contoh:

```text
stock-in.create
stock-in.update
stock-in.submit
stock-out.create
stock-out.approve
stock-opname.create
report.view
audit.view
```

---

# 5. Master Data

Master data digunakan sebagai dasar operasi inventory.

Contoh:

```text
Categories
Units
Suppliers
Warehouses
Locations
Item Attributes
```

### Decisions

1. Master data harus memiliki validation.
2. Data yang masih digunakan oleh transaction tidak boleh dihapus sembarangan.
3. Jika diperlukan, gunakan soft delete/deactivation.
4. Perubahan master data penting harus dapat ditelusuri.
5. Master data digunakan sebagai reference oleh transaction.

---

# 6. Item Management

Item merupakan entity utama inventory.

Minimal informasi:

```text
Item Code
Item Name
Category
Unit
Status
```

### Decisions

1. Item memiliki identifier yang unik.
2. Item harus memiliki unit.
3. Item dapat dikategorikan.
4. Item inactive tidak dapat digunakan untuk transaction baru.
5. Item yang sudah memiliki historical transaction tidak boleh dihapus secara destructive tanpa aturan khusus.
6. Perubahan penting terhadap item harus dapat diaudit.

---

# 7. Warehouse

Warehouse digunakan untuk menentukan lokasi inventory.

### Decisions

1. Inventory selalu memiliki warehouse scope.
2. User hanya dapat mengakses warehouse sesuai permission/scope.
3. Warehouse inactive tidak dapat digunakan untuk transaction baru.
4. Inventory antar warehouse harus diperlakukan sebagai movement yang terkontrol.
5. Warehouse access harus diterapkan pada backend.

---

# 8. Inventory

Inventra menggunakan:

```text
Inventory Balance
+
Inventory Ledger
```

### Decisions

`Inventory Balance` digunakan untuk mengetahui current stock.

`Inventory Ledger` digunakan untuk mencatat movement stock.

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
+5
```

Current stock:

```text
100 - 20 + 5 = 85
```

Ledger menjadi historical record dari movement tersebut.

---

# 9. Stock In

### Workflow

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

### Decisions

1. Membuat Stock In tidak langsung menambah stock.
2. Draft tidak mempengaruhi inventory.
3. Stock bertambah ketika transaction mencapai state yang diizinkan untuk posting.
4. Stock In yang sudah approved tidak boleh diedit secara normal.
5. Stock movement harus menghasilkan Inventory Ledger.
6. Transaction harus melewati authorization.
7. Transaction penting harus dapat diaudit.

---

# 10. Stock Out

### Workflow

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

### Decisions

1. Membuat Stock Out tidak langsung mengurangi stock.
2. Draft tidak mempengaruhi inventory.
3. Stock berkurang ketika transaction approved/posted sesuai business rule.
4. Stock Out harus melakukan stock validation.
5. Quantity tidak boleh menghasilkan stock invalid.
6. Stock movement menghasilkan Inventory Ledger.
7. Transaction approved tidak dapat diedit secara normal.
8. Approval mengikuti RBAC.
9. User tidak boleh melakukan action yang tidak dimiliki permission.

---

# 11. Stock Opname

Stock Opname digunakan untuk membandingkan:

```text
System Quantity
vs
Physical Quantity
```

### Decisions

1. User melakukan counting terhadap physical stock.
2. System menghitung variance.

Contoh:

```text
System = 100
Physical = 95

Variance = -5
```

3. Variance harus dapat ditelusuri.
4. Adjustment tidak boleh dilakukan secara silent.
5. Adjustment harus menghasilkan Inventory Ledger.
6. Adjustment mengikuti permission dan workflow yang ditentukan.
7. Historical result Stock Opname harus dipertahankan.

---

# 12. Asset Management

Asset digunakan untuk mengelola barang/aset yang membutuhkan tracking lebih detail.

### Decisions

1. Asset dapat memiliki identifier unik.
2. Asset dapat memiliki status.
3. Asset dapat dikaitkan dengan lokasi atau warehouse.
4. Perubahan status asset harus dapat ditelusuri.
5. Asset tidak otomatis dianggap sebagai inventory consumable.
6. Business rule asset harus dipisahkan dari stock movement jika behavior-nya berbeda.

---

# 13. Approval Workflow

Approval digunakan untuk transaction yang membutuhkan authorization tambahan.

### Standard State

```text
DRAFT
SUBMITTED
APPROVED
REJECTED
CANCELLED
```

Tidak semua transaction harus menggunakan seluruh state tersebut.

### Decisions

1. User hanya dapat melakukan transition yang diizinkan.
2. Approval harus menggunakan permission.
3. Approval tidak boleh dilakukan oleh user tanpa authorization.
4. Transaction approved dianggap finalized untuk workflow normal.
5. Transaction rejected tidak menjalankan efek inventory.
6. Setiap approval/rejection penting harus dapat diaudit.
7. State transition harus divalidasi backend.

---

# 14. Transaction History

Inventra harus menyediakan history transaction.

History digunakan untuk:

```text
Tracking
Investigation
Reporting
Operational Review
```

### Decisions

1. Transaction history tidak boleh hilang ketika transaction selesai.
2. History harus menunjukkan status transaction.
3. History dapat menunjukkan actor dan timestamp.
4. Historical transaction tidak boleh diubah secara sembarangan.
5. Detail transaction harus dapat ditelusuri ke inventory movement jika berkaitan dengan stock.

---

# 15. Reporting

Reporting digunakan untuk memberikan informasi operasional dan historical.

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

### Decisions

1. Report hanya menampilkan data yang boleh diakses user.
2. Warehouse scope harus diterapkan pada report.
3. Report besar harus menggunakan pagination/filtering atau proses yang sesuai.
4. Query reporting tidak boleh mengganggu transaction secara berlebihan.
5. Export report harus mengikuti permission.
6. Export penting dapat dicatat dalam Audit Log.

---

# 16. Dashboard

Dashboard memberikan overview kondisi inventory.

Contoh:

```text
Total Items
Total Stock
Low Stock
Pending Approval
Recent Transactions
Stock Movement
```

### Decisions

1. Dashboard menampilkan informasi sesuai role/permission.
2. Dashboard tidak harus menampilkan semua data.
3. Query dashboard harus dioptimalkan.
4. Informasi yang membutuhkan real-time accuracy tidak boleh menggunakan stale cache tanpa business approval.
5. Dashboard harus tetap usable pada mobile.

---

# 17. Audit Log

Audit Log digunakan untuk mencatat aktivitas bisnis dan security penting.

### Decisions

Audit event minimal dapat mencakup:

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

sesuai kebutuhan fitur.

Audit record dapat menyimpan:

```text
Actor
Action
Resource
Timestamp
Metadata
Before Value
After Value
```

jika diperlukan.

### Rules

1. Audit Log tidak boleh digunakan untuk menyimpan password atau secret.
2. Audit history tidak boleh diedit melalui application normal.
3. Audit Log tidak menggantikan Inventory Ledger.
4. Audit access harus menggunakan permission.
5. Audit event penting harus konsisten dengan transaction.

---

# 18. Export

Export digunakan untuk menghasilkan data dalam format yang dapat digunakan di luar aplikasi.

Contoh:

```text
CSV
Excel
PDF
```

### Decisions

1. Export harus mengikuti permission.
2. Export harus mengikuti warehouse scope.
3. Dataset besar harus diproses dengan aman.
4. Export tidak boleh memberikan data yang tidak boleh dilihat user.
5. Aktivitas export sensitif dapat dicatat dalam Audit Log.

---

# 19. Search, Filter & Sort

Feature yang memiliki dataset besar harus mendukung filtering sesuai kebutuhan.

Contoh:

```text
Search
Status
Warehouse
Category
Date Range
User
```

### Decisions

1. Filter hanya boleh menggunakan field yang diperbolehkan.
2. Search harus menggunakan query yang terkontrol.
3. Sorting harus menggunakan field yang diizinkan.
4. Query harus mempertimbangkan database index.
5. Dataset besar harus menggunakan pagination.

---

# 20. Pagination

Pagination digunakan untuk dataset besar.

Minimal untuk:

```text
Items
Transactions
Inventory Ledger
Audit Logs
Reports
```

### Decisions

1. Jangan mengirim seluruh dataset jika tidak diperlukan.
2. Offset pagination dapat digunakan untuk kebutuhan normal.
3. Cursor pagination dapat digunakan untuk dataset besar/high-volume.
4. Pagination harus tetap konsisten dengan filtering dan sorting.

---

# 21. Notification

Notification digunakan untuk memberi informasi kepada user mengenai event penting.

Contoh:

```text
Approval Required
Transaction Approved
Transaction Rejected
Low Stock
```

### Decisions

1. Notification harus memiliki alasan yang jelas.
2. Notification tidak boleh menggantikan audit trail.
3. Notification harus mengikuti permission.
4. Notification yang tidak penting tidak boleh membanjiri user.

---

# 22. Security

Semua feature harus mengikuti:

```text
Authentication
Authorization
Validation
Least Privilege
Secure Error Handling
Auditability
```

### Decisions

1. Backend adalah security boundary.
2. Frontend tidak boleh dipercaya untuk authorization.
3. Semua input harus divalidasi.
4. Sensitive information tidak boleh dikirim jika tidak diperlukan.
5. Error production tidak boleh membocorkan internal implementation.

---

# 23. Data Integrity

Inventra harus menjaga consistency antara:

```text
Transaction
Inventory Balance
Inventory Ledger
Audit Log
```

Untuk operation critical:

```text
BEGIN
   ↓
Business Operation
   ↓
Inventory Update
   ↓
Ledger
   ↓
Audit
   ↓
COMMIT
```

Jika gagal:

```text
ROLLBACK
```

---

# 24. Concurrency

Inventory dapat diakses oleh beberapa user secara bersamaan.

### Decisions

1. Critical inventory operation harus menggunakan transaction.
2. Race condition harus diperhatikan.
3. Row locking dapat digunakan jika diperlukan.
4. Lock scope harus seminimal mungkin.
5. Transaction tidak boleh dibuat lebih panjang dari yang diperlukan.
6. Stock tidak boleh menjadi negative akibat race condition jika business rule melarangnya.

---

# 25. Performance

Performance harus berdasarkan measurement.

### Decisions

Gunakan:

```text
EXPLAIN
EXPLAIN ANALYZE
```

untuk menganalisis query.

Prioritas:

```text
Correct Query
↓
Proper Index
↓
Avoid N+1
↓
Pagination
↓
Measure
↓
Cache if Needed
```

Jangan menambahkan index atau cache tanpa alasan yang jelas.

---

# 26. UI / UX

Inventra menggunakan:

```text
Responsive Design
Light Mode
Dark Mode
Plus Jakarta Sans
60:30:10 Color Principle
```

Visual identity:

```text
Trust
Honesty
Transparency
Stability
Professionalism
```

### Decisions

1. Tidak menggunakan warna purple dan turunannya.
2. Warna utama menggunakan neutral + blue/navy family.
3. UI harus responsive.
4. UI harus memiliki state loading, empty, error, success.
5. UI harus mengikuti permission.
6. UI tidak boleh menjadi satu-satunya layer security.

---

# 27. API

Inventra menggunakan:

```text
REST API
/api/v1
```

### Decisions

1. API menggunakan versioning.
2. Resource menggunakan naming yang konsisten.
3. API mengikuti authentication dan authorization.
4. API menggunakan validation.
5. API menggunakan response format yang konsisten.
6. API menggunakan pagination untuk collection besar.
7. API menggunakan business logic yang sama dengan web application.
8. API tidak boleh menjadi jalan untuk bypass permission.

---

# 28. Feature State Rules

Setiap feature yang memiliki workflow harus mendefinisikan:

```text
Initial State
Allowed Transition
Restricted Transition
Final State
```

Contoh:

```text
DRAFT
  ↓
SUBMITTED
  ↓
APPROVED
```

Tidak diperbolehkan secara normal:

```text
APPROVED
  ↓
DRAFT
```

kecuali terdapat business rule khusus.

---

# 29. Deletion Policy

Tidak semua data boleh dihapus secara destructive.

### Decisions

Gunakan pendekatan sesuai jenis data:

```text
Master Data
→ Deactivate / Soft Delete jika diperlukan

Transaction
→ Tidak destructive delete setelah finalized

Inventory Ledger
→ Historical record

Audit Log
→ Append-oriented
```

Deletion harus mempertimbangkan referential integrity dan historical data.

---

# 30. Out of Scope untuk V1

Feature berikut tidak menjadi requirement utama V1 kecuali kebutuhan berubah:

```text
Full Event Sourcing
Microservices
Database Sharding
External Search Engine
Complex AI Forecasting
Advanced Warehouse Automation
Multi-tenant SaaS Architecture
```

Feature tersebut dapat dipertimbangkan pada versi berikutnya jika terdapat business requirement yang jelas.

---

# 31. Decision Change Policy

Feature decision dapat berubah.

Jika terjadi perubahan:

```text
Old Decision
    ↓
New Requirement
    ↓
Impact Analysis
    ↓
Update Documentation
    ↓
Update Implementation
```

Perubahan yang berdampak pada architecture atau technology decision harus dipertimbangkan untuk dibuat sebagai ADR.

---

# 32. Source of Truth

Prioritas referensi ketika terjadi konflik:

```text
1. Latest Accepted Decision
2. PRD
3. Feature Decisions
4. Architecture
5. Module Documentation
6. Code Guide
7. Sprint Implementation
```

Namun jika perubahan merupakan keputusan arsitektur, ADR menjadi source of truth untuk keputusan tersebut.

---

# 33. Final Principle

Feature Inventra harus mengikuti prinsip:

```text
Business Requirement
        ↓
Feature Decision
        ↓
User Flow
        ↓
Architecture
        ↓
Database / API
        ↓
Code Guide
        ↓
Sprint
        ↓
Implementation
        ↓
Testing
```

Dokumentasi tidak boleh dibuat hanya untuk formalitas.

Setiap keputusan harus membantu developer memahami:

```text
What?
Why?
How?
Who can access it?
What happens to the data?
What happens when something fails?
```

---

# 34. Final Decision

`02_FEATURE_DECISIONS.md` menjadi **single reference untuk keputusan perilaku dan scope fitur Inventra**.

Dokumen ini harus diperbarui apabila terdapat perubahan business rule atau feature behavior yang signifikan.

Keputusan arsitektur/teknologi yang berdampak luas tetap dicatat melalui:

```text
decisions/ADR/
```

Dengan demikian:

```text
FEATURE DECISION
=
Bagaimana fitur harus berperilaku.

ADR
=
Kenapa keputusan teknis/arsitektur dipilih.
```

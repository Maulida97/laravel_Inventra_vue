# ADR-001 — PostgreSQL

**Project:** Inventra
**Status:** Accepted
**Date:** 2026-08-30

---

## 1. Context

Inventra merupakan aplikasi inventory yang mengelola:

- Item
- Warehouse
- Stock In
- Stock Out
- Stock Opname
- Asset
- Approval
- Transaction History
- Reporting
- Audit Log

Data tersebut memiliki banyak hubungan antar tabel dan membutuhkan **konsistensi data yang kuat**, terutama pada transaksi inventory.

Kesalahan pada data stock dapat menyebabkan informasi inventory, laporan, dan audit menjadi tidak akurat.

---

## 2. Decision

Inventra menggunakan **PostgreSQL sebagai database utama**.

PostgreSQL menjadi source of truth untuk:

```text
Master Data
Items
Warehouses
Inventory
Inventory Ledger
Transactions
Assets
Approvals
Audit Logs
```

---

## 3. Why PostgreSQL

### 3.1 Relational Data

Struktur Inventra sangat relational.

Contoh:

```text
Warehouse
    ↓
Inventory
    ↓
Item
    ↓
Stock Movement
    ↓
Transaction
```

Relational database cocok untuk menjaga hubungan tersebut.

---

### 3.2 Data Integrity

Inventory membutuhkan constraint dan transaction yang kuat.

Contoh:

```text
Stock Out
    ↓
Inventory Update
    ↓
Ledger Entry
```

Ketiga proses tersebut harus konsisten.

Jika salah satu proses gagal, transaksi dapat di-rollback.

---

### 3.3 Transaction Support

PostgreSQL mendukung database transaction yang diperlukan untuk operasi inventory.

Contoh konsep:

```text
BEGIN
    Create Transaction
    Create Transaction Detail
    Update Inventory
    Create Ledger
COMMIT
```

Jika terjadi error:

```text
ROLLBACK
```

---

### 3.4 Constraint

PostgreSQL dapat digunakan untuk menjaga aturan database seperti:

```text
PRIMARY KEY
FOREIGN KEY
UNIQUE
NOT NULL
CHECK
```

Contoh:

```text
SKU harus unique
Transaction harus memiliki warehouse valid
Inventory harus memiliki item valid
```

---

### 3.5 Indexing

Inventra akan memiliki query yang sering digunakan untuk:

```text
SKU
Warehouse
Item
Transaction
Status
Created At
```

PostgreSQL mendukung indexing untuk meningkatkan performa query tersebut.

Index tidak dibuat secara sembarangan.

Index akan ditentukan berdasarkan:

```text
Query Pattern
Data Volume
EXPLAIN ANALYZE
Actual Workload
```

---

### 3.6 Reporting

Inventra memiliki kebutuhan reporting dan dashboard.

PostgreSQL mendukung:

```text
JOIN
Aggregation
GROUP BY
Filtering
Subquery
CTE
Window Function
```

yang dapat digunakan untuk kebutuhan tersebut.

---

## 4. Alternatives Considered

### MySQL

MySQL dapat digunakan untuk aplikasi inventory dan merupakan alternatif yang valid.

Namun untuk Inventra, PostgreSQL dipilih karena:

```text
Strong relational capabilities
Transaction support
Constraint capabilities
Advanced SQL
Reporting flexibility
```

---

### SQLite

SQLite cocok untuk:

```text
Prototype
Small Local Application
Testing
```

Tetapi tidak dipilih sebagai database production Inventra karena aplikasi dirancang untuk:

```text
Multi-user
Concurrent Transactions
VPS Deployment
Centralized Database
```

---

### NoSQL Database

NoSQL tidak dipilih sebagai primary database karena struktur utama Inventra sangat relational dan membutuhkan:

```text
Foreign Keys
Transactions
Constraints
Joins
Consistent Inventory State
```

NoSQL masih dapat dipertimbangkan sebagai teknologi pendukung apabila kebutuhan khusus muncul di masa depan.

---

## 5. Consequences

### Positive

```text
+ Strong data integrity
+ Reliable transactions
+ Powerful SQL
+ Good relational modeling
+ Suitable for inventory ledger
+ Suitable for reporting
+ Strong indexing capabilities
```

### Negative

```text
- Requires proper schema design
- Requires database migration management
- Complex queries need optimization
- Database maintenance is required
```

---

## 6. Implementation Rules

Database development Inventra mengikuti prinsip:

```text
Migration
    ↓
Schema
    ↓
Constraint
    ↓
Index
    ↓
Query
    ↓
Performance Analysis
```

Jangan menyelesaikan masalah performa hanya dengan menambahkan index.

Query harus dianalisis terlebih dahulu.

Gunakan:

```text
EXPLAIN
EXPLAIN ANALYZE
```

untuk query yang penting atau berat.

---

## 7. Related Decisions

Keputusan ini berkaitan dengan:

```text
ADR-003 — Inventory Ledger
ADR-008 — Audit Log
```

Karena PostgreSQL menjadi database utama untuk menjaga integritas inventory dan audit data.

---

## 8. Final Decision

**Accepted**

PostgreSQL digunakan sebagai **primary production database Inventra**.

Perubahan database utama di masa depan harus dibuat sebagai Architecture Decision Record baru dan menjelaskan alasan perubahan tersebut.

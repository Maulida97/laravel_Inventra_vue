# ADR-007 — Database Performance

**Project:** Inventra
**Status:** Accepted
**Date:** 2026-08-30

---

# 1. Context

Inventra akan menyimpan data yang terus bertambah, terutama:

```text
Items
Inventory
Stock In
Stock Out
Stock Opname
Transactions
Inventory Ledger
Audit Log
```

Beberapa tabel seperti:

```text
inventory_ledger
audit_logs
transactions
```

berpotensi menjadi tabel besar.

Performance database harus dipertimbangkan sejak awal tanpa melakukan premature optimization.

---

# 2. Problem

Query sederhana dapat menjadi lambat ketika data bertambah.

Contoh:

```sql
SELECT *
FROM inventory_ledger
WHERE warehouse_id = ?
AND item_id = ?;
```

Jika tidak memiliki index yang sesuai, PostgreSQL dapat melakukan scan terhadap banyak record.

Masalah yang mungkin muncul:

```text
Slow Query
High CPU
High Database Load
Long Response Time
N+1 Query
Large Payload
```

---

# 3. Decision

Inventra menggunakan pendekatan:

```text
Correct Schema
+
Proper Indexing
+
Efficient Query
+
Pagination
+
Eager Loading
+
Query Analysis
+
Transaction / Locking
```

Optimization dilakukan berdasarkan workload dan hasil pengukuran, bukan sekadar menambahkan index pada semua column.

---

# 4. Database Engine

Inventra menggunakan:

```text
PostgreSQL
```

sesuai:

```text
ADR-001 — PostgreSQL
```

PostgreSQL menjadi primary relational database Inventra.

---

# 5. Indexing Strategy

Index dibuat berdasarkan pola query aktual.

Column yang sering digunakan untuk:

```text
WHERE
JOIN
ORDER BY
```

dapat menjadi kandidat index.

Contoh:

```text
warehouse_id
item_id
transaction_id
status
created_at
```

Namun tidak semua column harus di-index.

---

# 6. Primary Key

Setiap table utama menggunakan primary key yang sesuai dengan database design Inventra.

Contoh:

```text
id
```

Primary key otomatis memiliki index.

Jangan membuat index tambahan pada primary key tanpa alasan teknis.

---

# 7. Foreign Key Index

Foreign key yang sering digunakan dalam:

```text
JOIN
WHERE
Relationship Query
```

dapat diberikan index.

Contoh:

```text
warehouse_id
item_id
user_id
transaction_id
```

Hal ini terutama penting untuk table dengan jumlah record besar.

---

# 8. Composite Index

Jika query sering menggunakan beberapa column secara bersamaan, gunakan composite index.

Contoh query:

```sql
SELECT *
FROM inventory_ledger
WHERE warehouse_id = ?
AND item_id = ?
ORDER BY created_at DESC;
```

Kandidat index:

```text
(warehouse_id, item_id, created_at)
```

Urutan column harus ditentukan berdasarkan query pattern dan selectivity.

Jangan membuat composite index hanya berdasarkan asumsi.

---

# 9. Index Column Order

Urutan composite index penting.

Contoh:

```text
INDEX (warehouse_id, item_id, created_at)
```

berbeda dengan:

```text
INDEX (created_at, warehouse_id, item_id)
```

Pemilihan urutan mempertimbangkan:

```text
Query Pattern
Selectivity
Filtering
Sorting
Data Distribution
```

---

# 10. Inventory Query

Query inventory merupakan salah satu query penting Inventra.

Contoh:

```text
Current Stock
=
Warehouse
+
Item
```

Current stock sebaiknya membaca:

```text
inventory_balances
```

daripada menghitung seluruh ledger setiap request.

Ledger digunakan untuk:

```text
History
Movement
Reporting
Reconciliation
Audit Investigation
```

---

# 11. Inventory Ledger Index

`inventory_ledger` berpotensi menjadi tabel besar.

Index kandidat:

```text
warehouse_id
item_id
transaction_id
created_at
```

Untuk query kombinasi, composite index dapat digunakan.

Contoh:

```text
(warehouse_id, item_id, created_at)
```

Index final harus divalidasi menggunakan query aktual.

---

# 12. Transaction Index

Transaction sering difilter berdasarkan:

```text
status
warehouse_id
created_by
created_at
```

Index dibuat berdasarkan endpoint dan query yang benar-benar digunakan.

Contoh:

```text
GET /transactions?status=SUBMITTED
```

dapat membutuhkan index yang mendukung filtering tersebut jika workload cukup besar.

---

# 13. Status Index

Column seperti:

```text
status
```

tidak otomatis harus di-index.

Jika cardinality sangat rendah:

```text
DRAFT
SUBMITTED
APPROVED
REJECTED
```

index tunggal pada `status` mungkin tidak selalu memberikan keuntungan besar.

Jika status digunakan bersama filter lain, composite index dapat lebih sesuai.

---

# 14. Timestamp Index

Column:

```text
created_at
updated_at
transaction_date
```

dapat menjadi kandidat index jika sering digunakan untuk:

```text
ORDER BY
Date Range
Reporting
Pagination
```

Contoh:

```sql
WHERE created_at >= ?
ORDER BY created_at DESC
```

---

# 15. Query Optimization

Query harus mengambil hanya data yang diperlukan.

Hindari:

```sql
SELECT *
```

jika hanya membutuhkan beberapa column.

Lebih baik:

```sql
SELECT id, name, quantity
FROM items;
```

Tujuannya:

```text
Less Data
Less Memory
Less Network Payload
Better Performance
```

---

# 16. N+1 Query

Inventra harus menghindari N+1 query.

Contoh masalah:

```text
1 query
   ↓
100 items
   ↓
100 query warehouse
```

Total:

```text
101 queries
```

Jika relationship memang dibutuhkan, gunakan eager loading secara tepat.

Contoh konsep Laravel:

```php
Item::with('category')->get();
```

Namun eager loading juga tidak boleh dilakukan secara membabi buta.

Load hanya relationship yang dibutuhkan.

---

# 17. Pagination

Collection besar harus menggunakan pagination.

Contoh:

```text
Items
Transactions
Inventory Ledger
Audit Logs
```

Jangan:

```text
SELECT millions of rows
```

untuk satu request.

Gunakan pagination sesuai kebutuhan endpoint.

---

# 18. Cursor Pagination

Untuk dataset besar atau data yang terus bertambah, cursor pagination dapat dipertimbangkan.

Contoh:

```text
Audit Log
Inventory Ledger
Transaction History
```

Cursor pagination dapat lebih efisien daripada offset pagination pada kondisi tertentu.

Pemilihan:

```text
Offset
vs
Cursor
```

ditentukan berdasarkan query pattern dan kebutuhan endpoint.

---

# 19. Search

Search harus menggunakan strategi yang sesuai dengan kebutuhan.

Untuk pencarian sederhana:

```text
WHERE name LIKE ...
```

dapat digunakan pada dataset kecil.

Jika dataset besar dan kebutuhan search berkembang, pertimbangkan PostgreSQL indexing/search capability yang sesuai.

Jangan langsung menambahkan external search engine sebelum kebutuhan benar-benar muncul.

---

# 20. EXPLAIN

Query penting harus dapat dianalisis menggunakan:

```sql
EXPLAIN
```

Untuk mengetahui query plan.

Contoh:

```sql
EXPLAIN
SELECT *
FROM inventory_ledger
WHERE warehouse_id = 1
AND item_id = 10;
```

---

# 21. EXPLAIN ANALYZE

Untuk mengukur actual execution:

```sql
EXPLAIN ANALYZE
SELECT ...
```

Ini digunakan untuk melihat:

```text
Actual Execution Time
Rows
Planning Time
Execution Time
Scan Type
Join Strategy
```

Gunakan dengan hati-hati pada query yang memiliki side effect karena `EXPLAIN ANALYZE` benar-benar menjalankan statement tertentu.

Untuk query read-only, aman digunakan sebagai profiling tool.

---

# 22. Sequential Scan

Sequential scan tidak selalu berarti query buruk.

PostgreSQL dapat memilih sequential scan ketika:

```text
Table Small
Large Portion of Table Needed
Index Not Beneficial
```

Jangan memaksa index hanya karena melihat:

```text
Seq Scan
```

Evaluasi berdasarkan:

```text
Execution Time
Rows
Cost
Actual Workload
```

---

# 23. Query Performance Workflow

Jika query lambat:

```text
Slow Query
    ↓
Measure
    ↓
EXPLAIN ANALYZE
    ↓
Identify Bottleneck
    ↓
Optimize Query / Index
    ↓
Measure Again
```

Bukan:

```text
Slow Query
    ↓
Add Random Index
```

---

# 24. Database Transactions

Operation yang mengubah beberapa data harus menggunakan database transaction jika atomicity diperlukan.

Contoh Stock Out:

```text
BEGIN
 ↓
Create Transaction
 ↓
Create Ledger
 ↓
Update Balance
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

# 25. Concurrency

Inventory merupakan resource yang dapat diakses secara concurrent.

Contoh:

```text
User A → Stock Out
User B → Stock Out
```

Database harus memastikan kedua operation tidak menghasilkan inventory yang invalid.

Gunakan mekanisme PostgreSQL/Laravel yang sesuai seperti:

```text
Database Transaction
Row Locking
Atomic Update
Isolation Control
```

sesuai kebutuhan operation.

---

# 26. Row Locking

Untuk kondisi tertentu, row locking dapat digunakan ketika membaca dan mengubah inventory secara concurrent.

Konsep:

```text
SELECT inventory_balance
FOR UPDATE;
```

Kemudian:

```text
Validate
 ↓
Update
 ↓
Commit
```

Tujuannya mencegah race condition pada critical inventory operation.

Lock hanya digunakan pada bagian yang memang membutuhkan locking.

---

# 27. Deadlock

Penggunaan lock harus memperhatikan kemungkinan deadlock.

Contoh:

```text
Transaction A
locks Item 1
waits Item 2

Transaction B
locks Item 2
waits Item 1
```

Strategy:

```text
Consistent Lock Ordering
Short Transactions
Minimal Lock Scope
Retry When Appropriate
```

---

# 28. Caching

Cache bukan solusi pertama untuk database yang lambat.

Urutan optimization:

```text
Correct Query
 ↓
Proper Index
 ↓
Reduce Data
 ↓
Fix N+1
 ↓
Pagination
 ↓
Measure
 ↓
Cache if Necessary
```

Cache digunakan jika terdapat read-heavy data yang cocok untuk caching.

---

# 29. Cache Invalidation

Jika current inventory di-cache, perubahan stock harus mempertimbangkan cache invalidation.

Contoh:

```text
Stock Updated
   ↓
Inventory Cache Invalidated
```

Jangan menggunakan cache yang dapat menyebabkan current stock menampilkan data stale tanpa business acceptance.

---

# 30. Reporting Queries

Reporting dapat menghasilkan query berat.

Jangan menjalankan query analytics berat pada setiap dashboard request jika tidak diperlukan.

Gunakan:

```text
Pagination
Pre-aggregation
Caching
Optimized Query
Background Processing
```

sesuai kebutuhan.

---

# 31. Dashboard Queries

Dashboard harus menggunakan query yang terukur.

Contoh:

```text
Total Items
Current Stock
Pending Approval
Recent Transactions
Stock Movement
```

Jangan melakukan query seluruh historical ledger hanya untuk menampilkan angka sederhana jika balance atau aggregate data sudah tersedia.

---

# 32. Large Tables

Table yang berpotensi besar:

```text
inventory_ledger
audit_logs
transactions
```

harus dipantau pertumbuhannya.

Jika volume sangat besar di masa depan, partitioning dapat dipertimbangkan.

Namun partitioning **bukan default V1**.

---

# 33. Partitioning

Partitioning dapat dipertimbangkan berdasarkan:

```text
Table Size
Query Pattern
Retention Policy
Write Volume
Date Range Query
Operational Requirement
```

Contoh kandidat:

```text
audit_logs
inventory_ledger
```

berdasarkan waktu.

Keputusan partitioning dibuat ketika evidence menunjukkan bahwa partitioning diperlukan.

---

# 34. Database Connection

Application harus menggunakan connection pool / connection management sesuai environment deployment.

Perhatikan:

```text
Application Workers
Concurrent Requests
Database Connection Limit
Background Jobs
```

Jangan membuat jumlah connection melebihi kemampuan PostgreSQL.

---

# 35. Production Monitoring

Production harus dapat memantau:

```text
Query Latency
Database CPU
Memory
Connections
Locks
Slow Queries
Table Growth
Index Usage
```

Monitoring dapat ditingkatkan seiring skala aplikasi.

---

# 36. Migration Safety

Migration harus mempertimbangkan production data.

Hindari migration yang berpotensi:

```text
Long Table Lock
Large Blocking Operation
Unexpected Data Loss
```

Untuk perubahan besar:

```text
Migration
 ↓
Test
 ↓
Measure
 ↓
Deploy Carefully
```

---

# 37. Index Maintenance

Index memiliki cost.

Setiap index dapat meningkatkan:

```text
Storage
INSERT Cost
UPDATE Cost
DELETE Cost
```

Karena itu:

> Jangan membuat index pada semua column.

Index harus memiliki alasan berdasarkan query pattern.

---

# 38. Performance Testing

Endpoint penting harus diuji menggunakan data yang realistis.

Minimal perhatikan:

```text
Items
Transactions
Inventory
Ledger
Audit Logs
```

Performance test sebaiknya tidak hanya menggunakan database kosong.

---

# 39. Alternatives Considered

### Redis First

Tidak dipilih sebagai solusi database performance utama.

Redis dapat digunakan kemudian untuk caching atau kebutuhan tertentu.

### Elasticsearch

Tidak digunakan untuk V1 karena kebutuhan search Inventra belum membutuhkan external search engine.

### Database Sharding

Tidak diperlukan untuk skala V1.

### Full Database Partitioning

Tidak digunakan sejak awal karena menambah kompleksitas operasional.

---

# 40. Consequences

### Positive

```text
+ Predictable database performance
+ Easier query optimization
+ Better scalability
+ Reduced unnecessary database load
+ Better inventory concurrency
+ Easier troubleshooting
```

### Negative

```text
- Requires performance monitoring
- Index maintenance required
- Query optimization requires measurement
- Locking introduces complexity
- Large-scale optimization may require additional infrastructure
```

---

# 41. Implementation Principle

Prinsip performance Inventra:

```text
Measure
   ↓
Understand Query
   ↓
Optimize
   ↓
Measure Again
```

Gunakan:

```text
EXPLAIN
EXPLAIN ANALYZE
```

sebagai tools utama untuk analisis query PostgreSQL.

---

# 42. Maintenance Guide

Jika endpoint lambat:

```text
1. Measure response time
2. Check query count
3. Check N+1
4. Check generated SQL
5. Run EXPLAIN ANALYZE
6. Check indexes
7. Check pagination
8. Check database load
9. Optimize
10. Measure again
```

Jika inventory mengalami race condition:

```text
1. Check transaction boundary
2. Check concurrent requests
3. Check row locking
4. Check isolation
5. Check ledger
6. Check inventory balance
```

Jika database semakin besar:

```text
1. Identify largest tables
2. Analyze query patterns
3. Review indexes
4. Review pagination
5. Review archival strategy
6. Consider partitioning only when justified
```

---

# 43. Related Decisions

```text
ADR-001 — PostgreSQL
ADR-003 — Inventory Ledger
ADR-005 — Approval Workflow
ADR-006 — API Architecture
```

Dokumen terkait:

```text
05_DATABASE.md
06_API.md
architecture/DATA_FLOW.md
```

---

# 44. Final Decision

**Accepted**

Inventra menggunakan pendekatan database performance berbasis **measurement dan workload**, bukan premature optimization.

Prinsip utama:

```text
Good Schema
    ↓
Proper Index
    ↓
Efficient Query
    ↓
Pagination
    ↓
No N+1
    ↓
EXPLAIN ANALYZE
    ↓
Concurrency Control
    ↓
Monitoring
```

Untuk V1:

```text
PostgreSQL
+
Indexing
+
Query Optimization
+
Transaction
+
Locking
+
Pagination
```

menjadi fondasi utama performance database.

Caching, partitioning, Redis, maupun infrastructure tambahan hanya digunakan ketika terdapat kebutuhan dan evidence yang jelas.

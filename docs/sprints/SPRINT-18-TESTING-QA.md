# Inventra

## Sprint 18 — Testing & QA

**Sprint:** SPRINT-18
**Name:** Testing & QA
**Project:** Inventra
**Status:** Planned
**Branch:** `feature/testing-qa`

---

# 1. Sprint Overview

Sprint ini digunakan untuk melakukan pengujian menyeluruh terhadap Inventra sebelum deployment.

Fokus:

```text
Functional Testing
Integration Testing
API Testing
Security Testing
Database Testing
UI Testing
Performance Testing
Regression Testing
Bug Fixing
Release Validation
```

---

# 2. Objective

Memastikan:

```text
Feature works
Flow works
Permission works
Data is correct
API works
Security works
Performance acceptable
Regression controlled
```

---

# 3. Testing Strategy

Testing dilakukan berlapis:

```text
Unit Test
   ↓
Feature Test
   ↓
Integration Test
   ↓
API Test
   ↓
UI Test
   ↓
Security Test
   ↓
Performance Test
   ↓
Regression Test
   ↓
Release Test
```

Tidak semua logic harus memiliki jenis test yang sama.

---

# 4. Test Pyramid

Prioritas:

```text
        E2E / UI
       ─────────
      Integration
     ─────────────
    Feature Tests
   ────────────────
      Unit Tests
────────────────────
```

Semakin bawah:

```text
Faster
More Numerous
```

Semakin atas:

```text
Slower
Fewer
More Realistic
```

---

# 5. Functional Testing

Test seluruh fitur utama:

```text
Authentication
RBAC
Master Data
Item Management
Warehouse
Stock In
Stock Out
Stock Opname
Asset Management
Approval
Transaction History
Reporting
Dashboard
Audit Log
Export
```

---

# 6. Authentication Test

Test:

```text
[ ] Login success
[ ] Login failed
[ ] Invalid credentials
[ ] Logout
[ ] Session expiration
[ ] Password validation
[ ] Password reset
[ ] Rate limiting
```

---

# 7. RBAC Test

Test setiap role.

Contoh:

```text
Admin
Staff
Manager
```

Setiap role diuji terhadap:

```text
View
Create
Update
Delete
Approve
Export
```

Hasil harus sesuai permission matrix.

---

# 8. Master Data Test

Test:

```text
Create
Read
Update
Delete
Search
Filter
Validation
Duplicate
Inactive Status
```

Pastikan data yang sudah digunakan transaction tidak dapat dihapus secara sembarangan jika memiliki dependency.

---

# 9. Item Management Test

Test:

```text
Create Item
Update Item
Search Item
Filter Item
Category
Unit
SKU
Minimum Stock
Status
```

Validasi:

```text
Duplicate SKU
Invalid Category
Invalid Unit
Invalid Quantity
```

---

# 10. Warehouse Test

Test:

```text
Create Warehouse
Update Warehouse
Warehouse Status
Warehouse Scope
User Assignment
```

Critical:

```text
User A
 ↓
Warehouse A
```

tidak boleh melihat data:

```text
Warehouse B
```

tanpa permission.

---

# 11. Stock In Test

Test flow:

```text
Create Stock In
 ↓
Add Items
 ↓
Submit
 ↓
Approval
 ↓
Approved
 ↓
Inventory Increased
```

Pastikan:

```text
Pending
```

belum mengubah final stock jika business rule Inventra menetapkan perubahan stock hanya setelah approval.

---

# 12. Stock Out Test

Test:

```text
Create Stock Out
 ↓
Add Items
 ↓
Validate Stock
 ↓
Submit
 ↓
Approval
 ↓
Approved
 ↓
Inventory Decreased
```

Test insufficient stock:

```text
Available = 5
Request = 10
```

Result:

```text
Rejected
```

sesuai business rule.

---

# 13. Stock Opname Test

Test:

```text
Create Opname
 ↓
System Quantity
 ↓
Physical Count
 ↓
Difference
 ↓
Approval
 ↓
Adjustment
```

Test:

```text
Positive Difference
Negative Difference
Zero Difference
```

Pastikan adjustment tercatat dengan benar.

---

# 14. Asset Management Test

Test:

```text
Create Asset
Assign Asset
Transfer Asset
Change Status
Update Condition
Return Asset
Asset History
```

Pastikan asset history tetap traceable.

---

# 15. Approval Workflow Test

Test:

```text
Draft
 ↓
Submitted
 ↓
Pending Approval
 ↓
Approved / Rejected
```

Test unauthorized approval:

```text
Staff
 ↓
Approve
 ↓
403
```

jika staff tidak memiliki permission approve.

---

# 16. Transaction History Test

Pastikan setiap transaction:

```text
Stock In
Stock Out
Stock Opname
Asset Transaction
```

dapat ditelusuri.

Check:

```text
Transaction Number
Date
User
Warehouse
Status
Items
Quantity
Approval
```

---

# 17. Reporting Test

Test:

```text
Filter
Date Range
Warehouse
Category
Status
Totals
```

Bandingkan:

```text
Report
vs
Source Transaction
```

Nilainya harus konsisten.

---

# 18. Dashboard Test

Dashboard tidak boleh menggunakan angka yang berbeda dengan source data.

Contoh:

```text
Dashboard Stock
        vs
Inventory
```

harus konsisten.

Test:

```text
Total Items
Total Stock
Low Stock
Transactions
Assets
```

sesuai scope user.

---

# 19. Audit Log Test

Test event:

```text
Login
Create
Update
Delete
Approval
Export
Role Change
Permission Change
```

Pastikan:

```text
Actor
Action
Entity
Timestamp
```

tersimpan.

Audit Log tidak boleh berubah melalui normal user interface.

---

# 20. Export Test

Test:

```text
CSV
Excel
Filters
Date Range
Warehouse Scope
Permission
Large Dataset
Filename
```

Bandingkan:

```text
UI Data
vs
Export Data
```

---

# 21. API Testing

Test endpoint:

```text
Authentication
Items
Warehouses
Inventory
Stock
Assets
Transactions
Reports
Export
```

Periksa:

```text
HTTP Status
Request Validation
Response Structure
Authorization
Error Response
Pagination
Filtering
```

---

# 22. API Status Code

Gunakan status code secara konsisten.

Contoh:

```text
200 OK
201 Created
204 No Content
400 Bad Request
401 Unauthorized
403 Forbidden
404 Not Found
422 Validation Error
429 Too Many Requests
500 Internal Server Error
```

Tidak semua endpoint harus menggunakan seluruh status code.

---

# 23. Database Testing

Periksa:

```text
Foreign Key
Unique Constraint
Nullable
Default Value
Indexes
Cascade Rules
Transaction Integrity
```

Test invalid state.

Contoh:

```text
Transaction
 ↓
Invalid Warehouse ID
```

harus ditolak.

---

# 24. Inventory Integrity

Ini merupakan salah satu test paling penting.

Contoh:

```text
Opening Stock
+ Stock In
- Stock Out
± Adjustment
= Current Stock
```

Hasil ledger harus sama dengan inventory balance.

---

# 25. Inventory Ledger Test

Untuk setiap movement:

```text
Before
Movement
After
```

harus konsisten.

Contoh:

```text
Before = 100
Stock Out = 20
After = 80
```

Jika:

```text
After != Before - Movement
```

maka ada data integrity issue.

---

# 26. Transaction Atomicity

Stock transaction harus atomic.

Contoh:

```text
Stock Out
 ├── Header Created
 ├── Detail Created
 ├── Inventory Updated
 └── Ledger Created
```

Jika satu langkah gagal:

```text
Rollback
```

Tidak boleh menghasilkan transaction setengah jadi.

---

# 27. Security Regression

Ulangi test:

```text
IDOR
RBAC
Warehouse Scope
Mass Assignment
XSS
SQL Injection
API Authorization
File Access
Export Authorization
```

Setelah bug diperbaiki, test tersebut menjadi regression test.

---

# 28. UI Testing

Test browser:

```text
Login
Navigation
Forms
Modal
Table
Filter
Search
Pagination
Approval
Export
Notifications
Error State
Loading State
```

---

# 29. Responsive Testing

Minimal:

```text
Desktop
Tablet
Mobile
```

Fokus pada usability.

Tidak semua halaman harus memiliki layout mobile kompleks jika target utama Inventra adalah internal desktop application, tetapi tidak boleh terjadi UI yang unusable.

---

# 30. Browser Testing

Minimal test browser modern:

```text
Chrome
Edge
Firefox
```

Safari dapat diuji jika memang menjadi target deployment.

---

# 31. Validation Testing

Test:

```text
Required
Invalid Type
Invalid Format
Too Long
Too Short
Duplicate
Negative Quantity
Invalid Date
Invalid Status
```

Pastikan error ditampilkan dengan jelas.

---

# 32. Boundary Testing

Test batas:

```text
0
1
Maximum
Maximum + 1
Empty
Null
```

Contoh quantity:

```text
0
1
999999
1000000
```

sesuai business rule yang ditetapkan.

---

# 33. Search Testing

Test:

```text
Exact Match
Partial Match
Uppercase
Lowercase
Special Character
Empty Search
No Result
Large Result
```

---

# 34. Pagination Testing

Test:

```text
First Page
Middle Page
Last Page
Empty Page
Large Dataset
```

Pastikan:

```text
Total
Current Page
Per Page
```

konsisten.

---

# 35. Performance Testing

Test:

```text
Dashboard
Inventory
Transaction History
Reports
Export
Search
```

Measure:

```text
Response Time
Query Count
Memory
CPU
Database Load
```

---

# 36. N+1 Testing

Periksa halaman:

```text
Inventory
Transactions
Assets
Stock Movement
Reports
```

Jangan sampai:

```text
1 Query
+
N Queries
```

untuk relasi yang sebenarnya dapat diambil secara efisien.

---

# 37. Database Query Testing

Query berat dianalisis menggunakan:

```text
EXPLAIN
EXPLAIN ANALYZE
```

Periksa:

```text
Index Usage
Sequential Scan
Join Strategy
Rows Examined
Execution Time
```

---

# 38. Index Testing

Pastikan index tersedia untuk query yang sering digunakan.

Contoh kandidat:

```text
sku
warehouse_id
item_id
transaction_id
status
created_at
```

Index final mengikuti actual query workload.

---

# 39. Load Testing

Jika environment memungkinkan:

```text
10 Users
50 Users
100 Users
```

Test:

```text
Login
Dashboard
Inventory
Transaction
Report
Export
```

Tujuannya menemukan bottleneck, bukan sekadar mengejar angka concurrency tertentu.

---

# 40. Bug Classification

Gunakan severity:

### Critical

```text
System unusable
Data corruption
Security breach
Inventory corruption
```

### High

```text
Major feature broken
Incorrect stock
Authorization failure
```

### Medium

```text
Feature partially broken
Incorrect UI behavior
```

### Low

```text
Minor UI
Text
Non-critical cosmetic issue
```

---

# 41. Bug Lifecycle

```text
Open
 ↓
Triaged
 ↓
In Progress
 ↓
Fixed
 ↓
Retest
 ↓
Verified
 ↓
Closed
```

Jika gagal:

```text
Retest
 ↓
Failed
 ↓
Reopen
```

---

# 42. Bug Report Format

Gunakan:

```text
Bug ID:
BUG-001

Title:
Stock Out allows insufficient stock

Severity:
High

Environment:
Local / Staging

Steps:
1. Open Stock Out
2. Select Item
3. Request quantity > available stock
4. Submit

Expected:
Transaction rejected

Actual:
Transaction accepted

Evidence:
Screenshot / Log

Status:
Open
```

---

# 43. Regression Testing

Setelah perubahan:

```text
Feature Change
      ↓
Affected Tests
      ↓
Related Tests
      ↓
Core Regression
```

Jangan hanya test fitur yang baru diubah.

Contoh:

```text
Change Stock Out
```

harus mempertimbangkan:

```text
Inventory
Ledger
Reports
Dashboard
Transaction History
Audit
Export
```

---

# 44. Critical Regression Suite

Sebelum release, minimal:

```text
[ ] Login
[ ] RBAC
[ ] Warehouse Scope
[ ] Create Item
[ ] Stock In
[ ] Stock Out
[ ] Stock Balance
[ ] Stock Opname
[ ] Approval
[ ] Asset
[ ] Reports
[ ] Dashboard
[ ] Audit Log
[ ] Export
[ ] API
```

---

# 45. Test Data

Gunakan dedicated test data.

Contoh:

```text
TEST-ITEM-001
TEST-WH-001
TEST-USER-001
TEST-TRX-001
```

Jangan menggunakan production data untuk testing kecuali sudah melalui proses yang aman dan sesuai kebutuhan.

---

# 46. Test Environment

Ideal:

```text
Development
      ↓
Testing
      ↓
Staging
      ↓
Production
```

Production bukan environment untuk testing fitur.

---

# 47. Seed Data

Buat seed data untuk:

```text
Roles
Permissions
Users
Warehouses
Categories
Units
Items
Transactions
```

Tujuannya agar testing dapat diulang.

---

# 48. Automated Test Structure

Contoh:

```text
tests/
├── Unit/
├── Feature/
│   ├── Authentication/
│   ├── RBAC/
│   ├── Items/
│   ├── Warehouse/
│   ├── Stock/
│   ├── Assets/
│   ├── Reports/
│   └── Export/
└── Security/
```

---

# 49. Test Naming

Gunakan nama yang menjelaskan behavior.

Contoh:

```php
public function test_user_cannot_access_other_warehouse_inventory()
```

Lebih baik daripada:

```php
public function test_inventory_1()
```

Test harus membantu developer memahami expected behavior.

---

# 50. Frontend Test

Jika frontend testing digunakan:

```text
Components
Forms
Validation
State
Interaction
```

Prioritaskan komponen yang memiliki business impact tinggi.

---

# 51. API Contract Testing

Pastikan response API konsisten.

Contoh:

```json
{
  "data": [],
  "meta": {}
}
```

Struktur final mengikuti API design Inventra.

Test perubahan response agar tidak merusak consumer.

---

# 52. Error Testing

Setiap module diuji terhadap:

```text
401
403
404
422
429
500
```

sesuai endpoint.

Frontend harus menangani error tersebut dengan benar.

---

# 53. Data Consistency Testing

Bandingkan:

```text
Inventory
Ledger
Transaction
Dashboard
Report
Export
```

Jika semuanya merepresentasikan data yang sama, hasil harus konsisten.

---

# 54. End-to-End Scenario

### Scenario 1 — Stock In

```text
Login
 ↓
Create Stock In
 ↓
Submit
 ↓
Approve
 ↓
Inventory Increased
 ↓
Ledger Updated
 ↓
Dashboard Updated
 ↓
Audit Created
 ↓
Report Updated
 ↓
Export Contains Transaction
```

---

### Scenario 2 — Stock Out

```text
Login
 ↓
Create Stock Out
 ↓
Validate Stock
 ↓
Submit
 ↓
Approve
 ↓
Inventory Decreased
 ↓
Ledger Updated
 ↓
Audit Created
 ↓
Report Updated
```

---

### Scenario 3 — Unauthorized Access

```text
Staff
 ↓
Access Admin Resource
 ↓
Authorization
 ↓
403
 ↓
Audit Security Event if applicable
```

---

# 55. Release Candidate Testing

Sebelum deployment:

```text
RC Build
 ↓
Database Migration
 ↓
Seed / Setup
 ↓
Automated Tests
 ↓
Manual QA
 ↓
Security Tests
 ↓
Performance Tests
 ↓
Regression
 ↓
Release Decision
```

---

# 56. Release Checklist

```text
[ ] Tests pass
[ ] No Critical bug
[ ] No unresolved High security issue
[ ] Inventory integrity verified
[ ] Database migration verified
[ ] Environment verified
[ ] Security configuration verified
[ ] Backup verified
[ ] Rollback plan prepared
[ ] Documentation updated
```

---

# 57. Code Documentation

Testing code mengikuti:

```text
docs/code-guide/00_CODE_DOCUMENTATION_STANDARD.md
```

Test yang kompleks harus menjelaskan:

```text
Purpose
Scenario
Expected Behavior
Important Business Rule
```

Contoh:

```php
/**
 * Verify that stock cannot be issued beyond
 * the available inventory balance.
 *
 * Business Rule:
 * Stock Out must never create a negative
 * available quantity.
 */
```

---

# 58. Maintenance Guide

### "Fitur baru merusak fitur lama."

Cari:

```text
Feature Change
 ↓
Related Service
 ↓
Affected Tests
 ↓
Regression Suite
```

---

### "Test gagal setelah perubahan database."

Periksa:

```text
Migration
 ↓
Factory
 ↓
Seeder
 ↓
Test Data
 ↓
Assertions
```

---

### "Stock berbeda antara dashboard dan inventory."

Trace:

```text
Transaction
 ↓
Ledger
 ↓
Inventory
 ↓
Dashboard Query
```

Bandingkan source of truth.

---

# 59. Code Understanding Map

Untuk memahami test:

```text
Test
 ↓
Arrange
 ↓
Act
 ↓
Assert
```

Contoh:

```text
Arrange:
Create user + warehouse + item

Act:
Create Stock Out

Assert:
Inventory decreased
Ledger created
Audit created
```

Test bukan hanya alat untuk mencari bug.

Test juga merupakan **dokumentasi behavior aplikasi**.

---

# 60. Expected Files

```text
tests/
├── Unit/
├── Feature/
│   ├── Authentication/
│   ├── RBAC/
│   ├── MasterData/
│   ├── Items/
│   ├── Warehouse/
│   ├── StockIn/
│   ├── StockOut/
│   ├── StockOpname/
│   ├── Assets/
│   ├── Approval/
│   ├── Transactions/
│   ├── Reports/
│   ├── Dashboard/
│   ├── Audit/
│   └── Export/
│
├── Security/
└── Performance/
```

---

# 61. Git Branch

```text
feature/testing-qa
```

Dependency:

```text
SPRINT-01 → 17
        ↓
SPRINT-18
```

Testing dilakukan setelah fitur dan security baseline tersedia, tetapi test automation sebaiknya tetap dibuat sejak sprint fitur masing-masing.

---

# 62. Suggested Commits

```text
test(qa): add test structure
test(auth): add authentication tests
test(rbac): add permission tests
test(master-data): add master data tests
test(items): add item tests
test(warehouse): add warehouse tests
test(stock-in): add stock in tests
test(stock-out): add stock out tests
test(stock-opname): add stock opname tests
test(assets): add asset tests
test(approval): add approval tests
test(transactions): add transaction tests
test(reports): add reporting tests
test(dashboard): add dashboard tests
test(audit): add audit log tests
test(export): add export tests
test(api): add api tests
test(security): add security regression tests
test(inventory): add inventory integrity tests
test(performance): add performance tests
fix(qa): resolve critical test failures
docs(qa): add testing documentation
```

---

# 63. Acceptance Criteria

Sprint selesai apabila:

```text
1. Test structure tersedia.

2. Authentication tests tersedia.

3. RBAC tests tersedia.

4. Master Data tests tersedia.

5. Item tests tersedia.

6. Warehouse tests tersedia.

7. Stock In tests tersedia.

8. Stock Out tests tersedia.

9. Stock Opname tests tersedia.

10. Asset tests tersedia.

11. Approval tests tersedia.

12. Transaction tests tersedia.

13. Reporting tests tersedia.

14. Dashboard tests tersedia.

15. Audit tests tersedia.

16. Export tests tersedia.

17. API tests tersedia.

18. Security regression tests tersedia.

19. Inventory integrity test tersedia.

20. Transaction atomicity test tersedia.

21. Validation tests tersedia.

22. Authorization tests tersedia.

23. Warehouse scope tests tersedia.

24. IDOR tests tersedia.

25. Performance bottleneck telah diidentifikasi.

26. Query penting telah dianalisis.

27. N+1 telah diperiksa.

28. Critical regression suite berhasil.

29. Tidak ada Critical bug yang unresolved.

30. Security issue High/Critical tidak unresolved.

31. Release checklist selesai.

32. Dokumentasi testing tersedia.

33. Developer dapat memahami behavior melalui test.

34. Test dapat dijalankan kembali secara konsisten.
```

---

# 64. Definition of Done

```text
Functional
    ✓ All Core Features Tested
    ✓ Business Flow Tested
    ✓ Validation Tested

Integration
    ✓ Module Integration
    ✓ Inventory Integration
    ✓ Approval Integration
    ✓ Audit Integration

Security
    ✓ RBAC
    ✓ IDOR
    ✓ Scope
    ✓ API
    ✓ Export

Performance
    ✓ Query Review
    ✓ N+1 Review
    ✓ Critical Endpoint Review

Regression
    ✓ Critical Suite
    ✓ Bug Retest

Release
    ✓ No Critical Bug
    ✓ Security Review
    ✓ Migration Verified
    ✓ Rollback Plan

Documentation
    ✓ Test Documentation
    ✓ Code Comments
    ✓ Maintenance Guide

Git
    ✓ feature/testing-qa
```

---

# 65. Final QA Principle

Inventra dianggap siap release bukan karena:

```text
"Semua fitur sudah selesai."
```

Tetapi:

```text
Feature
   ↓
Works
   ↓
Works Together
   ↓
Secure
   ↓
Data Correct
   ↓
Performance Acceptable
   ↓
Regression Passed
   ↓
Release Ready
```

**Testing bukan tahap untuk mencari siapa yang salah. Testing adalah mekanisme untuk memastikan Inventra tetap dapat dipercaya ketika fitur, data, user, dan kondisi sistem semakin kompleks.**

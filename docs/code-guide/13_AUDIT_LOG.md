# Inventra

## Audit Log Code Guide

**Document:** Audit Log Code Guide
**Version:** V1.0
**Status:** Draft

---

# 1. Purpose

Audit Log digunakan untuk mencatat aktivitas penting yang terjadi di Inventra.

Tujuannya:

- Mengetahui siapa melakukan suatu tindakan.
- Mengetahui kapan tindakan dilakukan.
- Mengetahui data apa yang terpengaruh.
- Mengetahui perubahan yang terjadi.
- Membantu troubleshooting.
- Mendukung accountability.
- Membantu investigasi security incident.

Konsep sederhana:

```text
User
 ↓
Action
 ↓
Business Operation
 ↓
Audit Log
```

---

# 2. Audit Log vs Application Log

Keduanya berbeda.

### Audit Log

Mencatat **aktivitas bisnis/user**.

Contoh:

```text
Budi
CREATE
Stock Out
SO-00021
2026-08-30 10:20
```

### Application Log

Mencatat **kejadian teknis aplikasi**.

Contoh:

```text
Database connection failed
Payment API timeout
Exception occurred
```

Jadi:

```text
Audit Log
→ WHO did WHAT

Application Log
→ WHAT happened to the APPLICATION
```

---

# 3. Audit Log Architecture

```text
User
 ↓
Request
 ↓
Authentication
 ↓
Authorization
 ↓
Business Action
 ↓
Audit Event
 ↓
Audit Log
 ↓
Database
```

Audit log harus dibuat setelah tindakan berhasil dilakukan jika log tersebut merepresentasikan aksi bisnis yang berhasil.

---

# 4. Audit Log Data

Minimal informasi:

```text
id
user_id
action
module
resource_type
resource_id
description
old_values
new_values
ip_address
user_agent
created_at
```

Tidak semua field harus selalu terisi.

Contoh:

```text
User:
Budi

Action:
UPDATE

Module:
Item

Resource:
ITEM-0001

Old:
minimum_stock = 5

New:
minimum_stock = 10
```

---

# 5. Audit Event

Action dapat menggunakan standar:

```text
CREATE
UPDATE
DELETE
LOGIN
LOGOUT
APPROVE
REJECT
EXPORT
```

Gunakan vocabulary yang konsisten.

Jangan:

```text
CREATE
created
add
insert
new
```

untuk arti yang sama.

Pilih satu standar.

---

# 6. Module

Audit Log harus menunjukkan module asal aktivitas.

Contoh:

```text
AUTHENTICATION
RBAC
MASTER_DATA
ITEM
WAREHOUSE
STOCK_IN
STOCK_OUT
STOCK_OPNAME
ASSET
APPROVAL
REPORTING
```

Contoh:

```text
Action:
CREATE

Module:
STOCK_OUT
```

---

# 7. Resource

Resource menunjukkan object yang terkena tindakan.

Contoh:

```text
resource_type:
StockOut

resource_id:
SO-00021
```

atau:

```text
resource_type:
Item

resource_id:
ITEM-0001
```

---

# 8. Audit Log Table

Concept:

```text
audit_logs
├── id
├── user_id
├── action
├── module
├── resource_type
├── resource_id
├── description
├── old_values
├── new_values
├── ip_address
├── user_agent
└── created_at
```

`old_values` dan `new_values` dapat menggunakan JSON/JSONB sesuai desain database Inventra.

---

# 9. Audit Log Flow

Contoh Stock Out:

```text
User
 ↓
Submit Stock Out
 ↓
Validate
 ↓
Authorize
 ↓
Create Stock Out
 ↓
Update Inventory Ledger
 ↓
Create Audit Log
 ↓
Response
```

Audit log harus merepresentasikan event yang benar-benar terjadi.

---

# 10. Transaction Consistency

Untuk operasi penting:

```text
Business Transaction
+
Audit Log
```

sebaiknya berada dalam database transaction jika keduanya harus konsisten.

Concept:

```text
DB Transaction
├── Create Stock Out
├── Update Inventory
└── Create Audit Log
```

Jika transaction rollback:

```text
Stock Out
✕
Inventory Update
✕
Audit Log
✕
```

Dengan demikian tidak terjadi audit log yang menyatakan transaksi berhasil padahal business transaction gagal.

---

# 11. Example

```php
DB::transaction(function () use ($data) {

    $stockOut = $this->createStockOut($data);

    $this->updateInventory($stockOut);

    $this->auditLogger->log(
        action: 'CREATE',
        module: 'STOCK_OUT',
        resource: $stockOut
    );
});
```

Ini hanya contoh konsep. Implementasi final mengikuti architecture Inventra.

---

# 12. Audit Logger

Agar kode tidak berulang, gunakan service khusus.

Concept:

```text
app/Services/
└── Audit/
    └── AuditLogger.php
```

Tanggung jawab:

```text
Create audit event
Normalize data
Store metadata
Handle old/new values
```

Contoh penggunaan:

```text
Business Service
 ↓
AuditLogger
 ↓
audit_logs
```

---

# 13. Jangan Menulis Audit Log di Semua Tempat

Hindari:

```text
Controller
Service
Model
Observer
Event
```

semuanya membuat audit log untuk event yang sama.

Risiko:

```text
1 action
 ↓
5 audit logs
```

Audit strategy harus ditentukan secara konsisten.

---

# 14. Audit Strategy

Inventra dapat menggunakan kombinasi:

```text
Explicit Audit
+
Domain/Event-based Audit
```

### Explicit Audit

Business service secara eksplisit membuat log.

Cocok untuk:

```text
Approve
Reject
Stock In
Stock Out
Stock Opname
Asset Assignment
```

### Event-based Audit

Event/listener digunakan untuk aktivitas tertentu yang konsisten.

Cocok untuk:

```text
Authentication
Model lifecycle tertentu
```

Jangan menggunakan event untuk semua hal hanya demi menghindari penulisan satu baris audit.

---

# 15. Old Values

Untuk UPDATE, old values membantu mengetahui kondisi sebelum perubahan.

Contoh:

```json
{
  "minimum_stock": 5,
  "status": "ACTIVE"
}
```

---

# 16. New Values

Contoh:

```json
{
  "minimum_stock": 10,
  "status": "ACTIVE"
}
```

Kemudian audit menunjukkan:

```text
minimum_stock
5 → 10
```

---

# 17. Jangan Simpan Semua Data

Audit log bukan tempat menyimpan seluruh object database.

Hanya simpan field yang relevan terhadap perubahan.

Hindari:

```text
Entire User Object
Entire Item Object
Password
Token
Session Secret
```

---

# 18. Sensitive Data

**Jangan pernah menyimpan** informasi sensitif seperti:

```text
Password
Password Hash
Authentication Token
API Secret
Session Token
Private Key
```

ke audit log.

Jika sebuah field sensitif berubah, audit cukup mencatat:

```text
password_changed = true
```

tanpa menyimpan nilainya.

---

# 19. IP Address

IP address dapat membantu security investigation.

Contoh:

```text
ip_address:
192.168.x.x
```

Gunakan hanya sesuai kebutuhan dan kebijakan aplikasi.

---

# 20. User Agent

User agent dapat membantu mengetahui client:

```text
Chrome
Firefox
Mobile Browser
```

Tetapi jangan menganggap user agent sebagai bukti identitas yang kuat karena dapat dimanipulasi.

---

# 21. Authentication Audit

Aktivitas authentication yang penting:

```text
LOGIN_SUCCESS
LOGIN_FAILED
LOGOUT
PASSWORD_CHANGED
PASSWORD_RESET
```

Contoh:

```text
User:
Budi

Action:
LOGIN_SUCCESS

Time:
2026-08-30 08:10
```

Untuk failed login, `user_id` mungkin tidak tersedia jika username/email tidak cocok.

---

# 22. Authorization Audit

Tidak semua authorization failure harus menjadi audit record bisnis.

Namun event security tertentu dapat dicatat.

Contoh:

```text
UNAUTHORIZED_ACCESS
```

Informasi:

```text
User
Route
Resource
Timestamp
IP
```

Application/security logging juga dapat digunakan sesuai kebutuhan.

---

# 23. CRUD Audit

### CREATE

```text
User creates Item
```

Log:

```text
CREATE
ITEM
ITEM-0001
```

### UPDATE

```text
User changes Item
```

Log:

```text
UPDATE
ITEM
ITEM-0001
old_values
new_values
```

### DELETE

```text
User deletes Item
```

Log:

```text
DELETE
ITEM
ITEM-0001
old_values
```

---

# 24. Business Actions

Tidak semua aktivitas hanya CRUD.

Contoh:

```text
APPROVE
REJECT
POST
CANCEL
ASSIGN
TRANSFER
ADJUST
```

Contoh stock opname:

```text
APPROVE
STOCK_OPNAME
SO-0001
```

Action harus merepresentasikan business operation.

---

# 25. Audit Log Immutability

Audit log sebaiknya:

```text
CREATE
READ
```

dan tidak dapat diedit oleh user biasa.

Hindari:

```text
UPDATE audit_logs
DELETE audit_logs
```

dari UI.

---

# 26. Admin Tidak Otomatis Boleh Menghapus Audit

Walaupun Admin memiliki privilege tinggi, audit log sebaiknya dilindungi.

Jika kebutuhan compliance mengharuskan retention/deletion, mekanismenya harus eksplisit dan terkontrol.

---

# 27. Audit Log Authorization

Contoh permission:

```text
audit.view
audit.export
```

Tidak semua user boleh melihat audit log.

---

# 28. Audit Log Scope

Jika user hanya boleh melihat warehouse tertentu:

```text
Audit Log
 ↓
Warehouse Scope
```

harus tetap diterapkan jika audit event mengandung data warehouse.

Jangan menganggap:

```text
audit.view
```

berarti otomatis:

```text
view everything
```

---

# 29. Audit Log Query

Filter minimal:

```text
User
Action
Module
Resource
Date From
Date To
```

Contoh:

```text
Module:
STOCK_OUT

Action:
CREATE

Date:
2026-08-01 → 2026-08-30
```

---

# 30. Audit Log Pagination

Audit log dapat berkembang sangat besar.

Gunakan:

```php
->paginate(20)
```

atau pagination yang sesuai kebutuhan.

Jangan:

```php
AuditLog::all();
```

untuk dataset besar.

---

# 31. Audit Log Index

Index mengikuti pola query.

Contoh:

```text
user_id
action
module
resource_type
resource_id
created_at
```

Untuk query:

```text
WHERE module = ?
AND created_at BETWEEN ? AND ?
```

dapat dipertimbangkan composite index:

```text
(module, created_at)
```

Keputusan final berdasarkan query pattern dan `EXPLAIN`.

---

# 32. Audit Log Performance

Audit log tidak boleh membuat transaksi utama menjadi berat secara tidak perlu.

Perhatikan:

```text
Index
Payload size
JSON size
Query
Insert frequency
Retention
```

Jika volume sangat besar, architecture dapat berkembang menjadi:

```text
Application
 ↓
Audit Event
 ↓
Queue
 ↓
Audit Storage
```

Namun untuk V1:

```text
Direct database insert
```

dapat lebih sederhana selama performanya mencukupi.

---

# 33. Queue Consideration

Queue cocok jika:

```text
Audit volume sangat tinggi
Audit processing mahal
External audit system
```

Tetapi ada trade-off.

Jika audit harus atomic dengan business transaction:

```text
Business transaction
+
Audit record
```

lebih cocok berada dalam transaction yang sama.

Jangan menggunakan queue secara otomatis untuk event yang membutuhkan jaminan atomicity.

---

# 34. Audit Log UI

Concept:

```text
resources/js/Pages/AuditLogs/
└── Index.vue
```

Components:

```text
resources/js/Components/Audit/
├── AuditFilters.vue
├── AuditTable.vue
└── AuditDetail.vue
```

---

# 35. Audit Log Detail

User dapat membuka detail:

```text
Action
Module
User
Time
Resource
Description
Old Values
New Values
```

Contoh:

```text
UPDATE ITEM

User:
Budi

Item:
ITEM-0001

Changes:

minimum_stock
5 → 10
```

---

# 36. JSON Display

`old_values` dan `new_values` sebaiknya ditampilkan secara readable.

Jangan langsung menampilkan raw JSON besar tanpa formatting.

Frontend:

```text
Old Values
minimum_stock: 5

New Values
minimum_stock: 10
```

---

# 37. Audit Export

Audit log dapat diexport jika diperlukan.

Permission:

```text
audit.export
```

Export harus tetap mengikuti:

```text
Authorization
Scope
Filters
Pagination/Chunking strategy
```

Jangan memberi export akses lebih luas daripada halaman audit.

---

# 38. Security

Audit log sendiri adalah data sensitif.

Lindungi dari:

```text
Unauthorized Read
Unauthorized Modification
Unauthorized Delete
Data Leakage
Sensitive Data Exposure
```

Audit log harus dianggap sebagai bagian dari security architecture.

---

# 39. Common Mistakes

### Mistake 1 — Menyimpan password

**Jangan.**

---

### Mistake 2 — Menyimpan token

**Jangan.**

---

### Mistake 3 — Audit hanya di frontend

Audit harus dibuat di backend.

Frontend dapat dimanipulasi.

---

### Mistake 4 — Audit sebelum transaction berhasil

Dapat menghasilkan:

```text
Audit:
Stock Out created

Actual:
Transaction rolled back
```

Gunakan transaction strategy yang tepat.

---

### Mistake 5 — Duplicate audit

Satu aksi menghasilkan beberapa log identik.

---

### Mistake 6 — Tidak ada resource ID

Sulit mengetahui object mana yang berubah.

---

### Mistake 7 — Tidak ada old/new values

Perubahan sulit ditelusuri.

---

### Mistake 8 — Audit log dapat diedit

Mengurangi reliability audit trail.

---

# 40. Maintenance Guide

### "Saya ingin menambahkan audit untuk fitur baru."

Cari:

```text
Business Service
```

kemudian tentukan:

```text
Action
Module
Resource
Description
Old Values
New Values
```

Lalu gunakan:

```text
AuditLogger
```

---

### "Audit tidak muncul."

Periksa:

```text
[ ] Business action berhasil?
[ ] AuditLogger dipanggil?
[ ] Database transaction rollback?
[ ] Event/listener aktif?
[ ] User authenticated?
[ ] audit_logs insert berhasil?
```

---

### "Audit muncul padahal transaksi gagal."

Periksa:

```text
[ ] Transaction boundary
[ ] Audit insert location
[ ] Queue
[ ] Event timing
```

Pastikan strategi audit sesuai kebutuhan atomicity.

---

### "Audit terlalu banyak."

Periksa:

```text
[ ] Duplicate observer
[ ] Duplicate event
[ ] Service logging
[ ] Model logging
```

Tentukan satu source of audit untuk setiap action.

---

### "Saya mau mengubah tampilan audit."

Cari:

```text
resources/js/Pages/AuditLogs/
resources/js/Components/Audit/
```

---

### "Saya mau menambahkan filter."

Flow:

```text
AuditFilters.vue
 ↓
Route
 ↓
Controller
 ↓
Request Validation
 ↓
Audit Query
 ↓
Database
```

---

### "Saya mau mengubah data yang dicatat."

Cari:

```text
Business Service
 ↓
AuditLogger
```

Bukan hanya Vue.

---

# 41. Code Reading Flow

Untuk memahami audit:

```text
Business Action
 ↓
Service
 ↓
AuditLogger
 ↓
Audit Model
 ↓
audit_logs
```

Untuk memahami tampilan:

```text
AuditLogs/Index.vue
 ↓
Inertia Props
 ↓
Audit Controller
 ↓
Audit Query
 ↓
Database
```

Untuk memahami perubahan:

```text
UPDATE Action
 ↓
Old Values
 ↓
Business Update
 ↓
New Values
 ↓
Audit Log
```

---

# 42. Debugging Checklist

Jika audit tidak tercatat:

```text
[ ] User authentication
[ ] Business action
[ ] AuditLogger
[ ] Transaction
[ ] Database insert
```

Jika audit salah:

```text
[ ] Action
[ ] Module
[ ] Resource
[ ] Old values
[ ] New values
```

Jika audit lambat:

```text
[ ] Index
[ ] Payload size
[ ] Query
[ ] Dataset
[ ] Retention
```

Jika user melihat audit yang tidak seharusnya:

```text
[ ] Permission
[ ] Policy
[ ] Scope
[ ] Query
```

---

# 43. Testing

Minimal:

```text
[ ] CREATE logged
[ ] UPDATE logged
[ ] DELETE logged
[ ] APPROVE logged
[ ] REJECT logged
[ ] LOGIN logged
[ ] Failed login handled
[ ] Old values correct
[ ] New values correct
[ ] User recorded
[ ] Resource recorded
[ ] Timestamp recorded
[ ] Unauthorized access blocked
[ ] Audit cannot be modified through UI
[ ] Audit cannot expose sensitive data
[ ] Transaction rollback does not create false success audit
[ ] Export respects permission and scope
```

---

# 44. Definition of Done

```text
[ ] Audit schema
[ ] AuditLogger
[ ] Business event integration
[ ] Authentication audit
[ ] CRUD audit
[ ] Business action audit
[ ] Authorization
[ ] Scope filtering
[ ] Audit UI
[ ] Detail view
[ ] Filtering
[ ] Pagination
[ ] Index
[ ] Sensitive data protection
[ ] Export
[ ] Tests
[ ] Documentation
```

---

# 45. Final Audit Architecture

```text
                         USER
                           │
                           ▼
                         ACTION
                           │
                           ▼
                     AUTHORIZATION
                           │
                           ▼
                    BUSINESS SERVICE
                           │
                    ┌──────┴──────┐
                    ▼             ▼
              BUSINESS DATA   AUDIT LOGGER
                    │             │
                    │             ▼
                    │        AUDIT RECORD
                    │             │
                    └──────┬──────┘
                           ▼
                     DB TRANSACTION
                           │
                           ▼
                       DATABASE
                           │
                           ▼
                       AUDIT LOG
                           │
                    ┌──────┴──────┐
                    ▼             ▼
                  AUDIT UI       EXPORT
```

---

# 46. Key Principle

Audit Log Inventra mengikuti prinsip:

```text
WHO
+
WHAT
+
WHEN
+
WHERE
+
WHICH RESOURCE
+
WHAT CHANGED
```

Untuk memahami kode:

```text
Business Action
 ↓
Service
 ↓
AuditLogger
 ↓
Database
```

Untuk memahami tampilan:

```text
AuditLogs/Index.vue
 ↓
Inertia Props
 ↓
Controller
 ↓
Query
 ↓
Database
```

Untuk debugging:

```text
Action
 ↓
Transaction
 ↓
AuditLogger
 ↓
audit_logs
```

Prinsip terpenting:

> **Audit Log adalah jejak aktivitas yang harus dapat dipercaya. Jangan mencatat data sensitif, jangan membiarkan audit mudah dimanipulasi, dan pastikan audit merepresentasikan aktivitas bisnis yang benar-benar terjadi.**

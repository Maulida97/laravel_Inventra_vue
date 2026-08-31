# Inventra

## Security Architecture

**Document:** Security Architecture
**Version:** V1.0
**Status:** Draft

---

# 1. Purpose

Dokumen ini mendefinisikan arsitektur keamanan Inventra.

Security Inventra dirancang untuk melindungi:

- User account.
- Inventory data.
- Transaction.
- Procurement.
- Asset.
- Approval.
- Audit history.
- API.
- Database.

Prinsip utama:

```text
Never trust the client.
```

Frontend hanya membantu UX.

**Security decision selalu dilakukan di backend.**

---

# 2. Security Architecture Overview

```text
                    USER / CLIENT
                         │
                         ▼
                    HTTPS / TLS
                         │
                         ▼
                  Laravel Application
                         │
          ┌──────────────┼──────────────┐
          ▼              ▼              ▼
   Authentication   Authorization   Validation
          │              │              │
          └──────────────┼──────────────┘
                         ▼
                   Business Logic
                         │
                         ▼
                      Database
                         │
                         ▼
                    Audit Log
```

---

# 3. Security Principles

Inventra mengikuti prinsip:

```text
Authentication
+
Authorization
+
Least Privilege
+
Deny by Default
+
Server-side Validation
+
Server-side Scope Checking
+
Secure Database Access
+
Auditability
+
Secure Configuration
```

---

# 4. Authentication

Authentication menjawab:

> "Siapa user ini?"

Flow:

```text
User
 ↓
Login
 ↓
Credential Verification
 ↓
Authenticated Session
 ↓
User Identity
```

Authentication tidak menentukan permission.

Contoh:

```text
User berhasil login
        ≠
User boleh melakukan Stock Out
```

Setelah login, authorization tetap dilakukan.

---

# 5. Password Security

Password:

- Tidak disimpan plaintext.
- Menggunakan secure password hashing.
- Tidak pernah dikirim kembali ke client.
- Tidak dicatat dalam application log.
- Tidak dimasukkan ke audit log.

Credential sensitif tidak boleh muncul di:

```text
Logs
Error Messages
API Responses
Database Query Logs
```

---

# 6. Session Security

Session harus:

```text
Secure
HttpOnly
SameSite
```

jika environment dan deployment mendukungnya.

Session ID harus diregenerasi setelah authentication untuk mencegah session fixation.

Logout harus mengakhiri authenticated session.

---

# 7. Authentication vs Authorization

Perbedaan:

```text
Authentication
=
Who are you?

Authorization
=
What can you do?
```

Contoh:

```text
Budi
 ↓
Authenticated
 ↓
Role: Warehouse Staff
 ↓
Permission: stock.out
 ↓
Scope: WH-001
```

---

# 8. RBAC Architecture

Inventra menggunakan Role-Based Access Control.

```text
User
 ↓
Role
 ↓
Permissions
```

Contoh:

```text
Warehouse Staff
├── stock.in
├── stock.out
├── stock.transfer
└── inventory.view
```

Role menentukan kumpulan permission.

---

# 9. Permission

Permission dibuat granular.

Contoh:

```text
inventory.view
inventory.create
inventory.update

stock.in.create
stock.out.create
stock.transfer.create

stock.opname.create
stock.opname.approve

purchase.request.create
purchase.request.approve

asset.view
asset.create
asset.assign
asset.dispose
```

Hindari permission terlalu umum seperti:

```text
inventory.manage
```

jika action membutuhkan kontrol berbeda.

---

# 10. Least Privilege

User hanya mendapatkan permission yang diperlukan.

Contoh:

```text
Warehouse Staff
```

boleh:

```text
View Stock
Stock In
Stock Out
```

tetapi tidak otomatis boleh:

```text
Delete Item
Manage User
Manage Permission
Approve Purchase
```

---

# 11. Deny by Default

Default authorization:

```text
NO PERMISSION
      ↓
     DENY
```

Permission harus diberikan secara eksplisit.

Contoh:

```text
User
 ↓
Request /api/v1/items
 ↓
No inventory.view
 ↓
403 Forbidden
```

---

# 12. Scope Architecture

Permission saja belum cukup.

Inventra menggunakan:

```text
Permission
+
Scope
```

Contoh:

```text
stock.out
```

berarti user memiliki kemampuan melakukan Stock Out.

Tetapi scope menentukan **di mana / terhadap data apa** kemampuan tersebut berlaku.

---

# 13. Department Scope

Department digunakan untuk membatasi resource berdasarkan department.

Contoh:

```text
IT
 ↓
Allowed Items
├── Laptop
├── Mouse
└── Keyboard
```

QC:

```text
QC
 ↓
Allowed Items
├── Testing Equipment
└── QC Consumables
```

User tidak otomatis dapat membuat PR terhadap semua item.

---

# 14. Warehouse Scope

Warehouse scope membatasi akses terhadap warehouse tertentu.

Contoh:

```text
User A
 ↓
Warehouse Scope
 ↓
WH-001
```

User tidak boleh mengakses:

```text
WH-002
```

jika tidak memiliki scope tersebut.

---

# 15. Location Scope

Jika diperlukan, akses dapat dibatasi sampai location.

```text
User
 ↓
WH-001
 ↓
RACK-A
 ↓
A-01
```

Authorization harus memeriksa hierarchy tersebut.

---

# 16. Scope Hierarchy

```text
Company
   ↓
Department
   ↓
Warehouse
   ↓
Location
   ↓
Item / Resource
```

Tidak semua module membutuhkan seluruh hierarchy.

Scope diterapkan sesuai kebutuhan business.

---

# 17. Policy Architecture

Laravel Policy digunakan untuk authorization terhadap resource.

Contoh:

```text
StockOutPolicy
PurchaseRequestPolicy
ItemPolicy
WarehousePolicy
AssetPolicy
StockOpnamePolicy
```

Flow:

```text
Request
 ↓
Authenticated User
 ↓
Permission
 ↓
Policy
 ↓
Scope Check
 ↓
Allow / Deny
```

---

# 18. Authorization Must Be Server-Side

Frontend boleh menyembunyikan button:

```text
if user.can('stock.out')
    show button
```

Tetapi ini hanya UX.

Backend tetap harus melakukan:

```text
Permission Check
+
Policy Check
+
Scope Check
```

Contoh:

```text
User manipulates HTTP request
 ↓
POST /stock-out
 ↓
Backend authorization
 ↓
DENY
```

---

# 19. IDOR Protection

Inventra harus mencegah user mengakses resource hanya dengan mengganti ID.

Contoh serangan:

```text
/stock-out/100
```

diubah menjadi:

```text
/stock-out/101
```

Jika transaction `101` bukan milik scope user:

```text
403 / 404
```

bukan menampilkan data.

Authorization dilakukan berdasarkan **resource**, bukan hanya endpoint.

---

# 20. Mass Assignment Protection

Input client tidak boleh bebas menentukan field sensitif.

Contoh field yang tidak boleh dipercaya langsung:

```text
created_by
approved_by
approved_at
status
stock_balance
audit_user_id
```

Field tersebut harus ditentukan backend berdasarkan business logic.

---

# 21. Request Validation

Semua input user divalidasi di server.

Contoh Stock Out:

```text
item_id
warehouse_id
location_id
quantity
unit
reason
```

Validation mencakup:

```text
Required
Type
Format
Range
Existence
Relationship
Business Constraint
```

---

# 22. Business Validation

Validation database belum tentu cukup.

Contoh:

```text
quantity = 500
```

secara datatype valid.

Tetapi:

```text
Available Stock = 100
Requested = 500
```

tidak valid secara business.

Business validation dilakukan oleh service/domain logic.

---

# 23. Inventory Security

Inventory operation merupakan critical operation.

Stock Out:

```text
Authentication
 ↓
Permission
 ↓
Scope
 ↓
Item Validation
 ↓
Warehouse Validation
 ↓
Stock Availability
 ↓
Approval
 ↓
Transaction
```

Semua harus lolos sebelum stock berubah.

---

# 24. Negative Stock Protection

Default policy:

```text
Available Stock
>=
Requested Quantity
```

Jika:

```text
Available = 100
Request = 150
```

maka:

```text
REJECT
```

Tidak boleh:

```text
Balance = -50
```

kecuali business rule secara eksplisit mengizinkan negative stock.

---

# 25. Concurrency Protection

Dua user dapat melakukan Stock Out terhadap stock yang sama pada waktu bersamaan.

Contoh:

```text
Stock = 100

User A → request 80
User B → request 50
```

Jika diproses tanpa concurrency control:

```text
A sees 100
B sees 100

A succeeds
B succeeds

Final = -30
```

Inventra harus menggunakan database transaction dan mekanisme concurrency control yang sesuai untuk critical inventory update.

---

# 26. Database Security

Database access dilakukan melalui application layer.

User tidak mendapatkan direct database access dari browser.

```text
Browser
   X
   │
   └── NO DIRECT DATABASE ACCESS

Browser
 ↓
Laravel
 ↓
PostgreSQL
```

---

# 27. Database Credentials

Database credentials disimpan melalui environment configuration.

Contoh:

```text
DB_HOST
DB_PORT
DB_DATABASE
DB_USERNAME
DB_PASSWORD
```

Credential tidak boleh:

```text
Hardcoded
Committed to Git
Included in frontend
Returned by API
```

---

# 28. SQL Injection Protection

Database query menggunakan Laravel query builder/Eloquent dengan parameter binding.

Hindari raw SQL yang menggabungkan input user secara langsung.

Tidak boleh:

```text
SELECT * FROM items WHERE name = 'USER_INPUT'
```

dengan string concatenation.

Gunakan parameterized query.

---

# 29. XSS Protection

User-generated content harus diperlakukan sebagai untrusted input.

Contoh:

```text
Item Name
Description
Notes
Reason
Comments
```

Frontend tidak boleh melakukan raw HTML rendering tanpa kebutuhan dan sanitization yang sesuai.

---

# 30. CSRF Protection

Web form yang menggunakan session authentication harus dilindungi CSRF.

Flow:

```text
Browser
 ↓
CSRF Token
 ↓
Laravel
 ↓
Verify
 ↓
Process Request
```

Request tanpa token valid ditolak.

---

# 31. API Security

REST API menggunakan:

```text
Authentication
+
Authorization
+
Scope
+
Validation
+
Rate Limiting
```

API versioning:

```text
/api/v1/...
```

memudahkan perubahan API di masa depan.

---

# 32. Rate Limiting

Rate limit diterapkan terutama pada endpoint sensitif.

Contoh:

```text
Login
Password-related endpoint
API
Expensive report
Export
```

Tujuan:

```text
Brute Force Protection
Abuse Prevention
Resource Protection
```

---

# 33. API Response Security

API hanya mengembalikan field yang diperlukan.

Hindari mengembalikan:

```text
password
password_hash
internal secrets
private credentials
unnecessary internal metadata
```

Gunakan API Resource/Transformer untuk mengontrol response.

---

# 34. File Upload Security

Jika Inventra menerima file:

```text
Invoice
Purchase Document
Asset Document
Evidence
```

maka server harus memvalidasi:

```text
File Type
MIME Type
Extension
File Size
Filename
Storage Location
```

File tidak boleh dieksekusi sebagai application code.

---

# 35. Audit Log Security

Audit log digunakan untuk accountability.

Contoh:

```text
User
Action
Resource
Reference
Timestamp
IP
```

Untuk critical operation:

```text
Stock Out
Stock Adjustment
Approval
Asset Disposal
Permission Change
```

audit wajib tersedia.

---

# 36. Audit Log Integrity

User biasa tidak boleh:

```text
Edit Audit
Delete Audit
```

Jika koreksi diperlukan:

```text
New Audit Event
```

bukan mengubah history lama.

---

# 37. Sensitive Data Logging

Jangan memasukkan secret ke log.

Tidak boleh:

```text
Password
API Secret
Access Token
Database Password
Session Secret
```

Jika request logging digunakan, sensitive fields harus di-redact.

---

# 38. Error Handling Security

Production error response tidak boleh membocorkan:

```text
SQL Query
Stack Trace
File Path
Database Credentials
Internal Service Details
```

User cukup mendapatkan informasi yang diperlukan.

Contoh:

```text
500
Something went wrong.
```

Detail teknis masuk application log.

---

# 39. Security Headers

Production application harus menggunakan security headers yang sesuai.

Contoh:

```text
Content-Security-Policy
X-Content-Type-Options
Referrer-Policy
Frame protection
HSTS
```

Konfigurasi disesuaikan dengan deployment environment.

---

# 40. HTTPS

Production wajib menggunakan HTTPS.

```text
HTTP
 ↓
Redirect
 ↓
HTTPS
```

HTTPS melindungi:

```text
Credentials
Session
Transaction Data
API Data
```

dari interception saat transit.

---

# 41. Environment Security

Environment:

```text
Local
Testing
Staging
Production
```

harus dipisahkan.

Production credentials tidak digunakan di local/testing.

File environment sensitif tidak boleh masuk repository.

---

# 42. Docker Security

Docker digunakan untuk environment consistency, bukan sebagai satu-satunya security layer.

Container harus:

```text
Minimal
Least Privilege
No unnecessary exposed ports
Secrets managed securely
```

Database port tidak perlu diekspos ke public internet.

Contoh:

```text
Internet
   X
   │
PostgreSQL
```

Database hanya dapat diakses oleh application network.

---

# 43. Dependency Security

Dependency Laravel/PHP/Node harus diperbarui secara berkala.

Security process:

```text
Dependency
 ↓
Version Check
 ↓
Security Advisory
 ↓
Update
 ↓
Test
 ↓
Deploy
```

Dependency yang tidak diperlukan tidak perlu dipasang.

---

# 44. Authorization Testing

Security test wajib menguji bukan hanya happy path.

Contoh:

```text
User tanpa permission
→ DENY

User dengan permission
→ ALLOW

User dengan permission tetapi salah warehouse
→ DENY

User dengan permission tetapi salah department
→ DENY

User mencoba mengganti ID resource
→ DENY
```

---

# 45. Inventory Security Testing

Test scenario:

```text
Stock = 100

User A:
Request 50
→ Success

User B:
Request 100
→ Depends on remaining stock

User C:
Request 200
→ Reject
```

Concurrency test juga harus dilakukan untuk critical stock operation.

---

# 46. Security Flow — Stock Out

```text
                  STOCK OUT
                      │
                      ▼
               Authentication
                      │
                      ▼
                  Permission
                      │
                      ▼
                    Scope
                      │
                      ▼
                 Validation
                      │
                      ▼
                Policy Check
                      │
                      ▼
              Stock Availability
                      │
                      ▼
                  Approval
                      │
                      ▼
             Database Transaction
                      │
          ┌───────────┼───────────┐
          ▼           ▼           ▼
      Transaction   Ledger      Balance
          │           │           │
          └───────────┼───────────┘
                      ▼
                  Audit Log
                      │
                      ▼
                    COMMIT
```

---

# 47. Security Flow — Purchase Request

```text
User
 ↓
Authentication
 ↓
purchase.request.create
 ↓
Department Scope
 ↓
Item Scope
 ↓
Validation
 ↓
Create PR
 ↓
Approval Workflow
 ↓
Audit Log
```

Contoh:

```text
Department IT
```

tidak otomatis dapat membuat PR untuk item yang hanya diperuntukkan bagi:

```text
Department QC
```

---

# 48. Security Flow — API

```text
API Client
    ↓
HTTPS
    ↓
Authentication
    ↓
Rate Limit
    ↓
Permission
    ↓
Scope
    ↓
Validation
    ↓
Policy
    ↓
Service
    ↓
Database
    ↓
Filtered Response
```

---

# 49. Security Checklist

Sebelum feature dianggap selesai:

```text
[ ] Authentication checked
[ ] Permission checked
[ ] Scope checked
[ ] Policy checked
[ ] Server-side validation
[ ] Business validation
[ ] CSRF protection where applicable
[ ] SQL injection protection
[ ] XSS protection
[ ] Mass assignment protection
[ ] Sensitive fields protected
[ ] Audit log implemented
[ ] Error response reviewed
[ ] API authorization reviewed
[ ] Concurrency considered
[ ] Tests added
```

---

# 50. Security Responsibility

Security bukan responsibility satu layer.

```text
Vue
 ↓
UX Security

Laravel
 ↓
Primary Security Enforcement

Database
 ↓
Data Integrity

Docker / Server
 ↓
Infrastructure Security

Audit
 ↓
Accountability
```

Frontend tidak pernah dianggap sebagai security boundary.

---

# 51. Core Security Principle

Arsitektur keamanan Inventra diringkas menjadi:

```text
WHO?
 ↓
Authentication

CAN DO WHAT?
 ↓
Permission

WHERE?
 ↓
Scope

IS THIS RESOURCE ALLOWED?
 ↓
Policy

IS THE INPUT VALID?
 ↓
Validation

IS THE BUSINESS OPERATION VALID?
 ↓
Service / Business Logic

CAN THE DATA CHANGE SAFELY?
 ↓
Database Transaction

WHO DID IT?
 ↓
Audit Log
```

Dengan architecture ini, perubahan request dari browser, manipulasi ID, pemanggilan API secara langsung, atau bypass UI **tidak boleh dapat melewati authorization backend**.

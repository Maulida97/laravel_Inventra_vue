# Inventra

## Sprint 17 — Security Hardening

**Sprint:** SPRINT-17
**Name:** Security Hardening
**Project:** Inventra
**Status:** Planned
**Branch:** `feature/security-hardening`

---

# 1. Sprint Overview

Security Hardening memastikan seluruh aplikasi Inventra memiliki baseline keamanan yang konsisten.

Fokus:

```text
Authentication
Authorization
Input Validation
Database Security
API Security
Session Security
File Security
Frontend Security
Audit Security
Infrastructure Configuration
Security Testing
```

Sprint ini **tidak menambahkan fitur bisnis baru**.

---

# 2. Objective

Target utama:

```text
Prevent
Detect
Limit
Trace
```

terhadap:

```text
Unauthorized Access
Privilege Escalation
IDOR
Injection
Session Abuse
Sensitive Data Exposure
Mass Assignment
File Upload Abuse
API Abuse
Configuration Errors
```

---

# 3. Security Layers

```text
                 USER
                   │
                   ▼
             Authentication
                   │
                   ▼
             Authorization
                   │
                   ▼
              Validation
                   │
                   ▼
            Business Rules
                   │
                   ▼
            Database Access
                   │
                   ▼
              Audit Log
```

Setiap layer memiliki tanggung jawab berbeda.

---

# 4. Security Principle

## Never Trust Client Input

Semua input dari:

```text
Browser
API
URL
Query String
Form
Headers
Uploaded Files
```

dianggap tidak terpercaya sampai divalidasi dan diotorisasi.

---

# 5. Authentication Hardening

Periksa:

```text
[ ] Password hashing
[ ] Login protection
[ ] Session management
[ ] Logout
[ ] Token management
[ ] Password reset
[ ] Authentication rate limit
```

Password tidak boleh disimpan plaintext.

---

# 6. Password Security

Gunakan password hashing Laravel yang sesuai.

Jangan:

```text
MD5
SHA1
Plaintext
Custom Weak Hash
```

Password juga tidak boleh muncul di:

```text
Application Log
Audit Log
API Response
Exception Message
Database Query Log
```

---

# 7. Login Rate Limiting

Protect login endpoint dari brute force.

Flow:

```text
Repeated Failed Login
        ↓
Rate Limit
        ↓
Temporary Block / Throttle
```

Threshold final ditentukan melalui implementation/testing.

---

# 8. Session Security

Periksa:

```text
Session Cookie
Secure
HttpOnly
SameSite
Session Regeneration
Session Expiration
Logout Invalidation
```

Session ID harus diregenerate setelah authentication sesuai security best practice.

---

# 9. CSRF Protection

Untuk web application yang menggunakan cookie/session authentication:

```text
CSRF Protection
```

harus aktif untuk state-changing requests.

Contoh:

```text
POST
PUT
PATCH
DELETE
```

API dengan token authentication memiliki mekanisme threat model berbeda dan tidak boleh asal menonaktifkan protection secara global.

---

# 10. Authorization

Setiap protected action harus melalui:

```text
Authentication
      ↓
Permission
      ↓
Policy / Gate
      ↓
Resource Scope
```

Jangan mengandalkan frontend untuk security.

---

# 11. RBAC Hardening

Periksa:

```text
[ ] Role validation
[ ] Permission validation
[ ] Role assignment authorization
[ ] Permission modification authorization
[ ] Admin protection
```

User biasa tidak boleh menaikkan permission dirinya sendiri.

---

# 12. Privilege Escalation

Test:

```text
Staff
 ↓
attempt Admin endpoint
```

Result:

```text
403 Forbidden
```

Test juga:

```text
Staff
→ change own role
Staff
→ assign admin role
Staff
→ modify permission
```

semuanya harus ditolak.

---

# 13. IDOR Protection

Semua resource berbasis ID harus diperiksa.

Contoh:

```text
/items/123
/assets/123
/transactions/123
/warehouses/123
/audit-logs/123
```

Jangan hanya memeriksa:

```text
Does record exist?
```

Periksa:

```text
Does user have access?
```

---

# 14. Warehouse Scope Security

User Warehouse A:

```text
GET Warehouse A
→ Allowed
```

User Warehouse A:

```text
GET Warehouse B
→ Denied
```

Test pada:

```text
Items
Inventory
Stock
Assets
Transactions
Reports
Exports
```

---

# 15. Mass Assignment

Pastikan user tidak dapat mengirim field sensitif secara langsung.

Contoh malicious request:

```json
{
  "name": "Budi",
  "role_id": 1,
  "is_admin": true
}
```

Jika field tersebut tidak diizinkan:

```text
→ ignored/rejected
```

---

# 16. Input Validation

Semua input harus memiliki validation.

Validasi:

```text
Required
Type
Length
Format
Range
Existence
Uniqueness
Allowed Values
```

---

# 17. SQL Injection

Jangan membuat query dengan string concatenation.

Hindari:

```php
DB::select("... WHERE name = '$name'");
```

Gunakan:

```text
Query Builder
Eloquent
Parameterized Queries
```

---

# 18. Query Safety

Dynamic:

```text
sort
filter
column
direction
```

harus menggunakan whitelist.

Contoh:

```text
Allowed Sort:
created_at
name
sku
```

Bukan menerima nama kolom SQL mentah dari client.

---

# 19. XSS Protection

User-generated data harus di-output dengan aman.

Perhatikan:

```text
Item Name
Description
Notes
Asset Notes
Comments
```

Frontend tidak boleh sembarangan menggunakan raw HTML rendering.

---

# 20. Vue Security

Hindari penggunaan raw HTML rendering kecuali benar-benar diperlukan dan sudah disanitasi.

Contoh yang perlu diawasi:

```text
v-html
```

Data dari database tidak otomatis dianggap aman.

---

# 21. API Security

API harus memiliki:

```text
Authentication
Authorization
Rate Limiting
Validation
Resource Transformation
Error Handling
```

Endpoint sensitif tidak boleh public tanpa alasan.

---

# 22. API Token Security

Token:

```text
Never expose unnecessarily
Never log
Never put in URL
Never store in Audit Log
```

Gunakan storage mechanism yang sesuai dengan threat model client.

---

# 23. API Error Security

Production API tidak boleh membocorkan:

```text
SQL Query
File Path
Stack Trace
Database Credentials
Environment Variables
Internal Secrets
```

Response cukup:

```text
message
errors
request_id
```

jika relevan.

---

# 24. Exception Handling

Production:

```text
User
 ↓
Generic Error Response
```

Developer:

```text
Application Log
 ↓
Detailed Exception
```

Pisahkan informasi yang diberikan kepada user dan informasi internal.

---

# 25. APP_DEBUG

Production:

```text
APP_DEBUG=false
```

Jangan deploy production dengan:

```text
APP_DEBUG=true
```

---

# 26. Environment Security

`.env` tidak boleh masuk Git.

Pastikan:

```text
.env
.env.*
```

yang berisi secret tidak tercommit.

Repository hanya berisi:

```text
.env.example
```

tanpa credential production.

---

# 27. Secrets

Jangan hardcode:

```text
Database Password
API Key
Token
Private Key
Application Secret
Third-party Credential
```

di source code.

Gunakan environment/configuration management.

---

# 28. Database Credentials

Database credential hanya berada pada:

```text
Environment / Secret Management
```

Bukan:

```text
Git
Source Code
Frontend
API Response
```

---

# 29. File Upload Security

Jika Inventra memiliki upload:

```text
Validate MIME Type
Validate Extension
Validate Size
Generate Safe Filename
Store Outside Public Directory
```

Jangan percaya extension dari client.

---

# 30. File Download Security

File private harus melalui:

```text
Authentication
 ↓
Authorization
 ↓
Download
```

Jangan membuat private file dapat diakses langsung hanya karena mengetahui URL.

---

# 31. Path Traversal

Reject path manipulation seperti:

```text
../
..\
```

File path harus berasal dari controlled identifier, bukan raw user input.

---

# 32. Export Security

Export mengikuti:

```text
RBAC
Warehouse Scope
IDOR Protection
Rate Limit
Sensitive Data Protection
```

Export tidak boleh menjadi security bypass.

---

# 33. CSV Injection

Jika export menghasilkan CSV, user-controlled values harus ditangani untuk mencegah spreadsheet formula injection.

Periksa field seperti:

```text
Item Name
Description
Notes
User Input
```

---

# 34. Audit Log Security

Audit Log harus:

```text
Immutable
Authorized
Traceable
Sensitive-data safe
```

User tidak boleh:

```text
Edit Audit
Delete Audit
Change Actor
Change Timestamp
```

---

# 35. Audit of Security Events

Catat event penting:

```text
Login Success
Login Failure
Logout
Password Change
Role Change
Permission Change
Important Data Change
Export
Approval
```

Tidak mencatat credential.

---

# 36. Security Headers

Production web application harus mempertimbangkan:

```text
Content-Security-Policy
X-Content-Type-Options
Referrer-Policy
Frame-Ancestors / Clickjacking Protection
Strict-Transport-Security
```

Header final harus diuji agar tidak merusak Inertia/Vue atau third-party dependency yang memang dibutuhkan.

---

# 37. HTTPS

Production wajib menggunakan HTTPS.

Flow:

```text
Browser
   ↓
HTTPS
   ↓
Reverse Proxy / Web Server
   ↓
Laravel
```

Redirect HTTP → HTTPS jika sesuai deployment.

---

# 38. Cookie Security

Authentication cookies harus memiliki konfigurasi yang sesuai:

```text
Secure
HttpOnly
SameSite
```

terutama pada production.

---

# 39. CORS

API CORS harus restrictive.

Jangan:

```text
Allow-Origin: *
```

secara default untuk authenticated sensitive API.

Hanya izinkan origin yang memang dibutuhkan.

---

# 40. Rate Limiting

Rate limit diterapkan pada endpoint sensitif:

```text
Login
Password Reset
API
Export
Heavy Reports
```

Tujuannya:

```text
Brute Force Protection
Abuse Protection
Resource Protection
```

---

# 41. Database Security

Database user production harus menggunakan least privilege.

Application user tidak perlu memiliki:

```text
Database Superuser
Administrative Privileges
```

jika tidak dibutuhkan.

---

# 42. Database Exposure

Database:

```text
Internet
   X
Database
```

idealnya:

```text
Internet
   ↓
Application
   ↓
Private Database
```

Database tidak diekspos langsung ke public internet tanpa kebutuhan.

---

# 43. Migration Security

Migration harus:

```text
Reviewed
Versioned
Tested
Reversible where practical
```

Jangan melakukan destructive migration production tanpa backup/recovery plan.

---

# 44. Backup

Production database memiliki:

```text
Backup
Recovery Plan
Restore Test
```

Backup bukan bagian dari application feature, tetapi menjadi bagian dari operational security.

---

# 45. Dependency Security

Periksa dependency:

```text
PHP
Laravel
Vue
NPM Packages
Composer Packages
```

Gunakan update/security advisory process.

Jangan sembarang menjalankan dependency lama yang memiliki known vulnerability.

---

# 46. Composer / NPM

Sebelum release:

```text
composer audit
```

dan package security review yang sesuai untuk ecosystem frontend.

Dependency yang vulnerable harus:

```text
Update
Patch
Replace
```

atau diberi documented exception jika memang belum dapat diperbaiki.

---

# 47. Production Configuration

Periksa:

```text
APP_ENV
APP_DEBUG
APP_KEY
Database
Cache
Queue
Mail
Storage
Session
Logging
```

Tidak ada production secret di repository.

---

# 48. Security Logging

Application log dapat mencatat:

```text
Request ID
User ID
Endpoint
Status
Duration
Exception
Security Event
```

Jangan mencatat:

```text
Password
Bearer Token
Session Secret
API Secret
Private Key
```

---

# 49. Request ID

Gunakan request identifier untuk tracing.

```text
Client
 ↓
Request ID
 ↓
Application
 ↓
Log
```

Contoh:

```text
X-Request-ID
```

Tujuannya memudahkan investigation tanpa membocorkan credential.

---

# 50. Security Testing Matrix

| Area           | Test                   |
| -------------- | ---------------------- |
| Authentication | Login abuse            |
| RBAC           | Permission bypass      |
| IDOR           | Resource access        |
| Warehouse      | Cross-warehouse access |
| API            | Token/auth bypass      |
| Input          | Injection              |
| Frontend       | XSS                    |
| Upload         | Malicious file         |
| Export         | Data leakage           |
| Audit          | Tampering              |
| Session        | Session abuse          |
| Rate Limit     | Request abuse          |

---

# 51. Automated Security Tests

Minimal:

```text
[ ] Authentication bypass
[ ] Authorization bypass
[ ] IDOR
[ ] Warehouse scope bypass
[ ] Mass assignment
[ ] Validation bypass
[ ] SQL injection protection
[ ] XSS protection
[ ] Sensitive response
[ ] Rate limit
```

---

# 52. Manual Security Checklist

Sebelum release:

```text
[ ] Admin endpoints tested
[ ] Staff endpoints tested
[ ] Warehouse scope tested
[ ] API tested
[ ] Export tested
[ ] File access tested
[ ] Production configuration reviewed
[ ] Secrets checked
[ ] Dependencies checked
```

---

# 53. Security Review

Gunakan review:

```text
Threat
 ↓
Control
 ↓
Test
 ↓
Result
```

Contoh:

```text
Threat:
IDOR

Control:
Policy + Scope

Test:
User A requests User B resource

Result:
403
```

---

# 54. Security Documentation

Dokumentasikan:

```text
Authentication Model
Authorization Model
RBAC
Warehouse Scope
API Security
Audit Log
File Security
Deployment Security
Secrets
Security Testing
```

Dokumen utama:

```text
docs/architecture/SECURITY_ARCHITECTURE.md
```

Sprint ini melakukan hardening berdasarkan architecture tersebut.

---

# 55. Code Documentation

Semua file mengikuti:

```text
docs/code-guide/00_CODE_DOCUMENTATION_STANDARD.md
```

Contoh:

```php
/**
 * Security Middleware
 *
 * Purpose:
 * Apply application-wide security controls.
 *
 * Responsibility:
 * - Security headers
 * - Request security context
 * - Request tracing
 *
 * Important:
 * Authentication and authorization must remain
 * separate from generic security headers.
 */
```

---

# 56. Maintenance Guide

### "User bisa membuka data warehouse lain."

Trace:

```text
Route
 ↓
Controller
 ↓
Policy
 ↓
Warehouse Scope
 ↓
Query
```

---

### "User bisa mengubah role sendiri."

Trace:

```text
Request
 ↓
Validation
 ↓
Authorization
 ↓
Role Service
 ↓
Audit Log
```

---

### "API mengembalikan informasi sensitif."

Trace:

```text
Controller
 ↓
API Resource
 ↓
Response
```

Periksa juga:

```text
Exception Handler
```

---

### "Production menampilkan error detail."

Periksa:

```text
APP_ENV
APP_DEBUG
Exception Handler
```

---

### "Security test gagal."

Jangan langsung menonaktifkan protection.

Trace:

```text
Threat
 ↓
Expected Control
 ↓
Implementation
 ↓
Test
```

---

# 57. Code Understanding Map

```text
Request
   ↓
Middleware
   ↓
Authentication
   ↓
Authorization
   ↓
Validation
   ↓
Policy / Scope
   ↓
Business Service
   ↓
Database
   ↓
Audit
   ↓
Response
```

Untuk memahami security sebuah endpoint:

```text
1. Cari route
2. Cari middleware
3. Cari authentication
4. Cari permission
5. Cari policy
6. Cari validation
7. Cari warehouse scope
8. Cari business service
9. Cari audit
10. Cari response
```

---

# 58. Expected Files

```text
app/
├── Http/
│   ├── Middleware/
│   │   ├── SecurityHeaders.php
│   │   └── RequestId.php
│   │
│   └── Exceptions/
│
├── Policies/
├── Rules/
└── Services/
    └── Security/

config/
├── auth.php
├── cors.php
├── session.php
└── ...

tests/
└── Security/
    ├── AuthenticationTest.php
    ├── AuthorizationTest.php
    ├── IDORTest.php
    ├── WarehouseScopeTest.php
    ├── MassAssignmentTest.php
    ├── ApiSecurityTest.php
    └── FileSecurityTest.php
```

File final mengikuti kebutuhan actual implementation.

---

# 59. Git Branch

```text
feature/security-hardening
```

Dependency:

```text
SPRINT-01 → Authentication
SPRINT-02 → RBAC
SPRINT-03+ → Business Modules
SPRINT-14 → Audit Log
SPRINT-15 → REST API
SPRINT-16 → Export
SPRINT-17 → Security Hardening
```

---

# 60. Suggested Commits

```text
security(auth): harden authentication
security(auth): add login rate limiting
security(session): harden session configuration
security(rbac): harden authorization checks
security(scope): enforce warehouse scope
security(api): harden api authentication
security(api): harden api authorization
security(api): add api rate limiting
security(input): harden request validation
security(db): harden query handling
security(frontend): review xss protection
security(files): harden file access
security(export): harden export authorization
security(audit): enforce audit immutability
security(headers): add security headers
security(cors): harden cors configuration
security(errors): sanitize production errors
security(config): harden production configuration
security(dependencies): update vulnerable dependencies
test(security): add authentication tests
test(security): add authorization tests
test(security): add idor tests
test(security): add scope tests
test(security): add api security tests
docs(security): update security documentation
```

---

# 61. Acceptance Criteria

Sprint selesai apabila:

```text
1. Authentication telah di-hardening.

2. Login rate limiting tersedia.

3. Session security telah direview.

4. CSRF protection telah diverifikasi.

5. RBAC bypass telah diuji.

6. Privilege escalation telah diuji.

7. IDOR telah diuji.

8. Warehouse scope telah diuji.

9. Mass assignment telah diuji.

10. Input validation telah direview.

11. SQL injection protection telah diuji.

12. XSS protection telah direview.

13. API security telah direview.

14. API rate limiting tersedia.

15. Token tidak masuk log.

16. Sensitive response telah diperiksa.

17. File upload security telah direview jika upload tersedia.

18. File download authorization tersedia.

19. Export security telah diuji.

20. CSV injection telah ditangani.

21. Audit Log tidak dapat dimanipulasi melalui normal application flow.

22. Security headers telah dikonfigurasi.

23. HTTPS production requirement telah ditetapkan.

24. Cookie security telah direview.

25. CORS telah dikonfigurasi secara restrictive.

26. Production APP_DEBUG=false.

27. Secrets tidak berada di repository.

28. Database privilege mengikuti least privilege.

29. Dependency security telah diperiksa.

30. Security logging tidak membocorkan credential.

31. Request ID tersedia untuk tracing jika digunakan.

32. Automated security tests berhasil.

33. Manual security checklist selesai.

34. SECURITY_ARCHITECTURE.md tetap sinkron dengan implementation.

35. Code documentation mengikuti standard Inventra.
```

---

# 62. Definition of Done

```text
Authentication
    ✓ Hardened
    ✓ Rate Limited
    ✓ Session Reviewed

Authorization
    ✓ RBAC
    ✓ Policy
    ✓ Warehouse Scope
    ✓ IDOR Protection

Application
    ✓ Validation
    ✓ SQL Injection Protection
    ✓ XSS Review
    ✓ Mass Assignment Protection

API
    ✓ Authentication
    ✓ Authorization
    ✓ Rate Limit
    ✓ Safe Error Response

Files
    ✓ Upload Security
    ✓ Download Authorization

Export
    ✓ Scope
    ✓ Authorization
    ✓ CSV Injection Protection

Infrastructure
    ✓ HTTPS
    ✓ Secure Cookies
    ✓ CORS
    ✓ APP_DEBUG=false
    ✓ Secrets Protected

Audit
    ✓ Immutable
    ✓ Security Events

Testing
    ✓ Automated
    ✓ Manual
    ✓ Security Review

Documentation
    ✓ Architecture
    ✓ Code Guide
    ✓ Maintenance Guide

Git
    ✓ feature/security-hardening
```

---

# 63. Final Security Model

```text
                    INTERNET
                       │
                      HTTPS
                       │
                       ▼
                Security Headers
                       │
                       ▼
                 Rate Limiting
                       │
                       ▼
                Authentication
                       │
                       ▼
                  RBAC / Policy
                       │
                       ▼
                Warehouse Scope
                       │
                       ▼
                  Validation
                       │
                       ▼
                Business Service
                       │
                       ▼
                 Database Layer
                       │
              ┌────────┴────────┐
              ▼                 ▼
          Audit Log          Application Log
```

Prinsip akhirnya:

```text
Never trust the client.
Never bypass authorization.
Never expose sensitive data.
Never store secrets in source code.
Never allow audit tampering.
Always validate scope on the server.
```

Security Hardening selesai ketika **fitur yang sudah dibuat tidak hanya "bisa digunakan", tetapi juga sudah diuji dari sisi "bagaimana kalau user mencoba menyalahgunakannya?"**

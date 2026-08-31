# Inventra

## Sprint 01 — Authentication

**Sprint:** SPRINT-01
**Name:** Authentication
**Project:** Inventra
**Status:** Planned
**Branch:** `feature/authentication`

---

# 1. Sprint Overview

Sprint ini membangun sistem authentication sebagai pintu masuk utama aplikasi Inventra.

Authentication bertanggung jawab memastikan:

```text
"Siapa user yang sedang menggunakan aplikasi?"
```

Flow utama:

```text
User
 ↓
Login
 ↓
Authentication
 ↓
Session
 ↓
Authenticated User
 ↓
Inventra
```

---

# 2. Objective

Membangun authentication yang:

- Aman.
- Menggunakan Laravel authentication.
- Menggunakan session-based authentication.
- Terintegrasi dengan Inertia.js + Vue.
- Memiliki login dan logout.
- Memiliki password management dasar.
- Melindungi halaman authenticated.
- Mencatat aktivitas authentication penting ke Audit Log.

---

# 3. Scope

### Included

```text
Login
Logout
Session
Authenticated User
Password Hashing
Remember Me
Authentication Middleware
Login Validation
Failed Login Handling
Rate Limiting
Authentication Audit
```

### Optional / Later

```text
Email Verification
Forgot Password via Email
2FA
SSO
OAuth
API Token Authentication
```

Fitur tambahan tidak dimasukkan ke sprint ini kecuali memang dibutuhkan oleh requirement final.

---

# 4. User Stories

### Login

> Sebagai user, saya ingin login menggunakan credential saya agar dapat mengakses Inventra.

### Logout

> Sebagai user, saya ingin logout agar session saya berakhir dengan aman.

### Protected Page

> Sebagai sistem, halaman internal Inventra hanya dapat diakses oleh user yang sudah login.

### Invalid Login

> Sebagai user, saya mendapatkan pesan yang sesuai ketika credential salah.

### Session

> Sebagai user, session saya tetap aktif selama session masih valid.

---

# 5. Authentication Flow

```text
                    USER
                     │
                     ▼
                  Login Page
                     │
                     ▼
               Login Request
                     │
                     ▼
                 Validation
                     │
                     ▼
              Authentication
                ┌────┴────┐
                │         │
              FAIL       SUCCESS
                │         │
                ▼         ▼
          Error Message  Session
                          │
                          ▼
                       Dashboard
```

---

# 6. Logout Flow

```text
User
 ↓
Click Logout
 ↓
POST /logout
 ↓
Logout
 ↓
Invalidate Session
 ↓
Regenerate CSRF Token
 ↓
Login Page
```

---

# 7. Technical Stack

Authentication menggunakan:

```text
Backend
→ Laravel

Frontend
→ Vue.js

SPA Bridge
→ Inertia.js

Database
→ PostgreSQL

Authentication
→ Laravel Session Authentication

Password
→ Laravel Hashing

Security
→ CSRF Protection
→ Rate Limiting
→ Session Security
```

---

# 8. Authentication Pages

Minimal:

```text
resources/js/Pages/Auth/
├── Login.vue
```

Jika password reset diimplementasikan:

```text
resources/js/Pages/Auth/
├── Login.vue
├── ForgotPassword.vue
└── ResetPassword.vue
```

Untuk V1, `Login.vue` merupakan halaman wajib.

---

# 9. Backend Structure

Concept:

```text
app/
├── Http/
│   ├── Controllers/
│   │   └── Auth/
│   │       └── AuthenticatedSessionController.php
│   │
│   └── Requests/
│       └── Auth/
│           └── LoginRequest.php
│
└── Services/
    └── Auth/
```

Struktur dapat disesuaikan dengan Laravel starter/authentication package yang digunakan.

Jangan membuat abstraction tambahan jika belum diperlukan.

---

# 10. Routes

Minimal:

```text
GET  /login
POST /login
POST /logout
```

Concept:

```php
Route::get('/login', ...)
    ->name('login');

Route::post('/login', ...)
    ->name('login');

Route::post('/logout', ...)
    ->middleware('auth')
    ->name('logout');
```

Halaman authenticated menggunakan middleware:

```text
auth
```

---

# 11. Login Request

Login menerima:

```text
email
password
remember
```

Contoh:

```text
email:
user@inventra.test

password:
********

remember:
true
```

Input harus divalidasi di backend.

---

# 12. Validation

Minimal:

```text
email
→ required
→ valid email format

password
→ required
→ string
```

Validasi frontend boleh digunakan untuk UX, tetapi backend tetap menjadi source of truth.

---

# 13. Authentication Logic

Concept:

```text
Request
 ↓
Validate
 ↓
Find User
 ↓
Check Password
 ↓
Check Account Status
 ↓
Authenticate
 ↓
Regenerate Session
 ↓
Redirect
```

---

# 14. Session Regeneration

Setelah login berhasil:

```text
Session ID
 ↓
Regenerate
```

Tujuannya membantu mencegah session fixation.

Authentication tidak boleh hanya:

```text
check password
→ redirect
```

Session handling harus mengikuti mekanisme keamanan Laravel.

---

# 15. Password Hashing

Password tidak boleh disimpan plaintext.

Database:

```text
password
→ hashed password
```

Jangan:

```text
password = "123456"
```

Password harus diproses menggunakan Laravel hashing mechanism.

---

# 16. Password Verification

Jangan membandingkan:

```php
$user->password === $request->password
```

Gunakan mekanisme password verification Laravel.

Concept:

```text
Plain Password
       ↓
Password Hash Verification
       ↓
True / False
```

---

# 17. Remember Me

Jika user memilih:

```text
Remember Me
```

authentication dapat menggunakan fitur remember Laravel.

Namun:

```text
remember = true
```

harus berasal dari input yang tervalidasi.

---

# 18. Authentication Middleware

Semua halaman internal harus menggunakan:

```text
auth
```

Concept:

```text
GET /dashboard
       │
       ▼
    auth middleware
       │
   ┌───┴───┐
   ▼       ▼
Guest    Authenticated
   │       │
Login    Dashboard
```

---

# 19. Guest Middleware

Login page sebaiknya hanya digunakan oleh guest.

Jika user sudah login:

```text
/login
 ↓
redirect
 ↓
dashboard
```

---

# 20. CSRF Protection

Semua state-changing request melalui web session harus dilindungi CSRF.

Contoh:

```text
POST /login
POST /logout
```

Jangan menonaktifkan CSRF hanya agar request bekerja.

---

# 21. Rate Limiting

Login harus memiliki protection terhadap brute-force attempts.

Concept:

```text
User
 ↓
Repeated Login Attempts
 ↓
Rate Limiter
 ↓
Temporary Throttle
```

Rate limit mempertimbangkan kombinasi yang sesuai, misalnya:

```text
IP
+
email/username
```

Detail final mengikuti implementasi Laravel.

---

# 22. Failed Login

Jika credential salah:

```text
Login Failed
 ↓
No Session Created
 ↓
Error Message
 ↓
User Remains Login Page
```

Pesan error jangan memberikan informasi berlebihan.

Hindari:

```text
"Email benar tetapi password salah."
```

Lebih aman:

```text
"Email atau password tidak valid."
```

---

# 23. Account Status

Authentication harus mempertimbangkan status account.

Contoh:

```text
ACTIVE
INACTIVE
SUSPENDED
```

Concept:

```text
Correct Password
       ↓
Account Status
       ↓
ACTIVE → Login
INACTIVE → Reject
SUSPENDED → Reject
```

Detail status mengikuti desain User/RBAC Inventra.

---

# 24. Authentication Audit

Aktivitas authentication penting dicatat.

Minimal:

```text
LOGIN_SUCCESS
LOGIN_FAILED
LOGOUT
```

Jika password management ditambahkan:

```text
PASSWORD_CHANGED
PASSWORD_RESET
```

---

# 25. Audit Flow

Successful login:

```text
Login Request
 ↓
Authentication Success
 ↓
Session Created
 ↓
Audit Log
 ↓
Dashboard
```

Failed login:

```text
Login Request
 ↓
Authentication Failed
 ↓
Audit Log
 ↓
Error Response
```

Audit log tidak boleh menyimpan:

```text
password
password hash
session token
remember token
```

---

# 26. Login Audit Data

Contoh:

```text
Action:
LOGIN_SUCCESS

Module:
AUTHENTICATION

User:
USR-0001

IP:
...

User Agent:
...

Created At:
...
```

Untuk failed login:

```text
Action:
LOGIN_FAILED

User:
nullable

Identifier:
email / username sesuai policy
```

Hindari menyimpan data sensitif secara berlebihan.

---

# 27. Frontend Login Flow

```text
Login.vue
 ↓
Form
 ↓
POST /login
 ↓
Laravel
 ↓
Success
 ↓
Redirect Dashboard
```

Jika gagal:

```text
Laravel
 ↓
Validation / Authentication Error
 ↓
Inertia Response
 ↓
Login.vue
 ↓
Display Error
```

---

# 28. Login UI

Minimal:

```text
Email
Password
Remember Me
Login Button
Validation Error
Authentication Error
```

UI tidak perlu terlalu kompleks pada sprint ini.

---

# 29. Frontend Responsibility

Vue bertanggung jawab:

```text
Form
Input
Loading State
Error Display
UX
```

Vue tidak bertanggung jawab:

```text
Password verification
Authorization
Session creation
Permission checking
```

Security logic berada di backend.

---

# 30. Authentication State

Inertia dapat menerima informasi user yang sedang authenticated melalui shared props.

Concept:

```text
Backend
 ↓
Authenticated User
 ↓
Inertia Shared Data
 ↓
Vue
```

Contoh data:

```text
user:
{
    id,
    name,
    email
}
```

Jangan mengirim seluruh record user jika tidak diperlukan.

---

# 31. Shared Authentication Data

Data global yang mungkin dibutuhkan:

```text
auth.user
```

Kemudian halaman Vue dapat mengetahui:

```text
$user is authenticated
```

Untuk permission/RBAC detail akan dibahas pada:

```text
SPRINT-02-RBAC
```

---

# 32. Security Boundary

Penting:

```text
Vue
```

bukan security boundary.

Contoh buruk:

```javascript
if (user.isAdmin) {
  showAdminButton();
}
```

Ini hanya UI.

Backend tetap harus melakukan authorization.

Authentication menjawab:

```text
WHO ARE YOU?
```

RBAC menjawab:

```text
WHAT CAN YOU DO?
```

---

# 33. Error Handling

Authentication harus menangani:

```text
Invalid Credentials
Validation Error
Inactive Account
Rate Limit
Unexpected Error
```

Jangan menampilkan stack trace kepada user.

---

# 34. Session Security

Perhatikan:

```text
Session Driver
Session Lifetime
Secure Cookie
HttpOnly Cookie
SameSite
HTTPS
```

Production configuration harus menggunakan secure cookie settings.

---

# 35. Environment Configuration

Credential/database/session configuration menggunakan `.env`.

Contoh:

```text
APP_ENV
APP_URL

SESSION_DRIVER
SESSION_LIFETIME

DB_CONNECTION
DB_HOST
DB_PORT
DB_DATABASE
DB_USERNAME
DB_PASSWORD
```

Jangan commit:

```text
.env
```

ke repository.

---

# 36. Docker Consideration

Authentication harus dapat berjalan di environment Docker.

Concept:

```text
Browser
 ↓
Nginx / Web Server
 ↓
Laravel
 ↓
PostgreSQL
```

Session configuration harus tetap bekerja ketika aplikasi dijalankan melalui Docker.

Detail deployment dibahas di:

```text
SPRINT-19-DOCKER-DEPLOYMENT
```

---

# 37. Testing

Minimal feature tests:

```text
[ ] Guest can view login
[ ] Valid user can login
[ ] Invalid credentials rejected
[ ] Inactive user rejected
[ ] Session created after login
[ ] Session regenerated
[ ] Authenticated user can access dashboard
[ ] Guest cannot access dashboard
[ ] Authenticated user can logout
[ ] Session invalidated after logout
[ ] CSRF protection works
[ ] Rate limiting works
[ ] Login success audit created
[ ] Login failure audit created
```

---

# 38. Security Testing

Periksa:

```text
[ ] Password not stored plaintext
[ ] Password not logged
[ ] Session ID regenerated
[ ] CSRF enabled
[ ] Rate limiting enabled
[ ] Auth middleware applied
[ ] Sensitive data not exposed
[ ] .env not committed
[ ] Error messages do not leak information
```

---

# 39. Acceptance Criteria

Sprint dianggap berhasil jika:

```text
1. User dapat membuka login page.

2. User dengan credential valid dapat login.

3. Credential invalid ditolak.

4. User yang tidak aktif tidak dapat login.

5. Session dibuat setelah login.

6. Session ID diregenerasi setelah login.

7. Halaman internal tidak dapat diakses guest.

8. User dapat logout.

9. Session invalid setelah logout.

10. Login memiliki rate limiting.

11. CSRF protection aktif.

12. Authentication event tercatat pada Audit Log.

13. Password tidak pernah disimpan atau dicatat dalam plaintext.

14. Authentication dapat berjalan pada environment Docker.

15. Automated tests berhasil.
```

---

# 40. Expected Files

Conceptual files:

```text
app/
├── Http/
│   ├── Controllers/Auth/
│   │   └── AuthenticatedSessionController.php
│   │
│   └── Requests/Auth/
│       └── LoginRequest.php
│
├── Models/
│   └── User.php
│
└── Services/
    └── Auth/

resources/js/
└── Pages/
    └── Auth/
        └── Login.vue

routes/
└── web.php

tests/
└── Feature/
    └── Auth/
        └── AuthenticationTest.php
```

**Catatan:** nama file dapat berubah mengikuti Laravel starter kit/authentication approach yang dipilih saat implementation.

---

# 41. Code Documentation

Setiap file baru mengikuti:

```text
00_CODE_DOCUMENTATION_STANDARD.md
```

Bagian atas file minimal menjelaskan:

```text
Purpose
Responsibility
Related Files
Main Flow
```

Contoh:

```php
/**
 * Authentication Session Controller
 *
 * Purpose:
 * Handle user login and logout.
 *
 * Main Flow:
 * Request
 * → Validation
 * → Authentication
 * → Session
 * → Redirect
 *
 * Related:
 * - LoginRequest
 * - User
 * - AuditLogger
 */
```

Komentar harus menjelaskan **why**, bukan memenuhi file dengan komentar untuk setiap baris.

---

# 42. Code Understanding Map

Untuk memahami login:

```text
resources/js/Pages/Auth/Login.vue
        ↓
POST /login
        ↓
routes/web.php
        ↓
AuthenticatedSessionController
        ↓
LoginRequest
        ↓
Laravel Authentication
        ↓
User
        ↓
Session
        ↓
AuditLogger
        ↓
Dashboard
```

---

# 43. Maintenance Guide

### "Saya ingin mengubah tampilan login."

Buka:

```text
resources/js/Pages/Auth/Login.vue
```

---

### "Saya ingin mengubah validasi login."

Cari:

```text
app/Http/Requests/Auth/LoginRequest.php
```

---

### "Saya ingin mengubah proses login."

Cari:

```text
app/Http/Controllers/Auth/
```

kemudian ikuti:

```text
Controller
 ↓
Authentication
 ↓
Session
```

---

### "Saya ingin mengubah redirect setelah login."

Cari:

```text
AuthenticatedSessionController
```

atau konfigurasi authentication flow yang digunakan.

---

### "Saya ingin mengubah lama session."

Periksa:

```text
config/session.php
```

dan:

```text
.env
```

---

### "Saya ingin mengubah rate limit."

Cari konfigurasi throttle/rate limiter authentication.

---

### "Saya ingin mengubah audit login."

Cari:

```text
AuditLogger
```

dan integrasi authentication event.

---

### "Login tidak bekerja."

Ikuti:

```text
Login.vue
 ↓
Network Request
 ↓
Route
 ↓
Controller
 ↓
Validation
 ↓
Authentication
 ↓
User
 ↓
Session
```

Jangan langsung mengubah banyak file.

Cari titik pertama di mana flow berhenti.

---

# 44. Debugging Flow

### Login button tidak melakukan apa-apa

Periksa:

```text
[ ] Form submit
[ ] Inertia request
[ ] Route
[ ] Browser console
```

### Response 422

Periksa:

```text
[ ] Validation
[ ] Request fields
[ ] Form field names
```

### Response 419

Periksa:

```text
[ ] CSRF
[ ] Session
[ ] Cookie
```

### Response 401 / login gagal

Periksa:

```text
[ ] Email
[ ] Password
[ ] User exists
[ ] Account status
```

### Login berhasil tetapi kembali ke login

Periksa:

```text
[ ] Session driver
[ ] Cookie
[ ] Domain
[ ] HTTPS configuration
[ ] Auth middleware
```

---

# 45. Git Branch

Branch:

```text
feature/authentication
```

Branch ini digunakan untuk seluruh pekerjaan authentication dalam sprint.

Contoh:

```text
main
 │
 └── feature/authentication
```

---

# 46. Suggested Commit Structure

Kamu tetap melakukan commit dan push sendiri.

Commit dapat dipisahkan berdasarkan logical change:

```text
feat(auth): add login flow
feat(auth): add logout flow
feat(auth): protect authenticated routes
feat(auth): add login rate limiting
feat(auth): add authentication audit logs
test(auth): add authentication feature tests
docs(auth): document authentication flow
```

Tidak wajib satu commit per file.

Lebih penting commit mewakili perubahan yang logis.

---

# 47. Sprint Completion Checklist

```text
Authentication
[ ] Login
[ ] Logout
[ ] Session
[ ] Remember Me
[ ] Auth Middleware
[ ] Validation
[ ] Rate Limiting
[ ] CSRF
[ ] Account Status
[ ] Audit Log

Frontend
[ ] Login Page
[ ] Form
[ ] Error State
[ ] Loading State

Backend
[ ] Controller
[ ] Request Validation
[ ] Authentication
[ ] Session Handling

Security
[ ] Password Hashing
[ ] Session Regeneration
[ ] CSRF
[ ] Rate Limiting
[ ] Sensitive Data Protection

Testing
[ ] Feature Tests
[ ] Security Tests

Documentation
[ ] Code Comments
[ ] Code Guide Updated
[ ] Sprint Documentation Updated
```

---

# 48. Definition of Done

Sprint 01 selesai apabila:

```text
Code
    ✓ Authentication implemented

Security
    ✓ Authentication secured

Database
    ✓ User authentication works with PostgreSQL

Frontend
    ✓ Login UI works with Inertia + Vue

Testing
    ✓ Authentication tests pass

Audit
    ✓ Authentication events logged

Documentation
    ✓ Code documented
    ✓ Maintenance path documented

Git
    ✓ Changes organized under:
      feature/authentication
```

---

# 49. Final Authentication Architecture

```text
                         BROWSER
                            │
                            ▼
                       Login.vue
                            │
                            │ POST /login
                            ▼
                         ROUTER
                            │
                            ▼
                    AUTH CONTROLLER
                            │
                            ▼
                     LOGIN REQUEST
                       VALIDATION
                            │
                            ▼
                  LARAVEL AUTHENTICATION
                            │
                     ┌──────┴──────┐
                     ▼             ▼
                  FAILED        SUCCESS
                     │             │
                     ▼             ▼
                AUDIT LOG       SESSION
                     │             │
                     │             ▼
                     │          REDIRECT
                     │             │
                     │             ▼
                     │         DASHBOARD
                     │
                     ▼
                  RESPONSE
```

---

# 50. Key Principle

Authentication menjawab:

```text
WHO ARE YOU?
```

Flow yang harus dipahami:

```text
Login.vue
 ↓
POST /login
 ↓
Route
 ↓
Controller
 ↓
Validation
 ↓
Authentication
 ↓
Session
 ↓
Dashboard
```

Sedangkan:

```text
Authentication
```

berbeda dengan:

```text
Authorization
```

Authentication:

```text
"Apakah kamu user yang valid?"
```

Authorization:

```text
"Apa yang boleh kamu lakukan?"
```

Authorization akan dibangun pada:

```text
SPRINT-02-RBAC
```

**Prinsip utama Sprint 01:**

> **Jangan hanya membuat login yang bekerja. Pastikan kamu memahami alur request → validation → authentication → session → middleware → Inertia → Vue, karena alur ini akan menjadi fondasi hampir seluruh fitur Inventra berikutnya.**

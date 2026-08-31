# Inventra

## Authentication Code Guide

**Document:** Authentication Code Guide
**Version:** V1.0
**Status:** Draft

---

# 1. Purpose

Authentication bertanggung jawab untuk memastikan hanya user yang terdaftar dan terautentikasi yang dapat mengakses fitur internal Inventra.

Authentication menangani:

- Login
- Logout
- Session
- Password
- User identity
- Authentication middleware
- Password reset jika diaktifkan

Authentication **tidak menentukan permission**.

Permission ditangani oleh RBAC.

---

# 2. Authentication Architecture

```text
User
 ↓
Login Page
 ↓
Inertia
 ↓
Laravel Route
 ↓
Authentication Controller
 ↓
Credential Validation
 ↓
Authentication
 ↓
Session
 ↓
Authenticated User
```

Setelah authentication berhasil:

```text
Authenticated User
        ↓
       RBAC
        ↓
   Authorization
```

---

# 3. Technology

Authentication menggunakan:

```text
Backend
├── Laravel
└── Laravel Authentication

Frontend
├── Vue.js
└── Inertia.js

Database
└── PostgreSQL

Session
└── Laravel Session
```

Password disimpan dalam bentuk secure hash.

---

# 4. User Authentication Flow

Login:

```text
User
 ↓
Email / Username
 ↓
Password
 ↓
Submit
 ↓
Login Request
 ↓
Validation
 ↓
Find User
 ↓
Verify Password
 ↓
Regenerate Session
 ↓
Authenticated
 ↓
Dashboard
```

Jika credential salah:

```text
Login Request
 ↓
Credential Verification
 ↓
FAILED
 ↓
Error Response
 ↓
Login Page
```

---

# 5. Authentication Files

Struktur konseptual:

```text
app/
├── Http/
│   ├── Controllers/
│   │   └── Auth/
│   ├── Requests/
│   │   └── Auth/
│   └── Middleware/
│
├── Models/
│   └── User.php
│
└── Services/
    └── Auth/
```

Frontend:

```text
resources/
└── js/
    └── Pages/
        └── Auth/
            ├── Login.vue
            └── ...
```

Routes:

```text
routes/
├── web.php
└── api.php
```

Actual filenames dapat menyesuaikan implementation Laravel yang digunakan.

---

# 6. Login Page

File:

```text
resources/js/Pages/Auth/Login.vue
```

Responsibility:

```text
Display login form
Collect credentials
Display validation errors
Submit login request
```

Login page tidak melakukan password verification.

Contoh flow:

```text
Login.vue
 ↓
email
password
 ↓
Inertia POST
 ↓
Laravel
```

---

# 7. Login Form

Conceptual structure:

```vue
<form @submit.prevent="submit">
    Email
    Password
    Login Button
</form>
```

Frontend hanya mengirim data.

```text
email
password
```

Frontend tidak menentukan:

```text
authenticated = true
```

Status authentication ditentukan server.

---

# 8. Login Route

Conceptual:

```php
Route::get('/login', [AuthenticatedSessionController::class, 'create'])
    ->name('login');

Route::post('/login', [AuthenticatedSessionController::class, 'store']);
```

Flow:

```text
GET /login
 ↓
Show Login Page
```

dan:

```text
POST /login
 ↓
Authenticate User
```

---

# 9. Login Request

Jika menggunakan Form Request:

```text
app/Http/Requests/Auth/
```

Request bertanggung jawab terhadap validation.

Contoh:

```php
public function rules(): array
{
    return [
        'email' => ['required', 'email'],
        'password' => ['required', 'string'],
    ];
}
```

Validation bukan credential verification.

---

# 10. Credential Verification

Setelah validation:

```text
Input
 ↓
Find User
 ↓
Verify Password Hash
 ↓
Success / Failure
```

Password plaintext tidak dibandingkan dengan database secara langsung.

Concept:

```text
User Password
      ↓
Hash Verification
      ↓
Stored Password Hash
```

---

# 11. Password Storage

Database tidak menyimpan:

```text
password = "password123"
```

Database menyimpan secure password hash.

Concept:

```text
Password
 ↓
Hash
 ↓
Database
```

Ketika login:

```text
Entered Password
 ↓
Verify Against Hash
 ↓
Success / Failure
```

Password tidak dapat digunakan kembali dari database sebagai plaintext.

---

# 12. Session Regeneration

Setelah login berhasil:

```text
Old Session
     ↓
Regenerate
     ↓
New Session
     ↓
Authenticated User
```

Tujuannya mengurangi risiko session fixation.

---

# 13. Authenticated Middleware

Protected routes menggunakan authentication middleware.

Concept:

```text
Route
 ↓
auth middleware
 ↓
Authenticated?
```

Jika belum login:

```text
NO
 ↓
Redirect /login
```

Jika sudah:

```text
YES
 ↓
Continue
```

---

# 14. Protected Route

Contoh:

```php
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', ...);

    Route::get('/inventory', ...);

    Route::get('/stock-out', ...);

});
```

Dengan demikian authentication check dilakukan sebelum controller dijalankan.

---

# 15. Authentication vs RBAC

Contoh:

```text
User belum login
 ↓
Authentication
 ↓
DENY
```

User sudah login:

```text
Authenticated
 ↓
RBAC
 ↓
Permission Check
```

Misalnya:

```text
User:
Warehouse Staff

Permission:
inventory.view
```

tetapi tidak:

```text
user.manage
```

Maka:

```text
/inventory
→ ALLOW

/users
→ DENY
```

---

# 16. Current User

Laravel menyediakan authenticated user melalui authentication context.

Concept:

```php
$request->user()
```

atau:

```php
auth()->user()
```

Gunakan authenticated identity dari backend.

Jangan menerima:

```text
user_id
```

dari frontend sebagai sumber kebenaran untuk menentukan siapa yang melakukan action.

---

# 17. Created By

Untuk transaction:

```text
created_by
```

harus berasal dari authenticated user.

Contoh:

```php
$transaction->created_by = $request->user()->id;
```

Bukan:

```php
$transaction->created_by = $request->input('created_by');
```

Karena client dapat memanipulasi request.

---

# 18. Logout Flow

```text
User
 ↓
Logout
 ↓
Laravel
 ↓
Session Invalidation
 ↓
Session Regeneration
 ↓
Login Page
```

Logout harus mengakhiri authenticated session.

---

# 19. Logout Route

Conceptual:

```php
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');
```

Logout menggunakan POST karena merupakan state-changing operation.

---

# 20. Session Security

Session configuration harus memperhatikan:

```text
Secure Cookie
HttpOnly
SameSite
Session Regeneration
Session Expiration
```

Production harus menggunakan HTTPS.

---

# 21. Password Reset

Jika fitur password reset digunakan:

```text
Request Reset
 ↓
Verify Account / Token Flow
 ↓
Reset Password
 ↓
Hash New Password
 ↓
Invalidate Existing Sessions if appropriate
```

Password reset token tidak boleh diekspos secara tidak aman.

---

# 22. Failed Login

Jika login gagal:

```text
Credential Verification
 ↓
FAILED
 ↓
Generic Error
```

Jangan memberikan informasi berlebihan seperti:

```text
"Email benar tetapi password salah."
```

yang dapat membantu account enumeration.

Gunakan pesan yang tidak membocorkan apakah account tertentu terdaftar jika flow tersebut memerlukan proteksi enumeration.

---

# 23. Login Rate Limiting

Login endpoint harus memiliki rate limiting.

Concept:

```text
Repeated Login Attempts
        ↓
Rate Limiter
        ↓
Temporary Restriction
```

Tujuan:

```text
Brute Force Protection
```

---

# 24. Authentication Error Flow

```text
Invalid Request
      ↓
Validation Error
```

atau:

```text
Valid Request
      ↓
Invalid Credential
      ↓
Authentication Error
```

atau:

```text
Authenticated
      ↓
No Permission
      ↓
403
```

Ketiganya berbeda.

---

# 25. Authentication Error vs Authorization Error

```text
401
=
Not authenticated
```

```text
403
=
Authenticated but not authorized
```

Contoh:

```text
Belum login
→ 401 / redirect login
```

Sudah login tetapi tidak punya permission:

```text
→ 403 Forbidden
```

---

# 26. Authentication Data Flow

```text
Login.vue
   │
   ▼
POST /login
   │
   ▼
Laravel Route
   │
   ▼
Login Request
   │
   ▼
Authentication Controller
   │
   ▼
Credential Verification
   │
   ▼
Session
   │
   ▼
Authenticated User
   │
   ▼
Redirect Dashboard
```

---

# 27. Authentication File Responsibility

| File / Layer    | Responsibility                  |
| --------------- | ------------------------------- |
| `Login.vue`     | Login UI                        |
| Auth Route      | Endpoint mapping                |
| Auth Request    | Input validation                |
| Auth Controller | Authentication entry point      |
| Auth Service    | Authentication workflow if used |
| `User.php`      | User model                      |
| Auth Middleware | Protect routes                  |
| Session         | Maintain authenticated state    |

---

# 28. Authentication Maintenance Guide

### "Saya mau mengubah tampilan login."

Cari:

```text
resources/js/Pages/Auth/Login.vue
```

---

### "Saya mau menambah field login."

Cari:

```text
Login.vue
+
Login Request
+
Authentication logic
```

---

### "Saya mau mengubah validasi email."

Cari:

```text
Auth Request
```

---

### "Saya mau mengubah cara credential diverifikasi."

Cari:

```text
Authentication Controller / Service
```

---

### "Saya mau mengubah redirect setelah login."

Cari:

```text
Authentication Controller / Login Flow
```

---

### "Saya mau melindungi route baru."

Cari:

```text
routes/web.php
```

dan gunakan:

```text
auth middleware
```

---

### "Saya mau mengubah permission setelah login."

**Jangan mengubah authentication.**

Cari:

```text
code-guide/02_RBAC.md
```

---

# 29. Common Mistakes

## Mistake 1 — Trusting User ID

Buruk:

```php
$userId = $request->input('user_id');
```

Untuk menentukan actor.

Gunakan:

```php
$request->user()->id
```

---

## Mistake 2 — Authorization di Vue Saja

Buruk:

```text
Button hidden
=
Security
```

Tidak cukup.

Backend tetap harus melakukan authorization.

---

## Mistake 3 — Password Plaintext

Tidak boleh menyimpan plaintext password.

---

## Mistake 4 — Authentication dan Permission Dicampur

Authentication:

```text
Who are you?
```

RBAC:

```text
What can you do?
```

---

# 30. Testing

Authentication minimal memiliki test:

```text
[ ] Guest can access login page
[ ] User can login with valid credentials
[ ] Invalid credentials are rejected
[ ] Protected route rejects guest
[ ] Authenticated user can access protected route
[ ] Logout invalidates session
[ ] Session is regenerated after login
[ ] Rate limiting works
```

---

# 31. Security Testing

Test:

```text
[ ] Cannot authenticate using invalid password
[ ] Cannot bypass auth middleware
[ ] Cannot impersonate another user using user_id
[ ] Sensitive data is not returned
[ ] Password is never logged
[ ] Session is regenerated
[ ] Logout invalidates session
```

---

# 32. Code Reading Exercise

Ketika membaca authentication code, ikuti urutan:

```text
1. Login.vue
       ↓
2. Route
       ↓
3. Request
       ↓
4. Controller
       ↓
5. Authentication logic
       ↓
6. User Model
       ↓
7. Session
       ↓
8. Redirect
```

Jangan langsung membaca seluruh project.

Ikuti request flow.

---

# 33. Example Maintenance Scenario

Requirement:

> "Setelah login berhasil, user harus diarahkan ke dashboard."

Trace:

```text
Login.vue
 ↓
POST /login
 ↓
Authentication Controller
 ↓
Successful Authentication
 ↓
Redirect
```

File yang perlu diperiksa:

```text
Auth Controller
```

Tidak perlu mengubah:

```text
User Model
Database
Vue Dashboard
RBAC
```

kecuali memang requirement membutuhkannya.

---

# 34. Example Security Scenario

Requirement:

> "User tidak boleh menentukan siapa yang membuat Stock Out."

Flow:

```text
Stock Out Request
 ↓
Backend
 ↓
Authenticated User
 ↓
$request->user()
 ↓
created_by
```

Frontend tidak mengirim `created_by` sebagai sumber kebenaran.

---

# 35. Authentication Definition of Done

```text
[ ] Login works
[ ] Logout works
[ ] Protected routes work
[ ] Session security configured
[ ] Password securely hashed
[ ] Invalid credential handled
[ ] Rate limiting configured
[ ] Authentication tests added
[ ] Security tests added
[ ] File comments added
[ ] Documentation updated
```

---

# 36. Final Authentication Flow

```text
                         USER
                           │
                           ▼
                       Login.vue
                           │
                           ▼
                      Inertia POST
                           │
                           ▼
                     Laravel Route
                           │
                           ▼
                    Auth Middleware
                           │
                           ▼
                     Auth Request
                           │
                           ▼
                 Authentication Controller
                           │
                           ▼
                 Credential Verification
                           │
                    ┌──────┴──────┐
                    │             │
                 FAILED        SUCCESS
                    │             │
                    ▼             ▼
                 Error        Regenerate
                    │          Session
                    │             │
                    │             ▼
                    │      Authenticated User
                    │             │
                    │             ▼
                    │            RBAC
                    │             │
                    │             ▼
                    │        Authorization
                    │             │
                    └───────┬─────┘
                            ▼
                         Response
                            │
                            ▼
                          Vue.js
```

---

# 37. Key Principle

Authentication hanya menjawab:

> **"Siapa user ini?"**

Setelah itu Inventra melanjutkan:

```text
Authentication
      ↓
Who?
      ↓
RBAC
      ↓
What can they do?
      ↓
Scope
      ↓
Where / which resource?
      ↓
Policy
      ↓
Is this specific action allowed?
```

Dengan pemisahan ini, authentication tetap sederhana dan responsibility setiap layer jelas.

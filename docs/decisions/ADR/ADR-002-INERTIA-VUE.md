# ADR-002 — Inertia + Vue

**Project:** Inventra
**Status:** Accepted
**Date:** 2026-08-30

---

## 1. Context

Inventra membutuhkan interface web untuk:

- Dashboard
- Master Data
- Item Management
- Warehouse
- Stock In
- Stock Out
- Stock Opname
- Asset Management
- Approval
- Reporting
- Audit Log

Application membutuhkan frontend yang interaktif, tetapi tidak membutuhkan kompleksitas SPA penuh untuk seluruh sistem.

Backend juga membutuhkan kontrol penuh terhadap:

```text
Authentication
Authorization
Validation
Business Logic
Database Transaction
API
```

---

## 2. Decision

Inventra menggunakan:

```text
Backend
Laravel

Frontend
Vue.js

Frontend-Backend Bridge
Inertia.js
```

Architecture:

```text
Browser
   ↓
Vue Components
   ↓
Inertia
   ↓
Laravel
   ↓
Service / Business Logic
   ↓
PostgreSQL
```

---

## 3. Why Laravel

Laravel digunakan sebagai application backend karena Inventra membutuhkan:

```text
Routing
Authentication
Authorization
Validation
ORM
Database Migration
Queue
Cache
API
Testing
```

Laravel juga menjadi pusat business logic Inventra.

---

## 4. Why Vue

Vue digunakan sebagai frontend framework karena Inventra membutuhkan UI yang:

```text
Component-based
Interactive
Maintainable
Reactive
```

Contoh component:

```text
DataTable
Form
Modal
Select
Filter
Pagination
Dashboard Card
Chart
```

Component dapat digunakan kembali di berbagai module.

---

## 5. Why Inertia

Inertia menjadi bridge antara Laravel dan Vue.

Tanpa Inertia, architecture dapat menjadi:

```text
Laravel API
      ↓
Vue SPA
      ↓
API Requests
```

Dengan Inertia:

```text
Laravel
   ↓
Inertia
   ↓
Vue Page
```

Sehingga Inventra dapat memperoleh pengalaman frontend modern tanpa harus membangun SPA API-first penuh untuk seluruh interface internal.

---

## 6. Application Architecture

Struktur konseptual:

```text
Laravel
│
├── Routes
│
├── Controllers
│
├── Requests
│
├── Services
│
├── Policies
│
├── Models
│
└── Database
        │
        ▼
    PostgreSQL


Inertia
    │
    ▼

Vue
│
├── Pages
├── Components
├── Layouts
└── Composables
```

---

## 7. Why Not Full SPA

Full SPA seperti:

```text
Vue SPA
    ↓
REST API
    ↓
Laravel
```

tetap valid.

Namun untuk kebutuhan utama Inventra, architecture tersebut menambah kompleksitas:

```text
API Authentication
API State Management
API Error Handling
API Contracts
Frontend Data Fetching
Frontend Authorization Handling
```

Inventra tidak membutuhkan kompleksitas tersebut pada seluruh internal web interface.

REST API tetap dapat disediakan untuk kebutuhan integrasi eksternal.

---

## 8. Why Not Blade Only

Blade tetap cocok untuk aplikasi server-rendered sederhana.

Namun Inventra memiliki UI yang cukup interaktif:

```text
Dynamic Tables
Filters
Forms
Modal
Dashboard
Charts
Approval
Search
Pagination
```

Vue memberikan component model yang lebih sesuai untuk kebutuhan tersebut.

---

## 9. Consequences

### Positive

```text
+ Modern reactive UI
+ Laravel remains application center
+ Less SPA infrastructure
+ Reusable Vue components
+ Server-side routing remains simple
+ Authentication integration is simpler
+ Validation can remain close to backend
```

### Negative

```text
- Requires understanding Laravel + Vue
- Inertia adds another abstraction layer
- Frontend and backend conventions must remain consistent
- Large frontend components can become difficult to maintain
```

---

## 10. Development Rules

### Backend owns business logic

Business rules tidak boleh hanya berada di Vue.

Contoh:

```text
Stock Out
```

Frontend boleh melakukan:

```text
UI Validation
User Feedback
```

Tetapi backend tetap melakukan:

```text
Stock Validation
Permission Validation
Transaction
Inventory Update
Ledger Creation
```

Backend adalah authoritative layer.

---

## 11. Frontend Responsibility

Vue bertanggung jawab terhadap:

```text
UI
Interaction
State Display
Form Interaction
Loading State
Error Presentation
Component Reuse
```

Vue tidak menjadi source of truth untuk business-critical data.

---

## 12. Inertia Responsibility

Inertia menangani komunikasi page-level antara:

```text
Laravel
    ↕
Vue
```

Gunakan Inertia untuk navigation dan form/page interaction yang memang menjadi bagian dari web application.

---

## 13. REST API

REST API tetap disediakan pada module yang memang membutuhkan:

```text
External Integration
Mobile Application
Third-party Consumer
Automation
Future Client
```

Sehingga architecture Inventra menjadi:

```text
                    Laravel
                   /       \
                  /         \
             Inertia        REST API
                ↓              ↓
               Vue       External Client
```

---

## 14. Security Principle

Authorization tidak boleh hanya dilakukan di Vue.

Contoh:

```text
Vue
 ↓
Hide Delete Button
```

bukan security mechanism.

Backend tetap harus melakukan:

```text
Request
 ↓
Authentication
 ↓
Authorization
 ↓
Policy
 ↓
Business Logic
```

---

## 15. Validation Principle

Frontend validation digunakan untuk UX.

Backend validation digunakan untuk security dan data integrity.

```text
Vue Validation
      ↓
Fast User Feedback

Laravel Validation
      ↓
Authoritative Validation
```

Keduanya bukan pengganti satu sama lain.

---

## 16. Maintainability

Component Vue harus dibuat dengan tanggung jawab yang jelas.

Hindari component seperti:

```text
InventoryPage.vue
```

yang berisi:

```text
Table
Form
Modal
API Logic
Business Logic
Formatting
Permission Logic
```

seluruhnya dalam satu file.

Pisahkan menjadi component/composable/service yang sesuai.

---

## 17. Code Documentation

File penting mengikuti:

```text
docs/code-guide/00_CODE_DOCUMENTATION_STANDARD.md
```

Section yang kompleks dapat diberikan komentar:

```vue
<!--
  Purpose:
  Display inventory balance for the selected warehouse.

  Important:
  This component only displays inventory data.
  Stock calculation remains authoritative on the backend.
-->
```

Komentar menjelaskan **why / responsibility**, bukan syntax yang sudah jelas.

---

## 18. Testing

Testing dilakukan pada:

```text
Laravel
    ↓
Unit / Feature Tests

Inertia
    ↓
Integration / Feature Tests

Vue
    ↓
Component Tests where useful

Browser
    ↓
End-to-End / Smoke Tests
```

Prioritas testing diberikan pada business-critical flow.

---

## 19. Performance Principle

Jangan mengirim data yang tidak diperlukan ke frontend.

Gunakan:

```text
Pagination
Filtering
Lazy Loading
Selective Data
Query Optimization
```

untuk dataset besar.

Contoh:

```text
10,000 Transactions
```

tidak dikirim sekaligus ke browser hanya untuk menampilkan 20 row.

---

## 20. Alternatives Considered

### Laravel Blade

Ditolak sebagai primary frontend karena UI Inventra membutuhkan interaktivitas yang lebih tinggi.

### Vue SPA + REST API

Tidak dipilih sebagai primary web architecture karena menambah kompleksitas yang belum diperlukan.

### React

Valid secara teknis, tetapi Vue dipilih untuk Inventra sebagai frontend framework.

---

## 21. Related Decisions

```text
ADR-001 — PostgreSQL
ADR-003 — Inventory Ledger
ADR-004 — RBAC Authorization
ADR-006 — API Architecture
```

---

## 22. Final Decision

**Accepted**

Inventra menggunakan:

```text
Laravel
    +
Inertia.js
    +
Vue.js
    +
PostgreSQL
```

Laravel menjadi pusat business logic dan security, Inertia menjadi bridge untuk web interface, dan Vue menjadi presentation/UI layer.

REST API tetap tersedia sebagai interface untuk kebutuhan integrasi yang memang memerlukannya.

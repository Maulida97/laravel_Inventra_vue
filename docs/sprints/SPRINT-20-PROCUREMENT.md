# SPRINT-20 — PROCUREMENT

**Project:** Inventra
**Sprint:** 20
**Epic:** EPIC-09 — Procurement
**Status:** Planned
**Phase:** Phase 11

---

# 1. Sprint Objective

Membangun modul **Procurement** untuk mengelola proses pengadaan barang mulai dari permintaan pembelian hingga penerimaan barang.

Procurement harus terintegrasi dengan:

```text
Master Data
Item Management
Warehouse
Inventory
Approval Workflow
Audit Log
Reporting
```

Core workflow:

```text
Purchase Request
       ↓
Approval
       ↓
Purchase Order
       ↓
Supplier
       ↓
Receiving
       ↓
Inventory
```

---

# 2. Scope

Sprint ini mencakup tiga bagian utama:

```text
1. Purchase Request
2. Purchase Order
3. Receiving
```

---

# 3. Purchase Request

Purchase Request (PR) digunakan untuk mengajukan kebutuhan pengadaan barang.

## Features

```text
Create PR
View PR
Edit Draft PR
Submit PR
Cancel PR
View PR History
```

## PR Data

Minimal:

```text
PR Number
Requester
Warehouse
Request Date
Required Date
Purpose
Status
Items
Quantity
Unit
Notes
```

---

# 4. Purchase Request Workflow

Workflow:

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

Setelah approved:

```text
APPROVED
   ↓
Eligible for Purchase Order
```

## Rules

1. PR yang masih `DRAFT` dapat diedit oleh requester sesuai permission.
2. PR harus melalui validation sebelum submit.
3. PR yang sudah `SUBMITTED` tidak dapat diedit secara normal.
4. PR membutuhkan approval sesuai permission.
5. PR yang `REJECTED` tidak dapat digunakan langsung untuk membuat PO.
6. PR yang `APPROVED` dapat menjadi dasar Purchase Order.
7. Setiap perubahan penting harus dapat diaudit.

---

# 5. Purchase Order

Purchase Order (PO) digunakan untuk membuat pesanan resmi kepada supplier.

## Features

```text
Create PO
View PO
Edit Draft PO
Submit PO
Approve PO
Cancel PO
View PO History
```

## PO Data

Minimal:

```text
PO Number
Supplier
Purchase Request Reference
Warehouse
Order Date
Expected Delivery Date
Status
Items
Quantity
Unit Price
Subtotal
Tax
Discount
Grand Total
Notes
```

---

# 6. Purchase Order Workflow

Workflow:

```text
DRAFT
   ↓
SUBMITTED
   ↓
APPROVED
   ↓
ORDERED
   ↓
PARTIALLY_RECEIVED / RECEIVED
```

Jika ditolak:

```text
SUBMITTED
   ↓
REJECTED
```

Jika dibatalkan:

```text
DRAFT / APPROVED / ORDERED
   ↓
CANCELLED
```

Transition harus divalidasi oleh backend.

---

# 7. Purchase Order Rules

1. PO dapat dibuat berdasarkan approved PR.
2. PO harus memiliki supplier.
3. PO harus memiliki minimal satu item.
4. Quantity harus lebih besar dari zero.
5. Harga harus memiliki nilai valid.
6. PO yang sudah approved tidak boleh diedit secara normal.
7. PO yang sudah `RECEIVED` tidak dapat dibatalkan secara sembarangan.
8. PO yang sudah `CANCELLED` tidak dapat diproses menjadi receiving.
9. PO dapat menerima barang secara partial.
10. Semua perubahan penting harus dapat diaudit.

---

# 8. Receiving

Receiving digunakan untuk mencatat barang yang benar-benar diterima dari supplier.

Receiving **bukan sekadar update status PO**.

Receiving merupakan business transaction yang dapat menyebabkan:

```text
Inventory Increase
Inventory Ledger Entry
Audit Event
```

---

# 9. Receiving Workflow

Contoh:

```text
PO APPROVED
     ↓
ORDERED
     ↓
RECEIVING
     ↓
PARTIALLY_RECEIVED
     ↓
RECEIVED
```

Jika seluruh quantity telah diterima:

```text
PO
 ↓
RECEIVED
```

Jika hanya sebagian:

```text
PO
 ↓
PARTIALLY_RECEIVED
```

---

# 10. Partial Receiving

Inventra harus mendukung partial receiving.

Contoh:

```text
PO Quantity = 100

Receiving #1 = 60
Receiving #2 = 40
```

Maka:

```text
Received = 100
Remaining = 0
```

Status:

```text
RECEIVED
```

Contoh lain:

```text
PO Quantity = 100

Receiving = 60
```

Maka:

```text
Received = 60
Remaining = 40
```

Status:

```text
PARTIALLY_RECEIVED
```

---

# 11. Receiving Rules

1. Receiving harus mengacu pada PO yang valid.
2. Receiving hanya dapat dilakukan terhadap PO yang eligible.
3. Quantity received tidak boleh melebihi remaining quantity tanpa business rule khusus.
4. Partial receiving harus didukung.
5. Setiap receiving yang berhasil harus menghasilkan inventory movement.
6. Inventory movement harus menghasilkan Inventory Ledger.
7. Receiving harus memiliki warehouse destination.
8. Receiving harus dapat ditelusuri ke PO.
9. Receiving finalized tidak boleh diedit secara normal.
10. Koreksi receiving harus menggunakan mekanisme adjustment/reversal yang terkontrol.

---

# 12. Procurement → Inventory Integration

Flow utama:

```text
Purchase Request
       ↓
Approval
       ↓
Purchase Order
       ↓
Supplier
       ↓
Receiving
       ↓
Inventory Service
       ↓
Inventory Balance
       ↓
Inventory Ledger
```

Procurement tidak boleh mengubah quantity inventory secara langsung.

Gunakan boundary:

```text
Receiving Service
       ↓
Inventory Service
```

---

# 13. Inventory Transaction

Ketika receiving berhasil:

```text
BEGIN
   ↓
Create Receiving
   ↓
Validate Remaining PO Quantity
   ↓
Increase Inventory
   ↓
Create Inventory Ledger
   ↓
Create Audit Log
   ↓
COMMIT
```

Jika terjadi error:

```text
ROLLBACK
```

Tujuannya menjaga consistency antara:

```text
Receiving
Inventory Balance
Inventory Ledger
Audit Log
```

---

# 14. Supplier

Procurement membutuhkan supplier sebagai master data.

Minimal:

```text
Supplier Code
Supplier Name
Contact
Address
Status
```

Supplier inactive tidak boleh digunakan untuk PO baru.

Supplier yang sudah digunakan pada historical transaction tidak boleh dihapus secara destructive tanpa aturan khusus.

---

# 15. Permission

Permission Procurement minimal:

```text
procurement.view
procurement.create
procurement.update
procurement.submit
procurement.cancel

purchase-order.view
purchase-order.create
purchase-order.update
purchase-order.submit
purchase-order.approve
purchase-order.cancel

receiving.view
receiving.create
receiving.update
receiving.confirm
```

Nama permission dapat disesuaikan dengan implementasi final RBAC.

---

# 16. Role Example

Contoh akses:

```text
Staff
├── Create Purchase Request
├── View Own Request
└── Submit Purchase Request

Manager
├── View Purchase Request
├── Approve Purchase Request
└── View Purchase Order

Procurement
├── Manage Purchase Request
├── Create Purchase Order
├── Manage Purchase Order
└── Process Receiving

Warehouse
├── View Purchase Order
└── Process Receiving

Admin
└── Full Access according to permission
```

Role tidak boleh menjadi satu-satunya source of truth.

Permission tetap menjadi authorization boundary.

---

# 17. Warehouse Scope

Procurement dan Receiving harus memperhatikan warehouse scope.

Contoh:

```text
User
   ↓
Warehouse A
```

maka user tidak otomatis dapat melakukan receiving ke:

```text
Warehouse B
```

Backend harus melakukan authorization terhadap warehouse scope.

---

# 18. Database Requirements

Entity utama:

```text
suppliers
purchase_requests
purchase_request_items

purchase_orders
purchase_order_items

receivings
receiving_items
```

Relasi konseptual:

```text
Supplier
   │
   ▼
Purchase Order
   │
   ├── Purchase Request
   │
   └── PO Items
           │
           ▼
       Receiving
           │
           ▼
       Inventory
```

Detail final mengikuti:

```text
docs/05_DATABASE.md
```

---

# 19. API Requirements

Endpoint mengikuti REST architecture.

Contoh:

```text
GET    /api/v1/purchase-requests
POST   /api/v1/purchase-requests
GET    /api/v1/purchase-requests/{id}
PUT    /api/v1/purchase-requests/{id}
POST   /api/v1/purchase-requests/{id}/submit
POST   /api/v1/purchase-requests/{id}/approve
POST   /api/v1/purchase-requests/{id}/reject
```

Purchase Order:

```text
GET    /api/v1/purchase-orders
POST   /api/v1/purchase-orders
GET    /api/v1/purchase-orders/{id}
PUT    /api/v1/purchase-orders/{id}
POST   /api/v1/purchase-orders/{id}/submit
POST   /api/v1/purchase-orders/{id}/approve
POST   /api/v1/purchase-orders/{id}/cancel
```

Receiving:

```text
GET    /api/v1/receivings
POST   /api/v1/receivings
GET    /api/v1/receivings/{id}
POST   /api/v1/receivings/{id}/confirm
```

Endpoint final harus mengikuti `06_API.md`.

---

# 20. Frontend Requirements

Halaman utama:

```text
Procurement
├── Purchase Requests
├── Purchase Orders
└── Receiving
```

Purchase Request:

```text
List
Create
Detail
Edit
Approval
History
```

Purchase Order:

```text
List
Create
Detail
Approval
History
```

Receiving:

```text
List
Create
Detail
Confirm
History
```

UI harus mengikuti:

```text
RBAC
Responsive Design
Light Mode
Dark Mode
Inventra Design System
```

---

# 21. Procurement Dashboard

Jika diperlukan, procurement dapat memiliki summary:

```text
Pending Purchase Requests
Pending Purchase Orders
Open Purchase Orders
Partially Received
Overdue Deliveries
Recent Receiving
```

Jangan membuat dashboard procurement terlalu kompleks pada V1.

---

# 22. Audit Log

Event yang harus dipertimbangkan:

```text
PURCHASE_REQUEST_CREATED
PURCHASE_REQUEST_SUBMITTED
PURCHASE_REQUEST_APPROVED
PURCHASE_REQUEST_REJECTED

PURCHASE_ORDER_CREATED
PURCHASE_ORDER_SUBMITTED
PURCHASE_ORDER_APPROVED
PURCHASE_ORDER_CANCELLED

RECEIVING_CREATED
RECEIVING_CONFIRMED
```

Receiving confirmation harus memiliki audit trail karena berdampak terhadap inventory.

---

# 23. Reporting

Procurement dapat menyediakan:

```text
Purchase Request Report
Purchase Order Report
Receiving Report
Supplier Purchase Report
Outstanding Purchase Order
```

Reporting harus mengikuti:

```text
Permission
Warehouse Scope
Date Filter
Status Filter
Supplier Filter
```

---

# 24. Validation

Minimal validation:

### Purchase Request

```text
Requester required
Warehouse required
Item required
Quantity > 0
Required date valid
```

### Purchase Order

```text
Supplier required
Warehouse required
Item required
Quantity > 0
Price >= 0
Approved PR valid
```

### Receiving

```text
PO required
Warehouse required
Item required
Received quantity > 0
Received quantity <= remaining quantity
```

Validation final tetap dilakukan di backend.

---

# 25. Concurrency

Receiving dapat dilakukan oleh beberapa user.

Karena itu proses:

```text
Check Remaining Quantity
+
Create Receiving
+
Update Inventory
```

harus memperhatikan concurrency.

Gunakan database transaction dan locking yang sesuai jika diperlukan.

Tujuannya mencegah:

```text
PO = 100

User A receives 70
User B receives 70

Result incorrectly becomes 140
```

Business rule harus mencegah over-receiving.

---

# 26. Error Handling

Contoh business errors:

```text
PR_ALREADY_APPROVED
PR_NOT_APPROVED
PO_ALREADY_CANCELLED
PO_NOT_ELIGIBLE_FOR_RECEIVING
RECEIVING_EXCEEDS_REMAINING
SUPPLIER_INACTIVE
ITEM_INACTIVE
WAREHOUSE_ACCESS_DENIED
```

Error harus dikembalikan menggunakan API error format yang konsisten.

---

# 27. Testing

Minimal testing:

## Purchase Request

```text
Create PR
Update Draft PR
Submit PR
Approve PR
Reject PR
Unauthorized Approval
```

## Purchase Order

```text
Create PO
Create PO from Approved PR
Submit PO
Approve PO
Cancel PO
Invalid Supplier
```

## Receiving

```text
Create Receiving
Confirm Receiving
Partial Receiving
Full Receiving
Over Receiving
Invalid PO
Unauthorized Warehouse
```

## Inventory

```text
Receiving increases inventory
Receiving creates ledger
Failed receiving rolls back
```

---

# 28. Definition of Done

Sprint Procurement dianggap selesai apabila:

```text
[ ] Purchase Request implemented
[ ] Purchase Request workflow implemented
[ ] Purchase Order implemented
[ ] Purchase Order workflow implemented
[ ] Receiving implemented
[ ] Partial receiving implemented
[ ] Supplier integration implemented
[ ] Warehouse scope implemented
[ ] RBAC implemented
[ ] Validation implemented
[ ] Inventory integration implemented
[ ] Inventory Ledger integration implemented
[ ] Audit Log integration implemented
[ ] API implemented
[ ] Frontend implemented
[ ] Responsive UI implemented
[ ] Tests implemented
[ ] Error handling implemented
[ ] Documentation updated
[ ] No critical bug remaining
```

---

# 29. Documentation Dependencies

Sebelum implementasi, baca:

```text
docs/00_PRD.md
docs/02_FEATURE_DECISIONS.md
docs/03_MODULES.md
docs/04_USER_FLOW.md
docs/05_DATABASE.md
docs/06_API.md
docs/07_PERMISSION_MATRIX.md
docs/09_BACKLOG.md

docs/architecture/SYSTEM_ARCHITECTURE.md
docs/architecture/MODULE_ARCHITECTURE.md
docs/architecture/DATA_FLOW.md
docs/architecture/SECURITY_ARCHITECTURE.md

docs/code-guide/03_MASTER_DATA.md
docs/code-guide/10_APPROVAL_WORKFLOW.md
docs/code-guide/13_AUDIT_LOG.md

docs/decisions/ADR/
```

---

# 30. Related Architecture Decisions

Procurement harus mengikuti:

```text
ADR-001 — PostgreSQL
ADR-003 — Inventory Ledger
ADR-004 — RBAC Authorization
ADR-005 — Approval Workflow
ADR-006 — API Architecture
ADR-007 — Database Performance
ADR-008 — Audit Log
```

---

# 31. Out of Scope

Tidak termasuk dalam sprint ini kecuali requirement berubah:

```text
Supplier Portal
Automated Supplier Integration
Electronic Procurement
Three-way Matching
Invoice Management
Payment Processing
Advanced Procurement Forecasting
AI Procurement Recommendation
```

Fitur tersebut dapat menjadi backlog/epic berikutnya.

---

# 32. Implementation Order

Implementasi direkomendasikan:

```text
1. Supplier Master Data
       ↓
2. Purchase Request
       ↓
3. Purchase Request Approval
       ↓
4. Purchase Order
       ↓
5. Purchase Order Approval
       ↓
6. Receiving
       ↓
7. Inventory Integration
       ↓
8. Inventory Ledger
       ↓
9. Audit Log
       ↓
10. Reporting
       ↓
11. Testing
       ↓
12. UI/UX Refinement
```

---

# 33. Final Architecture Flow

```text
                    PROCUREMENT

                         │
                         ▼
                 Purchase Request
                         │
                         ▼
                      Approval
                         │
                         ▼
                  Purchase Order
                         │
                         ▼
                     Supplier
                         │
                         ▼
                     Receiving
                         │
                         ▼
                  Inventory Service
                         │
              ┌──────────┴──────────┐
              ▼                     ▼
      Inventory Balance       Inventory Ledger
              │                     │
              └──────────┬──────────┘
                         ▼
                     Audit Log
```

---

# 34. Sprint Principle

Procurement harus dibangun sebagai bagian dari modular monolith Inventra.

Procurement tidak boleh:

```text
Directly modify inventory tables
Bypass approval
Bypass RBAC
Bypass audit
Create inconsistent stock
```

Sebaliknya:

```text
Procurement
     ↓
Business Service
     ↓
Approval
     ↓
Receiving
     ↓
Inventory Service
     ↓
Ledger
     ↓
Audit
```

---

# 35. Final Decision

`SPRINT-20-PROCUREMENT.md` menjadi dokumen implementasi untuk **EPIC-09 — Procurement**.

Scope utama:

```text
Purchase Request
        +
Purchase Order
        +
Receiving
        +
Inventory Integration
```

Sprint ini menjadi penghubung antara requirement Procurement di PRD/Backlog/Roadmap dengan implementasi aktual.

**Status: Planned**

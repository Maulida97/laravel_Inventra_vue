# Inventra

## REST API Specification

**Document:** REST API Specification
**Version:** V1.0
**Status:** Draft
**Base URL:** `/api/v1`

---

# 1. API Principles

REST API Inventra mengikuti prinsip:

1. Menggunakan RESTful resource.
2. API menggunakan versioning melalui `/api/v1`.
3. Authentication wajib untuk endpoint internal.
4. Authorization menggunakan permission dan scope.
5. API menggunakan business logic yang sama dengan web application.
6. Response menggunakan format JSON.
7. Validation dilakukan di backend.
8. Error response menggunakan HTTP status code yang sesuai.
9. Inventory transaction tidak boleh melewati business rule hanya karena menggunakan API.
10. API tidak boleh melakukan direct update terhadap stock balance.

---

# 2. Authentication

API menggunakan token-based authentication.

### Login

```http
POST /api/v1/auth/login
```

Request:

```json
{
  "email": "user@example.com",
  "password": "password"
}
```

Response:

```json
{
  "message": "Login successful",
  "data": {
    "user": {},
    "token": "..."
  }
}
```

### Logout

```http
POST /api/v1/auth/logout
```

Authentication:

```text
Bearer Token
```

---

# 3. Current User

### Get Current User

```http
GET /api/v1/auth/me
```

Response:

```json
{
  "data": {
    "id": 1,
    "name": "Budi",
    "roles": [],
    "permissions": [],
    "scopes": []
  }
}
```

Endpoint ini membantu client mengetahui permission dan scope yang dimiliki user.

---

# 4. Users

### List Users

```http
GET /api/v1/users
```

### Get User

```http
GET /api/v1/users/{id}
```

### Create User

```http
POST /api/v1/users
```

### Update User

```http
PUT /api/v1/users/{id}
```

### Deactivate User

```http
PATCH /api/v1/users/{id}/status
```

User management membutuhkan permission administratif.

---

# 5. Roles

### List Roles

```http
GET /api/v1/roles
```

### Get Role

```http
GET /api/v1/roles/{id}
```

### Create Role

```http
POST /api/v1/roles
```

### Update Role

```http
PUT /api/v1/roles/{id}
```

### Assign Permission

```http
POST /api/v1/roles/{id}/permissions
```

---

# 6. Permissions

### List Permissions

```http
GET /api/v1/permissions
```

Permission biasanya merupakan system-defined data.

User tidak dapat membuat permission baru melalui endpoint normal.

---

# 7. Departments

### List

```http
GET /api/v1/departments
```

### Get

```http
GET /api/v1/departments/{id}
```

### Create

```http
POST /api/v1/departments
```

### Update

```http
PUT /api/v1/departments/{id}
```

### Status

```http
PATCH /api/v1/departments/{id}/status
```

---

# 8. Warehouses

### List Warehouses

```http
GET /api/v1/warehouses
```

### Get Warehouse

```http
GET /api/v1/warehouses/{id}
```

### Create Warehouse

```http
POST /api/v1/warehouses
```

### Update Warehouse

```http
PUT /api/v1/warehouses/{id}
```

### Status

```http
PATCH /api/v1/warehouses/{id}/status
```

Warehouse list harus otomatis difilter berdasarkan scope user.

---

# 9. Locations

### List Locations

```http
GET /api/v1/warehouses/{warehouse}/locations
```

### Get Location

```http
GET /api/v1/locations/{id}
```

### Create Location

```http
POST /api/v1/warehouses/{warehouse}/locations
```

### Update Location

```http
PUT /api/v1/locations/{id}
```

### Deactivate

```http
PATCH /api/v1/locations/{id}/status
```

Parent-child hierarchy harus divalidasi oleh backend.

---

# 10. Categories

### List

```http
GET /api/v1/categories
```

### Get

```http
GET /api/v1/categories/{id}
```

### Create

```http
POST /api/v1/categories
```

### Update

```http
PUT /api/v1/categories/{id}
```

---

# 11. Units

### List Units

```http
GET /api/v1/units
```

### Get Unit

```http
GET /api/v1/units/{id}
```

Unit merupakan master reference.

Inventra tidak menyediakan global endpoint seperti:

```http
POST /api/v1/unit-conversions
```

karena content per unit bersifat manual dan dapat berbeda berdasarkan item/brand/transaksi.

---

# 12. Items

### List Items

```http
GET /api/v1/items
```

Supported filters:

```text
category_id
item_type
brand
status
search
```

### Get Item

```http
GET /api/v1/items/{id}
```

### Create Item

```http
POST /api/v1/items
```

Example:

```json
{
  "code": "ITEM-001",
  "name": "Hydraulic Oil",
  "category_id": 10,
  "brand": "Brand A",
  "item_type": "quantity",
  "base_unit_id": 2,
  "minimum_stock": 100
}
```

### Update Item

```http
PUT /api/v1/items/{id}
```

### Status

```http
PATCH /api/v1/items/{id}/status
```

---

# 13. Stock Balance

### Get Stock

```http
GET /api/v1/stock
```

Filters:

```text
item_id
warehouse_id
location_id
category_id
low_stock
```

### Get Item Stock

```http
GET /api/v1/items/{item}/stock
```

### Get Warehouse Stock

```http
GET /api/v1/warehouses/{warehouse}/stock
```

Response:

```json
{
  "data": {
    "item": {},
    "warehouse": {},
    "location": {},
    "quantity": 500,
    "reserved_quantity": 0,
    "available_quantity": 500
  }
}
```

Stock endpoint hanya membaca stock.

Tidak tersedia endpoint:

```http
PUT /api/v1/stock/{id}
```

untuk mengubah quantity secara langsung.

---

# 14. Stock In

### Create Stock In

```http
POST /api/v1/stock-in
```

Request:

```json
{
  "item_id": 10,
  "warehouse_id": 1,
  "location_id": 5,
  "quantity": 6,
  "unit_id": 4,
  "content_per_unit": 100,
  "content_unit_id": 1,
  "notes": "Receiving PO-000123"
}
```

System menghitung:

```text
6 × 100 = 600 equivalent units
```

### Get Stock In

```http
GET /api/v1/stock-in/{id}
```

### List Stock In

```http
GET /api/v1/stock-in
```

---

# 15. Stock Out

### Create Stock Out

```http
POST /api/v1/stock-out
```

Request:

```json
{
  "item_id": 10,
  "warehouse_id": 1,
  "location_id": 5,
  "quantity": 2,
  "unit_id": 4,
  "content_per_unit": 100,
  "content_unit_id": 1,
  "department_id": 3,
  "reason": "Operational usage"
}
```

System melakukan:

```text
Validation
 ↓
Authorization
 ↓
Stock Availability
 ↓
Approval
 ↓
Execution
```

### Get

```http
GET /api/v1/stock-out/{id}
```

### List

```http
GET /api/v1/stock-out
```

---

# 16. Stock Transfer

### Create Transfer

```http
POST /api/v1/stock-transfers
```

Request:

```json
{
  "item_id": 10,
  "source_warehouse_id": 1,
  "source_location_id": 5,
  "destination_warehouse_id": 2,
  "destination_location_id": 10,
  "quantity": 50,
  "unit_id": 2
}
```

System harus memastikan:

- Source dapat diakses user.
- Destination dapat diakses user.
- Stock mencukupi.
- Item valid.
- Location sesuai warehouse.

### Get

```http
GET /api/v1/stock-transfers/{id}
```

### List

```http
GET /api/v1/stock-transfers
```

---

# 17. Stock Adjustment

### Create Adjustment

```http
POST /api/v1/stock-adjustments
```

Request:

```json
{
  "item_id": 10,
  "warehouse_id": 1,
  "location_id": 5,
  "quantity": -20,
  "reason": "Stock Opname difference"
}
```

Adjustment harus memiliki reason.

### Get

```http
GET /api/v1/stock-adjustments/{id}
```

### List

```http
GET /api/v1/stock-adjustments
```

Adjustment mengikuti approval workflow apabila diwajibkan.

---

# 18. Stock Return

### Create Return

```http
POST /api/v1/stock-returns
```

Request:

```json
{
  "item_id": 10,
  "warehouse_id": 1,
  "location_id": 5,
  "quantity": 2,
  "reference_type": "stock_out",
  "reference_id": 123,
  "reason": "Unused material"
}
```

Return mengarah kembali ke inventory melalui Stock In.

---

# 19. Inventory Transactions

### List Transactions

```http
GET /api/v1/inventory-transactions
```

Filters:

```text
reference_number
transaction_type
item_id
warehouse_id
location_id
status
date_from
date_to
```

### Get Transaction

```http
GET /api/v1/inventory-transactions/{id}
```

Response harus dapat menampilkan informasi tracing:

```text
Transaction
 ├── Item
 ├── Warehouse
 ├── Location
 ├── User
 ├── Reference
 ├── Approval
 └── Ledger
```

---

# 20. Inventory Ledger

### Get Ledger

```http
GET /api/v1/inventory-ledger
```

Filters:

```text
item_id
warehouse_id
location_id
transaction_type
date_from
date_to
```

### Get Item Ledger

```http
GET /api/v1/items/{item}/ledger
```

Ledger bersifat read-only melalui API.

Tidak tersedia:

```http
POST /api/v1/inventory-ledger
PUT /api/v1/inventory-ledger/{id}
DELETE /api/v1/inventory-ledger/{id}
```

Ledger dibuat sebagai konsekuensi inventory transaction.

---

# 21. Stock Opname

### Create

```http
POST /api/v1/stock-opnames
```

Request:

```json
{
  "warehouse_id": 1,
  "location_id": 5
}
```

### Get

```http
GET /api/v1/stock-opnames/{id}
```

### List

```http
GET /api/v1/stock-opnames
```

### Submit Physical Count

```http
POST /api/v1/stock-opnames/{id}/count
```

Request:

```json
{
  "items": [
    {
      "item_id": 10,
      "physical_quantity": 480
    }
  ]
}
```

### Complete

```http
POST /api/v1/stock-opnames/{id}/complete
```

System menghitung:

```text
difference =
physical_quantity - system_quantity
```

Adjustment dilakukan melalui inventory transaction.

---

# 22. Purchase Requests

### Create

```http
POST /api/v1/purchase-requests
```

### List

```http
GET /api/v1/purchase-requests
```

### Get

```http
GET /api/v1/purchase-requests/{id}
```

### Submit

```http
POST /api/v1/purchase-requests/{id}/submit
```

### Cancel

```http
POST /api/v1/purchase-requests/{id}/cancel
```

PR hanya dapat dibuat untuk item yang diizinkan oleh department/user scope.

---

# 23. Purchase Orders

### Create

```http
POST /api/v1/purchase-orders
```

### List

```http
GET /api/v1/purchase-orders
```

### Get

```http
GET /api/v1/purchase-orders/{id}
```

### Submit

```http
POST /api/v1/purchase-orders/{id}/submit
```

PO dapat mengacu pada approved Purchase Request.

---

# 24. Receiving

### Create Receiving

```http
POST /api/v1/receivings
```

### Get

```http
GET /api/v1/receivings/{id}
```

### Confirm

```http
POST /api/v1/receivings/{id}/confirm
```

Confirmation menghasilkan Stock In.

```text
Receiving
    ↓
Confirm
    ↓
Stock In
    ↓
Ledger
    ↓
Stock Balance
```

---

# 25. Assets

### List

```http
GET /api/v1/assets
```

Filters:

```text
status
department_id
warehouse_id
location_id
custodian_id
item_id
```

### Get

```http
GET /api/v1/assets/{id}
```

### Register

```http
POST /api/v1/assets
```

Request:

```json
{
  "item_id": 20,
  "asset_tag": "AST-000123",
  "serial_number": "ABC123",
  "warehouse_id": 1,
  "location_id": 5,
  "department_id": 2
}
```

### Update

```http
PUT /api/v1/assets/{id}
```

### Assign

```http
POST /api/v1/assets/{id}/assign
```

### Return

```http
POST /api/v1/assets/{id}/return
```

### Dispose

```http
POST /api/v1/assets/{id}/dispose
```

Setiap perubahan lifecycle menghasilkan Asset History.

---

# 26. Asset History

### Get Asset History

```http
GET /api/v1/assets/{asset}/history
```

History bersifat read-only.

---

# 27. Approval

### List Pending Approval

```http
GET /api/v1/approvals/pending
```

### Get Approval

```http
GET /api/v1/approvals/{id}
```

### Approve

```http
POST /api/v1/approvals/{id}/approve
```

Request:

```json
{
  "notes": "Approved"
}
```

### Reject

```http
POST /api/v1/approvals/{id}/reject
```

Request:

```json
{
  "notes": "Please revise quantity"
}
```

Backend harus memastikan approver:

```text
Has Permission
+
Has Correct Scope
+
Is Current Approver
```

---

# 28. Approval Workflow Configuration

### List Workflows

```http
GET /api/v1/approval-workflows
```

### Get Workflow

```http
GET /api/v1/approval-workflows/{id}
```

### Create

```http
POST /api/v1/approval-workflows
```

### Update

```http
PUT /api/v1/approval-workflows/{id}
```

Workflow configuration hanya dapat dilakukan oleh user dengan permission yang sesuai.

---

# 29. Reporting

### Stock Report

```http
GET /api/v1/reports/stock
```

### Stock Movement

```http
GET /api/v1/reports/stock-movement
```

### Stock Opname

```http
GET /api/v1/reports/stock-opname
```

### Asset Report

```http
GET /api/v1/reports/assets
```

### Transaction Report

```http
GET /api/v1/reports/transactions
```

### Procurement Report

```http
GET /api/v1/reports/procurement
```

Semua report menerapkan permission dan scope.

---

# 30. Dashboard

### Dashboard Summary

```http
GET /api/v1/dashboard
```

Response dapat berisi:

```json
{
  "data": {
    "stock_summary": {},
    "low_stock": [],
    "pending_approvals": [],
    "recent_transactions": [],
    "asset_summary": {}
  }
}
```

Data dashboard disesuaikan dengan scope user.

---

# 31. Audit Logs

### List Audit Logs

```http
GET /api/v1/audit-logs
```

### Get Audit Log

```http
GET /api/v1/audit-logs/{id}
```

Audit Log bersifat read-only.

User biasa tidak dapat menghapus atau mengubah audit log.

---

# 32. Export

Export menggunakan endpoint khusus.

### Excel

```http
GET /api/v1/exports/stock/excel
GET /api/v1/exports/transactions/excel
GET /api/v1/exports/assets/excel
```

### PDF

```http
GET /api/v1/exports/stock/pdf
GET /api/v1/exports/transactions/pdf
GET /api/v1/exports/assets/pdf
```

Export harus menggunakan filter dan scope yang sama dengan report.

---

# 33. Filtering & Pagination

List endpoint mendukung pagination.

Contoh:

```http
GET /api/v1/items?page=1&per_page=20
```

Filtering:

```http
GET /api/v1/inventory-transactions?warehouse_id=1&status=completed
```

Search:

```http
GET /api/v1/items?search=hydraulic
```

Sorting:

```http
GET /api/v1/items?sort=name&direction=asc
```

Implementation final dapat menggunakan Laravel API Resources dan pagination standar Laravel.

---

# 34. Standard Response

Success:

```json
{
  "message": "Success",
  "data": {}
}
```

Collection:

```json
{
  "message": "Success",
  "data": [],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 100
  }
}
```

---

# 35. Validation Error

HTTP:

```text
422 Unprocessable Entity
```

Response:

```json
{
  "message": "Validation failed",
  "errors": {
    "quantity": ["The quantity must be greater than zero."]
  }
}
```

---

# 36. Authorization Error

HTTP:

```text
403 Forbidden
```

Response:

```json
{
  "message": "You are not authorized to perform this action."
}
```

Authorization harus diperiksa berdasarkan:

```text
User
 ↓
Role
 ↓
Permission
 ↓
Scope
 ↓
Resource
```

---

# 37. Not Found

HTTP:

```text
404 Not Found
```

Response:

```json
{
  "message": "Resource not found."
}
```

API tidak boleh memberikan informasi sensitif mengenai resource yang tidak dapat diakses user.

---

# 38. Business Rule Error

HTTP:

```text
409 Conflict
```

Contoh:

```json
{
  "message": "Insufficient stock."
}
```

atau:

```json
{
  "message": "Transaction cannot be executed before approval."
}
```

---

# 39. Server Error

HTTP:

```text
500 Internal Server Error
```

Response kepada client tidak boleh membocorkan:

- SQL query
- Stack trace
- Password
- Internal credentials
- Sensitive system information

Detail error dicatat melalui application logging.

---

# 40. API Security

API wajib menerapkan:

```text
Authentication
     ↓
Rate Limiting
     ↓
Validation
     ↓
Authorization
     ↓
Scope Validation
     ↓
Business Logic
```

Sensitive endpoint seperti:

```text
Approval
Stock Adjustment
Stock Out
User Management
Role Management
Audit
```

harus memiliki authorization yang ketat.

---

# 41. Inventory API Safety

API tidak boleh menyediakan operasi:

```http
PUT /stock/{id}
PATCH /stock/{id}
DELETE /stock/{id}
```

untuk mengubah quantity secara langsung.

Perubahan harus melalui:

```text
Stock In
Stock Out
Transfer
Adjustment
Return
```

Kemudian:

```text
Transaction
 ↓
Ledger
 ↓
Stock Balance
```

---

# 42. API & Web Business Logic

Web:

```text
Vue
 ↓
Inertia
 ↓
Laravel
```

API:

```text
External Client
 ↓
REST API
 ↓
Laravel
```

Keduanya harus menggunakan business rules yang sama.

```text
             Business Logic
              /          \
             /            \
          Web              API
```

Tidak boleh terdapat aturan:

```text
Web → Stock Out membutuhkan approval

API → Stock Out langsung mengubah stock
```

API harus mengikuti aturan yang sama.

---

# 43. API Versioning

V1:

```text
/api/v1/
```

Jika terjadi breaking change:

```text
/api/v2/
```

V1 tidak langsung diubah secara breaking hanya untuk memenuhi kebutuhan client baru.

---

# 44. API Documentation

Endpoint final akan didokumentasikan menggunakan OpenAPI/Swagger pada tahap implementation.

Dokumentasi API harus mencakup:

- Endpoint
- HTTP Method
- Authentication
- Permission
- Scope
- Request
- Response
- Validation
- Error
- Example

---

# 45. API Design Summary

```text
Client
  ↓
Authentication
  ↓
Validation
  ↓
Authorization
  ↓
Scope
  ↓
Business Logic
  ↓
Database Transaction
  ↓
Response
```

Untuk inventory:

```text
API Request
     ↓
Inventory Transaction
     ↓
Approval (if required)
     ↓
Execution
     ↓
Inventory Ledger
     ↓
Stock Balance
     ↓
Transaction History
     ↓
Audit Log
```

API Inventra bukan sekadar CRUD database.

API merupakan **interface terhadap business logic Inventra** dan harus mengikuti seluruh aturan inventory, approval, authorization, scope, ledger, dan audit.

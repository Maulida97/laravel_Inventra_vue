# Inventra

## Permission Matrix

**Document:** Permission Matrix
**Version:** V1.0
**Status:** Draft

---

# 1. Authorization Model

Inventra menggunakan tiga lapisan authorization:

```text
User
  ↓
Role
  ↓
Permission
  ↓
Scope
```

Permission menentukan **apa yang boleh dilakukan**.

Scope menentukan **terhadap data/resource mana user boleh melakukannya**.

Contoh:

```text
IT Staff
  ↓
Permission: purchase_request.create
  ↓
Scope: IT Department
  ↓
Hanya dapat membuat PR sesuai aturan IT
```

---

# 2. Permission Levels

Permission menggunakan pola:

```text
module.action
```

Contoh:

```text
item.view
item.create
item.update

stock.view
stock.in
stock.out

purchase_request.create
purchase_request.approve
```

---

# 3. Roles

Role utama Inventra:

| Role               | Fungsi                       |
| ------------------ | ---------------------------- |
| System Admin       | Administrasi sistem          |
| Warehouse Staff    | Operasional warehouse        |
| Warehouse Manager  | Kontrol & approval warehouse |
| Department Staff   | Membuat request department   |
| Department Manager | Approval department          |
| Procurement        | Procurement & PO             |
| Asset Manager      | Pengelolaan asset            |
| Management         | Monitoring & reporting       |

Role dapat dikembangkan tanpa mengubah struktur permission.

---

# 4. System Admin

System Admin memiliki akses administratif penuh terhadap konfigurasi sistem.

| Module            | View | Create | Update | Delete/Deactivate | Approve |
| ----------------- | :--: | :----: | :----: | :---------------: | :-----: |
| Users             |  ✓   |   ✓    |   ✓    |         ✓         |    -    |
| Roles             |  ✓   |   ✓    |   ✓    |         ✓         |    -    |
| Permissions       |  ✓   |   -    |   -    |         -         |    -    |
| Departments       |  ✓   |   ✓    |   ✓    |         ✓         |    -    |
| Warehouses        |  ✓   |   ✓    |   ✓    |         ✓         |    -    |
| Locations         |  ✓   |   ✓    |   ✓    |         ✓         |    -    |
| Items             |  ✓   |   ✓    |   ✓    |         ✓         |    -    |
| Categories        |  ✓   |   ✓    |   ✓    |         ✓         |    -    |
| Units             |  ✓   |   ✓    |   ✓    |         ✓         |    -    |
| Suppliers         |  ✓   |   ✓    |   ✓    |         ✓         |    -    |
| Inventory         |  ✓   |   -    |   -    |         -         |    ✓    |
| Assets            |  ✓   |   ✓    |   ✓    |         ✓         |    -    |
| Approval Workflow |  ✓   |   ✓    |   ✓    |         ✓         |    -    |
| Audit Log         |  ✓   |   -    |   -    |         -         |    -    |
| Reports           |  ✓   |   -    |   -    |         -         |    -    |
| Export            |  ✓   |   -    |   -    |         -         |    -    |

System Admin tidak boleh mengubah Inventory Ledger secara manual.

---

# 5. Warehouse Staff

Warehouse Staff bertanggung jawab terhadap operasional fisik warehouse.

| Permission                   | Access  |
| ---------------------------- | :-----: |
| `item.view`                  |    ✓    |
| `stock.view`                 |    ✓    |
| `stock.in`                   |    ✓    |
| `stock.out`                  |    ✓    |
| `stock.transfer`             |    ✓    |
| `stock.opname`               |    ✓    |
| `stock.adjust`               | Limited |
| `inventory_transaction.view` |    ✓    |
| `inventory_ledger.view`      |    ✓    |
| `asset.view`                 |    ✓    |
| `asset.register`             | Limited |
| `report.stock`               |    ✓    |

Warehouse Staff hanya dapat mengakses warehouse yang termasuk dalam **Warehouse Scope**.

---

# 6. Warehouse Manager

Warehouse Manager memiliki kontrol lebih tinggi terhadap warehouse.

| Permission                   | Access |
| ---------------------------- | :----: |
| `stock.view`                 |   ✓    |
| `stock.in`                   |   ✓    |
| `stock.out`                  |   ✓    |
| `stock.transfer`             |   ✓    |
| `stock.opname`               |   ✓    |
| `stock.adjust`               |   ✓    |
| `inventory_transaction.view` |   ✓    |
| `inventory_ledger.view`      |   ✓    |
| `approval.view`              |   ✓    |
| `approval.approve`           |   ✓    |
| `approval.reject`            |   ✓    |
| `report.stock`               |   ✓    |
| `report.stock_movement`      |   ✓    |
| `export.stock`               |   ✓    |

Scope tetap berlaku.

Warehouse Manager yang hanya memiliki akses:

```text
WH-001
```

tidak otomatis dapat mengakses:

```text
WH-002
```

---

# 7. Department Staff

Department Staff merupakan requester.

Permission utama:

| Permission                |   Access    |
| ------------------------- | :---------: |
| `item.view`               |      ✓      |
| `purchase_request.view`   |      ✓      |
| `purchase_request.create` |      ✓      |
| `purchase_request.update` |   Limited   |
| `purchase_request.submit` |      ✓      |
| `purchase_request.cancel` | Own Request |
| `stock.view`              |   Limited   |
| `asset.view`              |   Limited   |
| `asset.request`           |      ✓      |

Department Staff tidak memiliki permission:

```text
stock.in
stock.out
stock.adjust
stock.transfer
```

karena perubahan inventory dilakukan oleh warehouse.

---

# 8. Department Staff Item Restriction

Ini merupakan business rule penting Inventra.

Department Staff **tidak dapat membuat PR untuk semua item secara otomatis**.

Akses item untuk PR mengikuti konfigurasi department.

Contoh:

```text
Department IT
 ├── Laptop
 ├── Mouse
 ├── Keyboard
 └── Network Cable
```

Sedangkan:

```text
Department QC
 ├── Hydraulic Oil
 ├── Chemical
 └── Measuring Tool
```

Maka:

```text
IT Staff
 ↓
PR
 ↓
Laptop ✓
Hydraulic Oil ✗
```

dan:

```text
QC Staff
 ↓
PR
 ↓
Hydraulic Oil ✓
Laptop ✗
```

Rule ini harus divalidasi di backend.

UI hanya menyembunyikan item yang tidak tersedia; backend tetap melakukan authorization check.

---

# 9. Department Manager

Department Manager melakukan approval request dari department.

| Permission                 | Access  |
| -------------------------- | :-----: |
| `purchase_request.view`    |    ✓    |
| `purchase_request.create`  |    ✓    |
| `purchase_request.approve` |    ✓    |
| `purchase_request.reject`  |    ✓    |
| `asset.view`               | Limited |
| `report.department`        |    ✓    |

Scope:

```text
Department Manager
        ↓
Department Scope
        ↓
Own Department
```

Department Manager tidak otomatis dapat approve PR department lain.

---

# 10. Procurement

Procurement menangani proses pembelian.

| Permission              | Access |
| ----------------------- | :----: |
| `purchase_request.view` |   ✓    |
| `purchase_order.view`   |   ✓    |
| `purchase_order.create` |   ✓    |
| `purchase_order.update` |   ✓    |
| `purchase_order.submit` |   ✓    |
| `supplier.view`         |   ✓    |
| `supplier.create`       |   ✓    |
| `supplier.update`       |   ✓    |
| `receiving.view`        |   ✓    |
| `report.procurement`    |   ✓    |
| `export.procurement`    |   ✓    |

Procurement tidak otomatis memiliki permission untuk melakukan Stock Adjustment.

---

# 11. Asset Manager

Asset Manager mengelola lifecycle asset.

| Permission           | Access  |
| -------------------- | :-----: |
| `asset.view`         |    ✓    |
| `asset.register`     |    ✓    |
| `asset.update`       |    ✓    |
| `asset.assign`       |    ✓    |
| `asset.return`       |    ✓    |
| `asset.maintenance`  |    ✓    |
| `asset.dispose`      | Limited |
| `asset.history.view` |    ✓    |
| `report.asset`       |    ✓    |
| `export.asset`       |    ✓    |

Asset Manager tetap mengikuti warehouse/department scope jika scope diterapkan.

---

# 12. Management

Management memiliki akses monitoring dan reporting.

| Module       |  View   | Create | Update | Approve |
| ------------ | :-----: | :----: | :----: | :-----: |
| Dashboard    |    ✓    |   -    |   -    |    -    |
| Stock        |    ✓    |   -    |   -    |    -    |
| Transactions |    ✓    |   -    |   -    |    -    |
| Assets       |    ✓    |   -    |   -    |    -    |
| Procurement  |    ✓    |   -    |   -    |    -    |
| Reports      |    ✓    |   -    |   -    |    -    |
| Export       |    ✓    |   -    |   -    |    -    |
| Audit Log    | Limited |   -    |   -    |    -    |

Management tidak boleh mengubah data operasional hanya karena memiliki akses read/reporting.

---

# 13. Permission Matrix — Inventory

| Action       | Admin | WH Staff | WH Manager | Dept Staff | Dept Manager | Procurement | Asset Manager | Management |
| ------------ | :---: | :------: | :--------: | :--------: | :----------: | :---------: | :-----------: | :--------: |
| View Stock   |   ✓   |    ✓     |     ✓      |  Limited   |   Limited    |   Limited   |       ✓       |     ✓      |
| Stock In     |   ✓   |    ✓     |     ✓      |     ✗      |      ✗       |      ✗      |    Limited    |     ✗      |
| Stock Out    |   ✓   |    ✓     |     ✓      |     ✗      |      ✗       |      ✗      |    Limited    |     ✗      |
| Transfer     |   ✓   |    ✓     |     ✓      |     ✗      |      ✗       |      ✗      |       ✗       |     ✗      |
| Adjustment   |   ✓   | Limited  |     ✓      |     ✗      |      ✗       |      ✗      |       ✗       |     ✗      |
| Stock Opname |   ✓   |    ✓     |     ✓      |     ✗      |      ✗       |      ✗      |       ✗       |    View    |
| View Ledger  |   ✓   |    ✓     |     ✓      |  Limited   |   Limited    |   Limited   |       ✓       |     ✓      |

---

# 14. Permission Matrix — Procurement

| Action           | Admin | WH Staff | WH Manager | Dept Staff | Dept Manager | Procurement | Management |
| ---------------- | :---: | :------: | :--------: | :--------: | :----------: | :---------: | :--------: |
| Create PR        |   ✓   | Limited  |  Limited   |     ✓      |      ✓       |      ✓      |     ✗      |
| Submit PR        |   ✓   | Limited  |  Limited   |     ✓      |      ✓       |      ✓      |     ✗      |
| Approve PR       |   ✓   |    ✗     |     ✗      |     ✗      |      ✓       |   Limited   |  Limited   |
| Create PO        |   ✓   |    ✗     |     ✗      |     ✗      |      ✗       |      ✓      |     ✗      |
| Create Receiving |   ✓   |    ✓     |     ✓      |     ✗      |      ✗       |      ✓      |     ✗      |
| View Supplier    |   ✓   | Limited  |     ✓      |     ✗      |      ✗       |      ✓      |     ✓      |

---

# 15. Approval Separation

Inventra menggunakan prinsip **Separation of Duties**.

Contoh:

```text
Requester
    ↓
Department Staff
    ↓
Approval
    ↓
Department Manager
    ↓
Procurement
    ↓
Warehouse Receiving
```

User tidak boleh secara otomatis melakukan seluruh proses hanya karena memiliki beberapa permission, kecuali secara eksplisit diberikan oleh administrator.

---

# 16. Self Approval

Secara default:

```text
Requester ≠ Approver
```

User yang membuat request tidak dapat approve request miliknya sendiri.

Contoh:

```text
Budi
 ↓
Create PR-000123
 ↓
Budi approve PR-000123
 ✗
```

Approval harus dilakukan oleh approver lain yang memenuhi workflow.

---

# 17. Scope Matrix

Permission menentukan action.

Scope menentukan resource.

### Department Scope

Digunakan untuk:

```text
Purchase Request
Department Data
Department Reports
Department Approval
```

### Warehouse Scope

Digunakan untuk:

```text
Stock
Stock In
Stock Out
Transfer
Stock Opname
Warehouse Report
```

### Location Scope

Digunakan ketika akses perlu dibatasi sampai lokasi tertentu.

```text
Warehouse
 ├── Rack A
 ├── Rack B
 └── Rack C
```

User dengan Location Scope:

```text
Rack A
```

tidak dapat melakukan transaksi terhadap:

```text
Rack B
```

---

# 18. Scope Enforcement

Scope harus diperiksa pada backend.

Contoh request:

```http
POST /api/v1/stock-out
```

User:

```text
Role:
Warehouse Staff

Permission:
stock.out

Warehouse Scope:
WH-001
```

Request:

```text
warehouse_id = WH-002
```

Result:

```text
403 Forbidden
```

Meskipun user memiliki permission `stock.out`.

---

# 19. UI vs Backend Authorization

UI:

```text
Hide button
```

Backend:

```text
Actually enforce permission
```

Contoh:

```text
User tidak memiliki stock.adjust
       ↓
UI tidak menampilkan tombol Adjustment
```

Tetapi jika user mencoba:

```http
POST /api/v1/stock-adjustments
```

backend tetap harus menolak.

---

# 20. Sensitive Permissions

Permission berikut dianggap high-risk:

```text
user.delete
role.update
permission.assign

stock.adjust
stock.out
stock.transfer

approval.approve
approval.reject

asset.dispose

audit_log.view
```

Permission tersebut harus diberikan secara terbatas.

---

# 21. Audit Permission

Aktivitas berikut harus menghasilkan Audit Log:

```text
Create
Update
Deactivate
Approve
Reject
Stock In
Stock Out
Transfer
Adjustment
Stock Opname
Asset Assignment
Asset Return
Asset Disposal
Role Assignment
Permission Assignment
```

---

# 22. Permission Naming Convention

Format:

```text
module.action
```

Contoh:

```text
user.view
user.create
user.update
user.deactivate

item.view
item.create
item.update

warehouse.view
warehouse.create
warehouse.update

stock.view
stock.in
stock.out
stock.transfer
stock.adjust

stock_opname.view
stock_opname.create
stock_opname.count
stock_opname.complete

asset.view
asset.register
asset.update
asset.assign
asset.return
asset.dispose

approval.view
approval.approve
approval.reject

report.stock
report.asset
report.procurement

export.stock
export.asset
```

---

# 23. Default Deny

Inventra menggunakan prinsip:

```text
Tidak punya permission
        ↓
DENY
```

Bukan:

```text
Tidak dilarang
        ↓
ALLOW
```

Authorization harus bersifat explicit.

---

# 24. Permission Evaluation

Authorization flow:

```text
Request
   ↓
Authenticated?
   ├── No → 401
   └── Yes
        ↓
Has Permission?
   ├── No → 403
   └── Yes
        ↓
Scope Valid?
   ├── No → 403
   └── Yes
        ↓
Business Rule Valid?
   ├── No → Reject
   └── Yes
        ↓
Execute
```

---

# 25. Final Authorization Model

```text
                 USER
                   │
                   ▼
                  ROLE
                   │
                   ▼
              PERMISSION
                   │
                   ▼
                 SCOPE
                   │
                   ▼
            BUSINESS RULE
                   │
                   ▼
                ACTION
```

Contoh lengkap:

```text
IT Staff
   ↓
Department Staff
   ↓
purchase_request.create
   ↓
Department Scope = IT
   ↓
Item allowed for IT?
   ↓
YES
   ↓
Create PR
```

Jika:

```text
Item = Hydraulic Oil
Allowed Department = QC
```

maka:

```text
IT Staff
 ↓
purchase_request.create ✓
 ↓
Hydraulic Oil ✗
 ↓
403 Forbidden
```

---

# 26. Permission Matrix Principle

Inventra tidak menggunakan:

```text
Role = akses penuh
```

Tetapi:

```text
Role
 +
Permission
 +
Scope
 +
Business Rule
 =
Actual Access
```

Hal ini memungkinkan sistem memiliki role yang fleksibel tanpa memberikan akses berlebihan.

---

# 27. Security Principle

Authorization harus dilakukan:

```text
Server-side
```

dan bukan hanya:

```text
Frontend
```

Frontend digunakan untuk UX.

Backend merupakan sumber kebenaran authorization.

---

# 28. Future Extension

Model ini dapat dikembangkan menjadi:

```text
Role
 ├── Permissions
 └── Scopes

Department
 └── Allowed Items

Warehouse
 └── Allowed Locations

Approval Workflow
 └── Approver Rules
```

Tanpa perlu mengubah konsep dasar authorization.

---

# 29. Completion Criteria

Permission Matrix dianggap selesai apabila:

- Semua role utama memiliki permission yang jelas.
- Semua module memiliki authorization.
- Department scope telah didefinisikan.
- Warehouse scope telah didefinisikan.
- Item restriction untuk Department Staff telah didefinisikan.
- Self-approval telah dicegah.
- High-risk permission telah diidentifikasi.
- Backend enforcement menjadi mandatory.
- Default access adalah deny.
- Audit untuk aktivitas sensitif telah ditentukan.

---

# 30. Authorization Summary

```text
User
 ↓
Authentication
 ↓
Role
 ↓
Permission
 ↓
Scope
 ↓
Business Rule
 ↓
Allowed?
 ├── No → 403
 └── Yes
       ↓
     Action
       ↓
   Audit Log
```

Dengan model ini, Inventra dapat menerapkan kontrol seperti:

> **"Staff IT boleh membuat PR, tetapi hanya untuk item yang diperbolehkan untuk Department IT."**

dan:

> **"Warehouse Staff boleh melakukan Stock Out, tetapi hanya dari warehouse/location yang menjadi scope-nya."**

Serta:

> **"User yang membuat transaksi tidak dapat menyetujui transaksi tersebut sendiri."**

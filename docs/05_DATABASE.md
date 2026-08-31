# Inventra

## Database Design

**Document:** Database Design
**Version:** V1.0
**Status:** Draft
**Database:** PostgreSQL

---

# 1. Database Principles

Database Inventra dirancang dengan prinsip:

1. Stock tidak diubah secara langsung oleh user.
2. Perubahan stock berasal dari inventory transaction.
3. Inventory Ledger menyimpan histori perubahan stock.
4. Stock Balance menyimpan kondisi stock saat ini.
5. Setiap stock dapat ditelusuri ke warehouse dan location.
6. Transaction memiliki reference number yang unik.
7. Data approval dan audit disimpan secara terpisah.
8. Master data tidak boleh merusak histori transaksi.
9. Foreign key digunakan untuk menjaga referential integrity.
10. Operasi inventory penting menggunakan database transaction.

---

# 2. High-Level Entity

```text
Company
  │
  ├── Departments
  │
  └── Warehouses
          │
          └── Locations

Users
  │
  ├── Roles
  │     └── Permissions
  │
  └── Scopes

Items
  │
  ├── Categories
  ├── Units
  └── Item Brands

Inventory
  │
  ├── Stock Balances
  ├── Inventory Transactions
  └── Inventory Ledger

Procurement
  │
  ├── Purchase Requests
  ├── Purchase Orders
  └── Receivings

Assets
  │
  └── Asset Histories

Approval
  │
  ├── Workflows
  └── Approval Records

Audit
  └── Audit Logs
```

---

# 3. Company

### Table

```text
companies
```

### Purpose

Menyimpan identitas perusahaan.

### Main Fields

```text
id
code
name
description
is_active
created_at
updated_at
```

V1 hanya menggunakan satu company, tetapi struktur tetap menggunakan entity `Company` agar dapat dikembangkan ke multi-company pada V2.

---

# 4. Departments

### Table

```text
departments
```

### Main Fields

```text
id
company_id
code
name
description
is_active
created_at
updated_at
```

### Relationship

```text
Company
  └── hasMany Departments
```

Contoh:

```text
IT
QC
Finance
HR
Procurement
```

---

# 5. Users

### Table

```text
users
```

### Main Fields

```text
id
name
email
password
department_id
is_active
created_at
updated_at
```

User memiliki department utama.

Relationship:

```text
Department
    └── hasMany Users
```

User dapat memiliki lebih dari satu role.

---

# 6. Roles

### Table

```text
roles
```

### Main Fields

```text
id
name
code
description
is_active
created_at
updated_at
```

Contoh:

```text
system_admin
warehouse_staff
warehouse_manager
department_staff
department_manager
procurement
asset_manager
management
```

---

# 7. Permissions

### Table

```text
permissions
```

### Main Fields

```text
id
name
code
module
description
```

Contoh:

```text
item.view
item.create
item.update

stock.view
stock.in
stock.out
stock.adjust
stock.transfer

asset.view
asset.assign

approval.view
approval.approve
approval.reject
```

---

# 8. User Roles

### Table

```text
role_user
```

### Relationship

```text
User
  ↕
Role
```

Many-to-many.

Satu user dapat memiliki beberapa role.

Contoh:

```text
Budi
 ├── Warehouse Staff
 └── Stock Opname Operator
```

---

# 9. Role Permissions

### Table

```text
permission_role
```

### Relationship

```text
Role
  ↕
Permission
```

Role menentukan permission yang dimiliki user.

---

# 10. Access Scope

Scope digunakan untuk membatasi **data yang dapat diakses**, bukan hanya action.

### Scope Types

```text
Department
Warehouse
Location
```

### Tables

```text
user_departments
user_warehouses
user_locations
```

atau dapat dikembangkan menjadi generic scope model apabila diperlukan saat implementation.

Contoh:

```text
User:
Budi

Role:
Warehouse Manager

Permission:
stock.approve

Warehouse Scope:
WH-001
```

Budi tidak otomatis memiliki akses ke warehouse lain.

---

# 11. Categories

### Table

```text
categories
```

### Main Fields

```text
id
parent_id
code
name
description
is_active
created_at
updated_at
```

Category mendukung hierarchy.

Contoh:

```text
Mechanical
 ├── Lubricant
 ├── Hydraulic
 └── Sparepart
```

---

# 12. Units

### Table

```text
units
```

### Main Fields

```text
id
code
name
type
description
```

Contoh:

```text
PCS
LITER
ML
KG
BOX
DRUM
PACK
```

Unit hanya menjadi referensi satuan.

Inventra **tidak menggunakan global unit conversion table** untuk menentukan isi setiap kemasan.

---

# 13. Items

### Table

```text
items
```

### Main Fields

```text
id
category_id
code
name
description
brand
item_type
base_unit_id
minimum_stock
is_active
created_at
updated_at
```

### Item Type

```text
quantity
asset
```

Contoh:

```text
Hydraulic Oil
item_type = quantity
base_unit = LITER
```

```text
Laptop Lenovo
item_type = asset
base_unit = PCS
```

---

# 14. Item Content

Karena isi kemasan dapat berbeda berdasarkan barang/brand, Inventra menggunakan data content yang dapat ditentukan pada konteks transaksi.

### Concept

```text
Transaction Unit
Content per Unit
Equivalent Quantity
```

Contoh:

```text
Item:
Paku Brand A

Transaction Unit:
BOX

Quantity:
6

Content per Unit:
100 PCS

Equivalent:
600 PCS
```

Tidak boleh diasumsikan:

```text
1 BOX = 100 PCS
```

untuk seluruh item.

Nilai `content_per_unit` yang digunakan dalam transaksi harus tersimpan sebagai bagian dari histori transaksi agar data lama tetap dapat ditelusuri meskipun informasi master berubah.

---

# 15. Warehouses

### Table

```text
warehouses
```

### Main Fields

```text
id
company_id
code
name
description
is_active
created_at
updated_at
```

Contoh:

```text
WH-001
Main Warehouse
```

---

# 16. Locations

### Table

```text
locations
```

### Main Fields

```text
id
warehouse_id
parent_id
code
name
location_type
is_active
created_at
updated_at
```

Location menggunakan hierarchy.

Contoh:

```text
Warehouse
└── Rack A
    └── Shelf 01
```

atau cukup:

```text
Warehouse
└── Rack A
```

Tidak semua warehouse wajib menggunakan seluruh level.

---

# 17. Stock Location Rule

Untuk V1, satu kombinasi:

```text
Item + Warehouse
```

memiliki **satu primary physical location** untuk memudahkan Stock Opname dan pengecekan fisik.

Contoh:

```text
Hydraulic Oil
Warehouse: WH-001
Location: RACK-A
```

Bukan:

```text
Hydraulic Oil
WH-001 / Rack-A
WH-001 / Rack-B
WH-001 / Rack-C
```

dalam kondisi normal.

Jika kebutuhan bisnis berkembang menjadi satu item tersebar di beberapa lokasi dalam satu warehouse, model ini dapat dikembangkan pada versi berikutnya.

---

# 18. Stock Balances

### Table

```text
stock_balances
```

### Purpose

Menyimpan kondisi stock terkini untuk akses operasional yang cepat.

### Main Fields

```text
id
item_id
warehouse_id
location_id
quantity
reserved_quantity
available_quantity
updated_at
```

### Concept

```text
available_quantity =
quantity - reserved_quantity
```

Stock balance bukan histori.

Histori perubahan disimpan di Inventory Ledger.

---

# 19. Inventory Transactions

### Table

```text
inventory_transactions
```

### Purpose

Menyimpan transaksi inventory.

### Main Fields

```text
id
reference_number
transaction_type
status
item_id
warehouse_id
location_id
quantity
transaction_unit_id
content_per_unit
equivalent_quantity
reference_type
reference_id
performed_by
transaction_date
notes
created_at
updated_at
```

### Transaction Types

```text
opening_balance
stock_in
stock_out
transfer
adjustment
return
```

### Status

```text
draft
pending
approved
rejected
completed
cancelled
```

`reference_number` harus unik.

Contoh:

```text
SI-000001
SO-000001
TRF-000001
ADJ-000001
RET-000001
```

---

# 20. Inventory Ledger

### Table

```text
inventory_ledger
```

### Purpose

Menyimpan histori perubahan quantity.

### Main Fields

```text
id
inventory_transaction_id
item_id
warehouse_id
location_id
quantity_before
quantity_change
quantity_after
transaction_type
reference_number
created_at
```

### Example

```text
Opening Balance
Before: 0
Change: +500
After: 500

Stock In
Before: 500
Change: +200
After: 700

Stock Out
Before: 700
Change: -100
After: 600
```

Ledger digunakan untuk tracing perubahan stock.

---

# 21. Inventory Transaction vs Ledger

Keduanya tidak digabung.

```text
Inventory Transaction
→ Apa transaksi yang terjadi?

Inventory Ledger
→ Bagaimana quantity berubah?
```

Contoh:

```text
SO-000123
Stock Out
100 Liter
WH-001
Rack-A
User: Budi
```

Ledger:

```text
Before: 500
Change: -100
After: 400
```

---

# 22. Purchase Requests

### Table

```text
purchase_requests
```

### Main Fields

```text
id
reference_number
department_id
requested_by
status
required_date
reason
created_at
updated_at
```

### PR Items

```text
purchase_request_items
```

Fields:

```text
id
purchase_request_id
item_id
quantity
unit_id
notes
```

---

# 23. Purchase Orders

### Table

```text
purchase_orders
```

### Main Fields

```text
id
reference_number
purchase_request_id
supplier_id
status
ordered_by
order_date
expected_date
notes
created_at
updated_at
```

### PO Items

```text
purchase_order_items
```

Fields:

```text
id
purchase_order_id
item_id
quantity
unit_id
unit_price
notes
```

---

# 24. Suppliers

### Table

```text
suppliers
```

### Main Fields

```text
id
code
name
contact
address
is_active
created_at
updated_at
```

Supplier menjadi master data untuk Procurement.

---

# 25. Receivings

### Table

```text
receivings
```

### Main Fields

```text
id
reference_number
purchase_order_id
warehouse_id
received_by
status
received_at
notes
created_at
updated_at
```

### Receiving Items

```text
receiving_items
```

Fields:

```text
id
receiving_id
purchase_order_item_id
item_id
quantity
unit_id
content_per_unit
equivalent_quantity
condition
notes
```

Receiving yang telah dikonfirmasi menghasilkan Stock In.

---

# 26. Stock Opname

### Header

```text
stock_opnames
```

Fields:

```text
id
reference_number
warehouse_id
location_id
status
started_by
completed_by
started_at
completed_at
notes
```

### Items

```text
stock_opname_items
```

Fields:

```text
id
stock_opname_id
item_id
system_quantity
physical_quantity
difference
notes
```

### Flow

```text
System Quantity
      ↓
Physical Count
      ↓
Difference
```

Difference tidak langsung mengubah stock.

---

# 27. Assets

### Table

```text
assets
```

### Main Fields

```text
id
item_id
asset_tag
serial_number
warehouse_id
location_id
department_id
custodian_id
status
purchase_date
condition
notes
created_at
updated_at
```

### Asset Status

```text
available
assigned
returned
maintenance
disposed
```

`asset_tag` harus unik.

Serial number dapat diberi unique constraint apabila business rule item mengharuskannya.

---

# 28. Asset History

### Table

```text
asset_histories
```

### Main Fields

```text
id
asset_id
action
from_location_id
to_location_id
from_department_id
to_department_id
from_custodian_id
to_custodian_id
from_status
to_status
performed_by
notes
created_at
```

Digunakan untuk tracing lifecycle asset.

---

# 29. Approval Workflow

### Workflow

```text
approval_workflows
```

Fields:

```text
id
name
code
module
is_active
created_at
updated_at
```

### Steps

```text
approval_workflow_steps
```

Fields:

```text
id
workflow_id
step_order
name
approver_type
required
```

### Approval Instance

```text
approval_requests
```

Fields:

```text
id
workflow_id
reference_type
reference_id
status
current_step
created_at
updated_at
```

### Approval Actions

```text
approval_actions
```

Fields:

```text
id
approval_request_id
step_id
approver_id
action
notes
acted_at
```

---

# 30. Approval Relationship

```text
Transaction
     ↓
Approval Request
     ↓
Workflow
     ↓
Workflow Steps
     ↓
Approval Actions
```

Contoh:

```text
Stock Adjustment
      ↓
Approval Request
      ↓
Department Manager
      ↓
Warehouse Manager
      ↓
Approved
```

Approval history tidak boleh ditimpa.

---

# 31. Audit Logs

### Table

```text
audit_logs
```

### Main Fields

```text
id
user_id
action
auditable_type
auditable_id
reference_number
old_values
new_values
ip_address
user_agent
created_at
```

### Purpose

Menjawab:

```text
Who?
What?
Which Entity?
When?
What Changed?
```

`old_values` dan `new_values` dapat menggunakan PostgreSQL `JSONB`.

---

# 32. Audit Log vs Transaction History vs Ledger

Ketiganya memiliki fungsi berbeda.

```text
Transaction History
→ Histori transaksi bisnis

Inventory Ledger
→ Histori perubahan stock

Audit Log
→ Histori aktivitas/perubahan data oleh user
```

Contoh:

```text
Budi
 ↓
Approve SO-000123
 ↓
Transaction History
 ↓
Inventory Ledger
 ↓
Audit Log
```

---

# 33. Reference Number

Entity yang memiliki transaksi harus memiliki nomor referensi.

Contoh:

```text
PR-000001
PO-000001
GR-000001
SI-000001
SO-000001
TRF-000001
ADJ-000001
RET-000001
SO-000001
AST-000001
```

Reference number digunakan sebagai identifier operasional untuk tracing.

Format final dapat ditentukan pada implementation.

---

# 34. Important Relationships

```text
Company
 ├── Departments
 │      └── Users
 │
 └── Warehouses
        └── Locations
```

```text
Item
 ├── Category
 ├── Base Unit
 └── Stock Balance
        └── Warehouse
             └── Location
```

```text
Inventory Transaction
 ├── Item
 ├── Warehouse
 ├── Location
 ├── User
 ├── Approval
 └── Inventory Ledger
```

```text
Purchase Request
 └── Purchase Request Items
        ↓
Purchase Order
 └── Purchase Order Items
        ↓
Receiving
 └── Receiving Items
        ↓
Stock In
```

```text
Asset
 ├── Item
 ├── Location
 ├── Department
 ├── Custodian
 └── Asset History
```

---

# 35. Core Inventory Relationship

```text
                    Item
                     │
                     ▼
              Stock Balance
                     │
          ┌──────────┴──────────┐
          ▼                     ▼
      Warehouse              Location
          │
          ▼
   Inventory Transaction
          │
          ▼
   Inventory Ledger
```

Current stock:

```text
Stock Balance
```

Stock history:

```text
Inventory Ledger
```

Transaction details:

```text
Inventory Transaction
```

---

# 36. Data Integrity Rules

### Stock

`stock_balances.quantity` tidak boleh berubah melalui CRUD biasa.

Perubahan harus berasal dari inventory transaction.

### Transaction

Completed transaction tidak boleh diubah sembarangan.

Jika terjadi koreksi, gunakan transaction baru seperti:

```text
Adjustment
Return
```

### Ledger

Ledger bersifat append-oriented.

Record historis tidak boleh diedit melalui operasi normal.

### Approval

Approval action tidak boleh dihapus untuk menghilangkan histori.

### Audit

Audit log tidak boleh dihapus oleh user operasional.

### Asset

Asset history tidak boleh ditimpa oleh update asset.

---

# 37. Database Transaction Boundary

Operasi inventory yang mengubah beberapa entity harus berada dalam satu database transaction.

Contoh Stock Out:

```text
BEGIN
   ↓
Validate Stock
   ↓
Create Inventory Transaction
   ↓
Create Ledger
   ↓
Update Stock Balance
   ↓
Create Audit Log
   ↓
COMMIT
```

Jika salah satu proses gagal:

```text
ROLLBACK
```

Tidak boleh terjadi kondisi seperti:

```text
Ledger berhasil
Stock Balance gagal
```

atau:

```text
Stock Balance berubah
Transaction gagal
```

---

# 38. Indexing Strategy

Index utama akan diberikan pada field yang sering digunakan untuk:

- Lookup
- Filtering
- Foreign key
- Reference number
- Date range
- Transaction history
- Stock lookup

Contoh:

```text
inventory_transactions.reference_number
inventory_transactions.item_id
inventory_transactions.warehouse_id
inventory_transactions.transaction_date

inventory_ledger.item_id
inventory_ledger.warehouse_id
inventory_ledger.created_at

stock_balances.item_id
stock_balances.warehouse_id
stock_balances.location_id
```

Index final akan ditentukan setelah query pattern diketahui.

---

# 39. Soft Delete Strategy

Master data yang sudah digunakan dalam transaksi tidak langsung dihapus.

Contoh:

```text
Item
Supplier
Warehouse
Location
Department
```

Jika tidak digunakan lagi:

```text
is_active = false
```

atau soft delete sesuai kebutuhan entity.

Tujuannya menjaga referential integrity dan histori.

Transaction dan ledger tidak menggunakan hard delete sebagai operasi normal.

---

# 40. Database Design Principles Summary

```text
Master Data
     ↓
Operational Transaction
     ↓
Inventory Ledger
     ↓
Stock Balance
```

Dengan tracing:

```text
User
 ↓
Transaction
 ↓
Reference Number
 ↓
Warehouse
 ↓
Location
 ↓
Item
 ↓
Stock Change
 ↓
Ledger
 ↓
Audit Log
```

Dengan demikian Inventra dapat menjawab:

> **Barang apa yang berubah, berapa jumlahnya, berada di mana, transaksi apa yang menyebabkan perubahan, siapa yang melakukan, dan kapan perubahan tersebut terjadi.**

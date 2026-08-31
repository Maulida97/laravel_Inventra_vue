# Inventra

## Product Vision

**Document:** Product Vision
**Version:** V1.0
**Status:** Approved

---

# 1. Vision

Membangun sistem Inventory & Asset Management yang **terkontrol, mudah ditelusuri, dan dapat dikembangkan**, sehingga perusahaan dapat mengetahui kondisi barang, pergerakan stock, lokasi fisik, asset, serta aktivitas pengguna secara akurat.

Inventra tidak hanya berfungsi sebagai pencatatan stock, tetapi sebagai **central system untuk mengontrol lifecycle inventory dan asset**.

---

# 2. Problem

Pengelolaan inventory secara manual atau menggunakan sistem yang tidak terintegrasi dapat menyebabkan:

- Perbedaan antara stock sistem dan stock fisik.
- Kesulitan mengetahui riwayat keluar-masuk barang.
- Barang berada di lokasi yang sulit dilacak.
- Pengeluaran barang tanpa kontrol approval.
- Sulit mengetahui siapa yang melakukan perubahan.
- Data antar department tidak terkontrol.
- Proses Stock Opname membutuhkan waktu.
- Asset sulit dilacak berdasarkan pengguna dan lokasi.

Inventra dibuat untuk mengurangi masalah tersebut melalui **centralized inventory control dan traceability**.

---

# 3. Target Users

Inventra ditujukan untuk organisasi yang memiliki:

- Warehouse.
- Banyak jenis inventory.
- Beberapa department.
- Barang consumable maupun non-consumable.
- Asset yang membutuhkan tracking.
- Proses approval.
- Kebutuhan audit dan reporting.

Pengguna utama:

```text
Management
    │
    ├── Warehouse Manager
    ├── Department Manager
    ├── Procurement
    ├── Asset Manager
    ├── Warehouse Staff
    └── Department Staff
```

---

# 4. Core Value

### 4.1 Traceability

Setiap pergerakan inventory dapat ditelusuri.

```text
Item
 ↓
Transaction
 ↓
Warehouse
 ↓
Location
 ↓
User
 ↓
Timestamp
```

---

### 4.2 Controlled Inventory

Perubahan stock tidak dilakukan secara bebas.

```text
Request
 ↓
Validation
 ↓
Approval
 ↓
Transaction
 ↓
Ledger
```

---

### 4.3 Physical Accuracy

Inventra menghubungkan stock sistem dengan kondisi fisik melalui Stock Opname.

```text
System Stock
     ↕
Physical Count
     ↓
Difference
     ↓
Adjustment
```

---

### 4.4 Accountability

Aktivitas penting dapat diketahui:

```text
WHO
WHAT
WHEN
WHERE
```

melalui Audit Log dan Transaction History.

---

### 4.5 Maintainability

Sistem dikembangkan dengan dokumentasi yang memungkinkan developer memahami dan melakukan maintenance terhadap code tanpa harus bergantung sepenuhnya pada AI.

---

# 5. Product Principles

Inventra menggunakan prinsip berikut:

### Controlled

Transaksi inventory harus mengikuti business rules dan authorization.

### Traceable

Data harus dapat ditelusuri dari transaksi hingga perubahan stock.

### Auditable

Aktivitas penting harus memiliki jejak audit.

### Simple

Workflow dibuat sesederhana mungkin tanpa menghilangkan kontrol.

### Flexible

Struktur warehouse, location, item, dan department tidak dibuat terlalu kaku.

### Maintainable

Code, architecture, dan business logic harus terdokumentasi.

### Extensible

V1 single-company harus memiliki fondasi yang memungkinkan pengembangan menuju multi-company dan fitur lanjutan.

---

# 6. Product Direction

### V1 — Operational Inventory

```text
Single Company
      ↓
Inventory
      ↓
Warehouse
      ↓
Stock Control
      ↓
Asset
      ↓
Approval
      ↓
Reporting
```

Fokus V1 adalah membuat **operational inventory system yang solid dan traceable**.

### V2 — Scalable Platform

Arah pengembangan:

```text
Single Company
       ↓
Multi Company
       ↓
Advanced Inventory
       ↓
Mobile Application
       ↓
External Integration
```

Kemampuan V2 tidak menjadi bagian dari scope implementasi V1.

---

# 7. Success Criteria

Inventra dianggap berhasil apabila pengguna dapat:

1. Mengetahui jumlah stock berdasarkan warehouse dan lokasi.
2. Mengetahui histori pergerakan suatu item.
3. Mengetahui siapa yang melakukan transaksi.
4. Melakukan Stock Opname dan mengetahui selisihnya.
5. Mengontrol transaksi melalui approval.
6. Melacak asset berdasarkan tag, serial number, lokasi, dan custodian.
7. Menghasilkan laporan operasional.
8. Membatasi akses berdasarkan role, permission, dan scope.
9. Melakukan tracing terhadap perubahan inventory.
10. Memahami dan melakukan maintenance terhadap code melalui dokumentasi yang tersedia.

---

# 8. One-Line Product Definition

> **Inventra adalah sistem Inventory & Asset Management yang membantu perusahaan mengontrol, melacak, dan mengaudit seluruh lifecycle inventory dan asset secara terstruktur.**

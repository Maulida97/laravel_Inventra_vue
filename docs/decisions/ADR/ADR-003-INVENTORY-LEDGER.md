# ADR-003 — Inventory Ledger

**Project:** Inventra
**Status:** Accepted
**Date:** 2026-08-30

---

# 1. Context

Inventra mengelola berbagai aktivitas inventory:

```text
Stock In
Stock Out
Stock Opname
Stock Adjustment
```

Setiap aktivitas tersebut mengubah jumlah stock.

Sistem harus dapat menjawab:

```text
Berapa stock saat ini?
Kenapa jumlahnya berubah?
Siapa yang melakukan perubahan?
Kapan perubahan terjadi?
Transaksi apa yang menyebabkan perubahan?
```

Jika sistem hanya menyimpan angka stock terakhir, riwayat perubahan akan sulit ditelusuri.

Contoh:

```text
Current Stock = 80
```

Tidak cukup untuk mengetahui:

```text
100 → Stock Out 20 → 80
```

atau:

```text
70 → Adjustment +10 → 80
```

---

# 2. Problem

Pendekatan sederhana:

```text
items
└── stock_quantity
```

memiliki keterbatasan.

Jika stock berubah:

```text
stock_quantity = stock_quantity - 20
```

informasi mengenai perubahan tersebut dapat hilang jika tidak dicatat di tempat lain.

Akibatnya sulit melakukan:

```text
Audit
Stock History
Investigation
Reconciliation
Reporting
```

---

# 3. Decision

Inventra menggunakan **Inventory Ledger** untuk mencatat setiap pergerakan stock.

Konsep:

```text
Stock Movement
      ↓
Inventory Ledger
      ↓
Inventory Balance
```

Ledger menjadi catatan historis pergerakan inventory.

---

# 4. Ledger Concept

Setiap movement dicatat sebagai:

```text
IN
OUT
ADJUSTMENT
```

Contoh:

```text
Opening
+100

Stock In
+50

Stock Out
-20

Adjustment
-5

----------------
Current
125
```

Secara konseptual:

```text
Current Stock
=
Opening Balance
+
Total IN
-
Total OUT
± Adjustment
```

---

# 5. Inventory Balance

Inventra dapat memiliki tabel balance untuk membaca stock saat ini secara cepat.

Konsep:

```text
inventory_balances
```

menyimpan current state.

Sedangkan:

```text
inventory_ledger
```

menyimpan movement history.

Jadi:

```text
Ledger
   ↓
Historical Truth

Balance
   ↓
Current State
```

---

# 6. Source of Truth

Untuk **pergerakan stock**, Inventory Ledger merupakan historical source of truth.

Untuk **current stock lookup**, inventory balance digunakan sebagai current state yang harus konsisten dengan ledger.

Hubungan:

```text
Transaction
     ↓
Ledger Movement
     ↓
Inventory Balance
```

Tidak boleh ada perubahan stock yang bypass ledger untuk transaksi inventory normal.

---

# 7. Stock In

Flow:

```text
Stock In
   ↓
Approval
   ↓
Approved
   ↓
Create Ledger IN
   ↓
Increase Inventory Balance
```

Contoh:

```text
Before = 100
IN     = +50
After  = 150
```

---

# 8. Stock Out

Flow:

```text
Stock Out
   ↓
Validate Available Stock
   ↓
Approval
   ↓
Approved
   ↓
Create Ledger OUT
   ↓
Decrease Inventory Balance
```

Contoh:

```text
Before = 150
OUT    = -20
After  = 130
```

---

# 9. Stock Opname

Stock Opname berbeda dengan Stock In/Out.

Contoh:

```text
System Stock = 100
Physical     = 95
Difference   = -5
```

Maka:

```text
Opname
   ↓
Difference -5
   ↓
Adjustment Ledger
   ↓
Balance = 95
```

Adjustment harus memiliki reference ke stock opname yang menyebabkan perubahan.

---

# 10. Negative Stock

Inventra tidak mengizinkan stock menjadi negatif kecuali business rule secara eksplisit mengizinkannya di masa depan.

Contoh:

```text
Available = 10
Request   = 15
```

Result:

```text
Rejected
```

bukan:

```text
Balance = -5
```

---

# 11. Transaction Atomicity

Perubahan inventory harus atomic.

Contoh Stock Out:

```text
BEGIN
   ↓
Validate Stock
   ↓
Create Transaction
   ↓
Create Ledger
   ↓
Update Inventory Balance
   ↓
Create Audit Log
   ↓
COMMIT
```

Jika salah satu operasi critical gagal:

```text
ROLLBACK
```

Tujuannya mencegah:

```text
Ledger created
BUT
Inventory not updated
```

atau:

```text
Inventory updated
BUT
Ledger missing
```

---

# 12. Ledger Immutability

Ledger movement tidak boleh diedit secara sembarangan setelah dibuat.

Tidak diperbolehkan:

```text
Ledger
 ↓
UPDATE quantity
```

untuk memperbaiki kesalahan secara manual.

Jika terjadi kesalahan:

```text
Incorrect Movement
       ↓
Correction / Adjustment
       ↓
New Ledger Entry
```

Dengan demikian history tetap terlihat.

---

# 13. Correction Principle

Contoh kesalahan:

```text
OUT -20
```

seharusnya:

```text
OUT -15
```

Jangan mengubah:

```text
-20 → -15
```

Tetapi buat correction:

```text
Original: -20

Correction:
+5

Net:
-15
```

History tetap dapat ditelusuri.

---

# 14. Ledger Reference

Setiap ledger entry harus dapat ditelusuri ke sumber perubahan.

Contoh:

```text
ledger
   ↓
transaction
   ↓
transaction_detail
```

Reference dapat mengidentifikasi:

```text
Stock In
Stock Out
Stock Opname
Adjustment
```

Tujuannya:

```text
Ledger
 ↓
Why did stock change?
 ↓
Original Transaction
```

---

# 15. Warehouse Scope

Inventory harus memperhatikan warehouse.

Contoh:

```text
Warehouse A
Item X
Stock = 100
```

berbeda dengan:

```text
Warehouse B
Item X
Stock = 50
```

Ledger harus memiliki konteks warehouse yang jelas.

---

# 16. Item Scope

Ledger juga harus mengidentifikasi item.

Konsep:

```text
warehouse_id
item_id
quantity
movement_type
```

Sehingga movement dapat dihitung berdasarkan:

```text
Item
+
Warehouse
```

---

# 17. Quantity Direction

Movement harus memiliki arah yang jelas.

Contoh:

```text
IN
+100

OUT
-20

ADJUSTMENT
±5
```

Hindari logic yang membuat developer harus menebak apakah quantity positif atau negatif.

---

# 18. Timestamp

Ledger harus memiliki waktu movement yang dapat ditelusuri.

Minimal:

```text
created_at
```

Jika business requirement membutuhkan tanggal transaksi yang berbeda dari waktu record dibuat, gunakan field terpisah seperti:

```text
transaction_date
created_at
```

Keduanya memiliki arti berbeda.

---

# 19. Actor

Movement harus dapat ditelusuri ke user/system actor yang menyebabkan perubahan.

Contoh:

```text
user_id
```

Tujuannya:

```text
Who changed stock?
```

dapat dijawab.

---

# 20. Concurrency

Inventory dapat diakses oleh beberapa user secara bersamaan.

Contoh:

```text
User A → Stock Out 60
User B → Stock Out 50

Available = 100
```

Sistem harus mencegah kedua transaksi menghasilkan:

```text
100 - 60 - 50 = -10
```

jika negative stock tidak diperbolehkan.

Database transaction dan locking strategy harus digunakan sesuai implementasi untuk menjaga inventory consistency.

---

# 21. Race Condition

Contoh:

```text
Stock = 100

User A membaca 100
User B membaca 100

A mengurangi 80
B mengurangi 80
```

Tanpa concurrency control, sistem dapat menghasilkan kondisi yang salah.

Karena itu validasi stock harus dilakukan dalam transaction yang aman terhadap concurrent update.

---

# 22. Reconciliation

Inventra dapat melakukan reconciliation:

```text
Ledger Calculation
        vs
Inventory Balance
```

Contoh:

```text
Ledger = 125
Balance = 125
```

Status:

```text
OK
```

Jika:

```text
Ledger = 125
Balance = 120
```

status:

```text
Mismatch
```

Mismatch harus dapat diinvestigasi.

---

# 23. Reporting

Reporting inventory sebaiknya menggunakan data yang konsisten dengan ledger.

Contoh:

```text
Stock Movement Report
        ↓
Inventory Ledger
```

Sedangkan:

```text
Current Stock Report
        ↓
Inventory Balance
```

Jika diperlukan, keduanya dapat dibandingkan untuk reconciliation.

---

# 24. Dashboard

Dashboard current stock dapat membaca:

```text
Inventory Balance
```

agar query lebih cepat daripada menghitung seluruh ledger setiap request.

Untuk analytics historis:

```text
Inventory Ledger
```

dapat digunakan.

---

# 25. Performance

Ledger dapat menjadi tabel besar.

Karena itu query perlu diperhatikan.

Index kandidat:

```text
warehouse_id
item_id
movement_type
created_at
transaction_id
```

Index final ditentukan berdasarkan query aktual dan workload.

Analisis menggunakan:

```text
EXPLAIN
EXPLAIN ANALYZE
```

---

# 26. Data Retention

Ledger tidak boleh dihapus hanya untuk mengurangi jumlah data.

Karena ledger merupakan historical record.

Jika retention policy diperlukan:

```text
Retention Policy
        ↓
Archive Strategy
```

harus dibuat sebagai keputusan arsitektur tersendiri.

---

# 27. Alternatives Considered

### Direct Stock Column

```text
items.stock_quantity
```

Tidak dipilih sebagai satu-satunya mekanisme karena tidak memberikan historical movement yang memadai.

---

### Recalculate Everything From Ledger

Secara teori current stock dapat selalu dihitung:

```text
SUM(all movements)
```

Namun untuk aplikasi dengan dataset besar, pendekatan ini dapat menjadi mahal untuk query current stock yang sangat sering.

Karena itu Inventra menggunakan:

```text
Ledger
+
Balance
```

---

### Event Sourcing Full

Full event sourcing tidak digunakan untuk V1 karena kompleksitasnya lebih besar dari kebutuhan Inventra saat ini.

Inventra menggunakan prinsip ledger untuk inventory tanpa menjadikan seluruh aplikasi sebagai full event-sourced system.

---

# 28. Consequences

### Positive

```text
+ Stock movement traceable
+ Historical data preserved
+ Easier audit
+ Easier reconciliation
+ Better reporting
+ Supports stock opname
+ Easier investigation
```

### Negative

```text
- More database records
- More complex transaction logic
- Requires concurrency handling
- Requires careful indexing
- Requires balance/ledger consistency checks
```

---

# 29. Implementation Principle

Setiap perubahan stock normal harus mengikuti:

```text
Business Transaction
       ↓
Validation
       ↓
Database Transaction
       ↓
Ledger Entry
       ↓
Balance Update
       ↓
Audit
       ↓
Commit
```

Tidak diperbolehkan:

```text
Controller
   ↓
Direct stock update
```

tanpa mekanisme ledger yang ditentukan architecture Inventra.

---

# 30. Maintenance Guide

Jika suatu hari ditemukan:

```text
Dashboard Stock = 100
Inventory Ledger = 95
```

jangan langsung mengubah angka balance.

Lakukan:

```text
Balance
   ↓
Compare Ledger
   ↓
Find Missing / Incorrect Movement
   ↓
Find Source Transaction
   ↓
Investigate
   ↓
Correction / Adjustment
```

---

# 31. Related Decisions

```text
ADR-001 — PostgreSQL
ADR-008 — Audit Log
```

Dokumen terkait:

```text
05_DATABASE.md
06_STOCK_IN.md
07_STOCK_OUT.md
08_STOCK_OPNAME.md
```

---

# 32. Final Decision

**Accepted**

Inventra menggunakan **Inventory Ledger + Inventory Balance** untuk mengelola inventory.

Prinsip utama:

```text
Ledger
=
Historical Movement

Balance
=
Current Inventory State

Transaction
=
Reason for Movement
```

Setiap perubahan stock harus dapat ditelusuri kembali ke transaksi yang menyebabkannya dan tidak boleh mengorbankan inventory integrity.

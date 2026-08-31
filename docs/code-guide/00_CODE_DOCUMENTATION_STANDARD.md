# Inventra

## Code Documentation Standard

**Document:** Code Documentation Standard
**Version:** V1.0
**Status:** Draft

---

# 1. Purpose

Dokumen ini menetapkan standar penulisan, struktur, komentar, dan dokumentasi source code Inventra.

Tujuan utamanya:

1. Code mudah dibaca.
2. Developer memahami alasan sebuah code dibuat.
3. Developer dapat melakukan maintenance tanpa AI.
4. Developer dapat menemukan code berdasarkan feature.
5. Developer baru dapat memahami flow aplikasi.
6. Vibe coding tetap dapat digunakan tanpa membuat code menjadi sulit dipelihara.

Prinsip:

```text
Code should explain HOW.
Documentation should explain WHY.
```

---

# 2. Technology Stack

Code documentation mengikuti stack:

```text
Backend
├── PHP
├── Laravel
└── REST API

Frontend
├── Vue.js
├── Inertia.js
└── Tailwind CSS

Database
└── PostgreSQL

Environment
└── Docker

Testing
├── PHPUnit / Pest
└── Laravel Testing

Version Control
└── Git / GitHub
```

---

# 3. General Code Principle

Code Inventra harus:

```text
Readable
Predictable
Testable
Maintainable
Secure
Documented
```

Hindari code yang:

```text
Too clever
Too complex
Duplicated
Unexplained
Tightly coupled
```

---

# 4. File Header Documentation

Setiap file penting harus memiliki komentar di bagian atas.

Contoh:

```php
<?php

/**
 * Module: Stock Out
 * Feature: Create Stock Out
 * Responsibility:
 * - Validate stock out request
 * - Process stock out business logic
 *
 * Flow:
 * Controller
 * → StockOutService
 * → InventoryService
 * → Ledger
 * → Stock Balance
 * → Audit
 *
 * Related Documentation:
 * docs/code-guide/07_STOCK_OUT.md
 */
```

Tujuan header:

```text
Open file
   ↓
Read header
   ↓
Immediately understand:
- File ini untuk apa?
- Feature apa?
- Flow-nya bagaimana?
- Dokumentasi mana?
```

---

# 5. Vue File Header

Contoh:

```vue
<script setup>
/**
 * Module: Stock Out
 * Feature: Stock Out Form
 * Responsibility:
 * - Display stock out form
 * - Collect user input
 * - Submit request through Inertia
 *
 * Business logic must remain on backend.
 *
 * Related Documentation:
 * docs/code-guide/07_STOCK_OUT.md
 */
</script>
```

Vue tidak boleh menjadi tempat business rule utama.

---

# 6. Section Comments

File yang cukup panjang harus memiliki section comment.

Contoh:

```php
// ============================================================
// DEPENDENCIES
// ============================================================

// ============================================================
// VALIDATION
// ============================================================

// ============================================================
// AUTHORIZATION
// ============================================================

// ============================================================
// BUSINESS LOGIC
// ============================================================

// ============================================================
// RESPONSE
// ============================================================
```

Tujuannya agar developer dapat melakukan scanning terhadap file dengan cepat.

---

# 7. Do Not Comment Every Line

Tidak perlu:

```php
// Get user
$user = auth()->user();

// Get item
$item = Item::find($id);

// Return item
return $item;
```

Komentar seperti ini hanya mengulang code.

Lebih baik menjelaskan alasan:

```php
// Only items assigned to the user's department can be requested.
// This prevents departments from requesting restricted items.
$items = $this->itemService->getAllowedItemsForDepartment($department);
```

---

# 8. Comment the WHY

Komentar harus menjelaskan alasan business/technical decision.

Buruk:

```php
// Check stock
```

Lebih baik:

```php
// Stock availability must be checked inside the transaction
// to prevent concurrent Stock Out operations from creating
// an invalid negative balance.
```

---

# 9. Business Rule Comments

Business rule yang tidak obvious harus diberi komentar.

Contoh:

```php
// Content per unit is stored on the transaction detail because
// package content may differ by brand, supplier, or packaging.
// Historical transactions must remain reproducible.
```

---

# 10. Security Comments

Security-sensitive code harus menjelaskan security reason.

Contoh:

```php
// Do not trust warehouse_id from the frontend.
// The policy verifies that the authenticated user has access
// to the requested warehouse.
$this->authorize('create', [StockOut::class, $warehouse]);
```

---

# 11. Controller Standard

Controller bertanggung jawab sebagai entry point.

Controller:

```text
Receive Request
 ↓
Authorize
 ↓
Call Service
 ↓
Return Response
```

Controller tidak menjadi tempat seluruh business logic.

Contoh struktur:

```php
public function store(StoreStockOutRequest $request)
{
    $this->authorize('create', StockOut::class);

    $stockOut = $this->stockOutService->create(
        $request->validated(),
        $request->user()
    );

    return redirect()->route('stock-out.show', $stockOut);
}
```

---

# 12. Service Standard

Service berisi business logic.

Contoh:

```text
StockOutController
        ↓
StockOutService
        ↓
InventoryService
```

Service bertanggung jawab terhadap:

- Business rules.
- Transaction orchestration.
- Calling domain operations.
- Database transaction boundary.

---

# 13. Model Standard

Model bertanggung jawab terhadap:

- Database representation.
- Relationships.
- Casts.
- Model-specific behavior.

Hindari memasukkan business workflow kompleks ke model jika logic tersebut lebih tepat berada di service.

---

# 14. Form Request Standard

Form Request digunakan untuk:

```text
Input validation
+
Authorization when appropriate
```

Contoh:

```php
class StoreStockOutRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'items' => ['required', 'array'],
        ];
    }
}
```

---

# 15. Policy Standard

Policy digunakan untuk authorization terhadap resource.

Contoh:

```text
StockOutPolicy
ItemPolicy
WarehousePolicy
AssetPolicy
PurchaseRequestPolicy
```

Policy menjawab:

```text
Can this user perform this action
on this resource?
```

---

# 16. Vue Responsibility

Vue bertanggung jawab terhadap:

```text
Presentation
Interaction
Form State
UI State
```

Vue tidak bertanggung jawab terhadap:

```text
Stock calculation
Authorization
Permission enforcement
Financial/business decision
Database manipulation
```

---

# 17. Inertia Responsibility

Inertia menjadi bridge antara Laravel dan Vue.

```text
Laravel
   ↕
Inertia
   ↕
Vue
```

Inertia digunakan untuk:

- Page rendering.
- Form submission.
- Server-provided props.
- Navigation.

---

# 18. API Responsibility

API menjadi interface untuk external client.

API harus tetap menggunakan:

```text
Authentication
Authorization
Validation
Business Logic
```

Jangan membuat business logic berbeda antara Web dan API tanpa alasan yang jelas.

Ideal:

```text
Web Controller ─┐
                ├── Service
API Controller ─┘
```

---

# 19. Database Query Standard

Query harus memperhatikan:

```text
Correctness
Performance
Security
Readability
```

Gunakan:

- Eloquent.
- Query Builder.
- Parameter binding.

Hindari query yang mengambil data lebih banyak dari yang dibutuhkan.

---

# 20. Database Performance

Query yang sering digunakan harus diperhatikan performanya.

Gunakan:

```text
Indexes
Proper Relationships
Eager Loading
Pagination
Query Optimization
EXPLAIN ANALYZE
```

Contoh masalah:

```text
N+1 Query
```

Harus dihindari.

Untuk query penting:

```text
EXPLAIN ANALYZE
```

dapat digunakan untuk mengetahui execution plan dan bottleneck.

---

# 21. Naming Convention

Nama harus menjelaskan fungsi.

Contoh:

```text
createStockOut()
calculateEquivalentQuantity()
getAvailableStock()
approvePurchaseRequest()
```

Hindari:

```text
process()
handle()
doStuff()
run()
```

jika tidak jelas konteksnya.

---

# 22. Variable Naming

Gunakan nama yang jelas.

Buruk:

```php
$q = 100;
$x = $item;
$d = $department;
```

Lebih baik:

```php
$requestedQuantity = 100;
$item = $item;
$department = $department;
```

Nama panjang diperbolehkan jika membuat code lebih mudah dipahami.

---

# 23. Method Size

Method sebaiknya memiliki satu responsibility utama.

Jika method mulai terlalu panjang:

```text
Large Method
    ↓
Identify responsibilities
    ↓
Extract Method / Service
```

Jangan memecah code secara berlebihan hanya demi membuat file kecil.

---

# 24. Magic Numbers

Hindari:

```php
if ($quantity > 100) {
```

jika angka `100` memiliki arti business tertentu.

Gunakan constant/configuration:

```php
MAX_ALLOWED_QUANTITY
```

atau business rule yang jelas.

---

# 25. Error Handling

Error harus memiliki konteks.

Contoh:

```php
throw new InsufficientStockException(
    'Insufficient stock for item.'
);
```

Jangan memberikan error teknis database langsung kepada user.

---

# 26. Logging

Log digunakan untuk troubleshooting.

Contoh informasi yang berguna:

```text
Transaction Reference
User ID
Action
Module
Error Context
```

Jangan log:

```text
Password
Token
Secret
Database Password
```

---

# 27. Audit vs Application Log

Keduanya berbeda.

### Application Log

Untuk developer/system troubleshooting.

```text
Exception
Error
Performance
Debugging
```

### Audit Log

Untuk business accountability.

```text
Who
Did What
To Which Resource
When
```

Jangan menggunakan application log sebagai pengganti audit log.

---

# 28. Transaction Code Documentation

Critical database operation harus menunjukkan transaction boundary.

Contoh:

```php
DB::transaction(function () use ($data) {

    // Create business transaction.

    // Create immutable inventory ledger entry.

    // Update current stock balance.

    // Record audit event.

});
```

Komentar membantu developer memahami bagian mana yang harus atomic.

---

# 29. Inventory Code Standard

Inventory operation harus mudah ditelusuri.

```text
Stock In
 ↓
Inventory Transaction
 ↓
Ledger
 ↓
Stock Balance
 ↓
Audit
```

Stock Out:

```text
Stock Out
 ↓
Validation
 ↓
Availability Check
 ↓
Approval
 ↓
Inventory Transaction
 ↓
Ledger
 ↓
Stock Balance
 ↓
Audit
```

---

# 30. Content Per Unit Documentation

Jika code memproses content per unit:

```php
/**
 * Convert the physical package quantity into the item's
 * base-unit equivalent.
 *
 * Example:
 * 6 BOX × 100 PCS/BOX = 600 PCS.
 *
 * The package information comes from the transaction detail
 * rather than a global conversion table because package
 * content can vary by product/brand.
 */
```

Ini merupakan business rule penting Inventra.

---

# 31. Permission Code Documentation

Permission-sensitive code harus menjelaskan scope.

Contoh:

```php
/**
 * Authorization is not limited to the stock.out permission.
 *
 * The user must also have access to the requested warehouse
 * and the related resource scope.
 */
```

---

# 32. Feature Traceability

Setiap feature harus dapat ditelusuri:

```text
Page
 ↓
Route
 ↓
Controller
 ↓
Request
 ↓
Policy
 ↓
Service
 ↓
Model
 ↓
Database
 ↓
Audit
```

Developer harus dapat mengikuti flow ini tanpa AI.

---

# 33. Code Guide Structure

Setiap module documentation mengikuti pola:

```text
1. Purpose
2. Business Context
3. Feature List
4. User Flow
5. Code Flow
6. File Structure
7. Important Files
8. Database Interaction
9. Authorization
10. Validation
11. Business Rules
12. Error Handling
13. Security Considerations
14. Testing
15. Maintenance Guide
```

Tidak semua bagian harus panjang.

Yang penting:

```text
Enough detail
+
Easy to understand
+
Easy to maintain
```

---

# 34. Maintenance Guide

Setiap module wajib memiliki bagian:

```text
"If I need to change X, where do I go?"
```

Contoh Stock Out:

```text
Change UI
→ resources/js/Pages/StockOut/

Change validation
→ StoreStockOutRequest

Change permission
→ StockOutPolicy / RBAC

Change business rule
→ StockOutService

Change stock calculation
→ InventoryService

Change database
→ migrations / models

Change audit
→ AuditService
```

---

# 35. Vibe Coding Rule

AI/vibe coding diperbolehkan.

Namun code hasil AI harus:

```text
Read
 ↓
Understand
 ↓
Review
 ↓
Test
 ↓
Document
```

Jangan langsung:

```text
Prompt
 ↓
Copy
 ↓
Commit
```

Developer tetap bertanggung jawab terhadap code.

---

# 36. AI-Generated Code Checklist

Sebelum menerima code dari AI:

```text
[ ] Saya tahu file ini untuk apa
[ ] Saya tahu function ini melakukan apa
[ ] Saya tahu input-nya
[ ] Saya tahu output-nya
[ ] Saya tahu business rule-nya
[ ] Saya tahu database yang disentuh
[ ] Saya tahu permission yang digunakan
[ ] Saya tahu kemungkinan error
[ ] Saya tahu cara test-nya
[ ] Saya tahu cara mengubahnya jika requirement berubah
```

Jika belum bisa menjawab:

```text
DO NOT MERGE YET
```

---

# 37. Learning-First Development

Setiap feature yang dibuat harus dapat dijelaskan kembali oleh developer.

Minimal developer memahami:

```text
What?
Why?
Where?
How?
```

### What

Feature melakukan apa?

### Why

Kenapa business rule tersebut dibuat?

### Where

File mana yang mengimplementasikannya?

### How

Bagaimana data berjalan dari frontend sampai database?

---

# 38. Maintenance Without AI

Untuk perubahan sederhana:

```text
Requirement
 ↓
Identify Feature
 ↓
Open Code Guide
 ↓
Trace Code
 ↓
Modify
 ↓
Run Test
 ↓
Review
```

AI hanya digunakan jika:

```text
Complex Problem
Unknown Error
Optimization Research
Refactoring Assistance
```

bukan sebagai satu-satunya cara memahami system.

---

# 39. Git & Documentation

Setiap feature dikembangkan melalui branch feature.

Contoh:

```text
feature/stock-in
feature/stock-out
feature/stock-opname
feature/asset-management
```

Branch mengikuti sprint/feature yang telah ditentukan.

Dokumentasi sprint menjelaskan:

```text
Scope
Tasks
Files
Implementation
Testing
Acceptance Criteria
```

Developer melakukan push ke GitHub secara manual.

---

# 40. Definition of Done

Code feature dianggap selesai jika:

```text
[ ] Feature implemented
[ ] Validation implemented
[ ] Authorization implemented
[ ] Business logic implemented
[ ] Database handled correctly
[ ] Tests added
[ ] Security reviewed
[ ] Code comments added where needed
[ ] File headers added
[ ] Code guide updated
[ ] Sprint documentation updated
[ ] Developer understands the flow
```

---

# 41. Final Principle

Inventra tidak hanya mengejar:

```text
"Code works."
```

Tetapi:

```text
Code works
+
Code is understandable
+
Code is traceable
+
Code is testable
+
Code is maintainable
```

Target akhir:

> **Developer dapat membuka project Inventra beberapa bulan kemudian, membaca dokumentasi dan source code, lalu memahami serta mengubah feature tanpa harus melakukan vibe coding untuk setiap perubahan.**

# Inventra

## Product Backlog

**Document:** Product Backlog
**Version:** V1.0
**Status:** Draft

---

# 1. Backlog Purpose

Backlog Inventra berisi seluruh pekerjaan yang diperlukan untuk membangun aplikasi dari foundation sampai deployment.

Backlog menjadi sumber untuk:

```text
Backlog
   ↓
Sprint
   ↓
Feature
   ↓
Git Branch
   ↓
Implementation
   ↓
Test
   ↓
Documentation
```

Satu fitur utama diusahakan memiliki satu branch Git.

---

# 2. Priority

| Priority | Meaning          |
| -------- | ---------------- |
| P0       | Critical / wajib |
| P1       | High             |
| P2       | Medium           |
| P3       | Future           |

---

# 3. Status

Status backlog:

```text
BACKLOG
READY
IN PROGRESS
TESTING
DONE
BLOCKED
```

---

# 4. Epic Structure

Inventra menggunakan epic:

```text
EPIC-01 Foundation
EPIC-02 Authentication
EPIC-03 RBAC
EPIC-04 Master Data
EPIC-05 Item Management
EPIC-06 Warehouse
EPIC-07 Inventory
EPIC-08 Stock Opname
EPIC-09 Procurement
EPIC-10 Asset Management
EPIC-11 Approval
EPIC-12 Transaction & Audit
EPIC-13 Reporting
EPIC-14 Dashboard
EPIC-15 REST API
EPIC-16 Export
EPIC-17 Security
EPIC-18 Testing
EPIC-19 Deployment
```

---

# 5. EPIC-01 — Foundation

| ID      | Task                              | Priority | Status  |
| ------- | --------------------------------- | :------: | ------- |
| FND-001 | Initialize Laravel project        |    P0    | BACKLOG |
| FND-002 | Configure Inertia.js              |    P0    | BACKLOG |
| FND-003 | Configure Vue.js                  |    P0    | BACKLOG |
| FND-004 | Configure PostgreSQL              |    P0    | BACKLOG |
| FND-005 | Configure Docker                  |    P0    | BACKLOG |
| FND-006 | Configure environment variables   |    P0    | BACKLOG |
| FND-007 | Setup application structure       |    P0    | BACKLOG |
| FND-008 | Setup logging                     |    P1    | BACKLOG |
| FND-009 | Setup error handling              |    P1    | BACKLOG |
| FND-010 | Apply Code Documentation Standard |    P0    | BACKLOG |

---

# 6. EPIC-02 — Authentication

| ID       | Task                          | Priority | Status  |
| -------- | ----------------------------- | :------: | ------- |
| AUTH-001 | Login                         |    P0    | BACKLOG |
| AUTH-002 | Logout                        |    P0    | BACKLOG |
| AUTH-003 | Session management            |    P0    | BACKLOG |
| AUTH-004 | Password hashing              |    P0    | BACKLOG |
| AUTH-005 | User status                   |    P1    | BACKLOG |
| AUTH-006 | Authentication middleware     |    P0    | BACKLOG |
| AUTH-007 | Authentication error handling |    P1    | BACKLOG |

---

# 7. EPIC-03 — RBAC

| ID       | Task                       | Priority | Status  |
| -------- | -------------------------- | :------: | ------- |
| RBAC-001 | Role management            |    P0    | BACKLOG |
| RBAC-002 | Permission management      |    P0    | BACKLOG |
| RBAC-003 | Role-permission assignment |    P0    | BACKLOG |
| RBAC-004 | Department scope           |    P0    | BACKLOG |
| RBAC-005 | Warehouse scope            |    P0    | BACKLOG |
| RBAC-006 | Location scope             |    P1    | BACKLOG |
| RBAC-007 | Backend authorization      |    P0    | BACKLOG |
| RBAC-008 | Default deny               |    P0    | BACKLOG |
| RBAC-009 | Self-approval prevention   |    P0    | BACKLOG |

---

# 8. EPIC-04 — Master Data

| ID      | Task                  | Priority | Status  |
| ------- | --------------------- | :------: | ------- |
| MST-001 | Company management    |    P1    | BACKLOG |
| MST-002 | Department management |    P0    | BACKLOG |
| MST-003 | Unit management       |    P0    | BACKLOG |
| MST-004 | Category management   |    P0    | BACKLOG |
| MST-005 | Supplier management   |    P1    | BACKLOG |
| MST-006 | Master data status    |    P1    | BACKLOG |

---

# 9. EPIC-05 — Item Management

| ID       | Task                     | Priority | Status  |
| -------- | ------------------------ | :------: | ------- |
| ITEM-001 | Item CRUD                |    P0    | BACKLOG |
| ITEM-002 | Item code                |    P0    | BACKLOG |
| ITEM-003 | Item category            |    P0    | BACKLOG |
| ITEM-004 | Brand information        |    P1    | BACKLOG |
| ITEM-005 | Item type                |    P0    | BACKLOG |
| ITEM-006 | Base unit                |    P0    | BACKLOG |
| ITEM-007 | Minimum stock            |    P1    | BACKLOG |
| ITEM-008 | Item status              |    P0    | BACKLOG |
| ITEM-009 | Department allowed items |    P0    | BACKLOG |
| ITEM-010 | Item search & filter     |    P1    | BACKLOG |

---

# 10. EPIC-06 — Warehouse

| ID     | Task                    | Priority | Status  |
| ------ | ----------------------- | :------: | ------- |
| WH-001 | Warehouse CRUD          |    P0    | BACKLOG |
| WH-002 | Warehouse code          |    P0    | BACKLOG |
| WH-003 | Warehouse status        |    P0    | BACKLOG |
| WH-004 | Location CRUD           |    P0    | BACKLOG |
| WH-005 | Rack/location hierarchy |    P0    | BACKLOG |
| WH-006 | Warehouse scope         |    P0    | BACKLOG |
| WH-007 | Location scope          |    P1    | BACKLOG |
| WH-008 | Physical item location  |    P0    | BACKLOG |

---

# 11. EPIC-07 — Inventory

## Stock In

| ID      | Task                            | Priority | Status  |
| ------- | ------------------------------- | :------: | ------- |
| INV-001 | Stock In form                   |    P0    | BACKLOG |
| INV-002 | Multiple item input             |    P0    | BACKLOG |
| INV-003 | Manual unit input               |    P0    | BACKLOG |
| INV-004 | Content per unit                |    P0    | BACKLOG |
| INV-005 | Equivalent quantity calculation |    P0    | BACKLOG |
| INV-006 | Warehouse selection             |    P0    | BACKLOG |
| INV-007 | Location selection              |    P0    | BACKLOG |
| INV-008 | Stock In validation             |    P0    | BACKLOG |
| INV-009 | Stock In transaction            |    P0    | BACKLOG |
| INV-010 | Inventory ledger entry          |    P0    | BACKLOG |
| INV-011 | Stock balance update            |    P0    | BACKLOG |
| INV-012 | Transaction reference number    |    P0    | BACKLOG |

### Stock Out

| ID      | Task                          | Priority | Status  |
| ------- | ----------------------------- | :------: | ------- |
| INV-013 | Stock Out form                |    P0    | BACKLOG |
| INV-014 | Multiple item input           |    P0    | BACKLOG |
| INV-015 | Department destination        |    P0    | BACKLOG |
| INV-016 | Stock availability validation |    P0    | BACKLOG |
| INV-017 | Stock Out approval            |    P0    | BACKLOG |
| INV-018 | Stock Out execution           |    P0    | BACKLOG |
| INV-019 | Ledger entry                  |    P0    | BACKLOG |
| INV-020 | Stock balance update          |    P0    | BACKLOG |

### Transfer

| ID      | Task                 | Priority | Status  |
| ------- | -------------------- | :------: | ------- |
| INV-021 | Stock transfer form  |    P0    | BACKLOG |
| INV-022 | Source location      |    P0    | BACKLOG |
| INV-023 | Destination location |    P0    | BACKLOG |
| INV-024 | Transfer validation  |    P0    | BACKLOG |
| INV-025 | Transfer transaction |    P0    | BACKLOG |

### Return

| ID      | Task                           | Priority | Status  |
| ------- | ------------------------------ | :------: | ------- |
| INV-026 | Stock return                   |    P1    | BACKLOG |
| INV-027 | Reference original transaction |    P1    | BACKLOG |
| INV-028 | Return validation              |    P1    | BACKLOG |
| INV-029 | Return Stock In                |    P1    | BACKLOG |

---

# 12. EPIC-08 — Stock Opname

| ID      | Task                    | Priority | Status  |
| ------- | ----------------------- | :------: | ------- |
| OPN-001 | Create stock opname     |    P0    | BACKLOG |
| OPN-002 | Select warehouse        |    P0    | BACKLOG |
| OPN-003 | Select location         |    P0    | BACKLOG |
| OPN-004 | Display system quantity |    P0    | BACKLOG |
| OPN-005 | Input physical quantity |    P0    | BACKLOG |
| OPN-006 | Calculate difference    |    P0    | BACKLOG |
| OPN-007 | Opname approval         |    P0    | BACKLOG |
| OPN-008 | Generate adjustment     |    P0    | BACKLOG |
| OPN-009 | Opname history          |    P1    | BACKLOG |

---

# 13. EPIC-09 — Procurement

## Purchase Request

| ID     | Task                    | Priority | Status  |
| ------ | ----------------------- | :------: | ------- |
| PR-001 | Create PR               |    P0    | BACKLOG |
| PR-002 | Multiple PR items       |    P0    | BACKLOG |
| PR-003 | Department restriction  |    P0    | BACKLOG |
| PR-004 | Allowed item validation |    P0    | BACKLOG |
| PR-005 | PR submit               |    P0    | BACKLOG |
| PR-006 | PR approval             |    P0    | BACKLOG |
| PR-007 | PR rejection            |    P0    | BACKLOG |
| PR-008 | PR revision             |    P1    | BACKLOG |
| PR-009 | PR cancellation         |    P1    | BACKLOG |

## Purchase Order

| ID     | Task               | Priority | Status  |
| ------ | ------------------ | :------: | ------- |
| PO-001 | Create PO          |    P1    | BACKLOG |
| PO-002 | PR reference       |    P1    | BACKLOG |
| PO-003 | Supplier selection |    P1    | BACKLOG |
| PO-004 | PO approval        |    P1    | BACKLOG |
| PO-005 | PO status          |    P1    | BACKLOG |

## Receiving

| ID      | Task                   | Priority | Status  |
| ------- | ---------------------- | :------: | ------- |
| REC-001 | Receiving form         |    P1    | BACKLOG |
| REC-002 | PO reference           |    P1    | BACKLOG |
| REC-003 | Quantity verification  |    P1    | BACKLOG |
| REC-004 | Receiving confirmation |    P1    | BACKLOG |
| REC-005 | Generate Stock In      |    P1    | BACKLOG |

---

# 14. EPIC-10 — Asset Management

| ID      | Task                  | Priority | Status  |
| ------- | --------------------- | :------: | ------- |
| AST-001 | Asset registration    |    P1    | BACKLOG |
| AST-002 | Asset tag             |    P1    | BACKLOG |
| AST-003 | Serial number         |    P1    | BACKLOG |
| AST-004 | Asset location        |    P1    | BACKLOG |
| AST-005 | Department assignment |    P1    | BACKLOG |
| AST-006 | Custodian assignment  |    P1    | BACKLOG |
| AST-007 | Asset return          |    P1    | BACKLOG |
| AST-008 | Asset maintenance     |    P2    | BACKLOG |
| AST-009 | Asset disposal        |    P1    | BACKLOG |
| AST-010 | Asset history         |    P1    | BACKLOG |

---

# 15. EPIC-11 — Approval Workflow

| ID      | Task                     | Priority | Status  |
| ------- | ------------------------ | :------: | ------- |
| APR-001 | Workflow configuration   |    P0    | BACKLOG |
| APR-002 | Workflow steps           |    P0    | BACKLOG |
| APR-003 | Approver assignment      |    P0    | BACKLOG |
| APR-004 | Approval request         |    P0    | BACKLOG |
| APR-005 | Approve                  |    P0    | BACKLOG |
| APR-006 | Reject                   |    P0    | BACKLOG |
| APR-007 | Approval history         |    P0    | BACKLOG |
| APR-008 | Self-approval prevention |    P0    | BACKLOG |

---

# 16. EPIC-12 — Transaction & Audit

## Transaction History

| ID      | Task                     | Priority | Status  |
| ------- | ------------------------ | :------: | ------- |
| TRX-001 | Transaction list         |    P0    | BACKLOG |
| TRX-002 | Transaction detail       |    P0    | BACKLOG |
| TRX-003 | Reference number tracing |    P0    | BACKLOG |
| TRX-004 | Related transaction      |    P1    | BACKLOG |
| TRX-005 | Item tracing             |    P0    | BACKLOG |
| TRX-006 | Warehouse tracing        |    P0    | BACKLOG |
| TRX-007 | Location tracing         |    P0    | BACKLOG |

## Audit

| ID      | Task               | Priority | Status  |
| ------- | ------------------ | :------: | ------- |
| AUD-001 | Audit log          |    P0    | BACKLOG |
| AUD-002 | User tracking      |    P0    | BACKLOG |
| AUD-003 | Action tracking    |    P0    | BACKLOG |
| AUD-004 | Before/after data  |    P1    | BACKLOG |
| AUD-005 | IP/device metadata |    P1    | BACKLOG |
| AUD-006 | Audit log viewer   |    P1    | BACKLOG |

---

# 17. EPIC-13 — Reporting

| ID      | Task                  | Priority | Status  |
| ------- | --------------------- | :------: | ------- |
| REP-001 | Stock report          |    P1    | BACKLOG |
| REP-002 | Stock movement report |    P1    | BACKLOG |
| REP-003 | Low stock report      |    P1    | BACKLOG |
| REP-004 | Stock opname report   |    P1    | BACKLOG |
| REP-005 | Transaction report    |    P1    | BACKLOG |
| REP-006 | Procurement report    |    P2    | BACKLOG |
| REP-007 | Asset report          |    P2    | BACKLOG |
| REP-008 | Scope-based reporting |    P0    | BACKLOG |

---

# 18. EPIC-14 — Dashboard

| ID       | Task                 | Priority | Status  |
| -------- | -------------------- | :------: | ------- |
| DASH-001 | Dashboard layout     |    P1    | BACKLOG |
| DASH-002 | Stock summary        |    P1    | BACKLOG |
| DASH-003 | Low stock widget     |    P1    | BACKLOG |
| DASH-004 | Pending approval     |    P1    | BACKLOG |
| DASH-005 | Recent transactions  |    P1    | BACKLOG |
| DASH-006 | Asset summary        |    P2    | BACKLOG |
| DASH-007 | Role-based dashboard |    P1    | BACKLOG |

---

# 19. EPIC-15 — REST API

| ID      | Task                  | Priority | Status  |
| ------- | --------------------- | :------: | ------- |
| API-001 | API authentication    |    P1    | BACKLOG |
| API-002 | API versioning        |    P1    | BACKLOG |
| API-003 | Item API              |    P1    | BACKLOG |
| API-004 | Stock API             |    P1    | BACKLOG |
| API-005 | Transaction API       |    P1    | BACKLOG |
| API-006 | Procurement API       |    P2    | BACKLOG |
| API-007 | Asset API             |    P2    | BACKLOG |
| API-008 | Approval API          |    P2    | BACKLOG |
| API-009 | Report API            |    P2    | BACKLOG |
| API-010 | OpenAPI documentation |    P1    | BACKLOG |

---

# 20. EPIC-16 — Export

| ID      | Task                     | Priority | Status  |
| ------- | ------------------------ | :------: | ------- |
| EXP-001 | Excel stock export       |    P1    | BACKLOG |
| EXP-002 | Excel transaction export |    P1    | BACKLOG |
| EXP-003 | Excel asset export       |    P2    | BACKLOG |
| EXP-004 | PDF stock export         |    P2    | BACKLOG |
| EXP-005 | PDF transaction export   |    P2    | BACKLOG |
| EXP-006 | Scope-based export       |    P0    | BACKLOG |

---

# 21. EPIC-17 — Security

| ID      | Task                     | Priority | Status  |
| ------- | ------------------------ | :------: | ------- |
| SEC-001 | Authentication review    |    P0    | BACKLOG |
| SEC-002 | Authorization review     |    P0    | BACKLOG |
| SEC-003 | Scope enforcement review |    P0    | BACKLOG |
| SEC-004 | Input validation review  |    P0    | BACKLOG |
| SEC-005 | SQL injection review     |    P0    | BACKLOG |
| SEC-006 | XSS protection review    |    P0    | BACKLOG |
| SEC-007 | CSRF review              |    P0    | BACKLOG |
| SEC-008 | Rate limiting            |    P1    | BACKLOG |
| SEC-009 | Sensitive data review    |    P0    | BACKLOG |
| SEC-010 | Security logging         |    P1    | BACKLOG |

---

# 22. EPIC-18 — Testing & QA

| ID     | Task                          | Priority | Status  |
| ------ | ----------------------------- | :------: | ------- |
| QA-001 | Unit testing setup            |    P0    | BACKLOG |
| QA-002 | Feature testing setup         |    P0    | BACKLOG |
| QA-003 | Authorization testing         |    P0    | BACKLOG |
| QA-004 | Inventory calculation testing |    P0    | BACKLOG |
| QA-005 | Stock transaction testing     |    P0    | BACKLOG |
| QA-006 | Approval testing              |    P0    | BACKLOG |
| QA-007 | API testing                   |    P1    | BACKLOG |
| QA-008 | Browser testing               |    P1    | BACKLOG |
| QA-009 | Security testing              |    P0    | BACKLOG |
| QA-010 | Regression testing            |    P0    | BACKLOG |

---

# 23. EPIC-19 — Deployment

| ID      | Task                            | Priority | Status  |
| ------- | ------------------------------- | :------: | ------- |
| DEP-001 | Docker production configuration |    P1    | BACKLOG |
| DEP-002 | Production environment          |    P1    | BACKLOG |
| DEP-003 | Database migration strategy     |    P0    | BACKLOG |
| DEP-004 | Backup strategy                 |    P0    | BACKLOG |
| DEP-005 | Logging configuration           |    P1    | BACKLOG |
| DEP-006 | Health check                    |    P1    | BACKLOG |
| DEP-007 | Deployment documentation        |    P1    | BACKLOG |

---

# 24. Feature Branch Convention

Setiap feature utama dibuat dalam branch tersendiri.

Format:

```text
feature/<backlog-id>-<feature-name>
```

Contoh:

```text
feature/auth-001-login
feature/rbac-001-role-management
feature/item-001-item-crud
feature/inv-001-stock-in
feature/inv-013-stock-out
feature/opn-001-stock-opname
feature/pr-001-purchase-request
```

Bug:

```text
fix/<backlog-id>-<bug-name>
```

Security:

```text
security/<backlog-id>-<security-task>
```

---

# 25. Feature Development Flow

Setiap feature mengikuti:

```text
BACKLOG
   ↓
READY
   ↓
Create Branch
   ↓
Implementation
   ↓
Test
   ↓
Documentation
   ↓
Code Review / Self Review
   ↓
DONE
```

Contoh:

```text
INV-001 Stock In
       ↓
feature/inv-001-stock-in
       ↓
Code
       ↓
Test
       ↓
Update code-guide
       ↓
Update sprint documentation
       ↓
DONE
```

Git push dilakukan oleh developer setelah feature selesai.

---

# 26. Definition of Done

Sebuah backlog item tidak dianggap DONE hanya karena kode berhasil dibuat.

Harus memenuhi:

- Feature bekerja.
- Validation bekerja.
- Authorization bekerja.
- Scope bekerja jika diperlukan.
- Error handling tersedia.
- Test tersedia.
- Audit tersedia jika diperlukan.
- Code comments/documentation tersedia.
- `code-guide` diperbarui jika diperlukan.
- Sprint documentation diperbarui.
- Tidak ada critical regression.

---

# 27. Code Documentation Requirement

Setiap feature wajib mengikuti:

```text
docs/code-guide/00_CODE_DOCUMENTATION_STANDARD.md
```

Dokumentasi harus menjelaskan:

```text
Purpose
Flow
Files
Important Functions
Database Interaction
Authorization
Validation
Error Handling
Maintenance Notes
```

Komentar kode digunakan pada:

- Bagian penting file.
- Section kode.
- Business logic yang tidak obvious.
- Query kompleks.
- Security-sensitive logic.
- Calculation penting.

Tidak perlu memberi komentar pada setiap baris kode yang sudah jelas.

---

# 28. Sprint Mapping

Backlog akan dipetakan ke sprint.

Contoh:

```text
SPRINT-01
Authentication
 ├── AUTH-001
 ├── AUTH-002
 ├── AUTH-003
 └── AUTH-004
```

Kemudian:

```text
SPRINT-02
RBAC
 ├── RBAC-001
 ├── RBAC-002
 ├── RBAC-003
 └── ...
```

Satu sprint dapat memiliki beberapa backlog item yang masih berada dalam satu feature/module.

---

# 29. Backlog Rule

Backlog tidak harus langsung dikerjakan seluruhnya.

Prioritas utama:

```text
P0 → Core System
P1 → Important Feature
P2 → Enhancement
P3 → Future
```

Jika terdapat perubahan requirement:

```text
Requirement Change
       ↓
Update PRD
       ↓
Update Feature Decision
       ↓
Update Database/API
       ↓
Update Backlog
       ↓
Update Sprint
```

Dokumentasi harus tetap sinkron dengan implementation.

---

# 30. Final Backlog Flow

```text
                    PRD
                     ↓
               Product Vision
                     ↓
               Feature Decisions
                     ↓
                  Modules
                     ↓
                 User Flow
                     ↓
                 Database
                     ↓
                    API
                     ↓
             Permission Matrix
                     ↓
                  Roadmap
                     ↓
                 BACKLOG
                     ↓
                  SPRINT
                     ↓
                GIT BRANCH
                     ↓
               IMPLEMENTATION
                     ↓
                   TEST
                     ↓
              DOCUMENTATION
                     ↓
                    DONE
```

Backlog Inventra menjadi **single working list** untuk menerjemahkan requirement menjadi pekerjaan development yang dapat dikerjakan, diuji, didokumentasikan, dan dipisahkan berdasarkan Git branch.

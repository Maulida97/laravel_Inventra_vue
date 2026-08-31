# Inventra

## Sprint 19 — Docker Deployment

**Sprint:** SPRINT-19
**Name:** Docker Deployment
**Project:** Inventra
**Status:** Planned
**Branch:** `feature/docker-deployment`

---

# 1. Sprint Overview

Sprint ini mempersiapkan Inventra agar dapat dijalankan secara konsisten pada:

```text
Local Development
        ↓
Testing / Staging
        ↓
Production Server
```

Menggunakan Docker sebagai environment dan deployment layer.

Fokus:

```text
Docker
Application Container
Database
Web Server
Environment Configuration
Networking
Storage
Queue
Cache
Health Check
Deployment
Backup
Rollback
```

---

# 2. Objective

Target sprint:

```text
1. Inventra dapat dijalankan menggunakan Docker.
2. Environment development dan production terstruktur.
3. Service antar-container dapat berkomunikasi.
4. Database dapat digunakan secara persistent.
5. Storage tidak hilang ketika container dibuat ulang.
6. Application dapat di-deploy ke VPS/server.
7. Configuration menggunakan environment variables.
8. Health check tersedia.
9. Backup dan rollback memiliki prosedur.
10. Deployment dapat dilakukan secara repeatable.
```

---

# 3. Deployment Architecture

Arsitektur dasar:

```text
                    INTERNET
                       │
                      HTTPS
                       │
                       ▼
              Reverse Proxy / Web
                       │
                       ▼
              ┌─────────────────┐
              │ Laravel + Vue   │
              │    Inertia      │
              └────────┬────────┘
                       │
          ┌────────────┼────────────┐
          ▼            ▼            ▼
      PostgreSQL      Redis       Queue
          │
          ▼
     Persistent Volume
```

Detail implementasi dapat disesuaikan dengan kebutuhan server.

---

# 4. Docker Services

Minimal service:

```text
app
database
redis
web
```

Opsional:

```text
queue
scheduler
```

Jika queue dan scheduler dijalankan sebagai process terpisah.

---

# 5. Container Responsibility

## App

Menjalankan:

```text
Laravel
PHP
Application Logic
Inertia Backend
```

---

## Web

Menangani:

```text
HTTP
HTTPS termination
Static Assets
Reverse Proxy
```

---

## Database

Menjalankan:

```text
PostgreSQL
```

Database menggunakan persistent volume.

---

## Redis

Digunakan untuk kebutuhan seperti:

```text
Cache
Queue
Session
```

sesuai konfigurasi Inventra.

---

# 6. Dockerfile

Application memiliki:

```text
Dockerfile
```

Tujuan:

```text
Build
Dependency Installation
Application Runtime
Production Optimization
```

Gunakan multi-stage build jika memang memberikan manfaat.

---

# 7. Docker Image Principle

Image production harus:

```text
Small
Reproducible
Versioned
Secure
```

Hindari memasukkan:

```text
.env
Secrets
Development-only files
Unnecessary dependencies
```

ke dalam image production.

---

# 8. Docker Compose

Development dapat menggunakan:

```text
docker-compose.yml
```

atau:

```text
compose.yaml
```

Service:

```yaml
services:
  app:
  web:
  database:
  redis:
```

Production configuration dapat dipisahkan apabila diperlukan.

---

# 9. Environment Configuration

Configuration menggunakan environment variables.

Contoh:

```text
APP_ENV
APP_KEY
APP_DEBUG

DB_CONNECTION
DB_HOST
DB_PORT
DB_DATABASE
DB_USERNAME
DB_PASSWORD

REDIS_HOST
REDIS_PORT
```

Secret tidak boleh di-hardcode.

---

# 10. Environment Separation

Pisahkan:

```text
Development
Testing
Staging
Production
```

Contoh:

```text
.env.example
.env.testing
.env.production
```

Production secret tidak disimpan di repository.

---

# 11. Docker Network

Service internal menggunakan Docker network.

Contoh:

```text
app
 │
 ├── database
 │
 └── redis
```

Database tidak perlu diekspos langsung ke public internet.

---

# 12. Port Exposure

Public:

```text
80
443
```

Internal:

```text
5432
6379
```

Database dan Redis sebaiknya hanya accessible dari network internal apabila tidak ada kebutuhan lain.

---

# 13. Persistent Storage

Database:

```text
PostgreSQL
      ↓
Docker Volume
```

Storage aplikasi:

```text
storage/
      ↓
Persistent Volume
```

Tujuannya:

```text
Container Restart
        ↓
Data tetap ada
```

---

# 14. Database Volume

Jangan menyimpan database hanya di filesystem container.

Gunakan:

```text
postgres-data
```

sebagai persistent volume.

---

# 15. Application Storage

Jika Inventra menggunakan uploaded files:

```text
storage/app
```

harus memiliki persistence sesuai kebutuhan.

Jika file bersifat penting, pertimbangkan object storage sebagai tahap pengembangan berikutnya.

---

# 16. Queue

Jika Inventra menggunakan queue:

```text
Application
    ↓
Redis
    ↓
Queue Worker
```

Worker harus dapat restart secara otomatis.

---

# 17. Queue Worker

Production worker harus memiliki:

```text
Restart Policy
Graceful Shutdown
Timeout
Retry
Failed Job Handling
```

Jangan menjalankan worker hanya sebagai proses manual yang harus selalu dijaga developer.

---

# 18. Scheduler

Jika Inventra menggunakan Laravel Scheduler:

```text
Scheduler
   ↓
Scheduled Jobs
```

Pastikan hanya ada satu mekanisme scheduler production agar task tidak dijalankan berkali-kali secara tidak sengaja.

---

# 19. Database Migration

Deployment flow:

```text
Pull New Version
      ↓
Build Image
      ↓
Start Containers
      ↓
Run Migration
      ↓
Optimize Application
      ↓
Health Check
```

Migration harus dilakukan secara terkontrol.

---

# 20. Migration Safety

Sebelum migration production:

```text
Backup
 ↓
Migration
 ↓
Verification
```

Migration destructive harus memiliki recovery plan.

---

# 21. Laravel Optimization

Production build dapat menjalankan optimasi framework sesuai kebutuhan.

Contoh:

```text
Config Cache
Route Cache
View Cache
Event Cache
```

Gunakan hanya mekanisme yang sesuai dengan versi Laravel dan deployment setup Inventra.

---

# 22. Frontend Build

Vue/Inertia assets harus dibuild pada deployment/build stage.

Flow:

```text
Source
 ↓
npm install / npm ci
 ↓
npm run build
 ↓
Production Assets
```

Production tidak membutuhkan development server frontend.

---

# 23. Dependency Installation

PHP:

```text
composer install
```

Frontend:

```text
npm ci
```

Production image tidak perlu membawa development dependencies jika tidak dibutuhkan runtime.

---

# 24. Application Key

Production harus memiliki:

```text
APP_KEY
```

yang stabil.

Jangan menjalankan key generation setiap container restart.

Jika key berubah, data terenkripsi/session tertentu dapat bermasalah.

---

# 25. File Permissions

Container harus menjalankan application dengan user yang sesuai.

Periksa:

```text
storage/
bootstrap/cache/
```

harus writable oleh process yang memang membutuhkannya.

Hindari menjalankan seluruh application sebagai root tanpa alasan.

---

# 26. Health Check

Minimal:

```text
GET /health
```

Response:

```json
{
  "status": "ok"
}
```

Health check tidak boleh membocorkan:

```text
Database Password
Environment Variables
Internal Errors
Secrets
```

---

# 27. Container Health

Health check:

```text
Container
   ↓
Health Check
   ↓
Healthy
```

Jika service gagal:

```text
Unhealthy
   ↓
Restart / Alert
```

sesuai deployment environment.

---

# 28. Application Health

Health check dapat memeriksa dependency penting secara ringan:

```text
Application
Database
Redis
```

Namun endpoint public tidak perlu menampilkan detail internal dependency.

---

# 29. Logging

Docker/application logs harus dapat diakses.

Minimal:

```text
Application Log
Web Log
Queue Log
Database Log
```

Hindari menyimpan credential di log.

---

# 30. Log Rotation

Production tidak boleh membiarkan log tumbuh tanpa batas.

Gunakan:

```text
Log Rotation
Retention
Cleanup
```

sesuai kapasitas server.

---

# 31. Restart Policy

Service production harus memiliki restart policy yang sesuai.

Contoh:

```text
Application Crash
      ↓
Container Restart
```

Tetapi restart policy bukan pengganti monitoring dan root-cause analysis.

---

# 32. Reverse Proxy

Reverse proxy menangani:

```text
HTTP
HTTPS
Domain
TLS
Static Assets
Proxy
```

Flow:

```text
Internet
   ↓
Reverse Proxy
   ↓
Application
```

---

# 33. HTTPS

Production:

```text
HTTP
 ↓
HTTPS
```

Certificate dikelola melalui mekanisme deployment yang sesuai.

---

# 34. Domain

Production configuration harus mendukung:

```text
APP_URL
```

dengan domain production.

Contoh:

```text
https://inventra.example.com
```

Domain final ditentukan ketika deployment.

---

# 35. Database Security

Production database:

```text
Internet
   X
PostgreSQL
```

Ideal:

```text
Internet
   ↓
Reverse Proxy
   ↓
Application
   ↓
PostgreSQL
```

Database hanya exposed pada internal network.

---

# 36. Redis Security

Redis juga tidak perlu public.

```text
Internet
   X
Redis
```

Akses:

```text
Application
   ↓
Internal Docker Network
   ↓
Redis
```

---

# 37. Resource Limits

Server memiliki resource terbatas.

Periksa:

```text
CPU
RAM
Disk
Database Storage
Log Storage
```

Container dapat diberikan resource limit sesuai kebutuhan server.

---

# 38. VPS Deployment

Target deployment dapat berupa:

```text
VPS
```

Contoh architecture:

```text
VPS
│
├── Reverse Proxy
├── Inventra App
├── PostgreSQL
├── Redis
└── Volumes
```

---

# 39. Server Firewall

Public port minimal:

```text
22
80
443
```

SSH:

```text
Restricted
```

Database:

```text
Not Public
```

Redis:

```text
Not Public
```

Port final menyesuaikan infrastructure.

---

# 40. SSH Security

Production server sebaiknya:

```text
SSH Key
Disable Password Login
Disable Root Login where appropriate
```

dan menggunakan user deployment/admin yang sesuai.

---

# 41. Deployment Flow

Standard deployment:

```text
Developer
    ↓
Git Repository
    ↓
Pull / Checkout Release
    ↓
Build Image
    ↓
Run Tests
    ↓
Deploy Containers
    ↓
Migration
    ↓
Cache / Optimization
    ↓
Health Check
    ↓
Smoke Test
```

---

# 42. Zero / Low Downtime

Jika diperlukan:

```text
Old Version
     ↓
New Version Build
     ↓
New Container
     ↓
Health Check
     ↓
Traffic Switch
```

Untuk deployment awal VPS sederhana, short downtime dapat diterima selama terdokumentasi.

---

# 43. Rollback

Jika deployment gagal:

```text
Current Version
      ↓
Problem
      ↓
Stop / Switch
      ↓
Previous Version
      ↓
Migration Recovery if required
      ↓
Health Check
```

Rollback application dan rollback database adalah dua hal berbeda.

---

# 44. Database Backup

Sebelum deployment yang berpotensi mengubah schema:

```text
Database Backup
      ↓
Migration
```

Backup harus dapat digunakan untuk restore.

---

# 45. Restore Test

Backup dianggap valid setelah:

```text
Backup
 ↓
Restore
 ↓
Verify
```

Bukan hanya karena file backup berhasil dibuat.

---

# 46. Deployment Verification

Setelah deploy:

```text
[ ] Homepage
[ ] Login
[ ] Logout
[ ] Dashboard
[ ] Item
[ ] Warehouse
[ ] Stock
[ ] Approval
[ ] Asset
[ ] Report
[ ] Export
[ ] API
[ ] Audit
```

---

# 47. Smoke Test

Smoke test harus singkat.

Flow:

```text
Login
 ↓
Dashboard
 ↓
Open Item
 ↓
Open Inventory
 ↓
Create Transaction
 ↓
Verify
```

Tujuannya memastikan deployment dasar berfungsi.

---

# 48. Production Security

Pastikan:

```text
APP_ENV=production
APP_DEBUG=false
HTTPS=true
Secrets protected
Database private
Redis private
Security headers enabled
```

Mengikuti hasil Sprint 17.

---

# 49. Monitoring

Minimal monitoring:

```text
CPU
RAM
Disk
Container Status
Application Errors
Database Availability
```

Monitoring lebih advanced dapat ditambahkan kemudian.

---

# 50. Disk Monitoring

Perhatikan:

```text
Database
Logs
Docker Images
Uploaded Files
Backups
```

Disk penuh dapat menyebabkan aplikasi gagal walaupun CPU/RAM masih normal.

---

# 51. Docker Cleanup

Jangan sembarang menjalankan:

```text
docker system prune
```

di production.

Pastikan memahami image/container/volume yang akan terhapus sebelum cleanup.

Volume database sangat penting.

---

# 52. Versioning

Gunakan release version.

Contoh:

```text
v1.0.0
v1.0.1
v1.1.0
```

Deployment dapat menunjuk ke version tertentu sehingga rollback lebih mudah.

---

# 53. Deployment Documentation

Dokumentasikan:

```text
Server Setup
Docker Setup
Environment
Database
Deployment
Migration
Backup
Restore
Rollback
Troubleshooting
```

Dokumentasi harus memungkinkan developer lain menjalankan deployment tanpa bergantung pada orang yang membuat sistem.

---

# 54. Maintenance Guide

### "Container Laravel mati."

Trace:

```text
Container
 ↓
docker logs
 ↓
Application Log
 ↓
Environment
 ↓
Dependency
```

---

### "Database connection gagal."

Trace:

```text
App
 ↓
DB_HOST
 ↓
Docker Network
 ↓
PostgreSQL Container
 ↓
Credentials
```

---

### "Data hilang setelah container restart."

Periksa:

```text
Database Volume
Storage Volume
Volume Mount
```

---

### "Website bisa diakses tetapi asset Vue tidak muncul."

Periksa:

```text
npm build
 ↓
public/build
 ↓
Web Server
 ↓
Application URL
```

---

### "Deployment berhasil tetapi migration gagal."

Jangan langsung menghapus database.

Trace:

```text
Migration Error
 ↓
Database State
 ↓
Previous Migration
 ↓
Current Migration
 ↓
Backup / Recovery Plan
```

---

# 55. Code Documentation

Docker configuration juga mengikuti:

```text
docs/code-guide/00_CODE_DOCUMENTATION_STANDARD.md
```

Contoh:

```dockerfile
# --------------------------------------------------
# Production Application Image
#
# Purpose:
# Build the Inventra Laravel application runtime.
#
# Important:
# - Production secrets are injected at runtime.
# - .env is never copied into the image.
# - Frontend assets are built during image creation.
# --------------------------------------------------
```

Section penting harus memiliki komentar yang menjelaskan:

```text
Purpose
Why
Dependency
Important Warning
```

Bukan hanya menjelaskan syntax.

---

# 56. Expected Files

```text
.
├── Dockerfile
├── compose.yaml
├── .dockerignore
│
├── docker/
│   ├── nginx/
│   │   └── default.conf
│   │
│   ├── php/
│   │   └── php.ini
│   │
│   └── supervisor/
│       └── supervisord.conf
│
├── scripts/
│   ├── deploy.sh
│   ├── health-check.sh
│   └── backup.sh
│
└── docs/
    └── deployment/
        ├── DEPLOYMENT.md
        ├── BACKUP.md
        ├── RESTORE.md
        └── ROLLBACK.md
```

Tidak semua file wajib dibuat jika architecture final menggunakan pendekatan berbeda.

---

# 57. Git Branch

```text
feature/docker-deployment
```

---

# 58. Suggested Commits

```text
build(docker): add application dockerfile
build(docker): add compose configuration
build(docker): add production configuration
build(docker): configure application network
build(docker): configure persistent volumes
build(docker): configure redis
build(docker): configure queue worker
build(docker): configure scheduler
build(web): configure reverse proxy
build(web): configure https
build(app): configure production environment
build(app): optimize production build
build(health): add application health check
build(logging): configure production logging
build(server): configure firewall
build(server): harden ssh
ops(backup): add database backup procedure
ops(restore): document database restore
ops(deployment): add deployment script
ops(rollback): document rollback procedure
test(deployment): add deployment smoke test
docs(deployment): add deployment documentation
```

---

# 59. Acceptance Criteria

Sprint selesai apabila:

```text
1. Dockerfile tersedia.

2. Docker Compose configuration tersedia.

3. Inventra dapat dijalankan melalui Docker.

4. Application container berjalan.

5. PostgreSQL container berjalan.

6. Redis container berjalan jika digunakan.

7. Queue worker berjalan jika digunakan.

8. Scheduler berjalan jika digunakan.

9. Docker network terkonfigurasi.

10. Database menggunakan persistent volume.

11. Application storage menggunakan persistence sesuai kebutuhan.

12. Production secret tidak masuk Docker image.

13. .env tidak masuk repository.

14. Production configuration tersedia.

15. Frontend assets berhasil dibuild.

16. Health check tersedia.

17. Container restart behavior telah diuji.

18. Logging tersedia.

19. Log tidak menyimpan secret.

20. Reverse proxy tersedia.

21. HTTPS production dapat digunakan.

22. Database tidak diekspos secara public.

23. Redis tidak diekspos secara public.

24. Server firewall dikonfigurasi.

25. Resource server dapat dimonitor.

26. Database backup procedure tersedia.

27. Restore procedure tersedia.

28. Rollback procedure tersedia.

29. Deployment procedure terdokumentasi.

30. Smoke test berhasil.

31. Production security configuration telah diverifikasi.

32. Inventra dapat dideploy ulang secara konsisten.

33. Developer lain dapat memahami deployment tanpa bantuan pembuat awal.

34. Dokumentasi mengikuti Code Documentation Standard.
```

---

# 60. Definition of Done

```text
Docker
    ✓ Dockerfile
    ✓ Compose
    ✓ Network
    ✓ Volumes

Application
    ✓ Laravel
    ✓ Vue/Inertia Build
    ✓ Production Config

Infrastructure
    ✓ PostgreSQL
    ✓ Redis
    ✓ Queue
    ✓ Scheduler

Security
    ✓ Secrets Protected
    ✓ HTTPS
    ✓ Firewall
    ✓ Private Database
    ✓ Private Redis

Operations
    ✓ Health Check
    ✓ Logging
    ✓ Restart
    ✓ Monitoring

Database
    ✓ Persistent Storage
    ✓ Backup
    ✓ Restore
    ✓ Migration Strategy

Deployment
    ✓ Deploy Procedure
    ✓ Smoke Test
    ✓ Rollback Procedure

Documentation
    ✓ Deployment Guide
    ✓ Troubleshooting
    ✓ Code Comments

Git
    ✓ feature/docker-deployment
```

---

# 61. Final Deployment Model

```text
                         INTERNET
                            │
                           HTTPS
                            │
                            ▼
                  ┌──────────────────┐
                  │ Reverse Proxy    │
                  │ Nginx / Gateway  │
                  └────────┬─────────┘
                           │
                           ▼
                  ┌──────────────────┐
                  │ Inventra App     │
                  │ Laravel + Vue    │
                  │ Inertia          │
                  └───────┬──────────┘
                          │
             ┌────────────┼────────────┐
             │            │            │
             ▼            ▼            ▼
        PostgreSQL      Redis       Queue
             │            │            │
             ▼            ▼            ▼
        Persistent     Persistent    Worker
          Volume         Data
```

---

# 62. Deployment Principle

Inventra harus mengikuti prinsip:

```text
Build Once
Configure at Runtime
Deploy Consistently
Keep Data Persistent
Protect Secrets
Monitor Production
Backup Before Risky Changes
Know How to Roll Back
```

Docker bukan tujuan akhir.

Tujuannya adalah membuat:

```text
Development
      =
Testing
      =
Production
```

dari sisi environment dan dependency sebanyak mungkin, sehingga masalah **"di laptop saya jalan, di server tidak"** dapat diminimalkan.

---

# 63. Final Project Sprint Sequence

Dengan Sprint 19 selesai, roadmap sprint utama Inventra menjadi:

```text
SPRINT-01  Authentication
SPRINT-02  RBAC
SPRINT-03  Master Data
SPRINT-04  Item Management
SPRINT-05  Warehouse
SPRINT-06  Stock In
SPRINT-07  Stock Out
SPRINT-08  Stock Opname
SPRINT-09  Asset Management
SPRINT-10  Approval Workflow
SPRINT-11  Transaction History
SPRINT-12  Reporting
SPRINT-13  Dashboard
SPRINT-14  Audit Log
SPRINT-15  REST API
SPRINT-16  Export
SPRINT-17  Security Hardening
SPRINT-18  Testing & QA
SPRINT-19  Docker Deployment
```

Setiap sprint memiliki branch sendiri:

```text
feature/authentication
feature/rbac
feature/master-data
...
feature/docker-deployment
```

dan **Git push tetap kamu yang lakukan sendiri** setelah pekerjaan pada sprint/branch tersebut selesai.

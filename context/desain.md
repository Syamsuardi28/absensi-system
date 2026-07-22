# Desain Teknis — Sistem Absensi Sekolah Berbasis QR Code
**Technical Design Document (TDD) | Turunan dari `prd.md`**

| Field | Detail |
|---|---|
| Dokumen | Technical Design Document (TDD) |
| Versi | 1.0 |
| Referensi | `prd.md` v1.0 |
| Stack | Laravel 11 (API) + Vue.js 3 (SPA) |

---

## 1. Tujuan Dokumen

Dokumen ini menerjemahkan requirement pada `prd.md` menjadi rancangan teknis konkret: arsitektur sistem, skema database, kontrak API, struktur folder, alur proses (sequence), dan strategi keamanan — agar tim engineering dapat langsung mengimplementasikan tanpa ambiguitas.

---

## 2. Arsitektur Sistem

### 2.1 Gaya Arsitektur

Arsitektur **Client-Server** dengan Laravel sebagai **REST API backend** (stateless, token-based) dan Vue.js sebagai **SPA frontend** yang mengonsumsi API tersebut secara terpisah (decoupled).

```
┌──────────────────┐        HTTPS/REST (JSON)        ┌───────────────────────┐
│   Vue.js 3 SPA    │ ───────────────────────────────▶│   Laravel 11 REST API │
│  (Vite + Pinia)   │◀─────────────────────────────── │  (Sanctum Auth)       │
└──────────────────┘                                  └───────────┬───────────┘
                                                                   │
                                    ┌──────────────────────────────┼──────────────────────────────┐
                                    ▼                              ▼                              ▼
                            ┌───────────────┐           ┌──────────────────┐          ┌────────────────────┐
                            │ MySQL/Postgre │           │  Redis (Cache &   │          │  Queue Worker       │
                            │  (Database)   │           │  Queue Driver)    │          │  (Notifikasi Job)   │
                            └───────────────┘           └──────────────────┘          └──────────┬──────────┘
                                                                                                   ▼
                                                                                   ┌───────────────────────────────┐
                                                                                   │ SMTP (Email) & WhatsApp API    │
                                                                                   │ Provider (mis. Fonnte/WABlas)  │
                                                                                   └───────────────────────────────┘
```

### 2.2 Layered Architecture (Backend Laravel)

Mengikuti prinsip **Separation of Concerns**, backend dibagi menjadi layer berikut agar mudah di-maintain dan di-test:

```
Request → Route → Middleware (Auth/Role) → FormRequest (Validasi)
        → Controller → Service Layer (Business Logic)
        → Repository/Eloquent Model → Database

Response ← API Resource (Transformer) ← Service ← Controller
```

| Layer | Tanggung Jawab |
|---|---|
| **Route** | Definisi endpoint, grouping per versi (`/api/v1/...`) |
| **Middleware** | Autentikasi (Sanctum), otorisasi role, rate limiting |
| **FormRequest** | Validasi input per endpoint |
| **Controller** | Menerima request, memanggil Service, mengembalikan Resource |
| **Service** | Logika bisnis inti (mis. `AttendanceService`, `LeaveRequestService`) |
| **Repository (opsional)** | Abstraksi query kompleks agar Controller/Service tidak bergantung langsung ke Eloquent |
| **Model (Eloquent)** | Representasi tabel & relasi |
| **API Resource** | Transformasi output JSON yang konsisten |
| **Job/Listener** | Proses async (kirim notifikasi email/WA) |
| **Policy** | Otorisasi berbasis role per resource (mis. `AttendancePolicy`) |

### 2.3 Arsitektur Frontend (Vue.js)

Pola **Feature-based Structure** + **Composition API**, dengan pemisahan state management (Pinia), service layer (API client), dan komponen UI.

```
View (Page) → Component → Composable (business logic reusable)
            → Pinia Store (state) → API Service (axios) → Backend
```

---

## 3. Struktur Folder Proyek

### 3.1 Backend — Laravel

```
app/
├── Http/
│   ├── Controllers/Api/V1/
│   │   ├── AuthController.php
│   │   ├── AttendanceController.php
│   │   ├── LeaveRequestController.php
│   │   ├── QrCodeController.php
│   │   ├── ReportController.php
│   │   └── Admin/ (StudentController, TeacherController, ClassController, dst.)
│   ├── Requests/
│   │   ├── Attendance/ScanAttendanceRequest.php
│   │   ├── LeaveRequest/StoreLeaveRequest.php
│   │   └── ...
│   ├── Resources/
│   │   ├── AttendanceResource.php
│   │   ├── UserResource.php
│   │   └── LeaveRequestResource.php
│   └── Middleware/
│       └── EnsureRole.php
├── Models/
│   ├── User.php, Student.php, Teacher.php, ClassRoom.php
│   ├── Attendance.php, LeaveRequest.php, Schedule.php
│   └── Notification.php, AuditLog.php
├── Services/
│   ├── AttendanceService.php
│   ├── QrCodeService.php
│   ├── LeaveRequestService.php
│   └── NotificationService.php
├── Policies/
│   ├── AttendancePolicy.php
│   └── LeaveRequestPolicy.php
├── Jobs/
│   ├── SendAlpaNotificationJob.php
│   └── SendLeaveDecisionNotificationJob.php
├── Notifications/
│   ├── AlpaNotification.php
│   └── LeaveStatusNotification.php
└── Providers/

database/
├── migrations/
├── seeders/
└── factories/

routes/
├── api.php  (grouped: /api/v1/...)
```

### 3.2 Frontend — Vue.js

```
src/
├── assets/
├── components/
│   ├── common/ (BaseButton, BaseModal, BaseTable, ...)
│   ├── attendance/ (QrScanner.vue, AttendanceStatusBadge.vue)
│   └── leave/ (LeaveRequestForm.vue, LeaveRequestCard.vue)
├── composables/
│   ├── useQrScanner.js
│   ├── useAttendance.js
│   └── useLeaveRequest.js
├── layouts/
│   ├── AdminLayout.vue
│   ├── TeacherLayout.vue
│   └── StudentLayout.vue
├── views/
│   ├── auth/Login.vue
│   ├── dashboard/AdminDashboard.vue
│   ├── attendance/ScanPage.vue
│   ├── attendance/History.vue
│   ├── leave/LeaveRequestPage.vue
│   └── reports/ReportPage.vue
├── stores/ (Pinia)
│   ├── authStore.js
│   ├── attendanceStore.js
│   └── leaveRequestStore.js
├── services/ (API client per resource)
│   ├── api.js (axios instance + interceptor token)
│   ├── attendanceService.js
│   └── leaveRequestService.js
├── router/
│   └── index.js (route guard per role)
└── main.js
```

---

## 4. Skema Database (ERD)

### 4.1 Diagram Relasi (ERD Deskriptif)

```
roles (1) ────────< (N) users
users (1) ─────────< (N) attendances
users (1) ─────────< (N) leave_requests
users (1) ── 1:1 ── students   (jika role = student)
users (1) ── 1:1 ── teachers   (jika role = teacher)
school_years (1) ──< (N) classes
classes (1) ───────< (N) students
classes (1) ───────< (N) schedules
subjects (1) ───────< (N) schedules
teachers (1) ───────< (N) schedules
schedules (1) ──────< (N) attendances  (nullable, untuk absensi per sesi)
users (1) ──────────< (N) audit_logs
users (1) ──────────< (N) notifications
```

### 4.2 Detail Tabel & Tipe Data

**`roles`**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint, PK | |
| name | varchar(50), unique | super_admin, admin, teacher, student |

**`users`**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint, PK | |
| role_id | bigint, FK → roles | |
| name | varchar(150) | |
| email | varchar(150), unique | |
| password | varchar, hashed | |
| phone | varchar(20), nullable | |
| qr_token | varchar(191), unique, indexed | Token QR (UUID v4 / HMAC-signed) |
| status | enum('active','inactive') | default active |
| email_verified_at | timestamp, nullable | |
| created_at, updated_at | timestamp | |

**`school_years`**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint, PK | |
| name | varchar(20) | mis. "2025/2026" |
| start_date, end_date | date | |
| is_active | boolean | hanya 1 tahun ajaran aktif |

**`classes`**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint, PK | |
| school_year_id | bigint, FK | |
| name | varchar(50) | mis. "7A" |
| homeroom_teacher_id | bigint, FK → teachers, nullable | Wali kelas |

**`students`**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint, PK | |
| user_id | bigint, FK → users, unique | |
| class_id | bigint, FK → classes | |
| nis | varchar(30), unique | Nomor Induk Siswa |
| parent_name | varchar(150), nullable | |
| parent_phone | varchar(20), nullable | untuk notifikasi WA |
| parent_email | varchar(150), nullable | |

**`teachers`**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint, PK | |
| user_id | bigint, FK → users, unique | |
| nip | varchar(30), unique, nullable | |

**`subjects`**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint, PK | |
| name | varchar(100) | |
| code | varchar(20), unique | |

**`teacher_subject`** *(pivot N—N)*
| Kolom | Tipe |
|---|---|
| teacher_id | bigint, FK |
| subject_id | bigint, FK |

**`schedules`**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint, PK | |
| class_id | bigint, FK | |
| subject_id | bigint, FK | |
| teacher_id | bigint, FK | |
| day | enum('senin'..'sabtu') | |
| start_time, end_time | time | |

**`attendances`**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint, PK | |
| user_id | bigint, FK → users, indexed | |
| schedule_id | bigint, FK → schedules, nullable | null = absensi masuk/pulang sekolah |
| type | enum('self_in','self_out','session') | |
| status | enum('hadir','terlambat','izin','sakit','alpa') | |
| scan_time | datetime | |
| notes | text, nullable | |
| created_at, updated_at | timestamp | |
| **Index** | `(user_id, scan_time)`, `(schedule_id, status)` | Percepat query rekap |

**`leave_requests`**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint, PK | |
| user_id | bigint, FK → users | |
| type | enum('izin','sakit','cuti') | |
| start_date, end_date | date | |
| reason | text | |
| attachment_path | varchar, nullable | |
| status | enum('pending','approved','rejected') | default pending |
| approved_by | bigint, FK → users, nullable | |
| approved_at | timestamp, nullable | |
| rejection_note | text, nullable | |
| **Index** | `(status)`, `(user_id, start_date)` | |

**`notifications`**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint, PK | |
| user_id | bigint, FK | |
| type | varchar(100) | mis. "alpa_alert", "leave_decision" |
| channel | enum('email','whatsapp') | |
| payload | json | |
| status | enum('pending','sent','failed') | |
| sent_at | timestamp, nullable | |

**`audit_logs`**
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint, PK | |
| user_id | bigint, FK, nullable | pelaku aksi |
| action | varchar(100) | mis. "attendance.scan", "qr.regenerate" |
| model_type | varchar(150) | |
| model_id | bigint, nullable | |
| meta | json, nullable | detail perubahan |
| created_at | timestamp | |

---

## 5. Kontrak API Detail

Format response konsisten untuk seluruh endpoint:

```json
{
  "success": true,
  "message": "Berhasil mengambil data",
  "data": { }
}
```

Error response:
```json
{
  "success": false,
  "message": "Data tidak ditemukan",
  "errors": { }
}
```

### 5.1 Autentikasi

**POST `/api/v1/auth/login`**
```json
// Request
{ "email": "guru01@sekolah.sch.id", "password": "secret123" }

// Response 200
{
  "success": true,
  "message": "Login berhasil",
  "data": {
    "token": "1|abcdef123456...",
    "user": { "id": 5, "name": "Budi Santoso", "role": "teacher" }
  }
}
```
- Status: `200` sukses, `401` kredensial salah, `422` validasi gagal.

**POST `/api/v1/auth/logout`** — Revoke token aktif. Auth required.

### 5.2 Absensi

**POST `/api/v1/attendance/scan`**
```json
// Request
{ "qr_token": "b3f1c2...", "schedule_id": null }

// Response 200
{
  "success": true,
  "message": "Absensi berhasil dicatat",
  "data": {
    "user": "Ani Wijaya",
    "status": "terlambat",
    "scan_time": "2026-07-20T07:15:00+08:00"
  }
}
```
- `404` jika `qr_token` tidak valid/expired.
- `409 Conflict` jika user sudah absen pada sesi/hari yang sama.

**GET `/api/v1/attendance/history?user_id=&from=&to=`**
- Auth required. Siswa hanya bisa melihat data miliknya sendiri (dicek via Policy).

**GET `/api/v1/attendance/report?class_id=&period=monthly&month=2026-07`**
- Role: admin, teacher (khusus kelas yang diampu).
- Response berisi agregat per siswa: total hadir/izin/sakit/alpa.

### 5.3 Cuti/Izin

**POST `/api/v1/leave-requests`**
```json
// Request (multipart/form-data untuk attachment)
{
  "type": "sakit",
  "start_date": "2026-07-21",
  "end_date": "2026-07-22",
  "reason": "Demam tinggi",
  "attachment": "<file>"
}
```

**PATCH `/api/v1/leave-requests/{id}/approve`**
```json
{ "decision": "approved", "note": "Disetujui, semoga cepat sembuh" }
```
- Trigger `LeaveRequestApproved` event → job update status attendance + kirim notifikasi.

### 5.4 QR Code

**POST `/api/v1/qr/regenerate/{userId}`** — Role: admin. Invalidasi token lama, generate token baru, kembalikan gambar QR base64/URL.

### 5.5 Dashboard

**GET `/api/v1/dashboard/summary`**
```json
{
  "data": {
    "total_hadir": 320,
    "total_izin": 12,
    "total_sakit": 8,
    "total_alpa": 5,
    "tanggal": "2026-07-20"
  }
}
```

---

## 6. Sequence Diagram — Alur Kritis

### 6.1 Absensi Scan QR Code

```
Siswa/Guru        Vue.js (Scanner)        Laravel API          Database        Queue
    │                    │                     │                   │             │
    │  buka kamera       │                     │                   │             │
    │───────────────────▶│                     │                   │             │
    │  scan QR image      │                     │                   │             │
    │───────────────────▶│  POST /attendance/scan                   │             │
    │                    │────────────────────▶│  validasi token    │             │
    │                    │                     │──────────────────▶│             │
    │                    │                     │◀──────────────────│             │
    │                    │                     │  cek duplikasi     │             │
    │                    │                     │──────────────────▶│             │
    │                    │                     │  simpan attendance │             │
    │                    │                     │──────────────────▶│             │
    │                    │◀────────────────────│  response sukses   │             │
    │◀───────────────────│  tampilkan status   │                   │             │
    │                    │                     │  (jika alpa) dispatch job         │
    │                    │                     │────────────────────────────────▶│
    │                    │                     │                   │   kirim WA/Email
```

### 6.2 Pengajuan & Approval Izin

```
Siswa/Guru → [Form Izin] → API: POST /leave-requests → status=pending
Guru/Admin ← [Notifikasi masuk] ← Job: NotifyApprover
Guru/Admin → [Approve/Reject] → API: PATCH /leave-requests/{id}/approve
API → update leave_requests.status → update attendances.status (jika perlu)
API → dispatch Job: SendLeaveDecisionNotificationJob → Email/WA ke pemohon
```

---

## 7. Rancangan Halaman (Wireframe — Garis Besar)

| Halaman | Role Akses | Komponen Utama |
|---|---|---|
| Login | Semua | Form email + password |
| Dashboard | Admin/Guru | Cards ringkasan (hadir/izin/sakit/alpa), grafik tren mingguan |
| Scan Absensi | Guru/Siswa | Kamera scanner, feedback status real-time |
| Riwayat Kehadiran | Semua | Tabel filter tanggal, badge status berwarna |
| Absensi per Sesi | Guru | Daftar siswa per kelas/mapel, toggle status manual |
| Pengajuan Izin | Guru/Siswa | Form (jenis, tanggal, alasan, upload file) |
| Approval Izin | Guru/Admin | List pending, tombol approve/reject + catatan |
| Manajemen Master Data | Admin | CRUD tabel (kelas, siswa, guru, jadwal, mapel) |
| Generate/Cetak QR Code | Admin | List user + tombol generate/print QR |
| Laporan | Admin/Guru | Filter kelas/periode, tombol export Excel/PDF |

*Wireframe visual (mockup) dapat dibuat sebagai dokumen terpisah bila dibutuhkan (Figma/Canva).*

---

## 8. Strategi Keamanan (Detail Implementasi)

| Aspek | Implementasi |
|---|---|
| Autentikasi | Laravel Sanctum (token berbasis personal access token untuk SPA) |
| Otorisasi | Laravel Policy & Gate per resource (`AttendancePolicy`, `LeaveRequestPolicy`) |
| QR Token | UUID v4 di-generate via `Str::uuid()`, disimpan ter-hash/di-index unik; opsi tambahan: HMAC signature untuk validasi tanpa perlu selalu query DB |
| Rate Limiting | Middleware `throttle:scan` khusus endpoint `/attendance/scan` (mis. 10 request/menit per IP) |
| Validasi Input | Laravel FormRequest di setiap endpoint (whitelist field, tipe data, ukuran file upload) |
| Enkripsi | Kolom sensitif (nomor HP orang tua) memakai Laravel encrypted cast bila diperlukan kepatuhan privasi |
| Audit Trail | Observer/Event Listener otomatis mencatat ke `audit_logs` pada aksi kritikal |
| Transport | HTTPS wajib (HSTS) di production, CORS dikonfigurasi hanya untuk domain frontend resmi |
| Upload File | Validasi mime-type & ukuran maksimum untuk lampiran surat izin, disimpan di storage privat (bukan public disk) |

---

## 9. Strategi Deployment (Ringkas)

| Komponen | Rekomendasi |
|---|---|
| Backend | PHP-FPM + Nginx, dikelola via Docker/Supervisor untuk queue worker |
| Frontend | Build statis (Vite build) di-serve via Nginx/CDN, terpisah dari backend |
| Database | Managed MySQL/PostgreSQL dengan backup harian otomatis |
| Queue Worker | Supervisor menjalankan `php artisan queue:work` secara persisten |
| Scheduler | Cron `php artisan schedule:run` untuk job harian (mis. deteksi alpa akhir hari) |
| Environment | `.env` terpisah per environment (local/staging/production), secret tidak di-commit ke Git |
| CI/CD | Pipeline: lint → test (PHPUnit/Vitest) → build → deploy (bisa GitHub Actions) |

---

## 10. Hal yang Masih Perlu Dikonfirmasi

Beberapa keputusan teknis berikut bergantung pada jawaban **Open Questions** di `prd.md` dan perlu difinalisasi sebelum coding dimulai:

1. Provider WhatsApp API final (memengaruhi desain `NotificationService` & kredensial di `.env`).
2. Aturan jam masuk/pulang per kelas/jenjang (memengaruhi logika `AttendanceService::determineStatus()`).
3. Kebutuhan mode cetak QR Code fisik (memengaruhi desain halaman "Generate/Cetak QR").

---

## 11. Langkah Selanjutnya

1. Review desain ini bersama tim engineering & stakeholder sekolah.
2. Setelah disetujui, buat **migration file** Laravel sesuai skema Section 4.
3. Breakdown task per modul (Auth, QR, Attendance, Leave, Report, Notification) untuk sprint planning.
4. Mulai implementasi mengikuti struktur folder pada Section 3.

Beri tahu jika ingin saya lanjutkan ke tahap berikutnya — misalnya membuat file migration Laravel, atau breakdown task/sprint planning.

# ARCHITECTURE.md — Sistem Absensi Sekolah "SIAP"

> Dokumen referensi arsitektur untuk tim engineering. Turunan teknis dari `prd.md` dan `desain.md`, difokuskan sebagai panduan implementasi (bukan lagi dokumen perencanaan).

---

## 1. Ringkasan Sistem

| Aspek | Detail |
|---|---|
| Nama Produk | SIAP — Sistem Informasi Absensi Sekolah |
| Backend | Laravel 11 (PHP 8.2+) — REST API |
| Frontend | Vue.js 3 (Composition API) + Vite — SPA |
| Database | MySQL 8 / PostgreSQL 15 |
| Auth | Laravel Sanctum (token-based, SPA) |
| Queue/Cache | Redis |
| Metode Absensi | QR Code |

---

## 2. Prinsip Arsitektur

1. **Separation of Concerns** — backend (API) dan frontend (SPA) sepenuhnya terpisah, berkomunikasi hanya lewat REST API berformat JSON.
2. **Stateless API** — tidak ada session server-side untuk API; autentikasi murni berbasis token (Sanctum).
3. **Thin Controller, Fat Service** — Controller hanya menangani HTTP request/response; logika bisnis berada di Service layer.
4. **Single Source of Truth untuk Skema** — seluruh perubahan struktur data wajib lewat migration (lihat `SCHEMA.md`), tidak ada perubahan manual di database.
5. **Async untuk Proses Non-Kritis** — pengiriman notifikasi (email/WhatsApp) selalu lewat Queue Job, tidak pernah blocking response API.
6. **Konsistensi Konvensi** — seluruh konvensi penamaan & standar kode mengikuti `RULES.md`.

---

## 3. Diagram Arsitektur Sistem

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
                                                                                   └───────────────────────────────┘
```

---

## 4. Layered Architecture (Backend)

```
Request → Route → Middleware (Auth/Role) → FormRequest (Validasi)
        → Controller → Service (Business Logic) → Policy (Otorisasi)
        → Model/Eloquent → Database

Response ← API Resource ← Service ← Controller
```

| Layer | Lokasi | Tanggung Jawab | Larangan |
|---|---|---|---|
| Route | `routes/api.php` | Definisi endpoint, grouping versi & middleware | Jangan taruh logika di sini |
| Middleware | `app/Http/Middleware` | Auth, role check, rate limit | — |
| FormRequest | `app/Http/Requests` | Validasi & otorisasi dasar per request | Jangan taruh query DB |
| Controller | `app/Http/Controllers/Api/V1` | Terima request → panggil Service → kembalikan Resource | **Dilarang** menulis query/logika bisnis langsung |
| Service | `app/Services` | Logika bisnis inti, transaksi DB, orkestrasi Job | — |
| Policy | `app/Policies` | Aturan otorisasi per resource (mis. siswa hanya lihat data sendiri) | — |
| Model | `app/Models` | Relasi Eloquent, accessor/mutator, scope | Jangan taruh business logic kompleks |
| Job/Notification | `app/Jobs`, `app/Notifications` | Proses async (kirim email/WA) | — |
| API Resource | `app/Http/Resources` | Transformasi output JSON | — |

---

## 5. Struktur Folder

### 5.1 Backend (Laravel)

```
app/
├── Http/
│   ├── Controllers/Api/V1/
│   ├── Requests/
│   ├── Resources/
│   └── Middleware/
├── Models/
├── Services/
├── Policies/
├── Jobs/
├── Notifications/
└── Providers/

database/
├── migrations/
├── seeders/
└── factories/

routes/
└── api.php   (grouped: /api/v1/...)

tests/
├── Feature/
└── Unit/
```

### 5.2 Frontend (Vue.js)

```
src/
├── components/    (common/, attendance/, leave/)
├── composables/   (useQrScanner.js, useAttendance.js, ...)
├── layouts/       (AdminLayout, TeacherLayout, StudentLayout)
├── views/         (per fitur: auth/, dashboard/, attendance/, leave/, reports/)
├── stores/        (Pinia: authStore, attendanceStore, leaveRequestStore)
├── services/       (axios client per resource)
├── router/         (route guard per role)
└── main.js
```

---

## 6. Modul Sistem & Tanggung Jawab

| Modul | Backend (Service) | Frontend (Store/View) |
|---|---|---|
| Auth | `AuthService` | `authStore`, `views/auth/` |
| Absensi | `AttendanceService`, `QrCodeService` | `attendanceStore`, `views/attendance/` |
| Cuti/Izin | `LeaveRequestService` | `leaveRequestStore`, `views/leave/` |
| Master Data | `Admin\*Service` | `views/admin/` |
| Laporan | `ReportService` | `views/reports/` |
| Notifikasi | `NotificationService` + Job | — (trigger backend, tampil via toast di FE) |
| Audit Log | Observer otomatis di setiap Model kritikal | Tidak ada FE, akses via Admin panel |

---

## 7. Alur Data Kritis (Ringkas)

**Scan Absensi:** `Vue Scanner → POST /attendance/scan → AttendanceService::process() → validasi token & duplikasi → simpan record → (jika alpa) dispatch SendAlpaNotificationJob → response ke FE`

**Approval Izin:** `PATCH /leave-requests/{id}/approve → LeaveRequestService::decide() → update status → update attendance terkait (jika perlu) → dispatch SendLeaveDecisionNotificationJob`

Detail sequence diagram lengkap ada di `desain.md` Section 6.

---

## 8. Keamanan (Ringkasan — detail lihat `RULES.md` Section Security)

- Autentikasi: Laravel Sanctum, token expiry dikonfigurasi eksplisit.
- Otorisasi: wajib lewat Policy, tidak ada pengecekan role manual di Controller.
- QR Token: UUID v4, unik, invalidasi otomatis saat regenerasi.
- Rate limiting khusus endpoint `/attendance/scan`.
- HTTPS wajib di semua environment kecuali local development.

---

## 9. Environment & Konfigurasi

| Environment | Tujuan | Catatan |
|---|---|---|
| `local` | Development harian | Debug mode aktif, mail driver `log` |
| `staging` | UAT sebelum rilis | Data dummy, mail driver sandbox |
| `production` | Live | Debug mode **wajib** off, HTTPS wajib, backup harian aktif |

Variabel wajib di `.env`: `APP_KEY`, `DB_*`, `SANCTUM_STATEFUL_DOMAINS`, `QUEUE_CONNECTION=redis`, `MAIL_*`, `WHATSAPP_API_KEY`, `WHATSAPP_API_URL`.

---

## 10. Deployment

| Komponen | Setup |
|---|---|
| Backend | PHP-FPM + Nginx, `php artisan queue:work` via Supervisor, `php artisan schedule:run` via cron |
| Frontend | Build Vite statis, di-serve terpisah (Nginx/CDN) |
| Database | Managed instance dengan backup harian otomatis |
| CI/CD | Lint → Test (PHPUnit/Vitest) → Build → Deploy |

---

## 11. Dependensi Utama

| Kebutuhan | Package |
|---|---|
| QR Code Generator | `simplesoftwareio/simple-qrcode` |
| QR Scanner (FE) | `html5-qrcode` atau `vue-qrcode-reader` |
| Export Excel | `maatwebsite/excel` |
| Export PDF | `barryvdh/laravel-dompdf` |
| Auth | `laravel/sanctum` |
| State Management | `pinia` |
| HTTP Client (FE) | `axios` |

---

## 12. Referensi Silang

- Skema database lengkap → `SCHEMA.md`
- Standar kode, konvensi, dan aturan kontribusi → `RULES.md`
- Requirement produk & user story → `prd.md`
- Kontrak API & wireframe → `desain.md`

# RULES.md — Standar & Aturan Pengembangan "SIAP"

> Wajib dipatuhi seluruh kontributor (backend, frontend, reviewer) agar kode konsisten, aman, dan mudah dipelihara. Referensi arsitektur ada di `ARCHITECTURE.md`, referensi skema di `SCHEMA.md`.

---

## 1. Prinsip Umum

- **Clean Code** — kode harus terbaca tanpa perlu banyak komentar; komentar hanya untuk menjelaskan "kenapa", bukan "apa".
- **SOLID** — khususnya Single Responsibility (1 class = 1 tanggung jawab) dan Dependency Injection (jangan `new` Service langsung di Controller, gunakan constructor injection).
- **DRY** — logika yang dipakai berulang wajib diekstrak ke Service/Composable/Helper, bukan copy-paste.
- **KISS** — pilih solusi paling sederhana yang memenuhi requirement, hindari over-engineering.
- **Tidak ada logika bisnis di Controller/View/Component** — logika bisnis backend di Service, logika reusable frontend di Composable.

---

## 2. Konvensi Penamaan — Backend (Laravel/PHP)

Mengikuti **PSR-12** sebagai dasar, ditambah aturan berikut:

| Elemen | Konvensi | Contoh |
|---|---|---|
| Class | PascalCase | `AttendanceService`, `LeaveRequestController` |
| Method/Function | camelCase | `getAttendanceHistory()`, `determineStatus()` |
| Variabel | camelCase | `$scanTime`, `$leaveRequest` |
| Konstanta | UPPER_SNAKE_CASE | `MAX_SCAN_PER_MINUTE` |
| Tabel Database | snake_case, plural | `attendances`, `leave_requests` |
| Kolom Database | snake_case | `scan_time`, `qr_token` |
| Route (URL) | kebab-case | `/api/v1/leave-requests` |
| Nama File Migration | `yyyy_mm_dd_hhmmss_deskripsi` | `2026_07_21_000001_create_attendances_table.php` |
| Service Method Aksi | verb + noun | `AttendanceService::recordScan()` |
| Job | verb + noun + `Job` suffix | `SendAlpaNotificationJob` |
| Policy | model name + `Policy` suffix | `AttendancePolicy` |
| Enum value (DB) | snake_case, bahasa Indonesia sesuai domain | `hadir`, `terlambat`, `izin`, `sakit`, `alpa` |

**Aturan tambahan Controller:**
- Method mengikuti resource standar: `index`, `store`, `show`, `update`, `destroy`. Untuk aksi khusus (mis. approve), gunakan nama deskriptif: `approve()`, `regenerate()`.
- Controller **tidak boleh** memanggil Model/Eloquent langsung untuk query kompleks — panggil Service.

---

## 3. Konvensi Penamaan — Frontend (Vue.js)

| Elemen | Konvensi | Contoh |
|---|---|---|
| Nama Komponen (file) | PascalCase | `QrScanner.vue`, `LeaveRequestForm.vue` |
| Nama Komponen (pemakaian di template) | PascalCase atau kebab-case konsisten | `<QrScanner />` |
| Composable | camelCase, prefix `use` | `useAttendance.js`, `useQrScanner.js` |
| Pinia Store | camelCase, suffix `Store` | `authStore`, `attendanceStore` |
| Props | camelCase di script, kebab-case di template | `scanResult` → `:scan-result` |
| Event Emit | kebab-case | `emit('scan-success')` |
| File Service API | camelCase, suffix `Service` | `attendanceService.js` |
| Folder View | per fitur (feature-based) | `views/attendance/`, `views/leave/` |

**Aturan tambahan:**
- Gunakan **Composition API** (`<script setup>`) di seluruh komponen baru, bukan Options API.
- State global hanya lewat Pinia store — dilarang menyimpan state lintas komponen di `localStorage`/`sessionStorage` untuk data sensitif (token cukup di store + memory, gunakan HttpOnly cookie/Sanctum SPA jika memungkinkan).
- Panggilan API tidak boleh langsung dari komponen `.vue` — wajib lewat file `services/*.js`.

---

## 4. Standar API

- Semua endpoint di-versioning: `/api/v1/...`.
- Format response **wajib konsisten**:
```json
{ "success": true, "message": "...", "data": {} }
{ "success": false, "message": "...", "errors": {} }
```
- Gunakan HTTP status code yang benar: `200` sukses, `201` created, `204` no content, `401` unauthenticated, `403` forbidden, `404` not found, `409` conflict (mis. duplikasi scan absen), `422` validasi gagal, `500` server error.
- Validasi input **wajib** lewat FormRequest, dilarang validasi manual di Controller.
- Setiap response list (index) wajib mendukung pagination.
- Otorisasi resource wajib lewat Policy (`$this->authorize(...)`), bukan pengecekan `if ($user->role === ...)` manual berulang di Controller.

---

## 5. Standar Keamanan

| Aturan | Wajib |
|---|---|
| Autentikasi | Laravel Sanctum, token expiry dikonfigurasi eksplisit |
| QR Token | UUID v4 unik, regenerasi otomatis invalidasi token lama |
| Rate Limiting | Endpoint `/attendance/scan` dan `/auth/login` wajib throttle |
| Input Validation | Semua input via FormRequest, whitelist field |
| File Upload | Validasi mime-type & ukuran, simpan di disk privat (bukan public) |
| Secrets | Tidak boleh hard-code kredensial; semua lewat `.env`, `.env` **tidak boleh** di-commit |
| SQL Injection | Wajib gunakan Eloquent/Query Builder, dilarang raw query dengan string concatenation |
| XSS (Frontend) | Dilarang `v-html` dengan data dari user tanpa sanitasi |
| Audit Log | Aksi kritikal (scan, approval, regenerasi QR) wajib tercatat via Observer/Event |
| HTTPS | Wajib di staging & production |

---

## 6. Standar Testing

| Layer | Tools | Minimum Coverage |
|---|---|---|
| Backend Unit | PHPUnit/Pest | Service layer (business logic) wajib ada test |
| Backend Feature | PHPUnit/Pest | Setiap endpoint API wajib punya minimal 1 happy path + 1 edge case test |
| Frontend Unit | Vitest | Composable & store logic |
| Manual QA | Checklist per fitur | Wajib sebelum merge ke `main`/`develop` |

**Edge case wajib diuji untuk modul Absensi:**
- Scan QR Code yang sudah expired/invalid.
- Scan duplikat di sesi/hari yang sama (harus return `409`).
- Scan di luar jam sekolah (status harus otomatis "Terlambat").

**Edge case wajib diuji untuk modul Izin:**
- Approval oleh user yang tidak berwenang (harus `403`).
- Pengajuan dengan tanggal mulai > tanggal selesai (harus `422`).

---

## 7. Git Workflow

**Branching:**
- `main` — kode production-ready, hanya menerima merge dari `develop` via release.
- `develop` — integrasi fitur, basis untuk staging.
- `feature/nama-fitur` — pengembangan fitur baru dari `develop`.
- `fix/nama-bug` — perbaikan bug.
- `hotfix/nama-hotfix` — perbaikan darurat langsung dari `main`.

**Commit Message** — mengikuti [Conventional Commits](https://www.conventionalcommits.org/):
```
feat(attendance): tambah endpoint scan QR Code
fix(leave-request): perbaiki validasi tanggal mulai izin
docs(readme): update panduan instalasi
refactor(service): pisahkan logic status terlambat ke AttendanceService
test(attendance): tambah test untuk duplikasi scan
```

**Pull Request:**
- Wajib deskripsi jelas: apa yang diubah, kenapa, cara testing.
- Wajib lolos CI (lint + test) sebelum bisa direview.
- Wajib minimal 1 approval sebelum merge ke `develop`.
- Dilarang merge langsung ke `main` tanpa melalui `develop` → staging → release.

---

## 8. Code Review Checklist

- [ ] Tidak ada logika bisnis di Controller/Component.
- [ ] Validasi input lengkap (FormRequest/frontend form validation).
- [ ] Otorisasi resource sudah benar (Policy diterapkan).
- [ ] Tidak ada credential/secret ter-hardcode.
- [ ] Response API mengikuti format standar (Section 4).
- [ ] Query database efisien (tidak N+1, sudah pakai eager loading bila perlu).
- [ ] Ada test untuk logika baru/berubah.
- [ ] Penamaan mengikuti konvensi Section 2/3.
- [ ] Tidak ada `console.log`/`dd()`/`dump()` tertinggal di kode.

---

## 9. Dokumentasi Kode

- Setiap Service class wajib PHPDoc singkat di atas method publik yang menjelaskan tujuan (bukan mengulang nama method).
- Setiap endpoint API baru wajib didokumentasikan (update `desain.md` Section 5 atau dokumentasi API terpisah bila proyek berkembang, mis. OpenAPI/Postman collection).
- Perubahan skema database wajib disertai update di `SCHEMA.md`.

---

## 10. Larangan Eksplisit

- ❌ Query database langsung di Controller atau di komponen Vue.
- ❌ Menyimpan token/data sensitif di `localStorage`.
- ❌ Mengubah struktur database tanpa migration.
- ❌ Mengirim notifikasi (email/WA) secara synchronous di request cycle utama.
- ❌ Menonaktifkan validasi/otorisasi "sementara" tanpa tiket & persetujuan reviewer.
- ❌ Commit file `.env` atau kredensial apa pun ke Git.

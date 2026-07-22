# PRD — Sistem Absensi Sekolah Berbasis QR Code
**Stack: Laravel (Backend/API) + Vue.js (Frontend SPA)**

| Field | Detail |
|---|---|
| Dokumen | Product Requirements Document (PRD) |
| Versi | 1.0 |
| Status | Draft — untuk direview sebelum masuk fase implementasi |
| Target Pengguna | Sekolah (Guru & Siswa) |
| Metode Absensi | QR Code |

---

## 1. Ringkasan Eksekutif

Sistem Absensi Sekolah adalah aplikasi web berbasis **Laravel (REST API)** dan **Vue.js (SPA)** yang digunakan untuk mencatat kehadiran guru dan siswa menggunakan **QR Code**, dilengkapi dengan manajemen cuti/izin, laporan & dashboard analitik, serta notifikasi otomatis melalui email dan WhatsApp.

Sistem ini menggantikan proses absensi manual (buku/kertas) yang rawan human error, sulit direkap, dan tidak real-time, dengan sistem digital yang cepat, akurat, dan dapat diaudit.

---

## 2. Problem Statement

Sekolah saat ini mencatat kehadiran guru dan siswa secara manual (tanda tangan di kertas absensi), yang menimbulkan masalah:

- Rekap kehadiran memakan waktu lama dan rawan kesalahan input ulang ke sistem lain (misalnya rapor/payroll guru).
- Wali kelas/Tata Usaha tidak memiliki data kehadiran real-time untuk memantau siswa yang sering terlambat/tidak hadir.
- Orang tua tidak mendapat informasi kehadiran anak secara langsung.
- Proses pengajuan izin/sakit dilakukan secara manual (surat kertas) tanpa histori digital.

**Dampak jika tidak diselesaikan:** beban administratif Tata Usaha tinggi, potensi manipulasi data kehadiran, dan lambatnya deteksi masalah kedisiplinan siswa.

---

## 3. Tujuan (Goals)

1. Mempercepat proses absensi harian (guru & siswa) menjadi di bawah 5 detik per orang menggunakan scan QR Code.
2. Menyediakan data kehadiran real-time yang dapat diakses oleh Admin, Guru, dan Wali Kelas.
3. Mengurangi kesalahan rekap manual hingga mendekati 0% melalui pencatatan otomatis di database.
4. Menyediakan alur pengajuan & approval cuti/izin secara digital dengan histori lengkap.
5. Mengirimkan notifikasi otomatis (email/WhatsApp) kepada orang tua/wali saat siswa tidak hadir tanpa keterangan.

---

## 4. Non-Goals (Di Luar Cakupan v1)

| Non-Goal | Alasan |
|---|---|
| Absensi via Face Recognition / Fingerprint | Tidak dipilih sebagai metode utama pada versi ini; dapat menjadi fase berikutnya. |
| Integrasi payroll/gaji guru | Belum termasuk dalam scope fitur yang diminta. |
| Aplikasi mobile native (Android/iOS) | v1 fokus pada web app responsif (PWA-ready), aplikasi native menyusul. |
| Modul akademik (nilai, rapor, kurikulum) | Di luar cakupan sistem absensi. |
| Multi-sekolah/multi-tenant (SaaS) | Target pengguna v1 adalah satu institusi sekolah tunggal. |

---

## 5. Peran Pengguna (User Roles)

| Role | Deskripsi |
|---|---|
| **Super Admin** | Mengelola seluruh master data (sekolah, tahun ajaran, kelas, mapel, user). Full access. |
| **Admin/Tata Usaha** | Mengelola data guru, siswa, kelas, jadwal, approval izin, generate QR Code, laporan. |
| **Guru** | Melakukan absensi diri (scan QR masuk/pulang), melakukan absensi siswa per kelas/mapel, mengajukan izin/cuti, melihat laporan kelas yang diampu. |
| **Wali Kelas** | Sama seperti guru + akses laporan rekap kehadiran khusus kelas yang diampu. |
| **Siswa** | Melakukan absensi (scan QR masuk/pulang), mengajukan izin/sakit, melihat riwayat kehadiran pribadi. |
| **Orang Tua/Wali** *(read-only, opsional v1.1)* | Menerima notifikasi & melihat riwayat kehadiran anak. |

---

## 6. User Stories

**Siswa**
- Sebagai siswa, saya ingin melakukan scan QR Code saat tiba di sekolah agar kehadiran saya tercatat otomatis tanpa perlu tanda tangan manual.
- Sebagai siswa, saya ingin mengajukan izin/sakit secara online lengkap dengan upload surat keterangan, agar guru dapat menyetujuinya tanpa surat fisik.
- Sebagai siswa, saya ingin melihat riwayat kehadiran saya sendiri (hadir/izin/sakit/alpa) per bulan.

**Guru**
- Sebagai guru, saya ingin melakukan absensi kehadiran saya sendiri via QR Code saat datang dan pulang.
- Sebagai guru, saya ingin mengambil absensi siswa di kelas yang saya ajar per sesi mata pelajaran, agar tercatat siapa saja yang hadir saat jam pelajaran tersebut.
- Sebagai guru, saya ingin menyetujui/menolak pengajuan izin siswa di kelas saya beserta alasannya.

**Admin/Tata Usaha**
- Sebagai admin, saya ingin men-generate dan mencetak QR Code unik untuk setiap guru dan siswa.
- Sebagai admin, saya ingin melihat dashboard rekap kehadiran harian seluruh sekolah (jumlah hadir, izin, sakit, alpa).
- Sebagai admin, saya ingin mengekspor laporan kehadiran bulanan dalam format Excel/PDF per kelas atau per guru.
- Sebagai admin, saya ingin sistem otomatis mengirim notifikasi ke orang tua saat siswa tercatat alpa (tanpa keterangan).

**Edge Case**
- Sebagai siswa, jika saya scan QR Code dua kali di hari yang sama, sistem harus menolak duplikasi dan menampilkan pesan bahwa saya sudah absen.
- Sebagai admin, jika QR Code hilang/rusak, saya bisa generate ulang QR Code baru dan QR lama otomatis invalid.
- Sebagai guru, jika saya scan di luar jam sekolah yang ditentukan, sistem mencatat status "Terlambat" sesuai konfigurasi jam masuk.

---

## 7. Functional Requirements

### 7.1 Must-Have (P0)

| # | Requirement | Acceptance Criteria |
|---|---|---|
| P0-1 | Autentikasi & manajemen role (Super Admin, Admin, Guru, Siswa) | - Login dengan email/username + password<br>- Role-based access control (middleware/policy)<br>- Reset password via email |
| P0-2 | Generate QR Code unik per guru & siswa | - Setiap user memiliki 1 QR Code unik (berbasis UUID/token, bukan ID sekuensial)<br>- QR Code dapat dicetak (kartu/lanyard) dan diregenerasi kapan saja (QR lama otomatis invalid) |
| P0-3 | Absensi Masuk/Pulang via Scan QR Code | - Guru/siswa scan QR menggunakan kamera device (web-based scanner)<br>- Sistem mencatat waktu scan, status (Hadir/Terlambat) berdasarkan jam konfigurasi<br>- Duplikasi scan di hari & sesi yang sama ditolak dengan pesan jelas |
| P0-4 | Absensi Siswa per Sesi oleh Guru | - Guru memilih kelas & mapel, sistem menampilkan daftar siswa<br>- Guru dapat menandai status per siswa (Hadir/Izin/Sakit/Alpa) atau siswa scan mandiri<br>- Data tersimpan per sesi jam pelajaran |
| P0-5 | Manajemen Cuti & Izin | - Siswa/guru mengajukan izin dengan jenis (Sakit/Izin/Cuti), tanggal, keterangan, lampiran (opsional)<br>- Guru/Admin dapat approve/reject dengan catatan<br>- Status pengajuan: Pending/Approved/Rejected<br>- Riwayat pengajuan tersimpan lengkap |
| P0-6 | Dashboard & Laporan Kehadiran | - Dashboard ringkasan harian (total hadir/izin/sakit/alpa) per role<br>- Laporan dapat difilter per kelas, per periode (harian/mingguan/bulanan)<br>- Ekspor laporan ke Excel & PDF |
| P0-7 | Notifikasi Email & WhatsApp | - Notifikasi otomatis ke orang tua/wali saat siswa berstatus Alpa<br>- Notifikasi approval/reject pengajuan izin ke pemohon<br>- Konfigurasi template notifikasi oleh Admin |
| P0-8 | Master Data | - CRUD Tahun Ajaran, Kelas, Mata Pelajaran, Jadwal, Guru, Siswa |
| P0-9 | Audit Log | - Semua aksi kritikal (absensi, approval, generate QR) tercatat dengan timestamp & pelaku |

### 7.2 Nice-to-Have (P1)

- Dashboard khusus untuk Orang Tua (read-only) melihat kehadiran anak.
- Statistik & grafik tren kehadiran (chart mingguan/bulanan) per siswa/kelas.
- Pengaturan toleransi keterlambatan (grace period) per sekolah.
- Export laporan terjadwal otomatis (dikirim email tiap akhir bulan ke Kepala Sekolah).
- Fitur "absen massal" oleh guru untuk kondisi darurat (QR Scanner tidak berfungsi).

### 7.3 Future Considerations (P2)

- Metode absensi tambahan: Face Recognition, Fingerprint device integration.
- Aplikasi mobile native (Flutter/React Native) dengan push notification.
- Multi-sekolah/multi-tenant (SaaS model).
- Integrasi dengan sistem akademik (nilai, rapor) dan payroll guru.

---

## 8. Alur Sistem Utama (Attendance Flow)

**Absensi Masuk (Guru/Siswa):**
1. User membuka halaman scanner (web camera) atau Admin men-scan menggunakan scanner device di gerbang/kelas.
2. QR Code di-scan → sistem validasi token QR (valid/expired/user aktif).
3. Sistem cek apakah user sudah absen masuk hari ini pada sesi tersebut → jika belum, catat waktu & tentukan status (Hadir/Terlambat) berdasarkan jam konfigurasi.
4. Sistem menyimpan record kehadiran & menampilkan konfirmasi (nama, waktu, status) di layar scanner.
5. Jika status Alpa terdeteksi di akhir sesi (tidak ada scan sama sekali), trigger job notifikasi ke orang tua.

**Pengajuan Izin/Cuti:**
1. Siswa/Guru mengisi form pengajuan (jenis, tanggal, alasan, lampiran).
2. Sistem mengirim notifikasi ke Guru/Wali Kelas (untuk siswa) atau Admin (untuk guru).
3. Approver melakukan approve/reject beserta catatan.
4. Status kehadiran otomatis di-update sesuai keputusan (Izin/Sakit tercatat, bukan Alpa).
5. Notifikasi hasil keputusan dikirim ke pemohon.

---

## 9. Arsitektur & Tech Stack

| Layer | Teknologi | Keterangan |
|---|---|---|
| Backend/API | **Laravel 11** (PHP 8.2+) | REST API, autentikasi via Laravel Sanctum |
| Frontend | **Vue.js 3** (Composition API) + Vite | SPA, konsumsi REST API |
| State Management | Pinia | Manajemen state Vue |
| UI Framework | Tailwind CSS + komponen (mis. PrimeVue/HeadlessUI) | Disesuaikan preferensi tim |
| Database | **MySQL/PostgreSQL** | Menggunakan Laravel Migration |
| Queue/Job | Laravel Queue (database/redis driver) | Untuk proses notifikasi async |
| Cache | Redis (opsional) | Cache dashboard/laporan agregat |
| QR Code Generator | `simplesoftwareio/simple-qrcode` (Laravel) | Generate QR berbasis UUID token |
| QR Scanner (Frontend) | `vue-qrcode-reader` atau `html5-qrcode` | Akses kamera device via browser |
| Notifikasi Email | Laravel Notification + Mail (SMTP) | |
| Notifikasi WhatsApp | Integrasi API pihak ketiga (mis. Fonnte/WABlas/WhatsApp Business API) | Perlu konfirmasi provider (lihat Open Questions) |
| Export Laporan | `maatwebsite/excel` (Excel), `barryvdh/laravel-dompdf` (PDF) | |
| Autentikasi | Laravel Sanctum (SPA token-based) | |
| Testing | PHPUnit/Pest (backend), Vitest (frontend) | |

**Alasan pemilihan stack:** Laravel dipilih karena ekosistem matang untuk RBAC, queue, notification, dan reporting yang dibutuhkan sistem ini; Vue.js dipilih sesuai instruksi awal dan cocok untuk SPA ringan dengan kebutuhan real-time scanner QR.

---

## 10. Rancangan Struktur Database (Ringkasan Entitas)

| Tabel | Kolom Utama | Relasi |
|---|---|---|
| `users` | id, name, email, password, role_id, phone, qr_token (unique), status | 1—1 ke `teachers`/`students` |
| `roles` | id, name (super_admin, admin, teacher, student) | 1—N ke `users` |
| `school_years` | id, name, start_date, end_date, is_active | 1—N ke `classes` |
| `classes` | id, name, school_year_id, homeroom_teacher_id | 1—N ke `students` |
| `students` | id, user_id, class_id, nis, parent_name, parent_phone, parent_email | N—1 ke `classes` |
| `teachers` | id, user_id, nip, subject_ids | N—N ke `subjects` |
| `subjects` | id, name, code | N—N ke `teachers` |
| `schedules` | id, class_id, subject_id, teacher_id, day, start_time, end_time | N—1 ke `classes`, `subjects` |
| `attendances` | id, user_id, schedule_id (nullable), type (self/session), status (hadir/terlambat/izin/sakit/alpa), scan_time, notes | N—1 ke `users` |
| `leave_requests` | id, user_id, type (izin/sakit/cuti), start_date, end_date, reason, attachment_path, status (pending/approved/rejected), approved_by, approved_at | N—1 ke `users` |
| `notifications` | id, user_id, type, channel (email/whatsapp), payload, sent_at, status | N—1 ke `users` |
| `audit_logs` | id, user_id, action, model_type, model_id, meta, created_at | N—1 ke `users` |

**Catatan indexing:** index pada `qr_token` (unique), `attendances(user_id, scan_time)` untuk query rekap cepat, `leave_requests(status)` untuk filter approval pending.

---

## 11. Gambaran API Endpoint (Contoh)

| Method | Endpoint | Deskripsi |
|---|---|---|
| POST | `/api/auth/login` | Login user, return token Sanctum |
| POST | `/api/attendance/scan` | Submit hasil scan QR Code |
| GET | `/api/attendance/history` | Riwayat kehadiran user (filter tanggal) |
| GET | `/api/attendance/report` | Laporan rekap (filter kelas/periode), khusus Admin/Guru |
| POST | `/api/leave-requests` | Ajukan izin/cuti |
| PATCH | `/api/leave-requests/{id}/approve` | Approve/reject pengajuan izin |
| GET | `/api/students` / `POST/PUT/DELETE` | CRUD data siswa |
| GET | `/api/teachers` / `POST/PUT/DELETE` | CRUD data guru |
| POST | `/api/qr/regenerate/{userId}` | Regenerasi QR Code user |
| GET | `/api/dashboard/summary` | Ringkasan data dashboard |

Setiap endpoint mengikuti standar response konsisten (status code, message, data) serta validasi request menggunakan Laravel Form Request.

---

## 12. Keamanan

- Autentikasi berbasis token (Laravel Sanctum), token expiry & refresh policy jelas.
- QR Code berbasis token acak (UUID/HMAC-signed), bukan ID sekuensial, untuk mencegah tebakan/spoofing.
- Rate limiting pada endpoint scan absensi untuk mencegah abuse.
- Validasi role-based access control di setiap endpoint (policy/gate Laravel).
- Enkripsi data sensitif (nomor telepon orang tua) sesuai kebutuhan privasi.
- Audit log untuk seluruh aksi kritikal (approval izin, regenerasi QR, absensi manual oleh admin).
- HTTPS wajib di lingkungan production.

---

## 13. Success Metrics

**Leading Indicators**
- ≥ 90% guru & siswa berhasil melakukan absensi mandiri via QR dalam 2 minggu pertama peluncuran.
- Rata-rata waktu proses scan absensi < 5 detik per orang.
- ≥ 80% pengajuan izin diproses (approve/reject) dalam waktu 1x24 jam.

**Lagging Indicators**
- Pengurangan waktu rekap kehadiran bulanan oleh Tata Usaha sebesar ≥ 70% dibanding proses manual.
- Penurunan jumlah kasus "alpa tanpa diketahui orang tua" mendekati 0% (karena notifikasi otomatis).
- Tingkat kepuasan pengguna (guru/TU) terhadap sistem ≥ 4/5 dalam survei pasca-implementasi (1 bulan).

---

## 14. Open Questions

| Pertanyaan | Ditujukan ke | Blocking? |
|---|---|---|
| Provider WhatsApp API apa yang akan digunakan (Fonnte, WABlas, WhatsApp Business API resmi)? Ada budget/kontrak? | Stakeholder/Sekolah | Ya, sebelum implementasi modul notifikasi WA |
| Apakah jam masuk/pulang sekolah seragam untuk semua kelas, atau berbeda per jenjang/kelas? | Stakeholder/Sekolah | Ya, memengaruhi logika status Terlambat |
| Apakah dibutuhkan mode offline (misal saat internet gerbang sekolah down)? | Engineering | Tidak, dapat didesain di fase berikutnya |
| Apakah QR Code dicetak di kartu fisik (perlu desain kartu) atau cukup ditampilkan di aplikasi siswa? | Design/Stakeholder | Tidak, tapi memengaruhi UX awal rollout |
| Apakah dibutuhkan akses Orang Tua (login terpisah) di v1, atau notifikasi saja sudah cukup? | Stakeholder | Tidak, saat ini di-scope sebagai notifikasi email/WA saja (P1 untuk dashboard read-only) |

---

## 15. Timeline & Phasing (Usulan)

| Fase | Cakupan | Estimasi |
|---|---|---|
| **Fase 1 (MVP)** | Autentikasi, master data, generate QR, absensi scan (guru & siswa), dashboard dasar | 3-4 minggu |
| **Fase 2** | Manajemen cuti/izin, laporan ekspor Excel/PDF | 2 minggu |
| **Fase 3** | Notifikasi email & WhatsApp, audit log | 2 minggu |
| **Fase 4** | Refinement UX, testing menyeluruh, dashboard analitik lanjutan (P1) | 1-2 minggu |

*Catatan: estimasi bersifat indikatif, perlu disesuaikan dengan ukuran tim dan hasil jawaban Open Questions.*

---

## 16. Langkah Selanjutnya

Setelah PRD ini direview dan disetujui, dokumen lanjutan yang disarankan:
1. **Technical Design Document (TDD)** — detail skema database final, ERD, dan kontrak API lengkap.
2. **UI/UX Wireframe** — alur halaman scanner, dashboard, form pengajuan izin.
3. **Breakdown task engineering** — per modul (Auth, QR, Attendance, Leave, Report, Notification) untuk sprint planning.

Silakan beri tahu jika ada bagian yang perlu disesuaikan atau diperluas (misalnya detail ERD lebih dalam, kontrak API lengkap per endpoint, atau breakdown task untuk sprint pertama).

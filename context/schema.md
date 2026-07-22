# SCHEMA.md — Skema Database Sistem "SIAP"

> Referensi definitif struktur database. Setiap perubahan skema **wajib** melalui migration Laravel dan wajib meng-update dokumen ini di PR yang sama. Lihat `ARCHITECTURE.md` untuk konteks sistem dan `RULES.md` untuk aturan penamaan.

---

## 1. Diagram Relasi (ERD)

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
teachers (N) ──────< teacher_subject >────── (N) subjects
schedules (1) ──────< (N) attendances  (nullable — null = absensi masuk/pulang sekolah)
users (1) ──────────< (N) audit_logs
users (1) ──────────< (N) notifications
```

---

## 2. Definisi Tabel

### 2.1 `roles`

| Kolom | Tipe | Constraint |
|---|---|---|
| id | bigint unsigned | PK, auto increment |
| name | varchar(50) | unique, not null — `super_admin`, `admin`, `teacher`, `student` |
| created_at, updated_at | timestamp | nullable |

---

### 2.2 `users`

| Kolom | Tipe | Constraint |
|---|---|---|
| id | bigint unsigned | PK |
| role_id | bigint unsigned | FK → `roles.id`, not null |
| name | varchar(150) | not null |
| email | varchar(150) | unique, not null |
| password | varchar(255) | not null, hashed (bcrypt) |
| phone | varchar(20) | nullable |
| qr_token | varchar(191) | unique, indexed, not null — UUID v4 |
| status | enum('active','inactive') | default `active` |
| email_verified_at | timestamp | nullable |
| remember_token | varchar(100) | nullable |
| created_at, updated_at | timestamp | nullable |

**Index:** `unique(email)`, `unique(qr_token)`, `index(role_id)`

---

### 2.3 `school_years`

| Kolom | Tipe | Constraint |
|---|---|---|
| id | bigint unsigned | PK |
| name | varchar(20) | not null, mis. "2025/2026" |
| start_date | date | not null |
| end_date | date | not null |
| is_active | boolean | default false — hanya 1 baris boleh `true` (enforce di Service layer) |
| created_at, updated_at | timestamp | nullable |

---

### 2.4 `classes`

| Kolom | Tipe | Constraint |
|---|---|---|
| id | bigint unsigned | PK |
| school_year_id | bigint unsigned | FK → `school_years.id`, not null |
| name | varchar(50) | not null, mis. "7A" |
| homeroom_teacher_id | bigint unsigned | FK → `teachers.id`, nullable |
| created_at, updated_at | timestamp | nullable |

**Index:** `index(school_year_id)`

---

### 2.5 `students`

| Kolom | Tipe | Constraint |
|---|---|---|
| id | bigint unsigned | PK |
| user_id | bigint unsigned | FK → `users.id`, unique, not null |
| class_id | bigint unsigned | FK → `classes.id`, not null |
| nis | varchar(30) | unique, not null |
| parent_name | varchar(150) | nullable |
| parent_phone | varchar(20) | nullable — untuk notifikasi WhatsApp |
| parent_email | varchar(150) | nullable |
| created_at, updated_at | timestamp | nullable |

**Index:** `unique(user_id)`, `unique(nis)`, `index(class_id)`

---

### 2.6 `teachers`

| Kolom | Tipe | Constraint |
|---|---|---|
| id | bigint unsigned | PK |
| user_id | bigint unsigned | FK → `users.id`, unique, not null |
| nip | varchar(30) | unique, nullable |
| created_at, updated_at | timestamp | nullable |

---

### 2.7 `subjects`

| Kolom | Tipe | Constraint |
|---|---|---|
| id | bigint unsigned | PK |
| name | varchar(100) | not null |
| code | varchar(20) | unique, not null |
| created_at, updated_at | timestamp | nullable |

---

### 2.8 `teacher_subject` *(pivot N—N)*

| Kolom | Tipe | Constraint |
|---|---|---|
| teacher_id | bigint unsigned | FK → `teachers.id` |
| subject_id | bigint unsigned | FK → `subjects.id` |

**Index:** `primary(teacher_id, subject_id)`

---

### 2.9 `schedules`

| Kolom | Tipe | Constraint |
|---|---|---|
| id | bigint unsigned | PK |
| class_id | bigint unsigned | FK → `classes.id`, not null |
| subject_id | bigint unsigned | FK → `subjects.id`, not null |
| teacher_id | bigint unsigned | FK → `teachers.id`, not null |
| day | enum('senin','selasa','rabu','kamis','jumat','sabtu') | not null |
| start_time | time | not null |
| end_time | time | not null |
| created_at, updated_at | timestamp | nullable |

**Index:** `index(class_id, day)`

---

### 2.10 `attendances`

| Kolom | Tipe | Constraint |
|---|---|---|
| id | bigint unsigned | PK |
| user_id | bigint unsigned | FK → `users.id`, not null |
| schedule_id | bigint unsigned | FK → `schedules.id`, nullable — null berarti absensi masuk/pulang sekolah, terisi berarti absensi per sesi mapel |
| type | enum('self_in','self_out','session') | not null |
| status | enum('hadir','terlambat','izin','sakit','alpa') | not null |
| scan_time | datetime | not null |
| notes | text | nullable |
| created_at, updated_at | timestamp | nullable |

**Index:** `index(user_id, scan_time)`, `index(schedule_id, status)`
**Constraint bisnis (di level Service, bukan DB):** kombinasi `(user_id, type, schedule_id, tanggal)` tidak boleh duplikat pada hari yang sama.

---

### 2.11 `leave_requests`

| Kolom | Tipe | Constraint |
|---|---|---|
| id | bigint unsigned | PK |
| user_id | bigint unsigned | FK → `users.id`, not null |
| type | enum('izin','sakit','cuti') | not null |
| start_date | date | not null |
| end_date | date | not null, harus ≥ `start_date` (validasi Service) |
| reason | text | not null |
| attachment_path | varchar(255) | nullable — disimpan di disk privat |
| status | enum('pending','approved','rejected') | default `pending` |
| approved_by | bigint unsigned | FK → `users.id`, nullable |
| approved_at | timestamp | nullable |
| rejection_note | text | nullable |
| created_at, updated_at | timestamp | nullable |

**Index:** `index(status)`, `index(user_id, start_date)`

---

### 2.12 `notifications`

| Kolom | Tipe | Constraint |
|---|---|---|
| id | bigint unsigned | PK |
| user_id | bigint unsigned | FK → `users.id`, not null |
| type | varchar(100) | not null — mis. `alpa_alert`, `leave_decision` |
| channel | enum('email','whatsapp') | not null |
| payload | json | not null |
| status | enum('pending','sent','failed') | default `pending` |
| sent_at | timestamp | nullable |
| created_at, updated_at | timestamp | nullable |

**Index:** `index(user_id)`, `index(status)`

---

### 2.13 `audit_logs`

| Kolom | Tipe | Constraint |
|---|---|---|
| id | bigint unsigned | PK |
| user_id | bigint unsigned | FK → `users.id`, nullable (pelaku aksi) |
| action | varchar(100) | not null — mis. `attendance.scan`, `qr.regenerate` |
| model_type | varchar(150) | nullable |
| model_id | bigint unsigned | nullable |
| meta | json | nullable |
| created_at | timestamp | not null |

**Index:** `index(user_id)`, `index(action)`

---

## 3. Ringkasan Foreign Key & Aksi Cascade

| Tabel | Kolom FK | Referensi | On Delete |
|---|---|---|---|
| users | role_id | roles.id | restrict |
| students | user_id | users.id | cascade |
| students | class_id | classes.id | restrict |
| teachers | user_id | users.id | cascade |
| classes | school_year_id | school_years.id | restrict |
| classes | homeroom_teacher_id | teachers.id | set null |
| schedules | class_id | classes.id | cascade |
| schedules | subject_id | subjects.id | restrict |
| schedules | teacher_id | teachers.id | restrict |
| teacher_subject | teacher_id, subject_id | teachers.id, subjects.id | cascade |
| attendances | user_id | users.id | cascade |
| attendances | schedule_id | schedules.id | set null |
| leave_requests | user_id | users.id | cascade |
| leave_requests | approved_by | users.id | set null |
| notifications | user_id | users.id | cascade |
| audit_logs | user_id | users.id | set null |

**Catatan:** gunakan `restrict` untuk relasi yang tidak boleh menghapus data induk selama masih dipakai (mis. tidak boleh hapus `classes` jika masih ada `students`), dan `cascade`/`set null` sesuai kebutuhan agar data historis (absensi, log) tidak hilang meski user dihapus (soft delete direkomendasikan untuk `users`, lihat Section 4).

---

## 4. Rekomendasi Tambahan

- Gunakan **Soft Delete** (`deleted_at`) pada tabel `users`, `students`, `teachers`, `classes` agar data historis absensi tetap valid meski user/kelas dinonaktifkan, bukan dihapus permanen.
- Tambahkan `timestamps()` standar Laravel (`created_at`, `updated_at`) di seluruh tabel kecuali `audit_logs` (cukup `created_at`).
- Kolom `json` (`payload`, `meta`) memakai tipe native `JSON` (bukan `TEXT`) agar bisa di-query dengan `whereJsonContains` bila diperlukan.
- Seed data awal (`database/seeders`) wajib mencakup minimal: role default, 1 akun super admin, 1 tahun ajaran aktif — untuk mempercepat setup development.
- Setiap penambahan/perubahan kolom di production wajib migration baru (bukan mengedit migration lama yang sudah di-deploy).

---

## 5. Riwayat Perubahan Skema

| Tanggal | Perubahan | PR/Referensi |
|---|---|---|
| 2026-07-21 | Skema awal v1.0 sesuai `desain.md` | — |

*(Tim wajib menambah baris baru di tabel ini setiap kali ada migration baru yang mengubah struktur.)*

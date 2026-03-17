# LeaveHub - Leave Request Management System

Aplikasi Fullstack Web untuk pengelolaan cuti karyawan. Proyek ini dibangun untuk memenuhi kriteria tes teknikal **Junior Fullstack Web Developer 2026** di **Energeek**.

## Tech Stack
- **Backend:** Laravel 11 (API)
- **Frontend:** Vue.js 3 + Vite
- **Database:** PostgreSQL
- **Authentication:** Laravel Sanctum

## Fitur Utama
- **Multi-Role:** Admin & User (Employee).
- **Dashboard User:** Visualisasi sisa kuota cuti (Progress Bar) dan statistik pengajuan.
- **Pengajuan Cuti:** Form validasi dinamis (pengecekan saldo & tanggal).
- **Riwayat Cuti:** Fitur cancel pengajuan berstatus pending dan hapus riwayat ditolak.
- **Admin Panel:** Kelola user, generate saldo cuti tahunan, dan persetujuan (Approve/Reject) cuti karyawan.

## Instalasi Backend (Laravel)
1. Masuk ke folder backend: `cd backend`
2. Install dependensi: `composer install`
3. Konfigurasi file `.env`:
   - Sesuaikan `DB_CONNECTION=pgsql`
   - Sesuaikan host, port, dan nama database.
4. Jalankan migrasi & seeder: `php artisan migrate --seed`
5. Generate Key: `php artisan key:generate`
6. Jalankan storage link (wajib untuk lampiran): `php artisan storage:link`
7. Jalankan server: `php artisan serve`

## Instalasi Frontend (Vue 3)
1. Masuk ke folder frontend: `cd frontend`
2. Install dependensi: `npm install`
3. Konfigurasi API Base URL: Pastikan mengarah ke `http://localhost:8000/api`
4. Jalankan aplikasi: `npm run dev`

## Dokumentasi API
File dokumentasi API dalam format JSON Collection tersedia di folder root dengan nama: **`Leave_Request_API.postman_collection.json`**. Silakan import file tersebut ke Postman, Insomnia, atau Bruno.

## Cara Menjalankan Testing
1. Masuk ke folder backend: `cd backend`
2. Jalankan perintah: `php artisan test`
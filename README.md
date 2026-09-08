# SI-KASEP — Sistem Informasi Rekap Absen Spenli

**SI-KASEP** (Sistem Informasi Rekap Absen Spenli) adalah aplikasi manajemen absensi dan penilaian siswa untuk **SMP Negeri 5 Ciamis (SPENLI)** yang dibangun menggunakan Laravel 12 & Bootstrap 5.

---

## 🚀 Fitur Utama

- **Login System**: Autentikasi akun aman untuk admin dan guru.
- **Dashboard Ringkasan Interaktif**:
  - Rekap Kehadiran Siswa per Kelas.
  - Rekap Persentase Kehadiran (Hadir, Izin, Sakit, Alpa) secara **Hari Ini**, **Minggu Ini**, **Bulan Ini**, dan **Keseluruhan**.
  - Matriks ringkasan persentase status kehadiran.
- **Manajemen Data Siswa (562 Siswa SPENLI)**:
  - Terbagi lengkap dalam 18 kelas (VII A - VII F, VIII A - VIII F, IX A - IX F).
  - Fitur Tambah, Edit, Hapus, dan Impor File CSV Data Siswa.
- **Absensi Cepat & Rekapitulasi**:
  - Catat kehadiran harian kelas secara cepat.
  - Cetak & Ekspor Rekapitulasi Kehadiran format **PDF**.
- **Manajemen Nilai & Mata Pelajaran**:
  - Kelola Mata Pelajaran & KKM.
  - Input Penilaian Kegiatan (Tugas, UH, PTS, PAS) & Ekspor Matriks Nilai Excel / PDF.

---

## 🛠️ Cara Menjalankan Aplikasi Secara Lokal

1. **Clone Repository**:
   ```bash
   git clone https://github.com/hendrianandrie/absenspenli.git
   cd absenspenli
   ```

2. **Install Dependensi PHP & Node.js**:
   ```bash
   composer install
   npm install
   ```

3. **Salin `.env` & Generate Key**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Jalankan Migrasi & Database Seeder**:
   ```bash
   php artisan migrate --seed
   ```

5. **Jalankan Development Server**:
   ```bash
   php artisan serve
   ```
   Aplikasi dapat diakses melalui browser di: **http://127.0.0.1:8000**

---

## 🔐 Akun Login Bawaan

- **Username / Email**: `admin@smp.sch.id`
- **Password**: `spenlimantap`

---

## 📝 Catatan Penting Mengenai GitHub Pages

> **Pemberitahuan**: GitHub Pages hanya mendukung hosting file statis HTML/CSS/JS dan **tidak mendukung eksekusi backend PHP maupun database SQLite/MySQL Laravel**. 
> 
> Oleh karena itu, halaman GitHub Pages (`hendrianandrie.github.io/absenspenli`) hanya menampilkan dokumen README ini. Untuk menjalankan fungsi Login dan Database SI-KASEP secara online (cloud), disarankan menggunakan web hosting PHP/Laravel (seperti **Render.com**, **Railway.app**, **Fly.io**, atau **cPanel/Shared Hosting**).

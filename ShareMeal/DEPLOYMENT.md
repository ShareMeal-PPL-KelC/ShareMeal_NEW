# Panduan Deployment Laravel - ShareMeal (Railway)

Dokumen ini berisi panduan langkah demi langkah untuk men-deploy aplikasi Laravel **ShareMeal** ke **Railway**. Platform ini mendukung PHP secara native, menyediakan basis data terintegrasi, dan mendeteksi konfigurasi Laravel secara otomatis.

---

## Langkah-langkah Deployment:

1. **Daftar & Hubungkan GitHub**:
   * Buka [Railway.app](https://railway.app) dan masuk menggunakan akun GitHub Anda.

2. **Buat Database Baru**:
   * Di dashboard Railway, klik **New Project** -> **Provision PostgreSQL** atau **Provision MySQL** (pilih salah satu sesuai kebutuhan database Anda).

3. **Tambahkan Layanan Aplikasi**:
   * Klik **+ Add** -> **GitHub Repo** -> pilih repositori `ShareMeal_NEW`.

4. **Konfigurasi Variabel Lingkungan (Variables)**:
   * Klik pada layanan aplikasi `ShareMeal_NEW` Anda, masuk ke tab **Variables**, lalu klik **New Variable** untuk menambahkan variabel berikut:
     * `APP_ENV`: `production`
     * `APP_DEBUG`: `false`
     * `APP_KEY`: *(Dapatkan kunci ini dengan menjalankan `php artisan key:generate --show` di terminal lokal Anda)*
     * `APP_URL`: `${{ RAILWAY_PUBLIC_DOMAIN }}` (Railway akan mengisi ini secara otomatis)

5. **Hubungkan Database ke Aplikasi**:
   * Railway mempermudah koneksi database dengan referensi otomatis. Tambahkan variabel database berikut di tab **Variables** aplikasi Anda:
     * **Jika menggunakan MySQL**:
       * `DB_CONNECTION`: `mysql`
       * `DB_HOST`: `${{ MYSQLHOST }}`
       * `DB_PORT`: `${{ MYSQLPORT }}`
       * `DB_DATABASE`: `${{ MYSQLDATABASE }}`
       * `DB_USERNAME`: `${{ MYSQLUSER }}`
       * `DB_PASSWORD`: `${{ MYSQLPASSWORD }}`
     * **Jika menggunakan PostgreSQL**:
       * `DB_CONNECTION`: `pgsql`
       * `DB_HOST`: `${{ PGDATABASE }}`
       * `DB_PORT`: `${{ PGPORT }}`
       * `DB_DATABASE`: `${{ PGDATABASE }}`
       * `DB_USERNAME`: `${{ PGUSER }}`
       * `DB_PASSWORD`: `${{ PGPASSWORD }}`

6. **Command Jalankan Migrasi Otomatis (Opsional tapi direkomendasikan)**:
   * Di tab **Settings** layanan aplikasi Anda, cari bagian **Deploy** -> **Custom Start Command**, isi dengan:
     ```bash
     php artisan migrate --force && php artisan config:cache && php artisan route:cache && php artisan view:cache
     ```
   * Railway secara otomatis menggunakan Nixpacks untuk mendeteksi bahwa ini adalah proyek Laravel, mengompilasi aset front-end (Vite), menginstal dependencies Composer, dan mengarahkan web server ke direktori `public/`.

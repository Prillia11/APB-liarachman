# Panduan Hosting APB-Prillia (Vercel & Aiven)

Dokumen ini menjelaskan langkah-langkah untuk menghosting aplikasi PHP Anda menggunakan **Vercel** (untuk aplikasi) dan **Aiven** (untuk database MySQL).

## 1. Persiapan Database (Aiven)

1. **Daftar/Login ke [Aiven.io](https://aiven.io/)**.
2. **Create Service** baru:
   - Pilih **MySQL**.
   - Pilih Cloud Provider (misal: DigitalOcean atau Google Cloud) dan region terdekat (misal: Singapore).
   - Pilih Plan **Free Tier** (jika tersedia) atau yang sesuai.
3. Tunggu sampai status service menjadi **Running**.
4. Di dashboard Aiven, cari bagian **Connection Information**:
   - Catat `Host`, `Port`, `User`, dan `Password`.
   - Nama database default biasanya `defaultdb`.
5. **Import Database**:
   - Gunakan tool seperti MySQL Workbench, DBeaver, atau command line untuk mengimpor file `sekolah.sql` ke database Aiven Anda.
   ```bash
   mysql -h <HOST_AIVEN> -P <PORT_AIVEN> -u <USER_AIVEN> -p <DB_NAME_AIVEN> < sekolah.sql
   ```

## 2. Persiapan Aplikasi (Vercel)

1. **Push ke GitHub**:
   - Pastikan project Anda sudah di-upload ke repository GitHub (termasuk file `vercel.json` dan folder `config/`).
2. **Login ke [Vercel](https://vercel.com/)**.
3. **Add New Project**:
   - Hubungkan ke repository GitHub Anda.
4. **Environment Variables**:
   Sebelum klik *Deploy*, buka bagian **Environment Variables** dan tambahkan data dari Aiven tadi:
   - `DB_HOST`: Host dari Aiven.
   - `DB_PORT`: Port dari Aiven (biasanya bukan 3306, misal 11337).
   - `DB_NAME`: Nama database di Aiven (biasanya `defaultdb`).
   - `DB_USER`: User dari Aiven (biasanya `avnadmin`).
   - `DB_PASSWORD`: Password dari Aiven.
5. **Deploy**:
   - Klik **Deploy**. Tunggu proses selesai.
   - Vercel akan memberikan domain (misal: `apb-prillia.vercel.app`).

## 3. Catatan Penting

- **File `.htaccess`**: Vercel tidak mendukung `.htaccess`. Semua routing dikelola lewat `vercel.json`.
- **Penyimpanan Gambar**: Jika aplikasi Anda memiliki fitur upload gambar ke folder lokal (misal `assets/img/`), file tersebut **tidak akan tersimpan permanen** di Vercel. Disarankan menggunakan cloud storage seperti Cloudinary atau AWS S3 untuk aplikasi produksi.
- **SSL**: Koneksi ke Aiven sudah aman (SSL), dan Vercel menyediakan HTTPS secara otomatis.

---
*Dibuat oleh Antigravity*

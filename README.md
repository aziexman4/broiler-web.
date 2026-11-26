# SmartCoop — Sistem Informasi Manajemen Kepegawaian & Monitoring Produktivitas Kandang Ayam Broiler

Aplikasi web berbasis **Laravel 13**, **TailwindCSS**, **Alpine.js**, **Chart.js**, dan **DomPDF** untuk mengelola operasional harian budidaya ayam broiler, logistik pakan & OVK, pemantauan **Break-Even Point (BEP)** real-time, benchmarking **Indeks Penampilan (IP)** antar unit kandang, arus kas saldo riil, serta penggajian pekerja berbasis performa.

> **Proyek Praktik Kerja Lapangan (PKL)**  
> **Pengembang**: M Aulia Aziz (`2310020119`)  
> **Program Studi**: S1 Sistem Informasi — Fakultas Teknologi Informasi, Universitas Islam Kalimantan (UNISKA) Muhammad Arsyad Al Banjari Banjarmasin  
> **Studi Kasus Unit**: Kandang Hanau (Pusat), Kandang Seruyan Utama, & Kandang Plasma C — Kabupaten Seruyan, Kalimantan Tengah.

---

## Fitur Utama

### 1. Role-Based Access Control (Owner vs Pekerja Kandang)
- **Owner (`manage-enterprise`)**: Akses penuh ke dashboard finansial, seluruh unit kandang, gudang pakan pusat, inventaris OVK, panen (penjarangan & tutup buku), analitik & benchmarking, penggajian (payroll), arus kas operasional, dan audit trail.
- **Pekerja Kandang (`worker`)**: Hanya melihat dan mengelola siklus kandang yang ditugaskan kepadanya, mencatat recording harian 3 fase pakan, mengajukan & menerima kiriman pakan, mencetak recording harian, serta mengunduh slip gaji miliknya sendiri (`/my-payslips`).

### 2. Operasional Kandang, Validasi Biologis, & Panen Penjarangan
- **Recording Harian 3 Fase Pakan**: Pencatatan konsumsi pakan `Pre-Starter`, `Starter`, dan `Finisher`, kematian (`mortality`), afkir (`culling`), serta sampling bobot rata-rata harian (`gram/ekor`).
- **Validasi Kapasitas Biologis & EWS**: Mencegah salah input/manipulasi konsumsi pakan melebihi batas biologis harian per ekor, serta memicu **Early Warning System (EWS)** jika kematian harian melebihi `0.5%`.
- **Logistik Pakan Terkontrol**: Alur permintaan pakan (`pending` -> `shipped` -> `completed` atau `rejected` untuk tolak/batal) yang terhubung otomatis dengan stok gudang pusat dan stok kandang.
- **Panen Penjarangan (*Thinning*) & Panen Akhir**: Mendukung panen parsial tanpa menutup siklus maupun panen akhir kuras kandang (*Tutup Buku*) dengan perhitungan proporsional biaya pakan & modal DOC.

### 3. Analitik, Benchmarking, & Live BEP Forecasting
- **Live & Historical BEP Tracker**: Membandingkan kurva bobot aktual ayam terhadap garis target impas harian (`Modal DOC + Kumulatif Pakan` dibagi harga pasar & populasi hidup).
- **Monitoring Kandang Aktif vs Historis Tutup Buku**: Memisahkan estimasi performa kandang yang sedang berjalan (termasuk kurva pertumbuhan bobot harian antar kandang aktif) dari rata-rata historis siklus selesai.
- **Unit Leaderboard & Dual Grafik**: Peringkat performa antar unit kandang berdasarkan rata-rata IP, dilengkapi garis target bonus `IP >= 300` serta grafik tren efisiensi `FCR` & bobot panen.

### 4. Keuangan Terintegrasi & 5 Laporan Cetak PDF A4 Resmi
- **Saldo Kas Riil & Running Balance**: Sinkronisasi kas otomatis dari pembelian pakan, OVK, Chick-In DOC, penjualan panen, dan pembayaran gaji, dilengkapi kolom **Saldo Kas Saat Itu** di setiap baris transaksi.
- **Payroll & Bonus IP Otomatis**: Menghitung rekomendasi bonus pekerja berdasarkan capaian IP siklus selesai.
- **Cetak Pratinjau & Unduh PDF A4 Ber-kop**:
  1. Laporan Recording Harian Kandang
  2. Laporan Akhir Panen & Evaluasi Laba/Rugi
  3. Laporan Analitik & Benchmarking Unit Kandang
  4. Laporan Arus Kas Operasional
  5. Laporan Audit Mutasi Gudang Pakan & Slip Gaji Pekerja

---

## Rumus Perhitungan Utama

- **Daya Hidup (Livability)**:
  $$\text{Livability (\%)} = \frac{\text{Populasi Hidup}}{\text{Populasi Awal}} \times 100$$
- **Feed Conversion Ratio (FCR)**:
  $$\text{FCR} = \frac{\text{Total Pakan Terpakai (kg)}}{\text{Total Berat Panen (kg)}}$$
- **Indeks Penampilan (IP)**:
  $$\text{IP} = \frac{\text{Livability (\%)} \times \text{Bobot Rata-rata (kg)}}{\text{FCR} \times \text{Umur Panen (hari)}} \times 100$$
- **Skema Bonus Performa Pekerja**:
  - `IP >= 400`: **20%** dari Gaji Pokok (*Sangat Baik*)
  - `IP 350 - 399`: **15%** dari Gaji Pokok (*Baik*)
  - `IP 300 - 349`: **10%** dari Gaji Pokok (*Cukup / Standar*)
  - `IP < 300`: **0%** (*Kurang*)

---

## Instalasi & Menjalankan di Lokal

1. **Clone repositori & instal dependensi**
   ```bash
   git clone https://github.com/aziexman4/broiler-web..git
   cd broiler-web
   composer install
   npm install
   ```
2. **Siapkan konfigurasi `.env` & database**
   ```bash
   cp .env.example .env
   php artisan key:generate
   php artisan migrate --seed
   ```
3. **Build aset frontend & jalankan server**
   ```bash
   npm run build
   php artisan serve
   ```

---

## Akun Demo Lokal (`php artisan db:seed`)

Pada environment `local`, halaman `/login` menyediakan kartu **Akses Cepat Demo PKL** dengan kata sandi seluruh akun: `password`.

| Peran | Nama Pengguna | Unit Kandang | Email | Kata Sandi |
| :--- | :--- | :--- | :--- | :--- |
| **Owner / Pemilik** | Owner SmartCoop | Seluruh Unit (Pusat) | `owner@smartcoop.com` | `password` |
| **Pekerja Kandang** | Muhammad Harries Rasyadi | Kandang Hanau | `harries@smartcoop.com` | `password` |
| **Pekerja Kandang** | Danang Onky Romadhoni | Kandang Seruyan Utama | `danang@smartcoop.com` | `password` |
| **Pekerja Kandang** | Mujawiri Apandie | Kandang Plasma C | `mujawiri@smartcoop.com` | `password` |

---

## Pengujian Otomatis (Automated Testing)

Seluruh fitur utama, otorisasi role, rumus metrik, logistik pakan, OVK, arus kas riil, dan ekspor PDF diuji menggunakan **PHPUnit**:

```bash
php vendor/phpunit/phpunit/phpunit --bootstrap vendor/autoload.php
```

# Dokumentasi Teknis & Analisis Keuangan Modul Arus Kas Operasional (Cash Flow)

**Objek Studi Kasus:** Peternakan Broiler Hanau — Desa Pembuang Hulu I, Kec. Hanau, Kab. Seruyan, Kalimantan Tengah  
**Sistem:** Sistem Informasi Manajemen Kepegawaian dan Monitoring Produktivitas Kandang Ayam Broiler (Laravel 13.8)  
**Periode Data Evaluasi:** 05 Februari 2026 – 14 November 2026 (33 Entri Transaksi)

---

## Bagian I — Penjelasan Teknis Arsitektur & Cara Kerja Modul Arus Kas

### 1. Rute & Kontrol Akses (Otorisasi)
Berdasarkan berkas `routes/web.php`, seluruh endpoint modul Arus Kas berada di dalam grup middleware `auth` dan `can:manage-enterprise` (Gate `manage-enterprise` pada `app/Providers/AppServiceProvider.php` yang hanya mengizinkan pengguna dengan `role === 'owner'`):

| HTTP Method & URI | Nama Rute | Controller & Method | Hak Akses | Keterangan |
| :--- | :--- | :--- | :--- | :--- |
| `GET /cash-flows` | `cash-flows.index` | `CashFlowController@index` | `auth`, `owner` | Halaman daftar mutasi kas, kartu KPI, dan form input |
| `POST /cash-flows` | `cash-flows.store` | `CashFlowController@store` | `auth`, `owner` | Menyimpan transaksi kas masuk/keluar baru |
| `GET /reports/cash-flow` | `reports.cash-flow` | `ReportController@cashFlowPreview` | `auth`, `owner` | Pratinjau & cetak A4 Laporan Arus Kas Operasional |

*Catatan implementasi:* `Route::resource('cash-flows', CashFlowController::class)->only(['index', 'store'])` membatasi transaksi kas agar bersifat *append-only* (tidak menyediakan rute `edit`, `update`, atau `destroy` di antarmuka web). Tautan cetak `route('reports.cash-flow')` saat ini tersedia di menu dropdown laporan pada `resources/views/flocks/show.blade.php`.

---

### 2. Struktur Tabel & Kolom Database
Didefinisikan dalam migrasi `database/migrations/2026_07_06_000009_create_cash_flows_table.php` dan dipetakan oleh model `App\Models\CashFlow` (`app/Models/CashFlow.php`).

* **Nama Tabel:** `cash_flows` (`protected $table = 'cash_flows'`)
* **Sifat Relasi:** Berdiri sendiri (*standalone*), tidak memiliki kolom foreign key ke `flocks` maupun `coops`. Model menggunakan trait `HasFactory` (tidak menggunakan trait `LogsActivity`).

| Nama Kolom | Tipe Data Migrasi | Cast Model & Validasi | Fungsi |
| :--- | :--- | :--- | :--- |
| `id` | `bigIncrements` (`id()`) | Primary Key | Identitas unik setiap catatan transaksi kas |
| `transaction_date` | `date` | Cast: `'date'` \| Validasi: `required\|date` | Tanggal terjadinya transaksi kas |
| `description` | `string` | Validasi: `required\|string\|max:255` | Uraian/keterangan transaksi operasional |
| `type` | `enum('in', 'out')` | Validasi: `required\|in:in,out` | Klasifikasi arus kas: `'in'` (Kas Masuk/Debet) atau `'out'` (Kas Keluar/Kredit) |
| `amount` | `integer` | Cast: `'integer'` \| Validasi: `required\|integer\|min:1` | Nominal transaksi dalam mata uang Rupiah bulat |
| `created_at`, `updated_at` | `timestamps()` | Timestamp otomatis | Waktu pencatatan dan pembaruan baris di database |

---

### 3. Sumber Data Pemasukan & Pengeluaran
Berdasarkan inspeksi kode seluruh controller dan model pada proyek ini, **tidak ada modul lain yang melakukan pencatatan otomatis ke tabel `cash_flows`**. Setiap modul finansial memiliki tabel terpisah:
* **Penjualan ayam panen** dicatat pada tabel `harvests` (`HarvestController`).
* **Pengadaan & distribusi pakan** dicatat pada tabel `feed_inventories` dan `feed_requests` (`FeedInventoryController`, `DailyRecordController`).
* **Penggajian & bonus performa pekerja** dicatat pada tabel `payrolls` (`PayrollController`).
* **Pengadaan & pemakaian OVK (Obat, Vaksin, Kimia)** dicatat pada tabel `ovk_inventories` (`OvkController`).

Dengan demikian, seluruh record di tabel `cash_flows` berasal dari dua sumber:
1. **Input manual oleh Owner** melalui formulir pada `resources/views/cash_flows/index.blade.php` yang dikirim ke `CashFlowController@store`.
2. **Data awal seeder** pada `database/seeders/FarmDataSeeder.php`.

---

### 4. Rumus Perhitungan & Mekanisme Periode Tanggal
Baik pada dashboard manajemen (`CashFlowController@index`) maupun pratinjau cetak (`ReportController@cashFlowPreview`), agregasi dihitung langsung via Eloquent Query Builder dengan rumus identik:

$$\text{total\_in} = \sum (\text{amount di mana } \text{type} = \text{'in'})$$
$$\text{total\_out} = \sum (\text{amount di mana } \text{type} = \text{'out'})$$
$$\text{balance (Net Cash Flow)} = \text{total\_in} - \text{total\_out}$$

Implementasi di controller:
```php
$records = CashFlow::orderBy('transaction_date', 'desc')
    ->orderBy('id', 'desc')
    ->get();

$total_in = CashFlow::where('type', 'in')->sum('amount');
$total_out = CashFlow::where('type', 'out')->sum('amount');
$balance = $total_in - $total_out;
```

**Mekanisme Filter Periode Dua Mode (Mutually Exclusive) & Pengurutan:**
* Method `CashFlow::getFilteredSummary(Request $request)` mendukung 2 mode filter yang saling bergantian pada halaman manajemen (`cash_flows/index.blade.php`) dan laporan cetak (`reports/cashflow-preview.blade.php`):
  1. **Mode Per Bulan (`filter_mode = 'month'`):** Menyaring `transaction_date` menggunakan `whereYear` dan `whereMonth` sesuai input `YYYY-MM`, serta menampilkan subjudul bulan berbahasa Indonesia (contoh: `Periode: April 2026`).
  2. **Mode Rentang Tanggal (`filter_mode = 'range'`):** Menyaring `transaction_date` menggunakan `whereDate('>=', $startDate)` dan/atau `whereDate('<=', $endDate)`, serta menampilkan subjudul rentang tanggal tersebut.
  3. **Tanpa Filter (`all`):** Menarik seluruh transaksi dan menampilkan rentang otomatis dari transaksi paling awal (`$records->last()->transaction_date`) hingga transaksi paling akhir (`$records->first()->transaction_date`).
* Sebelum dirender ke tabel rincian cetak, koleksi diurutkan ulang secara kronologis menaik (`$records->sortBy('transaction_date')->values()`) agar pembacaan buku kas urut dari tanggal terlama hingga terbaru.
* Halaman `resources/views/cash_flows/index.blade.php` telah dilengkapi dukungan penuh mode gelap (`dark:*`), notifikasi `session('success')` & validasi error, serta tombol pintas **Cetak Laporan A4** yang meneruskan parameter filter aktif ke `route('reports.cash-flow')`.

---

### 5. Mekanisme Cetak Laporan Global (Kop Surat Kantor Pusat)
Karena transaksi pada `cash_flows` bersifat lintas kandang/global, sistem menerapkan mekanisme Kop Surat Kantor Pusat secara otomatis melalui tiga komponen berikut:

1. **Controller (`ReportController@cashFlowPreview`):**  
   Mengambil unit yang berstatus sebagai Kantor Pusat dari master unit kandang (`coops`):
   ```php
   $coop = Coop::where('is_headquarters', true)->first();
   ```
   Lalu mengirim `$coop`, `$records`, `$total_in`, `$total_out`, dan `$balance` ke view `reports.cashflow-preview`.
2. **View Laporan (`resources/views/reports/cashflow-preview.blade.php`):**  
   Mewarisi layout induk cetak dengan meneruskan objek kantor pusat (`@extends('layouts.print', ['coop' => $coop ?? null])`) serta menampilkan keterangan ruang lingkup **"Laporan Rekapitulasi Pusat — Seluruh Unit Kandang"** bersama periode tanggal.
3. **Master Layout A4 (`resources/views/layouts/print.blade.php`):**  
   Menyelesaikan entitas kop melalui `$activeCoop = $coop ?? Coop::where('is_headquarters', true)->first() ?? Coop::first()`. Untuk konfigurasi saat ini, unit dengan `is_headquarters = true` adalah **Kandang Hanau** (*Desa Pembuang Hulu I, Kec. Hanau, Kab. Seruyan, Kalimantan Tengah*), sehingga nama unit, tagline, alamat, telepon, email, serta nama Kepala Unit/Penanggung Jawab ditampilkan pada kop surat dan blok tanda tangan dalam ukuran kertas **A4 Portrait** (`@page { margin: 15mm 10mm; size: A4 portrait; }`).

---

## Bagian II — Analisis Keuangan Agribisnis (Laporan & Sidang PKL)

### Ringkasan Bulanan Arus Kas Operasional (Februari – November 2026)

| Bulan Periode | Jumlah Transaksi | Total Debet (Kas Masuk) | Total Kredit (Kas Keluar) | Saldo Akhir Bulanan |
| :--- | :-: | --: | --: | --: |
| Februari 2026 | 3 | Rp 1.850.000 | Rp 4.550.000 | -Rp 2.700.000 |
| Maret 2026 | 2 | Rp 1.450.000 | Rp 1.850.000 | -Rp 400.000 |
| April 2026 | 4 | Rp 5.850.000 | Rp 4.700.000 | +Rp 1.150.000 |
| Mei 2026 | 2 | Rp 0 | Rp 3.250.000 | -Rp 3.250.000 |
| Juni 2026 | 4 | Rp 3.930.000 | Rp 3.600.000 | +Rp 330.000 |
| Juli 2026 | 2 | Rp 1.600.000 | Rp 1.050.000 | +Rp 550.000 |
| Agustus 2026 | 5 | Rp 5.300.000 | Rp 5.900.000 | -Rp 600.000 |
| September 2026 | 2 | Rp 1.750.000 | Rp 1.250.000 | +Rp 500.000 |
| Oktober 2026 | 4 | Rp 4.650.000 | Rp 5.950.000 | -Rp 1.300.000 |
| November 2026 | 5 | Rp 3.800.000 | Rp 3.900.000 | -Rp 100.000 |
| **TOTAL (05 Feb – 14 Nov 2026)** | **33** | **Rp 30.180.000** | **Rp 36.000.000** | **-Rp 5.820.000** |

---

### 1. Ringkasan Kondisi Arus Kas
Selama periode laporan **05 Februari 2026 – 14 November 2026**, tercatat **33 entri transaksi** kas operasional non-panen pada Peternakan Broiler Hanau dengan total perputaran dana bruto sebesar **Rp 66.180.000**:
* **Total Uang Masuk (Debet):** **Rp 30.180.000** (ditampilkan ringkas pada kartu KPI sebesar `30,2Jt`), berasal dari penjualan produk sampingan peternakan (pupuk kotoran ayam, karung pakan bekas, dan sisa sekam/limbah non-ayam).
* **Total Uang Keluar (Kredit):** **Rp 36.000.000** (ditampilkan ringkas pada kartu KPI sebesar `36,0Jt`), berasal dari pembiayaan operasional rutin kandang (sekam alas kandang, listrik & air, gas LPG pemanas *brooding* DOC, serta perawatan kipas *blower* & instalasi).
* **Makna Saldo Akhir (Net Cash Flow):** Saldo akhir kumulatif tercatat sebesar **-Rp 5.820.000** (ditampilkan ringkas sebagai `-5,8Jt`). Angka ini menunjukkan bahwa pemasukan kas sampingan senilai Rp 30.180.000 telah mampu menutupi **83,83%** dari total biaya operasional pendukung non-pakan/DOC, sedangkan sisa kebutuhan biaya sebesar Rp 5.820.000 ditutup dari pendapatan utama penjualan ayam hidup.

---

### 2. Klasifikasi Pos Transaksi
* **A. Pemasukan Sampingan Kandang (*By-Product Revenue*) — Rp 6.600.000**
  1. **Penjualan Limbah Organik (68,18% dari kas masuk):** Hasil penjualan pupuk karungan berbahan kotoran ayam (14 April 2026) sebesar **Rp 4.500.000**.
  2. **Penjualan Limbah Kemasan (31,82% dari kas masuk):** Hasil penjualan karung pakan bekas (14 Agustus 2026) sebesar **Rp 2.100.000**.
* **B. Pengeluaran Operasional Harian & Rutin (*Operational Overhead*) — Rp 10.850.000**
  1. **Pengadaan Bahan Alas Kandang / *Litter* (71,89% dari kas keluar):** Pembelian sekam alas kandang sebanyak 3 truk (14 Februari 2026, Rp 3.600.000) dan pembelian sekam baru untuk siklus aktif (14 Oktober 2026, Rp 4.200.000) dengan total **Rp 7.800.000**.
  2. **Biaya Utilitas & Energi (17,05% dari kas keluar):** Pembayaran listrik dan token kandang utama (14 Maret 2026) sebesar **Rp 1.850.000**.
  3. **Biaya Pemeliharaan Peralatan Kandang (11,06% dari kas keluar):** Perbaikan kipas *ventilation blower* untuk menjaga sirkulasi udara kandang (14 Juni 2026) sebesar **Rp 1.200.000**.

---

### 3. Konteks Ruang Lingkup Laporan (Alasan Logis Saldo Negatif)
Dalam struktur akuntansi Peternakan Broiler Hanau pada aplikasi ini, pelaporan keuangan dipisahkan berdasarkan pusat biaya dan pendapatan agar evaluasi manajerial lebih akurat:
* **Pendapatan utama (*core revenue*)** dari penjualan daging ayam panen dicatat secara terpisah pada modul **Laporan Hasil Panen & Audit Finansial (P&L)** bersama biaya variabel utama produksi (*Cost of Goods Sold* / COGS berupa bibit DOC dan pakan) serta upah/bonus pekerja kandang (*Payroll*).
* **Modul Arus Kas Operasional** berfokus secara khusus pada pencatatan biaya penunjang operasional (*overhead* non-pakan/DOC) di tingkat kandang/pusat serta pemasukan kas non-panen (*by-product*).

Oleh karena itu, saldo akhir sebesar **-Rp 4.250.000** pada Laporan Arus Kas **tidak berarti usaha peternakan mengalami kerugian secara keseluruhan**. Nilai tersebut mencerminkan **beban *overhead* operasional bersih** (*net operational overhead*) setelah disubsidi oleh penjualan pupuk kandang dan karung bekas, yang secara keseluruhan jauh lebih kecil dibandingkan laba kotor hasil penjualan ayam panen.

---

### 4. Rekomendasi Manajerial bagi Peternakan Broiler Hanau
1. **Pengendalian Biaya Tersembunyi (*Hidden Cost*) di Luar Pakan dan DOC:** Pencatatan kas digital menunjukkan pos pengadaan sekam menyerap porsi terbesar pengeluaran operasional (71,89%). Pemilik dapat menggunakan data ini untuk mengevaluasi pemasok sekam, melakukan negosiasi harga kontrak curah, serta memantau tren konsumsi listrik kandang agar biaya *overhead* per kilogram panen tetap rendah.
2. **Optimalisasi Penerimaan Produk Sampingan (*Cost Recovery*):** Realisasi penjualan pupuk kandang dan karung bekas berhasil menekan 60,83% beban operasional rutin. Manajemen disarankan membuat jadwal pengumpulan dan penjualan karung bekas serta pupuk kandang secara berkala di setiap akhir siklus tutup buku untuk memperkuat likuiditas kas operasional.
3. **Transparansi Audit & Perencanaan Anggaran Siklus Baru:** Dokumen cetak A4 ber-Kop Surat Kantor Pusat (**Kandang Hanau**) memberikan rekam jejak transaksi yang kronologis dan akuntabel untuk kebutuhan audit internal, pelaporan kemitraan, serta acuan penentuan cadangan kas kecil (*petty cash*) dan jadwal pemeliharaan preventif peralatan (seperti *ventilation blower*) sebelum *chick-in* siklus berikutnya dimulai.

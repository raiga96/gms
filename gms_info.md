# Maklumat Projek: Greens Management System (GMS / SmartCut)

Greens Management System (DBKU - Dewan Bandaraya Kuching Utara) adalah sebuah aplikasi berasaskan web yang dibangunkan menggunakan PHP dan MySQL. Sistem ini direka khusus untuk pengurusan data kontraktor, pemantauan kontrak, serta pengurusan zon dan subzon di bawah pentadbiran Bahagian Landskap (*Landscape Division*) DBKU.

---

## 1. Maklumat Umum Projek

* **Nama Projek:** Greens Management System (GMS) / SmartCut
* **Organisasi / Agensi:** Dewan Bandaraya Kuching Utara (DBKU)
* **Bahagian Terlibat:** Bahagian Landskap (*Landscape Division*)
* **Objektif Utama:**
  * Menguruskan pendaftaran zon dan pecahan subzon landskap.
  * Memantau peruntukan dana dan pengagihan kontrak pemotongan rumput/penyelenggaraan.
  * Menyelaras penugasan kontraktor dan pegawai pemeriksa DBKU.
  * Merekod buku log aktiviti tapak harian (*Site Daily Record / Daily Diary*) mengikut sesi pusingan kerja.
  * Menyediakan laporan bercetak dan PDF rasmi bagi tujuan pengesahan dan pembayaran.
  * Memetakan kawasan zon dan subzon secara visual menggunakan fail geospatial (KMZ) dan Cesium 3D.

---

## 2. Seni Bina & Teknologi (Tech Stack)

* **Backend:** PHP (Vanilla / Procedural menggunakan sambungan `mysqli`).
* **Pangkalan Data:** MySQL / MariaDB.
* **Frontend:**
  * HTML5, CSS3, JavaScript (jQuery & Vanilla JS).
  * Templat Dashboard Admin (Stellar Admin berasaskan Bootstrap).
  * Komponen cetakan dan borang moden menggunakan Tailwind CSS (contohnya `sitedailyrecord.php`).
  * Pustaka Ikon: Simple Line Icons & Font Awesome.
* **Pemetaan & Geospatial:**
  * **Google Maps API:** Paparan peta interaktif satelit/hibrid bagi zon dan subzon (`maps.php`, `contract_details.php`, dll.).
  * **KMZ / KML:** Muat naik dan parsing data fail geodata bagi sempadan kawasan subzon (`/kmz`).
* **Penjanaan Dokumen:**
  * **dompdf:** Pustaka PHP untuk menjana laporan PDF rasmi daripada format HTML tapak harian.

---

## 3. Modul & Fungsi Utama Sistem

### A. Pengesahan & Pengurusan Pengguna (*Authentication & Access Control*)
* **Log Masuk & Keluar:** `login.php`, `logout.php`, `includes/login_action.php`.
* **Kawalan Akses Global:** Disemak secara berpusat melalui `includes/connection.php` untuk memastikan sesi aktif (`$_SESSION['user_id']`).
* **Keselamatan Kata Laluan:** Menggunakan fungsi hashing standard PHP `password_verify()`.
* **Pengurusan Pengguna:** `users_management.php`, `includes/process_add_user.php` bagi mengawal peranan pengguna (Admin, Pegawai DBKU, Wakil Kontraktor).

### B. Pengurusan Zon & Subzon (*Zones & Subzones*)
* **Zon:** Senarai zon (`zone.php`), pendaftaran zon baharu (`add_zone.php`), kemaskini (`update_zone.php`), butiran terperinci (`zone_details.php`).
* **Subzon:** Pecahan kawasan kerja (`add_subzone.php`, `subzone_details.php`, `update_sz.php`).
* **Metrik Kawasan:** Pengiraan perimeter (m) dan keluasan kawasan (hektar - ha) bagi setiap subzon.
* **Geodata Subzon:** Muat naik dan pengemaskinian fail KMZ (`includes/upload_kmz.php`, `includes/update_kmz.php`).

### C. Pengurusan Kontraktor (*Contractor Management*)
* **Pendaftaran Kontraktor:** `new_contractor.php`, `includes/new_contractor.php`.
* **Senarai & Profil Kontraktor:** `contractor_list.php`, `contractor_details.php`.
* **Pendaftaran Akaun Pengguna Kontraktor:** `includes/reguser_contractor.php`.

### D. Pengurusan Kontrak & Penugasan (*Contracts & Assignments*)
* **Senarai Kontrak:** `contract_list.php` yang memaparkan ringkasan jumlah kontrak, kontraktor berdaftar, nilai peruntukan dana (*fund*), dan saiz keluasan (ha).
* **Maklumat Kontrak:** `contract_details.php`.
* **Penugasan Kontraktor ke Subzon:** `assign_contractor.php`, `includes/assign_contractor_action.php`.
* **Penugasan Pegawai DBKU:** `assign_dbku.php`, `includes/assign_dbku_action.php`.

### E. Buku Harian Tapak (*Site Daily Record / Daily Diary*)
* **Perekodan Harian:** Merekod aktiviti potongan rumput harian mengikut kitaran sesi kerja (`daily_diary.php`, `daily_details.php`, `sitedaily_records.php`).
* **Borang Rasmi Tapak:** `sitedailyrecord.php` (format khusus cetakan dengan nombor rujukan seperti `DEKULNP/5A-28`).
* **Lampiran Bukti Kerja:** Muat naik gambar foto sebelum/selepas dan dokumen sokongan (`sitedaily_attachment.php`).
* **Eksport PDF:** Penjanaan PDF automatik untuk dokumentasi dan rekod semakan (`sitedailyrecordpdf.php`).

---

## 4. Struktur Direktori Utama

```text
gms/
├── assets/                  # Fail-fail statik (CSS, JavaScript, fon, imej templat)
├── docs/                    # Fail dokumentasi tambahan
├── dompdf/                  # Pustaka penjanaan PDF berasaskan PHP
├── includes/                # Skrip pemprosesan logik backend & operasi DB
│   ├── connection.php       # Sambungan MySQL dan sekatan sesi autentikasi
│   ├── x.php                # Pembolehubah konfigurasi hos, pengguna, kata laluan DB
│   ├── login_action.php     # Logik pengesahan kata laluan pengguna
│   ├── assign_*_action.php  # Tindakan penugasan kontraktor dan pegawai
│   ├── upload_kmz.php       # Pemprosesan fail geospatial KMZ
│   └── ...                  # Skrip CRUD data sistem
├── kmz/                     # Direktori simpanan fail-fail KMZ yang dimuat naik
├── partials/                # Komponen UI boleh guna semula (navbar.php, sidebar.php, footer.php)
├── index.php                # Papan pemuka utama sistem (Dashboard metrik)
├── login.php / logout.php   # Antaramuka log masuk dan log keluar
├── zone*.php                # Halaman pengurusan zon
├── subzone*.php             # Halaman pengurusan subzon
├── contractor*.php          # Halaman pengurusan kontraktor
├── contract*.php            # Halaman pengurusan kontrak
├── sitedaily*.php           # Halaman pengurusan laporan harian tapak & eksport PDF
├── users_management.php     # Halaman pengurusan akses pengguna
├── gms_info.md              # Dokumentasi menyeluruh pemahaman sistem
└── README.md                # Ringkasan maklumat asas repositori
```

---

## 5. Keperluan & Panduan Pemasangan

1. **Keperluan Pelayan:**
   * Apache / Nginx (disyorkan WAMP / XAMPP untuk persekitaran Windows).
   * PHP 7.4 atau lebih tinggi (menyokong fungsi `password_verify` dan `mysqli`).
   * MySQL / MariaDB.
2. **Konfigurasi Pangkalan Data:**
   * Konfigurasi capaian pangkalan data disimpan dalam `includes/x.php` yang dipanggil oleh `includes/connection.php`.
   * Pastikan nama pangkalan data, hos, nama pengguna, dan kata laluan sepadan dengan pelayan MySQL setempat.
3. **Capaian Sistem:**
   * Buka pelayar web dan layari `http://localhost/gms` (atau mengikut direktori WAMP anda).

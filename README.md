# Greens Management System

Greens Management System (DBKU - Dewan Bandaraya Kuching Utara) adalah sebuah aplikasi berasaskan web yang dibangunkan menggunakan PHP dan MySQL. Sistem ini direka untuk memudahkan pengurusan data kontraktor, pemantauan kontrak, serta pengurusan zon dan subzon di bawah pentadbiran DBKU.

## 🚀 Ciri-Ciri Utama (Key Features)

- **Papan Pemuka (Dashboard):** Paparan ringkas statistik keseluruhan kontrak, kontraktor, dan sesi semasa.
- **Pengurusan Kontraktor:** Pendaftaran kontraktor baharu, senarai kontraktor, dan maklumat terperinci kontraktor.
- **Pengurusan Kontrak:** Peruntukan kontrak, butiran kontrak, dan penugasan kontraktor ke subzon.
- **Pengurusan Zon & Subzon:** Menambah, mengemaskini, dan mengurus maklumat zon serta subzon.
- **Rekod Harian (Daily Diary/Site Records):** Perekodan aktiviti harian tapak, muat naik lampiran (attachment), dan penjanaan laporan PDF.
- **Integrasi Peta (Maps Integration):** Visualisasi zon dan lokasi melalui sokongan Cesium dan fail KMZ.
- **Pengurusan Pengguna:** Kawalan akses dan pengurusan profil pengguna sistem.

## 🛠️ Teknologi Yang Digunakan (Tech Stack)

- **Frontend:** HTML, CSS, JavaScript
- **Backend:** PHP (Vanilla)
- **Pangkalan Data:** MySQL
- **Lain-lain:** Dompdf (untuk penjanaan PDF), Cesium (Sistem Pemetaan Web 3D)

## 📂 Struktur Direktori (Directory Structure)

```text
dbku/
├── assets/         # Fail-fail statik seperti CSS, JS, imej
├── Cesium/         # Pustaka (Library) untuk pemetaan web 3D
├── docs/           # Dokumentasi tambahan
├── dompdf/         # Pustaka (Library) PHP untuk menjana laporan PDF
├── includes/       # Skrip PHP yang disertakan (seperti sambungan pangkalan data - connection.php)
├── kmz/            # Fail-fail geodata / pemetaan (KMZ files)
├── pages/          # Fail-fail halaman sekunder atau templat
├── partials/       # Sebahagian elemen UI (header, footer, sidebar)
├── src/            # Kod sumber utama (JavaScript, dll)
├── index.php       # Halaman utama (Dashboard)
├── login.php       # Halaman log masuk sistem
└── [Fail PHP Lain] # Modul-modul pengurusan (kontraktor, zon, rekod harian)
```

## ⚙️ Keperluan Sistem (System Requirements)

- Pelayan Web (Web Server): Apache atau Nginx (disyorkan WAMP/XAMPP untuk pembangunan tempatan)
- PHP: Versi 7.4 ke atas (atau bersesuaian dengan sistem)
- Pangkalan Data: MySQL / MariaDB

## 💻 Panduan Pemasangan (Installation Guide)

1. **Klon / Salin Projek:** Letakkan folder `dbku` di dalam direktori pelayan web anda (contohnya `C:\wamp64\www\dbku` jika menggunakan WAMP).
2. **Pangkalan Data:**
   - Cipta satu pangkalan data baharu dalam MySQL.
   - Import fail `.sql` untuk pangkalan data (jika disediakan).
3. **Konfigurasi Pangkalan Data:**
   - Buka fail `includes/connection.php`.
   - Kemaskini butiran sambungan pangkalan data (`hostname`, `username`, `password`, `dbname`) mengikut tetapan pelayan anda.
4. **Log Masuk:**
   - Buka pelayar web dan akses `http://localhost/dbku`.
   - Log masuk menggunakan ID dan kata laluan yang didaftarkan.

## 📄 Lesen (License)

Hak Cipta © Dewan Bandaraya Kuching Utara (DBKU). Terhad untuk kegunaan dalaman sahaja.

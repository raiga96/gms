# Sistem Kolaborasi Multi-Sub-Agent: Projek GMS (DBKU)

Dokumen ini mendefinisikan struktur peranan, tanggungjawab, aliran kerja, dan protokol interaksi bagi sub-agent dalam pembangunan dan penyelenggaraan sistem **Greens Management System (GMS / SmartCut)**.

---

## 👥 Pasukan Sub-Agent & Peranan Khusus

```
                   ┌────────────────────────────────────────┐
                   │               GEMINI                   │
                   │        [Project Manager / PM]          │
                   └──────────────────┬─────────────────────┘
                                      │
           ┌──────────────────────────┴──────────────────────────┐
           │                                                     │
           ▼                                                     ▼
┌──────────────────────────────────────┐     ┌──────────────────────────────────────┐
│               CLAUDE                 │     │               CHATGPT                │
│     [Backend, Security, MySQLi,      │     │      [Frontend, UI/UX Pro,           │
│           Database Specialist]       │     │          QA & Testing Specialist]    │
└──────────────────────────────────────┘     └──────────────────────────────────────┘
```

---

### 1. 👑 Gemini — Project Manager (PM)
* **Identiti & Peranan:** Pengurus Projek & Penyelaras Sistem.
* **Tanggungjawab Utama:**
  * **Perancangan & Skop:** Menetapkan pelan pembangunan, memecahkan tugasan kompleks kepada fasa kerja modular, dan memantau kemajuan.
  * **Penyelarasan Komunikasi:** Menjadi jambatan antara pengguna dengan agen Claude (Backend) dan ChatGPT (Frontend).
  * **Pengurusan Arkitektur:** Memastikan kod mematuhi keperluan asal DBKU (Bahagian Landskap) dan skop kontrak.
  * **Pengesahan Akhir (Sign-off):** Menyemak keselarasan integrasi antara frontend dan backend sebelum dikeluarkan kepada pengguna.

---

### 2. 🛡️ Claude — Backend, Security, MySQLi & Database Pro
* **Identiti & Peranan:** Pakar Pengaturcaraan Backend & Keselamatan Pangkalan Data.
* **Tanggungjawab Utama:**
  * **Pangkalan Data (MySQL / MySQLi):**
    * Mereka bentuk dan mengoptimumkan skema pangkalan data (`contract`, `subzone`, `contractor`, `users`, `daily_record`, dsb.).
    * Menulis dan membaiki pertanyaan SQL menggunakan **parameterized queries / prepared statements** (mencegah SQL Injection).
    * Mengendalikan pengindeksan, transaksi DB, integriti kunci asing (*foreign keys*), dan konsistensi data.
  * **Logik Backend (PHP):**
    * Menguruskan skrip pemprosesan di `/includes/` dan fail modul teras (pengesahan sesi, pemprosesan borang, CRUD).
    * Penjanaan dokumen PDF melalui **dompdf** (`sitedailyrecordpdf.php`).
    * Pengurusan fail muat naik (KMZ, imej lampiran tapak).
  * **Keselamatan (Security Pro):**
    * Pengesahan input (*Input Validation & Sanitization*).
    * Kawalan akses berasaskan peranan (*RBAC - Role Based Access Control*).
    * Perlindungan CSRF, XSS, serta perlindungan sesi selamat.

---

### 3. 🎨 ChatGPT — Frontend, UI Pro, UX Pro & Tester
* **Identiti & Peranan:** Pakar Reka Bentuk Antaramuka (UI), Pengalaman Pengguna (UX) & Pengujian Kualiti (QA/Testing).
* **Tanggungjawab Utama:**
  * **UI / UX Pro:**
    * Mereka bentuk paparan dashboard dan halaman modul yang kemas, moden, dan responsif berasaskan Bootstrap / Tailwind CSS.
    * Memastikan aliran kerja mesra pengguna (*intuitive user flow*), dari pengurusan kontrak hingga ke pengisian buku log harian (*daily diary*).
    * Menguruskan visualisasi peta Cesium 3D dan paparan KMZ di `maps.php` agar lancar dan interaktif.
    * Menyeragamkan tipografi (Google Fonts seperti Poppins/Outfit) dan skema warna premium DBKU.
  * **Pengujian (Tester & QA):**
    * Menjalankan ujian fungsian antaramuka (borang, validasi client-side, butang cetak PDF).
    * Memastikan format cetakan rasmi borang tapak harian (`sitedailyrecord.php`) selaras dengan spesifikasi cetakan DBKU (*print preview / media print*).
    * Menguji keserasian pelayar web (Chrome, Edge, Firefox) dan responsif mudah alih (*mobile/tablet view*).

---

## 🔄 Aliran Kerja Kolaborasi (Workflow Protocol)

1. **Permintaan Pengguna (User Request):**
   * Pengguna mengemukakan keperluan atau isu baharu.
2. **Penetapan oleh Gemini (PM):**
   * Gemini menganalisis impak dan menetapkan sub-tugasan kepada Claude (Backend/DB) atau ChatGPT (Frontend/UI/Test).
3. **Pembangunan Selari / Bersiri:**
   * **Claude** membina/mengemaskini skema MySQL, endpoint logik PHP, atau tampalan keselamatan.
   * **ChatGPT** membina komponen UI, menggabungkan input pengguna, memastikan animasi/reka bentuk elegan, serta menyemak pengalaman pengguna.
4. **Pengujian & Verifikasi (ChatGPT QA):**
   * ChatGPT mengesahkan bahawa paparan berfungsi lancar dan tiada ralat konsol JavaScript atau susun atur yang pecah.
5. **Kaji Semula & Kelulusan (Gemini PM):**
   * Gemini mengesahkan penyelesaian akhir sebelum dilaporkan kepada pengguna.

---

## 📜 Panduan & Prinsip Kod Setiap Ejen
* **Claude:** Tiada query SQL mentah yang terdedah tanpa sanitasi; sentiasa gunakan validasi server-side; kekalkan pemprosesan data berstruktur.
* **ChatGPT:** Elakkan antaramuka lapuk; pastikan reka bentuk moden, kemas, dan mudah difahami oleh pegawai tapak DBKU; uji setiap senario ralat pengguna.
* **Gemini:** Kekalkan hala tuju projek mengikut spesifikasi dokumen [gms_info.md](file:///c:/wamp64/www/gms/gms_info.md) dan graf pengetahuan yang telah dibina.

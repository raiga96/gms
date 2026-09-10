# CHANGELOG.md — Rekod Perubahan Projek GMS (SmartCut)

Semua perubahan teknikal, pengubahsuaian gaya reka bentuk, palet warna, tipografi, dan pembaikan ralat bagi projek **Greens Management System (GMS / SmartCut)** direkodkan di sini secara terperinci.

---

## [Versi 2.1.0] — 2026-09-07

### 🎨 Sistem Reka Bentuk & Palet Warna (Color Palette & Tokens)

Sistem reka bentuk moden berasaskan fail [`assets/css/modern-dashboard.css`](file:///c:/wamp64/www/gms/assets/css/modern-dashboard.css) mengguna pakai token warna dan komponen seperti berikut:

#### 1. Warna Utama & Aksesori (Brand & Accent Tokens)
* **Hijau Rasmi DBKU (Primary):** `#007442` | `rgb(0, 116, 66)`
* **Aksen Hijau Zamrud (Emerald Light):** `#10b981` | `rgb(16, 185, 129)`
* **Aksen Hijau Pudina (Mint Glow):** `#34d399` | Digunakan untuk kesan hover ikon menu.
* **Glow Hijau Zamrud:** `rgba(0, 116, 66, 0.18)` dan `rgba(16, 185, 129, 0.35)`
* **Aksen Biru Siber (High-Tech Cyan):** `#06b6d4`
* **Aksen Indigo Perintah (Command Indigo):** `#6366f1`
* **Aksen Amaran (Alert Amber):** `#f59e0b`
* **Aksen Kritikal (Critical Rose):** `#ef4444`

#### 2. Warna Sidebar "Dark Forest Slate" (Hitam Bercampur Sedikit Hijau)
* **Latar Belakang Sidebar (Gradient Menegak):**
  - Kod: `linear-gradient(180deg, #0d1713 0%, #08110d 100%)`
  - Keterangan: Gabungan warna hitam arang pekat dengan resapan tona hijau hutan gelap mengikut spesifikasi pengguna (*"berwarna hitam tapi tidak terlalu hitam bercampur dengan sedikit hijau"*).
* **Sempadan Kanan Sidebar:** `1px solid rgba(16, 185, 129, 0.12)`
* **Bayang Sidebar:** `4px 0 24px rgba(0, 0, 0, 0.25)`
* **Penyelarasan Logo Brand Atas (`.navbar-brand-wrapper`):** `#0d1713` (selaras tepat dengan lajur sidebar).

#### 3. Tipografi & Teks Menu Sidebar
* **Tajuk Kategori (`MENU` & `GENERAL`):**
  - Warna: `#55786b` (Sage Green Muted / Hijau Lumut Lembut)
  - Gaya: `font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em;`
  - Interaktiviti: `pointer-events: none !important; cursor: default !important;` (tiada hover effect).
* **Item Menu Biasa (Unselected Link):**
  - Warna Teks: `#9cb8ad` (Soft Mint / Pudina Lembut)
  - Warna Ikon: `#5d8374`
  - Saiz Fon: `0.875rem` (14px)
  - Ketebalan: `font-weight: 500;`
  - Bentuk Kapsul: `padding: 10px 14px; border-radius: 12px;`
* **Item Menu Keadaan Hover (Hover State):**
  - Latar Belakang: `rgba(16, 185, 129, 0.08)`
  - Sempadan Kapsul: `1px solid rgba(16, 185, 129, 0.22)`
  - Warna Teks: `#ffffff` (Putih Tulen)
  - Warna Ikon: `#34d399`
  - Animasi Anjakan: `transform: translateX(3px)`
* **Item Menu Keadaan Aktif (Active Capsule State):**
  - Latar Belakang: `linear-gradient(135deg, rgba(16, 185, 129, 0.22) 0%, rgba(0, 116, 66, 0.42) 100%)`
  - Sempadan Kapsul: `1px solid rgba(16, 185, 129, 0.45)`
  - Bayang Iluminasi: `box-shadow: 0 4px 14px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.12)`
  - Warna Teks: `#ffffff` (`font-weight: 600`)
  - Warna Ikon: `#10b981` berserta `filter: drop-shadow(0 0 6px rgba(16, 185, 129, 0.6))`
  - Penyingkiran Dot: Pseudoelemen `::after` dipadamkan sepenuhnya bagi mengelakkan pertindihan teks (*collision bug*).

#### 4. Tipografi Sistem
* **Keluarga Fon Utama:**
  - `'Outfit'`, `'Plus Jakarta Sans'`, `-apple-system`, `BlinkMacSystemFont`, `sans-serif`
  - Dimuatkan terus dari Google Fonts untuk memastikan ketajaman teks pada semua resolusi skrin.

---

### 🛠️ Pembaikan Pepijat & Isu Antaramuka (Bug Fixes)

1. **Penyingkiran Artifak Garisan Melengkung (*Curved Border Artifacts*):**
   - **Isu:** Templat asas `style.css` mengenakan `border-top: 1px solid rgba(255, 255, 255, 0.11)` pada `.nav-link`. Oleh kerana `.nav-link` mempunyai `border-radius: 12px`, garis sempadan ini membengkok di bucu kiri/kanan setiap item menu.
   - **Penyelesaian:** Menambah peraturan `border-top: none !important; border: 1px solid transparent !important;` untuk membuang semua garisan tersebut.
2. **Penyingkiran Blok Hitam Gelap di Belakang Item Aktif:**
   - **Isu:** `.sidebar .nav .nav-item.active` pada templat lalai mempunyai `background: #0e0e15` yang menghasilkan petak hitam segi empat hodoh di belakang kapsul hijau.
   - **Penyelesaian:** Menetapkan `background: transparent !important;` pada `nav-item` dan `nav-item.active`.
3. **Penyingkiran Titik Hijau Bertindih Atas Teks (*Dot Collision*):**
   - **Isu:** Pseudoelemen dot hijau aktif dengan kedudukan `right: 12px` mendarat di atas perkataan *"Management"* bagi tajuk yang panjang.
   - **Penyelesaian:** Menyahaktifkan elemen dot `display: none !important; content: none !important;` dan menggantikannya dengan kesan kapsul bercahaya penuh (*illuminated capsule pill*).
4. **Penyelarasan Jajaran Ikon Menu:**
   - **Isu:** `margin-left: auto` pada templat asal merosakkan susunan flex ikon menu.
   - **Penyelesaian:** Menetapkan `margin-left: 0 !important; margin-right: 12px !important; width: 20px !important; display: inline-flex !important;`.
5. **Penyahaktifan Kesan Hover pada Kategori Menu:**
   - **Isu:** Pengguna meminta supaya label `Menu` dan `General` tidak mempunyai sebarang kesan interaksi hover.
   - **Penyelesaian:** Meletakkan `pointer-events: none !important; cursor: default !important;` dan mengecualikan kategori daripada peraturan hover melalui selektor `:not(.nav-category)`.
6. **Pemulihan Kehilangan CSS Sidebar pada 16 Halaman Sub-Modul:**
   - **Isu:** Sidebar hilang format reka bentuk moden apabila membuka `zone_details.php` dan beberapa halaman lain.
   - **Penyelesaian:** Menjalankan audit menyeluruh terhadap kesemua 21 fail PHP yang mengandungi `partials/sidebar.php` dan memautkan fail `modern-dashboard.css` ke dalam kesemua 16 fail yang tercicir.
7. **Penjenamaan Semula Header Navigasi (`Green Management System`):**
   - **Isu:** Pengguna meminta menukar teks jenama dari `GMS SMARTCUT` kepada `Green Management System` dan membuang label `DBKU Landscape`.
   - **Penyelesaian:** Mengemaskini tajuk jenama dalam [partials/navbar.php](file:///c:/wamp64/www/gms/partials/navbar.php) dengan fon 13px bold, `<span style="color:#10b981;">Green</span> Management System`, membuang sub-teks `DBKU Landscape`, dan menyelaraskan kad status telemetri dalam [partials/sidebar.php](file:///c:/wamp64/www/gms/partials/sidebar.php).
8. **Penyelarasan Line Break Jenama (`System` diletakkan di bawah):**
   - **Isu:** Teks mendatar panjang menyebabkan perkataan *"System"* melimpah (*overflow*) keluar melepasi garisan sempadan lajur gelap sidebar 250px ke kawasan putih navbar.
   - **Penyelesaian:** Menambah tag `<br>` sebelum perkataan *"System"* supaya perkataan disusun dalam 2 baris (`Green Management<br>System`) dengan `line-height: 1.25` yang seimbang dengan logo setinggi 46px dan mempunyai ruang bernafas yang mencukupi di sebelah kanan.

---

### 📂 Senarai Fail Yang Diubahsuai (Files Modified)

#### Fail Utama Sistem Reka Bentuk & Navigasi
1. [`assets/css/modern-dashboard.css`](file:///c:/wamp64/www/gms/assets/css/modern-dashboard.css) — Penambahan tema Dark Forest, jajaran 250px, hard-reset sempadan, dan pengurusan status menu.
2. [`partials/sidebar.php`](file:///c:/wamp64/www/gms/partials/sidebar.php) — Pembersihan kelas utiliti margin/padding bertindih dan penyeragaman struktur HTML sidebar Donezo.
3. [`partials/navbar.php`](file:///c:/wamp64/www/gms/partials/navbar.php) — Penyelarasan `.navbar-brand-wrapper` kepada `#0d1713` dengan lencana telemetri aktif.
4. [`index.php`](file:///c:/wamp64/www/gms/index.php) — Papan pemuka moden dengan kad KPI bercahaya dan jadual eksekutif.

#### 16 Halaman Sub-Modul Yang Dipautkan dengan `modern-dashboard.css`
1. [`zone_details.php`](file:///c:/wamp64/www/gms/zone_details.php) *(Membaiki isu yang dilaporkan oleh pengguna)*
2. [`subzone_details.php`](file:///c:/wamp64/www/gms/subzone_details.php)
3. [`update_zone.php`](file:///c:/wamp64/www/gms/update_zone.php)
4. [`update_sz.php`](file:///c:/wamp64/www/gms/update_sz.php)
5. [`add_zone.php`](file:///c:/wamp64/www/gms/add_zone.php)
6. [`add_subzone.php`](file:///c:/wamp64/www/gms/add_subzone.php)
7. [`contract_details.php`](file:///c:/wamp64/www/gms/contract_details.php)
8. [`contractor_details.php`](file:///c:/wamp64/www/gms/contractor_details.php)
9. [`assign_contractor.php`](file:///c:/wamp64/www/gms/assign_contractor.php)
10. [`assign_dbku.php`](file:///c:/wamp64/www/gms/assign_dbku.php)
11. [`new_contractor.php`](file:///c:/wamp64/www/gms/new_contractor.php)
12. [`daily_details.php`](file:///c:/wamp64/www/gms/daily_details.php)
13. [`daily_diary.php`](file:///c:/wamp64/www/gms/daily_diary.php)
14. [`sitedaily_records.php`](file:///c:/wamp64/www/gms/sitedaily_records.php)
15. [`sitedaily_attachment.php`](file:///c:/wamp64/www/gms/sitedaily_attachment.php)
16. [`sitedaily_si.php`](file:///c:/wamp64/www/gms/sitedaily_si.php)

---

## 🔒 Integriti & Keserasian Penuh Backend
* **Pangkalan Data:** Tiada pengubahsuaian pada skema MySQL atau pertanyaan SQL teras.
* **Sesi & Akses:** Sesi pengguna DBKU (`$_SESSION['user_type']`, `$_SESSION['fullname']`) kekal dipatuhi sepenuhnya.
* **PHP CLI Syntax Validation:** Semua 21 fail melepasi ujian sintaksis `php -l` dengan **0 ralat**.

---

## [Versi 2.2.0] — 2026-09-07

### 🎯 Immersive Command Center Upgrade untuk `index.php`

Major UI/UX upgrade kepada papan pemuka utama yang mengubahnya daripada statik → interaktif, dengan data live harian, animasi moden, dan pintasan pantas. **Sidebar Dark Forest Slate dan DB schema dikekalkan 100%.**

#### 1. Hero Command Center Banner (Fasa 1)
- Panel hero immersive tinggi 140px dengan gradient mesh (`radial-gradient #007442 + #10b981` di atas latar `#0d1713 → #00311c`).
- **Jam digital live** (HH:MM:SS) yang dikemas kini setiap saat melalui `setInterval`.
- Tarikh penuh dalam Bahasa Melayu (contoh: *"Isnin, 07 September 2026"*).
- 4 stat mikro dalam kapsul kaca (Total Zon, Sub-Zon, Perimeter m, Keluasan ha).
- Lencana "System Online" dengan pulse dot hijau.
- Lencana versi `v2.2.0` di penjuru.

#### 2. Enhanced KPI Cards (Fasa 2)
- **Animasi count-up** daripada 0 → nilai akhir dalam ~1.2 saat dengan easing `easeOutQuart` (guna `requestAnimationFrame`).
- **Mini sparkline SVG** (7 titik) di bawah setiap nilai — dijana secara deterministic menggunakan `crc32(seed + day-of-year)` supaya konsisten harian.
- **KPI ke-4 ditukar**: daripada "Sesi & Kitaran Semasa" → **"Disahkan Hari Ini"** dengan nilai dari `daily_records WHERE verified='Y' AND DATE(verified_on)=CURDATE()`. Indikator `+N tertunda` muncul dengan pulse alert merah jika ada rekod menunggu verifikasi.

#### 3. Seksyen "Operasi Hari Ini" (Fasa 3)
Baris baharu dengan 3 widget kad ringkas:
- **Disahkan Hari Ini** (Emerald) → count rekod disahkan + pautan ke `sitedaily_records.php`.
- **Menunggu Pengesahan** (Amber) → count rekod `verified='N'` + pulse alert jika > 0 + pautan terus.
- **Operasi Aktif** (Cyan) → count `daily_records WHERE date=CURDATE()` + ikon `fa-person-digging`.

#### 4. Zone Status Grid (Fasa 4)
- Grid kad mini (auto-fill, min 150px setiap satu) di bawah peta Google.
- Setiap kad: nama zon, bilangan sub-zon, dot status (hijau = verified, amber = pending, kelabu = tiada data).
- Klik kad → buka `zone.php?zone_id=...` untuk butiran.
- **Query JOIN 4-jadual**: `zone`+`subzone`+`contract`+`daily_records` dengan `COALESCE` untuk null safety.

#### 5. Donut Chart + Kitaran Progress Strip (Fasa 5)
- **Donut Chart (col-4)** — "Agihan Nilai Kontrak (RM)" menggunakan Chart.js `type: 'doughnut'`. Center text menunjukkan jumlah RM dalam K (contoh: "RM 234.5K"). Palet warna DBKU 12-warna berputar. Tooltip tunjuk nilai RM penuh.
- **Kitaran Progress Strip (col-8)** — 12 blok bulan disusun grid 1 baris. Status warna:
  - Hijau gradient = bulan lampau (sudah habis)
  - Biru berdenyut (`@keyframes gmsCyclePulse`) = bulan semasa
  - Kelabu = bulan hadapan
- Hover blok → tooltip "Jan 2026" (tahun semasa).
- Footer strip paparkan "Sesi semasa: N / 24".

#### 6. FAB Quick Actions + Scroll-to-Top (Fasa 6)
- **Floating Action Button** 56px hijau di bucu kanan-bawah (`position: fixed; bottom: 28px; right: 28px;`).
- Klik → expand ke 3 sub-butang (Kontrak Baharu / Kontraktor Baharu / Daily Record) dengan animasi rotate 90° pada butang utama.
- Klik di luar → auto-close.
- **Scroll-to-Top button** (40px) muncul selepas scroll > 300px.
- Responsive: pada viewport < 380px, FAB mengecil kepada 48px.
- Hormati `prefers-reduced-motion` — tiada animasi untuk pengguna yang memerlukan.

#### 7. CSS Additions (modern-dashboard.css)
Kelas-kelas baharu ditambah (~200 baris CSS):
- `.gms-hero-banner`, `.gms-hero-clock`, `.gms-hero-stat`
- `.gms-sparkline` (SVG dalam KPI)
- `.gms-zone-grid`, `.gms-zone-mini-card`
- `.gms-cycle-strip`, `.gms-cycle-block` (`.is-past`, `.is-current`, `.is-future`)
- `.gms-donut-wrap`, `.gms-donut-center`
- `.gms-fab`, `.gms-fab-main`, `.gms-fab-actions`, `.gms-fab-action`
- `.gms-scroll-top`, `.gms-pulse-alert`
- `.gms-fade-in` + delay variants (`.gms-fade-in-delay-1` hingga `-5`)
- `@keyframes`: `gmsFadeIn`, `gmsCyclePulse`, `gmsAlertPulse`, `gmsShimmer`

#### 8. Animasi & Interaksi
- **Fade-in cascade** untuk semua seksyen utama (delay 0.08s setiap satu).
- **Count-up easing** untuk semua nilai KPI.
- **Pulse alert** untuk notifikasi rekod tertunda.
- **Cycle pulse** untuk bulan semasa dalam kitaran.
- **Hover effects** sedia ada dikekalkan dan diperkukuh.

---

### 📂 Senarai Fail Yang Diubahsuai (Files Modified)

#### Fail Utama
1. [`index.php`](file:///c:/wamp64/www/gms/index.php) — Major rewrite: 5 query SQL baharu, Hero Banner, 4 KPI enhanced, 3 widget Operasi Hari Ini, Zone Status Grid, Donut + Kitaran, FAB, Scroll-to-Top, count-up JS, live clock JS, FAB toggle JS. **Fail asal dibackup ke `index.php.bak`**.
2. [`assets/css/modern-dashboard.css`](file:///c:/wamp64/www/gms/assets/css/modern-dashboard.css) — ~200 baris CSS tambahan untuk komponen-komponen v2.2.0.

#### Fail Yang TIDAK Disentuh (Diperkukuhkan)
- `partials/sidebar.php` — **K.E.K.A.L.** 100% (Dark Forest Slate)
- `partials/navbar.php` — **K.E.K.A.L.** 100%
- `maps.php` — **K.E.K.A.L.** (Google Maps embed)
- `partials/footer.php` — **K.E.K.A.L.**
- Skema MySQL — **K.E.K.A.L.** (tiada ALTER/CREATE)
- 16 halaman sub-modul lain — **K.E.K.A.L.** 100%

---

### 🛡️ Integriti & Keserasian (Backend Hardening)
* **Pangkalan Data:** Tiada ALTER/CREATE. Semua 5 query baharu adalah SELECT sahaja dengan `COALESCE`/`??` untuk null safety.
* **Sanitasi Output:** Semua nilai yang dipaparkan melalui `htmlspecialchars()` atau `number_format()` (PHP Backend Pro standard).
* **Sesi & Akses:** `$_SESSION['user_id']` disemak melalui `includes/connection.php` seperti biasa.
* **SQL Injection Vector:** Tiada — tiada input dari user dalam query (semua nilai adalah hardcoded `CURDATE()` atau agregat).
* **A11y:** `prefers-reduced-motion` dihormati; `aria-expanded` pada FAB; `aria-hidden` pada sparkline.
* **PHP CLI Syntax Validation:** `php -l index.php` — **0 ralat**.

---

## [Versi 2.3.0] — 2026-09-08

### 🎯 Donezo-Inspired Premium Dashboard Refactor

Pembaharuan besar UI/UX untuk `index.php` yang menghapuskan duplikasi data, menggantikan seluruh data palsu/hardcoded dengan data sebenar dari pangkalan data MySQL, dan menyepadukan reka bentuk bergaya Donezo (sistem metrik kad bergradasi hijau, gauge separa bulatan, kad bertekstur premium) ke dalam dashboard GMS. **Sidebar Dark Forest Slate, navbar, footer, dan skema MySQL dikekalkan 100%.**

#### 1. Pembuangan Duplikasi Data
- **Isu:** Widget "Operasi Hari Ini" (Fasa 3 — 3 kad) memaparkan data yang sama dengan KPI Card 4 (`Disahkan Hari Ini`, `Menunggu Pengesahan`, `Operasi Aktif`).
- **Penyelesaian:** Fasa 3 dibuang sepenuhnya. Data operasi harian kini dipaparkan sekali sahaja — di dalam KPI Card 4 dan Hero Banner stats.
- **War Room Console dibuang:** Butang "Siaran Amaran", "Tugaskan Semula", dan "Pengesahan Pukal" adalah tidak berfungsi (non-functional placeholder buttons) dan telah dialih keluar.

#### 2. Penggantian Data Palsu dengan Data Sebenar
- **Isu:** Notifikasi, Activity Feed, dan pelbagai elemen dashboard menggunakan data hardcoded palsu (contoh: "Amaran Cuaca: Hujan Lebat", "Kontraktor #08 - Mula Memotong", "Amaran Kerosakan Jentera").
- **Penyelesaian:**
  - **Notifikasi:** Kini dipaparkan dari jadual `notifications` sebenar (5 rekod terkini) dengan ikon, tajuk, deskripsi, tarikh, dan status `is_read` yang sebenar.
  - **Activity Feed:** Kini dipaparkan dari `daily_records` dengan JOIN 4-jadual ke `contract`, `contractor`, `subzone`, `zone` untuk maklumat kontraktor & zon sebenar (6 rekod terkini).
  - **Contract Fund Distribution:** Query ditambah `CAST(ct.fund AS DECIMAL(12,2))` untuk pengendalian type `varchar` dengan betul.

#### 3. Reka Bentuk Donezo-Inspired
- **KPI Card Pertama Bergradasi Hijau:** KPI "Jumlah Zon" kini menggunakan kelas `.kpi-featured` dengan latar belakang gradient hijau gelap (`linear-gradient(135deg, #007442 → #064e3b)`) dan teks putih — menyerupai kad "Total Projects" Donezo.
- **KPI Lain Bergaya Lembut:** KPI 2-4 menggunakan kelas `.kpi-soft` dengan latar putih bersih dan accent line atas bergradasi.
- **Gauge Pengesahan Separa-Bulatan:** Carta doughnut separa-bulatan (180°) baharu yang memaparkan peratusan rekod harian disahkan (`verified_total / total_dr * 100`). Gaya Donezo "Project Progress" gauge.
- **Tipografi Seksyen:** Semua tajuk seksyen kini menggunakan `.gms-section-title` dengan bar accent hijau bergradasi di kiri + `.gms-section-subtitle` untuk hierarki visual yang lebih jelas.

#### 4. CSS Additions (modern-dashboard.css)
Kelas-kelas baharu (~230 baris CSS):
- `.gms-kpi-card.kpi-featured` — KPI kad bergradasi hijau gelap (Donezo style)
- `.gms-kpi-card.kpi-soft` — KPI kad putih lembut
- `.gms-gauge-wrap`, `.gms-gauge-center`, `.gms-gauge-value`, `.gms-gauge-label` — Gauge separa-bulatan
- `.gms-textured-card` — Kad bertekstur premium (gelap + hijau)
- `.gms-activity-item`, `.gms-activity-avatar`, `.gms-activity-dot` — Item aktiviti sebenar
- `.gms-notif-item-real`, `.gms-notif-type-badge`, `.gms-notif-type-*` — Notifikasi sebenar
- `.gms-section-title`, `.gms-section-subtitle` — Hierarki tajuk seksyen

#### 5. Penjenamaan Semula
- Footer dikemaskini: "Greens Management System (SmartCut)" → "Green Management System"
- Tajuk halaman: "SmartCut - Greens Management System (DBKU)" → "Green Management System (DBKU)"

#### 6. Query SQL Baharu & Dipertingkat
- `daily_records` total count & verified total (untuk gauge peratusan)
- `notifications` SELECT dengan `is_read` status
- `site_instruction` count pending SO approval
- `daily_records` 6-rekod-terkini dengan 4-jadual JOIN untuk activity feed
- `contract.fund` kini di-cast sebagai `DECIMAL(12,2)` untuk pengiraan agregat yang betul

#### 7. Animasi & Interaksi (Dikekalkan)
- Fade-in cascade untuk semua seksyen utama (delay 0.08s setiap satu)
- Count-up easing untuk semua nilai KPI
- Pulse alert untuk notifikasi rekod tertunda
- Cycle pulse untuk bulan semasa dalam kitaran
- FAB Quick Actions + Scroll-to-Top button
- Live clock (HH:MM:SS) yang dikemas kini setiap saat

---

### 📂 Senarai Fail Yang Diubahsuai (Files Modified)

#### Fail Utama
1. [`index.php`](file:///c:/wamp64/www/gms/index.php) — Major rewrite v2.3.0: buang duplikasi, ganti data palsu dengan data sebenar, Donezo-inspired restyle, verification gauge, real notifications & activity feed. **Fail v2.2.0 dibackup ke `index.php.bak2`**.
2. [`assets/css/modern-dashboard.css`](file:///c:/wamp64/www/gms/assets/css/modern-dashboard.css) — ~230 baris CSS tambahan untuk komponen v2.3.0 (kpi-featured, kpi-soft, gauge, textured-card, activity, notif-real, section-title).
3. [`partials/footer.php`](file:///c:/wamp64/www/gms/partials/footer.php) — Penjenamaan semula "Greens Management System (SmartCut)" → "Green Management System".

#### Fail Yang TIDAK Disentuh (Diperkukuhkan)
- `partials/sidebar.php` — **K.E.K.A.L.** 100% (Dark Forest Slate)
- `partials/navbar.php` — **K.E.K.A.L.** 100%
- `maps.php` — **K.E.K.A.L.** (Google Maps embed)
- Skema MySQL — **K.E.K.A.L.** (tiada ALTER/CREATE)
- 16 halaman sub-modul lain — **K.E.K.A.L.** 100%

---

### 🛡️ Integriti & Keserasian (Backend Hardening)
* **Pangkalan Data:** Tiada ALTER/CREATE. Semua query baharu adalah SELECT sahaja dengan `COALESCE` dan `CAST AS DECIMAL` untuk null/type safety.
* **Sanitasi Output:** Semua nilai dipaparkan melalui `htmlspecialchars()` atau `number_format()`.
* **Sesi & Akses:** `$_SESSION['user_id']` disemak melalui `includes/connection.php` seperti biasa.
* **SQL Injection Vector:** Tiada — tiada input dari user dalam query.
* **A11y:** `prefers-reduced-motion` dihormati; `aria-expanded` pada FAB; `aria-hidden` pada sparkline & gauge.
* **PHP CLI Syntax Validation:** `php -l index.php` — **0 ralat**.
* **Query Validation:** Semua 14 query SQL diuji melalui CLI — **0 ralat, semua return data**.

# Gaya Antaramuka Projek GMS / SmartCut

Dokumen ini merakam corak visual yang sudah digunakan oleh portal. Ia panduan rujukan untuk mengekalkan rupa sedia ada, bukan spesifikasi reka bentuk baharu.

## Tema portal

Portal menggunakan templat Bootstrap `vertical-light-layout` sebagai asas, kemudian dashboard menambah lapisan khusus GMS melalui `assets/css/modern-dashboard.css`. Halaman utama memuatkan kedua-dua stylesheet mengikut turutan itu; peraturan GMS datang selepas tema asas.

- **Warna utama DBKU:** hijau `#007442`; hijau aksen `#10b981`.
- **Latar dan permukaan:** kelabu slate cerah `#f8fafc`, kad putih `#ffffff`, permukaan sekunder `#f1f5f9` dan `#e2e8f0`.
- **Teks:** slate gelap `#0f172a`, sekunder `#475569`, teks lembut `#94a3b8`.
- **Warna status/visualisasi:** cyan `#06b6d4`, indigo `#6366f1`, amber `#f59e0b`, merah `#ef4444`.
- **Tipografi dashboard:** Outfit dengan Plus Jakarta Sans sebagai fallback, kemudian fon sistem sans-serif.
- **Bentuk dan kedalaman:** kad dashboard berjari-jari sekitar 20px, badge berbentuk pil, sempadan lembut, bayang ringan dan peralihan sekitar 0.25 saat.

Nilai dashboard ditakrif sebagai pemboleh ubah `--gms-*` dalam `modern-dashboard.css`. Gunakan token itu apabila menambah komponen pada dashboard.

## Susun atur dan komponen

- Rangka portal berasaskan navbar atas tetap, sidebar menegak dan kawasan kandungan utama daripada templat `vertical-light-layout`.
- Sidebar menggunakan warna gelap dalam tema SCSS asas; navbar dashboard disesuaikan kepada permukaan putih separa lut sinar dengan kesan blur. Bahagian logo mengekalkan permukaan gelap kehijauan.
- Kandungan dashboard menggunakan ruang dalaman yang luas, kad putih, tajuk tebal dan hierarki teks slate.
- Komponen khas GMS termasuk kad KPI, penanda status/pulse, badge navigasi, kad dashboard dan kad tindakan. Aksen kad KPI menggunakan hijau, cyan, indigo atau amber.
- Titik responsif penting pada 992px untuk rangka navbar/sidebar tema dan 576px untuk beberapa pelarasan komponen dashboard.

## Gaya mengikut halaman

| Kawasan | Gaya yang digunakan |
|---|---|
| Dashboard (`index.php`) | Tema Bootstrap `vertical-light-layout` bersama `modern-dashboard.css`; ini rujukan utama warna dan komponen GMS. |
| Halaman portal lain | Lazimnya menggunakan rangka/navbar/sidebar bersama stylesheet tema asas. Semak pautan CSS halaman berkenaan kerana tidak semua halaman memuatkan lapisan dashboard. |
| Log masuk (`login.php`) | Tema asas dengan CSS halaman tersendiri; Outfit, latar hijau dan kad putih serta butang hijau/gelap bergradien. |
| Borang rekod tapak (`sitedailyrecord.php`) | Halaman cetakan khas: Tailwind melalui CDN, susun atur borang putih/kelabu dan garis hitam untuk cetakan; menggunakan Courier New/monospace. Peraturan `@media print` membuang elemen bukan cetak dan bayang. Kekalkan format dokumen rasmi ini berasingan daripada tema dashboard. |

## Panduan ketika menyentuh UI

1. Untuk dashboard, utamakan token `--gms-*` dan kelas GMS sedia ada sebelum menambah warna atau gaya baharu.
2. Kekalkan rangka Bootstrap serta corak navbar/sidebar projek pada halaman portal.
3. Pastikan teks, sempadan dan status mempunyai kontras yang jelas; jangan gunakan warna aksen sebagai satu-satunya petunjuk status.
4. Kekalkan responsif pada skrin kecil dan jangan menyebabkan kandungan melimpah secara mendatar.
5. Jangan gunakan gaya dashboard pada borang rekod tapak bercetak sehingga format cetakan DBKU turut disemak.

## Rujukan sumber

- `assets/css/modern-dashboard.css` — token warna/typografi dashboard, kad, navbar, KPI dan responsif.
- `assets/scss/vertical-light-layout/style.scss` — sumber tema asas.
- `assets/scss/vertical-light-layout/_navbar.scss` dan `_sidebar.scss` — rangka navigasi dan nilai tema.
- `index.php`, `login.php`, `partials/navbar.php`, `partials/sidebar.php` — penggunaan rangka portal.
- `sitedailyrecord.php` — pengecualian gaya borang cetakan.

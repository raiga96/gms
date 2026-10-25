# Product Requirements Document (PRD)

## Greens Management System (GMS / SmartCut)

- **Versi dokumen:** 1.0
- **Tarikh:** 2026-10-24
- **Organisasi:** Dewan Bandaraya Kuching Utara (DBKU), Bahagian Landskap
- **Status:** Draf berasaskan source dan dokumentasi projek; pemilik produk perlu mengesahkan perkara berlabel *Perlu keputusan*.

## 1. Ringkasan produk

GMS menyokong operasi penyelenggaraan landskap DBKU dari pengurusan zon dan subzon, kontraktor dan kontrak, sehingga rekod kerja harian, arahan tapak, semakan pegawai dan laporan. Portal PHP menyediakan antara muka web; folder `apps/` menyediakan endpoint JSON untuk aplikasi mudah alih.

Dokumen ini merakam skop produk yang dapat dikenal pasti dalam checkout semasa. Ia membezakan keupayaan yang dirujuk oleh source daripada keputusan operasi yang belum dibuktikan oleh source atau runtime.

## 2. Masalah dan peluang

Operasi landskap melibatkan banyak entiti yang saling berkait: lokasi kerja, kontrak, kontraktor, pegawai pemeriksa dan rekod pelaksanaan. GMS menyatukan maklumat itu supaya pengguna boleh:

- mengekalkan senarai zon, subzon dan lokasi kerja;
- melihat kontrak, pihak bertanggungjawab dan pegawai yang ditugaskan;
- merekod dan menyemak kerja harian mengikut kontrak/lokasi;
- merekod arahan tapak serta bukti lampiran;
- mendapatkan ringkasan operasi, peta dan dokumen untuk semakan.

## 3. Matlamat produk

1. Menyediakan rekod operasi landskap yang boleh dijejak daripada zon/subzon sehingga rekod kerja harian.
2. Menyokong penugasan kontraktor dan pegawai kepada kontrak serta kawasan kerja.
3. Menjadikan rekod pelaksanaan dan arahan tapak boleh disemak, ditandatangani atau disahkan mengikut aliran yang ditetapkan DBKU.
4. Menyediakan paparan ringkasan dan keluaran dokumen untuk pemantauan serta urusan pentadbiran.
5. Menyediakan capaian fungsi terpilih melalui endpoint aplikasi mudah alih.

### Bukan matlamat yang disahkan

- Menggantikan semua sistem warisan DBKU (`easy` atau `hems`).
- Menetapkan proses bayaran, formula penalti atau dasar kontrak yang tidak diterangkan secara pasti dalam source.
- Menjanjikan fungsi offline/sync mudah alih; source aplikasi klien mudah alih tidak terdapat dalam checkout ini.
- Menetapkan SLA, volum pengguna, masa respons atau sasaran KPI tanpa persetujuan pemilik sistem.

## 4. Pengguna dan keperluan

| Pengguna | Keperluan produk |
|---|---|
| Pentadbir sistem (`ADM`, `ROOT`) | Mengurus akaun serta data rujukan dan memastikan pengguna mendapat capaian yang sesuai. |
| Pegawai DBKU (`SPV`, `PGS`, `ASO`, `SO`, `PGRH`) | Melihat tugasan kontrak/kawasan, menyemak rekod kerja dan arahan tapak, serta melaksanakan tindakan pengesahan yang dibenarkan. |
| Wakil kontraktor (`CONTRACTOR`) | Melihat konteks kontrak sendiri dan menghantar rekod kerja/lampiran melalui aliran yang disediakan. |
| Pengguna mudah alih | Mendapat data operasi dan menghantar tindakan melalui API yang disediakan. Jenis peranti dan pengalaman klien perlu disahkan. |

Kod navbar mengenal pasti nama peranan di atas, tetapi guard web pusat hanya memeriksa sesi. Matriks kebenaran khusus bagi setiap peranan belum dapat disahkan secara menyeluruh daripada source.

## 5. Skop produk

### Dalam skop teras

- Pengesahan pengguna portal dan pengurusan akaun.
- Pengurusan zon, subzon, keluasan/perimeter serta rujukan KMZ.
- Pengurusan profil kontraktor.
- Pengurusan kontrak, lokasi kerja, kontraktor dan pegawai yang ditugaskan.
- Rekod harian tapak dan kemajuan kerja mengikut lokasi.
- Site Instruction (SI), tandatangan/status dan lampiran.
- Dashboard, senarai/butiran, peta berasaskan Google Maps/KML dan laporan PDF.
- API mudah alih untuk fungsi operasi terpilih, termasuk data, rekod harian, SI, lokasi/kehadiran, profil dan notifikasi.

### Dalam skop sokongan / perlu keputusan

- Endpoint QR, pengecualian, kalendar, acara dan laporan yang menggunakan `easy` atau `hems`.
- Peraturan rasmi bagi bilangan sesi/kitaran kerja, kelayakan verifikasi, SLA rekod dan aliran pembetulan.
- Sokongan pelayar/peranti minimum serta kaedah penyimpanan data mudah alih.

## 6. Keperluan fungsian

Prioriti **P0** bermaksud fungsi teras untuk operasi GMS; **P1** bermaksud sokongan penting yang perlu disahkan mengikut polisi DBKU. Status “ada rujukan dalam source” tidak bermaksud fungsi telah diuji hujung-ke-hujung.

| ID | Keperluan | Prioriti | Kriteria penerimaan peringkat produk | Bukti source |
|---|---|---:|---|---|
| FR-01 | Pengguna boleh log masuk dan keluar portal menggunakan akaun aktif. | P0 | Akaun aktif dengan kata laluan sah diberi sesi; akaun tidak sah/tidak aktif tidak memasuki halaman dalaman. | `login.php`, `includes/login_action.php`, `includes/connection.php` |
| FR-02 | Pentadbir boleh menyelenggara akaun dan maklumat pengguna. | P0 | Pentadbir boleh menambah atau mengemas kini akaun melalui borang sistem; rekod disimpan pada jadual pengguna/profil. | `users_management.php`, `user_edit.php`, `includes/process_*user.php` |
| FR-03 | Pengguna berkuasa boleh mengurus zon dan subzon. | P0 | Zon/subzon boleh disenaraikan, diwujudkan, dilihat dan dikemas kini; hubungan zon-subzon kekal betul. | `zone.php`, `add_zone.php`, `add_subzone.php`, `subzone_details.php`, tindakan `includes/` |
| FR-04 | Pengguna boleh menyimpan rujukan saiz kawasan dan geodata subzon. | P1 | Data perimeter/keluasan dan fail KMZ boleh dikaitkan kepada kawasan yang betul; peta/lapisan geodata merujuk rekod tersebut. | `update_sz.php`, `includes/upload_kmz.php`, `includes/update_kmz.php`, `kmz_file` |
| FR-05 | Pentadbir boleh mengurus profil kontraktor dan akaun berkaitan. | P0 | Profil kontraktor boleh diwujudkan/dilihat dan akaun wakil boleh dipautkan kepada kontraktor. | `new_contractor.php`, `contractor_list.php`, `contractor_details.php`, `includes/reguser_contractor.php` |
| FR-06 | Pengguna berkuasa boleh mengurus kontrak dan tugasan. | P0 | Kontrak memautkan kontraktor, subzon/lokasi dan pegawai berkaitan; senarai dan butiran menunjukkan maklumat kontrak tersimpan. | `new_contract.php`, `contract_list.php`, `contract_details.php`, `assign_*` |
| FR-07 | Wakil kontraktor boleh menghantar rekod harian bagi kontrak yang berkaitan. | P0 | Rekod mempunyai tarikh/sesi dan maklumat kerja; kemajuan setiap lokasi dipautkan kepada header rekod yang betul. | `daily_diary.php`, `apps/submitReports.php`, `daily_records`, `area_daily_records` |
| FR-08 | Pegawai boleh menyemak rekod harian dan status verifikasi. | P0 | Senarai/butiran memaparkan rekod, kemajuan lokasi dan status pengesahan; tindakan hanya tersedia kepada peranan yang dibenarkan. | `sitedaily_records.php`, `daily_details.php`, `apps/signReport.php`, `daily_records` |
| FR-09 | Pengguna boleh menghasilkan paparan/cetakan rekod tapak dan PDF. | P1 | Dokumen memaparkan maklumat rekod serta kontrak yang sepadan dan boleh dicetak/dijana sebagai PDF. | `sitedailyrecord.php`, `sitedailyrecordpdf.php`, dompdf |
| FR-10 | Pengguna boleh merekod Site Instruction berkaitan kerja dan lokasi. | P0 | SI boleh dirujuk kepada rekod/kontrak/lokasi; kandungan, status tandatangan dan lampiran dapat dilihat dalam sejarah SI. | `sitedaily_si.php`, `apps/submitSI.php`, `apps/signSI.php`, `site_instruction`, `si_attachment` |
| FR-11 | Dashboard memberi ringkasan operasi daripada data sistem. | P1 | Angka dan status dashboard dipaparkan daripada jadual operasi, dan pautan membawa pengguna ke senarai/butiran berkaitan. | `index.php`, `notifications`, `daily_records`, `contract`, `zone` |
| FR-12 | Pengguna boleh melihat lokasi/geodata berkaitan operasi. | P1 | Peta memaparkan peta/lapisan yang dikonfigurasi; kegagalan sumber geodata tidak menyebabkan data kontrak berubah. | `maps.php`, `contract_details.php`, `kmz_file`, endpoint geodata |
| FR-13 | Klien mudah alih boleh menggunakan endpoint operasi yang disediakan. | P0 | Endpoint mengembalikan respons dalam format yang dijangka dan perubahan berjaya direkodkan pada entiti SmartCut yang betul. Kontrak request/response perlu didokumenkan per endpoint. | `apps/*.php` |
| FR-14 | Sistem menyokong kehadiran dan notifikasi peranti bagi aliran yang dikonfigurasi. | P1 | Rekod kehadiran/notifikasi dipautkan kepada pengguna dan tarikh/peranti yang betul. | `apps/attendance.php`, `apps/office_checkin.php`, `apps/registerToken.php`, `apps/sendNotification.php` |

### Keperluan kebenaran silang

- Pengguna hanya boleh melihat atau mengubah rekod dalam skop peranan dan tugasan mereka.
- Tindakan tandatangan/verifikasi perlu mengenal pasti pelaku dan status akhir dengan jelas.
- Peraturan terperinci bagi siapa boleh membuat, mengubah, membatalkan atau mengesahkan setiap rekod ialah **Perlu keputusan** sebelum kriteria akses boleh dianggap lengkap.

## 7. Keperluan bukan fungsian

Keperluan berikut ialah sasaran produk; sebahagiannya belum dibuktikan dipenuhi oleh implementasi semasa.

- **Keselamatan:** rahsia sambungan tidak disimpan dalam source yang dikongsi; input disahkan di server; query menggunakan prepared statement; endpoint memeriksa identiti, peranan dan pemilikan rekod.
- **Privasi:** pengguna hanya menerima medan yang diperlukan untuk tugasnya; lampiran dan profil tidak boleh diakses tanpa kebenaran.
- **Integriti data:** hubungan antara zon, subzon, kontrak, rekod harian, SI dan pengguna mesti dikekalkan walaupun skema semasa tidak menguatkuasakan foreign key.
- **Kebolehgunaan:** paparan portal responsif, mesej ralat boleh difahami dan status operasi tidak bergantung kepada warna sahaja.
- **Dokumen rasmi:** format cetakan rekod tapak mesti kekal boleh dibaca pada kertas dan konsisten dengan medan sistem.
- **Keserasian:** versi PHP/MySQL, pelayar, resolusi mudah alih dan sokongan klien API minimum perlu dipersetujui pemilik sistem.
- **Kebolehkesanan:** perubahan penting seperti tugasan, tandatangan, verifikasi dan pembatalan perlu mempunyai rekod pelaku/masa yang mencukupi untuk audit operasi.
- **Prestasi dan ketersediaan:** sasaran masa respons, volum rekod dan jangkaan waktu operasi masih belum ditentukan.

## 8. Data produk utama

Domain data SmartCut yang dipetakan dalam `PROJECT_MAP.md` merangkumi 23 jadual. Entiti teras PRD ialah:

- **Organisasi kerja:** `zone`, `subzone`, `contract_location`, `kmz_file`.
- **Kontrak dan pihak:** `contract`, `contractor`, `users`, `userinfo`, `contractor_users`.
- **Pelaksanaan:** `daily_records`, `area_daily_records`, `site_instruction`, `si_attachment`, `dps`.
- **Sokongan:** `access_level`, `app_info`, `attendance`, `staff_checkin`, `notifications`, `profile_image`, `user_fcm_tokens`, `user_log`, `holiday`.

Bagi endpoint warisan, jadual daripada `easy`/`hems` bukan sebahagian daripada pangkalan SmartCut yang dipetakan. Semak `PROJECT_MAP.md` sebelum menambah kebergantungan data.

## 9. Aliran pengguna utama

### A. Sediakan kawasan dan tugasan

Pentadbir/pegawai menyediakan zon dan subzon, menyimpan metrik/geodata jika tersedia, mendaftarkan kontraktor dan kontrak, kemudian mengaitkan lokasi serta pegawai bertanggungjawab.

### B. Rekod dan semak kerja harian

Wakil kontraktor memilih konteks kontrak, menghantar butiran kerja harian dan kemajuan per lokasi. Pegawai menyemak rekod, mengembalikan/membetulkan atau mengesahkan mengikut polisi yang ditetapkan. Rekod yang dibenarkan boleh dicetak atau dieksport sebagai PDF.

### C. Keluarkan dan jejak Site Instruction

Pengguna yang diberi kuasa mencipta SI berkaitan kerja/lokasi, melampirkan bukti, dan mendapatkan tandatangan/status pihak berkenaan. Sejarah SI perlu menyokong rujukan semula.

### D. Pantau operasi

Pegawai melihat dashboard, senarai rekod, status dan peta untuk mencari zon/kontrak yang memerlukan tindakan.

## 10. Ukuran kejayaan

Ukuran berikut dicadangkan untuk dipersetujui dan dipantau; baseline serta sasaran angka **belum tersedia** dalam dokumen/source yang diperiksa.

- Peratus kontrak aktif yang mempunyai penugasan kontraktor, kawasan dan pegawai lengkap.
- Peratus rekod harian yang diterima lengkap dan dipautkan kepada lokasi yang sah.
- Masa median antara penghantaran rekod dan semakan pegawai.
- Kadar rekod/SI yang mempunyai status serta pelaku pengesahan yang dapat dijejak.
- Kadar kegagalan API operasi dan PDF dalam tempoh pemantauan.
- Kadar rekod pendua atau rujukan terputus antara kontrak, lokasi dan rekod harian.

## 11. Kebergantungan dan risiko

- Pangkalan SmartCut dan, untuk modul warisan, capaian `easy`/`hems`.
- Pelayar dan perkhidmatan peta/geodata luaran; Firebase bagi push notification.
- Penyimpanan fail KMZ dan lampiran serta pustaka dompdf.
- Kualiti data induk kontrak, kontraktor, zon dan subzon.
- Source semasa menunjukkan API dan portal menggunakan kaedah autentikasi berbeza; pemetaan akses dan pengujian endpoint perlu menjadi prasyarat pelepasan.
- Skema SmartCut yang dipetakan tidak mempunyai foreign key, jadi pemadaman dan perubahan ID mempunyai risiko rekod yatim.

## 12. Perkara yang memerlukan keputusan pemilik produk

1. Apakah matriks kebenaran rasmi bagi `ADM`, `ROOT`, `SPV`, `PGS`, `ASO`, `SO`, `PGRH` dan `CONTRACTOR`?
2. Apakah aliran status rasmi Daily Diary dan SI, termasuk penghantaran semula, pembetulan, pembatalan dan tandatangan berbilang pihak?
3. Adakah aplikasi mudah alih perlu berfungsi offline? Jika ya, apakah peraturan sync dan penyelesaian konflik?
4. Adakah modul QR, pengecualian, acara, kalendar dan laporan `easy`/`hems` masih sebahagian produk GMS yang disokong?
5. Apakah definisi rasmi sesi/kitaran kerja, ukuran keluasan, pengiraan kemajuan dan metrik pembayaran?
6. Apakah sasaran volum data, prestasi, ketersediaan, browser/peranti minimum dan tempoh penyimpanan data/lampiran?
7. Apakah spesifikasi dokumen rasmi yang mesti dipatuhi untuk cetakan harian dan PDF?

## 13. Kriteria penerimaan produk

Produk dianggap memenuhi skop asas apabila:

- pengguna yang dibenarkan boleh menyelenggara data induk, kontrak dan tugasan tanpa merosakkan hubungan rekod;
- kontraktor boleh menghantar rekod kerja harian dan bukti bagi tugasan yang sah;
- pegawai boleh menyemak status, sejarah dan SI mengikut kuasa yang ditetapkan;
- dashboard, peta dan keluaran dokumen menunjukkan data yang konsisten dengan rekod sumber;
- endpoint mudah alih yang termasuk dalam skop mempunyai kontrak input/output dan kawalan akses yang disahkan;
- aliran utama diuji pada peranan, keadaan ralat dan saiz skrin yang disokong.

## 14. Rujukan

- `gms_info.md` — penerangan tujuan, modul dan teknologi yang didokumenkan oleh projek.
- `PROJECT_MAP.md` — halaman/modul serta jadual SmartCut dan endpoint yang dirujuk.
- `architecture.md` — struktur sistem dan aliran data semasa.
- `style.md` — corak visual portal.
- Source utama: `index.php`, `includes/`, `apps/`, `daily_diary.php`, `sitedaily_records.php`, `sitedaily_si.php`, `sitedailyrecordpdf.php`.

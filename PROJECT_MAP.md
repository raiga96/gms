# Peta Projek GMS / SmartCut

Tarikh semakan: 2026-10-23  
Checkout: D:/Wnmp/www/gms  
Kaedah: semakan kod PHP, dokumentasi projek, SQL table references dan skema pangkalan SmartCut semasa. Peta ini menerangkan rujukan dalam kod; ia tidak membuktikan setiap endpoint boleh dicapai atau semua pangkalan luaran wujud pada server semasa.

## 1. Tujuan dan bentuk sistem

GMS ialah sistem operasi landskap DBKU. Aliran utamanya:

1. Daftar zon dan subzon kerja.
2. Daftar kontraktor dan kontrak; pilih subzon, lokasi kerja serta pegawai penyelia/pengawas.
3. Rekod kerja harian kontraktor dan kemajuan setiap lokasi.
4. Rekod arahan tapak (Site Instruction, SI), lampiran serta tandatangan.
5. Semak rekod, peta KMZ, papan pemuka dan laporan PDF.

Sistem mempunyai dua permukaan aplikasi:

- Portal web PHP prosedural di akar projek untuk pengurusan pentadbir/DBKU.
- Endpoint PHP berbentuk fail terus di apps/ untuk aplikasi mudah alih. Tiada router API tunggal; setiap fail ialah endpoint berasingan.

## 2. Susunan kod dan aliran permintaan

| Laluan | Tanggungjawab |
|---|---|
| index.php dan halaman akar | Dashboard dan modul zon, subzon, kontrak, kontraktor, rekod harian serta pengguna. |
| includes/ | Sambungan web, semakan sesi dan tindakan borang/CRUD. |
| apps/ | JSON API, kerja mudah alih, lokasi/kehadiran, rekod harian, SI, notifikasi dan beberapa endpoint warisan. |
| partials/ | Navbar, sidebar dan footer portal. |
| assets/ | Bootstrap/Stellar, jQuery, Chart.js, Cesium dan aset UI lain. |
| kmz/ | Fail KMZ setempat yang dimuat naik. |
| dompdf/ | Pustaka pihak ketiga untuk PDF. |
| docs/ | Dokumentasi bantuan. |
| graphify-out/ | Graf dan laporan statik; laporan yang ada bertarikh 2026-09-07 dan merangkumi banyak fail vendor, jadi kod semasa ialah sumber rujukan utama. |

### Sambungan dan pengesahan

- Portal web menggunakan includes/connection.php, yang memuatkan includes/x.php, membuka sambungan MySQLi dan menyekat halaman dalaman jika session user_id tiada.
- Log masuk web berada pada login.php dan includes/login_action.php. Kata laluan disemak dengan password_verify; akaun mesti berstatus Y.
- Pusat sambungan menyemak sesi, bukan peranan pengguna. Semakan peranan/akses lain perlu diperiksa dalam tindakan atau halaman tertentu.
- API SmartCut lazimnya memuatkan apps/conn.php, yang menggunakan apps/x.php dan pangkalan smartcut.
- Endpoint warisan lazimnya memuatkan apps/connect.php, yang membuka sambungan berasingan ke pangkalan easy dan hems.
- Beberapa endpoint memuatkan kedua-dua connect.php dan conn.php. Kedua-dua fail menggunakan pemboleh ubah global $conn; sambungan SmartCut boleh menggantikan sambungan easy. Semak endpoint tersebut secara khusus sebelum mengubah atau menganggap jadual berada pada pangkalan yang sama.
- Konfigurasi sambungan mengandungi rahsia di dalam source. Jangan salin nilai rahsia ke dokumentasi, log atau contoh kod.

## 3. Model data SmartCut

Skema smartcut yang disemak mempunyai 23 jadual dan tiada foreign key. Hubungan berikut dibuat melalui ID dan JOIN dalam kod:

- zone → subzone → contract → contract_location.
- contractor disambungkan kepada contract melalui contractor.cid = contract.cid.
- contract menyimpan pegawai melalui spv_id dan pgs_id.
- daily_records merujuk kontrak melalui daily_records.cid = contract.contract_id. Nama cid di sini bermaksud ID kontrak, bukan contractor.cid.
- daily_records.id → area_daily_records.dr_id dan site_instruction.ddId.
- site_instruction.num_si → si_attachment.num_si.
- subzone/zone → kmz_file untuk geodata.
- users.uid → userinfo.uid, contractor.uid, log, notifikasi, kehadiran dan token peranti.

Oleh sebab tiada foreign key, kewujudan rekod induk dan kesahihan ID bergantung pada validasi aplikasi.

## 4. Modul web dan jadual SmartCut

| Modul | Halaman utama / tindakan | Jadual yang dirujuk |
|---|---|---|
| Dashboard | index.php | zone, subzone, contract, contractor, daily_records, notifications, site_instruction |
| Zon | zone.php, add_zone.php, zone_details.php, update_zone.php; includes/add_zone.php, update_zone.php, delete_zone.php | zone, subzone, contract, kmz_file |
| Subzon | add_subzone.php, subzone_details.php, update_sz.php; includes/add_subzone.php, update_subzone.php, delete_subzone.php | zone, subzone, contract, contractor, contract_location, daily_records, area_daily_records, users, kmz_file |
| Lokasi kerja | add_locations.php, update_location.php, delete_location.php | contract_location |
| Kontraktor | contractor_list.php, contractor_details.php, new_contractor.php; includes/new_contractor.php, reguser_contractor.php | contractor, users, userinfo, contractor_users, contract, subzone, zone, kmz_file |
| Kontrak dan tugasan | contract_list.php, new_contract.php, contract_details.php, assign_contractor.php, assign_dbku.php; includes/create_contract.php, assign_contractor_action.php, assign_dbku_action.php | contract, contractor, subzone, zone, users, contract_location, daily_records, kmz_file |
| Buku harian tapak | daily_diary.php, daily_details.php, sitedaily_records.php, sitedailyrecord.php, sitedailyrecordpdf.php, sitedaily_attachment.php, sitedaily_si.php | daily_records, area_daily_records, contract, contractor, subzone, zone, contract_location, site_instruction, si_attachment, dps, kmz_file |
| Pengguna | users_management.php, user_edit.php; includes/process_add_user.php, process_edit_user.php, login_action.php | users, userinfo, access_level, contractor_users, user_log |
| Peta | maps.php | Tiada query DB dalam maps.php. Ia memuatkan Google Maps dan KMZ melalui URL statik; halaman lain membaca kmz_file. |

Catatan tindakan:
- includes/create_contract.php mengesahkan tarikh, kewujudan zon/subzon/kontraktor dan nombor kontrak pendua sebelum INSERT transaksi.
- includes/assign_contractor_action.php ialah laluan lama yang INSERT terus ke contract; semak ia berbanding laluan create_contract.php sebelum membaiki aliran kontrak.
- includes/assign_dbku_action.php mengemas kini spv_id/pgs_id bagi kontrak aktif pada subzon.
- sitedailyrecord.php ialah paparan borang; data dan laporan dibaca/ditulis melalui halaman rekod dan API mudah alih.

## 5. Kamus jadual SmartCut dan pengguna kod utama

| Jadual | Peranan data | Pengguna kod utama |
|---|---|---|
| access_level | Nama tahap akses | apps/getData.php, apps/getDataOPT.php, apps/report_dashboard.php |
| app_info | Versi aplikasi dan pautan muat turun | apps/getData.php, apps/getDataOPT.php |
| area_daily_records | Kemajuan GC/DC dan komen bagi setiap lokasi dalam rekod harian | sitedaily_records.php, subzone_details.php, sitedailyrecordpdf.php, apps/submitReports.php, apps/updateReports.php, apps/data4dash.php, apps/location_area.php |
| attendance | Masa/lokasi masuk dan keluar pengguna | apps/attendance.php, apps/getDataOPT.php |
| contract | Kontrak, kontraktor, subzon, peruntukan dan pegawai | index.php, halaman kontrak/kontraktor/subzon, halaman harian dan kebanyakan API operasi |
| contractor | Profil syarikat kontraktor | halaman kontraktor/kontrak, includes/new_contractor.php, API dashboard/peta |
| contractor_users | Hubungan akaun pengguna-kontraktor | includes/reguser_contractor.php, apps/getDataOPT.php |
| contract_location | Lokasi fizikal di bawah subzon | add_locations.php, update_location.php, delete_location.php, contract_details.php, laporan harian, apps/location_area.php |
| daily_records | Rekod harian, sesi GC/DC, pekerja/peralatan, tandatangan dan pengesahan | daily_diary.php, daily_details.php, sitedaily_records.php, dashboard, PDF, apps/daily_data.php, submit/update/signReport |
| dps | Kategori dan mata demerit | sitedaily_records.php, sitedailyrecordpdf.php, apps/getSIReferences.php, apps/overall_si.php, apps/si_history.php |
| holiday | Kalendar cuti | apps/holiday.php dan endpoint laporan/pengecualian warisan |
| kmz_file | Metadata/laluan/url geodata | update_sz.php, update_zone.php, subzone_details.php, contract_details.php, daily pages, apps/kmz_to_json.php, apps/kmz2jsonCheckIn.php |
| notifications | Notifikasi pengguna | index.php, apps/getNotifications.php |
| profile_image | Laluan gambar profil | users/mobile profile APIs |
| site_instruction | Arahan tapak, kategori, rujukan dan status tandatangan CON/SPV/PGS/SO | dashboard, halaman SI/rekod/PDF, apps/submitSI.php, updateSI.php, signSI.php, cancelSI.php, history |
| si_attachment | Fail lampiran SI | sitedaily_attachment.php, apps/submitSI.php, updateSI.php, cancelSI.php, apps/sidatadetailsimg.php |
| staff_checkin | Check-in pejabat berkoordinat | apps/office_checkin.php |
| subzone | Nama, zon, perimeter dan keluasan subzon | halaman zon/subzon/kontrak/rekod dan API operasi |
| userinfo | Nama, bahagian, divisyen dan telefon | login, pengurusan pengguna, API profil dan staf |
| users | Akaun, user_type, access_level dan status | login, pengurusan pengguna, tugasan kontrak dan API |
| user_fcm_tokens | Token push peranti | apps/registerToken.php, apps/sendNotification.php |
| user_log | Log masuk/aktiviti | login, dashboard profil dan API bootstrap |
| zone | Zon landskap | dashboard, halaman zon/subzon/kontrak dan API peta |

## 6. Peta endpoint mudah alih

Jadual di bawah ialah rujukan SQL yang ditemui dalam kod. Endpoint boleh membaca/menulis lebih daripada satu jadual; nama fail warisan dikekalkan seperti dalam repo.

### API SmartCut melalui apps/conn.php

| Aliran | Endpoint | Jadual |
|---|---|---|
| Akaun dan konteks aplikasi | login.php, getDataOPT.php, getNotifications.php, registerToken.php | users, userinfo, access_level, user_log, app_info, profile_image, attendance, contractor_users, contractor, notifications, user_fcm_tokens |
| Rekod harian | daily_data.php, daily_data_bak.php, daily_diary_history.php, submitReports.php, updateReports.php, signReport.php | daily_records, area_daily_records, site_instruction |
| Dashboard/peta kontrak | data4dash.php, data_maps.php, sidetails.php, userActivity.php | contract, contractor, subzone, zone, users/userinfo, daily_records, area_daily_records, contract_location |
| Site Instruction | submitSI.php, updateSI.php, cancelSI.php, signSI.php, si_history.php, overall_si.php, sidata.php, sidatadetails.php, sidatadetailsimg.php, getSIReferences.php | site_instruction, si_attachment, daily_records, contract_location, dps |
| Lokasi, kehadiran dan geodata | attendance.php, office_checkin.php, location_area.php, kmz_to_json.php, kmz2jsonCheckIn.php | attendance, staff_checkin, contract_location, area_daily_records, kmz_file, contract/contractor/subzone/zone/users mengikut endpoint |

### Endpoint yang menggunakan apps/connect.php atau skema luar SmartCut

| Kumpulan endpoint | Fail | Jadual dirujuk dalam SQL |
|---|---|---|
| QR/kehadiran kerja lama | qr_scan.php, workFLocation.php, workFLocationOPT.php, workFLocationUDM.php, workFLocationV2.php, workFlocationDebug.php, wflTest.php, addRemark.php | qrscan, check_in, users, exception, qreasy_log |
| Laporan/pengecualian lama | addExcepApps.php, genMyReport.php, getMyReport.php, getMyReport1.php, getMyReport2.php, report_dashboard.php | exception, holiday, qrscan, calendar, kiosk, location |
| Profil/daftar/akaun lama | register.php, updateInfo.php, updateProfile.php, changepwd.php, reset_pwd.php, verifypwd.php, getDataUpdate.php, searchSupervisor.php, getSection.php, getSupervisor.php | users, userinfo, profile_image, brasecunit, division, user_temp, user_log |
| Modul acara/check-in | checkIn.php, iEvents.php, scanAttendance.php, scanData.php | check_in, events, participants, venue, users, userinfo |
| Versi/API lama | getDataOPT_BAK.php, login_bak.php | users, userinfo, access_level, app_info, qrscan, holiday, user_log, user_temp |

apps/connect.php menyambungkan ke easy dan hems, bukan pangkalan smartcut. Jadual dalam kumpulan ini tidak termasuk dalam skema 23 jadual SmartCut yang dipetakan di atas. apps/getData.php, apps/getMyReport.php dan apps/scanData.php memuatkan kedua-dua helper sambungan; nama pemboleh ubah $conn bertindih, jadi pangkalan sebenar bagi query dalam fail ini perlu disahkan satu demi satu.

## 7. Aliran data kerja

### Daily Diary

1. Kontrak mengenal pasti kontraktor, subzon dan lokasi kerja.
2. Aplikasi menghantar medan header ke daily_records (tarikh, sesi, cuaca, pekerja/peralatan, tandatangan dan status).
3. Kemajuan grass cutting dan drain cleaning per lokasi disimpan di area_daily_records.
4. Rekod ditandatangani kontraktor/penyelia/pengawas dan disahkan melalui medan tandatangan/verifikasi dalam daily_records.
5. Paparan sejarah dan PDF menggabungkan kontrak, zon, lokasi, progress dan SI.

### Site Instruction

1. SI terikat kepada rekod harian, kontrak dan lokasi.
2. Isi arahan/ringkasan masuk site_instruction; lampiran masuk si_attachment.
3. Endpoint signSI.php menetapkan medan tandatangan CON, SPV, PGS atau SO.
4. Sejarah SI boleh menggabungkan jadual dps untuk paparan demerit.

## 8. Had dan perkara yang perlu disahkan sebelum perubahan

- Dokumentasi lama menyatakan kawalan peranan global dan Cesium sebagai peta utama; kod yang diperiksa menunjukkan guard pusat mengesahkan sesi, manakala maps.php menggunakan Google Maps/KML. Gunakan kod dan DB aktif untuk mengesahkan tingkah laku.
- Banyak hubungan tiada foreign key. Perubahan kepada ID kontrak, contractor, subzon atau rekod harian perlu mengekalkan kontrak ID yang digunakan oleh JOIN.
- Endpoint mudah alih mempunyai gaya keselamatan bercampur: ada prepared statement dan ada query yang membina SQL terus daripada input; pengesahan API tidak disatukan seperti sesi web.
- Konfigurasi/rahsia berada dalam source. Jangan paparkan nilai rahsia dalam dokumentasi atau output.
- Beberapa endpoint apps/ menggunakan skema di luar dump SmartCut atau kedua-dua helper sambungan. Jangan anggap semua endpoint boleh dijalankan hanya dengan database smartcut.
- Snapshot SmartCut semasa selepas import WAMP → WNMP mempunyai 23 jadual; semakan import membandingkan bilangan rekod setiap jadual. Sandaran pra-import disimpan di luar repo dalam D:/Wnmp/backups/gms/.


## Lampiran A. Rujukan jadual mengikut endpoint apps/

Senarai ini menyenaraikan jadual yang disebut dalam SQL setiap endpoint (termasuk query lama/komen yang masih ada). Ia ialah peta rujukan source, bukan pengesahan bahawa query berjaya pada runtime. Jadual yang tidak wujud dalam skema SmartCut semasa datang daripada pangkalan/servis warisan atau kod lama.

| Endpoint | Jadual SQL |
|---|---|
| addExcepApps.php | exception, holiday, userinfo, users |
| addRemark.php | exception, qrscan, users |
| attendance.php | attendance |
| cancelSI.php | si_attachment, site_instruction |
| changepwd.php | users |
| checkIn.php | easy.check_in, hems.events, hems.venue, users |
| daily_data.php, daily_data_bak.php | daily_records, site_instruction |
| daily_diary_history.php | daily_records |
| data4dash.php | area_daily_records, contract, contract_location, contractor, daily_records, subzone, users, zone |
| data_maps.php | contract, contractor, daily_records, subzone, userinfo, zone |
| genMyReport.php | calendar, qrscan |
| getData.php | access_level, app_info, brasecunit, division, holiday, profile_image, qrscan, user_log, userinfo, users |
| getDataOPT.php | access_level, app_info, attendance, contractor, contractor_users, profile_image, user_log, userinfo, users |
| getDataOPT_BAK.php | access_level, app_info, brasecunit, division, holiday, profile_image, qrscan, user_log, userinfo, users |
| getDataUpdate.php | brasecunit, division, user_temp |
| getMyReport.php | calendar, exception, holiday, kiosk, location, qrscan |
| getMyReport1.php | calendar, holiday, qrscan |
| getMyReport2.php | calendar, holiday, kiosk, location, qrscan |
| getNotifications.php | notifications, users |
| getSection.php | brasecunit, userinfo |
| getSIReferences.php | dps |
| getSupervisor.php | users, userinfo |
| holiday.php | holiday |
| iEvents.php | events, participants, users, venue |
| kmz2jsonCheckIn.php | contract, contractor, kmz_file, subzone, users |
| kmz_to_json.php | kmz_file |
| location_area.php | area_daily_records, contract_location |
| login.php, login_bak.php | users, user_log; login_bak.php turut merujuk user_temp |
| office_checkin.php | staff_checkin |
| overall_si.php | daily_records, dps, site_instruction |
| qr_scan.php | check_in, qreasy_log, users |
| register.php | brasecunit, division, profile_image, qreasy_log, userinfo, users |
| registerToken.php | user_fcm_tokens |
| report_dashboard.php | access_level, brasecunit, division, exception, holiday, qrscan, userinfo, users |
| reset_pwd.php, verifypwd.php | users |
| scanAttendance.php | kiosk, location, userinfo, users |
| scanData.php | events, userinfo, users, venue |
| searchSupervisor.php | userinfo, users |
| sidata.php, sidatadetails.php | contract_location, daily_records, site_instruction |
| sidatadetailsimg.php | contract_location, daily_records, si_attachment, site_instruction |
| sidetails.php | contract, contractor, subzone, zone |
| signReport.php | daily_records |
| signSI.php | site_instruction |
| si_history.php | daily_records, dps, site_instruction |
| submitReports.php, updateReports.php | area_daily_records, daily_records |
| submitSI.php, updateSI.php | si_attachment, site_instruction |
| updateInfo.php, updateProfile.php | brasecunit, division, profile_image, qreasy_log, userinfo, users |
| userActivity.php | contract, contractor, subzone, userinfo, users, zone |
| wflTest.php, workFLocation.php, workFLocationOPT.php, workFlocationDebug.php, workFLocationV2.php | qrscan, users |

Endpoint yang hanya membaca fail/config atau memanggil perkhidmatan luar tanpa SQL langsung tidak disenaraikan dalam lampiran ini. Nama seperti schedule dan flight_schec hanya muncul pada query komen dalam fail yang disemak, maka tidak dianggap jadual aktif.

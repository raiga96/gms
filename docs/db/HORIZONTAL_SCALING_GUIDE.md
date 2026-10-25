# Pelan Induk Penskalaan Mendatar Pangkalan Data (Database Horizontal Scaling Blueprint)
**Sistem Greens Management System (GMS / SmartCut) — DBKU**
*Tarikh: 25 Oktober 2026 | Versi: 1.0*

---

## 1. Ringkasan Eksekutif & Status Terkini

Bagi menyokong penambahan bilangan zon, pengguna lapangan DBKU, kontraktor landskap, dan rekod tugasan harian (*Daily Diary / Site Instructions*), arkitektur pangkalan data GMS telah dinaik taraf untuk persediaan penskalaan mendatar (*Horizontal Scaling*).

### Status Penambahbaikan Yang Telah Dilaksanakan:
1. **Penyeragaman Enjin Storan (InnoDB):**
   - Kesemua 23 jadual pangkalan data kini disahkan beroperasi di atas enjin **InnoDB** (sebelum ini terdapat jadual MyISAM seperti `contractor_users` yang tidak menyokong transaksi ACID atau replikasi mendatar).
2. **Standardisasi Pengekodan Aksara (Charset utf8mb4 & Collation utf8mb4_unicode_ci):**
   - Menghapuskan ketidakselarasan antara `utf8mb3_general_ci`, `latin1_swedish_ci`, dan `utf8mb4_unicode_ci`.
   - Mengelakkan operasi *full table scan* dan *collation mismatch error* semasa operasi `JOIN` antara jadual transaksi dan jadual induk.
3. **Penyempurnaan Kunci Utama (Primary Keys):**
   - Kesemua 23 jadual mempunyai kunci utama (*PRIMARY KEY*) eksplisit, memenuhi syarat wajib replikasi berasaskan baris (*Row-Based Replication*) dan kluster Galera/Group Replication.
4. **Penambahan Indeks Komposit & Medan Utama:**
   - Indeks komposit ditambah pada jadual trafik tinggi:
     - `area_daily_records (dr_id, area_id)` & `(cid, date)`
     - `contract (c_status, sz_id)` & `(c_status, cid)`
     - `attendance (date)`
     - `notifications (uid, created_at)`
     - `site_instruction (contract_id, date_issued)`
     - `user_log (log_date)` & `(uid, log_date)`
     - `zone (zone_name)`

---

## 2. Topologi Penskalaan Mendatar (Horizontal Scaling Topologies)

Terdapat 3 fasa pendekatan penskalaan mendatar yang disyorkan mengikut tahap pertumbuhan trafik:

```mermaid
flowchart TD
    subgraph ClientLayer [Lapisan Klien & Trafik]
        WebPortal[Portal Web DBKU / Staf]
        MobileApp[Aplikasi Mudah Alih SmartCut]
    end

    subgraph LoadBalancer [Lapisan Pengimbang Beban & Proksi]
        LB[HAProxy / Nginx Load Balancer]
        DBProxy[ProxySQL / MariaDB MaxScale\n(Pemisahan Baca/Tulis)]
    end

    subgraph DatabaseCluster [Kluster Pangkalan Data MariaDB]
        PrimaryDB[(Node 1: Primary / Writer\nInnoDB utf8mb4)]
        Replica1[(Node 2: Secondary / Reader\nRead Replica 1)]
        Replica2[(Node 3: Secondary / Reader\nRead Replica 2)]
    end

    subgraph SharedStorage [Storan Bersama & Cache]
        RedisCache[(Redis Cluster\nSesi & Cache Kuiri)]
        SharedKMZ[NFS / S3 Object Storage\nFail KMZ & Lampiran]
    end

    WebPortal --> LB
    MobileApp --> LB
    LB --> DBProxy
    DBProxy -->|INSERT / UPDATE / DELETE| PrimaryDB
    DBProxy -->|SELECT / Pelaporan / Dashboard| Replica1
    DBProxy -->|SELECT / Peta / API| Replica2
    PrimaryDB -.->|Replikasi Binlog Asinkron / Semisinkron| Replica1
    PrimaryDB -.->|Replikasi Binlog Asinkron / Semisinkron| Replica2
    WebPortal -.-> RedisCache
    MobileApp -.-> RedisCache
    WebPortal -.-> SharedKMZ
```

---

### Fasa 1: Topologi Read Replica
* **Kesesuaian:** Hanya apabila metrik menunjukkan beban baca menjadi bottleneck dan aplikasi boleh menerima replication lag.
* **Mekanisme:**
  - 1 Node Utama (**Primary/Writer**): Mengendalikan semua transaksi penulisan (`INSERT`, `UPDATE`, `DELETE`, `BEGIN/COMMIT`).
  - 1-3 Node Pengikut (**Read Replicas**): Mengendalikan beban pertanyaan data (`SELECT`), laporan PDF (`dompdf`), peta geodata (`maps.php`), dan paparan papan pemuka.
* **Routing:** ProxySQL/MariaDB MaxScale boleh membantu, tetapi rules perlu diuji dengan transaksi, session state dan aliran read-after-write. Ukur replication lag dan sediakan laluan baca dari primary untuk bacaan yang perlu konsisten.

---

### Fasa 2: Kluster HA (contohnya MariaDB Galera)
* **Kesesuaian:** Keperluan ketersediaan tinggi selepas pemulihan dan failover diuji. Reka bentuk kluster sahaja tidak menjamin SLA atau sifar kehilangan data.
* **Mekanisme:**
  - Replikasi segerak berasaskan pensijilan (*certification-based replication*).
  - Replikasi multi-primary boleh menambah konflik dan kos tulis; ia bukan pengganti read replicas untuk skala bacaan.
  - Failover memerlukan quorum, rangkaian dan prosedur pemulihan yang diuji.
* **Konfigurasi Penting:**
  - `binlog_format=ROW`
  - `default_storage_engine=InnoDB`
  - `innodb_autoinc_lock_mode=2`
  - `wsrep_on=ON`

---

### Fasa 3: Partisi atau sharding
* **Kesesuaian:** Hanya selepas metrik dan `EXPLAIN` pada dataset produksi menunjukkan satu jadual menjadi bottleneck.
* **Partisi tarikh:** Jangan jalankan contoh `PARTITION BY YEAR(date)` sebelum mereka semula primary/unique keys. MySQL/MariaDB mensyaratkan partition key termasuk dalam setiap unique key; `daily_records` kini dikenal pasti dengan PK `id` dan dirujuk oleh ID itu dalam kod.
* **Sharding:** Tentukan shard key dan strategi ID global daripada pola baca/tulis serta hubungan data dahulu. Tiada shard key GMS ditetapkan dalam pelan ini.

---

## 3. Garis Panduan Pembangunan Aplikasi Berskala Mendatar

1. **Elakkan Ketergantungan Sesi Setempat (Stateless Web Nodes):**
   - Portal web yang berskala mendatar (berbilang kontena Nginx/PHP-FPM) memerlukan sesi disimpan dalam Redis (`session.save_handler = redis`) atau pangkalan data berpusat.
2. **Pengurusan ID Unik dalam Persekitaran Berbilang Induk:**
   - Untuk kluster berbilang induk, tetapkan:
     - `auto_increment_increment = 3` (mengikut bilangan nod)
     - `auto_increment_offset = 1, 2, 3` bagi setiap nod, atau gunakan penjana UUIDv7 untuk transaksi baharu.
3. **Penyimpanan Fail Lampiran Bersama:**
   - Fail `.kmz` di folder `kmz/` dan gambar di `apps/attachment/` perlu disimpan pada storan terpusat (Shared Object Store seperti MinIO / AWS S3 atau NFS Mount) supaya semua nod web membaca fail yang sama.

---

## 4. Audit Setempat dan Sempadan Persediaan (25 Oktober 2026)

Audit baca sahaja pada DB pembangunan `smartcut` (MariaDB 10.11.2) mendapati 23 jadual menggunakan InnoDB dan `utf8mb4_unicode_ci`, semua mempunyai primary key serta `AUTO_INCREMENT`, dan indeks utama yang disenaraikan di atas telah wujud. Indeks lookup `contract.contract_number(100)` turut ditambah kerana medan asal bertipe `TEXT`. Migrator `--dry-run` mengesahkan tiada lagi perubahan enjin, kolasi atau indeks daripada skrip itu.

`daily_records.cid` bertipe `VARCHAR` sedangkan `contract.contract_id` bertipe integer. Query harian kini mengikat `cid` sebagai string, dan dashboard menukar nilai kontrak kepada `CHAR` ketika join; semakan `EXPLAIN` menunjukkan join zon beralih daripada imbasan berulang ke `idx_cid`. Semakan agregat sebelum/selepas menunjukkan bilangan rekod zon tidak berubah.

Ini ialah persediaan skema setempat, bukan bukti bahawa penskalaan mendatar telah dilaksanakan. Tiada node replika/proksi/kluster diuji, tiada beban produksi tersedia untuk benchmark, dan tiada DDL dijalankan pada pelayan produksi. Pelan Fasa 1 perlu menguji lag replica, routing transaksi, serta bacaan selepas tulis sebelum trafik dipindahkan.

Gunakan read replica dahulu untuk skala bacaan dan ketersediaan; Galera ialah pilihan HA dan tidak menjamin throughput tulis meningkat secara linear. Pilih shard key daripada corak akses dan keperluan pemilikan data sebelum sharding. Jangan aktifkan partisi tarikh berdasarkan ambang baris umum: MySQL/MariaDB memerlukan partition key berada dalam setiap unique key, jadi partitioning `daily_records` berdasarkan `date` akan memerlukan perubahan primary key dan semua rujukan kod.

Migrasi skema tidak memadam token peranti secara automatik. `db/migrations/001_perf_scaling.sql` memaparkan kiraan kumpulan token pendua sebagai preflight; hentikan migrasi dan buat pembaikan data yang diluluskan jika bilangannya bukan sifar.

## 5. Arahan Menjalankan Skrip Migrasi Pengoptimuman

Skrip pengoptimuman telah disediakan di `tools/db_optimize_migration.php`.

```bash
# Simulasi ialah mod lalai. Alat ini menyasar konfigurasi localhost sahaja.
php tools/db_optimize_migration.php --dry-run

# Untuk pelaksanaan setempat selepas backup yang boleh dipulihkan disediakan:
php tools/db_optimize_migration.php --apply --backup-file=D:/path/outside/webroot/backup.sql
```

Semakan integriti sistem boleh dijalankan pada bila-bila masa melalui:
```powershell
powershell -File tools/qa-smoke.ps1
```

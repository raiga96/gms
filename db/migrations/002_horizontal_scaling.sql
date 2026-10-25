-- 002_horizontal_scaling.sql
-- Fasa 2: Penyeragaman Penuh Collation (utf8mb4_unicode_ci) & Indeks Komposit untuk Penskalaan Mendatar
-- Sesuai untuk Replikasi Pembacaan (Read Replicas), Galera Cluster & Pengimbangan Beban (ProxySQL/MaxScale)

-- 1. Penyeragaman Collation ke utf8mb4_unicode_ci
ALTER TABLE access_level      CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE app_info          CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE area_daily_records CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE attendance        CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE contract          CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE contract_location CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE contractor        CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE contractor_users  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE daily_records     CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE dps               CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE holiday           CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE kmz_file          CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE notifications     CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE profile_image     CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE si_attachment     CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE site_instruction  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE staff_checkin     CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE subzone           CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE user_fcm_tokens   CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE user_log          CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE userinfo          CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE users             CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE zone              CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 2. Indeks Komposit & Medan Kerap Dicari
ALTER TABLE access_level      ADD UNIQUE KEY uq_acc_lvl (acc_lvl);
ALTER TABLE app_info          ADD KEY idx_app_status (app_status);
ALTER TABLE area_daily_records ADD KEY idx_adr_dr_area (dr_id, area_id), ADD KEY idx_adr_cid_date (cid, date);
ALTER TABLE attendance        ADD KEY idx_att_date (date);
ALTER TABLE contract          ADD KEY idx_contract_status_sz (c_status, sz_id), ADD KEY idx_contract_status_cid (c_status, cid);
ALTER TABLE contractor_users  ADD UNIQUE KEY uq_contractor_user (contractor_id, user_id);
ALTER TABLE notifications     ADD KEY idx_notif_uid_created (uid, created_at);
ALTER TABLE site_instruction  ADD KEY idx_si_contract_date (contract_id, date_issued);
ALTER TABLE staff_checkin     ADD KEY idx_sc_location (location_name);
ALTER TABLE user_log          ADD KEY idx_ul_date (log_date), ADD KEY idx_ul_uid_date (uid, log_date);
ALTER TABLE zone              ADD KEY idx_zone_name (zone_name);

-- 3. Kemas kini Statistik Pengoptimum
ANALYZE TABLE access_level, app_info, area_daily_records, attendance, contract, contract_location,
              contractor, contractor_users, daily_records, dps, holiday, kmz_file, notifications,
              profile_image, si_attachment, site_instruction, staff_checkin, subzone,
              user_fcm_tokens, user_log, userinfo, users, zone;

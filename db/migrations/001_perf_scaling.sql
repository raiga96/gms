-- 001_perf_scaling.sql : AUTO_INCREMENT hilang pada PK, user_fcm_tokens tanpa PK/unique,
-- user_log tanpa index, MyISAM -> InnoDB. Idempotent-ish: jalankan sekali selepas backup.
-- Skala mendatang: auto_increment_increment/offset per node (multi-primary) atau tukar ke ID
-- sisi-aplikasi (UUIDv7) bila perlu; semua jadual kini InnoDB (replikasi/Galera sedia).
ALTER TABLE access_level      MODIFY accid INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE app_info          MODIFY appId INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE area_daily_records MODIFY id INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE attendance        MODIFY record_id INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE contract          MODIFY contract_id INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE contractor        MODIFY cid INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE contractor_users  ENGINE=InnoDB;
ALTER TABLE contractor_users  MODIFY contractor_user_id INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE contract_location MODIFY locId INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE daily_records     MODIFY id INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE dps               MODIFY dem_id INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE holiday           MODIFY Hid INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE kmz_file          MODIFY id INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE notifications     MODIFY id INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE profile_image     MODIFY PFID INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE site_instruction  MODIFY si_id INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE si_attachment     MODIFY attc_id INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE staff_checkin     MODIFY id INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE subzone           MODIFY sz_id INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE user_log          MODIFY log_id INT(11) NOT NULL AUTO_INCREMENT,
                              ADD KEY idx_ul_uid_log (uid, log_id),
                              CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE kmz_file          CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
-- Preflight only: never delete device tokens automatically during a schema migration.
-- Continue only when this returns 0; resolve any duplicates through an approved data repair.
SELECT COUNT(*) AS duplicate_token_groups FROM (
  SELECT uid, token FROM user_fcm_tokens GROUP BY uid, token HAVING COUNT(*) > 1
) AS duplicate_tokens;
ALTER TABLE user_fcm_tokens ADD PRIMARY KEY (id), MODIFY id INT(11) NOT NULL AUTO_INCREMENT,
                            ADD UNIQUE KEY uq_fcm_uid_token (uid, token(255));
-- indeks redundan (awalan sama dengan indeks komposit di atas)
ALTER TABLE user_log DROP INDEX idx_ulog_uid;
ALTER TABLE user_fcm_tokens DROP INDEX idx_fcm_uid;
-- daily_records.cid ialah contract_id (semua numerik): INT supaya join ke contract.contract_id guna indeks
ALTER TABLE daily_records MODIFY cid INT(11) NOT NULL COMMENT 'Contract ID';

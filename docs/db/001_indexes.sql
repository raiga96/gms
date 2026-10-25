-- GMS: additive indexes only (no data/schema change). Safe to re-run: ignore error 1061 (duplicate key name).
ALTER TABLE site_instruction  ADD INDEX idx_si_num_si (num_si), ADD INDEX idx_si_ddid (ddId);
ALTER TABLE si_attachment     ADD INDEX idx_siatt_num_si (num_si);
ALTER TABLE contract_location ADD INDEX idx_cl_sz_id (sz_id);
ALTER TABLE area_daily_records ADD INDEX idx_adr_area_id (area_id);
ALTER TABLE userinfo          ADD INDEX idx_ui_uid (uid), ADD INDEX idx_ui_division (division);
ALTER TABLE attendance        ADD INDEX idx_att_uid_date (uid, date);
ALTER TABLE holiday           ADD INDEX idx_hol_date (holiday_date);
ALTER TABLE user_log          ADD INDEX idx_ulog_uid (uid);
ALTER TABLE user_fcm_tokens   ADD INDEX idx_fcm_uid (uid);
ALTER TABLE profile_image     ADD INDEX idx_pi_uid (UID);
ALTER TABLE kmz_file          ADD INDEX idx_kmz_sz_id (sz_id), ADD INDEX idx_kmz_zone_id (zone_id);
ALTER TABLE users             ADD INDEX idx_users_type_status (user_type, status);

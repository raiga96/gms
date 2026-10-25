-- GMS: Horizontal Scaling Readiness & Collation Standardization
-- Phase 2: Engine to InnoDB, Character Set to utf8mb4, Collation to utf8mb4_unicode_ci,
-- and High-Performance Composite Indexes for Distributed Querying / Read-Replicas.

-- 1. Standardize All Tables to InnoDB & utf8mb4_unicode_ci
ALTER TABLE `access_level` ENGINE = InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `app_info` ENGINE = InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `area_daily_records` ENGINE = InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `attendance` ENGINE = InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `contract` ENGINE = InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `contract_location` ENGINE = InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `contractor` ENGINE = InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `contractor_users` ENGINE = InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `daily_records` ENGINE = InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `dps` ENGINE = InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `holiday` ENGINE = InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `kmz_file` ENGINE = InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `notifications` ENGINE = InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `profile_image` ENGINE = InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `si_attachment` ENGINE = InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `site_instruction` ENGINE = InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `staff_checkin` ENGINE = InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `subzone` ENGINE = InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `user_fcm_tokens` ENGINE = InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `user_log` ENGINE = InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `userinfo` ENGINE = InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `users` ENGINE = InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `zone` ENGINE = InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 2. Composite & Sharding/Replica Indexes
ALTER TABLE `access_level` ADD UNIQUE KEY `uq_acc_lvl` (`acc_lvl`);
ALTER TABLE `app_info` ADD KEY `idx_app_status` (`app_status`);
ALTER TABLE `area_daily_records` ADD KEY `idx_adr_dr_area` (`dr_id`, `area_id`), ADD KEY `idx_adr_cid_date` (`cid`, `date`);
ALTER TABLE `attendance` ADD KEY `idx_att_date` (`date`);
ALTER TABLE `contract` ADD KEY `idx_contract_status_sz` (`c_status`, `sz_id`), ADD KEY `idx_contract_status_cid` (`c_status`, `cid`);
ALTER TABLE `contractor_users` ADD UNIQUE KEY `uq_contractor_user` (`contractor_id`, `user_id`);
ALTER TABLE `notifications` ADD KEY `idx_notif_uid_created` (`uid`, `created_at`);
ALTER TABLE `site_instruction` ADD KEY `idx_si_contract_date` (`contract_id`, `date_issued`);
ALTER TABLE `staff_checkin` ADD KEY `idx_sc_location` (`location_name`);
ALTER TABLE `user_log` ADD KEY `idx_ul_date` (`log_date`), ADD KEY `idx_ul_uid_date` (`uid`, `log_date`);
ALTER TABLE `zone` ADD KEY `idx_zone_name` (`zone_name`);

-- 3. Update Table Statistics
ANALYZE TABLE `access_level`, `app_info`, `area_daily_records`, `attendance`, `contract`, `contract_location`,
              `contractor`, `contractor_users`, `daily_records`, `dps`, `holiday`, `kmz_file`, `notifications`,
              `profile_image`, `si_attachment`, `site_instruction`, `staff_checkin`, `subzone`,
              `user_fcm_tokens`, `user_log`, `userinfo`, `users`, `zone`;

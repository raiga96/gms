-- 003_prune_redundant_indexes.sql
-- Pembersihan Indeks Lebihan (Redundant Prefix Indexes)
-- Indeks komposit yang wujud sudah meliputi carian awalan (leftmost prefix).
-- Menghapuskan indeks lebihan menjimatkan storan, mengurangkan I/O penulisan (INSERT/UPDATE),
-- dan meningkatkan prestasi replikasi dalam persekitaran horizontal scaling.

ALTER TABLE daily_records DROP INDEX idx_cid, DROP INDEX idx_dr_verified;
ALTER TABLE area_daily_records DROP INDEX idx_adr_dr_id, DROP INDEX idx_adr_cid;
ALTER TABLE contract DROP INDEX idx_contract_status;
ALTER TABLE notifications DROP INDEX idx_uid;

ANALYZE TABLE daily_records, area_daily_records, contract, notifications;

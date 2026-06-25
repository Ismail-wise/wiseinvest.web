-- ================================================================
-- WiseInvest — Admin Users Features Migration
-- Adds: created_at defensive backfill + upgrade audit log table
-- Safe to run multiple times (idempotent). Uses ONLY existing columns
-- for the users table (membership_type, premium_expiry, created_at).
-- NO new columns added to `users`. NO data destroyed.
-- ================================================================

-- ----------------------------------------------------------------
-- 1) DEFENSIVE BACKFILL of created_at
--    created_at already exists and is auto-populated for new signups.
--    This only touches rows that somehow have a NULL or zero date
--    (e.g. users created before the column existed). Harmless no-op
--    if every row already has a valid date.
-- ----------------------------------------------------------------
UPDATE users
SET created_at = '2025-01-01 00:00:00'
WHERE created_at IS NULL
   OR created_at = '0000-00-00 00:00:00';

-- ----------------------------------------------------------------
-- 2) UPGRADE AUDIT LOG
--    Records every bulk (and optionally single) upgrade for the
--    new admin/upgrade-history.php page.
--    previous_level / new_level store membership_type values.
--    expires_at mirrors users.premium_expiry (NULL = lifetime).
-- ----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS upgrade_log (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  user_id        INT NOT NULL,
  previous_level VARCHAR(50)  NULL,
  new_level      VARCHAR(50)  NOT NULL,
  expires_at     DATETIME     NULL,
  upgraded_by    INT          NOT NULL,
  notes          TEXT         NULL,
  created_at     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user  (user_id),
  INDEX idx_admin (upgraded_by),
  INDEX idx_date  (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

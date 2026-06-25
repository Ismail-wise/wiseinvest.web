-- ============================================================
-- Daily Expenses Tracker — Table Migration
-- WiseInvest | Emergency Fund feature extension
--
-- HOW TO RUN:
--   1. Log in to cPanel → phpMyAdmin
--   2. Select the database: wisestel_wiseinvest
--   3. Click the "SQL" tab
--   4. Paste this entire file and click "Go"
-- ============================================================

CREATE TABLE IF NOT EXISTS `daily_expenses` (
  `id`           INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `user_id`      INT            NOT NULL,
  `expense_date` DATE           NOT NULL,
  `category`     ENUM('food','transport','utilities','entertainment','other')
                                NOT NULL DEFAULT 'other',
  `amount`       DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
  `note`         TEXT           NULL     COMMENT 'Optional Burmese/English note',
  `created_at`   TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_user_month` (`user_id`, `expense_date`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

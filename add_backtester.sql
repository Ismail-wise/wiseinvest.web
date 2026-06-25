-- ============================================================
-- WiseInvest Portfolio Backtester — Database Migration
-- Run ONCE on your MySQL server (wisestel_wiseinvest)
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ── 1. Price Cache (Yahoo Finance monthly closes, 24-hour TTL) ──
CREATE TABLE IF NOT EXISTS `backtest_price_cache` (
  `id`         int(11)      NOT NULL AUTO_INCREMENT,
  `ticker`     varchar(20)  NOT NULL,
  `price_data` longtext     NOT NULL COMMENT 'JSON object {YYYY-MM: close_price}',
  `ticker_name` varchar(255) DEFAULT NULL COMMENT 'shortName from Yahoo meta',
  `cached_at`  datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_ticker` (`ticker`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 2. Saved Backtests ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `saved_backtests` (
  `id`           int(11)      NOT NULL AUTO_INCREMENT,
  `user_id`      int(11)      NOT NULL,
  `title`        varchar(255) NOT NULL DEFAULT 'Untitled Backtest',
  `config_json`  longtext     NOT NULL COMMENT 'Portfolios + params JSON',
  `results_json` longtext     NOT NULL COMMENT 'Full results JSON from backtest.php',
  `share_token`  char(32)     NOT NULL COMMENT 'MD5 for shareable link',
  `is_public`    tinyint(1)   NOT NULL DEFAULT 0,
  `created_at`   datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY  `idx_user`  (`user_id`),
  UNIQUE KEY `uk_token` (`share_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 3. User Portfolio Presets ──────────────────────────────────
CREATE TABLE IF NOT EXISTS `user_portfolios` (
  `id`               int(11)      NOT NULL AUTO_INCREMENT,
  `user_id`          int(11)      NOT NULL,
  `name`             varchar(100) NOT NULL,
  `color`            varchar(20)  NOT NULL DEFAULT '#d4af37',
  `allocations_json` text         NOT NULL COMMENT 'JSON [{ticker,weight,name}]',
  `rebalance`        enum('none','monthly','quarterly','yearly') NOT NULL DEFAULT 'yearly',
  `created_at`       datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 4. Myanmar Stock Exchange (YSX) Historical Data ───────────
CREATE TABLE IF NOT EXISTS `myanmar_stocks` (
  `id`           int(11)        NOT NULL AUTO_INCREMENT,
  `ticker`       varchar(20)    NOT NULL,
  `company_name` varchar(255)   DEFAULT NULL,
  `price_date`   date           NOT NULL,
  `close_price`  decimal(14,4)  NOT NULL,
  `volume`       bigint(20)     NOT NULL DEFAULT 0,
  `uploaded_by`  int(11)        DEFAULT NULL COMMENT 'admin user_id',
  `uploaded_at`  datetime       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_ticker_date` (`ticker`,`price_date`),
  KEY `idx_ticker`  (`ticker`),
  KEY `idx_date`    (`price_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

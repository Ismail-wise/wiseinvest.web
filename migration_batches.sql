-- ================================================================
-- Batch Membership Migration
-- Run this once on your database before deploying the PHP changes.
-- ================================================================

-- Step 1: Extend membership_type to support batch tiers
-- Existing 'premium' rows are kept as-is (treated as Diamond / all-access)
ALTER TABLE users
  MODIFY membership_type
    ENUM('free','premium','b1_premium','b2_premium','b3_premium','b4_premium','b5_premium','diamond')
    NOT NULL DEFAULT 'free';

-- Step 2: Add batch tag column to videos
-- NULL  = general premium content (all paid tiers can watch)
-- 'b1'  = Batch-1 only  (b1_premium + diamond)
-- 'b2'  = Batch-2 only  (b2_premium + diamond)
-- etc.
ALTER TABLE premium_content
  ADD COLUMN IF NOT EXISTS batch VARCHAR(10) NULL DEFAULT NULL;

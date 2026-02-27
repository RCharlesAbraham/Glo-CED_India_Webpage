-- =============================================================
-- Migration: 002_create_submissions_table.sql
-- Creates the submissions table for contact form entries.
-- =============================================================

CREATE TABLE IF NOT EXISTS `submissions` (
    `id`         INT          UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(120) NOT NULL,
    `email`      VARCHAR(180) NOT NULL,
    `message`    TEXT         NOT NULL,
    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- Migration: 001_create_users_table.sql
-- Creates the users table for authentication.
-- =============================================================

CREATE TABLE IF NOT EXISTS `users` (
    `id`         INT          UNSIGNED NOT NULL AUTO_INCREMENT,
    `username`   VARCHAR(80)  NOT NULL UNIQUE,
    `email`      VARCHAR(180) NOT NULL UNIQUE,
    `password`   VARCHAR(255) NOT NULL,
    `role`       ENUM('admin','user') NOT NULL DEFAULT 'user',
    `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

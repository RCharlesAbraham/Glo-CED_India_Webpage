-- =============================================================
-- Seeder: seed_admin_user.sql
-- Inserts a default admin user.
-- Password: admin123  (bcrypt hash — change immediately in production!)
-- =============================================================

INSERT INTO `users` (`username`, `email`, `password`, `role`) VALUES (
    'admin',
    'admin@glo-ced.in',
    '$2y$10$E9Q0hV/xnDZn.1pVa9yqEOF3CjdxqvdnVxLl4tGKQJ.8/HU8pHdEu',
    'admin'
) ON DUPLICATE KEY UPDATE `id` = `id`;

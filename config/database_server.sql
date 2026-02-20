CREATE TABLE IF NOT EXISTS `admins` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `username` VARCHAR(100) UNIQUE NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255),
    `full_name` VARCHAR(255),
    `is_active` BOOLEAN DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `last_login` DATETIME NULL,
    INDEX `idx_username` (`username`),
    INDEX `idx_is_active` (`is_active`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `contact_submissions` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `message` LONGTEXT NOT NULL,
    `ip_address` VARCHAR(45),
    `status` ENUM('new', 'read', 'replied', 'archived') DEFAULT 'new',
    `admin_notes` LONGTEXT,
    `submitted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `replied_at` DATETIME NULL,
    `replied_by` INT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_email` (`email`),
    INDEX `idx_status` (`status`),
    INDEX `idx_submitted_at` (`submitted_at`),
    INDEX `idx_created_at` (`created_at`),
    FOREIGN KEY (`replied_by`) REFERENCES `admins`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `programs` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) UNIQUE NOT NULL,
    `description` LONGTEXT NOT NULL,
    `short_description` VARCHAR(500),
    `icon_class` VARCHAR(50),
    `color_class` VARCHAR(50),
    `is_active` BOOLEAN DEFAULT 1,
    `display_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_slug` (`slug`),
    INDEX `idx_is_active` (`is_active`),
    INDEX `idx_display_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `team_members` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `position` VARCHAR(150) NOT NULL,
    `email` VARCHAR(255),
    `phone` VARCHAR(20),
    `bio` LONGTEXT,
    `image_url` VARCHAR(500),
    `is_active` BOOLEAN DEFAULT 1,
    `display_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_is_active` (`is_active`),
    INDEX `idx_display_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `gallery_items` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `description` VARCHAR(500),
    `image_url` VARCHAR(500) NOT NULL,
    `thumbnail_url` VARCHAR(500),
    `category` VARCHAR(100),
    `is_active` BOOLEAN DEFAULT 1,
    `display_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_category` (`category`),
    INDEX `idx_is_active` (`is_active`),
    INDEX `idx_display_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `blog_posts` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) UNIQUE NOT NULL,
    `content` LONGTEXT NOT NULL,
    `excerpt` VARCHAR(500),
    `author_id` INT,
    `featured_image_url` VARCHAR(500),
    `category` VARCHAR(100),
    `is_published` BOOLEAN DEFAULT 0,
    `published_at` DATETIME NULL,
    `view_count` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_slug` (`slug`),
    INDEX `idx_is_published` (`is_published`),
    INDEX `idx_category` (`category`),
    INDEX `idx_published_at` (`published_at`),
    INDEX `idx_created_at` (`created_at`),
    FOREIGN KEY (`author_id`) REFERENCES `admins`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `testimonials` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `author_name` VARCHAR(255) NOT NULL,
    `author_title` VARCHAR(150),
    `content` LONGTEXT NOT NULL,
    `rating` INT DEFAULT 5,
    `author_image_url` VARCHAR(500),
    `is_featured` BOOLEAN DEFAULT 0,
    `is_active` BOOLEAN DEFAULT 1,
    `display_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_is_active` (`is_active`),
    INDEX `idx_is_featured` (`is_featured`),
    INDEX `idx_display_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `email_logs` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `recipient_email` VARCHAR(255) NOT NULL,
    `subject` VARCHAR(500) NOT NULL,
    `message_preview` VARCHAR(500),
    `email_type` VARCHAR(50),
    `submission_id` INT,
    `sent_by` INT,
    `is_sent` BOOLEAN DEFAULT 1,
    `sent_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_recipient_email` (`recipient_email`),
    INDEX `idx_email_type` (`email_type`),
    INDEX `idx_sent_at` (`sent_at`),
    FOREIGN KEY (`submission_id`) REFERENCES `contact_submissions`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`sent_by`) REFERENCES `admins`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `system_settings` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `setting_key` VARCHAR(100) UNIQUE NOT NULL,
    `setting_value` LONGTEXT,
    `setting_type` VARCHAR(50),
    `description` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `activity_logs` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `admin_id` INT,
    `action` VARCHAR(100) NOT NULL,
    `entity_type` VARCHAR(50),
    `entity_id` INT,
    `description` VARCHAR(500),
    `old_values` JSON,
    `new_values` JSON,
    `ip_address` VARCHAR(45),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_admin_id` (`admin_id`),
    INDEX `idx_action` (`action`),
    INDEX `idx_entity_type` (`entity_type`),
    INDEX `idx_created_at` (`created_at`),
    FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `admins` (`username`, `password_hash`, `email`, `full_name`, `is_active`) 
VALUES ('admin', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36P4/eS2', 'admin@glocedindia.org', 'System Administrator', 1)
ON DUPLICATE KEY UPDATE `is_active` = 1;

INSERT INTO `programs` (`title`, `slug`, `description`, `short_description`, `icon_class`, `color_class`, `is_active`, `display_order`) VALUES
('Education & Capacity Building', 'education-capacity', 'Comprehensive inclusive education programs and ICT-enabled skill development for grassroots community empowerment.', 'Education programs and skill development', 'fas fa-book', 'text-blue-700', 1, 1),
('Research & Policy Bridge', 'research-policy', 'Evidence-based knowledge generation with strategic links between research and policy for actionable solutions.', 'Research and policy integration', 'fas fa-chart-line', 'text-green-700', 1, 2),
('Health & Livelihood', 'health-livelihood', 'Health program implementation and livelihood enhancement initiatives for community resilience building.', 'Health and livelihood programs', 'fas fa-hands-helping', 'text-amber-700', 1, 3),
('Sustainable Development', 'sustainable-development', 'Inclusive development pathways promoting environmental responsibility and long-term social progress.', 'Environmental and development initiatives', 'fas fa-leaf', 'text-purple-700', 1, 4)
ON DUPLICATE KEY UPDATE `is_active` = VALUES(`is_active`);

INSERT INTO `testimonials` (`author_name`, `author_title`, `content`, `rating`, `is_featured`, `is_active`, `display_order`) VALUES
('Ramesh Kumar', 'Community Leader', 'Glo-CED India has transformed our community. Their education programs have changed the lives of hundreds of children.', 5, 1, 1, 1),
('Priya Sharma', 'Organization Director', 'An excellent organization dedicated to real change. Their holistic approach to development is truly commendable.', 5, 1, 1, 2),
('Dr. Anil Patel', 'Healthcare Professional', 'Their health initiatives have made a significant impact in our rural areas. Highly recommended!', 5, 0, 1, 3)
ON DUPLICATE KEY UPDATE `is_active` = VALUES(`is_active`);

INSERT INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('organization_name', 'Glo-CED India', 'text', 'Official name of the organization'),
('organization_email', 'info@glocedindia.org', 'email', 'Primary contact email'),
('organization_phone', '+91 (0) 123 456 7890', 'phone', 'Primary contact phone number'),
('organization_address', '123 Charity Street, City, State 12345, India', 'text', 'Physical address'),
('website_title', 'Glo-CED India | Character Building & Ethical Leadership', 'text', 'Website title'),
('website_description', 'Building character and driving social transformation through education, research, health, and sustainable development initiatives.', 'text', 'Website meta description'),
('enable_contact_form', '1', 'boolean', 'Enable/disable contact form submissions'),
('admin_notification_email', 'admin@glocedindia.org', 'email', 'Email address to receive admin notifications'),
('copyright_year', '2026', 'text', 'Copyright year for footer')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);

-- Charity Trust Database Schema
-- Create this database and tables to store contact form submissions and manage them

-- Create Database (if not exists)
CREATE DATABASE IF NOT EXISTS charity_trust;
USE charity_trust;

-- ============================================
-- CONTACT SUBMISSIONS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS contact_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    message LONGTEXT NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    status ENUM('new', 'read', 'replied', 'archived') DEFAULT 'new',
    admin_notes LONGTEXT,
    replied_at DATETIME,
    
    -- Indexes for better performance
    INDEX idx_email (email),
    INDEX idx_submitted_at (submitted_at),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- ADMIN USERS TABLE (for future admin functionality)
-- ============================================
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    full_name VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME,
    
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DONORS TABLE (for tracking donations)
-- ============================================
CREATE TABLE IF NOT EXISTS donors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    amount DECIMAL(10, 2),
    donation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    message TEXT,
    is_anonymous BOOLEAN DEFAULT FALSE,
    
    INDEX idx_donation_date (donation_date),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- NEWSLETTER SUBSCRIBERS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    
    INDEX idx_email (email),
    INDEX idx_subscribed_at (subscribed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SETTINGS TABLE (for global configuration)
-- ============================================
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value LONGTEXT,
    setting_type VARCHAR(50),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- INSERT DEFAULT SETTINGS
-- ============================================
INSERT INTO settings (setting_key, setting_value, setting_type) VALUES
('organization_name', 'Charity Trust', 'string'),
('organization_email', 'info@charitytrust.com', 'string'),
('organization_phone', '+91 (0) 123 456 7890', 'string'),
('contact_form_enabled', '1', 'boolean'),
('send_confirmation_email', '1', 'boolean'),
('admin_notification_email', 'admin@charitytrust.com', 'string')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- ============================================
-- CREATE INDEXES FOR BETTER QUERY PERFORMANCE
-- ============================================
ALTER TABLE contact_submissions ADD FULLTEXT INDEX ft_search (name, email, message);
ALTER TABLE donors ADD INDEX idx_anonymous (is_anonymous);
ALTER TABLE newsletter_subscribers ADD INDEX idx_active (is_active);

-- ============================================
-- SAMPLE DATA (Optional - for testing)
-- ============================================
-- Uncomment below to add sample data for testing

-- INSERT INTO contact_submissions (name, email, phone, message, ip_address) VALUES
-- ('John Doe', 'john@example.com', '+91 9876543210', 'I want to volunteer', '192.168.1.1'),
-- ('Jane Smith', 'jane@example.com', '+91 9876543211', 'How can I donate?', '192.168.1.2');

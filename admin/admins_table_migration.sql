-- Create admins table for managing admin user accounts
CREATE TABLE IF NOT EXISTS admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT 1
);

-- Insert default admin user (password: admin123)
-- To change this password, run: SELECT PASSWORD('your_new_password') in MySQL
-- Then replace the hash value below
INSERT INTO admins (username, password_hash, email, is_active) VALUES
('admin', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36P4/eS2', 'admin@charity.local', 1)
ON DUPLICATE KEY UPDATE is_active=1;

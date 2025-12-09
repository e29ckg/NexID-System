-- db/init.sql

SET NAMES utf8mb4;

-- 1. สร้างตาราง Users (ถ้ายังไม่มี)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. สร้างตาราง User Profiles
CREATE TABLE IF NOT EXISTS user_profiles (
    user_id INT PRIMARY KEY,
    first_name VARCHAR(100) DEFAULT '',
    last_name VARCHAR(100) DEFAULT '',
    phone VARCHAR(20) DEFAULT '',
    address TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 3. (Optional) เพิ่ม Admin ตั้งต้นให้เลย
-- Username: admin
-- Password: password123 (Hash นี้คือคำว่า password123)
INSERT INTO users (username, password_hash, role) 
VALUES ('admin', '$2y$10$5sL6H.W/1t.W5J/..examplerealhashneeded..', 'admin')
ON DUPLICATE KEY UPDATE username=username; 
-- หมายเหตุ: Hash ด้านบนเป็นตัวอย่าง คุณควร Register ผ่านหน้าเว็บเอาชัวร์กว่าครับ
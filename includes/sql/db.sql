CREATE DATABASE IF NOT EXISTS ims566_db;
USE ims566_db;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'student',
    full_name VARCHAR(255) DEFAULT NULL,
    student_id VARCHAR(20) DEFAULT NULL,
    ic_number VARCHAR(20) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    email VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS withdrawals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    name VARCHAR(255) NOT NULL,
    student_id VARCHAR(20) NOT NULL,
    ic_number VARCHAR(20) NOT NULL,
    program_code VARCHAR(10) NOT NULL,
    semester INT NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    reason VARCHAR(255) NOT NULL,
    other_reason TEXT,
    address TEXT NOT NULL,
    form_type VARCHAR(100) DEFAULT NULL,
    status VARCHAR(20) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    document_path VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS withdrawal_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    withdrawal_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    category VARCHAR(100) DEFAULT 'General',
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (withdrawal_id) REFERENCES withdrawals(id) ON DELETE CASCADE
);

-- Default Admin User (password: admin123)
-- In production, passwords should be hashed using password_hash()
INSERT IGNORE INTO users (username, password, role) VALUES ('admin', '$2y$10$1xaVU2l7xfWsdGoYYowWTedIqZWmmgXbBgTqm41C76clvGVILt3zm', 'admin');

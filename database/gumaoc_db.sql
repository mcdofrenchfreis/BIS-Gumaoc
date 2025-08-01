-- Create database if not exists
CREATE DATABASE IF NOT EXISTS gumaoc_db;
USE gumaoc_db;

-- Admin users table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('super_admin', 'admin') NOT NULL DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Resident registrations table (Census data)
CREATE TABLE IF NOT EXISTS resident_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100),
    last_name VARCHAR(100) NOT NULL,
    birth_date DATE NOT NULL,
    age INT NOT NULL,
    civil_status VARCHAR(50) NOT NULL,
    gender VARCHAR(20) NOT NULL,
    contact_number VARCHAR(20),
    house_number VARCHAR(20),
    pangkabuhayan VARCHAR(100),
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending'
);

-- Certificate requests table
CREATE TABLE IF NOT EXISTS certificate_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    address VARCHAR(500) NOT NULL,
    mobile_number VARCHAR(20),
    civil_status VARCHAR(50),
    gender VARCHAR(20),
    birth_date DATE NOT NULL,
    birth_place VARCHAR(255) NOT NULL,
    citizenship VARCHAR(100),
    years_of_residence INT,
    certificate_type VARCHAR(100) NOT NULL,
    purpose TEXT NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'processing', 'ready', 'released') DEFAULT 'pending'
);

-- Business applications table
CREATE TABLE IF NOT EXISTS business_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    business_name VARCHAR(255) NOT NULL,
    business_type VARCHAR(100) NOT NULL,
    business_address VARCHAR(500) NOT NULL,
    owner_name VARCHAR(255) NOT NULL,
    owner_address VARCHAR(500) NOT NULL,
    contact_number VARCHAR(20) NOT NULL,
    years_operation INT NOT NULL,
    investment_capital DECIMAL(15,2) NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'reviewing', 'approved', 'rejected') DEFAULT 'pending'
);

-- Insert default super admin user (password: admin123)
INSERT IGNORE INTO admin_users (username, password, full_name, email, role)
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin@gumaoc.local', 'super_admin'); 
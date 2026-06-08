-- Construction Management System Database Schema
-- MySQL Version: 5.7+

CREATE DATABASE IF NOT EXISTS construction_management_db;
USE construction_management_db;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'manager', 'employee') DEFAULT 'employee',
    status ENUM('active', 'inactive') DEFAULT 'active',
    profile_photo VARCHAR(255),
    phone VARCHAR(20),
    address TEXT,
    last_login DATETIME,
    password_reset_token VARCHAR(255),
    password_reset_expires DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Employees Table
CREATE TABLE IF NOT EXISTS employees (
    id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    position VARCHAR(50) NOT NULL,
    site_id INT,
    hire_date DATE NOT NULL,
    day_salary DECIMAL(10, 2) NOT NULL,
    employment_type ENUM('full_time', 'part_time', 'contract') DEFAULT 'full_time',
    status ENUM('active', 'inactive', 'on_leave') DEFAULT 'active',
    profile_photo VARCHAR(255),
    emergency_contact VARCHAR(100),
    emergency_phone VARCHAR(20),
    address TEXT,
    cnic VARCHAR(20),
    bank_account VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE SET NULL,
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_site_id (site_id),
    INDEX idx_hire_date (hire_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sites Table
CREATE TABLE IF NOT EXISTS sites (
    id INT PRIMARY KEY AUTO_INCREMENT,
    site_name VARCHAR(100) NOT NULL,
    site_address TEXT NOT NULL,
    gps_location VARCHAR(100),
    google_map_link TEXT,
    site_manager_id INT,
    site_status ENUM('active', 'inactive', 'completed') DEFAULT 'active',
    start_date DATE,
    end_date DATE,
    budget DECIMAL(15, 2),
    spent DECIMAL(15, 2) DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (site_manager_id) REFERENCES employees(id) ON DELETE SET NULL,
    INDEX idx_status (site_status),
    INDEX idx_start_date (start_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Attendance Table
CREATE TABLE IF NOT EXISTS attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    status ENUM('present', 'absent', 'half_day', 'leave') DEFAULT 'absent',
    site_id INT,
    check_in TIME,
    check_out TIME,
    working_hours DECIMAL(5, 2),
    overtime_hours DECIMAL(5, 2) DEFAULT 0,
    notes TEXT,
    marked_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE SET NULL,
    FOREIGN KEY (marked_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_attendance (employee_id, attendance_date),
    INDEX idx_date (attendance_date),
    INDEX idx_status (status),
    INDEX idx_employee_id (employee_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payroll Table
CREATE TABLE IF NOT EXISTS payroll (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    days_worked INT,
    day_salary DECIMAL(10, 2),
    basic_salary DECIMAL(15, 2),
    overtime_hours DECIMAL(5, 2) DEFAULT 0,
    overtime_rate DECIMAL(10, 2) DEFAULT 0,
    overtime_pay DECIMAL(15, 2) DEFAULT 0,
    allowances DECIMAL(15, 2) DEFAULT 0,
    deductions DECIMAL(15, 2) DEFAULT 0,
    net_salary DECIMAL(15, 2),
    payroll_status ENUM('pending', 'approved', 'paid') DEFAULT 'pending',
    payment_date DATE,
    payment_method ENUM('cash', 'bank_transfer', 'check') DEFAULT 'bank_transfer',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    INDEX idx_period (period_start, period_end),
    INDEX idx_status (payroll_status),
    INDEX idx_employee_id (employee_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inventory Table
CREATE TABLE IF NOT EXISTS inventory (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_name VARCHAR(100) NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    unit_price DECIMAL(10, 2),
    employee_id INT,
    site_id INT,
    date_received DATE,
    expiry_date DATE,
    supplier VARCHAR(100),
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE SET NULL,
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE SET NULL,
    INDEX idx_site_id (site_id),
    INDEX idx_product_name (product_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Files Table
CREATE TABLE IF NOT EXISTS files (
    id INT PRIMARY KEY AUTO_INCREMENT,
    site_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    file_path VARCHAR(255) NOT NULL,
    uploaded_by INT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_site_id (site_id),
    INDEX idx_file_type (file_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activities Log Table
CREATE TABLE IF NOT EXISTS activities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    module VARCHAR(50),
    record_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_module (module),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Sample Data
INSERT INTO users (username, email, password, role, status) VALUES
('admin', 'admin@cms.com', '$2y$12$abcdefghijklmnopqrstuvwxyz', 'admin', 'active'),
('manager', 'manager@cms.com', '$2y$12$abcdefghijklmnopqrstuvwxyz', 'manager', 'active'),
('employee', 'employee@cms.com', '$2y$12$abcdefghijklmnopqrstuvwxyz', 'employee', 'active');

INSERT INTO sites (site_name, site_address, gps_location, site_status, start_date) VALUES
('Site A - Downtown Plaza', '123 Main Street', '31.5204,74.3587', 'active', '2024-01-01'),
('Site B - Residential Complex', '456 Oak Avenue', '31.5205,74.3588', 'active', '2024-02-01'),
('Site C - Commercial Center', '789 Pine Road', '31.5206,74.3589', 'inactive', '2024-03-01');

INSERT INTO employees (full_name, email, phone, position, site_id, hire_date, day_salary, employment_type, status) VALUES
('Ahmed Ali Khan', 'ahmed@example.com', '03001234567', 'Site Manager', 1, '2023-01-15', 2500, 'full_time', 'active'),
('Fatima Hassan', 'fatima@example.com', '03007654321', 'Engineer', 1, '2023-02-20', 2000, 'full_time', 'active'),
('Muhammad Hassan', 'hassan@example.com', '03009876543', 'Worker', 2, '2023-03-10', 1000, 'full_time', 'active'),
('Ayesha Ahmad', 'ayesha@example.com', '03005555555', 'Supervisor', 2, '2023-04-05', 1800, 'full_time', 'active'),
('Ali Raza', 'ali@example.com', '03008888888', 'Worker', 1, '2023-05-12', 1000, 'contract', 'active');

<?php
/**
 * Database Configuration
 * 
 * Update these settings according to your local setup
 */

// Database Connection Details
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'construction_management_db');
define('DB_PORT', 3306);

// Application Settings
define('APP_NAME', 'Construction Management System');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/ML-Construction-Management-System');

// Security Settings
define('ENCRYPTION_KEY', 'your-secret-encryption-key-here');
define('SESSION_TIMEOUT', 1800); // 30 minutes

// File Upload Settings
define('UPLOAD_DIR', __DIR__ . '/../public/uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);
define('ALLOWED_DOC_TYPES', ['pdf', 'doc', 'docx', 'xls', 'xlsx']);
define('ALLOWED_VIDEO_TYPES', ['mp4', 'avi', 'mov', 'mkv']);

// Email Settings (Optional for password recovery)
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USER', 'your-email@gmail.com');
define('MAIL_PASS', 'your-app-password');
define('MAIL_FROM', 'noreply@constructionmgt.com');

// Pagination Settings
define('ITEMS_PER_PAGE', 10);

// Date Format
define('DATE_FORMAT', 'Y-m-d');
define('TIME_FORMAT', 'H:i:s');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');

// User Roles
define('ROLE_ADMIN', 'admin');
define('ROLE_MANAGER', 'manager');
define('ROLE_EMPLOYEE', 'employee');

// Attendance Status
define('ATTENDANCE_PRESENT', 'present');
define('ATTENDANCE_ABSENT', 'absent');
define('ATTENDANCE_HALF_DAY', 'half_day');
define('ATTENDANCE_LEAVE', 'leave');

// Employee Status
define('EMP_STATUS_ACTIVE', 'active');
define('EMP_STATUS_INACTIVE', 'inactive');
define('EMP_STATUS_ON_LEAVE', 'on_leave');

// Site Status
define('SITE_STATUS_ACTIVE', 'active');
define('SITE_STATUS_INACTIVE', 'inactive');
define('SITE_STATUS_COMPLETED', 'completed');

// Payroll Status
define('PAYROLL_STATUS_PENDING', 'pending');
define('PAYROLL_STATUS_APPROVED', 'approved');
define('PAYROLL_STATUS_PAID', 'paid');

?>

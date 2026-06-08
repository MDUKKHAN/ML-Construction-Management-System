<?php
/**
 * Helper Class
 * 
 * Utility functions for the application
 */

class Helper {
    /**
     * Format date
     */
    public static function formatDate($date, $format = DATE_FORMAT) {
        return date($format, strtotime($date));
    }

    /**
     * Format datetime
     */
    public static function formatDateTime($datetime, $format = DATETIME_FORMAT) {
        return date($format, strtotime($datetime));
    }

    /**
     * Format time
     */
    public static function formatTime($time, $format = TIME_FORMAT) {
        return date($format, strtotime($time));
    }

    /**
     * Calculate working hours
     */
    public static function calculateWorkingHours($checkIn, $checkOut) {
        if (empty($checkIn) || empty($checkOut)) {
            return 0;
        }

        $start = new DateTime($checkIn);
        $end = new DateTime($checkOut);
        $diff = $start->diff($end);

        return $diff->h + ($diff->i / 60);
    }

    /**
     * Calculate overtime hours
     */
    public static function calculateOvertimeHours($workingHours, $normalHours = 8) {
        return max(0, $workingHours - $normalHours);
    }

    /**
     * Format currency
     */
    public static function formatCurrency($amount, $currency = 'PKR') {
        return $currency . ' ' . number_format($amount, 2);
    }

    /**
     * Format percentage
     */
    public static function formatPercentage($value, $total) {
        return $total > 0 ? round(($value / $total) * 100, 2) : 0;
    }

    /**
     * Get attendance status badge
     */
    public static function getAttendanceBadge($status) {
        $badges = [
            'present' => '<span class="badge bg-success">Present</span>',
            'absent' => '<span class="badge bg-danger">Absent</span>',
            'half_day' => '<span class="badge bg-warning">Half Day</span>',
            'leave' => '<span class="badge bg-info">Leave</span>',
        ];

        return $badges[$status] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    /**
     * Get employee status badge
     */
    public static function getEmployeeStatusBadge($status) {
        $badges = [
            'active' => '<span class="badge bg-success">Active</span>',
            'inactive' => '<span class="badge bg-danger">Inactive</span>',
            'on_leave' => '<span class="badge bg-warning">On Leave</span>',
        ];

        return $badges[$status] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    /**
     * Get site status badge
     */
    public static function getSiteStatusBadge($status) {
        $badges = [
            'active' => '<span class="badge bg-success">Active</span>',
            'inactive' => '<span class="badge bg-danger">Inactive</span>',
            'completed' => '<span class="badge bg-info">Completed</span>',
        ];

        return $badges[$status] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    /**
     * Get payroll status badge
     */
    public static function getPayrollStatusBadge($status) {
        $badges = [
            'pending' => '<span class="badge bg-warning">Pending</span>',
            'approved' => '<span class="badge bg-info">Approved</span>',
            'paid' => '<span class="badge bg-success">Paid</span>',
        ];

        return $badges[$status] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    /**
     * Truncate text
     */
    public static function truncate($text, $length = 50, $suffix = '...') {
        if (strlen($text) <= $length) {
            return $text;
        }

        return substr($text, 0, $length) . $suffix;
    }

    /**
     * Get file size in human readable format
     */
    public static function formatFileSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Generate random string
     */
    public static function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $string = '';

        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $string;
    }

    /**
     * Upload file
     */
    public static function uploadFile($file, $uploadDir = null) {
        if ($uploadDir === null) {
            $uploadDir = UPLOAD_DIR;
        }

        if (!isset($file['name']) || empty($file['name'])) {
            return ['success' => false, 'message' => 'No file selected'];
        }

        $fileName = $file['name'];
        $fileTmp = $file['tmp_name'];
        $fileError = $file['error'];
        $fileSize = $file['size'];

        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Validate file
        if ($fileError !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'File upload error'];
        }

        if ($fileSize > MAX_FILE_SIZE) {
            return ['success' => false, 'message' => 'File size exceeds limit'];
        }

        // Check file type
        $allowedTypes = array_merge(ALLOWED_IMAGE_TYPES, ALLOWED_DOC_TYPES, ALLOWED_VIDEO_TYPES);
        if (!in_array($fileExt, $allowedTypes)) {
            return ['success' => false, 'message' => 'File type not allowed'];
        }

        // Generate unique filename
        $newFileName = time() . '_' . self::generateRandomString(8) . '.' . $fileExt;
        $uploadPath = $uploadDir . $newFileName;

        if (move_uploaded_file($fileTmp, $uploadPath)) {
            return ['success' => true, 'filename' => $newFileName, 'path' => $uploadPath];
        }

        return ['success' => false, 'message' => 'Failed to upload file'];
    }

    /**
     * Delete file
     */
    public static function deleteFile($filePath) {
        if (file_exists($filePath) && is_file($filePath)) {
            return unlink($filePath);
        }

        return false;
    }

    /**
     * Get days in month
     */
    public static function getDaysInMonth($month, $year) {
        return cal_days_in_month(CAL_GREGORIAN, $month, $year);
    }

    /**
     * Get date range
     */
    public static function getDateRange($startDate, $endDate) {
        $dates = [];
        $current = new DateTime($startDate);
        $end = new DateTime($endDate);

        while ($current <= $end) {
            $dates[] = $current->format(DATE_FORMAT);
            $current->modify('+1 day');
        }

        return $dates;
    }
}

?>

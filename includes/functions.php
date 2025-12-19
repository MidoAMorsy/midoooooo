<?php
/**
 * Ahmed Ashraf Portfolio - Common Functions
 */

/**
 * Sanitize input string
 */
function sanitize($input)
{
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Escape output for HTML
 */
function e($string)
{
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF token
 */
function generateCSRFToken()
{
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token)
{
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Generate CSRF input field
 */
function csrfField()
{
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . generateCSRFToken() . '">';
}

/**
 * Get CSRF token value
 */
function getCSRFToken()
{
    return generateCSRFToken();
}

/**
 * Get current URL
 */
function getCurrentUrl()
{
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Redirect to URL
 */
function redirect($url, $statusCode = 302)
{
    session_write_close();
    header("Location: $url", true, $statusCode);
    exit;
}

/**
 * Get site setting
 */
function getSetting($key, $default = null)
{
    static $settings = null;

    if ($settings === null) {
        try {
            $stmt = db()->query("SELECT setting_key, setting_value FROM site_settings");
            $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (PDOException $e) {
            return $default;
        }
    }

    return $settings[$key] ?? $default;
}

/**
 * Update site setting
 */
function updateSetting($key, $value)
{
    try {
        $stmt = db()->prepare("INSERT INTO site_settings (setting_key, setting_value) 
                               VALUES (?, ?) 
                               ON DUPLICATE KEY UPDATE setting_value = ?");
        return $stmt->execute([$key, $value, $value]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Get translated field value
 */
function trans($row, $field)
{
    $lang = getCurrentLanguage();
    $langField = $field . '_' . $lang;
    $fallbackField = $field . '_en';

    if (isset($row[$langField]) && !empty($row[$langField])) {
        return $row[$langField];
    }

    return $row[$fallbackField] ?? $row[$field] ?? '';
}

/**
 * Get language-specific label
 */
function __($key)
{
    static $translations = null;

    if ($translations === null) {
        $lang = getCurrentLanguage();
        $filePath = INCLUDES_PATH . "lang/{$lang}.php";

        if (file_exists($filePath)) {
            $translations = include $filePath;
        } else {
            $translations = [];
        }
    }

    return $translations[$key] ?? $key;
}

/**
 * Create URL slug
 */
function createSlug($string, $separator = '-')
{
    // Convert to ASCII
    $string = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $string);

    // Remove non-alphanumeric characters
    $string = preg_replace('/[^a-z0-9\s-]/', '', strtolower($string));

    // Replace spaces and multiple dashes with single separator
    $string = preg_replace('/[\s-]+/', $separator, $string);

    return trim($string, $separator);
}

/**
 * Format date for display
 */
function formatDate($date, $format = null)
{
    if (empty($date))
        return '';

    $timestamp = is_numeric($date) ? $date : strtotime($date);

    if ($format === null) {
        $format = getCurrentLanguage() === 'ar' ? 'd/m/Y' : 'M d, Y';
    }

    return date($format, $timestamp);
}

/**
 * Format datetime for display
 */
function formatDateTime($datetime, $format = null)
{
    if (empty($datetime))
        return '';

    $timestamp = is_numeric($datetime) ? $datetime : strtotime($datetime);

    if ($format === null) {
        $format = getCurrentLanguage() === 'ar' ? 'd/m/Y H:i' : 'M d, Y g:i A';
    }

    return date($format, $timestamp);
}

/**
 * Time ago format
 */
function timeAgo($datetime)
{
    $time = is_numeric($datetime) ? $datetime : strtotime($datetime);
    $diff = time() - $time;

    $intervals = [
        31536000 => ['year', 'سنة', 'سنوات'],
        2592000 => ['month', 'شهر', 'أشهر'],
        604800 => ['week', 'أسبوع', 'أسابيع'],
        86400 => ['day', 'يوم', 'أيام'],
        3600 => ['hour', 'ساعة', 'ساعات'],
        60 => ['minute', 'دقيقة', 'دقائق'],
    ];

    $lang = getCurrentLanguage();

    foreach ($intervals as $seconds => $names) {
        $count = floor($diff / $seconds);
        if ($count >= 1) {
            if ($lang === 'ar') {
                $unit = $count == 1 ? $names[1] : $names[2];
                return "منذ $count $unit";
            } else {
                $unit = $count == 1 ? $names[0] : $names[0] . 's';
                return "$count $unit ago";
            }
        }
    }

    return $lang === 'ar' ? 'الآن' : 'Just now';
}

/**
 * Truncate text
 */
function truncate($text, $length = 100, $suffix = '...')
{
    $text = strip_tags($text);
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . $suffix;
}

/**
 * Format file size
 */
function formatFileSize($bytes)
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}

/**
 * Get file extension
 */
function getFileExtension($filename)
{
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Check if file type is allowed
 */
function isAllowedFileType($filename, $type = 'image')
{
    $ext = getFileExtension($filename);

    switch ($type) {
        case 'image':
            return in_array($ext, ALLOWED_IMAGE_TYPES);
        case 'document':
            return in_array($ext, ALLOWED_DOC_TYPES);
        case 'video':
            return in_array($ext, ALLOWED_VIDEO_TYPES);
        default:
            return in_array($ext, array_merge(ALLOWED_IMAGE_TYPES, ALLOWED_DOC_TYPES, ALLOWED_VIDEO_TYPES));
    }
}

/**
 * Upload file
 */
function uploadFile($file, $directory = 'general', $allowedTypes = 'image')
{
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return ['success' => false, 'error' => 'No file uploaded'];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Upload error: ' . $file['error']];
    }

    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return ['success' => false, 'error' => 'File too large'];
    }

    if (!isAllowedFileType($file['name'], $allowedTypes)) {
        return ['success' => false, 'error' => 'File type not allowed'];
    }

    $uploadDir = UPLOADS_PATH . $directory . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $ext = getFileExtension($file['name']);
    $filename = uniqid() . '_' . time() . '.' . $ext;
    $filepath = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $directory . '/' . $filename,
            'fullpath' => $filepath,
            'url' => UPLOADS_URL . '/' . $directory . '/' . $filename
        ];
    }

    return ['success' => false, 'error' => 'Failed to move uploaded file'];
}

/**
 * Delete file
 */
function deleteFile($filepath)
{
    $fullPath = UPLOADS_PATH . $filepath;
    if (file_exists($fullPath)) {
        return unlink($fullPath);
    }
    return false;
}

/**
 * Generate random string
 */
function generateRandomString($length = 16)
{
    return bin2hex(random_bytes($length / 2));
}

/**
 * Get client IP address
 */
function getClientIP()
{
    $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];

    foreach ($ipKeys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = explode(',', $_SERVER[$key])[0];
            $ip = trim($ip);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }

    return '0.0.0.0';
}

/**
 * Send JSON response
 */
function jsonResponse($data, $statusCode = 200)
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Set flash message
 */
function setFlash($type, $message)
{
    $_SESSION['flash'][$type] = $message;
}

/**
 * Get flash message
 */
function getFlash($type)
{
    if (isset($_SESSION['flash'][$type])) {
        $message = $_SESSION['flash'][$type];
        unset($_SESSION['flash'][$type]);
        return $message;
    }
    return null;
}

/**
 * Display flash messages
 */
function displayFlashMessages()
{
    $types = ['success', 'error', 'warning', 'info'];
    $html = '';

    foreach ($types as $type) {
        $message = getFlash($type);
        if ($message) {
            $icon = match ($type) {
                'success' => 'check-circle',
                'error' => 'exclamation-circle',
                'warning' => 'exclamation-triangle',
                'info' => 'info-circle',
                default => 'info-circle'
            };
            $html .= "<div class='alert alert-{$type}'><i class='fas fa-{$icon}'></i> {$message}</div>";
        }
    }

    return $html;
}

/**
 * Pagination helper
 */
function paginate($total, $perPage = 10, $currentPage = 1)
{
    $totalPages = ceil($total / $perPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;

    return [
        'total' => $total,
        'per_page' => $perPage,
        'current_page' => $currentPage,
        'total_pages' => $totalPages,
        'offset' => $offset,
        'has_previous' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages,
        'previous_page' => max(1, $currentPage - 1),
        'next_page' => min($totalPages, $currentPage + 1)
    ];
}

/**
 * Generate pagination HTML
 */
function paginationHTML($pagination, $baseUrl = '?')
{
    if ($pagination['total_pages'] <= 1)
        return '';

    $lang = getCurrentLanguage();
    $prev = $lang === 'ar' ? 'السابق' : 'Previous';
    $next = $lang === 'ar' ? 'التالي' : 'Next';

    $html = '<div class="pagination">';

    // Previous button
    if ($pagination['has_previous']) {
        $html .= '<a href="' . $baseUrl . 'page=' . $pagination['previous_page'] . '" class="pagination-btn">&laquo; ' . $prev . '</a>';
    }

    // Page numbers
    $start = max(1, $pagination['current_page'] - 2);
    $end = min($pagination['total_pages'], $pagination['current_page'] + 2);

    if ($start > 1) {
        $html .= '<a href="' . $baseUrl . 'page=1" class="pagination-num">1</a>';
        if ($start > 2) {
            $html .= '<span class="pagination-dots">...</span>';
        }
    }

    for ($i = $start; $i <= $end; $i++) {
        $active = $i === $pagination['current_page'] ? ' active' : '';
        $html .= '<a href="' . $baseUrl . 'page=' . $i . '" class="pagination-num' . $active . '">' . $i . '</a>';
    }

    if ($end < $pagination['total_pages']) {
        if ($end < $pagination['total_pages'] - 1) {
            $html .= '<span class="pagination-dots">...</span>';
        }
        $html .= '<a href="' . $baseUrl . 'page=' . $pagination['total_pages'] . '" class="pagination-num">' . $pagination['total_pages'] . '</a>';
    }

    // Next button
    if ($pagination['has_next']) {
        $html .= '<a href="' . $baseUrl . 'page=' . $pagination['next_page'] . '" class="pagination-btn">' . $next . ' &raquo;</a>';
    }

    $html .= '</div>';

    return $html;
}

/**
 * Get breadcrumb HTML
 */
function breadcrumbs($items)
{
    $lang = getCurrentLanguage();
    $home = $lang === 'ar' ? 'الرئيسية' : 'Home';

    $html = '<nav class="breadcrumb" aria-label="breadcrumb">';
    $html .= '<ol itemscope itemtype="https://schema.org/BreadcrumbList">';

    // Home
    $html .= '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
    $html .= '<a itemprop="item" href="' . SITE_URL . '"><span itemprop="name">' . $home . '</span></a>';
    $html .= '<meta itemprop="position" content="1">';
    $html .= '</li>';

    $position = 2;
    $lastIndex = count($items) - 1;

    foreach ($items as $index => $item) {
        $isLast = $index === $lastIndex;
        $html .= '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"' . ($isLast ? ' class="active"' : '') . '>';

        if ($isLast || empty($item['url'])) {
            $html .= '<span itemprop="name">' . e($item['title']) . '</span>';
        } else {
            $html .= '<a itemprop="item" href="' . e($item['url']) . '"><span itemprop="name">' . e($item['title']) . '</span></a>';
        }

        $html .= '<meta itemprop="position" content="' . $position . '">';
        $html .= '</li>';
        $position++;
    }

    $html .= '</ol></nav>';

    return $html;
}

/**
 * Log activity
 */
function logActivity($action, $description = '', $entityType = null, $entityId = null)
{
    try {
        $adminId = $_SESSION['admin_id'] ?? null;
        $stmt = db()->prepare("INSERT INTO activity_logs (admin_id, action, description, entity_type, entity_id, ip_address, user_agent) 
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $adminId,
            $action,
            $description,
            $entityType,
            $entityId,
            getClientIP(),
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Send email
 */
function sendEmail($to, $subject, $body, $isHTML = true)
{
    $headers = [];
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = $isHTML ? 'Content-type: text/html; charset=UTF-8' : 'Content-type: text/plain; charset=UTF-8';
    $headers[] = 'From: ' . SITE_NAME . ' <' . ADMIN_EMAIL . '>';
    $headers[] = 'Reply-To: ' . ADMIN_EMAIL;
    $headers[] = 'X-Mailer: PHP/' . phpversion();

    return mail($to, $subject, $body, implode("\r\n", $headers));
}

/**
 * Validate email
 */
function isValidEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Clean phone number
 */
function cleanPhoneNumber($phone)
{
    return preg_replace('/[^0-9+]/', '', $phone);
}

/**
 * Format phone number for display
 */
function formatPhoneNumber($phone)
{
    $phone = cleanPhoneNumber($phone);
    if (strlen($phone) === 11 && substr($phone, 0, 2) === '01') {
        // Egyptian mobile
        return '+20-' . substr($phone, 1, 2) . '-' . substr($phone, 3, 3) . '-' . substr($phone, 6);
    }
    return $phone;
}

/**
 * Get uploaded file URL with fallback
 */
function getUploadedUrl($filename, $directory = 'images', $fallback = 'placeholder.jpg')
{
    if (empty($filename)) {
        return ASSETS_URL . '/images/' . $fallback;
    }

    if (filter_var($filename, FILTER_VALIDATE_URL)) {
        return $filename;
    }

    return UPLOADS_URL . '/' . $directory . '/' . $filename;
}

<?php
ob_start(); // Buffer output to prevent headers sent errors
/**
 * Ahmed Ashraf Portfolio - Configuration File
 * 
 * Database connection and site-wide settings
 */

// Error Reporting (Set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session Configuration
if (session_status() === PHP_SESSION_NONE) {
    // Relaxed settings for debugging/compatibility
    // ini_set('session.cookie_httponly', 1);
    // ini_set('session.use_only_cookies', 1);
    // ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    session_start();
}

// Timezone
date_default_timezone_set('Africa/Cairo');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'mido_db');
define('DB_USER', 'mido_user');
define('DB_PASS', '8Fhja&166');
define('DB_CHARSET', 'utf8mb4');

// Site Configuration
define('SITE_URL', 'https://ahmedmidoo.com');
define('SITE_NAME', 'Ahmed Ashraf');
define('ADMIN_EMAIL', 'info@ahmedmidoo.com');

// Directory Paths
define('ROOT_PATH', dirname(__DIR__) . '/');
define('INCLUDES_PATH', ROOT_PATH . 'includes/');
define('ASSETS_PATH', ROOT_PATH . 'assets/');
define('UPLOADS_PATH', ROOT_PATH . 'uploads/');
define('APPS_PATH', ROOT_PATH . 'apps/');

// URL Paths
define('ASSETS_URL', SITE_URL . '/assets');
define('UPLOADS_URL', SITE_URL . '/uploads');
define('APPS_URL', SITE_URL . '/apps');

// Upload Settings
define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
define('ALLOWED_DOC_TYPES', ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'txt']);
define('ALLOWED_VIDEO_TYPES', ['mp4', 'webm', 'mov', 'avi']);

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');
define('PASSWORD_COST', 12);

// Language Settings
define('DEFAULT_LANGUAGE', 'en');
define('SUPPORTED_LANGUAGES', ['en', 'ar']);

/**
 * Database Connection Class
 */
class Database
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->pdo;
    }

    // Prevent cloning
    private function __clone()
    {
    }

    // Prevent unserialization
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }
}

/**
 * Get database connection
 */
function db()
{
    return Database::getInstance()->getConnection();
}

/**
 * Get current language
 */
function getCurrentLanguage()
{
    if (isset($_SESSION['language']) && in_array($_SESSION['language'], SUPPORTED_LANGUAGES)) {
        return $_SESSION['language'];
    }

    if (isset($_COOKIE['language']) && in_array($_COOKIE['language'], SUPPORTED_LANGUAGES)) {
        $_SESSION['language'] = $_COOKIE['language'];
        return $_COOKIE['language'];
    }

    return DEFAULT_LANGUAGE;
}

/**
 * Set language
 */
function setLanguage($lang)
{
    if (in_array($lang, SUPPORTED_LANGUAGES)) {
        $_SESSION['language'] = $lang;
        setcookie('language', $lang, time() + (86400 * 365), '/');
        return true;
    }
    return false;
}

/**
 * Check if current language is RTL
 */
function isRTL()
{
    return getCurrentLanguage() === 'ar';
}

/**
 * Get text direction
 */
function getDirection()
{
    return isRTL() ? 'rtl' : 'ltr';
}

// Include other required files
require_once INCLUDES_PATH . 'functions.php';
require_once INCLUDES_PATH . 'auth.php';
require_once INCLUDES_PATH . 'seo.php';

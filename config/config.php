<?php
/**
 * KisanMitra AP — Core Configuration, PDO Connection, Session & CSRF Guard
 */
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Environment & API Keys (Replace with your live keys or set in system environment)
define('APP_NAME', 'KisanMitra AP');
define('APP_VERSION', '2.0.0');
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('DB_NAME') ?: 'kisanmitra_ap');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

define('GEMINI_API_KEY', getenv('GEMINI_API_KEY') ?: 'YOUR_GEMINI_API_KEY_HERE');
define('GEMINI_MODEL', 'gemini-2.5-flash');
define('OPENWEATHER_API_KEY', getenv('OPENWEATHER_API_KEY') ?: 'YOUR_OPENWEATHER_API_KEY_HERE');

// 2. Bilingual Language Handling (English 'en' / Telugu 'te')
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'te'], true)) {
    $_SESSION['lang'] = $_GET['lang'];
}
$currentLang = $_SESSION['lang'] ?? 'en';
$langFile = __DIR__ . '/../lang/' . $currentLang . '.php';
$L = file_exists($langFile) ? require $langFile : require __DIR__ . '/../lang/en.php';

function t(string $key): string {
    global $L;
    return htmlspecialchars($L[$key] ?? $key, ENT_QUOTES, 'UTF-8');
}

// 3. CSRF Token Generation & Validation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') . '">';
}

function verify_csrf(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// 4. PDO Database Connection with Graceful Offline Fallback
function get_db(): ?PDO {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        error_log('KisanMitra DB Connection Error: ' . $e->getMessage());
        return null;
    }
}

// 5. Output Escaping Helper
function e(?string $val): string {
    return htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8');
}

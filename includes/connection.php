<?php
require_once 'x.php';

// Global Authentication Check
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS'])]);
    session_start();
}

// CSRF: tolak POST dari origin lain (tiada perubahan borang diperlukan)
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $src = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '';
    if ($src === '' || parse_url($src, PHP_URL_HOST) !== parse_url('//' . ($_SERVER['HTTP_HOST'] ?? 'localhost'), PHP_URL_HOST)) {
        http_response_code(403);
        exit('Permintaan ditolak.');
    }
}

// RBAC: hanya peranan tersenarai dibenarkan
function gms_require_role(array $roles)
{
    if (!in_array($_SESSION['user_type'] ?? '', $roles, true)) {
        http_response_code(403);
        exit('Akses ditolak.');
    }
}

$current_page = basename($_SERVER['PHP_SELF']);
$public_pages = ['login.php', 'login_action.php'];

// Sekat akses ke halaman dalaman jika belum log masuk
if (!in_array($current_page, $public_pages)) {
    if (!isset($_SESSION['user_id'])) {
        $redirect_url = "login.php";
        if (strpos($_SERVER['REQUEST_URI'] ?? '', '/includes/') !== false) {
            $redirect_url = "../login.php";
        }
        header("Location: " . $redirect_url);
        exit();
    }
}

// Sambung DB hanya selepas auth lulus (pengguna tanpa sesi tidak mencetus sambungan)
$servername = $host;
$dBUsername = $user;
$dBPassword = $password;
$dBName = $db;
$dBPort = isset($port) ? (int)$port : 3306;

mysqli_report(MYSQLI_REPORT_OFF);
$conn = mysqli_connect($servername, $dBUsername, $dBPassword, $dBName, $dBPort);

if (!$conn) {
    error_log('GMS DB connect failed: ' . mysqli_connect_error());
    http_response_code(503);
    exit('Perkhidmatan tidak tersedia. Sila cuba sebentar lagi.');
}

mysqli_set_charset($conn, "utf8mb4");

// Fungsi pembantu penukaran tarikh ke Bahasa Melayu
if (!function_exists('gms_format_date')) {
    function gms_format_date($value, $format = 'd M Y')
    {
        if ($value instanceof DateTimeInterface) {
            $timestamp = $value->getTimestamp();
        } else {
            $timestamp = is_numeric($value) ? (int)$value : strtotime((string)$value);
        }

        if ($timestamp === false) {
            return '';
        }

        return strtr(date($format, $timestamp), [
            'Sunday' => 'Ahad',
            'Monday' => 'Isnin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Khamis',
            'Friday' => 'Jumaat',
            'Saturday' => 'Sabtu',
            'January' => 'Januari',
            'February' => 'Februari',
            'March' => 'Mac',
            'April' => 'April',
            'May' => 'Mei',
            'June' => 'Jun',
            'July' => 'Julai',
            'August' => 'Ogos',
            'September' => 'September',
            'October' => 'Oktober',
            'November' => 'November',
            'December' => 'Disember',
            'Jan' => 'Jan',
            'Feb' => 'Feb',
            'Mar' => 'Mac',
            'Apr' => 'Apr',
            'May' => 'Mei',
            'Jun' => 'Jun',
            'Jul' => 'Jul',
            'Aug' => 'Ogos',
            'Sep' => 'Sep',
            'Oct' => 'Okt',
            'Nov' => 'Nov',
            'Dec' => 'Dis'
        ]);
    }
}

// =============================================================================
// DATABASE QUERY & PREPARED STATEMENT HELPERS (Secure & High Performance)
// =============================================================================
if (!function_exists('gms_db_query')) {
    function gms_db_query($conn, $sql, $params = [], $types = '') {
        if (empty($params)) {
            return $conn->query($sql);
        }
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("GMS Prepare Error: " . $conn->error . " in SQL: " . $sql);
            return false;
        }
        if ($types === '') {
            $types = '';
            foreach ($params as $p) {
                if (is_int($p)) $types .= 'i';
                elseif (is_float($p)) $types .= 'd';
                else $types .= 's';
            }
        }
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }
}

if (!function_exists('gms_db_fetch_one')) {
    function gms_db_fetch_one($conn, $sql, $params = [], $types = '') {
        $res = gms_db_query($conn, $sql, $params, $types);
        if ($res && $res instanceof mysqli_result) {
            return $res->fetch_assoc() ?: null;
        }
        return null;
    }
}

if (!function_exists('gms_db_fetch_all')) {
    function gms_db_fetch_all($conn, $sql, $params = [], $types = '') {
        $res = gms_db_query($conn, $sql, $params, $types);
        $rows = [];
        if ($res && $res instanceof mysqli_result) {
            while ($row = $res->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        return $rows;
    }
}

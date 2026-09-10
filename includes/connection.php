<?php
require_once 'x.php';

$servername = $host;
$dBUsername = $user;
$dBPassword = $password;
$dBName = $db;
$dBPort = isset($port) ? (int)$port : 3306;

// Sambungan ke MySQL menggunakan parameter Port khusus
$conn = mysqli_connect($servername, $dBUsername, $dBPassword, $dBName, $dBPort);

// Semakan sambungan database
if (!$conn) {
    die("Sambungan gagal: " . mysqli_connect_error());
}

// Pastikan charset UTF-8 disokong sepenuhnya
mysqli_set_charset($conn, "utf8mb4");

// Global Authentication Check
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);
$public_pages = ['login.php', 'login_action.php'];

// Sekat akses ke halaman dalaman jika belum log masuk
if (!in_array($current_page, $public_pages)) {
    if (!isset($_SESSION['user_id'])) {
        $redirect_url = "login.php";
        if (strpos($_SERVER['REQUEST_URI'], '/includes/') !== false) {
            $redirect_url = "../login.php";
        }
        header("Location: " . $redirect_url);
        exit();
    }
}

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
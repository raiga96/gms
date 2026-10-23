<?php
// Semak sama ada berjalan di localhost WAMP atau di server sebenar (garisaksara)
$http_host = $_SERVER['HTTP_HOST'] ?? '';

if (strpos($http_host, 'localhost') !== false || strpos($http_host, '127.0.0.1') !== false) {
    // === TETAPAN LOCALHOST WAMP ===
    $host = 'localhost';
    $port = 3306;
    $user = 'root';
    $password = ''; // Kata laluan WAMP anda
    $db = '';
} else {
    // === TETAPAN SERVER HOSTING (garisaksara.com.my) ===
    $host = '';
    $port = ; // Port khusus hosting anda
    $user = '';
    $password = ''; // Masukkan password sebenar anda
    $db = '';
}

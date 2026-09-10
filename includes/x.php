<?php
// Semak sama ada berjalan di localhost WAMP atau di server sebenar (garisaksara)
$http_host = $_SERVER['HTTP_HOST'] ?? '';

if (strpos($http_host, 'localhost') !== false || strpos($http_host, '127.0.0.1') !== false) {
    // === TETAPAN LOCALHOST WAMP ===
    $host = 'localhost';
    $port = 3306;
    $user = 'root';
    $password = 'imdagreat1'; // Kata laluan WAMP anda
    $db = 'smartcut';
} else {
    // === TETAPAN SERVER HOSTING (garisaksara.com.my) ===
    $host = '127.0.0.1';
    $port = 3309; // Port khusus hosting anda
    $user = 'gms_garisaksara_com_my';
    $password = '68f9f36402854ffe8015e9f7031098cf'; // Masukkan password sebenar anda
    $db = 'gms_garisaksara_com_my';
}
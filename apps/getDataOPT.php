<?php
require 'conn.php';

// Pastikan UID tersedia
if (!isset($_GET['uid']) || empty($_GET['uid'])) {
    header('Content-Type: application/json');
    echo json_encode(["error" => "UID tidak ditemukan"]);
    exit;
}

$uid = $_GET['uid'];

// Encryption Key (Pastikan 32 bytes) dan IV (16 bytes)
$encryptionKey = "2e54d1151eaf55af8d889ec1425fdc85";
$iv = "5fbc84cbabd58453";

// 1. Ambil tipe user menggunakan Prepared Statement
$stmt = $conn->prepare("SELECT user_type FROM users WHERE uid = ?");
$stmt->bind_param("s", $uid);
$stmt->execute();
$result = $stmt->get_result();
$tp = $result->fetch_assoc();

if (!$tp) {
    header('Content-Type: application/json');
    echo json_encode(["error" => "User tidak terdaftar"]);
    exit;
}

$type = $tp['user_type'];

// 2. Ambil data user berdasarkan tipe
if ($type == 'CON') {
    $stmt = $conn->prepare("SELECT * FROM users AS a 
                            JOIN contractor_users AS b ON b.user_id = a.uid 
                            JOIN contractor AS c ON c.cid = b.contractor_id 
                            WHERE a.uid = ?");
} else { // SPV atau tipe lainnya
    $stmt = $conn->prepare("SELECT *, fullname AS name FROM users AS a JOIN userinfo AS b ON a.uid = b.uid WHERE a.uid = ?");
}
$stmt->bind_param("s", $uid);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

// 3. Ambil data QR Scan hari ini
$today_date = date('Y-m-d');
$stmt = $conn->prepare("SELECT * FROM attendance WHERE uid = ? AND date = ?");
$stmt->bind_param("ss", $row['uid'], $today_date);
$stmt->execute();
$qr_row1 = $stmt->get_result()->fetch_assoc();

if (empty($qr_row1['record_id'])) {
    $timeIn = $today_date . ' 00:00:00';
    $timeOut = $today_date . ' 00:00:00';
} else {
    $timeIn = $qr_row1['date'] . ' ' . $qr_row1['timeIN'];
    $timeOut = $qr_row1['date'] . ' ' . $qr_row1['timeOUT'];
}

// 4. Ambil Profile Image
$stmt = $conn->prepare("SELECT FILE_PATH FROM profile_image WHERE UID = ?");
$stmt->bind_param("s", $uid);
$stmt->execute();
$row1 = $stmt->get_result()->fetch_assoc();

// 5. Ambil Log Terakhir
$stmt = $conn->prepare("SELECT CREATED_ON FROM user_log WHERE uid = ? ORDER BY log_id DESC LIMIT 1");
$stmt->bind_param("s", $row['uid']);
$stmt->execute();
$row3 = $stmt->get_result()->fetch_assoc();

$logdate = empty($row3['CREATED_ON']) ? $today_date . ' 00:00:00' : date("d M Y, h:i a", strtotime($row3['CREATED_ON']));

// 6. Ambil Info Aplikasi
$result = $conn->query("SELECT app_version, app_link_apple, app_link_android FROM app_info WHERE app_status = 'Y' LIMIT 1");
$row4 = $result->fetch_assoc();

// 7. Ambil Akses Level
$stmt = $conn->prepare("SELECT lvl_name FROM access_level WHERE acc_lvl = ?");
$stmt->bind_param("s", $row['access_level']);
$stmt->execute();
$row5 = $stmt->get_result()->fetch_assoc();

// Menyusun Data Array
$data = array(
    "type" => $type,
    "uid" => $row['uid'],
    "name" => $row['name'],
    "email" => $row['user_email'],
    "access_level" => $row5['lvl_name'] ?? null,
    "user_type" => $row['user_type'],
    "img_path" => $row1['FILE_PATH'] ?? null,
    "timeIn" => date('h:i a', strtotime($timeIn)),
    "timeIn1" => date('H:i', strtotime($timeIn)), //sepatutnya Hi
    "timeOut" => date('h:i a', strtotime($timeOut)),
    "timeOut1" => date('H:i', strtotime($timeOut)),//sepatutnya Hi
    "record_id" => '1',
    "day" => '1',
    "logdate" => $logdate,
    "app_version" => $row4['app_version'] ?? '1.0.0',
    "ios_link" => $row4['app_link_apple'] ?? '',
    "android_link" => $row4['app_link_android'] ?? ''
);

// Convert ke JSON
$jsonData = json_encode($data);

// Proses Enkripsi
$encryptedData = openssl_encrypt($jsonData, 'aes-256-cbc', $encryptionKey, 0, $iv);

// Set Header JSON
header('Content-Type: application/json');

// PILIHAN OUTPUT (Pilih salah satu sesuai kebutuhan aplikasi Anda):

// Opsi A: Jika aplikasi Anda membaca data yang sudah TERENKRIPSI (Rekomendasi Keamanan)
//echo json_encode(["status" => "success", "data" => $encryptedData]);

// Opsi B: Jika aplikasi Anda belum siap enkripsi dan butuh JSON BIASA (Hapus Opsi A jika pakai ini)
// echo $jsonData;


// Set the Content-Type header to indicate that the response contains JSON data
header('Content-Type: application/json');

// Output the JSON data
//print_r($jsonData);
print_r($encryptedData);

$conn->close();

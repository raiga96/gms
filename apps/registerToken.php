<?php
header('Content-Type: application/json');

/**
 * registerToken.php
 * Skrip untuk menyimpan FCM Token peranti pengguna ke dalam MySQL.
 */

// 1. Konfigurasi Pangkalan Data
include 'conn.php';


// Semak sambungan
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

// 2. Ambil data daripada POST request
$uid         = isset($_POST['uid']) ? $_POST['uid'] : '';
$token       = isset($_POST['token']) ? $_POST['token'] : '';
$device_type = isset($_POST['device_type']) ? $_POST['device_type'] : 'android';

// 3. Validasi Input
if (empty($uid) || empty($token)) {
    echo json_encode(["status" => "error", "message" => "UID and Token are required"]);
    exit;
}

/**
 * 4. Simpan atau Kemaskini Token
 * Kami menggunakan 'ON DUPLICATE KEY UPDATE' supaya jika peranti yang sama 
 * mendaftar semula, kami hanya kemaskini 'updated_at' atau info berkaitan.
 */
$sql = "INSERT INTO user_fcm_tokens (uid, token, device_type) 
        VALUES (?, ?, ?) 
        ON DUPLICATE KEY UPDATE 
        updated_at = CURRENT_TIMESTAMP, 
        device_type = VALUES(device_type)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $uid, $token, $device_type);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success", 
        "message" => "Token registered successfully",
        "data" => [
            "uid" => $uid,
            "device" => $device_type
        ]
    ]);
} else {
    echo json_encode([
        "status" => "error", 
        "message" => "Failed to register token: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
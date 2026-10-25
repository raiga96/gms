<?php
header('Content-Type: application/json');

// 1. Konfigurasi Pangkalan Data
include 'conn.php';

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(["error" => "Database connection failed"]));
}

// 2. Ambil dan bersihkan parameter GET
$user_input = isset($_GET['uid']) ? trim($_GET['uid']) : '';

if (empty($user_input)) {
    echo json_encode([]);
    exit;
}

// Guna Prepared Statement untuk pastikan user_id wujud dalam table users
$user_sql = "SELECT * FROM users WHERE user_id = ? LIMIT 1";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("s", $user_input);
$user_stmt->execute();
$user_res = $user_stmt->get_result();

if ($user_res->num_rows === 0) {
    // Jika 'khairula' tiada langsung dalam table users, pulangkan array kosong
    echo json_encode([]);
    exit;
}

$user_row = $user_res->fetch_assoc();
$uid = (int)$user_row['uid'];
$user_stmt->close();


// 3. Fungsi Time Ago (Menggunakan floor untuk ketepatan)
function time_ago($timestamp) {
    $time_ago = strtotime($timestamp);
    $current_time = time();
    $time_difference = $current_time - $time_ago;

    if ($time_difference < 1) { return "Just now"; }

    $condition = array(
        12 * 30 * 24 * 60 * 60  =>  'year',
        30 * 24 * 60 * 60       =>  'month',
        7 * 24 * 60 * 60        =>  'week',
        24 * 60 * 60            =>  'day',
        60 * 60                 =>  'hour',
        60                      =>  'min',
        1                       =>  'sec'
    );

    foreach ($condition as $secs => $str) {
        $d = $time_difference / $secs;
        if ($d >= 1) {
            $t = floor($d);
            
            if ($str == 'day' && $t == 1) { return "Yesterday"; }
            if ($str == 'sec') { return "Just now"; }
            
            return $t . ' ' . $str . ($t > 1 ? 's' : '') . ' ago';
        }
    }
    return "Just now";
}

// 4. Query notifikasi melalui senarai penerima yang diindeks
$sql = "SELECT title, description as `desc`, type, is_read, created_at 
        FROM notifications n
        INNER JOIN notification_recipients nr ON nr.notification_id = n.id
        WHERE nr.uid = ?
        ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $uid);
$stmt->execute();
$result = $stmt->get_result();

$notifications = array();

while($row = $result->fetch_assoc()) {
    $row['time'] = time_ago($row['created_at']);
    
    // Guna cast (bool) untuk hantar true/false tepat ke Flutter
    $row['is_read'] = (bool)$row['is_read'];
    
    $notifications[] = $row;
}

echo json_encode($notifications);

$stmt->close();
$conn->close();
?>

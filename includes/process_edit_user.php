<?php
require 'connection.php';
gms_require_role(['ADM', 'ROOT']);

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../users_management.php');
    exit;
}

// Sanitize & validate input
$uid         = isset($_POST['uid']) ? (int)$_POST['uid'] : 0;
$user_id     = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';
$user_email  = isset($_POST['user_email']) ? trim($_POST['user_email']) : '';
$user_type   = isset($_POST['user_type']) ? trim($_POST['user_type']) : '';
$status      = isset($_POST['status']) ? trim($_POST['status']) : 'Y';
$password    = isset($_POST['user_password']) ? $_POST['user_password'] : '';
$fullname    = isset($_POST['fullname']) ? trim($_POST['fullname']) : '';
$phone       = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$sex         = isset($_POST['sex']) ? trim($_POST['sex']) : 'M';
$section     = isset($_POST['section']) ? trim($_POST['section']) : '';
$division    = isset($_POST['division']) ? trim($_POST['division']) : '';

// Validate required fields
if ($uid <= 0 || empty($user_id) || empty($user_email) || empty($user_type)) {
    $_SESSION['status'] = 'danger';
    $_SESSION['message'] = 'Ruang wajib tidak lengkap. Sila isi semua ruangan bertanda *.';
    header('Location: ../user_edit.php?uid=' . $uid);
    exit;
}

// Validate email format
if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['status'] = 'danger';
    $_SESSION['message'] = 'Format e-mel tidak sah.';
    header('Location: ../user_edit.php?uid=' . $uid);
    exit;
}

// Validate role
$valid_roles = ['SPV','PGS','ASO','SO','PGRH','ADM','ROOT','CON'];
if (!in_array($user_type, $valid_roles)) {
    $_SESSION['status'] = 'danger';
    $_SESSION['message'] = 'Peranan tidak sah.';
    header('Location: ../user_edit.php?uid=' . $uid);
    exit;
}

// Check for duplicate username (exclude current user)
$stmt = $conn->prepare("SELECT uid FROM users WHERE user_id = ? AND uid != ?");
$stmt->bind_param("si", $user_id, $uid);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows > 0) {
    $stmt->close();
    $_SESSION['status'] = 'danger';
    $_SESSION['message'] = 'Nama pengguna sudah digunakan oleh akaun lain.';
    header('Location: ../user_edit.php?uid=' . $uid);
    exit;
}
$stmt->close();

// Check for duplicate email (exclude current user)
$stmt = $conn->prepare("SELECT uid FROM users WHERE user_email = ? AND uid != ?");
$stmt->bind_param("si", $user_email, $uid);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows > 0) {
    $stmt->close();
    $_SESSION['status'] = 'danger';
    $_SESSION['message'] = 'Alamat e-mel sudah digunakan oleh akaun lain.';
    header('Location: ../user_edit.php?uid=' . $uid);
    exit;
}
$stmt->close();

// Update users table
if (!empty($password) && strlen($password) >= 8) {
    // Password provided and valid — update with new hash
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET user_id = ?, user_email = ?, user_type = ?, status = ?, user_password = ? WHERE uid = ?");
    $stmt->bind_param("sssssi", $user_id, $user_email, $user_type, $status, $hashed_password, $uid);
} else {
    // No password change — update without touching password
    $stmt = $conn->prepare("UPDATE users SET user_id = ?, user_email = ?, user_type = ?, status = ? WHERE uid = ?");
    $stmt->bind_param("ssssi", $user_id, $user_email, $user_type, $status, $uid);
}
$stmt->execute();
$stmt->close();

// Update or insert userinfo
$stmt = $conn->prepare("SELECT uid FROM userinfo WHERE uid = ?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$res = $stmt->get_result();
$has_info = $res->num_rows > 0;
$stmt->close();

if ($has_info) {
    $stmt = $conn->prepare("UPDATE userinfo SET fullname = ?, phone = ?, sex = ?, section = ?, division = ? WHERE uid = ?");
    $stmt->bind_param("sssssi", $fullname, $phone, $sex, $section, $division, $uid);
} else {
    $stmt = $conn->prepare("INSERT INTO userinfo (uid, fullname, phone, sex, section, division) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $uid, $fullname, $phone, $sex, $section, $division);
}
$stmt->execute();
$stmt->close();

$_SESSION['status'] = 'success';
$_SESSION['message'] = 'Pengguna \"' . htmlspecialchars($fullname ?: $user_id) . '\" telah dikemaskini dengan jayanya.';
header('Location: ../users_management.php');
exit;

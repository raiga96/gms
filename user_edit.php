<?php
session_start();
require 'includes/connection.php';

// Prevent SQL injection: cast uid to int
$uid = isset($_GET['uid']) ? (int)$_GET['uid'] : 0;
if ($uid <= 0) {
    $_SESSION['status'] = 'danger';
    $_SESSION['message'] = 'ID pengguna tidak sah.';
    header('Location: users_management.php');
    exit;
}

$user_roles = [
    'SPV'  => 'Penyelia',
    'PGS'  => 'Pengawas',
    'ASO'  => 'Penolong Pegawai Penguasa',
    'SO'   => 'Pegawai Penguasa',
    'PGRH' => 'Pengarah',
    'ADM'  => 'Pentadbir',
    'ROOT' => 'Pentadbir Utama',
    'CON'  => 'Kontraktor'
];

// Fetch user data
$stmt = $conn->prepare("
    SELECT u.uid, u.user_id, u.user_email, u.user_type, u.status, u.created,
           i.fullname, i.phone, i.sex, i.section, i.division
    FROM users u
    LEFT JOIN userinfo i ON u.uid = i.uid
    WHERE u.uid = ?
");
$stmt->bind_param("i", $uid);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    $_SESSION['status'] = 'danger';
    $_SESSION['message'] = 'Pengguna tidak ditemui.';
    header('Location: users_management.php');
    exit;
}

$user = $res->fetch_assoc();
$stmt->close();

$fullname  = $user['fullname']  ?? '';
$phone     = $user['phone']     ?? '';
$sex       = $user['sex']       ?? 'M';
$section   = $user['section']   ?? '';
$division  = $user['division']  ?? '';
$today_date_bm = date('d F Y');
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Green Management System - Edit Pengguna</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" integrity="sha512-5Hs3dF2AEPkpNAR7UiOHba+lRSJNeM2ECkwxUIxC1Q/FLycGTbNapWXB4tP889k5T5Ju8fs4b1P5z/iB4nMfSQ==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <!-- plugins:css -->
    <link rel="stylesheet" href="assets/vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="assets/vendors/flag-icon-css/css/flag-icons.min.css">
    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
    <!-- Layout styles -->
    <link rel="stylesheet" href="assets/css/vertical-light-layout/style.css">
    <link rel="stylesheet" href="assets/css/modern-dashboard.css?v=<?php echo filemtime(__DIR__ . '/assets/css/modern-dashboard.css'); ?>">
    <link rel="shortcut icon" href="assets/images/favicon.png">
    <style>
        .avatar-lg {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
            color: #fff;
            flex-shrink: 0;
        }
    </style>
</head>
<body>
    <div class="container-scroller">
        <?php include 'partials/navbar.php'; ?>
        <div class="container-fluid page-body-wrapper">
            <?php include 'partials/sidebar.php'; ?>
            <div class="main-panel">
                <div class="content-wrapper">

                    <!-- ============================================================== -->
                    <!-- HERO BANNER (Donezo-inspired) -->
                    <!-- ============================================================== -->
                    <div class="gms-hero-banner gms-fade-in">
                        <div class="row align-items-center g-3">
                            <div class="col-lg-4">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="gms-pulse-dot" style="background:#34d399;"></span>
                                    <span class="gms-hero-subtitle">Sistem Beroperasi</span>
                                    <span class="badge rounded-pill px-2 py-1" style="background:rgba(16,185,129,0.20);color:#34d399;font-size:0.65rem;font-weight:700;letter-spacing:0.05em;">v2.3.0</span>
                                </div>
                                <h1 class="gms-hero-title mb-1">Edit Pengguna</h1>
                                <p class="gms-hero-subtitle">DBKU Landscape Division</p>
                            </div>
                            <div class="col-lg-3 text-lg-center">
                                <div class="gms-hero-clock" id="gmsLiveClock"><?php echo date('H:i:s'); ?></div>
                                <div class="gms-hero-date"><?php echo htmlspecialchars($today_date_bm); ?></div>
                            </div>
                            <div class="col-lg-5">
                                <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                                    <div class="gms-hero-stat">
                                        <div class="gms-hero-stat-icon"><i class="fa-solid fa-id-badge"></i></div>
                                        <div>
                                            <div class="gms-hero-stat-label">ID Pengguna</div>
                                            <div class="gms-hero-stat-value">#<?php echo $uid; ?></div>
                                        </div>
                                    </div>
                                    <div class="gms-hero-stat">
                                        <div class="gms-hero-stat-icon"><i class="fa-solid fa-calendar-check"></i></div>
                                        <div>
                                            <div class="gms-hero-stat-label">Daftar</div>
                                            <div class="gms-hero-stat-value" style="font-size:0.85rem;">
                                                <?php echo date('d M Y', strtotime($user['created'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="gms-hero-stat">
                                        <div class="gms-hero-stat-icon">
                                            <i class="fa-solid fa-circle <?php echo $user['status'] === 'Y' ? 'text-success' : 'text-danger'; ?>" style="font-size:0.6rem;"></i>
                                        </div>
                                        <div>
                                            <div class="gms-hero-stat-label">Status</div>
                                            <div class="gms-hero-stat-value" style="font-size:0.85rem;">
                                                <?php echo $user['status'] === 'Y' ? 'Aktif' : 'Tidak Aktif'; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================================== -->
                    <!-- USER PROFILE CARD -->
                    <!-- ============================================================== -->
                    <div class="row g-3 mb-3">
                        <!-- Profile Summary -->
                        <div class="col-md-4">
                            <div class="card shadow h-100" style="margin-top:0;">
                                <div class="card-body text-center p-4">
                                    <?php
                                    $avatar_char = strtoupper(substr($fullname ?: $user['user_id'], 0, 1));
                                    $avatar_colors = ['#10b981','#06b6d4','#6366f1','#f59e0b','#ef4444','#8b5cf6'];
                                    $av_color = $avatar_colors[$uid % count($avatar_colors)];
                                    ?>
                                    <div class="avatar-lg mx-auto mb-3" style="background:linear-gradient(135deg,<?php echo $av_color; ?> 0%,<?php echo $av_color; ?>88 100%);">
                                        <?php echo $avatar_char; ?>
                                    </div>
                                    <h4 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($fullname ?: '-'); ?></h4>
                                    <p class="text-muted mb-2" style="font-size:0.9rem;">
                                        <i class="fa-solid fa-at me-1"></i><?php echo htmlspecialchars($user['user_id']); ?>
                                    </p>
                                    <span class="badge rounded-pill px-3 py-2 mb-3" style="background:rgba(16,185,129,0.1);color:#007442;font-size:0.8rem;font-weight:600;">
                                        <?php echo $user_roles[$user['user_type']] ?? htmlspecialchars($user['user_type']); ?>
                                    </span>
                                    <hr class="my-3">
                                    <div class="text-start">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted small"><i class="fa-solid fa-envelope me-1"></i> E-mel</span>
                                            <span class="fw-semibold small text-end" style="max-width:60%;font-size:0.8rem;word-break:break-all;"><?php echo htmlspecialchars($user['user_email']); ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted small"><i class="fa-solid fa-phone me-1"></i> Telefon</span>
                                            <span class="fw-semibold small"><?php echo htmlspecialchars($phone ?: '-'); ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted small"><i class="fa-solid fa-venus-mars me-1"></i> Jantina</span>
                                            <span class="fw-semibold small"><?php echo $sex === 'M' ? 'Lelaki' : 'Perempuan'; ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted small"><i class="fa-solid fa-building me-1"></i> Seksyen</span>
                                            <span class="fw-semibold small"><?php echo htmlspecialchars($section ?: '-'); ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted small"><i class="fa-solid fa-sitemap me-1"></i> Bahagian</span>
                                            <span class="fw-semibold small"><?php echo htmlspecialchars($division ?: '-'); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Edit Form -->
                        <div class="col-md-8">
                            <div class="card shadow h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center gap-3 mb-4">
                                        <div style="width:44px;height:44px;border-radius:12px;background:rgba(16,185,129,0.1);display:flex;align-items:center;justify-content:center;">
                                            <i class="fa-solid fa-user-pen text-success" style="font-size:1.1rem;"></i>
                                        </div>
                                        <div>
                                            <h4 class="gms-section-title mb-0">Kemaskini Maklumat</h4>
                                            <small class="text-muted">Lengkapkan semua medan bertanda <span class="text-danger">*</span></small>
                                        </div>
                                    </div>

                                    <form action="includes/process_edit_user.php" method="POST" onsubmit="return confirm('Simpan perubahan pengguna ini?');">
                                        <input type="hidden" name="uid" value="<?php echo $uid; ?>">

                                        <!-- Account Info -->
                                        <div class="card mb-3" style="border-radius:12px;border:1px solid #e2e8f0;background:#f8fafc;">
                                            <div class="card-body p-3">
                                                <h6 class="fw-bold mb-3" style="color:#007442;">
                                                    <i class="fa-solid fa-shield-halved me-1"></i> Maklumat Akaun
                                                </h6>
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-semibold text-muted mb-1">Nama Pengguna <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control form-control-sm rounded-3" name="user_id"
                                                            value="<?php echo htmlspecialchars($user['user_id']); ?>"
                                                            placeholder="cth. ahmad.osman" required style="border-color:#e2e8f0;">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-semibold text-muted mb-1">Alamat E-mel <span class="text-danger">*</span></label>
                                                        <input type="email" class="form-control form-control-sm rounded-3" name="user_email"
                                                            value="<?php echo htmlspecialchars($user['user_email']); ?>"
                                                            placeholder="example@dbku.gov.my" required style="border-color:#e2e8f0;">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-semibold text-muted mb-1">Kata Laluan Baru <span class="text-muted">(biarkan kosong jika tidak tukar)</span></label>
                                                        <input type="password" class="form-control form-control-sm rounded-3" name="user_password"
                                                            placeholder="Minimum 8 aksara" style="border-color:#e2e8f0;">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-semibold text-muted mb-1">Peranan <span class="text-danger">*</span></label>
                                                        <select class="form-select form-select-sm rounded-3" name="user_type" required style="border-color:#e2e8f0;">
                                                            <?php foreach ($user_roles as $code => $label): ?>
                                                                <option value="<?php echo $code; ?>" <?php echo $user['user_type'] === $code ? 'selected' : ''; ?>>
                                                                    <?php echo $label; ?> (<?php echo $code; ?>)
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-semibold text-muted mb-1">Status Akaun</label>
                                                        <select class="form-select form-select-sm rounded-3" name="status" style="border-color:#e2e8f0;">
                                                            <option value="Y" <?php echo $user['status'] === 'Y' ? 'selected' : ''; ?>>Aktif</option>
                                                            <option value="N" <?php echo $user['status'] === 'N' ? 'selected' : ''; ?>>Tidak Aktif</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Personal Info -->
                                        <div class="card mb-3" style="border-radius:12px;border:1px solid #e2e8f0;background:#f8fafc;">
                                            <div class="card-body p-3">
                                                <h6 class="fw-bold mb-3" style="color:#007442;">
                                                    <i class="fa-solid fa-id-card me-1"></i> Maklumat Peribadi
                                                </h6>
                                                <div class="row g-3">
                                                    <div class="col-md-12">
                                                        <label class="form-label small fw-semibold text-muted mb-1">Nama Penuh <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control form-control-sm rounded-3" name="fullname"
                                                            value="<?php echo htmlspecialchars($fullname); ?>"
                                                            placeholder="Masukkan nama penuh seperti Kad Pengenalan" required style="border-color:#e2e8f0;">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-semibold text-muted mb-1">Nombor Telefon</label>
                                                        <input type="text" class="form-control form-control-sm rounded-3" name="phone"
                                                            value="<?php echo htmlspecialchars($phone); ?>"
                                                            placeholder="cth. 012-3456789" style="border-color:#e2e8f0;">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-semibold text-muted mb-1">Jantina</label>
                                                        <select class="form-select form-select-sm rounded-3" name="sex" style="border-color:#e2e8f0;">
                                                            <option value="M" <?php echo $sex === 'M' ? 'selected' : ''; ?>>Lelaki</option>
                                                            <option value="F" <?php echo $sex === 'F' ? 'selected' : ''; ?>>Perempuan</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-semibold text-muted mb-1">Seksyen</label>
                                                        <input type="text" class="form-control form-control-sm rounded-3" name="section"
                                                            value="<?php echo htmlspecialchars($section); ?>"
                                                            placeholder="cth. Seksyen IT" style="border-color:#e2e8f0;">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-semibold text-muted mb-1">Bahagian</label>
                                                        <input type="text" class="form-control form-control-sm rounded-3" name="division"
                                                            value="<?php echo htmlspecialchars($division); ?>"
                                                            placeholder="cth. Digital" style="border-color:#e2e8f0;">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Actions -->
                                        <div class="d-flex gap-2 justify-content-end">
                                            <a href="users_management.php" class="btn btn-light rounded-pill px-4">
                                                <i class="fa-solid fa-arrow-left me-1"></i> Batal
                                            </a>
                                            <button type="submit" class="btn rounded-pill px-4" style="background:linear-gradient(135deg,#10b981 0%,#007442 100%);color:#fff;border:none;font-weight:600;">
                                                <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                </div><!-- .content-wrapper -->
                <?php include 'partials/footer.php'; ?>
            </div><!-- .main-panel -->
        </div><!-- .container-fluid -->
    </div><!-- .container-scroller -->

    <!-- plugins:js -->
    <script src="assets/vendors/js/vendor.bundle.base.js"></script>
    <!-- endinject -->
    <script src="assets/vendors/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- inject:js -->
    <script src="assets/js/off-canvas.js"></script>
    <script src="assets/js/hoverable-collapse.js"></script>
    <script src="assets/js/misc.js"></script>
    <!-- endinject -->

    <script>
    function updateClock() {
        const now = new Date();
        const timeStr = now.toLocaleTimeString('en-GB', {hour12: false});
        const dateStr = now.toLocaleDateString('ms-MY', {day:'numeric',month:'long',year:'numeric'});
        const el = document.getElementById('gmsLiveClock');
        if (el) el.textContent = timeStr;
        const de = document.querySelector('.gms-hero-date');
        if (de) de.textContent = dateStr;
    }
    updateClock();
    setInterval(updateClock, 1000);
    </script>
</body>
</html>

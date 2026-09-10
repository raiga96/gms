<?php
session_start();
require 'includes/connection.php';

// Prevent SQL injection: cast all input IDs to int
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Stats with prepared statements
$total_users = 0;
$stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM users");
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) $total_users = (int)$row['cnt'];
$stmt->close();

$total_spv = 0;
$stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM users WHERE user_type = 'SPV'");
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) $total_spv = (int)$row['cnt'];
$stmt->close();

$total_pgs = 0;
$stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM users WHERE user_type = 'PGS'");
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) $total_pgs = (int)$row['cnt'];
$stmt->close();

$total_con = 0;
$stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM users WHERE user_type = 'CON'");
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) $total_con = (int)$row['cnt'];
$stmt->close();

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

// Fetch users paginated (internal staff)
$stmt_int = $conn->prepare("
    SELECT u.uid, u.user_id, u.user_email, u.user_type, u.status, u.created,
           i.fullname, i.phone
    FROM users u
    LEFT JOIN userinfo i ON u.uid = i.uid
    WHERE u.user_type != 'CON' OR u.user_type IS NULL
    ORDER BY u.created DESC
    LIMIT ? OFFSET ?
");
$stmt_int->bind_param("ii", $per_page, $offset);
$stmt_int->execute();
$res_int = $stmt_int->get_result();

$total_int = 0;
$stmt_cnt = $conn->prepare("SELECT COUNT(*) AS cnt FROM users WHERE user_type != 'CON' OR user_type IS NULL");
$stmt_cnt->execute();
$res_cnt = $stmt_cnt->get_result();
if ($r = $res_cnt->fetch_assoc()) $total_int = (int)$r['cnt'];
$stmt_cnt->close();

// Fetch contractors paginated
$stmt_con = $conn->prepare("
    SELECT u.uid, u.user_id, u.user_email, u.user_type, u.status, u.created,
           i.fullname, i.phone
    FROM users u
    LEFT JOIN userinfo i ON u.uid = i.uid
    WHERE u.user_type = 'CON'
    ORDER BY u.created DESC
    LIMIT ? OFFSET ?
");
$stmt_con->bind_param("ii", $per_page, $offset);
$stmt_con->execute();
$res_con = $stmt_con->get_result();

$total_con_users = 0;
$stmt_cnt2 = $conn->prepare("SELECT COUNT(*) AS cnt FROM users WHERE user_type = 'CON'");
$stmt_cnt2->execute();
$res_cnt2 = $stmt_cnt2->get_result();
if ($r = $res_cnt2->fetch_assoc()) $total_con_users = (int)$r['cnt'];
$stmt_cnt2->close();

$today_date_bm = date('d F Y');
$total_pages_int = $total_int > 0 ? ceil($total_int / $per_page) : 1;
$total_pages_con = $total_con_users > 0 ? ceil($total_con_users / $per_page) : 1;
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Green Management System - Pengurusan Pengguna</title>
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
    <link rel="stylesheet" href="assets/css/modern-dashboard.css">
    <link rel="shortcut icon" href="assets/images/favicon.png">
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
                                <h1 class="gms-hero-title mb-1">Pengurusan Pengguna</h1>
                                <p class="gms-hero-subtitle">DBKU Landscape Division</p>
                            </div>
                            <div class="col-lg-3 text-lg-center">
                                <div class="gms-hero-clock" id="gmsLiveClock"><?php echo date('H:i:s'); ?></div>
                                <div class="gms-hero-date"><?php echo htmlspecialchars($today_date_bm); ?></div>
                            </div>
                            <div class="col-lg-5">
                                <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                                    <div class="gms-hero-stat">
                                        <div class="gms-hero-stat-icon"><i class="icon-people"></i></div>
                                        <div>
                                            <div class="gms-hero-stat-label">Jumlah</div>
                                            <div class="gms-hero-stat-value"><?php echo number_format($total_users); ?></div>
                                        </div>
                                    </div>
                                    <div class="gms-hero-stat">
                                        <div class="gms-hero-stat-icon"><i class="icon-eye"></i></div>
                                        <div>
                                            <div class="gms-hero-stat-label">Penyelia</div>
                                            <div class="gms-hero-stat-value"><?php echo number_format($total_spv); ?></div>
                                        </div>
                                    </div>
                                    <div class="gms-hero-stat">
                                        <div class="gms-hero-stat-icon"><i class="icon-badge"></i></div>
                                        <div>
                                            <div class="gms-hero-stat-label">Pegawai</div>
                                            <div class="gms-hero-stat-value"><?php echo number_format($total_pgs); ?></div>
                                        </div>
                                    </div>
                                    <div class="gms-hero-stat">
                                        <div class="gms-hero-stat-icon"><i class="icon-briefcase"></i></div>
                                        <div>
                                            <div class="gms-hero-stat-label">Kontraktor</div>
                                            <div class="gms-hero-stat-value"><?php echo number_format($total_con); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================================== -->
                    <!-- ACTION BANNER -->
                    <!-- ============================================================== -->
                    <div class="gms-console gms-fade-in">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                            <div>
                                <h3 class="gms-console-title mb-1">
                                    <i class="fa-solid fa-users-gear me-2 text-success"></i>Daftar Pengguna Baharu
                                </h3>
                                <p class="gms-console-sub mb-0">Urus akaun dan akses pengguna sistem GMS</p>
                            </div>
                            <button type="button" class="gms-console-btn gms-console-btn-assign" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                <i class="fa-solid fa-user-plus me-1"></i> Tambah Pengguna
                            </button>
                        </div>
                    </div>

                    <!-- ============================================================== -->
                    <!-- TABS: Staff vs Contractor -->
                    <!-- ============================================================== -->
                    <div class="gms-fade-in" style="margin-top:1.5rem;">
                        <ul class="nav nav-pills mb-3 gap-2" id="userTabs" role="tablist" style="border-bottom:2px solid #e2e8f0;">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active rounded-pill px-4 fw-semibold" id="staff-tab" data-bs-toggle="tab" data-bs-target="#staffPane" type="button" role="tab" aria-controls="staffPane" aria-selected="true"
                                    style="background:#10b981;color:#fff;font-size:0.85rem;">
                                    <i class="fa-solid fa-building-user me-1"></i> Staff Dalaman
                                    <span class="badge rounded-pill ms-1" style="background:rgba(255,255,255,0.25);font-size:0.7rem;"><?php echo $total_int; ?></span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link rounded-pill px-4 fw-semibold" id="contractor-tab" data-bs-toggle="tab" data-bs-target="#contractorPane" type="button" role="tab" aria-controls="contractorPane" aria-selected="false"
                                    style="background:#f1f5f9;color:#475569;font-size:0.85rem;">
                                    <i class="fa-solid fa-toolbox me-1"></i> Kontraktor
                                    <span class="badge rounded-pill ms-1" style="background:#e2e8f0;color:#475569;font-size:0.7rem;"><?php echo $total_con_users; ?></span>
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="userTabsContent">
                            <!-- ============================================================== -->
                            <!-- STAFF TAB -->
                            <!-- ============================================================== -->
                            <div class="tab-pane fade show active" id="staffPane" role="tabpanel" aria-labelledby="staff-tab">
                                <div class="card shadow mt-0">
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="gms-table mb-0">
                                                <thead>
                                                    <tr>
                                                        <th style="width:30%;">Nama Penuh</th>
                                                        <th style="width:15%;">Nama Pengguna</th>
                                                        <th style="width:20%;">E-mel</th>
                                                        <th style="width:12%;">Peranan</th>
                                                        <th style="width:10%;">Status</th>
                                                        <th style="width:13%;">Tindakan</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    if ($res_int && $res_int->num_rows > 0) {
                                                        while ($row = $res_int->fetch_assoc()) {
                                                            $role_name = $user_roles[$row['user_type']] ?? htmlspecialchars($row['user_type']);
                                                            $status_badge = $row['status'] === 'Y'
                                                                ? '<span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2">' . '<i class="fa-solid fa-circle me-1" style="font-size:0.4rem;vertical-align:middle;"></i>Aktif</span>'
                                                                : '<span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-2">' . '<i class="fa-solid fa-circle me-1" style="font-size:0.4rem;vertical-align:middle;"></i>Tidak Aktif</span>';
                                                            $avatar = strtoupper(substr($row['fullname'] ?? $row['user_id'], 0, 1));
                                                            $avatar_color = ['#10b981','#06b6d4','#6366f1','#f59e0b','#ef4444'][mt_rand(0,4)];
                                                            echo "<tr>";
                                                            echo "<td>
                                                                <div class=\"d-flex align-items-center gap-3\">
                                                                    <div style=\"width:36px;height:36px;border-radius:10px;background:{$avatar_color}22;color:{$avatar_color};display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.85rem;flex-shrink:0;\">{$avatar}</div>
                                                                    <div>
                                                                        <div class=\"fw-bold text-dark\" style=\"font-size:0.9rem;\">" . htmlspecialchars($row['fullname'] ?? '-') . "</div>
                                                                        <div class=\"text-muted\" style=\"font-size:0.75rem;\">" . htmlspecialchars($row['phone'] ?? '') . "</div>
                                                                    </div>
                                                                </div>
                                                            </td>";
                                                            echo "<td style=\"font-family:'Outfit',sans-serif;font-size:0.85rem;\">" . htmlspecialchars($row['user_id']) . "</td>";
                                                            echo "<td style=\"font-size:0.85rem;\">" . htmlspecialchars($row['user_email']) . "</td>";
                                                            echo "<td><span class=\"badge bg-secondary rounded-pill px-3 py-2\" style=\"font-size:0.75rem;\">" . $role_name . "</span></td>";
                                                            echo "<td>" . $status_badge . "</td>";
                                                            echo "<td>
                                                                <div class=\"d-flex gap-2\">
                                                                    <a href=\"user_edit.php?uid=" . (int)$row['uid'] . "\" class=\"btn btn-sm rounded-circle\" style=\"width:32px;height:32px;display:inline-flex;align-items:center;justify-content:center;background:rgba(0,116,66,0.08);color:#007442;text-decoration:none;transition:all 0.2s;\" title=\"Edit\" onmouseover=\"this.style.background='#007442';this.style.color='#fff'\" onmouseout=\"this.style.background='rgba(0,116,66,0.08)';this.style.color='#007442'\">
                                                                        <i class=\"fa-solid fa-pen\" style=\"font-size:0.75rem;\"></i>
                                                                    </a>
                                                                    <a href=\"#\" class=\"btn btn-sm rounded-circle\" style=\"width:32px;height:32px;display:inline-flex;align-items:center;justify-content:center;background:rgba(239,68,68,0.08);color:#ef4444;text-decoration:none;transition:all 0.2s;\" title=\"Padam\" onmouseover=\"this.style.background='#ef4444';this.style.color='#fff'\" onmouseout=\"this.style.background='rgba(239,68,68,0.08)';this.style.color='#ef4444'\">
                                                                        <i class=\"fa-solid fa-trash\" style=\"font-size:0.75rem;\"></i>
                                                                    </a>
                                                                </div>
                                                            </td>";
                                                            echo "</tr>";
                                                        }
                                                    } else {
                                                        echo "<tr><td colspan=\"6\" class=\"text-center py-5 text-muted\">
                                                            <i class=\"fa-solid fa-users-slash\" style=\"font-size:2rem;opacity:0.3;display:block;margin-bottom:0.5rem;\"></i>
                                                            Tiada pengguna dalaman ditemui.
                                                        </td></tr>";
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <?php if ($total_pages_int > 1): ?>
                                        <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top">
                                            <span class="text-muted small">Muka surat <?php echo $page; ?> daripada <?php echo $total_pages_int; ?> — <?php echo $total_int; ?> rekod</span>
                                            <div class="d-flex gap-1">
                                                <?php if ($page > 1): ?>
                                                    <a href="?page=<?php echo $page - 1; ?>" class="btn btn-sm btn-outline-secondary rounded-pill"><i class="fa-solid fa-chevron-left"></i></a>
                                                <?php endif; ?>
                                                <?php for ($p = max(1, $page - 2); $p <= min($total_pages_int, $page + 2); $p++): ?>
                                                    <a href="?page=<?php echo $p; ?>" class="btn btn-sm rounded-pill <?php echo $p === $page ? 'btn-success' : 'btn-outline-secondary'; ?>"><?php echo $p; ?></a>
                                                <?php endfor; ?>
                                                <?php if ($page < $total_pages_int): ?>
                                                    <a href="?page=<?php echo $page + 1; ?>" class="btn btn-sm btn-outline-secondary rounded-pill"><i class="fa-solid fa-chevron-right"></i></a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- ============================================================== -->
                            <!-- CONTRACTOR TAB -->
                            <!-- ============================================================== -->
                            <div class="tab-pane fade" id="contractorPane" role="tabpanel" aria-labelledby="contractor-tab">
                                <div class="card shadow mt-0">
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="gms-table mb-0">
                                                <thead>
                                                    <tr>
                                                        <th style="width:30%;">Nama Penuh</th>
                                                        <th style="width:15%;">Nama Pengguna</th>
                                                        <th style="width:20%;">E-mel</th>
                                                        <th style="width:12%;">Peranan</th>
                                                        <th style="width:10%;">Status</th>
                                                        <th style="width:13%;">Tindakan</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    if ($res_con && $res_con->num_rows > 0) {
                                                        while ($row = $res_con->fetch_assoc()) {
                                                            $role_name = $user_roles[$row['user_type']] ?? htmlspecialchars($row['user_type']);
                                                            $status_badge = $row['status'] === 'Y'
                                                                ? '<span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2">' . '<i class="fa-solid fa-circle me-1" style="font-size:0.4rem;vertical-align:middle;"></i>Aktif</span>'
                                                                : '<span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-2">' . '<i class="fa-solid fa-circle me-1" style="font-size:0.4rem;vertical-align:middle;"></i>Tidak Aktif</span>';
                                                            $avatar = strtoupper(substr($row['fullname'] ?? $row['user_id'], 0, 1));
                                                            $avatar_color = ['#10b981','#06b6d4','#6366f1','#f59e0b','#ef4444'][mt_rand(0,4)];
                                                            echo "<tr>";
                                                            echo "<td>
                                                                <div class=\"d-flex align-items-center gap-3\">
                                                                    <div style=\"width:36px;height:36px;border-radius:10px;background:{$avatar_color}22;color:{$avatar_color};display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.85rem;flex-shrink:0;\">{$avatar}</div>
                                                                    <div>
                                                                        <div class=\"fw-bold text-dark\" style=\"font-size:0.9rem;\">" . htmlspecialchars($row['fullname'] ?? '-') . "</div>
                                                                        <div class=\"text-muted\" style=\"font-size:0.75rem;\">" . htmlspecialchars($row['phone'] ?? '') . "</div>
                                                                    </div>
                                                                </div>
                                                            </td>";
                                                            echo "<td style=\"font-family:'Outfit',sans-serif;font-size:0.85rem;\">" . htmlspecialchars($row['user_id']) . "</td>";
                                                            echo "<td style=\"font-size:0.85rem;\">" . htmlspecialchars($row['user_email']) . "</td>";
                                                            echo "<td><span class=\"badge bg-secondary rounded-pill px-3 py-2\" style=\"font-size:0.75rem;\">" . $role_name . "</span></td>";
                                                            echo "<td>" . $status_badge . "</td>";
                                                            echo "<td>
                                                                <div class=\"d-flex gap-2\">
                                                                    <a href=\"user_edit.php?uid=" . (int)$row['uid'] . "\" class=\"btn btn-sm rounded-circle\" style=\"width:32px;height:32px;display:inline-flex;align-items:center;justify-content:center;background:rgba(0,116,66,0.08);color:#007442;text-decoration:none;transition:all 0.2s;\" title=\"Edit\" onmouseover=\"this.style.background='#007442';this.style.color='#fff'\" onmouseout=\"this.style.background='rgba(0,116,66,0.08)';this.style.color='#007442'\">
                                                                        <i class=\"fa-solid fa-pen\" style=\"font-size:0.75rem;\"></i>
                                                                    </a>
                                                                    <a href=\"#\" class=\"btn btn-sm rounded-circle\" style=\"width:32px;height:32px;display:inline-flex;align-items:center;justify-content:center;background:rgba(239,68,68,0.08);color:#ef4444;text-decoration:none;transition:all 0.2s;\" title=\"Padam\" onmouseover=\"this.style.background='#ef4444';this.style.color='#fff'\" onmouseout=\"this.style.background='rgba(239,68,68,0.08)';this.style.color='#ef4444'\">
                                                                        <i class=\"fa-solid fa-trash\" style=\"font-size:0.75rem;\"></i>
                                                                    </a>
                                                                </div>
                                                            </td>";
                                                            echo "</tr>";
                                                        }
                                                    } else {
                                                        echo "<tr><td colspan=\"6\" class=\"text-center py-5 text-muted\">
                                                            <i class=\"fa-solid fa-toolbox\" style=\"font-size:2rem;opacity:0.3;display:block;margin-bottom:0.5rem;\"></i>
                                                            Tiada pengguna kontraktor ditemui.
                                                        </td></tr>";
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <?php if ($total_pages_con > 1): ?>
                                        <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top">
                                            <span class="text-muted small">Muka surat 1 daripada <?php echo $total_pages_con; ?> — <?php echo $total_con_users; ?> rekod</span>
                                            <div class="d-flex gap-1">
                                                <?php if ($page > 1): ?>
                                                    <a href="?page=<?php echo $page - 1; ?>#contractorPane" class="btn btn-sm btn-outline-secondary rounded-pill"><i class="fa-solid fa-chevron-left"></i></a>
                                                <?php endif; ?>
                                                <?php for ($p = 1; $p <= $total_pages_con; $p++): ?>
                                                    <a href="?page=<?php echo $p; ?>#contractorPane" class="btn btn-sm rounded-pill <?php echo $p === $page ? 'btn-success' : 'btn-outline-secondary'; ?>"><?php echo $p; ?></a>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div><!-- .content-wrapper -->
                <?php include 'partials/footer.php'; ?>
            </div><!-- .main-panel -->
        </div><!-- .container-fluid -->
    </div><!-- .container-scroller -->

    <!-- ============================================================== -->
    <!-- ADD USER MODAL (Donezo-styled) -->
    <!-- ============================================================== -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="border-radius:var(--gms-card-radius,16px);border:none;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
                <div class="modal-header border-bottom-0 pb-0" style="background:linear-gradient(135deg,#0d1713 0%,#00311c 100%);border-radius:var(--gms-card-radius,16px) var(--gms-card-radius,16px) 0 0;">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:48px;height:48px;border-radius:12px;background:rgba(16,185,129,0.2);display:flex;align-items:center;justify-content:center;">
                            <i class="fa-solid fa-user-plus text-success" style="font-size:1.2rem;"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0" id="addUserModalLabel" style="color:#fff;">Daftar Pengguna Baharu</h5>
                            <p class="mb-0 small" style="color:rgba(255,255,255,0.65);">Green Management System — DBKU Landscape Division</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form action="includes/process_add_user.php" method="POST" onsubmit="return confirm('Savings this user?')">
                    <div class="modal-body" style="background:#f8fafc;">
                        <!-- Account Info Card -->
                        <div class="card shadow-sm mb-3" style="border-radius:12px;border:none;">
                            <div class="card-body p-3">
                                <h6 class="fw-bold mb-3" style="color:#007442;"><i class="fa-solid fa-shield-halved me-1"></i> Maklumat Akaun</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-muted mb-1">Nama Pengguna <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm rounded-3" name="user_id" placeholder="cth. ahmad.osman" required style="border-color:#e2e8f0;">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-muted mb-1">Alamat E-mel <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control form-control-sm rounded-3" name="user_email" placeholder="example@dbku.gov.my" required style="border-color:#e2e8f0;">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-muted mb-1">Kata Laluan <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control form-control-sm rounded-3" name="user_password" placeholder="Minimum 8 aksara" required style="border-color:#e2e8f0;">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-muted mb-1">Peranan <span class="text-danger">*</span></label>
                                        <select class="form-select form-select-sm rounded-3" name="user_type" required style="border-color:#e2e8f0;">
                                            <option value="" disabled selected>-- Pilih Peranan --</option>
                                            <option value="ADM">Pentadbir (ADM)</option>
                                            <option value="SPV">Penyelia (SPV)</option>
                                            <option value="PGS">Pengawas (PGS)</option>
                                            <option value="ASO">Penolong Pegawai Penguasa (ASO)</option>
                                            <option value="SO">Pegawai Penguasa (SO)</option>
                                            <option value="PGRH">Pengarah (PGRH)</option>
                                            <option value="ROOT">Pentadbir Utama (ROOT)</option>
                                            <option value="CON">Kontraktor (CON)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Personal Info Card -->
                        <div class="card shadow-sm" style="border-radius:12px;border:none;">
                            <div class="card-body p-3">
                                <h6 class="fw-bold mb-3" style="color:#007442;"><i class="fa-solid fa-id-card me-1"></i> Maklumat Peribadi</h6>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label small fw-semibold text-muted mb-1">Nama Penuh <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm rounded-3" name="fullname" placeholder="Masukkan nama penuh seperti Kad Pengenalan" required style="border-color:#e2e8f0;">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-muted mb-1">Nombor Telefon</label>
                                        <input type="text" class="form-control form-control-sm rounded-3" name="phone" placeholder="cth. 012-3456789" style="border-color:#e2e8f0;">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-muted mb-1">Jantina</label>
                                        <select class="form-select form-select-sm rounded-3" name="sex" style="border-color:#e2e8f0;">
                                            <option value="M">Lelaki</option>
                                            <option value="F">Perempuan</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-muted mb-1">Seksyen</label>
                                        <input type="text" class="form-control form-control-sm rounded-3" name="section" placeholder="cth. Seksyen IT" style="border-color:#e2e8f0;">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-muted mb-1">Bahagian</label>
                                        <input type="text" class="form-control form-control-sm rounded-3" name="division" placeholder="cth. Digital" style="border-color:#e2e8f0;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0" style="background:#f8fafc;border-radius:0 0 var(--gms-card-radius,16px) var(--gms-card-radius,16px);">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn rounded-pill px-4" style="background:linear-gradient(135deg,#10b981 0%,#007442 100%);color:#fff;border:none;font-weight:600;">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Pengguna
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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
    // Live clock
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

    // Tab state from URL hash
    if (window.location.hash === '#contractorPane') {
        const tab = new bootstrap.Tab(document.getElementById('contractor-tab'));
        tab.show();
    }
    </script>
</body>
</html>

<?php
require 'includes/connection.php';

// Upload KMZ Logic (Hardened & Parameterized)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'upload_kmz') {
    $uploadDir = 'kmz/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $sz_id_post = (int)($_POST['sz_id'] ?? 0);
    $zone_id_post = (int)($_POST['zone_id'] ?? 0);
    $cid_post = (int)($_POST['contract_id_param'] ?? 0);
    $publicUrlBase = 'https://smartcut.com.my/kmz/';

    if (!empty($_FILES['kmz_file']['name'])) {
        $originalName = basename($_FILES['kmz_file']['name']);
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($ext, ['kmz', 'kml'])) {
            $_SESSION['status'] = 'error';
            $_SESSION['message'] = 'Hanya fail berformat .kmz atau .kml sahaja yang dibenarkan.';
        } else {
            $cleanName = preg_replace('/[^A-Za-z0-9\._-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
            $cleanName = strtolower(time() . '_' . $cleanName . '.' . $ext);
            $targetPath = $uploadDir . $cleanName;
            $publicUrl = $publicUrlBase . $cleanName;

            if (move_uploaded_file($_FILES['kmz_file']['tmp_name'], $targetPath)) {
                $user_uid = (int)($_SESSION['uid'] ?? 0);
                $stmt_i = $conn->prepare("INSERT INTO kmz_file (zone_id, sz_id, kmz_file, file_path, url, add_by) VALUES (?, ?, ?, ?, ?, ?)");
                if ($stmt_i) {
                    $stmt_i->bind_param("iisssi", $zone_id_post, $sz_id_post, $cleanName, $targetPath, $publicUrl, $user_uid);
                    $stmt_i->execute();
                    $stmt_i->close();
                }

                $stmt_u = $conn->prepare("UPDATE subzone SET kmz_file = ? WHERE sz_id = ?");
                if ($stmt_u) {
                    $stmt_u->bind_param("si", $cleanName, $sz_id_post);
                    $stmt_u->execute();
                    $stmt_u->close();
                }

                $_SESSION['status'] = 'success';
                $_SESSION['message'] = 'Fail KMZ berjaya dimuat naik.';
            } else {
                $_SESSION['status'] = 'error';
                $_SESSION['message'] = 'Gagal memuat naik fail KMZ.';
            }
        }
    }
    header("Location: contract_details.php?id=" . $cid_post);
    exit();
}

$cid = (int)($_GET['id'] ?? 0);
if ($cid <= 0) {
    header("Location: contract_list.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Butiran Kontrak &mdash; Green Management System (DBKU)</title>
    <link rel="shortcut icon" href="assets/images/favicon.png" />
    <link rel="stylesheet" href="assets/vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="assets/vendors/flag-icon-css/css/flag-icons.min.css">
    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="assets/vendors/font-awesome/css/font-awesome.min.css" />
    <link rel="stylesheet" href="assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="assets/vendors/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="assets/css/vertical-light-layout/style.css">
    <link rel="stylesheet" href="assets/css/modern-dashboard.css?v=<?php echo filemtime(__DIR__ . '/assets/css/modern-dashboard.css'); ?>">
</head>

<body>
    <div class="container-scroller">
        <!-- partial:partials/_navbar.html -->
        <?php include 'partials/navbar.php' ?>
        <!-- partial -->
        <div class="container-fluid page-body-wrapper">
            <!-- partial:partials/_sidebar.html -->
            <?php include 'partials/sidebar.php' ?>
            <!-- partial -->
            <div class="main-panel" style="height: 100%;">
                <div class="content-wrapper">
                    <?php
                    // Fetch all contract, subzone, zone, contractor, and DBKU personnel details in ONE single JOIN query
                    $stmt = $conn->prepare("
                        SELECT ct.*,
                               sz.sz_name, sz.perimeter, sz.area,
                               z.zone_id, z.zone_name,
                               c.name as contractor_name, c.email as contractor_email, c.address as contractor_address,
                               u_spv.user_id as spv_username, u_spv.user_email as spv_email,
                               u_pgs.user_id as pgs_username, u_pgs.user_email as pgs_email
                        FROM contract ct
                        LEFT JOIN subzone sz ON ct.sz_id = sz.sz_id
                        LEFT JOIN zone z ON sz.zone_id = z.zone_id
                        LEFT JOIN contractor c ON ct.cid = c.cid
                        LEFT JOIN users u_spv ON ct.spv_id = u_spv.uid
                        LEFT JOIN users u_pgs ON ct.pgs_id = u_pgs.uid
                        WHERE ct.contract_id = ?
                    ");
                    $row1 = null;
                    if ($stmt) {
                        $stmt->bind_param("i", $cid);
                        $stmt->execute();
                        $row1 = $stmt->get_result()->fetch_assoc();
                        $stmt->close();
                    }

                    if (!$row1) {
                        header("Location: contract_list.php");
                        exit;
                    }

                    $row2 = [
                        'zone_id' => $row1['zone_id'],
                        'sz_name' => $row1['sz_name'],
                        'kmz_file' => $row1['sz_kmz'] ?? ''
                    ];
                    $row3 = [
                        'zone_name' => $row1['zone_name'],
                        'zone_id' => $row1['zone_id']
                    ];
                    $row4 = [
                        'name' => $row1['contractor_name'],
                        'email' => $row1['contractor_email'],
                        'address' => $row1['contractor_address']
                    ];
                    $dbku_spv_name = $row1['spv_username'] ?: '-';
                    $dbku_spv_email = $row1['spv_email'] ?: '-';
                    $dbku_pgs_name = $row1['pgs_username'] ?: '-';
                    $dbku_pgs_email = $row1['pgs_email'] ?: '-';

                    // Check kmz_file
                    $sz_id_kmz = (int)$row1['sz_id'];
                    $has_kmz = false;
                    $kmz_url = '';
                    $stmt_kmz = $conn->prepare("SELECT url FROM kmz_file WHERE sz_id = ? ORDER BY id DESC LIMIT 1");
                    if ($stmt_kmz) {
                        $stmt_kmz->bind_param("i", $sz_id_kmz);
                        $stmt_kmz->execute();
                        $res_kmz = $stmt_kmz->get_result();
                        if ($res_kmz && $kmz_record = $res_kmz->fetch_assoc()) {
                            if (!empty($kmz_record['url'])) {
                                $has_kmz = true;
                                $kmz_url = $kmz_record['url'];
                            }
                        } elseif (!empty($row1['sz_kmz'])) {
                            $has_kmz = true;
                            $kmz_url = 'https://smartcut.com.my/kmz/' . $row1['sz_kmz'];
                        }
                        $stmt_kmz->close();
                    }

                    $start = new DateTime($row1['start_date']);
                    $end = new DateTime($row1['end_date']);
                    $interval = $start->diff($end);
                    $duration_months = ($interval->y * 12) + $interval->m;
                    if ($interval->d > 0) {
                        $duration_months++;
                    }

                    // --- Session Filter Logic for Daily Diary (Hardened) ---
                    $contract_id = (int)$row1['contract_id'];
                    $current_year = (int)date("Y");
                    $current_month = (int)date("n");
                    $current_day = (int)date("j");
                    $current_session_default = ($current_month - 1) * 2 + ($current_day <= 15 ? 1 : 2);

                    $filter_year = !empty($_GET['filter_year']) ? (int)$_GET['filter_year'] : 0;
                    $filter_session = !empty($_GET['filter_session']) ? (int)$_GET['filter_session'] : 0;

                    $stmt_avail = $conn->prepare("SELECT DISTINCT YEAR(date) as yr FROM daily_records WHERE cid = ? ORDER BY yr DESC");
                    $available_years = [];
                    if ($stmt_avail) {
                        $stmt_avail->bind_param("s", $contract_id);
                        $stmt_avail->execute();
                        $avail_res = $stmt_avail->get_result();
                        if ($avail_res && $avail_res->num_rows > 0) {
                            while ($ar = $avail_res->fetch_assoc()) {
                                $available_years[] = (int)$ar['yr'];
                            }
                        }
                        $stmt_avail->close();
                    }
                    if (empty($available_years)) {
                        $available_years = [$current_year];
                    }

                    if (!in_array($filter_year, $available_years, true)) {
                        $filter_year = $available_years[0];
                    }
                    if ($filter_session <= 0) {
                        $year_start = sprintf('%04d-01-01', $filter_year);
                        $year_end = sprintf('%04d-01-01', $filter_year + 1);
                        $stmt_max = $conn->prepare("SELECT MAX(gc_session) as max_s FROM daily_records WHERE cid = ? AND date >= ? AND date < ?");
                        if ($stmt_max) {
                            $stmt_max->bind_param("sss", $contract_id, $year_start, $year_end);
                            $stmt_max->execute();
                            $max_s_row = $stmt_max->get_result()->fetch_assoc();
                            if ($max_s_row && !empty($max_s_row['max_s'])) {
                                $filter_session = (int)$max_s_row['max_s'];
                            } else {
                                $filter_session = $current_session_default;
                            }
                            $stmt_max->close();
                        } else {
                            $filter_session = $current_session_default;
                        }
                    }

                    // Determine start and end dates for the selected session
                    $month = ceil($filter_session / 2);
                    if ($filter_session % 2 != 0) {
                        $start_date = sprintf("%04d-%02d-01", $filter_year, $month);
                        $end_date = sprintf("%04d-%02d-15", $filter_year, $month);
                    } else {
                        $start_date = sprintf("%04d-%02d-16", $filter_year, $month);
                        $end_date = date("Y-m-t", strtotime(sprintf("%04d-%02d-01", $filter_year, $month)));
                    }

                    // Fetch existing records for the date range
                    $stmt_dr = $conn->prepare("SELECT * FROM daily_records WHERE cid = ? AND date >= ? AND date <= ? ORDER BY date ASC");
                    $daily_data = [];
                    if ($stmt_dr) {
                        $stmt_dr->bind_param("sss", $contract_id, $start_date, $end_date);
                        $stmt_dr->execute();
                        $res_records = $stmt_dr->get_result();
                        if ($res_records && $res_records->num_rows > 0) {
                            while ($dr = $res_records->fetch_assoc()) {
                                $daily_data[$dr['date']] = $dr;
                            }
                        }
                        $stmt_dr->close();
                    }
                    ?>
                    <!-- ============================================================== -->
                    <!-- HERO BANNER -->
                    <!-- ============================================================== -->
                    <div class="gms-hero-banner gms-fade-in">
                        <div class="row align-items-center g-3">
                            <div class="col-lg-8">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="gms-pulse-dot" style="background:#34d399;"></span>
                                    <span class="gms-hero-subtitle" style="color: rgba(255,255,255,0.75);">Butiran Kontrak</span>
                                </div>
                                <h1 class="gms-hero-title mb-1">Butiran Kontrak &mdash; <?php echo htmlspecialchars($row4['name']); ?></h1>
                                <p class="gms-hero-subtitle">Kontrak di bawah Zon <?php echo htmlspecialchars($row3['zone_name']); ?></p>
                            </div>
                            <div class="col-lg-4">
                                <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                                    <div class="gms-hero-stat">
                                        <div class="gms-hero-stat-icon"><i class="icon-calendar"></i></div>
                                        <div>
                                            <div class="gms-hero-stat-label">Durasi</div>
                                            <div class="gms-hero-stat-value"><?php echo $duration_months; ?> Bulan</div>
                                        </div>
                                    </div>
                                    <div class="gms-hero-stat">
                                        <div class="gms-hero-stat-icon"><i class="icon-briefcase"></i></div>
                                        <div>
                                            <div class="gms-hero-stat-label">Kontraktor</div>
                                            <div class="gms-hero-stat-value" style="font-size:0.85rem;"><?php echo htmlspecialchars($row4['name']); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================================== -->
                    <!-- KPI METRIC CARDS (Donezo-inspired) -->
                    <!-- ============================================================== -->
                    <div class="row g-3 mb-4 gms-fade-in gms-fade-in-delay-1">
                        <!-- KPI 1: Contract Number (FEATURED) -->
                        <div class="col-sm-6 col-xl-3">
                            <div class="gms-kpi-card kpi-featured">
                                <div>
                                    <div class="gms-kpi-header">
                                        <span class="gms-kpi-label">No. Kontrak</span>
                                        <div class="gms-kpi-icon">
                                            <i class="icon-doc"></i>
                                        </div>
                                    </div>
                                    <div class="gms-kpi-value" style="font-size:1.2rem;"><?php echo htmlspecialchars($row1['contract_number']); ?></div>
                                </div>
                                <div class="gms-kpi-footer">
                                    <span style="background: rgba(255,255,255,0.15); color: #6ee7b7; padding: 3px 9px; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;">
                                        <i class="fa-solid fa-file-contract"></i> Aktif
                                    </span>
                                    <span>DBKU</span>
                                </div>
                            </div>
                        </div>

                        <!-- KPI 2: Fund -->
                        <div class="col-sm-6 col-xl-3">
                            <div class="gms-kpi-card kpi-soft kpi-emerald">
                                <div>
                                    <div class="gms-kpi-header">
                                        <span class="gms-kpi-label">Nilai Kontrak</span>
                                        <div class="gms-kpi-icon">
                                            <i class="icon-size-fullscreen"></i>
                                        </div>
                                    </div>
                                    <div class="gms-kpi-value">RM <?php echo number_format($row1['fund'], 2); ?></div>
                                </div>
                                <div class="gms-kpi-footer">
                                    <span class="gms-kpi-badge badge-emerald-soft">
                                        <i class="fa-solid fa-coins"></i> Peruntukan
                                    </span>
                                    <span>DBKU</span>
                                </div>
                            </div>
                        </div>

                        <!-- KPI 3: Duration -->
                        <div class="col-sm-6 col-xl-3">
                            <div class="gms-kpi-card kpi-soft kpi-cyan">
                                <div>
                                    <div class="gms-kpi-header">
                                        <span class="gms-kpi-label">Tempoh</span>
                                        <div class="gms-kpi-icon">
                                            <i class="icon-calendar"></i>
                                        </div>
                                    </div>
                                    <div class="gms-kpi-value" style="font-size:1.2rem;"><?php echo $duration_months; ?> <small style="font-size:0.85rem; font-weight:600;">Bulan</small></div>
                                </div>
                                <div class="gms-kpi-footer">
                                    <span class="gms-kpi-badge badge-cyan-soft">
                                        <i class="fa-solid fa-clock"></i> Masa
                                    </span>
                                    <span><?php echo gms_format_date($start, 'd M Y'); ?> - <?php echo gms_format_date($end, 'd M Y'); ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- KPI 4: Status -->
                        <div class="col-sm-6 col-xl-3">
                            <div class="gms-kpi-card kpi-soft kpi-amber">
                                <div>
                                    <div class="gms-kpi-header">
                                        <span class="gms-kpi-label">Status</span>
                                        <div class="gms-kpi-icon">
                                            <i class="icon-check"></i>
                                        </div>
                                    </div>
                                    <div class="gms-kpi-value" style="font-size:1.2rem;"><span class="gms-status-dot" style="background-color:#10b981;"></span> Aktif</div>
                                </div>
                                <div class="gms-kpi-footer">
                                    <span class="gms-kpi-badge badge-amber-soft">
                                        <i class="fa-solid fa-circle-check"></i> Berjaya
                                    </span>
                                    <span>DBKU</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- KPI END -->

                    <!-- Contract Overview Info Cards -->
                    <div class="row mb-4 gms-fade-in gms-fade-in-delay-2">
                        <div class="col-md-12 grid-margin stretch-card">
                            <div class="card shadow">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                        <div>
                                            <h4 class="gms-section-title">
                                                Gambaran Kontrak
                                            </h4>
                                            <p class="gms-section-subtitle">Butiran keseluruhan kontrak penyerahan kerja</p>
                                        </div>
                                        <span class="gms-badge-contract"><i class="fa-solid fa-circle-check me-1"></i> Aktif</span>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 mb-3 mb-md-0">
                                            <div class="gms-kpi-card kpi-soft kpi-blue" style="--gms-kpi-icon-bg: rgba(37,99,235,0.12); --gms-kpi-icon-color: #2563eb;">
                                                <div>
                                                    <div class="gms-kpi-header">
                                                        <span class="gms-kpi-label">Maklumat Asas</span>
                                                        <div class="gms-kpi-icon">
                                                            <i class="icon-info"></i>
                                                        </div>
                                                    </div>
                                                    <div class="gms-kpi-value" style="font-size:1rem;">Kontrak #<?php echo htmlspecialchars($row1['contract_number']); ?></div>
                                                </div>
                                                <div class="gms-kpi-footer">
                                                    <span class="gms-kpi-badge" style="background:rgba(37,99,235,0.1); color:#1e40af;">Butiran</span>
                                                    <span>ID: <?php echo $cid; ?></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4 mb-3 mb-md-0">
                                            <div class="gms-kpi-card kpi-soft kpi-green">
                                                <div>
                                                    <div class="gms-kpi-header">
                                                        <span class="gms-kpi-label">Kontraktor</span>
                                                        <div class="gms-kpi-icon" style="background:rgba(16,185,129,0.12); color:#059669;">
                                                            <i class="icon-briefcase"></i>
                                                        </div>
                                                    </div>
                                                    <div class="gms-kpi-value" style="font-size:1rem;"><?php echo htmlspecialchars($row4['name']); ?></div>
                                                </div>
                                                <div class="gms-kpi-footer">
                                                    <span class="gms-kpi-badge badge-cyan-soft">
                                                        <i class="fa-solid fa-user"></i> Syarikat
                                                    </span>
                                                    <span>CID: <?php echo $row1['cid']; ?></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="gms-kpi-card kpi-soft kpi-purple">
                                                <div>
                                                    <div class="gms-kpi-header">
                                                        <span class="gms-kpi-label">Pengawasan DBKU</span>
                                                        <div class="gms-kpi-icon" style="background:rgba(147,51,234,0.12); color:#9333ea;">
                                                            <i class="icon-badge"></i>
                                                        </div>
                                                    </div>
                                                    <div class="gms-kpi-value" style="font-size:0.9rem;"><?php echo htmlspecialchars($dbku_spv_name); ?></div>
                                                </div>
                                                <div class="gms-kpi-footer">
                                                    <span class="gms-kpi-badge" style="background:rgba(147,51,234,0.1); color:#6b21a8;">
                                                        <i class="fa-solid fa-user-shield"></i> SPV/PGS
                                                    </span>
                                                    <span><?php echo htmlspecialchars($dbku_pgs_name); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Map Subzone -->
                    <div class="row mb-4 gms-fade-in gms-fade-in-delay-3">
                        <div class="col-md-12 grid-margin stretch-card">
                            <div class="card shadow">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                        <div>
                                            <h4 class="gms-section-title">
                                                Peta Sub-Zon
                                            </h4>
                                            <p class="gms-section-subtitle">Visualisasi lokasi tapak kontrak</p>
                                        </div>
                                        <?php if(!$has_kmz): ?>
                                            <span class="gms-tag-pill" style="background:rgba(245,158,11,0.1); color:#d97706; border:1px solid rgba(245,158,11,0.2);"><i class="fa-solid fa-triangle-exclamation me-1"></i> KMZ Diperlukan</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if($has_kmz): ?>
                                        <div id="map" class="gms-map-container" style="height: 350px;"></div>
                                    <?php else: ?>
                                        <div class="text-center py-5 border rounded" style="background-color: #f8fafc; border-style: dashed !important; border-width: 2px !important;">
                                            <i class="icon-cloud-upload text-muted mb-3" style="font-size: 48px;"></i>
                                            <h5 class="text-muted fw-bold">Tiada Fail KMZ Ditemui</h5>
                                            <p class="text-small text-muted">Sila muat naik fail KMZ untuk subzon ini.</p>
                                            <button type="button" class="btn btn-primary mt-3 px-4 py-2" data-bs-toggle="modal" data-bs-target="#uploadKmzModal" style="border-radius: 12px;">
                                                <i class="icon-cloud-upload me-2"></i>Muat Naik KMZ
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Daily Diary Table -->
                    <div class="row mb-4 gms-fade-in gms-fade-in-delay-4">
                        <div class="col-md-12 grid-margin stretch-card">
                            <div class="card shadow gms-table-card">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom flex-wrap gap-2">
                                        <div>
                                            <h4 class="gms-section-title">
                                                Rekod Harian Tapak
                                            </h4>
                                            <p class="gms-section-subtitle">Pemantauan aktiviti harian kontrak</p>
                                        </div>
                                        <form method="GET" action="contract_details.php" class="d-flex align-items-center gap-2 m-0">
                                            <input type="hidden" name="id" value="<?php echo $cid; ?>">
                                            <div class="gms-table-search">
                                                <i class="icon-calendar"></i>
                                                <select name="filter_year" class="form-select form-select-sm" style="min-width: 100px; border-radius: 12px; padding: 8px 14px;">
                                                    <?php
                                                    foreach($available_years as $y) {
                                                        $sel = ($y == $filter_year) ? 'selected' : '';
                                                        echo "<option value='$y' $sel>$y</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="gms-table-search">
                                                <i class="icon-clock"></i>
                                                <select name="filter_session" class="form-select form-select-sm" style="min-width: 120px; border-radius: 12px; padding: 8px 14px;">
                                                    <?php
                                                    for($i=1; $i<=24; $i++) {
                                                        $sel = ($i == $filter_session) ? 'selected' : '';
                                                        echo "<option value='$i' $sel>Sesi $i</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <button type="submit" class="btn btn-primary btn-sm" style="border-radius: 12px; padding: 8px 18px;">
                                                <i class="icon-magnifier me-1"></i>Tapis
                                            </button>
                                        </form>
                                    </div>
                                    <div class="gms-table-wrapper mb-2">
                                        <table class="gms-table">
                                            <thead>
                                                <tr>
                                                    <th>Tarikh</th>
                                                    <th>Sesi</th>
                                                    <th>Kontraktor</th>
                                                    <th>Pengawas</th>
                                                    <th>Pengawas DBKU</th>
                                                    <th>Disahkan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $current = strtotime($start_date);
                                                $last = strtotime($end_date);

                                                while($current <= $last) {
                                                    $d_str = date("Y-m-d", $current);
                                                    if(isset($daily_data[$d_str])) {
                                                        $row_daily = $daily_data[$d_str];
                                                ?>
                                                        <tr>
                                                            <td>
                                                                <a href="sitedaily_records.php?id=<?php echo $row_daily['id'] ?>">
                                                                    <?php echo gms_format_date($row_daily['date'], 'd M Y') ?>
                                                                </a>
                                                            </td>
                                                            <td>Sesi <?php echo htmlspecialchars($row_daily['gc_session']) ?></td>
                                                            <td>
                                                                <?php echo ($row_daily['contractor_dsign'] === 'Y') ? '<i class="fa fa-check-circle text-success" style="font-size: 18px;" title="Ditelan"></i>' : '<i class="fa fa-times-circle text-danger" style="font-size: 18px;" title="Tak Ditelan"></i>'; ?>
                                                            </td>
                                                            <td>
                                                                <?php echo ($row_daily['supervisor_dsign'] === 'Y') ? '<i class="fa fa-check-circle text-success" style="font-size: 18px;" title="Ditelan"></i>' : '<i class="fa fa-times-circle text-danger" style="font-size: 18px;" title="Tak Ditelan"></i>'; ?>
                                                            </td>
                                                            <td>
                                                                <?php echo ($row_daily['pengawas_dsign'] === 'Y') ? '<i class="fa fa-check-circle text-success" style="font-size: 18px;" title="Ditelan"></i>' : '<i class="fa fa-times-circle text-danger" style="font-size: 18px;" title="Tak Ditelan"></i>'; ?>
                                                            </td>
                                                            <td>
                                                                <?php echo ($row_daily['verified'] === 'Y') ? '<i class="fa fa-check-square text-success" style="font-size: 18px;" title="Disahkan"></i>' : '<i class="fa fa-minus-circle text-danger" style="font-size: 18px;" title="Tak Disahkan"></i>'; ?>
                                                            </td>
                                                        </tr>
                                                <?php
                                                    } else {
                                                ?>
                                                        <tr>
                                                            <td><?php echo gms_format_date($current, 'd M Y') ?></td>
                                                            <td>Sesi <?php echo htmlspecialchars($filter_session) ?></td>
                                                            <td colspan="4" class="text-center text-muted">
                                                                <span class="gms-tag-pill" style="background:rgba(239,68,68,0.08); color:#dc2626; border:1px dashed rgba(239,68,68,0.3);"><i class="fa-solid fa-circle-xmark me-1"></i> Tiada Data</span>
                                                            </td>
                                                        </tr>
                                                <?php
                                                    }
                                                    $current = strtotime("+1 day", $current);
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contract Location List -->
                    <div class="row mb-4 gms-fade-in gms-fade-in-delay-5">
                        <div class="col-md-12 grid-margin stretch-card">
                            <div class="card shadow gms-table-card">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                        <div>
                                            <h4 class="gms-section-title">
                                                Senarai Lokasi Kontrak
                                            </h4>
                                            <p class="gms-section-subtitle">Kawasan dan lokasi di bawah kontrak ini</p>
                                        </div>
                                    </div>
                                    <div class="gms-table-wrapper">
                                        <table class="gms-table">
                                            <thead>
                                                <tr>
                                                    <th style="width: 25%;">Kawasan</th>
                                                    <th style="width: 60%;">Lokasi</th>
                                                    <th style="width: 15%; text-align: center;">Tindakan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                // Get the primary key column name dynamically to ensure it works
                                                $pk_col = 'id';
                                                $res_pk = $conn->query("SHOW KEYS FROM contract_location WHERE Key_name = 'PRIMARY'");
                                                if ($res_pk && $res_pk->num_rows > 0) {
                                                    $pk_row = $res_pk->fetch_assoc();
                                                    $pk_col = $pk_row['Column_name'];
                                                }

                                                $sql = "SELECT * FROM contract_location WHERE sz_id = '$row1[sz_id]' ORDER BY area ASC";
                                                $res = $conn->query($sql);
                                                if ($res->num_rows > 0) {
                                                    while ($row = $res->fetch_assoc()) {
                                                        $loc_id = $row[$pk_col];
                                                ?>
                                                    <tr style="height:60px" id="row_<?php echo $loc_id; ?>">
                                                        <td style="width: 20px;">
                                                            <input type="text" id="area_<?php echo $loc_id; ?>" value="<?php echo htmlspecialchars($row['area'] ?? ''); ?>" class="form-control" readonly style="border-radius: 12px; border: 1px solid #e2e8f0; padding: 10px 14px;">
                                                        </td>
                                                        <td style="width: 250px">
                                                            <textarea id="location_<?php echo $loc_id; ?>" cols="30" rows="3" class="form-control" readonly style="border-radius: 12px; border: 1px solid #e2e8f0; padding: 10px 14px;"><?php echo htmlspecialchars($row['location'] ?? ''); ?></textarea>
                                                        </td>
                                                        <td>
                                                            <div class="text-center">
                                                                <button type="button" class="action-btn-circle btn-circle-edit" data-id="<?php echo $loc_id; ?>" title="Kemaskini"><i class="fa fa-edit"></i></button>
                                                                <button type="button" class="action-btn-circle btn-circle-delete" data-id="<?php echo $loc_id; ?>" title="Padam"><i class="fa fa-trash"></i></button>
                                                                <button type="button" class="action-btn-circle btn-save-modern btn-save d-none" data-id="<?php echo $loc_id; ?>" title="Simpan"><i class="fa fa-save"></i></button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php }
                                                } else { ?>
                                                    <tr>
                                                        <td colspan="3" class="text-center text-muted py-5">
                                                            <h5 class="font-weight-bold">Tiada data tersedia</h5>
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                                <!-- Add Location Row -->
                                                <tr style="height:60px">
                                                    <td style="width: 25%;">
                                                        <input type="hidden" id="sz_id_input" value="<?php echo htmlspecialchars($row1['sz_id']); ?>">
                                                        <input type="text" id="area_input" class="form-control" placeholder="Kawasan" style="border-radius: 12px; border: 1px solid #e2e8f0; padding: 10px 14px;">
                                                    </td>
                                                    <td style="width: 60%;">
                                                        <textarea id="location_input" cols="30" rows="3" class="form-control" placeholder="Lokasi" style="border-radius: 12px; border: 1px solid #e2e8f0; padding: 10px 14px;"></textarea>
                                                    </td>
                                                    <td>
                                                        <div class="text-center">
                                                            <button type="button" id="btnAddLocation" class="action-btn-circle" style="background-color: rgba(0,116,66,0.08); color: #007442;" title="Tambah Lokasi"><i class="fa fa-plus"></i></button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- content-wrapper ends -->
                    <!-- partial:partials/_footer.html -->
                    <?php include 'partials/footer.php' ?>
                    <!-- partial -->
                </div>
                <!-- main-panel ends -->
            </div>
            <!-- page-body-wrapper ends -->
        </div>
        <!-- container-scroller -->
        <!-- plugins:js -->
        <script src="assets/vendors/js/vendor.bundle.base.js"></script>
        <!-- endinject -->
        <!-- Plugin js for this page -->
        <script src="assets/vendors/chart.js/chart.umd.js"></script>
        <script src="assets/vendors/jvectormap/jquery-jvectormap.min.js"></script>
        <script src="assets/vendors/jvectormap/jquery-jvectormap-world-mill-en.js"></script>
        <script src="assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
        <script src="assets/vendors/moment/moment.min.js"></script>
        <script src="assets/vendors/daterangepicker/daterangepicker.js"></script>
        <script src="assets/vendors/chartist/chartist.min.js"></script>
        <script src="assets/vendors/progressbar.js/progressbar.min.js"></script>
        <script src="assets/js/jquery.cookie.js"></script>
        <!-- endinject -->
        <!-- inject:js -->
        <script src="assets/js/off-canvas.js"></script>
        <script src="assets/js/hoverable-collapse.js"></script>
        <script src="assets/js/misc.js"></script>
        <script src="assets/js/settings.js"></script>
        <script src="assets/js/todolist.js"></script>
        <!-- endinject -->
        <!-- Custom js for this page -->
        <script src="assets/js/dashboard.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <!-- End custom js for this page -->

        <script async defer src="https://maps.googleapis.com/maps/api/js?key=&#65306;redacted:AIzaâ€¦&callback=initMap"></script>
        <script>
        function initMap() {
            var map = new google.maps.Map(document.getElementById('map'), {
                zoom: 13,
                center: {lat: 1.5535, lng: 110.3593}, // Default Kuching
                mapTypeId: google.maps.MapTypeId.HYBRID
            });

            <?php if($has_kmz): ?>
            var kmzLayer = new google.maps.KmlLayer({
                url: '<?php echo $kmz_url ?>',
                map: map,
                preserveViewport: false
            });
            <?php endif; ?>
        }

        $(document).ready(function() {
            // --- ADD LOCATION --
            $('#btnAddLocation').on('click', function(e) {
                e.preventDefault();

                var sz_id = $('#sz_id_input').val();
                var area = $('#area_input').val();
                var location = $('#location_input').val();

                if (!area || !location) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Oops...',
                        text: 'Sila isi kedua-dua ruangan Kawasan dan Lokasi!',
                    });
                    return;
                }

                $.ajax({
                    url: 'add_locations.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        sz_id: sz_id,
                        area: area,
                        location: location
                    },
                    beforeSend: function() {
                        $('#btnAddLocation').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berjaya!',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Ralat!',
                                text: response.message,
                            });
                            $('#btnAddLocation').prop('disabled', false).html('<i class="fa fa-plus"></i>');
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Ralat Sistem!',
                            text: 'Gagal menghubungi pelayan.',
                        });
                        $('#btnAddLocation').prop('disabled', false).html('<i class="fa fa-plus"></i>');
                    }
                });
            });

            // --- EDIT BUTTON --
            $('.btn-edit').on('click', function() {
                var id = $(this).data('id');
                // Remove readonly attributes
                $('#area_' + id).removeAttr('readonly').focus();
                $('#location_' + id).removeAttr('readonly');

                // Hide edit button, show save button
                $(this).addClass('d-none');
                $(this).siblings('.btn-save').removeClass('d-none');
            });

            // --- SAVE (UPDATE) BUTTON --
            $('.btn-save').on('click', function() {
                var id = $(this).data('id');
                var area = $('#area_' + id).val();
                var location = $('#location_' + id).val();
                var btn = $(this);

                if (!area || !location) {
                    Swal.fire('Oops...', 'Ruangan tidak boleh dibiarkan kosong!', 'warning');
                    return;
                }

                $.ajax({
                    url: 'update_location.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        id: id,
                        area: area,
                        location: location
                    },
                    beforeSend: function() {
                        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berjaya dikemaskini!',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                // Set back to readonly
                                $('#area_' + id).attr('readonly', true);
                                $('#location_' + id).attr('readonly', true);

                                // Hide save, show edit
                                btn.addClass('d-none').prop('disabled', false).html('<i class="fa fa-save"></i>');
                                btn.siblings('.btn-edit').removeClass('d-none');
                            });
                        } else {
                            Swal.fire('Ralat!', response.message, 'error');
                            btn.prop('disabled', false).html('<i class="fa fa-save"></i>');
                        }
                    },
                    error: function() {
                        Swal.fire('Ralat Sistem!', 'Gagal menghubungi pelayan.', 'error');
                        btn.prop('disabled', false).html('<i class="fa fa-save"></i>');
                    }
                });
            });

            // --- DELETE BUTTON --
            $('.btn-delete').on('click', function() {
                var id = $(this).data('id');

                Swal.fire({
                    title: 'Adakah anda pasti?',
                    text: "Data yang dipadam tidak boleh dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Padam!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'delete_location.php',
                            type: 'POST',
                            dataType: 'json',
                            data: { id: id },
                            success: function(response) {
                                if (response.status === 'success') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Telah dipadam!',
                                        showConfirmButton: false,
                                        timer: 1500
                                    }).then(() => {
                                        $('#row_' + id).fadeOut(300, function() { $(this).remove(); });
                                    });
                                } else {
                                    Swal.fire('Ralat!', response.message, 'error');
                                }
                            },
                            error: function() {
                                Swal.fire('Ralat Sistem!', 'Gagal menghubungi pelayan.', 'error');
                            }
                        });
                    }
                });
            });
        });
        </script>

        <?php
        // Check if there is a status message in the session
        if (isset($_SESSION['status'])) {
            $status = $_SESSION['status'];   // 'success' or 'error'
            $message = $_SESSION['message']; // Error or success message

            // Clear session message after use
            unset($_SESSION['status']);
            unset($_SESSION['message']);

            // Only output script if valid
            if ($status === 'success') {
                echo "<script>
                        Swal.fire({
                            icon: 'success',
                            title: 'Berjaya',
                            text: '{$message}'
                        });
                      </script>";
            } else {
                echo "<script>
                        Swal.fire({
                            icon: 'error',
                            title: 'Ralat',
                            text: '{$message}'
                        });
                      </script>";
            }
        }
        ?>

        <!-- Upload KMZ Modal -->
        <div class="modal fade" id="uploadKmzModal" tabindex="-1" aria-labelledby="uploadKmzModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content" style="border-radius: 12px; border: none;">
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title fw-bold" id="uploadKmzModalLabel">Muat Naik Fail KMZ</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <form method="POST" action="contract_details.php?id=<?php echo $cid; ?>" enctype="multipart/form-data">
                        <div class="modal-body p-4">
                            <input type="hidden" name="action" value="upload_kmz">
                            <input type="hidden" name="contract_id_param" value="<?php echo $cid; ?>">
                            <input type="hidden" name="sz_id" value="<?php echo $row1['sz_id']; ?>">
                            <input type="hidden" name="zone_id" value="<?php echo $row2['zone_id']; ?>">

                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted small text-uppercase">Pilih Fail KMZ</label>
                                <input class="form-control" type="file" name="kmz_file" accept=".kmz" required style="border-radius: 12px; border: 1px solid #e2e8f0; padding: 10px 14px;">
                            </div>
                        </div>
                        <div class="modal-footer border-top-0 pt-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 12px;">Batal</button>
                            <button type="submit" class="btn btn-primary" style="border-radius: 12px;"><i class="icon-cloud-upload me-2"></i>Muat Naik</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Action button styles (small block for btn-circle-edit/btn-circle-delete) -->
        <style>
            .btn-circle-edit {
                width: 36px; height: 36px; border-radius: 50%;
                display: inline-flex; align-items: center; justify-content: center;
                background-color: #eff6ff; color: #3b82f6;
                border: none; transition: all 0.2s ease;
            }
            .btn-circle-edit:hover {
                background-color: #3b82f6; color: #ffffff;
                box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3);
                transform: scale(1.1);
            }
            .btn-circle-delete {
                width: 36px; height: 36px; border-radius: 50%;
                display: inline-flex; align-items: center; justify-content: center;
                background-color: #fef2f2; color: #ef4444;
                border: none; transition: all 0.2s ease;
            }
            .btn-circle-delete:hover {
                background-color: #ef4444; color: #ffffff;
                box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3);
                transform: scale(1.1);
            }
        </style>
    </body>
</html>

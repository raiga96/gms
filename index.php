<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'includes/connection.php';

// =============================================================================
// HERO STATS
// =============================================================================
$sql_zone = "SELECT COUNT(*) AS total_zone FROM zone";
$res_zone = $conn->query($sql_zone);
$total_zone = ($res_zone && $row = $res_zone->fetch_assoc()) ? (int)$row['total_zone'] : 0;

$sql_sub_stats = "SELECT COUNT(*) AS total_subzone, COALESCE(SUM(perimeter), 0) AS total_perimeter, COALESCE(SUM(area), 0) AS total_area FROM subzone";
$res_sub_stats = $conn->query($sql_sub_stats);
$sub_stats = ($res_sub_stats && $row = $res_sub_stats->fetch_assoc()) ? $row : ['total_subzone' => 0, 'total_perimeter' => 0, 'total_area' => 0];
$total_subzone = (int)$sub_stats['total_subzone'];
$total_perimeter = (float)$sub_stats['total_perimeter'];
$total_area = (float)$sub_stats['total_area'];

// =============================================================================
// CORE QUERIES
// =============================================================================
$sql_tc = "SELECT COUNT(*) AS total_contract FROM contract";
$res_tc = $conn->query($sql_tc);
$row_tc = $res_tc ? $res_tc->fetch_assoc() : ['total_contract' => 0];
$total_contract = (int)$row_tc['total_contract'];

$sql_tcon = "SELECT COUNT(*) AS total_contractor FROM contractor";
$res_tcon = $conn->query($sql_tcon);
$row_tcon = $res_tcon ? $res_tcon->fetch_assoc() : ['total_contractor' => 0];
$total_contractor = (int)$row_tcon['total_contractor'];

// Session
$current_month = (int)date('n');
$current_day = (int)date('j');
$session_num = ($current_month - 1) * 2 + ($current_day <= 15 ? 1 : 2);
$current_session = "Sesi " . $session_num;
$session_cycle = ($current_day <= 15 ? "Kitaran 1 (1 - 15 " . gms_format_date(time(), 'M') . ")" : "Kitaran 2 (16 - " . gms_format_date(time(), 't M') . ")");

// Contractors Data for Table
$contractors_sql = "
    SELECT c.cid, c.name,
        COUNT(ct.contract_id) as jumlah_contract,
        GROUP_CONCAT(ct.contract_number SEPARATOR ', ') as number_contract,
        GROUP_CONCAT(sz.sz_name SEPARATOR ', ') as zone_names
    FROM contractor c
    LEFT JOIN contract ct ON c.cid = ct.cid
    LEFT JOIN subzone sz ON ct.sz_id = sz.sz_id
    GROUP BY c.cid
";
$contractors_res = $conn->query($contractors_sql);

// =============================================================================
// TODAY'S OPERATIONS METRICS (single pass over daily_records)
// =============================================================================
$sql_daily_metrics = "SELECT COUNT(*) AS total_dr,
    COALESCE(SUM(verified = 'Y'), 0) AS verified_total,
    COALESCE(SUM(verified = 'Y' AND verified_on >= CURDATE() AND verified_on < CURDATE() + INTERVAL 1 DAY), 0) AS verified_today,
    COALESCE(SUM(verified = 'N' AND date <= CURDATE()), 0) AS pending_verification,
    COALESCE(SUM(date = CURDATE()), 0) AS active_today
FROM daily_records";
$daily_metrics_result = $conn->query($sql_daily_metrics);
$daily_metrics = ($daily_metrics_result && $row = $daily_metrics_result->fetch_assoc()) ? $row : [];
$verified_today = (int)($daily_metrics['verified_today'] ?? 0);
$pending_verification = (int)($daily_metrics['pending_verification'] ?? 0);
$active_today = (int)($daily_metrics['active_today'] ?? 0);
$total_dr = (int)($daily_metrics['total_dr'] ?? 0);
$verified_total = (int)($daily_metrics['verified_total'] ?? 0);
$verify_pct = ($total_dr > 0) ? round(($verified_total / $total_dr) * 100) : 0;

// =============================================================================
// ZONE STATUS GRID
// =============================================================================
$zones_data = [];
$sql_zones = "
    SELECT z.zone_id, z.zone_name,
        COUNT(DISTINCT sz.sz_id) AS total_subzone,
        COALESCE(SUM(CASE WHEN dr.verified = 'Y' AND dr.verified_on >= CURDATE() AND dr.verified_on < CURDATE() + INTERVAL 1 DAY THEN 1 ELSE 0 END), 0) AS verified_today,
        COALESCE(SUM(CASE WHEN dr.verified = 'N' AND dr.date <= CURDATE() THEN 1 ELSE 0 END), 0) AS pending
    FROM zone z
    LEFT JOIN subzone sz ON sz.zone_id = z.zone_id
    LEFT JOIN contract ct ON ct.sz_id = sz.sz_id AND ct.c_status = 'Y'
    LEFT JOIN daily_records dr ON dr.cid = ct.contract_id
    GROUP BY z.zone_id, z.zone_name
    ORDER BY z.zone_name ASC
";
$res_zones = $conn->query($sql_zones);
if ($res_zones && $res_zones->num_rows > 0) {
    while ($row = $res_zones->fetch_assoc()) {
        $zones_data[] = [
            'zone_id' => (int)$row['zone_id'],
            'zone_name' => $row['zone_name'],
            'total_subzone' => (int)$row['total_subzone'],
            'verified_today' => (int)$row['verified_today'],
            'pending' => (int)$row['pending'],
        ];
    }
}

// =============================================================================
// CONTRACT FUND DISTRIBUTION
// =============================================================================
$fund_distribution = [];
$total_fund_all = 0;
$sql_fund = "
    SELECT z.zone_name, COALESCE(SUM(CAST(ct.fund AS DECIMAL(12,2))), 0) AS total_fund
    FROM zone z
    JOIN subzone sz ON sz.zone_id = z.zone_id
    JOIN contract ct ON ct.sz_id = sz.sz_id
    WHERE ct.c_status = 'Y'
    GROUP BY z.zone_id, z.zone_name
    HAVING total_fund > 0
    ORDER BY total_fund DESC
";
$res_fund = $conn->query($sql_fund);
if ($res_fund && $res_fund->num_rows > 0) {
    while ($row = $res_fund->fetch_assoc()) {
        $fund_distribution[] = [
            'zone_name' => $row['zone_name'],
            'total_fund' => (float)$row['total_fund'],
        ];
        $total_fund_all += (float)$row['total_fund'];
    }
}
$fund_labels_json = json_encode(array_column($fund_distribution, 'zone_name'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
$fund_data_json = json_encode(array_column($fund_distribution, 'total_fund'));

// =============================================================================
// REAL ACTIVITY FEED (last 6 daily_records)
// =============================================================================
$activity_data = [];
$sql_activity = "
    SELECT dr.id, dr.date, dr.verified, dr.created_at,
           c.name as contractor_name, c.cid as contractor_cid,
           z.zone_name, sz.sz_name
    FROM daily_records dr
    LEFT JOIN contract ct ON dr.cid = ct.contract_id
    LEFT JOIN contractor c ON ct.cid = c.cid
    LEFT JOIN subzone sz ON ct.sz_id = sz.sz_id
    LEFT JOIN zone z ON sz.zone_id = z.zone_id
    ORDER BY dr.id DESC
    LIMIT 6
";
$res_activity = $conn->query($sql_activity);
if ($res_activity && $res_activity->num_rows > 0) {
    while ($row = $res_activity->fetch_assoc()) {
        $activity_data[] = $row;
    }
}

// =============================================================================
// REAL NOTIFICATIONS
// =============================================================================
$notif_data = [];
$sql_notif = "SELECT * FROM notifications ORDER BY created_at DESC LIMIT 5";
$res_notif = $conn->query($sql_notif);
if ($res_notif && $res_notif->num_rows > 0) {
    while ($row = $res_notif->fetch_assoc()) {
        $notif_data[] = $row;
    }
}
$unread_notif_count = 0;
foreach ($notif_data as $n) {
    if (!$n['is_read']) $unread_notif_count++;
}

// =============================================================================
// SITE INSTRUCTIONS pending
// =============================================================================
$si_pending = 0;
$sql_si = "SELECT COUNT(*) AS si_pending FROM site_instruction WHERE so_dsign = 'N'";
$res_si = $conn->query($sql_si);
if ($res_si && $row = $res_si->fetch_assoc()) {
    $si_pending = (int)$row['si_pending'];
}

// Malay day name
$malay_days = ['Ahad' => 'Sunday', 'Isnin' => 'Monday', 'Selasa' => 'Tuesday', 'Rabu' => 'Wednesday', 'Khamis' => 'Thursday', 'Jumaat' => 'Friday', 'Sabtu' => 'Saturday'];
$today_eng = date('l');
$today_bm = array_search($today_eng, $malay_days) ?: $today_eng;
$today_date_bm = $today_bm . ', ' . gms_format_date(time(), 'd F Y');

$cycle_months = ['Jan','Feb','Mac','Apr','Mei','Jun','Jul','Ogos','Sep','Okt','Nov','Dis'];
$current_month_num = (int)date('n');

// Notif type icon map
$notif_icons = [
    'priority' => 'fa-solid fa-triangle-exclamation',
    'ready' => 'fa-solid fa-circle-check',
    'welcome' => 'fa-solid fa-hand-wave',
    'general' => 'fa-solid fa-circle-info',
];
?>
<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Green Management System (DBKU)</title>
    <link rel="shortcut icon" href="assets/images/favicon.png" />
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="stylesheet" href="assets/vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="assets/vendors/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="assets/css/vertical-light-layout/style.css">
    <link rel="stylesheet" href="assets/css/modern-dashboard.css?v=<?php echo filemtime(__DIR__ . '/assets/css/modern-dashboard.css'); ?>">
</head>

<body class="gms-dashboard">
    <div class="container-scroller">
        <?php include 'partials/navbar.php'; ?>

        <div class="container-fluid page-body-wrapper">
            <?php include 'partials/sidebar.php'; ?>

            <div class="main-panel">
                <div class="content-wrapper">

                    <!-- ============================================================== -->
                    <!-- HERO COMMAND CENTER BANNER                                      -->
                    <!-- ============================================================== -->
                    <div class="gms-hero-banner gms-fade-in">
                        <div class="row align-items-center g-3">
                            <div class="col-lg-4">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="gms-hero-subtitle" style="color: rgba(255,255,255,0.75);">Ringkasan Operasi</span>
                                </div>
                                <h1 class="gms-hero-title mb-1">Pusat Kawalan Operasi</h1>
                                <p class="gms-hero-subtitle">Bahagian Landskap DBKU</p>
                            </div>
                            <div class="col-lg-3 text-lg-center">
                                <div class="gms-hero-clock" id="gmsLiveClock"><?php echo date('H:i:s'); ?></div>
                                <div class="gms-hero-date"><?php echo htmlspecialchars($today_date_bm); ?></div>
                            </div>
                            <div class="col-lg-5">
                                <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                                    <div class="gms-hero-stat">
                                        <div class="gms-hero-stat-icon"><i class="icon-map"></i></div>
                                        <div>
                                            <div class="gms-hero-stat-label">Zon</div>
                                            <div class="gms-hero-stat-value" data-countup="<?php echo $total_zone; ?>"><?php echo $total_zone; ?></div>
                                        </div>
                                    </div>
                                    <div class="gms-hero-stat">
                                        <div class="gms-hero-stat-icon"><i class="icon-layers"></i></div>
                                        <div>
                                            <div class="gms-hero-stat-label">Sub-Zon</div>
                                            <div class="gms-hero-stat-value" data-countup="<?php echo $total_subzone; ?>"><?php echo $total_subzone; ?></div>
                                        </div>
                                    </div>
                                    <div class="gms-hero-stat">
                                        <div class="gms-hero-stat-icon"><i class="icon-size-fullscreen"></i></div>
                                        <div>
                                            <div class="gms-hero-stat-label">Perimeter</div>
                                            <div class="gms-hero-stat-value"><?php echo number_format($total_perimeter, 0); ?> m</div>
                                        </div>
                                    </div>
                                    <div class="gms-hero-stat">
                                        <div class="gms-hero-stat-icon"><i class="icon-globe"></i></div>
                                        <div>
                                            <div class="gms-hero-stat-label">Keluasan</div>
                                            <div class="gms-hero-stat-value"><?php echo number_format($total_area, 2); ?> ha</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================================== -->
                    <!-- KPI CARDS (4 cards — first card gradient-green featured)      -->
                    <!-- ============================================================== -->
                    <div class="row g-3 mb-4 gms-fade-in gms-fade-in-delay-1">
                        <!-- KPI 1: Total Zone (FEATURED — gradient green) -->
                        <div class="col-sm-6 col-xl-3">
                            <div class="gms-kpi-card kpi-featured">
                                <div>
                                    <div class="gms-kpi-header">
                                        <span class="gms-kpi-label">Jumlah Zon</span>
                                        <div class="gms-kpi-icon">
                                            <i class="icon-map"></i>
                                        </div>
                                    </div>
                                    <div class="gms-kpi-value" data-countup="<?php echo $total_zone; ?>">0</div>
                                </div>
                                <div class="gms-kpi-footer">
                                    <span style="background: rgba(255,255,255,0.12); color: #6ee7b7; padding: 3px 9px; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;">
                                        <i class="fa-solid fa-layer-group"></i> <?php echo $total_subzone; ?> Sub-Zon
                                    </span>
                                    <span>Sub-zon berdaftar</span>
                                </div>
                            </div>
                        </div>

                        <!-- KPI 2: Contractors -->
                        <div class="col-sm-6 col-xl-3">
                            <div class="gms-kpi-card kpi-soft kpi-cyan">
                                <div>
                                    <div class="gms-kpi-header">
                                        <span class="gms-kpi-label">Kontraktor Berdaftar</span>
                                        <div class="gms-kpi-icon">
                                            <i class="icon-people"></i>
                                        </div>
                                    </div>
                                    <div class="gms-kpi-value" data-countup="<?php echo $total_contractor; ?>">0</div>
                                </div>
                                <div class="gms-kpi-footer">
                                    <span class="gms-kpi-badge badge-cyan-soft">
                                        <i class="fa-solid fa-circle-check"></i> Berdaftar
                                    </span>
                                    <span><?php echo $total_contract; ?> Kontrak berdaftar</span>
                                </div>
                            </div>
                        </div>

                        <!-- KPI 3: Total Contracts -->
                        <div class="col-sm-6 col-xl-3">
                            <div class="gms-kpi-card kpi-soft kpi-indigo">
                                <div>
                                    <div class="gms-kpi-header">
                                        <span class="gms-kpi-label">Jumlah Kontrak</span>
                                        <div class="gms-kpi-icon">
                                            <i class="icon-doc"></i>
                                        </div>
                                    </div>
                                    <div class="gms-kpi-value" data-countup="<?php echo $total_contract; ?>">0</div>
                                </div>
                                <div class="gms-kpi-footer">
                                    <span class="gms-kpi-badge badge-indigo-soft">
                                        <i class="fa-solid fa-file-contract"></i> Kontrak Rasmi
                                    </span>
                                    <span>Tender DBKU</span>
                                </div>
                            </div>
                        </div>

                        <!-- KPI 4: Verified Today (consolidated — no more duplicate Phase 3) -->
                        <div class="col-sm-6 col-xl-3">
                            <div class="gms-kpi-card kpi-soft kpi-amber">
                                <div>
                                    <div class="gms-kpi-header">
                                        <span class="gms-kpi-label">Disahkan Hari Ini</span>
                                        <div class="gms-kpi-icon">
                                            <i class="icon-check"></i>
                                        </div>
                                    </div>
                                    <div class="gms-kpi-value" data-countup="<?php echo $verified_today; ?>">0</div>
                                </div>
                                <div class="gms-kpi-footer">
                                    <span class="gms-kpi-badge badge-amber-soft">
                                        <i class="fa-solid fa-rotate"></i> <?php echo htmlspecialchars($session_cycle); ?>
                                    </span>
                                    <span>
                                        <?php echo $current_session; ?>
                                        <?php if ($pending_verification > 0): ?>
                                            <a href="sitedaily_records.php" class="text-danger font-weight-bold ms-1" style="text-decoration: none;">
                                                +<?php echo $pending_verification; ?> tertunda<span class="gms-pulse-alert"></span>
                                            </a>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- KPI END -->

                    <!-- ============================================================== -->
                    <!-- ROW 1: ZONE MAP + NOTIFICATIONS (real data)                     -->
                    <!-- ============================================================== -->
                    <div class="row g-4 mb-4 gms-fade-in gms-fade-in-delay-2">
                        <div class="col-lg-8 stretch-card">
                            <div class="card shadow w-100">
                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
                                        <div>
                                            <h4 class="gms-section-title">
                                                Peta Zon &amp; Lapisan Geospatial
                                            </h4>
                                            <p class="gms-section-subtitle">Visualisasi satelit interaktif sempadan zon dan sub-zon DBKU</p>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-1">
                                                <?php echo $total_subzone; ?> Sub-Zon
                                            </span>
                                        </div>
                                    </div>

                                    <div class="gms-map-container flex-grow-1 mb-3">
                                        <?php include 'maps.php'; ?>
                                    </div>

                                    <div class="d-flex flex-wrap align-items-center justify-content-between pt-2 border-top border-light gap-3">
                                        <div class="d-flex flex-wrap align-items-center gap-4">
                                            <div class="d-flex align-items-center">
                                                <span class="gms-status-dot" style="background-color: #10b981; box-shadow: 0 0 8px rgba(16, 185, 129, 0.4);"></span>
                                                <span class="small text-secondary font-weight-medium">Disahkan</span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <span class="gms-status-dot" style="background-color: #f59e0b; box-shadow: 0 0 8px rgba(245, 158, 11, 0.4);"></span>
                                                <span class="small text-secondary font-weight-medium">Menunggu</span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <span class="gms-status-dot" style="background-color: #94a3b8;"></span>
                                                <span class="small text-secondary font-weight-medium">Tiada Data</span>
                                            </div>
                                        </div>
                                        <span class="text-muted small">
                                            <i class="fa-solid fa-cloud-arrow-up me-1 text-primary"></i> Lapisan KMZ DBKU
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 stretch-card">
                            <div class="card shadow w-100">
                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <h4 class="gms-section-title">
                                            Notifikasi &amp; Amaran
                                        </h4>
                                        <?php if ($unread_notif_count > 0): ?>
                                        <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-1 small font-weight-bold">
                                            <?php echo $unread_notif_count; ?> Belum Dibaca
                                        </span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="notification-list flex-grow-1" style="max-height: 420px; overflow-y: auto; padding-right: 4px;">
                                        <?php if (!empty($notif_data)): ?>
                                            <?php foreach ($notif_data as $n):
                                                $n_type = $n['type'];
                                                $n_icon = $notif_icons[$n_type] ?? 'fa-solid fa-bell';
                                                $unread_class = !$n['is_read'] ? 'unread' : '';
                                            ?>
                                                <div class="gms-notif-item-real <?php echo $unread_class; ?>">
                                                    <div class="gms-notif-type-badge gms-notif-type-<?php echo htmlspecialchars($n_type); ?>">
                                                        <i class="<?php echo $n_icon; ?>"></i>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex align-items-center justify-content-between">
                                                            <h6 class="mb-1 font-weight-bold text-dark" style="font-size: 0.85rem;"><?php echo htmlspecialchars($n['title']); ?></h6>
                                                            <?php if (!$n['is_read']): ?>
                                                                <small class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2">Baru</small>
                                                            <?php endif; ?>
                                                        </div>
                                                        <p class="mb-1 text-secondary small"><?php echo htmlspecialchars($n['description']); ?></p>
                                                        <small class="text-muted" style="font-size: 11px;">
                                                            <i class="icon-clock me-1"></i><?php echo gms_format_date($n['created_at'], 'd M Y, H:i'); ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="text-center text-muted py-5">
                                                <i class="icon-bell" style="font-size: 2rem; opacity: 0.3;"></i>
                                                <p class="mt-2">Tiada notifikasi</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- ROW 1 END -->

                    <!-- ============================================================== -->
                    <!-- ROW 2: VERIFICATION GAUGE + FUND DONUT + CYCLE STRIP           -->
                    <!-- ============================================================== -->
                    <div class="row g-4 mb-4 gms-fade-in gms-fade-in-delay-3">
                        <!-- Verification Gauge (semi-circular, Donezo style) -->
                        <div class="col-lg-4 stretch-card">
                            <div class="card shadow w-100 h-100">
                                <div class="card-body d-flex flex-column">
                                    <h4 class="gms-section-title">Kemajuan Pengesahan</h4>
                                    <p class="gms-section-subtitle">Kadar rekod harian disahkan</p>
                                    <div class="gms-gauge-wrap flex-grow-1 d-flex align-items-center justify-content-center">
                                        <canvas id="verifyGauge" role="img" aria-label="Kadar pengesahan rekod harian: <?php echo $verify_pct; ?> peratus."></canvas>
                                        <div class="gms-gauge-center">
                                            <div class="gms-gauge-value"><?php echo $verify_pct; ?>%</div>
                                            <div class="gms-gauge-label">Disahkan</div>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-around mt-3 pt-2 border-top">
                                        <div class="text-center">
                                            <div class="font-weight-bold text-success" style="font-size: 1.1rem;"><?php echo $verified_total; ?></div>
                                            <small class="text-muted">Disahkan</small>
                                        </div>
                                        <div class="text-center">
                                            <div class="font-weight-bold text-warning" style="font-size: 1.1rem;"><?php echo $pending_verification; ?></div>
                                            <small class="text-muted">Tertunda</small>
                                        </div>
                                        <div class="text-center">
                                            <div class="font-weight-bold text-primary" style="font-size: 1.1rem;"><?php echo $total_dr; ?></div>
                                            <small class="text-muted">Jumlah Rekod</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Donut Chart: Fund Distribution -->
                        <div class="col-lg-4 stretch-card">
                            <div class="card shadow w-100 h-100">
                                <div class="card-body d-flex flex-column">
                                    <h4 class="gms-section-title">Agihan Nilai Kontrak</h4>
                                    <p class="gms-section-subtitle">Peruntukan RM mengikut zon (Aktif)</p>
                                    <?php if (!empty($fund_distribution)): ?>
                                    <div class="gms-donut-wrap" style="height: 240px;">
                                        <canvas id="fundDonutChart" role="img" aria-label="Agihan peruntukan kontrak aktif mengikut zon." aria-describedby="fundDonutSummary"></canvas>
                                        <div class="gms-donut-center">
                                            <div class="gms-donut-center-label">JUMLAH</div>
                                            <div class="gms-donut-center-value">RM <?php echo number_format($total_fund_all / 1000, 1); ?>K</div>
                                        </div>
                                    </div>
                                    <ul id="fundDonutSummary" class="visually-hidden">
                                        <?php foreach ($fund_distribution as $fund_item): ?>
                                            <li><?php echo htmlspecialchars($fund_item['zone_name'], ENT_QUOTES, 'UTF-8'); ?>: RM <?php echo number_format($fund_item['total_fund'], 2); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <?php else: ?>
                                    <div class="text-center text-muted py-5 flex-grow-1 d-flex align-items-center justify-content-center">
                                        <div>
                                            <i class="icon-info mr-2"></i>Tiada data peruntukan aktif
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Cycle Strip -->
                        <div class="col-lg-4 stretch-card">
                            <div class="card shadow w-100 h-100">
                                <div class="card-body d-flex flex-column">
                                    <h4 class="gms-section-title">Kitaran <?php echo date('Y'); ?></h4>
                                    <p class="gms-section-subtitle">Hijau = selesai, Biru = semasa, Kelabu = akan datang</p>
                                    <div class="gms-cycle-strip flex-grow-1" aria-label="Kitaran pemotongan tahunan">
                                        <?php foreach ($cycle_months as $i => $month):
                                            $month_num = $i + 1;
                                            if ($month_num < $current_month_num) {
                                                $cycle_class = 'is-past';
                                            } elseif ($month_num == $current_month_num) {
                                                $cycle_class = 'is-current';
                                            } else {
                                                $cycle_class = 'is-future';
                                            }
                                        ?>
                                            <div class="gms-cycle-block <?php echo $cycle_class; ?>" title="<?php echo $month . ' ' . date('Y'); ?>">
                                                <span><?php echo htmlspecialchars($month); ?></span>
                                                <small><?php echo $month_num; ?></small>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="d-flex flex-wrap align-items-center justify-content-between mt-auto pt-3 border-top gap-2">
                                        <small class="text-muted">
                                            <i class="fa-solid fa-circle-info me-1"></i>
                                            Kitaran 1 (1-15) &amp; 2 (16-akhir) setiap bulan
                                        </small>
                                        <small class="text-muted">
                                            Sesi: <span class="font-weight-bold text-dark"><?php echo $session_num; ?></span> / 24
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- ROW 2 END -->

                    <!-- ============================================================== -->
                    <!-- ROW 3: ZONE STATUS GRID                                        -->
                    <!-- ============================================================== -->
                    <?php if (!empty($zones_data)): ?>
                    <div class="row mb-4 gms-fade-in gms-fade-in-delay-3">
                        <div class="col-12">
                            <div class="card shadow">
                                <div class="card-body">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-2 gap-2">
                                        <div>
                                            <h4 class="gms-section-title">Status Zon DBKU</h4>
                                            <p class="gms-section-subtitle">Ringkasan status kerja harian mengikut zon &mdash; klik untuk melihat butiran</p>
                                        </div>
                                        <div class="d-flex align-items-center gap-3 small text-muted">
                                            <span><span class="gms-status-dot" style="background-color:#10b981;"></span>Disahkan</span>
                                            <span><span class="gms-status-dot" style="background-color:#f59e0b;"></span>Menunggu</span>
                                            <span><span class="gms-status-dot" style="background-color:#94a3b8;"></span>Tiada Data</span>
                                        </div>
                                    </div>
                                    <div class="gms-zone-grid">
                                        <?php foreach ($zones_data as $z):
                                            if ($z['verified_today'] > 0) {
                                                $dot_color = '#10b981';
                                                $status_text = $z['verified_today'] . ' siap';
                                                $status_class = 'text-success';
                                            } elseif ($z['pending'] > 0) {
                                                $dot_color = '#f59e0b';
                                                $status_text = $z['pending'] . ' tertunda';
                                                $status_class = 'text-warning';
                                            } else {
                                                $dot_color = '#94a3b8';
                                                $status_text = 'Tiada data';
                                                $status_class = 'text-muted';
                                            }
                                        ?>
                                            <a class="gms-zone-mini-card" href="zone.php?zone_id=<?php echo $z['zone_id']; ?>" title="Buka Zon <?php echo htmlspecialchars($z['zone_name']); ?>">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <div class="gms-zone-mini-name"><?php echo htmlspecialchars($z['zone_name']); ?></div>
                                                    <span class="gms-status-dot" style="background-color: <?php echo $dot_color; ?>; box-shadow: 0 0 6px <?php echo $dot_color; ?>66;"></span>
                                                </div>
                                                <div class="gms-zone-mini-meta">
                                                    <span class="font-weight-bold text-dark"><?php echo $z['total_subzone']; ?></span> sub-zon
                                                    <span class="mx-1">&bull;</span>
                                                    <span class="<?php echo $status_class; ?>"><?php echo htmlspecialchars($status_text); ?></span>
                                                </div>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <!-- ROW 3 END -->

                    <!-- ============================================================== -->
                    <!-- ROW 4: REAL ACTIVITY FEED (from DB)                            -->
                    <!-- ============================================================== -->
                    <div class="row g-4 mb-4 gms-fade-in gms-fade-in-delay-4">
                        <div class="col-lg-12 stretch-card">
                            <div class="card shadow w-100">
                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <h4 class="gms-section-title">Aktiviti Lapangan Terkini</h4>
                                        <span class="badge bg-light text-muted border rounded-pill small px-2">Langsung dari DB</span>
                                    </div>

                                    <?php if (!empty($activity_data)): ?>
                                    <div class="row g-3 flex-grow-1">
                                        <?php foreach ($activity_data as $act):
                                            $act_name = $act['contractor_name'] ?? 'Tidak diketahui';
                                            $first_letter = mb_strtoupper(mb_substr($act_name, 0, 1));
                                            $is_verified = $act['verified'] === 'Y';
                                            $dot_color = $is_verified ? '#10b981' : '#f59e0b';
                                            $status_label = $is_verified ? 'Disahkan' : 'Menunggu';
                                        ?>
                                        <div class="col-md-6 col-lg-4">
                                            <div class="gms-activity-item">
                                                <div class="gms-activity-avatar"><?php echo $first_letter; ?></div>
                                                <div class="flex-grow-1">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <h6 class="mb-1 font-weight-bold text-dark" style="font-size: 0.85rem;"><?php echo htmlspecialchars($act_name); ?></h6>
                                                        <span class="gms-activity-dot" style="background-color: <?php echo $dot_color; ?>;"></span>
                                                    </div>
                                                    <p class="mb-1 text-muted small">
                                                        <?php echo htmlspecialchars($act['zone_name'] ?? 'N/A'); ?> / <?php echo htmlspecialchars($act['sz_name'] ?? 'N/A'); ?>
                                                    </p>
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <small class="text-secondary" style="font-size: 11px;">
                                                            <i class="icon-calendar me-1"></i><?php echo gms_format_date($act['date'], 'd M Y'); ?>
                                                        </small>
                                                        <span class="badge bg-<?php echo $is_verified ? 'success' : 'warning'; ?> bg-opacity-10 text-<?php echo $is_verified ? 'success' : 'warning'; ?> rounded-pill px-2" style="font-size: 0.65rem;">
                                                            <?php echo $status_label; ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php else: ?>
                                    <div class="text-center text-muted py-5">
                                        <i class="fa-solid fa-clipboard-list" style="font-size: 2rem; opacity: 0.3;"></i>
                                        <p class="mt-2">Tiada aktiviti rekod harian ditemui</p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- ROW 4 END -->

                    <!-- ============================================================== -->
                    <!-- ROW 5: CONTRACTOR MASTER TABLE                                -->
                    <!-- ============================================================== -->
                    <div class="row mb-4 gms-fade-in gms-fade-in-delay-5">
                        <div class="col-12">
                            <div class="card shadow gms-table-card">
                                <div class="card-body p-4">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
                                        <div>
                                            <h4 class="gms-section-title">Senarai &amp; Prestasi Kontraktor</h4>
                                            <p class="gms-section-subtitle">Senarai firma kontraktor pemotongan rumput berserta pecahan kontrak dan zon lantikan.</p>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="gms-table-search">
                                                <i class="icon-magnifier"></i>
                                                <input type="text" id="contractorTableSearch" placeholder="Cari nama kontraktor atau zon..." autocomplete="off">
                                            </div>
                                            <a href="contractor_list.php" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-2 font-weight-semibold">
                                                Lihat Penuh <i class="icon-arrow-right ms-1"></i>
                                            </a>
                                        </div>
                                    </div>

                                    <div class="gms-table-wrapper">
                                        <table class="gms-table" id="contractorDashboardTable">
                                            <thead>
                                                <tr>
                                                    <th>Nama Syarikat / Kontraktor</th>
                                                    <th class="text-center">Jumlah Kontrak</th>
                                                    <th>Nombor Rujukan Kontrak</th>
                                                    <th>Zon Ditugaskan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if ($contractors_res && $contractors_res->num_rows > 0) {
                                                    while ($c_row = $contractors_res->fetch_assoc()) {
                                                        $c_name = htmlspecialchars($c_row['name']);
                                                        $first_letter = mb_strtoupper(mb_substr($c_name, 0, 1));
                                                        $num_contracts = (int)$c_row['jumlah_contract'];
                                                        $contracts_str = $c_row['number_contract'] ?: '-';
                                                        $zones_str = $c_row['zone_names'] ?: '-';

                                                        echo "<tr>";
                                                        echo "<td>
                                                                <div class='d-flex align-items-center'>
                                                                    <div class='gms-contractor-avatar me-3 shadow-sm'>{$first_letter}</div>
                                                                    <div>
                                                                        <div class='font-weight-bold text-dark' style='font-size: 0.9rem;'>{$c_name}</div>
                                                                        <small class='text-muted'>CID: #" . htmlspecialchars($c_row['cid']) . "</small>
                                                                    </div>
                                                                </div>
                                                              </td>";
                                                        echo "<td class='text-center'>
                                                                <span class='gms-badge-contract'>
                                                                    <i class='fa-solid fa-briefcase me-1'></i>{$num_contracts} Kontrak
                                                                </span>
                                                              </td>";
                                                        echo "<td><span class='font-weight-semibold text-secondary' style='font-size: 0.8125rem; font-family: monospace;'>" . htmlspecialchars($contracts_str) . "</span></td>";
                                                        echo "<td>";
                                                        if (!empty($c_row['zone_names'])) {
                                                            $zone_list = explode(', ', $c_row['zone_names']);
                                                            foreach (array_slice($zone_list, 0, 3) as $z) {
                                                                echo "<span class='gms-tag-pill'>" . htmlspecialchars($z) . "</span>";
                                                            }
                                                            if (count($zone_list) > 3) {
                                                                echo "<span class='gms-tag-pill text-primary font-weight-bold'>+" . (count($zone_list) - 3) . " lagi</span>";
                                                            }
                                                        } else {
                                                            echo "<span class='text-muted'>-</span>";
                                                        }
                                                        echo "</td>";
                                                        echo "</tr>";
                                                    }
                                                } else {
                                                    echo "<tr><td colspan='4' class='text-center text-muted py-5'><i class='icon-info me-2'></i>Tiada rekod kontraktor ditemui</td></tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="d-flex flex-wrap align-items-center justify-content-between mt-4 gap-3">
                                        <p class="text-muted mb-0 small" id="tableRecordCount">Memaparkan senarai penuh kontraktor berdaftar DBKU</p>
                                        <a href="contractor_list.php" class="btn btn-sm btn-outline-success rounded-pill px-3">
                                            Urus Kontraktor <i class="icon-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- ROW 5 END -->

                </div>
                <!-- content-wrapper ends -->

                <?php include 'partials/footer.php'; ?>
            </div>
            <!-- main-panel ends -->
        </div>
        <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->

    <!-- ============================================================== -->
    <!-- FAB QUICK ACTIONS + SCROLL TO TOP                              -->
    <!-- ============================================================== -->
    <div class="gms-fab" id="gmsFab">
        <div class="gms-fab-actions" id="gmsFabActions">
            <a href="contract_list.php?action=new" class="gms-fab-action" title="Daftar Kontrak Baharu">
                <i class="fa-solid fa-file-contract"></i>
                <span>Kontrak Baharu</span>
            </a>
            <a href="new_contractor.php" class="gms-fab-action" title="Daftar Kontraktor Baharu">
                <i class="fa-solid fa-user-plus"></i>
                <span>Kontraktor Baharu</span>
            </a>
            <a href="contractor_list.php" class="gms-fab-action" title="Pilih kontraktor untuk rekod harian">
                <i class="fa-solid fa-clipboard-list"></i>
                <span>Rekod Harian</span>
            </a>
        </div>
        <button class="gms-fab-main" id="gmsFabMain" type="button" aria-label="Tindakan Pantas" aria-expanded="false">
            <i class="fa-solid fa-plus"></i>
        </button>
    </div>

    <button class="gms-scroll-top" id="gmsScrollTop" type="button" aria-label="Tatal ke atas">
        <i class="fa-solid fa-arrow-up"></i>
    </button>

    <!-- JS -->
    <script src="assets/vendors/js/vendor.bundle.base.js"></script>
    <script src="assets/vendors/chart.js/chart.umd.js"></script>
    <script src="assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <script src="assets/vendors/moment/moment.min.js"></script>
    <script src="assets/vendors/daterangepicker/daterangepicker.js"></script>
    <script src="assets/js/jquery.cookie.js"></script>
    <script src="assets/js/off-canvas.js"></script>
    <script src="assets/js/hoverable-collapse.js"></script>
    <script src="assets/js/misc.js"></script>
    <script src="assets/js/settings.js"></script>
    <script src="assets/js/todolist.js"></script>

    <script>
    document.addEventListener("DOMContentLoaded", function() {

        // 1. Live Clock
        var clockEl = document.getElementById('gmsLiveClock');
        if (clockEl) {
            function updateClock() {
                var now = new Date();
                var hh = String(now.getHours()).padStart(2, '0');
                var mm = String(now.getMinutes()).padStart(2, '0');
                var ss = String(now.getSeconds()).padStart(2, '0');
                clockEl.textContent = hh + ':' + mm + ':' + ss;
            }
            updateClock();
            setInterval(updateClock, 1000);
        }

        // 2. Count-Up animation
        var countEls = document.querySelectorAll('[data-countup]');
        countEls.forEach(function(el) {
            var target = parseFloat(el.getAttribute('data-countup')) || 0;
            if (target === 0) { el.textContent = '0'; return; }
            var duration = 1200;
            var startTime = null;
            var startVal = 0;
            function step(ts) {
                if (!startTime) startTime = ts;
                var progress = Math.min((ts - startTime) / duration, 1);
                var eased = 1 - Math.pow(1 - progress, 4);
                var current = startVal + (target - startVal) * eased;
                el.textContent = Math.floor(current).toLocaleString();
                if (progress < 1) {
                    requestAnimationFrame(step);
                } else {
                    el.textContent = target.toLocaleString();
                }
            }
            requestAnimationFrame(step);
        });

        // 3. Verification Gauge (semi-circular doughnut)
        var gaugeCanvas = document.getElementById('verifyGauge');
        if (gaugeCanvas && typeof Chart !== 'undefined') {
            var gctx = gaugeCanvas.getContext('2d');
            var pct = <?php echo $verify_pct; ?>;
            new Chart(gctx, {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: [pct, 100 - pct],
                        backgroundColor: ['#10b981', '#e2e8f0'],
                        borderColor: 'transparent',
                        borderWidth: 0,
                        circumference: 180,
                        rotation: 270,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '75%',
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: false }
                    }
                }
            });
        }

        // 4. Donut Chart — Fund Distribution
        var fundCanvas = document.getElementById('fundDonutChart');
        if (fundCanvas && typeof Chart !== 'undefined') {
            var ctx = fundCanvas.getContext('2d');
            var fundLabels = <?php echo $fund_labels_json ?: '[]'; ?>;
            var fundData = <?php echo $fund_data_json ?: '[]'; ?>;
            var palette = [
                '#007442', '#10b981', '#34d399', '#06b6d4',
                '#6366f1', '#f59e0b', '#ef4444', '#0d9488',
                '#65a30d', '#a16207', '#9333ea', '#dc2626'
            ];
            var colors = fundLabels.map(function(_, i) { return palette[i % palette.length]; });
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: fundLabels,
                    datasets: [{
                        data: fundData,
                        backgroundColor: colors,
                        borderColor: '#ffffff',
                        borderWidth: 2,
                        hoverOffset: 12
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 8,
                                boxHeight: 8,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                font: { family: "'Outfit', sans-serif", size: 10, weight: '500' },
                                color: '#475569',
                                padding: 8
                            }
                        },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleFont: { family: "'Outfit', sans-serif", size: 12, weight: 'bold' },
                            bodyFont: { family: "'Outfit', sans-serif", size: 11 },
                            padding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(ctx) {
                                    var val = ctx.parsed;
                                    var total = ctx.dataset.data.reduce(function(a, b) { return a + b; }, 0);
                                    var pct = total > 0 ? Math.round((val / total) * 100) : 0;
                                    return ' RM ' + val.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' (' + pct + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }

        // 5. FAB toggle
        var fabMain = document.getElementById('gmsFabMain');
        var fabActions = document.getElementById('gmsFabActions');
        if (fabMain && fabActions) {
            fabMain.addEventListener('click', function() {
                var isOpen = fabActions.classList.toggle('is-open');
                fabMain.classList.toggle('is-open', isOpen);
                fabMain.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });
            document.addEventListener('click', function(e) {
                if (!document.getElementById('gmsFab').contains(e.target) && fabActions.classList.contains('is-open')) {
                    fabActions.classList.remove('is-open');
                    fabMain.classList.remove('is-open');
                    fabMain.setAttribute('aria-expanded', 'false');
                }
            });
        }

        // 6. Scroll-to-Top
        var scrollTopBtn = document.getElementById('gmsScrollTop');
        if (scrollTopBtn) {
            window.addEventListener('scroll', function() {
                if (window.scrollY > 300) {
                    scrollTopBtn.classList.add('is-visible');
                } else {
                    scrollTopBtn.classList.remove('is-visible');
                }
            });
            scrollTopBtn.addEventListener('click', function() {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }

        // 7. Table search
        var searchInput = document.getElementById('contractorTableSearch');
        var table = document.getElementById('contractorDashboardTable');
        var countLabel = document.getElementById('tableRecordCount');
        if (searchInput && table) {
            searchInput.addEventListener('input', function() {
                var filter = searchInput.value.toLowerCase().trim();
                var rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
                var visibleCount = 0;
                for (var i = 0; i < rows.length; i++) {
                    var text = rows[i].textContent.toLowerCase();
                    if (text.indexOf(filter) > -1) {
                        rows[i].style.display = '';
                        visibleCount++;
                    } else {
                        rows[i].style.display = 'none';
                    }
                }
                if (countLabel) {
                    if (filter === '') {
                        countLabel.textContent = 'Memaparkan senarai penuh kontraktor berdaftar DBKU';
                    } else {
                        countLabel.textContent = 'Ditemui ' + visibleCount + ' kontraktor sepadan dengan carian "' + filter + '"';
                    }
                }
            });
        }

    });
    </script>
</body>

</html>

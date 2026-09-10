<?php
session_start();
$subzone_id = (int)($_GET['szid'] ?? 0);

require 'includes/connection.php';

// Fetch subzone info safely
$stmt = $conn->prepare("SELECT sz.*, z.zone_name, z.zone_id FROM subzone sz LEFT JOIN zone z ON sz.zone_id = z.zone_id WHERE sz.sz_id = ?");
$stmt->bind_param("i", $subzone_id);
$stmt->execute();
$res_sz = $stmt->get_result();
$sz_row = $res_sz->fetch_assoc();

if (!$sz_row) {
    // Subzone not found — redirect
    header("Location: zone.php");
    exit;
}

$subzone_name = $sz_row['sz_name'];
$zone_id = (int)$sz_row['zone_id'];
$zone_name = $sz_row['zone_name'];
$total_perimeter = (float)($sz_row['perimeter'] ?? 0);
$total_area = (float)($sz_row['area'] ?? 0);
$stmt->close();

// Malay day name for hero banner
$malay_days = ['Ahad' => 'Sunday', 'Isnin' => 'Monday', 'Selasa' => 'Tuesday', 'Rabu' => 'Wednesday', 'Khamis' => 'Thursday', 'Jumaat' => 'Friday', 'Sabtu' => 'Saturday'];
$today_eng = date('l');
$today_bm = array_search($today_eng, $malay_days) ?: $today_eng;
$today_date_bm = $today_bm . ', ' . gms_format_date(time(), 'd F Y');
?>
<!DOCTYPE html>
<html lang="ms">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Butiran Sub-Zon — Green Management System (DBKU)</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="assets/vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="assets/vendors/flag-icon-css/css/flag-icons.min.css">
    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.min.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="assets/vendors/font-awesome/css/font-awesome.min.css" />
    <link rel="stylesheet" href="assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="assets/vendors/daterangepicker/daterangepicker.css">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
        <!-- endinject -->
        <!-- Layout styles -->
        <link rel="stylesheet" href="assets/css/vertical-light-layout/style.css">
        <!-- End layout styles -->
            <link rel="shortcut icon" href="assets/images/favicon.png" />
            <link rel="stylesheet" href="assets/css/modern-dashboard.css?v=<?php echo (int)filemtime('assets/css/modern-dashboard.css'); ?>">
            <!-- End layout styles -->
            <style>
                .gms-console {
                    position: relative !important;
                    padding: 1.5rem 1.75rem !important;
                    overflow: hidden !important;
                    color: #ffffff !important;
                    background:
                        radial-gradient(circle at 0% 0%, rgba(16, 185, 129, 0.20) 0%, transparent 45%),
                        radial-gradient(circle at 100% 100%, rgba(0, 116, 66, 0.45) 0%, transparent 50%),
                        linear-gradient(135deg, #0d1713 0%, #0a1f17 50%, #00311c 100%) !important;
                    border: 1px solid rgba(16, 185, 129, 0.20) !important;
                    border-radius: 20px !important;
                    box-shadow: 0 12px 30px -4px rgba(15, 23, 42, 0.25), 0 0 30px rgba(0, 116, 66, 0.15) !important;
                }

                .gms-console-label,
                .gms-console-title,
                .gms-console-sub {
                    color: #ffffff !important;
                }

                .gms-console-label { opacity: 0.92; }
                .gms-console-sub { opacity: 0.72; }

                .gms-console-btn {
                    display: inline-flex !important;
                    align-items: center !important;
                    gap: 4px !important;
                    padding: 0.45rem 1rem !important;
                    color: #ffffff !important;
                    border-radius: 50rem !important;
                    border: none !important;
                    text-decoration: none !important;
                }

                .gms-console-btn-assign {
                    background: linear-gradient(135deg, #10b981 0%, #007442 100%) !important;
                }

                .gms-console-btn-reassign {
                    color: #fbbf24 !important;
                    background: rgba(245, 158, 11, 0.15) !important;
                    border: 1px solid rgba(245, 158, 11, 0.35) !important;
                }

                .gms-console-btn-light {
                    color: #007442 !important;
                    background: #ffffff !important;
                    border: 1px solid rgba(255, 255, 255, 0.6) !important;
                }

                .gms-console-btn-cyan {
                    background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%) !important;
                }

                .gms-hero-stat-name {
                    max-width: 145px;
                    font-size: 0.82rem !important;
                    line-height: 1.2 !important;
                    white-space: normal !important;
                    overflow-wrap: anywhere;
                }

                .gms-area-card {
                    overflow: hidden;
                    border: 1px solid #e2e8f0 !important;
                    border-radius: 18px !important;
                    background: #ffffff !important;
                    box-shadow: 0 10px 25px rgba(15, 23, 42, 0.07) !important;
                }

                .gms-area-card-body {
                    padding: 1.25rem !important;
                }

                .gms-area-header {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 1rem;
                    margin-bottom: 1rem;
                }

                .gms-area-title {
                    display: flex;
                    align-items: center;
                    gap: 0.55rem;
                    margin: 0;
                    color: #172033;
                    font-size: 1rem;
                    font-weight: 750;
                }

                .gms-area-title-mark {
                    display: inline-block;
                    width: 4px;
                    height: 22px;
                    background: linear-gradient(180deg, #10b981 0%, #007442 100%);
                    border-radius: 999px;
                }

                .gms-area-subtitle {
                    margin: 0.3rem 0 0 0.6rem;
                    color: #94a3b8;
                    font-size: 0.74rem;
                }

                .gms-area-add {
                    display: inline-flex !important;
                    align-items: center;
                    justify-content: center;
                    width: 40px;
                    height: 40px;
                    padding: 0 !important;
                    color: #ffffff !important;
                    background: linear-gradient(135deg, #16c79a 0%, #007442 100%) !important;
                    border: 0 !important;
                    border-radius: 50% !important;
                    box-shadow: 0 7px 14px rgba(0, 116, 66, 0.2);
                    font-size: 1rem;
                    transition: all 0.2s ease;
                }

                .gms-area-add:hover {
                    color: #ffffff !important;
                    transform: translateY(-2px) scale(1.04);
                    box-shadow: 0 10px 18px rgba(0, 116, 66, 0.28);
                }

                .gms-area-table-shell {
                    overflow: hidden;
                    border: 1px solid #e2e8f0;
                    border-radius: 13px;
                    background: #ffffff;
                }

                .gms-area-table-scroll {
                    max-height: 400px;
                    overflow-y: auto;
                }

                .gms-area-table {
                    width: 100%;
                    min-width: 460px;
                    margin: 0 !important;
                    table-layout: fixed;
                }

                .gms-area-table thead th {
                    position: sticky;
                    top: 0;
                    z-index: 1;
                    padding: 0.75rem 0.9rem;
                    color: #64748b;
                    background: #f8fafc;
                    border: 0;
                    border-bottom: 1px solid #e2e8f0;
                    font-size: 0.68rem;
                    font-weight: 750;
                    letter-spacing: 0.05em;
                    text-transform: uppercase;
                }

                .gms-area-table tbody td {
                    padding: 0.85rem 0.9rem;
                    color: #334155;
                    background: #ffffff;
                    border: 0;
                    border-bottom: 1px solid #f1f5f9;
                    font-size: 0.8rem;
                    line-height: 1.5;
                    overflow-wrap: anywhere;
                    vertical-align: middle;
                }

                .gms-area-table tbody tr:last-child td {
                    border-bottom: 0;
                }

                .gms-area-table tbody tr:hover td {
                    background: #f8fcfa;
                }

                .gms-area-empty {
                    padding: 2rem 1rem !important;
                    color: #94a3b8 !important;
                    background: #ffffff !important;
                    font-size: 0.8rem !important;
                }

                @media (max-width: 575.98px) {
                    .gms-area-card-body {
                        padding: 1rem !important;
                    }

                    .gms-area-subtitle {
                        display: none;
                    }
                }

                .gms-location-modal {
                    overflow: hidden;
                    border: 0 !important;
                    border-radius: 22px !important;
                    background: #f8fafc !important;
                    box-shadow: 0 24px 70px rgba(15, 23, 42, 0.28) !important;
                }

                .gms-location-modal-header {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    padding: 1.25rem 1.5rem;
                    background: #ffffff;
                    border-bottom: 1px solid #e8eef3;
                }

                .gms-location-modal-heading {
                    display: flex;
                    align-items: center;
                    gap: 0.85rem;
                }

                .gms-location-modal-icon {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    width: 46px;
                    height: 46px;
                    color: #007442;
                    background: linear-gradient(135deg, #e8f8f1 0%, #d5f1e5 100%);
                    border-radius: 14px;
                    font-size: 1.15rem;
                }

                .gms-location-modal-title {
                    margin: 0;
                    color: #172033;
                    font-size: 1.05rem;
                    font-weight: 750;
                }

                .gms-location-modal-subtitle {
                    margin: 0.2rem 0 0;
                    color: #94a3b8;
                    font-size: 0.76rem;
                    font-weight: 500;
                }

                .gms-location-modal-close {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    width: 36px;
                    height: 36px;
                    padding: 0;
                    color: #64748b;
                    background: #f1f5f9;
                    border: 0;
                    border-radius: 50%;
                    transition: all 0.2s ease;
                }

                .gms-location-modal-close:hover {
                    color: #007442;
                    background: #e3f5ed;
                    transform: rotate(90deg);
                }

                .gms-location-modal-body {
                    padding: 1.35rem 1.5rem 1.5rem !important;
                }

                .gms-location-table-shell {
                    overflow: hidden;
                    border: 1px solid #e2e8f0;
                    border-radius: 15px;
                    background: #ffffff;
                }

                .gms-location-table-scroll {
                    max-height: 310px;
                    overflow-y: auto;
                }

                .gms-location-table {
                    width: 100%;
                    margin: 0 !important;
                    table-layout: fixed;
                }

                .gms-location-table thead th {
                    position: sticky;
                    top: 0;
                    z-index: 1;
                    padding: 0.85rem 1rem;
                    color: #64748b;
                    background: #f8fafc;
                    border: 0;
                    border-bottom: 1px solid #e2e8f0;
                    font-size: 0.72rem;
                    font-weight: 750;
                    letter-spacing: 0.04em;
                    text-transform: uppercase;
                }

                .gms-location-table tbody td {
                    padding: 0.85rem 1rem;
                    color: #334155;
                    border: 0;
                    border-bottom: 1px solid #f1f5f9;
                    font-size: 0.82rem;
                    line-height: 1.5;
                    overflow-wrap: anywhere;
                }

                .gms-location-table tbody tr:last-child td {
                    border-bottom: 0;
                }

                .gms-location-empty {
                    color: #94a3b8 !important;
                    background: #ffffff;
                    font-size: 0.82rem !important;
                }

                .gms-location-status {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    gap: 0.35rem;
                    color: #15803d;
                    font-size: 0.74rem;
                    font-weight: 650;
                }

                .gms-location-entry {
                    margin-top: 1rem;
                    padding: 1rem;
                    border: 1px solid #dbeee5;
                    border-radius: 15px;
                    background: linear-gradient(135deg, #f0fbf6 0%, #ffffff 100%);
                }

                .gms-location-entry-title {
                    display: flex;
                    align-items: center;
                    gap: 0.45rem;
                    margin-bottom: 0.8rem;
                    color: #166534;
                    font-size: 0.8rem;
                    font-weight: 750;
                }

                .gms-location-entry .form-label {
                    margin-bottom: 0.4rem;
                    color: #475569;
                    font-size: 0.74rem;
                    font-weight: 700;
                }

                .gms-location-entry .form-control {
                    min-height: 46px;
                    color: #1e293b;
                    background: #ffffff;
                    border: 1px solid #dbe4ec;
                    border-radius: 10px;
                    box-shadow: none;
                    font-size: 0.82rem;
                }

                .gms-location-entry textarea.form-control {
                    min-height: 46px;
                    resize: vertical;
                }

                .gms-location-entry .form-control:focus {
                    border-color: #10b981;
                    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.12);
                }

                .gms-location-save {
                    display: inline-flex !important;
                    align-items: center;
                    justify-content: center;
                    gap: 0.45rem;
                    width: 100%;
                    min-height: 46px;
                    color: #ffffff !important;
                    background: linear-gradient(135deg, #16c79a 0%, #007442 100%) !important;
                    border: 0 !important;
                    border-radius: 10px !important;
                    box-shadow: 0 8px 16px rgba(0, 116, 66, 0.18);
                    font-size: 0.8rem;
                    font-weight: 700;
                    transition: all 0.2s ease;
                }

                .gms-location-save:hover {
                    transform: translateY(-1px);
                    box-shadow: 0 11px 20px rgba(0, 116, 66, 0.25);
                }

                @media (max-width: 767.98px) {
                    .gms-location-modal-header,
                    .gms-location-modal-body {
                        padding-right: 1rem !important;
                        padding-left: 1rem !important;
                    }

                    .gms-location-table-scroll {
                        max-height: 260px;
                    }

                    .gms-location-table {
                        min-width: 620px;
                    }

                    .gms-location-entry .row > div {
                        width: 100%;
                    }
                }

                .action-btn-group .btn-circle-edit { width:34px;height:34px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;background:rgba(0,116,66,0.08);color:#007442;border:1px solid rgba(0,116,66,0.15);font-size:0.8rem;text-decoration:none;transition:all 0.2s ease; }
                .action-btn-group .btn-circle-edit:hover { background:#007442;color:#fff;border-color:#007442;transform:scale(1.08);box-shadow:0 4px 10px rgba(0,116,66,0.2);text-decoration:none; }
                .action-btn-group .btn-circle-delete { width:34px;height:34px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;background:rgba(239,68,68,0.08);color:#ef4444;border:1px solid rgba(239,68,68,0.15);font-size:0.8rem;cursor:pointer;transition:all 0.2s ease; }
                .action-btn-group .btn-circle-delete:hover { background:#ef4444;color:#fff;border-color:#ef4444;transform:scale(1.08);box-shadow:0 4px 10px rgba(239,68,68,0.2); }
            </style>
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

                    <!-- ============================================================== -->
                    <!-- HERO BANNER (Donezo-inspired) -->
                    <!-- ============================================================== -->
                    <div class="gms-hero-banner gms-fade-in">
                        <div class="row align-items-center g-3">
                            <div class="col-lg-4">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="gms-pulse-dot" style="background:#34d399;"></span>
                                    <span class="gms-hero-subtitle" style="color: rgba(255,255,255,0.75);">Sistem Beroperasi</span>
                                    <span class="badge rounded-pill px-2 py-1" style="background: rgba(16, 185, 129, 0.20); color: #34d399; font-size: 0.65rem; font-weight: 700; letter-spacing: 0.05em;">v2.3.0</span>
                                </div>
                                <h1 class="gms-hero-title mb-1">Butiran Sub-Zon</h1>
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
                                            <div class="gms-hero-stat-value gms-hero-stat-name"><?php echo htmlspecialchars($zone_name ?: '-', ENT_QUOTES, 'UTF-8'); ?></div>
                                        </div>
                                    </div>
                                    <div class="gms-hero-stat">
                                        <div class="gms-hero-stat-icon"><i class="icon-layers"></i></div>
                                        <div>
                                            <div class="gms-hero-stat-label">Sub-Zon</div>
                                            <div class="gms-hero-stat-value gms-hero-stat-name"><?php echo htmlspecialchars($subzone_name ?: '-', ENT_QUOTES, 'UTF-8'); ?></div>
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

                        <?php
                        $btn_status = false;
                        $stmt_btn = $conn->prepare("SELECT 1 FROM contract WHERE sz_id = ? AND c_status = 'Y' LIMIT 1");
                        $stmt_btn->bind_param("i", $subzone_id);
                        $stmt_btn->execute();
                        $res_btn = $stmt_btn->get_result();
                        if ($res_btn->num_rows > 0) {
                            $btn_status = true;
                        }
                        $stmt_btn->close();
                        ?>

                        <!-- Contractor Console + DBKU Console (single full-width banner) -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="gms-console">
                                    <div class="row align-items-center g-3">
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <span class="gms-pulse-dot" style="background:#34d399;"></span>
                                                <span class="gms-console-label">Konsol Operasi</span>
                                            </div>
                                            <h3 class="gms-console-title">
                                                <i class="fa-solid fa-wrench me-2 text-success"></i>Konsol Kontraktor
                                            </h3>
                                            <p class="gms-console-sub">Tindakan segera untuk kontraktor &amp; pegawai DBKU</p>
                                            <div class="d-flex gap-2 flex-wrap">
                                                <a href="assign_contractor.php?szid=<?php echo $subzone_id; ?>" class="gms-console-btn gms-console-btn-assign <?php if ($btn_status) { echo 'disabled'; } ?>"><i class="fa-solid fa-bullhorn me-1"></i> Tugas Kontraktor</a>
                                                <a href="reassign_contractor.php?sz_id=<?php echo $subzone_id; ?>" class="gms-console-btn gms-console-btn-reassign"><i class="fa-solid fa-rotate me-1"></i> Tugas Semula</a>
                                                <a href="payments.php?sz_id=<?php echo $subzone_id; ?>" class="gms-console-btn gms-console-btn-light"><i class="fa-solid fa-file-invoice-dollar me-1"></i> Bayaran</a>
                                            </div>
                                        </div>
                                        <div class="col-md-6 border-start border-success border-opacity-25 ps-md-4">
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <span class="gms-pulse-dot" style="background:#06b6d4;"></span>
                                                <span class="gms-console-label">Konsol Pegawai</span>
                                            </div>
                                            <h3 class="gms-console-title">
                                                <i class="fa-solid fa-shield-halved me-2 text-info"></i>Konsol DBKU
                                            </h3>
                                            <p class="gms-console-sub">Tindakan untuk penyelia &amp; pengawas DBKU</p>
                                            <div class="d-flex gap-2 flex-wrap">
                                                <a href="assign_dbku.php?szid=<?php echo $subzone_id; ?>" class="gms-console-btn gms-console-btn-cyan <?php if (!$btn_status) { echo 'disabled'; } ?>"><i class="fa-solid fa-user-plus me-1"></i> Tugas Pegawai</a>
                                                <a href="assign_dbku.php?szid=<?php echo $subzone_id; ?>" class="gms-console-btn gms-console-btn-reassign <?php if (!$btn_status) { echo 'disabled'; } ?>"><i class="fa-solid fa-rotate me-1"></i> Tugas Semula</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <!-- Jika ada contractor row ini akan appear -->


                    <?php
                    // Query untuk mendapatkan maklumat kontraktor yang di-assign ke subzone ini
                    $stmt_ct = $conn->prepare("SELECT * FROM contract WHERE sz_id = ? AND c_status = 'Y' ORDER BY start_date DESC LIMIT 1");
                    $stmt_ct->bind_param("i", $subzone_id);
                    $stmt_ct->execute();
                    $res = $stmt_ct->get_result();
                    if ($res->num_rows > 0) {
                        $row = $res->fetch_assoc();
                        $contract_id = $row['contract_id'];
                        $contractor_id = $row['cid'];
                        $stmt_con = $conn->prepare("SELECT * FROM contractor WHERE cid = ?");
                        $stmt_con->bind_param("i", $contractor_id);
                        $stmt_con->execute();
                        $res1 = $stmt_con->get_result();
                        $row1 = $res1->fetch_assoc();
                        $stmt_con->close();

                        // Fetch DBKU Supervisor & Pengawas info
                        $spv_id = $row['spv_id'];
                        $pgs_id = $row['pgs_id'];

                        $dbku_spv_name = '-';
                        $dbku_spv_email = '-';
                        if ($spv_id) {
                            $stmt_spv = $conn->prepare("SELECT * FROM users WHERE uid = ?");
                            $stmt_spv->bind_param("i", $spv_id);
                            $stmt_spv->execute();
                            $res_spv = $stmt_spv->get_result();
                            if ($res_spv && $res_spv->num_rows > 0) {
                                $row_spv = $res_spv->fetch_assoc();
                                $dbku_spv_name = $row_spv['user_id'];
                                $dbku_spv_email = $row_spv['user_email'];
                            }
                            $stmt_spv->close();
                        }

                        $dbku_pgs_name = '-';
                        $dbku_pgs_email = '-';
                        if ($pgs_id) {
                            $stmt_pgs = $conn->prepare("SELECT * FROM users WHERE uid = ?");
                            $stmt_pgs->bind_param("i", $pgs_id);
                            $stmt_pgs->execute();
                            $res_pgs = $stmt_pgs->get_result();
                            if ($res_pgs && $res_pgs->num_rows > 0) {
                                $row_pgs = $res_pgs->fetch_assoc();
                                $dbku_pgs_name = $row_pgs['user_id'];
                                $dbku_pgs_email = $row_pgs['user_email'];
                            }
                            $stmt_pgs->close();
                        }
                    ?>
                        <div class="row mb-2">
                            <div class="col-md-12 grid-margin stretch-card">
                                <div class="card shadow">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-center mb-4">
                                            <h4 class="gms-section-title"><i class="fa-solid fa-user-tie me-2 text-success"></i>Butiran Kontraktor</h4>
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2">Aktif</span>
                                        </div>

                                        <!-- 3-column balanced layout with visible borders -->
                                        <div class="row g-0">

                                            <!-- COL 1: Contract Info -->
                                            <div class="col-md-4 pe-4 border-end" style="border-color: #e2e8f0 !important;">
                                                <div class="mb-4">
                                                    <p class="text-muted mb-2 small text-uppercase fw-bold" style="font-size:0.65rem;letter-spacing:0.08em;color:#94a3b8;">Kontraktor</p>
                                                    <div class="d-flex align-items-center">
                                                        <div class="gms-contractor-avatar me-3" style="width:44px;height:44px;border-radius:12px;background:rgba(0,116,66,0.08);color:#007442;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;">
                                                            <i class="fa-solid fa-building"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0 fw-bold text-dark" style="font-size:0.95rem;"><?php echo htmlspecialchars($row1['name']); ?></h6>
                                                            <p class="text-muted mb-0" style="font-size:0.75rem;">PIC: <?php echo htmlspecialchars($row1['contact_person']); ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mb-4">
                                                    <p class="text-muted mb-2 small text-uppercase fw-bold" style="font-size:0.65rem;letter-spacing:0.08em;color:#94a3b8;">Butiran Kontrak</p>
                                                    <div class="d-flex align-items-center">
                                                        <div class="gms-contractor-avatar me-3" style="width:44px;height:44px;border-radius:12px;background:rgba(6,182,212,0.08);color:#0891b2;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;">
                                                            <i class="fa-solid fa-file-contract"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0 fw-bold text-dark" style="font-size:0.85rem;font-family:monospace;"><?php echo htmlspecialchars($row['contract_number']); ?></h6>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div>
                                                    <p class="text-muted mb-2 small text-uppercase fw-bold" style="font-size:0.65rem;letter-spacing:0.08em;color:#94a3b8;">Tempoh Kontrak</p>
                                                    <div class="d-flex align-items-center">
                                                        <div class="gms-contractor-avatar me-3" style="width:44px;height:44px;border-radius:12px;background:rgba(99,102,241,0.08);color:#4f46e5;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;">
                                                            <i class="fa-solid fa-calendar-days"></i>
                                                        </div>
                                                        <div>
                                                            <p class="mb-0 fw-bold text-dark" style="font-size:0.85rem;"><?php echo gms_format_date($row['start_date'], 'd M Y'); ?> — <?php echo gms_format_date($row['end_date'], 'd M Y'); ?></p>
                                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1" style="font-size:0.6rem;">Aktif</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- COL 2: Stakeholder Contacts -->
                                            <div class="col-md-4 px-4 border-end" style="border-color: #e2e8f0 !important;">
                                                <p class="text-muted mb-2 small text-uppercase fw-bold" style="font-size:0.65rem;letter-spacing:0.08em;color:#94a3b8;">Juruforeman</p>
                                                <h6 class="mb-1 text-dark fw-bold" style="font-size:0.9rem;">Mohd Hazwan</h6>
                                                <p class="mb-3 text-primary" style="font-size:0.8rem;"><i class="fa-solid fa-phone me-1"></i> 012-3456789</p>
                                                <div class="row mb-3">
                                                    <div class="col-6">
                                                        <p class="text-muted mb-1 small text-uppercase fw-bold" style="font-size:0.65rem;letter-spacing:0.08em;color:#94a3b8;">Penyelia 1</p>
                                                        <h6 class="mb-1 text-dark fw-bold" style="font-size:0.85rem;"><?php echo htmlspecialchars($row['supervisor_1']); ?></h6>
                                                        <p class="mb-0 text-primary" style="font-size:0.75rem;"><i class="fa-solid fa-phone me-1"></i> <?php echo htmlspecialchars($row['supervisor1_contact']); ?></p>
                                                    </div>
                                                    <div class="col-6">
                                                        <p class="text-muted mb-1 small text-uppercase fw-bold" style="font-size:0.65rem;letter-spacing:0.08em;color:#94a3b8;">Penyelia 2</p>
                                                        <h6 class="mb-1 text-dark fw-bold" style="font-size:0.85rem;"><?php echo htmlspecialchars($row['supervisor_2']); ?></h6>
                                                        <p class="mb-0 text-primary" style="font-size:0.75rem;"><i class="fa-solid fa-phone me-1"></i> <?php echo htmlspecialchars($row['supervisor2_contact']); ?></p>
                                                    </div>
                                                </div>
                                                <div class="border-top pt-3" style="border-color:#f1f5f9 !important;">
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <p class="text-muted mb-1 small text-uppercase fw-bold" style="font-size:0.65rem;letter-spacing:0.08em;color:#94a3b8;">Penyelia DBKU</p>
                                                            <h6 class="mb-1 text-dark fw-bold" style="font-size:0.85rem;"><?php echo htmlspecialchars($dbku_spv_name); ?></h6>
                                                            <span class="text-muted" style="font-size: 11px; word-break: break-all;"><i class="fa fa-envelope me-1"></i> <?php echo htmlspecialchars($dbku_spv_email); ?></span>
                                                        </div>
                                                        <div class="col-6">
                                                            <p class="text-muted mb-1 small text-uppercase fw-bold" style="font-size:0.65rem;letter-spacing:0.08em;color:#94a3b8;">Pengawas DBKU</p>
                                                            <h6 class="mb-1 text-dark fw-bold" style="font-size:0.85rem;"><?php echo htmlspecialchars($dbku_pgs_name); ?></h6>
                                                            <span class="text-muted" style="font-size: 11px; word-break: break-all;"><i class="fa fa-envelope me-1"></i> <?php echo htmlspecialchars($dbku_pgs_email); ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- COL 3: Status & Actions -->
                                            <div class="col-md-4 ps-4">
                                                <?php
                                                $current_month = (int)date("m");
                                                $current_day = (int)date("d");
                                                if ($current_day <= 15) {
                                                    $current_session = ($current_month - 1) * 2 + 1;
                                                } else {
                                                    $current_session = ($current_month - 1) * 2 + 2;
                                                }
                                                ?>
                                                <?php
                                                $sql_progress = "SELECT 
                                                    a.contract_id,
                                                    a.sz_id AS target_sz_id, 
                                                    COUNT(f.locId) AS total_tempat,
                                                    (COUNT(f.locId) * 2) AS total_kerja_wajib,
                                                    SUM(CASE WHEN e.gc_progress IN (2, 3) THEN 1 ELSE 0 END) AS gc_siap,
                                                    SUM(CASE WHEN e.dc_progress IN (2, 3) THEN 1 ELSE 0 END) AS dc_siap,
                                                    (
                                                        SUM(CASE WHEN e.gc_progress IN (2, 3) THEN 1 ELSE 0 END) + 
                                                        SUM(CASE WHEN e.dc_progress IN (2, 3) THEN 1 ELSE 0 END)
                                                    ) AS total_kerja_siap,
                                                    ROUND(
                                                        (
                                                            (SUM(CASE WHEN e.gc_progress IN (2, 3) THEN 1 ELSE 0 END) + 
                                                             SUM(CASE WHEN e.dc_progress IN (2, 3) THEN 1 ELSE 0 END)) 
                                                            / NULLIF(COUNT(f.locId) * 2, 0)
                                                        ) * 100, 
                                                        2
                                                    ) AS progress_sesi_peratusan,
                                                    IFNULL(MAX(g.gc_workers), 0) AS max_gc_workers,
                                                    IFNULL(MAX(g.dc_workers), 0) AS max_dc_workers,
                                                    (IFNULL(MAX(g.gc_workers), 0) + IFNULL(MAX(g.dc_workers), 0)) AS total_max_workers
                                                FROM contract AS a 
                                                JOIN contractor AS b ON b.cid = a.cid 
                                                LEFT JOIN area_daily_records AS e ON e.cid = a.contract_id 
                                                LEFT JOIN contract_location AS f ON f.locId = e.area_id 
                                                LEFT JOIN daily_records AS g ON g.id = e.dr_id 
                                                WHERE a.sz_id = ?
                                                GROUP BY a.contract_id";
                                                
                                                $stmt_progress = $conn->prepare($sql_progress);
                                                $stmt_progress->bind_param("i", $subzone_id);
                                                $stmt_progress->execute();
                                                $res_progress = $stmt_progress->get_result();
                                                $progress_percent = 0;
                                                $max_workers = 0;
                                                if ($res_progress && $res_progress->num_rows > 0) {
                                                    $row_progress = $res_progress->fetch_assoc();
                                                    $progress_percent = $row_progress['progress_sesi_peratusan'] ?? 0;
                                                    $max_workers = $row_progress['total_max_workers'] ?? 0;
                                                }
                                                ?>
                                                <div class="d-flex justify-content-around text-center mb-3">
                                                    <div>
                                                        <div class="gms-contractor-avatar mx-auto mb-1" style="width:36px;height:36px;border-radius:10px;background:rgba(16,185,129,0.08);color:#059669;display:flex;align-items:center;justify-content:center;font-size:0.9rem;">
                                                            <i class="fa-solid fa-people-group"></i>
                                                        </div>
                                                        <p class="text-muted mb-0 small" style="font-size:0.7rem;">Pekerja</p>
                                                        <p class="mb-0 fw-bold text-dark" style="font-size:0.95rem;"><?php echo $max_workers; ?></p>
                                                    </div>
                                                    <div>
                                                        <div class="gms-contractor-avatar mx-auto mb-1" style="width:36px;height:36px;border-radius:10px;background:rgba(245,158,11,0.08);color:#d97706;display:flex;align-items:center;justify-content:center;font-size:0.9rem;">
                                                            <i class="fa-solid fa-flag-checkered"></i>
                                                        </div>
                                                        <p class="text-muted mb-0 small" style="font-size:0.7rem;">Jangka Siap</p>
                                                        <p class="mb-0 fw-bold text-dark" style="font-size:0.8rem;"><?php
                                                            if (date("d") <= 15) {
                                                                echo gms_format_date(date("Y-m-15"), 'd M Y');
                                                            } else {
                                                                echo gms_format_date(date("Y-m-t"), 'd M Y');
                                                            }
                                                        ?></p>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <p class="text-muted mb-0 small text-uppercase fw-bold" style="font-size:0.7rem;letter-spacing:0.05em;">Status</p>
                                                        <p class="text-success mb-0 fw-bold" style="font-size:0.8rem;"><?php echo $progress_percent; ?>% Siap</p>
                                                    </div>
                                                    <div class="progress" style="height: 8px; border-radius: 4px; background: #e2e8f0;">
                                                        <div class="progress-bar" role="progressbar" style="width: <?php echo $progress_percent; ?>%; background: linear-gradient(90deg, #10b981, #007442); border-radius: 4px;" aria-valuenow="<?php echo $progress_percent; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                </div>
                                                <div class="d-grid gap-2">
                                                    <a href="contractor_details.php?id=<?php echo $row1['cid']; ?>" class="btn btn-outline-primary btn-sm rounded-pill py-2 font-weight-semibold">
                                                        <i class="fa-solid fa-circle-info me-1"></i> Butiran Kontraktor
                                                    </a>
                                                    <a href="daily_details.php?id=<?php echo $contract_id; ?>" class="btn btn-success btn-sm rounded-pill py-2 font-weight-semibold">
                                                        <i class="fa-solid fa-clipboard-list me-1"></i> Buku Harian
                                                    </a>
                                                    <a href="#" class="btn btn-outline-secondary btn-sm rounded-pill py-2 font-weight-semibold">
                                                        <i class="fa-solid fa-phone me-1"></i> Hubungi
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php //else : 
                    }
                    ?>
                    <!-- <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="card shadow border-0 bg-light">
                                    <div class="card-body text-center py-4">
                                        <i class="icon-info text-warning" style="font-size: 2rem;"></i>
                                        <p class="mt-2 mb-0 text-muted">Tiada kontraktor yang di-assign untuk zon ini.</p>
                                        <small>Sila gunakan <b>Konsol Kontraktor</b> di atas untuk melantik kontraktor.</small>
                                    </div>
                                </div>
                            </div>
                        </div> -->
                    <?php //endif; 
                    ?>


                    <!-- Jika ada contractor yang terassign -->

                    <div class="row">
                        <div class="col-md-6 grid-margin stretch-card">
                            <div class="card shadow">
                                <div class="card-body">
                                    <div class="d-sm-flex align-items-center mb-3">
                                        <h4 class="gms-section-title">Peta</h4>

                                    </div>

                                    <div id="map" style="width: 100%; height: 50vh;"></div>

                                    <div class="d-flex flex-wrap mt-3">
                                        <div class="d-flex align-items-center me-4">
                                            <span class="gms-status-dot" style="background-color: #10b981;"></span>
                                            <p class="mb-0 ms-2 text-muted small">Zon Selesai</p>
                                        </div>
                                        <div class="d-flex align-items-center me-4">
                                            <span class="gms-status-dot" style="background-color: #f59e0b;"></span>
                                            <p class="mb-0 ms-2 text-muted small">Sedang Berjalan</p>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <span class="gms-status-dot" style="background-color: #ef4444;"></span>
                                            <p class="mb-0 ms-2 text-muted small">Kawasan Bermasalah</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 grid-margin stretch-card">
                            <div class="card shadow gms-area-card">
                                <div class="card-body gms-area-card-body">
                                    <div class="gms-area-header">
                                        <div>
                                            <h4 class="gms-area-title"><span class="gms-area-title-mark"></span>Kawasan</h4>
                                            <p class="gms-area-subtitle">Senarai kawasan dan lokasi penyelenggaraan.</p>
                                        </div>
                                        <button type="button" class="btn gms-area-add" data-bs-toggle="modal" data-bs-target="#addLocationModal" title="Tambah Lokasi" aria-label="Tambah Lokasi">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>

                                    <div class="gms-area-table-shell">
                                        <div class="table-responsive gms-area-table-scroll">
                                        <table class="table gms-area-table">
                                            <thead>
                                                <tr>
                                                    <th style="width: 25%;">Kawasan</th>
                                                    <th style="width: 60%;">Lokasi</th>
                                                    <th style="width: 15%; text-align: center;">Tindakan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                // Get primary key column dynamically
                                                $pk_col = 'id';
                                                $res_pk = $conn->query("SHOW KEYS FROM contract_location WHERE Key_name = 'PRIMARY'");
                                                if ($res_pk && $res_pk->num_rows > 0) {
                                                    $pk_row = $res_pk->fetch_assoc();
                                                    $pk_col = $pk_row['Column_name'];
                                                }

                                                $stmt_area = $conn->prepare("SELECT * FROM contract_location WHERE sz_id = ?");
                                                $stmt_area->bind_param("i", $subzone_id);
                                                $stmt_area->execute();
                                                $res_area = $stmt_area->get_result();
                                                
                                                if ($res_area && $res_area->num_rows > 0) {
                                                    while ($row_area = $res_area->fetch_assoc()) {
                                                        $loc_id = $row_area[$pk_col] ?? '';
                                                        ?>
                                                        <tr>
                                                            <td><?php echo isset($row_area['area']) ? htmlspecialchars($row_area['area']) : '-'; ?></td>
                                                            <td><?php echo isset($row_area['location']) ? htmlspecialchars($row_area['location']) : '-'; ?></td>
                                                            <td class="text-center">
                                                                <div class="action-btn-group">
                                                                    <button type="button" class="btn-circle-edit" 
                                                                        data-id="<?php echo $loc_id; ?>" 
                                                                        data-area="<?php echo htmlspecialchars($row_area['area'] ?? ''); ?>" 
                                                                        data-location="<?php echo htmlspecialchars($row_area['location'] ?? ''); ?>" 
                                                                        onclick="openEditLocationModal(this)" title="Kemaskini">
                                                                        <i class="fa fa-pencil"></i>
                                                                    </button>
                                                                    <button type="button" class="btn-circle-delete" 
                                                                        data-id="<?php echo $loc_id; ?>" 
                                                                        onclick="deleteLocation(this)" title="Padam">
                                                                        <i class="fa fa-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <?php
                                                    }
                                                } else {
                                                    echo '<tr><td colspan="3" class="gms-area-empty text-center">Tiada rekod dijumpai</td></tr>';
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                </div>
                </div>
                <!-- content-wrapper ends -->

                <?php include 'partials/footer.php'; ?>
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
        <script src="assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
        <script src="assets/vendors/moment/moment.min.js"></script>
        <script src="assets/vendors/daterangepicker/daterangepicker.js"></script>
        <script src="assets/js/jquery.cookie.js"></script>
        <!-- End plugin js for this page -->
        <!-- inject:js -->
        <script src="assets/js/off-canvas.js"></script>
        <script src="assets/js/hoverable-collapse.js"></script>
        <script src="assets/js/misc.js"></script>
        <script src="assets/js/settings.js"></script>
        <script src="assets/js/todolist.js"></script>
        <!-- endinject -->
        <!-- Custom js for this page -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <!-- End custom js for this page -->


        <?php
        // Check if there is a status message in the session
        if (isset($_SESSION['status'])) {
            $status = $_SESSION['status'];   // 'success' or 'error'
            $message = $_SESSION['message']; // Error or success message

            // Clear session message after use
            unset($_SESSION['status']);
            unset($_SESSION['message']);

            if ($status == 'success') {
                echo '<script>
                            document.addEventListener("DOMContentLoaded", function() {
                                Swal.fire({
                                    title: "' . $message . '",
                                    text: "",
                                    icon: "success",
                                });
                            });
                        </script>';
            } else {
                echo '<script>
                            document.addEventListener("DOMContentLoaded", function() {
                                Swal.fire({
                                    title: "' . $message . '",
                                    icon: "error",
                                });
                            });
                        </script>';
            }
        }
        ?>
        <script>
            function initMap() {

                var map = new google.maps.Map(document.getElementById('map'), {
                    center: {
                        lat: 1.585367,
                        lng: 110.334509
                    },
                    zoom: 17,
                    mapTypeId: google.maps.MapTypeId.HYBRID
                });

                <?php
                $stmt_kmz = $conn->prepare("SELECT * FROM kmz_file WHERE sz_id = ?");
                $stmt_kmz->bind_param("i", $subzone_id);
                $stmt_kmz->execute();
                $res = $stmt_kmz->get_result();
                if ($res->num_rows > 0) {
                    $row = $res->fetch_assoc();
                    $url = $row['url'];
                } else {
                    $url = '';
                }


                ?>

                var kmzLayer = new google.maps.KmlLayer({
                    url: '<?php echo $url ?>',
                    map: map,
                    zoom: 8,
                    preserveViewport: false,
                });
            }
        </script>

        <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAM9KnXl4SMmy68M2syalpDyMfZeNItPjk&callback=initMap"></script>

    <!-- Add Location Modal -->
    <div class="modal fade" id="addLocationModal" tabindex="-1" aria-labelledby="addLocationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content gms-location-modal">
                <div class="gms-location-modal-header">
                    <div class="gms-location-modal-heading">
                        <span class="gms-location-modal-icon"><i class="fa fa-map-marker-alt"></i></span>
                        <div>
                            <h5 class="gms-location-modal-title" id="addLocationModalLabel">Tambah Lokasi</h5>
                            <p class="gms-location-modal-subtitle">Tambah kawasan dan lokasi untuk sub-zon ini.</p>
                        </div>
                    </div>
                    <button type="button" class="gms-location-modal-close" data-bs-dismiss="modal" aria-label="Tutup">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
                <div class="modal-body gms-location-modal-body">
                    <form id="formAddLocation">
                        <input type="hidden" name="sz_id" id="sz_id" value="<?php echo $subzone_id; ?>">
                        <div class="gms-location-table-shell">
                            <div class="table-responsive gms-location-table-scroll">
                            <table class="table align-middle gms-location-table">
                                <thead>
                                    <tr>
                                        <th style="width: 25%;">Kawasan</th>
                                        <th style="width: 60%;">Lokasi</th>
                                        <th style="width: 15%; text-align: center;">Tindakan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt_modal = $conn->prepare("SELECT * FROM contract_location WHERE sz_id = ? ORDER BY area ASC");
                                    $stmt_modal->bind_param("i", $subzone_id);
                                    $stmt_modal->execute();
                                    $res_modal_area = $stmt_modal->get_result();
                                    if ($res_modal_area && $res_modal_area->num_rows > 0) {
                                        while ($row_modal = $res_modal_area->fetch_assoc()) {
                                            echo '<tr>';
                                            echo '<td>' . htmlspecialchars($row_modal['area']) . '</td>';
                                            echo '<td>' . htmlspecialchars($row_modal['location']) . '</td>';
                                            echo '<td class="text-center"><span class="gms-location-status"><i class="fa fa-check-circle"></i> Disimpan</span></td>';
                                            echo '</tr>';
                                        }
                                    } else {
                                        echo "<tr><td colspan='3' class='gms-location-empty text-center py-4'>Tiada rekod lokasi dijumpai. Sila tambah di bawah.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                            </div>
                        </div>

                        <div class="gms-location-entry">
                            <div class="gms-location-entry-title"><i class="fa fa-plus-circle"></i> Lokasi Baharu</div>
                            <div class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label for="area" class="form-label">Kawasan</label>
                                    <input type="text" class="form-control" id="area" name="area" placeholder="Contoh: Taman A" required>
                                </div>
                                <div class="col-md-7">
                                    <label for="location" class="form-label">Lokasi</label>
                                    <textarea class="form-control" id="location" name="location" rows="1" placeholder="Masukkan baris demi baris jika ada" required></textarea>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn gms-location-save" id="btnSaveLocation">
                                        <i class="fa fa-plus"></i><span>Simpan</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('formAddLocation').addEventListener('submit', function(e) {
            e.preventDefault();
            let btn = document.getElementById('btnSaveLocation');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span><span>Menyimpan...</span>';

            let formData = new FormData(this);

            fetch('add_locations.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-plus"></i><span>Simpan</span>';
                
                if (data.status === 'success') {
                    Swal.fire({
                        title: 'Berjaya',
                        text: data.message,
                        icon: 'success'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Ralat',
                        text: data.message,
                        icon: 'error'
                    });
                }
            })
            .catch(error => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-plus"></i><span>Simpan</span>';
                Swal.fire({
                    title: 'Ralat',
                    text: 'Terdapat ralat pada pelayan.',
                    icon: 'error'
                });
            });
        });

        // Edit Location Logic
        function openEditLocationModal(btn) {
            let id = btn.getAttribute('data-id');
            let area = btn.getAttribute('data-area');
            let location = btn.getAttribute('data-location');
            
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_area').value = area;
            document.getElementById('edit_location').value = location;
            
            var editModal = new bootstrap.Modal(document.getElementById('editLocationModal'));
            editModal.show();
        }

        document.getElementById('formEditLocation').addEventListener('submit', function(e) {
            e.preventDefault();
            let btn = document.getElementById('btnUpdateLocation');
            btn.disabled = true;
            btn.innerText = 'Mengemaskini...';

            let formData = new FormData(this);

            fetch('update_location.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                btn.disabled = false;
                btn.innerText = 'Kemaskini';
                
                if (data.status === 'success') {
                    Swal.fire({
                        title: 'Berjaya',
                        text: data.message,
                        icon: 'success'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Ralat',
                        text: data.message,
                        icon: 'error'
                    });
                }
            })
            .catch(error => {
                btn.disabled = false;
                btn.innerText = 'Kemaskini';
                Swal.fire({
                    title: 'Ralat',
                    text: 'Terdapat ralat pada pelayan.',
                    icon: 'error'
                });
            });
        });

        // Delete Location Logic
        function deleteLocation(btn) {
            let id = btn.getAttribute('data-id');
            
            Swal.fire({
                title: 'Adakah anda pasti?',
                text: "Data yang dipadam tidak boleh dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, padam!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    let formData = new FormData();
                    formData.append('id', id);

                    fetch('delete_location.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            Swal.fire(
                                'Terpadam!',
                                data.message,
                                'success'
                            ).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire(
                                'Ralat!',
                                data.message,
                                'error'
                            );
                        }
                    })
                    .catch(error => {
                        Swal.fire(
                            'Ralat!',
                            'Terdapat ralat pada pelayan.',
                            'error'
                        );
                    });
                }
            });
        }
    </script>

    <!-- Edit Location Modal -->
    <div class="modal fade" id="editLocationModal" tabindex="-1" aria-labelledby="editLocationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editLocationModalLabel">Kemaskini Lokasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditLocation">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-3">
                            <label for="edit_area" class="form-label">Kawasan</label>
                            <input type="text" class="form-control" id="edit_area" name="area" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_location" class="form-label">Lokasi</label>
                            <textarea class="form-control" id="edit_location" name="location" rows="3" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" id="btnUpdateLocation">Kemaskini</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php
session_start();
$subzone_id = $_GET['szid'];

require 'includes/connection.php';

//Total sub-zone
$sql1 = "SELECT COUNT(*) AS total_subzone FROM subzone WHERE sz_id = '$subzone_id'";
$res1 = $conn->query($sql1);
$row1 = $res1->fetch_assoc();
$total_subzone = $row1['total_subzone'];

// Total Perimeter(m) by zone
$sql2 = "SELECT SUM(perimeter) AS total_perimeter FROM subzone WHERE sz_id = '$subzone_id'";
$res2 = $conn->query($sql2);
$row2 = $res2->fetch_assoc();
$total_perimeter = $row2['total_perimeter'] ?? 0;

// Total Area(ha) by zone
$sql3 = "SELECT SUM(area) AS total_area FROM subzone WHERE sz_id = '$subzone_id'";
$res3 = $conn->query($sql3);
$row3 = $res3->fetch_assoc();
$total_area = $row3['total_area'] ?? 0;
?>
<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Lantik Kontraktor — Green Management System (DBKU)</title>
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
    <link rel="stylesheet" href="assets/css/modern-dashboard.css">
    <style>
        /* Donezo-inspired form page scoped styles (use theme CSS variables) */
        .gms-form-card .card-body { padding: 2rem !important; }

        .gms-parameter-grid {
            align-items: flex-start;
        }

        .gms-parameter-grid > [class*="col-"] {
            min-width: 0;
        }

        .gms-parameter-grid .gms-input-group,
        .gms-parameter-grid .gms-form-control {
            min-width: 0;
            width: 100%;
        }

        @media (max-width: 991.98px) {
            .gms-form-card .card-body { padding: 1.5rem !important; }
        }

        @media (max-width: 575.98px) {
            .gms-form-card .card-body { padding: 1.25rem !important; }
        }

        .gms-form-label {
            display: block;
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--gms-text-secondary);
            letter-spacing: 0.02em;
            margin-bottom: 0.5rem;
        }
        .gms-form-label .text-danger { color: #ef4444; }

        .gms-input-group {
            position: relative;
            display: flex;
            align-items: stretch;
        }
        .gms-input-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 46px;
            flex-shrink: 0;
            background: rgba(0, 116, 66, 0.06);
            color: var(--gms-primary);
            border: 1px solid var(--gms-border);
            border-right: none;
            border-radius: 12px 0 0 12px;
            font-size: 1rem;
            transition: var(--gms-transition);
        }
        .gms-input-group:focus-within .gms-input-icon {
            background: rgba(0, 116, 66, 0.12);
            color: #059669;
            border-color: var(--gms-border-focus);
        }
        .gms-form-control {
            flex: 1 1 auto;
            padding: 12px 16px;
            border: 1px solid var(--gms-border);
            border-radius: 0 12px 12px 0;
            background: #f8fafc;
            color: var(--gms-text-primary);
            font-size: 0.9rem;
            font-family: inherit;
            outline: none;
            transition: var(--gms-transition);
        }
        .gms-form-control::placeholder { color: var(--gms-text-muted); }
        .gms-form-control:focus {
            background: #ffffff;
            border-color: var(--gms-border-focus);
            box-shadow: 0 0 0 3px rgba(0, 116, 66, 0.12);
        }
        textarea.gms-form-control {
            border-radius: 0 12px 12px 12px;
        }
        select.gms-form-control {
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 14px center;
            background-size: 18px;
            padding-right: 40px;
        }

        .gms-btn-primary {
            padding: 12px 28px;
            border-radius: var(--gms-pill-radius);
            font-weight: 600;
            font-size: 0.875rem;
            background: linear-gradient(135deg, #10b981 0%, #007442 100%);
            color: #ffffff;
            border: none;
            box-shadow: 0 6px 18px rgba(0, 116, 66, 0.3);
            transition: var(--gms-transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .gms-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(0, 116, 66, 0.4);
            color: #ffffff;
        }
        .gms-btn-outline {
            padding: 12px 28px;
            border-radius: var(--gms-pill-radius);
            font-weight: 600;
            font-size: 0.875rem;
            background: #ffffff;
            color: var(--gms-primary);
            border: 1px solid rgba(0, 116, 66, 0.3);
            transition: var(--gms-transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        .gms-btn-outline:hover {
            background: rgba(0, 116, 66, 0.06);
            border-color: rgba(0, 116, 66, 0.5);
            transform: translateY(-2px);
            text-decoration: none;
        }
        .gms-btn-light {
            padding: 12px 28px;
            border-radius: var(--gms-pill-radius);
            font-weight: 600;
            font-size: 0.875rem;
            background: #f1f5f9;
            color: var(--gms-text-secondary);
            border: 1px solid var(--gms-border);
            transition: var(--gms-transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            cursor: pointer;
        }
        .gms-btn-light:hover {
            background: #e2e8f0;
            color: var(--gms-text-primary);
            transform: translateY(-2px);
            text-decoration: none;
        }

        /* Profile summary tiles (dynamic contractor details) */
        .gms-profile-tile {
            background: #ffffff;
            border: 1px solid var(--gms-border);
            border-radius: 14px;
            padding: 1.1rem 1.25rem;
            height: 100%;
            display: flex;
            align-items: center;
            gap: 14px;
            transition: var(--gms-transition);
        }
        .gms-profile-tile:hover {
            box-shadow: var(--gms-shadow-sm);
            border-color: rgba(203, 213, 225, 0.9);
        }
        .gms-profile-tile-icon {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
            background: rgba(0, 116, 66, 0.08);
            color: var(--gms-primary);
        }
        .gms-profile-tile-label {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--gms-text-muted);
            margin-bottom: 2px;
        }
        .gms-profile-tile-value {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--gms-text-primary);
            word-break: break-word;
            line-height: 1.3;
        }
    </style>
</head>

<body>
    <div class="container-scroller">
        <?php include 'partials/navbar.php'; ?>

        <div class="container-fluid page-body-wrapper">
            <?php include 'partials/sidebar.php'; ?>

            <div class="main-panel" style="height: 100%;">
                <div class="content-wrapper">

                    <!-- ============================================================== -->
                    <!-- PAGE HEADER BANNER (Donezo-inspired hero)                       -->
                    <!-- ============================================================== -->
                    <?php
                    $sql = "SELECT * FROM subzone AS a JOIN zone AS b ON b.zone_id = a.zone_id WHERE a.sz_id = '$subzone_id'";
                    $res = $conn->query($sql);
                    $row = $res->fetch_assoc();
                    $zone_name = $row['zone_name'];
                    $subzone_name = $row['sz_name'];
                    $zone_id = $row['zone_id'];
                    ?>
                    <div class="gms-hero-banner gms-fade-in" style="padding: 1.5rem 2rem;">
                        <div class="d-flex align-items-center justify-content-between flex-wrap g-3">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="gms-pulse-dot" style="background:#34d399;"></span>
                                    <span class="gms-hero-subtitle" style="color: rgba(255,255,255,0.75);">Modul Lantikan Kontraktor</span>
                                </div>
                                <h1 class="gms-hero-title mb-1">Lantik Kontraktor</h1>
                                <p class="gms-hero-subtitle">Konfigurasi butiran kontraktor &amp; tetapkan kerja bagi <?php echo htmlspecialchars($subzone_name); ?> (Zon <?php echo htmlspecialchars($zone_name); ?>)</p>
                            </div>
                            <div class="d-flex flex-wrap align-items-center gap-2 justify-content-lg-end">
                                <div class="gms-hero-stat">
                                    <div class="gms-hero-stat-icon"><i class="icon-size-fullscreen"></i></div>
                                    <div>
                                        <div class="gms-hero-stat-label">Perimeter</div>
                                        <div class="gms-hero-stat-value"><?php echo number_format($total_perimeter); ?> m</div>
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

                    <!-- ============================================================== -->
                    <!-- KPI METRIC CARDS (Donezo-inspired)                              -->
                    <!-- ============================================================== -->
                    <div class="row g-3 mb-4 gms-fade-in gms-fade-in-delay-1">
                        <!-- KPI 1: Sub-Zone Perimeter (featured gradient green) -->
                        <div class="col-sm-6 col-xl-6">
                            <div class="gms-kpi-card kpi-featured">
                                <div>
                                    <div class="gms-kpi-header">
                                        <span class="gms-kpi-label">Perimeter Sub-Zon</span>
                                        <div class="gms-kpi-icon">
                                            <i class="icon-size-fullscreen"></i>
                                        </div>
                                    </div>
                                    <div class="gms-kpi-value" data-countup="<?php echo (float)$total_perimeter; ?>">0</div>
                                </div>
                                <div class="gms-kpi-footer">
                                    <span style="background: rgba(255,255,255,0.12); color: #6ee7b7; padding: 3px 9px; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;">
                                        <i class="fa-solid fa-ruler"></i> Ukuran Sempadan
                                    </span>
                                    <span>meter (m)</span>
                                </div>
                            </div>
                        </div>

                        <!-- KPI 2: Sub-Zone Area -->
                        <div class="col-sm-6 col-xl-6">
                            <div class="gms-kpi-card kpi-soft kpi-amber">
                                <div>
                                    <div class="gms-kpi-header">
                                        <span class="gms-kpi-label">Keluasan Sub-Zon</span>
                                        <div class="gms-kpi-icon">
                                            <i class="icon-pie-chart"></i>
                                        </div>
                                    </div>
                                    <div class="gms-kpi-value"><?php echo number_format($total_area, 2); ?> <small style="font-size: 0.9rem; font-weight: 600;">ha</small></div>
                                </div>
                                <div class="gms-kpi-footer">
                                    <span class="gms-kpi-badge badge-amber-soft">
                                        <i class="fa-solid fa-chart-area"></i> Kawasan Operasi
                                    </span>
                                    <span>hektar (ha)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- KPI END -->

                    <!-- ============================================================== -->
                    <!-- STEP 1: CHOOSE CONTRACTOR SELECT                               -->
                    <!-- ============================================================== -->
                    <div class="row mb-4 gms-fade-in gms-fade-in-delay-2">
                        <div class="col-12">
                            <div class="card shadow gms-form-card">
                                <div class="card-body">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 pb-3 border-bottom gap-3">
                                        <div class="d-flex align-items-center">
                                            <div class="gms-kpi-icon me-3" style="background: rgba(0, 116, 66, 0.08); color: var(--gms-primary);">
                                                <i class="fa-solid fa-people-group"></i>
                                            </div>
                                            <div>
                                                <h4 class="gms-section-title">Pilih Kontraktor</h4>
                                                <p class="gms-section-subtitle mb-0">Pilih profil kontraktor daripada senarai berdaftar DBKU.</p>
                                            </div>
                                        </div>
                                        <a href="new_contractor.php" class="gms-btn-outline">
                                            <i class="fa-solid fa-user-plus"></i> Kontraktor Baharu
                                        </a>
                                    </div>
                                    <div class="row g-4 align-items-end">
                                        <div class="col-md-8">
                                            <label for="contractorSelect" class="gms-form-label">Profil Kontraktor <span class="text-danger">*</span></label>
                                            <div class="gms-input-group">
                                                <span class="gms-input-icon"><i class="fa-solid fa-building"></i></span>
                                                <select name="contractor_id" id="contractorSelect" class="gms-form-control" required>
                                                    <option value="" selected>-- Pilih Kontraktor --</option>
                                                    <?php
                                                    $sql = "SELECT * FROM contractor ";
                                                    $res1 = $conn->query($sql);
                                                    if ($res1->num_rows > 0) {
                                                        while ($row1 = $res1->fetch_assoc()) {
                                                    ?>
                                                            <option value="<?php echo $row1['cid'] ?>"><?php echo htmlspecialchars($row1['name']) ?></option>
                                                    <?php }
                                                    } ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-center gap-2 p-3 rounded-3" style="background: rgba(0,116,66,0.05); border: 1px dashed rgba(0,116,66,0.25);">
                                                <i class="fa-solid fa-circle-info" style="color: var(--gms-primary);"></i>
                                                <small class="text-secondary">Butiran kontraktor akan dipaparkan selepas pilihan dibuat.</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================================== -->
                    <!-- STEP 2: CONTRACTOR DETAILS + ASSIGNMENT FORM (DYNAMIC)          -->
                    <!-- ============================================================== -->
                    <div id="contractorDetails" style="display: none; flex-direction: column; gap: 1.5rem;">

                        <!-- Contractor Profile Information -->
                        <div class="row g-4 gms-fade-in gms-fade-in-delay-3">
                            <div class="col-12">
                                <div class="card shadow gms-form-card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                                            <div class="gms-kpi-icon me-3" style="background: rgba(0, 116, 66, 0.08); color: var(--gms-primary);">
                                                <i class="fa-solid fa-id-badge"></i>
                                            </div>
                                            <div>
                                                <h4 class="gms-section-title">Maklumat Profil Kontraktor</h4>
                                                <p class="gms-section-subtitle mb-0">Butiran syarikat &amp; maklumat hubungan.</p>
                                            </div>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <div class="gms-profile-tile">
                                                    <div class="gms-profile-tile-icon">
                                                        <i class="fa-solid fa-user"></i>
                                                    </div>
                                                    <div>
                                                        <div class="gms-profile-tile-label">Kontraktor</div>
                                                        <div class="gms-profile-tile-value" id="contractorName">-</div>
                                                        <small class="text-muted">PIC: <span id="contractorId">-</span></small>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="gms-profile-tile">
                                                    <div class="gms-profile-tile-icon" style="background: rgba(6, 182, 212, 0.1); color: #0891b2;">
                                                        <i class="fa-solid fa-envelope"></i>
                                                    </div>
                                                    <div>
                                                        <div class="gms-profile-tile-label">Alamat Emel</div>
                                                        <div class="gms-profile-tile-value" id="contractorEmail">--</div>
                                                        <small class="text-muted"><i class="fa-solid fa-phone me-1"></i><span id="contractorPhone">--</span></small>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="gms-profile-tile">
                                                    <div class="gms-profile-tile-icon" style="background: rgba(99, 102, 241, 0.1); color: #4f46e5;">
                                                        <i class="fa-solid fa-location-dot"></i>
                                                    </div>
                                                    <div>
                                                        <div class="gms-profile-tile-label">Alamat Perniagaan</div>
                                                        <div class="gms-profile-tile-value" style="font-size: 0.85rem; font-weight: 600; line-height: 1.5;" id="contractorAddress">--</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Assignment Parameters Form -->
                        <div class="row g-4 gms-fade-in gms-fade-in-delay-4">
                            <div class="col-12">
                                <div class="card shadow gms-form-card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                                            <div class="gms-kpi-icon me-3" style="background: rgba(0, 116, 66, 0.08); color: var(--gms-primary);">
                                                <i class="fa-solid fa-file-pdf"></i>
                                            </div>
                                            <div>
                                                <h4 class="gms-section-title">Parameter Lantikan</h4>
                                                <p class="gms-section-subtitle mb-0">Lengkapkan nombor kontrak, tarikh, penyelia &amp; peruntukan.</p>
                                            </div>
                                        </div>

                                        <form action="includes/assign_contractor_action.php" method="POST" class="forms-sample">
                                            <input type="text" name="zid" value="<?php echo $zone_id ?>" hidden>
                                            <input type="text" name="sz_id" value="<?php echo $subzone_id ?>" hidden>
                                            <input type="text" name="cid" id="contractorIdc" hidden>

                                            <div class="row g-4 gms-parameter-grid">
                                                <!-- Contract Number & Dates -->
                                                <div class="col-lg-4">
                                                    <label for="contract_number" class="gms-form-label">Nombor Kontrak <span class="text-danger">*</span></label>
                                                    <div class="gms-input-group mb-3">
                                                        <span class="gms-input-icon"><i class="fa-solid fa-hashtag"></i></span>
                                                        <input type="text" name="contract_number" id="contract_number" class="gms-form-control" placeholder="cth. DBKU/CT/2025/001" required>
                                                    </div>
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label for="start_date" class="gms-form-label">Tarikh Mula <span class="text-danger">*</span></label>
                                                            <div class="gms-input-group">
                                                                <span class="gms-input-icon"><i class="fa-solid fa-calendar-day"></i></span>
                                                                <input type="date" name="start_date" id="start_date" class="gms-form-control" required>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="end_date" class="gms-form-label">Tarikh Tamat <span class="text-danger">*</span></label>
                                                            <div class="gms-input-group">
                                                                <span class="gms-input-icon"><i class="fa-solid fa-calendar-check"></i></span>
                                                                <input type="date" name="end_date" id="end_date" class="gms-form-control" required>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Supervisors -->
                                                <div class="col-lg-4">
                                                    <div class="mb-3">
                                                        <label class="gms-form-label">Penyelia 1</label>
                                                        <div class="row g-2">
                                                            <div class="col-md-6">
                                                                <div class="gms-input-group">
                                                                    <span class="gms-input-icon"><i class="fa-solid fa-user"></i></span>
                                                                    <input type="text" name="supervisor1_name" class="gms-form-control" placeholder="Nama">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="gms-input-group">
                                                                    <span class="gms-input-icon"><i class="fa-solid fa-phone"></i></span>
                                                                    <input type="text" name="supervisor1_contact" class="gms-form-control" placeholder="Nombor Hubungan">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="gms-form-label">Penyelia 2</label>
                                                        <div class="row g-2">
                                                            <div class="col-md-6">
                                                                <div class="gms-input-group">
                                                                    <span class="gms-input-icon"><i class="fa-solid fa-user"></i></span>
                                                                    <input type="text" name="supervisor2_name" class="gms-form-control" placeholder="Nama">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="gms-input-group">
                                                                    <span class="gms-input-icon"><i class="fa-solid fa-phone"></i></span>
                                                                    <input type="text" name="supervisor2_contact" class="gms-form-control" placeholder="Nombor Hubungan">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Funding & Remarks -->
                                                <div class="col-lg-4">
                                                    <div class="mb-3">
                                                        <label for="fund" class="gms-form-label">Peruntukan (Fund)</label>
                                                        <div class="gms-input-group">
                                                            <span class="gms-input-icon"><i class="fa-solid fa-money-bill-trend-up"></i></span>
                                                            <input type="text" name="fund" id="fund" class="gms-form-control" placeholder="cth. RM 50,000">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label for="remarks" class="gms-form-label">Catatan</label>
                                                        <div class="gms-input-group">
                                                            <span class="gms-input-icon" style="align-items: flex-start; padding-top: 12px;"><i class="fa-solid fa-comment-dots"></i></span>
                                                            <textarea name="remarks" id="remarks" cols="30" rows="3" class="gms-form-control" placeholder="Masukkan catatan tambahan..."></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <hr class="my-4 border-light">

                                            <!-- Submission Row -->
                                            <div class="d-flex justify-content-center gap-3 flex-wrap">
                                                <button type="button" class="gms-btn-light" onclick="history.back()"><i class="fa-solid fa-xmark me-1"></i> Batal</button>
                                                <button type="submit" class="gms-btn-primary"><i class="fa-solid fa-check me-1"></i> Lantik Kontraktor</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- contractorDetails END -->

                </div>
                <!-- content-wrapper ends -->

                <?php include 'partials/footer.php'; ?>
            </div>
            <!-- main-panel ends -->
        </div>
        <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->

    <!-- JS -->
    <script src="assets/vendors/js/vendor.bundle.base.js"></script>
    <script src="assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <script src="assets/vendors/moment/moment.min.js"></script>
    <script src="assets/vendors/daterangepicker/daterangepicker.js"></script>
    <script src="assets/js/jquery.cookie.js"></script>
    <script src="assets/js/off-canvas.js"></script>
    <script src="assets/js/hoverable-collapse.js"></script>
    <script src="assets/js/misc.js"></script>
    <script src="assets/js/settings.js"></script>
    <script src="assets/js/todolist.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <?php
    // Check if there is a status message in the session
    if (isset($_SESSION['status'])) {
        $status = $_SESSION['status'];   // 'success' or 'error'
        $message = $_SESSION['message']; // Error or success message

        // Clear session message after use
        unset($_SESSION['status']);
        unset($_SESSION['message']);

        $icon = ($status == 'success') ? 'success' : 'error';
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({ title: "' . addslashes($message) . '", icon: "' . $icon . '" });
            });
        </script>';
    }
    ?>

    <script>
        // Contractor select → load profile details via fetch
        document.getElementById("contractorSelect").addEventListener("change", function() {
            var cid = this.value;
            var details = document.getElementById("contractorDetails");

            if (cid) {
                details.style.display = "flex";

                fetch("get_contractor.php?cid=" + cid)
                    .then(res => res.json())
                    .then(data => {
                        document.getElementById("contractorIdc").value = data.cid; // Set hidden input value
                        document.getElementById("contractorName").innerText = data.name;
                        document.getElementById("contractorId").innerText = data.contact_person;
                        document.getElementById("contractorEmail").innerText = data.email;
                        document.getElementById("contractorPhone").innerText = data.contact_number;
                        document.getElementById("contractorAddress").innerText = data.address;
                    });

            } else {
                details.style.display = "none";
            }
        });

        // Count-up animation for KPI values
        document.addEventListener("DOMContentLoaded", function() {
            var countEls = document.querySelectorAll('[data-countup]');
            countEls.forEach(function(el) {
                var target = parseFloat(el.getAttribute('data-countup')) || 0;
                if (target === 0) { el.textContent = '0'; return; }
                var duration = 1000;
                var startTime = null;
                function step(ts) {
                    if (!startTime) startTime = ts;
                    var progress = Math.min((ts - startTime) / duration, 1);
                    var eased = 1 - Math.pow(1 - progress, 4);
                    var current = target * eased;
                    el.textContent = Math.floor(current).toLocaleString();
                    if (progress < 1) { requestAnimationFrame(step); }
                    else { el.textContent = target.toLocaleString(); }
                }
                requestAnimationFrame(step);
            });
        });
    </script>

</body>

</html>

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
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>SmartCut</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="assets/vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="assets/vendors/flag-icon-css/css/flag-icons.min.css">
    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="assets/vendors/font-awesome/css/font-awesome.min.css" />
    <link rel="stylesheet" href="assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="assets/vendors/jvectormap/jquery-jvectormap.css">
    <link rel="stylesheet" href="assets/vendors/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="assets/vendors/chartist/chartist.min.css">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="assets/css/vertical-light-layout/style.css">
    <!-- End layout styles -->
    <link rel="shortcut icon" href="assets/images/favicon.png" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');

        .modern-zone-details-container {
            font-family: 'Outfit', sans-serif;
            color: #334155;
        }

        /* Metric Cards Styling */
        .metric-card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }

        .metric-card-modern {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05), 0 5px 15px -8px rgba(0, 0, 0, 0.05);
            transition: all 0.25s ease;
        }

        .metric-card-modern:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 35px -5px rgba(0, 0, 0, 0.08), 0 8px 20px -8px rgba(0, 0, 0, 0.08);
            border-color: #cbd5e1;
        }

        .metric-card-content {
            display: flex;
            flex-direction: column;
        }

        .metric-card-title {
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 6px;
        }

        .metric-card-value {
            font-size: 24px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }

        .metric-card-icon-wrapper {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 20px;
            color: #ffffff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .icon-blue {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
        }

        .icon-yellow {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2);
        }

        /* Action Banner / Header */
        .modern-banner {
            background: linear-gradient(90deg, #1d384d 0%, #00311c 100%);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
            border: none;
        }
        
        .modern-contractor-banner {
            background: linear-gradient(90deg, #1d384d 0%, #007442 100%);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
            border: none;
        }

        .modern-banner-title {
            font-size: 22px;
            font-weight: 700;
            color: #ffffff;
            margin: 0;
            letter-spacing: -0.02em;
        }
        
        .modern-banner-subtitle {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.8);
            margin-top: 4px;
            margin-bottom: 0;
        }

        .modern-btn-white {
            background-color: #ffffff;
            color: #1e3a5f !important;
            font-weight: 600;
            font-size: 13px;
            padding: 8px 18px;
            border-radius: 30px;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
            text-decoration: none;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .modern-btn-white:hover {
            background-color: #f8fafc;
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            text-decoration: none;
        }
        
        .modern-btn-primary {
            background-color: #3b82f6;
            color: #ffffff !important;
            font-weight: 600;
            font-size: 13px;
            padding: 8px 18px;
            border-radius: 30px;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
            text-decoration: none;
            box-shadow: 0 4px 10px rgba(59, 130, 246, 0.2);
        }

        .modern-btn-primary:hover {
            background-color: #2563eb;
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(59, 130, 246, 0.3);
            text-decoration: none;
        }
        
        .modern-btn-warning {
            background-color: #f59e0b;
            color: #ffffff !important;
            font-weight: 600;
            font-size: 13px;
            padding: 8px 18px;
            border-radius: 30px;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
            text-decoration: none;
            box-shadow: 0 4px 10px rgba(245, 158, 11, 0.2);
        }
        
        .modern-btn-warning:hover {
            background-color: #d97706;
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(245, 158, 11, 0.3);
            text-decoration: none;
        }

        /* Modern Card Details */
        .modern-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05), 0 5px 15px -8px rgba(0, 0, 0, 0.05) !important;
            background: #ffffff;
            overflow: hidden;
            margin-bottom: 24px;
        }
        
        .modern-card-title {
            font-size: 18px;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 0;
            display: flex;
            align-items: center;
        }
        .action-btn-group {
            display: inline-flex;
            gap: 8px;
            align-items: center;
        }

        .btn-circle-edit {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #eff6ff;
            color: #3b82f6;
            border: none;
            font-size: 13px;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-circle-edit:hover {
            background-color: #3b82f6;
            color: #ffffff;
            transform: scale(1.05);
            box-shadow: 0 4px 10px rgba(59, 130, 246, 0.2);
        }

        .btn-circle-delete {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #fef2f2;
            color: #ef4444;
            border: none;
            font-size: 13px;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .btn-circle-delete:hover {
            background-color: #ef4444;
            color: #ffffff;
            transform: scale(1.05);
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.2);
        }
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

                    <!-- Performance Indicator Start -->
                    <?php
                    $sql = "SELECT * FROM subzone AS a JOIN zone AS b ON b.zone_id = a.zone_id WHERE a.sz_id = '$subzone_id'";
                    $res = $conn->query($sql);
                    $row = $res->fetch_assoc();
                    $zone_name = $row['zone_name'];
                    $subzone_name = $row['sz_name'];
                    ?>

                    <div class="modern-zone-details-container mt-4 mb-0">
                        <div class="row">
                            <div class="col-12">
                                <!-- Action Banner / Header -->
                                <div class="card modern-banner bg-dark">
                                    <div class="card-body p-0 d-sm-flex align-items-center justify-content-between">
                                        <div>
                                            <p class="modern-banner-subtitle"><?php echo $zone_name ?> Zone</p>
                                            <h3 class="modern-banner-title"><i class="icon-location-pin mr-2"></i> <?php echo $subzone_name ?></h3>
                                        </div>
                                        <a href="update_sz.php?szid=<?php echo $subzone_id ?>" class="modern-btn-white mt-3 mt-sm-0"><i class="icon-pencil"></i> Update</a>
                                    </div>
                                </div>
                                
                                <!-- Metrics Grid -->
                                <div class="metric-card-grid">
                                    <!-- Card 1: Total Perimeter -->
                                    <div class="metric-card-modern">
                                        <div class="metric-card-content">
                                            <span class="metric-card-title">Total Perimeter</span>
                                            <h3 class="metric-card-value"><?php echo number_format($total_perimeter) ?> m</h3>
                                        </div>
                                        <div class="metric-card-icon-wrapper icon-blue">
                                            <i class="icon-size-fullscreen"></i>
                                        </div>
                                    </div>
                                    
                                    <!-- Card 2: Total Area -->
                                    <div class="metric-card-modern">
                                        <div class="metric-card-content">
                                            <span class="metric-card-title">Total Area</span>
                                            <h3 class="metric-card-value"><?php echo number_format($total_area, 2) ?> ha</h3>
                                        </div>
                                        <div class="metric-card-icon-wrapper icon-yellow">
                                            <i class="icon-pie-chart"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php
                        $btn_status = false;
                        $sql = "SELECT * FROM contract WHERE sz_id = '$subzone_id' AND c_status = 'Y'"; // Ambil kontrak terbaru untuk subzone ini
                        $res = $conn->query($sql);
                        if ($res->num_rows > 0) {
                            $btn_status = true;
                        }
                        ?>

                        <!-- Contractor Console -->
                        <div class="row mb-4">
                            <div class="col-6">
                                <div class="card modern-contractor-banner">
                                    <div class="card-body p-0 d-sm-flex align-items-center justify-content-between">
                                        <div>
                                            <h3 class="modern-banner-title"><i class="icon-wrench mr-2"></i> Contractor Console</h3>
                                            <p class="modern-banner-subtitle">Tindakan segera untuk isu lapangan dan kontraktor</p>
                                        </div>
                                        <div class="d-flex gap-2 mt-3 mt-sm-0 flex-wrap">
                                            <a href="assign_contractor.php?szid=<?php echo $subzone_id; ?>" class="modern-btn-primary <?php if ($btn_status) { echo 'disabled'; } ?>"><i class="mdi mdi-bullhorn-variant"></i> Assign</a>
                                            <a href="reassign_contractor.php?sz_id=<?php echo $subzone_id; ?>" class="modern-btn-warning"><i class="mdi mdi-account-alert"></i> Reassign</a>
                                            <a href="payments.php?sz_id=<?php echo $subzone_id; ?>" class="modern-btn-white"><i class="mdi mdi-file-check"></i> Payments</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card modern-contractor-banner">
                                    <div class="card-body p-0 d-sm-flex align-items-center justify-content-between">
                                        <div>
                                            <h3 class="modern-banner-title"><i class="icon-wrench mr-2"></i> DBKU Console</h3>
                                            <p class="modern-banner-subtitle">Tindakan untuk supervisor dan pengawas DBKU</p>
                                        </div>
                                        <div class="d-flex gap-2 mt-3 mt-sm-0 flex-wrap">
                                            <a href="assign_dbku.php?szid=<?php echo $subzone_id; ?>" class="modern-btn-primary <?php if (!$btn_status) { echo 'disabled'; } ?>"><i class="mdi mdi-bullhorn-variant"></i> Assign</a>
                                            <a href="assign_dbku.php?szid=<?php echo $subzone_id; ?>" class="modern-btn-warning <?php if (!$btn_status) { echo 'disabled'; } ?>"><i class="mdi mdi-account-alert"></i> Reassign</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <!-- Jika ada contractor row ini akan appear -->


                    <?php
                    // Query untuk mendapatkan maklumat kontraktor yang di-assign ke subzone ini
                    // Anda mungkin perlu menyesuaikan nama column (cth: contractor_id) mengikut database anda
                    $sql = "SELECT * FROM contract WHERE sz_id = '$subzone_id' AND YEAR(start_date) = YEAR(CURDATE()) AND c_status = 'Y'"; // Ambil kontrak terbaru untuk subzone ini
                    $res = $conn->query($sql);
                    if ($res->num_rows > 0) {
                        $row = $res->fetch_assoc();
                        $contract_id = $row['contract_id'];
                        $contractor_id = $row['cid'];
                        // Anda boleh gunakan data kontrak ini untuk dipaparkan di UI
                        $sql1 = "SELECT * FROM contractor WHERE cid = '" . $contractor_id . "'";
                        $res1 = $conn->query($sql1);
                        $row1 = $res1->fetch_assoc();

                        // Fetch DBKU Supervisor & Pengawas info
                        $spv_id = $row['spv_id'];
                        $pgs_id = $row['pgs_id'];
                        
                        $dbku_spv_name = '-';
                        $dbku_spv_email = '-';
                        if ($spv_id) {
                            $sql_spv = "SELECT * FROM users WHERE uid = '$spv_id'";
                            $res_spv = $conn->query($sql_spv);
                            if ($res_spv && $res_spv->num_rows > 0) {
                                $row_spv = $res_spv->fetch_assoc();
                                $dbku_spv_name = $row_spv['user_id'];
                                $dbku_spv_email = $row_spv['user_email'];
                            }
                        }

                        $dbku_pgs_name = '-';
                        $dbku_pgs_email = '-';
                        if ($pgs_id) {
                            $sql_pgs = "SELECT * FROM users WHERE uid = '$pgs_id'";
                            $res_pgs = $conn->query($sql_pgs);
                            if ($res_pgs && $res_pgs->num_rows > 0) {
                                $row_pgs = $res_pgs->fetch_assoc();
                                $dbku_pgs_name = $row_pgs['user_id'];
                                $dbku_pgs_email = $row_pgs['user_email'];
                            }
                        }
                    ?>
                        <div class="row mb-2">
                            <div class="col-md-12 grid-margin stretch-card">
                                <div class="card modern-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h4 class="modern-card-title"><i class="icon-user-following me-2 text-success"></i>Contractor Details</h4>
                                            <span class="badge badge-success px-3 py-2">Active</span>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-4 border-end">
                                                <div class="d-flex align-item-center">
                                                    <div class="bg-light p-3 rounded me-3">
                                                        <i class="icon-user style-icon text-primary" style="font-size: 30px;"></i>
                                                    </div>
                                                    <div>
                                                        <p class="text-muted mb-1 small text-uppercase fw-bold">Contractor</p>
                                                        <h5 class="mb-0 fw-bold"><?php echo $row1['name']; ?></h5>
                                                        <p class="text-small text-muted mb-0 text-uppercase">PIC: <?php echo $row1['contact_person']; ?></p>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center mt-3">
                                                    <div class="bg-light p-3 rounded me-3">
                                                        <i class="icon-list style-icon text-primary" style="font-size: 30px;"></i>
                                                    </div>
                                                    <div>
                                                        <p class="text-muted mb-1 small text-uppercase fw-bold">Contract Details</p>
                                                        <h5 class="mb-0 fw-bold"><?php echo $row['contract_number']; ?></h5>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center mt-3">
                                                    <div class="bg-light p-3 rounded me-3">
                                                        <i class="icon-calendar style-icon text-primary" style="font-size: 30px;"></i>
                                                    </div>
                                                    <div>
                                                        <p class="text-muted mb-1 small text-uppercase fw-bold">Duration <span class="badge badge-success px-3 py-2">Active</span></p>
                                                        <div class="d-flex gap-3">
                                                            <h5 class="mb-0 fw-bold"><?php echo date("d F Y", strtotime($row['start_date'])); ?></h5>
                                                            <h5 class="mb-0 fw-bold">-</h5>
                                                            <h5 class="mb-0 fw-bold"><?php echo date("d F Y", strtotime($row['end_date'])); ?></h5>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4 border-end">
                                                <p class="text-muted mb-1 small text-uppercase fw-bold">Foreman</p>
                                                <h6 class="mb-1">Mohd Hazwan</h6>
                                                <b class="mb-2 d-block text-primary"><i class="icon-phone me-1"></i> 012-3456789</b>
                                                <div class="row mb-3">
                                                    <div class="col-6">
                                                        <p class="text-muted mb-1 small text-uppercase fw-bold">Supervisor 1</p>
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($row['supervisor_1']); ?></h6>
                                                        <b class="mb-0 text-primary"><i class="icon-phone me-1"></i> <?php echo htmlspecialchars($row['supervisor1_contact']); ?></b>
                                                    </div>
                                                    <div class="col-6">
                                                        <p class="text-muted mb-1 small text-uppercase fw-bold">Supervisor 2</p>
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($row['supervisor_2']); ?></h6>
                                                        <b class="mb-0 text-primary"><i class="icon-phone me-1"></i> <?php echo htmlspecialchars($row['supervisor2_contact']); ?></b>
                                                    </div>
                                                </div>

                                                <div class="border-top pt-3 mb-3">
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <p class="text-muted mb-1 small text-uppercase fw-bold">DBKU Supervisor</p>
                                                            <h6 class="mb-1 text-dark fw-bold"><?php echo htmlspecialchars($dbku_spv_name); ?></h6>
                                                            <span class="text-small text-muted" style="font-size: 11px; word-break: break-all;"><i class="fa fa-envelope me-1"></i> <?php echo htmlspecialchars($dbku_spv_email); ?></span>
                                                        </div>
                                                        <div class="col-6">
                                                            <p class="text-muted mb-1 small text-uppercase fw-bold">DBKU Pengawas</p>
                                                            <h6 class="mb-1 text-dark fw-bold"><?php echo htmlspecialchars($dbku_pgs_name); ?></h6>
                                                            <span class="text-small text-muted" style="font-size: 11px; word-break: break-all;"><i class="fa fa-envelope me-1"></i> <?php echo htmlspecialchars($dbku_pgs_email); ?></span>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>

                                            <div class="col-md-4 justify-content-center">
                                                <div class="row justify-content-center">
                                                    <div class="col-3 pt-2">
                                                        <?php
                                                        $current_month = (int)date("m");
                                                        $current_day = (int)date("d");
                                                        if ($current_day <= 15) {
                                                            $current_session = ($current_month - 1) * 2 + 1;
                                                        } else {
                                                            $current_session = ($current_month - 1) * 2 + 2;
                                                        }
                                                        ?>
                                                        <span class="badge badge-success px-3 py-2 text-uppercase fw-bold">
                                                            <i class="icon-refresh me-1"></i>Session <?php echo $current_session; ?>
                                                        </span>
                                                    </div>
                                                </div>
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
                                                WHERE a.sz_id = '$subzone_id'
                                                GROUP BY a.contract_id";
                                                
                                                $res_progress = $conn->query($sql_progress);
                                                $progress_percent = 0;
                                                $max_workers = 0;
                                                if ($res_progress && $res_progress->num_rows > 0) {
                                                    $row_progress = $res_progress->fetch_assoc();
                                                    $progress_percent = $row_progress['progress_sesi_peratusan'] ?? 0;
                                                    $max_workers = $row_progress['total_max_workers'] ?? 0;
                                                }
                                                ?>
                                                <div class="d-flex justify-content-between mt-3">
                                                    <div class="p-3 rounded align-items-center me-3">
                                                        <i class="icon-people text-muted me-1"> Workers</i>
                                                        <p class="text-muted mb-1 small text-uppercase fw-bold"><?php echo $max_workers; ?></p>
                                                    </div>
                                                    <div class="p-3 rounded me-3">
                                                        <i class="icon-clock text-muted me-1"> Expected Completion</i>
                                                        <p class="text-muted mb-1 small text-uppercase fw-bold"><?php 
                                                            if (date("d") <= 15) {
                                                                echo strtoupper(date("15 F Y"));
                                                            } else {
                                                                echo strtoupper(date("t F Y"));
                                                            }
                                                        ?></p>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center pt-3">
                                                    <p class="text-muted mb-1 small text-uppercase fw-bold">Status</p>
                                                    <p class="text-success mb-1 small"><?php echo $progress_percent; ?>% Completed</p>
                                                </div>
                                                <div class="progress progress-md">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $progress_percent; ?>%" aria-valuenow="<?php echo $progress_percent; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center mt-4">
                                                    <a href="contractor_details.php?id=<?php echo $row1['cid'];
                                                                                        ?>" class="btn btn-outline-primary py-2">Details</a>
                                                    <a href="daily_details.php?id=<?php echo $contract_id;
                                                                                ?>" class="btn btn-success py-2 ms-1">Daily Diary</a>
                                                    <a href="#" class="btn btn-primary py-2 ms-1">Contact</a>
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
                                        <small>Sila gunakan <b>Contractor Console</b> di atas untuk melantik kontraktor.</small>
                                    </div>
                                </div>
                            </div>
                        </div> -->
                    <?php //endif; 
                    ?>


                    <!-- Jika ada contractor yang terassign -->

                    <div class="row">
                        <div class="col-md-6 grid-margin stretch-card">
                            <div class="card modern-card">
                                <div class="card-body">
                                    <div class="d-sm-flex align-items-center mb-3">
                                        <h4 class="modern-card-title">Peta</h4>

                                    </div>

                                    <div id="map" style="width: 100%; height: 50vh;"></div>

                                    <div class="d-flex flex-wrap mt-3">
                                        <div class="d-flex align-items-center me-4">
                                            <span class="dot-indicator bg-success"></span>
                                            <p class="mb-0 ms-2 text-muted">Zon Selesai</p>
                                        </div>
                                        <div class="d-flex align-items-center me-4">
                                            <span class="dot-indicator bg-warning"></span>
                                            <p class="mb-0 ms-2 text-muted">Sedang Berjalan</p>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <span class="dot-indicator bg-danger"></span>
                                            <p class="mb-0 ms-2 text-muted">Kawasan Bermasalah</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 grid-margin stretch-card">
                            <div class="card modern-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h4 class="modern-card-title mb-0">Area</h4>
                                        <button type="button" class="btn btn-sm btn-primary rounded-circle" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;" data-bs-toggle="modal" data-bs-target="#addLocationModal" title="Tambah Lokasi">
                                            <i class="icon-plus"></i>
                                        </button>
                                    </div>

                                    <div class="table-responsive mt-3" style="max-height: 400px; overflow-y: auto;">
                                        <table class="table table-bordered table-hover" style="table-layout: fixed; width: 100%;">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th style="width: 10%;">AREA</th>
                                                    <th style="width: 50%;">LOCATION</th>
                                                    <th style="width: 15%"></th>
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

                                                $sql_area = "SELECT * FROM contract_location WHERE sz_id = '$subzone_id'";
                                                $res_area = $conn->query($sql_area);
                                                
                                                if ($res_area && $res_area->num_rows > 0) {
                                                    while ($row_area = $res_area->fetch_assoc()) {
                                                        $loc_id = $row_area[$pk_col] ?? '';
                                                        ?>
                                                        <tr>
                                                            <td style="word-wrap: break-word; white-space: normal; text-align: justify; padding: 12px 10px; line-height: 1.2;"><?php echo isset($row_area['area']) ? htmlspecialchars($row_area['area']) : '-'; ?></td>
                                                            <td style="word-wrap: break-word; white-space: normal; text-align: justify; padding: 12px 10px; line-height: 1.2;"><?php echo isset($row_area['location']) ? htmlspecialchars($row_area['location']) : '-'; ?></td>
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
                                                    echo "<tr><td colspan='3' class='text-center text-muted'>Tiada rekod dijumpai</td></tr>";
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
                    <!-- content-wrapper ends -->
                    <!-- partial:partials/_footer.html -->
                    <footer class="footer">
                        <div class="d-sm-flex justify-content-center justify-content-sm-between">
                            <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">Copyright © 2024 Stellar. All rights reserved. <a href="#"> Terms of use</a><a href="#">Privacy Policy</a></span>
                            <span class="float-none float-sm-right d-block mt-1 mt-sm-0 text-center">Hand-crafted & made with <i class="icon-heart text-danger"></i></span>
                        </div>
                    </footer>
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
        <!-- End plugin js for this page -->
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
                $sql = "SELECT * FROM kmz_file WHERE sz_id = '$subzone_id'";
                $res = $conn->query($sql);
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
    <!-- Add Location Modal -->
    <div class="modal fade" id="addLocationModal" tabindex="-1" aria-labelledby="addLocationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="background-color: #ffffff;">
                <div class="modal-header">
                    <h5 class="modal-title" id="addLocationModalLabel">Tambah Lokasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="formAddLocation">
                        <input type="hidden" name="sz_id" id="sz_id" value="<?php echo $subzone_id; ?>">
                        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                            <table class="table align-middle" style="table-layout: fixed; width: 100%;">
                                <thead style="position: sticky; top: 0; z-index: 1; background-color: #ffffff;">
                                    <tr>
                                        <th style="width: 25%; border-bottom: 2px solid #dee2e6;">Area</th>
                                        <th style="width: 60%; border-bottom: 2px solid #dee2e6;">Location</th>
                                        <th style="width: 15%; text-align: center; border-bottom: 2px solid #dee2e6;">Tindakan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql_modal_area = "SELECT * FROM contract_location WHERE sz_id = '$subzone_id' ORDER BY area ASC";
                                    $res_modal_area = $conn->query($sql_modal_area);
                                    if ($res_modal_area && $res_modal_area->num_rows > 0) {
                                        while ($row_modal = $res_modal_area->fetch_assoc()) {
                                            echo '<tr>';
                                            echo '<td style="word-wrap: break-word; white-space: normal; padding: 12px; line-height: 1.6; border-bottom: 1px solid #f1f5f9;">' . htmlspecialchars($row_modal['area']) . '</td>';
                                            echo '<td style="word-wrap: break-word; white-space: normal; padding: 12px; line-height: 1.6; border-bottom: 1px solid #f1f5f9;">' . htmlspecialchars($row_modal['location']) . '</td>';
                                            echo '<td class="text-center text-muted" style="border-bottom: 1px solid #f1f5f9;"><i class="fa fa-check text-success"></i> Disimpan</td>';
                                            echo '</tr>';
                                        }
                                    } else {
                                        echo "<tr><td colspan='3' class='text-center text-muted py-4' style='border-bottom: 1px solid #f1f5f9;'>Tiada rekod lokasi dijumpai. Sila tambah di bawah.</td></tr>";
                                    }
                                    ?>
                                    
                                    <!-- Row for new entry -->
                                    <tr>
                                        <td class="p-2">
                                            <input type="text" class="form-control" id="area" name="area" placeholder="Masukkan Area" required>
                                        </td>
                                        <td class="p-2">
                                            <textarea class="form-control" id="location" name="location" rows="2" placeholder="Masukkan baris demi baris jika ada" required></textarea>
                                        </td>
                                        <td class="p-2 text-center">
                                            <button type="submit" class="btn btn-primary w-100 h-100 shadow-sm" id="btnSaveLocation">
                                                <i class="icon-plus"></i> Simpan
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
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
            btn.innerText = 'Menyimpan...';

            let formData = new FormData(this);

            fetch('add_locations.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                btn.disabled = false;
                btn.innerText = 'Simpan';
                
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
                btn.innerText = 'Simpan';
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
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditLocation">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-3">
                            <label for="edit_area" class="form-label">Area</label>
                            <input type="text" class="form-control" id="edit_area" name="area" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_location" class="form-label">Location</label>
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
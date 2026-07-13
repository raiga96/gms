<?php
session_start();
require 'includes/connection.php';

// Total Contract
$sql_tc = "SELECT COUNT(*) AS total_contract FROM contract";
$res_tc = $conn->query($sql_tc);
$row_tc = $res_tc->fetch_assoc();
$total_contract = $row_tc['total_contract'];

// Total Contractor
$sql_tcon = "SELECT COUNT(*) AS total_contractor FROM contractor";
$res_tcon = $conn->query($sql_tcon);
$row_tcon = $res_tcon->fetch_assoc();
$total_contractor = $row_tcon['total_contractor'];

// Calculate current session (2 sessions per month, 24 sessions per year)
$current_month = (int)date('n');
$current_day = (int)date('j');
$session_num = ($current_month - 1) * 2 + ($current_day <= 15 ? 1 : 2);
$current_session = "Session " . $session_num;

// Contractors Data
$contractors_sql = "
    SELECT 
        c.cid,
        c.name,
        COUNT(ct.contract_id) as jumlah_contract,
        GROUP_CONCAT(ct.contract_number SEPARATOR ', ') as number_contract,
        GROUP_CONCAT(sz.sz_name SEPARATOR ', ') as zone_names
    FROM contractor c
    LEFT JOIN contract ct ON c.cid = ct.cid
    LEFT JOIN subzone sz ON ct.sz_id = sz.sz_id
    GROUP BY c.cid
";
$contractors_res = $conn->query($contractors_sql);

// Chart Data (Contracts per Zone)
$chart_sql = "
    SELECT 
        sz.sz_name, 
        COUNT(ct.contract_id) as total_contract 
    FROM contract ct 
    JOIN subzone sz ON ct.sz_id = sz.sz_id 
    GROUP BY sz.sz_id
";
$chart_res = $conn->query($chart_sql);
$chart_labels = [];
$chart_data = [];
if ($chart_res && $chart_res->num_rows > 0) {
    while ($row = $chart_res->fetch_assoc()) {
        $chart_labels[] = $row['sz_name'];
        $chart_data[] = $row['total_contract'];
    }
}
$chart_labels_json = json_encode($chart_labels);
$chart_data_json = json_encode($chart_data);
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

        body, .content-wrapper, .card {
            font-family: 'Outfit', sans-serif !important;
        }

        .card.shadow {
            border: none;
            border-radius: 16px !important;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05), 0 5px 15px -8px rgba(0, 0, 0, 0.05) !important;
        }
        
        .card-title {
            font-weight: 700 !important;
            color: #1e293b;
        }

        .modern-stat-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(226, 232, 240, 0.8);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            height: 100%;
        }

        .modern-stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 30px -5px rgba(0, 0, 0, 0.08);
            border-color: #cbd5e1;
        }

        .modern-stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
        }
        
        .modern-stat-card.stat-success::before { background: #10b981; }
        .modern-stat-card.stat-danger::before { background: #ef4444; }
        .modern-stat-card.stat-info::before { background: #3b82f6; }
        .modern-stat-card.stat-warning::before { background: #f59e0b; }
        .modern-stat-card.stat-primary::before { background: #6366f1; }

        .modern-stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        
        .modern-stat-card.stat-success .modern-stat-icon { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        .modern-stat-card.stat-danger .modern-stat-icon { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
        .modern-stat-card.stat-info .modern-stat-icon { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
        .modern-stat-card.stat-warning .modern-stat-icon { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
        .modern-stat-card.stat-primary .modern-stat-icon { background: rgba(99, 102, 241, 0.1); color: #6366f1; }

        .modern-stat-content h2 {
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
            margin: 4px 0;
            letter-spacing: -0.02em;
        }

        .modern-stat-content .report-title {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .modern-stat-content .report-count {
            font-size: 12px;
            font-weight: 500;
            color: #94a3b8;
        }

        /* Modern Tables */
        .modern-table-wrapper {
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
        }

        .modern-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .modern-table th {
            background-color: #f8fafc;
            color: #475569;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 16px 14px;
            border-bottom: 1px solid #e2e8f0;
        }

        .modern-table td {
            padding: 16px 14px;
            vertical-align: middle;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
            color: #334155;
            background-color: #ffffff;
        }

        .modern-table tr:last-child td {
            border-bottom: none;
        }

        .modern-table tbody tr:hover td {
            background-color: #f8fafc;
        }
        
        .modern-banner {
            background: linear-gradient(135deg, #1d384d 0%, #007442 100%) !important;
            border-radius: 16px !important;
        }
        
        .modern-banner .btn {
            border-radius: 8px;
            font-weight: 700;
            letter-spacing: 0.02em;
            font-size: 13px;
            padding: 12px 24px;
            border: none;
        }
        
        .notification-list .wrapper h6 {
            color: #1e293b;
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
            <div class="main-panel">
                <div class="content-wrapper">
                    <!-- Report Summary Start -->
                    <div class="row">
                        <div class="col-md-12 grid-margin">
                            <div class="card shadow">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="d-sm-flex align-items-baseline report-summary-header">
                                                <h5 class="font-weight-semibold">Report Summary</h5> <span class="ms-auto">Updated Report</span> <button class="btn btn-icons border-0 p-2"><i class="icon-refresh"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row g-4 mt-2">
                                        <div class="col-md-4 col-xl">
                                            <div class="modern-stat-card stat-success">
                                                <div class="modern-stat-content">
                                                    <div class="report-title">TOTAL ZONE</div>
                                                    <h2>20</h2>
                                                    <div class="report-count">49 Sub-zone</div>
                                                </div>
                                                <div class="modern-stat-icon">
                                                    <i class="icon-map"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-xl">
                                            <div class="modern-stat-card stat-danger">
                                                <div class="modern-stat-content">
                                                    <div class="report-title">CONTRACTOR</div>
                                                    <h2><?php echo $total_contractor; ?></h2>
                                                    <div class="report-count">Active</div>
                                                </div>
                                                <div class="modern-stat-icon">
                                                    <i class="icon-people"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-xl">
                                            <div class="modern-stat-card stat-info">
                                                <div class="modern-stat-content">
                                                    <div class="report-title">TOTAL CONTRACT</div>
                                                    <h2><?php echo $total_contract; ?></h2>
                                                    <div class="report-count">Active Contracts</div>
                                                </div>
                                                <div class="modern-stat-icon">
                                                    <i class="icon-doc"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-xl">
                                            <div class="modern-stat-card stat-warning">
                                                <div class="modern-stat-content">
                                                    <div class="report-title">CURRENT SESSION</div>
                                                    <h2><?php echo $current_session; ?></h2>
                                                    <div class="report-count">Ongoing</div>
                                                </div>
                                                <div class="modern-stat-icon">
                                                    <i class="icon-clock"></i>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Report Summary End -->
                    <!-- Performance Indicator Start -->

                    <div class="row mt-4 mb-5">
                        <div class="col-12">
                            <div class="card shadow modern-banner border-0">
                                <div class="card-body d-sm-flex align-items-center justify-content-between text-white p-4">
                                    <div class="mb-3 mb-sm-0">
                                        <h5 class="mb-1 text-white fw-bold">War Room Console</h5>
                                        <p class="mb-0 opacity-75">Tindakan segera untuk isu lapangan dan kontraktor</p>
                                    </div>
                                    <div class="btn-group gap-2">
                                        <button class="btn btn-danger shadow-sm"><i class="mdi mdi-bullhorn-variant me-1"></i> Broadcast Warning</button>
                                        <button class="btn btn-warning text-dark shadow-sm"><i class="mdi mdi-account-alert me-1"></i> Reassign Contractor</button>
                                        <button class="btn btn-light shadow-sm text-primary"><i class="mdi mdi-file-check me-1"></i> Bulk Approve Payments</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- <div class="row">
                        <div class="col-md-8 grid-margin stretch-card">
                            <div class="card shadow">
                                <div class="card-body performane-indicator-card">
                                    <div class="d-sm-flex">
                                        <h4 class="card-title flex-shrink-1">Performance Indicator</h4>
                                        <p class="m-sm-0 ms-sm-auto flex-shrink-0">
                                            <span class="data-time-range ms-0">7d</span>
                                            <span class="data-time-range active">2w</span>
                                            <span class="data-time-range">1m</span>
                                            <span class="data-time-range">3m</span>
                                            <span class="data-time-range">6m</span>
                                        </p>
                                    </div>
                                    <div class="d-sm-flex flex-wrap mt-3">
                                        <div class="d-flex align-items-center">
                                            <span class="dot-indicator bg-primary ms-2"></span>
                                            <p class="mb-0 ms-2 text-muted font-weight-semibold">Complaints (2098)</p>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <span class="dot-indicator bg-info ms-2"></span>
                                            <p class="mb-0 ms-2 text-muted font-weight-semibold"> Task Done (1123)</p>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <span class="dot-indicator bg-danger ms-2"></span>
                                            <p class="mb-0 ms-2 text-muted font-weight-semibold">Attends (876)</p>
                                        </div>
                                    </div>
                                    <div class="dotted-chart-height">
                                        <canvas id="performance-indicator-chart" class="mt-5"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 grid-margin stretch-card">
                            <div class="card shadow">
                                <div class="card-body">
                                    <h4 class="card-title">Sessions by channel</h4>
                                    <div class="aligner-wrapper py-3">
                                        <div class="doughnut-chart-height">
                                            <canvas id="sessionsDoughnutChart" height="210"></canvas>
                                        </div>
                                        <div class="wrapper d-flex flex-column justify-content-center absolute absolute-center">
                                            <h2 class="text-center mb-0 font-weight-bold">8.234</h2>
                                            <small class="d-block text-center text-muted  font-weight-semibold mb-0">Total Leads</small>
                                        </div>
                                    </div>
                                    <div class="wrapper mt-4 d-flex flex-wrap align-items-cente">
                                        <div class="d-flex">
                                            <span class="square-indicator bg-danger ms-2"></span>
                                            <p class="mb-0 ms-2">Assigned</p>
                                        </div>
                                        <div class="d-flex">
                                            <span class="square-indicator bg-success ms-2"></span>
                                            <p class="mb-0 ms-2">Not Assigned</p>
                                        </div>
                                        <div class="d-flex">
                                            <span class="square-indicator bg-warning ms-2"></span>
                                            <p class="mb-0 ms-2">Reassigned</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> -->

                    <div class="row">
                        <div class="col-md-8 grid-margin stretch-card">
                            <div class="card shadow">
                                <div class="card-body">
                                    <div class="d-sm-flex align-items-center mb-3">
                                        <h4 class="card-title mb-0">Zone Maps</h4>
                                        <div class="ms-auto">
                                            <div class="form-check form-switch d-inline-block me-3">
                                                <input class="form-check-input" type="checkbox" id="heatmapToggle" checked>
                                                <label class="form-check-label" for="heatmapToggle">Heatmap Mode</label>
                                            </div>
                                            <span class="badge bg-success"> 49 Sub Zone</span>
                                        </div>
                                    </div>
                                    <!-- <div id="controls" style="
    background:#fff;
    padding:10px;
    border:1px solid #ccc;
    display:flex;        /* flex container */
    flex-wrap:wrap;      /* auto break ke baris baru */
    gap:10px;            /* jarak antara checkbox */
">
                                        <b style="width:100%;">Layers:</b>
                                    </div> -->


                                    <?php include 'maps.php' ?>

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

                        <div class="col-md-4 grid-margin stretch-card">
                            <div class="card shadow">
                                <div class="card-body">
                                    <h4 class="card-title">Notifikasi & Amaran</h4>

                                    <div class="notification-list mt-4" style="max-height: 400px; overflow-y: auto;">
                                        <div class="d-flex align-items-start border-bottom py-3">
                                            <div class="bg-danger p-2 rounded-circle me-3">
                                                <i class="mdi mdi-weather-pouring text-white"></i>
                                            </div>
                                            <div class="wrapper">
                                                <h6 class="mb-1 font-weight-bold">Amaran Cuaca: Hujan Lebat</h6>
                                                <p class="mb-0 text-muted small">Zon 15 & 16: Hentikan operasi segera.</p>
                                                <small class="text-primary fw-bold">Baru Sahaja</small>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-start border-bottom py-3">
                                            <div class="bg-warning p-2 rounded-circle me-3">
                                                <i class="mdi mdi-message-alert text-dark"></i>
                                            </div>
                                            <div class="wrapper text-truncate" style="max-width: 200px;">
                                                <h6 class="mb-1 font-weight-bold">Aduan Awam Baru</h6>
                                                <p class="mb-0 text-muted small">Jalan Semariang: Rumput panjang menghalang pandangan.</p>
                                                <small class="text-muted">10 minit yang lalu</small>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-start py-3 border-bottom">
                                            <div class="bg-success p-2 rounded-circle me-3">
                                                <i class="mdi mdi-check-circle text-white"></i>
                                            </div>
                                            <div class="wrapper">
                                                <h6 class="mb-1 font-weight-bold">Kerja Selesai: Cycle 1</h6>
                                                <p class="mb-0 text-muted small">Kontraktor #007 telah memuat naik laporan akhir.</p>
                                                <small class="text-muted">1 jam yang lalu</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4">
                                        <button class="btn btn-outline-primary btn-block w-100">Lihat Semua Notifikasi</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-8 grid-margin stretch-card">
                            <div class="card shadow">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <div>
                                            <h4 class="card-title mb-1">Statistik Kontrak Mengikut Zon</h4>
                                            <p class="text-muted small">Taburan bilangan kontrak aktif di setiap zon</p>
                                        </div>
                                    </div>
                                    <div style="height: 300px;">
                                        <canvas id="zoneContractsChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 grid-margin stretch-card">
                            <div class="card shadow">
                                <div class="card-body">
                                    <h4 class="card-title">Live Activity Feed</h4>
                                    <div class="timeline-wrapper mt-4" style="max-height: 320px; overflow-y: auto;">
                                        <div class="d-flex mb-4">
                                            <div class="text-success me-3">
                                                <i class="mdi mdi-play-circle fs-4"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold">Kontraktor #08 - Start Work</h6>
                                                <small class="text-muted">Zone 15 (Jln Semariang) • 2 min ago</small>
                                            </div>
                                        </div>
                                        <div class="d-flex mb-4">
                                            <div class="text-danger me-3">
                                                <i class="mdi mdi-alert fs-4"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold">Machine Overheat Alert</h6>
                                                <small class="text-muted">Jentera A-44 • 15 min ago</small>
                                            </div>
                                        </div>
                                        <div class="d-flex mb-4">
                                            <div class="text-info me-3">
                                                <i class="mdi mdi-camera fs-4"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold">"Before" Photo Uploaded</h6>
                                                <small class="text-muted">Zone 12 • 45 min ago</small>
                                            </div>
                                        </div>
                                    </div>
                                    <button class="btn btn-light w-100 mt-2 text-primary">View Full Log</button>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Removed Quick Action Toolbar -->

                    <div class="row">
                        <div class="col-md-12 grid-margin stretch-card">
                            <div class="card shadow">
                                <div class="card-body">
                                    <div class="d-sm-flex align-items-center mb-4">
                                        <h4 class="card-title mb-sm-0">Senarai Kontraktor</h4>
                                        <a href="#" class="text-dark ms-auto mb-3 mb-sm-0"> Lihat Semua</a>
                                    </div>
                                    <div class="modern-table-wrapper">
                                        <table class="modern-table">
                                            <thead>
                                                <tr>
                                                    <th class="font-weight-bold">Kontraktor</th>
                                                    <th class="font-weight-bold text-center">Jumlah Contract</th>
                                                    <th class="font-weight-bold">Number Contract</th>
                                                    <th class="font-weight-bold">Zon</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                if ($contractors_res && $contractors_res->num_rows > 0) {
                                                    while ($c_row = $contractors_res->fetch_assoc()) {
                                                        echo "<tr>";
                                                        echo "<td><div class='d-flex align-items-center'><div class='bg-light text-primary rounded-circle p-2 me-3'><i class='icon-user'></i></div> <span class='font-weight-bold text-dark'>" . htmlspecialchars($c_row['name']) . "</span></div></td>";
                                                        echo "<td class='text-center'><span class='badge badge-success p-2'>" . $c_row['jumlah_contract'] . " Kontrak</span></td>";
                                                        echo "<td><span class='text-muted font-weight-semibold'>" . htmlspecialchars($c_row['number_contract'] ?: '-') . "</span></td>";
                                                        echo "<td><span class='text-muted'>" . htmlspecialchars($c_row['zone_names'] ?: '-') . "</span></td>";
                                                        echo "</tr>";
                                                    }
                                                } else {
                                                    echo "<tr><td colspan='4' class='text-center text-muted'>Tiada rekod kontraktor</td></tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex mt-4 flex-wrap align-items-center">
                                        <p class="text-muted mb-sm-0">Showing 1 to 10 of 57 entries</p>
                                        <nav class="ms-auto">
                                            <ul class="pagination separated pagination-info mb-sm-0">
                                                <li class="page-item"><a href="#" class="page-link"><i class="icon-arrow-left"></i></a></li>
                                                <li class="page-item active"><a href="#" class="page-link">1</a></li>
                                                <li class="page-item"><a href="#" class="page-link">2</a></li>
                                                <li class="page-item"><a href="#" class="page-link">3</a></li>
                                                <li class="page-item"><a href="#" class="page-link">4</a></li>
                                                <li class="page-item"><a href="#" class="page-link"><i class="icon-arrow-right"></i></a></li>
                                            </ul>
                                        </nav>
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
    <!-- End custom js for this page -->

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var ctx = document.getElementById('zoneContractsChart').getContext('2d');
            
            var chartLabels = <?php echo $chart_labels_json; ?>;
            var chartData = <?php echo $chart_data_json; ?>;

            var myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: 'Jumlah Kontrak',
                        data: chartData,
                        backgroundColor: 'rgba(0, 116, 66, 0.7)',
                        borderColor: '#007442',
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    }
                }
            });
        });
    </script>
</body>

</html>
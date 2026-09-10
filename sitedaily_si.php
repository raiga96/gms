<?php
session_start();
$cid = $_GET['id'];

require 'includes/connection.php';
?>
<!DOCTYPE html>
<html lang="ms">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>SmartCut - Arahan Tapak (SI)</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="assets/vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="assets/vendors/flag-icon-css/css/flag-icons.min.css">
    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="assets/vendors/font-awesome/css/font-awesome.min.css" />
    <link rel="stylesheet" href="assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="assets/css/vertical-light-layout/style.css">
    <link rel="stylesheet" href="assets/css/modern-dashboard.css">
    <!-- End layout styles -->
    <link rel="shortcut icon" href="assets/images/favicon.png" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Outfit', sans-serif; background-color: #f8fafc; }
        .card { border-radius: 16px; border: none; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
        .modern-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .modern-table-wrapper {
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
        }

        .modern-table th {
            background-color: #f8fafc;
            color: #475569;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 12px 14px;
            border-bottom: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
            text-align: center;
            vertical-align: middle;
        }

        .modern-table th:last-child {
            border-right: none;
        }

        .modern-table td {
            padding: 12px 14px;
            vertical-align: middle;
            border-bottom: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
            font-size: 13px;
            color: #334155;
            background-color: #ffffff;
        }

        .modern-table td:last-child {
            border-right: none;
        }

        .modern-table tr:last-child td {
            border-bottom: none;
        }

        .modern-table tbody tr:hover td {
            background-color: #f8fafc;
        }

        /* Day cell styling */
        .modern-table .day-cell {
            font-weight: 600;
            color: #0f172a;
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
                    <div class="row justify-content-center">
                        <div class="col-12 mb-4">
                            <a href="sitedaily_records.php?id=<?php echo $cid; ?>" class="btn btn-light shadow-sm">
                                <i class="icon-arrow-left"></i> Kembali ke Rekod Harian
                            </a>
                        </div>
                        <div class="col-8 grid-margin stretch-card">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title text-dark font-weight-bold mb-4">Arahan Tapak (SI)</h4>
                                    
                                    <!-- Placeholder for SI Content -->
                                   <div class="modern-table-wrapper mb-4">
                                        <table class="modern-table">
                                            <tbody>
                                                <tr>
                                                    <td><strong>Nombor SI</strong></td>
                                                    <td><strong>SC_3067</strong></td>
                                                </tr>
                                                <tr>
                                                    <td class="no-col">Projek</td>
                                                    <td><strong>KERJA-KERJA MEMOTONG RUMPUT DAN MEMBERSIHKAN LONGKANG DALAM KAWASAN DBKU ZON</strong></td>
                                                </tr>
                                                <tr>
                                                    <td>Nombor Kontrak</td>
                                                    <td>DBKU/CQS/S/50/25</td>
                                                </tr>
                                                <tr>
                                                    <td>Kontraktor</td>
                                                    <td>ABRAR MAJU SDN BHD</td>
                                                </tr>
                                                <tr>
                                                    <td>Tarikh</td>
                                                    <td>19/07/2026</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="modern-table-wrapper mb-4">
                                        <table class="modern-table">
                                            <thead>
                                                <th style="text-align: left;"><strong>Perkara</strong></th>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><strong>Perihalan</strong></td>
                                                </tr>
                                                
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="modern-table-wrapper">
                                        <table class="modern-table">
                                            <thead>
                                                <th style="text-align: left; width:90%"><strong>Arahan</strong></th>
                                                <th>Rujukan</th>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><strong>Perihalan</strong></td>
                                                    <td></td>
                                                </tr>
                                                
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

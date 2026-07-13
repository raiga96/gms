<?php
session_start();
require 'includes/connection.php';

//Total Zone
$sql = "SELECT COUNT(*) AS total_contract FROM contract";
$res = $conn->query($sql);
$row = $res->fetch_assoc();
$total_zone = $row['total_contract'];

//Total sub-zone
$sql1 = "SELECT COUNT(*) AS total_contractor FROM contractor";
$res1 = $conn->query($sql1);
$row1 = $res1->fetch_assoc();
$total_subzone = $row1['total_contractor'];

// Total Perimeter(m) by zone
$sql2 = "SELECT SUM(fund) AS total_contract_value FROM contract";
$res2 = $conn->query($sql2);
$row2 = $res2->fetch_assoc();
$total_perimeter = $row2['total_contract_value'] ?? 0;

// Total Area(ha) by zone
$sql3 = "SELECT SUM(area) AS total_area FROM subzone";
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
    <link rel="shortcut icon" href="assets/images/favicon.png" />
    <!-- plugins:css -->
    <link rel="stylesheet" href="assets/vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="assets/vendors/flag-icon-css/css/flag-icons.min.css">
    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" integrity="sha512-5Hs3dF2AEPkpNAR7UiOHba+lRSJNeM2ECkwxUIxC1Q/FLycGTbNapWXB4tP889k5T5Ju8fs4b1P5z/iB4nMfSQ==" crossorigin="anonymous" referrerpolicy="no-referrer">

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
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');

        .modern-zone-container {
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

        .icon-green {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
        }

        .icon-red {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
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
            padding: 20px 24px;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
            border: none;
        }

        .modern-banner-title {
            font-size: 18px;
            font-weight: 600;
            color: #ffffff;
            margin: 0;
        }

        .modern-btn-white {
            background-color: #ffffff;
            color: #1e3a5f !important;
            font-weight: 600;
            font-size: 13px;
            padding: 8px 16px;
            border-radius: 30px;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .modern-btn-white:hover {
            background-color: #f1f5f9;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-decoration: none;
        }

        /* Modern Table Card */
        .modern-zone-container .card {
            border: none;
            border-radius: 16px !important;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05), 0 5px 15px -8px rgba(0, 0, 0, 0.05) !important;
            background: #ffffff;
            overflow: hidden;
        }

        .modern-zone-container .card-title {
            font-size: 18px;
            font-weight: 600;
            color: #0f172a;
            margin: 0;
        }

        .modern-table-wrapper {
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            margin-top: 15px;
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
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 14px 16px;
            border-bottom: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
            text-align: left;
        }

        .modern-table th:last-child {
            border-right: none;
        }

        .modern-table td {
            padding: 14px 16px;
            vertical-align: middle;
            border-bottom: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
            font-size: 14px;
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

        /* Action Buttons */
        .action-btn-group {
            display: inline-flex;
            gap: 8px;
            align-items: center;
        }

        .btn-circle-edit {
            width: 36px;
            height: 36px;
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
            text-decoration: none;
        }

        .btn-circle-delete {
            width: 36px;
            height: 36px;
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

        /* Zone Link */
        .zone-link {
            color: #1e3a8a;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.15s ease;
        }

        .zone-link:hover {
            color: #3b82f6;
            text-decoration: none;
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
                    <!-- Report Summary Start -->
                    <div class="modern-zone-container">
                        <!-- Metrics Grid -->
                        <div class="metric-card-grid">
                            <!-- Card 1: Total Contract -->
                            <div class="metric-card-modern">
                                <div class="metric-card-content">
                                    <span class="metric-card-title">Total Contract</span>
                                    <h3 class="metric-card-value"><?php echo $total_zone ?></h3>
                                </div>
                                <div class="metric-card-icon-wrapper icon-green">
                                    <i class="icon-briefcase"></i>
                                </div>
                            </div>
                            
                            <!-- Card 2: Total Contractor -->
                            <div class="metric-card-modern">
                                <div class="metric-card-content">
                                    <span class="metric-card-title">Total Contractor</span>
                                    <h3 class="metric-card-value"><?php echo $total_subzone ?></h3>
                                </div>
                                <div class="metric-card-icon-wrapper icon-red">
                                    <i class="icon-people"></i>
                                </div>
                            </div>
                            
                            <!-- Card 3: Total Contract Value -->
                            <div class="metric-card-modern">
                                <div class="metric-card-content">
                                    <span class="metric-card-title">Total Contract Value</span>
                                    <h3 class="metric-card-value">RM <?php echo number_format($total_perimeter,2) ?></h3>
                                </div>
                                <div class="metric-card-icon-wrapper icon-blue">
                                    <i class="icon-wallet"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Action Banner / Header -->
                        <!-- <div class="card modern-banner bg-dark">
                            <div class="card-body p-0 d-sm-flex align-items-center justify-content-between">
                                <h4 class="modern-banner-title"><i class="icon-settings mr-2"></i> Zone Settings & Management</h4>
                                <a href="add_zone.php" class="modern-btn-white mt-3 mt-sm-0"><i class="icon-plus"></i> Add New Zone</a>
                            </div>
                        </div> -->

                        <!-- Table Section -->
                        <div class="row">
                            <div class="col-md-12 grid-margin stretch-card">
                                <div class="card shadow">
                                    <div class="card-body">
                                        <div class="d-sm-flex align-items-center justify-content-between mb-3">
                                            <h4 class="card-title">Contract Listing</h4>
                                        </div>
                                        <div class="modern-table-wrapper">
                                            <table class="modern-table">
                                                <thead>
                                                    <tr>
                                                        <th>Contract Number</th>
                                                        <th>Contractor Name</th>
                                                        <th>Contract Value</th>
                                                        <th>Zone</th>
                                                        <th style="width: 120px; text-align: center;">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $sql = "SELECT * FROM contract ORDER BY contract_number ASC";
                                                    $res = $conn->query($sql);
                                                    if ($res->num_rows > 0) {
                                                        while ($row = $res->fetch_assoc()) {
                                                            // Total subzone by zone
                                                            $sql1 = "SELECT * FROM subzone WHERE sz_id = '" . $row['sz_id'] . "'";
                                                            $res1 = $conn->query($sql1);
                                                            $row1 = $res1->fetch_assoc();

                                                            // Total Perimeter(m) by zone
                                                            $sql2 = "SELECT * FROM zone WHERE zone_id = '" . $row1['zone_id'] . "'";
                                                            $res2 = $conn->query($sql2);
                                                            $row2 = $res2->fetch_assoc();

                                                            // Total Area(ha) by zone
                                                            $sql3 = "SELECT SUM(fund) AS total_value FROM contract WHERE contract_id = '" . $row['contract_id'] . "'";
                                                            $res3 = $conn->query($sql3);
                                                            $row3 = $res3->fetch_assoc();
                                                            $total_value = $row3['total_value'];
                                                            
                                                            // Contractor name
                                                            $sql4 = "SELECT * FROM contractor WHERE cid = '" . $row['cid'] . "'";
                                                            $res4 = $conn->query($sql4);
                                                            $row4 = $res4->fetch_assoc();
                                                    ?>
                                                            <tr>
                                                                <td>
                                                                    <a class="zone-link" href="contract_details.php?id=<?php echo $row['contract_id'] ?>">
                                                                        <?php echo htmlspecialchars($row['contract_number']) ?>
                                                                    </a>
                                                                </td>
                                                                <td class="font-weight-bold text-dark"><?php echo $row4['name'] ?></td>
                                                                <td>RM <?php echo number_format($total_value, 2) ?></td>
                                                                <td><?php echo $row2['zone_name']  ?></td>
                                                                <td style="text-align: center;">
                                                                    <div class="action-btn-group">
                                                                        <a href="update_zone.php?id=<?php  ?>" class="btn-circle-edit" title="Edit Zone">
                                                                            <i class="fa fa-pencil"></i>
                                                                        </a>
                                                                        <button type="button" class="btn-circle-delete" data-id="<?php  ?>" onclick="deleteDb(this)" title="Delete Zone">
                                                                            <i class="fa fa-trash"></i>
                                                                        </button>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        <?php }
                                                    } else { ?>
                                                        <tr>
                                                            <td colspan="5" class="text-center text-danger font-weight-bold py-4">
                                                                <i class="icon-info mr-2"></i> No zone data available.
                                                            </td>
                                                        </tr>
                                                    <?php } ?>
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
    <script>
        function deleteDb(btn) {

            let id = btn.dataset.id;
            let row = btn.closest("tr");

            // ambil nama contractor
            let zone = row.cells[0].innerText.trim();

            Swal.fire({
                title: "Delete Subzone?",
                html: "Are you sure want to delete <b>" + zone + "</b> and all it subzone ?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, delete",
                cancelButtonText: "Cancel"
            }).then((result) => {

                if (result.isConfirmed) {

                    fetch("includes/delete_zone.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: "id=" + id
                        })
                        .then(res => res.text())
                        .then(data => {

                            if (data.trim() == "success") {

                                Swal.fire({
                                    title: "Deleted!",
                                    text: zone + " has been removed.",
                                    icon: "success"
                                }).then(() => {
                                    location.reload(); // refresh page
                                });

                            } else {

                                Swal.fire(
                                    "Error!",
                                    "Failed to delete subzone.",
                                    "error"
                                );

                            }

                        });

                }

            });

        }
    </script>

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


</body>

</html>
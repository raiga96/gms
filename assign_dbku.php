<?php
session_start();
$subzone_id = $_GET['szid'];

require 'includes/connection.php';

// Total sub-zone info
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
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');

        .modern-assign-container {
            font-family: 'Outfit', sans-serif;
            color: #334155;
        }

        /* Modern Banner / Header */
        .modern-banner {
            background: linear-gradient(90deg, #1d384d 0%, #00311c 100%);
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
            font-size: 13px;
            color: rgba(255, 255, 255, 0.7);
            margin: 4px 0 0 0;
        }

        /* Metric Cards Styling */
        .metric-card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }

        .metric-card-modern {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 18px 20px;
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
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }

        .metric-card-icon-wrapper {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 18px;
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

        /* Modern Card Details */
        .modern-assign-container .card {
            border: none;
            border-radius: 16px !important;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05), 0 5px 15px -8px rgba(0, 0, 0, 0.05) !important;
            background: #ffffff;
            overflow: hidden;
            margin-bottom: 24px;
        }

        .modern-assign-container .card-body {
            padding: 28px !important;
        }

        .modern-assign-container .card-title {
            font-size: 16px;
            font-weight: 700;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .form-section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }

        /* Custom Form Controls */
        .modern-assign-container .form-group {
            margin-bottom: 0px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .modern-assign-container label {
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 0px;
        }

        .modern-assign-container .form-control {
            border: 1px solid #cbd5e1;
            border-radius: 10px !important;
            padding: 10px 14px;
            font-size: 14px;
            font-weight: 500;
            color: #1e293b;
            background-color: #ffffff;
            transition: all 0.2s ease;
            height: auto;
        }

        .modern-assign-container select.form-control {
            padding: 10px 14px !important;
            cursor: pointer;
        }

        .modern-assign-container .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
            outline: none;
            background-color: #ffffff;
            color: #1e293b;
        }

        /* Alerts Styling */
        .modern-alert {
            background-color: #fff1f2;
            border: 1px solid #fecdd3;
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            color: #be123c;
            margin-bottom: 24px;
            box-shadow: 0 4px 12px rgba(244, 63, 94, 0.05);
        }

        .modern-alert-icon {
            font-size: 28px;
        }

        .modern-alert-title {
            font-weight: 700;
            margin: 0 0 4px 0;
            font-size: 15px;
        }

        .modern-alert-desc {
            margin: 0;
            font-size: 13px;
            color: #e11d48;
        }

        /* Buttons styling */
        .btn-modern-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff !important;
            font-weight: 600;
            font-size: 13px;
            padding: 10px 24px;
            border-radius: 30px;
            border: none;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-modern-success:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.3);
            text-decoration: none;
        }

        .btn-modern-danger {
            background: #f1f5f9;
            color: #475569 !important;
            font-weight: 600;
            font-size: 13px;
            padding: 10px 24px;
            border-radius: 30px;
            border: 1px solid #cbd5e1;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-modern-danger:hover {
            background-color: #e2e8f0;
            color: #334155 !important;
            transform: translateY(-1px);
            text-decoration: none;
        }
    </style>
    <link rel="shortcut icon" href="assets/images/favicon.png" />
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
                    $sql = "SELECT * FROM subzone AS a JOIN zone AS b ON b.zone_id = a.zone_id WHERE a.sz_id = '$subzone_id'";
                    $res = $conn->query($sql);
                    $row = $res->fetch_assoc();
                    $zone_name = $row['zone_name'];
                    $subzone_name = $row['sz_name'];
                    $zone_id = $row['zone_id'];

                    // Check if there is an active contract to assign SPV & PGS to
                    $sql_contract = "SELECT * FROM contract WHERE sz_id = '$subzone_id' AND c_status = 'Y'";
                    $res_contract = $conn->query($sql_contract);
                    $has_contract = ($res_contract && $res_contract->num_rows > 0);
                    
                    $current_spv = '';
                    $current_pgs = '';
                    if ($has_contract) {
                        $contract_row = $res_contract->fetch_assoc();
                        $current_spv = $contract_row['spv_id'];
                        $current_pgs = $contract_row['pgs_id'];
                    }
                    ?>

                    <div class="modern-assign-container">
                        <!-- Action Banner / Header -->
                        <div class="card modern-banner bg-dark">
                            <div class="card-body p-0 d-sm-flex align-items-center justify-content-between text-white">
                                <div>
                                    <h3 class="modern-banner-title"><i class="icon-user-following mr-2"></i> Assign DBKU Staff</h3>
                                    <p class="modern-banner-subtitle">Configure Supervisor and Pengawas assignments for <?php echo htmlspecialchars($subzone_name) ?></p>
                                </div>
                                <button type="button" class="modern-btn-white mt-3 mt-sm-0" onclick="history.back()"><i class="icon-arrow-left"></i> Back</button>
                            </div>
                        </div>

                        <?php if (!$has_contract) { ?>
                            <!-- Alert: No active contract found -->
                            <div class="modern-alert">
                                <div class="modern-alert-icon">
                                    <i class="icon-info"></i>
                                </div>
                                <div>
                                    <h5 class="modern-alert-title">Active Contract Required</h5>
                                    <p class="modern-alert-desc">You cannot assign DBKU staff because there is no active contractor contract assigned to this sub-zone yet. Please assign a contractor first.</p>
                                </div>
                            </div>
                            <div class="d-flex justify-content-start mt-3">
                                <button class="btn-modern-danger" type="button" onclick="history.back()"><i class="icon-close"></i> Go Back</button>
                            </div>
                        <?php } else { ?>
                            <!-- Assignment Form -->
                            <div class="card">
                                <div class="card-body">
                                    <div class="form-section-header">
                                        <h4 class="card-title"><i class="icon-badge text-primary mr-1"></i> Staff Assignment</h4>
                                    </div>
                                    
                                    <form action="includes/assign_dbku_action.php" method="POST" class="forms-sample">
                                        <input type="text" name="zid" value="<?php echo $zone_id ?>" hidden>
                                        <input type="text" name="sz_id" value="<?php echo $subzone_id ?>" hidden>

                                        <div class="row g-3">
                                            <!-- Supervisor (SPV) Select -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="spv_id">Supervisor (SPV)</label>
                                                    <select name="spv_id" id="spv_id" class="form-control">
                                                        <option value="">-- Select Supervisor --</option>
                                                        <?php
                                                        $sql_spv = "SELECT * FROM users WHERE user_type = 'SPV' AND status = 'Y' ORDER BY user_id ASC";
                                                        $res_spv = $conn->query($sql_spv);
                                                        if ($res_spv && $res_spv->num_rows > 0) {
                                                            while ($row_spv = $res_spv->fetch_assoc()) {
                                                                $selected = ($row_spv['uid'] == $current_spv) ? 'selected' : '';
                                                                echo "<option value='{$row_spv['uid']}' {$selected}>" . htmlspecialchars($row_spv['user_id']) . " (" . htmlspecialchars($row_spv['user_email']) . ")</option>";
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Pengawas (PGS) Select -->
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="pgs_id">Pengawas (PGS)</label>
                                                    <select name="pgs_id" id="pgs_id" class="form-control">
                                                        <option value="">-- Select Pengawas --</option>
                                                        <?php
                                                        $sql_pgs = "SELECT * FROM users WHERE user_type = 'PGS' AND status = 'Y' ORDER BY user_id ASC";
                                                        $res_pgs = $conn->query($sql_pgs);
                                                        if ($res_pgs && $res_pgs->num_rows > 0) {
                                                            while ($row_pgs = $res_pgs->fetch_assoc()) {
                                                                $selected = ($row_pgs['uid'] == $current_pgs) ? 'selected' : '';
                                                                echo "<option value='{$row_pgs['uid']}' {$selected}>" . htmlspecialchars($row_pgs['user_id']) . " (" . htmlspecialchars($row_pgs['user_email']) . ")</option>";
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Submission Row -->
                                        <div class="action-button-group mt-4" style="display: flex; gap: 12px; justify-content: flex-end; border-top: 1px solid #e2e8f0; padding-top: 20px;">
                                            <button class="btn-modern-danger" type="button" onclick="history.back()"><i class="icon-close"></i> Cancel</button>
                                            <button type="submit" class="btn-modern-success"><i class="icon-check"></i> Assign Staff</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <!-- content-wrapper ends -->
                <!-- partial:partials/_footer.html -->
                <?php include 'partials/footer.php'; ?>
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

</body>

</html>

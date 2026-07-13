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

        /* Profile details view */
        .profile-summary-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 18px;
            display: flex;
            gap: 16px;
            align-items: center;
            height: 100%;
        }

        .profile-summary-icon {
            width: 54px;
            height: 54px;
            background: #eff6ff;
            color: #3b82f6;
            border-radius: 12px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 24px;
        }

        .profile-meta-title {
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin: 0 0 4px 0;
        }

        .profile-meta-value {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }

        .profile-meta-sub {
            font-size: 12px;
            color: #64748b;
            margin: 2px 0 0 0;
        }

        .info-card-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 18px;
            height: 100%;
        }

        .info-card-item {
            margin-bottom: 12px;
        }

        .info-card-item:last-child {
            margin-bottom: 0;
        }

        /* Buttons styling */
        .btn-modern-primary {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff !important;
            font-weight: 600;
            font-size: 13px;
            padding: 10px 24px;
            border-radius: 30px;
            border: none;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-modern-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.3);
            text-decoration: none;
        }

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

                    <!-- Performance Indicator Start -->
                    <?php
                    $sql = "SELECT * FROM subzone AS a JOIN zone AS b ON b.zone_id = a.zone_id WHERE a.sz_id = '$subzone_id'";
                    $res = $conn->query($sql);
                    $row = $res->fetch_assoc();
                    $zone_name = $row['zone_name'];
                    $subzone_name = $row['sz_name'];
                    $zone_id = $row['zone_id'];
                    ?>

                    <div class="modern-assign-container">
                        <!-- Action Banner / Header -->
                        <div class="card modern-banner bg-dark">
                            <div class="card-body p-0 d-sm-flex align-items-center justify-content-between text-white">
                                <div>
                                    <h3 class="modern-banner-title"><i class="icon-user-follow mr-2"></i> Assign Contractor</h3>
                                    <p class="modern-banner-subtitle">Configure contractor details and assign work for <?php echo htmlspecialchars($subzone_name) ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Metrics Grid -->
                        <div class="metric-card-grid">
                            <!-- Card 1: Sub-Zone Perimeter -->
                            <div class="metric-card-modern">
                                <div class="metric-card-content">
                                    <span class="metric-card-title">Perimeter</span>
                                    <h3 class="metric-card-value"><?php echo number_format($total_perimeter) ?> m</h3>
                                </div>
                                <div class="metric-card-icon-wrapper icon-blue">
                                    <i class="icon-size-fullscreen"></i>
                                </div>
                            </div>
                            
                            <!-- Card 2: Sub-Zone Area -->
                            <div class="metric-card-modern">
                                <div class="metric-card-content">
                                    <span class="metric-card-title">Area</span>
                                    <h3 class="metric-card-value"><?php echo number_format($total_area, 2) ?> ha</h3>
                                </div>
                                <div class="metric-card-icon-wrapper icon-yellow">
                                    <i class="icon-pie-chart"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Step 1: Choose Contractor Select -->
                        <div class="card">
                            <div class="card-body">
                                <div class="form-section-header">
                                    <h4 class="card-title"><i class="icon-people text-primary mr-1"></i> Choose Contractor</h4>
                                    <a class="btn-modern-primary py-2 px-3" href="new_contractor.php"><i class="icon-plus"></i> New Contractor</a>
                                </div>
                                <div class="form-group mt-2">
                                    <label for="contractorSelect">Select Contractor Profile</label>
                                    <select name="contractor_id" id="contractorSelect" class="form-control">
                                        <option value="" selected>-- Select Contractor --</option>
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
                        </div>

                        <!-- Step 2: Contractor Details Form (Displays Dynamically) -->
                        <div id="contractorDetails" style="display: none; flex-direction: column; gap: 24px;">
                            <!-- Contractor Profile Information Card -->
                            <div class="card">
                                <div class="card-body">
                                    <div class="form-section-header mb-3">
                                        <h4 class="card-title"><i class="icon-badge text-primary mr-1"></i> Profile Information</h4>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <div class="profile-summary-box">
                                                <div class="profile-summary-icon">
                                                    <i class="icon-user"></i>
                                                </div>
                                                <div>
                                                    <p class="profile-meta-title">Contractor</p>
                                                    <h5 class="profile-meta-value" id="contractorName">-</h5>
                                                    <p class="profile-meta-sub">PIC: <span id="contractorId">-</span></p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="info-card-box">
                                                <div class="info-card-item">
                                                    <p class="profile-meta-title">Email Address</p>
                                                    <h6 class="profile-meta-value text-primary"><i class="fa fa-envelope me-1"></i> <span id="contractorEmail">--</span></h6>
                                                </div>
                                                <div class="info-card-item">
                                                    <p class="profile-meta-title">Phone Number</p>
                                                    <h6 class="profile-meta-value"><i class="icon-phone me-1"></i> <span id="contractorPhone">--</span></h6>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="info-card-box">
                                                <p class="profile-meta-title">Business Address</p>
                                                <h6 class="profile-meta-value text-secondary" style="font-size:13px; line-height:1.4;" id="contractorAddress">--</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Assignment Parameters Input Card -->
                            <div class="card">
                                <div class="card-body">
                                    <div class="form-section-header">
                                        <h4 class="card-title"><i class="icon-doc text-primary mr-1"></i> Assignment Parameters</h4>
                                    </div>
                                    
                                    <form action="includes/assign_contractor_action.php" method="POST" class="forms-sample">
                                        <input type="text" name="zid" value="<?php echo $zone_id ?>" hidden>
                                        <input type="text" name="sz_id" value="<?php echo $subzone_id ?>" hidden>
                                        <input type="text" name="cid" id="contractorIdc" hidden>

                                        <div class="row g-3">
                                            <!-- Contract Number & Dates -->
                                            <div class="col-md-4">
                                                <div class="form-group mb-3">
                                                    <label for="contract_number">Contract Number</label>
                                                    <input type="text" name="contract_number" id="contract_number" class="form-control" placeholder="Enter Contract Number" required>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6 form-group">
                                                        <label for="start_date">Start Date</label>
                                                        <input type="date" name="start_date" id="start_date" class="form-control" required>
                                                    </div>
                                                    <div class="col-md-6 form-group">
                                                        <label for="end_date">End Date</label>
                                                        <input type="date" name="end_date" id="end_date" class="form-control" required>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Supervisors -->
                                            <div class="col-md-4">
                                                <div class="row g-2 mb-3">
                                                    <label class="col-12">Supervisor 1</label>
                                                    <div class="col-md-6 form-group">
                                                        <input type="text" name="supervisor1_name" class="form-control" placeholder="Name">
                                                    </div>
                                                    <div class="col-md-6 form-group">
                                                        <input type="text" name="supervisor1_contact" class="form-control" placeholder="Contact Number">
                                                    </div>
                                                </div>
                                                <div class="row g-2">
                                                    <label class="col-12">Supervisor 2</label>
                                                    <div class="col-md-6 form-group">
                                                        <input type="text" name="supervisor2_name" class="form-control" placeholder="Name">
                                                    </div>
                                                    <div class="col-md-6 form-group">
                                                        <input type="text" name="supervisor2_contact" class="form-control" placeholder="Contact Number">
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Funding & Remarks -->
                                            <div class="col-md-4">
                                                <div class="form-group mb-3">
                                                    <label for="fund">Fund</label>
                                                    <input type="text" name="fund" id="fund" class="form-control" placeholder="Enter Fund Details">
                                                </div>
                                                <div class="form-group">
                                                    <label for="remarks">Remarks</label>
                                                    <textarea name="remarks" id="remarks" cols="30" rows="3" class="form-control" placeholder="Enter Remarks"></textarea>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Submission Row -->
                                        <div class="action-button-group mt-4">
                                            <button class="btn-modern-danger" type="button" onclick="history.back()"><i class="icon-close"></i> Cancel</button>
                                            <button type="submit" class="btn-modern-success"><i class="icon-check"></i> Assign Contractor</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Jika ada contractor row ini akan appear -->
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
    <script>
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
    </script>

    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAM9KnXl4SMmy68M2syalpDyMfZeNItPjk&callback=initMap"></script>

</body>

</html>
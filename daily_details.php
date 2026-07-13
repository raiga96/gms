<?php
session_start();
$cid = $_GET['id'];

require 'includes/connection.php';


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

        .modern-record-container {
            font-family: 'Outfit', sans-serif;
            color: #334155;
        }

        .modern-record-container .card {
            border: none;
            border-radius: 16px !important;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05), 0 5px 15px -8px rgba(0, 0, 0, 0.05) !important;
            background: #ffffff;
            margin-bottom: 24px;
            overflow: hidden;
        }

        .modern-record-container .card-title {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
            color: #0f172a;
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
            text-align: center;
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
        
        .modern-table td a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s ease;
        }
        
        .modern-table td a:hover {
            color: #2563eb;
        }
        .modern-contractor-banner {
            background: linear-gradient(90deg, #1d384d 0%, #007442 100%);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
            border: none;
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
                    $sql = "SELECT * FROM contract WHERE contract_id = '$cid'";
                    $res = $conn->query($sql);
                    $row = $res->fetch_assoc();
                    $contract_id = $row['contract_id'];

                    $sql1 = "SELECT * FROM contractor WHERE cid = '".$row['cid']."'";
                    $res1 = $conn -> query($sql1);
                    $row1 = $res1 -> fetch_assoc();
                    $contractor = $row1['name'];
                    $email = $row1['email'];
                    ?>

                    <div class="row mt-4 mb-0">
                        <div class="col-12">
                            <div class="card  modern-contractor-banner shadow border-0">
                                <div class="card-body p-0 d-sm-flex align-items-center justify-content-between">
                                    <div class="mb-3 mb-sm-0">
                                        <h3 class="mb-1 text-white fw-bold"><?php echo $row1['name'] ?></h3>
                                        <h5 class="mb-1 text-white fw-bold"><?php echo $row1['address'] ?></h5>
                                    </div>
                                    <div class="btn-group gap-2">
                                        <a class="btn btn-light shadow-sm text-primary" href="update_contractor.php?id=<?php echo $cid ?>">Update</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php
                    // Anda boleh gunakan data kontrak ini untuk dipaparkan di UI
                    $sql1 = "SELECT * FROM contract WHERE contract_id = '" . $row['contract_id'] . "'";
                    $res1 = $conn->query($sql1);
                    $row1 = $res1->fetch_assoc();

                    // --- Session Filter Logic ---
                    $current_year = date("Y");
                    $current_month = date("n");
                    $current_day = date("j");
                    $current_session_default = ($current_month - 1) * 2 + ($current_day <= 15 ? 1 : 2);

                    $filter_year = isset($_GET['filter_year']) ? $_GET['filter_year'] : '';
                    $filter_session = isset($_GET['filter_session']) ? $_GET['filter_session'] : '';

                    $avail_sql = "SELECT DISTINCT YEAR(date) as yr FROM daily_records WHERE cid = '$contract_id' ORDER BY yr DESC";
                    $avail_res = $conn->query($avail_sql);
                    $available_years = [];
                    if ($avail_res && $avail_res->num_rows > 0) {
                        while($ar = $avail_res->fetch_assoc()) {
                            $available_years[] = $ar['yr'];
                        }
                    } else {
                        $available_years = [$current_year];
                    }

                    if ($filter_year == '') {
                        $filter_year = $available_years[0];
                    }
                    if ($filter_session == '') {
                        $max_sess_sql = "SELECT MAX(gc_session) as max_s FROM daily_records WHERE cid = '$contract_id' AND YEAR(date) = '$filter_year'";
                        $max_sess_res = $conn->query($max_sess_sql);
                        $max_s_row = $max_sess_res->fetch_assoc();
                        if ($max_s_row && $max_s_row['max_s']) {
                            $filter_session = $max_s_row['max_s'];
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
                    $sql_records = "SELECT * FROM daily_records WHERE cid = '$contract_id' AND date >= '$start_date' AND date <= '$end_date' ORDER BY date ASC";
                    $res_records = $conn->query($sql_records);
                    $daily_data = [];
                    if($res_records && $res_records->num_rows > 0) {
                        while($dr = $res_records->fetch_assoc()) {
                            $daily_data[$dr['date']] = $dr;
                        }
                    }
                    ?>


                    <div class="row modern-record-container">
                        <div class="col-md-12 grid-margin stretch-card">
                            <div class="card shadow">
                                <div class="card-body">
                                    <div class="d-sm-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
                                        <h4 class="card-title mb-sm-0">Site Daily Record</h4>
                                        <form method="GET" action="daily_details.php" class="d-flex align-items-center gap-2 m-0">
                                            <input type="hidden" name="id" value="<?php echo $cid; ?>">
                                            <select name="filter_year" class="form-select form-select-sm" style="min-width: 100px; border-radius: 8px;">
                                                <?php
                                                foreach($available_years as $y) {
                                                    $sel = ($y == $filter_year) ? 'selected' : '';
                                                    echo "<option value='$y' $sel>$y</option>";
                                                }
                                                ?>
                                            </select>
                                            <select name="filter_session" class="form-select form-select-sm" style="min-width: 120px; border-radius: 8px;">
                                                <?php
                                                for($i=1; $i<=24; $i++) {
                                                    $sel = ($i == $filter_session) ? 'selected' : '';
                                                    echo "<option value='$i' $sel>Session $i</option>";
                                                }
                                                ?>
                                            </select>
                                            <button type="submit" class="btn btn-primary btn-sm" style="border-radius: 8px;">Filter</button>
                                        </form>
                                    </div>
                                    <div class="modern-table-wrapper mb-2">
                                        <table class="modern-table">
                                            <thead>
                                                <tr>
                                                    <th>Dates</th>
                                                    <th>Session</th>
                                                    <th>Contractor</th>
                                                    <th>Supervisor</th>
                                                    <th>Pengawas</th>
                                                    <th>Verified</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $current = strtotime($start_date);
                                                $last = strtotime($end_date);
                                                
                                                while($current <= $last) {
                                                    $d_str = date("Y-m-d", $current);
                                                    if(isset($daily_data[$d_str])) {
                                                        $row = $daily_data[$d_str];
                                                ?>
                                                        <tr>
                                                            <td style="word-wrap: break-word;overflow-wrap: break-word; white-space: normal;">
                                                                <a href="sitedaily_records.php?id=<?php echo $row['id'] ?>">
                                                                    <?php echo date("d M Y", strtotime($row['date'])) ?>
                                                                </a>
                                                            </td>
                                                            <td style="word-wrap: break-word;overflow-wrap: break-word; white-space: normal;">
                                                                Session <?php echo htmlspecialchars($row['gc_session']) ?>
                                                            </td>
                                                            <td>
                                                                <?php echo ($row['contractor_dsign'] === 'Y') ? '<i class="fa fa-check-circle text-success" style="font-size: 18px;" title="Signed"></i>' : '<i class="fa fa-times-circle text-danger" style="font-size: 18px;" title="Not Signed"></i>'; ?>
                                                            </td>
                                                            <td>
                                                                <?php echo ($row['supervisor_dsign'] === 'Y') ? '<i class="fa fa-check-circle text-success" style="font-size: 18px;" title="Signed"></i>' : '<i class="fa fa-times-circle text-danger" style="font-size: 18px;" title="Not Signed"></i>'; ?>
                                                            </td>
                                                            <td>
                                                                <?php echo ($row['pengawas_dsign'] === 'Y') ? '<i class="fa fa-check-circle text-success" style="font-size: 18px;" title="Signed"></i>' : '<i class="fa fa-times-circle text-danger" style="font-size: 18px;" title="Not Signed"></i>'; ?>
                                                            </td>
                                                            <td>
                                                                <?php echo ($row['verified'] === 'Y') ? '<i class="fa fa-check-square text-success" style="font-size: 18px;" title="Verified"></i>' : '<i class="fa fa-minus-circle text-danger" style="font-size: 18px;" title="Not Verified"></i>'; ?>
                                                            </td>
                                                        </tr>
                                                <?php 
                                                    } else { 
                                                ?>
                                                        <tr>
                                                            <td style="color: #64748b;">
                                                                <?php echo date("d M Y", $current) ?>
                                                            </td>
                                                            <td style="color: #64748b;">
                                                                Session <?php echo htmlspecialchars($filter_session) ?>
                                                            </td>
                                                            <td colspan="4" class="text-center text-muted" style="font-style: italic;">
                                                                <span class="badge badge-outline-secondary" style="font-size: 11px; padding: 4px 8px; border: 1px dashed #f82727ff; color: #d62525ff; background-color: transparent;">No Data</span>
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

                    <!-- Modal -->
                    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel">Register Contractor ID</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form action="includes/reguser_contractor.php" method="POST" enctype="multipart/form-data">
                                        <input type="text" name="id" value="<?php echo $cid  ?>" hidden>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <label for="username" class="form-label"><small class="font-weight-bold">Name</small></label>
                                                <input type="text" class="form-control" name="fname" value="<?php echo $contractor ?>">
                                            </div>
                                            <div class="col-md-12 mt-4">
                                                <label for="username" class="form-label"><small class="font-weight-bold">Email</small></label>
                                                <input type="email" class="form-control" name="email" value="<?php echo $email ?>">
                                            </div>
                                            <hr class="mt-4">
                                            <div class="col-md-12 mt-2">
                                                <label for="username" class="form-label"><small class="font-weight-bold">Username</small></label>
                                                <input type="text" class="form-control" name="username" placeholder="Username">
                                            </div>
                                            <div class="col-md-12 mt-4">
                                                <label for="password" class="form-label"><small class="font-weight-bold">Password</small></label>
                                                <div class="input-group">
                                                    <input type="password" class="form-control" id="pwd" name="pwd" placeholder="Password">
                                                    <div class="input-group-append" style="cursor: pointer;">
                                                        <span class="input-group-text">
                                                            <i class='fa fa-eye text-dark' id="toggleIcon" onclick="togglePassword()"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-center mt-4">
                                            <button type="submit" class="btn btn-info">Register</button>
                                        </div>
                                    </form>
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
            function togglePassword() {
                const pwd = document.getElementById("pwd");
                const icon = document.getElementById("toggleIcon");
                if (pwd.type === "password") {
                    pwd.type = "text";
                    icon.classList.replace("mdi-eye", "mdi-eye-off");
                } else {
                    pwd.type = "password";
                    icon.classList.replace("mdi-eye-off", "mdi-eye");
                }
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

</body>

</html>
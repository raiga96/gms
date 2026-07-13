<?php
session_start();
$subzone_id = $_GET['szid'];

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
    <link rel="shortcut icon" href="assets/images/favicon.png" />
    <!-- End layout styles -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');

        .modern-form-container {
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

        /* Modern Form Card */
        .modern-form-container .card {
            border: none;
            border-radius: 16px !important;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05), 0 5px 15px -8px rgba(0, 0, 0, 0.05) !important;
            background: #ffffff;
            overflow: hidden;
            margin-bottom: 24px;
        }

        .modern-form-container .card-body {
            padding: 28px !important;
        }

        .forms-sample {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-section-title {
            font-size: 14px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 8px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Custom Form Controls */
        .modern-form-container .form-group {
            margin-bottom: 0px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .modern-form-container label {
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 0px;
        }

        .modern-form-container .form-control {
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

        .modern-form-container select.form-control {
            padding: 10px 14px !important;
            cursor: pointer;
        }

        .modern-form-container .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
            outline: none;
            background-color: #ffffff;
            color: #1e293b;
        }

        /* Map Container */
        .map-preview-wrapper {
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #cbd5e1;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
            margin-top: 5px;
        }

        /* Custom Alert Banner */
        .kmz-alert-banner {
            background-color: #fff1f2;
            border: 1px solid #fecdd3;
            border-radius: 10px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #be123c;
            font-size: 13px;
            font-weight: 500;
            margin-top: 10px;
        }

        .kmz-alert-banner i {
            font-size: 16px;
        }

        /* Action Buttons styling */
        .action-button-group {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 15px;
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
        }

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

        /* Modal styling */
        .modal-content {
            border: none;
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
        }

        .modal-header {
            border-bottom: 1px solid #e2e8f0;
            background-color: #f8fafc;
            border-top-left-radius: 16px;
            border-top-right-radius: 16px;
            padding: 18px 24px;
        }

        .modal-title {
            font-weight: 700;
            color: #0f172a;
            font-size: 16px;
        }

        .modal-body {
            padding: 24px;
        }

        .modal-footer {
            border-top: 1px solid #e2e8f0;
            padding: 16px 24px;
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

                    <div class="modern-form-container">
                        <!-- Action Banner / Header -->
                        <div class="card modern-banner bg-dark">
                            <div class="card-body p-0 d-sm-flex align-items-center justify-content-between">
                                <div>
                                    <h3 class="modern-banner-title"><i class="icon-note mr-2"></i> Edit Sub-Zone Details</h3>
                                    <p class="modern-banner-subtitle">Update sub-zone boundaries, perimeter, area, and KMZ file</p>
                                </div>
                                <button type="button" class="modern-btn-white mt-3 mt-sm-0" data-bs-toggle="modal" data-bs-target="#historyModal"><i class="icon-clock"></i> View History</button>
                            </div>
                        </div>

                        <?php
                        $sql = "SELECT * FROM subzone WHERE sz_id = '$subzone_id'";
                        $res = $conn->query($sql);
                        $row = $res->fetch_assoc();
                        ?>

                        <!-- Main Form Card -->
                        <div class="card">
                            <div class="card-body">
                                <form class="forms-sample" action="includes/update_subzone.php" method="POST">
                                    <input type="text" name="subzone_id" value="<?php echo $subzone_id ?>" hidden>
                                    
                                    <div class="form-section-title">
                                        <i class="icon-info text-primary animate-pulse"></i> General Information
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="subzone_name">Sub-Zone Name</label>
                                                <input type="text" class="form-control" name="subzone" id="subzone_name" value="<?php echo htmlspecialchars($row['sz_name'] ?? '') ?>" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-3">
                                        <!-- Zone selection dropdown -->
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="zone_id">Zone</label>
                                                <select name="zone_id" id="zone_id" class="form-control">
                                                    <?php
                                                    $sql_zones = "SELECT * FROM zone";
                                                    $res_zones = $conn->query($sql_zones);
                                                    if ($res_zones && $res_zones->num_rows > 0) {
                                                        while ($row_zone = $res_zones->fetch_assoc()) {
                                                            $selected = ($row_zone['zone_id'] == $row['zone_id']) ? 'selected' : '';
                                                            echo "<option value='{$row_zone['zone_id']}' {$selected}>{$row_zone['zone_name']}</option>";
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Perimeter -->
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="perimeter">Perimeter (m)</label>
                                                <input type="number" step="any" class="form-control" name="perimeter" id="perimeter" value="<?php echo htmlspecialchars($row['perimeter'] ?? '') ?>">
                                            </div>
                                        </div>

                                        <!-- Area -->
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="area">Area (ha)</label>
                                                <input type="number" step="any" class="form-control" name="area" id="area" value="<?php echo htmlspecialchars($row['area'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-section-title mt-4">
                                        <i class="icon-map text-primary"></i> Location Map Preview
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="map-preview-wrapper">
                                                <div id="map" style="width: 100%; height: 45vh;"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- KMZ file status alert -->
                                    <?php
                                    $sql_kmz = "SELECT * FROM kmz_file WHERE sz_id = '$subzone_id' AND kmz_status = 'Y'";
                                    $res_kmz = $conn->query($sql_kmz);
                                    $has_kmz = ($res_kmz && $res_kmz->num_rows > 0);
                                    if (!$has_kmz) {
                                    ?>
                                        <div class="kmz-alert-banner">
                                            <i class="icon-exclamation"></i>
                                            <span>No KMZ boundary file found for this sub-zone. You can upload one below.</span>
                                        </div>
                                    <?php } ?>

                                    <!-- Action Button Group -->
                                    <div class="action-button-group">
                                        <button class="btn-modern-danger" type="button" onclick="history.back()"><i class="icon-close"></i> Cancel</button>
                                        <?php if ($has_kmz) { ?>
                                            <button type="button" class="btn-modern-success" data-bs-toggle="modal" data-bs-target="#exampleModal"><i class="icon-cloud-upload"></i> Update KMZ</button>
                                        <?php } ?>
                                        <button type="submit" class="btn-modern-primary"><i class="icon-check"></i> Save Changes</button>
                                    </div>
                                </form>

                                <!-- In case no KMZ file, provide upload form directly on page -->
                                <?php if (!$has_kmz) { ?>
                                    <form action="includes/upload_kmz.php" class="pt-4 border-top mt-4" method="POST" enctype="multipart/form-data">
                                        <div class="form-section-title">
                                            <i class="icon-cloud-upload text-primary"></i> Upload KMZ Boundary File
                                        </div>
                                        <input type="text" name="zone_id" value="<?php echo $row['zone_id'] ?>" hidden>
                                        <input type="text" name="subzone_id" value="<?php echo $subzone_id ?>" hidden>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="new_kmz_file">Select KMZ File</label>
                                                    <input type="file" class="form-control" name="files[]" id="new_kmz_file" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-end mt-3">
                                            <button type="submit" class="btn-modern-primary"><i class="icon-cloud-upload"></i> Upload KMZ File</button>
                                        </div>
                                    </form>
                                <?php } ?>
                            </div>
                        </div>

                        <!-- Update KMZ Modal -->
                        <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLabel"><i class="icon-cloud-upload mr-2 text-primary"></i> Update KMZ Boundary File</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="includes/update_kmz.php" method="POST" enctype="multipart/form-data">
                                        <div class="modal-body">
                                            <input type="text" name="zone_id" value="<?php echo $row['zone_id'] ?>" hidden>
                                            <input type="text" name="subzone_id" value="<?php echo $subzone_id ?>" hidden>
                                            <div class="form-group">
                                                <label for="update_kmz_file" class="form-label">Select New KMZ File</label>
                                                <input type="file" class="form-control" name="files[]" id="update_kmz_file" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn-modern-danger py-2" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn-modern-primary py-2"><i class="icon-cloud-upload"></i> Upload & Update</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- History KMZ Modal -->
                        <div class="modal fade" id="historyModal" tabindex="-1" role="dialog" aria-labelledby="historyModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="historyModalLabel"><i class="icon-clock mr-2 text-primary"></i> KMZ Upload History</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>File Name</th>
                                                        <th style="width: 150px;">Upload Date</th>
                                                        <th style="width: 100px; text-align: center;">Status</th>
                                                        <th style="width: 100px; text-align: center;">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $history_sql = "SELECT * FROM kmz_file WHERE sz_id = '$subzone_id' ORDER BY add_date DESC";
                                                    $history_res = $conn->query($history_sql);
                                                    if ($history_res && $history_res->num_rows > 0) {
                                                        while ($h_row = $history_res->fetch_assoc()) {
                                                            $status_badge = ($h_row['kmz_status'] == 'Y') 
                                                                ? '<span class="badge bg-success text-white">Active</span>' 
                                                                : '<span class="badge bg-secondary text-white">Inactive</span>';
                                                    ?>
                                                            <tr>
                                                                <td class="text-wrap" style="max-width: 250px; font-weight: 500;">
                                                                    <i class="icon-doc text-secondary mr-2"></i><?php echo htmlspecialchars($h_row['kmz_file']) ?>
                                                                </td>
                                                                <td>
                                                                    <small class="text-muted"><?php echo htmlspecialchars($h_row['add_date']) ?></small>
                                                                </td>
                                                                <td style="text-align: center;">
                                                                    <?php echo $status_badge ?>
                                                                </td>
                                                                <td style="text-align: center;">
                                                                    <a href="<?php echo htmlspecialchars($h_row['url']) ?>" class="btn btn-sm btn-outline-primary py-1 px-2" target="_blank" download>
                                                                        <i class="icon-cloud-download"></i> Download
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                    <?php
                                                        }
                                                    } else {
                                                    ?>
                                                        <tr>
                                                            <td colspan="4" class="text-center text-muted py-4">
                                                                <i class="icon-info mr-2"></i> No history records available.
                                                            </td>
                                                        </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn-modern-danger py-2" data-bs-dismiss="modal">Close</button>
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
                $sql = "SELECT * FROM kmz_file WHERE sz_id = '$subzone_id' AND kmz_status = 'Y'";
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
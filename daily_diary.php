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
    <link rel="stylesheet" href="assets/css/modern-dashboard.css">
    <!-- End layout styles -->
    <link rel="shortcut icon" href="assets/images/favicon.png" />
    <style>
        .border-box {
            border: 1px solid #ced4da;
            border-radius: 8px;
            padding: 15px;
            background: #ffffff;
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
                    $sql = "SELECT * FROM contractor WHERE cid = '$cid'";
                    $res = $conn->query($sql);
                    $row = $res->fetch_assoc();
                    $contractor = $row['name'];
                    $email = $row['email'];
                    ?>

                    <div class="row mt-4 mb-0">
                        <div class="col-12">
                            <div class="card shadow border-0" style="background: linear-gradient(90deg, #1d384d 0%, #00311c 100%);">
                                <div class="card-body d-sm-flex align-items-center justify-content-between text-white">
                                    <div class="mb-3 mb-sm-0">
                                        <h2 class="mb-1 text-white fw-bold"><?php echo $row['name'] ?></h2>
                                        <h4 class="mb-1 text-white fw-bold"><?php echo $row['address'] ?></h4>
                                    </div>
                                    <div class="btn-group gap-2">
                                        <a class="btn btn-light shadow-sm text-primary" href="update_contractor.php?id=<?php echo $cid ?>">Kemaskini</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php
                    // Anda boleh gunakan data kontrak ini untuk dipaparkan di UI
                    $sql1 = "SELECT * FROM contractor WHERE cid = '" . $row['cid'] . "'";
                    $res1 = $conn->query($sql1);
                    $row1 = $res1->fetch_assoc();
                    ?>


                    <div class="row">
                        <div class="col-md-12 grid-margin stretch-card">
                            <div class="card shadow">
                                <div class="card-body">
                                    <div class="d-sm-flex align-items-center mb-4">
                                        <h4 class="card-title mb-sm-0">Rekod Harian Tapak</h4>
                                    </div>
                                    <div class="table-responsive border rounded p-1">
                                        <table class="table table-hover table-striped">
                                            <thead class="thead-dark">
                                                <th>
                                                    <h5>Tajuk</h5>
                                                </th>
                                                <th>
                                                    <h5>Nombor Kontrak</h5>
                                                </th>
                                                <th>
                                                    <h5>Penyelia</h5>
                                                </th>
                                                <th>
                                                    <h5>Mandur</h5>
                                                </th>
                                                <th>
                                                    <h5>Tempoh</h5>
                                                </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $sql = "SELECT * FROM contract WHERE cid = '$cid' ORDER BY contract_number ASC";
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

                                                        $start = new DateTime($row['start_date']);
                                                        $end = new DateTime($row['end_date']);

                                                        $interval = $start->diff($end);

                                                        // kira jumlah bulan
                                                        $duration_months = ($interval->y * 12) + $interval->m;

                                                        // jika ada baki hari lebih dari 0, tambah 1 bulan
                                                        if ($interval->d > 0) {
                                                            $duration_months++;
                                                        }


                                                ?>

                                                        <tr style="height:60px">
                                                            <td style="width: 500px;word-wrap: break-word;overflow-wrap: break-word; white-space: normal;">
                                                                <a href="daily_details.php?id=<?php echo $row['contract_id']; ?>">
                                                            <h6 class="text-uppercase">KERJA-KERJA MEMOTONG RUMPUT DAN MEMBERSIHKAN LONGKANG DALAM KAWASAN DBKU ZON <?php echo $row2['zone_name'] ?></h6>
                                                                </a>
                                                            </td>
                                                            <td style="width: 250px;word-wrap: break-word;overflow-wrap: break-word; white-space: normal;">
                                                                <h6><?php echo $row['contract_number'] ?></h6>
                                                            </td>
                                                            <td>
                                                                <h6><?php echo $row['supervisor_1'] ?></h6>
                                                                <small class="mb-2"><?php echo $row['supervisor1_contact'] ?></small>
                                                                <div class="mt-3"></div>
                                                                <h6><?php echo $row['supervisor_2'] ?></h6>
                                                                <small><?php echo $row['supervisor2_contact'] ?></small>
                                                            </td>
                                                            <td>
                                                                <h5><?php  ?></h5>
                                                            </td>
                                                            <td style="width:220px">
                                                                <h6><?php echo gms_format_date($row['start_date'], 'd M Y') ?> - <?php echo gms_format_date($row['end_date'], 'd M Y') ?></h6>
                                                                <small><?php echo $duration_months ?> bulan</small>
                                                            </td>
                                                        </tr>
                                                    <?php }
                                                } else { ?>
                                                    <tr>
                                                        <td colspan="6" class="text-center text-danger">
                                                            <h5 class="font-weight-bold">Tiada data tersedia</h5>
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

                    <!-- Modal -->
                    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel">Daftar ID Kontraktor</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                </div>
                                <div class="modal-body">
                                    <form action="includes/reguser_contractor.php" method="POST" enctype="multipart/form-data">
                                        <input type="text" name="id" value="<?php echo $cid  ?>" hidden>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <label for="username" class="form-label"><small class="font-weight-bold">Nama</small></label>
                                                <input type="text" class="form-control" name="fname" value="<?php echo $contractor ?>">
                                            </div>
                                            <div class="col-md-12 mt-4">
                                                <label for="username" class="form-label"><small class="font-weight-bold">E-mel</small></label>
                                                <input type="email" class="form-control" name="email" value="<?php echo $email ?>">
                                            </div>
                                            <hr class="mt-4">
                                            <div class="col-md-12 mt-2">
                                                <label for="username" class="form-label"><small class="font-weight-bold">Nama Pengguna</small></label>
                                                <input type="text" class="form-control" name="username" placeholder="Nama pengguna">
                                            </div>
                                            <div class="col-md-12 mt-4">
                                                <label for="password" class="form-label"><small class="font-weight-bold">Kata Laluan</small></label>
                                                <div class="input-group">
                                                    <input type="password" class="form-control" id="pwd" name="pwd" placeholder="Kata laluan">
                                                    <div class="input-group-append" style="cursor: pointer;">
                                                        <span class="input-group-text">
                                                            <i class='fa fa-eye text-dark' id="toggleIcon" onclick="togglePassword()"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-center mt-4">
                                            <button type="submit" class="btn btn-info">Daftar</button>
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

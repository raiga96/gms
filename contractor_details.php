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
        
        .modern-banner {
            background: linear-gradient(135deg, #1d384d 0%, #007442 100%) !important;
            border-radius: 16px !important;
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
        
        .border-box {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            background: #ffffff;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .border-box:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
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

                    <div class="row modern-record-container mt-4 mb-0">
                        <div class="col-12">
                            <div class="card shadow border-0 modern-banner">
                                <div class="card-body p-4 d-sm-flex align-items-center justify-content-between text-white">
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
                    <div class="row modern-record-container mb-2">
                        <div class="col-md-12 grid-margin stretch-card">
                            <div class="card shadow border-0">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h4 class="card-title mb-0"><i class="icon-user-following me-2 text-success"></i>Butiran Kontraktor</h4>
                                        <span class="badge badge-success px-3 py-2">Aktif</span>
                                    </div>
                                    <hr>
                                    <div class="row g-4">
                                        <!-- Column 1: Details -->
                                        <div class="col-md-4">
                                            <div class="border-box h-100 position-relative d-flex flex-column">
                                                <div class="text-center mb-4">
                                                    <span class="badge bg-soft-info text-info px-3 py-2 text-uppercase fw-bold rounded-pill" style="background-color: #e0f2fe; color: #0284c7;">
                                                        <i class="icon-info me-1"></i> Butiran Syarikat
                                                    </span>
                                                </div>
                                                
                                                <div class="d-flex align-items-start mb-4">
                                                    <div class="bg-light p-3 rounded-circle me-3 text-primary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                                        <i class="icon-briefcase" style="font-size: 20px;"></i>
                                                    </div>
                                                    <div>
                                                        <p class="text-muted mb-1 small text-uppercase fw-bold" style="letter-spacing: 0.5px;">Nama Kontraktor</p>
                                                        <h5 class="mb-1 fw-bold text-dark"><?php echo $row1['name']; ?></h5>
                                                        <p class="small text-muted mb-0"><i class="icon-user me-1"></i> PIC: <?php echo $row1['contact_person']; ?></p>
                                                    </div>
                                                </div>
                                                
                                                <div class="d-flex align-items-start mb-4">
                                                    <div class="bg-light p-3 rounded-circle me-3 text-primary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                                        <i class="icon-envelope" style="font-size: 20px;"></i>
                                                    </div>
                                                    <div>
                                                        <p class="text-muted mb-1 small text-uppercase fw-bold" style="letter-spacing: 0.5px;">Alamat E-mel</p>
                                                        <h5 class="mb-0 fw-bold text-dark"><?php echo $row1['email'] ?: 'N/A'; ?></h5>
                                                    </div>
                                                </div>
                                                
                                                <div class="d-flex align-items-start mt-auto">
                                                    <div class="bg-light p-3 rounded-circle me-3 text-primary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                                        <i class="icon-calendar" style="font-size: 20px;"></i>
                                                    </div>
                                                    <div>
                                                        <p class="text-muted mb-1 small text-uppercase fw-bold" style="letter-spacing: 0.5px;">Tempoh Aktif</p>
                                                        <h5 class="mb-0 fw-bold text-dark">-</h5>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Column 2: Login & Access -->
                                        <div class="col-md-4">
                                            <div class="border-box h-100 position-relative d-flex flex-column">
                                                <div class="text-center mb-4">
                                                    <span class="badge bg-soft-success text-success px-3 py-2 text-uppercase fw-bold rounded-pill" style="background-color: #dcfce7; color: #166534;">
                                                        <i class="icon-lock me-1"></i> Log Masuk & Akses
                                                    </span>
                                                </div>
                                                
                                                <?php
                                                $stat = false;
                                                $sql = "SELECT * FROM users WHERE uid = '" . $row['uid'] . "'";
                                                $res = $conn->query($sql);
                                                $user_row = $res->fetch_assoc();
                                                $status = 'N/A';
                                                $status_color = 'secondary';
                                                if ($res->num_rows > 0) {
                                                    $stat = true;
                                                    switch ($user_row['status']) {
                                                        case 'Y': $status = 'Aktif'; $status_color = 'success'; break;
                                                        case 'N': $status = 'Tidak Aktif'; $status_color = 'danger'; break;
                                                        case 'C': $status = 'Dibatalkan'; $status_color = 'warning'; break;
                                                        case 'T': $status = 'Ditamatkan'; $status_color = 'danger'; break;
                                                        default:  $status = 'Tidak Tersedia'; $status_color = 'secondary'; break;
                                                    }
                                                }
                                                ?>
                                                
                                                <div class="text-center mb-4">
                                                    <p class="text-muted mb-1 small text-uppercase fw-bold" style="letter-spacing: 0.5px;">Akaun Sistem</p>
                                                    <h3 class="fw-bold text-dark mb-2"><?php echo $user_row['user_id'] ?? 'Belum Didaftar'; ?></h3>
                                                    <span class="badge bg-<?php echo $status_color; ?> px-3 py-1 rounded-pill"><i class="icon-energy me-1"></i> <?php echo $status; ?></span>
                                                </div>

                                                <?php if (!$stat) { ?>
                                                    <div class="alert alert-warning border-0 bg-light text-warning d-flex align-items-center mb-4" role="alert">
                                                        <i class="icon-info me-2" style="font-size: 20px;"></i>
                                                        <div class="small fw-bold">
                                                            Akses log masuk sistem belum tersedia. Sila daftarkan ID.
                                                        </div>
                                                    </div>
                                                <?php } ?>
                                                
                                                <div class="d-grid gap-2 mt-auto pt-3">
                                                    <button class="btn btn-primary btn-sm rounded-pill fw-bold shadow-sm" <?php if ($stat) { echo 'disabled'; } ?> type="button" data-bs-toggle="modal" data-bs-target="#exampleModal">
                                                        <i class="icon-user-follow me-1"></i> Daftar ID
                                                    </button>
                                                    <a href="contractor_profile.php?id=<?php echo $cid; ?>" class="btn btn-outline-secondary btn-sm rounded-pill fw-bold">
                                                        <i class="icon-people me-1"></i> Lihat Senarai Pengguna
                                                    </a>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Column 3: Payment/Contracts -->
                                        <div class="col-md-4">
                                            <div class="border-box h-100 position-relative d-flex flex-column">
                                                <div class="text-center mb-4">
                                                    <span class="badge bg-soft-primary text-primary px-3 py-2 text-uppercase fw-bold rounded-pill" style="background-color: #e0e7ff; color: #3730a3;">
                                                        <i class="icon-wallet me-1"></i> Bayaran & Kontrak
                                                    </span>
                                                </div>
                                                
                                                <div class="text-center mb-4">
                                                    <div class="d-inline-block bg-light p-4 rounded-circle mb-3">
                                                        <i class="icon-docs text-primary" style="font-size: 32px;"></i>
                                                    </div>
                                                    <h5 class="fw-bold text-dark">Dokumen Kontrak</h5>
                                                    <p class="text-muted small">Urus perjanjian dan butiran bayaran kontraktor ini.</p>
                                                </div>
                                                
                                                <div class="d-grid gap-2 mt-auto pt-3">
                                                    <button class="btn btn-info btn-sm rounded-pill fw-bold text-white shadow-sm">
                                                        <i class="icon-doc me-1"></i> Tambah Kontrak
                                                    </button>
                                                    <button class="btn btn-outline-info btn-sm rounded-pill fw-bold">
                                                        <i class="icon-credit-card me-1"></i> Sejarah Bayaran
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row modern-record-container">
                        <div class="col-md-12 grid-margin stretch-card">
                            <div class="card shadow">
                                <div class="card-body">
                                    <div class="d-sm-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
                                        <h4 class="card-title mb-sm-0">Senarai Kontrak</h4>
                                    </div>
                                    <div class="modern-table-wrapper mb-2">
                                        <table class="modern-table">
                                            <thead>
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
                                                <th width="10%"></th>
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
                                                            <h6 class="text-uppercase">KERJA-KERJA MEMOTONG RUMPUT DAN MEMBERSIHKAN LONGKANG DALAM KAWASAN DBKU ZON <?php echo $row2['zone_name'] ?></h6>
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
                                                            <td>
                                                                <button type="button" class="btn btn-danger p-3" data-id="<?php ?>" onclick="deleteDb(this)"><i class=" fa fa-trash"></i></button>

                                                                <a href="update_zone.php?id=<?php ?>" class="btn btn-primary p-3"><i class="fa fa-pencil"></i></a>

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

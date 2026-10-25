<?php
require 'includes/connection.php';

$cid = (int)($_GET['id'] ?? 0);
if ($cid <= 0) {
    header('Location: contract_list.php');
    exit;
}

$stmt = $conn->prepare("
    SELECT ct.*, c.name as contractor_name, c.address as contractor_address, c.email as contractor_email
    FROM contract ct
    LEFT JOIN contractor c ON ct.cid = c.cid
    WHERE ct.contract_id = ?
");
$stmt->bind_param("i", $cid);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    header('Location: contract_list.php');
    exit;
}

$contract_id = (int)$row['contract_id'];
$contractor = $row['contractor_name'] ?? 'Kontraktor';
$row1 = [
    'name' => $contractor,
    'address' => $row['contractor_address'] ?? '',
    'email' => $row['contractor_email'] ?? '',
    'cid' => $row['cid']
];
?>
<!DOCTYPE html>
<html lang="ms">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Green Management System - Butiran Harian</title>
    <!-- Google Fonts Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome 6 CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <!-- plugins:css -->
    <link rel="stylesheet" href="assets/vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="assets/vendors/flag-icon-css/css/flag-icons.min.css">
    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="assets/vendors/jvectormap/jquery-jvectormap.css">
    <link rel="stylesheet" href="assets/vendors/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="assets/vendors/chartist/chartist.min.css">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="assets/css/vertical-light-layout/style.css">
    <link rel="stylesheet" href="assets/css/modern-dashboard.css?v=<?php echo filemtime(__DIR__ . '/assets/css/modern-dashboard.css'); ?>">
    <!-- End layout styles -->
    <link rel="shortcut icon" href="assets/images/favicon.png" />
    <style>
        /* Print media - preserve exactly as-is */
        @media print {
            body { background: white; }
            .sidebar, .navbar, .d-print-none, .gms-console, .gms-hero-banner, footer { display: none !important; }
            .main-panel { width: 100% !important; margin: 0 !important; padding: 0 !important; }
            .content-wrapper { padding: 0 !important; margin: 0 !important; background: white; }
            .card { box-shadow: none !important; border: none !important; margin-bottom: 0 !important; }
            .card-body { padding: 0 !important; }
            .gms-table td, .gms-table th { border-color: #000 !important; color: #000 !important; }
            .gms-table { border-color: #000 !important; }
            .badge-outline-success { border-color: #000 !important; color: #000 !important; }
            .page-body-wrapper { padding-top: 0 !important; }
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

                    <div class="row mt-4 mb-0">
                        <div class="col-12">
                            <div class="card gms-console shadow">
                                <div class="card-body p-0 d-sm-flex align-items-center justify-content-between">
                                    <div class="mb-3 mb-sm-0">
                                        <h3 class="mb-1 text-white fw-bold"><?php echo htmlspecialchars($contractor); ?></h3>
                                        <h5 class="mb-1 text-white fw-bold"><?php echo htmlspecialchars($row1['address'] ?? ''); ?></h5>
                                    </div>
                                    <div class="btn-group gap-2">
                                        <a class="btn btn-light shadow-sm text-primary" href="update_contractor.php?id=<?php echo (int)($row1['cid'] ?? 0); ?>">Kemaskini</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php
                    // --- Session Filter Logic (Hardened & Parameterized) ---
                    $current_year = (int)date("Y");
                    $current_month = (int)date("n");
                    $current_day = (int)date("j");
                    $current_session_default = ($current_month - 1) * 2 + ($current_day <= 15 ? 1 : 2);

                    $filter_year = !empty($_GET['filter_year']) ? (int)$_GET['filter_year'] : 0;
                    $filter_session = !empty($_GET['filter_session']) ? (int)$_GET['filter_session'] : 0;

                    $stmt_avail = $conn->prepare("SELECT DISTINCT YEAR(date) as yr FROM daily_records WHERE cid = ? ORDER BY yr DESC");
                    $available_years = [];
                    if ($stmt_avail) {
                        $stmt_avail->bind_param("s", $contract_id);
                        $stmt_avail->execute();
                        $avail_res = $stmt_avail->get_result();
                        if ($avail_res && $avail_res->num_rows > 0) {
                            while ($ar = $avail_res->fetch_assoc()) {
                                $available_years[] = (int)$ar['yr'];
                            }
                        }
                        $stmt_avail->close();
                    }
                    if (empty($available_years)) {
                        $available_years = [$current_year];
                    }

                    if (!in_array($filter_year, $available_years, true)) {
                        $filter_year = $available_years[0];
                    }
                    if ($filter_session <= 0) {
                        $year_start = sprintf('%04d-01-01', $filter_year);
                        $year_end = sprintf('%04d-01-01', $filter_year + 1);
                        $stmt_max = $conn->prepare("SELECT MAX(gc_session) as max_s FROM daily_records WHERE cid = ? AND date >= ? AND date < ?");
                        if ($stmt_max) {
                            $stmt_max->bind_param("sss", $contract_id, $year_start, $year_end);
                            $stmt_max->execute();
                            $max_s_row = $stmt_max->get_result()->fetch_assoc();
                            if ($max_s_row && !empty($max_s_row['max_s'])) {
                                $filter_session = (int)$max_s_row['max_s'];
                            } else {
                                $filter_session = $current_session_default;
                            }
                            $stmt_max->close();
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
                    $stmt_dr = $conn->prepare("SELECT * FROM daily_records WHERE cid = ? AND date >= ? AND date <= ? ORDER BY date ASC");
                    $daily_data = [];
                    $total_records = 0;
                    if ($stmt_dr) {
                        $stmt_dr->bind_param("sss", $contract_id, $start_date, $end_date);
                        $stmt_dr->execute();
                        $res_records = $stmt_dr->get_result();
                        if ($res_records && $res_records->num_rows > 0) {
                            $total_records = $res_records->num_rows;
                            while ($dr = $res_records->fetch_assoc()) {
                                $daily_data[$dr['date']] = $dr;
                            }
                        }
                        $stmt_dr->close();
                    }
                    ?>


                    <?php
                    // Build KPI stats for the hero banner
                    $total_records = $res_records ? $res_records->num_rows : 0;
                    $confirmed_records = 0;
                    $pending_records = 0;
                    if (isset($daily_data) && !empty($daily_data)) {
                        foreach ($daily_data as $dr) {
                            if ($dr['verified'] === 'Y') $confirmed_records++;
                            else $pending_records++;
                        }
                    }
                    ?>
                    <!-- Hero Banner -->
                    <div class="row mt-0 mb-4">
                        <div class="col-12">
                            <div class="gms-hero-banner gms-fade-in">
                                <div class="d-sm-flex align-items-center justify-content-between flex-wrap gap-3">
                                    <div>
                                        <h2 class="gms-hero-title mb-1">Rekod Harian - <?php echo htmlspecialchars($contractor); ?></h2>
                                        <p class="gms-hero-subtitle mb-0">
                                            <i class="fas fa-calendar-alt me-1"></i> Sesi <?php echo htmlspecialchars($filter_session) ?> Â· <?php echo htmlspecialchars($filter_year) ?>
                                            &nbsp;|&nbsp;
                                            <i class="fas fa-map-marker-alt me-1"></i> Kontrak: <?php echo htmlspecialchars($row['contract_number'] ?? $contract_id) ?>
                                        </p>
                                    </div>
                                    <div class="d-flex gap-4 flex-wrap">
                                        <div class="gms-hero-stat">
                                            <i class="gms-hero-stat-icon fas fa-list"></i>
                                            <div>
                                                <div class="gms-hero-stat-value"><?php echo $total_records ?></div>
                                                <div class="gms-hero-stat-label">Keseluruhan</div>
                                            </div>
                                        </div>
                                        <div class="gms-hero-stat">
                                            <i class="gms-hero-stat-icon fas fa-check-circle text-success"></i>
                                            <div>
                                                <div class="gms-hero-stat-value"><?php echo $confirmed_records ?></div>
                                                <div class="gms-hero-stat-label">Disahkan</div>
                                            </div>
                                        </div>
                                        <div class="gms-hero-stat">
                                            <i class="gms-hero-stat-icon fas fa-clock text-warning"></i>
                                            <div>
                                                <div class="gms-hero-stat-value"><?php echo $pending_records ?></div>
                                                <div class="gms-hero-stat-label">Belum Sahkan</div>
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
                                        <h4 class="card-title mb-sm-0">Rekod Harian Tapak</h4>
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
                                                    echo "<option value='$i' $sel>Sesi $i</option>";
                                                }
                                                ?>
                                            </select>
                                            <button type="submit" class="btn btn-primary btn-sm" style="border-radius: 8px;">Tapis</button>
                                        </form>
                                    </div>
                                    <div class="gms-table-wrapper mb-2">
                                        <table class="gms-table">
                                            <thead>
                                                <tr>
                                                    <th>Tarikh</th>
                                                    <th>Sesi</th>
                                                    <th>Kontraktor</th>
                                                    <th>Penyelia</th>
                                                    <th>Pengawas</th>
                                                    <th>Disahkan</th>
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
                                                                    <?php echo gms_format_date($row['date'], 'd M Y') ?>
                                                                </a>
                                                            </td>
                                                            <td style="word-wrap: break-word;overflow-wrap: break-word; white-space: normal;">
                                                                Sesi <?php echo htmlspecialchars($row['gc_session']) ?>
                                                            </td>
                                                            <td>
                                                                <?php echo ($row['contractor_dsign'] === 'Y') ? '<i class="fa fa-check-circle text-success" style="font-size: 18px;" title="Ditandatangani"></i>' : '<i class="fa fa-times-circle text-danger" style="font-size: 18px;" title="Belum ditandatangani"></i>'; ?>
                                                            </td>
                                                            <td>
                                                                <?php echo ($row['supervisor_dsign'] === 'Y') ? '<i class="fa fa-check-circle text-success" style="font-size: 18px;" title="Ditandatangani"></i>' : '<i class="fa fa-times-circle text-danger" style="font-size: 18px;" title="Belum ditandatangani"></i>'; ?>
                                                            </td>
                                                            <td>
                                                                <?php echo ($row['pengawas_dsign'] === 'Y') ? '<i class="fa fa-check-circle text-success" style="font-size: 18px;" title="Ditandatangani"></i>' : '<i class="fa fa-times-circle text-danger" style="font-size: 18px;" title="Belum ditandatangani"></i>'; ?>
                                                            </td>
                                                            <td>
                                                                <?php echo ($row['verified'] === 'Y') ? '<i class="fa fa-check-square text-success" style="font-size: 18px;" title="Disahkan"></i>' : '<i class="fa fa-minus-circle text-danger" style="font-size: 18px;" title="Belum disahkan"></i>'; ?>
                                                            </td>
                                                        </tr>
                                                <?php 
                                                    } else { 
                                                ?>
                                                        <tr>
                                                            <td style="color: #64748b;">
                                                                <?php echo gms_format_date($current, 'd M Y') ?>
                                                            </td>
                                                            <td style="color: #64748b;">
                                                                Sesi <?php echo htmlspecialchars($filter_session) ?>
                                                            </td>
                                                            <td colspan="4" class="text-center text-muted" style="font-style: italic;">
                                                                <span class="badge badge-outline-secondary" style="font-size: 11px; padding: 4px 8px; border: 1px dashed #f82727ff; color: #d62525ff; background-color: transparent;">Tiada data</span>
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

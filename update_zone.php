<?php
session_start();
require 'includes/connection.php';

$zone_id = (int)($_GET['id'] ?? 0);

if ($zone_id <= 0) {
    header("Location: zone.php");
    exit();
}

// Prepared statement for zone lookup
$stmt = $conn->prepare("SELECT zone_id, zone_name FROM zone WHERE zone_id = ?");
$stmt->bind_param("i", $zone_id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

if (!$row) {
    $_SESSION['status'] = 'error';
    $_SESSION['message'] = 'Zon tidak dijumpai.';
    header("Location: zone.php");
    exit();
}

// Subzones count in this zone
$stmt_sz = $conn->prepare("SELECT COUNT(*) AS total_sz FROM subzone WHERE zone_id = ?");
$stmt_sz->bind_param("i", $zone_id);
$stmt_sz->execute();
$res_sz = $stmt_sz->get_result()->fetch_assoc();
$total_subzones = (int)($res_sz['total_sz'] ?? 0);
$stmt_sz->close();
?>
<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Kemaskini Zon — Green Management System (DBKU)</title>
    <link rel="shortcut icon" href="assets/images/favicon.png" />
    <link rel="stylesheet" href="assets/vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="assets/vendors/flag-icon-css/css/flag-icons.min.css">
    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="assets/vendors/font-awesome/css/font-awesome.min.css" />
    <link rel="stylesheet" href="assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="assets/vendors/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="assets/css/vertical-light-layout/style.css">
    <link rel="stylesheet" href="assets/css/modern-dashboard.css?v=<?php echo filemtime(__DIR__ . '/assets/css/modern-dashboard.css'); ?>">
</head>

<body>
    <div class="container-scroller">
        <?php include 'partials/navbar.php'; ?>

        <div class="container-fluid page-body-wrapper">
            <?php include 'partials/sidebar.php'; ?>

            <div class="main-panel" style="height: 100%;">
                <div class="content-wrapper">

                    <!-- ============================================================== -->
                    <!-- PAGE HEADER BANNER                                              -->
                    <!-- ============================================================== -->
                    <div class="gms-hero-banner gms-fade-in" style="padding: 1.5rem 2rem;">
                        <div class="d-flex align-items-center justify-content-between flex-wrap g-3">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="gms-pulse-dot" style="background:#34d399;"></span>
                                    <span class="gms-hero-subtitle" style="color: rgba(255,255,255,0.75);">Modul Pengurusan Zon</span>
                                </div>
                                <h1 class="gms-hero-title mb-1">Kemaskini Zon</h1>
                                <p class="gms-hero-subtitle">Nama Semasa: <strong style="color:#34d399;"><?php echo htmlspecialchars($row['zone_name']); ?></strong></p>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <a href="zone_details.php?id=<?php echo $zone_id; ?>" class="btn btn-outline-light btn-sm rounded-pill px-3 py-2 font-weight-bold">
                                    <i class="fa-solid fa-eye me-1"></i> Lihat Butiran Zon
                                </a>
                                <a href="zone.php" class="btn btn-light btn-sm rounded-pill px-3 py-2 font-weight-bold" style="color: #007442;">
                                    <i class="fa-solid fa-arrow-left me-1"></i> Senarai Zon
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================================== -->
                    <!-- UPDATE FORM + INFO CARD                                         -->
                    <!-- ============================================================== -->
                    <div class="row g-4 mb-4 gms-fade-in gms-fade-in-delay-1">
                        <!-- Form card -->
                        <div class="col-lg-7 col-xl-6">
                            <div class="card shadow gms-table-card h-100">
                                <div class="card-body p-4 d-flex flex-column">
                                    <div class="mb-4">
                                        <h4 class="gms-section-title">Nama Zon Pentadbiran</h4>
                                        <p class="gms-section-subtitle">Ubah nama zon pentadbiran DBKU di bawah.</p>
                                    </div>

                                    <form class="forms-sample flex-grow-1" action="includes/update_zone.php" method="POST" id="updateZoneForm">
                                        <input type="hidden" name="zone_id" value="<?php echo $zone_id; ?>">

                                        <div class="form-group mb-4">
                                            <label for="zoneName" class="mb-2 font-weight-bold text-dark">Nama Zon</label>
                                            <div class="gms-input-icon">
                                                <i class="fa-solid fa-location-dot"></i>
                                                <input type="text" class="form-control" name="zone_name" id="zoneName" value="<?php echo htmlspecialchars($row['zone_name']); ?>" required>
                                            </div>
                                            <small class="form-text text-muted mt-2">Nama ini akan dipaparkan pada semua kontrak, sub-zon, dan modul pemantauan geospatial.</small>
                                        </div>

                                        <div class="d-flex flex-wrap gap-2 pt-3 mt-auto border-top">
                                            <button type="submit" class="btn btn-success rounded-pill px-4 py-2 font-weight-semibold">
                                                <i class="fa-solid fa-check me-1"></i> Simpan Kemaskini
                                            </button>
                                            <a href="zone_details.php?id=<?php echo $zone_id; ?>" class="btn btn-outline-secondary rounded-pill px-4 py-2 font-weight-semibold">
                                                <i class="fa-solid fa-xmark me-1"></i> Batal
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Info / subzones summary card -->
                        <div class="col-lg-5 col-xl-6">
                            <div class="card shadow h-100" style="border: 1px dashed rgba(0,116,66,0.25); background: rgba(0,116,66,0.03);">
                                <div class="card-body p-4 d-flex flex-column">
                                    <h4 class="gms-section-title">
                                        <i class="fa-solid fa-circle-info me-2 text-success"></i>Ringkasan Zon
                                    </h4>
                                    <p class="gms-section-subtitle mb-3">Statistik & pautan pantas</p>

                                    <div class="p-3 mb-3 bg-white rounded-3 border">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="text-secondary small fw-semibold">Jumlah Sub-Zon Berkaitan:</span>
                                            <span class="badge bg-success rounded-pill px-3 py-1 font-weight-bold" style="font-size:0.85rem;"><?php echo $total_subzones; ?> Sub-Zon</span>
                                        </div>
                                    </div>

                                    <ul class="mb-0 text-secondary small" style="line-height: 1.8;">
                                        <li>Sebarang pindaan nama zon akan <strong>diselaraskan secara automatik</strong> pada semua sub-zon di bawahnya.</li>
                                        <li>Tiada rekod kontrak atau buku log harian tapak yang akan terjejas kerana hubungan data menggunakan <code>zone_id</code>.</li>
                                        <li>Untuk menambah atau mengurus sempadan sub-zon, gunakan butang <em>Lihat Butiran Zon</em> di atas.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- content-wrapper ends -->

                <?php include 'partials/footer.php'; ?>
            </div>
            <!-- main-panel ends -->
        </div>
        <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->

    <!-- JS -->
    <script src="assets/vendors/js/vendor.bundle.base.js"></script>
    <script src="assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <script src="assets/vendors/moment/moment.min.js"></script>
    <script src="assets/vendors/daterangepicker/daterangepicker.js"></script>
    <script src="assets/js/jquery.cookie.js"></script>
    <script src="assets/js/off-canvas.js"></script>
    <script src="assets/js/hoverable-collapse.js"></script>
    <script src="assets/js/misc.js"></script>
    <script src="assets/js/settings.js"></script>
    <script src="assets/js/todolist.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .gms-input-icon {
            position: relative;
        }
        .gms-input-icon > i {
            position: absolute;
            top: 50%;
            left: 14px;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 0.95rem;
            pointer-events: none;
        }
        .gms-input-icon .form-control {
            padding-left: 40px;
            height: 46px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            font-size: 0.95rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .gms-input-icon .form-control:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.12);
        }
    </style>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var zoneInput = document.getElementById('zoneName');
            if (zoneInput) { 
                zoneInput.focus();
                zoneInput.select();
            }

            var form = document.getElementById('updateZoneForm');
            if (form && zoneInput) {
                form.addEventListener('submit', function(e) {
                    if (zoneInput.value.trim() === '') {
                        e.preventDefault();
                        Swal.fire({
                            title: "Ruangan Kosong",
                            text: "Sila masukkan nama zon yang sah.",
                            icon: "warning",
                            confirmButtonColor: "#007442"
                        });
                    }
                });
            }
        });
    </script>

    <?php
    if (isset($_SESSION['status'])) {
        $status = $_SESSION['status'];
        $message = $_SESSION['message'];
        unset($_SESSION['status']);
        unset($_SESSION['message']);
        $icon = ($status == 'success') ? 'success' : (($status == 'warning') ? 'warning' : 'error');
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({ title: "' . addslashes($message) . '", icon: "' . $icon . '" });
            });
        </script>';
    }
    ?>
</body>

</html>
<?php
session_start();
$zone_id = (int)($_GET['id'] ?? 0);

require 'includes/connection.php';

// Fetch zone name safely (prepared statement)
$stmt = $conn->prepare("SELECT zone_name FROM zone WHERE zone_id = ?");
$stmt->bind_param("i", $zone_id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$zone_name = $row ? $row['zone_name'] : 'Tidak Dikenali';
$stmt->close();
?>
<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Daftar Sub-Zon Baharu — Green Management System (DBKU)</title>
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
                    <!-- PAGE HEADER BANNER                                             -->
                    <!-- ============================================================== -->
                    <div class="gms-hero-banner gms-fade-in" style="padding: 1.5rem 2rem;">
                        <div class="d-flex align-items-center justify-content-between flex-wrap g-3">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="gms-pulse-dot" style="background:#34d399;"></span>
                                    <span class="gms-hero-subtitle" style="color: rgba(255,255,255,0.75);">Modul Zon</span>
                                </div>
                                <h1 class="gms-hero-title mb-1">Daftar Sub-Zon Baharu</h1>
                                <p class="gms-hero-subtitle">Zon: <strong style="color:#34d399;"><?php echo htmlspecialchars($zone_name); ?></strong></p>
                            </div>
                            <div>
                                <a href="zone_details.php?id=<?php echo $zone_id; ?>" class="btn btn-light btn-sm rounded-pill px-3 py-2 font-weight-bold" style="color: #007442;">
                                    <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Zon
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================================== -->
                    <!-- FORM CARD                                                     -->
                    <!-- ============================================================== -->
                    <div class="row mb-4 gms-fade-in gms-fade-in-delay-1">
                        <div class="col-lg-8">
                            <div class="card shadow">
                                <div class="card-body p-4">
                                    <h4 class="gms-section-title">
                                        <span class="gms-kpi-icon" style="width:36px; height:36px; font-size:1rem; background: rgba(0, 116, 66, 0.08); color: #007442;">
                                            <i class="fa-solid fa-map-location-dot"></i>
                                        </span>
                                        Butiran Sub-Zon
                                    </h4>
                                    <p class="gms-section-subtitle">Sila isi maklumat sub-zon baharu di bawah zon <?php echo htmlspecialchars($zone_name); ?>.</p>

                                    <form action="includes/add_subzone.php" method="POST" id="subzoneForm">
                                        <input type="hidden" name="zone_id" value="<?php echo $zone_id; ?>">

                                        <div class="mb-3">
                                            <label for="subzoneName" class="form-label font-weight-bold text-dark" style="font-size: 0.85rem;">Nama Sub-Zon</label>
                                            <input type="text" class="form-control" name="subzone" id="subzoneName" placeholder="Cth: Petra Jaya A1" required style="border-radius: 12px; border: 1px solid #e2e8f0; padding: 10px 14px; font-size: 0.9rem;">
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="perimeter" class="form-label font-weight-bold text-dark" style="font-size: 0.85rem;">Perimeter (m)</label>
                                                    <div class="input-group" style="border-radius: 12px; overflow: hidden;">
                                                        <span class="input-group-text" style="background: #f1f5f9; border: 1px solid #e2e8f0; border-right: none; color: #64748b;"><i class="fa-solid fa-ruler"></i></span>
                                                        <input type="number" step="0.01" class="form-control" name="perimeter" id="perimeter" placeholder="0.00" style="border-radius: 0 12px 12px 0; border: 1px solid #e2e8f0; border-left: none; padding: 10px 14px; font-size: 0.9rem;">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="area" class="form-label font-weight-bold text-dark" style="font-size: 0.85rem;">Keluasan (ha)</label>
                                                    <div class="input-group" style="border-radius: 12px; overflow: hidden;">
                                                        <span class="input-group-text" style="background: #f1f5f9; border: 1px solid #e2e8f0; border-right: none; color: #64748b;"><i class="fa-solid fa-chart-area"></i></span>
                                                        <input type="number" step="0.01" class="form-control" name="area" id="area" placeholder="0.00" style="border-radius: 0 12px 12px 0; border: 1px solid #e2e8f0; border-left: none; padding: 10px 14px; font-size: 0.9rem;">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex gap-2 mt-4">
                                            <button type="submit" class="btn btn-success rounded-pill px-4 py-2 font-weight-bold">
                                                <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Sub-Zon
                                            </button>
                                            <a href="zone_details.php?id=<?php echo $zone_id; ?>" class="btn btn-outline-secondary rounded-pill px-4 py-2 font-weight-semibold">
                                                <i class="fa-solid fa-xmark me-1"></i> Batal
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Panduan Card -->
                        <div class="col-lg-4">
                            <div class="card shadow h-100">
                                <div class="card-body p-4">
                                    <h4 class="gms-section-title">
                                        <span class="gms-kpi-icon" style="width:36px; height:36px; font-size:1rem; background: rgba(6, 182, 212, 0.12); color: #0891b2;">
                                            <i class="fa-solid fa-circle-info"></i>
                                        </span>
                                        Panduan
                                    </h4>
                                    <div class="mt-3">
                                        <div class="d-flex align-items-start gap-2 mb-3">
                                            <span class="gms-status-dot" style="background-color: #10b981; margin-top: 6px;"></span>
                                            <div>
                                                <span class="font-weight-bold text-dark" style="font-size: 0.85rem;">Perimeter</span>
                                                <p class="text-muted mb-0" style="font-size: 0.8rem;">Jumlah panjang sempadan kawasan sub-zon dalam meter (m).</p>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-start gap-2 mb-3">
                                            <span class="gms-status-dot" style="background-color: #f59e0b; margin-top: 6px;"></span>
                                            <div>
                                                <span class="font-weight-bold text-dark" style="font-size: 0.85rem;">Keluasan</span>
                                                <p class="text-muted mb-0" style="font-size: 0.8rem;">Jumlah kawasan operasi dalam hektar (ha).</p>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-start gap-2">
                                            <span class="gms-status-dot" style="background-color: #6366f1; margin-top: 6px;"></span>
                                            <div>
                                                <span class="font-weight-bold text-dark" style="font-size: 0.85rem;">Nama Sub-Zon</span>
                                                <p class="text-muted mb-0" style="font-size: 0.8rem;">Gunakan nama yang jelas &amp; mudah dikenali (Cth: Satok A1).</p>
                                            </div>
                                        </div>
                                    </div>
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

    <script>
        // Auto-focus on name field
        document.addEventListener("DOMContentLoaded", function() {
            var nameInput = document.getElementById('subzoneName');
            if (nameInput) nameInput.focus();
        });
    </script>

    <?php
    // Session status message (SweetAlert)
    if (isset($_SESSION['status'])) {
        $status = $_SESSION['status'];
        $message = $_SESSION['message'];
        unset($_SESSION['status']);
        unset($_SESSION['message']);
        $icon = ($status == 'success') ? 'success' : 'error';
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({ title: "' . addslashes($message) . '", icon: "' . $icon . '" });
            });
        </script>';
    }
    ?>
</body>

</html>

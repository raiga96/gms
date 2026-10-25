<?php
session_start();
require 'includes/connection.php';
?>
<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Daftar Zon Baharu — Green Management System (DBKU)</title>
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
                                <h1 class="gms-hero-title mb-1">Daftar Zon Baharu</h1>
                                <p class="gms-hero-subtitle">Tambah zon pentadbiran baharu ke dalam sistem DBKU</p>
                            </div>
                            <div>
                                <a href="zone.php" class="btn btn-light btn-sm rounded-pill px-3 py-2 font-weight-bold" style="color: #007442;">
                                    <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Senarai Zon
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================================== -->
                    <!-- ADD ZONE FORM + HELPER CARD                                     -->
                    <!-- ============================================================== -->
                    <div class="row g-4 mb-4 gms-fade-in gms-fade-in-delay-1">
                        <!-- Form card -->
                        <div class="col-lg-7 col-xl-6">
                            <div class="card shadow gms-table-card h-100">
                                <div class="card-body p-4 d-flex flex-column">
                                    <div class="mb-4">
                                        <h4 class="gms-section-title">Maklumat Zon</h4>
                                        <p class="gms-section-subtitle">Isi nama zon baharu. Ruangan ini diperlukan.</p>
                                    </div>

                                    <form class="forms-sample flex-grow-1" action="includes/add_zone.php" method="POST" id="addZoneForm">
                                        <div class="form-group mb-4">
                                            <label for="zoneName" class="mb-2 font-weight-bold text-dark">Nama Zon</label>
                                            <div class="gms-input-icon">
                                                <i class="fa-solid fa-location-dot"></i>
                                                <input type="text" class="form-control" name="zone" id="zoneName" placeholder="cth: Zon 1 — Bandaraya" required>
                                            </div>
                                            <small class="form-text text-muted mt-2">Nama ini akan dipaparkan dalam senarai zon & peta geospatial.</small>
                                        </div>

                                        <div class="d-flex flex-wrap gap-2 pt-3 mt-auto border-top">
                                            <button type="submit" class="btn btn-success rounded-pill px-4 py-2 font-weight-semibold">
                                                <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Zon
                                            </button>
                                            <a href="zone.php" class="btn btn-outline-secondary rounded-pill px-4 py-2 font-weight-semibold">
                                                <i class="fa-solid fa-xmark me-1"></i> Batal
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Helper / tips card -->
                        <div class="col-lg-5 col-xl-6">
                            <div class="card shadow h-100" style="border: 1px dashed rgba(0,116,66,0.25); background: rgba(0,116,66,0.03);">
                                <div class="card-body p-4 d-flex flex-column">
                                    <h4 class="gms-section-title">
                                        <i class="fa-solid fa-circle-info me-2 text-success"></i>Panduan
                                    </h4>
                                    <p class="gms-section-subtitle mb-3">Tips penamaan & pengurusan zon</p>
                                    <ul class="mb-0 text-secondary small" style="line-height: 1.8;">
                                        <li class="mb-2">Gunakan <strong>nama yang jelas</strong> &amp; unik untuk setiap zon.</li>
                                        <li class="mb-2">Selepas zon dicipta, anda boleh menambah <strong>sub-zon</strong> di bawahnya.</li>
                                        <li class="mb-2">Sub-zon memerlukan fail <strong>KMZ</strong> untuk paparan peta geospatial.</li>
                                        <li>Data zon dipaparkan di <a href="zone.php" class="text-success font-weight-semibold">Senarai Zon</a>.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- FORM CARD END -->

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

    <!-- Form input icon style (page-specific) -->
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
            // Auto-focus the zone name input
            var zoneInput = document.getElementById('zoneName');
            if (zoneInput) { zoneInput.focus(); }

            // Empty-field validation via SweetAlert
            var form = document.getElementById('addZoneForm');
            if (form && zoneInput) {
                form.addEventListener('submit', function(e) {
                    if (zoneInput.value.trim() === '') {
                        e.preventDefault();
                        Swal.fire({
                            title: "Ruangan Kosong",
                            text: "Sila isi nama zon sebelum menyimpan.",
                            icon: "warning",
                            confirmButtonColor: "#007442"
                        });
                    }
                });
            }
        });
    </script>

    <?php
    // Session status message (SweetAlert) — shown after the handler redirects back
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

<?php
session_start();

require 'includes/connection.php';

$page = $_GET['page'] ?? '';

?>
<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Daftar Kontraktor Baharu — Green Management System (DBKU)</title>
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
    <link rel="stylesheet" href="assets/css/modern-dashboard.css">
    <style>
        /* Donezo-inspired form page scoped styles (use theme CSS variables) */
        .gms-form-card .card-body { padding: 2rem !important; }

        .gms-form-label {
            display: block;
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--gms-text-secondary);
            letter-spacing: 0.02em;
            margin-bottom: 0.5rem;
        }
        .gms-form-label .text-danger { color: #ef4444; }

        .gms-input-group {
            position: relative;
            display: flex;
            align-items: stretch;
        }
        .gms-input-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 46px;
            flex-shrink: 0;
            background: rgba(0, 116, 66, 0.06);
            color: var(--gms-primary);
            border: 1px solid var(--gms-border);
            border-right: none;
            border-radius: 12px 0 0 12px;
            font-size: 1rem;
            transition: var(--gms-transition);
        }
        .gms-input-group:focus-within .gms-input-icon {
            background: rgba(0, 116, 66, 0.12);
            color: #059669;
            border-color: var(--gms-border-focus);
        }
        .gms-form-control {
            flex: 1 1 auto;
            padding: 12px 16px;
            border: 1px solid var(--gms-border);
            border-radius: 0 12px 12px 0;
            background: #f8fafc;
            color: var(--gms-text-primary);
            font-size: 0.9rem;
            font-family: inherit;
            outline: none;
            transition: var(--gms-transition);
        }
        .gms-form-control::placeholder { color: var(--gms-text-muted); }
        .gms-form-control:focus {
            background: #ffffff;
            border-color: var(--gms-border-focus);
            box-shadow: 0 0 0 3px rgba(0, 116, 66, 0.12);
        }
        textarea.gms-form-control {
            border-radius: 0 12px 12px 12px;
        }

        .gms-btn-primary {
            padding: 12px 28px;
            border-radius: var(--gms-pill-radius);
            font-weight: 600;
            font-size: 0.875rem;
            background: linear-gradient(135deg, #10b981 0%, #007442 100%);
            color: #ffffff;
            border: none;
            box-shadow: 0 6px 18px rgba(0, 116, 66, 0.3);
            transition: var(--gms-transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .gms-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(0, 116, 66, 0.4);
            color: #ffffff;
        }
        .gms-btn-light {
            padding: 12px 28px;
            border-radius: var(--gms-pill-radius);
            font-weight: 600;
            font-size: 0.875rem;
            background: #f1f5f9;
            color: var(--gms-text-secondary);
            border: 1px solid var(--gms-border);
            transition: var(--gms-transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        .gms-btn-light:hover {
            background: #e2e8f0;
            color: var(--gms-text-primary);
            transform: translateY(-2px);
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="container-scroller">
        <?php include 'partials/navbar.php'; ?>

        <div class="container-fluid page-body-wrapper">
            <?php include 'partials/sidebar.php'; ?>

            <div class="main-panel" style="height: 100%;">
                <div class="content-wrapper">

                    <!-- ============================================================== -->
                    <!-- PAGE HEADER BANNER (Donezo-inspired hero)                       -->
                    <!-- ============================================================== -->
                    <div class="gms-hero-banner gms-fade-in" style="padding: 1.5rem 2rem;">
                        <div class="d-flex align-items-center justify-content-between flex-wrap g-3">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="gms-pulse-dot" style="background:#34d399;"></span>
                                    <span class="gms-hero-subtitle" style="color: rgba(255,255,255,0.75);">Modul Kontraktor</span>
                                </div>
                                <h1 class="gms-hero-title mb-1">Daftar Kontraktor Baharu</h1>
                                <p class="gms-hero-subtitle">Tambah profil kontraktor baru ke dalam sistem pengurusan DBKU</p>
                            </div>
                            <div>
                                <a href="contractor_list.php" class="btn btn-light btn-sm rounded-pill px-3 py-2 font-weight-bold" style="color: #007442;">
                                    <i class="fa-solid fa-arrow-left me-1"></i> Senarai Kontraktor
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================================== -->
                    <!-- CONTRACTOR REGISTRATION FORM                                   -->
                    <!-- ============================================================== -->
                    <div class="row mb-4 gms-fade-in gms-fade-in-delay-1">
                        <div class="col-12">
                            <div class="card shadow gms-form-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                                        <div class="gms-kpi-icon" style="background: rgba(0, 116, 66, 0.08); color: var(--gms-primary);">
                                            <i class="fa-solid fa-user-plus"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h4 class="gms-section-title">Butiran Kontraktor</h4>
                                            <p class="gms-section-subtitle mb-0">Sila lengkapkan maklumat syarikat kontraktor di bawah.</p>
                                        </div>
                                    </div>

                                    <form action="includes/new_contractor.php" method="POST">
                                        <input type="text" name="page" value="<?php echo $page ?>" hidden>

                                        <div class="row g-4">
                                            <div class="col-md-12">
                                                <label for="contractorName" class="gms-form-label">Nama Kontraktor <span class="text-danger">*</span></label>
                                                <div class="gms-input-group">
                                                    <span class="gms-input-icon"><i class="fa-solid fa-building"></i></span>
                                                    <input type="text" class="gms-form-control" name="contractorName" id="contractorName" placeholder="cth. Bina Maju Sdn Bhd" required>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <label for="contractorEmail" class="gms-form-label">Alamat Emel <span class="text-danger">*</span></label>
                                                <div class="gms-input-group">
                                                    <span class="gms-input-icon"><i class="fa-solid fa-envelope"></i></span>
                                                    <input type="email" class="gms-form-control" name="contractorEmail" id="contractorEmail" placeholder="example@domain.com" required>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <label for="contractorContact" class="gms-form-label">Nombor Telefon <span class="text-danger">*</span></label>
                                                <div class="gms-input-group">
                                                    <span class="gms-input-icon"><i class="fa-solid fa-phone"></i></span>
                                                    <input type="text" class="gms-form-control" name="contractorContact" id="contractorContact" placeholder="cth. 012-3456789" required>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <label for="contractorPic" class="gms-form-label">Pegawai Bertugas (PIC) <span class="text-danger">*</span></label>
                                                <div class="gms-input-group">
                                                    <span class="gms-input-icon"><i class="fa-solid fa-user"></i></span>
                                                    <input type="text" class="gms-form-control" name="contractorPic" id="contractorPic" placeholder="Nama PIC" required>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <label for="contractorAddress" class="gms-form-label">Alamat Berdaftar</label>
                                                <div class="gms-input-group">
                                                    <span class="gms-input-icon" style="align-items: flex-start; padding-top: 12px;"><i class="fa-solid fa-location-dot"></i></span>
                                                    <textarea class="gms-form-control" name="contractorAddress" id="contractorAddress" rows="3" placeholder="Masukkan alamat penuh..."></textarea>
                                                </div>
                                            </div>
                                        </div>

                                        <hr class="my-4 border-light">

                                        <div class="d-flex justify-content-center gap-3">
                                            <a href="contractor_list.php" class="gms-btn-light"><i class="fa-solid fa-xmark me-1"></i> Batal</a>
                                            <button type="submit" class="gms-btn-primary"><i class="fa-solid fa-check me-1"></i> Simpan Kontraktor</button>
                                        </div>
                                    </form>
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

<?php
session_start();
$subzone_id = $_GET['szid'];

require 'includes/connection.php';

// Total sub-zone info
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
<html lang="ms">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Tugaskan Staf DBKU — Green Management System (DBKU)</title>
    <link rel="shortcut icon" href="assets/images/favicon.png" />
    <link rel="stylesheet" href="assets/vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="assets/vendors/flag-icon-css/css/flag-icons.min.css">
    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="assets/vendors/font-awesome/css/font-awesome.min.css" />
    <link rel="stylesheet" href="assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="assets/vendors/daterangepicker/daterangepicker.css">
    <!-- Layout styles -->
    <link rel="stylesheet" href="assets/css/vertical-light-layout/style.css">
    <link rel="stylesheet" href="assets/css/modern-dashboard.css">
    <!-- End layout styles -->

    <style>
        /* Page-specific touches that complement modern-dashboard.css */
        .gms-form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 1.25rem;
        }

        .gms-form-group > label {
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--gms-text-secondary);
            margin-bottom: 0;
            letter-spacing: 0.01em;
        }

        .gms-form-group > label .req {
            color: var(--gms-accent-rose);
            margin-left: 2px;
        }

        .gms-form-control {
            border: 1px solid var(--gms-surface-muted);
            border-radius: 12px !important;
            padding: 12px 16px;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--gms-text-primary);
            background-color: var(--gms-surface);
            transition: var(--gms-transition);
            height: auto;
            width: 100%;
        }

        .gms-form-control:focus {
            border-color: var(--gms-primary);
            box-shadow: 0 0 0 3px var(--gms-primary-glow);
            outline: none;
            background-color: var(--gms-surface);
        }

        select.gms-form-control {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23475569' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506-.001z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            padding-right: 38px;
        }

        /* Modern action buttons aligned with theme */
        .gms-btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 600;
            font-size: 0.8125rem;
            padding: 10px 22px;
            border-radius: var(--gms-pill-radius);
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: var(--gms-transition);
        }

        .gms-btn-primary {
            background: linear-gradient(135deg, #10b981 0%, #007442 100%);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 116, 66, 0.25);
        }

        .gms-btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(0, 116, 66, 0.35);
            color: #ffffff;
            text-decoration: none;
        }

        .gms-btn-ghost {
            background: var(--gms-surface);
            color: var(--gms-text-secondary);
            border: 1px solid var(--gms-surface-muted);
        }

        .gms-btn-ghost:hover {
            background: var(--gms-surface-subtle);
            color: var(--gms-text-primary);
            transform: translateY(-1px);
            text-decoration: none;
        }

        /* Soft inline alert (rose) matching the dashboard notification palette */
        .gms-alert-soft {
            background-color: rgba(239, 68, 68, 0.06);
            border: 1px solid rgba(239, 68, 68, 0.20);
            border-radius: var(--gms-card-radius);
            padding: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            color: var(--gms-accent-rose);
            margin-bottom: 1.5rem;
        }

        .gms-alert-soft-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: rgba(239, 68, 68, 0.12);
            color: var(--gms-accent-rose);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }

        .gms-alert-soft-title {
            font-weight: 700;
            margin: 0 0 4px 0;
            font-size: 0.95rem;
            color: #be123c;
        }

        .gms-alert-soft-desc {
            margin: 0;
            font-size: 0.8125rem;
            color: #e11d48;
            line-height: 1.5;
        }

        /* Current-assignment mini summary card */
        .gms-current-staff {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 14px;
            background: rgba(0, 116, 66, 0.06);
            border: 1px solid rgba(0, 116, 66, 0.18);
            border-radius: 12px;
            font-size: 0.8125rem;
            color: var(--gms-text-secondary);
        }

        .gms-current-staff strong {
            color: var(--gms-primary);
            font-weight: 700;
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

                    <?php
                    $sql = "SELECT * FROM subzone AS a JOIN zone AS b ON b.zone_id = a.zone_id WHERE a.sz_id = '$subzone_id'";
                    $res = $conn->query($sql);
                    $row = $res->fetch_assoc();
                    $zone_name = $row['zone_name'];
                    $subzone_name = $row['sz_name'];
                    $zone_id = $row['zone_id'];

                    // Check if there is an active contract to assign SPV & PGS to
                    $sql_contract = "SELECT * FROM contract WHERE sz_id = '$subzone_id' AND c_status = 'Y'";
                    $res_contract = $conn->query($sql_contract);
                    $has_contract = ($res_contract && $res_contract->num_rows > 0);

                    $current_spv = '';
                    $current_pgs = '';
                    if ($has_contract) {
                        $contract_row = $res_contract->fetch_assoc();
                        $current_spv = $contract_row['spv_id'];
                        $current_pgs = $contract_row['pgs_id'];
                    }
                    ?>

                    <!-- ============================================================== -->
                    <!-- PAGE HEADER BANNER (Donezo-inspired gms-hero)                  -->
                    <!-- ============================================================== -->
                    <div class="gms-hero-banner gms-fade-in" style="padding: 1.5rem 2rem;">
                        <div class="d-flex align-items-center justify-content-between flex-wrap g-3">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="gms-pulse-dot" style="background:#34d399;"></span>
                                    <span class="gms-hero-subtitle" style="color: rgba(255,255,255,0.75);">Modul Tugasan Pegawai</span>
                                </div>
                                <h1 class="gms-hero-title mb-1">
                                    <i class="fa-solid fa-user-gear me-2"></i> Tugaskan Staf DBKU
                                </h1>
                                <p class="gms-hero-subtitle">
                                    Konfigur tugasan Penyelia &amp; Pengawas bagi sub-zon
                                    <strong style="color: #6ee7b7;"><?php echo htmlspecialchars($subzone_name); ?></strong>
                                    (Zon <?php echo htmlspecialchars($zone_name); ?>)
                                </p>
                            </div>
                            <div>
                                <button type="button" class="btn btn-light btn-sm rounded-pill px-3 py-2 font-weight-bold"
                                        onclick="history.back()" style="color: #007442;">
                                    <i class="fa-solid fa-arrow-left me-1"></i> Kembali
                                </button>
                            </div>
                        </div>
                    </div>

                    <?php if (!$has_contract) { ?>
                        <!-- ============================================================== -->
                        <!-- ALERT: NO ACTIVE CONTRACT                                       -->
                        <!-- ============================================================== -->
                        <div class="gms-alert-soft gms-fade-in gms-fade-in-delay-1">
                            <div class="gms-alert-soft-icon">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                            </div>
                            <div>
                                <h5 class="gms-alert-soft-title">Kontrak Aktif Diperlukan</h5>
                                <p class="gms-alert-soft-desc">
                                    Anda tidak boleh menugaskan pegawai DBKU kerana tiada kontrak aktif untuk sub-zon ini.
                                    Sila tugaskan kontraktor terlebih dahulu sebelum membuat penugasan staf.
                                </p>
                            </div>
                        </div>

                        <div class="d-flex justify-content-start mt-3 gms-fade-in gms-fade-in-delay-1">
                            <button class="gms-btn-action gms-btn-ghost" type="button" onclick="history.back()">
                                <i class="fa-solid fa-xmark"></i> Kembali
                            </button>
                        </div>
                    <?php } else { ?>
                        <!-- ============================================================== -->
                        <!-- ASSIGNMENT FORM CARD                                            -->
                        <!-- ============================================================== -->
                        <div class="row gms-fade-in gms-fade-in-delay-1">
                            <div class="col-12">
                                <div class="card shadow">
                                    <div class="card-body p-4">
                                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
                                            <div>
                                                <h4 class="gms-section-title">Tugasan Staf DBKU</h4>
                                                <p class="gms-section-subtitle">Pilih Penyelia (SPV) dan Pengawas (PGS) untuk sub-zon ini. Pegawai yang dipilih akan dipautkan kepada kontrak aktif semasa.</p>
                                            </div>
                                            <?php if (!empty($current_spv) || !empty($current_pgs)): ?>
                                            <div class="gms-current-staff">
                                                <i class="fa-solid fa-circle-check"></i>
                                                <span>Tugasan semasa: <strong><?php echo htmlspecialchars($current_spv ?: '-'); ?></strong> &middot; <strong><?php echo htmlspecialchars($current_pgs ?: '-'); ?></strong></span>
                                            </div>
                                            <?php endif; ?>
                                        </div>

                                        <form action="includes/assign_dbku_action.php" method="POST" class="forms-sample">
                                            <input type="text" name="zid" value="<?php echo $zone_id ?>" hidden>
                                            <input type="text" name="sz_id" value="<?php echo $subzone_id ?>" hidden>

                                            <div class="row g-3">
                                                <!-- Supervisor (SPV) Select -->
                                                <div class="col-md-6">
                                                    <div class="gms-form-group">
                                                        <label for="spv_id">Penyelia (SPV) <span class="req">*</span></label>
                                                        <select name="spv_id" id="spv_id" class="gms-form-control">
                                                            <option value="">-- Pilih Penyelia --</option>
                                                            <?php
                                                            $sql_spv = "SELECT * FROM users WHERE user_type = 'SPV' AND status = 'Y' ORDER BY user_id ASC";
                                                            $res_spv = $conn->query($sql_spv);
                                                            if ($res_spv && $res_spv->num_rows > 0) {
                                                                while ($row_spv = $res_spv->fetch_assoc()) {
                                                                    $selected = ($row_spv['uid'] == $current_spv) ? 'selected' : '';
                                                                    echo "<option value='{$row_spv['uid']}' {$selected}>" . htmlspecialchars($row_spv['user_id']) . " (" . htmlspecialchars($row_spv['user_email']) . ")</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>

                                                <!-- Pengawas (PGS) Select -->
                                                <div class="col-md-6">
                                                    <div class="gms-form-group">
                                                        <label for="pgs_id">Pengawas (PGS) <span class="req">*</span></label>
                                                        <select name="pgs_id" id="pgs_id" class="gms-form-control">
                                                            <option value="">-- Pilih Pengawas --</option>
                                                            <?php
                                                            $sql_pgs = "SELECT * FROM users WHERE user_type = 'PGS' AND status = 'Y' ORDER BY user_id ASC";
                                                            $res_pgs = $conn->query($sql_pgs);
                                                            if ($res_pgs && $res_pgs->num_rows > 0) {
                                                                while ($row_pgs = $res_pgs->fetch_assoc()) {
                                                                    $selected = ($row_pgs['uid'] == $current_pgs) ? 'selected' : '';
                                                                    echo "<option value='{$row_pgs['uid']}' {$selected}>" . htmlspecialchars($row_pgs['user_id']) . " (" . htmlspecialchars($row_pgs['user_email']) . ")</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Submission Row -->
                                            <div class="d-flex justify-content-end gap-2 mt-4 pt-3" style="border-top: 1px solid var(--gms-border);">
                                                <button class="gms-btn-action gms-btn-ghost" type="button" onclick="history.back()">
                                                    <i class="fa-solid fa-xmark"></i> Batal
                                                </button>
                                                <button type="submit" class="gms-btn-action gms-btn-primary">
                                                    <i class="fa-solid fa-check"></i> Tugaskan Staf
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>

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
    // Check if there is a status message in the session
    if (isset($_SESSION['status'])) {
        $status = $_SESSION['status'];   // 'success' or 'error'
        $message = $_SESSION['message']; // Error or success message

        // Clear session message after use
        unset($_SESSION['status']);
        unset($_SESSION['message']);

        $icon = ($status == 'success') ? 'success' : 'error';
        echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    Swal.fire({
                        title: "' . addslashes($message) . '",
                        icon: "' . $icon . '",
                    });
                });
            </script>';
    }
    ?>

</body>

</html>

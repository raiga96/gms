<?php
session_start();
require 'includes/connection.php';

// Cast & validate zone id (hardening — prevents SQL injection via id)
$zone_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch zone name
$zone_name = 'Zon Tidak Diketahui';
$sql_zone = "SELECT zone_name FROM zone WHERE zone_id = $zone_id";
$res_zone = $conn->query($sql_zone);
if ($res_zone && $res_zone->num_rows > 0) {
    $zone_name = $res_zone->fetch_assoc()['zone_name'];
}

// =============================================================================
// ZONE METRICS (total subzone, perimeter, area)
// =============================================================================
$sql_metrics = "
    SELECT
        COUNT(*) AS total_subzone,
        COALESCE(SUM(CAST(perimeter AS DECIMAL(12,2))), 0) AS total_perimeter,
        COALESCE(SUM(CAST(area AS DECIMAL(12,2))), 0) AS total_area
    FROM subzone
    WHERE zone_id = $zone_id
";
$res_metrics = $conn->query($sql_metrics);
$m = ($res_metrics && $row = $res_metrics->fetch_assoc()) ? $row : ['total_subzone' => 0, 'total_perimeter' => 0, 'total_area' => 0];
$total_subzone = (int)$m['total_subzone'];
$total_perimeter = (float)$m['total_perimeter'];
$total_area = (float)$m['total_area'];

// Active contracts in this zone
$sql_contracts = "
    SELECT COUNT(*) AS total_contract
    FROM contract ct
    JOIN subzone sz ON ct.sz_id = sz.sz_id
    WHERE sz.zone_id = $zone_id AND ct.c_status = 'Y'
";
$res_contracts = $conn->query($sql_contracts);
$total_contract = ($res_contracts && $row = $res_contracts->fetch_assoc()) ? (int)$row['total_contract'] : 0;

// =============================================================================
// SUBZONE LISTING (with per-subzone active contract counts — single query, no N+1)
// =============================================================================
$subzones_list = [];
$sql_subzones = "
    SELECT sz.sz_id, sz.sz_name,
           CAST(sz.perimeter AS DECIMAL(12,2)) AS perimeter,
           CAST(sz.area AS DECIMAL(12,2)) AS area,
           COUNT(ct.contract_id) AS contract_count
    FROM subzone sz
    LEFT JOIN contract ct ON ct.sz_id = sz.sz_id AND ct.c_status = 'Y'
    WHERE sz.zone_id = $zone_id
    GROUP BY sz.sz_id, sz.sz_name, sz.perimeter, sz.area
    ORDER BY sz.sz_name ASC
";
$res_subzones = $conn->query($sql_subzones);
if ($res_subzones && $res_subzones->num_rows > 0) {
    while ($row = $res_subzones->fetch_assoc()) {
        $subzones_list[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Butiran Zon — Green Management System (DBKU)</title>
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
                                    <span class="gms-hero-subtitle" style="color: rgba(255,255,255,0.75);">Modul Butiran Zon</span>
                                </div>
                                <h1 class="gms-hero-title mb-1"><i class="fa-solid fa-location-dot me-2"></i><?php echo htmlspecialchars($zone_name); ?></h1>
                                <p class="gms-hero-subtitle">Senarai sub-zon, kontrak aktif dan kawalan operasi kawasan</p>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <a href="zone.php" class="btn btn-outline-light btn-sm rounded-pill px-3 py-2 font-weight-bold" title="Kembali ke senarai zon">
                                    <i class="fa-solid fa-arrow-left me-1"></i> Senarai Zon
                                </a>
                                <a href="add_subzone.php?id=<?php echo $zone_id; ?>" class="btn btn-light btn-sm rounded-pill px-3 py-2 font-weight-bold" style="color: #007442;">
                                    <i class="fa-solid fa-plus me-1"></i> Daftar Sub-Zon Baharu
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================================== -->
                    <!-- KPI METRIC CARDS (Donezo-inspired — first card gradient green) -->
                    <!-- ============================================================== -->
                    <div class="row g-3 mb-4 gms-fade-in gms-fade-in-delay-1">
                        <!-- KPI 1: Total Sub-Zone (featured gradient) -->
                        <div class="col-sm-6 col-xl-3">
                            <div class="gms-kpi-card kpi-featured">
                                <div>
                                    <div class="gms-kpi-header">
                                        <span class="gms-kpi-label">Jumlah Sub-Zon</span>
                                        <div class="gms-kpi-icon">
                                            <i class="icon-layers"></i>
                                        </div>
                                    </div>
                                    <div class="gms-kpi-value" data-countup="<?php echo $total_subzone; ?>">0</div>
                                </div>
                                <div class="gms-kpi-footer">
                                    <span style="background: rgba(255,255,255,0.12); color: #6ee7b7; padding: 3px 9px; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;">
                                        <i class="fa-solid fa-layer-group"></i> Pecahan Kawasan
                                    </span>
                                    <span>Zon <?php echo htmlspecialchars($zone_name); ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- KPI 2: Active Contracts -->
                        <div class="col-sm-6 col-xl-3">
                            <div class="gms-kpi-card kpi-soft kpi-cyan">
                                <div>
                                    <div class="gms-kpi-header">
                                        <span class="gms-kpi-label">Kontrak Aktif</span>
                                        <div class="gms-kpi-icon">
                                            <i class="icon-doc"></i>
                                        </div>
                                    </div>
                                    <div class="gms-kpi-value" data-countup="<?php echo $total_contract; ?>">0</div>
                                </div>
                                <div class="gms-kpi-footer">
                                    <span class="gms-kpi-badge badge-cyan-soft">
                                        <i class="fa-solid fa-file-contract"></i> Kontrak Rasmi
                                    </span>
                                    <span>c_status = Y</span>
                                </div>
                            </div>
                        </div>

                        <!-- KPI 3: Total Perimeter -->
                        <div class="col-sm-6 col-xl-3">
                            <div class="gms-kpi-card kpi-soft kpi-indigo">
                                <div>
                                    <div class="gms-kpi-header">
                                        <span class="gms-kpi-label">Jumlah Perimeter</span>
                                        <div class="gms-kpi-icon">
                                            <i class="icon-size-fullscreen"></i>
                                        </div>
                                    </div>
                                    <div class="gms-kpi-value"><?php echo number_format($total_perimeter, 0); ?> <small style="font-size: 0.9rem; font-weight: 600;">m</small></div>
                                </div>
                                <div class="gms-kpi-footer">
                                    <span class="gms-kpi-badge badge-indigo-soft">
                                        <i class="fa-solid fa-ruler"></i> Ukuran Sempadan
                                    </span>
                                    <span>Geospatial</span>
                                </div>
                            </div>
                        </div>

                        <!-- KPI 4: Total Area -->
                        <div class="col-sm-6 col-xl-3">
                            <div class="gms-kpi-card kpi-soft kpi-amber">
                                <div>
                                    <div class="gms-kpi-header">
                                        <span class="gms-kpi-label">Jumlah Keluasan</span>
                                        <div class="gms-kpi-icon">
                                            <i class="icon-globe"></i>
                                        </div>
                                    </div>
                                    <div class="gms-kpi-value"><?php echo number_format($total_area, 2); ?> <small style="font-size: 0.9rem; font-weight: 600;">ha</small></div>
                                </div>
                                <div class="gms-kpi-footer">
                                    <span class="gms-kpi-badge badge-amber-soft">
                                        <i class="fa-solid fa-chart-area"></i> Kawasan Operasi
                                    </span>
                                    <span>Hektar</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- KPI END -->

                    <!-- ============================================================== -->
                    <!-- SUB-ZONE LISTING TABLE                                          -->
                    <!-- ============================================================== -->
                    <div class="row mb-4 gms-fade-in gms-fade-in-delay-2">
                        <div class="col-12">
                            <div class="card shadow gms-table-card">
                                <div class="card-body p-4">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
                                        <div>
                                            <h4 class="gms-section-title">Senarai Sub-Zon</h4>
                                            <p class="gms-section-subtitle">Klik nama sub-zon untuk butiran kontrak dan rekod harian.</p>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="gms-table-search">
                                                <i class="icon-magnifier"></i>
                                                <input type="text" id="subzoneTableSearch" placeholder="Cari nama sub-zon..." autocomplete="off">
                                            </div>
                                            <a href="add_subzone.php?id=<?php echo $zone_id; ?>" class="btn btn-success btn-sm rounded-pill px-3 py-2 font-weight-semibold">
                                                <i class="fa-solid fa-plus me-1"></i> Sub-Zon Baharu
                                            </a>
                                        </div>
                                    </div>

                                    <div class="gms-table-wrapper">
                                        <table class="gms-table" id="subzoneTable">
                                            <thead>
                                                <tr>
                                                    <th>Nama Sub-Zon</th>
                                                    <th class="text-center">Kontrak Aktif</th>
                                                    <th class="text-end">Perimeter (m)</th>
                                                    <th class="text-end">Keluasan (ha)</th>
                                                    <th style="width: 120px; text-align: center;">Tindakan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if (!empty($subzones_list)) {
                                                    foreach ($subzones_list as $sz_row) {
                                                        $sz_id = (int)$sz_row['sz_id'];
                                                        $sz_name = htmlspecialchars($sz_row['sz_name']);
                                                        $sz_perimeter = (float)$sz_row['perimeter'];
                                                        $sz_area = (float)$sz_row['area'];
                                                        $sz_contracts = (int)$sz_row['contract_count'];

                                                        echo "<tr>";
                                                        echo "<td>
                                                                <a class=\"zone-link font-weight-bold text-dark\" href=\"subzone_details.php?zid={$zone_id}&szid={$sz_id}\" style=\"text-decoration: none;\">
                                                                    <span class=\"gms-contractor-avatar me-2 shadow-sm\" style=\"width: 32px; height: 32px; font-size: 0.8rem; border-radius: 8px; background: rgba(0, 116, 66, 0.08); color: #007442; display: inline-flex; align-items: center; justify-content: center; vertical-align: middle;\">
                                                                        <i class=\"fa-solid fa-signs-post\"></i>
                                                                    </span>
                                                                    {$sz_name}
                                                                </a>
                                                              </td>";
                                                        echo "<td class=\"text-center\">
                                                                <span class=\"gms-badge-contract\">
                                                                    <i class=\"fa-solid fa-file-contract me-1\"></i>{$sz_contracts} Kontrak
                                                                </span>
                                                              </td>";
                                                        echo "<td class=\"text-end font-weight-semibold text-secondary\" style=\"font-family: monospace;\">" . number_format($sz_perimeter, 0) . " m</td>";
                                                        echo "<td class=\"text-end font-weight-semibold text-secondary\" style=\"font-family: monospace;\">" . number_format($sz_area, 2) . " ha</td>";
                                                        echo "<td style=\"text-align: center;\">
                                                                <div class=\"action-btn-group d-inline-flex gap-2\">
                                                                    <a href=\"update_sz.php?szid={$sz_id}\" class=\"btn-circle-edit\" title=\"Kemaskini Sub-Zon\">
                                                                        <i class=\"fa fa-pencil\"></i>
                                                                    </a>
                                                                    <button type=\"button\" class=\"btn-circle-delete\" data-id=\"{$sz_id}\" data-name=\"{$sz_name}\" onclick=\"deleteDb(this)\" title=\"Padam Sub-Zon\">
                                                                        <i class=\"fa fa-trash\"></i>
                                                                    </button>
                                                                </div>
                                                              </td>";
                                                        echo "</tr>";
                                                    }
                                                } else {
                                                    echo "<tr><td colspan='5' class='text-center text-muted py-5'><i class='icon-info me-2'></i>Tiada rekod sub-zon ditemui untuk zon ini</td></tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="d-flex flex-wrap align-items-center justify-content-between mt-4 gap-3">
                                        <p class="text-muted mb-0 small" id="tableRecordCount">Memaparkan <?php echo count($subzones_list); ?> sub-zon di bawah Zon <?php echo htmlspecialchars($zone_name); ?></p>
                                        <span class="badge bg-light text-muted border rounded-pill small px-3 py-1">
                                            <i class="fa-solid fa-database me-1 text-success"></i> Data Langsung
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- TABLE END -->

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

    <!-- Action button styles (consistent with zone.php) -->
    <style>
        .action-btn-group .btn-circle-edit {
            width: 34px; height: 34px; border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            background: rgba(0, 116, 66, 0.08); color: #007442;
            border: 1px solid rgba(0, 116, 66, 0.15);
            font-size: 0.8rem; text-decoration: none;
            transition: all 0.2s ease;
        }
        .action-btn-group .btn-circle-edit:hover {
            background: #007442; color: #fff; border-color: #007442;
            transform: scale(1.08); box-shadow: 0 4px 10px rgba(0, 116, 66, 0.2);
            text-decoration: none;
        }
        .action-btn-group .btn-circle-delete {
            width: 34px; height: 34px; border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            background: rgba(239, 68, 68, 0.08); color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.15);
            font-size: 0.8rem; cursor: pointer;
            transition: all 0.2s ease;
        }
        .action-btn-group .btn-circle-delete:hover {
            background: #ef4444; color: #fff; border-color: #ef4444;
            transform: scale(1.08); box-shadow: 0 4px 10px rgba(239, 68, 68, 0.2);
        }
    </style>

    <script>
        function deleteDb(btn) {
            let id = btn.dataset.id;
            let name = btn.dataset.name || 'sub-zon ini';

            Swal.fire({
                title: "Padam Sub-Zon?",
                html: "Adakah anda pasti mahu memadam <b>" + name + "</b>?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#007442",
                confirmButtonText: "Ya, padam",
                cancelButtonText: "Batal"
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch("includes/delete_subzone.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/x-www-form-urlencoded" },
                        body: "id=" + id
                    })
                    .then(res => res.text())
                    .then(data => {
                        if (data.trim() == "success") {
                            Swal.fire({
                                title: "Dipadam!",
                                text: name + " telah dialih keluar.",
                                icon: "success"
                            }).then(() => { location.reload(); });
                        } else {
                            Swal.fire("Ralat!", "Gagal memadam sub-zon.", "error");
                        }
                    });
                }
            });
        }

        // Table live search + count-up animation
        document.addEventListener("DOMContentLoaded", function() {
            var searchInput = document.getElementById('subzoneTableSearch');
            var table = document.getElementById('subzoneTable');
            var countLabel = document.getElementById('tableRecordCount');
            if (searchInput && table) {
                searchInput.addEventListener('keyup', function() {
                    var filter = searchInput.value.toLowerCase().trim();
                    var rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
                    var visibleCount = 0;
                    for (var i = 0; i < rows.length; i++) {
                        var text = rows[i].textContent.toLowerCase();
                        if (text.indexOf(filter) > -1) {
                            rows[i].style.display = '';
                            visibleCount++;
                        } else {
                            rows[i].style.display = 'none';
                        }
                    }
                    if (countLabel) {
                        if (filter === '') {
                            countLabel.textContent = 'Memaparkan <?php echo count($subzones_list); ?> sub-zon di bawah Zon <?php echo htmlspecialchars($zone_name); ?>';
                        } else {
                            countLabel.textContent = 'Ditemui ' + visibleCount + ' sub-zon sepadan dengan carian "' + filter + '"';
                        }
                    }
                });
            }

            // Count-up animation for KPI values
            var countEls = document.querySelectorAll('[data-countup]');
            countEls.forEach(function(el) {
                var target = parseFloat(el.getAttribute('data-countup')) || 0;
                if (target === 0) { el.textContent = '0'; return; }
                var duration = 1000;
                var startTime = null;
                function step(ts) {
                    if (!startTime) startTime = ts;
                    var progress = Math.min((ts - startTime) / duration, 1);
                    var eased = 1 - Math.pow(1 - progress, 4);
                    var current = target * eased;
                    el.textContent = Math.floor(current).toLocaleString();
                    if (progress < 1) { requestAnimationFrame(step); }
                    else { el.textContent = target.toLocaleString(); }
                }
                requestAnimationFrame(step);
            });
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

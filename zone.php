<?php
session_start();
require 'includes/connection.php';

// Total Zone
$sql = "SELECT COUNT(*) AS total_zone FROM zone";
$res = $conn->query($sql);
$total_zone = ($res && $row = $res->fetch_assoc()) ? (int)$row['total_zone'] : 0;

// Total Sub-zone
$sql1 = "SELECT COUNT(*) AS total_subzone FROM subzone";
$res1 = $conn->query($sql1);
$total_subzone = ($res1 && $row1 = $res1->fetch_assoc()) ? (int)$row1['total_subzone'] : 0;

// Total Perimeter(m)
$sql2 = "SELECT COALESCE(SUM(CAST(perimeter AS DECIMAL(12,2))), 0) AS total_perimeter FROM subzone";
$res2 = $conn->query($sql2);
$total_perimeter = ($res2 && $row2 = $res2->fetch_assoc()) ? (float)$row2['total_perimeter'] : 0;

// Total Area(ha)
$sql3 = "SELECT COALESCE(SUM(CAST(area AS DECIMAL(12,2))), 0) AS total_area FROM subzone";
$res3 = $conn->query($sql3);
$total_area = ($res3 && $row3 = $res3->fetch_assoc()) ? (float)$row3['total_area'] : 0;

// Zone list with subzone counts (single optimized query — no N+1)
$zones_list = [];
$sql_zones = "
    SELECT z.zone_id, z.zone_name,
        COUNT(sz.sz_id) AS sz_count,
        COALESCE(SUM(CAST(sz.perimeter AS DECIMAL(12,2))), 0) AS total_perimeter,
        COALESCE(SUM(CAST(sz.area AS DECIMAL(12,2))), 0) AS total_area
    FROM zone z
    LEFT JOIN subzone sz ON sz.zone_id = z.zone_id
    GROUP BY z.zone_id, z.zone_name
    ORDER BY z.zone_name ASC
";
$res_zones = $conn->query($sql_zones);
if ($res_zones && $res_zones->num_rows > 0) {
    while ($row = $res_zones->fetch_assoc()) {
        $zones_list[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Pengurusan Zon — Green Management System (DBKU)</title>
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
                                <h1 class="gms-hero-title mb-1">Pengurusan Zon &amp; Sub-Zon</h1>
                                <p class="gms-hero-subtitle">Senarai dan kawalan zon pentadbiran DBKU</p>
                            </div>
                            <div>
                                <a href="add_zone.php" class="btn btn-light btn-sm rounded-pill px-3 py-2 font-weight-bold" style="color: #007442;">
                                    <i class="fa-solid fa-plus me-1"></i> Daftar Zon Baharu
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================================== -->
                    <!-- KPI METRIC CARDS (Donezo-inspired — first card gradient green) -->
                    <!-- ============================================================== -->
                    <div class="row g-3 mb-4 gms-fade-in gms-fade-in-delay-1">
                        <!-- KPI 1: Total Zone (featured gradient) -->
                        <div class="col-sm-6 col-xl-3">
                            <div class="gms-kpi-card kpi-featured">
                                <div>
                                    <div class="gms-kpi-header">
                                        <span class="gms-kpi-label">Jumlah Zon</span>
                                        <div class="gms-kpi-icon">
                                            <i class="icon-map"></i>
                                        </div>
                                    </div>
                                    <div class="gms-kpi-value" data-countup="<?php echo $total_zone; ?>">0</div>
                                </div>
                                <div class="gms-kpi-footer">
                                    <span style="background: rgba(255,255,255,0.12); color: #6ee7b7; padding: 3px 9px; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;">
                                        <i class="fa-solid fa-layer-group"></i> Zon Aktif
                                    </span>
                                    <span>Pentadbiran DBKU</span>
                                </div>
                            </div>
                        </div>

                        <!-- KPI 2: Total Sub-Zone -->
                        <div class="col-sm-6 col-xl-3">
                            <div class="gms-kpi-card kpi-soft kpi-cyan">
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
                                    <span class="gms-kpi-badge badge-cyan-soft">
                                        <i class="fa-solid fa-circle-check"></i> Pecahan Kawasan
                                    </span>
                                    <span><?php echo $total_zone; ?> Zon</span>
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
                    <!-- ZONE LISTING TABLE                                             -->
                    <!-- ============================================================== -->
                    <div class="row mb-4 gms-fade-in gms-fade-in-delay-2">
                        <div class="col-12">
                            <div class="card shadow gms-table-card">
                                <div class="card-body p-4">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
                                        <div>
                                            <h4 class="gms-section-title">Senarai Zon DBKU</h4>
                                            <p class="gms-section-subtitle">Klik nama zon untuk butiran terperinci sub-zon dan kontrak.</p>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="gms-table-search">
                                                <i class="icon-magnifier"></i>
                                                <input type="text" id="zoneTableSearch" placeholder="Cari nama zon..." autocomplete="off">
                                            </div>
                                            <a href="add_zone.php" class="btn btn-success btn-sm rounded-pill px-3 py-2 font-weight-semibold">
                                                <i class="fa-solid fa-plus me-1"></i> Zon Baharu
                                            </a>
                                        </div>
                                    </div>

                                    <div class="gms-table-wrapper">
                                        <table class="gms-table" id="zoneTable">
                                            <thead>
                                                <tr>
                                                    <th>Nama Zon</th>
                                                    <th class="text-center">Bil. Sub-Zon</th>
                                                    <th class="text-end">Perimeter (m)</th>
                                                    <th class="text-end">Keluasan (ha)</th>
                                                    <th style="width: 120px; text-align: center;">Tindakan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if (!empty($zones_list)) {
                                                    foreach ($zones_list as $z_row) {
                                                        $z_name = htmlspecialchars($z_row['zone_name']);
                                                        $z_id = (int)$z_row['zone_id'];
                                                        $sz_count = (int)$z_row['sz_count'];
                                                        $z_perimeter = (float)$z_row['total_perimeter'];
                                                        $z_area = (float)$z_row['total_area'];

                                                        echo "<tr>";
                                                        echo "<td>
                                                                <a class=\"zone-link font-weight-bold text-dark\" href=\"zone_details.php?id={$z_id}\" style=\"text-decoration: none;\">
                                                                    <span class=\"gms-contractor-avatar me-2 shadow-sm\" style=\"width: 32px; height: 32px; font-size: 0.8rem; border-radius: 8px; background: rgba(0, 116, 66, 0.08); color: #007442; display: inline-flex; align-items: center; justify-content: center; vertical-align: middle;\">
                                                                        <i class=\"fa-solid fa-location-dot\"></i>
                                                                    </span>
                                                                    {$z_name}
                                                                </a>
                                                              </td>";
                                                        echo "<td class=\"text-center\">
                                                                <span class=\"gms-badge-contract\">
                                                                    <i class=\"fa-solid fa-layer-group me-1\"></i>{$sz_count} Sub-Zon
                                                                </span>
                                                              </td>";
                                                        echo "<td class=\"text-end font-weight-semibold text-secondary\" style=\"font-family: monospace;\">" . number_format($z_perimeter, 0) . " m</td>";
                                                        echo "<td class=\"text-end font-weight-semibold text-secondary\" style=\"font-family: monospace;\">" . number_format($z_area, 2) . " ha</td>";
                                                        echo "<td style=\"text-align: center;\">
                                                                <div class=\"action-btn-group d-inline-flex gap-2\">
                                                                    <a href=\"update_zone.php?id={$z_id}\" class=\"btn-circle-edit\" title=\"Kemaskini Zon\">
                                                                        <i class=\"fa fa-pencil\"></i>
                                                                    </a>
                                                                    <button type=\"button\" class=\"btn-circle-delete\" data-id=\"{$z_id}\" onclick=\"deleteDb(this)\" title=\"Padam Zon\">
                                                                        <i class=\"fa fa-trash\"></i>
                                                                    </button>
                                                                </div>
                                                              </td>";
                                                        echo "</tr>";
                                                    }
                                                } else {
                                                    echo "<tr><td colspan='5' class='text-center text-muted py-5'><i class='icon-info me-2'></i>Tiada rekod zon ditemui</td></tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="d-flex flex-wrap align-items-center justify-content-between mt-4 gap-3">
                                        <p class="text-muted mb-0 small" id="tableRecordCount">Memaparkan <?php echo count($zones_list); ?> zon berdaftar DBKU</p>
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

    <!-- Action button styles (moved from inline CSS to keep consistent) -->
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
            let row = btn.closest("tr");
            let zone = row.cells[0].innerText.trim();

            Swal.fire({
                title: "Padam Zon?",
                html: "Adakah anda pasti mahu memadam <b>" + zone + "</b> dan semua sub-zon di bawahnya?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#007442",
                confirmButtonText: "Ya, padam",
                cancelButtonText: "Batal"
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch("includes/delete_zone.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/x-www-form-urlencoded" },
                        body: "id=" + id
                    })
                    .then(res => res.text())
                    .then(data => {
                        if (data.trim() == "success") {
                            Swal.fire({
                                title: "Dipadam!",
                                text: zone + " telah dialih keluar.",
                                icon: "success"
                            }).then(() => { location.reload(); });
                        } else {
                            Swal.fire("Ralat!", "Gagal memadam zon.", "error");
                        }
                    });
                }
            });
        }

        // Table live search
        document.addEventListener("DOMContentLoaded", function() {
            var searchInput = document.getElementById('zoneTableSearch');
            var table = document.getElementById('zoneTable');
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
                            countLabel.textContent = 'Memaparkan <?php echo count($zones_list); ?> zon berdaftar DBKU';
                        } else {
                            countLabel.textContent = 'Ditemui ' + visibleCount + ' zon sepadan dengan carian "' + filter + '"';
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

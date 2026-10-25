<?php
session_start();
require 'includes/connection.php';

// =============================================================================
// KPI METRICS
// =============================================================================
// Total Contractors
$sql_tc = "SELECT COUNT(*) AS total_contractor FROM contractor";
$res_tc = $conn->query($sql_tc);
$total_contractor = ($res_tc && $row = $res_tc->fetch_assoc()) ? (int)$row['total_contractor'] : 0;

// Active contracts + total fund (single optimized query)
$sql_stats = "SELECT
    COUNT(CASE WHEN c_status = 'Y' THEN 1 END) AS active_contracts,
    COALESCE(SUM(CASE WHEN c_status = 'Y' THEN CAST(fund AS DECIMAL(12,2)) ELSE 0 END), 0) AS total_fund
FROM contract";
$res_stats = $conn->query($sql_stats);
$stats = ($res_stats && $row = $res_stats->fetch_assoc()) ? $row : ['active_contracts' => 0, 'total_fund' => 0];
$active_contracts = (int)$stats['active_contracts'];
$total_fund = (float)$stats['total_fund'];
$avg_fund = ($total_contractor > 0) ? ($total_fund / $total_contractor) : 0;

// =============================================================================
// CONTRACTOR LIST (single query — no N+1, no raw SQL interpolation)
// =============================================================================
$contractors_list = [];
$sql_contractors = "
    SELECT c.cid, c.name, c.address,
        COUNT(CASE WHEN ct.c_status = 'Y' THEN ct.contract_id END) AS contract_count,
        COALESCE(SUM(CASE WHEN ct.c_status = 'Y' THEN CAST(ct.fund AS DECIMAL(12,2)) ELSE 0 END), 0) AS total_fund
    FROM contractor c
    LEFT JOIN contract ct ON c.cid = ct.cid
    GROUP BY c.cid, c.name, c.address
    ORDER BY c.name ASC
";
$res_contractors = $conn->query($sql_contractors);
if ($res_contractors && $res_contractors->num_rows > 0) {
    while ($row = $res_contractors->fetch_assoc()) {
        $contractors_list[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Pengurusan Kontraktor — Green Management System (DBKU)</title>
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
                                    <span class="gms-hero-subtitle" style="color: rgba(255,255,255,0.75);">Modul Pengurusan Kontraktor</span>
                                </div>
                                <h1 class="gms-hero-title mb-1">Pengurusan Kontraktor</h1>
                                <p class="gms-hero-subtitle">Senarai firma kontraktor pemotongan rumput DBKU</p>
                            </div>
                            <div>
                                <a href="new_contractor.php?page=con" class="btn btn-light btn-sm rounded-pill px-3 py-2 font-weight-bold" style="color: #007442;">
                                    <i class="fa-solid fa-plus me-1"></i> Daftar Kontraktor Baharu
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================================== -->
                    <!-- KPI METRIC CARDS (Donezo-inspired — first card gradient green) -->
                    <!-- ============================================================== -->
                    <div class="row g-3 mb-4 gms-fade-in gms-fade-in-delay-1">
                        <!-- KPI 1: Total Contractors (featured gradient) -->
                        <div class="col-sm-6 col-xl-3">
                            <div class="gms-kpi-card kpi-featured">
                                <div>
                                    <div class="gms-kpi-header">
                                        <span class="gms-kpi-label">Jumlah Kontraktor</span>
                                        <div class="gms-kpi-icon">
                                            <i class="icon-people"></i>
                                        </div>
                                    </div>
                                    <div class="gms-kpi-value" data-countup="<?php echo $total_contractor; ?>">0</div>
                                </div>
                                <div class="gms-kpi-footer">
                                    <span style="background: rgba(255,255,255,0.12); color: #6ee7b7; padding: 3px 9px; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;">
                                        <i class="fa-solid fa-circle-check"></i> Berdaftar
                                    </span>
                                    <span>Firma DBKU</span>
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
                                    <div class="gms-kpi-value" data-countup="<?php echo $active_contracts; ?>">0</div>
                                </div>
                                <div class="gms-kpi-footer">
                                    <span class="gms-kpi-badge badge-cyan-soft">
                                        <i class="fa-solid fa-file-contract"></i> Kontrak Rasmi
                                    </span>
                                    <span>Status Aktif</span>
                                </div>
                            </div>
                        </div>

                        <!-- KPI 3: Total Contract Value -->
                        <div class="col-sm-6 col-xl-3">
                            <div class="gms-kpi-card kpi-soft kpi-indigo">
                                <div>
                                    <div class="gms-kpi-header">
                                        <span class="gms-kpi-label">Nilai Kontrak</span>
                                        <div class="gms-kpi-icon">
                                            <i class="icon-wallet"></i>
                                        </div>
                                    </div>
                                    <div class="gms-kpi-value">RM <?php echo number_format($total_fund / 1000, 1); ?><small style="font-size: 0.9rem; font-weight: 600;">K</small></div>
                                </div>
                                <div class="gms-kpi-footer">
                                    <span class="gms-kpi-badge badge-indigo-soft">
                                        <i class="fa-solid fa-coins"></i> Peruntukan Aktif
                                    </span>
                                    <span>Jumlah Dana</span>
                                </div>
                            </div>
                        </div>

                        <!-- KPI 4: Average Value per Contractor -->
                        <div class="col-sm-6 col-xl-3">
                            <div class="gms-kpi-card kpi-soft kpi-amber">
                                <div>
                                    <div class="gms-kpi-header">
                                        <span class="gms-kpi-label">Purata / Kontraktor</span>
                                        <div class="gms-kpi-icon">
                                            <i class="icon-chart"></i>
                                        </div>
                                    </div>
                                    <div class="gms-kpi-value">RM <?php echo number_format($avg_fund, 0); ?></div>
                                </div>
                                <div class="gms-kpi-footer">
                                    <span class="gms-kpi-badge badge-amber-soft">
                                        <i class="fa-solid fa-chart-line"></i> Nilai Purata
                                    </span>
                                    <span>Setiap Firma</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- KPI END -->

                    <!-- ============================================================== -->
                    <!-- CONTRACTOR LISTING TABLE                                        -->
                    <!-- ============================================================== -->
                    <div class="row mb-4 gms-fade-in gms-fade-in-delay-2">
                        <div class="col-12">
                            <div class="card shadow gms-table-card">
                                <div class="card-body p-4">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
                                        <div>
                                            <h4 class="gms-section-title">Senarai Kontraktor DBKU</h4>
                                            <p class="gms-section-subtitle">Klik nama kontraktor untuk butiran terperinci kontrak dan rekod kerja.</p>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="gms-table-search">
                                                <i class="icon-magnifier"></i>
                                                <input type="text" id="contractorTableSearch" placeholder="Cari nama atau alamat kontraktor..." autocomplete="off">
                                            </div>
                                            <a href="new_contractor.php?page=con" class="btn btn-success btn-sm rounded-pill px-3 py-2 font-weight-semibold">
                                                <i class="fa-solid fa-plus me-1"></i> Kontraktor Baharu
                                            </a>
                                        </div>
                                    </div>

                                    <div class="gms-table-wrapper">
                                        <table class="gms-table" id="contractorTable">
                                            <thead>
                                                <tr>
                                                    <th>Nama Kontraktor</th>
                                                    <th>Alamat</th>
                                                    <th class="text-center">Kontrak</th>
                                                    <th class="text-end">Nilai (RM)</th>
                                                    <th style="width: 120px; text-align: center;">Tindakan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if (!empty($contractors_list)) {
                                                    foreach ($contractors_list as $c_row) {
                                                        $c_name = htmlspecialchars($c_row['name']);
                                                        $c_id = (int)$c_row['cid'];
                                                        $c_address = htmlspecialchars($c_row['address'] ?? '-');
                                                        $contract_count = (int)$c_row['contract_count'];
                                                        $fund = (float)$c_row['total_fund'];
                                                        $first_letter = mb_strtoupper(mb_substr($c_name, 0, 1));

                                                        echo "<tr>";
                                                        echo "<td>
                                                                <a class=\"font-weight-bold text-dark\" href=\"contractor_details?id={$c_id}\" style=\"text-decoration: none;\">
                                                                    <div class=\"d-flex align-items-center\">
                                                                        <span class=\"gms-contractor-avatar me-2 shadow-sm\" style=\"width: 32px; height: 32px; font-size: 0.8rem; border-radius: 8px; background: rgba(0, 116, 66, 0.08); color: #007442; display: inline-flex; align-items: center; justify-content: center; vertical-align: middle;\">
                                                                            {$first_letter}
                                                                        </span>
                                                                        <div>
                                                                            <div class=\"font-weight-bold text-dark\" style=\"font-size: 0.9rem;\">{$c_name}</div>
                                                                            <small class=\"text-muted\">CID: #{$c_id}</small>
                                                                        </div>
                                                                    </div>
                                                                </a>
                                                              </td>";
                                                        echo "<td class=\"font-weight-semibold text-secondary\" style=\"max-width: 220px; white-space: normal; word-break: break-word; line-height: 1.5;\">{$c_address}</td>";
                                                        echo "<td class=\"text-center\">
                                                                <span class=\"gms-badge-contract\">
                                                                    <i class=\"fa-solid fa-briefcase me-1\"></i>{$contract_count} Kontrak
                                                                </span>
                                                              </td>";
                                                        echo "<td class=\"text-end font-weight-semibold text-secondary\" style=\"font-family: monospace;\">RM " . number_format($fund, 2) . "</td>";
                                                        echo "<td style=\"text-align: center;\">
                                                                <div class=\"action-btn-group d-inline-flex gap-2\">
                                                                    <a href=\"update_contractor.php?id={$c_id}\" class=\"btn-circle-edit\" title=\"Kemaskini Kontraktor\">
                                                                        <i class=\"fa fa-pencil\"></i>
                                                                    </a>
                                                                    <button type=\"button\" class=\"btn-circle-delete\" data-id=\"{$c_id}\" onclick=\"deleteDb(this)\" title=\"Padam Kontraktor\">
                                                                        <i class=\"fa fa-trash\"></i>
                                                                    </button>
                                                                </div>
                                                              </td>";
                                                        echo "</tr>";
                                                    }
                                                } else {
                                                    echo "<tr><td colspan='5' class='text-center text-muted py-5'><i class='icon-info me-2'></i>Tiada rekod kontraktor ditemui</td></tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="d-flex flex-wrap align-items-center justify-content-between mt-4 gap-3">
                                        <p class="text-muted mb-0 small" id="tableRecordCount">Memaparkan <?php echo count($contractors_list); ?> kontraktor berdaftar DBKU</p>
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
            let row = btn.closest("tr");
            let contractor = row.cells[0].innerText.trim();

            Swal.fire({
                title: "Padam Kontraktor?",
                html: "Adakah anda pasti mahu memadam <b>" + contractor + "</b>?",
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
                                text: contractor + " telah dialih keluar.",
                                icon: "success"
                            }).then(() => { location.reload(); });
                        } else {
                            Swal.fire("Ralat!", "Gagal memadam kontraktor.", "error");
                        }
                    });
                }
            });
        }

        // Table live search
        document.addEventListener("DOMContentLoaded", function() {
            var searchInput = document.getElementById('contractorTableSearch');
            var table = document.getElementById('contractorTable');
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
                            countLabel.textContent = 'Memaparkan <?php echo count($contractors_list); ?> kontraktor berdaftar DBKU';
                        } else {
                            countLabel.textContent = 'Ditemui ' + visibleCount + ' kontraktor sepadan dengan carian "' + filter + '"';
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

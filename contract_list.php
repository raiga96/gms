<?php
session_start();
require 'includes/connection.php';

if (($_GET['action'] ?? '') === 'new') {
    header('Location: new_contract.php');
    exit;
}

// =============================================================================
// KPI METRICS (Consolidated single query — no redundant roundtrips)
// =============================================================================
$sql_c_metrics = "SELECT
    COUNT(*) AS total_contract,
    COALESCE(SUM(CAST(fund AS DECIMAL(14,2))), 0) AS total_value,
    COALESCE(SUM(CASE WHEN c_status = 'Y' THEN 1 ELSE 0 END), 0) AS active_contract
FROM contract";
$res_c_metrics = $conn->query($sql_c_metrics);
$c_metrics = ($res_c_metrics && $row = $res_c_metrics->fetch_assoc()) ? $row : ['total_contract' => 0, 'total_value' => 0, 'active_contract' => 0];
$total_contract = (int)$c_metrics['total_contract'];
$total_contract_value = (float)$c_metrics['total_value'];
$active_contract = (int)$c_metrics['active_contract'];

// Total Contractor
$sql_tcon = "SELECT COUNT(*) AS total_contractor FROM contractor";
$res_tcon = $conn->query($sql_tcon);
$total_contractor = ($res_tcon && $row = $res_tcon->fetch_assoc()) ? (int)$row['total_contractor'] : 0;

// =============================================================================
// CONTRACT LISTING (single JOIN query â€” eliminates legacy N+1 per-row queries)
// =============================================================================
$contracts_list = [];
$sql_contracts = "
    SELECT ct.contract_id, ct.contract_number, ct.fund, ct.c_status, ct.sz_id, ct.cid,
           c.name AS contractor_name,
           sz.sz_name,
           z.zone_id, z.zone_name
    FROM contract ct
    LEFT JOIN contractor c ON ct.cid = c.cid
    LEFT JOIN subzone sz ON ct.sz_id = sz.sz_id
    LEFT JOIN zone z ON sz.zone_id = z.zone_id
    ORDER BY ct.contract_number ASC
";
$res_contracts = $conn->query($sql_contracts);
if ($res_contracts && $res_contracts->num_rows > 0) {
    while ($row = $res_contracts->fetch_assoc()) {
        $contracts_list[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Senarai Kontrak &mdash; Green Management System (DBKU)</title>
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
                                    <span class="gms-hero-subtitle" style="color: rgba(255,255,255,0.75);">Modul Pengurusan Kontrak</span>
                                </div>
                                <h1 class="gms-hero-title mb-1">Pengurusan Senarai Kontrak</h1>
                                <p class="gms-hero-subtitle">Daftar, pandang dan urus kontrak pemotongan rumput DBKU</p>
                            </div>
                            <div>
                                <a href="contract_list.php?action=new" class="btn btn-light btn-sm rounded-pill px-3 py-2 font-weight-bold" style="color: #007442;">
                                    <i class="fa-solid fa-plus me-1"></i> Daftar Kontrak Baharu
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================================== -->
                    <!-- KPI METRIC CARDS (Donezo-inspired â€” first card gradient green) -->
                    <!-- ============================================================== -->
                    <div class="row g-3 mb-4 gms-fade-in gms-fade-in-delay-1">
                        <!-- KPI 1: Total Contract (featured gradient) -->
                        <div class="col-sm-6 col-xl-3">
                            <div class="gms-kpi-card kpi-featured">
                                <div>
                                    <div class="gms-kpi-header">
                                        <span class="gms-kpi-label">Jumlah Kontrak</span>
                                        <div class="gms-kpi-icon">
                                            <i class="icon-doc"></i>
                                        </div>
                                    </div>
                                    <div class="gms-kpi-value" data-countup="<?php echo $total_contract; ?>">0</div>
                                </div>
                                <div class="gms-kpi-footer">
                                    <span style="background: rgba(255,255,255,0.12); color: #6ee7b7; padding: 3px 9px; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;">
                                        <i class="fa-solid fa-file-contract"></i> Kontrak Rasmi
                                    </span>
                                    <span>Tender DBKU</span>
                                </div>
                            </div>
                        </div>

                        <!-- KPI 2: Total Contractor -->
                        <div class="col-sm-6 col-xl-3">
                            <div class="gms-kpi-card kpi-soft kpi-cyan">
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
                                    <span class="gms-kpi-badge badge-cyan-soft">
                                        <i class="fa-solid fa-circle-check"></i> Aktif Beroperasi
                                    </span>
                                    <span>Firma Berdaftar</span>
                                </div>
                            </div>
                        </div>

                        <!-- KPI 3: Total Contract Value -->
                        <div class="col-sm-6 col-xl-3">
                            <div class="gms-kpi-card kpi-soft kpi-indigo">
                                <div>
                                    <div class="gms-kpi-header">
                                        <span class="gms-kpi-label">Jumlah Nilai Kontrak</span>
                                        <div class="gms-kpi-icon">
                                            <i class="icon-wallet"></i>
                                        </div>
                                    </div>
                                    <div class="gms-kpi-value">RM <?php echo number_format($total_contract_value / 1000, 2); ?><small style="font-size: 0.9rem; font-weight: 600;">K</small></div>
                                </div>
                                <div class="gms-kpi-footer">
                                    <span class="gms-kpi-badge badge-indigo-soft">
                                        <i class="fa-solid fa-coins"></i> Peruntukan Keseluruhan
                                    </span>
                                    <span>Ringgit Malaysia</span>
                                </div>
                            </div>
                        </div>

                        <!-- KPI 4: Active Contract -->
                        <div class="col-sm-6 col-xl-3">
                            <div class="gms-kpi-card kpi-soft kpi-amber">
                                <div>
                                    <div class="gms-kpi-header">
                                        <span class="gms-kpi-label">Kontrak Aktif</span>
                                        <div class="gms-kpi-icon">
                                            <i class="icon-check"></i>
                                        </div>
                                    </div>
                                    <div class="gms-kpi-value" data-countup="<?php echo $active_contract; ?>">0</div>
                                </div>
                                <div class="gms-kpi-footer">
                                    <span class="gms-kpi-badge badge-amber-soft">
                                        <i class="fa-solid fa-rotate"></i> Beroperasi Kini
                                    </span>
                                    <span>Status: Aktif</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- KPI END -->

                    <!-- ============================================================== -->
                    <!-- CONTRACT LISTING TABLE                                          -->
                    <!-- ============================================================== -->
                    <div class="row mb-4 gms-fade-in gms-fade-in-delay-2">
                        <div class="col-12">
                            <div class="card shadow gms-table-card">
                                <div class="card-body p-4">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
                                        <div>
                                            <h4 class="gms-section-title">Senarai Kontrak DBKU</h4>
                                            <p class="gms-section-subtitle">Klik nombor kontrak untuk butiran terperinci sub-zon dan kontraktor.</p>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="gms-table-search">
                                                <i class="icon-magnifier"></i>
                                                <input type="text" id="contractTableSearch" placeholder="Cari nombor kontrak, kontraktor atau zon..." autocomplete="off">
                                            </div>
                                            <a href="contract_list.php?action=new" class="btn btn-success btn-sm rounded-pill px-3 py-2 font-weight-semibold">
                                                <i class="fa-solid fa-plus me-1"></i> Kontrak Baharu
                                            </a>
                                        </div>
                                    </div>

                                    <div class="gms-table-wrapper">
                                        <table class="gms-table" id="contractTable">
                                            <thead>
                                                <tr>
                                                    <th>Nombor Kontrak</th>
                                                    <th>Nama Kontraktor</th>
                                                    <th class="text-end">Nilai Kontrak (RM)</th>
                                                    <th>Zon</th>
                                                    <th class="text-center">Status</th>
                                                    <th style="width: 120px; text-align: center;">Tindakan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if (!empty($contracts_list)) {
                                                    foreach ($contracts_list as $ct_row) {
                                                        $ct_id = (int)$ct_row['contract_id'];
                                                        $ct_number = htmlspecialchars($ct_row['contract_number']);
                                                        $ct_fund = (float)$ct_row['fund'];
                                                        $ct_status = $ct_row['c_status'];
                                                        $ct_contractor = htmlspecialchars($ct_row['contractor_name'] ?? 'Tidak diketahui');
                                                        $ct_zone = htmlspecialchars($ct_row['zone_name'] ?? '-');
                                                        $ct_zone_id = (int)$ct_row['zone_id'];
                                                        $first_letter = mb_strtoupper(mb_substr($ct_row['contractor_name'] ?? '?', 0, 1));
                                                        $is_active = ($ct_status === 'Y');

                                                        echo "<tr>";
                                                        echo "<td>
                                                                <a class=\"zone-link font-weight-bold text-dark\" href=\"contract_details.php?id={$ct_id}\" style=\"text-decoration: none;\">
                                                                    <span class=\"gms-contractor-avatar me-2 shadow-sm\" style=\"width: 32px; height: 32px; font-size: 0.8rem; border-radius: 8px; background: rgba(0, 116, 66, 0.08); color: #007442; display: inline-flex; align-items: center; justify-content: center; vertical-align: middle;\">
                                                                        <i class=\"fa-solid fa-file-contract\"></i>
                                                                    </span>
                                                                    {$ct_number}
                                                                </a>
                                                              </td>";
                                                        echo "<td>
                                                                <div class='d-flex align-items-center'>
                                                                    <div class='gms-contractor-avatar me-3 shadow-sm'>{$first_letter}</div>
                                                                    <div>
                                                                        <div class='font-weight-bold text-dark' style='font-size: 0.9rem;'>{$ct_contractor}</div>
                                                                        <small class='text-muted'>CID: #" . htmlspecialchars($ct_row['cid'] ?? '-') . "</small>
                                                                    </div>
                                                                </div>
                                                              </td>";
                                                        echo "<td class=\"text-end font-weight-semibold text-secondary\" style=\"font-family: monospace;\">RM " . number_format($ct_fund, 2) . "</td>";
                                                        echo "<td>";
                                                        if (!empty($ct_row['zone_name'])) {
                                                            echo "<a class=\"zone-link\" href=\"zone_details.php?id={$ct_zone_id}\" style=\"text-decoration: none;\">
                                                                    <span class=\"gms-tag-pill\"><i class=\"fa-solid fa-location-dot me-1\"></i>{$ct_zone}</span>
                                                                  </a>";
                                                        } else {
                                                            echo "<span class=\"text-muted\">-</span>";
                                                        }
                                                        echo "</td>";
                                                        echo "<td class=\"text-center\">";
                                                        if ($is_active) {
                                                            echo "<span class=\"badge bg-success bg-opacity-10 text-success rounded-pill px-2\" style=\"font-size: 0.65rem;\"><i class=\"fa-solid fa-circle-check me-1\"></i>Aktif</span>";
                                                        } else {
                                                            echo "<span class=\"badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2\" style=\"font-size: 0.65rem;\"><i class=\"fa-solid fa-circle-minus me-1\"></i>Tidak Aktif</span>";
                                                        }
                                                        echo "</td>";
                                                        echo "<td style=\"text-align: center;\">";
                                                        echo "<div class=\"action-btn-group d-inline-flex gap-2\">";
                                                        echo "<a href=\"contract_details.php?id={$ct_id}\" class=\"btn-circle-edit\" title=\"Pandang & Kemaskini Kontrak\">
                                                                <i class=\"fa fa-eye\"></i>
                                                              </a>";
                                                        echo "<button type=\"button\" class=\"btn-circle-delete\" data-id=\"{$ct_id}\" onclick=\"deleteDb(this)\" title=\"Padam Kontrak\">
                                                                <i class=\"fa fa-trash\"></i>
                                                              </button>";
                                                        echo "</div>";
                                                        echo "</td>";
                                                        echo "</tr>";
                                                    }
                                                } else {
                                                    echo "<tr><td colspan='6' class='text-center text-muted py-5'><i class='icon-info me-2'></i>Tiada rekod kontrak ditemui</td></tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="d-flex flex-wrap align-items-center justify-content-between mt-4 gap-3">
                                        <p class="text-muted mb-0 small" id="tableRecordCount">Memaparkan <?php echo count($contracts_list); ?> kontrak berdaftar DBKU</p>
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
            let contract = row.cells[0].innerText.trim();

            Swal.fire({
                title: "Padam Kontrak?",
                html: "Adakah anda pasti mahu memadam kontrak <b>" + contract + "</b>? Semua rekod berkaitan akan terjejas.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#007442",
                confirmButtonText: "Ya, padam",
                cancelButtonText: "Batal"
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch("includes/delete_contract.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/x-www-form-urlencoded" },
                        body: "id=" + id
                    })
                    .then(res => res.text())
                    .then(data => {
                        if (data.trim() == "success") {
                            Swal.fire({
                                title: "Dipadam!",
                                text: contract + " telah dialih keluar.",
                                icon: "success"
                            }).then(() => { location.reload(); });
                        } else {
                            Swal.fire("Ralat!", "Gagal memadam kontrak. Sila cuba lagi.", "error");
                        }
                    });
                }
            });
        }

        // Table live search
        document.addEventListener("DOMContentLoaded", function() {
            var searchInput = document.getElementById('contractTableSearch');
            var table = document.getElementById('contractTable');
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
                            countLabel.textContent = 'Memaparkan <?php echo count($contracts_list); ?> kontrak berdaftar DBKU';
                        } else {
                            countLabel.textContent = 'Ditemui ' + visibleCount + ' kontrak sepadan dengan carian "' + filter + '"';
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

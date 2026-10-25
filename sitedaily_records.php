<?php
require 'includes/connection.php';

$cid = (int)($_GET['id'] ?? 0);

if ($cid <= 0) {
    // Cari rekod tertunda atau rekod harian terkini jika tiada parameter ID
    $fallback_res = $conn->query("SELECT id FROM daily_records WHERE verified = 'N' ORDER BY date DESC, id DESC LIMIT 1");
    if (!$fallback_res || $fallback_res->num_rows === 0) {
        $fallback_res = $conn->query("SELECT id FROM daily_records ORDER BY id DESC LIMIT 1");
    }
    if ($fallback_res && $row_fb = $fallback_res->fetch_assoc()) {
        header("Location: sitedaily_records.php?id=" . (int)$row_fb['id']);
        exit;
    }
    http_response_code(404);
    exit('Tiada rekod harian tersedia dalam pangkalan data.');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verify_aso'])) {
    $ver_comment = trim($_POST['ver_comment'] ?? '');
    $verify_by = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : (isset($_SESSION['id']) ? (int)$_SESSION['id'] : 1);

    $stmt = $conn->prepare("UPDATE daily_records SET verified = 'Y', verify_by = ?, verified_on = NOW(), ver_comment = ? WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("isi", $verify_by, $ver_comment, $cid);
        if ($stmt->execute()) {
            $_SESSION['status'] = "success";
            $_SESSION['message'] = "Rekod berjaya disahkan.";
        } else {
            $_SESSION['status'] = "error";
            $_SESSION['message'] = "Ralat semasa mengesahkan rekod: " . $stmt->error;
        }
        $stmt->close();
    }

    header("Location: sitedaily_records.php?id=" . $cid);
    exit();
}
?>
<!DOCTYPE html>
<html lang="ms">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Green Management System - Rekod Harian</title>
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
        /* Functional CSS preserved - styling that Bootstrap cannot replicate */
        .modern-record-container .custom-checkbox-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .modern-record-container .custom-checkbox {
            appearance: none;
            -webkit-appearance: none;
            width: 22px;
            height: 22px;
            border: 2px solid #cbd5e1;
            border-radius: 6px;
            outline: none;
            display: inline-block;
            position: relative;
            cursor: not-allowed;
            background-color: #f8fafc;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .modern-record-container .custom-checkbox:checked {
            background-color: #10b981;
            border-color: #10b981;
        }

        .modern-record-container .custom-checkbox.blue-checkbox:checked {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }

        .modern-record-container .custom-checkbox:checked::after {
            content: '';
            position: absolute;
            width: 5px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            top: 3px;
            left: 7px;
            transform: rotate(45deg);
        }

        .modern-record-container .modern-input-group {
            display: flex;
            align-items: center;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background-color: #ffffff;
            overflow: hidden;
            transition: all 0.2s ease;
            max-width: 150px;
            margin: 0 auto;
        }

        .modern-record-container .modern-input-group:focus-within {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        .modern-record-container .modern-input {
            width: 100%;
            border: none;
            background: transparent;
            padding: 6px 12px;
            font-size: 13px;
            font-weight: 500;
            color: #1e293b;
            outline: none;
            text-align: center;
        }

        .modern-record-container .modern-input[readonly] {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 600;
            cursor: default;
        }

        /* Weather radio labels - functional styling */
        .weather-radio-group input[type="radio"] {
            display: none;
        }

        .weather-radio-label {
            padding: 6px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            color: #64748b;
            cursor: not-allowed;
            transition: all 0.2s ease;
        }

        .weather-radio-group input[type="radio"]:checked + .weather-radio-label.rainy-label {
            background-color: #3b82f6;
            color: white;
            border-color: #3b82f6;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .weather-radio-group input[type="radio"]:checked + .weather-radio-label.cloudy-label {
            background-color: #64748b;
            color: white;
            border-color: #64748b;
            box-shadow: 0 4px 12px rgba(100, 116, 139, 0.3);
        }

        .weather-radio-group input[type="radio"]:checked + .weather-radio-label.fine-label {
            background-color: #f59e0b;
            color: white;
            border-color: #f59e0b;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }

        /* Day cell */
        .gms-table .day-cell {
            font-weight: 600;
            color: #0f172a;
        }

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

                    <!-- Performance Indicator Start -->
                    <?php
                    $stmtC = $conn->prepare("
                        SELECT dr.*,
                               ct.contract_id, ct.contract_number, ct.start_date as contract_start, ct.end_date as contract_end,
                               ct.sz_id as contract_sz_id, ct.cid as contract_cid, ct.fund,
                               c.cid as contractor_id, c.name as contractor_name, c.email as contractor_email, c.address as contractor_address,
                               sz.sz_id, sz.sz_name, sz.perimeter, sz.area,
                               z.zone_id, z.zone_name
                        FROM daily_records dr
                        LEFT JOIN contract ct ON dr.cid = ct.contract_id
                        LEFT JOIN contractor c ON ct.cid = c.cid
                        LEFT JOIN subzone sz ON ct.sz_id = sz.sz_id
                        LEFT JOIN zone z ON sz.zone_id = z.zone_id
                        WHERE dr.id = ?
                    ");
                    $resC = false;
                    $rowC = null;
                    if ($stmtC) {
                        $stmtC->bind_param("i", $cid);
                        $stmtC->execute();
                        $resC = $stmtC->get_result();
                        $rowC = $resC ? $resC->fetch_assoc() : null;
                        $stmtC->close();
                    }

                    if (!$rowC) {
                        http_response_code(404);
                        exit('Rekod harian tidak ditemui.');
                    }
                    $row3 = $rowC;
                    $row = $rowC;
                    $row1 = $rowC;
                    $row2 = $rowC;
                    $rowCon = $rowC;
                    $contract_id = $rowC['contract_id'];
                    $contractor_id = $rowC['contractor_id'];
                    $sz_id = (int)($rowC['sz_id'] ?? 0);
                    $contractor = $rowC['contractor_name'] ?: 'Kontraktor';
                    $email = $rowC['contractor_email'] ?: '';
                    ?>

                    <!-- Hero Banner -->
                    <div class="row mt-0 mb-4">
                        <div class="col-12">
                            <div class="gms-hero-banner gms-fade-in">
                                <div class="d-sm-flex align-items-center justify-content-between flex-wrap gap-3">
                                    <div>
                                        <h2 class="gms-hero-title mb-1"><?php echo htmlspecialchars($contractor) ?></h2>
                                        <p class="gms-hero-subtitle mb-0">
                                             <i class="fas fa-calendar-alt me-1"></i> <?php echo gms_format_date($row3['date'], 'd F Y') ?>
                                             &nbsp;|&nbsp;
                                             <i class="fas fa-map-marker-alt me-1"></i> Zon: <?php echo htmlspecialchars(($rowC['zone_name'] ?? '') . ' - ' . ($rowC['sz_name'] ?? 'N/A')) ?>
                                         </p>
                                    </div>
                                    <div class="d-flex gap-4 flex-wrap">
                                        <div class="gms-hero-stat">
                                            <i class="gms-hero-stat-icon fas fa-file-alt"></i>
                                            <div>
                                                <div class="gms-hero-stat-value">1</div>
                                                <div class="gms-hero-stat-label">Rekod</div>
                                            </div>
                                        </div>
                                        <div class="gms-hero-stat">
                                            <i class="gms-hero-stat-icon fas fa-check-circle <?php echo $row3['verified'] === 'Y' ? 'text-success' : 'text-muted' ?>"></i>
                                            <div>
                                                <div class="gms-hero-stat-value"><?php echo $row3['verified'] === 'Y' ? 'Ya' : 'Tidak' ?></div>
                                                <div class="gms-hero-stat-label">Disahkan</div>
                                            </div>
                                        </div>
                                        <div class="gms-hero-stat">
                                            <i class="gms-hero-stat-icon fas fa-pen text-primary"></i>
                                            <div>
                                                <div class="gms-hero-stat-value"><?php echo $row3['contractor_dsign'] === 'Y' ? 'Ya' : 'Tidak' ?></div>
                                                <div class="gms-hero-stat-label">Kontraktor</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php
                    // --- Fetch Site Instructions & Demerits grouped by area_id ---
                    $demerits_by_area = [];
                    $reference_by_area = [];
                    $si_numbers = [];
                    $reference = [];

                    $sql_si = "SELECT
                        b.num_si,
                        b.area_id,
                        b.si_category,
                        b.reference,
                        COALESCE(sub_dps.total_demerit, 0) AS demerit_point_sebenar
                    FROM daily_records AS a
                    JOIN site_instruction AS b ON b.ddId = a.id
                    LEFT JOIN (
                        SELECT
                            b_sub.si_id AS actual_id,
                            SUM(c_sub.demerit_point) AS total_demerit
                        FROM site_instruction AS b_sub
                        JOIN dps AS c_sub ON
                            c_sub.dem_cate = b_sub.si_category
                            AND JSON_CONTAINS(
                                b_sub.reference,
                                JSON_QUOTE(CONCAT(c_sub.ref, '(', c_sub.numb, ')')),
                                '$'
                            )
                        GROUP BY b_sub.si_id
                    ) AS sub_dps ON sub_dps.actual_id = b.si_id
                    WHERE a.id = ?";

                    $stmt_si = $conn->prepare($sql_si);
                    $res_si_all = false;
                    if ($stmt_si) {
                        $stmt_si->bind_param("i", $cid);
                        $stmt_si->execute();
                        $res_si_all = $stmt_si->get_result();
                    }
                    if ($res_si_all && $res_si_all->num_rows > 0) {
                        while ($row_si = $res_si_all->fetch_assoc()) {
                            // $demerits_by_area[$row_si['area_id']] = $row_si['demerit_point_sebenar'];
                            $demerits_by_area[$row_si['area_id']][$row_si['si_category']] = $row_si['demerit_point_sebenar'];
                            $reference_by_area[$row_si['area_id']][$row_si['si_category']] = $row_si['reference'];
                            if (!empty($row_si['num_si'])) {
                                $si_numbers[] = $row_si['num_si'];
                            }
                        }
                    }
                    ?>


                    <div class="row modern-record-container">
                        <div class="col-12 grid-margin stretch-card mb-0">
                            <div class="card shadow">
                                <div class="card-body">
                                    <!-- Title & Meta Header -->
                                    <div class="d-sm-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
                                        <div class="mt-3 mt-sm-0">
                                            <span class="badge badge-outline-success font-weight-bold text-uppercase" style="font-size: 12px; padding: 8px 16px; border-radius: 30px; border: 1px solid #10b981; color: #10b981; background-color: #f0fdf4;">
                                                Zon: <?php echo $row2['sz_name'] ?>
                                            </span>
                                        </div>
                                        <div class="mt-3 mt-sm-0 d-print-none d-flex gap-2">
                                            <a href="sitedaily_attachment.php?id=<?php echo $cid ?>" class="btn btn-info btn-sm text-white" style="border-radius: 8px;">
                                                <i class="icon-paper-clip"></i> Lampiran
                                            </a>
                                            <a href="sitedaily_si.php?id=<?php echo $cid ?>" class="btn btn-warning btn-sm text-dark" style="border-radius: 8px;">
                                                <i class="icon-doc"></i> SI
                                            </a>
                                            <a href="sitedailyrecordpdf.php?id=<?php echo $cid ?>"  class="btn btn-primary btn-sm" style="border-radius: 8px;">
                                                <i class="icon-printer"></i> Cetak
                                            </a>
                                        </div>
                                    </div>
                                    <div class="text-center mb-4">
                                        <h6 class="mb-1 font-weight-bold text-dark text-uppercase" style="font-size: 16px;">Rekod Harian Tapak</h6>
                                        <span class="text-uppercase text-muted font-weight-bold" style="font-size: 17px; letter-spacing: 0.15em;">Bahagian Landskap</span>

                                        <p class="text-muted mb-0 text-uppercase" style="font-size: 13px;">Penyelenggaraan Taman Berkualiti - Pemotongan Rumput & Landskap Lembut</p>
                                    </div>
                                    <div class="d-sm-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
                                        <div class="mt-3 mt-sm-0">
                                            <h6 class="text-uppercase font-weight-bold">
                                                Zon: <?php echo $row1['contract_number'].'-'.$row2['sz_name']; ?>
                                            </h6>
                                        </div>
                                        <div class="mt-3 mt-sm-0">
                                            <h6 class="text-uppercase">
                                                Tarikh: <?php echo gms_format_date($row3['date'], 'd F Y'); ?>
                                            </h6>
                                        </div>
                                    </div>

                                    <!-- Table 1: Calendar / Week Schedule -->
                                    <div class="gms-table-wrapper mb-2">
                                        <table class="gms-table">
                                            <thead>
                                                <tr>
                                                    <th rowspan="2" style="width:120px; background-color: #f8fafc;">Hari</th>
                                                    <th colspan="3" class="section-header" style="background:#f0fdf4; color:#166534; border-bottom-color: #bbf7d0;">Pemotongan Rumput</th>
                                                    <th colspan="3" class="section-header" style="background:#eff6ff; color:#1e40af; border-bottom-color: #bfdbfe;">Pembersihan Longkang</th>
                                                </tr>
                                                <tr>
                                                    <th style="background:#f0fdf4; color:#166534; width: 80px;">Status</th>
                                                    <th style="background:#f0fdf4; color:#166534; width:120px;">Sesi</th>
                                                    <th style="background:#f0fdf4; color:#166534;">Hari Ke-</th>
                                                    <th style="background:#eff6ff; color:#1e40af; width: 80px;">Status</th>
                                                    <th style="background:#eff6ff; color:#1e40af; width:120px;">Sesi</th>
                                                    <th style="background:#eff6ff; color:#1e40af;">Hari Ke-</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbody"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Two-Column Layout for Activities & Weather -->
                        <div class="col-lg-8 grid-margin stretch-card">
                            <div class="card shadow h-100">
                                <div class="card-body">
                                    <h4 class="card-title mb-4" style="color: #0f172a; font-weight:600; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">Ringkasan Aktiviti</h4>

                                    <div class="gms-table-wrapper">
                                        <table class="gms-table">
                                            <thead>
                                                <tr>
                                                    <th class="no-col" style="width: 50px;">No.</th>
                                                    <th class="act-col">Aktiviti</th>
                                                    <th class="workers-col">Bilangan Pekerja</th>
                                                    <th class="tools-col">Mesin / Peralatan</th>
                                                    <th class="comment-col">Catatan / Masa</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Row 1: Grass Cutting -->
                                                <tr>
                                                    <td class="no-col">1</td>
                                                    <td><strong>Pemotongan Rumput</strong></td>
                                                    <td class="text-center font-weight-bold text-dark" style="font-size: 14px;"><?php echo $row3['gc_workers'] ?></td>
                                                    <td class="text-center font-weight-bold text-dark"><?php echo $row3['gc_tools'] ?></td>
                                                    <td>
                                                        <div class="modern-input-group">
                                                            <span style="font-size: 11px; padding-left: 8px; color: #64748b; font-weight: 500;">Datang:</span>
                                                            <input type="text" class="modern-input" value="" placeholder="--:--" readonly>
                                                        </div>
                                                    </td>
                                                </tr>

                                                <!-- Row 4: Drain Cleaning -->
                                                <tr>
                                                    <td class="no-col">2</td>
                                                    <td class="sub-act" style=""><strong>Pembersihan Longkang</strong></td>
                                                    <td class="text-center font-weight-bold text-dark" style="font-size: 14px;"><?php echo $row3['dc_workers'] ?></td>
                                                    <td class="text-center font-weight-bold text-dark"><?php echo $row3['dc_tools'] ?></td>
                                                    <td class="text-dark font-weight-bold"><div class="modern-input-group">
                                                            <span style="font-size: 11px; padding-left: 8px; color: #64748b; font-weight: 500;">Pulang:</span>
                                                            <input type="text" class="modern-input" value="" placeholder="--:--" readonly>
                                                        </div></td>
                                                </tr>
                                                <!-- Row 5: Total -->
                                                <tr style="background-color: #f8fafc; font-weight: 600;">
                                                    <td colspan="2" class="text-right" style="padding-right: 15px; font-weight: 600; color: #0f172a;">Jumlah:</td>
                                                    <td class="text-center text-primary font-weight-bold" style="font-size: 15px;"><?php
                                                    if($row3['dc_workers'] == null || $row3['dc_workers'] == "" || $row3['dc_workers'] == '0'){ $dc_worker = 0; } else {$dc_worker = $row3['dc_workers'];} echo $row3['gc_workers'] + $dc_worker ?></td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Weather & Time of Inspection -->
                        <div class="col-lg-4 grid-margin stretch-card">
                            <div class="card shadow h-100">
                                <div class="card-body d-flex flex-column justify-content-between">
                                    <div>
                                        <h4 class="card-title mb-4" style="color: #0f172a; font-weight: 600; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">Cuaca & Pemeriksaan</h4>

                                        <!-- Weather Box -->
                                        <div class="weather-widget">
                                            <div class="weather-clock-wrap" >
                                                <svg class="oval" width="120" height="64" viewBox="0 0 120 64">
                                                    <ellipse cx="60" cy="32" rx="50" ry="26" fill="none" stroke="#64748b" stroke-width="1.2" />
                                                    <line x1="60" y1="4" x2="60" y2="60" stroke="#64748b" stroke-width="1" />
                                                    <line x1="10" y1="32" x2="110" y2="32" stroke="#64748b" stroke-width="1" />
                                                    <text x="60" y="-2" text-anchor="middle" font-size="9" fill="#64748b" font-weight="500">0600</text>
                                                    <text x="60" y="72" text-anchor="middle" font-size="9" fill="#64748b" font-weight="500">1200</text>
                                                    <text x="-4" y="36" text-anchor="end" font-size="9" fill="#64748b" font-weight="500">1600</text>
                                                    <text x="124" y="36" text-anchor="start" font-size="9" fill="#64748b" font-weight="500">0900</text>
                                                </svg>
                                            </div>

                                            <!-- Weather Selection Radio Buttons -->
                                            <div class="weather-radio-group">
                                                <input type="radio" id="weather-rainy" name="weather" value="Rainy" <?php if ($row3['weather'] == 'Rainy') echo 'checked'; ?> disabled>
                                                <label for="weather-rainy" class="weather-radio-label rainy-label">Hujan</label>

                                                <input type="radio" id="weather-cloudy" name="weather" value="Cloudy" <?php if ($row3['weather'] == 'Cloudy') echo 'checked'; ?> disabled>
                                                <label for="weather-cloudy" class="weather-radio-label cloudy-label">Mendung</label>

                                                <input type="radio" id="weather-fine" name="weather" value="Fine" <?php if ($row3['weather'] == 'Fine') echo 'checked'; ?> disabled>
                                                <label for="weather-fine" class="weather-radio-label fine-label">Cerah</label>
                                            </div>


                                        </div>

                                        <!-- AM / PM Inspection Time -->
                                        <div class="inspection-card">
                                            <div class="text-center mb-3 pt-0">
                                                <div class="time-inspect">( Masa pemeriksaan )</div>
                                            </div>
                                            <div class="time-grid">
                                                <div class="time-cell-modern">
                                                    <span class="time-cell-title">Masa AM</span>
                                                    <input type="text" class="time-cell-value" value="" placeholder="--:--" readonly>
                                                </div>
                                                <div class="time-cell-modern">
                                                    <span class="time-cell-title">Masa PM</span>
                                                    <input type="text" class="time-cell-value" value="" placeholder="--:--" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-12 grid-margin stretch-card">
                            <div class="card shadow h-100">
                                <div class="card-body d-flex flex-column justify-content-between">
                                    <h4 class="card-title mb-4" style="color: #0f172a; font-weight:600; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">Kawasan</h4>

                                    <div class="gms-table-wrapper">
                                        <table class="gms-table">
                                            <thead>
                                                <tr>
                                                    <th class="no-col" style="width: 50px;">Kawasan</th>
                                                    <th class="act-col" style="width: 300px;">Lokasi</th>
                                                    <th class="workers-col" style="width: 100px;">Aktiviti</th>
                                                    <th class="tools-col" style="width: 100px;">Kemajuan <br> (kerja)</th>
                                                    <th class="comment-col">Catatan</th>
                                                    <th style="width: 150px;">Rujukan</th>
                                                    <th class="comment-col" style="width: 100px;">Jumlah <br> Mata Penalti</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                 <?php
                                                 $stmt_loc = $conn->prepare("
                                                     SELECT cl.*, adr.gc_progress, adr.gc_comment, adr.dc_progress, adr.dc_comment
                                                     FROM contract_location cl
                                                     LEFT JOIN area_daily_records adr ON adr.area_id = cl.area AND adr.dr_id = ?
                                                     WHERE cl.sz_id = ?
                                                     ORDER BY cl.locId ASC
                                                 ");
                                                 if ($stmt_loc) {
                                                     $stmt_loc->bind_param("ii", $cid, $sz_id);
                                                     $stmt_loc->execute();
                                                     $res = $stmt_loc->get_result();
                                                 } else {
                                                     $res = false;
                                                 }
                                                 if ($res && $res->num_rows > 0) {
                                                     while ($row = $res->fetch_assoc()) {
                                                        $area_no = $row['area'];
                                                        $locId = $row['locId'];
                                                        $row1 = $row;

                                                        $status_map = [
                                                            '0' => 'X',
                                                            '1' => '—',
                                                            '2' => '✓',
                                                            '3' => 'O'
                                                        ];
                                                        $gc_progress = isset($row1['gc_progress']) && array_key_exists($row1['gc_progress'], $status_map) ? $status_map[$row1['gc_progress']] : '-';
                                                        $gc_comment = !empty($row1['gc_comment']) ? htmlspecialchars($row1['gc_comment']) : '-';
                                                        $dc_progress = isset($row1['dc_progress']) && array_key_exists($row1['dc_progress'], $status_map) ? $status_map[$row1['dc_progress']] : '-';
                                                        $dc_comment = !empty($row1['dc_comment']) ? htmlspecialchars($row1['dc_comment']) : '-';

                                                        // Get the demerit point from precalculated array
                                                        $demerit_val_gc = isset($demerits_by_area[$locId]['GC']) && $demerits_by_area[$locId]['GC'] > 0 ? $demerits_by_area[$locId]['GC'] : '-';
                                                        $demerit_val_dc = isset($demerits_by_area[$locId]['DC']) && $demerits_by_area[$locId]['DC'] > 0 ? $demerits_by_area[$locId]['DC'] : '-';

                                                        // Get the reference from array
                                                        // Ambil data asal (jika tiada, default kepada string kosong '[]')
                                                        $raw_gc = $reference_by_area[$locId]['GC'] ?? '-';
                                                        $raw_dc = $reference_by_area[$locId]['DC'] ?? '-';

                                                        // Fungsi pendek untuk proses JSON -> Teks bersih
                                                        $clean_format = function($data) {
                                                            // Decode JSON string menjadi array PHP
                                                            $array = json_decode($data, true);

                                                            // Pastikan ia adalah array dan tidak kosong
                                                            if (is_array($array) && !empty($array)) {
                                                                return implode(', ', $array); // Gabungkan array jadi "A(i), C(ii)"
                                                            }

                                                            // Jika bukan JSON/array biasa, return data asal atau '-' jika kosong
                                                            return (!empty($data) && $data !== '-') ? $data : '-';
                                                        };

                                                        // Guna fungsi di atas untuk dapatkan hasil bersih
                                                        $ref_gc = $clean_format($raw_gc);
                                                        $ref_dc = $clean_format($raw_dc);

                                                 ?>
                                                <tr>
                                                    <td rowspan="2"><?php echo $row['area'] ?></td>
                                                    <td rowspan="2" style="max-width: 200px; white-space: normal;"><strong style="word-break: break-word; overflow-wrap: break-word; display: block; line-height: 1.4;"><?php echo $row['location'] ?></strong></td>
                                                    <td>Pemotongan Rumput</td>
                                                    <td class="text-center font-weight-bold text-dark"><?php echo $gc_progress; ?></td>
                                                    <td class="text-dark font-weight-bold"><?php echo $gc_comment; ?></td>
                                                    <td class="text-center font-weight-bold text-dark" style="vertical-align: middle;"><?php echo $ref_gc ?></td>
                                                    <td class="text-center font-weight-bold text-dark" style="vertical-align: middle;"><?php echo $demerit_val_gc; ?></td>
                                                </tr>
                                                <tr>
                                                    <td>Pembersihan Longkang</td>
                                                    <td class="text-center font-weight-bold text-dark"><?php echo $dc_progress; ?></td>
                                                    <td class="text-dark font-weight-bold"><?php echo $dc_comment; ?></td>
                                                    <td class="text-center font-weight-bold text-dark" style="vertical-align: middle;"><?php echo $ref_dc ?></td>
                                                    <td class="text-center font-weight-bold text-dark" style="vertical-align: middle;"><?php echo $demerit_val_dc; ?></td>
                                                </tr>
                                                <?php }
                                            }
                                            ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-12 grid-margin stretch-card">
                            <div class="card shadow h-100">
                                <div class="card-body d-flex flex-column justify-content-between">
                                    <h5 class="font-weight-bold text-dark mb-3">Rekod Keselamatan Tapak</h5>
                                        <div class="gms-table-wrapper">
                                            <table class="gms-table">
                                                <tbody>
                                                    <tr>
                                                        <td >Arahan kepada kontraktor: </td>
                                                        <td colspan="3" style="width: 500px;" class="text-center">
                                                            S.I No. <?php echo !empty($si_numbers) ? implode(', ', array_unique($si_numbers)) : '......................./Lisan'; ?>
                                                        </td>
                                                        <td style="width: 200px;">Tidak</td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="5"><small class="text-muted">Nota: Wakil Kontraktor hendaklah menyemak dan memahami catatan ini sebelum menandatangani</small></td>
                                                    </tr>

                                                </tbody>
                                            </table>
                                        </div>
                                         <div class="gms-table-wrapper">
                                            <table class="gms-table">
                                                <tbody>
                                                    <tr>
                                                        <td class="text-center" style="font-weight: 600; background-color: #f8fafc; height: 40px; width: 33.33%;">Wakil Kontraktor <br> Tandatangan</td>
                                                        <td class="text-center" style="font-weight: 600; background-color: #f8fafc; height: 40px; width: 33.33%;">Wakil Pegawai Penguasa <br> (Penyelia) Tandatangan</td>
                                                        <td class="text-center" style="font-weight: 600; background-color: #f8fafc; height: 40px; width: 33.33%;">Wakil Pegawai Penguasa <br> (Pengawas) Tandatangan</td>
                                                    </tr>
                                                    <tr style="height: 110px; vertical-align: middle;">
                                                        <td class="text-center">
                                                            <?php
                                                            if ($row3 && $row3['contractor_dsign'] === 'Y') {
                                                                echo '
                                                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                        <path d="M12 2L14.39 4.26L17.61 3.57L18.3 6.79L21.27 8.25L20.21 11.39L22 14.18L19.53 16.36L19.06 19.56L15.93 19.86L14.07 22.45L12 21.32L9.93 22.45L8.07 19.86L4.94 19.56L4.47 16.36L2 14.18L3.79 11.39L2.73 8.25L5.7 6.79L6.39 3.57L9.61 3.57L12 2Z" fill="#10b981"/>
                                                                        <path d="M9 12L11 14L15 10" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                        <path d="M8 18L6 22L12 20L18 22L16 18" fill="#059669"/>
                                                                    </svg>
                                                                    <br>
                                                                <div style="display:inline-flex; flex-direction:column; align-items:center; justify-content:center; color:#10b981;">
                                                                    <span style="font-size:13px; font-weight:700; font-style:italic; margin-top:4px; color:#059669; letter-spacing:0.5px;">Telah Ditandatangani Secara Digital</span>
                                                                </div>';
                                                            } else {
                                                                echo '<span class="text-muted" style="font-style: italic;">...............................................</span>';
                                                            }
                                                            ?>
                                                        </td>
                                                        <td class="text-center">
                                                            <?php
                                                            if ($row3 && $row3['supervisor_dsign'] === 'Y') {
                                                                echo '
                                                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                        <path d="M12 2L14.39 4.26L17.61 3.57L18.3 6.79L21.27 8.25L20.21 11.39L22 14.18L19.53 16.36L19.06 19.56L15.93 19.86L14.07 22.45L12 21.32L9.93 22.45L8.07 19.86L4.94 19.56L4.47 16.36L2 14.18L3.79 11.39L2.73 8.25L5.7 6.79L6.39 3.57L9.61 3.57L12 2Z" fill="#10b981"/>
                                                                        <path d="M9 12L11 14L15 10" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                        <path d="M8 18L6 22L12 20L18 22L16 18" fill="#059669"/>
                                                                    </svg>
                                                                    <br>
                                                                <div style="display:inline-flex; flex-direction:column; align-items:center; justify-content:center; color:#10b981;">
                                                                    <span style="font-size:13px; font-weight:700; font-style:italic; margin-top:4px; color:#059669; letter-spacing:0.5px;">Telah Ditandatangani Secara Digital</span>
                                                                </div>';
                                                            } else {
                                                                echo '<span class="text-muted" style="font-style: italic;">...............................................</span>';
                                                            }
                                                            ?>
                                                        </td>
                                                        <td class="text-center">
                                                            <?php
                                                            if ($row3 && $row3['pengawas_dsign'] === 'Y') {
                                                                echo '
                                                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                        <path d="M12 2L14.39 4.26L17.61 3.57L18.3 6.79L21.27 8.25L20.21 11.39L22 14.18L19.53 16.36L19.06 19.56L15.93 19.86L14.07 22.45L12 21.32L9.93 22.45L8.07 19.86L4.94 19.56L4.47 16.36L2 14.18L3.79 11.39L2.73 8.25L5.7 6.79L6.39 3.57L9.61 3.57L12 2Z" fill="#10b981"/>
                                                                        <path d="M9 12L11 14L15 10" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                        <path d="M8 18L6 22L12 20L18 22L16 18" fill="#059669"/>
                                                                    </svg>
                                                                    <br>
                                                                <div style="display:inline-flex; flex-direction:column; align-items:center; justify-content:center; color:#10b981;">
                                                                    <span style="font-size:13px; font-weight:700; font-style:italic; margin-top:4px; color:#059669; letter-spacing:0.5px;">Telah Ditandatangani Secara Digital</span>
                                                                </div>';
                                                            } else {
                                                                echo '<span class="text-muted" style="font-style: italic;">...............................................</span>';
                                                            }
                                                            ?>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="3" class="text-center" style="font-weight: 600; padding: 12px; background-color: #f8fafc; position: relative;">
                                                            Pengesahan &amp; Catatan Oleh ASO
                                                        </td>
                                                    </tr>
                                                    <tr style="min-height: 60px;">
                                                        <td colspan="3" class="text-center text-dark" style="font-weight: 500; padding: 15px; font-style: italic;">
                                                             <?php
                                                            if ($row3 && $row3['pengawas_dsign'] === 'Y') {
                                                                echo '
                                                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                        <path d="M12 2L14.39 4.26L17.61 3.57L18.3 6.79L21.27 8.25L20.21 11.39L22 14.18L19.53 16.36L19.06 19.56L15.93 19.86L14.07 22.45L12 21.32L9.93 22.45L8.07 19.86L4.94 19.56L4.47 16.36L2 14.18L3.79 11.39L2.73 8.25L5.7 6.79L6.39 3.57L9.61 3.57L12 2Z" fill="#10b981"/>
                                                                        <path d="M9 12L11 14L15 10" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                        <path d="M8 18L6 22L12 20L18 22L16 18" fill="#059669"/>
                                                                    </svg>
                                                                    <br>
                                                                <div style="display:inline-flex; flex-direction:column; align-items:center; justify-content:center; color:#10b981;">
                                                                    <span style="font-size:13px; font-weight:700; font-style:italic; margin-top:4px; color:#059669; letter-spacing:0.5px;">Telah Ditandatangani Secara Digital</span>
                                                                </div>';
                                                            } else {
                                                                echo '<span class="text-muted" style="font-style: italic;">...............................................</span>';
                                                            }
                                                            ?>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="3" class="text-center text-dark" style="font-weight: 500; padding: 15px; font-style: italic;">
                                                            <?php
                                                            // Collect comments from site instructions
                                                            $instructions = [];
                                                            $res_si_all->data_seek(0); // Reset result pointer
                                                            while ($row_si = $res_si_all->fetch_assoc()) {
                                                                if (!empty($row_si['instruction'])) {
                                                                    $instructions[] = htmlspecialchars($row_si['instruction']);
                                                                }
                                                            }
                                                            echo !empty($instructions) ? implode('; ', array_unique($instructions)) : 'Tiada catatan daripada ASO';
                                                            ?>
                                                            <div class="m-2"></div>
                                                            <br>
                                                            <?php if ($row3 && $row3['verified'] == 'N'): ?>
                                                                <button type="button" class="btn btn-primary btn-md" style="" data-bs-toggle="modal" data-bs-target="#verifyModal">
                                                                    <i class="icon-check"></i> Sahkan
                                                                </button>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                 </tbody>
                                             </table>
                                         </div>
                                     </div>
                                 </div>
                             </div>
                         </div>
                     </div>
                 </div>
                 </div>

                <!-- Verify Modal -->
                <div class="modal fade" id="verifyModal" tabindex="-1" aria-labelledby="verifyModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST" action="">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="verifyModalLabel">Sahkan Rekod Harian Tapak</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Adakah anda pasti mahu mengesahkan rekod ini?</p>
                                    <div class="form-group">
                                        <label for="ver_comment">Catatan (Pilihan)</label>
                                        <textarea class="form-control" id="ver_comment" name="ver_comment" rows="3" placeholder="Masukkan sebarang catatan di sini..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" name="verify_aso" class="btn btn-primary">Sahkan</button>
                                </div>
                            </form>
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
        const days = [
            { english: 'Monday', malay: 'Isnin' },
            { english: 'Tuesday', malay: 'Selasa' },
            { english: 'Wednesday', malay: 'Rabu' },
            { english: 'Thursday', malay: 'Khamis' },
            { english: 'Friday', malay: 'Jumaat' },
            { english: 'Saturday', malay: 'Sabtu' }
        ];
        const tbody = document.getElementById('tbody');

        // Ambil data dari PHP dengan fallback jika data null/undefined
        const recordDay = "<?php echo isset($row3['date']) ? date('l', strtotime($row3['date'])) : ''; ?>";
        const gcChecked = <?php echo isset($row3['gc_checked']) ? (int)$row3['gc_checked'] : 0; ?>;
        const gcSession = <?php echo isset($row3['gc_session']) ? json_encode($row3['gc_session']) : '""'; ?>;
        const gcDayNo   = <?php echo isset($row3['gc_day_no']) ? json_encode($row3['gc_day_no']) : '""'; ?>;

        const dmChecked = <?php echo isset($row3['dm_checked']) ? (int)$row3['dm_checked'] : 0; ?>;
        const dmSession = <?php echo isset($row3['dm_session']) ? json_encode($row3['dm_session']) : '""'; ?>;
        const dmDayNo   = <?php echo isset($row3['dm_day_no']) ? json_encode($row3['dm_day_no']) : '""'; ?>;

        days.forEach(day => {
            const tr = document.createElement('tr');

            // Semak jika hari dalam loop sepadan dengan hari rekod
            const isMatch = (day.english.toLowerCase() === recordDay.toLowerCase());

            // Set data jika hari sepadan
            const gcCheckedAttr = (isMatch && gcChecked === 1) ? 'checked' : '';
            const gcSessionVal  = isMatch ? gcSession : '';
            const gcDayNoVal    = isMatch ? gcDayNo : '';

            const dmCheckedAttr = (isMatch && dmChecked === 1) ? 'checked' : '';
            const dmSessionVal  = isMatch ? dmSession : '';
            const dmDayNoVal    = isMatch ? dmDayNo : '';

            tr.innerHTML = `
                <td class="day-cell">${day.malay}</td>
                <td class="checkbox-cell">
                    <div class="custom-checkbox-wrapper">
                        <input type="checkbox" class="custom-checkbox" ${gcCheckedAttr} disabled>
                    </div>
                </td>
                <td>
                    <div class="modern-input-group">
                        <input type="text" class="modern-input" value="${gcSessionVal}" readonly>
                    </div>
                </td>
                <td>
                    <div class="modern-input-group">
                        <input type="text" class="modern-input" value="${gcDayNoVal}" readonly>
                    </div>
                </td>
                <td class="checkbox-cell">
                    <div class="custom-checkbox-wrapper">
                        <input type="checkbox" class="custom-checkbox blue-checkbox" ${dmCheckedAttr} disabled>
                    </div>
                </td>
                <td>
                    <div class="modern-input-group">
                        <input type="text" class="modern-input" value="${dmSessionVal}" readonly>
                    </div>
                </td>
                <td>
                    <div class="modern-input-group">
                        <input type="text" class="modern-input" value="${dmDayNoVal}" readonly>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
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
            $url = '';
            if ($sz_id > 0) {
                $stmt_kmz = $conn->prepare("SELECT url FROM kmz_file WHERE sz_id = ? ORDER BY id DESC LIMIT 1");
                if ($stmt_kmz) {
                    $stmt_kmz->bind_param("i", $sz_id);
                    $stmt_kmz->execute();
                    $res_kmz = $stmt_kmz->get_result();
                    if ($res_kmz && $row_kmz = $res_kmz->fetch_assoc()) {
                        $url = $row_kmz['url'];
                    }
                    $stmt_kmz->close();
                }
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

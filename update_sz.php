<?php
session_start();
require 'includes/connection.php';

$subzone_id = (int)($_GET['szid'] ?? 0);

if ($subzone_id <= 0) {
    header("Location: zone.php");
    exit();
}

// 1. Fetch subzone & zone information safely via prepared query
$stmt = $conn->prepare("
    SELECT sz.*, z.zone_name 
    FROM subzone sz 
    LEFT JOIN zone z ON sz.zone_id = z.zone_id 
    WHERE sz.sz_id = ? 
    LIMIT 1
");
$stmt->bind_param("i", $subzone_id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

if (!$row) {
    $_SESSION['status'] = 'error';
    $_SESSION['message'] = 'Sub-zon tidak dijumpai.';
    header("Location: zone.php");
    exit();
}

$zone_id = (int)$row['zone_id'];

// 2. Fetch all zones for dropdown
$zones_list = [];
$res_zones = $conn->query("SELECT zone_id, zone_name FROM zone ORDER BY zone_name ASC");
if ($res_zones) {
    while ($z = $res_zones->fetch_assoc()) {
        $zones_list[] = $z;
    }
}

// 3. Fetch active KMZ record safely
$stmt_kmz = $conn->prepare("SELECT * FROM kmz_file WHERE sz_id = ? AND kmz_status = 'Y' ORDER BY kmz_id DESC LIMIT 1");
$stmt_kmz->bind_param("i", $subzone_id);
$stmt_kmz->execute();
$kmz_active = $stmt_kmz->get_result()->fetch_assoc();
$stmt_kmz->close();

$has_kmz = !empty($kmz_active);
$kmz_url = $has_kmz ? ($kmz_active['url'] ?? '') : '';

// 4. Fetch KMZ history for modal
$stmt_hist = $conn->prepare("SELECT * FROM kmz_file WHERE sz_id = ? ORDER BY add_date DESC");
$stmt_hist->bind_param("i", $subzone_id);
$stmt_hist->execute();
$history_res = $stmt_hist->get_result();
?>
<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Kemaskini Sub-Zon — Green Management System (DBKU)</title>
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
    <style>
        .map-preview-container {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--gms-border);
            position: relative;
        }

        .kmz-alert-banner {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.08) 0%, rgba(245, 158, 11, 0.04) 100%);
            border: 1px solid rgba(245, 158, 11, 0.3);
            border-radius: 12px;
            padding: 14px 18px;
            color: #b45309;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .action-button-group {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            padding-top: 20px;
            border-top: 1px solid var(--gms-border);
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
                    <!-- PAGE HEADER BANNER                                              -->
                    <!-- ============================================================== -->
                    <div class="gms-hero-banner gms-fade-in" style="padding: 1.5rem 2rem;">
                        <div class="d-flex align-items-center justify-content-between flex-wrap g-3">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="gms-pulse-dot" style="background:#34d399;"></span>
                                    <span class="gms-hero-subtitle" style="color: rgba(255,255,255,0.75);">Modul Geospatial & Sub-Zon</span>
                                </div>
                                <h1 class="gms-hero-title mb-1">Kemaskini Butiran Sub-Zon</h1>
                                <p class="gms-hero-subtitle">Sub-Zon: <strong style="color:#34d399;"><?php echo htmlspecialchars($row['sz_name'] ?? ''); ?></strong> &bull; Zon: <?php echo htmlspecialchars($row['zone_name'] ?? '-'); ?></p>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-outline-light btn-sm rounded-pill px-3 py-2 font-weight-bold" data-bs-toggle="modal" data-bs-target="#historyModal">
                                    <i class="fa-solid fa-clock-rotate-left me-1"></i> Sejarah KMZ
                                </button>
                                <a href="subzone_details.php?zid=<?php echo $zone_id; ?>&szid=<?php echo $subzone_id; ?>" class="btn btn-light btn-sm rounded-pill px-3 py-2 font-weight-bold" style="color: #007442;">
                                    <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Butiran
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================================== -->
                    <!-- MAIN FORM CARD                                                 -->
                    <!-- ============================================================== -->
                    <div class="card shadow gms-table-card mb-4 gms-fade-in gms-fade-in-delay-1">
                        <div class="card-body p-4">
                            <form class="forms-sample" action="includes/update_subzone.php" method="POST">
                                <input type="hidden" name="subzone_id" value="<?php echo $subzone_id; ?>">

                                <div class="mb-4">
                                    <h4 class="gms-section-title">
                                        <i class="fa-solid fa-circle-info me-2 text-success"></i>Maklumat Am Sub-Zon
                                    </h4>
                                    <p class="gms-section-subtitle">Kemaskini nama, zon induk, perimeter dan keluasan sub-zon.</p>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <div class="form-group mb-3">
                                            <label for="subzone_name" class="font-weight-bold text-dark mb-2">Nama Sub-Zon</label>
                                            <input type="text" class="form-control" name="subzone" id="subzone_name" value="<?php echo htmlspecialchars($row['sz_name'] ?? ''); ?>" required style="border-radius:10px; height:46px;">
                                        </div>
                                    </div>

                                    <!-- Zone selection dropdown -->
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label for="zone_id" class="font-weight-bold text-dark mb-2">Zon Pentadbiran</label>
                                            <select name="zone_id" id="zone_id" class="form-control" style="border-radius:10px; height:46px;">
                                                <?php foreach ($zones_list as $z_opt) { 
                                                    $selected = ($z_opt['zone_id'] == $row['zone_id']) ? 'selected' : '';
                                                ?>
                                                    <option value="<?php echo (int)$z_opt['zone_id']; ?>" <?php echo $selected; ?>>
                                                        <?php echo htmlspecialchars($z_opt['zone_name']); ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Perimeter -->
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label for="perimeter" class="font-weight-bold text-dark mb-2">Perimeter (m)</label>
                                            <input type="number" step="any" class="form-control" name="perimeter" id="perimeter" value="<?php echo htmlspecialchars($row['perimeter'] ?? ''); ?>" style="border-radius:10px; height:46px;">
                                        </div>
                                    </div>

                                    <!-- Area -->
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label for="area" class="font-weight-bold text-dark mb-2">Keluasan (ha)</label>
                                            <input type="number" step="any" class="form-control" name="area" id="area" value="<?php echo htmlspecialchars($row['area'] ?? ''); ?>" style="border-radius:10px; height:46px;">
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 mb-3">
                                    <h4 class="gms-section-title">
                                        <i class="fa-solid fa-map-location-dot me-2 text-success"></i>Pratonton Peta Sempadan Geospatial
                                    </h4>
                                    <p class="gms-section-subtitle">Visualisasi sempadan KMZ sub-zon pada peta Google Hybrid.</p>
                                </div>

                                <div class="map-preview-container mb-4">
                                    <div id="map" style="width: 100%; height: 420px;"></div>
                                </div>

                                <!-- KMZ file status alert -->
                                <?php if (!$has_kmz) { ?>
                                    <div class="kmz-alert-banner mb-4">
                                        <i class="fa-solid fa-triangle-exclamation text-warning" style="font-size:1.2rem;"></i>
                                        <span>Tiada fail sempadan KMZ aktif untuk sub-zon ini. Sila muat naik fail sempadan di bawah untuk pengesanan visual.</span>
                                    </div>
                                <?php } else { ?>
                                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded-3 mb-4 border">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="badge bg-success rounded-pill px-3 py-2">
                                                <i class="fa-solid fa-circle-check me-1"></i> KMZ Aktif
                                            </div>
                                            <span class="text-dark fw-semibold small"><?php echo htmlspecialchars($kmz_active['kmz_file'] ?? ''); ?></span>
                                            <small class="text-muted">(Dimuat naik: <?php echo htmlspecialchars($kmz_active['add_date'] ?? '-'); ?>)</small>
                                        </div>
                                        <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 font-weight-bold" data-bs-toggle="modal" data-bs-target="#exampleModal">
                                            <i class="fa-solid fa-cloud-arrow-up me-1"></i> Ganti Fail KMZ
                                        </button>
                                    </div>
                                <?php } ?>

                                <!-- Action Button Group -->
                                <div class="action-button-group">
                                    <a href="subzone_details.php?zid=<?php echo $zone_id; ?>&szid=<?php echo $subzone_id; ?>" class="btn btn-outline-secondary rounded-pill px-4 py-2 font-weight-semibold">
                                        <i class="fa-solid fa-xmark me-1"></i> Batal
                                    </a>
                                    <button type="submit" class="btn btn-success rounded-pill px-4 py-2 font-weight-semibold">
                                        <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan
                                    </button>
                                </div>
                            </form>

                            <!-- In case no KMZ file, provide upload form directly on page -->
                            <?php if (!$has_kmz) { ?>
                                <form action="includes/upload_kmz.php" class="pt-4 border-top mt-4" method="POST" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <h4 class="gms-section-title">
                                            <i class="fa-solid fa-cloud-arrow-up me-2 text-success"></i>Muat Naik Fail Sempadan KMZ
                                        </h4>
                                        <p class="gms-section-subtitle">Pilih fail KMZ/KML sempadan rasmi dari pelayar komputer anda.</p>
                                    </div>
                                    <input type="hidden" name="zone_id" value="<?php echo $zone_id; ?>">
                                    <input type="hidden" name="subzone_id" value="<?php echo $subzone_id; ?>">
                                    
                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <div class="form-group mb-3">
                                                <label for="new_kmz_file" class="font-weight-bold text-dark mb-2">Pilih Fail (.kmz / .kml)</label>
                                                <input type="file" class="form-control" name="files[]" id="new_kmz_file" accept=".kmz,.kml" required style="border-radius:10px; padding:10px;">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 font-weight-semibold">
                                            <i class="fa-solid fa-upload me-1"></i> Muat Naik Fail KMZ
                                        </button>
                                    </div>
                                </form>
                            <?php } ?>
                        </div>
                    </div>

                    <!-- ============================================================== -->
                    <!-- MODAL: UPDATE KMZ                                              -->
                    <!-- ============================================================== -->
                    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content" style="border-radius: 16px; border:none; box-shadow: 0 10px 40px rgba(0,0,0,0.15);">
                                <div class="modal-header border-0 pb-0 pt-4 px-4">
                                    <h5 class="modal-title font-weight-bold" id="exampleModalLabel">
                                        <i class="fa-solid fa-cloud-arrow-up me-2 text-success"></i>Kemaskini Fail Sempadan KMZ
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                </div>
                                <form action="includes/update_kmz.php" method="POST" enctype="multipart/form-data">
                                    <div class="modal-body p-4">
                                        <input type="hidden" name="zone_id" value="<?php echo $zone_id; ?>">
                                        <input type="hidden" name="subzone_id" value="<?php echo $subzone_id; ?>">
                                        
                                        <p class="text-secondary small mb-3">Memuat naik fail KMZ baharu akan menyahaktifkan fail sempadan semasa secara automatik.</p>

                                        <div class="form-group mb-3">
                                            <label for="update_kmz_file" class="form-label font-weight-bold text-dark">Pilih Fail KMZ Baharu</label>
                                            <input type="file" class="form-control" name="files[]" id="update_kmz_file" accept=".kmz,.kml" required style="border-radius:10px; padding:10px;">
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0 pt-0 pb-4 px-4 gap-2">
                                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                                        <button type="submit" class="btn btn-success rounded-pill px-4 font-weight-bold">
                                            <i class="fa-solid fa-check me-1"></i> Muat Naik & Kemaskini
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================================== -->
                    <!-- MODAL: HISTORY KMZ                                             -->
                    <!-- ============================================================== -->
                    <div class="modal fade" id="historyModal" tabindex="-1" role="dialog" aria-labelledby="historyModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                            <div class="modal-content" style="border-radius: 16px; border:none; box-shadow: 0 10px 40px rgba(0,0,0,0.15);">
                                <div class="modal-header border-0 pb-0 pt-4 px-4">
                                    <h5 class="modal-title font-weight-bold" id="historyModalLabel">
                                        <i class="fa-solid fa-clock-rotate-left me-2 text-success"></i>Sejarah Muat Naik KMZ
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Nama Fail</th>
                                                    <th style="width: 170px;">Tarikh Muat Naik</th>
                                                    <th style="width: 110px; text-align: center;">Status</th>
                                                    <th style="width: 130px; text-align: center;">Tindakan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if ($history_res && $history_res->num_rows > 0) {
                                                    while ($h_row = $history_res->fetch_assoc()) {
                                                        $is_active = ($h_row['kmz_status'] == 'Y');
                                                        $status_badge = $is_active 
                                                            ? '<span class="badge bg-success rounded-pill px-3 py-1 text-white">Aktif</span>'
                                                            : '<span class="badge bg-secondary rounded-pill px-3 py-1 text-white">Arkib</span>';
                                                ?>
                                                        <tr>
                                                            <td class="text-wrap" style="max-width: 250px; font-weight: 500;">
                                                                <i class="fa-regular fa-file-lines text-secondary me-2"></i><?php echo htmlspecialchars($h_row['kmz_file'] ?? ''); ?>
                                                            </td>
                                                            <td>
                                                                <small class="text-muted"><?php echo htmlspecialchars($h_row['add_date'] ?? '-'); ?></small>
                                                            </td>
                                                            <td style="text-align: center;">
                                                                <?php echo $status_badge; ?>
                                                            </td>
                                                            <td style="text-align: center;">
                                                                <a href="<?php echo htmlspecialchars($h_row['url'] ?? '#'); ?>" class="btn btn-sm btn-outline-primary rounded-pill py-1 px-3" target="_blank" download>
                                                                    <i class="fa-solid fa-download me-1"></i> Unduh
                                                                </a>
                                                            </td>
                                                        </tr>
                                                <?php
                                                    }
                                                } else {
                                                ?>
                                                    <tr>
                                                        <td colspan="4" class="text-center text-muted py-4">
                                                            <i class="fa-solid fa-circle-info me-2"></i> Tiada rekod sejarah KMZ tersedia.
                                                        </td>
                                                    </tr>
                                                <?php } 
                                                $stmt_hist->close();
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
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
        function initMap() {
            var mapElement = document.getElementById('map');
            if (!mapElement) return;

            var map = new google.maps.Map(mapElement, {
                center: {
                    lat: 1.585367,
                    lng: 110.334509
                },
                zoom: 16,
                mapTypeId: google.maps.MapTypeId.HYBRID
            });

            var kmzUrl = <?php echo json_encode($kmz_url); ?>;
            if (kmzUrl && kmzUrl.trim() !== '') {
                var kmzLayer = new google.maps.KmlLayer({
                    url: kmzUrl,
                    map: map,
                    zoom: 15,
                    preserveViewport: false
                });
            }
        }
    </script>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAM9KnXl4SMmy68M2syalpDyMfZeNItPjk&callback=initMap"></script>

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
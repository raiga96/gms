<?php
session_start();
require 'includes/connection.php';

$zones = [];
$zone_result = $conn->query("SELECT zone_id, zone_name FROM zone ORDER BY zone_name ASC");
if ($zone_result) {
    while ($zone = $zone_result->fetch_assoc()) {
        $zones[] = $zone;
    }
}

$subzones = [];
$subzone_result = $conn->query("SELECT sz_id, zone_id, sz_name FROM subzone ORDER BY sz_name ASC");
if ($subzone_result) {
    while ($subzone = $subzone_result->fetch_assoc()) {
        $subzones[] = $subzone;
    }
}

$contractors = [];
$contractor_result = $conn->query("SELECT cid, name FROM contractor ORDER BY name ASC");
if ($contractor_result) {
    while ($contractor = $contractor_result->fetch_assoc()) {
        $contractors[] = $contractor;
    }
}

$flash_status = $_SESSION['status'] ?? '';
$flash_message = $_SESSION['message'] ?? '';
unset($_SESSION['status'], $_SESSION['message']);
?>
<!DOCTYPE html>
<html lang="ms">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Kontrak Baharu — Green Management System (DBKU)</title>
    <link rel="shortcut icon" href="assets/images/favicon.png" />
    <link rel="stylesheet" href="assets/vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="assets/vendors/flag-icon-css/css/flag-icons.min.css">
    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="assets/vendors/font-awesome/css/font-awesome.min.css" />
    <link rel="stylesheet" href="assets/css/vertical-light-layout/style.css">
    <link rel="stylesheet" href="assets/css/modern-dashboard.css">
    <style>
        .gms-form-card .card-body { padding: 2rem !important; }

        .gms-form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--gms-text-secondary);
            font-size: 0.8125rem;
            font-weight: 600;
            letter-spacing: 0.02em;
        }

        .gms-form-label .text-danger { color: #ef4444; }

        .gms-input-group {
            display: flex;
            align-items: stretch;
            position: relative;
        }

        .gms-input-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 46px;
            flex-shrink: 0;
            color: var(--gms-primary);
            background: rgba(0, 116, 66, 0.06);
            border: 1px solid var(--gms-border);
            border-right: none;
            border-radius: 12px 0 0 12px;
            font-size: 1rem;
            transition: var(--gms-transition);
        }

        .gms-input-group:focus-within .gms-input-icon {
            color: #059669;
            background: rgba(0, 116, 66, 0.12);
            border-color: var(--gms-border-focus);
        }

        .gms-form-control {
            flex: 1 1 auto;
            min-width: 0;
            padding: 12px 16px;
            color: var(--gms-text-primary);
            background: #f8fafc;
            border: 1px solid var(--gms-border);
            border-radius: 0 12px 12px 0;
            outline: none;
            font-family: inherit;
            font-size: 0.9rem;
            transition: var(--gms-transition);
        }

        .gms-form-control::placeholder { color: var(--gms-text-muted); }

        .gms-form-control:focus {
            background: #ffffff;
            border-color: var(--gms-border-focus);
            box-shadow: 0 0 0 3px rgba(0, 116, 66, 0.12);
        }

        textarea.gms-form-control { border-radius: 0 12px 12px 12px; resize: vertical; }

        select.gms-form-control {
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 14px center;
            background-size: 18px;
            padding-right: 40px;
        }

        select.gms-form-control:disabled {
            cursor: not-allowed;
            opacity: 0.65;
        }

        .gms-btn-primary,
        .gms-btn-light {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 28px;
            border-radius: var(--gms-pill-radius);
            font-size: 0.875rem;
            font-weight: 600;
            transition: var(--gms-transition);
        }

        .gms-btn-primary {
            color: #ffffff;
            background: linear-gradient(135deg, #10b981 0%, #007442 100%);
            border: none;
            box-shadow: 0 6px 18px rgba(0, 116, 66, 0.3);
        }

        .gms-btn-primary:hover {
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(0, 116, 66, 0.4);
        }

        .gms-btn-light {
            color: var(--gms-text-secondary);
            background: #f1f5f9;
            border: 1px solid var(--gms-border);
            cursor: pointer;
        }

        .gms-btn-light:hover {
            color: var(--gms-text-primary);
            background: #e2e8f0;
            transform: translateY(-2px);
        }

        .gms-context-card {
            background: rgba(0, 116, 66, 0.035);
            border: 1px solid rgba(0, 116, 66, 0.12);
            border-radius: 16px;
        }

        @media (max-width: 767.98px) {
            .gms-form-card .card-body { padding: 1.25rem !important; }
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
                    <div class="gms-hero-banner gms-fade-in" style="padding: 1.5rem 2rem;">
                        <div class="d-flex align-items-center justify-content-between flex-wrap g-3">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="gms-pulse-dot" style="background:#34d399;"></span>
                                    <span class="gms-hero-subtitle" style="color: rgba(255,255,255,0.75);">Modul Pengurusan Kontrak</span>
                                </div>
                                <h1 class="gms-hero-title mb-1">Daftar Kontrak Baharu</h1>
                                <p class="gms-hero-subtitle">Lengkapkan maklumat kontrak dan tetapkan kontraktor untuk operasi DBKU.</p>
                            </div>
                            <a href="contract_list.php" class="gms-btn-light" style="background: rgba(255,255,255,0.12); color: #ffffff; border-color: rgba(255,255,255,0.25);">
                                <i class="fa-solid fa-arrow-left"></i> Senarai Kontrak
                            </a>
                        </div>
                    </div>

                    <?php if ($flash_message !== ''): ?>
                        <div class="alert <?php echo $flash_status === 'success' ? 'alert-success' : 'alert-danger'; ?> shadow-sm border-0 rounded-3 mt-4 mb-0" role="alert">
                            <i class="fa-solid <?php echo $flash_status === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'; ?> me-2"></i>
                            <?php echo htmlspecialchars($flash_message); ?>
                        </div>
                    <?php endif; ?>

                    <form action="includes/create_contract.php" method="POST" id="newContractForm">
                        <div class="row g-4 mt-1 gms-fade-in gms-fade-in-delay-1">
                            <div class="col-12">
                                <div class="card shadow gms-form-card gms-context-card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                                            <div class="gms-kpi-icon me-3" style="background: rgba(0, 116, 66, 0.08); color: var(--gms-primary);">
                                                <i class="fa-solid fa-sitemap"></i>
                                            </div>
                                            <div>
                                                <h4 class="gms-section-title">Skop Kontrak</h4>
                                                <p class="gms-section-subtitle mb-0">Pilih zon, sub-zon dan kontraktor yang akan menerima lantikan.</p>
                                            </div>
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-lg-4">
                                                <label for="zone_id" class="gms-form-label">Zon <span class="text-danger">*</span></label>
                                                <div class="gms-input-group">
                                                    <span class="gms-input-icon"><i class="fa-solid fa-location-dot"></i></span>
                                                    <select name="zid" id="zone_id" class="gms-form-control" required>
                                                        <option value="">-- Pilih Zon --</option>
                                                        <?php foreach ($zones as $zone): ?>
                                                            <option value="<?php echo (int)$zone['zone_id']; ?>"><?php echo htmlspecialchars($zone['zone_name']); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-lg-4">
                                                <label for="subzone_id" class="gms-form-label">Sub-Zon <span class="text-danger">*</span></label>
                                                <div class="gms-input-group">
                                                    <span class="gms-input-icon"><i class="fa-solid fa-map"></i></span>
                                                    <select name="sz_id" id="subzone_id" class="gms-form-control" required disabled>
                                                        <option value="">-- Pilih Zon Dahulu --</option>
                                                        <?php foreach ($subzones as $subzone): ?>
                                                            <option value="<?php echo (int)$subzone['sz_id']; ?>" data-zone-id="<?php echo (int)$subzone['zone_id']; ?>"><?php echo htmlspecialchars($subzone['sz_name']); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-lg-4">
                                                <label for="contractor_id" class="gms-form-label">Kontraktor <span class="text-danger">*</span></label>
                                                <div class="gms-input-group">
                                                    <span class="gms-input-icon"><i class="fa-solid fa-building"></i></span>
                                                    <select name="cid" id="contractor_id" class="gms-form-control" required>
                                                        <option value="">-- Pilih Kontraktor --</option>
                                                        <?php foreach ($contractors as $contractor): ?>
                                                            <option value="<?php echo (int)$contractor['cid']; ?>"><?php echo htmlspecialchars($contractor['name']); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mt-1 gms-fade-in gms-fade-in-delay-2">
                            <div class="col-12">
                                <div class="card shadow gms-form-card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                                            <div class="gms-kpi-icon me-3" style="background: rgba(0, 116, 66, 0.08); color: var(--gms-primary);">
                                                <i class="fa-solid fa-file-contract"></i>
                                            </div>
                                            <div>
                                                <h4 class="gms-section-title">Parameter Lantikan</h4>
                                                <p class="gms-section-subtitle mb-0">Lengkapkan nombor kontrak, tarikh, penyelia &amp; peruntukan.</p>
                                            </div>
                                        </div>

                                        <div class="row g-4">
                                            <div class="col-lg-4">
                                                <label for="contract_number" class="gms-form-label">Nombor Kontrak <span class="text-danger">*</span></label>
                                                <div class="gms-input-group mb-3">
                                                    <span class="gms-input-icon"><i class="fa-solid fa-hashtag"></i></span>
                                                    <input type="text" name="contract_number" id="contract_number" class="gms-form-control" placeholder="cth. DBKU/CT/2025/001" required maxlength="100">
                                                </div>
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label for="start_date" class="gms-form-label">Tarikh Mula <span class="text-danger">*</span></label>
                                                        <div class="gms-input-group">
                                                            <span class="gms-input-icon"><i class="fa-solid fa-calendar-day"></i></span>
                                                            <input type="date" name="start_date" id="start_date" class="gms-form-control" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="end_date" class="gms-form-label">Tarikh Tamat <span class="text-danger">*</span></label>
                                                        <div class="gms-input-group">
                                                            <span class="gms-input-icon"><i class="fa-solid fa-calendar-check"></i></span>
                                                            <input type="date" name="end_date" id="end_date" class="gms-form-control" required>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-lg-4">
                                                <div class="mb-3">
                                                    <label for="supervisor1_name" class="gms-form-label">Penyelia 1</label>
                                                    <div class="row g-2">
                                                        <div class="col-md-6">
                                                            <div class="gms-input-group">
                                                                <span class="gms-input-icon"><i class="fa-solid fa-user"></i></span>
                                                                <input type="text" name="supervisor1_name" id="supervisor1_name" class="gms-form-control" placeholder="Nama" maxlength="100">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="gms-input-group">
                                                                <span class="gms-input-icon"><i class="fa-solid fa-phone"></i></span>
                                                                <input type="text" name="supervisor1_contact" id="supervisor1_contact" class="gms-form-control" placeholder="Nombor Hubungan" maxlength="50">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div>
                                                    <label for="supervisor2_name" class="gms-form-label">Penyelia 2</label>
                                                    <div class="row g-2">
                                                        <div class="col-md-6">
                                                            <div class="gms-input-group">
                                                                <span class="gms-input-icon"><i class="fa-solid fa-user"></i></span>
                                                                <input type="text" name="supervisor2_name" id="supervisor2_name" class="gms-form-control" placeholder="Nama" maxlength="100">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="gms-input-group">
                                                                <span class="gms-input-icon"><i class="fa-solid fa-phone"></i></span>
                                                                <input type="text" name="supervisor2_contact" id="supervisor2_contact" class="gms-form-control" placeholder="Nombor Hubungan" maxlength="50">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-lg-4">
                                                <div class="mb-3">
                                                    <label for="fund" class="gms-form-label">Peruntukan (Fund)</label>
                                                    <div class="gms-input-group">
                                                        <span class="gms-input-icon"><i class="fa-solid fa-money-bill-trend-up"></i></span>
                                                        <input type="text" name="fund" id="fund" class="gms-form-control" placeholder="cth. RM 50,000" maxlength="50">
                                                    </div>
                                                </div>
                                                <div>
                                                    <label for="remarks" class="gms-form-label">Catatan</label>
                                                    <div class="gms-input-group">
                                                        <span class="gms-input-icon" style="align-items: flex-start; padding-top: 12px;"><i class="fa-solid fa-comment-dots"></i></span>
                                                        <textarea name="remarks" id="remarks" rows="3" class="gms-form-control" placeholder="Masukkan catatan tambahan..." maxlength="1000"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <hr class="my-4 border-light">
                                        <div class="d-flex justify-content-center gap-3 flex-wrap">
                                            <a href="contract_list.php" class="gms-btn-light"><i class="fa-solid fa-xmark"></i> Batal</a>
                                            <button type="submit" class="gms-btn-primary"><i class="fa-solid fa-check"></i> Daftar Kontrak</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <?php include 'partials/footer.php'; ?>
            </div>
        </div>
    </div>

    <script src="assets/vendors/js/vendor.bundle.base.js"></script>
    <script src="assets/js/off-canvas.js"></script>
    <script src="assets/js/hoverable-collapse.js"></script>
    <script src="assets/js/misc.js"></script>
    <script>
        (function () {
            var zoneSelect = document.getElementById('zone_id');
            var subzoneSelect = document.getElementById('subzone_id');
            var form = document.getElementById('newContractForm');
            var startDate = document.getElementById('start_date');
            var endDate = document.getElementById('end_date');

            zoneSelect.addEventListener('change', function () {
                var selectedZone = this.value;
                var hasOptions = false;

                Array.prototype.forEach.call(subzoneSelect.options, function (option, index) {
                    if (index === 0) {
                        option.hidden = false;
                        return;
                    }

                    var visible = selectedZone !== '' && option.getAttribute('data-zone-id') === selectedZone;
                    option.hidden = !visible;
                    option.disabled = !visible;
                    if (visible) hasOptions = true;
                });

                subzoneSelect.value = '';
                subzoneSelect.disabled = !selectedZone || !hasOptions;
                subzoneSelect.options[0].textContent = hasOptions ? '-- Pilih Sub-Zon --' : '-- Tiada Sub-Zon --';
            });

            form.addEventListener('submit', function (event) {
                if (startDate.value && endDate.value && startDate.value > endDate.value) {
                    event.preventDefault();
                    endDate.setCustomValidity('Tarikh tamat mesti selepas tarikh mula.');
                    endDate.reportValidity();
                } else {
                    endDate.setCustomValidity('');
                }
            });

            endDate.addEventListener('change', function () {
                if (!startDate.value || !endDate.value || startDate.value <= endDate.value) {
                    endDate.setCustomValidity('');
                }
            });
        }());
    </script>
</body>

</html>

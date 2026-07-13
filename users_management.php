<?php
session_start();
require 'includes/connection.php';

// Stats
$total_users = $conn->query("SELECT COUNT(*) AS cnt FROM users")->fetch_assoc()['cnt'];
$total_spv = $conn->query("SELECT COUNT(*) AS cnt FROM users WHERE user_type='SPV'")->fetch_assoc()['cnt'];
$total_pgs = $conn->query("SELECT COUNT(*) AS cnt FROM users WHERE user_type='PGS'")->fetch_assoc()['cnt'];
$total_con = $conn->query("SELECT COUNT(*) AS cnt FROM users WHERE user_type='CON'")->fetch_assoc()['cnt'];

$user_roles = [
    'SPV' => 'Supervisor',
    'PGS' => 'PENGAWAS',
    'ASO' => 'Assistant Superintending Officer',
    'SO'  => 'Superintending Officer',
    'PGRH'=> 'Pengarah',
    'ADM' => 'Administrator',
    'ROOT'=> 'Super Admin',
    'CON' => 'Contractor'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>SmartCut - User Management</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="assets/vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="assets/vendors/flag-icon-css/css/flag-icons.min.css">
    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" integrity="sha512-5Hs3dF2AEPkpNAR7UiOHba+lRSJNeM2ECkwxUIxC1Q/FLycGTbNapWXB4tP889k5T5Ju8fs4b1P5z/iB4nMfSQ==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <!-- Layout styles -->
    <link rel="stylesheet" href="assets/css/vertical-light-layout/style.css">
    <link rel="shortcut icon" href="assets/images/favicon.png" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');

        .modern-zone-container {
            font-family: 'Outfit', sans-serif;
            color: #334155;
        }

        /* Action Banner / Header */
        .modern-banner {
            background: linear-gradient(90deg, #1d384d 0%, #00311c 100%);
            border-radius: 16px;
            padding: 20px 24px;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
            border: none;
        }
        .modern-banner-title {
            font-size: 18px;
            font-weight: 600;
            color: #ffffff;
            margin: 0;
        }
        .modern-btn-white {
            background-color: #ffffff;
            color: #1e3a5f !important;
            font-weight: 600;
            font-size: 13px;
            padding: 8px 16px;
            border-radius: 30px;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .modern-btn-white:hover {
            background-color: #f1f5f9;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-decoration: none;
        }
        .modern-zone-container .card {
            border: none;
            border-radius: 16px !important;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05), 0 5px 15px -8px rgba(0, 0, 0, 0.05) !important;
            background: #ffffff;
            overflow: hidden;
        }
        .modern-table-wrapper {
            width: 100%;
            overflow-x: auto;
        }
        .modern-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 10px;
        }
        .modern-table th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            padding: 16px 20px;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e2e8f0;
        }
        .modern-table td {
            padding: 16px 20px;
            color: #334155;
            font-size: 14px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .modern-table tbody tr:hover {
            background-color: #f8fafc;
        }
        .badge-soft-success {
            background-color: #d1fae5;
            color: #065f46;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-soft-danger {
            background-color: #fee2e2;
            color: #b91c1c;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        /* Modern Stat Cards */
        .modern-stat-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(226, 232, 240, 0.8);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            height: 100%;
        }

        .modern-stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 30px -5px rgba(0, 0, 0, 0.08);
            border-color: #cbd5e1;
        }

        .modern-stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
        }
        
        .modern-stat-card.stat-success::before { background: #10b981; }
        .modern-stat-card.stat-danger::before { background: #ef4444; }
        .modern-stat-card.stat-info::before { background: #3b82f6; }
        .modern-stat-card.stat-warning::before { background: #f59e0b; }
        .modern-stat-card.stat-primary::before { background: #6366f1; }

        .modern-stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        
        .modern-stat-card.stat-success .modern-stat-icon { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        .modern-stat-card.stat-danger .modern-stat-icon { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
        .modern-stat-card.stat-info .modern-stat-icon { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
        .modern-stat-card.stat-warning .modern-stat-icon { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
        .modern-stat-card.stat-primary .modern-stat-icon { background: rgba(99, 102, 241, 0.1); color: #6366f1; }
        
        .modern-stat-content h2 { margin: 0; font-weight: 700; color: #1e293b; font-size: 24px; }
        .modern-stat-content .report-title { font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
    </style>
</head>
<body>
    <div class="container-scroller">
        <?php include 'partials/navbar.php' ?>
        <div class="container-fluid page-body-wrapper">
            <?php include 'partials/sidebar.php' ?>
            <div class="main-panel" style="height: 100%;">
                <div class="content-wrapper">
                    
                    <?php if (isset($_SESSION['status'])) { ?>
                        <div class="alert alert-<?php echo $_SESSION['status'] == 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                            <?php echo $_SESSION['message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['status']); unset($_SESSION['message']); ?>
                    <?php } ?>

                    <div class="row mb-4 g-3">
                        <div class="col-md-3">
                            <div class="modern-stat-card stat-primary">
                                <div class="modern-stat-content">
                                    <div class="report-title">Total Users</div>
                                    <h2><?php echo $total_users; ?></h2>
                                </div>
                                <div class="modern-stat-icon">
                                    <i class="icon-people"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="modern-stat-card stat-info">
                                <div class="modern-stat-content">
                                    <div class="report-title">Supervisors (SPV)</div>
                                    <h2><?php echo $total_spv; ?></h2>
                                </div>
                                <div class="modern-stat-icon">
                                    <i class="icon-eye"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="modern-stat-card stat-success">
                                <div class="modern-stat-content">
                                    <div class="report-title">Pegawai (PGS)</div>
                                    <h2><?php echo $total_pgs; ?></h2>
                                </div>
                                <div class="modern-stat-icon">
                                    <i class="icon-badge"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="modern-stat-card stat-warning">
                                <div class="modern-stat-content">
                                    <div class="report-title">Contractors (CON)</div>
                                    <h2><?php echo $total_con; ?></h2>
                                </div>
                                <div class="modern-stat-icon">
                                    <i class="icon-briefcase"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modern-zone-container">
                        <div class="card modern-banner bg-dark mt-2">
                            <div class="card-body p-0 d-sm-flex align-items-center justify-content-between">
                                <h4 class="modern-banner-title"><i class="icon-people mr-2"></i> User Management</h4>
                                <button type="button" class="modern-btn-white mt-3 mt-sm-0" data-bs-toggle="modal" data-bs-target="#addUserModal"><i class="icon-plus"></i> Add New User</button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 grid-margin stretch-card">
                                <div class="card shadow">
                                    <div class="card-body">
                                        <div class="d-sm-flex align-items-center justify-content-between mb-3">
                                            <h4 class="card-title">User List</h4>
                                        </div>

                                        <!-- Nav tabs -->
                                        <ul class="nav nav-tabs border-bottom-0 pb-0" id="userTabs" role="tablist">
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link active fw-bold text-primary" id="non-contractor-tab" data-bs-toggle="tab" data-bs-target="#non-contractor" type="button" role="tab" aria-controls="non-contractor" aria-selected="true"><i class="icon-user-following me-1"></i> Internal Staff (ADM/SPV/PGS)</button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link fw-bold text-secondary" id="contractor-tab" data-bs-toggle="tab" data-bs-target="#contractor" type="button" role="tab" aria-controls="contractor" aria-selected="false"><i class="icon-briefcase me-1"></i> Contractors (CON)</button>
                                            </li>
                                        </ul>

                                        <!-- Tab panes -->
                                        <div class="tab-content border border-top-0 pt-0" id="userTabsContent">
                                            <!-- Internal Staff Tab -->
                                            <div class="tab-pane fade show active" id="non-contractor" role="tabpanel" aria-labelledby="non-contractor-tab">
                                                <div class="modern-table-wrapper">
                                                    <table class="modern-table mt-0">
                                                        <thead>
                                                            <tr>
                                                                <th>Full Name</th>
                                                                <th>Username</th>
                                                                <th>Email</th>
                                                                <th>Type</th>
                                                                <th>Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            $sql_int = "SELECT u.uid, u.user_id, u.user_email, u.user_type, u.status, i.fullname, i.phone 
                                                                    FROM users u 
                                                                    LEFT JOIN userinfo i ON u.uid = i.uid 
                                                                    WHERE u.user_type != 'CON' OR u.user_type IS NULL
                                                                    ORDER BY u.created DESC";
                                                            $res_int = $conn->query($sql_int);
                                                            if ($res_int && $res_int->num_rows > 0) {
                                                                while ($row = $res_int->fetch_assoc()) {
                                                                    $status_badge = $row['status'] == 'Y' ? '<span class="badge-soft-success">Active</span>' : '<span class="badge-soft-danger">Inactive</span>';
                                                                    echo "<tr>";
                                                                    echo "<td><div class='fw-bold text-dark'>" . htmlspecialchars($row['fullname'] ?? '-') . "</div></td>";
                                                                    echo "<td>" . htmlspecialchars($row['user_id']) . "</td>";
                                                                    echo "<td>" . htmlspecialchars($row['user_email']) . "</td>";
                                                                    $role_name = $user_roles[$row['user_type']] ?? $row['user_type'];
                                                                    echo "<td><span class='badge bg-secondary'>" . htmlspecialchars($role_name) . "</span></td>";
                                                                    echo "<td>" . $status_badge . "</td>";
                                                                    echo "</tr>";
                                                                }
                                                            } else {
                                                                echo "<tr><td colspan='5' class='text-center py-4 text-muted'>No internal users found.</td></tr>";
                                                            }
                                                            ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                            <!-- Contractor Tab -->
                                            <div class="tab-pane fade" id="contractor" role="tabpanel" aria-labelledby="contractor-tab">
                                                <div class="modern-table-wrapper">
                                                    <table class="modern-table mt-0">
                                                        <thead>
                                                            <tr>
                                                                <th>Full Name</th>
                                                                <th>Username</th>
                                                                <th>Email</th>
                                                                <th>Type</th>
                                                                <th>Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            $sql_con = "SELECT u.uid, u.user_id, u.user_email, u.user_type, u.status, i.fullname, i.phone 
                                                                    FROM users u 
                                                                    LEFT JOIN userinfo i ON u.uid = i.uid 
                                                                    WHERE u.user_type = 'CON'
                                                                    ORDER BY u.created DESC";
                                                            $res_con = $conn->query($sql_con);
                                                            if ($res_con && $res_con->num_rows > 0) {
                                                                while ($row = $res_con->fetch_assoc()) {
                                                                    $status_badge = $row['status'] == 'Y' ? '<span class="badge-soft-success">Active</span>' : '<span class="badge-soft-danger">Inactive</span>';
                                                                    echo "<tr>";
                                                                    echo "<td><div class='fw-bold text-dark'>" . htmlspecialchars($row['fullname'] ?? '-') . "</div></td>";
                                                                    echo "<td>" . htmlspecialchars($row['user_id']) . "</td>";
                                                                    echo "<td>" . htmlspecialchars($row['user_email']) . "</td>";
                                                                    $role_name = $user_roles[$row['user_type']] ?? $row['user_type'];
                                                                    echo "<td><span class='badge bg-secondary'>" . htmlspecialchars($role_name) . "</span></td>";
                                                                    echo "<td>" . $status_badge . "</td>";
                                                                    echo "</tr>";
                                                                }
                                                            } else {
                                                                echo "<tr><td colspan='5' class='text-center py-4 text-muted'>No contractor users found.</td></tr>";
                                                            }
                                                            ?>
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

                </div>
                <footer class="footer">
                    <div class="d-sm-flex justify-content-center justify-content-sm-between">
                        <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">Copyright © 2026. All rights reserved.</span>
                    </div>
                </footer>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" id="addUserModalLabel"><i class="icon-user-follow text-primary me-2"></i> Register New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="includes/process_add_user.php" method="POST">
                    <div class="modal-body">
                        
                        <div class="card bg-light border-0 shadow-sm mb-4">
                            <div class="card-body">
                                <h6 class="mb-3 text-primary fw-bold"><i class="icon-lock me-1"></i> Account Credentials</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold text-muted">Username <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control border-0 shadow-sm" name="user_id" placeholder="Enter username" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold text-muted">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control border-0 shadow-sm" name="user_email" placeholder="example@domain.com" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold text-muted">Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control border-0 shadow-sm" name="user_password" placeholder="Create password" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold text-muted">User Role <span class="text-danger">*</span></label>
                                        <select class="form-select form-control border-0 shadow-sm" name="user_type" required>
                                            <option value="" disabled selected>-- Select Role --</option>
                                            <option value="ADM">Administrator (ADM)</option>
                                            <option value="SPV">Supervisor (SPV)</option>
                                            <option value="PGS">PENGAWAS (PGS)</option>
                                            <option value="ASO">Assistant Superintending Officer (ASO)</option>
                                            <option value="SO">Superintending Officer (SO)</option>
                                            <option value="PGRH">Pengarah (PGRH)</option>
                                            <option value="ROOT">Super Admin (ROOT)</option>
                                            <option value="CON">Contractor (CON)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card bg-light border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="mb-3 text-primary fw-bold"><i class="icon-info me-1"></i> Personal Information</h6>
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label fw-semibold text-muted">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control border-0 shadow-sm" name="fullname" placeholder="Enter full name" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold text-muted">Phone Number</label>
                                        <input type="text" class="form-control border-0 shadow-sm" name="phone" placeholder="e.g. 012-3456789">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold text-muted">Gender</label>
                                        <select class="form-select form-control border-0 shadow-sm" name="sex">
                                            <option value="M">Male</option>
                                            <option value="F">Female</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold text-muted">Section</label>
                                        <input type="text" class="form-control border-0 shadow-sm" name="section" placeholder="e.g. IT Section">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold text-muted">Division</label>
                                        <input type="text" class="form-control border-0 shadow-sm" name="division" placeholder="e.g. Digital">
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm"><i class="icon-check me-1"></i> Create User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- plugins:js -->
    <script src="assets/vendors/js/vendor.bundle.base.js"></script>
    <!-- endinject -->
    <script src="assets/vendors/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- inject:js -->
    <script src="assets/js/off-canvas.js"></script>
    <script src="assets/js/hoverable-collapse.js"></script>
    <script src="assets/js/misc.js"></script>
    <!-- endinject -->
</body>
</html>
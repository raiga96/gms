<?php
session_start();
require 'includes/connection.php';

//Total Zone
$sql = "SELECT COUNT(*) AS total_zone FROM zone";
$res = $conn->query($sql);
$row = $res->fetch_assoc();
$total_zone = $row['total_zone'];

//Total sub-zone
$sql1 = "SELECT COUNT(*) AS total_subzone FROM subzone";
$res1 = $conn->query($sql1);
$row1 = $res1->fetch_assoc();
$total_subzone = $row1['total_subzone'];

// Total Perimeter(m) by zone
$sql2 = "SELECT SUM(perimeter) AS total_perimeter FROM subzone ";
$res2 = $conn->query($sql2);
$row2 = $res2->fetch_assoc();
$total_perimeter = $row2['total_perimeter'] ?? 0;

// Total Area(ha) by zone
$sql3 = "SELECT SUM(area) AS total_area FROM subzone";
$res3 = $conn->query($sql3);
$row3 = $res3->fetch_assoc();
$total_area = $row3['total_area'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>SmartCut</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="assets/vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="assets/vendors/flag-icon-css/css/flag-icons.min.css">
    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" integrity="sha512-5Hs3dF2AEPkpNAR7UiOHba+lRSJNeM2ECkwxUIxC1Q/FLycGTbNapWXB4tP889k5T5Ju8fs4b1P5z/iB4nMfSQ==" crossorigin="anonymous" referrerpolicy="no-referrer">

    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="assets/vendors/font-awesome/css/font-awesome.min.css" />
    <link rel="stylesheet" href="assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="assets/vendors/jvectormap/jquery-jvectormap.css">
    <link rel="stylesheet" href="assets/vendors/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="assets/vendors/chartist/chartist.min.css">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="assets/css/vertical-light-layout/style.css">
    <!-- End layout styles -->
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

        /* Modern Table Card */
        .modern-zone-container .card {
            border: none;
            border-radius: 16px !important;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05), 0 5px 15px -8px rgba(0, 0, 0, 0.05) !important;
            background: #ffffff;
            overflow: hidden;
        }

        .modern-zone-container .card-title {
            font-size: 18px;
            font-weight: 600;
            color: #0f172a;
            margin: 0;
        }

        .modern-table-wrapper {
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            margin-top: 15px;
        }

        .modern-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .modern-table th {
            background-color: #f8fafc;
            color: #475569;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 14px 16px;
            border-bottom: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
            text-align: left;
        }

        .modern-table th:last-child {
            border-right: none;
        }

        .modern-table td {
            padding: 14px 16px;
            vertical-align: middle;
            border-bottom: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
            font-size: 14px;
            color: #334155;
            background-color: #ffffff;
        }

        .modern-table td:last-child {
            border-right: none;
        }

        .modern-table tr:last-child td {
            border-bottom: none;
        }

        .modern-table tbody tr:hover td {
            background-color: #f8fafc;
        }

        /* Action Buttons */
        .action-btn-group {
            display: inline-flex;
            gap: 8px;
            align-items: center;
        }

        .btn-circle-edit {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #eff6ff;
            color: #3b82f6;
            border: none;
            font-size: 13px;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-circle-edit:hover {
            background-color: #3b82f6;
            color: #ffffff;
            transform: scale(1.05);
            box-shadow: 0 4px 10px rgba(59, 130, 246, 0.2);
            text-decoration: none;
        }

        .btn-circle-delete {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #fef2f2;
            color: #ef4444;
            border: none;
            font-size: 13px;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .btn-circle-delete:hover {
            background-color: #ef4444;
            color: #ffffff;
            transform: scale(1.05);
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.2);
        }

        /* Link Styling */
        .modern-link {
            color: #1e3a8a;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.15s ease;
        }

        .modern-link:hover {
            color: #3b82f6;
            text-decoration: none;
        }

        .address-column {
            max-width: 200px;
            white-space: normal !important;
            word-break: break-word;
            line-height: 1.5;
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
                    <!-- Report Summary Start -->
                    <!-- <div class="row">
                        <div class="col-md-12 grid-margin">
                            <div class="card shadow">
                                <div class="card-body">

                                    <div class="row report-inner-cards-wrapper">
                                        <div class=" col-md -6 col-xl report-inner-card">
                                            <div class="inner-card-text">
                                                <span class="report-title">Total Contractor</span>
                                                <h2><?php echo $total_zone ?></h2>
                                            </div>
                                            <div class="inner-card-icon bg-success">
                                                <i class="icon-map"></i>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-xl report-inner-card">
                                            <div class="inner-card-text">
                                                <span class="report-title">Total Sub-zone</span>
                                                <h2><?php echo $total_subzone ?></h2>
                                            </div>
                                            <div class="inner-card-icon bg-danger">
                                                <i class="icon-map"></i>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-xl report-inner-card">
                                            <div class="inner-card-text">
                                                <span class="report-title">Total Perimeter</span>
                                                <h2><?php echo number_format($total_perimeter) ?> m</h2>
                                            </div>
                                            <div class="inner-card-icon bg-primary">
                                                <i class="icon-map"></i>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-xl report-inner-card">
                                            <div class="inner-card-text">
                                                <span class="report-title">Total Area</span>
                                                <h2><?php echo number_format($total_area, 2) ?> ha</h2>
                                            </div>
                                            <div class="inner-card-icon bg-warning">
                                                <i class="icon-map"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> -->
                    <!-- Report Summary End -->
                    <!-- Performance Indicator Start -->

                    <div class="modern-zone-container">
                        <!-- Action Banner / Header -->
                        <div class="card modern-banner bg-dark mt-4">
                            <div class="card-body p-0 d-sm-flex align-items-center justify-content-between">
                                <h4 class="modern-banner-title"><i class="icon-people mr-2"></i> Contractor Management</h4>
                                <a href="new_contractor?page=con" class="modern-btn-white mt-3 mt-sm-0"><i class="icon-plus"></i> Add New Contractor</a>
                            </div>
                        </div>

                        <!-- Table Section -->
                        <div class="row">
                            <div class="col-md-12 grid-margin stretch-card">
                                <div class="card shadow">
                                    <div class="card-body">
                                        <div class="d-sm-flex align-items-center justify-content-between mb-3">
                                            <h4 class="card-title">Contractor List</h4>
                                        </div>
                                        <div class="modern-table-wrapper">
                                            <table class="modern-table">
                                                <thead>
                                                    <tr>
                                                        <th>Contractor Name</th>
                                                        <th>Address</th>
                                                        <th>Contract</th>
                                                        <th>Price</th>
                                                        <th style="width: 120px; text-align: center;">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $sql = "SELECT * FROM contractor ORDER BY name ASC";
                                                    $res = $conn->query($sql);
                                                    if ($res->num_rows > 0) {
                                                        while ($row = $res->fetch_assoc()) {
                                                            $sql = "SELECT COUNT(contract_id) AS count, SUM(fund) AS fund FROM contract WHERE cid = '" . $row['cid'] . "' AND c_status = 'Y'";
                                                            $res1 = $conn->query($sql);
                                                            $row1 = $res1->fetch_assoc();
                                                            $contract = $row1['count'];
                                                            $fund = $row1['fund'] ?? 0;
                                                    ?>
                                                            <tr>
                                                                <td>
                                                                    <a class="modern-link" href="contractor_details?id=<?php echo $row['cid'] ?>">
                                                                        <i class="icon-user text-primary mr-1"></i> <?php echo $row['name'] ?>
                                                                    </a>
                                                                </td>
                                                                <td class="address-column"><?php echo $row['address'] ?></td>
                                                                <td class="font-weight-bold text-dark"><?php echo $contract ?></td>
                                                                <td>RM <?php echo number_format($fund, 2) ?></td>
                                                                <td style="text-align: center;">
                                                                    <div class="action-btn-group">
                                                                        <a href="update_contractor.php?id=<?php echo $row['cid'] ?>" class="btn-circle-edit" title="Edit Contractor">
                                                                            <i class="fa fa-pencil"></i>
                                                                        </a>
                                                                        <button type="button" class="btn-circle-delete" data-id="<?php echo $row['cid'] ?>" onclick="deleteDb(this)" title="Delete Contractor">
                                                                            <i class="fa fa-trash"></i>
                                                                        </button>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        <?php }
                                                    } else { ?>
                                                        <tr>
                                                            <td colspan="5" class="text-center text-danger font-weight-bold py-4">
                                                                <i class="icon-info mr-2"></i> No contractor data available.
                                                            </td>
                                                        </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                </div>
                            </div>
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
        function deleteDb(btn) {

            let id = btn.dataset.id;
            let row = btn.closest("tr");

            // ambil nama contractor
            let zone = row.cells[0].innerText.trim();

            Swal.fire({
                title: "Delete Contractor?",
                html: "Are you sure want to delete <b>" + zone + "</b> ?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, delete",
                cancelButtonText: "Cancel"
            }).then((result) => {

                if (result.isConfirmed) {

                    fetch("includes/delete_zone.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: "id=" + id
                        })
                        .then(res => res.text())
                        .then(data => {

                            if (data.trim() == "success") {

                                Swal.fire({
                                    title: "Deleted!",
                                    text: zone + " has been removed.",
                                    icon: "success"
                                }).then(() => {
                                    location.reload(); // refresh page
                                });

                            } else {

                                Swal.fire(
                                    "Error!",
                                    "Failed to delete subzone.",
                                    "error"
                                );

                            }

                        });

                }

            });

        }
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


</body>

</html>
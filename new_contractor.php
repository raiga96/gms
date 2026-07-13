<?php
session_start();

require 'includes/connection.php';

$page = $_GET['page'] ?? '';


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
        
        .modern-form-container {
            font-family: 'Outfit', sans-serif;
            color: #334155;
        }

        .modern-banner {
            background: linear-gradient(135deg, #1d384d 0%, #007442 100%) !important;
            border-radius: 16px !important;
        }
        
        .modern-form-container .card {
            border: none;
            border-radius: 16px !important;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05), 0 5px 15px -8px rgba(0, 0, 0, 0.05) !important;
            background: #ffffff;
        }
        
        .modern-form-container .form-label {
            font-weight: 600;
            color: #475569;
            font-size: 14px;
            margin-bottom: 8px;
            letter-spacing: 0.3px;
        }
        
        .modern-form-container .form-control {
            border-radius: 10px;
            padding: 12px 16px;
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
            color: #1e293b;
            transition: all 0.3s ease;
        }
        
        .modern-form-container .input-group-text {
            border-radius: 10px 0 0 10px;
            border: 1px solid #cbd5e1;
            padding-left: 16px;
            padding-right: 12px;
        }
        
        .modern-form-container .form-control:focus {
            border-color: #3b82f6;
            box-shadow: none;
            background-color: #ffffff;
        }
        
        .modern-form-container .input-group:focus-within .input-group-text,
        .modern-form-container .input-group:focus-within .form-control {
            border-color: #3b82f6;
            background-color: #ffffff;
        }
        
        .modern-form-container .btn-modern {
            padding: 12px 30px;
            border-radius: 50rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        
        .btn-modern-primary {
            background: #007442;
            border: none;
            color: white;
            box-shadow: 0 4px 12px rgba(0, 116, 66, 0.2);
        }
        
        .btn-modern-primary:hover {
            background: #005a33;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 116, 66, 0.3);
            color: white;
        }
        
        .btn-modern-light {
            background: #f1f5f9;
            border: none;
            color: #475569;
        }
        
        .btn-modern-light:hover {
            background: #e2e8f0;
            color: #1e293b;
            transform: translateY(-2px);
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

                    <div class="modern-form-container">
                        <div class="row mt-4 mb-4">
                            <div class="col-12">
                                <div class="card border-0 modern-banner">
                                    <div class="card-body p-4 d-sm-flex align-items-center justify-content-between text-white">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-white bg-opacity-25 p-3 rounded-circle me-3">
                                                <i class="icon-briefcase text-white" style="font-size: 24px;"></i>
                                            </div>
                                            <div>
                                                <h3 class="mb-1 text-white fw-bold">Register Contractor</h3>
                                                <p class="mb-0 text-white-50 small">Add a new contractor profile to the system</p>
                                            </div>
                                        </div>
                                        <a href="contractor_list.php" class="btn btn-light rounded-pill mt-3 mt-sm-0 text-dark fw-bold px-4 shadow-sm">
                                            <i class="icon-arrow-left me-2"></i> Back to List
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-12 grid-margin stretch-card">
                                <div class="card">
                                    <div class="card-body p-5">
                                        <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                                            <div class="bg-soft-primary text-primary p-2 rounded me-3" style="background: #e0e7ff;">
                                                <i class="icon-info" style="font-size: 20px;"></i>
                                            </div>
                                            <h4 class="card-title mb-0 fw-bold text-dark">Contractor Details</h4>
                                        </div>
                                        
                                        <form action="includes/new_contractor.php" method="POST">
                                            <input type="text" name="page" value="<?php echo $page ?>" hidden>
                                            
                                            <div class="row g-4">
                                                <div class="col-md-12">
                                                    <label for="contractorName" class="form-label">Contractor Name <span class="text-danger">*</span></label>
                                                    <div class="input-group shadow-sm" style="border-radius: 10px;">
                                                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="icon-briefcase"></i></span>
                                                        <input type="text" class="form-control border-start-0 ps-0" name="contractorName" placeholder="e.g. Bina Maju Sdn Bhd" required>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <label for="contractorEmail" class="form-label">Email Address <span class="text-danger">*</span></label>
                                                    <div class="input-group shadow-sm" style="border-radius: 10px;">
                                                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="icon-envelope"></i></span>
                                                        <input type="email" class="form-control border-start-0 ps-0" name="contractorEmail" placeholder="example@domain.com" required>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <label for="contractorContact" class="form-label">Contact Number <span class="text-danger">*</span></label>
                                                    <div class="input-group shadow-sm" style="border-radius: 10px;">
                                                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="icon-phone"></i></span>
                                                        <input type="text" class="form-control border-start-0 ps-0" name="contractorContact" placeholder="e.g. 012-3456789" required>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <label for="contractorPic" class="form-label">Person in Charge (PIC) <span class="text-danger">*</span></label>
                                                    <div class="input-group shadow-sm" style="border-radius: 10px;">
                                                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="icon-user"></i></span>
                                                        <input type="text" class="form-control border-start-0 ps-0" name="contractorPic" placeholder="Enter PIC name" required>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <label for="contractorAddress" class="form-label">Registered Address</label>
                                                    <div class="input-group shadow-sm" style="border-radius: 10px;">
                                                        <span class="input-group-text bg-light border-end-0 text-muted align-items-start pt-3"><i class="icon-location-pin"></i></span>
                                                        <textarea class="form-control border-start-0 ps-0" name="contractorAddress" rows="3" placeholder="Enter full address..."></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <hr class="my-5 border-light">

                                            <div class="d-flex justify-content-center gap-3">
                                                <a href="contractor_list.php" class="btn btn-modern btn-modern-light"><i class="icon-close me-1"></i> Cancel</a>
                                                <button type="submit" class="btn btn-modern btn-modern-primary"><i class="icon-check me-1"></i> Save Contractor</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Jika ada contractor row ini akan appear -->
                </div>


                <!-- content-wrapper ends -->
                <!-- partial:partials/_footer.html -->

                <?php include 'partials/footer.php'; ?>

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
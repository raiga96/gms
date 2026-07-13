<nav class="navbar default-layout-navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
    <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
        <a class="navbar-brand brand-logo" href="index.html" style="text-decoration:none;">
            <div style="display:flex; align-items:center; gap:8px;">
                <img src="assets/images/gms.png" alt="logo" class="logo-dark" style="height:60px; width:auto;"/>
                <div class="logo-dark-text-group" style="display:flex; flex-direction:column; align-items:flex-start; line-height:1.2; text-align:left;">
                    <span class="font-weight-bold" style="font-size:12px; font-family:'Poppins', sans-serif; color:#ffffff; white-space:nowrap;">Green Management</span>
                    <span class="font-weight-medium" style="font-size:10px; font-family:'Poppins', sans-serif; color:#94a3b8; letter-spacing:1px;">SYSTEM</span>
                </div>
                <img src="assets/images/gms.png" alt="logo-light" class="logo-light" style="height:40px; width:auto;">
            </div>
        </a>
        <a class="navbar-brand brand-logo-mini" href="index.html"><img src="assets/images/gms.png" alt="logo" style="height:50px;width:auto"/></a>
        <!-- <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
            <span class="icon-menu"></span>
        </button> -->
        <div style="padding-left:95px"></div>
    </div>
    <div class="navbar-menu-wrapper d-flex align-items-center">
        <h5 class="mb-0 font-weight-medium d-none d-lg-flex">Welcome to Green Management System</h5>
        <ul class="navbar-nav navbar-nav-right">
           

            <?php
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $nav_fullname = $_SESSION['fullname'] ?? 'User';
            $nav_userid = $_SESSION['user_id'] ?? '';
            $nav_role = $_SESSION['user_type'] ?? 'Staff';
            
            $role_names = [
                'SPV' => 'Supervisor',
                'PGS' => 'Pengawas',
                'ASO' => 'Assistant Superintending Officer',
                'SO' => 'Superintending Officer',
                'PGRH' => 'Pengarah',
                'ADM' => 'Administrator',
                'ROOT' => 'Super Admin',
                'CONTRACTOR' => 'Contractor'
            ];
            $pretty_role = $role_names[$nav_role] ?? $nav_role;
            ?>
            <li class="nav-item dropdown d-none d-xl-inline-flex user-dropdown">
                <a class="nav-link dropdown-toggle" id="UserDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                    <img class="img-xs rounded-circle ms-2" src="assets/images/faces/face8.jpg" alt="Profile image"> 
                    <span class="font-weight-normal text-dark"> <?php echo htmlspecialchars($nav_fullname); ?> </span>
                </a>
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="UserDropdown">
                    <div class="dropdown-header text-center">
                        <img class="img-md rounded-circle" src="assets/images/faces/face8.jpg" alt="Profile image">
                        <p class="mb-1 mt-3 font-weight-bold text-dark"><?php echo htmlspecialchars($nav_fullname); ?></p>
                        <p class="font-weight-light text-muted mb-0"><?php echo htmlspecialchars($nav_userid . ' | ' . $pretty_role); ?></p>
                    </div>
                    <a class="dropdown-item" href="manage_profile.php"><i class="dropdown-item-icon icon-user text-primary"></i> Manage Profile</a>
                    <a class="dropdown-item" href="change_password.php"><i class="dropdown-item-icon icon-lock text-primary"></i> Change Password</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="logout.php"><i class="dropdown-item-icon icon-power text-danger"></i> Sign Out</a>
                </div>
            </li>
        </ul>
        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
            <span class="icon-menu"></span>
        </button>
    </div>
</nav>
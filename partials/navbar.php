<nav class="navbar default-layout-navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
    <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-between px-3">
        <a class="navbar-brand brand-logo" href="index.php" style="text-decoration:none;">
            <div style="display:flex; align-items:center; gap:10px;">
                <img src="assets/images/gms.png" alt="DBKU GMS Logo" class="logo-dark" style="height:46px; width:auto; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.25));"/>
                <div class="logo-dark-text-group" style="display:flex; flex-direction:column; align-items:flex-start; line-height:1.2; text-align:left;">
                    <span class="font-weight-bold" style="font-size:13px; font-weight:700; letter-spacing:0.01em; color:#ffffff; line-height:1.25;"><span style="color:#10b981;">Green</span> Management<br>System</span>
                </div>
            </div>
        </a>
        <a class="navbar-brand brand-logo-mini" href="index.php">
            <img src="assets/images/gms.png" alt="logo" style="height:38px; width:auto;"/>
        </a>
    </div>
    <div class="navbar-menu-wrapper d-flex align-items-center justify-content-between">
        <div class="d-none d-lg-flex align-items-center gap-3">
            <div class="gms-nav-badge">
                <span>Portal Operasi GMS</span>
            </div>
            <span class="text-muted small d-none d-xl-inline">|</span>
            <span class="text-secondary small font-weight-medium d-none d-xl-inline">
                <i class="icon-calendar me-1 text-primary"></i> <?php echo gms_format_date(time(), 'l, d F Y'); ?>
            </span>
        </div>

        <ul class="navbar-nav navbar-nav-right align-items-center">
            <?php
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $nav_fullname = $_SESSION['fullname'] ?? 'Pengguna';
            $nav_userid = $_SESSION['user_id'] ?? '';
            $nav_role = $_SESSION['user_type'] ?? 'Staf';
            
            $role_names = [
                'SPV' => 'Penyelia',
                'PGS' => 'Pengawas',
                'ASO' => 'Penolong Pegawai Penguasa',
                'SO' => 'Pegawai Penguasa',
                'PGRH' => 'Pengarah',
                'ADM' => 'Administrator',
                'ROOT' => 'Pentadbir Utama',
                'CONTRACTOR' => 'Kontraktor'
            ];
            $pretty_role = $role_names[$nav_role] ?? $nav_role;
            ?>
            <li class="nav-item dropdown d-inline-flex user-dropdown">
                <a class="nav-link dropdown-toggle" id="UserDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="gms-user-avatar">
                        <img class="img-xs rounded-circle" src="assets/images/faces/face8.jpg" alt="Imej profil">
                        <span class="gms-user-status" aria-hidden="true"></span>
                    </span>
                    <span class="gms-user-meta d-none d-md-flex flex-column text-start">
                        <span class="gms-user-name"> <?php echo htmlspecialchars($nav_fullname); ?> </span>
                        <span class="gms-user-role"> <?php echo htmlspecialchars($pretty_role); ?> </span>
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="UserDropdown">
                    <div class="dropdown-header text-center">
                        <img class="img-md rounded-circle" src="assets/images/faces/face8.jpg" alt="Imej profil" style="border: 3px solid #10b981;">
                        <p class="mb-1 mt-3 font-weight-bold text-dark"><?php echo htmlspecialchars($nav_fullname); ?></p>
                        <p class="font-weight-light text-muted mb-0 small"><?php echo htmlspecialchars($nav_userid . ' • ' . $pretty_role); ?></p>
                    </div>
                    <a class="dropdown-item" href="manage_profile.php"><i class="dropdown-item-icon icon-user text-primary"></i> Profil Saya</a>
                    <a class="dropdown-item" href="change_password.php"><i class="dropdown-item-icon icon-lock text-primary"></i> Tukar Kata Laluan</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="logout.php"><i class="dropdown-item-icon icon-power text-danger"></i> Log Keluar</a>
                </div>
            </li>
        </ul>
        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas" aria-label="Buka atau tutup menu navigasi" aria-controls="sidebar" aria-expanded="false">
            <span class="icon-menu"></span>
        </button>
    </div>
</nav>

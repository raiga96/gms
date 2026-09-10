<?php
$current_page = basename($_SERVER['PHP_SELF']);
function is_active($page, $current) {
    if ($page === 'index' && ($current === 'index.php' || $current === 'index')) return 'active';
    if (strpos($current, $page) !== false) return 'active';
    return '';
}
?>
<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        <!-- Brand Header for Mobile / Collapsed View -->
        <li class="nav-item navbar-brand-mini-wrapper d-none">
            <a class="nav-link navbar-brand brand-logo-mini" href="index.php">
                <img src="assets/images/gms.png" alt="logo" style="height:36px"/>
            </a>
        </li>

        <!-- Menu Section (Donezo Style) -->
        <li class="nav-item nav-category">
            <span class="nav-link">Menu</span>
        </li>
        <li class="nav-item <?php echo is_active('index', $current_page); ?>">
            <a class="nav-link" href="index.php">
                <i class="icon-grid menu-icon"></i>
                <span class="menu-title">Papan Pemuka</span>
            </a>
        </li>
        <li class="nav-item <?php echo is_active('zone', $current_page); ?>">
            <a class="nav-link" href="zone.php">
                <i class="icon-layers menu-icon"></i>
                <span class="menu-title">Pengurusan Zon</span>
            </a>
        </li>
        <li class="nav-item <?php echo is_active('contract_list', $current_page); ?>">
            <a class="nav-link" href="contract_list.php">
                <i class="icon-doc menu-icon"></i>
                <span class="menu-title">Kontrak</span>
            </a>
        </li>
        <li class="nav-item <?php echo is_active('contractor', $current_page); ?>">
            <a class="nav-link" href="contractor_list.php">
                <i class="icon-people menu-icon"></i>
                <span class="menu-title">Kontraktor</span>
            </a>
        </li>

        <!-- General Section (Donezo Style) -->
        <li class="nav-item nav-category">
            <span class="nav-link">General</span>
        </li>
        <li class="nav-item <?php echo is_active('users_management', $current_page); ?>">
            <a class="nav-link" href="users_management.php">
                <i class="icon-settings menu-icon"></i>
                <span class="menu-title">Pengurusan Pengguna</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="docs/documentation.html" target="_blank">
                <i class="icon-question menu-icon"></i>
                <span class="menu-title">Dokumentasi & Bantuan</span>
            </a>
        </li>
    </ul>

    <!-- Bottom Telemetry Card (Donezo / Executive High-Tech Dark Style) -->
    <div class="sidebar-system-status d-none d-xl-block">
        <div class="d-flex align-items-center mb-1">
            <span class="gms-pulse-dot me-2"></span>
            <span style="font-size: 11px; font-weight: 700; color: #10b981; letter-spacing: 0.04em;">Green Management System</span>
        </div>
        <p class="mb-0" style="font-size: 11px; color: #7e9e92; line-height: 1.3;">Enjin Pemantauan Dalam Talian</p>
    </div>
</nav>

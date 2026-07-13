<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
.sidebar {
    font-family: 'Poppins', sans-serif !important;
}
.sidebar .nav .nav-category .nav-link {
    font-family: 'Poppins', sans-serif !important;
}
</style>
<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        <li class="nav-item navbar-brand-mini-wrapper">
            <a class="nav-link navbar-brand brand-logo-mini" href="index.html"><img src="assets/images/gms.png" alt="logo" style="height:50px"/></a>
        </li>
        <!-- <li class="nav-item nav-profile">
            <a href="#" class="nav-link">
                <div class="profile-image">
                    <img class="img-xs rounded-circle" src="assets/images/faces/face8.jpg" alt="profile image">
                    <div class="dot-indicator bg-success"></div>
                </div>
                <div class="text-wrapper">
                    <p class="profile-name">DBKU ADMIN</p>
                    <p class="designation">Administrator</p>
                </div>
                <div class="icon-container">
                    <i class="icon-bubbles"></i>
                    <div class="dot-indicator bg-danger"></div>
                </div>
            </a>
        </li> -->
        <li class="nav-item nav-category">
            <span class="nav-link">Dashboard</span>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="index">
                <span class="menu-title">Dashboard</span>
                <i class="icon-screen-desktop menu-icon"></i>
            </a>
        </li>
        <li class="nav-item nav-category"><span class="nav-link">Management</span></li>
        <li class="nav-item">
            <a class="nav-link" href="zone">
                <span class="menu-title">Zone</span>
                <i class="icon-layers menu-icon"></i>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="contract_list">
                <span class="menu-title">Contract</span>
                <i class="icon-list menu-icon"></i>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="contractor_list">
                <span class="menu-title">Contractor</span>
                <i class="icon-user menu-icon"></i>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="users_management">
                <span class="menu-title">User Management</span>
                <i class="icon-people menu-icon"></i>
            </a>
        </li>
        
        <!-- <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#ui-basic" aria-expanded="false" aria-controls="ui-basic">
                <span class="menu-title">Basic UI Elements</span>
                <i class="icon-layers menu-icon"></i>
            </a>
            <div class="collapse" id="ui-basic">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item"> <a class="nav-link" href="pages/ui-features/buttons.html">Zone</a></li>
                    <li class="nav-item"> <a class="nav-link" href="pages/ui-features/dropdowns.html">Dropdowns</a></li>
                    <li class="nav-item"> <a class="nav-link" href="pages/ui-features/typography.html">Typography</a></li>
                </ul>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#icons" aria-expanded="false" aria-controls="icons">
                <span class="menu-title">Zone</span>
                <i class="icon-globe menu-icon"></i>
            </a>
            <div class="collapse" id="icons">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item"> <a class="nav-link" href="pages/icons/font-awesome.html">Font Awesome</a></li>
                </ul>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#forms" aria-expanded="false" aria-controls="forms">
                <span class="menu-title">Forms</span>
                <i class="icon-book-open menu-icon"></i>
            </a>
            <div class="collapse" id="forms">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item"> <a class="nav-link" href="pages/forms/basic_elements.html">Form Elements</a></li>
                </ul>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#charts" aria-expanded="false" aria-controls="charts">
                <span class="menu-title">Charts</span>
                <i class="icon-chart menu-icon"></i>
            </a>
            <div class="collapse" id="charts">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item"> <a class="nav-link" href="pages/charts/chartjs.html">ChartJs</a></li>
                </ul>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#tables" aria-expanded="false" aria-controls="tables">
                <span class="menu-title">Tables</span>
                <i class="icon-grid menu-icon"></i>
            </a>
            <div class="collapse" id="tables">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item"> <a class="nav-link" href="pages/tables/basic-table.html">Basic Table</a></li>
                </ul>
            </div>
        </li> -->
        <!-- <li class="nav-item nav-category"><span class="nav-link">COMPONENTS</span></li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#data" aria-expanded="false" aria-controls="auth">
                <span class="menu-title">DATA ENTRY</span>
                <i class="icon-disc menu-icon"></i>
            </a>
            <div class="collapse" id="data">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item"> <a class="nav-link disabled" href="pages/samples/blank-page.html"> DATA ENTRY </a></li>
                    <li class="nav-item"> <a class="nav-link disabled" href="pages/samples/login.html"> DATA UPDATE </a></li>
                    <li class="nav-item"> <a class="nav-link disabled" href="pages/samples/register.html"> CONTRACTOR </a></li>
                    <li class="nav-item"> <a class="nav-link disabled" href="pages/samples/error-404.html"> PAYMENT UPDATE </a></li>
                    <li class="nav-item"> <a class="nav-link disabled" href="pages/samples/error-500.html"> PERFORMANCE </a></li>
                </ul>
            </div>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#details" aria-expanded="false" aria-controls="auth">
                <span class="menu-title">WORK DETAILS</span>
                <i class="icon-disc menu-icon"></i>
            </a>
            <div class="collapse" id="details">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item"> <a class="nav-link disabled" href="pages/samples/blank-page.html"> WORK SCHEDULE </a></li>
                    <li class="nav-item"> <a class="nav-link disabled" href="pages/samples/login.html"> DAILY DIARY </a></li>
                    <li class="nav-item"> <a class="nav-link disabled" href="pages/samples/register.html"> Register </a></li>
                    <li class="nav-item"> <a class="nav-link disabled" href="pages/samples/error-404.html"> 404 </a></li>
                    <li class="nav-item"> <a class="nav-link disabled" href="pages/samples/error-500.html"> 500 </a></li>
                </ul>
            </div>
        </li> -->
        <li class="nav-item nav-category"><span class="nav-link">REPORTING</span></li>
        <li class="nav-item">
            <a class="nav-link disabled" href="../../docs/documentation.html" target="_blank">
                <span class="menu-title">REPORTS</span>
                <i class="icon-folder-alt menu-icon"></i>
            </a>
        </li>
    </ul>
</nav>
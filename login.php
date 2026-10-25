<?php
session_start();
// If already logged in, redirect to index
if(isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Green Management System</title>
    <link rel="stylesheet" href="assets/vendors/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="assets/vendors/flag-icon-css/css/flag-icons.min.css">
    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="assets/css/vertical-light-layout/style.css">
    <link rel="shortcut icon" href="assets/images/favicon.png" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #00884dff;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        .login-card {
            display: flex;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 850px; 
            max-width: 95%;
            height: 500px;
        }

        .login-left {
            flex: 1.2;
            background: #ffffff;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .login-left::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-image: url('assets/images/login_bg.png?v=2.0');
            background-size: cover;
            background-position: center;
            z-index: 0;
        }
        
        .login-left::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(135deg, rgba(29, 56, 77, 0.6) 0%, rgba(0, 116, 66, 0.6) 100%);
            z-index: 1;
        }
        
        .login-left-content {
            position: relative;
            z-index: 2;
        }

        .login-left h2 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 15px;
            color: #ffffff;
        }

        .login-left p {
            font-size: 15px;
            line-height: 1.6;
            opacity: 0.9;
            color: #e2e8f0;
        }

        .login-right {
            flex: 1;
            padding: 40px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #ffffff;
            border-left: none;
        }

        .login-right .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-right .logo img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
        }

        .login-right h3 {
            font-size: 24px;
            font-weight: 700;
            color: #007442;
            margin-bottom: 25px;
            text-align: center;
        }

        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            color: #334155;
            margin-bottom: 15px;
            font-size: 14px;
            font-family: 'Outfit', sans-serif;
        }
        
        .form-control::placeholder {
            color: #94a3b8;
        }

        .form-control:focus {
            border-color: #007442;
            box-shadow: 0 0 0 3px rgba(0,116,66,0.1);
            background: #ffffff;
            color: #334155;
        }

        .btn-login {
            background: linear-gradient(90deg, #1d384d 0%, #007442 100%);
            color: #ffffff;
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: 700;
            width: 100%;
            margin-top: 10px;
            transition: all 0.3s ease;
            font-family: 'Outfit', sans-serif;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,116,66,0.3);
            color: #ffffff;
        }
        
        .alert-custom {
            border-radius: 8px;
            font-size: 13px;
            padding: 10px 15px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .login-card {
                flex-direction: column;
                height: auto;
            }
            .login-left {
                padding: 30px;
                text-align: center;
            }
            .login-right {
                padding: 30px;
            }
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-left">
            <div class="login-left-content">
                <h2>Selamat kembali!</h2>
                <p>SmartCut DBKU ialah sistem bersepadu untuk memantau dan mengurus perkhidmatan pemotongan rumput serta pembersihan longkang dalam kawasan pentadbiran Dewan Bandaraya Kuching Utara.</p>
                <p class="mt-4"><small>&copy; <?php echo date('Y'); ?> DBKU. Hak cipta terpelihara.</small></p>
            </div>
        </div>
        <div class="login-right">
            <div class="logo">
                <img src="assets/images/gms.png" alt="DBKU Logo" style="height:130px; width:auto;">
            </div>
            <h3>Log Masuk</h3>
            
            <?php
            if(isset($_SESSION['login_error'])) {
                echo '<div class="alert alert-danger alert-custom">'.$_SESSION['login_error'].'</div>';
                unset($_SESSION['login_error']);
            }
            if(isset($_SESSION['login_success'])) {
                echo '<div class="alert alert-success alert-custom">'.$_SESSION['login_success'].'</div>';
                unset($_SESSION['login_success']);
            }
            ?>
            
            <form action="includes/login_action.php" method="POST">
                <div class="form-group">
                    <input type="text" class="form-control" name="user_id" aria-label="Nama pengguna atau e-mel" placeholder="Nama pengguna atau e-mel" required>
                </div>
                <div class="form-group">
                    <input type="password" class="form-control" name="password" aria-label="Kata laluan" placeholder="Kata laluan" required>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="form-check m-0">
                        <label class="form-check-label text-muted" style="font-size: 13px;">
                            <input type="checkbox" class="form-check-input"> Ingat saya
                        </label>
                    </div>
                    <a href="#" class="text-muted" style="font-size: 13px; text-decoration: none;">Lupa kata laluan?</a>
                </div>
                <button type="submit" class="btn btn-login">Log Masuk</button>
            </form>
        </div>
    </div>
</body>
</html>

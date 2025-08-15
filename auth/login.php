<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/User.php';

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $pdo = $database->getConnection();
    $user = new User($pdo);
    
    if (isset($_POST['login'])) {
        $username = sanitizeInput($_POST['username']);
        $password = $_POST['password'];
        
        if (empty($username) || empty($password)) {
            $error_message = 'Username dan password harus diisi';
        } else {
            $result = $user->login($username, $password);
            
            if ($result['success']) {
                redirectTo('../dashboard.php');
            } else {
                $error_message = $result['message'];
            }
        }
    }
    
    if (isset($_POST['register'])) {
        $username = sanitizeInput($_POST['reg_username']);
        $email = sanitizeInput($_POST['reg_email']);
        $password = $_POST['reg_password'];
        $confirm_password = $_POST['reg_confirm_password'];
        $full_name = sanitizeInput($_POST['reg_full_name']);
        
        if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
            $error_message = 'Semua field harus diisi';
        } elseif ($password !== $confirm_password) {
            $error_message = 'Password dan konfirmasi password tidak sama';
        } elseif (strlen($password) < 6) {
            $error_message = 'Password minimal 6 karakter';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = 'Format email tidak valid';
        } else {
            $result = $user->register($username, $email, $password, $full_name);
            
            if ($result['success']) {
                $success_message = $result['message'] . '. Silakan login.';
            } else {
                $error_message = $result['message'];
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Prakiraan Cuaca</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .auth-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            padding: 3rem;
            width: 100%;
            max-width: 450px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .auth-tabs {
            display: flex;
            margin-bottom: 2rem;
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.1);
            padding: 5px;
        }
        
        .auth-tab {
            flex: 1;
            padding: 12px;
            text-align: center;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #fff;
            font-weight: 500;
        }
        
        .auth-tab.active {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
        }
        
        .auth-form {
            display: none;
        }
        
        .auth-form.active {
            display: block;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #fff;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: rgba(255, 255, 255, 0.5);
            background: rgba(255, 255, 255, 0.15);
        }
        
        .form-group input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }
        
        .auth-btn {
            width: 100%;
            padding: 12px;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .auth-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
        
        .alert-error {
            background: rgba(231, 76, 60, 0.2);
            color: #fff;
            border: 1px solid rgba(231, 76, 60, 0.3);
        }
        
        .alert-success {
            background: rgba(39, 174, 96, 0.2);
            color: #fff;
            border: 1px solid rgba(39, 174, 96, 0.3);
        }
        
        .back-link {
            text-align: center;
            margin-top: 2rem;
        }
        
        .back-link a {
            color: #fff;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .back-link a:hover {
            color: rgba(255, 255, 255, 0.8);
        }
        
        .auth-title {
            text-align: center;
            color: #fff;
            margin-bottom: 2rem;
            font-size: 2rem;
            font-weight: 700;
        }
    </style>
</head>
<body class="morning">
    <div class="auth-container">
        <div class="auth-card">
            <h1 class="auth-title">
                <i class="fas fa-cloud-sun"></i>
                Prakiraan Cuaca
            </h1>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <div class="auth-tabs">
                <div class="auth-tab active" onclick="switchTab('login')">
                    <i class="fas fa-sign-in-alt"></i> Login
                </div>
                <div class="auth-tab" onclick="switchTab('register')">
                    <i class="fas fa-user-plus"></i> Daftar
                </div>
            </div>
            
            <!-- Login Form -->
            <form class="auth-form active" id="login-form" method="POST">
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i> Username atau Email
                    </label>
                    <input type="text" id="username" name="username" placeholder="Masukkan username atau email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                </div>
                
                <button type="submit" name="login" class="auth-btn">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
            
            <!-- Register Form -->
            <form class="auth-form" id="register-form" method="POST">
                <div class="form-group">
                    <label for="reg_full_name">
                        <i class="fas fa-id-card"></i> Nama Lengkap
                    </label>
                    <input type="text" id="reg_full_name" name="reg_full_name" placeholder="Masukkan nama lengkap" required>
                </div>
                
                <div class="form-group">
                    <label for="reg_username">
                        <i class="fas fa-user"></i> Username
                    </label>
                    <input type="text" id="reg_username" name="reg_username" placeholder="Masukkan username" required>
                </div>
                
                <div class="form-group">
                    <label for="reg_email">
                        <i class="fas fa-envelope"></i> Email
                    </label>
                    <input type="email" id="reg_email" name="reg_email" placeholder="Masukkan email" required>
                </div>
                
                <div class="form-group">
                    <label for="reg_password">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <input type="password" id="reg_password" name="reg_password" placeholder="Masukkan password (min. 6 karakter)" required>
                </div>
                
                <div class="form-group">
                    <label for="reg_confirm_password">
                        <i class="fas fa-lock"></i> Konfirmasi Password
                    </label>
                    <input type="password" id="reg_confirm_password" name="reg_confirm_password" placeholder="Konfirmasi password" required>
                </div>
                
                <button type="submit" name="register" class="auth-btn">
                    <i class="fas fa-user-plus"></i> Daftar
                </button>
            </form>
            
            <div class="back-link">
                <a href="../prakiraan-cuaca.php">
                    <i class="fas fa-arrow-left"></i> Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
    
    <script>
        function switchTab(tab) {
            // Remove active class from all tabs and forms
            document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.auth-form').forEach(f => f.classList.remove('active'));
            
            // Add active class to selected tab and form
            event.target.classList.add('active');
            document.getElementById(tab + '-form').classList.add('active');
        }
        
        // Set background based on time
        function setTimeBasedBackground() {
            const hour = new Date().getHours();
            const body = document.body;
            
            body.className = '';
            
            if (hour >= 5 && hour < 10) {
                body.classList.add('morning');
            } else if (hour >= 10 && hour < 15) {
                body.classList.add('day');
            } else if (hour >= 15 && hour < 18) {
                body.classList.add('evening');
            } else {
                body.classList.add('night');
            }
        }
        
        setTimeBasedBackground();
    </script>
</body>
</html>
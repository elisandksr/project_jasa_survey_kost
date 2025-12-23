<?php
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Mengambil dan membersihkan input
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validasi
    if (empty($email) || empty($password)) {
        $error = 'Email dan password harus diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } else {
        // Query untuk mencari user berdasarkan email
        $sql = "SELECT id_klien, nama_lengkap, email, no_wa, password FROM klien WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Password benar, set session
                $_SESSION['user_id'] = $user['id_klien'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['no_wa'] = $user['no_wa'];
                $_SESSION['isLoggedIn'] = true;

                $success = 'Login berhasil! Mengalihkan ke dashboard...';
                header("refresh:1.5;url=dashboard.php");
            } else {
                $error = 'Password salah!';
            }
        } else {
            $error = 'Akun tidak ditemukan. Silakan daftar terlebih dahulu.';
        }
        $stmt->close();
    }
}

// Cek apakah sudah login
if (isset($_SESSION['isLoggedIn']) && $_SESSION['isLoggedIn'] == true) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Informasi Pemesanan Jasa Survey Kost</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #8B7355 0%, #A0826D 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }

        .container {
            background: white;
            width: 100%;
            max-width: 100%;
            min-height: 100vh;
            border-radius: 0;
            box-shadow: none;
            overflow: auto;
            display: flex;
            flex-direction: column;
        }

        .header {
            background: linear-gradient(135deg, #8B7355 0%, #A0826D 100%);
            padding: 20px 30px;
            border-bottom: 2px solid #D2B48C;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            text-align: center;
        }

        .header h1 {
            font-size: 26px;
            color: #FFF8F0;
            font-weight: 600;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
            margin: 0;
        }

        .content {
            background: #FFF8F0;
            padding: 50px 40px;
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        .login-box {
            width: 100%;
            max-width: 900px;
        }

        .profile-section {
            text-align: center;
            margin-bottom: 40px;
            animation: slideUp 0.6s ease-out;
        }

        .login-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(139, 115, 85, 0.1);
            border: 2px solid #D2B48C;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .profile-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #8B7355 0%, #A0826D 100%);
            border-radius: 15px;
            margin: 0 auto 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 50px;
            box-shadow: 0 8px 25px rgba(139, 115, 85, 0.25);
            animation: bounce 0.8s ease-out;
        }

        @keyframes bounce {
            0% {
                transform: scale(0.8);
                opacity: 0;
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .profile-section h2 {
            font-size: 32px;
            color: #8B7355;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .profile-section p {
            font-size: 14px;
            color: #A0826D;
            font-weight: 500;
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 10px;
            color: #8B7355;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            color: #A0826D;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 14px 16px 14px 50px;
            border: 2px solid #D2B48C;
            border-radius: 12px;
            font-size: 15px;
            color: #8B7355;
            background: white;
            transition: all 0.3s ease;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #8B7355;
            box-shadow: 0 0 0 4px rgba(139, 115, 85, 0.1);
            background: #FFF8F0;
        }

        input[type="email"]::placeholder,
        input[type="password"]::placeholder {
            color: #D2B48C;
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            background: none;
            border: none;
            color: #A0826D;
            cursor: pointer;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.3s ease;
            padding: 0;
        }

        .password-toggle:hover {
            color: #8B7355;
        }

        .password-toggle svg {
            width: 24px;
            height: 24px;
            position: absolute;
        }

        .submit-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #8B7355 0%, #A0826D 100%);
            color: #FFF8F0;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 8px 20px rgba(139, 115, 85, 0.3);
            margin-top: 10px;
            margin-bottom: 25px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(139, 115, 85, 0.4);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .register-link {
            text-align: center;
            font-size: 14px;
            color: #8B7355;
            padding-top: 20px;
            border-top: 1px solid #F0E6D2;
        }

        .register-link span {
            display: block;
            margin-bottom: 12px;
        }

        .register-link a {
            display: inline-block;
            background: transparent;
            color: #A0826D;
            border: 2px solid #D2B48C;
            padding: 10px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .register-link a:hover {
            background: #FFF8F0;
            border-color: #8B7355;
            color: #8B7355;
        }

        .success-message {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            display: block;
            border-left: 4px solid #2e7d32;
            animation: slideDown 0.3s ease;
            font-size: 13px;
        }

        .error-message {
            background: #ffebee;
            color: #c62828;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            display: block;
            border-left: 4px solid #c62828;
            animation: slideDown 0.3s ease;
            font-size: 13px;
        }

        .hidden {
            display: none !important;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .content {
                padding: 60px 20px;
            }

            .profile-section h2 {
                font-size: 28px;
            }

            .profile-icon {
                width: 80px;
                height: 80px;
                font-size: 40px;
            }

            .login-box {
                max-width: 100%;
            }
        }

        @media (max-width: 600px) {
            .header {
                padding: 15px 20px;
            }

            .header h1 {
                font-size: 20px;
            }

            .content {
                padding: 40px 20px;
            }

            .profile-section {
                margin-bottom: 30px;
            }

            .profile-section h2 {
                font-size: 24px;
            }

            .profile-icon {
                width: 70px;
                height: 70px;
                font-size: 35px;
                margin-bottom: 15px;
            }

            input[type="email"],
            input[type="password"] {
                padding: 12px 16px 12px 45px;
                font-size: 14px;
            }

            .submit-btn {
                padding: 12px;
                font-size: 14px;
            }

            .register-link a {
                padding: 8px 20px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Login</h1>
        </div>

        <div class="content">
            <div class="login-box">
                <div class="profile-section">
                    <div class="profile-icon">🏠</div>
                    <h2>Survey Kost Solo</h2>
                    <p>Pesan Survey Kost Anda Sekarang</p>
                </div>

                <div class="login-card">
                    <?php if ($error): ?>
                        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>

                    <form id="loginForm" method="POST" action="">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <div class="input-wrapper">
                                <div class="input-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="2" y="4" width="20" height="16" rx="2"/>
                                        <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                                    </svg>
                                </div>
                                <input type="email" id="email" name="email" placeholder="email@gmail.com" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-wrapper">
                                <div class="input-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                    </svg>
                                </div>
                                <input type="password" id="password" name="password" placeholder="••••••••" required>
                                <button type="button" class="password-toggle" onclick="togglePassword()" id="toggleBtn">
                                    <svg id="eyeOpen" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                    <svg id="eyeClosed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;">
                                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                        <line x1="1" y1="1" x2="23" y2="23"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="submit-btn">Masuk</button>

                        <div class="register-link">
                            <span>Belum punya akun?</span>
                            <a href="register.php">Daftar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeOpen = document.getElementById('eyeOpen');
            const eyeClosed = document.getElementById('eyeClosed');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeOpen.style.display = 'none';
                eyeClosed.style.display = 'block';
            } else {
                passwordInput.type = 'password';
                eyeOpen.style.display = 'block';
                eyeClosed.style.display = 'none';
            }
        }
    </script>
</body>
</html>
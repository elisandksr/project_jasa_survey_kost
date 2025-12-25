<?php
require_once 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';
$success = '';
$username_input = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_input = trim($_POST['username']); 
    $email_input = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($username_input) || empty($email_input) || empty($password)) {
        $error = 'Semua field (Username, Email, Password) harus diisi!';
    } else {
        // Authenticate Admin: Must match BOTH Username AND Email
        $stmt = $conn->prepare("SELECT id_admin, username, password FROM admin WHERE username = ? AND email = ?");
        $stmt->bind_param("ss", $username_input, $email_input);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            $db_password = $admin['password'];
            $valid_login = false;

            // Check Plain Text Password ONLY (As requested for consistency)
            // Ideally this should be password_verify(), but we stick to user's existing pattern.
            if ($password === $db_password) {
                $valid_login = true;
            }

            if ($valid_login) {
                session_regenerate_id(true);
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['role'] = 'admin'; // Added for compatibility
                $_SESSION['admin_id'] = $admin['id_admin'];
                $_SESSION['admin_name'] = $admin['username'];

                $success = 'Login Admin Berhasil!';
                echo "<script>
                        setTimeout(function() {
                            window.location.href = 'admin/dashboard.php';
                        }, 800);
                      </script>";
            } else {
                $error = 'Password salah!';
            }
        } else {
            $error = 'Akun admin tidak ditemukan!';
        }
        $stmt->close();
    }
}

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin/dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Admin - Survey Kost Solo</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Outfit', sans-serif; height: 100vh; overflow: hidden; }
        
        .split-screen {
            display: flex; height: 100%;
        }
        
        /* Left Panel - Branding */
        .left-panel {
            flex: 1; background: linear-gradient(135deg, #8B7355 0%, #6D5B45 100%);
            display: flex; flex-direction: column; justify-content: center; align-items: center;
            color: white; padding: 40px; text-align: center;
            position: relative; overflow: hidden;
        }
        .left-panel::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            background: url('https://img.freepik.com/free-vector/city-skyline-concept-illustration_114360-8923.jpg');
            background-size: cover; opacity: 0.1; mix-blend-mode: overlay;
        }
        .brand-content { position: relative; z-index: 2; animation: fadeIn 1s ease-out; }
        .logo-img { width: 120px; margin-bottom: 20px; filter: drop-shadow(0 4px 10px rgba(0,0,0,0.2)); }
        .brand-title { font-size: 32px; font-weight: 700; margin-bottom: 10px; letter-spacing: 0.5px; }
        .brand-desc { font-size: 16px; opacity: 0.9; max-width: 400px; margin: 0 auto; line-height: 1.6; }
        .brand-subtitle { font-size: 14px; opacity: 0.8; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 5px; }
        
        /* Right Panel - Form */
        .right-panel {
            flex: 1; background: #FFFCF9;
            display: flex; flex-direction: column; justify-content: center; align-items: center;
            padding: 40px; position: relative;
        }
        .login-wrapper { width: 100%; max-width: 420px; animation: slideUp 0.8s ease-out; }
        
        .form-header { margin-bottom: 30px; text-align: left; }
        .form-header h2 { font-size: 28px; color: #5D4037; font-weight: 700; margin-bottom: 8px; }
        .form-header p { color: #8D6E63; font-size: 15px; }
        
        .input-group { margin-bottom: 20px; position: relative; }
        .input-group label { display: block; font-size: 13px; font-weight: 600; color: #5D4037; margin-bottom: 8px; }
        .input-wrapper { position: relative; }
        .input-field {
            width: 100%; padding: 14px 16px 14px 45px;
            border: 2px solid #E5D5C5; border-radius: 12px;
            background: white; font-family: inherit; font-size: 15px;
            transition: all 0.3s; color: #5D4037;
        }
        .input-field:focus {
            border-color: #8B7355; outline: none; box-shadow: 0 4px 12px rgba(139, 115, 85, 0.15);
        }
        .input-icon {
            position: absolute; left: 15px; top: 50%; transform: translateY(-50%);
            color: #A1887F; pointer-events: none;
        }
        
        .btn-submit {
            width: 100%; padding: 15px;
            background: linear-gradient(135deg, #8B7355 0%, #6D5B45 100%);
            color: white; border: none; border-radius: 12px;
            font-weight: 600; font-size: 16px; cursor: pointer;
            transition: 0.3s; margin-top: 10px; box-shadow: 0 4px 15px rgba(139, 115, 85, 0.3);
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(139, 115, 85, 0.4); }
        
        .auth-footer { text-align: center; margin-top: 25px; font-size: 14px; color: #8D6E63; }
        .auth-footer a { color: #8B7355; text-decoration: none; font-weight: 600; }
        .auth-footer a:hover { text-decoration: underline; }
        
        .alert {
            padding: 12px 16px; border-radius: 10px; font-size: 14px; margin-bottom: 25px;
            display: flex; align-items: center; gap: 10px;
        }
        .alert-error { background: #FFEBEE; color: #C62828; border-left: 4px solid #C62828; }
        .alert-success { background: #E8F5E9; color: #2E7D32; border-left: 4px solid #2E7D32; }
        
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        
        @media (max-width: 900px) {
            .split-screen { flex-direction: column; }
            .left-panel { flex: 0.4; min-height: 250px; }
            .right-panel { flex: 0.6; padding: 30px; }
            body { overflow: auto; }
        }
    </style>
</head>
<body>
    <div class="split-screen">
        <!-- Brand Side -->
        <div class="left-panel">
            <div class="brand-content">
                <img src="logo1.png" alt="Logo" class="logo-img">
                <div class="brand-subtitle">PANEL ADMIN</div>
                <h1 class="brand-title">SURVEY KOST</h1>
                <p class="brand-desc">Kelola data pemesanan dan pembayaran dengan mudah dan aman.</p>
            </div>
        </div>
        
        <!-- Form Side -->
        <div class="right-panel">
            <div class="login-wrapper">
                <div class="form-header">
                    <h2>Masuk Admin</h2>
                    <p>Masukkan kredensial Anda untuk masuk.</p>
                </div>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-error">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span><?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php endif; ?>
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        <span><?php echo htmlspecialchars($success); ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="input-group">
                        <label>USERNAME</label>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            </span>
                            <input type="text" name="username" class="input-field" placeholder="Masukkan username" required value="<?php echo htmlspecialchars($username_input); ?>">
                        </div>
                    </div>

                    <div class="input-group">
                        <label>EMAIL</label>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            </span>
                            <input type="email" name="email" class="input-field" placeholder="Masukkan email" required>
                        </div>
                    </div>
                    
                    <div class="input-group">
                        <label>KATA SANDI</label>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </span>
                            <input type="password" name="password" class="input-field" placeholder="••••••••" required>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">MASUK SEKARANG</button>
                    
                    <div class="auth-footer">
                         Bukan Admin? <a href="login.php">Masuk Di Sini</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

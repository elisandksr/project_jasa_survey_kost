<?php
require_once 'config.php';

$error = '';
$success = '';

// Sticky inputs
$input_nama = '';
$input_email = '';
$input_wa = '';

// Clear previous user session to prevent confusion
if (isset($_SESSION['user_logged_in'])) {
    unset($_SESSION['user_logged_in']);
    unset($_SESSION['user_id']);
    unset($_SESSION['nama_lengkap']);
    unset($_SESSION['isLoggedIn']); // Clear legacy flag
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $namaLengkap = trim($_POST['namaLengkap']);
    $email = trim($_POST['email']);
    $whatsapp = trim($_POST['whatsapp']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    $input_nama = $namaLengkap;
    $input_email = $email;
    $input_wa = $whatsapp;

    if (empty($namaLengkap) || empty($email) || empty($whatsapp) || empty($password) || empty($confirmPassword)) {
        $error = 'Harap isi semua kolom yang tersedia.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid (contoh: user@email.com).';
    } elseif (!preg_match('/^[0-9]{10,14}$/', $whatsapp)) {
        $error = 'Nomor WhatsApp harus angka valid (10-14 digit).';
    } elseif (strlen($password) < 8) {
        $error = 'Password harus memiliki minimal 8 karakter.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Konfirmasi password tidak cocok.';
    } else {
        // Check Klien Only
        $stmt_check = $conn->prepare("SELECT id_klien FROM klien WHERE email = ? OR no_wa = ?");
        $stmt_check->bind_param("ss", $email, $whatsapp);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result();

        if ($res_check->num_rows > 0) {
            $error = 'Email atau Nomor WhatsApp sudah terdaftar. Silakan login.';
        } else {
            // Insert Plain Text Password (As requested)
            // Note: Not recommended for security, but requested by user.
            $stmt_insert = $conn->prepare("INSERT INTO klien (nama_lengkap, email, no_wa, password) VALUES (?, ?, ?, ?)");
            $stmt_insert->bind_param("ssss", $namaLengkap, $email, $whatsapp, $password);

            if ($stmt_insert->execute()) {
                $success = 'Registrasi Berhasil! Mengalihkan ke login...';
                // Clear fields
                $input_nama = ''; $input_email = ''; $input_wa = '';
                echo "<script>
                        setTimeout(function() {
                            window.location.href = 'login.php';
                        }, 2000);
                      </script>";
            } else {
                $error = 'Terjadi kesalahan sistem. Coba lagi nanti.';
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Survey Kost Solo</title>
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
        
        /* Right Panel - Form */
        .right-panel {
            flex: 1; background: #FFFCF9;
            display: flex; flex-direction: column; justify-content: center; align-items: center;
            padding: 40px; position: relative;
        }
        /* Wider wrapper for register form */
        .login-wrapper { width: 100%; max-width: 480px; animation: slideUp 0.8s ease-out; }
        
        .form-header { margin-bottom: 30px; text-align: left; }
        .form-header h2 { font-size: 28px; color: #5D4037; font-weight: 700; margin-bottom: 8px; }
        .form-header p { color: #8D6E63; font-size: 15px; }
        
        .input-group { margin-bottom: 15px; position: relative; }
        .input-group label { display: block; font-size: 12px; font-weight: 700; color: #8B7355; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
        .input-wrapper { position: relative; }
        .input-field {
            width: 100%; padding: 12px 16px;
            border: 2px solid #E5D5C5; border-radius: 10px;
            background: white; font-family: inherit; font-size: 14px;
            transition: all 0.3s; color: #5D4037;
        }
        .input-field:focus {
            border-color: #8B7355; outline: none; box-shadow: 0 4px 12px rgba(139, 115, 85, 0.15);
        }
        
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        
        .btn-submit {
            width: 100%; padding: 14px;
            background: linear-gradient(135deg, #8B7355 0%, #6D5B45 100%);
            color: white; border: none; border-radius: 12px;
            font-weight: 600; font-size: 16px; cursor: pointer;
            transition: 0.3s; margin-top: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(139, 115, 85, 0.3); }
        
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
            .left-panel { flex: 0.3; min-height: 200px; padding: 20px; }
            .brand-img { width: 80px; }
            .brand-title { font-size: 24px; }
            .right-panel { flex: 0.7; padding: 30px; display: block; overflow-y: auto; }
            .login-wrapper { max-width: 100%; margin: 0 auto; }
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
                <h1 class="brand-title">BUAT AKUN</h1>
                <p class="brand-desc">Bergabunglah dan temukan tempat tinggal nyaman di Solo Raya.</p>
            </div>
        </div>
        
        <!-- Form Side -->
        <div class="right-panel">
            <div class="login-wrapper">
                <div class="form-header">
                    <h2>Registrasi Klien</h2>
                    <p>Isi data diri Anda dengan benar.</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <span>⚠ <?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <span>✅ <?php echo htmlspecialchars($success); ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="input-group">
                        <label>NAMA LENGKAP</label>
                        <input type="text" name="namaLengkap" class="input-field" placeholder="Contoh: Budi Santoso" value="<?php echo htmlspecialchars($input_nama); ?>" required>
                    </div>

                    <div class="grid-2">
                        <div class="input-group">
                            <label>EMAIL</label>
                            <input type="email" name="email" class="input-field" placeholder="nama@email.com" value="<?php echo htmlspecialchars($input_email); ?>" required>
                        </div>
                        <div class="input-group">
                            <label>WHATSAPP</label>
                            <input type="tel" name="whatsapp" class="input-field" placeholder="08xxxxxxxxxx" value="<?php echo htmlspecialchars($input_wa); ?>" required>
                        </div>
                    </div>
                    
                    <div class="grid-2">
                        <div class="input-group">
                            <label>PASSWORD</label>
                            <input type="password" name="password" id="pass" class="input-field" placeholder="Min. 8 Karakter" required>
                        </div>
                        <div class="input-group">
                            <label>KONFIRMASI</label>
                            <input type="password" name="confirmPassword" id="cpass" class="input-field" placeholder="Ulangi Password" required>
                        </div>
                    </div>

                    <p id="match-msg" style="font-size:12px; margin-top:-10px; margin-bottom:15px; height:15px;"></p>

                    <button type="submit" class="btn-submit">DAFTAR SEKARANG</button>
                    
                    <div class="auth-footer">
                         Sudah punya akun? <a href="login.php">Masuk disini</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        const pass = document.getElementById('pass');
        const cpass = document.getElementById('cpass');
        const msg = document.getElementById('match-msg');
        
        function check() {
            if(cpass.value.length > 0) {
                if(pass.value !== cpass.value) {
                    msg.style.color = '#D32F2F';
                    msg.innerText = 'Password tidak cocok ❌';
                    cpass.style.borderColor = '#D32F2F';
                } else {
                    msg.style.color = '#2E7D32';
                    msg.innerText = 'Password cocok ✅';
                    cpass.style.borderColor = '#2E7D32';
                }
            } else {
                msg.innerText = '';
                cpass.style.borderColor = '#E5D5C5';
            }
        }
        
        pass.addEventListener('input', check);
        cpass.addEventListener('input', check);
    </script>
</body>
</html>
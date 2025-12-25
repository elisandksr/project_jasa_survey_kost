<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jasa Survey Kost Solo - Cepat & Terpercaya</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #8B7355;
            --primary-dark: #6D5B45;
            --secondary: #9F8A6E;
            --bg-light: #FFFCF9;
            --text-dark: #4A3B32;
            --text-light: #8D7F75;
            --white: #FFFFFF;
            --gradient: linear-gradient(135deg, #8B7355 0%, #6D5B45 100%);
        }

        * { margin:0; padding:0; box-sizing:border-box; }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
            overflow-x: hidden;
        }

        a { text-decoration: none; color: inherit; transition: 0.3s; }
        ul { list-style: none; }

        /* --- NAVBAR --- */
        .navbar {
            display: flex; justify-content: space-between; align-items: center;
            padding: 20px 5%;
            background: rgba(255, 252, 249, 0.95);
            backdrop-filter: blur(10px);
            position: fixed; top: 0; width: 100%; z-index: 1000;
            box-shadow: 0 2px 20px rgba(0,0,0,0.03);
        }

        .nav-logo {
            font-size: 24px; font-weight: 800; color: var(--primary);
            display: flex; align-items: center; gap: 10px;
        }
        
        /* Removed Nav Links as requested */
        /*.nav-menu { display: flex; gap: 30px; align-items: center; }*/

        .nav-auth { margin-left: auto; display: flex; align-items: center; }

        .btn-nav {
            padding: 10px 24px;
            background: var(--primary);
            color: var(--white);
            border-radius: 30px;
            font-weight: 600;
            font-size: 14px;
        }
        .btn-nav:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(139, 115, 85, 0.2);
        }

        .btn-outline {
            padding: 8px 22px;
            border: 2px solid var(--primary);
            color: var(--primary);
            border-radius: 30px;
            font-weight: 600;
            font-size: 14px;
            margin-right: 10px;
        }
        .btn-outline:hover {
            background: var(--primary); color: var(--white);
        }

        /* --- HERO SECTION --- */
        .hero {
            padding: 140px 5% 80px 5%;
            display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center;
            min-height: 100vh;
            background: radial-gradient(circle at top right, rgba(139, 115, 85, 0.05), transparent 60%);
        }

        .hero-content h1 {
            font-size: 56px; line-height: 1.1; font-weight: 800; color: var(--text-dark);
            margin-bottom: 20px;
        }

        .hero-content p {
            font-size: 18px; color: var(--text-light); line-height: 1.6;
            margin-bottom: 40px; max-width: 500px;
        }

        .hero-btns { display: flex; gap: 15px; }

        .btn-lg {
            padding: 16px 36px; font-size: 16px; border-radius: 50px;
            display: inline-flex; align-items: center; gap: 10px;
            font-weight: 600; cursor: pointer; border: none;
        }
        .btn-primary { background: var(--gradient); color: white; box-shadow: 0 10px 30px rgba(139, 115, 85, 0.25); }
        .btn-primary:hover { transform: translateY(-3px); box-shadow: 0 15px 40px rgba(139, 115, 85, 0.35); }

        .hero-image {
            position: relative;
        }
        .hero-img-box {
            width: 100%; 
            max-width: 500px; /* Enhanced size as requested ("perbesar sedikit") */
            margin: 0 auto;   /* Center it */
            border-radius: 30px; overflow: hidden;
            box-shadow: 20px 20px 0 var(--background-shade, #F0E6D2);
            /* Placeholder styling if no image */
            background: #EAE0D5; 
            display: flex; align-items: center; justify-content: center;
        }
        .hero-img-box img { width: 100%; height: auto; display: block; }
        
        .floating-card {
            position: absolute; background: white; padding: 20px;
            border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            display: flex; align-items: center; gap: 15px;
            bottom: 40px; left: -30px; animation: float 6s ease-in-out infinite;
        }
        @keyframes float { 0%{transform:translateY(0)} 50%{transform:translateY(-15px)} 100%{transform:translateY(0)} }

        .icon-box {
            width: 50px; height: 50px; background: #E8F5E9; color: #2E7D32;
            border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px;
        }

        /* --- FEATURES --- */
        .features { padding: 100px 5%; text-align: center; }
        .section-tag {
            color: var(--primary); font-weight: 700; letter-spacing: 2px; text-transform: uppercase; font-size: 12px;
            margin-bottom: 10px; display: block;
        }
        .section-title { font-size: 36px; margin-bottom: 60px; font-weight: 700; }
        
        .feature-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;
        }
        
        .feature-card {
            background: white; padding: 40px 30px; border-radius: 24px;
            text-align: left; transition: 0.3s; border: 1px solid rgba(139, 115, 85, 0.1);
        }
        .feature-card:hover {
            transform: translateY(-10px); box-shadow: 0 20px 40px rgba(139, 115, 85, 0.1);
            border-color: var(--primary);
        }
        
        .f-icon {
            width: 60px; height: 60px; background: #FFFCF9; border: 1px solid rgba(139, 115, 85, 0.2);
            border-radius: 16px; display: flex; align-items: center; justify-content: center;
            font-size: 28px; margin-bottom: 25px; color: var(--primary);
        }
        
        .feature-card h3 { font-size: 20px; margin-bottom: 15px; }
        .feature-card p { color: var(--text-light); line-height: 1.6; font-size: 15px; }

        /* --- FOOTER --- */
        footer {
            background: #2D2420; color: #F0E6D2; padding: 80px 5% 30px 5%;
        }
        .footer-grid {
            display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 50px; margin-bottom: 60px;
        }
        .footer-brand h2 { color: white; margin-bottom: 20px; }
        .footer-logo { display:flex; align-items:center; gap:10px; margin-bottom:20px; }
        .footer-logo img { width:40px; }
        .footer-brand p { opacity: 0.7; line-height: 1.6; max-width: 300px; }
        
        .footer-col h4 { color: white; margin-bottom: 25px; font-size: 18px; }
        .footer-links li { margin-bottom: 15px; }
        .footer-links a { opacity: 0.7; }
        .footer-links a:hover { opacity: 1; color: var(--primary); }

        .copyright {
            border-top: 1px solid rgba(255,255,255,0.1); padding-top: 30px;
            text-align: center; font-size: 14px; opacity: 0.6;
        }

        /* Responsive */
        @media(max-width: 900px) {
            .hero { grid-template-columns: 1fr; text-align: center; padding-top: 120px; }
            .hero-btns { justify-content: center; }
            .floating-card { left: 50%; transform: translateX(-50%); bottom: -20px; }
            .footer-grid { grid-template-columns: 1fr; gap: 40px; }
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-logo">
            <img src="logo1.png" alt="SurveyKost" style="height:40px;">
            <span>SurveyKost</span>
        </div>
        
        <!-- Links Removed -->
        <!-- <div class="nav-menu">...</div> -->

        <div class="nav-auth">
            <a href="login.php" class="btn-outline">Masuk</a>
            <a href="register.php" class="btn-nav">Daftar</a>
        </div>
    </nav>

    <!-- Hero -->
    <section class="hero">
        <div class="hero-content">
            <h1>Cari Kost di Solo Tanpa Ribet Survey</h1>
            <p>Hemat waktu & tenaga Anda. Tim kami siap survey lokasi, cek kondisi, dan memberikan laporan lengkap kondisi kost impian Anda secara akurat.</p>
            <div class="hero-btns">
                <a href="register.php" class="btn-lg btn-primary">Pesan Survey Sekarang ➜</a>
            </div>
            
            <div style="margin-top: 40px; display: flex; gap: 30px; align-items: center;">
                <div>
                    <h3 style="font-size: 28px; font-weight: 800; color: var(--primary);">Praktis</h3>
                    <p style="font-size: 13px; color: var(--text-light);">Hemat Waktu</p>
                </div>
                <div style="width: 1px; height: 40px; background: #E0D0C0;"></div>
                <div>
                    <h3 style="font-size: 28px; font-weight: 800; color: var(--primary);">Survey Cepat</h3>
                    <p style="font-size: 13px; color: var(--text-light);">Kost Tepat</p>
                </div>
            </div>
        </div>
        <div class="hero-image">
            <div class="hero-img-box">
                 <!-- Generated Image -->
                 <img src="hero_survey.png" alt="Ilustrasi Survey Kost Solo">
            </div>
            <div class="floating-card">
                <div class="icon-box">📍</div>
                <div>
                    <h5 style="margin: 0; font-size: 15px;">Area Solo Raya</h5>
                    <!-- Removed "Cover seluruh kota" -->
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="features" id="features">
        <span class="section-tag">MENGAPA MEMILIH KAMI</span>
        <h2 class="section-title">Solusi Cerdas Pencari Kost Luar Kota</h2>
        
        <div class="feature-grid">
            <div class="feature-card">
                <div class="f-icon">⚡</div>
                <h3>Hemat Waktu & Tenaga</h3>
                <p>Anda fokus kuliah atau kerja, biar tim kami yang turun ke lapangan mengecek kondisi kost incaran Anda.</p>
            </div>
            <div class="feature-card">
                <div class="f-icon">📸</div>
                <h3>Laporan Visual Lengkap</h3>
                <p>Dapatkan foto terbaru, video room tour, hingga cek sinyal dan kebersihan lingkungan sekitar kost.</p>
            </div>
            <div class="feature-card">
                <div class="f-icon">🛡️</div>
                <h3>Jujur & Transparan</h3>
                <p>Kami bekerja untuk Anda. Informasi yang kami berikan adalah kondisi real tanpa ditutup-tutupi oleh pemilik kost.</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-grid">
            <div class="footer-col footer-brand">
                 <div class="footer-logo">
                    <!-- Image removed as per user request ("kotakan putih") -->
                    <h3 style="color:white; margin:0;">SurveyKost</h3>
                 </div>
                <p>Jasa survey kost profesional pertama di Kota Solo. Kami menjadi mata Anda di lokasi.</p>
            </div>
            <div class="footer-col">
                <h4>Layanan</h4>
                <ul class="footer-links">
                    <li><a href="#">Paket Regular</a></li>
                    <li><a href="#">Paket Express</a></li>
                    <!-- Removed Syarat & Ketentuan -->
                </ul>
            </div>
            <div class="footer-col">
                <h4>Kontak</h4>
                <ul class="footer-links">
                    <li>📍 Kota Solo, Jawa Tengah</li>
                    <li>📧 admin@surveykost.id</li>
                    <li>📞 +62 812-3456-7890</li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Media Sosial</h4>
                <ul class="footer-links">
                    <li><a href="https://instagram.com/surveykost.solo">@surveykost.solo</a></li>
                    <li><a href="https://facebook.com/surveykost">Survey Kost Solo</a></li>
                    <li><a href="https://tiktok.com/@surveykost_id">@surveykost_id</a></li>
                </ul>
            </div>
        </div>
        <div class="copyright">
            &copy; 2025 Survey Kost Solo. All Rights Reserved.
        </div>
    </footer>

</body>
</html>

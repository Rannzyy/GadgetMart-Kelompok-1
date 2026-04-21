<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami - GadgetMart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ===== VARIABLES & RESET ===== */
        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --primary-light: #93c5fd;
            --primary-bg: #eff6ff;
            --secondary: #8b5cf6;
            --danger: #ef4444;
            --success: #10b981;
            --dark: #1e293b;
            --dark-light: #334155;
            --light: #f8fafc;
            --gray: #64748b;
            --gray-light: #e2e8f0;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e9eef5 100%);
            color: var(--dark);
            line-height: 1.6;
        }

        /* ===== HEADER & NAVIGATION ===== */
        header {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 2px solid var(--primary-light);
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 5%;
            max-width: 1400px;
            margin: 0 auto;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--primary);
            text-decoration: none;
            transition: var(--transition);
        }

        .logo:hover {
            transform: scale(1.02);
        }

        .logo i {
            font-size: 1.8rem;
            color: var(--primary);
        }

        .nav-links {
            display: flex;
            gap: 2rem;
        }

        .nav-links a {
            color: var(--dark);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            position: relative;
            padding: 0.5rem 0;
        }

        .nav-links a::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: var(--transition);
        }

        .nav-links a:hover::after,
        .nav-links a.active::after {
            width: 100%;
        }

        .nav-links a:hover {
            color: var(--primary);
        }

        .nav-links a.active {
            color: var(--primary);
            font-weight: 600;
        }

        /* ===== MAIN CONTENT ===== */
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, var(--primary-bg) 0%, #fff 100%);
            border-radius: 2rem;
            padding: 3rem 2rem;
            text-align: center;
            margin-bottom: 3rem;
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(59,130,246,0.05) 0%, transparent 70%);
            pointer-events: none;
        }

        .hero-section h1 {
            font-size: 3rem;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 1rem;
            position: relative;
        }

        .hero-section h1 span {
            color: var(--primary);
        }

        .hero-section p {
            color: var(--gray);
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.8;
        }

        /* ===== ABOUT SECTIONS ===== */
        .about-section {
            background: white;
            border-radius: 1.5rem;
            box-shadow: var(--shadow-md);
            padding: 2.5rem;
            margin-bottom: 2rem;
            transition: var(--transition);
            border: 1px solid var(--gray-light);
        }

        .about-section:hover {
            box-shadow: var(--shadow-xl);
            transform: translateY(-2px);
        }

        .about-section h2 {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 3px solid var(--primary-light);
            display: inline-block;
        }

        .about-section p {
            color: var(--gray);
            margin-bottom: 1rem;
            line-height: 1.8;
        }

        /* Values List */
        .values-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .value-card {
            background: var(--primary-bg);
            border-radius: 1rem;
            padding: 1.5rem;
            transition: var(--transition);
            border-left: 4px solid var(--primary);
        }

        .value-card:hover {
            transform: translateX(5px);
            box-shadow: var(--shadow-md);
        }

        .value-card i {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .value-card h3 {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .value-card p {
            font-size: 0.9rem;
            margin-bottom: 0;
        }

        /* ===== TEAM SECTION ===== */
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .team-member {
            background: var(--light);
            border-radius: 1.5rem;
            overflow: hidden;
            transition: var(--transition);
            text-align: center;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-light);
        }

        .team-member:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
            border-color: var(--primary-light);
        }

        .member-image {
            width: 100%;
            height: 280px;
            object-fit: cover;
            position: relative;
        }

        .member-info {
            padding: 1.5rem;
        }

        .member-name {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.25rem;
        }

        .member-role {
            color: var(--primary);
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 1rem;
        }

        .member-desc {
            color: var(--gray);
            font-size: 0.85rem;
            margin-bottom: 1.25rem;
            line-height: 1.6;
        }

        .social-links {
            display: flex;
            justify-content: center;
            gap: 0.75rem;
        }

        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            transition: var(--transition);
        }

        .social-links a:hover {
            background: var(--primary-dark);
            transform: scale(1.1);
            box-shadow: var(--shadow-md);
        }

        /* Stats Section */
        .stats-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }

        .stat-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            text-align: center;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--gray-light);
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
            border-color: var(--primary-light);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--primary);
            line-height: 1;
        }

        .stat-label {
            font-size: 0.85rem;
            color: var(--gray);
            margin-top: 0.5rem;
        }

        /* ===== FOOTER ===== */
        footer {
            background: linear-gradient(135deg, var(--dark) 0%, var(--dark-light) 100%);
            color: white;
            padding: 3rem 0 1.5rem;
            margin-top: 4rem;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        .footer-column h3 {
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 0.75rem;
        }

        .footer-column h3::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--primary);
            border-radius: 2px;
        }

        .footer-column p {
            color: #9ca3af;
            line-height: 1.7;
            font-size: 0.9rem;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 0.75rem;
        }

        .footer-links a {
            color: #9ca3af;
            text-decoration: none;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .footer-links a:hover {
            color: var(--primary);
            transform: translateX(5px);
        }

        .footer-links i {
            width: 20px;
        }

        .copyright {
            text-align: center;
            padding-top: 2rem;
            margin-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: #9ca3af;
            font-size: 0.85rem;
        }

        /* ===== RESPONSIVE DESIGN ===== */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 1rem;
                padding: 1rem;
            }

            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
                gap: 1rem;
            }

            .hero-section h1 {
                font-size: 2rem;
            }

            .hero-section p {
                font-size: 0.95rem;
            }

            .about-section {
                padding: 1.5rem;
            }

            .about-section h2 {
                font-size: 1.4rem;
            }

            .team-grid {
                grid-template-columns: 1fr;
            }

            .stats-section {
                grid-template-columns: repeat(2, 1fr);
            }

            .footer-content {
                gap: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0 1rem;
            }

            .stats-section {
                grid-template-columns: 1fr;
            }

            .values-list {
                grid-template-columns: 1fr;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .about-section, .team-member, .stat-card, .value-card {
            animation: fadeInUp 0.5s ease-out backwards;
        }

        .about-section:nth-child(1) { animation-delay: 0.1s; }
        .about-section:nth-child(2) { animation-delay: 0.2s; }
        .about-section:nth-child(3) { animation-delay: 0.3s; }
    </style>
</head>
<body>
    <!-- Header & Navigation -->
    <header>
        <nav class="navbar">
            <a href="index.php" class="logo">
                <i class="fas fa-laptop-code"></i>
                <span>GadgetMart</span>
            </a>

            <div class="nav-links">
                <a href="index.php">Beranda</a>
                <a href="index.php">Produk</a>
                <a href="tentang.php" class="active">Tentang Kami</a>
                <a href="kontak.php">Kontak</a>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <div class="container">
        <!-- Hero Section -->
        <div class="hero-section">
            <h1>Tentang <span>GadgetMart</span></h1>
            <p>Kami adalah tim passionate yang berdedikasi untuk menyediakan gadget terbaik dengan harga terjangkau untuk masyarakat Indonesia. Sejak 2020, kami telah melayani ribuan pelanggan dengan produk berkualitas dan layanan terbaik.</p>
        </div>

        <!-- Stats Section -->
        <div class="stats-section">
            <div class="stat-card">
                <div class="stat-number">5+</div>
                <div class="stat-label">Tahun Pengalaman</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">10K+</div>
                <div class="stat-label">Pelanggan Puas</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">500+</div>
                <div class="stat-label">Produk Terjual</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">24/7</div>
                <div class="stat-label">Layanan Pelanggan</div>
            </div>
        </div>

        <!-- Company Overview -->
        <div class="about-section">
            <h2>Visi & Misi Kami</h2>
            <p><strong>Visi:</strong> Menjadi platform terdepan dalam penjualan gadget premium di Indonesia yang dapat diakses oleh semua kalangan.</p>
            <p><strong>Misi:</strong> Menyediakan produk-produk gadget terbaru dan terbaik dengan jaminan keaslian 100%, harga kompetitif, dan layanan pelanggan yang exceptional. Kami berkomitmen untuk terus berinovasi dan memberikan pengalaman berbelanja yang mudah, aman, dan menyenangkan.</p>
        </div>

        <!-- Values Section -->
        <div class="about-section">
            <h2>Nilai-Nilai Kami</h2>
            <div class="values-list">
                <div class="value-card">
                    <i class="fas fa-handshake"></i>
                    <h3>Integritas</h3>
                    <p>Selalu jujur dan transparan dalam setiap transaksi dengan pelanggan</p>
                </div>
                <div class="value-card">
                    <i class="fas fa-lightbulb"></i>
                    <h3>Inovasi</h3>
                    <p>Terus mengembangkan layanan untuk pengalaman berbelanja yang lebih baik</p>
                </div>
                <div class="value-card">
                    <i class="fas fa-smile"></i>
                    <h3>Kepuasan Pelanggan</h3>
                    <p>Prioritas utama kami adalah kebahagiaan dan kepercayaan pelanggan</p>
                </div>
                <div class="value-card">
                    <i class="fas fa-gem"></i>
                    <h3>Kualitas</h3>
                    <p>Hanya menyediakan produk-produk berkualitas dengan garansi resmi</p>
                </div>
                <div class="value-card">
                    <i class="fas fa-users"></i>
                    <h3>Komunitas</h3>
                    <p>Membangun ekosistem yang saling mendukung bagi pecinta gadget</p>
                </div>
            </div>
        </div>

        <!-- Team Section -->
        <div class="about-section">
            <h2>Tim Kami</h2>
            <p>Di balik kesuksesan GadgetMart, terdapat tim yang solid dan berkomitmen untuk memberikan pengalaman berbelanja terbaik bagi pelanggan kami.</p>
            
            <div class="team-grid">
                <!-- Team Member 1 -->
                <div class="team-member">
                    <img src="images/foto gw.jpg" alt="Randi Andriansyah Ramadhan" class="member-image" onerror="this.src='https://via.placeholder.com/300x280?text=R+R'">
                    <div class="member-info">
                        <h3 class="member-name">Randi Andriansyah Ramadhan</h3>
                        <p class="member-role">CEO & Founder</p>
                        <p class="member-desc">Visionary leader dengan passion di bidang teknologi dan bisnis digital. Berpengalaman lebih dari 5 tahun di industri e-commerce.</p>
                        <div class="social-links">
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Team Member 2 -->
                <div class="team-member">
                    <img src="images/faizza.jpg" alt="Faizatul Sadiah" class="member-image" onerror="this.src='https://via.placeholder.com/300x280?text=F+S'">
                    <div class="member-info">
                        <h3 class="member-name">Faizatul Sadiah</h3>
                        <p class="member-role">Head of Marketing</p>
                        <p class="member-desc">Ahli strategi pemasaran digital dengan kemampuan analitis yang kuat. Bertanggung jawab atas pertumbuhan brand GadgetMart.</p>
                        <div class="social-links">
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Team Member 3 -->
                <div class="team-member">
                    <img src="images/diana.jpg" alt="Diana Fariskha Ramadhani" class="member-image" onerror="this.src='https://via.placeholder.com/300x280?text=D+F+R'">
                    <div class="member-info">
                        <h3 class="member-name">Diana Fariskha Ramadhani</h3>
                        <p class="member-role">CTO & Lead Developer</p>
                        <p class="member-desc">Spesialis pengembangan web dengan keahlian membangun platform e-commerce yang scalable dan user-friendly.</p>
                        <div class="social-links">
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#"><i class="fab fa-github"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-column">
                <h3>Tentang GadgetMart</h3>
                <p>Toko online terpercaya untuk laptop, smartphone, dan aksesoris gadget dengan garansi resmi dan harga terbaik.</p>
            </div>

            <div class="footer-column">
                <h3>Belanja</h3>
                <ul class="footer-links">
                    <li><a href="index.php?kategori=Laptop"><i class="fas fa-chevron-right"></i> Laptop</a></li>
                    <li><a href="index.php?kategori=HP"><i class="fas fa-chevron-right"></i> Smartphone</a></li>
                    <li><a href="index.php?kategori=Tablet"><i class="fas fa-chevron-right"></i> Tablet</a></li>
                    <li><a href="index.php?kategori=Aksesoris"><i class="fas fa-chevron-right"></i> Aksesoris</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3>Bantuan</h3>
                <ul class="footer-links">
                    <li><a href="#"><i class="fas fa-chevron-right"></i> Cara Belanja</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i> Pembayaran</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i> Pengembalian</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i> FAQ</a></li>
                </ul>
            </div>

            <div class="footer-column">
                <h3>Kontak</h3>
                <ul class="footer-links">
                    <li><a href="#"><i class="fas fa-phone-alt"></i> (021) 1234-5678</a></li>
                    <li><a href="#"><i class="fas fa-envelope"></i> cs@gadgetmart.id</a></li>
                    <li><a href="#"><i class="fas fa-map-marker-alt"></i> Malang, Indonesia</a></li>
                    <li><a href="#"><i class="fas fa-clock"></i> Buka 07:00 - 20:00 WIB</a></li>
                </ul>
            </div>
        </div>

        <div class="copyright">
            &copy; <?php echo date('Y'); ?> GadgetMart.id - All Rights Reserved
        </div>
    </footer>
</body>
</html>
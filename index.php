<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Corner Flag Arena - Football Field Booking</title>
    <link rel="icon" href="assets/logo.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="header-container">
            <a href="index.php" class="logo-wrapper">
                <img src="assets/logo.png" alt="Corner Flag Arena Logo">
                <div class="logo-text">
                    CORNER FLAG
                    <span>ARENA</span>
                </div>
            </a>
            <ul class="nav-links">
                <li><a href="index.php" class="active">Home</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['user_name']); ?>)</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php" class="btn-accent">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </header>

    <main class="container">
        <!-- Hero Section -->
        <section class="hero">
            <h1>Welcome to <span>Corner Flag Arena</span></h1>
            <p>Experience football on our state-of-the-art single turf. Book your preferred slot, upload your payment proof, and dominate the pitch!</p>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php" class="btn btn-yellow">Go to Dashboard</a>
            <?php else: ?>
                <a href="register.php" class="btn btn-yellow">Register Now</a>
                <a href="login.php" class="btn btn-primary" style="margin-left: 10px;">Book a Field</a>
            <?php endif; ?>
        </section>

        <!-- Features Section -->
        <section class="panel" style="border-top-color: var(--accent-yellow)">
            <h2 class="panel-title" style="border-bottom: none; text-align: center; display: block; margin-bottom: 2.5rem;">
                Why Choose Corner Flag Arena?
            </h2>
            <div class="dashboard-grid">
                <div class="kpi-card" style="border-left-color: var(--primary)">
                    <div class="kpi-info">
                        <h3 style="color: var(--primary)">Premium Turf</h3>
                        <p style="font-size: 0.9rem; color: var(--text-muted); margin-top: 5px;">High-grade artificial grass designed for optimal ball control and player safety.</p>
                    </div>
                    <div class="kpi-icon" style="color: var(--primary)">🏟️</div>
                </div>
                <div class="kpi-card" style="border-left-color: var(--accent-yellow)">
                    <div class="kpi-info">
                        <h3 style="color: var(--primary-dark)">Top Floodlights</h3>
                        <p style="font-size: 0.9rem; color: var(--text-muted); margin-top: 5px;">Bright, professional-grade LED lighting for perfect visibility during night matches.</p>
                    </div>
                    <div class="kpi-icon" style="color: var(--accent-yellow)">💡</div>
                </div>
                <div class="kpi-card" style="border-left-color: var(--accent-red)">
                    <div class="kpi-info">
                        <h3 style="color: var(--accent-red)">Easy Booking</h3>
                        <p style="font-size: 0.9rem; color: var(--text-muted); margin-top: 5px;">Reserve your slot online, upload proof of payment, and get immediate verification.</p>
                    </div>
                    <div class="kpi-icon" style="color: var(--accent-red)">📅</div>
                </div>
            </div>
        </section>

        <!-- Booking Hours Info -->
        <section class="panel">
            <h2 class="panel-title">Available Slots & Pricing</h2>
            <p style="margin-bottom: 1.5rem;">We offer premium high-demand time slots every day. Book in advance to secure your game:</p>
            <div class="slots-container" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                <div class="slot-option" style="cursor: default; border-color: var(--primary); background: #f0f7f3;">
                    4:00 PM – 5:00 PM
                    <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: normal; margin-top: 4px;">Afternoon Kick-off</div>
                </div>
                <div class="slot-option" style="cursor: default; border-color: var(--primary); background: #f0f7f3;">
                    5:00 PM – 6:00 PM
                    <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: normal; margin-top: 4px;">Golden Hour</div>
                </div>
                <div class="slot-option" style="cursor: default; border-color: var(--primary); background: #f0f7f3;">
                    6:00 PM – 7:00 PM
                    <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: normal; margin-top: 4px;">Twilight Clash</div>
                </div>
                <div class="slot-option" style="cursor: default; border-color: var(--primary); background: #f0f7f3;">
                    7:00 PM – 8:00 PM
                    <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: normal; margin-top: 4px;">Prime Time Lights</div>
                </div>
                <div class="slot-option" style="cursor: default; border-color: var(--primary); background: #f0f7f3;">
                    8:00 PM – 9:00 PM
                    <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: normal; margin-top: 4px;">Late Night Kick</div>
                </div>
                <div class="slot-option" style="cursor: default; border-color: var(--primary); background: #f0f7f3;">
                    9:00 PM – 10:00 PM
                    <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: normal; margin-top: 4px;">Midnight Lights</div>
                </div>
                <div class="slot-option" style="cursor: default; border-color: var(--primary); background: #f0f7f3;">
                    10:00 PM – 11:00 PM
                    <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: normal; margin-top: 4px;">Closing Clash</div>
                </div>
            </div>
        </section>
        <!-- Location Section -->
        <section class="panel" style="border-top-color: var(--primary)">
            <h2 class="panel-title">Location & Contact</h2>
            <div class="dashboard-layout" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
                <div>
                    <h3 style="color: var(--primary-dark); font-size: 1.25rem; margin-bottom: 0.5rem;">Where to Find Us</h3>
                    <p style="font-size: 1.05rem; font-weight: 600; color: var(--text-dark); margin-bottom: 1rem;">
                        📍 Corner Flag Arena, Badawa Layout, Kano
                    </p>
                    <p style="color: var(--text-muted); margin-bottom: 1.5rem;">
                        Our arena is conveniently located in Badawa Layout, Kano. We provide high-quality artificial turf, floodlights for night matches, and a secure environment.
                    </p>
                    <div style="display: flex; gap: 0.5rem; flex-direction: column;">
                        <div><span style="font-weight: 700; color: var(--primary-dark);">📞 Support:</span> <span style="color: var(--text-dark);">+234 806 139 9073</span></div>
                        <div><span style="font-weight: 700; color: var(--primary-dark);">⏰ Hours:</span> <span style="color: var(--text-dark);">4:00 PM – 8:00 PM Daily</span></div>
                    </div>
                </div>
                <div>
                    <div class="map-container">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3902.9734187211153!2d8.552945275060155!3d11.976378487339175!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x11ae79efab5e7b4b%3A0x2f6460fae24fb142!2sBadawa%20Layout%2C%20Kano!5e0!3m2!1sen!2sng!4v1718500000000!5m2!1sen!2sng" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Corner Flag Arena. All rights reserved.</p>
            <p style="margin-top: 10px; font-size: 0.8rem;">
                <a href="admin_login.php" style="color: var(--accent-yellow); text-decoration: underline;">Admin Portal</a>
            </p>
        </div>
    </footer>
</body>
</html>

<?php
session_start();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FindIt - Lost & Found</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }

        /* Navigation Bar */
        nav {
            position: fixed;
            top: 0;
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .nav-container {
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
            cursor: pointer;
            text-decoration: none;
        }

        .logo-image {
            height: 40px;
            width: auto;
            object-fit: contain;
            transition: transform 0.3s ease;
        }

        .logo:hover .logo-image {
            transform: scale(1.05);
        }

        .nav-menu {
            display: flex;
            gap: 0;
            list-style: none;
        }

        .nav-menu li a {
            text-decoration: none;
            color: #333;
            font-size: 1.1rem;
            font-weight: 600;
            padding: 1rem 2.5rem;
            display: block;
            transition: all 0.3s ease;
            border-radius: 12px;
        }

        .nav-menu li a.active {
            background: #0000ff;
            color: white;
        }

        .nav-menu li a:hover:not(.active) {
            background: #f0f0f0;
        }

        .auth-section {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-left: 1.5rem;
        }

        .auth-button {
            color: #0000ff;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .auth-button:hover {
            opacity: 0.7;
        }

        .auth-icon {
            width: 32px;
            height: 32px;
            background: #0000ff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .auth-icon:hover {
            background: #0000cc;
            transform: scale(1.05);
        }

        .user-menu {
            position: relative;
        }

        .user-button {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            color: #333;
            font-family: inherit;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .user-button:hover {
            opacity: 0.7;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            background: #0000ff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1rem;
        }

        .dropdown-menu {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            min-width: 180px;
            display: none;
            z-index: 1000;
            overflow: hidden;
        }

        .dropdown-menu.show {
            display: block;
            animation: slideDown 0.3s ease;
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

        .dropdown-item {
            padding: 0.8rem 1.2rem;
            color: #333;
            text-decoration: none;
            display: block;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }

        .dropdown-item:hover {
            background: #f5f5f5;
        }

        .dropdown-item.logout {
            color: #ff4444;
            border-top: 1px solid #f0f0f0;
        }

        .dropdown-item.logout:hover {
            background: #fff5f5;
        }

        /* Hero Section */
        .hero {
            position: relative;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            overflow: hidden;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .hero::before {
            content: "";
            position: absolute;
            width: 100%;
            height: 100%;
            background-image: 
                linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px),
                linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: gridMove 20s linear infinite;
        }

        @keyframes gridMove {
            0% {
                transform: translate(0, 0);
            }
            100% {
                transform: translate(50px, 50px);
            }
        }

        .cityscape {
            position: absolute;
            width: 100%;
            height: 100%;
            opacity: 0.3;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 400"><rect x="50" y="200" width="80" height="200" fill="%23fff"/><rect x="150" y="150" width="60" height="250" fill="%23fff"/><rect x="230" y="180" width="70" height="220" fill="%23fff"/><rect x="320" y="120" width="90" height="280" fill="%23fff"/><rect x="430" y="160" width="75" height="240" fill="%23fff"/><rect x="525" y="140" width="85" height="260" fill="%23fff"/><rect x="630" y="190" width="65" height="210" fill="%23fff"/><rect x="715" y="170" width="80" height="230" fill="%23fff"/><rect x="815" y="130" width="70" height="270" fill="%23fff"/></svg>') center bottom no-repeat;
            background-size: cover;
        }

        .hero-content {
            position: relative;
            z-index: 10;
            color: white;
            padding: 2rem;
            max-width: 800px;
        }

        .hero h1 {
            font-size: 2.5rem;
            margin-bottom: 2rem;
            text-shadow: 2px 2px 10px rgba(0, 0, 0, 0.3);
        }

        .find-button {
            background: #0000ff;
            color: white;
            border: none;
            padding: 1.2rem 4rem;
            font-size: 1.3rem;
            font-weight: 600;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(0, 0, 255, 0.4);
            margin-bottom: 2rem;
        }

        .find-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0, 0, 255, 0.6);
            background: #0000cc;
        }

        .find-button:active {
            transform: translateY(-1px);
        }

        .report-link {
            color: white;
            font-size: 1.1rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .report-link:hover {
            text-decoration: underline;
            opacity: 0.8;
        }

        .report-link a {
            color: #ffd700;
            font-weight: 600;
            text-decoration: none;
        }

        .report-link a:hover {
            text-decoration: underline;
        }

        /* Floating Animation */
        @keyframes float {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-20px);
            }
        }

        .hero-content {
            animation: float 6s ease-in-out infinite;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav-menu {
                flex-direction: column;
                position: absolute;
                top: 100%;
                left: 0;
                width: 100%;
                background: white;
                display: none;
            }

            .nav-menu.active {
                display: flex;
            }

            .hero h1 {
                font-size: 1.8rem;
            }

            .find-button {
                padding: 1rem 3rem;
                font-size: 1.1rem;
            }

            .logo-image {
                height: 40px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
<nav>
    <div class="nav-container">
        <a href="home.php" class="logo">
            <img src="logo.png" alt="FindIt Logo" class="logo-image">
        </a>

        <ul class="nav-menu">
            <li><a href="home.php" class="<?= basename($_SERVER['PHP_SELF']) == 'home.php' ? 'active' : '' ?>">Home</a></li>
            <li><a href="report.php">Report</a></li>
            <li><a href="find.php">Find</a></li>
            <li><a href="history.php">History</a></li>
        </ul>

        <div class="auth-section">
            <?php if (!isset($_SESSION['user_id'])): ?>
                <!-- User NOT logged in -->
                <a href="login.php" class="auth-button">
                    <span class="auth-icon">👤</span>
                    Login
                </a>

            <?php else: ?>
                <!-- User IS logged in -->
                <div class="user-menu">
                    <button class="user-button" onclick="toggleDropdown()">
                        <div class="user-avatar">
                            <?= strtoupper(substr($_SESSION['full_name'], 0, 2)) ?>
                        </div>
                    </button>

                    <div class="dropdown-menu" id="dropdownMenu">
                        <div class="dropdown-item" style="font-weight: bold; border-bottom: 1px solid #f0f0f0;">
                            <?= $_SESSION['full_name'] ?>
                        </div>
                        <a href="history.php" class="dropdown-item">My Reports</a>
                        <a href="logout.php" class="dropdown-item logout">Logout</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    </div>
</nav>

<script>
function toggleDropdown() {
    document.getElementById("dropdownMenu").classList.toggle("show");
}
</script>

    <!-- Hero Section -->
    <section class="hero">
        <div class="cityscape"></div>
        <div class="hero-content">
            <h1>Feeling like something is missing?</h1>
            <button class="find-button" onclick="location.href='find.php'">Find Now</button>
            <p class="report-link">
                Found something that doesn't belong to you? 
                <a href="report.php">Report</a>
            </p>
        </div>
    </section>

</body>
</html>
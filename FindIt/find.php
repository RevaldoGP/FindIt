<?php
// --- CONFIG DATABASE & SESSION ---
$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";
$DB_NAME = "findit"; 

$conn = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

session_start();

if (isset($_POST['submit_claim'])) {
    $reportId = intval($_POST['report_id']);
    $brand = mysqli_real_escape_string($conn, $_POST['claim_brand']);
    $desc  = mysqli_real_escape_string($conn, $_POST['claim_description']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $userId = $_SESSION['user_id'] ?? null;

    if ($userId) {
        $sql = "INSERT INTO claims (report_id, user_id, brand, description, phone, claim_at)
                VALUES ($reportId, $userId, '$brand', '$desc', '$phone', NOW())";

        mysqli_query($conn, $sql);
    }

    header("Location: find.php");
    exit;
}


// Handle logout via query param (so tidak perlu file terpisah)
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    // Clear session and redirect back to page
    session_unset();
    session_destroy();
    header("Location: find.php");
    exit;
}

// Ambil session info jika ada (sesuaikan nama variabel session jika berbeda)
$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$fullName = $isLoggedIn ? (isset($_SESSION['full_name']) ? $_SESSION['full_name'] : '') : null;

// Ambil semua laporan dengan status 'Valid' (urut terbaru)
$sql = "SELECT report_id, user_id, item_name, description, location, image_path, status, created_at 
        FROM reports 
        WHERE status = 'Valid'
        ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);

$items = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Find - FindIt</title>
    <style>
        /* ====== CSS (mengambil style dari file awal dan sedikit penyesuaian) ====== */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; min-height: 100vh; }

        /* Navigation Bar */
        nav { position: fixed; top: 0; width: 100%; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); box-shadow: 0 2px 20px rgba(0,0,0,0.1); z-index: 1000; }
        .nav-container { display:flex; justify-content:space-between; align-items:center; padding:1rem 5%; max-width:1400px; margin:0 auto; }
        .logo { display:flex; align-items:center; gap:0.5rem; cursor:pointer; text-decoration:none; }
        .logo-image { height:40px; width:auto; object-fit:contain; transition:transform 0.3s ease; }
        .logo:hover .logo-image { transform:scale(1.05); }
        .nav-menu { display:flex; gap:0; list-style:none; }
        .nav-menu li a { text-decoration:none; color:#333; font-size:1.1rem; font-weight:600; padding:1rem 2.5rem; display:block; transition:all 0.3s ease; border-radius:12px; }
        .nav-menu li a.active { background:#0000ff; color:white; }
        .nav-menu li a:hover:not(.active) { background:#f0f0f0; }
        .auth-section { display:flex; align-items:center; gap:0.5rem; margin-left:1.5rem; }
        .auth-button { color:#0000ff; text-decoration:none; font-weight:600; font-size:1rem; display:flex; align-items:center; gap:0.3rem; }
        .auth-button:hover { opacity:0.8; }
        .auth-icon { width:32px; height:32px; background:#0000ff; border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; font-size:1rem; cursor:pointer; transition:all 0.3s ease; }
        .auth-icon:hover { background:#0000cc; transform:scale(1.05); }

        .user-menu { position:relative; }
        .user-button { display:flex; align-items:center; gap:0.5rem; background:none; border:none; cursor:pointer; font-size:1rem; color:#333; font-family:inherit; font-weight:600; }
        .user-avatar { width:36px; height:36px; background:#0000ff; border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; font-weight:bold; font-size:1rem; }

        .dropdown-menu { position:absolute; top:calc(100% + 10px); right:0; background:white; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,0.15); min-width:180px; display:none; z-index:1000; overflow:hidden; }
        .dropdown-menu.show { display:block; animation:slideDown 0.3s ease; }
        @keyframes slideDown { from { opacity:0; transform:translateY(-10px); } to { opacity:1; transform:translateY(0); } }
        .dropdown-item { padding:0.8rem 1.2rem; color:#333; text-decoration:none; display:block; transition:all 0.3s ease; font-size:0.95rem; }
        .dropdown-item:hover { background:#f5f5f5; }
        .dropdown-item.logout { color:#ff4444; border-top:1px solid #f0f0f0; }
        .dropdown-item.logout:hover { background:#fff5f5; }

        /* Main Content */
        .main-content { padding-top:120px; padding-bottom:120px; max-width:1400px; margin:0 auto; padding-left:2rem; padding-right:2rem; }

        /* Search Bar */
        .search-container { max-width:800px; margin:0 auto 4rem; position:relative; }
        .search-bar { width:100%; padding:1.2rem 1.5rem 1.2rem 4rem; font-size:1.1rem; border:none; border-radius:50px; background:#d9d9d9; color:#666; transition:all 0.3s ease; }
        .search-bar::placeholder { color:#999; }
        .search-bar:focus { outline:none; background:#e5e5e5; box-shadow:0 0 0 3px rgba(0,0,255,0.1); }
        .search-icon { position:absolute; left:1.5rem; top:50%; transform:translateY(-50%); font-size:1.5rem; color:#666; }

        /* Items Grid */
        .items-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(350px, 1fr)); gap:2rem; margin-bottom:3rem; }
        .item-card { background:#d9d9d9; border-radius:25px; padding:1.5rem; transition:all 0.3s ease; cursor:pointer; display:flex; flex-direction:column; }
        .item-card:hover { transform:translateY(-5px); box-shadow:0 10px 30px rgba(0,0,0,0.15); }
        .item-title { font-size:1.5rem; font-weight:bold; color:#333; margin-bottom:0.5rem; }
        .item-location { font-size:0.95rem; color:#666; margin-bottom:0.8rem; }
        .item-description { font-size:1rem; color:#555; line-height:1.5; }

        /* small meta row */
        .meta-row { display:flex; justify-content:space-between; align-items:center; gap:1rem; margin-top:12px; flex-wrap:wrap; }
        .meta-left { font-size:0.9rem; color:#555; }
        .meta-right { font-size:0.85rem; color:#777; }

        /* Footer */
        .footer { position:fixed; bottom:0; width:100%; background:#0000ff; color:white; text-align:center; padding:1.5rem; }
        .footer h3 { font-size:1.5rem; margin-bottom:0.5rem; }
        .footer p { font-size:1.8rem; font-weight:bold; letter-spacing:1px; }

        /* Empty State */
        .empty-state { text-align:center; padding:4rem 2rem; color:#999; grid-column:1 / -1; }
        .empty-state-icon { font-size:4rem; margin-bottom:1rem; }
        .empty-state-text { font-size:1.2rem; }

        /* Responsive */
        @media (max-width:768px) {
            .nav-menu { flex-wrap:wrap; justify-content:center; gap:0.5rem; }
            .nav-menu li a { padding:0.8rem 1.5rem; font-size:1rem; }
            .main-content { padding-top:180px; }
            .items-grid { grid-template-columns:1fr; }
            .logo-image { height:40px; }
        }
        @media (max-width:480px) {
            .nav-container { flex-direction:column; gap:1rem; }
            .nav-menu { width:100%; }
            .nav-menu li { flex:1; }
            .nav-menu li a { text-align:center; padding:0.8rem; }
            .main-content { padding-top:220px; padding-left:1rem; padding-right:1rem; }
            .auth-section { margin-left:0; }
        }

    </style>
</head>
<body>
    <!-- Navigation -->
    <nav>
        <div class="nav-container">
            <a href="home.php" class="logo">
                <img src="logo.png" alt="FindIt Logo" class="logo-image" onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 200 50%22><text x=%2250%%22 y=%2250%%22 text-anchor=%22middle%22 font-size=%2220%22 fill=%22%23333%22>FindIt</text></svg>'">
            </a>

            <ul class="nav-menu">
                <li><a href="home.php">Home</a></li>
                <li><a href="report.php">Report</a></li>
                <li><a href="find.php" class="active">Find</a></li>
                <li><a href="history.php">History</a></li>
            </ul>

            <div id="authSection">
                <?php if ($isLoggedIn): ?>
                    <div class="user-menu">
                        <button class="user-button" onclick="toggleDropdown()">
                            <div class="user-avatar"><?= htmlspecialchars(strtoupper(substr($fullName, 0, 2))) ?></div>
                        </button>
                        <div class="dropdown-menu" id="dropdownMenu">
                            <div class="dropdown-item" style="font-weight:bold; border-bottom:1px solid #f0f0f0;">
                                <?= htmlspecialchars($fullName) ?>
                            </div>
                            <a href="history.php" class="dropdown-item">My Reports</a>
                            <!-- Logout handled by query param -->
                            <a href="?action=logout" class="dropdown-item logout">Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="auth-section">
                        <a href="login.php" class="auth-button">
                            <span class="auth-icon">👤</span>
                            Login
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Search Bar -->
        <div class="search-container">
            <span class="search-icon">🔍</span>
            <input 
                type="text" 
                class="search-bar" 
                placeholder="Search by item, location, or description..."
                id="searchInput"
                oninput="filterItems()"
            >
        </div>

        <!-- Items Grid -->
        <div class="items-grid" id="itemsGrid">
            <?php if (count($items) === 0): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">🔭</div>
                    <p class="empty-state-text">No items found</p>
                </div>
            <?php else: ?>
                <?php foreach ($items as $idx => $row): 
                    // sanitize and prepare values
                    $report_id   = (int)$row['report_id'];
                    $item_name   = htmlspecialchars($row['item_name']);
                    $location    = htmlspecialchars($row['location']);
                    $description = nl2br(htmlspecialchars($row['description']));
                    $status      = htmlspecialchars($row['status']);
                    $created_at  = $row['created_at'] ? date("Y-m-d", strtotime($row['created_at'])) : '';
                    // handle image: if empty use placeholder. If relative path, keep as is.
                    $imgRaw = trim($row['image_path']);
                    if ($imgRaw === "" || $imgRaw === null) {
                        // placeholder data URI SVG - lightweight
                        $imgSrc = "data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 300'><rect fill='%23f0f0f0' width='400' height='300'/><text x='50%' y='50%' text-anchor='middle' fill='%23999' font-size='22' font-family='Arial'>No Image</text></svg>";
                    } else {
                        // If stored value looks like an absolute URL (starts with http), use it.
                        if (preg_match("/^https?:\\/\\//i", $imgRaw)) {
                            $imgSrc = $imgRaw;
                        } else {
                            // assume relative path to file in your project (e.g. uploads/...)
                            // Escape for HTML attribute
                            $imgSrc = htmlspecialchars($imgRaw);
                        }
                    }
                ?>
                    <div class="item-card" data-item="<?= strtolower($item_name . ' ' . $location . ' ' . strip_tags($description)) ?>" onclick="showItemDetail(<?= $report_id ?>)">
                        <h3 class="item-title"><?= $item_name ?></h3>
                        <p class="item-location"><strong>Location:</strong> <?= $location ?></p>
                        <p class="item-description"><?= $description ?></p>

                        <div class="meta-row">
                            <div class="meta-right">Date: <?= $created_at ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
    <!-- ===== POPUP FORM CLAIM ===== -->
<div id="claimModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index:2000;">
    <div style="width:90%; max-width:420px; background:white; padding:25px; border-radius:20px;">
        <h2>Klaim Barang</h2>
        <form method="POST">
            <input type="hidden" id="claim_report_id" name="report_id">

            <label>Nama Brand:</label>
            <input type="text" name="claim_brand" required style="width:100%; padding:10px; margin-bottom:10px;">

            <label>Deskripsi Detail:</label>
            <textarea name="claim_description" required style="width:100%; padding:10px; height:120px; margin-bottom:10px;"></textarea>

            <label>Nomor Telepon:</label>
            <input type="text" name="phone" required style="width:100%; padding:10px; margin-bottom:10px;">

            <button type="submit" name="submit_claim" style="width:100%; padding:12px; background:#0066ff; color:white; border:none; border-radius:10px;">
                Klaim Barang
            </button>

            <button type="button" onclick="closeClaimModal()" style="width:100%; padding:12px; background:#ccc; border:none; border-radius:10px; margin-top:10px;">
                Batal
            </button>
        </form>
    </div>
</div>

    <script>
        // Toggle dropdown for user menu
        function toggleDropdown() {
            const dropdown = document.getElementById('dropdownMenu');
            if (!dropdown) return;
            dropdown.classList.toggle('show');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const userMenu = document.querySelector('.user-menu');
            const dropdown = document.getElementById('dropdownMenu');
            if (!dropdown || !userMenu) return;
            if (!userMenu.contains(event.target)) {
                dropdown.classList.remove('show');
            }
        });

        // Filter items based on search input (client-side)
        function filterItems() {
            const q = document.getElementById('searchInput').value.trim().toLowerCase();
            const cards = document.querySelectorAll('.item-card');
            if (!cards) return;

            let visibleCount = 0;
            cards.forEach(card => {
                const data = (card.getAttribute('data-item') || '').toLowerCase();
                if (q === "" || data.indexOf(q) !== -1) {
                    card.style.display = 'flex';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            // show empty state when no visible
            const emptyStateHTML = document.querySelector('.empty-state');
            if (emptyStateHTML) {
                emptyStateHTML.style.display = (visibleCount === 0) ? 'block' : 'none';
            }
        }

        // Show quick detail (you can replace with modal if mau)
        function showItemDetail(reportId) {
            <?php if (!$isLoggedIn): ?>
        alert("Silakan login untuk melakukan klaim.");
        window.location.href = "login.php";
        return;
    <?php endif; ?>
    document.getElementById("claim_report_id").value = reportId;
    document.getElementById("claimModal").style.display = "flex";
}

function closeClaimModal() {
    document.getElementById("claimModal").style.display = "none";
}

        // Add nav shadow on scroll
        window.addEventListener('scroll', () => {
            const nav = document.querySelector('nav');
            if (!nav) return;
            if (window.scrollY > 50) {
                nav.style.boxShadow = '0 2px 30px rgba(0,0,0,0.2)';
            } else {
                nav.style.boxShadow = '0 2px 20px rgba(0,0,0,0.1)';
            }
        });
    </script>
</body>
</html>

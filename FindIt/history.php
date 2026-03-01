<?php
// history.php - single file (PHP + HTML + CSS + JS)
// Sesuaikan DB credentials bila perlu.

$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";
$DB_NAME = "findit"; // ganti jika DB name berbeda

// koneksi DB
$conn = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

session_start();

// Handle logout via query param (sama mekanisme seperti find.php)
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

// Pastikan user wajib login untuk halaman ini
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$fullName = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'User';

// Ambil laporan milik user (urut terbaru)
$sql = "SELECT report_id, user_id, item_name, description, location, image_path, status, created_at
        FROM reports
        WHERE user_id = ?
        ORDER BY created_at DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

$reports = [];
if ($res) {
    while ($r = mysqli_fetch_assoc($res)) {
        $reports[] = $r;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>History - FindIt</title>
<style>
/* ===== CSS (disederhanakan & konsisten dengan find.php) ===== */
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;background:#f5f5f5;min-height:100vh}

/* NAV */
nav{position:fixed;top:0;left:0;right:0;background:rgba(255,255,255,0.95);backdrop-filter:blur(8px);box-shadow:0 2px 20px rgba(0,0,0,0.08);z-index:1000}
.nav-container{max-width:1400px;margin:0 auto;padding:1rem 5%;display:flex;align-items:center;justify-content:space-between}
.logo-image{height:40px}
.nav-menu{display:flex;gap:0;list-style:none}
.nav-menu a{text-decoration:none;color:#333;font-weight:600;padding:0.9rem 1.8rem;border-radius:12px;display:block}
.nav-menu a.active{background:#0000ff;color:#fff}
.user-menu{position:relative}
.user-button{background:none;border:0;cursor:pointer;display:flex;align-items:center;gap:0.5rem;font-weight:600}
.user-avatar{width:36px;height:36px;border-radius:50%;background:#0000ff;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700}
.dropdown-menu{position:absolute;right:0;top:calc(100% + 10px);background:#fff;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,0.12);min-width:180px;display:none;overflow:hidden}
.dropdown-menu.show{display:block}
.dropdown-item{padding:0.8rem 1.2rem;color:#333;text-decoration:none;display:block}
.dropdown-item.logout{color:#ff4444;border-top:1px solid #f0f0f0}

/* MAIN */
.main-content{max-width:1400px;margin:0 auto;padding:140px 2rem 120px}
.page-title{font-size:2rem;margin-bottom:8px}
.page-sub{color:#666;margin-bottom:20px}

/* GRID */
.items-grid{display:grid;gap:2rem;grid-template-columns:repeat(auto-fit,minmax(350px,1fr))}
.item-card{background:#d9d9d9;border-radius:20px;padding:1.25rem;position:relative;transition:all .2s}
.item-card:hover{transform:translateY(-6px);box-shadow:0 12px 30px rgba(0,0,0,0.08)}
.item-image{width:100%;height:260px;border-radius:12px;overflow:hidden;background:#fff;margin-bottom:1rem}
.item-image img{width:100%;height:100%;object-fit:cover;display:block}
.item-title{font-size:1.25rem;font-weight:700;color:#222;margin-bottom:6px}
.item-location{color:#444;margin-bottom:6px}
.item-description{color:#333;line-height:1.4;margin-bottom:10px}

/* status badge / warna */
.status-badge{display:inline-block;padding:0.45rem 0.85rem;border-radius:999px;font-weight:700;font-size:0.9rem}
.status-pending{background:#FFC107;color:#111}
.status-rejected{background:#ff4444;color:#fff}
.status-valid{background:#4CAF50;color:#fff}
.status-claimed{background:#0097A7;color:#fff}

/* date */
.date-label{display:block;margin-top:8px;color:#666;font-size:0.92rem}

/* EMPTY */
.empty{padding:3rem;text-align:center;color:#777}

/* responsive tweaks */
@media (max-width:600px){.item-image{height:200px}}
</style>
</head>
<body>

<nav>
  <div class="nav-container">
    <a href="home.php" class="logo"><img src="logo.png" alt="FindIt" class="logo-image" onerror="this.onerror=null; this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 200 40%22><text x=%2250%%22 y=%2250%%22 text-anchor=%22middle%22 font-size=%2220%22 fill=%22%23333%22>FindIt</text></svg>'"></a>

    <ul class="nav-menu">
      <li><a href="home.php">Home</a></li>
      <li><a href="report.php">Report</a></li>
      <li><a href="find.php">Find</a></li>
      <li><a href="history.php" class="active">History</a></li>
    </ul>

    <div id="authSection">
      <div class="user-menu">
        <button class="user-button" onclick="toggleDropdown()">
          <div class="user-avatar"><?= htmlspecialchars(strtoupper(substr($fullName,0,2))) ?></div>
          <span style="display:none;"><?= htmlspecialchars($fullName) ?></span>
        </button>

        <div class="dropdown-menu" id="dropdownMenu">
          <div class="dropdown-item" style="font-weight:700;border-bottom:1px solid #f0f0f0;">
            <?= htmlspecialchars($fullName) ?>
          </div>
          <a href="history.php" class="dropdown-item">My Reports</a>
          <a href="?action=logout" class="dropdown-item logout">Logout</a>
        </div>
      </div>
    </div>
  </div>
</nav>

<main class="main-content">
  <h1 class="page-title">History</h1>
  <p class="page-sub">Recently reported items</p>

  <?php if (count($reports) === 0): ?>
    <div class="empty">
      <p>Belum ada laporan yang kamu buat.</p>
      <p style="margin-top:10px"><a href="report.php" style="background:#0000ff;color:#fff;padding:10px 18px;border-radius:10px;text-decoration:none">Buat Laporan</a></p>
    </div>
  <?php else: ?>
    <section class="items-grid" id="itemsGrid">
      <?php foreach ($reports as $r): 
        // ambil & sanitasi
        $report_id = (int)$r['report_id'];
        $item_name = htmlspecialchars($r['item_name']);
        $location = htmlspecialchars($r['location']);
        $description = nl2br(htmlspecialchars($r['description']));
        $status_raw = trim($r['status']);
        $created_at_raw = $r['created_at'];

        // normalisasi status (case-insensitive) -> gunakan kelas CSS
        $sLower = strtolower($status_raw);
        if ($sLower === 'pending') $statusClass = 'status-pending';
        elseif ($sLower === 'rejected') $statusClass = 'status-rejected';
        elseif ($sLower === 'valid') $statusClass = 'status-valid';
        elseif ($sLower === 'claimed') $statusClass = 'status-claimed';
        else $statusClass = 'status-pending';

        // tanggal (hanya tanggal saja)
        $dateOnly = $created_at_raw ? date('Y-m-d', strtotime($created_at_raw)) : '';

        // image
        $imgRaw = trim($r['image_path']);
        if ($imgRaw === "" || $imgRaw === null) {
            $imgSrc = "data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 300'><rect fill='%23f0f0f0' width='400' height='300'/><text x='50%' y='50%' text-anchor='middle' fill='%23999' font-size='22' font-family='Arial'>No Image</text></svg>";
        } else {
            // jika path dimulai dengan http gunakan as-is, jika tidak treat sebagai relatif
            if (preg_match("/^https?:\\/\\//i", $imgRaw)) {
                $imgSrc = htmlspecialchars($imgRaw);
            } else {
                $imgSrc = htmlspecialchars($imgRaw);
            }
        }
      ?>
        <article class="item-card" data-report-id="<?= $report_id ?>">
          <div class="item-image">
            <img src="<?= $imgSrc ?>" alt="<?= $item_name ?>" onerror="this.onerror=null; this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 400 300%22><rect fill=%22%23f0f0f0%22 width=%22400%22 height=%22300%22/><text x=%2250%%22 y=%2250%%22 text-anchor=%22middle%22 fill=%22%23999%22 font-size=%2224%22 font-family=%22Arial%22>No Image</text></svg>'">
          </div>

          <h3 class="item-title"><?= $item_name ?></h3>
          <p class="item-location"><strong>Lokasi:</strong> <?= $location ?></p>
          <p class="item-description"><?= $description ?></p>

          <div style="margin-top:8px;">
            <span class="status-badge <?= $statusClass ?>"><?= htmlspecialchars(ucfirst($status_raw)) ?></span>
            <span class="date-label">Dilaporkan: <?= $dateOnly ?></span>
          </div>
        </article>
      <?php endforeach; ?>
    </section>
  <?php endif; ?>
</main>

<script>
// Dropdown behavior (sama dengan find.php)
function toggleDropdown(){
  const dropdown = document.getElementById('dropdownMenu');
  if (!dropdown) return;
  dropdown.classList.toggle('show');
}
document.addEventListener('click', function(e){
  const userMenu = document.querySelector('.user-menu');
  const dropdown = document.getElementById('dropdownMenu');
  if (!dropdown || !userMenu) return;
  if (!userMenu.contains(e.target)) dropdown.classList.remove('show');
});

// Shadow on scroll (visual)
window.addEventListener('scroll', () => {
  const nav = document.querySelector('nav');
  if (!nav) return;
  nav.style.boxShadow = window.scrollY > 50 ? '0 2px 30px rgba(0,0,0,0.2)' : '0 2px 20px rgba(0,0,0,0.08)';
});
</script>

</body>
</html>

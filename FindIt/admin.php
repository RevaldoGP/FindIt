<?php
session_start();
require "db.php";

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: logmin.php");
    exit;
}

$query = $conn->query("SELECT * FROM reports ORDER BY report_id DESC");
$reports = $query->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Admin Panel - FindIt</title>

<style>
body { font-family: Arial, sans-serif; background:#f5f5f5; }
nav {
    background:white;
    padding:1rem 5%;
    box-shadow:0 2px 20px rgba(0,0,0,0.1);
    position:fixed;
    width:100%;
    top:0;
}
.logout {
    float:right;
    background:red;
    color:white;
    padding:8px 25px;
    border-radius:8px;
    text-decoration:none;
    font-weight:bold;
    margin-right:100px
}
.container { padding-top:120px; width:90%; margin:auto; }
.grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(350px,1fr)); gap:20px; }
.card {
    background:#d9d9d9;
    padding:1.2rem;
    border-radius:20px;
}

.card img {
    width:100%;
    height:350px;
    object-fit:cover;
    border-radius:12px;
    margin-bottom:10px;
}

button {
    padding:8px 14px;
    border:none;
    border-radius:8px;
    color:white;
    font-weight:bold;
    cursor:pointer;
    margin-right:5px;
}
.valid { background:#4CAF50; }
.reject { background:#e53935; }
.claimed { background:#ff9800; }

/* Slider tampilan klaim */
.claim-slider {
    margin-top:20px;
    padding:12px;
    background:white;
    border-radius:12px;
    text-align:center;
    position:relative;
}

.slide-box {
    background:#f1f1f1;
    padding:15px;
    border-radius:10px;
    min-height:150px;
}

.arrow {
    position:absolute;
    top:50%;
    transform:translateY(-50%);
    font-size:25px;
    font-weight:bold;
    cursor:pointer;
    padding:5px 10px;
    user-select:none;
}

.arrow-left { left:10px; }
.arrow-right { right:10px; }

</style>
</head>

<body>

<nav>
    <h2 style="display:inline;">Admin Panel - FindIt</h2>
    <a href="logout_admin.php" class="logout">Logout</a>
</nav>

<div class="container">
    <h1>Semua Laporan</h1>

    <div class="grid">
        <?php foreach ($reports as $r): ?>
        <div class="card">
            <img src="<?= $r['image_path'] ?>">

            <h2><?= $r['item_name'] ?></h2>
            <p><b>Lokasi:</b> <?= $r['location'] ?></p>
            <p><b>Deskripsi:</b> <?= $r['description'] ?></p>
            <p><b>Status:</b> <?= $r['status'] ?></p>
            <p><b>Dibuat:</b> <?= $r['created_at'] ?></p>

            <form action="update_status.php" method="POST" style="margin-top:15px;">
                <input type="hidden" name="report_id" value="<?= $r['report_id'] ?>">

                <button class="valid" name="status" value="Valid">Valid</button>
                <button class="reject" name="status" value="Rejected">Reject</button>
                <button class="claimed" name="status" value="Claimed">Claimed</button>
            </form>

            <?php
            $claimQuery = $conn->query("
                SELECT c.*, u.full_name 
                FROM claims c
                JOIN users u ON c.user_id = u.user_id
                WHERE c.report_id = " . $r['report_id'] . "
                ORDER BY c.claim_at DESC
            ");
            $claims = $claimQuery->fetch_all(MYSQLI_ASSOC);
            ?>

            <?php if (count($claims) > 0): ?>
            <div class="claim-slider" id="slider-<?= $r['report_id'] ?>">
                <div class="arrow arrow-left" onclick="prevClaim(<?= $r['report_id'] ?>)">‹</div>
                <div class="arrow arrow-right" onclick="nextClaim(<?= $r['report_id'] ?>)">›</div>

                <div class="slide-box" id="slide-box-<?= $r['report_id'] ?>">
                    <!-- JS akan memasukkan klaim di sini -->
                </div>
            </div>

            <script>
                let claims<?= $r['report_id'] ?> = <?= json_encode($claims) ?>;
                let index<?= $r['report_id'] ?> = 0;

                function renderClaim<?= $r['report_id'] ?>() {
                    let c = claims<?= $r['report_id'] ?>[index<?= $r['report_id'] ?>];
                    document.getElementById("slide-box-<?= $r['report_id'] ?>").innerHTML = `
                        <p><b>Nama Pengklaim:</b> ${c.full_name}</p>
                        <p><b>Nomor Telepon:</b> ${c.phone}</p>
                        <p><b>Brand:</b> ${c.brand}</p>
                        <p><b>Deskripsi:</b> ${c.description}</p>
                        <p><b>Waktu Klaim:</b> ${c.claim_at}</p>
                    `;
                }

                function nextClaim(id) {
                    index<?= $r['report_id'] ?>++;
                    if (index<?= $r['report_id'] ?> >= claims<?= $r['report_id'] ?>.length) {
                        index<?= $r['report_id'] ?> = 0;
                    }
                    renderClaim<?= $r['report_id'] ?>();
                }

                function prevClaim(id) {
                    index<?= $r['report_id'] ?>--;
                    if (index<?= $r['report_id'] ?> < 0) {
                        index<?= $r['report_id'] ?> = claims<?= $r['report_id'] ?>.length - 1;
                    }
                    renderClaim<?= $r['report_id'] ?>();
                }

                renderClaim<?= $r['report_id'] ?>();
            </script>
            <?php endif; ?>

        </div>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>
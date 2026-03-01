<?php
session_start();
require_once "db.php";

// Pastikan user login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['report_error'] = "Anda harus login terlebih dahulu untuk melaporkan barang.";
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: report.php");
    exit;
}

// Ambil data form
$user_id    = intval($_SESSION['user_id']);
$item_name  = isset($_POST['item_name']) ? trim($_POST['item_name']) : '';
$description= isset($_POST['description']) ? trim($_POST['description']) : '';
$location   = isset($_POST['location']) ? trim($_POST['location']) : '';

// Validasi sederhana
if ($item_name === '' || $description === '' || $location === '') {
    $_SESSION['report_error'] = "Semua field harus diisi.";
    header("Location: report.php");
    exit;
}

// Handle upload image (opsional)
$image_path = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['image'];

    // validasi error upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['report_error'] = "Terjadi kesalahan saat meng-upload gambar.";
        header("Location: report.php");
        exit;
    }

    // batas ukuran 5MB
    if ($file['size'] > 5 * 1024 * 1024) {
        $_SESSION['report_error'] = "Ukuran gambar maksimal 5MB.";
        header("Location: report.php");
        exit;
    }

    // validasi tipe
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
    if (!array_key_exists($mime, $allowed)) {
        $_SESSION['report_error'] = "Tipe file tidak didukung. Gunakan JPG, PNG, atau GIF.";
        header("Location: report.php");
        exit;
    }

    // generate nama file aman
    $ext = $allowed[$mime];
    $newName = sprintf('%s_%s.%s', time(), bin2hex(random_bytes(6)), $ext);

    // folder uploads (pastikan ada dan writable)
    $uploadDir = __DIR__ . '/uploads';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $destination = $uploadDir . '/' . $newName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        $_SESSION['report_error'] = "Gagal menyimpan gambar.";
        header("Location: report.php");
        exit;
    }

    // simpan path relatif (untuk digunakan di UI)
    $image_path = 'uploads/' . $newName;
}

// Insert ke database
$stmt = $conn->prepare("INSERT INTO reports (user_id, item_name, description, location, image_path, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
if (!$stmt) {
    $_SESSION['report_error'] = "Query error: " . $conn->error;
    header("Location: report.php");
    exit;
}
$stmt->bind_param("issss", $user_id, $item_name, $description, $location, $image_path);
$ok = $stmt->execute();

if ($ok) {
    $_SESSION['report_success'] = "Laporan berhasil dikirim dan menunggu validasi satpam.";
    header("Location: report.php");
    exit;
} else {
    $_SESSION['report_error'] = "Gagal menyimpan laporan: " . $stmt->error;
    header("Location: report.php");
    exit;
}

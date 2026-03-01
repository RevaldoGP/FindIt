<?php
session_start();
require "db.php";

// Jika admin sudah login
if (isset($_SESSION['admin_logged_in'])) {
    header("Location: admin.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Login ADMIN, tabel khusus admin
    $query = $conn->prepare("SELECT * FROM admin WHERE username = ? LIMIT 1");
    $query->bind_param("s", $username);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();

        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['admin_id'];
            header("Location: admin.php");
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Admin tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Admin Login</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background:#f5f5f5;
        display:flex;
        justify-content:center;
        align-items:center;
        height:100vh;
    }
    .form-box {
        background:white;
        padding:2rem;
        width:350px;
        border-radius:15px;
        box-shadow:0 2px 15px rgba(0,0,0,0.2);
    }
    input {
        width:100%;
        padding:10px;
        margin-top:10px;
        border:1px solid #ccc;
        border-radius:8px;
    }
    button {
        margin-top:15px;
        width:100%;
        padding:10px;
        border:none;
        background:black;
        color:white;
        border-radius:8px;
        font-weight:bold;
    }
    .error {
        color:red;
        margin-top:10px;
        text-align:center;
    }
</style>
</head>
<body>

<div class="form-box">
    <h2>Admin Login</h2>

    <?php if ($error): ?>
        <p class="error"><?= $error ?></p>
    <?php endif; ?>

    <form action="" method="POST">
        <input type="text" name="username" placeholder="Username admin" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Masuk</button>
    </form>
</div>

</body>
</html>

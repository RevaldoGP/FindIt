<?php 
session_start();
require "db.php"; // koneksi database

// Jika tombol signup ditekan
if (isset($_POST['signup_btn'])) {

    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $nim = mysqli_real_escape_string($conn, $_POST['nim']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Cek email atau NIM sudah terdaftar
    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' OR nim='$nim'");
    if (mysqli_num_rows($check) > 0) {
        $_SESSION['signup_error'] = "Email atau NIM sudah terdaftar!";
        header("Location: signup.php");
        exit();
    }

    // Insert user baru
    $insert = mysqli_query($conn, "
        INSERT INTO users (full_name, nim, email, password)
        VALUES ('$full_name', '$nim', '$email', '$password_hash')
    ");

    if ($insert) {

        // Ambil data user setelah insert untuk dimasukkan ke session
        $getUser = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
        $user = mysqli_fetch_assoc($getUser);

        // AUTO LOGIN (SET SESSION)
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['nim'] = $user['nim'];
        $_SESSION['logged_in'] = true;

        // Redirect ke halaman home
        header("Location: home.php");
        exit();
    } else {
        $_SESSION['signup_error'] = "Terjadi kesalahan saat registrasi.";
        header("Location: signup.php");
        exit();
    }
}
?>

<!DOCTYPE html> 
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - FindIt</title>
    <style>
        /* ——————————— YOUR ORIGINAL CSS, UNTOUCHED ——————————— */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; min-height: 100vh; display: flex; position: relative; }
        .back-button { position: fixed; top: 2rem; left: 2rem; z-index: 1000; background: rgba(255, 255, 255, 0.95); width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1); text-decoration: none; color: #333; font-size: 1.5rem; }
        .back-button:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15); background: white; }
        .back-button::before { content: "←"; }
        .left-section { flex: 1; background: linear-gradient(135deg, #715ab7 0%, #6779e2 100%); display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 3rem; position: relative; overflow: hidden; }
        .left-section::before { content: ""; position: absolute; width: 300px; height: 300px; background: rgba(255, 255, 255, 0.1); border-radius: 50%; top: -100px; left: -100px; }
        .left-section::after { content: ""; position: absolute; width: 400px; height: 400px; background: rgba(255, 255, 255, 0.1); border-radius: 50%; bottom: -150px; right: -150px; }
        .brand-logo { margin-bottom: 3rem; z-index: 10; }
        .brand-logo-image { height: 65px; width: auto; margin-left: 130px; object-fit: contain; filter: drop-shadow(0 10px 30px rgba(0, 0, 0, 0.2)); }
        .illustration { width: 100%; max-width: 400px; margin-bottom: 3rem; z-index: 10; }
        .illustration-card { background: white; border-radius: 25px; padding: 2rem; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2); position: relative; }
        .illustration-icon { position: absolute; top: -30px; left: 30px; width: 80px; height: 80px; background: #0000ff; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; box-shadow: 0 10px 30px rgba(0, 0, 255, 0.3); }
        .illustration-content { margin-top: 3rem; display: flex; flex-direction: column; gap: 1rem; }
        .illustration-item { display: flex; gap: 1rem; align-items: center; }
        .item-icon { width: 40px; height: 40px; background: #f0f0f0; border-radius: 8px; }
        .item-line { flex: 1; height: 10px; background: #f0f0f0; border-radius: 5px; }
        .item-line.short { flex: 0.5; }
        .tagline { font-size: 1.5rem; color: #333; text-align: center; font-weight: 500; z-index: 10; max-width: 400px; }
        .right-section { flex: 1; background: #f8f8f8; display: flex; align-items: center; justify-content: center; padding: 3rem; }
        .signup-container { width: 100%; max-width: 450px; }
        .signup-header { margin-bottom: 3rem; }
        .signup-title { font-size: 2.5rem; font-weight: bold; color: #333; margin-bottom: 0.5rem; }
        .signup-subtitle { font-size: 1.1rem; color: #999; }
        .signup-form { display: flex; flex-direction: column; gap: 1.5rem; }
        .form-input { width: 100%; padding: 1.2rem 1.5rem; font-size: 1rem; border: none; border-radius: 50px; background: #d9d9d9; color: #333; transition: all 0.3s ease; }
        .form-input:focus { outline: none; background: #e5e5e5; box-shadow: 0 0 0 3px rgba(0, 0, 255, 0.1); }
        .signup-button { background: #0000ff; color: white; border: none; padding: 1.2rem; font-size: 1.2rem; font-weight: 600; border-radius: 50px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 10px 30px rgba(0, 0, 255, 0.3); margin-top: 1rem; }
        .signup-button:hover { transform: translateY(-3px); box-shadow: 0 15px 40px rgba(0, 0, 255, 0.5); background: #0000cc; }
        .login-link { text-align: center; margin-top: 2rem; color: #999; font-size: 1rem; }
        .login-link a { color: #0000ff; font-weight: 600; text-decoration: none; }
        .success-message, .error-message { display: none; padding: 1rem; border-radius: 10px; text-align: center; margin-bottom: 1rem; }
        .success-message { background: #4CAF50; color: white; }
        .error-message { background: #ff4444; color: white; }
    </style>
</head>
<body>

    <a href="login.php" class="back-button"></a>

    <div class="left-section">
        <div class="brand-logo">
            <img src="logo.png" class="brand-logo-image">
        </div>

        <div class="illustration">
            <div class="illustration-card">
                <div class="illustration-icon">📱</div>
                <div class="illustration-content">
                    <div class="illustration-item"><div class="item-icon"></div><div class="item-line"></div></div>
                    <div class="illustration-item"><div class="item-icon"></div><div class="item-line short"></div></div>
                    <div class="illustration-item"><div class="item-icon"></div><div class="item-line"></div></div>
                </div>
            </div>
        </div>
        
        <p class="tagline">Never lose track of your belongings on campus</p>
    </div>

    <div class="right-section">
        <div class="signup-container">

            <div class="signup-header">
                <h1 class="signup-title">Sign up</h1>
                <p class="signup-subtitle">Join us to report lost item</p>
            </div>

            <!-- PHP Error -->
            <?php if (isset($_SESSION['signup_error'])): ?>
                <div class="error-message" style="display:block;">
                    <?= $_SESSION['signup_error']; unset($_SESSION['signup_error']); ?>
                </div>
            <?php endif; ?>

            <!-- FORM -->
            <form class="signup-form" action="signup.php" method="POST">

                <input type="text" class="form-input" placeholder="Full Name" name="full_name" required>

                <input type="text" class="form-input" placeholder="NIM" name="nim" required>

                <input type="email" class="form-input" placeholder="Email" name="email" required>

                <input type="password" class="form-input" placeholder="Password" name="password" required minlength="6">

                <button type="submit" class="signup-button" name="signup_btn">Register</button>
            </form>

            <p class="login-link">
                Already have an account? <a href="login.php">Log in</a>
            </p>

        </div>
    </div>

</body>
</html>

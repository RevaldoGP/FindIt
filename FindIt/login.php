<?php 
session_start();
include "db.php";

// LOGIN PROCESS
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nim = $_POST['nim'];
    $password = $_POST['password'];

    // cek akun
    $query = mysqli_query($conn, "SELECT * FROM users WHERE nim='$nim'");
    
    if (mysqli_num_rows($query) === 1) {
        $data = mysqli_fetch_assoc($query);

        if (password_verify($password, $data['password'])) {

            // SET SESSION LOGIN
            $_SESSION['user_id'] = $data['user_id'];   // FIX!
            $_SESSION['full_name'] = $data['full_name'];
            $_SESSION['nim'] = $data['nim'];
            $_SESSION['email'] = $data['email'];

            header("Location: home.php");
            exit;
        } else {
            $_SESSION['login_error'] = "Wrong password!";
        }
    } else {
        $_SESSION['login_error'] = "Account not found!";
    }

    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FindIt</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            position: relative;
        }

        /* Back Button */
        .back-button {
            position: fixed;
            top: 2rem;
            left: 2rem;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.95);
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            text-decoration: none;
            color: #333;
            font-size: 1.5rem;
        }

        .back-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            background: white;
        }

        .back-button::before {
            content: "←";
        }

        /* Left Side - Branding */
        .left-section {
            flex: 1;
            background: linear-gradient(135deg, #715ab7 0%, #6779e2 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            position: relative;
            overflow: hidden;
        }

        .left-section::before {
            content: "";
            position: absolute;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -100px;
            left: -100px;
        }

        .left-section::after {
            content: "";
            position: absolute;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            bottom: -150px;
            right: -150px;
        }

        .brand-logo {
            margin-bottom: 3rem;
            z-index: 10;
        }

        .brand-logo-image {
            height: 65px;
            width: auto;
            margin-left: 130px;
            object-fit: contain;
            filter: drop-shadow(0 10px 30px rgba(0, 0, 0, 0.2));
        }

        .illustration {
            width: 100%;
            max-width: 400px;
            margin-bottom: 3rem;
            z-index: 10;
        }

        .illustration-card {
            background: white;
            border-radius: 25px;
            padding: 2rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        .illustration-icon {
            position: absolute;
            top: -30px;
            left: 30px;
            width: 80px;
            height: 80px;
            background: #0000cc;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 255, 0.3);
        }

        .illustration-content {
            margin-top: 3rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .illustration-item {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .item-icon {
            width: 40px;
            height: 40px;
            background: #f0f0f0;
            border-radius: 8px;
        }

        .item-line {
            flex: 1;
            height: 10px;
            background: #f0f0f0;
            border-radius: 5px;
        }

        .item-line.short {
            flex: 0.5;
        }

        .tagline {
            font-size: 1.5rem;
            color: #333;
            text-align: center;
            font-weight: 500;
            z-index: 10;
            max-width: 400px;
        }

        /* Right Side - Login Form */
        .right-section {
            flex: 1;
            background: #f8f8f8;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem;
        }

        .login-container {
            width: 100%;
            max-width: 450px;
        }

        .login-header {
            margin-bottom: 3rem;
        }

        .login-title {
            font-size: 2.5rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .login-subtitle {
            font-size: 1.1rem;
            color: #999;
        }

        .login-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .form-input {
            width: 100%;
            padding: 1.2rem 1.5rem;
            font-size: 1rem;
            border: none;
            border-radius: 50px;
            background: #d9d9d9;
            color: #333;
            transition: all 0.3s ease;
        }

        .form-input::placeholder {
            color: #999;
        }

        .form-input:focus {
            outline: none;
            background: #e5e5e5;
            box-shadow: 0 0 0 3px rgba(0, 0, 255, 0.1);
        }

        .login-button {
            background: #0000ff;
            color: white;
            border: none;
            padding: 1.2rem;
            font-size: 1.2rem;
            font-weight: 600;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(0, 0, 255, 0.3);
            margin-top: 1rem;
        }

        .login-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0, 0, 255, 0.5);
            background: #0000cc;
        }

        .login-button:active {
            transform: translateY(-1px);
        }

        .signup-link {
            text-align: center;
            margin-top: 2rem;
            color: #999;
            font-size: 1rem;
        }

        .signup-link a {
            color: #0000ff;
            font-weight: 600;
            text-decoration: none;
        }

        .signup-link a:hover {
            text-decoration: underline;
        }

        .error-message {
            background: #ff4444;
            color: white;
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
            display: none;
            animation: shake 0.5s;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        /* Responsive */
        @media (max-width: 968px) {
            body {
                flex-direction: column;
            }

            .left-section {
                min-height: 40vh;
                padding: 2rem;
            }

            .brand-logo-image {
                height: 60px;
            }

            .illustration {
                max-width: 300px;
                margin-bottom: 1.5rem;
            }

            .tagline {
                font-size: 1.2rem;
            }

            .right-section {
                padding: 2rem 1.5rem;
            }

            .login-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Back Button -->
    <a href="home.php" class="back-button" title="Back to Home"></a>

    <!-- Left Section - Branding -->
    <div class="left-section">
        <div class="brand-logo">
            <img src="logo.png" alt="FindIt Logo" class="brand-logo-image">
        </div>
        
        <div class="illustration">
            <div class="illustration-card">
                <div class="illustration-icon">📱</div>
                <div class="illustration-content">
                    <div class="illustration-item">
                        <div class="item-icon"></div>
                        <div class="item-line"></div>
                    </div>
                    <div class="illustration-item">
                        <div class="item-icon"></div>
                        <div class="item-line short"></div>
                    </div>
                    <div class="illustration-item">
                        <div class="item-icon"></div>
                        <div class="item-line"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <p class="tagline">Never lose track of your belongings on campus</p>
    </div>

    <!-- Right Section - Login Form -->
    <div class="right-section">
        <div class="login-container">
            <div class="login-header">
                <h1 class="login-title">Log in</h1>
                <p class="login-subtitle">Log in to your account</p>
            </div>

            <div id="errorMessage" class="error-message"
            style="<?php echo isset($_SESSION['login_error']) ? 'display:block;' : 'display:none;'; ?>">
            <?php
            if (isset($_SESSION['login_error'])) {
                echo $_SESSION['login_error'];
                unset($_SESSION['login_error']); // reset pesan error setelah ditampilkan
                }
                ?>
                </div>

            <form class="login-form" method="POST" action="login.php">
                <input 
                    type="text" 
                    class="form-input" 
                    placeholder="NIM"
                    name="nim"
                    required
                >
                
                <input 
                    type="password" 
                    class="form-input" 
                    placeholder="Password"
                    name="password"
                    required
                >
                
                <button type="submit" class="login-button">Log In</button>
            </form>

            <p class="signup-link">
                Doesn't have an account yet? <a href="signup.php">Sign Up</a>
            </p>
        </div>
    </div>
</body>
</html>
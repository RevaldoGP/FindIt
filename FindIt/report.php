<?php
session_start();
require_once "db.php";

// Jika belum login -> redirect ke login
if (!isset($_SESSION['user_id'])) {
    // Simpan redirect agar kembali ke report setelah login
    $_SESSION['redirect_after_login'] = 'report.php';
    header("Location: login.php");
    exit;
}

// Ambil pesan flash bila ada
$success_msg = isset($_SESSION['report_success']) ? $_SESSION['report_success'] : null;
$error_msg   = isset($_SESSION['report_error']) ? $_SESSION['report_error'] : null;
unset($_SESSION['report_success'], $_SESSION['report_error']);

// ambil nama user untuk navbar
$full_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report - FindIt</title>
    <style>
        /* (sama persis dengan CSS lama-mu) */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; min-height: 100vh; }

        /* Navigation Bar */
        nav { position: fixed; top: 0; width: 100%; background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); box-shadow: 0 2px 20px rgba(0,0,0,0.1); z-index: 1000; }
        .nav-container { display:flex; justify-content: space-between; align-items:center; padding:1rem 5%; max-width:1400px; margin:0 auto; }
        .logo { display:flex; align-items:center; gap:0.5rem; cursor:pointer; text-decoration:none; }
        .logo-image { height:40px; width:auto; object-fit:contain; transition: transform 0.3s ease; }
        .logo:hover .logo-image { transform: scale(1.05); }
        .nav-menu { display:flex; gap:0; list-style:none; }
        .nav-menu li a { text-decoration:none; color:#333; font-size:1.1rem; font-weight:600; padding:1rem 2.5rem; display:block; transition:all .3s ease; border-radius:12px; }
        .nav-menu li a.active { background:#0000ff; color:white; }
        .nav-menu li a:hover:not(.active) { background:#f0f0f0; }

        .auth-section { display:flex; align-items:center; gap:0.5rem; margin-left:1.5rem; }
        .auth-button { color:#0000ff; text-decoration:none; font-weight:600; font-size:1rem; transition:all .3s ease; display:flex; align-items:center; gap:0.3rem; }
        .auth-icon { width:32px; height:32px; background:#0000ff; border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; font-size:1rem; cursor:pointer; transition:all .3s ease; }
        .user-menu { position:relative; }
        .user-button { display:flex; align-items:center; gap:0.5rem; background:none; border:none; cursor:pointer; font-size:1rem; color:#333; font-weight:600; }
        .user-avatar { width:36px; height:36px; background:#0000ff; border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; font-weight:bold; font-size:1rem; }

        .dropdown-menu { position:absolute; top:calc(100% + 10px); right:0; background:white; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,0.15); min-width:180px; display:none; z-index:1000; overflow:hidden; }
        .dropdown-menu.show { display:block; animation: slideDown 0.3s ease; }
        @keyframes slideDown { from { opacity:0; transform:translateY(-10px);} to { opacity:1; transform:translateY(0);} }
        .dropdown-item { padding:0.8rem 1.2rem; color:#333; text-decoration:none; display:block; font-size:0.95rem; }
        .dropdown-item.logout { color:#ff4444; border-top:1px solid #f0f0f0; }
        .dropdown-item:hover { background:#f5f5f5; }

        /* Main Content (sama) */
        .main-content { padding-top: 120px; padding-bottom: 80px; max-width: 700px; margin: 0 auto; padding-left:2rem; padding-right:2rem; }
        .page-title { font-size: 2.5rem; font-weight: bold; color: #333; margin-bottom: 1rem; text-align:center; }
        .page-subtitle { font-size:1.2rem; color:#999; margin-bottom:3rem; text-align:center; }

        .report-form { display:flex; flex-direction:column; gap:1.5rem; }
        .form-group { display:flex; flex-direction:column; gap:0.5rem; }
        .form-input, .form-textarea { width:100%; padding:1.2rem 1.5rem; font-size:1rem; border:none; border-radius:50px; background:#d9d9d9; color:#666; transition:all .3s; font-family:inherit; }
        .form-textarea { border-radius:25px; resize:vertical; min-height:100px; }
        .upload styles { /* omitted for brevity - same as your CSS */ }

        .image-upload-container { width:100%; position:relative; }
        .image-upload-wrapper { width:100%; min-height:250px; background:#d9d9d9; border-radius:25px; display:flex; flex-direction:column; align-items:center; justify-content:center; cursor:pointer; transition:all .3s ease; overflow:hidden; position:relative; }
        .upload-icon { font-size:3rem; color:#999; margin-bottom:1rem; }
        .upload-text { color:#999; font-size:1rem; text-align:center; padding:0 2rem; }
        .image-preview { width:100%; height:100%; min-height:250px; object-fit:cover; display:none; border-radius:25px; }
        .image-preview.show { display:block; }
        .remove-image { position:absolute; top:1rem; right:1rem; width:40px; height:40px; background:#ff4444; color:white; border:none; border-radius:50%; font-size:1.5rem; cursor:pointer; display:none; align-items:center; justify-content:center; z-index:10; }
        .remove-image.show { display:flex; }

        .submit-button { background:#0000ff; color:white; border:none; padding:1.2rem 3rem; font-size:1.2rem; font-weight:600; border-radius:50px; cursor:pointer; box-shadow:0 10px 30px rgba(0,0,255,0.3); margin-top:2rem; }
        .success-message { display:none; background:#4CAF50; color:white; padding:1rem; border-radius:10px; text-align:center; margin-bottom:1rem; animation: slideDownMsg 0.3s ease; }
        .error-message { display:none; background:#ff4444; color:white; padding:1rem; border-radius:10px; text-align:center; margin-bottom:1rem; }

        @media (max-width:768px) { /* responsive omitted for brevity */ }
    </style>
</head>
<body>
    <!-- Navigation (PHP-based auth section) -->
    <nav>
        <div class="nav-container">
            <a href="home.php" class="logo">
                <img src="logo.png" alt="FindIt Logo" class="logo-image">
            </a>

            <ul class="nav-menu">
                <li><a href="home.php">Home</a></li>
                <li><a href="report.php" class="active">Report</a></li>
                <li><a href="find.php">Find</a></li>
                <li><a href="history.php">History</a></li>
            </ul>

            <div class="auth-section">
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="login.php" class="auth-button">
                        <span class="auth-icon">👤</span>
                        Login
                    </a>
                <?php else: ?>
                    <div class="user-menu">
                        <button class="user-button" onclick="toggleDropdown()">
                            <div class="user-avatar">
                                <?= strtoupper(substr($full_name,0,2)); ?>
                            </div>
                        </button>
                        <div class="dropdown-menu" id="dropdownMenu">
                            <div class="dropdown-item" style="font-weight:bold; border-bottom:1px solid #f0f0f0;">
                                <?= htmlspecialchars($full_name); ?>
                            </div>
                            <a href="history.php" class="dropdown-item">My Reports</a>
                            <a href="logout.php" class="dropdown-item logout">Logout</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <?php if ($success_msg): ?>
            <div id="successMessage" class="success-message" style="display:block;">
                <?= htmlspecialchars($success_msg); ?>
            </div>
        <?php endif; ?>

        <?php if ($error_msg): ?>
            <div id="errorMessage" class="error-message" style="display:block;">
                <?= htmlspecialchars($error_msg); ?>
            </div>
        <?php endif; ?>

        <h1 class="page-title">Report</h1>
        <p class="page-subtitle">Report lost item that you found</p>

        <form class="report-form" id="reportForm" action="process_report.php" method="POST" enctype="multipart/form-data">
            <!-- user_id will come from session on server side; no need for hidden input -->
            <div class="form-group">
                <input type="text" class="form-input" placeholder="Item name" name="item_name" required>
            </div>

            <div class="form-group">
                <textarea class="form-textarea" placeholder="Description" name="description" required></textarea>
            </div>

            <div class="form-group">
                <input type="text" class="form-input" placeholder="Location" name="location" required>
            </div>

            <!-- Image Upload -->
            <div class="form-group">
                <div class="image-upload-container">
                    <input type="file" id="imageInput" name="image" accept="image/*" onchange="handleImageSelect(event)">
                    <div class="image-upload-wrapper" onclick="document.getElementById('imageInput').click()">
                        <div class="upload-icon">📷</div>
                        <div class="upload-text">
                            <strong>Click to upload</strong> item image<br>
                            <small>Supports: JPG, PNG, GIF (Max 5MB)</small>
                        </div>
                        <img id="imagePreview" class="image-preview" alt="Preview">
                    </div>
                    <button type="button" class="remove-image" id="removeImageBtn" onclick="removeImage()">×</button>
                </div>
            </div>

            <button type="submit" class="submit-button">Submit Report</button>
        </form>
    </div>

    <script>
        // image preview logic (kept client-side)
        function handleImageSelect(event) {
            const file = event.target.files[0];
            if (!file) return;

            if (file.size > 5 * 1024 * 1024) {
                alert('File size must be less than 5MB');
                event.target.value = '';
                return;
            }
            if (!file.type.startsWith('image/')) {
                alert('Please select an image file');
                event.target.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('imagePreview');
                const wrapper = document.querySelector('.image-upload-wrapper');
                const removeBtn = document.getElementById('removeImageBtn');

                preview.src = e.target.result;
                preview.classList.add('show');
                wrapper.classList.add('has-image');
                removeBtn.classList.add('show');
                wrapper.querySelector('.upload-icon').style.display = 'none';
                wrapper.querySelector('.upload-text').style.display = 'none';
            };
            reader.readAsDataURL(file);
        }

        function removeImage() {
            event.stopPropagation();
            const preview = document.getElementById('imagePreview');
            const wrapper = document.querySelector('.image-upload-wrapper');
            const removeBtn = document.getElementById('removeImageBtn');
            const input = document.getElementById('imageInput');

            preview.src = '';
            preview.classList.remove('show');
            wrapper.classList.remove('has-image');
            removeBtn.classList.remove('show');
            input.value = '';
            wrapper.querySelector('.upload-icon').style.display = 'block';
            wrapper.querySelector('.upload-text').style.display = 'block';
        }

        function toggleDropdown() {
            const dropdown = document.getElementById('dropdownMenu');
            if (dropdown) dropdown.classList.toggle('show');
        }

        // close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const userMenu = document.querySelector('.user-menu');
            const dropdown = document.getElementById('dropdownMenu');
            if (dropdown && userMenu && !userMenu.contains(event.target)) {
                dropdown.classList.remove('show');
            }
        });

        // show/hide nav shadow on scroll
        window.addEventListener('scroll', () => {
            const nav = document.querySelector('nav');
            if (window.scrollY > 50) nav.style.boxShadow = '0 2px 30px rgba(0,0,0,0.2)';
            else nav.style.boxShadow = '0 2px 20px rgba(0,0,0,0.1)';
        });
    </script>
</body>
</html>

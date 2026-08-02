<?php
// ============================================
// File: admin_login.php - Admin Login
// ============================================
session_start();
include("includes/DBConn.php");

if(isset($_POST['login'])){
    $u = sanitize($_POST['username']);
    $p = md5($_POST['password']);
    $q = mysqli_query($conn, "SELECT * FROM tblUser WHERE username='$u' AND password='$p' AND role='Admin' AND status='Approved'");
    if(mysqli_num_rows($q) > 0){
        $_SESSION['admin'] = true;
        $_SESSION['user'] = $u;
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $error = "Invalid admin credentials.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login | Pastimes</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #1e2f3c; display: flex; justify-content: center; align-items: center; height: 100vh; margin:0; }
        .login-card { background: white; padding: 2.5rem; border-radius: 32px; width: 380px; box-shadow: 0 20px 40px rgba(0,0,0,0.3); }
        .login-card h2 { margin-bottom: 0.5rem; font-weight: 700; color: #1e2f3c; }
        .login-card p { color: #7a8e9e; margin-bottom: 1.5rem; font-size: 0.9rem; }
        input { width: 100%; padding: 12px; margin: 8px 0; border: 1px solid #ddd; border-radius: 60px; font-family: inherit; }
        button { background: #1e2f3c; color: white; border: none; padding: 12px; border-radius: 60px; width: 100%; font-weight: bold; cursor: pointer; margin-top: 10px; }
        button:hover { background: #e07c4c; }
        .error { color: #d9534f; margin-bottom: 1rem; }
        .back-link { text-align: center; margin-top: 1rem; }
        .back-link a { color: #7a8e9e; text-decoration: none; font-size: 0.9rem; }
        .admin-badge { display: inline-block; background: #fef3c7; color: #b45309; padding: 0.2rem 0.8rem; border-radius: 20px; font-size: 0.7rem; font-weight: 600; }
    </style>
</head>
<body>
<div class="login-card">
    <h2><i class="fas fa-shield-alt"></i> Admin Access</h2>
    <p>Enter your admin credentials <span class="admin-badge">Restricted</span></p>
    <?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>
    <form method="POST">
        <input name="username" placeholder="Admin Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button name="login">Access Dashboard →</button>
    </form>
    <div class="back-link"><a href="index.php">← Return to Store</a></div>
</div>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</body>
</html>
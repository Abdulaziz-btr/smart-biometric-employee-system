<?php
require_once 'config.php';
session_start();
if(!empty($_SESSION['admin_id'])){header('Location:/smart_employee/admin/dashboard.php');exit;}
$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $u=trim($_POST['username']??'');
    $p=trim($_POST['password']??'');
    $a=row("SELECT * FROM admin WHERE username='".xss($u)."'");
    if($a && password_verify($p,$a['password'])){
        $_SESSION['admin_id']=$a['id'];$_SESSION['admin_name']=$a['username'];
        header('Location:/smart_employee/admin/dashboard.php');exit;
    }
    $err='Invalid username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Login – Smart Employee Management</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="auth-wrap">
  <div class="auth-box">
    <div class="auth-icon">🛡️</div>
    <h2>Smart Employee Management System</h2>
    <p class="auth-sub">Admin Login Required</p>

    <?php if($err): ?>
    <div class="alert alert-err"><?= esc($err) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="fg">
        <label>👤 Username</label>
        <div class="input-wrap">
          <span class="input-icon">👤</span>
          <input class="fc" type="text" name="username" placeholder="Enter your username" required autofocus>
        </div>
      </div>
      <div class="fg">
        <label>🔐 Password</label>
        <div class="input-wrap">
          <span class="input-icon">🔐</span>
          <input class="fc" type="password" name="password" placeholder="Enter your password" required>
        </div>
      </div>
      <button type="submit" class="btn btn-navy btn-lg" style="margin-top:6px;">🔑 Log In</button>
    </form>

    <div class="alert alert-info" style="margin-top:18px;font-size:12px;">
      <div><strong>Default Login:</strong> &nbsp;Username: <code>admin</code> &nbsp;|&nbsp; Password: <code>admin123</code>
      <br><small style="margin-top:4px;display:block;">If login fails, run <strong>fix_admin.php</strong> first.</small></div>
    </div>

    <div class="auth-link">Employee? <a href="login.php">Login here</a></div>
  </div>
</div>
</body>
</html>

<?php
require_once 'config.php';
session_start();
if(!empty($_SESSION['emp_id'])){header('Location:/smart_employee/employee/dashboard.php');exit;}
$err='';$step='id';$demo_otp='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(isset($_POST['send_code'])){
        $fp=(int)$_POST['fp_id'];$email=xss($_POST['email']);
        $e=row("SELECT * FROM employee WHERE fingerprintId=$fp AND email='$email'");
        if($e){
            $otp=rand(100000,999999);$exp=date('Y-m-d H:i:s',strtotime('+15 minutes'));
            db()->query("UPDATE employee SET loginOtp='$otp',loginOtpExpiresAt='$exp' WHERE id={$e['id']}");
            $_SESSION['otp_eid']=$e['id'];$_SESSION['_demo_otp']=$otp;
            $step='otp';$demo_otp=$otp;
        } else { $err='Fingerprint ID or Email not found.'; }
    }
    if(isset($_POST['verify_otp'])){
        $eid=(int)($_SESSION['otp_eid']??0);
        $otp=xss($_POST['otp']);
        $e=row("SELECT * FROM employee WHERE id=$eid AND loginOtp='$otp'");
        if($e){
            db()->query("UPDATE employee SET loginOtp=NULL,loginOtpExpiresAt=NULL WHERE id=$eid");
            $_SESSION['emp_id']=$e['id'];$_SESSION['emp_name']=$e['name'];$_SESSION['emp_pos']=$e['position'];
            unset($_SESSION['otp_eid'],$_SESSION['_demo_otp']);
            header('Location:/smart_employee/employee/dashboard.php');exit;
        } else { $err='Invalid or expired OTP.';$step='otp'; }
    }
}
if(!empty($_SESSION['otp_eid'])){$step='otp';$demo_otp=$_SESSION['_demo_otp']??'';}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Employee Login – Smart Employee Management</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="auth-wrap">
  <div class="auth-box">
    <div class="auth-icon">🔒</div>
    <h2>Employee Login</h2>
    <p class="auth-sub">Enter your Fingerprint ID and Email</p>

    <?php if($err): ?>
    <div class="alert alert-err"><?= esc($err) ?></div>
    <?php endif; ?>

    <?php if($demo_otp): ?>
    <div class="alert alert-info" style="flex-direction:column;align-items:flex-start;gap:6px;">
      <div style="font-weight:700;">📱 Your OTP Code (Demo Mode):</div>
      <div style="font-size:28px;font-weight:800;letter-spacing:6px;color:var(--navy);text-align:center;width:100%;"><?= $demo_otp ?></div>
      <div style="font-size:11px;color:var(--muted);">In production this is sent to your email. Copy the code above.</div>
    </div>
    <?php endif; ?>

    <?php if($step==='id'): ?>
    <form method="POST">
      <div class="fg">
        <label>Fingerprint ID</label>
        <div class="input-wrap">
          <span class="input-icon">👆</span>
          <input class="fc" type="number" name="fp_id" placeholder="e.g., 1001" required autofocus>
        </div>
      </div>
      <div class="fg">
        <label>Email Address</label>
        <div class="input-wrap">
          <span class="input-icon">✉️</span>
          <input class="fc" type="email" name="email" placeholder="e.g., you@company.com" required>
        </div>
      </div>
      <button type="submit" name="send_code" class="btn btn-navy btn-lg" style="margin-top:6px;">📱 Send Login Code</button>
    </form>

    <?php else: ?>
    <form method="POST">
      <div class="fg">
        <label>Enter the 6-digit OTP code</label>
        <input class="fc" type="text" name="otp" placeholder="000000" maxlength="6" required autofocus
               style="font-size:22px;letter-spacing:8px;text-align:center;font-weight:800;">
      </div>
      <button type="submit" name="verify_otp" class="btn btn-green btn-lg">✅ Verify &amp; Login</button>
    </form>
    <div class="auth-link" style="margin-top:12px;"><a href="login.php">← Use different account</a></div>
    <?php endif; ?>

    <div style="border-top:1px solid var(--border);margin-top:20px;padding-top:16px;">
      <p style="font-size:11px;color:var(--muted);text-align:center;margin-bottom:8px;font-weight:600;">TEST ACCOUNTS</p>
      <div style="display:grid;gap:6px;">
        <?php
        $emps=db()->query("SELECT fingerprintId,name,position,email FROM employee ORDER BY id");
        while($e=$emps->fetch_assoc()):
        ?>
        <div style="background:#f7f8fc;border-radius:6px;padding:7px 11px;font-size:11px;display:flex;justify-content:space-between;align-items:center;">
          <div><strong><?= esc($e['name']) ?></strong> <span style="color:var(--muted);">(<?= esc($e['position']) ?>)</span></div>
          <code>FP: <?= $e['fingerprintId'] ?></code>
        </div>
        <?php endwhile; ?>
      </div>
    </div>

    <div class="auth-link" style="margin-top:14px;">Admin? <a href="admin_login.php">Login here</a></div>
  </div>
</div>
</body>
</html>

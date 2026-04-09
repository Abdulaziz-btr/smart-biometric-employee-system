<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Fix Admin – AVATA</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Poppins,sans-serif;background:linear-gradient(135deg,#0d1560,#1a237e);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.box{background:#fff;border-radius:14px;padding:36px;max-width:440px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.3)}
.icon{font-size:40px;text-align:center;margin-bottom:12px}
h2{text-align:center;color:#1a237e;font-size:18px;margin-bottom:6px}
.sub{text-align:center;color:#888;font-size:12px;margin-bottom:22px}
label{display:block;font-size:12px;font-weight:600;color:#444;margin-bottom:5px;margin-top:12px}
input{width:100%;padding:10px 13px;border:1.5px solid #dde1f0;border-radius:7px;font-family:Poppins,sans-serif;font-size:13px}
input:focus{outline:none;border-color:#1a237e}
.btn{display:block;width:100%;padding:12px;background:#1a237e;color:#fff;border:none;border-radius:7px;font-family:Poppins,sans-serif;font-size:14px;font-weight:700;cursor:pointer;margin-top:18px;transition:background .2s}
.btn:hover{background:#283593}
.ok{background:#f0faf0;color:#2e7d32;border:1px solid #c8e6c9;border-radius:8px;padding:14px;margin-top:14px;font-size:13px;font-weight:600}
.err{background:#fff5f5;color:#c62828;border:1px solid #ffcdd2;border-radius:8px;padding:14px;margin-top:14px;font-size:13px;font-weight:600}
.note{background:#fff8f0;color:#e65100;border:1px solid #ffe0b2;border-left:4px solid #e65100;border-radius:8px;padding:12px;margin-top:14px;font-size:12px}
a.go{display:block;text-align:center;margin-top:12px;color:#1a237e;font-weight:700;font-size:13px}
</style>
</head>
<body>
<div class="box">
  <div class="icon">🔧</div>
  <h2>Admin Password Reset</h2>
  <p class="sub">Sets the admin password correctly in the database</p>
<?php
$host='localhost'; $user='root'; $pass=''; $db='smart_employee';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $np=trim($_POST['pw']??'admin123');
    $nu=trim($_POST['un']??'admin');
    if(strlen($np)<4){ echo '<div class="err">❌ Password too short (min 4 characters)</div>'; }
    else {
        $c=new mysqli($host,$user,$pass,$db);
        if($c->connect_error){ echo '<div class="err">❌ Cannot connect: '.$c->connect_error.'</div>'; }
        else {
            $h=password_hash($np,PASSWORD_BCRYPT);
            $u=$c->real_escape_string($nu);
            $c->query("DELETE FROM admin");
            $c->query("INSERT INTO admin (username,password) VALUES ('$u','$h')");
            echo '<div class="ok">✅ Done! Username: <strong>'.$u.'</strong> | Password: <strong>'.htmlspecialchars($np).'</strong></div>';
            echo '<a class="go" href="/smart_employee/admin_login.php">→ Go to Admin Login</a>';
            echo '<div class="note">🗑️ Delete this file after login:<br><code>C:\xampp\htdocs\smart_employee\fix_admin.php</code></div>';
            $c->close();
        }
    }
} else {
    $c=@new mysqli($host,$user,$pass,$db);
    if(!$c->connect_error){
        $n=$c->query("SELECT COUNT(*) c FROM admin")->fetch_assoc()['c'];
        echo '<div class="ok" style="margin-bottom:14px">✅ Database connected | Admin records: <strong>'.$n.'</strong></div>';
        $c->close();
    } else { echo '<div class="err" style="margin-bottom:14px">❌ Cannot reach database — start MySQL in XAMPP</div>'; }
}
?>
  <form method="POST">
    <label>Admin Username</label>
    <input type="text" name="un" value="admin" required>
    <label>Admin Password</label>
    <input type="text" name="pw" value="admin123" required>
    <button type="submit" class="btn">🔧 Reset Password Now</button>
  </form>
</div>
</body>
</html>

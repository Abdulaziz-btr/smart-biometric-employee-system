<?php
/* ── database ── */
define('DB_HOST','localhost');
define('DB_USER','root');
define('DB_PASS','');
define('DB_NAME','smart_employee');

function db(){
    static $c=null;
    if($c) return $c;
    $c=new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
    if($c->connect_error) die('<div style="font-family:Poppins,Arial,sans-serif;background:#f0f2f8;min-height:100vh;display:flex;align-items:center;justify-content:center;"><div style="background:#fff;border-radius:12px;padding:40px;max-width:500px;text-align:center;box-shadow:0 8px 30px rgba(0,0,0,.12)"><div style="font-size:48px;margin-bottom:16px;">❌</div><h2 style="color:#c62828;margin-bottom:12px;">Database Connection Failed</h2><p style="color:#666;margin-bottom:8px;">'.$c->connect_error.'</p><p style="color:#888;font-size:13px;">Make sure XAMPP MySQL is running and you have imported <strong>setup.sql</strong></p></div></div>');
    $c->set_charset('utf8mb4');
    return $c;
}

/* ── auth ── */
function authAdmin(){
    if(session_status()===PHP_SESSION_NONE) session_start();
    if(empty($_SESSION['admin_id'])){ header('Location:/smart_employee/admin_login.php'); exit; }
}
function authEmp(){
    if(session_status()===PHP_SESSION_NONE) session_start();
    if(empty($_SESSION['emp_id'])){ header('Location:/smart_employee/login.php'); exit; }
}

/* ── helpers ── */
function esc($s){ return htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8'); }
function row($sql){ $r=db()->query($sql); return $r?$r->fetch_assoc():null; }
function val($sql){ $r=row($sql); return $r?reset($r):0; }
function xss($s){ return db()->real_escape_string(trim($s)); }
function rwf($amount){ return number_format((float)$amount,0,'.',',').' RWF'; }
?>
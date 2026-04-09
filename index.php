<?php session_start();
if(!empty($_SESSION['admin_id'])){header('Location:/smart_employee/admin/dashboard.php');exit;}
if(!empty($_SESSION['emp_id']))  {header('Location:/smart_employee/employee/dashboard.php');exit;}
header('Location:/smart_employee/admin_login.php');exit;

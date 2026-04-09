<?php require_once '../config.php';authAdmin();$id=(int)($_GET['id']??0);if($id>0)db()->query("DELETE FROM attendancelog WHERE id=$id");header('Location:attendance.php');exit;

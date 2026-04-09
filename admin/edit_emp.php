<?php
require_once '../config.php'; authAdmin();
$id=(int)($_GET['id']??0);$e=row("SELECT * FROM employee WHERE id=$id");
if(!$e){header('Location:employees.php');exit;}
$msg='';
if(isset($_POST['save'])){
    $n=xss($_POST['name']);$p=xss($_POST['position']);$em=xss($_POST['email']);
    $s=(float)$_POST['salary'];$t=(float)$_POST['target'];$fp=(int)$_POST['fp_id'];
    db()->query("UPDATE employee SET name='$n',position='$p',email='$em',salaryRatePerDay=$s,dailySalesTarget=$t,fingerprintId=$fp WHERE id=$id");
    $msg='✅ Employee updated.';$e=row("SELECT * FROM employee WHERE id=$id");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Edit Employee</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="layout"><?php include '_sb.php'; ?>
<div class="main">
  <div class="topbar">
    <div class="topbar-left"><h1>✏️ Edit Employee</h1></div>
    <div class="topbar-right"><a href="employees.php" class="btn btn-gray btn-sm">← Back</a></div>
  </div>
  <div class="page">
    <?php if($msg): ?><div class="alert alert-ok"><?= $msg ?></div><?php endif; ?>
    <div class="card" style="max-width:620px;">
      <div class="card-head"><h3><?= esc($e['name']) ?></h3></div>
      <div class="card-body">
        <form method="POST">
          <div class="row2">
            <div class="fg"><label>Full Name</label><input type="text" name="name" class="fc" value="<?= esc($e['name']) ?>" required></div>
            <div class="fg"><label>Position</label>
              <select name="position" class="fc">
                <?php foreach(['Seller','Delivery Person','Store Keeper','Manager'] as $p): ?>
                <option value="<?= $p ?>" <?= $e['position']===$p?'selected':'' ?>><?= $p ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="fg"><label>Email</label><input type="email" name="email" class="fc" value="<?= esc($e['email']) ?>" required></div>
          <div class="row3">
            <div class="fg"><label>Fingerprint ID</label><input type="number" name="fp_id" class="fc" value="<?= $e['fingerprintId'] ?>"></div>
            <div class="fg"><label>Daily Rate ($)</label><input type="number" name="salary" step="0.01" class="fc" value="<?= $e['salaryRatePerDay'] ?>"></div>
            <div class="fg"><label>Sales Target ($)</label><input type="number" name="target" step="0.01" class="fc" value="<?= $e['dailySalesTarget'] ?>"></div>
          </div>
          <div class="flex gap10"><button type="submit" name="save" class="btn btn-navy">💾 Save Changes</button><a href="employees.php" class="btn btn-gray">Cancel</a></div>
        </form>
      </div>
    </div>
  </div>
</div>
</div>
</body>
</html>

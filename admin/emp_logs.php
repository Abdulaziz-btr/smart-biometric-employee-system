<?php
require_once '../config.php'; authAdmin();
$id=(int)($_GET['id']??0);$emp=row("SELECT * FROM employee WHERE id=$id");
if(!$emp){header('Location:employees.php');exit;}
$month=(int)($_GET['month']??date('m'));$year=(int)($_GET['year']??date('Y'));
$my=sprintf('%04d-%02d',$year,$month);
$logs=db()->query("SELECT * FROM attendancelog WHERE employeeId=$id AND DATE_FORMAT(timestamp,'%Y-%m')='$my' ORDER BY timestamp DESC");
$days=val("SELECT COUNT(DISTINCT DATE(timestamp)) FROM attendancelog WHERE employeeId=$id AND DATE_FORMAT(timestamp,'%Y-%m')='$my'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Employee Logs</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="layout"><?php include '_sb.php'; ?>
<div class="main">
  <div class="topbar">
    <div class="topbar-left"><h1>📋 Logs — <?= esc($emp['name']) ?></h1></div>
    <div class="topbar-right"><a href="employees.php" class="btn btn-gray btn-sm">← Back</a></div>
  </div>
  <div class="page">
    <div class="stats-row" style="grid-template-columns:repeat(2,1fr);max-width:360px;">
      <div class="stat-card"><div class="stat-icon ic-green">✅</div><div class="stat-info"><h3><?= $days ?></h3><p>Days Present</p></div></div>
      <div class="stat-card"><div class="stat-icon ic-navy">📋</div><div class="stat-info"><h3><?= $logs->num_rows ?></h3><p>Total Scans</p></div></div>
    </div>
    <div class="card"><div class="card-body" style="padding:14px 20px;">
      <form method="GET" class="flex gap10">
        <input type="hidden" name="id" value="<?= $id ?>">
        <select name="month" class="fc" style="width:140px;"><?php for($m=1;$m<=12;$m++): ?><option value="<?= $m ?>" <?= $m==$month?'selected':'' ?>><?= date('F',mktime(0,0,0,$m,1)) ?></option><?php endfor; ?></select>
        <select name="year" class="fc" style="width:95px;"><?php for($y=date('Y')-1;$y<=date('Y');$y++): ?><option value="<?= $y ?>" <?= $y==$year?'selected':'' ?>><?= $y ?></option><?php endfor; ?></select>
        <button type="submit" class="btn btn-navy btn-sm">Filter</button>
      </form>
    </div></div>
    <div class="card mb0">
      <div class="card-head"><h3><?= esc($emp['name']) ?> — <?= date('F Y',mktime(0,0,0,$month,1,$year)) ?></h3></div>
      <div class="card-body-0">
        <?php if($logs->num_rows===0): ?><p class="empty">No records found.</p><?php else: ?>
        <div class="tbl-wrap"><table>
          <thead><tr><th>#</th><th>Date</th><th>Time</th><th>Status</th></tr></thead>
          <tbody>
          <?php $i=1;while($l=$logs->fetch_assoc()): $late=(int)date('H',strtotime($l['timestamp']))>=9; ?>
          <tr><td><?= $i++ ?></td><td class="nowrap"><?= date('D, M d Y',strtotime($l['timestamp'])) ?></td>
          <td class="nowrap"><?= date('h:i:s A',strtotime($l['timestamp'])) ?></td>
          <td><span class="tag <?= $late?'tag-orange':'tag-green' ?>"><?= $late?'⚠️ Late':'✅ On Time' ?></span></td></tr>
          <?php endwhile; ?>
          </tbody>
        </table></div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
</div>
</body>
</html>

<?php
require_once '../config.php';
authEmp();
$eid   = $_SESSION['emp_id'];
$emp   = row("SELECT * FROM employee WHERE id=$eid");
$month = (int)($_GET['month'] ?? date('m'));
$year  = (int)($_GET['year']  ?? date('Y'));
$my    = sprintf('%04d-%02d',$year,$month);

$logs  = db()->query("SELECT * FROM attendancelog WHERE employeeId=$eid AND DATE_FORMAT(timestamp,'%Y-%m')='$my' ORDER BY timestamp DESC");
$days  = (int)val("SELECT COUNT(DISTINCT DATE(timestamp)) FROM attendancelog WHERE employeeId=$eid AND DATE_FORMAT(timestamp,'%Y-%m')='$my'");
$lateCount = 0;
$tmp   = db()->query("SELECT timestamp FROM attendancelog WHERE employeeId=$eid AND DATE_FORMAT(timestamp,'%Y-%m')='$my'");
while($t=$tmp->fetch_assoc()){ if((int)date('H',strtotime($t['timestamp']))>=9) $lateCount++; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>My Attendance – Smart Employee Management</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="layout">
  <?php include '_sb.php'; ?>
  <div class="main">

    <div class="topbar">
      <div class="topbar-left">
        <h1>📋 My Attendance History</h1>
        <div class="sub-info">👤 <?= esc($emp['name']) ?> &nbsp;|&nbsp; <?= esc($emp['position']) ?></div>
      </div>
      <div class="topbar-right">
        <a href="dashboard.php" class="btn btn-gray btn-sm">← Dashboard</a>
      </div>
    </div>

    <div class="page">

      <!-- Stats -->
      <div class="stats-row" style="grid-template-columns:repeat(3,1fr);max-width:520px;">
        <div class="stat-card">
          <div class="stat-icon ic-green">✅</div>
          <div class="stat-info"><h3><?= $days ?></h3><p>Days Present</p><small><?= date('F Y',mktime(0,0,0,$month,1,$year)) ?></small></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon ic-red">⚠️</div>
          <div class="stat-info"><h3><?= $lateCount ?></h3><p>Late Arrivals</p><small>After 9:00 AM</small></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon ic-navy">📋</div>
          <div class="stat-info"><h3><?= $logs->num_rows ?></h3><p>Total Scans</p><small>This month</small></div>
        </div>
      </div>

      <!-- Month filter -->
      <div class="card">
        <div class="card-body" style="padding:14px 20px;">
          <form method="GET" class="flex gap14">
            <label style="font-size:13px;font-weight:600;white-space:nowrap;">📅 Select Month:</label>
            <select name="month" class="fc" style="width:145px;">
              <?php for($m=1;$m<=12;$m++): ?>
              <option value="<?= $m ?>" <?= $m==$month?'selected':'' ?>><?= date('F',mktime(0,0,0,$m,1)) ?></option>
              <?php endfor; ?>
            </select>
            <select name="year" class="fc" style="width:98px;">
              <?php for($y=date('Y')-1;$y<=date('Y');$y++): ?>
              <option value="<?= $y ?>" <?= $y==$year?'selected':'' ?>><?= $y ?></option>
              <?php endfor; ?>
            </select>
            <button type="submit" class="btn btn-navy btn-sm">🔍 View</button>
          </form>
        </div>
      </div>

      <!-- Attendance table -->
      <div class="card mb0">
        <div class="card-head">
          <h3>Attendance — <?= date('F Y',mktime(0,0,0,$month,1,$year)) ?></h3>
          <span class="tag tag-green"><?= $days ?> days present</span>
        </div>
        <div class="card-body-0">
          <?php if($logs->num_rows===0): ?>
          <p class="empty">No attendance records for this month.<br><small>Attendance is recorded when you scan your fingerprint at the entrance.</small></p>
          <?php else: $logs->data_seek(0); ?>
          <div class="tbl-wrap">
            <table>
              <thead>
                <tr><th>#</th><th>Date</th><th>Scan Time</th><th>Status</th></tr>
              </thead>
              <tbody>
              <?php $i=1; while($l=$logs->fetch_assoc()):
                $late=(int)date('H',strtotime($l['timestamp']))>=9;
              ?>
              <tr>
                <td><?= $i++ ?></td>
                <td class="nowrap"><?= date('D, M d Y',strtotime($l['timestamp'])) ?></td>
                <td class="nowrap"><?= date('h:i:s A',strtotime($l['timestamp'])) ?></td>
                <td>
                  <span class="tag <?= $late?'tag-orange':'tag-green' ?>">
                    <?= $late ? '⚠️ Late Arrival' : '✅ On Time' ?>
                  </span>
                </td>
              </tr>
              <?php endwhile; ?>
              </tbody>
            </table>
          </div>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </div>
</div>
</body>
</html>

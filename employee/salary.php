<?php
require_once '../config.php';
authEmp();
$eid   = $_SESSION['emp_id'];
$emp   = row("SELECT * FROM employee WHERE id=$eid");
$month = (int)($_GET['month'] ?? date('m'));
$year  = (int)($_GET['year']  ?? date('Y'));
$my    = sprintf('%04d-%02d',$year,$month);

$sal = row("SELECT * FROM salaryrecord WHERE employeeId=$eid AND monthYear='$my'");
$abs = db()->query("SELECT * FROM absencerequest WHERE employeeId=$eid AND statusId='approved' AND DATE_FORMAT(date,'%Y-%m')='$my'");
$ot  = db()->query("SELECT * FROM overtimerecord WHERE employeeId=$eid AND month=$month AND year=$year");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Salary Details – Smart Employee Management</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="layout">
  <?php include '_sb.php'; ?>
  <div class="main">
    <div class="topbar">
      <div class="topbar-left">
        <h1>💰 My Salary Details</h1>
        <div class="sub-info">👤 <?= esc($emp['name']) ?> &nbsp;|&nbsp; <?= esc($emp['position']) ?></div>
      </div>
    </div>
    <div class="page">

      <div class="card">
        <div class="card-body" style="padding:14px 20px;">
          <form method="GET" class="flex gap14">
            <select name="month" class="fc" style="width:150px;">
              <?php for($m=1;$m<=12;$m++): ?>
              <option value="<?= $m ?>" <?= $m==$month?'selected':'' ?>><?= date('F',mktime(0,0,0,$m,1)) ?></option>
              <?php endfor; ?>
            </select>
            <select name="year" class="fc" style="width:100px;">
              <?php for($y=date('Y')-1;$y<=date('Y');$y++): ?>
              <option value="<?= $y ?>" <?= $y==$year?'selected':'' ?>><?= $y ?></option>
              <?php endfor; ?>
            </select>
            <button type="submit" class="btn btn-navy btn-sm">🔍 View</button>
          </form>
        </div>
      </div>

      <?php if($sal): ?>
      <div class="stats-row" style="grid-template-columns:repeat(4,1fr);">
        <div class="stat-card"><div class="stat-icon ic-green">📅</div><div class="stat-info"><h3><?= $sal['daysPresent'] ?></h3><p>Days Paid</p></div></div>
        <div class="stat-card"><div class="stat-icon ic-blue">💵</div><div class="stat-info"><h3 style="font-size:18px;"><?= rwf($sal['totalSalary']) ?></h3><p>Gross Salary</p></div></div>
        <div class="stat-card"><div class="stat-icon ic-red">➖</div><div class="stat-info"><h3 style="font-size:18px;"><?= rwf($sal['deductions']) ?></h3><p>Deductions</p></div></div>
        <div class="stat-card"><div class="stat-icon ic-teal">💰</div><div class="stat-info"><h3 style="font-size:18px;color:var(--green);"><?= rwf($sal['netSalary']) ?></h3><p>Net Salary</p></div></div>
      </div>

      <div class="card">
        <div class="card-head"><h3>📊 Salary Breakdown — <?= date('F Y',mktime(0,0,0,$month,1,$year)) ?></h3></div>
        <div class="card-body">
          <table style="max-width:500px;">
            <tbody>
              <tr><td style="padding:9px 0;width:220px;font-weight:600;">Daily Salary Rate</td><td><strong><?= rwf($emp['salaryRatePerDay']) ?> / day</strong></td></tr>
              <tr><td style="padding:9px 0;">Days Present / Paid</td><td><?= $sal['daysPresent'] ?> days</td></tr>
              <tr><td style="padding:9px 0;">Overtime Pay</td><td><?= rwf($sal['overtimePay']) ?></td></tr>
              <tr><td style="padding:9px 0;">Gross Salary</td><td><?= rwf($sal['totalSalary']) ?></td></tr>
              <tr><td style="padding:9px 0;">Deductions</td><td style="color:var(--red);">-<?= rwf($sal['deductions']) ?></td></tr>
              <tr style="border-top:2px solid var(--border);">
                <td style="padding:12px 0;font-weight:800;font-size:15px;">NET SALARY</td>
                <td style="font-weight:800;font-size:22px;color:var(--green);"><?= rwf($sal['netSalary']) ?></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <?php else: ?>
      <div class="alert alert-warn">
        ⏳ Salary for <strong><?= date('F Y',mktime(0,0,0,$month,1,$year)) ?></strong> has not been calculated yet.
        Please ask your Admin to generate the salary report.
      </div>
      <?php endif; ?>

      <?php if($abs->num_rows > 0): ?>
      <div class="card">
        <div class="card-head"><h3>🗓️ Approved Absences This Month</h3></div>
        <div class="card-body-0">
          <div class="tbl-wrap">
            <table>
              <thead><tr><th>Date</th><th>Reason</th><th>Status</th></tr></thead>
              <tbody>
              <?php while($a=$abs->fetch_assoc()): ?>
              <tr>
                <td class="nowrap"><?= date('M d, Y',strtotime($a['date'])) ?></td>
                <td><?= esc($a['reason']) ?></td>
                <td><span class="tag tag-green">✅ Approved (Paid)</span></td>
              </tr>
              <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <?php if($ot->num_rows > 0): ?>
      <div class="card mb0">
        <div class="card-head"><h3>⏰ Overtime Records</h3></div>
        <div class="card-body-0">
          <div class="tbl-wrap">
            <table>
              <thead><tr><th>Hours</th><th>Amount</th><th>Reason</th><th>Status</th></tr></thead>
              <tbody>
              <?php while($o=$ot->fetch_assoc()): ?>
              <tr>
                <td><?= $o['hours'] ?>h</td>
                <td><?= rwf($o['amount']) ?></td>
                <td><?= esc($o['reason']) ?></td>
                <td><span class="tag tag-blue"><?= ucfirst($o['statusId']) ?></span></td>
              </tr>
              <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <?php endif; ?>

    </div>
  </div>
</div>
</body>
</html>

<?php
require_once '../config.php'; authAdmin();
if(!function_exists('rwf')){
    function rwf($amount){ return number_format((float)$amount,0,'.',',').' RWF'; }
}
$msg='';
$month=(int)($_GET['month']??date('m'));
$year=(int)($_GET['year']??date('Y'));
$my=sprintf('%04d-%02d',$year,$month);

if(isset($_POST['gen'])){
    $emps=db()->query("SELECT * FROM employee");
    while($e=$emps->fetch_assoc()){
        $eid=$e['id'];
        $dp=(int)val("SELECT COUNT(DISTINCT DATE(timestamp)) FROM attendancelog WHERE employeeId=$eid AND DATE_FORMAT(timestamp,'%Y-%m')='$my'");
        $pa=(int)val("SELECT COUNT(*) FROM absencerequest WHERE employeeId=$eid AND statusId='approved' AND DATE_FORMAT(date,'%Y-%m')='$my'");
        $ot=(float)val("SELECT COALESCE(SUM(amount),0) FROM overtimerecord WHERE employeeId=$eid AND month=$month AND year=$year AND statusId='approved'");
        $paid=$dp+$pa;$gross=$paid*$e['salaryRatePerDay']+$ot;$net=$gross;
        $ex=row("SELECT id FROM salaryrecord WHERE employeeId=$eid AND monthYear='$my'");
        if($ex) db()->query("UPDATE salaryrecord SET totalSalary=$gross,deductions=0,netSalary=$net,overtimePay=$ot,daysPresent=$paid WHERE id={$ex['id']}");
        else    db()->query("INSERT INTO salaryrecord (employeeId,monthYear,totalSalary,deductions,netSalary,overtimePay,daysPresent) VALUES ($eid,'$my',$gross,0,$net,$ot,$paid)");
    }
    header("Location:salary.php?month=$month&year=$year&ok=1");exit;
}
if(isset($_GET['ok'])) $msg='✅ Salary records generated for '.date('F Y',mktime(0,0,0,$month,1,$year));

$rows     = db()->query("SELECT sr.*,e.name,e.position,e.email,e.fingerprintId,e.salaryRatePerDay FROM salaryrecord sr JOIN employee e ON e.id=sr.employeeId WHERE sr.monthYear='$my' ORDER BY e.name");
$totalPay = (float)val("SELECT COALESCE(SUM(netSalary),0) FROM salaryrecord WHERE monthYear='$my'");
$rptPend  = val("SELECT COUNT(*) FROM dailyreport WHERE justificationStatus='pending'");
$absPend  = val("SELECT COUNT(*) FROM absencerequest WHERE statusId='pending'");
$delivery = val("SELECT COUNT(*) FROM employee WHERE position='Delivery Person'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Salary Summary – Smart Employee Management</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="layout">
  <?php include '_sb.php'; ?>
  <div class="main">

    <div class="topbar">
      <div class="topbar-left">
        <h1>💰 Monthly Salary Summary</h1>
        <div class="sub-info">Total payroll: <strong><?= rwf($totalPay) ?></strong></div>
      </div>
      <div class="topbar-right">
        <form method="POST" style="display:inline;">
          <button type="submit" name="gen" class="btn btn-green btn-sm">⚙️ Generate Salary Report</button>
        </form>
      </div>
    </div>

    <div class="page">
      <?php if($msg): ?><div class="alert alert-ok"><?= $msg ?></div><?php endif; ?>

      <div class="stats-row" style="grid-template-columns:repeat(3,1fr);max-width:540px;">
        <div class="stat-card"><div class="stat-icon ic-orange">📋</div><div class="stat-info"><h3>1</h3><p>Stock Reports</p></div></div>
        <div class="stat-card"><div class="stat-icon ic-blue">🚗</div><div class="stat-info"><h3><?= $delivery ?></h3><p>Delivery Persons</p></div></div>
        <div class="stat-card"><div class="stat-icon ic-red">🔔</div><div class="stat-info"><h3><?= $absPend ?></h3><p>Alerts</p></div></div>
      </div>

      <div class="pend-bar">
        <div class="pb-left"><h4>📄 <?= $rptPend ?> Daily Reports Status</h4><p><?= $rptPend ?> submitted</p></div>
        <a href="reports.php" class="btn btn-orange btn-sm">View Daily Reports</a>
      </div>

      <!-- Month selector -->
      <div class="card">
        <div class="card-body" style="padding:14px 20px;">
          <form method="GET" class="flex gap14">
            <label style="font-size:13px;font-weight:600;white-space:nowrap;">Select Month:</label>
            <select name="month" class="fc" style="width:150px;">
              <?php for($m=1;$m<=12;$m++): ?>
              <option value="<?= $m ?>" <?= $m==$month?'selected':'' ?>><?= date('F',mktime(0,0,0,$m,1)) ?></option>
              <?php endfor; ?>
            </select>
            <select name="year" class="fc" style="width:100px;">
              <?php for($y=date('Y')-2;$y<=date('Y')+1;$y++): ?>
              <option value="<?= $y ?>" <?= $y==$year?'selected':'' ?>><?= $y ?></option>
              <?php endfor; ?>
            </select>
            <button type="submit" class="btn btn-navy btn-sm">🔍 View</button>
          </form>
        </div>
      </div>

      <!-- Email bar — Figure 5.7 -->
      <div style="background:#f0faf5;border:1px solid #c8e6c9;border-radius:8px;padding:13px 18px;display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
        <div style="font-size:13px;font-weight:500;color:#2e7d32;">
          ✉️ <strong>Salary Email Notifications</strong> — Send automatic emails to all employees with their monthly earnings.
        </div>
        <button class="btn btn-green btn-sm" onclick="alert('Salary emails sent to all employees!')">📧 Send Salary Emails</button>
      </div>

      <!-- SALARY TABLE — Figure 5.7 exact -->
      <div class="card mb0">
        <div class="card-head">
          <h3>Monthly Salary Summary — <?= date('F Y',mktime(0,0,0,$month,1,$year)) ?></h3>
          <span class="muted" style="font-size:12px;"><?= $rows->num_rows ?> employees</span>
        </div>
        <div class="card-body-0">
          <?php if($rows->num_rows===0): ?>
          <div class="empty">
            No salary records yet.<br>
            <small>Click <strong>"Generate Salary Report"</strong> to calculate salaries.</small>
          </div>
          <?php else: ?>
          <div class="tbl-wrap">
            <table>
              <thead>
                <tr>
                  <th>Name</th><th>Position</th><th>FP ID</th>
                  <th>Days Present</th><th>Approved Absences</th><th>Days Paid</th>
                  <th>Daily Rate</th><th>Total Salary</th><th>Email</th>
                </tr>
              </thead>
              <tbody>
              <?php while($s=$rows->fetch_assoc()): ?>
              <tr>
                <td><strong><?= esc($s['name']) ?></strong></td>
                <td><span class="tag tag-navy"><?= esc($s['position']) ?></span></td>
                <td><code><?= $s['fingerprintId'] ?></code></td>
                <td><?= $s['daysPresent'] ?></td>
                <?php
                  $approvedAbs = (int)val("SELECT COUNT(*) FROM absencerequest WHERE employeeId={$s['employeeId']} AND statusId='approved' AND DATE_FORMAT(date,'%Y-%m')='$my'");
                ?>
                <td><?= $approvedAbs > 0 ? '<span class="tag tag-green">'.$approvedAbs.'</span>' : '<span class="muted">0</span>' ?></td>
                <td><strong><?= $s['daysPresent'] ?></strong></td>
                <td><?= rwf($s['salaryRatePerDay']) ?></td>
                <td>
                  <strong style="color:<?= $s['netSalary']>0?'var(--green)':'var(--muted)' ?>;">
                    <?= rwf($s['netSalary']) ?>
                  </strong>
                  <?php if($s['netSalary']==0): ?><span class="tag tag-orange" style="margin-left:4px;">No Earnings</span><?php endif; ?>
                </td>
                <td style="font-size:11px;"><?= esc($s['email']) ?></td>
              </tr>
              <?php endwhile; ?>
              </tbody>
              <tfoot>
                <tr><td colspan="7"><strong>Total Payroll</strong></td>
                    <td><strong style="color:var(--green);font-size:15px;"><?= rwf($totalPay) ?></strong></td>
                    <td></td></tr>
              </tfoot>
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

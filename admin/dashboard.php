<?php
require_once '../config.php'; 
if(!function_exists('rwf')){
    function rwf($amount){ return number_format((float)$amount,0,'.',',').' RWF'; }
}authAdmin();
$totalEmp  = val("SELECT COUNT(*) FROM employee");
$sellers   = val("SELECT COUNT(*) FROM employee WHERE position='Seller'");
$delivery  = val("SELECT COUNT(*) FROM employee WHERE position='Delivery Person'");
$storeKeep = val("SELECT COUNT(*) FROM employee WHERE position='Store Keeper'");
$rptPend   = val("SELECT COUNT(*) FROM dailyreport WHERE justificationStatus='pending'");
$absPend   = val("SELECT COUNT(*) FROM absencerequest WHERE statusId='pending'");
$belowTgt  = val("SELECT COUNT(*) FROM dailyreport WHERE totalSalesAmount < dailyTarget AND DATE(submittedAt)=CURDATE()");
$recentRpts= db()->query("SELECT dr.*,e.name,e.position FROM dailyreport dr JOIN employee e ON e.id=dr.employeeId ORDER BY dr.submittedAt DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Dashboard – Smart Employee Management</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="layout">
  <?php include '_sb.php'; ?>
  <div class="main">

    <!-- TOPBAR — Figure 5.4 exact -->
    <div class="topbar">
      <div class="topbar-left">
        <h1>HUMAN RESOURCE (ADMIN)</h1>
      </div>
      <div class="topbar-right">
        <a href="employees.php" class="btn btn-blue btn-sm">➕ Enroll New Employee</a>
        <a href="employees.php" class="btn btn-red btn-sm">👁 Employee Portal</a>
        <a href="reports.php"   class="btn btn-orange btn-sm">📄 View Daily Reports</a>
        <a href="absences.php"  class="btn btn-navy btn-sm">🗓 New Absence</a>
        <a href="dashboard.php" class="btn btn-teal btn-sm">🔄 Refresh</a>
      </div>
    </div>

    <div class="page">

      <!-- ROW 1: 5 main stat cards — Figure 5.4 top row -->
      <div class="stats-row">
        <div class="stat-card">
          <div class="stat-icon ic-navy">👥</div>
          <div class="stat-info">
            <h3><?= $totalEmp ?></h3>
            <p>Total Employees</p>
            <small>Active in system</small>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon ic-orange">📄</div>
          <div class="stat-info">
            <h3><?= $rptPend ?></h3>
            <p>Reports Submitted</p>
            <small><?= $rptPend ?> pending</small>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon ic-red">⚠️</div>
          <div class="stat-info">
            <h3><?= $belowTgt ?></h3>
            <p>Vendor Review</p>
            <small>Below sales target</small>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon ic-green">📦</div>
          <div class="stat-info">
            <h3><?= $storeKeep ?></h3>
            <p>Store Employees</p>
            <small>Managing inventory</small>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon ic-teal">🚚</div>
          <div class="stat-info">
            <h3><?= $sellers + $delivery ?></h3>
            <p>Total Staff</p>
            <small>Sellers &amp; Delivery</small>
          </div>
        </div>
      </div>

<div class="stats-row" style="grid-template-columns:repeat(3,1fr);">
  <div class="stat-card" style="background:#e65100;box-shadow:0 4px 15px rgba(230,81,0,.3);">
    <div class="stat-icon" style="background:rgba(255,255,255,.2);font-size:24px;width:52px;height:52px;border-radius:12px;display:flex;align-items:center;justify-content:center;">📋</div>
    <div class="stat-info">
      <h3 style="color:#fff;font-size:28px;">1</h3>
      <p style="color:rgba(255,255,255,.9);font-size:13px;font-weight:600;">Stock Reports</p>
    </div>
  </div>
  <div class="stat-card" style="background:#1565c0;box-shadow:0 4px 15px rgba(21,101,192,.3);">
    <div class="stat-icon" style="background:rgba(255,255,255,.2);font-size:24px;width:52px;height:52px;border-radius:12px;display:flex;align-items:center;justify-content:center;">🚗</div>
    <div class="stat-info">
      <h3 style="color:#fff;font-size:28px;"><?= $delivery ?></h3>
      <p style="color:rgba(255,255,255,.9);font-size:13px;font-weight:600;">Delivery Persons</p>
    </div>
  </div>
  <div class="stat-card" style="background:#2e7d32;box-shadow:0 4px 15px rgba(46,125,50,.3);">
    <div class="stat-icon" style="background:rgba(255,255,255,.2);font-size:24px;width:52px;height:52px;border-radius:12px;display:flex;align-items:center;justify-content:center;">🔔</div>
    <div class="stat-info">
      <h3 style="color:#fff;font-size:28px;"><?= $absPend ?></h3>
      <p style="color:rgba(255,255,255,.9);font-size:13px;font-weight:600;">Alerts</p>
    </div>
  </div>
</div>

      <!-- PENDING BAR -->
      <?php if($rptPend > 0): ?>
      <div class="pend-bar">
        <div class="pb-left">
          <h4>📄 <?= $rptPend ?> Daily Report<?= $rptPend>1?'s':'' ?> Pending</h4>
          <p><?= $rptPend ?> submitted today</p>
        </div>
        <a href="reports.php" class="btn btn-orange btn-sm">View Daily Reports</a>
      </div>
      <?php endif; ?>

      <!-- RECENT WORK REPORTS TABLE -->
      <div class="card mb0">
        <div class="card-head">
          <h3>📋 Recent Work Reports (Today)</h3>
          <span class="tag tag-navy"><?= $recentRpts->num_rows ?> records</span>
        </div>
        <div class="card-body-0">
          <?php if($recentRpts->num_rows === 0): ?>
          <p class="empty">No reports submitted today.</p>
          <?php else: ?>
          <div class="tbl-wrap">
            <table>
              <thead>
                <tr>
                  <th>Employee</th><th>Position</th>
                  <th>Sales Amount</th><th>Daily Target</th>
                  <th>Performance</th><th>Submitted</th>
                </tr>
              </thead>
              <tbody>
              <?php while($r=$recentRpts->fetch_assoc()):
                $met=$r['totalSalesAmount']>=$r['dailyTarget'];
              ?>
              <tr>
                <td><strong><?= esc($r['name']) ?></strong></td>
                <td><span class="tag tag-navy"><?= esc($r['position']) ?></span></td>
                <td>$<?= number_format($r['totalSalesAmount'],2) ?></td>
                <td>$<?= number_format($r['dailyTarget'],2) ?></td>
                <td><span class="tag <?= $met?'tag-green':'tag-red' ?>"><?= $met?'✅ Above Target':'⚠️ Below Target' ?></span></td>
                <td class="muted nowrap"><?= date('h:i A',strtotime($r['submittedAt'])) ?></td>
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

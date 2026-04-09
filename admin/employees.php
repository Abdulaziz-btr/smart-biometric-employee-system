<?php
require_once '../config.php'; authAdmin();
if(!function_exists('rwf')){
    function rwf($amount){ return number_format((float)$amount,0,'.',',').' RWF'; }
}
$msg=$err='';

if(isset($_GET['del'])){
    db()->query("DELETE FROM employee WHERE id=".(int)$_GET['del']);
    header('Location:employees.php?ok=del');exit;
}
if(isset($_GET['ok'])&&$_GET['ok']==='del') $msg='🗑️ Employee deleted.';

if(isset($_POST['add'])){
    $n=xss($_POST['name']);$po=xss($_POST['position']);$em=xss($_POST['email']);
    $s=(float)$_POST['salary'];$tg=(float)$_POST['target'];$fp=(int)$_POST['fp_id'];
    if(row("SELECT id FROM employee WHERE fingerprintId=$fp OR email='$em'")){
        $err='Fingerprint ID or Email already registered.';
    } else {
        db()->query("INSERT INTO employee (fingerprintId,name,position,email,salaryRatePerDay,dailySalesTarget) VALUES ($fp,'$n','$po','$em',$s,$tg)");
        $msg="✅ Employee '$n' enrolled successfully.";
    }
}
if(isset($_POST['ot'])){
    $eid=(int)$_POST['emp_id'];$hrs=(float)$_POST['hours'];$rsn=xss($_POST['reason']??'');
    $rt=(float)val("SELECT salaryRatePerDay FROM employee WHERE id=$eid");
    $amt=$hrs*($rt/8);
    db()->query("INSERT INTO overtimerecord (employeeId,hours,amount,reason,month,year) VALUES ($eid,$hrs,$amt,'$rsn',MONTH(NOW()),YEAR(NOW()))");
    $msg='✅ Overtime recorded.';
}

$rptPend = val("SELECT COUNT(*) FROM dailyreport WHERE justificationStatus='pending'");
$absPend = val("SELECT COUNT(*) FROM absencerequest WHERE statusId='pending'");
$delivery= val("SELECT COUNT(*) FROM employee WHERE position='Delivery Person'");
$emps    = db()->query("SELECT * FROM employee ORDER BY id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Employees – Smart Employee Management</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="layout">
  <?php include '_sb.php'; ?>
  <div class="main">

    <div class="topbar">
      <div class="topbar-left">
        <h1>👥 Manage Employees</h1>
        <div class="sub-info">Manage enrolled employees, assign positions, manage overtime, and track attendance.</div>
      </div>
      <div class="topbar-right">
        <button class="btn btn-blue btn-sm" onclick="openModal('addModal')">➕ Enroll New Employee</button>
      </div>
    </div>

    <div class="page">
      <?php if($msg): ?><div class="alert alert-ok"><?= $msg ?></div><?php endif; ?>
      <?php if($err): ?><div class="alert alert-err"><?= $err ?></div><?php endif; ?>

      <div class="stats-row" style="grid-template-columns:repeat(3,1fr);max-width:540px;">
        <div class="stat-card"><div class="stat-icon ic-orange">📋</div><div class="stat-info"><h3>1</h3><p>Stock Reports</p></div></div>
        <div class="stat-card"><div class="stat-icon ic-blue">🚗</div><div class="stat-info"><h3><?= $delivery ?></h3><p>Delivery Persons</p></div></div>
        <div class="stat-card"><div class="stat-icon ic-red">🔔</div><div class="stat-info"><h3><?= $absPend ?></h3><p>Alerts</p></div></div>
      </div>

      <div class="pend-bar">
        <div class="pb-left"><h4>📄 <?= $rptPend ?> Daily Reports Status</h4><p><?= $rptPend ?> submitted</p></div>
        <a href="reports.php" class="btn btn-orange btn-sm">View Daily Reports</a>
      </div>

      <!-- EMPLOYEES TABLE — Figure 5.6 exact -->
      <div class="card mb0">
        <div class="card-head">
          <h3>Enrolled Employees &nbsp;<span class="tag tag-navy">Total: <?= $emps->num_rows ?></span></h3>
          <div class="flex gap6">
            <a href="attendance.php" class="btn btn-blue btn-sm">Mass Scan Manager</a>
            <a href="employees.php"  class="btn btn-gray btn-sm">View All</a>
          </div>
        </div>
        <div class="card-body-0">
          <div class="tbl-wrap">
            <table>
              <thead>
                <tr><th>#</th><th>Name</th><th>Position</th><th>Email</th><th>Fingerprint ID</th><th>Daily Salary</th><th>Actions</th></tr>
              </thead>
              <tbody>
              <?php $i=1;while($e=$emps->fetch_assoc()): ?>
              <tr>
                <td><?= $i++ ?></td>
                <td><strong><?= esc($e['name']) ?></strong></td>
                <td><span class="tag tag-navy"><?= esc($e['position']) ?></span></td>
                <td style="font-size:12px;"><?= esc($e['email']) ?></td>
                <td><code><?= $e['fingerprintId'] ?></code></td>
                <td><?= rwf($e['salaryRatePerDay']) ?></td>
                <td>
                  <div class="flex gap6" style="flex-wrap:wrap;">
                    <div class="flex gap6" style="flex-wrap:nowrap;">
                      <a href="edit_emp.php?id=<?= $e['id'] ?>" class="btn btn-xs" style="background:#f59e0b;color:#fff;">✏️ Edit</a>
                      <a href="?del=<?= $e['id'] ?>" class="btn btn-red btn-xs" onclick="return confirm('Delete <?= esc($e['name']) ?>?')">🗑 Delete</a>
                      <a href="emp_logs.php?id=<?= $e['id'] ?>" class="btn btn-xs" style="background:#1565c0;color:#fff;">📋 View Logs</a>
                      <button class="btn btn-xs" style="background:#2e7d32;color:#fff;" onclick="openOT(<?= $e['id'] ?>,'<?= esc($e['name']) ?>')">⏰ Set Overtime</button>
                    </div>
                  </div>
                </td>
              </tr>
              <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- ADD EMPLOYEE MODAL -->
<div class="modal-bg" id="addModal">
  <div class="modal-box">
    <h3>➕ Enroll New Employee</h3>
    <form method="POST">
      <div class="row2">
        <div class="fg"><label>Full Name</label><input type="text" name="name" class="fc" required></div>
        <div class="fg"><label>Position</label>
          <select name="position" class="fc" required>
            <option value="Seller">Seller</option>
            <option value="Delivery Person">Delivery Person</option>
            <option value="Store Keeper">Store Keeper</option>
            <option value="Manager">Manager</option>
          </select>
        </div>
      </div>
      <div class="fg"><label>Email Address</label><input type="email" name="email" class="fc" required></div>
      <div class="row3">
        <div class="fg"><label>Fingerprint ID</label><input type="number" name="fp_id" class="fc" required></div>
        <div class="fg"><label>Daily Rate ($)</label><input type="number" name="salary" step="0.01" value="10.00" class="fc"></div>
        <div class="fg"><label>Sales Target ($)</label><input type="number" name="target" step="0.01" value="500.00" class="fc"></div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-gray" onclick="closeModal('addModal')">Cancel</button>
        <button type="submit" name="add" class="btn btn-navy">✅ Enroll Employee</button>
      </div>
    </form>
  </div>
</div>

<!-- OVERTIME MODAL -->
<div class="modal-bg" id="otModal">
  <div class="modal-box" style="max-width:420px;">
    <h3>⏰ Set Overtime</h3>
    <form method="POST">
      <input type="hidden" name="emp_id" id="ot_id">
      <div class="fg"><label>Employee</label><input type="text" id="ot_nm" class="fc" readonly></div>
      <div class="row2">
        <div class="fg"><label>Overtime Hours</label><input type="number" name="hours" step="0.5" class="fc" required></div>
        <div class="fg"><label>Reason</label><input type="text" name="reason" class="fc"></div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-gray" onclick="closeModal('otModal')">Cancel</button>
        <button type="submit" name="ot" class="btn btn-orange">✅ Save Overtime</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id){document.getElementById(id).classList.add('open');}
function closeModal(id){document.getElementById(id).classList.remove('open');}
function openOT(id,n){document.getElementById('ot_id').value=id;document.getElementById('ot_nm').value=n;openModal('otModal');}
document.querySelectorAll('.modal-bg').forEach(m=>m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('open');}));
</script>
</body>
</html>

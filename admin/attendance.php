<?php
require_once '../config.php'; authAdmin();
$msg = $err = '';
if(!function_exists('rwf')){
    function rwf($amount){ return number_format((float)$amount,0,'.',',').' RWF'; }
}

// ── Use MySQL's date (avoids PHP timezone mismatch) ───────
$mysqlNow  = row("SELECT CURDATE() AS d, NOW() AS n");
$today     = $mysqlNow['d'];              // e.g. 2026-03-26
$date      = $_GET['date'] ?? $today;

// ── Manual scan ───────────────────────────────────────────
if (isset($_POST['do_scan'])) {
    $fp = (int)$_POST['fp_id'];
    $e  = row("SELECT * FROM employee WHERE fingerprintId=$fp");
    if ($e) {
        db()->query("INSERT INTO attendancelog (employeeId,fingerprintId,status) VALUES ({$e['id']},$fp,'present')");
        $msg = "✅ Attendance recorded for <strong>".esc($e['name'])."</strong> at ".date('h:i A');
    } else {
        $err = "❌ No employee found with Fingerprint ID: $fp";
    }
}

// ── Stats ─────────────────────────────────────────────────
$rptPend    = val("SELECT COUNT(*) FROM dailyreport WHERE justificationStatus='pending'");
$absPend    = val("SELECT COUNT(*) FROM absencerequest WHERE statusId='pending'");
$delivery   = val("SELECT COUNT(*) FROM employee WHERE position='Delivery Person'");
$totalAll   = val("SELECT COUNT(*) FROM attendancelog");
$totalToday = val("SELECT COUNT(*) FROM attendancelog WHERE DATE(timestamp)='$date'");

// ── Metrics ───────────────────────────────────────────────
$lateArr = $earlyDep = $perfect = $overtime = $breaks = 0;
$tmp = db()->query("SELECT timestamp FROM attendancelog WHERE DATE(timestamp)='$date'");
while ($t = $tmp->fetch_assoc()) {
    if ((int)date('H', strtotime($t['timestamp'])) >= 9) $lateArr++;
}

// ── Main logs query ───────────────────────────────────────
$logs = db()->query("
    SELECT al.*, e.name, e.position
    FROM attendancelog al
    JOIN employee e ON e.id = al.employeeId
    WHERE DATE(al.timestamp) = '$date'
    ORDER BY al.timestamp DESC
");

// ── All dates that have records (for date picker hint) ────
$datesWithData = db()->query("SELECT DISTINCT DATE(timestamp) AS d FROM attendancelog ORDER BY d DESC LIMIT 10");
$allDates = [];
while ($d = $datesWithData->fetch_assoc()) $allDates[] = $d['d'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Attendance – Smart Employee Management</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="layout">
  <?php include '_sb.php'; ?>
  <div class="main">

    <!-- TOPBAR — Figure 5.5 -->
    <div class="topbar">
      <div class="topbar-left">
        <h1>📋 Today's Attendance Logs</h1>
        <div class="sub-info">
          🕐 Server Time: <?= date('h:i:s A') ?>
          &nbsp;|&nbsp; 📅 MySQL Date: <strong><?= $today ?></strong>
          &nbsp;|&nbsp; Shift: 8:00 AM → 5:00 PM
        </div>
      </div>
      <div class="topbar-right">
        <button class="btn btn-navy btn-sm" onclick="openModal('scanModal')">▶ Start Continuous Scan</button>
        <button class="btn btn-gray btn-sm" onclick="openModal('scanModal')">🔍 Single Scan</button>
        <a href="attendance.php" class="btn btn-teal btn-sm">🔄 Refresh</a>
      </div>
    </div>

    <div class="page">

      <?php if($msg): ?><div class="alert alert-ok"><?= $msg ?></div><?php endif; ?>
      <?php if($err): ?><div class="alert alert-err"><?= $err ?></div><?php endif; ?>

      <!-- DEBUG INFO — shows if date mismatch is the problem -->
      <?php if ($totalAll > 0 && $totalToday == 0): ?>
      <div class="alert alert-warn" style="flex-direction:column;align-items:flex-start;gap:6px;">
        <strong>⚠️ Records exist but not showing for date: <?= $date ?></strong>
        <span>Total records in database: <strong><?= $totalAll ?></strong></span>
        <span>Dates with records:
          <?php foreach($allDates as $d): ?>
          <a href="?date=<?= $d ?>" style="margin-right:8px;color:var(--navy);font-weight:700;">📅 <?= $d ?></a>
          <?php endforeach; ?>
        </span>
        <span style="font-size:11px;">Click a date above to view its records.</span>
      </div>
      <?php endif; ?>

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


      <!-- Pending Bar -->
      <div class="pend-bar">
        <div class="pb-left">
          <h4>📄 <?= $rptPend ?> Daily Reports Status</h4>
          <p><?= $rptPend ?> submitted today</p>
        </div>
        <a href="reports.php" class="btn btn-orange btn-sm">View Daily Reports</a>
      </div>

      <!-- ATTENDANCE METRICS — Figure 5.5 -->
      <div class="att-metrics">
        <div class="att-metric">
          <h3 style="color:var(--orange);"><?= $overtime ?></h3>
          <div class="m-label">⏰ Overtime Today</div>
          <div class="m-sub">Includes pending &amp; extra overtime<br>Click to filter</div>
        </div>
        <div class="att-metric">
          <h3 style="color:var(--red);"><?= $lateArr ?></h3>
          <div class="m-label">⚠️ Late Arrivals</div>
          <div class="m-sub">Arrived after 9:00 AM grace period<br>Click to filter</div>
        </div>
        <div class="att-metric">
          <h3 style="color:var(--blue);"><?= $earlyDep ?></h3>
          <div class="m-label">🏃 Early Departures</div>
          <div class="m-sub">Left before 5:00 PM end time<br>Click to filter</div>
        </div>
        <div class="att-metric">
          <h3 style="color:var(--purple);"><?= $breaks ?></h3>
          <div class="m-label">☕ Break Scans</div>
          <div class="m-sub">Lunch &amp; break check-ins/outs<br>Click to filter</div>
        </div>
        <div class="att-metric">
          <h3 style="color:var(--green);"><?= $perfect ?></h3>
          <div class="m-label">⭐ Perfect Attendance</div>
          <div class="m-sub">On-time arrival &amp; complete shift<br>Click to filter</div>
        </div>
      </div>

      <!-- Date Filter -->
      <div class="card">
        <div class="card-body" style="padding:14px 20px;">
          <form method="GET" class="flex gap14" style="flex-wrap:wrap;align-items:center;">
            <label style="font-size:13px;font-weight:600;white-space:nowrap;">📅 Filter Date:</label>
            <input type="date" name="date" value="<?= $date ?>" class="fc" style="width:190px;">
            <button type="submit" class="btn btn-navy btn-sm">Filter</button>
            <a href="attendance.php" class="btn btn-gray btn-sm">Today (<?= $today ?>)</a>
            <!-- Quick links to dates with actual data -->
            <?php if (!empty($allDates)): ?>
            <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center;">
              <span style="font-size:11px;color:var(--muted);">Jump to:</span>
              <?php foreach(array_slice($allDates,0,5) as $d): ?>
              <a href="?date=<?= $d ?>" class="btn btn-outline btn-xs <?= $d===$date?'btn-navy':'' ?>"><?= $d ?></a>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>
          </form>
        </div>
      </div>

      <!-- Attendance Table -->
      <div class="card mb0">
        <div class="card-head">
          <h3>Attendance Records — <?= date('D, F d Y', strtotime($date)) ?></h3>
          <div class="flex gap6">
            <span class="tag tag-navy"><?= $logs->num_rows ?> records today</span>
            <span class="tag tag-gray"><?= $totalAll ?> total all time</span>
          </div>
        </div>
        <div class="card-body-0">

          <?php if ($totalAll == 0): ?>
          <!-- No records at all in database -->
          <div style="text-align:center;padding:40px;color:var(--muted);">
            <div style="font-size:36px;margin-bottom:12px;">📋</div>
            <strong style="font-size:14px;color:var(--text);">No attendance records yet</strong>
            <p style="font-size:12px;margin-top:8px;">
              Simulate a scan by clicking <strong>Single Scan</strong> above<br>
              or open this URL in your browser:
            </p>
            <code style="font-size:11px;display:block;margin:10px auto;max-width:500px;padding:8px;text-align:left;">
              http://localhost/smart_employee/api/checkin.php?fp=1&secret=AVATA2026
            </code>
          </div>

          <?php elseif ($logs->num_rows == 0): ?>
          <!-- Records exist but not for this date -->
          <div style="text-align:center;padding:40px;color:var(--muted);">
            <div style="font-size:36px;margin-bottom:12px;">🔍</div>
            <strong style="font-size:14px;color:var(--text);">No records for <?= $date ?></strong>
            <p style="font-size:12px;margin-top:8px;">Records exist for these dates:</p>
            <div style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap;margin-top:10px;">
              <?php foreach($allDates as $d): ?>
              <a href="?date=<?= $d ?>" class="btn btn-navy btn-sm">📅 <?= $d ?></a>
              <?php endforeach; ?>
            </div>
          </div>

          <?php else: ?>
          <!-- Show records -->
          <div class="tbl-wrap">
            <table>
              <thead>
                <tr>
                  <th>Employee Name</th>
                  <th>Position</th>
                  <th>Scan Time</th>
                  <th>Date Stored</th>
                  <th>Status</th>
                  <th>FP ID</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
              <?php while($l = $logs->fetch_assoc()):
                $h    = (int)date('H', strtotime($l['timestamp']));
                $late = $h >= 9;
              ?>
              <tr>
                <td><strong><?= esc($l['name']) ?></strong></td>
                <td><span class="tag tag-navy"><?= esc($l['position']) ?></span></td>
                <td class="nowrap"><?= date('h:i:s A', strtotime($l['timestamp'])) ?></td>
                <td class="nowrap" style="font-size:11px;color:var(--muted);"><?= date('Y-m-d', strtotime($l['timestamp'])) ?></td>
                <td>
                  <span class="tag <?= $late ? 'tag-orange' : 'tag-green' ?>">
                    <?= $late ? '⚠️ Late' : '✅ On Time' ?>
                  </span>
                </td>
                <td><code><?= $l['fingerprintId'] ?></code></td>
                <td>
                  <a href="del_log.php?id=<?= $l['id'] ?>"
                     class="btn btn-red btn-xs"
                     onclick="return confirm('Delete this record?')">🗑</a>
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

<!-- SCAN MODAL -->
<div class="modal-bg" id="scanModal">
  <div class="modal-box" style="max-width:420px;">
    <h3>🔍 Record Fingerprint Attendance</h3>
    <p class="muted" style="font-size:12px;margin-bottom:16px;">
      Enter the employee's Fingerprint ID to record attendance manually.
    </p>
    <form method="POST">
      <div class="fg">
        <label>Fingerprint ID</label>
        <input type="number" name="fp_id" class="fc" placeholder="e.g., 1" required autofocus>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-gray" onclick="closeModal('scanModal')">Cancel</button>
        <button type="submit" name="do_scan" class="btn btn-navy">✅ Record Attendance</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-bg').forEach(m =>
  m.addEventListener('click', e => { if(e.target===m) m.classList.remove('open'); })
);
</script>
</body>
</html>
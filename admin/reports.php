<?php
require_once '../config.php'; authAdmin();
if(!function_exists('rwf')){
    function rwf($amount){ return number_format((float)$amount,0,'.',',').' RWF'; }
}
$msg = '';

if(isset($_POST['approve'])){ db()->query("UPDATE dailyreport SET justificationStatus='approved' WHERE id=".(int)$_POST['rid']); $msg='✅ Report approved.'; }
if(isset($_POST['reject'])){ $id=(int)$_POST['rid']; $note=xss($_POST['note']??''); db()->query("UPDATE dailyreport SET justificationStatus='rejected',justificationNote='$note' WHERE id=$id"); $msg='❌ Report rejected.'; }

// ── Get MySQL date ────────────────────────────────────────
$mysqlDate = row("SELECT CURDATE() AS d")->{'d'} ?? date('Y-m-d');
$mysqlRow  = row("SELECT CURDATE() AS d");
$mysqlDate = $mysqlRow['d'];

// ── Filters ───────────────────────────────────────────────
$pos    = xss($_GET['pos']    ?? '');
$status = xss($_GET['status'] ?? '');
$date   = $_GET['date'] ?? '';

$where = "WHERE 1";
if($pos)    $where .= " AND e.position='$pos'";
if($status) $where .= " AND dr.justificationStatus='$status'";
if($date)   $where .= " AND DATE(dr.submittedAt)='$date'";

// ── All dates with data ───────────────────────────────────
$datesQ   = db()->query("SELECT DISTINCT DATE(submittedAt) AS d FROM dailyreport ORDER BY d DESC LIMIT 10");
$allDates = [];
while($dd=$datesQ->fetch_assoc()) $allDates[]=$dd['d'];

$reports  = db()->query("SELECT dr.*,e.name,e.position FROM dailyreport dr JOIN employee e ON e.id=dr.employeeId $where ORDER BY dr.submittedAt DESC");
$pending  = val("SELECT COUNT(*) FROM dailyreport WHERE justificationStatus='pending'");
$delivery = val("SELECT COUNT(*) FROM employee WHERE position='Delivery Person'");
$absPend  = val("SELECT COUNT(*) FROM absencerequest WHERE statusId='pending'");
$totalAll = val("SELECT COUNT(*) FROM dailyreport");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Daily Reports – Smart Employee Management</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<style>
/* ── Figure 5.10 3-column report grid ── */
.rpt-grid {
  display: grid;
  grid-template-columns: repeat(3,1fr);
  gap: 14px;
  margin-bottom: 16px;
}
.rpt-col {
  background: #fff;
  border-radius: 10px;
  box-shadow: var(--shadow);
  overflow: hidden;
}
.rpt-col-head {
  padding: 11px 14px;
  border-bottom: 1px solid #f0f2f8;
}
.rpt-col-head .emp-name { font-size:14px; font-weight:700; color:var(--text); }
.rpt-col-head .emp-pos  { font-size:11px; color:var(--muted); margin-top:2px; }
.rpt-col-body { padding:11px 14px; }
.rpt-col-body .rpt-line { font-size:12px; color:var(--muted); margin-bottom:4px; }
.rpt-col-body .rpt-line strong { color:var(--text); }
.rpt-col-foot {
  padding:10px 14px;
  border-top:1px solid #f0f2f8;
  display:flex; gap:7px; justify-content:flex-end;
  align-items:center;
}
/* Pending badge on card header */
.rpt-status-pending { color:#e65100; font-size:11px; font-weight:700; }
.rpt-status-approved{ color:#2e7d32; font-size:11px; font-weight:700; }
.rpt-status-rejected{ color:#c62828; font-size:11px; font-weight:700; }
</style>
</head>
<body>
<div class="layout">
  <?php include '_sb.php'; ?>
  <div class="main">

    <div class="topbar">
      <div class="topbar-left"><h1>Daily Reports Management</h1></div>
      <div class="topbar-right">
        <a href="reports.php" class="btn btn-teal btn-sm">🔄 Refresh</a>
        <button class="btn btn-gray btn-sm" onclick="window.print()">🖨 Print a Summary</button>
      </div>
    </div>

    <div class="page">
      <?php if($msg): ?><div class="alert alert-ok"><?= $msg ?></div><?php endif; ?>

      <!-- FILTER BAR — Figure 5.10 -->
      <div class="card">
        <div class="card-body" style="padding:14px 18px;">
          <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
            <select name="pos" class="fc" style="width:150px;">
              <option value="">All Positions</option>
              <?php foreach(['Seller','Delivery Person','Store Keeper'] as $p): ?>
              <option value="<?= $p ?>" <?= $pos===$p?'selected':'' ?>><?= $p ?></option>
              <?php endforeach; ?>
            </select>
            <select name="status" class="fc" style="width:140px;">
              <option value="">All Statuses</option>
              <option value="pending"  <?= $status==='pending' ?'selected':'' ?>>Pending</option>
              <option value="approved" <?= $status==='approved'?'selected':'' ?>>Approved</option>
              <option value="rejected" <?= $status==='rejected'?'selected':'' ?>>Rejected</option>
            </select>
            <input type="date" name="date" value="<?= $date ?>" class="fc" style="width:160px;">
            <button type="submit" class="btn btn-navy btn-sm">Copy Plans</button>
            <a href="reports.php" class="btn btn-gray btn-sm">Reset</a>
            <!-- Quick date links -->
            <?php foreach(array_slice($allDates,0,4) as $dd): ?>
            <a href="?date=<?= $dd ?>" class="btn btn-outline btn-xs <?= $dd===$date?'btn-navy':'' ?>"><?= $dd ?></a>
            <?php endforeach; ?>
          </form>
        </div>
      </div>

      <?php if($pending>0): ?>
      <div class="pend-bar">
        <div class="pb-left">
          <h4>⏳ <?= $pending ?> report(s) awaiting review</h4>
        </div>
        <span class="tag tag-orange"><?= $pending ?> Pending</span>
      </div>
      <?php endif; ?>

      <?php if($reports->num_rows===0): ?>
      <!-- No reports -->
      <div class="card">
        <div style="text-align:center;padding:50px;color:var(--muted);">
          <div style="font-size:40px;margin-bottom:12px;">📄</div>
          <strong style="font-size:14px;color:var(--text);">No reports found</strong>
          <p style="font-size:12px;margin-top:8px;">
            <?php if(!empty($allDates)): ?>
            Reports exist for: <?php foreach($allDates as $dd): ?>
            <a href="?date=<?= $dd ?>" style="color:var(--navy);font-weight:700;margin:0 4px;"><?= $dd ?></a>
            <?php endforeach; ?>
            <?php else: ?>
            No reports submitted yet. Have employees submit their daily sales reports.
            <?php endif; ?>
          </p>
        </div>
      </div>

      <?php else: ?>

      <!-- REPORTS GRID — Figure 5.10 three columns -->
      <div class="rpt-grid">
        <?php while($r=$reports->fetch_assoc()):
          $met   = $r['totalSalesAmount'] >= $r['dailyTarget'];
          $stcls = $r['justificationStatus']==='approved'?'tag-green':($r['justificationStatus']==='rejected'?'tag-red':'tag-orange');
          $stlbl = $r['justificationStatus']==='approved'?'rpt-status-approved':($r['justificationStatus']==='rejected'?'rpt-status-rejected':'rpt-status-pending');
        ?>
        <div class="rpt-col">
          <!-- Employee name + status -->
          <div class="rpt-col-head">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
              <div>
                <div class="emp-name"><?= esc($r['name']) ?></div>
                <div class="emp-pos"><?= esc($r['position']) ?></div>
              </div>
              <span class="<?= $stlbl ?>"><?= ucfirst($r['justificationStatus']) ?></span>
            </div>
          </div>

          <!-- Report details -->
          <div class="rpt-col-body">
            <div class="rpt-line">Date: <strong><?= date('M d, Y',strtotime($r['date'])) ?></strong></div>
            <div class="rpt-line">Sales: <strong><?= rwf($r['totalSalesAmount']) ?></strong>
              &nbsp;
              <span class="tag <?= $met?'tag-green':'tag-red' ?>" style="font-size:10px;">
                <?= $met?'✅ Above Target':'⚠️ Below Target' ?>
              </span>
            </div>
            <div class="rpt-line">Items added: <strong><?= $r['itemsAddedCount'] ?></strong></div>
            <?php if($r['justificationNote']): ?>
            <div class="rpt-line">📝 <?= esc($r['justificationNote']) ?></div>
            <?php endif; ?>
            <?php if($r['justificationFile']): ?>
            <a href="#" style="font-size:11px;color:var(--navy);">📎 View Attachment</a>
            <?php endif; ?>
          </div>

          <!-- Approve / Reject buttons -->
          <?php if($r['justificationStatus']==='pending'): ?>
          <div class="rpt-col-foot">
            <form method="POST" style="display:inline;">
              <input type="hidden" name="rid" value="<?= $r['id'] ?>">
              <button name="approve" class="btn btn-green btn-xs">✅ Approve</button>
            </form>
            <button class="btn btn-red btn-xs" onclick="openReject(<?= $r['id'] ?>)">❌ Reject</button>
          </div>
          <?php else: ?>
          <div class="rpt-col-foot" style="justify-content:flex-start;">
            <span style="font-size:11px;color:var(--muted);">
              Submitted <?= date('h:i A',strtotime($r['submittedAt'])) ?>
            </span>
          </div>
          <?php endif; ?>
        </div>
        <?php endwhile; ?>
      </div>

      <?php endif; ?>

    </div>
  </div>
</div>

<!-- REJECT MODAL -->
<div class="modal-bg" id="rejectModal">
  <div class="modal-box" style="max-width:400px;">
    <h3>❌ Reject Report</h3>
    <form method="POST">
      <input type="hidden" name="rid" id="rej_id">
      <div class="fg">
        <label>Reason (optional)</label>
        <textarea name="note" class="fc" placeholder="Enter rejection reason..."></textarea>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-gray" onclick="closeModal('rejectModal')">Cancel</button>
        <button type="submit" name="reject" class="btn btn-red">❌ Reject</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id){ document.getElementById(id).classList.remove('open'); }
function openReject(id){ document.getElementById('rej_id').value=id; openModal('rejectModal'); }
document.querySelectorAll('.modal-bg').forEach(m=>
  m.addEventListener('click',e=>{ if(e.target===m) m.classList.remove('open'); })
);
</script>
</body>
</html>

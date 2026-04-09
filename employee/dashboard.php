<?php
require_once '../config.php';
if(!function_exists('rwf')){
    function rwf($amount){ return number_format((float)$amount,0,'.',',').' RWF'; }
}
authEmp();

$eid = $_SESSION['emp_id'];
$emp = row("SELECT * FROM employee WHERE id=$eid");
$msg = '';

// ── Submit Sales Report ──────────────────────────────────
if (isset($_POST['sub_report'])) {
    $total  = (float)($_POST['manual_total'] ?? 0);
    $count  = (int)($_POST['items_count_hidden'] ?? 0);
    $target = (float)$emp['dailySalesTarget'];
    $today  = date('Y-m-d');
    $ex     = row("SELECT id FROM dailyreport WHERE employeeId=$eid AND date='$today'");
    if ($ex) {
        db()->query("UPDATE dailyreport SET totalSalesAmount=$total,itemsAddedCount=$count,submittedAt=NOW() WHERE id={$ex['id']}");
        $msg = '✅ Sales report updated successfully.';
    } else {
        db()->query("INSERT INTO dailyreport (employeeId,date,totalSalesAmount,dailyTarget,itemsAddedCount) VALUES ($eid,'$today',$total,$target,$count)");
        $msg = '✅ Sales report submitted successfully.';
    }
}

// ── Submit Absence ────────────────────────────────────────
if (isset($_POST['sub_absence'])) {
    $adate  = xss($_POST['abs_date']);
    $reason = xss($_POST['abs_reason']);
    db()->query("INSERT INTO absencerequest (employeeId,date,reason) VALUES ($eid,'$adate','$reason')");
    $msg = '✅ Absence request submitted. Admin will review.';
}

// ── Stats ─────────────────────────────────────────────────
$my          = date('Y-m');
$daysPresent = (int)val("SELECT COUNT(DISTINCT DATE(timestamp)) FROM attendancelog WHERE employeeId=$eid AND DATE_FORMAT(timestamp,'%Y-%m')='$my'");
$paidAbs     = (int)val("SELECT COUNT(*) FROM absencerequest WHERE employeeId=$eid AND statusId='approved' AND DATE_FORMAT(date,'%Y-%m')='$my'");
$totalPaid   = $daysPresent + $paidAbs;
$salRow      = row("SELECT * FROM salaryrecord WHERE employeeId=$eid AND monthYear='$my'");
$monthlySal  = $salRow ? $salRow['netSalary'] : 0;
$reportToday = row("SELECT * FROM dailyreport WHERE employeeId=$eid AND date=CURDATE()");
$totalRpts   = (int)val("SELECT COUNT(*) FROM dailyreport WHERE employeeId=$eid");
$recentRpts  = db()->query("SELECT * FROM dailyreport WHERE employeeId=$eid ORDER BY submittedAt DESC LIMIT 5");

// ── Recent absence requests ──────────────────────────────
$recentAbs = db()->query("SELECT * FROM absencerequest WHERE employeeId=$eid ORDER BY createdAt DESC LIMIT 3");

// ── Inventory items ───────────────────────────────────────
$invResult = db()->query("SELECT * FROM inventoryitem ORDER BY name");
$inv = [];
while ($it = $invResult->fetch_assoc()) $inv[] = $it;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Employee Dashboard – Smart Employee Management</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<style>
/* ── Figure 5.8 Colored Stat Cards ── */
.emp-stat-green  { background:#2e7d32 !important; }
.emp-stat-blue   { background:#1a237e !important; }
.emp-stat-purple { background:#6a1b9a !important; }
.emp-stat-orange { background:#e65100 !important; }
.emp-stat-green h3, .emp-stat-green p,
.emp-stat-blue h3,  .emp-stat-blue p,
.emp-stat-purple h3,.emp-stat-purple p,
.emp-stat-orange h3,.emp-stat-orange p { color:#fff !important; }
.emp-stat-icon {
  width:44px; height:44px; border-radius:50%;
  background:rgba(255,255,255,.2) !important;
  display:flex; align-items:center; justify-content:center;
  font-size:20px; flex-shrink:0;
}

/* ── Figure 5.8 Orange Profile Banner ── */
.emp-banner-orange {
  background: linear-gradient(135deg, #e65100 0%, #f57c00 100%);
  color:#fff; border-radius:10px; padding:18px 22px;
  display:flex; align-items:center; justify-content:space-between;
  margin-bottom:14px; box-shadow:0 4px 16px rgba(230,81,0,.3);
}
.emp-banner-orange .eb-left p  { font-size:11px; opacity:.85; margin-bottom:4px; }
.emp-banner-orange .eb-left h2 { font-size:20px; font-weight:800; }
.emp-banner-orange .eb-icon    { font-size:38px; opacity:.9; }

/* ── Figure 5.8 Daily Warning ── */
.daily-warn-fig {
  background:#fff9e6; border:1px solid #ffe082;
  border-left:3px solid #f9a825; border-radius:6px;
  padding:10px 14px; margin-bottom:14px;
  font-size:12px; color:#795548; display:flex;
  align-items:flex-start; gap:8px;
}
.daily-warn-fig strong { display:block; font-size:13px; color:#5d4037; margin-bottom:2px; }

/* ── Figure 5.8 Action Tiles ── */
.emp-tiles {
  display:grid; grid-template-columns:repeat(4,1fr);
  gap:12px; margin-bottom:16px;
}
.emp-tile {
  background:#fff; border-radius:10px; padding:20px 12px;
  text-align:center; cursor:pointer; border:none;
  font-family:var(--font); box-shadow:var(--shadow);
  transition:transform .15s,box-shadow .15s;
  display:flex; flex-direction:column; align-items:center;
  text-decoration:none; color:var(--text);
}
.emp-tile:hover { transform:translateY(-3px); box-shadow:var(--shadow2); }
.emp-tile .t-icon { font-size:28px; margin-bottom:10px; }
.emp-tile .t-title { font-size:12px; font-weight:700; display:block; }
.emp-tile .t-sub   { font-size:11px; color:var(--muted); margin-top:3px; display:block; }

/* ── Inventory modal (Figure 5.9 dark bg) ── */
.inv-modal-inner { background:#111827; border-radius:10px; padding:0; overflow:hidden; }
.inv-modal-header { background:#1f2937; padding:14px 18px; display:flex; justify-content:space-between; align-items:center; }
.inv-modal-header h3 { color:#fff; font-size:15px; font-weight:700; margin:0; }
.inv-modal-header button { background:transparent; border:none; color:#9ca3af; font-size:20px; cursor:pointer; line-height:1; }
.inv-subtitle { color:#9ca3af; font-size:12px; padding:10px 18px 6px; }
.inv-scroll { max-height:220px; overflow-y:auto; }
.inv-row {
  display:flex; align-items:center; justify-content:space-between;
  padding:10px 18px; border-bottom:1px solid #1f2937;
  cursor:pointer; transition:background .12s; gap:10px;
}
.inv-row:last-child { border-bottom:none; }
.inv-row:hover { background:#1f2937; }
.inv-row.picked { background:#1e3a5f; }
.inv-row .iname { font-size:13px; color:#e5e7eb; flex:1; }
.inv-row .iprice { font-size:13px; color:#e5e7eb; white-space:nowrap; }
.qty-wrap { display:flex; align-items:center; gap:5px; flex-shrink:0; }
.qbtn {
  width:24px; height:24px; border-radius:50%;
  border:1.5px solid #4b5563; background:transparent;
  cursor:pointer; font-size:14px; font-weight:800;
  color:#e5e7eb; display:flex; align-items:center;
  justify-content:center; transition:all .13s;
}
.qbtn:hover { background:#374151; border-color:#6b7280; }
.qnum { font-size:13px; font-weight:700; min-width:20px; text-align:center; color:#fff; }
.inv-total-bar {
  background:#1f2937; padding:12px 18px;
  display:flex; justify-content:space-between; align-items:center;
  border-top:1px solid #374151;
}
.inv-total-bar .tl { color:#9ca3af; font-size:12px; }
.inv-total-bar .tl span { display:block; font-size:10px; color:#6b7280; margin-top:2px; }
.inv-total-bar .ta { font-size:20px; font-weight:800; color:#fff; }
.inv-submit-row { padding:12px 18px 16px; }
.inv-submit-btn {
  width:100%; padding:11px; background:#1d4ed8; color:#fff;
  border:none; border-radius:7px; font-size:13px; font-weight:700;
  cursor:pointer; font-family:var(--font); transition:background .15s;
}
.inv-submit-btn:hover { background:#1e40af; }
.inv-submit-btn:disabled { background:#374151; color:#6b7280; cursor:not-allowed; }

/* ── Override modal-bg for dark version ── */
.modal-dark .modal-box {
  background:transparent !important;
  padding:0 !important;
  box-shadow:0 20px 60px rgba(0,0,0,.6) !important;
  max-width:380px;
}

/* ── Recent reports card ── */
.rpt-item {
  padding:13px 16px; border-bottom:1px solid #f0f2f8;
  display:flex; justify-content:space-between; align-items:flex-start; gap:10px;
}
.rpt-item:last-child { border-bottom:none; }
</style>
</head>
<body>
<div class="layout">
  <?php include '_sb.php'; ?>
  <div class="main">

    <!-- TOPBAR — Figure 5.8 exact -->
    <div class="topbar">
      <div class="topbar-left">
        <h1>Welcome, <?= esc($emp['name']) ?>! 🔔</h1>
        <div style="display:flex;align-items:center;gap:8px;margin-top:3px;">
          <code style="font-size:11px;background:#e8eaf6;padding:2px 7px;border-radius:4px;color:#1a237e;">ID: <?= $emp['fingerprintId'] ?></code>
          <span style="background:#e8f5e9;color:#2e7d32;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:700;">🚀 <?= esc($emp['position']) ?></span>
        </div>
      </div>
      <div class="topbar-right">
        <button class="btn btn-sm" onclick="openModal('rptModal')"
          style="background:#1565c0;color:#fff;">📊 Submit Sales Report</button>
        <button class="btn btn-sm" onclick="location.reload()"
          style="background:#2e7d32;color:#fff;">🔄 Refresh</button>
        <a href="../logout.php" class="btn btn-sm"
          style="background:#c62828;color:#fff;">🚪 Logout</a>
      </div>
    </div>

    <div class="page">

      <?php if($msg): ?>
      <div class="alert alert-ok"><?= $msg ?></div>
      <?php endif; ?>

      <!-- ORANGE PROFILE BANNER — Figure 5.8 -->
      <div class="emp-banner-orange">
        <div class="eb-left">
          <p>Your Position</p>
          <h2><?= esc($emp['position']) ?></h2>
        </div>
        <div class="eb-icon">
          <?php
          $icons = ['Seller'=>'🛒','Delivery Person'=>'🚚','Store Keeper'=>'📦','Manager'=>'👔'];
          echo $icons[$emp['position']] ?? '👤';
          ?>
        </div>
      </div>

      <!-- DAILY REPORT WARNING — Figure 5.8 -->
      <?php if(!$reportToday): ?>
      <div class="daily-warn-fig">
        <span style="font-size:16px;">⚠️</span>
        <div>
          <strong>Daily Report Pending</strong>
          Submit your sales report before end of day!
        </div>
      </div>
      <?php endif; ?>

      <!-- 4 COLORED STAT CARDS — Figure 5.8 -->
      <div class="stats-row" style="grid-template-columns:repeat(4,1fr);">
        <div class="stat-card emp-stat-green">
          <div class="emp-stat-icon">✅</div>
          <div class="stat-info">
            <h3><?= $daysPresent ?></h3>
            <p>Days Present</p>
          </div>
        </div>
        <div class="stat-card emp-stat-blue">
          <div class="emp-stat-icon">📋</div>
          <div class="stat-info">
            <h3><?= $paidAbs ?></h3>
            <p>Paid Absences</p>
          </div>
        </div>
        <div class="stat-card emp-stat-purple">
          <div class="emp-stat-icon">📊</div>
          <div class="stat-info">
            <h3><?= $totalPaid ?></h3>
            <p>Total Days Paid</p>
          </div>
        </div>
        <div class="stat-card emp-stat-orange">
          <div class="emp-stat-icon">💰</div>
          <div class="stat-info">
            <h3 style="font-size:16px;"><?= rwf($monthlySal) ?></h3>
            <p>Monthly Salary</p>
          </div>
        </div>
      </div>

      <!-- 4 ACTION TILES — Figure 5.8 -->
      <div class="emp-tiles">
        <button class="emp-tile" onclick="openModal('rptModal')">
          <div class="t-icon">📊</div>
          <span class="t-title">Submit Sales Report</span>
          <span class="t-sub">Record today's sales</span>
        </button>
        <button class="emp-tile" onclick="openModal('absModal')">
          <div class="t-icon">📅</div>
          <span class="t-title">Request Absence</span>
          <span class="t-sub">Submit absence request</span>
        </button>
        <a href="attendance.php" class="emp-tile">
          <div class="t-icon">📋</div>
          <span class="t-title">My Attendance</span>
          <span class="t-sub">View attendance history</span>
        </a>
        <a href="salary.php" class="emp-tile">
          <div class="t-icon">💰</div>
          <span class="t-title">Salary Details</span>
          <span class="t-sub">View salary breakdown</span>
        </a>
      </div>

      <!-- RECENT WORK REPORTS — Figure 5.8 -->
      <div class="card mb0">
        <div class="card-head">
          <h3>Recent Work Reports</h3>
          <span style="font-size:12px;color:var(--muted);"><?= $totalRpts ?> total reports</span>
        </div>
        <div class="card-body-0">
          <?php if($recentRpts->num_rows===0): ?>
          <p class="empty">No reports yet.<br><small>Click "Submit Sales Report" to add your first report.</small></p>
          <?php else: ?>
          <?php while($r=$recentRpts->fetch_assoc()):
            $met   = $r['totalSalesAmount'] >= $r['dailyTarget'];
            $stcls = $r['justificationStatus']==='approved'?'tag-green':($r['justificationStatus']==='rejected'?'tag-red':'tag-orange');
          ?>
          <div class="rpt-item">
            <div style="flex:1;">
              <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:5px;">
                <span class="tag tag-blue"><?= date('D, M d Y',strtotime($r['date'])) ?></span>
                <span class="tag tag-navy"><?= esc($emp['position']) ?></span>
                <span class="tag <?= $met?'tag-green':'tag-red' ?>"><?= $met?'✅ Above Target':'⚠️ Below Target' ?></span>
                <?php if($r['justificationFile']): ?>
                <span class="tag tag-teal">📎 Justification Attached</span>
                <?php endif; ?>
              </div>
              <div style="font-size:12px;color:var(--muted);">
                Sales: <?= rwf($r['totalSalesAmount']) ?>
                &nbsp;|&nbsp; Target: <?= rwf($r['dailyTarget']) ?>
              </div>
              <div style="font-size:11px;color:var(--muted);margin-top:2px;">
                Submitted at <?= date('h:i A',strtotime($r['submittedAt'])) ?>
              </div>
              <?php if($r['justificationNote']): ?>
              <div style="font-size:11px;color:var(--muted);">📝 <?= esc($r['justificationNote']) ?></div>
              <?php endif; ?>
              <?php if($r['justificationFile']): ?>
              <a href="#" style="font-size:11px;color:var(--navy);">View Justification →</a>
              <?php endif; ?>
            </div>
            <div style="text-align:right;flex-shrink:0;">
              <span class="tag <?= $stcls ?>"><?= ucfirst($r['justificationStatus']) ?></span>
              <?php if($r['justificationStatus']==='pending'): ?>
              <br><button class="btn btn-blue btn-xs" style="margin-top:6px;"
                onclick="prefill(<?= $r['totalSalesAmount'] ?>,<?= $r['itemsAddedCount'] ?>)">Edit</button>
              <?php endif; ?>
            </div>
          </div>
          <?php endwhile; ?>
          <?php endif; ?>
        </div>
      </div>

    <!-- RECENT ABSENCE REQUESTS SECTION -->
    <?php if($recentAbs && $recentAbs->num_rows > 0): ?>
    <div class="card" style="margin-top:16px;">
      <div class="card-head">
        <h3>📅 My Absence Requests</h3>
        <span style="font-size:12px;color:var(--muted);">Latest requests</span>
      </div>
      <div class="card-body-0">
        <?php while($ab=$recentAbs->fetch_assoc()):
          $abst = $ab['statusId'];
          $abcls = $abst==='approved'?'tag-green':($abst==='rejected'?'tag-red':'tag-orange');
          $ablbl = $abst==='approved'?'✅ Approved':($abst==='rejected'?'❌ Rejected':'⏳ Pending');
        ?>
        <div style="padding:12px 16px;border-bottom:1px solid #f0f2f8;display:flex;justify-content:space-between;align-items:center;">
          <div>
            <div style="font-size:13px;font-weight:600;"><?= date('D, M d Y',strtotime($ab['date'])) ?></div>
            <div style="font-size:12px;color:var(--muted);margin-top:2px;"><?= esc($ab['reason']) ?></div>
            <div style="font-size:11px;color:var(--muted);">Submitted: <?= date('M d, h:i A',strtotime($ab['createdAt'])) ?></div>
          </div>
          <span class="tag <?= $abcls ?>" style="font-size:12px;padding:5px 14px;"><?= $ablbl ?></span>
        </div>
        <?php endwhile; ?>
      </div>
    </div>
    <?php endif; ?>

    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════
     SUBMIT SALES REPORT — Figure 5.9 LEFT
     Dark background, items list
═══════════════════════════════════════ -->
<div class="modal-bg modal-dark" id="rptModal">
  <div class="modal-box">
    <div class="inv-modal-inner">
      <!-- Header -->
      <div class="inv-modal-header">
        <h3>Submit Sales Report</h3>
        <button onclick="closeModal('rptModal')">×</button>
      </div>
      <div class="inv-subtitle">Select Items Sold Today:</div>

      <!-- Item list -->
      <div class="inv-scroll">
        <?php if(empty($inv)): ?>
        <div class="inv-row"><span class="iname" style="color:#6b7280;">No inventory items</span></div>
        <?php else: ?>
        <?php foreach($inv as $it): ?>
        <div class="inv-row" id="irow-<?= $it['id'] ?>"
             onclick="rowClick(<?= $it['id'] ?>, <?= (float)$it['priceD'] ?>)">
          <span class="iname"><?= esc($it['name']) ?></span>
          <div style="display:flex;align-items:center;gap:10px;" onclick="event.stopPropagation()">
            <span class="iprice"><?= number_format($it['priceD'],0) ?> RWF each</span>
            <div class="qty-wrap">
              <button class="qbtn" onclick="adj(<?= $it['id'] ?>,-1,<?= (float)$it['priceD'] ?>)">−</button>
              <span class="qnum" id="qn-<?= $it['id'] ?>">0</span>
              <button class="qbtn" onclick="adj(<?= $it['id'] ?>,+1,<?= (float)$it['priceD'] ?>)">+</button>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <!-- Total bar -->
      <div class="inv-total-bar">
        <div class="tl">
          Total Sales Amount
          <span id="sel-count">0 items selected</span>
        </div>
        <div class="ta" id="sel-total">0 RWF</div>
      </div>

      <!-- Submit -->
      <div class="inv-submit-row">
        <form method="POST" id="rptForm">
          <input type="hidden" name="manual_total" id="f-total" value="0">
          <input type="hidden" name="items_count_hidden" id="f-count" value="0">
          <button type="submit" name="sub_report" id="rptBtn"
            class="inv-submit-btn" disabled>
            📤 Submit Sales Report
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════
     REQUEST ABSENCE — Figure 5.9 RIGHT
═══════════════════════════════════════ -->
<div class="modal-bg" id="absModal">
  <div class="modal-box" style="max-width:440px;">
    <h3>🗓️ Request Absence</h3>
    <p style="font-size:12px;color:var(--muted);margin:-10px 0 12px;">Submit a new absence request</p>
    <div class="alert alert-warn" style="font-size:12px;margin-bottom:14px;">
      ⚠️ <strong>Note:</strong> Approved absences are paid days. Make sure to provide valid
      proof documents for Admin approval.
    </div>
    <form method="POST" enctype="multipart/form-data">
      <div class="fg">
        <label>Employee Name</label>
        <input type="text" class="fc" value="<?= esc($emp['name']) ?>" readonly
               style="background:#f7f8fc;color:var(--muted);">
      </div>
      <div class="fg">
        <label>Date of Absence *</label>
        <input type="date" name="abs_date" class="fc" required>
      </div>
      <div class="fg">
        <label>Reason for Absence *</label>
        <textarea name="abs_reason" class="fc" placeholder="Enter your reason..." required></textarea>
      </div>
      <div class="fg">
        <label>Proof Document (PDF/Image)</label>
        <input type="file" name="proof_doc" class="fc" accept=".pdf,.jpg,.jpeg,.png">
        <div style="font-size:11px;color:var(--muted);margin-top:3px;">Accepted: PDF, JPG, PNG (Max 5 MB)</div>
      </div>
      <div class="modal-foot">
        <button type="button" class="btn btn-gray" onclick="closeModal('absModal')">Cancel</button>
        <button type="submit" name="sub_absence" class="btn btn-blue">📤 Submit Request</button>
      </div>
    </form>
  </div>
</div>

<script>
/* ── Inventory selection ── */
const Q={}, P={};
function rowClick(id,price){
  P[id]=price; Q[id]=Q[id]?0:1; refresh(id); recalc();
}
function adj(id,delta,price){
  P[id]=price; Q[id]=Math.max(0,(Q[id]||0)+delta); refresh(id); recalc();
}
function refresh(id){
  const q=Q[id]||0;
  document.getElementById('qn-'+id).textContent=q;
  const row=document.getElementById('irow-'+id);
  q>0?row.classList.add('picked'):row.classList.remove('picked');
}
function recalc(){
  let total=0,count=0;
  for(const id in Q){ const q=Q[id]||0; if(q>0){total+=(P[id]||0)*q;count+=q;} }
  document.getElementById('sel-total').textContent=total.toLocaleString()+'  RWF';
  document.getElementById('sel-count').textContent=count+' item'+(count!==1?'s':'')+' selected';
  document.getElementById('f-total').value=total.toFixed(2);
  document.getElementById('f-count').value=count;
  const btn=document.getElementById('rptBtn');
  btn.disabled=count===0;
}
function prefill(total,count){
  document.getElementById('f-total').value=total;
  document.getElementById('f-count').value=count;
  document.getElementById('sel-total').textContent=parseFloat(total).toLocaleString()+'  RWF';
  document.getElementById('sel-count').textContent=count+' items';
  document.getElementById('rptBtn').disabled=false;
  openModal('rptModal');
}
/* ── Modals ── */
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id){ document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-bg').forEach(m=>
  m.addEventListener('click',e=>{ if(e.target===m) m.classList.remove('open'); })
);
</script>
</body>
</html>

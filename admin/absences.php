<?php
require_once '../config.php'; authAdmin();
$msg='';
if(isset($_POST['approve'])){ db()->query("UPDATE absencerequest SET statusId='approved' WHERE id=".(int)$_POST['aid']); $msg='✅ Approved.'; }
if(isset($_POST['reject'])) { db()->query("UPDATE absencerequest SET statusId='rejected' WHERE id=".(int)$_POST['aid']); $msg='❌ Rejected.'; }
$abs=db()->query("SELECT ar.*,e.name,e.position FROM absencerequest ar JOIN employee e ON e.id=ar.employeeId ORDER BY ar.createdAt DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Absences – Smart Employee Management</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="layout">
  <?php include '_sb.php'; ?>
  <div class="main">
    <div class="topbar"><div class="topbar-left"><h1>🗓️ Absence Requests</h1></div></div>
    <div class="page">
      <?php if($msg): ?><div class="alert alert-ok"><?= $msg ?></div><?php endif; ?>
      <div class="card mb0">
        <div class="card-head"><h3>All Absence Requests</h3><span class="tag tag-navy"><?= $abs->num_rows ?> total</span></div>
        <div class="card-body-0">
          <div class="tbl-wrap">
            <table>
              <thead><tr><th>Employee</th><th>Position</th><th>Date</th><th>Reason</th><th>Status</th><th>Actions</th></tr></thead>
              <tbody>
              <?php while($a=$abs->fetch_assoc()):
                $sc=$a['statusId']==='approved'?'tag-green':($a['statusId']==='rejected'?'tag-red':'tag-orange');
              ?>
              <tr>
                <td><strong><?= esc($a['name']) ?></strong></td>
                <td><span class="tag tag-navy"><?= esc($a['position']) ?></span></td>
                <td class="nowrap"><?= date('M d, Y',strtotime($a['date'])) ?></td>
                <td><?= esc($a['reason']) ?></td>
                <td><span class="tag <?= $sc ?>"><?= ucfirst($a['statusId']) ?></span></td>
                <td>
                  <?php if($a['statusId']==='pending'): ?>
                  <form method="POST" style="display:inline;" class="flex gap6">
                    <input type="hidden" name="aid" value="<?= $a['id'] ?>">
                    <button name="approve" class="btn btn-green btn-xs">✅ Approve</button>
                    <button name="reject"  class="btn btn-red btn-xs">❌ Reject</button>
                  </form>
                  <?php else: ?><span class="muted" style="font-size:12px;">Processed</span><?php endif; ?>
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
</body>
</html>

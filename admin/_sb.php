<?php $pg=basename($_SERVER['PHP_SELF'],'.php'); ?>
<div class="sidebar">
  <div class="sb-brand">
    <div class="logo-text">SMART EMPLOYEE<br>MANAGEMENT</div>
    <div class="sub">AVATA Trading Ltd</div>
  </div>
  <nav class="sb-nav">
    <a href="dashboard.php"  class="<?= $pg==='dashboard' ?'active':'' ?>">Dashboard</a>
    <a href="attendance.php" class="<?= $pg==='attendance'?'active':'' ?>">Attendance</a>
    <a href="employees.php"  class="<?= $pg==='employees' ?'active':'' ?>">Employees</a>
    <a href="salary.php"     class="<?= $pg==='salary'    ?'active':'' ?>">Salary Summary</a>
    <a href="reports.php"    class="<?= $pg==='reports'   ?'active':'' ?>">Daily Reports</a>
    <a href="absences.php"   class="<?= $pg==='absences'  ?'active':'' ?>">Absences</a>
    <a href="../logout.php"  class="sb-logout">Logout</a>
  </nav>
</div>
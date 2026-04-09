================================================================
  SMART-BASED EMPLOYED ENTRANCE DETECTOR
  CASE STUDY: AVATA TRADING LTD
  Author : Butera Abdulaziz 
================================================================

  COMPLETE INSTALLATION GUIDE
================================================================

STEP 1 — START XAMPP
  1. Open XAMPP Control Panel
  2. Click START next to Apache  → must show green
  3. Click START next to MySQL   → must show green

STEP 2 — COPY FILES
  1. Open:  C:\xampp\htdocs\
  2. Create a new folder named exactly:  smart_employee
  3. Extract this ZIP and copy ALL files into that folder

  Result:  C:\xampp\htdocs\smart_employee\index.php
           C:\xampp\htdocs\smart_employee\login.php
           C:\xampp\htdocs\smart_employee\admin\dashboard.php
           ... etc.

STEP 3 — CREATE DATABASE
  1. Open browser → http://localhost/phpmyadmin
  2. Click NEW on the left sidebar
  3. Database name: smart_employee
  4. Collation:     utf8mb4_unicode_ci
  5. Click CREATE
  6. Click IMPORT tab at top
  7. Choose File → select setup.sql from this folder
  8. Click GO
  9. Green message = success ✅

STEP 4 — FIX ADMIN PASSWORD (IMPORTANT!)
  1. Open: http://localhost/smart_employee/fix_admin.php
  2. Click the blue button "Reset Password Now"
  3. You will see green success message
  4. Delete fix_admin.php after login works

STEP 5 — OPEN THE SYSTEM
  http://localhost/smart_employee/

  ADMIN LOGIN:
    URL:      http://localhost/smart_employee/admin_login.php
    Username: admin
    Password: admin123

  EMPLOYEE LOGIN:
    URL:            http://localhost/smart_employee/login.php
    Fingerprint ID: 1
    Email:          mahoroegide77@gmail.com
    (OTP appears on screen — copy and paste it)

  OTHER EMPLOYEES:
    FP 2 → mugishalight@gmail.com   (Delivery Person)
    FP 3 → mugishajustin@gmail.com  (Seller)
    FP 4 → nkusengajustin@gmail.com (Store Keeper)

STEP 6 — TEST IoT API (no hardware needed)
  http://localhost/smart_employee/api/checkin.php?fp=1&secret=AVATA2026
  Change fp=1 to fp=2, fp=3, fp=4 for other employees.

================================================================
  PAGES AND MATCHING BOOK FIGURES
================================================================
  admin_login.php          Figure 5.3  (right panel)
  login.php                Figure 5.3  (left panel)
  admin/dashboard.php      Figure 5.4
  admin/attendance.php     Figure 5.5
  admin/employees.php      Figure 5.6
  admin/salary.php         Figure 5.7
  employee/dashboard.php   Figure 5.8
  (Sales modal)            Figure 5.9  (left form)
  (Absence modal)          Figure 5.9  (right form)
  admin/reports.php        Figure 5.10
  setup.sql                Figure 4.6  (all 8 ERD tables)
  api/checkin.php          IoT API (DFD Figures 4.2 & 4.3)

================================================================
  TROUBLESHOOTING
================================================================
  "Invalid username or password"
    → Run fix_admin.php first (Step 4 above)

  "Database connection failed"
    → Make sure MySQL is STARTED in XAMPP

  "404 Page Not Found"
    → Folder must be named exactly: smart_employee
    → Must be inside: C:\xampp\htdocs\

  "Import failed"
    → In phpMyAdmin, go to SQL tab
    → Paste the entire content of setup.sql
    → Click GO

  OTP not working on employee login
    → The OTP is displayed on the login page itself
    → Copy the big numbers shown in the blue box

================================================================
  AVATA TRADING LTD — Smart Employee Management System
  Mount Kigali University | March 2026
================================================================

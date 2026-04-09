<?php
/**
 * ============================================================
 * SMART EMPLOYEE MANAGEMENT SYSTEM — AVATA TRADING LTD
 * IoT API Endpoint for ESP32 Fingerprint Scanner
 *
 * HOW TO TEST WITHOUT ESP32 HARDWARE:
 *   Open browser: http://localhost/smart_employee/api/checkin.php?fp=1&secret=AVATA2026
 *   Change fp=1 to fp=2, fp=3, fp=4 for other employees.
 *
 * ESP32 sends:
 *   POST http://YOUR_PC_IP/smart_employee/api/checkin.php
 *   Body: { "fingerprint_id": 1, "secret": "AVATA2026" }
 * ============================================================
 */

require_once '../config.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

define('IOT_SECRET', 'AVATA2026');

// Accept JSON (ESP32) or GET params (browser test)
$raw   = file_get_contents('php://input');
$input = $raw ? json_decode($raw, true) : null;
if (!$input) {
    $input = [
        'fingerprint_id' => (int)($_GET['fp']     ?? 0),
        'secret'         =>      ($_GET['secret'] ?? '')
    ];
}

// ── Auth ────────────────────────────────────────────────────
if (($input['secret'] ?? '') !== IOT_SECRET) {
    http_response_code(401);
    echo json_encode(['success'=>false,'message'=>'Unauthorized — wrong secret key']);
    exit;
}

$fpId = (int)($input['fingerprint_id'] ?? 0);
if ($fpId <= 0) {
    echo json_encode(['success'=>false,'message'=>'Invalid fingerprint_id value']);
    exit;
}

// ── Find employee ────────────────────────────────────────────
$emp = row("SELECT * FROM employee WHERE fingerprintId=$fpId");
if (!$emp) {
    echo json_encode([
        'success' => false,
        'fp_id'   => $fpId,
        'message' => 'Fingerprint not registered in system',
        'display' => 'UNKNOWN - SEE ADMIN'
    ]);
    exit;
}

$empId = $emp['id'];

// ── Duplicate check (prevent double scan within 1 hour) ──────
$last = row("SELECT * FROM attendancelog
             WHERE employeeId=$empId
             AND timestamp > DATE_SUB(NOW(), INTERVAL 1 HOUR)
             ORDER BY timestamp DESC LIMIT 1");
if ($last) {
    echo json_encode([
        'success'    => true,
        'already_in' => true,
        'employee'   => $emp['name'],
        'position'   => $emp['position'],
        'last_scan'  => $last['timestamp'],
        'display'    => 'ALREADY IN — ' . strtoupper($emp['name']),
        'message'    => 'Already scanned within the last hour'
    ]);
    exit;
}

// ── Record attendance ────────────────────────────────────────
db()->query("INSERT INTO attendancelog (employeeId, fingerprintId, status)
             VALUES ($empId, $fpId, 'present')");

$hour = (int)date('H');
$late = $hour >= 9;
$time = date('h:i A');

echo json_encode([
    'success'    => true,
    'already_in' => false,
    'employee'   => $emp['name'],
    'position'   => $emp['position'],
    'fp_id'      => $fpId,
    'time'       => $time,
    'late'       => $late,
    'message'    => 'Attendance recorded successfully',
    'display'    => ($late ? 'LATE — ' : 'WELCOME! ') . strtoupper($emp['name']) . ' | ' . $time
]);
?>

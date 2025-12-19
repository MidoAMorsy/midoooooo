<?php
/**
 * Smart Attendance System - Ajax Controller
 */

require_once '../../includes/config.php';
requireLogin();

header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';
$response = ['status' => 'error', 'message' => 'Invalid action'];

try {
    switch ($action) {

        case 'get_records':
            $start = $_GET['start'] ?? ($_GET['date'] ?? date('Y-m-d'));
            $end = $_GET['end'] ?? $start;
            $status = $_GET['status'] ?? '';
            $empId = $_GET['emp_id'] ?? '';

            // JOIN to get live employee details and correct emp_code
            $sql = "SELECT r.*, e.emp_code, e.name_en, e.name_ar 
                    FROM attendance_records r 
                    LEFT JOIN attendance_employees e ON r.employee_id = e.id 
                    WHERE r.date BETWEEN ? AND ?";
            $params = [$start, $end];

            if (!empty($status)) {
                $sql .= " AND r.status = ?";
                $params[] = $status;
            }

            if (!empty($empId)) {
                $sql .= " AND r.employee_id = ?";
                $params[] = $empId;
            }

            $sql .= " ORDER BY r.date DESC, r.time_in ASC";

            $stmt = db()->prepare($sql);
            $stmt->execute($params);
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Add status labels & fallback names
            foreach ($records as &$r) {
                // Determine name based on lang (global or session, here logic is simple)
                $r['employee_name'] = $r['name_en'] ?? $r['employee_name'];
                $r['status_label'] = ucfirst($r['status']);
                if ($r['status'] == 'excused')
                    $r['status_label'] = 'Excused (Regular)';
            }

            $response = ['status' => 'success', 'data' => $records];
            break;

        case 'get_employees':
            $stmt = db()->query("SELECT * FROM attendance_employees ORDER BY name_en");
            $employees = $stmt->fetchAll();
            $response = ['status' => 'success', 'data' => $employees];
            break;

        case 'save_employee':
            $id = $_POST['id'] ?? '';
            $code = sanitize($_POST['emp_code']);
            $name = sanitize($_POST['name']);
            $salary = floatval($_POST['salary'] ?? 0);
            $incentives = floatval($_POST['incentives'] ?? 0);

            // Shift & Policy
            $start = !empty($_POST['shift_start']) ? $_POST['shift_start'] : null;
            $end = !empty($_POST['shift_end']) ? $_POST['shift_end'] : null;

            // Off Days: Handle array or string
            $off_days = $_POST['off_days'] ?? '';
            if (is_array($off_days))
                $off_days = implode(',', $off_days);

            $vacation_bal = floatval($_POST['vacation_balance_days'] ?? 21);
            $permission_bal = floatval($_POST['permission_balance_hours'] ?? 0);

            if (empty($code) || empty($name)) {
                throw new Exception("Code and Name are required");
            }

            if (!empty($id)) {
                $sql = "UPDATE attendance_employees SET 
                        emp_code=?, name_en=?, salary=?, incentives=?, 
                        shift_start=?, shift_end=?, off_days=?, 
                        vacation_balance_days=?, permission_balance_hours=? 
                        WHERE id=?";
                db()->prepare($sql)->execute([
                    $code,
                    $name,
                    $salary,
                    $incentives,
                    $start,
                    $end,
                    $off_days,
                    $vacation_bal,
                    $permission_bal,
                    $id
                ]);
                $msg = 'Employee updated';
            } else {
                $sql = "INSERT INTO attendance_employees 
                        (emp_code, name_en, salary, incentives, shift_start, shift_end, off_days, vacation_balance_days, permission_balance_hours, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')";
                db()->prepare($sql)->execute([
                    $code,
                    $name,
                    $salary,
                    $incentives,
                    $start,
                    $end,
                    $off_days,
                    $vacation_bal,
                    $permission_bal
                ]);
                $msg = 'Employee added';
            }

            $response = ['status' => 'success', 'message' => $msg];
            break;

        case 'delete_employee':
            $id = $_POST['id'] ?? 0;
            if ($id) {
                db()->prepare("DELETE FROM attendance_employees WHERE id=?")->execute([$id]);
                $response = ['status' => 'success', 'message' => 'Employee deleted'];
            }
            break;

        case 'import_data':
            if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("Upload failed or no file selected");
            }

            $type = $_POST['type'] ?? 'attendance'; // 'attendance' or 'employees'
            $tmpFile = $_FILES['file']['tmp_name'];

            if ($type === 'employees') {
                require_once 'import_employees_processor.php';
                $result = processEmployeeImport($tmpFile);
            } else {
                require_once 'process_attendance_logs.php';
                $result = processAttendanceLogs($tmpFile);
            }

            if (!$result['success']) {
                throw new Exception($result['message']);
            }

            $response = ['status' => 'success', 'message' => $result['message']];
            break;

        case 'get_stats':
            // Logic moved to explicit query here for accuracy
            $m = $_GET['month'] ?? date('m');
            $y = $_GET['year'] ?? date('Y');

            $stats = [
                'total_employees' => db()->query("SELECT COUNT(*) FROM attendance_employees WHERE status='active'")->fetchColumn(),
                'total_late_minutes' => db()->query("SELECT SUM(late_minutes) FROM attendance_records WHERE MONTH(date)=$m AND YEAR(date)=$y")->fetchColumn() ?: 0,
                'total_deductions' => db()->query("SELECT SUM(net_deduction_amount) FROM attendance_records WHERE MONTH(date)=$m AND YEAR(date)=$y")->fetchColumn() ?: 0,
                // Attendance Rate: Present days / (Total Emps * Work Days so far) - approx logic
                'present_today' => db()->query("SELECT COUNT(DISTINCT employee_id) FROM attendance_records WHERE date = CURDATE() AND status != 'absent'")->fetchColumn()
            ];
            $response = ['status' => 'success', 'data' => $stats];
            break;

        case 'export_report':
            $type = $_GET['type'] ?? 'custom';
            $start = $_GET['start'] ?? date('Y-m-01');
            $end = $_GET['end'] ?? date('Y-m-d');
            $empId = $_GET['emp_id'] ?? '';

            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="Attendance_Report_' . $start . '_to_' . $end . '.csv"');

            $output = fopen('php://output', 'w');
            // BOM for Excel
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($output, ['Date', 'Employee', 'Code', 'Time In', 'Time Out', 'Late (Mins)', 'Deduction', 'Status', 'Reason']);

            $sql = "SELECT r.*, e.emp_code, e.name_en 
                    FROM attendance_records r 
                    LEFT JOIN attendance_employees e ON r.employee_id = e.id 
                    WHERE r.date BETWEEN ? AND ?";
            $params = [$start, $end];

            if (!empty($empId)) {
                $sql .= " AND r.employee_id = ?";
                $params[] = $empId;
            }

            $sql .= " ORDER BY r.date ASC, e.name_en ASC";

            $stmt = db()->prepare($sql);
            $stmt->execute($params);

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                fputcsv($output, [
                    $row['date'],
                    $row['name_en'],
                    $row['emp_code'],
                    $row['time_in'],
                    $row['time_out'],
                    $row['late_minutes'],
                    $row['net_deduction_amount'],
                    ucfirst($row['status']),
                    $row['deduction_reason']
                ]);
            }
            exit;
            break;

        case 'update_record':
            $id = $_POST['id'] ?? 0;
            $in = $_POST['time_in'] ?? null;
            $out = $_POST['time_out'] ?? null;
            $status = $_POST['status'] ?? 'present';

            if (!$id)
                throw new Exception("Record ID missing");

            // Recalculate logic
            $rec = db()->query("SELECT * FROM attendance_records WHERE id=$id")->fetch(PDO::FETCH_ASSOC);
            $emp = db()->query("SELECT * FROM attendance_employees WHERE id=" . $rec['employee_id'])->fetch(PDO::FETCH_ASSOC);

            require_once 'AttendanceCalculator.php';
            $calc = new AttendanceCalculator();

            // If status is excused, force it
            if ($status === 'excused') {
                $calcRes = [
                    'late_minutes' => 0,
                    'permission_used_minutes' => 0,
                    'deduction_reason' => 'Excused by Admin',
                    'net_deduction_amount' => 0,
                    'status' => 'excused'
                ];
            } else {
                // Determine timestamps
                // If In/Out provided, use them, else use original Rec date + In/Out
                // NOTE: Input comes as HH:MM. We need to attach Date.

                // If inputs are empty, pass null? No, user might want to clear them? 
                // Let's assume user provides times or keeps existing.

                // We re-run the calculation with the NEW times.
                $calcRes = $calc->calculateDailyLog($emp, $rec['date'], $in, $out);
            }

            // Init Update
            $sql = "UPDATE attendance_records SET 
                    time_in=?, time_out=?, 
                    late_minutes=?, permission_used_minutes=?, 
                    deduction_reason=?, net_deduction_amount=?, 
                    status=? 
                    WHERE id=?";

            db()->prepare($sql)->execute([
                $in,
                $out,
                $calcRes['late_minutes'],
                $calcRes['permission_used_minutes'],
                $calcRes['deduction_reason'],
                $calcRes['net_deduction_amount'],
                $calcRes['status'],
                $id
            ]);

            $response = ['status' => 'success', 'message' => 'Record updated & recalculated'];
            break;

        case 'get_settings':
            $defaults = [
                'work_start_time' => '09:00',
                'work_end_time' => '17:00',
                'grace_period_minutes' => '15',
                'half_day_late_threshold_mins' => '60',
                'full_day_late_threshold_mins' => '120',
                'daily_work_hours' => '8'
            ];

            // The table is Single Row (id=1) with columns
            $row = db()->query("SELECT * FROM attendance_settings WHERE id=1")->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                // Merge DB row with defaults (DB overrides)
                $data = array_merge($defaults, $row);
            } else {
                $data = $defaults;
            }

            $response = ['status' => 'success', 'data' => $data];
            break;

        case 'save_settings':
            $start = $_POST['work_start_time'] ?? '09:00';
            $end = $_POST['work_end_time'] ?? '17:00';
            $grace = $_POST['grace_period_minutes'] ?? 15;
            $half = $_POST['half_day_late_threshold_mins'] ?? 60;
            $full = $_POST['full_day_late_threshold_mins'] ?? 120;
            $daily_hours = $_POST['daily_work_hours'] ?? 8;

            // Check if row 1 exists
            $check = db()->query("SELECT id FROM attendance_settings WHERE id=1")->fetch();

            if ($check) {
                $sql = "UPDATE attendance_settings SET 
                        work_start_time=?, work_end_time=?, grace_period_minutes=?,
                        half_day_late_threshold_mins=?, full_day_late_threshold_mins=?, daily_work_hours=?
                        WHERE id=1";
                db()->prepare($sql)->execute([$start, $end, $grace, $half, $full, $daily_hours]);
            } else {
                $sql = "INSERT INTO attendance_settings 
                        (id, work_start_time, work_end_time, grace_period_minutes, half_day_late_threshold_mins, full_day_late_threshold_mins, daily_work_hours)
                        VALUES (1, ?, ?, ?, ?, ?, ?)";
                db()->prepare($sql)->execute([$start, $end, $grace, $half, $full, $daily_hours]);
            }

            $response = ['status' => 'success', 'message' => 'Settings saved'];
            break;

        case 'get_analytics':
            // 1. Monthly Status Breakdown (Pie Chart)
            $m = $_GET['month'] ?? date('m');
            $y = $_GET['year'] ?? date('Y');

            $sqlStatus = "SELECT status, COUNT(*) as count 
                          FROM attendance_records 
                          WHERE MONTH(date) = ? AND YEAR(date) = ? 
                          GROUP BY status";
            $stmt = db()->prepare($sqlStatus);
            $stmt->execute([$m, $y]);
            $statusData = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // e.g. ['late'=>5, 'present'=>20]

            // Ensure all keys exist for cleaner JS
            $pie = [
                'present' => $statusData['present'] ?? 0,
                'late' => $statusData['late'] ?? 0,
                'absent' => $statusData['absent'] ?? 0,
                'excused' => $statusData['excused'] ?? 0
            ];

            // 2. Weekly Trend (Last 7 Days) (Line/Bar Chart)
            // We want array of dates and counts for 'present' + 'late' (as "Attendance") vs 'absent'
            $trend = [];
            for ($i = 6; $i >= 0; $i--) {
                $d = date('Y-m-d', strtotime("-$i days"));
                // Count Present/Late
                $pCount = db()->query("SELECT COUNT(*) FROM attendance_records WHERE date='$d' AND status IN ('present','late','excused')")->fetchColumn();
                // Count Absent
                $aCount = db()->query("SELECT COUNT(*) FROM attendance_records WHERE date='$d' AND status='absent'")->fetchColumn();

                $trend[] = [
                    'date' => date('d M', strtotime($d)),
                    'attendance' => $pCount,
                    'absent' => $aCount
                ];
            }

            $response = [
                'status' => 'success',
                'data' => [
                    'pie' => $pie,
                    'trend' => $trend
                ]
            ];
            break;



        case 'get_payslip_data':
            $empId = $_GET['emp_id'];
            $month = $_GET['month'] ?? date('m');
            $year = $_GET['year'] ?? date('Y');

            // 1. Employee Info
            $emp = db()->query("SELECT * FROM attendance_employees WHERE id = " . intval($empId))->fetch(PDO::FETCH_ASSOC);

            // 2. Records
            $records = db()->query("SELECT * FROM attendance_records WHERE employee_id = $empId AND MONTH(date) = $month AND YEAR(date) = $year ORDER BY date ASC")->fetchAll(PDO::FETCH_ASSOC);

            // 3. Summary Stats
            $summary = [
                'total_late_min' => 0,
                'total_deductions' => 0,
                'absent_days' => 0,
                'records_count' => count($records)
            ];

            foreach ($records as $r) {
                $summary['total_late_min'] += $r['late_minutes'];
                $summary['total_deductions'] += $r['net_deduction_amount'];
                if ($r['status'] === 'absent')
                    $summary['absent_days']++;
            }

            // 4. Net Salary Calc
            $gross = $emp['salary'] + $emp['incentives'];
            $net = $gross - $summary['total_deductions'];

            $response = [
                'status' => 'success',
                'data' => [
                    'employee' => $emp,
                    'records' => $records,
                    'summary' => $summary,
                    'financials' => [
                        'gross' => $gross,
                        'net' => $net
                    ]
                ]
            ];
            break;

        default:
            $response = ['status' => 'error', 'message' => 'Unknown action'];
    }

} catch (Exception $e) {
    $response = ['status' => 'error', 'message' => $e->getMessage()];
}

echo json_encode($response);
exit;

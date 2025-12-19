<?php
/**
 * Smart Log Processor
 * Handles Raw Logs -> Aggregation -> Calculation -> storage
 */

if (!defined('DB_HOST'))
    require_once '../../includes/config.php';
require_once 'AttendanceCalculator.php';

function processAttendanceLogs($file)
{
    if (!file_exists($file))
        return ['success' => false, 'message' => 'File not found'];

    // Check Header & BOM
    $handle = fopen($file, 'r');
    $line = fgets($handle);
    $line = preg_replace('/^\xEF\xBB\xBF/', '', $line);
    $header = str_getcsv($line);

    // Smart Column Mapping
    $col = ['id' => -1, 'date' => -1, 'time' => -1, 'datetime' => -1];
    foreach ($header as $i => $h) {
        $h = strtolower(trim($h));
        if (in_array($h, ['ac-no.', 'ac-no', 'id', 'user id', 'no.']))
            $col['id'] = $i;
        if (in_array($h, ['name', 'enname']))
            $col['name'] = $i; // Optional
        if (in_array($h, ['date', 'day']))
            $col['date'] = $i;
        if (in_array($h, ['time', 'clock']))
            $col['time'] = $i;
        if (in_array($h, ['datetime', 'time', 'timestamp']))
            $col['datetime'] = $i;
    }

    if ($col['id'] === -1)
        return ['success' => false, 'message' => 'Col [AC-No] not found'];

    // 1. Aggregation Phase (Memory Based - assuming reasonable file size for shared host)
    // Structure: $logs[emp_code][date] = [times...];
    $logs = [];

    while (($row = fgetcsv($handle)) !== false) {
        if (empty(implode('', $row)))
            continue;

        $empCode = trim($row[$col['id']]);
        if (empty($empCode))
            continue;

        // Parse Date/Time
        $d = '';
        $t = '';

        if ($col['datetime'] !== -1 && isset($row[$col['datetime']])) {
            // Try parse combined
            $ts = strtotime($row[$col['datetime']]);
            if ($ts) {
                $d = date('Y-m-d', $ts);
                $t = date('H:i:s', $ts);
            }
        } elseif ($col['date'] !== -1 && $col['time'] !== -1) {
            // Separate
            $tsD = strtotime($row[$col['date']]);
            $tsT = strtotime($row[$col['time']]);
            if ($tsD && $tsT) {
                $d = date('Y-m-d', $tsD);
                $t = date('H:i:s', $tsT);
            }
        } else {
            // Fallback: try finding any date-like string in row? No, strict is better.
            continue;
        }

        if ($d && $t) {
            $logs[$empCode][$d][] = $t;
        }
    }
    fclose($handle);

    // 2. Processing Phase
    $calculator = new AttendanceCalculator();

    // Cache Employees
    $stmt = db()->query("SELECT * FROM attendance_employees");
    $emps = [];
    while ($r = $stmt->fetch())
        $emps[$r['emp_code']] = $r;

    $processed = 0;
    $pdo = db();

    foreach ($logs as $code => $dates) {
        if (!isset($emps[$code]))
            continue; // Skip unknown employee or Auto-create? Skipping for safety.
        $employee = $emps[$code];

        foreach ($dates as $date => $times) {
            // Smart Grouping: First In, Last Out
            sort($times);
            $in = $times[0];
            $out = (count($times) > 1) ? end($times) : null;

            // If the file explicitly has "Clock In" and "Clock Out" cols, we might adjust logic, 
            // but this "Raw Punch" logic works for both if we treat them as a stream of events.

            // RUN ENGINE
            $res = $calculator->calculateDailyLog($employee, $date, $in, $out);

            // Upsert
            $sql = "INSERT INTO attendance_records 
                    (employee_id, employee_name, date, time_in, time_out, late_minutes, permission_used_minutes, deduction_reason, net_deduction_amount, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                    time_in=VALUES(time_in), time_out=VALUES(time_out),
                    late_minutes=VALUES(late_minutes), permission_used_minutes=VALUES(permission_used_minutes),
                    deduction_reason=VALUES(deduction_reason), net_deduction_amount=VALUES(net_deduction_amount),
                    status=VALUES(status)";

            $pdo->prepare($sql)->execute([
                $employee['id'],
                $employee['name_en'],
                $date,
                $in,
                $out,
                $res['late_minutes'],
                $res['permission_used_minutes'],
                $res['deduction_reason'],
                $res['net_deduction_amount'],
                $res['status']
            ]);
            $processed++;

            // Handle Absent?
            // "Absent" is usually detected by a scheduled job (Cron) checking for missing records.
            // But we can't do Cron easily on shared hosting sometimes.
            // Dashboard "Absent" stat is dynamic (if no record found for today).
            // For past dates, we can have a "Fill Absents" button.
        }
    }

    return ['success' => true, 'message' => "Proccessed $processed Daily Logs (Consolidated from raw punches)."];
}

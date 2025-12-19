<?php
/**
 * Smart Attendance System - Process Upload with Auto-Detection
 */

require_once '../../includes/config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['attendance_file']) || $_FILES['attendance_file']['error'] !== UPLOAD_ERR_OK) {
        setFlash('error', __('upload_error'));
        redirect('index.php');
    }

    $file = $_FILES['attendance_file']['tmp_name'];
    $handle = fopen($file, "r");

    if ($handle === false) {
        setFlash('error', __('file_open_error'));
        redirect('index.php');
    }

    // Settings
    $shiftStart = getSetting('attendance_shift_start', '09:00');
    $shiftEnd = getSetting('attendance_shift_end', '17:00');
    $gracePeriod = (int) getSetting('attendance_grace_period', 15);

    $importedCount = 0;

    // Read Header Row
    $header = fgetcsv($handle, 1000, ",");
    if (!$header) {
        fclose($handle);
        setFlash('error', 'Empty CSV file');
        redirect('index.php');
    }

    // Auto-Detect Column Indexes
    $colMap = ['id' => -1, 'name' => -1, 'date' => -1, 'time' => -1];

    foreach ($header as $index => $colName) {
        $colName = strtolower(trim($colName));
        // Employee ID
        if (in_array($colName, ['id', 'emp_id', 'ac-no', 'user id', 'رقم الموظف']))
            $colMap['id'] = $index;
        // Name
        if (in_array($colName, ['name', 'employee name', 'user name', 'اسم الموظف']))
            $colMap['name'] = $index;
        // Date (or Date/Time combined)
        if (in_array($colName, ['date', 'time', 'datetime', 'timestamp', 'التاريخ', 'الوقت']))
            $colMap['date'] = $index;
    }

    // Fallback if not detected (assume 0=ID, 1=Name, 2=Date/Time)
    if ($colMap['id'] === -1)
        $colMap['id'] = 0;
    if ($colMap['name'] === -1)
        $colMap['name'] = 1;
    if ($colMap['date'] === -1)
        $colMap['date'] = 2;

    $rawData = [];

    // Parse Rows
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        if (count($data) <= max($colMap))
            continue;

        $empId = trim($data[$colMap['id']]);
        $name = trim($data[$colMap['name']]);
        $dateTimeStr = trim($data[$colMap['date']]); // Could be "2023-10-01 09:00" or just Date

        if (empty($empId) || empty($dateTimeStr))
            continue;

        // Normalize Date/Time parsing
        $ts = strtotime($dateTimeStr);
        if (!$ts)
            continue;

        $date = date('Y-m-d', $ts);
        $time = date('H:i', $ts);

        // Group by Employee & Date
        $key = "$empId|$date";
        if (!isset($rawData[$key])) {
            $rawData[$key] = [
                'emp_id' => $empId,
                'name' => $name,
                'date' => $date,
                'times' => []
            ];
        }
        $rawData[$key]['times'][] = $ts;
    }
    fclose($handle);

    // Process Grouped Data
    try {
        db()->beginTransaction();

        foreach ($rawData as $record) {
            $times = $record['times'];
            sort($times); // Sort timestamps ascending

            // Determine First In and Last Out
            $firstInJs = $times[0];
            $lastOutTs = end($times);

            $timeIn = date('H:i', $firstInJs);
            $timeOut = ($firstInJs === $lastOutTs) ? null : date('H:i', $lastOutTs); // If only one record, assume no check-out

            // Calculate Late Minutes
            $lateMinutes = 0;
            $shiftStartTime = strtotime("{$record['date']} $shiftStart");
            $graceTime = $shiftStartTime + ($gracePeriod * 60);

            if ($firstInJs > $graceTime) {
                $lateMinutes = round(($firstInJs - $shiftStartTime) / 60);
            }

            // Calculate Early Minutes
            $earlyMinutes = 0;
            if ($timeOut) {
                $shiftEndTime = strtotime("{$record['date']} $shiftEnd");
                if ($lastOutTs < $shiftEndTime) {
                    $earlyMinutes = round(($shiftEndTime - $lastOutTs) / 60);
                }
            }

            // Status Logic
            $status = 'present';
            if ($lateMinutes > 0)
                $status = 'late';
            // If only Check-In exists, maybe flag as error or partial? For now keep 'present'

            // Insert
            $stmt = db()->prepare("INSERT INTO attendance_records 
                (employee_id, employee_name, date, time_in, time_out, late_minutes, early_minutes, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE 
                time_in = VALUES(time_in), 
                time_out = VALUES(time_out),
                late_minutes = VALUES(late_minutes),
                early_minutes = VALUES(early_minutes),
                status = VALUES(status)");

            $stmt->execute([
                $record['emp_id'],
                $record['name'],
                $record['date'],
                $timeIn,
                $timeOut,
                $lateMinutes,
                $earlyMinutes,
                $status
            ]);
            $importedCount++;
        }

        db()->commit();
        setFlash('success', sprintf(__('import_success'), $importedCount));

    } catch (Exception $e) {
        db()->rollBack();
        setFlash('error', __('import_error') . ': ' . $e->getMessage());
    }

    redirect('index.php');
} else {
    redirect('index.php');
}

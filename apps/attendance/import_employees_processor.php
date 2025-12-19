<?php
/**
 * Employee Data Importer (Native PHP)
 * Handles Bulk Update & Insert with strict column mapping.
 */

if (!defined('DB_HOST')) {
    require_once '../../includes/config.php';
}

function processEmployeeImport($file)
{
    global $lang;

    // 1. Check & Open File
    if (!file_exists($file))
        return ['success' => false, 'message' => 'File not found'];

    // Check XLSX Signature
    $f = fopen($file, 'r');
    $sig = fread($f, 4);
    fclose($f);
    if ($sig === "PK\x03\x04") {
        return ['success' => false, 'message' => 'Error: Is this an Excel (.xlsx) file? Please save it as CSV (Comma Delimited) and try again.'];
    }

    $handle = fopen($file, 'r');
    if (!$handle)
        return ['success' => false, 'message' => 'Unable to open file'];

    // 2. Parse Header (Remove BOM)
    $line = fgets($handle);
    if (!$line) {
        fclose($handle);
        return ['success' => false, 'message' => 'Empty file'];
    }
    $line = preg_replace('/^\xEF\xBB\xBF/', '', $line);
    $header = str_getcsv($line);

    // 3. Map Columns
    // Required: AC-No, Name, Arabic Name, Start Time, End Time, Vacation Balance, Off Days
    $map = [
        'code' => -1,
        'name_en' => -1,
        'name_ar' => -1,
        'salary' => -1,
        'incentives' => -1,
        'start' => -1,
        'end' => -1,
        'vacation' => -1,
        'off_days' => -1
    ];

    foreach ($header as $i => $col) {
        $c = strtolower(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $col)));

        if (in_array($c, ['ac-no', 'ac-no.', 'code', 'emp_code']))
            $map['code'] = $i;
        if (in_array($c, ['name', 'name_en', 'english name']))
            $map['name_en'] = $i;
        if (in_array($c, ['arabic name', 'name_ar', 'ar name']))
            $map['name_ar'] = $i;
        if (in_array($c, ['salary', 'basic salary']))
            $map['salary'] = $i;
        if (in_array($c, ['incentives', 'bonus']))
            $map['incentives'] = $i;
        if (in_array($c, ['start time', 'start', 'shift start']))
            $map['start'] = $i;
        if (in_array($c, ['end time', 'end', 'shift end']))
            $map['end'] = $i;
        if (in_array($c, ['vacation balance', 'vacation', 'vacation_days']))
            $map['vacation'] = $i;
        if (in_array($c, ['off days', 'off_days', 'weekend']))
            $map['off_days'] = $i;
    }

    if ($map['code'] === -1) {
        fclose($handle);
        return ['success' => false, 'message' => 'Required column "AC-No" not found.'];
    }

    // 4. Prepare SQL (ON DUPLICATE KEY UPDATE)
    $stmt = db()->prepare("
        INSERT INTO attendance_employees 
        (emp_code, name_en, name_ar, salary, incentives, shift_start, shift_end, vacation_balance_days, off_days) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
        name_en = VALUES(name_en),
        name_ar = VALUES(name_ar),
        salary = VALUES(salary),
        incentives = VALUES(incentives),
        shift_start = VALUES(shift_start),
        shift_end = VALUES(shift_end),
        vacation_balance_days = VALUES(vacation_balance_days),
        off_days = VALUES(off_days)
    ");

    $processed = 0;

    // 5. Process Rows
    while (($row = fgetcsv($handle)) !== false) {
        // Skip empty
        if (empty(implode('', $row)))
            continue;

        $code = isset($row[$map['code']]) ? trim($row[$map['code']]) : '';
        if (empty($code))
            continue;

        // Extract Data
        $nameEn = ($map['name_en'] >= 0) ? trim($row[$map['name_en']]) : $code;
        $nameAr = ($map['name_ar'] >= 0) ? trim($row[$map['name_ar']]) : $nameEn;

        $salary = ($map['salary'] >= 0) ? floatval(str_replace(',', '', $row[$map['salary']])) : 0;
        $incentives = ($map['incentives'] >= 0) ? floatval(str_replace(',', '', $row[$map['incentives']])) : 0;

        $vacation = ($map['vacation'] >= 0) ? floatval($row[$map['vacation']]) : 21.00;
        $offDays = ($map['off_days'] >= 0) ? trim($row[$map['off_days']]) : '5';

        // Time Parsing
        $startRaw = ($map['start'] >= 0) ? trim($row[$map['start']]) : '09:00';
        $endRaw = ($map['end'] >= 0) ? trim($row[$map['end']]) : '17:00';

        $start = date('H:i:s', strtotime($startRaw));
        $end = date('H:i:s', strtotime($endRaw));

        try {
            $stmt->execute([$code, $nameEn, $nameAr, $salary, $incentives, $start, $end, $vacation, $offDays]);
            $processed++;
        } catch (Exception $e) {
            continue;
        }
    }

    fclose($handle);
    return ['success' => true, 'message' => "Imported/Updated $processed employees successfully."];
}

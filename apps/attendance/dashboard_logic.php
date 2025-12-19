<?php
/**
 * Dashboard & Reporting Logic Snippets
 * Can be included in index.php or ajax.php
 */

/* 1. Dashboard Summary Query */
function getAttendanceSummary($month, $year)
{
    return db()->query("
        SELECT 
            COUNT(DISTINCT employee_id) as total_employees,
            SUM(late_minutes) as total_late_minutes,
            SUM(net_deduction_amount) as total_deductions,
            SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as total_absent_days,
            SUM(permission_used_minutes) as total_permission_used
        FROM attendance_records
        WHERE MONTH(date) = $month AND YEAR(date) = $year
    ")->fetch(PDO::FETCH_ASSOC);
}

/* 2. Manual Override (Admin Action) */
function overrideRecord($recordId, $newStatus, $adminNote)
{
    $sql = "UPDATE attendance_records SET 
            status = ?, 
            deduction_reason = NULL, 
            net_deduction_amount = 0.00, 
            late_minutes = 0 
            WHERE id = ?";
    db()->prepare($sql)->execute([$newStatus, $recordId]);
    // Log admin action if needed
}

/* 3. Detailed Monthly Report Query */
function getMonthlyReport($month, $year)
{
    return db()->query("
        SELECT 
            e.name_en, 
            e.emp_code,
            COUNT(r.id) as working_days,
            SUM(r.late_minutes) as total_late,
            SUM(r.net_deduction_amount) as total_penalty,
            e.vacation_balance_days
        FROM attendance_employees e
        LEFT JOIN attendance_records r ON e.id = r.employee_id 
        WHERE MONTH(r.date) = $month AND YEAR(r.date) = $year
        GROUP BY e.id
    ")->fetchAll(PDO::FETCH_ASSOC);
}

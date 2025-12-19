<?php
/**
 * Attendance Calculator Logic
 * Handles complex Payroll & HR Rules: Lateness, Permissions, Deductions.
 */

class AttendanceCalculator
{

    private $settings;
    private $pdo;

    public function __construct()
    {
        $this->pdo = db();
        $this->loadSettings();
    }

    private function loadSettings()
    {
        $stmt = $this->pdo->query("SELECT * FROM attendance_settings LIMIT 1");
        $this->settings = $stmt->fetch(PDO::FETCH_ASSOC);

        // Defaults if missing
        if (!$this->settings) {
            $this->settings = [
                'grace_period_minutes' => 15,
                'work_start_time' => '09:00:00',
                'daily_work_hours' => 8,
                'half_day_late_threshold_mins' => 60,
                'full_day_late_threshold_mins' => 120
            ];
        }
    }

    /**
     * Calculate Daily Attendance for an Employee
     */
    public function calculateDailyLog($employee, $date, $timeIn, $timeOut)
    {
        $empId = $employee['id'];
        $salary = (float) $employee['salary'];

        // Settings & Shift
        $shiftStart = $employee['shift_start'] ?: $this->settings['work_start_time'];
        $shiftEnd = $employee['shift_end'] ?: $this->settings['work_end_time'];
        $graceMins = (int) $this->settings['grace_period_minutes'];

        // 1. Check Weekend / Off Employee
        $dayOfWeek = date('N', strtotime($date)); // 1 (Mon) to 7 (Sun)
        // Convert DB off_days "5,6" (Fri/Sat) to array. Note: PHP date('N') Fri=5, Sat=6.
        $offDays = explode(',', $employee['off_days'] ?? '5,6');
        $isWeekend = in_array((string) $dayOfWeek, $offDays);

        if ($isWeekend) {
            // If present on weekend -> Weekend Work
            if ($timeIn || $timeOut) {
                return $this->buildResult('weekend_work', 0, 0, null, 0); // No penalty, maybe Overtime (future)
            }
            return $this->buildResult('weekend', 0, 0, null, 0);
        }

        // 2. Check Absent
        if (!$timeIn && !$timeOut) {
            return $this->buildResult('absent', 0, 0, 'Absent', $salary / 30); // 1 Day Penalty
        }

        // 3. Check Incomplete (Missing Punch)
        if (!$timeIn || !$timeOut) {
            return $this->buildResult('incomplete', 0, 0, 'Incomplete Log', 0); // Warning only, or custom penalty
        }

        // 4. Time Calculations
        $shiftStartTs = strtotime("$date $shiftStart");
        $shiftEndTs = strtotime("$date $shiftEnd");
        $actualInTs = strtotime("$date $timeIn");
        $actualOutTs = strtotime("$date $timeOut");

        // Lateness
        $lateMinutes = 0;
        $allowedInTs = $shiftStartTs + ($graceMins * 60);
        if ($actualInTs > $allowedInTs) {
            $lateMinutes = round(($actualInTs - $shiftStartTs) / 60);
        }

        // Early Departure
        $earlyMinutes = 0;
        if ($actualOutTs < $shiftEndTs) {
            $earlyMinutes = round(($shiftEndTs - $actualOutTs) / 60);
        }

        // 5. Financial Logic (Permissions & Penalties)
        $permissionUsed = 0;
        $deductionReason = null;
        $netDeduction = 0.00;
        $status = 'present';

        $totalViolation = $lateMinutes + $earlyMinutes;

        if ($totalViolation > 0) {
            // Check Permission Balance
            $currentBalanceMins = ((float) $employee['permission_balance_hours']) * 60;

            if ($currentBalanceMins >= $totalViolation) {
                // Covered by permission
                $permissionUsed = $totalViolation;
                $remViolation = 0;
                $status = 'permission_used';
                // Decrease Balance (This should ideally happen in a separate transactional step, but done here for logic flow)
                // Note: We don't update DB here to allow dry-run. The caller handles DB update if needed or we assume this is the 'run'.
                // Ideally, we return the 'new balance' to be updated.
            } else {
                // Partial cover
                $permissionUsed = $currentBalanceMins;
                $remViolation = $totalViolation - $currentBalanceMins;
                $status = 'late';
            }

            // Apply Penalties on Remaining Violation
            if ($remViolation > 0) {
                $halfDayLimit = (int) $this->settings['half_day_late_threshold_mins'];
                $fullDayLimit = (int) $this->settings['full_day_late_threshold_mins'];
                $dailySalary = $salary / 30;

                if ($remViolation > $fullDayLimit) {
                    $deductionReason = 'Full Day Penalty (> ' . $fullDayLimit . 'm)';
                    $netDeduction = $dailySalary;
                } elseif ($remViolation > $halfDayLimit) {
                    $deductionReason = 'Half Day Penalty (> ' . $halfDayLimit . 'm)';
                    $netDeduction = $dailySalary / 2;
                } else {
                    $deductionReason = 'Late Warning'; // Or calculate minute-value
                    // Optional: $netDeduction = ($dailySalary / 8 / 60) * $remViolation;
                }
            }
        }

        // Update Balance in DB if permission used (only if this is a committed run, assumed yes)
        if ($permissionUsed > 0) {
            $newBal = max(0, ($currentBalanceMins - $permissionUsed) / 60);
            $this->updateEmployeeBalance($empId, $newBal);
        }

        return $this->buildResult($status, $totalViolation, $permissionUsed, $deductionReason, $netDeduction);
    }

    /**
     * Update Employee Permission Balance
     */
    private function updateEmployeeBalance($empId, $newBalanceHours)
    {
        $sql = "UPDATE attendance_employees SET permission_balance_hours = ? WHERE id = ?";
        // We use the global db() function from config
        $stmt = db()->prepare($sql);
        $stmt->execute([$newBalanceHours, $empId]);
    }

    private function buildResult($status, $late, $perm, $reason, $amt)
    {
        return [
            'status' => $status,
            'late_minutes' => $late,
            'permission_used_minutes' => $perm,
            'deduction_reason' => $reason,
            'net_deduction_amount' => round($amt, 2)
        ];
    }

    public function recalculateRecord($recordId, $newIn, $newOut, $status)
    {
        // 1. Fetch Record & Employee
        $stmt = $this->pdo->prepare("SELECT r.*, e.*, e.id as emp_id FROM attendance_records r JOIN attendance_employees e ON r.employee_id = e.id WHERE r.id = ?");
        $stmt->execute([$recordId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data)
            return ['success' => false, 'message' => 'Record not found'];

        // 2. Handle "Excused" Special Case
        if ($status === 'excused') {
            $sql = "UPDATE attendance_records SET time_in=?, time_out=?, status='excused', late_minutes=0, permission_used_minutes=0, deduction_reason=NULL, net_deduction_amount=0 WHERE id=?";
            $this->pdo->prepare($sql)->execute([$newIn, $newOut, $recordId]);
            return ['success' => true];
        }

        // 3. Normal Recalculation
        $date = $data['date'];
        // Use new times if provided, else keep old
        $in = $newIn ?: $data['time_in'];
        $out = $newOut ?: $data['time_out'];

        // Re-run Calculation Logic
        $result = $this->calculateDailyLog($data, $date, $in, $out);

        // 4. Update DB
        $sql = "UPDATE attendance_records SET 
                time_in = ?, time_out = ?, 
                late_minutes = ?, permission_used_minutes = ?, 
                deduction_reason = ?, net_deduction_amount = ?, 
                status = ? 
                WHERE id = ?";

        $this->pdo->prepare($sql)->execute([
            $in,
            $out,
            $result['late_minutes'],
            $result['permission_used_minutes'],
            $result['deduction_reason'],
            $result['net_deduction_amount'],
            $result['status'], // e.g. 'late' or 'present'
            $recordId
        ]);

        return ['success' => true];
    }
}

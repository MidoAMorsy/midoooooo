<?php
require_once '../../includes/config.php';
requireLogin();

$empId = $_GET['emp_id'] ?? 0;
$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');

$emp = db()->query("SELECT * FROM attendance_employees WHERE id = " . intval($empId))->fetch(PDO::FETCH_ASSOC);
if (!$emp) die('Employee not found');

// Fetch Settings for Grace Info if needed
$settings = db()->query("SELECT * FROM attendance_settings")->fetchAll(PDO::FETCH_KEY_PAIR);

$records = db()->query("SELECT * FROM attendance_records WHERE employee_id = $empId AND MONTH(date) = $month AND YEAR(date) = $year ORDER BY date ASC")->fetchAll(PDO::FETCH_ASSOC);

$totalLate = 0;
$totalPenalties = 0;
$absentDays = 0;
$workDays = 0;

foreach ($records as $r) {
    if($r['status'] !== 'absent') $workDays++;
    $totalLate += $r['late_minutes'];
    $totalPenalties += $r['net_deduction_amount'];
    if ($r['status'] === 'absent') $absentDays++;
}

$gross = $emp['salary'] + $emp['incentives'];
$basicSalary = $emp['salary'];
$incentives = $emp['incentives']; // Fixed additives
$gross = $basicSalary + $incentives;
$net = $gross - $totalDeductions;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payslip - <?php echo e($emp['name_en']); ?> - <?php echo $monthName . ' ' . $year; ?></title>
    <style>
        
        .payslip-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 20px; }
        .box { padding: 20px; border: 1px solid #e5e7eb; border-radius: 8px; background: #f9fafb; position:relative; overflow:hidden; }
        .box-title { font-weight: 700; color: #4b5563; margin-bottom: 15px; font-size: 14px; text-transform: uppercase; border-bottom: 1px solid #e5e7eb; padding-bottom: 5px; letter-spacing:1px;}
        .row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 16px; border-bottom: 1px dashed #e5e7eb; padding-bottom: 4px; }
        .row:last-child { border: none; }
        .row strong { font-weight: 600; color: #111; }

        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; font-size: 13px; margin-top: 20px; }
        .data-table th { background: #f3f4f6; padding: 12px; border: 1px solid #e5e7eb; text-align: center; font-weight: 700; text-transform: uppercase; }
        .data-table td { padding: 10px; border: 1px solid #e5e7eb; text-align: center; }
        .row-alert { background-color: #fff1f2 !important; color: #9f1239; font-weight:600; }
        .row-excused { background-color: #f0fdf4 !important; color: #15803d; }

        .totals { display: flex; justify-content: flex-end; }
        .totals-box { width: 320px; border: 2px solid #000; border-radius: 8px; overflow: hidden; background: #fff; }
        .total-row { display: flex; justify-content: space-between; padding: 12px 20px; border-bottom: 1px solid #e5e7eb; font-size: 15px; }
        .total-row.net { background: #000; color: #fff; border: none; font-size: 20px; font-weight: 700; padding: 15px 20px; }
        
        .signatures { margin-top: 80px; display: flex; justify-content: space-between; padding: 0 50px; }
        .sig-line { border-top: 2px solid #000; width: 200px; text-align: center; padding-top: 10px; font-weight: 700; }
        
        button.print-btn {
             position: fixed; top: 20px; left: 20px; padding: 12px 25px; 
             background: #2563eb; color: #fff; border: none; border-radius: 50px; 
             box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.3); font-weight: 600; cursor: pointer; transition: transform 0.2s;
        }
        button.print-btn:hover { transform: scale(1.05); }

        @media print {
            body { padding: 0; background: #fff; }
            .no-print, button.print-btn { display: none !important; }
            .box { border: 1px solid #000; background: #fff; }
            .data-table th, .data-table td, .data-table { border: 1px solid #000; }
        }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">🖨️ طباعة التقرير / Print</button>

    <div class="header">
        <div>
            <h1>كشف راتب شهري</h1>
            <h2 style="margin:0;font-weight:400;font-size:16px;color:#555;">Monthly Payslip</h2>
        </div>
        <div style="text-align:left;">
            <p style="margin:0;font-weight:700;font-size:18px;">MidOo Smart Systems</p>
            <p style="margin:5px 0 0;color:#666;">Period: <strong><?php echo "$month / $year"; ?></strong></p>
        </div>
    </div>

    <div class="payslip-grid">
        <div class="box">
            <div class="box-title">Employee Details</div>
            <div class="row"><span>Name</span> <strong><?php echo $emp['name_ar']; ?></strong></div>
            <div class="row"><span>ID / Code</span> <strong><?php echo $emp['emp_code']; ?></strong></div>
            <div class="row"><span>Job Title</span> <strong>Employee</strong></div>
            <div class="row"><span>Department</span> <strong>General</strong></div>
        </div>
        <div class="box">
            <div class="box-title">Earnings & Balance</div>
            <div class="row"><span>Basic Salary</span> <strong><?php echo number_format($emp['salary'], 2); ?></strong></div>
            <div class="row"><span>Incentives</span> <strong><?php echo number_format($emp['incentives'], 2); ?></strong></div>
            <div class="row"><span>Perm. Bal.</span> <strong><?php echo $emp['permission_balance_hours']; ?> Hrs</strong></div>
            <div class="row"><span>Vac. Bal.</span> <strong><?php echo $emp['vacation_balance_days']; ?> Days</strong></div>
        </div>
    </div>

    <h3 style="border-bottom: 2px solid #eee; padding-bottom: 10px; margin-top: 40px;">سجل الحضور والخصومات / Attendance & Deductions</h3>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>In</th>
                <th>Out</th>
                <th>Late (Min)</th>
                <th>Deduction</th>
                <th>Reason</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($records)): ?>
                <tr><td colspan="7">No records found for this month.</td></tr>
            <?php endif; ?>
            <?php foreach ($records as $r): 
                $bgClass = '';
                if($r['net_deduction_amount'] > 0) $bgClass = 'row-alert';
                if($r['status'] === 'excused') $bgClass = 'row-excused';
            ?>
                <tr class="<?php echo $bgClass; ?>">
                    <td><?php echo date('d/m/Y', strtotime($r['date'])); ?></td>
                    <td><?php echo $r['time_in'] ? substr($r['time_in'], 0, 5) : '-'; ?></td>
                    <td><?php echo $r['time_out'] ? substr($r['time_out'], 0, 5) : '-'; ?></td>
                    <td><?php echo $r['late_minutes'] > 0 ? $r['late_minutes'] : '-'; ?></td>
                    <td><?php echo $r['net_deduction_amount'] > 0 ? number_format($r['net_deduction_amount'], 2) : '-'; ?></td>
                    <td style="font-size:12px;"><?php echo $r['deduction_reason'] ?: '-'; ?></td>
                    <td style="font-weight:600;"><?php echo ucfirst($r['status']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="totals">
        <div class="totals-box">
            <div class="total-row">
                <span>Total Earnings</span>
                <strong><?php echo number_format($gross, 2); ?></strong>
            </div>
            <div class="total-row" style="color:#dc2626;">
                <span>Total Deductions</span>
                <strong>- <?php echo number_format($totalPenalties, 2); ?></strong>
            </div>
            <div class="total-row">
                <span>Late Minutes</span>
                <span><?php echo $totalLate; ?> m</span>
            </div>
            <div class="total-row net">
                <span>Net Salary</span>
                <span><?php echo number_format($net, 2); ?> EGP</span>
            </div>
        </div>
    </div>

    <div class="signatures">
        <div class="sig-line">Employee Signature</div>
        <div class="sig-line">Manager Signature</div>
    </div>

</body>
</html>
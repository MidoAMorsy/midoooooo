<?php
/**
 * MidOo Smart Attendance System
 * Professional Dashboard UI (Revamped)
 */
require_once '../../includes/config.php';
$lang = getCurrentLanguage();
$seoOptions = ['title' => __('attendance_system')];
include '../../includes/header.php';
?>

<!-- Professional Styles & Animations -->
<style>
    :root {
        /* Aligning with ahmedmidoo.com themes */
        --primary: #0066cc;
        --primary-dark: #004999;
        --secondary: #00bcd4;
        --accent: #ff6b35;
        --bg: #f1f5f9;
        --surface: #ffffff;
        --text: #1e293b;
        --text-light: #64748b;
        --border: #e2e8f0;
        --success: #22c55e;
        --danger: #ef4444;
        --warning: #f59e0b;

        --primary-soft: rgba(0, 102, 204, 0.1);
        --success-soft: rgba(34, 197, 94, 0.1);
        --warning-soft: rgba(245, 158, 11, 0.1);
        --danger-soft: rgba(239, 68, 68, 0.1);

        --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
        --hover-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
    }

    * {
        box-sizing: border-box;
        outline: none;
    }

    body {
        font-family: 'Inter', system-ui, sans-serif;
        background: var(--bg);
        color: var(--text);
        margin: 0;
        padding: 0;
        line-height: 1.5;
        font-size: 14px;
    }

    /* Layout Grid */
    .layout {
        display: grid;
        grid-template-columns: 260px 1fr;
        min-height: 100vh;
    }

    /* Sidebar */
    .sidebar {
        background: var(--surface);
        border-right: 1px solid var(--border);
        display: flex;
        flex-direction: column;
        position: fixed;
        width: 260px;
        height: 100vh;
        z-index: 100;
    }

    .brand {
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        border-bottom: 1px solid var(--border);
    }

    .brand img {
        height: 32px;
        width: auto;
        object-fit: contain;
    }

    .brand span {
        font-weight: 700;
        font-size: 1.1rem;
        color: var(--text);
        letter-spacing: -0.025em;
    }

    .nav {
        padding: 1.5rem 1rem;
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .nav-item {
        padding: 0.75rem 1rem;
        border-radius: 8px;
        color: var(--text-light);
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-weight: 500;
        transition: all 0.2s;
        cursor: pointer;
        border: 1px solid transparent;
    }

    .nav-item:hover {
        background: #f1f5f9;
        color: var(--text);
    }

    .nav-item.active {
        background: #eff6ff;
        color: var(--primary);
        border-color: #dbeafe;
    }

    .nav-item i {
        width: 20px;
        text-align: center;
    }

    /* Main Content */
    .main {
        margin-left: 260px;
        padding: 2rem;
        max-width: 1600px;
    }

    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .page-title {
        font-size: 1.5rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }


    .data-table td {
        padding: 1rem;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .data-table tr:hover td {
        background: #f8fafc;
    }

    .data-table tr:last-child td {
        border-bottom: none;
    }

    /* Badges */
    .badge {
        padding: 0.35em 0.8em;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.75rem;
        letter-spacing: 0.025em;
    }

    .badge-soft-danger {
        background: var(--danger-soft);
        color: #dc2626;
    }

    .badge-soft-success {
        background: var(--success-soft);
        color: #16a34a;
    }

    .badge-soft-warning {
        background: var(--warning-soft);
        color: #d97706;
    }

    .badge-soft-secondary {
        background: #f1f5f9;
        color: #475569;
    }

    /* Modals */
    .modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.4);
        backdrop-filter: blur(4px);
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s;
    }

    .modal-backdrop.show {
        opacity: 1;
    }

    .modal-content {
        background: #fff;
        border-radius: 20px;
        width: 95%;
        max-width: 550px;
        padding: 2rem;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        transform: scale(0.95);
        transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .modal-backdrop.show .modal-content {
        transform: scale(1);
    }

    /* Filter Bar */
    .filter-bar {
        background: #fff;
        padding: 1.5rem;
        border-radius: 16px;
        box-shadow: var(--card-shadow);
        border: 1px solid #f1f5f9;
        margin-bottom: 1.5rem;
    }

    .form-control {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.6rem 1rem;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .form-control:focus {
        border-color: var(--primary-dark);
        box-shadow: 0 0 0 3px var(--primary-soft);
        outline: none;
    }
</style>

<div class="container section">

    <!-- Top Stats -->
    <div class="grid grid-4" style="gap:1.5rem;margin-bottom:2rem;">
        <div class="stat-card fade-in">
            <div class="d-flex justify-content-between">
                <div>
                    <h2 class="mb-1" id="stat-total-emp">0</h2>
                    <p class="text-muted text-sm m-0">Total Employees</p>
                </div>
                <div class="stat-icon" style="background:var(--primary-soft);color:var(--primary-dark);margin:0;"><i
                        class="fas fa-users"></i></div>
            </div>
        </div>
        <div class="stat-card fade-in delay-100">
            <div class="d-flex justify-content-between">
                <div>
                    <h2 class="mb-1" id="stat-late-min">0</h2>
                    <p class="text-muted text-sm m-0">Total Late (Mins)</p>
                </div>
                <div class="stat-icon" style="background:var(--warning-soft);color:#d97706;margin:0;"><i
                        class="fas fa-history"></i></div>
            </div>
        </div>
        <div class="stat-card fade-in delay-200">
            <div class="d-flex justify-content-between">
                <div>
                    <h2 class="mb-1" id="stat-deductions">0.00</h2>
                    <p class="text-muted text-sm m-0">Total Deductions</p>
                </div>
                <div class="stat-icon" style="background:var(--danger-soft);color:#dc2626;margin:0;"><i
                        class="fas fa-file-invoice-dollar"></i></div>
            </div>
        </div>
        <div class="stat-card fade-in delay-300">
            <div class="d-flex justify-content-between">
                <div>
                    <h2 class="mb-1" id="stat-present">0</h2>
                    <p class="text-muted text-sm m-0">Present Today</p>
                </div>
                <div class="stat-icon" style="background:var(--success-soft);color:#16a34a;margin:0;"><i
                        class="fas fa-check-circle"></i></div>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <div class="nav-tabs fade-in delay-300">
        <button class="nav-tab active" data-target="logs"><i class="fas fa-list-alt"></i> Attendance Logs</button>
        <button class="nav-tab" data-target="employees"><i class="fas fa-users-cog"></i> Employees</button>
        <button class="nav-tab" data-target="reports"><i class="fas fa-chart-line"></i> Reports</button>
        <button class="nav-tab" data-target="settings"><i class="fas fa-cogs"></i> Global Policies</button>
        <button class="nav-tab" data-target="import"><i class="fas fa-cloud-upload-alt"></i> Import</button>
    </div>

    <!-- TAB: LOGS -->
    <div id="tab-logs" class="tab-content fade-in">
        <div class="filter-bar">
            <div class="grid grid-3 gap-4 align-items-end">
                <div>
                    <label class="form-label text-xs uppercase text-muted font-bold mb-2">Filter by Employee</label>
                    <select id="log-employee-filter" class="form-control" onchange="loadLogs()">
                        <option value="">All Employees</option>
                        <!-- Populated via JS -->
                    </select>
                </div>
                <div>
                    <label class="form-label text-xs uppercase text-muted font-bold mb-2">Date Range</label>
                    <div class="d-flex gap-2">
                        <input type="date" id="log-date-start" class="form-control"
                            value="<?php echo date('Y-m-d'); ?>">
                        <input type="date" id="log-date-end" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <div class="flex-grow-1">
                        <label class="form-label text-xs uppercase text-muted font-bold mb-2">Status</label>
                        <select id="log-status-filter" class="form-control" onchange="loadLogs()">
                            <option value="">All Statuses</option>
                            <option value="late">Late</option>
                            <option value="absent">Absent</option>
                            <option value="excused">Excused</option>
                        </select>
                    </div>
                    <button class="btn btn-primary" onclick="loadLogs()" style="height:42px;margin-top:auto;"><i
                            class="fas fa-filter"></i> Apply</button>
                </div>
            </div>
            <div class="mt-3 d-flex gap-2">
                <button class="btn btn-xs btn-ghost" onclick="setPreset('today')">Today</button>
                <button class="btn btn-xs btn-ghost" onclick="setPreset('week')">Last 7 Days</button>
                <button class="btn btn-xs btn-ghost" onclick="setPreset('month')">This Month</button>
            </div>
        </div>

        <div class="table-card">
            <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                <h3 class="m-0 h5">Daily Records</h3>
                <button class="btn btn-sm btn-secondary" onclick="openReportModal()"><i class="fas fa-download"></i>
                    Export Report</button>
            </div>
            <div class="table-responsive">
                <table class="data-table w-full">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Date</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Late</th>
                            <th>Deduction</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="logs-body">
                        <tr>
                            <td colspan="8" class="text-center p-5 text-muted">Select filters and search...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB: EMPLOYEES -->
    <div id="tab-employees" class="tab-content" style="display:none;">
        <div class="table-card">
            <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                <h3 class="m-0 h5">Employees Directory</h3>
                <button class="btn btn-primary" onclick="showEmpModal()"><i class="fas fa-plus"></i> New
                    Employee</button>
            </div>
            <table class="data-table w-full">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Shift</th>
                        <th>Salary</th>
                        <th>Balance (H)</th>
                        <th>Vacation (D)</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="emp-body"></tbody>
            </table>
        </div>
    </div>

    <!-- TAB: REPORTS -->
    <div id="tab-reports" class="tab-content fade-in" style="display:none;">
        <div class="grid grid-2 gap-4 mb-4">
            <div class="stat-card text-center p-5 cursor-pointer hover:bg-gray-50 transition"
                onclick="openReportModal()">
                <div class="stat-icon mx-auto bg-primary-soft text-primary"><i class="fas fa-file-csv"></i></div>
                <h3>Export Data</h3>
                <p class="text-muted text-sm">Download detailed CSV reports for Payroll/HR.</p>
                <button class="btn btn-sm btn-outline-primary mt-3">Generate Report</button>
            </div>
            <div class="stat-card p-4">
                <h4 class="h6 mb-3 font-bold text-muted uppercase">Attendance Overview (This Month)</h4>
                <div style="height:200px; position:relative;">
                    <canvas id="chartPie"></canvas>
                </div>
            </div>
        </div>

        <div class="stat-card p-4">
            <h4 class="h6 mb-3 font-bold text-muted uppercase">Last 7 Days Trend</h4>
            <div style="height:300px; width:100%;">
                <canvas id="chartLine"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- TAB: SETTINGS -->
    <div id="tab-settings" class="tab-content" style="display:none;">
        <div class="stat-card p-4 mx-auto" style="max-width:800px;">
            <h3 class="mb-4">Global Policies & Shifts</h3>
            <form id="settings-form">
                <input type="hidden" name="action" value="save_settings">

                <div class="grid grid-2 gap-4 mb-4">
                    <div class="form-group">
                        <label>Standard Shift Start</label>
                        <input type="time" name="work_start_time" id="s_start" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Standard Shift End</label>
                        <input type="time" name="work_end_time" id="s_end" class="form-control">
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label>Grace Period (Minutes)</label>
                    <input type="number" name="grace_period_minutes" id="s_grace" class="form-control" placeholder="15">
                    <small class="text-muted">Lateness below this minutes is ignored.</small>
                </div>

                <hr class="my-4">
                <h4 class="h6 mb-3">Penalty Thresholds</h4>

                <div class="grid grid-2 gap-4">
                    <div class="form-group">
                        <label>Half Day Penalty Threshold (Mins)</label>
                        <input type="number" name="half_day_late_threshold_mins" id="s_half" class="form-control"
                            placeholder="60">
                        <small class="text-muted">Late > this = Half Day Penalty</small>
                    </div>
                    <div class="form-group">
                        <label>Full Day Penalty Threshold (Mins)</label>
                        <input type="number" name="full_day_late_threshold_mins" id="s_full" class="form-control"
                            placeholder="120">
                        <small class="text-muted">Late > this = Full Day Penalty</small>
                    </div>
                </div>

                <div class="text-right mt-4">
                    <button type="submit" class="btn btn-primary">Save Policies</button>
                </div>
            </form>
        </div>
    </div>

    <!-- TAB: IMPORT -->
    <div id="tab-import" class="tab-content" style="display:none;">
        <div class="stat-card p-5 mx-auto" style="max-width:600px;">
            <h3 class="text-center mb-4">Upload Attendance File</h3>
            <form id="import-form">
                <input type="hidden" name="action" value="import_data">

                <div class="d-flex justify-content-center mb-4">
                    <div class="text-center p-5 border rounded"
                        style="border:2px dashed #cbd5e1; width:100%; cursor:pointer; background:#f8fafc;"
                        onclick="document.getElementById('import-file').click()">
                        <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                        <p class="text-muted font-bold m-0">Click to Select CSV / Excel File</p>
                        <p class="text-xs text-muted m-0 mt-1">Supports drag and drop</p>
                    </div>
                </div>
                <input type="file" name="file" id="import-file" hidden accept=".csv,.xlsx,.xls"
                    onchange="document.querySelector('.text-center p').textContent = this.files[0].name">

                <div class="form-group mb-4">
                    <label class="form-label">Data Type</label>
                    <select name="type" class="form-control">
                        <option value="attendance">Attendance Logs (Fingerprint)</option>
                        <option value="employees">Employee Data (Updates Info)</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary w-full py-3">Start Import Process</button>
            </form>
        </div>
    </div>

</div>

<!-- MODALS -->

<!-- Edit Record Modal -->
<div id="edit-modal" class="modal-backdrop">
    <div class="modal-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="m-0 h5">Modify Attendance Record</h3>
            <button class="btn btn-ghost btn-sm" onclick="closeModal('edit-modal')"><i
                    class="fas fa-times"></i></button>
        </div>
        <form id="edit-form">
            <input type="hidden" name="action" value="update_record">
            <input type="hidden" name="id" id="e_rec_id">

            <div class="alert alert-info py-2 px-3 text-sm mb-4" id="e_rec_info"></div>

            <div class="grid grid-2 gap-4 mb-4">
                <div class="form-group"><label>Time In</label><input type="time" name="time_in" id="e_time_in"
                        class="form-control"></div>
                <div class="form-group"><label>Time Out</label><input type="time" name="time_out" id="e_time_out"
                        class="form-control"></div>
            </div>

            <div class="form-group mb-4">
                <label>Status Override</label>
                <select name="status" id="e_status" class="form-control">
                    <option value="present">Auto-Calculate (Standard)</option>
                    <option value="excused">✨ Mark as Excused (No Penalty)</option>
                </select>
            </div>

            <div class="text-right">
                <button type="button" class="btn btn-ghost" onclick="closeModal('edit-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Report Generator Modal -->
<div id="report-modal" class="modal-backdrop">
    <div class="modal-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="m-0 h5">Generate Report</h3>
            <button class="btn btn-ghost btn-sm" onclick="closeModal('report-modal')"><i
                    class="fas fa-times"></i></button>
        </div>
        <div class="form-group mb-3">
            <label>Report Type</label>
            <select id="rep-type" class="form-control">
                <option value="daily">Detailed Daily/Range Report</option>
                <option value="monthly">Monthly Payroll Summary</option>
            </select>
        </div>
        <div class="form-group mb-3">
            <label>Date Range</label>
            <div class="d-flex gap-2">
                <input type="date" id="rep-start" class="form-control" value="<?php echo date('Y-m-01'); ?>">
                <input type="date" id="rep-end" class="form-control" value="<?php echo date('Y-m-d'); ?>">
            </div>
        </div>
        <div class="form-group mb-4">
            <label>Filter Employee (Optional)</label>
            <select id="rep-emp" class="form-control">
                <option value="">All Employees</option>
                <!-- JS -->
            </select>
        </div>
        <button class="btn btn-primary w-full" onclick="downloadReport()">Download CSV Report</button>
    </div>
</div>

<!-- Employee Modal (Professional) -->
<div id="emp-modal" class="modal-backdrop">
    <div class="modal-content" style="max-width:650px;">
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
            <h3 class="m-0 h5 font-bold"><i class="fas fa-user-edit text-primary mr-2"></i> Employee Profile</h3>
            <button class="btn btn-ghost btn-sm" onclick="closeModal('emp-modal')"><i class="fas fa-times"></i></button>
        </div>

        <form id="emp-form">
            <input type="hidden" name="action" value="save_employee">
            <input type="hidden" name="id" id="emp_id_field">

            <!-- Modal Tabs -->
            <div class="d-flex gap-2 mb-4">
                <button type="button" class="btn btn-xs btn-primary rounded-pill px-3"
                    onclick="switchModalTab('m-basic', this)">Basic Info</button>
                <button type="button" class="btn btn-xs btn-ghost rounded-pill px-3"
                    onclick="switchModalTab('m-shift', this)">Shift & Policy</button>
                <button type="button" class="btn btn-xs btn-ghost rounded-pill px-3"
                    onclick="switchModalTab('m-balance', this)">Balances (Leave)</button>
            </div>

            <!-- TAB: Basic -->
            <div id="m-basic" class="modal-tab-content fade-in">
                <div class="grid grid-2 gap-4">
                    <div class="form-group span-2">
                        <label class="form-label text-xs uppercase text-muted font-bold">Full Name</label>
                        <input class="form-control" name="name" id="emp_name_f" required placeholder="e.g. John Doe">
                    </div>
                    <div class="form-group">
                        <label class="form-label text-xs uppercase text-muted font-bold">Employee Code</label>
                        <input class="form-control" name="emp_code" id="emp_code_f" required placeholder="e.g. 1001">
                    </div>
                    <div class="form-group">
                        <label class="form-label text-xs uppercase text-muted font-bold">Basic Salary</label>
                        <input type="number" class="form-control" name="salary" id="emp_sal_f" placeholder="0.00">
                    </div>
                </div>
            </div>

            <!-- TAB: Shift -->
            <div id="m-shift" class="modal-tab-content fade-in" style="display:none;">
                <div class="alert alert-info py-2 px-3 text-sm mb-3">
                    <i class="fas fa-info-circle"></i> Leave times empty to use <b>Global Default</b>.
                </div>
                <div class="grid grid-2 gap-4 mb-4">
                    <div class="form-group">
                        <label class="form-label text-xs uppercase text-muted font-bold">Shift Start</label>
                        <input type="time" class="form-control" name="shift_start" id="emp_ss_f">
                    </div>
                    <div class="form-group">
                        <label class="form-label text-xs uppercase text-muted font-bold">Shift End</label>
                        <input type="time" class="form-control" name="shift_end" id="emp_se_f">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label text-xs uppercase text-muted font-bold mb-2">Off Days (Weekly)</label>
                    <div class="d-flex gap-2 flex-wrap">
                        <label class="d-flex align-items-center gap-1 cursor-pointer"><input type="checkbox"
                                name="off_days[]" value="5"> Fri</label>
                        <label class="d-flex align-items-center gap-1 cursor-pointer"><input type="checkbox"
                                name="off_days[]" value="6"> Sat</label>
                        <label class="d-flex align-items-center gap-1 cursor-pointer"><input type="checkbox"
                                name="off_days[]" value="0"> Sun</label>
                        <label class="d-flex align-items-center gap-1 cursor-pointer"><input type="checkbox"
                                name="off_days[]" value="1"> Mon</label>
                        <label class="d-flex align-items-center gap-1 cursor-pointer"><input type="checkbox"
                                name="off_days[]" value="2"> Tue</label>
                        <label class="d-flex align-items-center gap-1 cursor-pointer"><input type="checkbox"
                                name="off_days[]" value="3"> Wed</label>
                        <label class="d-flex align-items-center gap-1 cursor-pointer"><input type="checkbox"
                                name="off_days[]" value="4"> Thu</label>
                    </div>
                </div>
            </div>

            <!-- TAB: Balance -->
            <div id="m-balance" class="modal-tab-content fade-in" style="display:none;">
                <div class="grid grid-2 gap-4">
                    <div class="form-group">
                        <label class="form-label text-xs uppercase text-muted font-bold">Permission Balance
                            (Hours)</label>
                        <input type="number" step="0.1" class="form-control" name="permission_balance_hours"
                            id="emp_pb_f" placeholder="0">
                        <small class="text-muted">Monthly permission allowance</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label text-xs uppercase text-muted font-bold">Annual Vacation (Days)</label>
                        <input type="number" step="0.5" class="form-control" name="vacation_balance_days" id="emp_vb_f"
                            placeholder="21">
                    </div>
                </div>
            </div>

            <div class="text-right mt-4 pt-3 border-top">
                <button type="button" class="btn btn-ghost" onclick="closeModal('emp-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save mr-2"></i> Save
                    Employee</button>
            </div>
        </form>
    </div>
</div>

<script>
    const API = 'ajax.php';
    let employeesList = [];

    // --- INITIALIZATION ---
    document.addEventListener('DOMContentLoaded', () => {
        setupTabs();
        setupFilters(); // Filter "Apply" logic
        loadStats();
        loadEmployees();
        setPreset('today');
    });

    // --- TABS & NAVIGATION ---
    function setupTabs() {
        document.querySelectorAll('.nav-tab').forEach(t => {
            t.addEventListener('click', () => {
                document.querySelectorAll('.nav-tab').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.style.display = 'none');
                t.classList.add('active');
                document.getElementById('tab-' + t.dataset.target).style.display = 'block';
                const target = t.dataset.target;
                if (target === 'logs') loadLogs();
                if (target === 'employees') renderEmployees();
                if (target === 'settings') loadSettings();
                if (target === 'reports') loadAnalytics();
            });
        });
    }

    // Modal Tabs
    window.switchModalTab = function (tabId, btn) {
        // Toggle Buttons
        const parent = btn.parentElement;
        parent.querySelectorAll('button').forEach(b => {
            b.classList.remove('btn-primary');
            b.classList.add('btn-ghost');
        });
        btn.classList.remove('btn-ghost');
        btn.classList.add('btn-primary');

        // Toggle Content
        const form = btn.closest('form');
        form.querySelectorAll('.modal-tab-content').forEach(c => c.style.display = 'none');
        document.getElementById(tabId).style.display = 'block';
    }

    // --- SETTINGS ---
    function loadSettings() {
        fetch(`${API}?action=get_settings`)
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    const d = res.data;
                    document.getElementById('s_start').value = d.work_start_time || '09:00';
                    document.getElementById('s_end').value = d.work_end_time || '17:00';
                    document.getElementById('s_grace').value = d.grace_period_minutes || '15';
                    document.getElementById('s_half').value = d.half_day_late_threshold_mins || '60';
                    document.getElementById('s_full').value = d.full_day_late_threshold_mins || '120';
                }
            });
    }
    document.getElementById('settings-form').addEventListener('submit', function (e) {
        e.preventDefault();
        saveForm(this, () => alert('Policies Saved Successfully!'));
    });

    // --- EMPLOYEES ---
    function loadEmployees() {
        fetch(`${API}?action=get_employees`)
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    employeesList = res.data;
                    populateEmpDropdowns();
                    // if tab active
                    if (document.querySelector('.nav-tab[data-target="employees"]').classList.contains('active')) {
                        renderEmployees();
                    }
                }
            });
    }

    function populateEmpDropdowns() {
        const opts = '<option value="">All Employees</option>' +
            employeesList.map(e => `<option value="${e.id}">${e.name_en} (${e.emp_code})</option>`).join('');
        const logFilter = document.getElementById('log-employee-filter');
        if (logFilter) logFilter.innerHTML = opts;
        const repFilter = document.getElementById('rep-emp');
        if (repFilter) repFilter.innerHTML = opts;
    }

    function renderEmployees() {
        const tbody = document.getElementById('emp-body');
        if (employeesList.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center p-5">No employees found.</td></tr>`;
            return;
        }
        tbody.innerHTML = employeesList.map(e => {
            const eStr = JSON.stringify(e).replace(/'/g, "&apos;").replace(/"/g, "&quot;");
            return `
            <tr>
                <td>${e.emp_code}</td>
                <td class="font-bold">${e.name_en}</td>
                <td>${e.shift_start ? e.shift_start.substring(0, 5) : 'Global'} - ${e.shift_end ? e.shift_end.substring(0, 5) : 'Global'}</td>
                <td>${parseFloat(e.salary).toLocaleString()}</td>
                <td>${e.permission_balance_hours}h</td>
                <td>${e.vacation_balance_days}d</td>
                <td class="text-right">
                    <button class="btn btn-sm btn-ghost text-primary" onclick='showEmpModal(${eStr})'><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-ghost text-danger" onclick="deleteEmployee(${e.id})"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `}).join('');
    }

    function showEmpModal(data = null) {
        if (data && typeof data === 'string') data = JSON.parse(data);

        const f = document.getElementById('emp-form');
        f.reset();
        document.getElementById('emp_id_field').value = '';

        // Reset Tabs
        document.querySelector('button[onclick*="m-basic"]').click(); // Click first tab
        // Clear checkboxes
        f.querySelectorAll('input[type="checkbox"]').forEach(c => c.checked = false);

        if (data) {
            document.getElementById('emp_id_field').value = data.id;
            document.getElementById('emp_name_f').value = data.name_en;
            document.getElementById('emp_code_f').value = data.emp_code;
            document.getElementById('emp_sal_f').value = data.salary;
            document.getElementById('emp_ss_f').value = data.shift_start || '';
            document.getElementById('emp_se_f').value = data.shift_end || '';
            document.getElementById('emp_pb_f').value = data.permission_balance_hours || 0;
            document.getElementById('emp_vb_f').value = data.vacation_balance_days || 21;

            // Handle Off Days (Comma separated '5,6')
            if (data.off_days) {
                const days = data.off_days.split(',');
                days.forEach(d => {
                    const cb = f.querySelector(`input[value="${d.trim()}"]`);
                    if (cb) cb.checked = true;
                });
            }
        }
        openModal('emp-modal');
    }

    document.getElementById('emp-form').addEventListener('submit', function (e) { e.preventDefault(); saveForm(this, () => { closeModal('emp-modal'); loadEmployees(); }); });

    function deleteEmployee(id) {
        if (confirm('Delete this employee?')) {
            const fd = new FormData(); fd.append('action', 'delete_employee'); fd.append('id', id);
            fetch(API, { method: 'POST', body: fd }).then(r => r.json()).then(res => { if (res.status === 'success') loadEmployees(); });
        }
    }

    // --- LOGS & STATS ---
    function setupFilters() {
        // Debounce or just keep regular change
    }

    function loadStats() {
        const d = new Date();
        fetch(`${API}?action=get_stats&month=${d.getMonth() + 1}&year=${d.getFullYear()}`)
            .then(r => r.json()).then(res => {
                if (res.status === 'success') {
                    const d = res.data;
                    document.getElementById('stat-total-emp').textContent = d.total_employees;
                    document.getElementById('stat-late-min').textContent = d.total_late_minutes;
                    document.getElementById('stat-deductions').textContent = parseFloat(d.total_deductions).toFixed(2);
                    document.getElementById('stat-present').textContent = d.present_today;
                }
            });
    }

    function loadLogs() {
        const tbody = document.getElementById('logs-body');
        tbody.innerHTML = `<tr><td colspan="8" class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i></td></tr>`;

        const params = new URLSearchParams({
            action: 'get_records',
            start: document.getElementById('log-date-start').value,
            end: document.getElementById('log-date-end').value,
            status: document.getElementById('log-status-filter').value,
            emp_id: document.getElementById('log-employee-filter').value
        });

        fetch(`${API}?${params}`)
            .then(r => r.json())
            .then(res => {
                const records = res.data || [];
                if (records.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="8" class="text-center p-5 text-muted">No records found.</td></tr>`;
                    return;
                }
                tbody.innerHTML = records.map(r => {
                    const rStr = JSON.stringify(r).replace(/'/g, "&apos;").replace(/"/g, "&quot;");
                    return `
                    <tr class="${r.status === 'excused' ? 'bg-success-soft' : ''} fade-in">
                        <td>
                            <div class="font-bold text-dark">${r.employee_name || 'Unknown'}</div>
                            <div class="text-xs text-muted">${r.emp_code || 'N/A'}</div>
                        </td>
                        <td>${r.date}</td>
                        <td>${r.time_in ? r.time_in.substring(0, 5) : '-'}</td>
                        <td>${r.time_out ? r.time_out.substring(0, 5) : '-'}</td>
                        <td>${r.late_minutes > 0 ? `<span class="badge badge-soft-warning">${r.late_minutes} min</span>` : '-'}</td>
                        <td>${parseFloat(r.net_deduction_amount) > 0 ? `<span class="badge badge-soft-danger">${parseFloat(r.net_deduction_amount).toFixed(2)}</span>` : '-'}</td>
                        <td><span class="badge ${getBadge(r.status)}">${r.status_label || r.status}</span></td>
                        <td class="text-right">
                            <button class="btn btn-sm btn-ghost" onclick='openEditModal(${rStr})'><i class="fas fa-edit text-primary"></i></button>
                            <a href="print_payslip.php?emp_id=${r.employee_id}&month=${new Date(r.date).getMonth() + 1}&year=${new Date(r.date).getFullYear()}" 
                               target="_blank" class="btn btn-sm btn-ghost"><i class="fas fa-file-invoice text-dark"></i></a>
                        </td>
                    </tr>
                `}).join('');
            });
    }

    function setPreset(type) {
        const end = new Date();
        const start = new Date();
        if (type === 'week') start.setDate(end.getDate() - 7);
        if (type === 'month') start.setDate(1);

        document.getElementById('log-date-end').value = end.toISOString().split('T')[0];
        document.getElementById('log-date-start').value = start.toISOString().split('T')[0];
        loadLogs();
    }

    // --- ANALYTICS ---
    let chartPieInstance = null;
    let chartLineInstance = null;
    function loadAnalytics() {
        fetch(`${API}?action=get_analytics`).then(r => r.json()).then(res => { if (res.status === 'success') renderCharts(res.data); });
    }
    function renderCharts(data) {
        if (chartPieInstance) chartPieInstance.destroy();
        if (chartLineInstance) chartLineInstance.destroy();

        const ctxPie = document.getElementById('chartPie').getContext('2d');
        chartPieInstance = new Chart(ctxPie, {
            type: 'doughnut',
            data: {
                labels: ['Present', 'Late', 'Absent', 'Excused'],
                datasets: [{ data: [data.pie.present, data.pie.late, data.pie.absent, data.pie.excused], backgroundColor: ['#22c55e', '#f59e0b', '#ef4444', '#3b82f6'], borderWidth: 0 }]
            },
            options: { plugins: { legend: { position: 'right' } }, responsive: true, maintainAspectRatio: false }
        });

        const ctxLine = document.getElementById('chartLine').getContext('2d');
        chartLineInstance = new Chart(ctxLine, {
            type: 'bar',
            data: {
                labels: data.trend.map(i => i.date),
                datasets: [
                    { label: 'Present', data: data.trend.map(i => i.attendance), backgroundColor: '#3b82f6', borderRadius: 4 },
                    { label: 'Absent', data: data.trend.map(i => i.absent), backgroundColor: '#ef4444', borderRadius: 4 }
                ]
            },
            options: { scales: { x: { grid: { display: false } }, y: { beginAtZero: true } }, responsive: true, maintainAspectRatio: false }
        });
    }

    // --- UTILS ---
    function openModal(id) {
        const m = document.getElementById(id);
        m.style.display = 'flex';
        setTimeout(() => m.classList.add('show'), 10);
    }
    function closeModal(id) {
        const m = document.getElementById(id);
        m.classList.remove('show');
        setTimeout(() => m.style.display = 'none', 300);
    }
    function getBadge(s) {
        if (s === 'late') return 'badge-soft-warning';
        if (s === 'absent') return 'badge-soft-danger';
        if (s === 'excused') return 'badge-soft-success';
        return 'badge-soft-success';
    }

    // Edit Modal
    function openEditModal(record) {
        if (typeof record === 'string') record = JSON.parse(record);
        document.getElementById('e_rec_id').value = record.id;
        document.getElementById('e_time_in').value = record.time_in;
        document.getElementById('e_time_out').value = record.time_out;
        document.getElementById('e_rec_info').innerHTML = `Editing <b>${record.employee_name}</b> for date <b>${record.date}</b>`;
        document.getElementById('e_status').value = record.status === 'excused' ? 'excused' : 'present';
        openModal('edit-modal');
    }
    document.getElementById('edit-form').addEventListener('submit', function (e) {
        e.preventDefault();
        saveForm(this, () => { closeModal('edit-modal'); loadLogs(); });
    });

    // Report
    function openReportModal() { openModal('report-modal'); }
    function downloadReport() {
        const type = document.getElementById('rep-type').value;
        const start = document.getElementById('rep-start').value;
        const end = document.getElementById('rep-end').value;
        const emp = document.getElementById('rep-emp').value;
        window.location.href = `${API}?action=export_report&type=${type}&start=${start}&end=${end}&emp_id=${emp}`;
        closeModal('report-modal');
    }

    // Save Form
    function saveForm(form, onSuccess, onError = null) {
        const fd = new FormData(form);
        fetch(API, { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    if (onSuccess) onSuccess();
                } else {
                    alert(res.message);
                    if (onError) onError();
                }
            })
            .catch(e => { alert('Error: ' + e); if (onError) onError(); });
    }

    // Import
    document.getElementById('import-form').addEventListener('submit', function (e) {
        e.preventDefault();
        saveForm(this, () => { alert('Done'); loadStats(); loadEmployees(); });
    });

</script>

<?php include '../../includes/footer.php'; ?>
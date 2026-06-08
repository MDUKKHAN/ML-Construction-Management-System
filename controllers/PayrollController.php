<?php
/**
 * Payroll Controller
 */

class PayrollController extends Controller {
    private $payrollModel;
    private $employeeModel;
    private $attendanceModel;
    private $activityModel;

    public function __construct() {
        parent::__construct();
        $this->requireLogin();
        $this->payrollModel = new Payroll();
        $this->employeeModel = new Employee();
        $this->attendanceModel = new Attendance();
        $this->activityModel = new Activity();
    }

    public function index() {
        $this->requireRole(ROLE_MANAGER);

        $page = isset($_GET['pg']) ? (int)$_GET['pg'] : 1;
        $limit = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;

        $status = $_GET['status'] ?? '';

        if (!empty($status)) {
            $payrolls = $this->payrollModel->getByStatus($status, $limit, $offset);
        } else {
            $payrolls = [];
        }

        $data = [
            'payrolls' => $payrolls,
            'page' => $page,
            'status' => $status,
            'user' => $this->getCurrentUser(),
            'csrf_token' => $this->getCsrfToken()
        ];

        $this->loadView('payroll/index', $data);
    }

    public function generate() {
        $this->requireRole(ROLE_ADMIN);

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $error = 'Invalid request';
            } else {
                $periodStart = $_POST['period_start'] ?? '';
                $periodEnd = $_POST['period_end'] ?? '';
                $employeeId = $_POST['employee_id'] ?? null;

                if (empty($periodStart) || empty($periodEnd)) {
                    $error = 'Period dates are required';
                } else {
                    $employee = $this->employeeModel->getById($employeeId);
                    if (!$employee) {
                        $error = 'Employee not found';
                    } else {
                        // Calculate payroll
                        $attendance = $this->attendanceModel->getByEmployeeMonth(
                            $employeeId,
                            date('m', strtotime($periodStart)),
                            date('Y', strtotime($periodStart))
                        );

                        $daysWorked = 0;
                        $totalOvertime = 0;

                        foreach ($attendance as $record) {
                            if ($record['status'] === 'present') {
                                $daysWorked++;
                            }
                            $totalOvertime += $record['overtime_hours'];
                        }

                        $basicSalary = $daysWorked * $employee['day_salary'];
                        $overtimePay = $totalOvertime * ($employee['day_salary'] / 8);
                        $netSalary = $basicSalary + $overtimePay;

                        $payrollData = [
                            'employee_id' => $employeeId,
                            'period_start' => $periodStart,
                            'period_end' => $periodEnd,
                            'days_worked' => $daysWorked,
                            'day_salary' => $employee['day_salary'],
                            'basic_salary' => $basicSalary,
                            'overtime_hours' => $totalOvertime,
                            'overtime_pay' => $overtimePay,
                            'net_salary' => $netSalary
                        ];

                        $payrollId = $this->payrollModel->create($payrollData);
                        $this->activityModel->log($_SESSION['user_id'], 'generate', 'payroll', $payrollId, 'Generated payroll');
                        $success = 'Payroll generated successfully';
                    }
                }
            }
        }

        $employees = $this->employeeModel->getByStatus('active');
        $data = [
            'employees' => $employees,
            'error' => $error,
            'success' => $success,
            'user' => $this->getCurrentUser(),
            'csrf_token' => $this->getCsrfToken()
        ];

        $this->loadView('payroll/generate', $data);
    }

    public function slip() {
        $payrollId = $_GET['id'] ?? null;
        if (!$payrollId) {
            $this->redirect('/index.php?page=payroll');
        }

        $payroll = $this->payrollModel->getById($payrollId);
        if (!$payroll) {
            $this->redirect('/index.php?page=payroll');
        }

        $employee = $this->employeeModel->getById($payroll['employee_id']);

        $data = [
            'payroll' => $payroll,
            'employee' => $employee,
            'user' => $this->getCurrentUser(),
            'csrf_token' => $this->getCsrfToken()
        ];

        $this->loadView('payroll/slip', $data);
    }

    public function report() {
        $this->requireRole(ROLE_MANAGER);

        $month = $_GET['month'] ?? date('m');
        $year = $_GET['year'] ?? date('Y');

        $periodStart = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01';
        $periodEnd = date('Y-m-t', strtotime($periodStart));

        $payrolls = $this->payrollModel->getByPeriod($periodStart, $periodEnd);

        $data = [
            'payrolls' => $payrolls,
            'month' => $month,
            'year' => $year,
            'user' => $this->getCurrentUser(),
            'csrf_token' => $this->getCsrfToken()
        ];

        $this->loadView('payroll/report', $data);
    }
}

?>
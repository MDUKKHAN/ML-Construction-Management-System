<?php
/**
 * Report Controller
 */

class ReportController extends Controller {
    private $employeeModel;
    private $attendanceModel;
    private $payrollModel;
    private $siteModel;
    private $inventoryModel;
    private $activityModel;

    public function __construct() {
        parent::__construct();
        $this->requireLogin();
        $this->employeeModel = new Employee();
        $this->attendanceModel = new Attendance();
        $this->payrollModel = new Payroll();
        $this->siteModel = new Site();
        $this->inventoryModel = new Inventory();
        $this->activityModel = new Activity();
    }

    public function index() {
        $data = [
            'user' => $this->getCurrentUser(),
            'csrf_token' => $this->getCsrfToken()
        ];

        $this->loadView('reports/index', $data);
    }

    public function employeeReport() {
        $employees = $this->employeeModel->getAll();
        $totalEmployees = $this->employeeModel->count();
        $activeEmployees = $this->employeeModel->countByStatus('active');

        $data = [
            'employees' => $employees,
            'totalEmployees' => $totalEmployees,
            'activeEmployees' => $activeEmployees,
            'reportType' => 'Employee Report',
            'user' => $this->getCurrentUser(),
            'csrf_token' => $this->getCsrfToken()
        ];

        $this->activityModel->log($_SESSION['user_id'], 'view', 'reports', null, 'Viewed employee report');
        $this->loadView('reports/employee', $data);
    }

    public function attendanceReport() {
        $month = $_GET['month'] ?? date('m');
        $year = $_GET['year'] ?? date('Y');
        $employeeId = $_GET['employee_id'] ?? null;

        $employees = $this->employeeModel->getAll();
        $attendance = [];

        if ($employeeId) {
            $attendance = $this->attendanceModel->getByEmployeeMonth($employeeId, $month, $year);
        }

        $data = [
            'month' => $month,
            'year' => $year,
            'employees' => $employees,
            'attendance' => $attendance,
            'employeeId' => $employeeId,
            'reportType' => 'Attendance Report',
            'user' => $this->getCurrentUser(),
            'csrf_token' => $this->getCsrfToken()
        ];

        $this->activityModel->log($_SESSION['user_id'], 'view', 'reports', null, 'Viewed attendance report');
        $this->loadView('reports/attendance', $data);
    }

    public function payrollReport() {
        $month = $_GET['month'] ?? date('m');
        $year = $_GET['year'] ?? date('Y');

        $periodStart = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01';
        $periodEnd = date('Y-m-t', strtotime($periodStart));

        $payrolls = $this->payrollModel->getByPeriod($periodStart, $periodEnd);

        $totalPaid = 0;
        foreach ($payrolls as $payroll) {
            $totalPaid += $payroll['net_salary'];
        }

        $data = [
            'month' => $month,
            'year' => $year,
            'payrolls' => $payrolls,
            'totalPaid' => $totalPaid,
            'reportType' => 'Payroll Report',
            'user' => $this->getCurrentUser(),
            'csrf_token' => $this->getCsrfToken()
        ];

        $this->activityModel->log($_SESSION['user_id'], 'view', 'reports', null, 'Viewed payroll report');
        $this->loadView('reports/payroll', $data);
    }

    public function siteReport() {
        $sites = $this->siteModel->getAll();
        $totalSites = $this->siteModel->count();
        $activeSites = $this->siteModel->countByStatus('active');

        $data = [
            'sites' => $sites,
            'totalSites' => $totalSites,
            'activeSites' => $activeSites,
            'reportType' => 'Site Report',
            'user' => $this->getCurrentUser(),
            'csrf_token' => $this->getCsrfToken()
        ];

        $this->activityModel->log($_SESSION['user_id'], 'view', 'reports', null, 'Viewed site report');
        $this->loadView('reports/site', $data);
    }

    public function inventoryReport() {
        $items = $this->inventoryModel->getAll();
        $totalItems = $this->inventoryModel->count();
        $totalValue = 0;

        foreach ($items as $item) {
            $totalValue += ($item['quantity'] * $item['unit_price']);
        }

        $data = [
            'items' => $items,
            'totalItems' => $totalItems,
            'totalValue' => $totalValue,
            'reportType' => 'Inventory Report',
            'user' => $this->getCurrentUser(),
            'csrf_token' => $this->getCsrfToken()
        ];

        $this->activityModel->log($_SESSION['user_id'], 'view', 'reports', null, 'Viewed inventory report');
        $this->loadView('reports/inventory', $data);
    }
}

?>
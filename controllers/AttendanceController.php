<?php
/**
 * Attendance Controller
 */

class AttendanceController extends Controller {
    private $attendanceModel;
    private $employeeModel;
    private $siteModel;
    private $activityModel;

    public function __construct() {
        parent::__construct();
        $this->requireLogin();
        $this->attendanceModel = new Attendance();
        $this->employeeModel = new Employee();
        $this->siteModel = new Site();
        $this->activityModel = new Activity();
    }

    public function index() {
        $date = $_GET['date'] ?? date('Y-m-d');
        $employees = $this->employeeModel->getByStatus('active');

        $data = [
            'date' => $date,
            'employees' => $employees,
            'user' => $this->getCurrentUser(),
            'csrf_token' => $this->getCsrfToken()
        ];

        $this->loadView('attendance/index', $data);
    }

    public function mark() {
        $this->requireRole(ROLE_MANAGER);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $this->json(['success' => false, 'message' => 'Invalid request'], 400);
            }

            $employeeId = $_POST['employee_id'] ?? null;
            $date = $_POST['date'] ?? date('Y-m-d');
            $status = $_POST['status'] ?? 'absent';
            $checkIn = $_POST['check_in'] ?? null;
            $checkOut = $_POST['check_out'] ?? null;

            // Check if attendance already exists
            $existing = $this->attendanceModel->getByEmployeeAndDate($employeeId, $date);

            $data = [
                'status' => $status,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'marked_by' => $_SESSION['user_id']
            ];

            if ($checkIn && $checkOut) {
                $data['working_hours'] = Helper::calculateWorkingHours($checkIn, $checkOut);
                $data['overtime_hours'] = Helper::calculateOvertimeHours($data['working_hours']);
            }

            if ($existing) {
                $this->attendanceModel->update($existing['id'], $data);
                $message = 'Attendance updated successfully';
            } else {
                $data['employee_id'] = $employeeId;
                $data['attendance_date'] = $date;
                $this->attendanceModel->create($data);
                $message = 'Attendance marked successfully';
            }

            $this->activityModel->log($_SESSION['user_id'], 'mark', 'attendance', $employeeId, $message);
            $this->json(['success' => true, 'message' => $message]);
        }
    }

    public function report() {
        $month = $_GET['month'] ?? date('m');
        $year = $_GET['year'] ?? date('Y');
        $employeeId = $_GET['employee_id'] ?? null;

        if ($employeeId) {
            $attendance = $this->attendanceModel->getByEmployeeMonth($employeeId, $month, $year);
            $employee = $this->employeeModel->getById($employeeId);
        } else {
            $attendance = [];
            $employee = null;
        }

        $data = [
            'month' => $month,
            'year' => $year,
            'attendance' => $attendance,
            'employee' => $employee,
            'employees' => $this->employeeModel->getAll(),
            'user' => $this->getCurrentUser(),
            'csrf_token' => $this->getCsrfToken()
        ];

        $this->loadView('attendance/report', $data);
    }

    public function history() {
        $employeeId = $_GET['employee_id'] ?? null;
        $page = isset($_GET['pg']) ? (int)$_GET['pg'] : 1;
        $limit = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;

        if (!$employeeId) {
            $this->redirect('/index.php?page=attendance');
        }

        $employee = $this->employeeModel->getById($employeeId);
        if (!$employee) {
            $this->redirect('/index.php?page=attendance');
        }

        $data = [
            'employee' => $employee,
            'page' => $page,
            'user' => $this->getCurrentUser(),
            'csrf_token' => $this->getCsrfToken()
        ];

        $this->loadView('attendance/history', $data);
    }
}

?>
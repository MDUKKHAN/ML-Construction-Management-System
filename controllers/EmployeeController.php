<?php
/**
 * Employee Controller
 */

class EmployeeController extends Controller {
    private $employeeModel;
    private $siteModel;
    private $activityModel;

    public function __construct() {
        parent::__construct();
        $this->requireLogin();
        $this->employeeModel = new Employee();
        $this->siteModel = new Site();
        $this->activityModel = new Activity();
    }

    public function index() {
        $page = isset($_GET['pg']) ? (int)$_GET['pg'] : 1;
        $limit = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;

        $search = isset($_GET['search']) ? Database::sanitize($_GET['search']) : '';

        if (!empty($search)) {
            $employees = $this->employeeModel->search($search, $limit, $offset);
        } else {
            $employees = $this->employeeModel->getAll($limit, $offset);
        }

        $data = [
            'employees' => $employees,
            'page' => $page,
            'search' => $search,
            'user' => $this->getCurrentUser(),
            'csrf_token' => $this->getCsrfToken()
        ];

        $this->loadView('employees/index', $data);
    }

    public function add() {
        $this->requireRole(ROLE_ADMIN);

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $error = 'Invalid request';
            } else {
                $data = [
                    'full_name' => Database::sanitize($_POST['full_name'] ?? ''),
                    'email' => Database::sanitize($_POST['email'] ?? ''),
                    'phone' => Database::sanitize($_POST['phone'] ?? ''),
                    'position' => Database::sanitize($_POST['position'] ?? ''),
                    'site_id' => $_POST['site_id'] ?? null,
                    'hire_date' => $_POST['hire_date'] ?? '',
                    'day_salary' => $_POST['day_salary'] ?? 0,
                    'employment_type' => $_POST['employment_type'] ?? 'full_time'
                ];

                if (empty($data['full_name']) || empty($data['email']) || empty($data['phone'])) {
                    $error = 'All required fields must be filled';
                } else if ($this->employeeModel->emailExists($data['email'])) {
                    $error = 'Email already exists';
                } else {
                    $employeeId = $this->employeeModel->create($data);
                    $this->activityModel->log($_SESSION['user_id'], 'create', 'employees', $employeeId, 'Added employee: ' . $data['full_name']);
                    $success = 'Employee added successfully';
                }
            }
        }

        $sites = $this->siteModel->getAll();
        $data = [
            'sites' => $sites,
            'error' => $error,
            'success' => $success,
            'user' => $this->getCurrentUser(),
            'csrf_token' => $this->getCsrfToken()
        ];

        $this->loadView('employees/add', $data);
    }

    public function edit() {
        $this->requireRole(ROLE_ADMIN);

        $employeeId = $_GET['id'] ?? null;
        if (!$employeeId) {
            $this->redirect('/index.php?page=employees');
        }

        $employee = $this->employeeModel->getById($employeeId);
        if (!$employee) {
            $this->redirect('/index.php?page=employees');
        }

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $error = 'Invalid request';
            } else {
                $data = [
                    'full_name' => Database::sanitize($_POST['full_name'] ?? ''),
                    'phone' => Database::sanitize($_POST['phone'] ?? ''),
                    'position' => Database::sanitize($_POST['position'] ?? ''),
                    'site_id' => $_POST['site_id'] ?? null,
                    'day_salary' => $_POST['day_salary'] ?? 0,
                    'status' => $_POST['status'] ?? 'active'
                ];

                if (empty($data['full_name']) || empty($data['phone'])) {
                    $error = 'Required fields must be filled';
                } else {
                    $this->employeeModel->update($employeeId, $data);
                    $this->activityModel->log($_SESSION['user_id'], 'update', 'employees', $employeeId, 'Updated employee');
                    $success = 'Employee updated successfully';
                    $employee = $this->employeeModel->getById($employeeId);
                }
            }
        }

        $sites = $this->siteModel->getAll();
        $data = [
            'employee' => $employee,
            'sites' => $sites,
            'error' => $error,
            'success' => $success,
            'user' => $this->getCurrentUser(),
            'csrf_token' => $this->getCsrfToken()
        ];

        $this->loadView('employees/edit', $data);
    }

    public function view() {
        $employeeId = $_GET['id'] ?? null;
        if (!$employeeId) {
            $this->redirect('/index.php?page=employees');
        }

        $employee = $this->employeeModel->getById($employeeId);
        if (!$employee) {
            $this->redirect('/index.php?page=employees');
        }

        $data = [
            'employee' => $employee,
            'user' => $this->getCurrentUser(),
            'csrf_token' => $this->getCsrfToken()
        ];

        $this->loadView('employees/view', $data);
    }

    public function delete() {
        $this->requireRole(ROLE_ADMIN);

        $employeeId = $_GET['id'] ?? null;
        if (!$employeeId) {
            $this->json(['success' => false, 'message' => 'Invalid employee ID'], 400);
        }

        $this->employeeModel->delete($employeeId);
        $this->activityModel->log($_SESSION['user_id'], 'delete', 'employees', $employeeId, 'Deleted employee');

        $this->json(['success' => true, 'message' => 'Employee deleted successfully']);
    }
}

?>
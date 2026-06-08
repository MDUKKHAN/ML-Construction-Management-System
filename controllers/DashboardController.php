<?php
/**
 * Dashboard Controller
 */

class DashboardController extends Controller {
    private $employeeModel;
    private $attendanceModel;
    private $siteModel;
    private $payrollModel;
    private $activityModel;

    public function __construct() {
        parent::__construct();
        $this->requireLogin();
        $this->employeeModel = new Employee();
        $this->attendanceModel = new Attendance();
        $this->siteModel = new Site();
        $this->payrollModel = new Payroll();
        $this->activityModel = new Activity();
    }

    public function index() {
        $data = [];

        // Employee Statistics
        $data['total_employees'] = $this->employeeModel->count();
        $data['active_employees'] = $this->employeeModel->countByStatus('active');

        // Site Statistics
        $data['total_sites'] = $this->siteModel->count();
        $data['active_sites'] = $this->siteModel->countByStatus('active');

        // Payroll Statistics
        $data['pending_payroll'] = $this->payrollModel->countByStatus('pending');
        $data['total_payroll'] = $this->payrollModel->count();

        // Today's Attendance
        $today = date('Y-m-d');
        $data['today_date'] = $today;

        // Recent Activities
        $data['recent_activities'] = $this->activityModel->getRecent(10);

        // Current User
        $data['user'] = $this->getCurrentUser();
        $data['csrf_token'] = $this->getCsrfToken();

        $this->loadView('dashboard/index', $data);
    }
}

?>
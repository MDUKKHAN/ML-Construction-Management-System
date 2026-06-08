<?php
/**
 * Site Controller
 */

class SiteController extends Controller {
    private $siteModel;
    private $fileModel;
    private $activityModel;

    public function __construct() {
        parent::__construct();
        $this->requireLogin();
        $this->siteModel = new Site();
        $this->activityModel = new Activity();
    }

    public function index() {
        $page = isset($_GET['pg']) ? (int)$_GET['pg'] : 1;
        $limit = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;

        $sites = $this->siteModel->getAll($limit, $offset);

        $data = [
            'sites' => $sites,
            'page' => $page,
            'user' => $this->getCurrentUser(),
            'csrf_token' => $this->getCsrfToken()
        ];

        $this->loadView('sites/index', $data);
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
                    'site_name' => Database::sanitize($_POST['site_name'] ?? ''),
                    'site_address' => Database::sanitize($_POST['site_address'] ?? ''),
                    'gps_location' => Database::sanitize($_POST['gps_location'] ?? ''),
                    'google_map_link' => Database::sanitize($_POST['google_map_link'] ?? ''),
                    'start_date' => $_POST['start_date'] ?? null,
                    'end_date' => $_POST['end_date'] ?? null,
                    'budget' => $_POST['budget'] ?? 0,
                    'notes' => Database::sanitize($_POST['notes'] ?? '')
                ];

                if (empty($data['site_name']) || empty($data['site_address'])) {
                    $error = 'Site name and address are required';
                } else {
                    $siteId = $this->siteModel->create($data);
                    $this->activityModel->log($_SESSION['user_id'], 'create', 'sites', $siteId, 'Added site: ' . $data['site_name']);
                    $success = 'Site added successfully';
                }
            }
        }

        $data = [
            'error' => $error,
            'success' => $success,
            'user' => $this->getCurrentUser(),
            'csrf_token' => $this->getCsrfToken()
        ];

        $this->loadView('sites/add', $data);
    }

    public function edit() {
        $this->requireRole(ROLE_ADMIN);

        $siteId = $_GET['id'] ?? null;
        if (!$siteId) {
            $this->redirect('/index.php?page=sites');
        }

        $site = $this->siteModel->getById($siteId);
        if (!$site) {
            $this->redirect('/index.php?page=sites');
        }

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $error = 'Invalid request';
            } else {
                $data = [
                    'site_name' => Database::sanitize($_POST['site_name'] ?? ''),
                    'site_address' => Database::sanitize($_POST['site_address'] ?? ''),
                    'gps_location' => Database::sanitize($_POST['gps_location'] ?? ''),
                    'google_map_link' => Database::sanitize($_POST['google_map_link'] ?? ''),
                    'site_status' => $_POST['site_status'] ?? 'active',
                    'budget' => $_POST['budget'] ?? 0,
                    'notes' => Database::sanitize($_POST['notes'] ?? '')
                ];

                if (empty($data['site_name']) || empty($data['site_address'])) {
                    $error = 'Site name and address are required';
                } else {
                    $this->siteModel->update($siteId, $data);
                    $this->activityModel->log($_SESSION['user_id'], 'update', 'sites', $siteId, 'Updated site');
                    $success = 'Site updated successfully';
                    $site = $this->siteModel->getById($siteId);
                }
            }
        }

        $data = [
            'site' => $site,
            'error' => $error,
            'success' => $success,
            'user' => $this->getCurrentUser(),
            'csrf_token' => $this->getCsrfToken()
        ];

        $this->loadView('sites/edit', $data);
    }

    public function view() {
        $siteId = $_GET['id'] ?? null;
        if (!$siteId) {
            $this->redirect('/index.php?page=sites');
        }

        $site = $this->siteModel->getById($siteId);
        if (!$site) {
            $this->redirect('/index.php?page=sites');
        }

        $data = [
            'site' => $site,
            'user' => $this->getCurrentUser(),
            'csrf_token' => $this->getCsrfToken()
        ];

        $this->loadView('sites/view', $data);
    }

    public function delete() {
        $this->requireRole(ROLE_ADMIN);

        $siteId = $_GET['id'] ?? null;
        if (!$siteId) {
            $this->json(['success' => false, 'message' => 'Invalid site ID'], 400);
        }

        $this->siteModel->delete($siteId);
        $this->activityModel->log($_SESSION['user_id'], 'delete', 'sites', $siteId, 'Deleted site');

        $this->json(['success' => true, 'message' => 'Site deleted successfully']);
    }

    public function gallery() {
        $siteId = $_GET['id'] ?? null;
        if (!$siteId) {
            $this->redirect('/index.php?page=sites');
        }

        $site = $this->siteModel->getById($siteId);
        if (!$site) {
            $this->redirect('/index.php?page=sites');
        }

        $data = [
            'site' => $site,
            'user' => $this->getCurrentUser(),
            'csrf_token' => $this->getCsrfToken()
        ];

        $this->loadView('sites/gallery', $data);
    }
}

?>
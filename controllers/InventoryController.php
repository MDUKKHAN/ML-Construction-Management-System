<?php
/**
 * Inventory Controller
 */

class InventoryController extends Controller {
    private $inventoryModel;
    private $siteModel;
    private $activityModel;

    public function __construct() {
        parent::__construct();
        $this->requireLogin();
        $this->inventoryModel = new Inventory();
        $this->siteModel = new Site();
        $this->activityModel = new Activity();
    }

    public function index() {
        $page = isset($_GET['pg']) ? (int)$_GET['pg'] : 1;
        $limit = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;

        $search = isset($_GET['search']) ? Database::sanitize($_GET['search']) : '';
        $siteId = isset($_GET['site']) ? (int)$_GET['site'] : null;

        if (!empty($search)) {
            $items = $this->inventoryModel->search($search, $limit, $offset);
        } else if ($siteId) {
            $items = $this->inventoryModel->getBySite($siteId, $limit, $offset);
        } else {
            $items = $this->inventoryModel->getAll($limit, $offset);
        }

        $sites = $this->siteModel->getAll();

        $data = [
            'items' => $items,
            'sites' => $sites,
            'page' => $page,
            'search' => $search,
            'siteId' => $siteId,
            'user' => $this->getCurrentUser(),
            'csrf_token' => $this->getCsrfToken()
        ];

        $this->loadView('inventory/index', $data);
    }

    public function add() {
        $this->requireRole(ROLE_MANAGER);

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $error = 'Invalid request';
            } else {
                $data = [
                    'product_name' => Database::sanitize($_POST['product_name'] ?? ''),
                    'quantity' => (int)($_POST['quantity'] ?? 0),
                    'unit_price' => $_POST['unit_price'] ?? 0,
                    'site_id' => $_POST['site_id'] ?? null,
                    'date_received' => $_POST['date_received'] ?? null,
                    'supplier' => Database::sanitize($_POST['supplier'] ?? ''),
                    'remarks' => Database::sanitize($_POST['remarks'] ?? '')
                ];

                if (empty($data['product_name']) || $data['quantity'] <= 0) {
                    $error = 'Product name and quantity are required';
                } else {
                    $itemId = $this->inventoryModel->create($data);
                    $this->activityModel->log($_SESSION['user_id'], 'add', 'inventory', $itemId, 'Added inventory item');
                    $success = 'Item added successfully';
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

        $this->loadView('inventory/add', $data);
    }

    public function edit() {
        $this->requireRole(ROLE_MANAGER);

        $itemId = $_GET['id'] ?? null;
        if (!$itemId) {
            $this->redirect('/index.php?page=inventory');
        }

        $item = $this->inventoryModel->getById($itemId);
        if (!$item) {
            $this->redirect('/index.php?page=inventory');
        }

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $error = 'Invalid request';
            } else {
                $data = [
                    'product_name' => Database::sanitize($_POST['product_name'] ?? ''),
                    'quantity' => (int)($_POST['quantity'] ?? 0),
                    'unit_price' => $_POST['unit_price'] ?? 0,
                    'site_id' => $_POST['site_id'] ?? null,
                    'supplier' => Database::sanitize($_POST['supplier'] ?? ''),
                    'remarks' => Database::sanitize($_POST['remarks'] ?? '')
                ];

                if (empty($data['product_name']) || $data['quantity'] < 0) {
                    $error = 'Product name and quantity are required';
                } else {
                    $this->inventoryModel->update($itemId, $data);
                    $this->activityModel->log($_SESSION['user_id'], 'update', 'inventory', $itemId, 'Updated inventory item');
                    $success = 'Item updated successfully';
                    $item = $this->inventoryModel->getById($itemId);
                }
            }
        }

        $sites = $this->siteModel->getAll();
        $data = [
            'item' => $item,
            'sites' => $sites,
            'error' => $error,
            'success' => $success,
            'user' => $this->getCurrentUser(),
            'csrf_token' => $this->getCsrfToken()
        ];

        $this->loadView('inventory/edit', $data);
    }

    public function delete() {
        $this->requireRole(ROLE_MANAGER);

        $itemId = $_GET['id'] ?? null;
        if (!$itemId) {
            $this->json(['success' => false, 'message' => 'Invalid item ID'], 400);
        }

        $this->inventoryModel->delete($itemId);
        $this->activityModel->log($_SESSION['user_id'], 'delete', 'inventory', $itemId, 'Deleted inventory item');

        $this->json(['success' => true, 'message' => 'Item deleted successfully']);
    }
}

?>
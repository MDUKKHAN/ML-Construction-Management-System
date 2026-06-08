<?php
/**
 * File Controller
 */

class FileController extends Controller {
    private $activityModel;

    public function __construct() {
        parent::__construct();
        $this->requireLogin();
        $this->activityModel = new Activity();
    }

    public function index() {
        $siteId = $_GET['site'] ?? null;

        $data = [
            'siteId' => $siteId,
            'user' => $this->getCurrentUser(),
            'csrf_token' => $this->getCsrfToken()
        ];

        $this->loadView('files/index', $data);
    }

    public function upload() {
        $this->requireRole(ROLE_MANAGER);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $this->json(['success' => false, 'message' => 'Invalid request'], 400);
            }

            $siteId = $_POST['site_id'] ?? null;
            $description = Database::sanitize($_POST['description'] ?? '');

            if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                $this->json(['success' => false, 'message' => 'File upload failed'], 400);
            }

            $uploadResult = Helper::uploadFile($_FILES['file']);

            if ($uploadResult['success']) {
                $this->activityModel->log($_SESSION['user_id'], 'upload', 'files', $siteId, 'Uploaded file: ' . $_FILES['file']['name']);
                $this->json(['success' => true, 'message' => 'File uploaded successfully', 'filename' => $uploadResult['filename']]);
            } else {
                $this->json(['success' => false, 'message' => $uploadResult['message']], 400);
            }
        }
    }

    public function download() {
        $fileName = $_GET['file'] ?? null;

        if (!$fileName) {
            $this->json(['success' => false, 'message' => 'File not found'], 404);
        }

        $filePath = UPLOAD_DIR . $fileName;

        if (!file_exists($filePath)) {
            $this->json(['success' => false, 'message' => 'File not found'], 404);
        }

        $this->activityModel->log($_SESSION['user_id'], 'download', 'files', null, 'Downloaded file: ' . $fileName);

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Content-Length: ' . filesize($filePath));

        readfile($filePath);
        exit;
    }

    public function delete() {
        $this->requireRole(ROLE_MANAGER);

        $fileName = $_GET['file'] ?? null;

        if (!$fileName) {
            $this->json(['success' => false, 'message' => 'File not found'], 404);
        }

        $filePath = UPLOAD_DIR . $fileName;

        if (file_exists($filePath) && Helper::deleteFile($filePath)) {
            $this->activityModel->log($_SESSION['user_id'], 'delete', 'files', null, 'Deleted file: ' . $fileName);
            $this->json(['success' => true, 'message' => 'File deleted successfully']);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to delete file'], 400);
        }
    }
}

?>
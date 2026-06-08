<?php
/**
 * Account Controller
 */

class AccountController extends Controller {
    private $userModel;
    private $activityModel;

    public function __construct() {
        parent::__construct();
        $this->requireLogin();
        $this->userModel = new User();
        $this->activityModel = new Activity();
    }

    public function profile() {
        $user = $this->userModel->getById($_SESSION['user_id']);

        $data = [
            'user' => $user,
            'csrf_token' => $this->getCsrfToken()
        ];

        $this->loadView('account/profile', $data);
    }

    public function edit() {
        $error = '';
        $success = '';

        $user = $this->userModel->getById($_SESSION['user_id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $error = 'Invalid request';
            } else {
                $data = [
                    'phone' => Database::sanitize($_POST['phone'] ?? ''),
                    'address' => Database::sanitize($_POST['address'] ?? '')
                ];

                if ($this->userModel->update($_SESSION['user_id'], $data)) {
                    $this->activityModel->log($_SESSION['user_id'], 'update', 'account', $_SESSION['user_id'], 'Updated profile');
                    $success = 'Profile updated successfully';
                    $user = $this->userModel->getById($_SESSION['user_id']);
                } else {
                    $error = 'Failed to update profile';
                }
            }
        }

        $data = [
            'user' => $user,
            'error' => $error,
            'success' => $success,
            'csrf_token' => $this->getCsrfToken()
        ];

        $this->loadView('account/edit', $data);
    }

    public function settings() {
        $error = '';
        $success = '';

        $user = $this->userModel->getById($_SESSION['user_id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $error = 'Invalid request';
            } else if (isset($_POST['action']) && $_POST['action'] === 'change_password') {
                $currentPassword = $_POST['current_password'] ?? '';
                $newPassword = $_POST['new_password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';

                if (!Database::verifyPassword($currentPassword, $user['password'])) {
                    $error = 'Current password is incorrect';
                } else if ($newPassword !== $confirmPassword) {
                    $error = 'New passwords do not match';
                } else if (strlen($newPassword) < 6) {
                    $error = 'Password must be at least 6 characters';
                } else {
                    $this->userModel->updatePassword($_SESSION['user_id'], $newPassword);
                    $this->activityModel->log($_SESSION['user_id'], 'change_password', 'account', $_SESSION['user_id'], 'Changed password');
                    $success = 'Password changed successfully';
                    $user = $this->userModel->getById($_SESSION['user_id']);
                }
            }
        }

        $data = [
            'user' => $user,
            'error' => $error,
            'success' => $success,
            'csrf_token' => $this->getCsrfToken()
        ];

        $this->loadView('account/settings', $data);
    }
}

?>
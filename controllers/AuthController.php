<?php
/**
 * Authentication Controller
 */

class AuthController extends Controller {
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
    }

    public function login() {
        if ($this->isLoggedIn()) {
            $this->redirect('/index.php?page=dashboard');
        }

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = Database::sanitize($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $error = 'Email and password are required';
            } else if (!Database::validateEmail($email)) {
                $error = 'Invalid email format';
            } else if ($this->userModel->verifyPassword($email, $password)) {
                $user = $this->userModel->getByEmail($email);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];

                $this->userModel->updateLastLogin($user['id']);
                $this->redirect('/index.php?page=dashboard');
            } else {
                $error = 'Invalid email or password';
            }
        }

        $this->loadView('auth/login', ['error' => $error, 'success' => $success]);
    }

    public function logout() {
        $this->requireLogin();
        session_destroy();
        $this->redirect('/index.php?page=login');
    }

    public function register() {
        if ($this->isLoggedIn()) {
            $this->redirect('/index.php?page=dashboard');
        }

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = Database::sanitize($_POST['username'] ?? '');
            $email = Database::sanitize($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (empty($username) || empty($email) || empty($password)) {
                $error = 'All fields are required';
            } else if ($this->userModel->usernameExists($username)) {
                $error = 'Username already exists';
            } else if ($this->userModel->emailExists($email)) {
                $error = 'Email already exists';
            } else if ($password !== $confirmPassword) {
                $error = 'Passwords do not match';
            } else if (strlen($password) < 6) {
                $error = 'Password must be at least 6 characters';
            } else {
                $this->userModel->create([
                    'username' => $username,
                    'email' => $email,
                    'password' => $password,
                    'role' => 'employee'
                ]);
                $success = 'Registration successful. Please login';
            }
        }

        $this->loadView('auth/register', ['error' => $error, 'success' => $success]);
    }

    public function forgotPassword() {
        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = Database::sanitize($_POST['email'] ?? '');

            if (empty($email)) {
                $error = 'Email is required';
            } else if (!Database::validateEmail($email)) {
                $error = 'Invalid email format';
            } else if (!$this->userModel->emailExists($email)) {
                $error = 'Email not found';
            } else {
                // In production, send reset link via email
                $success = 'Password reset instructions sent to your email';
            }
        }

        $this->loadView('auth/forgot-password', ['error' => $error, 'success' => $success]);
    }

    public function changePassword() {
        $this->requireLogin();

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            $user = $this->userModel->getById($_SESSION['user_id']);

            if (!Database::verifyPassword($currentPassword, $user['password'])) {
                $error = 'Current password is incorrect';
            } else if ($newPassword !== $confirmPassword) {
                $error = 'New passwords do not match';
            } else if (strlen($newPassword) < 6) {
                $error = 'Password must be at least 6 characters';
            } else {
                $this->userModel->updatePassword($_SESSION['user_id'], $newPassword);
                $success = 'Password changed successfully';
            }
        }

        $this->loadView('auth/change-password', ['error' => $error, 'success' => $success]);
    }
}

?>
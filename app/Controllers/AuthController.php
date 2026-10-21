<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\UserModel;
use RuntimeException;

class AuthController extends Controller
{
    public function login(): void
    {
        if (Auth::check()) {
            $this->redirect('index.php');
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim((string)($_POST['username'] ?? ''));
            $password = (string)($_POST['password'] ?? '');

            $user = UserModel::authenticate($username, $password);

            if ($user) {
                session_regenerate_id(true);
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['department'] = $user['department'];

                $this->redirect('index.php');
            }

            $error = 'Invalid username or password.';
        }

        $this->render('auth/login', [
            'error' => $error,
            'username' => $_POST['username'] ?? '',
        ]);
    }

    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
        $this->redirect('login.php');
    }

    public function changePassword(): void
    {
        Auth::requireLogin();

        if (Auth::isAdmin()) {
            http_response_code(403);
            exit('Password changes are available to regular users only.');
        }

        $error = '';
        $success = '';
        $csrfToken = $_SESSION['change_password_csrf'] ?? '';
        if (!is_string($csrfToken) || $csrfToken === '') {
            $csrfToken = bin2hex(random_bytes(32));
            $_SESSION['change_password_csrf'] = $csrfToken;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $submittedToken = is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : '';
            $currentPassword = is_string($_POST['current_password'] ?? null) ? $_POST['current_password'] : '';
            $newPassword = is_string($_POST['new_password'] ?? null) ? $_POST['new_password'] : '';
            $confirmPassword = is_string($_POST['confirm_password'] ?? null) ? $_POST['confirm_password'] : '';

            $username = Auth::username();

            if (!hash_equals($csrfToken, $submittedToken)) {
                http_response_code(400);
                $error = 'Your session expired. Reload the page and try again.';
            } elseif (!UserModel::verifyPassword($username, $currentPassword)) {
                $error = 'Your current password is incorrect.';
            } elseif (strlen($newPassword) < 8) {
                $error = 'Your new password must be at least 8 characters.';
            } elseif ($newPassword !== $confirmPassword) {
                $error = 'The new passwords do not match.';
            } elseif ($newPassword === $currentPassword) {
                $error = 'Your new password must be different from your current password.';
            } else {
                UserModel::changePassword($username, $newPassword);
                session_regenerate_id(true);
                $csrfToken = bin2hex(random_bytes(32));
                $_SESSION['change_password_csrf'] = $csrfToken;
                $success = 'Your password has been changed.';
            }
        }

        $this->render('auth/change_password', [
            'error' => $error,
            'success' => $success,
            'csrfToken' => $csrfToken,
            'username' => Auth::username(),
            'isAdmin' => Auth::isAdmin(),
        ]);
    }
}

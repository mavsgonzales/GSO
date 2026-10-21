<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Config\Constants;
use App\Core\Auth;
use App\Core\Controller;
use App\Models\UserModel;
use PDOException;

class UserController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();
        Auth::requireAdmin();

        $error = '';
        $success = '';
        $usernameInput = '';
        $departmentInput = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usernameInput = trim((string)($_POST['username'] ?? ''));
            $password = (string)($_POST['password'] ?? '');
            $departmentInput = trim((string)($_POST['department'] ?? ''));

            if (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $usernameInput)) {
                $error = 'Username must be 3-50 characters and use only letters, numbers, and underscores.';
            } elseif (strlen($password) < 8) {
                $error = 'Temporary password must be at least 8 characters.';
            } elseif (!in_array($departmentInput, Constants::DEPARTMENT_CHOICES, true)) {
                $error = 'Select a valid department.';
            } else {
                try {
                    UserModel::create($usernameInput, $password, $departmentInput);
                    $success = 'User account created successfully.';
                    $usernameInput = '';
                    $departmentInput = '';
                } catch (PDOException $e) {
                    if ($e->getCode() === '23000' ||
                        str_contains(strtolower($e->getMessage()), 'unique') ||
                        str_contains(strtolower($e->getMessage()), 'duplicate')) {
                        $error = 'That username is already registered.';
                    } else {
                        error_log($e->getMessage());
                        $error = 'Unable to create the user account. Please try again.';
                    }
                }
            }
        }

        $users = UserModel::getAll();

        $this->render('users/index', [
            'users'             => $users,
            'departmentChoices' => Constants::DEPARTMENT_CHOICES,
            'error'             => $error,
            'success'           => $success,
            'usernameInput'     => $usernameInput,
            'departmentInput'   => $departmentInput,
            'isAdmin'           => Auth::isAdmin(),
        ]);
    }
}

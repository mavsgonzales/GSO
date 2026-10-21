<?php
declare(strict_types=1);

/**
 * Backward-compatibility wrapper for Auth helper functions.
 * Delegates to App\Core\Auth and App\Config\Constants.
 */

require_once __DIR__ . '/app/bootstrap.php';

use App\Config\Constants;
use App\Config\Database;
use App\Core\Auth;

$pdo = Database::getConnection();

function departmentChoices(): array
{
    return Constants::DEPARTMENT_CHOICES;
}

function requireLogin(): void
{
    Auth::requireLogin();
}

function isAdminUser(): bool
{
    return Auth::isAdmin();
}

function requireAdmin(): void
{
    Auth::requireAdmin();
}
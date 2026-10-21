<?php
declare(strict_types=1);

/**
 * Change Password Entry Point -> AuthController@changePassword
 */

require_once __DIR__ . '/app/bootstrap.php';

use App\Controllers\AuthController;

$controller = new AuthController();
$controller->changePassword();

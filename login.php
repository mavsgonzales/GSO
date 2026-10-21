<?php
declare(strict_types=1);

/**
 * Authentication Entry Point -> AuthController@login
 */

require_once __DIR__ . '/app/bootstrap.php';

use App\Controllers\AuthController;

$controller = new AuthController();
$controller->login();
<?php
declare(strict_types=1);

/**
 * User Management Entry Point -> UserController@index
 */

require_once __DIR__ . '/app/bootstrap.php';

use App\Controllers\UserController;

$controller = new UserController();
$controller->index();

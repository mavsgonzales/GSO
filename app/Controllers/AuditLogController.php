<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\AuditLogModel;

class AuditLogController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();
        Auth::requireAdmin();

        $logs = AuditLogModel::getRecent(500);

        $this->render('audit_logs/index', [
            'logs' => $logs,
            'isAdmin' => Auth::isAdmin(),
        ]);
    }
}

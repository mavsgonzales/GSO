<?php
use App\Core\Auth;
$currentPage = basename($_SERVER['SCRIPT_NAME'] ?? '');
$isAdmin = Auth::isAdmin();
$isDisposedPage = $currentPage === 'index.php' && (string)($_GET['view'] ?? '') === 'disposed';
?>
<aside class="sidebar" aria-label="Main navigation">
    <div class="sidebar-brand">
        <span class="sidebar-brand-mark" aria-hidden="true">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M4 7V4h16v3M4 17v3h16v-3M9 12h6"/>
            </svg>
        </span>
        <span>GSO Inventory</span>
    </div>
    <div class="sidebar-caption">Navigation</div>
    <nav class="sidebar-nav">
        <a class="sidebar-link<?= $currentPage === 'index.php' && !$isDisposedPage ? ' active' : '' ?>"
           href="index.php"<?= $currentPage === 'index.php' && !$isDisposedPage ? ' aria-current="page"' : '' ?>>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <rect x="3" y="3" width="8" height="8" rx="1"/>
                <rect x="13" y="3" width="8" height="5" rx="1"/>
                <rect x="13" y="10" width="8" height="11" rx="1"/>
                <rect x="3" y="13" width="8" height="8" rx="1"/>
            </svg>
            Dashboard
        </a>
        <a class="sidebar-link<?= $isDisposedPage ? ' active' : '' ?>"
           href="index.php?view=disposed"<?= $isDisposedPage ? ' aria-current="page"' : '' ?>>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M3 6h18"/>
                <path d="M8 6V4h8v2m3 0-1 14H6L5 6"/>
                <path d="M10 11v5m4-5v5"/>
            </svg>
            Disposed Items
        </a>
        <a class="sidebar-link<?= $currentPage === 'inventory_check.php' ? ' active' : '' ?>"
           href="inventory_check.php"<?= $currentPage === 'inventory_check.php' ? ' aria-current="page"' : '' ?>>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M4 4h5v5H4zM15 4h5v5h-5zM4 15h5v5H4z"/>
                <path d="M15 15h2v2h-2zm3 3h2v2h-2zm0-4v1m-3 3h1m-2-4h1"/>
            </svg>
            Inventory Check
        </a>
        <a class="sidebar-link<?= $currentPage === 'report.php' ? ' active' : '' ?>"
           href="report.php"<?= $currentPage === 'report.php' ? ' aria-current="page"' : '' ?>>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                <path d="M8 7h8M8 11h8"/>
            </svg>
            Generate Report
        </a>
        <?php if ($isAdmin): ?>
        <a class="sidebar-link<?= $currentPage === 'users.php' ? ' active' : '' ?>"
           href="users.php"<?= $currentPage === 'users.php' ? ' aria-current="page"' : '' ?>>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="10" cy="7" r="4"/>
                <path d="M20 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            Users
        </a>
        <a class="sidebar-link<?= $currentPage === 'audit_logs.php' ? ' active' : '' ?>"
           href="audit_logs.php"<?= $currentPage === 'audit_logs.php' ? ' aria-current="page"' : '' ?>>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M3 3v5h5"/>
                <path d="M3.05 13A9 9 0 1 0 5 6.3L3 8"/>
                <path d="M12 7v5l3 2"/>
            </svg>
            Audit Logs
        </a>
        <?php endif; ?>
    </nav>
    <div class="sidebar-footer">
        <a class="sidebar-link sidebar-logout" href="logout.php">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M10 17l5-5-5-5"/>
                <path d="M15 12H3"/>
                <path d="M12 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6"/>
            </svg>
            Log Out
        </a>
    </div>
</aside>

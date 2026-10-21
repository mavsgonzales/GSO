<?php
use App\Models\AuditLogModel;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Audit Logs | GSO Inventory</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="sidebar.css">
    <style>
        * { box-sizing: border-box; }
        body {
            display: flex;
            min-height: 100vh;
            margin: 0;
            background: #fff;
            color: #111827;
            font-family: 'Inter', system-ui, sans-serif;
        }
        .app-content { display: flex; flex: 1; flex-direction: column; min-width: 0; }
        header {
            position: sticky;
            top: 0;
            z-index: 5;
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.75rem;
            background: #fff;
            border-bottom: 1px solid #d1d5db;
        }
        header h1 { margin: 0; font-size: 1.15rem; }
        main { flex: 1; width: 100%; padding: 1.5rem 1.75rem; }
        .logs-card {
            overflow: hidden;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
        }
        .logs-card-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #d1d5db;
            background: #f9fafb;
        }
        .logs-card-head h2 { margin: 0; font-size: 1rem; }
        .logs-count { color: #4b5563; font-size: 0.82rem; }
        .table-scroll { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th, td { padding: 0.8rem 1rem; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        th {
            background: #f3f4f6;
            color: #111827;
            font-size: 0.72rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            white-space: nowrap;
        }
        td { color: #1f2937; font-size: 0.85rem; }
        tbody tr:last-child td { border-bottom: 0; }
        .action-badge {
            display: inline-block;
            padding: 0.2rem 0.55rem;
            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: capitalize;
        }
        .action-delete { background: #fef2f2; color: #b91c1c; }
        .details { min-width: 240px; max-width: 480px; line-height: 1.5; overflow-wrap: anywhere; }
        .empty-state { padding: 2rem; color: #4b5563; text-align: center; }
        @media (max-width: 760px) {
            body { flex-direction: column; }
            header { padding: 1rem; }
            main { padding: 1rem; }
        }
    </style>
</head>
<body>
    <?php require __DIR__ . '/../partials/sidebar.php'; ?>
    <div class="app-content">
        <header>
            <h1>Audit Logs</h1>
        </header>
        <main>
            <section class="logs-card" aria-labelledby="logs-heading">
                <div class="logs-card-head">
                    <h2 id="logs-heading">Inventory Activity</h2>
                    <span class="logs-count">Showing latest <?= count($logs) ?> activities (maximum 500)</span>
                </div>
                <?php if ($logs): ?>
                    <div class="table-scroll">
                        <table>
                            <thead>
                                <tr>
                                    <th scope="col">Date &amp; Time</th>
                                    <th scope="col">User</th>
                                    <th scope="col">Action</th>
                                    <th scope="col">Inventory Record</th>
                                    <th scope="col">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                    <?php
                                        $details = json_decode($log['details'], true, 512, JSON_THROW_ON_ERROR);
                                        $record = $details['after'] ?? $details['record'] ?? [];
                                        $summary = $log['action'] === 'edit'
                                            ? AuditLogModel::formatChangedFields($details['before'] ?? [], $details['after'] ?? [])
                                            : ucfirst($log['action']) . ' inventory record';
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($log['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($log['username'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><span class="action-badge action-<?= htmlspecialchars($log['action'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($log['action'], ENT_QUOTES, 'UTF-8') ?></span></td>
                                        <td><?= htmlspecialchars(AuditLogModel::formatRecordLabel($record), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="details"><?= htmlspecialchars($summary, ENT_QUOTES, 'UTF-8') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="empty-state">No inventory activity has been logged yet.</p>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>

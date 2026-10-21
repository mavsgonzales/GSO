<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GSO Property &amp; Road Inventory Ledger (25 Columns)</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="sidebar.css">
    <style>
        :root {
            --bg-body: #ffffff;
            --bg-card: #ffffff;
            --bg-surface: #ffffff;
            --bg-hover: #f3f4f6;
            --border: #d1d5db;
            --border-hover: #9ca3af;
            --text-main: #111827;
            --text-muted: #374151;
            --text-sub: #4b5563;
            --primary: #3b82f6;
            --primary-hover: #2563eb;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --mono: 'JetBrains Mono', monospace;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
        }

        .app-content {
            display: flex;
            flex: 1;
            flex-direction: column;
            min-width: 0;
        }

        /* Top Header */
        header {
            background: #ffffff;
            border-bottom: 1px solid var(--border);
            padding: 0.85rem 1.75rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .header-title {
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }

        .header-badge-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #2563eb, #7c3aed);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 0.65rem;
        }

        /* Main Container */
        main {
            flex: 1;
            width: 100%;
            padding: 1.5rem 1.75rem;
        }

        @media (max-width: 760px) {
            body { flex-direction: column; }
            header { flex-wrap: wrap; gap: 0.75rem; }
            .header-actions { flex-wrap: wrap; }
            main { padding: 1rem; }
        }

        .toast {
            padding: 0.85rem 1.25rem;
            border-radius: 8px;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.88rem;
        }
        .toast-success { background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: #047857; }
        .toast-danger { background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #b91c1c; }
        .toast-info { background: rgba(59, 130, 246, 0.15); border: 1px solid rgba(59, 130, 246, 0.3); color: #1d4ed8; }

        /* KPI Banner */
        .kpi-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        .kpi-box {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 1rem 1.25rem;
            box-shadow: 0 2px 6px rgba(0,0,0,0.03);
        }

        .kpi-label {
            font-size: 0.75rem;
            color: var(--text-sub);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
        }

        .kpi-value {
            font-size: 1.45rem;
            font-weight: 700;
            color: var(--text-main);
            margin-top: 0.25rem;
        }

        /* Filter & Controls Toolbar */
        .toolbar {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 0.85rem 1.25rem;
            margin-bottom: 1.25rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            align-items: center;
            justify-content: space-between;
        }

        .toolbar-left {
            display: flex;
            flex-wrap: wrap;
            gap: 0.65rem;
            align-items: center;
            flex: 1;
        }

        .toolbar-search {
            min-width: 280px;
            max-width: 450px;
            flex: 1;
            padding: 0.55rem 0.85rem;
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: 6px;
            color: var(--text-main);
            font-size: 0.85rem;
            outline: none;
        }

        .search-suggestions-wrap {
            position: relative;
            display: flex;
            flex: 1;
            min-width: 280px;
            max-width: 450px;
        }

        .search-suggestions-wrap .toolbar-search {
            width: 100%;
            min-width: 0;
            max-width: none;
        }

        .search-suggestions {
            position: absolute;
            top: calc(100% + 6px);
            right: 0;
            left: 0;
            z-index: 60;
            display: none;
            max-height: 280px;
            overflow-y: auto;
            padding: 0.35rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 12px 28px rgba(17, 24, 39, 0.15);
        }

        .search-suggestions.is-open { display: block; }

        .search-suggestion {
            display: block;
            width: 100%;
            padding: 0.6rem 0.7rem;
            border: 0;
            border-radius: 5px;
            background: transparent;
            color: var(--text-main);
            font: inherit;
            text-align: left;
            cursor: pointer;
        }

        .search-suggestion:hover,
        .search-suggestion[aria-selected="true"] {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .search-suggestions-message {
            padding: 0.6rem 0.7rem;
            color: var(--text-muted);
            font-size: 0.8rem;
        }

        .toolbar-select {
            padding: 0.55rem 0.85rem;
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: 6px;
            color: var(--text-main);
            font-size: 0.85rem;
            outline: none;
            cursor: pointer;
        }

        .search-field-select {
            min-width: 185px;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.55rem 1rem;
            font-size: 0.83rem;
            font-weight: 600;
            border-radius: 6px;
            border: 1px solid transparent;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
        }

        .btn-primary { background: #2563eb; color: #fff; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-secondary { background: var(--bg-surface); color: var(--text-main); border-color: var(--border); }
        .btn-secondary:hover { background: var(--bg-hover); }
        .btn-danger { background: rgba(239, 68, 68, 0.15); color: #f87171; border-color: rgba(239, 68, 68, 0.3); }
        .btn-danger:hover { background: #ef4444; color: #fff; }

        /* Full 25-Column Table Wrapper */
        .table-container {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        }

        .table-info-bar {
            padding: 0.75rem 1.25rem;
            background: #f9fafb;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.82rem;
            color: var(--text-muted);
        }

        .table-scroll {
            width: 100%;
            overflow-x: auto;
            position: relative;
            max-height: 68vh;
            scrollbar-gutter: stable;
        }

        /* Keep every field visible in the ledger, with horizontal scrolling on smaller screens. */
        table.full-gso-table {
            width: max-content;
            min-width: 100%;
            border-collapse: collapse;
            font-size: 0.8rem;
            text-align: left;
            white-space: nowrap;
        }

        table.full-gso-table th {
            background: #f3f4f6;
            color: #111827;
            font-weight: 600;
            font-size: 0.72rem;
            line-height: 1.35;
            padding: 0.7rem 0.8rem;
            border-bottom: 1px solid var(--border);
            border-right: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 2;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            white-space: normal;
            min-width: 110px;
            max-width: 170px;
            vertical-align: bottom;
        }

        .selection-column {
            width: 42px;
            min-width: 42px !important;
            max-width: 42px !important;
            padding: 0.5rem !important;
            text-align: center;
        }

        .row-selection {
            width: 15px;
            height: 15px;
            accent-color: var(--primary);
            cursor: pointer;
            vertical-align: middle;
        }

        table.full-gso-table td {
            padding: 0.65rem 0.8rem;
            border-bottom: 1px solid var(--border);
            border-right: 1px solid #e5e7eb;
            color: var(--text-main);
            vertical-align: middle;
            max-width: 240px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        table.full-gso-table tbody tr:nth-child(even) td {
            background-color: #f9fafb;
        }

        table.full-gso-table tbody tr {
            cursor: pointer;
        }

        table.full-gso-table tbody tr:hover td {
            background-color: rgba(59, 130, 246, 0.05);
        }

        .view-record-actions { display: flex; justify-content: flex-end; gap: 0.5rem; }

        .view-modal {
            position: fixed;
            inset: 0;
            z-index: 110;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background: rgba(17, 24, 39, 0.55);
        }

        .view-modal.is-open { display: flex; }

        .view-modal-box {
            display: flex;
            flex-direction: column;
            width: min(900px, 100%);
            max-height: 90vh;
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.2);
        }

        .view-modal-head,
        .view-modal-foot {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem 1.25rem;
            background: #f9fafb;
        }

        .view-modal-head { border-bottom: 1px solid var(--border); }
        .view-modal-head h2 { margin: 0; font-size: 1.05rem; }
        .view-modal-foot { justify-content: flex-end; border-top: 1px solid var(--border); }
        .view-modal-body { overflow-y: auto; padding: 1.25rem; }

        .record-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
            gap: 0.85rem;
        }

        .record-detail {
            min-width: 0;
            padding: 0.8rem;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #fff;
        }

        .record-detail dt {
            margin-bottom: 0.35rem;
            color: var(--text-muted);
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .record-detail dd {
            margin: 0;
            color: var(--text-main);
            font-size: 0.85rem;
            overflow-wrap: anywhere;
            white-space: pre-wrap;
        }

        /* Badge Styles */
        .status-badge {
            display: inline-block;
            padding: 0.2rem 0.55rem;
            border-radius: 9999px;
            font-size: 0.72rem;
            font-weight: 600;
        }
        .status-Serviceable    { background: rgba(16, 185, 129, 0.15); color: #047857; }
        .status-Under-Repair   { background: rgba(245, 158, 11, 0.15); color: #b45309; }
        .status-Surrendered    { background: rgba(139, 92, 246, 0.15); color: #7c3aed; }
        .status-Unserviceable, .status-Disposed { background: rgba(239, 68, 68, 0.15); color: #b91c1c; }

        /* Empty State */
        .blank-state {
            padding: 3.5rem 2rem;
            text-align: center;
            color: var(--text-muted);
        }

        .blank-state h3 {
            color: var(--text-main);
            font-size: 1.15rem;
            margin-bottom: 0.4rem;
        }

        /* Modal Dialog */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; width: 100vw; height: 100vh;
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(6px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 100;
            padding: 1.5rem;
        }

        .modal-box {
            background: #ffffff;
            border: 1px solid var(--border-hover);
            border-radius: 12px;
            width: 100%;
            max-width: 960px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 25px 50px rgba(0,0,0,0.25);
        }

        .modal-head {
            padding: 1.1rem 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            background: #ffffff;
            z-index: 5;
        }

        .modal-head h3 { font-size: 1.15rem; color: var(--text-main); }

        .modal-close-btn {
            background: transparent;
            border: none;
            color: var(--text-muted);
            font-size: 1.5rem;
            cursor: pointer;
        }

        .modal-body { padding: 1.5rem; }

        .modal-foot {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            background: #f9fafb;
            position: sticky;
            bottom: 0;
            z-index: 5;
        }

        .form-section {
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #2563eb;
            margin: 1.25rem 0 0.65rem 0;
            padding-bottom: 0.35rem;
            border-bottom: 1px solid rgba(37, 99, 235, 0.2);
        }
        .form-section:first-of-type { margin-top: 0; }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 0.85rem;
        }

        .form-field {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
            position: relative;
        }
        .form-field.full { grid-column: 1 / -1; }

        .field-suggestions {
            position: absolute;
            top: 100%;
            right: 0;
            left: 0;
            z-index: 120;
            display: none;
            max-height: 220px;
            overflow-y: auto;
            padding: 0.3rem;
            border: 1px solid var(--border);
            border-radius: 7px;
            background: #fff;
            box-shadow: 0 10px 24px rgba(17, 24, 39, 0.16);
        }

        .field-suggestions.is-open { display: block; }

        .field-suggestion {
            display: block;
            width: 100%;
            padding: 0.55rem 0.65rem;
            border: 0;
            border-radius: 5px;
            background: transparent;
            color: var(--text-main);
            font: inherit;
            text-align: left;
            overflow-wrap: anywhere;
            cursor: pointer;
        }

        .field-suggestion:hover,
        .field-suggestion[aria-selected="true"] {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .field-suggestions-message {
            padding: 0.55rem 0.65rem;
            color: var(--text-muted);
            font-size: 0.78rem;
        }

        .form-field label {
            font-size: 0.76rem;
            font-weight: 600;
            color: var(--text-muted);
        }

        .form-input {
            padding: 0.55rem 0.75rem;
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: 6px;
            color: var(--text-main);
            font-size: 0.82rem;
            outline: none;
            width: 100%;
        }
        .form-input:focus { border-color: var(--primary); }

        input::-webkit-calendar-picker-indicator { cursor: pointer; }
    </style>
</head>
<body>

    <?php require __DIR__ . '/../partials/sidebar.php'; ?>
    <div class="app-content">
    <header>
        <div class="header-title">
            <div class="header-badge-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7V4h16v3M4 17v3h16v-3M9 12h6"/></svg>
            </div>
            <div>
                <h1 style="font-size: 1.15rem; font-weight: 700;"><?= $disposedOnly ? 'Disposed Items' : 'GSO Property &amp; Inventory Table' ?></h1>
                <p style="font-size: 0.75rem; color: var(--text-muted);"><?= $disposedOnly ? 'All inventory records with Disposed status' : 'All 25 Specified Property Fields &bull; General Services Office' ?></p>
            </div>
        </div>

        <div class="header-actions">
            <span style="font-size:0.82rem; color:var(--text-muted);">Welcome, <?= htmlspecialchars($_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
            <?php if (!$isAdmin): ?>
                <a href="change_password.php" class="btn btn-secondary">Change Password</a>
                <a href="logout.php" class="btn btn-secondary">Log Out</a>
            <?php endif; ?>
            <?php if ($isAdmin): ?>
            <form method="POST" action="index.php" enctype="multipart/form-data" id="icsExcelImportForm">
                <input type="hidden" name="action" value="import_excel">
                <input type="hidden" name="return_view" value="<?= $disposedOnly ? 'disposed' : '' ?>">
                <input type="file" name="ics_excel" id="icsExcelFile" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required aria-label="Choose Excel inventory workbook" hidden>
                <button type="button" class="btn btn-secondary" id="importIcsExcelButton">Import ICS from Excel</button>
            </form>
            <button type="button" class="btn btn-primary" onclick="openCreateModal()">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                + Add Record Modal
            </button>
            <?php endif; ?>
        </div>
    </header>

    <main>
        <?php if (!empty($message)): ?>
            <div class="toast toast-<?= htmlspecialchars($messageType) ?>">
                <span><?= htmlspecialchars($message) ?></span>
                <button onclick="this.parentElement.remove()" style="background:none;border:none;color:inherit;cursor:pointer;font-size:1.2rem;">&times;</button>
            </div>
        <?php endif; ?>

        <!-- Filter & Search Toolbar -->
        <div class="toolbar">
            <form method="GET" action="index.php" class="toolbar-left" id="searchFilterForm">
                <?php if ($disposedOnly): ?>
                    <input type="hidden" name="view" value="disposed">
                <?php endif; ?>
                <select name="search_field" id="inventorySearchField" class="toolbar-select search-field-select" aria-label="Choose inventory field to search">
                    <option value="all" <?= $searchField === 'all' ? 'selected' : '' ?>>All Fields</option>
                    <?php foreach ($inventoryFields as $field => $label): ?>
                        <option value="<?= htmlspecialchars($field, ENT_QUOTES, 'UTF-8') ?>" <?= $searchField === $field ? 'selected' : '' ?>>
                            <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="search-suggestions-wrap">
                    <input
                        type="text"
                        name="search"
                        id="inventorySearch"
                        class="toolbar-search"
                        placeholder="Type a value or any text to search..."
                        value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
                        autocomplete="off"
                        role="combobox"
                        aria-autocomplete="list"
                        aria-expanded="false"
                        aria-controls="inventorySearchSuggestions"
                    >
                    <div class="search-suggestions" id="inventorySearchSuggestions" role="listbox"></div>
                </div>

                <?php if (!$disposedOnly): ?>
                    <select name="status" class="toolbar-select" onchange="document.getElementById('searchFilterForm').submit()">
                        <option value="">Status: All Choices</option>
                        <?php foreach ($statusChoices as $choice): ?>
                            <?php if ($choice === 'Disposed') { continue; } ?>
                            <option value="<?= htmlspecialchars($choice) ?>" <?= $statusFilter === $choice ? 'selected' : '' ?>>
                                <?= htmlspecialchars($choice) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>

                <button type="submit" class="btn btn-secondary">Filter</button>
                <?php if ($isAdmin): ?>
                    <button type="button" class="btn btn-secondary" id="printSelectedIcs">Print Selected ICS</button>
                    <button type="button" class="btn btn-secondary" id="printSelectedQrCodes">Print Selected QR Codes</button>
                <?php endif; ?>
                <?php if ($search !== '' || $searchField !== 'all' || $statusFilter !== ''): ?>
                    <a href="index.php<?= $disposedOnly ? '?view=disposed' : '' ?>" class="btn btn-secondary">Reset</a>
                <?php endif; ?>
            </form>

            <div style="font-size: 0.8rem; color: var(--text-muted);">
                Showing <strong>All 25 Fields</strong> as distinct columns
            </div>
        </div>

        <!-- The 25-Column Full Table -->
        <div class="table-container">
            <div class="table-info-bar">
                <span><strong><?= $disposedOnly ? 'Disposed Inventory:' : 'GSO Master Ledger:' ?></strong> Double-click a row to view its details.</span>
                <span style="font-family: var(--mono); color: var(--text-main);"><?= count($items) ?> Records</span>
            </div>

            <div class="table-scroll">
                <table class="full-gso-table" id="gsoMasterTable">
                    <thead>
                        <tr>
                            <?php if ($isAdmin): ?>
                            <th scope="col" class="selection-column">
                                <input type="checkbox" id="selectAllInventory" class="row-selection" aria-label="Select all visible inventory items">
                            </th>
                            <?php endif; ?>
                            <?php foreach ($inventoryFields as $label): ?>
                                <th scope="col"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($items)): ?>
                            <tr>
                                <?php if ($isAdmin): ?><td class="selection-column"></td><?php endif; ?>
                                <td colspan="<?= count($inventoryFields) ?>">
                                    <div class="blank-state">
                                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="1.5" style="margin-bottom:0.75rem;">
                                            <rect width="18" height="18" x="3" y="3" rx="2"/>
                                            <line x1="9" y1="9" x2="15" y2="15"/>
                                            <line x1="15" y1="9" x2="9" y2="15"/>
                                        </svg>
                                        <h3><?= $disposedOnly ? 'No disposed items found' : 'No inventory records yet' ?></h3>
                                        <p style="font-size:0.85rem; color:var(--text-sub); margin-bottom:1rem;"><?= $disposedOnly ? 'Items marked Disposed will appear here.' : 'Add a property record to see it listed here.' ?></p>
                                        <?php if ($isAdmin && !$disposedOnly): ?>
                                            <button type="button" class="btn btn-primary" onclick="openCreateModal()">+ Add New Property Record</button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($items as $row): ?>
                                <tr data-id="<?= (int)$row['id'] ?>"
                                    data-record="<?= htmlspecialchars(json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8') ?>"
                                    ondblclick="openViewModal(this.dataset.record)"
                                    title="Double-click to view record">
                                    <?php if ($isAdmin): ?>
                                    <td class="selection-column">
                                        <input type="checkbox" class="row-selection inventory-row-selection" aria-label="Select inventory item"
                                               onclick="event.stopPropagation()"
                                               ondblclick="event.stopPropagation()">
                                    </td>
                                    <?php endif; ?>
                                    <?php foreach ($inventoryFields as $field => $label): ?>
                                        <?php
                                            $value = $row[$field] ?? '';
                                            if ($field === 'onhand_quantity') {
                                                $value = number_format((float)$value, 2);
                                            } elseif ($field === 'unit_cost' || $field === 'onhand_total_value') {
                                                $amount = $field === 'onhand_total_value'
                                                    ? (float)$row['onhand_quantity'] * (float)$row['unit_cost']
                                                    : (float)$value;
                                                $value = '₱' . number_format($amount, 2);
                                            }
                                        ?>
                                        <td title="<?= htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') ?>">
                                            <?php if ($field === 'status'): ?>
                                                <?php $sanitizedStatus = str_replace(' ', '-', $row['status'] ?? 'Serviceable'); ?>
                                                <span class="status-badge status-<?= htmlspecialchars($sanitizedStatus, ENT_QUOTES, 'UTF-8') ?>">
                                                    <?= htmlspecialchars((string)($value ?: '—'), ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                            <?php else: ?>
                                                <?= htmlspecialchars((string)($value !== '' ? $value : '—'), ENT_QUOTES, 'UTF-8') ?>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    </div>

    <?php if ($isAdmin): ?>
        <form id="icsPrintForm" action="ics_print.php" method="POST" target="_blank" hidden>
            <div id="icsPrintIds"></div>
        </form>
        <form id="qrPrintForm" action="qr_print.php" method="POST" target="_blank" hidden>
            <div id="qrPrintIds"></div>
        </form>

        <!-- Add / Edit Modal Component (All 25 Fields in Exact Sequence) -->
        <?php include __DIR__ . '/add_modal.php'; ?>
    <?php endif; ?>

    <div class="view-modal" id="viewModal" role="dialog" aria-modal="true" aria-labelledby="viewModalTitle">
        <div class="view-modal-box">
            <div class="view-modal-head">
                <h2 id="viewModalTitle">Inventory Record</h2>
                <button type="button" class="modal-close-btn" onclick="closeViewModal()" aria-label="Close details">&times;</button>
            </div>
            <div class="view-modal-body">
                <dl class="record-details" id="recordDetails"></dl>
            </div>
            <div class="view-modal-foot">
                <div class="view-record-actions">
                    <?php if ($isAdmin): ?>
                    <form method="POST" action="index.php" id="viewDeleteForm" onsubmit="return confirm('Delete this record?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="viewDeleteRecordId" value="">
                        <input type="hidden" name="return_view" value="<?= $disposedOnly ? 'disposed' : '' ?>">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                    <button type="button" class="btn btn-secondary" id="viewEditButton">Edit</button>
                    <?php endif; ?>
                    <button type="button" class="btn btn-secondary" onclick="closeViewModal()">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Export and Modal Scripts -->
    <script>
        const inventoryFieldLabels = <?= json_encode(
            array_map(
                static fn(string $field, string $label): array => [$field, $label],
                array_keys($inventoryFields),
                array_values($inventoryFields)
            ),
            JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP
        ) ?>;

        const isAdmin = <?= $isAdmin ? 'true' : 'false' ?>;

        if (isAdmin) {
            const importIcsExcelButton = document.getElementById('importIcsExcelButton');
            const icsExcelFile = document.getElementById('icsExcelFile');
            const icsExcelImportForm = document.getElementById('icsExcelImportForm');
            importIcsExcelButton.addEventListener('click', function() {
                icsExcelFile.click();
            });
            icsExcelFile.addEventListener('change', function() {
                if (icsExcelFile.files.length > 0) {
                    icsExcelImportForm.requestSubmit();
                }
            });

            const selectAllInventory = document.getElementById('selectAllInventory');
            const inventoryRowSelections = Array.from(document.querySelectorAll('.inventory-row-selection'));

            function updateInventorySelection() {
                const selectedCount = inventoryRowSelections.filter(function(checkbox) {
                    return checkbox.checked;
                }).length;
                selectAllInventory.checked = inventoryRowSelections.length > 0 && selectedCount === inventoryRowSelections.length;
                selectAllInventory.indeterminate = selectedCount > 0 && selectedCount < inventoryRowSelections.length;
            }

            if (selectAllInventory) {
                selectAllInventory.addEventListener('change', function() {
                    inventoryRowSelections.forEach(function(checkbox) {
                        checkbox.checked = selectAllInventory.checked;
                    });
                    updateInventorySelection();
                });
            }

            inventoryRowSelections.forEach(function(checkbox) {
                checkbox.addEventListener('change', updateInventorySelection);
            });

            const printIcsBtn = document.getElementById('printSelectedIcs');
            if (printIcsBtn) {
                printIcsBtn.addEventListener('click', function() {
                    const selectedRows = inventoryRowSelections
                        .filter(function(checkbox) { return checkbox.checked; })
                        .map(function(checkbox) { return checkbox.closest('tr'); });

                    if (!selectedRows.length) {
                        window.alert('Select at least one inventory item to print an ICS.');
                        return;
                    }

                    const idsContainer = document.getElementById('icsPrintIds');
                    idsContainer.replaceChildren(); 
                    selectedRows.forEach(function(row) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'ids[]';
                        input.value = row.dataset.id;
                        idsContainer.appendChild(input);
                    });
                    document.getElementById('icsPrintForm').requestSubmit();
                });
            }

            const printQrBtn = document.getElementById('printSelectedQrCodes');
            if (printQrBtn) {
                printQrBtn.addEventListener('click', function() {
                    const selectedRows = inventoryRowSelections
                        .filter(function(checkbox) { return checkbox.checked; })
                        .map(function(checkbox) { return checkbox.closest('tr'); });

                    if (!selectedRows.length) {
                        window.alert('Select at least one inventory item to generate QR codes.');
                        return;
                    }

                    const idsContainer = document.getElementById('qrPrintIds');
                    idsContainer.replaceChildren();
                    selectedRows.forEach(function(row) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'ids[]';
                        input.value = row.dataset.id;
                        idsContainer.appendChild(input);
                    });
                    document.getElementById('qrPrintForm').requestSubmit();
                });
            }
        }

        function openViewModal(encodedRecord) {
            const record = JSON.parse(encodedRecord);
            const details = document.getElementById('recordDetails');
            details.replaceChildren();
            const recordName = record.property_number || record.article_road_name || 'Inventory Record';
            document.getElementById('viewModalTitle').textContent = 'Inventory Record: ' + recordName;

            inventoryFieldLabels.forEach(function(field) {
                const [key, label] = field;
                let value = record[key] ?? '';
                if (key === 'onhand_quantity') value = Number(value || 0).toFixed(2);
                if (key === 'unit_cost' || key === 'onhand_total_value') {
                    value = '₱' + Number(value || 0).toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }

                const entry = document.createElement('div');
                entry.className = 'record-detail';
                const term = document.createElement('dt');
                term.textContent = label;
                const description = document.createElement('dd');
                description.textContent = value === '' ? '—' : String(value);
                entry.append(term, description);
                details.appendChild(entry);
            });

            if (isAdmin) {
                document.getElementById('viewDeleteRecordId').value = record.id;
                document.getElementById('viewEditButton').onclick = function() {
                    closeViewModal();
                    openEditModal(encodedRecord);
                };
            }
            document.getElementById('viewModal').classList.add('is-open');
            document.getElementById('viewModal').querySelector('button').focus();
        }

        function closeViewModal() {
            document.getElementById('viewModal').classList.remove('is-open');
        }

        const searchInput = document.getElementById('inventorySearch');
        const searchFieldSelect = document.getElementById('inventorySearchField');
        const suggestionsBox = document.getElementById('inventorySearchSuggestions');
        let suggestionTimer;
        let activeSuggestionIndex = -1;
        let suggestionRequest;

        function closeSearchSuggestions() {
            suggestionsBox.replaceChildren();
            suggestionsBox.classList.remove('is-open');
            searchInput.setAttribute('aria-expanded', 'false');
            searchInput.removeAttribute('aria-activedescendant');
            activeSuggestionIndex = -1;
        }

        function showSearchSuggestions(values) {
            suggestionsBox.replaceChildren();
            activeSuggestionIndex = -1;

            if (!values.length) {
                closeSearchSuggestions();
                return;
            }

            values.forEach(function(value, index) {
                const option = document.createElement('button');
                option.type = 'button';
                option.className = 'search-suggestion';
                option.id = 'inventory-search-option-' + index;
                option.setAttribute('role', 'option');
                option.setAttribute('aria-selected', 'false');
                option.textContent = value;
                option.addEventListener('mousedown', function(event) {
                    event.preventDefault();
                    searchInput.value = value;
                    closeSearchSuggestions();
                    document.getElementById('searchFilterForm').requestSubmit();
                });
                suggestionsBox.appendChild(option);
            });

            suggestionsBox.classList.add('is-open');
            searchInput.setAttribute('aria-expanded', 'true');
        }

        searchInput.addEventListener('input', function() {
            window.clearTimeout(suggestionTimer);
            if (suggestionRequest) suggestionRequest.abort();

            const term = searchInput.value.trim();
            if (!term) {
                closeSearchSuggestions();
                return;
            }

            suggestionsBox.replaceChildren();
            const loading = document.createElement('div');
            loading.className = 'search-suggestions-message';
            loading.textContent = 'Finding matches…';
            suggestionsBox.replaceChildren(loading);
            suggestionsBox.classList.add('is-open');
            searchInput.setAttribute('aria-expanded', 'true');

            suggestionTimer = window.setTimeout(function() {
                suggestionRequest = new AbortController();
                const field = searchFieldSelect.value;
                const suggestionUrl = 'search_suggestions.php?q=' + encodeURIComponent(term) +
                    '&field=' + encodeURIComponent(field);
                fetch(suggestionUrl, {
                    headers: { 'Accept': 'application/json' },
                    signal: suggestionRequest.signal
                })
                    .then(function(response) {
                        if (!response.ok) throw new Error('Suggestions request failed.');
                        return response.json();
                    })
                    .then(function(values) {
                        if (searchInput.value.trim() === term && searchFieldSelect.value === field) {
                            showSearchSuggestions(values);
                        }
                    })
                    .catch(function(error) {
                        if (error.name === 'AbortError') return;
                        suggestionsBox.replaceChildren();
                        const message = document.createElement('div');
                        message.className = 'search-suggestions-message';
                        message.textContent = 'Could not load suggestions. Press Enter to search.';
                        suggestionsBox.appendChild(message);
                    });
            }, 250);
        });

        searchFieldSelect.addEventListener('change', function() {
            window.clearTimeout(suggestionTimer);
            if (suggestionRequest) suggestionRequest.abort();
            closeSearchSuggestions();
            if (searchInput.value.trim()) {
                searchInput.dispatchEvent(new Event('input'));
            }
        });

        searchInput.addEventListener('keydown', function(event) {
            const options = suggestionsBox.querySelectorAll('.search-suggestion');
            if (!options.length) {
                if (event.key === 'Escape') closeSearchSuggestions();
                return;
            }

            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                const direction = event.key === 'ArrowDown' ? 1 : -1;
                activeSuggestionIndex = (activeSuggestionIndex + direction + options.length) % options.length;
                options.forEach(function(option, index) {
                    const active = index === activeSuggestionIndex;
                    option.setAttribute('aria-selected', active ? 'true' : 'false');
                    if (active) {
                        searchInput.setAttribute('aria-activedescendant', option.id);
                        option.scrollIntoView({ block: 'nearest' });
                    }
                });
            } else if (event.key === 'Enter' && activeSuggestionIndex >= 0) {
                event.preventDefault();
                options[activeSuggestionIndex].dispatchEvent(new MouseEvent('mousedown', { bubbles: true }));
            } else if (event.key === 'Escape') {
                closeSearchSuggestions();
            }
        });

        document.addEventListener('click', function(event) {
            if (!event.target.closest('.search-suggestions-wrap')) closeSearchSuggestions();
        });

        document.getElementById('viewModal').addEventListener('click', function(event) {
            if (event.target === this) closeViewModal();
        });

        window.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') closeViewModal();
        });
    </script>
</body>
</html>

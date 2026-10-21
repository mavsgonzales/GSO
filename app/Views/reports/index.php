<?php
if (!function_exists('reportEscape')) {
    function reportEscape(string|int|float|null $value): string
    {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('reportValue')) {
    function reportValue(array $item, string $field): string
    {
        $value = $item[$field] ?? '';
        if ($value === null || $value === '') {
            return '';
        }

        if ($field === 'onhand_quantity') {
            return number_format((float)$value, 2);
        }
        if ($field === 'unit_cost' || $field === 'onhand_total_value') {
            $amount = $field === 'onhand_total_value'
                ? (float)($item['onhand_quantity'] ?? 0) * (float)($item['unit_cost'] ?? 0)
                : (float)$value;
            return '₱' . number_format($amount, 2);
        }

        return (string)$value;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Generate Report | GSO Inventory</title>
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
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem 1.75rem;
            background: #fff;
            border-bottom: 1px solid #d1d5db;
        }
        header h1 { margin: 0; font-size: 1.15rem; }
        header span { color: #4b5563; font-size: 0.82rem; }
        main { flex: 1; width: 100%; min-width: 0; padding: 1.5rem 1.75rem; }
        .report-filters, .report-results {
            overflow: hidden;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
        }
        .report-filters { margin-bottom: 1.25rem; padding: 1.25rem; }
        .filter-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(180px, 1fr));
            gap: 1rem;
        }
        .filter-field { display: grid; gap: 0.4rem; }
        .filter-field label { color: #374151; font-size: 0.82rem; font-weight: 600; }
        .filter-field input {
            width: 100%;
            min-height: 40px;
            padding: 0.55rem 0.7rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: #fff;
            color: #111827;
            font: inherit;
            font-size: 0.85rem;
        }
        .filter-actions { display: flex; gap: 0.6rem; margin-top: 1rem; }
        .button {
            display: inline-flex;
            align-items: center;
            min-height: 38px;
            padding: 0.55rem 0.9rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: #fff;
            color: #111827;
            font: inherit;
            font-size: 0.84rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }
        .button-primary { border-color: #2563eb; background: #2563eb; color: #fff; }
        .results-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #d1d5db;
            background: #f9fafb;
        }
        .results-head h2 { margin: 0; font-size: 1rem; }
        .results-count { color: #4b5563; font-size: 0.82rem; white-space: nowrap; }
        .print-report-heading { display: none; }
        .total-summary { padding: 0.85rem 1rem; font-weight: 700; text-align: right; }
        .report-total-row { background: #f3f4f6; font-weight: 700; }
        .report-total-row td { vertical-align: middle; }
        .report-total-quantity, .report-total-amount { white-space: nowrap; }
        .report-total-amount { text-align: right; }
        .report-total-detail {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 4px;
            white-space: nowrap;
        }
        .report-total-detail + .report-total-detail { margin-top: 2px; }
        .report-total-detail-label { color: #4b5563; font-size: 11px; font-weight: 600; }
        .report-total-detail-value { font-size: 14px; font-weight: 700; }
        .table-scroll { overflow: auto; max-height: calc(100vh - 310px); }
        table { width: max-content; min-width: 100%; border-collapse: collapse; text-align: left; }
        th, td {
            max-width: 300px;
            padding: 0.7rem 0.85rem;
            border-right: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
            font-size: 0.8rem;
            overflow-wrap: anywhere;
            vertical-align: top;
        }
        th {
            position: sticky;
            top: 0;
            z-index: 1;
            background: #f3f4f6;
            color: #111827;
            font-size: 0.72rem;
            text-transform: uppercase;
            white-space: nowrap;
        }
        tbody tr:nth-child(even) { background: #f9fafb; }
        .total-row { background: #eee; font-weight: 700; }
        .total-label, .total-value { text-align: right; }
        .total-items { text-align: left; padding-left: 1.25rem; font-weight: 700; }
        .empty-state { padding: 2rem; color: #4b5563; text-align: center; }
        @media (max-width: 760px) {
            body { flex-direction: column; }
            header { flex-wrap: wrap; padding: 1rem; }
            main { padding: 1rem; }
            .filter-grid { grid-template-columns: 1fr; }
            .table-scroll { max-height: none; }
        }
        @page { size: 13in 8.5in; margin: 8mm; }
        @media print {
            body { display: block; }
            .sidebar, header, .report-filters, .results-head { display: none !important; }
            main, .app-content { width: 100%; min-width: 0; padding: 0; }
            .report-results { overflow: visible; border: 0; border-radius: 0; box-shadow: none; }
            .print-report-heading { display: block; margin-bottom: 5mm; }
            .print-report-heading h1 { margin: 0 0 2mm; font-size: 15pt; }
            .table-scroll { max-height: none; overflow: visible; }
            table {
                width: 100%;
                max-width: 100%;
                min-width: 0;
                table-layout: fixed;
                border-collapse: collapse;
                border: 1px solid #444;
                font-size: 14px;
            }
            th, td {
                padding: 4px;
                border: 1px solid #444;
                font-size: 14px;
                white-space: normal;
                overflow-wrap: anywhere;
                word-break: normal;
                vertical-align: top;
            }
            thead { display: table-header-group; }
            tfoot {
                display: table-row-group;
                break-inside: avoid;
                page-break-inside: avoid;
            }
            tr { break-inside: avoid; page-break-inside: avoid; }
            th { position: static; background: #f3f4f6 !important; }
            .total-row { break-inside: avoid; page-break-inside: avoid; }
            .report-total-row { break-inside: avoid; page-break-inside: avoid; }
            .report-total-quantity, .report-total-amount { white-space: nowrap; }
        }
    </style>
</head>
<body>
    <?php require __DIR__ . '/../partials/sidebar.php'; ?>
    <div class="app-content">
        <header>
            <h1>Generate Report</h1>
            <span>Welcome, <?= htmlspecialchars($_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
        </header>
        <main>
            <section class="report-filters" aria-labelledby="filters-heading">
                <form method="GET" action="report.php">
                    <input type="hidden" name="generate" value="1">
                    <div class="filter-grid">
                        <?php foreach ($filters as $field => $label): ?>
                            <div class="filter-field">
                                <label for="filter-<?= $field ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></label>
                                <input id="filter-<?= $field ?>" name="<?= $field ?>"
                                       list="list-<?= $field ?>"
                                       placeholder="Type or select <?= strtolower($label) ?>"
                                       value="<?= reportEscape($selectedFilters[$field]) ?>">
                                <datalist id="list-<?= $field ?>">
                                    <?php foreach ($options[$field] as $option): ?>
                                        <option value="<?= reportEscape($option) ?>">
                                    <?php endforeach; ?>
                                </datalist>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="filter-actions">
                        <button class="button button-primary" type="submit">Filter Report</button>
                        <a class="button" href="report.php">Reset Filters</a>
                    </div>
                </form>
            </section>

            <section class="report-results" aria-labelledby="results-heading">
                <div class="print-report-heading">
                    <h1>General Services Office - Property Inventory Report</h1>
                </div>
                <div class="results-head">
                    <h2 id="results-heading">Report Preview</h2>
                    <div>
                        <span class="results-count"><?= count($items) ?> items found</span>
                        <?php if ($reportGenerated && $items): ?>
                            <button class="button button-primary" style="margin-left: 0.6rem;" onclick="window.print()">Print Report</button>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($reportGenerated && $items): ?>
                    <div class="table-scroll">
                        <table>
                            <thead>
                                <tr>
                                    <?php foreach ($fields as $field => $label): ?>
                                        <th scope="col"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <?php foreach ($fields as $field => $label): ?>
                                            <td><?= reportEscape(reportValue($item, $field)) ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="total-row report-total-row">
                                    <td colspan="2"></td>
                                    <td class="total-items report-total-quantity">
                                        <div class="report-total-detail">
                                            <span class="report-total-detail-label">Items</span>
                                            <span class="report-total-detail-value"><?= count($items) ?></span>
                                        </div>
                                        <div class="report-total-detail">
                                            <span class="report-total-detail-label">Qty</span>
                                            <span class="report-total-detail-value"><?= number_format((float)$totalQuantity, 2) ?></span>
                                        </div>
                                    </td>
                                    <td colspan="2"></td>
                                    <td class="total-value report-total-amount">
                                        Total Amount:<br>₱<?= number_format($totalAmount, 2) ?>
                                    </td>
                                    <td colspan="5"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php elseif ($reportGenerated): ?>
                    <p class="empty-state">No matching inventory records found for the selected criteria.</p>
                <?php else: ?>
                    <p class="empty-state">Use the filters above and click "Filter Report" to generate an inventory summary.</p>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>

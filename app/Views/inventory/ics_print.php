<?php
if (!function_exists('icsEscape')) {
    function icsEscape(string|int|float|null $value): string
    {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('icsDate')) {
    function icsDate(string|null $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $timestamp = strtotime($value);
        return $timestamp === false ? $value : date('n/j/Y', $timestamp);
    }
}

if (!function_exists('icsNumber')) {
    function icsNumber(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2, '.', ','), '0'), '.');
    }
}

if (!function_exists('icsSharedValue')) {
    function icsSharedValue(array $items, string $field): string
    {
        $values = array_values(array_unique(array_map(
            static fn(array $item): string => trim((string)($item[$field] ?? '')),
            $items
        )));

        return count($values) === 1 ? $values[0] : 'Multiple';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inventory Custodian Slip (ICS)</title>
    <style>
        :root {
            --line: #555;
            --text: #333;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 20px;
            background: #eee;
            color: var(--text);
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
        }
        .paper {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto 20px;
            padding: 8mm 9mm 6mm;
            background: #fff;
            box-shadow: 0 2px 10px #0002;
        }
        .letterhead {
            display: grid;
            grid-template-columns: 25mm 1fr 25mm;
            align-items: center;
            min-height: 30mm;
            text-align: center;
        }
        .letterhead-logo {
            display: block;
            width: 22mm;
            height: 22mm;
            margin: auto;
            object-fit: contain;
        }
        .office-heading { font-size: 10px; line-height: 1.6; }
        .republic { font-size: 10px; }
        .province { font-size: 12px; font-weight: normal; }
        .office-name { font-size: 11px; }
        .document-title { margin-top: 2px; font-size: 12px; font-weight: bold; }
        .info-table, .inventory-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin: 0;
        }
        .inventory-table { flex: 1; }
        .info-table td, .inventory-table th, .inventory-table td {
            border: 1px solid var(--line);
        }
        .info-table td {
            height: 9mm;
            padding: 6px;
            vertical-align: middle;
            font-size: 10px;
        }
        .inventory-table th, .inventory-table td {
            padding: 3px 5px;
            overflow-wrap: break-word;
        }
        .inventory-table th {
            height: 15mm;
            text-align: center;
            vertical-align: middle;
            font-size: 9.5px;
            font-weight: bold;
        }
        .inventory-table td { vertical-align: top; line-height: 1.2; }
        .inventory-table .item-row td { height: auto; }
        .inventory-table .item-row td { border-bottom: 0; }
        .inventory-table .item-row + .item-row td { border-top: 0; }
        .inventory-table.single-item .item-row td {
            height: 180mm;
            font-size: 11px;
        }
        .inventory-table thead { display: table-header-group; }
        .inventory-table .total-row td {
            height: 9mm;
            padding: 3px 5px;
            vertical-align: middle;
            background: #e5e7eb;
            font-weight: bold;
        }
        .center { text-align: center; }
        .right { text-align: right; }
        .description { text-align: left; text-transform: uppercase; }
        .meta { margin-top: 0; line-height: 1.2; }
        .meta div { min-height: 0; }
        .total-row td { vertical-align: middle; }
        .total-label, .total-amount { text-align: right; font-weight: bold; }
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            width: 100%;
        }
        .signature {
            min-height: 36mm;
            padding: 5px 7px;
            border: 1px solid var(--line);
        }
        .signature + .signature { border-left: 0; }
        .signature-label { font-size: 10px; }
        .signed-name {
            margin-top: 13mm;
            text-align: center;
            font-size: 10px;
            font-weight: bold;
        }
        .signed-role, .signed-date { margin-top: 3px; text-align: center; }
        .footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            min-height: 5mm;
            margin-top: 3px;
            font-size: 9px;
        }
        .error-card {
            width: min(100%, 540px);
            margin: 40px auto;
            padding: 24px;
            background: #fff;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            box-shadow: 0 4px 12px #0002;
            font-size: 13px;
        }
        .error-card h1 { margin-top: 0; font-size: 18px; color: #b91c1c; }
        .error-card p { line-height: 1.5; }
        .error-card a {
            display: inline-block;
            margin-top: 12px;
            padding: 8px 14px;
            background: #2563eb;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
        }
        @page {
            size: A4 portrait;
            margin: 4mm 9mm;

            @bottom-left {
                content: "CAPITOL BUILDING, SUKLAYIN, BALER, AURORA";
                color: #333;
                font-family: Arial, Helvetica, sans-serif;
                font-size: 9px;
            }

            @bottom-right {
                content: "Page " counter(page) " of " counter(pages);
                color: #333;
                font-family: Arial, Helvetica, sans-serif;
                font-size: 9px;
            }
        }
        @media print {
            body { margin: 0; padding: 0; background: #fff; }
            .paper {
                display: block;
                width: auto;
                min-height: 0;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }
            .letterhead, .info-table, .inventory-table .item-row, .inventory-table .total-row,
            .signatures, .signature { break-inside: avoid; }
            .inventory-table { flex: none; }
            .inventory-table thead { display: table-row-group; }
            .inventory-table tfoot { display: table-footer-group; }
            .footer { display: none; }
        }
        @media screen and (max-width: 850px) {
            body { padding: 5px; overflow-x: auto; }
            .paper { margin: 0 0 20px; }
        }
    </style>
</head>
<body>
<?php if (!empty($error)): ?>
    <div class="error-card">
        <h1>Unable to Generate ICS</h1>
        <p><?= icsEscape($error) ?></p>
        <a href="index.php">Return to Dashboard</a>
    </div>
<?php else: ?>
    <?php $printedDate = date('n/j/Y'); ?>
    <?php
        $totalAmount = array_sum(array_map(
            static fn(array $item): float => (float)($item['onhand_quantity'] ?? 0) * (float)($item['unit_cost'] ?? 0),
            $items
        ));
        $department = icsSharedValue($items, 'department');
        $location = icsSharedValue($items, 'location');
        $receivedBy = icsSharedValue($items, 'accountable');
        $receivedRole = icsSharedValue($items, 'designation');
    ?>
    <main class="paper">
            <header class="letterhead">
                <img class="letterhead-logo" src="images/logo.png" alt="Provincial seal">
                <div class="office-heading">
                    <div class="republic">Republic of the Philippines</div>
                    <div class="province">PROVINCIAL GOVERNMENT OF AURORA</div>
                    <div>BALER, AURORA</div>
                    <div class="office-name">PROVINCIAL GENERAL SERVICES OFFICE</div>
                    <div class="document-title">INVENTORY CUSTODIAN SLIP (ICS)</div>
                </div>
                <img class="letterhead-logo" src="images/logo1.png" alt="Provincial General Services Office logo">
            </header>

            <table class="info-table">
                <colgroup>
                    <col style="width: 70%;">
                    <col style="width: 30%;">
                </colgroup>
                <tbody>
                    <tr>
                        <td><strong>DEPARTMENT:</strong> <?= icsEscape($department) ?></td>
                        <td><strong>LOCATION:</strong> <?= icsEscape($location) ?></td>
                    </tr>
                </tbody>
            </table>

            <table class="inventory-table<?= count($items) === 1 ? ' single-item' : '' ?>">
                <colgroup>
                    <col style="width: 8%;">
                    <col style="width: 10%;">
                    <col style="width: 46%;">
                    <col style="width: 18%;">
                    <col style="width: 18%;">
                </colgroup>
                <thead>
                    <tr>
                        <th>QTY.</th>
                        <th>UNIT</th>
                        <th>DESCRIPTION</th>
                        <th>DATE<br>ACQUIRED</th>
                        <th>TOTAL COST</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <?php
                            $quantity = (float)($item['onhand_quantity'] ?? 0);
                            $itemTotal = $quantity * (float)($item['unit_cost'] ?? 0);
                        ?>
                        <tr class="item-row">
                            <td class="center"><?= icsEscape(icsNumber($quantity)) ?></td>
                            <td class="center"><?= icsEscape($item['unit'] ?? '') ?></td>
                            <td class="description">
                                <div><?= nl2br(icsEscape($item['description'] ?? '')) ?></div>
                                <div class="meta">
                                    <div>PN: <?= icsEscape($item['property_number'] ?? '') ?></div>
                                    <div>SUPPLIER: <?= icsEscape($item['supplier'] ?? '') ?></div>
                                    <div>STATUS: <?= icsEscape($item['status'] ?? '') ?></div>
                                    <div>REMARKS: <?= icsEscape($item['remarks'] ?? '') ?></div>
                                    <div><strong>ICS NUMBER: <?= icsEscape($item['are_number'] ?? '') ?></strong></div>
                                    <div>AIR NUMBER: <?= icsEscape($item['air_number'] ?? '') ?></div>
                                    <div>PO NUMBER: <?= icsEscape($item['po_number'] ?? '') ?></div>
                                    <div>PR NUMBER: <?= icsEscape($item['pr_number'] ?? '') ?></div>
                                    <div>INVOICE NUMBER: <?= icsEscape($item['invoice_number'] ?? '') ?></div>
                                    <div>FUND: <?= icsEscape($item['source_of_fund'] ?? '') ?></div>
                                    <div>OBR NUMBER:</div>
                                </div>
                            </td>
                            <td class="center"><?= icsEscape(icsDate($item['acquisition_date'] ?? null)) ?></td>
                            <td class="right"><?= icsEscape(number_format($itemTotal, 2)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="total-row">
                        <td colspan="4" class="right">TOTAL AMOUNT:</td>
                        <td class="right"><?= icsEscape(number_format($totalAmount, 2)) ?></td>
                    </tr>
                </tbody>
            </table>

            <section class="signatures">
                <div class="signature">
                    <div class="signature-label">RECEIVED BY:</div>
                    <div class="signed-name"><?= icsEscape($receivedBy !== '' ? $receivedBy : '____________________') ?></div>
                    <div class="signed-role"><?= icsEscape($receivedRole !== '' ? $receivedRole : 'Signature Over Printed Name') ?></div>
                    <div class="signed-date">Date: <?= icsEscape($printedDate) ?></div>
                </div>
                <div class="signature">
                    <div class="signature-label">ISSUED BY:</div>
                    <div class="signed-name">RICARDO Q. BAUTISTA, CPA</div>
                    <div class="signed-role">PROVINCIAL GENERAL SERVICES OFFICER</div>
                    <div class="signed-date">Date: <?= icsEscape($printedDate) ?></div>
                </div>
            </section>

            <footer class="footer">
                <span>CAPITOL BUILDING, SUKLAYIN, BALER, AURORA</span>
                <span>Page 1 of 1</span>
            </footer>
    </main>
    <script>
        window.addEventListener('load', function() {
            window.print();
        });
    </script>
<?php endif; ?>
</body>
</html>

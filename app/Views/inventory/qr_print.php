<?php
declare(strict_types=1);

function qrPrintEscape($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selected Inventory QR Codes</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 18px;
            color: #202124;
            background: #f3f4f6;
            font-family: Arial, Helvetica, sans-serif;
        }
        .toolbar {
            max-width: 210mm;
            margin: 0 auto 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .toolbar h1 { margin: 0; font-size: 20px; }
        .print-button, .return-link {
            border: 0;
            border-radius: 5px;
            padding: 10px 14px;
            color: #fff;
            background: #2457a7;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }
        .sheet {
            max-width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 12mm;
            background: #fff;
            box-shadow: 0 2px 12px #0002;
        }
        .qr-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 9mm 7mm;
        }
        .qr-card {
            min-height: 62mm;
            padding: 4mm;
            border: 1px solid #d4d8de;
            border-radius: 2mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            break-inside: avoid;
            page-break-inside: avoid;
        }
        .qr-card img {
            display: block;
            width: 44mm;
            height: 44mm;
            max-width: 100%;
        }
        .qr-card h2 {
            width: 100%;
            margin: 2mm 0 1mm;
            overflow-wrap: anywhere;
            font-size: 10pt;
        }
        .qr-card p {
            margin: 0;
            overflow-wrap: anywhere;
            color: #4b5563;
            font-size: 8pt;
        }
        .error-card {
            max-width: 210mm;
            margin: 30px auto;
            padding: 20px;
            border-left: 5px solid #b42318;
            background: #fff;
        }
        .error-card h1 { margin-top: 0; color: #b42318; }
        @page { size: A4 portrait; margin: 10mm; }
        @media print {
            body { padding: 0; background: #fff; }
            .toolbar { display: none; }
            .sheet {
                max-width: none;
                min-height: 0;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }
        }
        @media screen and (max-width: 700px) {
            .qr-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .sheet { min-height: 0; padding: 5mm; }
        }
    </style>
</head>
<body>
<?php if (!empty($error)): ?>
    <main class="error-card">
        <h1>Unable to Generate QR Codes</h1>
        <p><?= qrPrintEscape($error) ?></p>
        <a class="return-link" href="index.php">Return to Dashboard</a>
    </main>
<?php else: ?>
    <header class="toolbar">
        <h1>Selected Inventory QR Codes</h1>
        <button class="print-button" type="button" onclick="window.print()">Print QR Codes</button>
    </header>
    <main class="sheet">
        <div class="qr-grid">
            <?php foreach ($items as $item): ?>
                <article class="qr-card">
                    <img src="<?= qrPrintEscape($item['qr_image']) ?>" alt="QR code containing all inventory fields">
                    <h2><?= qrPrintEscape($item['property_number'] ?: $item['article_road_name']) ?></h2>
                    <p><?= qrPrintEscape($item['barcode'] ?? '') ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </main>
<?php endif; ?>
</body>
</html>

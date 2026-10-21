<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inventory Check | GSO Inventory</title>
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
        header p { margin-top: 0.25rem; color: #4b5563; font-size: 0.82rem; }
        main { flex: 1; width: 100%; min-width: 0; padding: 1.5rem 1.75rem; }
        .check-panel, .records-panel {
            overflow: hidden;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
        }
        .check-panel { margin-bottom: 1.25rem; padding: 1.25rem; }
        .check-instructions { margin: 0 0 1rem; color: #4b5563; font-size: 0.88rem; line-height: 1.5; }
        .scan-controls { display: flex; flex-wrap: wrap; align-items: center; gap: 0.65rem; }
        .department-filter {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #374151;
            font-size: 0.82rem;
            font-weight: 600;
        }
        .department-filter select {
            min-height: 36px;
            max-width: min(380px, 55vw);
            padding: 0.4rem 0.65rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: #fff;
            color: #111827;
            font: inherit;
            font-weight: 400;
        }
        .scan-input {
            flex: 1 1 320px;
            min-width: 0;
            min-height: 42px;
            padding: 0.65rem 0.8rem;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            color: #111827;
            background: #fff;
            font: inherit;
            font-size: 0.86rem;
        }
        .scan-input:focus, .search-input:focus {
            border-color: #2563eb;
            outline: 3px solid rgba(37, 99, 235, 0.14);
        }
        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 0.6rem 0.95rem;
            border: 1px solid #2563eb;
            border-radius: 6px;
            background: #2563eb;
            color: #fff;
            font: inherit;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
        }
        .button:hover { background: #1d4ed8; }
        .button-secondary { border-color: #d1d5db; background: #fff; color: #111827; }
        .button-secondary:hover { background: #f3f4f6; }
        .button:disabled { cursor: wait; opacity: 0.65; }
        .scan-message {
            display: none;
            margin-top: 1rem;
            padding: 0.75rem 0.9rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.86rem;
        }
        .scan-message.is-visible { display: block; }
        .scan-message.is-success { border-color: #a7f3d0; background: #ecfdf5; color: #047857; }
        .scan-message.is-error { border-color: #fecaca; background: #fef2f2; color: #b91c1c; }
        .camera-wrap { display: none; width: min(100%, 520px); margin: 1rem auto 0; }
        .camera-wrap.is-open { display: block; }
        .camera-wrap video { display: block; width: 100%; max-height: 360px; border-radius: 8px; background: #111827; }
        .camera-note { margin-top: 0.6rem; color: #4b5563; font-size: 0.8rem; text-align: center; }
        .panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #d1d5db;
            background: #f9fafb;
        }
        .panel-head h2 { margin: 0; font-size: 1rem; }
        .panel-meta { color: #4b5563; font-size: 0.82rem; }
        .panel-tools { display: flex; align-items: center; gap: 0.75rem; }
        .search-input {
            min-height: 36px;
            width: min(260px, 40vw);
            padding: 0.45rem 0.65rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            color: #111827;
            font: inherit;
            font-size: 0.82rem;
        }
        .table-scroll { overflow: auto; max-height: calc(100vh - 440px); }
        table { width: 100%; min-width: 850px; border-collapse: collapse; text-align: left; }
        th, td { padding: 0.75rem 0.85rem; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        th {
            position: sticky;
            top: 0;
            z-index: 1;
            background: #f3f4f6;
            color: #111827;
            font-size: 0.7rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            white-space: nowrap;
        }
        td { color: #1f2937; font-size: 0.82rem; }
        tbody tr:nth-child(even) { background: #f9fafb; }
        tbody tr:last-child td { border-bottom: 0; }
        .verify-badge {
            display: inline-block;
            padding: 0.22rem 0.55rem;
            border-radius: 999px;
            background: #f3f4f6;
            color: #4b5563;
            font-size: 0.72rem;
            font-weight: 700;
            white-space: nowrap;
        }
        .verify-badge.is-verified { background: #ecfdf5; color: #047857; }
        .empty-state { padding: 2rem; color: #4b5563; text-align: center; }
        .scan-summary { display: none; }
        [hidden] { display: none !important; }
        @media print {
            body { display: block; min-height: 0; }
            .sidebar, .app-content > header, .check-panel, .records-panel { display: none !important; }
            .app-content, main { display: block; width: 100%; min-width: 0; padding: 0; }
            .scan-summary { display: block; padding: 0; color: #111827; }
            .scan-summary h1 { margin: 0 0 0.4rem; font-size: 18pt; }
            .scan-summary p { margin: 0 0 0.5rem; font-size: 10pt; }
            .scan-summary table { width: 100%; min-width: 0; margin-top: 1rem; }
            .scan-summary th, .scan-summary td { border: 1px solid #9ca3af; padding: 0.45rem; font-size: 9pt; }
            .scan-summary th { position: static; background: #f3f4f6 !important; }
            .scan-summary tr { break-inside: avoid; page-break-inside: avoid; }
            @page { margin: 15mm; }
        }
        @media (max-width: 760px) {
            body { flex-direction: column; }
            header { align-items: flex-start; flex-wrap: wrap; padding: 1rem; }
            main { padding: 1rem; }
            .check-panel { padding: 1rem; }
            .panel-head { align-items: flex-start; flex-direction: column; }
            .panel-tools { width: 100%; justify-content: space-between; }
            .search-input { width: min(240px, 55vw); }
            .table-scroll { max-height: none; }
        }
    </style>
</head>
<body>
    <?php require __DIR__ . '/../partials/sidebar.php'; ?>
    <div class="app-content">
        <header>
            <div>
                <h1>Inventory Check</h1>
                <p>Scan an item’s system-generated QR code to confirm it is physically present.</p>
            </div>
            <span class="panel-meta">Welcome, <?= htmlspecialchars($_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
        </header>
        <main>
            <section class="check-panel" aria-labelledby="scan-heading">
                <h2 id="scan-heading" style="margin:0 0 0.5rem;font-size:1rem;">Verify an inventory item</h2>
                <p class="check-instructions">Use your camera to scan a QR label, or focus this field and scan with a USB QR reader. The item will be marked as found and the scan time and user will be recorded.</p>
                <form id="scanForm" class="scan-controls">
                    <input class="scan-input" id="qrPayload" name="payload" type="text" autocomplete="off"
                           placeholder="Scan or paste the QR code contents here" aria-label="Scanned QR code contents" required>
                    <input type="hidden" name="department" value="<?= htmlspecialchars($selectedDepartment, ENT_QUOTES, 'UTF-8') ?>">
                    <button class="button" type="submit" id="submitScan">Mark as Found</button>
                    <button class="button button-secondary" type="button" id="startCamera">Use Camera</button>
                </form>
                <div class="scan-message" id="scanMessage" role="status" aria-live="polite"></div>
                <div class="camera-wrap" id="cameraWrap">
                    <video id="cameraPreview" playsinline muted aria-label="QR code camera preview"></video>
                    <p class="camera-note">Hold the QR label steady inside the camera view.</p>
                    <button class="button button-secondary" type="button" id="stopCamera" style="display:block;margin:0.65rem auto 0;">Stop camera</button>
                </div>
            </section>

            <section class="records-panel" aria-labelledby="records-heading">
                <div class="panel-head">
                    <h2 id="records-heading">Physical inventory status</h2>
                    <div class="panel-tools">
                        <span class="panel-meta"><strong id="verifiedCount"><?= $verifiedCount ?></strong> of <strong id="totalCount"><?= count($items) ?></strong> verified</span>
                        <span class="panel-meta"><strong id="sessionScanCount">0</strong> scanned this session</span>
                        <button class="button" type="button" id="printScanSummary" disabled>Print Scan Summary</button>
                        <?php if ($isAdmin): ?>
                            <form method="GET" action="inventory_check.php" class="department-filter">
                                <label for="departmentFilter">Department</label>
                                <select id="departmentFilter" name="department" onchange="this.form.submit()">
                                    <option value="">All departments</option>
                                    <?php foreach ($departmentChoices as $department): ?>
                                        <option value="<?= htmlspecialchars($department, ENT_QUOTES, 'UTF-8') ?>"
                                            <?= $selectedDepartment === $department ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($department, ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        <?php else: ?>
                            <span class="panel-meta">Department: <?= htmlspecialchars($userDepartment !== '' ? $userDepartment : 'Not assigned', ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endif; ?>
                        <input class="search-input" type="search" id="recordSearch" placeholder="Find an item..." aria-label="Filter inventory list">
                    </div>
                </div>
                <div class="table-scroll">
                    <table>
                        <thead>
                            <tr>
                                <th scope="col">Barcode</th>
                                <th scope="col">Property Number</th>
                                <th scope="col">Article / Road Name</th>
                                <th scope="col">Department</th>
                                <th scope="col">Verification</th>
                                <th scope="col">Last Scanned</th>
                                <th scope="col">Scanned By</th>
                                <th scope="col">Scan Count</th>
                            </tr>
                        </thead>
                        <tbody id="inventoryRows">
                            <?php if (!$items): ?>
                                <tr><td class="empty-state" colspan="8">No inventory items are available in your assigned records.</td></tr>
                            <?php else: ?>
                                <?php foreach ($items as $item): ?>
                                    <?php $isVerified = !empty($item['verified_at']); ?>
                                    <tr data-record-id="<?= (int)$item['id'] ?>"
                                        data-verified="<?= $isVerified ? 'true' : 'false' ?>">
                                        <td><?= htmlspecialchars((string)($item['barcode'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars((string)($item['property_number'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars((string)($item['article_road_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars((string)($item['department'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><span class="verify-badge<?= $isVerified ? ' is-verified' : '' ?>"><?= $isVerified ? 'Found' : 'Not scanned' ?></span></td>
                                        <td><?= htmlspecialchars((string)($item['verified_at'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars((string)($item['verified_by'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= (int)($item['scan_count'] ?? 0) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
            <section class="scan-summary" id="scanSummary" aria-label="Inventory scan summary">
                <h1>Physical Inventory Scan Summary</h1>
                <p><strong>Scanned by:</strong> <?= htmlspecialchars($_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                <p><strong>Department scope:</strong>
                    <?= htmlspecialchars(
                        $isAdmin
                            ? ($selectedDepartment !== '' ? $selectedDepartment : 'All departments')
                            : ($userDepartment !== '' ? $userDepartment : 'Not assigned'),
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </p>
                <p><strong>Printed:</strong> <span id="summaryPrintedAt"></span></p>
                <p><strong>Properties scanned:</strong> <span id="summaryPropertyCount">0</span></p>
                <table>
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Property Number</th>
                            <th scope="col">Barcode</th>
                            <th scope="col">Article / Road Name</th>
                            <th scope="col">Department</th>
                            <th scope="col">Scanned At</th>
                        </tr>
                    </thead>
                    <tbody id="summaryRows"></tbody>
                </table>
            </section>
        </main>
    </div>
    <script>
        const scanForm = document.getElementById('scanForm');
        const qrPayload = document.getElementById('qrPayload');
        const submitScan = document.getElementById('submitScan');
        const scanMessage = document.getElementById('scanMessage');
        const cameraWrap = document.getElementById('cameraWrap');
        const cameraPreview = document.getElementById('cameraPreview');
        const inventoryRows = document.getElementById('inventoryRows');
        const sessionScanCount = document.getElementById('sessionScanCount');
        const printScanSummary = document.getElementById('printScanSummary');
        const scannedProperties = new Map();
        let cameraStream = null;
        let cameraTimer = null;
        let isSubmitting = false;

        function showMessage(message, kind) {
            scanMessage.textContent = message;
            scanMessage.className = 'scan-message is-visible ' + (kind === 'success' ? 'is-success' : 'is-error');
        }

        function stopCamera() {
            if (cameraTimer !== null) {
                window.clearTimeout(cameraTimer);
                cameraTimer = null;
            }
            if (cameraStream) {
                cameraStream.getTracks().forEach(function(track) { track.stop(); });
                cameraStream = null;
            }
            cameraPreview.srcObject = null;
            cameraWrap.classList.remove('is-open');
        }

        function updateVerifiedRow(item) {
            const row = Array.from(inventoryRows.querySelectorAll('tr[data-record-id]'))
                .find(function(candidate) { return candidate.dataset.recordId === String(item.id); });
            if (!row) return;

            const wasVerified = row.dataset.verified === 'true';
            row.dataset.verified = 'true';
            const cells = row.cells;
            const badge = cells[4].querySelector('.verify-badge');
            badge.textContent = 'Found';
            badge.classList.add('is-verified');
            cells[5].textContent = item.verified_at;
            cells[6].textContent = item.verified_by;
            cells[7].textContent = item.scan_count;

            if (!wasVerified) {
                document.getElementById('verifiedCount').textContent =
                    String(Number(document.getElementById('verifiedCount').textContent) + 1);
            }
        }

        function addScannedProperty(item) {
            scannedProperties.set(String(item.id), item);
            sessionScanCount.textContent = String(scannedProperties.size);
            printScanSummary.disabled = scannedProperties.size === 0;
        }

        printScanSummary.addEventListener('click', function() {
            if (scannedProperties.size === 0) return;

            const summaryRows = document.getElementById('summaryRows');
            summaryRows.replaceChildren();
            Array.from(scannedProperties.values()).forEach(function(item, index) {
                const row = document.createElement('tr');
                [
                    String(index + 1),
                    item.property_number || '',
                    item.barcode || '',
                    item.article_road_name || '',
                    item.department || '',
                    item.verified_at || ''
                ].forEach(function(value) {
                    const cell = document.createElement('td');
                    cell.textContent = value;
                    row.appendChild(cell);
                });
                summaryRows.appendChild(row);
            });

            document.getElementById('summaryPropertyCount').textContent = String(scannedProperties.size);
            document.getElementById('summaryPrintedAt').textContent = new Date().toLocaleString();
            window.print();
        });

        scanForm.addEventListener('submit', async function(event) {
            event.preventDefault();
            if (isSubmitting) return;
            if (!qrPayload.value.trim()) {
                showMessage('Scan or paste the contents of an inventory QR code first.', 'error');
                qrPayload.focus();
                return;
            }

            isSubmitting = true;
            submitScan.disabled = true;
            try {
                const response = await fetch('inventory_check.php', {
                    method: 'POST',
                    body: new FormData(scanForm),
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const result = await response.json();
                if (!response.ok || !result.success) {
                    showMessage(result.message || 'The QR code could not be verified.', 'error');
                    return;
                }

                updateVerifiedRow(result.item);
                addScannedProperty(result.item);
                showMessage(
                    result.message + ' ' + (result.item.property_number || result.item.barcode),
                    'success'
                );
                qrPayload.value = '';
                qrPayload.focus();
            } catch (error) {
                showMessage('Could not contact the inventory system. Check your connection and try again.', 'error');
            } finally {
                isSubmitting = false;
                submitScan.disabled = false;
            }
        });

        document.getElementById('recordSearch').addEventListener('input', function(event) {
            const term = event.target.value.trim().toLocaleLowerCase();
            inventoryRows.querySelectorAll('tr[data-record-id]').forEach(function(row) {
                row.hidden = term !== '' && !row.textContent.toLocaleLowerCase().includes(term);
            });
        });

        document.getElementById('startCamera').addEventListener('click', async function() {
            if (!('BarcodeDetector' in window)) {
                showMessage('Camera QR scanning is not supported by this browser. Use a USB QR reader or paste the scanned QR contents into the field.', 'error');
                return;
            }
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                showMessage('Camera access requires a secure connection. Open this page on localhost or over HTTPS, or use a QR reader.', 'error');
                return;
            }

            try {
                const detector = new BarcodeDetector({ formats: ['qr_code'] });
                cameraStream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: { ideal: 'environment' } },
                    audio: false
                });
                cameraPreview.srcObject = cameraStream;
                cameraWrap.classList.add('is-open');
                await cameraPreview.play();

                const scanFrame = async function() {
                    if (!cameraStream || isSubmitting) return;
                    try {
                        const codes = await detector.detect(cameraPreview);
                        if (codes.length && codes[0].rawValue) {
                            qrPayload.value = codes[0].rawValue;
                            stopCamera();
                            scanForm.requestSubmit();
                            return;
                        }
                        cameraTimer = window.setTimeout(scanFrame, 250);
                    } catch (error) {
                        stopCamera();
                        showMessage('The camera could not read this QR code. Adjust the camera and try again, or use a QR reader.', 'error');
                    }
                };
                scanFrame();
            } catch (error) {
                stopCamera();
                showMessage('Camera access failed. Allow camera permission, then try again or use a QR reader.', 'error');
            }
        });

        document.getElementById('stopCamera').addEventListener('click', stopCamera);
        window.addEventListener('pagehide', stopCamera);
    </script>
</body>
</html>

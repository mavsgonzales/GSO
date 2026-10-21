<?php
use App\Config\Constants;

$statusChoices       = $statusChoices ?? Constants::STATUS_CHOICES;
$unitChoices         = $unitChoices ?? Constants::UNIT_CHOICES;
$departmentChoices   = $departmentChoices ?? Constants::DEPARTMENT_CHOICES;
$sourceOfFundChoices = $sourceOfFundChoices ?? Constants::SOURCE_OF_FUND_CHOICES;
$generalLedgerChoices = $generalLedgerChoices ?? Constants::GENERAL_LEDGER_CHOICES;
?>

<!-- Modal: Register Property Record (All 25 Fields in Sequential Order) -->
<div class="modal-overlay" id="createModal">
    <div class="modal-box">
        <form method="POST" action="index.php" id="recordForm">
            <input type="hidden" name="action" value="create">
            <input type="hidden" name="record_id" value="">
            <input type="hidden" name="return_view" value="<?= !empty($disposedOnly) ? 'disposed' : '' ?>">
            
            <div class="modal-head">
                <div style="display: flex; align-items: center; gap: 0.6rem;">
                    <span style="display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; border-radius:6px; background:rgba(37,99,235,0.2); color:#60a5fa;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    </span>
                    <h3 id="recordModalTitle" style="margin: 0;">Register Property Record (25 Fields)</h3>
                </div>
                <button type="button" class="modal-close-btn" onclick="closeCreateModal()" aria-label="Close modal">&times;</button>
            </div>

            <div class="modal-body">
                <!-- 1 to 4: Identification & Technical Description -->
                <div class="form-section">1. Identification &amp; Technical Description</div>
                <div class="form-row">
                    <div class="form-field">
                        <label for="f_barcode">1. Barcode (Auto-generated)</label>
                        <input type="text" id="f_barcode" name="barcode" class="form-input" placeholder="Assigned automatically (0000001, 0000002, ...)" readonly>
                    </div>
                    <div class="form-field">
                        <label for="f_property_number">2. Property Number *</label>
                        <input type="text" id="f_property_number" name="property_number" class="form-input" placeholder="e.g. PROP-2024-EQ-001" required>
                    </div>
                    <div class="form-field full">
                        <label for="f_article_road_name">3. Article / Road Name *</label>
                        <input type="text" id="f_article_road_name" name="article_road_name" class="form-input" placeholder="e.g. Concrete Road Section OR Heavy Excavator" required>
                    </div>
                    <div class="form-field full">
                        <label for="f_description">4. Technical Description</label>
                        <textarea id="f_description" name="description" class="form-input" style="min-height:60px;" placeholder="Full specifications, serial number, make, model..."></textarea>
                    </div>
                </div>

                <!-- 5 to 8: Quantity & Valuation -->
                <div class="form-section">2. Inventory Count &amp; Valuation</div>
                <div class="form-row">
                    <div class="form-field">
                        <label for="modalQty">5. Onhand Quantity *</label>
                        <input type="number" step="0.01" min="0" id="modalQty" name="onhand_quantity" class="form-input" value="1.00" required oninput="updateModalValuation()">
                    </div>
                    <div class="form-field">
                        <label for="f_unit">6. Unit * (Choice)</label>
                        <select id="f_unit" name="unit" class="form-input" required>
                            <?php foreach ($unitChoices as $choice): ?>
                                <option value="<?= htmlspecialchars($choice) ?>"><?= htmlspecialchars($choice) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-field">
                        <label for="modalCost">7. Unit Cost (₱) *</label>
                        <input type="number" step="0.01" min="0" id="modalCost" name="unit_cost" class="form-input" value="0.00" required oninput="updateModalValuation()">
                    </div>
                    <div class="form-field">
                        <label for="modalTotalVal">8. Onhand Total Value (Auto-computed)</label>
                        <input type="text" id="modalTotalVal" class="form-input" value="₱0.00" readonly style="background:rgba(16,185,129,0.1); color:#34d399; font-weight:700;">
                    </div>
                </div>

                <!-- 9 to 14: Status, Custodianship & Location -->
                <div class="form-section">3. Status, Custodianship &amp; Location</div>
                <div class="form-row">
                    <div class="form-field">
                        <label for="f_status">9. Status (Choice) *</label>
                        <select id="f_status" name="status" class="form-input" required>
                            <?php foreach ($statusChoices as $choice): ?>
                                <option value="<?= htmlspecialchars($choice) ?>"><?= htmlspecialchars($choice) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-field">
                        <label for="f_location">10. Location *</label>
                        <input type="text" id="f_location" name="location" class="form-input" placeholder="Office, Station, Room, or Road Location" required>
                    </div>
                    <div class="form-field">
                        <label for="f_accountable">11. Accountable Person *</label>
                        <input type="text" id="f_accountable" name="accountable" class="form-input" placeholder="Full name of accountable officer" required>
                    </div>
                    <div class="form-field">
                        <label for="f_designation">12. Designation</label>
                        <input type="text" id="f_designation" name="designation" class="form-input" placeholder="Position / Title">
                    </div>
                    <div class="form-field full">
                        <label for="f_department">13. Department (Choice) *</label>
                        <select id="f_department" name="department" class="form-input" required>
                            <option value="">-- Select Assigned Department --</option>
                            <?php foreach ($departmentChoices as $choice): ?>
                                <option value="<?= htmlspecialchars($choice) ?>"><?= htmlspecialchars($choice) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-field full">
                        <label for="f_previously_accountable">14. Previously Accountable</label>
                        <input type="text" id="f_previously_accountable" name="previously_accountable" class="form-input" placeholder="Previous custodian if transferred">
                    </div>
                </div>

                <!-- 15 to 21: Lifecycle & Procurement Documents -->
                <div class="form-section">4. Lifecycle &amp; Procurement Documents</div>
                <div class="form-row">
                    <div class="form-field">
                        <label for="f_acquisition_date">15. Acquisition Date</label>
                        <input type="date" id="f_acquisition_date" name="acquisition_date" class="form-input" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-field">
                        <label for="f_date_surrendered">16. Date Surrendered (if returned)</label>
                        <input type="date" id="f_date_surrendered" name="date_surrendered" class="form-input">
                    </div>
                    <div class="form-field">
                        <label for="f_po_number">17. PO Number</label>
                        <input type="text" id="f_po_number" name="po_number" class="form-input" placeholder="PO-2024-...">
                    </div>
                    <div class="form-field">
                        <label for="f_pr_number">18. PR Number</label>
                        <input type="text" id="f_pr_number" name="pr_number" class="form-input" placeholder="PR-2024-...">
                    </div>
                    <div class="form-field">
                        <label for="f_invoice_number">19. Invoice Number</label>
                        <input type="text" id="f_invoice_number" name="invoice_number" class="form-input" placeholder="INV-...">
                    </div>
                    <div class="form-field">
                        <label for="f_are_number">20. ICS Number</label>
                        <input type="text" id="f_are_number" name="are_number" class="form-input" placeholder="ICS-2024-...">
                    </div>
                    <div class="form-field full">
                        <label for="f_air_number">21. AIR Number</label>
                        <input type="text" id="f_air_number" name="air_number" class="form-input" placeholder="AIR-2024-...">
                    </div>
                </div>

                <!-- 22 to 25: Remarks, Supplier & Funding Classification -->
                <div class="form-section">5. Remarks, Supplier &amp; Accounts</div>
                <div class="form-row">
                    <div class="form-field full">
                        <label for="f_remarks">22. Remarks</label>
                        <textarea id="f_remarks" name="remarks" class="form-input" style="min-height:50px;" placeholder="Notes, conditions, maintenance log..."></textarea>
                    </div>
                    <div class="form-field full">
                        <label for="f_supplier">23. Supplier</label>
                        <input type="text" id="f_supplier" name="supplier" class="form-input" placeholder="Supplier / Vendor name">
                    </div>
                    <div class="form-field full">
                        <label for="f_source_of_fund">24. Source of Fund (Choice)</label>
                        <select id="f_source_of_fund" name="source_of_fund" class="form-input">
                            <option value="">-- Select Source of Fund --</option>
                            <?php foreach ($sourceOfFundChoices as $choice): ?>
                                <option value="<?= htmlspecialchars($choice) ?>"><?= htmlspecialchars($choice) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-field full">
                        <label for="f_general_ledger">25. General Ledger (Choice)</label>
                        <select id="f_general_ledger" name="general_ledger" class="form-input">
                            <option value="">-- Select COA Chart of Accounts --</option>
                            <?php foreach ($generalLedgerChoices as $choice): ?>
                                <option value="<?= htmlspecialchars($choice) ?>"><?= htmlspecialchars($choice) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="modal-foot">
                <button type="button" class="btn btn-secondary" onclick="closeCreateModal()">Cancel</button>
                <button type="submit" id="recordSubmitButton" class="btn btn-primary" style="display:inline-flex; align-items:center; gap:0.4rem;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>Save to Ledger</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const autocompleteFields = new Map();

    document.querySelectorAll('#recordForm input[type="text"][name], #recordForm textarea[name]').forEach(function(input) {
        const container = input.closest('.form-field');
        if (!container) return;

        const suggestions = document.createElement('div');
        suggestions.className = 'field-suggestions';
        suggestions.setAttribute('role', 'listbox');
        container.appendChild(suggestions);
        autocompleteFields.set(input, { box: suggestions, timer: null, request: null, activeIndex: -1 });
    });

    function closeFieldSuggestions(input) {
        const state = autocompleteFields.get(input);
        if (!state) return;
        state.box.replaceChildren();
        state.box.classList.remove('is-open');
        state.activeIndex = -1;
    }

    function showFieldSuggestions(input, values) {
        const state = autocompleteFields.get(input);
        state.box.replaceChildren();
        state.activeIndex = -1;

        if (!values.length) {
            closeFieldSuggestions(input);
            return;
        }

        values.forEach(function(value, index) {
            const option = document.createElement('button');
            option.type = 'button';
            option.className = 'field-suggestion';
            option.setAttribute('role', 'option');
            option.setAttribute('aria-selected', 'false');
            option.textContent = value;
            option.addEventListener('pointerdown', function(event) {
                event.preventDefault();
                input.value = value;
                closeFieldSuggestions(input);
            });
            state.box.appendChild(option);
        });
        state.box.classList.add('is-open');
    }

    autocompleteFields.forEach(function(state, input) {
        input.setAttribute('autocomplete', 'off');

        input.addEventListener('input', function() {
            window.clearTimeout(state.timer);
            if (state.request) state.request.abort();

            const term = input.value.trim();
            const showSavedChoices = term === '' &&
                (input.name === 'accountable' || input.name === 'supplier');
            if (!term && !showSavedChoices) {
                closeFieldSuggestions(input);
                return;
            }

            state.box.replaceChildren();
            const loading = document.createElement('div');
            loading.className = 'field-suggestions-message';
            loading.textContent = 'Finding matching data…';
            state.box.appendChild(loading);
            state.box.classList.add('is-open');

            state.timer = window.setTimeout(function() {
                state.request = new AbortController();
                const url = 'search_suggestions.php?field=' +
                    encodeURIComponent(input.name) + '&q=' + encodeURIComponent(term);
                fetch(url, {
                    headers: { 'Accept': 'application/json' },
                    signal: state.request.signal
                })
                    .then(function(response) {
                        if (!response.ok) throw new Error('Suggestions request failed.');
                        return response.json();
                    })
                    .then(function(values) {
                        if (input.value.trim() === term) showFieldSuggestions(input, values);
                    })
                    .catch(function(error) {
                        if (error.name === 'AbortError') return;
                        state.box.replaceChildren();
                        const message = document.createElement('div');
                        message.className = 'field-suggestions-message';
                        message.textContent = 'Suggestions could not be loaded.';
                        state.box.appendChild(message);
                    });
            }, 250);
        });

        if (input.name === 'accountable' || input.name === 'supplier') {
            input.addEventListener('focus', function() {
                if (!input.value.trim()) {
                    input.dispatchEvent(new Event('input'));
                }
            });
        }

        input.addEventListener('keydown', function(event) {
            const options = state.box.querySelectorAll('.field-suggestion');
            if (event.key === 'Escape') {
                closeFieldSuggestions(input);
                return;
            }
            if (event.key === 'Enter' && state.activeIndex >= 0 && options[state.activeIndex]) {
                event.preventDefault();
                input.value = options[state.activeIndex].textContent;
                closeFieldSuggestions(input);
                return;
            }
            if (!options.length || (event.key !== 'ArrowDown' && event.key !== 'ArrowUp')) return;

            event.preventDefault();
            const step = event.key === 'ArrowDown' ? 1 : -1;
            state.activeIndex = (state.activeIndex + step + options.length) % options.length;
            options.forEach(function(option, index) {
                const active = index === state.activeIndex;
                option.setAttribute('aria-selected', active ? 'true' : 'false');
                if (active) option.scrollIntoView({ block: 'nearest' });
            });
        });

        input.addEventListener('blur', function() {
            window.setTimeout(function() {
                closeFieldSuggestions(input);
            }, 150);
        });
    });

    function openCreateModal() {
        const modal = document.getElementById('createModal');
        if (modal) {
            const form = document.getElementById('recordForm');
            const departmentSelect = document.getElementById('f_department');
            const legacyDepartment = departmentSelect.querySelector('option[data-legacy-department]');
            if (legacyDepartment) legacyDepartment.remove();
            form.reset();
            form.elements.namedItem('action').value = 'create';
            form.elements.record_id.value = '';
            const barcodeInput = document.getElementById('f_barcode');
            barcodeInput.value = '';
            barcodeInput.readOnly = true;
            barcodeInput.required = false;
            document.getElementById('recordModalTitle').textContent = 'Register Property Record (25 Fields)';
            document.querySelector('#recordSubmitButton span').textContent = 'Save to Ledger';
            updateModalValuation();
            modal.style.display = 'flex';
            const firstInput = document.getElementById('f_barcode');
            if (firstInput) firstInput.focus();
        }
    }

    function openEditModal(encodedRecord) {
        const form = document.getElementById('recordForm');
        const record = JSON.parse(encodedRecord);
        const departmentSelect = document.getElementById('f_department');
        const legacyDepartment = departmentSelect.querySelector('option[data-legacy-department]');
        if (legacyDepartment) legacyDepartment.remove();
        form.reset();
        form.elements.namedItem('action').value = 'edit';
        form.elements.record_id.value = record.id;
        const barcodeInput = document.getElementById('f_barcode');
        barcodeInput.readOnly = false;
        barcodeInput.required = true;

        Object.keys(record).forEach(function(field) {
            const input = form.elements.namedItem(field);
            if (input && input.type !== 'submit') {
                if (input.id === 'f_department' && record[field]
                    && !Array.from(input.options).some(function(option) { return option.value === record[field]; })) {
                    const legacyOption = document.createElement('option');
                    legacyOption.value = record[field];
                    legacyOption.textContent = record[field] + ' (current record)';
                    legacyOption.dataset.legacyDepartment = 'true';
                    input.appendChild(legacyOption);
                }
                input.value = record[field] ?? '';
            }
        });

        document.getElementById('recordModalTitle').textContent = 'Edit Property Record';
        document.querySelector('#recordSubmitButton span').textContent = 'Save Changes';
        updateModalValuation();
        document.getElementById('createModal').style.display = 'flex';
        document.getElementById('f_barcode').focus();
    }

    function closeCreateModal() {
        const modal = document.getElementById('createModal');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    function updateModalValuation() {
        const qtyEl = document.getElementById('modalQty');
        const costEl = document.getElementById('modalCost');
        const totalEl = document.getElementById('modalTotalVal');
        
        const qty = parseFloat(qtyEl ? qtyEl.value : 0) || 0;
        const cost = parseFloat(costEl ? costEl.value : 0) || 0;
        const total = qty * cost;
        
        if (totalEl) {
            totalEl.value = '₱' + total.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
    }

    // Close on click outside modal content
    window.addEventListener('click', function(e) {
        const modal = document.getElementById('createModal');
        if (e.target === modal) {
            closeCreateModal();
        }
    });

    // Close on Escape key press
    window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeCreateModal();
        }
    });
</script>

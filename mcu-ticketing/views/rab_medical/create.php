<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../../helpers/DateHelper.php'; ?>

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<style>
    .section-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        margin-bottom: 1.5rem;
        overflow: hidden;
    }
    .section-header {
        background: linear-gradient(135deg, #204EAB 0%, #3d6dd9 100%);
        color: white;
        padding: 1.25rem 1.5rem;
        font-weight: 600;
        font-size: 1rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    .section-header i {
        font-size: 1.25rem;
    }
    .section-body {
        padding: 1.5rem;
        background: white;
    }
    .date-card {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 1rem;
        background: #f8f9fa;
        transition: all 0.2s;
    }
    .date-card:hover {
        border-color: #204EAB;
        box-shadow: 0 4px 12px rgba(32, 78, 171, 0.1);
    }
    .date-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid #dee2e6;
    }
    .date-badge {
        background: #204EAB;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.9rem;
    }
    .personnel-count {
        background: white;
        border: 2px solid #204EAB;
        color: #204EAB;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.9rem;
    }
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: #6c757d;
    }
    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.3;
    }
    .add-date-section {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 1.5rem;
        border: 2px dashed #dee2e6;
    }
    .info-display {
        background: #e7f3ff;
        border-left: 4px solid #204EAB;
        padding: 1rem;
        border-radius: 6px;
        margin-bottom: 1rem;
    }
    .info-display strong {
        color: #204EAB;
    }
    .toggle-card {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 1.25rem;
        background: white;
        transition: all 0.2s;
        cursor: pointer;
    }
    .toggle-card:hover {
        border-color: #204EAB;
    }
    .toggle-card.active {
        border-color: #204EAB;
        background: #f0f5ff;
    }
    .toggle-card input[type="checkbox"] {
        width: 1.5rem;
        height: 1.5rem;
        cursor: pointer;
    }
    .sticky-submit {
        position: sticky;
        bottom: 0;
        background: white;
        padding: 1.5rem;
        box-shadow: 0 -4px 12px rgba(0,0,0,0.1);
        z-index: 100;
        border-radius: 12px 12px 0 0;
    }
    @media (max-width: 768px) {
        .section-body {
            padding: 1rem;
        }
        .date-card {
            padding: 0.75rem;
        }
    }
</style>

<div class="container-fluid px-4 pb-5">
    <div class="d-flex justify-content-between align-items-center page-header-container mb-4">
        <div>
            <h1 class="page-header-title"><?php echo isset($is_edit) && $is_edit ? 'Edit RAB Medical' : 'Create RAB Medical Submission'; ?></h1>
            <p class="page-header-subtitle">Plan personnel needs for medical report projects</p>
        </div>
        <div>
            <a href="index.php?page=rab_medical_index" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <form action="index.php?page=<?php echo isset($is_edit) && $is_edit ? 'rab_medical_update' : 'rab_medical_store'; ?>" method="POST" id="rabForm">
                <?php if(isset($is_edit) && $is_edit): ?>
                    <input type="hidden" name="id" value="<?php echo $rab['id']; ?>">
                <?php endif; ?>
                
                <!-- 1. PROJECT INFORMATION -->
                <div class="section-card">
                    <div class="section-header">
                        <i class="fas fa-folder-open"></i>
                        <span>Project Information</span>
                    </div>
                    <div class="section-body">
                        <div class="mb-3">
                            <label for="project_id" class="form-label fw-bold">Select Project <span class="text-danger">*</span></label>
                            <select class="form-select form-select-lg" id="project_id" name="project_id" required>
                                <option value="">-- Choose Project --</option>
                                <?php foreach ($projects as $p): ?>
                                    <option value="<?php echo $p['project_id']; ?>" 
                                            data-date="<?php echo DateHelper::formatSmartDateIndonesian($p['tanggal_mcu']); ?>"
                                            data-location="<?php echo htmlspecialchars($p['alamat'] ?? '-'); ?>"
                                            data-korlap="<?php echo htmlspecialchars($p['korlap'] ?? '-'); ?>"
                                            <?php echo (isset($selected_project_id) && $selected_project_id == $p['project_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($p['nama_project']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div id="projectDetails" style="display: none;">
                            <div class="info-display">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <small class="text-muted d-block mb-1"><i class="fas fa-calendar me-2"></i>Project Date</small>
                                        <strong id="displayDate">-</strong>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted d-block mb-1"><i class="fas fa-map-marker-alt me-2"></i>Location</small>
                                        <strong id="displayLocation">-</strong>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted d-block mb-1"><i class="fas fa-user-tie me-2"></i>Korlap</small>
                                        <strong id="displayKorlap">-</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="toggle-card" onclick="toggleCheckbox('needs_hardcopy')">
                                <div class="d-flex align-items-center gap-3">
                                    <input class="form-check-input m-0" type="checkbox" id="needs_hardcopy" name="needs_hardcopy" value="1" <?php echo (isset($rab['needs_hardcopy']) && $rab['needs_hardcopy']) ? 'checked' : ''; ?>>
                                    <div>
                                        <div class="fw-bold"><i class="fas fa-print me-2 text-primary"></i>Hardcopy Required</div>
                                        <small class="text-muted">Check if physical documents are needed</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. NOTIFICATION PREFERENCES -->
                <div class="section-card">
                    <div class="section-header">
                        <i class="fas fa-bell"></i>
                        <span>Notification Preferences</span>
                    </div>
                    <div class="section-body">
                        <p class="text-muted mb-3"><small>Select how you want to notify personnel about their assignments</small></p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="toggle-card" onclick="toggleCheckbox('send_whatsapp')">
                                    <div class="d-flex align-items-center gap-3">
                                        <input class="form-check-input m-0" type="checkbox" id="send_whatsapp" name="send_whatsapp" value="1" <?php echo (isset($rab['send_whatsapp']) && $rab['send_whatsapp']) ? 'checked' : ''; ?>>
                                        <div>
                                            <div class="fw-bold"><i class="fab fa-whatsapp me-2 text-success"></i>WhatsApp Notification</div>
                                            <small class="text-muted">Send via WhatsApp message</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="toggle-card" onclick="toggleCheckbox('send_email')">
                                    <div class="d-flex align-items-center gap-3">
                                        <input class="form-check-input m-0" type="checkbox" id="send_email" name="send_email" value="1" <?php echo (isset($rab['send_email']) && $rab['send_email']) ? 'checked' : ''; ?>>
                                        <div>
                                            <div class="fw-bold"><i class="fas fa-envelope me-2 text-primary"></i>Email Notification</div>
                                            <small class="text-muted">Send via email</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. PERSONNEL PLANNING -->
                <div class="section-card">
                    <div class="section-header">
                        <i class="fas fa-users"></i>
                        <span>Personnel Planning</span>
                    </div>
                    <div class="section-body">
                        <!-- Add Date Section -->
                        <div class="add-date-section mb-4">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small">Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control form-control-lg" id="inputDate">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold small">Personnel Needed <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control form-control-lg" id="inputCount" min="1" value="1">
                                </div>
                                <div class="col-md-5">
                                    <button type="button" class="btn btn-primary btn-lg w-100" id="btnAddDate">
                                        <i class="fas fa-plus-circle me-2"></i>Add Date
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Date Cards Container -->
                        <div id="dateCardsContainer">
                            <div class="empty-state" id="emptyState">
                                <i class="fas fa-calendar-plus"></i>
                                <p class="mb-0">No dates added yet. Add your first date above.</p>
                            </div>
                        </div>

                        <div id="hiddenInputs"></div>
                    </div>
                </div>



                <!-- 4. NOTES -->
                <div class="section-card">
                    <div class="section-header">
                        <i class="fas fa-sticky-note"></i>
                        <span>Notes (Optional)</span>
                    </div>
                    <div class="section-body">
                        <textarea class="form-control" id="notes" name="notes" rows="4" placeholder="Add any additional instructions or context here..."><?php echo isset($rab['notes']) ? htmlspecialchars($rab['notes']) : ''; ?></textarea>
                    </div>
                </div>

                <!-- Sticky Submit -->
                <div class="sticky-submit">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            <small><i class="fas fa-info-circle me-1"></i>Make sure all required fields are filled</small>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg px-5">
                            <i class="fas fa-paper-plane me-2"></i><?php echo isset($is_edit) && $is_edit ? 'Update RAB' : 'Submit RAB'; ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Select2
    $('#project_id').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Choose Project --'
    });

    // Project selection handler
    $('#project_id').on('change', function() {
        const selected = $(this).find(':selected');
        if (selected.val()) {
            $('#displayDate').text(selected.data('date') || '-');
            $('#displayLocation').text(selected.data('location') || '-');
            $('#displayKorlap').text(selected.data('korlap') || '-');
            $('#projectDetails').slideDown();
        } else {
            $('#projectDetails').slideUp();
        }
    });

    // Trigger on page load if editing
    <?php if(isset($selected_project_id) && $selected_project_id): ?>
        $('#project_id').trigger('change');
    <?php endif; ?>

    const btnAdd = document.getElementById('btnAddDate');
    const inputDate = document.getElementById('inputDate');
    const inputCount = document.getElementById('inputCount');
    const dateCardsContainer = document.getElementById('dateCardsContainer');
    const emptyState = document.getElementById('emptyState');
    const hiddenInputs = document.getElementById('hiddenInputs');
    const form = document.getElementById('rabForm');

    // Set min date to today
    const today = new Date().toISOString().split('T')[0];
    inputDate.setAttribute('min', today);

    // Format dates using PHP helper if possible, or JS for dynamic dates
    const indonesianMonths = <?php echo json_encode([
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ]); ?>;

    function formatDate(dateString) {
        if (!dateString) return '-';
        const d = new Date(dateString);
        const day = d.getDate().toString().padStart(2, '0');
        const month = indonesianMonths[d.getMonth()];
        const year = d.getFullYear();
        return `${day} ${month} ${year}`;
    }

    let dateData = {};

    // Add Date Handler
    btnAdd.addEventListener('click', function() {
        const dateVal = inputDate.value;
        const countVal = parseInt(inputCount.value);

        if (!dateVal) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Date',
                text: 'Please select a date before adding.',
                confirmButtonColor: '#204EAB'
            });
            return;
        }

        if (countVal < 1) {
            Swal.fire({
                icon: 'warning',
                title: 'Invalid Count',
                text: 'Personnel count must be at least 1.',
                confirmButtonColor: '#204EAB'
            });
            return;
        }

        if (dateData[dateVal]) {
            Swal.fire({
                icon: 'info',
                title: 'Date Exists',
                text: 'This date is already added. Remove it first if you want to change.',
                confirmButtonColor: '#204EAB'
            });
            return;
        }

        addDateCard(dateVal, countVal);
        
        // Reset inputs
        inputDate.value = '';
        inputCount.value = 1;
    });

    function addDateCard(dateVal, countVal) {
        // Hide empty state
        emptyState.style.display = 'none';

        // Store data
        dateData[dateVal] = { count: countVal };

        // Create card
        const card = document.createElement('div');
        card.className = 'date-card';
        card.id = `card_${dateVal}`;
        card.innerHTML = `
            <div class="date-card-header">
                <div class="d-flex gap-3 align-items-center">
                    <div class="date-badge">
                        <i class="fas fa-calendar-day me-2"></i>${formatDate(dateVal)}
                    </div>
                    <div class="personnel-count">
                        <i class="fas fa-users me-2"></i>${countVal} Personnel
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeDate('${dateVal}')">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        dateCardsContainer.appendChild(card);

        // Add hidden inputs
        const hiddenCount = document.createElement('input');
        hiddenCount.type = 'hidden';
        hiddenCount.name = `dates[${dateVal}][count]`;
        hiddenCount.value = countVal;
        hiddenCount.id = `hidden_count_${dateVal}`;
        hiddenInputs.appendChild(hiddenCount);

        const hiddenDetails = document.createElement('input');
        hiddenDetails.type = 'hidden';
        hiddenDetails.name = `dates[${dateVal}][details]`;
        hiddenDetails.value = '';
        hiddenDetails.id = `hidden_details_${dateVal}`;
        hiddenInputs.appendChild(hiddenDetails);
    }

    window.removeDate = function(dateVal) {
        delete dateData[dateVal];
        document.getElementById(`card_${dateVal}`).remove();
        document.getElementById(`hidden_count_${dateVal}`).remove();
        document.getElementById(`hidden_details_${dateVal}`).remove();

        if (Object.keys(dateData).length === 0) {
            emptyState.style.display = 'block';
        }
    };

    // Form validation
    form.addEventListener('submit', function(e) {
        if (Object.keys(dateData).length === 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'No Dates Added',
                text: 'Please add at least one date for the schedule.',
                confirmButtonColor: '#204EAB'
            });
        }
    });

    // Pre-fill dates if editing
    <?php if(isset($is_edit) && $is_edit && !empty($rab['dates'])): ?>
        <?php foreach($rab['dates'] as $d): ?>
            addDateCard("<?php echo $d['date']; ?>", <?php echo $d['personnel_count']; ?>);
        <?php endforeach; ?>
    <?php endif; ?>
});

// Toggle checkbox helper
function toggleCheckbox(id) {
    const checkbox = document.getElementById(id);
    checkbox.checked = !checkbox.checked;
    checkbox.closest('.toggle-card').classList.toggle('active', checkbox.checked);
}

// Initialize toggle cards
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.toggle-card input[type="checkbox"]').forEach(cb => {
        cb.closest('.toggle-card').classList.toggle('active', cb.checked);
    });
});
</script>

<?php include '../views/layouts/footer.php'; ?>

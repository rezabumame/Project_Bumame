<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<style>
    .cell-safe { background-color: #d1e7dd !important; color: #0f5132 !important; }
    .cell-warning { background-color: #fff3cd !important; color: #664d03 !important; }
    .cell-critical { background-color: #f8d7da !important; color: #842029 !important; }
    .heatmap-cell { cursor: pointer; transition: all 0.2s; min-width: 60px; text-align: center; }
    .heatmap-cell:hover { filter: brightness(0.95); transform: scale(1.05); }
    .sticky-col { position: sticky; left: 0; background-color: #fff; z-index: 10; border-right: 2px solid #dee2e6; }
    .table-container { max-height: 70vh; overflow: auto; }
</style>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center my-4">
        <div>
            <h1 class="page-header-title">
                <i class="fas fa-th text-primary me-2"></i>Availability Heatmap
            </h1>
            <div class="text-muted small">
                Proactive Operational Planning & Capacity Monitoring
            </div>
        </div>
        <div>
            <a href="index.php?page=man_power_mcu" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Project List
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form method="GET" action="index.php" class="row g-3">
                <input type="hidden" name="page" value="man_power_heatmap">
                
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="Internal" <?php echo ($filters['status'] == 'Internal') ? 'selected' : ''; ?>>Internal</option>
                        <option value="External" <?php echo ($filters['status'] == 'External') ? 'selected' : ''; ?>>External</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted">Skill / Station</label>
                    <select name="role" class="form-select">
                        <option value="">All Skills</option>
                        <?php foreach ($skills as $skill): ?>
                        <option value="<?php echo htmlspecialchars($skill['name']); ?>" <?php echo ($filters['role'] == $skill['name']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($skill['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-2"></i>Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Legend -->
    <div class="d-flex align-items-center mb-3 gap-3">
        <span class="small fw-bold text-muted">Availability Indicators:</span>
        <span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success px-3">Safe (≥ 3)</span>
        <span class="badge rounded-pill bg-warning bg-opacity-10 text-warning border border-warning px-3">Warning (< 3)</span>
        <span class="badge rounded-pill bg-danger bg-opacity-10 text-danger border border-danger px-3">Critical (0)</span>
    </div>

    <!-- Heatmap Table -->
    <div class="card border-0 shadow-sm rounded-4 mb-5">
        <div class="card-body p-0">
            <div class="table-responsive table-container">
                <table class="table table-bordered mb-0" style="font-size: 0.85rem;">
                    <thead class="bg-light sticky-top" style="z-index: 20;">
                        <tr>
                            <th class="sticky-col py-3 ps-3" style="min-width: 200px;">Skill / Station</th>
                            <?php foreach ($dates as $date): ?>
                            <th class="text-center py-3" style="min-width: 60px;">
                                <div class="fw-bold"><?php echo date('d', strtotime($date)); ?></div>
                                <div class="small text-muted" style="font-size: 0.7rem;"><?php echo date('M', strtotime($date)); ?></div>
                            </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($skills as $skill): 
                            $skill_name = $skill['name'];
                            if ($filters['role'] && $skill_name !== $filters['role']) continue;
                        ?>
                        <tr>
                            <td class="sticky-col fw-bold ps-3 bg-white text-dark">
                                <?php echo htmlspecialchars($skill_name); ?>
                            </td>
                            <?php foreach ($dates as $date): 
                                $cell = $heatmap_data[$date][$skill_name] ?? ['available' => 0, 'status' => 'critical', 'capacity' => 0, 'used' => 0, 'used_details' => [], 'available_details' => []];
                                $bg_class = 'cell-' . $cell['status'];
                                $details_json = htmlspecialchars(json_encode($cell['used_details']), ENT_QUOTES, 'UTF-8');
                                $available_json = htmlspecialchars(json_encode($cell['available_details']), ENT_QUOTES, 'UTF-8');
                            ?>
                            <td class="p-0 align-middle">
                                <div class="heatmap-cell <?php echo $bg_class; ?> w-100 h-100 py-3 d-flex align-items-center justify-content-center" 
                                    onclick="openHeatmapDetail(this)"
                                    data-date="<?php echo date('d M Y', strtotime($date)); ?>"
                                    data-skill="<?php echo htmlspecialchars($skill_name); ?>"
                                    data-capacity="<?php echo $cell['capacity']; ?>"
                                    data-used="<?php echo $cell['used']; ?>"
                                    data-available="<?php echo $cell['available']; ?>"
                                    data-details='<?php echo $details_json; ?>'
                                    data-available-details='<?php echo $available_json; ?>'
                                    title="Total: <?php echo $cell['capacity']; ?> | Assigned: <?php echo $cell['used']; ?> | Available: <?php echo $cell['available']; ?>"
                                    role="button">
                                    
                                    <span class="fw-bold"><?php echo $cell['available']; ?></span>
                                </div>
                            </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Availability Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-light border d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <div class="small text-muted">Date</div>
                        <div class="fw-bold" id="modalDate">-</div>
                    </div>
                    <div>
                        <div class="small text-muted">Skill</div>
                        <div class="fw-bold" id="modalSkill">-</div>
                    </div>
                    <div class="text-end">
                        <div class="small text-muted">Availability</div>
                        <div class="h4 mb-0 fw-bold" id="modalAvailability">-</div>
                    </div>
                </div>

                <!-- Available Personnel Section -->
                <h6 class="fw-bold mb-3 text-success border-bottom border-success pb-2">
                    <i class="fas fa-check-circle me-2"></i>Available Personnel (Ready)
                </h6>
                <div class="table-responsive mb-4">
                    <table class="table table-sm table-hover" id="availableTable">
                        <thead class="bg-success bg-opacity-10 text-success">
                            <tr>
                                <th>Name</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="availableTableBody">
                            <!-- Populated via JS -->
                        </tbody>
                    </table>
                </div>
                <div id="noAvailableState" class="text-center py-3 text-muted border rounded mb-4 d-none bg-light">
                    <i class="fas fa-exclamation-circle text-warning mb-2"></i>
                    <p class="mb-0 small">No personnel available for this skill.</p>
                </div>

                <!-- Assigned Personnel Section -->
                <h6 class="fw-bold mb-3 text-secondary border-bottom pb-2">
                    <i class="fas fa-user-clock me-2"></i>Assigned Personnel (Busy)
                </h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover" id="modalTable">
                        <thead class="bg-light">
                            <tr>
                                <th>Name</th>
                                <th>Project</th>
                                <th>Assigned Role</th>
                            </tr>
                        </thead>
                        <tbody id="modalTableBody">
                            <!-- Populated via JS -->
                        </tbody>
                    </table>
                </div>
                <div id="emptyState" class="text-center py-3 text-muted border rounded d-none bg-light">
                    <i class="fas fa-check text-secondary mb-2"></i>
                    <p class="mb-0 small">No assignments yet.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Global function to ensure accessibility
function openHeatmapDetail(element) {
    try {
        console.log('Heatmap cell clicked');
        
        var date = element.getAttribute('data-date');
        var skill = element.getAttribute('data-skill');
        var capacity = element.getAttribute('data-capacity');
        var used = element.getAttribute('data-used');
        var available = element.getAttribute('data-available');
        
        // Parse JSON safely
        var details = [];
        var availableDetails = [];
        try {
            details = JSON.parse(element.getAttribute('data-details') || '[]');
        } catch(e) { console.error('Error parsing details', e); }
        
        try {
            availableDetails = JSON.parse(element.getAttribute('data-available-details') || '[]');
        } catch(e) { console.error('Error parsing available details', e); }

        console.log('Details:', details);
        console.log('Available:', availableDetails);

        // Update Header Info
        document.getElementById('modalDate').textContent = date;
        document.getElementById('modalSkill').textContent = skill;
        
        var availElem = document.getElementById('modalAvailability');
        availElem.textContent = available + ' / ' + capacity;
        
        if (available <= 0) availElem.className = 'h4 mb-0 fw-bold text-danger';
        else if (available < 3) availElem.className = 'h4 mb-0 fw-bold text-warning';
        else availElem.className = 'h4 mb-0 fw-bold text-success';

        // Update Assigned Table
        var tbody = document.getElementById('modalTableBody');
        var emptyState = document.getElementById('emptyState');
        var table = document.getElementById('modalTable');
        
        tbody.innerHTML = '';
        
        if (details.length === 0) {
            table.classList.add('d-none');
            emptyState.classList.remove('d-none');
        } else {
            table.classList.remove('d-none');
            emptyState.classList.add('d-none');
            
            details.forEach(function(item) {
                var row = document.createElement('tr');
                row.innerHTML = `
                    <td class="fw-bold text-dark">${item.man_power_name}</td>
                    <td>${item.nama_project}</td>
                    <td><span class="badge bg-light text-dark border">${item.role}</span></td>
                `;
                tbody.appendChild(row);
            });
        }

        // Update Available Table
        var availBody = document.getElementById('availableTableBody');
        var noAvailState = document.getElementById('noAvailableState');
        var availTable = document.getElementById('availableTable');
        
        availBody.innerHTML = '';

        if (availableDetails.length === 0) {
            availTable.classList.add('d-none');
            noAvailState.classList.remove('d-none');
        } else {
            availTable.classList.remove('d-none');
            noAvailState.classList.add('d-none');

            availableDetails.forEach(function(item) {
                var row = document.createElement('tr');
                // Case-insensitive check
                var isInternal = (item.status && item.status.toLowerCase() === 'internal');
                
                var statusBadge = isInternal 
                    ? '<span class="badge bg-info bg-opacity-10 text-info border border-info">Internal</span>' 
                    : '<span class="badge bg-warning bg-opacity-10 text-warning border border-warning">External</span>';
                
                row.innerHTML = `
                    <td class="fw-bold text-success">${item.name}</td>
                    <td>${statusBadge}</td>
                `;
                availBody.appendChild(row);
            });
        }
        
        // Show Modal
        var modalElement = document.getElementById('detailModal');
        var detailModal = bootstrap.Modal.getInstance(modalElement);
        if (!detailModal) {
            detailModal = new bootstrap.Modal(modalElement);
        }
        detailModal.show();
        
    } catch (e) {
        console.error('Error opening modal:', e);
        alert('Could not open details. Please check console for errors.');
    }
}
</script>

<?php include '../views/layouts/footer.php'; ?>
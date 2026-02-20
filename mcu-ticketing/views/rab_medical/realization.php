<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../../helpers/DateHelper.php'; ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center page-header-container">
        <div>
            <h1 class="page-header-title">Medical Report Realization</h1>
            <p class="page-header-subtitle"><?php echo htmlspecialchars($project['nama_project']); ?> | ID: <?php echo $project['project_id']; ?></p>
        </div>
        <div>
            <a href="index.php?page=rab_medical_index" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm">
                <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white p-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-check-circle me-2"></i>Pelaksanaan Aktual</h5>
                    <span class="badge bg-white text-success shadow-sm">Source of Truth</span>
                </div>
                <div class="card-body p-4">
                    
                    <!-- Header Info -->
                    <div class="row mb-4 border-bottom pb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted text-uppercase small fw-bold">Project Context</h6>
                            <h4 class="fw-bold mb-1"><?php echo htmlspecialchars($project['nama_project']); ?></h4>
                            <p class="text-muted mb-0">
                                RAB Status: 
                                <?php if($rab): ?>
                                    <span class="badge bg-primary">Planned</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Unplanned (Direct Realization)</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-6 text-end">
                            <button class="btn btn-success rounded-pill" data-bs-toggle="modal" data-bs-target="#addRealizationModal">
                                <i class="fas fa-plus me-2"></i>Add Realization
                            </button>
                        </div>
                    </div>

                    <!-- Realization Table -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Personnel</th>
                                    <th>Role / Task</th>
                                    <th>Shift Info</th>
                                    <th>Status / Overlap</th>
                                    <th>Notes</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($realizations)): ?>
                                    <tr><td colspan="7" class="text-center text-muted py-5">No realization data recorded yet.</td></tr>
                                <?php else: ?>
                                    <?php foreach($realizations as $r): ?>
                                    <tr>
                                        <td class="fw-bold"><?php echo DateHelper::formatIndonesianDate($r['date']); ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle bg-secondary text-white me-2">
                                                    <?php echo strtoupper(substr($r['user_name'], 0, 1)); ?>
                                                </div>
                                                <?php echo htmlspecialchars($r['user_name']); ?>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($r['role']); ?></span></td>
                                        <td><?php echo htmlspecialchars($r['shift_info']); ?></td>
                                        <td>
                                            <?php if ($r['overlap_count'] > 1): ?>
                                                <div class="text-danger fw-bold small">
                                                    <i class="fas fa-exclamation-triangle me-1"></i> Overlap Detected!
                                                </div>
                                                <div class="small text-muted">
                                                    Also in: 
                                                    <?php 
                                                    foreach($r['other_projects'] as $op) {
                                                        echo htmlspecialchars($op['nama_project']) . " "; 
                                                    }
                                                    ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="badge bg-success"><i class="fas fa-check me-1"></i> Exclusive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="small text-muted"><?php echo htmlspecialchars($r['notes']); ?></td>
                                        <td class="text-end">
                                            <a href="index.php?page=rab_medical_delete_realization&id=<?php echo $r['id']; ?>&project_id=<?php echo $project['project_id']; ?>" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Are you sure? This will affect cost calculations.');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
</div>

<!-- Add Realization Modal -->
<div class="modal fade" id="addRealizationModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="index.php?page=rab_medical_store_realization" method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Record Realization</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="project_id" value="<?php echo $project['project_id']; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Personnel</label>
                        <select class="form-select" name="user_id" required>
                            <option value="">Select Personnel...</option>
                            <?php foreach($users as $user): ?>
                                <option value="<?php echo $user['user_id']; ?>">
                                    <?php echo htmlspecialchars($user['full_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <select class="form-select" name="date" required>
                            <?php 
                            // Use project dates + maybe RAB dates
                            $dates = DateHelper::parseDateArray($project['tanggal_mcu']);
                            foreach($dates as $date): 
                            ?>
                                <option value="<?php echo $date; ?>"><?php echo DateHelper::formatIndonesianDate($date); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Role / Task</label>
                        <select class="form-select" name="role" required>
                            <option value="Input Result">Input Result</option>
                            <option value="Verifier">Verifier</option>
                            <option value="Printing">Printing</option>
                            <option value="Packing">Packing</option>
                            <option value="Admin">Admin</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Shift / Hours</label>
                        <input type="text" class="form-control" name="shift_info" placeholder="e.g. 08:00 - 16:00 or Full Shift">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Realization</button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
.avatar-circle {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: bold;
}
</style>

<?php include '../views/layouts/footer.php'; ?>

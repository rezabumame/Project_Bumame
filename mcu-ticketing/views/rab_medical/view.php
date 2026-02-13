<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center page-header-container mb-4 mt-4">
        <div>
            <h1 class="page-header-title h3">RAB Medical Report Details</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="index.php?page=rab_medical_index">RAB Medical</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($rab['nama_project']); ?></li>
                </ol>
            </nav>
        </div>
        <div>
            <span class="badge bg-<?php 
                echo match($rab['status']) {
                    'draft' => 'secondary',
                    'submitted' => 'warning',
                    'approved_manager' => 'info',
                    'approved_head' => 'success',
                    'completed' => 'dark',
                    'rejected' => 'danger',
                    default => 'secondary'
                };
            ?> fs-6">
                <?php echo strtoupper(str_replace('_', ' ', $rab['status'])); ?>
            </span>
        </div>
    </div>

    <!-- Overlap Warning -->
    <?php if (isset($_SESSION['overlap_warning'])): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php echo $_SESSION['overlap_warning']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['overlap_warning']); ?>
    <?php endif; ?>

    <div class="row">
        <!-- Left Column: RAB Details -->
        <div class="col-lg-8">
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-header bg-primary bg-opacity-10 border-bottom border-primary border-2 py-3">
                    <h5 class="card-title mb-0 text-primary"><i class="fas fa-info-circle me-2"></i>Project Information</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="small text-muted text-uppercase fw-bold mb-1 d-block">Project Name</label>
                            <div class="fw-bold"><?php echo htmlspecialchars($rab['nama_project']); ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-muted text-uppercase fw-bold mb-1 d-block">Company</label>
                            <div><?php echo htmlspecialchars($rab['company_name'] ?? '-'); ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-muted text-uppercase fw-bold mb-1 d-block">Created By</label>
                            <div><?php echo htmlspecialchars($rab['creator_name']); ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-muted text-uppercase fw-bold mb-1 d-block">Hardcopy Required</label>
                            <div>
                                <?php if($rab['needs_hardcopy']): ?>
                                    <span class="badge bg-success"><i class="fas fa-check me-1"></i>YES</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><i class="fas fa-times me-1"></i>NO</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <?php if(!empty($rab['notes'])): ?>
                    <div class="mt-3 pt-3 border-top">
                        <label class="small text-muted text-uppercase fw-bold mb-2 d-block">Notes</label>
                        <div class="p-3 bg-light rounded">
                            <?php echo nl2br(htmlspecialchars($rab['notes'])); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Notification Preferences -->
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-header bg-info bg-opacity-10 border-bottom border-info border-2 py-3">
                    <h5 class="card-title mb-0 text-info"><i class="fas fa-bell me-2"></i>Notification Preferences</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 rounded border <?php echo (isset($rab['send_whatsapp']) && $rab['send_whatsapp']) ? 'border-success bg-success bg-opacity-10' : 'bg-light border-secondary'; ?>">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <i class="fab fa-whatsapp fa-2x me-3 <?php echo (isset($rab['send_whatsapp']) && $rab['send_whatsapp']) ? 'text-success' : 'text-muted'; ?>"></i>
                                        <div>
                                            <div class="fw-bold">WhatsApp</div>
                                            <small class="text-muted">Send via WhatsApp</small>
                                        </div>
                                    </div>
                                    <?php if(isset($rab['send_whatsapp']) && $rab['send_whatsapp']): ?>
                                        <span class="badge bg-success"><i class="fas fa-check"></i> Enabled</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Disabled</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 rounded border <?php echo (isset($rab['send_email']) && $rab['send_email']) ? 'border-warning bg-warning bg-opacity-10' : 'bg-light border-secondary'; ?>">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-envelope fa-2x me-3 <?php echo (isset($rab['send_email']) && $rab['send_email']) ? 'text-warning' : 'text-muted'; ?>"></i>
                                        <div>
                                            <div class="fw-bold">Email</div>
                                            <small class="text-muted">Send via email</small>
                                        </div>
                                    </div>
                                    <?php if(isset($rab['send_email']) && $rab['send_email']): ?>
                                        <span class="badge bg-warning text-dark"><i class="fas fa-check"></i> Enabled</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Disabled</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Requested Schedule -->
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-header bg-success bg-opacity-10 border-bottom border-success border-2 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 text-success"><i class="fas fa-calendar-alt me-2"></i>Requested Schedule</h5>
                    <span class="badge bg-success">
                        Total: <?php echo count($rab['dates']); ?> Dates
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4 py-3">Date</th>
                                <th class="text-center py-3">Personnel Needed</th>
                                <th class="ps-4 py-3">Assigned Kohas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($rab['dates'] as $date): ?>
                            <tr>
                                <td class="ps-4 py-3">
                                    <strong><?php echo date('d M Y', strtotime($date['date'])); ?></strong>
                                    <br><small class="text-muted"><?php echo date('l', strtotime($date['date'])); ?></small>
                                </td>
                                <td class="text-center py-3">
                                    <span class="badge bg-info">
                                        <?php echo $date['personnel_count']; ?> Person(s)
                                    </span>
                                </td>
                                <td class="ps-4 py-3">
                                    <?php 
                                    if (!empty($date['personnel_details'])) {
                                        $ids = explode(',', $date['personnel_details']);
                                        $names = [];
                                        foreach($ids as $uid) {
                                            $uid = trim($uid);
                                            if (isset($personnel_map[$uid])) {
                                                $names[] = '<span class="badge bg-light text-dark border me-1">' . htmlspecialchars($personnel_map[$uid]) . '</span>';
                                            } else {
                                                $names[] = '<span class="badge bg-light text-dark border me-1">' . $uid . '</span>'; 
                                            }
                                        }
                                        echo implode(' ', $names);
                                    } else {
                                        echo '<span class="text-muted fst-italic">Not assigned yet</span>';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Status Timeline -->
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-header bg-warning bg-opacity-10 border-bottom border-warning border-2 py-3">
                    <h5 class="card-title mb-0 text-warning"><i class="fas fa-history me-2"></i>Status Timeline</h5>
                </div>
                <div class="card-body p-4">
                    <div class="timeline">
                        <div class="timeline-item pb-3 border-start border-2 ps-4 position-relative">
                            <div class="timeline-point position-absolute top-0 start-0 translate-middle bg-secondary rounded-circle" style="width: 12px; height: 12px; margin-left: -1px;"></div>
                            <h6 class="mb-1"><i class="fas fa-plus-circle me-2 text-secondary"></i>Created</h6>
                            <small class="text-muted d-block">by <?php echo htmlspecialchars($rab['creator_name']); ?></small>
                            <small class="text-muted"><?php echo date('d M Y H:i', strtotime($rab['created_at'])); ?></small>
                        </div>
                        
                        <?php if($rab['status'] != 'draft'): ?>
                        <div class="timeline-item pb-3 border-start border-2 ps-4 position-relative">
                             <div class="timeline-point position-absolute top-0 start-0 translate-middle bg-warning rounded-circle" style="width: 12px; height: 12px; margin-left: -1px;"></div>
                             <h6 class="mb-1"><i class="fas fa-paper-plane me-2 text-warning"></i>Submitted</h6>
                             <small class="text-muted d-block">Waiting for Manager Approval</small>
                        </div>
                        <?php endif; ?>

                        <?php if(!empty($rab['rejection_reason'])): ?>
                        <div class="timeline-item pb-3 border-start border-2 ps-4 position-relative">
                             <div class="timeline-point position-absolute top-0 start-0 translate-middle bg-danger rounded-circle" style="width: 12px; height: 12px; margin-left: -1px;"></div>
                             <h6 class="mb-1 text-danger"><i class="fas fa-times-circle me-2"></i>Rejected / Revision Requested</h6>
                             <div class="alert alert-danger mt-2 mb-1 p-2 small">
                                 <strong>Reason:</strong> <?php echo nl2br(htmlspecialchars($rab['rejection_reason'])); ?>
                             </div>
                             <?php if($rab['status'] == 'rejected'): ?>
                                <small class="text-muted d-block">Please review and resubmit.</small>
                             <?php else: ?>
                                <small class="text-muted d-block">Revision history.</small>
                             <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <?php if($rab['approved_manager_by']): ?>
                        <div class="timeline-item pb-3 border-start border-2 ps-4 position-relative">
                             <div class="timeline-point position-absolute top-0 start-0 translate-middle bg-success rounded-circle" style="width: 12px; height: 12px; margin-left: -1px;"></div>
                             <h6 class="mb-1 text-success"><i class="fas fa-check-circle me-2"></i>Manager Approved</h6>
                             <small class="text-muted d-block"><?php echo date('d M Y H:i', strtotime($rab['approved_manager_at'])); ?></small>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($rab['approved_head_by']): ?>
                        <div class="timeline-item pb-3 border-start border-2 ps-4 position-relative">
                             <div class="timeline-point position-absolute top-0 start-0 translate-middle bg-success rounded-circle" style="width: 12px; height: 12px; margin-left: -1px;"></div>
                             <h6 class="mb-1 text-success"><i class="fas fa-check-double me-2"></i>Head Approved</h6>
                             <small class="text-muted d-block"><?php echo date('d M Y H:i', strtotime($rab['approved_head_at'])); ?></small>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Column: Actions -->
        <div class="col-lg-4">
            
            <!-- Actions Card -->
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-header bg-white border-bottom border-primary border-2 py-3">
                    <h5 class="card-title mb-0 text-primary"><i class="fas fa-cogs me-2"></i>Actions</h5>
                </div>
                <div class="card-body p-3">
                    <div class="d-grid gap-2">
                        <?php if(in_array($rab['status'], ['draft', 'rejected'])): ?>
                            <form id="form-submit-rab" action="index.php?page=rab_medical_submit" method="POST">
                                <input type="hidden" name="id" value="<?php echo $rab['id']; ?>">
                                <?php if(in_array($_SESSION['role'], ['dw_tim_hasil', 'surat_hasil', 'superadmin'])): ?>
                                    <a href="index.php?page=rab_medical_edit&id=<?php echo $rab['id']; ?>" class="btn btn-warning w-100 mb-2">
                                        <i class="fas fa-edit me-2"></i>Edit
                                    </a>
                                <?php endif; ?>
                                <button type="button" class="btn btn-primary w-100" onclick="confirmAction('form-submit-rab', 'Submit this RAB for approval?')">
                                    <i class="fas fa-paper-plane me-2"></i><?php echo ($rab['status'] == 'rejected') ? 'Resubmit' : 'Submit for Approval'; ?>
                                </button>
                            </form>
                        <?php endif; ?>

                        <?php if($rab['status'] == 'submitted' && ($_SESSION['role'] == 'manager_ops' || $_SESSION['role'] == 'superadmin')): ?>
                            <form id="form-approve-manager" action="index.php?page=rab_medical_approve" method="POST">
                                <input type="hidden" name="id" value="<?php echo $rab['id']; ?>">
                                <input type="hidden" name="action" value="approve_manager">
                                <button type="button" class="btn btn-success w-100 mb-2" onclick="confirmAction('form-approve-manager', 'Approve as Manager?')">
                                    <i class="fas fa-check-circle me-2"></i>Approve (Manager)
                                </button>
                            </form>
                            <form id="form-reject-manager" action="index.php?page=rab_medical_approve" method="POST">
                                <input type="hidden" name="id" value="<?php echo $rab['id']; ?>">
                                <input type="hidden" name="action" value="reject">
                                <input type="hidden" name="rejection_reason" id="reject-reason-manager">
                                <button type="button" class="btn btn-danger w-100" onclick="confirmReject('form-reject-manager')">
                                    <i class="fas fa-times-circle me-2"></i>Reject
                                </button>
                            </form>
                        <?php endif; ?>
                        
                         <?php if($rab['status'] == 'approved_manager' && $head_approval_enabled && ($_SESSION['role'] == 'head_ops' || $_SESSION['role'] == 'superadmin')): ?>
                            <form id="form-approve-head" action="index.php?page=rab_medical_approve" method="POST">
                                <input type="hidden" name="id" value="<?php echo $rab['id']; ?>">
                                <input type="hidden" name="action" value="approve_head">
                                <button type="button" class="btn btn-success w-100 mb-2" onclick="confirmAction('form-approve-head', 'Approve as Head?')">
                                    <i class="fas fa-check-double me-2"></i>Approve (Head)
                                </button>
                            </form>
                            <form id="form-reject-head" action="index.php?page=rab_medical_approve" method="POST">
                                <input type="hidden" name="id" value="<?php echo $rab['id']; ?>">
                                <input type="hidden" name="action" value="reject">
                                <input type="hidden" name="rejection_reason" id="reject-reason-head">
                                <button type="button" class="btn btn-danger w-100" onclick="confirmReject('form-reject-head')">
                                    <i class="fas fa-times-circle me-2"></i>Reject
                                </button>
                            </form>
                        <?php endif; ?>
                        
                        <?php if(!in_array($rab['status'], ['draft', 'submitted', 'approved_manager', 'approved_head'])): ?>
                            <div class="alert alert-secondary text-center mb-0 small">
                                No actions available
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Realization Section (Only visible if approved or completed) -->
            <?php if(in_array($rab['status'], ['approved_manager', 'approved_head', 'completed'])): ?>
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-header bg-white border-bottom border-secondary border-2 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="fas fa-tasks me-2 text-secondary"></i>Realization</h5>
                    <?php if($_SESSION['role'] == 'dw_tim_hasil' || $_SESSION['role'] == 'surat_hasil' || $_SESSION['role'] == 'superadmin'): ?>
                    <button class="btn btn-sm btn-outline-primary" onclick="openRealizationModal()">
                        <i class="fas fa-edit me-1"></i> Manage
                    </button>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <?php if(!empty($realizations)): 
                        // Group realizations by date
                        $groupedRealizations = [];
                        foreach($realizations as $real) {
                            $date = $real['date'];
                            if(!isset($groupedRealizations[$date])) {
                                $groupedRealizations[$date] = [
                                    'personnel' => [],
                                    'notes' => [],
                                    'has_overlap' => false,
                                    'overlap_msg' => []
                                ];
                            }
                            $groupedRealizations[$date]['personnel'][] = $real['user_name'];
                            if(!empty($real['notes'])) {
                                $groupedRealizations[$date]['notes'][] = $real['notes'];
                            }
                            if(!empty($real['overlaps'])) {
                                $groupedRealizations[$date]['has_overlap'] = true;
                                $groupedRealizations[$date]['overlap_msg'] = array_unique(array_merge($groupedRealizations[$date]['overlap_msg'], $real['overlaps']));
                            }
                        }
                        ksort($groupedRealizations);
                    ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light small">
                                    <tr>
                                        <th class="ps-4 py-3" style="width: 15%">Date</th>
                                        <th class="py-3" style="width: 50%">Personnel Assigned</th>
                                        <th class="py-3" style="width: 35%">Notes / Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($groupedRealizations as $date => $data): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark"><?php echo date('d M Y', strtotime($date)); ?></div>
                                            <?php if($data['has_overlap']): ?>
                                                <small class="text-danger d-block mt-1" title="Overlap: <?php echo implode(', ', $data['overlap_msg']); ?>" data-bs-toggle="tooltip">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>Overlap detected
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                <?php foreach($data['personnel'] as $name): ?>
                                                    <span class="badge bg-soft-primary text-primary border border-primary-subtle rounded-pill">
                                                        <i class="fas fa-user-circle me-1 opacity-75"></i><?php echo htmlspecialchars($name); ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if(empty($data['notes'])): ?>
                                                <span class="text-muted small italic">-</span>
                                            <?php else: ?>
                                                <div class="small text-dark">
                                                    <?php echo htmlspecialchars(implode('; ', array_unique($data['notes']))); ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="p-4 text-center text-muted">
                            <i class="fas fa-inbox fa-2x mb-2 opacity-50"></i>
                            <p class="mb-0 small">No realization data yet.</p>
                            <small>Click "Manage" to add.</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- Modal for Managing Realization -->
<div class="modal fade" id="manageRealizationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="index.php?page=rab_medical_store_realization" method="POST" id="realizationForm">
            <div class="modal-content">
                <div class="modal-header bg-light border-bottom">
                    <h5 class="modal-title fw-bold text-primary"><i class="fas fa-tasks me-2"></i>Manage Realization</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="rab_id" value="<?php echo $rab['id']; ?>">
                    <input type="hidden" name="mode" value="replace">
                    
                    <div class="alert alert-info py-2 small mb-4">
                        <i class="fas fa-info-circle me-1"></i> 
                        Grouping by date. You can select multiple personnel for each date.
                    </div>

                    <div id="realizationRows">
                        <!-- Rows will be injected by JS -->
                    </div>
                    
                    <div class="mt-4 pt-3 border-top text-center">
                        <button type="button" class="btn btn-outline-primary btn-sm px-4" id="btnAddRow">
                            <i class="fas fa-plus-circle me-1"></i> Add Another Date
                        </button>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
var rabDates = <?php echo json_encode($rab['dates']); ?>;
var realizationData = <?php echo json_encode($realizations); ?>;
var usersList = <?php echo json_encode(array_values($users)); ?>;
var personnelMap = <?php echo json_encode($personnel_map); ?>; // ID -> Name map

// Create Name -> ID map for reverse lookup (handling legacy data storing names instead of IDs)
var nameToIdMap = {};
Object.entries(personnelMap).forEach(([id, name]) => {
    if (name) {
        nameToIdMap[name.trim()] = id;
        nameToIdMap[name.trim().toLowerCase()] = id;
    }
});

function openRealizationModal() {
    var modal = new bootstrap.Modal(document.getElementById('manageRealizationModal'));
    renderRealizationRows();
    modal.show();
}

function renderRealizationRows() {
    const container = document.getElementById('realizationRows');
    container.innerHTML = '';
    
    let groupedData = {};
    
    // 1. Pre-fill with RAB Plan Dates
    if (rabDates && rabDates.length > 0) {
        rabDates.forEach(rDate => {
            groupedData[rDate.date] = { 
                user_ids: [], 
                notes: '', 
                rab_count: rDate.personnel_count || 0 
            };
        });
    }
    
    // 2. Merge with existing Realization Data
    if (realizationData && realizationData.length > 0) {
        realizationData.forEach(item => {
            if (!groupedData[item.date]) {
                groupedData[item.date] = { user_ids: [], notes: '', rab_count: 0 };
            }
            if (item.user_id) {
                groupedData[item.date].user_ids.push(item.user_id);
            }
            if (!groupedData[item.date].notes && item.notes) {
                groupedData[item.date].notes = item.notes;
            }
        });
    }
    
    const dates = Object.keys(groupedData).sort();
    if (dates.length === 0) {
        addRealizationRow();
    } else {
        dates.forEach((date, index) => {
            addRealizationRow({ 
                date: date, 
                user_ids: groupedData[date].user_ids, 
                notes: groupedData[date].notes,
                rab_count: groupedData[date].rab_count
            }, index);
        });
    }
}

function addRealizationRow(data = null, index = null) {
    const container = document.getElementById('realizationRows');
    const idx = index !== null ? index : document.querySelectorAll('.realization-row').length;
    
    const div = document.createElement('div');
    div.className = 'card mb-3 realization-row shadow-sm border-light';
    
    const dateVal = data ? data.date : '';
    const userIds = data ? data.user_ids : [];
    const notesVal = data ? data.notes : '';
    const rabCount = data ? data.rab_count : 0;

    let userOptions = '';
    usersList.forEach(u => {
        const selected = userIds.includes(String(u.user_id)) || userIds.includes(Number(u.user_id)) ? 'selected' : '';
        userOptions += `<option value="${u.user_id}" ${selected}>${u.full_name}</option>`;
    });

    div.innerHTML = `
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-dark border"><i class="fas fa-calendar-day me-1"></i> Row #${idx + 1}</span>
                    ${rabCount > 0 ? `<span class="badge bg-soft-info text-info border border-info"><i class="fas fa-clipboard-list me-1"></i> RAB Need: ${rabCount} Personnel</span>` : '<span class="badge bg-soft-secondary text-secondary border">Manual Date (Extra)</span>'}
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="this.closest('.realization-row').remove()">
                    <i class="fas fa-trash me-1"></i> Remove
                </button>
            </div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="small fw-bold text-muted mb-1 d-block">Date</label>
                    <input type="date" class="form-control" name="entries[${idx}][date]" value="${dateVal}" required>
                </div>
                <div class="col-md-8">
                    <label class="small fw-bold text-muted mb-1 d-block">Personnel Assigned (Actual)</label>
                    <select class="form-select user-select2" name="entries[${idx}][user_id][]" multiple="multiple" style="width: 100%">
                        ${userOptions}
                    </select>
                    <div class="mt-1">
                        <small class="text-muted status-pax-match" style="font-size: 0.7rem;">
                            Selected: <span class="selected-count">${userIds.length}</span> / Required: ${rabCount > 0 ? rabCount : 'Any'}
                        </small>
                    </div>
                </div>
                <div class="col-12 mt-2">
                    <label class="small fw-bold text-muted mb-1 d-block">Shared Notes</label>
                    <input type="text" class="form-control form-control-sm" name="entries[${idx}][notes]" value="${notesVal}" placeholder="Notes for this date...">
                </div>
            </div>
        </div>
    `;
    
    container.appendChild(div);
    
    // Initialize Select2
    const $select = $(div).find('.user-select2');
    $select.select2({
        theme: 'bootstrap-5',
        placeholder: 'Choose personnel...',
        allowClear: true
    });

    // Handle change to update match counter
    $select.on('change', function() {
        const count = $(this).val().length;
        $(div).find('.selected-count').text(count);
        const matchDisplay = $(div).find('.status-pax-match');
        if (rabCount > 0) {
            if (count === rabCount) {
                matchDisplay.removeClass('text-muted text-danger').addClass('text-success fw-bold');
            } else if (count > rabCount) {
                matchDisplay.removeClass('text-muted text-success').addClass('text-info');
            } else if (count > 0) {
                matchDisplay.removeClass('text-muted text-success').addClass('text-danger');
            } else {
                matchDisplay.removeClass('text-success text-danger text-info').addClass('text-muted font-normal');
            }
        }
    });

    // Trigger initial change to set colors
    $select.trigger('change');
}

document.addEventListener('DOMContentLoaded', function() {
    const btnAddRow = document.getElementById('btnAddRow');
    if (btnAddRow) {
        btnAddRow.addEventListener('click', function() {
            addRealizationRow();
        });
    }
});


function confirmAction(formId, message, icon = 'question') {
    Swal.fire({
        title: 'Confirmation',
        text: message,
        icon: icon,
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, proceed!'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(formId).submit();
        }
    })
}

function confirmDelete(url) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    })
}
</script>

<script>
function confirmReject(formId) {
    Swal.fire({
        title: 'Reject RAB?',
        text: "Please provide a reason for rejection or revision request:",
        icon: 'warning',
        input: 'textarea',
        inputPlaceholder: 'Type your reason here...',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, Reject',
        inputValidator: (value) => {
            if (!value) {
                return 'You need to write something!'
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Set the reason to the hidden input
            const inputId = formId === 'form-reject-manager' ? 'reject-reason-manager' : 'reject-reason-head';
            document.getElementById(inputId).value = result.value;
            document.getElementById(formId).submit();
        }
    });
}
</script>
<?php include '../views/layouts/footer.php'; ?>

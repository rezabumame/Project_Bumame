<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center page-header-container">
        <div>
            <h1 class="page-header-title"><?php echo $page_title; ?></h1>
            <p class="page-header-subtitle">Manage medical check-up results and TAT.</p>
        </div>
        <?php if ($_SESSION['role'] == 'dw_tim_hasil' || $_SESSION['role'] == 'surat_hasil'): ?>
        <div class="bg-white p-1 rounded-pill shadow-sm border">
            <a href="index.php?page=medical_results_index&view=my" class="btn btn-sm rounded-pill px-3 <?php echo ($view_mode == 'my') ? 'btn-primary' : 'btn-light text-muted'; ?>">
                <i class="fas fa-user-check me-1"></i> My Assignments
            </a>
            <a href="index.php?page=medical_results_index&view=all" class="btn btn-sm rounded-pill px-3 <?php echo ($view_mode == 'all') ? 'btn-primary' : 'btn-light text-muted'; ?>">
                <i class="fas fa-globe me-1"></i> All Projects
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Main Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="table-medical-results" class="table table-hover align-middle mb-0" style="width:100%">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="text-center py-3 ps-4">Project Name</th>
                            <th class="text-center py-3">Type</th>
                            <th class="text-center py-3">MCU Date</th>
                            <th class="text-center py-3">Pax</th>
                            <th class="text-center py-3">Project Status</th>
                            <th class="text-center py-3">Result Status</th>
                            <th class="text-center py-3">RAB Status</th>
                            <th class="text-center py-3">Assigned Kohas</th>
                            <th class="text-center py-3">Action</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        <?php foreach ($projects as $project): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="mb-1">
                                        <span class="badge bg-light text-primary border border-primary-subtle rounded-pill px-2">
                                            <i class="fas fa-hashtag me-1"></i><?php echo $project['project_id']; ?>
                                        </span>
                                    </div>
                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($project['nama_project']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($project['company_name']); ?></small>
                                </td>
                                <td class="text-center">
                                    <?php 
                                        $pType = $project['project_type'] ?? 'on-site';
                                        if(strtolower($pType) == 'walk-in') {
                                            echo '<span class="badge bg-info text-white rounded-pill shadow-sm"><i class="fas fa-walking me-1"></i>Walk-In</span>';
                                        } else {
                                            echo '<span class="badge bg-success text-white rounded-pill shadow-sm"><i class="fas fa-building me-1"></i>On-Site</span>';
                                        }
                                    ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border fw-normal">
                                        <?php echo DateHelper::formatSmartDateIndonesian($project['tanggal_mcu']); ?>
                                    </span>
                                </td>
                                <td class="text-center"><span class="fw-medium"><?php echo number_format($project['total_peserta']); ?></span></td>
                                <td class="text-center">
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info rounded-pill px-3 py-2">
                                        <?php echo strtoupper(str_replace('_', ' ', $project['status_project'])); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php 
                                    $status = $project['result_status'] ?? 'PENDING';
                                    $badgeClass = 'bg-secondary bg-opacity-10 text-secondary border-secondary'; // Default
                                    
                                    if ($status == 'IN_PROGRESS') $badgeClass = 'bg-warning bg-opacity-10 text-dark border-warning';
                                    if ($status == 'PENDING_PARTICIPANTS') $badgeClass = 'bg-warning text-dark border-warning shadow-sm';
                                    if ($status == 'COMPLETED') $badgeClass = 'bg-success bg-opacity-10 text-success border-success';
                                    if ($status == 'NOT_NEEDED') $badgeClass = 'bg-danger bg-opacity-10 text-danger border-danger';
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?> border rounded-pill px-3 py-2">
                                        <?php echo strtoupper(str_replace('_', ' ', $status)); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php 
                                    $rabStatus = $project['rab_status'] ?? null;
                                    if (!$rabStatus) {
                                        echo '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary rounded-pill px-2">Not Created</span>';
                                    } else {
                                        $rabClass = 'bg-secondary text-white';
                                        if ($rabStatus == 'draft') $rabClass = 'bg-secondary bg-opacity-10 text-secondary border border-secondary';
                                        if ($rabStatus == 'submitted') $rabClass = 'bg-warning bg-opacity-10 text-dark border border-warning';
                                        if ($rabStatus == 'approved_manager') $rabClass = 'bg-info bg-opacity-10 text-info border border-info';
                                        if ($rabStatus == 'approved_head') $rabClass = 'bg-success bg-opacity-10 text-success border border-success';
                                        if ($rabStatus == 'rejected') $rabClass = 'bg-danger bg-opacity-10 text-danger border border-danger';
                                        echo '<span class="badge ' . $rabClass . ' rounded-pill px-2">' . strtoupper(str_replace('_', ' ', $rabStatus)) . '</span>';
                                    }
                                    ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($project['assigned_names'])): ?>
                                        <div class="d-flex align-items-center justify-content-center">
                                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-1 me-2 d-flex align-items-center justify-content-center" style="width:24px; height:24px;">
                                                <i class="fas fa-user" style="font-size: 0.7rem;"></i>
                                            </div>
                                            <span class="text-truncate small fw-medium" style="max-width: 150px;" title="<?php echo htmlspecialchars($project['assigned_names']); ?>">
                                                <?php echo htmlspecialchars($project['assigned_names']); ?>
                                            </span>
                                        </div>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border rounded-pill fw-normal">Unassigned</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <?php if (in_array($project['rab_status'], ['approved_manager', 'approved_head', 'completed'])): ?>
                                        <a href="index.php?page=medical_results_detail&id=<?php echo $project['project_id']; ?>" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                                            <i class="fas fa-eye me-1"></i> Detail
                                        </a>
                                        <?php else: ?>
                                            <?php 
                                                $tooltip = 'Waiting for Manager Approval';
                                                if (!$project['rab_status']) $tooltip = 'RAB Not Created';
                                                elseif ($project['rab_status'] == 'rejected') $tooltip = 'RAB Rejected';
                                            ?>
                                        <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3 shadow-sm" disabled title="<?php echo $tooltip; ?>">
                                            <i class="fas fa-eye-slash me-1"></i> Detail
                                        </button>
                                        <?php endif; ?>
                                        <?php if (in_array($_SESSION['role'], ['dw_tim_hasil', 'surat_hasil', 'superadmin']) && !$project['rab_status']): ?>
                                        <a href="index.php?page=rab_medical_create&project_id=<?php echo $project['project_id']; ?>" 
                                           class="btn btn-success btn-sm rounded-pill ms-1 px-3 shadow-sm"
                                           title="Create RAB">
                                            <i class="fas fa-plus me-1"></i> RAB
                                        </a>
                                        <?php endif; ?>
                                        <?php if (in_array($_SESSION['role'], ['admin_ops', 'manager_ops', 'superadmin'])): ?>
                                        <button type="button" class="btn btn-light border btn-sm rounded-pill ms-2 px-3 btn-assign" 
                                                data-project-id="<?php echo $project['project_id']; ?>"
                                                data-project-name="<?php echo htmlspecialchars($project['nama_project']); ?>"
                                                title="Assign Kohas">
                                            <i class="fas fa-user-check text-primary"></i>
                                        </button>
                                        <button type="button" class="btn btn-light border btn-sm rounded-pill ms-2 px-3 btn-not-needed" 
                                                data-project-id="<?php echo $project['project_id']; ?>"
                                                data-project-name="<?php echo htmlspecialchars($project['nama_project']); ?>"
                                                title="Mark as Not Needed (Hide)">
                                            <i class="fas fa-ban text-danger"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Assign Modal -->
<div class="modal fade" id="assignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="index.php?page=medical_results_assign_project_batch" method="POST">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Assign Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="project_id" id="assign_project_id">
                    
                    <div class="mb-4 text-center">
                        <div class="avatar-circle bg-primary bg-opacity-10 text-primary mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; border-radius: 50%;">
                            <i class="fas fa-project-diagram fa-2x"></i>
                        </div>
                        <h6 class="fw-bold mb-1" id="assign_project_name_display">Project Name</h6>
                        <p class="text-muted small mb-0">Select a Koordinator Hasil to handle all dates for this project.</p>
                        <input type="hidden" id="assign_project_name"> <!-- Legacy hidden input if needed by JS logic, though we use display now -->
                    </div>

                    <div class="form-floating mb-3">
                        <select class="form-select" name="assigned_to_user_id" id="assigned_to_user_id" required>
                            <option value="">-- Select User --</option>
                            <?php if(isset($kohas_users)): foreach ($kohas_users as $user): ?>
                                <option value="<?php echo $user['user_id']; ?>">
                                    <?php echo htmlspecialchars($user['full_name']); ?>
                                </option>
                            <?php endforeach; endif; ?>
                        </select>
                        <label for="assigned_to_user_id">Assign To</label>
                    </div>
                    
                    <div class="alert alert-info border-0 bg-info bg-opacity-10 text-info small rounded-3">
                        <i class="fas fa-info-circle me-2"></i> This will override individual date assignments for this project.
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Assign All</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../views/layouts/footer.php'; ?>

<script>
    $(document).ready(function() {
        $('#table-medical-results').DataTable({
            "responsive": true,
            "autoWidth": false,
            "order": [[ 6, "desc" ]], // Sort by Last Update desc
            "language": {
                "emptyTable": "No eligible projects found",
                "search": "",
                "searchPlaceholder": "Search projects..."
            },
            "dom": '<"d-flex justify-content-between align-items-center mb-3"f>t<"d-flex justify-content-between align-items-center mt-3"ip>',
        });

        // Handle Assign Button
        $('.btn-assign').click(function() {
            var pid = $(this).data('project-id');
            var pname = $(this).data('project-name');
            $('#assign_project_id').val(pid);
            $('#assign_project_name').val(pname); // Keep hidden input populated
            $('#assign_project_name_display').text(pname); // Update display text
            
            var myModal = new bootstrap.Modal(document.getElementById('assignModal'));
            myModal.show();
        });

        // Handle Not Needed Button
        $('.btn-not-needed').click(function() {
            var pid = $(this).data('project-id');
            var pname = $(this).data('project-name');
            
            Swal.fire({
                title: 'Mark as Not Needed?',
                text: "Are you sure you want to mark '" + pname + "' as Not Needed? It will be removed from this list.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, Mark as Not Needed'
            }).then((result) => {
                if (result.isConfirmed) {
                    var form = $('<form action="index.php?page=medical_results_mark_not_needed" method="POST">' +
                        '<input type="hidden" name="project_id" value="' + pid + '" />' +
                        '</form>');
                    $('body').append(form);
                    form.submit();
                }
            });
        });
    });
</script>

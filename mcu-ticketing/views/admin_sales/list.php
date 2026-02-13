<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<div class="container-fluid px-4">
    <!-- Import Result Modal (Auto Show) -->
    <?php if (isset($_SESSION['import_result'])): ?>
    <div class="modal fade" id="importResultModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import Summary</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-<?php echo $_SESSION['import_result']['error_count'] > 0 ? 'warning' : 'success'; ?>">
                        Successfully imported: <strong><?php echo $_SESSION['import_result']['success_count']; ?></strong><br>
                        Failed: <strong><?php echo $_SESSION['import_result']['error_count']; ?></strong>
                    </div>

                    <?php if (!empty($_SESSION['import_result']['errors'])): ?>
                        <div class="alert alert-danger">
                            <h6>Errors:</h6>
                            <ul class="mb-0">
                                <?php foreach ($_SESSION['import_result']['errors'] as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($_SESSION['import_result']['details'])): ?>
                        <h6>Project Action Items:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>Project ID</th>
                                        <th>Name</th>
                                        <th>Missing / Needs Attention</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($_SESSION['import_result']['details'] as $pid => $detail): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($pid); ?></td>
                                            <td><?php echo htmlspecialchars($detail['name']); ?></td>
                                            <td>
                                                <ul class="mb-0 small text-danger">
                                                    <?php foreach ($detail['missing'] as $key => $val): ?>
                                                        <li><strong><?php echo $key; ?>:</strong> <?php echo $val; ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var myModal = new bootstrap.Modal(document.getElementById('importResultModal'));
            myModal.show();
        });
    </script>
    <?php unset($_SESSION['import_result']); endif; ?>

    <div class="d-flex justify-content-between align-items-center my-4">
        <div>
            <h2 class="fw-bold mb-0" style="color: #204EAB;">Projects List</h2>
            <p class="text-muted mb-0">View and manage all sales projects.</p>
        </div>
        <div>
            <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fas fa-file-csv me-1"></i> Import CSV
            </button>
            <a href="index.php?page=projects_create" class="btn btn-primary"><i class="fas fa-plus"></i> New Project</a>
        </div>
    </div>

<?php if(isset($_GET['status'])): ?>
    <?php if($_GET['status'] == 'success'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Project created successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php elseif($_GET['status'] == 'updated'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Project updated successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
<?php endif; ?>

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Project ID</th>
                        <th>Project Name</th>
                        <th>Company</th>
                        <th>Sales</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($projects as $row): ?>
                        <tr>
                            <td><?php echo $row['project_id']; ?></td>
                            <td><?php echo $row['nama_project']; ?></td>
                            <td><?php echo $row['company_name']; ?></td>
                            <td><?php echo $row['sales_name']; ?></td>
                            <td>
                                <?php 
                                    $badge_color = 'secondary';
                                    if($row['status_project'] == 'approved') $badge_color = 'success';
                                    if($row['status_project'] == 'need_approval_manager') $badge_color = 'warning';
                                    if($row['status_project'] == 'need_approval_head') $badge_color = 'info';
                                    if($row['status_project'] == 'rejected' || $row['status_project'] == 'cancelled' || $row['status_project'] == 're-nego') $badge_color = 'danger';
                                    if($row['status_project'] == 'completed') $badge_color = 'primary';
                                    if($row['status_project'] == 'process_vendor') $badge_color = 'info';
                                    if($row['status_project'] == 'vendor_assigned') $badge_color = 'info text-dark';
                                    if($row['status_project'] == 'no_vendor_needed') $badge_color = 'secondary text-white';
                                    if($row['status_project'] == 'ready_for_invoicing') $badge_color = 'info text-dark';
                                    if($row['status_project'] == 'invoice_requested') $badge_color = 'info text-dark';
                                    if($row['status_project'] == 'invoiced') $badge_color = 'primary';
                                    if($row['status_project'] == 'paid') $badge_color = 'success';
                                    
                                    $statusLabel = strtoupper(str_replace(['_', '-'], ' ', $row['status_project']));
                                    if ($row['status_project'] == 'vendor_assigned') $statusLabel = 'VENDOR CONFIRMED';
                                    if ($row['status_project'] == 'no_vendor_needed') $statusLabel = 'NO VENDOR NEEDED';
                                ?>
                                <span class="badge bg-<?php echo $badge_color; ?>"><?php echo $statusLabel; ?></span>
                            </td>
                            <td><?php echo $row['created_at']; ?></td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="loadProjectDetail('<?php echo $row['project_id']; ?>')"><i class="fas fa-eye"></i></button>
                                


                                <?php if($row['status_project'] == 'need_approval_manager' || $row['status_project'] == 're-nego' || $row['status_project'] == 'rejected'): ?>
                                    <a href="index.php?page=projects_edit&id=<?php echo $row['project_id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Project Detail Modal -->
<?php include '../views/partials/project_detail_modal.php'; ?>

<!-- Import CSV Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Projects from CSV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?page=import_projects_csv" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="alert alert-info small">
                        Use the template to import projects. Duplicate Project IDs will be skipped.
                        <br>
                        <a href="index.php?page=download_project_template" class="alert-link">Download Template</a>
                    </div>
                    <div class="mb-3">
                        <label for="project_csv" class="form-label">Select CSV File</label>
                        <input class="form-control" type="file" id="project_csv" name="project_csv" accept=".csv" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- SPH Link Modal -->
<div class="modal fade" id="sphModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Set SPH Link</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?page=upload_sph" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="project_id" id="sph_project_id">
                    <div class="mb-3">
                        <label for="sph_file" class="form-label">Google Drive Link</label>
                        <input class="form-control" type="url" id="sph_file" name="sph_file" placeholder="https://drive.google.com/..." required pattern="https?://.+">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Link</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#dataTable').DataTable({
            "order": [[ 5, "desc" ]]
        });
    });

    function openSphModal(projectId) {
        document.getElementById('sph_project_id').value = projectId;
        var myModal = new bootstrap.Modal(document.getElementById('sphModal'));
        myModal.show();
    }
    
    var userRole = '<?php echo $_SESSION['role'] ?? ''; ?>';
</script>
<script src="js/project_detail.js?v=<?php echo time(); ?>"></script>

<?php include '../views/layouts/footer.php'; ?>

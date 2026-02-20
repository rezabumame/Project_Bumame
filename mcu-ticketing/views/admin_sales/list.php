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
                <i class="fas fa-file-excel me-1"></i> Import Excel
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

<!-- Import Excel Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Projects from Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="importExcelForm">
                <div class="modal-body">
                    <div class="alert alert-info small">
                        Use the template to import projects. Duplicate Project IDs will be skipped.
                        <br>
                        <button type="button" onclick="downloadProjectExcelTemplate()" class="btn btn-link p-0 alert-link">Download Template</button>
                    </div>
                    <div class="mb-3">
                        <label for="project_excel" class="form-label">Select Excel File (.xlsx, .xls)</label>
                        <input class="form-control" type="file" id="project_excel" name="project_excel" accept=".xlsx, .xls" required>
                    </div>
                    <div id="importStatus" class="mt-3" style="display: none;">
                        <div class="progress mb-2" style="height: 10px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%"></div>
                        </div>
                        <p class="small text-center text-muted mb-0">Processing file...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" id="btnSubmitImport" class="btn btn-success">Import</button>
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable({
            "order": [[ 5, "desc" ]]
        });

        // Handle Project Import Excel
        $('#importExcelForm').on('submit', function(e) {
            e.preventDefault();
            
            const fileInput = document.getElementById('project_excel');
            const file = fileInput.files[0];
            if (!file) return;

            const submitBtn = $('#btnSubmitImport');
            const statusDiv = $('#importStatus');
            
            submitBtn.prop('disabled', true);
            statusDiv.show();

            const reader = new FileReader();
            reader.onload = function(e) {
                const data = new Uint8Array(e.target.result);
                const workbook = XLSX.read(data, {type: 'array'});
                const firstSheetName = workbook.SheetNames[0];
                const worksheet = workbook.Sheets[firstSheetName];
                const jsonData = XLSX.utils.sheet_to_json(worksheet);

                if (jsonData.length === 0) {
                    Swal.fire('Error', 'File kosong atau format tidak sesuai.', 'error');
                    submitBtn.prop('disabled', false);
                    statusDiv.hide();
                    return;
                }

                // Send to server
                $.ajax({
                    url: 'index.php?page=import_projects_excel_json',
                    method: 'POST',
                    data: JSON.stringify({data: jsonData}),
                    contentType: 'application/json',
                    success: function(response) {
                        try {
                            const res = JSON.parse(response);
                            if (res.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: res.message,
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire('Gagal', res.message, 'error');
                            }
                        } catch (e) {
                            console.error('Parse error:', response);
                            Swal.fire('Error', 'Gagal memproses respon server.', 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', 'Terjadi kesalahan saat mengirim data.', 'error');
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false);
                        statusDiv.hide();
                    }
                });
            };
            reader.readAsArrayBuffer(file);
        });
    });

    function downloadProjectExcelTemplate() {
        const rows = [
            [
                'Project ID', 
                'Project Name', 
                'Company Names (Comma Separated)', 
                'Sales Person ID', 
                'Project Type (on_site/walk_in)',
                'Clinic Location (Required for Walk-In)',
                'Jenis Pemeriksaan', 
                'Total Peserta', 
                'Tanggal MCU (YYYY-MM-DD, separated by comma if multiple)', 
                'Alamat', 
                'Notes', 
                'Lunch (Ya/Tidak)', 
                'Snack (Ya/Tidak)',
                'SPH Link (GDrive)',
                'Referral No SPH',
                'Lunch Budget',
                'Snack Budget',
                'Lunch Items (Item:Qty|Item:Qty)',
                'Snack Items (Item:Qty|Item:Qty)'
            ],
            [
                'PRJ-001', 
                'Annual MCU PT Example', 
                'PT Example Indonesia, PT Example Branch', 
                '1', 
                'on_site', 
                '', 
                'Paket Silver', 
                '100', 
                '2026-02-14', 
                'Jl. Sudirman No. 1, Jakarta', 
                'VIP handling required', 
                'Ya', 
                'Ya',
                'https://drive.google.com/file/d/example/view',
                'REF-123',
                '50000',
                '25000',
                'Nasi Padang:50|Ayam Bakar:50',
                'Risoles:100|Puding:100'
            ]
        ];

        const ws = XLSX.utils.aoa_to_sheet(rows);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Project Template");

        // Set column widths
        ws['!cols'] = [
            {wch: 15}, {wch: 30}, {wch: 40}, {wch: 15}, {wch: 20},
            {wch: 30}, {wch: 25}, {wch: 15}, {wch: 30}, {wch: 40},
            {wch: 30}, {wch: 15}, {wch: 15}, {wch: 40}, {wch: 20},
            {wch: 15}, {wch: 15}, {wch: 40}, {wch: 40}
        ];

        XLSX.writeFile(wb, `template_project_import_${new Date().getTime()}.xlsx`);
    }

    function openSphModal(projectId) {
        document.getElementById('sph_project_id').value = projectId;
        var myModal = new bootstrap.Modal(document.getElementById('sphModal'));
        myModal.show();
    }
    
    var userRole = '<?php echo $_SESSION['role'] ?? ''; ?>';
</script>
<script src="js/project_detail.js?v=<?php echo time(); ?>"></script>

<?php include '../views/layouts/footer.php'; ?>

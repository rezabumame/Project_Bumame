<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<div class="main-content">
    <div class="container-fluid">
        <div class="page-header-container mb-4">
            <h1 class="page-header-title">Select Project for Technical Meeting</h1>
            <p class="page-header-subtitle">Silakan pilih proyek untuk membuat Technical Meeting baru.</p>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="projectSelectTable">
                        <thead class="bg-light text-muted">
                            <tr>
                                <th class="ps-4 py-3">Project ID</th>
                                <th class="py-3">Project Name</th>
                                <th class="py-3">Company</th>
                                <th class="py-3">MCU Date</th>
                                <th class="py-3">Status</th>
                                <th class="pe-4 py-3 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($projects)): ?>
                                <?php foreach ($projects as $p): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold text-primary"><?php echo htmlspecialchars($p['project_id']); ?></td>
                                        <td><?php echo htmlspecialchars($p['nama_project']); ?></td>
                                        <td><?php echo htmlspecialchars($p['company_name']); ?></td>
                                        <td>
                                            <?php 
                                                $dates = json_decode($p['tanggal_mcu'], true);
                                                if (is_array($dates)) {
                                                    echo date('d M Y', strtotime($dates[0]));
                                                    if (count($dates) > 1) echo ' <span class="badge bg-secondary">+' . (count($dates)-1) . '</span>';
                                                } else {
                                                    echo date('d M Y', strtotime($p['tanggal_mcu']));
                                                }
                                            ?>
                                        </td>
                                        <td>
                                            <span class="badge rounded-pill bg-<?php 
                                                echo ($p['status_project'] == 'In Progress' ? 'primary' : 
                                                     ($p['status_project'] == 'Approved' ? 'success' : 'secondary')); 
                                            ?>">
                                                <?php echo htmlspecialchars($p['status_project']); ?>
                                            </span>
                                        </td>
                                        <td class="pe-4 text-end">
                                            <a href="index.php?page=technical_meeting_create&project_id=<?php echo $p['project_id']; ?>" class="btn btn-sm btn-primary rounded-pill px-3">
                                                <i class="fas fa-plus me-1"></i>Create TM
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fas fa-folder-open fs-1 mb-3 d-block opacity-25"></i>
                                        No active projects found for you to create a Technical Meeting.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="mt-3">
            <a href="index.php?page=technical_meeting_list" class="btn btn-outline-secondary rounded-pill">
                <i class="fas fa-arrow-left me-2"></i>Back to Log
            </a>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#projectSelectTable').DataTable({
        "pageLength": 10,
        "order": [[ 3, "desc" ]], // Order by MCU Date
        "language": {
            "search": "Cari Proyek:",
            "lengthMenu": "Tampilkan _MENU_ data",
            "zeroRecords": "Data tidak ditemukan",
            "info": "Menampilkan _START_ ke _END_ dari _TOTAL_ data",
            "infoEmpty": "Menampilkan 0 ke 0 dari 0 data",
            "infoFiltered": "(disaring dari _MAX_ total data)"
        }
    });
});
</script>

<?php include '../views/layouts/footer.php'; ?>

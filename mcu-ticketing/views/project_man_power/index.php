<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center page-header-container">
        <div>
            <h1 class="page-header-title">Staff Assignment</h1>
            <p class="page-header-subtitle">Manage Personnel Assignments per Project</p>
        </div>
        <div class="d-flex gap-2">
            <?php if(in_array($_SESSION['role'] ?? '', ['superadmin', 'admin_ops', 'manager_ops', 'head_ops'])): ?>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exportModal">
                <i class="fas fa-file-excel me-2"></i>Export XLSX
            </button>
            <?php endif; ?>
            <a href="index.php?page=man_power_heatmap" class="btn btn-primary">
                <i class="fas fa-th me-2"></i>View Availability Heatmap
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="projectsTable">
                    <thead class="bg-light">
                        <tr>
                            <th>Project ID</th>
                            <th>Project Name</th>
                            <th>Korlap</th>
                            <th>Sales</th>
                            <th style="min-width: 200px;">Dates</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($projects) > 0): ?>
                            <?php foreach ($projects as $p): ?>
                            <tr>
                                <td><span class="badge bg-light text-primary border border-primary-subtle rounded-pill"><?php echo $p['project_id']; ?></span></td>
                                <td class="fw-bold"><?php echo htmlspecialchars($p['nama_project']); ?></td>
                                <td><?php echo htmlspecialchars($p['korlap_name'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($p['sales_name'] ?? '-'); ?></td>
                                <td>
                                    <?php 
                                        echo DateHelper::formatSmartDate($p['tanggal_mcu']);
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                        $badge = 'bg-secondary';
                                        if ($p['status_project'] == 'approved') $badge = 'bg-success';
                                        elseif ($p['status_project'] == 'in_progress_ops') $badge = 'bg-info text-dark';
                                        elseif ($p['status_project'] == 'completed') $badge = 'bg-primary';
                                    ?>
                                    <span class="badge <?php echo $badge; ?> rounded-pill">
                                        <?php echo ucwords(str_replace('_', ' ', $p['status_project'])); ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="index.php?page=man_power_detail&project_id=<?php echo $p['project_id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-users-cog me-2"></i>Manage
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No available projects found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#projectsTable').DataTable({
            "order": [[ 3, "desc" ]]
        });
    });
</script>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Export Staff Assignment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="exportForm">
                <input type="hidden" name="page" value="project_man_power_export">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Date Range (Assignment Date) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="date" name="start_date" class="form-control" required>
                            <span class="input-group-text">to</span>
                            <input type="date" name="end_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="include_internal" name="include_internal" value="1">
                            <label class="form-check-label" for="include_internal">Include Internal Staff</label>
                        </div>
                        <div class="form-text">If checked, internal staff will be included and 'Status' column will be shown.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="btnExport" onclick="processExport()">Export</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
function processExport() {
    const form = document.getElementById('exportForm');
    const formData = new FormData(form);
    
    // Convert FormData to URL params
    const params = new URLSearchParams();
    for (const pair of formData.entries()) {
        params.append(pair[0], pair[1]);
    }
    
    const startDate = form.querySelector('[name="start_date"]').value;
    const endDate = form.querySelector('[name="end_date"]').value;
    
    if (!startDate || !endDate) {
        alert("Please select date range");
        return;
    }

    const btn = document.getElementById('btnExport');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Exporting...';

    fetch('index.php?' + params.toString())
        .then(response => response.json())
        .then(res => {
            if (res.status === 'success') {
                const data = res.data;
                const includeInternal = res.include_internal;
                
                if (data.length === 0) {
                    alert('No data found for the selected period.');
                    return;
                }
                
                // Prepare Excel Data
                const headers = ["Nama Project", "Tanggal Penugasan", "Nama Petugas", "Role / Station", "Catatan"];
                if (includeInternal) headers.push("Status Petugas");
                
                const rows = [headers];
                
                const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
                
                data.forEach(item => {
                    // Format Date: YYYY-MM-DD -> dd mmm yyyy
                    let formattedDate = item.assignment_date;
                    if (item.assignment_date) {
                        const parts = item.assignment_date.split('-');
                        if (parts.length === 3) {
                            const d = parts[2];
                            const m = monthNames[parseInt(parts[1], 10) - 1];
                            const y = parts[0];
                            formattedDate = `${d} ${m} ${y}`;
                        }
                    }

                    const row = [
                        item.nama_project,
                        formattedDate,
                        item.man_power_name,
                        item.role,
                        item.notes || ''
                    ];
                    if (includeInternal) {
                        row.push(item.man_power_status ? (item.man_power_status.charAt(0).toUpperCase() + item.man_power_status.slice(1)) : '-');
                    }
                    rows.push(row);
                });
                
                // Create Workbook
                const ws = XLSX.utils.aoa_to_sheet(rows);
                
                // Auto-width columns (simple)
                const wscols = [
                    {wch: 30}, // Project
                    {wch: 15}, // Date
                    {wch: 25}, // Name
                    {wch: 20}, // Role
                    {wch: 30}, // Notes
                ];
                if (includeInternal) wscols.push({wch: 15});
                ws['!cols'] = wscols;

                const wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, "Staff Assignment");
                
                // Filename
                const sDate = startDate.replace(/-/g, '');
                const eDate = endDate.replace(/-/g, '');
                XLSX.writeFile(wb, `Staff_Assignment_${sDate}_to_${eDate}.xlsx`);
                
                // Close modal
                const modalEl = document.getElementById('exportModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();
            } else {
                alert('Export failed: ' + (res.message || 'Unknown error'));
            }
        })
        .catch(err => {
            console.error(err);
            alert('An error occurred during export.');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
}
</script>

<?php include '../views/layouts/footer.php'; ?>
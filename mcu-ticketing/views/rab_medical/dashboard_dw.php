<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center page-header-container mb-4">
        <div>
            <h1 class="page-header-title">Dashboard Medical Report</h1>
            <p class="page-header-subtitle">Monitoring status project dan kebutuhan petugas.</p>
        </div>
    </div>

    <!-- Dashboard Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 small opacity-75">Project Aktif</h6>
                            <h2 class="mb-0 fw-bold"><?php echo $stats['active_projects']; ?></h2>
                        </div>
                        <i class="fas fa-project-diagram fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 small opacity-75">Kebutuhan Hari Ini</h6>
                            <h2 class="mb-0 fw-bold"><?php echo $stats['daily_needs']; ?> Petugas</h2>
                        </div>
                        <i class="fas fa-users fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-dark h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1 small opacity-75">Menunggu Approval</h6>
                            <h2 class="mb-0 fw-bold"><?php echo $stats['pending_approval']; ?></h2>
                        </div>
                        <i class="fas fa-clock fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Daftar Project Medical Report</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4" style="width: 25%">Nama Project</th>
                            <th style="width: 12%">Status Approval</th>
                            <th style="width: 15%">Kohas</th>
                            <th style="width: 12%">Stats</th>
                            <th style="width: 8%">Notif</th>
                            <th style="width: 8%">Hardcopy</th>
                            <th style="width: 10%">Tanggal</th>
                            <th class="text-end pe-4" style="width: 10%">Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($rabs)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="fas fa-folder-open fa-3x mb-3 d-block opacity-50"></i>
                                    Belum ada data.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($rabs as $r): ?>
                            <tr>
                                <td class="ps-4 fw-bold">
                                    <?php echo htmlspecialchars($r['nama_project']); ?>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = 'bg-secondary';
                                    $statusLabel = ucfirst(str_replace('_', ' ', $r['status']));
                                    
                                    if($r['status'] == 'approved_manager' || $r['status'] == 'approved_head' || $r['status'] == 'completed') {
                                        $statusClass = 'bg-success';
                                    } elseif($r['status'] == 'submitted') {
                                        $statusClass = 'bg-warning text-dark';
                                    } elseif($r['status'] == 'rejected') {
                                        $statusClass = 'bg-danger';
                                    }
                                    ?>
                                    <span class="badge <?php echo $statusClass; ?> rounded-pill small">
                                        <?php echo $statusLabel; ?>
                                    </span>
                                </td>
                                <td class="small"><?php echo htmlspecialchars($r['creator_name']); ?></td>
                                <td>
                                    <div class="small fw-bold text-dark text-nowrap"><?php echo $r['total_days'] ?? 0; ?> Hari</div>
                                    <div class="small text-muted text-nowrap"><?php echo $r['total_personnel'] ?? 0; ?> Petugas</div>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <i class="fab fa-whatsapp <?php echo $r['send_whatsapp'] ? 'text-success' : 'text-muted opacity-25'; ?>" title="WhatsApp"></i>
                                        <i class="fas fa-envelope <?php echo $r['send_email'] ? 'text-primary' : 'text-muted opacity-25'; ?>" title="Email"></i>
                                    </div>
                                </td>
                                <td>
                                    <?php if($r['needs_hardcopy']): ?>
                                        <span class="badge bg-soft-info text-info border border-info" style="font-size: 0.7rem;">YES</span>
                                    <?php else: ?>
                                        <span class="text-muted small">NO</span>
                                    <?php endif; ?>
                                </td>
                                <td class="small text-nowrap"><?php echo date('d M Y', strtotime($r['created_at'])); ?></td>
                                <td class="text-end pe-4">
                                    <a href="index.php?page=rab_medical_view&id=<?php echo $r['id']; ?>" class="btn btn-sm btn-outline-primary text-nowrap">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if($total_pages > 1): ?>
        <div class="card-footer bg-white py-3">
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-end mb-0">
                    <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="index.php?page=rab_medical_index&p=<?php echo $current_page - 1; ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                            <a class="page-link" href="index.php?page=rab_medical_index&p=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="index.php?page=rab_medical_index&p=<?php echo $current_page + 1; ?>">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../views/layouts/footer.php'; ?>

<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center page-header-container">
            <div>
                <h1 class="page-header-title">Technical Meetings Log</h1>
                <p class="page-header-subtitle">Daftar log pertemuan teknis proyek.</p>
            </div>
            <!-- Link to All Projects to create new TM -->
            <?php if (in_array($_SESSION['role'], ['korlap', 'admin_ops', 'superadmin'])): ?>
            <a href="index.php?page=all_projects" class="btn btn-primary rounded-pill px-4 shadow-sm">
                <i class="fas fa-plus-circle me-2"></i>New Technical Meeting
            </a>
            <?php endif; ?>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted">
                            <tr>
                                <th class="ps-4 py-3">Project</th>
                                <th class="py-3">TM Date</th>
                                <th class="py-3">Type</th>
                                <th class="py-3">Setting Alat</th>
                                <th class="py-3">Notes</th>
                                <th class="pe-4 py-3 text-end">Documents</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($tms)): ?>
                                <?php foreach ($tms as $tm): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($tm['nama_project']); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($tm['project_id']); ?> | <?php echo htmlspecialchars($tm['company_name']); ?></small>
                                        </td>
                                        <td>
                                            <?php echo date('d M Y', strtotime($tm['tm_date'])); ?>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $tm['tm_type'] == 'Offline' ? 'bg-info' : 'bg-success'; ?>">
                                                <?php echo htmlspecialchars($tm['tm_type']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo !empty($tm['setting_alat_date']) ? date('d M Y', strtotime($tm['setting_alat_date'])) : '-'; ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $note = htmlspecialchars($tm['notes']);
                                            if (strlen($note) > 50) {
                                                echo substr($note, 0, 50) . '...';
                                            } else {
                                                echo $note;
                                            }
                                            ?>
                                        </td>
                                        <td class="pe-4 text-end">
                                            <?php if (!empty($tm['tm_file_path'])): ?>
                                                <a href="uploads/tm/<?php echo htmlspecialchars($tm['tm_file_path']); ?>" target="_blank" class="btn btn-sm btn-outline-danger" title="TM Document">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if (!empty($tm['layout_file_path'])): ?>
                                                <a href="uploads/tm/<?php echo htmlspecialchars($tm['layout_file_path']); ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Layout Document">
                                                    <i class="fas fa-map"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <!-- Edit button -->
                                            <?php if (in_array($_SESSION['role'], ['korlap', 'admin_ops', 'superadmin'])): ?>
                                            <a href="index.php?page=technical_meeting_create&project_id=<?php echo $tm['project_id']; ?>" class="btn btn-sm btn-outline-primary ms-1" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php endif; ?>

                                            <!-- View Detail button -->
                                            <a href="index.php?page=technical_meeting_view&project_id=<?php echo $tm['project_id']; ?>" class="btn btn-sm btn-outline-info ms-1" title="View Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fas fa-folder-open fs-1 mb-3 d-block opacity-25"></i>
                                        No Technical Meetings recorded yet.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if (isset($total_pages) && $total_pages > 1): ?>
                <div class="px-4 py-3 border-top bg-light d-flex justify-content-between align-items-center">
                    <small class="text-muted">Showing <?php echo count($tms); ?> of <?php echo $total_rows; ?> entries</small>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="index.php?page=technical_meeting_list&p=<?php echo $page - 1; ?>">&laquo; Prev</a>
                            </li>
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="index.php?page=technical_meeting_list&p=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="index.php?page=technical_meeting_list&p=<?php echo $page + 1; ?>">Next &raquo;</a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../views/layouts/footer.php'; ?>

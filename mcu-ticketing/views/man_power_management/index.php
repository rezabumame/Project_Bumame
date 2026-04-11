<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center page-header-container">
        <div>
            <h1 class="page-header-title">Staff Management</h1>
            <p class="page-header-subtitle">Master Data Petugas MCU</p>
        </div>
        <?php if($can_edit): ?>
        <div>
            <?php if($needs_normalization): ?>
            <a href="index.php?page=man_power_management&fix_data=1" class="btn btn-outline-warning me-2" onclick="return confirm('Normalize existing staff data casing? This will fix status and skill names.')">
                <i class="fas fa-sync me-2"></i>Normalize Data
            </a>
            <?php endif; ?>
            <a href="index.php?page=man_power_create" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add New Man Power
            </a>
        </div>
        <?php endif; ?>
    </div>

    <?php if (isset($_SESSION['fix_msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <?php 
            echo $_SESSION['fix_msg']; 
            unset($_SESSION['fix_msg']);
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-12">
                    <form action="index.php" method="GET" class="row g-2 align-items-center">
                        <input type="hidden" name="page" value="man_power_management">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                                <input type="text" name="search" class="form-control border-start-0" placeholder="Search name, email, or skill..." value="<?php echo htmlspecialchars($filters['search']); ?>">
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <select name="status_filter" class="form-select">
                                <option value="">All Status</option>
                                <option value="Internal" <?php echo ($filters['status'] == 'Internal') ? 'selected' : ''; ?>>Internal</option>
                                <option value="External" <?php echo ($filters['status'] == 'External') ? 'selected' : ''; ?>>External</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="skill_filter" class="form-select">
                                <option value="">All Skills</option>
                                <?php foreach($available_skills as $s): ?>
                                    <option value="<?php echo htmlspecialchars($s['name']); ?>" <?php echo ($filters['skill'] == $s['name']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($s['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary px-4">Filter</button>
                            <a href="index.php?page=man_power_management" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Email</th>
                            <th>Skills / Stations</th>
                            <th>Active</th>
                            <?php if($can_edit): ?>
                            <th class="text-end">Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($man_powers) > 0): ?>
                            <?php foreach ($man_powers as $mp): ?>
                            <tr>
                                <td class="fw-bold"><?php echo htmlspecialchars($mp['name']); ?></td>
                                <td>
                                    <?php if(strcasecmp($mp['status'], 'internal') == 0): ?>
                                        <span class="badge bg-primary">Internal</span>
                                    <?php else: ?>
                                        <span class="badge bg-info text-dark">External</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($mp['email']); ?></td>
                                <td>
                                    <?php foreach($mp['skills_array'] as $skill): ?>
                                        <span class="badge bg-secondary me-1"><?php echo htmlspecialchars($skill); ?></span>
                                    <?php endforeach; ?>
                                </td>
                                <td>
                                    <?php if($mp['is_active']): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <?php if($can_edit): ?>
                                <td class="text-end">
                                    <a href="index.php?page=man_power_edit&id=<?php echo $mp['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No man power data found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                            <a class="page-link" href="index.php?page=man_power_management&p=<?php echo $i; ?>&search=<?php echo urlencode($filters['search']); ?>&status_filter=<?php echo urlencode($filters['status']); ?>&skill_filter=<?php echo urlencode($filters['skill']); ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../views/layouts/footer.php'; ?>
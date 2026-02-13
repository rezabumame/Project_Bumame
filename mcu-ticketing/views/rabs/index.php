<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<style>
    /* Custom Styles for RAB List */
    .bg-gradient-primary-soft {
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    }
    .bg-gradient-warning-soft {
        background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
    }
    .bg-gradient-success-soft {
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    }
    
    .card-summary {
        border: none;
        border-radius: 16px;
        transition: transform 0.2s;
    }
    .card-summary:hover {
        transform: translateY(-5px);
    }
    
    .icon-box {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .status-badge {
        padding: 0.5em 1em;
        border-radius: 50rem;
        font-weight: 600;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }
    
    /* Status Colors */
    .badge-need-approval-manager { background-color: #ff9800; color: white; } /* Orange */
    .badge-need-approval-head { background-color: #0d6efd; color: white; } /* Blue */
    .badge-need-approval-ceo { background-color: #6f42c1; color: white; } /* Purple */
    .badge-approved { background-color: #198754; color: white; } /* Green */
    .badge-rejected { background-color: #dc3545; color: white; } /* Red */
    .badge-draft { background-color: #6c757d; color: white; } /* Gray */
    .badge-cancelled { background-color: #212529; color: white; } /* Dark */
    .badge-send-back { background-color: #fd7e14; color: white; } /* Orange-Red */
    .badge-submitted-to-finance { background-color: #0dcaf0; color: black; } /* Cyan */
    .badge-advance-paid { background-color: #20c997; color: white; } /* Teal */
    .badge-need-approval-realization { background-color: #ffc107; color: black; } /* Yellow */
    .badge-realization-approved { background-color: #146c43; color: white; } /* Dark Green */
    .badge-realization-rejected { background-color: #b02a37; color: white; } /* Dark Red */
    .badge-completed { background-color: #052c65; color: white; } /* Navy Blue */

    .table-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.03);
        overflow: hidden;
    }
    
    .table thead th {
        background-color: #f8f9fa;
        border-bottom: 1px solid #edf2f7;
        color: #8492a6;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        padding: 1rem;
    }
    
    .table tbody td {
        padding: 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.875rem;
    }

    .table-hover tbody tr:hover {
        background-color: #f8faff;
    }
    
    .filter-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.02);
    }

    /* Mobile Card View */
    .rab-card-mobile {
        border: none;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        margin-bottom: 1rem;
        background: white;
    }
    .rab-card-mobile .card-body {
        padding: 1.25rem;
    }
    .rab-label {
        font-size: 0.75rem;
        color: #8492a6;
        margin-bottom: 0.25rem;
    }
    .rab-value {
        font-weight: 600;
        color: #3e4455;
    }

    .text-gray-800 {
        color: #1f2937 !important;
    }
    
    .avatar-sm {
        width: 2.5rem;
        height: 2.5rem;
        font-size: 0.8rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
</style>

<div class="container-fluid px-4 pb-5">
    <!-- Header & Action -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 page-header-container">
        <div>
            <h1 class="page-header-title">Daftar Pengajuan RAB</h1>
            <p class="page-header-subtitle">Kelola dan pantau pengajuan anggaran MCU</p>
        </div>
        <div class="d-flex gap-2">
            <?php if ($_SESSION['role'] == 'superadmin' || $_SESSION['role'] == 'head_ops' || $_SESSION['role'] == 'manager_ops'): ?>
            <a href="index.php?page=settings" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm">
                <i class="fas fa-cog me-2"></i> System Config
            </a>
            <?php endif; ?>
            <?php if ($_SESSION['role'] == 'korlap' || $_SESSION['role'] == 'admin_ops'): ?>
            <a href="index.php?page=rabs_create" class="btn btn-primary rounded-pill px-4 shadow-sm">
                <i class="fas fa-plus me-2"></i> Buat RAB Baru
            </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i> RAB berhasil disimpan!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <!-- Total Month -->
        <div class="col-md-2">
            <div class="card card-summary bg-gradient-primary-soft h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-primary fw-bold small text-uppercase mb-1">Bulan Ini</p>
                            <h2 class="mb-0 fw-bold text-gray-800"><?php echo number_format($summary['total_month']); ?></h2>
                        </div>
                        <div class="icon-box bg-white text-primary shadow-sm" style="width: 40px; height: 40px; font-size: 1.2rem;">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Total Pending -->
        <div class="col-md-2">
            <div class="card card-summary bg-gradient-warning-soft h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-warning fw-bold small text-uppercase mb-1">Pending</p>
                            <h2 class="mb-0 fw-bold text-gray-800"><?php echo number_format($summary['pending']); ?></h2>
                        </div>
                        <div class="icon-box bg-white text-warning shadow-sm" style="width: 40px; height: 40px; font-size: 1.2rem;">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Total Approved -->
        <div class="col-md-2">
            <div class="card card-summary bg-gradient-success-soft h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-success fw-bold small text-uppercase mb-1">Approved</p>
                            <h2 class="mb-0 fw-bold text-gray-800"><?php echo number_format($summary['approved']); ?></h2>
                        </div>
                        <div class="icon-box bg-white text-success shadow-sm" style="width: 40px; height: 40px; font-size: 1.2rem;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Completed -->
        <div class="col-md-2">
            <div class="card card-summary h-100" style="background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-info fw-bold small text-uppercase mb-1">Completed</p>
                            <h2 class="mb-0 fw-bold text-gray-800"><?php echo number_format($summary['completed']); ?></h2>
                        </div>
                        <div class="icon-box bg-white text-info shadow-sm" style="width: 40px; height: 40px; font-size: 1.2rem;">
                            <i class="fas fa-flag-checkered"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Budget / Unpaid Advance -->
        <div class="col-md-4">
            <div class="card card-summary bg-white border h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted fw-bold small text-uppercase mb-1">Total Cash Advance (Belum Dibayar)</p>
                            <h4 class="mb-0 fw-bold text-dark">Rp <?php echo number_format($summary['unpaid_advance'], 0, ',', '.'); ?></h4>
                        </div>
                        <div class="icon-box bg-light text-dark" style="width: 40px; height: 40px; font-size: 1.2rem;">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card filter-card mb-4">
        <div class="card-body p-4">
            <form method="GET" action="index.php" class="row g-3">
                <input type="hidden" name="page" value="rabs_list">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari No RAB atau Project..." value="<?php echo htmlspecialchars($filters['search']); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="all">Semua Status</option>
                        <option value="draft" <?php echo $filters['status'] == 'draft' ? 'selected' : ''; ?>>Draft</option>
                        <option value="need_approval_manager" <?php echo $filters['status'] == 'need_approval_manager' ? 'selected' : ''; ?>>Butuh Approval Manager</option>
                        <option value="need_approval_head" <?php echo $filters['status'] == 'need_approval_head' ? 'selected' : ''; ?>>Butuh Approval Head</option>
                        <option value="approved" <?php echo $filters['status'] == 'approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="rejected" <?php echo $filters['status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="input-group">
                        <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($filters['start_date']); ?>">
                        <span class="input-group-text bg-light border-0">to</span>
                        <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($filters['end_date']); ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card table-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>No RAB</th>
                            <th>Project</th>
                            <th>Created By</th>
                            <th>Cash Advance</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rabs)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p class="mb-0">Tidak ada data RAB ditemukan.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($rabs as $rab): 
                                $statusClass = 'badge-' . str_replace('_', '-', $rab['status']);
                            ?>
                            <tr>
                                <td class="fw-bold text-primary">
                                    <a href="index.php?page=rabs_show&id=<?php echo $rab['id']; ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($rab['rab_number']); ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="fw-bold text-gray-800"><?php echo htmlspecialchars($rab['nama_project']); ?></div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm rounded-circle bg-light text-primary me-2 fw-bold">
                                            <?php echo strtoupper(substr($rab['creator_name'], 0, 1)); ?>
                                        </div>
                                        <span><?php echo htmlspecialchars($rab['creator_name']); ?></span>
                                    </div>
                                </td>
                                <td class="fw-bold text-warning">Rp <?php echo number_format((float)($rab['total_transport'] ?? 0)); ?></td>
                                <td class="fw-bold text-gray-800">Rp <?php echo number_format((float)($rab['grand_total'] ?? 0)); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $statusClass; ?>">
                                        <?php echo ucwords(str_replace('_', ' ', $rab['status'])); ?>
                                    </span>
                                </td>
                                <td class="text-muted small">
                                    <?php echo date('d M Y', strtotime($rab['created_at'])); ?><br>
                                    <?php echo date('H:i', strtotime($rab['created_at'])); ?>
                                </td>
                                <td class="text-end">
                                    <a href="index.php?page=rabs_show&id=<?php echo $rab['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        <i class="fas fa-eye me-1"></i> Detail
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if (isset($total_pages) && $total_pages > 1): ?>
            <div class="px-4 py-3 border-top bg-light d-flex justify-content-between align-items-center">
                <small class="text-muted">Showing <?php echo count($rabs); ?> of <?php echo $total_rows; ?> entries</small>
                <nav aria-label="Page navigation">
                    <ul class="pagination pagination-sm mb-0">
                        <!-- Previous Link -->
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link border-0 bg-transparent" href="<?php echo ($page <= 1) ? '#' : 'index.php?page=rabs_list&p=' . ($page - 1) . '&search=' . urlencode($filters['search']) . '&status=' . urlencode($filters['status']) . '&start_date=' . urlencode($filters['start_date']) . '&end_date=' . urlencode($filters['end_date']); ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo; Prev</span>
                            </a>
                        </li>
                        
                        <!-- Page Numbers -->
                        <?php 
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        if ($start_page > 1) {
                            echo '<li class="page-item"><a class="page-link border-0 bg-transparent" href="index.php?page=rabs_list&p=1&search='.urlencode($filters['search']).'&status='.urlencode($filters['status']).'&start_date='.urlencode($filters['start_date']).'&end_date='.urlencode($filters['end_date']).'">1</a></li>';
                            if ($start_page > 2) {
                                echo '<li class="page-item disabled"><span class="page-link border-0 bg-transparent">...</span></li>';
                            }
                        }
                        
                        for ($i = $start_page; $i <= $end_page; $i++): 
                        ?>
                            <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                <a class="page-link border-0 <?php echo ($page == $i) ? 'bg-primary text-white rounded-circle' : 'bg-transparent'; ?>" href="index.php?page=rabs_list&p=<?php echo $i; ?>&search=<?php echo urlencode($filters['search']); ?>&status=<?php echo urlencode($filters['status']); ?>&start_date=<?php echo urlencode($filters['start_date']); ?>&end_date=<?php echo urlencode($filters['end_date']); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php 
                        if ($end_page < $total_pages) {
                            if ($end_page < $total_pages - 1) {
                                echo '<li class="page-item disabled"><span class="page-link border-0 bg-transparent">...</span></li>';
                            }
                            echo '<li class="page-item"><a class="page-link border-0 bg-transparent" href="index.php?page=rabs_list&p='.$total_pages.'&search='.urlencode($filters['search']).'&status='.urlencode($filters['status']).'&start_date='.urlencode($filters['start_date']).'&end_date='.urlencode($filters['end_date']).'">'.$total_pages.'</a></li>';
                        }
                        ?>

                        <!-- Next Link -->
                        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link border-0 bg-transparent" href="<?php echo ($page >= $total_pages) ? '#' : 'index.php?page=rabs_list&p=' . ($page + 1) . '&search=' . urlencode($filters['search']) . '&status=' . urlencode($filters['status']) . '&start_date=' . urlencode($filters['start_date']) . '&end_date=' . urlencode($filters['end_date']); ?>" aria-label="Next">
                                <span aria-hidden="true">Next &raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../views/layouts/footer.php'; ?>
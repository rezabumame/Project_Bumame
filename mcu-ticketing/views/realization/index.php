<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center page-header-container">
        <div>
            <h1 class="page-header-title">Realisasi Harian</h1>
            <p class="page-header-subtitle">Input realisasi harian berdasarkan RAB yang disetujui.</p>
        </div>
    </div>

            <?php if (isset($_GET['msg'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($_GET['msg']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if ($_SESSION['role'] == 'korlap'): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="fw-bold mb-0 text-primary">Pilih RAB untuk Realisasi</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No. RAB</th>
                                    <th>Project</th>
                                    <th>Anggaran (RAB)</th>
                                    <th>Total Realisasi</th>
                                    <th>Sisa Anggaran</th>
                                    <th>Status</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($active_rabs)): ?>
                                    <?php foreach ($active_rabs as $rab): ?>
                                        <?php 
                                            $total_realization = $rab['total_realization'] ?? 0;
                                            $sisa_anggaran = $rab['grand_total'] - $total_realization;
                                            $sisa_class = $sisa_anggaran < 0 ? 'text-danger' : 'text-success';
                                        ?>
                                        <tr>
                                            <td class="fw-bold"><?php echo htmlspecialchars($rab['rab_number']); ?></td>
                                            <td>
                                                <?php echo htmlspecialchars($rab['nama_project']); ?><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($rab['creator_name']); ?></small>
                                            </td>
                                            <td>Rp <?php echo number_format($rab['grand_total']); ?></td>
                                            <td>Rp <?php echo number_format($total_realization); ?></td>
                                            <td class="fw-bold <?php echo $sisa_class; ?>">Rp <?php echo number_format($sisa_anggaran); ?></td>
                                            <td><span class="badge bg-success"><?php echo ucfirst(str_replace('_', ' ', $rab['status'])); ?></span></td>
                                            <td class="text-end">
                                                <a href="index.php?page=realization_create&rab_id=<?php echo $rab['id']; ?>" class="btn btn-sm btn-primary px-3">
                                                    <i class="fas fa-edit me-1"></i> Input
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-3 text-muted">
                                            Tidak ada RAB yang siap untuk direalisasi (Approved / Paid).
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (isset($grouped_history) && !empty($grouped_history)): ?>
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="fw-bold mb-0 text-secondary">Riwayat Realisasi Harian</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No. RAB</th>
                                    <th>Project</th>
                                    <th>Korlap</th>
                                    <th>Total Anggaran (RAB)</th>
                                    <th>Total Realisasi</th>
                                    <th>Sisa Anggaran</th>
                                    <th>Status Realisasi</th>
                                    <th class="text-end">Detail</th>
                                </tr>
                            </thead>
                            <tbody id="accordionHistory">
                                <?php foreach ($grouped_history as $rab_id => $group): ?>
                                    <?php 
                                        $sisa = $group['rab_grand_total'] - $group['total_realized_sum'];
                                        $sisa_class = $sisa < 0 ? 'text-danger' : 'text-success';
                                        $collapseId = 'collapse-' . $rab_id;
                                        
                                        // Determine Aggregate Realization Status
                                        $has_submitted = false;
                                        $has_rejected = false;
                                        $all_approved = true;
                                        
                                        // Check parent RAB status first to handle legacy data or sync issues
                                        $parent_approved = in_array($group['rab_status'], ['realization_approved', 'completed', 'invoice_requested', 'invoiced', 'paid']);
                                        $is_completed = in_array($group['rab_status'], ['completed', 'invoice_requested', 'invoiced', 'paid']);
                                        
                                        if ($parent_approved) {
                                            $all_approved = true;
                                            $has_submitted = false;
                                            $has_rejected = false;
                                        } elseif (!empty($group['items'])) {
                                            foreach ($group['items'] as $item) {
                                                if ($item['status'] == 'submitted') $has_submitted = true;
                                                if ($item['status'] == 'rejected') $has_rejected = true;
                                                if ($item['status'] != 'approved') $all_approved = false;
                                            }
                                        } else {
                                            $all_approved = false; // No items
                                        }

                                        if ($is_completed) {
                                            $realization_status = 'Completed';
                                            $statusBadgeClass = 'bg-primary';
                                        } elseif ($has_submitted) {
                                            $realization_status = 'Waiting Approval';
                                            $statusBadgeClass = 'bg-info';
                                        } elseif ($has_rejected) {
                                            $realization_status = 'Has Rejection';
                                            $statusBadgeClass = 'bg-danger';
                                        } elseif ($all_approved) {
                                            $realization_status = 'All Approved';
                                            $statusBadgeClass = 'bg-success';
                                        } else {
                                            $realization_status = 'In Progress'; // e.g. drafts
                                            $statusBadgeClass = 'bg-secondary';
                                        }
                                    ?>
                                    <!-- Parent Row -->
                                    <tr class="clickable-row">
                                        <td class="fw-bold" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#<?php echo $collapseId; ?>"><?php echo htmlspecialchars($group['rab_number']); ?></td>
                                        <td style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#<?php echo $collapseId; ?>"><?php echo htmlspecialchars($group['project_name']); ?></td>
                                        <td style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#<?php echo $collapseId; ?>"><?php echo htmlspecialchars($group['korlap_name']); ?></td>
                                        <td style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#<?php echo $collapseId; ?>">Rp <?php echo number_format($group['rab_grand_total']); ?></td>
                                        <td style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#<?php echo $collapseId; ?>">Rp <?php echo number_format($group['total_realized_sum']); ?></td>
                                        <td class="fw-bold <?php echo $sisa_class; ?>" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#<?php echo $collapseId; ?>">Rp <?php echo number_format($sisa); ?></td>
                                        <td style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#<?php echo $collapseId; ?>"><span class="badge <?php echo $statusBadgeClass; ?>"><?php echo $realization_status; ?></span></td>
                                        <td class="text-end">
                                            <a href="index.php?page=realization_comparison&rab_id=<?php echo $rab_id; ?>" class="btn btn-sm btn-info text-white me-1" title="Detail Perbandingan">
                                                <i class="fas fa-chart-pie me-1"></i>Detail Budget
                                            </a>
                                            <button class="btn btn-sm btn-outline-info" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $collapseId; ?>">
                                                <i class="fas fa-chevron-down"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <!-- Child Row (Hidden) -->
                                    <tr>
                                        <td colspan="8" class="p-0 border-0">
                                            <div class="collapse bg-light" id="<?php echo $collapseId; ?>" data-bs-parent="#accordionHistory">
                                                <div class="p-3">
                                                    <h6 class="fw-bold mb-2">Detail Realisasi per Tanggal:</h6>
                                                    <table class="table table-sm table-bordered bg-white mb-0" style="font-size: 0.85rem;">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>Tanggal</th>
                                                                <th>Total Realisasi</th>
                                                                <th>PETUGAS MEDIS & LAPANGAN</th>
                                                                <th>TRANSPORTASI & AKOMODASI</th>
                                                                <th>KONSUMSI & LAINNYA</th>
                                                                <th>Status</th>
                                                                <th class="text-end">Aksi</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($group['items'] as $h): ?>
                                                                <?php 
                                                                    $cats = $h['category_totals'] ?? [];
                                                                    $personnel = $cats['personnel'] ?? 0;
                                                                    $trans_acc = ($cats['transport'] ?? 0) + ($cats['accommodation'] ?? 0);
                                                                    $cons_other = ($cats['consumption'] ?? 0) + ($cats['other'] ?? 0) + ($cats['vendor'] ?? 0);
                                                                ?>
                                                                <tr>
                                                                    <td><?php echo date('d M Y', strtotime($h['date'])); ?></td>
                                                                    <td class="fw-bold">Rp <?php echo number_format($h['total_amount']); ?></td>
                                                                    
                                                                    <td><?php echo $personnel > 0 ? number_format($personnel) : '-'; ?></td>
                                                                    <td><?php echo $trans_acc > 0 ? number_format($trans_acc) : '-'; ?></td>
                                                                    <td><?php echo $cons_other > 0 ? number_format($cons_other) : '-'; ?></td>

                                                                    <td>
                                                                        <?php 
                                                                            $displayStatus = $h['status'];
                                                                            // If parent is approved/completed, force display child as approved to match
                                                                            if ($parent_approved) $displayStatus = 'approved';

                                                                            $statusClass = 'bg-secondary';
                                                                            if ($displayStatus == 'submitted') $statusClass = 'bg-info';
                                                                            if ($displayStatus == 'approved') $statusClass = 'bg-success';
                                                                            if ($displayStatus == 'rejected') $statusClass = 'bg-danger';
                                                                        ?>
                                                                        <span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst($displayStatus); ?></span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <?php if ($h['status'] == 'submitted' || $h['status'] == 'draft' || $h['status'] == 'rejected'): ?>
                                                                            <a href="index.php?page=realization_edit&id=<?php echo $h['id']; ?>" class="btn btn-xs btn-primary">
                                                                                <i class="fas fa-pencil-alt"></i> Edit
                                                                            </a>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                     <!-- Pagination -->
                     <?php if (isset($total_pages) && $total_pages > 1): ?>
                    <div class="px-4 py-3 border-top bg-light d-flex justify-content-between align-items-center">
                        <small class="text-muted">Showing <?php echo $total_rows; ?> total entries</small>
                        <nav aria-label="Page navigation">
                            <ul class="pagination pagination-sm mb-0">
                                <!-- Previous Link -->
                                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link border-0 bg-transparent" href="<?php echo ($page <= 1) ? '#' : 'index.php?page=realization_list&p=' . ($page - 1); ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo; Prev</span>
                                    </a>
                                </li>
                                
                                <!-- Page Numbers -->
                                <?php 
                                $start_page = max(1, $page - 2);
                                $end_page = min($total_pages, $page + 2);
                                
                                if ($start_page > 1) {
                                    echo '<li class="page-item"><a class="page-link border-0 bg-transparent" href="index.php?page=realization_list&p=1">1</a></li>';
                                    if ($start_page > 2) {
                                        echo '<li class="page-item disabled"><span class="page-link border-0 bg-transparent">...</span></li>';
                                    }
                                }
                                
                                for ($i = $start_page; $i <= $end_page; $i++): 
                                ?>
                                    <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                        <a class="page-link border-0 <?php echo ($page == $i) ? 'bg-primary text-white rounded-circle' : 'bg-transparent'; ?>" href="index.php?page=realization_list&p=<?php echo $i; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php 
                                if ($end_page < $total_pages) {
                                    if ($end_page < $total_pages - 1) {
                                        echo '<li class="page-item disabled"><span class="page-link border-0 bg-transparent">...</span></li>';
                                    }
                                    echo '<li class="page-item"><a class="page-link border-0 bg-transparent" href="index.php?page=realization_list&p='.$total_pages.'">'.$total_pages.'</a></li>';
                                }
                                ?>

                                <!-- Next Link -->
                                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                    <a class="page-link border-0 bg-transparent" href="<?php echo ($page >= $total_pages) ? '#' : 'index.php?page=realization_list&p=' . ($page + 1); ?>" aria-label="Next">
                                        <span aria-hidden="true">Next &raquo;</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
            <?php else: ?>
            <div class="card shadow-sm">
                <div class="card-body text-center py-5 text-muted">
                    <i class="fas fa-history fa-3x mb-3 text-light-gray"></i>
                    <p>Belum ada riwayat realisasi.</p>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php include '../views/layouts/footer.php'; ?>
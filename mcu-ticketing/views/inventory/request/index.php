<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center page-header-container">
        <div>
            <h1 class="page-header-title">Inventory Request (Korlap)</h1>
            <p class="page-header-subtitle">Kelola dan pantau pengajuan logistik proyek.</p>
        </div>
        <div>
             <a href="index.php?page=inventory_request_create" class="btn btn-primary rounded-pill px-4 shadow-sm">
                <i class="fas fa-plus me-2"></i> Buat Request Baru
             </a>
        </div>
    </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <table id="datatable" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                <thead>
                                    <tr>
                                        <th>No Request</th>
                                        <th>Project</th>
                                        <th>Requester</th>
                                        <th>Tanggal MCU</th>
                                        <th>Requested pada</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    require_once '../helpers/DateHelper.php';
                                    foreach ($requests as $r): ?>
                                    <tr>
                                        <td><?php echo $r['request_number']; ?></td>
                                        <td><?php echo $r['nama_project']; ?></td>
                                        <td><?php echo $r['requester_name']; ?></td>
                                        <td class="fw-bold text-primary"><?php echo DateHelper::formatSmartDateIndonesian($r['tanggal_mcu']); ?></td>
                                        <td><?php echo DateHelper::formatIndonesianDate($r['created_at'], true); ?></td>
                                        <td>
                                            <?php if ($r['status'] == 'SPLIT_SYSTEM' && !empty($r['warehouse_statuses'])): ?>
                                                <div class="d-flex flex-column gap-1">
                                                <?php 
                                                    $statuses = explode('|', $r['warehouse_statuses']);
                                                    $canEdit = true;
                                                    foreach ($statuses as $statusStr):
                                                        [$type, $status] = explode(':', $statusStr);
                                                        
                                                        if ($status != 'PENDING') $canEdit = false;

                                                        // Config
                                                        $badgeClass = 'bg-secondary';
                                                        $icon = 'fa-circle';
                                                        $text = $status;
                                                        
                                                        if ($status == 'PENDING') {
                                                            $badgeClass = 'bg-soft-warning text-warning';
                                                            $icon = 'fa-clock';
                                                            $text = 'Menunggu';
                                                        } elseif ($status == 'IN_PREPARATION') {
                                                            $badgeClass = 'bg-soft-info text-info';
                                                            $icon = 'fa-spinner fa-spin';
                                                            $text = 'Diproses';
                                                        } elseif ($status == 'READY') {
                                                            $badgeClass = 'bg-soft-primary text-primary';
                                                            $icon = 'fa-check-circle';
                                                            $text = 'Siap Diambil';
                                                        } elseif ($status == 'COMPLETED') {
                                                            $badgeClass = 'bg-soft-success text-success';
                                                            $icon = 'fa-check-double';
                                                            $text = 'Selesai';
                                                        }
                                                        
                                                        // Shorten type
                                                        $label = ($type == 'GUDANG_ASET') ? 'Aset' : 'Konsumable';
                                                ?>
                                                    <div class="d-flex align-items-center justify-content-between border rounded p-1" style="max-width: 200px;">
                                                        <span class="text-muted small fw-bold ps-1"><?php echo $label; ?></span>
                                                        <span class="badge <?php echo $badgeClass; ?> rounded-pill">
                                                            <i class="fas <?php echo $icon; ?> me-1"></i> <?php echo $text; ?>
                                                        </span>
                                                    </div>
                                                <?php endforeach; ?>
                                                </div>
                                            <?php else: ?>
                                                <?php 
                                                    $badge = 'secondary';
                                                    if ($r['status'] == 'SUBMITTED') $badge = 'info';
                                                    if ($r['status'] == 'SPLIT_SYSTEM') $badge = 'primary';
                                                    if ($r['status'] == 'COMPLETED') $badge = 'success';
                                                    $canEdit = ($r['status'] == 'SUBMITTED');
                                                ?>
                                                <span class="badge bg-<?php echo $badge; ?>"><?php echo $r['status']; ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="index.php?page=inventory_request_detail&id=<?php echo $r['id']; ?>" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> Detail
                                            </a>
                                            <?php if ($canEdit): ?>
                                            <a href="index.php?page=inventory_request_edit&id=<?php echo $r['id']; ?>" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

</div>

<?php include '../views/layouts/footer.php'; ?>
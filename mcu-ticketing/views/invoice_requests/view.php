<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Detail Invoice Request</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php?page=invoice_requests_index">Invoice Requests</a></li>
        <li class="breadcrumb-item active"><?php echo htmlspecialchars($request['request_number']); ?></li>
    </ol>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-info-circle me-1"></i> Informasi Request
            <span class="float-end badge bg-<?php echo $request['status'] == 'DRAFT' ? 'secondary' : 'primary'; ?>">
                <?php echo $request['status']; ?>
            </span>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">No Request</th>
                            <td>: <?php echo htmlspecialchars($request['request_number']); ?></td>
                        </tr>
                        <tr>
                            <th>Tanggal</th>
                            <td>: <?php echo date('d M Y', strtotime($request['request_date'])); ?></td>
                        </tr>
                        <tr>
                            <th>Sales PIC</th>
                            <td>: <?php echo htmlspecialchars($request['sales_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Company</th>
                            <td>: <?php echo htmlspecialchars($request['client_company']); ?></td>
                        </tr>
                        <tr>
                            <th>PIC Client</th>
                            <td>: <?php echo htmlspecialchars($request['client_pic']); ?> (<?php echo htmlspecialchars($request['client_phone']); ?>)</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">Link NPWP</th>
                            <td>: 
                                <?php if (!empty($request['link_gdrive_npwp'])): ?>
                                    <a href="<?php echo htmlspecialchars($request['link_gdrive_npwp']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-external-link-alt me-1"></i> Lihat NPWP
                                    </a>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Tidak ada</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Link Absensi</th>
                            <td>: 
                                <?php if (!empty($request['link_gdrive_absensi'])): ?>
                                    <a href="<?php echo htmlspecialchars($request['link_gdrive_absensi']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-external-link-alt me-1"></i> Lihat Absensi
                                    </a>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Tidak ada</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Link BA</th>
                            <td>: 
                                <?php if (!empty($request['linked_projects'])): ?>
                                    <div class="d-flex flex-column gap-2">
                                        <?php foreach ($request['linked_projects'] as $proj): ?>
                                            <div class="border rounded p-2">
                                                <div class="fw-bold mb-1"><?php echo htmlspecialchars($proj['nama_project']); ?></div>
                                                <?php 
                                                    $records = isset($proj['ba_records']) ? $proj['ba_records'] : [];
                                                    $hasRecords = !empty($records);
                                                    $schedule = isset($proj['schedule_dates']) ? $proj['schedule_dates'] : [];
                                                ?>
                                                <?php if ($hasRecords): ?>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        <?php foreach ($records as $rec): ?>
                                                            <?php $d = date('d M Y', strtotime($rec['tanggal_mcu'])); ?>
                                                            <?php if ($rec['status'] === 'uploaded' && !empty($rec['file_path'])): ?>
                                                                <a href="index.php?page=download_ba&project_id=<?php echo $proj['project_id']; ?>&date=<?php echo htmlspecialchars($rec['tanggal_mcu']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                    BA - <?php echo $d; ?>
                                                                </a>
                                                            <?php elseif ($rec['status'] === 'cancelled'): ?>
                                                                <span class="badge bg-danger">BA - <?php echo $d; ?> (Cancelled)</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-warning text-dark">BA - <?php echo $d; ?> (Pending)</span>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php elseif (!empty($schedule)): ?>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        <?php foreach ($schedule as $sd): ?>
                                                            <?php $d = date('d M Y', strtotime($sd)); ?>
                                                            <span class="badge bg-warning text-dark">BA - <?php echo $d; ?> (Pending)</span>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Link SPH</th>
                            <td>: 
                                <?php if (!empty($request['linked_projects'])): ?>
                                    <?php foreach ($request['linked_projects'] as $proj): ?>
                                        <?php if (!empty($proj['sph_file'])): ?>
                                            <span class="d-inline-block mb-1 me-2">
                                                <?php if (preg_match('#^https?://#', $proj['sph_file'])): ?>
                                                     <a href="<?php echo htmlspecialchars($proj['sph_file']); ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                         <i class="fas fa-file-pdf me-1"></i> Lihat SPH <?php echo count($request['linked_projects']) > 1 ? '(' . htmlspecialchars($proj['nama_project']) . ')' : ''; ?>
                                                     </a>
                                                <?php else: ?>
                                                     <a href="index.php?page=download_sph&project_id=<?php echo $proj['project_id']; ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                         <i class="fas fa-file-pdf me-1"></i> Lihat SPH <?php echo count($request['linked_projects']) > 1 ? '(' . htmlspecialchars($proj['nama_project']) . ')' : ''; ?>
                                                     </a>
                                                <?php endif; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="d-inline-block mb-1 text-muted me-2">
                                                - <?php echo count($request['linked_projects']) > 1 ? '(' . htmlspecialchars($proj['nama_project']) . ')' : ''; ?>
                                            </span>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th width="30%">Terms</th>
                            <td>: <?php echo nl2br(htmlspecialchars($request['invoice_terms'])); ?></td>
                        </tr>
                        <tr>
                            <th>Alamat Kirim</th>
                            <td>: <?php echo nl2br(htmlspecialchars($request['shipping_address'])); ?></td>
                        </tr>
                        <tr>
                            <th>Notes</th>
                            <td>: <?php echo nl2br(htmlspecialchars($request['notes'])); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <h5 class="mt-4">Status Approval</h5>
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row text-center">
                        <!-- Sales Approval -->
                        <div class="col-md-4">
                            <div class="card h-100 <?php echo ($request['status'] == 'APPROVED_SALES' || $request['status'] == 'APPROVED_SPV' || $request['status'] == 'APPROVED_MANAGER' || $request['status'] == 'PROCESSED') ? 'border-success' : ''; ?>">
                                <div class="card-body">
                                    <h6 class="card-title">Sales Approval</h6>
                                    <?php if ($request['approver_sales_name']): ?>
                                        <p class="text-success mb-0"><i class="fas fa-check-circle"></i> Disetujui</p>
                                        <small class="text-muted">Oleh: <?php echo htmlspecialchars($request['approver_sales_name']); ?></small>
                                    <?php else: ?>
                                        <p class="text-muted mb-0"><i class="fas fa-clock"></i> Pending</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- SPV Approval -->
                        <div class="col-md-4">
                            <div class="card h-100 <?php echo ($request['status'] == 'APPROVED_SPV' || $request['status'] == 'APPROVED_MANAGER' || $request['status'] == 'PROCESSED') ? 'border-success' : ''; ?>">
                                <div class="card-body">
                                    <h6 class="card-title">Supervisor Approval</h6>
                                    <?php if ($request['approver_spv_name']): ?>
                                        <p class="text-success mb-0"><i class="fas fa-check-circle"></i> Disetujui</p>
                                        <small class="text-muted">Oleh: <?php echo htmlspecialchars($request['approver_spv_name']); ?></small>
                                    <?php else: ?>
                                        <p class="text-muted mb-0"><i class="fas fa-clock"></i> Pending</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Manager Approval -->
                        <div class="col-md-4">
                            <div class="card h-100 <?php echo ($request['status'] == 'APPROVED_MANAGER' || $request['status'] == 'PROCESSED') ? 'border-success' : ''; ?>">
                                <div class="card-body">
                                    <h6 class="card-title">Manager Approval</h6>
                                    <?php if ($request['approver_mgr_name']): ?>
                                        <p class="text-success mb-0"><i class="fas fa-check-circle"></i> Disetujui</p>
                                        <small class="text-muted">Oleh: <?php echo htmlspecialchars($request['approver_mgr_name']); ?></small>
                                    <?php else: ?>
                                        <p class="text-muted mb-0"><i class="fas fa-clock"></i> Pending</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <h5 class="mt-4">Detail Item</h5>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Deskripsi</th>
                            <th>Harga</th>
                            <th>Qty</th>
                            <th>Total</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $grand_total = 0;
                        foreach ($request['items'] as $item): 
                            $total = $item['price'] * $item['qty'];
                            $grand_total += $total;
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['item_description']); ?></td>
                                <td class="text-end">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                                <td class="text-center"><?php echo $item['qty']; ?></td>
                                <td class="text-end">Rp <?php echo number_format($total, 0, ',', '.'); ?></td>
                                <td><?php echo htmlspecialchars($item['remarks']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td colspan="4" class="text-end fw-bold">Grand Total</td>
                            <td class="text-end fw-bold">Rp <?php echo number_format($grand_total, 0, ',', '.'); ?></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <a href="index.php?page=invoice_requests_print&id=<?php echo $request['id']; ?>" target="_blank" class="btn btn-secondary me-2">
                    <i class="fas fa-print"></i> Generate Document
                </a>

                <?php if ($request['status'] == 'DRAFT' && ($_SESSION['role'] == 'admin_sales' || $_SESSION['role'] == 'superadmin')): ?>
                    <form action="index.php?page=invoice_requests_store" method="POST" class="d-inline">
                        <input type="hidden" name="action" value="submit">
                        <input type="hidden" name="id" value="<?php echo $request['id']; ?>">
                        <!-- Note: The controller store() doesn't handle submit only by ID yet. 
                             It expects POST data. I should implement a separate submit action in Controller 
                             or update the route.
                             Controller has `submit($id)`. I need to route to it.
                             I'll use a direct link or form to a new action.
                        -->
                    </form>
                    <!-- Correction: Use a link to a specific action -->
                    <a href="index.php?page=invoice_requests_submit&id=<?php echo $request['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" 
                       class="btn btn-warning swal-confirm-submit">
                       <i class="fas fa-paper-plane"></i> Submit Request
                    </a>
                <?php endif; ?>

                <?php 
                $showApproveBtn = false;
                $approveLabel = "Approve Request";

                if ($request['status'] == 'SUBMITTED' && ($_SESSION['role'] == 'sales' || $_SESSION['role'] == 'admin_sales' || $_SESSION['role'] == 'superadmin')) {
                    if ($_SESSION['role'] == 'superadmin' || $_SESSION['role'] == 'admin_sales' || ($_SESSION['role'] == 'sales' && $request['pic_sales_id'] == $_SESSION['user_id'])) {
                        $showApproveBtn = true;
                        $approveLabel = "Approve as Sales";
                    }
                } elseif ($request['status'] == 'APPROVED_SALES' && ($_SESSION['role'] == 'sales_support_supervisor' || $_SESSION['role'] == 'superadmin')) {
                    $showApproveBtn = true;
                    $approveLabel = "Approve as Supervisor";
                } elseif ($request['status'] == 'APPROVED_SPV' && ($_SESSION['role'] == 'sales_performance_manager' || $_SESSION['role'] == 'superadmin')) {
                    $showApproveBtn = true;
                    $approveLabel = "Approve as Manager";
                }
                ?>

                <?php if ($showApproveBtn): ?>
                    <a href="index.php?page=invoice_requests_approve&id=<?php echo $request['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" 
                       class="btn btn-success swal-confirm-approve"
                       data-label="<?php echo $approveLabel; ?>">
                       <i class="fas fa-check"></i> <?php echo $approveLabel; ?>
                    </a>
                <?php endif; ?>

                <?php if ($request['status'] != 'DRAFT'): ?>
                    <div class="alert alert-info">
                        Request ini sudah disubmit. Cek menu <strong>Invoice Processing</strong> untuk status selanjutnya.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.swal-confirm-submit').on('click', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        
        Swal.fire({
            title: 'Konfirmasi Submit',
            text: 'Apakah Anda yakin ingin submit request ini? Draft invoice akan dibuat otomatis.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#204EAB',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Submit!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            borderRadius: '15px',
            showClass: {
                popup: 'animate__animated animate__fadeInDown'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutUp'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    });

    $('.swal-confirm-approve').on('click', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        const label = $(this).data('label');
        
        Swal.fire({
            title: 'Konfirmasi Approval',
            text: 'Apakah Anda yakin ingin menyetujui request ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Approve!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            borderRadius: '15px'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    });
});
</script>

<?php include '../views/layouts/footer.php'; ?>

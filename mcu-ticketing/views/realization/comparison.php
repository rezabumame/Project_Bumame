<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<div class="container-fluid px-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fs-4 mb-0 fw-bold text-primary">
                        <i class="fas fa-balance-scale me-2"></i>Perbandingan Realisasi vs Anggaran
                    </h3>
                    <p class="text-muted mb-0">
                        <?php echo htmlspecialchars($rab['rab_number']); ?> - <?php echo htmlspecialchars($rab['nama_project'] ?? ''); ?>
                    </p>
                </div>
                <a href="index.php?page=realization_list" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
            </div>

            <?php if (isset($_GET['msg'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($_GET['msg']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['err'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($_GET['err']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Action Buttons -->
            <?php if ($rab['status'] == 'advance_paid' || $rab['status'] == 'approved'): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0 fw-bold text-primary"><i class="fas fa-info-circle me-2"></i>Status Realisasi: Draft / Ongoing</h6>
                            <p class="text-muted small mb-0">Pastikan semua data realisasi sudah benar sebelum mengajukan approval.</p>
                        </div>
                        <form id="form-submit-realization" action="index.php?page=realization_submit" method="POST">
                            <input type="hidden" name="rab_id" value="<?php echo $rab['id']; ?>">
                            <button type="button" id="btn-submit-realization" class="btn btn-success text-white">
                                <i class="fas fa-paper-plane me-2"></i>Submit Realisasi (Need Approval)
                            </button>
                        </form>
                    </div>
                </div>
            <?php elseif ($rab['status'] == 'need_approval_realization'): ?>
                <div class="alert alert-warning d-flex align-items-center mb-4 justify-content-between" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-clock fa-2x me-3"></i>
                        <div>
                            <h6 class="alert-heading fw-bold mb-1">Menunggu Approval Manager</h6>
                            <p class="mb-0">Realisasi ini sedang menunggu persetujuan dari Manager Ops.</p>
                        </div>
                    </div>
                    
                    <?php if ($_SESSION['role'] == 'manager_ops'): ?>
                    <div>
                        <button type="button" onclick="confirmApproval('approve')" class="btn btn-success me-2">
                            <i class="fas fa-check me-2"></i>Approve
                        </button>
                        <!-- Optional: Add Reject button if needed -->
                    </div>
                    <?php endif; ?>
                </div>

                <?php if ($_SESSION['role'] == 'manager_ops'): ?>
                <form id="form-approve-realization" action="index.php?page=realization_approve" method="POST" style="display: none;">
                    <input type="hidden" name="rab_id" value="<?php echo $rab['id']; ?>">
                    <input type="hidden" name="action" value="approve">
                </form>
                
                <script>
                function confirmApproval(action) {
                    Swal.fire({
                        title: 'Approve Realisasi?',
                        text: 'Anda yakin ingin menyetujui realisasi ini? Status akan berubah menjadi Realization Approved.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#198754',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, Approve!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById('form-approve-realization').submit();
                        }
                    });
                }
                </script>
                <?php endif; ?>
            <?php elseif ($rab['status'] == 'realization_approved' || $rab['status'] == 'completed'): ?>
                <div class="alert alert-success d-flex align-items-center mb-4 justify-content-between" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle fa-2x me-3"></i>
                        <div>
                            <h6 class="alert-heading fw-bold mb-1">Realisasi Disetujui</h6>
                            <p class="mb-0">
                                <?php if ($rab['status'] == 'completed'): ?>
                                    Proses penyelesaian keuangan telah selesai.
                                <?php else: ?>
                                    Silakan unduh LPUM dan upload bukti penyelesaian keuangan (Pengembalian/Kekurangan).
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center gap-2">
                        <a href="index.php?page=realization_export_lpum&rab_id=<?php echo $rab['id']; ?>" target="_blank" class="btn btn-light text-success fw-bold">
                            <i class="fas fa-file-pdf me-2"></i>Download LPUM
                        </a>
                        <?php if (!empty($rab['transfer_proof_path'])): ?>
                            <a href="<?php echo htmlspecialchars($rab['transfer_proof_path']); ?>" target="_blank" class="btn btn-outline-light text-info fw-bold me-2">
                                <i class="fas fa-money-check-alt me-2"></i>Bukti Advance
                            </a>
                        <?php endif; ?>
                        <?php if ($rab['status'] == 'completed' && !empty($rab['settlement_proof_path'])): ?>
                            <a href="<?php echo htmlspecialchars($rab['settlement_proof_path']); ?>" target="_blank" class="btn btn-outline-light text-success fw-bold">
                                <i class="fas fa-file-invoice-dollar me-2"></i>Bukti Selisih
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if ($rab['status'] == 'realization_approved' && $_SESSION['role'] == 'korlap'): ?>
                    <div class="card shadow-sm mb-4 border-start border-4 border-warning">
                        <div class="card-body">
                            <h5 class="fw-bold mb-3">Penyelesaian Keuangan</h5>
                            <p class="text-muted small">
                                Berdasarkan selisih realisasi, silakan lakukan penyelesaian keuangan dan upload bukti transaksi.
                                <br>Jika <strong>Minus (Over Budget)</strong>: Finance transfer ke Korlap.
                                <br>Jika <strong>Plus (Under Budget)</strong>: Korlap transfer pengembalian ke Finance.
                            </p>
                            
                            <form action="index.php?page=realization_upload_settlement" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="rab_id" value="<?php echo $rab['id']; ?>">
                                <div class="mb-3">
                                    <label for="transfer_proof" class="form-label">Upload Bukti Transaksi</label>
                                    <input class="form-control" type="file" id="transfer_proof" name="transfer_proof" required accept=".jpg,.jpeg,.png,.pdf">
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-upload me-2"></i>Upload & Selesaikan
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Summary Cards -->
            <div class="row row-cols-1 row-cols-md-5 g-3 mb-4">
                <?php 
                $total_rab = 0;
                $total_realized = 0;
                foreach ($category_summary as $cat_sum) {
                    $total_rab += $cat_sum['rab_total'];
                    $total_realized += $cat_sum['realized_total'];
                }
                $total_variance = $total_rab - $total_realized;
                $variance_class = $total_variance < 0 ? 'text-danger' : 'text-success';
                
                // Calculate percentage based on Cost Value (Anggaran Ops)
                $anggaran_ops = $rab['cost_value'] ?? 0;
                $percentage_usage = ($anggaran_ops > 0) ? ($total_realized / $anggaran_ops) * 100 : 0;
                
                // Determine color for percentage
                $percentage_class = 'text-success';
                if ($percentage_usage > 100) {
                    $percentage_class = 'text-danger';
                } elseif ($percentage_usage > 90) {
                    $percentage_class = 'text-warning';
                }
                ?>
                
                <div class="col">
                    <div class="card shadow-sm border-start border-4 border-secondary h-100">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Anggaran Ops</h6>
                            <h3 class="fw-bold text-secondary mb-0">Rp <?php echo number_format($anggaran_ops); ?></h3>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="card shadow-sm border-start border-4 border-primary h-100">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Pengajuan RAB</h6>
                            <h3 class="fw-bold text-primary mb-0">Rp <?php echo number_format($total_rab); ?></h3>
                        </div>
                    </div>
                </div>
                
                <div class="col">
                    <div class="card shadow-sm border-start border-4 border-info h-100">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Total Realisasi (All Day)</h6>
                            <h3 class="fw-bold text-info mb-0">Rp <?php echo number_format($total_realized); ?></h3>
                        </div>
                    </div>
                </div>
                
                <div class="col">
                    <div class="card shadow-sm border-start border-4 <?php echo $total_variance < 0 ? 'border-danger' : 'border-success'; ?> h-100">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Selisih (Variance)</h6>
                            <h3 class="fw-bold <?php echo $variance_class; ?> mb-0">
                                Rp <?php echo number_format($total_variance); ?>
                            </h3>
                            <small class="text-muted"><?php echo $total_variance < 0 ? 'Over Budget' : 'Under Budget'; ?></small>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="card shadow-sm border-start border-4 <?php echo ($percentage_usage > 100) ? 'border-danger' : 'border-success'; ?> h-100">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">% Realisasi vs Ops</h6>
                            <h3 class="fw-bold <?php echo $percentage_class; ?> mb-0">
                                <?php echo number_format($percentage_usage, 2); ?>%
                            </h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Accommodation Reconciliation Section (Automatic Check) -->
            <?php
            $acc_diff = $accommodation_advance_total - $accommodation_realized_total;
            $acc_status_class = 'bg-success';
            $acc_status_text = 'MATCH (Sesuai)';
            $acc_action_text = 'Tidak ada aksi diperlukan';
            
            if ($acc_diff > 0) {
                // Advance > Realized -> Sisa Uang Muka (Kembali ke Finance)
                $acc_status_class = 'bg-warning text-dark';
                $acc_status_text = 'LEBIH BAYAR (Sisa Uang Muka)';
                $acc_action_text = 'Korlap wajib mengembalikan sisa Rp ' . number_format($acc_diff);
            } elseif ($acc_diff < 0) {
                // Advance < Realized -> Kurang Bayar (Finance transfer ke Korlap)
                $acc_status_class = 'bg-danger';
                $acc_status_text = 'KURANG BAYAR (Reimbursement)';
                $acc_action_text = 'Finance wajib transfer kekurangan Rp ' . number_format(abs($acc_diff));
            }
            ?>
            <div class="card shadow-sm mb-4 border-primary">
                <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-money-bill-wave me-2"></i>Rekonsiliasi Akomodasi (Actual Checked vs Released)</h5>
                    <span class="badge bg-white text-primary fw-bold">Otomatis</span>
                </div>
                <div class="card-body">
                    <div class="row text-center align-items-center">
                        <div class="col-md-3">
                            <h6 class="text-muted mb-1">Total Uang Muka (Released)</h6>
                            <h4 class="fw-bold text-primary">Rp <?php echo number_format($accommodation_advance_total); ?></h4>
                        </div>
                        <div class="col-md-1">
                            <i class="fas fa-minus fa-2x text-muted"></i>
                        </div>
                        <div class="col-md-3">
                            <h6 class="text-muted mb-1">Total Realisasi (Checked)</h6>
                            <h4 class="fw-bold text-info">Rp <?php echo number_format($accommodation_realized_total); ?></h4>
                        </div>
                        <div class="col-md-1">
                            <i class="fas fa-equals fa-2x text-muted"></i>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded <?php echo $acc_status_class; ?> bg-opacity-25 border">
                                <h6 class="mb-1 fw-bold">Selisih: Rp <?php echo number_format($acc_diff); ?></h6>
                                <div class="badge <?php echo $acc_status_class; ?> mb-2"><?php echo $acc_status_text; ?></div>
                                <p class="small mb-0 fst-italic"><?php echo $acc_action_text; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Comparison Table -->
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">Detail Per Kategori</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0 align-middle">
                            <thead class="table-light text-center">
                                <tr>
                                    <th rowspan="2" class="align-middle">Kategori / Item</th>
                                    <th colspan="2">Pengajuan (RAB)</th>
                                    <th colspan="2">Realisasi (All Day)</th>
                                    <th rowspan="2" class="align-middle">Selisih (Rp)</th>
                                    <th rowspan="2" class="align-middle">Status</th>
                                </tr>
                                <tr>
                                    <th>Qty</th>
                                    <th>Total (Rp)</th>
                                    <th>Qty</th>
                                    <th>Total (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $cat_map = [
                                    'personnel' => 'PETUGAS MEDIS & LAPANGAN',
                                    'vendor' => 'VENDOR',
                                    'transport' => 'TRANSPORTASI & AKOMODASI',
                                    'consumption' => 'KONSUMSI & LAINNYA'
                                ];
                                foreach ($comparison_data as $category => $items): 
                                    $display_category = isset($cat_map[$category]) ? $cat_map[$category] : ucfirst($category);
                                ?>
                                    <!-- Category Header -->
                                    <tr class="table-secondary">
                                        <td colspan="5" class="fw-bold text-uppercase ps-3">
                                            <i class="fas fa-layer-group me-2"></i><?php echo $display_category; ?>
                                        </td>
                                        <td class="fw-bold text-end">
                                            <?php 
                                            $cat_var = $category_summary[$category]['variance'];
                                            $cat_class = $cat_var < 0 ? 'text-danger' : 'text-success';
                                            echo '<span class="' . $cat_class . '">Rp ' . number_format($cat_var) . '</span>';
                                            ?>
                                        </td>
                                        <td class="text-center fw-bold">
                                            <?php echo $cat_var < 0 ? '<span class="badge bg-danger">OVER</span>' : '<span class="badge bg-success">OK</span>'; ?>
                                        </td>
                                    </tr>

                                    <!-- Items -->
                                    <?php foreach ($items as $item): ?>
                                        <?php 
                                            $item_var = $item['rab_total'] - $item['realized_total'];
                                            $item_class = $item_var < 0 ? 'text-danger' : 'text-success';
                                        ?>
                                        <tr>
                                            <td class="ps-4">
                                                <?php echo htmlspecialchars($item['item_name']); ?>
                                                <?php if (!empty($item['realized_details']) && count($item['realized_details']) > 1 || (count($item['realized_details']) == 1 && $item['realized_details'][0]['item_name'] != $item['item_name'])): ?>
                                                    <a class="text-decoration-none ms-2" data-bs-toggle="collapse" href="#collapse-<?php echo md5($item['item_name']); ?>" role="button" aria-expanded="false" aria-controls="collapse-<?php echo md5($item['item_name']); ?>">
                                                        <i class="fas fa-chevron-down text-primary small"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center"><?php echo number_format($item['rab_qty']); ?></td>
                                            <td class="text-end text-muted">Rp <?php echo number_format($item['rab_total']); ?></td>
                                            <td class="text-center"><?php echo number_format($item['realized_qty']); ?></td>
                                            <td class="text-end fw-bold">Rp <?php echo number_format($item['realized_total']); ?></td>
                                            <td class="text-end fw-bold <?php echo $item_class; ?>">
                                                Rp <?php echo number_format($item_var); ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($item_var < 0): ?>
                                                    <i class="fas fa-exclamation-circle text-danger" title="Over Budget"></i>
                                                <?php elseif ($item_var > 0): ?>
                                                    <i class="fas fa-check-circle text-success" title="Under Budget"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-minus-circle text-muted" title="Exact"></i>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php if (!empty($item['realized_details']) && (count($item['realized_details']) > 1 || (count($item['realized_details']) == 1 && $item['realized_details'][0]['item_name'] != $item['item_name']))): ?>
                                            <?php foreach ($item['realized_details'] as $detail): ?>
                                            <tr class="collapse show bg-light small text-muted" id="collapse-<?php echo md5($item['item_name']); ?>">
                                                <td class="ps-5">
                                                    <i class="fas fa-angle-right me-2"></i><?php echo htmlspecialchars($detail['item_name']); ?>
                                                    <?php if (!empty($detail['notes'])): ?>
                                                        <br><span class="fst-italic text-info ms-4"><i class="fas fa-sticky-note me-1"></i><?php echo htmlspecialchars($detail['notes']); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center"> - </td>
                                                <td class="text-end"> - </td>
                                                <td class="text-center"><?php echo number_format($detail['qty']); ?></td>
                                                <td class="text-end">Rp <?php echo number_format($detail['total']); ?></td>
                                                <td colspan="2"></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td class="text-end">GRAND TOTAL</td>
                                    <td></td>
                                    <td class="text-end">Rp <?php echo number_format($total_rab); ?></td>
                                    <td></td>
                                    <td class="text-end">Rp <?php echo number_format($total_realized); ?></td>
                                    <td class="text-end <?php echo $variance_class; ?>">Rp <?php echo number_format($total_variance); ?></td>
                                    <td class="text-center">
                                        <?php echo $total_variance < 0 ? 'OVER BUDGET' : 'UNDER BUDGET'; ?>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const submitBtn = document.getElementById('btn-submit-realization');
            if (submitBtn) {
                submitBtn.addEventListener('click', function() {
                    Swal.fire({
                        title: 'Ajukan Realisasi?',
                        text: "Data tidak dapat diubah setelah diajukan. Pastikan semua data sudah benar.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#198754',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, Ajukan!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById('form-submit-realization').submit();
                        }
                    });
                });
            }
        });
    </script>

<?php include '../views/layouts/footer.php'; ?>
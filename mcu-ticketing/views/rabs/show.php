<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<!-- Custom Styles for Medical Look -->
<style>
    :root {
        --medical-primary: #204EAB; /* Primary Blue from Bumame */
        --medical-light: #F0F4FA;
        --medical-success: #28a745;
        --medical-danger: #dc3545;
        --medical-border: #e2e8f0;
    }
    
    .bg-medical-light { background-color: var(--medical-light); }
    .text-medical-primary { color: var(--medical-primary); }
    
    .card {
        border: 1px solid var(--medical-border);
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }
    
    .card-header {
        background-color: white;
        border-bottom: 1px solid var(--medical-border);
        font-weight: 600;
        color: var(--medical-primary);
        border-radius: 12px 12px 0 0 !important;
        padding: 1.25rem;
    }

    .table thead th {
        background-color: #f8f9fa;
        color: #6c757d;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        border-bottom: 2px solid var(--medical-border);
    }

    .group-header {
        background-color: #e9ecef;
        font-weight: 700;
        color: #495057;
    }

    .subtotal-row {
        background-color: #f8f9fa;
        font-weight: 600;
    }

    .grand-total-section {
        background-color: #d1e7dd;
        border: 1px solid #badbcc;
        border-radius: 12px;
        padding: 1.5rem;
        margin-top: 2rem;
    }

    .badge-lg {
        font-size: 0.9rem;
        padding: 0.5em 1em;
    }

    /* Sticky Actions */
    .sticky-actions {
        position: sticky;
        top: 0;
        z-index: 1020;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-bottom: 1px solid var(--medical-border);
        padding: 1rem 0;
        margin-bottom: 2rem;
    }
</style>

<div class="container-fluid px-0">
    <!-- Sticky Header & Actions -->
    <div class="sticky-actions px-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="index.php?page=rabs_list">RAB</a></li>
                        <li class="breadcrumb-item"><a href="#">Detail</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($rab['rab_number']); ?></li>
                    </ol>
                </nav>
                <div class="d-flex align-items-center gap-3">
                    <h2 class="h4 mb-0 fw-bold text-medical-primary">Detail RAB</h2>
                    <?php 
                        $status_colors = [
                            'draft' => 'secondary',
                            'need_approval_manager' => 'warning',
                            'need_approval_head' => 'info',
                            'need_approval_ceo' => 'primary',
                            'approved' => 'success',
                            'rejected' => 'danger',
                            'cancelled' => 'dark',
                            'submitted_to_finance' => 'info',
                            'advance_paid' => 'teal'
                        ];
                        $bg_color = $status_colors[$rab['status']] ?? 'secondary';
                    ?>
                    <span class="badge bg-<?php echo $bg_color; ?> rounded-pill badge-lg">
                        <?php echo ucwords(str_replace('_', ' ', $rab['status'])); ?>
                    </span>
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <a href="index.php?page=rabs_list" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
                
                <?php if (($rab['status'] == 'draft' || $rab['status'] == 'need_approval_manager') && ($_SESSION['user_id'] == $rab['created_by'] || $_SESSION['role'] == 'admin_ops')): ?>
                    <a href="index.php?page=rabs_edit&id=<?php echo $rab['id']; ?>" class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i>Edit RAB
                    </a>
                <?php endif; ?>

                <?php if (!in_array($rab['status'], ['need_approval_manager', 'need_approval_head', 'need_approval_ceo'])): ?>
                    <a href="index.php?page=rabs_export_pdf&id=<?php echo $rab['id']; ?>" target="_blank" class="btn btn-primary">
                        <i class="fas fa-file-pdf me-2"></i>Export PDF
                    </a>
                <?php endif; ?>

                <?php 
                // Detection for Consumption Petugas
                $has_consumption_petugas = false;
                foreach ($items as $item) {
                    if ($item['category'] == 'consumption' && (stripos($item['item_name'], 'Makan') !== false || stripos($item['item_name'], 'Snack') !== false || stripos($item['item_name'], 'Petugas') !== false)) {
                        $has_consumption_petugas = true;
                        break;
                    }
                }
                ?>

                <?php if ($rab['status'] == 'approved' && ($_SESSION['role'] == 'korlap' || $_SESSION['role'] == 'admin_ops')): ?>
                    <button type="button" class="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#submitFinanceModal" id="submitFinanceBtn" 
                        <?php echo ($has_consumption_petugas && $_SESSION['role'] == 'korlap') ? 'disabled title="Please click Open Lark first"' : ''; ?>>
                        <i class="fas fa-paper-plane me-2"></i>Submit to Finance
                    </button>
                <?php endif; ?>

                <?php 
                // Special Button for Korlap when consumption is needed
                if ($has_consumption_petugas && $_SESSION['role'] == 'korlap'): ?>
                    <?php if (in_array($rab['status'], ['draft', 'rejected', 'need_approval_manager', 'need_approval_head', 'need_approval_ceo', 'approved'])): ?>
                        <button type="button" class="btn btn-info text-white" onclick="showLarkTemplate()">
                            <i class="fas fa-file-alt me-2"></i>Template Lark
                        </button>
                        <a href="<?php echo $lark_link; ?>" target="_blank" class="btn btn-success" onclick="markLarkClicked()">
                            <i class="fas fa-external-link-alt me-2"></i>Open Lark
                        </a>
                    <?php endif; ?>

                    <?php if (in_array($rab['status'], ['draft', 'rejected', 'need_approval_manager', 'need_approval_head', 'need_approval_ceo'])): ?>
                        <button type="button" class="btn btn-primary" id="autoApproveSubmitBtn" onclick="autoApproveAndSubmit()" disabled title="Please click Open Lark first">
                            <i class="fas fa-magic me-2"></i>Submit to Finance (Auto Approve)
                        </button>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($rab['status'] == 'submitted_to_finance' && $_SESSION['role'] == 'finance'): ?>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#advancePaidModal">
                        <i class="fas fa-money-bill-wave me-2"></i>Advance Paid
                    </button>
                <?php endif; ?>

                <!-- Approval Actions -->
                <?php 
                    $can_approve = false;
                    $can_reject = false;
                    
                    if ($_SESSION['role'] == 'manager_ops' && $rab['status'] == 'need_approval_manager') {
                        $can_approve = true;
                        $can_reject = true;
                    } elseif ($_SESSION['role'] == 'head_ops' && $rab['status'] == 'need_approval_head') {
                        $can_approve = true;
                        $can_reject = true;
                    } elseif ($_SESSION['role'] == 'ceo' && $rab['status'] == 'need_approval_ceo') {
                        $can_approve = true;
                        $can_reject = true;
                    }
                ?>
                
                <?php if ($can_reject): ?>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="fas fa-times me-2"></i>Reject / Send Back
                    </button>
                <?php endif; ?>
                
                <?php if ($can_approve): ?>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">
                        <i class="fas fa-check me-2"></i><?php echo $_SESSION['role'] == 'ceo' ? 'Approve RAB' : 'Approve RAB'; ?>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="px-4 pb-5">
        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
                <i class="fas fa-info-circle me-2"></i><?php echo htmlspecialchars($_GET['msg']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($rab['rejection_reason'])): ?>
            <div class="alert alert-danger mb-4">
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-history me-2 fs-5"></i>
                    <h6 class="fw-bold mb-0">Riwayat Catatan Revisi</h6>
                </div>
                <div class="bg-white rounded p-3 border border-danger-subtle">
                    <?php 
                        $reasons = explode('||', $rab['rejection_reason']);
                    ?>
                    <ul class="list-unstyled mb-0">
                        <?php foreach($reasons as $index => $reason): ?>
                            <li class="mb-2 pb-2 <?php echo $index < count($reasons) - 1 ? 'border-bottom' : ''; ?>">
                                <i class="fas fa-comment-dots text-danger me-2"></i>
                                <?php echo htmlspecialchars($reason); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Info Project Card -->
            <div class="col-12">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-info-circle me-2"></i>Info Project</span>
                        <button class="btn btn-sm btn-link text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target="#infoCollapse">
                            <i class="fas fa-chevron-up"></i>
                        </button>
                    </div>
                    <div class="collapse show" id="infoCollapse">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 border-end">
                                    <div class="mb-3 row">
                                        <label class="col-sm-4 text-muted">No. RAB</label>
                                        <div class="col-sm-8 fw-bold text-medical-primary"><?php echo htmlspecialchars($rab['rab_number']); ?></div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label class="col-sm-4 text-muted">Project</label>
                                        <div class="col-sm-8 fw-bold"><?php echo htmlspecialchars($rab['nama_project']); ?></div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label class="col-sm-4 text-muted">Sales</label>
                                        <div class="col-sm-8 fw-bold text-primary">
                                            <i class="fas fa-user-tie me-2"></i><?php echo htmlspecialchars($project_info['sales_name'] ?? '-'); ?>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label class="col-sm-4 text-muted">Lokasi MCU</label>
                                        <div class="col-sm-8">
                                            <i class="fas fa-map-marker-alt text-danger me-2"></i>
                                            <?php echo ucwords(str_replace('_', ' ', $rab['location_type'])); ?>
                                            <?php 
                                                $display_address = !empty($project_info['alamat']) ? $project_info['alamat'] : ($rab['project_location'] ?? '');
                                            ?>
                                            <?php if(!empty($display_address)): ?>
                                                <div class="small text-muted mt-1">
                                                    <i class="fas fa-building me-1"></i>
                                                    <?php echo htmlspecialchars($display_address); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label class="col-sm-4 text-muted">Tanggal MCU</label>
                                        <div class="col-sm-8">
                                            <i class="fas fa-calendar-alt text-primary me-2"></i>
                                            <?php 
                                                echo DateHelper::formatSmartDateIndonesian($rab['selected_dates']);
                                            ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Finance Info -->
                                    <?php if (in_array($rab['status'], ['advance_paid', 'submitted_to_finance', 'need_approval_realization', 'realization_approved', 'completed'])): ?>
                                    <div class="mt-4 pt-3 border-top">
                                        <h6 class="text-muted mb-3 text-uppercase small fw-bold">Informasi Pembayaran & Penyelesaian</h6>
                                        <div class="row g-3">
                                            <?php if (!empty($rab['transfer_proof_path'])): ?>
                                            <div class="col-md-6">
                                                <div class="alert alert-success border-0 mb-0 h-100">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <i class="fas fa-check-circle text-success me-2"></i>
                                                        <strong class="text-success small text-uppercase">Bukti Advance Bayar</strong>
                                                    </div>
                                                    <div class="small text-muted mb-2">
                                                        <i class="fas fa-clock me-1"></i> <?php echo !empty($rab['finance_paid_at']) ? DateHelper::formatIndonesianDate($rab['finance_paid_at']) : '-'; ?>
                                                    </div>
                                                    <a href="<?php echo htmlspecialchars($rab['transfer_proof_path']); ?>" target="_blank" class="btn btn-sm btn-success w-100">
                                                        <i class="fas fa-receipt me-1"></i> Lihat Bukti Advance
                                                    </a>
                                                </div>
                                            </div>
                                            <?php endif; ?>

                                            <?php if (!empty($rab['settlement_proof_path'])): ?>
                                            <div class="col-md-6">
                                                <div class="alert alert-info border-0 mb-0 h-100">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <i class="fas fa-file-invoice-dollar text-info me-2"></i>
                                                        <strong class="text-info small text-uppercase">Bukti Selisih (Settlement)</strong>
                                                    </div>
                                                    <div class="small text-muted mb-2">
                                                        <i class="fas fa-check-double me-1"></i> Status: Completed
                                                    </div>
                                                    <a href="<?php echo htmlspecialchars($rab['settlement_proof_path']); ?>" target="_blank" class="btn btn-sm btn-info text-white w-100">
                                                        <i class="fas fa-file-download me-1"></i> Lihat Bukti Selisih
                                                    </a>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        </div>

                                        <?php if (!empty($rab['finance_note'])): ?>
                                        <div class="bg-light p-2 rounded border mt-3 small text-muted">
                                            <i class="fas fa-comment-alt me-1"></i> <strong>Catatan Finance:</strong> <?php echo htmlspecialchars($rab['finance_note']); ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6 ps-md-4">
                                    <div class="mb-3 row">
                                        <label class="col-sm-4 text-muted">Dibuat Oleh</label>
                                        <div class="col-sm-8">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width:32px; height:32px; font-size:12px;">
                                                    <?php echo substr($rab['creator_name'], 0, 2); ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($rab['creator_name']); ?></div>
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i>
                                                <?php echo date('d M Y H:i', strtotime($rab['created_at'])); ?>
                                            </small>
                                        </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3 row">
                                        <label class="col-sm-4 text-muted">Total Peserta</label>
                                        <div class="col-sm-8 fw-bold fs-5">
                                            <i class="fas fa-users text-info me-2"></i>
                                            <?php echo number_format($rab['total_participants']); ?>
                                        </div>
                                    </div>
                                    
                                    <?php 
                                        /* 
                                        // Removed as per request to move to Catatan column
                                        if (!empty($rab['personnel_notes'])): ?>
                                    <div class="mb-3 row">
                                        <label class="col-sm-4 text-muted">Catatan Petugas</label>
                                        <div class="col-sm-8">
                                            <div class="alert alert-warning py-2 px-3 mb-0 small">
                                                <i class="fas fa-sticky-note me-2"></i>
                                                <?php echo htmlspecialchars($rab['personnel_notes']); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; 
                                    */
                                    ?>

                                    <!-- Consumption Details Section -->
                                    <?php 
                                        $lunch_items = [];
                                        $snack_items = [];
                                        $total_lunch_cost = 0;
                                        $total_snack_cost = 0;

                                        if (isset($project_info['lunch_items'])) {
                                            $decoded = json_decode($project_info['lunch_items'], true);
                                            if (is_array($decoded)) $lunch_items = $decoded;
                                        }
                                        if (isset($project_info['snack_items'])) {
                                            $decoded = json_decode($project_info['snack_items'], true);
                                            if (is_array($decoded)) $snack_items = $decoded;
                                        }
                                        
                                        // Calculate totals
                                        foreach ($lunch_items as $item) {
                                            if (is_array($item) && !empty($item['total'])) {
                                                $total_lunch_cost += (float)$item['total'];
                                            }
                                        }
                                        foreach ($snack_items as $item) {
                                            if (is_array($item) && !empty($item['total'])) {
                                                $total_snack_cost += (float)$item['total'];
                                            }
                                        }
                                        
                                        $grand_total_consumption = $total_lunch_cost + $total_snack_cost;

                                        if (!empty($lunch_items) || !empty($snack_items)):
                                    ?>
                                    <div class="mt-4 pt-3 border-top">
                                        <h6 class="text-muted mb-3 text-uppercase small fw-bold">Detail Menu Konsumsi (Project)</h6>
                                        
                                        <?php if (!empty($lunch_items)): ?>
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <div class="small fw-bold text-dark"><i class="fas fa-utensils text-warning me-2"></i>Makan Siang</div>
                                                <?php if($total_lunch_cost > 0): ?>
                                                    <div class="small fw-bold text-primary">Rp <?php echo number_format($total_lunch_cost, 0, ',', '.'); ?></div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="bg-light rounded p-2 border small">
                                                <ul class="mb-0 ps-3 list-unstyled">
                                                    <?php foreach($lunch_items as $item): ?>
                                                        <li class="mb-1">
                                                            <i class="fas fa-check-circle text-success me-2" style="font-size: 0.8em;"></i>
                                                            <?php 
                                                            if(is_array($item)) {
                                                                echo '<span class="fw-medium">' . ($item['item'] ?? '-') . '</span>' . 
                                                                     (!empty($item['qty']) ? ' <span class="badge bg-secondary rounded-pill ms-1">'.$item['qty'].' pax</span>' : '') . 
                                                                     (!empty($item['price']) ? ' <span class="text-muted ms-1">@ Rp '.number_format($item['price'],0,',','.').'</span>' : '') . 
                                                                     (!empty($item['total']) ? ' <span class="fw-bold text-dark ms-1">= Rp '.number_format($item['total'],0,',','.').'</span>' : '');
                                                            } else {
                                                                echo htmlspecialchars($item);
                                                            }
                                                            ?>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        </div>
                                        <?php endif; ?>

                                        <?php if (!empty($snack_items)): ?>
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <div class="small fw-bold text-dark"><i class="fas fa-cookie-bite text-warning me-2"></i>Snack</div>
                                                <?php if($total_snack_cost > 0): ?>
                                                    <div class="small fw-bold text-primary">Rp <?php echo number_format($total_snack_cost, 0, ',', '.'); ?></div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="bg-light rounded p-2 border small">
                                                <ul class="mb-0 ps-3 list-unstyled">
                                                    <?php foreach($snack_items as $item): ?>
                                                        <li class="mb-1">
                                                            <i class="fas fa-check-circle text-success me-2" style="font-size: 0.8em;"></i>
                                                            <?php 
                                                            if(is_array($item)) {
                                                                echo '<span class="fw-medium">' . ($item['item'] ?? '-') . '</span>' . 
                                                                     (!empty($item['qty']) ? ' <span class="badge bg-secondary rounded-pill ms-1">'.$item['qty'].' pax</span>' : '') . 
                                                                     (!empty($item['price']) ? ' <span class="text-muted ms-1">@ Rp '.number_format($item['price'],0,',','.').'</span>' : '') . 
                                                                     (!empty($item['total']) ? ' <span class="fw-bold text-dark ms-1">= Rp '.number_format($item['total'],0,',','.').'</span>' : '');
                                                            } else {
                                                                echo htmlspecialchars($item);
                                                            }
                                                            ?>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($grand_total_consumption > 0): ?>
                                        <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center">
                                            <span class="small fw-bold text-muted text-uppercase">Total Konsumsi</span>
                                            <span class="fw-bold text-success">Rp <?php echo number_format($grand_total_consumption, 0, ',', '.'); ?></span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>

                                    <!-- SPH Document Section -->
                                    <div class="mt-4 pt-3 border-top">
                                        <h6 class="text-muted mb-3 text-uppercase small fw-bold">Dokumen Project</h6>
                                        <div class="card bg-light border-0">
                                            <div class="card-body p-3 d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-file-pdf text-danger fs-3 me-3"></i>
                                                    <div>
                                                        <h6 class="mb-0 fw-bold">SPH Document</h6>
                                                        <small class="text-muted"><?php echo $sph_file ? 'Tersedia' : 'Belum Tersedia'; ?></small>
                                                    </div>
                                                </div>
                                                <?php if ($sph_file): ?>
                                                    <?php if (preg_match('#^https?://#', $sph_file)): ?>
                                                        <a href="<?php echo htmlspecialchars($sph_file); ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                            <i class="fas fa-external-link-alt me-1"></i> Link SPH
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="index.php?page=download_sph&project_id=<?php echo $rab['project_id']; ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                            <i class="fas fa-eye me-1"></i> View File
                                                        </a>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary text-white" title="No SPH link">
                                                        Not Available
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rincian Biaya -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-file-invoice-dollar me-2"></i>Rincian Biaya</span>
                    </div>
                    <div class="card-body p-0">
                        <!-- Desktop View -->
                        <div class="table-responsive d-none d-md-block">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 30%;">Item / Deskripsi</th>
                                        <th style="width: 15%;">Expense Code</th>
                                        <th class="text-center" style="width: 5%;">Qty</th>
                                        <th class="text-center" style="width: 5%;">Hari</th>
                                        <th class="text-end" style="width: 10%;">Harga Satuan</th>
                                        <th class="text-end" style="width: 10%;">Subtotal</th>
                                        <th style="width: 25%;">Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Grouping Logic
                                    $groups = [
                                        'PETUGAS MEDIS & LAPANGAN' => [],
                                        'KEBUTUHAN VENDOR (EXTERNAL)' => [],
                                        'TRANSPORTASI & AKOMODASI' => [],
                                        'KONSUMSI & LAINNYA' => []
                                    ];
                                    
                                    $cat_map_group = [
                                        'personnel' => 'PETUGAS MEDIS & LAPANGAN',
                                        'vendor' => 'KEBUTUHAN VENDOR (EXTERNAL)',
                                        'transport' => 'TRANSPORTASI & AKOMODASI',
                                        'consumption' => 'KONSUMSI & LAINNYA'
                                    ];

                                    foreach ($items as $item) {
                                        $group_name = $cat_map_group[$item['category']] ?? 'LAINNYA';
                                        $groups[$group_name][] = $item;
                                    }

                                    foreach ($groups as $group_name => $group_items):
                                        if (empty($group_items)) continue;
                                        $group_total = 0;
                                        
                                        // Styling for Advance Payment Group
                                        $header_class = "bg-light text-dark";
                                        $header_style = "";
                                        $icon_class = "opacity-50";
                                        $row_style = "";
                                        $col_style = "";
                                        $first_col_class = "";
                                        
                                        if ($group_name == 'TRANSPORTASI & AKOMODASI') {
                                            // Solid block style for Cash Advance (Yellow Block)
                                            // Using a softer, more professional yellow (Amber 50 / #FFF8E1)
                                            $block_color = "#FFF8E1";
                                            
                                            $header_class = "text-dark";
                                            $header_style = 'style="background-color: ' . $block_color . ' !important;"';
                                            $icon_class = "text-warning";
                                            
                                            $row_style = 'style="background-color: ' . $block_color . ' !important;"';
                                            $col_style = 'style="background-color: ' . $block_color . ' !important;"';
                                        }
                                    ?>
                                        <!-- Group Header -->
                                        <tr class="group-header">
                                            <td colspan="7" class="py-2 ps-2 fw-bold <?php echo $header_class; ?>" <?php echo $header_style; ?>>
                                                <i class="fas fa-layer-group me-2 <?php echo $icon_class; ?>"></i>
                                                <?php echo $group_name; ?>
                                                <?php if ($group_name == 'TRANSPORTASI & AKOMODASI'): ?>
                                                    <span class="badge bg-warning text-dark ms-2" style="font-size: 0.7rem;">Cash Advance</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>

                                        <?php 
                                        /* 
                                        // Merged Notes for Personnel - Removed as we move to Catatan column
                                        if ($group_name == 'PETUGAS MEDIS & LAPANGAN') {
                                            $p_notes = [];
                                            foreach ($group_items as $p_item) {
                                                if (!empty($p_item['notes'])) {
                                                    $p_notes[] = $p_item['notes'];
                                                }
                                            }
                                            $p_notes = array_unique($p_notes);
                                            
                                            if (!empty($p_notes)):
                                        ?>
                                            <tr>
                                                <td colspan="7" class="p-0 border-0">
                                                    <div class="alert alert-info m-2 py-2 px-3 small">
                                                        <i class="fas fa-info-circle me-2"></i>
                                                        <strong>Catatan Gabungan:</strong> <?php echo htmlspecialchars(implode(' | ', $p_notes)); ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php 
                                            endif;
                                        }
                                        */
                                        ?>

                                        <?php foreach ($group_items as $index => $item): 
                                            // Price Logic
                                            $price = (float)($item['price'] ?? 0);
                                            $subtotal = (float)($item['subtotal'] ?? 0);
                                            
                                            $group_total += $subtotal;

                                            // Expense Code Mapping
                                            $exp_code = isset($cost_code_map[$item['item_name']]) 
                                                ? '<span class="badge bg-light text-dark border font-monospace">' . $cost_code_map[$item['item_name']] . '</span>' 
                                                : '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger">Not Mapped</span>';
                                        ?>
                                            <tr <?php echo $row_style; ?>>
                                                <td <?php echo $col_style; ?> class="ps-4 fw-medium <?php echo $first_col_class; ?>"><?php echo htmlspecialchars($item['item_name'] ?? ''); ?></td>
                                                <td <?php echo $col_style; ?>><?php echo $exp_code; ?></td>
                                                <td <?php echo $col_style; ?> class="text-center"><?php echo $item['qty']; ?></td>
                                                <td <?php echo $col_style; ?> class="text-center"><?php echo $item['days']; ?></td>
                                                <td <?php echo $col_style; ?> class="text-end font-monospace text-muted">
                                                    <?php echo ($price > 0) ? number_format($price, 0, ',', '.') : '-'; ?>
                                                </td>
                                                <td <?php echo $col_style; ?> class="text-end fw-bold font-monospace">
                                                    Rp <?php echo number_format($subtotal, 0, ',', '.'); ?>
                                                </td>
                                                
                                                <?php if ($group_name == 'PETUGAS MEDIS & LAPANGAN'): ?>
                                                    <?php if ($index === 0): ?>
                                                        <?php 
                                                            // Calculate merged notes once
                                                            $p_notes = [];
                                                            // Add Personnel Notes (General)
                                                            if (!empty($rab['personnel_notes'])) {
                                                                $p_notes[] = $rab['personnel_notes'];
                                                            }
                                                            // Add Item Notes
                                                            foreach ($group_items as $g_item) {
                                                                if (!empty($g_item['notes'])) {
                                                                    $p_notes[] = $g_item['notes'];
                                                                }
                                                            }
                                                            $p_notes = array_unique($p_notes);
                                                            $merged_notes_str = implode(' | ', $p_notes);
                                                        ?>
                                                        <td <?php echo $col_style; ?> class="small text-muted align-middle" rowspan="<?php echo count($group_items); ?>">
                                                            <?php if(!empty($merged_notes_str)): ?>
                                                                <div class="alert alert-info py-1 px-2 mb-0 small">
                                                                    <i class="fas fa-sticky-note me-1"></i> <?php echo htmlspecialchars($merged_notes_str); ?>
                                                                </div>
                                                            <?php else: ?>
                                                                -
                                                            <?php endif; ?>
                                                        </td>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <td <?php echo $col_style; ?> class="small text-muted">
                                                        <?php echo htmlspecialchars($item['notes'] ?? ''); ?>
                                                    </td>
                                                <?php endif; ?>
                                            </tr>
                                        <?php endforeach; ?>

                                        <!-- Group Subtotal -->
                                        <tr class="subtotal-row" <?php echo $row_style; ?>>
                                            <td <?php echo $col_style; ?> colspan="5" class="text-end py-2 text-uppercase text-muted small <?php echo $first_col_class; ?>">Subtotal <?php echo ucwords(strtolower($group_name)); ?></td>
                                            <td <?php echo $col_style; ?> class="text-end py-2 text-primary">Rp <?php echo number_format($group_total, 0, ',', '.'); ?></td>
                                            <td <?php echo $col_style; ?>></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile View (Cards) -->
                        <div class="d-md-none p-3 bg-light">
                            <?php foreach ($groups as $group_name => $group_items):
                                if (empty($group_items)) continue;
                                $group_total = 0;
                            ?>
                                <h6 class="text-uppercase text-muted fw-bold mb-3 mt-2 small border-bottom pb-2">
                                    <i class="fas fa-layer-group me-2"></i><?php echo $group_name; ?>
                                </h6>
                                
                                <?php 
                                // Merged Notes for Personnel (Mobile)
                                if ($group_name == 'PETUGAS MEDIS & LAPANGAN') {
                                    $p_notes = [];
                                    foreach ($group_items as $p_item) {
                                        if (!empty($p_item['notes'])) {
                                            $p_notes[] = $p_item['notes'];
                                        }
                                    }
                                    $p_notes = array_unique($p_notes);
                                    
                                    if (!empty($p_notes)):
                                ?>
                                    <div class="alert alert-info py-2 px-3 small mb-3">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Catatan Gabungan:</strong> <?php echo htmlspecialchars(implode(' | ', $p_notes)); ?>
                                    </div>
                                <?php 
                                    endif;
                                }
                                ?>
                                
                                <?php foreach ($group_items as $item): 
                                    // Price Logic
                                    $price = (float)($item['price'] ?? 0);
                                    $subtotal = (float)($item['subtotal'] ?? 0);
                                    
                                    $group_total += $subtotal;

                                    // Expense Code Mapping
                                    $exp_code_badge = isset($cost_code_map[$item['item_name']]) 
                                        ? '<span class="badge bg-white text-dark border font-monospace">' . $cost_code_map[$item['item_name']] . '</span>' 
                                        : '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger">Not Mapped</span>';
                                ?>
                                    <div class="card mb-3 shadow-sm border-0">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div>
                                                    <h6 class="fw-bold mb-1 text-dark"><?php echo htmlspecialchars($item['item_name'] ?? ''); ?></h6>
                                                    <?php echo $exp_code_badge; ?>
                                                </div>
                                                <div class="text-end">
                                                    <div class="fw-bold text-primary">Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></div>
                                                    <small class="text-muted d-block"><?php echo ($price > 0) ? '@ '.number_format($price, 0, ',', '.') : '-'; ?></small>
                                                </div>
                                            </div>
                                            
                                            <div class="bg-light rounded p-2 mb-2 text-center">
                                                <small class="text-muted d-block" style="font-size: 0.7rem;">VOLUME / SATUAN</small>
                                                <span class="fw-bold">
                                                    <?php 
                                                    echo (float)$item['qty'];
                                                    if ((float)$item['days'] > 0 && (float)$item['days'] != $project_total_days) {
                                                        echo ' x ' . (float)$item['days'] . ' Hari';
                                                    }
                                                    ?>
                                                </span>
                                            </div>

                                            <?php if (!empty($item['notes'])): ?>
                                                <div class="alert alert-info py-1 px-2 mb-0 small">
                                                    <i class="fas fa-sticky-note me-1"></i> <?php echo htmlspecialchars($item['notes']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                
                                <div class="d-flex justify-content-between align-items-center bg-white border rounded p-3 mb-4">
                                    <span class="text-uppercase small fw-bold text-muted">Subtotal Group</span>
                                    <span class="fw-bold text-dark">Rp <?php echo number_format($group_total, 0, ',', '.'); ?></span>
                                </div>

                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grand Total Section -->
            <div class="col-12">
                <div class="grand-total-section d-flex justify-content-between align-items-center shadow-sm">
                    <div>
                        <h5 class="mb-0 text-success fw-bold">GRAND TOTAL ANGGARAN</h5>
                        <small class="text-muted">Total seluruh estimasi biaya project</small>
                    </div>
                    <div class="text-end">
                        <h2 class="mb-0 fw-bold text-dark">Rp <?php echo number_format($rab['grand_total'], 0, ',', '.'); ?></h2>
                    </div>
                </div>
            </div>

            <!-- Cost Analysis Section (Moved from Modal) -->
            <?php 
                $allowed_roles = ['manager_ops', 'head_ops', 'ceo', 'admin_ops', 'superadmin'];
                if (in_array($_SESSION['role'], $allowed_roles)): 
                    $is_manager_approving = ($_SESSION['role'] == 'manager_ops' && $rab['status'] == 'need_approval_manager');
                    $is_manager_editing_after_approve = ($_SESSION['role'] == 'manager_ops' && $rab['status'] == 'need_approval_head');
                    
                    $is_editable = $is_manager_approving || $is_manager_editing_after_approve;
                    $readonly_attr = $is_editable ? '' : 'readonly';
                    
                    // Input background: white if editable, light if readonly
                    $input_bg = $is_editable ? 'bg-white' : 'bg-light';
            ?>
            <div class="col-12 mt-3">
                <div class="card border-0 shadow-sm" style="background-color: #f8f9fa;">
                    <div class="card-body">
                         <?php if ($is_manager_editing_after_approve): ?>
                             <form action="index.php?page=rabs_update_profit" method="POST" id="profitForm">
                                 <input type="hidden" name="id" value="<?php echo $rab['id']; ?>">
                         <?php endif; ?>

                         <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                            <h6 class="fw-bold text-dark mb-0"><i class="fas fa-chart-pie me-2"></i>Budget Operasional & Persentase</h6>
                            <div class="d-flex align-items-center gap-3">
                                <?php if ($is_manager_editing_after_approve): ?>
                                    <button type="submit" form="profitForm" class="btn btn-sm btn-primary">
                                        <i class="fas fa-save me-1"></i> Update
                                    </button>
                                <?php endif; ?>
                            </div>
                         </div>
                         
                         <!-- Warning removed as per request (Budget Ops expected to be > Grand Total) -->
 
                          <div class="row align-items-center">
                              <div class="col-md-9 mb-3">
                                 <label class="form-label small text-muted fw-bold">Maksimal Budget Ops</label>
                                 <div class="input-group input-group-lg">
                                     <span class="input-group-text <?php echo $input_bg; ?> border-end-0 fw-bold text-success" style="font-size: 2rem;">Rp</span>
                                     <input type="text" id="main_cost_value" name="cost_value" class="form-control currency-input border-start-0 fw-bold text-success <?php echo $input_bg; ?>" style="font-size: 2rem; height: auto;" placeholder="0" onkeyup="updateModalCost()" value="<?php echo ($rab['cost_value'] > 0) ? number_format($rab['cost_value'], 0, ',', '.') : ''; ?>" <?php echo $readonly_attr; ?>>
                                 </div>
                                 <div class="form-text small">Masukkan nominal budget operasional yang dikeluarkan.</div>
                             </div>
                             <div class="col-md-3 mb-3">
                                 <label class="form-label small text-muted fw-bold">Persentase Budget Max (%)</label>
                                 <div class="input-group">
                                     <input type="number" step="0.01" id="main_cost_percentage" name="cost_percentage" class="form-control <?php echo $input_bg; ?> fw-bold text-end" placeholder="0" onkeyup="updateModalCost()" value="<?php echo ($rab['cost_percentage'] != 0) ? $rab['cost_percentage'] : ''; ?>" <?php echo $readonly_attr; ?>>
                                     <span class="input-group-text bg-white fw-bold">%</span>
                                 </div>
                                 <div class="form-text small">Manual Input (e.g. 12%)</div>
                             </div>
                         </div>

                         <?php if ($is_manager_editing_after_approve): ?>
                             </form>
                         <?php endif; ?>
                    </div>
                </div>
            </div>
            <!-- Store Grand Total for JS calculation -->
            <input type="hidden" id="grand_total_value" value="<?php echo $rab['grand_total']; ?>">
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="index.php?page=rabs_approve" method="POST">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title"><i class="fas fa-check-circle me-2"></i>Approve RAB</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <p>Apakah Anda yakin ingin menyetujui RAB ini?</p>
            
            <input type="hidden" name="id" value="<?php echo $rab['id']; ?>">
            <input type="hidden" name="action" value="approve">
            
            <?php if ($_SESSION['role'] == 'manager_ops'): ?>
                <!-- Hidden inputs to receive values from main page -->
                <input type="hidden" name="cost_value" id="modal_cost_value">
                <input type="hidden" name="cost_percentage" id="modal_cost_percentage">
            <?php endif; ?>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-success px-4">Ya, Approve</button>
          </div>
      </form>
    </div>
  </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="index.php?page=rabs_approve" method="POST">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title"><i class="fas fa-times-circle me-2"></i>Reject / Send Back</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="id" value="<?php echo $rab['id']; ?>">
            <input type="hidden" name="action" value="reject">
            
            <div class="mb-3">
                <label class="form-label fw-bold">Alasan Penolakan / Revisi <span class="text-danger">*</span></label>
                <textarea name="reason" class="form-control" rows="4" placeholder="Tuliskan alasan penolakan atau catatan revisi..." required></textarea>
                <div class="form-text">Status akan kembali ke Draft dan dikirimkan notifikasi ke pembuat.</div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-danger px-4">Submit Rejection</button>
          </div>
      </form>
    </div>
  </div>
</div>

<!-- Advance Paid Modal -->
<div class="modal fade" id="advancePaidModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="index.php?page=rabs_advance_paid" method="POST" enctype="multipart/form-data">
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title"><i class="fas fa-money-check-alt me-2"></i>Advance Paid</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="id" value="<?php echo $rab['id']; ?>">
            
            <div class="alert alert-info small">
                <i class="fas fa-info-circle me-1"></i>
                Status RAB akan diubah menjadi <strong>Advance Paid</strong> dan tidak dapat diedit lagi.
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Bukti Transfer (Optional)</label>
                <input type="file" name="transfer_proof" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                <div class="form-text">Format: PDF, JPG, PNG. Max 5MB.</div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Catatan Finance (Optional)</label>
                <textarea name="finance_note" class="form-control" rows="3" placeholder="Tambahkan catatan pembayaran..."></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-success px-4">Submit Payment</button>
          </div>
      </form>
    </div>
  </div>
</div>

<!-- Submit to Finance Modal -->
<div class="modal fade" id="submitFinanceModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="index.php?page=rabs_submit_finance" method="POST">
          <div class="modal-header bg-info text-white">
            <h5 class="modal-title"><i class="fas fa-paper-plane me-2"></i>Submit to Finance</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="id" value="<?php echo $rab['id']; ?>">
            
            <div class="alert alert-info small">
                <i class="fas fa-info-circle me-1"></i>
                RAB akan dikirim ke Finance dan tidak dapat diedit lagi.
            </div>

            <div class="row g-2">
              <div class="col-6">
                <div class="border rounded px-3 py-2">
                  <div class="text-muted small">Nomor RAB</div>
                  <div class="fw-bold"><?php echo htmlspecialchars($rab['rab_number']); ?></div>
                </div>
              </div>
              <div class="col-6">
                <div class="border rounded px-3 py-2">
                  <div class="text-muted small">Grand Total</div>
                  <div class="fw-bold">Rp <?php echo number_format($rab['grand_total']); ?></div>
                </div>
              </div>
              <div class="col-6">
                <div class="border rounded px-3 py-2">
                  <div class="text-muted small">Total Hari</div>
                  <div class="fw-bold">
                    <?php 
                      $d = json_decode($rab['selected_dates'], true);
                      echo is_array($d) ? count($d) : 1; 
                    ?>
                  </div>
                </div>
              </div>
              <div class="col-6">
                <div class="border rounded px-3 py-2">
                  <div class="text-muted small">Total Peserta</div>
                  <div class="fw-bold"><?php echo number_format($rab['total_participants']); ?></div>
                </div>
              </div>
            </div>

            <div class="form-check mt-3">
              <input class="form-check-input" type="checkbox" id="confirmSubmitCheck" required>
              <label class="form-check-label" for="confirmSubmitCheck">Saya mengerti konsekuensi pengiriman ke Finance</label>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-info text-white px-4">Kirim ke Finance</button>
          </div>
      </form>
    </div>
  </div>
</div>

<!-- Lark Template Modal -->
<?php include '../views/admin_sales/lark_template_modal.php'; ?>

<!-- Hidden Form for Auto Approve -->
<form id="autoApproveForm" action="index.php?page=rabs_auto_approve_submit" method="POST" style="display:none;">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
    <input type="hidden" name="id" value="<?php echo $rab['id']; ?>">
</form>

<script>
    function markLarkClicked() {
        const autoBtn = document.getElementById('autoApproveSubmitBtn');
        if (autoBtn) {
            autoBtn.disabled = false;
            autoBtn.removeAttribute('title');
        }
        
        const financeBtn = document.getElementById('submitFinanceBtn');
        if (financeBtn) {
            financeBtn.disabled = false;
            financeBtn.removeAttribute('title');
        }
    }

    function autoApproveAndSubmit() {
        if (confirm('Apakah Anda yakin ingin menyetujui RAB ini secara otomatis dan mengirimkannya ke Finance?\n\nProses ini akan melewati semua tahap persetujuan (Manager, Head, CEO) secara instan.')) {
            document.getElementById('autoApproveForm').submit();
        }
    }

    function showLarkTemplate() {
        // Prepare data for generateLarkTemplate() in lark_template_modal.php
        
        // Clean existing hidden inputs if any
        $('input[name="nama_project"], input[name="sales_name"], input[name="tanggal_mcu"], input[name="lunch"], input[name="snack"], input[name="lunch_item_name[]"], input[name="lunch_item_qty[]"], input[name="snack_item_name[]"], input[name="snack_item_qty[]"]').remove();

        // Parse dates from JSON to comma-separated string for formatLarkDate
        let selectedDates = '<?php echo $rab['selected_dates']; ?>';
        let cleanDates = '';
        try {
            let parsed = JSON.parse(selectedDates);
            if (Array.isArray(parsed)) {
                cleanDates = parsed.join(', ');
            } else {
                cleanDates = selectedDates;
            }
        } catch (e) {
            cleanDates = selectedDates;
        }
        
        $('<input>').attr({type: 'hidden', name: 'nama_project', value: '<?php echo addslashes($rab['nama_project']); ?>'}).appendTo('body');
        $('<input>').attr({type: 'hidden', name: 'sales_name', value: '<?php echo addslashes($project_info['sales_name'] ?? '-'); ?>'}).appendTo('body');
        $('<input>').attr({type: 'hidden', name: 'tanggal_mcu', value: cleanDates}).appendTo('body');
        
        // Handle lunch/snack items (Only Petugas items)
        let hasLunch = false;
        let hasSnack = false;
        
        <?php foreach($items as $item): ?>
            <?php 
                $itemName = $item['item_name'];
                $isPetugas = stripos($itemName, 'Petugas') !== false;
                $isMakan = stripos($itemName, 'Makan') !== false;
                $isSnack = stripos($itemName, 'Snack') !== false || stripos($itemName, 'Air Mineral') !== false;
            ?>
            <?php if ($item['category'] == 'consumption' && $isPetugas): ?>
                <?php if ($isMakan): ?>
                    hasLunch = true;
                    $('<input>').attr({type: 'hidden', name: 'lunch_item_name[]', value: '<?php echo addslashes($itemName); ?>'}).appendTo('body');
                    $('<input>').attr({type: 'hidden', name: 'lunch_item_qty[]', value: '<?php echo $item['qty']; ?>'}).appendTo('body');
                <?php elseif ($isSnack): ?>
                    hasSnack = true;
                    $('<input>').attr({type: 'hidden', name: 'snack_item_name[]', value: '<?php echo addslashes($itemName); ?>'}).appendTo('body');
                    $('<input>').attr({type: 'hidden', name: 'snack_item_qty[]', value: '<?php echo $item['qty']; ?>'}).appendTo('body');
                <?php endif; ?>
            <?php endif; ?>
        <?php endforeach; ?>
        
        $('<input>').attr({type: 'radio', name: 'lunch', value: 'Ya', checked: hasLunch}).hide().appendTo('body');
        $('<input>').attr({type: 'radio', name: 'snack', value: 'Ya', checked: hasSnack}).hide().appendTo('body');

        if (typeof generateLarkTemplate === 'function') {
            const template = generateLarkTemplate();
            $('#larkTemplateText').val(template);
            $('#larkTemplateModal').modal('show');
        } else {
            alert('Lark template generator not found.');
        }
    }

    // Initialize Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    })

    // Format Currency Input
    document.querySelectorAll('.currency-input').forEach(input => {
        input.addEventListener('keyup', function(e) {
            let value = this.value.replace(/[^0-9]/g, '');
            this.value = new Intl.NumberFormat('id-ID').format(value);
            
            // Check if this input is main_cost_value, if so trigger update
            if(this.id === 'main_cost_value') {
                updateModalCost();
            }
        });
    });

    // Removed auto-calculation of margin as per request. 
    // Both fields are now manual input.
    function calculateMargin() {
        // Legacy function kept to avoid reference errors if called elsewhere, 
        // but it now only triggers modal update.
        updateModalCost();
    }
    
    function updateModalCost() {
        // Sync values from main inputs to modal hidden inputs
        var mainValue = document.getElementById('main_cost_value');
        var mainPercent = document.getElementById('main_cost_percentage');
        
        var modalValue = document.getElementById('modal_cost_value');
        var modalPercent = document.getElementById('modal_cost_percentage');
        
        if(mainValue && modalValue) {
            modalValue.value = mainValue.value;
        }
        
        if(mainPercent && modalPercent) {
            modalPercent.value = mainPercent.value;
        }
    }

    // Initialize on load
    window.addEventListener('DOMContentLoaded', function() {
        updateModalCost();
    });
</script>

<?php include '../views/layouts/footer.php'; ?>

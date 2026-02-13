<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<style>
    :root {
        --primary-medical: #204EAB;
        --secondary-medical: #4facfe;
        --light-medical: #f0f4f8;
        --soft-gray: #e9ecef;
    }

    body {
        background-color: #f8f9fa;
    }

    .medical-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        transition: transform 0.2s;
        background: white;
        overflow: hidden;
    }

    .medical-card-header {
        background-color: white;
        border-bottom: 1px solid var(--soft-gray);
        padding: 1.5rem;
    }

    .section-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background-color: rgba(32, 78, 171, 0.1);
        color: var(--primary-medical);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        margin-right: 1rem;
    }

    /* Stepper */
    .stepper-wrapper {
        display: flex;
        justify-content: space-between;
        margin-bottom: 2rem;
        position: relative;
    }

    .stepper-wrapper::before {
        content: '';
        position: absolute;
        top: 15px;
        left: 0;
        width: 100%;
        height: 2px;
        background-color: #e0e0e0;
        z-index: 0;
    }

    .stepper-item {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        flex: 1;
        z-index: 1;
    }

    .step-counter {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background-color: white;
        border: 2px solid #e0e0e0;
        display: flex;
        justify-content: center;
        align-items: center;
        font-weight: bold;
        color: #999;
        margin-bottom: 8px;
        transition: all 0.3s;
    }

    .stepper-item.active .step-counter {
        border-color: var(--primary-medical);
        background-color: var(--primary-medical);
        color: white;
        box-shadow: 0 0 0 4px rgba(32, 78, 171, 0.2);
    }

    .stepper-item.completed .step-counter {
        border-color: #198754;
        background-color: #198754;
        color: white;
    }

    .step-name {
        font-size: 0.85rem;
        color: #999;
        font-weight: 500;
    }

    .stepper-item.active .step-name {
        color: var(--primary-medical);
        font-weight: bold;
    }

    /* Sticky Summary */
    .sticky-summary {
        position: sticky;
        top: 20px;
        z-index: 1020;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }

    .summary-total {
        border-top: 2px dashed #dee2e6;
        margin-top: 1rem;
        padding-top: 1rem;
    }

    /* Form Elements */
    .form-control, .form-select {
        border-radius: 8px;
        border: 1px solid #dee2e6;
        padding: 0.6rem 1rem;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-medical);
        box-shadow: 0 0 0 0.25rem rgba(32, 78, 171, 0.1);
    }

    .btn-primary-medical {
        background-color: var(--primary-medical);
        border-color: var(--primary-medical);
        color: white;
        border-radius: 50px;
        padding: 0.6rem 2rem;
        font-weight: 600;
        transition: all 0.3s;
    }

    .btn-primary-medical:hover {
        background-color: #1a4296;
        box-shadow: 0 4px 12px rgba(32, 78, 171, 0.3);
    }

    .table-custom thead th {
        background-color: var(--light-medical);
        color: var(--primary-medical);
        font-weight: 600;
        border: none;
        padding: 1rem;
    }
    
    .table-custom tbody td {
        padding: 1rem;
        vertical-align: middle;
    }

    .code-badge {
        font-size: 0.75em;
        background-color: #eee;
        padding: 2px 6px;
        border-radius: 4px;
    }

    /* Step Visibility */
    .step-section {
        display: none;
        animation: fadeIn 0.4s ease-in-out;
    }

    .step-section.active {
        display: block;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="container-fluid px-4 pb-5">
    <div class="d-flex justify-content-between align-items-center my-4">
        <div>
            <h3 class="fw-bold mb-0" style="color: var(--primary-medical);">Edit Realisasi Harian</h3>
            <p class="text-muted mb-0">Project <strong><?php echo htmlspecialchars($realization['nama_project'] ?? ''); ?></strong> - RAB <strong><?php echo htmlspecialchars($realization['rab_number'] ?? ''); ?></strong></p>
        </div>
        <a href="index.php?page=realization_list" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <!-- Stepper -->
    <div class="row justify-content-center mb-4">
        <div class="col-lg-8">
            <div class="stepper-wrapper">
                <div class="stepper-item active" data-step="1">
                    <div class="step-counter">1</div>
                    <div class="step-name">Info Dasar</div>
                </div>
                <div class="stepper-item" data-step="2">
                    <div class="step-counter">2</div>
                    <div class="step-name">Petugas</div>
                </div>
                <div class="stepper-item" data-step="3">
                    <div class="step-counter">3</div>
                    <div class="step-name">Vendor</div>
                </div>
                <div class="stepper-item" data-step="4">
                    <div class="step-counter">4</div>
                    <div class="step-name">Transportasi</div>
                </div>
                <div class="stepper-item" data-step="5">
                    <div class="step-counter">5</div>
                    <div class="step-name">Konsumsi</div>
                </div>
                <div class="stepper-item" data-step="6">
                    <div class="step-counter">6</div>
                    <div class="step-name">Finalisasi</div>
                </div>
            </div>
        </div>
    </div>

    <form action="index.php?page=realization_update" method="POST" id="realizationForm" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $realization['id']; ?>">
        
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                
                <!-- STEP 1: INFO DASAR -->
                <div id="step-1" class="step-section active">
                    <!-- Date Selection Card -->
                    <div class="medical-card mb-4">
                        <div class="medical-card-header d-flex align-items-center">
                            <div class="section-icon"><i class="fas fa-calendar-alt"></i></div>
                            <h5 class="mb-0 fw-bold">Tanggal & Informasi Dasar</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-muted small text-uppercase">Tanggal Realisasi</label>
                                    <input type="text" class="form-control form-control-lg bg-light text-primary fw-bold" value="<?php echo date('d M Y', strtotime($realization['date'])); ?>" readonly>
                                    <input type="hidden" name="date" value="<?php echo $realization['date']; ?>">
                                </div>
                            </div>
                            
                            <hr class="my-4 text-muted opacity-25">
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-primary small text-uppercase">Total Pax (Aktual) <span class="text-danger">*</span></label>
                                    <input type="number" name="actual_participants" id="actual_participants" class="form-control fw-bold border-primary shadow-sm" value="<?php echo $realization['actual_participants']; ?>" min="1" required>
                                    <div class="form-text text-primary small"><i class="fas fa-pencil-alt me-1"></i>Isi manual sesuai kehadiran hari ini.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: PETUGAS -->
                <div id="step-2" class="step-section">
                    <!-- SECTION: PETUGAS MEDIS & LAPANGAN -->
                    <div class="medical-card mb-4">
                    <div class="medical-card-header d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="section-icon"><i class="fas fa-user-md"></i></div>
                            <h5 class="mb-0 fw-bold">Petugas Medis & Lapangan</h5>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" id="btn-open-personnel-modal">
                            <i class="fas fa-plus me-1"></i> Tambah Petugas
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-custom align-middle mb-0" id="table-personnel">
                                <thead>
                                    <tr>
                                        <th style="width: 30%">Role / Item</th>
                                        <th style="width: 15%" class="text-center">Qty Aktual</th>
                                        <th style="width: 20%" class="text-end">Harga Satuan</th>
                                        <th style="width: 20%" class="text-end">Subtotal</th>
                                        <th style="width: 5%"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $itemIndex = 0;
                                    foreach ($grouped_items['personnel'] as $item): 
                                        // Skip overload fee as it is auto-calculated
                                        if ($item['item_name'] === 'Fee Dokter (Overload)') continue;
                                    ?>
                                    <tr class="item-row" data-category="personnel">
                                        <td>
                                            <input type="hidden" name="items[<?php echo $itemIndex; ?>][category]" value="personnel">
                                            <input type="text" name="items[<?php echo $itemIndex; ?>][item_name]" class="form-control form-control-plaintext fw-bold" value="<?php echo htmlspecialchars($item['item_name']); ?>" readonly>
                                            <input type="text" name="items[<?php echo $itemIndex; ?>][notes]" class="form-control form-control-sm mt-1" placeholder="Catatan (Nama Petugas, dll)" value="<?php echo htmlspecialchars($item['notes'] ?? ''); ?>">
                                        </td>
                                        <td>
                                            <input type="number" name="items[<?php echo $itemIndex; ?>][qty]" class="form-control text-center qty-input" value="<?php echo round($item['qty']); ?>" min="0">
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-light border-end-0">Rp</span>
                                                <input type="text" name="items[<?php echo $itemIndex; ?>][price]" class="form-control text-end price-input border-start-0 bg-light" value="<?php echo number_format($item['price'], 0, ',', '.'); ?>" readonly>
                                            </div>
                                        </td>
                                        <td class="text-end fw-bold subtotal-display">
                                            Rp <?php echo number_format($item['qty'] * $item['price'], 0, ',', '.'); ?>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-link text-danger p-0 remove-row" title="Hapus"><i class="fas fa-times"></i></button>
                                        </td>
                                    </tr>
                                    <?php $itemIndex++; endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Doctor Logic Panel -->
                        <div id="doctor-wrapper" style="display: none;">
                            <div class="bg-light p-3 m-3 rounded-3 border" id="doctor-logic-panel">
                                <div class="row g-3 align-items-center">
                                    <div class="col-md-6">
                                        <label class="fw-bold small text-uppercase text-muted mb-1">Total Pax (Validasi Dokter)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white text-primary"><i class="fas fa-user-check"></i></span>
                                            <input type="number" name="doctor_participants" id="doctor_participants" class="form-control fw-bold" placeholder="Jumlah peserta..." min="0" value="<?php echo $realization['doctor_participants']; ?>">
                                        </div>
                                        <small class="text-muted fst-italic mt-1 d-block">Digunakan khusus untuk menghitung overload fee dokter.</small>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="alert alert-warning mb-0 small shadow-sm" id="doctor-fee-alert" style="display: none;">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-exclamation-triangle me-3 fs-4"></i>
                                                <div>
                                                    <strong>Overload Fee Detected!</strong>
                                                    <div class="mt-1">
                                                        Peserta melebihi kapasitas dokter (<span id="capacity-display">0</span>).
                                                        Biaya Tambahan: <strong class="text-danger fs-6" id="extra-fee-display">Rp 0</strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>

                <!-- STEP 3: VENDOR -->
                <div id="step-3" class="step-section">
                    <div class="medical-card mb-4">
                        <div class="medical-card-header d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="section-icon"><i class="fas fa-truck-loading"></i></div>
                                <h5 class="mb-0 fw-bold">Vendor</h5>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="appendRow('vendor', '', '', 1, 0)">
                                <i class="fas fa-plus me-1"></i> Tambah Item
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-custom align-middle mb-0" id="table-vendor">
                                    <thead>
                                        <tr>
                                            <th style="width: 30%">Item Vendor</th>
                                            <th style="width: 15%" class="text-center">Qty</th>
                                            <th style="width: 20%" class="text-end">Harga Satuan</th>
                                            <th style="width: 20%" class="text-end">Subtotal</th>
                                            <th style="width: 5%"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($grouped_items['vendor'] as $item): ?>
                                        <tr class="item-row" data-category="vendor">
                                            <td>
                                                <input type="hidden" name="items[<?php echo $itemIndex; ?>][category]" value="vendor">
                                                <input type="text" name="items[<?php echo $itemIndex; ?>][item_name]" class="form-control form-control-plaintext fw-bold" value="<?php echo htmlspecialchars($item['item_name']); ?>" readonly>
                                                <input type="text" name="items[<?php echo $itemIndex; ?>][notes]" class="form-control form-control-sm mt-1" placeholder="Catatan" value="<?php echo htmlspecialchars($item['notes'] ?? ''); ?>">
                                            </td>
                                            <td>
                                                <input type="number" name="items[<?php echo $itemIndex; ?>][qty]" class="form-control text-center qty-input" value="<?php echo round($item['qty']); ?>" min="0">
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-light border-end-0">Rp</span>
                                                    <input type="text" name="items[<?php echo $itemIndex; ?>][price]" class="form-control text-end price-input border-start-0 bg-light" value="<?php echo number_format($item['price'], 0, ',', '.'); ?>" readonly>
                                                </div>
                                            </td>
                                            <td class="text-end fw-bold subtotal-display">
                                                Rp <?php echo number_format($item['qty'] * $item['price'], 0, ',', '.'); ?>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-link text-danger p-0 remove-row" title="Hapus"><i class="fas fa-times"></i></button>
                                            </td>
                                        </tr>
                                        <?php $itemIndex++; endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 4: TRANSPORTASI -->
                <div id="step-4" class="step-section">
                    <!-- SECTION: TRANSPORTASI & AKOMODASI -->
                    <div class="medical-card mb-4">
                        <div class="medical-card-header d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="section-icon"><i class="fas fa-ambulance"></i></div>
                                <h5 class="mb-0 fw-bold">Transportasi & Akomodasi</h5>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" id="btn-open-transport-modal">
                                <i class="fas fa-plus me-1"></i> Tambah Item
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-warning text-dark rounded-pill px-3 ms-2" onclick="appendRow('transport', 'EMERGENCY', '', 0, '', true); calculateAll();">
                                <i class="fas fa-exclamation-triangle me-1"></i> Emergency Cost
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-custom align-middle mb-0" id="table-transport">
                                    <thead>
                                        <tr>
                                            <th style="width: 30%">Item / Keterangan</th>
                                            <th style="width: 15%" class="text-center">Qty</th>
                                            <th style="width: 20%" class="text-end">Harga Satuan</th>
                                            <th style="width: 20%" class="text-end">Subtotal</th>
                                            <th style="width: 5%"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($grouped_items['transport'] as $item): 
                                        $isEmergency = stripos($item['item_name'], 'emergency') !== false;
                                    ?>
                                    <tr class="item-row" data-category="transport">
                                        <td>
                                            <input type="hidden" name="items[<?php echo $itemIndex; ?>][category]" value="transport">
                                            <?php if ($isEmergency): ?>
                                                <input type="hidden" name="items[<?php echo $itemIndex; ?>][item_name]" class="item-name-hidden" value="<?php echo htmlspecialchars($item['item_name']); ?>">
                                                <div class="mb-1">
                                                    <span class="badge bg-warning text-dark mb-1">Emergency Cost</span>
                                                    <span class="fw-bold d-block text-dark"><?php echo trim(str_ireplace('EMERGENCY:', '', htmlspecialchars($item['item_name']))); ?></span>
                                                </div>
                                            <?php else: ?>
                                                <input type="text" name="items[<?php echo $itemIndex; ?>][item_name]" class="form-control form-control-plaintext fw-bold" value="<?php echo htmlspecialchars($item['item_name']); ?>" readonly>
                                            <?php endif; ?>
                                            <input type="text" name="items[<?php echo $itemIndex; ?>][notes]" class="form-control form-control-sm mt-1" placeholder="Catatan" value="<?php echo htmlspecialchars($item['notes'] ?? ''); ?>">
                                        </td>
                                        <td class="text-center">
                                            <?php if ($isEmergency): ?>
                                                <input type="hidden" name="items[<?php echo $itemIndex; ?>][qty]" class="qty-input" value="1">
                                                <span class="badge bg-light text-dark border px-3 py-2">1</span>
                                            <?php else: ?>
                                                <input type="number" name="items[<?php echo $itemIndex; ?>][qty]" class="form-control text-center qty-input" value="<?php echo round($item['qty']); ?>" min="0">
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-light border-end-0">Rp</span>
                                                <input type="text" name="items[<?php echo $itemIndex; ?>][price]" class="form-control text-end price-input border-start-0 bg-light" value="<?php echo number_format($item['price'], 0, ',', '.'); ?>" readonly>
                                            </div>
                                        </td>
                                        <td class="text-end fw-bold subtotal-display">
                                            Rp <?php echo number_format($item['qty'] * $item['price'], 0, ',', '.'); ?>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-link text-danger p-0 remove-row" title="Hapus"><i class="fas fa-times"></i></button>
                                        </td>
                                    </tr>
                                    <?php $itemIndex++; endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 4: KONSUMSI -->
                <div id="step-4" class="step-section">
                    <!-- SECTION: KONSUMSI -->
                    <div class="medical-card mb-4">
                        <div class="medical-card-header d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="section-icon"><i class="fas fa-utensils"></i></div>
                                <h5 class="mb-0 fw-bold">Konsumsi & Lainnya</h5>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" id="btn-open-consumption-modal">
                                <i class="fas fa-plus me-1"></i> Tambah Item
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-custom align-middle mb-0" id="table-consumption">
                                    <thead>
                                        <tr>
                                            <th style="width: 30%">Item / Keterangan</th>
                                            <th style="width: 15%" class="text-center">Qty</th>
                                            <th style="width: 20%" class="text-end">Harga Satuan</th>
                                            <th style="width: 20%" class="text-end">Subtotal</th>
                                            <th style="width: 5%"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($grouped_items['consumption'] as $item): ?>
                                    <tr class="item-row" data-category="consumption">
                                        <td>
                                            <input type="hidden" name="items[<?php echo $itemIndex; ?>][category]" value="consumption">
                                            <input type="text" name="items[<?php echo $itemIndex; ?>][item_name]" class="form-control form-control-plaintext fw-bold" value="<?php echo htmlspecialchars($item['item_name']); ?>" readonly>
                                            <input type="text" name="items[<?php echo $itemIndex; ?>][notes]" class="form-control form-control-sm mt-1" placeholder="Catatan" value="<?php echo htmlspecialchars($item['notes'] ?? ''); ?>">
                                        </td>
                                        <td>
                                            <input type="number" name="items[<?php echo $itemIndex; ?>][qty]" class="form-control text-center qty-input" value="<?php echo round($item['qty']); ?>" min="0">
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-light border-end-0">Rp</span>
                                                <input type="text" name="items[<?php echo $itemIndex; ?>][price]" class="form-control text-end price-input border-start-0 bg-light" value="<?php echo number_format($item['price'], 0, ',', '.'); ?>" readonly>
                                            </div>
                                        </td>
                                        <td class="text-end fw-bold subtotal-display">
                                            Rp <?php echo number_format($item['qty'] * $item['price'], 0, ',', '.'); ?>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-link text-danger p-0 remove-row" title="Hapus"><i class="fas fa-times"></i></button>
                                        </td>
                                    </tr>
                                    <?php $itemIndex++; endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="medical-card mb-4">
                        <div class="medical-card-header">
                            <h5 class="mb-0 fw-bold">Catatan Tambahan</h5>
                        </div>
                        <div class="card-body p-4">
                            <textarea name="notes" class="form-control" rows="3" placeholder="Tulis catatan jika ada..."><?php echo htmlspecialchars($realization['notes'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- STEP 5: FINALISASI -->
                <div id="step-5" class="step-section">
                    <div class="medical-card mb-4">
                        <div class="medical-card-header d-flex align-items-center">
                            <div class="section-icon"><i class="fas fa-check-double"></i></div>
                            <h5 class="mb-0 fw-bold">Finalisasi & Upload BA</h5>
                        </div>
                        <div class="card-body p-4">
                            <p class="text-muted mb-4">Silakan review kembali data Anda sebelum melakukan finalisasi. Pastikan file Berita Acara (BA) sudah ditandatangani dan diupload.</p>

                            <!-- Berita Acara Upload -->
                            <div class="alert alert-info border-0 shadow-sm mb-4">
                                <div class="d-flex">
                                    <div class="me-3">
                                        <i class="fas fa-file-upload fa-2x text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-1">Upload Berita Acara</h6>
                                        <div class="small mb-2">File BA wajib diupload untuk melakukan Finalize. Jika status Draft, upload bersifat opsional.</div>
                                        
                                        <?php if(!empty($realization['ba_file_url'])): ?>
                                            <div class="bg-white p-2 rounded border mb-2">
                                                <i class="fas fa-check-circle text-success me-1"></i>
                                                Existing File: 
                                                <a href="<?php echo $realization['ba_file_url']; ?>" target="_blank" class="fw-bold text-decoration-underline">Lihat BA Terupload</a>
                                                <span class="badge bg-success ms-2"><?php echo isset($realization['ba_status']) ? $realization['ba_status'] : 'Uploaded'; ?></span>
                                            </div>
                                        <?php endif; ?>

                                        <input type="file" name="ba_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                        <div class="form-text text-muted small mt-1">Format: PDF, JPG, PNG. Max 5MB.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                <i class="fas fa-info-circle text-primary me-3 fs-4"></i>
                                <div class="small text-muted">
                                    Dengan menekan tombol <strong>Update & Finalize</strong>, data akan dikunci dan status berubah menjadi Submitted/Final.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Sticky Sidebar (Right) -->
            <div class="col-lg-4">
                <div class="sticky-summary">
                    <div class="medical-card">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-4">Summary Realisasi</h5>
                            
                            <!-- Subtotals -->
                            <div class="summary-row">
                                <span class="text-muted">Total Petugas</span>
                                <span class="fw-bold" id="summary_personnel">0</span>
                            </div>
                            <div class="summary-row">
                                <span class="text-muted">Total Vendor</span>
                                <span class="fw-bold" id="summary_vendor">0</span>
                            </div>
                            <div class="summary-row">
                                <span class="text-muted">Total Transport</span>
                                <span class="fw-bold" id="summary_transport">0</span>
                            </div>
                            <div class="summary-row">
                                <span class="text-muted">Total Konsumsi</span>
                                <span class="fw-bold" id="summary_consumption">0</span>
                            </div>
                            <div class="summary-row" id="summary_doctor_fee_row" style="display: none;">
                                <span class="text-warning fw-bold">Fee Dokter (Overload)</span>
                                <span class="fw-bold text-warning" id="summary_doctor_fee">0</span>
                            </div>

                            <!-- Grand Total -->
                            <div class="summary-total text-center">
                                <small class="text-muted text-uppercase fw-bold">Total Realisasi Harian</small>
                                <h3 class="text-primary fw-bold mt-2" id="grand_total_display">Rp 0</h3>
                                <input type="hidden" name="total_amount" id="total_amount" value="0">
                            </div>

                            <hr class="my-4">

                            <!-- Navigation Buttons -->
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-primary-medical" id="btn_next">
                                    Selanjutnya <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                                
                                <button type="submit" name="save_as" value="draft" class="btn btn-outline-secondary rounded-pill fw-bold py-2 d-none" id="btn_draft">
                                    <i class="fas fa-save me-2"></i>Update Draft
                                </button>

                                <button type="submit" name="save_as" value="submitted" class="btn btn-success rounded-pill fw-bold py-2 d-none" id="btn_submit">
                                    <i class="fas fa-paper-plane me-2"></i>Update & Finalize
                                </button>
                                
                                <button type="button" class="btn btn-light rounded-pill text-muted" id="btn_prev" style="display: none;">
                                    <i class="fas fa-arrow-left me-2"></i>Kembali
                                </button>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Modals for Adding Items -->
<!-- 1. Add Personnel Modal -->
<div class="modal fade" id="addPersonnelModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-primary">Tambah Petugas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="list-group list-group-flush">
                    <?php foreach ($master_personnel as $p): 
                        // Calculate Price based on Config & Location
                        $role_key = str_replace(' ', '_', $p['name']);
                        $key_dalam = "fee_dalam_kota_" . $role_key;
                        $key_luar = "fee_luar_kota_" . $role_key;
                        
                        $price_dalam = isset($fee_settings[$key_dalam]) ? (float)str_replace(['.', ','], '', $fee_settings[$key_dalam]) : 0;
                        $price_luar = isset($fee_settings[$key_luar]) ? (float)str_replace(['.', ','], '', $fee_settings[$key_luar]) : 0;
                        
                        $is_luar = isset($rab['location_type']) && $rab['location_type'] == 'luar_kota';
                        $current_price = $is_luar ? $price_luar : $price_dalam;
                    ?>
                    <label class="list-group-item list-group-item-action d-flex gap-3 align-items-center py-3 px-4">
                        <input class="form-check-input flex-shrink-0 add-item-checkbox" type="checkbox" value="" 
                            data-category="personnel" 
                            data-name="<?php echo $p['name']; ?>"
                            data-code="<?php echo $p['expense_code']; ?>"
                            data-price="<?php echo $current_price; ?>"
                            style="width: 1.2em; height: 1.2em;">
                        <div class="d-flex flex-column">
                            <span class="fw-bold text-dark"><?php echo $p['name']; ?></span>
                            <small class="text-muted code-badge d-inline-block mt-1"><?php echo $p['expense_code']; ?></small>
                            <small class="text-primary fw-bold">Rp <?php echo number_format($current_price, 0, ',', '.'); ?></small>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-link text-secondary text-decoration-none" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary-medical px-4" onclick="addSelectedItems('personnel')">Tambahkan Terpilih</button>
            </div>
        </div>
    </div>
</div>

<!-- 2. Add Transport Modal -->
<div class="modal fade" id="addTransportModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-primary">Tambah Transportasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-4">
                    <label class="form-label fw-bold text-uppercase small text-muted mb-3">Opsi Standar</label>
                    <div class="list-group">
                        <?php foreach ($master_transport as $t): 
                            if (stripos($t['name'], 'emergency') !== false) continue; // Skip Emergency items in standard list
                        ?>
                        <label class="list-group-item list-group-item-action d-flex gap-3 align-items-center">
                            <input class="form-check-input flex-shrink-0 add-item-checkbox" type="checkbox" value="" 
                                data-category="transport" 
                                data-name="<?php echo $t['name']; ?>"
                                data-code="<?php echo $t['expense_code']; ?>">
                            <span><?php echo $t['name']; ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="border rounded p-3 bg-warning bg-opacity-10 border-warning">
                    <label class="d-flex gap-2 align-items-center mb-0 cursor-pointer">
                        <input class="form-check-input flex-shrink-0" type="checkbox" id="check-emergency">
                        <span class="fw-bold text-dark">
                            <i class="fas fa-exclamation-triangle me-1 text-warning"></i> Emergency Cost
                        </span>
                    </label>
                    
                    <div class="mt-3" id="emergency-options" style="display: none;">
                        <select class="form-select form-select-sm" id="emergency-type-select">
                            <option value="">-- Pilih Tipe Emergency --</option>
                            <?php foreach ($emergency_types as $e): ?>
                                <option value="<?php echo $e['name']; ?>" data-code="<?php echo $e['expense_code']; ?>">
                                    <?php echo $e['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" class="form-control form-control-sm mt-2" id="emergency-detail" placeholder="Detail penggunaan (e.g. Dari RS A ke RS B)">
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-link text-secondary text-decoration-none" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary-medical px-4" onclick="addTransportItems()">Tambahkan</button>
            </div>
        </div>
    </div>
</div>

<!-- 3. Add Consumption Modal -->
<div class="modal fade" id="addConsumptionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-primary">Tambah Konsumsi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="list-group list-group-flush">
                    <?php foreach ($master_consumption as $c): ?>
                    <label class="list-group-item list-group-item-action d-flex gap-3 align-items-center py-3 px-4">
                        <input class="form-check-input flex-shrink-0 add-item-checkbox" type="checkbox" value="" 
                            data-category="consumption" 
                            data-name="<?php echo $c['name']; ?>"
                            data-code="<?php echo $c['expense_code']; ?>"
                            style="width: 1.2em; height: 1.2em;">
                        <div class="d-flex flex-column">
                            <span class="fw-bold text-dark"><?php echo $c['name']; ?></span>
                            <small class="text-muted code-badge d-inline-block mt-1"><?php echo $c['expense_code']; ?></small>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-link text-secondary text-decoration-none" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary-medical px-4" onclick="addSelectedItems('consumption')">Tambahkan Terpilih</button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    const EMERGENCY_TYPES = <?php echo json_encode($emergency_types); ?>;
    let newItemIndex = 1000;

    document.addEventListener('DOMContentLoaded', function() {
        // Stepper Logic
        let currentStep = 1;
        const totalSteps = 5;

        function showStep(step) {
            $('.step-section').removeClass('active');
            $('#step-' + step).addClass('active');
            
            $('.stepper-item').removeClass('active completed');
            for(let i=1; i<=totalSteps; i++) {
                if(i < step) {
                    $('.stepper-item[data-step="'+i+'"]').addClass('completed');
                } else if(i === step) {
                    $('.stepper-item[data-step="'+i+'"]').addClass('active');
                }
            }

            if(step === 1) {
                $('#btn_prev').hide();
            } else {
                $('#btn_prev').show();
            }

            if(step === totalSteps) {
                $('#btn_next').hide();
                $('#btn_submit').removeClass('d-none');
                $('#btn_draft').removeClass('d-none');
            } else {
                $('#btn_next').show();
                $('#btn_submit').addClass('d-none');
                $('#btn_draft').addClass('d-none');
            }
            
            window.scrollTo(0, 0);
        }

        $('#btn_next').click(function() {
            if(validateStep(currentStep)) {
                currentStep++;
                showStep(currentStep);
            }
        });

        $('#btn_prev').click(function() {
            currentStep--;
            showStep(currentStep);
        });

        function validateStep(step) {
            if(step === 1) {
                if($('#actual_participants').val() === '' || $('#actual_participants').val() < 1) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Data Belum Lengkap',
                        text: 'Silakan isi Total Pax Aktual',
                        confirmButtonColor: '#204EAB'
                    });
                    return false;
                }
            }
            return true;
        }

        // Calculation Engine
        window.calculateAll = function() {
            let totalPersonnel = 0;
            let totalVendor = 0;
            let totalTransport = 0;
            let totalConsumption = 0;
            let doctorCount = 0;
            let doctorFee = 0;

            $('.item-row').each(function() {
                const cat = $(this).data('category');
                const qty = parseInt($(this).find('.qty-input').val()) || 0;
                const price = parseCurrency($(this).find('.price-input').val());
                const subtotal = qty * price;
                const itemName = $(this).find('input[name*="[item_name]"]').val();

                // Format subtotal display
                $(this).find('.subtotal-display').text('Rp ' + formatNumber(subtotal));
                
                // Update subtotal in dataset for robust calculation
                $(this).attr('data-subtotal', subtotal);

                if(cat === 'personnel') {
                    totalPersonnel += subtotal;
                    if(itemName && itemName.toLowerCase().includes('dokter')) {
                        doctorCount += qty;
                    }
                } else if(cat === 'vendor') {
                    totalVendor += subtotal;
                } else if(cat === 'transport') {
                    totalTransport += subtotal;
                } else if(cat === 'consumption') {
                    totalConsumption += subtotal;
                }
            });

            // Doctor Logic
            const maxPatient = <?php echo $doctor_max_patient; ?>;
            const extraFee = <?php echo $doctor_extra_fee; ?>;
            const actualPax = parseInt($('#actual_participants').val()) || 0;
            
            let doctorPax = parseInt($('#doctor_participants').val());
            if (isNaN(doctorPax)) doctorPax = actualPax;

            if (doctorCount > 0) {
                $('#doctor-wrapper').show();
                const capacity = doctorCount * maxPatient;
                $('#capacity-display').text(capacity);

                if (doctorPax > capacity) {
                    const overload = doctorPax - capacity;
                    doctorFee = overload * extraFee;
                    
                    $('#doctor-fee-alert').show();
                    $('#extra-fee-display').text('Rp ' + formatNumber(doctorFee));
                    $('#summary_doctor_fee_row').show();
                } else {
                    $('#doctor-fee-alert').hide();
                    $('#summary_doctor_fee_row').hide();
                }
            } else {
                $('#doctor-wrapper').hide();
                $('#summary_doctor_fee_row').hide();
            }

            $('#summary_personnel').text('Rp ' + formatNumber(totalPersonnel));
            $('#summary_vendor').text('Rp ' + formatNumber(totalVendor));
            $('#summary_transport').text('Rp ' + formatNumber(totalTransport));
            $('#summary_consumption').text('Rp ' + formatNumber(totalConsumption));
            $('#summary_doctor_fee').text('Rp ' + formatNumber(doctorFee));

            const grandTotal = totalPersonnel + totalVendor + totalTransport + totalConsumption + doctorFee;
            $('#grand_total_display').text('Rp ' + formatNumber(grandTotal));
            $('#total_amount').val(grandTotal);
        }

        // Helper Functions
        window.formatNumber = function(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        window.parseCurrency = function(str) {
            if(!str) return 0;
            return parseInt(str.toString().replace(/\./g, '')) || 0;
        }

        $(document).on('input', '.qty-input, #actual_participants, #doctor_participants', function() {
            calculateAll();
        });

        $(document).on('click', '.remove-row', function() {
            if(confirm('Hapus item ini?')) {
                $(this).closest('tr').remove();
                calculateAll();
            }
        });
        
        // Modal Event Listeners
        function setupModalTrigger(btnId, modalId) {
            const btn = document.getElementById(btnId);
            if (btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const modalEl = document.getElementById(modalId);
                    if (modalEl && typeof bootstrap !== 'undefined') {
                        // Use getOrCreateInstance if available, fallback to new
                        const modal = bootstrap.Modal.getOrCreateInstance ? 
                                      bootstrap.Modal.getOrCreateInstance(modalEl) : 
                                      new bootstrap.Modal(modalEl);
                        modal.show();
                    } else {
                        console.error('Bootstrap or Modal not found');
                        if (typeof bootstrap === 'undefined') alert('System Error: Bootstrap JS not loaded');
                    }
                });
            }
        }

        setupModalTrigger('btn-open-personnel-modal', 'addPersonnelModal');
        setupModalTrigger('btn-open-transport-modal', 'addTransportModal');
        setupModalTrigger('btn-open-consumption-modal', 'addConsumptionModal');

        // Emergency Checkbox Logic
        $('#check-emergency').change(function() {
            if(this.checked) {
                $('#emergency-options').show();
            } else {
                $('#emergency-options').hide();
            }
        });
        
        // Initial Calc
        calculateAll();
        
        // Submit Logic
        $('#btn_submit').click(function(e) {
            e.preventDefault();

            // Check BA presence
            <?php 
            $baExists = !empty($realization['ba_file']) || !empty($realization['ba_file_url']);
            if (!$baExists): 
            ?>
                const baFiles = document.querySelector('input[name="ba_file"]');
                if (!baFiles || baFiles.files.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Upload BA Diperlukan',
                        text: 'Untuk Finalize, Anda wajib mengupload file Berita Acara (BA).',
                        confirmButtonColor: '#204EAB'
                    });
                    return;
                }
            <?php endif; ?>

            Swal.fire({
                title: 'Konfirmasi Update',
                text: 'Update dan Finalize realisasi ini?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#204EAB',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Finalize',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('realizationForm').submit();
                }
            });
        });

        $('#btn_draft').click(function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Simpan sebagai Draft?',
                text: 'Anda bisa melengkapi data nanti.',
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#204EAB',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Simpan Draft',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    let input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'save_as';
                    input.value = 'draft';
                    document.getElementById('realizationForm').appendChild(input);
                    document.getElementById('realizationForm').submit();
                }
            });
        });
    });

    // Global Functions for Adding Items
    window.addSelectedItems = function(category) {
        let modalId = category === 'personnel' ? 'addPersonnelModal' : 'addConsumptionModal';
        let modalEl = document.getElementById(modalId);
        let modal = bootstrap.Modal.getInstance(modalEl);
        
        let checkboxes = document.querySelectorAll(`#${modalId} .add-item-checkbox:checked`);
        
        checkboxes.forEach(cb => {
            let price = cb.dataset.price || 0;
            appendRow(category, cb.dataset.name, cb.dataset.code, price);
            cb.checked = false; 
        });
        
        modal.hide();
        calculateAll();
    }

    window.addTransportItems = function() {
        let modalEl = document.getElementById('addTransportModal');
        let modal = bootstrap.Modal.getInstance(modalEl);

        let checkboxes = document.querySelectorAll('#addTransportModal .add-item-checkbox:checked');
        let emergencyCheck = document.getElementById('check-emergency');
        let emergencySelect = document.getElementById('emergency-type-select');
        let emergencyDetail = document.getElementById('emergency-detail');
        
        // Add Standard Items
        checkboxes.forEach(cb => {
            appendRow('transport', cb.dataset.name, cb.dataset.code);
            cb.checked = false;
        });

        // Add Emergency
        if (emergencyCheck.checked && emergencySelect.value) {
            let name = "EMERGENCY: " + emergencySelect.value;
            let code = emergencySelect.options[emergencySelect.selectedIndex].dataset.code;
            let note = emergencyDetail.value;
            appendRow('transport', name, code, 0, note, true);
            
            // Reset
            emergencyCheck.checked = false;
            emergencySelect.value = "";
            emergencyDetail.value = "";
            $('#emergency-options').hide();
        }

        modal.hide();
        calculateAll();
    }

    window.appendRow = function(category, name, code, price = 0, note = '', isEmergency = false) {
        let tbody = document.querySelector(`#table-${category} tbody`);
        let idx = newItemIndex++;
        
        let qty = 1;
        let subtotal = price * qty;

        let tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.dataset.category = category;
        tr.dataset.subtotal = subtotal;
        
        let priceAttr = '';
        let priceClass = '';
        if (category === 'personnel') {
            priceAttr = 'readonly';
            priceClass = 'bg-light';
        }

        let qtyInputHtml = '';
        let nameInputHtml = '';
        
        if (isEmergency) {
            qtyInputHtml = `
                <input type="hidden" name="extra_items[${idx}][qty]" class="qty-input" value="1">
                <span class="badge bg-light text-dark border px-3 py-2">1</span>
            `;

            let selectedType = "";
            let parts = name.split(':');
            if (parts.length > 1) {
                selectedType = parts[1].trim();
            }

            let options = '<option value="">-- Pilih Tipe --</option>';
            if (typeof EMERGENCY_TYPES !== 'undefined') {
                EMERGENCY_TYPES.forEach(t => {
                    let isSelected = (t.name === selectedType) ? 'selected' : '';
                    options += `<option value="${t.name}" data-code="${t.expense_code}" ${isSelected}>${t.name}</option>`;
                });
            }

            nameInputHtml = `
                <input type="hidden" name="extra_items[${idx}][category]" value="${category}">
                <input type="hidden" name="extra_items[${idx}][item_name]" class="item-name-hidden" value="${name}">
                <div class="mb-1">
                    <span class="badge bg-warning text-dark mb-1">Emergency Cost</span>
                    <select class="form-select form-select-sm emergency-type-select fw-bold" onchange="updateEmergencyRow(this)">
                        ${options}
                    </select>
                </div>
                <input type="text" name="extra_items[${idx}][notes]" class="form-control form-control-sm mt-1" placeholder="Catatan" value="${note}">
                <small class="text-muted fst-italic code-badge">${code || ''}</small>
            `;
        } else {
            qtyInputHtml = `
                <input type="number" name="extra_items[${idx}][qty]" class="form-control text-center qty-input" value="${qty}" min="0">
            `;
            
            nameInputHtml = `
                <input type="hidden" name="extra_items[${idx}][category]" value="${category}">
                <input type="text" name="extra_items[${idx}][item_name]" class="form-control form-control-plaintext fw-bold" value="${name}" readonly>
                <input type="text" name="extra_items[${idx}][notes]" class="form-control form-control-sm mt-1" placeholder="Catatan" value="${note}">
                ${code ? `<small class="text-muted fst-italic">${code}</small>` : ''}
            `;
        }

        let formattedPrice = formatNumber(price);
        let formattedSubtotal = formatNumber(subtotal);
        
        let priceHtml = `
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0">Rp</span>
                <input type="text" name="extra_items[${idx}][price]" class="form-control text-end price-input border-start-0 ${priceClass}" value="${formattedPrice}" ${priceAttr}>
            </div>
        `;
        
        tr.innerHTML = `
            <td>
                ${nameInputHtml}
            </td>
            <td class="text-center align-middle">
                ${qtyInputHtml}
            </td>
            <td>
                ${priceHtml}
            </td>
            <td class="text-end fw-bold subtotal-display align-middle">Rp ${formattedSubtotal}</td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-link text-danger p-0 remove-row"><i class="fas fa-times"></i></button>
            </td>
        `;
        
        tbody.appendChild(tr);
        calculateAll();
    }

    // Helper for emergency row update (needed because I added onchange="updateEmergencyRow(this)")
    window.updateEmergencyRow = function(select) {
        let row = select.closest('tr');
        let selectedOption = select.options[select.selectedIndex];
        let typeName = select.value;
        let expenseCode = selectedOption.dataset.code || '';
        
        let nameInput = row.querySelector('.item-name-hidden');
        if (nameInput) {
            nameInput.value = "EMERGENCY: " + typeName;
        }
        
        let codeBadge = row.querySelector('.code-badge');
        if (codeBadge) {
            codeBadge.innerText = expenseCode;
        }
    }
</script>

<?php include '../views/layouts/footer.php'; ?>
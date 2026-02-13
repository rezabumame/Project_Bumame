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
            <h3 class="fw-bold mb-0" style="color: var(--primary-medical);">Realisasi Harian</h3>
            <p class="text-muted mb-0">Input realisasi untuk project <strong><?php echo htmlspecialchars($rab['nama_project'] ?? ''); ?></strong></p>
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

    <form action="index.php?page=realization_store" method="POST" id="realizationForm" enctype="multipart/form-data">
        <input type="hidden" name="rab_id" value="<?php echo $rab['id']; ?>">
        <input type="hidden" name="project_id" value="<?php echo $rab['project_id']; ?>">
        
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
                                    <label class="form-label fw-bold text-muted small text-uppercase">Tanggal Realisasi <span class="text-danger">*</span></label>
                                    <select name="date" class="form-select form-select-lg bg-light text-primary fw-bold" required>
                                        <option value="">-- Pilih Tanggal --</option>
                                        <?php foreach ($available_dates as $date): ?>
                                            <option value="<?php echo $date; ?>">
                                                <?php echo date('d M Y', strtotime($date)); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <div class="alert alert-info mb-0 py-2 border-0 bg-light-info text-primary small">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Pilih tanggal untuk memuat item dari RAB sebagai template.
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="my-4 text-muted opacity-25">
                            
                            <div class="row g-3">
                                <!-- Lokasi Full Width -->
                                <div class="col-12">
                                    <label class="form-label text-muted small text-uppercase fw-bold">Lokasi (RAB)</label>
                                    <div class="d-flex align-items-center bg-light p-3 rounded border">
                                        <i class="fas fa-map-marker-alt text-danger me-3 fs-5"></i>
                                        <div class="fw-bold text-break">
                                            <?php echo htmlspecialchars($rab['project_location'] ?? '-'); ?>
                                            <div class="small text-muted fw-normal mt-1">
                                                <i class="fas fa-tag me-1"></i>
                                                <?php echo ucwords(str_replace('_', ' ', $rab['location_type'] ?? '-')); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Pax Info Row -->
                                <div class="col-md-6">
                                    <label class="form-label text-muted small text-uppercase fw-bold">
                                        Total Pax (RAB) 
                                        <i class="fas fa-info-circle text-info ms-1" title="Total peserta untuk SEMUA tanggal (bukan per hari)"></i>
                                    </label>
                                    <div class="input-group">
                                        <input type="text" class="form-control bg-light" value="<?php echo number_format($rab['total_participants'] ?? 0, 0, ',', '.'); ?>" readonly>
                                        <span class="input-group-text bg-light text-muted small">All Dates</span>
                                    </div>
                                    <div class="form-text text-muted small">Total target peserta dari semua tanggal.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-primary small text-uppercase">Total Pax (Aktual) <span class="text-danger">*</span></label>
                                    <input type="number" name="actual_participants" id="actual_participants" class="form-control fw-bold border-primary shadow-sm" placeholder="0" min="1" required>
                                    <div class="form-text text-primary small"><i class="fas fa-pencil-alt me-1"></i>Isi manual sesuai kehadiran hari ini.</div>
                                </div>
                            </div>
                            
                            <!-- RAB Info Container Removed (Moved to Step 1 Main View) -->
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
                                    $globalIndex = 0;
                                    foreach ($grouped_items['personnel'] as $item): 
                                    ?>
                                    <tr class="item-row" data-category="personnel">
                                        <td>
                                            <input type="hidden" name="items[<?php echo $globalIndex; ?>][category]" value="personnel">
                                            <input type="text" name="items[<?php echo $globalIndex; ?>][item_name]" class="form-control form-control-plaintext fw-bold" value="<?php echo htmlspecialchars($item['item_name']); ?>" readonly>
                                            <input type="text" name="items[<?php echo $globalIndex; ?>][notes]" class="form-control form-control-sm mt-1" placeholder="Catatan (Nama Petugas, dll)" value="<?php echo htmlspecialchars($item['notes'] ?? ''); ?>">
                                        </td>
                                        <td>
                                            <input type="number" name="items[<?php echo $globalIndex; ?>][qty]" class="form-control text-center qty-input" value="<?php echo round($item['qty']); ?>" min="0">
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-light border-end-0">Rp</span>
                                                <input type="text" name="items[<?php echo $globalIndex; ?>][price]" class="form-control text-end price-input border-start-0 bg-light" value="<?php echo number_format($item['price'], 0, ',', '.'); ?>" readonly>
                                            </div>
                                        </td>
                                        <td class="text-end fw-bold subtotal-display">
                                            Rp <?php echo number_format($item['qty'] * $item['price'], 0, ',', '.'); ?>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-link text-danger p-0 remove-row" title="Hapus"><i class="fas fa-times"></i></button>
                                        </td>
                                    </tr>
                                    <?php $globalIndex++; endforeach; ?>
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
                                            <input type="number" name="doctor_participants" id="doctor_participants" class="form-control fw-bold" placeholder="Jumlah peserta..." min="0">
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
                            <?php if (!empty($grouped_items['vendor'])): ?>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="appendRow('vendor', '', '', 1, 0)">
                                <i class="fas fa-plus me-1"></i> Tambah Item
                            </button>
                            <?php endif; ?>
                        </div>
                        <?php if (empty($grouped_items['vendor'])): ?>
                            <div class="card-body p-4">
                                <div class="alert alert-info mb-0 bg-light-info text-primary border-0">
                                    <i class="fas fa-info-circle me-2"></i>Tidak ada pengajuan Vendor di RAB.
                                </div>
                            </div>
                        <?php else: ?>
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
                                                <input type="hidden" name="items[<?php echo $globalIndex; ?>][category]" value="vendor">
                                                <input type="text" name="items[<?php echo $globalIndex; ?>][item_name]" class="form-control form-control-plaintext fw-bold" value="<?php echo htmlspecialchars($item['item_name']); ?>" readonly>
                                                <input type="text" name="items[<?php echo $globalIndex; ?>][notes]" class="form-control form-control-sm mt-1" placeholder="Catatan" value="<?php echo htmlspecialchars($item['notes'] ?? ''); ?>">
                                            </td>
                                            <td>
                                                <input type="number" name="items[<?php echo $globalIndex; ?>][qty]" class="form-control text-center qty-input" value="<?php echo round($item['qty']); ?>" min="0">
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-light border-end-0">Rp</span>
                                                    <input type="text" name="items[<?php echo $globalIndex; ?>][price]" class="form-control text-end price-input border-start-0 bg-light" value="<?php echo number_format($item['price'], 0, ',', '.'); ?>" readonly>
                                                </div>
                                            </td>
                                            <td class="text-end fw-bold subtotal-display">
                                                Rp <?php echo number_format($item['qty'] * $item['price'], 0, ',', '.'); ?>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-link text-danger p-0 remove-row" title="Hapus"><i class="fas fa-times"></i></button>
                                            </td>
                                        </tr>
                                        <?php $globalIndex++; endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php endif; ?>
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
                            <?php if (empty($grouped_items['transport'])): ?>
                                <div class="alert alert-info m-3 bg-light-info text-primary border-0">
                                    <i class="fas fa-info-circle me-2"></i>Tidak ada pengajuan Transportasi di RAB.
                                </div>
                            <?php endif; ?>
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
                                            $is_emergency = (stripos($item['item_name'], 'emergency') !== false);
                                            $current_type = '';
                                            if ($is_emergency) {
                                                // Extract type: "EMERGENCY: Type"
                                                $parts = explode(':', $item['item_name']);
                                                if (count($parts) > 1) {
                                                    $current_type = trim($parts[1]);
                                                }
                                            }
                                        ?>
                                        <tr class="item-row" data-category="transport">
                                            <td>
                                                <input type="hidden" name="items[<?php echo $globalIndex; ?>][category]" value="transport">
                                                
                                                <?php if ($is_emergency): ?>
                                                    <!-- Emergency Dropdown -->
                                                    <input type="hidden" name="items[<?php echo $globalIndex; ?>][item_name]" class="item-name-hidden" value="<?php echo htmlspecialchars($item['item_name']); ?>">
                                                    <div class="mb-1">
                                                        <span class="badge bg-warning text-dark mb-1">Emergency Cost</span>
                                                        <select class="form-select form-select-sm emergency-type-select fw-bold">
                                                            <option value="">-- Pilih Tipe --</option>
                                                            <?php foreach ($emergency_types as $e): ?>
                                                                <option value="<?php echo $e['name']; ?>" 
                                                                    data-code="<?php echo $e['expense_code']; ?>"
                                                                    <?php echo ($e['name'] == $current_type) ? 'selected' : ''; ?>>
                                                                    <?php echo $e['name']; ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                <?php else: ?>
                                                    <input type="text" name="items[<?php echo $globalIndex; ?>][item_name]" class="form-control form-control-plaintext fw-bold" value="<?php echo htmlspecialchars($item['item_name']); ?>" readonly>
                                                <?php endif; ?>

                                                <input type="text" name="items[<?php echo $globalIndex; ?>][notes]" class="form-control form-control-sm mt-1" placeholder="Catatan" value="<?php echo htmlspecialchars($item['notes'] ?? ''); ?>">
                                            </td>
                                            <td class="text-center align-middle">
                                                <?php if ($is_emergency): ?>
                                                    <input type="hidden" name="items[<?php echo $globalIndex; ?>][qty]" class="qty-input" value="1">
                                                    <span class="badge bg-light text-dark border px-3 py-2">1</span>
                                                <?php else: ?>
                                                    <input type="number" name="items[<?php echo $globalIndex; ?>][qty]" class="form-control text-center qty-input" value="<?php echo round($item['qty']); ?>" min="0">
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-light border-end-0">Rp</span>
                                                    <input type="text" name="items[<?php echo $globalIndex; ?>][price]" class="form-control text-end price-input border-start-0" value="<?php echo number_format($item['price'], 0, ',', '.'); ?>">
                                                </div>
                                            </td>
                                            <td class="text-end fw-bold subtotal-display align-middle">
                                                Rp <?php echo number_format($item['qty'] * $item['price'], 0, ',', '.'); ?>
                                            </td>
                                            <td class="text-center align-middle">
                                                <button type="button" class="btn btn-link text-danger p-0 remove-row"><i class="fas fa-times"></i></button>
                                            </td>
                                        </tr>
                                        <?php $globalIndex++; endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Accommodation Advance Input (AUTO-CALCULATED FROM RAB) -->
                    <!-- Removed as per user request: "ini hilangkan aja harusnya kan dari rab bagian TRANSPORTASI & AKOMODASI" -->
                    <!-- <div class="medical-card mb-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3"><i class="fas fa-money-bill-wave me-2"></i>Uang Muka Akomodasi</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Nominal Uang Muka (Rp)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" name="accommodation_advance" class="form-control currency-input text-end fw-bold" 
                                            value="<?php echo number_format($realization['accommodation_advance'] ?? $rab_accommodation_budget ?? 0, 0, ',', '.'); ?>" 
                                            placeholder="0">
                                    </div>
                                    <div class="form-text">Masukkan jumlah uang muka akomodasi yang telah dibayarkan (jika ada).</div>
                                </div>
                            </div>
                        </div>
                    </div> -->
                    <input type="hidden" name="accommodation_advance" value="0"> <!-- Ignored by controller but kept for form integrity if needed -->

                </div>

                <!-- STEP 5: KONSUMSI -->
                <div id="step-5" class="step-section">
                    <!-- SECTION: KONSUMSI & LAINNYA -->
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
                            <?php if (empty($grouped_items['consumption'])): ?>
                                <div class="alert alert-info m-3 bg-light-info text-primary border-0">
                                    <i class="fas fa-info-circle me-2"></i>Tidak ada pengajuan Konsumsi di RAB.
                                </div>
                            <?php endif; ?>
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
                                                <input type="hidden" name="items[<?php echo $globalIndex; ?>][category]" value="consumption">
                                                <input type="text" name="items[<?php echo $globalIndex; ?>][item_name]" class="form-control form-control-plaintext fw-bold" value="<?php echo htmlspecialchars($item['item_name']); ?>" readonly>
                                                <input type="text" name="items[<?php echo $globalIndex; ?>][notes]" class="form-control form-control-sm mt-1" placeholder="Catatan" value="<?php echo htmlspecialchars($item['notes'] ?? ''); ?>">
                                            </td>
                                            <td>
                                            <input type="number" name="items[<?php echo $globalIndex; ?>][qty]" class="form-control text-center qty-input" value="<?php echo round($item['qty']); ?>" min="0">
                                        </td>
                                            <td>
                                                <?php 
                                                $is_snack_makan = (stripos($item['item_name'], 'Snack Peserta') !== false || stripos($item['item_name'], 'Makan Peserta') !== false);
                                                if ($is_snack_makan): 
                                                ?>
                                                    <input type="hidden" name="items[<?php echo $globalIndex; ?>][price]" class="price-input" value="0">
                                                    <div class="text-center text-muted small fst-italic py-2 bg-light rounded">Logistik</div>
                                                <?php else: ?>
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text bg-light border-end-0">Rp</span>
                                                        <input type="text" name="items[<?php echo $globalIndex; ?>][price]" class="form-control text-end price-input border-start-0" value="<?php echo number_format($item['price'], 0, ',', '.'); ?>">
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end fw-bold subtotal-display">
                                                <?php if ($is_snack_makan): ?>
                                                    -
                                                <?php else: ?>
                                                    Rp <?php echo number_format($item['qty'] * $item['price'], 0, ',', '.'); ?>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-link text-danger p-0 remove-row"><i class="fas fa-times"></i></button>
                                            </td>
                                        </tr>
                                        <?php $globalIndex++; endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Snack Participant -->
                    <div class="p-3 border rounded mb-3 hover-shadow-sm bg-white" data-type="participant">
                        <div class="form-check form-switch">
                            <input class="form-check-input cons-check" type="checkbox" name="cons_snack_participant_check" id="cons_snack_participant_check">
                            <label class="form-check-label fw-bold" for="cons_snack_participant_check">Snack Peserta</label>
                        </div>
                        <div class="mt-3 ps-4 inputs-container d-none">
                            <div class="alert alert-info py-2 small"><i class="fas fa-info-circle me-1"></i>Hanya untuk pencatatan jumlah, biaya Rp 0 di RAB ini.</div>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="small text-muted">Total Quantity</label>
                                    <input type="text" class="form-control cons-qty currency-input" name="cons_snack_participant_qty" placeholder="0">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Makan Siang Peserta -->
                    <div class="p-3 border rounded mb-3 hover-shadow-sm bg-white" data-type="participant">
                        <div class="form-check form-switch">
                            <input class="form-check-input cons-check" type="checkbox" name="cons_lunch_participant_check" id="cons_lunch_participant_check">
                            <label class="form-check-label fw-bold" for="cons_lunch_participant_check">Makan Siang Peserta</label>
                        </div>
                        <div class="mt-3 ps-4 inputs-container d-none">
                            <div class="alert alert-info py-2 small"><i class="fas fa-info-circle me-1"></i>Hanya untuk pencatatan jumlah, biaya Rp 0 di RAB ini.</div>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="small text-muted">Total Quantity</label>
                                    <input type="text" class="form-control cons-qty currency-input" name="cons_lunch_participant_qty" placeholder="0">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="medical-card mb-4">
                        <div class="card-body p-4">
                            <label class="form-label fw-bold text-muted small text-uppercase">Catatan Umum</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Catatan tambahan untuk realisasi hari ini..."></textarea>
                        </div>
                    </div>

                </div>

                <!-- STEP 6: FINALISASI -->
                <div id="step-6" class="step-section">
                    <div class="alert alert-primary mb-4">
                        <h5 class="alert-heading fw-bold"><i class="fas fa-check-circle me-2"></i>Finalisasi Realisasi</h5>
                        <p class="mb-0">Silakan review kembali ringkasan biaya di sebelah kanan. Jika sudah sesuai, upload Berita Acara (BA) dan klik Submit.</p>
                    </div>

                    <!-- Berita Acara Upload -->
                    <div class="medical-card mb-5">
                        <div class="medical-card-header d-flex align-items-center">
                            <div class="section-icon"><i class="fas fa-file-upload"></i></div>
                            <h5 class="mb-0 fw-bold">Berita Acara (BA)</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="alert alert-warning small mb-3">
                                <i class="fas fa-exclamation-circle me-1"></i>
                                Upload file BA (PDF/Image). Wajib diisi untuk Finalize, opsional jika Draft.
                                <br>
                                <span class="fw-bold fst-italic">* Jika Anda sudah upload BA di menu Project Details untuk tanggal ini, Anda tidak perlu upload ulang disini (sistem akan mendeteksi otomatis).</span>
                            </div>
                            <input type="file" name="ba_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                            <div class="form-text">Maksimal 5MB. Format: PDF, JPG, PNG.</div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Sticky Sidebar (Right) -->
            <div class="col-lg-4">
                <div class="sticky-summary">
                    <div class="medical-card">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-4">Ringkasan Biaya</h5>
                            
                            <!-- Subtotals -->
                            <div class="summary-row">
                                <span class="text-muted">Petugas Medis & Lapangan</span>
                                <span class="fw-bold" id="sum-personnel">Rp 0</span>
                            </div>
                            <div class="summary-row">
                                <span class="text-muted">Vendor</span>
                                <span class="fw-bold" id="sum-vendor">Rp 0</span>
                            </div>
                            <div class="summary-row">
                                <span class="text-muted">Transportasi & Akomodasi</span>
                                <span class="fw-bold" id="sum-transport">Rp 0</span>
                            </div>
                            <!-- Accommodation Advance Summary -->
                            <div class="summary-row text-secondary small" id="row-advance" style="display:none;">
                                <span class="ms-2"><i class="fas fa-minus me-1"></i> Uang Muka Akomodasi</span>
                                <span class="fw-bold" id="sum-advance">Rp 0</span>
                            </div>
                            <div class="summary-row text-primary small border-bottom pb-2 mb-2" id="row-net-accommodation" style="display:none;">
                                <span class="ms-2 fw-bold">Net Akomodasi</span>
                                <span class="fw-bold" id="sum-net-accommodation">Rp 0</span>
                            </div>

                            <div class="summary-row">
                                <span class="text-muted">Konsumsi & Lainnya</span>
                                <span class="fw-bold" id="sum-consumption">Rp 0</span>
                            </div>
                            <div class="summary-row" id="summary_doctor_fee_row" style="display: none;">
                                <span class="text-warning fw-bold">Fee Dokter (Overload)</span>
                                <span class="fw-bold text-warning" id="summary_doctor_fee">0</span>
                            </div>
                            
                            <div class="summary-total">
                                <small class="text-muted text-uppercase fw-bold">Grand Total Realisasi</small>
                                <h3 class="text-primary fw-bold mt-2" id="grand-total">Rp 0</h3>
                            </div>

                            <!-- Budget Info -->
                            <div class="bg-light rounded p-3 mt-4 border border-dashed">
                                <h6 class="text-muted small text-uppercase fw-bold mb-3 border-bottom pb-2">Status Anggaran RAB</h6>
                                
                                <div class="summary-row small mb-2">
                                    <span>Total RAB:</span>
                                    <span class="fw-bold">Rp <?php echo number_format($rab['grand_total'], 0, ',', '.'); ?></span>
                                </div>
                                <div class="summary-row small mb-2">
                                    <span>Realisasi Sblmnya:</span>
                                    <span class="fw-bold text-muted">Rp <?php echo number_format($previous_realization_total ?? 0, 0, ',', '.'); ?></span>
                                </div>
                                 <div class="d-flex justify-content-between border-top pt-2 mt-2">
                                    <span class="fw-bold">Sisa Anggaran:</span>
                                    <span class="fw-bold text-success" id="remaining-budget">Rp 0</span>
                                </div>
                                <div id="over-budget-badge" class="badge bg-danger w-100 mt-3 py-2" style="display: none;">
                                    <i class="fas fa-exclamation-circle me-1"></i> OVER BUDGET
                                </div>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="button" class="btn btn-primary-medical" id="btn_next">
                                    Selanjutnya <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                                
                                <button type="submit" name="save_as" value="draft" class="btn btn-outline-secondary rounded-pill fw-bold py-2 d-none action-btn" id="btn_draft">
                                    <i class="fas fa-save me-2"></i>Simpan Draft
                                </button>

                                <button type="submit" name="save_as" value="submitted" class="btn btn-success rounded-pill fw-bold py-2 d-none action-btn" id="btn_submit">
                                    <i class="fas fa-paper-plane me-2"></i>Submit & Finalize
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
    <!-- Container closed in footer -->

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

<script>
    // System Configs
    const DOCTOR_MAX_PATIENT = <?php echo $doctor_max_patient; ?>;
    const DOCTOR_EXTRA_FEE = <?php echo $doctor_extra_fee; ?>;
    const RAB_GRAND_TOTAL = <?php echo $rab['grand_total']; ?>;
    const TOTAL_REALIZED_BEFORE = <?php echo $previous_realization_total ?? 0; ?>;
    const EMERGENCY_TYPES = <?php echo json_encode($emergency_types); ?>;
    
    let newItemIndex = 1000; // Start high to avoid collision with PHP indices

    document.addEventListener('DOMContentLoaded', function() {
        // Emergency Type Change Handler
        document.getElementById('realizationForm').addEventListener('change', function(e) {
            if (e.target.classList.contains('emergency-type-select')) {
                let row = e.target.closest('tr');
                let selectedType = e.target.value;
                let hiddenName = row.querySelector('.item-name-hidden');
                let codeBadge = row.querySelector('.code-badge');
                
                if (hiddenName) {
                    hiddenName.value = "EMERGENCY: " + selectedType;
                }
                
                // Update Code if available
                let selectedOption = e.target.options[e.target.selectedIndex];
                let code = selectedOption.dataset.code;
                if (codeBadge) {
                    codeBadge.innerText = code || '';
                }
            }
        });
        // Modal Triggers (Manual Handling for Reliability)
        document.getElementById('btn-open-personnel-modal').addEventListener('click', function() {
            let modal = new bootstrap.Modal(document.getElementById('addPersonnelModal'));
            modal.show();
        });

        document.getElementById('btn-open-transport-modal').addEventListener('click', function() {
            let modal = new bootstrap.Modal(document.getElementById('addTransportModal'));
            modal.show();
        });

        document.getElementById('btn-open-consumption-modal').addEventListener('click', function() {
            let modal = new bootstrap.Modal(document.getElementById('addConsumptionModal'));
            modal.show();
        });

        // Initial Calculation
        calculateAll();
        
        // Event Delegation for Inputs
        document.getElementById('realizationForm').addEventListener('input', function(e) {
            if (e.target.classList.contains('qty-input') || e.target.classList.contains('price-input')) {
                // Format Price Input
                if (e.target.classList.contains('price-input')) {
                    formatCurrencyInput(e.target);
                }
                
                // Recalculate Row
                let row = e.target.closest('tr');
                calculateRow(row);
                calculateAll();
            }
        });

        // Toggle Consumption Inputs
        document.querySelectorAll('.cons-check').forEach(chk => {
            chk.addEventListener('change', function() {
                const container = this.closest('.p-3').querySelector('.inputs-container');
                if(this.checked) {
                    container.classList.remove('d-none');
                } else {
                    container.classList.add('d-none');
                }
            });
        });

        // Emergency Dropdown Change Listener
        document.getElementById('realizationForm').addEventListener('change', function(e) {
            if (e.target.classList.contains('emergency-type-select')) {
                let row = e.target.closest('tr');
                let selectedOption = e.target.options[e.target.selectedIndex];
                let typeName = e.target.value;
                let expenseCode = selectedOption.dataset.code || '';
                
                // Update hidden item_name
                let nameInput = row.querySelector('.item-name-hidden');
                if (nameInput) {
                    nameInput.value = "EMERGENCY: " + typeName;
                }
                
                // Update code badge
                let codeBadge = row.querySelector('.code-badge');
                if (codeBadge) {
                    codeBadge.innerText = expenseCode;
                }
            }
        });

        // Remove Row
        document.getElementById('realizationForm').addEventListener('click', function(e) {
            if (e.target.closest('.remove-row')) {
                if(confirm('Hapus item ini?')) {
                    e.target.closest('tr').remove();
                    calculateAll();
                }
            }
        });

        // Doctor Participants Input
        document.getElementById('actual_participants').addEventListener('input', calculateAll);
        document.getElementById('doctor_participants').addEventListener('input', calculateAll);
        
        // Accommodation Advance Input
        const advanceInput = document.querySelector('input[name="accommodation_advance"]');
        if (advanceInput) {
            advanceInput.addEventListener('input', function() {
                formatCurrencyInput(this);
                calculateAll();
            });
        }

        // Emergency Checkbox Toggle
        const emergencyCheck = document.getElementById('check-emergency');
        if (emergencyCheck) {
            emergencyCheck.addEventListener('change', function() {
                document.getElementById('emergency-options').style.display = this.checked ? 'block' : 'none';
            });
        }

        // Submit Buttons Logic
        const btnSubmit = document.getElementById('btn_submit');
        const btnDraft = document.getElementById('btn_draft');
        
        if (btnSubmit) {
            btnSubmit.addEventListener('click', function(e) {
                e.preventDefault();

                // Check if BA is uploaded or exists in database
                const baFile = document.querySelector('input[name="ba_file"]');
                const dateSelect = document.querySelector('select[name="date"]');
                const selectedDate = dateSelect ? dateSelect.value : null;
                
                let baExists = false;
                if (selectedDate && typeof EXISTING_BAS !== 'undefined' && EXISTING_BAS[selectedDate] && EXISTING_BAS[selectedDate].status === 'uploaded') {
                    baExists = true;
                }

                if ((!baFile || baFile.files.length === 0) && !baExists) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Upload BA Diperlukan',
                        text: 'Untuk Finalize, Anda wajib mengupload file Berita Acara (BA).',
                        confirmButtonColor: '#204EAB'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Konfirmasi Finalisasi',
                    text: 'Apakah Anda yakin data realisasi sudah benar? Data yang disimpan sebagai FINAL tidak dapat diubah kembali (kecuali oleh admin).',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#204EAB',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Simpan Final',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('realizationForm').submit();
                    }
                });
            });
        }

        if (btnDraft) {
            btnDraft.addEventListener('click', function(e) {
                e.preventDefault();

                Swal.fire({
                    title: 'Simpan sebagai Draft?',
                    text: 'Anda bisa melengkapi data dan upload BA nanti.',
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
        }

        // --- Stepper Logic ---
        let currentStep = 1;
        const totalSteps = 6;

        function showStep(step) {
            // Manage Section Visibility
            document.querySelectorAll('.step-section').forEach(el => el.classList.remove('active'));
            const currentSection = document.getElementById('step-' + step);
            if (currentSection) currentSection.classList.add('active');
            
            // Update Stepper Indicators
            document.querySelectorAll('.stepper-item').forEach(el => {
                el.classList.remove('active', 'completed');
                let s = parseInt(el.dataset.step);
                if(s < step) el.classList.add('completed');
                else if(s === step) el.classList.add('active');
            });

            // Manage Button Visibility
            const btnPrev = document.getElementById('btn_prev');
            const btnNext = document.getElementById('btn_next');
            const btnSubmit = document.getElementById('btn_submit');
            const btnDraft = document.getElementById('btn_draft');

            if(step === 1) {
                if(btnPrev) btnPrev.style.display = 'none';
            } else {
                if(btnPrev) btnPrev.style.display = 'inline-block';
            }

            if(step === totalSteps) {
                if(btnNext) btnNext.style.display = 'none';
                if(btnSubmit) btnSubmit.classList.remove('d-none');
                if(btnDraft) btnDraft.classList.remove('d-none');
            } else {
                if(btnNext) btnNext.style.display = 'inline-block';
                if(btnSubmit) btnSubmit.classList.add('d-none');
                if(btnDraft) btnDraft.classList.add('d-none');
            }
            
            window.scrollTo(0, 0);
        }

        const btnNext = document.getElementById('btn_next');
        if(btnNext) {
            btnNext.addEventListener('click', function() {
                if(validateStep(currentStep)) {
                    currentStep++;
                    showStep(currentStep);
                }
            });
        }

        const btnPrev = document.getElementById('btn_prev');
        if(btnPrev) {
            btnPrev.addEventListener('click', function() {
                currentStep--;
                showStep(currentStep);
            });
        }

        function validateStep(step) {
            if(step === 1) {
                const dateSelect = document.querySelector('select[name="date"]');
                if(dateSelect && dateSelect.value === '') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Tanggal Belum Dipilih',
                        text: 'Silakan pilih tanggal realisasi terlebih dahulu.',
                        confirmButtonColor: '#204EAB'
                    });
                    return false;
                }
            }
            return true;
        }

        // Initial Stepper State
        showStep(currentStep);
    });

    function formatCurrencyInput(input) {
        let value = input.value.replace(/\D/g, '');
        input.value = new Intl.NumberFormat('id-ID').format(value);
    }

    function parseCurrency(str) {
        if (!str) return 0;
        return parseFloat(str.toString().replace(/\./g, '').replace(/,/g, '.')) || 0;
    }

    function calculateRow(row) {
        let qty = parseFloat(row.querySelector('.qty-input').value) || 0;
        let price = parseCurrency(row.querySelector('.price-input').value);
        let subtotal = qty * price;
        
        row.querySelector('.subtotal-display').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(subtotal);
        row.dataset.subtotal = subtotal;
    }

    function calculateAll() {
        let totalPersonnel = 0;
        let totalVendor = 0;
        let totalTransport = 0;
        let totalConsumption = 0;
        let doctorCount = 0;
        let hasDoctor = false;

        // Personnel
        document.querySelectorAll('#table-personnel tr.item-row').forEach(row => {
            let sub = parseFloat(row.dataset.subtotal) || 0;
            // Recalculate if not set (initial load)
            if (!row.dataset.subtotal) {
                let qty = parseFloat(row.querySelector('.qty-input').value) || 0;
                let price = parseCurrency(row.querySelector('.price-input').value);
                sub = qty * price;
                row.dataset.subtotal = sub;
            }
            totalPersonnel += sub;

            // Check for Doctor
            let name = row.querySelector('input[name*="[item_name]"]').value.toLowerCase();
            if (name.includes('dokter')) {
                hasDoctor = true;
                doctorCount += (parseFloat(row.querySelector('.qty-input').value) || 0);
            }
        });
        
        // Vendor
        document.querySelectorAll('#table-vendor tr.item-row').forEach(row => {
            let sub = parseFloat(row.dataset.subtotal) || 0;
            if (!row.dataset.subtotal) {
                let qty = parseFloat(row.querySelector('.qty-input').value) || 0;
                let price = parseCurrency(row.querySelector('.price-input').value);
                sub = qty * price;
                row.dataset.subtotal = sub;
            }
            totalVendor += sub;
        });
        document.getElementById('sum-vendor').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(totalVendor);
        
        // Toggle Doctor Panel
        let doctorWrapper = document.getElementById('doctor-wrapper');
        if (hasDoctor) {
            doctorWrapper.style.display = 'block';
        } else {
            doctorWrapper.style.display = 'none';
            // Reset fields if hidden
            document.getElementById('doctor_participants').value = '';
            document.getElementById('doctor-fee-alert').style.display = 'none';
        }

        // Transport
        document.querySelectorAll('#table-transport tr.item-row').forEach(row => {
            let sub = parseFloat(row.dataset.subtotal) || 0;
            if (!row.dataset.subtotal) {
                let qty = parseFloat(row.querySelector('.qty-input').value) || 0;
                let price = parseCurrency(row.querySelector('.price-input').value);
                sub = qty * price;
                row.dataset.subtotal = sub;
            }
            totalTransport += sub;
        });

        // Accommodation Advance Logic
        const advanceInput = document.querySelector('input[name="accommodation_advance"]');
        let advanceAmount = 0;
        if (advanceInput) {
            advanceAmount = parseCurrency(advanceInput.value);
        }
        
        const rowAdvance = document.getElementById('row-advance');
        const rowNet = document.getElementById('row-net-accommodation');
        const sumAdvance = document.getElementById('sum-advance');
        const sumNet = document.getElementById('sum-net-accommodation');

        if (advanceAmount > 0) {
            rowAdvance.style.display = 'flex';
            rowNet.style.display = 'flex';
            sumAdvance.innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(advanceAmount);
            
            let netAcc = totalTransport - advanceAmount;
            let sign = netAcc < 0 ? '(Lebih Bayar) ' : '';
            sumNet.innerText = sign + 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.abs(netAcc));
            
            if (netAcc < 0) {
                sumNet.classList.remove('text-primary');
                sumNet.classList.add('text-success'); // Refund/Saving
            } else {
                sumNet.classList.remove('text-success');
                sumNet.classList.add('text-primary');
            }
        } else {
            rowAdvance.style.display = 'none';
            rowNet.style.display = 'none';
        }

        // Consumption
        document.querySelectorAll('#table-consumption tr.item-row').forEach(row => {
            let sub = parseFloat(row.dataset.subtotal) || 0;
            if (!row.dataset.subtotal) {
                let qty = parseFloat(row.querySelector('.qty-input').value) || 0;
                let price = parseCurrency(row.querySelector('.price-input').value);
                sub = qty * price;
                row.dataset.subtotal = sub;
            }
            totalConsumption += sub;
        });

        // Doctor Extra Fee
        let doctorFee = calculateDoctorRule(doctorCount);
        
        let grandTotal = totalPersonnel + totalVendor + totalTransport + totalConsumption + doctorFee;

        // Update UI
        document.getElementById('sum-personnel').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(totalPersonnel);
        document.getElementById('sum-vendor').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(totalVendor);
        document.getElementById('sum-transport').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(totalTransport);
        document.getElementById('sum-consumption').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(totalConsumption);
        document.getElementById('summary_doctor_fee').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(doctorFee);
        
        if(doctorFee > 0) {
            document.getElementById('summary_doctor_fee_row').style.display = 'flex';
        } else {
            document.getElementById('summary_doctor_fee_row').style.display = 'none';
        }

        document.getElementById('grand-total').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(grandTotal);

        // Budget Logic
        let remaining = RAB_GRAND_TOTAL - (TOTAL_REALIZED_BEFORE + grandTotal);
        let remainingEl = document.getElementById('remaining-budget');
        remainingEl.innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(remaining);
        
        if (remaining < 0) {
            remainingEl.classList.remove('text-success');
            remainingEl.classList.add('text-danger');
            document.getElementById('over-budget-badge').style.display = 'block';
        } else {
            remainingEl.classList.remove('text-danger');
            remainingEl.classList.add('text-success');
            document.getElementById('over-budget-badge').style.display = 'none';
        }
    }

    function calculateDoctorRule(doctorCountInput) {
        // Can be called by event or manually. If event, re-count doctors.
        let doctorCount = 0;
        if (typeof doctorCountInput === 'number') {
            doctorCount = doctorCountInput;
        } else {
            document.querySelectorAll('#table-personnel tr.item-row').forEach(row => {
                let name = row.querySelector('input[name*="[item_name]"]').value.toLowerCase();
                if (name.includes('dokter')) {
                    doctorCount += (parseFloat(row.querySelector('.qty-input').value) || 0);
                }
            });
        }

        // Use Doctor Participants ONLY (Strict Mode)
        let doctorPaxInput = document.getElementById('doctor_participants').value;
        
        let actual = 0;
        if (doctorPaxInput !== "") {
            actual = parseFloat(doctorPaxInput);
        }

        let capacity = doctorCount * DOCTOR_MAX_PATIENT;
        let fee = 0;

        document.getElementById('capacity-display').innerText = capacity;

        if (doctorCount > 0 && actual > capacity) {
            let excess = actual - capacity;
            fee = excess * DOCTOR_EXTRA_FEE;
            document.getElementById('doctor-fee-alert').style.display = 'block';
            document.getElementById('extra-fee-display').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(fee);
        } else {
            document.getElementById('doctor-fee-alert').style.display = 'none';
        }

        return fee;
    }

    // Adding Items
    function addSelectedItems(category) {
        let modalId = category === 'personnel' ? 'addPersonnelModal' : 'addConsumptionModal';
        let modalEl = document.getElementById(modalId);
        
        // Safe Modal Instance Retrieval
        let modal = bootstrap.Modal.getInstance(modalEl);
        if (!modal) {
            modal = new bootstrap.Modal(modalEl);
        }

        let checkboxes = document.querySelectorAll(`#${modalId} .add-item-checkbox:checked`);
        
        checkboxes.forEach(cb => {
            let price = cb.dataset.price || 0;
            appendRow(category, cb.dataset.name, cb.dataset.code, price);
            cb.checked = false; // Reset
        });
        
        modal.hide();
        calculateAll();
    }

    function addTransportItems() {
        let modalEl = document.getElementById('addTransportModal');
        let modal = bootstrap.Modal.getInstance(modalEl);

        if (!modal) {
            modal = new bootstrap.Modal(modalEl);
        }

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
            document.getElementById('emergency-options').style.display = 'none';
        }

        modal.hide();
        calculateAll();
    }

    function appendRow(category, name, code, price = 0, note = '', isEmergency = false) {
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

            // Extract selected type if provided in name "EMERGENCY: Type"
            let selectedType = "";
            let parts = name.split(':');
            if (parts.length > 1) {
                selectedType = parts[1].trim();
            }

            // Create Dropdown Options
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
                    <select class="form-select form-select-sm emergency-type-select fw-bold">
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

        let formattedPrice = new Intl.NumberFormat('id-ID').format(price);
        let formattedSubtotal = new Intl.NumberFormat('id-ID').format(subtotal);
        
        // Check for Snack/Makan Peserta to hide price
        let isSnackMakan = false;
        if (category === 'consumption' && (name.toLowerCase().includes('snack peserta') || name.toLowerCase().includes('makan peserta') || name.toLowerCase().includes('lunch peserta'))) {
            isSnackMakan = true;
        }

        let priceHtml = '';
        let subtotalHtml = '';

        if (isSnackMakan) {
            priceHtml = `
                <input type="hidden" name="extra_items[${idx}][price]" class="price-input" value="0">
                <div class="text-center text-muted small fst-italic py-2 bg-light rounded">Logistik</div>
            `;
            subtotalHtml = '-';
        } else {
            priceHtml = `
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0">Rp</span>
                    <input type="text" name="extra_items[${idx}][price]" class="form-control text-end price-input border-start-0 ${priceClass}" value="${formattedPrice}" ${priceAttr}>
                </div>
            `;
            subtotalHtml = `Rp ${formattedSubtotal}`;
        }
        
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
            <td class="text-end fw-bold subtotal-display align-middle">${subtotalHtml}</td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-link text-danger p-0 remove-row"><i class="fas fa-times"></i></button>
            </td>
        `;
        
        tbody.appendChild(tr);
    }
</script>
<?php include '../views/layouts/footer.php'; ?>
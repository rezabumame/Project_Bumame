<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<?php
// Pre-process consumption items to populate the form
$cons_mineral = ['check' => false, 'qty' => '', 'price' => '', 'days' => 1];
$cons_lunch_staff = ['check' => false, 'qty' => '', 'price' => '', 'days' => 1];
$cons_snack_participant = ['check' => false, 'qty' => '', 'days' => 1];
$cons_lunch_participant = ['check' => false, 'qty' => '', 'days' => 1];

// Also pre-process Transport items if needed, but they seem to use separate table or logic?
// Let's check transport logic later if needed. Focused on Consumption.
$transport_bbm = ['check' => false, 'nominal' => '', 'cars' => '', 'days' => 1];
$transport_tol = ['check' => false, 'nominal' => '', 'cars' => '', 'days' => 1];
$transport_emergency = ['check' => false, 'nominal' => '', 'notes' => ''];

if (isset($items) && is_array($items)) {
    foreach ($items as $item) {
        if ($item['category'] == 'consumption') {
            if ($item['item_name'] == 'Air Mineral Petugas') {
                $cons_mineral = [
                    'check' => true,
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'days' => $item['days']
                ];
            } elseif ($item['item_name'] == 'Makan Siang Petugas') {
                $cons_lunch_staff = [
                    'check' => true,
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'days' => $item['days']
                ];
            } elseif ($item['item_name'] == 'Snack Peserta') {
                $cons_snack_participant = [
                    'check' => true,
                    'qty' => $item['qty'],
                    'days' => $item['days']
                ];
            } elseif ($item['item_name'] == 'Makan Siang Peserta') {
                $cons_lunch_participant = [
                    'check' => true,
                    'qty' => $item['qty'],
                    'days' => $item['days']
                ];
            }
        } elseif ($item['category'] == 'transport') {
            if ($item['item_name'] == 'BBM' || $item['item_name'] == 'BBM (Bahan Bakar)') {
                $transport_bbm = [
                    'check' => true,
                    'nominal' => $item['price'],
                    'cars' => $item['qty'],
                    'days' => $item['days']
                ];
            } elseif ($item['item_name'] == 'Tol' || $item['item_name'] == 'Biaya Tol') {
                $transport_tol = [
                    'check' => true,
                    'nominal' => $item['price'],
                    'cars' => $item['qty'],
                    'days' => $item['days']
                ];
            } elseif ($item['item_name'] == 'Emergency Cost') {
                $transport_emergency = [
                    'check' => true,
                    'nominal' => $item['price'],
                    'notes' => $item['notes']
                ];
            }
        }
    }
}
?>

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
    <div class="d-flex justify-content-between align-items-center page-header-container">
        <div>
            <h1 class="page-header-title">Edit RAB</h1>
            <p class="page-header-subtitle">Nomor: <?php echo $rab['rab_number']; ?></p>
        </div>
        <a href="index.php?page=rabs_list" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <!-- Stepper -->
    <div class="row justify-content-center mb-4">
        <div class="col-lg-8">
            <div class="stepper-wrapper">
                <div class="stepper-item active" data-step="1">
                    <div class="step-counter">1</div>
                    <div class="step-name">Project Info</div>
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
            </div>
        </div>
    </div>

    <form action="index.php?page=rabs_update" method="POST" id="formRab">
        <input type="hidden" name="id" value="<?php echo $rab['id']; ?>">
        
        <div class="row">
            <!-- Main Content Area -->
            <div class="col-lg-8">
                
                <!-- STEP 1: INFO PROJECT -->
                <div id="step-1" class="step-section active">
                    <div class="medical-card mb-4">
                        <div class="medical-card-header d-flex align-items-center">
                            <div class="section-icon"><i class="fas fa-hospital-user"></i></div>
                            <h5 class="mb-0 fw-bold">Informasi Project</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-muted small text-uppercase">Project</label>
                                    <select class="form-select select2-basic" name="project_id" id="project_id" required>
                                        <option value="">-- Cari Project --</option>
                                        <?php foreach ($projects as $p): ?>
                                            <option value="<?php echo $p['project_id']; ?>" <?php echo ($p['project_id'] == $rab['project_id']) ? 'selected' : ''; ?>>
                                                <?php echo $p['nama_project']; ?> - <?php echo $p['company_name']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text text-primary"><i class="fas fa-info-circle me-1"></i>Hanya project dengan tanggal MCU yang muncul.</div>
                                 </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-muted small text-uppercase">Lokasi MCU</label>
                                    <div class="d-flex gap-3 mt-2 p-2 bg-light rounded">
                                        <div class="form-check">
                                            <input class="form-check-input location-type" type="radio" name="location_type" id="loc_dalam" value="dalam_kota" <?php echo ($rab['location_type'] == 'dalam_kota') ? 'checked' : ''; ?>>
                                            <label class="form-check-label fw-bold" for="loc_dalam">Dalam Kota</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input location-type" type="radio" name="location_type" id="loc_luar" value="luar_kota" <?php echo ($rab['location_type'] == 'luar_kota') ? 'checked' : ''; ?>>
                                            <label class="form-check-label fw-bold" for="loc_luar">Luar Kota</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label fw-bold text-muted small text-uppercase">Tanggal Pelaksanaan</label>
                                    <div id="date_selection_container" class="p-3 bg-light rounded border-dashed text-center">
                                        <span class="text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Memuat tanggal...</span>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-muted small text-uppercase">Estimasi Peserta</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fas fa-users text-primary"></i></span>
                                        <input type="text" class="form-control currency-input fw-bold" name="total_participants" id="total_participants" value="<?php echo number_format($rab['total_participants'], 0, ',', '.'); ?>" placeholder="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: PETUGAS -->
                <div id="step-2" class="step-section">
                    <div class="medical-card mb-4">
                        <div class="medical-card-header d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="section-icon"><i class="fas fa-user-md"></i></div>
                                <h5 class="mb-0 fw-bold">Uraian Petugas</h5>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <!-- Add Personnel Control -->
                            <div class="row g-3 mb-4 bg-light p-3 rounded align-items-end">
                                <div class="col-md-8">
                                    <label class="form-label small fw-bold text-muted">Tambah Posisi Petugas (Bisa pilih lebih dari satu)</label>
                                    <select id="role_selector" class="form-select select2-basic" multiple="multiple" data-placeholder="-- Pilih Posisi --">
                                        <!-- Option value="" removed for multiple select compatibility with placeholder -->
                                        <?php 
                                        $roles = [
                                            'Admin', 'TTV', 'Visus', 'Plebo', 'Dokter', 'Driver', 'Petugas Loading',
                                            'Rontgen', 'EKG', 'Spirometri', 'Audiometri', 'Rectal', 'Treadmill',
                                            'USG Mammae', 'USG Abdomen', 'Pap Smear', 'Feses'
                                        ];
                                        foreach ($roles as $role): 
                                            $key_dalam = "fee_dalam_kota_" . str_replace(' ', '_', $role);
                                            $key_luar = "fee_luar_kota_" . str_replace(' ', '_', $role);
                                            $price_dalam = str_replace('.', '', $fee_settings[$key_dalam] ?? 0);
                                            $price_luar = str_replace('.', '', $fee_settings[$key_luar] ?? 0);
                                        ?>
                                            <option value="<?php echo $role; ?>" 
                                                    data-price-dalam="<?php echo $price_dalam; ?>"
                                                    data-price-luar="<?php echo $price_luar; ?>">
                                                <?php echo $role; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button type="button" class="btn btn-primary-medical w-100 rounded-pill" id="btn_add_personnel">
                                        <i class="fas fa-plus me-2"></i>Tambah
                                    </button>
                                </div>
                            </div>

                            <!-- Personnel Table -->
                            <div class="table-responsive">
                                <table class="table table-custom align-middle">
                                    <thead>
                                        <tr>
                                            <th width="20%">Posisi</th>
                                            <th width="20%">Uraian</th>
                                            <th width="10%">Jml Org</th>
                                            <th width="15%">Fee/Hari</th>
                                            <th width="10%">Hari</th>
                                            <th width="15%">Subtotal</th>
                                            <th width="5%"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="personnel_table_body">
                                        <tr id="empty_personnel_row">
                                            <td colspan="6" class="text-center text-muted py-4">Belum ada petugas dipilih</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">
                                <label class="form-label small fw-bold text-muted">Catatan Petugas</label>
                                <textarea class="form-control" name="notes_personnel" rows="2" placeholder="Contoh: Dokter standby jam 08.00"><?php echo htmlspecialchars($rab['personnel_notes'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 3: VENDOR -->
                <div id="step-3" class="step-section">
                    <div class="medical-card mb-4">
                        <div class="medical-card-header d-flex align-items-center">
                            <div class="section-icon"><i class="fas fa-store"></i></div>
                            <h5 class="mb-0 fw-bold">Kebutuhan Vendor (External)</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="table-responsive">
                                <table class="table table-custom align-middle" id="vendor_table">
                                    <thead>
                                        <tr>
                                            <th width="25%">Item</th>
                                            <th width="10%">Qty</th>
                                            <th width="15%">Hari</th>
                                            <th width="20%">Harga Satuan</th>
                                            <th width="20%">Subtotal</th>
                                            <th width="10%">Ket</th>
                                        </tr>
                                    </thead>
                                    <tbody id="vendor_table_body">
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-3">Memuat data vendor...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 4: TRANSPORTASI -->
                <div id="step-4" class="step-section">
                    <div class="medical-card mb-4">
                        <div class="medical-card-header d-flex align-items-center">
                            <div class="section-icon"><i class="fas fa-ambulance"></i></div>
                            <h5 class="mb-0 fw-bold">Transportasi</h5>
                        </div>
                        <div class="card-body p-4">
                            
                            <!-- BBM -->
                            <div class="p-3 border rounded mb-3 hover-shadow-sm">
                                <div class="form-check form-switch">
                                    <input class="form-check-input transport-check" type="checkbox" name="transport_bbm_check" id="transport_bbm_check" <?php echo $transport_bbm['check'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label fw-bold" for="transport_bbm_check">BBM (Bahan Bakar)</label>
                                </div>
                                <div class="mt-3 ps-4 inputs-container <?php echo $transport_bbm['check'] ? '' : 'd-none'; ?>">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="small text-muted">Nominal per Mobil</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white">Rp</span>
                                                <input type="text" class="form-control transport-nominal currency-input" name="transport_bbm_nominal" id="transport_bbm_nominal" placeholder="0" value="<?php echo number_format((float)$transport_bbm['nominal'], 0, ',', '.'); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="small text-muted">Jumlah Mobil</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control transport-qty currency-input" name="transport_bbm_cars" id="transport_bbm_cars" placeholder="0" value="<?php echo $transport_bbm['cars']; ?>">
                                                <span class="input-group-text bg-white">Unit</span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="small text-muted">Jumlah Hari</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control transport-days currency-input" name="transport_bbm_days" id="transport_bbm_days" value="<?php echo $transport_bbm['days']; ?>" placeholder="0">
                                                <span class="input-group-text bg-white">Hari</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tol -->
                            <div class="p-3 border rounded mb-3 hover-shadow-sm">
                                <div class="form-check form-switch">
                                    <input class="form-check-input transport-check" type="checkbox" name="transport_tol_check" id="transport_tol_check" <?php echo $transport_tol['check'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label fw-bold" for="transport_tol_check">Biaya Tol</label>
                                </div>
                                <div class="mt-3 ps-4 inputs-container <?php echo $transport_tol['check'] ? '' : 'd-none'; ?>">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="small text-muted">Nominal per Mobil</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white">Rp</span>
                                                <input type="text" class="form-control transport-nominal currency-input" name="transport_tol_nominal" id="transport_tol_nominal" placeholder="0" value="<?php echo number_format((float)$transport_tol['nominal'], 0, ',', '.'); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="small text-muted">Jumlah Mobil</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control transport-qty currency-input" name="transport_tol_cars" id="transport_tol_cars" placeholder="0" value="<?php echo $transport_tol['cars']; ?>">
                                                <span class="input-group-text bg-white">Unit</span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="small text-muted">Jumlah Hari</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control transport-days currency-input" name="transport_tol_days" id="transport_tol_days" value="<?php echo $transport_tol['days']; ?>" placeholder="0">
                                                <span class="input-group-text bg-white">Hari</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Emergency -->
                            <div class="p-3 border rounded mb-3 hover-shadow-sm" data-type="emergency">
                                <div class="form-check form-switch">
                                    <input class="form-check-input transport-check" type="checkbox" name="transport_emergency_check" id="transport_emergency_check" <?php echo $transport_emergency['check'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label fw-bold" for="transport_emergency_check">Emergency Cost</label>
                                </div>
                                <div class="mt-3 ps-4 inputs-container <?php echo $transport_emergency['check'] ? '' : 'd-none'; ?>">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="small text-muted">Total Nominal</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white">Rp</span>
                                                <input type="text" class="form-control transport-nominal currency-input" name="transport_emergency_nominal" id="transport_emergency_nominal" placeholder="0" value="<?php echo number_format((float)$transport_emergency['nominal'], 0, ',', '.'); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="small text-muted">Keterangan</label>
                                            <input type="text" class="form-control" name="transport_emergency_notes" id="transport_emergency_notes" placeholder="Keperluan..." value="<?php echo htmlspecialchars($transport_emergency['notes']); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- STEP 5: KONSUMSI -->
                <div id="step-5" class="step-section">
                    <div class="medical-card mb-4">
                        <div class="medical-card-header d-flex align-items-center">
                            <div class="section-icon"><i class="fas fa-utensils"></i></div>
                            <h5 class="mb-0 fw-bold">Konsumsi</h5>
                        </div>
                        <div class="card-body p-4">
                            
                            <!-- Mineral -->
                            <div class="p-3 border rounded mb-3 hover-shadow-sm">
                                <div class="form-check form-switch">
                                    <input class="form-check-input cons-check" type="checkbox" name="cons_mineral_check" id="cons_mineral_check" <?php echo $cons_mineral['check'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label fw-bold" for="cons_mineral_check">Air Mineral Petugas</label>
                                </div>
                                <div class="mt-3 ps-4 inputs-container <?php echo $cons_mineral['check'] ? '' : 'd-none'; ?>">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="small text-muted">Qty per Hari</label>
                                            <input type="text" class="form-control cons-qty currency-input" name="cons_mineral_qty" id="cons_mineral_qty" placeholder="0" value="<?php echo $cons_mineral['qty']; ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="small text-muted">Harga Satuan</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white">Rp</span>
                                                <input type="text" class="form-control cons-price currency-input" name="cons_mineral_price" id="cons_mineral_price" placeholder="0" value="<?php echo number_format((float)$cons_mineral['price'], 0, ',', '.'); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="small text-muted">Jumlah Hari</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control cons-days currency-input" name="cons_mineral_days" id="cons_mineral_days" value="<?php echo $cons_mineral['days']; ?>" placeholder="0">
                                                <span class="input-group-text bg-white">Hari</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Makan Siang Petugas -->
                            <div class="p-3 border rounded mb-3 hover-shadow-sm">
                                <div class="form-check form-switch">
                                    <input class="form-check-input cons-check" type="checkbox" name="cons_lunch_staff_check" id="cons_lunch_staff_check" <?php echo $cons_lunch_staff['check'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label fw-bold" for="cons_lunch_staff_check">Makan Siang Petugas</label>
                                </div>
                                <div class="mt-3 ps-4 inputs-container <?php echo $cons_lunch_staff['check'] ? '' : 'd-none'; ?>">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="small text-muted">Qty per Hari</label>
                                            <input type="text" class="form-control cons-qty currency-input" name="cons_lunch_staff_qty" id="cons_lunch_staff_qty" placeholder="0" value="<?php echo $cons_lunch_staff['qty']; ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="small text-muted">Harga Satuan</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white">Rp</span>
                                                <input type="text" class="form-control cons-price currency-input" name="cons_lunch_staff_price" id="cons_lunch_staff_price" placeholder="0" value="<?php echo number_format((float)$cons_lunch_staff['price'], 0, ',', '.'); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="small text-muted">Jumlah Hari</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control cons-days currency-input" name="cons_lunch_staff_days" id="cons_lunch_staff_days" value="<?php echo $cons_lunch_staff['days']; ?>" placeholder="0">
                                                <span class="input-group-text bg-white">Hari</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Snack Participant -->
                            <div class="p-3 border rounded mb-3 hover-shadow-sm" data-type="participant">
                                <div class="form-check form-switch">
                                    <input class="form-check-input cons-check" type="checkbox" name="cons_snack_participant_check" id="cons_snack_participant_check" <?php echo $cons_snack_participant['check'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label fw-bold" for="cons_snack_participant_check">Snack Peserta</label>
                                </div>
                                <div class="mt-3 ps-4 inputs-container <?php echo $cons_snack_participant['check'] ? '' : 'd-none'; ?>">
                                    <div class="alert alert-info py-2 small"><i class="fas fa-info-circle me-1"></i>Hanya untuk pencatatan jumlah, biaya Rp 0 di RAB ini.</div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="small text-muted">Total Quantity</label>
                                            <input type="text" class="form-control cons-qty currency-input" name="cons_snack_participant_qty" id="cons_snack_participant_qty" placeholder="0" value="<?php echo number_format((float)$cons_snack_participant['qty'], 0, ',', '.'); ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="small text-muted">Jumlah Hari</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control cons-days currency-input" name="cons_snack_participant_days" id="cons_snack_participant_days" value="<?php echo $cons_snack_participant['days']; ?>" placeholder="0">
                                                <span class="input-group-text bg-white">Hari</span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php 
                                    $snack_items = [];
                                    if (isset($project_info['snack_items'])) {
                                        $decoded = json_decode($project_info['snack_items'], true);
                                        if (is_array($decoded)) $snack_items = $decoded;
                                    }
                                    if (!empty($snack_items)): 
                                    ?>
                                    <div class="mt-2 p-2 bg-light rounded border small">
                                        <strong>Menu Snack:</strong>
                                        <ul class="mb-0 ps-0 list-unstyled">
                                            <?php foreach($snack_items as $item): ?>
                                                <li class="mb-1">
                                                    <?php 
                                                    if(is_array($item)) {
                                                        echo '<div class="d-flex align-items-center mb-1">' .
                                                             '<i class="fas fa-check-circle text-success me-2" style="font-size: 0.8em;"></i>' .
                                                             '<span class="fw-medium text-dark">' . ($item['item'] ?? '-') . '</span>' . 
                                                             (!empty($item['qty']) ? ' <span class="badge bg-secondary rounded-pill ms-1">'.$item['qty'].' pax</span>' : '') . 
                                                             (!empty($item['price']) ? ' <span class="text-muted ms-1">@ Rp '.number_format($item['price'],0,',','.').'</span>' : '') . 
                                                             (!empty($item['total']) ? ' <span class="fw-bold text-dark ms-1">= Rp '.number_format($item['total'],0,',','.').'</span>' : '') .
                                                             '</div>';
                                                    } else {
                                                        echo '<div class="d-flex align-items-center mb-1"><i class="fas fa-check-circle text-success me-2" style="font-size: 0.8em;"></i><span>' . htmlspecialchars($item) . '</span></div>';
                                                    }
                                                    ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Makan Siang Peserta -->
                            <div class="p-3 border rounded mb-3 hover-shadow-sm" data-type="participant">
                                <div class="form-check form-switch">
                                    <input class="form-check-input cons-check" type="checkbox" name="cons_lunch_participant_check" id="cons_lunch_participant_check" <?php echo $cons_lunch_participant['check'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label fw-bold" for="cons_lunch_participant_check">Makan Siang Peserta</label>
                                </div>
                                <div class="mt-3 ps-4 inputs-container <?php echo $cons_lunch_participant['check'] ? '' : 'd-none'; ?>">
                                    <div class="alert alert-info py-2 small"><i class="fas fa-info-circle me-1"></i>Hanya untuk pencatatan jumlah, biaya Rp 0 di RAB ini.</div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="small text-muted">Total Quantity</label>
                                            <input type="text" class="form-control cons-qty currency-input" name="cons_lunch_participant_qty" id="cons_lunch_participant_qty" placeholder="0" value="<?php echo number_format((float)$cons_lunch_participant['qty'], 0, ',', '.'); ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="small text-muted">Jumlah Hari</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control cons-days currency-input" name="cons_lunch_participant_days" id="cons_lunch_participant_days" value="<?php echo $cons_lunch_participant['days']; ?>" placeholder="0">

                                                <span class="input-group-text bg-white">Hari</span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php 
                                    $lunch_items = [];
                                    if (isset($project_info['lunch_items'])) {
                                        $decoded = json_decode($project_info['lunch_items'], true);
                                        if (is_array($decoded)) $lunch_items = $decoded;
                                    }
                                    if (!empty($lunch_items)): 
                                    ?>
                                    <div class="mt-2 p-2 bg-light rounded border small">
                                        <strong class="d-block mb-2 text-primary"><i class="fas fa-utensils me-2"></i>Menu Makan Siang Project:</strong>
                                        <ul class="mb-0 ps-0 list-unstyled">
                                            <?php foreach($lunch_items as $item): ?>
                                                <li class="mb-1">
                                                    <?php 
                                                    if(is_array($item)) {
                                                        echo '<div class="d-flex align-items-center mb-1">' .
                                                             '<i class="fas fa-check-circle text-success me-2" style="font-size: 0.8em;"></i>' .
                                                             '<span class="fw-medium text-dark">' . ($item['item'] ?? '-') . '</span>' . 
                                                             (!empty($item['qty']) ? ' <span class="badge bg-secondary rounded-pill ms-1">'.$item['qty'].' pax</span>' : '') . 
                                                             (!empty($item['price']) ? ' <span class="text-muted ms-1">@ Rp '.number_format($item['price'],0,',','.').'</span>' : '') . 
                                                             (!empty($item['total']) ? ' <span class="fw-bold text-dark ms-1">= Rp '.number_format($item['total'],0,',','.').'</span>' : '') .
                                                             '</div>';
                                                    } else {
                                                        echo '<div class="d-flex align-items-center mb-1"><i class="fas fa-check-circle text-success me-2" style="font-size: 0.8em;"></i><span>' . htmlspecialchars($item) . '</span></div>';
                                                    }
                                                    ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <?php endif; ?>
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
                            <h5 class="fw-bold mb-4">Summary RAB</h5>
                            
                            <!-- Subtotals -->
                            <div class="summary-row">
                                <span class="text-muted">Total Petugas</span>
                                <span class="fw-bold" id="summary_personnel_display">0</span>
                                <input type="hidden" name="summary_personnel" id="summary_personnel" value="0">
                            </div>
                            <div class="summary-row">
                                <span class="text-muted">Total Vendor</span>
                                <span class="fw-bold" id="summary_vendor_display">0</span>
                                <input type="hidden" name="summary_vendor" id="summary_vendor" value="0">
                            </div>
                            <div class="summary-row">
                                <span class="text-muted">Total Transport</span>
                                <span class="fw-bold" id="summary_transport_display">0</span>
                                <input type="hidden" name="summary_transport" id="summary_transport" value="0">
                            </div>
                            <div class="summary-row">
                                <span class="text-muted">Total Konsumsi</span>
                                <span class="fw-bold" id="summary_consumption_display">0</span>
                                <input type="hidden" name="summary_consumption" id="summary_consumption" value="0">
                            </div>

                            <!-- Grand Total -->
                            <div class="summary-total text-center">
                                <small class="text-muted text-uppercase fw-bold">Grand Total Anggaran</small>
                                <h3 class="text-primary fw-bold mt-2" id="grand_total_display">Rp 0</h3>
                                <input type="hidden" name="grand_total" id="grand_total" value="0">
                            </div>

                            <hr class="my-4">

                            <!-- Navigation Buttons -->
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-primary-medical" id="btn_next">
                                    Selanjutnya <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                                
                                <button type="submit" class="btn btn-success rounded-pill fw-bold py-2 d-none" id="btn_submit">
                                    <i class="fas fa-save me-2"></i>Update RAB
                                </button>
                                
                                <button type="submit" name="action" value="draft" class="btn btn-outline-secondary rounded-pill fw-bold py-2 d-none" id="btn_draft">
                                    <i class="fas fa-save me-2"></i>Update as Draft
                                </button>

                                <button type="button" class="btn btn-light rounded-pill text-muted" id="btn_prev" style="display: none;">
                                    <i class="fas fa-arrow-left me-2"></i>Kembali
                                </button>
                            </div>

                        </div>
                    </div>

                    <!-- Debug Info (Optional, can be hidden) -->
                    <div class="alert alert-warning mt-3 small d-none">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        Pastikan data diisi lengkap sebelum submit.
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
// Data Injection
var savedDates = <?php echo $rab['selected_dates'] ?: '[]'; ?>;
var rabId = <?php echo $rab['id']; ?>;
var rabIdProject = "<?php echo $rab['project_id']; ?>";
var savedItems = <?php echo json_encode($items); ?>;

$(document).ready(function() {
    // 1. Init Select2
    $('.select2-basic').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });

    // 2. Stepper Logic
    let currentStep = 1;
    const totalSteps = 5;

    function showStep(step) {
        $('.step-section').removeClass('active');
        $('#step-' + step).addClass('active');
        
        // Update Stepper UI
        $('.stepper-item').removeClass('active completed');
        for(let i=1; i<=totalSteps; i++) {
            if(i < step) {
                $('.stepper-item[data-step="'+i+'"]').addClass('completed');
            } else if(i === step) {
                $('.stepper-item[data-step="'+i+'"]').addClass('active');
            }
        }

        // Update Buttons
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
        // Simple validation
        if(step === 1) {
            if($('#project_id').val() === '') {
                Swal.fire('Error', 'Silakan pilih Project terlebih dahulu', 'error');
                return false;
            }
            if($('.project-date-check:checked').length === 0) {
                 Swal.fire('Error', 'Silakan pilih minimal satu tanggal pelaksanaan', 'error');
                 return false;
            }
        }
        return true;
    }

    // 3. Project Selection Logic (AJAX)
    // Trigger automatically on load if project_id exists
    function loadProjectDates(projectId) {
        if(projectId) {
            $.ajax({
                url: 'index.php?page=rabs_get_project_dates',
                type: 'GET',
                data: { project_id: projectId, exclude_rab_id: rabId },
                dataType: 'json',
                success: function(data) {
                    let html = '';
                    // Combine available dates with saved dates (ensure unique)
                    let availableDates = data.dates || [];
                    
                    // Add saved dates if not in available (because they are used by this RAB)
                    savedDates.forEach(d => {
                        if (!availableDates.includes(d)) {
                            availableDates.push(d);
                        }
                    });
                    
                    // Sort dates
                    availableDates.sort();

                    const dates = availableDates;
                    
                    if(dates.length > 0) {
                        html += '<div class="d-flex flex-wrap gap-2 justify-content-center">';
                        dates.forEach(function(date) {
                            // Check if this date is in savedDates
                            const isChecked = savedDates.includes(date) ? 'checked' : '';
                            html += `
                                <input type="checkbox" class="btn-check project-date-check" name="dates[]" value="${date}" id="date_${date}" ${isChecked} autocomplete="off">
                                <label class="btn btn-outline-primary fw-bold rounded-pill px-4" for="date_${date}">
                                    <i class="fas fa-calendar-day me-2"></i>${formatDateIndo(date)}
                                </label>
                            `;
                        });
                        html += '</div>';
                        
                        window.projectTotalDays = data.total_days || dates.length;
                        window.projectTotalParticipants = parseInt(data.total_participants) || 0;

                        // Count initial checked
                        const initialChecked = savedDates.length;
                        updateTotalDays(initialChecked);
                    } else {
                        html = '<p class="text-danger mb-0">Tidak ada tanggal MCU yang tersedia.</p>';
                        window.projectTotalDays = 0;
                        window.projectTotalParticipants = 0;
                        updateTotalDays(0);
                    }
                    $('#date_selection_container').html(html);

                    // Update Vendor Table (Only if Project ID changed from saved one)
                    // If same project, prefillData() handles it from savedItems
                    // Logic: If we have savedItems for vendor, use them. Otherwise load from Project.
                    const vendorBody = $('#vendor_table_body');
                    vendorBody.empty();
                    
                    // Filter saved items for vendors
                    const savedVendorItems = savedItems.filter(item => item.category === 'vendor');
                    
                    // Check if we are loading the original project (not a changed project ID)
                    // If projectId matches the one in PHP $rab['project_id'], we might want to use saved items.
                    // But savedItems are already loaded from DB for this RAB.
                    // So if savedVendorItems > 0, we should use them.
                    
                    if (savedVendorItems.length > 0 && projectId == rabIdProject) {
                         savedVendorItems.forEach(function(item, index) {
                            let qty = parseFloat(item.qty) || 0;
                            let price = parseFloat(item.price) || 0;
                            let days = parseFloat(item.days) || (window.totalDays || 1);
                            
                            addVendorRow(index, item.item_name, qty, days, price, item.notes);
                        });
                    } else {
                        // Load from Fresh Project Data
                        const vendors = data.vendor_allocations || [];
                        if(vendors.length > 0) {
                            vendors.forEach(function(item, index) {
                                const days = window.totalDays || 1;
                                const qty = parseFloat(item.participant_count) || 0;
                                const price = 0; // Default 0
                                const note = item.assigned_vendor_name ? `${item.assigned_vendor_name}` : (item.notes || '');
                                
                                addVendorRow(index, item.exam_type, qty, days, price, note);
                            });
                        } else {
                             vendorBody.html('<tr><td colspan="6" class="text-center text-muted py-3">Tidak ada data vendor dari database.</td></tr>');
                        }
                    }

                    calculateAll();
                },
                error: function() {
                    $('#date_selection_container').html('<p class="text-danger">Gagal mengambil tanggal.</p>');
                }
            });
        } else {
            $('#date_selection_container').html('<span class="text-muted"><i class="fas fa-calendar-times me-2"></i>Pilih Project terlebih dahulu</span>');
            updateTotalDays(0);
        }
    }

    $('#project_id').change(function() {
        const projectId = $(this).val();
        // If changing project, clear saved dates so we don't pre-select wrong dates
        // Only keep savedDates on initial load
        if (projectId != "<?php echo $rab['project_id']; ?>") {
             savedDates = [];
        } else {
             savedDates = <?php echo $rab['selected_dates'] ?: '[]'; ?>;
        }
        loadProjectDates(projectId);
    });

    // Initial Load
    loadProjectDates($('#project_id').val());

    // Helper: Format Date
    function formatDateIndo(dateStr) {
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        return new Date(dateStr).toLocaleDateString('id-ID', options);
    }

    // Helper: Update Total Days
    function updateTotalDays(count) {
        window.totalDays = count;
        $('.days-display').text(count);
        
        // Update Personnel Days (Skip Petugas Loading)
        $('.personnel-days').each(function() {
            const row = $(this).closest('.personnel-row');
            const role = row.find('input[name*="[role]"]').val(); // Use name selector as ID might be dynamic
            // Or get it from text? No, hidden input is reliable.
            // Wait, in edit.php, row structure might be slightly different?
            // Let's check addPersonnelRow in edit.php to be sure.
            // But usually input[name*="[role]"] works if name is personnel[X][role]
            
            // Safer to check value directly if possible, or assume same structure.
            // In create.php: <input type="hidden" name="personnel[${personnelIndex}][role]" value="${role}">
            
            // Let's assume standard structure.
             if (role !== 'Petugas Loading') {
                 $(this).val(count);
             }
        });
        
        // Update other input fields
        $('.transport-days, .cons-days, .v-days').val(count);

        calculateAll();
    }

    // Monitor Date Checkboxes
    $(document).on('change', '.project-date-check', function() {
        const checkedCount = $('.project-date-check:checked').length;
        updateTotalDays(checkedCount);
    });

    // 4. Personnel Logic (Dynamic Rows)
    let personnelIndex = 0;
    
    // Function to add row (reused)
    function addPersonnelRow(role, qty, price, notes = '', days = null) {
        // Use provided days or global totalDays or 1
        const currentDays = days !== null ? days : (window.totalDays || 1);

        const rowHtml = `
            <tr class="personnel-row" id="p_row_${personnelIndex}">
                <td>
                    <span class="fw-bold text-primary">${role}</span>
                    <input type="hidden" name="personnel[${personnelIndex}][role]" value="${role}">
                    <input type="hidden" name="personnel[${personnelIndex}][selected]" value="1">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" name="personnel[${personnelIndex}][notes]" value="${notes}" placeholder="Keterangan">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm p-qty currency-input" name="personnel[${personnelIndex}][qty]" value="${qty}">
                </td>
                <td>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0">Rp</span>
                        <input type="text" class="form-control text-end price-input currency-input border-start-0" name="personnel[${personnelIndex}][price]" value="${formatNumber(price)}">
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control form-control-sm text-center personnel-days currency-input" name="personnel[${personnelIndex}][days]" value="${currentDays}">
                        <span class="input-group-text bg-light">Hari</span>
                    </div>
                </td>
                <td class="fw-bold text-end">
                    Rp <span class="p-subtotal">0</span>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-personnel" data-id="${personnelIndex}">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        
        $('#personnel_table_body').append(rowHtml);
        personnelIndex++;
        
        // Remove empty row if exists
        $('#empty_personnel_row').remove();
        
        calculateAll();
    }

    // Function to add vendor row
    function addVendorRow(index, item, qty, days, price, notes) {
        const rowHtml = `
            <tr class="vendor-row">
                <td>
                    <span class="fw-bold text-dark">${item}</span>
                    <input type="hidden" name="vendor[${index}][item_name]" value="${item}">
                    <input type="hidden" name="vendor[${index}][notes]" value="${notes}">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm text-center vendor-qty" name="vendor[${index}][qty]" value="${formatNumber(qty)}" readonly>
                </td>
                <td>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control form-control-sm text-center v-days" name="vendor[${index}][days]" value="${days}" readonly>
                        <span class="input-group-text bg-light">Hari</span>
                    </div>
                </td>
                <td>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0">Rp</span>
                        <input type="text" class="form-control text-end vendor-price border-start-0" name="vendor[${index}][price]" value="${formatNumber(price)}" readonly>
                    </div>
                </td>
                <td class="fw-bold text-end">
                    Rp <span class="vendor-subtotal">0</span>
                </td>
                <td>
                    <small class="text-muted">${notes}</small>
                </td>
            </tr>
        `;
        $('#vendor_table_body').append(rowHtml);
    }

    $('#btn_add_personnel').click(function() {
        const roleSelect = $('#role_selector');
        const selectedOptions = roleSelect.find(':selected');
        
        // Filter out empty value if "Pilih Posisi" is somehow selected
        const validOptions = selectedOptions.filter(function() {
            return this.value !== "";
        });
        
        if(validOptions.length === 0) {
            Swal.fire('Info', 'Pilih posisi petugas terlebih dahulu', 'info');
            return;
        }

        validOptions.each(function() {
            const role = $(this).val();
            const priceDalam = parseFloat($(this).data('price-dalam')) || 0;
            const priceLuar = parseFloat($(this).data('price-luar')) || 0;
            const locationType = $('input[name="location_type"]:checked').val();
            const price = (locationType === 'luar_kota') ? priceLuar : priceDalam;

            let days = null;
            if (role === 'Petugas Loading') {
                days = 1;
            }

            addPersonnelRow(role, 1, price, '', days);
        });
        
        // Reset selector
        roleSelect.val(null).trigger('change');
    });

    $(document).on('click', '.btn-remove-personnel', function() {
        const id = $(this).data('id');
        $('#p_row_' + id).remove();
        calculateAll();
        
        if($('.personnel-row').length === 0) {
            $('#personnel_table_body').html('<tr id="empty_personnel_row"><td colspan="6" class="text-center text-muted py-4">Belum ada petugas dipilih</td></tr>');
        }
    });

    $(document).on('input', '.p-qty', function() {
        calculateAll();
    });

    $('input[name="location_type"]').change(function() {
        // Recalculate prices if location changes
        calculateAll();
    });

    // 5. Pre-fill Data
    function prefillData() {
        // Personnel
        savedItems.forEach(item => {
            // Fix: Parse as float to remove potential decimal .00 from DB strings
            let qty = parseFloat(item.qty) || 0;
            let price = parseFloat(item.price) || 0;
            let days = parseFloat(item.days) || (window.totalDays || 1);

            if(item.category === 'personnel') {
                // If price is 0 (invalid/missing), try to fetch from system config based on Role
                if (price === 0) {
                    const option = $(`#role_selector option[value="${item.item_name}"]`);
                    if (option.length > 0) {
                        const priceDalam = parseFloat(option.data('price-dalam')) || 0;
                        const priceLuar = parseFloat(option.data('price-luar')) || 0;
                        const locationType = $('input[name="location_type"]:checked').val();
                        price = (locationType === 'luar_kota') ? priceLuar : priceDalam;
                    }
                }
                addPersonnelRow(item.item_name, qty, price, item.notes, days);
            } else if (item.category === 'transport') {
                if(item.item_name === 'BBM' || item.item_name === 'BBM (Bahan Bakar)') {
                    $('#transport_bbm_check').prop('checked', true).trigger('change');
                    $('#transport_bbm_cars').val(formatNumber(qty));
                    $('#transport_bbm_nominal').val(formatNumber(price));
                    $('#transport_bbm_days').val(formatNumber(days));
                } else if (item.item_name === 'Tol' || item.item_name === 'Biaya Tol') {
                    $('#transport_tol_check').prop('checked', true).trigger('change');
                    $('#transport_tol_cars').val(formatNumber(qty));
                    $('#transport_tol_nominal').val(formatNumber(price));
                    $('#transport_tol_days').val(formatNumber(days));
                } else if (item.item_name === 'Emergency Cost') {
                    $('#transport_emergency_check').prop('checked', true).trigger('change');
                    $('#transport_emergency_nominal').val(formatNumber(price)); 
                    $('#transport_emergency_notes').val(item.notes);
                }
            } else if (item.category === 'consumption') {
                if(item.item_name === 'Air Mineral Petugas') {
                    $('#cons_mineral_check').prop('checked', true).trigger('change');
                    $('#cons_mineral_qty').val(formatNumber(qty));
                    $('#cons_mineral_price').val(formatNumber(price));
                    $('#cons_mineral_days').val(days);
                } else if (item.item_name === 'Makan Siang Petugas') {
                    $('#cons_lunch_staff_check').prop('checked', true).trigger('change');
                    $('#cons_lunch_staff_qty').val(formatNumber(qty));
                    $('#cons_lunch_staff_price').val(formatNumber(price));
                    $('#cons_lunch_staff_days').val(days);
                } else if (item.item_name === 'Snack Peserta') {
                    $('#cons_snack_participant_check').prop('checked', true).trigger('change');
                    $('#cons_snack_participant_qty').val(formatNumber(qty));
                    $('#cons_snack_participant_days').val(days);
                } else if (item.item_name === 'Makan Siang Peserta') {
                    $('#cons_lunch_participant_check').prop('checked', true).trigger('change');
                    $('#cons_lunch_participant_qty').val(formatNumber(qty));
                    $('#cons_lunch_participant_days').val(days);
                }
            }
        });
    }

    // Transport & Consumption Toggle Logic
    $('.transport-check, .cons-check').change(function() {
        // Fix: Navigate to common parent wrapper (.border) to find sibling container
        const container = $(this).closest('.border').find('.inputs-container');
        if($(this).is(':checked')) {
            container.removeClass('d-none');
        } else {
            container.addClass('d-none');
        }
        calculateAll();
    });

    // Currency Formatting
    $(document).on('input', '.currency-input', function() {
        let val = $(this).val().replace(/\D/g, '');
        $(this).val(formatNumber(val));
        calculateAll();
    });

    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function parseCurrency(str) {
        if(!str) return 0;
        return parseInt(str.toString().replace(/\./g, '')) || 0;
    }

    // CALCULATION ENGINE
    window.calculateAll = function() {
        let totalPersonnel = 0;
        let totalVendor = 0;
        let totalTransport = 0;
        let totalConsumption = 0;
        
        // Use global days only for display, not calculation
        const globalDays = window.totalDays || 0;
        $('.days-display').text(globalDays);

        // 1. Personnel
        $('.personnel-row').each(function() {
            const qty = parseCurrency($(this).find('.p-qty').val());
            const price = parseCurrency($(this).find('.price-input').val());
            const days = parseCurrency($(this).find('.personnel-days').val());
            const subtotal = qty * price * days;
            
            $(this).find('.p-subtotal').text(formatNumber(subtotal));
            totalPersonnel += subtotal;
        });

        // 2. Vendor
        $('.vendor-row').each(function() {
            const qty = parseCurrency($(this).find('.vendor-qty').val());
            const price = parseCurrency($(this).find('.vendor-price').val());
            const days = parseCurrency($(this).find('.v-days').val());
            const subtotal = qty * price * days;
            
            $(this).find('.vendor-subtotal').text(formatNumber(subtotal));
            totalVendor += subtotal;
        });

        // 3. Transport
        if($('#transport_bbm_check').is(':checked')) {
            const cars = parseCurrency($('#transport_bbm_cars').val());
            const nominal = parseCurrency($('#transport_bbm_nominal').val());
            const days = parseCurrency($('#transport_bbm_days').val());
            totalTransport += (cars * nominal * days);
        }
        if($('#transport_tol_check').is(':checked')) {
            const cars = parseCurrency($('#transport_tol_cars').val());
            const nominal = parseCurrency($('#transport_tol_nominal').val());
            const days = parseCurrency($('#transport_tol_days').val());
            totalTransport += (cars * nominal * days);
        }
        if($('#transport_emergency_check').is(':checked')) {
            const nominal = parseCurrency($('#transport_emergency_nominal').val());
            totalTransport += nominal; // One time
        }

        // 3. Consumption
        if($('#cons_mineral_check').is(':checked')) {
            const qty = parseCurrency($('#cons_mineral_qty').val());
            const price = parseCurrency($('#cons_mineral_price').val());
            const days = parseCurrency($('#cons_mineral_days').val());
            totalConsumption += (qty * price * days);
        }
        if($('#cons_lunch_staff_check').is(':checked')) {
            const qty = parseCurrency($('#cons_lunch_staff_qty').val());
            const price = parseCurrency($('#cons_lunch_staff_price').val());
            const days = parseCurrency($('#cons_lunch_staff_days').val());
            totalConsumption += (qty * price * days);
        }
        // Snack Peserta
        if($('#cons_snack_participant_check').is(':checked')) {
            const qty = parseCurrency($('#cons_snack_participant_qty').val());
            const price = parseCurrency($('#cons_snack_participant_price').val());
            // Snack/Makan Siang Peserta biasanya 1 hari event or calculated differently?
            // Existing logic had no price/cost (Rp 0).
            // So we don't add to totalConsumption.
        }
        // Makan Siang Peserta
        if($('#cons_lunch_participant_check').is(':checked')) {
            const qty = parseCurrency($('#cons_lunch_participant_qty').val());
            const price = parseCurrency($('#cons_lunch_participant_price').val());
            // No cost
        }

        // Update Summaries
        $('#summary_personnel_display').text('Rp ' + formatNumber(totalPersonnel));
        $('#summary_personnel').val(totalPersonnel);

        $('#summary_vendor_display').text('Rp ' + formatNumber(totalVendor));
        $('#summary_vendor').val(totalVendor);

        $('#summary_transport_display').text('Rp ' + formatNumber(totalTransport));
        $('#summary_transport').val(totalTransport);

        $('#summary_consumption_display').text('Rp ' + formatNumber(totalConsumption));
        $('#summary_consumption').val(totalConsumption);

        const grandTotal = totalPersonnel + totalVendor + totalTransport + totalConsumption;
        $('#grand_total_display').text('Rp ' + formatNumber(grandTotal));
        $('#grand_total').val(grandTotal);
    }

    // Run prefill after short delay to ensure everything loaded?
    // Call it immediately, but inside ready.
    prefillData();

});
</script>
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
            <h1 class="page-header-title">Pengajuan RAB</h1>
            <p class="page-header-subtitle">Buat Rancangan Anggaran Biaya Baru</p>
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

    <form action="index.php?page=rabs_store" method="POST" id="formRab">
        <!-- Hidden Fields for Submission -->
        <input type="hidden" name="selected_dates" id="selected_dates" value="[]">
        <input type="hidden" name="status" id="status_field" value="draft">
        <input type="hidden" name="total_days" id="total_days_field" value="0">
        
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
                                            <option value="<?php echo $p['project_id']; ?>">
                                                <?php echo $p['nama_project']; ?> (<?php echo DateHelper::formatSmartDateIndonesian($p['tanggal_mcu']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text text-primary"><i class="fas fa-info-circle me-1"></i>Hanya project dengan tanggal MCU yang muncul.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-muted small text-uppercase">Lokasi MCU</label>
                                    <div class="d-flex gap-3 mt-2 p-2 bg-light rounded">
                                        <div class="form-check">
                                            <input class="form-check-input location-type" type="radio" name="location_type" id="loc_dalam" value="dalam_kota" checked>
                                            <label class="form-check-label fw-bold" for="loc_dalam">Dalam Kota</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input location-type" type="radio" name="location_type" id="loc_luar" value="luar_kota">
                                            <label class="form-check-label fw-bold" for="loc_luar">Luar Kota</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label fw-bold text-muted small text-uppercase">Tanggal Pelaksanaan</label>
                                    <div id="date_selection_container" class="p-3 bg-light rounded border-dashed text-center">
                                        <span class="text-muted"><i class="fas fa-calendar-times me-2"></i>Pilih Project terlebih dahulu</span>
                                    </div>
                                </div>
                                
                                <!-- Manual Date Input Section -->
                                <div class="col-12 mt-3">
                                    <div class="border-top pt-3">
                                        <label class="form-label fw-bold text-muted small text-uppercase">
                                            <i class="fas fa-calendar-plus me-2"></i>Tambah Tanggal Manual (Opsional)
                                        </label>
                                        <div class="row g-2 align-items-end">
                                            <div class="col-md-6">
                                                <input type="date" class="form-control" id="manual_date_input" placeholder="Pilih tanggal">
                                            </div>
                                            <div class="col-md-3">
                                                <button type="button" class="btn btn-outline-primary w-100" id="btn_add_manual_date">
                                                    <i class="fas fa-plus me-2"></i>Tambah
                                                </button>
                                            </div>
                                        </div>
                                        <div class="form-text mt-1"><i class="fas fa-info-circle me-1"></i>Tambahkan tanggal di luar jadwal MCU project</div>
                                        <div id="manual_dates_container" class="mt-3">
                                            <p class="text-muted small mb-0"><i class="fas fa-calendar-check me-1"></i>Belum ada tanggal manual</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-muted small text-uppercase">Estimasi Peserta</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fas fa-users text-primary"></i></span>
                                        <input type="text" class="form-control currency-input fw-bold" name="total_participants" id="total_participants" value="0" placeholder="0">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-muted small text-uppercase">Link SPH</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fas fa-file-pdf text-danger"></i></span>
                                        <input type="text" class="form-control" id="sph_display" placeholder="Tidak ada SPH" readonly>
                                        <a href="#" class="btn btn-outline-primary" id="btn_view_sph" target="_blank" style="display:none;">
                                            <i class="fas fa-external-link-alt"></i> Buka
                                        </a>
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
                                            // Clean potential formatting from DB settings
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
                                        <!-- Rows added via JS -->
                                        <tr id="empty_personnel_row">
                                            <td colspan="6" class="text-center text-muted py-4">Belum ada petugas dipilih</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">
                                <label class="form-label small fw-bold text-muted">Catatan Petugas</label>
                                <textarea class="form-control" name="notes_personnel" rows="2" placeholder="Contoh: Dokter standby jam 08.00"></textarea>
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
                                            <td colspan="5" class="text-center text-muted py-3">Pilih Project terlebih dahulu untuk memuat kebutuhan vendor.</td>
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
                                    <input class="form-check-input transport-check" type="checkbox" name="transport_bbm_check" id="transport_bbm_check">
                                    <label class="form-check-label fw-bold" for="transport_bbm_check">BBM (Bahan Bakar)</label>
                                </div>
                                <div class="mt-3 ps-4 inputs-container d-none">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="small text-muted">Nominal per Mobil</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white">Rp</span>
                                                <input type="text" class="form-control transport-nominal currency-input" name="transport_bbm_nominal" id="transport_bbm_nominal" placeholder="0">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="small text-muted">Jumlah Mobil</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control transport-qty currency-input" name="transport_bbm_cars" id="transport_bbm_cars" placeholder="0">
                                                <span class="input-group-text bg-white">Unit</span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="small text-muted">Jumlah Hari</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control transport-days currency-input" name="transport_bbm_days" id="transport_bbm_days" value="1" placeholder="0">
                                                <span class="input-group-text bg-white">Hari</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tol -->
                            <div class="p-3 border rounded mb-3 hover-shadow-sm">
                                <div class="form-check form-switch">
                                    <input class="form-check-input transport-check" type="checkbox" name="transport_tol_check" id="transport_tol_check">
                                    <label class="form-check-label fw-bold" for="transport_tol_check">Biaya Tol</label>
                                </div>
                                <div class="mt-3 ps-4 inputs-container d-none">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="small text-muted">Nominal per Mobil</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white">Rp</span>
                                                <input type="text" class="form-control transport-nominal currency-input" name="transport_tol_nominal" placeholder="0">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="small text-muted">Jumlah Mobil</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control transport-qty currency-input" name="transport_tol_cars" placeholder="0">
                                                <span class="input-group-text bg-white">Unit</span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="small text-muted">Jumlah Hari</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control transport-days currency-input" name="transport_tol_days" value="1" placeholder="0">
                                                <span class="input-group-text bg-white">Hari</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Emergency -->
                            <div class="p-3 border rounded mb-3 hover-shadow-sm" data-type="emergency">
                                <div class="form-check form-switch">
                                    <input class="form-check-input transport-check" type="checkbox" name="transport_emergency_check" id="transport_emergency_check">
                                    <label class="form-check-label fw-bold" for="transport_emergency_check">Emergency Cost</label>
                                </div>
                                <div class="mt-3 ps-4 inputs-container d-none">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="small text-muted">Total Nominal</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white">Rp</span>
                                                <input type="text" class="form-control transport-nominal currency-input" name="transport_emergency_nominal" placeholder="0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="small text-muted">Keterangan</label>
                                            <input type="text" class="form-control" name="transport_emergency_notes" placeholder="Keperluan...">
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
                                    <input class="form-check-input cons-check" type="checkbox" name="cons_mineral_check" id="cons_mineral_check">
                                    <label class="form-check-label fw-bold" for="cons_mineral_check">Air Mineral Petugas</label>
                                </div>
                                <div class="mt-3 ps-4 inputs-container d-none">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="small text-muted">Qty per Hari</label>
                                            <input type="text" class="form-control cons-qty currency-input" name="cons_mineral_qty" id="cons_mineral_qty" placeholder="0">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="small text-muted">Harga Satuan</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white">Rp</span>
                                                <input type="text" class="form-control cons-price currency-input" name="cons_mineral_price" id="cons_mineral_price" placeholder="0">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="small text-muted">Jumlah Hari</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control cons-days currency-input" name="cons_mineral_days" id="cons_mineral_days" value="1" placeholder="0">
                                                <span class="input-group-text bg-white">Hari</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Makan Siang Petugas -->
                            <div class="p-3 border rounded mb-3 hover-shadow-sm">
                                <div class="form-check form-switch">
                                    <input class="form-check-input cons-check" type="checkbox" name="cons_lunch_staff_check" id="cons_lunch_staff_check">
                                    <label class="form-check-label fw-bold" for="cons_lunch_staff_check">Makan Siang Petugas</label>
                                </div>
                                <div class="mt-3 ps-4 inputs-container d-none">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="small text-muted">Qty per Hari</label>
                                            <input type="text" class="form-control cons-qty currency-input" name="cons_lunch_staff_qty" id="cons_lunch_staff_qty" placeholder="0">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="small text-muted">Harga Satuan</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white">Rp</span>
                                                <input type="text" class="form-control cons-price currency-input" name="cons_lunch_staff_price" id="cons_lunch_staff_price" placeholder="0">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="small text-muted">Jumlah Hari</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control cons-days currency-input" name="cons_lunch_staff_days" id="cons_lunch_staff_days" value="1" placeholder="0">
                                                <span class="input-group-text bg-white">Hari</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Snack Participant -->
                            <div class="p-3 border rounded mb-3 hover-shadow-sm" data-type="participant">
                                <div class="form-check form-switch">
                                    <input class="form-check-input cons-check" type="checkbox" name="cons_snack_participant_check" id="cons_snack_participant_check">
                                    <label class="form-check-label fw-bold" for="cons_snack_participant_check">Snack Peserta</label>
                                </div>
                                <div class="mt-3 ps-4 inputs-container d-none">
                                    <div class="alert alert-info py-2 small"><i class="fas fa-info-circle me-1"></i>Hanya untuk pencatatan jumlah, biaya Rp 0 di RAB ini.</div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="small text-muted">Total Quantity</label>
                                            <input type="text" class="form-control cons-qty currency-input" name="cons_snack_participant_qty" id="cons_snack_participant_qty" placeholder="0">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="small text-muted">Jumlah Hari</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control cons-days currency-input" name="cons_snack_participant_days" id="cons_snack_participant_days" value="1" placeholder="0">
                                                <span class="input-group-text bg-white">Hari</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-2 p-2 bg-light rounded border small" id="cons_snack_participant_items_container" style="display:none;">
                                        <strong>Menu Snack:</strong>
                                        <ul class="mb-0 ps-3" id="cons_snack_participant_items_list"></ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Makan Siang Peserta -->
                            <div class="p-3 border rounded mb-3 hover-shadow-sm" data-type="participant">
                                <div class="form-check form-switch">
                                    <input class="form-check-input cons-check" type="checkbox" name="cons_lunch_participant_check" id="cons_lunch_participant_check">
                                    <label class="form-check-label fw-bold" for="cons_lunch_participant_check">Makan Siang Peserta</label>
                                </div>
                                <div class="mt-3 ps-4 inputs-container d-none">
                                    <div class="alert alert-info py-2 small"><i class="fas fa-info-circle me-1"></i>Hanya untuk pencatatan jumlah, biaya Rp 0 di RAB ini.</div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="small text-muted">Total Quantity</label>
                                            <input type="text" class="form-control cons-qty currency-input" name="cons_lunch_participant_qty" id="cons_lunch_participant_qty" placeholder="0">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="small text-muted">Jumlah Hari</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control cons-days currency-input" name="cons_lunch_participant_days" id="cons_lunch_participant_days" value="1" placeholder="0">
                                                <span class="input-group-text bg-white">Hari</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-2 p-2 bg-light rounded border small" id="cons_lunch_participant_items_container" style="display:none;">
                                        <strong>Menu Makan Siang:</strong>
                                        <ul class="mb-0 ps-3" id="cons_lunch_participant_items_list"></ul>
                                    </div>
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
                                    <i class="fas fa-paper-plane me-2"></i>Submit RAB
                                </button>
                                
                                <button type="submit" name="action" value="draft" class="btn btn-outline-secondary rounded-pill fw-bold py-2 d-none" id="btn_draft">
                                    <i class="fas fa-save me-2"></i>Simpan Draft
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
            // Check both project dates and manual dates
            const hasProjectDates = $('.project-date-check:checked').length > 0;
            const hasManualDates = manualDates.length > 0;
            
            if(!hasProjectDates && !hasManualDates) {
                 Swal.fire('Error', 'Silakan pilih minimal satu tanggal pelaksanaan', 'error');
                 return false;
            }
        }
        return true;
    }

    // 3. Project Selection Logic (AJAX)
    $('#project_id').change(function() {
        const projectId = $(this).val();
        if(projectId) {
            $.ajax({
                url: 'index.php?page=rabs_get_project_dates',
                type: 'GET',
                data: { project_id: projectId },
                dataType: 'json',
                success: function(data) {
                    let html = '';
                    const dates = data.dates || [];
                    
                    if(dates.length > 0) {
                        html += '<div class="d-flex flex-wrap gap-2 justify-content-center">';
                        dates.forEach(function(date) {
                            html += `
                                <input type="checkbox" class="btn-check project-date-check" name="dates[]" value="${date}" id="date_${date}" checked autocomplete="off">
                                <label class="btn btn-outline-primary fw-bold rounded-pill px-4" for="date_${date}">
                                    <i class="fas fa-calendar-day me-2"></i>${formatDateIndo(date)}
                                </label>
                            `;
                        });
                        html += '</div>';
                        
                        // Store Project Meta
                        window.projectTotalDays = data.total_days || dates.length;
                        window.projectTotalParticipants = parseInt(data.total_participants) || 0;

                        // Update total days display
                        updateTotalDays(dates.length);

                        // AUTOMATION: Consumption (Snack & Makan Siang Peserta)
                        // Reset first
                        $('#cons_snack_participant_check').prop('checked', false).trigger('change');
                        $('#cons_lunch_participant_check').prop('checked', false).trigger('change');
                        $('#cons_snack_participant_qty').val(0).prop('readonly', false).removeClass('bg-light');
                        $('#cons_snack_participant_days').prop('readonly', false).removeClass('bg-light');
                        $('#cons_lunch_participant_qty').val(0).prop('readonly', false).removeClass('bg-light');
                        $('#cons_lunch_participant_days').prop('readonly', false).removeClass('bg-light');
                        $('#cons_snack_participant_items_container').hide();
                        $('#cons_lunch_participant_items_container').hide();

                        // Helper to display items
                        const displayConsumptionItems = (itemsRaw, listId, containerId) => {
                            let items = [];
                            try {
                                if (typeof itemsRaw === 'string') {
                                    if(!itemsRaw || itemsRaw === 'null') itemsRaw = '[]';
                                    items = JSON.parse(itemsRaw);
                                } else if (Array.isArray(itemsRaw)) {
                                    items = itemsRaw;
                                }
                            } catch (e) { console.error("Error parsing items", e); }

                            const list = $(listId);
                            const container = $(containerId);
                            list.empty();
                            list.addClass('list-unstyled mb-0 ps-0'); // Remove default list style

                            if (Array.isArray(items) && items.length > 0) {
                                items.forEach(item => {
                                    let htmlContent = '';
                                    if(typeof item === 'object' && item !== null) {
                                        const name = item.item || '-';
                                        const qty = item.qty ? `<span class="badge bg-secondary rounded-pill ms-1">${item.qty} pax</span>` : '';
                                        const price = item.price ? `<span class="text-muted ms-1">@ Rp ${formatNumber(item.price)}</span>` : '';
                                        const total = item.total ? `<span class="fw-bold text-dark ms-1">= Rp ${formatNumber(item.total)}</span>` : '';
                                        
                                        htmlContent = `
                                            <div class="d-flex align-items-center mb-1">
                                                <i class="fas fa-check-circle text-success me-2" style="font-size: 0.8em;"></i>
                                                <span class="fw-medium text-dark">${name}</span>
                                                ${qty}
                                                ${price}
                                                ${total}
                                            </div>
                                        `;
                                    } else {
                                        htmlContent = `
                                            <div class="d-flex align-items-center mb-1">
                                                <i class="fas fa-check-circle text-success me-2" style="font-size: 0.8em;"></i>
                                                <span>${item}</span>
                                            </div>
                                        `;
                                    }
                                    list.append(`<li>${htmlContent}</li>`);
                                });
                                container.show();
                            }
                        };

                        // Snack Peserta
                        if (data.snack === 'Ya') {
                            $('#cons_snack_participant_check').prop('checked', true).trigger('change');
                            // Use procurement qty if available, else total participants
                            let snackQty = parseFloat(data.procurement_snack_qty) || window.projectTotalParticipants || 0;
                            $('#cons_snack_participant_qty').val(formatNumber(snackQty)).prop('readonly', true).addClass('bg-light');
                            $('#cons_snack_participant_days').prop('readonly', true).addClass('bg-light');
                            displayConsumptionItems(data.snack_items, '#cons_snack_participant_items_list', '#cons_snack_participant_items_container');
                        }

                        // Makan Siang Peserta
                        if (data.lunch === 'Ya') {
                            $('#cons_lunch_participant_check').prop('checked', true).trigger('change');
                            // Use procurement qty if available, else total participants
                            let lunchQty = parseFloat(data.procurement_lunch_qty) || window.projectTotalParticipants || 0;
                            $('#cons_lunch_participant_qty').val(formatNumber(lunchQty)).prop('readonly', true).addClass('bg-light');
                            $('#cons_lunch_participant_days').prop('readonly', true).addClass('bg-light');
                            displayConsumptionItems(data.lunch_items, '#cons_lunch_participant_items_list', '#cons_lunch_participant_items_container');
                        }
                    } else {
                        html = '<p class="text-danger mb-0">Tidak ada tanggal MCU yang tersedia (habis atau lampau).</p>';
                        window.projectTotalDays = 0;
                        window.projectTotalParticipants = 0;
                        updateTotalDays(0);
                    }
                    $('#date_selection_container').html(html);

                    // Update SPH Display
                    if(data.sph_file) {
                        $('#sph_display').val(data.sph_file);
                        let sphUrl;
                        if (data.sph_file.match(/^https?:\/\//)) {
                            sphUrl = data.sph_file;
                            $('#btn_view_sph').html('<i class="fas fa-external-link-alt me-1"></i> Link SPH');
                        } else {
                            // Legacy file
                            sphUrl = 'index.php?page=download_sph&project_id=' + projectId;
                            $('#btn_view_sph').html('<i class="fas fa-eye me-1"></i> View File');
                        }
                        $('#btn_view_sph').attr('href', sphUrl).show();
                    } else {
                        $('#sph_display').val('Tidak ada SPH');
                        $('#btn_view_sph').hide().attr('href', '#');
                    }

                    // Populate Vendor Table
                    const vendorBody = $('#vendor_table_body');
                    vendorBody.empty();
                    
                    const vendors = data.vendor_allocations || [];
                    if(vendors.length > 0) {
                        vendors.forEach(function(item, index) {
                            const days = window.totalDays || 1;
                            const qty = parseFloat(item.participant_count) || 0;
                            const price = 0; // Default 0 as per view-only request
                            // Display assigned vendor in notes
                            const note = item.assigned_vendor_name ? `${item.assigned_vendor_name}` : (item.notes || '');
                            const subtotal = qty * price * days;
                            
                            const row = `
                                <tr class="vendor-row">
                                    <td>
                                        <span class="fw-bold text-primary">${item.exam_type}</span>
                                        <input type="hidden" name="vendor[${index}][item_name]" value="${item.exam_type}">
                                        <input type="hidden" name="vendor[${index}][qty]" class="vendor-qty" value="${qty}">
                                        <input type="hidden" name="vendor[${index}][price]" class="vendor-price" value="${price}">
                                        <input type="hidden" name="vendor[${index}][notes]" value="${note}">
                                    </td>
                                    <td class="text-center fw-bold">${qty}</td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control form-control-sm text-center v-days" name="vendor[${index}][days]" value="${days}" readonly>
                                            <span class="input-group-text bg-light">Hari</span>
                                        </div>
                                    </td>
                                    <td class="text-end">Rp ${formatNumber(price)}</td>
                                    <td class="text-end fw-bold">Rp <span class="vendor-subtotal">${formatNumber(subtotal)}</span></td>
                                    <td>
                                        <small class="text-muted d-block text-truncate" style="max-width: 150px;">${note || '-'}</small>
                                    </td>
                                </tr>
                            `;
                            vendorBody.append(row);
                        });
                    } else {
                         vendorBody.html('<tr><td colspan="6" class="text-center text-muted py-3">Tidak ada data vendor dari database.</td></tr>');
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
    });

    // Helper: Format Date
    function formatDateIndo(dateStr) {
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        return new Date(dateStr).toLocaleDateString('id-ID', options);
    }

    // Helper: Update Total Days
    function updateTotalDays(count) {
        // Update Store total days globally
        window.totalDays = count;
        $('.days-display').text(count);
        
        // Update Personnel Days (Skip Petugas Loading)
        $('.personnel-days').each(function() {
            const row = $(this).closest('.personnel-row');
            const role = row.find('input[name*="[role]"]').val();
            if (role !== 'Petugas Loading') {
                $(this).val(count);
            }
        });

        // Update other input fields
        $('.transport-days, .cons-days, .v-days').val(count);

        // Update Estimated Participants
        if (window.projectTotalDays > 0 && window.projectTotalParticipants > 0) {
             const dailyAvg = window.projectTotalParticipants / window.projectTotalDays;
             const estimated = Math.ceil(dailyAvg * count);
             $('#total_participants').val(formatNumber(estimated));
        } else if (window.projectTotalParticipants > 0) {
             $('#total_participants').val(formatNumber(window.projectTotalParticipants));
        } else {
             $('#total_participants').val(0);
        }

        // Trigger recalculations
        calculateAll();
    }

    // Monitor Date Checkboxes
    $(document).on('change', '.project-date-check', function() {
        const checkedCount = $('.project-date-check:checked').length;
        const totalCount = checkedCount + manualDates.length;
        updateTotalDays(totalCount);
    });

    // 4. Personnel Logic (Dynamic Rows)
    let personnelIndex = 0;
    
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

        // Remove empty row if exists
        $('#empty_personnel_row').remove();

        validOptions.each(function() {
            const role = $(this).val();
            const priceDalam = parseFloat($(this).data('price-dalam')) || 0;
            const priceLuar = parseFloat($(this).data('price-luar')) || 0;
            const locationType = $('input[name="location_type"]:checked').val();
            const price = (locationType === 'luar_kota') ? priceLuar : priceDalam;

            let days = window.totalDays || 1;
            if (role === 'Petugas Loading') {
                days = 1;
            }

            const rowHtml = `
                <tr class="personnel-row" id="p_row_${personnelIndex}">
                    <td>
                        <span class="fw-bold text-primary">${role}</span>
                        <input type="hidden" name="personnel[${personnelIndex}][role]" value="${role}">
                        <input type="hidden" name="personnel[${personnelIndex}][selected]" value="1">
                        <input type="hidden" class="price-dalam" value="${priceDalam}">
                        <input type="hidden" class="price-luar" value="${priceLuar}">
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm" name="personnel[${personnelIndex}][notes]" placeholder="Keterangan">
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm personnel-qty currency-input" name="personnel[${personnelIndex}][qty]" value="1">
                    </td>
                    <td>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0">Rp</span>
                            <input type="text" class="form-control text-end price-actual currency-input border-start-0" name="personnel[${personnelIndex}][price]" value="${formatNumber(price)}">
                        </div>
                    </td>
                    <td>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control form-control-sm text-center personnel-days currency-input" name="personnel[${personnelIndex}][days]" value="${days}">
                            <span class="input-group-text bg-light">Hari</span>
                        </div>
                    </td>
                    <td class="text-end fw-bold">
                        <span class="subtotal-display">0</span>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-personnel" data-id="${personnelIndex}">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>
            `;

            $('#personnel_table_body').append(rowHtml);
            personnelIndex++;
        });
        
        // Reset selector
        roleSelect.val(null).trigger('change');
        
        // Recalculate
        calculateAll();
    });

    $(document).on('click', '.remove-personnel', function() {
        $(this).closest('tr').remove();
        if($('#personnel_table_body tr').length === 0) {
             $('#personnel_table_body').html('<tr id="empty_personnel_row"><td colspan="6" class="text-center text-muted py-4">Belum ada petugas dipilih</td></tr>');
        }
        calculateAll();
    });

    // 5. General Calculation Logic
    
    // Listen for inputs
    $(document).on('keyup change', '.currency-input, .location-type, .transport-check, .cons-check', function() {
        // Format input while typing (simple version)
        if($(this).hasClass('currency-input')) {
            const val = parseCurrency($(this).val());
            // Only format on blur or careful handling to not mess up cursor
            // For now just calculate
        }
        calculateAll();
    });

    // Format currency on blur
    $(document).on('blur', '.currency-input', function() {
        const val = parseCurrency($(this).val());
        $(this).val(formatNumber(val));
    });

    // Handle Location Change (Update Prices)
    $('.location-type').change(function() {
        const type = $(this).val();
        $('.personnel-row').each(function() {
            const priceDalam = parseFloat($(this).find('.price-dalam').val()) || 0;
            const priceLuar = parseFloat($(this).find('.price-luar').val()) || 0;
            const newPrice = (type === 'luar_kota') ? priceLuar : priceDalam;
            
            $(this).find('.price-actual').val(formatNumber(newPrice));
        });
        calculateAll();
    });

    // Handle Checkboxes Show/Hide
    $('.transport-check, .cons-check').change(function() {
        const target = $(this).closest('.row, .border').find('.inputs-container');
        if($(this).is(':checked')) {
            target.removeClass('d-none');
        } else {
            target.addClass('d-none');
        }
        calculateAll();
    });

    function calculateAll() {
        let totalPersonnel = 0;
        let totalVendor = 0;
        let totalTransport = 0;
        let totalConsumption = 0;
        // const days = window.totalDays || 0; // Removed global multiplier

        // Personnel
        $('.personnel-row').each(function() {
            const qty = parseCurrency($(this).find('.personnel-qty').val());
            const price = parseCurrency($(this).find('.price-actual').val());
            const days = parseCurrency($(this).find('.personnel-days').val());
            const subtotal = qty * price * days;
            
            $(this).find('.subtotal-display').text(formatNumber(subtotal));
            totalPersonnel += subtotal;
        });

        // Vendor
        $('.vendor-row').each(function() {
            const qty = parseCurrency($(this).find('.vendor-qty').val());
            const price = parseCurrency($(this).find('.vendor-price').val());
            const days = parseCurrency($(this).find('.v-days').val());
            const subtotal = qty * price * days;
            
            $(this).find('.vendor-subtotal').text(formatNumber(subtotal));
            totalVendor += subtotal;
        });

        // Transport
        // BBM
        if($('#transport_bbm_check').is(':checked')) {
            const nom = parseCurrency($('input[name="transport_bbm_nominal"]').val());
            const cars = parseCurrency($('input[name="transport_bbm_cars"]').val());
            const days = parseCurrency($('input[name="transport_bbm_days"]').val());
            totalTransport += nom * cars * days;
        }
        // Tol
        if($('#transport_tol_check').is(':checked')) {
            const nom = parseCurrency($('input[name="transport_tol_nominal"]').val());
            const cars = parseCurrency($('input[name="transport_tol_cars"]').val());
            const days = parseCurrency($('input[name="transport_tol_days"]').val());
            totalTransport += nom * cars * days;
        }
        // Emergency
        if($('#transport_emergency_check').is(':checked')) {
            const nom = parseCurrency($('input[name="transport_emergency_nominal"]').val());
            totalTransport += nom;
        }

        // Consumption
        // Mineral
        if($('#cons_mineral_check').is(':checked')) {
            const qty = parseCurrency($('input[name="cons_mineral_qty"]').val());
            const price = parseCurrency($('input[name="cons_mineral_price"]').val());
            const days = parseCurrency($('input[name="cons_mineral_days"]').val());
            totalConsumption += qty * price * days;
        }
        // Makan Siang Petugas
        if($('#cons_lunch_staff_check').is(':checked')) {
            const qty = parseCurrency($('input[name="cons_lunch_staff_qty"]').val());
            const price = parseCurrency($('input[name="cons_lunch_staff_price"]').val());
            const days = parseCurrency($('input[name="cons_lunch_staff_days"]').val());
            totalConsumption += qty * price * days;
        }
        // Snack Peserta
        if($('#cons_snack_participant_check').is(':checked')) {
            // Biaya 0
        }
        // Makan Siang Peserta
        if($('#cons_lunch_participant_check').is(':checked')) {
            // Biaya 0
        }
        
        // Update Summary
        $('#summary_personnel').val(totalPersonnel);
        $('#summary_personnel_display').text(formatNumber(totalPersonnel));
        
        $('#summary_vendor').val(totalVendor);
        $('#summary_vendor_display').text(formatNumber(totalVendor));

        $('#summary_transport').val(totalTransport);
        $('#summary_transport_display').text(formatNumber(totalTransport));
        
        $('#summary_consumption').val(totalConsumption);
        $('#summary_consumption_display').text(formatNumber(totalConsumption));

        const grandTotal = totalPersonnel + totalVendor + totalTransport + totalConsumption;
        $('#grand_total').val(grandTotal);
        $('#grand_total_display').text('Rp ' + formatNumber(grandTotal));
    }

    // Helper: Parse Currency (Remove dots)
    function parseCurrency(val) {
        if(!val) return 0;
        return parseFloat(val.toString().replace(/\./g, '')) || 0;
    }

    // Helper: Format Number
    function formatNumber(num) {
        return num.toLocaleString('id-ID');
    }

    // Handle Submit Button Click (set status to need_approval_manager)
    $('#btn_submit').on('click', function(e) {
        e.preventDefault();
        $('#status_field').val('need_approval_manager');
        $('#formRab').submit();
    });

    // Handle Draft Button Click (set status to draft)
    $('#btn_draft').on('click', function(e) {
        e.preventDefault();
        $('#status_field').val('draft');
        $('#formRab').submit();
    });

    // ===== MANUAL DATE INPUT LOGIC =====
    let manualDates = [];

    // Add Manual Date Button Click
    $('#btn_add_manual_date').on('click', function() {
        const dateInput = $('#manual_date_input');
        const date = dateInput.val();
        
        if (!date) {
            Swal.fire('Error', 'Silakan pilih tanggal terlebih dahulu', 'error');
            return;
        }
        
        // Check if date already exists in project dates
        const existsInProject = $('.project-date-check[value="' + date + '"]').length > 0;
        if (existsInProject) {
            Swal.fire('Info', 'Tanggal ini sudah ada di tanggal MCU project', 'info');
            return;
        }
        
        // Check if date already exists in manual dates
        if (manualDates.includes(date)) {
            Swal.fire('Info', 'Tanggal ini sudah ditambahkan', 'info');
            return;
        }
        
        // Add to manual dates array
        manualDates.push(date);
        manualDates.sort(); // Keep sorted
        
        // Display as badge
        renderManualDates();
        
        // Clear input
        dateInput.val('');
        
        // Show success message
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Tanggal manual berhasil ditambahkan',
            timer: 1500,
            showConfirmButton: false
        });
    });

    // Render Manual Dates as Badges
    function renderManualDates() {
        const container = $('#manual_dates_container');
        container.empty();
        
        if (manualDates.length === 0) {
            container.html('<p class="text-muted small mb-0"><i class="fas fa-calendar-check me-1"></i>Belum ada tanggal manual</p>');
        } else {
            const wrapper = $('<div class="d-flex flex-wrap gap-2"></div>');
            
            manualDates.forEach(function(date, index) {
                const badge = $(`
                    <span class="badge bg-info text-dark p-2 d-inline-flex align-items-center">
                        <i class="fas fa-calendar-day me-2"></i>
                        <span class="me-2">${formatDateIndo(date)}</span>
                        <button type="button" class="btn-close btn-close-white ms-1" style="font-size: 0.7rem;" data-index="${index}"></button>
                    </span>
                `);
                
                // Remove button click handler
                badge.find('.btn-close').on('click', function() {
                    const idx = $(this).data('index');
                    manualDates.splice(idx, 1);
                    renderManualDates();
                });
                
                wrapper.append(badge);
            });
            
            container.append(wrapper);
        }
        
        // Update total days count
        const checkedCount = $('.project-date-check:checked').length;
        const totalCount = checkedCount + manualDates.length;
        updateTotalDays(totalCount);
    }

    // Initialize manual dates display
    renderManualDates();

    // Form Submit Handler - Prepare data before submission
    $('#formRab').on('submit', function(e) {
        // Collect selected dates from checkboxes (project dates)
        const projectDates = [];
        $('.project-date-check:checked').each(function() {
            projectDates.push($(this).val());
        });
        
        // Merge project dates + manual dates
        const allDates = [...projectDates, ...manualDates];
        
        // Remove duplicates and sort
        const uniqueDates = [...new Set(allDates)].sort();
        
        // Set selected_dates as JSON string
        $('#selected_dates').val(JSON.stringify(uniqueDates));
        
        // Set total_days
        $('#total_days_field').val(uniqueDates.length);
        
        // Validation: must select at least one date (from project OR manual)
        if (uniqueDates.length === 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Silakan pilih minimal satu tanggal pelaksanaan (dari project atau tambah manual)',
                confirmButtonText: 'OK'
            });
            return false;
        }
        
        // Allow form to submit
        return true;
    });
});
</script>
<?php include '../views/layouts/footer.php'; ?>

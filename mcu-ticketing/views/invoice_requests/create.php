<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Buat Invoice Request</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php?page=invoice_requests_index">Invoice Requests</a></li>
        <li class="breadcrumb-item active">Form Detail</li>
    </ol>

    <?php if (!empty($secondary_groups)): ?>
    <div class="alert alert-warning shadow-sm border-start border-warning border-4">
        <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Multiple Companies Detected</h5>
        <p class="mb-2">
            Anda memilih project dari beberapa company berbeda:
            <strong><?php echo htmlspecialchars($primary_company); ?></strong> (Utama) dan 
            <strong><?php echo implode(', ', array_keys($secondary_groups)); ?></strong>.
        </p>
        
        <div class="card bg-white mt-3">
            <div class="card-body py-2">
                <label class="form-label fw-bold mb-2">Pilih Metode Pemisahan:</label>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="split_mode" id="split_yes" value="split" checked>
                    <label class="form-check-label" for="split_yes">
                        <strong>Pisahkan Invoice (Recommended)</strong>
                        <div class="text-muted small">
                            Buat Invoice Request aktif untuk <strong><?php echo htmlspecialchars($primary_company); ?></strong>. 
                            Company lain akan dibuatkan <strong>Draft Request</strong> terpisah secara otomatis.
                        </div>
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="split_mode" id="split_no" value="merge">
                    <label class="form-check-label" for="split_no">
                        <strong>Gabung Semua Project</strong>
                        <div class="text-muted small">
                            Semua project (termasuk dari company lain) akan digabung ke dalam satu Invoice Request ini atas nama <strong><?php echo htmlspecialchars($primary_company); ?></strong>.
                        </div>
                    </label>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="m-0 font-weight-bold text-primary"><i class="fas fa-file-invoice me-2"></i>Form Pengajuan Invoice</h5>
                <small class="text-muted">Isi formulir berikut dengan lengkap</small>
            </div>
        </div>
        <div class="card-body">
            <form action="index.php?page=invoice_requests_store" method="POST" id="invoiceRequestForm">
                <!-- Hidden Inputs -->
                <input type="hidden" name="primary_project_ids" value='<?php echo json_encode($primary_project_ids); ?>'>
                <?php if (!empty($secondary_groups)): ?>
                <input type="hidden" name="secondary_groups" value='<?php echo json_encode($secondary_groups); ?>'>
                <?php endif; ?>

                <!-- Top Section: Selected Projects Summary -->
                <div class="alert alert-light border shadow-sm mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-3 border-end text-center">
                            <h2 class="display-6 fw-bold text-primary mb-0"><?php echo count($primary_projects); ?></h2>
                            <small class="text-muted text-uppercase fw-bold">Project Terpilih</small>
                        </div>
                        <div class="col-md-9 ps-4">
                            <div class="row">
                                <?php foreach ($primary_projects as $index => $p): 
                                    if ($index > 1) { // Show max 2 projects initially
                                        echo '<div class="col-12 text-muted small fst-italic">... dan ' . (count($primary_projects) - 2) . ' project lainnya</div>';
                                        break;
                                    }
                                ?>
                                <div class="col-md-6 mb-2">
                                    <div class="d-flex align-items-start">
                                        <i class="fas fa-check-circle text-success mt-1 me-2"></i>
                                        <div>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($p['nama_project']); ?></div>
                                            <div class="small text-muted">ID: <?php echo htmlspecialchars($p['project_id']); ?> | MCU: <?php echo DateHelper::formatSmartDateIndonesian($p['tanggal_mcu']); ?></div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Form Grid -->
                <div class="row g-4">
                    <!-- LEFT COLUMN: Event & Project Details -->
                    <div class="col-lg-6">
                        <div class="card h-100 border-0 shadow-sm bg-light">
                            <div class="card-body">
                                <h6 class="card-title text-uppercase text-muted fw-bold mb-3 border-bottom pb-2">
                                    <i class="fas fa-info-circle me-1"></i> Informasi Event & Project
                                </h6>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Nomor Surat / Request Number</label>
                                    <input type="text" class="form-control form-control-lg border-primary" name="request_number" placeholder="Masukkan Nomor Surat (Freetext)" required>
                                    <div class="form-text">Contoh: 210/SCO/INV/BCM/I/2026</div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-uppercase">Jenis Rekanan</label>
                                        <div class="bg-white p-2 border rounded text-center">
                                            <div class="form-check form-check-inline m-0">
                                                <input class="form-check-input" type="checkbox" checked disabled>
                                                <label class="form-check-label fw-bold text-dark">Corporate</label>
                                            </div>
                                        </div>
                                        <input type="hidden" name="partner_type" value="Corporate">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-uppercase">Jenis Event</label>
                                        <div class="bg-white p-2 border rounded">
                                            <div class="d-flex justify-content-between">
                                                <div class="form-check form-check-inline small m-0">
                                                    <input class="form-check-input" type="radio" name="event_type" id="event_walkin" value="Walk In">
                                                    <label class="form-check-label" for="event_walkin">Walk In</label>
                                                </div>
                                                <div class="form-check form-check-inline small m-0">
                                                    <input class="form-check-input" type="radio" name="event_type" id="event_onsite" value="On Site" checked>
                                                    <label class="form-check-label" for="event_onsite">On Site</label>
                                                </div>
                                                <div class="form-check form-check-inline small m-0">
                                                    <input class="form-check-input" type="radio" name="event_type" id="event_subcon" value="Subcon">
                                                    <label class="form-check-label" for="event_subcon">Subcon</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-12">
                                        <div class="card bg-info bg-opacity-10 border-info border-opacity-25">
                                            <div class="card-body py-2">
                                                <label class="form-label fw-bold small text-uppercase text-info mb-2">Opsi Pembuatan Invoice</label>
                                                
                                                <?php if (count($primary_projects) > 1): ?>
                                                <div class="mb-2">
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="split_mode" id="pm_merge" value="merge" checked>
                                                        <label class="form-check-label fw-bold" for="pm_merge">Gabung Semua Project jadi 1 Invoice</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="split_mode" id="pm_split" value="split">
                                                        <label class="form-check-label fw-bold" for="pm_split">Pisah per Project (Buat Draft Individual)</label>
                                                    </div>
                                                </div>
                                                <?php else: ?>
                                                <!-- Hidden input to default to merge/single if only 1 project -->
                                                <input type="hidden" name="split_mode" value="merge">
                                                <?php endif; ?>

                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="create_extra_draft" id="create_extra_draft" value="1">
                                                    <label class="form-check-label" for="create_extra_draft">
                                                        <strong>Split NPWP / Buat Draft Tambahan</strong>
                                                        <span class="text-muted small d-block">
                                                            Centang jika ingin membuat invoice kedua (draft) dengan data yang sama (misal: untuk pemisahan NPWP).
                                                        </span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Project Name(s)</label>
                                    <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars(implode(', ', array_column($primary_projects, 'nama_project'))); ?>" readonly>
                                    <div class="form-text">Project yang akan ditagihkan dalam invoice ini.</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Nama Perusahaan / Client (Billing)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fas fa-building text-muted"></i></span>
                                        <input type="text" class="form-control" name="client_company" id="input_client_company" value="<?php echo htmlspecialchars($client_company); ?>">
                                    </div>
                                    <div class="form-text">Nama perusahaan yang akan muncul di Invoice (Bisa diedit jika perlu penyesuaian).</div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Tgl. Pelaksanaan (Pengajuan)</label>
                                        <input type="date" class="form-control" name="request_date" value="<?php echo date('Y-m-d'); ?>" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-primary small text-uppercase mb-1">PIC Sales (Requester)</label>
                                        <?php if (!empty($sales_users)): ?>
                                            <select class="form-select border-primary" name="pic_sales_id" required>
                                                <option value="">-- Pilih Sales --</option>
                                                <?php foreach ($sales_users as $su): ?>
                                                    <option value="<?php echo $su['user_id']; ?>" <?php echo ($su['user_id'] == $sales_id) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($su['full_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php else: ?>
                                            <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($sales_name); ?>" readonly>
                                            <input type="hidden" name="pic_sales_id" value="<?php echo $sales_id; ?>">
                                        <?php endif; ?>
                                        <div class="form-text mt-1"><i class="fas fa-info-circle me-1"></i> Nama yang akan muncul sebagai pengaju di dokumen.</div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Nama NPWP</label>
                                    <input type="text" class="form-control bg-light" id="input_client_npwp" value="<?php echo htmlspecialchars($client_company); ?>" readonly>
                                    <div class="form-text fst-italic text-muted">Akan disamakan dengan Nama Perusahaan/Client di atas.</div>
                                </div>

                                <script>
                                    // Simple script to sync Company Name with NPWP Name
                                    document.getElementById('input_client_company').addEventListener('input', function() {
                                        document.getElementById('input_client_npwp').value = this.value;
                                    });
                                </script>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: Client & Logistics Info -->
                    <div class="col-lg-6">
                        <div class="card h-100 border-0 shadow-sm bg-light">
                            <div class="card-body">
                                <h6 class="card-title text-uppercase text-muted fw-bold mb-3 border-bottom pb-2">
                                    <i class="fas fa-user-tie me-1"></i> Informasi Rekanan & Logistik
                                </h6>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Nama PIC Rekanan</label>
                                        <input type="text" class="form-control" name="client_pic" required placeholder="Nama Lengkap PIC">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Nomor Telp. Rekanan</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i class="fas fa-phone text-muted"></i></span>
                                            <input type="text" class="form-control" name="client_phone" required placeholder="08xxxx">
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Email PIC Rekanan</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fas fa-envelope text-muted"></i></span>
                                        <input type="email" class="form-control" name="client_email" placeholder="email@company.com">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Alamat Pengiriman</label>
                                    <textarea class="form-control" name="shipping_address" rows="2" placeholder="Alamat lengkap pengiriman invoice fisik..."></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Ketentuan Invoice</label>
                                    <textarea class="form-control" name="invoice_terms" rows="2" placeholder="Term of Payment (TOP), dll..."></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Catatan Tambahan</label>
                                    <textarea class="form-control" name="notes" rows="1" placeholder="Catatan internal..."></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-primary">Link GDrive NPWP</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fab fa-google-drive text-success"></i></span>
                                        <input type="url" class="form-control" name="link_gdrive_npwp" placeholder="https://drive.google.com/..." required>
                                    </div>
                                    <div class="form-text">Pastikan link dapat diakses (Public/Shared).</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-primary">Link GDrive Absensi</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fab fa-google-drive text-success"></i></span>
                                        <input type="url" class="form-control" name="link_gdrive_absensi" placeholder="https://drive.google.com/..." required>
                                    </div>
                                    <div class="form-text">Data absensi pendukung invoice.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ITEM DETAILS SECTION -->
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-list-ol me-2"></i>Rincian Item Invoice</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="itemsTable">
                                <thead class="bg-light text-secondary small text-uppercase">
                                    <tr>
                                        <th class="ps-4 py-3" width="35%">Jenis Pemeriksaan / Deskripsi</th>
                                        <th class="py-3" width="20%">Harga (IDR)</th>
                                        <th class="py-3" width="10%">Qty</th>
                                        <th class="py-3" width="20%">Total (IDR)</th>
                                        <th class="py-3" width="10%">Keterangan</th>
                                        <th class="pe-4 py-3 text-end" width="5%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Default Row -->
                                     <tr>
                                        <td class="ps-4">
                                            <input type="text" class="form-control border-0 bg-light" name="item_description[]" required placeholder="Nama Item / Jasa">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control price-input border-0 bg-light" name="price[]" value="0" required onkeyup="formatRupiah(this)" onchange="calculateTotal(this)">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control qty-input border-0 bg-light text-center" name="qty[]" value="1" required onchange="calculateTotal(this)">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control total-input border-0 bg-white fw-bold text-end" readonly>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control border-0 bg-light" name="remarks[]" placeholder="-">
                                        </td>
                                        <td class="pe-4 text-end">
                                            <button type="button" class="btn btn-outline-danger btn-sm rounded-circle" onclick="removeRow(this)" title="Hapus Baris"><i class="fas fa-trash-alt"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="p-3 bg-light border-top">
                            <button type="button" class="btn btn-outline-success btn-sm fw-bold" onclick="addItemRow()">
                                <i class="fas fa-plus-circle me-1"></i> Tambah Item Baris
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ACTION BUTTONS -->
                <div class="d-flex justify-content-end align-items-center mt-5 mb-5">
                    <a href="index.php?page=invoice_requests_index" class="btn btn-light text-secondary me-3 fw-bold px-4">Batal</a>
                    <button type="submit" name="action" value="draft" class="btn btn-outline-primary me-3 fw-bold px-4">
                        <i class="fas fa-save me-2"></i>Simpan Draft
                    </button>
                    <button type="submit" name="action" value="submit" class="btn btn-primary fw-bold px-5 py-2 shadow-sm">
                        <i class="fas fa-paper-plane me-2"></i>Simpan & Submit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function calculateTotal(element) {
    const row = element.closest('tr');
    const price = parseFloat(row.querySelector('.price-input').value) || 0;
    const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
    const total = price * qty;
    
    // Format currency
    row.querySelector('.total-input').value = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(total);
}

function addItemRow() {
    const table = document.getElementById('itemsTable').getElementsByTagName('tbody')[0];
    const newRow = table.rows[0].cloneNode(true);
    
    // Clear inputs
    newRow.querySelectorAll('input').forEach(input => {
        if(input.classList.contains('qty-input')) input.value = 1;
        else if(input.classList.contains('price-input')) input.value = 0;
        else input.value = '';
    });
    
    table.appendChild(newRow);
}

function removeRow(btn) {
    const row = btn.closest('tr');
    const tbody = row.parentNode;
    if(tbody.rows.length > 1) {
        row.remove();
    } else {
        Swal.fire({
            title: 'Peringatan',
            text: 'Minimal satu item harus ada.',
            icon: 'warning',
            confirmButtonColor: '#204EAB'
        });
    }
}
</script>

<?php include '../views/layouts/footer.php'; ?>msTable').getElementsByTagName('tbody')[0];
    const newRow = table.rows[0].cloneNode(true);
    
    // Clear inputs
    newRow.querySelectorAll('input').forEach(input => {
        if(input.classList.contains('qty-input')) input.value = 1;
        else if(input.classList.contains('price-input')) input.value = '';
        else input.value = '';
    });
    
    table.appendChild(newRow);
}

function removeRow(btn) {
    const row = btn.closest('tr');
    const tbody = row.parentNode;
    if(tbody.rows.length > 1) {
        row.remove();
    } else {
        alert("Minimal satu item harus ada.");
    }
}

// Clean inputs on submit
document.getElementById('invoiceRequestForm').addEventListener('submit', function(e) {
    const inputs = document.querySelectorAll('.price-input');
    inputs.forEach(function(input) {
        input.value = input.value.replace(/\./g, '');
    });
});
</script>

<?php include '../views/layouts/footer.php'; ?>
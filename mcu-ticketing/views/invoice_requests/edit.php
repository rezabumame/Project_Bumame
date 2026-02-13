<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Edit Invoice Request</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php?page=invoice_requests_index">Invoice Requests</a></li>
        <li class="breadcrumb-item active">Edit Draft</li>
    </ol>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="m-0 font-weight-bold text-primary"><i class="fas fa-edit me-2"></i>Form Edit Invoice Request</h5>
                <span class="badge bg-warning text-dark">DRAFT MODE</span>
            </div>
        </div>
        <div class="card-body">
            <form action="index.php?page=invoice_requests_update_action" method="POST" id="invoiceRequestForm">
                <input type="hidden" name="id" value="<?php echo $request['id']; ?>">

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
                                    <input type="text" class="form-control form-control-lg border-primary" name="request_number" value="<?php echo htmlspecialchars($request['request_number']); ?>" required>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-uppercase">Jenis Rekanan</label>
                                        <div class="bg-white p-2 border rounded text-center">
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($request['partner_type'] ?? '-'); ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-uppercase">Jenis Event</label>
                                        <div class="bg-white p-2 border rounded text-center">
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($request['event_type'] ?? '-'); ?></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Nama Perusahaan / Client (Billing)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fas fa-building text-muted"></i></span>
                                        <input type="text" class="form-control" name="client_company" id="input_client_company" value="<?php echo htmlspecialchars($request['client_company']); ?>" required>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Tgl. Pelaksanaan (Pengajuan)</label>
                                        <input type="date" class="form-control" name="request_date" value="<?php echo $request['request_date']; ?>" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-primary small text-uppercase mb-1">PIC Sales (Requester)</label>
                                        <?php if (!empty($sales_users)): ?>
                                            <select class="form-select border-primary" name="pic_sales_id" required>
                                                <option value="">-- Pilih Sales --</option>
                                                <?php foreach ($sales_users as $su): ?>
                                                    <option value="<?php echo $su['user_id']; ?>" <?php echo ($su['user_id'] == $request['pic_sales_id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($su['full_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php else: ?>
                                            <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($request['sales_name']); ?>" readonly>
                                            <input type="hidden" name="pic_sales_id" value="<?php echo $request['pic_sales_id']; ?>">
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Nama NPWP</label>
                                    <input type="text" class="form-control bg-light" id="input_client_npwp" value="<?php echo htmlspecialchars($request['client_company']); ?>" readonly>
                                    <div class="form-text fst-italic text-muted">Akan disamakan dengan Nama Perusahaan/Client di atas.</div>
                                </div>

                                <script>
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
                                        <input type="text" class="form-control" name="client_pic" value="<?php echo htmlspecialchars($request['client_pic']); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Nomor Telp. Rekanan</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i class="fas fa-phone text-muted"></i></span>
                                            <input type="text" class="form-control" name="client_phone" value="<?php echo htmlspecialchars($request['client_phone']); ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Email PIC Rekanan</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fas fa-envelope text-muted"></i></span>
                                        <input type="email" class="form-control" name="client_email" value="<?php echo htmlspecialchars($request['client_email']); ?>">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Alamat Pengiriman</label>
                                    <textarea class="form-control" name="shipping_address" rows="2"><?php echo htmlspecialchars($request['shipping_address']); ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Ketentuan Invoice</label>
                                    <textarea class="form-control" name="invoice_terms" rows="2"><?php echo htmlspecialchars($request['invoice_terms']); ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Catatan Tambahan</label>
                                    <textarea class="form-control" name="notes" rows="1"><?php echo htmlspecialchars($request['notes']); ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-primary">Link GDrive NPWP</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fab fa-google-drive text-success"></i></span>
                                        <input type="url" class="form-control" name="link_gdrive_npwp" value="<?php echo htmlspecialchars($request['link_gdrive_npwp'] ?? ''); ?>" placeholder="https://drive.google.com/..." required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-primary">Link GDrive Absensi</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fab fa-google-drive text-success"></i></span>
                                        <input type="url" class="form-control" name="link_gdrive_absensi" value="<?php echo htmlspecialchars($request['link_gdrive_absensi'] ?? ''); ?>" placeholder="https://drive.google.com/..." required>
                                    </div>
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
                                    <?php if (!empty($request['items'])): ?>
                                        <?php foreach ($request['items'] as $item): ?>
                                        <tr>
                                            <td class="ps-4">
                                                <input type="text" class="form-control border-0 bg-light" name="item_description[]" required value="<?php echo htmlspecialchars($item['item_description']); ?>">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control price-input border-0 bg-light" name="price[]" value="<?php echo number_format($item['price'], 0, ',', '.'); ?>" required onkeyup="formatRupiah(this)" onchange="calculateTotal(this)">
                                            </td>
                                            <td>
                                                <input type="number" class="form-control qty-input border-0 bg-light text-center" name="qty[]" value="<?php echo $item['qty']; ?>" required onchange="calculateTotal(this)">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control total-input border-0 bg-white fw-bold text-end" value="<?php echo number_format($item['price'] * $item['qty'], 0, ',', '.'); ?>" readonly>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control border-0 bg-light" name="remarks[]" value="<?php echo htmlspecialchars($item['remarks'] ?? '-'); ?>">
                                            </td>
                                            <td class="pe-4 text-end">
                                                <button type="button" class="btn btn-outline-danger btn-sm rounded-circle" onclick="removeRow(this)" title="Hapus Baris"><i class="fas fa-trash-alt"></i></button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <!-- Default Row if no items (should not happen usually) -->
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
                                    <?php endif; ?>
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
                    <button type="submit" class="btn btn-primary fw-bold px-5 py-2 shadow-sm">
                        <i class="fas fa-save me-2"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function calculateTotal(element) {
    const row = element.closest('tr');
    let priceStr = row.querySelector('.price-input').value.replace(/\./g, '');
    const price = parseFloat(priceStr) || 0;
    const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
    const total = price * qty;
    
    // Format currency
    row.querySelector('.total-input').value = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(total).replace('Rp', '').trim();
}

function formatRupiah(angka) {
    var number_string = angka.value.replace(/[^,\d]/g, '').toString(),
    split   = number_string.split(','),
    sisa    = split[0].length % 3,
    rupiah  = split[0].substr(0, sisa),
    ribuan  = split[0].substr(sisa).match(/\d{3}/gi);

    if(ribuan){
        separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
    }

    rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    angka.value = rupiah;
}

function addItemRow() {
    const table = document.getElementById('itemsTable').getElementsByTagName('tbody')[0];
    // Clone the first row if it exists, or create a fresh one structure
    let newRow;
    if (table.rows.length > 0) {
        newRow = table.rows[0].cloneNode(true);
    } else {
        // Fallback if table is empty
        const tbody = document.createElement('tbody');
        tbody.innerHTML = `
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
        </tr>`;
        newRow = tbody.rows[0];
    }
    
    // Clear inputs
    newRow.querySelectorAll('input').forEach(input => {
        if(input.classList.contains('qty-input')) input.value = 1;
        else if(input.classList.contains('price-input')) input.value = 0;
        else if(input.classList.contains('total-input')) input.value = '';
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

// Clean inputs on submit
document.getElementById('invoiceRequestForm').addEventListener('submit', function(e) {
    const inputs = document.querySelectorAll('.price-input');
    inputs.forEach(function(input) {
        // Remove dots before submit so it sends clean number
        // But wait, the controller expects to do str_replace('.', '', ...)
        // However, standard form submit keeps the value. 
        // If we change it here, it changes on screen too which might be weird.
        // Actually the controller code I saw does: str_replace('.', '', $_POST['price'][$i])
        // So we don't need to clean it here necessarily, but it's safer if we do or if we trust the backend.
        // The previous create.php had a cleaner. Let's keep it but maybe it's fine.
        // Let's rely on backend cleaning since modifying value on submit can be visually jarring if submit fails (though here it redirects).
        // Actually, if I strip dots here, the user sees raw numbers if there is a delay.
        // I will rely on the Controller's logic: 'price' => empty($_POST['price'][$i]) ? 0 : str_replace('.', '', $_POST['price'][$i]),
    });
});
</script>

<?php include '../views/layouts/footer.php'; ?>
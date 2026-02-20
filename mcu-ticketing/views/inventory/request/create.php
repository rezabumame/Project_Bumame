<?php include '../views/layouts/header.php'; ?>
<?php include_once '../helpers/DateHelper.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center page-header-container">
        <div>
            <h1 class="page-header-title">Buat Inventory Request Baru</h1>
            <p class="page-header-subtitle">Silakan pilih project dan tentukan kebutuhan barang.</p>
        </div>
    </div>

            <form id="inventoryRequestForm" action="index.php?page=inventory_request_store" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $this->generateCsrfToken(); ?>">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-3">1. Pilih Project</h5>
                                <div class="mb-3">
                                    <label class="form-label">Project</label>
                                    <select class="form-select select2" name="project_id" required>
                                        <option value="">-- Pilih Project --</option>
                                        <?php foreach ($projects as $p): ?>
                                            <option value="<?php echo $p['project_id']; ?>">
                                                <?php echo $p['nama_project']; ?> (<?php echo DateHelper::formatSmartDateIndonesian($p['tanggal_mcu']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted">Hanya project Approved / In Progress Ops yang muncul.</small>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg mt-4">
                                        <i class="ri-send-plane-fill"></i> Submit Request
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-3">2. Input Kebutuhan Barang</h5>
                                
                                <div class="accordion" id="accordionItems">
                                    <?php $i=0; foreach ($items_by_category as $category => $items): $i++; ?>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="heading<?php echo $i; ?>">
                                            <button class="accordion-button <?php echo $i > 1 ? 'collapsed' : ''; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $i; ?>" aria-expanded="<?php echo $i==1?'true':'false'; ?>">
                                                Kategori: <?php echo $category; ?>
                                            </button>
                                        </h2>
                                        <div id="collapse<?php echo $i; ?>" class="accordion-collapse collapse <?php echo $i==1?'show':''; ?>" data-bs-parent="#accordionItems">
                                            <div class="accordion-body">
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-hover align-middle">
                                                        <thead>
                                                            <tr>
                                                                <th style="width: 50%;">Nama Barang</th>
                                                                <th>Tipe</th>
                                                                <th>Satuan</th>
                                                                <th style="width: 20%;">Qty Request</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($items as $item): ?>
                                                            <tr>
                                                                <td>
                                                                    <strong><?php echo $item['item_name']; ?></strong>
                                                                    <input type="hidden" name="item_type[<?php echo $item['id']; ?>]" value="<?php echo $item['item_type']; ?>">
                                                                    <input type="hidden" name="item_warehouse[<?php echo $item['id']; ?>]" value="<?php echo $item['target_warehouse']; ?>">
                                                                </td>
                                                                <td>
                                                                    <span class="badge bg-<?php echo $item['item_type'] == 'ASET' ? 'warning' : 'success'; ?>">
                                                                        <?php echo $item['item_type']; ?>
                                                                    </span>
                                                                </td>
                                                                <td><?php echo $item['unit']; ?></td>
                                                                <td>
                                                                    <input type="number" name="items[<?php echo $item['id']; ?>]" class="form-control form-control-sm" min="0" placeholder="0">
                                                                </td>
                                                            </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </form>

</div>

<script>
    document.getElementById('inventoryRequestForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // 1. Validate Project Selection
        const projectSelect = document.querySelector('select[name="project_id"]');
        if (!projectSelect.value) {
            Swal.fire({
                icon: 'warning',
                title: 'Project Belum Dipilih',
                text: 'Silakan pilih project terlebih dahulu.',
                confirmButtonColor: '#3085d6',
            });
            return;
        }

        // 2. Validate Items (At least one item with qty > 0)
        let hasItems = false;
        const inputs = document.querySelectorAll('input[name^="items"]');
        
        inputs.forEach(function(input) {
            if (input.value && parseInt(input.value) > 0) {
                hasItems = true;
            }
        });

        if (!hasItems) {
            Swal.fire({
                icon: 'warning',
                title: 'Tidak Ada Barang Dipilih',
                text: 'Mohon isi kuantitas (qty) minimal untuk satu barang.',
                confirmButtonColor: '#3085d6',
            });
            return;
        }

        // If all valid, submit
        this.submit();
    });
</script>

<?php include '../views/layouts/footer.php'; ?>
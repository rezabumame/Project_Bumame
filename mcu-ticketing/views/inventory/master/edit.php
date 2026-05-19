<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center page-header-container">
        <div>
            <h1 class="page-header-title">Edit Inventory Item</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="index.php?page=inventory_master_index">Master Inventory</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-edit me-1"></i>
            Item Details
        </div>
        <div class="card-body">
            <form action="index.php?page=inventory_master_update" method="POST">
                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="category" class="form-label">Category</label>
                        <input type="text" class="form-control" id="category" name="category" value="<?php echo htmlspecialchars($item['category']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="item_name" class="form-label">Item Name</label>
                        <input type="text" class="form-control" id="item_name" name="item_name" value="<?php echo htmlspecialchars($item['item_name']); ?>" required>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="item_type" class="form-label">Item Type</label>
                        <select class="form-select" id="item_type" name="item_type" required>
                            <option value="ASET" <?php echo $item['item_type'] == 'ASET' ? 'selected' : ''; ?>>ASET</option>
                            <option value="KONSUMABLE" <?php echo $item['item_type'] == 'KONSUMABLE' ? 'selected' : ''; ?>>KONSUMABLE</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="unit" class="form-label">Unit</label>
                        <input type="text" class="form-control" id="unit" name="unit" value="<?php echo htmlspecialchars($item['unit']); ?>" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="target_warehouse" class="form-label">Target Warehouse</label>
                    <select class="form-select" id="target_warehouse" name="target_warehouse" required>
                        <option value="GUDANG_ASET" <?php echo $item['target_warehouse'] == 'GUDANG_ASET' ? 'selected' : ''; ?>>GUDANG_ASET</option>
                        <option value="GUDANG_KONSUMABLE" <?php echo $item['target_warehouse'] == 'GUDANG_KONSUMABLE' ? 'selected' : ''; ?>>GUDANG_KONSUMABLE</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="is_active" class="form-label">Status</label>
                    <select class="form-select" id="is_active" name="is_active" required>
                        <option value="1" <?php echo $item['is_active'] == 1 ? 'selected' : ''; ?>>Active</option>
                        <option value="0" <?php echo $item['is_active'] == 0 ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>

                <div id="asset-codes-section" class="mb-3" style="display:<?php echo $item['item_type'] === 'ASET' ? 'block' : 'none'; ?>;">
                    <label class="form-label fw-semibold">Asset Codes</label>
                    <div class="form-text mb-2">Tambahkan kode aset untuk item ini. Setiap kode harus unik.</div>
                    <div id="asset-codes-container">
                        <?php if (!empty($assetCodes)): ?>
                            <?php foreach ($assetCodes as $code): ?>
                            <div class="input-group mb-2 asset-code-row">
                                <input type="text" class="form-control" name="asset_codes[]" value="<?php echo htmlspecialchars($code); ?>">
                                <button type="button" class="btn btn-outline-danger btn-remove-code" tabindex="-1">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="input-group mb-2 asset-code-row">
                                <input type="text" class="form-control" name="asset_codes[]" placeholder="e.g. BCM-MA-TDG-001-B2B">
                                <button type="button" class="btn btn-outline-danger btn-remove-code" tabindex="-1">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                    <button type="button" id="btn-add-code" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-plus me-1"></i>Add Code
                    </button>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="index.php?page=inventory_master_index" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const itemTypeSelect = document.getElementById('item_type');
    const warehouseSelect = document.getElementById('target_warehouse');
    const assetSection = document.getElementById('asset-codes-section');
    const container = document.getElementById('asset-codes-container');

    itemTypeSelect.addEventListener('change', function() {
        const isAset = this.value === 'ASET';
        assetSection.style.display = isAset ? 'block' : 'none';
        warehouseSelect.value = isAset ? 'GUDANG_ASET' : 'GUDANG_KONSUMABLE';
    });

    document.getElementById('btn-add-code').addEventListener('click', function() {
        const row = document.createElement('div');
        row.className = 'input-group mb-2 asset-code-row';
        row.innerHTML = '<input type="text" class="form-control" name="asset_codes[]" placeholder="e.g. BCM-MA-TDG-001-B2B">' +
            '<button type="button" class="btn btn-outline-danger btn-remove-code" tabindex="-1"><i class="fas fa-times"></i></button>';
        container.appendChild(row);
    });

    container.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-remove-code');
        if (!btn) return;
        const rows = container.querySelectorAll('.asset-code-row');
        if (rows.length > 1) {
            btn.closest('.asset-code-row').remove();
        } else {
            btn.closest('.asset-code-row').querySelector('input').value = '';
        }
    });
</script>

<?php include '../views/layouts/footer.php'; ?>
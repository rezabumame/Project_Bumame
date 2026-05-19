<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<?php
$asetItems      = array_filter($items, fn($i) => $i['item_type'] === 'ASET');
$konsumableItems = array_filter($items, fn($i) => $i['item_type'] === 'KONSUMABLE');

function renderRow($item) { ?>
    <tr>
        <td><?php echo htmlspecialchars($item['category']); ?></td>
        <td><?php echo htmlspecialchars($item['item_name']); ?></td>
        <td><?php echo htmlspecialchars($item['unit']); ?></td>
        <td>
            <span class="badge <?php echo $item['target_warehouse'] == 'GUDANG_ASET' ? 'bg-warning text-dark' : 'bg-secondary'; ?>">
                <?php echo htmlspecialchars($item['target_warehouse']); ?>
            </span>
        </td>
        <?php if ($item['item_type'] === 'ASET'): ?>
        <td>
            <span class="badge bg-primary"><?php echo (int)$item['asset_code_count']; ?> kode</span>
        </td>
        <?php endif; ?>
        <td>
            <?php if ($item['is_active']): ?>
                <span class="badge bg-success">Active</span>
            <?php else: ?>
                <span class="badge bg-danger">Inactive</span>
            <?php endif; ?>
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-warning btn-edit-item"
                    data-id="<?php echo $item['id']; ?>">
                <i class="fas fa-edit"></i>
            </button>
            <?php if ($item['is_active']): ?>
            <a href="index.php?page=inventory_master_delete&id=<?php echo $item['id']; ?>"
               class="btn btn-sm btn-danger"
               onclick="return confirm('Are you sure you want to deactivate this item?')">
                <i class="fas fa-trash"></i>
            </a>
            <?php endif; ?>
        </td>
    </tr>
<?php }
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center page-header-container">
        <div>
            <h1 class="page-header-title">Master Data Inventory</h1>
            <p class="page-header-subtitle">Kelola daftar item inventaris dan aset.</p>
        </div>
        <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCreate">
            <i class="fas fa-plus me-2"></i>Add New Item
        </button>
    </div>

    <ul class="nav nav-tabs mb-3" id="inventoryTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-aset-btn" data-bs-toggle="tab" data-bs-target="#tab-aset" type="button" role="tab">
                <i class="fas fa-box me-1"></i>Aset
                <span class="badge bg-primary ms-1"><?php echo count($asetItems); ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-konsumable-btn" data-bs-toggle="tab" data-bs-target="#tab-konsumable" type="button" role="tab">
                <i class="fas fa-cubes me-1"></i>Konsumable
                <span class="badge bg-info ms-1"><?php echo count($konsumableItems); ?></span>
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="tab-aset" role="tabpanel">
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-table me-1"></i>List Item Aset</div>
                <div class="card-body">
                    <table id="tableAset" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Item Name</th>
                                <th>Unit</th>
                                <th>Target Warehouse</th>
                                <th>Asset Codes</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($asetItems as $item): renderRow($item); endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="tab-konsumable" role="tabpanel">
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-table me-1"></i>List Item Konsumable</div>
                <div class="card-body">
                    <table id="tableKonsumable" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Item Name</th>
                                <th>Unit</th>
                                <th>Target Warehouse</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($konsumableItems as $item): renderRow($item); endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODAL CREATE ===== -->
<div class="modal fade" id="modalCreate" tabindex="-1" aria-labelledby="modalCreateLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="index.php?page=inventory_master_store" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $this->generateCsrfToken(); ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCreateLabel"><i class="fas fa-plus me-2"></i>Add New Inventory Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <input type="text" class="form-control" name="category" required placeholder="e.g., Elektronik, APD, ATK">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Item Name</label>
                            <input type="text" class="form-control" name="item_name" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Item Type</label>
                            <select class="form-select" name="item_type" id="create_item_type" required>
                                <option value="ASET">ASET</option>
                                <option value="KONSUMABLE">KONSUMABLE</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Unit</label>
                            <input type="text" class="form-control" name="unit" required placeholder="e.g., Pcs, Unit, Box">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Target Warehouse</label>
                        <select class="form-select" name="target_warehouse" id="create_target_warehouse" required>
                            <option value="GUDANG_ASET">GUDANG_ASET</option>
                            <option value="GUDANG_KONSUMABLE">GUDANG_KONSUMABLE</option>
                        </select>
                    </div>
                    <div id="create_asset_section" class="mb-1">
                        <label class="form-label fw-semibold">Asset Codes</label>
                        <div class="form-text mb-2">Tambahkan kode aset. Setiap kode harus unik.</div>
                        <div id="create_codes_container">
                            <div class="input-group mb-2 asset-code-row">
                                <input type="text" class="form-control" name="asset_codes[]" placeholder="e.g. BCM-MA-TDG-001-B2B">
                                <button type="button" class="btn btn-outline-danger btn-remove-code" tabindex="-1"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm btn-add-code" data-target="create_codes_container">
                            <i class="fas fa-plus me-1"></i>Add Code
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===== MODAL EDIT ===== -->
<div class="modal fade" id="modalEdit" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="index.php?page=inventory_master_update" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $this->generateCsrfToken(); ?>">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditLabel"><i class="fas fa-edit me-2"></i>Edit Inventory Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="edit_loading" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 text-muted">Loading...</p>
                    </div>
                    <div id="edit_form_body" style="display:none;">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Category</label>
                                <input type="text" class="form-control" name="category" id="edit_category" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Item Name</label>
                                <input type="text" class="form-control" name="item_name" id="edit_item_name" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Item Type</label>
                                <select class="form-select" name="item_type" id="edit_item_type" required>
                                    <option value="ASET">ASET</option>
                                    <option value="KONSUMABLE">KONSUMABLE</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Unit</label>
                                <input type="text" class="form-control" name="unit" id="edit_unit" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Target Warehouse</label>
                                <select class="form-select" name="target_warehouse" id="edit_target_warehouse" required>
                                    <option value="GUDANG_ASET">GUDANG_ASET</option>
                                    <option value="GUDANG_KONSUMABLE">GUDANG_KONSUMABLE</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="is_active" id="edit_is_active" required>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div id="edit_asset_section" class="mb-1">
                            <label class="form-label fw-semibold">Asset Codes</label>
                            <div class="form-text mb-2">Tambahkan kode aset. Setiap kode harus unik.</div>
                            <div id="edit_codes_container"></div>
                            <button type="button" class="btn btn-outline-primary btn-sm btn-add-code" data-target="edit_codes_container">
                                <i class="fas fa-plus me-1"></i>Add Code
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="edit_submit_btn">Update Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#tableAset').DataTable({ language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json' } });
    $('#tableKonsumable').DataTable({ language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json' } });
});

// ---- Shared: asset code repeater ----
function makeCodeRow(value) {
    return '<div class="input-group mb-2 asset-code-row">' +
        '<input type="text" class="form-control" name="asset_codes[]" value="' + escHtml(value) + '" placeholder="e.g. BCM-MA-TDG-001-B2B">' +
        '<button type="button" class="btn btn-outline-danger btn-remove-code" tabindex="-1"><i class="fas fa-times"></i></button>' +
        '</div>';
}

function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

document.addEventListener('click', function(e) {
    // Remove code row
    var removeBtn = e.target.closest('.btn-remove-code');
    if (removeBtn) {
        var container = removeBtn.closest('[id$="_codes_container"]');
        var rows = container.querySelectorAll('.asset-code-row');
        if (rows.length > 1) {
            removeBtn.closest('.asset-code-row').remove();
        } else {
            removeBtn.closest('.asset-code-row').querySelector('input').value = '';
        }
    }

    // Add code row
    var addBtn = e.target.closest('.btn-add-code');
    if (addBtn) {
        var targetId = addBtn.getAttribute('data-target');
        var container = document.getElementById(targetId);
        container.insertAdjacentHTML('beforeend', makeCodeRow(''));
    }
});

// ---- Create modal: toggle asset section ----
function toggleCreateAssetSection() {
    var isAset = document.getElementById('create_item_type').value === 'ASET';
    document.getElementById('create_asset_section').style.display = isAset ? 'block' : 'none';
    document.getElementById('create_target_warehouse').value = isAset ? 'GUDANG_ASET' : 'GUDANG_KONSUMABLE';
}
document.getElementById('create_item_type').addEventListener('change', toggleCreateAssetSection);

document.getElementById('modalCreate').addEventListener('show.bs.modal', function() {
    toggleCreateAssetSection();
});

document.getElementById('modalCreate').addEventListener('hidden.bs.modal', function() {
    this.querySelector('form').reset();
    var container = document.getElementById('create_codes_container');
    container.innerHTML = makeCodeRow('');
    toggleCreateAssetSection();
});

// ---- Edit modal: toggle asset section ----
function toggleEditAssetSection() {
    var isAset = document.getElementById('edit_item_type').value === 'ASET';
    document.getElementById('edit_asset_section').style.display = isAset ? 'block' : 'none';
    document.getElementById('edit_target_warehouse').value = isAset ? 'GUDANG_ASET' : 'GUDANG_KONSUMABLE';
}
document.getElementById('edit_item_type').addEventListener('change', toggleEditAssetSection);

// ---- Edit modal: open & load data via AJAX ----
document.addEventListener('click', function(e) {
    var btn = e.target.closest('.btn-edit-item');
    if (!btn) return;

    var id = btn.getAttribute('data-id');
    var modal = new bootstrap.Modal(document.getElementById('modalEdit'));

    document.getElementById('edit_loading').style.display = 'block';
    document.getElementById('edit_form_body').style.display = 'none';
    document.getElementById('edit_submit_btn').disabled = true;

    modal.show();

    fetch('index.php?page=inventory_master_get_item&id=' + id)
        .then(function(r) { return r.json(); })
        .then(function(item) {
            document.getElementById('edit_id').value            = item.id;
            document.getElementById('edit_category').value      = item.category;
            document.getElementById('edit_item_name').value     = item.item_name;
            document.getElementById('edit_unit').value          = item.unit;
            document.getElementById('edit_item_type').value     = item.item_type;
            document.getElementById('edit_target_warehouse').value = item.target_warehouse;
            document.getElementById('edit_is_active').value     = item.is_active;

            // Populate asset codes
            var container = document.getElementById('edit_codes_container');
            container.innerHTML = '';
            if (item.item_type === 'ASET') {
                var codes = item.asset_codes && item.asset_codes.length ? item.asset_codes : [''];
                codes.forEach(function(code) {
                    container.insertAdjacentHTML('beforeend', makeCodeRow(code));
                });
            }

            toggleEditAssetSection();

            document.getElementById('edit_loading').style.display = 'none';
            document.getElementById('edit_form_body').style.display = 'block';
            document.getElementById('edit_submit_btn').disabled = false;
        })
        .catch(function() {
            document.getElementById('edit_loading').innerHTML = '<p class="text-danger">Gagal memuat data. Silakan coba lagi.</p>';
        });
});
</script>

<?php include '../views/layouts/footer.php'; ?>

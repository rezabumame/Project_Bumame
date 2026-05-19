<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<?php
$asetItems = array_filter($items, fn($i) => $i['item_type'] === 'ASET');
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
            <span class="badge bg-primary">
                <?php echo (int)$item['asset_code_count']; ?> kode
            </span>
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
            <a href="index.php?page=inventory_master_edit&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-warning">
                <i class="fas fa-edit"></i>
            </a>
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
        <a href="index.php?page=inventory_master_create" class="btn btn-primary rounded-pill px-4 shadow-sm">
            <i class="fas fa-plus me-2"></i>Add New Item
        </a>
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
        <!-- Tab ASET -->
        <div class="tab-pane fade show active" id="tab-aset" role="tabpanel">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-table me-1"></i>List Item Aset
                </div>
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

        <!-- Tab KONSUMABLE -->
        <div class="tab-pane fade" id="tab-konsumable" role="tabpanel">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-table me-1"></i>List Item Konsumable
                </div>
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

<script>
$(document).ready(function() {
    $('#tableAset').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json' }
    });
    $('#tableKonsumable').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json' }
    });
});
</script>

<?php include '../views/layouts/footer.php'; ?>

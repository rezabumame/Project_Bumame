<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

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
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            List Inventory Items
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Item Name</th>
                        <th>Type</th>
                        <th>Unit</th>
                        <th>Target Warehouse</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['category']); ?></td>
                        <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                        <td>
                            <span class="badge <?php echo $item['item_type'] == 'ASET' ? 'bg-primary' : 'bg-info'; ?>">
                                <?php echo htmlspecialchars($item['item_type']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($item['unit']); ?></td>
                        <td>
                            <span class="badge <?php echo $item['target_warehouse'] == 'GUDANG_ASET' ? 'bg-warning text-dark' : 'bg-secondary'; ?>">
                                <?php echo htmlspecialchars($item['target_warehouse']); ?>
                            </span>
                        </td>
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
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../views/layouts/footer.php'; ?>
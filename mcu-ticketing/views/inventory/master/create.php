<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center page-header-container">
        <div>
            <h1 class="page-header-title">Add New Inventory Item</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="index.php?page=inventory_master_index">Master Inventory</a></li>
                    <li class="breadcrumb-item active">Add New</li>
                </ol>
            </nav>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-plus me-1"></i>
            Item Details
        </div>
        <div class="card-body">
            <form action="index.php?page=inventory_master_store" method="POST">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="category" class="form-label">Category</label>
                        <input type="text" class="form-control" id="category" name="category" required placeholder="e.g., Elektronik, APD, ATK">
                    </div>
                    <div class="col-md-6">
                        <label for="item_name" class="form-label">Item Name</label>
                        <input type="text" class="form-control" id="item_name" name="item_name" required>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="item_type" class="form-label">Item Type</label>
                        <select class="form-select" id="item_type" name="item_type" required>
                            <option value="ASET">ASET</option>
                            <option value="KONSUMABLE">KONSUMABLE</option>
                        </select>
                        <div class="form-text">Determines if item is a fixed asset or consumable.</div>
                    </div>
                    <div class="col-md-6">
                        <label for="unit" class="form-label">Unit</label>
                        <input type="text" class="form-control" id="unit" name="unit" required placeholder="e.g., Pcs, Unit, Box, Rim">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="target_warehouse" class="form-label">Target Warehouse</label>
                    <select class="form-select" id="target_warehouse" name="target_warehouse" required>
                        <option value="GUDANG_ASET">GUDANG_ASET</option>
                        <option value="GUDANG_KONSUMABLE">GUDANG_KONSUMABLE</option>
                    </select>
                    <div class="form-text">Requests for this item will be routed to this warehouse.</div>
                </div>
                
                <div class="d-flex justify-content-end">
                    <a href="index.php?page=inventory_master_index" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Create Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Auto-select Target Warehouse based on Item Type
    document.getElementById('item_type').addEventListener('change', function() {
        var type = this.value;
        var warehouseSelect = document.getElementById('target_warehouse');
        
        if (type === 'ASET') {
            warehouseSelect.value = 'GUDANG_ASET';
        } else if (type === 'KONSUMABLE') {
            warehouseSelect.value = 'GUDANG_KONSUMABLE';
        }
    });
</script>

<?php include '../views/layouts/footer.php'; ?>
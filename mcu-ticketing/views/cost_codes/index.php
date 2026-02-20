<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<div class="container-fluid px-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center page-header-container">
        <div>
            <h1 class="page-header-title">Expense Code Management</h1>
            <p class="page-header-subtitle">Centralized cost codes for RAB, Consumption, and Vendor Memos.</p>
        </div>
        <?php if ($_SESSION['role'] == 'superadmin'): ?>
        <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addCostCodeModal">
            <i class="fas fa-plus me-2"></i>Add New Code
        </button>
        <?php endif; ?>
    </div>

    <!-- Alert Section -->
    <?php if (isset($_GET['status'])): ?>
        <div class="alert alert-<?php echo $_GET['status'] == 'error' ? 'danger' : 'success'; ?> alert-dismissible fade show shadow-sm border-0" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-<?php echo $_GET['status'] == 'error' ? 'exclamation-circle' : 'check-circle'; ?> me-2 fs-5"></i>
                <div>
                    <?php 
                        if ($_GET['status'] == 'success') echo "New expense code has been successfully created.";
                        elseif ($_GET['status'] == 'deleted') echo "Expense code has been deleted.";
                        elseif ($_GET['status'] == 'updated') echo "Expense code details have been updated.";
                        else echo "An error occurred during the operation.";
                    ?>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php
    $vendor_codes = [];
    $rab_codes = [];
    $konsumsi_codes = [];

    foreach ($cost_codes as $code) {
        if ($code['category'] == 'Vendor (Internal Memo)') {
            $vendor_codes[] = $code;
        } elseif ($code['category'] == 'RAB') {
            $rab_codes[] = $code;
        } elseif ($code['category'] == 'Konsumsi') {
            $konsumsi_codes[] = $code;
        }
    }

    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'vendor';
    ?>

    <!-- Main Content Card -->
    <div class="card border-0 shadow-lg rounded-3 overflow-hidden">
        <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0">
            <ul class="nav nav-tabs card-header-tabs nav-fill" id="costCodeTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold py-3 <?php echo $active_tab == 'vendor' ? 'active text-primary border-top-3 border-primary' : 'text-muted'; ?>" 
                            id="vendor-tab" data-bs-toggle="tab" data-bs-target="#vendor" type="button" role="tab">
                        <i class="fas fa-file-invoice me-2"></i>Vendor
                        <span class="badge bg-light text-dark ms-2 rounded-pill"><?php echo count($vendor_codes); ?></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold py-3 <?php echo $active_tab == 'rab' ? 'active text-primary border-top-3 border-primary' : 'text-muted'; ?>" 
                            id="rab-tab" data-bs-toggle="tab" data-bs-target="#rab" type="button" role="tab">
                        <i class="fas fa-calculator me-2"></i>RAB
                        <span class="badge bg-light text-dark ms-2 rounded-pill"><?php echo count($rab_codes); ?></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold py-3 <?php echo $active_tab == 'konsumsi' ? 'active text-primary border-top-3 border-primary' : 'text-muted'; ?>" 
                            id="konsumsi-tab" data-bs-toggle="tab" data-bs-target="#konsumsi" type="button" role="tab">
                        <i class="fas fa-utensils me-2"></i>Konsumsi
                        <span class="badge bg-light text-dark ms-2 rounded-pill"><?php echo count($konsumsi_codes); ?></span>
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-0">
            <div class="tab-content" id="costCodeTabsContent">
                
                <!-- Helper for Table Rendering -->
                <?php 
                function renderCostTable($id, $data) { 
                ?>
                <div class="p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 w-100" id="<?php echo $id; ?>">
                            <thead class="bg-light text-uppercase small text-muted">
                                <tr>
                                    <th class="ps-3 py-3 rounded-start" style="white-space: nowrap;">Code</th>
                                    <th class="py-3 d-none d-md-table-cell">Lookup Value</th>
                                    <th class="py-3 d-none d-md-table-cell">Description</th>
                                    <th class="text-end pe-3 py-3 rounded-end" style="width: 100px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data as $code): ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-bold text-dark font-monospace fs-6"><?php echo htmlspecialchars($code['code']); ?></div>
                                        <!-- Mobile-only Lookup Display -->
                                        <div class="d-md-none mt-2">
                                            <?php 
                                            $values = array_filter(array_map('trim', explode(',', $code['lookup_value'])));
                                            $limit = 2;
                                            $count = count($values);
                                            foreach(array_slice($values, 0, $limit) as $val): 
                                            ?>
                                                <span class="badge rounded-pill px-2 py-1 me-1 mb-1" style="background-color: #E8F0FE; color: #1967D2; font-weight: 500; font-size: 0.75rem;">
                                                    <?php echo htmlspecialchars($val); ?>
                                                </span>
                                            <?php endforeach; ?>
                                            <?php if($count > $limit): ?>
                                                <span class="badge rounded-pill px-2 py-1 me-1 mb-1 text-muted border" style="background-color: #f8f9fa; font-size: 0.75rem;">
                                                    +<?php echo $count - $limit; ?> more
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="d-none d-md-table-cell">
                                        <?php if($code['lookup_value']): 
                                            $values = array_filter(array_map('trim', explode(',', $code['lookup_value'])));
                                            $limit = 2;
                                            $count = count($values);
                                            foreach(array_slice($values, 0, $limit) as $val): 
                                        ?>
                                            <span class="badge rounded-pill px-3 py-2 me-1 mb-1" style="background-color: #E8F0FE; color: #1967D2; font-weight: 500;">
                                                <?php echo htmlspecialchars($val); ?>
                                            </span>
                                        <?php endforeach; ?>
                                        <?php if($count > $limit): ?>
                                            <span class="badge rounded-pill px-2 py-2 me-1 mb-1 text-muted border" style="background-color: #f8f9fa;">
                                                +<?php echo $count - $limit; ?> more
                                            </span>
                                        <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted small fst-italic">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="d-none d-md-table-cell">
                                        <div class="text-truncate-2-lines text-muted" style="max-width: 400px; font-size: 0.9em;">
                                            <?php echo htmlspecialchars($code['description']); ?>
                                        </div>
                                    </td>
                                    <td class="text-end pe-3">
                                        <?php if ($_SESSION['role'] == 'superadmin'): ?>
                                        <div class="btn-group">
                                            <button type="button" 
                                                    class="btn btn-sm btn-light text-primary edit-btn"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editCostCodeModal"
                                                    data-id="<?php echo $code['id']; ?>"
                                                    data-code="<?php echo htmlspecialchars($code['code']); ?>"
                                                    data-category="<?php echo htmlspecialchars($code['category']); ?>"
                                                    data-lookup="<?php echo htmlspecialchars($code['lookup_value']); ?>"
                                                    data-desc="<?php echo htmlspecialchars($code['description']); ?>"
                                                    title="Edit">
                                                <i class="fas fa-pen"></i>
                                            </button>
                                            <a href="index.php?page=cost_codes_delete&id=<?php echo $code['id']; ?>" 
                                               class="btn btn-sm btn-light text-danger" 
                                               onclick="return confirm('Are you sure you want to delete this code?')"
                                               title="Delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php } ?>

                <!-- Vendor Tab -->
                <div class="tab-pane fade <?php echo $active_tab == 'vendor' ? 'show active' : ''; ?>" id="vendor" role="tabpanel">
                    <?php renderCostTable('tableVendor', $vendor_codes); ?>
                </div>

                <!-- RAB Tab -->
                <div class="tab-pane fade <?php echo $active_tab == 'rab' ? 'show active' : ''; ?>" id="rab" role="tabpanel">
                    <?php renderCostTable('tableRAB', $rab_codes); ?>
                </div>

                <!-- Konsumsi Tab -->
                <div class="tab-pane fade <?php echo $active_tab == 'konsumsi' ? 'show active' : ''; ?>" id="konsumsi" role="tabpanel">
                    <?php renderCostTable('tableKonsumsi', $konsumsi_codes); ?>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Add Cost Code Modal -->
<div class="modal fade" id="addCostCodeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header modal-modern-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-plus-circle me-2"></i>New Expense Code</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?page=cost_codes_create" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase text-muted">Expense Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control font-monospace" placeholder="e.g. EXP-OPS-001" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase text-muted">Category <span class="text-danger">*</span></label>
                        <select name="category" class="form-select" required>
                            <option value="Vendor (Internal Memo)">Vendor (Internal Memo)</option>
                            <option value="RAB">RAB</option>
                            <option value="Konsumsi">Konsumsi</option>
                        </select>
                        <div class="form-text">Determines which tab this code appears in.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase text-muted">Lookup Value / Display Name</label>
                        <input type="text" name="lookup_value" class="form-control" placeholder="e.g. Sewa Kendaraan">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase text-muted">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Additional details..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-link text-secondary text-decoration-none" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 rounded-pill">Save Code</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Cost Code Modal -->
<div class="modal fade" id="editCostCodeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header modal-modern-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>Edit Expense Code</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?page=cost_codes_update" method="POST">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase text-muted">Expense Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" id="edit_code" class="form-control font-monospace" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase text-muted">Category <span class="text-danger">*</span></label>
                        <select name="category" id="edit_category" class="form-select" required>
                            <option value="Vendor (Internal Memo)">Vendor (Internal Memo)</option>
                            <option value="RAB">RAB</option>
                            <option value="Konsumsi">Konsumsi</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase text-muted">Lookup Value / Display Name</label>
                        <input type="text" name="lookup_value" id="edit_lookup_value" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase text-muted">Description</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-link text-secondary text-decoration-none" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 rounded-pill">Update Code</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    const tableConfig = {
        "pageLength": 10,
        "language": {
            "search": "",
            "searchPlaceholder": "Search...",
            "lengthMenu": "_MENU_",
            "info": "Showing _START_ – _END_ of _TOTAL_",
            "infoEmpty": "No records found",
            "infoFiltered": "(filtered from _MAX_ total records)",
            "paginate": {
                "first": "First",
                "last": "Last",
                "next": "Next",
                "previous": "Previous"
            },
            "emptyTable": `<div class="text-center py-5">
                            <div class="mb-3">
                                <i class="fas fa-folder-open fa-3x text-muted opacity-50"></i>
                            </div>
                            <h6 class="text-muted">No expense codes found</h6>
                            <button class="btn btn-sm btn-outline-primary mt-2" data-bs-toggle="modal" data-bs-target="#addCostCodeModal">
                                Add New Code
                            </button>
                           </div>`
        },
        "dom": "<'row align-items-center bg-white px-3 py-3 border-bottom'<'col-4 col-md-3 d-flex align-items-center'l><'col-4 col-md-6 text-center text-muted small'i><'col-4 col-md-3'f>>" +
               "<'row'<'col-12'tr>>" +
               "<'row px-3 py-3 align-items-center'<'col-12 d-flex justify-content-end'p>>",
        "renderer": "bootstrap",
        "autoWidth": false,
        "drawCallback": function(settings) {
            // Styling adjustments after draw
            $('.dataTables_paginate .pagination').addClass('pagination-sm mb-0');
        }
    };

    $('#tableVendor').DataTable(tableConfig);
    $('#tableRAB').DataTable(tableConfig);
    $('#tableKonsumsi').DataTable(tableConfig);

    // Edit Button Logic
    $(document).on('click', '.edit-btn', function() {
        var id = $(this).data('id');
        var code = $(this).data('code');
        var category = $(this).data('category');
        var lookup = $(this).data('lookup');
        var desc = $(this).data('desc');

        $('#edit_id').val(id);
        $('#edit_code').val(code);
        $('#edit_category').val(category);
        $('#edit_lookup_value').val(lookup);
        $('#edit_description').val(desc);
    });
});
</script>

<style>
/* Custom Tab Styles */
.nav-tabs .nav-link {
    border: none;
    border-bottom: 3px solid transparent;
    color: #6c757d;
    transition: all 0.2s ease;
    font-size: 0.95rem;
}
.nav-tabs .nav-link:hover {
    color: #204EAB;
    background-color: transparent;
}
.nav-tabs .nav-link.active {
    color: #204EAB;
    border-bottom: 3px solid #204EAB;
    background-color: transparent;
}

/* Table Styles */
.custom-table tbody tr:nth-of-type(odd) {
    background-color: rgba(0, 0, 0, 0.01);
}
.custom-table tbody tr:hover {
    background-color: rgba(32, 78, 171, 0.03);
}
.custom-table td {
    padding-top: 1rem !important;
    padding-bottom: 1rem !important;
    border-bottom: 1px solid #f0f0f0;
}
.text-truncate-2-lines {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* DataTables Customization */
div.dataTables_wrapper div.dataTables_filter input {
    border-radius: 20px;
    padding: 6px 15px 6px 40px; /* Left padding for icon */
    border: 1px solid #e0e0e0;
    margin-left: 0;
    width: 100%;
    background-color: #f8f9fa;
    transition: all 0.2s;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23999' class='bi bi-search' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z'%3F%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: left 15px center;
}
div.dataTables_wrapper div.dataTables_filter input:focus {
    background-color: #fff;
    border-color: #204EAB;
    box-shadow: 0 0 0 0.2rem rgba(32, 78, 171, 0.1);
}
div.dataTables_wrapper div.dataTables_length select {
    border-radius: 20px;
    padding: 6px 30px 6px 15px;
    border: 1px solid #e0e0e0;
    cursor: pointer;
    background-color: #fff;
}
.page-item.active .page-link {
    background-color: #204EAB;
    border-color: #204EAB;
    color: #fff;
}
.page-link {
    color: #555;
    border-radius: 50%;
    margin: 0 3px;
    border: none;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
}
.page-link:hover {
    background-color: #e9ecef;
    color: #204EAB;
}
.page-item.disabled .page-link {
    background-color: transparent;
    color: #ccc;
}
</style>

<?php include '../views/layouts/footer.php'; ?>
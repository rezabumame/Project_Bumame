<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center page-header-container">
        <div>
            <h1 class="page-header-title">User Management</h1>
            <p class="page-header-subtitle">Add, edit, or remove system users.</p>
        </div>
        <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#userModal" onclick="resetForm()">
            <i class="fas fa-plus me-2"></i>Add User
        </button>
    </div>

    <?php if (isset($_GET['error']) && $_GET['error'] == 'duplicate_username'): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
        <div class="d-flex align-items-center">
            <div class="me-3">
                <i class="fas fa-exclamation-triangle fa-2x"></i>
            </div>
            <div>
                <h5 class="alert-heading fw-bold mb-1">Gagal Menyimpan!</h5>
                <p class="mb-0">Username yang Anda masukkan sudah terdaftar. Mohon gunakan username yang berbeda.</p>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <?php
    $roleMap = [];
    foreach ($roles as $r) {
        $roleMap[$r['role_key']] = $r['role_name'];
    }
    ?>
    <div class="card border-0 shadow-sm rounded-card">
        <div class="card-header bg-white py-3 border-bottom-0">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0 fw-bold text-dark">User List</h5>
                </div>
                <div class="col-auto">
                    <div class="input-group input-group-sm" style="width: 250px;">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="userSearch" class="form-control bg-light border-start-0" placeholder="Search users...">
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="usersTable" class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 text-uppercase text-muted small fw-bold">User</th>
                            <th class="text-uppercase text-muted small fw-bold">Jabatan</th>
                            <th class="text-uppercase text-muted small fw-bold">Role</th>
                            <th class="text-uppercase text-muted small fw-bold">Status</th>
                            <th class="text-uppercase text-muted small fw-bold">Created At</th>
                            <th class="text-end pe-4 text-uppercase text-muted small fw-bold">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($user['full_name']); ?></div>
                                <small class="text-muted"><?php echo htmlspecialchars($user['username']); ?></small>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($user['jabatan'] ?? '-'); ?>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border-0 p-0 fs-6">
                                    <?php echo htmlspecialchars($roleMap[$user['role']] ?? $user['role']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($user['is_active']): ?>
                                    <span class="badge bg-success-subtle text-success rounded-pill">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger rounded-pill">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted small">
                                <?php echo date('d M Y', strtotime($user['created_at'])); ?>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-outline-primary me-1" 
                                        onclick='editUser(<?php echo json_encode($user); ?>)'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php if($user['role'] !== 'superadmin'): ?>
                                <a href="index.php?page=superadmin_delete_user&id=<?php echo $user['user_id']; ?>" 
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Are you sure?')">
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
</div>

<!-- User Modal -->
<div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header modal-modern-header">
                <h5 class="modal-title fw-bold" id="modalTitle">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?page=superadmin_save_user" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="user_id" id="user_id">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase">Username / Email</label>
                        <input type="text" class="form-control" name="username" id="username" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase">Full Name</label>
                        <input type="text" class="form-control" name="full_name" id="full_name" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase">Jabatan</label>
                        <input type="text" class="form-control" name="jabatan" id="jabatan" placeholder="e.g. Staff IT">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase">Role</label>
                        <select class="form-select" name="role" id="role" required>
                            <option value="">Select Role</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?php echo $role['role_key']; ?>">
                                    <?php echo $role['role_name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase">Password</label>
                        <input type="password" class="form-control" name="password" id="password" placeholder="Leave blank to keep current">
                        <small class="text-muted d-none" id="passHelp">Only fill if changing password</small>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">Save User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../views/layouts/footer.php'; ?>

<script>
function resetForm() {
    document.getElementById('modalTitle').innerText = 'Add New User';
    document.getElementById('user_id').value = '';
    document.getElementById('username').value = '';
    document.getElementById('full_name').value = '';
    document.getElementById('jabatan').value = '';
    document.getElementById('role').value = '';
    document.getElementById('password').required = true;
    document.getElementById('passHelp').classList.add('d-none');
}

function editUser(user) {
    var myModalEl = document.getElementById('userModal');
    var myModal = bootstrap.Modal.getInstance(myModalEl);
    if (!myModal) {
        myModal = new bootstrap.Modal(myModalEl);
    }
    
    document.getElementById('modalTitle').innerText = 'Edit User';
    document.getElementById('user_id').value = user.user_id;
    document.getElementById('username').value = user.username;
    document.getElementById('full_name').value = user.full_name;
    document.getElementById('jabatan').value = user.jabatan || '';
    document.getElementById('role').value = user.role; // Assuming role key matches
    
    // Password not required for edit
    document.getElementById('password').required = false;
    document.getElementById('passHelp').classList.remove('d-none');
    
    myModal.show();
}

// DataTables Initialization
$(document).ready(function() {
    var table = $('#usersTable').DataTable({
                    "dom": 'rtip', // Hide default search bar, we use custom one
                    "searching": true,
                    "pageLength": 50,
                    "ordering": true,
                    "order": [[ 2, "asc" ]], // Group by Role
                    "language": {
                        "emptyTable": "No users found"
                    },
        "drawCallback": function ( settings ) {
            var api = this.api();
            var rows = api.rows( {page:'current'} ).nodes();
            var last = null;
 
            api.column(2, {page:'current'} ).data().each( function ( group, i ) {
                // Extract text from badge HTML in column 2
                var groupName = $(group).text();
                
                if ( last !== groupName ) {
                    $(rows).eq( i ).before(
                        '<tr class="bg-light bg-opacity-50"><td colspan="6" class="ps-4 py-2 border-bottom"><span class="badge bg-primary rounded-pill px-3"><i class="fas fa-users-cog me-2"></i>' + groupName + '</span></td></tr>'
                    );
 
                    last = groupName;
                }
            });
        }
    });

    // Custom search binding
    $('#userSearch').on('keyup change input', function() {
        table.search(this.value).draw();
    });
});
</script>

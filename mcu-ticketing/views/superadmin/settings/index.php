<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center page-header-container">
        <div>
            <h1 class="page-header-title">System Configuration</h1>
            <p class="page-header-subtitle">Configure system-wide settings and parameters.</p>
        </div>
    </div>
            
            <?php if (isset($_GET['status']) && $_GET['status'] == 'updated'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    Settings updated successfully.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="settingsTab" role="tablist">
                        <?php 
                        $active = 'active'; 
                        foreach ($grouped_settings as $group_name => $items): 
                            $slug = strtolower(str_replace([' ', '/'], '_', $group_name));
                        ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?php echo $active; ?>" id="<?php echo $slug; ?>-tab" data-bs-toggle="tab" data-bs-target="#<?php echo $slug; ?>" type="button" role="tab" aria-controls="<?php echo $slug; ?>" aria-selected="<?php echo $active ? 'true' : 'false'; ?>">
                                <?php echo htmlspecialchars($group_name); ?>
                            </button>
                        </li>
                        <?php 
                            $active = ''; 
                        endforeach; 
                        ?>
                    </ul>
                </div>
                <div class="card-body">
                    <form action="index.php?page=settings_update" method="POST">
                        <div class="tab-content" id="settingsTabContent">
                            <?php 
                            $active = 'show active'; 
                            foreach ($grouped_settings as $group_name => $items): 
                                $slug = strtolower(str_replace([' ', '/'], '_', $group_name));
                            ?>
                            <div class="tab-pane fade <?php echo $active; ?>" id="<?php echo $slug; ?>" role="tabpanel" aria-labelledby="<?php echo $slug; ?>-tab">
                                <h5 class="card-title mt-3 mb-3"><?php echo htmlspecialchars($group_name); ?></h5>
                                <?php if ($group_name === 'RAB Configuration'): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="fw-bold text-secondary mb-0">Role & Fee Configuration</h6>
                                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                                            <i class="fas fa-plus me-1"></i> Add New Personnel Role
                                        </button>
                                    </div>
                                    
                                    <!-- Modal Delete Role Removed from here to prevent loops/nesting issues -->

                                    <?php
                                    $dalam_kota = [];
                                    $luar_kota = [];
                                    foreach ($items as $item) {
                                        if (strpos($item['setting_key'], 'fee_dalam_kota_') !== false) {
                                            $role = str_replace('fee_dalam_kota_', '', $item['setting_key']);
                                            $dalam_kota[$role] = $item;
                                        } elseif (strpos($item['setting_key'], 'fee_luar_kota_') !== false) {
                                            $role = str_replace('fee_luar_kota_', '', $item['setting_key']);
                                            $luar_kota[$role] = $item;
                                        }
                                    }
                                    $roles = array_unique(array_merge(array_keys($dalam_kota), array_keys($luar_kota)));
                                    sort($roles);
                                    ?>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card border-0">
                                                <div class="card-header bg-primary text-white fw-bold">
                                                    <i class="fas fa-building me-2"></i>Dalam Kota
                                                </div>
                                                <div class="card-body p-0">
                                                    <table class="table table-striped table-hover mb-0">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>Role</th>
                                                                <th style="width: 180px;">Fee (IDR)</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($roles as $role): ?>
                                                                <?php if (isset($dalam_kota[$role])): $item = $dalam_kota[$role]; ?>
                                                                <tr>
                                                                    <td class="align-middle fw-bold text-secondary">
                                                                        <div class="d-flex justify-content-between align-items-center">
                                                                            <span><?php echo str_replace('_', ' ', $role); ?></span>
                                                                            <button type="button" class="btn btn-link text-danger p-0 ms-2" onclick="confirmDeleteRole('<?php echo $role; ?>', '<?php echo str_replace('_', ' ', $role); ?>')" title="Delete Role">
                                                                                <i class="fas fa-trash-alt small"></i>
                                                                            </button>
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <div class="input-group input-group-sm">
                                                                            <span class="input-group-text">Rp</span>
                                                                            <input type="text" class="form-control text-end currency-input" name="settings[<?php echo $item['setting_key']; ?>]" value="<?php echo number_format((float)$item['setting_value'], 0, ',', '.'); ?>" required>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                                <?php endif; ?>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card border-0">
                                                <div class="card-header bg-success text-white fw-bold">
                                                    <i class="fas fa-plane-departure me-2"></i>Luar Kota
                                                </div>
                                                <div class="card-body p-0">
                                                    <table class="table table-striped table-hover mb-0">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>Role</th>
                                                                <th style="width: 180px;">Fee (IDR)</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($roles as $role): ?>
                                                                <?php if (isset($luar_kota[$role])): $item = $luar_kota[$role]; ?>
                                                                <tr>
                                                                    <td class="align-middle fw-bold text-secondary"><?php echo str_replace('_', ' ', $role); ?></td>
                                                                    <td>
                                                                        <div class="input-group input-group-sm">
                                                                            <span class="input-group-text">Rp</span>
                                                                            <input type="text" class="form-control text-end currency-input" name="settings[<?php echo $item['setting_key']; ?>]" value="<?php echo number_format((float)$item['setting_value'], 0, ',', '.'); ?>" required>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                                <?php endif; ?>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Other Parameters Section -->
                                    <h6 class="fw-bold mt-4 mb-3 text-secondary">Parameter Lainnya</h6>
                                    <div class="table-responsive">
                                        <table class="table table-hover border">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 30%;">Setting Key</th>
                                                    <th style="width: 40%;">Description</th>
                                                    <th style="width: 30%;">Value</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                foreach ($items as $item): 
                                                    if (strpos($item['setting_key'], 'fee_dalam_kota_') === false && strpos($item['setting_key'], 'fee_luar_kota_') === false):
                                                ?>
                                                <tr>
                                                    <td class="fw-bold small text-muted"><?php echo htmlspecialchars($item['setting_key']); ?></td>
                                                    <td><?php echo htmlspecialchars($item['description']); ?></td>
                                                    <td>
                                                        <?php if (strpos($item['setting_key'], 'fee') !== false || strpos($item['setting_key'], 'price') !== false): ?>
                                                            <div class="input-group input-group-sm">
                                                                <span class="input-group-text">Rp</span>
                                                                <input type="text" class="form-control text-end currency-input" name="settings[<?php echo $item['setting_key']; ?>]" value="<?php echo number_format((float)$item['setting_value'], 0, ',', '.'); ?>" required>
                                                            </div>
                                                        <?php elseif (strpos($item['setting_key'], 'rab_') === 0): ?>
                                                            <textarea class="form-control font-monospace small" name="settings[<?php echo $item['setting_key']; ?>]" rows="4" required><?php echo htmlspecialchars($item['setting_value']); ?></textarea>
                                                        <?php else: ?>
                                                            <input type="text" class="form-control" name="settings[<?php echo $item['setting_key']; ?>]" value="<?php echo htmlspecialchars($item['setting_value']); ?>" required>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php 
                                                    endif; 
                                                endforeach; 
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <?php if (empty($items)): ?>
                                        <p class="text-muted">No settings found in this category.</p>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th style="width: 30%;">Setting Key</th>
                                                        <th style="width: 40%;">Description</th>
                                                        <th style="width: 30%;">Value</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($items as $item): ?>
                                                    <tr>
                                                        <td class="fw-bold small text-muted"><?php echo htmlspecialchars($item['setting_key']); ?></td>
                                                        <td><?php echo htmlspecialchars($item['description']); ?></td>
                                                        <td>
                                                            <?php if ($item['setting_key'] === 'tat_config_rules'): ?>
                                                                <input type="hidden" id="tat_config_rules_input" name="settings[<?php echo $item['setting_key']; ?>]" value="<?php echo htmlspecialchars($item['setting_value']); ?>">
                                                                <div class="border rounded p-2 bg-light">
                                                                    <table class="table table-sm table-bordered mb-2 bg-white" id="tat_rules_table">
                                                                        <thead class="table-light">
                                                                            <tr>
                                                                                <th>Keyword (e.g. Rectal)</th>
                                                                                <th width="100">Days</th>
                                                                                <th width="50"></th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody></tbody>
                                                                    </table>
                                                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addTatRuleRow()">
                                                                        <i class="fas fa-plus me-1"></i> Add Rule
                                                                    </button>
                                                                </div>
                                                                <script>
                                                                    (function() {
                                                                        let rules = [];
                                                                        try {
                                                                            rules = JSON.parse(document.getElementById('tat_config_rules_input').value) || [];
                                                                        } catch(e) { rules = []; }

                                                                        const tableBody = document.querySelector('#tat_rules_table tbody');
                                                                        const hiddenInput = document.getElementById('tat_config_rules_input');

                                                                        window.renderTatRules = function() {
                                                                            tableBody.innerHTML = '';
                                                                            rules.forEach((rule, index) => {
                                                                                const tr = document.createElement('tr');
                                                                                tr.innerHTML = `
                                                                                    <td>
                                                                                        <input type="text" class="form-control form-control-sm" value="${rule.keyword}" onchange="updateTatRule(${index}, 'keyword', this.value)" placeholder="Exam Name">
                                                                                    </td>
                                                                                    <td>
                                                                                        <input type="number" class="form-control form-control-sm" value="${rule.days}" onchange="updateTatRule(${index}, 'days', this.value)" min="1">
                                                                                    </td>
                                                                                    <td class="text-center align-middle">
                                                                                        <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeTatRule(${index})">
                                                                                            <i class="fas fa-trash"></i>
                                                                                        </button>
                                                                                    </td>
                                                                                `;
                                                                                tableBody.appendChild(tr);
                                                                            });
                                                                            hiddenInput.value = JSON.stringify(rules);
                                                                        };

                                                                        window.addTatRuleRow = function() {
                                                                            rules.push({keyword: '', days: 3});
                                                                            renderTatRules();
                                                                        };

                                                                        window.removeTatRule = function(index) {
                                                                            rules.splice(index, 1);
                                                                            renderTatRules();
                                                                        };

                                                                        window.updateTatRule = function(index, key, value) {
                                                                            rules[index][key] = key === 'days' ? parseInt(value) : value;
                                                                            renderTatRules();
                                                                        };

                                                                        renderTatRules();
                                                                    })();
                                                                </script>
                                                            <?php else: ?>
                                                                <input type="text" class="form-control" name="settings[<?php echo $item['setting_key']; ?>]" value="<?php echo htmlspecialchars($item['setting_value']); ?>" required>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <?php 
                                $active = ''; 
                            endforeach; 
                            ?>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4 border-top pt-3">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save All Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Add Role -->
<div class="modal fade" id="addRoleModal" tabindex="-1" aria-labelledby="addRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addRoleModalLabel">Add New Personnel Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?page=settings_add_role" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="role_name" class="form-label">Role Name</label>
                        <input type="text" class="form-control" id="role_name" name="role_name" placeholder="e.g. Dokter Gigi" required>
                        <div class="form-text">This will add new fee configurations for this role.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Delete Role (Moved to bottom) -->
<div class="modal fade" id="deleteRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Delete Role</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?page=settings_delete_role" method="POST">
                <div class="modal-body">
                    <p>Are you sure you want to delete the role <strong id="deleteRoleName"></strong>?</p>
                    <p class="small text-danger">This will remove the fee configuration for this role. This action cannot be undone.</p>
                    <input type="hidden" name="role_key" id="deleteRoleKey">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function confirmDeleteRole(key, name) {
        document.getElementById('deleteRoleKey').value = key;
        document.getElementById('deleteRoleName').textContent = name;
        var myModalEl = document.getElementById('deleteRoleModal');
        var modal = bootstrap.Modal.getOrCreateInstance(myModalEl);
        modal.show();
    }
</script>

<?php include '../views/layouts/footer.php'; ?>

<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center page-header-container mb-4">
        <div>
            <h1 class="page-header-title">Staff Assignment</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="index.php?page=man_power_mcu">Staff Assignment</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($project['nama_project']); ?></li>
                </ol>
            </nav>
        </div>
        <div class="d-flex align-items-center gap-3">
            <a href="index.php?page=man_power_mcu" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back
            </a>
            <span class="badge bg-primary fs-6"><?php echo $project['project_id']; ?></span>
        </div>
    </div>

    <!-- Alerts -->
    <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars(urldecode($_GET['msg'])); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars(urldecode($_GET['error'])); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Left Column: Project Info & Requirements -->
        <div class="col-lg-4">
            <!-- Project Details -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-transparent border-0 fw-bold">
                    <i class="fas fa-info-circle me-2 text-primary"></i>Project Information
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Project</small>
                        <span class="fw-bold"><?php echo htmlspecialchars($project['nama_project']); ?></span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Location</small>
                        <span><?php echo htmlspecialchars($project['alamat'] ?? $project['clinic_location']); ?></span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Korlap</small>
                        <span><?php echo htmlspecialchars($project['korlap_name'] ?? '-'); ?></span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Dates</small>
                        <?php foreach($project_dates as $d): ?>
                            <span class="badge bg-light text-dark border me-1 mb-1">
                                <?php echo date('d M Y', strtotime($d)); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- RAB Requirements vs Realization -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-0 fw-bold d-flex justify-content-between">
                    <span><i class="fas fa-clipboard-list me-2 text-primary"></i>RAB vs Actual</span>
                    <span class="badge bg-info text-dark">Plan</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3">Role</th>
                                    <th class="text-center">RAB</th>
                                    <th class="text-center">Actual</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                // Flatten RAB requirements to Role => Qty
                                $rab_map = [];
                                foreach ($rab_requirements as $req) {
                                    $rab_map[$req['item_name']] = $req['qty'];
                                }
                                
                                // Calculate Total Actual per role across all dates (Person-Days)
                                $actual_map = [];
                                foreach ($summary as $date => $roles) {
                                    foreach ($roles as $role => $count) {
                                        if (!isset($actual_map[$role])) $actual_map[$role] = 0;
                                        // Use SUM to reflect total person-days assigned
                                        $actual_map[$role] += $count;
                                    }
                                }

                                // Merge keys
                                $all_roles = array_unique(array_merge(array_keys($rab_map), array_keys($actual_map), ['Dokter']));
                                sort($all_roles);
                                ?>

                                <?php if(empty($all_roles)): ?>
                                    <tr><td colspan="3" class="text-center text-muted small py-3">No data available</td></tr>
                                <?php else: ?>
                                    <?php foreach ($all_roles as $role): ?>
                                    <?php 
                                        $rab_qty = $rab_map[$role] ?? 0;
                                        $act_qty = $actual_map[$role] ?? 0;
                                        $status_class = '';
                                        if ($act_qty > $rab_qty) $status_class = 'table-info fw-bold';
                                        elseif ($act_qty == $rab_qty && $rab_qty > 0) $status_class = 'table-success fw-bold';
                                        elseif ($act_qty > 0 && $act_qty < $rab_qty) $status_class = 'table-warning fw-bold';
                                    ?>
                                    <tr class="<?php echo $status_class; ?>">
                                        <td class="ps-3 small"><?php echo htmlspecialchars($role); ?></td>
                                        <td class="text-center small"><?php echo $rab_qty; ?></td>
                                        <td class="text-center small">
                                            <?php echo $act_qty; ?>
                                            <?php if($act_qty > $rab_qty): ?>
                                                <i class="fas fa-info-circle text-primary ms-1" title="Above RAB Requirement"></i>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-light small text-muted">
                    * RAB shows total personnel requirements (people x days). Actual shows total person-days assigned.
                </div>
            </div>

            <!-- Doctor Activities Schedule -->
            <div class="card border-0 shadow-sm rounded-4 mt-4">
                <div class="card-header bg-transparent border-0 fw-bold">
                    <i class="fas fa-calendar-check me-2 text-primary"></i>Doctor Schedule
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3">Date</th>
                                    <th>Activity</th>
                                    <th>Doctor</th>
                                    <th class="text-end pe-3">Method</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                // Extract doctor activities
                                $doctor_activities = [];
                                foreach ($assignments as $row) {
                                    if ($row['role'] === 'Dokter' && !empty($row['doctor_details'])) {
                                        $details = json_decode($row['doctor_details'], true);
                                        if (is_array($details)) {
                                            foreach ($details as $d) {
                                                $doctor_activities[] = [
                                                    'date' => $row['date'],
                                                    'doctor' => $row['man_power_name'],
                                                    'type' => $d['type'],
                                                    'method' => $d['method'] ?? '-'
                                                ];
                                            }
                                        }
                                    }
                                }
                                // Sort by date
                                usort($doctor_activities, function($a, $b) {
                                    return strtotime($a['date']) - strtotime($b['date']);
                                });
                                ?>

                                <?php if(empty($doctor_activities)): ?>
                                    <tr><td colspan="4" class="text-center text-muted small py-3">No scheduled activities</td></tr>
                                <?php else: ?>
                                    <?php foreach ($doctor_activities as $act): ?>
                                    <tr>
                                        <td class="ps-3 small"><?php echo date('d M', strtotime($act['date'])); ?></td>
                                        <td class="small fw-bold"><?php echo htmlspecialchars($act['type']); ?></td>
                                        <td class="small"><?php echo htmlspecialchars($act['doctor']); ?></td>
                                        <td class="text-end pe-3">
                                            <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($act['method']); ?></span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Assignment Form & List -->
        <div class="col-lg-8">
            <!-- Assignment Form -->
            <?php if($can_edit): ?>
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-transparent border-0 fw-bold">
                    <i class="fas fa-user-plus me-2 text-primary"></i>Assign Man Power
                </div>
                <div class="card-body">
                    <form action="index.php?page=man_power_assign_store" method="POST">
                        <input type="hidden" name="project_id" value="<?php echo $project['project_id']; ?>">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Assign Role <span class="text-danger">*</span></label>
                                <select class="form-select" name="role" id="roleSelect" required>
                                    <option value="">-- Choose Role --</option>
                                    <?php foreach ($all_roles as $role): ?>
                                        <option value="<?php echo htmlspecialchars($role); ?>"><?php echo htmlspecialchars($role); ?></option>
                                    <?php endforeach; ?>
                                    <option disabled>──────────</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Select Personnel <span class="text-danger">*</span></label>
                                <select class="form-select" name="man_power_id" id="personnelSelect" required>
                                    <option value="">-- Choose Person --</option>
                                    <?php foreach ($man_powers as $mp): ?>
                                    <option value="<?php echo $mp['id']; ?>" data-skills='<?php echo json_encode($mp['skills_array']); ?>'>
                                        <?php echo htmlspecialchars($mp['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Doctor Specific Fields -->
                            <div id="doctorFields" class="col-md-12 mb-3" style="display: none;">
                                <label class="form-label">Doctor Activities</label>
                                <div class="card p-3 bg-light border-0">
                                    <div class="row g-2">
                                        <!-- Pemaparan -->
                                        <div class="col-md-4">
                                            <div class="card h-100 border shadow-sm">
                                                <div class="card-body p-2">
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input" type="checkbox" name="activity_types[]" value="Pemaparan" id="act_Pemaparan">
                                                        <label class="form-check-label fw-bold" for="act_Pemaparan">Pemaparan</label>
                                                    </div>
                                                    <div class="btn-group btn-group-sm w-100" role="group">
                                                        <input type="radio" class="btn-check" name="method_Pemaparan" id="method_Pemaparan_Online" value="Online" checked>
                                                        <label class="btn btn-outline-primary" for="method_Pemaparan_Online">Online</label>
                                                        <input type="radio" class="btn-check" name="method_Pemaparan" id="method_Pemaparan_Offline" value="Offline">
                                                        <label class="btn btn-outline-primary" for="method_Pemaparan_Offline">Offline</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Konsultasi -->
                                        <div class="col-md-4">
                                            <div class="card h-100 border shadow-sm">
                                                <div class="card-body p-2">
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input" type="checkbox" name="activity_types[]" value="Konsultasi" id="act_Konsultasi">
                                                        <label class="form-check-label fw-bold" for="act_Konsultasi">Konsultasi</label>
                                                    </div>
                                                    <div class="btn-group btn-group-sm w-100" role="group">
                                                        <input type="radio" class="btn-check" name="method_Konsultasi" id="method_Konsultasi_Online" value="Online" checked>
                                                        <label class="btn btn-outline-primary" for="method_Konsultasi_Online">Online</label>
                                                        <input type="radio" class="btn-check" name="method_Konsultasi" id="method_Konsultasi_Offline" value="Offline">
                                                        <label class="btn btn-outline-primary" for="method_Konsultasi_Offline">Offline</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Health Talk -->
                                        <div class="col-md-4">
                                            <div class="card h-100 border shadow-sm">
                                                <div class="card-body p-2">
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input" type="checkbox" name="activity_types[]" value="Health Talk" id="act_Health_Talk">
                                                        <label class="form-check-label fw-bold" for="act_Health_Talk">Health Talk</label>
                                                    </div>
                                                    <div class="btn-group btn-group-sm w-100" role="group">
                                                        <input type="radio" class="btn-check" name="method_Health_Talk" id="method_Health_Talk_Online" value="Online" checked>
                                                        <label class="btn btn-outline-primary" for="method_Health_Talk_Online">Online</label>
                                                        <input type="radio" class="btn-check" name="method_Health_Talk" id="method_Health_Talk_Offline" value="Offline">
                                                        <label class="btn btn-outline-primary" for="method_Health_Talk_Offline">Offline</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Select Dates <span class="text-danger">*</span></label>
                                <div class="card p-2 bg-light">
                                    <div class="d-flex flex-wrap gap-3 mb-2">
                                        <?php foreach ($project_dates as $d): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="dates[]" value="<?php echo $d; ?>" id="date_<?php echo $d; ?>" checked>
                                            <label class="form-check-label" for="date_<?php echo $d; ?>">
                                                <?php echo date('d M Y', strtotime($d)); ?>
                                            </label>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="border-top pt-2 mt-1">
                                        <label class="form-label small text-muted mb-1">Add Custom Date (For Doctor/Follow-up)</label>
                                        <input type="date" class="form-control form-control-sm" name="manual_date" id="manualDateInput">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Notes (Optional)</label>
                                <input type="text" class="form-control" name="notes" placeholder="Shift info, specific task, etc.">
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check me-2"></i>Assign Personnel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- Assignments List -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-0 fw-bold">
                    <i class="fas fa-users me-2 text-primary"></i>Assigned Personnel
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Date</th>
                                    <th>Personnel</th>
                                    <th>Role</th>
                                    <th>Notes</th>
                                    <th class="text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($assignments) > 0): ?>
                                    <?php 
                                    $current_date = null;
                                    foreach ($assignments as $row): 
                                        $is_new_date = ($current_date !== $row['date']);
                                        $current_date = $row['date'];
                                    ?>
                                    <tr class="<?php echo $is_new_date ? 'border-top border-2' : ''; ?>">
                                        <td class="ps-4">
                                            <?php if($is_new_date): ?>
                                                <span class="badge bg-light text-dark border">
                                                    <?php echo date('d M Y', strtotime($row['date'])); ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($row['man_power_name']); ?></div>
                                            <div class="small text-muted"><?php echo htmlspecialchars($row['man_power_email'] ?? ''); ?></div>
                                            <small class="text-muted"><?php echo ucfirst($row['man_power_status']); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                                <?php echo htmlspecialchars($row['role']); ?>
                                            </span>
                                            <?php if ($row['role'] === 'Dokter' && !empty($row['doctor_details'])): ?>
                                                <div class="mt-2">
                                                    <?php 
                                                    $details = json_decode($row['doctor_details'], true);
                                                    if (is_array($details)) {
                                                        foreach ($details as $d) {
                                                            echo '<div class="d-flex align-items-center mb-1">';
                                                            echo '<i class="fas fa-check-circle text-success me-1" style="font-size: 0.7rem;"></i>';
                                                            echo '<span class="small fw-bold">' . htmlspecialchars($d['type']) . '</span>';
                                                            echo '<span class="badge bg-white text-dark border ms-2" style="font-size: 0.6rem;">' . htmlspecialchars($d['method'] ?? '-') . '</span>';
                                                            echo '</div>';
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><small><?php echo htmlspecialchars($row['notes']); ?></small></td>
                                        <td class="text-end pe-4">
                                            <?php if($can_edit): ?>
                                            <a href="index.php?page=man_power_assignment_delete&id=<?php echo $row['id']; ?>&project_id=<?php echo $project['project_id']; ?>" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Remove this assignment?');">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="fas fa-user-slash fa-2x mb-3 d-block opacity-25"></i>
                                            No personnel assigned yet.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
var allPersonnel = [];

function initPersonnelData() {
    var select = document.getElementById('personnelSelect');
    var options = select.options;
    for (var i = 0; i < options.length; i++) {
        var opt = options[i];
        if (opt.value === "") continue; // Skip placeholder
        allPersonnel.push({
            id: opt.value,
            text: opt.text,
            skills: JSON.parse(opt.getAttribute('data-skills') || '[]')
        });
    }
}

function filterPersonnel() {
    var roleSelect = document.getElementById('roleSelect');
    var personnelSelect = $('#personnelSelect');
    var selectedRole = roleSelect.value;
    var currentVal = personnelSelect.val();
    
    // Clear and add placeholder
    personnelSelect.empty();
    personnelSelect.append(new Option('-- Choose Person --', '', true, true));
    
    // Filter and add options
    allPersonnel.forEach(function(p) {
        if (selectedRole === "Other" || selectedRole === "" || p.skills.includes(selectedRole)) {
            var newOption = new Option(p.text, p.id, false, false);
            personnelSelect.append(newOption);
        }
    });
    
    // Restore selection if valid, otherwise select placeholder
    if (currentVal) {
        // Check if currentVal exists in the new options
        if (personnelSelect.find("option[value='" + currentVal + "']").length > 0) {
            personnelSelect.val(currentVal);
        } else {
            personnelSelect.val('');
        }
    } else {
        personnelSelect.val('');
    }
    
    // Trigger Select2 update
    personnelSelect.trigger('change');
    
    // Toggle Doctor Fields
    var doctorFields = document.getElementById('doctorFields');
    if (selectedRole === 'Dokter') {
        doctorFields.style.display = 'block';
    } else {
        doctorFields.style.display = 'none';
        
        // Check if any activity was checked before we uncheck them
        // If yes, we should restore the dates (as per the "uncheck all" logic reversal)
        var $checks = $(doctorFields).find('input[type="checkbox"]');
        if ($checks.filter(':checked').length > 0) {
             $('input[name="dates[]"]').prop('checked', true);
        }
        
        // Uncheck checkboxes when hidden
        $checks.prop('checked', false);
    }
}

$(document).ready(function() {
    initPersonnelData();
    
    // Initialize Select2
    $('#personnelSelect').select2({
        theme: 'bootstrap-5',
        width: '100%',
        dropdownParent: $('body')
    });
    
    // Bind change event to Role
    $('#roleSelect').on('change', function() {
        filterPersonnel();
    });
    
    // Auto-handle MCU dates based on Doctor Activities
    $(document).on('change', 'input[name="activity_types[]"]', function() {
        var anyChecked = $('input[name="activity_types[]"]:checked').length > 0;
        var dateCheckboxes = $('input[name="dates[]"]');
        
        if (anyChecked) {
            // Uncheck all MCU dates if any doctor activity is selected
            dateCheckboxes.prop('checked', false);
        } else {
            // Re-check all MCU dates if no doctor activity is selected
            dateCheckboxes.prop('checked', true);
        }
    });
    
    // Initial filter run
    // We need to run this to filter options based on initial role (if any)
    // But we must be careful not to lose the initial selection if it's valid
    filterPersonnel();
});
</script>

<?php include '../views/layouts/footer.php'; ?>

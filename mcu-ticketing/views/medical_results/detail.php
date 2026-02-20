<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<div class="container-fluid px-4">
    <!-- Header & Navigation -->
    <?php 
    // Access Control: Lock editing if COMPLETED, unless Superadmin
    $is_locked = (isset($result['status']) && $result['status'] == 'COMPLETED' && $_SESSION['role'] != 'superadmin');
    ?>
    <div class="d-flex justify-content-between align-items-center page-header-container">
        <div>
            <h1 class="page-header-title">Result Entry</h1>
            <p class="page-header-subtitle">Manage medical results and assignments for <?php echo htmlspecialchars($project['nama_project']); ?></p>
        </div>
        <div>
            <?php if (in_array($_SESSION['role'], ['dw_tim_hasil', 'surat_hasil', 'superadmin'])): ?>
            <a href="index.php?page=rab_medical_create&project_id=<?php echo $project['project_id']; ?>" 
               class="btn btn-success rounded-pill px-4 shadow-sm me-2">
                <i class="fas fa-plus me-2"></i>Create RAB
            </a>
            <?php endif; ?>
            <a href="index.php?page=medical_results_index" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm">
                <i class="fas fa-arrow-left me-2"></i>Back to List
            </a>
        </div>
    </div>

    <?php if (isset($result['status']) && $result['status'] == 'PENDING_PARTICIPANTS'): ?>
        <div class="alert alert-warning border-warning shadow-sm mb-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-circle fa-2x me-3 text-warning"></i>
                <div>
                    <h5 class="alert-heading fw-bold mb-1">Attention Needed: Pending Participants</h5>
                    <p class="mb-0">
                        There are <strong><?php echo $result['pending_participants_count'] ?? 0; ?></strong> pending participants (susulan).
                        <br>
                        Notes: <em><?php echo htmlspecialchars($result['pending_participants_notes'] ?? '-'); ?></em>
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 border-start border-success border-4" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($_GET['msg']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['err'])): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 border-start border-danger border-4" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($_GET['err']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Project Overview Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="row g-0">
                <div class="col-md-8 p-4">
                    <h5 class="fw-bold text-dark mb-3"><i class="fas fa-project-diagram me-2 text-primary"></i><?php echo htmlspecialchars($project['nama_project']); ?></h5>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Project ID</small>
                            <div class="fw-medium text-dark"><?php echo htmlspecialchars($project['project_id']); ?></div>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Company</small>
                            <div class="fw-medium text-dark"><?php echo htmlspecialchars($project['company_name']); ?></div>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">MCU Dates</small>
                            <div>
                                <span class="badge bg-light text-dark border fw-normal">
                                    <?php echo DateHelper::formatSmartDateIndonesian($project['tanggal_mcu']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Current Status</small>
                            <div><span class="badge bg-info bg-opacity-10 text-info border border-info px-3 py-1"><?php echo $result['status']; ?></span></div>
                        </div>
                    </div>
                    
                    <div class="mt-4 pt-3 border-top">
                        <button class="btn btn-sm btn-link text-decoration-none px-0" type="button" data-bs-toggle="collapse" data-bs-target="#examPackagesCollapse">
                            <i class="fas fa-vial me-1"></i>View Exam Packages
                        </button>
                        <div class="collapse mt-2" id="examPackagesCollapse">
                            <div class="card card-body bg-light border-0 small">
                                <?php echo PackageHelper::renderMatrix($project['jenis_pemeriksaan'], $project['company_name']); ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 bg-light bg-opacity-50 p-4 border-start">
                    <h6 class="fw-bold text-muted mb-3"><i class="fas fa-sliders-h me-2"></i>Configuration</h6>
                    
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">TAT Setting</small>
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <div class="fw-bold text-dark"><?php echo $tat_info['type']; ?></div>
                                <small class="text-muted"><?php echo $tat_info['days']; ?> Days</small>
                            </div>
                            <div class="vr mx-2"></div>
                            <div>
                                <div class="fw-bold text-dark"><?php echo ucfirst(str_replace('_', ' ', $tat_info['mode'])); ?></div>
                                <small class="text-muted">Mode</small>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Links Form -->
                    <form action="index.php?page=medical_results_save_project" method="POST" class="mt-4">
                        <input type="hidden" name="project_id" value="<?php echo $project['project_id']; ?>">
                        <div class="mb-2">
                            <label class="form-label small fw-bold text-muted">Summary Links</label>
                            <input type="url" class="form-control form-control-sm mb-2" name="link_summary_excel" value="<?php echo htmlspecialchars($result['link_summary_excel'] ?? ''); ?>" placeholder="Excel Link (https://...)">
                            <input type="url" class="form-control form-control-sm" name="link_summary_dashboard" value="<?php echo htmlspecialchars($result['link_summary_dashboard'] ?? ''); ?>" placeholder="Dashboard Link (https://...)">
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3">Save Links</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content: Date List -->
    <div class="accordion d-grid gap-3" id="mcuAccordion">
    <?php foreach ($dates_data as $index => $item): ?>
        <?php $accId = 'mcu_'.$index; ?>
        <div class="accordion-item border-0 shadow-sm rounded-4 overflow-hidden">
            <h2 class="accordion-header" id="heading_<?php echo $accId; ?>">
                <button class="accordion-button collapsed bg-white py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_<?php echo $accId; ?>" aria-expanded="false" aria-controls="collapse_<?php echo $accId; ?>">
                    <div class="w-100 pe-3">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-circle me-3">
                                    <i class="fas fa-calendar-day"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark"><?php echo date('d M Y', strtotime($item['date_mcu'])); ?></h6>
                                    <small class="text-muted">Deadline: <?php echo date('d M Y', strtotime($item['deadline_date'])); ?></small>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-center gap-2">
                                <!-- Assigned User Badge -->
                                <?php 
                                    $assigned_name = "Unassigned";
                                    if(isset($kohas_users) && isset($item['assigned_to_user_id'])) {
                                        foreach($kohas_users as $u) {
                                            if($u['user_id'] == $item['assigned_to_user_id']) {
                                                $assigned_name = $u['full_name'];
                                                break;
                                            }
                                        }
                                    }
                                ?>
                                <span class="badge rounded-pill <?php echo ($assigned_name == 'Unassigned') ? 'bg-warning text-dark bg-opacity-25' : 'bg-info text-info bg-opacity-10 border border-info'; ?> px-3 py-2">
                                    <i class="fas fa-user me-1"></i> <?php echo htmlspecialchars($assigned_name); ?>
                                </span>

                                <!-- Status Badge -->
                                <span class="badge rounded-pill <?php echo ($item['status']=='RELEASED')?'bg-success':'bg-secondary'; ?> px-3 py-2">
                                    <?php echo $item['status']; ?>
                                </span>
                                
                                <!-- TAT Badge -->
                                <span class="badge rounded-pill <?php echo ($item['tat_overdue'])?'bg-danger':'bg-light text-muted border'; ?> px-3 py-2">
                                    <?php echo ($item['tat_overdue'])?'OVERDUE':'ON TIME'; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </button>
            </h2>
            <div id="collapse_<?php echo $accId; ?>" class="accordion-collapse collapse" aria-labelledby="heading_<?php echo $accId; ?>" data-bs-parent="#mcuAccordion">
                <div class="accordion-body bg-light bg-opacity-10 p-4">
                    
                    <!-- Assignment Section (Only for Admins) -->
                    <?php if(in_array($_SESSION['role'], ['admin_ops', 'manager_ops', 'superadmin'])): ?>
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body bg-white rounded-3">
                            <form action="index.php?page=medical_results_assign_user" method="POST" class="d-flex align-items-center gap-3 flex-wrap">
                                <input type="hidden" name="project_id" value="<?php echo $project['project_id']; ?>">
                                <input type="hidden" name="medical_result_id" value="<?php echo $result['id']; ?>">
                                <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($item['id'] ?? ''); ?>">
                                <input type="hidden" name="date_mcu" value="<?php echo htmlspecialchars($item['date_mcu']); ?>">
                                
                                <div class="flex-grow-1">
                                    <label class="form-label small text-muted fw-bold mb-1">Assign Koordinator Hasil</label>
                                    <select class="form-select form-select-sm" name="assigned_to_user_id">
                                        <option value="">-- Unassigned --</option>
                                        <?php foreach ($kohas_users as $user): ?>
                                            <option value="<?php echo $user['user_id']; ?>" <?php echo (isset($item['assigned_to_user_id']) && $item['assigned_to_user_id'] == $user['user_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($user['full_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="align-self-end">
                                    <button type="submit" class="btn btn-sm btn-primary px-4 rounded-pill">Assign</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Main Form -->
                    <form action="index.php?page=medical_results_save_item" method="POST" onsubmit="return confirmSaveItem(event);">
                        <input type="hidden" name="project_id" value="<?php echo $project['project_id']; ?>">
                        <input type="hidden" name="medical_result_id" value="<?php echo $result['id']; ?>">
                        <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($item['id'] ?? ''); ?>">
                        <input type="hidden" name="date_mcu" value="<?php echo htmlspecialchars($item['date_mcu']); ?>">
                        <input type="hidden" name="deadline_date" value="<?php echo htmlspecialchars($item['deadline_date']); ?>">
                        
                        <div class="row g-4">
                            <!-- Left Column: Stats & Dates -->
                            <div class="col-lg-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-white py-3 border-0">
                                        <h6 class="fw-bold mb-0 text-primary"><i class="fas fa-chart-pie me-2"></i>Result Statistics</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="form-floating">
                                                    <input type="number" class="form-control fw-bold" id="actual_pax_checked_<?php echo $accId; ?>" name="actual_pax_checked" value="<?php echo htmlspecialchars($item['actual_pax_checked']); ?>" required placeholder="Checked" <?php if($is_locked) echo 'readonly'; ?>>
                                                    <label>Actual Checked</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-floating">
                                                    <input type="number" class="form-control fw-bold" id="actual_pax_released_<?php echo $accId; ?>" name="actual_pax_released" value="<?php echo htmlspecialchars($item['actual_pax_released']); ?>" required placeholder="Released" <?php if($is_locked) echo 'readonly'; ?>>
                                                    <label>Actual Released</label>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="form-floating">
                                                    <input type="date" class="form-control" id="release_date_<?php echo $accId; ?>" name="release_date" value="<?php echo htmlspecialchars($item['release_date']); ?>" required placeholder="Release Date" <?php if($is_locked) echo 'readonly'; ?>>
                                                    <label>Release Date</label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Difference Section (Auto-show via JS) -->
                                        <input type="hidden" id="has_difference_<?php echo $accId; ?>" name="has_difference" value="<?php echo ($item['has_difference'])?'1':'0'; ?>">
                                        <div id="diff_fields_<?php echo $accId; ?>" class="mt-4 p-3 bg-danger bg-opacity-10 rounded-3 border border-danger border-opacity-25" style="<?php echo ($item['has_difference'])?'':'display:none;'; ?>">
                                            <h6 class="text-danger fw-bold small mb-2"><i class="fas fa-exclamation-triangle me-2"></i>Discrepancy Detected</h6>
                                            <div id="diff_container_<?php echo $accId; ?>"></div>
                                            <!-- Legacy Hidden Inputs -->
                                            <input type="hidden" id="raw_diff_names_<?php echo $accId; ?>" value="<?php echo htmlspecialchars($item['difference_names']); ?>">
                                            <input type="hidden" id="raw_diff_reason_<?php echo $accId; ?>" value="<?php echo htmlspecialchars($item['difference_reason']); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Documents & Issues -->
                            <div class="col-lg-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-white py-3 border-0">
                                        <h6 class="fw-bold mb-0 text-primary"><i class="fas fa-file-alt me-2"></i>Documents & Notes</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold text-muted">PDF Result Link</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-link text-muted"></i></span>
                                                <input type="text" class="form-control border-start-0 ps-0" id="link_pdf_<?php echo $accId; ?>" name="link_pdf" value="<?php echo htmlspecialchars($item['link_pdf']); ?>" placeholder="Paste URL here..." required <?php if($is_locked) echo 'readonly'; ?>>
                                                <?php if(!empty($item['link_pdf'])): ?>
                                                    <a href="<?php echo htmlspecialchars($item['link_pdf']); ?>" target="_blank" class="btn btn-outline-secondary"><i class="fas fa-external-link-alt"></i></a>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label small fw-bold text-muted">General Notes</label>
                                            <textarea class="form-control" id="notes_<?php echo $accId; ?>" name="notes" rows="3" placeholder="Optional notes..." <?php if($is_locked) echo 'readonly'; ?>><?php echo htmlspecialchars($item['notes']); ?></textarea>
                                        </div>

                                        <!-- TAT Issue Box -->
                                        <div id="tat_issue_box_<?php echo $accId; ?>" class="alert alert-danger border-0 shadow-sm mb-0" style="<?php echo ($item['tat_overdue'])?'':'display:none;'; ?>">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="fas fa-clock text-danger me-2"></i>
                                                <h6 class="mb-0 fw-bold small text-danger">Overdue Justification</h6>
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-md-4">
                                                    <select class="form-select form-select-sm" id="tat_issue_<?php echo $accId; ?>" name="tat_issue" <?php if($is_locked) echo 'disabled'; ?>>
                                                        <option value="">-- Reason --</option>
                                                        <option value="Internal" <?php echo ($item['tat_issue']=='Internal')?'selected':''; ?>>Internal</option>
                                                        <option value="External" <?php echo ($item['tat_issue']=='External')?'selected':''; ?>>External</option>
                                                        <option value="System" <?php echo ($item['tat_issue']=='System')?'selected':''; ?>>System</option>
                                                        <option value="Other" <?php echo ($item['tat_issue']=='Other')?'selected':''; ?>>Other</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-8">
                                                    <textarea class="form-control form-control-sm" id="tat_issue_notes_<?php echo $accId; ?>" name="tat_issue_notes" rows="1" placeholder="Explanation..." <?php if($is_locked) echo 'readonly'; ?>><?php echo htmlspecialchars($item['tat_issue_notes']); ?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end align-items-center mt-4 pt-3 border-top gap-3">
                            <span class="text-muted small fst-italic">Last updated: <?php echo (!empty($item['updated_at'])) ? date('d M Y H:i', strtotime($item['updated_at'])) : '-'; ?></span>
                            <div>
                                <button type="button" class="btn btn-primary rounded-pill px-3 border me-2 btn-wa-template"
                                        data-date-mcu="<?php echo htmlspecialchars($item['date_mcu']); ?>"
                                        data-checked="<?php echo htmlspecialchars($item['actual_pax_checked']); ?>"
                                        data-released="<?php echo htmlspecialchars($item['actual_pax_released']); ?>"
                                        data-link="<?php echo htmlspecialchars($item['link_pdf']); ?>"
                                        data-diff-names="<?php echo htmlspecialchars($item['difference_names']); ?>"
                                        data-diff-reason="<?php echo htmlspecialchars($item['difference_reason']); ?>"
                                        onclick="showWaTemplateModal(
                                            this.getAttribute('data-date-mcu'),
                                            this.getAttribute('data-checked'),
                                            this.getAttribute('data-released'),
                                            this.getAttribute('data-link'),
                                            this.getAttribute('data-diff-names'),
                                            this.getAttribute('data-diff-reason')
                                        )">
                                    <i class="fas fa-comment-dots me-2"></i>Template Lark
                                </button>
                                <?php if(!$is_locked): ?>
                                    <button type="reset" class="btn btn-light rounded-pill px-3 border me-2">Reset</button>
                                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm"><i class="fas fa-save me-2"></i>Save Changes</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>

                    <!-- Follow-up Section -->
                    <div class="mt-5">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="fw-bold text-dark mb-0"><i class="fas fa-history me-2 text-primary"></i>Follow-up & Susulan</h6>
                            
                            <?php
                                $checked   = isset($item['actual_pax_checked']) ? (int)$item['actual_pax_checked'] : 0;
                                $released  = isset($item['actual_pax_released']) ? (int)$item['actual_pax_released'] : 0;
                                $remaining = $checked - $released;
                                $sum_followups = 0;
                                if (!empty($item['followups']) && is_array($item['followups'])) {
                                    foreach ($item['followups'] as $fp) {
                                        $sum_followups += isset($fp['pax_susulan']) ? (int)$fp['pax_susulan'] : 0;
                                    }
                                }
                                $remaining_after = $remaining - $sum_followups;
                            ?>
                            
                            <?php if ($item['id'] && $remaining_after > 0 && !$is_locked): ?>
                                <button class="btn btn-sm btn-outline-primary rounded-pill" onclick="addFollowup(<?php echo htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8'); ?>)">
                                    <i class="fas fa-plus me-1"></i> Add Follow-up (<?php echo $remaining_after; ?> Remaining)
                                </button>
                            <?php elseif (!$item['id']): ?>
                                <span class="badge bg-light text-muted border">Save main result first</span>
                            <?php else: ?>
                                <span class="badge bg-success bg-opacity-10 text-success"><i class="fas fa-check-circle me-1"></i>All pax accounted for</span>
                            <?php endif; ?>
                        </div>

                        <div class="card border-0 shadow-sm overflow-hidden">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light text-muted small text-uppercase">
                                        <tr>
                                            <th class="ps-4">Pax</th>
                                            <th>Names</th>
                                            <th>Release Date</th>
                                            <th>Notes</th>
                                            <th>Status</th>
                                            <th class="text-end pe-4">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0">
                                        <?php foreach ($item['followups'] as $fp): ?>
                                            <tr>
                                                <td class="ps-4 fw-bold text-dark"><?php echo $fp['pax_susulan']; ?></td>
                                                <td>
                                                    <?php 
                                                    if (!empty($fp['pax_names'])) {
                                                        $names = json_decode($fp['pax_names'], true);
                                                        if (is_array($names)) {
                                                            echo '<div class="text-truncate" style="max-width: 200px;" title="'.implode(', ', array_map('htmlspecialchars', $names)).'">' . implode(', ', array_map('htmlspecialchars', $names)) . '</div>';
                                                        } else {
                                                            echo htmlspecialchars($fp['pax_names']);
                                                        }
                                                    } else {
                                                        echo '<span class="text-muted">-</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td><?php echo $fp['release_date_susulan'] ? date('d M Y', strtotime($fp['release_date_susulan'])) : '<span class="text-muted">-</span>'; ?></td>
                                                <td><small class="text-muted"><?php echo htmlspecialchars($fp['reason']); ?></small></td>
                                                <td><span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill">Pending</span></td>
                                                <td class="text-end pe-4">
                                                    <?php if(!$is_locked): ?>
                                                    <button class="btn btn-sm btn-link text-primary p-0" onclick="editFollowup(<?php echo htmlspecialchars(json_encode($fp), ENT_QUOTES, 'UTF-8'); ?>, <?php echo htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8'); ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($item['followups'])): ?>
                                            <tr><td colspan="6" class="text-center text-muted py-5 small">No follow-up data available</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    <?php endforeach; ?>
    </div>

    <!-- Complete Project Button -->
    <div class="card border-0 shadow-sm rounded-4 mt-5 bg-white">
        <div class="card-body text-center p-5">
            <?php if ($result['status'] != 'COMPLETED'): ?>
                <h5 class="fw-bold mb-3">Is everything finished?</h5>
                <p class="text-muted mb-4">Once you mark the project as completed, no further changes can be made.</p>
                
                <button type="button" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm" onclick="openCompleteModal()">
                    <i class="fas fa-check-circle me-2"></i> Update Project Status
                </button>
            <?php else: ?>
                <div class="d-inline-block p-3 rounded-circle bg-success bg-opacity-10 text-success mb-3">
                    <i class="fas fa-check fa-2x"></i>
                </div>
                <h4 class="fw-bold text-success">Project Completed</h4>
                <?php if($_SESSION['role'] == 'superadmin'): ?>
                    <p class="text-primary mb-0 fw-bold"><i class="fas fa-unlock me-2"></i>Superadmin Access: Editing Enabled</p>
                <?php else: ?>
                    <p class="text-muted mb-0">All results have been processed and finalized.</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Edit Item Modal (Kept for compatibility, though mostly inline forms are used) -->
<div class="modal fade" id="editItemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="index.php?page=medical_results_save_item" method="POST">
            <input type="hidden" name="project_id" value="<?php echo $project['project_id']; ?>">
            <input type="hidden" name="medical_result_id" value="<?php echo $result['id']; ?>">
            <input type="hidden" name="item_id" id="item_id">
            <input type="hidden" name="date_mcu" id="item_date_mcu">
            <input type="hidden" name="deadline_date" id="item_deadline_date">

            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Update Result</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="number" class="form-control" name="actual_pax_checked" id="actual_pax_checked" required placeholder="Checked">
                                <label>Actual Pax Checked <span class="text-danger">*</span></label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="number" class="form-control" name="actual_pax_released" id="actual_pax_released" required placeholder="Released">
                                <label>Actual Pax Released <span class="text-danger">*</span></label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3 form-floating">
                        <input type="date" class="form-control" name="release_date" id="release_date" required placeholder="Date">
                        <label>Release Date <span class="text-danger">*</span></label>
                        <div class="form-text">Deadline: <span id="display_deadline" class="fw-bold text-danger"></span></div>
                    </div>
                    
                    <div class="mt-3 form-floating">
                        <input type="text" class="form-control" name="link_pdf" id="link_pdf" placeholder="URL">
                        <label>Link PDF</label>
                    </div>
                    
                    <div class="mt-3 form-floating">
                        <textarea class="form-control" name="notes" id="notes" style="height: 100px" placeholder="Notes"></textarea>
                        <label>Notes</label>
                    </div>

                    <input type="hidden" id="has_difference" name="has_difference" value="0">

                    <div id="diff_fields" class="mt-3 p-3 bg-danger bg-opacity-10 rounded border border-danger border-opacity-25" style="display:none;">
                        <h6 class="text-danger fw-bold small mb-2">Discrepancy Details</h6>
                        <div class="mb-2">
                            <label class="form-label small">Difference Names</label>
                            <textarea class="form-control form-control-sm" name="difference_names" id="difference_names"></textarea>
                        </div>
                        <div>
                            <label class="form-label small">Difference Reason</label>
                            <textarea class="form-control form-control-sm" name="difference_reason" id="difference_reason"></textarea>
                        </div>
                    </div>
                    
                    <!-- TAT Issue fields -->
                    <div class="mt-3" id="tatIssueContainer">
                         <div class="card border-danger shadow-sm">
                            <div class="card-header bg-danger text-white py-2 small fw-bold">
                                 <i class="fas fa-exclamation-triangle me-2"></i>TAT Issue Details (Required if Overdue)
                            </div>
                            <div class="card-body bg-light">
                                <div class="mb-2">
                                    <label class="form-label small">Issue Type</label>
                                    <select class="form-select form-select-sm" name="tat_issue" id="tat_issue">
                                        <option value="">-- Select --</option>
                                        <option value="Internal">Internal</option>
                                        <option value="External">External</option>
                                        <option value="System">System</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label small">Issue Notes</label>
                                    <textarea class="form-control form-control-sm" name="tat_issue_notes" id="tat_issue_notes"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit Followup Modal -->
<div class="modal fade" id="editFollowupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="index.php?page=medical_results_save_followup" method="POST">
            <input type="hidden" name="project_id" value="<?php echo $project['project_id']; ?>">
            <input type="hidden" name="item_id" id="followup_item_id">
            <input type="hidden" name="followup_id" id="followup_id">

            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Follow-up Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Read-only Difference Info from Main Item -->
                    <div class="alert alert-secondary d-flex align-items-start gap-3 mb-4">
                        <i class="fas fa-info-circle mt-1"></i>
                        <div class="w-100">
                            <h6 class="fw-bold small mb-2">Pending from Main Result</h6>
                            <div class="row g-2">
                                <div class="col-6">
                                    <small class="text-uppercase text-muted d-block" style="font-size: 0.7rem;">Names</small>
                                    <div class="fw-medium small text-truncate" id="followup_diff_names">-</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-uppercase text-muted d-block" style="font-size: 0.7rem;">Reason</small>
                                    <div class="fw-medium small text-truncate" id="followup_diff_reason">-</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Select Participants <span class="text-danger">*</span></label>
                        <div id="followup_names_container" class="border rounded p-3 bg-light" style="max-height: 200px; overflow-y: auto;">
                            <!-- Checkboxes will be rendered here -->
                            <div class="text-muted small fst-italic">No participants available from difference list.</div>
                        </div>
                        <input type="hidden" name="pax_susulan" id="pax_susulan">
                    </div>
                    
                    <div class="mb-3 form-floating">
                        <input type="date" class="form-control" name="release_date_susulan" id="release_date_susulan">
                        <label>Release Date Susulan</label>
                    </div>
                    
                    <div class="mb-3 form-floating">
                        <textarea class="form-control" name="reason" id="reason" required style="height: 100px" placeholder="Reason"></textarea>
                        <label>Reason / Notes <span class="text-danger">*</span></label>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Save Followup</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include '../views/layouts/footer.php'; ?>
<?php include 'wa_template_partial.php'; ?>

<!-- Original Scripts Preserved for Logic Integrity -->
<!-- Complete Project Modal -->
<div class="modal fade" id="completeProjectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="index.php?page=medical_results_complete" method="POST">
                <input type="hidden" name="project_id" value="<?php echo $project['project_id']; ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Project Status Update</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Apakah ada susulan?</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="has_followup" id="followupYes" value="yes" onchange="toggleFollowupFields()">
                                <label class="form-check-label" for="followupYes">Ya, Ada Susulan</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="has_followup" id="followupNo" value="no" onchange="toggleFollowupFields()" checked>
                                <label class="form-check-label" for="followupNo">Tidak, Semua Selesai</label>
                            </div>
                        </div>
                    </div>
                    
                    <div id="followupFields" style="display:none;" class="bg-light p-3 rounded mb-3">
                        <div class="mb-3">
                            <label class="form-label">Total Berapa?</label>
                            <input type="number" name="pending_count" class="form-control" min="1">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes (e.g. susulan walkin)</label>
                            <textarea name="pending_notes" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="alert alert-warning small">
                            <i class="fas fa-exclamation-triangle me-1"></i> Status project akan menjadi <strong>PENDING PARTICIPANTS</strong>. Mohon diperhatikan.
                        </div>
                    </div>
                    
                    <div id="completeMessage" class="alert alert-success">
                        <i class="fas fa-check me-1"></i> Status project akan menjadi <strong>COMPLETED</strong>. Pastikan semua hasil sudah final.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function confirmSaveItem(e) {
        e.preventDefault();
        const form = e.target;
        
        Swal.fire({
            title: 'Save Result?',
            text: "Are you sure you want to save/update this result?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#dc3545',
            confirmButtonText: 'Yes, save it!'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
        return false;
    }

    function confirmComplete(e) {
        e.preventDefault();
        const form = e.target;
        
        Swal.fire({
            title: 'Mark as Completed?',
            text: "This will mark the project as COMPLETED. Ensure all results are final.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#dc3545',
            confirmButtonText: 'Yes, Complete Project'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
        return false;
    }

    // Discrepancy Helper Functions
    window.addDiffRow = function(accId, name = '', reason = '') {
        const container = document.getElementById('diff_rows_' + accId);
        if (!container) return;
        const rowId = 'diff_row_' + accId + '_' + Date.now() + '_' + Math.floor(Math.random() * 1000);
        
        const div = document.createElement('div');
        div.className = 'row g-2 mb-2 align-items-start diff-row';
        div.id = rowId;
        div.innerHTML = `
            <div class="col-12 col-md-4">
                <textarea class="form-control form-control-sm diff-name-input" rows="2" style="resize: vertical;" placeholder="Example:&#10;Reza&#10;Ervan&#10;Ai" oninput="updateDiffInputs('${accId}')">${escapeHtml(name)}</textarea>
            </div>
            <div class="col-12 col-md-7">
                <textarea class="form-control form-control-sm diff-reason-input" rows="2" style="resize: vertical;" placeholder="Reason for these names..." oninput="updateDiffInputs('${accId}')">${escapeHtml(reason)}</textarea>
            </div>
            <div class="col-12 col-md-1 text-end">
                <button type="button" class="btn btn-sm btn-outline-danger border-0 mt-1" onclick="removeDiffRow('${rowId}', '${accId}')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        container.appendChild(div);
        updateDiffInputs(accId);
    };

    window.removeDiffRow = function(rowId, accId) {
        const row = document.getElementById(rowId);
        if(row) {
            row.remove();
            updateDiffInputs(accId);
        }
    };

    window.updateDiffInputs = function(accId) {
        const container = document.getElementById('diff_rows_' + accId);
        const hiddenContainer = document.getElementById('diff_hidden_' + accId);
        
        if (hiddenContainer) hiddenContainer.innerHTML = '';
        
        if(container) {
            container.querySelectorAll('.diff-row').forEach(row => {
                const namesBlock = row.querySelector('.diff-name-input').value.trim();
                const reason = row.querySelector('.diff-reason-input').value.trim();
                
                if(namesBlock) {
                    if (hiddenContainer) {
                        const nameInput = document.createElement('input');
                        nameInput.type = 'hidden';
                        nameInput.name = 'diff_group_names[]';
                        nameInput.value = namesBlock;
                        
                        const reasonInput = document.createElement('input');
                        reasonInput.type = 'hidden';
                        reasonInput.name = 'diff_group_reason[]';
                        reasonInput.value = reason;
                        
                        hiddenContainer.appendChild(nameInput);
                        hiddenContainer.appendChild(reasonInput);
                    }
                }
            });
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        // Safe JSON encode
        const items = <?php echo json_encode($dates_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE); ?>;
        
        // Helper for HTML escaping
        function escapeHtml(text) {
            if (!text) return '';
            return String(text)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        items.forEach(function(item, index) {
            const accId = 'mcu_' + index;
            const checkedInput = document.getElementById('actual_pax_checked_' + accId);
            const releasedInput = document.getElementById('actual_pax_released_' + accId);
            const diffFields = document.getElementById('diff_fields_' + accId);
            const diffContainer = document.getElementById('diff_container_' + accId);
            const hasDiffInput = document.getElementById('has_difference_' + accId);
            const rawDiffNames = document.getElementById('raw_diff_names_' + accId);
            const rawDiffReason = document.getElementById('raw_diff_reason_' + accId);

            // TAT Elements
            const releaseDateInput = document.getElementById('release_date_' + accId);
            const tatIssueBox = document.getElementById('tat_issue_box_' + accId);
            const deadlineDate = new Date(item.deadline_date); // Assuming YYYY-MM-DD format works

            function checkDifference() {
                const checked = parseInt(checkedInput.value) || 0;
                const released = parseInt(releasedInput.value) || 0;
                
                if (checked > released) {
                    diffFields.style.display = 'block';
                    hasDiffInput.value = '1';
                    
                    // Render difference inputs dynamically if empty
                    if (diffContainer.innerHTML.trim() === '') {
                        // Init structure
                        diffContainer.innerHTML = `
                            <div class="mb-2">
                                <label class="form-label small fw-bold">Discrepancy Details</label>
                                <div id="diff_rows_${accId}"></div>
                                <button type="button" class="btn btn-sm btn-light border text-primary mt-1" onclick="addDiffRow('${accId}')">
                                    <i class="fas fa-plus-circle me-1"></i> Add Discrepancy
                                </button>
                            </div>
                            <div id="diff_hidden_${accId}" style="display:none;"></div>
                        `;
                        
                        // Parse legacy data
                        let rawNames = [];
                        let rawReasons = [];
                        const valNames = rawDiffNames.value;
                        const valReason = rawDiffReason.value;
                        
                        try {
                            // Try JSON first
                            if(valNames.trim().startsWith('[') || valNames.trim().startsWith('{')) {
                                const parsed = JSON.parse(valNames);
                                if (Array.isArray(parsed) && parsed.length > 0 && typeof parsed[0] === 'object') {
                                    parsed.forEach(p => {
                                        rawNames.push(p.name);
                                        rawReasons.push(p.reason || '');
                                    });
                                } else if (Array.isArray(parsed)) {
                                    rawNames = parsed;
                                }
                            }
                        } catch(e) {}
                        
                        // Fallback to newline split if empty
                        if(rawNames.length === 0 && valNames) {
                            rawNames = valNames.split(/[\r\n]+/).map(s=>s.trim()).filter(s=>s);
                        }
                        
                        // Parse reasons if not extracted from JSON
                        if(rawReasons.length === 0 && valReason) {
                            rawReasons = valReason.split(/[\r\n]+/).map(s=>s.trim()).filter(s=>s);
                        }
                        
                        // Populate rows
                        if(rawNames.length > 0) {
                            rawNames.forEach((n, i) => {
                                const r = rawReasons[i] || '';
                                addDiffRow(accId, n, r);
                            });
                        } else {
                            addDiffRow(accId); // Default empty row
                        }
                    }
                } else {
                    diffFields.style.display = 'none';
                    hasDiffInput.value = '0';
                    // Optional: Clear container or keep it? Keeping it prevents data loss if accidental toggle
                }
            }

            if (checkedInput && releasedInput) {
                checkedInput.addEventListener('input', checkDifference);
                releasedInput.addEventListener('input', checkDifference);
                // Initial check
                checkDifference();
            }
            
            // TAT Check Logic
            function checkTat() {
                if (!releaseDateInput.value) return;
                
                const releaseDate = new Date(releaseDateInput.value);
                // Reset time components for accurate date comparison
                releaseDate.setHours(0,0,0,0);
                deadlineDate.setHours(0,0,0,0);
                
                if (releaseDate > deadlineDate) {
                    // Overdue
                    tatIssueBox.style.display = 'block';
                } else {
                    tatIssueBox.style.display = 'none';
                }
            }
            
            if (releaseDateInput) {
                releaseDateInput.addEventListener('change', checkTat);
                // Initial check not needed as PHP handles initial display class/style? 
                // PHP handles strict TAT calculation from server side for the badge.
                // This JS is just for showing the justification box interactively.
                // Let's run it to be safe in case user changes date.
                checkTat();
            }
        });
    });

    // Followup Modal Logic
    var followupModal = new bootstrap.Modal(document.getElementById('editFollowupModal'));

    function addFollowup(item) {
        document.getElementById('followup_item_id').value = item.id;
        document.getElementById('followup_id').value = ''; // Empty for new
        document.getElementById('pax_susulan').value = '';
        document.getElementById('release_date_susulan').value = '';
        document.getElementById('reason').value = '';
        
        // Populate Difference Info
        document.getElementById('followup_diff_names').textContent = item.difference_names || '-';
        document.getElementById('followup_diff_reason').textContent = item.difference_reason || '-';
        
        // Populate Names Checkboxes
        const container = document.getElementById('followup_names_container');
        container.innerHTML = '';
        
        if (item.difference_names) {
            // Split names by comma or newline
            let names = [];
            try {
                // Try parsing JSON first
                if (item.difference_names.trim().startsWith('[') || item.difference_names.trim().startsWith('{')) {
                    const parsed = JSON.parse(item.difference_names);
                    if (Array.isArray(parsed) && parsed.length > 0 && typeof parsed[0] === 'object') {
                        // Extract and flatten names from object array
                        parsed.forEach(p => {
                            if(p.name) {
                                const chunk = p.name.split(/[\n,]+/).map(n => n.trim()).filter(n => n);
                                names.push(...chunk);
                            }
                        });
                    } else if (Array.isArray(parsed)) {
                         names = parsed;
                    }
                } else {
                    // Legacy string format
                    names = item.difference_names.split(/[\n,]+/).map(n => n.trim()).filter(n => n);
                }
            } catch(e) {
                // Fallback
                names = item.difference_names.split(/[\n,]+/).map(n => n.trim()).filter(n => n);
            }
            
            // Deduplicate
            names = [...new Set(names)];
            
            if (names.length > 0) {
                names.forEach((name, idx) => {
                    const div = document.createElement('div');
                    div.className = 'form-check';
                    div.innerHTML = `
                        <input class="form-check-input pax-checkbox" type="checkbox" value="${escapeHtml(name)}" id="pax_check_${idx}">
                        <label class="form-check-label" for="pax_check_${idx}">
                            ${escapeHtml(name)}
                        </label>
                    `;
                    container.appendChild(div);
                });
                
                // Add listener to count
                const checkboxes = container.querySelectorAll('.pax-checkbox');
                checkboxes.forEach(cb => {
                    cb.addEventListener('change', updatePaxCount);
                });
            } else {
                container.innerHTML = '<div class="text-muted small fst-italic">No parseable names found in difference list.</div>';
            }
        } else {
             container.innerHTML = '<div class="text-muted small fst-italic">No difference names recorded.</div>';
        }

        followupModal.show();
    }

    function editFollowup(fp, item) {
        document.getElementById('followup_item_id').value = item.id;
        document.getElementById('followup_id').value = fp.id;
        document.getElementById('pax_susulan').value = fp.pax_susulan;
        document.getElementById('release_date_susulan').value = fp.release_date_susulan;
        document.getElementById('reason').value = fp.reason;

        // Populate Difference Info
        document.getElementById('followup_diff_names').textContent = item.difference_names || '-';
        document.getElementById('followup_diff_reason').textContent = item.difference_reason || '-';

        // Populate Names Checkboxes
        const container = document.getElementById('followup_names_container');
        container.innerHTML = '';
        
        // Parse selected names from current followup
        let selectedNames = [];
        try {
            selectedNames = JSON.parse(fp.pax_names);
        } catch(e) {
            selectedNames = [fp.pax_names];
        }
        if (!Array.isArray(selectedNames)) selectedNames = [];

        // All potential names from difference
        let allNames = [];
        try {
            if (item.difference_names) {
                if (item.difference_names.trim().startsWith('[') || item.difference_names.trim().startsWith('{')) {
                    const parsed = JSON.parse(item.difference_names);
                    if (Array.isArray(parsed) && parsed.length > 0 && typeof parsed[0] === 'object') {
                        parsed.forEach(p => {
                            if(p.name) {
                                const chunk = p.name.split(/[\n,]+/).map(n => n.trim()).filter(n => n);
                                allNames.push(...chunk);
                            }
                        });
                    } else if (Array.isArray(parsed)) {
                        allNames = parsed;
                    }
                } else {
                     allNames = item.difference_names.split(/[\n,]+/).map(n => n.trim()).filter(n => n);
                }
            }
        } catch(e) {
             if(item.difference_names) allNames = item.difference_names.split(/[\n,]+/).map(n => n.trim()).filter(n => n);
        }
        
        // Deduplicate
        allNames = [...new Set(allNames)];
        
        // Merge current selected names if they aren't in the difference list anymore (edge case)
        const uniqueNames = [...new Set([...allNames, ...selectedNames])];

        if (uniqueNames.length > 0) {
            uniqueNames.forEach((name, idx) => {
                const isChecked = selectedNames.includes(name);
                const div = document.createElement('div');
                div.className = 'form-check';
                div.innerHTML = `
                    <input class="form-check-input pax-checkbox" type="checkbox" value="${escapeHtml(name)}" id="pax_check_${idx}" ${isChecked ? 'checked' : ''}>
                    <label class="form-check-label" for="pax_check_${idx}">
                        ${escapeHtml(name)}
                    </label>
                `;
                container.appendChild(div);
            });
            
            // Add listener to count
            const checkboxes = container.querySelectorAll('.pax-checkbox');
            checkboxes.forEach(cb => {
                cb.addEventListener('change', updatePaxCount);
            });
        } else {
             container.innerHTML = '<div class="text-muted small fst-italic">No names available.</div>';
        }

        followupModal.show();
    }

    function updatePaxCount() {
        const checkboxes = document.querySelectorAll('.pax-checkbox:checked');
        document.getElementById('pax_susulan').value = checkboxes.length;
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
    function openCompleteModal() {
        var modal = new bootstrap.Modal(document.getElementById('completeProjectModal'));
        modal.show();
    }

    function toggleFollowupFields() {
        const isYes = document.getElementById('followupYes').checked;
        const fields = document.getElementById('followupFields');
        const msg = document.getElementById('completeMessage');
        
        if (isYes) {
            fields.style.display = 'block';
            msg.style.display = 'none';
        } else {
            fields.style.display = 'none';
            msg.style.display = 'block';
        }
    }
</script>

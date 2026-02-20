<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-0" style="color: #204EAB;">Project Activity History</h2>
                <p class="text-muted mb-0">Track changes and updates for this project.</p>
            </div>
            <a href="javascript:history.back()" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back
            </a>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Project Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <strong>Project ID:</strong> <?php echo $project['project_id']; ?>
                    </div>
                    <div class="col-md-4">
                        <strong>Project Name:</strong> <?php echo $project['nama_project']; ?>
                    </div>
                    <div class="col-md-4">
                        <strong>Current Status:</strong> 
                        <?php 
                            $statusLabel = strtoupper(str_replace(['_', '-'], ' ', $project['status_project']));
                            if ($project['status_project'] == 'vendor_assigned') $statusLabel = 'VENDOR CONFIRMED';
                            if ($project['status_project'] == 'no_vendor_needed') $statusLabel = 'NO VENDOR NEEDED';
                        ?>
                        <span class="badge bg-primary"><?php echo $statusLabel; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow border-0">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="mb-0 text-primary fw-bold"><i class="fas fa-history me-2"></i>Activity Log</h5>
            </div>
            <div class="card-body">
                <?php if (empty($history)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-history fa-3x mb-3 opacity-25"></i>
                        <p>No history available for this project.</p>
                    </div>
                <?php else: ?>
                    <div class="timeline">
                        <?php 
                        $project_statuses = ['need_approval_manager', 'need_approval_head', 'approved', 'process_vendor', 'vendor_assigned', 'no_vendor_needed', 'completed', 'rejected', 'cancelled', 're-nego'];
                        foreach ($history as $log): 
                            $status = $log['status_to'];
                            $status_lower = strtolower($status);
                            $notes_lower = strtolower($log['notes'] ?? '');
                            
                            // Default Style
                            $badgeClass = 'secondary';
                            $icon = 'fa-circle';

                            // --- MAPPING LOGIC ---
                            
                            // 1. Create
                            if (strpos($status_lower, 'project created') !== false) {
                                $badgeClass = 'primary';
                                $icon = 'fa-plus-circle';
                            }
                            // 2. Edit (Project Updated)
                            elseif (strpos($status_lower, 'project updated') !== false) {
                                $badgeClass = 'info';
                                $icon = 'fa-edit';
                            }
                            // 3. SPH Upload / Update
                            elseif (strpos($status_lower, 'sph uploaded') !== false || strpos($status_lower, 'sph file') !== false) {
                                $badgeClass = 'danger'; // Red for PDF
                                $icon = 'fa-file-pdf';
                            }
                            // 4. Berita Acara (BA)
                            elseif (strpos($status_lower, 'berita acara uploaded') !== false) {
                                $badgeClass = 'primary';
                                $icon = 'fa-file-signature';
                            }
                            elseif (strpos($status_lower, 'berita acara cancelled') !== false) {
                                $badgeClass = 'danger';
                                $icon = 'fa-file-excel';
                            }
                            // 5. Schedule / Date
                            elseif (strpos($status_lower, 'schedule date updated') !== false) {
                                $badgeClass = 'warning';
                                $icon = 'fa-calendar-alt';
                            }
                            // 6. Consumption
                            elseif (strpos($status_lower, 'consumption approved') !== false) {
                                $badgeClass = 'warning'; // Orange for food
                                $icon = 'fa-utensils';
                            }
                            // 7. Vendor / Logistics
                            elseif (strpos($status_lower, 'vendor package assigned') !== false) {
                                $badgeClass = 'info';
                                $icon = 'fa-box-open';
                            }
                            elseif (strpos($status_lower, 'vendor fulfillment updated') !== false) {
                                $badgeClass = 'dark';
                                $icon = 'fa-truck';
                            }
                            elseif (strpos($status_lower, 'no vendor needed') !== false) {
                                $badgeClass = 'secondary';
                                $icon = 'fa-ban';
                            }
                            // 8. Korlap
                            elseif (strpos($status_lower, 'korlap assigned') !== false) {
                                $badgeClass = 'success';
                                $icon = 'fa-hard-hat';
                            }
                            // 9. Status Changes - Approvals
                            elseif (strpos($status_lower, 'approved') !== false) {
                                // Check if it's completion rejected (which logs as approved status but with notes)
                                if (strpos($notes_lower, 'completion rejected') !== false) {
                                    $badgeClass = 'danger';
                                    $icon = 'fa-undo';
                                } else {
                                    $badgeClass = 'success';
                                    $icon = 'fa-check-circle';
                                }
                            }
                            elseif (strpos($status_lower, 'need approval manager') !== false || strpos($status_lower, 'need_approval_manager') !== false) {
                                $badgeClass = 'warning';
                                $icon = 'fa-user-tie';
                            }
                            elseif (strpos($status_lower, 'need approval head') !== false || strpos($status_lower, 'need_approval_head') !== false) {
                                $badgeClass = 'info';
                                $icon = 'fa-user-shield';
                            }
                            // 10. Status Changes - Completion/Rejection
                            elseif (strpos($status_lower, 'completion approved') !== false || $status_lower == 'completed') {
                                $badgeClass = 'success';
                                $icon = 'fa-flag-checkered';
                            }
                            elseif (strpos($status_lower, 'rejected') !== false) {
                                $badgeClass = 'danger';
                                $icon = 'fa-times-circle';
                            }
                            elseif (strpos($status_lower, 'cancelled') !== false) {
                                $badgeClass = 'danger';
                                $icon = 'fa-ban';
                            }
                            elseif (strpos($status_lower, 're-nego') !== false) {
                                $badgeClass = 'warning';
                                $icon = 'fa-sync-alt';
                            }
                            // 11. General Status Change Fallback
                            elseif (strpos($status_lower, 'status changed') !== false) {
                                $badgeClass = 'secondary';
                                $icon = 'fa-exchange-alt';
                            }

                            
                            $formattedStatus = strtoupper(str_replace(['_', '-'], ' ', $status));
                            if ($status == 'vendor_assigned') $formattedStatus = 'VENDOR CONFIRMED';
                            if ($status == 'no_vendor_needed') $formattedStatus = 'NO VENDOR NEEDED';
                            $formattedDate = date('d M Y H:i', strtotime($log['changed_at']));
                        ?>
                            <div class="timeline-item">
                                <div class="timeline-marker bg-white border-<?php echo $badgeClass; ?> text-<?php echo $badgeClass; ?>">
                                    <i class="fas <?php echo $icon; ?>"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <span class="timeline-title fw-bold text-dark"><?php echo htmlspecialchars($log['changed_by_name'] ?? 'System'); ?></span>
                                        <span class="timeline-time text-muted small"><i class="far fa-clock me-1"></i><?php echo $formattedDate; ?></span>
                                    </div>
                                    <div class="timeline-body">
                                        <p class="mb-1 fw-bold text-<?php echo $badgeClass; ?>"><?php echo $formattedStatus; ?></p>
                                        <p class="mb-0 text-muted small"><?php echo nl2br(htmlspecialchars($log['notes'] ?? '-')); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../views/layouts/footer.php'; ?>

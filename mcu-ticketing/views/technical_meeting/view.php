<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<div class="container-fluid p-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="text-primary mb-0"><i class="fas fa-file-alt me-2"></i>Technical Meeting Details</h4>
                        <a href="index.php?page=technical_meeting_list" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Back to List</a>
                    </div>
                    <p class="text-muted">Detail data Technical Meeting untuk Project ID: <strong><?php echo $project['project_id']; ?></strong></p>
                    
                    <!-- 1. Info Dasar -->
                    <h5 class="mt-4 mb-3 border-bottom pb-2">1. Info Dasar</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nama Project</label>
                            <p class="form-control-plaintext"><?php echo htmlspecialchars($project['nama_project']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Lokasi MCU</label>
                            <p class="form-control-plaintext"><?php echo htmlspecialchars($project['alamat']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tanggal MCU</label>
                            <div class="form-control-plaintext">
                                <?php 
                                    $dates = json_decode($project['tanggal_mcu'], true);
                                    if (is_array($dates)) {
                                        echo implode(", ", array_map(function($d) { return date('d M Y', strtotime($d)); }, $dates));
                                    } else {
                                        echo date('d M Y', strtotime($project['tanggal_mcu']));
                                    }
                                ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tanggal Technical Meeting</label>
                            <p class="form-control-plaintext"><?php echo isset($tm['tm_date']) ? date('d M Y H:i', strtotime($tm['tm_date'])) : '-'; ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Metode Meeting</label>
                            <p class="form-control-plaintext"><?php echo isset($tm['tm_type']) ? htmlspecialchars($tm['tm_type']) : '-'; ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tanggal Setting Alat</label>
                            <p class="form-control-plaintext"><?php echo (isset($tm['setting_alat_date']) && $tm['setting_alat_date']) ? date('d M Y H:i', strtotime($tm['setting_alat_date'])) : '-'; ?></p>
                        </div>
                    </div>

                    <!-- 2. Catatan Persiapan -->
                    <h5 class="mt-4 mb-3 border-bottom pb-2">2. Catatan Persiapan Project</h5>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Notes Persiapan Project</label>
                        <div class="p-3 bg-light rounded border">
                            <?php echo isset($tm['notes']) ? nl2br(htmlspecialchars($tm['notes'])) : '-'; ?>
                        </div>
                    </div>

                    <!-- 3. Dokumen -->
                    <h5 class="mt-4 mb-3 border-bottom pb-2">3. Dokumen</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Dokumen Technical Meeting</label>
                            <div>
                                <?php if (isset($tm['tm_file_path'])): ?>
                                    <a href="../public/uploads/tm/<?php echo $tm['tm_file_path']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-file-pdf me-1"></i> View Document
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">No document uploaded</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Layout Area MCU</label>
                            <div>
                                <?php if (isset($tm['layout_file_path']) && $tm['layout_file_path']): ?>
                                    <a href="../public/uploads/tm/<?php echo $tm['layout_file_path']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-image me-1"></i> View Layout
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">No layout uploaded</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 border-top pt-3">
                        <small class="text-muted">
                            Created at: <?php echo isset($tm['created_at']) ? date('d M Y H:i', strtotime($tm['created_at'])) : '-'; ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../views/layouts/footer.php'; ?>

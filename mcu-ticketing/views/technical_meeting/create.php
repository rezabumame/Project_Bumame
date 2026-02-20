<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<div class="container-fluid p-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h4 class="mb-3 text-primary"><i class="fas fa-handshake me-2"></i>Technical Meeting Form</h4>
                    <p class="text-muted">Catat hasil persiapan Technical Meeting untuk Project ID: <strong><?php echo $project['project_id']; ?></strong></p>
                    
                    <form action="index.php?page=technical_meeting_store" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="project_id" value="<?php echo $project['project_id']; ?>">
                        
                        <!-- 1. Info Dasar -->
                        <h5 class="mt-4 mb-3 border-bottom pb-2">1. Info Dasar</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Project</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($project['nama_project']); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Lokasi MCU</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($project['alamat']); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tanggal MCU</label>
                                <div class="form-control bg-light">
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
                                <label class="form-label">Tanggal Technical Meeting <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" name="tm_date" required value="<?php echo isset($existing_tm['tm_date']) ? date('Y-m-d\TH:i', strtotime($existing_tm['tm_date'])) : ''; ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Metode Meeting<span class="text-danger">*</span></label>
                                <select class="form-select" name="tm_type" required>
                                    <option value="">Pilih Metode</option>
                                    <option value="Online" <?php echo (isset($existing_tm['tm_type']) && $existing_tm['tm_type'] == 'Online') ? 'selected' : ''; ?>>Online</option>
                                    <option value="Offline" <?php echo (isset($existing_tm['tm_type']) && $existing_tm['tm_type'] == 'Offline') ? 'selected' : ''; ?>>Offline</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Setting Alat</label>
                                <input type="datetime-local" class="form-control" name="setting_alat_date" value="<?php echo (isset($existing_tm['setting_alat_date']) && $existing_tm['setting_alat_date']) ? date('Y-m-d\TH:i', strtotime($existing_tm['setting_alat_date'])) : ''; ?>">
                            </div>
                        </div>

                        <!-- 2. Catatan Persiapan -->
                        <h5 class="mt-4 mb-3 border-bottom pb-2">2. Catatan Persiapan Project <span class="text-danger">*</span></h5>
                        <div class="mb-3">
                            <label class="form-label">Notes Persiapan Project</label>
                            <textarea class="form-control" name="notes" rows="6" required placeholder="Catat SEMUA hasil dan persiapan project di sini..."><?php echo isset($existing_tm['notes']) ? htmlspecialchars($existing_tm['notes']) : ''; ?></textarea>
                            <div class="form-text">Digunakan untuk mencatat seluruh persiapan project hasil Technical Meeting sebagai acuan operasional.</div>
                        </div>

                        <!-- 3. Dokumen -->
                        <h5 class="mt-4 mb-3 border-bottom pb-2">3. Dokumen</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Upload Dokumen Technical Meeting (PDF) <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" name="tm_file" accept=".pdf" <?php echo isset($existing_tm['tm_file_path']) ? '' : 'required'; ?>>
                                <?php if (isset($existing_tm['tm_file_path'])): ?>
                                    <div class="mt-2">
                                        <small class="text-success"><i class="fas fa-check-circle"></i> File uploaded: <a href="../public/uploads/tm/<?php echo $existing_tm['tm_file_path']; ?>" target="_blank">View File</a></small>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Upload Layout Area MCU (PDF / Image / PowerPoint) <span class="text-muted">(Opsional)</span></label>
                                <input type="file" class="form-control" name="layout_file" accept=".pdf, .jpg, .jpeg, .png, .ppt, .pptx">
                                <?php if (isset($existing_tm['layout_file_path']) && $existing_tm['layout_file_path']): ?>
                                    <div class="mt-2">
                                        <small class="text-success"><i class="fas fa-check-circle"></i> File uploaded: <a href="../public/uploads/tm/<?php echo $existing_tm['layout_file_path']; ?>" target="_blank">View File</a></small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mt-5 d-flex justify-content-end">
                            <a href="index.php?page=projects_list" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save Technical Meeting Data</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../views/layouts/footer.php'; ?>


<!-- Project Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header modal-modern-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-notes-medical me-2"></i>Project Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body bg-light" id="modal-content-body" style="min-height: 400px;">
                <div class="d-flex justify-content-center align-items-center h-100">
                    <div class="spinner-border" style="color: #204EAB;" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0 bg-light rounded-bottom-4">
                <!-- Debug Role -->
                <input type="hidden" id="debug_user_role" value="<?php echo isset($_SESSION['role']) ? $_SESSION['role'] : ''; ?>">
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'superadmin'): ?>
                    <button type="button" class="btn btn-danger rounded-pill px-4 me-2" id="btn-cancel-project-modal" style="display: none;">
                        <i class="fas fa-ban me-2"></i>Cancel Project
                    </button>
                <?php endif; ?>
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Upload Berita Acara -->
<div class="modal fade" id="uploadBaModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="index.php?page=upload_berita_acara" method="POST" enctype="multipart/form-data">
                <div class="modal-header modal-modern-header">
                    <h5 class="modal-title fw-bold"><i class="fas fa-file-upload me-2"></i>Upload Berita Acara</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="project_id" id="upload_ba_project_id">
                    <input type="hidden" name="date" id="upload_ba_date">
                    
                    <div class="mb-4">
                        <label class="form-label text-uppercase fw-bold text-muted small">MCU Date</label>
                        <input type="text" class="form-control bg-light" id="upload_ba_date_display" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-uppercase fw-bold text-muted small">Berita Acara File (PDF)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fas fa-file-pdf text-danger"></i></span>
                            <input type="file" class="form-control" name="ba_file" accept=".pdf" required>
                        </div>
                        <div class="form-text mt-2"><i class="fas fa-info-circle me-1"></i> Max size: 5MB. Format: PDF only.</div>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn text-white rounded-pill px-4" style="background-color: #204EAB;">Upload File</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Vendor Memo -->
<div class="modal fade" id="vendorMemoModalAjax" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="index.php?page=generate_vendor_memo" method="POST" target="_blank">
                <div class="modal-header modal-modern-header">
                    <h5 class="modal-title fw-bold"><i class="fas fa-file-invoice-dollar me-2"></i>Generate Vendor Memo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="project_id" id="vendor_memo_project_id">
                    <div class="mb-3">
                        <label class="form-label text-uppercase fw-bold text-muted small">Expense Code</label>
                        <select class="form-select bg-light" name="cost_code_id" id="vendor_memo_cost_code" required>
                            <option value="">-- Select Expense Code --</option>
                        </select>
                        <div class="form-text mt-2"><i class="fas fa-info-circle me-1"></i> Only "Vendor (Internal Memo)" codes are shown.</div>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn text-white rounded-pill px-4" style="background-color: #204EAB;">Generate PDF</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Cancel Berita Acara -->
<div class="modal fade" id="cancelBaModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="index.php?page=cancel_berita_acara" method="POST">
                <div class="modal-header bg-danger text-white" style="border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
                    <h5 class="modal-title fw-bold"><i class="fas fa-times-circle me-2"></i>Cancel Berita Acara</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="project_id" id="cancel_ba_project_id">
                    <input type="hidden" name="date" id="cancel_ba_date">
                    
                    <div class="mb-4">
                        <label class="form-label text-uppercase fw-bold text-muted small">MCU Date</label>
                        <input type="text" class="form-control bg-light" id="cancel_ba_date_display" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-uppercase fw-bold text-muted small">Reason for Cancellation</label>
                        <textarea class="form-control" name="reason" rows="3" required placeholder="Please provide a reason..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4">Confirm Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

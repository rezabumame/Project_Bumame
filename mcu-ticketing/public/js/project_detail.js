// Function removed as we use server-side rendering for matrix
function formatJenisPemeriksaan(text) {
    return text; // Fallback
}

function showUploadModal(projectId, date, formattedDate) {
    document.getElementById('upload_ba_project_id').value = projectId;
    document.getElementById('upload_ba_date').value = date;

    // Format date for display
    if (formattedDate) {
        document.getElementById('upload_ba_date_display').value = formattedDate;
    } else {
        const d = new Date(date);
        const options = { day: 'numeric', month: 'long', year: 'numeric' };
        document.getElementById('upload_ba_date_display').value = d.toLocaleDateString('id-ID', options);
    }

    var myModal = new bootstrap.Modal(document.getElementById('uploadBaModal'));
    myModal.show();
}

function showCancelModal(projectId, date, formattedDate) {
    document.getElementById('cancel_ba_project_id').value = projectId;
    document.getElementById('cancel_ba_date').value = date;

    // Format date for display
    if (formattedDate) {
        document.getElementById('cancel_ba_date_display').value = formattedDate;
    } else {
        const d = new Date(date);
        const options = { day: 'numeric', month: 'long', year: 'numeric' };
        document.getElementById('cancel_ba_date_display').value = d.toLocaleDateString('id-ID', options);
    }

    var myModal = new bootstrap.Modal(document.getElementById('cancelBaModal'));
    myModal.show();
}

function loadProjectDetail(projectId, activeTab = 'details') {
    // Handle null passed from URLSearchParams
    activeTab = activeTab || 'details';

    $('#detailModal').modal('show');
    $('#modal-content-body').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');

    $.ajax({
        url: 'index.php?page=get_project_detail_ajax',
        data: { id: projectId },
        success: function (response) {
            try {
                const p = typeof response === 'string' ? JSON.parse(response) : response;
                if (!p) {
                    $('#modal-content-body').html('<div class="alert alert-danger">Project not found (Empty response)</div>');
                    return;
                }

                if (p.error) {
                    $('#modal-content-body').html('<div class="alert alert-danger">' + p.error + '</div>');
                    return;
                }

                // Superadmin Cancel Project Button Logic
                const btnCancel = $('#detailModal #btn-cancel-project-modal');
                const userRole = $('#debug_user_role').val();

                console.log('Debug - Role:', userRole);
                console.log('Debug - Button Found:', btnCancel.length);

                // Normalize status
                let status = (p.status_project || '').trim().toLowerCase();
                // Map 'no vendor needed' to 'no_vendor_needed' just in case
                if (status === 'no vendor needed') status = 'no_vendor_needed';

                console.log('Cancel Check - Status:', status);

                // Statuses where Cancel button should be HIDDEN
                const kanbanStatuses = [
                    'need_approval_manager',
                    'need_approval_head',
                    'approved',
                    'rejected',
                    're-nego',
                    'cancelled'
                ];

                // Show button only if project is NOT in the restricted statuses
                if (btnCancel.length) {
                    if (!kanbanStatuses.includes(status)) {
                        console.log('Showing Cancel Button');
                        btnCancel.removeClass('d-none').show();
                        btnCancel.off('click').on('click', function () {
                            Swal.fire({
                                title: 'Cancel Project?',
                                text: "Are you sure you want to cancel this project? This action cannot be undone easily.",
                                icon: 'warning',
                                input: 'textarea',
                                inputPlaceholder: 'Enter cancellation reason...',
                                inputAttributes: {
                                    'aria-label': 'Cancellation reason'
                                },
                                showCancelButton: true,
                                confirmButtonColor: '#d33',
                                cancelButtonColor: '#3085d6',
                                confirmButtonText: 'Yes, Cancel Project',
                                preConfirm: (reason) => {
                                    if (!reason) {
                                        Swal.showValidationMessage('Reason is required');
                                    }
                                    return reason;
                                }
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    submitStatusUpdate(p.project_id, 'cancelled', result.value);
                                }
                            });
                        });
                    } else {
                        console.log('Hiding Cancel Button (Status Restricted)');
                        btnCancel.hide();
                    }
                }

                let sphLink = '-';
                if (p.sph_file) {
                    if (p.sph_file.match(/^https?:\/\//)) {
                        sphLink = `<a href="${p.sph_file}" target="_blank" class="btn btn-sm btn-outline-primary" style="color: #204EAB; border-color: #204EAB;"><i class="fas fa-external-link-alt me-1"></i> Link SPH</a>`;
                    } else {
                        sphLink = `<a href="index.php?page=download_sph&project_id=${p.project_id}" target="_blank" class="btn btn-sm btn-outline-primary" style="color: #204EAB; border-color: #204EAB;"><i class="fas fa-eye me-1"></i> View File</a>`;
                    }
                } else {
                    if ((userRole === 'admin_sales' || userRole === 'superadmin') && typeof openSphModal === 'function') {
                        sphLink = `<button type="button" class="btn btn-sm btn-outline-warning shadow-sm" title="Set SPH Link" onclick="openSphModal('${p.project_id}')">
                                        <i class="fas fa-link me-1"></i> Set Link
                                   </button>`;
                    }
                }

                // Alert for Re-nego or Rejected
                let alertHtml = '';
                if (p.status_project === 're-nego' || p.status_project === 'rejected') {
                    let alertTitle = p.status_project === 're-nego' ? 'Re-Negotiation Requested' : 'Project Rejected';
                    alertHtml = `
                        <div class="alert alert-danger mb-3">
                            <h6 class="alert-heading"><i class="fas fa-exclamation-circle me-1"></i> ${alertTitle}</h6>
                            <p class="mb-0">${p.reject_reason || 'No specific notes provided.'}</p>
                        </div>
                    `;
                }

                // Vendor Memo Button Logic
                let vendorMemoButton = '';
                // Filter cost codes for "Vendor (Internal Memo)" category only (case insensitive)
                let vendorCostCodes = p.cost_codes ? p.cost_codes.filter(c => c.category && c.category.toLowerCase().includes('vendor')) : [];

                let costCodeOptions = '<option value="">-- Select Expense Code --</option>';
                if (vendorCostCodes.length > 0) {
                    vendorCostCodes.forEach(c => {
                        costCodeOptions += `<option value="${c.id}">${c.code}</option>`;
                    });
                } else {
                    costCodeOptions = '<option value="" disabled>No expense codes available</option>';
                }

                if (p.vendor_allocations && p.vendor_allocations.length > 0) {
                    vendorMemoButton = `
                        <div class="mt-4">
                            <label class="text-muted small text-uppercase fw-bold mb-2">Expense Code Memo</label>
                            <button type="button" class="btn btn-outline-primary w-100 p-3 d-flex align-items-center justify-content-between shadow-sm" data-bs-toggle="modal" data-bs-target="#vendorMemoModalAjax" style="color: #204EAB; border-color: #204EAB;" onmouseover="this.style.backgroundColor='#204EAB'; this.style.color='white';" onmouseout="this.style.backgroundColor='transparent'; this.style.color='#204EAB';">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-file-invoice-dollar fs-3 me-3"></i>
                                    <div class="text-start">
                                        <div class="fw-medium">Generate Vendor Memo</div>
                                        <div class="small opacity-75">Create PDF for Vendor submission</div>
                                    </div>
                                </div>
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    `;

                    // Update static modal fields
                    $('#vendor_memo_project_id').val(p.project_id);
                    $('#vendor_memo_cost_code').html(costCodeOptions);
                }

                // Details Tab Content
                let detailsHtml = `
                    ${alertHtml}
                    <div class="row g-3">
                        <div class="col-md-8">
                            <div class="card border-0 shadow-sm h-100 rounded-3">
                                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                                    <h6 class="text-uppercase fw-bold mb-0" style="color: #204EAB;"><i class="fas fa-info-circle me-2"></i>Project Information</h6>
                                    <hr class="mt-2 mb-0" style="color: #204EAB; opacity: 0.2;">
                                </div>
                                <div class="card-body pt-3">
                                    <div class="row mb-3">
                                        <div class="col-sm-4 text-muted small text-uppercase fw-bold">Project Name</div>
                                        <div class="col-sm-8 fw-medium text-dark">${p.nama_project}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-4 text-muted small text-uppercase fw-bold">Company / Client</div>
                                        <div class="col-sm-8 fw-medium">${p.company_name}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-4 text-muted small text-uppercase fw-bold">SPH Number</div>
                                        <div class="col-sm-8 fw-medium text-primary">${p.sph_number || '-'}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-4 text-muted small text-uppercase fw-bold">Consumption</div>
                                        <div class="col-sm-8">
                                            <div class="d-flex flex-wrap gap-2">
                                                <span class="badge ${p.lunch === 'Ya' ? 'bg-success' : 'bg-light text-muted border'}">
                                                    <i class="fas fa-utensils me-1"></i> Lunch: ${p.lunch || 'No'} ${p.lunch === 'Ya' && p.lunch_qty ? `(${p.lunch_qty})` : ''}
                                                </span>
                                                <span class="badge ${p.snack === 'Ya' ? 'bg-info' : 'bg-light text-muted border'}">
                                                    <i class="fas fa-cookie-bite me-1"></i> Snack: ${p.snack || 'No'} ${p.snack === 'Ya' && p.snack_qty ? `(${p.snack_qty})` : ''}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-4 text-muted small text-uppercase fw-bold">Date of MCU</div>
                                        <div class="col-sm-8">
                                            <span class="badge bg-light text-dark border"><i class="far fa-calendar-alt me-2"></i>
                                                ${p.tanggal_mcu_formatted || p.tanggal_mcu}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-4 text-muted small text-uppercase fw-bold">Location</div>
                                        <div class="col-sm-8 text-break"><i class="fas fa-map-marker-alt text-danger me-2"></i>${p.alamat}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-4 text-muted small text-uppercase fw-bold">Participants</div>
                                        <div class="col-sm-8"><span class="badge bg-primary rounded-pill px-3">${p.total_peserta} Pax</span></div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-4 text-muted small text-uppercase fw-bold">Sales Person</div>
                                        <div class="col-sm-8">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle bg-light text-primary me-2" style="width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 10px;">
                                                    <i class="fas fa-user-tie"></i>
                                                </div>
                                                ${p.sales_name || '-'}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-4 text-muted small text-uppercase fw-bold">Koordinator Lapangan</div>
                                        <div class="col-sm-8">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle bg-light text-info me-2" style="width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 10px;">
                                                    <i class="fas fa-user-shield"></i>
                                                </div>
                                                ${p.korlap_name || '-'}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-4 text-muted small text-uppercase fw-bold">Koordinator Hasil</div>
                                        <div class="col-sm-8">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle bg-light text-primary me-2" style="width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 10px;">
                                                    <i class="fas fa-poll-h"></i>
                                                </div>
                                                ${p.koordinator_hasil || '-'}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-0">
                                        <div class="col-sm-4 text-muted small text-uppercase fw-bold">Notes</div>
                                        <div class="col-sm-8 text-muted fst-italic bg-light p-2 rounded small border">${p.notes || 'No notes provided.'}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm h-100 rounded-3">
                                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                                    <h6 class="text-uppercase fw-bold mb-0" style="color: #204EAB;"><i class="fas fa-cog me-2"></i>Preferences</h6>
                                    <hr class="mt-2 mb-0" style="color: #204EAB; opacity: 0.2;">
                                </div>
                                <div class="card-body pt-3">
                                    <div class="mb-4">
                                        <label class="text-muted small text-uppercase fw-bold mb-3">Facilities & Consumption</label>
                                        
                                        <!-- Lunch Section -->
                                        <div class="p-3 mb-3 rounded-3 border ${p.lunch === 'Ya' ? 'bg-white border-primary border-opacity-25 shadow-sm' : 'bg-light border-0 opacity-75'}">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle me-3 d-flex align-items-center justify-content-center" 
                                                         style="width: 40px; height: 40px; ${p.lunch === 'Ya' ? 'background-color: #e7f1ff; color: #0d6efd;' : 'background-color: #e9ecef; color: #6c757d;'}">
                                                        <i class="fas fa-utensils"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 fw-bold ${p.lunch === 'Ya' ? 'text-dark' : 'text-muted'}">Lunch</h6>
                                                        <small class="${p.lunch === 'Ya' ? 'text-success' : 'text-muted'}">
                                                            ${p.lunch === 'Ya' ? '<i class="fas fa-check-circle me-1"></i>Requested' : 'Not Requested'}
                                                        </small>
                                                    </div>
                                                </div>
                                                ${p.lunch === 'Ya' && p.lunch_qty ? `<span class="badge bg-primary rounded-pill">${p.lunch_qty} Pax</span>` : ''}
                                            </div>
                                            
                                            ${p.lunch === 'Ya' ? `
                                                ${p.lunch_notes ? `<div class="small text-muted fst-italic mb-2 ps-2 border-start border-3 border-primary bg-light p-1 rounded">${p.lunch_notes}</div>` : ''}
                                                
                                                ${(() => {
                            try {
                                const lunchItems = typeof p.lunch_items === 'string' ? JSON.parse(p.lunch_items) : p.lunch_items;
                                if (Array.isArray(lunchItems) && lunchItems.length > 0) {
                                    return `
                                                                <div class="mt-2 pt-2 border-top border-light">
                                                                    <div class="d-flex flex-wrap gap-2">
                                                                        ${lunchItems.map(item => `
                                                                            <span class="badge bg-light text-dark border fw-normal">
                                                                                ${item.item} <span class="fw-bold ms-1 text-primary">x${item.qty}</span>
                                                                            </span>
                                                                        `).join('')}
                                                                    </div>
                                                                </div>
                                                            `;
                                }
                                return '';
                            } catch (e) { return ''; }
                        })()}
                                            ` : ''}
                                        </div>

                                        <!-- Snack Section -->
                                        <div class="p-3 rounded-3 border ${p.snack === 'Ya' ? 'bg-white border-warning border-opacity-25 shadow-sm' : 'bg-light border-0 opacity-75'}">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle me-3 d-flex align-items-center justify-content-center" 
                                                         style="width: 40px; height: 40px; ${p.snack === 'Ya' ? 'background-color: #fff3cd; color: #856404;' : 'background-color: #e9ecef; color: #6c757d;'}">
                                                        <i class="fas fa-cookie-bite"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 fw-bold ${p.snack === 'Ya' ? 'text-dark' : 'text-muted'}">Snack</h6>
                                                        <small class="${p.snack === 'Ya' ? 'text-success' : 'text-muted'}">
                                                            ${p.snack === 'Ya' ? '<i class="fas fa-check-circle me-1"></i>Requested' : 'Not Requested'}
                                                        </small>
                                                    </div>
                                                </div>
                                                ${p.snack === 'Ya' && p.snack_qty ? `<span class="badge bg-warning text-dark rounded-pill">${p.snack_qty} Pax</span>` : ''}
                                            </div>
                                            
                                            ${p.snack === 'Ya' ? `
                                                ${p.snack_notes ? `<div class="small text-muted fst-italic mb-2 ps-2 border-start border-3 border-warning bg-light p-1 rounded">${p.snack_notes}</div>` : ''}
                                                
                                                ${(() => {
                            try {
                                const snackItems = typeof p.snack_items === 'string' ? JSON.parse(p.snack_items) : p.snack_items;
                                if (Array.isArray(snackItems) && snackItems.length > 0) {
                                    return `
                                                                <div class="mt-2 pt-2 border-top border-light">
                                                                    <div class="d-flex flex-wrap gap-2">
                                                                        ${snackItems.map(item => `
                                                                            <span class="badge bg-light text-dark border fw-normal">
                                                                                ${item.item} <span class="fw-bold ms-1 text-warning">x${item.qty}</span>
                                                                            </span>
                                                                        `).join('')}
                                                                    </div>
                                                                </div>
                                                            `;
                                }
                                return '';
                            } catch (e) { return ''; }
                        })()}
                                            ` : ''}
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label class="text-muted small text-uppercase fw-bold mb-2">Report Settings</label>
                                        <div class="d-flex justify-content-between mb-2 small">
                                            <span>Header/Footer</span>
                                            <span class="fw-bold">${p.header_footer === 'Ya' ? 'Yes' : 'No'}</span>
                                        </div>
                                        <div class="d-flex justify-content-between small">
                                            <span>Participant Photos</span>
                                            <span class="fw-bold">${p.foto_peserta === 'Ya' ? 'Yes' : 'No'}</span>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="text-muted small text-uppercase fw-bold mb-2">Documents</label>
                                        <div class="d-grid gap-2">
                                            ${sphLink}
                                            ${p.sph_number ? `
                                                <div class="p-2 border rounded bg-light d-flex align-items-center justify-content-center text-muted small">
                                                    <i class="fas fa-hashtag me-2"></i>SPH: <span class="fw-bold ms-1 text-dark">${p.sph_number}</span>
                                                </div>
                                            ` : ''}
                                        </div>
                                    </div>
                                    
                                    ${vendorMemoButton}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="card border-0 shadow-sm rounded-3">
                                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                                    <h6 class="text-uppercase fw-bold mb-0" style="color: #204EAB;"><i class="fas fa-vial me-2"></i>Exam Packages / Types</h6>
                                    <hr class="mt-2 mb-0" style="color: #204EAB; opacity: 0.2;">
                                </div>
                                <div class="card-body pt-3">
                                    ${p.exam_matrix_html || formatJenisPemeriksaan(p.jenis_pemeriksaan)}
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                // History Tab Content
                let historyHtml = `<div class="timeline">`;
                if (p.history && p.history.length > 0) {
                    p.history.forEach(log => {
                        let badgeClass = 'secondary';
                        let icon = 'fa-circle';
                        let status_lower = log.status_to.toLowerCase();

                        // Simple logic for badge/icon (abbreviated from kanban for brevity but keeping core)
                        if (status_lower.indexOf('approved') !== -1) { badgeClass = 'success'; icon = 'fa-check-circle'; }
                        else if (status_lower.indexOf('rejected') !== -1 || status_lower.indexOf('cancelled') !== -1) { badgeClass = 'danger'; icon = 'fa-times-circle'; }
                        else if (status_lower.indexOf('completed') !== -1) { badgeClass = 'primary'; icon = 'fa-flag-checkered'; }
                        else { badgeClass = 'info'; icon = 'fa-info-circle'; }

                        const formattedDate = log.formatted_at || log.changed_at;
                        const formattedStatus = log.status_to.replace(/_/g, ' ').toUpperCase();

                        const isPrimary = badgeClass === 'primary';
                        const markerStyle = isPrimary ? 'style="color: #204EAB; border-color: #204EAB;"' : '';
                        const textStyle = isPrimary ? 'style="color: #204EAB;"' : '';
                        // If primary, we don't use border-primary/text-primary classes to avoid conflict, or we override them.
                        // Bootstrap border-* classes use !important sometimes? No. But text-* might.
                        // Safest is to not include the class if primary.
                        const markerClass = isPrimary ? 'timeline-marker bg-white' : `timeline-marker bg-white border-${badgeClass} text-${badgeClass}`;
                        const textClass = isPrimary ? 'mb-1 fw-bold' : `mb-1 fw-bold text-${badgeClass}`;

                        historyHtml += `
                            <div class="timeline-item">
                                <div class="${markerClass}" ${markerStyle}>
                                    <i class="fas ${icon}"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <span class="timeline-title">${log.changed_by_name || 'System'}</span>
                                        <span class="timeline-time">${formattedDate}</span>
                                    </div>
                                    <div class="timeline-body">
                                        <p class="${textClass}" ${textStyle}>${formattedStatus}</p>
                                        <p class="mb-0 text-muted small">${log.notes || '-'}</p>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    historyHtml += `<div class="text-center py-4 text-muted">No history available.</div>`;
                }
                historyHtml += `</div>`;


                // Berita Acara Tab Header (for Manager Ops Approval) - Removed as per request (Auto-complete on cancel)
                let baHeader = '';


                // Technical Meeting Tab Content
                let tmHtml = '';
                if (p.technical_meeting) {
                    const tm = p.technical_meeting;
                    const tmDate = tm.tm_date_formatted || tm.tm_date;
                    const settingDate = tm.setting_alat_date_formatted || tm.setting_alat_date || '-';

                    let tmDocs = '';
                    if (tm.tm_file_path) {
                        tmDocs += `<a href="uploads/tm/${tm.tm_file_path}" target="_blank" class="btn btn-outline-danger me-2 mb-2"><i class="fas fa-file-pdf me-2"></i>TM Document</a>`;
                    }
                    if (tm.layout_file_path) {
                        tmDocs += `<a href="uploads/tm/${tm.layout_file_path}" target="_blank" class="btn btn-outline-primary me-2 mb-2"><i class="fas fa-map me-2"></i>Layout Document</a>`;
                    }

                    tmHtml = `
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="text-uppercase fw-bold mb-0" style="color: #204EAB;"><i class="fas fa-handshake me-2"></i>Technical Meeting Details</h6>
                                    ${(typeof userRole !== 'undefined' && (userRole === 'korlap' || userRole === 'admin_ops')) ?
                            `<a href="index.php?page=technical_meeting_create&project_id=${p.project_id}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit me-1"></i>Edit</a>`
                            : ''}
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-sm-4 text-muted small text-uppercase fw-bold">TM Date</div>
                                    <div class="col-sm-8 fw-bold">${tmDate}</div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4 text-muted small text-uppercase fw-bold">Type</div>
                                    <div class="col-sm-8"><span class="badge ${tm.tm_type === 'Offline' ? 'bg-info' : 'bg-success'}">${tm.tm_type}</span></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4 text-muted small text-uppercase fw-bold">Setting Alat Date</div>
                                    <div class="col-sm-8">${settingDate}</div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4 text-muted small text-uppercase fw-bold">Notes</div>
                                    <div class="col-sm-8 bg-light p-3 rounded fst-italic">${tm.notes}</div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-4 text-muted small text-uppercase fw-bold">Documents</div>
                                    <div class="col-sm-8">
                                        ${tmDocs || '<span class="text-muted fst-italic">No documents uploaded</span>'}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    tmHtml = `
                        <div class="text-center py-5">
                            <i class="fas fa-folder-open fs-1 text-muted opacity-25 mb-3"></i>
                            <p class="text-muted">No Technical Meeting data recorded for this project.</p>
                            ${(userRole === 'korlap' || userRole === 'admin_ops') && p.korlap_id ?
                            `<a href="index.php?page=technical_meeting_create&project_id=${p.project_id}" class="btn btn-primary rounded-pill px-4 mt-2"><i class="fas fa-plus-circle me-2"></i>Create Technical Meeting</a>`
                            : ''}
                        </div>
                    `;
                }

                // Tabs Construction
                // Tabs Construction
                var tabsHtml = `
                    <div class="tabs-scroll-wrapper mb-3" style="overflow-x: auto; -webkit-overflow-scrolling: touch; border-bottom: 1px solid #dee2e6; scrollbar-width: none;">
                        <style>
                            .tabs-scroll-wrapper::-webkit-scrollbar { display: none; }
                            #projectTabs .nav-link { white-space: nowrap; border: none; border-bottom: 2px solid transparent; transition: all 0.2s; }
                            #projectTabs .nav-link.active { border-bottom: 2px solid #204EAB !important; background: transparent !important; }
                        </style>
                        <ul class="nav nav-tabs flex-nowrap border-0" id="projectTabs" role="tablist" style="width: max-content;">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link ${activeTab === 'details' ? 'active fw-bold' : ''} px-3 py-2" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button" role="tab" style="${activeTab === 'details' ? 'color: #204EAB;' : 'color: #6c757d;'}"><i class="fas fa-info-circle me-1"></i>Detail</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link ${activeTab === 'chatter' ? 'active fw-bold' : ''} px-3 py-2" id="chatter-tab" data-bs-toggle="tab" data-bs-target="#chatter" type="button" role="tab" style="${activeTab === 'chatter' ? 'color: #204EAB;' : 'color: #6c757d;'}">
                                    <i class="fas fa-comments me-1"></i>Chatter
                                    <span id="chatter-badge" class="badge bg-danger rounded-pill ms-1" style="display:none;font-size:10px;"></span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link ${activeTab === 'vendor' ? 'active fw-bold' : ''} px-3 py-2" id="vendor-tab" data-bs-toggle="tab" data-bs-target="#vendor" type="button" role="tab" style="${activeTab === 'vendor' ? 'color: #204EAB;' : 'color: #6c757d;'}"><i class="fas fa-store me-1"></i>Vendor</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link ${activeTab === 'staff' ? 'active fw-bold' : ''} px-3 py-2" id="staff-tab" data-bs-toggle="tab" data-bs-target="#staff" type="button" role="tab" style="${activeTab === 'staff' ? 'color: #204EAB;' : 'color: #6c757d;'}"><i class="fas fa-user-friends me-1"></i>Field Team</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link ${activeTab === 'realization' ? 'active fw-bold' : ''} px-3 py-2" id="realization-tab" data-bs-toggle="tab" data-bs-target="#realization" type="button" role="tab" style="${activeTab === 'realization' ? 'color: #204EAB;' : 'color: #6c757d;'}"><i class="fas fa-clipboard-check me-1"></i>Results Team</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link ${activeTab === 'tm' ? 'active fw-bold' : ''} px-3 py-2" id="tm-tab" data-bs-toggle="tab" data-bs-target="#tm" type="button" role="tab" style="${activeTab === 'tm' ? 'color: #204EAB;' : 'color: #6c757d;'}"><i class="fas fa-handshake me-1"></i>TM</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link ${activeTab === 'ba' ? 'active fw-bold' : ''} px-3 py-2" id="ba-tab" data-bs-toggle="tab" data-bs-target="#ba" type="button" role="tab" style="${activeTab === 'ba' ? 'color: #204EAB;' : 'color: #6c757d;'}"><i class="fas fa-file-signature me-1"></i>BA</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link ${activeTab === 'history' ? 'active fw-bold' : ''} px-3 py-2" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab" style="${activeTab === 'history' ? 'color: #204EAB;' : 'color: #6c757d;'}"><i class="fas fa-history me-1"></i>History</button>
                            </li>
                        </ul>
                    </div>
                    <div class="tab-content" id="projectTabsContent">
                        <div class="tab-pane fade ${activeTab === 'details' ? 'show active' : ''}" id="details" role="tabpanel">
                            ${detailsHtml}
                        </div>
                        <div class="tab-pane fade ${activeTab === 'chatter' ? 'show active' : ''}" id="chatter" role="tabpanel">
                            <div id="chatter-content">
                                <div class="text-center py-5"><div class="spinner-border text-primary"></div></div>
                            </div>
                        </div>
                        <div class="tab-pane fade ${activeTab === 'vendor' ? 'show active' : ''}" id="vendor" role="tabpanel">
                             <div class="text-center py-4"><div class="spinner-border" style="color: #204EAB;"></div></div>
                        </div>
                        <div class="tab-pane fade ${activeTab === 'staff' ? 'show active' : ''}" id="staff" role="tabpanel">
                            <div id="staff-content">
                                <div class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Loading field team...</div>
                            </div>
                        </div>
                        <div class="tab-pane fade ${activeTab === 'realization' ? 'show active' : ''}" id="realization" role="tabpanel">
                            <div id="realization-content">
                                <div class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Loading results team...</div>
                            </div>
                        </div>
                        <div class="tab-pane fade ${activeTab === 'tm' ? 'show active' : ''}" id="tm" role="tabpanel">
                             ${tmHtml}
                        </div>
                        <div class="tab-pane fade ${activeTab === 'ba' ? 'show active' : ''}" id="ba" role="tabpanel">
                             ${baHeader}
                             <div id="ba-content">
                                <div class="text-center py-4"><div class="spinner-border" style="color: #204EAB;"></div></div>
                             </div>
                        </div>
                        <div class="tab-pane fade ${activeTab === 'history' ? 'show active' : ''}" id="history" role="tabpanel">
                            ${historyHtml}
                        </div>
                    </div>
                `;

                $('#modal-content-body').html(tabsHtml);

                // Initialize Chatter or Badge
                if (activeTab === 'chatter') {
                    loadChatter(projectId);
                } else {
                    $.get('index.php?page=get_unread_chat_count', { project_id: projectId }, function (res) {
                        try {
                            const data = JSON.parse(res);
                            if (data.unread > 0) {
                                $('#chatter-badge').text(data.unread).show();
                            }
                        } catch (e) { }
                    });
                }

                // Add click listener to refresh chatter when tab is clicked
                document.getElementById('chatter-tab').addEventListener('shown.bs.tab', function (e) {
                    loadChatter(projectId);
                    $('#chatter-badge').hide(); // Clear badge
                });

                // Add action buttons logic
                let buttons = '';
                const btnStyle = "box-shadow: 0 4px 12px rgba(0,0,0,0.1); transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1); font-weight: 600; letter-spacing: 0.5px;";
                const hoverEffect = "onmouseover=\"this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 16px rgba(0,0,0,0.2)'\" onmouseout=\"this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.1)'\"";

                if (typeof userRole !== 'undefined') {
                    if ((userRole === 'manager_ops' || userRole === 'superadmin') && p.status_project === 'need_approval_manager') {
                        buttons += `<button class="btn btn-success me-2 rounded-pill px-4 text-uppercase border-0" style="${btnStyle} background: linear-gradient(135deg, #28a745 0%, #218838 100%);" ${hoverEffect} onclick="updateProjectStatus('${p.project_id}', 'need_approval_head', 'Approve this project?')"><i class="fas fa-check-circle me-2"></i>Approve</button>`;
                        buttons += `<button class="btn btn-danger me-2 rounded-pill px-4 text-uppercase border-0" style="${btnStyle} background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);" ${hoverEffect} onclick="updateProjectStatus('${p.project_id}', 'rejected', 'Reject this project?')"><i class="fas fa-times-circle me-2"></i>Reject</button>`;
                        buttons += `<button class="btn btn-warning rounded-pill px-4 text-uppercase border-0 text-white" style="${btnStyle} background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);" ${hoverEffect} onclick="updateProjectStatus('${p.project_id}', 're-nego', 'Request re-negotiation?')"><i class="fas fa-sync-alt me-2"></i>Re-Nego</button>`;
                    } else if ((userRole === 'head_ops' || userRole === 'superadmin') && p.status_project === 'need_approval_head') {
                        buttons += `<button class="btn btn-success me-2 rounded-pill px-4 text-uppercase border-0" style="${btnStyle} background: linear-gradient(135deg, #28a745 0%, #218838 100%);" ${hoverEffect} onclick="updateProjectStatus('${p.project_id}', 'approved', 'Approve this project?')"><i class="fas fa-check-double me-2"></i>Final Approve</button>`;
                        buttons += `<button class="btn btn-danger me-2 rounded-pill px-4 text-uppercase border-0" style="${btnStyle} background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);" ${hoverEffect} onclick="updateProjectStatus('${p.project_id}', 'rejected', 'Reject this project?')"><i class="fas fa-times-circle me-2"></i>Reject</button>`;
                        buttons += `<button class="btn btn-warning rounded-pill px-4 text-uppercase border-0 text-white" style="${btnStyle} background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);" ${hoverEffect} onclick="updateProjectStatus('${p.project_id}', 're-nego', 'Request re-negotiation?')"><i class="fas fa-sync-alt me-2"></i>Re-Nego</button>`;
                    }
                }

                // Update modal footer
                const footer = $('#detailModal .modal-footer');
                if (buttons) {
                    footer.html(`<button type="button" class="btn btn-link text-decoration-none text-muted me-auto px-3" data-bs-dismiss="modal">Close</button>${buttons}`);
                } else {
                    footer.html(`<button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>`);
                }

                // Load other tabs data
                loadVendorData(projectId);
                loadBaData(projectId);
                renderStaffAssignments(p.staff_assignments);
                renderDWRealizations(p.realizations);

            } catch (e) {
                console.error("Project Detail Parse Error:", e);
                $('#modal-content-body').html('<div class="alert alert-danger">Error processing project data. Please contact support.</div>');
            }
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error:", error);
            $('#modal-content-body').html('<div class="alert alert-danger">Failed to load project details. Check connection.</div>');
        }
    });
}

function updateProjectStatus(id, status, confirmMsg) {
    if (status === 're-nego' || status === 'rejected') {
        Swal.fire({
            title: 'Reason Required',
            text: confirmMsg,
            input: 'textarea',
            inputPlaceholder: 'Enter reason here...',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Submit',
            confirmButtonColor: '#204EAB',
            cancelButtonColor: '#d33',
            preConfirm: (reason) => {
                if (!reason) {
                    Swal.showValidationMessage('Reason is required');
                }
                return reason;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                submitStatusUpdate(id, status, result.value);
            }
        });
    } else {
        Swal.fire({
            title: 'Confirmation',
            text: confirmMsg,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, proceed',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                submitStatusUpdate(id, status, null);
            }
        });
    }
}

function submitStatusUpdate(id, status, reason) {
    Swal.fire({
        title: 'Processing...',
        didOpen: () => Swal.showLoading()
    });
    $.post('index.php?page=update_project_status', { project_id: id, status: status, reason: reason }, function (res) {
        try {
            if (typeof res === 'string') {
                res = JSON.parse(res);
            }
            if (res.status === 'success') {
                Swal.fire('Success', 'Status updated successfully', 'success').then(() => location.reload());
            } else {
                Swal.fire('Error', res.message || 'Update failed', 'error');
            }
        } catch (e) { Swal.fire('Error', 'Invalid server response', 'error'); }
    });
}

function loadVendorData(projectId) {
    $.ajax({
        url: 'index.php?page=get_vendor_allocations_ajax',
        data: { project_id: projectId },
        success: function (vResponse) {
            try {
                var vRes = typeof vResponse === 'string' ? JSON.parse(vResponse) : vResponse;
                if (vRes.status === 'success' && vRes.data.length > 0) {
                    let vendorTable = `
                        <div class="table-responsive mt-3">
                            <table class="table table-bordered table-sm table-striped">
                                <thead style="background-color: #f0f8ff; color: #204EAB;"><tr><th>Exam Type</th><th>Participants</th><th>Notes</th><th>Assigned Vendor</th></tr></thead>
                                <tbody>
                    `;
                    vRes.data.forEach(item => {
                        vendorTable += `
                            <tr>
                                <td>${item.exam_type}</td>
                                <td>${item.participant_count}</td>
                                <td>${item.notes || '-'}</td>
                                <td class="${item.assigned_vendor_name ? 'fw-bold text-success' : 'text-muted fst-italic'}">
                                    ${item.assigned_vendor_name || 'Pending'}
                                </td>
                            </tr>
                        `;
                    });
                    vendorTable += `</tbody></table></div>`;
                    $('#vendor').html(vendorTable);
                } else {
                    $('#vendor').html('<div class="p-4 text-center text-muted border rounded bg-light">No vendor allocations found.</div>');
                }
            } catch (e) {
                $('#vendor').html('<div class="alert alert-danger">Error loading vendor data.</div>');
            }
        },
        error: function () { $('#vendor').html('<div class="alert alert-danger">Failed to load vendor data.</div>'); }
    });
}

function loadBaData(projectId) {
    $.ajax({
        url: 'index.php?page=get_ba_status_ajax',
        data: { project_id: projectId },
        success: function (baResponse) {
            // Determine container
            var container = $('#ba-content').length ? $('#ba-content') : $('#ba');

            try {
                var baRes = typeof baResponse === 'string' ? JSON.parse(baResponse) : baResponse;
                if (baRes.status === 'success') {
                    // Add hidden form for direct upload
                    let baTable = `
                        <form id="directUploadForm" action="index.php?page=upload_berita_acara" method="POST" enctype="multipart/form-data" style="display:none;">
                            <input type="hidden" name="project_id" id="direct_upload_project_id">
                            <input type="hidden" name="date" id="direct_upload_date">
                            <input type="hidden" name="original_date" id="direct_upload_original_date">
                            <input type="file" name="ba_file" id="direct_upload_file" accept=".pdf" onchange="document.getElementById('directUploadForm').submit()">
                        </form>
                        <div class="table-responsive mt-3">
                            <table class="table table-bordered table-sm table-striped align-middle">
                                <thead style="background-color: #f0f8ff; color: #204EAB;"><tr><th style="width: 180px;">MCU Date</th><th>Status</th><th>Action</th></tr></thead>
                                <tbody>
                    `;

                    const role = typeof userRole !== 'undefined' ? userRole : '';

                    baRes.data.forEach(item => {
                        let statusBadge = '';
                        let actionButtons = '';

                        // Editable Date Input
                        // Only editable if pending and user is korlap? Or always editable?
                        // User requirement: "date MCU nya bisa di rubah sesuai tanggal realisasinya"
                        // We'll allow editing if not cancelled.
                        let dateInput = `<input type="date" class="form-control form-control-sm" id="date_${item.date}" value="${item.date}" data-original="${item.date}">`;

                        if (item.status == 'uploaded') {
                            statusBadge = '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Uploaded</span>';
                            actionButtons = `<a href="${item.file_url}" target="_blank" class="btn btn-sm btn-outline-primary" style="color: #204EAB; border-color: #204EAB;"><i class="fas fa-eye"></i> View</a>`;

                            // If user is manager/head/superadmin, show cancel reject/approve logic if pending cancellation
                            // Logic for checking cancellation is not here, it seems logic is simplistic here
                        } else if (item.status == 'pending_cancellation' || item.status == 'cancelled') {
                            // Unified Cancelled View (No approval needed)
                            statusBadge = '<span class="badge bg-secondary"><i class="fas fa-ban me-1"></i>Cancelled</span>';
                            actionButtons = '<span class="text-muted small fst-italic">Cancelled</span>';

                        } else if (item.status == 'cancelled') {
                            statusBadge = `<span class="badge bg-secondary" title="Cancellation Approved"><i class="fas fa-check-double me-1"></i>Cancelled (Verified)</span>`;
                            actionButtons = '<span class="text-success small"><i class="fas fa-check me-1"></i>Approved</span>';
                        } else {
                            statusBadge = '<span class="badge bg-secondary">Pending</span>';
                            if (role === 'korlap') {
                                actionButtons = `
                                    <button class="btn btn-sm btn-primary" style="background-color: #204EAB; border-color: #204EAB;" onclick="triggerDirectUpload('${projectId}', '${item.date}', '${item.formatted_date}')"><i class="fas fa-upload me-1"></i>Upload</button>
                                    <button class="btn btn-sm btn-danger ms-1" onclick="showCancelModal('${projectId}', '${item.date}', '${item.formatted_date}')"><i class="fas fa-times me-1"></i>Cancel</button>
                                `;
                            } else {
                                actionButtons = '<span class="text-muted small">Need Upload BA</span>';
                                dateInput = item.formatted_date;
                            }
                        }

                        // If user is not korlap, show formatted date
                        if (role !== 'korlap') {
                            dateInput = item.formatted_date;
                        }

                        baTable += `
                            <tr>
                                <td>${dateInput}</td>
                                <td>${statusBadge}</td>
                                <td>${actionButtons}</td>
                            </tr>
                        `;
                    });

                    baTable += `</tbody></table></div>`;
                    container.html(baTable);
                } else {
                    container.html('<div class="alert alert-info">No dates found for this project.</div>');
                }
            } catch (e) {
                console.error("BA Data Parse Error:", e);
                container.html('<div class="alert alert-danger">Error loading Berita Acara data.</div>');
            }
        },
        error: function () {
            var container = $('#ba-content').length ? $('#ba-content') : $('#ba');
            container.html('<div class="alert alert-danger">Failed to load Berita Acara data.</div>');
        }
    });
}

function triggerDirectUpload(projectId, date, formattedDate) {
    // Populate the modal fields
    var projectIdField = document.getElementById('upload_ba_project_id');
    var dateField = document.getElementById('upload_ba_date');
    var dateDisplayField = document.getElementById('upload_ba_date_display');

    if (projectIdField) projectIdField.value = projectId;
    if (dateField) dateField.value = date;
    if (dateDisplayField) dateDisplayField.value = formattedDate || date;

    // Show the modal
    var modalEl = document.getElementById('uploadBaModal');
    if (modalEl) {
        var myModal = new bootstrap.Modal(modalEl);
        myModal.show();
    } else {
        console.error('Upload modal element #uploadBaModal not found in DOM.');
        alert('Error: Upload interface not loaded. Please refresh the page.');
    }
}



function loadChatter(projectId) {
    activeReplyId = null; // Reset reply state on load
    const container = $('#chatter-content');

    $.ajax({
        url: 'index.php?page=get_comments',
        data: { project_id: projectId },
        success: function (response) {
            try {
                let comments = [];
                let userStatus = { is_muted: 0 };
                let currentUserId = null;

                try {
                    const parsed = typeof response === 'string' ? JSON.parse(response) : response;
                    if (Array.isArray(parsed)) {
                        comments = parsed;
                    } else {
                        comments = parsed.comments || [];
                        userStatus = parsed.user_status || { is_muted: 0 };
                        currentUserId = parsed.current_user_id;
                    }
                } catch (e) {
                    console.error("JSON Parse Error", e);
                }

                let html = `
                    <div class="d-flex justify-content-end mb-2">
                         <button class="btn btn-sm ${userStatus.is_muted == 1 ? 'btn-danger' : 'btn-outline-secondary'}" onclick="toggleChatMute('${projectId}', this)" title="${userStatus.is_muted == 1 ? 'Unmute Notifications' : 'Mute Notifications'}">
                             <i class="fas ${userStatus.is_muted == 1 ? 'fa-bell-slash' : 'fa-bell'}"></i> ${userStatus.is_muted == 1 ? 'Muted' : 'Mute'}
                         </button>
                    </div>
                    <div class="chatter-container d-flex flex-column" style="height: 500px;">
                        <div class="chatter-messages flex-grow-1 overflow-auto p-3 bg-white rounded border mb-3" id="chatter-messages">
                `;

                if (comments.length === 0) {
                    html += `<div class="text-center text-muted py-5"><i class="fas fa-comments fs-1 opacity-25 mb-3"></i><p>No comments yet. Start the conversation!</p></div>`;
                } else {
                    comments.forEach(c => {
                        const date = new Date(c.created_at).toLocaleString();
                        const isMe = (currentUserId && c.user_id == currentUserId);

                        // Reply Context Block
                        let replyBlock = '';
                        if (c.parent_id) {
                            replyBlock = `
                                <div class="mb-2 p-2 rounded bg-white bg-opacity-50 border-start border-4 border-primary small" style="background-color: rgba(0,0,0,0.05);">
                                    <strong class="text-primary d-block">${c.parent_user_name || 'Unknown'}</strong>
                                    <span class="text-muted text-truncate d-block" style="max-width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${c.parent_message || 'Message deleted'}</span>
                                </div>
                             `;
                        }

                        // Escape for JS function
                        const safeName = (c.full_name || '').replace(/'/g, "\\'");
                        const safeMsg = (c.message || '').replace(/'/g, "\\'").replace(/\n/g, ' ').replace(/"/g, '&quot;');

                        const replyBtn = `
                            <button class="btn btn-link btn-sm text-muted p-0 ms-2 opacity-50 hover-opacity-100" onclick="triggerReply('${c.id}', '${safeName}', '${safeMsg}')" title="Reply">
                                <i class="fas fa-reply"></i>
                            </button>
                        `;

                        if (isMe) {
                            html += `
                                <div class="d-flex mb-3 flex-row-reverse chat-message-row">
                                    <div class="flex-shrink-0 ms-3">
                                        <div class="avatar-circle bg-primary text-white fw-bold d-flex align-items-center justify-content-center border border-white shadow-sm" style="width: 40px; height: 40px; border-radius: 50%;">
                                            ${c.full_name.charAt(0)}
                                        </div>
                                    </div>
                                    <div class="" style="max-width: 80%;">
                                        <div class="p-3 rounded-3 shadow-sm border position-relative" style="background-color: #e3f2fd !important;">
                                            ${replyBlock}
                                            <div class="d-flex justify-content-between align-items-center mb-1 border-bottom border-secondary border-opacity-25 pb-1">
                                                <small class="text-muted" style="font-size: 0.75rem;">${date}</small>
                                                <div class="d-flex align-items-center">
                                                    ${replyBtn}
                                                    <strong class="text-primary small ms-2">${c.full_name}</strong>
                                                </div>
                                            </div>
                                            <div class="text-dark" style="white-space: pre-wrap;">${c.message}</div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        } else {
                            html += `
                                <div class="d-flex mb-3 chat-message-row">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="avatar-circle bg-light text-primary fw-bold d-flex align-items-center justify-content-center border" style="width: 40px; height: 40px; border-radius: 50%;">
                                            ${c.full_name.charAt(0)}
                                        </div>
                                    </div>
                                    <div class="" style="max-width: 80%;">
                                        <div class="bg-light p-3 rounded-3 border position-relative">
                                            ${replyBlock}
                                            <div class="d-flex justify-content-between align-items-center mb-1 border-bottom pb-1">
                                                <strong class="text-primary small">${c.full_name} <span class="text-muted fw-normal">(${c.role})</span></strong>
                                                <div class="d-flex align-items-center">
                                                    <small class="text-muted" style="font-size: 0.75rem;">${date}</small>
                                                    ${replyBtn}
                                                </div>
                                            </div>
                                            <div class="text-dark" style="white-space: pre-wrap;">${c.message}</div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        }
                    });
                }

                html += `
                        </div>
                        <div class="chatter-input bg-light p-3 rounded border position-relative">
                            <style>
                                #mentionSuggestions .list-group-item { cursor: pointer; }
                                #mentionSuggestions .list-group-item:hover { background-color: #f8f9fa; }
                                #commentMessageDiv:empty:before { content: attr(placeholder); color: #aaa; }
                                .mention-highlight { color: #204EAB; font-weight: bold; background-color: #e8f0fe; padding: 0 4px; border-radius: 4px; }
                                .hover-opacity-100:hover { opacity: 1 !important; }
                            </style>
                            <div id="mentionSuggestions" class="list-group position-absolute shadow" style="display:none; bottom: 100%; left: 20px; right: 20px; z-index: 1000; max-height: 200px; overflow-y: auto;"></div>
                            
                            <!-- Reply Context Bar -->
                            <div id="reply-context-bar" class="alert alert-secondary mb-2 p-2 small" style="display:none; border-left: 4px solid #204EAB;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="overflow-hidden">
                                        <strong class="text-primary" id="reply-to-name"></strong>
                                        <div class="text-muted text-truncate" id="reply-to-text" style="max-width: 300px;"></div>
                                    </div>
                                    <button type="button" class="btn-close btn-sm" onclick="cancelReply()"></button>
                                </div>
                            </div>

                            <div class="mb-2">
                                <div id="commentMessageDiv" class="form-control" contenteditable="true" style="min-height: 80px; max-height: 150px; overflow-y: auto;" placeholder="Type a message... Use @username to mention users."></div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Mentions: <span class="text-primary fw-bold">@username</span></small>
                                <button type="button" onclick="sendComment(event, '${projectId}')" class="btn btn-primary rounded-pill px-4" style="background-color: #204EAB; border-color: #204EAB;"><i class="fas fa-paper-plane me-2"></i>Send</button>
                            </div>
                        </div>
                    </div>
                `;

                container.html(html);

                // Scroll to bottom
                const messagesDiv = document.getElementById('chatter-messages');
                if (messagesDiv) messagesDiv.scrollTop = messagesDiv.scrollHeight;

                // Initialize Mention Logic
                initMentionLogic(projectId);
                initSwipeToReply();

            } catch (e) {
                console.error(e);
                container.html('<div class="alert alert-danger">Error loading chatter.</div>');
            }
        }
    });
}

function sendComment(e, projectId) {
    e.preventDefault();

    // Support both textarea (legacy/fallback) and contenteditable div
    const textarea = $('#commentMessage');
    const divInput = $('#commentMessageDiv');
    let message = '';

    if (textarea.length) {
        message = textarea.val();
    } else if (divInput.length) {
        message = divInput[0].innerText;
    }

    if (!message || message.trim() === '') return;

    const btn = $(e.target).closest('button');
    const originalBtn = btn.html();

    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Sending...');

    // Harvest mentions
    let mentions = [];
    if (divInput.length) {
        divInput.find('.mention-highlight').each(function () {
            const u = $(this).data('username');
            if (u) mentions.push(u);
        });
    }

    $.post('index.php?page=add_comment', {
        project_id: projectId,
        message: message,
        mentions: mentions,
        parent_id: activeReplyId
    }, function (response) {
        try {
            const res = typeof response === 'string' ? JSON.parse(response) : response;
            if (res.status === 'success') {
                activeReplyId = null;
                loadChatter(projectId);
            } else {
                alert('Error sending message');
                btn.prop('disabled', false).html(originalBtn);
            }
        } catch (e) {
            alert('Error parsing response');
            btn.prop('disabled', false).html(originalBtn);
        }
    });
}

function initMentionLogic(projectId) {
    const $input = $('#commentMessageDiv');
    const $suggestions = $('#mentionSuggestions');

    $input.on('input keyup click', function (e) {
        const selection = window.getSelection();
        if (!selection.rangeCount) return;
        const range = selection.getRangeAt(0);

        let textNode = range.startContainer;
        let caretPos = range.startOffset;

        // Handle case where caret is in element but not text node
        if (textNode.nodeType !== Node.TEXT_NODE) {
            $suggestions.hide();
            return;
        }

        const text = textNode.textContent;
        const textBeforeCaret = text.substring(0, caretPos);
        const atIndex = textBeforeCaret.lastIndexOf('@');

        if (atIndex !== -1) {
            // Check valid trigger: start of string or preceded by whitespace
            if (atIndex === 0 || /\s/.test(text.charAt(atIndex - 1))) {
                const query = textBeforeCaret.substring(atIndex + 1);

                // Allow spaces in query for full name search, but maybe limit length or just rely on the user
                // We'll allow up to 20 chars or similar to prevent searching whole paragraph
                if (query.length > 20) {
                    $suggestions.hide();
                    return;
                }

                $.get('index.php?page=search_users', { term: query }, function (response) {
                    try {
                        const users = typeof response === 'string' ? JSON.parse(response) : response;
                        if (users.length > 0) {
                            let html = '';
                            users.forEach(u => {
                                html += `
                                <div class="list-group-item list-group-item-action d-flex align-items-center mention-item" style="cursor:pointer;" data-username="${u.username}" data-fullname="${u.full_name}" data-userid="${u.user_id}">
                                    <div class="avatar-circle bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px; font-size: 12px;">
                                        ${u.full_name.charAt(0)}
                                    </div>
                                    <div>
                                        <div class="fw-bold small">${u.full_name}</div>
                                        <div class="text-muted" style="font-size: 11px;">@${u.username} (${u.role})</div>
                                    </div>
                                </div>
                            `;
                            });
                            $suggestions.html(html).show();
                        } else {
                            $suggestions.hide();
                        }
                    } catch (e) {
                        console.error('Error parsing users', e);
                    }
                });
                return;
            }
        }
        $suggestions.hide();
    });

    // Handle suggestion click via delegation
    $suggestions.on('mousedown', '.mention-item', function (e) {
        e.preventDefault();
        const username = $(this).data('username');
        const fullname = $(this).data('fullname');
        const userid = $(this).data('userid');
        insertMention(fullname, username, userid);
    });

    // Close suggestions on click outside
    $(document).off('click.chatterDismiss').on('click.chatterDismiss', function (e) {
        if (!$(e.target).closest('.chatter-input').length) {
            $suggestions.hide();
        }
    });
}

function insertMention(displayName, username, userid) {
    const $input = $('#commentMessageDiv');
    const $suggestions = $('#mentionSuggestions');
    const selection = window.getSelection();
    if (!selection.rangeCount) return;
    const range = selection.getRangeAt(0);
    const textNode = range.startContainer;

    // Ensure we are in a text node
    if (textNode.nodeType !== Node.TEXT_NODE) return;

    const text = textNode.textContent;
    const caretPos = range.startOffset;
    const textBeforeCaret = text.substring(0, caretPos);
    const atIndex = textBeforeCaret.lastIndexOf('@');

    if (atIndex !== -1) {
        // Create the mention node
        const mentionNode = document.createElement('span');
        mentionNode.className = 'mention-highlight';
        mentionNode.textContent = '@' + displayName;
        mentionNode.contentEditable = false;
        mentionNode.setAttribute('data-username', username);
        mentionNode.setAttribute('data-userid', userid);

        // Split text
        const beforeText = text.substring(0, atIndex);
        const afterText = text.substring(caretPos);

        const parent = textNode.parentNode;

        if (beforeText) {
            parent.insertBefore(document.createTextNode(beforeText), textNode);
        }
        parent.insertBefore(mentionNode, textNode);

        // Add a space after mention
        const spaceNode = document.createTextNode('\u00A0');
        parent.insertBefore(spaceNode, textNode);

        if (afterText) {
            parent.insertBefore(document.createTextNode(afterText), textNode);
        }

        parent.removeChild(textNode);

        // Move caret after the space
        const newRange = document.createRange();
        newRange.setStartAfter(spaceNode);
        newRange.collapse(true);
        selection.removeAllRanges();
        selection.addRange(newRange);

        $suggestions.hide();
        $input.focus();
    }
}

function toggleChatMute(projectId, btn) {
    const $btn = $(btn);
    $btn.prop('disabled', true);

    $.post('index.php?page=toggle_chat_mute', { project_id: projectId }, function (response) {
        try {
            const status = typeof response === 'string' ? JSON.parse(response) : response;
            if (status.is_muted == 1) {
                $btn.removeClass('btn-outline-secondary').addClass('btn-danger');
                $btn.html('<i class="fas fa-bell-slash"></i> Muted');
                $btn.attr('title', 'Unmute Notifications');
            } else {
                $btn.removeClass('btn-danger').addClass('btn-outline-secondary');
                $btn.html('<i class="fas fa-bell"></i> Mute');
                $btn.attr('title', 'Mute Notifications');
            }
        } catch (e) {
            console.error(e);
            alert('Error updating mute status');
        }
        $btn.prop('disabled', false);
    });
}

// Global variable for reply state
let activeReplyId = null;

function triggerReply(id, name, message) {
    activeReplyId = id;
    $('#reply-to-name').text(name);

    // Strip HTML from message for display in quote
    const div = document.createElement('div');
    div.innerHTML = message;
    $('#reply-to-text').text(div.textContent || div.innerText || '');

    $('#reply-context-bar').slideDown('fast');
    $('#commentMessageDiv').focus();
}

function cancelReply() {
    activeReplyId = null;
    $('#reply-to-name').text('');
    $('#reply-to-text').text('');
    $('#reply-context-bar').slideUp('fast');
}

function initSwipeToReply() {
    const messages = document.querySelectorAll('.chat-message-row');
    messages.forEach(row => {
        let startX = 0;
        let startY = 0;
        let currentX = 0;
        let isSwiping = false;
        let isScrolling = false;

        row.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
            isSwiping = false;
            isScrolling = false;
            currentX = 0;
            row.style.transition = 'none';
        }, { passive: true });

        row.addEventListener('touchmove', (e) => {
            if (isScrolling) return;

            const x = e.touches[0].clientX;
            const y = e.touches[0].clientY;
            const deltaX = x - startX;
            const deltaY = Math.abs(y - startY);

            // Determine if scrolling or swiping (if vertical move is dominant, it's a scroll)
            if (!isSwiping && deltaY > Math.abs(deltaX)) {
                isScrolling = true;
                return;
            }

            // Only allow swipe right
            if (deltaX > 0 && deltaX < 150) {
                // If horizontal move is dominant and positive
                if (Math.abs(deltaX) > deltaY) {
                    isSwiping = true;
                    currentX = deltaX;
                    row.style.transform = `translateX(${deltaX}px)`;
                }
            }
        }, { passive: true });

        row.addEventListener('touchend', (e) => {
            if (isSwiping) {
                if (currentX > 80) { // Threshold
                    const replyBtn = row.querySelector('button[onclick^="triggerReply"]');
                    if (replyBtn) {
                        replyBtn.click();
                        if (navigator.vibrate) navigator.vibrate(50);
                    }
                }
            }

            // Reset position
            row.style.transition = 'transform 0.2s ease-out';
            row.style.transform = 'translateX(0)';

            isSwiping = false;
            currentX = 0;
            isScrolling = false;
        });
    });
}

function renderStaffAssignments(items) {
    let container = $('#staff-content');
    if (!items || items.length === 0) {
        container.html('<div class="p-4 text-center text-muted border rounded bg-light">No staff assignments found.</div>');
        return;
    }

    let html = `
        <div class="table-responsive mt-3">
            <table class="table table-bordered table-sm table-striped align-middle">
                <thead style="background-color: #f0f8ff; color: #204EAB;">
                    <tr>
                        <th style="width: 150px;">Date</th>
                        <th>Staff Name</th>
                        <th>Role</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
    `;

    items.forEach(item => {
        const dateStr = item.formatted_date || item.date;

        html += `
            <tr>
                <td class="fw-bold">${dateStr}</td>
                <td>${item.man_power_name}</td>
                <td><span class="badge bg-light text-dark border">${item.role}</span></td>
                <td class="small text-muted">${item.notes || '-'}</td>
            </tr>
        `;
    });

    html += `</tbody></table></div>`;
    container.html(html);
}

function renderDWRealizations(items) {
    let container = $('#realization-content');
    if (!items || items.length === 0) {
        container.html('<div class="p-4 text-center text-muted border rounded bg-light">No realization data found for DW Tim Hasil.</div>');
        return;
    }

    let html = `
        <div class="table-responsive mt-3">
            <table class="table table-bordered table-sm table-striped align-middle">
                <thead style="background-color: #f0f8ff; color: #204EAB;">
                    <tr>
                        <th style="width: 150px;">Date</th>
                        <th>DW Name</th>
                        <th>Koordinator Hasil</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
    `;

    items.forEach(item => {
        const dateStr = item.formatted_date || item.date;

        html += `
            <tr>
                <td class="fw-bold">${dateStr}</td>
                <td class="fw-bold text-primary">${item.user_name}</td>
                <td><small class="text-muted"><i class="fas fa-user-circle me-1"></i>${item.kohas_name}</small></td>
                <td class="small text-muted">${item.notes || '-'}</td>
            </tr>
        `;
    });

    html += `</tbody></table></div>`;
    container.html(html);
}

<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<?php
// Determine if we are editing
$is_edit = isset($project_data);
$action = $is_edit ? "index.php?page=projects_update" : "index.php?page=projects_store";
$title = $is_edit ? "Edit Project" : "Create New Project";
$btn_text = $is_edit ? "Update Project" : "Submit Project";

// Check for session form data
$form_data = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : ($is_edit ? $project_data : []);
if (isset($_SESSION['form_data'])) unset($_SESSION['form_data']);

// Handle dates
$date_value = '';
if (isset($form_data['tanggal_mcu'])) {
    $date_value = htmlspecialchars($form_data['tanggal_mcu']);
} elseif ($is_edit && isset($project_data['tanggal_mcu'])) {
    $decoded = json_decode($project_data['tanggal_mcu']);
    if (is_array($decoded)) {
        $date_value = implode(', ', $decoded);
    } else {
        $date_value = $project_data['tanggal_mcu'];
    }
}

// Handle Header/Footer Separation for Edit
$hf_value = ViewHelper::getValue('header_footer', $form_data);
$hf_select = 'Bumame';
$hf_notes = '';

if ($hf_value) {
    if (stripos($hf_value, 'Bumame') !== false) {
        $hf_select = 'Bumame';
    } elseif (stripos($hf_value, 'White Label') !== false) {
        $hf_select = 'White Label';
        // Extract notes if any: "White Label (Notes)"
        if (preg_match('/White Label \((.*?)\)/', $hf_value, $m)) {
            $hf_notes = $m[1];
        }
    } elseif (stripos($hf_value, 'Co-Branding') !== false) {
        $hf_select = 'Co-Branding';
        if (preg_match('/Co-Branding \((.*?)\)/', $hf_value, $m)) {
            $hf_notes = $m[1];
        }
    } else {
        // Default unknown values to White Label with existing text as notes
        $hf_select = 'White Label';
        $hf_notes = $hf_value;
    }
}
?>

<link rel="stylesheet" href="css/medical_theme.css">

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center my-4">
        <div>
            <h2 class="fw-bold mb-0" style="color: #204EAB;"><?php echo $title; ?></h2>
            <p class="text-muted mb-0">Fill in the form below to create or update a project.</p>
        </div>
        <a href="index.php?page=projects_list" class="btn btn-secondary rounded-pill px-4"><i class="fas fa-arrow-left me-2"></i>Back</a>
    </div>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> <?php echo $_SESSION['error_message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <form action="<?php echo $action; ?>" method="POST" enctype="multipart/form-data" class="needs-validation" id="projectForm" novalidate>
        <!-- CSRF Token -->
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">

        <?php if($is_edit): ?>
            <input type="hidden" name="project_id" value="<?php echo ViewHelper::getValue('project_id', $project_data); ?>">
        <?php endif; ?>
        
        <?php if(!$is_edit): ?>
        <!-- Import from File -->
        <div class="card card-medical mb-4 bg-light border-dashed">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="fw-bold text-bumame mb-1"><i class="fas fa-file-excel me-2"></i>Import Data from Excel</h6>
                        <p class="text-muted small mb-0">Upload an Excel file to auto-fill the project details below. (Single Project)</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" onclick="downloadProjectSingleTemplate()" class="btn btn-outline-primary btn-sm rounded-pill">
                            <i class="fas fa-download me-1"></i>Template
                        </button>
                        <button type="button" class="btn btn-primary btn-sm rounded-pill" onclick="document.getElementById('import_file').click()">
                            <i class="fas fa-upload me-1"></i>Upload File
                        </button>
                        <input type="file" id="import_file" accept=".xlsx, .xls" style="display: none;" onchange="handleImport(this)">
                    </div>
                </div>
            </div>
        </div>
        
        <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
        <script>
        function downloadProjectSingleTemplate() {
            const rows = [
                ['Project ID', 'Project Name', 'Company Names (Comma Separated)', 'Sales Person ID', 'Project Type (on_site/walk_in)', 
                'Clinic Location (Required for Walk-In)', 'Jenis Pemeriksaan', 'Total Peserta', 'Tanggal MCU (YYYY-MM-DD, separated by comma if multiple)', 
                'Alamat', 'Notes', 'Lunch (Ya/Tidak)', 'Snack (Ya/Tidak)', 'SPH Link (GDrive)', 'Referral No SPH', 
                'Lunch Budget', 'Snack Budget', 'Lunch Items (Item:Qty|Item:Qty)', 'Snack Items (Item:Qty|Item:Qty)'],
                ['PRJ-001', 'Annual MCU PT Example', 'PT Example Indonesia, PT Example Branch', '1', 'on_site', 
                '', 'Paket Silver', '100', '2026-02-14', 'Jl. Sudirman No. 1, Jakarta', 'VIP handling required', 
                'Ya', 'Ya', 'https://drive.google.com/file/d/example/view', 'REF-123', '50000', '25000', 
                'Nasi Padang:50|Ayam Bakar:50', 'Risoles:100|Puding:100']
            ];
            const ws = XLSX.utils.aoa_to_sheet(rows);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Project Template");
            ws['!cols'] = [
                {wch: 15}, {wch: 30}, {wch: 40}, {wch: 15}, {wch: 20},
                {wch: 30}, {wch: 25}, {wch: 15}, {wch: 30}, {wch: 40},
                {wch: 30}, {wch: 15}, {wch: 15}, {wch: 40}, {wch: 20},
                {wch: 15}, {wch: 15}, {wch: 40}, {wch: 40}
            ];
            XLSX.writeFile(wb, `template_single_project_${new Date().getTime()}.xlsx`);
        }

        function handleImport(input) {
            const file = input.files[0];
            if (!file) return;

            const reader = new FileReader();
            
            reader.onload = function(e) {
                const data = e.target.result;
                    let rows = [];

                    // Use XLSX for both CSV and Excel to handle quoted fields correctly
                    const workbook = XLSX.read(data, {type: 'binary'});
                    const sheetName = workbook.SheetNames[0];
                    const sheet = workbook.Sheets[sheetName];
                    rows = XLSX.utils.sheet_to_json(sheet, {header: 1});
                    
                    // Assuming Row 1 is Header, Row 2 is Data
                if (rows.length >= 2) {
                    const dataRow = rows[1];
                    // Mapping based on template structure
                    // ID, Name, Companies, SalesPersonID, Type, Participants, Dates(comma sep), Address, Notes, Lunch(Y/N), Snack(Y/N), SPH Link
                    
                    // Helper to safely get value
                    const getVal = (idx) => (dataRow[idx] !== undefined ? String(dataRow[idx]).trim() : '');

                    console.log('DEBUG - Import Row:', dataRow);
                    console.log('DEBUG - Date Value (index 8):', getVal(8));

                    if(getVal(0)) $('input[name="project_id"]').val(getVal(0));
                    if(getVal(1)) $('input[name="nama_project"]').val(getVal(1));
                    
                    // Company Handling (Multiple)
                    if(getVal(2)) {
                        const companies = getVal(2).split(',').map(c => c.trim()).filter(c => c !== '');
                        const list = document.getElementById('companyList');
                        list.innerHTML = ''; // Clear default input
                        
                        companies.forEach((comp, idx) => {
                            const div = document.createElement('div');
                            div.className = 'input-group mb-2 company-item';
                            const required = idx === 0 ? 'required' : '';
                            const removeBtn = idx > 0 ? '<button type="button" class="btn btn-outline-danger" onclick="removeCompanyItem(this)"><i class="fas fa-times"></i></button>' : '';
                            
                            div.innerHTML = '<span class="input-group-text"><i class="fas fa-building"></i></span>' +
                                            '<input type="text" name="company_names[]" class="form-control" placeholder="Company Name" value="' + comp + '" ' + required + '>' +
                                            removeBtn;
                            list.appendChild(div);
                        });
                    }

                    // Sales Person ID might need select2 update if used
                    if(getVal(3)) {
                        const val = getVal(3);
                        const select = $('select[name="sales_person_id"]');
                        
                        // Try setting by value (ID) first
                        select.val(val);
                        
                        // If not set (value didn't match an ID), try matching by text
                        if (!select.val()) {
                            select.find('option').each(function() {
                                if ($(this).text().trim().toLowerCase() === val.toLowerCase()) {
                                    select.val($(this).val());
                                    return false; // break loop
                                }
                            });
                        }
                        
                        select.trigger('change');
                    }
                    // Project Type (index 4)
                    if(getVal(4)) {
                        const projectType = getVal(4).toLowerCase();
                        if(projectType === 'walk_in' || projectType === 'walkin') {
                            $('#type_walkin').prop('checked', true);
                        } else {
                            $('#type_onsite').prop('checked', true);
                        }
                        toggleClinicLocation(); // Trigger toggle
                    }
                    
                    // Clinic Location (index 5)
                    if(getVal(5)) $('#clinic_location').val(getVal(5));
                    
                    if(getVal(6)) $('textarea[name="jenis_pemeriksaan"]').val(getVal(6));
                    if(getVal(7)) $('input[name="total_peserta"]').val(getVal(7));
                    
                    // Date handling
                    const excelDateToJSDate = (serial) => {
                       const utc_days  = Math.floor(serial - 25569);
                       const utc_value = utc_days * 86400;                                        
                       const date_info = new Date(utc_value * 1000);
                    
                       const fractional_day = serial - Math.floor(serial) + 0.0000001;
                    
                       let total_seconds = Math.floor(86400 * fractional_day);
                    
                       const seconds = total_seconds % 60;
                    
                       total_seconds -= seconds;
                    
                       const hours = Math.floor(total_seconds / (60 * 60));
                       const minutes = Math.floor(total_seconds / 60) % 60;
                    
                       return new Date(date_info.getFullYear(), date_info.getMonth(), date_info.getDate(), hours, minutes, seconds);
                    };

                    const formatDate = (date) => {
                        let d = new Date(date),
                            month = '' + (d.getMonth() + 1),
                            day = '' + d.getDate(),
                            year = d.getFullYear();
                    
                        if (month.length < 2) 
                            month = '0' + month;
                        if (day.length < 2) 
                            day = '0' + day;
                    
                        return [year, month, day].join('-');
                    }

                    if(getVal(8)) {
                         // Initialize flatpickr if needed or just set value
                         const dateInput = document.getElementById('tanggal_mcu');
                         if(dateInput && dateInput._flatpickr) {
                             let dateStr = getVal(8).trim();
                             
                             // Handle Excel Serial Date
                             if (!isNaN(dateStr) && parseFloat(dateStr) > 25569) {
                                 const jsDate = excelDateToJSDate(parseFloat(dateStr));
                                 dateStr = formatDate(jsDate);
                             }

                             if(dateStr.length < 3) {
                                 console.log('Skipping invalid/short date string:', dateStr);
                             } else {
                                 // Validate Holidays and Weekends
                                 let datesToSet = [];
                                 if (dateStr.includes(',')) {
                                     datesToSet = dateStr.split(',').map(d => d.trim());
                                 } else {
                                     datesToSet = [dateStr];
                                 }
                                 
                                 const invalidDates = [];
                                 const holidays = window.projectHolidays || [];
                                 
                                 datesToSet.forEach(d => {
                                     const checkDate = new Date(d);
                                     if (!isNaN(checkDate.getTime())) {
                                          const day = checkDate.getDay();
                                          const y = checkDate.getFullYear();
                                          const m = String(checkDate.getMonth() + 1).padStart(2, '0');
                                          const da = String(checkDate.getDate()).padStart(2, '0');
                                          const isoDate = `${y}-${m}-${da}`;
                                          
                                          if (day === 0 || day === 6) {
                                              invalidDates.push(`${d} (Weekend)`);
                                          } else if (holidays.includes(isoDate)) {
                                              invalidDates.push(`${d} (Holiday)`);
                                          }
                                     }
                                 });

                                 if (invalidDates.length > 0) {
                                     Swal.fire({
                                         icon: 'warning',
                                         title: 'Tanggal Tidak Tersedia',
                                         html: 'Beberapa tanggal tidak dapat dipilih karena merupakan hari libur atau akhir pekan:<br><br><b>' + invalidDates.join('<br>') + '</b><br><br>Tanggal tersebut akan dilewati otomatis.',
                                     });
                                 }
                                 
                                 try {
                                     dateInput._flatpickr.setDate(datesToSet);
                                     
                                     // Verify if date was actually set
                                     if (dateInput._flatpickr.selectedDates.length === 0) {
                                         throw new Error("Date parsing failed");
                                     }
                                 } catch (e) {
                                     console.error("Flatpickr setDate failed:", e);
                                 }
                             }
                         } else if (dateInput) {
                             dateInput.value = getVal(8);
                         }
                    }
                    
                    if(getVal(9)) $('textarea[name="alamat"]').val(getVal(9)); // Was 7, now 9
                    if(getVal(10)) $('textarea[name="notes"]').val(getVal(10)); // Was 8, now 10
                    
                    // Radio Buttons mapping
                    const setRadio = (name, val) => {
                        val = val.toLowerCase();
                        if(val === 'ya' || val === 'yes' || val === 'y') {
                            $(`input[name="${name}"][value="Ya"]`).prop('checked', true).trigger('change');
                        } else {
                            $(`input[name="${name}"][value="Tidak"]`).prop('checked', true).trigger('change');
                        }
                        // Trigger toggle to show/hide notes/budget
                        if (typeof window.toggleConsumptionNotes === 'function') {
                            window.toggleConsumptionNotes(name);
                        }
                    };
                    
                    if(getVal(11)) setRadio('lunch', getVal(11));
                    if(getVal(12)) setRadio('snack', getVal(12));
                    
                    // SPH Link
                    if(getVal(13)) $('input[name="sph_file"]').val(getVal(13));

                    // Referral No SPH
                    if(getVal(14)) $('input[name="sph_number"]').val(getVal(14));

                    // Budget
                    if(getVal(15)) $('input[name="lunch_budget"]').val(getVal(15));
                    if(getVal(16)) $('input[name="snack_budget"]').val(getVal(16));

                    // Helper for Consumption Items
                    const setConsumptionItems = (type, val) => {
                        if (!val) return;
                        
                        // Handle potential quote wrapping from CSV
                        val = val.replace(/^"|"$/g, '');
                        
                        const items = val.split('|');
                        const listContainer = document.getElementById(`${type}_items_list`);
                        if (!listContainer) return;

                        // Clear all rows except the first one to reset
                        const existingRows = listContainer.querySelectorAll(`.${type}-item-row`);
                        for (let i = 1; i < existingRows.length; i++) {
                            existingRows[i].remove();
                        }
                        
                        // Reset first row inputs
                        const firstRow = listContainer.querySelector(`.${type}-item-row`);
                        if (firstRow) {
                            firstRow.querySelector(`input[name="${type}_item_name[]"]`).value = '';
                            firstRow.querySelector(`input[name="${type}_item_qty[]"]`).value = '';
                        }

                        // Process items
                        items.forEach((itemStr, index) => {
                            const parts = itemStr.split(':');
                            const itemName = parts[0] ? parts[0].trim() : '';
                            const itemQty = parts[1] ? parts[1].trim() : '';
                            
                            if (index === 0) {
                                // Update first row
                                if (firstRow) {
                                    firstRow.querySelector(`input[name="${type}_item_name[]"]`).value = itemName;
                                    firstRow.querySelector(`input[name="${type}_item_qty[]"]`).value = itemQty;
                                }
                            } else {
                                // Add new row
                                if (typeof window.addConsumptionItem === 'function') {
                                    window.addConsumptionItem(type);
                                    // Get the last added row
                                    const newRows = listContainer.querySelectorAll(`.${type}-item-row`);
                                    const lastRow = newRows[newRows.length - 1];
                                    if (lastRow) {
                                        lastRow.querySelector(`input[name="${type}_item_name[]"]`).value = itemName;
                                        lastRow.querySelector(`input[name="${type}_item_qty[]"]`).value = itemQty;
                                    }
                                }
                            }
                        });
                    };

                    // Lunch Items
                    if(getVal(17)) setConsumptionItems('lunch', getVal(17));
                    // Snack Items
                    if(getVal(18)) setConsumptionItems('snack', getVal(18));

                    // Final check for Lark buttons visibility
                    if (typeof window.checkLarkRequirement === 'function') {
                        window.checkLarkRequirement();
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Data Imported',
                        text: 'Form has been populated from the Excel file.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                     Swal.fire('Error', 'File appears to be empty or invalid format', 'error');
                }
            };

            reader.readAsBinaryString(file);
            
            // Reset input
            input.value = '';
        }
        </script>
        <?php endif; ?>
        
        <div class="row g-4">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Project Info Card -->
                <div class="card card-medical mb-4">
                    <div class="card-header card-header-medical">
                        <h5 class="m-0 fw-bold text-bumame"><i class="fas fa-info-circle me-2"></i>Project Information</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-muted">Project ID <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                    <input type="text" name="project_id" class="form-control" placeholder="Enter Project ID" required value="<?php echo ViewHelper::getValue('project_id', $form_data); ?>" <?php echo $is_edit ? 'readonly' : ''; ?>>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-muted">Project Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-project-diagram"></i></span>
                                    <input type="text" name="nama_project" class="form-control" placeholder="Enter Project Name" required value="<?php echo ViewHelper::getValue('nama_project', $form_data); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-muted">Project Type</label>
                                <div class="toggle-group">
                                    <input type="radio" name="project_type" id="type_onsite" value="on_site" <?php echo (ViewHelper::getValue('project_type', $form_data) != 'walk_in') ? 'checked' : ''; ?> onchange="toggleClinicLocation()">
                                    <label for="type_onsite"><i class="fas fa-building"></i> On-Site</label>
                                    
                                    <input type="radio" name="project_type" id="type_walkin" value="walk_in" <?php echo (ViewHelper::getValue('project_type', $form_data) == 'walk_in') ? 'checked' : ''; ?> onchange="toggleClinicLocation()">
                                    <label for="type_walkin"><i class="fas fa-walking"></i> Walk-In</label>
                                </div>
                            </div>
                            <div class="col-md-6" id="clinic_location_container" style="display: none;">
                                <label class="form-label fw-bold small text-uppercase text-muted">Clinic Location <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-hospital"></i></span>
                                    <input type="text" name="clinic_location" id="clinic_location" class="form-control" placeholder="Enter Clinic Name/Location" value="<?php echo ViewHelper::getValue('clinic_location', $form_data); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-muted">Company Name <span class="text-danger">*</span></label>
                                <div id="companyList">
                                    <?php
                                        $companies = [];
                                        // Check for array input (from failed validation/POST)
                                        if (isset($form_data['company_names']) && is_array($form_data['company_names'])) {
                                            $companies = $form_data['company_names'];
                                        } 
                                        // Check for legacy string input (from DB or legacy POST)
                                        elseif (isset($form_data['company_name'])) {
                                            $companies = explode(',', $form_data['company_name']);
                                        }
                                        
                                        // Clean and filter
                                        $companies = array_filter(array_map('trim', $companies));
                                        
                                        if (empty($companies)) {
                                            $companies = [''];
                                        }
                                        foreach ($companies as $idx => $comp):
                                    ?>
                                    <div class="input-group mb-2 company-item">
                                        <span class="input-group-text"><i class="fas fa-building"></i></span>
                                        <input type="text" name="company_names[]" class="form-control" placeholder="Company Name" value="<?php echo htmlspecialchars($comp); ?>" <?php echo $idx === 0 ? 'required' : ''; ?>>
                                        <?php if ($idx > 0): ?>
                                            <button type="button" class="btn btn-outline-danger" onclick="removeCompanyItem(this)"><i class="fas fa-times"></i></button>
                                        <?php endif; ?>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="addCompanyItem()"><i class="fas fa-plus me-1"></i>Add Company</button>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-muted">Sales Person <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user-tie"></i></span>
                                    <select name="sales_person_id" class="form-select" required>
                                        <option value="">Select Sales</option>
                                        <?php foreach($sales_users as $sales): ?>
                                            <option value="<?php echo $sales['id']; ?>" <?php echo ViewHelper::isSelected('sales_person_id', $sales['id'], $form_data); ?>><?php echo $sales['sales_name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Jenis Pemeriksaan <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-stethoscope"></i></span>
                                <textarea name="jenis_pemeriksaan" class="form-control" rows="2" placeholder="Describe examination types..." required><?php echo ViewHelper::getValue('jenis_pemeriksaan', $form_data); ?></textarea>
                            </div>
                        </div>
                        <div class="row mb-3">
                             <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-muted">Total Peserta <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-users"></i></span>
                                    <input type="number" name="total_peserta" class="form-control" min="1" placeholder="0" required value="<?php echo ViewHelper::getValue('total_peserta', $form_data); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-muted">Tanggal MCU <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                    <input type="text" name="tanggal_mcu" id="tanggal_mcu" class="form-control datepicker" placeholder="Select dates" required value="<?php echo htmlspecialchars($date_value); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Alamat / Lokasi <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                <textarea name="alamat" class="form-control" rows="2" placeholder="Location address..." required><?php echo ViewHelper::getValue('alamat', $form_data); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Requirements Card -->
                <div class="card card-medical mb-4">
                    <div class="card-header card-header-medical">
                        <h5 class="m-0 fw-bold text-bumame"><i class="fas fa-clipboard-check me-2"></i>Requirements</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-muted mb-2">Foto Peserta <span class="text-danger">*</span></label>
                                <div class="toggle-group">
                                    <input type="radio" name="foto_peserta" id="foto_tidak" value="Tidak" <?php echo (ViewHelper::getValue('foto_peserta', $form_data) != 'Ya') ? 'checked' : ''; ?> required>
                                    <label for="foto_tidak"><i class="fas fa-times"></i> Tidak</label>
                                    
                                    <input type="radio" name="foto_peserta" id="foto_ya" value="Ya" <?php echo (ViewHelper::getValue('foto_peserta', $form_data) == 'Ya') ? 'checked' : ''; ?>>
                                    <label for="foto_ya"><i class="fas fa-camera"></i> Ya</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-muted mb-2">Header/Footer <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-file-alt"></i></span>
                                    <select id="header_footer_select" class="form-select" onchange="toggleHeaderFooter()" required>
                                        <option value="Bumame" <?php echo ($hf_select == 'Bumame') ? 'selected' : ''; ?>>Bumame</option>
                                        <option value="White Label" <?php echo ($hf_select == 'White Label') ? 'selected' : ''; ?>>White Label</option>
                                        <option value="Co-Branding" <?php echo ($hf_select == 'Co-Branding') ? 'selected' : ''; ?>>Co-Branding</option>
                                    </select>
                                </div>
                                <!-- Hidden input for the final value sent to DB -->
                                <input type="hidden" name="header_footer" id="header_footer_final">
                                
                                <div id="hf_notes_container" class="mt-2 <?php echo ($hf_select == 'Bumame') ? 'd-none' : ''; ?>">
                                    <input type="text" id="header_footer_notes" class="form-control" placeholder="Specify Name (e.g. Logo/Brand Name)" value="<?php echo htmlspecialchars($hf_notes); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-muted mb-2">Makan Siang <span class="text-danger">*</span></label>
                                <div class="d-flex flex-column">
                                    <div class="row align-items-center mb-2 g-2">
                                        <div class="col-auto">
                                            <div class="toggle-group">
                                                <input type="radio" name="lunch" id="lunch_tidak" value="Tidak" <?php echo (ViewHelper::getValue('lunch', $form_data) != 'Ya') ? 'checked' : ''; ?> onclick="toggleConsumptionNotes('lunch')" required>
                                                <label for="lunch_tidak"><i class="fas fa-times"></i> Tidak</label>
                                                
                                                <input type="radio" name="lunch" id="lunch_ya" value="Ya" <?php echo (ViewHelper::getValue('lunch', $form_data) == 'Ya') ? 'checked' : ''; ?> onclick="toggleConsumptionNotes('lunch')">
                                                <label for="lunch_ya"><i class="fas fa-utensils"></i> Ya</label>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div id="lunch_budget_container" class="<?php echo (ViewHelper::getValue('lunch', $form_data) == 'Ya') ? '' : 'd-none'; ?>">
                                                <label class="form-label small text-muted mb-0">Budget (Rp)</label>
                                                <input type="text" name="lunch_budget" class="form-control form-control-sm" value="<?php $v=ViewHelper::getValue('lunch_budget', $form_data); echo ($v != 0 && $v !== '0' && $v > 0) ? number_format((float)$v, 0, ',', '.') : ''; ?>" onkeyup="formatRupiah(this)" placeholder="0" <?php echo (ViewHelper::getValue('lunch', $form_data) == 'Ya') ? 'required' : ''; ?>>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div id="lunch_notes_container" class="<?php echo (ViewHelper::getValue('lunch', $form_data) == 'Ya') ? '' : 'd-none'; ?>">
                                        <!-- Hidden textarea for backward compatibility -->
                                        <div class="mb-2 d-none">
                                            <textarea name="lunch_notes" class="form-control" rows="2" placeholder="Lunch details/menu..."><?php echo ViewHelper::getValue('lunch_notes', $form_data); ?></textarea>
                                        </div>
                                        
                                        <!-- Dynamic Item List -->
                                        <div id="lunch_items_list">
                                            <?php 
                                            $lunch_items = isset($form_data['lunch_items']) ? json_decode($form_data['lunch_items'], true) : (isset($project_data['lunch_items']) ? json_decode($project_data['lunch_items'], true) : []);
                                            if (empty($lunch_items)) {
                                                $lunch_items = [['item' => '', 'qty' => '']];
                                            }
                                            foreach ($lunch_items as $index => $item): 
                                            ?>
                                            <div class="row g-2 mb-2 lunch-item-row">
                                                <div class="col-7">
                                                    <?php if($index === 0): ?><label id="lunch_item_label" class="form-label small text-muted mb-0">Item <?php echo (ViewHelper::getValue('lunch', $form_data) == 'Ya') ? '<span class="text-danger">*</span>' : ''; ?></label><?php endif; ?>
                                                    <input type="text" name="lunch_item_name[]" class="form-control" value="<?php echo htmlspecialchars($item['item'] ?? ''); ?>" placeholder="Item name" required>
                                                </div>
                                                <div class="col-5">
                                                    <?php if($index === 0): ?><label id="lunch_qty_label" class="form-label small text-muted mb-0">Qty <?php echo (ViewHelper::getValue('lunch', $form_data) == 'Ya') ? '<span class="text-danger">*</span>' : ''; ?></label><?php endif; ?>
                                                    <div class="input-group">
                                                        <input type="number" name="lunch_item_qty[]" class="form-control" value="<?php echo htmlspecialchars($item['qty'] ?? ''); ?>" placeholder="Qty" min="1" required>
                                                        <button type="button" class="btn btn-outline-danger" onclick="removeConsumptionItem(this)" <?php echo ($index === 0) ? 'disabled' : ''; ?>><i class="fas fa-times"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <button type="button" class="btn btn-link btn-sm text-decoration-none p-0" onclick="addConsumptionItem('lunch')">
                                            <i class="fas fa-plus-circle"></i> Add Item
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase text-muted mb-2">Snack <span class="text-danger">*</span></label>
                                <div class="d-flex flex-column">
                                    <div class="row align-items-center mb-2 g-2">
                                        <div class="col-auto">
                                            <div class="toggle-group">
                                                <input type="radio" name="snack" id="snack_tidak" value="Tidak" <?php echo (ViewHelper::getValue('snack', $form_data) != 'Ya') ? 'checked' : ''; ?> onclick="toggleConsumptionNotes('snack')" required>
                                                <label for="snack_tidak"><i class="fas fa-times"></i> Tidak</label>
                                                
                                                <input type="radio" name="snack" id="snack_ya" value="Ya" <?php echo (ViewHelper::getValue('snack', $form_data) == 'Ya') ? 'checked' : ''; ?> onclick="toggleConsumptionNotes('snack')">
                                                <label for="snack_ya"><i class="fas fa-cookie-bite"></i> Ya</label>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div id="snack_budget_container" class="<?php echo (ViewHelper::getValue('snack', $form_data) == 'Ya') ? '' : 'd-none'; ?>">
                                                <label class="form-label small text-muted mb-0">Budget (Rp)</label>
                                                <input type="text" name="snack_budget" class="form-control form-control-sm" value="<?php $v=ViewHelper::getValue('snack_budget', $form_data); echo ($v != 0 && $v !== '0' && $v > 0) ? number_format((float)$v, 0, ',', '.') : ''; ?>" onkeyup="formatRupiah(this)" placeholder="0" <?php echo (ViewHelper::getValue('snack', $form_data) == 'Ya') ? 'required' : ''; ?>>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div id="snack_notes_container" class="<?php echo (ViewHelper::getValue('snack', $form_data) == 'Ya') ? '' : 'd-none'; ?>">
                                        <!-- Hidden textarea for backward compatibility -->
                                        <div class="mb-2 d-none">
                                            <textarea name="snack_notes" class="form-control" rows="2" placeholder="Snack details..."><?php echo ViewHelper::getValue('snack_notes', $form_data); ?></textarea>
                                        </div>
                                        
                                        <!-- Dynamic Item List -->
                                        <div id="snack_items_list">
                                            <?php 
                                            $snack_items = isset($form_data['snack_items']) ? json_decode($form_data['snack_items'], true) : (isset($project_data['snack_items']) ? json_decode($project_data['snack_items'], true) : []);
                                            if (empty($snack_items)) {
                                                $snack_items = [['item' => '', 'qty' => '']];
                                            }
                                            foreach ($snack_items as $index => $item): 
                                            ?>
                                            <div class="row g-2 mb-2 snack-item-row">
                                                <div class="col-7">
                                                    <?php if($index === 0): ?><label id="snack_item_label" class="form-label small text-muted mb-0">Item <?php echo (ViewHelper::getValue('snack', $form_data) == 'Ya') ? '<span class="text-danger">*</span>' : ''; ?></label><?php endif; ?>
                                                    <input type="text" name="snack_item_name[]" class="form-control" value="<?php echo htmlspecialchars($item['item'] ?? ''); ?>" placeholder="Item name" required>
                                                </div>
                                                <div class="col-5">
                                                    <?php if($index === 0): ?><label id="snack_qty_label" class="form-label small text-muted mb-0">Qty <?php echo (ViewHelper::getValue('snack', $form_data) == 'Ya') ? '<span class="text-danger">*</span>' : ''; ?></label><?php endif; ?>
                                                    <div class="input-group">
                                                        <input type="number" name="snack_item_qty[]" class="form-control" value="<?php echo htmlspecialchars($item['qty'] ?? ''); ?>" placeholder="Qty" min="1" required>
                                                        <button type="button" class="btn btn-outline-danger" onclick="removeConsumptionItem(this)" <?php echo ($index === 0) ? 'disabled' : ''; ?>><i class="fas fa-times"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <button type="button" class="btn btn-link btn-sm text-decoration-none p-0" onclick="addConsumptionItem('snack')">
                                            <i class="fas fa-plus-circle"></i> Add Item
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-4">
                <!-- File Upload Card -->
                <div class="card card-medical mb-4">
                    <div class="card-header card-header-medical">
                        <h5 class="m-0 fw-bold text-bumame"><i class="fas fa-file-upload me-2"></i>Files & Submit</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-uppercase text-muted" id="sph_link_label">SPH Link (Google Drive) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fab fa-google-drive"></i></span>
                                <input type="url" name="sph_file" id="sph_file" class="form-control" placeholder="https://drive.google.com/..." pattern="https?://.+" <?php echo $is_edit ? '' : 'required'; ?> value="<?php echo htmlspecialchars(ViewHelper::getValue('sph_file', $form_data)); ?>">
                            </div>
                            <?php if($is_edit && !empty($project_data['sph_file'])): ?>
                                <div class="mt-2 p-2 bg-light rounded border">
                                    <small class="d-block text-muted mb-1">Current Link:</small>
                                    <a href="<?php echo htmlspecialchars($project_data['sph_file']); ?>" target="_blank" class="text-decoration-none fw-bold text-bumame">
                                        <i class="fas fa-link me-1"></i><?php echo htmlspecialchars($project_data['sph_file']); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-uppercase text-muted" id="sph_number_label">Referral No SPH <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                <input type="text" name="sph_number" id="sph_number" class="form-control" placeholder="No. SPH" required value="<?php echo htmlspecialchars(ViewHelper::getValue('sph_number', $form_data)); ?>">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-uppercase text-muted">Internal Notes</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-sticky-note"></i></span>
                                <textarea name="notes" class="form-control" rows="4" placeholder="Optional notes for internal team..."><?php echo ViewHelper::getValue('notes', $form_data); ?></textarea>
                            </div>
                        </div>
                        <!-- Lark Integration (Hidden by default) -->
                        <div id="larkLinkContainer" class="mb-3 text-center d-none">
                            <div class="d-flex justify-content-center gap-2">
                                <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3" onclick="showLarkTemplate()">
                                    <i class="fas fa-bullhorn me-1"></i>Template
                                </button>
                                <a href="<?php echo $lark_link ?? 'https://www.larksuite.com'; ?>" id="openLarkBtn" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="markLarkClicked()">
                                    <i class="fas fa-external-link-alt me-1"></i>Open Lark
                                </a>
                            </div>
                            <small class="text-muted d-block mt-2" style="font-size: 0.75rem;">
                                <i class="fas fa-info-circle me-1"></i>Klik Open Lark untuk melanjutkan
                            </small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" id="submitProjectBtn" class="btn btn-lg fw-bold text-white shadow-sm" style="background-color: var(--bumame-blue); border-radius: 50px;">
                                <i class="fas fa-paper-plane me-2"></i><?php echo $btn_text; ?>
                            </button>
                            <a href="index.php?page=projects_list" class="btn btn-light btn-lg fw-bold text-secondary shadow-sm" style="border-radius: 50px;">
                                Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php include 'lark_template_modal.php'; ?>
<?php include '../views/layouts/footer.php'; ?>

<script src="js/project_form.js"></script>
<script>
    function addCompanyItem() {
        const list = document.getElementById('companyList');
        const div = document.createElement('div');
        div.className = 'input-group mb-2 company-item';
        div.innerHTML = '<span class="input-group-text"><i class="fas fa-building"></i></span>' +
                        '<input type="text" name="company_names[]" class="form-control" placeholder="Company Name">' +
                        '<button type="button" class="btn btn-outline-danger" onclick="removeCompanyItem(this)"><i class="fas fa-times"></i></button>';
        list.appendChild(div);
    }
    function removeCompanyItem(btn) {
        const item = btn.closest('.company-item');
        if (item) {
            item.remove();
        }
    }
    function toggleClinicLocation() {
        const walkInRadio = document.getElementById('type_walkin');
        const clinicContainer = document.getElementById('clinic_location_container');
        const clinicInput = document.getElementById('clinic_location');
        
        if (walkInRadio && walkInRadio.checked) {
            clinicContainer.style.display = 'block';
            clinicInput.setAttribute('required', 'required');
        } else {
            clinicContainer.style.display = 'none';
            clinicInput.removeAttribute('required');
        }
    }

    // Dynamic Consumption Items
    function addConsumptionItem(type) {
        const listId = type + '_items_list';
        const list = document.getElementById(listId);
        const row = document.createElement('div');
        row.className = 'row g-2 mb-2 ' + type + '-item-row';
        row.innerHTML = `
            <div class="col-7">
                <input type="text" name="${type}_item_name[]" class="form-control" placeholder="Item name" required>
            </div>
            <div class="col-5">
                <div class="input-group">
                    <input type="number" name="${type}_item_qty[]" class="form-control" placeholder="Qty" min="1" required>
                    <button type="button" class="btn btn-outline-danger" onclick="removeConsumptionItem(this)"><i class="fas fa-times"></i></button>
                </div>
            </div>
        `;
        list.appendChild(row);
    }

    function removeConsumptionItem(btn) {
        const row = btn.closest('.row');
        if (row) {
            row.remove();
        }
    }

    // Override toggleConsumptionNotes to handle budget container visibility and required attributes
    const originalToggleConsumptionNotes = window.toggleConsumptionNotes;
    window.toggleConsumptionNotes = function(type) {
        const isYes = document.getElementById(type + '_ya').checked;
        const notesContainer = document.getElementById(type + '_notes_container');
        const budgetContainer = document.getElementById(type + '_budget_container');
        const budgetInput = document.querySelector(`input[name="${type}_budget"]`);
        const itemLabel = document.getElementById(type + '_item_label');
        const qtyLabel = document.getElementById(type + '_qty_label');
        
        if (notesContainer) {
            if (isYes) {
                notesContainer.classList.remove('d-none');
                // Set required for inputs inside notesContainer
                const inputs = notesContainer.querySelectorAll('input');
                inputs.forEach(input => {
                     // Check if it's item name or qty
                     if (input.name.includes('item_name') || input.name.includes('item_qty')) {
                         input.setAttribute('required', 'required');
                         if(input.name.includes('item_qty')) input.setAttribute('min', '1');
                     }
                });
                if(itemLabel) itemLabel.innerHTML = 'Item <span class="text-danger">*</span>';
                if(qtyLabel) qtyLabel.innerHTML = 'Qty <span class="text-danger">*</span>';
            } else {
                notesContainer.classList.add('d-none');
                // Remove required
                 const inputs = notesContainer.querySelectorAll('input');
                 inputs.forEach(input => input.removeAttribute('required'));
                 if(itemLabel) itemLabel.innerHTML = 'Item';
                 if(qtyLabel) qtyLabel.innerHTML = 'Qty';
            }
        }
        
        if (budgetContainer) {
             if (isYes) {
                budgetContainer.classList.remove('d-none');
                if(budgetInput) budgetInput.setAttribute('required', 'required');
            } else {
                budgetContainer.classList.add('d-none');
                if(budgetInput) budgetInput.removeAttribute('required');
            }
        }
        
        // Also call original if exists (though we pretty much replaced its logic for this part)
        if (typeof originalToggleConsumptionNotes === 'function') {
            // originalToggleConsumptionNotes(type); 
        }
    };

    function formatRupiah(element) {
        let value = element.value.replace(/[^,\d]/g, '').toString();
        let split = value.split(',');
        let sisa = split[0].length % 3;
        let rupiah = split[0].substr(0, sisa);
        let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        element.value = rupiah;
    }

    document.addEventListener("DOMContentLoaded", function() {
        // Expose holidays for import validation
        window.projectHolidays = <?php echo json_encode($holidays ?? []); ?>;

        // Initialize Configuration
        const config = {
            holidays: window.projectHolidays,
            minDate: "<?php echo isset($minDate) ? $minDate : 'today'; ?>",
            dateValue: "<?php echo $date_value ? str_replace(', ', ', ', $date_value) : ''; ?>"
        };
        
        // Flatten dateValue manually if needed since JS expects "d M Y, d M Y" format string or array
        // My external JS expects dateValue string to split by ", "
        
        initProjectForm(config);

        // Initialize toggle state
        toggleClinicLocation();
    });
    
    // Toggle Clinic Location and SPH Requirements
    function toggleClinicLocation() {
        const isWalkIn = $('#type_walkin').is(':checked');
        const clinicLocationGroup = $('#clinic_location_container'); // Fixed: was clinic_location_group
        const clinicLocationInput = $('#clinic_location');
        const sphFileInput = $('#sph_file');
        const sphNumberInput = $('#sph_number');
        const sphLinkLabel = $('#sph_link_label');
        const sphNumberLabel = $('#sph_number_label');
        
        if(isWalkIn) {
            // Show clinic location
            clinicLocationGroup.show();
            clinicLocationInput.prop('required', true);
            
            // Make SPH fields optional for Walk-In
            sphFileInput.prop('required', false);
            sphNumberInput.prop('required', false);
            
            // Update labels
            sphLinkLabel.html('SPH Link (Google Drive) <span class="text-muted small">(Optional for Walk-In)</span>');
            sphNumberLabel.html('Referral No SPH <span class="text-muted small">(Optional for Walk-In)</span>');
        } else {
            // Hide clinic location
            clinicLocationGroup.hide();
            clinicLocationInput.prop('required', false);
            clinicLocationInput.val(''); // Clear value
            
            // Make SPH fields required for On-Site
            <?php if (!$is_edit): ?>
            sphFileInput.prop('required', true);
            <?php endif; ?>
            sphNumberInput.prop('required', true);
            
            // Restore labels
            sphLinkLabel.html('SPH Link (Google Drive) <span class="text-danger">*</span>');
            sphNumberLabel.html('Referral No SPH <span class="text-danger">*</span>');
        }
    }
</script>

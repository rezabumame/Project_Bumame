
<!-- Lark Template Modal -->
<div class="modal fade" id="larkTemplateModal" tabindex="-1" aria-hidden="true" style="z-index: 1070;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" style="color: #204EAB;">Template Lark</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="form-floating mb-3">
                    <textarea class="form-control bg-light" id="larkTemplateText" style="height: 350px; font-family: monospace; font-size: 0.9rem; white-space: pre-wrap;" readonly></textarea>
                    <label for="larkTemplateText">Template Text</label>
                </div>
                <div class="alert alert-info small d-flex align-items-center mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    <div>Text has been generated based on the current form data. Click Copy to use.</div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success rounded-pill px-4" onclick="copyLarkTemplate()">
                    <i class="fas fa-copy me-2"></i>Copy to Clipboard
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Helper to format date for Lark using Smart Date logic
    function formatLarkDate(dateStr) {
        if (!dateStr || dateStr === '-') return '-';
        
        // Clean potential JSON residues
        dateStr = dateStr.replace(/[\[\]"]/g, '');
        
        // If it's already formatted (contains month names in Indonesian/English), return as is
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        const hasMonthName = months.some(m => dateStr.toLowerCase().includes(m.toLowerCase()));
        if (hasMonthName && dateStr.includes(' ')) return dateStr;
        
        // Handle multiple dates (comma separated)
        let dates = dateStr.split(',').map(d => d.trim()).filter(d => d);
        if (dates.length === 0) return '-';
        
        // Sort dates chronologically
        dates.sort((a, b) => new Date(a) - new Date(b));
        
        const shortMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        
        let groups = [];
        if (dates.length > 0) {
            let currentGroup = [dates[0]];
            for (let i = 1; i < dates.length; i++) {
                let prev = new Date(dates[i-1]);
                let curr = new Date(dates[i]);
                
                // Reset time to midnight for accurate day comparison
                prev.setHours(0, 0, 0, 0);
                curr.setHours(0, 0, 0, 0);
                
                let diffTime = Math.abs(curr - prev);
                let diffDays = Math.round(diffTime / (1000 * 60 * 60 * 24));
                
                if (diffDays === 1) {
                    currentGroup.push(dates[i]);
                } else {
                    groups.push(currentGroup);
                    currentGroup = [dates[i]];
                }
            }
            groups.push(currentGroup);
        }
        
        let outputParts = [];
        let firstDate = new Date(dates[0]);
        let firstYear = firstDate.getFullYear();
        let sameYear = dates.every(d => {
            let dt = new Date(d);
            return !isNaN(dt.getTime()) && dt.getFullYear() === firstYear;
        });
        
        groups.forEach(group => {
            let start = new Date(group[0]);
            let end = new Date(group[group.length - 1]);
            
            if (isNaN(start.getTime()) || isNaN(end.getTime())) {
                outputParts.push(group[0]); // Fallback to raw if invalid
                return;
            }

            let startD = start.getDate();
            let startM = shortMonths[start.getMonth()];
            let startY = start.getFullYear();
            
            let endD = end.getDate();
            let endM = shortMonths[end.getMonth()];
            let endY = end.getFullYear();
            
            if (group.length === 1) {
                if (sameYear) {
                    outputParts.push(`${startD} ${startM}`);
                } else {
                    outputParts.push(`${startD} ${startM} ${startY}`);
                }
            } else {
                if (sameYear) {
                    if (startM === endM) {
                        outputParts.push(`${startD}-${endD} ${startM}`);
                    } else {
                        outputParts.push(`${startD} ${startM} - ${endD} ${endM}`);
                    }
                } else {
                    if (startY === endY) {
                        outputParts.push(`${startD} ${startM} - ${endD} ${endM} ${endY}`);
                    } else {
                        outputParts.push(`${startD} ${startM} ${startY} - ${endD} ${endM} ${endY}`);
                    }
                }
            }
        });
        
        let finalString = outputParts.join(', ');
        if (sameYear && !isNaN(firstYear)) {
            finalString += ' ' + firstYear;
        }
        return finalString;
    }

    function generateLarkTemplate() {
        let projectName = $('input[name="nama_project"]').val();
        let mcuDate = $('input[name="tanggal_mcu"]').val() || '-';
        
        // Sales Name logic: Try input first, then selected option from dropdown
        let salesName = $('input[name="sales_name"]').val();
        if (!salesName || salesName === '-') {
            const selectedSales = $('select[name="sales_person_id"] option:selected').text();
            if (selectedSales && selectedSales !== 'Select Sales') {
                salesName = selectedSales;
            }
        }

        let lunchEnabled = $('input[name="lunch"]:checked').val() === 'Ya';
        let snackEnabled = $('input[name="snack"]:checked').val() === 'Ya';
        
        let lunchBudget = $('input[name="lunch_budget"]').val() || '0';
        let snackBudget = $('input[name="snack_budget"]').val() || '0';
        
        let lunchNames = $('input[name="lunch_item_name[]"]').map(function(){ return $(this).val(); }).get();
        let lunchQtys = $('input[name="lunch_item_qty[]"]').map(function(){ return $(this).val(); }).get();
        
        let snackNames = $('input[name="snack_item_name[]"]').map(function(){ return $(this).val(); }).get();
        let snackQtys = $('input[name="snack_item_qty[]"]').map(function(){ return $(this).val(); }).get();

        // Support for list.php Vendor/Consumption Modal context
        if (!projectName && ($('#assignVendorModal').is(':visible') || $('#approveConsumptionModal').is(':visible'))) {
            const modal = $('#assignVendorModal').is(':visible') ? $('#assignVendorModal') : $('#approveConsumptionModal');
            projectName = modal.data('project-name');
            mcuDate = modal.data('mcu-date');
            salesName = modal.data('sales-name');
            lunchEnabled = modal.data('has-lunch') === 'Ya' || modal.data('has-lunch') === true;
            snackEnabled = modal.data('has-snack') === 'Ya' || modal.data('has-snack') === true;
        }
        
        projectName = projectName || '-';
        mcuDate = formatLarkDate(mcuDate);
        salesName = salesName || '-';

        // Context detection using Bootstrap show class
        const isVendorModalOpen = $('#assignVendorModal').hasClass('show');
        const isConsumptionModalOpen = $('#approveConsumptionModal').hasClass('show');

        // Kebutuhan Vendor (from list.php modal)
        let vendorItems = [];
        if (isVendorModalOpen) {
            $('#vendorTable tbody tr').each(function() {
                const type = $(this).find('.exam_type_select').val();
                const other = $(this).find('.exam_type_other').val();
                const exam = (type === 'Other') ? other : type;
                const count = $(this).find('.participant_count').val();
                const notes = $(this).find('.notes').val();
                
                if (exam && count) {
                    vendorItems.push(`${exam} - ${count} Pax${notes ? ' (' + notes + ')' : ''}`);
                }
            });
        }

        let template = '';
        
        template += `Nama Project: ${projectName}\n`;
        template += `Nama Sales: ${salesName}\n`;
        template += `Tanggal MCU: ${mcuDate}\n\n`;

        // Kebutuhan Vendor
        if (vendorItems.length > 0) {
            template += `*Kebutuhan Vendor*\n`;
            vendorItems.forEach((item, i) => {
                template += `${i+1}. ${item}\n`;
            });
            template += `\n`;
        }

        // Makan Siang - Skip if in Vendor Modal, but show in Project Form or Consumption Modal
        if (lunchEnabled && !isVendorModalOpen) {
            template += `*Makan Siang*\n`;
            if (lunchBudget !== '0' && lunchBudget !== '') template += `Budget: Rp ${lunchBudget}\n`;
            template += `Items:\n`;
            
            let itemsCount = 0;
            if (lunchNames.length > 0) {
                lunchNames.forEach((name, i) => {
                    const qty = lunchQtys[i];
                    if (name || qty) {
                        itemsCount++;
                        template += `${itemsCount}. ${name || '-'} - ${qty || '0'} Pax\n`;
                    }
                });
            }
            if (itemsCount === 0) template += `- (Koordinasi lebih lanjut)\n`;
            template += `\n`;
        }

        // Snack - Skip if in Vendor Modal
        if (snackEnabled && !isVendorModalOpen) {
            template += `*Snack*\n`;
            if (snackBudget !== '0' && snackBudget !== '') template += `Budget: Rp ${snackBudget}\n`;
            template += `Items:\n`;
            
            let itemsCount = 0;
            if (snackNames.length > 0) {
                snackNames.forEach((name, i) => {
                    const qty = snackQtys[i];
                    if (name || qty) {
                        itemsCount++;
                        template += `${itemsCount}. ${name || '-'} - ${qty || '0'} Pax\n`;
                    }
                });
            }
            if (itemsCount === 0) template += `- (Koordinasi lebih lanjut)\n`;
            template += `\n`;
        }


        return template;
    }

    function showLarkTemplate() {
        const text = generateLarkTemplate();
        document.getElementById('larkTemplateText').value = text;
        const myModal = new bootstrap.Modal(document.getElementById('larkTemplateModal'));
        myModal.show();
    }

    function copyLarkTemplate() {
        const copyText = document.getElementById("larkTemplateText");
        copyText.select();
        copyText.setSelectionRange(0, 99999); // For mobile devices
        navigator.clipboard.writeText(copyText.value).then(() => {
            Swal.fire({
                icon: 'success',
                title: 'Copied!',
                text: 'Lark template copied to clipboard.',
                timer: 1500,
                showConfirmButton: false
            });
        });
    }

    let larkClicked = false;
    function markLarkClicked() {
        larkClicked = true;
        $('#submitProjectBtn').prop('disabled', false).css('opacity', '1');
    }

    window.checkLarkRequirement = () => {
        const lunchEnabled = $('input[name="lunch"]:checked').val() === 'Ya';
        const snackEnabled = $('input[name="snack"]:checked').val() === 'Ya';
        const isConsumptionRequested = lunchEnabled || snackEnabled;

        // Handle visibility of Lark container
        if (isConsumptionRequested) {
            $('#larkLinkContainer').removeClass('d-none');
            $('#vendorLarkContainer').removeClass('d-none'); // Also for list view
        } else {
            $('#larkLinkContainer').addClass('d-none');
            $('#vendorLarkContainer').addClass('d-none'); // Also for list view
        }
        
        if (isConsumptionRequested && !larkClicked) {
            $('#submitProjectBtn').prop('disabled', true).css('opacity', '0.6').attr('title', 'Please click Open Lark first');
            $('.btn-save-vendor').prop('disabled', true).css('opacity', '0.6');
        } else {
            $('#submitProjectBtn').prop('disabled', false).css('opacity', '1').removeAttr('title');
            $('.btn-save-vendor').prop('disabled', false).css('opacity', '1');
        }
    };

    $(document).ready(function() {
        // Check on change of lunch/snack
        $('input[name="lunch"], input[name="snack"]').on('change', window.checkLarkRequirement);
        
        // Initial check
        window.checkLarkRequirement();

        // Also intercept form submit just in case
        $('form').on('submit', function(e) {
            const lunchEnabled = $('input[name="lunch"]:checked').val() === 'Ya';
            const snackEnabled = $('input[name="snack"]:checked').val() === 'Ya';
            
            if ((lunchEnabled || snackEnabled) && !larkClicked) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Akses Lark Diperlukan',
                    text: 'Silakan klik button "Open Lark" terlebih dahulu untuk koordinasi konsumsi.',
                    confirmButtonColor: '#204EAB'
                });
                return false;
            }
        });
    });
</script>

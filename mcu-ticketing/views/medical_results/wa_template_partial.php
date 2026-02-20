
<!-- WA Report Template Modal -->
<div class="modal fade" id="waTemplateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-primary">Template Lark</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="form-floating mb-3">
                    <textarea class="form-control bg-light" id="waTemplateText" style="height: 350px; font-family: monospace; font-size: 0.9rem; white-space: pre-wrap;" readonly></textarea>
                    <label for="waTemplateText">Template Text</label>
                </div>
                <div class="alert alert-info small d-flex align-items-center mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    <div>Text has been generated based on current data. Click Copy to use.</div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success rounded-pill px-4" onclick="copyWaTemplate()">
                    <i class="fas fa-copy me-2"></i>Copy to Clipboard
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Global Project Info
    const PROJECT_INFO = {
        sales_name: "<?php echo htmlspecialchars($project['sales_name'] ?? ''); ?>",
        project_name: "<?php echo htmlspecialchars($project['nama_project'] ?? ''); ?>",
        saved_date: "<?php echo isset($_GET['saved_date']) ? $_GET['saved_date'] : ''; ?>"
    };

    function getGreeting() {
        const hour = new Date().getHours();
        if (hour < 11) return 'pagi';
        if (hour < 15) return 'siang';
        if (hour < 18) return 'sore';
        return 'malam';
    }

    function generateWaTemplate(dateMcu, paxChecked, paxReleased, linkPdf, diffNamesJson, diffReason) {
        // Parse Difference Names
        let pendingList = "";
        let paxText = "";
        
        // Pax Logic
        if (parseInt(paxReleased) >= parseInt(paxChecked)) {
            paxText = `${paxReleased} Pax`;
        } else {
            const diff = parseInt(paxChecked) - parseInt(paxReleased);
            paxText = `${paxReleased} pax (${paxChecked} - ${diff} pax)`;
        }

        // Pending List Logic
        let pendingNames = [];
        try {
            if (diffNamesJson) {
                // If it's already an object/array, use it directly, otherwise parse string
                const parsed = (typeof diffNamesJson === 'string') ? JSON.parse(diffNamesJson) : diffNamesJson;
                
                // Handle new format array of objects {name, reason}
                if (Array.isArray(parsed) && parsed.length > 0 && typeof parsed[0] === 'object') {
                     // Extract and flatten names from potential multiline strings
                     parsed.forEach(p => {
                         if(p.name) {
                             const names = p.name.split(/[\r\n]+/).map(s => s.trim()).filter(s => s);
                             pendingNames.push(...names);
                         }
                     });
                } 
                // Handle old format array of strings
                else if (Array.isArray(parsed)) {
                    pendingNames = parsed;
                }
            }
        } catch (e) {
            console.error("JSON Parse Error", e);
        }

        if (pendingNames.length > 0) {
            pendingList = `\n\nNote pending :\n${diffReason ? diffReason + '\n' : ''}`;
            pendingNames.forEach((name, index) => {
                pendingList += `${index + 1}. ${name}\n`;
            });
        }

        // Format Date
        const dateObj = new Date(dateMcu);
        const day = String(dateObj.getDate()).padStart(2, '0');
        // Months are 0-indexed in JS
        const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        const month = String(dateObj.getMonth() + 1).padStart(2, '0');
        const year = dateObj.getFullYear();
        const formattedDate = `${day}-${month}-${year}`;

        // Construct Template
        const template = `Selamat ${getGreeting()} Bapak/Ibu @${PROJECT_INFO.sales_name}, berikut kami kirimkan surat hasil untuk event sebagai berikut :

- Client : ${PROJECT_INFO.project_name}
- Tanggal Pelaksanaan : ${formattedDate}
- Total Pax : ${paxText}
- Link Surat Hasil : 
${linkPdf || '-'}
${pendingList}

Terima kasih`;

        return template;
    }

    function showWaTemplateModal(dateMcu, paxChecked, paxReleased, linkPdf, diffNamesJson, diffReason) {
        // Decode HTML entities in JSON string if necessary
        // But usually value attribute handles it. 
        // Just in case double encoding happened
        
        const text = generateWaTemplate(dateMcu, paxChecked, paxReleased, linkPdf, diffNamesJson, diffReason);
        document.getElementById('waTemplateText').value = text;
        const myModal = new bootstrap.Modal(document.getElementById('waTemplateModal'));
        myModal.show();
    }

    function copyWaTemplate() {
        const copyText = document.getElementById("waTemplateText");
        copyText.select();
        copyText.setSelectionRange(0, 99999); // For mobile devices
        navigator.clipboard.writeText(copyText.value).then(() => {
            Swal.fire({
                icon: 'success',
                title: 'Copied!',
                text: 'Template copied to clipboard.',
                timer: 1500,
                showConfirmButton: false
            });
        });
    }

    // Auto-show modal if saved_date is present
    document.addEventListener('DOMContentLoaded', function() {
        if (PROJECT_INFO.saved_date) {
            const btn = document.querySelector(`.btn-wa-template[data-date-mcu="${PROJECT_INFO.saved_date}"]`);
            if (btn) {
                // Scroll to the accordion item
                const accordionItem = btn.closest('.accordion-item');
                if (accordionItem) {
                    accordionItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    // Open accordion if collapsed
                    const collapse = accordionItem.querySelector('.accordion-collapse');
                    // We need to use bootstrap API to show
                    // But usually clicking the button inside it works even if collapsed? No, button is inside body.
                    // We need to show the collapse first.
                    if (collapse && !collapse.classList.contains('show')) {
                        const bsCollapse = new bootstrap.Collapse(collapse, { toggle: true });
                        
                        // Wait for transition to finish or just click immediately (modal will show over it)
                        setTimeout(() => btn.click(), 500);
                    } else {
                        btn.click();
                    }
                } else {
                    btn.click();
                }
            }
        }
    });
</script>

function initProjectForm(config) {
    const { holidays, minDate, dateValue } = config;

    // Flatpickr Initialization
    if (typeof flatpickr !== 'undefined') {
        flatpickr(".datepicker", {
            mode: "multiple",
            minDate: minDate,
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d M Y",
            defaultDate: dateValue ? dateValue.split(', ') : [],
            disable: [
                function(date) {
                    // Disable weekends
                    if (date.getDay() === 0 || date.getDay() === 6) {
                        return true;
                    }
                    // Disable holidays
                    const year = date.getFullYear();
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const day = String(date.getDate()).padStart(2, '0');
                    const dateString = `${year}-${month}-${day}`;
                    return holidays.includes(dateString);
                }
            ]
        });
    } else {
        console.error("Flatpickr library not found");
    }

    // Auto-calculate Total Peserta
    const jenisPemeriksaan = document.querySelector('textarea[name="jenis_pemeriksaan"]');
    const totalPeserta = document.querySelector('input[name="total_peserta"]');

    if (jenisPemeriksaan && totalPeserta) {
        jenisPemeriksaan.addEventListener('input', function() {
            const text = this.value;
            const lines = text.split('\n');
            let total = 0;

            lines.forEach(function(line) {
                line = line.trim();
                // Skip lines starting with '-' (items)
                if (line.startsWith('-')) return;

                const regex = /(\d+)\s*pax/gi;
                let match;
                while ((match = regex.exec(line)) !== null) {
                    total += parseInt(match[1], 10);
                }
            });

            if (total > 0) totalPeserta.value = total;
        });
    }

    // Form Submit Handler
    const form = document.getElementById('projectForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const select = document.getElementById('header_footer_select');
            const notes = document.getElementById('header_footer_notes');
            const finalInput = document.getElementById('header_footer_final');
            
            if (select.value === 'Bumame') {
                finalInput.value = 'Bumame';
            } else {
                let noteVal = notes.value.trim();
                if (!noteVal) {
                    e.preventDefault();
                    alert('Please specify the Co-Branding/White Label name in the notes field.');
                    notes.focus();
                    return;
                }
                finalInput.value = select.value + ' (' + noteVal + ')';
            }
        });
    }
    
    // Initial Calls
    toggleHeaderFooter();
    toggleConsumptionNotes('lunch');
    toggleConsumptionNotes('snack');
}

function toggleConsumptionNotes(type) {
    const radios = document.getElementsByName(type);
    const container = document.getElementById(type + '_notes_container');
    let isYes = false;

    for (let i = 0; i < radios.length; i++) {
        if (radios[i].checked && radios[i].value === 'Ya') {
            isYes = true;
            break;
        }
    }
    
    if (container) {
        if (isYes) {
            container.classList.remove('d-none');
            container.style.display = 'block';
        } else {
            container.classList.add('d-none');
            container.style.display = 'none';
        }
    }
}

function toggleHeaderFooter() {
    const select = document.getElementById('header_footer_select');
    const container = document.getElementById('hf_notes_container');
    const notesInput = document.getElementById('header_footer_notes');
    
    if (select && container && notesInput) {
        if (select.value === 'Bumame') {
            container.classList.add('d-none');
            notesInput.removeAttribute('required');
        } else {
            container.classList.remove('d-none');
            notesInput.setAttribute('required', 'required');
        }
    }
}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Calendar - Public View</title>
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- FullCalendar -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css' rel='stylesheet' />
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <style>
        :root {
            --primary-blue: #204EAB;
            --soft-blue: #EEF4FF;
            --border-blue: #3B82F6;
            --text-dark: #1E293B;
            --text-muted: #64748B;
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: #F8FAFC; 
            color: var(--text-dark);
            margin: 0;
            padding: 20px;
        }

        .calendar-wrapper {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        /* Header Header */
        .page-header {
            background: var(--primary-blue);
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-header h4 { color: white; margin: 0; font-weight: 700; letter-spacing: -0.5px; }
        .page-header p { color: rgba(255,255,255,0.7); margin: 0; font-size: 13px; }

        /* Calendar Styling */
        .fc { background: white; padding: 20px; }
        
        /* Smaller Toolbar */
        .fc .fc-toolbar { margin-bottom: 1.5em !important; gap: 10px; }
        .fc .fc-toolbar-title { font-size: 1.25rem !important; font-weight: 800; color: var(--text-dark); }
        
        .fc .fc-button {
            padding: 6px 14px !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            border-radius: 8px !important;
            text-transform: capitalize !important;
            box-shadow: none !important;
        }

        .fc .fc-button-primary {
            background-color: white !important;
            border-color: #E2E8F0 !important;
            color: var(--text-dark) !important;
        }

        .fc .fc-button-primary:hover { background-color: #F1F5F9 !important; }
        .fc .fc-button-active { background-color: var(--primary-blue) !important; border-color: var(--primary-blue) !important; color: white !important; }
        .fc .fc-today-button { background-color: var(--soft-blue) !important; border-color: var(--soft-blue) !important; color: var(--primary-blue) !important; }

        /* Cell Styling */
        .fc-theme-standard td, .fc-theme-standard th { border-color: #F1F5F9 !important; }
        .fc-day-today { background-color: #FAFAFA !important; }
        
        .fc-daygrid-day-number { 
            font-weight: 700; 
            color: var(--text-muted); 
            padding: 8px 12px !important; 
            text-decoration: none !important;
            font-size: 13px;
        }

        .fc-col-header-cell-cushion { 
            color: var(--text-muted); 
            font-size: 11px; 
            text-transform: uppercase; 
            font-weight: 800; 
            letter-spacing: 1px;
            padding: 10px 0 !important;
            text-decoration: none !important;
        }

        /* Event Card Style */
        .custom-event {
            background: #F8FAFC !important;
            border: none !important;
            border-left: 4px solid var(--border-blue) !important;
            border-radius: 4px !important;
            padding: 3px 8px !important;
            margin: 1px 4px 2px 4px !important;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(0,0,0,0.02);
        }

        .custom-event:hover {
            transform: translateX(2px);
            background: #F1F5F9 !important;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        }

        .event-title {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-dark);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: block;
        }

        /* More Link */
        .fc-daygrid-more-link {
            font-size: 11px !important;
            font-weight: 700 !important;
            color: var(--text-muted) !important;
            padding-left: 8px !important;
            text-decoration: none !important;
        }
        
        /* Modal Animation */
        .modal.fade .modal-dialog { transform: scale(0.95); transition: transform 0.3s ease-out; }
        .modal.show .modal-dialog { transform: scale(1); }
        
        /* Responsive Mobile */
        @media (max-width: 768px) {
            .page-header { flex-direction: column; text-align: center; gap: 15px; }
            .fc .fc-toolbar { flex-direction: column; }
        }

        /* Tooltip Customization */
        .tooltip-inner {
            background-color: var(--primary-blue);
            font-size: 12px;
            padding: 10px 15px;
            border-radius: 10px;
            text-align: left;
            line-height: 1.6;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<div class="calendar-wrapper">
    <div class="page-header">
        <div>
            <h4>Project Calendar</h4>
            <p>Bumame Public View Only</p>
        </div>
        <img src="assets/images/logo.png" alt="Logo" style="height: 35px; filter: brightness(0) invert(1);">
    </div>
    
    <div id="calendar"></div>
</div>

<!-- Modal Container (Same as Dashboard but restricted) -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-primary text-white border-0 py-3">
                <h5 class="modal-title fw-bold"><i class="fas fa-file-invoice me-2"></i>Project Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" id="modal-content-body">
                <!-- Content loaded via AJAX (DashboardController::getPublicProjectDetailAjax) -->
            </div>
            <div class="modal-footer border-0 bg-light py-2">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="js/project_detail.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        const initTooltip = () => {
            const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            tooltips.forEach(t => new bootstrap.Tooltip(t));
        };

        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,listMonth'
            },
            dayMaxEvents: 3, // Requirements: Max 3 events per cell
            height: 'auto',
            events: 'index.php?page=get_public_calendar_events',
            
            eventContent: function(arg) {
                // Truncation logic (Ellipsis)
                let title = arg.event.title;
                // Simplified PT truncation example: "PT. Sillomaritime Perdana Tbk" -> "PT. Sillomaritime..."
                if (title.length > 25) title = title.substring(0, 22) + '...';
                
                let el = document.createElement('div');
                el.className = 'custom-event';
                el.innerHTML = `<span class="event-title">${title}</span>`;
                
                // Tooltip info
                const props = arg.event.extendedProps;
                const tooltipHtml = `
                    <div style="font-weight: 700;">${arg.event.title}</div>
                    <div><i class="far fa-calendar-alt me-1"></i> ${props.formatted_date}</div>
                    <div><i class="fas fa-map-marker-alt me-1"></i> ${props.location || '-'}</div>
                    <div><i class="fas fa-user-tie me-1"></i> ${props.sales_name || '-'}</div>
                `;
                
                el.setAttribute('data-bs-toggle', 'tooltip');
                el.setAttribute('data-bs-html', 'true');
                el.setAttribute('data-bs-title', tooltipHtml);
                el.setAttribute('data-bs-placement', 'top');
                
                return { domNodes: [el] };
            },
            
            didOpen: function() {
                initTooltip();
            },
            
            eventClick: function(info) {
                info.jsEvent.preventDefault();
                // Requirement: restricted view (Detail only)
                loadProjectDetail(info.event.id, 'details', { 
                    hideTabs: true, 
                    isPublic: true // Added this to project_detail.js to use safe endpoint
                });
            },

            datesSet: function() {
                // Re-initialize tooltips whenever view changes
                setTimeout(initTooltip, 100);
            }
        });
        calendar.render();
    });
</script>

</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Public Project Calendar - Bumame</title>
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css' rel='stylesheet' />
    <link href="css/custom.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .public-container { max-width: 1200px; margin: 2rem auto; padding: 0 1rem; }
        .calendar-card { background: white; border-radius: 1.5rem; border: none; box-shadow: 0 10px 25px rgba(0,0,0,0.05); overflow: hidden; }
        .calendar-header { background: #204EAB; color: white; padding: 1.5rem 2rem; }
        
        /* Custom Calendar Styling */
        .fc-toolbar-title { font-weight: 700 !important; color: #1e293b; }
        .fc-button-primary { background-color: #204EAB !important; border-color: #204EAB !important; border-radius: 0.75rem !important; }
        .fc-daygrid-day-number { font-weight: 600; color: #64748b; text-decoration: none; }
        .fc-col-header-cell-cushion { color: #94a3b8; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; text-decoration: none; }
        
        /* Restricted Modal Styling for Public */
        #detailModal .modal-header { background-color: #204EAB; color: white; border-radius: 1rem 1rem 0 0; }
        #detailModal .btn-close { filter: brightness(0) invert(1); }
        
        .custom-event-pill { font-size: 0.75rem; padding: 2px 6px; border-radius: 4px; border-left: 3px solid #204EAB; background: #f1f5f9; color: #1e293b; font-weight: 600; margin-bottom: 2px; cursor: pointer; }
    </style>
</head>
<body>

<div class="public-container">
    <div class="calendar-card">
        <div class="calendar-header d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1 fw-bold">Project Calendar</h4>
                <p class="mb-0 opacity-75 small">Public View Only</p>
            </div>
            <img src="assets/images/logo.png" alt="Logo" style="height: 40px; filter: brightness(0) invert(1);">
        </div>
        <div class="p-4">
            <div id="calendar"></div>
        </div>
    </div>
</div>

<!-- Modal Container (Reuse Logic) -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-file-invoice me-2"></i>Project Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" id="modal-content-body">
                <!-- Content loaded via AJAX -->
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="js/project_detail.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,listMonth'
            },
            events: 'index.php?page=get_public_calendar_events',
            eventContent: function(arg) {
                let color = arg.event.backgroundColor || '#204EAB';
                return {
                    html: `<div class="custom-event-pill" style="border-left-color: ${color};">${arg.event.title}</div>`
                };
            },
            eventClick: function(info) {
                info.jsEvent.preventDefault();
                // Pass hideTabs: true for public view
                loadProjectDetail(info.event.id, 'details', { hideTabs: true });
            }
        });
        calendar.render();
    });
</script>

</body>
</html>

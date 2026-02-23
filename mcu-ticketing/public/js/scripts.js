// Custom Scripts
$(document).ready(function () {
    console.log("Bumame Ticketing System Initialized");

    // Kanban initialization moved to specific view files to avoid conflicts


    // Datepicker initialization (Flatpickr)
    if ($(".datepicker").length > 0) {
        $(".datepicker").flatpickr({
            mode: 'multiple',
            minDate: 'today',
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: "d M Y"
        });
    }

    // Toggle Notes for Lunch/Snack - REMOVED (Handled in specific views)
    // window.toggleNotes function caused conflicts and targeted non-existent IDs
});

// Notification Logic
$(document).ready(function () {
    function loadNotifications() {
        $.ajax({
            url: 'index.php?page=get_notifications',
            success: function (response) {
                try {
                    let notifications = response;
                    if (typeof response === 'string') {
                        // If empty string, default to empty array
                        if (!response.trim()) {
                            notifications = [];
                        } else {
                            notifications = JSON.parse(response);
                        }
                    }

                    if (!Array.isArray(notifications)) {
                        console.warn('Notifications response is not an array', notifications);
                        notifications = [];
                    }

                    const count = notifications.length;
                    const badge = $('.notification-badge');
                    const list = $('.notification-list');

                    if (count > 0) {
                        badge.text(count).show();
                        let html = '';
                        notifications.forEach(n => {
                            let icon = 'fa-bell';
                            let color = 'primary';
                            if (n.type === 'mention') { icon = 'fa-at'; color = 'info'; }

                            html += `
                                <li class="p-3 border-bottom notification-item ${n.is_read == 1 ? 'bg-light opacity-75' : 'bg-white'}" onclick="handleNotificationClick(event, ${n.id}, '${n.link}')" style="cursor: pointer; transition: background 0.2s;" onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor='${n.is_read == 1 ? '#f8f9fa' : 'white'}'">
                                    <div class="d-flex align-items-start">
                                        <div class="flex-shrink-0 text-${color} mt-1">
                                            <i class="fas ${icon}"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <p class="mb-1 small">${n.message}</p>
                                            <small class="text-muted" style="font-size: 0.7rem;">${new Date(n.created_at).toLocaleString()}</small>
                                        </div>
                                    </div>
                                </li>
                            `;
                        });
                        list.html(html);
                    } else {
                        badge.hide();
                        list.html(`
                            <li class="p-4 text-center text-muted small">
                                <i class="fas fa-bell-slash fs-4 mb-2 d-block opacity-50"></i>
                                No new notifications
                            </li>
                        `);
                    }
                } catch (e) {
                    console.error('Error parsing notifications', e, response);
                }
            }
        });
    }

    // Load on start
    loadNotifications();

    // Poll every 30 seconds
    setInterval(loadNotifications, 30000);

    // Mark all read
    $('.mark-all-read-btn').click(function (e) {
        e.preventDefault();
        $.post('index.php?page=mark_all_notifications_read', function (response) {
            loadNotifications();
        });
    });

    window.handleNotificationClick = function (e, id, link) {
        e.preventDefault();
        e.stopPropagation(); // Prevent dropdown from closing immediately if needed, though redirect will happen
        $.post('index.php?page=mark_notification_read', { id: id }, function (response) {
            if (link && link !== 'null') {
                window.location.href = link;
            } else {
                loadNotifications();
            }
        });
    }
});

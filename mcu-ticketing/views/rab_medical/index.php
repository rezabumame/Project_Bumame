<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<style>
    .calendar-container {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        padding: 20px;
    }
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 15px;
    }
    .calendar-day-header {
        text-align: center;
        font-weight: 700;
        color: #6c757d;
        padding-bottom: 15px;
        text-transform: uppercase;
        font-size: 0.85rem;
    }
    .calendar-day {
        min-height: 140px;
        border: 1px solid #edf2f7;
        border-radius: 10px;
        padding: 10px;
        background: #fff;
        transition: all 0.2s;
        cursor: pointer;
        position: relative;
    }
    .calendar-day:hover {
        border-color: #204EAB;
        box-shadow: 0 4px 12px rgba(32, 78, 171, 0.1);
        transform: translateY(-2px);
    }
    .calendar-day.other-month {
        background: #f8fafc;
        color: #cbd5e0;
    }
    .calendar-day.today {
        border: 2px solid #204EAB;
        background: rgba(32, 78, 171, 0.02);
    }
    .day-number {
        font-weight: 700;
        font-size: 1.1rem;
        margin-bottom: 8px;
        display: block;
    }
    .day-stats {
        font-size: 0.75rem;
        color: #4a5568;
        display: flex;
        flex-direction: column;
        gap: 2px;
        margin-bottom: 8px;
    }
    .day-stats span {
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .project-chip {
        display: block;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 0.7rem;
        color: #fff;
        margin-bottom: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-weight: 600;
    }
    .nav-tabs-custom {
        border-bottom: 2px solid #edf2f7;
        margin-bottom: 25px;
    }
    .nav-tabs-custom .nav-link {
        border: none;
        color: #718096;
        font-weight: 600;
        padding: 12px 24px;
        position: relative;
        transition: all 0.3s;
    }
    .nav-tabs-custom .nav-link.active {
        color: #204EAB;
        background: transparent;
    }
    .nav-tabs-custom .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 2px;
        background: #204EAB;
    }
</style>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center page-header-container mb-4">
        <div>
            <h1 class="page-header-title">RAB Medical Report Management</h1>
            <p class="page-header-subtitle">Manage planning and realization for Medical Report teams.</p>
        </div>
        <div>
            <?php if (in_array($_SESSION['role'], ['dw_tim_hasil', 'surat_hasil', 'superadmin'])): ?>
            <a href="index.php?page=rab_medical_create" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> New Submission
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs nav-tabs-custom" id="rabTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link <?php echo $view === 'list' ? 'active' : ''; ?>" href="index.php?page=rab_medical_index&view=list">
                <i class="fas fa-list me-2"></i> Submission List
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $view === 'calendar' ? 'active' : ''; ?>" href="index.php?page=rab_medical_index&view=calendar">
                <i class="fas fa-calendar-alt me-2"></i> CALENDAR WORKLOAD
            </a>
        </li>
    </ul>

    <div class="tab-content" id="rabTabsContent">
        <?php if ($view === 'list'): ?>
        <!-- List View -->
        <div class="tab-pane fade show active">
            <!-- Dashboard Stats -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-uppercase mb-1 small opacity-75">Active Projects</h6>
                                    <h2 class="mb-0 fw-bold"><?php echo $stats['active_projects']; ?></h2>
                                </div>
                                <i class="fas fa-project-diagram fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-uppercase mb-1 small opacity-75">Daily Needs (Today)</h6>
                                    <h2 class="mb-0 fw-bold"><?php echo $stats['daily_needs']; ?> Person</h2>
                                </div>
                                <i class="fas fa-users fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-warning text-dark h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-uppercase mb-1 small opacity-75">Pending Approval</h6>
                                    <h2 class="mb-0 fw-bold"><?php echo $stats['pending_approval']; ?></h2>
                                </div>
                                <i class="fas fa-clock fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Submission List</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4" style="width: 25%">Project Name</th>
                                    <th style="width: 12%">Status</th>
                                    <th style="width: 15%">Kohas</th>
                                    <th style="width: 12%">Stats</th>
                                    <th style="width: 8%">Notif</th>
                                    <th style="width: 8%">Hardcopy</th>
                                    <th style="width: 10%">Date</th>
                                    <th class="text-end pe-4" style="width: 10%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($rabs)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-5 text-muted">
                                            <i class="fas fa-folder-open fa-3x mb-3 d-block opacity-50"></i>
                                            No submissions found.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($rabs as $r): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold">
                                            <?php echo htmlspecialchars($r['nama_project']); ?>
                                        </td>
                                        <td>
                                            <?php
                                            $statusClass = 'bg-secondary';
                                            $statusLabel = ucfirst(str_replace('_', ' ', $r['status']));
                                            
                                            if($r['status'] == 'approved_manager' || $r['status'] == 'approved_head' || $r['status'] == 'completed') {
                                                $statusClass = 'bg-success';
                                            } elseif($r['status'] == 'submitted') {
                                                $statusClass = 'bg-warning text-dark';
                                            } elseif($r['status'] == 'rejected') {
                                                $statusClass = 'bg-danger';
                                            }
                                            ?>
                                            <span class="badge <?php echo $statusClass; ?> rounded-pill small">
                                                <?php echo $statusLabel; ?>
                                            </span>
                                        </td>
                                        <td class="small"><?php echo htmlspecialchars($r['creator_name']); ?></td>
                                        <td>
                                            <div class="small fw-bold text-dark text-nowrap"><?php echo $r['total_days'] ?? 0; ?> Days</div>
                                            <div class="small text-muted text-nowrap"><?php echo $r['total_personnel'] ?? 0; ?> Person</div>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <i class="fab fa-whatsapp <?php echo $r['send_whatsapp'] ? 'text-success' : 'text-muted opacity-25'; ?>" title="WhatsApp"></i>
                                                <i class="fas fa-envelope <?php echo $r['send_email'] ? 'text-primary' : 'text-muted opacity-25'; ?>" title="Email"></i>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if($r['needs_hardcopy']): ?>
                                                <span class="badge bg-soft-info text-info border border-info" style="font-size: 0.7rem;">YES</span>
                                            <?php else: ?>
                                                <span class="text-muted small">NO</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="small text-nowrap"><?php echo DateHelper::formatIndonesianDate($r['created_at']); ?></td>
                                        <td class="text-end pe-4">
                                            <a href="index.php?page=rab_medical_view&id=<?php echo $r['id']; ?>" class="btn btn-sm btn-outline-primary text-nowrap">
                                                View Details
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php if($total_pages > 1): ?>
                <div class="card-footer bg-white py-3">
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-end mb-0">
                            <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="index.php?page=rab_medical_index&view=list&p=<?php echo $current_page - 1; ?>">Previous</a>
                            </li>
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="index.php?page=rab_medical_index&view=list&p=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="index.php?page=rab_medical_index&view=list&p=<?php echo $current_page + 1; ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php else: ?>
        <!-- Calendar View -->
        <div class="tab-pane fade show active">
            <div class="row mb-4 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Select Month</label>
                    <div class="input-group">
                        <a href="index.php?page=rab_medical_index&view=calendar&month=<?php echo $month == 1 ? 12 : $month - 1; ?>&year=<?php echo $month == 1 ? $year - 1 : $year; ?>&project_id=<?php echo $filters['project_id']; ?>&user_id=<?php echo $filters['user_id']; ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <span class="form-control text-center fw-bold">
                            <?php echo DateHelper::formatMonthYearIndonesian($month, $year); ?>
                        </span>
                        <a href="index.php?page=rab_medical_index&view=calendar&month=<?php echo $month == 12 ? 1 : $month + 1; ?>&year=<?php echo $month == 12 ? $year + 1 : $year; ?>&project_id=<?php echo $filters['project_id']; ?>&user_id=<?php echo $filters['user_id']; ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Filter Project</label>
                    <select class="form-select select2-simple" onchange="window.location.href='index.php?page=rab_medical_index&view=calendar&month=<?php echo $month; ?>&year=<?php echo $year; ?>&user_id=<?php echo $filters['user_id']; ?>&project_id=' + this.value">
                        <option value="">All Projects</option>
                        <?php foreach($projects as $p): ?>
                            <option value="<?php echo $p['project_id']; ?>" <?php echo $filters['project_id'] == $p['project_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($p['nama_project']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Filter Kohas</label>
                    <select class="form-select select2-simple" onchange="window.location.href='index.php?page=rab_medical_index&view=calendar&month=<?php echo $month; ?>&year=<?php echo $year; ?>&project_id=<?php echo $filters['project_id']; ?>&user_id=' + this.value">
                        <option value="">All Kohas</option>
                        <?php foreach($kohas_list as $k): ?>
                            <option value="<?php echo $k['user_id']; ?>" <?php echo $filters['user_id'] == $k['user_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($k['full_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 text-end">
                    <a href="index.php?page=rab_medical_index&view=calendar" class="btn btn-outline-danger">
                        <i class="fas fa-sync-alt me-1"></i> Reset Filters
                    </a>
                </div>
            </div>

            <div class="calendar-container">
                <div class="calendar-grid">
                    <div class="calendar-day-header">Sun</div>
                    <div class="calendar-day-header">Mon</div>
                    <div class="calendar-day-header">Tue</div>
                    <div class="calendar-day-header">Wed</div>
                    <div class="calendar-day-header">Thu</div>
                    <div class="calendar-day-header">Fri</div>
                    <div class="calendar-day-header">Sat</div>

                    <?php
                    $first_day = date('w', strtotime("$year-$month-01"));
                    $days_in_month = date('t', strtotime("$year-$month-01"));
                    
                    // Pad previous month days
                    for ($i = 0; $i < $first_day; $i++) {
                        echo '<div class="calendar-day other-month"></div>';
                    }

                    // Calendar Days
                    for ($day = 1; $day <= $days_in_month; $day++) {
                        $current_date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                        $is_today = $current_date === date('Y-m-d');
                        
                        // Extract data for this date
                        $day_data = array_filter($workload_data, function($w) use ($current_date) {
                            return $w['date'] === $current_date;
                        });
                        
                        $unique_projects = array_unique(array_column($day_data, 'project_id'));
                        $unique_dw = array_unique(array_column($day_data, 'dw_user_id'));
                        $unique_kohas = array_unique(array_column($day_data, 'kohas_user_id'));
                        $total_assignments = count($day_data);
                        $unique_users = count(array_unique(array_merge($unique_dw, $unique_kohas)));

                        echo '<div class="calendar-day ' . ($is_today ? 'today' : '') . '" onclick="showDetail(\'' . $current_date . '\')">';
                        echo '<span class="day-number">' . $day . '</span>';
                        
                        if (!empty($day_data)) {
                            echo '<div class="day-stats">';
                            echo '<span><i class="fas fa-briefcase fa-fw text-primary"></i> ' . count($unique_projects) . ' Projects</span>';
                            echo '<span><i class="fas fa-users fa-fw text-success"></i> ' . $unique_users . ' Users</span>';
                            echo '<span><i class="fas fa-tasks fa-fw text-warning"></i> ' . $total_assignments . ' Assign</span>';
                            echo '</div>';
                            
                            foreach ($unique_projects as $pid) {
                                // Find any record for this project to get name and kohas
                                $proj = array_values(array_filter($day_data, function($d) use ($pid) { return $d['project_id'] === $pid; }))[0];
                                $color = "hsl(" . (crc32($proj['kohas_user_id'] . $proj['kohas_name']) % 360) . ", 70%, 50%)";
                                echo '<div class="project-chip" style="background-color: ' . $color . ';" title="' . htmlspecialchars($proj['nama_project']) . '">';
                                echo htmlspecialchars($proj['nama_project']);
                                echo '</div>';
                            }
                        }
                        
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-calendar-day me-2"></i> Workload Detail - <span id="modalDateDisplay"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row text-center mb-4">
                    <div class="col-4">
                        <div class="p-3 rounded bg-light">
                            <h6 class="text-muted small text-uppercase mb-1">Total Project</h6>
                            <h4 class="mb-0 fw-bold text-primary" id="modalTotalProjects">0</h4>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 rounded bg-light">
                            <h6 class="text-muted small text-uppercase mb-1">Unique User</h6>
                            <h4 class="mb-0 fw-bold text-success" id="modalTotalUsers">0</h4>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 rounded bg-light">
                            <h6 class="text-muted small text-uppercase mb-1">Assignments</h6>
                            <h4 class="mb-0 fw-bold text-warning" id="modalTotalAssignments">0</h4>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3 border-bottom pb-2">
                            <i class="fas fa-user-shield me-2 text-primary"></i> KOHAS (Coordinators)
                        </h6>
                        <div id="kohasList" class="list-group list-group-flush"></div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3 border-bottom pb-2">
                            <i class="fas fa-user-edit me-2 text-success"></i> DW_TIM_HASIL (Processors)
                        </h6>
                        <div id="dwList" class="list-group list-group-flush"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const workloadData = <?php echo json_encode($workload_data); ?>;

    function stringToHsl(str) {
        let hash = 0;
        for (let i = 0; i < str.length; i++) {
            hash = str.charCodeAt(i) + ((hash << 5) - hash);
        }
        const h = Math.abs(hash % 360);
        return `hsl(${h}, 70%, 50%)`;
    }

    function showDetail(date) {
        const dayData = workloadData.filter(d => d.date === date);
        const modal = new bootstrap.Modal(document.getElementById('detailModal'));
        
        document.getElementById('modalDateDisplay').innerText = formatDate(date);

        const uniqueProjects = [...new Set(dayData.map(d => d.project_id))];
        const uniqueDwIds = [...new Set(dayData.map(d => d.dw_user_id))];
        const uniqueKohasIds = [...new Set(dayData.map(d => d.kohas_user_id))];
        const totalAssignments = dayData.length;
        const totalUniqueUsers = [...new Set([...uniqueDwIds, ...uniqueKohasIds])].length;

        document.getElementById('modalTotalProjects').innerText = uniqueProjects.length;
        document.getElementById('modalTotalUsers').innerText = totalUniqueUsers;
        document.getElementById('modalTotalAssignments').innerText = totalAssignments;

        // Render KOHAS
        const kohasContainer = document.getElementById('kohasList');
        kohasContainer.innerHTML = '';
        const uniqueKohas = [];
        dayData.forEach(d => {
            if (!uniqueKohas.find(k => k.id === d.kohas_user_id)) {
                uniqueKohas.push({id: d.kohas_user_id, name: d.kohas_name});
            }
        });

        uniqueKohas.forEach(k => {
            const color = stringToHsl(k.id + k.name);
            const projects = [...new Set(dayData.filter(d => d.kohas_user_id === k.id).map(d => d.nama_project))];
            
            const item = document.createElement('div');
            item.className = 'list-group-item px-0 border-0 mb-3';
            item.innerHTML = `
                <div class="d-flex align-items-center">
                    <div style="width: 12px; height: 12px; border-radius: 50%; background-color: ${color};" class="me-2"></div>
                    <div class="fw-bold">${k.name}</div>
                </div>
                <div class="ps-4 small text-muted">
                    Managing: ${projects.join(', ')}
                </div>
            `;
            kohasContainer.appendChild(item);
        });

        // Render DW
        const dwContainer = document.getElementById('dwList');
        dwContainer.innerHTML = '';
        const dwIds = [...new Set(dayData.map(d => d.dw_user_id))];
        
        dwIds.forEach(dwId => {
            const dwAssignments = dayData.filter(d => d.dw_user_id === dwId);
            const dwName = dwAssignments[0].dw_user_name;
            
            const item = document.createElement('div');
            item.className = 'list-group-item px-0 border-0 mb-3';
            
            let colorIndicators = '';
            dwAssignments.forEach(a => {
                const kColor = stringToHsl(a.kohas_user_id + a.kohas_name);
                colorIndicators += `<div style="width: 12px; height: 12px; border-radius: 50%; background-color: ${kColor};" class="me-1" title="Project: ${a.nama_project} (Kohas: ${a.kohas_name})"></div>`;
            });

            item.innerHTML = `
                <div class="d-flex align-items-center mb-1">
                    <div class="d-flex me-2">${colorIndicators}</div>
                    <div class="fw-bold">${dwName}</div>
                </div>
                <div class="ps-4 small text-muted">
                    Work on: ${dwAssignments.map(a => a.nama_project).join(', ')}
                </div>
            `;
            dwContainer.appendChild(item);
        });

        modal.show();
    }
</script>

<?php include '../views/layouts/footer.php'; ?>


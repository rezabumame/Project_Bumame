<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
masih    /* Premium UI Overrides */
     :root {
         --bumame-blue: #204EAB;
         --bumame-dark: #173b85;
     }

     .filter-card {
         background: #ffffff;
         border-radius: 1rem;
         box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05);
         border: 1px solid rgba(0,0,0,0.02);
     }
     
     .section-title {
         font-size: 0.75rem;
         letter-spacing: 0.05em;
         color: #8898aa;
         font-weight: 700;
         text-transform: uppercase;
         margin-bottom: 1rem;
     }
     
     .text-bumame {
         color: #204EAB !important;
     }

     /* Custom Inputs */
     .premium-input-group {
         background: #f8f9fa;
         border-radius: 0.75rem;
         border: 1px solid transparent;
         transition: all 0.2s ease;
         padding: 0.25rem;
     }
     
     .premium-input-group:focus-within {
         background: #fff;
         border-color: #e9ecef;
         box-shadow: 0 4px 12px rgba(32, 78, 171, 0.1);
     }
     
     .premium-input-group .input-group-text {
         background: transparent;
         border: none;
         color: #6c757d;
         padding-left: 1rem;
     }
     
     .premium-input-group .form-control {
         background: transparent;
         border: none;
         font-size: 0.9rem;
         font-weight: 500;
         color: #344767;
         box-shadow: none;
         padding-top: 0.6rem;
         padding-bottom: 0.6rem;
     }
     
     .premium-input-group .form-control:focus {
         box-shadow: none;
     }

     /* Modern Switch */
     .form-check-input:checked {
         background-color: #204EAB;
         border-color: #204EAB;
     }
     
     .comparison-box {
         background: linear-gradient(145deg, #ffffff, #f0f4ff);
         border: 1px dashed #ccdcfc;
         border-radius: 0.75rem;
         position: relative;
         overflow: hidden;
     }
     
     .comparison-box::before {
         content: '';
         position: absolute;
         top: 0;
         left: 0;
         width: 4px;
         height: 100%;
         background: #204EAB;
     }

     /* Select2 Customization */
     .select2-container--bootstrap-5 .select2-selection {
         border-radius: 0.75rem !important;
         border: 1px solid #e9ecef !important;
         background-color: #f8f9fa !important;
         min-height: 42px !important;
         padding-top: 4px;
     }
     
     .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
         color: #344767 !important;
         font-weight: 500;
     }
     
     .btn-premium-primary {
         background: linear-gradient(135deg, #204EAB 0%, #173b85 100%);
         border: 0;
         border-radius: 0.75rem;
         color: #fff;
         font-weight: 600;
         padding: 0.75rem 1.5rem;
         box-shadow: 0 4px 6px rgba(32, 78, 171, 0.2), 0 1px 3px rgba(0, 0, 0, 0.08);
         transition: all 0.2s;
     }
     
     .btn-premium-primary:hover {
         transform: translateY(-1px);
         box-shadow: 0 7px 14px rgba(32, 78, 171, 0.25), 0 3px 6px rgba(0, 0, 0, 0.08);
         color: #fff;
         background: linear-gradient(135deg, #1d4699 0%, #133270 100%);
     }

     .btn-premium-light {
         background: #fff;
         border: 1px solid #e9ecef;
         border-radius: 0.75rem;
         color: #6c757d;
         font-weight: 600;
         padding: 0.75rem 1.5rem;
     }
     
     .btn-premium-light:hover {
         background: #f8f9fa;
         color: #204EAB;
     }
</style>

<?php
// Initialize variables to defaults if not set (Defensive Coding for View)
$start_date = $start_date ?? date('Y-m-01');
$end_date = $end_date ?? date('Y-m-t');
$filter_project_id = $filter_project_id ?? '';
$filter_sales_id = $filter_sales_id ?? '';
$filter_korlap_id = $filter_korlap_id ?? '';
$filter_kohas_id = $filter_kohas_id ?? '';

$project_list = $project_list ?? [];
$sales_list = $sales_list ?? [];
$korlap_list = $korlap_list ?? [];
$kohas_list = $kohas_list ?? [];

$total_projects = $total_projects ?? 0;
$total_anggaran = $total_anggaran ?? 0;
$total_realisasi = $total_realisasi ?? 0;
$total_pax = $total_pax ?? 0;
$total_hari_ops = $total_hari_ops ?? 0;
$total_petugas = $total_petugas ?? 0;

$sales_stats = $sales_stats ?? [];
$korlap_stats = $korlap_stats ?? [];
$kohas_stats_data = $kohas_stats_data ?? [];
$trend_data = $trend_data ?? [];
$filtered_projects = $filtered_projects ?? [];
?>

<div class="container-fluid px-4">
    <div class="my-4">
        <h1 class="page-header-title mb-1">
            <i class="fas fa-chart-line text-primary me-2"></i>Productivity Ops
        </h1>
        <div class="text-muted small">
            Operations & Productivity Monitoring Dashboard
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-card mb-4">
        <div class="card-body p-4">
            <form method="GET" action="index.php">
                <input type="hidden" name="page" value="productivity_ops">
                
                <div class="row g-5">
                    <!-- Date Analysis Section -->
                    <div class="col-lg-5 border-end-lg">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 class="section-title mb-0">
                                <i class="fas fa-clock me-2 text-primary"></i>Period Analysis
                            </h6>
                            <!-- Quick Select Pills -->
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-light btn-sm text-muted border-0 py-1 px-2 small" onclick="setDateRange('this_month')">This Month</button>
                                <button type="button" class="btn btn-outline-light btn-sm text-muted border-0 py-1 px-2 small" onclick="setDateRange('last_month')">Last Month</button>
                            </div>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted mb-2">Active Period</label>
                                <div class="premium-input-group d-flex align-items-center">
                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                    <input type="date" class="form-control text-center" name="start_date" value="<?php echo $start_date; ?>">
                                    <span class="text-muted mx-2 small">to</span>
                                    <input type="date" class="form-control text-center" name="end_date" value="<?php echo $end_date; ?>">
                                </div>
                            </div>
                            
                            <div class="col-12 mt-4">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="form-check form-switch ps-0">
                                        <input class="form-check-input ms-0 me-2" type="checkbox" id="compare_mode" name="compare_mode" value="1" <?php echo isset($_GET['compare_mode']) && $_GET['compare_mode'] == '1' ? 'checked' : ''; ?> style="float: none; margin-left: 0;">
                                        <label class="form-check-label fw-bold small text-dark cursor-pointer" for="compare_mode">
                                            Compare with previous period
                                        </label>
                                    </div>
                                </div>

                                <div class="comparison-box p-3 <?php echo isset($_GET['compare_mode']) && $_GET['compare_mode'] == '1' ? '' : 'd-none'; ?>" id="compare_inputs">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <label class="form-label small fw-bold text-muted mb-0">Comparison Baseline</label>
                                        <span class="badge bg-light text-secondary border rounded-pill fw-normal" style="font-size: 0.65rem;">vs Previous</span>
                                    </div>
                                    <div class="premium-input-group d-flex align-items-center bg-white border">
                                        <span class="input-group-text"><i class="fas fa-history text-secondary"></i></span>
                                        <input type="date" class="form-control text-center" name="compare_start" value="<?php echo $_GET['compare_start'] ?? ''; ?>">
                                        <span class="text-muted mx-2 small">to</span>
                                        <input type="date" class="form-control text-center" name="compare_end" value="<?php echo $_GET['compare_end'] ?? ''; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Dimensions Section -->
                    <div class="col-lg-7">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 class="section-title mb-0">
                                <i class="fas fa-filter me-2 text-primary"></i>Data Dimensions
                            </h6>
                            <span class="badge bg-light text-muted fw-normal rounded-pill px-3 py-2">
                                <i class="fas fa-info-circle me-1"></i> Multi-select filters
                            </span>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted mb-2">Project Scope</label>
                                <select class="form-select select2" name="project_id" data-placeholder="Select Project Scope...">
                                    <option value="">All Projects</option>
                                    <?php foreach ($project_list as $p): ?>
                                        <option value="<?php echo $p['project_id']; ?>" <?php echo $filter_project_id == $p['project_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($p['nama_project']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted mb-2">Sales Representative</label>
                                <select class="form-select select2" name="sales_id" data-placeholder="Select Sales Rep...">
                                    <option value="">All Sales</option>
                                    <?php foreach ($sales_list as $s): ?>
                                        <option value="<?php echo $s['id']; ?>" <?php echo $filter_sales_id == $s['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($s['sales_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted mb-2">Field Coordinator (Korlap)</label>
                                <select class="form-select select2" name="korlap_id" data-placeholder="Select Korlap...">
                                    <option value="">All Korlap</option>
                                    <?php foreach ($korlap_list as $k): ?>
                                        <option value="<?php echo $k['user_id']; ?>" <?php echo $filter_korlap_id == $k['user_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($k['full_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted mb-2">Health Coordinator (Kohas)</label>
                                <select class="form-select select2" name="kohas_id" data-placeholder="Select Kohas...">
                                    <option value="">All Kohas</option>
                                    <?php foreach ($kohas_list as $kh): ?>
                                        <option value="<?php echo $kh['user_id']; ?>" <?php echo $filter_kohas_id == $kh['user_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($kh['full_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-12 mt-5 d-flex justify-content-end gap-3 align-items-center">
                                <a href="index.php?page=productivity_ops" class="btn btn-premium-light">
                                    <i class="fas fa-undo me-2"></i>Reset
                                </a>
                                <button type="submit" class="btn btn-premium-primary">
                                    <i class="fas fa-search me-2"></i>Apply Analytics
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-2 mb-4">
        <!-- Total Projects -->
        <div class="col-lg-1 col-md-2">
            <div class="card border-0 shadow-sm h-100 rounded-4 bg-primary text-white text-center">
                <div class="card-body p-2">
                    <div class="small opacity-75 mb-1 text-truncate">Projects</div>
                    <div class="h3 fw-bold mb-0"><?php echo number_format($total_projects); ?></div>
                    <?php if (isset($deltas['total_projects'])): ?>
                        <div class="small text-white opacity-75 mt-1" style="font-size: 0.7rem;">
                            <?php echo $deltas['total_projects']['sign'] . number_format(abs($deltas['total_projects']['pct']), 1); ?>%
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- Total Anggaran -->
        <div class="col-lg-3 col-md-4">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-body p-2">
                    <div class="small text-muted mb-1">Budget</div>
                    <div class="h3 fw-bold text-dark mb-0 text-nowrap">Rp <?php echo number_format($total_anggaran); ?></div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="small text-muted text-nowrap" style="font-size: 0.7rem;">
                            Est. Budget: Rp <?php echo number_format($total_rab_submission); ?>
                        </div>
                        <?php if (isset($deltas['total_anggaran'])): ?>
                            <div class="small fw-bold <?php echo $deltas['total_anggaran']['color']; ?>" style="font-size: 0.7rem;">
                                <?php echo $deltas['total_anggaran']['sign'] . number_format(abs($deltas['total_anggaran']['pct']), 1); ?>%
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <!-- Total Realisasi -->
        <div class="col-lg-3 col-md-4">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-body p-2">
                    <div class="small text-muted mb-1">Actual Cost</div>
                    <div class="h3 fw-bold text-primary mb-0 text-nowrap">Rp <?php echo number_format($total_realisasi); ?></div>
                    <div class="d-flex justify-content-between align-items-center">
                        <?php 
                            $global_utilization = ($total_anggaran > 0) ? ($total_realisasi / $total_anggaran * 100) : 0;
                            $util_color = ($global_utilization > 100) ? 'text-danger' : 'text-success';
                        ?>
                        <div class="small fw-bold <?php echo $util_color; ?>">
                            <?php echo number_format($global_utilization, 1); ?>% Used
                        </div>
                        <?php if (isset($deltas['total_realisasi'])): ?>
                            <div class="small fw-bold <?php echo $deltas['total_realisasi']['color']; ?>" style="font-size: 0.7rem;">
                                <?php echo $deltas['total_realisasi']['sign'] . number_format(abs($deltas['total_realisasi']['pct']), 1); ?>%
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <!-- Total Pax -->
        <div class="col-lg-2 col-md-3">
            <div class="card border-0 shadow-sm h-100 rounded-4 text-center">
                <div class="card-body p-2">
                    <div class="small text-muted mb-1">Total Participants</div>
                    <div class="h3 fw-bold text-dark mb-0"><?php echo number_format($total_pax); ?></div>
                    <?php if (isset($deltas['total_pax'])): ?>
                        <div class="small fw-bold mt-1 <?php echo $deltas['total_pax']['color']; ?>" style="font-size: 0.7rem;">
                            <?php echo $deltas['total_pax']['sign'] . number_format(abs($deltas['total_pax']['pct']), 1); ?>%
                        </div>
                    <?php else: ?>
                        <div class="small text-muted">Pax</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- Hari Operasional -->
        <div class="col-lg-1 col-md-3">
            <div class="card border-0 shadow-sm h-100 rounded-4 text-center">
                <div class="card-body p-2">
                    <div class="small text-muted mb-1 text-truncate">Ops Days</div>
                    <div class="h3 fw-bold text-dark mb-0"><?php echo number_format($total_hari_ops); ?></div>
                    <?php if (isset($deltas['total_hari_ops'])): ?>
                        <div class="small fw-bold mt-1 <?php echo $deltas['total_hari_ops']['color']; ?>" style="font-size: 0.7rem;">
                            <?php echo $deltas['total_hari_ops']['sign'] . number_format(abs($deltas['total_hari_ops']['pct']), 1); ?>%
                        </div>
                    <?php else: ?>
                        <div class="small text-muted">Days</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- Total Petugas -->
        <div class="col-lg-2 col-md-3">
            <div class="card border-0 shadow-sm h-100 rounded-4 text-center">
                <div class="card-body p-2">
                    <div class="small text-muted mb-1 text-truncate">Personnel</div>
                    <div class="h3 fw-bold text-dark mb-0"><?php echo number_format($total_petugas); ?></div>
                    <?php if (isset($deltas['total_petugas'])): ?>
                        <div class="small fw-bold mt-1 <?php echo $deltas['total_petugas']['color']; ?>" style="font-size: 0.7rem;">
                            <?php echo $deltas['total_petugas']['sign'] . number_format(abs($deltas['total_petugas']['pct']), 1); ?>%
                        </div>
                    <?php else: ?>
                        <div class="small text-muted">Staff</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Overall Budget Efficiency -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 fw-bold py-3">
                    <i class="fas fa-money-bill-wave text-primary me-2"></i>Overall Budget Efficiency
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <div style="width: 250px; height: 250px;">
                        <canvas id="budgetPieChart"></canvas>
                    </div>
                    <div class="ms-4">
                        <h4 class="mb-1">Efficiency Rate</h4>
                        <h2 class="display-6 fw-bold text-primary">
                            <?php echo ($total_anggaran > 0) ? number_format(($total_anggaran - $total_realisasi) / $total_anggaran * 100, 1) : 0; ?>%
                        </h2>
                        <p class="text-muted small">Savings from total budget</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Korlap Performance -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 fw-bold py-3 d-flex justify-content-between">
                    <div><i class="fas fa-user-shield text-primary me-2"></i>Korlap Performance</div>
                    <span class="badge bg-light text-muted border">Top 5 by Score</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Korlap Name</th>
                                    <th class="text-center">Projects</th>
                                    <th class="text-center">Days</th>
                                    <th class="text-center">Participants</th>
                                    <th class="text-center">Avg TAT</th>
                                    <th class="text-center">Utilization</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                // Sort by Score (Lower diff is better)
                                usort($korlap_stats, function($a, $b) { return $a['score'] <=> $b['score']; });
                                foreach (array_slice($korlap_stats, 0, 5) as $k): 
                                    $util = $k['utilization'];
                                    $status_badge = '';
                                    if ($util >= 95 && $util <= 100) $status_badge = '<span class="badge bg-success">Excellent</span>';
                                    elseif ($util >= 90 && $util < 95) $status_badge = '<span class="badge bg-primary">Good</span>';
                                    elseif ($util > 100 && $util <= 105) $status_badge = '<span class="badge bg-warning text-dark">Warning</span>';
                                    else $status_badge = '<span class="badge bg-danger">Poor</span>';
                                ?>
                                <tr>
                                    <td class="fw-bold"><?php echo htmlspecialchars($k['name']); ?></td>
                                    <td class="text-center"><?php echo $k['projects']; ?></td>
                                    <td class="text-center"><?php echo $k['days']; ?></td>
                                    <td class="text-center"><?php echo number_format($k['pax']); ?></td>
                                    <td class="text-center">
                                        <?php 
                                            $avg_tat = $k['avg_tat'];
                                            // Use config from controller, default to 3
                                            $tat_target = $korlap_tat_days ?? 3;
                                            $tat_color = ($avg_tat <= $tat_target) ? 'text-success' : 'text-danger';
                                        ?>
                                        <span class="fw-bold <?php echo $tat_color; ?>"><?php echo number_format($avg_tat, 1); ?> Days</span>
                                    </td>
                                    <td class="text-center">
                                        <?php 
                                            $diff = 100 - $util;
                                            $diff_color = ($diff < 0) ? 'text-danger' : 'text-success';
                                            $diff_sign = ($diff > 0) ? '+' : '';
                                        ?>
                                        <div class="fw-bold <?php echo $diff_color; ?>">
                                            <?php echo $diff_sign . number_format($diff, 1); ?>%
                                        </div>
                                        <div class="small text-muted" style="font-size: 0.7rem;">(vs Budget)</div>
                                    </td>
                                    <td><?php echo $status_badge; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Kohas Performance -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 fw-bold py-3">
                    <i class="fas fa-user-md text-primary me-2"></i>Kohas Performance
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Name</th>
                                    <th class="text-center">Projects</th>
                                    <th class="text-center">Total Results</th>
                                    <th class="text-center">Total Personnel</th>
                                    <th class="text-center">TAT %</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                // Process Kohas Data for TAT
                                foreach ($kohas_stats_data as &$kh) {
                                    $total_items = $kh['total_items'] ?? 0;
                                    $on_time = $kh['on_time_count'] ?? 0;
                                    $kh['tat_percent'] = ($total_items > 0) ? ($on_time / $total_items * 100) : 0;
                                }
                                unset($kh);
                                
                                // Sort by TAT % Descending (Higher is better)
                                usort($kohas_stats_data, function($a, $b) { return $b['tat_percent'] <=> $a['tat_percent']; });
                                
                                foreach (array_slice($kohas_stats_data, 0, 5) as $kh): 
                                    $tat = $kh['tat_percent'];
                                    $tat_color = ($tat >= 95) ? 'text-success' : (($tat >= 90) ? 'text-primary' : 'text-danger');
                                ?>
                                <tr>
                                    <td class="fw-bold"><?php echo htmlspecialchars($kh['full_name']); ?></td>
                                    <td class="text-center"><?php echo $kh['total_projects']; ?></td>
                                    <td class="text-center"><?php echo number_format($kh['total_surat_hasil']); ?></td>
                                    <td class="text-center"><?php echo number_format($kh['total_petugas_realized']); ?></td>
                                    <td class="text-center fw-bold <?php echo $tat_color; ?>"><?php echo number_format($tat, 1); ?>%</td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($kohas_stats_data)): ?>
                                <tr><td colspan="5" class="text-center text-muted small">No data available</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Projects at Risk -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 fw-bold py-3">
                    <i class="fas fa-exclamation-triangle text-danger me-2"></i>Projects at Risk
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Project</th>
                                    <th class="text-center">Issue</th>
                                    <th class="text-center">Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                // Identify Risk Projects
                                $risk_projects = [];
                                
                                foreach ($filtered_projects as $p) {
                                    $issues = [];
                                    
                                    // 1. Over Budget
                                    $budget = $p['metrics']['budget'];
                                    $realization = $p['metrics']['realization'];
                                    if ($budget > 0 && $realization > $budget) {
                                        $diff = $realization - $budget;
                                        $pct = ($diff / $budget) * 100;
                                        $issues[] = [
                                            'type' => 'Over Budget',
                                            'val' => '+' . number_format($pct, 0) . '%',
                                            'color' => 'text-danger'
                                        ];
                                    }
                                    
                                    // 2. TAT Missed
                                    $tat_days = $p['metrics']['tat_days'];
                                    $tat_target = $korlap_tat_days ?? 3;
                                    if ($tat_days > $tat_target) {
                                         $issues[] = [
                                            'type' => 'TAT Missed',
                                            'val' => $tat_days . 'd',
                                            'color' => 'text-warning text-dark'
                                        ];
                                    }
                                    
                                    if (!empty($issues)) {
                                        // If multiple issues, pick the most severe (Over Budget > TAT) or list first?
                                        // Let's just take the first one for the summary table.
                                        $p['primary_issue'] = $issues[0];
                                        $risk_projects[] = $p;
                                    }
                                }
                                
                                // Show top 5
                                $risk_count = 0;
                                foreach (array_slice($risk_projects, 0, 5) as $p): 
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-circle text-danger me-2 small" style="font-size: 0.5rem;"></i>
                                            <div class="fw-bold" title="<?php echo htmlspecialchars($p['nama_project']); ?>">
                                                <?php echo htmlspecialchars($p['nama_project']); ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center small text-danger fw-bold">
                                        <?php echo $p['primary_issue']['type']; ?>
                                    </td>
                                    <td class="text-center small fw-bold <?php echo $p['primary_issue']['color']; ?>">
                                        <?php echo $p['primary_issue']['val']; ?>
                                    </td>
                                </tr>
                                <?php 
                                    $risk_count++;
                                endforeach; 
                                
                                if ($risk_count == 0):
                                ?>
                                <tr><td colspan="3" class="text-center text-muted small py-3">No critical issues found</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row g-4 mb-4">
        <!-- Monthly Trends -->
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 fw-bold py-3">
                    <i class="fas fa-chart-line text-primary me-2"></i>Monthly Trends (Projects & Participants)
                </div>
                <div class="card-body">
                    <canvas id="trendChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Detailed Project Table -->
    <div class="card border-0 shadow-sm rounded-4 mb-5">
        <div class="card-header bg-transparent border-0 fw-bold py-3">
            <i class="fas fa-list text-primary me-2"></i>Project Details
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="detailTable">
                    <thead class="bg-light">
                        <tr>
                            <th>Project</th>
                            <th>Sales</th>
                            <th>Korlap</th>
                            <th>Kohas</th>
                            <th class="text-center">Days</th>
                            <th class="text-center">Participants</th>
                            <th class="text-end">Budget</th>
                            <th class="text-end">Actual Cost</th>
                            <th class="text-center">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($filtered_projects as $p): ?>
                        <tr>
                            <td>
                                <div class="fw-bold">
                                    <a href="#" class="text-decoration-none text-dark project-detail-link" 
                                       data-id="<?php echo $p['project_id']; ?>">
                                        <?php echo htmlspecialchars($p['nama_project']); ?> 
                                    </a>
                                </div>
                                <span class="badge bg-light text-secondary border rounded-pill small"><?php echo $p['project_id']; ?></span>
                            </td>
                            <td class="small"><?php echo htmlspecialchars($p['sales_name'] ?? '-'); ?></td>
                            <td class="small"><?php echo htmlspecialchars($p['korlap_name'] ?? '-'); ?></td>
                            <td class="small"><?php echo htmlspecialchars($p['kohas_names'] ?? '-'); ?></td>
                            <td class="text-center"><?php echo $p['metrics']['days']; ?></td>
                            <td class="text-center fw-bold"><?php echo number_format($p['metrics']['pax']); ?></td>
                            <td class="text-end small"><?php echo number_format($p['metrics']['budget']); ?></td>
                            <td class="text-end small"><?php echo number_format($p['metrics']['realization']); ?></td>
                            <td class="text-center">
                                <?php 
                                    $util = $p['metrics']['utilization'];
                                    $color = ($util > 100) ? 'text-danger fw-bold' : 'text-success fw-bold';
                                ?>
                                <span class="<?php echo $color; ?>"><?php echo number_format($util, 1); ?>%</span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../views/partials/project_detail_modal.php'; ?>

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="js/project_detail.js"></script>

<script>
// Helper function to set date range
function setDateRange(range) {
    const today = new Date();
    let start, end;
    
    if (range === 'this_month') {
        start = new Date(today.getFullYear(), today.getMonth(), 1);
        end = new Date(today.getFullYear(), today.getMonth() + 1, 0);
    } else if (range === 'last_month') {
        start = new Date(today.getFullYear(), today.getMonth() - 1, 1);
        end = new Date(today.getFullYear(), today.getMonth(), 0);
    }
    
    // Format to YYYY-MM-DD
    const formatDate = (date) => {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };
    
    $('input[name="start_date"]').val(formatDate(start));
    $('input[name="end_date"]').val(formatDate(end));
}

// Data for Charts
const trendData = <?php echo json_encode($trend_data); ?>;

// Initialize Select2
$(document).ready(function() {
    $('.select2').select2({
        theme: 'bootstrap-5',
        width: '100%',
        allowClear: true
    });
});

// Trend Chart
const trendCtx = document.getElementById('trendChart').getContext('2d');
const trendLabels = Object.keys(trendData);
const trendProjects = trendLabels.map(k => trendData[k].projects);
const trendPax = trendLabels.map(k => trendData[k].pax);

new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: trendLabels,
        datasets: [
            {
                label: 'Projects',
                data: trendProjects,
                borderColor: 'rgba(54, 162, 235, 1)',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                yAxisID: 'y',
                tension: 0.3,
                fill: true
            },
            {
                label: 'Total Pax',
                data: trendPax,
                borderColor: 'rgba(255, 99, 132, 1)',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                yAxisID: 'y1',
                tension: 0.3,
                fill: true
            }
        ]
    },
    options: {
        responsive: true,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: { display: true, text: 'Projects' },
                beginAtZero: true
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: { display: true, text: 'Pax' },
                beginAtZero: true,
                grid: {
                    drawOnChartArea: false,
                },
            },
        }
    }
});

// Budget Pie Chart
const budgetCtx = document.getElementById('budgetPieChart').getContext('2d');
new Chart(budgetCtx, {
    type: 'doughnut',
    data: {
        labels: ['Realisasi', 'Remaining Budget'],
        datasets: [{
            data: [<?php echo $total_realisasi; ?>, <?php echo max(0, $total_anggaran - $total_realisasi); ?>],
            backgroundColor: ['#4facfe', '#e9ecef'],
            borderWidth: 0
        }]
    },
    options: {
        cutout: '70%',
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});

// Initialize DataTable
$(document).ready(function() {
    $('#detailTable').DataTable({
        pageLength: 10,
        order: [[ 7, "desc" ]] // Sort by utilization desc
    });

    // Handle Project Modal
    $('.project-detail-link').on('click', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        loadProjectDetail(id);
    });
    
    // Toggle Compare Mode
    $('#compare_mode').on('change', function() {
        if ($(this).is(':checked')) {
            $('#compare_inputs').removeClass('d-none');
        } else {
            $('#compare_inputs').addClass('d-none');
        }
    });
});
</script>

<?php include '../views/layouts/footer.php'; ?>
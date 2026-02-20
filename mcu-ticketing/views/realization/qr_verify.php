<?php
$project_name_display = $project_name ?? '-';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bumame Verification</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #005EB8;
            --success: #10B981;
            --warning: #F59E0B;
            --text-main: #1F2937;
            --text-secondary: #6B7280;
            --bg-page: #F3F4F6;
            --bg-card: #FFFFFF;
            --border: #E5E7EB;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-page);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .verify-card {
            background: var(--bg-card);
            width: 100%;
            max-width: 500px;
            border-radius: 20px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
        .card-header {
            background: #fff;
            padding: 24px 32px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .logo { height: 32px; object-fit: contain; }
        .header-badge {
            font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;
            color: var(--text-secondary); background: #F9FAFB; padding: 6px 12px; border-radius: 99px; border: 1px solid var(--border);
        }
        .card-body { padding: 32px; }
        .status-section { text-align: center; margin-bottom: 32px; }
        .status-icon-wrapper { width: 80px; height: 80px; margin: 0 auto 16px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .status-approved .status-icon-wrapper { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        .status-pending .status-icon-wrapper { background: rgba(245, 158, 11, 0.1); color: var(--warning); }
        .status-icon { width: 40px; height: 40px; }
        .status-title { font-size: 24px; font-weight: 700; margin-bottom: 8px; color: var(--text-main); }
        .status-subtitle { font-size: 14px; color: var(--text-secondary); }
        .details-grid { display: grid; gap: 20px; }
        .detail-item { display: flex; flex-direction: column; gap: 6px; padding-bottom: 16px; border-bottom: 1px dashed var(--border); }
        .detail-item:last-child { border-bottom: none; padding-bottom: 0; }
        .detail-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.8px; color: var(--text-secondary); }
        .detail-value { font-size: 15px; font-weight: 500; color: var(--text-main); word-break: break-word; }
        .approver-box { background: #F8FAFC; border-radius: 12px; padding: 16px; margin-top: 24px; border: 1px solid var(--border); display: flex; align-items: center; gap: 12px; }
        .approver-avatar { width: 40px; height: 40px; background: var(--primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 16px; flex-shrink: 0; }
        .approver-info { display: flex; flex-direction: column; }
        .approver-name { font-size: 14px; font-weight: 600; color: var(--text-main); }
        .approver-role { font-size: 12px; color: var(--text-secondary); }
        .card-footer { background: #F9FAFB; padding: 16px; text-align: center; border-top: 1px solid var(--border); }
        .footer-text { font-size: 12px; color: var(--text-secondary); display: flex; align-items: center; justify-content: center; gap: 6px; }
        .verified-tick { width: 16px; height: 16px; fill: var(--primary); }
        @media (min-width: 640px) {
            .details-grid { grid-template-columns: 1fr 1fr; gap: 24px; }
            .detail-item { border-bottom: none; padding-bottom: 0; }
            .detail-item.full-width { grid-column: span 2; border-bottom: 1px dashed var(--border); padding-bottom: 24px; margin-bottom: 8px; }
        }
    </style>
</head>
<body>
<?php
$is_approved = in_array($status_label, ['Approved', 'Completed']);
$status_class = $is_approved ? 'status-approved' : 'status-pending';
$display_status = $is_approved ? 'VERIFIED' : $status_label;
$initials = !empty($name) ? substr($name, 0, 1) : '-';
?>
    <div class="verify-card <?php echo $status_class; ?>">
        <div class="card-header">
            <img src="assets/images/logo.png" alt="BUMAME" class="logo">
            <div class="header-badge">Official Document</div>
        </div>
        <div class="card-body">
            <div class="status-section">
                <div class="status-icon-wrapper">
                    <?php if($is_approved): ?>
                        <svg class="status-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"></path></svg>
                    <?php else: ?>
                        <svg class="status-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <?php endif; ?>
                </div>
                <h1 class="status-title"><?php echo htmlspecialchars($display_status); ?></h1>
                <p class="status-subtitle">
                    <?php if($is_approved): ?>
                        Dokumen ini telah disetujui secara digital.
                    <?php else: ?>
                        Dokumen ini sedang dalam proses review.
                    <?php endif; ?>
                </p>
            </div>
            <div class="details-grid">
                <div class="detail-item full-width">
                    <span class="detail-label">Judul Dokumen</span>
                    <span class="detail-value"><?php echo htmlspecialchars($doc_title); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Nomor Dokumen</span>
                    <span class="detail-value"><?php echo htmlspecialchars($doc_number); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Tanggal</span>
                    <span class="detail-value"><?php echo !empty($approved_at) ? date('d M Y, H:i', strtotime($approved_at)) : '-'; ?></span>
                </div>
                <div class="detail-item full-width">
                    <span class="detail-label">Perusahaan Client</span>
                    <span class="detail-value"><?php echo htmlspecialchars($company); ?></span>
                </div>
                <div class="detail-item full-width">
                    <span class="detail-label">Project</span>
                    <span class="detail-value"><?php echo htmlspecialchars($project_name_display); ?></span>
                </div>
            </div>
            <div class="approver-box">
                <div class="approver-avatar"><?php echo strtoupper($initials); ?></div>
                <div class="approver-info">
                    <span class="detail-label">Disetujui Oleh</span>
                    <span class="approver-name"><?php echo htmlspecialchars($name); ?></span>
                    <span class="approver-role"><?php echo htmlspecialchars($role); ?></span>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <div class="footer-text">
                <svg class="verified-tick" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                Verifikasi Sistem Bumame
            </div>
        </div>
    </div>
</body>
</html>

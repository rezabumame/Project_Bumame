<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Internal Memo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/all.min.css">
    <style>
        body { background-color: #f4f7f6; font-family: 'Inter', sans-serif; }
        .verify-card { max-width: 500px; margin: 50px auto; border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .status-badge { font-size: 0.9rem; padding: 8px 16px; border-radius: 50px; }
        .success-bg { background: linear-gradient(135deg, #204EAB 0%, #173b85 100%); color: white; }
        .info-label { color: #6c757d; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }
        .info-value { font-weight: 600; color: #343a40; }
        .signature-section { background: #f8f9fa; border-radius: 15px; padding: 20px; margin-top: 20px; border-left: 5px solid #204EAB; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card verify-card mt-5">
            <div class="card-header success-bg text-center py-4 border-0 rounded-top-20">
                <i class="fas fa-check-circle fa-3x mb-3 text-white"></i>
                <h4 class="mb-0 text-white">Verification Status</h4>
                <p class="mb-0 opacity-75">Bumame Internal Document</p>
            </div>
            <div class="card-body p-4">
                <div class="mb-4">
                    <label class="info-label">Dokumen</label>
                    <div class="info-value">INTERNAL MEMO</div>
                </div>

                <div class="mb-4">
                    <label class="info-label">Project</label>
                    <div class="info-value"><?php echo htmlspecialchars($project['nama_project']); ?></div>
                    <div class="text-muted small"><?php echo htmlspecialchars($project['company_name']); ?></div>
                </div>

                <div class="mb-4">
                    <label class="info-label">Cost Code</label>
                    <div class="info-value"><?php echo isset($selected_cost_code) ? htmlspecialchars($selected_cost_code['code']) : '-'; ?></div>
                </div>

                <div class="signature-section">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <label class="info-label mb-1"><?php echo $status_label; ?> Oleh</label>
                            <div class="h5 mb-0 info-value"><?php echo htmlspecialchars($name); ?></div>
                            <div class="text-muted"><?php echo htmlspecialchars($title); ?></div>
                        </div>
                        <div class="ms-3 text-center" style="color: #204EAB;">
                            <i class="fas fa-shield-alt fa-2x"></i>
                            <div class="small fw-bold mt-1">AUTHENTIC</div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">
                
                <div class="text-center">
                    <img src="assets/images/logo.png" alt="Bumame" style="height: 30px; opacity: 0.6;">
                    <p class="text-muted small mt-3 mb-0">&copy; <?php echo date('Y'); ?> PT. Bumame Cahaya Medika</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

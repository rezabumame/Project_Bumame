<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center page-header-container">
        <div>
            <h1 class="page-header-title">Detail Request Inventory</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="index.php?page=inventory_request_index">Inventory</a></li>
                    <li class="breadcrumb-item active">Detail</li>
                </ol>
            </nav>
        </div>
    </div>

            <div class="row">
                <!-- Header Info -->
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Informasi Request</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th style="width: 150px;">No Request</th>
                                            <td>: <?php echo $data['header']['request_number']; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Project</th>
                                            <td>: <?php echo $data['header']['nama_project']; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Tanggal</th>
                                            <td>: <?php echo date('d M Y H:i', strtotime($data['header']['created_at'])); ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th style="width: 150px;">Requester</th>
                                            <td>: <?php echo $data['header']['requester_name']; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Status Utama</th>
                                            <td>: 
                                                <span class="badge bg-primary"><?php echo $data['header']['status']; ?></span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Split Status -->
                <?php if (!empty($data['splits'])): ?>
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Status Gudang</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>Gudang</th>
                                            <th>Status</th>
                                            <th>Last Update</th>
                                            <th>Aksi</th>
                                            <th>Dokumen</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($data['splits'] as $split): ?>
                                        <tr>
                                            <td>
                                                <?php if ($split['warehouse_type'] == 'GUDANG_ASET'): ?>
                                                    <span class="badge bg-warning text-dark">Gudang Aset</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Gudang Konsumable</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                    $badge = 'secondary';
                                                    if ($split['status'] == 'PENDING') $badge = 'warning';
                                                    if ($split['status'] == 'IN_PREPARATION') $badge = 'info';
                                                    if ($split['status'] == 'READY') $badge = 'primary';
                                                    if ($split['status'] == 'COMPLETED') $badge = 'success';
                                                ?>
                                                <span class="badge bg-<?php echo $badge; ?>"><?php echo $split['status']; ?></span>
                                            </td>
                                            <td><?php echo $split['updated_at'] ? date('d M Y H:i', strtotime($split['updated_at'])) : '-'; ?></td>
                                            <td>
                                                <?php if ($split['status'] == 'READY' && $_SESSION['role'] == 'korlap'): ?>
                                                    <form action="index.php?page=warehouse_update_status" method="POST" class="d-inline form-confirm-reception">
                                                        <?php echo $this->getCsrfField(); ?>
                                                        <input type="hidden" name="id" value="<?php echo $split['id']; ?>">
                                                        <input type="hidden" name="status" value="COMPLETED">
                                                        <button type="button" class="btn btn-sm btn-success" onclick="confirmReception(this)">
                                                            <i class="fas fa-check-circle me-1"></i> Konfirmasi Diterima
                                                        </button>
                                                    </form>
                                                <?php elseif ($split['status'] == 'COMPLETED'): ?>
                                                    <span class="badge bg-success"><i class="fas fa-check"></i> Sudah Diterima</span>
                                                <?php elseif ($split['proof_file']): ?>
                                                     <a href="<?php echo $split['proof_file']; ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-file-download me-1"></i> Bukti</a>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="index.php?page=warehouse_print&id=<?php echo $split['id']; ?>" target="_blank" class="btn btn-sm btn-outline-danger" title="Download PDF">
                                                    <i class="fas fa-file-pdf me-1"></i> PDF
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Items -->
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Daftar Barang</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-nowrap align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Barang</th>
                                            <th>Kategori</th>
                                            <th>Tipe</th>
                                            <th>Gudang</th>
                                            <th>Jumlah Request</th>
                                            <th>Satuan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1; foreach ($data['items'] as $item): ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo $item['item_name']; ?></td>
                                            <td><?php echo $item['category']; ?></td>
                                            <td><?php echo $item['item_type_snapshot']; ?></td>
                                            <td><?php echo $item['warehouse_snapshot']; ?></td>
                                            <td class="fw-bold"><?php echo $item['qty_request']; ?></td>
                                            <td><?php echo $item['unit']; ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 mb-4">
                    <a href="index.php?page=inventory_request_index" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>
                </div>

            </div>
</div>

<script>
function confirmReception(btn) {
    Swal.fire({
        title: 'Konfirmasi Penerimaan',
        text: "Apakah Anda yakin barang sudah diterima lengkap? Status akan berubah menjadi COMPLETED.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Terima Barang',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $(btn).closest('form').submit();
        }
    });
}
</script>

<?php include '../views/layouts/footer.php'; ?>
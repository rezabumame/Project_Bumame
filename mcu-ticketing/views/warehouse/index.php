<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center page-header-container">
        <div>
            <h1 class="page-header-title"><?php echo $page_title; ?></h1>
            <p class="page-header-subtitle">Manage and monitor warehouse inventory requests.</p>
        </div>
    </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-none d-md-block">
                                <table id="datatable" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                    <thead>
                                        <tr>
                                            <th>ID Project</th>
                                            <th>Project</th>
                                            <th>Requester</th>
                                            <th>Tanggal Request</th>
                                            <th>Status Gudang</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($requests as $r): ?>
                                        <tr>
                                            <td><?php echo $r['project_id']; ?></td>
                                            <td><?php echo $r['nama_project']; ?></td>
                                            <td><?php echo $r['requester_name']; ?></td>
                                            <td><?php echo date('d M Y H:i', strtotime($r['request_date'])); ?></td>
                                            <td>
                                                <?php 
                                                    $badge = 'secondary';
                                                    if ($r['status'] == 'IN_PREPARATION') $badge = 'warning';
                                                    if ($r['status'] == 'READY') $badge = 'success';
                                                    if ($r['status'] == 'COMPLETED') $badge = 'primary';
                                                ?>
                                                <span class="badge bg-<?php echo $badge; ?>"><?php echo $r['status']; ?></span>
                                            </td>
                                            <td>
                                                <a href="index.php?page=warehouse_detail&id=<?php echo $r['id']; ?>" class="btn btn-sm btn-primary">Process</a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Mobile Card View -->
                            <div class="d-block d-md-none">
                                <?php foreach ($requests as $r): ?>
                                <div class="card mb-3 border shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h5 class="card-title mb-0">ID: <?php echo $r['project_id']; ?></h5>
                                            <?php 
                                                $badge = 'secondary';
                                                if ($r['status'] == 'IN_PREPARATION') $badge = 'warning';
                                                if ($r['status'] == 'READY') $badge = 'success';
                                                if ($r['status'] == 'COMPLETED') $badge = 'primary';
                                            ?>
                                            <span class="badge bg-<?php echo $badge; ?>"><?php echo $r['status']; ?></span>
                                        </div>
                                        <p class="card-text mb-1"><strong>Project:</strong> <?php echo $r['nama_project']; ?></p>
                                        <p class="card-text mb-1"><strong>Requester:</strong> <?php echo $r['requester_name']; ?></p>
                                        <p class="card-text mb-2"><small class="text-muted"><?php echo date('d M Y H:i', strtotime($r['request_date'])); ?></small></p>
                                        <div class="d-grid">
                                            <a href="index.php?page=warehouse_detail&id=<?php echo $r['id']; ?>" class="btn btn-primary">Process</a>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

</div>

<?php include '../views/layouts/footer.php'; ?>
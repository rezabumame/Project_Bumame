<?php include '../views/layouts/header.php'; ?>
<?php include '../views/layouts/sidebar.php'; ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center page-header-container">
        <div>
            <h1 class="page-header-title">National Holidays</h1>
            <p class="page-header-subtitle">Manage national holidays and days off for scheduling.</p>
        </div>
    </div>

    <?php if (isset($_GET['status'])): ?>
        <div class="alert alert-<?php echo $_GET['status'] == 'error' ? 'danger' : 'success'; ?> alert-dismissible fade show" role="alert">
            <?php 
            if ($_GET['status'] == 'success') echo "Holiday added successfully.";
            elseif ($_GET['status'] == 'deleted') echo "Holiday deleted successfully.";
            else echo "An error occurred.";
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-plus me-1"></i>
                    Add Holiday
                </div>
                <div class="card-body">
                    <form action="index.php?page=holidays_store" method="POST">
                        <div class="mb-3">
                            <label for="holiday_date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="holiday_date" name="holiday_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <input type="text" class="form-control" id="description" name="description" placeholder="e.g., Independence Day" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Holiday</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-table me-1"></i>
                    List of National Holidays
                </div>
                <div class="card-body">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr>
                                    <td><?php echo date('d F Y', strtotime($row['holiday_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                                    <td>
                                        <a href="index.php?page=holidays_delete&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../views/layouts/footer.php'; ?>
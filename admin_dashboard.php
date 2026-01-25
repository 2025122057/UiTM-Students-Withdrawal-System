<?php
require_once 'includes/db.php';
require_once 'includes/auth_check.php';

// Restricted to admin only
if (($_SESSION['role'] ?? '') != 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Fetch Statistics
$stats = [
    'total' => $pdo->query("SELECT COUNT(*) FROM withdrawals")->fetchColumn(),
    'pending' => $pdo->query("SELECT COUNT(*) FROM withdrawals WHERE status = 'Pending'")->fetchColumn(),
    'approved' => $pdo->query("SELECT COUNT(*) FROM withdrawals WHERE status = 'Approved'")->fetchColumn(),
    'rejected' => $pdo->query("SELECT COUNT(*) FROM withdrawals WHERE status = 'Rejected'")->fetchColumn(),
];

$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';

$sql = "SELECT * FROM withdrawals WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (name LIKE :search OR student_id LIKE :search OR email LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($status_filter) {
    $sql .= " AND status = :status";
    $params[':status'] = $status_filter;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$records = $stmt->fetchAll();

// Fetch Reason Distribution for Chart
$stmt_reasons = $pdo->query("SELECT reason, COUNT(*) as count FROM withdrawals GROUP BY reason");
$reasons_data = $stmt_reasons->fetchAll(PDO::FETCH_KEY_PAIR);

include 'includes/header.php';
?>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
        <i class="fas fa-check-circle me-2"></i> <?php echo htmlspecialchars($_GET['msg']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center p-3 bg-primary text-white border-0 shadow-sm rounded-4">
            <h6 class="text-uppercase small fw-bold">Total Applications</h6>
            <h2 class="mb-0">
                <?php echo $stats['total']; ?>
            </h2>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center p-3 bg-warning text-dark border-0 shadow-sm rounded-4">
            <h6 class="text-uppercase small fw-bold">Pending Approval</h6>
            <h2 class="mb-0">
                <?php echo $stats['pending']; ?>
            </h2>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center p-3 bg-success text-white border-0 shadow-sm rounded-4">
            <h6 class="text-uppercase small fw-bold">Approved</h6>
            <h2 class="mb-0">
                <?php echo $stats['approved']; ?>
            </h2>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center p-3 bg-danger text-white border-0 shadow-sm rounded-4">
            <h6 class="text-uppercase small fw-bold">Rejected</h6>
            <h2 class="mb-0">
                <?php echo $stats['rejected']; ?>
            </h2>
        </div>
    </div>
</div>

<!-- Visualizations Section -->
<div class="row mb-4">
    <div class="col-md-5">
        <div class="card p-4 border-0 shadow-sm rounded-4 h-100">
            <h5 class="fw-bold mb-4 text-center">Status Overview</h5>
            <canvas id="statusChart"></canvas>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card p-4 border-0 shadow-sm rounded-4 h-100">
            <h5 class="fw-bold mb-4 text-center">Withdrawal Reasons</h5>
            <canvas id="reasonChart"></canvas>
        </div>
    </div>
</div>

<div class="card p-4 border-0 shadow-sm rounded-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold m-0 text-primary"><i class="fas fa-tasks me-2"></i> Application Management</h3>
        <form action="" method="GET" class="d-flex gap-2">
            <select name="status" class="form-select form-select-sm border-0 bg-light" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="Pending" <?php echo $status_filter == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="Approved" <?php echo $status_filter == 'Approved' ? 'selected' : ''; ?>>Approved</option>
                <option value="Rejected" <?php echo $status_filter == 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
            </select>
            <input type="text" name="search" class="form-control form-control-sm border-0 bg-light"
                placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-sm btn-primary px-3 rounded-pill">Search</button>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="bg-light">
                <tr>
                    <th class="border-0">Date</th>
                    <th class="border-0">Student Name</th>
                    <th class="border-0">Student ID</th>
                    <th class="border-0">Program</th>
                    <th class="border-0">Status</th>
                    <th class="text-end border-0">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($records) > 0): ?>
                    <?php foreach ($records as $record): ?>
                        <tr>
                            <td class="text-muted small">
                                <?php echo date('d/m/Y', strtotime($record['created_at'])); ?>
                            </td>
                            <td class="fw-bold">
                                <?php echo htmlspecialchars($record['name']); ?>
                            </td>
                            <td>
                                <code><?php echo htmlspecialchars($record['student_id']); ?></code>
                            </td>
                            <td>
                                <span
                                    class="badge bg-light text-primary"><?php echo htmlspecialchars($record['program_code']); ?></span>
                            </td>
                            <td>
                                <span class="badge rounded-pill bg-<?php
                                echo $record['status'] == 'Pending' ? 'warning' : ($record['status'] == 'Approved' ? 'success' : 'danger');
                                ?> px-3">
                                    <?php echo $record['status'] == 'Pending' ? 'Pending Approval' : $record['status']; ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group shadow-sm rounded-pill overflow-hidden">
                                    <a href="view.php?id=<?php echo $record['id']; ?>" class="btn btn-sm btn-light"
                                        title="Details"><i class="fas fa-eye text-primary"></i></a>
                                    <?php if ($record['status'] == 'Pending'): ?>
                                        <a href="update_status.php?id=<?php echo $record['id']; ?>&status=Approved"
                                            class="btn btn-sm btn-light" onclick="return confirm('Approve this application?')"
                                            title="Approve"><i class="fas fa-check text-success"></i></a>
                                        <a href="update_status.php?id=<?php echo $record['id']; ?>&status=Rejected"
                                            class="btn btn-sm btn-light" onclick="return confirm('Reject this application?')"
                                            title="Reject"><i class="fas fa-times text-danger"></i></a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fas fa-folder-open mb-3 d-block fs-1"></i>
                            No applications found matching your criteria.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Status Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Approved', 'Rejected'],
            datasets: [{
                data: [<?php echo $stats['pending']; ?>, <?php echo $stats['approved']; ?>, <?php echo $stats['rejected']; ?>],
                backgroundColor: ['#ffc107', '#198754', '#dc3545'],
                borderWidth: 0,
                hoverOffset: 15
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            },
            cutout: '70%'
        }
    });

    // Reason Chart
    const reasonCtx = document.getElementById('reasonChart').getContext('2d');
    new Chart(reasonCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_keys($reasons_data)); ?>,
            datasets: [{
                label: 'Total Students',
                data: <?php echo json_encode(array_values($reasons_data)); ?>,
                backgroundColor: 'rgba(13, 110, 253, 0.2)',
                borderColor: 'rgb(13, 110, 253)',
                borderWidth: 2,
                borderRadius: 10
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: { beginAtZero: true, ticks: { stepSize: 1 } },
                y: { grid: { display: false } }
            }
        }
    });
</script>

<?php include 'includes/footer.php'; ?>
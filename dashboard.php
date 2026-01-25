<?php
require_once 'includes/db.php';
require_once 'includes/auth_check.php';

$search = $_GET['search'] ?? '';

$sql = "SELECT * FROM withdrawals";
if ($search && ($_SESSION['role'] ?? '') == 'admin') {
    $sql .= " WHERE name LIKE :search OR student_id LIKE :search";
} else {
    // Reset search if not admin
    $search = '';
    if (($_SESSION['role'] ?? '') != 'admin') {
        $sql .= " WHERE user_id = :user_id";
    }
}
$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
if ($search) {
    $stmt->bindValue(':search', "%$search%");
}
if (($_SESSION['role'] ?? '') != 'admin') {
    $stmt->bindValue(':user_id', $_SESSION['user_id']);
}
$stmt->execute();
$records = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Withdrawal Records</h2>
            <?php if (($_SESSION['role'] ?? '') != 'admin'): ?>
                <?php
                // Check if user already has a pending application
                $check_stmt = $pdo->prepare("SELECT id FROM withdrawals WHERE user_id = ? AND status IN ('Pending', 'Saved') LIMIT 1");
                $check_stmt->execute([$_SESSION['user_id']]);
                $has_request = $check_stmt->fetch();
                ?>
                <div class="dropdown">
                    <button
                        class="btn <?php echo $has_request ? 'btn-info text-white' : 'btn-primary'; ?> dropdown-toggle rounded-pill px-4"
                        type="button" id="newRequestDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas <?php echo $has_request ? 'fa-edit' : 'fa-plus'; ?> me-2"></i>
                        <?php echo $has_request ? 'Kemaskini Permohonan' : 'New Request'; ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow rounded-3"
                        aria-labelledby="newRequestDropdown">
                        <li>
                            <h6 class="dropdown-header">
                                <?php echo $has_request ? 'Pilih Borang Untuk Dikemaskini' : 'Select Form Type'; ?></h6>
                        </li>
                        <li><a class="dropdown-item py-2" href="form.php?type=dekan"><i
                                    class="fas fa-file-signature me-2 text-primary"></i> Borang Dekan</a></li>
                        <li><a class="dropdown-item py-2" href="form.php?type=bendahari"><i
                                    class="fas fa-money-check-alt me-2 text-success"></i> Borang Bendahari</a></li>
                        <li><a class="dropdown-item py-2" href="form.php?type=pustakawan"><i
                                    class="fas fa-book-reader me-2 text-info"></i> Borang Pustakawan</a></li>
                        <li><a class="dropdown-item py-2" href="form.php?type=pengetua"><i
                                    class="fas fa-key me-2 text-warning"></i>
                                Borang Pengetua</a></li>
                        <li><a class="dropdown-item py-2" href="form.php?type=hep"><i
                                    class="fas fa-hand-holding-usd me-2 text-primary"></i> Borang HEP</a></li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>

        <?php if (($_SESSION['role'] ?? '') == 'admin'): ?>
            <div class="card p-3 mb-4">
                <form method="GET" class="row g-2">
                    <div class="col-md-10">
                        <input type="text" name="search" class="form-control" placeholder="Search by name or student ID..."
                            value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="submit" class="btn btn-primary">Search</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3">ID</th>
                                <th>Name</th>
                                <th>Student ID</th>
                                <th>Files Uploaded</th>
                                <th>Status</th>
                                <th>Submitted On</th>
                                <th class="text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($records) > 0): ?>
                                <?php foreach ($records as $record): ?>
                                    <tr>
                                        <td class="ps-3">
                                            <?php echo $record['id']; ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($record['name']); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($record['student_id']); ?>
                                        </td>
                                        <td>
                                            <?php
                                            $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM withdrawal_documents WHERE withdrawal_id = ?");
                                            $stmt_count->execute([$record['id']]);
                                            $doc_count = $stmt_count->fetchColumn();

                                            $badge_color = 'bg-secondary';
                                            if ($doc_count == 5) {
                                                $badge_color = 'bg-success';
                                            } elseif ($doc_count > 0) {
                                                $badge_color = 'bg-warning text-dark';
                                            }
                                            ?>
                                            <span class="badge <?php echo $badge_color; ?> px-3 py-2 rounded-pill">
                                                <i class="fas fa-file-upload me-1"></i> <?php echo $doc_count; ?> / 5
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $status = $record['status'] ?? 'Saved';
                                            $badge_class = 'warning';
                                            $display_status = $status;

                                            if ($status == 'Pending') {
                                                $display_status = 'Pending Approval';
                                            } elseif ($status == 'Approved') {
                                                $badge_class = 'success';
                                            } elseif ($status == 'Rejected') {
                                                $badge_class = 'danger';
                                            }
                                            ?>
                                            <span class="badge bg-<?php echo $badge_class; ?>">
                                                <?php echo $display_status; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo date('d M Y', strtotime($record['created_at'])); ?>
                                        </td>
                                        <td class="text-end pe-3">
                                            <div class="d-flex justify-content-end align-items-center gap-2">
                                                <!-- Documents -->
                                                <div class="d-flex gap-1 flex-wrap justify-content-end"
                                                    style="max-width: 120px;">
                                                    <?php
                                                    $stmt_docs = $pdo->prepare("SELECT * FROM withdrawal_documents WHERE withdrawal_id = ? ORDER BY uploaded_at ASC");
                                                    $stmt_docs->execute([$record['id']]);
                                                    $docs = $stmt_docs->fetchAll();
                                                    ?>

                                                    <?php if ($docs): ?>
                                                        <?php foreach ($docs as $doc): ?>
                                                            <a href="uploads/<?php echo $doc['file_path']; ?>" target="_blank"
                                                                class="btn btn-sm btn-outline-secondary" title="View Document">
                                                                <i class="fas fa-file-alt"></i>
                                                            </a>
                                                        <?php endforeach; ?>
                                                    <?php elseif (!empty($record['document_path'])): ?>
                                                        <a href="uploads/<?php echo $record['document_path']; ?>" target="_blank"
                                                            class="btn btn-sm btn-outline-secondary" title="View Document">
                                                            <i class="fas fa-file-alt"></i>
                                                        </a>
                                                    <?php endif; ?>

                                                    <a href="upload.php?id=<?php echo $record['id']; ?>"
                                                        class="btn btn-sm btn-primary" title="Upload Document">
                                                        <i class="fas fa-upload"></i>
                                                    </a>
                                                </div>

                                                <!-- Actions -->
                                                <div class="btn-group">
                                                    <a href="view.php?id=<?php echo $record['id']; ?>"
                                                        class="btn btn-sm btn-info text-white" title="View"><i
                                                            class="fas fa-eye"></i></a>

                                                    <a href="edit.php?id=<?php echo $record['id']; ?>"
                                                        class="btn btn-sm btn-warning" title="Edit"><i
                                                            class="fas fa-edit"></i></a>
                                                    <a href="delete.php?id=<?php echo $record['id']; ?>"
                                                        class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Are you sure you want to delete this record?')"
                                                        title="Delete"><i class="fas fa-trash"></i></a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No records found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
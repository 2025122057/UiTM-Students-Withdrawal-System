<?php
require_once 'includes/db.php';
require_once 'includes/auth_check.php';

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM withdrawals WHERE id = ?");
$stmt->execute([$id]);
$record = $stmt->fetch();

if (!$record || ($_SESSION['role'] != 'admin' && $record['user_id'] != $_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
                <h3 class="fw-bold m-0">Withdrawal Record #
                    <?php echo $record['id']; ?> | <span
                        class="text-primary small"><?php echo htmlspecialchars($record['form_type'] ?? 'N/A'); ?></span>
                    <?php
                    $status = $record['status'] ?? 'Saved';
                    $badge_class = 'warning';
                    $display_status = $status;
                    if ($status == 'Pending')
                        $display_status = 'Pending Approval';
                    elseif ($status == 'Approved')
                        $badge_class = 'success';
                    elseif ($status == 'Rejected')
                        $badge_class = 'danger';
                    ?>
                    <span class="badge bg-<?php echo $badge_class; ?> ms-2">
                        <?php echo $display_status; ?>
                    </span>
                </h3>
                <div>
                    <a href="edit.php?id=<?php echo $record['id']; ?>" class="btn btn-warning"><i
                            class="fas fa-edit me-2"></i>Edit</a>
                    <a href="export_pdf.php?id=<?php echo $record['id']; ?>" class="btn btn-success"><i
                            class="fas fa-file-pdf me-2"></i>PDF</a>
                    <a href="dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-12">
                    <label class="text-muted small text-uppercase">Student Name</label>
                    <div class="fw-bold">
                        <?php echo htmlspecialchars($record['name']); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small text-uppercase">Student ID</label>
                    <div class="fw-bold">
                        <?php echo htmlspecialchars($record['student_id']); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small text-uppercase">IC Number</label>
                    <div class="fw-bold">
                        <?php echo htmlspecialchars($record['ic_number']); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small text-uppercase">Program Code</label>
                    <div class="fw-bold">
                        <?php echo htmlspecialchars($record['program_code']); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small text-uppercase">Semester</label>
                    <div class="fw-bold">
                        <?php echo htmlspecialchars($record['semester']); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small text-uppercase">Phone</label>
                    <div class="fw-bold">
                        <?php echo htmlspecialchars($record['phone']); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small text-uppercase">Email</label>
                    <div class="fw-bold">
                        <?php echo htmlspecialchars($record['email']); ?>
                    </div>
                </div>

                <div class="col-md-12">
                    <label class="text-muted small text-uppercase">Reason for Withdrawal</label>
                    <div class="fw-bold text-primary">
                        <?php echo htmlspecialchars($record['reason']); ?>
                    </div>
                    <?php if ($record['other_reason']): ?>
                        <div class="mt-2 p-2 bg-light border-start border-4 border-primary">
                            <?php echo nl2br(htmlspecialchars($record['other_reason'])); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-md-12">
                    <label class="text-muted small text-uppercase">Address</label>
                    <div class="p-2 bg-light rounded">
                        <?php echo nl2br(htmlspecialchars($record['address'])); ?>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="text-muted small text-uppercase">Status</label>
                    <div>
                        <span
                            class="badge bg-<?php echo $record['status'] == 'Pending' ? 'warning' : 'success'; ?> fs-6">
                            <?php echo $record['status']; ?>
                        </span>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="text-muted small text-uppercase">Submitted On</label>
                    <div class="fw-bold">
                        <?php echo date('d F Y, h:i A', strtotime($record['created_at'])); ?>
                    </div>
                </div>

                <!-- Documents Section -->
                <div class="col-md-12 mt-4">
                    <label class="text-muted small text-uppercase">Supporting Documents</label>
                    <div class="mt-2">
                        <?php
                        $stmt_docs = $pdo->prepare("SELECT * FROM withdrawal_documents WHERE withdrawal_id = ? ORDER BY category ASC, uploaded_at ASC");
                        $stmt_docs->execute([$id]);
                        $docs = $stmt_docs->fetchAll();

                        $docs_by_category = [];
                        foreach ($docs as $doc) {
                            $cat = $doc['category'] ?? 'General';
                            $docs_by_category[$cat][] = $doc;
                        }
                        ?>

                        <?php if ($docs_by_category): ?>
                            <?php foreach ($docs_by_category as $category => $category_docs): ?>
                                <div class="mb-3">
                                    <h6 class="fw-bold text-primary border-bottom pb-1 small">
                                        <i class="fas fa-folder-open me-2"></i><?php echo htmlspecialchars($category); ?>
                                    </h6>
                                    <div class="row g-2">
                                        <?php foreach ($category_docs as $doc): ?>
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center p-2 border rounded bg-white shadow-sm">
                                                    <i class="fas fa-file-alt text-secondary me-2 fs-5"></i>
                                                    <div class="text-truncate me-2 small fw-semibold"
                                                        title="<?php echo htmlspecialchars($doc['original_name']); ?>">
                                                        <?php echo htmlspecialchars($doc['original_name']); ?>
                                                    </div>
                                                    <a href="uploads/<?php echo $doc['file_path']; ?>" target="_blank"
                                                        class="btn btn-sm btn-outline-primary ms-auto py-0 px-2"
                                                        style="font-size: 0.75rem;">View</a>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php elseif (!empty($record['document_path'])): ?>
                            <div class="mb-3">
                                <h6 class="fw-bold text-primary border-bottom pb-1 small">
                                    <i class="fas fa-folder-open me-2"></i>Legacy Document
                                </h6>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center p-2 border rounded bg-white shadow-sm">
                                            <i class="fas fa-file-alt text-secondary me-2 fs-5"></i>
                                            <div class="text-truncate me-2 small fw-semibold">Legacy Document</div>
                                            <a href="uploads/<?php echo $record['document_path']; ?>" target="_blank"
                                                class="btn btn-sm btn-outline-primary ms-auto py-0 px-2"
                                                style="font-size: 0.75rem;">View</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-muted small py-3 text-center border rounded bg-light">
                                <i class="fas fa-info-circle me-1"></i> No documents uploaded yet.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
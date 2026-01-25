<?php
require_once 'includes/db.php';
require_once 'includes/auth_check.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';

// Verify ownership
$stmt = $pdo->prepare("SELECT * FROM withdrawals WHERE id = ?");
$stmt->execute([$id]);
$withdrawal = $stmt->fetch();

if (!$withdrawal) {
    die("Record not found.");
}

if ($_SESSION['role'] != 'admin' && $withdrawal['user_id'] != $_SESSION['user_id']) {
    die("Unauthorized access.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_all'])) {
    $allowed_types = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
    $max_size = 5 * 1024 * 1024; // 5MB
    $upload_dir = 'uploads/';

    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $uploaded_count = 0;
    $failed_files = [];

    // Loop through the 5 possible slots
    for ($i = 0; $i < 5; $i++) {
        $key = "borang_" . $i;
        if (!isset($_FILES[$key]) || $_FILES[$key]['error'] === UPLOAD_ERR_NO_FILE) {
            continue;
        }

        $file = $_FILES[$key];
        $category = $_POST["category_" . $i] ?? 'General';

        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_size = $file['size'];
        $file_error = $file['error'];
        $file_type = $file['type'];

        if (!in_array($file_type, $allowed_types)) {
            $failed_files[] = "$file_name (Invalid type)";
            continue;
        }

        if ($file_size > $max_size) {
            $failed_files[] = "$file_name (Too large)";
            continue;
        }

        if ($file_error !== UPLOAD_ERR_OK) {
            $failed_files[] = "$file_name (Error: $file_error)";
            continue;
        }

        $ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $new_filename = 'withdrawal_' . $id . '_' . uniqid() . '.' . $ext;
        $filepath = $upload_dir . $new_filename;

        if (move_uploaded_file($file_tmp, $filepath)) {
            // Insert into withdrawal_documents table
            $stmt = $pdo->prepare("INSERT INTO withdrawal_documents (withdrawal_id, file_path, original_name, category) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$id, $new_filename, $file_name, $category])) {
                $uploaded_count++;

                // Update the main table for backward compatibility (latest file)
                $stmt = $pdo->prepare("UPDATE withdrawals SET document_path = ? WHERE id = ?");
                $stmt->execute([$new_filename, $id]);
            }
        } else {
            $failed_files[] = "$file_name (Failed to move)";
        }
    }

    if ($uploaded_count > 0) {
        $success = "Successfully uploaded $uploaded_count document(s).";
        if (!empty($failed_files)) {
            $error = "Some files failed: " . implode(", ", $failed_files);
        }
    } elseif (!empty($failed_files)) {
        $error = "No files were uploaded. " . implode(", ", $failed_files);
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['finalize'])) {
    $stmt = $pdo->prepare("UPDATE withdrawals SET status = 'Pending' WHERE id = ?");
    if ($stmt->execute([$id])) {
        header("Location: dashboard.php?msg=pending");
        exit();
    } else {
        $error = "Failed to finalize submission.";
    }
}

include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-primary text-white p-4">
                <h4 class="fw-bold mb-0">
                    <i class="fas fa-file-upload me-2"></i> Upload Withdrawal Documents
                </h4>
            </div>
            <div class="card-body p-4">
                <?php if (isset($_GET['msg']) && $_GET['msg'] == 'saved'): ?>
                    <div class="alert alert-success border-0 shadow-sm mb-4">
                        <i class="fas fa-check-circle me-2"></i> Maklumat borang telah disimpan. Sila muat naik dokumen
                        sokongan di bawah.
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger border-0 shadow-sm mb-4">
                        <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success border-0 shadow-sm mb-4">
                        <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <div class="mb-4 text-center">
                    <h5 class="fw-bold text-dark mb-2">Muat Naik Borang Sokongan</h5>
                    <p class="text-muted small">Sila muat naik borang yang telah lengkap di ruangan yang disediakan.</p>
                </div>

                <form method="POST" enctype="multipart/form-data" class="mb-4">
                    <div class="row g-4">
                        <?php
                        $borang_types = [
                            "Borang Dekan",
                            "Borang Bendahari",
                            "Borang Ketua Pustakawan",
                            "Borang Pengetua Kolej",
                            "Borang Pengarah Ko-Kurikulum HEP"
                        ];

                        foreach ($borang_types as $index => $type):
                            ?>
                            <div class="col-md-12">
                                <div class="p-3 border rounded-4 bg-light shadow-sm">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label fw-bold mb-0 text-primary">
                                            <i class="fas fa-file-alt me-2"></i><?php echo $type; ?>
                                        </label>
                                        <?php
                                        // Check if this category already has a document uploaded
                                        $check_stmt = $pdo->prepare("SELECT id FROM withdrawal_documents WHERE withdrawal_id = ? AND category = ?");
                                        $check_stmt->execute([$id, $type]);
                                        $has_doc = $check_stmt->fetch();
                                        if ($has_doc):
                                            ?>
                                            <span class="badge bg-success"><i class="fas fa-check me-1"></i> Uploaded</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="input-group">
                                        <input type="file" name="borang_<?php echo $index; ?>" class="form-control"
                                            accept=".pdf,.jpg,.jpeg,.png">
                                        <input type="hidden" name="category_<?php echo $index; ?>"
                                            value="<?php echo $type; ?>">
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" name="upload_all"
                            class="btn btn-primary btn-lg px-5 py-3 fw-bold shadow rounded-pill">
                            <i class="fas fa-cloud-upload-alt me-2"></i> Muat Naik Semua Fail
                        </button>
                    </div>
                </form>

                <hr class="my-5">

                <div class="text-center">
                    <form method="POST" onsubmit="return confirmFinalize(event)">
                        <input type="hidden" name="finalize" value="1">
                        <div class="d-grid gap-3">
                            <button type="submit" class="btn btn-success btn-lg py-3 fw-bold shadow-sm rounded-4">
                                <i class="fas fa-paper-plane me-2"></i> Hantar Permohonan (Finalize Submission)
                            </button>
                            <a href="dashboard.php" class="btn btn-outline-secondary btn-lg py-3 rounded-4">
                                <i class="fas fa-arrow-left me-2"></i> Kembali ke Dashboard
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
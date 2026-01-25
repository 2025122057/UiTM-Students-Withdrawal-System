<?php
function processUpload($pdo, $withdrawal_id, $file)
{
    if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_type = $file['type'];
        $file_size = $file['size'];

        // Validate file type (Image or PDF)
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        if (in_array($file_type, $allowed_types)) {
            // Validate file size (e.g., max 5MB)
            if ($file_size <= 5 * 1024 * 1024) {
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $new_filename = uniqid('doc_', true) . '.' . $file_ext;
                $target_path = $upload_dir . $new_filename;

                if (move_uploaded_file($file_tmp, $target_path)) {
                    // Update Database (New Table)
                    $stmt = $pdo->prepare("INSERT INTO withdrawal_documents (withdrawal_id, file_path, original_name) VALUES (?, ?, ?)");
                    $stmt->execute([$withdrawal_id, $new_filename, $file_name]);
                }
            }
        }
    }
}
?>
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

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $stmt = $pdo->prepare("UPDATE withdrawals SET 
            name = ?, student_id = ?, ic_number = ?, program_code = ?, 
            semester = ?, phone = ?, email = ?, reason = ?, 
            other_reason = ?, address = ?, status = ? 
            WHERE id = ?");

        $stmt->execute([
            $_POST['name'],
            $_POST['student_id'],
            $_POST['ic_number'],
            $_POST['program_code'],
            $_POST['semester'],
            $_POST['phone'],
            $_POST['email'],
            $_POST['reason'],
            $_POST['other_reason'] ?? '',
            $_POST['address'],
            $_POST['status'] ?? $record['status'],
            $id
        ]);

        $success = "Maklumat telah berjaya dikemaskini!";
        // Refresh record data
        $stmt = $pdo->prepare("SELECT * FROM withdrawals WHERE id = ?");
        $stmt->execute([$id]);
        $record = $stmt->fetch();
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-9">
        <div class="card p-4 border-0 shadow-sm rounded-4">
            <div class="form-header text-center mb-4">
                <i class="fas fa-edit text-warning mb-3" style="font-size: 3rem;"></i>
                <h2 class="fw-bold text-warning">KEMASKINI MAKLUMAT</h2>
                <p class="text-muted">Sila betulkan sebarang kesilapan maklumat di bawah.</p>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success border-0 shadow-sm mb-4 animate__animated animate__fadeIn">
                    <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
                    <a href="dashboard.php" class="alert-link ms-2">Kembali ke Dashboard</a>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger border-0 shadow-sm mb-4">
                    <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="row g-3" onsubmit="return confirmUpdate(event)">
                <div class="form-section-title border-bottom pb-2 mb-3 fw-bold text-warning">
                    <i class="fas fa-user-graduate me-2"></i>A. MAKLUMAT PELAJAR
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-semibold">NAMA PENUH</label>
                    <input type="text" name="name" class="form-control form-control-lg bg-light border-0 shadow-none"
                        required value="<?php echo htmlspecialchars($record['name']); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">NO. PELAJAR UiTM</label>
                    <input type="text" name="student_id"
                        class="form-control form-control-lg bg-light border-0 shadow-none" required
                        value="<?php echo htmlspecialchars($record['student_id']); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">NO. KAD PENGENALAN</label>
                    <input type="text" name="ic_number"
                        class="form-control form-control-lg bg-light border-0 shadow-none" required
                        value="<?php echo htmlspecialchars($record['ic_number']); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">KOD PROGRAM</label>
                    <input type="text" name="program_code"
                        class="form-control form-control-lg bg-light border-0 shadow-none" required
                        value="<?php echo htmlspecialchars($record['program_code']); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">SEMESTER/BAHAGIAN</label>
                    <input type="number" name="semester"
                        class="form-control form-control-lg bg-light border-0 shadow-none" required min="1"
                        value="<?php echo htmlspecialchars($record['semester']); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">NO. TELEFON</label>
                    <input type="text" name="phone" class="form-control form-control-lg bg-light border-0 shadow-none"
                        required value="<?php echo htmlspecialchars($record['phone']); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">E-MEL</label>
                    <input type="email" name="email" class="form-control form-control-lg bg-light border-0 shadow-none"
                        required value="<?php echo htmlspecialchars($record['email']); ?>">
                </div>

                <div class="form-section-title border-bottom pb-2 mb-3 mt-4 fw-bold text-warning">
                    <i class="fas fa-question-circle me-2"></i>B. SEBAB MENARIK DIRI
                </div>
                <div class="col-md-12">
                    <div class="row">
                        <?php
                        $reasons = [
                            "Tidak minat bidang pengajian",
                            "Masalah kewangan",
                            "Bertukar Universiti/Kolej",
                            "Masalah kesihatan",
                            "Masalah peribadi",
                            "Mendapat pekerjaan"
                        ];
                        foreach ($reasons as $index => $reason): ?>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="form-check p-3 border rounded shadow-sm bg-white hover-shadow transition">
                                    <input class="form-check-input" type="radio" name="reason"
                                        value="<?php echo $reason; ?>" id="reason<?php echo $index; ?>" required <?php echo $record['reason'] == $reason ? 'checked' : ''; ?>>
                                    <label class="form-check-label ms-2" for="reason<?php echo $index; ?>">
                                        <?php echo $reason; ?>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="col-md-12">
                            <div class="form-check p-3 border rounded shadow-sm bg-white">
                                <input class="form-check-input" type="radio" name="reason" value="Lain-lain"
                                    id="reasonOther" required <?php echo !in_array($record['reason'], $reasons) ? 'checked' : ''; ?>>
                                <label class="form-check-label ms-2 fw-bold" for="reasonOther">Lain-lain sebab
                                    (nyatakan):</label>
                                <textarea name="other_reason" class="form-control mt-2 border-0 bg-light shadow-none"
                                    rows="3"><?php echo htmlspecialchars($record['other_reason']); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section-title border-bottom pb-2 mb-3 mt-4 fw-bold text-warning">
                    <i class="fas fa-map-marker-alt me-2"></i>C. ALAMAT SURAT-MENYURAT
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-semibold">ALAMAT PENUH</label>
                    <textarea name="address" class="form-control form-control-lg bg-light border-0 shadow-none" required
                        rows="3"><?php echo htmlspecialchars($record['address']); ?></textarea>
                </div>

                <?php if ($_SESSION['role'] == 'admin'): ?>
                    <div class="form-section-title border-bottom pb-2 mb-3 mt-4 fw-bold text-warning">
                        <i class="fas fa-user-shield me-2"></i>D. ADMIN ACTIONS
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">STATUS PERMOHONAN</label>
                        <select name="status" class="form-select form-select-lg bg-light border-0 shadow-none">
                            <option value="Pending" <?php echo $record['status'] == 'Pending' ? 'selected' : ''; ?>>Pending
                            </option>
                            <option value="Approved" <?php echo $record['status'] == 'Approved' ? 'selected' : ''; ?>>Approved
                            </option>
                            <option value="Rejected" <?php echo $record['status'] == 'Rejected' ? 'selected' : ''; ?>>Rejected
                            </option>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="col-md-12 text-center mt-5">
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <button type="submit"
                            class="btn btn-warning btn-lg px-5 py-3 fw-bold rounded-pill shadow text-dark">
                            <i class="fas fa-save me-2"></i> Kemaskini Maklumat
                        </button>
                        <a href="dashboard.php"
                            class="btn btn-light btn-lg px-5 py-3 fw-bold rounded-pill text-secondary">Batal</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .hover-shadow:hover {
        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
        border-color: #ffc107 !important;
    }

    .transition {
        transition: all 0.3s ease;
    }
</style>

<script>
    function confirmUpdate(event) {
        event.preventDefault();
        const form = event.target;

        Swal.fire({
            title: 'Kemaskini Rekod?',
            text: 'Adakah anda pasti ingin menyimpan perubahan ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Kemaskini',
            cancelButtonText: 'Batal',
            color: '#000',
            borderRadius: '15px'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });

        return false;
    }

    document.querySelectorAll('input[name="reason"]').forEach(radio => {
        radio.addEventListener('change', function () {
            const otherText = document.querySelector('textarea[name="other_reason"]');
            if (this.value === 'Lain-lain') {
                otherText.required = true;
                otherText.focus();
            } else {
                otherText.required = false;
            }
        });
    });
</script>

<?php include 'includes/footer.php'; ?>
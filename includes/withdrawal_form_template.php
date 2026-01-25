<?php
// includes/withdrawal_form_template.php

require_once 'includes/db.php';
require_once 'includes/auth_check.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Restricted: Admins cannot access the student application form
if (($_SESSION['role'] ?? '') == 'admin') {
    header("Location: admin_dashboard.php");
    exit();
}

// Fetch user details for auto-fill
$stmt_user = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt_user->execute([$_SESSION['user_id']]);
$user_data = $stmt_user->fetch();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Check if student already has a record that is not yet fully processed (Pending/Saved)
        // We allow updating the same record regardless of which form they are currently filling
        $check_stmt = $pdo->prepare("SELECT id FROM withdrawals WHERE user_id = ? AND status IN ('Pending', 'Saved') LIMIT 1");
        $check_stmt->execute([$_SESSION['user_id']]);
        $existing_record = $check_stmt->fetch();

        if ($existing_record) {
            $withdrawal_id = $existing_record['id'];
            $stmt = $pdo->prepare("UPDATE withdrawals SET 
                name = ?, student_id = ?, ic_number = ?, program_code = ?, 
                semester = ?, phone = ?, email = ?, reason = ?, 
                other_reason = ?, address = ?, form_type = ?
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
                "Student Withdrawal application",
                $withdrawal_id
            ]);
        } else {
            // First time applying
            $stmt = $pdo->prepare("INSERT INTO withdrawals (user_id, name, student_id, ic_number, program_code, semester, phone, email, reason, other_reason, address, form_type) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $stmt->execute([
                $_SESSION['user_id'],
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
                "Student Withdrawal application"
            ]);
            $withdrawal_id = $pdo->lastInsertId();
        }

        header("Location: upload.php?id=" . $withdrawal_id . "&msg=saved");
        exit();

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
                <i class="fas <?php echo $form_icon; ?> <?php echo $theme_color_text; ?> mb-3"
                    style="font-size: 3rem;"></i>
                <h2 class="fw-bold <?php echo $theme_color_text; ?>">
                    <?php echo $form_title; ?>
                </h2>
                <p class="text-muted text-uppercase fw-bold">
                    <?php echo $form_subtitle; ?>
                </p>

                <?php if (isset($extra_note)): ?>
                    <div class="alert <?php echo $extra_note_class; ?> border-0 shadow-sm rounded-4 mb-4">
                        <p class="mb-0 fw-bold">
                            <?php echo $extra_note; ?>
                        </p>
                    </div>
                <?php endif; ?>

                <p class="text-muted small">Sila lengkapkan maklumat peribadi anda di bawah sebelum memuat naik borang
                    sokongan.</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger border-0 shadow-sm">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="row g-3" onsubmit="confirmAndPrint(event)">
                <div class="form-section-title border-bottom pb-2 mb-3 fw-bold <?php echo $theme_color_text; ?>">
                    <i class="fas fa-user-graduate me-2"></i>A. MAKLUMAT PELAJAR
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-semibold">NAMA PENUH</label>
                    <input type="text" name="name" class="form-control form-control-lg bg-light border-0 shadow-none"
                        required value="<?php echo htmlspecialchars($user_data['full_name'] ?? ''); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">NO. PELAJAR UiTM</label>
                    <input type="text" name="student_id"
                        class="form-control form-control-lg bg-light border-0 shadow-none" required
                        value="<?php echo htmlspecialchars($user_data['student_id'] ?? ''); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">NO. KAD PENGENALAN</label>
                    <input type="text" name="ic_number"
                        class="form-control form-control-lg bg-light border-0 shadow-none" required
                        value="<?php echo htmlspecialchars($user_data['ic_number'] ?? ''); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">KOD PROGRAM</label>
                    <input type="text" name="program_code"
                        class="form-control form-control-lg bg-light border-0 shadow-none" required
                        placeholder="Contoh: CS240">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">SEMESTER/BAHAGIAN</label>
                    <input type="number" name="semester"
                        class="form-control form-control-lg bg-light border-0 shadow-none" required min="1">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">NO. TELEFON</label>
                    <input type="text" name="phone" class="form-control form-control-lg bg-light border-0 shadow-none"
                        required value="<?php echo htmlspecialchars($user_data['phone'] ?? ''); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">E-MEL</label>
                    <input type="email" name="email" class="form-control form-control-lg bg-light border-0 shadow-none"
                        required value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>">
                </div>

                <div class="form-section-title border-bottom pb-2 mb-3 mt-4 fw-bold <?php echo $theme_color_text; ?>">
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
                                        value="<?php echo $reason; ?>" id="reason<?php echo $index; ?>" required>
                                    <label class="form-check-label ms-2" for="reason<?php echo $index; ?>">
                                        <?php echo $reason; ?>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="col-md-12">
                            <div class="form-check p-3 border rounded shadow-sm bg-white">
                                <input class="form-check-input" type="radio" name="reason" value="Lain-lain"
                                    id="reasonOther" required>
                                <label class="form-check-label ms-2 fw-bold" for="reasonOther">Lain-lain sebab
                                    (nyatakan):</label>
                                <textarea name="other_reason" class="form-control mt-2 border-0 bg-light shadow-none"
                                    rows="3" placeholder="Sila nyatakan sebab anda..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION C -->
                <div class="form-section-title border-bottom pb-2 mb-3 mt-4 fw-bold <?php echo $theme_color_text; ?>">
                    <i class="fas fa-map-marker-alt me-2"></i>C. ALAMAT SURAT-MENYURAT
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-semibold">ALAMAT PENUH</label>
                    <textarea name="address" class="form-control form-control-lg bg-light border-0 shadow-none" required
                        rows="3" placeholder="Sila masukkan alamat penuh anda..."></textarea>
                </div>

                <!-- SECTION D -->
                <div class="form-section-title border-bottom pb-2 mb-3 mt-4 fw-bold <?php echo $theme_color_text; ?>">
                    <i class="fas fa-clipboard-check me-2"></i>D. PENGESAHAN KAKITANGAN (KEGUNAAN PEJABAT)
                </div>
                <div class="col-md-12">
                    <div class="p-4 border rounded-4 bg-light bg-opacity-50">
                        <div class="row g-4">
                            <div class="col-md-12">
                                <label class="form-label fw-bold text-muted small text-uppercase">CATATAN /
                                    ULASAN</label>
                                <div class="p-3 bg-white border rounded-3"
                                    style="min-height: 80px; border-style: dashed !important;">
                                    <span class="text-muted small italic">Untuk kegunaan kakitangan sahaja...</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mt-3">
                                    <label class="form-label fw-bold text-muted small text-uppercase">NAMA & TANDATANGAN
                                        PEGAWAI</label>
                                    <div class="border-bottom border-secondary mb-2" style="height: 40px;"></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mt-3">
                                    <label class="form-label fw-bold text-muted small text-uppercase">TARIKH</label>
                                    <div class="border-bottom border-secondary mb-2" style="height: 40px;"></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mt-3 text-center">
                                    <label class="form-label fw-bold text-muted small text-uppercase">COP RASMI</label>
                                    <div class="border border-secondary mx-auto rounded"
                                        style="width: 100px; height: 100px; border-style: dashed !important;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 text-center mt-5">
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <button type="submit"
                            class="btn <?php echo $theme_color_btn; ?> btn-lg px-5 py-3 fw-bold rounded-pill shadow <?php echo $theme_color_btn_text ?? ''; ?>">
                            <i class="fas fa-print me-2"></i> Cetak & Teruskan
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
        <?php
        $base_theme = str_replace(['text-', 'btn-'], '', $theme_color_text);
        ?>
        border-color: var(--bs-<?php echo $base_theme; ?>) !important;
    }

    .transition {
        transition: all 0.3s ease;
    }
</style>

<script>
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

    function confirmAndPrint(event) {
        event.preventDefault();
        const form = event.target;

        Swal.fire({
            title: 'Cetak & Hantar?',
            text: 'Adakah anda ingin mencetak borang ini dan menghantar maklumat anda?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#5a2e8a',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Cetak & Hantar',
            cancelButtonText: 'Batal',
            borderRadius: '15px'
        }).then((result) => {
            if (result.isConfirmed) {
                // Short delay to allow SweetAlert to close before printing
                setTimeout(() => {
                    window.print();
                    // Submit the form after the print dialog is closed
                    form.submit();
                }, 500);
            }
        });

        return false;
    }
</script>

<style>
    @media print {

        /* Hide non-essential elements */
        .navbar,
        .footer,
        .btn,
        .bg-circles,
        .form-header i,
        .text-muted.small,
        .alert,
        .bg-circles {
            display: none !important;
        }

        body {
            background-color: white !important;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100% !important;
            max-width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .card {
            border: none !important;
            box-shadow: none !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            background: white !important;
        }

        /* Adjust spacing for print */
        .row {
            --bs-gutter-x: 0;
        }

        /* Section titles */
        .form-section-title {
            border-bottom: 2px solid #000 !important;
            color: #000 !important;
            margin-top: 30px !important;
            font-weight: bold !important;
        }

        /* Inputs and Textareas */
        input.form-control,
        textarea.form-control {
            border: none !important;
            border-bottom: 1px dotted #ccc !important;
            background-color: transparent !important;
            padding: 5px 0 !important;
            color: black !important;
            box-shadow: none !important;
            border-radius: 0 !important;
        }

        /* Labels */
        .form-label {
            color: #333 !important;
            font-size: 0.85rem !important;
            margin-bottom: 2px !important;
        }

        /* Radio reasons layout */
        .form-check {
            border: none !important;
            box-shadow: none !important;
            background: transparent !important;
            padding: 0 !important;
            margin-bottom: 5px !important;
        }

        .form-check-label {
            font-size: 0.9rem !important;
        }

        /* Office use section */
        .bg-light.bg-opacity-50 {
            background-color: transparent !important;
            border: 1px solid #ddd !important;
        }

        .italic {
            font-style: italic;
        }
    }
</style>

<?php include 'includes/footer.php'; ?>
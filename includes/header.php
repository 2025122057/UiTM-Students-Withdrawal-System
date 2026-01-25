<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Withdrawal System - IMS566</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <!-- SweetAlert2 CSS & JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <nav class="navbar navbar-expand-lg mb-4">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-university me-2"></i> UiTM Withdrawal
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-content="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if (($_SESSION['role'] ?? '') == 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link fw-bold text-warning" href="admin_dashboard.php"><i
                                        class="fas fa-user-shield me-1"></i> Admin Panel</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php"><i class="fas fa-columns me-1"></i> Dashboard</a>
                        </li>
                        <?php if (($_SESSION['role'] ?? '') != 'admin'): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-file-alt me-1"></i> Borang
                                </a>
                                <ul class="dropdown-menu border-0 shadow-sm rounded-3" aria-labelledby="navbarDropdown">
                                    <li><a class="dropdown-item py-2" href="form.php?type=dekan"><i
                                                class="fas fa-file-signature me-2 text-primary"></i> Borang Dekan</a></li>
                                    <li><a class="dropdown-item py-2" href="form.php?type=bendahari"><i
                                                class="fas fa-money-check-alt me-2 text-success"></i> Borang Bendahari</a></li>
                                    <li><a class="dropdown-item py-2" href="form.php?type=pustakawan"><i
                                                class="fas fa-book-reader me-2 text-info"></i> Borang Pustakawan</a></li>
                                    <li><a class="dropdown-item py-2" href="form.php?type=pengetua"><i
                                                class="fas fa-key me-2 text-warning"></i> Borang Pengetua</a></li>
                                    <li><a class="dropdown-item py-2" href="form.php?type=hep"><i
                                                class="fas fa-hand-holding-usd me-2 text-primary"></i> Borang HEP</a></li>
                                </ul>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i> Logout
                                (<?php echo $_SESSION['username'] ?? 'User'; ?>)</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php"><i class="fas fa-sign-in-alt me-1"></i> Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container content">

        <script>
            function confirmSubmission(event) {
                event.preventDefault();
                const form = event.target;

                Swal.fire({
                    title: 'Are you sure?',
                    text: "Are you sure you want to save this file?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#0d6efd',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Save it!',
                    cancelButtonText: 'Cancel',
                    borderRadius: '15px'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });

                return false;
            }

            function confirmFinalize(event) {
                event.preventDefault();
                const form = event.target;

                Swal.fire({
                    title: 'TINDAKAN MUKTAMAD!',
                    text: "Adakah anda benar-benar pasti? Selepas dihantar, permohonan anda tidak boleh dipinda lagi dan akan diproses secara rasmi.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'YA, SAYA PASTI. HANTAR SEKARANG!',
                    cancelButtonText: 'Semak Semula',
                    customClass: {
                        popup: 'animate__animated animate__shakeX',
                        title: 'text-danger fw-black'
                    },
                    backdrop: `rgba(255,0,0,0.1)`,
                    allowOutsideClick: false,
                    allowEscapeKey: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Memproses...',
                            text: 'Sila tunggu sebentar sementara permohonan anda dihantar.',
                            icon: 'info',
                            showConfirmButton: false,
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                                form.submit();
                            }
                        });
                    }
                });

                return false;
            }
        </script>
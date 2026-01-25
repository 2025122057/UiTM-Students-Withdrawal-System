<?php
require_once 'includes/db.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - UiTM Withdrawal System</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            margin: 0;
            padding: 0;
        }
    </style>
</head>

<body>
    <div class="login-wrapper">
        <!-- Background Decorative Circles -->
        <div class="bg-circles">
            <div class="circle circle-1"></div>
            <div class="circle circle-2"></div>
            <div class="circle circle-3"></div>
        </div>

        <div class="login-container">
            <!-- Left Side: Information -->
            <div class="login-left">
                <h1>Student<br>Withdrawal.</h1>
                <h2>UiTM IMS566 System</h2>
                <p>
                    This system facilitates UiTM students in managing the withdrawal process from studies systematically
                    and quickly.
                    Please log in using your student number and password to proceed with the application.
                </p>
                <a href="#" class="btn-learn">Learn More</a>
            </div>

            <!-- Right Side: Login Form -->
            <div class="login-right">
                <div class="login-card-header">
                    <h3>UiTM IMS566</h3>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 small">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="input-group-custom">
                        <i class="fas fa-user"></i>
                        <input type="text" name="username" placeholder="ENTER USERNAME" required>
                    </div>

                    <div class="input-group-custom">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" placeholder="ENTER PASSWORD" required>
                    </div>

                    <button type="submit" class="btn-login">Login</button>

                    <div class="text-center mt-4 pt-2">
                        <span class="text-muted small">Don't have an account?</span>
                        <a href="register.php" class="text-primary text-decoration-none fw-bold small">Register here</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const btnLearn = document.querySelector('.btn-learn');
            if (btnLearn) {
                btnLearn.addEventListener('click', function (e) {
                    e.preventDefault();
                    Swal.fire({
                        title: '<h3 class="fw-bold mb-0">How to Use the System</h3>',
                        html: `
                            <div class="text-start p-2">
                                <div class="instruction-step mb-3">
                                    <h6 class="fw-bold text-primary mb-1"><i class="fas fa-user-plus me-2"></i>Step 1: Registration</h6>
                                    <p class="text-muted small mb-0">New users should click "Register here" to create an account using their Student ID and IC number.</p>
                                </div>
                                <div class="instruction-step mb-3">
                                    <h6 class="fw-bold text-primary mb-1"><i class="fas fa-sign-in-alt me-2"></i>Step 2: Secure Login</h6>
                                    <p class="text-muted small mb-0">Log in with your registered Student ID and password to access your personalized dashboard.</p>
                                </div>
                                <div class="instruction-step mb-3">
                                    <h6 class="fw-bold text-primary mb-1"><i class="fas fa-file-invoice me-2"></i>Step 3: Resource Selection</h6>
                                    <p class="text-muted small mb-0">Navigate to the "New Request" dropdown on your dashboard to select the specific withdrawal form required.</p>
                                </div>
                                <div class="instruction-step mb-3">
                                    <h6 class="fw-bold text-primary mb-1"><i class="fas fa-cloud-upload-alt me-2"></i>Step 4: Submission & Finalization</h6>
                                    <p class="text-muted small mb-2">Complete the digital form, save your progress, and upload necessary supporting documents.</p>
                                    <p class="text-danger small fw-bold mb-0"><i class="fas fa-exclamation-triangle me-1"></i>Crucial: You MUST click "Hantar Permohonan (Finalize Submission)" to complete the process.</p>
                                </div>
                            </div>
                        `,
                        icon: 'info',
                        confirmButtonText: 'Got it!',
                        confirmButtonColor: '#6f42c1',
                        buttonsStyling: true,
                        customClass: {
                            confirmButton: 'btn btn-primary px-5 rounded-pill shadow-sm'
                        },
                        showClass: {
                            popup: 'animate__animated animate__fadeInUp'
                        },
                        hideClass: {
                            popup: 'animate__animated animate__fadeOutDown'
                        }
                    });
                });
            }
        });
    </script>
</body>

</html>
<?php
require_once 'includes/db.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = $_POST['full_name'];
    $student_id = $_POST['student_id'];
    $ic_number = $_POST['ic_number'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if username exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = "Username already exists.";
        } else {
            // Hash password and insert
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role, full_name, student_id, ic_number, phone, email) VALUES (?, ?, 'student', ?, ?, ?, ?, ?)");
            if ($stmt->execute([$username, $hashed_password, $full_name, $student_id, $ic_number, $phone, $email])) {
                $success = "Registration successful! You can now <a href='login.php'>login</a>.";
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - UiTM Withdrawal System</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            margin: 0;
            padding: 0;
        }

        .login-right {
            width: 550px;
            /* Slightly wider for more fields */
            padding: 3rem;
        }

        .input-group-custom {
            margin-bottom: 1.2rem;
        }

        .login-container {
            max-width: 1200px;
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
                <h1>Join<br>Us.</h1>
                <h2>UiTM IMS566 System</h2>
                <p>
                    Please provide your accurate student details to ensure your withdrawal process is handled correctly.
                    Your information will be used to pre-fill your forms automatically.
                </p>
                <a href="login.php" class="btn-learn">Back to Login</a>
            </div>

            <!-- Right Side: Register Form -->
            <div class="login-right">
                <div class="login-card-header">
                    <h3>Register Account</h3>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 small mb-3">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success py-3 text-center">
                        <i class="fas fa-check-circle me-2 fs-4 d-block mb-2"></i>
                        <?php echo $success; ?>
                    </div>
                <?php else: ?>
                    <form method="POST">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="input-group-custom">
                                    <i class="fas fa-user-circle"></i>
                                    <input type="text" name="username" placeholder="USERNAME" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group-custom">
                                    <i class="fas fa-id-badge"></i>
                                    <input type="text" name="full_name" placeholder="FULL NAME" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group-custom">
                                    <i class="fas fa-graduation-cap"></i>
                                    <input type="text" name="student_id" placeholder="STUDENT ID" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group-custom">
                                    <i class="fas fa-id-card"></i>
                                    <input type="text" name="ic_number" placeholder="IC NUMBER" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group-custom">
                                    <i class="fas fa-phone"></i>
                                    <input type="text" name="phone" placeholder="PHONE NUMBER" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group-custom">
                                    <i class="fas fa-envelope"></i>
                                    <input type="email" name="email" placeholder="EMAIL ADDRESS" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group-custom">
                                    <i class="fas fa-lock"></i>
                                    <input type="password" name="password" placeholder="PASSWORD" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group-custom">
                                    <i class="fas fa-shield-alt"></i>
                                    <input type="password" name="confirm_password" placeholder="CONFIRM" required>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn-login">Create Account</button>

                        <div class="text-center mt-3">
                            <span class="text-muted small">Already have an account?</span>
                            <a href="login.php" class="text-primary text-decoration-none fw-bold small">Login here</a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
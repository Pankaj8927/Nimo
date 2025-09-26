<?php
session_start();
require_once "db_connect.php"; // Assuming you have this file from previous examples

$db = Database::getInstance();

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        $stmt = $db->prepare("
            INSERT INTO users (username, email, password) 
            VALUES (:username, :email, :password)
        ");
        $stmt->execute([
            'username' => $username,
            'email' => $email,
            'password' => $password
        ]);
        $success_message = "Account created successfully for $username! Please login.";
    } catch(PDOException $e) {
        $error_message = $e->getCode() == 23000 ? "Username or email already exists" : "Registration failed";
    }
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    try {
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header("Location: index.php");
            exit;
        } else {
            $error_message = "Invalid credentials";
        }
    } catch(PDOException $e) {
        $error_message = "Login failed";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SocialAuth</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Your existing CSS remains the same */
        :root {
            --primary-color: #1877f2;
            --secondary-color: #e4e6eb;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', sans-serif;
        }

        .auth-container {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            /* transform-style: preserve-3d; */
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
         } 

        .auth-container:hover {
            transform: translateY(-5px) rotateX(1deg) rotateY(1deg);
        }

        .auth-header {
            background: var(--primary-color);
            padding: 2.5rem;
            color: white;
            text-align: center;
            clip-path: polygon(0 0, 100% 0, 100% 85%, 0 100%);
        }

        .form-toggle {
            position: absolute;
            top: 20px;
            right: 20px;
            cursor: pointer;
            color: var(--primary-color);
            transition: transform 0.3s ease;
        }

        .form-toggle:hover {
            transform: scale(1.1);
        }

        .social-btn {
            transition: all 0.3s ease;
            border: 2px solid var(--secondary-color);
            border-radius: 12px;
            padding: 12px 20px;
        }

        .social-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .password-container {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-section {
            animation: slideIn 0.6s ease-out;
        }

        .progress-bar {
            height: 4px;
            transition: width 0.3s ease;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="auth-container">
                    <!-- Login Form -->
                    <div class="login-form" id="loginSection">
                        <div class="auth-header">
                            <h2 class="display-5 mb-3">Welcome Back!</h2>
                            <p class="lead">Connect with your community</p>
                        </div>
                        <div class="p-4 position-relative">
                            <span class="form-toggle" onclick="toggleForms()">Sign Up →</span>
                            <?php if(isset($error_message) && !isset($_POST['register'])): ?>
                                <div class="alert alert-danger"><?php echo $error_message; ?></div>
                            <?php endif; ?>
                            <?php if(isset($success_message)): ?>
                                <div class="alert alert-success"><?php echo $success_message; ?></div>
                            <?php endif; ?>
                            <form id="loginForm" method="POST">
                                <input type="hidden" name="login" value="1">
                                <div class="mb-4">
                                    <input type="email" name="email" class="form-control form-control-lg" 
                                        placeholder="Email" required>
                                </div>
                                <div class="mb-4 password-container">
                                    <input type="password" name="password" id="loginPassword" 
                                        class="form-control form-control-lg" placeholder="Password" required>
                                    <i class="fas fa-eye password-toggle" onclick="togglePassword('loginPassword')"></i>
                                </div>
                                <button type="submit" class="btn btn-primary btn-lg w-100 mb-4">
                                    Login <i class="fas fa-sign-in-alt"></i>
                                </button>
                                <div class="text-center mb-4">
                                    <a href="#forgotPassword" class="text-decoration-none" data-bs-toggle="modal">
                                        Forgot Password?
                                    </a>
                                </div>
                                <div class="social-login text-center">
                                    <div class="d-grid gap-2">
                                        <button type="button" class="btn social-btn btn-light">
                                            <i class="fab fa-google text-danger"></i> Continue with Google
                                        </button>
                                        <button type="button" class="btn social-btn btn-light">
                                            <i class="fab fa-facebook text-primary"></i> Continue with Facebook
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Signup Form -->
                    <div class="register-form d-none" id="registerSection">
                        <div class="auth-header bg-success">
                            <h2 class="display-5 mb-3">Join Us!</h2>
                            <p class="lead">Start your social journey</p>
                        </div>
                        <div class="p-4 position-relative">
                            <span class="form-toggle" onclick="toggleForms()">← Login</span>
                            <?php if(isset($error_message) && isset($_POST['register'])): ?>
                                <div class="alert alert-danger"><?php echo $error_message; ?></div>
                            <?php endif; ?>
                            <form id="registerForm" method="POST">
                                <input type="hidden" name="register" value="1">
                                <div class="mb-4">
                                    <input type="text" name="username" class="form-control form-control-lg" 
                                        placeholder="Username" required>
                                </div>
                                <div class="mb-4">
                                    <input type="email" name="email" class="form-control form-control-lg" 
                                        placeholder="Email" required>
                                </div>
                                <div class="mb-4 password-container">
                                    <input type="password" name="password" id="signupPassword" 
                                        class="form-control form-control-lg" placeholder="Password" required>
                                    <i class="fas fa-eye password-toggle" 
                                        onclick="togglePassword('signupPassword')"></i>
                                    <div class="progress mt-2">
                                        <div class="progress-bar" id="passwordStrength"></div>
                                    </div>
                                </div>
                                <div class="mb-4 password-container">
                                    <input type="password" name="confirm_password" 
                                        class="form-control form-control-lg" 
                                        placeholder="Confirm Password" required>
                                </div>
                                <button type="submit" class="btn btn-success btn-lg w-100 mb-4">
                                    Create Account <i class="fas fa-user-plus"></i>
                                </button>
                                <div class="social-login text-center">
                                    <div class="d-grid gap-2">
                                        <button type="button" class="btn social-btn btn-light">
                                            <i class="fab fa-apple"></i> Continue with Apple
                                        </button>
                                        <button type="button" class="btn social-btn btn-light">
                                            <i class="fab fa-twitter text-info"></i> Continue with Twitter
                                        </button>
                                    </div>
                                </div>
                                <p class="text-muted small mt-4">
                                    By signing up, you agree to our
                                    <a href="#" class="text-decoration-none">Terms</a> and
                                    <a href="#" class="text-decoration-none">Privacy Policy</a>
                                </p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleForms() {
            const loginSection = document.getElementById('loginSection');
            const registerSection = document.getElementById('registerSection');
            
            loginSection.classList.toggle('d-none');
            registerSection.classList.toggle('d-none');
            
            const activeForm = loginSection.classList.contains('d-none') ? 
                registerSection : loginSection;
            activeForm.classList.add('form-section');
            setTimeout(() => activeForm.classList.remove('form-section'), 600);
        }

        function togglePassword(fieldId) {
            const passwordField = document.getElementById(fieldId);
            const toggleIcon = passwordField.nextElementSibling;
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        document.getElementById('signupPassword').addEventListener('input', function(e) {
            const strength = calculatePasswordStrength(e.target.value);
            const strengthBar = document.getElementById('passwordStrength');
            strengthBar.style.width = strength + '%';
            strengthBar.className = `progress-bar bg-${getStrengthColor(strength)}`;
        });

        function calculatePasswordStrength(password) {
            let strength = 0;
            if (password.length >= 8) strength += 25;
            if (/[A-Z]/.test(password)) strength += 25;
            if (/[0-9]/.test(password)) strength += 25;
            if (/[^A-Za-z0-9]/.test(password)) strength += 25;
            return Math.min(strength, 100);
        }

        function getStrengthColor(strength) {
            if (strength < 50) return 'danger';
            if (strength < 75) return 'warning';
            return 'success';
        }

        // Client-side validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('signupPassword').value;
            const confirmPassword = this.querySelector('input[name="confirm_password"]').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
            }
        });
    </script>
</body>
</html>
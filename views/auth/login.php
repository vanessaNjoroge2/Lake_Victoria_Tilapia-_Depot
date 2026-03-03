<?php

/**
 * Login Page
 * Handles user authentication for all roles (Customer, Staff, Admin)
 */

require_once '../../config/config.php';
require_once '../../controllers/AuthController.php';

$authController = new AuthController();

// Redirect already logged-in users to their appropriate dashboard
$authController->redirectIfLoggedIn();

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialize variables for messages
$error   = null;
$success = null;

// Flash messages
if (isset($_SESSION['logout_success'])) {
    $success = $_SESSION['logout_success'];
    unset($_SESSION['logout_success']);
}
if (isset($_SESSION['registration_success'])) {
    $success = $_SESSION['registration_success'];
    unset($_SESSION['registration_success']);
}

// Auth-required redirect message
if (isset($_GET['error']) && $_GET['error'] === 'auth_required') {
    $error = "Please log in to access that page.";
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $error = "Security token mismatch. Please refresh and try again.";
    } else {
        $username = trim(strip_tags($_POST['username'] ?? ''));
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $error = "Username and password are required.";
        } elseif ($authController->login($username, $password)) {
            // Regenerate session ID after successful login (session fixation prevention)
            session_regenerate_id(true);
            $authController->postLoginRedirect();
        } else {
            $error = "Invalid username or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — <?php echo SITE_NAME; ?></title>
    <meta name="description" content="Sign in to Lake Victoria Tilapia Depot to order fresh tilapia fish.">

    <!-- Tailwind CSS, Icons & Fonts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * { font-family: 'Poppins', sans-serif; }

        /* Full-page gradient background */
        .page-bg {
            background: linear-gradient(135deg, #0e7490 0%, #0891b2 40%, #06b6d4 70%, #0284c7 100%);
            min-height: 100vh;
        }

        /* Decorative animated blobs */
        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.18;
            animation: blobFloat 8s ease-in-out infinite;
        }
        .blob-1 { width: 420px; height: 420px; background: #fde68a; top: -120px; left: -100px; animation-delay: 0s; }
        .blob-2 { width: 320px; height: 320px; background: #a5f3fc; bottom: -80px; right: -60px; animation-delay: 3s; }
        .blob-3 { width: 200px; height: 200px; background: #fff; top: 40%; left: 30%; animation-delay: 5s; }

        @keyframes blobFloat {
            0%, 100% { transform: translateY(0) scale(1); }
            50%       { transform: translateY(-30px) scale(1.05); }
        }

        /* Card entrance animation */
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(40px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .login-card { animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both; }

        /* Left panel fish float */
        @keyframes fishFloat {
            0%, 100% { transform: translateY(0px) rotate(-2deg); }
            50%       { transform: translateY(-18px) rotate(2deg); }
        }
        .fish-float { animation: fishFloat 5s ease-in-out infinite; }

        /* Input focus ring */
        .input-field {
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .input-field:focus {
            border-color: #06b6d4;
            box-shadow: 0 0 0 4px rgba(6,182,212,0.15);
            outline: none;
        }
        .input-field.error {
            border-color: #ef4444;
            box-shadow: 0 0 0 4px rgba(239,68,68,0.1);
        }

        /* Submit button shine */
        .btn-login {
            background: linear-gradient(135deg, #06b6d4 0%, #0284c7 100%);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .btn-login::after {
            content: '';
            position: absolute;
            top: -50%; left: -60%;
            width: 40%; height: 200%;
            background: rgba(255,255,255,0.18);
            transform: skewX(-20deg);
            transition: left 0.5s ease;
        }
        .btn-login:hover::after { left: 130%; }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(6,182,212,0.45);
        }
        .btn-login:active { transform: translateY(0); }

        /* Divider */
        .divider span {
            background: white;
            padding: 0 12px;
            color: #9ca3af;
            font-size: 0.75rem;
            font-weight: 500;
            letter-spacing: 0.05em;
        }

        /* Stat badge */
        .stat-badge {
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.25);
            transition: background 0.2s;
        }
        .stat-badge:hover { background: rgba(255,255,255,0.22); }

        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f0f9ff; }
        ::-webkit-scrollbar-thumb { background: #06b6d4; border-radius: 4px; }
    </style>
</head>

<body class="page-bg flex items-center justify-center p-4 relative overflow-hidden">

    <!-- Decorative blobs -->
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>

    <!-- ───────────────────────── Main Card ───────────────────────── -->
    <div class="login-card relative z-10 w-full max-w-5xl">
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col lg:flex-row min-h-[600px]">

            <!-- ══════════ LEFT PANEL — Branding ══════════ -->
            <div class="hidden lg:flex lg:w-5/12 flex-col items-center justify-between p-10
                        bg-gradient-to-br from-cyan-600 via-cyan-500 to-blue-600 text-white relative overflow-hidden">

                <!-- Decorative wave at bottom -->
                <svg class="absolute bottom-0 left-0 w-full opacity-20" viewBox="0 0 400 120" preserveAspectRatio="none">
                    <path d="M0,60 C80,120 160,0 240,60 C320,120 380,30 400,60 L400,120 L0,120 Z" fill="white"/>
                </svg>

                <!-- Logo / site name -->
                <div class="text-center w-full">
                    <a href="<?php echo BASE_URL; ?>/landing.php" class="inline-flex items-center gap-3 mb-6 group">
                        <img src="<?php echo BASE_URL; ?>/uploads/fresh_tilapia_logo.jpg"
                             alt="Logo"
                             class="w-14 h-14 rounded-full object-cover shadow-lg border-2 border-white/40
                                    group-hover:scale-110 transition">
                        <div class="text-left">
                            <div class="text-xl font-bold leading-tight">Lake Victoria</div>
                            <div class="text-sm text-cyan-100 font-medium">Tilapia Depot</div>
                        </div>
                    </a>
                </div>

                <!-- Hero image -->
                <div class="fish-float my-4">
                    <img src="<?php echo BASE_URL; ?>/uploads/fresh_tilapia.jpg"
                         alt="Fresh Tilapia"
                         class="w-56 h-56 object-cover rounded-full border-4 border-white/30 shadow-2xl mx-auto">
                </div>

                <!-- Tagline -->
                <div class="text-center">
                    <h2 class="text-3xl font-bold mb-2 leading-tight">
                        Fresh from<br><span class="text-yellow-300">Lake Victoria</span>
                    </h2>
                    <p class="text-cyan-100 text-sm mb-8">Farm to table — same day freshness guaranteed.</p>

                    <!-- Stats row -->
                    <div class="flex justify-center gap-4">
                        <div class="stat-badge rounded-2xl px-4 py-3 text-center">
                            <div class="text-lg font-bold text-yellow-300">1000+</div>
                            <div class="text-xs text-cyan-100">Customers</div>
                        </div>
                        <div class="stat-badge rounded-2xl px-4 py-3 text-center">
                            <div class="text-lg font-bold text-yellow-300">500kg</div>
                            <div class="text-xs text-cyan-100">Daily Fresh</div>
                        </div>
                        <div class="stat-badge rounded-2xl px-4 py-3 text-center">
                            <div class="text-lg font-bold text-yellow-300">24/7</div>
                            <div class="text-xs text-cyan-100">Support</div>
                        </div>
                    </div>
                </div>
            </div><!-- /LEFT PANEL -->

            <!-- ══════════ RIGHT PANEL — Login Form ══════════ -->
            <div class="flex-1 flex flex-col justify-center p-8 sm:p-12">

                <!-- Mobile logo (visible only on small screens) -->
                <div class="flex lg:hidden items-center gap-3 mb-8">
                    <img src="<?php echo BASE_URL; ?>/uploads/fresh_tilapia_logo.jpg"
                         alt="Logo" class="w-10 h-10 rounded-full object-cover shadow">
                    <div>
                        <div class="font-bold text-cyan-600 leading-tight">Lake Victoria</div>
                        <div class="text-xs text-gray-500">Tilapia Depot</div>
                    </div>
                </div>

                <!-- Heading -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-800 mb-1">Welcome back&nbsp;<span class="wave-hand">👋</span></h1>
                    <p class="text-gray-500 text-sm">Sign in to continue to your account.</p>
                </div>

                <!-- ── Flash messages ── -->
                <?php if ($success): ?>
                    <div id="msg-success"
                         class="flex items-start gap-3 bg-emerald-50 border border-emerald-300 text-emerald-800
                                rounded-xl px-4 py-3 mb-6 text-sm shadow-sm">
                        <i class="fas fa-check-circle text-emerald-500 mt-0.5 flex-shrink-0"></i>
                        <span><?php echo htmlspecialchars($success); ?></span>
                        <button type="button" onclick="this.parentElement.remove()"
                                class="ml-auto text-emerald-400 hover:text-emerald-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div id="msg-error"
                         class="flex items-start gap-3 bg-red-50 border border-red-300 text-red-800
                                rounded-xl px-4 py-3 mb-6 text-sm shadow-sm">
                        <i class="fas fa-exclamation-circle text-red-500 mt-0.5 flex-shrink-0"></i>
                        <span><?php echo htmlspecialchars($error); ?></span>
                        <button type="button" onclick="this.parentElement.remove()"
                                class="ml-auto text-red-400 hover:text-red-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                <?php endif; ?>

                <!-- ── Login Form ── -->
                <form method="POST" autocomplete="on" novalidate id="loginForm" class="space-y-5">

                    <!-- CSRF -->
                    <input type="hidden" name="csrf_token"
                           value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                    <!-- Username field -->
                    <div>
                        <label for="username" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Username
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 pointer-events-none">
                                <i class="fas fa-user text-sm"></i>
                            </span>
                            <input type="text"
                                   id="username"
                                   name="username"
                                   required
                                   autocomplete="username"
                                   placeholder="Your username"
                                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                                   class="input-field w-full pl-10 pr-4 py-3 border-2 border-gray-200 rounded-xl
                                          text-sm text-gray-800 placeholder-gray-400 bg-gray-50
                                          focus:bg-white <?php echo ($error && empty($_POST['username'] ?? '')) ? 'error' : ''; ?>">
                        </div>
                        <p id="username-err" class="hidden mt-1 text-xs text-red-500">
                            <i class="fas fa-exclamation-triangle mr-1"></i>Please enter your username.
                        </p>
                    </div>

                    <!-- Password field -->
                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Password
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 pointer-events-none">
                                <i class="fas fa-lock text-sm"></i>
                            </span>
                            <input type="password"
                                   id="password"
                                   name="password"
                                   required
                                   autocomplete="current-password"
                                   placeholder="Your password"
                                   class="input-field w-full pl-10 pr-12 py-3 border-2 border-gray-200 rounded-xl
                                          text-sm text-gray-800 placeholder-gray-400 bg-gray-50 focus:bg-white">
                            <button type="button"
                                    id="togglePwd"
                                    aria-label="Toggle password visibility"
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400
                                           hover:text-cyan-600 transition">
                                <i id="toggle-icon" class="fas fa-eye text-sm"></i>
                            </button>
                        </div>
                        <p id="password-err" class="hidden mt-1 text-xs text-red-500">
                            <i class="fas fa-exclamation-triangle mr-1"></i>Please enter your password.
                        </p>
                    </div>

                    <!-- Forgot password link -->
                    <div class="flex justify-end -mt-2">
                        <a href="<?php echo BASE_URL; ?>/views/auth/forgot_password.php"
                           class="text-xs font-medium text-cyan-600 hover:text-cyan-800 hover:underline transition">
                            <i class="fas fa-key mr-1"></i>Forgot password?
                        </a>
                    </div>

                    <!-- Submit button -->
                    <button type="submit" id="submitBtn"
                            class="btn-login w-full text-white py-3.5 rounded-xl font-semibold text-base
                                   shadow-lg flex items-center justify-center gap-2">
                        <span id="btn-text"><i class="fas fa-sign-in-alt"></i>&nbsp; Sign In</span>
                        <span id="btn-spinner" class="hidden">
                            <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Signing in…
                        </span>
                    </button>

                    <!-- Divider -->
                    <div class="divider flex items-center my-1">
                        <div class="flex-1 border-t border-gray-200"></div>
                        <span>or</span>
                        <div class="flex-1 border-t border-gray-200"></div>
                    </div>

                    <!-- Create account -->
                    <a href="<?php echo BASE_URL; ?>/views/auth/register.php"
                       class="block w-full text-center border-2 border-cyan-500 text-cyan-600 py-3 rounded-xl
                              font-semibold text-sm hover:bg-cyan-50 hover:border-cyan-600 transition">
                        <i class="fas fa-user-plus mr-2"></i>Create a new account
                    </a>

                </form>

                <!-- Back to home -->
                <div class="mt-8 text-center">
                    <a href="<?php echo BASE_URL; ?>/landing.php"
                       class="inline-flex items-center gap-1.5 text-sm text-gray-400 hover:text-cyan-600 transition">
                        <i class="fas fa-arrow-left text-xs"></i> Back to Home
                    </a>
                </div>

            </div><!-- /RIGHT PANEL -->

        </div><!-- /card inner -->
    </div><!-- /card wrapper -->

    <!-- ───────────────────────── Scripts ───────────────────────── -->
    <script>
        /* ── Password visibility toggle ── */
        const toggleBtn = document.getElementById('togglePwd');
        const pwdField  = document.getElementById('password');
        const toggleIco = document.getElementById('toggle-icon');

        toggleBtn.addEventListener('click', () => {
            const isHidden = pwdField.type === 'password';
            pwdField.type  = isHidden ? 'text' : 'password';
            toggleIco.classList.toggle('fa-eye',       !isHidden);
            toggleIco.classList.toggle('fa-eye-slash',  isHidden);
        });

        /* ── Client-side validation ── */
        const form        = document.getElementById('loginForm');
        const usernameEl  = document.getElementById('username');
        const passwordEl  = document.getElementById('password');
        const usernameErr = document.getElementById('username-err');
        const passwordErr = document.getElementById('password-err');
        const submitBtn   = document.getElementById('submitBtn');
        const btnText     = document.getElementById('btn-text');
        const btnSpinner  = document.getElementById('btn-spinner');

        function validateField(field, errEl) {
            const empty = field.value.trim() === '';
            field.classList.toggle('error', empty);
            errEl.classList.toggle('hidden', !empty);
            return !empty;
        }

        usernameEl.addEventListener('input', () => validateField(usernameEl, usernameErr));
        passwordEl.addEventListener('input', () => validateField(passwordEl, passwordErr));

        form.addEventListener('submit', (e) => {
            const okUser = validateField(usernameEl, usernameErr);
            const okPass = validateField(passwordEl, passwordErr);

            if (!okUser || !okPass) {
                e.preventDefault();
                return;
            }

            /* Show spinner while form submits */
            submitBtn.disabled = true;
            btnText.classList.add('hidden');
            btnSpinner.classList.remove('hidden');
        });

        /* ── Auto-dismiss flash messages after 6 seconds ── */
        ['msg-success', 'msg-error'].forEach(id => {
            const el = document.getElementById(id);
            if (el) setTimeout(() => el.remove(), 6000);
        });
    </script>
</body>

</html>
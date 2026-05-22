<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config/config.php';
require_once '../../includes/csrf.php';
require_once '../../controllers/TwoFactorController.php';

// If no pending 2FA login session, redirect to login
if (empty($_SESSION['temp_2fa_user_id'])) {
    header('Location: ' . BASE_URL . '/views/auth/login.php');
    exit;
}

$userId = (int) $_SESSION['temp_2fa_user_id'];
$error = null;
$success = null;

// Handle flash messages
if (isset($_SESSION['flash'])) {
    if ($_SESSION['flash']['type'] === 'success') {
        $success = $_SESSION['flash']['message'];
    } else {
        $error = $_SESSION['flash']['message'];
    }
    unset($_SESSION['flash']);
}

$twoFactorController = new TwoFactorController();

// Handle resend request
if (isset($_GET['action']) && $_GET['action'] === 'resend') {
    $code = $twoFactorController->generateOTP($userId);
    
    if ($code === 'rate_limited') {
        $error = "Too many OTP requests. Please wait up to an hour before requesting a new code.";
    } elseif ($code === false) {
        $error = "Failed to generate a new verification code. Please try again later.";
    } else {
        $sent = $twoFactorController->sendOTP($userId, $code);
        if ($sent) {
            $success = "A new verification code has been sent to your phone and email fallback.";
        } else {
            $error = "Failed to send verification code. Please verify your phone number and email configuration.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication — <?php echo SITE_NAME; ?></title>

    <!-- Tailwind CSS, Icons & Fonts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * { font-family: 'Poppins', sans-serif; }

        /* Full-page gradient background matching login.php */
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

        @keyframes blobFloat {
            0%, 100% { transform: translateY(0) scale(1); }
            50%       { transform: translateY(-30px) scale(1.05); }
        }

        /* Card entrance animation */
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(40px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .otp-card { animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both; }

        /* Input field styles */
        .input-field {
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .input-field:focus {
            border-color: #06b6d4;
            box-shadow: 0 0 0 4px rgba(6,182,212,0.15);
            outline: none;
        }

        /* Submit button shine */
        .btn-verify {
            background: linear-gradient(135deg, #06b6d4 0%, #0284c7 100%);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .btn-verify::after {
            content: '';
            position: absolute;
            top: -50%; left: -60%;
            width: 40%; height: 200%;
            background: rgba(255,255,255,0.18);
            transform: skewX(-20deg);
            transition: left 0.5s ease;
        }
        .btn-verify:hover::after { left: 130%; }
        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(6,182,212,0.45);
        }
        .btn-verify:active { transform: translateY(0); }
    </style>
</head>

<body class="page-bg flex items-center justify-center p-4 relative overflow-hidden">

    <!-- Decorative blobs -->
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>

    <!-- Main Card -->
    <div class="otp-card relative z-10 w-full max-w-md">
        <div class="bg-white rounded-3xl shadow-2xl p-8 sm:p-10">
            
            <!-- Icon and Heading -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-cyan-100 rounded-full text-cyan-600 text-2xl mb-4">
                    <i class="fas fa-shield-halved"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-800">Verification Required</h1>
                <p class="text-gray-500 text-sm mt-1">We sent a 6-digit OTP code to your registered mobile phone and email fallback.</p>
            </div>

            <!-- Flash alerts -->
            <?php if ($success): ?>
                <div id="msg-success" class="flex items-start gap-3 bg-emerald-50 border border-emerald-300 text-emerald-800 rounded-xl px-4 py-3 mb-6 text-sm">
                    <i class="fas fa-check-circle text-emerald-500 mt-0.5 flex-shrink-0"></i>
                    <span><?php echo htmlspecialchars($success); ?></span>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div id="msg-error" class="flex items-start gap-3 bg-red-50 border border-red-300 text-red-800 rounded-xl px-4 py-3 mb-6 text-sm">
                    <i class="fas fa-exclamation-circle text-red-500 mt-0.5 flex-shrink-0"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <!-- Verification Form -->
            <form action="<?php echo BASE_URL; ?>/handlers/two_factor_handler.php" method="POST" id="otpForm" class="space-y-6">
                
                <!-- CSRF -->
                <?php echo csrf_field(); ?>

                <!-- OTP inputs -->
                <div>
                    <label for="code" class="block text-sm font-semibold text-gray-700 mb-2 text-center">
                        Enter 6-Digit Code
                    </label>
                    <div class="flex justify-center">
                        <input type="text"
                               id="code"
                               name="code"
                               required
                               maxlength="6"
                               placeholder="******"
                               autocomplete="one-time-code"
                               inputmode="numeric"
                               pattern="[0-9]{6}"
                               class="input-field w-48 py-3 text-center text-2xl font-bold tracking-[8px] border-2 border-gray-200 rounded-xl text-gray-800 bg-gray-50 focus:bg-white">
                    </div>
                    <p id="code-err" class="hidden text-center mt-2 text-xs text-red-500">
                        <i class="fas fa-exclamation-triangle mr-1"></i>Please enter a 6-digit numeric code.
                    </p>
                </div>

                <!-- Expiry timer -->
                <div class="text-center text-xs text-gray-500">
                    Code expires in: <span id="countdown" class="font-semibold text-cyan-600">10:00</span>
                </div>

                <!-- Submit Button -->
                <button type="submit" id="submitBtn" class="btn-verify w-full text-white py-3.5 rounded-xl font-semibold text-base shadow-lg flex items-center justify-center gap-2">
                    <span id="btn-text"><i class="fas fa-key"></i>&nbsp; Verify and Sign In</span>
                    <span id="btn-spinner" class="hidden">
                        <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Verifying…
                    </span>
                </button>
            </form>

            <!-- Actions footer -->
            <div class="mt-8 pt-6 border-t border-gray-100 flex flex-col items-center gap-4">
                <a href="?action=resend" class="text-sm font-semibold text-cyan-600 hover:text-cyan-800 transition">
                    <i class="fas fa-rotate-right mr-1.5"></i>Resend Code
                </a>
                
                <a href="<?php echo BASE_URL; ?>/views/auth/login.php" class="text-sm text-gray-400 hover:text-cyan-600 transition">
                    <i class="fas fa-arrow-left mr-1.5 text-xs"></i>Back to Sign In
                </a>
            </div>

        </div>
    </div>

    <!-- Scripting -->
    <script>
        // Countdown timer for 10 minutes (600 seconds)
        let seconds = 600;
        const countdownEl = document.getElementById('countdown');

        const timer = setInterval(() => {
            seconds--;
            if (seconds <= 0) {
                clearInterval(timer);
                countdownEl.textContent = 'Expired';
                countdownEl.classList.remove('text-cyan-600');
                countdownEl.classList.add('text-red-500');
            } else {
                const mins = Math.floor(seconds / 60);
                const secs = seconds % 60;
                countdownEl.textContent = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
            }
        }, 1000);

        // Client side validation
        const form = document.getElementById('otpForm');
        const codeInput = document.getElementById('code');
        const codeErr = document.getElementById('code-err');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btn-text');
        const btnSpinner = document.getElementById('btn-spinner');

        // Automatically format/restrict input to numbers only
        codeInput.addEventListener('input', (e) => {
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
        });

        form.addEventListener('submit', (e) => {
            const codeVal = codeInput.value.trim();
            if (codeVal === '' || codeVal.length !== 6 || isNaN(codeVal)) {
                e.preventDefault();
                codeInput.classList.add('border-red-500');
                codeErr.classList.remove('hidden');
                return;
            }

            // Show loading spinner
            submitBtn.disabled = true;
            btnText.classList.add('hidden');
            btnSpinner.classList.remove('hidden');
        });

        // Flash message auto-dismiss
        ['msg-success', 'msg-error'].forEach(id => {
            const el = document.getElementById(id);
            if (el) setTimeout(() => el.remove(), 10000);
        });
    </script>
</body>

</html>

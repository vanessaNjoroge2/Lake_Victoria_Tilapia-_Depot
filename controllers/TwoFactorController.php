<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/NotificationService.php';
require_once __DIR__ . '/../controllers/AuditController.php';

class TwoFactorController
{
    private $db;

    public function __construct()
    {
        try {
            $database = new Database();
            $this->db = $database->getConnection();
        } catch (Exception $e) {
            error_log("TwoFactorController init error: " . $e->getMessage());
        }
    }

    /**
     * Check if 2FA is required or enabled for a given user
     *
     * @param array $user User data row
     * @return bool
     */
    public function is2faRequired(array $user): bool
    {
        // 2FA is mandatory for admin and staff
        if (isset($user['role']) && in_array($user['role'], ['admin', 'staff'])) {
            return true;
        }

        // 2FA is optional but enabled for customers
        if (isset($user['role']) && $user['role'] === 'customer') {
            return !empty($user['two_factor_enabled']);
        }

        return false;
    }

    /**
     * Enforce rate limiting on operations
     *
     * @param string $actionKey Unique action key (e.g. 'otp_request_userId')
     * @param int $maxAttempts Max attempts allowed
     * @param int $intervalSeconds Timeframe in seconds
     * @return bool True if allowed, False if blocked
     */
    public function checkRateLimit(string $actionKey, int $maxAttempts, int $intervalSeconds): bool
    {
        if (!$this->db) {
            return true; // fail-open if DB is down
        }

        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        
        try {
            // Find existing rate limit record
            $query = "SELECT * FROM rate_limits WHERE ip_address = :ip AND action_key = :key";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':ip' => $ipAddress, ':key' => $actionKey]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);

            $now = date('Y-m-d H:i:s');

            if (!$record) {
                // Insert new record
                $ins = "INSERT INTO rate_limits (ip_address, action_key, attempts, first_attempt, last_attempt) 
                        VALUES (:ip, :key, 1, :now, :now)";
                $stmtIns = $this->db->prepare($ins);
                $stmtIns->execute([':ip' => $ipAddress, ':key' => $actionKey, ':now' => $now]);
                return true;
            }

            $firstAttemptTime = strtotime($record['first_attempt']);
            $elapsed = time() - $firstAttemptTime;

            if ($elapsed > $intervalSeconds) {
                // Interval passed: reset window
                $upd = "UPDATE rate_limits 
                        SET attempts = 1, first_attempt = :now, last_attempt = :now 
                        WHERE id = :id";
                $stmtUpd = $this->db->prepare($upd);
                $stmtUpd->execute([':now' => $now, ':id' => $record['id']]);
                return true;
            }

            if ($record['attempts'] >= $maxAttempts) {
                // Blocked
                return false;
            }

            // Increment attempt
            $upd = "UPDATE rate_limits 
                    SET attempts = attempts + 1, last_attempt = :now 
                    WHERE id = :id";
            $stmtUpd = $this->db->prepare($upd);
            $stmtUpd->execute([':now' => $now, ':id' => $record['id']]);
            return true;

        } catch (Exception $e) {
            error_log("Rate limiting error: " . $e->getMessage());
            return true;
        }
    }

    /**
     * Generate a 6-digit secure OTP and save to database
     *
     * @param int $userId
     * @return string|bool The code or false on error
     */
    public function generateOTP(int $userId)
    {
        if (!$this->db) {
            return false;
        }

        try {
            // Rate limit check: Max 5 OTP requests per hour per user
            $rateLimitKey = 'otp_request_' . $userId;
            if (!$this->checkRateLimit($rateLimitKey, 5, 3600)) {
                return 'rate_limited';
            }

            // Invalidate older unused codes for this user
            $invalidate = "UPDATE two_factor_codes SET used = 1 WHERE user_id = :user_id AND used = 0";
            $stmt = $this->db->prepare($invalidate);
            $stmt->execute([':user_id' => $userId]);

            // Generate 6-digit cryptographically secure code
            $code = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

            $query = "INSERT INTO two_factor_codes (user_id, code, type, expires_at, used) 
                      VALUES (:user_id, :code, 'login', :expires_at, 0)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':user_id' => $userId,
                ':code' => $code,
                ':expires_at' => $expiresAt
            ]);

            return $code;
        } catch (Exception $e) {
            error_log("OTP Generation Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send OTP via Africa's Talking SMS and falls back to PHPMailer email
     *
     * @param int $userId
     * @param string $code
     * @return bool
     */
    public function sendOTP(int $userId, string $code): bool
    {
        if (!$this->db) {
            return false;
        }

        try {
            $query = "SELECT full_name, email, phone FROM users WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':id' => $userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return false;
            }

            $message = "Your verification OTP for Lake Victoria Tilapia Depot is: " . $code . ". Valid for 10 minutes.";
            
            $smsSent = false;
            if (!empty($user['phone'])) {
                // Send SMS via Africa's Talking
                $smsSent = NotificationService::sendSMS($user['phone'], $message);
            }

            // Fallback / simultaneous Email via PHPMailer
            $subject = "Verification OTP - " . SITE_NAME;
            $emailBody = "
                <div style='font-family: Arial, sans-serif; padding: 20px; color: #333;'>
                    <h2>Verification Required</h2>
                    <p>Hello " . htmlspecialchars($user['full_name']) . ",</p>
                    <p>You are attempting to log into your account at Lake Victoria Tilapia Depot.</p>
                    <p>Please enter the following 6-digit verification code to complete your login:</p>
                    <div style='background: #f3f4f6; font-size: 24px; font-weight: bold; letter-spacing: 4px; padding: 15px; border-radius: 8px; display: inline-block; margin: 15px 0;'>
                        " . $code . "
                    </div>
                    <p style='color: #ef4444; font-size: 13px;'>This code is valid for 10 minutes and is single-use.</p>
                </div>
            ";
            
            $emailSent = NotificationService::sendEmail($user['email'], $subject, $emailBody, $user['full_name']);

            return $smsSent || $emailSent;

        } catch (Exception $e) {
            error_log("OTP Send Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify the 6-digit OTP code
     *
     * @param int $userId
     * @param string $code
     * @return array ['success' => bool, 'message' => string, 'locked' => bool]
     */
    public function verifyOTP(int $userId, string $code): array
    {
        if (!$this->db) {
            return ['success' => false, 'message' => 'Database connection failed.', 'locked' => false];
        }

        if (empty($_SESSION['otp_attempts'])) {
            $_SESSION['otp_attempts'] = 0;
        }

        try {
            // Check wrong attempt threshold
            if ($_SESSION['otp_attempts'] >= 3) {
                // Lock current login session and require re-login
                unset($_SESSION['temp_2fa_user_id']);
                unset($_SESSION['otp_attempts']);
                AuditController::logActivity($userId, 'lockout_2fa_failed', 'users', $userId);
                return ['success' => false, 'message' => 'Too many failed OTP attempts. Re-authentication required.', 'locked' => true];
            }

            // Select active code
            $query = "SELECT * FROM two_factor_codes 
                      WHERE user_id = :user_id 
                        AND code = :code 
                        AND type = 'login' 
                        AND used = 0 
                        AND expires_at > NOW() 
                      ORDER BY created_at DESC LIMIT 1";

            $stmt = $this->db->prepare($query);
            $stmt->execute([':user_id' => $userId, ':code' => $code]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($record) {
                // Mark as used
                $update = "UPDATE two_factor_codes SET used = 1 WHERE id = :id";
                $stmtUp = $this->db->prepare($update);
                $stmtUp->execute([':id' => $record['id']]);

                // Clear attempts counter
                unset($_SESSION['otp_attempts']);

                return ['success' => true, 'message' => 'OTP verified successfully.', 'locked' => false];
            } else {
                $_SESSION['otp_attempts']++;
                $remaining = 3 - $_SESSION['otp_attempts'];
                
                if ($remaining <= 0) {
                    unset($_SESSION['temp_2fa_user_id']);
                    unset($_SESSION['otp_attempts']);
                    AuditController::logActivity($userId, 'lockout_2fa_failed', 'users', $userId);
                    return ['success' => false, 'message' => 'Too many failed OTP attempts. Re-authentication required.', 'locked' => true];
                }
                
                return [
                    'success' => false, 
                    'message' => "Invalid or expired verification code. {$remaining} attempts remaining.", 
                    'locked' => false
                ];
            }

        } catch (Exception $e) {
            error_log("OTP Verification Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Verification process failed.', 'locked' => false];
        }
    }
}

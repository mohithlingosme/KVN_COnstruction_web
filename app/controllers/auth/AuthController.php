<?php

class AuthController
{
    private PDO $conn;
    private User $userModel;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;

        require_once ROOT_PATH . '/app/models/User.php';

        $this->userModel = new User($conn);
    }

    /* =========================================================
       SEND LOGIN OTP
    ========================================================= */

    public function sendLoginOtp(string $phone): array
    {
        $phone = sanitize($phone);

        if (empty($phone)) {

            return [
                'status' => false,
                'message' => 'Phone number is required.'
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | RATE LIMIT
        |--------------------------------------------------------------------------
        */

        if (!checkRateLimit('client_otp', 3, 600)) {

            logSecurityEvent(
                'OTP_RATE_LIMIT',
                'OTP request limit exceeded',
                [
                    'phone' => $phone
                ]
            );

            return [
                'status' => false,
                'message' => 'Too many requests. Try again later.'
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | FIND USER
        |--------------------------------------------------------------------------
        */

        $user = $this->userModel->findByPhone($phone);

        /*
        |--------------------------------------------------------------------------
        | GENERIC RESPONSE
        |--------------------------------------------------------------------------
        */

        if (
            !$user ||
            $user['status'] !== 'active'
        ) {

            return [
                'status' => false,
                'message' => 'Unable to process request.'
            ];
        }

        try {

            $this->conn->beginTransaction();

            /*
            |--------------------------------------------------------------------------
            | INVALIDATE OLD OTPs
            |--------------------------------------------------------------------------
            */

            $invalidateQuery = "

                UPDATE user_otps

                SET is_used = 1

                WHERE user_id = :user_id
                AND purpose = 'login'
                AND is_used = 0

            ";

            $invalidateStmt =
            $this->conn->prepare($invalidateQuery);

            $invalidateStmt->execute([
                ':user_id' => $user['id']
            ]);

            /*
            |--------------------------------------------------------------------------
            | GENERATE OTP
            |--------------------------------------------------------------------------
            */

            $otp = generateOtp();

            /*
            |--------------------------------------------------------------------------
            | SAVE OTP
            |--------------------------------------------------------------------------
            */

            $this->userModel->saveOtp(
                $user['id'],
                $otp,
                'login'
            );

            /*
            |--------------------------------------------------------------------------
            | SEND OTP
            |--------------------------------------------------------------------------
            */

            sendOtpSms(
                $phone,
                $otp
            );

            if (!empty($user['email'])) {

                sendOtpEmail(
                    $user['email'],
                    $otp,
                    $user['full_name']
                );
            }

            $this->conn->commit();

        } catch (Exception $e) {

            $this->conn->rollBack();

            logSecurityEvent(
                'OTP_SEND_FAILED',
                $e->getMessage(),
                [
                    'phone' => $phone
                ]
            );

            return [
                'status' => false,
                'message' => 'Failed to send OTP.'
            ];
        }

        incrementRateLimit('client_otp');

        return [
            'status' => true,
            'message' => 'OTP sent successfully.'
        ];
    }

    /* =========================================================
       VERIFY LOGIN OTP
    ========================================================= */

    public function verifyPhoneOtp(
        string $phone,
        string $otp
    ): array {

        $phone = sanitize($phone);
        $otp   = sanitize($otp);

        $user = $this->userModel->findByPhone($phone);

        if (
            !$user ||
            $user['status'] !== 'active'
        ) {

            return [
                'status' => false,
                'message' => 'Invalid credentials.'
            ];
        }

        $query = "

            SELECT *

            FROM user_otps

            WHERE user_id = :user_id

            AND purpose = 'login'

            AND is_used = 0

            AND expires_at > NOW()

            ORDER BY id DESC

            LIMIT 1

        ";

        $stmt = $this->conn->prepare($query);

        $stmt->execute([
            ':user_id' => $user['id']
        ]);

        $otpRow = $stmt->fetch();

        if (!$otpRow) {

            return [
                'status' => false,
                'message' => 'OTP expired.'
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | ATTEMPT LIMIT
        |--------------------------------------------------------------------------
        */

        if ($otpRow['attempts'] >= 5) {

            logSecurityEvent(
                'OTP_BLOCKED',
                'OTP attempts exceeded',
                [
                    'user_id' => $user['id']
                ]
            );

            return [
                'status' => false,
                'message' => 'Too many attempts.'
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | VERIFY OTP
        |--------------------------------------------------------------------------
        */

        if (!password_verify($otp, $otpRow['otp'])) {

            $attemptQuery = "

                UPDATE user_otps

                SET attempts = attempts + 1

                WHERE id = :id

            ";

            $attemptStmt =
            $this->conn->prepare($attemptQuery);

            $attemptStmt->execute([
                ':id' => $otpRow['id']
            ]);

            logSecurityEvent(
                'INVALID_LOGIN_OTP',
                'Invalid login OTP',
                [
                    'phone' => $phone
                ]
            );

            return [
                'status' => false,
                'message' => 'Invalid OTP.'
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | MARK USED
        |--------------------------------------------------------------------------
        */

        $usedQuery = "

            UPDATE user_otps

            SET is_used = 1

            WHERE id = :id

        ";

        $usedStmt =
        $this->conn->prepare($usedQuery);

        $usedStmt->execute([
            ':id' => $otpRow['id']
        ]);

        /*
        |--------------------------------------------------------------------------
        | SESSION FIXATION PROTECTION
        |--------------------------------------------------------------------------
        */

        session_regenerate_id(true);

        /*
        |--------------------------------------------------------------------------
        | CREATE SESSION
        |--------------------------------------------------------------------------
        */

        createUserSession($user);

        /*
        |--------------------------------------------------------------------------
        | UPDATE LAST LOGIN
        |--------------------------------------------------------------------------
        */

        $this->userModel->updateLastLogin(
            $user['id']
        );

        /*
        |--------------------------------------------------------------------------
        | SECURITY LOG
        |--------------------------------------------------------------------------
        */

        logSecurityEvent(
            'USER_LOGIN',
            'User logged in successfully',
            [
                'user_id' => $user['id']
            ]
        );

        clearRateLimit('client_otp');

        return [
            'status' => true,
            'message' => 'Login successful.'
        ];
    }

    /* =========================================================
       ADMIN LOGIN
    ========================================================= */

    public function adminLogin(
        string $email,
        string $password
    ): array {

        $email = sanitize($email);

        if (!checkRateLimit('admin_login', 5, 900)) {

            logSecurityEvent(
                'ADMIN_LOGIN_RATE_LIMIT',
                'Admin login blocked',
                [
                    'email' => $email
                ]
            );

            return [
                'status' => false,
                'message' => 'Too many login attempts.'
            ];
        }

        $admin =
        $this->userModel->findByEmail($email);

        /*
        |--------------------------------------------------------------------------
        | GENERIC RESPONSE
        |--------------------------------------------------------------------------
        */

        if (
            !$admin ||
            $admin['status'] !== 'active'
        ) {

            incrementRateLimit('admin_login');

            return [
                'status' => false,
                'message' => 'Invalid credentials.'
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | ROLE CHECK
        |--------------------------------------------------------------------------
        */

        if (
            !in_array(
                $admin['role'],
                ['admin', 'super_admin']
            )
        ) {

            incrementRateLimit('admin_login');

            return [
                'status' => false,
                'message' => 'Unauthorized.'
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | ACCOUNT LOCK
        |--------------------------------------------------------------------------
        */

        if (
            !empty($admin['locked_until']) &&
            strtotime($admin['locked_until']) > time()
        ) {

            return [
                'status' => false,
                'message' => 'Account temporarily locked.'
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | PASSWORD VERIFY
        |--------------------------------------------------------------------------
        */

        if (
            !password_verify(
                $password,
                $admin['password']
            )
        ) {

            $this->userModel
            ->incrementFailedAttempts(
                $admin['id']
            );

            incrementRateLimit('admin_login');

            logSecurityEvent(
                'INVALID_ADMIN_PASSWORD',
                'Invalid admin password',
                [
                    'email' => $email
                ]
            );

            return [
                'status' => false,
                'message' => 'Invalid credentials.'
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | RESET FAILED ATTEMPTS
        |--------------------------------------------------------------------------
        */

        $this->userModel
        ->resetAttempts(
            $admin['id']
        );

        /*
        |--------------------------------------------------------------------------
        | SESSION FIXATION PROTECTION
        |--------------------------------------------------------------------------
        */

        session_regenerate_id(true);

        /*
        |--------------------------------------------------------------------------
        | CREATE SESSION
        |--------------------------------------------------------------------------
        */

        createAdminSession($admin);

        /*
        |--------------------------------------------------------------------------
        | UPDATE LAST LOGIN
        |--------------------------------------------------------------------------
        */

        $this->userModel
        ->updateLastLogin(
            $admin['id']
        );

        /*
        |--------------------------------------------------------------------------
        | SEND LOGIN ALERT
        |--------------------------------------------------------------------------
        */

        sendAdminLoginAlert(
            $admin['email'],
            $admin['full_name']
        );

        /*
        |--------------------------------------------------------------------------
        | LOG ADMIN ACTION
        |--------------------------------------------------------------------------
        */

        logAdminAction(
            $admin['id'],
            'ADMIN_LOGIN',
            'Admin logged in'
        );

        clearRateLimit('admin_login');

        return [
            'status' => true,
            'message' => 'Admin login successful.'
        ];
    }

    /* =========================================================
       LOGOUT
    ========================================================= */

    public function logout(): bool
    {
        if (isset($_SESSION['user_id'])) {

            logSecurityEvent(
                'USER_LOGOUT',
                'User logged out',
                [
                    'user_id' => $_SESSION['user_id']
                ]
            );
        }

        destroySession();

        session_regenerate_id(true);

        return true;
    }
}
?>
<?php

class AuthController
{
    private $conn;
    private $userModel;

    public function __construct($conn)
    {
        $this->conn = $conn;

        require_once ROOT_PATH . '/app/models/User.php';

        $this->userModel = new User($conn);
    }

    // ============================================
    // CLIENT PHONE LOGIN
    // ============================================

    public function sendLoginOtp($phone)
    {
        $phone = sanitize($phone);

        if(empty($phone)){

            return [
                'status' => false,
                'message' => 'Phone number required.'
            ];
        }

        // RATE LIMIT

        if(!checkRateLimit('client_otp', 3, 600)){

            logSecurityEvent(
                'OTP_RATE_LIMIT',
                'OTP limit exceeded',
                [
                    'phone' => $phone
                ]
            );

            return [
                'status' => false,
                'message' => 'Too many OTP requests. Try again later.'
            ];
        }

        // FIND USER

        $user = $this->userModel->findByPhone($phone);

        if(!$user){

            return [
                'status' => false,
                'message' => 'User not found.'
            ];
        }

        // ACCOUNT STATUS

        if($user['status'] !== 'active'){

            return [
                'status' => false,
                'message' => 'Account disabled.'
            ];
        }

        // GENERATE OTP

        $otp = generateOtp();

        // SAVE OTP

        $this->userModel->saveOtp(
            $user['id'],
            $otp,
            'login'
        );

        // SEND SMS

        sendOtpSms(
            $phone,
            $otp
        );

        // SEND EMAIL

        if(!empty($user['email'])){

            sendOtpEmail(
                $user['email'],
                $otp,
                $user['full_name']
            );
        }

        incrementRateLimit('client_otp');

        return [
            'status' => true,
            'message' => 'OTP sent successfully.'
        ];
    }

    // ============================================
    // VERIFY LOGIN OTP
    // ============================================

    public function verifyPhoneOtp($phone, $otp)
    {
        $phone = sanitize($phone);
        $otp   = sanitize($otp);

        $user = $this->userModel->findByPhone($phone);

        if(!$user){

            return [
                'status' => false,
                'message' => 'User not found.'
            ];
        }

        // FETCH OTP

        $query = "
            SELECT *
            FROM otps
            WHERE user_id = :user_id
            AND otp_type = 'login'
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

        if(!$otpRow){

            return [
                'status' => false,
                'message' => 'OTP expired.'
            ];
        }

        // OTP ATTEMPTS

        if((int) $otpRow['attempts'] >= 5){

            return [
                'status' => false,
                'message' => 'Too many attempts.'
            ];
        }

        // VERIFY HASHED OTP

        if(!password_verify($otp, $otpRow['otp_code'])){

            $attemptQuery = "
                UPDATE otps
                SET attempts = attempts + 1
                WHERE id = :id
            ";

            $attemptStmt = $this->conn->prepare($attemptQuery);

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

        // MARK USED

        $usedQuery = "
            UPDATE otps
            SET is_used = 1, used_at = NOW()
            WHERE id = :id
        ";

        $usedStmt = $this->conn->prepare($usedQuery);

        $usedStmt->execute([
            ':id' => $otpRow['id']
        ]);

        // CREATE SESSION

        createUserSession($user);

        // UPDATE LAST LOGIN

        $this->userModel->updateLastLogin($user['id']);

        return [
            'status' => true,
            'message' => 'Login successful.'
        ];
    }

    // ============================================
    // ADMIN LOGIN
    // ============================================

    public function adminLogin($email, $password)
    {
        $email = sanitize($email);

        if(!checkRateLimit('admin_login', 3, 600)){

            logSecurityEvent(
                'ADMIN_LOGIN_LIMIT',
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

        $admin = $this->userModel->findByEmail($email);

        if(
            !$admin ||
            $admin['role'] !== 'admin'
        ){

            incrementRateLimit('admin_login');

            return [
                'status' => false,
                'message' => 'Invalid credentials.'
            ];
        }

        // ACCOUNT STATUS

        if($admin['status'] !== 'active'){

            return [
                'status' => false,
                'message' => 'Admin account disabled.'
            ];
        }

        // ACCOUNT LOCK

        if(
            !empty($admin['locked_until']) &&
            strtotime($admin['locked_until']) > time()
        ){

            return [
                'status' => false,
                'message' => 'Account temporarily locked.'
            ];
        }

        // PASSWORD VERIFY

        if(!password_verify($password, $admin['password'])){

            $this->userModel->incrementFailedAttempts($admin['id']);

            incrementRateLimit('admin_login');

            logSecurityEvent(
                'INVALID_ADMIN_LOGIN',
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

        // RESET FAILED ATTEMPTS

        $this->userModel->resetAttempts($admin['id']);

        // CREATE ADMIN SESSION

        createAdminSession($admin);

        // EMAIL ALERT

        sendAdminLoginAlert(
            $admin['email'],
            $admin['full_name']
        );

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

    // ============================================
    // FORGOT PASSWORD
    // ============================================

    public function forgotPassword($email)
    {
        $email = sanitize($email);

        $user = $this->userModel->findByEmail($email);

        if(!$user){

            return [
                'status' => false,
                'message' => 'User not found.'
            ];
        }

        // GENERATE OTP

        $otp = generateOtp();

        // SAVE RESET OTP

        $this->userModel->saveOtp(
            $user['id'],
            $otp,
            'password_reset'
        );

        // SEND EMAIL

        sendPasswordResetOtp(
            $user['email'],
            $otp,
            $user['full_name']
        );

        return [
            'status' => true,
            'message' => 'Reset OTP sent.'
        ];
    }

    // ============================================
    // VERIFY RESET OTP
    // ============================================

    public function verifyResetOtp($email, $otp)
    {
        $email = sanitize($email);
        $otp   = sanitize($otp);

        $user = $this->userModel->findByEmail($email);

        if(!$user){

            return [
                'status' => false,
                'message' => 'User not found.'
            ];
        }

        $query = "
            SELECT *
            FROM otps
            WHERE user_id = :user_id
            AND otp_type = 'password_reset'
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

        if(!$otpRow){

            return [
                'status' => false,
                'message' => 'OTP expired.'
            ];
        }

        // ATTEMPT LIMIT

        if((int) $otpRow['attempts'] >= 5){

            return [
                'status' => false,
                'message' => 'Too many attempts.'
            ];
        }

        // VERIFY HASHED OTP

        if(!password_verify($otp, $otpRow['otp_code'])){

            $attemptQuery = "
                UPDATE otps
                SET attempts = attempts + 1
                WHERE id = :id
            ";

            $attemptStmt = $this->conn->prepare($attemptQuery);

            $attemptStmt->execute([
                ':id' => $otpRow['id']
            ]);

            return [
                'status' => false,
                'message' => 'Invalid OTP.'
            ];
        }

        // MARK USED

        $usedQuery = "
            UPDATE otps
            SET is_used = 1, used_at = NOW()
            WHERE id = :id
        ";

        $usedStmt = $this->conn->prepare($usedQuery);

        $usedStmt->execute([
            ':id' => $otpRow['id']
        ]);

        // SESSION FLAGS

        $_SESSION['password_reset_verified'] = true;

        $_SESSION['password_reset_user_id'] = $user['id'];

        return [
            'status' => true,
            'message' => 'OTP verified.'
        ];
    }

    // ============================================
    // RESET PASSWORD
    // ============================================

    public function resetPassword(
        $newPassword,
        $confirmPassword
    ){

        if(
            !isset($_SESSION['password_reset_verified']) ||
            !isset($_SESSION['password_reset_user_id'])
        ){

            return [
                'status' => false,
                'message' => 'Unauthorized request.'
            ];
        }

        if($newPassword !== $confirmPassword){

            return [
                'status' => false,
                'message' => 'Passwords do not match.'
            ];
        }

        if(strlen($newPassword) < 8){

            return [
                'status' => false,
                'message' => 'Password too short.'
            ];
        }

        $userId = $_SESSION['password_reset_user_id'];

        // HASH PASSWORD

        $hashedPassword = password_hash(
            $newPassword,
            PASSWORD_DEFAULT
        );

        // UPDATE PASSWORD

        $query = "
            UPDATE users
            SET password = :password
            WHERE id = :id
        ";

        $stmt = $this->conn->prepare($query);

        $stmt->execute([
            ':password' => $hashedPassword,
            ':id'       => $userId
        ]);

        // DESTROY ALL SESSIONS

        invalidateUserSessions($userId);

        // SUCCESS EMAIL

        $user = $this->userModel->findById($userId);

        sendPasswordResetSuccess(
            $user['email'],
            $user['full_name']
        );

        // REMOVE RESET FLAGS

        unset($_SESSION['password_reset_verified']);
        unset($_SESSION['password_reset_user_id']);

        // DESTROY SESSION

        destroySession();

        return [
            'status' => true,
            'message' => 'Password reset successful.'
        ];
    }

    // ============================================
    // RESEND OTP
    // ============================================

    public function resendOtp($phone)
    {
        return $this->sendLoginOtp($phone);
    }

    // ============================================
    // LOGOUT
    // ============================================

    public function logout()
    {
        if(isset($_SESSION['user_id'])){

            logSecurityEvent(
                'USER_LOGOUT',
                'User logged out',
                [
                    'user_id' => $_SESSION['user_id']
                ]
            );
        }

        destroySession();

        return true;
    }
}
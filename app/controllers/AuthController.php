<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| AUTHENTICATION CONTROLLER
|--------------------------------------------------------------------------
| File:
| /app/controllers/AuthController.php
|--------------------------------------------------------------------------
*/

class AuthController
{
    private $conn;

    public function __construct($database)
    {
        $this->conn = $database;
    }

    /*
    |--------------------------------------------------------------------------
    | CLIENT PHONE LOGIN
    |--------------------------------------------------------------------------
    */

    public function phoneLogin()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

            redirect('login.php');
        }

        validateCsrf();

        if (!otpRateLimit()) {

            $_SESSION['error'] =
            'Too many OTP requests. Please try later.';

            redirect('phone-login.php');
        }

        $phone = sanitizePhone(
            $_POST['phone'] ?? ''
        );

        if (!validatePhone($phone)) {

            $_SESSION['error'] =
            'Invalid phone number.';

            redirect('phone-login.php');
        }

        try {

            $query = "

                SELECT *

                FROM users

                WHERE phone = :phone

                LIMIT 1
            ";

            $stmt =
            $this->conn->prepare($query);

            $stmt->execute([

                ':phone' => $phone
            ]);

            $user =
            $stmt->fetch();

            /*
            |------------------------------------------------------------------
            | AUTO REGISTER CLIENT
            |------------------------------------------------------------------
            */

            if (!$user) {

                $insert = "

                    INSERT INTO users (

                        full_name,
                        phone,
                        role,
                        status,
                        phone_verified,
                        created_at

                    ) VALUES (

                        :full_name,
                        :phone,
                        'client',
                        'active',
                        0,
                        NOW()
                    )
                ";

                $stmt =
                $this->conn->prepare($insert);

                $stmt->execute([

                    ':full_name' => 'Client User',

                    ':phone' => $phone
                ]);

                $userId =
                $this->conn->lastInsertId();

                $query = "

                    SELECT *

                    FROM users

                    WHERE id = :id

                    LIMIT 1
                ";

                $stmt =
                $this->conn->prepare($query);

                $stmt->execute([

                    ':id' => $userId
                ]);

                $user =
                $stmt->fetch();
            }

            /*
            |------------------------------------------------------------------
            | ACCOUNT STATUS
            |------------------------------------------------------------------
            */

            if ($user['status'] !== 'active') {

                $_SESSION['error'] =
                'Account inactive.';

                redirect('phone-login.php');
            }

            /*
            |------------------------------------------------------------------
            | GENERATE OTP
            |------------------------------------------------------------------
            */

            $otpResponse =
            createPhoneOtp($phone);

            if (!$otpResponse['success']) {

                $_SESSION['error'] =
                $otpResponse['message'];

                redirect('phone-login.php');
            }

            $otp =
            $otpResponse['otp'];

            /*
            |------------------------------------------------------------------
            | SEND SMS
            |------------------------------------------------------------------
            */

            sendOtpSms($phone, $otp);

            /*
            |------------------------------------------------------------------
            | CREATE OTP SESSION
            |------------------------------------------------------------------
            */

            createOtpSession($phone);

            $_SESSION['otp_type'] = 'login';

            logSecurityEvent(

                $user['id'],

                'phone_login_otp_sent',

                'info',

                'OTP sent for phone login'
            );

            $_SESSION['success'] =
            'OTP sent successfully.';

            redirect('verify-phone-otp.php');

        } catch (Exception $e) {

            error_log($e->getMessage());

            $_SESSION['error'] =
            'Something went wrong.';

            redirect('phone-login.php');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | VERIFY PHONE OTP
    |--------------------------------------------------------------------------
    */

    public function verifyPhoneOtp()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

            redirect('verify-phone-otp.php');
        }

        validateCsrf();

        if (!isOtpSessionValid()) {

            $_SESSION['error'] =
            'OTP session expired.';

            redirect('phone-login.php');
        }

        $phone =
        $_SESSION['otp_phone'];

        $otp = sanitize(
            $_POST['otp'] ?? ''
        );

        if (empty($otp)) {

            $_SESSION['error'] =
            'OTP required.';

            redirect('verify-phone-otp.php');
        }

        $verify = verifyPhoneOtp(

            $phone,

            $otp,

            'login'
        );

        if (!$verify['success']) {

            $_SESSION['error'] =
            $verify['message'];

            redirect('verify-phone-otp.php');
        }

        try {

            $query = "

                SELECT *

                FROM users

                WHERE phone = :phone

                LIMIT 1
            ";

            $stmt =
            $this->conn->prepare($query);

            $stmt->execute([

                ':phone' => $phone
            ]);

            $user =
            $stmt->fetch();

            if (!$user) {

                $_SESSION['error'] =
                'User not found.';

                redirect('phone-login.php');
            }

            /*
            |------------------------------------------------------------------
            | UPDATE PHONE VERIFIED
            |------------------------------------------------------------------
            */

            $query = "

                UPDATE users

                SET
                    phone_verified = 1,
                    last_activity = NOW(),
                    last_ip = :last_ip

                WHERE id = :id
            ";

            $stmt =
            $this->conn->prepare($query);

            $stmt->execute([

                ':last_ip' =>
                $_SERVER['REMOTE_ADDR'] ?? null,

                ':id' =>
                $user['id']
            ]);

            /*
            |------------------------------------------------------------------
            | INITIALIZE SESSION
            |------------------------------------------------------------------
            */

            initializeSessionSecurity($user);

            /*
            |------------------------------------------------------------------
            | LOGIN ACTIVITY
            |------------------------------------------------------------------
            */

            $this->recordLoginActivity(

                $user['id'],

                'success'
            );

            clearOtpSession();

            logSecurityEvent(

                $user['id'],

                'client_login_success',

                'info',

                'Client login successful'
            );

            $_SESSION['success'] =
            'Login successful.';

            redirect('client/dashboard.php');

        } catch (Exception $e) {

            error_log($e->getMessage());

            $_SESSION['error'] =
            'Verification failed.';

            redirect('phone-login.php');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ADMIN LOGIN
    |--------------------------------------------------------------------------
    */

    public function adminLogin()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

            redirect('admin/login.php');
        }

        validateCsrf();

        if (!adminLoginRateLimit()) {

            $_SESSION['error'] =
            'Too many login attempts.';

            redirect('admin/login.php');
        }

        $email = sanitize(
            $_POST['email'] ?? ''
        );

        $password =
        $_POST['password'] ?? '';

        if (
            empty($email)
            ||
            empty($password)
        ) {

            $_SESSION['error'] =
            'All fields are required.';

            redirect('admin/login.php');
        }

        try {

            $query = "

                SELECT *

                FROM users

                WHERE email = :email

                AND role = 'admin'

                LIMIT 1
            ";

            $stmt =
            $this->conn->prepare($query);

            $stmt->execute([

                ':email' => $email
            ]);

            $admin =
            $stmt->fetch();

            if (!$admin) {

                $_SESSION['error'] =
                'Invalid credentials.';

                redirect('admin/login.php');
            }

            /*
            |------------------------------------------------------------------
            | LOCKED ACCOUNT
            |------------------------------------------------------------------
            */

            if (
                !empty($admin['locked_until'])
                &&
                strtotime($admin['locked_until']) > time()
            ) {

                $_SESSION['error'] =
                'Account temporarily locked.';

                redirect('admin/login.php');
            }

            /*
            |------------------------------------------------------------------
            | PASSWORD VERIFY
            |------------------------------------------------------------------
            */

            if (
                !verifyPassword(
                    $password,
                    $admin['password']
                )
            ) {

                $this->incrementFailedAttempts(
                    $admin['id']
                );

                $this->recordLoginActivity(

                    $admin['id'],

                    'failed'
                );

                logSecurityEvent(

                    $admin['id'],

                    'admin_login_failed',

                    'warning',

                    'Invalid admin password'
                );

                $_SESSION['error'] =
                'Invalid credentials.';

                redirect('admin/login.php');
            }

            /*
            |------------------------------------------------------------------
            | ACTIVE CHECK
            |------------------------------------------------------------------
            */

            if ($admin['status'] !== 'active') {

                $_SESSION['error'] =
                'Account inactive.';

                redirect('admin/login.php');
            }

            /*
            |------------------------------------------------------------------
            | RESET ATTEMPTS
            |------------------------------------------------------------------
            */

            $this->resetFailedAttempts(
                $admin['id']
            );

            /*
            |------------------------------------------------------------------
            | INIT SESSION
            |------------------------------------------------------------------
            */

            initializeSessionSecurity($admin);

            $_SESSION['is_admin'] = true;

            $_SESSION['admin_ip'] =
            $_SERVER['REMOTE_ADDR'] ?? null;

            $_SESSION['admin_user_agent'] =
            $_SERVER['HTTP_USER_AGENT'] ?? null;

            /*
            |------------------------------------------------------------------
            | LOGIN ACTIVITY
            |------------------------------------------------------------------
            */

            $this->recordLoginActivity(

                $admin['id'],

                'success'
            );

            /*
            |------------------------------------------------------------------
            | EMAIL ALERT
            |------------------------------------------------------------------
            */

            sendAdminLoginAlert(

                $admin['email'],

                $admin['full_name']
            );

            logSecurityEvent(

                $admin['id'],

                'admin_login_success',

                'info',

                'Admin login successful'
            );

            $_SESSION['success'] =
            'Welcome back Admin.';

            redirect('admin/dashboard.php');

        } catch (Exception $e) {

            error_log($e->getMessage());

            $_SESSION['error'] =
            'Login failed.';

            redirect('admin/login.php');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */

    public function logout()
    {
        if (isLoggedIn()) {

            logSecurityEvent(

                currentUserId(),

                'logout',

                'info',

                'User logout'
            );
        }

        destroySession();

        redirect('login.php');
    }

    /*
    |--------------------------------------------------------------------------
    | FAILED ATTEMPTS
    |--------------------------------------------------------------------------
    */

    private function incrementFailedAttempts($userId)
    {
        $query = "

            UPDATE users

            SET failed_attempts = failed_attempts + 1

            WHERE id = :id
        ";

        $stmt =
        $this->conn->prepare($query);

        $stmt->execute([

            ':id' => $userId
        ]);

        /*
        |------------------------------------------------------------------
        | FETCH UPDATED COUNT
        |------------------------------------------------------------------
        */

        $query = "

            SELECT failed_attempts

            FROM users

            WHERE id = :id
        ";

        $stmt =
        $this->conn->prepare($query);

        $stmt->execute([

            ':id' => $userId
        ]);

        $user =
        $stmt->fetch();

        if (
            $user
            &&
            $user['failed_attempts'] >= 5
        ) {

            $lockQuery = "

                UPDATE users

                SET locked_until = DATE_ADD(NOW(), INTERVAL 15 MINUTE)

                WHERE id = :id
            ";

            $stmt =
            $this->conn->prepare($lockQuery);

            $stmt->execute([

                ':id' => $userId
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | RESET FAILED ATTEMPTS
    |--------------------------------------------------------------------------
    */

    private function resetFailedAttempts($userId)
    {
        $query = "

            UPDATE users

            SET
                failed_attempts = 0,
                locked_until = NULL

            WHERE id = :id
        ";

        $stmt =
        $this->conn->prepare($query);

        $stmt->execute([

            ':id' => $userId
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | LOGIN ACTIVITY
    |--------------------------------------------------------------------------
    */

    private function recordLoginActivity(

        $userId,

        $status = 'success'
    ) {

        try {

            $query = "

                INSERT INTO login_activity (

                    user_id,
                    ip_address,
                    user_agent,
                    login_status,
                    device_info,
                    login_time

                ) VALUES (

                    :user_id,
                    :ip_address,
                    :user_agent,
                    :login_status,
                    :device_info,
                    NOW()
                )
            ";

            $stmt =
            $this->conn->prepare($query);

            $stmt->execute([

                ':user_id' => $userId,

                ':ip_address' =>
                $_SERVER['REMOTE_ADDR'] ?? null,

                ':user_agent' =>
                $_SERVER['HTTP_USER_AGENT'] ?? null,

                ':login_status' =>
                $status,

                ':device_info' =>
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);

        } catch (Exception $e) {

            error_log($e->getMessage());
        }
    }
}
?>

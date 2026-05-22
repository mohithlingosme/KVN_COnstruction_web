<?php

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| USER MODEL
|--------------------------------------------------------------------------
| File:
| /app/models/User.php
|--------------------------------------------------------------------------
*/

class User
{
    private $conn;

    private $table = 'users';

    public function __construct($database)
    {
        $this->conn = $database;
    }

    /*
    |--------------------------------------------------------------------------
    | FIND USER BY ID
    |--------------------------------------------------------------------------
    */

    public function findById($id)
    {
        $query = "

            SELECT *

            FROM {$this->table}

            WHERE id = :id

            LIMIT 1
        ";

        $stmt =
        $this->conn->prepare($query);

        $stmt->execute([

            ':id' => $id
        ]);

        return $stmt->fetch();
    }

    /*
    |--------------------------------------------------------------------------
    | FIND USER BY EMAIL
    |--------------------------------------------------------------------------
    */

    public function findByEmail($email)
    {
        $query = "

            SELECT *

            FROM {$this->table}

            WHERE email = :email

            LIMIT 1
        ";

        $stmt =
        $this->conn->prepare($query);

        $stmt->execute([

            ':email' => $email
        ]);

        return $stmt->fetch();
    }

    /*
    |--------------------------------------------------------------------------
    | FIND USER BY PHONE
    |--------------------------------------------------------------------------
    */

    public function findByPhone($phone)
    {
        $query = "

            SELECT *

            FROM {$this->table}

            WHERE phone = :phone

            LIMIT 1
        ";

        $stmt =
        $this->conn->prepare($query);

        $stmt->execute([

            ':phone' => $phone
        ]);

        return $stmt->fetch();
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE CLIENT USER
    |--------------------------------------------------------------------------
    */

    public function createClient($data = [])
    {
        $query = "

            INSERT INTO {$this->table} (

                full_name,
                email,
                phone,
                password,
                role,
                status,
                phone_verified,
                created_at

            ) VALUES (

                :full_name,
                :email,
                :phone,
                :password,
                'client',
                'active',
                0,
                NOW()
            )
        ";

        $stmt =
        $this->conn->prepare($query);

        return $stmt->execute([

            ':full_name' =>
            sanitize($data['full_name'] ?? ''),

            ':email' =>
            sanitize($data['email'] ?? ''),

            ':phone' =>
            sanitizePhone(
                $data['phone'] ?? ''
            ),

            ':password' =>
            !empty($data['password'])

            ?

            hashPassword(
                $data['password']
            )

            :

            null
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE ADMIN USER
    |--------------------------------------------------------------------------
    */

    public function createAdmin($data = [])
    {
        $query = "

            INSERT INTO {$this->table} (

                full_name,
                email,
                phone,
                password,
                role,
                status,
                phone_verified,
                created_at

            ) VALUES (

                :full_name,
                :email,
                :phone,
                :password,
                'admin',
                'active',
                1,
                NOW()
            )
        ";

        $stmt =
        $this->conn->prepare($query);

        return $stmt->execute([

            ':full_name' =>
            sanitize($data['full_name'] ?? ''),

            ':email' =>
            sanitize($data['email'] ?? ''),

            ':phone' =>
            sanitizePhone(
                $data['phone'] ?? ''
            ),

            ':password' =>
            hashPassword(
                $data['password']
            )
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE PHONE VERIFIED
    |--------------------------------------------------------------------------
    */

    public function markPhoneVerified($userId)
    {
        $query = "

            UPDATE {$this->table}

            SET phone_verified = 1

            WHERE id = :id
        ";

        $stmt =
        $this->conn->prepare($query);

        return $stmt->execute([

            ':id' => $userId
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE PASSWORD
    |--------------------------------------------------------------------------
    */

    public function updatePassword(

        $userId,

        $password
    ) {

        $query = "

            UPDATE {$this->table}

            SET

                password = :password,
                updated_at = NOW()

            WHERE id = :id
        ";

        $stmt =
        $this->conn->prepare($query);

        return $stmt->execute([

            ':password' =>
            hashPassword($password),

            ':id' =>
            $userId
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE LAST LOGIN
    |--------------------------------------------------------------------------
    */

    public function updateLastLogin($userId)
    {
        $query = "

            UPDATE {$this->table}

            SET

                last_login = NOW(),
                last_activity = NOW(),
                last_ip = :last_ip

            WHERE id = :id
        ";

        $stmt =
        $this->conn->prepare($query);

        return $stmt->execute([

            ':last_ip' =>
            $_SERVER['REMOTE_ADDR']
            ?? null,

            ':id' =>
            $userId
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE LAST ACTIVITY
    |--------------------------------------------------------------------------
    */

    public function updateActivity($userId)
    {
        $query = "

            UPDATE {$this->table}

            SET last_activity = NOW()

            WHERE id = :id
        ";

        $stmt =
        $this->conn->prepare($query);

        return $stmt->execute([

            ':id' => $userId
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | INCREMENT FAILED ATTEMPTS
    |--------------------------------------------------------------------------
    */

    public function incrementFailedAttempts($userId)
    {
        $query = "

            UPDATE {$this->table}

            SET failed_attempts = failed_attempts + 1

            WHERE id = :id
        ";

        $stmt =
        $this->conn->prepare($query);

        $stmt->execute([

            ':id' => $userId
        ]);

        /*
        |--------------------------------------------------------------------------
        | AUTO LOCK ACCOUNT
        |--------------------------------------------------------------------------
        */

        $user =
        $this->findById($userId);

        if (

            $user

            &&

            $user['failed_attempts']
            >=
            5
        ) {

            $this->lockAccount($userId);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | RESET FAILED ATTEMPTS
    |--------------------------------------------------------------------------
    */

    public function resetFailedAttempts($userId)
    {
        $query = "

            UPDATE {$this->table}

            SET

                failed_attempts = 0,
                locked_until = NULL

            WHERE id = :id
        ";

        $stmt =
        $this->conn->prepare($query);

        return $stmt->execute([

            ':id' => $userId
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | LOCK ACCOUNT
    |--------------------------------------------------------------------------
    */

    public function lockAccount(

        $userId,

        $minutes = 15
    ) {

        $query = "

            UPDATE {$this->table}

            SET

                locked_until = DATE_ADD(
                    NOW(),
                    INTERVAL :minutes MINUTE
                )

            WHERE id = :id
        ";

        $stmt =
        $this->conn->prepare($query);

        $stmt->bindValue(

            ':minutes',

            (int)$minutes,

            PDO::PARAM_INT
        );

        $stmt->bindValue(

            ':id',

            $userId,

            PDO::PARAM_INT
        );

        return $stmt->execute();
    }

    /*
    |--------------------------------------------------------------------------
    | CHECK ACCOUNT LOCK
    |--------------------------------------------------------------------------
    */

    public function isLocked($user)
    {
        if (

            empty($user['locked_until'])
        ) {

            return false;
        }

        return

            strtotime(
                $user['locked_until']
            )

            >

            time();
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE STATUS
    |--------------------------------------------------------------------------
    */

    public function updateStatus(

        $userId,

        $status
    ) {

        $query = "

            UPDATE {$this->table}

            SET status = :status

            WHERE id = :id
        ";

        $stmt =
        $this->conn->prepare($query);

        return $stmt->execute([

            ':status' => $status,

            ':id' => $userId
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE USER
    |--------------------------------------------------------------------------
    */

    public function delete($userId)
    {
        $query = "

            DELETE FROM {$this->table}

            WHERE id = :id
        ";

        $stmt =
        $this->conn->prepare($query);

        return $stmt->execute([

            ':id' => $userId
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | GET ALL CLIENTS
    |--------------------------------------------------------------------------
    */

    public function getClients()
    {
        $query = "

            SELECT *

            FROM {$this->table}

            WHERE role = 'client'

            ORDER BY id DESC
        ";

        $stmt =
        $this->conn->prepare($query);

        $stmt->execute();

        return $stmt->fetchAll();
    }

    /*
    |--------------------------------------------------------------------------
    | GET ALL ADMINS
    |--------------------------------------------------------------------------
    */

    public function getAdmins()
    {
        $query = "

            SELECT *

            FROM {$this->table}

            WHERE role = 'admin'

            ORDER BY id DESC
        ";

        $stmt =
        $this->conn->prepare($query);

        $stmt->execute();

        return $stmt->fetchAll();
    }

    /*
    |--------------------------------------------------------------------------
    | SEARCH USERS
    |--------------------------------------------------------------------------
    */

    public function search($keyword)
    {
        $query = "

            SELECT *

            FROM {$this->table}

            WHERE

                full_name LIKE :keyword

                OR

                email LIKE :keyword

                OR

                phone LIKE :keyword

            ORDER BY id DESC
        ";

        $stmt =
        $this->conn->prepare($query);

        $stmt->execute([

            ':keyword' =>
            '%' . $keyword . '%'
        ]);

        return $stmt->fetchAll();
    }

    /*
    |--------------------------------------------------------------------------
    | USER COUNT
    |--------------------------------------------------------------------------
    */

    public function totalUsers()
    {
        $query = "

            SELECT COUNT(*) as total

            FROM {$this->table}
        ";

        $stmt =
        $this->conn->prepare($query);

        $stmt->execute();

        $result =
        $stmt->fetch();

        return $result['total'] ?? 0;
    }

    /*
    |--------------------------------------------------------------------------
    | CLIENT COUNT
    |--------------------------------------------------------------------------
    */

    public function totalClients()
    {
        $query = "

            SELECT COUNT(*) as total

            FROM {$this->table}

            WHERE role = 'client'
        ";

        $stmt =
        $this->conn->prepare($query);

        $stmt->execute();

        $result =
        $stmt->fetch();

        return $result['total'] ?? 0;
    }
}
?>
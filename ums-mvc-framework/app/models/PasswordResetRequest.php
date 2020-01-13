<?php
namespace app\models;

use \PDO;

/**
 * Class model for CRUD operations on pending password reset request db table
 * @author Andrea Serra (DevAS) https://devas.info
 */
class PasswordResetRequest {
    protected $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */
    
    /* ############# CREATE FUNCTIONS ############# */

    /* function to add new password reset request */
    public function newPasswordResetReq(int $userId, string $ipAddress, string $expireDatetime) {
        /* set fail result */
        $result = [
            MESSAGE => 'Create a new password reset request failed',
            SUCCESS => FALSE
        ];

        /* prepare sql query and execute it */
        $sql = 'INSERT INTO '.PASSWORD_RESET_REQ_TABLE.' ('.USER_ID_FRGN.', '.PASSWORD_RESET_TOKEN.', '.IP_ADDRESS.', '.EXPIRE_DATETIME.') VALUES ';
        $sql .= "(:user_id, :token, :ip_addr, :expire_datetime)";
        $stmt = $this->conn->prepare($sql);
        $token = $this->getNewPassResetToken();
        $stmt->execute([
            'user_id' => $userId,
            'token' => $token,
            'ip_addr' => $ipAddress,
            'expire_datetime' => $expireDatetime
        ]);

        /* if sql query success, then return success result */
        if ($stmt && $stmt->rowCount()) {
            $result[TOKEN] = $token;
            $result[SUCCESS] = TRUE;
            $result[MESSAGE] = 'New password reset request created successfully';
        /* else set error info */
        } else $result[ERROR_INFO] = $stmt->errorInfo();

        /* return result */
        return $result;
    }

    /* ############# READ FUNCTIONS ############# */

    /* function to get user by login session token */
    public function getUserByResetPasswordToken(string $token, bool $unsetPassword = TRUE) {
        /* prepare sql query, then execute */
        $sql = 'SELECT * FROM '.PASSWORD_RESET_REQ_TABLE.' JOIN ';
        $sql .= USERS_TABLE.' ON '.USER_ID_FRGN.'='.USER_ID;
        $sql .= ' WHERE '.PASSWORD_RESET_TOKEN.' = :token';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['token' => $token]);

        /* if find user chech if session is expire */
        if ($stmt && ($user = $stmt->fetch(PDO::FETCH_OBJ))) {
            /* if require unset password and return user */
            if ($unsetPassword) unset($user->password);
            return $user;
        }
        /* else return false */
        return FALSE;
    }

    /* ############# UPDATE FUNCTIONS ############# */

    /* function to remove login session by session id */
    public function removePasswordResetReqForUser(int $userId): bool {
        /* prepare sql query and execute it */
        $sql = 'UPDATE '.PASSWORD_RESET_REQ_TABLE.' SET '.PASSWORD_RESET_TOKEN.'=NULL WHERE '.USER_ID_FRGN.'=:id';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $userId]);

        /* if sql query success return true */
        if ($stmt && $stmt->rowCount()) return TRUE;
        /* else return false */
        return FALSE;
    }

    /* ##################################### */
    /* PRIVATE FUNCTIONS */
    /* ##################################### */

    /* function to get new login session token */
    private function getNewPassResetToken(): string {
        /* generates a random string until it's not unique */
        do $token = getSecureRandomString();
        while ($this->getUserByResetPasswordToken($token));

        /* return unique token */
        return $token;
    }
}

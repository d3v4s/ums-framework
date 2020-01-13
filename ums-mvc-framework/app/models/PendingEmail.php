<?php
namespace app\models;

use \PDO;

/**
 * Class model for CRUD operations on pending emails db table
 * @author Andrea Serra (DevAS) https://devas.info
 */
class PendingEmail {
    protected $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */
    
    /* ############# CREATE FUNCTIONS ############# */

    /* function to set new login session */
    public function newPendingEmail(int $userId, string $newEmail, string $expireDatetime) {
        /* set fail result */
        $result = [
            MESSAGE => 'Adding new email failed',
            SUCCESS => FALSE
        ];

        /* prepare sql query and execute it */
        $sql = 'INSERT INTO '.PENDING_EMAILS_TABLE.' ('.USER_ID_FRGN.', '.NEW_EMAIL.', '.ENABLER_TOKEN.', '.EXPIRE_DATETIME.') VALUES ';
        $sql .= "(:user_id, :new_email, :token, :expire_datetime)";
        $stmt = $this->conn->prepare($sql);
        $enablTkn = $this->getNewEmailEnablerToken();
        $stmt->execute([
            'user_id' => $userId,
            'new_email' => $newEmail,
            'token' => $enablTkn,
            'expire_datetime' => $expireDatetime
        ]);

        /* if sql query success, then return success result */
        if ($stmt->rowCount()) {
            $result[TOKEN] = $enablTkn;
            $result[SUCCESS] = TRUE;
            $result[MESSAGE] = 'New email saved successfully';
        /* else set error info */
        } else $result[ERROR_INFO] = $stmt->errorInfo();

        /* return result */
        return $result;
    }

    /* ############# READ FUNCTIONS ############# */

    /* function to get user by login session token */
    public function getPendingEmailByUserId(string $userId) {
        /* prepare sql query, then execute */
        $sql = 'SELECT * FROM '.PENDING_EMAILS_TABLE;
        $sql .= ' WHERE '.USER_ID_FRGN.'=:id AND '.ENABLER_TOKEN.'IS NOT NULL';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $userId]);

        /* if find pending mail return it */
        if ($stmt && ($pendMail = $stmt->fetch(PDO::FETCH_OBJ))) {
            return $pendMail;
        }
        /* else return false */
        return FALSE;
    }

    /* function to get user by login session token */
    public function getPendingEmailByToken(string $token) {
        /* prepare sql query, then execute */
        $sql = 'SELECT * FROM '.PENDING_EMAILS_TABLE.' ';
        $sql .= 'WHERE '.ENABLER_TOKEN.'=:token';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['token' => $token]);
        
        /* if find pending mail return it */
        if ($stmt && ($pendMail = $stmt->fetch(PDO::FETCH_OBJ))) return $pendMail;
        /* else return false */
        return FALSE;
    }

    /* function to get user by email enabler token */
    public function getUserByEmailEnablerToken(string $token, bool $unsetPassword=TRUE) {
        /* prepare sql query, then execute */
        $sql = 'SELECT * FROM '.PENDING_EMAILS_TABLE.' JOIN ';
        $sql .= USERS_TABLE.' ON '.USER_ID_FRGN.'='.USER_ID;
        $sql .= ' WHERE '.ENABLER_TOKEN.' = :token';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['token' => $token]);
        
        /* if find user return it */
        if ($stmt && ($user = $stmt->fetch(PDO::FETCH_OBJ))) {
            /* if require unset password */
            if ($unsetPassword) unset($user->{PASSWORD});
            return $user;
        }
        /* else return false */
        return FALSE;
    }

    /* ############# UPDATE FUNCTIONS ############# */

    /* function to remove email enabler token on pending emails table */
    public function removeEmailEnablerToken(string $token): bool {
        /* prepare sql query and execute it */
        $stmt = $this->conn->prepare('UPDATE '.PENDING_EMAILS_TABLE.' SET '.ENABLER_TOKEN.'=NULL WHERE '.ENABLER_TOKEN.'=:token');
        $stmt->execute(['token' => $token]);
        
        /* if sql success return true */
        if($stmt->rowCount()) return TRUE;
        /* else return false */
        return FALSE;
    }

    /* function to remove email enabler token on pending emails table */
    public function removeAllEmailEnablerToken(string $userId): bool {
        /* prepare sql query and execute it */
        $stmt = $this->conn->prepare('UPDATE '.PENDING_EMAILS_TABLE.' SET '.ENABLER_TOKEN.'=NULL WHERE '.USER_ID_FRGN.'=:id');
        $stmt->execute(['id' => $userId]);
        
        /* if sql success return true */
        if($stmt->rowCount()) return TRUE;
        /* else return false */
        return FALSE;
    }

    /* ##################################### */
    /* PRIVATE FUNCTIONS */
    /* ##################################### */

    /* function to get new login session token */
    private function getNewEmailEnablerToken(): string {
        /* generates a random string until it's not unique */
        do $token = getSecureRandomString();
        while ($this->getPendingEmailByToken($token));

        /* return unique token */
        return $token;
    }
}

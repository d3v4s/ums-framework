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

    /* function to get pending emails list */
    public function getPendingEmails(string $orderBy=PENDING_EMAIL_ID, string $orderDir=DESC, string $search='', int $start=0, int $nRow=10) {
        /* set sql query */
        $sql = 'SELECT '.PENDING_EMAIL_ID.', '.USER_ID_FRGN.', '.NEW_EMAIL.', '.ENABLER_TOKEN.', '.EXPIRE_DATETIME.', '.USER_ID.', '.USERNAME;
        $sql .= ' FROM '.PENDING_EMAILS_TABLE.' LEFT JOIN ';
        $sql .= USERS_TABLE.' ON '.USER_ID_FRGN.'='.USER_ID;
        $data = [];
        if (!empty($search)) {
            $sql .= ' WHERE '.PENDING_EMAIL_ID.'=:searchId OR ';
            $sql .= USERNAME.' LIKE :search OR ';
            $sql .= NEW_EMAIL.' LIKE :search';
            $data = [
                'searchId' => $search,
                'search' =>"%$search%"
            ];
        }
        /* validate order by, order direction, start and num of row */
        $orderBy = in_array($orderBy, PENDING_EMAILS_ORDER_BY_LIST) ? $orderBy : PENDING_EMAIL_ID;
        $orderDir = in_array($orderDir, ORDER_DIR_LIST) ? $orderDir : DESC;
        $start = is_numeric($start) ? $start : 0;
        $nRow = is_numeric($nRow) ? $nRow : 20;
        /* prepare and execute sql query */
        $sql .= " ORDER BY $orderBy $orderDir LIMIT $start, $nRow";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($data);
        /* if success, then return users list */
        if ($stmt) return $stmt->fetchAll(PDO::FETCH_OBJ);
        /* else return empty array */
        return [];
    }

    /* function to get user by login session token */
    public function getValidPendingEmailByUserId(int $userId) {
        /* prepare sql query, then execute */
        $sql = 'SELECT * FROM '.PENDING_EMAILS_TABLE;
        $sql .= ' WHERE '.USER_ID_FRGN.'=:id AND ';
        $sql .= ENABLER_TOKEN.' IS NOT NULL ORDER BY '.PENDING_EMAIL_ID.' DESC LIMIT 1';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $userId]);

        /* if find pending mail return it */
        if ($stmt && ($pendMail = $stmt->fetch(PDO::FETCH_OBJ))) return $pendMail;
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

    /* function to get pending mail and user by pending email id */
    public function getPendingEmailLeftUser(int $pendMailId) {
        /* prepare sql query, then execute */
        $sql = 'SELECT * FROM '.PENDING_EMAILS_TABLE.' LEFT JOIN ';
        $sql .= USERS_TABLE.' ON '.USER_ID_FRGN.'='.USER_ID;
        $sql .= ' WHERE '.PENDING_EMAIL_ID.'=:id';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $pendMailId]);
        
        /* if find pending mail return it */
        if ($stmt && ($pendMail = $stmt->fetch(PDO::FETCH_OBJ))) return $pendMail;
        /* else return false */
        return FALSE;
    }

    /* function to get pending mail and user by pending email id */
    public function getValidPendingEmailAndUser(int $pendMailId) {
        /* prepare sql query, then execute */
        $sql = 'SELECT * FROM '.PENDING_EMAILS_TABLE.' JOIN ';
        $sql .= USERS_TABLE.' ON '.USER_ID_FRGN.'='.USER_ID;
        $sql .= ' WHERE '.PENDING_EMAIL_ID.'=:id AND '.ENABLER_TOKEN.' IS NOT NULL';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $pendMailId]);
        
        /* if find pending mail return it */
        if ($stmt && ($pendMail = $stmt->fetch(PDO::FETCH_OBJ))) return $pendMail;
        /* else return false */
        return FALSE;
    }

    /* function to count the all pending users on table */
    public function countAllPendingEmails($search=''): int {
        /* create sql query */
        $sql = 'SELECT COUNT(*) AS total FROM '.PENDING_EMAILS_TABLE.' LEFT JOIN ';
        $sql .= USERS_TABLE.' ON '.USER_ID.'='.USER_ID_FRGN;
        $data = [];
        if (!empty($search)) {
            $sql .= ' WHERE '.PENDING_EMAIL_ID.'=:searchId OR ';
            $sql .= USERNAME.' LIKE :search OR ';
            $sql .= NEW_EMAIL.' LIKE :search';
            $data = [
                'searchId' => $search,
                'search' =>"%$search%"
            ];
        }
        /* execute sql query */
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($data);
        /* return total users */
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    /* function to count the pending mails on table */
    public function countValidPendingEmails(): int {
        /* create sql query */
        $sql = 'SELECT COUNT(*) AS total FROM '.PENDING_EMAILS_TABLE;
        $sql .= ' WHERE '.ENABLER_TOKEN.' IS NOT NULL AND ';
        $sql .= EXPIRE_DATETIME.' > CURRENT_TIMESTAMP()';
        /* execute sql query */
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        /* return total users */
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    /* ############# UPDATE FUNCTIONS ############# */

    /* function to remove email enabler token */
    public function removeEmailEnablerTokenById(int $id): bool {
        /* prepare sql query and execute it */
        $stmt = $this->conn->prepare('UPDATE '.PENDING_EMAILS_TABLE.' SET '.ENABLER_TOKEN.'=NULL WHERE '.PENDING_EMAIL_ID.'=:id');
        $stmt->execute(['id' => $id]);
        
        /* if sql success return true */
        if($stmt->rowCount()) return TRUE;
        /* else return false */
        return FALSE;
    }

    /* function to remove email enabler token */
    public function removeEmailEnablerToken(string $token): bool {
        /* prepare sql query and execute it */
        $stmt = $this->conn->prepare('UPDATE '.PENDING_EMAILS_TABLE.' SET '.ENABLER_TOKEN.'=NULL WHERE '.ENABLER_TOKEN.'=:token');
        $stmt->execute(['token' => $token]);
        
        /* if sql success return true */
        if($stmt->rowCount()) return TRUE;
        /* else return false */
        return FALSE;
    }

    /* function to remove all email enabler tokens for user*/
    public function removeAllEmailEnablerToken(int $userId): bool {
        /* prepare sql query and execute it */
        $stmt = $this->conn->prepare('UPDATE '.PENDING_EMAILS_TABLE.' SET '.ENABLER_TOKEN.'=NULL WHERE '.USER_ID_FRGN.'=:id');
        $stmt->execute(['id' => $userId]);
        
        /* if sql success return true */
        if($stmt->rowCount()) return TRUE;
        /* else return false */
        return FALSE;
    }

    /* function to update expire datetime */
    public function updateExpireDatetime(string $token, string $datetime): bool {
        /* prepare sql query and execute it */
        $stmt = $this->conn->prepare('UPDATE '.PENDING_EMAILS_TABLE.' SET '.EXPIRE_DATETIME.'=:datetime WHERE '.ENABLER_TOKEN.'=:token');
        $stmt->execute([
            'datetime' => $datetime,
            'token' => $token
        ]);
        
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

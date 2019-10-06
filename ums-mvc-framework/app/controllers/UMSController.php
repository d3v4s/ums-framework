<?php
namespace app\controllers;

use app\models\User;
use app\models\Email;
use \PDO;

require_once __DIR__.'/../../autoload.php';
require_once __DIR__.'/../../helpers/functions.php';

class UMSController extends Controller {
    public function __construct(PDO $conn, string $layout = 'ums') {
        parent::__construct($conn, $layout);
    }

    public function showNewEmail() {
        $this->redirectIfNotAdmin();
        $this->isNewEmail = TRUE;
        $data = [
            'token' => $this->generateToken()
        ];
        $this->content = view('new-email', $data);
    }

    public function sendEmail() {
        $this->redirectIfNotAdmin();
        $this->setLayout('email');
        $from = $_POST['from'];
        $to = $_POST['to'];
        $subject = $_POST['subject'];
        $token = $_POST['_xf'] ?? 'tkn';
        $tokenSess = $_SESSION['csrf'] ?? '';
        unset($_SESSION['csrf']);

        $res = $this->verifyEmail($from, $to, $token, $tokenSess);
        if ($res['success']) {
            $this->content = $_POST['content'];
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $email = isset($subject) ? new Email($to, $from, $subject) : new Email($to, $from);
            $email->setHeaders($headers);
            $email->content = $this->getHtmlEmail();
            $res = $email->send();
            var_dump($res);
            dd($email);
        } else {
            dd($res);
            redirect("/ums/email/new");
        }
    }

    private function verifyEmail($from, $to, $token, $tokenSess) {
        $res = [
            'success' => FALSE,
            'message' => 'FAIL SEND EMAIL'
        ];

        if ($token !== $tokenSess)
            return $res;

        if (!filter_var($from, FILTER_VALIDATE_EMAIL)) {
            $res['message'] = 'WRONG FROM EMAIL';
            return $res;
        }
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $res['message'] = 'WRONG TO EMAIL';
            return $res;
        }

        unset($res['message']);
        $res['success'] = TRUE;
        return $res;
    }

    private function getHtmlEmail() {
        ob_start();
        require $this->layout;
        $content = ob_get_contents();
        ob_end_clean();
        
        return $content;
    }

    private function getStart(int $totUsers, int &$userForPage, int &$page) {
        $userForPage = in_array($userForPage, getConfig('app')['usersForPageList']) ? $userForPage : 10;
        $maxPages = ceil($totUsers/$userForPage);
        $page = $page > $maxPages ? $maxPages : $page;
        return $userForPage * ($page - 1);
    }

    public function showUsers(string $orderBy = 'id', string $orderDir = 'desc', int $page = 1, int $usersForPage = 10) {
        $this->redirectIfNotCanUpdate();
        $user = new User($this->conn);
        $search = $_GET['search'] ?? '';
        $totUsers = $user->countUsers($search);
        $orderDir = strtoupper($orderDir);
        $uri = isset($_SERVER['QUERY_STRING']) ? str_replace('?'.$_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']) : $_SERVER['REQUEST_URI'];
        $usersForPage = in_array($usersForPage, getConfig('app')['usersForPageList']) ? $usersForPage : 10;
        $maxPages = (int) ceil($totUsers/$usersForPage);
        $page = $page > $maxPages ? $maxPages : $page;
        $page = $page <= 0 ? 1 : $page;
        $start = $usersForPage * ($page - 1);
        $nlinkPagination = getConfig('app')['linkPagination'] - 1;
//         $extrLink = (int) $nlinkPagination / 2; 
        $startPage = $page - intdiv($nlinkPagination, 2);
        $startPage = (int) $startPage > ($maxPages - $nlinkPagination) ? $maxPages - $nlinkPagination : $startPage;
        $startPage = $startPage <= 0 ? 1 : $startPage;
        $stopPage = $startPage + $nlinkPagination;
        $stopPage = $stopPage >= $maxPages ? $maxPages : $stopPage;
//         dd($maxPages);
        $data = [
            'users' => $user->getUsers($orderBy, $orderDir, $search, $start, $usersForPage),
            'orderBy' => $orderBy,
            'orderDir' => strtolower($orderDir),
            'orderDirRev' => $orderDir === 'ASC' ? 'desc' : 'asc',
            'orderDirClass' => $orderDir === 'ASC' ? 'down' : 'up',
            'search' => $search,
            'page' => $page,
            'usersForPage' => $usersForPage,
            'totUsers' => $totUsers,
            'usersForPageList' => getConfig('app')['usersForPageList'],
            'uri' => $uri,
            'maxPages' => $maxPages,
            'startPage' => $startPage,
            'stopPage' => $stopPage
            
        ];
        $this->content = view('users-list', $data);
    }

    public function showUser($username) {
        $this->redirectIfNotCanUpdate();
        $user = new User($this->conn);
        if (is_numeric($username)) $usr = $user->getUser($username);
        else $usr = $user->getUserByUsername($username);
        $data = [
            'user' => $usr,
            'token' => $this->generateToken()
        ];
        $this->content = view('user-info', $data);
    }

    public function showUpdatePasswordUser($username) {
        $this->redirectIfNotAdmin();
        $user = new User($this->conn);
        if (is_numeric($username)) $usr = $user->getUser($username);
        else $usr = $user->getUserByUsername($username);
        $data = [
            'user' => $usr,
            'token' => $this->generateToken()
        ];
        $this->content = view('update-pass', $data);
    }

    public function updatePasswordUser() {
        $this->redirectIfNotAdmin();
        $id = $_POST['id'];
        $token = $_POST['_xf'] ?? 'tkn';
        $tokenSess = $_SESSION['csrf'] ?? '';
        $pass = $_POST['pass'] ?? '';
        unset($_SESSION['csrf']);
        
        $res = $this->verifyUpdatePass($id, $pass, $token, $tokenSess);
        if($res['success']) {
            $user = new User($this->conn);
            $resUser = $user->updateUserPass($id, $pass);
            if (!$resUser['success']) {
                $res['message'] = $resUser['message'];
                $res['success'] = $resUser['success'];
            }
        }
        $res['success'] ? redirect('/ums/user/'.$id) : redirect('/ums/user/'.$id.'/update/pass');
    }

    private function verifyUpdatePass($id, $pass, $token, $tokenSess) {
        $user = new User($this->conn);
        $result = [
            'message' => 'FAIL UPDATE',
            'success' => false
        ];
        
        if ($token !== $tokenSess) {
            //             $result['message'] = 'TOKEN MISMATCH';
            return $result;
        }
        if ($user->getUser($id) === FALSE) {
            $result['message'] = 'WRONG ID';
            return $result;
        }
        if (strlen($pass) < 4) {
            $result['message'] = 'PASSWORD TOO SMALL';
            return $result;
        }

        unset($result['message']);
        $result['success'] = true;
        
        return $result;
    }

    public function showUpdateUser($username) {
        $this->redirectIfNotCanUpdate();
        $user = new User($this->conn);
        if (is_numeric($username)) $usr = $user->getUser($username);
        else $usr = $user->getUserByUsername($username);
        $data = [
            'user' => $usr,
            'token' => $this->generateToken()
        ];
        $this->content = view('user-update', $data);
    }

    public function updateUser() {
        $this->redirectIfNotCanUpdate();
        $id = $_POST['id'];
        $token = $_POST['_xf'] ?? 'tkn';
        $tokenSess = $_SESSION['csrf'] ?? '';
        $email = $_POST['email'] ?? '';
        $username = $_POST['username'] ?? '';
        $name = $_POST['name'] ?? '';
        $roletype = isUserAdmin() ? $_POST['role'] : '';
        $enabled = isUserAdmin() && isset($_POST['enabled']) ? $_POST['enabled'] : '';
        $enabled = $enabled === 'true' ? TRUE : FALSE;
        unset($_SESSION['csrf']);
        
        $res = $this->verifyUpdate($id, $email, $username, $token, $tokenSess);
        if($res['success']) {
            $user = new User($this->conn);
            $data = compact('email', 'username', 'name', 'roletype', 'enabled');
            $resUser = $user->updateUser($id, $data);
            if (!$resUser['success']) {
                $res['message'] = $resUser['message'];
                $res['success'] = $resUser['success'];
            }
        }
        $res['success'] ? redirect('/ums/users') : redirect('/ums/user/'.$id.'/update');
//         dd($res);
    }

    private function verifyUpdate($id, $email, $username, $token, $tokenSess) {
        $user = new User($this->conn);
        $result = [
            'message' => 'FAIL UPDATE',
            'success' => false
        ];
        
        if ($token !== $tokenSess) {
            //             $result['message'] = 'TOKEN MISMATCH';
            return $result;
        }
        if ($user->getUser($id) === FALSE) {
            $result['message'] = 'WRONG ID';
            return $result;
        }
        if (!($email = filter_var($email, FILTER_VALIDATE_EMAIL))) {
            $result['message'] = 'WRONG EMAIL';
            return $result;
        }
        
        unset($result['message']);
        $result['success'] = true;
        
        return $result;
    }

    public function showNewUser() {
        $this->redirectIfNotCanCreate();
        $this->isNewUser = TRUE;
        $this->content = view('new-user', ['token' => $this->generateToken()]);
    }

    public function newUser() {
        $this->redirectIfNotCanCreate();
        $token = $_POST['_xf'] ?? 'tkn';
        $tokenSess = $_SESSION['csrf'] ?? '';
        $email = $_POST['email'] ?? '';
        $username = $_POST['username'] ?? '';
        $name = $_POST['name'] ?? '';
        $pass = $_POST['pass'] ?? '';
        $roletype = $_POST['role'] ?? 'user';
        $enabled = isset($_POST['enabled']) && $_POST['enabled'] === 'true' ? TRUE : FALSE;
        unset($_SESSION['csrf']);
        
        $res = $this->verifySignup($email, $username, $pass, $token, $tokenSess);
        if($res['success']) {
            $user = new User($this->conn);
            $data = compact('email', 'username', 'name', 'pass', 'roletype', 'enabled');
            $resUser = $user->saveUser($data);
            if (!$resUser['success']) {
                $res['message'] = $resUser['message'];
                $res['success'] = $resUser['success'];
            } 
        }
        $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
        switch ($header) {
            case 'XMLHTTPREQUEST':
                echo json_encode($res);
                exit;
            default:
                if (isset($res['message'])) $_SESSION['message'] = $res['message'];
                $res['success'] ? redirect('/ums/users') : redirect('/ums/user/new');
        };
    }

    public function deleteUser() {
        $this->redirectIfNotCanDelete();
        $id = $_POST['id'];
        $token = $_POST['_xf'] ?? 'tkn';
        $tokenSess = $_SESSION['csrf'] ?? '';
        unset($_SESSION['csrf']);

        $res = $this->verifyDelete($id, $token, $tokenSess);
        if($res['success']) {
            $user = new User($this->conn);
            $resUser = $user->deleteUser($id);
            if (!$resUser['success']) {
                $res['message'] = $resUser['message'];
                $res['success'] = $resUser['success'];
            }
        }
        $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
        switch ($header) {
            case 'XMLHTTPREQUEST':
                echo json_encode($res);
                exit;
            default:
                if (isset($res['message'])) $_SESSION['message'] = $res['message'];
                $res['success'] ? redirect('/ums/users') : redirect("/ums/user/$id");
        };
        
    }
    
    private function verifyDelete($id, $token, $tokenSess) {
        $user = new User($this->conn);
        $result = [
            'message' => 'FAIL DELETE',
            'success' => false
        ];

        if ($token !== $tokenSess) {
            //             $result['message'] = 'TOKEN MISMATCH';
            return $result;
        }

        if ($user->getUser($id) === FALSE) {
            $result['messsage'] = 'WRONG ID';
            return $result;
        }

        unset($result['message']);
        $result['success'] = true;
        
        return $result;
    }
}
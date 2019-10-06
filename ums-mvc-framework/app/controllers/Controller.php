<?php
namespace app\controllers;

use \PDO;
use app\models\User;

class Controller {
//     protected const layout = __DIR__.'/../../layout/default.tpl.php';
    protected $content;
    protected $conn;
    protected $layout;
    protected $addFakeUsers;
    protected $isHome = FALSE;
    protected $isLogin = FALSE;
    protected $isSignup = FALSE;
    protected $isNewUser = FALSE;
    protected $isNewEmail = FALSE;
    protected $isAddFakeUsers = FALSE;

    public function __construct(PDO $conn, string $layout = 'default') {
        $this->conn = $conn;
        $this->layout = __DIR__.'/../../layout/'.getConfig('layout')[$layout].'.tpl.php';
        $this->addFakeUsers = getConfig('app')['addFakeUsers'] ?? FALSE;
    }
    
    public function setLayout(string $layout) {
        $this->layout = __DIR__.'/../../layout/'.getConfig('layout')[$layout].'.tpl.php';
    }

    public function showHome() {
        $this->isHome = TRUE;
        $this->content = view('home');
    }

    public function display() {
        require_once $this->layout;
    }

    protected function redirectIfNotLoggin() {
        if(!isUserLoggedin()) redirect("/auth/login");
    }
    
    protected function redirectIfNotAdmin() {
        if(!isUserAdmin()) redirect("/");
    }

    protected function redirectIfNotCanCreate() {
        if(!userCanCreate()) redirect("/");
    }

    protected function redirectIfNotCanUpdate() {
        if(!userCanUpdate()) redirect("/");
    }

    protected function redirectIfNotCanDelete() {
        if(!userCanCreate()) redirect("/");
    }

    protected function redirectIfLoggin() {
        if(isUserLoggedin()) redirect("/");
    }

    protected function generateToken() {
        $bytes = random_bytes(32);
        $token = bin2hex($bytes);
        $_SESSION['csrf'] = $token;
        return $token;
    }

    protected function verifyToken($token, $tokenSess) {
        unset($_SESSION['csrf']);
        return $token === $tokenSess;
    }

    protected function verifySignup(string $email, string $username, string $pass, string $token, string $tokenSess): array {
        $user = new User($this->conn);
        $result = [
            'message' => 'FAIL SIGNUP',
            'success' => false
        ];
        
        if ($token !== $tokenSess) {
            //             $result['message'] = 'TOKEN MISMATCH';
            return $result;
        }
        if (!($email = filter_var($email, FILTER_VALIDATE_EMAIL))) {
            $result['message'] = 'WRONG EMAIL';
            return $result;
        }
        if (strlen($pass) < 4) {
            $result['message'] = 'PASSWORD TOO SMALL';
            return $result;
        }
        $resUser = $user->getUserByEmail($email);
        if($resUser) {
            $result['message'] = 'USER ALREADY EXISTS WITH THIS EMAIL';
            return $result;
        }
        $resUser = $user->getUserByUsername($username);
        if($resUser) {
            $result['message'] = 'USER ALREADY EXISTS WITH THIS USERNAME';
            return $result;
        }
        
        unset($result['message']);
        $result['success'] = true;
        
        return $result;
    }
}


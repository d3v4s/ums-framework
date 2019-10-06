<?php
namespace app\controllers;

use app\models\User;

use \PDO;

require_once __DIR__.'/../../autoload.php';
require_once __DIR__.'/../../helpers/functions.php';

class LoginController extends Controller {
    public function __construct(PDO $conn) {
        parent::__construct($conn);
    }

    public function logout() {
        session_regenerate_id();
        $_SESSION = [];
        redirect('/');
    }

    public function validateEmail(string $hash) {
//         $this->redirectIfLoggin();
        $user = new User($this->conn);
        $userEnabled = $user->getUserByHash($hash);
        if ($userEnabled) {
            $id = $userEnabled->id;
            $res = $user->enabledUser($id);
        }
        if (isset($res['message'])) $_SESSION['message'] = $res['message'];
        $res['success'] ? redirect('/auth/login') : redirect('/auth/signup');
    }

    public function signup() {
        $this->redirectIfLoggin();
        $token = $_POST['_xf'] ?? 'tkn';
        $tokenSess = $_SESSION['csrf'] ?? '';
        $email = $_POST['email'] ?? '';
        $username = $_POST['username'] ?? '';
        $name = $_POST['name'] ?? '';
        $pass = $_POST['pass'] ?? '';
        unset($_SESSION['csrf']);

        $res = $this->verifySignup($email, $username, $pass, $token, $tokenSess);
        if($res['success']) {
            $user = new User($this->conn);
            $enabled = FALSE;
            $data = compact('email', 'username', 'name', 'pass', 'enabled');
            $resUser = $user->saveUser($data);
            if ($resUser['success']) {
                session_regenerate_id();
                $_SESSION = [];
                $newUser = $user->getUser($resUser['id']);
                $_SESSION['link'] = '/validate/email/'.$newUser->hash_confirm;
//                 $_SESSION['loggedin'] = true;
//                 $_SESSION['user'] = $user->getUser($resUser['id']);
//                 $_SESSION['success'] = true;

            } else
                $res['message'] = $resUser['message'];
        }

        $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
        switch ($header) {
            case 'XMLHTTPREQUEST':
                echo json_encode($res);
                exit;
            default:
                if (isset($res['message'])) $_SESSION['message'] = $res['message'];
                $res['success'] ? redirect('/') : redirect('/auth/signup');
        }
    }

    public function login() {
        $this->redirectIfLoggin();
        if (isset($_SESSION['loggedin']) && $_SESSION['loggedin']) redirect('/');
        $token = $_POST['_xf'] ?? 'tkn';
        $tokenSess = $_SESSION['csrf'] ?? '';
        $user = $_POST['user'] ?? '';
        $pass = $_POST['pass'] ?? '';
        unset($_SESSION['csrf']);

        $key = openssl_pkey_get_private($_SESSION['privKey']);
        // conversione da annotazione hex
        $data = pack('H*', $pass);
        openssl_private_decrypt($data, $pass, $key);
        $data = pack('H*', $user);
        openssl_private_decrypt($data, $user, $key);

        $res = $this->verifyLogin($user, $pass, $token, $tokenSess);
        if($res['success']) {
            session_regenerate_id();
            $_SESSION = [];
            $_SESSION['loggedin'] = true;
            $_SESSION['user'] = $res['user'];
            $_SESSION['success'] = true;
        }

        $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
        switch ($header) {
            case 'XMLHTTPREQUEST':
//                 dd($res); die;
                echo json_encode($res);
                exit;
            default:
                if (isset($res['message'])) $_SESSION['message'] = $res['message'];
                $res['success'] ? redirect('/') : redirect('/auth/login');
                break;
        }
    }

    public function showLogin() {
        $this->redirectIfLoggin();
//         if (isset($_SESSION['loggedin']) && $_SESSION['loggedin']) redirect('/');
        $this->isLogin = true;
        $config = array(
            "digest_alg" => "sha512",
            "private_key_bits" => 4096,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        );
        $res = openssl_pkey_new($config);
        $privKey = '';
        openssl_pkey_export($res, $privKey);
        $_SESSION['privKey'] = $privKey;
//         echo $privKey . "<br><br><br>";
        $details = openssl_pkey_get_details($res);
        $keyN = toHex($details['rsa']['n']);
        $keyE = toHex($details['rsa']['e']);
//         unset($details);
//         dd($details);
//         $pubKey = $details['key'];
//         echo $pubKey;
        $this->content = view('login', [
            'token' => $this->generateToken(),
            'keyN' => $keyN,
            'keyE' => $keyE
        ]);
    }

    public function showSignup() {
        $this->redirectIfLoggin();
        $this->isSignup = true;
        $this->content = view('signup', ['token' => $this->generateToken()]);
    }

    private function verifyLogin(string $username, string $pass, string $token, string $tokenSess): array {
        $user = new User($this->conn);
        $result = [
            'message' => 'FAIL LOGIN',
            'success' => false
        ];
        
        if ($token !== $tokenSess) {
//             $result['message'] = 'TOKEN MISMATCH';
            return $result;
        }
        if (strlen($pass) < 4) {
            $result['message'] = 'PASSWORD TOO SMALL';
            return $result;
        }
        if ($email = filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $resUser = $user->getUserByEmail($email);
            if(!$resUser) {
                $result['message'] = 'USER NOT FOUND - WRONG EMAIL';
                return $result;
            }
        } else {
            $resUser = $user->getUserByUsername($username);
            if(!$resUser) {
                $result['message'] = 'USER NOT FOUND - WRONG USERNAME';
                return $result;
            }
        }
        if (!$resUser->enabled) {
            $result['message'] = 'DISABLED ACCOUNT';
            return $result;
        }

        if (!password_verify($pass, $resUser->password)) {
//             $result['message'] = 'WRONG PASSWORD';
            return $result;
        }
        
        $result['message'] = 'USER LOGGED IN';
        $result['success'] = true;
        unset($resUser->password);
        $result['user'] = $resUser;
        
        return $result;
    }
}


<?php
namespace app\controllers;

use app\models\User;

use \PDO;
use app\controllers\verifiers\Verifier;
use app\controllers\verifiers\LoginVerifier;

class LoginController extends Controller {
    public function __construct(PDO $conn, array $appConfig, string $layout = 'default') {
        parent::__construct($conn, $appConfig, $layout);
    }

    public function logout() {
        $this->redirectIfNotLoggin();

        $tokens = $this->getPostSessionTokens('_xf-out', 'csrfLogout');
        $id = getUserLoggedID();

        $verifier = LoginVerifier::getInstance($this->appConfig, $this->conn);
        $resLogout = $verifier->verifyLogout($id, $tokens);
        if ($resLogout['success']) {
            $this->resetSession();
            $resLogout['message'] = 'Succesfully logout'; 
        }

        $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
        switch ($header) {
            case 'XMLHTTPREQUEST':
                $resJSON = [
                    'success' => $resLogout['success'],
                    'message' => $resLogout['message'] ?? NULL
                ];
                if (!$resLogout['success']) $resJSON['ntk'] = generateToken('csrfLogout');
                header("Content-Type: application/json");
                header("X-Content-Type-Options: nosniff");
                echo json_encode($resJSON);
                exit;
            default:
                if (isset($resLogout['message'])) {
                    $_SESSION['message'] = $resLogout['message'];
                    $_SESSION['success'] = $resLogout['success'];
                }
                redirect();
                break;
        }
    }

    public function validateNewEmail(string $token) {
        $this->redirectIfNotEmailConfirmRequire();

        $verifier = LoginVerifier::getInstance($this->appConfig, $this->conn);
        $resValidate = $verifier->verifyValidateNewEmail($token);
        if (!$resValidate['success']) {
            $this->showPageNotFound();
            return;
        }

        $user = new User($this->conn, $this->appConfig);
        if (isset($resValidate['deleteUser'])) $user->deleteUser($resValidate['deleteUser']);
        $res = $user->confirmEmail($resValidate['userId']);

        if (isset($res['message'])) {
            $_SESSION['message'] = $res['message'];
            $_SESSION['success'] = $res['success'];
        }
        if (!$res['success']) redirect('/auth/signup');

        $user->removeTokenConfirmEmail($resValidate['userId']);

        if (!isUserLoggedin()) redirect('/auth/login');

        if ($resValidate['userId'] === getUserLoggedID()) {
            $usr = $user->getUser($resValidate['userId']);
            $this->createSessionLogin($usr);
        }

        redirect();
    }

    public function enableAccount(string $token) {
        $this->redirectIfNotEmailConfirmRequire();

        $verifier = LoginVerifier::getInstance($this->appConfig, $this->conn);
        $resEnable = $verifier->verifyEnableAccount($token);
        if (!$resEnable['success']) {
            $this->showPageNotFound();
            return;
        }

        $user = new User($this->conn, $this->appConfig);
        $res = $user->enableUser($resEnable['user']->id);

        if (isset($res['message'])){
            $_SESSION['message'] = $res['message'];
            $_SESSION['success'] = $res['success'];
        }
        if (!$res['success']) redirect('/auth/signup');

        $user->removeTokenEnabler($resEnable['user']->id);
        redirect('/auth/login');
    }

    public function showResetPasswordRequest() {
        $this->redirectIfLoggin();

        $this->title .= ' - Forgot Password';
        array_push($this->jsSrcs,
            ['src' => '/js/utils/validate.js'],
            ['src' => '/js/utils/login/res-pass-req.js']
        );

        $this->content = view('login/reset-pass-req', ['token' => generateToken('csrfResPassReq')]);
    }

    public function resetPasswordRequest() {
        $this->redirectIfLoggin();

        $tokens = $this->getPostSessionTokens('_xf', 'csrfResPassReq');
        $email = $_POST['email'] ?? '';

        $verifier = LoginVerifier::getInstance($this->appConfig, $this->conn);
        $resResetPassReq = $verifier->verifyResetPassReq($email, $tokens);
        if ($resResetPassReq['success']) {
            $user = new User($this->conn, $this->appConfig);
            $resUser = $user->createTokenResetPassword($resResetPassReq['user']->id);
            if ($resUser['success']) {
                $usr = $user->getUserByEmail($email);
                $resUser['success'] = $this->sendEmailResetPassword($email, $usr->token_reset_pass);
                $resUser['message'] = $resUser['success'] ? 'Email for password reset succesfully sended' : 'Send email failed';
            } 

            $resResetPassReq['success'] = $resUser['success'];
            $resResetPassReq['message'] = $resUser['message'];
        }

        $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
        switch ($header) {
            case 'XMLHTTPREQUEST':
                $resJSON = [
                    'success' => $resResetPassReq['success'],
                    'error' => $resResetPassReq['error'] ?? NULL,
                    'message' => $resResetPassReq['message'] ?? NULL
                ];
                if (!$resResetPassReq['success']) $resJSON['ntk'] = generateToken('csrfResPassReq');
                header("Content-Type: application/json");
                header("X-Content-Type-Options: nosniff");
                echo json_encode($resJSON);
                exit;
            default:
                if (isset($resResetPassReq['message'])) {
                    $_SESSION['message'] = $resResetPassReq['message'];
                    $_SESSION['success'] = $resResetPassReq['success'];
                }
                redirect('/auth/login');
                break;
        }
    }

    public function showResetPassword(string $token) {
        $this->redirectIfLoggin();

        if (!$this->isSetTokenResetPass($token)) {
            $this->showPageNotFound();
            return;
        }

        $this->title .= ' - Reset Password';
        array_push($this->jsSrcs,
            ['src' => '/js/crypt/jsbn.js'],
            ['src' => '/js/crypt/prng4.js'],
            ['src' => '/js/crypt/rng.js'],
            ['src' => '/js/crypt/rsa.js'],
            ['src' => '/js/utils/req-key.js'],
            ['src' => '/js/utils/validate.js'],
            ['src' => '/js/utils/login/reset-pass.js']
        );

        $data = [
            'token' => generateToken(),
            'tokenReset' => $token
        ];
        $this->content = view('login/reset-pass', $data);
    }

    public function resetPassword() {
        $this->redirectIfLoggin();

        $tokenReset = $_POST['token'] ?? '';
        $this->redirectIfNotXMLHTTPRequest("/user/reset/password/$tokenReset");

        $tokens = $this->getPostSessionTokens();
        $pass = $_POST['pass'] ?? '';
        $cpass = $_POST['cpass'] ?? 'x';

        $pass = $this->decryptData($pass);
        $cpass = $this->decryptData($cpass);

        $verifier = LoginVerifier::getInstance($this->appConfig, $this->conn);
        $resResetPass = $verifier->verifyResetPass($tokenReset, $pass, $cpass, $tokens);
        $user = new User($this->conn, $this->appConfig);
        if ($resResetPass['success']) {
            $resUser = $user->updateUserPass($resResetPass['user']->id , $pass);
            if ($resUser['success']) {
                $user->removeTokenResetPassword($resResetPass['user']->id);
                $resResetPass['message'] = 'Password reset successfully';
            } else $resResetPass['message'] = $resUser['message'];

            $resResetPass['success'] = $resUser['success'];
        } else if ($resResetPass['deleteToken']) $user->removeTokenResetPassword($resResetPass['userId']);
        
        $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''); 
        switch ($header) {
            case 'XMLHTTPREQUEST':
                $resJSON = [
                    'success' => $resResetPass['success'],
                    'error' => $resResetPass['error'] ?? NULL,
                    'message' => $resResetPass['message'] ?? NULL
                ];
                if (!$resResetPass['success']) $resJSON['ntk'] = generateToken();
                header("Content-Type: application/json");
                header("X-Content-Type-Options: nosniff");
                echo json_encode($resJSON);
                exit;
            default:
                if (isset($resResetPass['message'])) {
                    $_SESSION['message'] = $resResetPass['message'];
                    $_SESSION['success'] = $resResetPass['success'];
                }
                redirect('/auth/login');
                break;
        }
    }

    public function showLogin() {
        $this->redirectIfLoggin();

        $this->isLogin = TRUE;
        $this->title .= ' - Login';
        $this->keywords .= ', login, signin';
        $this->description = 'PHP FRAMEWORK UMS - Login page';

        array_push($this->jsSrcs,
            ['src' => '/js/crypt/jsbn.js'],
            ['src' => '/js/crypt/prng4.js'],
            ['src' => '/js/crypt/rng.js'],
            ['src' => '/js/crypt/rsa.js'],
            ['src' => '/js/utils/req-key.js'],
            ['src' => '/js/utils/login/login.js']
        );

        $this->content = view('login/login', ['token' => generateToken()]);
    }

    public function login() {
        $this->redirectIfLoggin();
        $this->redirectIfNotXMLHTTPRequest('/auth/login');

        $tokens = $this->getPostSessionTokens();
        $username = $_POST['user'] ?? '';
        $pass = $_POST['pass'] ?? '';

        $pass = $this->decryptData($pass);

        $verifier = LoginVerifier::getInstance($this->appConfig, $this->conn);
        $resLogin = $verifier->verifyLogin($username, $pass, $tokens);
        if($resLogin['success']) {
            $user = new User($this->conn, $this->appConfig);
            $user->resetWrongPasswordLock($resLogin['user']->id);
            $this->createSessionLogin($resLogin['user']);
        } else if ($resLogin['wrongPass']) $this->manageWrongPassword($resLogin['userId']);

        $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
        switch ($header) {
            case 'XMLHTTPREQUEST':
                $resJSON = [
                    'success' => $resLogin['success'],
                    'error' => $resLogin['error'] ?? NULL,
                    'message' => $resLogin['message'] ?? NULL
                ];
                if (!$resLogin['success']) $resJSON['ntk'] = generateToken();
                header("Content-Type: application/json");
                header("X-Content-Type-Options: nosniff");
                echo json_encode($resJSON);
                exit;
            default:
                if (isset($resLogin['message'])) {
                    $_SESSION['message'] = $resLogin['message'];
                    $_SESSION['success'] = $resLogin['success'];
                }
                $resLogin['success'] ? redirect() : redirect('/auth/login');
                break;
        }
    }

    public function showSignup() {
        $this->redirectIfLoggin();

        $this->isSignup = TRUE;
        $this->title .= ' - Signup';
        $this->keywords .= 'signup, registration, logon';
        $this->description = 'PHP FRAMEWORK UMS - Signup page';

        array_push($this->jsSrcs,
            ['src' => '/js/crypt/jsbn.js'],
            ['src' => '/js/crypt/prng4.js'],
            ['src' => '/js/crypt/rng.js'],
            ['src' => '/js/crypt/rsa.js'],
            ['src' => '/js/utils/req-key.js'],
            ['src' => '/js/utils/validate.js'],
            ['src' => '/js/utils/login/signup.js']
        );

        $this->content = view('login/signup', ['token' => generateToken()]);
    }

    public function signup() {
        $this->redirectIfLoggin();
        $this->redirectIfNotXMLHTTPRequest('/auth/signup');

        $tokens = $this->getPostSessionTokens();
        $email = $_POST['email'] ?? '';
        $username = $_POST['username'] ?? '';
        $name = $_POST['name'] ?? '';
        $pass = $_POST['pass'] ?? '';
        $cpass = $_POST['cpass'] ?? 'x';

        $pass = $this->decryptData($pass);
        $cpass = $this->decryptData($cpass);

        $verifier = Verifier::getInstance($this->appConfig, $this->conn);
        $resSignup = $verifier->verifySignup($name, $email, $username, $pass, $cpass, $tokens);
        if($resSignup['success']) {
            $user = new User($this->conn, $this->appConfig);

            if (isset($resSignup['deleteUser'])) foreach ($resSignup['deleteUser'] as $userId) $user->deleteUser($userId);

            $requireConfirmEmail = $this->appConfig['app']['requireConfirmEmail'];
            $enabled = !$requireConfirmEmail;
            $data = compact('email', 'username', 'name', 'pass', 'enabled');
            $resUser = $user->saveUser($data, $requireConfirmEmail);
            if ($resUser['success']) {
                $this->resetSession();
                $newUser = $user->getUser($resUser['id']);
                if ($requireConfirmEmail) {
                    $this->sendEmailValidation($email, $newUser->token_account_enabler);
                    $this->resetSession();
                    $_SESSION['signup'] = TRUE;
                    $_SESSION['userId'] = $resUser['id'];
                } else $this->createSessionLogin($newUser);
                $resSignup['message'] = 'New user signup successfully';
            } else $resSignup['message'] = $resUser['message'];
            $resSignup['success'] = $resUser['success'];
        }
        
        $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
        switch ($header) {
            case 'XMLHTTPREQUEST':
                $resJSON = [
                    'success' => $resSignup['success'],
                    'error' => $resSignup['error'] ?? NULL,
                    'message' => $resSignup['message'] ?? NULL
                ];
                if (!$resSignup['success']) $resJSON['ntk'] = generateToken();
                header("Content-Type: application/json");
                header("X-Content-Type-Options: nosniff");
                echo json_encode($resJSON);
                exit;
            default:
                if (isset($resSignup['message'])) {
                    $_SESSION['message'] = $resSignup['message'];
                    $_SESSION['success'] = $resSignup['success'];
                }
                $resSignup['success'] ? redirect('/auth/signup/confirm') : redirect('/auth/signup');
                break;
        }
    }

    public function showSignupConfirm() {
        $this->redirectIfNotEmailConfirmRequire();
        $this->redirectIfNotSignup();

        $userId = $_SESSION['userId'] ?? '';
        $user = new User($this->conn, $this->appConfig);
        if (!(is_numeric($userId) && $user->getUser($userId))) {
            $this->showMessage('ERROR');
            return;
        }

        $this->title .= ' - Signup Confirm';
        array_push($this->jsSrcs,
            ['src' => '/js/utils/login/signup-confirm.js']
        );

        $this->content = view('login/signup-confirm', ['token' => generateToken('csrfResendEmail')]);
    }

    public function signupResendEmail() {
        $this->redirectIfNotEmailConfirmRequire();
        $this->redirectIfNotSignup();

        $tokens = $this->getPostSessionTokens('_xf', 'csrfResendEmail');
        $userId = $_SESSION['userId'];

        $verifier = LoginVerifier::getInstance($this->appConfig, $this->conn);
        $resResendEmail = $verifier->verifySignupResendEmail($userId, $tokens);
        if ($resResendEmail['success']) {
            $this->sendEmailValidation($resResendEmail['email'], $resResendEmail['token']);
            $resResendEmail['message'] = 'Email successfully sended';
        }

        $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
        switch ($header) {
            case 'XMLHTTPREQUEST':
                $resJSON = [
                    'success' => $resResendEmail['success'],
                    'message' => $resResendEmail['message'] ?? NULL,
                    'error' => $resResendEmail['error'] ?? NULL
                ];
                $resJSON['ntk'] = generateToken('csrfResendEmail');
                header("Content-Type: application/json");
                header("X-Content-Type-Options: nosniff");
                echo json_encode($resJSON);
                exit;
            default:
                if (isset($resResendEmail['message'])) {
                    $_SESSION['message'] = $resResendEmail['message'];
                    $_SESSION['success'] = $resResendEmail['success'];
                }
                redirect('/auth/signup/confirm');
                break;
        }
    }

    private function redirectIfNotSignup() {
        if (!(($_SESSION['signup'] ?? FALSE) && ($_SESSION['userId'] ?? FALSE))) redirect();
    }

    private function isSetTokenResetPass(string $token): bool {
        $user = new User($this->conn, $this->appConfig);
        return (bool) $user->getUserByTokenResetPassword($token);
    }
}

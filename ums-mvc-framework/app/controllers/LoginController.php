<?php
namespace app\controllers;

use app\models\User;

use \PDO;
use app\controllers\verifiers\Verifier;
use app\controllers\verifiers\LoginVerifier;

/**
 * Class controller to mange login, signup and logout rquest
 * @author Andrea Serra (DevAS) https://devas.info
 */
class LoginController extends Controller {
    public function __construct(PDO $conn, array $appConfig, string $layout = 'default') {
        parent::__construct($conn, $appConfig, $layout);
    }

    /* ##################################### */
    /* PUBLIC FUNCTION */
    /* ##################################### */

    /* function handler for logout request */
    public function logout() {
        /* redirect */
        $this->redirectIfNotLoggin();

        /* get tokens and user id */
        $tokens = $this->getPostSessionTokens('XS_TKN_OUT', 'csrfLogout');
        $id = getUserLoggedID();

        /* get verifier instance, and check the logout request */
        $verifier = LoginVerifier::getInstance($this->appConfig, $this->conn);
        $resLogout = $verifier->verifyLogout($id, $tokens);
        if ($resLogout['success']) {
            $this->resetSession();
            $resLogout['message'] = 'Succesfully logout'; 
        }

        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data['message'])) {
                $_SESSION['message'] = $data['message'];
                $_SESSION['success'] = $data['success'];
            }
            redirect();
        };

        /* result data */
        $dataOut = [
            'success' => $resLogout['success'],
            'message' => $resLogout['message'] ?? NULL
        ];

        $this->switchResponse($dataOut, !$resLogout['success'], $funcDefault, 'csrfLogout');
    }

    /* function to validate a new email */
    public function validateNewEmail(string $token) {
        /* redirect */
        $this->redirectIfNotEmailConfirmRequire();

        /* get verifier instance, and check the validate a new email request */
        $verifier = LoginVerifier::getInstance($this->appConfig, $this->conn);
        $resValidate = $verifier->verifyValidateNewEmail($token);
        if (!$resValidate['success']) {
            /* if verifier fails, show page not found and return */
            $this->showPageNotFound();
            return;
        }

        /* init user model and confirm a new email */
        $user = new User($this->conn, $this->appConfig);
        if (isset($resValidate['deleteUser'])) $user->deleteUser($resValidate['deleteUser']);
        $res = $user->confirmEmail($resValidate['userId']);

        /* set session message */
        if (isset($res['message'])) {
            $_SESSION['message'] = $res['message'];
            $_SESSION['success'] = $res['success'];
        }
        /* if fail redirect on signup page */
        if (!$res['success']) redirect('/auth/signup');

        /* remove token to confirm new email */
        $user->removeTokenConfirmEmail($resValidate['userId']);

        /* if user is not loggin redirect on login page */
        if (!isUserLoggedin()) redirect('/auth/login');

        /* if loggin user is the same that require confirm a new email,
         * then regenerate a loggins session
         */
        if ($resValidate['userId'] === getUserLoggedID()) {
            $usr = $user->getUser($resValidate['userId']);
            $this->createSessionLogin($usr);
        }

        /* redirect to home */
        redirect();
    }

    /* function to enable new account */
    public function enableAccount(string $token) {
        /* redirect */
        $this->redirectIfNotEmailConfirmRequire();

        /* get verifier instance, and check the enable account request */
        $verifier = LoginVerifier::getInstance($this->appConfig, $this->conn);
        $resEnable = $verifier->verifyEnableAccount($token);
        if (!$resEnable['success']) {
            /* if verifier fails, show page not found and return */
            $this->showPageNotFound();
            return;
        }

        /* init user model and confirm a enable user reuqest */
        $user = new User($this->conn, $this->appConfig);
        $res = $user->enableUser($resEnable['user']->id);

        /* set session message */
        if (isset($res['message'])){
            $_SESSION['message'] = $res['message'];
            $_SESSION['success'] = $res['success'];
        }

        /* if enable user fails, redirect on signup page */
        if (!$res['success']) redirect('/auth/signup');

        /* remove account enabler token and redirect on login page */ 
        $user->removeTokenEnabler($resEnable['user']->id);
        redirect('/auth/login');
    }

    /* function to view reset password request page */
    public function showResetPasswordRequest() {
        /* redirect */
        $this->redirectIfLoggin();

        /* set page title */
        $this->title .= ' - Forgot Password';

        /* add javascript sources */
        array_push($this->jsSrcs,
            ['src' => '/js/utils/validate.js'],
            ['src' => '/js/utils/login/res-pass-req.js']
        );

        $this->content = view('login/reset-pass-req', ['token' => generateToken('csrfResPassReq')]);
    }

    /* function to mangae reset password request */ 
    public function resetPasswordRequest() {
        /* redirect */
        $this->redirectIfLoggin();

        /* get tokens and email */
        $tokens = $this->getPostSessionTokens('XS_TKN', 'csrfResPassReq');
        $email = $_POST['email'] ?? '';

        /* get verifier instance, and check the reset password request */
        $verifier = LoginVerifier::getInstance($this->appConfig, $this->conn);
        $resResetPassReq = $verifier->verifyResetPassReq($email, $tokens);
        if ($resResetPassReq['success']) {
            /* init user model and create a reset password token */
            $user = new User($this->conn, $this->appConfig);
            $resUser = $user->createTokenResetPassword($resResetPassReq['user']->id);
            /* if success */
            if ($resUser['success']) {
                /* get email to send reset password link */
                $usr = $user->getUserByEmail($email);
                $resUser['success'] = $this->sendEmailResetPassword($email, $usr->token_reset_pass);
                $resUser['message'] = $resUser['success'] ? 'Email for password reset succesfully sended' : 'Send email failed';
            } 

            /* set success and messsage */
            $resResetPassReq['success'] = $resUser['success'];
            $resResetPassReq['message'] = $resUser['message'];
        }

        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data['message'])) {
                $_SESSION['message'] = $data['message'];
                $_SESSION['success'] = $data['success'];
            }
            redirect('/auth/login');
        };

        /* result data */
        $dataOut = [
            'success' => $resResetPassReq['success'],
            'error' => $resResetPassReq['error'] ?? NULL,
            'message' => $resResetPassReq['message'] ?? NULL
        ];

        $this->switchResponse($dataOut, !$resResetPassReq['success'], $funcDefault, 'csrfResPassReq');
    }

    /* function to view reset password page */
    public function showResetPassword(string $token) {
        /* redirect */
        $this->redirectIfLoggin();

        /* show page not found if is not valid token */
        if (!$this->isSetTokenResetPass($token)) {
            $this->showPageNotFound();
            return;
        }

        /* set page title */
        $this->title .= ' - Reset Password';

        /* add javascript sources */
        array_push($this->jsSrcs,
            ['src' => '/js/crypt/jsbn.js'],
            ['src' => '/js/crypt/prng4.js'],
            ['src' => '/js/crypt/rng.js'],
            ['src' => '/js/crypt/rsa.js'],
            ['src' => '/js/utils/req-key.js'],
            ['src' => '/js/utils/validate.js'],
            ['src' => '/js/utils/login/reset-pass.js']
        );

        /* result data */
        $data = [
            'token' => generateToken(),
            'tokenReset' => $token
        ];
        $this->content = view('login/reset-pass', $data);
    }

    /* function to reset a password */
    public function resetPassword() {
        /* redirects */
        $this->redirectIfLoggin();
        $tokenReset = $_POST['token'] ?? '';
        $this->redirectIfNotXMLHTTPRequest("/user/reset/password/$tokenReset");

        /* get tokens and post data */
        $tokens = $this->getPostSessionTokens();
        $pass = $_POST['pass'] ?? '';
        $cpass = $_POST['cpass'] ?? 'x';

        /* decrypt passwords */
        $pass = $this->decryptData($pass);
        $cpass = $this->decryptData($cpass);

        /* get verifier instance, and check the reset password request */
        $verifier = LoginVerifier::getInstance($this->appConfig, $this->conn);
        $resResetPass = $verifier->verifyResetPass($tokenReset, $pass, $cpass, $tokens);
        /* init user model */
        $user = new User($this->conn, $this->appConfig);
        /* if verifier success */
        if ($resResetPass['success']) {
            /* update password */
            $resUser = $user->updateUserPass($resResetPass['user']->id , $pass);
            /* if success remove token */
            if ($resUser['success']) {
                $user->removeTokenResetPassword($resResetPass['user']->id);
                $resResetPass['message'] = 'Password reset successfully';
            /* else show error message */
            } else $resResetPass['message'] = $resUser['message'];

            /* set succcess */
            $resResetPass['success'] = $resUser['success'];
        /* else remove reset password token */
        } else if ($resResetPass['deleteToken']) $user->removeTokenResetPassword($resResetPass['userId']);

        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data['message'])) {
                $_SESSION['message'] = $data['message'];
                $_SESSION['success'] = $data['success'];
            }
            redirect('/auth/login');
        };

        /* result data */
        $dataOut = [
            'success' => $resResetPass['success'],
            'error' => $resResetPass['error'] ?? NULL,
            'message' => $resResetPass['message'] ?? NULL
        ];
        $this->switchResponse($dataOut, !$resResetPass['success'], $funcDefault);
    }

    /* function to view login page */
    public function showLogin() {
        /* redirect */
        $this->redirectIfLoggin();

        /* set location, page title, keywords and description */
        $this->isLogin = TRUE;
        $this->title .= ' - Login';
        $this->keywords .= ', login, signin';
        $this->description = 'PHP FRAMEWORK UMS - Login page';

        /* add javascript sources */
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

    /* function to login */
    public function login() {
        /* redirects */
        $this->redirectIfLoggin();
        $this->redirectIfNotXMLHTTPRequest('/auth/login');

        /* get tokens and post data */
        $tokens = $this->getPostSessionTokens();
        $username = $_POST['user'] ?? '';
        $pass = $_POST['pass'] ?? '';

        /* decrypt password */
        $pass = $this->decryptData($pass);

        /* get verifier instance, and check the login request */
        $verifier = LoginVerifier::getInstance($this->appConfig, $this->conn);
        $resLogin = $verifier->verifyLogin($username, $pass, $tokens);
        /* if success */
        if($resLogin['success']) {
            /* init user model and create a login session */
            $user = new User($this->conn, $this->appConfig);
            $user->resetWrongPasswordLock($resLogin['user']->id);
            $this->createSessionLogin($resLogin['user']);
        /* else if is wrong password, increments it */
        } else if ($resLogin['wrongPass']) $this->handlerWrongPassword($resLogin['userId']);

        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data['message'])) {
                $_SESSION['message'] = $data['message'];
                $_SESSION['success'] = $data['success'];
            }
            $data['success'] ? redirect() : redirect('/auth/login');
        };

        /* result data */
        $dataOut = [
            'success' => $resLogin['success'],
            'error' => $resLogin['error'] ?? NULL,
            'message' => $resLogin['message'] ?? NULL
        ];

        $this->switchResponse($dataOut, !$resLogin['success'], $funcDefault);
    }

    /* function to view signup page */
    public function showSignup() {
        /* redirect */
        $this->redirectIfLoggin();

        /* set location, page title, keywords and description */
        $this->isSignup = TRUE;
        $this->title .= ' - Signup';
        $this->keywords .= 'signup, registration, logon';
        $this->description = 'PHP FRAMEWORK UMS - Signup page';

        /* add javascript sources */
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

    /* function to signup */
    public function signup() {
        /* redirects */
        $this->redirectIfLoggin();
        $this->redirectIfNotXMLHTTPRequest('/auth/signup');

        /* get tokens and post data */
        $tokens = $this->getPostSessionTokens();
        $email = $_POST['email'] ?? '';
        $username = $_POST['username'] ?? '';
        $name = $_POST['name'] ?? '';
        $pass = $_POST['pass'] ?? '';
        $cpass = $_POST['cpass'] ?? 'x';

        /* decrypt passwords */
        $pass = $this->decryptData($pass);
        $cpass = $this->decryptData($cpass);

        /* get verifier instance, and check the signup request */
        $verifier = Verifier::getInstance($this->appConfig, $this->conn);
        $resSignup = $verifier->verifySignup($name, $email, $username, $pass, $cpass, $tokens);
        /* if succcess */
        if($resSignup['success']) {
            /* init user model */
            $user = new User($this->conn, $this->appConfig);

//             /* check if need to delete user, and delete it */
//             if (isset($resSignup['deleteUser'])) foreach ($resSignup['deleteUser'] as $userId) $user->deleteUser($userId);

            /* check if is require confirm email, and set account enable */
            $requireConfirmEmail = $this->appConfig['app']['requireConfirmEmail'];
            $enabled = !$requireConfirmEmail;

            /* compact the data of new user, and save it */
            $data = compact('email', 'username', 'name', 'pass', 'enabled');
            $resUser = $user->saveUser($data, $requireConfirmEmail);

            /* if success */
            if ($resUser['success']) {
                /* reset session and get new user id */
                $this->resetSession();
                $newUser = $user->getUser($resUser['id']);
                /* if require a email confirm */
                if ($requireConfirmEmail) {
                    /* send email and create a signup session */
                    $this->sendEmailValidation($email, $newUser->token_account_enabler);
                    $this->resetSession();
                    $_SESSION['signup'] = TRUE;
                    $_SESSION['userId'] = $resUser['id'];
                /* else create login session */
                } else $this->createSessionLogin($newUser);
                /* set success messagge */
                $resSignup['message'] = 'New user signup successfully';
            /* else set error message */
            } else $resSignup['message'] = $resUser['message'];

            /* set success */
            $resSignup['success'] = $resUser['success'];
        }

        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data['message'])) {
                $_SESSION['message'] = $data['message'];
                $_SESSION['success'] = $data['success'];
            }
            $data['success'] ? redirect('/auth/signup/confirm') : redirect('/auth/signup');
        };

        /* result data */
        $dataOut = [
            'success' => $resSignup['success'],
            'error' => $resSignup['error'] ?? NULL,
            'message' => $resSignup['message'] ?? NULL
        ];

        $this->switchResponse($dataOut, !$resSignup['success'], $funcDefault);
    }

    /* function to view signup confirm page */
    public function showSignupConfirm() {
        /* redirects */
        $this->redirectIfNotEmailConfirmRequire();
        $this->redirectIfNotSignup();

        /* get user id of signup session */
        $userId = $_SESSION['userId'] ?? '';

        /* init user model */
        $user = new User($this->conn, $this->appConfig);
        /* if is not valid user id, show error message and return */
        if (!(is_numeric($userId) && $user->getUser($userId))) {
            $this->showMessage('ERROR');
            return;
        }

        /* set page title */
        $this->title .= ' - Signup Confirm';

        /* add javascript sources */
        array_push($this->jsSrcs,
            ['src' => '/js/utils/login/signup-confirm.js']
        );

        $this->content = view('login/signup-confirm', ['token' => generateToken('csrfResendEmail')]);
    }

    /* function to resend a signup email */
    public function signupResendEmail() {
        /* redirects */
        $this->redirectIfNotEmailConfirmRequire();
        $this->redirectIfNotSignup();

        /* get tokens and user id */
        $tokens = $this->getPostSessionTokens('XS_TKN', 'csrfResendEmail');
        $userId = $_SESSION['userId'];

        /* get verifier instance, and check the resend validator email request */
        $verifier = LoginVerifier::getInstance($this->appConfig, $this->conn);
        $resResendEmail = $verifier->verifySignupResendEmail($userId, $tokens);
        if ($resResendEmail['success']) {
            $this->sendEmailValidation($resResendEmail['email'], $resResendEmail['token']);
            $resResendEmail['message'] = 'Email successfully sended';
        }

        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data['message'])) {
                $_SESSION['message'] = $data['message'];
                $_SESSION['success'] = $data['success'];
            }
            redirect('/auth/signup/confirm');
        };

        /* result data */
        $dataOut = [
            'success' => $resResendEmail['success'],
            'message' => $resResendEmail['message'] ?? NULL
        ];

        $this->switchResponse($dataOut, TRUE, $funcDefault, 'csrfResendEmail');
    }

    /* ##################################### */
    /* PRIVATE FUNCTION */
    /* ##################################### */
    

    /* function to redirect if client is not on signup session */
    private function redirectIfNotSignup() {
        if (!(($_SESSION['signup'] ?? FALSE) && ($_SESSION['userId'] ?? FALSE))) redirect();
    }

    /* function to check if is a valid password reset token */
    private function isSetTokenResetPass(string $token): bool {
        $user = new User($this->conn, $this->appConfig);
        return (bool) $user->getUserByTokenResetPassword($token);
    }
}

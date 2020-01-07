<?php
namespace app\controllers;

use app\models\User;
use app\controllers\verifiers\Verifier;
use app\controllers\verifiers\LoginVerifier;
use app\models\PendingEmail;
use app\models\PasswordResetRequest;
use app\models\PendingUser;
use \DateTime;
use \PDO;

/**
 * Class controller to mange login, signup and logout rquest
 * @author Andrea Serra (DevAS) https://devas.info
 */
class LoginController extends Controller {
    public function __construct(PDO $conn, array $appConfig, string $layout = DEFAULT_LAYOUT) {
        parent::__construct($conn, $appConfig, $layout);
    }

    /* ##################################### */
    /* PUBLIC FUNCTION */
    /* ##################################### */

    /* ############ LOGIN FUNCTIONS ############ */
    
    /* function to view login page */
    public function showLogin() {
        /* redirect */
        $this->redirectOrFailIfLogin();
        
        /* set location, page title, keywords and description */
        $this->isLogin = TRUE;
        $this->title .= ' - Login';
        $this->keywords .= ', login, signin, register, registration';
        $this->description = 'UMS - PHP FRAMEWORK - Login page - Signin in this site';
        
        /* add javascript sources */
        array_push($this->jsSrcs,
            [SOURCE => '/js/crypt/jsbn.js'],
            [SOURCE => '/js/crypt/prng4.js'],
            [SOURCE => '/js/crypt/rng.js'],
            [SOURCE => '/js/crypt/rsa.js'],
            [SOURCE => '/js/utils/req-key.js'],
            [SOURCE => '/js/utils/login/login.js']
        );

        /* show login page */
        $this->content = view('login/login', [TOKEN => generateToken(CSRF_LOGIN)]);
    }
    
    /* function to login */
    public function login() {
        /* redirects */
        $this->redirectOrFailIfLogin();
        $this->redirectIfNotXMLHTTPRequest('/'.LOGIN_ROUTE);
        
        /* get tokens and post data */
        $tokens = $this->getPostSessionTokens(CSRF_LOGIN);
        $username = $_POST[USER] ?? '';
        $pass = $_POST[PASSWORD] ?? '';
        
        /* decrypt password */
        $pass = $this->decryptData($pass);
        
        /* get verifier instance, and check the login request */
        $verifier = LoginVerifier::getInstance($this->appConfig, $this->conn);
        $resLogin = $verifier->verifyLogin($username, $pass, $tokens);
        /* if success */
        if($resLogin[SUCCESS]) {
            /* init user model and create a login session */
            $user = new User($this->conn);
            $user->resetLockCounts($resLogin[USER]->{USER_ID});
            $this->createLoginSession($resLogin[USER]);
        /* else if is wrong password, increments it */
        } else if ($resLogin[WRONG_PASSWORD]) $this->handlerWrongPassword($resLogin[USER_ID]);
        
        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data[MESSAGE])) {
                $_SESSION[MESSAGE] = $data[MESSAGE];
                $_SESSION[SUCCESS] = $data[SUCCESS];
            }
            $data[SUCCESS] ? redirect() : redirect('/'.LOGIN_ROUTE);
        };
        
        /* result data */
        $dataOut = [
            SUCCESS => $resLogin[SUCCESS],
            ERROR => $resLogin[ERROR] ?? NULL,
            MESSAGE => $resLogin[MESSAGE] ?? NULL
        ];
        
        $this->switchResponse($dataOut, (!$resLogin[SUCCESS] && $resLogin[GENERATE_TOKEN]), $funcDefault, CSRF_LOGIN);
    }
    
    /* ############ SIGNUP FUNCTIONS ############ */

    /* function to view signup page */
    public function showSignup() {
        /* redirect */
        $this->redirectOrFailIfLogin();
        
        /* set location, page title, keywords and description */
        $this->isSignup = TRUE;
        $this->title .= ' - Signup';
        $this->keywords .= 'signup, registration, logon';
        $this->description = 'PHP FRAMEWORK UMS - Signup page';
        
        /* add javascript sources */
        array_push($this->jsSrcs,
            [SOURCE => '/js/crypt/jsbn.js'],
            [SOURCE => '/js/crypt/prng4.js'],
            [SOURCE => '/js/crypt/rng.js'],
            [SOURCE => '/js/crypt/rsa.js'],
            [SOURCE => '/js/utils/req-key.js'],
            [SOURCE => '/js/utils/validate.js'],
            [SOURCE => '/js/utils/login/signup.js']
        );
        
        $this->content = view('login/signup', [TOKEN => generateToken(CSRF_SIGNUP)]);
    }
    
    /* function to signup */
    public function signup() {
        /* redirects */
        $this->redirectOrFailIfLogin();
        $this->redirectIfNotXMLHTTPRequest('/'.SIGNUP_ROUTE);
        
        /* get tokens and post data */
        $tokens = $this->getPostSessionTokens(CSRF_SIGNUP);
        $email = $_POST[EMAIL] ?? '';
        $username = $_POST[USERNAME] ?? '';
        $name = $_POST[NAME] ?? '';
        $pass = $_POST[PASSWORD] ?? '';
        /* send fail if empty pass */
        if (empty($pass)) $this->switchFailResponse('Insert a password', '/'.SIGNUP_ROUTE);
        $cpass = $_POST[CONFIRM_PASS] ?? '';
        
        /* decrypt passwords */
        $pass = $this->decryptData($pass);
        $cpass = $this->decryptData($cpass);
        
        /* get verifier instance, and check the signup request */
        $verifier = Verifier::getInstance($this->appConfig, $this->conn);
        $resSignup = $verifier->verifySignup($name, $email, $username, $pass, $cpass, $tokens);
        /* if succcess */
        if($resSignup[SUCCESS]) {
            $userData = [
                NAME => $name,
                USERNAME => $username,
                EMAIL => $email,
                PASSWORD => $pass,
                ROLE_ID => $this->appConfig[UMS][DEFAULT_USER_ROLE]
            ];
            /* if email confirm is require */
            if ($this->appConfig[UMS][REQUIRE_CONFIRM_EMAIL]) {
                /* save user on pending table */
                $pendUser = new PendingUser($this->conn);
                $res = $pendUser->savePendingUser($userData);
                /* if success create signup session */
                if ($res[SUCCESS]) {
                    $this->resetSession();
                    $this->sendEnablerEmail($email, $res[TOKEN]);
                    $_SESSION[SIGNUP] = TRUE;
                    $_SESSION[USER_ID] = $res[USER_ID];
                }
            } else {
                /* save user */
                $user = new User($this->conn);
                $res = $user->saveUser($userData);
                /* if success create login session */
                if ($res[SUCCESS]) $this->createLoginSession($res[USER_ID]);
            }
            /* set result */
            $resSignup[MESSAGE] = $res[MESSAGE];
            $resSignup[SUCCESS] = $res[SUCCESS];
        }
        
        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data[MESSAGE])) {
                $_SESSION[MESSAGE] = $data[MESSAGE];
                $_SESSION[SUCCESS] = $data[SUCCESS];
            }
            $data[SUCCESS] ? redirect('/'.CONFIRM_SIGNUP_ROUTE) : redirect('/'.SIGNUP_ROUTE);
        };
        
        /* result data */
        $dataOut = [
            SUCCESS => $resSignup[SUCCESS],
            ERROR => $resSignup[ERROR] ?? NULL,
            MESSAGE => $resSignup[MESSAGE] ?? NULL
        ];

        $this->switchResponse($dataOut, (!$resSignup[SUCCESS] && $resSignup[GENERATE_TOKEN]), $funcDefault, CSRF_SIGNUP);
    }

    /* function to view signup confirm page */
    public function showSignupConfirm() {
        /* redirects */
        $this->redirectOrFailIfConfirmEmailNotRequire();
        $this->redirectIfNotSignupSession();

        /* get user id of signup session */
        $userId = $_SESSION[USER_ID] ?? '';

        /* init pending user model */
        $pendUser = new PendingUser($this->conn);
        /* if is not valid user id, show error message and return */
        if (!(is_numeric($userId) && $pendUser->getPendingUser($userId))) {
//             $this->title .= ' - ERROR';
            $this->showMessageAndExit('ERROR', TRUE);
            return;
        }

        /* set page title */
        $this->title .= ' - Signup Confirm';

        /* add javascript sources */
        array_push($this->jsSrcs,
            [SOURCE => '/js/utils/login/signup-confirm.js']
        );

        $this->content = view('login/signup-confirm', [TOKEN => generateToken(CSRF_RESEND_ENABLER_ACC)]);
    }

    /* function to resend a signup email */
    public function signupResendEmail() {
        /* redirects */
        $this->redirectOrFailIfConfirmEmailNotRequire();
        $this->redirectIfNotSignupSession();

        if (isset($_SESSION[LAST_RESEND_REQ])) {
            $nextReqTime = new DateTime($_SESSION[LAST_RESEND_REQ]);
            $nextReqTime->modify('5 minutes');
            if ($nextReqTime > new DateTime()) $this->switchFailResponse('Wait a few minutes before another request', '/'.CONFIRM_SIGNUP_ROUTE);
        }

        /* get tokens and user id */
        $tokens = $this->getPostSessionTokens(CSRF_RESEND_ENABLER_ACC);
        $userId = $_SESSION[USER_ID];

        /* get verifier instance, and check the resend validator email request */
        $verifier = LoginVerifier::getInstance($this->appConfig, $this->conn);
        $resResendEmail = $verifier->verifySignupResendEmail($userId, $tokens);
        if ($resResendEmail[SUCCESS]) {
            $this->sendEmailValidation($resResendEmail[EMAIL], $resResendEmail[TOKEN]);
            $resResendEmail[MESSAGE] = 'Email successfully sended';
            $datetime = new DateTime();
            $_SESSION[LAST_RESEND_REQ] = $datetime->format('Y-m-d H:i:s');
        }

        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data[MESSAGE])) {
                $_SESSION[MESSAGE] = $data[MESSAGE];
                $_SESSION[SUCCESS] = $data[SUCCESS];
            }
            redirect('/auth/signup/confirm');
        };

        /* result data */
        $dataOut = [
            SUCCESS => $resResendEmail[SUCCESS],
            MESSAGE => $resResendEmail[MESSAGE] ?? NULL
        ];

        $this->switchResponse($dataOut, $resResendEmail[GENERATE_TOKEN], $funcDefault, CSRF_RESEND_ENABLER_ACC);
    }

    /* ############ LOGOUT FUNCTIONS ############ */

    /* function handler for logout request */
    public function logout() {
        /* redirect */
        $this->redirectOrFailIfNotLogin();

        /* get tokens and user id */
        $tokens = $this->getPostSessionTokens(CSRF_LOGOUT);
        $id = getUserLoggedID();

        /* get verifier instance, and check the logout request */
        $verifier = LoginVerifier::getInstance($this->appConfig, $this->conn);
        $resLogout = $verifier->verifyLogout($id, $tokens);
        if ($resLogout[SUCCESS]) {
            $resLogout[SUCCESS] = $this->resetLoginSession();
            $resLogout[MESSAGE] = $resLogout[SUCCESS] ? 'Succesfully logout' : 'Logout failed'; 
        }

        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data[MESSAGE])) {
                $_SESSION[MESSAGE] = $data[MESSAGE];
                $_SESSION[SUCCESS] = $data[SUCCESS];
            }
            redirect();
        };

        /* result data */
        $dataOut = [
            SUCCESS => $resLogout[SUCCESS],
            MESSAGE => $resLogout[MESSAGE] ?? NULL
        ];

        $this->switchResponse($dataOut, (!$resLogout[SUCCESS] && $resLogout[GENERATE_TOKEN]), $funcDefault, CSRF_LOGOUT);
    }

    /* ############ NEW EMAIL FUNCTIONS ############ */

    /* function to validate a new email */
    public function enableNewEmail(string $token) {
        /* redirect */
        $this->redirectOrFailIfConfirmEmailNotRequire();

        /* get verifier instance, and check the validate a new email request */
        $verifier = LoginVerifier::getInstance($this->appConfig, $this->conn);
        $resValidate = $verifier->verifyEnableNewEmail($token);

        /* if verifier fails, show page not found and return */
        if (!$resValidate[SUCCESS]) {
            $this->showPageNotFound();
            return;
        }

        /* init user model and confirm a new email */
        $user = new User($this->conn);
        $res = $user->updateEmail($resValidate[USER]->{USER_ID}, $resValidate[USER]->{NEW_EMAIL});

        /* set session message */
        if (isset($res[MESSAGE])) {
            $_SESSION[MESSAGE] = $res[MESSAGE];
            $_SESSION[SUCCESS] = $res[SUCCESS];
        }

        /* if fail redirect on signup page */
        if (!$res[SUCCESS]) redirect('/auth/signup');

        /* else remove token to confirm new email */
        $pendEmail = new PendingEmail($this->conn);
        $pendEmail->removeAllEmailEnablerToken($resValidate[USER]->{USER_ID});

        /* if user is not login redirect on login page */
        if (!$this->loginSession) redirect('/auth/login');

        /* else redirect to home */
        redirect();
    }

    /* ############ PASSWORD FUNCTIONS ############ */

    /* function to view reset password request page */
    public function showPasswordResetRequest() {
        /* redirect */
        $this->redirectIfLoggin();

        /* set page title */
        $this->title .= ' - Forgot Password';
        $this->keywords .= ',password, forgot, reset, account, recovery';

        /* add javascript sources */
        array_push($this->jsSrcs,
            [SOURCE => '/js/utils/validate.js'],
            [SOURCE => '/js/utils/login/pass-reset-req.js']
        );

        /* generate token and show page */
        $this->content = view('login/pass-reset-req', [TOKEN => generateToken(CSRF_PASS_RESET_REQ)]);
    }

    /* function to mangae reset password request */ 
    public function passwordResetRequest() {
        /* redirect */
        $this->redirectIfLoggin();

        /* get tokens and email */
        $tokens = $this->getPostSessionTokens(CSRF_PASS_RESET_REQ);
        $email = $_POST[EMAIL] ?? '';

        /* get verifier instance, and check the reset password request */
        $verifier = LoginVerifier::getInstance($this->appConfig, $this->conn);
        $resPassResetReq = $verifier->verifyPassResetReq($email, $tokens);
        if ($resPassResetReq[SUCCESS]) {
            /* init password reset request model */
            $passResReq = new PasswordResetRequest($this->conn);
            /* remove all previus request */
            $passResReq->removePasswordResetReqForUser($resPassResetReq[USER]->{USER_ID});
            /* calc expire datae time add a new request */
            $expireDatetime = getExpireDatetime($this->appConfig[UMS][PASS_RESET_EXPIRE_TIME]);
            $res = $passResReq->newPasswordResetReq($resPassResetReq[USER]->{USER_ID}, $_SERVER['REMOTE_ADDR'], $expireDatetime);
            /* if success */
            if ($res[SUCCESS]) {
                /* send email */
                $res[SUCCESS] = $this->sendEmailResetPassword($email, $res[TOKEN]);
                $res[MESSAGE] = $res[SUCCESS] ? 'Email for password reset succesfully sended' : 'Send email failed';
            }

            /* set success and messsage */
            $resPassResetReq[SUCCESS] = $res[SUCCESS];
            $resPassResetReq[MESSAGE] = $res[MESSAGE];
        }

        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data[MESSAGE])) {
                $_SESSION[MESSAGE] = $data[MESSAGE];
                $_SESSION[SUCCESS] = $data[SUCCESS];
            }
            redirect('/auth/login');
        };

        /* result data */
        $dataOut = [
            SUCCESS => $resPassResetReq[SUCCESS],
            ERROE => $resPassResetReq[ERROR] ?? NULL,
            MESSAGE => $resPassResetReq[MESSAGE] ?? NULL
        ];

        $this->switchResponse($dataOut, (!$resPassResetReq[SUCCESS] && $resPassResetReq[GENERATE_TOKEN]), $funcDefault, CSRF_PASS_RESET_REQ);
    }

    /* function to view reset password page */
    public function showPasswordReset(string $token) {
        /* redirect */
        $this->redirectIfLoggin();

        /* show page not found if is not valid token */
        $passResReq = new PasswordResetRequest($this->conn);
        if (!$passResReq->getUserByResetPasswordToken($token)) {
            $this->showPageNotFound();
            return;
        }

        /* set page title */
        $this->title .= ' - Reset Password';

        /* add javascript sources */
        array_push($this->jsSrcs,
            [SOURCE => '/js/crypt/jsbn.js'],
            [SOURCE => '/js/crypt/prng4.js'],
            [SOURCE => '/js/crypt/rng.js'],
            [SOURCE => '/js/crypt/rsa.js'],
            [SOURCE => '/js/utils/req-key.js'],
            [SOURCE => '/js/utils/validate.js'],
            [SOURCE => '/js/utils/login/pass-reset.js']
        );

        /* data to be view */
        $data = [
            TOKEN => generateToken(CSRF_PASS_RESET),
            PASSWORD_RESET_TOKEN => $token
        ];
        $this->content = view('login/pass-reset', $data);
    }

    /* function to reset a password */
    public function passwordReset() {
        /* redirects */
        $this->redirectIfLoggin();
        $tokenReset = $_POST[PASSWORD_RESET_TOKEN] ?? '';
        $this->redirectIfNotXMLHTTPRequest('/'.PASS_RESET_ROUTE."/$tokenReset");

        /* get tokens and post data */
        $tokens = $this->getPostSessionTokens(CSRF_PASS_RESET);
        $pass = $_POST[PASSWORD] ?? '';
        if (empty($pass)) $this->switchFailResponse('Insert a password', '/'.PASS_RESET_ROUTE."/$tokenReset");
        $cpass = $_POST[CONFIRM_PASS] ?? '';

        /* decrypt passwords */
        $pass = $this->decryptData($pass);
        $cpass = $this->decryptData($cpass);

        /* get verifier instance, and check the reset password request */
        $verifier = LoginVerifier::getInstance($this->appConfig, $this->conn);
        $resResetPass = $verifier->verifyPassReset($tokenReset, $pass, $cpass, $tokens);

        /* init pass reset req model */
        $passResReq = new PasswordResetRequest($this->conn);
        /* if verifier success */
        if ($resResetPass[SUCCESS]) {
        /* init user model */
            $user = new User($this->conn);
            /* update password */
            $resUser = $user->updatePassword($resResetPass[USER_ID], $pass);
            /* if success remove token */
            if ($resUser[SUCCESS]) {
                $passResReq->removePasswordResetReqForUser($resResetPass[USER_ID]);
                $resResetPass[MESSAGE] = 'Password reset successfully';
            /* else show error message */
            } else $resResetPass[MESSAGE] = $resUser[MESSAGE];

            /* set succcess */
            $resResetPass[SUCCESS] = $resUser[SUCCESS];
        /* else remove reset password token */
        } else if ($resResetPass[REMOVE_TOKEN]) $passResReq->removePasswordResetReqForUser($resResetPass[USER_ID]);

        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data[MESSAGE])) {
                $_SESSION[MESSAGE] = $data[MESSAGE];
                $_SESSION[SUCCESS] = $data[SUCCESS];
            }
            redirect('/auth/login');
        };

        /* result data */
        $dataOut = [
            SUCCESS => $resResetPass[SUCCESS],
            ERROR => $resResetPass[ERROR] ?? NULL,
            MESSAGE => $resResetPass[MESSAGE] ?? NULL
        ];
        $this->switchResponse($dataOut, (!$resResetPass[SUCCESS] && $resResetPass[GENERATE_TOKEN]), $funcDefault, CSRF_PASS_RESET);
    }

    /* ############ ACCOUNT FUNCTIONS ############ */

    /* function to enable new account */
    public function enableAccount(string $token) {
        /* redirect */
        $this->redirectOrFailIfConfirmEmailNotRequire();
        
        /* get verifier instance, and check the enable account request */
        $verifier = LoginVerifier::getInstance($this->appConfig, $this->conn);
        $resEnable = $verifier->verifyEnableAccount($token);
        
        /* if verifier fails, show page not found and return */
        if (!$resEnable[SUCCESS]) {
            /* if link is expire redirect to signup and remove token*/
            if ($resEnable[REMOVE_TOKEN]) {
                $pendUser = new PendingUser($this->conn);
                $pendUser->removeAccountEnablerToken($token);
                $this->switchFailResponse($resEnable[MESSAGE], '/'.SIGNUP_ROUTE);
            /* else show page not found */
            } else $this->showPageNotFound();

            return;
        }

        /* set user data */
        $dataUsr = [
            NAME => $resEnable[USER]->{NAME},
            USERNAME => $resEnable[USER]->{USERNAME},
            EMAIL => $resEnable[USER]->{EMAIL},
            PASSWORD => $resEnable[USER]->{PASSWORD},
            ROLE => $resEnable[USER]->{ROLE_ID_FRGN},
            ENABLED => TRUE,
            REGISTRATION_DATETIME => $resEnable[USER]->{REGISTRATION_DATETIME}
        ];
        /* init user model and save user */
        $user = new User($this->conn);
        $res = $user->saveUser($dataUsr);

        /* set session message */
        if (isset($res[MESSAGE])){
            $_SESSION[MESSAGE] = $res[MESSAGE];
            $_SESSION[SUCCESS] = $res[SUCCESS];
        }

        /* if enable user fails, redirect on signup page */
        if (!$res[SUCCESS]) redirect('/auth/signup');
        
        /* remove account enabler token and redirect on login page */
        $pendUser = new PendingUser($this->conn);
        $pendUser->removeAccountEnablerToken($token);
        redirect('/'.LOGIN_ROUTE);
    }

    /* ##################################### */
    /* PRIVATE FUNCTION */
    /* ##################################### */
    

    /* function to redirect if client is not on signup session */
    private function redirectIfNotSignupSession() {
        if (!(($_SESSION[SIGNUP] ?? FALSE) && ($_SESSION[USER_ID] ?? FALSE))) redirect();
    }
}

<?php
namespace app\controllers;

use app\controllers\verifiers\LoginVerifier;
use app\controllers\verifiers\Verifier;
use app\models\PasswordResetRequest;
use app\models\PendingEmail;
use app\models\PendingUser;
use app\models\User;
use \DateTime;
use \PDO;

/**
 * Class controller to mange login, signup and logout request
 * @author Andrea Serra (DevAS) https://devas.info
 */
class LoginController extends Controller {
    public function __construct(PDO $conn, array $appConfig, string $layout=DEFAULT_LAYOUT) {
        parent::__construct($conn, $appConfig, $layout);
    }

    /* ##################################### */
    /* PUBLIC FUNCTION */
    /* ##################################### */

    /* ############ SHOW FUNCTIONS ############ */
    
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
        $this->content = view(getPath('login','login'), [
            TOKEN => generateToken(CSRF_LOGIN),
            GET_KEY_TOKEN => generateToken(CSRF_KEY_JSON)
        ]);
    }

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
        
        $this->content = view(getPath('login','signup'), [
            TOKEN => generateToken(CSRF_SIGNUP),
            GET_KEY_TOKEN => generateToken(CSRF_KEY_JSON)
        ]);
    }

    /* function to view signup confirm page */
    public function showSignupConfirm() {
        /* redirects */
        $this->redirectOrFailIfConfirmEmailNotRequire();
        $this->redirectOrFailIfLogin();
        $this->redirectIfNotSignupSession();
        
        /* get user id of signup session */
        $userId = $_SESSION[USER_ID] ?? '';
        
        /* init pending user model */
        $pendUser = new PendingUser($this->conn);
        /* if is not valid user id, show error message and return */
        if (!(is_numeric($userId) && $pendUser->getPendingUser($userId))) {
            //             $this->title .= ' - ERROR';
            $this->showPageNotFound();
            return;
        }
        
        /* set page title */
        $this->title .= ' - Signup Confirm';
        
        /* add javascript sources */
        array_push($this->jsSrcs,
            [SOURCE => '/js/utils/login/signup-confirm.js']
        );
        
        $this->content = view(getPath('login', 'signup-confirm'), [TOKEN => generateToken(CSRF_RESEND_ENABLER_ACC)]);
    }

    /* function to view reset password request page */
    public function showPasswordResetRequest() {
        /* redirect */
        $this->redirectOrFailIfLogin();
        
        /* set page title */
        $this->title .= ' - Forgot Password';
        $this->keywords .= ',password, forgot, reset, account, recovery';
        
        /* add javascript sources */
        array_push($this->jsSrcs,
            [SOURCE => '/js/utils/validate.js'],
            [SOURCE => '/js/utils/login/pass-reset-req.js']
        );
        
        /* generate token and show page */
        $this->content = view(getPath('login', 'pass-reset-req'), [TOKEN => generateToken(CSRF_PASS_RESET_REQ)]);
    }
    
    /* function to view reset password page */
    public function showPasswordReset(string $token) {
        /* redirect */
        $this->redirectOrFailIfLogin();
        
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
            GET_KEY_TOKEN => generateToken(CSRF_KEY_JSON),
            TOKEN => generateToken(CSRF_PASS_RESET),
            PASSWORD_RESET_TOKEN => $token
        ];
        $this->content = view('login/pass-reset', $data);
    }

    /* ############ ACTION FUNCTIONS ############ */

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
        $verifier = LoginVerifier::getInstance($this->conn);
        $resLogin = $verifier->verifyLogin($username, $pass, $tokens);

        /* set url to redirect */
        $redirectTo = '/'.LOGIN_ROUTE;
        /* if success */
        if($resLogin[SUCCESS]) {
            /* init user model and create a login session */
            $user = new User($this->conn);
            $user->resetLockCounts($resLogin[USER]->{USER_ID});
            $this->createLoginSession($resLogin[USER]->{USER_ID});
            $redirectTo = '/';
        /* else if is wrong password, increments it */
        } else if ($resLogin[WRONG_PASSWORD]) $this->handlerWrongPassword($resLogin[USER_ID]);
        
        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data[MESSAGE])) {
                $_SESSION[MESSAGE] = $data[MESSAGE];
                $_SESSION[SUCCESS] = $data[SUCCESS];
            }
            redirect($data[REDIRECT_TO]);
        };
        
        /* result data */
        $dataOut = [
            REDIRECT_TO => $redirectTo,
            SUCCESS => $resLogin[SUCCESS],
            ERROR => $resLogin[ERROR] ?? NULL,
            MESSAGE => $resLogin[MESSAGE] ?? NULL
        ];
        
        $this->switchResponse($dataOut, (!$resLogin[SUCCESS] && $resLogin[GENERATE_TOKEN]), $funcDefault, CSRF_LOGIN);
    }

    /* function to signup */
    public function signup() {
        /* redirects */
        $this->redirectOrFailIfLogin();
        $this->redirectIfNotXMLHTTPRequest('/'.SIGNUP_ROUTE);
        
        /* get tokens and post data */
        $tokens = $this->getPostSessionTokens(CSRF_SIGNUP);
        $name = $_POST[NAME] ?? '';
        $username = $_POST[USERNAME] ?? '';
        $email = $_POST[EMAIL] ?? '';
        $pass = $_POST[PASSWORD] ?? '';
        $cpass = $_POST[CONFIRM_PASS] ?? '';
        /* decrypt passwords */
        $pass = $this->decryptData($pass);
        $cpass = $this->decryptData($cpass);
        
        /* get verifier instance, and check the signup request */
        $verifier = Verifier::getInstance($this->conn);
        $resSignup = $verifier->verifySignup($name, $email, $username, $pass, $cpass, $tokens);

        /* set url to redirect */
        $redirectTo = '/'.SIGNUP_ROUTE;
        /* if succcess */
        if($resSignup[SUCCESS]) {
            $userData = [
                NAME => $name,
                USERNAME => $username,
                EMAIL => $email,
                PASSWORD => $pass,
                ROLE_ID_FRGN => DEFAULT_ROLE,
                EXPIRE_DATETIME => getExpireDatetime(ENABLER_LINK_EXPIRE_TIME)
            ];
            
            /* if email confirm is require */
            if ($this->appConfig[UMS][REQUIRE_CONFIRM_EMAIL]) {
                /* save user on pending table */
                $pendUser = new PendingUser($this->conn);
                $resUser = $pendUser->savePendingUser($userData);
                /* if success create signup session */
                if ($resUser[SUCCESS]) {
                    $this->resetSession();
                    $this->sendEnablerEmail($email, $resUser[TOKEN]);
                    $_SESSION[SIGNUP] = TRUE;
                    $_SESSION[USER_ID] = $resUser[USER_ID];
                    $redirectTo = '/'.SIGNUP_ROUTE.'/'.CONFIRM_ROUTE;
                }
            } else {
                /* add enabled property */
                $userData[ENABLED] = TRUE;
                /* save user */
                $user = new User($this->conn);
                $resUser = $user->saveUser($userData);
                /* if success create login session */
                if ($resUser[SUCCESS]) {
                    $this->createLoginSession($resUser[USER_ID]);
                    $redirectTo = '/'.HOME_ROUTE;
                }
            }
            /* set result */
            $resSignup[MESSAGE] = $resUser[MESSAGE];
            $resSignup[SUCCESS] = $resUser[SUCCESS];
        }
        
        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data[MESSAGE])) {
                $_SESSION[MESSAGE] = $data[MESSAGE];
                $_SESSION[SUCCESS] = $data[SUCCESS];
            }
            redirect($data[REDIRECT_TO]);
        };
        
        /* result data */
        $dataOut = [
            REDIRECT_TO => $redirectTo,
            SUCCESS => $resSignup[SUCCESS],
            ERROR => $resSignup[ERROR] ?? NULL,
            MESSAGE => $resSignup[MESSAGE] ?? NULL
        ];

        $this->switchResponse($dataOut, (!$resSignup[SUCCESS] && $resSignup[GENERATE_TOKEN]), $funcDefault, CSRF_SIGNUP);
    }

    /* function to resend a signup email */
    public function signupResendEmail() {
        /* redirects */
        $this->redirectOrFailIfConfirmEmailNotRequire();
        $this->redirectOrFailIfLogin();
        $this->redirectIfNotSignupSession();

        /* set url to redirect */
        $redirectTo = '/'.SIGNUP_ROUTE.'/'.CONFIRM_ROUTE;
        /* check last request */
        if (isset($_SESSION[RESEND_LOCK_EXPIRE]) && $_SESSION[RESEND_LOCK_EXPIRE] > new DateTime()) $this->switchFailResponse('Wait a few minutes before another request', $redirectTo);

        /* get tokens and user id */
        $tokens = $this->getPostSessionTokens(CSRF_RESEND_ENABLER_ACC);
        $userId = $_SESSION[USER_ID];

        /* get verifier instance, and check the resend validator email request */
        $verifier = LoginVerifier::getInstance($this->conn);
        $resResendEmail = $verifier->verifySignupResendEmail($userId, $tokens);

        /* if verifier succes */
        if ($resResendEmail[SUCCESS]) {
            if ($resResendEmail[SUCCESS] = $this->sendEnablerEmail($resResendEmail[EMAIL], $resResendEmail[TOKEN])) {
                $resResendEmail[MESSAGE] = 'Email successfully sended at '.$resResendEmail[EMAIL];
                $_SESSION[RESEND_LOCK_EXPIRE] = new DateTime();
                $_SESSION[RESEND_LOCK_EXPIRE]->modify(RESEND_LOCK_EXPIRE_TIME);
            } else $resResendEmail[MESSAGE] = 'Send email failed';
        }

        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data[MESSAGE])) {
                $_SESSION[MESSAGE] = $data[MESSAGE];
                $_SESSION[SUCCESS] = $data[SUCCESS];
            }
            redirect($data[REDIRECT_TO]);
        };

        /* result data */
        $dataOut = [
            REDIRECT_TO => $redirectTo,
            SUCCESS => $resResendEmail[SUCCESS],
            MESSAGE => $resResendEmail[MESSAGE] ?? NULL
        ];

        $this->switchResponse($dataOut, $resResendEmail[GENERATE_TOKEN], $funcDefault, CSRF_RESEND_ENABLER_ACC);
    }

    /* function handler for logout request */
    public function logout() {
        /* redirect */
        $this->redirectOrFailIfNotLogin();

        /* get tokens and user id */
        $tokens = $this->getPostSessionTokens(CSRF_LOGOUT);

        /* set url to redirect */
        $redirectTo = '/'.HOME_ROUTE;

        /* get verifier instance, and check the logout request */
        $verifier = LoginVerifier::getInstance($this->conn);
        $resLogout = $verifier->verifyLogout($this->loginSession->{USER_ID}, $tokens);
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
            redirect($data[REDIRECT_TO]);
        };

        /* result data */
        $dataOut = [
            REDIRECT_TO => $redirectTo,
            SUCCESS => $resLogout[SUCCESS],
            MESSAGE => $resLogout[MESSAGE] ?? NULL
        ];

        $this->switchResponse($dataOut, (!$resLogout[SUCCESS] && $resLogout[GENERATE_TOKEN]), $funcDefault, CSRF_LOGOUT);
    }

    /* function to mangae reset password request */ 
    public function passwordResetRequest() {
        /* redirect */
        $this->redirectOrFailIfLogin();

        /* get tokens and email */
        $tokens = $this->getPostSessionTokens(CSRF_PASS_RESET_REQ);
        $email = $_POST[EMAIL] ?? '';

        /* get verifier instance, and check the reset password request */
        $verifier = LoginVerifier::getInstance($this->conn);
        $resPassResetReq = $verifier->verifyPassResetReq($email, $tokens);

        /* set url to redirect */
        $redirectTo = '/'.PASS_RESET_REQ_ROUTE;
        /* if verifier success */
        if ($resPassResetReq[SUCCESS]) {
            /* init password reset request model */
            $passResReq = new PasswordResetRequest($this->conn);
            /* remove all previus request */
            $passResReq->removePasswordResetReqForUser($resPassResetReq[USER]->{USER_ID});
            /* calc expire datae time add a new request */
            $expireDatetime = getExpireDatetime(PASS_RESET_EXPIRE_TIME);
            $res = $passResReq->newPasswordResetReq($resPassResetReq[USER]->{USER_ID}, $_SERVER['REMOTE_ADDR'], $expireDatetime);
            /* if success */
            if ($res[SUCCESS] && $res[SUCCESS] = $this->sendEmailResetPassword($email, $res[TOKEN])) {
                $res[MESSAGE] = 'Password reset email sended succesfully';
                $redirectTo = '/'.LOGIN_ROUTE;
            } else $res[MESSAGE] = 'Send email failed';

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
            redirect($data[REDIRECT_TO]);
        };

        /* result data */
        $dataOut = [
            REDIRECT_TO => $redirectTo,
            SUCCESS => $resPassResetReq[SUCCESS],
            ERROR => $resPassResetReq[ERROR] ?? NULL,
            MESSAGE => $resPassResetReq[MESSAGE] ?? NULL
        ];

        $this->switchResponse($dataOut, (!$resPassResetReq[SUCCESS] && $resPassResetReq[GENERATE_TOKEN]), $funcDefault, CSRF_PASS_RESET_REQ);
    }

    /* function to reset a password */
    public function passwordReset() {
        /* redirects */
        $this->redirectOrFailIfLogin();
        $tokenReset = $_POST[PASSWORD_RESET_TOKEN] ?? '';
        /* set url to redirect */
        $redirectTo = '/'.PASS_RESET_ROUTE."/$tokenReset";
        $this->redirectIfNotXMLHTTPRequest($redirectTo);

        /* get tokens and post data */
        $tokens = $this->getPostSessionTokens(CSRF_PASS_RESET);
        $pass = $_POST[PASSWORD] ?? '';
//         if (empty($pass)) $this->switchFailResponse('Insert a password', '/'.PASS_RESET_ROUTE."/$tokenReset");
        $cpass = $_POST[CONFIRM_PASS] ?? '';

        /* decrypt passwords */
        $pass = $this->decryptData($pass);
        $cpass = $this->decryptData($cpass);

        /* get verifier instance, and check the reset password request */
        $verifier = LoginVerifier::getInstance($this->conn);
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
                $redirectTo = '/'.LOGIN_ROUTE;
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
            redirect($data[REDIRECT_TO]);
        };

        /* result data */
        $dataOut = [
            REDIRECT_TO => $redirectTo,
            SUCCESS => $resResetPass[SUCCESS],
            ERROR => $resResetPass[ERROR] ?? NULL,
            MESSAGE => $resResetPass[MESSAGE] ?? NULL
        ];
        $this->switchResponse($dataOut, (!$resResetPass[SUCCESS] && $resResetPass[GENERATE_TOKEN]), $funcDefault, CSRF_PASS_RESET);
    }

    /* function to enable new account */
    public function enableAccount(string $token) {
        /* redirect */
        $this->redirectOrFailIfConfirmEmailNotRequire();
        
        /* get verifier instance, and check the enable account request */
        $verifier = LoginVerifier::getInstance($this->conn);
        $resEnable = $verifier->verifyEnableAccount($token);
        
        /* if verifier fails, show page not found and return */
        /* set url to redirect */
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
            ROLE_ID_FRGN => $resEnable[USER]->{ROLE_ID_FRGN},
            ENABLED => TRUE,
            REGISTRATION_DATETIME => $resEnable[USER]->{REGISTRATION_DATETIME}
        ];
        /* init user model and save user */
        $user = new User($this->conn);
        $res = $user->saveUserSetRegistrationDatetime($dataUsr, FALSE);
        /* set session message */
        if (isset($res[MESSAGE])){
            $_SESSION[MESSAGE] = $res[MESSAGE];
            $_SESSION[SUCCESS] = $res[SUCCESS];
        }

        /* if enable user fails, redirect on signup page */
        if (!$res[SUCCESS]) redirect('/'.SIGNUP_ROUTE);
        
        /* set user id, remove token and redirect on login page or home */
        $pendUser = new PendingUser($this->conn);
        $pendUser->setUserIdAndRemoveToken($token, $res[USER_ID]);
        redirect('/'.($this->loginSession ? HOME_ROUTE : LOGIN_ROUTE));
    }

    /* function to validate a new email */
    public function enableNewEmail(string $token) {
        /* redirect */
        $this->redirectOrFailIfConfirmEmailNotRequire();
        
        /* get verifier instance, and check the validate a new email request */
        $verifier = LoginVerifier::getInstance($this->conn);
        $resValidate = $verifier->verifyEnableNewEmail($token);
        
        /* set url to redirect */
        $redirectTo = '/'.($this->loginSession ? HOME_ROUTE : SIGNUP_ROUTE);
        /* if verifier fails, show page not found and return */
        if (!$resValidate[SUCCESS]) {
            /* if link is expire redirect to signup and remove token*/
            if ($resValidate[REMOVE_TOKEN]) {
                $pendEmail = new PendingEmail($this->conn);
                $pendEmail->removeAllEmailEnablerToken($resValidate->{USER_ID});
                $this->switchFailResponse($resValidate[MESSAGE], $redirectTo);
            /* else show page not found */
            } else $this->showPageNotFound();
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
        if (!$res[SUCCESS]) redirect($redirectTo);
        
        /* else remove token to confirm new email */
        $pendEmail = new PendingEmail($this->conn);
        $pendEmail->removeAllEmailEnablerToken($resValidate[USER]->{USER_ID});
        
        /* if user is not login redirect on login page */
        $redirectTo = '/'.($this->loginSession ? HOME_ROUTE : LOGIN_ROUTE);
        
        /* else redirect to home */
        redirect($redirectTo);
    }

    /* ##################################### */
    /* PRIVATE FUNCTION */
    /* ##################################### */
    

    /* function to redirect if client is not on signup session */
    private function redirectIfNotSignupSession() {
        if (!(($_SESSION[SIGNUP] ?? FALSE) && ($_SESSION[USER_ID] ?? FALSE))) redirect();
    }
}

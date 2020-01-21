<?php
namespace app\controllers;

use app\controllers\verifiers\AccountVerifier;
use app\controllers\data\AccountDataFactory;
use app\controllers\verifiers\Verifier;
use app\models\PendingEmail;
use app\models\DeletedUser;
use app\models\User;
use \DateTime;
use \PDO;
use app\models\PasswordResetRequest;
use app\models\Session;

/**
 * Class controller to manage the account requests
 * @author Andrea Serra (DevAS) https://devas.info
 */
class AccountController extends Controller {
    public function __construct(PDO $conn, array $appConfig, string $layout=DEFAULT_LAYOUT) {
        parent::__construct($conn, $appConfig, $layout);
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */
    
    /* ########## SHOW FUNCTIONS ########## */

    /* function to view delete account page */
    public function showDeleteAccount() {
        /* redirect */
        $this->redirectOrFailIfNotLogin();
        $this->handlerDoubleLogin();

        /* add javascript sources */
        array_push($this->jsSrcs,
            [SOURCE => '/js/utils/account/delete.js']
        );

//         /* calc expire time */
//         $expireDatetime = new DateTime();
//         $expireDatetime->modify(DELETE_SESSION_EXPIRE_TIME);
//         /* create a delete session */
//         $_SESSION[DELETE_SESSION] = [EXPIRE_DATETIME => $expireDatetime];

        /* generate token and show page */
        $this->content = view(getPath('account','delete'), [TOKEN => generateToken(CSRF_DELETE_ACCOUNT)]);
    }

    /* function to show the account settings */
    public function showAccountSettings() {
        /* redirect */
        $this->redirectOrFailIfNotLogin();
        
        /* add javascript sources */
        array_push($this->jsSrcs,
            [SOURCE => '/js/utils/validate.js'],
            [SOURCE => '/js/utils/account/settings.js']
        );
        
        /* get data from data factory */
        $data = AccountDataFactory::getInstance($this->conn)->getAccountSettingsData($this->loginSession->{USER_ID});
        /* if wait email confir, then add javascript sorce for new email settings */
        if ($data[WAIT_EMAIL_CONFIRM]) $this->jsSrcs[] = [SOURCE => '/js/utils/account/new-email-settings.js'];
        
        /* show user settings page */
        $this->content = view(getPath('account','settings'), $data);
    }

    /* function to view acoount info page */
    public function showAccountInfo() {
        /* redirect */
        $this->redirectOrFailIfNotLogin();

        $data = AccountDataFactory::getInstance($this->conn)->getAccountInfoData($this->loginSession->{USER_ID});

        /* generate token and show change account info page */
        $this->content = view(getPath('account','info'), $data);
    }

    /* function to view password change page */
    public function showChangePassword() {
        /* redirect */
        $this->redirectOrFailIfNotLogin();

        /* add javascript sources */
        array_push($this->jsSrcs,
            [SOURCE => '/js/crypt/jsbn.js'],
            [SOURCE => '/js/crypt/prng4.js'],
            [SOURCE => '/js/crypt/rng.js'],
            [SOURCE => '/js/crypt/rsa.js'],
            [SOURCE => '/js/utils/req-key.js'],
            [SOURCE => '/js/utils/validate.js'],
            [SOURCE => '/js/utils/account/change-pass.js']
        );

        /* generate token and show change password page */
        $this->content = view(getPath('account','change-pass'), [
            /* set tokens */
            TOKEN => generateToken(CSRF_CHANGE_PASS),
            GET_KEY_TOKEN => generateToken(CSRF_KEY_JSON)
        ]);
    }

    /* ########## ACTION FUNCTIONS ########## */

    /* fuction to delete the account */
    public function deleteAccount() {
        /* redirect */
        $this->redirectOrFailIfNotLogin();
//         $this->redirectOrFailIfNotDeleteSession();
        $this->handlerDoubleLogin();

        /* get tokens and user id */
        $tokens = $this->getPostSessionTokens(CSRF_DELETE_ACCOUNT);
        $id = $this->loginSession->{USER_ID};

        /* get verifier instance, and check delete account request */
        $verifier = Verifier::getInstance($this->conn);
        $resDelete = $verifier->verifyDelete($id, $tokens);
        /* if success */
        if($resDelete[SUCCESS]) {
            /* init user model and delete user */
            $userModel = new User($this->conn);
            $resUser = $userModel->deleteUser($id);
            /* if delete success */
            if ($resUser[SUCCESS]) {
                /* init delete model and save user deleted */
                $delModel = new DeletedUser($this->conn);
                $delModel->saveDeletedUser($resDelete[USER]);
                /* init pending mail model and remove all user tokens */
                $pendMailModel = new PendingEmail($this->conn);
                $pendMailModel->removeAllEmailEnablerToken($id);
                /* init password reset request model and remove all user tokens */
                $pendPassResReqModel = new PasswordResetRequest($this->conn);
                $pendPassResReqModel->removePasswordResetReqForUser($id);
                /* init session model and remove all user tokens */
                $sessionModel = new Session($this->conn);
                $sessionModel->removeAllLoginSessionTokens($id);
            }
            /* set result */
            $resDelete[MESSAGE] = $resUser[MESSAGE];
            $resDelete[SUCCESS] = $resUser[SUCCESS];
        }

        /* result data */
        $dataOut = [
            SUCCESS => $resDelete[SUCCESS],
            MESSAGE => $resDelete[MESSAGE] ?? NULL,
            ERROR => $resDelete[ERROR] ?? NULL
        ];

        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data[MESSAGE])) {
                $_SESSION[MESSAGE] = $data[MESSAGE];
                $_SESSION[SUCCESS] = $data[SUCCESS];
            }
            redirect();
        };

        $this->switchResponse($dataOut, (!$resDelete[SUCCESS] && $resDelete[GENERATE_TOKEN]), $funcDefault, CSRF_DELETE_ACCOUNT);
    }

    /* function to upadate account */
    public function updateAccount() {
        /* redirect */
        $this->redirectOrFailIfNotLogin();

        /* get tokens and post data */
        $tokens = $this->getPostSessionTokens(CSRF_UPDATE_ACCOUNT);
        $email = $_POST[EMAIL] ?? '';
        $username = $_POST[USERNAME] ?? '';
        $name = $_POST[NAME] ?? '';
        $id = $this->loginSession->{USER_ID};

        /* set redirect to */
        $redirectTo = '/'.ACCOUNT_SETTINGS_ROUTE;

        /* get verifier instance, and check update user request */
        $verifier = Verifier::getInstance($this->conn);
        $resUpdate = $verifier->verifyUpdate($id, $name, $email, $username, $tokens);
        /* if success */
        if($resUpdate[SUCCESS]) {
            /* create user data */
            $userData = [
                NAME => $name,
                USERNAME => $username
            ];
            /* if confirm email is not require, then set new email on user table */
            if (!$this->appConfig[UMS][REQUIRE_CONFIRM_EMAIL]) $userData[EMAIL] = $email;
            /* else if is change email, then add email on pending and send enabler link */
            elseif ($resUpdate[CHANGED_EMAIL]) {
                /* init pending model */
                $pendMailModel = new PendingEmail($this->conn);
                /* calc expire datetime */
                $expireDatetime = getExpireDatetime(ENABLER_LINK_EXPIRE_TIME);
                /* remove all previus request and add a new pending mail */
                $pendMailModel->removeAllEmailEnablerToken($this->loginSession->{USER_ID});
                $resPend = $pendMailModel->newPendingEmail($id, $email, $expireDatetime);
                /* if success send email and set success result */
                $resUpdate[SUCCESS] = $resPend[SUCCESS] && $this->sendEnablerEmail($email, $resPend[TOKEN], 'ENABLE YOUR EMAIL', TRUE);
            }
            if ($resUpdate[SUCCESS]) {
                /* init user model */
                $userModel = new User($this->conn);
                /* update user */
                $resUser = $userModel->updateUser($id, $userData);

                /* if success set redirecy to account info */
                if ($resUser[SUCCESS]) $redirectTo = '/'.ACCOUNT_INFO_ROUTE;

                /* set result */
                $resUpdate[MESSAGE] = $resUser[MESSAGE];
                $resUpdate[SUCCESS] = $resUser[SUCCESS];
            }
        }

        /* result data */
        $dataOut = [
            REDIRECT_TO => $redirectTo,
            SUCCESS => $resUpdate[SUCCESS],
            ERROR => $resUpdate[ERROR] ?? NULL,
            MESSAGE => $resUpdate[MESSAGE] ?? NULL
        ];

        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data[MESSAGE])) {
                $_SESSION[MESSAGE] = $data[MESSAGE];
                $_SESSION[SUCCESS] = $data[SUCCESS];
            }
            redirect($data[REDIRECT_TO]);
        };

        $this->switchResponse($dataOut, (!$resUpdate[SUCCESS] && $resUpdate[GENERATE_TOKEN]), $funcDefault, CSRF_UPDATE_ACCOUNT);
    }

    /* function to show password change page */
    public function changePassword() {
        /* redirects */
        $this->redirectOrFailIfNotLogin();
        /* set redirect to */
        $redirectTo = '/'.ACCOUNT_SETTINGS_ROUTE.'/'.PASS_UPDATE_ROUTE;
        $this->redirectIfNotXMLHTTPRequest($redirectTo);

        /* get tokens and post data */
        $tokens = $this->getPostSessionTokens(CSRF_CHANGE_PASS);
        $oldPass = $_POST[OLD_PASS] ?? '';
        $pass = $_POST[PASSWORD] ?? '';
        $cpass = $_POST[CONFIRM_PASS] ?? '';
        $id = $this->loginSession->{USER_ID};

        /* decrypt passwords */
        $oldPass = $this->decryptData($oldPass);
        $pass = $this->decryptData($pass);
        $cpass = $this->decryptData($cpass);

        /* get verifier instance, and check change password request */
        $verifier = AccountVerifier::getInstance($this->conn);
        $resPass = $verifier->verifyChangePass($id, $oldPass, $pass, $cpass, $tokens);


        /* if success */
        if($resPass[SUCCESS]) {
            /* init user model and reset wrong passwords lock */
            $user = new User($this->conn);
            $user->resetLockCounts($id);

            /* update user password */
            $resUser = $user->updatePassword($id, $pass);

            /* if success set redirecy to account info */
            if ($resUser[SUCCESS]) $redirectTo = '/'.ACCOUNT_INFO_ROUTE;

            /* set result */
            $resPass[MESSAGE] = $resUser[MESSAGE];
            $resPass[SUCCESS] = $resUser[SUCCESS];
        /* else set error message */
        } else if ($resPass[WRONG_PASSWORD]) $this->handlerWrongPassword($id);

        /* result data */
        $dataOut = [
            REDIRECT_TO => $redirectTo,
            SUCCESS => $resPass[SUCCESS],
            ERROR => $resPass[ERROR] ?? NULL,
            MESSAGE => $resPass[MESSAGE]?? NULL
        ];

        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data[MESSAGE])) {
                $_SESSION[MESSAGE] = $data[MESSAGE];
                $_SESSION[SUCCESS] = $data[SUCCESS];
            }
            redirect($data[REDIRECT_TO]);
        };

        $this->switchResponse($dataOut, (!$resPass[SUCCESS] && $resPass[GENERATE_TOKEN]), $funcDefault, CSRF_CHANGE_PASS);
    }

    /* function to delete new email on pending */
    public function deleteNewEmail() {
        /* redirects */
        $this->redirectOrFailIfNotLogin();
        $this->redirectOrFailIfConfirmEmailNotRequire();

        /* get tokens and user id */
        $tokens = $this->getPostSessionTokens(CSRF_DELETE_NEW_EMAIL);
        $id = $this->loginSession->{USER_ID};

        /* get verifier instance, and check delete new email request */
        $verifier = AccountVerifier::getInstance($this->conn);
        $resDeleteEmail = $verifier->verifyDeleteNewEmail($id, $tokens);
        /* if success */
        if ($resDeleteEmail[SUCCESS]) {
            /* init pending mail model */
            $pendMail = new PendingEmail($this->conn);
            /* remove new email with token and if success set success messagge */
            if (($resDeleteEmail[SUCCESS] = $pendMail->removeAllEmailEnablerToken($id))) $resDeleteEmail[MESSAGE] =  'Email successfully deleted';
            /* else set error message */
            else $resDeleteEmail[MESSAGE] = 'Delete email failed';
        }

        /* result data */
        $dataOut = [
            SUCCESS => $resDeleteEmail[SUCCESS],
            ERROR => $resDeleteEmail[ERROR] ?? NULL,
            MESSAGE => $resDeleteEmail[MESSAGE] ?? NULL
        ];

        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data[MESSAGE])) {
                $_SESSION[MESSAGE] = $data[MESSAGE];
                $_SESSION[SUCCESS] = $data[SUCCESS];
            }
            redirect('/'.ACCOUNT_SETTINGS_ROUTE);
        };

        $this->switchResponse($dataOut, (!$resDeleteEmail[SUCCESS] && $resDeleteEmail[GENERATE_TOKEN]), $funcDefault, CSRF_DELETE_NEW_EMAIL);
    }

    /* function to resend email enabler */
    public function resendEmailEnabler() {
        /* redirects */
        $this->redirectOrFailIfNotLogin();
        $this->redirectOrFailIfConfirmEmailNotRequire();

        /* check resend lock */
        if (isset($_SESSION[RESEND_LOCK_EXPIRE]) && $_SESSION[RESEND_LOCK_EXPIRE] > new DateTime()) $this->switchFailResponse('Wait a few minutes before another request');
        
        /* get tokens and user id */
        $tokens = $this->getPostSessionTokens(CSRF_RESEND_ENABLER_EMAIL);
        $id = $this->loginSession->{USER_ID};

        /* get verifier instance, and check resend new email validation request */
        $verifier = AccountVerifier::getInstance($this->conn);
        $resResendEmail = $verifier->verifyResendNewEmailValidation($id, $tokens);
        /* if success */
        if ($resResendEmail[SUCCESS]) {
            /* send email validation and set result */
            if ($resResendEmail[SUCCESS] = $this->sendEnablerEmail($resResendEmail[TO], $resResendEmail[TOKEN], 'ENABLE YOUR EMAIL', TRUE)) {
                $_SESSION[RESEND_LOCK_EXPIRE] = new DateTime();
                $_SESSION[RESEND_LOCK_EXPIRE]->modify(RESEND_LOCK_EXPIRE_TIME);
                $resResendEmail[MESSAGE] = 'Email sent successfully';
            } else $resResendEmail[MESSAGE] = 'Sending email failed';
        }

        /* result data */
        $dataOut = [
            SUCCESS => $resResendEmail[SUCCESS],
            ERROR => $resResendEmail[ERROR] ?? NULL,
            MESSAGE => $resResendEmail[MESSAGE] ?? NULL
        ];

        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data[MESSAGE])) {
                $_SESSION[MESSAGE] = $data[MESSAGE];
                $_SESSION[SUCCESS] = $data[SUCCESS];
            }
            redirect('/'.ACCOUNT_SETTINGS_ROUTE);
        };

        $this->switchResponse($dataOut, $resResendEmail[GENERATE_TOKEN], $funcDefault, CSRF_RESEND_ENABLER_EMAIL);
    }

    /* ##################################### */
    /* PRIVATE FUNCTIONS */
    /* ##################################### */

//     /* function to redirect if is not valid delete session */
//     private function redirectOrFailIfNotDeleteSession(){
//         if (!(isset($_SESSION[DELETE_SESSION]) && $_SESSION[DELETE_SESSION][EXPIRE_DATETIME] > new DateTime())) $this->switchFailResponse();
//     }
} 
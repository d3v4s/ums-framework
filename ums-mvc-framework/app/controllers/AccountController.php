<?php
namespace app\controllers;

use app\controllers\verifiers\AccountVerifier;
use app\controllers\data\AccountDataFactory;
use app\controllers\verifiers\Verifier;
use app\models\PasswordResetRequest;
use app\models\PendingEmail;
use app\models\DeletedUser;
use app\models\Session;
use app\models\User;
use \PDO;

/**
 * Class controller to manage the account requests
 * @author Andrea Serra (DevAS) https://devas.info
 */
class AccountController extends Controller {
    public function __construct(PDO $conn, array $appConfig, string $layout=DEFAULT_LAYOUT) {
        parent::__construct($conn, $appConfig, $layout);
        $this->lang = array_merge_recursive($this->lang, $this->getLanguageArray('account'));
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* ########## SHOW FUNCTIONS ########## */

    /* function to view delete account page */
    public function showDeleteAccount() {
        /* redirect */
        $this->sendFailIfNotLogin();
        $this->handlerDoubleLogin();

        /* add javascript sources */
        array_push($this->jsSrcs,
            [SOURCE => '/js/utils/account/delete.js']
        );

        $data = AccountDataFactory::getInstance($this->lang[DATA])->getDeleteAccountData();
        /* generate token and show page */
        $this->content = view(getPath('account','delete'), $data);
    }

    /* function to show the account settings */
    public function showAccountSettings() {
        /* redirect */
        $this->sendFailIfNotLogin();

        /* add javascript sources */
        array_push($this->jsSrcs,
            [SOURCE => '/js/utils/validate.js'],
            [SOURCE => '/js/utils/account/settings.js']
        );

        /* get data from data factory */
        $data = AccountDataFactory::getInstance($this->lang[DATA], $this->conn)->getAccountSettingsData($this->loginSession->{USER_ID});
        /* if wait email confir, then add javascript sorce for new email settings */
        if ($data[WAIT_EMAIL_CONFIRM]) $this->jsSrcs[] = [SOURCE => '/js/utils/account/new-email-settings.js'];

        /* show user settings page */
        $this->content = view(getPath('account','settings'), $data);
    }

    /* function to view acoount info page */
    public function showAccountInfo() {
        /* redirect */
        $this->sendFailIfNotLogin();

        $data = AccountDataFactory::getInstance($this->lang[DATA], $this->conn)->getAccountInfoData($this->loginSession->{USER_ID});

        /* generate token and show change account info page */
        $this->content = view(getPath('account','info'), $data);
    }

    /* function to view password change page */
    public function showChangePassword() {
        /* redirect */
        $this->sendFailIfNotLogin();

        /* add javascript sources */
        array_push($this->jsSrcs,
            [SOURCE => '/js/utils/validate.js'],
            [SOURCE => '/js/utils/account/change-pass.js']
        );

        $data = AccountDataFactory::getInstance($this->lang[DATA], $this->conn)->getChangePasswordData($this->loginSession->{USER_ID});

        /* generate token and show change password page */
        $this->content = view(getPath('account','change-pass'), $data);
    }

    /* function to show active sessions of user */
    public function showSessions() {
        /* redirect */
        $this->sendFailIfNotLogin();

        /* add javascript sources */
        array_push($this->jsSrcs, 
            [SOURCE => '/js/utils/account/sessions.js']
        );

        /* get sessions data */
        $data = AccountDataFactory::getInstance($this->lang[DATA], $this->conn)->getSessionsData($this->loginSession->{USER_ID}, $this->loginSession->{SESSION_ID});

        /* show sessions page */
        $this->content = view(getPath('account', 'sessions'), $data);
    }

    /* ########## ACTION FUNCTIONS ########## */

    /* fuction to delete the account */
    public function deleteAccount() {
        /* redirect */
        $this->sendFailIfNotLogin();
        $this->handlerDoubleLogin();

        /* get tokens and user id */
        $tokens = $this->getPostSessionTokens(CSRF_DELETE_ACCOUNT);
        $id = $this->loginSession->{USER_ID};

        /* get verifier instance, and check delete account request */
        $verifier = Verifier::getInstance($this->lang[MESSAGE], $this->conn);
        $resDelete = $verifier->verifyDelete($id, $tokens);
        /* if success */
        if($resDelete[SUCCESS]) {
            /* init user model and delete user */
            $userModel = new User($this->conn);
            $resDelete = array_merge($resDelete, $userModel->deleteUser($id));
            /* if delete success */
            if ($resDelete[SUCCESS]) {
                $resDelete[MESSAGE] = $this->lang[MESSAGE][USER_DELETE][SUCCESS];
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
            /* else set fail message */
            } else $resDelete[MESSAGE] = $this->lang[MESSAGE][USER_DELETE][FAIL];
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
        $this->sendFailIfNotLogin();

        /* get tokens and post data */
        $tokens = $this->getPostSessionTokens(CSRF_UPDATE_ACCOUNT);
        $name = $_POST[NAME] ?? '';
        $username = $_POST[USERNAME] ?? '';
        $email = $_POST[EMAIL] ?? '';
        $id = $this->loginSession->{USER_ID};

        /* set redirect to */
        $redirectTo = '/'.ACCOUNT_SETTINGS_ROUTE;

        /* get verifier instance, and check update user request */
        $verifier = Verifier::getInstance($this->lang[MESSAGE], $this->conn);
        $resUpdate = $verifier->verifyUpdate($id, $name, $email, $username, $tokens);
        /* if success */
        if($resUpdate[SUCCESS]) {
            /* create user data */
            $userData = [
                NAME => $name,
                USERNAME => $username
            ];
            /* if confirm email is not require, then set new email on user data */
            if (!$this->appConfig[UMS][REQUIRE_CONFIRM_EMAIL]) $userData[EMAIL] = $email;
            /* else if is change email, then add email on pending and send enabler link */
            elseif ($resUpdate[CHANGED_EMAIL]) {
                /* init pending model */
                $pendMailModel = new PendingEmail($this->conn);
                /* calc expire datetime */
                $expireDatetime = getExpireDatetime(ENABLER_LINK_EXPIRE_TIME);
                /* remove all previus request and add a new pending mail */
                $pendMailModel->removeAllEmailEnablerToken($this->loginSession->{USER_ID});
                $resUpdate = array_merge($resUpdate, $pendMailModel->newPendingEmail($id, $email, $expireDatetime));
                /* if success send email and set success result */
                $resUpdate[SUCCESS] = $resUpdate[SUCCESS] && $this->sendEnablerEmail($email, $resUpdate[TOKEN], 'ENABLE YOUR EMAIL', TRUE);
            }
            if ($resUpdate[SUCCESS]) {
                /* init user model */
                $userModel = new User($this->conn);
                /* update user */
                $resUpdate = array_merge($resUpdate, $userModel->updateUser($id, $userData));

                /* if success set redirecy to account info */
                if ($resUpdate[SUCCESS]) {
                    $resUpdate[MESSAGE] = $this->lang[MESSAGE][USER_UPDATE][SUCCESS];
                    $redirectTo = '/'.ACCOUNT_INFO_ROUTE;
                /* else set fail message */
                } else $resUpdate[MESSAGE] = $this->lang[MESSAGE][USER_UPDATE][FAIL];
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
        $this->sendFailIfNotLogin();
        /* set redirect to */
        $redirectTo = '/'.ACCOUNT_SETTINGS_ROUTE.'/'.PASS_UPDATE_ROUTE;
        $this->redirectIfNotXMLHTTPRequest($redirectTo);

        /* get tokens and post data */
        $tokens = $this->getPostSessionTokens(CSRF_CHANGE_PASS);
        $oldPass = $_POST[CURRENT_PASS] ?? '';
        $pass = $_POST[PASSWORD] ?? '';
        $cpass = $_POST[CONFIRM_PASS] ?? '';
        $id = $this->loginSession->{USER_ID};

        /* decrypt passwords */
        $oldPass = $this->decryptData($oldPass);
        $pass = $this->decryptData($pass);
        $cpass = $this->decryptData($cpass);

        /* get verifier instance, and check change password request */
        $verifier = AccountVerifier::getInstance($this->lang[MESSAGE], $this->conn);
        $resPass = $verifier->verifyChangePass($id, $oldPass, $pass, $cpass, $tokens);

        /* if success */
        if($resPass[SUCCESS]) {
            /* init user model and reset wrong passwords lock */
            $user = new User($this->conn);
            $user->lockCountsReset($id);

            /* update user password */
            $resPass = array_merge($resPass, $user->updatePassword($id, $pass));

            /* if success set redirecy to account info */
            if ($resPass[SUCCESS]) {
                $resPass[MESSAGE] = $this->lang[MESSAGE][CHANGE_PASS][SUCCESS];
                $redirectTo = '/'.ACCOUNT_INFO_ROUTE;
                /* else set fail message */
            } else $resPass[MESSAGE] = $this->lang[MESSAGE][CHANGE_PASS][FAIL];

        /* else if wrong pass, call handler */
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
        $this->sendFailIfNotLogin();
        $this->sendFailIfConfirmEmailNotRequire();

        /* get tokens and user id */
        $tokens = $this->getPostSessionTokens(CSRF_DELETE_NEW_EMAIL);
        $id = $this->loginSession->{USER_ID};

        /* get verifier instance, and check delete new email request */
        $verifier = AccountVerifier::getInstance($this->lang[MESSAGE], $this->conn);
        $resDeleteEmail = $verifier->verifyDeleteNewEmail($id, $tokens);
        /* if success */
        if ($resDeleteEmail[SUCCESS]) {
            /* init pending mail model */
            $pendMail = new PendingEmail($this->conn);
            /* remove new email with token and if success set success messagge */
            if (($resDeleteEmail[SUCCESS] = $pendMail->removeAllEmailEnablerToken($id))) $resDeleteEmail[MESSAGE] = $this->lang[MESSAGE][NEW_EMAIL_DELETE][SUCCESS];
            /* else set error message */
            else $resDeleteEmail[MESSAGE] = $this->lang[MESSAGE][NEW_EMAIL_DELETE][FAIL];
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
        $this->sendFailIfNotLogin();
        $this->sendFailIfConfirmEmailNotRequire();

        /* check resend lock */
        $this->handlerResendLock();

        /* get tokens and user id */
        $tokens = $this->getPostSessionTokens(CSRF_RESEND_ENABLER_EMAIL);
        $id = $this->loginSession->{USER_ID};

        /* get verifier instance, and check resend new email validation request */
        $verifier = AccountVerifier::getInstance($this->lang[MESSAGE], $this->conn);
        $resResendEmail = $verifier->verifyResendNewEmailValidation($id, $tokens);
        /* if success */
        if ($resResendEmail[SUCCESS]) {
            /* send email validation and set result */
            if ($resResendEmail[SUCCESS] = $this->sendEnablerEmail($resResendEmail[TO], $resResendEmail[TOKEN], 'ENABLE YOUR EMAIL', TRUE)) {
                $resResendEmail[MESSAGE] = $this->lang[MESSAGE][SEND_EMAIL][SUCCESS].$resResendEmail[TO];
                /* set resend lock */
                $this->setResendLock();
                /* init pending mail model and update expire time */
                $pendMailModel = new PendingEmail($this->conn);
                $expTime = getExpireDatetime(ENABLER_LINK_EXPIRE_TIME);
                $pendMailModel->updateExpireDatetime($resResendEmail[TOKEN], $expTime);
            } else $resResendEmail[MESSAGE] = $this->lang[MESSAGE][SEND_EMAIL][FAIL];
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

    /* function to delete new email on pending */
    public function removeSession() {
        /* redirects */
        $this->sendFailIfNotLogin();

        /* require double login */
        $this->handlerDoubleLogin();

        /* get tokens, user id and session id */
        $tokens = $this->getPostSessionTokens(CSRF_INVALIDATE_SESSION);
        $id = $this->loginSession->{USER_ID};
        $sessionId = $_POST[SESSION_ID];

        /* get verifier instance, and check remve session request */
        $resRemoveSess = AccountVerifier::getInstance($this->lang[MESSAGE], $this->conn)->verifyRemoveSession($id, $sessionId, $tokens);
        /* if success */
        if ($resRemoveSess[SUCCESS]) {
            /* init pending mail model */
            $sessModel = new Session($this->conn);
            /* remove new email with token and if success set success messagge */
            if (($resRemoveSess[SUCCESS] = $sessModel->removeLoginSession($sessionId))) $resRemoveSess[MESSAGE] = $this->lang[MESSAGE][REMOVE_SESSION][SUCCESS];
            /* else set error message */
            else $resRemoveSess[MESSAGE] = $this->lang[MESSAGE][REMOVE_SESSION][FAIL];
        }

        /* result data */
        $dataOut = [
            SUCCESS => $resRemoveSess[SUCCESS],
            MESSAGE => $resRemoveSess[MESSAGE] ?? NULL
        ];

        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data[MESSAGE])) {
                $_SESSION[MESSAGE] = $data[MESSAGE];
                $_SESSION[SUCCESS] = $data[SUCCESS];
            }
            redirect('/'.ACCOUNT_SETTINGS_ROUTE.'/'.SESSIONS_ROUTE);
        };

        $this->switchResponse($dataOut, (!$resRemoveSess[SUCCESS] && $resRemoveSess[GENERATE_TOKEN]), $funcDefault, CSRF_INVALIDATE_SESSION);
    }
}

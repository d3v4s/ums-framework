<?php
namespace app\controllers;

use app\controllers\verifiers\UMSVerifier;
use app\controllers\verifiers\Verifier;
use app\controllers\data\UMSDataFactory;
use app\models\PendingUser;
use app\models\DeletedUser;
use app\models\User;
use \PDO;
use app\models\Session;
use app\models\PendingEmail;
use app\models\PasswordResetRequest;
use app\core\Router;

/**
 * Class controller for users admin manage
 * @author Andrea Serra (DevAS) https://devas.info
 */
class UMSActionsController extends UMSBaseController {
    public function __construct(PDO $conn, array $appConfig, string $layout=UMS_LAYOUT) {
        parent::__construct($conn, $appConfig, $layout);
        $this->lang = array_merge_recursive($this->lang, $this->getLanguageArray('ums'));
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* fuction to switch request */
    public function switchShowAction(string $table, string $action, string $id='') {
        $this->sendFailIfSimpleUser();
        switch ($table) {
            case USERS_TABLE:
                $this->switchShowUserAction($action, $id);
                break;
            default:
                $this->switchFailResponse($this->lang[MESSAGE][GENERIC][INVALID_TABLE]);
                break;
        }
    }

    /* ########## SHOW FUNCTIONS ########## */

    /* function to view update user info page */
    public function showUserUpdate($username) {
        /* redirect */
        $this->sendFailIfCanNotUpdateUser();
        
        /* get data from data factory */
        $data = UMSDataFactory::getInstance($this->lang[DATA], $this->conn)->getUpdateUserData($username);
        
        /* if user not found, show error message */
        if (!$data[USER]) $this->showMessageAndExit('User not found', TRUE);
        
        /* add javascript sources */
        array_push($this->jsSrcs,
            [SOURCE => '/js/utils/validate.js'],
            [SOURCE => '/js/utils/ums/user-update.js']
        );
        $data[VIEW_ROLE] = $this->canViewRole();
        $data[CAN_CHANGE_PASSWORD] = $this->canChangePassword();
        /* show page */
        $this->content = view(getPath('ums','user-update'), $data);
    }

    /* function to view update password user page */
    public function showPasswordUpdate($username) {
        /* redirect */
        $this->sendFailIfCanNotChangePassword();
        $this->handlerDoubleLogin();
        
        /* init user model and get user */
        $userModel = new User($this->conn);
        if (is_numeric($username)) $user = $userModel->getUser($username);
        else $user = $userModel->getUserByUsername($username);
        
        /* if user not found, then show error message */
        if (!$user) $this->showMessageAndExit('User not found', TRUE);
        
        /* add javascript sources */
        array_push($this->jsSrcs,
            [SOURCE => '/js/utils/validate.js'],
            [SOURCE => '/js/utils/ums/pass-update.js']
        );

        /* set data */
        $data = [
            USER => $user,
            TOKEN => generateToken(CSRF_UPDATE_PASS),
            GET_KEY_TOKEN => generateToken(CSRF_KEY_JSON)
        ];
        $this->content = view(getPath('ums','pass-update'), $data);
    }

    /* function to view new user page */
    public function showNewUser() {
        /* redirect */
        $this->sendFailIfCanNotCreateUser();

        /* set current location */
        $this->isNewUser = TRUE;

        /* add javascript sources */
        array_push($this->jsSrcs,
            [SOURCE => '/js/utils/validate.js'],
            [SOURCE => '/js/utils/ums/new-user.js']
        );

        /* get data from data factory and show page */
        $data = UMSDataFactory::getInstance($this->lang[DATA], $this->conn)->getNewUserData();
        $this->content = view(getPath('ums', 'new-user'), $data);
    }

    /* ########## ACTION FUNCTIONS ########## */

    /* fuction to switch request */
    public function switchAction(string $table, string $action) {
        switch ($table) {
            case USERS_TABLE:
                $this->switchUserAction($action);
                break;
            case USER_LOCK_TABLE:
                $this->switchUserLockAction($action);
                break;
            case DELETED_USER_TABLE:
                $this->switchDeletedUserAction($action);
                break;
            case PENDING_USERS_TABLE:
                $this->switchPendingUserAction($action);
                break;
            case PENDING_EMAILS_TABLE:
                $this->switchPendingEmailAction($action);
                break;
            case PASSWORD_RESET_REQ_TABLE:
                $this->switchPasResReqAction($action);
                break;
            case SESSIONS_TABLE:
                $this->switchSessionAction($action);
                break;
            default:
                $this->switchFailResponse($this->lang[MESSAGE][GENERIC][INVALID_TABLE]);
                break;
        }
    }

    /* function to update a user info */
    public function userUpdate() {
        /* redirect */
        $this->sendFailIfCanNotUpdateUser();

        /* get tokens and post data */
        $tokens = $this->getPostSessionTokens(CSRF_UPDATE_USER);
        $id = $_POST[USER_ID] ?? '';
        $email = $_POST[EMAIL] ?? '';
        $username = $_POST[USERNAME] ?? '';
        $name = $_POST[NAME] ?? '';

        /* init user model and get user to be update */
        $userModel = new User($this->conn);
        $user = $userModel->getUser($id);
        if ($this->isAdminUser()) {
            $roletype = isset($_POST[ROLE_ID_FRGN]) ? $_POST[ROLE_ID_FRGN] : $user->{ROLE_ID_FRGN};
            $enabled = isset($_POST[ENABLED]) ? 1 : 0;
        } else {
            $roletype = $user->{ROLE_ID_FRGN};
            $enabled = $user->{ENABLED};
            unset($user);
        }

        /* set redirect to */
        $redirectTo = Router::getRoute('app\controllers\UMSActionsController', 'switchShowAction');
        $redirectTo = str_replace(':table', USERS_TABLE, $redirectTo);
        $redirectTo = str_replace(':action', 'update', $redirectTo)."/$id";
        /* get verifier instance, and check update user request */
        $resUpdate = UMSVerifier::getInstance($this->lang[MESSAGE], $this->conn)->verifyUpdateUser($id, $name, $email, $username, $roletype, $tokens);
        /* if success */
        if($resUpdate[SUCCESS]) {
            /* set user data and update user */
            $data = [
                NAME => $name,
                USERNAME => $username,
                EMAIL => $email,
                ROLE_ID_FRGN => $roletype,
                ENABLED => $enabled
            ];
            $resUpdate = array_merge($resUpdate, $userModel->updateUser($id, $data));

            /* if update success set success message and redirect to */
            if ($resUpdate[SUCCESS]){
                $resUpdate[MESSAGE] = $this->lang[MESSAGE][USER_UPDATE][SUCCESS];
                $redirectTo = Router::getRoute('app\controllers\UMSTablesController', 'showRow');
                $redirectTo = str_replace(':table', USERS_TABLE, $redirectTo);
                $redirectTo = str_replace(':id', $id, $redirectTo);
            /* else set fail message */
            } else $resUpdate[MESSAGE] = $this->lang[MESSAGE][USER_UPDATE][FAIL];
        }

        /* result data */
        $dataOut = [
            REDIRECT_TO => $redirectTo,
            SUCCESS => $resUpdate[SUCCESS],
            ERROR => $resUpdate[ERROR] ?? NULL,
            MESSAGE => $resUpdate[MESSAGE] ?? NULL,
        ];

        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data[MESSAGE])) {
                $_SESSION[MESSAGE] = $data[MESSAGE];
                $_SESSION[SUCCESS] = $data[SUCCESS];
            }
            redirect($data[REDIRECT_TO]);
        };

        $this->switchResponse($dataOut, (!$resUpdate[SUCCESS] && $resUpdate[GENERATE_TOKEN]), $funcDefault, CSRF_UPDATE_USER);
    }

    /* function to update password user */
    public function passwordUpdate() {
        /* redirects */
        $this->sendFailIfCanNotChangePassword();
        /* set redirect to */
        $id = $_POST[USER_ID] ?? '';
        $redirectTo = Router::getRoute('app\controllers\UMSActionsController', 'switchShowAction');
        $redirectTo = str_replace(':table', USERS_TABLE, $redirectTo);
        $redirectTo = str_replace(':action', 'password_update', $redirectTo)."/$id";
        $this->redirectIfNotXMLHTTPRequest($redirectTo);

        /* require double login */
        $this->handlerDoubleLogin();
        
        /* get tokens and post data */
        $tokens = $this->getPostSessionTokens(CSRF_UPDATE_PASS);
        $pass = $_POST[PASSWORD] ?? '';
        $cpass = $_POST[CONFIRM_PASS] ?? '';
        
        /* decrypt passwords */
        $pass = $this->decryptData($pass);
        $cpass = $this->decryptData($cpass);

        /* get instance of verifier and check password update request */
        $resPass = UMSVerifier::getInstance($this->lang[MESSAGE], $this->conn)->verifyUpdatePass($id, $pass, $cpass, $tokens);
        /* if success */
        if($resPass[SUCCESS]) {
            /* init user model */
            $user = new User($this->conn);
            /* update user password, and set result */
            $resPass = array_merge($resPass, $user->updatePassword($id, $pass));
            /* if update success set success message and redirect to */
            if ($resPass[SUCCESS]) {
                $resPass[MESSAGE] = $this->lang[MESSAGE][CHANGE_PASS][SUCCESS];
                $redirectTo = Router::getRoute('app\controllers\UMSTablesController', 'showRow');
                $redirectTo = str_replace(':table', USERS_TABLE, $redirectTo);
                $redirectTo = str_replace(':id', $id, $redirectTo);
            /* else set fail message */
            } else $resPass[MESSAGE] = $this->lang[MESSAGE][CHANGE_PASS][FAIL];
        }
        
        /* result data */
        $dataOut = [
            REDIRECT_TO => $redirectTo,
            SUCCESS => $resPass[SUCCESS],
            ERROR => $resPass[ERROR] ?? NULL,
            MESSAGE => $resPass[MESSAGE] ?? NULL,
        ];

        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data[MESSAGE])) {
                $_SESSION[MESSAGE] = $data[MESSAGE];
                $_SESSION[SUCCESS] = $data[SUCCESS];
            }
            redirect($data[REDIRECT_TO]);
        };

        $this->switchResponse($dataOut, (!$resPass[SUCCESS] && $resPass[GENERATE_TOKEN]), $funcDefault, CSRF_UPDATE_PASS);
    }

    /* function to reset counters of user lock */
    public function lockCountersReset() {
        /* redirect */
        $this->sendFailIfCanNotUnlockUser();

        /* require double login */
        $this->handlerDoubleLogin();

        /* get tokens ad user id */
        $tokens = $this->getPostSessionTokens(CSRF_LOCK_USER_RESET);
        $id = $_POST[USER_ID];

        /* get verifier instance, and check reset wrong user locks request */
        $resReset = UMSVerifier::getInstance($this->lang[MESSAGE], $this->conn)->verifyLockCounterReset($id, $tokens);
        if ($resReset[SUCCESS]) {
            /* if success init user model, and reset count user locks */
            $user = new User($this->conn);
            /* reset user locks and set results */
            $resReset = array_merge($resReset, $user->lockUserReset($id));
            $resReset[MESSAGE] = $resReset[SUCCESS] ? $this->lang[MESSAGE][LOCK_USER_RESET][SUCCESS] : $this->lang[MESSAGE][LOCK_USER_RESET][FAIL];
        }

        /* set redirect to */
        $redirectTo = Router::getRoute('app\controllers\UMSTablesController', 'showRow');
        $redirectTo = str_replace(':table', USER_LOCK_TABLE, $redirectTo);
        $redirectTo = str_replace(':id', $id, $redirectTo);

        /* result data */
        $dataOut = [
            REDIRECT_TO => $redirectTo,
            SUCCESS => $resReset[SUCCESS],
            MESSAGE => $resReset[MESSAGE] ?? NULL,
        ];
        
        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data[MESSAGE])) {
                $_SESSION[MESSAGE] = $data[MESSAGE];
                $_SESSION[SUCCESS] = $data[SUCCESS];
            }
            redirect($data[REDIRECT_TO]);
        };
        
        $this->switchResponse($dataOut, (!$resReset[SUCCESS] && $resReset[GENERATE_TOKEN]), $funcDefault, CSRF_LOCK_USER_RESET);
    }

    /* function to add a new user */
    public function newUser() {
        /* redirects */
        $this->sendFailIfCanNotCreateUser();
        /* set redirect */
        $redirectTo = Router::getRoute('app\controllers\UMSActionsController', 'switchShowAction');
        $redirectTo = str_replace(':table', USERS_TABLE, $redirectTo);
        $redirectTo = str_replace(':action', 'new', $redirectTo);
        $this->redirectIfNotXMLHTTPRequest($redirectTo);

        /* get tokens and post data */
        $tokens = $this->getPostSessionTokens(CSRF_NEW_USER);
        $email = $_POST[EMAIL] ?? '';
        $username = $_POST[USERNAME] ?? '';
        $name = $_POST[NAME] ?? '';
        $pass = $_POST[PASSWORD] ?? '';
        $cpass =$_POST[CONFIRM_PASS] ?? '';
        $roletype = $this->isAdminUser() ? ($_POST[ROLE_ID_FRGN] ?? DEFAULT_ROLE) : DEFAULT_ROLE;
        $pending = isset($_POST[PENDING]);
        
        /* decrypt passwords */
        $pass = $this->decryptData($pass);
        $cpass = $this->decryptData($cpass);

        
        /* get verifier instance, and check new user request */
        $resSignup = UMSVerifier::getInstance($this->lang[MESSAGE], $this->conn)->verifyNewUser($name, $email, $username, $pass, $cpass, $roletype, $tokens);
        /* if success */
        if($resSignup[SUCCESS]) {
            /* create data to save user */
            $usrData = [
                NAME => $name,
                USERNAME => $username,
                EMAIL => $email,
                PASSWORD => $pass,
                ROLE_ID_FRGN => $roletype,
                ENABLED => TRUE,
                EXPIRE_DATETIME => getExpireDatetime(ENABLER_LINK_EXPIRE_TIME)
            ];

            $redirectTo = Router::getRoute('app\controllers\UMSTablesController', 'showRow');

            /* if pending */
            if ($pending) {
                /* init pending model and save user */
                $pendModel = new PendingUser($this->conn);
                $resUser = $pendModel->savePendingUser($usrData);
                /* send enabler email */
                $this->sendEnablerEmail($email, $resUser[TOKEN]);
                $redirectTo = str_replace(':table', PENDING_USERS_TABLE, $redirectTo);
                $redirectTo = str_replace(':id', $resUser[USER_ID], $redirectTo);
            } else {
                /* init user model and save user */
                $user = new User($this->conn);
                $resUser = $user->saveUser($usrData);
                $redirectTo = str_replace(':table', USERS_TABLE, $redirectTo);
                $redirectTo = str_replace(':id', $resUser[USER_ID], $redirectTo);
            }
            /* set result */
            $resSignup = array_merge($resSignup, $resUser);
            $resSignup[MESSAGE] = $resSignup[SUCCESS] ? $this->lang[MESSAGE][SAVE_USER][SUCCESS] : $this->lang[MESSAGE][SAVE_USER][FAIL];
        }
        
        /* result data */
        $dataOut = [
            REDIRECT_TO => $redirectTo,
            SUCCESS => $resSignup[SUCCESS],
            ERROR => $resSignup[ERROR] ?? NULL,
            MESSAGE => $resSignup[MESSAGE] ?? NULL
        ];
        
        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data[MESSAGE])) {
                $_SESSION[MESSAGE] = $data[MESSAGE];
                $_SESSION[SUCCESS] = $data[SUCCESS];
            }
            redirect($data[REDIRECT_TO]);
        };

        $this->switchResponse($dataOut, (!$resSignup[SUCCESS] && $resSignup[GENERATE_TOKEN]), $funcDefault, CSRF_NEW_USER);
    }

    /* function to delete user */
    public function deleteUser() {
        /* redirect */
        $this->sendFailIfCanNotDeleteUser();

        /* require double login */
        $this->handlerDoubleLogin();

        /* get tokens and user id */
        $tokens = $this->getPostSessionTokens(CSRF_DELETE_USER);
        $id = $_POST[USER_ID] ?? '';

        /* set redirect to */
        $redirectTo = Router::getRoute('app\controllers\UMSTablesController', 'showRow');
        $redirectTo = str_replace(':table', USERS_TABLE, $redirectTo);
        $redirectTo = str_replace(':id', $id, $redirectTo);

        /* get verifier instance, and check delete user request */
        $resDelete = Verifier::getInstance($this->lang[MESSAGE], $this->conn)->verifyDelete($id, $tokens);
        if ($resDelete[SUCCESS]) {
            /* init user model and delete user */
            $user = new User($this->conn);
            $resDelete = array_merge($resDelete, $user->deleteUser($id));
            /* if delete success, then save delete user */
            if ($resDelete[SUCCESS]) {
                $resDelete[MESSAGE] = $this->lang[MESSAGE][USER_DELETE][SUCCESS];
                /* init deleted model and save deleted user */
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
                $redirectTo = Router::getRoute('app\controllers\UMSTablesController', 'showRow');
                $redirectTo = str_replace(':table', DELETED_USER_TABLE, $redirectTo);
                $redirectTo = str_replace(':id', $id, $redirectTo);
            /* else set fail message */
            } else $resDelete[MESSAGE] = $this->lang[MESSAGE][USER_DELETE][FAIL];
        }

        /* result data */
        $dataOut = [
            REDIRECT_TO => $redirectTo,
            SUCCESS => $resDelete[SUCCESS],
            ERROR => $resDelete[ERROR] ?? NULL,
            MESSAGE => $resDelete[MESSAGE] ?? NULL
        ];

        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data[MESSAGE])) {
                $_SESSION[MESSAGE] = $data[MESSAGE];
                $_SESSION[SUCCESS] = $data[SUCCESS];
            }
            redirect($data[REDIRECT_TO]);
        };

        $this->switchResponse($dataOut, (!$resDelete[SUCCESS] && $resDelete[GENERATE_TOKEN]), $funcDefault, CSRF_DELETE_USER);
    }

    /* function to restore a deleted user */
    public function restoreUser() {
        /* redirect */
        $this->sendFailIfCanNotRestoreUser();

        /* require double login */
        $this->handlerDoubleLogin();

        /* get tokens ad user id */
        $tokens = $this->getPostSessionTokens(CSRF_RESTORE_USER);
        $id = $_POST[USER_ID] ?? '';

        /* set redirect to */
        $redirectTo = Router::getRoute('app\controllers\UMSTablesController', 'showRow');
        $redirectTo = str_replace(':table', DELETED_USER_TABLE, $redirectTo);
        $redirectTo = str_replace(':id', $id, $redirectTo);

        /* get verifier instance, and check reset wrong user locks request */
        $resRestore = UMSVerifier::getInstance($this->lang[MESSAGE], $this->conn)->verifyRestoreUser($id, $tokens);
        if ($resRestore[SUCCESS]) {
            /* if success init user model, and set user data */
            $userModel = new User($this->conn);
            $password = getSecureRandomString(8);
            $userData = [
                NAME => $resRestore[USER]->{NAME},
                USERNAME => $resRestore[USER]->{USERNAME},
                EMAIL => $resRestore[USER]->{EMAIL},
                ROLE_ID_FRGN => $resRestore[USER]->{ROLE_ID_FRGN},
                REGISTRATION_DATETIME => $resRestore[USER]->{REGISTRATION_DATETIME},
                PASSWORD => $password,
                ENABLED => TRUE
            ];

            /* restore usere and merge results */
            $resRestore = array_merge($resRestore, $userModel->saveUserSetRegistrationDatetime($userData));
            /* if restore success */
            if ($resRestore[SUCCESS]) {
                $resRestore = array_merge($resRestore, $userModel->changeUserId($resRestore[USER_ID], $resRestore[USER]->{USER_ID}));
                /* if change id success */
                if ($resRestore[SUCCESS]) {
                    /* set success messege */
                    $resRestore[MESSAGE] = $this->lang[MESSAGE][RESTORE_USER][SUCCESS];
                    /* send email with new random password */
                    $this->sendEmailNewRandomPassword($resRestore[USER]->{EMAIL}, $password);
                    /* set redirect to user */
                    $redirectTo = Router::getRoute('app\controllers\UMSTablesController', 'showRow');
                    $redirectTo = str_replace(':table', USERS_TABLE, $redirectTo);
                    $redirectTo = str_replace(':id', $id, $redirectTo);
                    /* remove from delete users table */
                    $delUserModel = new DeletedUser($this->conn);
                    $delUserModel->removeDeleteUser($resRestore[USER]->{USER_ID});                    
                /* else delete created user */
                } else {
                    /* set fail messege */
                    $resRestore[MESSAGE] = $this->lang[MESSAGE][RESTORE_USER][FAIL];
                    $userModel->deleteUser($resRestore[USER_ID]);
                }
            }
        }

        /* result data */
        $dataOut = [
            REDIRECT_TO => $redirectTo,
            SUCCESS => $resRestore[SUCCESS],
            MESSAGE => $resRestore[MESSAGE] ?? NULL,
        ];

        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data[MESSAGE])) {
                $_SESSION[MESSAGE] = $data[MESSAGE];
                $_SESSION[SUCCESS] = $data[SUCCESS];
            }
            redirect($data[REDIRECT_TO]);
        };

        $this->switchResponse($dataOut, (!$resRestore[SUCCESS] && $resRestore[GENERATE_TOKEN]), $funcDefault, CSRF_RESTORE_USER);
    }

    /* ##### EMAIL RESENDERS ##### */

    /* function to resend enabler email */
    public function resendEnablerEmail() {
        /* redirect */
        $this->sendFailIfCanNotSendEmail();

        /* get tokens ad session id */
        $tokens = $this->getPostSessionTokens(CSRF_RESEND_ENABLER_EMAIL);
        $id = $_POST[PENDING_EMAIL_ID] ?? '';

        /* get verifier instance, and check remove session request */
        $resResend = UMSVerifier::getInstance($this->lang[MESSAGE], $this->conn)->verifyResendEnablerEmail($id, $tokens);
        if ($resResend[SUCCESS]) {
            /* if success resend email and if success set new expire date time */
            if (($resResend[SUCCESS] = $this->sendEnablerEmail($resResend[PENDING]->{NEW_EMAIL}, $resResend[PENDING]->{ENABLER_TOKEN}, 'ENABLE YOUR EMAIL', TRUE))) {
                /* init pending model and set new expire datetime */
                $penMailModel = new PendingEmail($this->conn);
                $expDatetime = getExpireDatetime(ENABLER_LINK_EXPIRE_TIME);
                $penMailModel->updateExpireDatetime($resResend[PENDING]->{ENABLER_TOKEN}, $expDatetime);
                /* set success message */
                $resResend[MESSAGE] = $this->lang[MESSAGE][SEND_EMAIL][SUCCESS].$resResend[PENDING]->{NEW_EMAIL};
            /* set fail message */
            } else $resResend[MESSAGE] = $this->lang[MESSAGE][SEND_EMAIL][FAIL];
        }

        /* set redirect to */
        $redirectTo = Router::getRoute('app\controllers\UMSTablesController', 'showRow');
        $redirectTo = str_replace(':table', PENDING_EMAILS_TABLE, $redirectTo);
        $redirectTo = str_replace(':id', $id, $redirectTo);

        /* result data */
        $dataOut = [
            REDIRECT_TO => $redirectTo,
            SUCCESS => $resResend[SUCCESS],
            MESSAGE => $resResend[MESSAGE] ?? NULL,
        ];

        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data[MESSAGE])) {
                $_SESSION[MESSAGE] = $data[MESSAGE];
                $_SESSION[SUCCESS] = $data[SUCCESS];
            }
            redirect($data[REDIRECT_TO]);
        };

        $this->switchResponse($dataOut, (!$resResend[SUCCESS] && $resResend[GENERATE_TOKEN]), $funcDefault, CSRF_RESEND_ENABLER_EMAIL);
    }

    /* function to resend enabler email */
    public function resendEnablerAccount() {
        /* redirect */
        $this->sendFailIfCanNotSendEmail();
        
        /* get tokens ad session id */
        $tokens = $this->getPostSessionTokens(CSRF_RESEND_ENABLER_ACC);
        $id = $_POST[PENDING_USER_ID] ?? '';

        /* get verifier instance, and check remove session request */
        $resResend = UMSVerifier::getInstance($this->lang[MESSAGE], $this->conn)->verifyResendEnablerAccount($id, $tokens);
        if ($resResend[SUCCESS]) {
            /* if success resend email and if success set new expire date time */
            if (($resResend[SUCCESS] = $this->sendEnablerEmail($resResend[PENDING]->{EMAIL}, $resResend[PENDING]->{ENABLER_TOKEN}))) {
                /* init pending model and set new expire datetime */
                $pendUserModel = new PendingUser($this->conn);
                $expDatetime = getExpireDatetime(ENABLER_LINK_EXPIRE_TIME);
                $pendUserModel->updateExpireDatetime($resResend[PENDING]->{ENABLER_TOKEN}, $expDatetime);
                /* set success message */
                $resResend[MESSAGE] = $this->lang[MESSAGE][SEND_EMAIL][SUCCESS].$resResend[PENDING]->{EMAIL};
                /* set fail message */
            } else $resResend[MESSAGE] = $this->lang[MESSAGE][SEND_EMAIL][FAIL];
        }

        /* set redirect to */
        $redirectTo = Router::getRoute('app\controllers\UMSTablesController', 'showRow');
        $redirectTo = str_replace(':table', PENDING_USERS_TABLE, $redirectTo);
        $redirectTo = str_replace(':id', $id, $redirectTo);

        /* result data */
        $dataOut = [
            REDIRECT_TO => $redirectTo,
            SUCCESS => $resResend[SUCCESS],
            MESSAGE => $resResend[MESSAGE] ?? NULL,
        ];
        
        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data[MESSAGE])) {
                $_SESSION[MESSAGE] = $data[MESSAGE];
                $_SESSION[SUCCESS] = $data[SUCCESS];
            }
            redirect($data[REDIRECT_TO]);
        };
        
        $this->switchResponse($dataOut, (!$resResend[SUCCESS] && $resResend[GENERATE_TOKEN]), $funcDefault, CSRF_RESEND_ENABLER_ACC);
    }

    /* function to resend password reset email */
    public function resendPasswordReset() {
        /* redirect */
        $this->sendFailIfCanNotSendEmail();
        
        /* get tokens ad session id */
        $tokens = $this->getPostSessionTokens(CSRF_RESEND_PASS_RES_REQ);
        $id = $_POST[PASSWORD_RESET_REQ_ID] ?? '';

        /* get verifier instance, and check remove session request */
        $resResend = UMSVerifier::getInstance($this->lang[MESSAGE], $this->conn)->verifyResendPassRes($id, $tokens);
        if ($resResend[SUCCESS]) {
            /* if success resend email and if success set new expire date time */
            if (($resResend[SUCCESS] = $this->sendEmailResetPassword($resResend[REQUEST]->{EMAIL}, $resResend[REQUEST]->{PASSWORD_RESET_TOKEN}))) {
                /* init pending model and set new expire datetime */
                $passResReqModel = new PasswordResetRequest($this->conn);
                $expDatetime = getExpireDatetime(PASS_RESET_EXPIRE_TIME);
                $passResReqModel->updateExpireDatetime($resResend[REQUEST]->{PASSWORD_RESET_TOKEN}, $expDatetime);
                /* set success message */
                $resResend[MESSAGE] = $this->lang[MESSAGE][SEND_EMAIL][SUCCESS].$resResend[REQUEST]->{EMAIL};
                /* set fail message */
            } else $resResend[MESSAGE] = $this->lang[MESSAGE][SEND_EMAIL][FAIL];
        }

        /* set redirect to */
        $redirectTo = Router::getRoute('app\controllers\UMSTablesController', 'showRow');
        $redirectTo = str_replace(':table', PASSWORD_RESET_REQ_TABLE, $redirectTo);
        $redirectTo = str_replace(':id', $id, $redirectTo);

        /* result data */
        $dataOut = [
            REDIRECT_TO => $redirectTo,
            SUCCESS => $resResend[SUCCESS],
            MESSAGE => $resResend[MESSAGE] ?? NULL,
        ];
        
        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data[MESSAGE])) {
                $_SESSION[MESSAGE] = $data[MESSAGE];
                $_SESSION[SUCCESS] = $data[SUCCESS];
            }
            redirect($data[REDIRECT_TO]);
        };
        
        $this->switchResponse($dataOut, (!$resResend[SUCCESS] && $resResend[GENERATE_TOKEN]), $funcDefault, CSRF_RESEND_PASS_RES_REQ);
    }

    /* ##### INVALIDATORS ##### */

    /* function to invalidate pending email */
    public function invalidatePendingEmail() {
        /* redirect */
        $this->sendFailIfCanNotRemoveEnablerToken();

        /* require double login */
        $this->handlerDoubleLogin();

        /* get tokens ad session id */
        $tokens = $this->getPostSessionTokens(CSRF_INVALIDATE_PENDING_EMAIL);
        $id = $_POST[PENDING_EMAIL_ID] ?? '';

        /* get verifier instance, and check remove session request */
        $resRemove = UMSVerifier::getInstance($this->lang[MESSAGE], $this->conn)->verifyInvalidatePendingEmail($id, $tokens);
        if ($resRemove[SUCCESS]) {
            /* if success init pending model and remove token */
            $pendMailModel = new PendingEmail($this->conn);
            if (($resRemove[SUCCESS] = $pendMailModel->removeEmailEnablerTokenById($id))) $resRemove[MESSAGE] = $this->lang[MESSAGE][INVALIDATE_PENDING_EMAIL][SUCCESS];
            else $resRemove[MESSAGE] = $this->lang[MESSAGE][INVALIDATE_PENDING_EMAIL][FAIL];
        }

        /* set redirect to */
        $redirectTo = Router::getRoute('app\controllers\UMSTablesController', 'showRow');
        $redirectTo = str_replace(':table', PENDING_EMAILS_TABLE, $redirectTo);
        $redirectTo = str_replace(':id', $id, $redirectTo);

        /* result data */
        $dataOut = [
            REDIRECT_TO => $redirectTo,
            SUCCESS => $resRemove[SUCCESS],
            MESSAGE => $resRemove[MESSAGE] ?? NULL,
        ];
        
        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data[MESSAGE])) {
                $_SESSION[MESSAGE] = $data[MESSAGE];
                $_SESSION[SUCCESS] = $data[SUCCESS];
            }
            redirect($data[REDIRECT_TO]);
        };
        
        $this->switchResponse($dataOut, (!$resRemove[SUCCESS] && $resRemove[GENERATE_TOKEN]), $funcDefault, CSRF_INVALIDATE_PENDING_EMAIL);
    }

    /* function to invalidate pending email */
    public function invalidatePendingUser() {
        /* redirect */
        $this->sendFailIfCanNotRemoveEnablerToken();

        /* require double login */
        $this->handlerDoubleLogin();

        /* get tokens ad session id */
        $tokens = $this->getPostSessionTokens(CSRF_INVALIDATE_PENDING_USER);
        $id = $_POST[PENDING_USER_ID] ?? '';

        /* get verifier instance, and check remove session request */
        $resRemove = UMSVerifier::getInstance($this->lang[MESSAGE], $this->conn)->verifyInvalidatePendingUser($id, $tokens);
        if ($resRemove[SUCCESS]) {
            /* if success init pending model and remove token */
            $pendUserModel = new PendingUser($this->conn);
            if (($resRemove[SUCCESS] = $pendUserModel->removeAccountEnablerTokenById($id))) $resRemove[MESSAGE] = $this->lang[MESSAGE][INVALIDATE_PENDING_USER][SUCCESS];
            else $resRemove[MESSAGE] = $this->lang[MESSAGE][INVALIDATE_PENDING_USER][FAIL];
        }

        /* set redirect to */
        $redirectTo = Router::getRoute('app\controllers\UMSTablesController', 'showRow');
        $redirectTo = str_replace(':table', PENDING_USERS_TABLE, $redirectTo);
        $redirectTo = str_replace(':id', $id, $redirectTo);

        /* result data */
        $dataOut = [
            REDIRECT_TO => $redirectTo,
            SUCCESS => $resRemove[SUCCESS],
            MESSAGE => $resRemove[MESSAGE] ?? NULL,
        ];
        
        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data[MESSAGE])) {
                $_SESSION[MESSAGE] = $data[MESSAGE];
                $_SESSION[SUCCESS] = $data[SUCCESS];
            }
            redirect($data[REDIRECT_TO]);
        };
        
        $this->switchResponse($dataOut, (!$resRemove[SUCCESS] && $resRemove[GENERATE_TOKEN]), $funcDefault, CSRF_INVALIDATE_PENDING_USER);
    }

    
    /* function to invalidate session */
    public function invalidateSession() {
        /* redirect */
        $this->sendFailIfCanNotRemoveSession();

        /* require double login */
        $this->handlerDoubleLogin();
        
        /* get tokens ad session id */
        $tokens = $this->getPostSessionTokens(CSRF_INVALIDATE_SESSION);
        $id = $_POST[SESSION_ID] ?? '';

        /* get verifier instance, and check remove session request */
        $resRemove = UMSVerifier::getInstance($this->lang[MESSAGE], $this->conn)->verifyInvalidateSession($id, $tokens);
        if ($resRemove[SUCCESS]) {
            /* if success init session model and remove session */
            $sessionModel = new Session($this->conn);
            if (($resRemove[SUCCESS] = $sessionModel->removeLoginSession($id))) $resRemove[MESSAGE] = $this->lang[MESSAGE][REMOVE_SESSION][SUCCESS];
            else $resRemove[MESSAGE] = $this->lang[MESSAGE][REMOVE_SESSION][FAIL];
        }

        /* set redirect to */
        $redirectTo = Router::getRoute('app\controllers\UMSTablesController', 'showRow');
        $redirectTo = str_replace(':table', SESSIONS_TABLE, $redirectTo);
        $redirectTo = str_replace(':id', $id, $redirectTo);

        /* result data */
        $dataOut = [
            REDIRECT_TO => $redirectTo,
            SUCCESS => $resRemove[SUCCESS],
            MESSAGE => $resRemove[MESSAGE] ?? NULL,
        ];
        
        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data[MESSAGE])) {
                $_SESSION[MESSAGE] = $data[MESSAGE];
                $_SESSION[SUCCESS] = $data[SUCCESS];
            }
            redirect($data[REDIRECT_TO]);
        };
        
        $this->switchResponse($dataOut, (!$resRemove[SUCCESS] && $resRemove[GENERATE_TOKEN]), $funcDefault, CSRF_INVALIDATE_SESSION);
    }

    /* function to invalidate pending email */
    public function invalidatePasswordResetRequest() {
        /* redirect */
        $this->sendFailIfCanNotRemoveEnablerToken();

        /* require double login */
        $this->handlerDoubleLogin();
        
        /* get tokens ad session id */
        $tokens = $this->getPostSessionTokens(CSRF_INVALIDATE_PASS_RES_REQ);
        $id = $_POST[PASSWORD_RESET_REQ_ID] ?? '';

        /* get verifier instance, and check remove session request */
        $resRemove = UMSVerifier::getInstance($this->lang[MESSAGE], $this->conn)->verifyInvalidatePasswordResetReq($id, $tokens);
        if ($resRemove[SUCCESS]) {
            /* if success init password reset request model and remove token */
            $passResetModel = new PasswordResetRequest($this->conn);
            if (($resRemove[SUCCESS] = $passResetModel->removePasswordResetReqTokenById($id))) $resRemove[MESSAGE] = $this->lang[MESSAGE][INVALIDATE_PASS_RES_REQ][SUCCESS];
            else $resRemove[MESSAGE] = $this->lang[MESSAGE][INVALIDATE_PASS_RES_REQ][FAIL];
        }

        /* set redirect to */
        $redirectTo = Router::getRoute('app\controllers\UMSTablesController', 'showRow');
        $redirectTo = str_replace(':table', PASSWORD_RESET_REQ_TABLE, $redirectTo);
        $redirectTo = str_replace(':id', $id, $redirectTo);

        /* result data */
        $dataOut = [
            REDIRECT_TO => $redirectTo,
            SUCCESS => $resRemove[SUCCESS],
            MESSAGE => $resRemove[MESSAGE] ?? NULL,
        ];
        
        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data[MESSAGE])) {
                $_SESSION[MESSAGE] = $data[MESSAGE];
                $_SESSION[SUCCESS] = $data[SUCCESS];
            }
            redirect($data[REDIRECT_TO]);
        };
        
        $this->switchResponse($dataOut, (!$resRemove[SUCCESS] && $resRemove[GENERATE_TOKEN]), $funcDefault, CSRF_INVALIDATE_PASS_RES_REQ);
    }
    /* ##################################### */
    /* PRIVATE FUNCTIONS */
    /* ##################################### */

    /* ######### SWITCHER ######### */

    /* function to switch show action for user table */
    private function switchShowUserAction(string $action, string $id='') {
        switch ($action) {
            case 'new':
                $this->showNewUser();
                break;
            case 'update':
                $this->showUserUpdate($id);
                break;
            case 'password_update':
                $this->showPasswordUpdate($id);
                break;
            default:
                $this->switchFailResponse($this->lang[MESSAGE][GENERIC][INVALID_ACTION]);
                break;
        }
    }

    /* function to switch action for user table */
    private function switchUserAction(string $action) {
        switch ($action) {
            case 'new':
                $this->newUser();
                break;
            case 'update':
                $this->userUpdate();
                break;
            case 'delete':
                $this->deleteUser();
                break;
            case 'password_update':
                $this->passwordUpdate();
                break;
            default:
                $this->switchFailResponse($this->lang[MESSAGE][GENERIC][INVALID_ACTION]);
                break;
        }
    }

    /* function to switch action for user table */
    private function switchUserLockAction(string $action) {
        switch ($action) {
            case 'reset':
                $this->lockCountersReset();
                break;
            default:
                $this->switchFailResponse($this->lang[MESSAGE][GENERIC][INVALID_ACTION]);
                break;
        }
    }

    /* function to switch action for deleted user table */
    private function switchDeletedUserAction(string $action) {
        switch ($action) {
            case 'restore':
                $this->restoreUser();
                break;
            default:
                $this->switchFailResponse($this->lang[MESSAGE][GENERIC][INVALID_ACTION]);
                break;
        }
    }
    /* function to switch action for pending user table */
    private function switchPendingUserAction(string $action) {
        switch ($action) {
            case 'resend':
                $this->resendEnablerAccount();
                break;
            case 'invalidate':
                $this->invalidatePendingUser();
                break;
            default:
                $this->switchFailResponse($this->lang[MESSAGE][GENERIC][INVALID_ACTION]);
                break;
        }
    }

    /* function to switch action for pending email table */
    private function switchPendingEmailAction(string $action) {
        switch ($action) {
            case 'resend':
                $this->resendEnablerEmail();
                break;
            case 'invalidate':
                $this->invalidatePendingEmail();
                break;
            default:
                $this->switchFailResponse($this->lang[MESSAGE][GENERIC][INVALID_ACTION]);
                break;
        }
    }

    /* function to switch action for passwpord reset request table */
    private function switchPasResReqAction(string $action) {
        switch ($action) {
            case 'resend':
                $this->resendPasswordReset();
                break;
            case 'invalidate':
                $this->invalidatePasswordResetRequest();
                break;
            default:
                $this->switchFailResponse($this->lang[MESSAGE][GENERIC][INVALID_ACTION]);
                break;
        }
    }

    /* function to switch action for session table */
    private function switchSessionAction(string $action) {
        switch ($action) {
            case 'invalidate':
                $this->invalidateSession();
                break;
            default:
                $this->switchFailResponse($this->lang[MESSAGE][GENERIC][INVALID_ACTION]);
                break;
        }
    }

    /* ######### REDIRECTS ######### */

    /* function to redirect if user can not change password */
    private function sendFailIfCanNotChangePassword() {
        if (!$this->canChangePassword()) $this->switchFailResponse();
    }

    /* function to redirect if user can not unlock users */
    private function sendFailIfCanNotUnlockUser() {
        if (!$this->canUnlockUser()) $this->switchFailResponse();
    }

    /* function to redirect if user can not restore users */
    private function sendFailIfCanNotRestoreUser() {
        if (!$this->canRestoreUser()) $this->switchFailResponse();
    }

    /* function to redirect if user can not remove session */
    private function sendFailIfCanNotRemoveSession() {
        if (!$this->canRemoveSession()) $this->switchFailResponse();
    }

    /* function to redirect if user can not remove enabler token */
    private function sendFailIfCanNotRemoveEnablerToken() {
        if (!$this->canRemoveEnablerToken()) $this->switchFailResponse();
    }
}

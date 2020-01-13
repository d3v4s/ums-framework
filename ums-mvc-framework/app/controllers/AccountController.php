<?php
namespace app\controllers;

use app\controllers\verifiers\Verifier;
use app\controllers\data\AccountDataFactory;
use app\controllers\verifiers\AccountVerifier;
use app\models\PendingEmail;
use app\models\DeletedUser;
use app\models\User;
use \PDO;

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

//     /* function to view delete account page */
//     public function showDeleteAccount() {
//         /* redirect */
//         $this->redirectOrFailIfNotLogin();

//         /* generate token and show page */
//         $this->content = view(getPath('user','user-delete'), [TOKEN => generateToken(CSRF_DELETE_ACCOUNT)]);
//     }

    /* function to show the account settings */
    public function showAccountSettings() {
        /* redirect */
        $this->redirectOrFailIfNotLogin();
        
        /* add javascript sources */
        array_push($this->jsSrcs,
            [SOURCE => '/js/utils/validate.js'],
            [SOURCE => '/js/utils/user/user-settings.js']
        );
        
        /* get data from data factory */
        $data = AccountDataFactory::getInstance($this->conn)->getUserData($this->loginSession->{USER_ID});
        /* if wait email confir, then add javascript sorce for new email settings */
        if ($data[WAIT_EMAIL_CONFIRM]) $this->jsSrcs[] = [SOURCE => '/js/utils/user/user-new-email-settings.js'];
        
        /* show user settings page */
        $this->content = view('account/settings', $data);
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
            [SOURCE => '/js/utils/user/change-pass.js']
        );

        /* generate token and show change password page */
        $this->content = view('account/change-pass', [TOKEN => generateToken(CSRF_CHANGE_PASS)]);
    }

    /* ########## ACTION FUNCTIONS ########## */

    /* fuction to delete the account */
    public function deleteAccount() {
        /* redirect */
        $this->redirectOrFailIfNotLogin();

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
            $delModel = new DeletedUser($this->conn);
            $delModel->saveDeletedUser($resDelete[USER]);
            /* set result */
            $resDelete[MESSAGE] = $resUser[MESSAGE];
            $resDelete[SUCCESS] = $resUser[SUCCESS];
            /* if success reset session */
            if ($resUser[SUCCESS]) $this->resetLoginSession();
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
//         $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
//         switch ($header) {
//             case 'XMLHTTPREQUEST':
//                 $resJSON
//                 if () $resJSON['ntk'] = generateToken('csrfUserSettings');
//                 header("Content-Type: application/json");
//                 header("X-Content-Type-Options: nosniff");
//                 echo json_encode($resJSON);
//                 exit;
//             default:
                
//                 break;
//         };
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
                $pendMailModel = new PendingEmail($this->conn);
                $expireDatetime = getExpireDatetime(ENABLER_LINK_EXPIRE_TIME);
                $resPend = $pendMailModel->newPendingEmail($id, $email, $expireDatetime);
                /* if success send email, else set fail */
                $resUpdate[SUCCESS] = $resPend[SUCCESS] && $this->sendEnablerEmail($email, $resPend[TOKEN], 'ENABLE YOUR EMAIL', TRUE);
            }
            if ($resUpdate[SUCCESS]) {
                /* init user model */
                $userModel = new User($this->conn);
                /* update user */
                $resUser = $userModel->updateUser($id, $userData);
//                 if ($resUser[SUCCESS]) {
//                     /* if success get user id and recreate the login session */
//                     $usr = $userModel->getUser($id);
//                     $this->createSessionLogin($usr);
//                 }
                /* set result */
                $resUpdate[MESSAGE] = $resUser[MESSAGE];
                $resUpdate[SUCCESS] = $resUser[SUCCESS];
                
            }
        }

        /* result data */
        $dataOut = [
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
            redirect('/'.ACCOUNT_SETTINGS_ROUTE);
        };

        $this->switchResponse($dataOut, (!$resUpdate[SUCCESS] && $resUpdate[GENERATE_TOKEN]), $funcDefault, CSRF_UPDATE_ACCOUNT);
//         $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
//         switch ($header) {
//             case 'XMLHTTPREQUEST':
//                 $resJSON = 
//                 if (!$resUpdate['success']) $resJSON['ntk'] = generateToken('csrfUserSettings');
//                 header("Content-Type: application/json");
//                 header("X-Content-Type-Options: nosniff");
//                 echo json_encode($resJSON);
//                 exit;
//             default:
//                 break;
//         }
    }

    /* function to show password change page */
    public function changePassword() {
        /* redirects */
        $this->redirectOrFailIfNotLogin();
        $this->redirectIfNotXMLHTTPRequest(ACCOUNT_SETTINGS_ROUTE.'/'.UPDATE_PASS_ROUTE);

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
            /* update user password and set result */
            $resUser = $user->updateUserPass($id, $pass);
            $resPass[MESSAGE] = $resUser[MESSAGE];
            $resPass[SUCCESS] = $resUser[SUCCESS];
        /* else set error message */
        } else if ($resPass[WRONG_PASSWORD]) $this->handlerWrongPassword($id);

        /* result data */
        $dataOut = [
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
            $data[SUCCESS] ? redirect('/'.ACCOUNT_SETTINGS_ROUTE) : redirect('/'.ACCOUNT_SETTINGS_ROUTE.'/'.UPDATE_PASS_ROUTE);
        };

        $this->switchResponse($dataOut, (!$resPass[SUCCESS] && $resPass[GENERATE_TOKEN]), $funcDefault);
//         $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
//         switch ($header) {
//             case 'XMLHTTPREQUEST':
//                 $resJSON = 
//                 if (!$resPass['success']) $resJSON['ntk'] = generateToken();
//                 header("Content-Type: application/json");
//                 header("X-Content-Type-Options: nosniff");
//                 echo json_encode($resJSON);
//                 exit;
//             default:

//                 break;
//         }
    }

    /* function to delete new email on pending */
    public function deleteNewEmail() {
        /* redirects */
        $this->redirectOrFailIfNotLogin();
        $this->redirectOrFailIfConfirmEmailNotRequire();

        /* get tokens and user id */
        $tokens = $this->getPostSessionTokens(CSRF_DELETE_NEW_EMAIL);
        $id = $this->loginSession->{USERID};

        /* get verifier instance, and check delete new email request */
        $verifier = AccountVerifier::getInstance($this->conn);
        $resDeleteEmail = $verifier->verifyDeleteNewEmail($id, $tokens);
        /* if success */
        if ($resDeleteEmail[SUCCESS]) {
            /* init pending mail model */
            $pendMail = new PendingEmail($this->conn);
//             $user = new User($this->conn);
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
//         $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
//         switch ($header) {
//             case 'XMLHTTPREQUEST':
//                 $resJSON = 
//                 if (!$resDeleteEmail['success']) $resJSON['ntk'] = generateToken('csrfUserSettings');
//                 header("Content-Type: application/json");
//                 header("X-Content-Type-Options: nosniff");
//                 echo json_encode($resJSON);
//                 exit;
//             default:
                
//                 break;
//         }
    }

    public function resendEmailEnabler() {
        /* redirects */
        $this->redirectIfNotLoggin();
        $this->redirectIfNotEmailConfirmRequire();

        /* get tokens and user id */
        $tokens = $this->getPostSessionTokens(CSRF_RESEND_ENABLER_EMAIL);
        $id = $this->loginSession->{USER_ID};

        /* get verifier instance, and check resend new email validation request */
        $verifier = AccountVerifier::getInstance($this->conn);
        $resResendEmail = $verifier->verifyResendNewEmailValidation($id, $tokens);
        /* if success */
        if ($resResendEmail[SUCCESS]) {
            /* send email validation and set result */
            $resResendEmail[SUCCESS] = $this->sendEnablerEmail($resResendEmail[TO], $resResendEmail[TOKEN], 'ENABLE YOUR EMAIL', TRUE);
            $resResendEmail[MESSAGE] = $resResendEmail[SUCCESS] ? 'Email sent successfully' : 'Sending email failed';
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
//         $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
//         switch ($header) {
//             case 'XMLHTTPREQUEST':
//                 $resJSON = 
//                 $resJSON['ntk'] = generateToken('csrfUserSettings');
//                 header("Content-Type: application/json");
//                 header("X-Content-Type-Options: nosniff");
//                 echo json_encode($resJSON);
//                 exit;
//             default:
                
//                 break;
//         }
    }
} 
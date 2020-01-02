<?php
namespace app\controllers;

use app\models\User;
use \PDO;
use app\controllers\verifiers\Verifier;
use app\controllers\verifiers\UserVerifier;
use app\controllers\data\UserDataFactory;

/**
 * Class controller to manage user requests
 * @author Andrea Serra (DevAS) https://devas.info
 */
class USerController extends Controller {
    public function __construct(PDO $conn, array $appConfig, string $layout = 'default') {
        parent::__construct($conn, $appConfig, $layout);
    }

    /* function to view delete account page */
    public function showDeleteAccount() {
        /* redirect */
        $this->redirectIfNotLoggin();

        /* generate token and show page */
        $this->content = view('user/user-delete', ['token' => generateToken('csrfUserSettings')]);
    }

    /* fuction to delete the account */
    public function deleteAccount() {
        /* redirect */
        $this->redirectIfNotLoggin();

        /* get tokens and user id */
        $tokens = $this->getPostSessionTokens('XS_TKN', 'csrfUserSettings');
        $id = getUserLoggedID();

        /* get verifier instance, and check delete account request */
        $verifier = Verifier::getInstance($this->appConfig, $this->conn);
        $resDelete = $verifier->verifyDelete($id, $tokens);
        /* if success */
        if($resDelete['success']) {
            /* init user model and delete user */
            $user = new User($this->conn, $this->appConfig);
            $resUser = $user->deleteUser($id);
            /* set result */
            $resDelete['message'] = $resUser['message'];
            $resDelete['success'] = $resUser['success'];
            /* if success reset session */
            if ($resUser['success']) $this->resetSession();
        }

        /* result data */
        $dataOut = [
            'success' => $resDelete['success'],
            'message' => $resDelete['message'] ?? NULL,
            'error' => $resDelete['error'] ?? NULL
        ];

        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data['message'])) {
                $_SESSION['message'] = $data['message'];
                $_SESSION['success'] = $data['success'];
            }
            redirect("/");
        };

        $this->switchResponse($dataOut, !$resDelete['success'], $funcDefault, 'csrfUserSettings');
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


    public function showUserSettings() {
        /* redirect */
        $this->redirectIfNotLoggin();

        /* add javascript sources */
        array_push($this->jsSrcs,
            ['src' => '/js/utils/validate.js'],
            ['src' => '/js/utils/user/user-settings.js']
        );

        /* get data from data factory */
        $data = UserDataFactory::getInstance($this->appConfig)->getUserData($this->tokenLogout);
        /* if new email confirm is require, then add javascript sorce for new email settings */
        if ($data['confirmNewEmail']) $this->jsSrcs[] = ['src' => '/js/utils/user/user-new-email-settings.js'];

        /* show user settings page */
        $this->content = view('user/user-settings', $data);
    }

    public function updateUser() {
        /* redirect */
        $this->redirectIfNotLoggin();

        /* get tokens and post data */
        $tokens = $this->getPostSessionTokens('XS_TKN', 'csrfUserSettings');
        $email = $_POST['email'] ?? '';
        $username = $_POST['username'] ?? '';
        $name = $_POST['name'] ?? '';
        $id = getUserLoggedID();

        /* get verifier instance, and check update user request */
        $verifier = Verifier::getInstance($this->appConfig, $this->conn);
        $resUpdate = $verifier->verifyUpdate($id, $name, $email, $username, $tokens);
        /* if success */
        if($resUpdate['success']) {
            /* init user model and delete user if require */
            $user = new User($this->conn, $this->appConfig);
            if (isset($resUpdate['deleteUser'])) $user->deleteUser($resUpdate['deleteUser']);
            /* create user data */
            $data = compact('email', 'username', 'name');
            /* check if confirm email is require and if user has chaged email */
            $generateTokenConfirmEmail = $this->appConfig['app']['requireConfirmEmail'] && $resUpdate['changeEmail'];
            /* update user */
            $resUser = $user->updateUser($id, $data, $generateTokenConfirmEmail);
            if ($resUser['success']) {
                /* if success get user id and recreate the login session */
                $usr = $user->getUser($id);
                $this->createSessionLogin($usr);
                /* if new email confirm is require, then send the email validator */
                if ($generateTokenConfirmEmail) $this->sendEmailValidation($email, $usr->token_confirm_email, 'ENABLE YOUR EMAIL', TRUE);
            }

            /* set result */
            $resUpdate['message'] = $resUser['message'];
            $resUpdate['success'] = $resUser['success'];
        }

        /* result data */
        $dataOut = [
            'success' => $resUpdate['success'],
            'error' => $resUpdate['error'] ?? NULL,
            'message' => $resUpdate['message'] ?? NULL
        ];

        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data['message'])) {
                $_SESSION['message'] = $data['message'];
                $_SESSION['success'] = $data['success'];
            }
            redirect('/user/settings');
        };

        $this->switchResponse($dataOut, !$resUpdate['success'], $funcDefault, 'csrfUserSettings');
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

    public function showChangePassword() {
        /* redirect */
        $this->redirectIfNotLoggin();

        /* add javascript sources */
        array_push($this->jsSrcs,
            ['src' => '/js/crypt/jsbn.js'],
            ['src' => '/js/crypt/prng4.js'],
            ['src' => '/js/crypt/rng.js'],
            ['src' => '/js/crypt/rsa.js'],
            ['src' => '/js/utils/req-key.js'],
            ['src' => '/js/utils/validate.js'],
            ['src' => '/js/utils/user/change-pass.js']
        );

        /* generate token and show change password page */
        $this->content = view('user/user-change-pass', ['token' => generateToken()]);
    }

    public function changePassword() {
        /* redirects */
        $this->redirectIfNotLoggin();
        $this->redirectIfNotXMLHTTPRequest('/user/settings/pass');

        /* get tokens and post data */
        $tokens = $this->getPostSessionTokens();
        $oldPass = $_POST['old-pass'] ?? '';
        $pass = $_POST['pass'] ?? '';
        $cpass = $_POST['cpass'] ?? 'x';
        $id = getUserLoggedID();

        /* decrypt passwords */
        $oldPass = $this->decryptData($oldPass);
        $pass = $this->decryptData($pass);
        $cpass = $this->decryptData($cpass);

        /* get verifier instance, and check change password request */
        $verifier = UserVerifier::getInstance($this->appConfig, $this->conn);
        $resPass = $verifier->verifyChangePass($id, $oldPass, $pass, $cpass, $tokens);
        /* if success */
        if($resPass['success']) {
            /* init user model and reset wrong passwords lock */
            $user = new User($this->conn, $this->appConfig);
            $user->resetWrongPasswordLock($id);
            /* update user password and set result */
            $resUser = $user->updateUserPass($id, $pass);
            $resPass['message'] = $resUser['message'];
            $resPass['success'] = $resUser['success'];
        /* else set error message */
        } else if ($resPass['wrongPass']) $this->handlerWrongPassword($id);

        /* result data */
        $dataOut = [
            'success' => $resPass['success'],
            'error' => $resPass['error'] ?? NULL,
            'message' => $resPass['message'] ?? NULL
        ];

        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data['message'])) {
                $_SESSION['message'] = $data['message'];
                $_SESSION['success'] = $data['success'];
            }
            $data['success'] ? redirect('/user/settings') : redirect('/user/settings/pass');
        };

        $this->switchResponse($dataOut, !$resPass['success'], $funcDefault);
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

    public function deleteNewEmail() {
        /* redirects */
        $this->redirectIfNotLoggin();
        $this->redirectIfNotEmailConfirmRequire();

        /* get tokens and user id */
        $tokens = $this->getPostSessionTokens('XS_TKN', 'csrfUserSettings');
        $id = getUserLoggedID();

        /* get verifier instance, and check delete new email request */
        $verifier = UserVerifier::getInstance($this->appConfig, $this->conn);
        $resDeleteEmail = $verifier->verifyDeleteNewEmail($id, $tokens);
        /* if success */
        if ($resDeleteEmail['success']) {
            /* init user model */
            $user = new User($this->conn, $this->appConfig);
            /* remove new email with token */
            if ($resDeleteEmail['success'] = $user->removeNewEmailAndToken($id)) {
                /* if success set messagge and recreate the login session */
                $resDeleteEmail['message'] =  'Email successfully deleted';
                $this->createSessionLogin($user->getUser($id));
            /* else set error message */
            } else $resDeleteEmail['message'] = 'Delete email failed';
        }

        /* result data */
        $dataOut = [
            'success' => $resDeleteEmail['success'],
            'error' => $resDeleteEmail['error'] ?? NULL,
            'message' => $resDeleteEmail['message'] ?? NULL
        ];

        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data['message'])) {
                $_SESSION['message'] = $data['message'];
                $_SESSION['success'] = $data['success'];
            }
            redirect('/user/settings');
        };

        $this->switchResponse($dataOut, !$resDeleteEmail['success'], $funcDefault, 'csrfUserSettings');
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

    public function resendNewEmailValidation() {
        /* redirects */
        $this->redirectIfNotLoggin();
        $this->redirectIfNotEmailConfirmRequire();

        /* get tokens and user id */
        $tokens = $this->getPostSessionTokens('XS_TKN', 'csrfUserSettings');
        $id = getUserLoggedID();

        /* get verifier instance, and check resend new email validation request */
        $verifier = UserVerifier::getInstance($this->appConfig, $this->conn);
        $resResendEmail = $verifier->verifyResendNewEmailValidation($id, $tokens);
        /* if success */
        if ($resResendEmail['success']) {
            /* send email validation and set result */
            $resResendEmail['success'] = $this->sendEmailValidation($resResendEmail['email'], $resResendEmail['token'], 'ENABLE YOUR EMAIL', TRUE);
            $resResendEmail['message'] = $resResendEmail['success'] ? 'Email sent successfully' : 'Sending email failed';
        }

        /* result data */
        $dataOut = [
            'success' => $resResendEmail['success'],
            'error' => $resResendEmail['error'] ?? NULL,
            'message' => $resResendEmail['message'] ?? NULL
        ];

        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data['message'])) {
                $_SESSION['message'] = $data['message'];
                $_SESSION['success'] = $data['success'];
            }
            redirect('/user/settings');
        };

        $this->switchResponse($dataOut, TRUE, $funcDefault, 'csrfUserSettings');
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
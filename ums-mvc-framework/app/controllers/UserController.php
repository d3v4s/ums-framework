<?php
namespace app\controllers;

use app\models\User;
use \PDO;
use app\controllers\verifiers\Verifier;
use app\controllers\verifiers\UserVerifier;
use app\controllers\data\UserDataFactory;

class USerController extends Controller {
    public function __construct(PDO $conn, array $appConfig, string $layout = 'default') {
        parent::__construct($conn, $appConfig, $layout);
    }

    public function showDeleteAccount() {
        $this->redirectIfNotLoggin();

        $this->content = view('user/user-delete', ['token' => generateToken('csrfUserSettings')]);
    }

    public function deleteAccount() {
        $this->redirectIfNotLoggin();

        $tokens = $this->getPostSessionTokens('_xf', 'csrfUserSettings');
        $id = getUserLoggedID();

        $verifier = Verifier::getInstance($this->appConfig, $this->conn);
        $resDelete = $verifier->verifyDelete($id, $tokens);
        if($resDelete['success']) {
            $user = new User($this->conn, $this->appConfig);
            $resUser = $user->deleteUser($id);
            $resDelete['message'] = $resUser['message'];
            $resDelete['success'] = $resUser['success'];
            if ($resUser['success']) $this->resetSession();
        }
        $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
        switch ($header) {
            case 'XMLHTTPREQUEST':
                $resJSON = [
                    'success' => $resDelete['success'],
                    'message' => $resDelete['message'] ?? NULL,
                    'error' => $resDelete['error'] ?? NULL
                ];
                if (!$resDelete['success']) $resJSON['ntk'] = generateToken('csrfUserSettings');
                echo json_encode($resJSON);
                exit;
            default:
                if (isset($resDelete['message'])) {
                    $_SESSION['message'] = $resDelete['message'];
                    $_SESSION['success'] = $resDelete['success'];
                }
                redirect("/");
                break;
        };
    }


    public function showUserSettings() {
        $this->redirectIfNotLoggin();

        array_push($this->jsSrcs,
            ['src' => '/js/utils/validate.js'],
            ['src' => '/js/utils/user/user-settings.js']
        );

        $data = UserDataFactory::getInstance($this->appConfig)->getUserData();
        if ($data['confirmNewEmail']) $this->jsSrcs[] = ['/js/utils/user/user-new-email-settings.js'];

        $this->content = view('user/user-settings', $data);
    }

    public function updateUser() {
        $this->redirectIfNotLoggin();

        $tokens = $this->getPostSessionTokens('_xf', 'csrfUserSettings');
        $email = $_POST['email'] ?? '';
        $username = $_POST['username'] ?? '';
        $name = $_POST['name'] ?? '';
        $id = getUserLoggedID();

        $verifier = Verifier::getInstance($this->appConfig, $this->conn);
        $resUpdate = $verifier->verifyUpdate($id, $name, $email, $username, $tokens);
        if($resUpdate['success']) {
            $user = new User($this->conn, $this->appConfig);
            if (isset($resUpdate['deleteUser'])) $user->deleteUser($resUpdate['deleteUser']);
            $data = compact('email', 'username', 'name');
            $generateTokenConfirmEmail = $this->appConfig['app']['requireConfirmEmail'] && $resUpdate['changeEmail'];
            $resUser = $user->updateUser($id, $data, $generateTokenConfirmEmail);
            if ($resUser['success']) {
                $usr = $user->getUser($id);
                $this->createSessionLogin($usr);
                if ($generateTokenConfirmEmail) $this->sendEmailValidation($email, $usr->token_confirm_email, 'ENABLE YOUR EMAIL', TRUE);
            }
            $resUpdate['message'] = $resUser['message'];
            $resUpdate['success'] = $resUser['success'];
        }
        $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
        switch ($header) {
            case 'XMLHTTPREQUEST':
                $resJSON = [
                    'success' => $resUpdate['success'],
                    'error' => $resUpdate['error'] ?? NULL,
                    'message' => $resUpdate['message'] ?? NULL
                ];
                if (!$resUpdate['success']) $resJSON['ntk'] = generateToken('csrfUserSettings');
                echo json_encode($resJSON);
                exit;
            default:
                if (isset($resUpdate['message'])) {
                    $_SESSION['message'] = $resUpdate['message'];
                    $_SESSION['success'] = $resUpdate['success'];
                }
                redirect('/user/settings');
                break;
        }
    }

    public function showChangePassword() {
        $this->redirectIfNotLoggin();

        array_push($this->jsSrcs,
            ['src' => '/js/crypt/jsbn.js'],
            ['src' => '/js/crypt/prng4.js'],
            ['src' => '/js/crypt/rng.js'],
            ['src' => '/js/crypt/rsa.js'],
            ['src' => '/js/utils/req-key.js'],
            ['src' => '/js/utils/validate.js'],
            ['src' => '/js/utils/user/change-pass.js']
        );

        $this->content = view('user/user-change-pass', ['token' => generateToken()]);
    }

    public function changePassword() {
        $this->redirectIfNotLoggin();
        $this->redirectIfNotXMLHTTPRequest('/user/settings/pass');

        $tokens = $this->getPostSessionTokens();
        $oldPass = $_POST['old-pass'] ?? '';
        $pass = $_POST['pass'] ?? '';
        $cpass = $_POST['cpass'] ?? 'x';
        $id = getUserLoggedID();

        $oldPass = $this->decryptData($oldPass);
        $pass = $this->decryptData($pass);
        $cpass = $this->decryptData($cpass);

        $verifier = UserVerifier::getInstance($this->appConfig, $this->conn);
        $resPass = $verifier->verifyChangePass($id, $oldPass, $pass, $cpass, $tokens);
        if($resPass['success']) {
            $user = new User($this->conn, $this->appConfig);
            $user->resetWrongPasswordLock($id);
            $resUser = $user->updateUserPass($id, $pass);
            $resPass['message'] = $resUser['message'];
            $resPass['success'] = $resUser['success'];
        } else if ($resPass['wrongPass']) $this->manageWrongPassword($id);

        $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
        switch ($header) {
            case 'XMLHTTPREQUEST':
                $resJSON = [
                    'success' => $resPass['success'],
                    'error' => $resPass['error'] ?? NULL,
                    'message' => $resPass['message'] ?? NULL
                ];
                if (!$resPass['success']) $resJSON['ntk'] = generateToken();
                echo json_encode($resJSON);
                exit;
            default:
                if (isset($resPass['message'])) {
                    $_SESSION['message'] = $resPass['message'];
                    $_SESSION['success'] = $resPass['success'];
                }
                $resPass['success'] ? redirect('/user/settings') : redirect('/user/settings/pass');
                break;
        }
    }

    public function deleteNewEmail() {
        $this->redirectIfNotLoggin();
        $this->redirectIfNotEmailConfirmRequire();

        $tokens = $this->getPostSessionTokens('_xf', 'csrfUserSettings');
        $id = getUserLoggedID();

        $verifier = UserVerifier::getInstance($this->appConfig, $this->conn);
        $resDeleteEmail = $verifier->verifyDeleteNewEmail($id, $tokens);
        if ($resDeleteEmail['success']) {
            $user = new User($this->conn, $this->appConfig);
            if ($resDeleteEmail['success'] = $user->removeNewEmailAndToken($id)) {
                $resDeleteEmail['message'] =  'Email successfully deleted';
                $this->createSessionLogin($user->getUser($id));
            } else $resDeleteEmail['message'] = 'Delete email failed';
        }

        $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
        switch ($header) {
            case 'XMLHTTPREQUEST':
                $resJSON = [
                    'success' => $resDeleteEmail['success'],
                    'error' => $resDeleteEmail['error'] ?? NULL,
                    'message' => $resDeleteEmail['message'] ?? NULL
                ];
                if (!$resDeleteEmail['success']) $resJSON['ntk'] = generateToken('csrfUserSettings');
                echo json_encode($resJSON);
                exit;
            default:
                if (isset($resDeleteEmail['message'])) {
                    $_SESSION['message'] = $resDeleteEmail['message'];
                    $_SESSION['success'] = $resDeleteEmail['success'];
                }
                redirect('/user/settings');
                break;
        }
    }

    public function resendNewEmailValidation() {
        $this->redirectIfNotLoggin();
        $this->redirectIfNotEmailConfirmRequire();

        $tokens = $this->getPostSessionTokens('_xf', 'csrfUserSettings');
        $id = getUserLoggedID();

        $verifier = UserVerifier::getInstance($this->appConfig, $this->conn);
        $resResendEmail = $verifier->verifyResendNewEmailValidation($id, $tokens);
        if ($resResendEmail['success']) {
            $resResendEmail['success'] = $this->sendEmailValidation($resResendEmail['email'], $resResendEmail['token'], 'ENABLE YOUR EMAIL', TRUE);
            $resResendEmail['message'] = $resResendEmail['success'] ? 'Email sent successfully' : 'Sending email failed';
        }
        
        $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
        switch ($header) {
            case 'XMLHTTPREQUEST':
                $resJSON = [
                    'success' => $resResendEmail['success'],
                    'error' => $resResendEmail['error'] ?? NULL,
                    'message' => $resResendEmail['message'] ?? NULL
                ];
                $resJSON['ntk'] = generateToken('csrfUserSettings');
                echo json_encode($resJSON);
                exit;
            default:
                if (isset($resResendEmail['message'])) {
                    $_SESSION['message'] = $resResendEmail['message'];
                    $_SESSION['success'] = $resResendEmail['success'];
                }
                redirect('/user/settings');
                break;
        }
    }
} 
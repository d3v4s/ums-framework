<?php
namespace app\controllers;

use \PDO;
use app\models\User;
use app\controllers\verifiers\UMSVerifier;
use app\controllers\verifiers\Verifier;
use app\controllers\data\UMSDataFactory;

class UMSController extends Controller {
    public function __construct(PDO $conn, array $appConfig, string $layout = 'ums') {
        parent::__construct($conn, $appConfig, $layout);
    }

    public function showUsersList(string $orderBy = 'id', string $orderDir = 'desc', int $page = 1, int $usersForPage = 10) {
        $this->redirectIfCanNotUpdate();

        $this->isUsersList = TRUE;
        $search = $_GET['search'] ?? '';
        $data = UMSDataFactory::getInstance($this->appConfig, $this->conn)->getUsersListData($orderBy, $orderDir, $page, $usersForPage, $search);

        $this->content = view('ums/admin-users-list', $data);
    }

    public function showUser($username) {
        $this->redirectIfCanNotUpdate();

        $user = new User($this->conn, $this->appConfig);
        if (is_numeric($username)) $usr = $user->getUser($username);
        else $usr = $user->getUserByUsername($username);

        if (!$usr) {
            $this->showMessage('USER NOT FOUND');
            return;
        }

        array_push($this->jsSrcs,
            ['src' => '/js/utils/ums/adm-usrinf.js']
        );
        $data = UMSDataFactory::getInstance($this->appConfig)->getUserData($usr);

        $this->content = view('ums/admin-user-info', $data);
    }

    public function resetWrongPasswords() {
        $this->redirectIfCanNotUpdate();

        $tokens = $this->getPostSessionTokens('_xf-rwp', 'csrfResetWrongPass');
        $id = $_POST['id'];

        $verifier = UMSVerifier::getInstance($this->appConfig, $this->conn);
        $resReset = $verifier->verifyResetWrongPasswords($id, $tokens);
        if ($resReset['success']) {
            $user = new User($this->conn, $this->appConfig);
            $resReset = $user->resetDatetimeAndNWrongPassword($id);
            $resReset['message'] = $resReset['success'] ? 'Wrong passwords succesfully reset' : 'Reset wrong passwords failed';
        }

        $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
        switch ($header) {
            case 'XMLHTTPREQUEST':
                $resJSON = [
                    'success' => $resReset['success'],
                    'message' => $resReset['message'] ?? NULL
                ];
                if (!$resReset['success']) $resJSON['ntk'] = generateToken('csrfResetWrongPass');
                header("Content-Type: application/json");
                header("X-Content-Type-Options: nosniff");
                echo json_encode($resJSON);
                exit;
            default:
                if (isset($resReset['message'])) {
                    $_SESSION['message'] = $resReset['message'];
                    $_SESSION['success'] = $resReset['success'];
                }
                redirect('/ums/user/'.$id);
                break;
        }
    }

    public function resetLockUser() {
        $this->redirectIfCanNotUpdate();

        $tokens = $this->getPostSessionTokens('_xf-rlu', 'csrfResetLockUser');
        $id = $_POST['id'];
        
        $verifier = UMSVerifier::getInstance($this->appConfig, $this->conn);
        $resReset = $verifier->verifyResetLockUser($id, $tokens);
        if ($resReset['success']) {
            $user = new User($this->conn, $this->appConfig);
            $resReset = $user->resetLockUser($id);
            $resReset['message'] = $resReset['success'] ? 'Lock user succesfully reset' : 'Reset lock user failed';
        }

        $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
        switch ($header) {
            case 'XMLHTTPREQUEST':
                $resJSON = [
                    'success' => $resReset['success'],
                    'message' => $resReset['message'] ?? NULL
                ];
                if (!$resReset['success']) $resJSON['ntk'] = generateToken('csrfResetLockUser');
                header("Content-Type: application/json");
                header("X-Content-Type-Options: nosniff");
                echo json_encode($resJSON);
                exit;
            default:
                if (isset($resReset['message'])) {
                    $_SESSION['message'] = $resReset['message'];
                    $_SESSION['success'] = $resReset['success'];
                }
                redirect('/ums/user/'.$id);
                break;
        }
    }

    public function showUpdatePasswordUser($username) {
        $this->redirectIfCanNotChangePassword();

        $user = new User($this->conn, $this->appConfig);
        if (is_numeric($username)) $usr = $user->getUser($username);
        else $usr = $user->getUserByUsername($username);

        if (!$usr) {
            $this->showMessage('USER NOT FOUND');
            return;
        }

        array_push($this->jsSrcs,
            ['src' => '/js/crypt/jsbn.js'],
            ['src' => '/js/crypt/prng4.js'],
            ['src' => '/js/crypt/rng.js'],
            ['src' => '/js/crypt/rsa.js'],
            ['src' => '/js/utils/req-key.js'],
            ['src' => '/js/utils/validate.js'],
            ['src' => '/js/utils/ums/adm-updpass.js']
        );
        $data = [
            'user' => $usr,
            'token' => generateToken()
        ];
        $this->content = view('ums/admin-update-pass', $data);
    }

    public function updatePasswordUser() {
        $this->redirectIfCanNotChangePassword();

        $id = $_POST['id'] ?? '';
        $this->redirectIfNotXMLHTTPRequest("/ums/user/$id/update/pass");

        $tokens = $this->getPostSessionTokens();
        $pass = $_POST['pass'] ?? '';
        $cpass = $_POST['cpass'] ?? 'x';

        $pass = $this->decryptData($pass);
        $cpass = $this->decryptData($cpass);

        $verifier = UMSVerifier::getInstance($this->appConfig, $this->conn);
        $resPass = $verifier->verifyUpdatePass($id, $pass, $cpass, $tokens);
        if($resPass['success']) {
            $user = new User($this->conn, $this->appConfig);
            $resUser = $user->updateUserPass($id, $pass);
            $resPass['message'] = $resUser['message'];
            $resPass['success'] = $resUser['success'];
        }
        $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
        switch ($header) {
            case 'XMLHTTPREQUEST':
                $resJSON = [
                    'success' => $resPass['success'],
                    'error' => $resPass['error'] ?? NULL,
                    'message' => $resPass['message'] ?? NULL,
                    'userId' => $id
                ];
                if (!$resPass['success']) $resJSON['ntk'] = generateToken();
                header("Content-Type: application/json");
                header("X-Content-Type-Options: nosniff");
                echo json_encode($resJSON);
                exit;
            default:
                if (isset($resPass['message'])) {
                    $_SESSION['message'] = $resPass['message'];
                    $_SESSION['success'] = $resPass['success'];
                }
                $resPass['success'] ? redirect('/ums/user/'.$id) : redirect('/ums/user/'.$id.'/update/pass');
                break;
        }
    }

    public function showUpdateUser($username) {
        $this->redirectIfCanNotUpdate();

        $data = UMSDataFactory::getInstance($this->appConfig, $this->conn)->getUpdateUserData($username);

        if (!$data['user']) {
            $this->showMessage('USER NOT FOUND');
            return;
        }

        array_push($this->jsSrcs,
            ['src' => '/js/utils/validate.js'],
            ['src' => '/js/utils/ums/adm-updusr.js']
        );

        $this->content = view('ums/admin-update-user', $data);
    }

    public function updateUser() {
        $this->redirectIfCanNotUpdate();

        $id = $_POST['id'];
        $tokens = $this->getPostSessionTokens('_xf', 'csrfUMSUpdateUser');
        $email = $_POST['email'] ?? '';
        $username = $_POST['username'] ?? '';
        $name = $_POST['name'] ?? '';

        $user = new User($this->conn, $this->appConfig);
        if (isUserAdmin()) {
            $roletype = isset($_POST['role']) ? $_POST['role'] : '';
            $enabled = isset($_POST['enabled']) ? 1 : 0;
        } else {
            $usr = $user->getUser($id);
            $roletype = $usr->roletype;
            $enabled = $usr->enabled;
            unset($usr);
        }

        $verifier = UMSVerifier::getInstance($this->appConfig, $this->conn);
        $resUpdate = $verifier->verifyUpdateUser($id, $name, $email, $username, $roletype, $tokens);
        if($resUpdate['success']) {
            if (isset($resUpdate['deleteUser'])) $user->deleteUser($resUpdate['deleteUser']);
            $data = compact('email', 'username', 'name', 'roletype', 'enabled');
            $resUser = $user->updateUser($id, $data);
            $resUpdate['message'] = $resUser['message'];
            $resUpdate['success'] = $resUser['success'];
        }
        $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
        switch ($header) {
            case 'XMLHTTPREQUEST':
                $resJSON = [
                    'success' => $resUpdate['success'],
                    'error' => $resUpdate['error'] ?? NULL,
                    'message' => $resUpdate['message'] ?? NULL,
                    'userId' => $id
                ];
                if (!$resUpdate['success']) $resJSON['ntk'] = generateToken('csrfUMSUpdateUser');
                header("Content-Type: application/json");
                header("X-Content-Type-Options: nosniff");
                echo json_encode($resJSON);
                exit;
            default:
                if (isset($resUpdate['message'])) {
                    $_SESSION['message'] = $resUpdate['message'];
                    $_SESSION['success'] = $resUpdate['success'];
                }
                $resUpdate['success'] ? redirect("/ums/user/$id") : redirect("/ums/user/$id/update");
                break;
        }
    }

    public function showNewUser() {
        $this->redirectIfCanNotCreate();
        $this->isNewUser = TRUE;

        array_push($this->jsSrcs,
            ['src' => '/js/crypt/jsbn.js'],
            ['src' => '/js/crypt/prng4.js'],
            ['src' => '/js/crypt/rng.js'],
            ['src' => '/js/crypt/rsa.js'],
            ['src' => '/js/utils/req-key.js'],
            ['src' => '/js/utils/validate.js'],
            ['src' => '/js/utils/ums/adm-nusr.js']
        );
        $data = UMSDataFactory::getInstance($this->appConfig)->getNewUserData();
        $this->content = view('ums/admin-new-user', $data);
    }

    public function newUser() {
        $this->redirectIfCanNotCreate();
        $this->redirectIfNotXMLHTTPRequest('/ums/user/new');

        $tokens = $this->getPostSessionTokens();
        $email = $_POST['email'] ?? '';
        $username = $_POST['username'] ?? '';
        $name = $_POST['name'] ?? '';
        $pass = $_POST['pass'] ?? '';
        $cpass =$_POST['cpass'] ?? 'x';
        $roletype = $_POST['role'] ?? 'user';
        $enabled = isset($_POST['enabled']);

        $pass = $this->decryptData($pass);
        $cpass = $this->decryptData($cpass);

        $verifier = UMSVerifier::getInstance($this->appConfig, $this->conn);
        $resSignup = $verifier->verifyNewUser($name, $email, $username, $pass, $cpass, $roletype, $tokens);
        if($resSignup['success']) {
            $user = new User($this->conn, $this->appConfig);
            if (isset($resSignup['deleteUser'])) foreach ($resSignup['deleteUser'] as $userId) $user->deleteUser($userId);
            $data = compact('email', 'username', 'name', 'pass', 'roletype', 'enabled');
            $resUser = $user->saveUser($data);
            $resSignup['message'] = $resUser['message'];
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
                $resSignup['success'] ? redirect('/ums/users') : redirect('/ums/user/new');
                break;
        };
    }

    public function showDeleteUser($username) {
        $this->redirectIfCanNotDelete();

        $user = new User($this->conn, $this->appConfig);
        if (is_numeric($username)) $usr = $user->getUser($username);
        else $usr = $user->getUserByUsername($username);
        
        if (!$usr) {
            $this->showMessage('USER NOT FOUND');
            return;
        }

        $data = [
            'user' => $usr,
            'token' => generateToken('csrfDeleteUser')
        ];
        $this->content = view('ums/admin-user-delete', $data);
    }

    public function deleteNewEmail() {
        $this->redirectIfCanNotUpdate();

        $tokens = $this->getPostSessionTokens('_xf-dnm', 'csrfDeleteNewEmail');
        $id = $_POST['id'];

        $verifier = UMSVerifier::getInstance($this->appConfig, $this->conn);
        $resDelete = $verifier->verifyDeleteNewEmail($id, $tokens);
        if ($resDelete['success']) {
            $user = new User($this->conn, $this->appConfig);
            $resDelete['success'] = $user->removeNewEmailAndToken($id);
            $resDelete['message'] = $resDelete['success'] ? 'New email succesfully deleted' : 'Delete new email failed';
        }

        $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
        switch ($header) {
            case 'XMLHTTPREQUEST':
                $resJSON = [
                    'success' => $resDelete['success'],
                    'message' => $resDelete['message'] ?? NULL
                ];
                if (!$resDelete['success']) $resJSON['ntk'] = generateToken('csrfDeleteNewEmail');
                header("Content-Type: application/json");
                header("X-Content-Type-Options: nosniff");
                echo json_encode($resJSON);
                exit;
            default:
                if (isset($resDelete['message'])) {
                    $_SESSION['message'] = $resDelete['message'];
                    $_SESSION['success'] = $resDelete['success'];
                }
                redirect("/ums/user/$id");
                break;
        };
    }

    public function deleteUser() {
        $this->redirectIfCanNotDelete();

        $tokens = $this->getPostSessionTokens('_xf-du', 'csrfDeleteUser');
        $id = $_POST['id'];

        $verifier = Verifier::getInstance($this->appConfig, $this->conn);
        $resDelete = $verifier->verifyDelete($id, $tokens);
        if($resDelete['success']) {
            $user = new User($this->conn, $this->appConfig);
            $resUser = $user->deleteUser($id);
            $resDelete['message'] = $resUser['message'];
            $resDelete['success'] = $resUser['success'];
        }
        $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
        switch ($header) {
            case 'XMLHTTPREQUEST':
                $resJSON = [
                    'success' => $resDelete['success'],
                    'error' => $resDelete['error'] ?? NULL,
                    'message' => $resDelete['message'] ?? NULL
                ];
                if (!$resDelete['success']) $resJSON['ntk'] = generateToken('csrfDeleteUser');
                header("Content-Type: application/json");
                header("X-Content-Type-Options: nosniff");
                echo json_encode($resJSON);
                exit;
            default:
                if (isset($resDelete['message'])) {
                    $_SESSION['message'] = $resDelete['message'];
                    $_SESSION['success'] = $resDelete['success'];
                }
                $resDelete['success'] ? redirect('/ums/users') : redirect("/ums/user/$id");
                break;
        };
    }

    private function redirectIfCanNotChangePassword() {
        if (!userCanChangePasswords()) redirect();
    }
}
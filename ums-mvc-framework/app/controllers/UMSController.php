<?php
namespace app\controllers;

use \PDO;
use app\models\User;
use app\controllers\verifiers\UMSVerifier;
use app\controllers\verifiers\Verifier;
use app\controllers\data\UMSDataFactory;

/**
 * Class controller for admin users manage
 * @author Andrea Serra (DevAS) https://devas.info
 */
class UMSController extends Controller {
    public function __construct(PDO $conn, array $appConfig, string $layout = 'ums') {
        parent::__construct($conn, $appConfig, $layout);
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* function to view the user list page */
    public function showUsersList(string $orderBy = 'id', string $orderDir = 'desc', int $page = 1, int $usersForPage = 10) {
        /* redirect */
        $this->redirectIfCanNotUpdate();

        /* set current location */
        $this->isUsersList = TRUE;

        /* get search query */
        $search = $_GET['search'] ?? '';

        /* get data from data factory and show page */
        $data = UMSDataFactory::getInstance($this->appConfig, $this->conn)->getUsersListData($orderBy, $orderDir, $page, $usersForPage, $search);
        $this->content = view('ums/admin-users-list', $data);
    }

    /* function to view a user page */
    public function showUser($username) {
        /* redirect */
        $this->redirectIfCanNotUpdate();

        /* init user model and get user */
        $user = new User($this->conn, $this->appConfig);
        /* if is numeric get user by id */
        if (is_numeric($username)) $usr = $user->getUser($username);
        /* else get user by username */
        else $usr = $user->getUserByUsername($username);

        /* if user not found, show error message */
        if (!$usr) {
            $this->showMessage('USER NOT FOUND');
            return;
        }

        /* add javascript sources */ 
        array_push($this->jsSrcs,
            ['src' => '/js/utils/ums/adm-usrinf.js']
        );

        /* get data by data factory and show page */
        $data = UMSDataFactory::getInstance($this->appConfig)->getUserData($usr);
        $this->content = view('ums/admin-user-info', $data);
    }

    /* function to reset wrong passwords */
    public function resetWrongPasswords() {
        /* redirect */
        $this->redirectIfCanNotUpdate();

        /* get tokens and user id */
        $tokens = $this->getPostSessionTokens('XS_TKN_RWP', 'csrfResetWrongPass');
        $id = $_POST['id'];

        /* get verifier instance, and check reset wrong password request */
        $verifier = UMSVerifier::getInstance($this->appConfig, $this->conn);
        $resReset = $verifier->verifyResetWrongPasswords($id, $tokens);
        if ($resReset['success']) {
            /* if success reset worong password */
            $user = new User($this->conn, $this->appConfig);
            $resReset = $user->resetDatetimeAndNWrongPassword($id);
            $resReset['message'] = $resReset['success'] ? 'Wrong passwords succesfully reset' : 'Reset wrong passwords failed';
        }

        /* result data */
        $dataOut = [
            'success' => $resReset['success'],
            'message' => $resReset['message'] ?? NULL,
            'id' => $id
        ];

        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data['message'])) {
                $_SESSION['message'] = $data['message'];
                $_SESSION['success'] = $data['success'];
            }
            redirect('/ums/user/'.$data['id']);
        };

        $this->switchResponse($dataOut, !$resReset['success'], $funcDefault, 'csrfResetWrongPass');
//         $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
//         switch ($header) {
//             case 'XMLHTTPREQUEST':
//                 $resJSON = [
//                     'success' => $resReset['success'],
//                     'message' => $resReset['message'] ?? NULL
//                 ];
//                 if (!$resReset['success']) $resJSON['ntk'] = generateToken('csrfResetWrongPass');
//                 header("Content-Type: application/json");
//                 header("X-Content-Type-Options: nosniff");
//                 echo json_encode($resJSON);
//                 exit;
//             default:
//                 if (isset($resReset['message'])) {
//                     $_SESSION['message'] = $resReset['message'];
//                     $_SESSION['success'] = $resReset['success'];
//                 }
//                 redirect('/ums/user/'.$id);
//                 break;
//         }
    }

    /* function to reset counter of user lock */
    public function resetLockUser() {
        /* redirect */
        $this->redirectIfCanNotUpdate();

        /* get tokens ad user id */
        $tokens = $this->getPostSessionTokens('XS_TKN_RLU', 'csrfResetLockUser');
        $id = $_POST['id'];

        /* get verifier instance, and check reset wrong user locks request */
        $verifier = UMSVerifier::getInstance($this->appConfig, $this->conn);
        $resReset = $verifier->verifyResetLockUser($id, $tokens);
        if ($resReset['success']) {
            /* if success init user model, and reset count user locks */
            $user = new User($this->conn, $this->appConfig);
            /* reset user locks and set results */
            $resReset = $user->resetLockUser($id);
            $resReset['message'] = $resReset['success'] ? 'Lock user succesfully reset' : 'Reset lock user failed';
        }

        /* result data */
        $dataOut = [
            'success' => $resReset['success'],
            'message' => $resReset['message'] ?? NULL,
            'id' => $id
        ];

        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data['message'])) {
                $_SESSION['message'] = $data['message'];
                $_SESSION['success'] = $data['success'];
            }
            redirect('/ums/user/'.$data['id']);
        };

        $this->switchResponse($dataOut, !$resReset['success'], $funcDefault, 'csrfResetLockUser');
//         $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
//         switch ($header) {
//             case 'XMLHTTPREQUEST':
//                 $resJSON
//                 if (!$resReset['success']) $resJSON['ntk'] = generateToken('csrfResetLockUser');
//                 header("Content-Type: application/json");
//                 header("X-Content-Type-Options: nosniff");
//                 echo json_encode($resJSON);
//                 exit;
//             default:
//                 if (isset($resReset['message'])) {
//                     $_SESSION['message'] = $resReset['message'];
//                     $_SESSION['success'] = $resReset['success'];
//                 }
//                 redirect('/ums/user/'.$id);
//                 break;
//         }
    }

    /* function to view update password user page */
    public function showUpdatePasswordUser($username) {
        /* redirect */
        $this->redirectIfCanNotChangePassword();

        /* init user model and get user */
        $user = new User($this->conn, $this->appConfig);
        if (is_numeric($username)) $usr = $user->getUser($username);
        else $usr = $user->getUserByUsername($username);

        /* if user not found, then show error message */
        if (!$usr) {
            $this->showMessage('USER NOT FOUND');
            return;
        }

        /* add javascript sources */
        array_push($this->jsSrcs,
            ['src' => '/js/crypt/jsbn.js'],
            ['src' => '/js/crypt/prng4.js'],
            ['src' => '/js/crypt/rng.js'],
            ['src' => '/js/crypt/rsa.js'],
            ['src' => '/js/utils/req-key.js'],
            ['src' => '/js/utils/validate.js'],
            ['src' => '/js/utils/ums/adm-updpass.js']
        );

        /* set data */
        $data = [
            'user' => $usr,
            'token' => generateToken()
        ];
        $this->content = view('ums/admin-update-pass', $data);
    }

    /* function to update password user */
    public function updatePasswordUser() {
        /* redirects */
        $this->redirectIfCanNotChangePassword();
        $id = $_POST['id'] ?? '';
        $this->redirectIfNotXMLHTTPRequest("/ums/user/$id/update/pass");

        /* get tokens and post data */
        $tokens = $this->getPostSessionTokens();
        $pass = $_POST['pass'] ?? '';
        $cpass = $_POST['cpass'] ?? 'x';

        /* decrypt passwords */
        $pass = $this->decryptData($pass);
        $cpass = $this->decryptData($cpass);

        /* get instance of verifier and check password update request */
        $verifier = UMSVerifier::getInstance($this->appConfig, $this->conn);
        $resPass = $verifier->verifyUpdatePass($id, $pass, $cpass, $tokens);
        /* if success */
        if($resPass['success']) {
            /* init user model */
            $user = new User($this->conn, $this->appConfig);
            /* update user password, and set result */
            $resUser = $user->updateUserPass($id, $pass);
            $resPass['message'] = $resUser['message'];
            $resPass['success'] = $resUser['success'];
        }

        /* result data */
        $dataOut = [
            'success' => $resPass['success'],
            'error' => $resPass['error'] ?? NULL,
            'message' => $resPass['message'] ?? NULL,
            'userId' => $id
        ];

        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data['message'])) {
                $_SESSION['message'] = $data['message'];
                $_SESSION['success'] = $data['success'];
            }
            $data['success'] ? redirect('/ums/user/'.$data['userId']) : redirect('/ums/user/'.$data['userId'].'/update/pass');
        };

        $this->switchResponse($dataOut, !$resPass['success'], $funcDefault);
//         $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
//         switch ($header) {
//             case 'XMLHTTPREQUEST':
//                 $resJSON 
//                 if (!$resPass['success']) $resJSON['ntk'] = generateToken();
//                 header("Content-Type: application/json");
//                 header("X-Content-Type-Options: nosniff");
//                 echo json_encode($resJSON);
//                 exit;
//             default:
//                 if (isset($resPass['message'])) {
//                     $_SESSION['message'] = $resPass['message'];
//                     $_SESSION['success'] = $resPass['success'];
//                 }
//                 $resPass['success'] ? redirect('/ums/user/'.$id) : redirect('/ums/user/'.$id.'/update/pass');
//                 break;
//         }
    }

    /* function to view update user info page */
    public function showUpdateUser($username) {
        /* redirect */
        $this->redirectIfCanNotUpdate();

        /* get data from data factory */
        $data = UMSDataFactory::getInstance($this->appConfig, $this->conn)->getUpdateUserData($username);

        /* if user not found, show error message */
        if (!$data['user']) {
            $this->showMessage('USER NOT FOUND');
            return;
        }

        /* add javascript sources */
        array_push($this->jsSrcs,
            ['src' => '/js/utils/validate.js'],
            ['src' => '/js/utils/ums/adm-updusr.js']
        );

        /* show page */
        $this->content = view('ums/admin-update-user', $data);
    }

    /* function to update a user info */
    public function updateUser() {
        /* redirect */
        $this->redirectIfCanNotUpdate();

        /* get tokens and post data */
        $tokens = $this->getPostSessionTokens('XS_TKN', 'csrfUMSUpdateUser');
        $id = $_POST['id'];
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

        /* get verifier instance, and check update user request */
        $verifier = UMSVerifier::getInstance($this->appConfig, $this->conn);
        $resUpdate = $verifier->verifyUpdateUser($id, $name, $email, $username, $roletype, $tokens);
        if($resUpdate['success']) {
            if (isset($resUpdate['deleteUser'])) $user->deleteUser($resUpdate['deleteUser']);
            $data = compact('email', 'username', 'name', 'roletype', 'enabled');
            $resUser = $user->updateUser($id, $data);
            /* set result */
            $resUpdate['message'] = $resUser['message'];
            $resUpdate['success'] = $resUser['success'];
        }

        /* result data */
        $dataOut = [
            'success' => $resUpdate['success'],
            'error' => $resUpdate['error'] ?? NULL,
            'message' => $resUpdate['message'] ?? NULL,
            'userId' => $id
        ];

        /* function for default response */
        $funcDefault = function($data) {
            
        };

        $this->switchResponse($dataOut, !$resUpdate['success'], $funcDefault, 'csrfUMSUpdateUser');
//         $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
//         switch ($header) {
//             case 'XMLHTTPREQUEST':
//                 $resJSON = [
//                     'success' => $resUpdate['success'],
//                     'error' => $resUpdate['error'] ?? NULL,
//                     'message' => $resUpdate['message'] ?? NULL,
//                     'userId' => $id
//                 ];
//                 if (!$resUpdate['success']) $resJSON['ntk'] = generateToken('csrfUMSUpdateUser');
//                 header("Content-Type: application/json");
//                 header("X-Content-Type-Options: nosniff");
//                 echo json_encode($resJSON);
//                 exit;
//             default:
//                 if (isset($resUpdate['message'])) {
//                     $_SESSION['message'] = $resUpdate['message'];
//                     $_SESSION['success'] = $resUpdate['success'];
//                 }
//                 $resUpdate['success'] ? redirect("/ums/user/$id") : redirect("/ums/user/$id/update");
//                 break;
//         }
    }

    /* function to view new user page */
    public function showNewUser() {
        /* redirect */
        $this->redirectIfCanNotCreate();

        /* set current location */
        $this->isNewUser = TRUE;

        /* add javascript sources */
        array_push($this->jsSrcs,
            ['src' => '/js/crypt/jsbn.js'],
            ['src' => '/js/crypt/prng4.js'],
            ['src' => '/js/crypt/rng.js'],
            ['src' => '/js/crypt/rsa.js'],
            ['src' => '/js/utils/req-key.js'],
            ['src' => '/js/utils/validate.js'],
            ['src' => '/js/utils/ums/adm-nusr.js']
        );

        /* get data from data factory and show page */
        $data = UMSDataFactory::getInstance($this->appConfig)->getNewUserData();
        $this->content = view('ums/admin-new-user', $data);
    }

    /* function to add a new user */
    public function newUser() {
        /* redirects */
        $this->redirectIfCanNotCreate();
        $this->redirectIfNotXMLHTTPRequest('/ums/user/new');

        /* get tokens and post data */
        $tokens = $this->getPostSessionTokens();
        $email = $_POST['email'] ?? '';
        $username = $_POST['username'] ?? '';
        $name = $_POST['name'] ?? '';
        $pass = $_POST['pass'] ?? '';
        $cpass =$_POST['cpass'] ?? 'x';
        $roletype = $_POST['role'] ?? 'user';
        $enabled = isset($_POST['enabled']);

        /* decrypt passwords */
        $pass = $this->decryptData($pass);
        $cpass = $this->decryptData($cpass);

        /* get verifier instance, and check new user request */
        $verifier = UMSVerifier::getInstance($this->appConfig, $this->conn);
        $resSignup = $verifier->verifyNewUser($name, $email, $username, $pass, $cpass, $roletype, $tokens);
        /* if success */
        if($resSignup['success']) {
            /* init user model */
            $user = new User($this->conn, $this->appConfig);
            /* delete user if require */
            if (isset($resSignup['deleteUser'])) foreach ($resSignup['deleteUser'] as $userId) $user->deleteUser($userId);
            /* create data to save user */
            $data = compact('email', 'username', 'name', 'pass', 'roletype', 'enabled');
            $resUser = $user->saveUser($data);
            /* set result */
            $resSignup['message'] = $resUser['message'];
            $resSignup['success'] = $resUser['success'];
        }

        /* result data */
        $dataOut = [
            'success' => $resSignup['success'],
            'error' => $resSignup['error'] ?? NULL,
            'message' => $resSignup['message'] ?? NULL
        ];

        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data['message'])) {
                $_SESSION['message'] = $data['message'];
                $_SESSION['success'] = $data['success'];
            }
            $data['success'] ? redirect('/ums/users') : redirect('/ums/user/new');
        };

        $this->switchResponse($dataOut, !$resSignup['success'], $funcDefault);
//         $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
//         switch ($header) {
//             case 'XMLHTTPREQUEST':
//                 $resJSON = [
//                     'success' => $resSignup['success'],
//                     'error' => $resSignup['error'] ?? NULL,
//                     'message' => $resSignup['message'] ?? NULL
//                 ];
//                 if (!$resSignup['success']) $resJSON['ntk'] = generateToken();
//                 header("Content-Type: application/json");
//                 header("X-Content-Type-Options: nosniff");
//                 echo json_encode($resJSON);
//                 exit;
//             default:
                
//                 break;
//         };
    }

    /* function to view a delete user page */
    public function showDeleteUser($username) {
        /* redirect */
        $this->redirectIfCanNotDelete();

        /* init user model and get user */
        $user = new User($this->conn, $this->appConfig);
        if (is_numeric($username)) $usr = $user->getUser($username);
        else $usr = $user->getUserByUsername($username);

        /* if user not found, show error message */
        if (!$usr) {
            $this->showMessage('USER NOT FOUND');
            return;
        }

        /* create data and show page */
        $data = [
            'user' => $usr,
            'token' => generateToken('csrfDeleteUser')
        ];
        $this->content = view('ums/admin-user-delete', $data);
    }

    /* function to delete a new email pending of user */
    public function deleteNewEmail() {
        /* redirect */
        $this->redirectIfCanNotUpdate();

        /* get tokens and user id */
        $tokens = $this->getPostSessionTokens('XS_TKN_DNM', 'csrfDeleteNewEmail');
        $id = $_POST['id'];

        /* get verifier instance, and check delete new email request */
        $verifier = UMSVerifier::getInstance($this->appConfig, $this->conn);
        $resDelete = $verifier->verifyDeleteNewEmail($id, $tokens);
        if ($resDelete['success']) {
            $user = new User($this->conn, $this->appConfig);
            /* delet new email with token, and set result */
            $resDelete['success'] = $user->removeNewEmailAndToken($id);
            $resDelete['message'] = $resDelete['success'] ? 'New email succesfully deleted' : 'Delete new email failed';
        }

        /* result data */
        $dataOut = [
            'success' => $resDelete['success'],
            'message' => $resDelete['message'] ?? NULL,
            'id' => $id
        ];

        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data['message'])) {
                $_SESSION['message'] = $data['message'];
                $_SESSION['success'] = $data['success'];
            }
            redirect("/ums/user/{$data['id']}");
        };

        $this->switchResponse($dataOut, !$resDelete['success'], $funcDefault, 'csrfDeleteNewEmail');
//         $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
//         switch ($header) {
//             case 'XMLHTTPREQUEST':
//                 $resJSON
//                 if (!$resDelete['success']) $resJSON['ntk'] = generateToken('csrfDeleteNewEmail');
//                 header("Content-Type: application/json");
//                 header("X-Content-Type-Options: nosniff");
//                 echo json_encode($resJSON);
//                 exit;
//             default:
                
//                 break;
//         };
    }

    /* function to delete user */
    public function deleteUser() {
        /* redirect */
        $this->redirectIfCanNotDelete();

        /* get tokens and user id */
        $tokens = $this->getPostSessionTokens('XS_TKN_DU', 'csrfDeleteUser');
        $id = $_POST['id'];

        /* get verifier instance, and check delete user request */
        $verifier = Verifier::getInstance($this->appConfig, $this->conn);
        $resDelete = $verifier->verifyDelete($id, $tokens);
        if($resDelete['success']) {
            /* init user model and delete user */
            $user = new User($this->conn, $this->appConfig);
            $resUser = $user->deleteUser($id);
            /* set result */
            $resDelete['message'] = $resUser['message'];
            $resDelete['success'] = $resUser['success'];
        }

        /* result data */
        $dataOut = [
            'success' => $resDelete['success'],
            'error' => $resDelete['error'] ?? NULL,
            'message' => $resDelete['message'] ?? NULL
        ];

        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data['message'])) {
                $_SESSION['message'] = $data['message'];
                $_SESSION['success'] = $data['success'];
            }
            $data['success'] ? redirect('/ums/users') : redirect("/ums/user/{$data['id']}");
        };

        $this->switchResponse($dataOut, !$resDelete['success'], $funcDefault, 'csrfDeleteUser');
//         $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
//         switch ($header) {
//             case 'XMLHTTPREQUEST':
//                 $resJSON = [
//                     'success' => $resDelete['success'],
//                     'error' => $resDelete['error'] ?? NULL,
//                     'message' => $resDelete['message'] ?? NULL
//                 ];
//                 if (!$resDelete['success']) $resJSON['ntk'] = generateToken('csrfDeleteUser');
//                 header("Content-Type: application/json");
//                 header("X-Content-Type-Options: nosniff");
//                 echo json_encode($resJSON);
//                 exit;
//             default:
                
//                 break;
//         };
    }

    /* ##################################### */
    /* PRIVATE FUNCTIONS */
    /* ##################################### */

    /* function to redirect if yser can not change password */
    private function redirectIfCanNotChangePassword() {
        if (!userCanChangePasswords()) redirect();
    }
}
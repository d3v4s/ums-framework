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

/**
 * Class controller for users admin manage
 * @author Andrea Serra (DevAS) https://devas.info
 */
class UMSController extends UMSBaseController {
    public function __construct(PDO $conn, array $appConfig, string $layout=UMS_LAYOUT) {
        parent::__construct($conn, $appConfig, $layout);
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* ########## SHOW FUNCTIONS ########## */

    /* function to view update user info page */
    public function showUserUpdate($username) {
        /* redirect */
        $this->redirectOrFailIfCanNotUpdateUser();
        
        /* get data from data factory */
        $data = UMSDataFactory::getInstance($this->conn)->getUpdateUserData($username);
        
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
        $this->redirectOrFailIfCanNotChangePassword();
        
        /* init user model and get user */
        $userModel = new User($this->conn);
        if (is_numeric($username)) $user = $userModel->getUser($username);
        else $user = $userModel->getUserByUsername($username);
        
        /* if user not found, then show error message */
        if (!$user) $this->showMessageAndExit('User not found', TRUE);
        
        /* add javascript sources */
        array_push($this->jsSrcs,
            [SOURCE => '/js/crypt/jsbn.js'],
            [SOURCE => '/js/crypt/prng4.js'],
            [SOURCE => '/js/crypt/rng.js'],
            [SOURCE => '/js/crypt/rsa.js'],
            [SOURCE => '/js/utils/req-key.js'],
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
        $this->redirectOrFailIfCanNotCreateUser();

        /* set current location */
        $this->isNewUser = TRUE;

        /* add javascript sources */
        array_push($this->jsSrcs,
            [SOURCE => '/js/crypt/jsbn.js'],
            [SOURCE => '/js/crypt/prng4.js'],
            [SOURCE => '/js/crypt/rng.js'],
            [SOURCE => '/js/crypt/rsa.js'],
            [SOURCE => '/js/utils/req-key.js'],
            [SOURCE => '/js/utils/validate.js'],
            [SOURCE => '/js/utils/ums/new-user.js']
        );

        /* get data from data factory and show page */
        $data = UMSDataFactory::getInstance($this->conn)->getNewUserData();
        $this->content = view(getPath('ums', 'new-user'), $data);
    }

    /* ########## ACTION FUNCTIONS ########## */

    /* function to update a user info */
    public function userUpdate() {
        /* redirect */
        $this->redirectOrFailIfCanNotUpdateUser();

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
        $redirectTo = '/'.USER_ROUTE.'/'.$id.'/'.UPDATE_ROUTE;
        /* get verifier instance, and check update user request */
        $verifier = UMSVerifier::getInstance($this->conn);
        $resUpdate = $verifier->verifyUpdateUser($id, $name, $email, $username, $roletype, $tokens);
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
            $resUser = $userModel->updateUser($id, $data);

            if ($resUser[SUCCESS]) $redirectTo = '/'.UMS_TABLES_ROUTE.'/'.GET_ROUTE.'/'.USERS_TABLE.'/'.$id;

            /* set result */
            $resUpdate[MESSAGE] = $resUser[MESSAGE];
            $resUpdate[SUCCESS] = $resUser[SUCCESS];
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
        $this->redirectOrFailIfCanNotChangePassword();
        $id = $_POST[USER_ID] ?? '';
        $this->redirectIfNotXMLHTTPRequest('/'.USER_ROUTE."/$id/".PASS_UPDATE_ROUTE);
        
        /* get tokens and post data */
        $tokens = $this->getPostSessionTokens(CSRF_UPDATE_PASS);
        $pass = $_POST[PASSWORD] ?? '';
        if (empty($pass)) $this->switchFailResponse('Insert a password', '/'.USER_ROUTE."/$id/".PASS_UPDATE_ROUTE);
        $cpass = $_POST[CONFIRM_PASS] ?? '';
        
        /* decrypt passwords */
        $pass = $this->decryptData($pass);
        $cpass = $this->decryptData($cpass);

        /* set redirect to */
        $redirectTo = '/'.USER_ROUTE.'/'.$id.'/'.PASS_UPDATE_ROUTE;
        /* get instance of verifier and check password update request */
        $verifier = UMSVerifier::getInstance($this->conn);
        $resPass = $verifier->verifyUpdatePass($id, $pass, $cpass, $tokens);
        /* if success */
        if($resPass[SUCCESS]) {
            /* init user model */
            $user = new User($this->conn);
            /* update user password, and set result */
            $resUser = $user->updatePassword($id, $pass);
            if ($resUser[SUCCESS]) $redirectTo = '/'.UMS_TABLES_ROUTE.'/'.GET_ROUTE.'/'.USERS_TABLE.'/'.$id;
            $resPass[MESSAGE] = $resUser[MESSAGE];
            $resPass[SUCCESS] = $resUser[SUCCESS];
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
        $this->redirectOrFailIfCanNotUnlockUser();

        /* get tokens ad user id */
        $tokens = $this->getPostSessionTokens(CSRF_LOCK_USER_RESET);
        $id = $_POST[USER_ID];

        /* get verifier instance, and check reset wrong user locks request */
        $verifier = UMSVerifier::getInstance($this->conn);
        $resReset = $verifier->verifyLockCounterReset($id, $tokens);
        if ($resReset[SUCCESS]) {
            /* if success init user model, and reset count user locks */
            $user = new User($this->conn);
            /* reset user locks and set results */
            $resReset = array_merge($resReset, $user->lockUserReset($id));
        }
        
        /* result data */
        $dataOut = [
            REDIRECT_TO => '/'.UMS_TABLES_ROUTE.'/'.GET_ROUTE.'/'.USER_LOCK_TABLE.'/'.$id,
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
        $this->redirectOrFailIfCanNotCreateUser();
        $this->redirectIfNotXMLHTTPRequest('/'.NEW_USER_ROUTE);

        /* get tokens and post data */
        $tokens = $this->getPostSessionTokens(CSRF_NEW_USER);
        $email = $_POST[EMAIL] ?? '';
        $username = $_POST[USERNAME] ?? '';
        $name = $_POST[NAME] ?? '';
        $pass = $_POST[PASSWORD] ?? '';
        if (empty($pass)) $this->switchFailResponse('Insert a password', '/'.NEW_USER_ROUTE);
        $cpass =$_POST[CONFIRM_PASS] ?? '';
        $roletype = $this->isAdminUser() ? $_POST[ROLE_ID_FRGN] ?? DEFAULT_ROLE : DEFAULT_ROLE;
        $pending = isset($_POST[PENDING]);
        
        /* decrypt passwords */
        $pass = $this->decryptData($pass);
        $cpass = $this->decryptData($cpass);

        /* set redirect */
        $redirectTo = '/'.NEW_USER_ROUTE;
        
        /* get verifier instance, and check new user request */
        $verifier = UMSVerifier::getInstance($this->conn);
        $resSignup = $verifier->verifyNewUser($name, $email, $username, $pass, $cpass, $roletype, $tokens);
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
            /* if pending */
            if ($pending) {
                /* init pending model and save user */
                $pendModel = new PendingUser($this->conn);
                $resUser = $pendModel->savePendingUser($usrData);
                /* send enabler email */
                $this->sendEnablerEmail($email, $resUser[TOKEN]);
                $redirectTo = '/'.UMS_TABLES_ROUTE.'/'.GET_ROUTE.'/'.PENDING_USERS_TABLE.'/'.$resUser[USER_ID];
            } else {
                /* init user model and save user */
                $user = new User($this->conn);
                $resUser = $user->saveUser($usrData);
                $redirectTo = '/'.UMS_TABLES_ROUTE.'/'.GET_ROUTE.'/'.USERS_TABLE.'/'.$resUser[USER_ID];
            }

            /* if success set redirect ro users list */
//             if ($resUser[SUCCESS]) $redirectTo = '/'.UMS_TABLES_ROUTE.'/'.USERS_TABLE;
            /* set result */
            $resSignup[MESSAGE] = $resUser[MESSAGE];
            $resSignup[SUCCESS] = $resUser[SUCCESS];
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
        $this->redirectOrFailIfCanNotDeleteUser();
        
        /* get tokens and user id */
        $tokens = $this->getPostSessionTokens(CSRF_DELETE_USER);
        $id = $_POST[USER_ID];

        /* ser redirect to */
        $redirectTo = '/'.UMS_TABLES_ROUTE.'/'.GET_ROUTE.'/'.USERS_TABLE.'/'.$id;
        /* get verifier instance, and check delete user request */
        $verifier = Verifier::getInstance($this->conn);
        $resDelete = $verifier->verifyDelete($id, $tokens);
        if ($resDelete[SUCCESS]) {
            /* init user model and delete user */
            $user = new User($this->conn);
            $resUser = $user->deleteUser($id);
            /* if delete success, then save delete user */
            if ($resUser[SUCCESS]) {
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
                $redirectTo = '/'.UMS_TABLES_ROUTE.'/'.GET_ROUTE.'/'.DELETED_USER_TABLE.'/'.$id;
            }
            /* set result */
            $resDelete[MESSAGE] = $resUser[MESSAGE];
            $resDelete[SUCCESS] = $resUser[SUCCESS];
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
        $this->redirectOrFailIfCanNotRestoreUser();

        /* get tokens ad user id */
        $tokens = $this->getPostSessionTokens(CSRF_RESTORE_USER);
        $id = $_POST[USER_ID];
        /* get verifier instance, and check reset wrong user locks request */
        $redirectTo = '/'.UMS_TABLES_ROUTE.'/'.GET_ROUTE.'/'.DELETED_USER_TABLE.'/'.$id;
        $verifier = UMSVerifier::getInstance($this->conn);
        $resRestore = $verifier->verifyRestoreUser($id, $tokens);
        if ($resRestore[SUCCESS]) {
            /* if success init user model, and set user data */
            $userModel = new User($this->conn);
            $password = mb_strcut(getSecureRandomString(), 0, 8);
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
                /* if fail change user id, then delete created user */
                if (!$resRestore[SUCCESS]) $userModel->deleteUser($resRestore[USER_ID]);
                else {
                    /* send email with new random password */
                    $this->sendEmailNewRandomPassword($resRestore[USER]->{EMAIL}, $password);
                    /* set redirect to restor user */
                    $redirectTo = '/'.UMS_TABLES_ROUTE.'/'.GET_ROUTE.'/'.USERS_TABLE.'/'.$id;
                    /* remove from delete users table */
                    $delUserModel = new DeletedUser($this->conn);
                    $delUserModel->removeDeleteUser($resRestore[USER]->{USER_ID});
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

    /* function to remove session */
    public function removeSession() {
        /* redirect */
        $this->redirectOrFailIfCanNotRemoveSession();
        
        /* get tokens ad session id */
        $tokens = $this->getPostSessionTokens(CSRF_REMOVE_SESSION);
        $id = $_POST[SESSION_ID] ?? '';
        /* get verifier instance, and check remove session request */
        $verifier = UMSVerifier::getInstance($this->conn);
        $resRemove = $verifier->verifyRemoveSession($id, $tokens);
        if ($resRemove[SUCCESS]) {
            /* if success init session model and remove session */
            $sessionModel = new Session($this->conn);
            if (($resRemove[SUCCESS] = $sessionModel->removeLoginSession($id))) $resRemove[MESSAGE] = 'Session removed successfully'; 
            else $resRemove[MESSAGE] = 'Remove session failed';
        }
        
        /* result data */
        $dataOut = [
            REDIRECT_TO => '/'.UMS_TABLES_ROUTE.'/'.GET_ROUTE.'/'.SESSIONS_TABLE.'/'.$id,
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
        
        $this->switchResponse($dataOut, (!$resRemove[SUCCESS] && $resRemove[GENERATE_TOKEN]), $funcDefault, CSRF_REMOVE_SESSION);
    }

    /* ##################################### */
    /* PRIVATE FUNCTIONS */
    /* ##################################### */
    
    /* function to redirect if user can not change password */
    private function redirectOrFailIfCanNotChangePassword() {
        if (!$this->canChangePassword()) $this->switchFailResponse();
    }

    /* function to redirect if user can not unlock users */
    private function redirectOrFailIfCanNotUnlockUser() {
        if (!$this->canUnlockUser()) $this->switchFailResponse();
    }

    /* function to redirect if user can not restore users */
    private function redirectOrFailIfCanNotRestoreUser() {
        if (!$this->canRestoreUser()) $this->switchFailResponse();
    }

    /* function to redirect if user can not remove session */
    private function redirectOrFailIfCanNotRemoveSession() {
        if (!$this->canRestoreUser()) $this->switchFailResponse();
    }
















//     /* function to reset wrong passwords */
//     public function resetWrongPasswords() {
//         /* redirect */
//         $this->redirectOrFailIfCanNotUpdateUser();

//         /* get tokens and user id */
//         $tokens = $this->getPostSessionTokens('XS_TKN_RWP', 'csrfResetWrongPass');
//         $id = $_POST['id'];

//         /* get verifier instance, and check reset wrong password request */
//         $verifier = UMSVerifier::getInstance($this->appConfig, $this->conn);
//         $resReset = $verifier->verifyResetWrongPasswords($id, $tokens);
//         if ($resReset['success']) {
//             /* if success reset worong password */
//             $user = new User($this->conn, $this->appConfig);
//             $resReset = $user->resetDatetimeAndNWrongPassword($id);
//             $resReset['message'] = $resReset['success'] ? 'Wrong passwords succesfully reset' : 'Reset wrong passwords failed';
//         }

//         /* result data */
//         $dataOut = [
//             'success' => $resReset['success'],
//             'message' => $resReset['message'] ?? NULL,
//             'id' => $id
//         ];

//         /* function for default response */
//         $funcDefault = function($data) {
//             if (isset($data['message'])) {
//                 $_SESSION['message'] = $data['message'];
//                 $_SESSION['success'] = $data['success'];
//             }
//             redirect('/ums/user/'.$data['id']);
//         };

//         $this->switchResponse($dataOut, !$resReset['success'], $funcDefault, 'csrfResetWrongPass');
//     }

//     /* function to reset counter of user lock */
//     public function resetLockUser() {
//         /* redirect */
//         $this->redirectIfCanNotUpdate();

//         /* get tokens ad user id */
//         $tokens = $this->getPostSessionTokens('XS_TKN_RLU', 'csrfResetLockUser');
//         $id = $_POST['id'];

//         /* get verifier instance, and check reset wrong user locks request */
//         $verifier = UMSVerifier::getInstance($this->appConfig, $this->conn);
//         $resReset = $verifier->verifyResetLockUser($id, $tokens);
//         if ($resReset['success']) {
//             /* if success init user model, and reset count user locks */
//             $user = new User($this->conn, $this->appConfig);
//             /* reset user locks and set results */
//             $resReset = $user->resetLockUser($id);
//             $resReset['message'] = $resReset['success'] ? 'Lock user succesfully reset' : 'Reset lock user failed';
//         }

//         /* result data */
//         $dataOut = [
//             'success' => $resReset['success'],
//             'message' => $resReset['message'] ?? NULL,
//             'id' => $id
//         ];

//         /* function for default response */
//         $funcDefault = function($data) {
//             if (isset($data['message'])) {
//                 $_SESSION['message'] = $data['message'];
//                 $_SESSION['success'] = $data['success'];
//             }
//             redirect('/ums/user/'.$data['id']);
//         };

//         $this->switchResponse($dataOut, !$resReset['success'], $funcDefault, 'csrfResetLockUser');
//     }



//     /* function to delete a new email pending of user */
//     public function deleteNewEmail() {
//         /* redirect */
//         $this->redirectIfCanNotUpdate();

//         /* get tokens and user id */
//         $tokens = $this->getPostSessionTokens('XS_TKN_DNM', 'csrfDeleteNewEmail');
//         $id = $_POST['id'];

//         /* get verifier instance, and check delete new email request */
//         $verifier = UMSVerifier::getInstance($this->appConfig, $this->conn);
//         $resDelete = $verifier->verifyDeleteNewEmail($id, $tokens);
//         if ($resDelete['success']) {
//             $user = new User($this->conn);
//             /* delet new email with token, and set result */
//             $resDelete['success'] = $user->removeNewEmailAndToken($id);
//             $resDelete['message'] = $resDelete['success'] ? 'New email succesfully deleted' : 'Delete new email failed';
//         }

//         /* result data */
//         $dataOut = [
//             'success' => $resDelete['success'],
//             'message' => $resDelete['message'] ?? NULL,
//             'id' => $id
//         ];

//         /* function for default response */
//         $funcDefault = function($data) {
//             if (isset($data['message'])) {
//                 $_SESSION['message'] = $data['message'];
//                 $_SESSION['success'] = $data['success'];
//             }
//             redirect("/ums/user/{$data['id']}");
//         };

//         $this->switchResponse($dataOut, !$resDelete['success'], $funcDefault, 'csrfDeleteNewEmail');
// //         $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
// //         switch ($header) {
// //             case 'XMLHTTPREQUEST':
// //                 $resJSON
// //                 if (!$resDelete['success']) $resJSON['ntk'] = generateToken('csrfDeleteNewEmail');
// //                 header("Content-Type: application/json");
// //                 header("X-Content-Type-Options: nosniff");
// //                 echo json_encode($resJSON);
// //                 exit;
// //             default:
                
// //                 break;
// //         };
//     }

}
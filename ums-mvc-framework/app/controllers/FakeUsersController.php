<?php
namespace app\controllers;

use app\models\User;
use \PDO;
use app\controllers\verifiers\FakeUsersVerifier;

/**
 * Class controller for manage creation of fake users
 * @author Andrea Serra (DevAS) https://devas.info
 */
class FakeUsersController extends Controller {
    /* arrays for names, lastname and domains random */
    protected $names = ['Andrea', 'Francesco', 'Giuseppe', 'John', 'Elena', 'Kayle', 'Stan', 'Erik', 'Kenny', 'Butters', 'Roger', 'David'];
    protected $lastnames = ['Serra', 'Rossi', 'da Vinci', 'Smith', 'Cruz', 'Waters', 'Gilmour', 'Marsh', 'Cartman', 'Stoch'];
    protected $domains = ['protonmail.com', 'gmail.com', 'yahoo.com', 'mail.com', 'hotmail.it', 'libero.it', 'devas.info'];

    public function __construct(PDO $conn, array $appConfig, string $layout = 'ums') {
        parent::__construct($conn, $appConfig, $layout);
    }

    /* ##################################### */
    /* PUBLIC FUNCTION */
    /* ##################################### */

    /* function to view fake users page */
    public function showAddFakeUsers() {
        /* redirects */
        $this->redirectIfNotAddFakeUsers();
        $this->redirectIfCanNotCreate();

        /* add javascript sources and view fake user page */
        array_push($this->jsSrcs,
            ['src' => '/js/utils/ums/adm-fkusrs.js']
        );
        $this->content = view('ums/admin-add-fake-users', ['token' => generateToken('csrfFakeUser')]);
    }

    /* function to add fake users */
    public function addFakeUsers() {
        /* redirects */
        $this->redirectIfNotAddFakeUsers();
        $this->redirectIfCanNotCreate();

        /* get data */
        $tokens = $this->getPostSessionTokens('XS_TKN', 'csrfFakeUser');
        $nFakeUsers = $_POST['n-users'];
        $enabled = isset($_POST['enabled']);

        /* get verifier instance, and check add fake users request */
        $verifier = FakeUsersVerifier::getInstance($this->appConfig);
        $resAddFakeUsers = $verifier->verifyAddFakeUsers($nFakeUsers, $tokens);
        /* if success */
        if ($resAddFakeUsers['success']) {
            /* set default password and role user */
            $pass = $this->appConfig['app']['passDefault'];
            $roletype = 'user';
            /* init user and counter */
            $user = new User($this->conn, $this->appConfig);
            $usersAdded = 0;
            /* start loop fake user creator */
            while ($nFakeUsers-- > 0) {
                $name = $this->getRandomName();
                $username = $this->getRandomUsername($name);
                $email = $this->getRandomEmail($name);
                $dataUsr = compact('email', 'username', 'name', 'pass', 'roletype', 'enabled');
                if ($user->saveUser($dataUsr)['success']) $usersAdded++;
            }
            $resAddFakeUsers['message'] = "$usersAdded fake users added successfully";
            $resAddFakeUsers['success'] = TRUE;
            $resAddFakeUsers['userAdded'] = $usersAdded;
        }

        /* result data */
        $dataOut = [
            'success' => $resAddFakeUsers['success'],
            'message' => $resAddFakeUsers['message'] ?? NULL,
            'error' => $resAddFakeUsers['error'] ?? NULL
        ];

        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data['message'])) {
                $_SESSION['message'] = $data['message'];
                $_SESSION['success'] = $data['success'];
            }
            $data['success'] ? redirect('/ums/users') : redirect('ums/users/fake');
        };

        $this->switchResponse($dataOut, !$resAddFakeUsers['success'], $funcDefault, 'csrfFakeUser');
    }

    /* ##################################### */
    /* PRIVATE FUNCTION */
    /* ##################################### */

    /* function to redirect if add fake user is disable on settings app */
    private function redirectIfNotAddFakeUsers() {
        if (!$this->appConfig['app']['addFakeUsersPage']) redirect();
    }

    /* function to get a random name */
    private function getRandomName(): string {
        $randName = mt_rand(0, count($this->names) - 1);
        $randLastname = mt_rand(0, count($this->lastnames) - 1);

        return $this->names[$randName].' '.$this->lastnames[$randLastname];
    }

    /* function to get a random username */
    private function getRandomUsername(string $name): string {
        $name = strtolower(str_replace(' ', '.', $name));
        return $name.mt_rand(10, 99);
    }

    /* function to get a random email */
    private function getRandomEmail(string $name): string {
        $randDomain = mt_rand(0, count($this->domains) - 1);
        return $this->getRandomUsername($name).'@'.$this->domains[$randDomain];
    }
}
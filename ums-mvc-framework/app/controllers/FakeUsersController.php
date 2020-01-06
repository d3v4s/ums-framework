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
        $this->redirectOrFailIfCanNotCreateUser();

        /* add javascript sources and view fake user page */
        array_push($this->jsSrcs,
            [SOURCE => '/js/utils/ums/adm-fkusrs.js']
        );
        $this->content = view('ums/admin-add-fake-users', [TOKEN => generateToken(CSRF_ADD_FAKE_USER)]);
    }

    /* function to add fake users */
    public function addFakeUsers() {
        /* redirects */
        $this->redirectIfNotAddFakeUsers();
        $this->redirectOrFailIfCanNotCreateUser();

        /* get data */
        $tokens = $this->getPostSessionTokens(CSRF_ADD_FAKE_USER);
        $nFakeUsers = $_POST[N_USERS];
        $enabled = isset($_POST[ENABLED]);
        $onPending = isset($_POST[PENDING]);

        /* get verifier instance, and check add fake users request */
        $verifier = FakeUsersVerifier::getInstance($this->appConfig);
        $resAddFakeUsers = $verifier->verifyAddFakeUsers($nFakeUsers, $tokens);
        /* if success */
        if ($resAddFakeUsers[SUCCESS]) {
            /* set default password and role user */
            $pass = $this->appConfig[UMS][PASS_DEFAULT];
            $roletype = $this->appConfig[UMS][DEFAULT_USER_ROLE];
            /* init user and counter */
            $user = new User($this->conn, $this->appConfig);
            $usersAdded = 0;
            /* set function to add user (pending user or not) */ 
            $funcAdder = $onPending ? 'savePendingUser' : 'saveUser';
            /* start loop fake user creator */
            while ($nFakeUsers-- > 0) {
                /* get random user properties */
                $name = $this->getRandomName();
                $username = $this->getRandomUsername($name);
                $email = $this->getRandomEmail($name);
                /* set fake user data */
                $dataUsr = [
                    USERNAME => $username,
                    EMAIL => $email,
                    NAME => $name,
                    PASS => $pass,
                    ROLE => $roletype,
                    ENABLED => $enabled
                ];
                /* add fake user */
                if ($user->{$funcAdder}($dataUsr)[SUCCESS]) $usersAdded++;
            }
            /* set result */
            $resAddFakeUsers[MESSAGE] = "$usersAdded fake users added successfully";
            $resAddFakeUsers[SUCCESS] = TRUE;
        }

        /* result data */
        $dataOut = [
            SUCCESS => $resAddFakeUsers[SUCCESS],
            MESSAGE => $resAddFakeUsers[MESSAGE] ?? NULL,
            ERROR => $resAddFakeUsers[ERROR] ?? NULL
        ];

        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data[MESSAGE])) {
                $_SESSION[MESSAGE] = $data[MESSAGE];
                $_SESSION[SUCCESS] = $data[SUCCESS];
            }
            $data[SUCCESS] ? redirect('/ums/users') : redirect('ums/users/fake');
        };

        $this->switchResponse($dataOut, !$resAddFakeUsers[SUCCESS], $funcDefault, CSRF_ADD_FAKE_USER);
    }

    /* ##################################### */
    /* PRIVATE FUNCTION */
    /* ##################################### */

    /* function to redirect if add fake user is disable on settings app */
    private function redirectIfNotAddFakeUsers() {
        if (!ADD_FAKE_USER_PAGE) $this->switchFailResponse();
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
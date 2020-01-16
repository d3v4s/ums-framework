<?php
namespace app\controllers;

use app\models\User;
use \PDO;
use app\controllers\verifiers\FakeUsersVerifier;
use app\models\PendingUser;

/**
 * Class controller for manage creation of fake users
 * @author Andrea Serra (DevAS) https://devas.info
 */
class FakeUsersController extends UMSBaseController {
    /* arrays for names, lastname and domains random */
    protected $names = ['Andrea', 'Francesco', 'Giuseppe', 'John', 'Elena', 'Kayle', 'Stan', 'Erik', 'Kenny', 'Butters', 'Roger', 'David'];
    protected $lastnames = ['Serra', 'Rossi', 'da Vinci', 'Smith', 'Cruz', 'Waters', 'Gilmour', 'Marsh', 'Cartman', 'Stoch'];
    protected $domains = ['protonmail.com', 'gmail.com', 'yahoo.com', 'mail.com', 'hotmail.it', 'libero.it', 'devas.info'];

    public function __construct(PDO $conn, array $appConfig, string $layout=UMS_LAYOUT) {
        parent::__construct($conn, $appConfig, $layout);
    }

    /* ##################################### */
    /* PUBLIC FUNCTION */
    /* ##################################### */

    /* function to view fake users page */
    public function showAddFakeUsers() {
        /* redirects */
        $this->redirectOrFailIfNotAddFakeUsers();
        $this->redirectOrFailIfCanNotCreateUser();

        /* add javascript sources and view fake user page */
        array_push($this->jsSrcs,
            [SOURCE => '/js/utils/ums/fake-users.js']
        );
        $this->content = view(getPath('ums', 'fake-users'), [TOKEN => generateToken(CSRF_ADD_FAKE_USER)]);
    }

    /* function to add fake users */
    public function addFakeUsers() {
        /* redirects */
        $this->redirectOrFailIfNotAddFakeUsers();
        $this->redirectOrFailIfCanNotCreateUser();

        /* get data */
        $tokens = $this->getPostSessionTokens(CSRF_ADD_FAKE_USER);
        $nFakeUsers = $_POST[N_USERS] ?? '';
        $enabled = isset($_POST[ENABLED]);
        $onPending = isset($_POST[PENDING]);

        /* set redirect to */
        $redirectTo = '/'.FAKE_USERS_ROUTE;

        /* get verifier instance, and check add fake users request */
        $verifier = FakeUsersVerifier::getInstance();
        $resAddFakeUsers = $verifier->verifyAddFakeUsers($nFakeUsers, $tokens);
        /* if success */
        if ($resAddFakeUsers[SUCCESS]) {
            /* set default password, role user and counter */
            $pass = DEFAULT_PASSWORD;
            $roleId = DEFAULT_ROLE;
            $usersAdded = 0;

            /* if pending is set */
            if ($onPending) {
                /* init pending user model and set function name */
                $model = new PendingUser($this->conn);
                $funcAdder = 'savePendingUser';
            } else {
                /* init user model and set function name */
                $model = new User($this->conn);
                $funcAdder = 'saveUser';
            }
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
                    PASSWORD => $pass,
                    ROLE_ID_FRGN => $roleId,
                    ENABLED => $enabled ? 1 : 0
                ];
                /* add fake user */
                if ($model->{$funcAdder}($dataUsr)[SUCCESS]) $usersAdded++;
                $redirectTo = '/'.USERS_LIST_ROUTE;
            }
            /* set result */
            $resAddFakeUsers[MESSAGE] = "$usersAdded fake users added successfully";
            $resAddFakeUsers[SUCCESS] = TRUE;
        }

        /* result data */
        $dataOut = [
            REDIRECT_TO => $redirectTo,
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
            redirect($data[REDIRECT_TO]);
        };

        $this->switchResponse($dataOut, (!$resAddFakeUsers[SUCCESS] && $resAddFakeUsers[GENERATE_TOKEN]), $funcDefault, CSRF_ADD_FAKE_USER);
    }

    /* ##################################### */
    /* PRIVATE FUNCTION */
    /* ##################################### */

    /* function to redirect if add fake user is disable on settings app */
    private function redirectOrFailIfNotAddFakeUsers() {
        if (!FAKE_USERS) $this->switchFailResponse();
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
<?php
namespace app\controllers;

use app\models\User;
use \PDO;
use app\controllers\verifiers\FakeUsersVerifier;

class FakeUsersController extends Controller {
    protected $names = ['Andrea', 'Francesco', 'Giuseppe', 'John', 'Elena', 'Kayle', 'Stan', 'Erik', 'Kenny', 'Butters', 'Roger', 'David'];
    protected $lastnames = ['Serra', 'Rossi', 'da Vinci', 'Smith', 'Cruz', 'Waters', 'Gilmour', 'Marsh', 'Cartman', 'Stoch'];
    protected $domains = ['protonmail.com', 'gmail.com', 'yahoo.com', 'mail.com', 'hotmail.it', 'libero.it', 'andreaserra.it'];

    public function __construct(PDO $conn, array $appConfig, string $layout = 'ums') {
        parent::__construct($conn, $appConfig, $layout);
    }

    public function showAddFakeUsers() {
        $this->redirectIfNotAddFakeUsers();
        $this->redirectIfCanNotCreate();

        array_push($this->jsSrcs,
            ['src' => '/js/utils/ums/adm-fkusrs.js']
        );
        $this->content = view('ums/admin-add-fake-users', ['token' => generateToken('csrfFakeUser')]);
    }

    public function addFakeUsers() {
        $this->redirectIfNotAddFakeUsers();
        $this->redirectIfCanNotCreate();

        $tokens = $this->getPostSessionTokens('_xf', 'csrfFakeUser');
        $nFakeUsers = $_POST['n-users'];
        $enabled = isset($_POST['enabled']);
        
        $verifier = FakeUsersVerifier::getInstance($this->appConfig);
        $resAddFakeUsers = $verifier->verifyAddFakeUsers($nFakeUsers, $tokens);
        if ($resAddFakeUsers['success']) {
            $pass = $this->appConfig['app']['passDefault'];
            $roletype = 'user';
            $user = new User($this->conn, $this->appConfig);
            $usersAdded = 0;
            while ($nFakeUsers-- > 0) {
                $name = $this->getRandomName();
                $username = $this->getRandomUsername($name);
                $email = $this->getRandomEmail($name);
                $data = compact('email', 'username', 'name', 'pass', 'roletype', 'enabled');
                if ($user->saveUser($data)['success']) $usersAdded++;
            }
            $resAddFakeUsers['message'] = "$usersAdded fake users added successfully";
            $resAddFakeUsers['success'] = TRUE;
            $resAddFakeUsers['userAdded'] = $usersAdded;
        }

        $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
        switch ($header) {
            case 'XMLHTTPREQUEST':
                $resJSON = [
                    'success' => $resAddFakeUsers['success'],
                    'message' => $resAddFakeUsers['message'] ?? NULL,
                    'error' => $resAddFakeUsers['error'] ?? NULL
                ];
                if (!$resAddFakeUsers['success']) $resJSON['ntk'] = generateToken('csrfFakeUser');
                header("Content-Type: application/json");
                header("X-Content-Type-Options: nosniff");
                echo json_encode($resJSON);
                exit;
            default:
                if (isset($resAddFakeUsers['message'])) {
                    $_SESSION['message'] = $resAddFakeUsers['message'];
                    $_SESSION['success'] = $resAddFakeUsers['success'];
                }
                $resAddFakeUsers['success'] ? redirect('/ums/users') : redirect('ums/users/fake');
                break;
        }
    }

    private function redirectIfNotAddFakeUsers() {
        if (!$this->appConfig['app']['addFakeUsersPage']) redirect();
    }

    private function getRandomName(): string {
        $randName = mt_rand(0, count($this->names) - 1);
        $randLastname = mt_rand(0, count($this->lastnames) - 1);

        return $this->names[$randName].' '.$this->lastnames[$randLastname];
    }

    private function getRandomUsername(string $name): string {
        $name = strtolower(str_replace(' ', '.', $name));
        return $name.mt_rand(10, 99);
    }

    private function getRandomEmail(string $name): string {
        $randDomain = mt_rand(0, count($this->domains) - 1);
        return $this->getRandomUsername($name).'@'.$this->domains[$randDomain];
    }
}
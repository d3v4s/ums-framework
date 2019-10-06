<?php
namespace app\controllers;

use app\models\User;
use \PDO;

require_once __DIR__.'/../../autoload.php';
require_once __DIR__.'/../../helpers/functions.php';

class FakeUsersController extends Controller {
    protected $names = ['Andrea', 'Francesco', 'Giuseppe', 'John', 'Elena', 'Kayle'];
    protected $lastnames = ['Serra', 'Rossi', 'da Vinci', 'Smith', 'Cruz', 'Waters'];
    protected $domains = ['protonmail.com', 'gmail.com', 'yahoo.com', 'mail.com', 'hotmail.it', 'libero.it', 'andreaserra.it'];

    public function __construct(PDO $conn, string $layout = 'ums') {
        parent::__construct($conn, $layout);
    }

    public function showAddFakeUsers() {
        $this->redirectIfNotAddFakeUsers();
        $this->redirectIfNotCanCreate();
        $this->isAddFakeUsers = TRUE;
        $data = [
            'token' => $this->generateToken()
        ];
        $this->content = view('add-fake-users', $data);
    }

    private function redirectIfNotAddFakeUsers() {
        if (!$this->addFakeUsers) redirect('/');
    }

    public function addFakeUsers() {
        $this->redirectIfNotAddFakeUsers();
        $this->redirectIfNotCanCreate();
        $token = $_POST['_xf'] ?? 'tkn';
        $tokenSess = $_SESSION['csrf'] ?? '';
        $nFakeUsers = $_POST['n-users'] ?? '';
        if ($this->verifyToken($token, $tokenSess)) {
            $user = new User($this->conn);
            while ($nFakeUsers-- > 0) {
                $name = $this->getRandomName();
                $username = $this->getRandomUsername($name);
                $email = $this->getRandomEmail($name);
                $pass = 'test';
                $roletype = 'user';
                $enabled = FALSE;
                $data = compact('email', 'username', 'name', 'pass', 'roletype', 'enabled');
                $user->saveUser($data);
            }
        }
        redirect('/ums/users');
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
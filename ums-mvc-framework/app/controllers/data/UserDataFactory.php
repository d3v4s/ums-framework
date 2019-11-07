<?php
namespace app\controllers\data;

class UserDataFactory extends DataFactory {
    protected $userRoles = [];

    protected function __construct(array $appConfig) {
        parent::__construct($appConfig);
        $this->userRoles = getList('userRoles');
    }

    public function setUserRoles(array $userRoles) {
        $this->userRoles = $userRoles;
    }

    public function getUserData(): array {
        return [
            'tokenLogout' => generateToken('csrfLogout'),
            'token' => generateToken('csrfUserSettings'),
            'user' => getUserLogged(),
            'userRoles' => $this->userRoles,
            'confirmNewEmail' => getUserLoggedNewEmail() && getUserLoggedTokenConfirmEmail()
        ];
    }
}


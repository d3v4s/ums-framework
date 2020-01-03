<?php
namespace app\controllers\data;

/**
 * Class data factory, to manage response data of user request
 * @author Andrea Serra (DevAS) https://devas.info
 */
class UserDataFactory extends DataFactory {
    protected $userRoles = [];

    protected function __construct(array $appConfig) {
        parent::__construct($appConfig);
        $this->userRoles = getList('userRoles');
    }

    /* function to set user roles */
    public function setUserRoles(array $userRoles) {
        $this->userRoles = $userRoles;
    }

    /* function to get user data */
    public function getUserData(&$tokenLogout): array {
        /* return data */
        return [
            'tokenLogout' => ($tokenLogout = generateToken('csrfLogout')),
            'token' => generateToken('csrfUserSettings'),
            'user' => getUserLogged(),
            'userRoles' => $this->userRoles,
            'confirmNewEmail' => getUserLoggedNewEmail() && getUserLoggedTokenConfirmEmail()
        ];
    }
}


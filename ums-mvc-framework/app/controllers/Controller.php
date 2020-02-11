<?php
namespace app\controllers;

use app\controllers\verifiers\Verifier;
use app\models\Session;
use app\models\Email;
use app\models\User;
use app\models\Role;
use \DateTime;
use \Exception;
use \PDO;
use app\core\Router;

/**
 * Class base controller that implement principal properties and functions
 * @author Andrea Serra (DevAS) https://devas.info
 */
class Controller {
    protected $content;
    protected $conn;
    protected $appConfig;
    protected $layout;
    /* CSP properties */
    protected $setCSPHeader = TRUE;
    protected $CSPDefaultSrc = "'self'";
    protected $CSPScriptSrc = "'self'";
    protected $CSPScriptNonce;
    protected $CSPStyleSrc = "'self'";
    protected $CSPStyleNonce;
    protected $CSPImgSrc = "'self'";
    protected $CSPImgNonce;
    protected $CSPMediaSrc = "'none'";
    protected $CSPMediaNonce;
    protected $CSPFontSrc = "'self'";
    protected $CSPConnectSrc = "'self'";
    protected $CSPFormAction = "'self'";
    protected $CSPFrameAncestors = "'none'";
    protected $CSPPluginTypes = "'none'";
    protected $CSPObjectSrc = "'none'";
    protected $CSPWorkerSrc = "'none'";
    protected $CSPFrameSrc = "'none'";
    protected $CSPChildSrc = "'none'";
    protected $CSPBaseUri = "'none'";
    protected $CSPSandbox = '';
    protected $CSPReportUri = '';
    /* x-frame-options hedaer */
    protected $XFrameOptions = 'block';
    /* x-xss-protection hedaer */
    protected $XXSSProtection = '1; mode=block';
    /* x-content-type-options header */
    protected $XContentTypeOptions = 'nosniff';
    /* js and css sources */
    protected $jsSrcs = [];
    protected $cssSrcs = [];
    /* current location properties */
    protected $isHome = FALSE;
    protected $isLogin = FALSE;
    protected $isSignup = FALSE;
    protected $isUmsHome = FALSE;
    /* search  engine robots properties */
    protected $robots = '';
    protected $googlebot = '';
    /* title page */
    protected $title = 'UMS Framework';
    /* content type */
    protected $contentType = 'text/html; charset=utf-8';
    /* keywords and description of page */
    protected $keywords = 'php, ums, framework, programming, development, users, management, system, user, mvc, model, view, controller';
    protected $description = 'PHP FRAMEWORK UMS - This is a framework for user management, which implements the design pattern MVC (Model-view-controller) - Developed by Andrea Serra - DevAS';
    protected $loginSession = NULL;
    protected $userRole = NULL;
    protected $langCli = NULL;
    protected $lang = NULL;

    public function __construct(PDO $conn = NULL, array $appConfig = NULL, string $layout=DEFAULT_LAYOUT) {
        /* get config if is null */
        $this->conn = $conn;
        $this->appConfig = $appConfig ?? getConfig();
        $this->layout = getLayoutPath().'/'.$this->appConfig[LAYOUT][$layout].'.tpl.php';
        $this->langCli = $this->getLang();
        $this->lang = $this->getLanguageArray();
        /* if require redirect on https */
        if ($this->appConfig[SECURITY][ONLY_HTTPS]) $this->redirectOnSecureConnection();
        /* get login session */
        $this->loginSession = $this->getLoginSession();
        /* if have login session */
        if ($this->loginSession) {
            /* init role model, and get roles of login user */
            $role = new Role($this->conn);
            $this->userRole = $role->getRole($this->loginSession->{ROLE_ID_FRGN});
            /* manage session */
            $this->handlerSession();
            /* generate logout token */
            $this->{LOGOUT_TOKEN} = generateToken(CSRF_LOGOUT);
            array_push($this->jsSrcs, 
                [SOURCE => '/js/utils/login/logout.js'],
                [SOURCE => '/js/crypt/jsbn.js'],
                [SOURCE => '/js/crypt/prng4.js'],
                [SOURCE => '/js/crypt/rng.js'],
                [SOURCE => '/js/crypt/rsa.js']
            );
        }
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* funciton to set layout */
    public function setLayout(string $layout) {
        $this->layout = getLayoutPath().'/'.$this->appConfig[LAYOUT][$layout].'.tpl.php';
    }

    /* function to display content on layout */
    public function display() {
        /* if CSP (Content Security Policy) is require */
        if ($this->setCSPHeader) {
            /* get CSP content and set CSP headers */
            $this->cspContent = $this->getCSPContent();
            header("Content-Security-Policy: $this->cspContent");
            header("X-Content-Security-Policy: $this->cspContent");
            header("X-WebKit-CSP: $this->cspContent");
        }
        /* set content type header */
        header("Content-Type: $this->contentType");
        /* set content type option header */
        header("X-Content-Type-Options: $this->XContentTypeOptions");
        /* set XSS (Cross Site Script) protection header */
        header("X-XSS-Protection: $this->XXSSProtection");
        /* set frame option header */
        header("X-Frame-Options: $this->XFrameOptions");
        /* view layout */
        require_once $this->layout;
    }

    /* ########## SHOW FUNCTIONS ########## */

    /* function to view the home page */
    public function showHome() {
        $this->isHome = TRUE;
        $this->keywords .= ', home, homepage, index, welcome';
        $this->content = view('home');
    }

    /* function to show a message on page */
    public function showMessage(string $message, bool $error=FALSE) {
        $data = [
            MESSAGE => $message,
            ERROR => $error
        ];
        $this->content = view('show-message', $data);
    }

    /* function to set a message page, show it and exit */
    public function showMessageAndExit(string $message, bool $error=FALSE) {
        $this->showMessage($message, $error);
        $this->display();
        exit;
    }

    /* function to send 404 code and show page not found */
    public function showPageNotFound() {
        /* set default layout */
        $this->setLayout(DEFAULT_LAYOUT);
        header($_SERVER["SERVER_PROTOCOL"].' 404 Not Found', TRUE, 404);
        $this->content = view(PAGE_NOT_FOUND);
    }

    /* function to send 404 code, show page not found  and exit */
    public function showPageNotFoundAndExit() {
        $this->showPageNotFound();
        $this->display();
        exit;
    }

    /* function to view error page */
    public function showPageError(Exception $exception) {
        $data = [];
        /* if is set show exception, then view info about it */
        if (SHOW_MESSAGE_EXCEPTION) {
            $data[EXCEPTION] = [
                TO_STRING => $exception->__toString(),
                CODE => $exception->getCode(),
                MESSAGE => $exception->getMessage(),
                FILE => $exception->getFile(),
                LINE => $exception->getLine(),
                PREVIOUS => $exception->getMessage(),
                TRACE => $exception->getTrace(),
                TRACE_STRING => $exception->getTraceAsString()
            ];
        }
        $this->content = view(PAGE_EXCEPTION, $data);
    }

    /* function to view the home page */
    public function showDoubleLogin() { 
        /* if already double login session redirect */
        if ($this->isDoubleLoginSession()) redirect($_GET[REDIRECT_TO] ?? '/');

        /* add javascript source */
        array_push($this->jsSrcs,
            [SOURCE => '/js/utils/login/double-login.js']
        );
        /* set redirect data */
        $data = [
            REDIRECT_TO => $_GET[REDIRECT_TO] ?? NULL,
            TOKEN => generateToken(CSRF_DOUBLE_LOGIN),
            GET_KEY_TOKEN => generateToken(CSRF_KEY_JSON),
            LANG => $this->lang[DATA]
        ];
//         dd($data);
        /* show page */
        $this->content = view(getPath('login', 'double-login'), $data);
    }

    /* function to send public key on json format */
    public function showKeyJSON() {
        /* redirect */
        $this->redirectIfNotXMLHTTPRequest();

        /* get tokens and init json result */
        $tokens = $this->getPostSessionTokens(CSRF_KEY_JSON);
        $resJSON = [];
        /* init verifier */
        /* if valide token, then send key */
        if (Verifier::getInstance()->verifyTokens($tokens)) {
            /* set success result */
            $resJSON[SUCCESS] = TRUE;
            /* get key and set on result */
            $keys = $this->getKey();
            $resJSON[KEY_N] = $keys[KEY_N];
            $resJSON[KEY_E] = $keys[KEY_E];
        } else {
            /* set fail result */
            $resJSON[MESSAGE] = 'Fail';
            $resJSON[SUCCESS] = FALSE;
        }
        /* send json response */
        sendJsonResponse($resJSON);
    }
    
    /* function to send json response if XML HTTP request or send a default response */
    public function switchResponse(array $data, bool $generateNewToken, callable $funcDefault, string $nameToken=CSRF) {
        /* get request with header */
        $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
        /* switc the response according to the header */
        switch ($header) {
            /* if response is XMLHTTP, then send json response */
            case 'XMLHTTPREQUEST':
                if ($generateNewToken) $data[NEW_TOKEN] = generateToken($nameToken);
                sendJsonResponse($data);
                exit;
                /* default function */
            default:
                $funcDefault($data);
                break;
        }
    }

    /* function to send response json (XML HTTP) or default */
    public function switchFailResponse(string $message=NULL) {
        /* set 404 error code on response header */
        header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
        /* get request with header */
        $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
        /* switc the response according to the header */
        switch ($header) {
            /* if response is XMLHTTP, then send json response */
            case 'XMLHTTPREQUEST':
                sendJsonResponse([
                MESSAGE => $message ?? 'Fail',
                SUCCESS => FALSE
                ]);
                exit;
                /* default function */
            default:
                /* set session message if it is set */
                if (isset($message)) {
                    $_SESSION[MESSAGE] = $message;
                    $_SESSION[SUCCESS] = FALSE;
                }
                /* show page not found */
                $this->showPageNotFoundAndExit();
        }
    }

    /* ##################################### */
    /* PROTECTED FUNCTIONS */
    /* ##################################### */

    /* function to request double login */
    protected function handlerDoubleLogin() {
        /* if is not double login session */
        if (!$this->isDoubleLoginSession()) {
            $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
            /* switc the response according to the header */
            switch ($header) {
                /* if response is XMLHTTP, then send json response */
                case 'XMLHTTPREQUEST':
                    sendJsonResponse([
                        DOUBLE_LOGIN_REQUIRE => TRUE,
                        MESSAGE => $this->lang[DATA]['double_login'],
                        SUCCESS => FALSE,
                        DOUBLE_LOGIN_DATA => [
                            ACTION => Router::getRoute('app\controllers\Controller', 'showDoubleLogin'),
                            TOKEN => generateToken(CSRF_DOUBLE_LOGIN),
                            TOKEN_NAME => CSRF_DOUBLE_LOGIN,
                            GET_KEY_TOKEN => generateToken(CSRF_KEY_JSON),
                            KEY_TOKEN_NAME => CSRF_KEY_JSON
                        ]
                    ]);
                    exit;
                /* default function */
                default:
                    /* show double login page and exit */
                    $this->showDoubleLogin();
                    $this->display();
                    exit();
                }
            }
    }

    /* function to get tokens of session and post */
    protected function getPostSessionTokens(string $nameToken=CSRF): array {
//         $postToken = $_POST[$postTokenName] ?? 'tkn';
        /* reformat name token for header */
        $headerToken = str_replace('-', '_', $nameToken);
        $headerToken = mb_strtoupper($headerToken);
        /* get post token from header */
        $postToken = $_SERVER['HTTP_'.$headerToken] ?? 'X';
        /* get session token and unset it */
        $sessionToken = isset($_SESSION[$nameToken]) && $_SESSION[$nameToken][EXPIRE_DATETIME] > new DateTime() ? $_SESSION[$nameToken][TOKEN] : '';
        unset($_SESSION[$nameToken]);
        /* return tokens */
        return [$postToken, $sessionToken];
    }
    
    /* function to get CPS content by properties */
    protected function getCSPContent(): string {
        /* generate nonces for scripts, styles, images and medias
         * and create the CSP specifications
         */
        $this->CSPScriptNonce = getSecureRandomString();
        $CSPScriptSrc = 'script-src '.($this->CSPScriptSrc ? $this->CSPScriptSrc : '')." 'nonce-$this->CSPScriptNonce'";
        $this->CSPStyleNonce = getSecureRandomString();
        $CSPStyleSrc = 'style-src '.($this->CSPStyleSrc ? $this->CSPStyleSrc : '')." 'nonce-$this->CSPStyleNonce'";
        $this->CSPImgNonce = getSecureRandomString();
        $CSPImgSrc = 'img-src '.($this->CSPImgSrc ? $this->CSPImgSrc : '')." 'nonce-$this->CSPImgNonce'";
        $this->CSPMediaNonce = getSecureRandomString();
        $CSPMediaSrc = 'media-src '.($this->CSPMediaSrc ? $this->CSPMediaSrc : '')." 'nonce-$this->CSPMediaNonce'";
        
        /* create a CSP content and return it */
        $content = $this->CSPDefaultSrc ? 'default-src '.$this->CSPDefaultSrc.'; ' : '';
        $content .= "$CSPScriptSrc; $CSPStyleSrc; $CSPImgSrc; $CSPMediaSrc; ";
        $content .= $this->CSPFontSrc ? 'font-src '.$this->CSPFontSrc.'; ' : '';
        $content .= $this->CSPConnectSrc ? 'connect-src '.$this->CSPConnectSrc.'; ' : '';
        $content .= $this->CSPFormAction ? 'form-action '.$this->CSPFormAction.'; ' : '';
        $content .= $this->CSPFrameAncestors ? 'frame-ancestors '.$this->CSPFrameAncestors.'; ' : '';
        $content .= $this->CSPPluginTypes ? 'plugin-types '.$this->CSPPluginTypes.'; ' : '';
        $content .= $this->CSPObjectSrc ? 'object-src '.$this->CSPObjectSrc.'; ' : '';
        $content .= $this->CSPWorkerSrc ? 'worker-src '.$this->CSPWorkerSrc.'; ' : '';
        $content .= $this->CSPFrameSrc ? 'frame-src '.$this->CSPFrameSrc.'; ' : '';
        $content .= $this->CSPChildSrc ? 'child-src '.$this->CSPChildSrc.'; ' : '';
        $content .= $this->CSPBaseUri ? 'base-uri '.$this->CSPBaseUri.'; ' : '';
        $content .= $this->CSPSandbox ? 'sandbox '.$this->CSPSandbox.'; ' : '';
        $content .= $this->CSPReportUri ? 'report-uri '.$this->CSPReportUri.'; ' : '';
        return $content;
    }

    /* function to manage wrong passwords */
    protected function handlerWrongPassword(int $id) {
        /* init user model, and get user LOCK */
        $userModel = new User($this->conn);
        /* if not found and not create user lock, than send fail */
        if (!(($usrLock = $userModel->getUserLock($id)) || ($usrLock = $userModel->createUserLock($id)))) $this->switchFailResponse();
        /* if wrong password expire datetime is not set or is expire */
        if (!isset($usrLock[EXPIRE_TIME_WRONG_PASSWORD]) || new DateTime($usrLock[EXPIRE_TIME_WRONG_PASSWORD]) < new DateTime()){
            /* set expire time and reset wrong passwords */
            $expireDatetime = getExpireDatetime(PASS_TRY_TIME);
            $userModel->wrongPasswordsReset($id, $expireDatetime);
            /* get user lock */
            $usrLock = $userModel->getUserLock($id);
        }

        /* increment counter wrong password and set on user */
        $userModel->setCountWrongPass($id, ++$usrLock[COUNT_WRONG_PASSWORDS]);

        /* init verifier and verify user lock */
        $verifier = Verifier::getInstance([], $this->conn);
        $res = $verifier->verifyWrongPassword($usrLock[COUNT_WRONG_PASSWORDS], $usrLock[COUNT_LOCKS]);

        /* if require lock */
        if ($res[LOCK]) {
            /* reset wrong password */
            $userModel->wrongPasswordsReset($id);
            /* get lock expire time */
            $expireLock = getExpireDatetime(USER_LOCK_TIME);
            $userModel->lockUser($id, $expireLock);
            /* increment count locks and set on user */
            $userModel->setCountUserLocks($id, ++$usrLock[COUNT_LOCKS]);

        /* else if is require disable, then disable the user */
        } else if ($res[DISABLE]) $userModel->disabledUser($id);
    }

    /* SESSION FUNCTIONS */

    /* function to reset session */
    protected function resetSession() {
        session_regenerate_id();
        $_SESSION = [];
    }

    /* function to reset login session */
    protected function removeLoginSession(string $sessionToken): bool {
        /* init session model and remove lgin session token */
        $session = new Session($this->conn);
        return $session->removeLoginSessionToken($sessionToken);
    }

    /* function to create a login session */
    protected function createLoginSession(int $userId) {
        /* reset session */
        $this->resetSession();
        /* init session model and calc session expire time */
        $sessionModel = new Session($this->conn);
        $expireDatetime = getExpireDatetime(MAX_TIME_UNCONNECTED_LOGIN_SESSION);
        /* create new login session */
        $res = $sessionModel->newLoginSession($userId, $_SERVER['REMOTE_ADDR'], $expireDatetime);
        /* if fail, send error response */
        if (!$res[SUCCESS]) $this->switchFailResponse();
        /* else get domain, calc expire in unix time and set login session cookie */
        setcookie(CK_LOGIN_SESSION, $res[TOKEN], time() + (86400 * COOKIE_EXPIRE_DAYS), '/',  DOMAIN_LOGIN_SESSION_COOCKIE, $this->appConfig[SECURITY][ONLY_HTTPS], TRUE);
        /* remove old session */
        $sessionModel->removeOldLoginSessionForUser($userId, MAX_SESSIONS);
    }

    /* function to reset session if client change ip address */
    protected function resetLoginSessionIfChangeIp() {
        /* check if ip has changed */
        if ($this->loginSession->{IP_ADDRESS} !== $_SERVER['REMOTE_ADDR']) {
            /* reset login session send fail response */
            $this->resetLoginSession();
            $this->switchFailResponse($this->lang[MESSAGE][GENERIC][IP_CHANGED]);
        }
    }

    /* function to reseset login session */
    protected function resetLoginSession(): bool {
        /* reset session */
        $this->resetSession();
        /* remove login session */
        setcookie(CK_LOGIN_SESSION, '', time()-1);
        return $this->removeLoginSession($this->loginSession->{SESSION_TOKEN});
    }

    /* function to create double login session */
    protected function createDoubleLoginSession() {
        $_SESSION[DOUBLE_LOGIN_SESSION] = new DateTime();
        $_SESSION[DOUBLE_LOGIN_SESSION]->modify(DOUBLE_LOGIN_SESSION_EXPIRE_TIME);
    }

    /* function to check dobuble login */
    protected function isDoubleLoginSession(): bool {
        /* check login and, if is setted, double login session expire time */
        return $this->loginSession && isset($_SESSION[DOUBLE_LOGIN_SESSION]) && $_SESSION[DOUBLE_LOGIN_SESSION] > new DateTime();
    }

    /* function to set resend email lock */
    protected function setResendLock() {
        $_SESSION[RESEND_LOCK_EXPIRE] = new DateTime();
        $_SESSION[RESEND_LOCK_EXPIRE]->modify(RESEND_LOCK_EXPIRE_TIME);;
    }

    /* function to manage the resend lock */
    protected function handlerResendLock() {
        if (isset($_SESSION[RESEND_LOCK_EXPIRE]) && $_SESSION[RESEND_LOCK_EXPIRE] > new DateTime()) $this->switchFailResponse($this->lang[MESSAGE][GENERIC][RESEND_LOCK]);
    }

    /* USER ROLETYPE FUNCTIONS */

    /* function to check if is admin user */
    protected function isAdminUser(): bool {
        return $this->loginSession && $this->loginSession->{ROLE_ID_FRGN} === ADMIN_ROLE_ID;
    }

    /* function to check if is editor user */
    protected function isEditorUser(): bool {
        return $this->loginSession && $this->loginSession->{ROLE_ID_FRGN} === EDITOR_ROLE_ID;
    }

    /* function to check if is simple user */
    protected function isSimpleUser(): bool {
        return $this->loginSession && isSimpleUser($this->loginSession->{ROLE_ID_FRGN});
    }

    /* function to check if user can create another user */
    protected function canCreateUser(): bool {
        return (bool) $this->loginSession && $this->userRole[CAN_CREATE_USER];
    }

    /* function to check if user can update another user */
    protected function canUpdateUser(): bool {
        return (bool) $this->loginSession && $this->userRole[CAN_UPDATE_USER];
    }

    /* function to check if user can create another user */
    protected function canDeleteUser(): bool {
        return (bool) $this->loginSession && $this->userRole[CAN_DELETE_USER];
    }

    /* function to check if user can unlock another user */
    protected function canUnlockUser(): bool {
        return (bool) $this->loginSession && $this->userRole[CAN_UNLOCK_USER];
    }

    /* function to check if user can restore another user */
    protected function canRestoreUser(): bool {
        return (bool) $this->loginSession && $this->userRole[CAN_RESTORE_USER];
    }

    /* function to check if user can change password at another user */
    protected function canChangePassword(): bool {
        return (bool) $this->loginSession && $this->userRole[CAN_CHANGE_PASSWORD];
    }

    /* function to check if user can remove session */
    protected function canRemoveSession(): bool {
        return (bool) $this->loginSession && $this->userRole[CAN_REMOVE_SESSION];
    }

    /* function to check if user can remove enabler token */
    protected function canRemoveEnablerToken(): bool {
        return (bool) $this->loginSession && $this->userRole[CAN_REMOVE_ENABLER_TOKEN];
    }

    /* function to check if user can genaret rsa key pair */
    protected function canGenerateRsaKey(): bool {
        return (bool) $this->loginSession && $this->loginSession && $this->userRole[CAN_GENERATE_RSA];
    }

    /* function to check if user can genaret sitemap */
    protected function canGenerateSitemap(): bool {
        return (bool) $this->loginSession && $this->userRole[CAN_GENERATE_SITEMAP];
    }

    /* function to check if user can change settings */
    protected function canChangeSettings(): bool {
        return (bool) $this->loginSession && $this->userRole[CAN_CHANGE_SETTINGS];
    }

    /* function to check if user can send emails */
    protected function canSendEmails(): bool {
        return (bool) $this->loginSession && $this->userRole[CAN_SEND_EMAIL];
    }

    /* function to check if user can view tables */
    protected function canViewTables(): bool {
        return (bool) $this->loginSession && $this->userRole[CAN_VIEW_TABLES];
    }

    /* REDIRECT OR SEND FAIL FUNCTIONS */

    /* function to redirect or send fail if user is loggin */
    protected function sendFailIfLogin() {
        if ($this->loginSession) $this->switchFailResponse();
    }

    /* function to redirect or send fail if client is not loggin */
    protected function sendFailIfNotLogin() {
        if (!$this->loginSession) $this->switchFailResponse();
    }

    /* function to redirect or send fail if client is a simple user */
    protected function sendFailIfSimpleUser() {
        $this->sendFailIfNotLogin();
        if ($this->isSimpleUser()) $this->switchFailResponse();
    }

    /* function to redirect or send fail if client is not admin user */
    protected function sendFailIfNotAdmin() {
        $this->sendFailIfNotLogin();
        if (!$this->isAdminUser()) $this->switchFailResponse();
    }

    /* function to redirect or send fail if client is not loggin */
    protected function sendFailIfCanNotCreateUser() {
        $this->sendFailIfNotLogin();
        if (!$this->canCreateUser()) $this->switchFailResponse();
    }

    /* function to redirect or send fail if user can not update */
    protected function sendFailIfCanNotUpdateUser() {
        $this->sendFailIfNotLogin();
        if (!$this->canUpdateUser()) $this->switchFailResponse();
    }

    /* function to redirect or send fail if user can not delete */
    protected function sendFailIfCanNotDeleteUser() {
        $this->sendFailIfNotLogin();
        if (!$this->canDeleteUser()) $this->switchFailResponse();
    }

    /* funtion to redirect or send fail if email confirm is not require */
    protected function sendFailIfConfirmEmailNotRequire() {
        if (!$this->appConfig[UMS][REQUIRE_CONFIRM_EMAIL]) $this->switchFailResponse();
    }

    /* function to redirect if not XML HTTP request */
    protected function redirectIfNotXMLHTTPRequest(string $url = '/') {
        if (!isXmlhttpRequest()) {
            $_SESSION[MESSAGE] = 'FAIL';
            $_SESSION[SUCCESS] = FALSE;
            redirect($url);
        }
    }

    /* function to redirect on HTTPS connection */
    protected function redirectOnSecureConnection() {
        if (!isSecureConnection()) {
            $urlServer = str_replace("www.", "", $_SERVER['HTTP_HOST']);
            $reqUri = $_SERVER['REQUEST_URI'];
            redirect("https://$urlServer/$reqUri");
        }
    }

    /* EMAIL SENDER FUNCTIONS */

    /* function to send the enabler email */
    protected function sendEnablerEmail(string $to, string $token, string $message = 'ACTIVATE YOUR ACCOUNT', bool $newEmail = FALSE): bool {
        /* get url domain from configurations */
        $link = $this->appConfig[UMS][DOMAIN_URL_LINK];
        /* append source path and token */
        $route .= Router::getRoute('app\controllers\LoginController', $newEmail ? 'enableNewEmail' : 'enableAccount');
        $route = str_replace(':token', $token, $route);
        $link .= $route;

        /* insert link on session if on DEV mode */
        if (DEV) $_SESSION[LINK] = $link;

        /* init email model and set headers */
        $email = new Email($to, $this->appConfig[UMS][ENABLER_EMAIL_FROM]);
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=utf-8\r\n";
        $email->setHeaders($headers);
        /* set layout and data, then generate email body and send it */
        $email->setLayout(ENABLER_EMAIL_LAYOUT);
        $email->setData([
            LINK => $link,
            MESSAGE => $message
        ]);
        $email->generateContentWithLayout();
        return $email->send();
    }

    /* function to send email for password reset */
    protected function sendEmailResetPassword(string $to, string $token, string $message = 'RESET YOUR PASSWORD'): bool {
        /* get domain url from configuration, next append source path and token */
        $link = $this->appConfig[UMS][DOMAIN_URL_LINK];
        $route = Router::getRoute('app\controllers\LoginController', 'showPasswordReset');
        $route = str_replace(':token', $token, $route);
        $link .= $route;

        /* insert link on session if on DEV mode */
        if (DEV) $_SESSION[LINK] = $link;

        /* init email model and set headers */
        $email = new Email($to, $this->appConfig[UMS][ENABLER_EMAIL_FROM]);
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type:text/html;charset=utf-8' . "\r\n";
        $email->setHeaders($headers);
        /* set layout and data, then generate email body and send it */
        $email->setLayout(PASSWORD_RESET_EMAIL_LAYOUT);

        $email->setData([
            LINK => $link,
            MESSAGE => $message
        ]);
        $email->generateContentWithLayout();
        return $email->send();
    }

    /* function to send email with random password */
    protected function sendEmailNewRandomPassword(string $to, string $password): bool {
        /* view password on message session if on DEV mode */
        if (DEV) $_SESSION[MESSAGE] = "New password: $password";

        /* init email model and set headers */
        $email = new Email($to, $this->appConfig[UMS][ENABLER_EMAIL_FROM]);
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type:text/html;charset=utf-8' . "\r\n";
        $email->setHeaders($headers);
        /* set layout and data, then generate email body and send it */
        $email->setLayout(RANDOM_PASSWORD_EMAIL_LAYOUT);

        $email->setData([
            PASSWORD => $password
        ]);
        $email->generateContentWithLayout();
        return $email->send();
    }

    /* RSA CRYPT FUNCTIONS */

    /* function to get private and public key */
    protected function getKey(): array {
        /* get path of private key and read it */
        $pathFile = getPath(getcwd(),'config', 'rsa', $this->appConfig[RSA][RSA_PRIV_KEY_FILE]);
        $privKey = safeFileRead($pathFile);
        /* validate key */
        if (!($key = openssl_pkey_get_private($privKey))) $this->switchFailResponse();

        /* get key details and return it */
        $details = openssl_pkey_get_details($key);
        $keyN = toHex($details['rsa']['n']);
        $keyE = toHex($details['rsa']['e']);
        return [
            PRIV_KEY => $privKey,
            KEY_N => $keyN,
            KEY_E => $keyE
        ];
    }

    /* function to decrypt data */
    protected function decryptData(string $data): string {
        /* get path of private key, and read it */
        $pathFile = getPath(getcwd(),'config', 'rsa', $this->appConfig[RSA][RSA_PRIV_KEY_FILE]);
        $privKey = safeFileRead($pathFile);
        /* validate key */
        if (!($key = openssl_pkey_get_private($privKey))) $this->switchFailResponse();

        /* convert data from hex annotation */
        $data = pack('H*', $data);
        /* decrypt data and return it */
        $res = '';
        openssl_private_decrypt($data, $res, $key);
        return $res;
    }

    /* SOURCES FUNCTIONS */

    /* function to get attributes of css by info array */
    protected function getAttributeCss(array $css): string {
        /* create link source */
        $str = 'href="'.$css[SOURCE].'" ';
        /* append CSP nonce */
        $str .= "nonce=\"$this->CSPStyleNonce\"";
        /* if is set integrity, append it */
        $str .= isset($css[INTEGRITY]) ? ' integrity="'.$css['integrity'].'"' : '';
        /* if is set crossorigin, append it */
        $str .= isset($css[CROSSORIGIN]) ? ' crossorigin="'.$css['crossorigin'].'"' : '';
        return $str;
    }

    /* function to get attribute of javascript by info array */
    protected function getAttributeJS(array $js): string {
        /* create link source */
        $str = 'src="'.$js[SOURCE].'" ';
        /* append CSP nonce */
        $str .= "nonce=\"$this->CSPScriptNonce\"";
        /* if is set integrity, append it */
        $str .= isset($js[INTEGRITY]) ? ' integrity="'.$js['integrity'].'"' : '';
        /* if is set crossorigin, append it */
        $str .= isset($js[CROSSORIGIN]) ? ' crossorigin="'.$js['crossorigin'].'"' : '';
        return $str;
    }

    /* function to get language select by client */
    protected function getLang() {
        /* if is set langugage cookie, then return it */
        if (isset($_COOKIE[CK_LANG]) &&  in_array($_COOKIE[CK_LANG], ACCEPT_LANG_LIST)) return $_COOKIE[CK_LANG];

        /* set default result */
        $langRes = DEFAULT_LANG;

        /* get accept lang from header request */
        $accLangs = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        /* get only language */
        $accLangs = explode(';', $accLangs)[0];
        /* get language list */
        $langList = explode(',', $accLangs);
        /* iterate langs list */
        foreach ($langList as $lang) {
            /* get first specification of lang */
            $lang = explode('-', $lang)[0];
            /* if lang is accepted by server, then set a result languafe and break loop*/
            if (in_array($lang, ACCEPT_LANG_LIST)) {
                $langRes = $lang;
                break;
            }
        }
        /* set cookie and return language */
        setcookie(CK_LANG, $langRes, 0, '/', null, $this->appConfig[SECURITY][ONLY_HTTPS],FALSE);
        return $langRes;
    }

    /* function to get laguage array */
    protected function getLanguageArray(string $section='gen'): array {
        /* init language array */
        $langArray = [];
        /* get lang array default */
        $langPath = getPath('lang', DEFAULT_LANG, MESSAGE_LANG_SOURCES, "$section.msg.lang.php");
        if (file_exists($langPath)) $langArray[MESSAGE] = require_once $langPath;
        $langPath = getPath('lang', DEFAULT_LANG, DATA_LANG_SOURCES, "$section.data.lang.php");
        if (file_exists($langPath)) $langArray[DATA] = require_once $langPath;

        /* check language set by client */
        if ($this->langCli !== DEFAULT_LANG) {
            /* init lang require from cli */
            $langCli = [];
            /* merge with the lang require of client */
            $langCliPath = getPath('lang', $this->langCli, MESSAGE_LANG_SOURCES, "$section.msg.lang.php");
            if (file_exists($langCliPath)) $langCli[MESSAGE] = require_once $langCliPath;
            $langCliPath = getPath('lang', $this->langCli, DATA_LANG_SOURCES, "$section.data.lang.php");
            if (file_exists($langCliPath)) $langCli[DATA] = require_once $langCliPath;
            if (!empty($langCli)) $langArray = array_replace_recursive($langArray, $langCli);
        }
        /* return array */
        return $langArray;
    }

    /* ##################################### */
    /* PRIVATE FUNCTIONS */
    /* ##################################### */

    /* function to get a loggin session */
    private function getLoginSession() {
        /* if is set login session token on cookie */
        if (($tkn = $_COOKIE[CK_LOGIN_SESSION] ?? FALSE)) {
            /* init session model */
            $session = new Session($this->conn);
            /* get user by token and return it. If session is expire, the function return false */
            $user = $session->getUserByLoginSessionToken($tkn);
            return $user;
        }
        /* else return false */
        return FALSE;
    }

    /* function to manage login session */
    private function handlerSession() {
        /* reset login session if client change ip */
        if ($this->appConfig[SECURITY][BLOCK_CHANGE_IP]) $this->resetLoginSessionIfChangeIp();
        /* check if session is expire, then reset it */
        if (new DateTime($this->loginSession->{EXPIRE_DATETIME}) < new DateTime()) {
            $this->resetLoginSession();
            /* set result data */
            $dataOut = [
                MESSAGE => $this->lang[MESSAGE][GENERIC][EXPIRED_SESSION],
                SUCCESS => FALSE
            ];
            
            /* function to default response */
            $funcDefault = function($data) {
                $_SESSION[MESSAGE] = $data[MESSAGE];
                $_SESSION[SUCCESS] = $data[SUCCESS];
                redirect();
            };
            
            /* send session expired message */
            $this->switchResponse($dataOut, FALSE, $funcDefault);
        }
        /* init session  and set expire date time */
        $session = new Session($this->conn);
        $expireDatetime = getExpireDatetime(MAX_TIME_UNCONNECTED_LOGIN_SESSION);
        $session->setExpireLoginSession($this->loginSession->{SESSION_ID}, $expireDatetime);
    }
}

<?php
namespace app\controllers;

use \PDO;
use \DateTime;
use \Exception;
use app\models\Email;
use app\models\User;
use app\controllers\verifiers\Verifier;
use app\models\Session;
use app\models\Role;

/**
 * Class controller to implement principal properties and functions
 * @author Andrea Serra (DevAS) https://devas.info
 */
class Controller {
    protected $content;
    protected $conn;
    protected $appConfig;
    protected $layout;
    protected $tokenLogout = '';
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
    protected $isNewUser = FALSE;
    protected $isNewEmail = FALSE;
    protected $isSettings = FALSE;
    protected $isUsersList = FALSE;
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
    protected $lang = NULL;

    public function __construct(PDO $conn = NULL, array $appConfig = NULL, string $layout = DEFAULT_LAYOUT) {
        /* get config if is null */
        $this->appConfig = $appConfig ?? getConfig();
        /* if require redirect on https */
        if ($this->appConfig[SECURITY][ONLY_HTTPS]) $this->redirectOnSecureConnection();
        /* get login session */
        $this->loginSession = $this->getLoginSession();
        /* if have login session */
        if ($this->loginSession) {
            /* init role model, and get roles of login user */
            $role = new Role($this->conn);
            $this->userRole = $role->getRole($this->loginSession->{ROLE_ID_FRGN});
            $this->handlerSession();
            array_push($this->jsSrcs, [SOURCE => '/js/utils/login/logout.js']);
        }
        $this->lang = $this->getLang();
        $this->conn = $conn;
        $this->layout = getLayoutPath().'/'.$this->appConfig[LAYOUT][$layout].'.tpl.php';
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* funciton to set layout */
    public function setLayout(string $layout) {
        $this->layout = getLayoutPath().'/'.$this->appConfig[LAYOUT][$layout].'.tpl.php';
    }

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
        header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
        $this->content = view($this->appConfig[APP][PAGE_NOT_FOUND]);
    }

    /* function to view error page */
    public function showPageError(Exception $exception) {
        $data = [];
        /* if is set show exception, then view info about it */
        if ($data[SHOW_MESSAGE_EXCEPTION] = $this->appConfig[APP][SHOW_MESSAGE_EXCEPTION]) {
            $data[EXCEPTION] = [
                'toString' => $exception->__toString(),
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'previous' => $exception->getMessage(),
                'trace' => $exception->getTrace(),
                'traceString' => $exception->getTraceAsString()
            ];
        }
        $this->content = view($this->appConfig[APP][PAGE_EXCEPTION], $data);
    }

    /* function to send public key on json format */
    public function showKeyJSON() {
        /* redirect */
        $this->redirectIfNotXMLHTTPRequest();

        /* get tokens and init json result */
        $tokens = $this->getPostSessionTokens();
        $resJSON = [];
        /* if valide token, then send key */
        if ($tokens[0] === $tokens[1]) {
            /* generate new token */
            $resJSON[NEW_TOKEN] = generateToken();
            /* get key and set on result */
            $keys = $this->getKey();
            $resJSON[KEY_N] = $keys[KEY_N];
            $resJSON[KEY_E] = $keys[KEY_E];
        }

        /* send json response */
        sendJsonResponse($resJSON);
    }

    /* display content on layout */
    public function display() {
        /* if is user loggin, then generate logout token */
        if (isUserLoggedin()) $this->tokenLogout = $this->tokenLogout ?? generateToken(CSRF_LOGOUT);
        /* if CSP (Content Security Policy) is require */
        if ($this->setCSPHeader) {
            /* get CSP content and create CSP headers */
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

    /* ##################################### */
    /* PROTECTED FUNCTIONS */
    /* ##################################### */

    /* function to send response json (XML HTTP) or default */
    protected function switchFailResponse(string $message='Fail') {
        /* get request with header */
        $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
        /* switc the response according to the header */
        switch ($header) {
            /* if response is XMLHTTP, then send json response */
            case 'XMLHTTPREQUEST':
                sendJsonResponse([
                    MESSAGE => $message,
                    SUCCESS => FALSE
                ]);
                exit;
            /* default function */
            default:
                /* display fail message and exit */
                $this->showMessageAndExit($message, TRUE);
                exit;
        }
    }

    /* function to send json response if XML HTTP request or send a default response */
    protected function switchResponse(array $data, bool $generateNewToken, callable $funcDefault, string $nameToken = CSRF) {
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
    
    /* function to get tokens of session and post */
    protected function getPostSessionTokens(string $nameToken = CSRF): array {
//         $postToken = $_POST[$postTokenName] ?? 'tkn';
        /* reformat name token for header */
        $headerToken = str_replace('-', '_', $nameToken);
        $headerToken = mb_strtoupper($headerToken);
        /* get post token from header */
        $postToken = $_SERVER['HTTP_'.$headerToken] ?? 'tkn';
        /* get session token and unset it */
        $sessionToken = $_SESSION[$nameToken] ?? '';
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
        $content .= $this->CSPFontSrc ? 'font-src '.$this->CSPFontSrc.'; ': '';
        $content .= $this->CSPConnectSrc ? 'connect-src '.$this->CSPConnectSrc.'; ': '';
        $content .= $this->CSPFormAction ? 'form-action '.$this->CSPFormAction.'; ': '';
        $content .= $this->CSPFrameAncestors ? 'frame-ancestors '.$this->CSPFrameAncestors.'; ': '';
        $content .= $this->CSPPluginTypes ? 'plugin-types '.$this->CSPPluginTypes.'; ': '';
        $content .= $this->CSPObjectSrc ? 'object-src '.$this->CSPObjectSrc.'; ': '';
        $content .= $this->CSPWorkerSrc ? 'worker-src '.$this->CSPWorkerSrc.'; ': '';
        $content .= $this->CSPFrameSrc ? 'frame-src '.$this->CSPFrameSrc.'; ': '';
        $content .= $this->CSPChildSrc ? 'child-src '.$this->CSPChildSrc.'; ': '';
        $content .= $this->CSPBaseUri ? 'base-uri '.$this->CSPBaseUri.'; ': '';
        $content .= $this->CSPSandbox ? 'sandbox '.$this->CSPSandbox.'; ': '';
        $content .= $this->CSPReportUri ? 'report-uri '.$this->CSPReportUri.'; ': '';
        return $content;
    }

    /* function to manage wrong passwords */
    protected function handlerWrongPassword(int $id) {
        /* init user model, and get user id */
        $user = new User($this->conn, $this->appConfig);
        $usrLock = $user->getUserLock($id);
        /* if wrong password expire datetime is not set or is expire */
        if (!isset($usrLock->{EXPIRE_WRONG_PASSWORD}) || new DateTime($usrLock->{EXPIRE_WRONG_PASSWORD}) < new DateTime()){
            /* set expire time and reset wrong passwords*/
            $expireDatetime = getExpireDatetime($this->appConfig[UMS][PASS_TRY_TIME]);
            $user->resetWrongPasswords($id, $expireDatetime);
        }

        /* increment counter wrong password and set on user */
        $user->setCountWrongPass($id, ++$usrLock->{COUNT_WRONG_PASSWORDS});

        /* init verifier and verify user lock */
        $verifier = Verifier::getInstance($this->appConfig, $this->conn);
        $res = $verifier->verifyWrongPassword($usrLock->{COUNT_WRONG_PASSWORDS}, $usrLock->{COUNT_LOCKS});

        /* if require lock */
        if ($res[LOCK]) {
            /* reset wrong password */
            $user->resetWrongPasswords($id);
            /* get lock expire time */
            $expireLock = getExpireDatetime($this->appConfig[SECURITY][USER_LOCK_TIME]);
            $user->lockUser($id, $expireLock);
            /* increment count locks and set on user */
            $user->setCountUserLocks($id, ++$usrLock->{COUNT_LOCKS});

        /* else if is require disable, then disable the user */
        } else if ($res[DISABLE]) $user->disabledUser($id);
    }

    /* SESSION FUNCTIONS */

    /* function to reset session */
    protected function resetSession() {
        session_regenerate_id();
        $_SESSION = [];
    }

    /* function to reset login session */
    protected function removeLoginSession(string $sessionToken): bool {
        /* reset session */
        $this->resetSession();
        /* init session model and remove lgin session token */
        $session = new Session($this->conn);
        return $session->removeLoginSessionToken($sessionToken);
    }

    /* function to create a login session */
    protected function createLoginSession(int $userId) {
        /* reset session */
        $this->resetSession();
        /* init session model and calc session expire time */
        $session = new Session($this->conn);
        $expireDatetime = getExpireDatetime($this->appConfig[SECURITY][MAX_TIME_UNCONNECTED_LOGIN_SESSION]);
        /* create login session */
        $res = $session->newLoginSession($userId, $_SERVER['REMOTE_ADDR'], $expireDatetime);
        /* calc expire in unix time and set login session cookie */
        $expireUnixTime =  date_timestamp_get($expireDatetime);
        setcookie(CK_LOGIN_SESSION, $res[TOKEN], time, '/',  $expireUnixTime, $this->appConfig[SECURITY][ONLY_HTTPS], TRUE);
    }

    /* function to reset session if client change ip address */
    protected function resetLoginSessionIfChangeIp() {
        /* check if ip has changed */
        if ($this->loginSession->{IP_ADDRESS} !== $_SERVER['REMOTE_ADDR']) {
            /* remove login session */
            $this->removeLoginSession($this->loginSession->{SESSION_TOKEN});

            /* send fail response */
            $this->switchFailResponse();
        }
    }

    /* REDIRECTS OR FAIL */

    /* function to redirect if user is loggin */
    protected function redirectOrFailIfLoggin() {
        if ($this->loginSession) $this->switchFailResponse();
    }

    /* function to redirect if client is not loggin */
    protected function redirectOrFailIfNotLogin() {
        if (!$this->loginSession) $this->switchFailResponse();
    }

    /* function to redirect if client is not admin user */
    protected function redirectOrFailIfNotAdmin() {
        $this->redirectOrFailIfNotLogin();
        if ($this->loginSession->{ROLE_ID_FRGN} !== 0) $this->switchFailResponse();
    }

    /* function to redirect if client is not loggin */
    protected function redirectOrFailIfCanNotCreateUser() {
        $this->redirectOrFailIfNotLogin();
        if (!$this->userRole->{CREATE_USER}) $this->switchFailResponse();
    }

    /* function to redirect if user can not update */
    protected function redirectOrFailIfCanNotUpdateUser() {
        $this->redirectOrFailIfNotLogin();
        if (!$this->userRole->{UPDATE_USER}) $this->switchFailResponse();
    }

    /* function to redirect if user can not delete */
    protected function redirectOrFailIfCanNotDelete() {
        $this->redirectOrFailIfNotLogin();
        if (!$this->userRole->{DELETE_USER}) $this->switchFailResponse();
    }

    /* redirect if email confirm is not require */
    protected function redirectIfNotRequireConfirmEmail() {
        if (!$this->appConfig[UMS][REQUIRE_CONFIRM_EMAIL]) $this->switchFailResponse();
    }
    
    /* function to redirect if not XML HTTP request */
    protected function redirectIfNotXMLHTTPRequest(string $url = '/') {
        if (!isXmlhttpRequest()) {
            $_SESSION[MESSAGE] = 'TO CONTINUE ENABLE JAVASCRIPT';
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
        $link .= $newEmail ? '/validate/new/email/' : '/account/enable/';
        $link .= $token;

        /* insert link on session if on DEV mode */
        if (DEV) $_SESSION['link'] = $link;

        /* init email model and set headers */
        $email = new Email($to, $this->appConfig[UMS][ENABLER_EMAIL_FROM]);
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=utf-8\r\n";
        $email->setHeaders($headers);
        /* set layout and data, then generate email body and send it */
        $email->setLayout(ENABLER_EMAIL_LAYOUT);
        $email->setData(compact('link', 'message'));
        $email->generateContentWithLayout();
        return $email->send();
    }

    /* function to send email for password reset */
    protected function sendEmailResetPassword(string $to, string $token, string $message = 'RESET YOUR PASSWORD'): bool {
        /* get domain url from configuration, next append source path and token */
        $link = $this->appConfig[UMS][DOMAIN_URL_LINK];
        $link .= '/user/reset/password/' . $token;

        /* insert link on session if on DEV mode */
        if (DEV) $_SESSION['link'] = $link;

        /* init email model and set headers */
        $email = new Email($to, $this->appConfig[UMS][ENABLER_EMAIL_FROM]);
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type:text/html;charset=utf-8' . "\r\n";
        $email->setHeaders($headers);
        /* set layout and data, then generate email body and send it */
        $email->setLayout(PASSWORD_RESET_EMAIL_LAYOUT);
        $email->setData(compact('link', 'message'));
        $email->generateContentWithLayout();
        return $email->send();
    }

    /* RSA CRYPT FUNCTIONS */

    /* function to get private and public key */
    protected function getKey(string $nameKey = 'privKey'): array {
        /* get rsa configuration */
        $configRsa = $this->appConfig[RSA];
        /* get path of private key and read it */
        $pathFile = getPath(getcwd(),'config', 'rsa', $configRsa[RSA_PRIV_KEY_FILE]);
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
    protected function decryptData(string $data, string $nameKeySession = 'privKey'): string {
        /* get rsa configuration */
        $configRsa = $this->appConfig[RSA];
        /* get path of private key, and read it */
        $pathFile = getPath(getcwd(),'config', 'rsa', $configRsa[RSA_PRIV_KEY_FILE]);
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

    /* ##################################### */
    /* PRIVATE FUNCTIONS */
    /* ##################################### */

    /* function to get language select by client */
    private function getLang() {
        /* if is set langugage cookie, then return it */
        if (isset($_COOKIE[CK_LANG])) return $_COOKIE[CK_LANG];

        /* set default result */
        $langRes = DEFAULT_LANG;

        /* get langs accepted on server */
        $serverLangs = getList(ACCEPT_LANG_LIST);
        /* else get accept lang from header request */
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
            if (in_array($lang, $serverLangs)) {
                $langRes = $lang;
                break;
            }
        }
        /* set cookie and return language */
        setcookie(CK_LANG, $langRes, 0, '/', null, $this->appConfig[SECURITY][ONLY_HTTPS],TRUE);
        return $langRes;
    }

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
        if (new DateTime($this->loginSession->{EXPIRE_LOGIN_SESSION}) < new DateTime()) {
            $this->removeLoginSession($this->loginSession->{SESSION_TOKEN});

            /* set result data */
            $dataOut = [
                MESSAGE => 'Your session has expired',
                SUCCESS => FALSE
            ];
            
            /* function to default response */
            $funcDefault = function($data) {
                $_SESSION[MESSAGE] = $data[MESSAGE];
                $_SESSION[SUCCESS] = $data[SUCCESS];
                redirect();
//                 $this->showMessageAndExit($data[MESSAGE], TRUE);
            };

            /* send session expired message */
            $this->switchResponse($dataOut, FALSE, $funcDefault);
        }
        /* init session  and set expire date time */
        $session = new Session($this->conn);
        $expireDatetime = getExpireDatetime($this->appConfig[SECURITY][MAX_TIME_UNCONNECTED_LOGIN_SESSION]);
        $session->setExpireLoginSession($this->loginSession->{SESSION_ID}, $expireDatetime);
    }
}

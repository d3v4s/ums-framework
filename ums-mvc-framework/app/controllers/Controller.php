<?php
namespace app\controllers;

use \PDO;
use \DateTime;
use \Exception;
use app\models\Email;
use app\models\User;
use app\controllers\verifiers\Verifier;
use app\models\Session;

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

    public function __construct(PDO $conn = NULL, array $appConfig = NULL, string $layout = DEFAULT_LAYOUT) {
        $this->appConfig = $appConfig ?? getConfig();
        if ($this->appConfig[SECURITY][ONLY_HTTPS]) $this->redirectIfNotSecureConnection();
        $this->loginSession = $this->getLoginSession();
        if ($this->loginSession) {
            $this->handlerSession();
            array_push($this->jsSrcs, ['src' => '/js/utils/login/logout.js']);
        }
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
    public function showMessage(string $message) {
        $this->content = view('show-message', [MESSAGE => $message]);
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
            $resJSON['ntk'] = generateToken();
            /* get key and set on result */
            $keys = $this->getKey();
            $resJSON['keyN'] = $keys['keyN'];
            $resJSON['keyE'] = $keys['keyE'];
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
    protected function switchResponse(array $data, bool $generateNewToken, callable $funcDefault, string $nameToken = CSRF) {
        /* get request with header */
        $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
        /* switc the response according to the header */
        switch ($header) {
            /* if response is XMLHTTP, then send json response */
            case 'XMLHTTPREQUEST':
                if ($generateNewToken) $data['ntk'] = generateToken($nameToken);
                sendJsonResponse($data);
                break;
            /* default function */
            default:
                $funcDefault($data);
                break;
        }
    }
    
    /* function to get tokens of session and post */
    protected function getPostSessionTokens(string $nameToken = CSRF): array {
//         $postToken = $_POST[$postTokenName] ?? 'tkn';
        $headerToken = str_replace('-', '_', $nameToken);
        $headerToken = mb_strtoupper($headerToken);
        $postToken = $_SERVER['HTTP_'.$headerToken] ?? 'tkn';
        $sessionToken = $_SESSION[$nameToken] ?? '';
        unset($_SESSION[$nameToken]);
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

    /* function handler for wrong passwords */
    protected function handlerWrongPassword(int $id) {
        /* init user model, and get user id */
        $user = new User($this->conn, $this->appConfig);
        $usrLock = $user->getUserLock($id);
        /* if wrong password expire datetime is not set or is expire,
         * then set new expire datetime and resest count wrong password
         */
        if (!isset($usrLock->{EXPIRE_WRONG_PASSWORD}) || new DateTime($usrLock->{EXPIRE_WRONG_PASSWORD}) < new DateTime()){
            /* set expire time and reset wrong passwords*/
            $expireDatetime = getExpireDatetime($this->appConfig[UMS][PASSWORD_TRY_TIME]);
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
//             $nLocks = (int) $this->getUserLock($id)->{COUNT_LOCKS};
//             ++$nLocks;
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

//         $_SESSION['loggedin'] = TRUE;
//         $_SESSION['user'] = $userId;
//         if ($this->appConfig['app']['checkConnectTimeLoginSession']) {
//             $expireTime = new DateTime();
//             $expireTime->modify($this->appConfig['app']['maxTimeUnconnectedLoginSession']);
//             $_SESSION['expireTime'] = $expireTime;
//         }
    }

    /* function to get a loggin session */
    protected function getLoginSession() {
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

    /* function to reset session if client change ip address */
    protected function resetLoginSessionIfChangeIp() {
        /* check if ip has changed */
        if ($this->loginSession->{IP_ADDRESS} !== $_SERVER['REMOTE_ADDR']) {
            /* remove login session */
            $this->removeLoginSession($this->loginSession->{SESSION_TOKEN});

            /* set result data */
            $dataOut = [
                MESSAGE => 'Your IP address has changed',
                SUCCESS => FALSE
            ];

            /* function to default rresponse */
            $funcDefault = function($data) {
                $this->showMessage($data[MESSAGE]);
                exit;
            };

            $this->switchResponse($dataOut, FALSE, $funcDefault);
//             $_SESSION[MESSAGE] = 'Your IP address has changed';
//             $_SESSION[SUCCESS] = FALSE;
        }
    }

    /* REDIRECTS */

    /* function to redirect if client is not loggin */
    protected function redirectIfNotLoggin() {
        if (!$this->loginSession) redirect("/auth/login");
    }

    /* function to redirect if client is not admin user */
    protected function redirectIfNotAdmin() {
        $this->redirectIfLoggin();
        if ($this->loginSession->{ROLE_ID_FRGN} !== 0) redirect();
    }

    /* function to redirect if client is not loggin */
    protected function redirectIfCanNotCreate() {
        if (!userCanCreate()) redirect();
    }

    /* function to redirect if user can not update */
    protected function redirectIfCanNotUpdate() {
        if (!userCanUpdate()) redirect();
    }

    /* function to redirect if user can not delete */
    protected function redirectIfCanNotDelete() {
        if (!userCanCreate()) redirect();
    }

    /* function to redirect if user is loggin */
    protected function redirectIfLoggin() {
        if (isUserLoggedin()) redirect();
    }

    /* function to redirect if not SSL connection */
    protected function redirectIfNotSecureConnection() {
        if (!isSecureConnection()) {
            $urlServer = str_replace("www.", "", $_SERVER['HTTP_HOST']);
            $reqUri = $_SERVER['REQUEST_URI'];
            redirect("https://$urlServer/$reqUri");
        }
    }

    /* function to redirect if not XML HTTP request */
    protected function redirectIfNotXMLHTTPRequest(string $url = '/') {
        if (!isXmlhttpRequest()) {
            $_SESSION[MESSAGE] = 'TO CONTINUE ENABLE JAVASCRIPT';
            $_SESSION[SUCCESS] = FALSE;
            redirect($url);
        }
    }

    /* redirect if email confirm is not require */
    protected function redirectIfNotEmailConfirmRequire() {
        if (!$this->appConfig['app']['requireConfirmEmail']) redirect();
    }

    /* EMAIL SENDER FUNCTIONS */

    /* function to send the validation email */
    protected function sendEmailValidation(string $to, string $token, string $message = 'ACTIVATE YOUR ACCOUNT', bool $newEmail = FALSE): bool {
        $link = $this->appConfig['app']['useServerDomainEmailValidationLink'] ? getUrlServer() : $this->appConfig['app']['urlDomainEmailValidationLink'];;
        $link .= $newEmail ? '/validate/new/email/' : '/account/enable/';
        $link .= $token;

        $_SESSION['link'] = $link;

        $email = new Email($to, $this->appConfig['app']['emailValidationFrom']);
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type:text/html;charset=UTF-8' . "\r\n";
        $email->setHeaders($headers);
        $email->setLayout('email-validation');
        $email->setData(compact('link', 'message'));
        $email->generateContentWithLayout();
        return $email->send();
    }

    /* function to send email for password reset */
    protected function sendEmailResetPassword(string $to, string $token, string $message = 'RESET YOUR PASSWORD'): bool {
        $link = $this->appConfig['app']['useServerDomainResetPassLink'] ? getUrlServer() : $this->appConfig['app']['urlDomainResetPasswordLink'];
        $link .= '/user/reset/password/' . $token;

        $_SESSION['link'] = $link;
        
        $email = new Email($to, $this->appConfig['app']['emailResetPassFrom']);
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type:text/html;charset=UTF-8' . "\r\n";
        $email->setHeaders($headers);
        $email->setLayout('email-reset-password');
        $email->setData(compact('link', 'message'));
        $email->generateContentWithLayout();
        return $email->send();
    }

    /* function to get private and public key */
    protected function getKey(string $nameKey = 'privKey'): array {
        $configRsa = $this->appConfig['rsa'];
        if ($configRsa['rsaKeyStatic']) {
            $pathFile = getcwd() . '/config/rsa/' . $configRsa['rsaPrivKeyFile'];
            $fHandle = fopen($pathFile, 'r');
            $privKey = fread($fHandle, filesize($pathFile));
            fclose($fHandle);
            $res = openssl_pkey_get_private($privKey);
        } else {
            $config = [
                'digest_alg' => $configRsa['digestAlg'],
                'private_key_bits' => $configRsa['privateKeyBits'],
                'private_key_type' => OPENSSL_KEYTYPE_RSA
            ];
            $res = openssl_pkey_new($config);
            openssl_pkey_export($res, $privKey);
            $_SESSION[$nameKey];
        }
        $details = openssl_pkey_get_details($res);
        $keyN = toHex($details['rsa']['n']);
        $keyE = toHex($details['rsa']['e']);
        return compact('privKey', 'keyN', 'keyE');
    }

    /* function to decrypt data */
    protected function decryptData(string $data, string $nameKeySession = 'privKey'): string {
        $confApp = $this->appConfig['rsa'];
        if ($confApp['rsaKeyStatic']) {
            $pathFile = getcwd() . '/config/rsa/' . $confApp['rsaPrivKeyFile'];
            $privKey = safeFileRead($pathFile);
        } else $privKey =  $_SESSION[$nameKeySession];
        $key = openssl_pkey_get_private($privKey);
        /* conversione da annotazione hex */
        $data = pack('H*', $data);
        $res = '';
        openssl_private_decrypt($data, $res, $key);
        return $res;
    }

    /* SOURCES FUNCTIONS */

    /* function to get attributes of css by info array */
    protected function getAttributeCss(array $css): string {
        $str = 'href="'.$css['src'].'" ';
        $str .= "nonce=\"$this->CSPStyleNonce\"";
        $str .= isset($css['integrity']) ? ' integrity="'.$css['integrity'].'"' : '';
        $str .= isset($css['crossorigin']) ? ' crossorigin="'.$css['crossorigin'].'"' : '';
        return $str;
    }

    /* function to get attribute of javascript by info array */
    protected function getAttributeJS(array $js): string {
        $str = 'src="'.$js['src'].'" ';
        $str .= "nonce=\"$this->CSPScriptNonce\"";
        $str .= isset($js['integrity']) ? ' integrity="'.$js['integrity'].'"' : '';
        $str .= isset($js['crossorigin']) ? ' crossorigin="'.$js['crossorigin'].'"' : '';
        return $str;
    }

    /* ##################################### */
    /* PRIVATE FUNCTIONS */
    /* ##################################### */

    /* function to manage login session */
    private function handlerSession() {
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
                $this->showMessage($data[MESSAGE]);
                exit;
            };

            
            $this->switchResponse($dataOut, FALSE, $funcDefault);
//             $_SESSION['message'] = 'SESSION EXPIRED';
//             $_SESSION['success'] = FALSE;
//             redirect();
        }
        /* init session  and set expire date time */
        $session = new Session($this->conn);
        $expireDatetime = getExpireDatetime($this->appConfig[SECURITY][MAX_TIME_UNCONNECTED_LOGIN_SESSION]);
        $session->setExpireLoginSession($this->loginSession->{SESSION_ID}, $expireDatetime);
//         $expireTime = new DateTime();
//         $expireTime->modify($this->appConfig['app']['maxTimeUnconnectedLoginSession']);
//         $_SESSION['expireTime'] = $expireTime;
    }
}

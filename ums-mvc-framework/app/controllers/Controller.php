<?php
namespace app\controllers;

use \PDO;
use \DateTime;
use \Exception;
use app\models\Email;
use app\models\User;
use app\controllers\verifiers\Verifier;

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

    public function __construct(PDO $conn = NULL, array $appConfig = NULL, string $layout = 'default') {
        $this->appConfig = $appConfig ?? getConfig();
        if ($this->appConfig['app']['onlyHttps']) $this->redirectIfNotSecureConnection();
        if ($this->appConfig['app']['blockChangeIp']) $this->resetSessionIfChangeIp();
        if ($this->appConfig['app']['checkConnectTimeLoginSession']) $this->handlerSession();
        if (isUserLoggedin()) array_push($this->jsSrcs, ['src' => '/js/utils/login/logout.js']);
        $this->conn = $conn;
        $this->layout = getLayoutPath().'/'.$this->appConfig['layout'][$layout].'.tpl.php';
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* funciton to set layout */
    public function setLayout(string $layout) {
        $this->layout = getLayoutPath().'/'.$this->appConfig['layout'][$layout].'.tpl.php';
    }

    /* function to view the home page */
    public function showHome() {
        $this->isHome = TRUE;
        $this->keywords .= ', home';
        $this->content = view('home');
    }

    /* function to show a message on page */
    public function showMessage(string $message) {
        $this->content = view('show-message', ['message' => $message]);
    }

    /* function to send 404 code and show page not found */
    public function showPageNotFound() {
        header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
        $this->content = view($this->appConfig['app']['pageNotFound']);
    }

    /* function to view error page */
    public function showPageError(Exception $exception) {
        $data = [];
        /* if is set show exception, then view info about it */
        if ($data['showMessageException'] = $this->appConfig['app']['showMessageException']) {
            $data['exception'] = [
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
        $this->content = view($this->appConfig['app']['pageException'], $data);
    }

    /* function to send public key on json format */
    public function showKeyJSON() {
        $this->redirectIfNotXMLHTTPRequest();

        $tokens = $this->getPostSessionTokens();
        $resJSON = ['ntk' => generateToken()];
        if ($tokens[0] === $tokens[1]) {
            $keys = $this->getKey();
            $resJSON['keyN'] = $keys['keyN'];
            $resJSON['keyE'] = $keys['keyE'];
        }

        sendJsonResponse($resJSON);
    }

    public function display() {
        if (isUserLoggedin()) $this->tokenLogout = $this->tokenLogout ?? generateToken('csrfLogout');
        if ($this->setCSPHeader) {
            $this->cspContent = $this->getCSPContent();
            header("Content-Security-Policy: $this->cspContent");
            header("X-Content-Security-Policy: $this->cspContent");
            header("X-WebKit-CSP: $this->cspContent");
        }
        header("Content-Type: $this->contentType");
        header("X-Content-Type-Options: $this->XContentTypeOptions");
        header("X-XSS-Protection: $this->XXSSProtection");
        header("X-Frame-Options: $this->XFrameOptions");
        require_once $this->layout;
    }

    /* ##################################### */
    /* PROTECTED FUNCTIONS */
    /* ##################################### */
    
    /* function to send response json (XML HTTP) or default */
    protected function switchResponse(array $data, bool $generateNewToken, callable $funcDefault, string $nameToken='csrf') {
        $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
        switch ($header) {
            case 'XMLHTTPREQUEST':
                if ($generateNewToken) $data['ntk'] = generateToken($nameToken);
                sendJsonResponse($data);
                break;
            default:
                $funcDefault($data);
                break;
        }
    }
    
    /* function to get tokens of session and post */
    protected function getPostSessionTokens(string $headerToken = 'XS_TKN', string $sessionTokenName = 'csrf'): array {
        //         $postToken = $_POST[$postTokenName] ?? 'tkn';
        $headerToken = str_replace('-', '_', $headerToken);
        $headerToken = mb_strtoupper($headerToken);
        $postToken = $_SERVER['HTTP_'.$headerToken] ?? 'tkn';
        $sessionToken = $_SESSION[$sessionTokenName] ?? '';
        unset($_SESSION[$sessionTokenName]);
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
        $user = new User($this->conn, $this->appConfig);
        $usrWrongPass = $user->getUser($id);
        if (!isset($usrWrongPass->datetime_reset_wrong_password) || new DateTime($usrWrongPass->datetime_reset_wrong_password) < new DateTime()) $user->setDatetimeResetWrongPassword($id);
        
        $user->incrementWrongPass($id);
        $usrWrongPass = $user->getUser($id);
        $verifier = Verifier::getInstance($this->appConfig, $this->conn);
        $res = $verifier->verifyWrongPassword($usrWrongPass->n_wrong_password, $usrWrongPass->n_locks);
        if ($res['lock']) {
            $user->resetDatetimeAndNWrongPassword($id);
            $user->lockUser($id);
        } else if ($res['disable']) $user->disabledUser($id);
    }

    /* SESSION FUNCTIONS */

    /* function to reset session */
    protected function resetSession() {
        session_regenerate_id();
        $_SESSION = [];
    }

    /* function to create a login session */
    protected function createSessionLogin($user) {
        $this->resetSession();
        unset($user->password);
        $_SESSION['loggedin'] = TRUE;
        $_SESSION['user'] = $user;
        if ($this->appConfig['app']['checkConnectTimeLoginSession']) {
            $expireTime = new DateTime();
            $expireTime->modify($this->appConfig['app']['maxTimeUnconnectedLoginSession']);
            $_SESSION['expireTime'] = $expireTime;
        }
    }

    /* function to reset session if client change ip address */
    protected function resetSessionIfChangeIp() {
        if (!isset($_SESSION['ipAddr'])) $_SESSION['ipAddr'] = $_SERVER['REMOTE_ADDR'];
        if ($_SESSION['ipAddr'] !== $_SERVER['REMOTE_ADDR']) {
            $this->resetSession();
            $_SESSION['message'] = 'YOUR IP IS CHANGED';
            $_SESSION['success'] = FALSE;
        }
    }

    /* REDIRECTS */

    /* function to redirect if client is not loggin */
    protected function redirectIfNotLoggin() {
        if (!isUserLoggedin()) redirect("/auth/login");
    }

    /* function to redirect if client is not admin user */
    protected function redirectIfNotAdmin() {
        if (!isUserAdmin()) redirect();
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
            $_SESSION['message'] = 'TO CONTINUE ENABLE JAVASCRIPT';
            $_SESSION['success'] = FALSE;
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
        if (isUserLoggedin()) {
            /* check if session is expire, then reset it */
            if (isset($_SESSION['expireTime']) && new DateTime($_SESSION['expireTime']) < new DateTime()) {
                $this->resetSession();
                $_SESSION['message'] = 'SESSION EXPIRED';
                $_SESSION['success'] = FALSE;
                redirect();
            }
            $expireTime = new DateTime();
            $expireTime->modify($this->appConfig['app']['maxTimeUnconnectedLoginSession']);
            $_SESSION['expireTime'] = $expireTime;
        }
    }

}

<?php
namespace app\controllers;

use \PDO;
use \DateTime;
use \Exception;
use app\models\Email;
use app\models\User;
use app\controllers\verifiers\Verifier;

class Controller {
    protected $content;
    protected $conn;
    protected $appConfig;
    protected $layout;
    protected $tokenLogout = '';
    protected $setCSPHeader = TRUE;
    protected $CSPDefaultSrc = '';
    protected $CSPScriptSrc = "'self'";// https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js" ;
    protected $CSPObjectSrc = "'none'";
    protected $CSPStyleSrc = "'self'"; // https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css ";
    protected $CSPImgSrc = "'self'";
    protected $CSPMediaSrc = "'self'";
    protected $CSPFrameSrc = "'none'";
    protected $CSPChildSrc = "'none'";
    protected $CSPFontSrc = "'self'";
    protected $CSPConnectSrc = "'self'";
    protected $CSPFormAction = "'self'";
    protected $CSPSandbox = '';
    protected $CSPScriptNonce = '';
    protected $CSPPluginTypes = "'none'";
    protected $CSPReflectedXss = '';
    protected $CSPReportUri = '';
    protected $CSPFrameAncestors = "'none'";
    protected $jsSrcs = [];
    protected $cssSrcs = [];
    protected $isHome = FALSE;
    protected $isLogin = FALSE;
    protected $isSignup = FALSE;
    protected $isNewUser = FALSE;
    protected $isNewEmail = FALSE;
    protected $isSettings = FALSE;
    protected $isUsersList = FALSE;
    protected $robots = '';
    protected $googlebot = '';
    protected $title = 'UMS Framework';
    protected $contentType = 'text/html; charset=utf-8';
    protected $keywords = 'php, ums, framework, programming, development, users, management, system, user, mvc, model, view, controller';
    protected $description = 'PHP FRAMEWORK UMS - This is a framework for user management, which implements the design pattern MVC (Model-view-controller) - Developed by Andrea Serra - DevAS';

    public function __construct(PDO $conn = NULL, array $appConfig = NULL, string $layout = 'default') {
        $this->appConfig = $appConfig ?? getConfig();
        if ($this->appConfig['app']['onlyHttps']) $this->redirectIfNotSecureConnection();
        if ($this->appConfig['app']['blockChangeIp']) $this->resetSessionIfChangeIp();
        if ($this->appConfig['app']['checkConnectTimeLoginSession']) $this->manageSession();
        if (isUserLoggedin()) array_push($this->jsSrcs, ['src' => '/js/utils/login/logout.js']);
        $this->conn = $conn;
        $this->layout = getLayoutPath().'/'.$this->appConfig['layout'][$layout].'.tpl.php';
    }
    
    public function setLayout(string $layout) {
        $this->layout = getLayoutPath().'/'.$this->appConfig['layout'][$layout].'.tpl.php';
    }

    public function showHome() {
        $this->isHome = TRUE;
        $this->keywords .= ', home';
        $this->content = view('home');
    }

    public function showMessage(string $message) {
        $this->content = view('show-message', ['message' => $message]);
    }

    public function showPageNotFound() {
        $this->content = view($this->appConfig['app']['pageNotFound']);
    }

    public function showPageError(Exception $exception) {
        $data = [];
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

    public function showKeyJSON() {
        $this->redirectIfNotXMLHTTPRequest();

        $tokens = $this->getPostSessionTokens();
        $resJSON = ['ntk' => generateToken()];
        if ($tokens[0] === $tokens[1]) {
            $keys = $this->getKey();
            $resJSON['keyN'] = $keys['keyN'];
            $resJSON['keyE'] = $keys['keyE'];
        }

        echo json_encode($resJSON);
        exit;
    }

    public function display() {
        if (isUserLoggedin()) $this->tokenLogout = generateToken('csrfLogout');
        require_once $this->layout;
    }

    private function manageSession() {
        if (isUserLoggedin()) {
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
    
    protected function manageWrongPassword(int $id) {
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

    protected function resetSession() {
        session_regenerate_id();
        $_SESSION = [];
    }

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

    protected function resetSessionIfChangeIp() {
        if (!isset($_SESSION['ipAddr'])) $_SESSION['ipAddr'] = $_SERVER['REMOTE_ADDR'];
        if ($_SESSION['ipAddr'] !== $_SERVER['REMOTE_ADDR']) {
            $this->resetSession();
            $_SESSION['message'] = 'YOUR IP IS CHANGED';
            $_SESSION['success'] = FALSE;
        }
    }

    protected function redirectIfNotLoggin() {
        if (!isUserLoggedin()) redirect("/auth/login");
    }
    
    protected function redirectIfNotAdmin() {
        if (!isUserAdmin()) redirect();
    }

    protected function redirectIfCanNotCreate() {
        if (!userCanCreate()) redirect();
    }

    protected function redirectIfCanNotUpdate() {
        if (!userCanUpdate()) redirect();
    }

    protected function redirectIfCanNotDelete() {
        if (!userCanCreate()) redirect();
    }

    protected function redirectIfLoggin() {
        if (isUserLoggedin()) redirect();
    }

    protected function redirectIfNotSecureConnection() {
        if (!isSecureConnection()) {
            $urlServer = str_replace("www.", "", $_SERVER['HTTP_HOST']);
            $reqUri = $_SERVER['REQUEST_URI'];
            redirect("https://$urlServer/$reqUri");
        }
    }

    protected function redirectIfNotXMLHTTPRequest(string $url = '/') {
        if (!isXmlhttpRequest()) {
            $_SESSION['message'] = 'TO CONTINUE ENABLE JAVASCRIPT';
            $_SESSION['success'] = FALSE;
            redirect($url);
        }
    }

    protected function redirectIfNotEmailConfirmRequire() {
        if (!$this->appConfig['app']['requireConfirmEmail']) redirect();
    }

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

    protected function decryptData(string $data, string $nameKeySession = 'privKey'): string {
        $confApp = $this->appConfig['rsa'];
        if ($confApp['rsaKeyStatic']) {
            $pathFile = getcwd() . '/config/rsa/' . $confApp['rsaPrivKeyFile'];
            $privKey = safeFileRead($pathFile);
        } else $privKey =  $_SESSION[$nameKeySession]; 
        $key = openssl_pkey_get_private($privKey);
        // conversione da annotazione hex
        $data = pack('H*', $data);
        $res = '';
        openssl_private_decrypt($data, $res, $key);
        return $res;
    }

    protected function getPostSessionTokens(string $postTokenName = '_xf', string $sessionTokenName = 'csrf'): array {
        $postToken = $_POST[$postTokenName] ?? 'tkn';
        $sessionToken = $_SESSION[$sessionTokenName] ?? '';
        unset($_SESSION[$sessionTokenName], $_POST[$postTokenName]);
        return [$postToken, $sessionToken];
    }

    protected function getCSPContent(): string {
        $content = $this->CSPDefaultSrc ? 'default-src '.$this->CSPDefaultSrc.';': '';
        $content .= $this->CSPScriptSrc ? 'script-src '.$this->CSPScriptSrc.';': '';
        $content .= $this->CSPObjectSrc ? 'object-src '.$this->CSPObjectSrc.';': '';
        $content .= $this->CSPStyleSrc ? 'style-src '.$this->CSPStyleSrc.';': '';
        $content .= $this->CSPImgSrc ? 'img-src '.$this->CSPImgSrc.';': '';
        $content .= $this->CSPMediaSrc ? 'media-src '.$this->CSPMediaSrc.';': '';
        $content .= $this->CSPFrameSrc ? 'frame-src '.$this->CSPFrameSrc.';': '';
        $content .= $this->CSPChildSrc ? 'child-src '.$this->CSPChildSrc.';': '';
        $content .= $this->CSPFontSrc ? 'font-src '.$this->CSPFontSrc.';': '';
        $content .= $this->CSPConnectSrc ? 'connect-src '.$this->CSPConnectSrc.';': '';
        $content .= $this->CSPFormAction ? 'form-action '.$this->CSPFormAction.';': '';
        $content .= $this->CSPSandbox ? 'sandbox '.$this->CSPSandbox.';': '';
        $content .= $this->CSPScriptNonce ? 'script-nonce '.$this->CSPScriptNonce.';': '';
        $content .= $this->CSPPluginTypes ? 'plugin-types '.$this->CSPPluginTypes.';': '';
        $content .= $this->CSPReflectedXss ? 'reflected-xss '.$this->CSPReflectedXss.';': '';
        $content .= $this->CSPReportUri ? 'report-uri '.$this->CSPReportUri.';': '';
        $content .= $this->CSPFrameAncestors ? 'frame-ancestors '.$this->CSPFrameAncestors.';': '';
        return $content;
    }

    protected function getStringTagJS(array $js):string {
        $str = 'src="'.$js['src'].'"';
        $str .= isset($js['intgrity']) ? ' integrity="'.$js['intgr'].'"' : '';
        $str .= isset($js['crossorigin']) ? ' crossorigin="'.$js['crossorigin'].'"' : '';
        return $str;
    }
}

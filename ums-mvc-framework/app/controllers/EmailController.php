<?php
namespace app\controllers;

use app\models\Email;
use \PDO;
use app\controllers\verifiers\EmailVerifier;

class EmailController extends Controller {
    public function __construct(PDO $conn, array $appConfig, string $layout = 'ums') {
        parent::__construct($conn, $appConfig, $layout);
    }

    public function showNewEmail() {
        $this->redirectIfCanNotSendEmail();

        $this->isNewEmail = TRUE;
//         $keys = $this->getKey();

        $this->CSPScriptSrc .= " 'unsafe-inline'";
        $this->CSPStyleSrc .= " 'unsafe-inline'";
        $this->CSPImgSrc .= ' data:';
        array_push($this->jsSrcs,
            ['src' => '/js/ckeditor/ckeditor.js'],
            ['src' => '/js/crypt/jsbn.js'],
            ['src' => '/js/crypt/prng4.js'],
            ['src' => '/js/crypt/rng.js'],
            ['src' => '/js/crypt/rsa.js'],
            ['src' => '/js/utils/req-key.js'],
            ['src' => '/js/utils/validate.js'],
            ['src' => '/js/utils/ums/adm-mail.js']
        );
        
        $this->content = view('ums/admin-new-email', ['token' => generateToken()]);
    }

    public function sendEmail() {
        $this->redirectIfCanNotSendEmail();
        $this->redirectIfNotXMLHTTPRequest('/ums/email/new');

        $to = $_POST['to'] ?? '';
        $subject = $_POST['subject'] ?? '';
        $content = $_POST['content'] ?? '';
        $tokens = $this->getPostSessionTokens();

        $to = $this->decryptData($to);
        $content = $this->decryptData($content);
        if (!empty($subject)) $subject = $this->decryptData($subject);

        $from = $this->appConfig['app']['sendEmailFrom'];
        $verifier = EmailVerifier::getInstance($this->appConfig);
        $resSendEmail = $verifier->verifySendEmail($from, $to, $tokens);
        if ($resSendEmail['success']) {
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $email = isset($subject) ? new Email($to, $from, $subject) : new Email($to, $from);
            $email->setHeaders($headers);
            $email->setData(['content' => $content]);
            $email->setLayout('email');
            $email->generateContentWithLayout();
            $resSendEmail['success'] = $email->send();
            $resSendEmail['message'] = $resSendEmail['success'] ? 'Email sent successfully' : 'Sending email failed' ;
        }


        $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
        switch ($header) {
            case 'XMLHTTPREQUEST':
                $resJSON = [
                    'success' => $resSendEmail['success'],
                    'message' => $resSendEmail['message'] ?? NULL,
                    'error' => $resSendEmail['error'] ?? NULL
                ];
                if (!$resSendEmail['success']) $resJSON['ntk'] = generateToken();
                header("Content-Type: application/json");
                header("X-Content-Type-Options: nosniff");
                echo json_encode($resJSON);
                exit;
            default:
                if (isset($resSendEmail['message'])) {
                    $_SESSION['message'] = $resSendEmail['message'];
                    $_SESSION['success'] = $resSendEmail['success'];
                }
                $resSendEmail['success'] ? redirect() : redirect('/ums/email/new');
                break;
        }
    }

    private function redirectIfCanNotSendEmail() {
        if (!userCanSendEmail()) redirect();
    }
}
<?php
namespace app\controllers;

use app\models\Email;
use \PDO;
use app\controllers\verifiers\EmailVerifier;

/**
 * Class controller to manage the email sender
 * @author Andrea Serra (DevAS) https://devas.info
 */
class EmailController extends Controller {
    public function __construct(PDO $conn, array $appConfig, string $layout = 'ums') {
        parent::__construct($conn, $appConfig, $layout);
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* function to view new email interfdace */
    public function showNewEmail() {
        $this->redirectIfCanNotSendEmail();

        $this->isNewEmail = TRUE;

        $this->CSPScriptSrc .= " 'strict-dynamic'";
        $this->CSPStyleSrc .= " 'unsafe-inline' 'strict-dynamic'";
        $this->CSPImgSrc .= ' data:';
        array_push($this->jsSrcs,
//             ['src' => '/js/ckeditor/ckeditor.js'],
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

    /* function to send email */
    public function sendEmail() {
        /* redirects */
        $this->redirectIfCanNotSendEmail();
        $this->redirectIfNotXMLHTTPRequest('/ums/email/new');

        /* get tokens and post data */
        $tokens = $this->getPostSessionTokens();
        $to = $_POST['to'] ?? '';
        $subject = $_POST['subject'] ?? '';
        $content = $_POST['content'] ?? '';
        $from = $this->appConfig['app']['sendEmailFrom'];

        /* decrypt data */
        $to = $this->decryptData($to);
        $content = $this->decryptData($content);
        if (!empty($subject)) $subject = $this->decryptData($subject);

        /* get verifier instance, and che send email request */
        $verifier = EmailVerifier::getInstance($this->appConfig);
        $resSendEmail = $verifier->verifySendEmail($from, $to, $tokens);
        /* if success */
        if ($resSendEmail['success']) {
            /* if is set add subject */
            $email = isset($subject) ? new Email($to, $from, $subject) : new Email($to, $from);
            /* set header of email */
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $email->setHeaders($headers);
            /* set data and layout */
            $email->setData(['content' => $content]);
            $email->setLayout('email');
            /* generate email with select layout and send it */
            $email->generateContentWithLayout();
            $resSendEmail['success'] = $email->send();
            $resSendEmail['message'] = $resSendEmail['success'] ? 'Email sent successfully' : 'Sending email failed' ;
        }

        /* result data */
        $dataOut = [
            'success' => $resSendEmail['success'],
            'message' => $resSendEmail['message'] ?? NULL,
            'error' => $resSendEmail['error'] ?? NULL
        ];

        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data['message'])) {
                $_SESSION['message'] = $data['message'];
                $_SESSION['success'] = $data['success'];
            }
            $data['success'] ? redirect() : redirect('/ums/email/new');
        };

        $this->switchResponse($dataOut, !$resSendEmail['success'], $funcDefault);
    }

    /* ##################################### */
    /* PRIVATE FUNCTIONS */
    /* ##################################### */

    /* function to redirect if user can not send email */
    private function redirectIfCanNotSendEmail() {
        if (!userCanSendEmail()) redirect();
    }
}
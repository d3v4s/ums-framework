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
    public function __construct(PDO $conn, array $appConfig, string $layout = UMS) {
        parent::__construct($conn, $appConfig, $layout);
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* function to view new email interfdace */
    public function showNewEmail() {
        /* redirect */
        $this->redirectOrFailIfCanNotSendEmail();

        /* set location */
        $this->isNewEmail = TRUE;

//         $this->CSPScriptSrc .= " 'strict-dynamic'";
//         $this->CSPStyleSrc .= " 'unsafe-inline' 'strict-dynamic'";
//         $this->CSPImgSrc .= ' data:';
        /* add javascript source */
        array_push($this->jsSrcs,
//             [SOURCE => '/js/ckeditor/ckeditor.js'],
            [SOURCE => '/js/crypt/jsbn.js'],
            [SOURCE => '/js/crypt/prng4.js'],
            [SOURCE => '/js/crypt/rng.js'],
            [SOURCE => '/js/crypt/rsa.js'],
            [SOURCE => '/js/utils/req-key.js'],
            [SOURCE => '/js/utils/validate.js'],
            [SOURCE => '/js/utils/ums/adm-mail.js']
        );

        /* generrate token and show new email page */
        $this->content = view('ums/admin-new-email', [TOKEN => generateToken(CSRF_NEW_EMAIL)]);
    }

    /* function to send email */
    public function sendEmail() {
        /* redirects */
        $this->redirectOrFailIfCanNotSendEmail();
        $this->redirectIfNotXMLHTTPRequest('/ums/email/new');

        /* get tokens and post data */
        $tokens = $this->getPostSessionTokens();
        $to = $_POST[TO] ?? '';
        $subject = $_POST[SUBJETC] ?? '';
        $content = $_POST[CONTENT] ?? '';
        $from = $this->appConfig[APP][SEND_EMAIL_FROM];

        /* decrypt data */
        $to = $this->decryptData($to);
        $content = $this->decryptData($content);
        if (!empty($subject)) $subject = $this->decryptData($subject);

        /* get verifier instance, and che send email request */
        $verifier = EmailVerifier::getInstance($this->appConfig);
        $resSendEmail = $verifier->verifySendEmail($from, $to, $tokens);
        /* if success */
        if ($resSendEmail[SUCCESS]) {
            /* if is set add subject */
            $email = isset($subject) ? new Email($to, $from, $subject) : new Email($to, $from);
            /* set header of email */
            $headers = "MIME-Version: 1.0"."\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8"."\r\n";
            $email->setHeaders($headers);
            /* set data and layout */
            $email->setData([CONTENT => $content]);
            $email->setLayout(EMAIL_LAYOUT);
            /* generate email with select layout */
            $email->generateContentWithLayout();
            /* send email and set result */
            $resSendEmail[SUCCESS] = $email->send();
            $resSendEmail[MESSAGE] = $resSendEmail[SUCCESS] ? 'Email sent successfully' : 'Sending email failed' ;
        }

        /* result data */
        $dataOut = [
            SUCCESS => $resSendEmail[SUCCESS],
            MESSAGE => $resSendEmail[MESSAGE] ?? NULL,
            ERROR => $resSendEmail[ERROR] ?? NULL
        ];

        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data[MESSAGE])) {
                $_SESSION[MESSAGE] = $data[MESSAGE];
                $_SESSION[SUCCESS] = $data[SUCCESS];
            }
            $data[SUCCESS] ? redirect() : redirect('/ums/email/new');
        };

        $this->switchResponse($dataOut, (!$resSendEmail[SUCCESS] && $resSendEmail[GENERATE_TOKEN]), $funcDefault);
    }

    /* ##################################### */
    /* PRIVATE FUNCTIONS */
    /* ##################################### */

    /* function to redirect if user can not send email */
    private function redirectOrFailIfCanNotSendEmail() {
        if (!$this->userRole->{CAN_SEND_EMAIL}) $this->switchFailResponse();
    }
}
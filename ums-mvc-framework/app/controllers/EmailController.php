<?php
namespace app\controllers;

use app\models\Email;
use \PDO;
use app\controllers\verifiers\EmailVerifier;
use app\core\Router;

/**
 * Class controller to manage the email sender
 * @author Andrea Serra (DevAS) https://devas.info
 */
class EmailController extends UMSBaseController {
    public function __construct(PDO $conn, array $appConfig, string $layout=UMS_LAYOUT) {
        parent::__construct($conn, $appConfig, $layout);
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* function to view new email interfdace */
    public function showNewEmail() {
        /* redirect */
        $this->sendFailIfCanNotSendEmail();

        /* set location */
        $this->isNewEmail = TRUE;

//         $this->CSPScriptSrc .= " 'strict-dynamic'";
//         $this->CSPStyleSrc .= " 'unsafe-inline' 'strict-dynamic'";
//         $this->CSPImgSrc .= ' data:';
        /* add javascript source */
        array_push($this->jsSrcs,
//             [SOURCE => '/js/ckeditor/ckeditor.js'],
            [SOURCE => '/js/utils/validate.js'],
            [SOURCE => '/js/utils/ums/send-email.js']
        );

        /* generrate token and show new email page */
        $this->content = view(getPath('ums', 'send-email'), [
            TOKEN => generateToken(CSRF_NEW_EMAIL),
            GET_KEY_TOKEN => generateToken(CSRF_KEY_JSON),
            TO => $_GET[TO] ?? ''
        ]);
    }

    /* function to send email */
    public function sendEmail() {
        /* redirects */
        $this->sendFailIfCanNotSendEmail();
        $this->redirectIfNotXMLHTTPRequest('/'.NEW_EMAIL_ROUTE);

        /* get tokens and post data */
        $tokens = $this->getPostSessionTokens(CSRF_NEW_EMAIL);
        $to = $_POST[TO] ?? '';
        $subject = $_POST[SUBJETC] ?? 'No subject';
        $content = $_POST[CONTENT] ?? '';
        $from = $this->appConfig[APP][SEND_EMAIL_FROM] ?? '';

        /* decrypt data */
        $to = $this->decryptData($to);
        $content = $this->decryptData($content);
        if (!empty($subject)) $subject = $this->decryptData($subject);

        /* set redirect to */
        $redirectTo = Router::getRoute('app\controllers\EmailController', 'showNewEmail');

        /* get verifier instance, and che send email request */
        $verifier = EmailVerifier::getInstance($this->lang[MESSAGE]);
        $resSendEmail = $verifier->verifySendEmail($from, $to, $content, $tokens);
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
            if ($resSendEmail[SUCCESS] = $email->send()) {
                $resSendEmail[MESSAGE] = $this->lang[MESSAGE][SEND_EMAIL][SUCCESS].$to;
                $redirectTo = Router::getRoute('app\controllers\UMSBaseController', 'showUmsHome');
            } else  $resSendEmail[MESSAGE] = $this->lang[MESSAGE][SEND_EMAIL][FAIL];
        }

        /* result data */
        $dataOut = [
            REDIRECT_TO => $redirectTo,
            SUCCESS => $resSendEmail[SUCCESS],
            ERROR => $resSendEmail[ERROR] ?? NULL,
            MESSAGE => $resSendEmail[MESSAGE] ?? NULL
        ];

        /* function for default response */
        $funcDefault = function($data) {
            if (isset($data[MESSAGE])) {
                $_SESSION[MESSAGE] = $data[MESSAGE];
                $_SESSION[SUCCESS] = $data[SUCCESS];
            }
            redirect($data[REDIRECT_TO]);
        };

        $this->switchResponse($dataOut, (!$resSendEmail[SUCCESS] && $resSendEmail[GENERATE_TOKEN]), $funcDefault, CSRF_NEW_EMAIL);
    }
}
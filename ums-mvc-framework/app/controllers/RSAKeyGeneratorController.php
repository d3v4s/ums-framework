<?php
namespace app\controllers;

use \PDO;
use app\controllers\verifiers\RSAVerifier;

require_once __DIR__.'/../../autoload.php';
require_once __DIR__.'/../../helpers/functions.php';

/**
 * Class controller to manger rsa key pair generator
 * @author Andrea Serra (DevAS) https://devas.info
 */
class RSAKeyGeneratorController extends Controller {
    public function __construct(PDO $conn, array $appConfig, string $layout = 'ums') {
        parent::__construct($conn, $appConfig, $layout);
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* function to view rsa key pair generator page */
    public function showRSAKeyGenerator() {
        /* redirect */
        $this->redirectIfCanNotGenerateRsaKey();

        /* set location */
        $this->isRSAGenerator = TRUE;

        /* add javascript sources */
        array_push($this->jsSrcs,
            ['src' => '/js/utils/ums/adm-rsa.js']
        );

        $this->content = view('ums/admin-rsa-generator', ['token' => generateToken('crsfRSA')]);
    }

    /* function to generate a new rsa key pair */
    public function rsaKeyGenerate() {
        /* redirects */
        $this->redirectIfCanNotGenerateRsaKey();
        $this->redirectIfNotXMLHTTPRequest('/ums/generator/rsa');

        /* get tokens */
        $tokens = $this->getPostSessionTokens('XS_TKN', 'crsfRSA');

        /* get verifier instance, and check gerate rsa key request */
        $verifier = RSAVerifier::getInstance($this->appConfig);
        $resKeyGenerate = $verifier->verifyKeyGenerate($tokens);
        /* if success */
        if ($resKeyGenerate['success']) {
            /* set rsa configuration */
            $confRsa = $this->appConfig['rsa'];
            $config = [
                "digest_alg" => $confRsa['digestAlg'],
                "private_key_bits" => $confRsa['privateKeyBits'],
                "private_key_type" => OPENSSL_KEYTYPE_RSA
            ];
            /* generate a key pair, and set success message */
            $keyPair = $this->generateRsaKey($config);
            $resKeyGenerate['message'] = 'Key pair successfully generated';
        }

        /* result data */
        $dataOut = [
            'success' => $resKeyGenerate['success'],
            'message' => $resKeyGenerate['message'] ?? NULL,
            'error' => $resKeyGenerate['error'] ?? NULL,
            'keyPair' => $keyPair ?? NULL,
            'oldTok' => $tokens
        ];

        /* function to default response */
        $funcDefault = function($data) {
            redirect('/ums/generator/rsa');
        };

        $this->switchResponse($dataOut, TRUE, $funcDefault, 'crsfRSA');

//         $resJSON = [
//             'success' => $resKeyGenerate['success'],
//             'message' => $resKeyGenerate['message'] ?? NULL,
//             'error' => $resKeyGenerate['error'] ?? NULL,
//             'keyPair' => $keyPair ?? NULL,
//             'ntk' => generateToken('crsfRSA'),
//             'oldTok' => $tokens
//         ];
//         sendJsonResponse($resJSON);
    }

    /* function to generate and save a key on server */
    public function rsaKeyGenerateSave() {
        /* redirect */
        $this->redirectIfNotAdmin();

        /* get tokens */
        $tokens = $this->getPostSessionTokens('XS_TKN_GS', 'csrfGenSave');

        /* get instance of verifier and switch by section */
        $verifier = RSAVerifier::getInstance($this->appConfig);
        $resKeySave = $verifier->verifyKeyGenerateSave($tokens);
        /* if success */
        if ($resKeySave['success']) {
            /* set rsa configuration */
            $confRsa = $this->appConfig['rsa'];
            $config = [
                "digest_alg" => $confRsa['digestAlg'],
                "private_key_bits" => $confRsa['privateKeyBits'],
                "private_key_type" => OPENSSL_KEYTYPE_RSA,
            ];
            /* generate and save rsa key pair */
            $resKeySave['success'] = $this->saveRsaPrivKey($this->generateRsaKey($config)['privKey']);
            /* set message by key pair generate success */ 
            $resKeySave['message'] = $resKeySave['success'] ? 'Rsa private key saved successfully' : 'Save rsa key failed';
        }

        /* result data */
        $dataOut = [
            'success' => $resKeySave['success'],
            'message' => $resKeySave['message'] ?? NULL,
            'error'=> $resKeySave['error'] ?? NULL
        ];

        /* function for default response */
        $funcDefault = function($data) {
            $this->showMessage(strtoupper($data['message']));
        };

        $this->switchResponse($dataOut, TRUE, $funcDefault, 'csrfGenSave');

//         $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
//         switch ($header) {
//             case 'XMLHTTPREQUEST':
//                 $resJSON = 
//                 header("Content-Type: application/json");
//                 header("X-Content-Type-Options: nosniff");
//                 echo json_encode($resJSON);
//                 exit;
//             default:
                
//                 return;
//         }
    }

    /* ##################################### */
    /* PRIVATE FUNCTIONS */
    /* ##################################### */

    /* function to redirect if user can not generate rsa key pair */ 
    private function redirectIfCanNotGenerateRsaKey() {
        if (!userCanGenerateRsaKey()) redirect();
    }

    /* function to save rsa private key */
    private function saveRsaPrivKey($privKey): bool {
        return safeFileRewrite(getcwd() . '/config/rsa/' . $this->appConfig['rsa']['rsaPrivKeyFile'], $privKey);
    }

    /* function to generate rsa key pair */
    private function generateRsaKey(array $configRsa): array {
        /* require new private key */
        $res = openssl_pkey_new($configRsa);
        $privKey = '';
        /* export private key and result */
        openssl_pkey_export($res, $privKey);
        /* get details from result, and get public key */
        $details = openssl_pkey_get_details($res);
        $publKey = $details['key'];
        /* return private and public key */
        return compact('privKey', 'publKey');
    }
}
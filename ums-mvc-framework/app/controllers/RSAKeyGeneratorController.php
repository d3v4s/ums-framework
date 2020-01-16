<?php
namespace app\controllers;

use \PDO;
use app\controllers\verifiers\RSAVerifier;

/**
 * Class controller to manger rsa key pair generator
 * @author Andrea Serra (DevAS) https://devas.info
 */
class RSAKeyGeneratorController extends SettingsBaseController {
    public function __construct(PDO $conn, array $appConfig, string $layout=SETTINGS_LAYOUT) {
        parent::__construct($conn, $appConfig, $layout);
    }

    /* ##################################### */
    /* PUBLIC FUNCTIONS */
    /* ##################################### */

    /* ########## SHOW FUNCTIONS ########## */

    /* function to view rsa key pair generator page */
    public function showRSAKeyGenerator() {
        /* redirect */
        $this->redirectOrFailIfCanNotGenerateRsaKey();

        /* add javascript sources */
        array_push($this->jsSrcs,
            [SOURCE => '/js/utils/ums/rsa-gen.js']
        );

        $this->content = view(getPath('ums', 'rsa-generator'), [TOKEN => generateToken(CSRF_GEN_RSA)]);
    }

    /* ########## ACTION FUNCTIONS ########## */

    /* function to generate a new rsa key pair */
    public function generateRsaKey() {
        /* redirects */
        $this->redirectOrFailIfCanNotGenerateRsaKey();
        $this->redirectIfNotXMLHTTPRequest('/'.RSA_GENERATOR_ROUTE);

        /* get tokens */
        $tokens = $this->getPostSessionTokens(CSRF_GEN_RSA);

        /* get verifier instance, and check gerate rsa key request */
        $verifier = RSAVerifier::getInstance();
        $resKeyGenerate = $verifier->verifyGenerateKey($tokens);
        /* if success */
        if ($resKeyGenerate[SUCCESS]) {
            /* set rsa configuration */
            $config = [
                "digest_alg" => DIGEST_ALG,
                "private_key_bits" => PRIVATE_KEY_BITS,
                "private_key_type" => OPENSSL_KEYTYPE_RSA
            ];
            /* generate a key pair, and set success message */
            $keyPair = $this->generateKey($config);
            $resKeyGenerate[MESSAGE] = 'Key pair successfully generated';
        }

        /* result data */
        $dataOut = [
            SUCCESS => $resKeyGenerate[SUCCESS],
            MESSAGE => $resKeyGenerate[MESSAGE] ?? NULL,
            KEY_PAIR => $keyPair ?? NULL
        ];

        /* function to default response */
        $funcDefault = function($data) {
            if (isset($data[MESSAGE])) {
                $_SESSION[MESSAGE] = $data[MESSAGE];
                $_SESSION[SUCCESS] = $data[SUCCESS];
            }
            redirect('/'.RSA_GENERATOR_ROUTE);
        };

        $this->switchResponse($dataOut, $resKeyGenerate[GENERATE_TOKEN], $funcDefault, CSRF_GEN_RSA);

    }

    /* function to generate and save a key on server */
    public function generateSaveRsaKey() {
        /* redirect */
        $this->redirectOrFailIfCanNotGenerateRsaKey();

        /* get tokens */
        $tokens = $this->getPostSessionTokens(CSRF_GEN_SAVE_RSA);

        /* get instance of verifier and switch by section */
        $verifier = RSAVerifier::getInstance();
        $resKeySave = $verifier->verifyKeyGenerateSave($tokens);
        /* if success */
        if ($resKeySave[SUCCESS]) {
            /* set rsa configuration */
            $config = [
                "digest_alg" => DIGEST_ALG,
                "private_key_bits" => PRIVATE_KEY_BITS,
                "private_key_type" => OPENSSL_KEYTYPE_RSA,
            ];
            /* generate and save rsa key pair */
            $resKeySave[SUCCESS] = $this->saveRsaPrivKey($this->generateKey($config)[PRIV_KEY]);
            /* set message by key pair generate success */ 
            $resKeySave[MESSAGE] = $resKeySave[SUCCESS] ? 'Rsa private key saved successfully' : 'Save rsa key failed';
        }

        /* result data */
        $dataOut = [
            SUCCESS => $resKeySave[SUCCESS],
            MESSAGE => $resKeySave[MESSAGE] ?? NULL
        ];

        /* function for default response */
        $funcDefault = function($data) {
            $this->showMessage(strtoupper($data[MESSAGE]));
        };

        $this->switchResponse($dataOut, $resKeySave[GENERATE_TOKEN], $funcDefault, CSRF_GEN_SAVE_RSA);
    }

    /* ##################################### */
    /* PRIVATE FUNCTIONS */
    /* ##################################### */

    /* function to redirect if user can not generate rsa key pair */ 
    private function redirectOrFailIfCanNotGenerateRsaKey() {
        if (!$this->canGenerateRsaKey()) redirect();
    }

    /* function to save rsa private key */
    private function saveRsaPrivKey($privKey): bool {
        return safeFileRewrite(getPath(getcwd(), 'config', 'rsa', $this->appConfig[RSA][RSA_PRIV_KEY_FILE]), $privKey);
    }

    /* function to generate rsa key pair */
    private function generateKey(array $configRsa): array {
        /* require new private key */
        $res = openssl_pkey_new($configRsa);
        $privKey = '';
        /* export private key and result */
        openssl_pkey_export($res, $privKey);
        /* get details from result, and get public key */
        $details = openssl_pkey_get_details($res);
        $publKey = $details['key'];
        /* return private and public key */
        return [
            PRIV_KEY => $privKey,
            PUBL_KEY => $publKey
        ];
    }
}
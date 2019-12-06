<?php
namespace app\controllers;

use \PDO;
use app\controllers\verifiers\RSAVerifier;

require_once __DIR__.'/../../autoload.php';
require_once __DIR__.'/../../helpers/functions.php';

class RSAKeyGeneratorController extends Controller {
    public function __construct(PDO $conn, array $appConfig, string $layout = 'ums') {
        parent::__construct($conn, $appConfig, $layout);
    }

    public function showRSAKeyGenerator() {
        $this->redirectIfCanNotGenerateRsaKey();

        $this->isRSAGenerator = TRUE;
        array_push($this->jsSrcs,
            ['src' => '/js/utils/ums/adm-rsa.js']
        );

        $this->content = view('ums/admin-rsa-generator', ['token' => generateToken('crsfRSA')]);
    }

    public function rsaKeyGenerate() {
        $this->redirectIfCanNotGenerateRsaKey();
        $this->redirectIfNotXMLHTTPRequest('/ums/generator/rsa');

        $tokens = $this->getPostSessionTokens('_xf', 'crsfRSA');
        $verifier = RSAVerifier::getInstance($this->appConfig);
        $resKeyGenerate = $verifier->verifyKeyGenerate($tokens);
        if ($resKeyGenerate['success']) {
            $confRsa = $this->appConfig['rsa'];
            $config = [
                "digest_alg" => $confRsa['digestAlg'],
                "private_key_bits" => $confRsa['privateKeyBits'],
                "private_key_type" => OPENSSL_KEYTYPE_RSA
            ];
            $resKeyGenerate['message'] = 'Key pair successfully generated';
            $keyPair = $this->generateRsaKey($config);
        }

        $resJSON = [
            'success' => $resKeyGenerate['success'],
            'message' => $resKeyGenerate['message'] ?? NULL,
            'error' => $resKeyGenerate['error'] ?? NULL,
            'keyPair' => $keyPair ?? NULL,
            'ntk' => generateToken('crsfRSA'),
            'oldTok' => $tokens
        ];
        echo json_encode($resJSON);
        exit;
    }

    public function rsaKeyGenerateSave() {
        $this->redirectIfNotAdmin();

        $tokens = $this->getPostSessionTokens('_xfgs', 'csrfGenSave');
        $verifier = RSAVerifier::getInstance($this->appConfig);
        $resKeySave = $verifier->verifyKeyGenerateSave($tokens);
        if ($resKeySave['success']) {
            $confRsa = $this->appConfig['rsa'];
            $config = [
                "digest_alg" => $confRsa['digestAlg'],
                "private_key_bits" => $confRsa['privateKeyBits'],
                "private_key_type" => OPENSSL_KEYTYPE_RSA,
            ];
            $resKeySave['success'] = $this->saveRsaPrivKey($this->generateRsaKey($config)['privKey']);
            $resKeySave['message'] = $resKeySave['success'] ? 'Rsa private key saved successfully' : 'Save rsa key failed';
        }

        $header = strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
        switch ($header) {
            case 'XMLHTTPREQUEST':
                $resJSON = [
                    'success' => $resKeySave['success'],
                    'message' => $resKeySave['message'] ?? NULL,
                    'error'=> $resKeySave['error'] ?? NULL,
                    'ntk' => generateToken('csrfGenSave')
                ];
                header("Content-Type: application/json");
                header("X-Content-Type-Options: nosniff");
                echo json_encode($resJSON);
                exit;
            default:
                $this->showMessage(strtoupper($resKeySave['message']));
                return;
        }
    }

    private function redirectIfCanNotGenerateRsaKey() {
        if (!userCanGenerateRsaKey()) redirect();
    }

    private function saveRsaPrivKey($privKey): bool {
        return safeFileRewrite(getcwd() . '/config/rsa/' . $this->appConfig['rsa']['rsaPrivKeyFile'], $privKey);
    }

    private function generateRsaKey(array $configRsa): array {
        $res = openssl_pkey_new($configRsa);
        $privKey = '';
        openssl_pkey_export($res, $privKey);
        $details = openssl_pkey_get_details($res);
        $publKey = $details['key'];
        return compact('privKey', 'publKey');
    }
}
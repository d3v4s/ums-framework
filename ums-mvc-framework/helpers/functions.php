<?php

/* function to send a json response */
function sendJsonResponse(array $data) {
    /* set headers for application json and no sniff content */
    header("Content-Type: application/json");
    header("X-Content-Type-Options: nosniff");
    /* print json format and exit */
    echo json_encode($data);
    exit;
}

function isSecureConnection(): bool {
    return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
}

function isXmlhttpRequest(): bool {
    return strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHTTPREQUEST';
}

function verifyNumVarRange(int $var, int $min, int $max): bool {
    if ($var < $min || $var > $max) return FALSE;
    return TRUE;
}

function writeFileIni(array $data, string $filename): bool {
    $res = array();
    foreach($data as $key => $val) {
        if(is_array($val)) {
            $res[] = "[$key]";
            foreach($val as $skey => $sval) $res[] = "$skey = ".(is_numeric($sval) ? $sval : '"'.$sval.'"');
            $res[] = '';
        } else $res[] = "$key = ".(is_numeric($val) ? $val : '"'.$val.'"');
    }
    return safeFileRewrite($filename, implode("\r\n", $res));
}

function safeFileRewrite(string $filename, $dataToSave): bool {
    $success = FALSE;
    if ($fHandle = fopen($filename, 'w')) {
        do {
            $canWrite = flock($fHandle, LOCK_EX);
            if (!$canWrite) usleep(round(rand(0, 10)*1000));
        } while (!$canWrite);
    
        fwrite($fHandle, $dataToSave);
        flock($fHandle, LOCK_UN);
        $success = TRUE;
        fclose($fHandle);
    }
    return $success;
}

function safeFileRead(string $filename) {
    $text = FALSE;
    if ($fHandle = fopen($filename, 'r')) {
        do {
            $canWrite = flock($fHandle, LOCK_EX);
            if (!$canWrite) usleep(round(rand(0, 10)*1000));
        } while (!$canWrite);

        $text = fread($fHandle, filesize($filename));
        flock($fHandle, LOCK_UN);
        fclose($fHandle);
    }
    return $text;
}

function modifyRegexJS(string &$phpRegex) {
    $phpRegex = trim($phpRegex, '/');
}

function toHex(string $data): string {
    return strtoupper(bin2hex($data));
}

function escapeHtmlDataView(array &$data, bool $escapeKey = FALSE) {
    $newData = [];
    foreach ($data as $key => $val) {
        if (substr($key, 0, 1) === '_'){
            $newData[$key] = $val;
            continue;
        }
        if ($escapeKey) $key = htmlspecialchars($key);
        if (is_string($val)) $newData[$key] = htmlspecialchars($val);
        elseif (is_object($val)) {
            escapeHtmlObjView($val);
            $newData[$key] = $val;
        }
        else if (is_array($val)) {
            escapeHtmlDataView($val, TRUE);
            $newData[$key] = $val;
        } else $newData[$key] = $val;
    }
    $data = $newData;
}

function escapeHtmlObjView(object &$object) {
    $newObj = new stdClass();
    foreach ($object as $key => $val) {
        if (is_string($val)) $newObj->$key = htmlspecialchars($val);
        elseif (is_object($val)) {
            escapeHtmlObjView($val);
            $newObj->$key = $val;
        }
        else if (is_array($val)) {
            escapeHtmlDataView($val);
            $newObj->$key = $val;
        } else $newObj->$key = $val;
    }
    $object = $newObj;
}

function view(string $view, array $data = []) {
    escapeHtmlDataView($data);
    extract($data);
    
    ob_start();
    require getViewsPath()."/$view.tpl.php";
    $content = ob_get_contents();
    ob_end_clean();
    
    return $content;
}

function getList(string $nameList): array {
    $lists = require getcwd().'/config/lists.php';
    return $lists[$nameList];
}

function getRoutes(): array {
    return require getcwd().'/config/app.routes.php';
}

function getConfig(string $section = NULL): array {
    $configApp = parse_ini_file(getcwd().'/config/config.ini', TRUE);
    if ($section === NULL) return $configApp;
    return $configApp[$section];
}

function dd($data) {
    var_dump($data);
    die;
}

function redirect(string $url = '/') { 
    header('Location: '.$url);
    exit;
}

function generateToken(string $name = 'csrf'): string {
//     if (isset($_SESSION[$name])) return $_SESSION[$name];
    $token = getSecureRandomString();
    $_SESSION[$name] = $token;
    return $token;
}

function getSecureRandomString(): string {
    return bin2hex(random_bytes(32));
}

function getDomain(string $url): string {
    $result = parse_url($url);

    $domain = $result['scheme']."://".$result['host'];
    if ($result['port'] !== 80) $domain .= ':' . $result['port']; 

    return $domain;
}

function siteMapExists(): bool {
    return file_exists(getcwd().'/public/sitemap.xml');
}

function getViewsPath(): string {
    return getcwd().'/app/views';
}

function getLayoutPath(): string {
    return getcwd().'/layout';
}

function getUrlServer(): string {
    return (isSecureConnection() ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'];
}

function isUserLoggedin(): bool {
    return $_SESSION['loggedin'] ?? FALSE;
}

function getUserLogged() {
    return $_SESSION['user'] ?? NULL;
}

function getUserLoggedFullName(): string {
    return $_SESSION['user']->name ?? '';
}

function getUserLoggedUsername(): string {
    return $_SESSION['user']->username ?? '';
}

function getUserLoggedEmail(): string {
    return $_SESSION['user']->email ?? '';
}

function getUserLoggedRole(): string {
    return $_SESSION['user']->roletype ?? '';
}

function getUserLoggedID(): string {
    return $_SESSION['user']->id ?? '';
}

function getUserLoggedNewEmail() {
    return $_SESSION['user']->new_email ?? FALSE;
}

function getUserLoggedTokenConfirmEmail() {
    return $_SESSION['user']->token_confirm_email ?? FALSE;
}

function isUserAdmin(): bool {
    return getUserLoggedRole() === 'admin';
}

function isUserEditor(): bool {
    return getUserLoggedRole() === 'editor';
}

function isUser(): bool {
    return getUserLoggedRole() === 'user';
}

function isNotSimpleUser(): bool {
    return isUserLoggedin() && !isUser();
}

function userCanCreate(): bool {
    return isUserAdmin();
}

function userCanUpdate(): bool {
    return isUserAdmin() || isUserEditor();
}

function userCanDelete(): bool {
    return isUserAdmin();
}

function userCanChangePasswords(): bool {
    return isUserAdmin();
}

function userCanGenerateRsaKey(): bool {
    return isUserAdmin();
}

function userCanGenerateSiteMap(): bool {
    return isUserAdmin();
}

function userCanChangeSettings(): bool {
    return isUserAdmin();
}

function userCanSendEmail(): bool {
    return isUserAdmin();
}

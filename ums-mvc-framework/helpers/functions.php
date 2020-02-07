<?php

/* function for debug print vardump with stack trace, and exit */
function dd($data) {
    /* print variable dump */
    var_dump($data);
    /* print stack trace */
    debug_print_backtrace();
    die;
}

/* functions that create a path for OS */
function getPath(string $pathstart, string ...$others) {
    /* iterate others and create a path */
    foreach ($others as $val) $pathstart .= DIRECTORY_SEPARATOR . $val;
    /* return path */
    return $pathstart;
}

/* function to get a url of server */
function getServerUrl(): string {
    return (isSecureConnection() ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'];
}

/* function to check if is XML HTTP request */
function isXmlhttpRequest(): bool {
    return strtoupper($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHTTPREQUEST';
}

/* function that check if is a secure connection */
function isSecureConnection(): bool {
    return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
}

/* function to redirect at new url */
function redirect(string $url='/'.HOME_ROUTE) {
    header('Location: '.$url);
    exit;
}

/* function to calculate expire time */
function getExpireDatetime(string $addTime) {
    $datetime = new DateTime();
    $datetime->modify($addTime);
    return $datetime->format('Y-m-d H:i:s');
}

/* function to read a file using lock */
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

/* function to write a file using lock */
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

/* fucntion to send 404 code error and exit*/
function send404() {
    http_response_code(404);
    exit;
}

/* fucntion to send code and exit */
function sendCode(int $code) {
    http_response_code($code);
    exit;
}

/* function to send a json response and exit */
function sendJsonResponse(array $data) {
    /* set headers for application json and no sniff content */
    header("Content-Type: application/json");
    header("X-Content-Type-Options: nosniff");
    /* print json format and exit */
    echo json_encode($data);
    exit;
}

/* function to get a sender email link */
function getSendEmailLink(bool $canSendEmail): string {
    return $canSendEmail ? '/'.NEW_EMAIL_ROUTE.'?to=' : 'mailto:';
}

/* function to verify if a number is in a range */
function verifyNumVarRange(int $var, int $min, int $max): bool {
    if ($var < $min || $var > $max) return FALSE;
    return TRUE;
}

/* function to write a ini file */
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

/* function format the regex for javascript */
function modifyRegexJS(string $phpRegex) {
    return trim($phpRegex, '/');
}

/* function to convert a string to hexadecimal */
function toHex(string $data): string {
    return strtoupper(bin2hex($data));
}

/* function to escape html special char from data to be view */
function escapeHtmlDataView(array &$data, bool $escapeKey = FALSE) {
    $newData = [];
    foreach ($data as $key => $val) {
        if (substr($key, 0, mb_strlen(NO_ESCAPE)) === NO_ESCAPE){
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

/* function to escape html special char from object */
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

/* function to get a content to be view */
function view(string $view, array $data = []) {
    escapeHtmlDataView($data);
    extract($data);
    ob_start();
    require getPath(getViewsPath(), "$view.tpl.php");
    $content = ob_get_contents();
    ob_end_clean();
    
    return $content;
}

/* function to get routes */
function getRoutes(): array {
    return require getPath(getcwd(), 'config', 'app.routes.php');
}

/* function to get configuration from file */
function getConfig(string $section = NULL): array {
    $configApp = parse_ini_file(getPath(getcwd(), 'config', 'config.ini'), TRUE);
    if ($section === NULL) return $configApp;
    return $configApp[$section];
}

/* function to genetate csrf token */
function generateToken(string $name = CSRF): string {
    /* get token */
    $token = isset($_SESSION[$name]) ? $_SESSION[$name][TOKEN] : getSecureRandomString();
    /* set token on session */
    $_SESSION[$name][TOKEN] = $token;
    $_SESSION[$name][EXPIRE_DATETIME] = new DateTime();
    $_SESSION[$name][EXPIRE_DATETIME]->modify(CSRF_TOKEN_EXPIRE_TIME);
    /* return token */
    return $token;
}

/* function to get a secure random string */
function getSecureRandomString(int $lenght=32): string {
    return bin2hex(random_bytes($lenght));
}

/* function to get a domani from url */
function getDomain(string $url): string {
    $result = parse_url($url);

    $domain = $result['scheme']."://".$result['host'];
    if (!($result['port'] === 80 || $result['port'] === 443)) $domain .= ':' . $result['port'];

    return $domain;
}

/* function to check if site map exists */
function siteMapExists(): bool {
    return file_exists(getPath(getcwd(), 'public', 'sitemap.xml'));
}

/* function to get the path of views */
function getViewsPath(): string {
    return getPath(getcwd(), 'app', 'views');
}

/* function to get the path of layouts */
function getLayoutPath(): string {
    return getPath(getcwd(), 'layout');
}

/* function to check if role id is for simple user */
function isSimpleUser($roleId): bool {
    return $roleId === USER_ROLE_ID;
}

/* function to remove null value from array  */
function filterNullVal(array $array): array {
    return array_filter($array, function($val, $key) {
        return $val !== NULL;
    }, ARRAY_FILTER_USE_BOTH);
}

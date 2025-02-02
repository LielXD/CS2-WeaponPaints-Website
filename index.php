<?php


include_once './config.php';

ini_set('session.use_only_cookies', 1);
ini_set('session.use_strict_mode', 1);

session_set_cookie_params([
    'lifetime' => 1800,
    'domain' => $_SERVER['SERVER_NAME'],
    'path' => '/',
    'secure' => true,
    'httponly' => true
]);

if(session_status() != PHP_SESSION_ACTIVE) {
    session_start();
}

function GetPrefix() {
    if(!str_contains(dirname(__FILE__), '\\')) {
        $path = explode('/', dirname(__FILE__));
    }else {
        $path = explode('\\', dirname(__FILE__));
    }
    $mainfolder = $path[count($path)-1];
    
    if($mainfolder == $_SERVER['HTTP_HOST']) {
        return "/";
    }

    $url = explode($mainfolder, $_SERVER['REQUEST_URI'])[0];
    return $url.$mainfolder.'/';
}

if($Website_Settings['language'] && isset($_COOKIE['cs2weaponpaints_lielxd_language'])
&& file_exists(realpath(path: "./translation/".$_COOKIE['cs2weaponpaints_lielxd_language'].".json"))) {
    $translations = json_decode(file_get_contents("./translation/".$_COOKIE['cs2weaponpaints_lielxd_language'].".json"));
}else if($Website_Settings['language'] && !isset($_COOKIE['cs2weaponpaints_lielxd_language'])
&& file_exists(realpath("./translation/$Website_Translate.json"))) {
    $translations = json_decode(file_get_contents("./translation/$Website_Translate.json"));
}else if(file_exists(realpath("./translation/en.json"))) {
    $translations = json_decode(file_get_contents("./translation/en.json"));
}else {
    $documentError_Code = 'translatefile';
}

if(!isset($documentError_Code)) {
    $bodyStyle = "";
    if($translations->invert_direction) {
        $bodyStyle .= "direction:rtl;";
    }
    if($Website_Settings['theme'] && isset($_COOKIE['cs2weaponpaints_lielxd_theme'])) {
        $Website_MainColor = $_COOKIE['cs2weaponpaints_lielxd_theme'];
        $bodyStyle .= "--main-color: $Website_MainColor;";
    }else if($Website_MainColor === true) {
        $Website_MainColor = rand(111111, 999999);
        $bodyStyle .= "--main-color: #$Website_MainColor;";
    }else {
        if(isset($Website_MainColor) && !empty($Website_MainColor)) {
            $bodyStyle .= "--main-color: $Website_MainColor;";
        }
    }
    if(!empty($bodyStyle)) {
        $bodyStyle = "style='$bodyStyle'";
    }
}

if(!isset($documentError_Code)) {
    if(!isset($SteamAPI_KEY) || empty($SteamAPI_KEY)) {
        $documentError_Code = 'steamapi';
    }else if(!isset($DatabaseInfo['host']) || empty($DatabaseInfo['host']) ||
    !isset($DatabaseInfo['database']) || empty($DatabaseInfo['database'])) {
        $documentError_Code = 'database';
    }
}

function Path($key = null) {
    $path = explode("/", $_GET['path'] ?? 'signin');
    if(is_null($key)) {
        return $path;
    }

    return $path[$key] ?? '';
}

if(isset($documentError_Code)) {
    include_once "./errorpage.php";
}else if(file_exists("./pages/".Path(0).".php")) {
    include_once "./pages/".Path(0).".php";
}else {
    $documentError_Code = 404;
    include_once "./errorpage.php";
}
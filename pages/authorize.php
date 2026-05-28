<?php

if(!function_exists("Path")) {
    return;
}

if(!isset($SteamAPI_KEY) || empty($SteamAPI_KEY)) {
    echo 'for website owner:<br>please enter a valid steam web api key.';
    exit;
}

if(isset($_SESSION['steamid'])) {
    Website_RequireSteamIDAccess($_SESSION['steamid']);
    header('Location: '.GetPrefix().'skins');
    exit;
}

try {
    require_once 'imports/openid.php';

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    $protocol = $isHttps ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'].GetPrefix();

    $openid = new LightOpenID($host);
    $openid->realm = $protocol . '://' . $host;
    $openid->returnUrl = $protocol . '://' . $host . 'authorize';
    $openid->identity = 'https://steamcommunity.com/openid';

    if(!$openid->mode) {
        header('Location: '.$openid->authUrl());
        exit;
    }

    if($openid->mode == 'cancel') {
        header('Location: '.GetPrefix());
        exit;
    }

    if($openid->validate()) {
        $steamid = Website_SteamIDFromOpenID($openid->identity);
        if($steamid && Website_SteamIDAllowed($steamid)) {
            $_SESSION['steamid'] = $steamid;
            header('Location: '.GetPrefix().'skins');
            exit;
        }

        Website_RequireSteamIDAccess($steamid);
    }

    $documentError_Code = 403;
    include_once './errorpage.php';
}catch(Exception $exception) {
    $documentError_Code = $exception->getCode();
    $documentError_Message = $exception->getMessage();

    $documentError_Message .= "<br>please contact website owner for help.";

    include_once './errorpage.php';
}

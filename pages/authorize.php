<?php

if(!function_exists("Path")) {
    return;
}

if(!isset($SteamAPI_KEY) || empty($SteamAPI_KEY)) {
    echo 'for website owner:<br>please enter a valid steam web api key.';
    exit;
}

if(isset($_SESSION['steamid'])) {
    header('Location: '.GetPrefix().'skins');
    exit;
}

require_once 'imports/openid.php';

try {
    $openid = new LightOpenID($_SERVER['SERVER_NAME']);
    
    if(!$openid->mode) {
        $openid->identity = 'https://steamcommunity.com/openid';
        header('Location: ' . $openid->authUrl());
    }else if($openid->mode == 'cancel') {
        header('Location: '.GetPrefix());
    }else {
        if($openid->validate()) {
            $url = explode('/', $openid->identity);
            $id = $url[count($url)-1];
            
            if(!isset($id) || empty($id)) {
                echo 'error with steam id 64, contact support.';
                exit;
            }
            
            $_SESSION['steamid'] = $id;
        }
    
        header('Location: '.GetPrefix());
    }
}catch(Exception $exception) {
    $documentError_Code = $exception->getCode();
    $documentError_Message = $exception->getMessage();

    $documentError_Message .= "<br>please contact website owner for help.";

    include_once './errorpage.php';
}
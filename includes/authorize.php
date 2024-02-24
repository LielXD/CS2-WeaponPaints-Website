<?php
    require_once '../config.php';

    if(isset($_GET['signout']) && $_GET['signout'] == 'true') {
        $_SESSION['steamid'] = null;
        header('Location: ../');
        exit;
    }

    if(!isset($SteamAPI_KEY) || empty($SteamAPI_KEY)) {
        echo 'for website owner:<br>please enter a valid steam web api key.';
        exit;
    }

    if(isset($_SESSION['steamid'])) {
        header('Location: ../skins/');
        exit;
    }

    require 'openid.php';
    if(!isset($Website_Domain) || empty($Website_Domain)) {
        $Website_Domain = $_SERVER['SERVER_NAME'];
    }
    
    $openid = new LightOpenID($Website_Domain);
    if(!$openid->mode) {
        $openid->identity = 'https://steamcommunity.com/openid';
        header('Location: ' . $openid->authUrl());
    }else if($openid->mode == 'cancel') {
        echo 'User has canceled authentication!';
    }else {
        if($openid->validate()) {
            $url = explode('/', $openid->identity);
            $id = $url[count($url)-1];
            
            if(!isset($id) || empty($id)) {
                echo 'something happened please try again';
                exit;
            }

            $_SESSION['steamid'] = $id;
            header('Location: ../');
        }else {
            header('Location: ../');
        }
    }
?>
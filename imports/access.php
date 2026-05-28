<?php

if(!function_exists("Path")) {
    return;
}

function Website_NormalizeSteamIDs($steamids) {
    if(!is_array($steamids)) {
        return [];
    }

    $normalized = [];
    foreach($steamids as $steamid) {
        $steamid = trim((string)$steamid);
        if(preg_match('/^[0-9]{17}$/', $steamid)) {
            $normalized[$steamid] = true;
        }
    }

    return array_keys($normalized);
}

function Website_GetAllowedSteamIDs() {
    global $Website_AllowedSteamIDs;

    if(!isset($Website_AllowedSteamIDs)) {
        return [];
    }

    return Website_NormalizeSteamIDs($Website_AllowedSteamIDs);
}

function Website_SteamIDAllowed($steamid) {
    $steamid = trim((string)$steamid);
    if(!preg_match('/^[0-9]{17}$/', $steamid)) {
        return false;
    }

    $allowedSteamIDs = Website_GetAllowedSteamIDs();
    if(empty($allowedSteamIDs)) {
        return true;
    }

    return in_array($steamid, $allowedSteamIDs, true);
}

function Website_SteamIDFromOpenID($openidIdentity) {
    if(preg_match('#^https?://steamcommunity\.com/openid/id/([0-9]{17})/?$#', $openidIdentity, $matches)) {
        return $matches[1];
    }

    return null;
}

function Website_RequireSteamIDAccess($steamid, $renderErrorPage = true) {
    if(Website_SteamIDAllowed($steamid)) {
        return;
    }

    unset($_SESSION['steamid']);
    http_response_code(403);

    if($renderErrorPage) {
        $documentError_Code = 403;
        include './errorpage.php';
    }else {
        echo 'forbidden';
    }

    exit;
}

?>

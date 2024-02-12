<?php

// Choose your translate file name located in translation/filename.json
// You can add your own translation.
$Website_Translate = 'en';

// If you store your website on a subfolder domain,
// Leave empty if using the domain as normal.
// Example: skins.cs2.lielxd.com/cs2/ | then we need here cs2 ↓
$Website_Subfolder = '';

// Enable this if you want categories else it will display all weapons.
$Website_UseCategories = true;

// You can choose your own theme color
// false/empty - will use the default color.
// any html acceptable color - will display that color.
// true - this will make get a random color.
$Website_MainColor = true;

// Write here your steam api key, get one from here: https://steamcommunity.com/dev/apikey.
$SteamAPI_KEY = '';

// Write here your database login details.
$DatabaseInfo = [
    'hostname' => '',
    'database' => '',
    'username' => '',
    'password' => '',
    'port' => '3306'
];

// -----------------
// Don't touch here.
// -----------------
if(session_status() != PHP_SESSION_ACTIVE) {
    session_start();
}

$dirPath = $_SERVER['DOCUMENT_ROOT'].'/'.$Website_Subfolder;
if(file_exists($dirPath."/translation/$Website_Translate.json")) {
    $translations = json_decode(file_get_contents($dirPath."/translation/$Website_Translate.json"));
}else if(file_exists($dirPath."/translation/en.json")) {
    $translations = json_decode(file_get_contents($dirPath."/translation/en.json"));
}else {
    echo "No translations have found<br>contact support.";
    die;
}

$bodyStyle = "";
if($translations->invert_direction) {
    $bodyStyle .= "direction:rtl;";
}
if(isset($Website_MainColor) && !empty($Website_MainColor)) {
    if($Website_MainColor == 1) {
        $Website_MainColor = rand(111111, 999999);
        $bodyStyle .= "--main-color: #$Website_MainColor;";
    }else {
        $bodyStyle .= "--main-color: $Website_MainColor;";
    }
}
if(!empty($bodyStyle)) {
    $bodyStyle = "style='$bodyStyle'";
}

?>

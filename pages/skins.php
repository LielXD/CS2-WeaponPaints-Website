<?php

if(!function_exists("Path")) {
    return;
}

if(!isset($_SESSION['steamid'])) {
    echo 'here: '.$_SESSION['steamid'];
    die;
    header('Location: '.GetPrefix());
    exit;
}

/************/
/* Database */
/************/

$conn = require_once('imports/database.php');
if($conn != 1) {
    $documentError_Code = 'database';
    include_once 'errorpage.php';
    exit;
}

$query = $pdo->query("SHOW TABLES LIKE 'wp_player_skins';");
if(empty($query->fetchAll())) {
    $documentError_Code = 'setup-plugin';
    include_once 'errorpage.php';
    exit;
}

/**************/
/* Json Files */
/**************/

$full_skins = json_decode(file_get_contents('src/data/skins.json'));
$gloves = json_decode(file_get_contents('src/data/gloves.json'));
$agents = json_decode(file_get_contents('src/data/agents.json'));
$songs = json_decode(file_get_contents('src/data/music.json'));
$stickers = json_decode(file_get_contents('src/data/stickers.json'));
$keychains = json_decode(file_get_contents('src/data/keychains.json'));

/**************/
/* Categories */
/**************/

$weapon_types = [
    // Knifes
    'weapon_bayonet' => 'knifes',
    'weapon_knife_css' => 'knifes',
    'weapon_knife_flip' => 'knifes',
    'weapon_knife_gut' => 'knifes',
    'weapon_knife_karambit' => 'knifes',
    'weapon_knife_m9_bayonet' => 'knifes',
    'weapon_knife_tactical' => 'knifes',
    'weapon_knife_falchion' => 'knifes',
    'weapon_knife_survival_bowie' => 'knifes',
    'weapon_knife_butterfly' => 'knifes',
    'weapon_knife_push' => 'knifes',
    'weapon_knife_cord' => 'knifes',
    'weapon_knife_canis' => 'knifes',
    'weapon_knife_ursus' => 'knifes',
    'weapon_knife_gypsy_jackknife' => 'knifes',
    'weapon_knife_outdoor' => 'knifes',
    'weapon_knife_stiletto' => 'knifes',
    'weapon_knife_widowmaker' => 'knifes',
    'weapon_knife_skeleton' => 'knifes',
    'weapon_knife_kukri' => 'knifes',
    'weapon_knife_default' => 'knifes',

    // Pistols
    'weapon_deagle' => 'pistols',
    'weapon_cz75a' => 'pistols',
    'weapon_fiveseven' => 'pistols',
    'weapon_glock' => 'pistols',
    'weapon_hkp2000' => 'pistols',
    'weapon_p250' => 'pistols',
    'weapon_revolver' => 'pistols',
    'weapon_tec9' => 'pistols',
    'weapon_usp_silencer' => 'pistols',
    'weapon_elite' => 'pistols',
    'weapon_taser' => 'pistols',

    // Rifles
    'weapon_ak47' => 'rifles',
    'weapon_aug' => 'rifles',
    'weapon_famas' => 'rifles',
    'weapon_galilar' => 'rifles',
    'weapon_m4a1' => 'rifles',
    'weapon_m4a1_silencer' => 'rifles',
    'weapon_sg556' => 'rifles',

    // Smg
    'weapon_mac10' => 'smg',
    'weapon_mp5sd' => 'smg',
    'weapon_mp7' => 'smg',
    'weapon_mp9' => 'smg',
    'weapon_bizon' => 'smg',
    'weapon_p90' => 'smg',
    'weapon_ump45' => 'smg',

    // Machine guns
    'weapon_m249' => 'machine_guns',
    'weapon_negev' => 'machine_guns',

    // Sniper rifles
    'weapon_ssg08' => 'sniper_rifles',
    'weapon_awp' => 'sniper_rifles',
    'weapon_scar20' => 'sniper_rifles',
    'weapon_g3sg1' => 'sniper_rifles',
    
    // Shotguns
    'weapon_mag7' => 'shotguns',
    'weapon_nova' => 'shotguns',
    'weapon_sawedoff' => 'shotguns',
    'weapon_xm1014' => 'shotguns'
];

$gloves_models = [
    4725,
    5027,
    5030,
    5031,
    5032,
    5033,
    5034,
    5035,
    'gloves_default'
];

function GetWeaponType($weapon_name) {
    global $weapon_types;
    global $gloves_models;

    if(isset($weapon_types[$weapon_name])) {
        return $weapon_types[$weapon_name];
    }elseif(in_array($weapon_name, $gloves_models)) {
        return 'gloves';
    }elseif($weapon_name == 'mvp') {
        return 'mvp';
    }else if($weapon_name == 'counter-terrorist' || $weapon_name == 'terrorist') {
        return 'agents';
    }else {
        return false;
    }
}

$ct_only = [];
$t_only = [];
if($Website_TeamOnlyWeapons) {
    $ct_only = [
        'weapon_m4a1',
        'weapon_m4a1_silencer',
        'weapon_usp_silencer',
        'weapon_aug',
        'weapon_fiveseven',
        'weapon_famas',
        'weapon_scar20',
        'weapon_mp9'
    ];
    
    $t_only = [
        'weapon_ak47',
        'weapon_galilar',
        'weapon_glock',
        'weapon_sg556',
        'weapon_elite',
        'weapon_g3sg1',
        'weapon_mac10',
        'weapon_tec9'
    ];
}

if(!Path(1)) {
    include_once 'imports/selectweapon.php';
}else if(Path(2)) {
    include_once 'imports/viewskin.php';
}else if(Path(1)) {
    include_once 'imports/selectskin.php';
}
?>

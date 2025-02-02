<?php

if(!function_exists("Path")) {
    return;
}

/*************/
/* SteamInfo */
/*************/

try {
    $steamApiUserInfo = file_get_contents("http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=$SteamAPI_KEY&steamids=".$_SESSION['steamid']);
    $UserInfo = json_decode($steamApiUserInfo)->response->players[0];
}catch(Exception $err) {
    header("Refresh:0;");
}

/*************/
/* Languages */
/*************/

$langs = scandir('translation/');
array_shift($langs);
array_shift($langs);

/*****************/
/* WeaponPreview */
/*****************/

$current = false;

$saved_t = false;
$saved_ct = false;

$player_skin = false;

$selectedpaint = 0;
if(Path(2) != 'default') {$selectedpaint = Path(2);}

$type = GetWeaponType(Path(1));
if($type == 'gloves') {
    foreach($gloves as $key=>$glove) {
        if($glove->weapon_defindex == Path(1)
        && $glove->paint == $selectedpaint) {
            $current = $glove;
            break;
        }
    }
}else if($type == 'agents') {
    Path(1) == 'terrorist' ? $team = 2:$team = 3;
    $modelagent = str_replace('-', '/', $selectedpaint);
    foreach($agents as $key=>$agent) {
        if($agent->team == $team
        && $agent->model == $modelagent) {
            $current = $agent;
            break;
        }
    }
}else if($type == 'mvp') {
    foreach($songs as $key=>$mvp) {
        if($mvp->id == $selectedpaint) {
            $current = $mvp;
            break;
        }
    }
}else {
    foreach($full_skins as $key=>$skin) {
        if($skin->weapon_name == Path(1)
        && $skin->paint == $selectedpaint) {
            $current = $skin;
            break;
        }
    }
}

if(!$current) {
    header("Location: ".GetPrefix());
    exit;
}

switch($type) {
    case 'gloves':
        $weapon = [
            'name' => $current->paint_name,
            'index' => $current->weapon_defindex,
            'paint' => $current->paint
        ];

        $query = $pdo->prepare("SELECT * FROM `wp_player_gloves` WHERE `steamid` = ? AND `weapon_defindex` = ?");
        $query->execute([$_SESSION['steamid'], $current->weapon_defindex]);
        $savedgloves = $query->fetchAll();
        
        foreach($savedgloves as $saved) {
            if($saved['weapon_team'] == 1) {
                $temp_t = $saved;
            }
            if($saved['weapon_team'] == 2) {
                $temp_ct = $saved;
            }
        }

        $query = $pdo->prepare("SELECT * FROM `wp_player_skins` WHERE `steamid` = ? AND `weapon_defindex` = ?");
        $query->execute([$_SESSION['steamid'], $current->weapon_defindex]);
        $savedskins = $query->fetchAll();
        
        foreach($savedskins as $saved) {
            if($saved['weapon_paint_id'] == $selectedpaint && $saved['weapon_team'] == 1) {
                $saved_t = $saved;
            }
            if($saved['weapon_paint_id'] == $selectedpaint && $saved['weapon_team'] == 2) {
                $saved_ct = $saved;
            }
        }

        $weapon['t'] = false;
        $weapon['ct'] = false;
        if($saved_t) {
            $weapon['t'] = true;
            $player_skin = $saved_t;
        }
        if($saved_ct) {
            $weapon['ct'] = true;

            if(!$player_skin) {
                $player_skin = $saved_ct;
            }
        }
        
        break;
    case 'agents':
        $weapon = [
            'name' => $current->agent_name,
            'index' => $current->model
        ];

        $query = $pdo->prepare("SELECT * FROM `wp_player_agents` WHERE `steamid` = ?");
        $query->execute([$_SESSION['steamid']]);
        
        $result = $query->fetch();
        
        if($current->team == 2) {
            if($result && $result['agent_t'] == $current->model) {
                $weapon['t'] = true;
            }else {
                $weapon['t'] = false;
            }
        }else if($current->team == 3) {
            if($result && $result['agent_ct'] == $current->model) {
                $weapon['ct'] = true;
            }else {
                $weapon['ct'] = false;
            }
        }
        break;
    case 'mvp':
        $weapon = [
            'name' => $current->name,
            'index' => $current->id
        ];

        $query = $pdo->prepare("SELECT * FROM `wp_player_music` WHERE `steamid` = ? AND `music_id` = ?");
        $query->execute([$_SESSION['steamid'], $current->id]);

        $weapon['t'] = false;
        $weapon['ct'] = false;

        $results = $query->fetchAll();
        foreach($results as $res) {
            if($res['weapon_team'] == 0 || $res['weapon_team'] == 1) {
                $weapon['t'] = true;
            }
            if($res['weapon_team'] == 0 || $res['weapon_team'] == 2) {
                $weapon['ct'] = true;
            }
        }
        break;
    default:
        $weapon = [
            'name' => $current->paint_name,
            'index' => $current->weapon_defindex,
            'paint' => $current->paint,
            'weapon_name' => $current->weapon_name
        ];

        $query = $pdo->prepare("SELECT * FROM `wp_player_skins` WHERE `steamid` = ? AND `weapon_defindex` = ? AND `weapon_paint_id` = ?");
        $query->execute([$_SESSION['steamid'], $current->weapon_defindex, $current->paint]);

        if(!in_array($current->weapon_name, $t_only)) {
            $weapon['ct'] = false;
        }
        if(!in_array($current->weapon_name, $ct_only)) {
            $weapon['t'] = false;
        }

        $player_skins = $query->fetchAll();
        if($player_skins) {
            foreach($player_skins as $skin) {
                if(!in_array($current->weapon_name, $ct_only)) {
                    if($skin['weapon_team'] == 0 || $skin['weapon_team'] == 1) {
                        $saved_t = $skin;
                        $weapon['t'] = true;
                    }
                }
                if(!in_array($current->weapon_name, $t_only)) {
                    if($skin['weapon_team'] == 0 || $skin['weapon_team'] == 2) {
                        $saved_ct = $skin;
                        $weapon['ct'] = true;
                    }
                }
            }
        }

        $player_skin = $player_skins[0] ?? false;

        break;
}

if(isset($player_skin) && $player_skin
&& $player_skin['weapon_wear'] != 0
&& $player_skin['weapon_wear'] != 0.07
&& $player_skin['weapon_wear'] != 0.15
&& $player_skin['weapon_wear'] != 0.38
&& $player_skin['weapon_wear'] != 0.45) {
    $custom_wear = $player_skin['weapon_wear'];
}


$stickers_loop = false;
if(isset($player_skin) && $player_skin) {
    if($player_skin['weapon_sticker_0']) {
        $sticker0 = explode(';', $player_skin['weapon_sticker_0']);
        $stickers_loop = true;
    }
    if($player_skin['weapon_sticker_1']) {
        $sticker1 = explode(';', $player_skin['weapon_sticker_1']);
        $stickers_loop = true;
    }
    if($player_skin['weapon_sticker_2']) {
        $sticker2 = explode(';', $player_skin['weapon_sticker_2']);
        $stickers_loop = true;
    }
    if($player_skin['weapon_sticker_3']) {
        $sticker3 = explode(';', $player_skin['weapon_sticker_3']);
        $stickers_loop = true;
    }
    if($player_skin['weapon_sticker_4']) {
        $sticker4 = explode(';', $player_skin['weapon_sticker_4']);
        $stickers_loop = true;
    }

    if($player_skin['weapon_keychain']) {
        $keychain0 = explode(';', $player_skin['weapon_keychain']);
        foreach($keychains as $keychain) {
            if($keychain->id == $keychain0[0]) {
                $keychain0_info = $keychain;
                break;
            }
        }
    }
}

if($stickers_loop) {
    foreach($stickers as $sticker) {
        if(isset($sticker0) && $sticker->id == $sticker0[0]) {
            $sticker0_info = $sticker;
        }
        if(isset($sticker1) && $sticker->id == $sticker1[0]) {
            $sticker1_info = $sticker;
        }
        if(isset($sticker2) && $sticker->id == $sticker2[0]) {
            $sticker2_info = $sticker;
        }
        if(isset($sticker3) && $sticker->id == $sticker3[0]) {
            $sticker3_info = $sticker;
        }
        if(isset($sticker4) && $sticker->id == $sticker4[0]) {
            $sticker4_info = $sticker;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="<?= GetPrefix(); ?>src/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="<?= GetPrefix(); ?>css/main.css" type="text/css">
    <link rel="stylesheet" href="<?= GetPrefix(); ?>css/skins.css" type="text/css">
    <link rel="stylesheet" href="<?= GetPrefix(); ?>css/view.css" type="text/css">
    <script src="<?= GetPrefix(); ?>js/skins.js" defer></script>
    <script type="importmap">
        {
            "imports": {
            "three": "https://cdn.jsdelivr.net/npm/three@0.170.0/build/three.module.js",
            "three/addons/": "https://cdn.jsdelivr.net/npm/three@0.170.0/examples/jsm/"
            }
        }
    </script>
    <script>
        let usethreejs = <?= $Website_UseThreejs?"true":"false"; ?>;
        let modelpath = false;texturepath = false,legacy = <?= empty($current->legacy_model) ? "false": $current->legacy_model; ?>;
        <?php
        if($Website_UseThreejs && isset($current->threejsmodel)) {
        ?>
        modelpath = "<?= $current->threejsmodel; ?>";
        <?php
        }
        if($Website_UseThreejs && isset($current->texture) && isset($current->texture_metal)) {
        ?>
        texturepath = ["<?= $current->texture; ?>", "<?= $current->texture_metal; ?>"];
        <?php
        }
        ?>
        const Prefix = "<?= GetPrefix(); ?>";
        const currenttype = "<?= $type; ?>";

        let saved_t = `<?= $saved_t?json_encode($saved_t):'0'; ?>`, saved_ct = `<?= $saved_ct?json_encode($saved_ct):'0'; ?>`;
    </script>
    <script src="<?= GetPrefix(); ?>js/weaponviewer.js" type="module" defer></script>
    <title><?= $translations->website_name; ?> - Skins</title>
</head>
<body <?= $bodyStyle ?? "" ?>>

<div id="loading">
    <span></span>
</div>

<div id="messages"></div>

<div class="wrapper mainbox" data-index="<?= $weapon['index'] ?>" <?= isset($weapon['paint'])?'data-paint="'.$weapon['paint'].'"':''; ?> <?= isset($weapon['weapon_name'])?'data-name="'.$weapon['weapon_name'].'"':''; ?>>
    <div id="viewselect">
        <div class="titlebox">
            <button class="viewselect_return" <?= $translations->invert_direction?'style="right: unset;left: 120%;"':''; ?>>^</button>
            <h2 class="title" data-prefix="<?= $translations->skins->view_skin->select; ?>"></h2>
        </div>
        <div class="input">
            <input type="text" placeholder="<?= $translations->skins->view_skin->search; ?>" data-prefix="<?= $translations->skins->view_skin->search; ?>">
        </div>
        <ul></ul>
    </div>

    <header>
        <h2><?= $weapon['name']; ?></h2>
    </header>
    <div id="threejsArea"></div>
    <div id="previewImg">
        <img src="<?= $current->image; ?>">
    </div>
    <div id="settings" <?= $translations->invert_direction?'style="animation: slideLeft 1s forwards;"':''; ?>>
        <div class="title">
            <a href="<?= GetPrefix(); ?>skins/<?= Path(1); ?>/" <?= $translations->invert_direction?'style="right: unset;left: 120%;"':''; ?>><</a>
            <span><?= $translations->skins->view_skin->title; ?>
                <svg viewBox="0 0 48 48"><path d="M36.9,12c-0.4-1.7-2-3-3.9-3s-3.4,1.3-3.9,3H2v2h27.1c0.4,1.7,2,3,3.9,3s3.4-1.3,3.9-3H46v-2H36.9z M33,15   c-1.1,0-2-0.9-2-2s0.9-2,2-2s2,0.9,2,2S34.1,15,33,15z"/><path d="M33,31c-1.9,0-3.4,1.3-3.9,3H2v2h27.1c0.4,1.7,2,3,3.9,3s3.4-1.3,3.9-3H46v-2h-9.1C36.4,32.3,34.9,31,33,31z M33,37   c-1.1,0-2-0.9-2-2s0.9-2,2-2s2,0.9,2,2S34.1,37,33,37z"/><path d="M15,20c-1.9,0-3.4,1.3-3.9,3H2v2h9.1c0.4,1.7,2,3,3.9,3s3.4-1.3,3.9-3H46v-2H18.9C18.4,21.3,16.9,20,15,20z M15,26   c-1.1,0-2-0.9-2-2c0-1.1,0.9-2,2-2s2,0.9,2,2C17,25.1,16.1,26,15,26z"/></svg>
            </span>
        </div>
        <div class="input" id="preset" <?= !$saved_t || !$saved_ct?'style="display:none;"':''; ?>>
            <label for="preset"><?= $translations->skins->view_skin->team; ?></label>
            <div class="marks">
                <input type="radio" name="preset" class="terrormark" checked>
                <input type="radio" name="preset" class="counterterrormark">
            </div>
        </div>
        <?php
        if($type != 'agents' && $type != 'mvp' && $current->paint != 'ct' && $current->paint != 't') {
        if($selectedpaint != 0 && $type != 'knifes' || $type == 'knifes') {
        ?>
        <div class="input" id="wear">
            <label for="wear"><?= $translations->skins->view_skin->wear->label; ?></label>
            <select>
                <option value="0" <?= $player_skin && $player_skin['weapon_wear'] == 0?'selected="selected"':''; ?>><?= $translations->skins->view_skin->wear->factory_new; ?></option>
                <option value="0.07" <?= $player_skin && $player_skin['weapon_wear'] == 0.07?'selected="selected"':''; ?>><?= $translations->skins->view_skin->wear->minimal_wear; ?></option>
                <option value="0.15" <?= $player_skin && $player_skin['weapon_wear'] == 0.15?'selected="selected"':''; ?>><?= $translations->skins->view_skin->wear->field_tested; ?></option>
                <option value="0.38" <?= $player_skin && $player_skin['weapon_wear'] == 0.38?'selected="selected"':''; ?>><?= $translations->skins->view_skin->wear->well_worn; ?></option>
                <option value="0.45" <?= $player_skin && $player_skin['weapon_wear'] == 0.45?'selected="selected"':''; ?>><?= $translations->skins->view_skin->wear->battle_scarred; ?></option>
                <option value="custom" <?= isset($custom_wear)?'selected="selected"':''; ?>><?= $translations->skins->view_skin->wear->custom; ?></option>
            </select>
            <input type="range" id="customwear" min="0" max="0.99" value="<?= isset($custom_wear)?$custom_wear:'0'; ?>" data-val="<?= isset($custom_wear)?$custom_wear:'0'; ?>" step="0.01"
            oninput="this.style.setProperty('--fill-precent', `${this.value / this.max * 100}%`);this.dataset.val = this.value;"
            style="--fill-precent: <?= isset($custom_wear)?(($custom_wear/1)*100).'%':'0'; ?>;">
        </div>
        <?php
        if($type != 'gloves') {
        ?>
        <div class="input" id="seed">
            <label for="seed"><?= $translations->skins->view_skin->seed; ?></label>
            <div class="box">
                <input type="checkbox" oninput="this.checked?this.nextElementSibling.disabled=false:this.nextElementSibling.disabled=true;" <?= $player_skin['weapon_seed'] ?? 0 > 0?'checked':''; ?>>
                <input type="number" placeholder="1 - 1000" min="1" max="1000" <?= $player_skin['weapon_seed'] ?? 0 > 0?"value='".$player_skin['weapon_seed']."'":'disabled'; ?>>
            </div>
        </div>
        <?php
        }
        ?>
        <div class="input" id="nametag">
            <label for="nametag"><?= $translations->skins->view_skin->nametag; ?></label>
            <div class="box">
                <input type="checkbox" oninput="this.checked?this.nextElementSibling.disabled=false:this.nextElementSibling.disabled=true;" <?= !is_null($player_skin['weapon_nametag'] ?? null)?'checked':''; ?>>
                <input type="text" placeholder="<?= $translations->skins->view_skin->nametag; ?>" <?php if($player_skin && !is_null($player_skin['weapon_nametag'])){echo 'value="'.$player_skin['weapon_nametag'].'"';}else {echo 'disabled';} ?>>
            </div>
        </div>
        <?php
        if($type != 'gloves') {
        ?>
        <div class="box" id="stattrak">
            <div class="input">
                <label for="stattrak"><?= $translations->skins->view_skin->stattrak->label; ?></label>
                <div class="box">
                    <input type="checkbox" oninput="this.checked?document.querySelector('#stattrak p').style.opacity = 1:document.querySelector('#stattrak p').style.opacity = 0.3;" <?= $player_skin['weapon_stattrak'] ?? false?'checked':''; ?>>
                </div>
            </div>
            <div class="input">
                <label for="stattrak"><?= $translations->skins->view_skin->stattrak->label_kills; ?></label>
                <p <?= $player_skin['weapon_stattrak'] ?? false?'style="opacity:1;color:white;"':''; ?>><?= $player_skin['weapon_stattrak'] ?? false?$player_skin['weapon_stattrak_count'] ?? false:'/'; ?></p>
            </div>
        </div>
        <?php
        if($type != 'knifes') {
        ?>
        <div class="addons">
            <div class="box-col">
                <label for="stickers"><?= $translations->skins->view_skin->stickers; ?></label>
                <div class="stickers">
                    <button <?= isset($sticker0_info)?'data-id="'.$sticker0[0].'" title="'.$sticker0_info->name.'"':''; ?>><?= isset($sticker0_info)?'<img src="'.$sticker0_info->image.'">':'+';?></button>
                    <button <?= isset($sticker1_info)?'data-id="'.$sticker1[0].'" title="'.$sticker1_info->name.'"':''; ?>><?= isset($sticker1_info)?'<img src="'.$sticker1_info->image.'">':'+';?></button>
                    <button <?= isset($sticker2_info)?'data-id="'.$sticker2[0].'" title="'.$sticker2_info->name.'"':''; ?>><?= isset($sticker2_info)?'<img src="'.$sticker2_info->image.'">':'+';?></button>
                    <button <?= isset($sticker3_info)?'data-id="'.$sticker3[0].'" title="'.$sticker3_info->name.'"':''; ?>><?= isset($sticker3_info)?'<img src="'.$sticker3_info->image.'">':'+';?></button>
                    <button <?= isset($sticker4_info)?'data-id="'.$sticker4[0].'" title="'.$sticker4_info->name.'"':''; ?>><?= isset($sticker4_info)?'<img src="'.$sticker4_info->image.'">':'+';?></button>
                </div>
            </div>
            <div class="box-col">
                <label for="keychains"><?= $translations->skins->view_skin->keychains; ?></label>
                <div class="keychains">
                    <button <?= isset($keychain0_info)?'data-id="'.$keychain0[0].'" title="'.$keychain0_info->name.'"':''; ?>><?= isset($keychain0_info)?'<img src="'.$keychain0_info->image.'">':'+';?></button>
                </div>
            </div>
        </div>
        <?php
        }
        }
        }
        }
        ?>
        <div class="apply">
            <?php
            if(isset($weapon['t'])) {
            ?>
            <button class="main-btn terror<?= $weapon['t'] ? ' applied': ''; ?>"><?= $translations->skins->view_skin->apply->terror; ?></button>
            <?php
            }
            if(isset($weapon['ct'])) {
            ?>
            <button class="main-btn counter-terror<?= $weapon['ct'] ? ' applied': ''; ?>"><?= $translations->skins->view_skin->apply->counterterror; ?></button>
            <?php
            }
            ?>
        </div>
    </div>
</div>

<footer>
    <div class="wrapper">
        <a class="info" href="https://steamcommunity.com/profiles/<?= $_SESSION['steamid']; ?>" target="_blank">
            <img src="<?= $UserInfo->avatarfull ?>" alt="name">
            <p><?= str_replace('{{name}}', "<strong>$UserInfo->personaname</strong>", $translations->skins->footer->signedin); ?></p>
        </a>
        <div class="credit">
            <p>This website created by LielXD</p>
        </div>
        <div class="actions">
            <div class="settings">
                <svg viewBox="0 0 48 48" data-action="toggle_menu"><path d="M0 0h48v48H0z" fill="none"/><path d="M38.86 25.95c.08-.64.14-1.29.14-1.95s-.06-1.31-.14-1.95l4.23-3.31c.38-.3.49-.84.24-1.28l-4-6.93c-.25-.43-.77-.61-1.22-.43l-4.98 2.01c-1.03-.79-2.16-1.46-3.38-1.97L29 4.84c-.09-.47-.5-.84-1-.84h-8c-.5 0-.91.37-.99.84l-.75 5.3c-1.22.51-2.35 1.17-3.38 1.97L9.9 10.1c-.45-.17-.97 0-1.22.43l-4 6.93c-.25.43-.14.97.24 1.28l4.22 3.31C9.06 22.69 9 23.34 9 24s.06 1.31.14 1.95l-4.22 3.31c-.38.3-.49.84-.24 1.28l4 6.93c.25.43.77.61 1.22.43l4.98-2.01c1.03.79 2.16 1.46 3.38 1.97l.75 5.3c.08.47.49.84.99.84h8c.5 0 .91-.37.99-.84l.75-5.3c1.22-.51 2.35-1.17 3.38-1.97l4.98 2.01c.45.17.97 0 1.22-.43l4-6.93c.25-.43.14-.97-.24-1.28l-4.22-3.31zM24 31c-3.87 0-7-3.13-7-7s3.13-7 7-7 7 3.13 7 7-3.13 7-7 7z"/></svg>
                <div class="items">
                    <ul>
                        <?php
                        if($Website_Settings['language']) {
                        ?>
                        <button data-action="language_select" data-langs='<?= json_encode($langs); ?>'>
                            <svg viewBox="0 0 20 20"><path d="M10,0 C4.5,0 0,4.5 0,10 C0,15.5 4.5,20 10,20 C15.5,20 20,15.5 20,10 C20,4.5 15.5,0 10,0 L10,0 Z M16.9,6 L14,6 C13.7,4.7 13.2,3.6 12.6,2.4 C14.4,3.1 16,4.3 16.9,6 L16.9,6 Z M10,2 C10.8,3.2 11.5,4.5 11.9,6 L8.1,6 C8.5,4.6 9.2,3.2 10,2 L10,2 Z M2.3,12 C2.1,11.4 2,10.7 2,10 C2,9.3 2.1,8.6 2.3,8 L5.7,8 C5.6,8.7 5.6,9.3 5.6,10 C5.6,10.7 5.7,11.3 5.7,12 L2.3,12 L2.3,12 Z M3.1,14 L6,14 C6.3,15.3 6.8,16.4 7.4,17.6 C5.6,16.9 4,15.7 3.1,14 L3.1,14 Z M6,6 L3.1,6 C4.1,4.3 5.6,3.1 7.4,2.4 C6.8,3.6 6.3,4.7 6,6 L6,6 Z M10,18 C9.2,16.8 8.5,15.5 8.1,14 L11.9,14 C11.5,15.4 10.8,16.8 10,18 L10,18 Z M12.3,12 L7.7,12 C7.6,11.3 7.5,10.7 7.5,10 C7.5,9.3 7.6,8.7 7.7,8 L12.4,8 C12.5,8.7 12.6,9.3 12.6,10 C12.6,10.7 12.4,11.3 12.3,12 L12.3,12 Z M12.6,17.6 C13.2,16.5 13.7,15.3 14,14 L16.9,14 C16,15.7 14.4,16.9 12.6,17.6 L12.6,17.6 Z M14.4,12 C14.5,11.3 14.5,10.7 14.5,10 C14.5,9.3 14.4,8.7 14.4,8 L17.8,8 C18,8.6 18.1,9.3 18.1,10 C18.1,10.7 18,11.4 17.8,12 L14.4,12 L14.4,12 Z"/></svg>
                            <div>
                                <span><?= $translations->skins->footer->settings->language ?></span>
                                <svg viewBox="0 0 512 512"><polygon points="160,115.4 180.7,96 352,256 180.7,416 160,396.7 310.5,256 "/></svg>
                            </div>
                        </button>
                        <?php
                        }
                        if($Website_Settings['theme']) {
                        ?>
                        <button data-action="color_select" data-translations='<?= json_encode($translations->skins->footer->settings->theme); ?>'>
                            <svg viewBox="0 0 20 20"><path d="M9 20v-1.7l.01-.24L15.07 12h2.94c1.1 0 1.99.89 1.99 2v4a2 2 0 0 1-2 2H9zm0-3.34V5.34l2.08-2.07a1.99 1.99 0 0 1 2.82 0l2.83 2.83a2 2 0 0 1 0 2.82L9 16.66zM0 1.99C0 .9.89 0 2 0h4a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2zM4 17a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/></svg>
                            <div>
                                <span><?= $translations->skins->footer->settings->theme->label ?></span>
                                <svg viewBox="0 0 512 512"><polygon points="160,115.4 180.7,96 352,256 180.7,416 160,396.7 310.5,256 "/></svg>
                            </div>
                        </button>
                        <?php
                        }
                        ?>
                    </ul>
                </div>
            </div>
            <button class="main-btn" onclick="ToggleLoading();location.href='<?= GetPrefix(); ?>signout';"><?= $translations->skins->footer->sign_out ?>
                <svg viewBox="0 0 24 24"><path d="M12,10c1.1,0,2-0.9,2-2V4c0-1.1-0.9-2-2-2s-2,0.9-2,2v4C10,9.1,10.9,10,12,10z"/><path d="M19.1,4.9L19.1,4.9c-0.3-0.3-0.6-0.4-1.1-0.4c-0.8,0-1.5,0.7-1.5,1.5c0,0.4,0.2,0.8,0.4,1.1l0,0c0,0,0,0,0,0c0,0,0,0,0,0    c1.3,1.3,2,3,2,4.9c0,3.9-3.1,7-7,7s-7-3.1-7-7c0-1.9,0.8-3.7,2.1-4.9l0,0C7.3,6.8,7.5,6.4,7.5,6c0-0.8-0.7-1.5-1.5-1.5    c-0.4,0-0.8,0.2-1.1,0.4l0,0C3.1,6.7,2,9.2,2,12c0,5.5,4.5,10,10,10s10-4.5,10-10C22,9.2,20.9,6.7,19.1,4.9z"/></svg>
            </button>
        </div>
    </div>
</footer>

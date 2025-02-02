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

/*******************/
/* Selected weapon */
/*******************/

$selectedweapon = Path(1);
$selectedweapon_type = GetWeaponType($selectedweapon);

if(!$selectedweapon_type) {
    header('Location: '.GetPrefix());
    exit;
}

$saved_ct = false;
$saved_t = false;

$current_ct = false;
$current_t = false;

$weapon_info = [];
switch($selectedweapon_type) {
    case 'gloves':
        $state = $pdo->prepare("SELECT * FROM `wp_player_gloves` WHERE `steamid` = ? AND `weapon_defindex` = ?");
        $state->execute([$_SESSION['steamid'], $selectedweapon]);
        $savedgloves = $state->fetchAll();

        foreach($savedgloves as $saved) {
            if($saved['weapon_team'] == 1) {
                $saved_t = $saved;
            }
            if($saved['weapon_team'] == 2) {
                $saved_ct = $saved;
            }
        }

        $state = $pdo->prepare("SELECT * FROM `wp_player_skins` WHERE `steamid` = ? AND `weapon_defindex` = ?");
        $state->execute([$_SESSION['steamid'], $selectedweapon]);
        $savedskins = $state->fetchAll();

        $temp_t = false;
        $temp_ct = false;
        foreach($savedskins as $saved) {
            if($saved['weapon_team'] == 1) {
                $temp_t = $saved;
            }
            if($saved['weapon_team'] == 2) {
                $temp_ct = $saved;
            }
        }

        foreach($gloves as $glove) {
            if(!$temp_t && !$temp_ct) {
                if($glove->weapon_defindex == $selectedweapon) {
                    $current_t = $glove;
                    break;
                }

                continue;
            }

            if($temp_t && $glove->weapon_defindex == $temp_t['weapon_defindex'] && $glove->paint == $temp_t['weapon_paint_id']) {
                $current_t = $glove;
            }
            if($temp_ct && $glove->weapon_defindex == $temp_ct['weapon_defindex'] && $glove->paint == $temp_ct['weapon_paint_id']) {
                $current_ct = $glove;
            }
        }

        $weapon_info['img'] = [];
        if($current_t) {
            $weapon_info['name'] = explode(' | ', $current_t->paint_name)[0];
            array_push($weapon_info['img'], $current_t->image);
        }
        if($current_ct) {
            if(!isset($weapon_info['name'])) {
                $weapon_info['name'] = explode(' | ', $current_ct->paint_name)[0];
            }
            array_push($weapon_info['img'], $current_ct->image);
        }
        break;
    case 'mvp':
        $state = $pdo->prepare("SELECT * FROM `wp_player_music` WHERE `steamid` = ?");
        $state->execute([$_SESSION['steamid']]);
        $savedmusic = $state->fetchAll();

        foreach($savedmusic as $saved) {
            if($saved['weapon_team'] == 1) {
                $saved_t = $saved;
            }
            if($saved['weapon_team'] == 2) {
                $saved_ct = $saved;
            }
        }

        foreach($songs as $song) {
            if(!$saved_t && !$saved_ct) {
                if($song->id == 0) {$current_t = $song;break;}
                continue;
            }
            if($saved_t && $song->id == $saved_t['music_id']) {
                $current_t = $song;
            }
            if($saved_ct && $song->id == $saved_ct['music_id']) {
                $current_ct = $song;
            }
        }

        if($current_t) {
            $weapon_info['name'] = $current_t->name;
            $weapon_info['img'] = [$current_t->image];
        }

        break;
    case 'agents':
        $state = $pdo->prepare("SELECT * FROM `wp_player_agents` WHERE `steamid` = ?");
        $state->execute([$_SESSION['steamid']]);
        $savedagents = $state->fetch();

        if($savedagents && isset($savedagents['agent_t'])) {
            $saved_t = $savedagents['agent_t'];
        }
        if($savedagents && isset($savedagents['agent_ct'])) {
            $saved_ct = $savedagents['agent_ct'];
        }

        foreach($agents as $agent) {
            if($selectedweapon == 'terrorist' && $agent->team != 2 || $selectedweapon == 'counter-terrorist' && $agent->team != 3) {continue;}

            if($selectedweapon == 'terrorist' && !$saved_t && $agent->model == 'default' && $agent->team == 2) {
                $current_t = $agent;
                break;
            }
            if($selectedweapon == 'counter-terrorist' && !$saved_ct && $selectedweapon == 'counter-terrorist' && $agent->model == 'default' && $agent->team == 3) {
                $current_t = $agent;
                break;
            }

            if($selectedweapon == 'terrorist' && $agent->model == $saved_t) {
                $current_t = $agent;
            }else if($selectedweapon == 'counter-terrorist' && $agent->model == $saved_ct) {
                $current_t = $agent;
            }
        }

        if($current_t) {
            $weapon_info['name'] = $current_t->agent_name;
            $weapon_info['img'] = [$current_t->image];
        }

        break;
    default:
        foreach($full_skins as $skin) {
            if($skin->weapon_name != $selectedweapon || $skin->paint != 0) {continue;}

            $current_t = $skin;
            break;
        }

        $state = $pdo->prepare("SELECT * FROM `wp_player_skins` WHERE `steamid` = ? AND `weapon_defindex` = ?");
        $state->execute([$_SESSION['steamid'], $current_t->weapon_defindex]);
        $savedskins = $state->fetchAll();

        $is_knife = $selectedweapon == 'weapon_bayonet' || str_contains($selectedweapon, 'knife');
        if($is_knife) {
            $state = $pdo->prepare("SELECT * FROM `wp_player_knife` WHERE `steamid` = ?");
            $state->execute([$_SESSION['steamid']]);
            $savedknifes = $state->fetchAll();
            
            $knife_t = false;
            $knife_ct = false;
            foreach($savedknifes as $saved) {
                if($saved['weapon_team'] == 1) {
                    $knife_t = $saved;
                }
                if($saved['weapon_team'] == 2) {
                    $knife_ct = $saved;
                }
            }
        }


        foreach($savedskins as $saved) {
            if($saved['weapon_team'] == 1) {
                $saved_t = $saved;
            }
            if($saved['weapon_team'] == 2) {
                $saved_ct = $saved;
            }
        }

        foreach($full_skins as $skin) {
            if($saved_t && $saved_t['weapon_defindex'] == $skin->weapon_defindex && $saved_t['weapon_paint_id'] == $skin->paint) {
                $current_t = $skin;
            }
            if($saved_ct && $saved_ct['weapon_defindex'] == $skin->weapon_defindex && $saved_ct['weapon_paint_id'] == $skin->paint) {
                $current_ct = $skin;
            }
        }

        $weapon_info['img'] = [];
        if($current_t) {
            $weapon_info['name'] = explode(' | ', $current_t->paint_name)[0];
            array_push($weapon_info['img'], $current_t->image);
        }
        if($current_ct) {
            if(!isset($weapon_info['name'])) {
                $weapon_info['name'] = explode(' | ', $current_ct->paint_name)[0];
            }
            array_push($weapon_info['img'], $current_ct->image);
        }

        break;
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
    <script src="<?= GetPrefix(); ?>js/skins.js" defer></script>
    <title><?= $translations->website_name; ?> - Skins</title>
</head>
<body <?= $bodyStyle ?? "" ?>>

<div id="loading">
    <span></span>
</div>

<div class="wrapper">
    <div class="container">
        <nav>
            <button class="preview" data-action="toggle_preview">
                <?php
                foreach($weapon_info['img'] as $imgsrc) {
                    echo "<img src='$imgsrc' loading='lazy'>";
                }
                ?>
            </button>
            <div class="info">
                <p><?= $translations->skins->selected_weapon->weapon_selected_label; ?><br><strong>
                    <?= $weapon_info['name']; ?>
                </strong></p>
                <button class="main-btn" data-action="weapon_choose"><?= $translations->skins->selected_weapon->choose_weapon_button; ?></button>
            </div>
        </nav>
        <div class="skins">
            <h3><?= $translations->skins->selected_weapon->select_skin_label; ?></h3>
            <ul>
                <?php
                switch($selectedweapon_type) {
                    case 'gloves':
                        foreach($gloves as $glove) {
                            if($glove->weapon_defindex != $selectedweapon) {continue;}
                            ?>
                            <li>
                                <button class='card <?= $saved_t['weapon_defindex'] == $glove->weapon_defindex && $current_t->paint == $glove->paint || $saved_ct['weapon_defindex'] == $glove->weapon_defindex && $current_ct->paint == $glove->paint?'selected':''; ?>' data-action='weapon_change' data-weapon='<?= $selectedweapon; ?>' data-defindex='<?= $glove->weapon_defindex; ?>' data-paint='<?= $glove->paint == '0' ?'default':$glove->paint; ?>'>
                                    <div class="imgbox">
                                        <img src='<?= $glove->image; ?>' loading='lazy'>
                                    </div>
                                    <span><?= explode('|', $glove->paint_name)[1]; ?></span>
                                    <div class="marks">
                                        <?php
                                        if($saved_t && $current_t && $saved_t['weapon_defindex'] == $glove->weapon_defindex && $current_t->paint == $glove->paint) {
                                            ?>
                                            <input type="radio" name="marks_<?= $glove->paint; ?>" class="terrormark" checked>
                                            <?php
                                        }
                                        if($saved_ct && $current_ct && $saved_ct['weapon_defindex'] == $glove->weapon_defindex && $current_ct->paint == $glove->paint) {
                                            ?>
                                            <input type="radio" name="marks_<?= $glove->paint; ?>" class="counterterrormark" <?= !$saved_t || !$current_t || $saved_t['weapon_defindex'] != $glove->weapon_defindex || $current_t->paint != $glove->paint?'checked':''; ?>>
                                            <?php
                                        }
                                        ?>
                                    </div>
                                </button>
                            </li>
                            <?php
                        }
                        break;
                    case 'mvp':
                        foreach($songs as $song) {
                            ?>
                            <li>
                                <button class='card <?= $saved_t['music_id'] == $song->id || $saved_ct['music_id'] == $song->id || !$saved_t && $song->id == 0 || !$saved_ct && $song->id == 0?'selected':''; ?>' data-action='mvp_change' data-id='<?= $song->id == 0?'default':$song->id; ?>'>
                                    <div class="imgbox">
                                        <img src='<?= $song->image; ?>' alt='<?= $song->id; ?>' loading='lazy'>
                                    </div>
                                    <span><?= $song->name; ?></span>
                                    <div class="marks">
                                        <?php
                                        if($saved_t && $saved_t['music_id'] == $song->id) {
                                            ?>
                                            <input type="radio" name="marks_<?= $song->id; ?>" class="terrormark" checked>
                                            <?php
                                        }
                                        if($saved_ct && $saved_ct['music_id'] == $song->id) {
                                            ?>
                                            <input type="radio" name="marks_<?= $song->id; ?>" class="counterterrormark" checked>
                                            <?php
                                        }
                                        ?>
                                    </div>
                                </button>
                            </li>
                            <?php
                        }
                        break;
                    case 'agents':
                        foreach($agents as $agent) {
                            if($selectedweapon == 'terrorist' && $agent->team != 2 || $selectedweapon == 'counter-terrorist' && $agent->team != 3) {continue;}
                            ?>
                            <li>
                                <button class='card <?= $saved_t && $saved_t == $agent->model || $saved_ct && $saved_ct == $agent->model || !$saved_t && $selectedweapon == 'terrorist' && $agent->model == 'default' || !$saved_ct && $selectedweapon == 'counter-terrorist' && $agent->model == 'default'?'selected':''; ?>' data-action='agent_change' data-agent='<?= $agent->model; ?>' data-team='<?= $agent->team; ?>'>
                                    <div class="imgbox">
                                        <img src='<?= $agent->image; ?>' alt='<?= $agent->model; ?>' loading='lazy'>
                                    </div>
                                    <span><?= explode('|', $agent->agent_name)[0]; ?></span>
                                </button>
                            </li>
                            <?php
                        }
                        break;
                    default:
                        foreach($full_skins as $skin) {
                            if($skin->weapon_name != $selectedweapon) {continue;}

                            $selected = false;
                            if($is_knife) {
                                if($knife_t && $current_t && $knife_t['knife'] == $skin->weapon_name && $current_t->paint == $skin->paint || $knife_ct && $current_ct && $knife_ct['knife'] == $skin->weapon_name && $current_ct->paint == $skin->paint) {
                                    $selected = true;
                                }else if($skin->weapon_name == 'weapon_knife_default') {
                                    if(!$knife_t || !$knife_ct) {
                                        $selected = true;
                                    }
                                }
                            }else {
                                $selected = $saved_t && $saved_t['weapon_paint_id'] == $skin->paint && !in_array($skin->weapon_name, $ct_only) || $saved_ct && $saved_ct['weapon_paint_id'] == $skin->paint && !in_array($skin->weapon_name, $t_only);
                            }
                            ?>
                            <li>
                                <button class='card <?= $selected?'selected':''; ?>' data-action='weapon_change' data-weapon='<?= $skin->weapon_name; ?>' data-defindex='<?= $skin->weapon_defindex; ?>' data-paint='<?= $skin->paint == '0' ?'default':$skin->paint; ?>'>
                                    <div class="imgbox">
                                        <img src='<?= $skin->image; ?>' alt='<?= $skin->weapon_name; ?>' loading='lazy'>
                                    </div>
                                    <span><?= explode('|', $skin->paint_name)[1]; ?></span>
                                    <div class="marks">
                                        <?php
                                        if(!in_array($skin->weapon_name, $ct_only)) {
                                            if($saved_t && $current_t && $current_t->paint == $skin->paint || $current_t && $current_t->paint == 0 && $skin->paint == 0 && !$is_knife) {
                                            ?>
                                            <input type="radio" name="marks_<?= $skin->paint; ?>" class="terrormark" checked>
                                            <?php
                                        }
                                        }
                                        if(!in_array($skin->weapon_name, $t_only)) {
                                        if($saved_ct && $current_ct && $current_ct->paint == $skin->paint || !$current_ct && $skin->paint == 0 && !$is_knife) {
                                            ?>
                                            <input type="radio" name="marks_<?= $skin->paint; ?>" class="counterterrormark" <?= !$saved_t && !$current_t || $current_t->paint != $skin->paint || in_array($skin->weapon_name, $ct_only)?'checked':''; ?>>
                                            <?php
                                        }
                                        }
                                        ?>
                                    </div>
                                </button>
                            </li>
                            <?php
                        }
                        break;
                }
                ?>
            </ul>
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

</body>
</html>

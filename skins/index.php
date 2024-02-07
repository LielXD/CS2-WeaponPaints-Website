<?php
    require_once '../config.php';

    if(!isset($_SESSION['steamid'])) {
        header('Location: ../');
        exit;
    }

    if(!isset($SteamAPI_KEY) || empty($SteamAPI_KEY)) {
        SendToErrorPage('Please contact support,<br>Steam API key is not valid.');
        exit;
    }

    $conn = require_once '../includes/database.php';
    if(!isset($conn) || $conn != 1) {
        SendToErrorPage("Please contact support,<br>$conn");
        exit;
    }

    if(isset($_POST['defindex']) && isset($_POST['paint']) && isset($_POST['wear']) && isset($_POST['seed']) && isset($_POST['weapon_name'])) {
        if($_POST['weapon_name'] == 'weapon_knife_default') {
            $state = $pdo->prepare('DELETE FROM `wp_player_knife` WHERE `steamid` = ?');
            $state->execute([$_SESSION['steamid']]);

            header("Refresh:0");
            exit;
        }

        $state = $pdo->prepare('SELECT * FROM `wp_player_skins` WHERE `steamid` = ? AND `weapon_defindex` = ?');
        $state->execute([$_SESSION['steamid'], $_POST['defindex']]);

        $player_skin = $state->fetch();

        if(empty($player_skin)) {
            $state = $pdo->prepare('INSERT INTO `wp_player_skins`(`steamid`, `weapon_defindex`, `weapon_paint_id`, `weapon_wear`, `weapon_seed`) VALUES(?, ?, ?, ?, ?)');
            $state->execute([$_SESSION['steamid'], $_POST['defindex'], $_POST['paint'], $_POST['wear'], $_POST['seed']]);
        }else {
            $state = $pdo->prepare('UPDATE `wp_player_skins` SET `steamid` = ?, `weapon_defindex`= ?, `weapon_paint_id` = ?, `weapon_wear` = ?, `weapon_seed` = ? WHERE `steamid` = ? AND `weapon_defindex`= ?');
            $state->execute([$_SESSION['steamid'], $_POST['defindex'], $_POST['paint'], $_POST['wear'], $_POST['seed'],$_SESSION['steamid'], $_POST['defindex']]);
        }

        if(str_contains($_POST['weapon_name'], 'knife') || $_POST['weapon_name'] == 'weapon_bayonet') {
            $state = $pdo->prepare('SELECT * FROM `wp_player_knife` WHERE `steamid` = ?');
            $state->execute([$_SESSION['steamid']]);

            $player_knife = $state->fetch();

            if(empty($player_knife)) {
                $state = $pdo->prepare('INSERT INTO `wp_player_knife`(`steamid`, `knife`) VALUES(?, ?)');
                $state->execute([$_SESSION['steamid'], $_POST['weapon_name']]);
            }else {
                $state = $pdo->prepare('UPDATE `wp_player_knife` SET `knife` = ? WHERE `steamid` = ?');
                $state->execute([$_POST['weapon_name'], $_SESSION['steamid']]);
            }
        }
        
        header("Refresh:0");
        exit;
    }

    $full_skins = json_decode(file_get_contents('skins.json'));

    $steamApiUserInfo = file_get_contents("http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=$SteamAPI_KEY&steamids=".$_SESSION['steamid']);
    $UserInfo = json_decode($steamApiUserInfo)->response->players[0];

    function SendToErrorPage($msg) {
        header("Location: ../error/?msg=$msg");
        exit;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../src/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="../css/main.css" type="text/css">
    <link rel="stylesheet" href="../css/skins.css" type="text/css">
    <script src="script.js" defer></script>
    <title><?= $translations->website_name; ?> - Skins</title>
</head>
<body <?= $bodyStyle ?>>

<div id="loading">
    <span></span>
</div>

<?php
if(!isset($_POST['weapon'])) {
?>
<div class="wrapper">
    <div class="container">
        <h2><?= $translations->skins->choose_weapon; ?></h2>
        <div class="choose">
            <ul>
                <?php
                    $state = $pdo->prepare('SELECT * FROM `wp_player_skins` WHERE `steamid` = ?');
                    $state->execute([$_SESSION['steamid']]);
                    $player_equiped = $state->fetchAll();

                    $player_weapons = [];
                    foreach($player_equiped as $weapon) {
                        $id = $weapon['weapon_defindex'];
                        $player_weapons["$id"] = $weapon;
                    }

                    foreach($full_skins as $weapon) {
                        if($weapon->paint == 0) {
                            if(isset($player_weapons["$weapon->weapon_defindex"])) {
                                foreach($full_skins as $weaponSecond) {
                                    if($weaponSecond->weapon_defindex == $weapon->weapon_defindex && $weaponSecond->paint == $player_weapons["$weapon->weapon_defindex"]['weapon_paint_id']) {
                                        $name = explode('|', $weaponSecond->paint_name)[0];
                                        
                                        echo html_entity_decode("<li>
                                            <button class='card' data-action='weapon_picked' data-weapon=".$weaponSecond->weapon_name.">
                                                <svg data-action='fullscreen' viewBox='0 0 32 32'><path d='M28,2h-6c-1.104,0-2,0.896-2,2s0.896,2,2,2h1.2l-4.6,4.601C18.28,10.921,18,11.344,18,12c0,1.094,0.859,2,2,2  c0.641,0,1.049-0.248,1.4-0.6L26,8.8V10c0,1.104,0.896,2,2,2s2-0.896,2-2V4C30,2.896,29.104,2,28,2z M12,18  c-0.641,0-1.049,0.248-1.4,0.6L6,23.2V22c0-1.104-0.896-2-2-2s-2,0.896-2,2v6c0,1.104,0.896,2,2,2h6c1.104,0,2-0.896,2-2  s-0.896-2-2-2H8.8l4.6-4.601C13.72,21.079,14,20.656,14,20C14,18.906,13.141,18,12,18z'/></svg>
                                                <img src=".$weaponSecond->image." loading='lazy'>
                                                <span>".$name."</span>
                                            </button>
                                        </li>");
                                        break;
                                    }
                                }
                                continue;
                            }

                            $name = explode('|', $weapon->paint_name)[0];
                            
                            echo "<li>
                                <button class='card' data-action='weapon_picked' data-weapon=".$weapon->weapon_name.">
                                    <svg data-action='fullscreen' viewBox='0 0 32 32'><path d='M28,2h-6c-1.104,0-2,0.896-2,2s0.896,2,2,2h1.2l-4.6,4.601C18.28,10.921,18,11.344,18,12c0,1.094,0.859,2,2,2  c0.641,0,1.049-0.248,1.4-0.6L26,8.8V10c0,1.104,0.896,2,2,2s2-0.896,2-2V4C30,2.896,29.104,2,28,2z M12,18  c-0.641,0-1.049,0.248-1.4,0.6L6,23.2V22c0-1.104-0.896-2-2-2s-2,0.896-2,2v6c0,1.104,0.896,2,2,2h6c1.104,0,2-0.896,2-2  s-0.896-2-2-2H8.8l4.6-4.601C13.72,21.079,14,20.656,14,20C14,18.906,13.141,18,12,18z'/></svg>
                                    <img src=".$weapon->image." loading='lazy'>
                                    <span>".$name."</span>
                                </button>
                            </li>";
                        }
                    }
                ?>
            </ul>
        </div>
    </div>
</div>
<?php
}else {

    if(str_contains($_POST['weapon'], 'knife')) {
        $state = $pdo->prepare('SELECT `knife` FROM `wp_player_knife` WHERE `steamid` = ?');
        $state->execute([$_SESSION['steamid']]);
    
        $knifeType = $state->fetch();

        if(!empty($knifeType)) {
            $knifeType = $knifeType['knife'];
        }
    }

    $currentWeapon = false;

    $weapon_skins = [];
    foreach($full_skins as $skin) {
        if($skin->weapon_name == $_POST['weapon']) {
            array_push($weapon_skins, $skin);

            if($skin->paint == 0) {
                $currentWeapon = $skin;
            }
        }
    }
    
    $state = $pdo->prepare('SELECT * FROM `wp_player_skins` WHERE `steamid` = ? AND `weapon_defindex` = ?');
    $state->execute([$_SESSION['steamid'], $weapon_skins[0]->weapon_defindex]);

    $player_skin = $state->fetch();

    $weapon_wear = ['','','','',''];
    $weapon_seed = 0;
    if(!empty($player_skin)) {
        foreach($weapon_skins as $weapon) {
            if($player_skin['weapon_defindex'] == $weapon->weapon_defindex && $player_skin['weapon_paint_id'] == $weapon->paint) {
                $currentWeapon = $weapon;
                break;
            }
        }

        $weapon_seed = $player_skin['weapon_seed'];
        if($player_skin['weapon_wear'] == 0) {
            $weapon_wear[0] = 'selected';
        }else if($player_skin['weapon_wear'] == 0.07) {
            $weapon_wear[1] = 'selected';
        }else if($player_skin['weapon_wear'] == 0.15) {
            $weapon_wear[2] = 'selected';
        }else if($player_skin['weapon_wear'] == 0.38) {
            $weapon_wear[3] = 'selected';
        }else if($player_skin['weapon_wear'] == 0.45) {
            $weapon_wear[4] = 'selected';
        }
    }
?>

<div class="wrapper">
    <div class="container">
        <nav>
            <button class="preview" data-action="weapon_choose">
                <img src="<?= $currentWeapon->image; ?>" alt="<?= $currentWeapon->weapon_name; ?>">
            </button>
            <div class="info">
                <p><?= $translations->skins->selected_weapon->weapon_selected_label; ?><br><strong><?= explode('|', $currentWeapon->paint_name)[0]; ?></strong></p>
                <button class="main-btn" data-action="weapon_choose"><?= $translations->skins->selected_weapon->choose_weapon_button; ?></button>
            </div>
            <div class="settings" <?php if($_POST['weapon'] == 'weapon_knife_default') { ?>style="display: none;"<?php } ?>>
                <div>
                    <label for="wear"><?= $translations->skins->selected_weapon->wear; ?></label>
                    <select name="wear" id="wear">
                        <option value="0.00"<?= $weapon_wear[0]; ?>>Factory New</option>
                        <option value="0.07"<?= $weapon_wear[1]; ?>>Minimal Wear</option>
                        <option value="0.15"<?= $weapon_wear[2]; ?>>Field Tested</option>
                        <option value="0.38"<?= $weapon_wear[3]; ?>>Well Worn</option>
                        <option value="0.45"<?= $weapon_wear[4]; ?>>Battle Scarred</option>
                    </select>
                </div>
                <div>
                    <label for="seed"><?= $translations->skins->selected_weapon->seed; ?></label>
                    <input type="number" value="<?= $weapon_seed; ?>" min="0" max="1000" step="1" name="seed" id="seed" placeholder="1 - 1000">
                </div>
            </div>
        </nav>
        <div class="skins">
            <h3><?= $translations->skins->selected_weapon->select_skin_label; ?></h3>
            <ul>
                <?php
                foreach($weapon_skins as $weapon) {
                    if($weapon->weapon_name == 'weapon_knife_default' && !empty($knifeType) || $currentWeapon->paint != $weapon->paint) {
                        echo "<li>
                            <button class='card' data-action='weapon_change' data-weapon='$weapon->weapon_name' data-defindex='$weapon->weapon_defindex' data-paint='$weapon->paint'>
                                <svg data-action='fullscreen' viewBox='0 0 32 32'><path d='M28,2h-6c-1.104,0-2,0.896-2,2s0.896,2,2,2h1.2l-4.6,4.601C18.28,10.921,18,11.344,18,12c0,1.094,0.859,2,2,2  c0.641,0,1.049-0.248,1.4-0.6L26,8.8V10c0,1.104,0.896,2,2,2s2-0.896,2-2V4C30,2.896,29.104,2,28,2z M12,18  c-0.641,0-1.049,0.248-1.4,0.6L6,23.2V22c0-1.104-0.896-2-2-2s-2,0.896-2,2v6c0,1.104,0.896,2,2,2h6c1.104,0,2-0.896,2-2  s-0.896-2-2-2H8.8l4.6-4.601C13.72,21.079,14,20.656,14,20C14,18.906,13.141,18,12,18z'/></svg>
                                <img src='$weapon->image' alt='$weapon->weapon_name' loading='lazy'>
                                <span>".explode('|', $weapon->paint_name)[1]."</span>
                            </button>
                        </li>";
                    }else {
                        echo "<li data-text='".$translations->skins->selected_weapon->already_equiped_message."'>
                            <button class='card selected' data-action='weapon_change' data-weapon='$weapon->weapon_name' data-defindex='$weapon->weapon_defindex' data-paint='$weapon->paint'>
                                <svg data-action='fullscreen' viewBox='0 0 32 32'><path d='M28,2h-6c-1.104,0-2,0.896-2,2s0.896,2,2,2h1.2l-4.6,4.601C18.28,10.921,18,11.344,18,12c0,1.094,0.859,2,2,2  c0.641,0,1.049-0.248,1.4-0.6L26,8.8V10c0,1.104,0.896,2,2,2s2-0.896,2-2V4C30,2.896,29.104,2,28,2z M12,18  c-0.641,0-1.049,0.248-1.4,0.6L6,23.2V22c0-1.104-0.896-2-2-2s-2,0.896-2,2v6c0,1.104,0.896,2,2,2h6c1.104,0,2-0.896,2-2  s-0.896-2-2-2H8.8l4.6-4.601C13.72,21.079,14,20.656,14,20C14,18.906,13.141,18,12,18z'/></svg>
                                <img src='$weapon->image' alt='$weapon->weapon_name' loading='lazy'>
                                <span>".explode('|', $weapon->paint_name)[1]."</span>
                            </button>
                        </li>";
                    }
                }
                ?>
            </ul>
        </div>
    </div>
</div>
<?php
}
?>

<div id="modalFullscreen">
    <button data-action="exit_fullscreen">
        <img src="https://raw.githubusercontent.com/Nereziel/cs2-WeaponPaints/main/website/img/skins/weapon_bizon_so_red_light.png">
        <svg data-action="exit_fullscreen" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"><line x1="18" x2="6" y1="6" y2="18"/><line x1="6" x2="18" y1="6" y2="18"/></svg>
    </button>
</div>

<footer>
    <div class="wrapper">
        <div class="info">
            <img src="<?= $UserInfo->avatarfull ?>" alt="name">
            <p><?= str_replace('{{name}}', "<strong>$UserInfo->personaname</strong>", $translations->skins->footer->signedin); ?></p>
        </div>
        <div class="credit">
            <p>This website created by LielXD</p>
        </div>
        <a class="main-btn" href="../includes/authorize.php?signout=true" onclick="ToggleLoading();"><?= $translations->skins->footer->sign_out ?>
            <svg viewBox="0 0 24 24"><path d="M12,10c1.1,0,2-0.9,2-2V4c0-1.1-0.9-2-2-2s-2,0.9-2,2v4C10,9.1,10.9,10,12,10z"/><path d="M19.1,4.9L19.1,4.9c-0.3-0.3-0.6-0.4-1.1-0.4c-0.8,0-1.5,0.7-1.5,1.5c0,0.4,0.2,0.8,0.4,1.1l0,0c0,0,0,0,0,0c0,0,0,0,0,0    c1.3,1.3,2,3,2,4.9c0,3.9-3.1,7-7,7s-7-3.1-7-7c0-1.9,0.8-3.7,2.1-4.9l0,0C7.3,6.8,7.5,6.4,7.5,6c0-0.8-0.7-1.5-1.5-1.5    c-0.4,0-0.8,0.2-1.1,0.4l0,0C3.1,6.7,2,9.2,2,12c0,5.5,4.5,10,10,10s10-4.5,10-10C22,9.2,20.9,6.7,19.1,4.9z"/></svg>
        </a>
    </div>
</footer>

</body>
</html>
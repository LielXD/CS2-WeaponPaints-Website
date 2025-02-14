<?php

if(file_exists('./config.php')) {
    exit;
}

$langs = scandir('translation/');
array_shift($langs);
array_shift($langs);

if(isset($_POST['generate'])) {

try {
if($_POST['color'] == 'random') {$_POST['color'] = true;}else {$_POST['color'] = '"'.$_POST['color'].'"';}

file_put_contents('./config.php', '<?php

// Choose your translate file name located in translation/filename.json
// You can add your own translation.
$Website_Translate = "'.$_POST['translation'].'";

// You can choose your own theme color
// false/empty - will use the default color.
// any html acceptable color - will display that color: "#5D3FD3".
// true - this will get a random color.
$Website_MainColor = '.$_POST['color'].';

// Enable this if you want categories else it will display all weapons.
$Website_UseCategories = '.$_POST['categories'].';

// Enable this if you want 3d preview of skins.
// note: disabling this will disable stickers custom placement too (not an option yet, future feature).
$Website_UseThreejs = '.$_POST['threejs'].';

// Exclusive team weapons will only be able to set to their team.
// for example m4a4 skins will only be equipped to ct team, skin will not be visible on t side.
$Website_TeamOnlyWeapons = '.$_POST['teamonly'].';

// Select which settings you want in the menu.
$Website_Settings = [
    "language" => '.$_POST['language'].',  // user can select his own language.
    "theme" => '.$_POST['theme'].'      // user can change his own color theme.
];

// Write here your steam api key, get one from here: https://steamcommunity.com/dev/apikey.
$SteamAPI_KEY = "'.$_POST['steamapi'].'";

$DatabaseInfo = [
    "host" => "'.$_POST['db_host'].'",
    "database" => "'.$_POST['db_name'].'",
    "username" => "'.$_POST['db_username'].'",
    "password" => "'.$_POST['db_password'].'",
    "port" => "'.$_POST['db_port'].'"
];
');


}catch(Exception $err) {
    echo $err->getMessage();
}


exit;

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
    <style>
        body {
            background: linear-gradient(225deg, black, var(--main-color));
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: flex-start;
        }

        .wrapperbox {
            position: relative;
            display: flex;
            justify-content: space-around;
            align-items: center;
            width: 100%;
            height: 100%;
            padding: 50px;
            flex-wrap: wrap;
        }

        .title-conf {
            display: grid;
            place-items: center;
            gap: 10px;
            color: rgba(255, 255, 255, 0.6);
            font-size: clamp(20px, 3vw, 25px);
            white-space: nowrap;
        }
        .title-conf h2 {
            color: var(--main-color);
            background: rgba(255, 255, 255, 0.6);
            padding: 5px 20px;
            border-radius: 5px;
            font-size: clamp(30px, 7vw, 60px);
        }

        form {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 25px;
            height: 100%;
        }

        .box {
            gap: 50px;
        }
        .input p {
            color: rgba(255, 255, 255, 0.6);
            max-width: 300px;
        }
    </style>
    <script src="<?= GetPrefix(); ?>js/skins.js" defer></script>
    <title>LielXD CS2 Server - Generate config</title>
</head>
<body <?= $bodyStyle ?? "" ?>>

<div id="loading">
    <span></span>
</div>

<div id="messages"></div>

<div class="wrapperbox">
    <div class="title-conf">
        <h2>LielXD Weaponpaints</h2>
        <p>setup website</p>
    </div>
    <form method="post" name="settings">
        <div class="input">
            <label for="translation">Default translation</label>
            <select name="translation">
                <?php
                foreach($langs as $lan) {
                    $langcode = str_replace('.json', '', $lan);
                    echo "<option value='$langcode'>$langcode</option>";
                }
                ?>
            </select>
        </div>
        <div class="input">
            <label for="color">Default color</label>
            <select name="color">
                <option value="default">Default color</option>
                <option value="random">Random color</option>
                <option value="custom">Custom color</option>
            </select>
            <input type="color" name="colorcustom" style="display: none;margin-top: 10px;" value="#4f75ff">
        </div>
        <div class="input">
            <label for="categories">Categories</label>
            <div class="box">
                <input type="checkbox" name="categories">
                <p>Display categories otherwise display all weapons.</p>
            </div>
        </div>
        <div class="input">
            <label for="threejs">3D skins preview</label>
            <div class="box">
                <input type="checkbox" name="threejs">
                <p>Display 3D preview of skins.<br>note: disabling this will disable stickers custom placement too.</p>
            </div>
        </div>
        <div class="input">
            <label for="threejs">Team only weapons</label>
            <div class="box">
                <input type="checkbox" name="teamonly">
                <p>Apply exclusive team weapons only to their team.<br>Example: M4A4 skins will equipped only to CT side.</p>
            </div>
        </div>
        <div class="input">
            <label for="settings">User settings</label>
            <div class="box" style="margin-bottom: 20px;">
                <input type="checkbox" name="language">
                <p>Language - let the user pick his own language.</p>
            </div>
            <div class="box">
                <input type="checkbox" name="theme">
                <p>Theme - let the user choose his own website color.</p>
            </div>
        </div>
        <button class="main-btn" style="width: 100%;">Next</button>
    </form>
</div>

<script>
    window.onload = function() {
        document.querySelectorAll('option').forEach(element => {
            try {
                const langdisplay = new Intl.DisplayNames([element.innerHTML], {type: 'language'});
                element.innerHTML = langdisplay.of(element.innerHTML);
            }catch(err) {}
        });
    }

    let colorselect = document.querySelector('select[name="color"]');
    let colorinput = document.querySelector('input[name="colorcustom"]');
    colorselect.addEventListener('input', function(e) {
        switch(colorselect.value) {
            case 'default':
                document.body.style.removeProperty('--main-color');
                break;
            case 'random':
                document.body.style.setProperty('--main-color', `#${Math.ceil(Math.random() * (999999 - 111111) + 111111)}`);
                break;
        }

        if(colorselect.value == 'custom' && colorinput.style.display) {
            colorinput.value = document.body.style.getPropertyValue('--main-color') || getComputedStyle(document.body).getPropertyValue('--main-color');
            colorinput.style.display = null;
        }else if(colorselect.value != 'custom' && !colorinput.style.display) {
            colorinput.style.display = 'none';
        }
    });
    colorinput.addEventListener('input', function() {
        document.body.style.setProperty('--main-color', colorinput.value);
    });

    let form = document.querySelector('form');
    let formdata = new FormData();
    form.onsubmit = function(e) {
        e.preventDefault();
        ToggleLoading();

        if(form.name == 'secret') {
            let stop = false;
            let steamapi = document.querySelector('input[name="steamapi"]');
            if(!steamapi || !steamapi.value || steamapi.value == '') {
                AddMessage('You must have a valid Steam API key!', 'warning');
                ToggleLoading(true);
                return;
            }
            formdata.set('steamapi', steamapi.value);
            
            let database_host = document.querySelector('input[name="db_host"]');
            if(!database_host || !database_host.value || database_host.value == '') {
                AddMessage('You must have a valid Database host!', 'warning');
                ToggleLoading(true);
                return;
            }
            formdata.set('db_host', database_host.value);

            let database_port = document.querySelector('input[name="db_port"]');
            if(!database_port || !database_port.value || database_port.value == '') {
                AddMessage('You must have a valid Database port!', 'warning');
                ToggleLoading(true);
                return;
            }
            formdata.set('db_port', database_port.value);

            let database_username = document.querySelector('input[name="db_username"]');
            if(!database_username || !database_username.value || database_username.value == '') {
                AddMessage('You must have a valid Database username!', 'warning');
                ToggleLoading(true);
                return;
            }
            formdata.set('db_username', database_username.value);

            let database_password = document.querySelector('input[name="db_password"]');
            if(!database_password || !database_password.value || database_password.value == '') {
                AddMessage('You must have a valid Database password!', 'warning');
                ToggleLoading(true);
                return;
            }
            formdata.set('db_password', database_password.value);

            let database_name = document.querySelector('input[name="db_name"]');
            if(!database_name || !database_name.value || database_name.value == '') {
                AddMessage('You must have a valid Database name!', 'warning');
                ToggleLoading(true);
                return;
            }
            formdata.set('db_name', database_name.value);

            formdata.set('generate', true);

            fetch('./config-gen.php', {
                method: 'post',
                body: formdata
            }).then(resp => resp.text()).then(resp => {
                if(resp && resp != '') {
                    ToggleLoading(true);
                    AddMessage(resp, 'fail', 10000);
                    return;
                }

                window.location.reload();
            });

            return;
        }

        let translation = document.querySelector('select[name="translation"]');
        if(translation) {
            formdata.set('translation', translation.value);
        }
        
        if(colorselect) {
            if(colorselect.value != 'random') {
                let color = getComputedStyle(document.body).getPropertyValue('--main-color');
                formdata.set('color', color);
            }else {
                formdata.set('color', 'random');
            }
        }

        let categories = document.querySelector('input[name="categories"]');
        if(categories) {
            formdata.set('categories', categories.checked);
        }
        
        let threejs = document.querySelector('input[name="threejs"]');
        if(threejs) {
            formdata.set('threejs', threejs.checked);
        }

        let teamonly = document.querySelector('input[name="teamonly"]');
        if(teamonly) {
            formdata.set('teamonly', teamonly.checked);
        }

        let language = document.querySelector('input[name="language"]');
        if(language) {
            formdata.set('language', language.checked);
        }

        let theme = document.querySelector('input[name="theme"]');
        if(theme) {
            formdata.set('theme', theme.checked);
        }
        
        form.name = 'secret';

        form.innerHTML = `<div class="input">
            <label for="steamapi">Steam API key</label>
            <p style='margin: 10px 0;'><a href='https://steamcommunity.com/dev/apikey' target='_blank'>Click here</a> to generate one!</p>
            <input type="text" name="steamapi" placeholder="Steam API key">
        </div>
        <div class="input">
            <label for="steamapi">Database credentials</label>
            <div class="box" style="margin-bottom: 20px;">
                <input type="text" name="db_host" autocomplete="off" placeholder="Host">
                <input type="number" name="db_port" placeholder="Port" autocomplete="off" value="3306" style="width: 20%;">
            </div>
            <input type="text" name="db_username" placeholder="Username" autocomplete="off" style="margin-bottom: 20px;">
            <input type="password" name="db_password" placeholder="Password" autocomplete="off" style="margin-bottom: 20px;">
            <input type="text" name="db_name" placeholder="Database name" autocomplete="off">
        </div>
        <button class="main-btn" style="width: 100%;">Done</button>`;

        ToggleLoading(true);
    }

    function AddMessage(msgContent = '', type = 'default', timeout = 3000) {
        if(!msgContent || msgContent == '' || typeof(msgContent) !== 'string') {return;}

        const msg = document.createElement('span');
        msg.classList.add('msg');
        msg.style.setProperty('--timeout', `${timeout}ms`);

        const content = document.createElement('p');
        content.innerHTML = msgContent;

        let svg = '';
        switch(type) {
            case 'fail':
                svg = '<svg viewBox="0 0 512 512"><path d="M255.997,460.351c112.685,0,204.355-91.668,204.355-204.348S368.682,51.648,255.997,51.648  c-112.68,0-204.348,91.676-204.348,204.355S143.317,460.351,255.997,460.351z M255.997,83.888  c94.906,0,172.123,77.209,172.123,172.115c0,94.898-77.217,172.117-172.123,172.117c-94.9,0-172.108-77.219-172.108-172.117  C83.888,161.097,161.096,83.888,255.997,83.888z"/><path d="M172.077,341.508c3.586,3.523,8.25,5.27,12.903,5.27c4.776,0,9.54-1.84,13.151-5.512l57.865-58.973l57.878,58.973  c3.609,3.672,8.375,5.512,13.146,5.512c4.658,0,9.316-1.746,12.902-5.27c7.264-7.125,7.369-18.793,0.242-26.051l-58.357-59.453  l58.357-59.461c7.127-7.258,7.021-18.92-0.242-26.047c-7.252-7.123-18.914-7.018-26.049,0.24l-57.878,58.971l-57.865-58.971  c-7.135-7.264-18.797-7.363-26.055-0.24c-7.258,7.127-7.369,18.789-0.236,26.047l58.351,59.461l-58.351,59.453  C164.708,322.715,164.819,334.383,172.077,341.508z"/></svg>';
                break;
            case 'success':
                svg = '<svg viewBox="0 0 32 32"><path d="M16,1A15,15,0,1,0,31,16,15,15,0,0,0,16,1Zm0,28.33A13.33,13.33,0,1,1,29.33,16,13.35,13.35,0,0,1,16,29.33Z"/><polygon points="13.91 18.59 10.71 15.39 9.29 16.8 13.91 21.41 23.61 11.71 22.2 10.29 13.91 18.59"/></svg>';
                break;
            case 'warning':
                svg = '<svg viewBox="0 0 24 24"><path d="M21.171,15.398l-5.912-9.854C14.483,4.251,13.296,3.511,12,3.511s-2.483,0.74-3.259,2.031l-5.912,9.856  c-0.786,1.309-0.872,2.705-0.235,3.83C3.23,20.354,4.472,21,6,21h12c1.528,0,2.77-0.646,3.406-1.771  C22.043,18.104,21.957,16.708,21.171,15.398z M12,17.549c-0.854,0-1.55-0.695-1.55-1.549c0-0.855,0.695-1.551,1.55-1.551  s1.55,0.696,1.55,1.551C13.55,16.854,12.854,17.549,12,17.549z M13.633,10.125c-0.011,0.031-1.401,3.468-1.401,3.468  c-0.038,0.094-0.13,0.156-0.231,0.156s-0.193-0.062-0.231-0.156l-1.391-3.438C10.289,9.922,10.25,9.712,10.25,9.5  c0-0.965,0.785-1.75,1.75-1.75s1.75,0.785,1.75,1.75C13.75,9.712,13.711,9.922,13.633,10.125z"/></svg>';
                break;
            default:
                svg = '<svg viewBox="0 0 16 16"><path d="M8,2C4.69,2,2,4.69,2,8s2.69,6,6,6s6-2.69,6-6S11.31,2,8,2z M8,13c-2.76,0-5-2.24-5-5s2.24-5,5-5s5,2.24,5,5    S10.76,13,8,13z"/><path d="M8,6.85c-0.28,0-0.5,0.22-0.5,0.5v3.4c0,0.28,0.22,0.5,0.5,0.5s0.5-0.22,0.5-0.5v-3.4C8.5,7.08,8.28,6.85,8,6.85z"/><path d="M8.01,4.8C7.75,4.78,7.51,5.05,7.5,5.32c0,0.01,0,0.07,0,0.08c0,0.27,0.21,0.47,0.49,0.48c0,0,0.01,0,0.01,0    c0.27,0,0.49-0.24,0.5-0.5c0-0.01,0-0.11,0-0.11C8.5,4.98,8.29,4.8,8.01,4.8z"/></svg>';
                break;
        }

        msg.innerHTML = svg;
        msg.append(content);

        document.querySelector('#messages').append(msg);
        setTimeout(() => {
            msg.remove();
        }, timeout);
    }
</script>

</body>
</html>
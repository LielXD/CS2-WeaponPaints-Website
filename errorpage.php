<?php

if(!isset($documentError_Code)) {
    if(isset($_GET['errorcode'])) {
        $documentError_Code = $_GET['errorcode'];
    }else {
        return;
    }
}

if(!function_exists('GetPrefix')) {
    function GetPrefix() {
        $path = explode('\\', dirname(__FILE__));
        $mainfolder = $path[count($path)-1];
    
        $url = explode($mainfolder, $_SERVER['REQUEST_URI'])[0];
        return $url.$mainfolder.'/';
    }
}

if(isset($translations)) {
    $errors = [
        403 => $translations->error->unauthorized,
        404 => $translations->error->notfound,
        500 => $translations->error->serverinternal,
        'translatefile' => 'Please contact website owner,<br>if you are the owner of this website, please add translation files or set the correct one in the config file.',
        'steamapi' => 'Please contact website owner,<br>if you are the owner of this website, please set steam api key.',
        'database' => 'Please contact website owner,<br>if you are the owner of this website, please set the correct database credentials',
        'setup-plugin' => 'Database is Empty!<br>Please contact website owner,<br>if you are the owner of this website, there is an error with the setup of the plugin: it did not create the database tables.'
    ];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= GetPrefix(); ?>css/main.css">
    <link rel="stylesheet" href="<?= GetPrefix(); ?>css/error.css">
    <title><?= $translations->website_name ?? 'CS2 Skins Website by LielXD'; ?> - <?php if(isset($errors[$documentError_Code])) {echo 'Error '.$documentError_Code;}else {echo 'something happened';} ?>!</title>
</head>
<body <?= $bodyStyle ?? "" ?>>
    
    <div class="wrapper">
        <main>
            <h2><?= str_replace('{{code}}', $documentError_Code, $translations->error->code ?? "Error {{code}}!");?></h2>
            <p><?php if(isset($errors[$documentError_Code])) {echo $errors[$documentError_Code];}else if(isset($documentError_Message)) {echo $documentError_Message;}else {echo "please contact support.";} ?></p>
            <?php
            if($documentError_Code != 'translatefile' && $documentError_Code != 'steamapi') {
            ?>
            <a class="main-btn" href="<?= GetPrefix(); ?>"><?= $translations->error->button ?? "Return to Website"; ?>
                <svg viewBox="0 0 16 16"><path d="M15,10 L15,14 C15,15.1045695 14.1045695,16 13,16 L2,16 C0.8954305,16 0,15.1045695 0,14 L0,3 C0,1.8954305 0.8954305,1 2,1 L6,1 L6,3 L2,3 L2,14 L13,14 L13,10 L15,10 Z M13.9971001,3.41421356 L7.70420685,9.70710678 L6.28999329,8.29289322 L12.5828865,2 L8.99710007,2 L8.99710007,0 L15.9971001,0 L15.9971001,7 L13.9971001,7 L13.9971001,3.41421356 Z" fill-rule="evenodd"/></svg>
            </a>
            <?php
            }
            ?>
        </main>
        <footer>
            <h3><?= $translations->website_name ?? 'CS2 Skins Website by LielXD'; ?></h3>
        </footer>
    </div>

</body>
</html>
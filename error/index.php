<?php
    require_once '../config.php';
    
    $errors = [
        403 => $translations->error->unauthorized,
        404 => $translations->error->notfound,
        500 => $translations->error->serverinternal
    ];
    
    if(!isset($_GET['msg'])) {
        header('Location: ../');
        exit;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/<?= $Website_Subfolder; ?>/css/main.css">
    <link rel="stylesheet" href="/<?= $Website_Subfolder; ?>/css/error.css">
    <title><?= $translations->website_name; ?> - <?php if(isset($errors[$_GET['msg']])) {echo 'Error '.$_GET['msg'];}else {echo 'something happened';} ?>!</title>
</head>
<body <?= $bodyStyle ?>>
    
    <div class="wrapper">
        <main>
            <?php
            if(isset($errors[$_GET['msg']])) {
            ?>
            <h2><?= str_replace('{{code}}', $_GET['msg'], $translations->error->code);?></h2>
            <p><?= $errors[$_GET['msg']]; ?></p>
            <?php
            }else {
            ?>
            <h2><?= $translations->error->message; ?></h2>
            <p><?= $_GET['msg']; ?></p>
            <?php
            }
            ?>
            <a class="main-btn" href="/<?= $Website_Subfolder; ?>"><?= $translations->error->button; ?>
                <svg viewBox="0 0 16 16"><path d="M15,10 L15,14 C15,15.1045695 14.1045695,16 13,16 L2,16 C0.8954305,16 0,15.1045695 0,14 L0,3 C0,1.8954305 0.8954305,1 2,1 L6,1 L6,3 L2,3 L2,14 L13,14 L13,10 L15,10 Z M13.9971001,3.41421356 L7.70420685,9.70710678 L6.28999329,8.29289322 L12.5828865,2 L8.99710007,2 L8.99710007,0 L15.9971001,0 L15.9971001,7 L13.9971001,7 L13.9971001,3.41421356 Z" fill-rule="evenodd"/></svg>
            </a>
        </main>
        <footer>
            <h3><?= $translations->website_name; ?></h3>
        </footer>
    </div>

</body>
</html>
<?php

if(!function_exists("Path")) {
    return;
}

$host = $DatabaseInfo['host'];
$database = $DatabaseInfo['database'];
$username = $DatabaseInfo['username'];
$password = $DatabaseInfo['password'];
$port = $DatabaseInfo['port'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;port=$port", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    return true;
}catch(Exception $err) {
    return $err->getMessage();
}

?>
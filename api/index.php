<?php
use Api\Api;

require __DIR__ . '/../vendor/autoload.php';
spl_autoload_register(function ($class) {
    $class = str_replace('\\', '/', $class);
    include "../{$class}.php";
});

$api = new Api();
$api->fetch(file_get_contents('php://input'));

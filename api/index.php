<?php

use Api\Api;

require "../vendor/autoload.php";
require "../config.php";
spl_autoload_register(function ($class) {
    $class = str_replace("\\", "/", $class);
    include "../{$class}.php";
});
error_reporting(E_ERROR | E_PARSE);
$api = new Api();
$api->fetch(file_get_contents("php://input"));

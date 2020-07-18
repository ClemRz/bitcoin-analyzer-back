<?php
use Api\Api;

require __DIR__ . '/../vendor/autoload.php';
spl_autoload_register(function ($class) {
    $class = str_replace('\\', '/', $class);
    include "../{$class}.php";
});

new Api();


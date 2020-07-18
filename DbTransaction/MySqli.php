<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config.php';

$db = new MysqliDb (DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$donors = $db->get('donors', 10);
if ($db->count > 0) {
    header('Content-type: text/plain');
    foreach ($donors as $donor) {
        print_r($donor);
    }
}
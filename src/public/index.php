<?php
/**
 * Front Controller
 */

require("../bootstrap.php");

use Controllers\BtcUsdController;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$uriParts = explode("/", $_SERVER["REQUEST_URI"]);
$method = $_SERVER["REQUEST_METHOD"];
$symbol = $uriParts[1];

$db = new MysqliDb($_ENV["DB_HOST"], $_ENV["MYSQL_USER"], $_ENV["MYSQL_PASSWORD"], $_ENV["MYSQL_DATABASE"], $_ENV["DB_PORT"]);

switch (strtoupper($symbol)) {
    case BtcUsdController::SYMBOL:
        $controller = new BtcUsdController($db, $method, $_SERVER["REQUEST_URI"]);
        break;
    default:
        header("HTTP/1.1 404 Not Found");
        exit();
}
$controller->processRequest();

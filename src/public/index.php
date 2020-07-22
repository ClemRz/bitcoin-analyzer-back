<?php
/**
 * Front Controller
 */

use Controllers\BtcUsdController;

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    if (0 === error_reporting()) { // error was suppressed with the @-operator
        return false;
    }
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

try {
    require("../bootstrap.php");

    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    $method = $_SERVER["REQUEST_METHOD"];
    $uri = $_SERVER["REQUEST_URI"]; // e.g. "/1594789200/1594875600/BTCUSD.json"

    $matches = Array();
    preg_match("/.*\/([^.]+)\./", $uri, $matches);
    $symbol = empty($matches) ? "" : $matches[1]; // will result in a 404 if extension (.<format>) is missing from the URI

    $db = new MysqliDb($_ENV["DB_HOST"], $_ENV["MYSQL_USER"], $_ENV["MYSQL_PASSWORD"], $_ENV["MYSQL_DATABASE"], $_ENV["DB_PORT"]);

    switch (strtoupper($symbol)) {
        case BtcUsdController::SYMBOL:
            $controller = new BtcUsdController($db, $method, $uri);
            break;
        default:
            header("HTTP/1.1 404 Not Found");
            exit();
    }
    $controller->processRequest();
} catch (Throwable $t) {
    error_log($t, 0); // Send the error to Apache's error.log
    header("HTTP/1.1 500 Internal Server Error"); // Obfuscate the error from the WWW
}
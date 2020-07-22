<?php
/**
 * CLI Controller
 */

$scriptsWhiteList = Array("initialize", "update");

$script = $argv[1];

if (!in_array($script, $scriptsWhiteList)) {
    exit("Wrong argument, possible values are: " . implode(", ", $scriptsWhiteList) . PHP_EOL);
}

use DbGateways\BtcUsdGateway;

require(__DIR__ . "/../bootstrap.php");
require(__DIR__ . "/./{$script}.php");

try {
    echo(sprintf("Connecting to db %s with user %s on %s:%d." . PHP_EOL, $_ENV["MYSQL_DATABASE"], $_ENV["MYSQL_USER"], $_ENV["DB_HOST"], $_ENV["DB_PORT"]));
    $db = new MysqliDb($_ENV["DB_HOST"], $_ENV["MYSQL_USER"], $_ENV["MYSQL_PASSWORD"], $_ENV["MYSQL_DATABASE"], $_ENV["DB_PORT"]);
    echo("Connected" . PHP_EOL);
    $intervals = Array(BtcUsdGateway::ONE_DAY, BtcUsdGateway::ONE_HOUR, BtcUsdGateway::ONE_MINUTE);
    foreach ($intervals as $interval) {
        echo("Fetching data with interval {$interval}" . PHP_EOL);
        run($db, $interval);
        echo("{$interval} interval data stored." . PHP_EOL);
    }
    echo("Success." . PHP_EOL);
} catch (Exception $e) {
    exit("Ended with error: {$e->getMessage()}" . PHP_EOL);
}
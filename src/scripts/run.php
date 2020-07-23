<?php
/**
 * CLI Controller
 */

use DbGateways\BtcUsdGateway;

/**
 * Wait for the dependencies to be installed
 *
 * During the docker orchestration there is no easy way for
 * the migration part to wait for Composer to install dependencies.
 * This is my way to solve that issue.
*/
function waitForDependencies(): void
{
    $autoload = __DIR__ . "/../vendor/autoload.php";
    $maxIntents = 4;
    $intents = 0;
    while (!file_exists($autoload) && $intents < $maxIntents) {
        $intents++;
        echo("Waiting 15 seconds for composer to install dependencies" . PHP_EOL);
        sleep(5);
    }
    if (!file_exists($autoload)) {
        throw new Exception("Could not find dependencies." . PHP_EOL);
    }
}

try {
    $scriptsWhiteList = Array("initialize", "update");
    $script = $argv[1];
    if (!in_array($script, $scriptsWhiteList)) {
        throw new Exception("Wrong argument, possible values are: " . implode(", ", $scriptsWhiteList));
    }

    waitForDependencies();

    require(__DIR__ . "/../bootstrap.php");
    require(__DIR__ . "/./{$script}.php");

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
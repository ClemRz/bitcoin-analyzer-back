<?php

/**
 * Script for backend initialization
 *
 * Tasks performed:
 *   1. Connect to the database
 *   2. For one day, one hour and one minute intervals do:
 *      a. Abort all if table contains values already
 *      b. Fetch the maximum amount of historical data from Yahoo
 *      c. Store data in database in chunks
 * */

use Controllers\YahooController;
use DbGateways\BtcUsdGateway;
use HttpGateways\YahooGateway;

require(__DIR__ . "/../bootstrap.php");

/**
 * Fetches and stores data for a given interval
 *
 * @param MysqliDb $db
 * @param string $interval
 * @throws Exception
 */
function run(MysqliDb $db, string $interval): void
{
    $btcUsdGateway = new BtcUsdGateway($db);
    $btcUsdGateway->setSuffix($interval);
    echo("Checking if the table is empty." . PHP_EOL);
    $cnt = $btcUsdGateway->countRows();
    if ($cnt > 0) {
        throw new Exception("Table is not empty, it contains {$cnt} entries. Aborting." . PHP_EOL);
    }
    echo("Table is empty, continuing." . PHP_EOL);
    $data = getAllPossibleData($interval);
    $chunkSize = 100;
    echo(sprintf("Inserting %d entries by chunks of %d rows." . PHP_EOL, count($data), $chunkSize));
    $btcUsdGateway->batchInsert($data, $chunkSize);
}

/**
 * Returns all the information it can find according to the provided interval
 *
 * @param string $interval
 * @return array
 * @throws Exception
 */
function getAllPossibleData(string $interval): array
{
    echo(sprintf("Fetching %s data from Yahoo with interval %s", YahooGateway::BTC_USD, $interval) . PHP_EOL);
    $controller = new YahooController($interval);
    $data = $controller->getAllPossibleData(YahooGateway::BTC_USD);
    echo(sprintf("Received %d entries." . PHP_EOL, count($data)));
    return $data;
}

try {
    echo(sprintf("Connecting to db %s with user %s on %s:%d." . PHP_EOL, $_ENV["DB_NAME"], $_ENV["DB_USER"], $_ENV["DB_HOST"], $_ENV["DB_PORT"]));
    $db = new MysqliDb($_ENV["DB_HOST"], $_ENV["DB_USER"], $_ENV["DB_PASSWORD"], $_ENV["DB_NAME"], $_ENV["DB_PORT"]);
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


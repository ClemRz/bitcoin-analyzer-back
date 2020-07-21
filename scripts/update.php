<?php

/**
 * Script for backend actualization
 *
 * Tasks performed:
 *   1. Connect to database
 *   2. Abort if database is empty
 *   3. For one day, one hour and one minute intervals do:
 *      a. Get the date of the last sample
 *      b. Fetch data from Yahoo between last sample's date and now
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
    $startDate = getLastSamplesTimestamp($btcUsdGateway);
    $endDate = time();
    $data = getData($startDate, $endDate, $interval);
    if ($data[0]["timestamp"] === $startDate) {
        echo("Ignoring first element which is already present in the database." . PHP_EOL);
        array_shift($data);
    }
    if (count($data) === 0) {
        echo("Nothing to insert in the database." . PHP_EOL);
    } else {
        $chunkSize = 100;
        echo(sprintf("Inserting %d entries by chunks of %d rows." . PHP_EOL, count($data), $chunkSize));
        $btcUsdGateway->chunkInsert($data, $chunkSize);
        echo("Insertion successful." . PHP_EOL);
    }
}

/**
 * Returns
 *
 * @param BtcUsdGateway $gateway
 * @return int
 * @throws Exception
 */
function getLastSamplesTimestamp(BtcUsdGateway $gateway): int
{
    echo("Getting the last entry's date" . PHP_EOL);
    $data = $gateway->findLatest();
    if ($gateway->getCount() === 0) {
        throw new Exception("Table is empty, Aborting." . PHP_EOL);
    }
    return $data[0]["timestamp"];
}

/**
 * Returns the information according to the provided date range and interval
 *
 * @param int $startDate
 * @param int $endDate
 * @param string $interval
 * @return array
 * @throws Exception
 */
function getData(int $startDate, int $endDate, string $interval): array
{
    echo(sprintf(
            "Fetching %s data from Yahoo from %s to %s with interval %s",
            YahooGateway::BTC_USD,
            date("M j G:i:s Y T", $startDate),
            date("M j G:i:s Y T", $endDate),
            $interval
        ) . PHP_EOL);
    $controller = new YahooController($interval);
    $data = $controller->getData($startDate, $endDate, YahooGateway::BTC_USD, $interval);
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
        run($db, BtcUsdGateway::ONE_DAY);
        echo("{$interval} interval data stored." . PHP_EOL);
    }
    echo("Success." . PHP_EOL);
} catch (Exception $e) {
    exit("Ended with error: {$e->getMessage()}" . PHP_EOL);
}


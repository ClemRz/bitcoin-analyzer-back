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
    if (!empty($data) && $data[0]["timestamp"] <= $startDate) {
        echo("Ignoring first element which is already present in the database." . PHP_EOL);
        array_shift($data);
    }
    if (empty($data)) {
        echo("Nothing to insert in the database." . PHP_EOL);
    } else {
        $chunkSize = 100;
        echo(sprintf("Inserting %d entries by chunks of %d rows." . PHP_EOL, count($data), $chunkSize));
        $btcUsdGateway->batchInsert($data, $chunkSize);
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
    echo("Getting the last dataPoint's date" . PHP_EOL);
    $data = $gateway->findLatest();
    if ($gateway->getCount() === 0) {
        throw new Exception("Table is empty, Aborting." . PHP_EOL);
    }
    $timestamp = $data[0]["timestamp"];
    echo("Last dataPoint's date: " . date("M j G:i:s Y T", $timestamp) . PHP_EOL);
    return $timestamp;
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
function getData(int $startDate, int $endDate, string $interval): Array
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


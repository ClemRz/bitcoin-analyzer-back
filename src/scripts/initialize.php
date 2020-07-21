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
function getAllPossibleData(string $interval): Array
{
    echo(sprintf("Fetching %s data from Yahoo with interval %s", YahooGateway::BTC_USD, $interval) . PHP_EOL);
    $controller = new YahooController($interval);
    $data = $controller->getAllPossibleData(YahooGateway::BTC_USD);
    echo(sprintf("Received %d entries." . PHP_EOL, count($data)));
    return $data;
}


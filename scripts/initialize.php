<?php

/**
 * Script for backend initialization
 *
 * Tasks performed:
 *   1. Connect to the database
 *   2. Abort if database contains values already
 *   3. Fetch one-day interval historical data from Yahoo
 *   4. Store data in database in chunks
 * */

use DbGateways\BtcUsdGateway;
use HttpGateways\Yahoo;

require "Utils.php";

try {
    $db = Utils::connectToDb();
    $btcUsdGateway = new BtcUsdGateway($db);
    echo("Checking if the table is empty." . PHP_EOL);
    $cnt = $btcUsdGateway->countRows();
    if ($cnt > 0) {
        throw new Exception("Table is not empty, it contains {$cnt} entries. Aborting." . PHP_EOL);
    }
    echo("Table is empty, continuing." . PHP_EOL);
    $startDate = Yahoo::BTC_ORIGIN_OF_TIME;
    $endDate = time();
    $data = Utils::fetchData($startDate, $endDate);
    $chunkSize = 100;
    echo(sprintf("Inserting %d entries by chunks of %d rows." . PHP_EOL, count($data), $chunkSize));
    $btcUsdGateway->chunkInsert($data, $chunkSize);
    echo("Success." . PHP_EOL);
} catch (Exception $e) {
    echo("Ended with error: {$e->getMessage()}" . PHP_EOL);
}


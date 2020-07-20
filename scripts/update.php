<?php

/**
 * Script for backend initialization
 *
 * Tasks performed:
 *   1. Connect to database
 *   2. Abort if database is empty
 *   3. Get the date of the last sample
 *   4. Fetch one-day interval data from Yahoo between last sample's date and now
 *   5. Store data in database in chunks
 * */

use DbGateways\BtcUsdGateway;

require "Utils.php";

try {
    $db = Utils::connectToDb();
    $btcUsdGateway = new BtcUsdGateway($db);
    $startDate = Utils::getLastSamplesTimestamp($btcUsdGateway);
    $endDate = time();
    $data = Utils::fetchData($startDate, $endDate);
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
    echo("Success." . PHP_EOL);
} catch (Exception $e) {
    echo("Ended with error: {$e->getMessage()}" . PHP_EOL);
}


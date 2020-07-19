<?php

/**
 * Script for backend initialization
 *
 * Tasks performed:
 *   1. Abort if database exists and contains values already
 *   2. Fetch one-day interval historical data from Yahoo
 *   3. Connect to database
 *   4. Store data in database
 * */

use HttpTransaction\Yahoo;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config.php';
spl_autoload_register(function ($class) {
    $class = str_replace('\\', '/', $class);
    include __DIR__ . "/{$class}.php";
});

try {
    echo(sprintf("Connecting to db %s with user %s on %s." . PHP_EOL, DB_NAME, DB_USER, DB_HOST));
    $db = new MysqliDb (DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    echo("Connected" . PHP_EOL);
    $tableName = "BTCUSD";
    echo("Checking if the table {$tableName} is empty." . PHP_EOL);
    $cnt = $db->getValue($tableName, "count(*)");
    if ($cnt > 0) {
        throw new \Exception("Table {$tableName} is not empty, it contains {$cnt} entries. Aborting." . PHP_EOL);
    }
    echo("Table {$tableName} is empty." . PHP_EOL);

    $startDate = Yahoo::BTC_ORIGIN_OF_TIME;
    $endDate = time();
    echo(sprintf(
        "Fetching BTC-USD data from Yahoo, from %s to %s." . PHP_EOL,
        date("M j G:i:s Y T", $startDate),
        date("M j G:i:s Y T", $endDate)
    ));
    $connector = new Yahoo("BTC-USD", $startDate, $endDate, "1d");
    $data = $connector->getData();
    echo(sprintf("Received %d entries." . PHP_EOL, count($data["dataPoints"])));
    $transform = function ($dataPoint) {
        return Array(
            "timestamp" => $dataPoint["x"],
            "close" => $dataPoint["y"]
        );
    };
    $insertData = array_map($transform, $data["dataPoints"]);
    $chunkSize = 100;
    $dataCount = count($insertData);
    echo(sprintf("Inserting %d entries by chunks of %d rows." . PHP_EOL, $dataCount, $chunkSize));
    $db->startTransaction();
    foreach (array_chunk($insertData, $chunkSize) as $i => $chunk) {
        $chunkCount = count($chunk);
        echo(sprintf("Processing %d entries: from %d to %d of %d. ", $chunkCount, $i * $chunkSize + 1, $i * $chunkSize + $chunkCount, $dataCount));
        if (!$db->insertMulti($tableName, $chunk)) {
            $db->rollback(); // Error while saving, cancel insertions
            echo("Failure" . PHP_EOL);
            throw new \Exception("Error while inserting data in database.");
        }
        echo("Success" . PHP_EOL);
    }
    $db->commit();
    echo("Insertion successful." . PHP_EOL);
    echo("Success. Please delete initialize.php file" . PHP_EOL);
} catch (Exception $e) {
    echo("Ended with error: {$e->getMessage()}" . PHP_EOL);
}


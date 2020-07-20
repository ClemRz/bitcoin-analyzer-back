<?php
require(__DIR__ . "/../bootstrap.php");

use DbGateways\BtcUsdGateway;
use HttpGateways\Yahoo;

/**
 * Class Utils
 * Utility class.
 */
class Utils
{

    /**
     * @param $startDate
     * @param int $endDate
     * @return array
     * @throws Exception
     */
    public static function fetchData(int $startDate, int $endDate): array
    {
        echo(sprintf(
            "Fetching BTC-USD data from Yahoo, from %s to %s." . PHP_EOL,
            date("M j G:i:s Y T", $startDate),
            date("M j G:i:s Y T", $endDate)
        ));
        $connector = new Yahoo("BTC-USD", $startDate, $endDate, "1d");
        $data = $connector->getData();
        echo(sprintf("Received %d entries." . PHP_EOL, count($data)));
        return $data;
    }

    /**
     * @return MysqliDb
     */
    public static function connectToDb(): MysqliDb
    {
        echo(sprintf("Connecting to db %s with user %s on %s:%d." . PHP_EOL, $_ENV["DB_NAME"], $_ENV["DB_USER"], $_ENV["DB_HOST"], $_ENV["DB_PORT"]));
        $db = new MysqliDb($_ENV["DB_HOST"], $_ENV["DB_USER"], $_ENV["DB_PASSWORD"], $_ENV["DB_NAME"], $_ENV["DB_PORT"]);
        echo("Connected" . PHP_EOL);
        return $db;
    }

    /**
     * @param BtcUsdGateway $gateway
     * @return int
     * @throws Exception
     */
    public static function getLastSamplesTimestamp(BtcUsdGateway $gateway): int
    {
        echo("Getting the last sample's date" . PHP_EOL);
        $data = $gateway->findLatest();
        if ($gateway->getCount() === 0) {
            throw new Exception("Table is empty, Aborting." . PHP_EOL);
        }
        return $data[0]["timestamp"];
    }
}
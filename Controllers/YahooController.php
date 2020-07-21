<?php

namespace Controllers;

use Exception;
use HttpGateways\YahooGateway;
use Validators\YahooValidator;

/**
 * Class YahooController
 * @package Controllers
 */
class YahooController
{
    private const MINUTE = 60; // seconds
    private const HOUR = 60 * self::MINUTE;
    private const DAY = 24 * self::HOUR;

    /**
     * Map of possible intervals and the amount of seconds their are available from today.
     * @var array
     */
    private const INTERVAL_TIME_MAP = Array(
        "1m" => 7 * self::DAY,
        "2m" => 60 * self::DAY,
        "5m" => 60 * self::DAY,
        "15m" => 60 * self::DAY,
        "30m" => 60 * self::DAY,
        "60m" => 729 * self::DAY,
        "90m" => 60 * self::DAY,
        "1h" => 729 * self::DAY,
        "1d" => -1,
        "5d" => -1,
        "1wk" => -1,
        "1mo" => -1,
        "3mo" => -1
    );

    private $interval;

    /**
     * YahooController constructor.
     * @param string $interval
     * @throws Exception
     */
    public function __construct(string $interval)
    {
        if (!array_key_exists($interval, self::INTERVAL_TIME_MAP)) {
            throw new Exception("Provided interval is not available.");
        }
        $this->interval = $interval;
    }

    /**
     * Fetches the maximum amount of data according to the provided interval.
     *
     * @param string $symbol
     * @return array
     * @throws Exception
     */
    public function getAllPossibleData(string $symbol): Array
    {
        $endDate = time();
        $time = self::INTERVAL_TIME_MAP[$this->interval];
        if ($time < 0) { // Special case where the available data is for all times
            $startDate = YahooGateway::BTC_ORIGIN_OF_TIME;
        } else {
            $startDate = $endDate - $time;
        }
        return $this->getData($startDate, $endDate, $symbol, $this->interval);
    }


    /**
     * Returns Yahoo's data transformed into the API format
     *
     * @param int $startDate
     * @param int $endDate
     * @param string $symbol
     * @param string $interval
     * @return array
     * @throws Exception
     */
    public function getData(int $startDate, int $endDate, string $symbol, string $interval)
    {
        $gateway = new YahooGateway($symbol, $startDate, $endDate, $interval);
        $data = $gateway->getData();
        $validator = new YahooValidator($data);
        $validator->validate();
        return $this->getTransformedData($data);
    }

    /**
     * Adapts the data to the API format
     *
     * @param $data
     * @return array
     */
    private function getTransformedData($data)
    {
        $root = $data["chart"]["result"][0];
        $timestamps = $root["timestamp"];
        if (is_null($timestamps)) { // No record returned
            return Array();
        }
        $closeQuotes = $root["indicators"]["quote"][0]["close"];
        $dataPoints = Array();
        foreach ($timestamps as $i => $timestamp) {
            array_push($dataPoints, Array(
                "timestamp" => $timestamp,
                "close" => $closeQuotes[$i]
            ));
        }
        return $dataPoints;
    }
}
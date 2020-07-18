<?php

namespace HttpTransaction;

/**
 * Class Yahoo
 * @package HttpTransaction
 */
class Yahoo
{
    private const ENDPOINT = 'https://query2.finance.yahoo.com/v8/finance/chart/';
    private const START_KEY = 'period1';
    private const END_KEY = 'period2';
    private const INTERVAL_KEY = 'interval';

    /**
     * Currency pair symbol. E.g. BTC-USD
     * @var string
     */
    private $_symbol;

    /**
     * Start date for the values to retrieve. Unix timestamp.
     * Historical data initial point is Sep. 16 2014 (1410908400)
     * @val int
     */
    private $_startDate;

    /**
     * End date for the values to retrieve. Unix timestamp.
     * @var int
     */
    private $_endDate;


    /**
     * Interval between the values to retrieve. Supported values are 1m, 2m, 5m, 15m, 30m, 60m, 90m, 1h, 1d, 5d, 1wk, 1mo, 3mo
     * @var string
     */
    private $interval;

    /**
     * Yahoo constructor.
     * @param string $symbol the currency pair symbol. E.g. BTC-USD
     * @param int $startDate the start date for the values to retrieve. Unix timestamp. Historical data initial point is Sep. 16 2014 (1410908400)
     * @param int $endDate the end date for the values to retrieve. Unix timestamp.
     * @param string $interval the interval between the values to retrieve. Supported values are 1m, 2m, 5m, 15m, 30m, 60m, 90m, 1h, 1d, 5d, 1wk, 1mo, 3mo
     */
    function __construct(string $symbol, int $startDate, int $endDate, string $interval)
    {
        $this->_symbol = $symbol;
        $this->_startDate = $startDate;
        $this->_endDate = $endDate;
        $this->interval = $interval;
    }

    /**
     * Returns Yahoo's data transformed into the API format
     * @return array
     * @throws \Exception
     */
    public function getData()
    {
        $data = json_decode($this->getResponse(), true);
        $this->checkForErrors($data);
        return $this->getTransformedData($data);
    }

    /**
     * Returns the transformed data to match the API format
     * @param $data
     * @return array
     */
    private function getTransformedData($data)
    {
        $root = $data["chart"]["result"][0];
        $timestamps = $root["timestamp"];
        $closeQuotes = $root["indicators"]["quote"][0]["close"];
        $dataPoints = array();
        foreach ($timestamps as $i => $timestamp) {
            array_push($dataPoints, array(
                "x" => $timestamp,
                "y" => $closeQuotes[$i]
            ));
        }
        return array("dataPoints" => $dataPoints);
    }

    /**
     * Builds and returns the URL with the parameters
     * @return string
     */
    private function getUrl(): string
    {
        $parameters = array(
            self::START_KEY => $this->_startDate,
            self::END_KEY => $this->_endDate,
            self::INTERVAL_KEY => $this->interval
        );
        return self::ENDPOINT . $this->_symbol . '?' . http_build_query($parameters);
    }

    /**
     * Triggers an exception if there is anything wrong with the data received
     * @param $data
     * @throws \Exception
     */
    private function checkForErrors($data): void
    {
        if (is_null($data)) {
            throw new \Exception("Unexpected error: no response or bad response format.");
        }
        if (!is_array($data)) {
            $type = gettype($data);
            throw new \Exception("Unexpected format: expected array, found {$type} instead.");
        }
        if (!array_key_exists("chart", $data)) {
            throw new \Exception("Unexpected format: expected key 'chart' not found.");
        }
        $chart = $data["chart"];
        if (array_key_exists("error", $chart)) {
            $error = $chart["error"];
            if (!is_null($error)) {
                throw new \Exception("Third party error: {$error["code"]}: {$error["description"]}");
            }
        }
        if (!array_key_exists("result", $chart)) {
            throw new \Exception("Unexpected format: expected key 'result' not found.");
        }
        $result = $chart["result"];
        if (count($result) !== 1) {
            throw new \Exception("Unexpected format: no entries in 'result' found.");
        }
        $root = $result[0];
        if (!array_key_exists("timestamp", $root)) {
            throw new \Exception("Unexpected format: expected key 'timestamp' not found.");
        }
        $timestamp = $root["timestamp"];
        if (count($timestamp) == 0) {
            throw new \Exception("No values found for the matching search parameters.");
        }
        if (!array_key_exists("indicators", $root)) {
            throw new \Exception("Unexpected format: expected key 'indicators' not found.");
        }
        $indicators = $root["indicators"];
        if (!array_key_exists("quote", $indicators)) {
            throw new \Exception("Unexpected format: expected key 'quote' not found.");
        }
        $quote = $indicators["quote"];
        if (count($quote) !== 1) {
            throw new \Exception("Unexpected format: no entries in 'quote' found.");
        }
        if (!array_key_exists("close", $quote[0])) {
            throw new \Exception("Unexpected format: expected key 'close' not found.");
        }
        $close = $quote[0]["close"];
        if (count($close) !== count($timestamp)) {
            throw new \Exception("Unexpected content: there should be as many items in 'close' as in 'timestamp'.");
        }
    }

    /**
     * Fetches the data
     * @return string
     */
    private function getResponse(): string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $this->getUrl());
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}



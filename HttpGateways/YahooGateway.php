<?php

namespace HttpGateways;

use Exception;
use Exceptions\FormatHttpTransactionException;
use Exceptions\ThirdPartyHttpTransactionException;

/**
 * Class Yahoo
 * @package HttpGateways
 */
class YahooGateway
{
    public const BTC_ORIGIN_OF_TIME = 1410825600; // First data point is Sep. 16 2014 (1410825600)
    public const BTC_USD = "BTC-USD";

    private const ENDPOINT = "https://query2.finance.yahoo.com/v8/finance/chart/";
    private const START_KEY = "period1";
    private const END_KEY = "period2";
    private const INTERVAL_KEY = "interval";

    /**
     * Currency pair symbol. E.g. BTC-USD
     * @var string
     */
    private $_symbol;

    /**
     * Start date for the values to retrieve. Unix timestamp.
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
     * @param int $startDate the start date for the values to retrieve. Unix timestamp.
     * @param int $endDate the end date for the values to retrieve. Unix timestamp.
     * @param string $interval the interval between the values to retrieve.
     */
    public function __construct(string $symbol, int $startDate, int $endDate, string $interval)
    {
        $this->_symbol = $symbol;
        $this->_startDate = $startDate;
        $this->_endDate = $endDate;
        $this->interval = $interval;
    }

    /**
     * Returns data from Yahoo
     *
     * @return array
     * @throws Exception
     */
    public function getData()
    {
        return json_decode($this->getResponse(), true);
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
        return self::ENDPOINT . $this->_symbol . "?" . http_build_query($parameters);
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



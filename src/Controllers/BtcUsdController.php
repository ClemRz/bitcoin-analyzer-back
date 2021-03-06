<?php

namespace Controllers;

use DbGateways\BtcUsdGateway;
use Exception;
use Exceptions\ApiException;
use Exceptions\InconsistencyValidationException;
use Exceptions\MissingParameterApiException;
use Exceptions\ValidationException;
use Exceptions\WrongValueValidationException;
use HttpGateways\YahooGateway;
use MysqliDb;
use Validators\BctUsdValidator;

/**
 * Class BtcUsdController
 * @package Controllers
 */
class BtcUsdController
{
    public const SYMBOL = "BTCUSD";

    private const MINUTE = 60; // seconds
    private const HOUR = 60 * self::MINUTE;
    private const DAY = 24 * self::HOUR;

    /**
     * Instance of DB connection
     * @var MysqliDb
     */
    private $_db;

    /**
     * HTTP method
     * @var string
     */
    private $_method;

    /**
     * HTTP URI
     * @var string
     */
    private $_uri;

    /**
     * Start unix timestamp
     * @var mixed
     */
    private $_startDate;

    /**
     * End unix timestamp
     * @var mixed
     */
    private $_endDate;

    /**
     * DB gateway
     * @var BtcUsdGateway
     */
    private $_btcUsdGateway;

    /**
     * BtcUsdController constructor.
     *
     * @param MysqliDb $db
     * @param string $method
     * @param string $uri
     */
    public function __construct(MysqliDb $db, string $method, string $uri)
    {
        $this->_db = $db;
        $this->_method = $method;
        $this->_uri = $uri;

        $this->_btcUsdGateway = new BtcUsdGateway($db);
    }

    /**
     * Processes the request
     *
     * @throws Exception
     */
    public function processRequest(): void
    {
        try {
            $tmpParts = preg_split("/[\/]/", $this->_uri); // e.g. "/api/BTCUSD/1594789200/1594875600"
            $tmpParts = array_slice($tmpParts, 3); // e.g. ["1594789200", "1594875600"]

            if (count($tmpParts) < 1 || empty($tmpParts[0])) {
                throw new MissingParameterApiException("startDate");
            }
            $this->_startDate = $tmpParts[0];

            if (count($tmpParts) < 2 || empty($tmpParts[1])) {
                throw new MissingParameterApiException("endDate");
            }
            $this->_endDate = $tmpParts[1];

            switch ($this->_method) {
                case "GET":
                    $data = $this->getEntriesDynamicInterval($this->_startDate, $this->_endDate);
                    $this->render($data);
                    break;
                default:
                    header("HTTP/1.1 404 Not Found");
                    break;
            }
        } catch (ApiException | ValidationException $e) {
            $this->render($this->getErrorResponse($e));
        }
    }

    /**
     * Validates the dates and dynamically pull the values with the most adequate interval
     *
     * @param mixed $startDate
     * @param mixed $endDate
     * @return array
     * @throws InconsistencyValidationException
     * @throws WrongValueValidationException
     * @throws Exception
     */
    private function getEntriesDynamicInterval($startDate, $endDate): Array
    {
        $validator = new BctUsdValidator($startDate, $endDate, YahooGateway::BTC_ORIGIN_OF_TIME);
        $validator->validate();

        $startDate = intval($startDate);
        $endDate = intval($endDate);

        $range = $endDate - $startDate;
        $allData = Array();

        foreach ($this->getRangeIntervalMap() as $interval => $inRange) {
            if ($inRange($range)) {
                $data = $this->getEntries($startDate, $endDate, $interval);
                $allData = array_merge($data, $allData);
            }
        }

        usort($allData, Array('self', 'compare'));

        return $allData;
    }

    /**
     * Look up for the entries in database matching the provided parameters
     *
     * @param int $startDate
     * @param int $endDate
     * @param string $interval
     * @return array
     * @throws Exception
     */
    private function getEntries(int $startDate, int $endDate, string $interval): Array
    {
        $this->_btcUsdGateway->setSuffix($interval);
        return $this->_btcUsdGateway->find($startDate, $endDate);
    }

    /**
     * Comparison function for data points sorting
     *
     * @param $dataPointA
     * @param $dataPointB
     * @return int
     */
    private static function compare($dataPointA, $dataPointB): int
    {
        $timestampA = $dataPointA["timestamp"];
        $timestampB = $dataPointB["timestamp"];
        if ($timestampA == $timestampB) {
            return 0;
        }
        return ($timestampA < $timestampB) ? -1 : 1;
    }

    /**
     * Renders the data as a json
     *
     * @param array $data
     */
    private function render(Array $data): void
    {
        header("HTTP/1.1 200 OK");
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($data);
    }

    /**
     * Create an error response object
     *
     * @param Exception $e
     * @return array
     */
    private function getErrorResponse(Exception $e): Array
    {
        return Array("error" => Array(
            "message" => $e->getMessage(),
            "code" => $e->getCode()
        ));
    }

    /**
     * Mapping between the granularity of the data to return and the range requested
     *
     * @return array
     */
    private function getRangeIntervalMap(): Array
    {
        return Array(
            BtcUsdGateway::ONE_MINUTE => function ($range) {
                return $range <= 2 * self::DAY;
            },
            BtcUsdGateway::ONE_HOUR => function ($range) {
                return $range <= 7 * self::DAY;
            },
            BtcUsdGateway::ONE_DAY => function ($range) {
                return $range > 7 * self::DAY;
            }
        );
    }
}
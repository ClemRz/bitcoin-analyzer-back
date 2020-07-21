<?php

namespace Controllers;

use DbGateways\BtcUsdGateway;
use Exception;
use Exceptions\ApiException;
use Exceptions\InconsistencyValidationException;
use Exceptions\MissingParameterApiException;
use Exceptions\UnsupportedValueApiException;
use Exceptions\ValidationException;
use Exceptions\WrongValueValidationException;
use HttpGateways\YahooGateway;
use MysqliDb;
use Throwable;
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

    private $_db;
    private $_method;
    private $_uri;
    private $_startDate;
    private $_endDate;
    private $_format;

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
     */
    public function processRequest(): void
    {
        try {
            $tmpParts = explode("/", $this->_uri);

            if (count($tmpParts) < 3 || empty($tmpParts[2])) {
                throw new MissingParameterApiException("startDate");
            }
            $this->_startDate = $tmpParts[2];

            if (count($tmpParts) < 4 || empty($tmpParts[3])) {
                throw new MissingParameterApiException("endDate");
            }
            $tmpParts = explode(".", $tmpParts[3]);
            $this->_endDate = $tmpParts[0];

            if (count($tmpParts) < 2 || empty($tmpParts[1])) {
                throw new MissingParameterApiException("format");
            }
            $this->_format = $tmpParts[1];
            switch ($this->_method) {
                case "GET":
                    $data = $this->getEntriesDynamicInterval($this->_startDate, $this->_endDate);
                    $this->render($data, $this->_format);
                    break;
                default:
                    header("HTTP/1.1 404 Not Found");
                    break;
            }
        } catch (ApiException | ValidationException $e) {
            $this->render($this->getErrorResponse($e), $this->_format);
        } catch (Throwable $e) {
            // Hide the details of those from the WWW
            $this->render(Array("error" => Array()), $this->_format);
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
     * Renders the data in the specified format when available
     *
     * @param array $data
     * @param string|null $format
     */
    private function render(Array $data, $format): void
    {
        switch ($format) {
            case null:
            case "json":
                header("HTTP/1.1 200 OK");
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode($data);
                break;
            default:
                $this->render($this->getErrorResponse(new UnsupportedValueApiException("format")), "json");
        }
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
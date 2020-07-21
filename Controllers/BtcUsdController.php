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

/**
 * Class BtcUsdController
 * @package Controllers
 */
class BtcUsdController
{
    private const MINUTE = 60; // seconds
    private const HOUR = 60 * self::MINUTE;
    private const DAY = 24 * self::HOUR;

    private $db;
    private $method;
    private $uri;
    private $startDate;
    private $endDate;
    private $format;

    private $btcUsdGateway;

    /**
     * BtcUsdController constructor.
     *
     * @param MysqliDb $db
     * @param string $method
     * @param string $uri
     */
    public function __construct(MysqliDb $db, string $method, string $uri)
    {
        $this->db = $db;
        $this->method = $method;
        $this->uri = $uri;

        $this->btcUsdGateway = new BtcUsdGateway($db);
    }

    /**
     * Processes the request
     */
    public function processRequest(): void
    {
        try {
            $tmpParts = explode("/", $this->uri);

            if (count($tmpParts) < 3 || empty($tmpParts[2])) {
                throw new MissingParameterApiException("startDate");
            }
            $this->startDate = $tmpParts[2];

            if (count($tmpParts) < 4 || empty($tmpParts[3])) {
                throw new MissingParameterApiException("endDate");
            }
            $tmpParts = explode(".", $tmpParts[3]);
            $this->endDate = $tmpParts[0];

            if (count($tmpParts) < 2 || empty($tmpParts[1])) {
                throw new MissingParameterApiException("format");
            }
            $this->format = $tmpParts[1];
            switch ($this->method) {
                case "GET":
                    $data = $this->getEntriesDynamicInterval($this->startDate, $this->endDate);
                    $this->render($data, $this->format);
                    break;
                default:
                    header("HTTP/1.1 404 Not Found");
                    break;
            }
        } catch (ApiException | ValidationException $e) {
            $this->render($this->getErrorResponse($e), $this->format);
        } catch (Throwable $e) {
            // Hide the details of those from the WWW
            $this->render(Array("error" => Array()), $this->format);
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
    private function getEntriesDynamicInterval($startDate, $endDate): array
    {
        $validator = new ParametersValidator($startDate, $endDate, YahooGateway::BTC_ORIGIN_OF_TIME);
        $validator->validate();

        $startDate = intval($startDate);
        $endDate = intval($endDate);

        $range = $endDate - $startDate;
        $allData = Array();

        if ($range <= 2 * self::DAY) {
            $data = $this->getEntries($startDate, $endDate, BtcUsdGateway::ONE_MINUTE);
            $allData = array_merge($data, $allData);
        }

        if ($range <= 7 * self::DAY) {
            $data = $this->getEntries($startDate, $endDate, BtcUsdGateway::ONE_HOUR);
            $allData = array_merge($data, $allData);
        }

        if ($range > 7 * self::DAY) {
            $data = $this->getEntries($startDate, $endDate, BtcUsdGateway::ONE_DAY);
            $allData = array_merge($data, $allData);
        }

        usort($allData, array('self', 'compare'));

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
    private function getEntries(int $startDate, int $endDate, string $interval): array
    {
        $this->btcUsdGateway->setSuffix($interval);
        return $this->btcUsdGateway->find($startDate, $endDate);
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
    private function getErrorResponse(Exception $e): array
    {
        return Array("error" => Array(
            "message" => $e->getMessage(),
            "code" => $e->getCode()
        ));
    }
}
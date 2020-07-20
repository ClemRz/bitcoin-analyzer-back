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
use HttpGateways\Yahoo;
use MysqliDb;
use Throwable;

/**
 * Class BtcUsdController
 * @package Controllers
 */
class BtcUsdController
{
    private $db;
    private $method;
    private $uri;
    private $startDate;
    private $endDate;
    private $format;

    private $BtcUsdGateway;

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

        $this->BtcUsdGateway = new BtcUsdGateway($db);
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
                    $data = $this->getEntries($this->startDate, $this->endDate);
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
     * Validates the dates and look up for the entries in database
     *
     * @param mixed $startDate
     * @param mixed $endDate
     * @return array
     * @throws InconsistencyValidationException
     * @throws WrongValueValidationException
     * @throws Exception
     */
    private function getEntries($startDate, $endDate): array
    {
        $validator = new ParametersValidator($startDate, $endDate, Yahoo::BTC_ORIGIN_OF_TIME);
        $validator->validate();

        $startDate = intval($startDate);
        $endDate = intval($endDate);

        $btcUsdGateway = new BtcUsdGateway($this->db);
        return $btcUsdGateway->find($startDate, $endDate);
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
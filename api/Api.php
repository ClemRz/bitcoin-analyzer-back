<?php


namespace Api;

use HttpTransaction\Yahoo;
use Validator\ParametersValidator;

class Api
{
    /**
     * The instance class used to fetch historical data
     * @val HttpTransaction_Connector
     */
    private $connector;

    /**
     * The data fetched by the connector
     * @val mixed
     */
    private $data;

    function __construct($json)
    {
        try {
            if (!$json) {
                throw new \Exception("Missing parameters.");
            }
            $parameters = json_decode($json, true);
            $startDate = $parameters["startDate"];
            if (empty($startDate)) {
                throw new \Exception("Missing parameter: startDate.");
            }
            $endDate = $parameters["endDate"];
            if (empty($endDate)) {
                throw new \Exception("Missing parameter: endDate.");
            }
            $symbol = $parameters["symbol"];
            if (empty($symbol)) {
                throw new \Exception("Missing parameter: symbol.");
            }

            $validator = new ParametersValidator($startDate, $endDate, $symbol);
            $validator->validate();

            $startDate = intval($startDate);
            $endDate = intval($endDate);

            //$this->render(array("dataPoints" => array(array("x" => 1595084438, "y" => 227.9326171875), array("x" => 1595085438, "y" => 9227.9326171875))));
            $connector = new Yahoo($symbol, $startDate, $endDate, "1d");
            $this->validate("foo");
            $data = $connector->getData(); // TODO clement fetch from DB, cache if necessary
            $this->render($data);
        } catch (\Exception $e) {
            $this->render($this->getErrorResponse($e));
        }
    }

    private function validate($input): bool
    {
        // TODO clement
        return true;
    }

    private function fetchData(): void
    {
        return;
    }

    private function render($data): void
    {
        header("Content-type: application/json; charset=utf-8");
        echo json_encode($data);
    }

    private function getErrorResponse(\Exception $e): array
    {
        return array("error" => $e->getMessage());
    }
}
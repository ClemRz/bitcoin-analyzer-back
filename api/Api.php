<?php


namespace Api;

use Exceptions\ApiException;
use Exceptions\MissingParameterApiException;
use Exceptions\ValidationException;
use HttpTransaction\Yahoo;
use Validator\ParametersValidator;

/**
 * Class Api
 * @package Api
 */
class Api
{
    /**
     * Fetches the information from thrid-party service
     * @param $json
     */
    public function fetch($json)
    {
        try {
            if (!$json) {
                throw new MissingParameterApiException("none provided");
            }
            $parameters = json_decode($json, true);
            $startDate = $parameters["startDate"];
            if (empty($startDate)) {
                throw new MissingParameterApiException("startDate");
            }
            $endDate = $parameters["endDate"];
            if (empty($endDate)) {
                throw new MissingParameterApiException("endDate");
            }
            $symbol = $parameters["symbol"];
            if (empty($symbol)) {
                throw new MissingParameterApiException("symbol");
            }

            $validator = new ParametersValidator($startDate, $endDate, $symbol, Yahoo::BTC_ORIGIN_OF_TIME);
            $validator->validate();

            $startDate = intval($startDate);
            $endDate = intval($endDate);

            $tableName = "BTCUSD";
            $db = new \MysqliDb(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
            $db->where("timestamp", Array($startDate, $endDate), "BETWEEN");
            $db->orderBy("timestamp", "asc");
            $data = $db->get($tableName);
            $this->render($data);
        } catch (ApiException | ValidationException $e) {
            $this->render($this->getErrorResponse($e));
        } catch (\Throwable $e) {
            // Hide the details of those from the WWW
            $this->render(Array("error" => Array()));
        }
    }

    private function render($data): void
    {
        header("Content-type: application/json; charset=utf-8");
        echo json_encode($data);
    }

    private function getErrorResponse(\Exception $e): array
    {
        return Array("error" => Array(
            "message" => $e->getMessage(),
            "code" => $e->getCode()
        ));
    }
}
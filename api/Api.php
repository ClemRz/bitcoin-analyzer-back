<?php


namespace Api;

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
        } catch (\Exception $e) {
            $this->render($this->getErrorResponse($e)); //TODO clement only one type of errors, obfuscate system-sensitive ones
        }
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
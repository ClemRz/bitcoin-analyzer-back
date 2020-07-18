<?php


namespace Api;

use HttpTransaction\Yahoo;

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

    function __construct()
    {
        $this->render(array("dataPoints" => array(array("x"=>1595084438,"y"=>227.9326171875), array("x"=>1595085438,"y"=>9227.9326171875))));
        //$this->render(array("error"=>"test"));
        /*$connector = new Yahoo("BTC-USD", 1595077132, 1595078132, "1d"); //1410908400
        $this->validate("foo");
        try {
            $data = $connector->getData(); // TODO clement fetch from DB, cache if necessary
            $this->render($data);
        } catch (\Exception $e) {
            $this->render($this->getErrorResponse($e));
        }*/
    }

    private function validate($input): bool
    {
        // TODO clement
        return true;
    }

    private function fetchData(): void
    {
        return ;
    }

    private function render($data): void
    {
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($data);
    }

    private function getErrorResponse(\Exception $e) : array
    {
        return array("error" => $e->getMessage());
    }
}
<?php


namespace Validators;


use Exceptions\FormatHttpTransactionException;
use Exceptions\ThirdPartyHttpTransactionException;

class YahooValidator
{
    private $_data;

    /**
     * YahooValidator constructor.
     * @param mixed $data
     */
    public function __construct($data)
    {
        $this->_data = $data;
    }

    /**
     * Triggers an exception if there is anything wrong with the data
     *
     * @throws ThirdPartyHttpTransactionException
     * @throws FormatHttpTransactionException
     */
    public function validate(): void
    {
        if (is_null($this->_data)) {
            throw new ThirdPartyHttpTransactionException("no response or bad response format (expecting json)");
        }
        if (!is_array($this->_data)) {
            throw new FormatHttpTransactionException(sprintf("expected array, found %s instead", gettype($this->_data)));
        }
        if (!array_key_exists("chart", $this->_data)) {
            throw new FormatHttpTransactionException("expected key 'chart' not found");
        }
        $chart = $this->_data["chart"];
        if (array_key_exists("error", $chart)) {
            $error = $chart["error"];
            if (!is_null($error)) {
                throw new ThirdPartyHttpTransactionException("{$error["code"]}: {$error["description"]}");
            }
        }
        if (!array_key_exists("result", $chart)) {
            throw new FormatHttpTransactionException("expected key 'result' not found");
        }
        $result = $chart["result"];
        if (count($result) !== 1) {
            throw new FormatHttpTransactionException("no entries in 'result' found");
        }
        $root = $result[0];
        if (!array_key_exists("timestamp", $root)) {
            return; //There is no record returned
        }
        $timestamp = $root["timestamp"];
        if (!array_key_exists("indicators", $root)) {
            throw new FormatHttpTransactionException("expected key 'indicators' not found");
        }
        $indicators = $root["indicators"];
        if (!array_key_exists("quote", $indicators)) {
            throw new FormatHttpTransactionException("expected key 'quote' not found");
        }
        $quote = $indicators["quote"];
        if (count($quote) !== 1) {
            throw new FormatHttpTransactionException("no entries in 'quote' found");
        }
        if (!array_key_exists("close", $quote[0])) {
            throw new FormatHttpTransactionException("expected key 'close' not found");
        }
        $close = $quote[0]["close"];
        if (count($close) !== count($timestamp)) {
            throw new FormatHttpTransactionException("there should be as many items in 'close' as in 'timestamp'");
        }
    }

}
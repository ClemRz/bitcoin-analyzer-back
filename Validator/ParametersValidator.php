<?php

namespace Validator;

class ParametersValidator
{
    private const ORIGIN_OF_TIME = 1410825600; // Unix timestamp in milliseconds, Sep. 16 2014;
    private const SYMBOLS = array("BTC-USD");

    /**
     * Unix timestamp
     * @var integer
     */
    private $_startDate;

    /**
     * Unix timestamp
     * @var integer
     */
    private $_endDate;

    /**
     * Currency pair symbol
     * @var string
     */
    private $_symbol;

    public function __construct($startDate, $endDate, $symbol)
    {
        $this->_startDate = $startDate;
        $this->_endDate = $endDate;
        $this->_symbol = $symbol;
    }

    public function validate()
    {
        if (!is_numeric($this->_startDate)) {
            throw new \Exception("Wrong value for startDate parameter: {$this->_startDate}.");
        }
        if (!is_numeric($this->_endDate)) {
            throw new \Exception("Wrong value for endDate parameter: {$this->_endDate}.");
        }
        if (!is_string($this->_symbol)) {
            throw new \Exception("Wrong value for symbol parameter: {$this->_symbol}.");
        }
        $startDate = intval($this->_startDate);
        $endDate = intval($this->_endDate);
        if ($startDate >= $endDate) {
            throw new \Exception(sprintf("Inconsistencies between startDate (%s) and endDate (%s) parameters.", $this->formatDate($startDate), $this->formatDate($endDate)));
        }
        $endOfTodayUtc = strtotime("tomorrow", gmmktime()) - 1;
        if ($endDate > $endOfTodayUtc) {
            throw new \Exception(sprintf("Cannot use date in the future, make sure the dates are expressed in UTC. EndDate: %s now: %s.", $this->formatDate($endDate), $this->formatDate(time())));
        }
        if ($startDate < self::ORIGIN_OF_TIME) {
            throw new \Exception(sprintf("Cannot use date (%s) before initial dataPoint: %s.",$this->formatDate($startDate), $this->formatDate(self::ORIGIN_OF_TIME)));
        }
        if (!in_array($this->_symbol, self::SYMBOLS)) {
            throw new \Exception("Invalid symbol ({$this->_symbol}), available values are: " . join(", ", self::SYMBOLS));
        }
    }

    /**
     * @param int $unixTimestamp
     * @return false|string
     */
    private function formatDate(int $unixTimestamp)
    {
        return date("M j G:i:s Y T", $unixTimestamp);
    }
}
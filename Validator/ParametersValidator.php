<?php

namespace Validator;

class ParametersValidator
{
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

    /**
     * Minimum value for the startDate field
     * @var integer
     */
    private $_originOfTime;

    public function __construct($startDate, $endDate, $symbol, $originOfTime)
    {
        $this->_startDate = $startDate;
        $this->_endDate = $endDate;
        $this->_symbol = $symbol;
        $this->_originOfTime = $originOfTime;
    }

    /**
     * Check all the fields for consistency
     * @throws \Exception
     */
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
        if ($startDate < $this->_originOfTime) {
            throw new \Exception(sprintf("Cannot use date (%s) before initial dataPoint: %s.",$this->formatDate($startDate), $this->formatDate($this->_originOfTime)));
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
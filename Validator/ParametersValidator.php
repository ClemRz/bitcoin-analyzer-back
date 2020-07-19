<?php

namespace Validator;

use Exceptions\InconsistencyValidationException;
use Exceptions\WrongValueValidationException;

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
     * @throws WrongValueValidationException
     * @throws InconsistencyValidationException
     */
    public function validate()
    {
        if (!is_numeric($this->_startDate)) {
            throw new WrongValueValidationException("startDate");
        }
        if (!is_numeric($this->_endDate)) {
            throw new WrongValueValidationException("endDate");
        }
        if (!is_string($this->_symbol)) {
            throw new WrongValueValidationException("symbol");
        }
        $startDate = intval($this->_startDate);
        $endDate = intval($this->_endDate);
        if ($startDate >= $endDate) {
            throw new InconsistencyValidationException("startDate is older than endDate");
        }
        $endOfTodayUtc = strtotime("tomorrow", gmmktime()) - 1;
        if ($endDate > $endOfTodayUtc) {
            throw new InconsistencyValidationException("endDate is in the future");
        }
        if ($startDate < $this->_originOfTime) {
            throw new InconsistencyValidationException("startDate is before initial dataPoint ({$this->formatDate($this->_originOfTime)})");
        }
        if (!in_array($this->_symbol, self::SYMBOLS)) {
            throw new WrongValueValidationException("symbol. Available values are: " . join(", ", self::SYMBOLS));
        }
    }

    /**
     * @param int $unixTimestamp
     * @return false|string
     */
    private function formatDate(int $unixTimestamp)
    {
        return date("M j Y", $unixTimestamp);
    }
}
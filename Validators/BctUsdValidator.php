<?php

namespace Validators;

use Exceptions\InconsistencyValidationException;
use Exceptions\WrongValueValidationException;

/**
 * Class BctUsdValidator
 * @package Validators
 */
class BctUsdValidator
{
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
     * Minimum value for the startDate field
     * @var integer
     */
    private $_originOfTime;

    public function __construct($startDate, $endDate, $originOfTime)
    {
        $this->_startDate = $startDate;
        $this->_endDate = $endDate;
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
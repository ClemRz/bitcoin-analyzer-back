<?php

namespace DbGateways;

use Exception;
use MysqliDb;

/**
 * Class BtcUsdGateway
 * @package DbGateways
 */
class BtcUsdGateway
{
    public const ONE_DAY = "1d";
    public const ONE_HOUR = "1h";
    public const ONE_MINUTE = "1m";

    private const TABLE_PREFIX = "BTCUSD";
    private const TABLE_SUFFIX_WHITELIST = Array(self::ONE_DAY, self::ONE_HOUR, self::ONE_MINUTE);

    /**
     * Instance of DB connection
     * @var MysqliDb
     */
    private $_db;

    /**
     * Suffix of the table name
     * @var
     */
    private $_suffix;

    /**
     * BtcUsdGateway constructor.
     *
     * @param MysqliDb $db
     */
    public function __construct(MysqliDb $db)
    {
        $this->_db = $db;
    }

    /**
     * Set which table to control
     *
     * @param string $suffix
     * @throws Exception
     */
    public function setSuffix(string $suffix): void
    {
        if (!in_array($suffix, self::TABLE_SUFFIX_WHITELIST)) {
            throw new Exception("Invalid value for suffix");
        }
        $this->_suffix = $suffix;
    }

    /**
     * Returns the latest entry in the table
     *
     * @return array|MysqliDb
     * @throws Exception
     */
    public function findLatest()
    {
        return $this->_db
            ->orderBy("timestamp", "desc")
            ->get($this->getTableName(), 1);
    }

    /**
     * Returns the number of rows fetched by the last selection query
     *
     * @return int
     */
    public function getCount()
    {
        return $this->_db->count;
    }

    /**
     * Returns the number of rows in the table
     *
     * @throws Exception
     */
    public function countRows()
    {
        return $this->_db->getValue($this->getTableName(), "count(*)");
    }

    /**
     * Returns the data-points matching the provided date range
     *
     * @param int $startDate
     * @param int $endDate
     * @return array|MysqliDb
     * @throws Exception
     */
    public function find(int $startDate, int $endDate)
    {
        return $this->_db
            ->where("timestamp", Array($startDate, $endDate), "BETWEEN")
            ->orderBy("timestamp", "asc")
            ->get($this->getTableName());
    }

    /**
     * Batch insertion
     *
     * @param array $input
     * @param int $chunkSize
     * @throws Exception
     */
    public function batchInsert(Array $input, int $chunkSize): void
    {
        $this->_db->startTransaction();
        foreach (array_chunk($input, $chunkSize) as $i => $chunk) {
            $insertMulti = $this->_db
                //->setQueryOption("IGNORE") // Not working as expected. Returns error signal when no data is inserted. https://github.com/ThingEngineer/PHP-MySQLi-Database-Class/issues/918
                ->insertMulti($this->getTableName(), $chunk);
            if (!$insertMulti) {
                $this->_db->rollback();
                throw new Exception(sprintf("Error while inserting data in database: %s. Rollback performed.", $this->_db->getLastError()));
            }
        }
        $this->_db->commit();
    }

    /**
     * Returns the name of the table according to the suffix provided
     *
     * @return string
     * @throws Exception
     */
    private function getTableName(): string
    {
        if (empty($this->_suffix)) {
            throw new Exception("Suffix has not been set.");
        }
        return self::TABLE_PREFIX . "_" . $this->_suffix;
    }
}
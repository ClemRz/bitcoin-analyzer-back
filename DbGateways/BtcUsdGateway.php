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

    private $db;
    private $suffix;

    /**
     * BtcUsdGateway constructor.
     *
     * @param MysqliDb $db
     */
    public function __construct(MysqliDb $db)
    {
        $this->db = $db;
    }

    /**
     * @param string $suffix
     * @throws Exception
     */
    public function setSuffix(string $suffix): void
    {
        if (!in_array($suffix, self::TABLE_SUFFIX_WHITELIST)) {
            throw new Exception("Invalid value for suffix");
        }
        $this->suffix = $suffix;
    }

    /**
     * @return array|MysqliDb
     * @throws Exception
     */
    public function findLatest()
    {
        $this->db->orderBy("timestamp", "desc");
        return $this->db->get($this->getTableName(), 1);
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->db->count;
    }

    /**
     * @throws Exception
     */
    public function countRows()
    {
        return $this->db->getValue($this->getTableName(), "count(*)");
    }

    /**
     * @param int $startDate
     * @param int $endDate
     * @return array|MysqliDb
     * @throws Exception
     */
    public function find(int $startDate, int $endDate)
    {
        $this->db->where("timestamp", Array($startDate, $endDate), "BETWEEN");
        $this->db->orderBy("timestamp", "asc");
        return $this->db->get($this->getTableName());
    }

    /**
     * @param array $input
     * @param int $chunkSize
     * @throws Exception
     */
    public function chunkInsert(Array $input, int $chunkSize)
    {
        $this->db->startTransaction();
        foreach (array_chunk($input, $chunkSize) as $i => $chunk) {
            if (!$this->db->insertMulti($this->getTableName(), $chunk)) {
                $this->db->rollback();
                throw new Exception("Error while inserting data in database. Rollback performed.");
            }
        }
        $this->db->commit();
    }

    /**
     * Returns the name of the table according to the suffix provided
     *
     * @return string
     * @throws Exception
     */
    private function getTableName(): string
    {
        if (empty($this->suffix)) {
            throw new Exception("Suffix has not been set.");
        }
        return self::TABLE_PREFIX . "_" . $this->suffix;
    }
}
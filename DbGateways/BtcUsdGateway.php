<?php

namespace DbGateways;

/**
 * Class BtcUsdGateway
 * @package DbGateways
 */
class BtcUsdGateway {
    private const TABLE_NAME = "BTCUSD";

    private $db = null;

    public function __construct(\MysqliDb $db)
    {
        $this->db = $db;
    }

    /**
     * @return array|\MysqliDb
     * @throws \Exception
     */
    public function findLatest()
    {
        $this->db->orderBy("timestamp", "desc");
        return $this->db->get(self::TABLE_NAME, 1);
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->db->count;
    }

    /**
     * @throws \Exception
     */
    public function countRows()
    {
        return $this->db->getValue(self::TABLE_NAME, "count(*)");
    }

    /**
     * @param int $startDate
     * @param int $endDate
     * @return array|\MysqliDb
     * @throws \Exception
     */
    public function find(int $startDate, int $endDate)
    {
        $this->db->where("timestamp", Array($startDate, $endDate), "BETWEEN");
        $this->db->orderBy("timestamp", "asc");
        return $this->db->get(self::TABLE_NAME);
    }

    /**
     * @param array $input
     * @param int $chunkSize
     * @throws \Exception
     */
    public function chunkInsert(Array $input, int $chunkSize)
    {
        $this->db->startTransaction();
        foreach (array_chunk($input, $chunkSize) as $i => $chunk) {
            if (!$this->db->insertMulti(self::TABLE_NAME, $chunk)) {
                $this->db->rollback();
                throw new \Exception("Error while inserting data in database. Rollback performed.");
            }
        }
        $this->db->commit();
    }
}
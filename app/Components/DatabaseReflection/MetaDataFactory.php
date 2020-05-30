<?php

namespace FKSDB\Components\DatabaseReflection;

use Nette\Database\Connection;

/**
 * Class MetaDataFactory
 * *
 */
class MetaDataFactory {
    /**
     * @var array[]
     */
    private $metadata = [];
    /**
     * @var Connection
     */
    private $connection;

    /**
     * MetaDataFactory constructor.
     * @param Connection $connection
     */
    public function __construct(Connection $connection) {
        $this->connection = $connection;
    }

    public function getMetaData(string $table, string $field): array {
        if (!isset($this->metadata[$table])) {
            $this->fetchMeta($table);
        }
        return $this->metadata[$table][$field];
    }

    /**
     * @param string $tableName
     * @return void
     */
    private function fetchMeta(string $tableName) {
        $this->metadata[$tableName] = [];
        foreach ($this->connection->getSupplementalDriver()->getColumns($tableName) as $columnMeta) {
            $this->metadata[$tableName][$columnMeta['name']] = $columnMeta;
        }
    }
}

<?php

namespace FKSDB\Components\DatabaseReflection;

use Nette\Database\Connection;
use Tracy\Debugger;

/**
 * Class MetaDataFactory
 * @package FKSDB\Components\Forms\Factories
 */
class MetaDataFactory {
    /**
     * @var array[][]
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

    /**
     * @param $table
     * @param $field
     * @return array
     */
    public function getMetaData($table, $field): array {
        if (!isset($this->metadata[$table])) {
            $this->fetchMeta($table);
        }
        return $this->metadata[$table][$field];
    }

    /**
     * @param string $tableName
     */
    private function fetchMeta(string $tableName) {
        Debugger::barDump($this->connection->getSupplementalDriver()->getForeignKeys($tableName), $tableName . '--FK');
        Debugger::barDump($this->connection->getSupplementalDriver()->getIndexes($tableName), $tableName . '--IDX');
        $this->metadata[$tableName] = [];
        foreach ($this->connection->getSupplementalDriver()->getColumns($tableName) as $columnMeta) {
            $this->metadata[$tableName][$columnMeta['name']] = $columnMeta;
        }
    }
}

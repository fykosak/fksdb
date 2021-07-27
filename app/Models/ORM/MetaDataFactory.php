<?php

namespace FKSDB\Models\ORM;

use Nette\Database\Connection;

class MetaDataFactory {

    private array $metadata = [];
    private Connection $connection;

    public function __construct(Connection $connection) {
        $this->connection = $connection;
    }

    public function getMetaData(string $table, string $field): array {
        if (!isset($this->metadata[$table])) {
            $this->fetchMeta($table);
        }
        return $this->metadata[$table][$field];
    }

    private function fetchMeta(string $tableName): void {
        $this->metadata[$tableName] = [];
        foreach ($this->connection->getDriver()->getColumns($tableName) as $columnMeta) {
            $this->metadata[$tableName][$columnMeta['name']] = $columnMeta;
        }
    }
}

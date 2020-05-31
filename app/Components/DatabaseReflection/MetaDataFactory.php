<?php

namespace FKSDB\Components\DatabaseReflection;

use Nette\Database\Connection;

/**
 * Class MetaDataFactory
 * @author Michal Červeňák <miso@fykos.cz>
 */
class MetaDataFactory {
    /**
     * @var array[]
     */
    private array $metadata = [];

    private Connection $connection;

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

    private function fetchMeta(string $tableName): void {
        $this->metadata[$tableName] = [];
        foreach ($this->connection->getSupplementalDriver()->getColumns($tableName) as $columnMeta) {
            $this->metadata[$tableName][$columnMeta['name']] = $columnMeta;
        }
    }
}

<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM;

use Nette\Database\Connection;

class MetaDataFactory
{
    /**
     * @phpstan-var array<string,array<string,array{'size':int|null}>>
     */
    private array $metadata = [];
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @phpstan-return array{'size':int|null}
     */
    public function getMetaData(string $table, string $field): array
    {
        if (!isset($this->metadata[$table])) {
            $this->fetchMeta($table);
        }
        return $this->metadata[$table][$field];
    }

    private function fetchMeta(string $tableName): void
    {
        $this->metadata[$tableName] = [];
        foreach ($this->connection->getDriver()->getColumns($tableName) as $columnMeta) {
            $this->metadata[$tableName][$columnMeta['name']] = $columnMeta;//@phpstan-ignore-line
        }
    }
}

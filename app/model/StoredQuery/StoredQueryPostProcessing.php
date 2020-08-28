<?php

namespace FKSDB\StoredQuery;

use Nette\SmartObject;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class StoredQueryPostProcessing {
    use SmartObject;

    protected array $parameters = [];

    final public function resetParameters(): void {
        $this->parameters = [];
    }

    /**
     * @param mixed $key
     * @param mixed $value
     */
    final public function bindValue($key, $value): void {
        $this->parameters[$key] = $value; // type is ignored so far
    }

    public function keepsCount(): bool {
        return true;
    }

    abstract public function processData(\PDOStatement $data): iterable;

    abstract public function getDescription(): string;
}

<?php

namespace Exports;

use Nette\SmartObject;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class StoredQueryPostProcessing {
    use SmartObject;

    /**
     * @var array
     */
    protected $parameters;

    final public function resetParameters() {
        $this->parameters = [];
    }

    /**
     * @param $key
     * @param $value
     * @param null $type
     */
    final public function bindValue($key, $value, $type = null) {
        $this->parameters[$key] = $value; // type is ignored so far
    }

    /**
     * @return bool
     */
    public function keepsCount(): bool {
        return true;
    }

    /**
     * @param $data
     * @return mixed
     */
    abstract public function processData(\PDOStatement $data);

    /**
     * @return mixed
     */
    abstract public function getDescription(): string;
}

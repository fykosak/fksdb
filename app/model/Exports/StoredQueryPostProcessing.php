<?php

namespace Exports;

use Nette\Object;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class StoredQueryPostProcessing extends Object {

    /**
     * @var array
     */
    protected $parameters;

    public final function resetParameters() {
        $this->parameters = [];
    }

    /**
     * @param $key
     * @param $value
     * @param null $type
     */
    public final function bindValue($key, $value, $type = null) {
        $this->parameters[$key] = $value; // type is ignored so far
    }

    /**
     * @return bool
     */
    public function keepsCount() {
        return true;
    }

    /**
     * @param $data
     * @return mixed
     */
    abstract public function processData($data);

    /**
     * @return mixed
     */
    abstract public function getDescription();
}

<?php

namespace Exports;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class StoredQueryPostProcessing {

    /**
     * @var array
     */
    protected $parameters;

    public final function resetParameters() {
        $this->parameters = array();
    }

    public final function bindValue($key, $value, $type = null) {
        $this->parameters[$key] = $value; // type is ignored so far
    }

    public function keepsCount() {
        return true;
    }

    abstract public function processData($data);

    abstract public function getDescription();
}

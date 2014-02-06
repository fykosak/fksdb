<?php

namespace FKS\Config;

use ArrayAccess;
use Nette\DI\Container;
use Nette\InvalidStateException;
use Nette\Object;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class GlobalParameters extends Object implements ArrayAccess {

    /**
     * @var array
     */
    private $parameters;

    function __construct(Container $container) {
        $this->parameters = $container->getParameters();
    }

    public function offsetExists($offset) {
        return isset($this->parameters[$offset]);
    }

    public function offsetGet($offset) {
        return $this->parameters[$offset];
    }

    public function offsetSet($offset, $value) {
        throw new InvalidStateException('Parameters are readonly.');
    }

    public function offsetUnset($offset) {
        throw new InvalidStateException('Parameters are readonly.');
    }

}

?>

<?php

namespace FKSDB\Config;

use ArrayAccess;
use Nette\DI\Container;
use Nette\InvalidStateException;
use Nette\SmartObject;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class GlobalParameters implements ArrayAccess {

    use SmartObject;

    /**
     * @var array
     */
    private $parameters;

    /**
     * GlobalParameters constructor.
     * @param Container $container
     */
    public function __construct(Container $container) {
        $this->parameters = $container->getParameters();
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset) {
        return isset($this->parameters[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset) {
        return $this->parameters[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value) {
        throw new InvalidStateException('Parameters are readonly.');
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset) {
        throw new InvalidStateException('Parameters are readonly.');
    }

}



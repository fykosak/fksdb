<?php

namespace Logging;

use FKS\Logging\FlashMessageDump;
use Nette\InvalidArgumentException;
use Nette\MemberAccessException;
use Nette\Utils\Strings;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class FlashDumpFactory {

    const CREATE_PREFIX = 'create';

    private $configuration;
    private $cache = array();

    function __construct($configuration) {
        $this->configuration = $configuration;
    }

    public function __call($name, $arguments) {
        if (Strings::startsWith($name, self::CREATE_PREFIX)) {
            $configName = substr($name, strlen(self::CREATE_PREFIX));
            $configName = lcfirst($configName);
            return $this->create($configName);
        }
        throw new MemberAccessException("Unknown method $name.");
    }

    private function create($name) {
        if (!isset($this->configuration[$name])) {
            throw new InvalidArgumentException("Unknown flash dump configuration '$name'.");
        }
        if (!isset($this->cache[$name])) {
            $this->cache[$name] = new FlashMessageDump($this->configuration[$name]);
        }
        return $this->cache[$name];
    }

}

<?php

namespace FKSDB\Logging;


use Nette\MemberAccessException;
use Nette\Utils\Strings;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class FlashDumpFactory {

    const CREATE_PREFIX = 'create';

    const DEFAULT_CONFIGURATION = 'default';

    private $configuration;
    /**
     * @var FlashMessageDump[]
     */
    private $cache = [];

    function __construct($configuration) {
        $this->configuration = $configuration;
    }

    public function __call($name, $arguments): FlashMessageDump {
        if (Strings::startsWith($name, self::CREATE_PREFIX)) {
            $configName = substr($name, strlen(self::CREATE_PREFIX));
            $configName = lcfirst($configName);
            return $this->create($configName);
        }
        throw new MemberAccessException("Unknown method $name.");
    }

    public function create($name): FlashMessageDump {
        if (!isset($this->configuration[$name])) {
            $name = self::DEFAULT_CONFIGURATION;
        }
        if (!isset($this->cache[$name])) {
            $this->cache[$name] = new FlashMessageDump($this->configuration[$name]);
        }
        return $this->cache[$name];
    }

}

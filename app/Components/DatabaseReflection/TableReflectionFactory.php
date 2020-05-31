<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\DatabaseReflection\Links\AbstractLink;
use Nette\DI\Container;
use Nette\InvalidArgumentException;
use Nette\SmartObject;
use Nette\Utils\Html;

/**
 * Class TableReflectionFactory
 * *
 */
final class TableReflectionFactory {
    use SmartObject;

    /**
     * @var Container
     */
    private $container;

    /**
     * PersonInfoFactory constructor.
     * @param Container $container
     */
    public function __construct(Container $container) {
        $this->container = $container;
    }

    public function loadRowFactory(string $factoryName): AbstractRow {
        $service = $this->container->getService('DBReflection.' . $factoryName);
        if (!$service instanceof AbstractRow) {
            throw new InvalidArgumentException('Field ' . $factoryName . ' not exists');
        }
        return $service;
    }

    /**
     * @param string $linkId
     * @return AbstractLink
     * @throws \Exception
     */
    public function loadLinkFactory(string $linkId): AbstractLink {
        $service = $this->container->getService('DBReflection.link.' . $linkId);
        if (!$service instanceof AbstractLink) {
            throw new InvalidArgumentException('LinkFactory ' . $linkId . ' not exists');
        }
        return $service;
    }
}

<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\DatabaseReflection\AbstractRow;
use FKSDB\Components\DatabaseReflection\Links\AbstractLink;
use FKSDB\Exceptions\BadTypeException;
use Nette\DI\Container;
use Nette\DI\MissingServiceException;
use Nette\SmartObject;

/**
 * Class TableReflectionFactory
 * @author Michal Červeňák <miso@fykos.cz>
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

    /**
     * @param string $factoryName
     * @return AbstractRow
     * @throws BadTypeException
     * @throws MissingServiceException
     */
    public function loadRowFactory(string $factoryName): AbstractRow {
        $service = $this->container->getService('DBReflection.' . $factoryName);
        if (!$service instanceof AbstractRow) {
            throw new BadTypeException(AbstractRow::class, $service);
        }
        return $service;
    }

    /**
     * @param string $linkId
     * @return AbstractLink
     * @throws MissingServiceException
     * @throws BadTypeException
     */
    public function loadLinkFactory(string $linkId): AbstractLink {
        $service = $this->container->getService('DBReflection.link.' . $linkId);
        if (!$service instanceof AbstractLink) {
            throw new BadTypeException(AbstractLink::class, $service);
        }
        return $service;
    }
}

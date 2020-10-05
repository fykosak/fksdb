<?php

namespace FKSDB\DBReflection;

use FKSDB\DBReflection\ColumnFactories\IColumnFactory;
use FKSDB\DBReflection\LinkFactories\ILinkFactory;
use FKSDB\Exceptions\BadTypeException;
use Nette\DI\Container;
use Nette\DI\MissingServiceException;
use Nette\SmartObject;

/**
 * Class TableReflectionFactory
 * @author Michal Červeňák <miso@fykos.cz>
 */
final class DBReflectionFactory {
    use SmartObject;

    private Container $container;

    public function __construct(Container $container) {
        $this->container = $container;
    }

    /**
     * @param string $factoryName
     * @return IColumnFactory
     * @throws BadTypeException
     * @throws MissingServiceException
     */
    public function loadColumnFactory(string $factoryName): IColumnFactory {
        $service = $this->container->getService('DBReflection.column.' . $factoryName);
        if (!$service instanceof IColumnFactory) {
            throw new BadTypeException(IColumnFactory::class, $service);
        }
        return $service;
    }

    /**
     * @param string $linkId
     * @return ILinkFactory
     * @throws MissingServiceException
     * @throws BadTypeException
     */
    public function loadLinkFactory(string $linkId): ILinkFactory {
        $service = $this->container->getService('DBReflection.link.' . $linkId);
        if (!$service instanceof ILinkFactory) {
            throw new BadTypeException(ILinkFactory::class, $service);
        }
        return $service;
    }
}

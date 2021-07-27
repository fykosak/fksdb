<?php

namespace FKSDB\Models\ORM;

use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Links\LinkFactory;
use Nette\DI\Container;
use Nette\DI\MissingServiceException;
use Nette\SmartObject;

final class ORMFactory {

    use SmartObject;

    private Container $container;

    public function __construct(Container $container) {
        $this->container = $container;
    }

    /**
     * @param string $tableName
     * @param string $colName
     * @return ColumnFactory
     * @throws BadTypeException
     * @throws MissingServiceException
     */
    public function loadColumnFactory(string $tableName, string $colName): ColumnFactory {
        $service = $this->container->getService('orm.' . $tableName . '.column.' . $colName);
        if (!$service instanceof ColumnFactory) {
            throw new BadTypeException(ColumnFactory::class, $service);
        }
        return $service;
    }

    /**
     * @param string $tableName
     * @param string $linkId
     * @return LinkFactory
     * @throws BadTypeException
     * @throws MissingServiceException
     */
    public function loadLinkFactory(string $tableName, string $linkId): LinkFactory {
        $service = $this->container->getService('orm.' . $tableName . '.link.' . $linkId);
        if (!$service instanceof LinkFactory) {
            throw new BadTypeException(LinkFactory::class, $service);
        }
        return $service;
    }
}

<?php

namespace FKSDB\Models\ORM;

use FKSDB\Models\ORM\Columns\IColumnFactory;
use FKSDB\Models\ORM\Links\ILinkFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use Nette\DI\Container;
use Nette\SmartObject;

/**
 * Class TableReflectionFactory
 * @author Michal Červeňák <miso@fykos.cz>
 */
final class ORMFactory {

    use SmartObject;

    private Container $container;

    public function __construct(Container $container) {
        $this->container = $container;
    }

    /**
     * @param string $tableName
     * @param string $colName
     * @return IColumnFactory
     * @throws BadTypeException
     */
    public function loadColumnFactory(string $tableName, string $colName): IColumnFactory {
        $service = $this->container->getService('orm.' . $tableName . '.column.' . $colName);
        if (!$service instanceof IColumnFactory) {
            throw new BadTypeException(IColumnFactory::class, $service);
        }
        return $service;
    }

    /**
     * @param string $tableName
     * @param string $linkId
     * @return ILinkFactory
     * @throws BadTypeException
     */
    public function loadLinkFactory(string $tableName, string $linkId): ILinkFactory {
        $service = $this->container->getService('orm.' . $tableName . '.link.' . $linkId);
        if (!$service instanceof ILinkFactory) {
            throw new BadTypeException(ILinkFactory::class, $service);
        }
        return $service;
    }
}

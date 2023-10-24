<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Columns\ColumnFactory;
use FKSDB\Models\ORM\Links\Link;
use Fykosak\NetteORM\Model;
use Nette\DI\Container;
use Nette\DI\MissingServiceException;
use Nette\SmartObject;

final class ReflectionFactory
{
    use SmartObject;

    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @phpstan-return ColumnFactory<Model,mixed>
     * @throws MissingServiceException
     * @throws BadTypeException
     */
    public function loadColumnFactory(string $tableName, string $colName): ColumnFactory
    {
        $service = $this->container->getService('orm.' . $tableName . '.column.' . $colName);
        if (!$service instanceof ColumnFactory) {
            throw new BadTypeException(ColumnFactory::class, $service);
        }
        return $service;
    }

    /**
     * @throws BadTypeException
     * @throws MissingServiceException
     * @phpstan-return Link<Model>
     */
    public function loadLinkFactory(string $tableName, string $linkId): Link
    {
        $service = $this->container->getService('orm.' . $tableName . '.link.' . $linkId);
        if (!$service instanceof Link) {
            throw new BadTypeException(Link::class, $service);
        }
        return $service;
    }
}
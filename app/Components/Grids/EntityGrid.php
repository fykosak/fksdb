<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use Fykosak\NetteORM\Service;
use Fykosak\NetteORM\TypedSelection;
use Nette\DI\Container;

abstract class EntityGrid extends BaseGrid
{
    protected Service $service;
    private array $queryParams;
    private array $columns;

    public function __construct(
        Container $container,
        string $classNameService,
        array $columns = [],
        array $queryParams = []
    ) {
        parent::__construct($container);
        $this->service = $container->getByType($classNameService);
        $this->queryParams = $queryParams;
        $this->columns = $columns;
    }

    protected function getModels(): TypedSelection
    {
        return $this->service->getTable()->where($this->queryParams);
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        $this->addColumns($this->columns);
    }
}

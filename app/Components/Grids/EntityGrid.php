<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use FKSDB\Models\Exceptions\BadTypeException;
use Fykosak\NetteORM\Service;
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

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        parent::configure();
        $this->data = $this->service->getTable()->where($this->queryParams);
        $this->addColumns($this->columns);
    }
}

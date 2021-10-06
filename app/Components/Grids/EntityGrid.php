<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use FKSDB\Models\Exceptions\BadTypeException;
use Fykosak\NetteORM\AbstractService;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateColumnException;

abstract class EntityGrid extends BaseGrid
{

    protected AbstractService $service;

    private array $queryParams;

    private array $columns;

    public function __construct(
        Container $container,
        string $serviceClassName,
        array $columns = [],
        array $queryParams = []
    ) {
        parent::__construct($container);
        $this->service = $container->getByType($serviceClassName);
        $this->queryParams = $queryParams;
        $this->columns = $columns;
    }

    protected function getData(): IDataSource
    {
        $source = $this->service->getTable()->where($this->queryParams);
        return new NDataSource($source);
    }

    /**
     * @throws BadTypeException
     * @throws DuplicateColumnException
     */
    protected function configure(Presenter $presenter): void
    {
        parent::configure($presenter);
        $this->addColumns($this->columns);
    }
}

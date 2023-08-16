<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use Fykosak\NetteORM\Service;
use Fykosak\NetteORM\TypedSelection;
use Nette\DI\Container;

/**
 * @deprecated
 * @template TModel of \Fykosak\NetteORM\Model
 * @phpstan-extends BaseGrid<TModel>
 */
abstract class EntityGrid extends BaseGrid
{
    /** @phpstan-var Service<TModel> */
    protected Service $service;
    /** @phpstan-var array<string,scalar|null> */
    private array $queryParams;
    /** @var string[] */
    private array $columns;

    /**
     * @phpstan-param class-string<Service<TModel>> $classNameService
     * @phpstan-param string[] $columns
     * @phpstan-param array<string,scalar|null> $queryParams
     */
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
     * @phpstan-return TypedSelection<TModel>
     */
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

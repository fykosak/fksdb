<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use FKSDB\Components\Grids\Components\Grid;
use Fykosak\NetteORM\Model;
use Nette\Database\Table\Selection;
use Nette\DI\Container;

abstract class RelatedGrid extends Grid
{
    protected Model $model;
    protected string $tableName;

    public function __construct(Container $container, Model $model, string $tableName)
    {
        parent::__construct($container);
        $this->tableName = $tableName;
        $this->model = $model;
    }

    protected function getModels(): Selection
    {
        return $this->model->related($this->tableName);
    }

    protected function configure(): void
    {
    }
}

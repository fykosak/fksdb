<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use Fykosak\NetteORM\Model;
use Nette\DI\Container;

abstract class RelatedGrid extends BaseGrid
{

    protected Model $model;
    protected string $tableName;

    public function __construct(Container $container, Model $model, string $tableName)
    {
        parent::__construct($container);
        $this->tableName = $tableName;
        $this->model = $model;
    }

    protected function setData(): void
    {
        $this->data = $this->model->related($this->tableName);
    }
}

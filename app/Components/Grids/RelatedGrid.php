<?php

namespace FKSDB\Components\Grids;

use Fykosak\NetteORM\AbstractModel;
use Nette\DI\Container;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DataSource\NDataSource;

abstract class RelatedGrid extends BaseGrid
{

    protected AbstractModel $model;
    protected string $tableName;

    public function __construct(Container $container, AbstractModel $model, string $tableName)
    {
        parent::__construct($container);
        $this->tableName = $tableName;
        $this->model = $model;
    }

    protected function getData(): IDataSource
    {
        $query = $this->model->related($this->tableName);
        return new NDataSource($query);
    }
}

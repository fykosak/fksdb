<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\ListComponent\Row;

use FKSDB\Components\Grids\ListComponent\Column\Column;
use FKSDB\Components\Grids\ListComponent\Column\ORMColumn;
use Fykosak\NetteORM\Model;

class ColumnsRow extends Row
{
    public function createReferencedRow(string $name): Column
    {
        $col = new ORMColumn($this->container, $name);
        $this->addComponent($col, str_replace('.', '__', $name));
        return $col;
    }

    public function render(Model $model): void
    {
        $this->template->className = $this->className;
        $this->template->model = $model;
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'columns.latte');
    }
}

<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\ListComponent;

use Fykosak\NetteORM\Model;

class ColumnsRow extends Row
{
    public function createReferencedRow(string $name): Colum
    {
        $col = new ORMColumn($this->container, $name);
        $this->addComponent($col, str_replace('.', '__', $name));
        return $col;
    }

    public function render(Model $model): void
    {
        $this->template->className = $this->className;
        $this->template->model = $model;
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'row.columns.latte');
    }
}

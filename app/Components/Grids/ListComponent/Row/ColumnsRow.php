<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\ListComponent\Row;

use FKSDB\Components\Grids\ListComponent\Column\ORMColumn;
use FKSDB\Components\Grids\ListComponent\Column\RendererColumn;

class ColumnsRow extends Row
{
    public function createReferencedColumn(string $name): ORMColumn
    {
        $col = new ORMColumn($this->container, $name);
        $this->addComponent($col, str_replace('.', '__', $name));
        return $col;
    }

    public function createRendererColumn(string $name, callable $renderer): RendererColumn
    {
        $col = new RendererColumn($this->container, $renderer);
        $this->addComponent($col, $name);
        return $col;
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'columns.latte';
    }
}

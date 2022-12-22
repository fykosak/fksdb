<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\ListComponent\Row;

use FKSDB\Components\Grids\ListComponent\ItemComponent;
use FKSDB\Components\Grids\ListComponent\Referenced\DefaultItem;
use FKSDB\Components\Grids\ListComponent\Renderer\RendererItem;

class ColumnsRow extends ItemComponent
{
    public function createReferencedColumn(string $name): DefaultItem
    {
        $col = new DefaultItem($this->container, $name);
        $this->addComponent($col, str_replace('.', '__', $name));
        return $col;
    }

    public function createRendererColumn(string $name, callable $renderer): RendererItem
    {
        $col = new RendererItem($this->container, $renderer);
        $this->addComponent($col, $name);
        return $col;
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'columns.latte';
    }
}

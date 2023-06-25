<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components;

abstract class FilterGrid extends BaseGrid
{
    use FilterTrait;

    public function render(): void
    {
        $this->traitRender();
        parent::render();
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'grid.filter.latte';
    }
}

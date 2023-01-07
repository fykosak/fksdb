<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components;

abstract class FilterList extends ListComponent
{
    use FilterTrait;

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'list.filter.latte';
    }

    public function render(): void
    {
        $this->traitRender();
        parent::render();
    }
}

<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components;

abstract class FilterListComponent extends ListComponent
{
    use FilterComponentTrait;

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'filter.latte';
    }

    public function render(): void
    {
        $this->traitRender();
        parent::render();
    }
}

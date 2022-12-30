<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components;

abstract class FilterBaseGrid extends BaseGrid
{
    use FilterComponentTrait;

    public function render(): void
    {
        $this->traitRender();
        parent::render();
    }
}

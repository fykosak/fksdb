<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Models\Exceptions\BadTypeException;
use Nette\Application\UI\Form;

abstract class FilterBaseGrid extends BaseGrid
{
    use FilterComponentTrait;

    public function render(): void
    {
        $this->traitRender();
        parent::render();
    }
}

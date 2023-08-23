<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components\Button;

use Nette\Application\UI\Control;

/**
 * @phpstan-template TModel of \Fykosak\NetteORM\Model
 * @phpstan-extends Button<TModel>
 */
class PresenterButton extends Button
{
    protected function getLinkControl(): Control
    {
        return $this->getPresenter();
    }
}

<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components\Button;

use Nette\Application\UI\Control;

/**
 * @template M of \Fykosak\NetteORM\Model
 * @phpstan-extends Button<M>
 */
class PresenterButton extends Button
{
    protected function getLinkControl(): Control
    {
        return $this->getPresenter();
    }
}

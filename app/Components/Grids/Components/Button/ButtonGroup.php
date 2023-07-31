<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components\Button;

use FKSDB\Components\Grids\Components\BaseItem;

/**
 * @template M of \Fykosak\NetteORM\Model
 * @phpstan-extends BaseItem<M>
 */
class ButtonGroup extends BaseItem
{
    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'group.latte';
    }
}

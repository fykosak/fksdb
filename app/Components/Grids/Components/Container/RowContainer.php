<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components\Container;

use FKSDB\Components\Grids\Components\BaseItem;

/**
 * @phpstan-template TModel of \Fykosak\NetteORM\Model
 * @phpstan-extends BaseItem<TModel>
 */
class RowContainer extends BaseItem
{
    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'row.latte';
    }
}

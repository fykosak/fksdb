<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components\Container;

use FKSDB\Components\Grids\Components\BaseItem;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-template TModel of \Fykosak\NetteORM\Model
 * @phpstan-extends BaseItem<TModel>
 */
class RowContainer extends BaseItem
{
    public function render(Model $model, int $userPermission): void
    {
        $this->template->render(
            __DIR__ . DIRECTORY_SEPARATOR . 'row.latte',
            [
                'model' => $model,
                'userPermission' => $userPermission,
            ]
        );
    }
}

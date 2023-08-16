<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components\Button;

use FKSDB\Components\Grids\Components\BaseItem;
use Fykosak\NetteORM\Model;
use Nette\DI\Container;

/**
 * @template TModel of \Fykosak\NetteORM\Model
 * @phpstan-extends BaseItem<TModel>
 */
class ButtonGroup extends BaseItem
{
    public function __construct(Container $container)
    {
        parent::__construct($container);
    }

    /**
     * @param TModel $model
     */
    public function render(Model $model, int $userPermission): void
    {
        $this->template->render(
            __DIR__ . DIRECTORY_SEPARATOR . 'group.latte',
            [
                'model' => $model,
                'userPermission' => $userPermission,
            ]
        );
    }
}

<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components\TableRow;

use FKSDB\Components\Grids\Components\BaseItem;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\UI\Title;
use Nette\ComponentModel\Container;
use Nette\DI\Container as DIContainer;

/**
 * @phpstan-template TModel of \Fykosak\NetteORM\Model
 * @phpstan-extends BaseItem<TModel>
 */
final class TableRow extends BaseItem
{
    public Container $buttons;
    public Container $columns;

    public function __construct(DIContainer $container)
    {
        parent::__construct($container);
        $this->buttons = new Container();
        $this->addComponent($this->buttons, 'buttons');
        $this->columns = new Container();
        $this->addComponent($this->columns, 'columns');
    }

    /**
     * @phpstan-param TModel $model
     */
    public function render(Model $model, int $userPermission): void
    {
        $this->template->render(
            __DIR__ . DIRECTORY_SEPARATOR . 'tableRow.row.latte',
            [
                'model' => $model,
                'userPermission' => $userPermission,
            ]
        );
    }

    /**
     * @phpstan-param BaseItem<TModel> $itemComponent
     */
    public function addButton(BaseItem $itemComponent, string $name): void
    {
        $this->buttons->addComponent($itemComponent, $name);
    }

    /**
     * @phpstan-param BaseItem<TModel> $itemComponent
     */
    public function addColumn(BaseItem $itemComponent, string $name): void
    {
        $this->columns->addComponent($itemComponent, $name);
    }

    public function renderHead(): void
    {
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'tableRow.head.latte');
    }

    public function getTitle(): ?Title
    {
        return null;
    }
}

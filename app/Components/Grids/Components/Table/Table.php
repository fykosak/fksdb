<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components\Table;

use FKSDB\Components\Grids\Components\BaseItem;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Fykosak\Utils\UI\Title;
use Nette\ComponentModel\Container;
use Nette\DI\Container as DIContainer;

/**
 * @phpstan-template TModel of \Fykosak\NetteORM\Model\Model
 */
final class Table extends BaseComponent
{
    private Container $buttons;
    private Container $columns;

    public function __construct(DIContainer $container)
    {
        parent::__construct($container);
        $this->buttons = new Container();
        $this->addComponent($this->buttons, 'buttons');
        $this->columns = new Container();
        $this->addComponent($this->columns, 'columns');
    }

    /**
     * @phpstan-template TComponent of BaseItem<TModel>
     * @phpstan-param TComponent $component
     * @phpstan-return TComponent
     */
    public function addButton(BaseItem $component, string $name): BaseItem
    {
        $this->buttons->addComponent($component, $name);
        return $component;
    }

    /**
     * @phpstan-template TComponent of BaseItem<TModel>
     * @phpstan-param TComponent $component
     * @phpstan-return TComponent
     */
    public function addColumn(BaseItem $component, string $name): BaseItem
    {
        $this->columns->addComponent($component, $name);
        return $component;
    }

    /**
     * @param \Iterator<TModel> $models
     */
    public function render(
        iterable $models,
        bool $head,
        int $userPermission,
        string $className = 'table table-sm table-striped'
    ): void {
        $this->template->render(
            __DIR__ . DIRECTORY_SEPARATOR . 'table.latte',
            [
                'models' => $models,
                'head' => $head,
                'userPermission' => $userPermission,
                'className' => $className,
            ]
        );
    }

    public function renderHead(): void
    {
        $this->template->render(
            __DIR__ . DIRECTORY_SEPARATOR . 'table.head.latte',
            [
                'buttons' => $this->buttons,
                'columns' => $this->columns,
            ]
        );
    }

    /**
     * @phpstan-param TModel $model
     */
    public function renderRow(Model $model, int $userPermission): void
    {
        $this->template->render(
            __DIR__ . DIRECTORY_SEPARATOR . 'table.row.latte',
            [
                'buttons' => $this->buttons,
                'columns' => $this->columns,
                'model' => $model,
                'userPermission' => $userPermission,
            ]
        );
    }


    public function getTitle(): ?Title
    {
        return null;
    }
}

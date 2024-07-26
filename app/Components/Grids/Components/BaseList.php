<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components;

use FKSDB\Models\ORM\FieldLevelPermissionValue;
use FKSDB\Components\Grids\Components\Button\ButtonGroup;
use FKSDB\Components\Grids\Components\Container\ListRows;
use FKSDB\Models\ORM\ORMFactory;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

/**
 * @phpstan-template TModel of \Fykosak\NetteORM\Model\Model
 * @phpstan-template TFilterParams of array
 * @phpstan-extends BaseComponent<TModel,TFilterParams>
 */
abstract class BaseList extends BaseComponent
{
    // phpcs:disable
    protected const ModeAlert = 'alert';
    protected const ModePanel = 'panel';
    protected const ModeCard = 'card';
    // phpcs:enable

    /** @phpstan-var callable(TModel):string */
    protected $classNameCallback = null;

    /** @phpstan-var self::Mode* $mode */
    protected string $mode = self::ModePanel;

    public function __construct(Container $container, int $userPermission)
    {
        parent::__construct($container, $userPermission);
        $this->addComponent(new \Nette\ComponentModel\Container(), 'buttons');
        $this->addComponent(new \Nette\ComponentModel\Container(), 'rows');
        $this->paginate = false;
    }

    protected function getTemplatePath(): string
    {
        switch ($this->mode) {
            case self::ModeCard:
                return __DIR__ . DIRECTORY_SEPARATOR . 'list.card.latte';
            case self::ModePanel:
                return __DIR__ . DIRECTORY_SEPARATOR . 'list.panel.latte';
            case self::ModeAlert:
            default:
                return __DIR__ . DIRECTORY_SEPARATOR . 'list.alert.latte';
        }
    }

    public function render(): void
    {
        $this->template->classNameCallback = $this->classNameCallback;
        parent::render();
    }

    abstract protected function configure(): void;

    /**
     * @phpstan-template TComponent of BaseItem<TModel>
     * @phpstan-param TComponent $component
     * @phpstan-return TComponent
     */
    protected function setTitle(BaseItem $component): BaseItem
    {
        $this->addComponent($component, 'title');
        return $component;
    }

    /**
     * @phpstan-template TComponent of BaseItem<TModel>
     * @phpstan-param TComponent $component
     * @phpstan-return TComponent
     */
    public function addRow(BaseItem $component, string $name): BaseItem
    {
        /** @phpstan-ignore-next-line */
        $this->getComponent('rows')->addComponent($component, $name);
        return $component;
    }

    public function createRow(): \Nette\ComponentModel\Container
    {
        $component = new \Nette\ComponentModel\Container();
        /** @phpstan-ignore-next-line */
        $length = count($this->getComponent('rows')->getComponents());
        /** @phpstan-ignore-next-line */
        $this->getComponent('rows')->addComponent($component, 'row' . $length);
        return $component;
    }

    /**
     * @phpstan-template TComponent of BaseItem<TModel>
     * @phpstan-param TComponent $component
     * @phpstan-return TComponent
     */
    public function addButton(BaseItem $component, string $name): BaseItem
    {
        /** @phpstan-ignore-next-line */
        $this->getComponent('buttons')->addComponent($component, $name);
        return $component;
    }
}

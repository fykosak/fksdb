<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components\Container;

use FKSDB\Components\Grids\Components\BaseItem;
use FKSDB\Components\Grids\Components\Button\ButtonGroup;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

/**
 * @template M of \Fykosak\NetteORM\Model
 * @phpstan-extends BaseItem<M>
 */
class TableRow extends BaseItem
{
    /** @var ButtonGroup<M>  */
    public ButtonGroup $buttons;

    public function __construct(Container $container, Title $title)
    {
        parent::__construct($container, $title);
        $this->buttons = new ButtonGroup($container, new Title(null, _('Actions')));
        $this->addComponent($this->buttons, 'buttons');
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'tableRow.latte';
    }

    /**
     * @phpstan-param BaseItem<M> $itemComponent
     */
    public function addButton(BaseItem $itemComponent, string $name): void
    {
        $this->buttons->addComponent($itemComponent, $name);
    }

    public function renderHead(): void
    {
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'tableRow.head.latte');
    }
}

<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Components\Grids\Components\Button\Button;
use FKSDB\Components\Grids\Components\Referenced\TemplateItem;
use FKSDB\Components\Grids\Components\Table\RelatedTable;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use Fykosak\NetteORM\Model;
use Fykosak\NetteORM\TypedGroupedSelection;
use Fykosak\Utils\UI\Title;
use Nette\Application\UI\Presenter;
use Nette\ComponentModel\Component;
use Nette\DI\Container;

/**
 * @phpstan-extends BaseGrid<ScheduleItemModel,array{}>
 */
final class ItemGrid extends BaseGrid
{
    private ScheduleGroupModel $group;

    public function __construct(Container $container, ScheduleGroupModel $group)
    {
        parent::__construct($container);
        $this->group = $group;
    }

    /**
     * @phpstan-return TypedGroupedSelection<ScheduleItemModel>
     */
    protected function getModels(): TypedGroupedSelection
    {
        return $this->group->getItems();
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        $this->paginate = false;
        self::addColumns($this, $this->container, $this->getPresenter());
    }

    /**
     * @param BaseGrid<ScheduleItemModel,array{}>|RelatedTable<Model,ScheduleItemModel> $component
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    public static function addColumns(Component $component, Container $container, Presenter $presenter): void
    {
        $component->addTableColumn(
            new TemplateItem(
                $container,
                '@schedule_item.name:value (@schedule_item.schedule_item_id:value)',
                '@schedule_item.name:title'
            ),
            'title'
        );
        $component->addTableColumn(
            new TemplateItem(
                $container,
                '@schedule_item.price_czk / @schedule_item.price_eur',
                _('Price')
            ),
            'price'
        );
        $component->addTableColumn(
            new TemplateItem(
                $container,
                '@schedule_item.used_capacity / @schedule_item.free_capacity / @schedule_item.capacity',
                _('Used / Free / Total')
            ),
            'capacity'
        );
        $component->addTableButton(
            new Button(
                $container,
                $presenter,
                new Title(null, _('Edit')),
                fn(ScheduleItemModel $model) => [':Schedule:Item:edit', ['id' => $model->schedule_item_id]]
            ),
            'edit'
        );
        $component->addTableButton(
            new Button(
                $container,
                $presenter,
                new Title(null, _('Detail')),
                fn(ScheduleItemModel $model) => [':Schedule:Item:detail', ['id' => $model->schedule_item_id]]
            ),
            'detail'
        );
        $component->addTableButton(
            new Button(
                $container,
                $presenter,
                new Title(null, _('2D code')), // TODO!!!
                fn(ScheduleItemModel $model) => [':Schedule:Item:code', ['id' => $model->schedule_item_id]]
            ),
            'code'
        );
    }
}

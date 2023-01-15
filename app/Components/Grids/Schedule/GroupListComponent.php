<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Schedule;

use FKSDB\Components\Grids\Components\Button\PresenterButton;
use FKSDB\Components\Grids\Components\Container\RelatedTable;
use FKSDB\Components\Grids\Components\Container\RowContainer;
use FKSDB\Components\Grids\Components\ListComponent;
use FKSDB\Components\Grids\Components\Referenced\TemplateItem;
use FKSDB\Components\Grids\Components\Renderer\RendererItem;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\FieldLevelPermissionValue;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use Fykosak\Utils\UI\Title;
use Nette\Database\Table\Selection;
use Nette\DI\Container;

class GroupListComponent extends ListComponent
{
    private EventModel $event;

    public function __construct(Container $container, EventModel $event)
    {
        parent::__construct($container, FieldLevelPermissionValue::Full);
        $this->event = $event;
    }

    protected function getModels(): Selection
    {
        return $this->event->getScheduleGroups();
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        $this->classNameCallback = fn(ScheduleGroupModel $model) => 'alert alert-secondary';
        $this->setTitle(
            new TemplateItem(
                $this->container,
                '@schedule_group.name_cs / @schedule_group.name_en (@schedule_group.schedule_group_id)'
            )
        );
        $row0 = new RowContainer($this->container, new Title(null, ''));
        $this->addRow($row0, 'row0');
        $row0->addComponent(new TemplateItem($this->container, '@schedule_group.schedule_group_type'), 'type');
        $row0->addComponent(
            new RendererItem(
                $this->container,
                fn(ScheduleGroupModel $model) => ($model->start->format('j-n') === $model->end->format('j-n'))
                    ? $model->start->format('j. n. Y H:i') . ' - ' . $model->end->format('H:i')
                    : $model->start->format('j. n. Y H:i') . ' - ' . $model->end->format('j. n. Y H:i'),
                new Title(null, '')
            ),
            'duration'
        );
        $itemsRow = new RelatedTable(
            $this->container,
            fn(ScheduleGroupModel $model) => $model->getItems(),
            new Title(null, _('Items'))
        );
        $this->addRow($itemsRow, 'items');
        $itemsRow->addColumn(
            new TemplateItem(
                $this->container,
                '@schedule_item.name_cs:value / @schedule_item.name_en:value (@schedule_item.schedule_item_id:value)'
            ),
            'title'
        );
        $itemsRow->addColumn(
            new TemplateItem(
                $this->container,
                '@schedule_item.price_czk / @schedule_item.price_eur'
            ),
            'price'
        );
        $itemsRow->addColumn(
            new TemplateItem(
                $this->container,
                '<span title="' . _('Used / Free / Total') .
                '">@schedule_item.used_capacity / @schedule_item.free_capacity / @schedule_item.capacity</span>'
            ),
            'capacity'
        );
        $itemsRow->addButton(
            new PresenterButton(
                $this->container,
                new Title(null, _('Edit')),
                fn(ScheduleItemModel $model) => [':Event:ScheduleItem:edit', ['id' => $model->getPrimary()]]
            ),
            'edit'
        );
        $itemsRow->addButton(
            new PresenterButton(
                $this->container,
                new Title(null, _('Detail')),
                fn(ScheduleItemModel $model) => [':Event:ScheduleItem:detail', ['id' => $model->getPrimary()]]
            ),
            'detail'
        );
        $this->addButton(
            new PresenterButton(
                $this->container,
                new Title(null, _('Detail')),
                fn(ScheduleGroupModel $model) => [':Event:ScheduleGroup:detail', ['id' => $model->getPrimary()]]
            ),
            'detail'
        );
        $this->addButton(
            new PresenterButton(
                $this->container,
                new Title(null, _('Edit')),
                fn(ScheduleGroupModel $model) => [':Event:ScheduleGroup:edit', ['id' => $model->getPrimary()]]
            ),
            'edit'
        );
    }
}

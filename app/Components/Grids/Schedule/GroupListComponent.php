<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Schedule;

use FKSDB\Components\Grids\ListComponent\Button\DefaultButton;
use FKSDB\Components\Grids\ListComponent\Column\ORMTemplateColumn;
use FKSDB\Components\Grids\ListComponent\ListComponent;
use FKSDB\Components\Grids\ListComponent\Row\ORMTemplateRow;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use FKSDB\Models\ValuePrinters\NumberPrinter;
use Nette\DI\Container;
use Nette\Utils\Html;

class GroupListComponent extends ListComponent
{
    private EventModel $event;

    public function __construct(Container $container, EventModel $event)
    {
        parent::__construct($container, FieldLevelPermission::ALLOW_FULL);
        $this->event = $event;
    }

    protected function getModels(): iterable
    {
        return $this->event->getScheduleGroups();
    }

    protected function configure(): void
    {
        $this->classNameCallback = fn(ScheduleGroupModel $model) => 'alert alert-secondary';
        $this->addComponent(
            new ORMTemplateRow(
                $this->container,
                '<strong>@schedule_group.name_cs:value / @schedule_group.name_en:value (#@schedule_group.schedule_group_id:value)</strong>'
            ),
            'title'
        );
        $row0 = $this->createColumnsRow('row0');
        $row0->createReferencedColumn('schedule_group.schedule_group_type');
        $row0->createRendererColumn(
            'duration',
            fn(ScheduleGroupModel $model) => ($model->start->format('j-n') === $model->end->format('j-n'))
                ? $model->start->format('j. n. Y H:i') . ' - ' . $model->end->format('H:i')
                : $model->start->format('j. n. Y H:i') . ' - ' . $model->end->format('j. n. Y H:i')
        );
        $this->createColumnsRow('items_title')->createRendererColumn('title', fn() => _('Items'))->className .= ' h5';
        $itemsRow = $this->createListGroupRow('items', fn(ScheduleGroupModel $model) => $model->getItems());
        $itemsRow->addComponent(
            new ORMTemplateColumn(
                $this->container,
                '@schedule_item.name_cs:value / @schedule_item.name_en:value (@schedule_item.schedule_item_id:value)'
            ),
            'title'
        );
        $itemsRow->addComponent(
            new ORMTemplateColumn(
                $this->container,
                '@schedule_item.price_czk / @schedule_item.price_eur</span>'
            ),
            'price'
        );
        $itemsRow->addComponent(
            new ORMTemplateColumn(
                $this->container,
                '<span title="' . _('Used / Free / Total') .
                '">@schedule_item.used_capacity / @schedule_item.free_capacity / @schedule_item.capacity</span>'
            ),
            'capacity'
        );
        $itemsRow->createRendererColumn(
            'button',
            fn(ScheduleItemModel $model) => Html::el('a')
                ->addAttributes(
                    [
                        'class' => 'btn btn-sm btn-outline-secondary',
                        'href' => $this->getPresenter()->link(
                            ':Event:ScheduleItem:detail',
                            ['id' => $model->getPrimary()]
                        ),
                    ]
                )->addText(_('Detail'))
        );
    }
}

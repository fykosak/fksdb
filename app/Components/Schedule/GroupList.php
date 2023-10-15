<?php

declare(strict_types=1);

namespace FKSDB\Components\Schedule;

use FKSDB\Components\Grids\Components\BaseList;
use FKSDB\Components\Grids\Components\Referenced\SimpleItem;
use FKSDB\Components\Grids\Components\Referenced\TemplateItem;
use FKSDB\Components\Grids\Components\Renderer\RendererItem;
use FKSDB\Components\Grids\Components\Table\RelatedTable;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use Fykosak\NetteORM\TypedGroupedSelection;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

/**
 * @phpstan-extends BaseList<ScheduleGroupModel,array{}>
 */
final class GroupList extends BaseList
{
    private EventModel $event;

    public function __construct(Container $container, EventModel $event)
    {
        parent::__construct($container, FieldLevelPermission::ALLOW_FULL);
        $this->event = $event;
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '../Grids/Components/list.panel.latte';
    }

    /**
     * @phpstan-return TypedGroupedSelection<ScheduleGroupModel>
     */
    protected function getModels(): TypedGroupedSelection
    {
        return $this->event->getScheduleGroups()->order('start');
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        $this->counter = false;
        //    $this->classNameCallback = fn(ScheduleGroupModel $model) => 'alert alert-secondary';
        $this->setTitle( // @phpstan-ignore-line
            new TemplateItem( // @phpstan-ignore-line
                $this->container,
                _('@schedule_group.name_en (@schedule_group.schedule_group_id)')
            )
        );
        $row0 = $this->createRow();
        $row0->addComponent(new SimpleItem($this->container, '@schedule_group.schedule_group_type'), 'type');
        $row0->addComponent(
            new RendererItem(
                $this->container,
                fn(ScheduleGroupModel $model) => ($model->start->format('j-n') === $model->end->format('j-n'))
                    ? $model->start->format('j. n. Y H:i') . ' - ' . $model->end->format('H:i')
                    : $model->start->format('j. n. Y H:i') . ' - ' . $model->end->format('j. n. Y H:i'),
                new Title(null, _('Duration'))
            ),
            'duration'
        );
        /** @phpstan-var RelatedTable<ScheduleGroupModel,ScheduleItemModel> $table */
        $table = $this->addRow(
            new RelatedTable(
                $this->container,
                fn(ScheduleGroupModel $model) => $model->getItems(), //@phpstan-ignore-line
                new Title(null, _('Items')),
                true
            ),
            'items'
        );
        /** @phpstan-ignore-next-line */
        ItemGrid::addColumns($table, $this->container, $this->getPresenter());
        $this->addPresenterButton(
            ':Schedule:Group:detail',
            'detail',
            _('Detail'),
            false,
            ['id' => 'schedule_group_id']
        );

        $this->addPresenterButton(
            ':Schedule:Group:edit',
            'edit',
            _('Edit'),
            false,
            ['id' => 'schedule_group_id']
        );

        $this->addPresenterButton(
            ':Schedule:Group:code',
            'code',
            _('Scan 2D code!'),
            false,
            ['id' => 'schedule_group_id']
        );
    }
}

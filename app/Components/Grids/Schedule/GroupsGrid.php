<?php

namespace FKSDB\Components\Grids\Schedule;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\Schedule\ModelScheduleGroup;
use Nette\DI\Container;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

/**
 * Class GroupsGrid
 * @author Michal Červeňák <miso@fykos.cz>
 */
class GroupsGrid extends BaseGrid {
    /**
     * @var ModelEvent
     */
    private $event;

    /**
     * GroupsGrid constructor.
     * @param ModelEvent $event
     * @param Container $container
     */
    public function __construct(ModelEvent $event, Container $container) {
        parent::__construct($container);
        $this->event = $event;
    }

    public function getModelClassName(): string {
        return ModelScheduleGroup::class;
    }

    /**
     * @param $presenter
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     * @throws NotImplementedException
     */
    protected function configure($presenter) {
        parent::configure($presenter);
        $this->paginate = false;
        $groups = $this->event->getScheduleGroups();

        $dataSource = new NDataSource($groups);
        $this->setDataSource($dataSource);
        $this->addColumn('schedule_group_id', _('#'));
        $this->addColumns([
            'schedule_group.name_cs',
            'schedule_group.name_en',
            'schedule_group.schedule_group_type',
            'schedule_group.start',
            'schedule_group.end',
        ]);

        $this->addColumn('items_count', _('Items count'))->setRenderer(function ($row) {
            $model = ModelScheduleGroup::createFromActiveRow($row);
            return $model->getItems()->count();
        });

        $this->addButton('detail', _('Detail'))->setText(_('Detail'))
            ->setLink(function ($row) {
                /** @var ModelScheduleGroup $row */
                return $this->getPresenter()->link('ScheduleItem:list', ['groupId' => $row->schedule_group_id]);
            });
    }
}

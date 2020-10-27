<?php

namespace FKSDB\Components\Grids\Schedule;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\Schedule\ModelScheduleGroup;
use Nette\Application\IPresenter;
use Nette\DI\Container;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

/**
 * Class GroupsGrid
 * @author Michal Červeňák <miso@fykos.cz>
 */
class GroupsGrid extends BaseGrid {

    private ModelEvent $event;

    public function __construct(ModelEvent $event, Container $container) {
        parent::__construct($container);
        $this->event = $event;
    }

    protected function getModelClassName(): string {
        return ModelScheduleGroup::class;
    }

    protected function getData(): IDataSource {
        $groups = $this->event->getScheduleGroups();
        return new NDataSource($groups);
    }

    /**
     * @param IPresenter $presenter
     * @return void
     * @throws BadTypeException
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     */
    protected function configure(IPresenter $presenter): void {
        parent::configure($presenter);
        $this->paginate = false;
        $this->addColumns([
            'schedule_group.schedule_group_id',
            'schedule_group.name_cs',
            'schedule_group.name_en',
            'schedule_group.schedule_group_type',
            'schedule_group.start',
            'schedule_group.end',
        ]);

        $this->addColumn('items_count', _('Items count'))->setRenderer(function ($row): int {
            $model = ModelScheduleGroup::createFromActiveRow($row);
            return $model->getItems()->count();
        });

        $this->addButton('detail')->setText(_('Detail'))
            ->setLink(function ($row): string {
                /** @var ModelScheduleGroup $row */
                return $this->getPresenter()->link('ScheduleGroup:detail', ['id' => $row->schedule_group_id]);
            });
        $this->addButton('edit')->setText(_('Edit'))
            ->setLink(function ($row): string {
                /** @var ModelScheduleGroup $row */
                return $this->getPresenter()->link('ScheduleGroup:edit', ['id' => $row->schedule_group_id]);
            });
    }
}

<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Schedule;

use FKSDB\Components\Grids\RelatedGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\Schedule\ModelScheduleGroup;
use Nette\Application\UI\Presenter;
use Nette\Database\Table\ActiveRow;
use Nette\DI\Container;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

class GroupsGrid extends RelatedGrid
{

    public function __construct(ModelEvent $event, Container $container)
    {
        parent::__construct($container, $event, 'schedule_group');
    }

    /**
     * @param Presenter $presenter
     * @return void
     * @throws BadTypeException
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     */
    protected function configure(Presenter $presenter): void
    {
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
        $this->addColumn('items_count', _('Items count'))->setRenderer(function (ActiveRow $row): int {
            $model = ModelScheduleGroup::createFromActiveRow($row);
            return $model->getItems()->count();
        });

        $this->addButton('detail')->setText(_('Detail'))
            ->setLink(function (ActiveRow $row): string {
                /** @var ModelScheduleGroup $row */
                return $this->getPresenter()->link('ScheduleGroup:detail', ['id' => $row->schedule_group_id]);
            });
        $this->addButton('edit')->setText(_('Edit'))
            ->setLink(function (ActiveRow $row): string {
                /** @var ModelScheduleGroup $row */
                return $this->getPresenter()->link('ScheduleGroup:edit', ['id' => $row->schedule_group_id]);
            });
    }

    protected function getModelClassName(): string
    {
        return ModelScheduleGroup::class;
    }
}

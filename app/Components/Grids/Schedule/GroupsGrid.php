<?php

namespace FKSDB\Components\Grids\Schedule;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\Schedule\ModelScheduleGroup;
use Nette\Application\AbortException;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

/**
 * Class GroupsGrid
 * @package FKSDB\Components\Grids\Schedule
 */
class GroupsGrid extends BaseGrid {
    /**
     * @var ModelEvent
     */
    private $event;

    /**
     * GroupsGrid constructor.
     * @param ModelEvent $event
     */
    public function __construct(ModelEvent $event) {
        parent::__construct();
        $this->event = $event;
    }

    /**
     * @param $presenter
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     */
    protected function configure($presenter) {
        parent::configure($presenter);
        $this->paginate = false;
        $groups = $this->event->getScheduleGroups();

        $dataSource = new NDataSource($groups);
        $this->setDataSource($dataSource);
        $this->addColumn('schedule_group_id', _('#'));

        $this->addColumn('schedule_group_type', _('Type'))->setRenderer(function ($row) {
            // TODO
            return $row->schedule_group_type;
        });
        $this->addColumn('start', _('Start'))->setRenderer(function ($row) {
            return $row->start->format('d. m. Y H:i');
        });
        $this->addColumn('end', _('End'))->setRenderer(function ($row) {
            return $row->end->format('d. m. Y H:i');
        });
        $this->addColumn('items_count', _('Items count'))->setRenderer(function ($row) {
            $model = ModelScheduleGroup::createFromActiveRow($row);
            return $model->getItems()->count();
        });

        $this->addButton('detail', _('Detail'))->setText(_('Detail'))
            ->setLink(function ($row) {
                return $this->getPresenter()->link('group', ['id' => $row->schedule_group_id]);
            });

        /* $this->addButton('delete', _('Remove'))->setClass('btn btn-sm btn-danger')->setText(_('Remove group'))
             ->setLink(function ($row) {
                 return $this->link('delete!', $row->schedule_group_id);
             })->setConfirmationDialog(function () {
                 return _('Do you want really remove this group?');
             });
/*
         $this->addGlobalButton('add')
             ->setLabel(_('Add accommodation'))
             ->setLink($this->getPresenter()->link('create'));
        */
    }

    /**
     * @param $id
     * @throws AbortException
     */
    /*
    public function handleDelete($id) {
        $model = $this->serviceEventAccommodation->findByPrimary($id);
        if (!$model) {
            $this->flashMessage(_('some another bullshit'));
            return;
        }
        try {
            $model->delete();
        } catch (\PDOException $exception) {
            if ($exception->getCode() == 23000) {
                $this->flashMessage(_('Nelze zmazat ubytovaní, když je nekto ubytovaný'), \BasePresenter::FLASH_ERROR);
                $this->redirect('this');
            };
        };
        $this->redirect('this');
    }*/
}

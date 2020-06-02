<?php

namespace FKSDB\Components\Grids\Schedule;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Models\Schedule\ModelPersonSchedule;
use Nette\Application\UI\Presenter;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateColumnException;
use NiftyGrid\GridException;

/**
 * Class PersonGrid
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PersonGrid extends BaseGrid {

    /**
     * @param ModelEvent $event
     * @param ModelPerson $person
     * @return void
     */
    public function setData(ModelEvent $event, ModelPerson $person) {
        $query = $person->getScheduleForEvent($event);
        $dataSource = new NDataSource($query);
        $this->setDataSource($dataSource);
    }

    /**
     * @param ModelPerson|null $person
     * @param ModelEvent|null $event
     * @throws \InvalidArgumentException
     * @throws GridException
     */
    public function render(ModelPerson $person = null, ModelEvent $event = null) {
        if (!$event || !$person) {
            throw new \InvalidArgumentException();
        }
        $this->setData($event, $person);
        parent::render();
    }

    /**
     * @param Presenter $presenter
     * @throws DuplicateColumnException
     */
    protected function configure(Presenter $presenter) {
        parent::configure($presenter);
        $this->paginate = false;

        $this->addColumn('person_schedule_id', _('#'));
        $this->addColumn('group_label', _('Group'))->setRenderer(function ($row) {
            $model = ModelPersonSchedule::createFromActiveRow($row);
            return $model->getScheduleItem()->getScheduleGroup()->getLabel();
        });
        $this->addColumn('item_label', _('Item'))->setRenderer(function ($row) {
            $model = ModelPersonSchedule::createFromActiveRow($row);
            return $model->getScheduleItem()->getLabel();
        });
        $this->addJoinedColumn('schedule_item', 'price_czk', function ($row) {
            $model = ModelPersonSchedule::createFromActiveRow($row);
            return $model->getScheduleItem();
        });
        $this->addJoinedColumn('schedule_item', 'price_eur', function ($row) {
            $model = ModelPersonSchedule::createFromActiveRow($row);
            return $model->getScheduleItem();
        });

        $this->addColumns(['referenced.payment_id']);

        $this->addColumn('state', _('State'))->setRenderer(function ($row) {
            $model = ModelPersonSchedule::createFromActiveRow($row);
            return $model->state;
        });
    }

    protected function getModelClassName(): string {
        return ModelPersonSchedule::class;
    }
}

<?php

namespace FKSDB\Components\Grids\Events\Application;

use FKSDB\ORM\Models\ModelEventParticipant;
use Nette\Application\UI\Presenter;
use Nette\Database\Table\Selection;
use SQL\SearchableDataSource;

/**
 * Class MyApplicationsGrid
 * @package FKSDB\Components\Grids\Events\Application
 */
class MyApplicationsGrid extends AbstractMyApplicationsGrid {

    /**
     * @param Presenter $presenter
     * @throws \NiftyGrid\DuplicateColumnException
     * @throws \NiftyGrid\DuplicateButtonException
     * @throws \Exception
     */
    protected function configure($presenter) {
        parent::configure($presenter);
        $participants = $this->getSource();
        $this->paginate = false;

        $source = new SearchableDataSource($participants);
        $this->setDataSource($source);

        $this->addReflectionColumn('referenced', 'event_name', ModelEventParticipant::class);

        $this->addReflectionColumn('event_participant', 'status', ModelEventParticipant::class);

        $this->addButton('detail')->setShow(function ($row) {
            $model = ModelEventParticipant::createFromActiveRow($row);
            return !\in_array($model->getEvent()->event_type_id, [1, 9]);
        })->setText(_('Detail'))
            ->setLink(function ($row) {
                $model = ModelEventParticipant::createFromActiveRow($row);
                return $this->getPresenter()->link('detail', [
                    'id' => $model->event_participant_id,
                ]);
            });

        $this->addButton('edit')->setText(_('Edit'))->setShow(function ($row) {
            $model = ModelEventParticipant::createFromActiveRow($row);
            return !\in_array($model->getEvent()->event_type_id, [1, 9]);
        })->setLink(function ($row) {
            $model = ModelEventParticipant::createFromActiveRow($row);
            return $this->getPresenter()->link(':Public:Application:default', [
                'id' => $model->event_participant_id,
                'eventId' => $model->event_id,
            ]);
        });
    }

    /**
     * @return Selection
     */
    protected function getSource(): Selection {
        return $this->person->getEventParticipant();
    }
}

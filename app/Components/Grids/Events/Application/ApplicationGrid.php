<?php

namespace FKSDB\Components\Grids\Events\Application;

use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelEventParticipant;
use Nette\Application\UI\Presenter;
use Nette\Database\Table\Selection;
use SQL\SearchableDataSource;

/**
 * Class ParticipantGrid
 * @package FKSDB\Components\Grids\Events
 */
class ApplicationGrid extends AbstractApplicationGrid {

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
        $source->setFilterCallback($this->getFilterCallBack());
        $this->setDataSource($source);

        $this->addColumns(['person_id', 'status']);

        $this->addButton('detail')->setShow(function ($row) {
            $model = ModelEventParticipant::createFromTableRow($row);
            return !\in_array($model->getEvent()->event_type_id, [1, 9]);
        })->setText(_('Detail'))
            ->setLink(function ($row) {
                $model = ModelEventParticipant::createFromTableRow($row);
                return $this->getPresenter()->link('detail', [
                    'id' => $model->event_participant_id,
                ]);
            });
    }

    /**
     * @return Selection
     */
    protected function getSource(): Selection {
        return $this->event->getParticipants();
    }

    /**
     * @return array
     */
    protected function getHoldersColumns(): array {
        return [
            'note',
            'swimmer',
            'arrival_ticket',
            'tshirt_color',
            'departure_time',
            'departure_ticket',
            'departure_destination',
            'arrival_time',
            'arrival_destination',
            'health_restrictions',
            'diet',
            'used_drugs',
            'tshirt_size',
            'price',
        ];
    }

    /**
     * @return string
     */
    protected function getModelClassName(): string {
        return ModelEventParticipant::class;
    }

    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_EVENT_PARTICIPANT;
    }
}

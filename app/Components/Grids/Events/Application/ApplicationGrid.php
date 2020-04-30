<?php

namespace FKSDB\Components\Grids\Events\Application;

use Exception;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelEventParticipant;
use Nette\Application\UI\Presenter;
use Nette\Database\Table\GroupedSelection;
use Nette\Database\Table\Selection;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use SQL\SearchableDataSource;

/**
 * Class ParticipantGrid
 * @package FKSDB\Components\Grids\Events
 */
class ApplicationGrid extends AbstractApplicationGrid {

    /**
     * @param Presenter $presenter
     * @throws DuplicateColumnException
     * @throws DuplicateButtonException
     * @throws Exception
     */
    protected function configure($presenter) {
        parent::configure($presenter);
        $participants = $this->getSource();
        $this->paginate = false;

        $source = new SearchableDataSource($participants);
        $source->setFilterCallback($this->getFilterCallBack());
        $this->setDataSource($source);

        $this->addColumns([
            'referenced.person_name',
            DbNames::TAB_EVENT_PARTICIPANT . '.status',
        ]);
        $this->addLinkButton('detail', 'detail', _('Detail'), false, ['id' => 'event_participant_id']);
        $this->addCSVDownloadButton();
    }

    /**
     * @return GroupedSelection
     */
    protected function getSource(): Selection {
        return $this->event->getParticipants();
    }

    /**
     * @return string[]
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

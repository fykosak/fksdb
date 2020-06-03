<?php

namespace FKSDB\Components\Grids\Events\Application;

use Exception;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelEventParticipant;
use Nette\Application\UI\Presenter;
use Nette\Database\Table\GroupedSelection;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

/**
 * Class ApplicationGrid
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ApplicationGrid extends AbstractApplicationGrid {

    /**
     * @param Presenter $presenter
     * @throws DuplicateColumnException
     * @throws DuplicateButtonException
     * @throws Exception
     */
    protected function configure(Presenter $presenter): void {
        parent::configure($presenter);
        $this->paginate = false;

        $this->addColumns([
            'person.full_name',
            'event_participant.status',
        ]);
        $this->addLinkButton('detail', 'detail', _('Detail'), false, ['id' => 'event_participant_id']);
        $this->addCSVDownloadButton();
    }

    protected function getSource(): GroupedSelection {
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

    protected function getModelClassName(): string {
        return ModelEventParticipant::class;
    }

    protected function getTableName(): string {
        return DbNames::TAB_EVENT_PARTICIPANT;
    }
}

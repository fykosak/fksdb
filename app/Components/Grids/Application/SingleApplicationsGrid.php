<?php

declare(strict_types=1);

declare(strict_types=1);

namespace FKSDB\Components\Grids\Application;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ModelEventParticipant;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use Nette\Database\Table\GroupedSelection;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use NiftyGrid\DuplicateGlobalButtonException;

class SingleApplicationsGrid extends AbstractApplicationsGrid
{

    /**
     * @throws BadTypeException
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     * @throws DuplicateGlobalButtonException
     * @throws InvalidLinkException
     */
    protected function configure(Presenter $presenter): void
    {
        $this->setDefaultOrder('person.family_name');
        $this->paginate = false;

        $this->addColumns([
            'person.full_name',
            'event_participant.status',
        ]);
        $this->addLinkButton('detail', 'detail', _('Detail'), false, ['id' => 'event_participant_id']);
        $this->addCSVDownloadButton();
        parent::configure($presenter);
    }

    protected function getSource(): GroupedSelection
    {
        return $this->event->getParticipants();
    }

    /**
     * @return string[]
     */
    protected function getHoldersColumns(): array
    {
        return [
            'price',
            'lunch_count',
            'tshirt_color',
            'tshirt_size',
            //'jumper_size',
            'arrival_ticket',
            'arrival_time',
            'arrival_destination',
            'departure_time',
            'departure_ticket',
            'departure_destination',
            'health_restrictions',
            'diet',
            'used_drugs',
            'note',
            'swimmer',
        ];
    }

    protected function getModelClassName(): string
    {
        return ModelEventParticipant::class;
    }

    protected function getTableName(): string
    {
        return DbNames::TAB_EVENT_PARTICIPANT;
    }
}

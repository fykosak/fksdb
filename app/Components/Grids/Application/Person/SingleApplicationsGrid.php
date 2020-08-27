<?php

namespace FKSDB\Components\Grids\Application\Person;

use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelEventParticipant;
use Nette\Application\UI\Presenter;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

/**
 * Class AbstractSingleGrid
 * @author Michal Červeňák <miso@fykos.cz>
 */
class SingleApplicationsGrid extends PersonApplicationsGrid {

    protected function getData(): IDataSource {
        $source = $this->person->getEventParticipants()
            ->where('event.event_type_id NOT IN ?', ModelEvent::TEAM_EVENTS);
        return new NDataSource($source);
    }

    /**
     * @param Presenter $presenter
     * @return void
     * @throws DuplicateColumnException
     * @throws BadTypeException
     * @throws DuplicateButtonException
     */
    protected function configure(Presenter $presenter): void {
        parent::configure($presenter);

        $this->addColumns([
            'event.name',
            'contest.contest',
            'event_participant.status',
        ]);
        $this->addLinkButton(':Public:Application:edit', 'edit', _('Edit'), false, ['eventId' => 'event_id', 'id' => 'event_participant_id']);
        $this->addLinkButton(':Event:Application:detail', 'detail', _('Detail'), false, ['eventId' => 'event_id', 'id' => 'event_participant_id']);
    }

    protected function getModelClassName(): string {
        return ModelEventParticipant::class;
    }
}

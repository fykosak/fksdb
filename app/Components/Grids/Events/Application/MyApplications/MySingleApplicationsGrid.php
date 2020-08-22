<?php

namespace FKSDB\Components\Grids\Events\Application;

use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelEventParticipant;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use NiftyGrid\DuplicateGlobalButtonException;


/**
 * Class ApplicationGrid
 * @author Michal Červeňák <miso@fykos.cz>
 */
class MySingleApplicationsGrid extends MyApplicationsGrid {

    protected function getData(): IDataSource {
        $source = $this->person->getEventParticipant()
            ->where('event.event_type_id NOT IN ?', ModelEvent::TEAM_EVENTS);
        return new NDataSource($source);
    }

    /**
     * @param Presenter $presenter
     * @return void
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     * @throws BadTypeException
     * @throws InvalidLinkException
     * @throws DuplicateGlobalButtonException
     */
    protected function configure(Presenter $presenter): void {
        parent::configure($presenter);

        $this->addColumns([
            'event.name',
            'contest.contest',
            'event_participant.status',
        ]);
    }

    protected function getModelClassName(): string {
        return ModelEventParticipant::class;
    }
}

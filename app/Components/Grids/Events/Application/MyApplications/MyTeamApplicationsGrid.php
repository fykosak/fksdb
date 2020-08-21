<?php

namespace FKSDB\Components\Grids\Events\Application;

use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Models\ModelEvent;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use NiftyGrid\DuplicateGlobalButtonException;

/**
 * Class TeamApplicationGrid
 * @author Michal Červeňák <miso@fykos.cz>
 */
class MyTeamApplicationsGrid extends MyApplicationsGrid {

    protected function getData(): IDataSource {
        $source = $this->person->getEventParticipant()
            ->where('event.event_type_id IN ?', ModelEvent::TEAM_EVENTS)
            ->select('event_participant.*, :e_fyziklani_participant.e_fyziklani_team.*');

        return new NDataSource($source);
    }

    /**
     * @param Presenter $presenter
     * @return void
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     * @throws DuplicateGlobalButtonException
     * @throws InvalidLinkException
     * @throws BadTypeException
     */
    protected function configure(Presenter $presenter): void {
        parent::configure($presenter);

        $this->addColumns([
            'event.name',
            'e_fyziklani_team.name',
            'e_fyziklani_team.status',
        ]);
    }

    protected function getModelClassName(): string {
        return ModelFyziklaniTeam::class;
    }
}

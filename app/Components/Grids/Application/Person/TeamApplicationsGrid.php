<?php

namespace FKSDB\Components\Grids\Application\Person;

use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Models\ModelEvent;
use Nette\Application\UI\Presenter;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

/**
 * Class AbstractTeamGrid
 * @author Michal Červeňák <miso@fykos.cz>
 */
class TeamApplicationsGrid extends PersonApplicationsGrid {

    protected function getData(): IDataSource {
        $source = $this->person->getEventParticipants()
            ->where('event.event_type_id IN ?', ModelEvent::TEAM_EVENTS)
            ->select('event_participant.*, :e_fyziklani_participant.e_fyziklani_team.*');
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
            'e_fyziklani_team.name',
            'contest.contest',
            'e_fyziklani_team.status',
        ]);
        $this->addLinkButton(':Public:Application:edit', 'edit', _('Edit'), false, ['eventId' => 'event_id', 'id' => 'e_fyziklani_team_id']);
        $this->addLinkButton(':Event:TeamApplication:detail', 'detail', _('Detail'), false, ['eventId' => 'event_id', 'id' => 'e_fyziklani_team_id']);
    }

    protected function getModelClassName(): string {
        return ModelFyziklaniTeam::class;
    }
}

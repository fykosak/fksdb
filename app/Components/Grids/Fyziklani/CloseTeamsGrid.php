<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Fyziklani;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\EventModel;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

class CloseTeamsGrid extends BaseGrid
{
    private EventModel $event;

    public function __construct(EventModel $event, Container $container)
    {
        parent::__construct($container);
        $this->event = $event;
    }

    protected function getData(): IDataSource
    {
        $teams = $this->event->getParticipatingFyziklaniTeams();//->where('points',NULL);
        return new NDataSource($teams);
    }

    /**
     * @throws BadTypeException
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     */
    protected function configure(Presenter $presenter): void
    {
        parent::configure($presenter);

        $this->paginate = false;

        $this->addColumns([
            'fyziklani_team.name',
            'fyziklani_team.fyziklani_team_id',
            'fyziklani_team.points',
            'fyziklani_team.category',
            'fyziklani_team.opened_submitting',
        ]);
        $this->addLinkButton(':Fyziklani:Close:team', 'close', _('Close submitting'), false, [
            'id' => 'fyziklani_team_id',
            'eventId' => 'event_id',
        ])->setShow(fn(TeamModel2 $row): bool => $row->canClose(false));
    }
}

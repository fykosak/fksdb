<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Fyziklani\Closing;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Fyziklani\FyziklaniException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

class TeamsGrid extends BaseGrid
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
        if ($this->event->event_type_id === 17) {
            $this->addColumns([
                'fyziklani_team.fyziklani_team_id',
                'fyziklani_team.name',
                'fyziklani_team.points',
                'fyziklani_team.opened_submitting',
            ]);
        } else {
            $this->addColumns([
                'fyziklani_team.fyziklani_team_id',
                'fyziklani_team.name',
                'fyziklani_team.category',
                'fyziklani_team.points',
                'fyziklani_team.opened_submitting',
            ]);
        }

        $this->addLinkButton(':Game:Close:team', 'close', _('Close submitting'), false, [
            'id' => 'fyziklani_team_id',
            'eventId' => 'event_id',
        ])->setShow(function (TeamModel2 $team): bool {
            try {
                $team->canClose();
                return true;
            } catch (FyziklaniException $exception) {
                return false;
            }
        });
    }
}

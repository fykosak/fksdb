<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Fyziklani;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\ModelEvent;
use Nette\Application\UI\Presenter;
use Nette\Database\Table\ActiveRow;
use Nette\DI\Container;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

class CloseTeamsGrid extends BaseGrid
{

    private ModelEvent $event;

    public function __construct(ModelEvent $event, Container $container)
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
            'e_fyziklani_team.name',
            'e_fyziklani_team.e_fyziklani_team_id',
            'e_fyziklani_team.points',
            'e_fyziklani_team.category',
            'e_fyziklani_team.opened_submitting',
        ]);
        $this->addLinkButton(':Fyziklani:Close:team', 'close', _('Close submitting'), false, [
            'id' => 'e_fyziklani_team_id',
            'eventId' => 'event_id',
        ])->setShow(fn(ActiveRow $row): bool => TeamModel2::createFromActiveRow($row)->canClose(false));
    }

    protected function getModelClassName(): string
    {
        return TeamModel2::class;
    }
}

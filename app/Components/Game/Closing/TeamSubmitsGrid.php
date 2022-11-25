<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Closing;

use FKSDB\Components\Game\Submits\SubmitsGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

class TeamSubmitsGrid extends SubmitsGrid
{

    private TeamModel2 $team;

    public function __construct(TeamModel2 $team, Container $container)
    {
        $this->team = $team;
        parent::__construct($container, $team->event);
    }

    protected function getData(): IDataSource
    {
        $submits = $this->team->getSubmits()
            ->order('fyziklani_submit.created');
        return new NDataSource($submits);
    }

    /**
     * @throws BadTypeException
     * @throws DuplicateColumnException
     * @throws DuplicateButtonException
     */
    protected function configure(Presenter $presenter): void
    {
        parent::configure($presenter);
        $this->paginate = false;

        $this->addColumns([
            'fyziklani_team.name',
            'fyziklani_task.label',
            'fyziklani_submit.points',
            'fyziklani_submit.created',
            'fyziklani_submit.state',
        ]);
    }
}

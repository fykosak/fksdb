<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Fyziklani;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\ModelEvent;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateColumnException;

class ResultsTotalGrid extends BaseGrid
{

    private ModelEvent $event;

    public function __construct(ModelEvent $event, Container $container)
    {
        parent::__construct($container);
        $this->event = $event;
    }

    protected function getData(): IDataSource
    {
        $teams = $this->event->getParticipatingFyziklaniTeams()
            ->order('name');
        return new NDataSource($teams);
    }

    /**
     * @throws BadTypeException
     * @throws DuplicateColumnException
     */
    protected function configure(Presenter $presenter): void
    {
        parent::configure($presenter);
        $this->paginate = false;

        $this->addColumns([
            'fyziklani_team.fyziklani_team_id',
            'fyziklani_team.name',
            'fyziklani_team.rank_total',
        ]);
    }

    protected function getModelClassName(): string
    {
        return TeamModel2::class;
    }
}

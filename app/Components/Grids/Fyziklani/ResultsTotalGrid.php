<?php

namespace FKSDB\Components\Grids\Fyziklani;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\Models\ORM\Models\ModelEvent;
use Nette\Application\IPresenter;
use Nette\DI\Container;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateColumnException;

class ResultsTotalGrid extends BaseGrid {

    private ModelEvent $event;

    public function __construct(ModelEvent $event, Container $container) {
        parent::__construct($container);
        $this->event = $event;
    }

    protected function getData(): IDataSource {
        $teams = $this->event->getParticipatingTeams()
            ->order('name');
        return new NDataSource($teams);
    }

    /**
     * @param IPresenter $presenter
     * @return void
     * @throws BadTypeException
     * @throws DuplicateColumnException
     */
    protected function configure(IPresenter $presenter): void {
        parent::configure($presenter);
        $this->paginate = false;

        $this->addColumns([
            'e_fyziklani_team.e_fyziklani_team_id',
            'e_fyziklani_team.name',
            'e_fyziklani_team.rank_total',
        ]);
    }

    protected function getModelClassName(): string {
        return ModelFyziklaniTeam::class;
    }
}

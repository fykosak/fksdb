<?php

namespace FKSDB\Components\Grids\Fyziklani;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\Models\ORM\Models\ModelEvent;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateColumnException;

class ResultsCategoryGrid extends BaseGrid {

    private ModelEvent $event;

    private string $category;

    public function __construct(ModelEvent $event, string $category, Container $container) {
        parent::__construct($container);
        $this->event = $event;
        $this->category = $category;
    }

    protected function getData(): IDataSource {
        $teams = $this->event->getParticipatingTeams()
            ->where('category', $this->category)
            ->order('name');
        return new NDataSource($teams);

    }

    /**
     * @param Presenter $presenter
     * @return void
     * @throws BadTypeException
     * @throws DuplicateColumnException
     */
    protected function configure(Presenter $presenter): void {
        parent::configure($presenter);

        $this->paginate = false;

        $this->addColumns([
            'e_fyziklani_team.e_fyziklani_team_id',
            'e_fyziklani_team.name',
            'e_fyziklani_team.rank_category',
        ]);
    }

    protected function getModelClassName(): string {
        return ModelFyziklaniTeam::class;
    }
}

<?php

namespace FKSDB\Components\Grids\Fyziklani;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use Nette\DI\Container;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateColumnException;

/**
 * Class ResultsCategoryGrid
 * *
 */
class ResultsCategoryGrid extends BaseGrid {

    private ServiceFyziklaniTeam $serviceFyziklaniTeam;

    private ModelEvent $event;

    private string $category;

    /**
     * FyziklaniSubmitsGrid constructor.
     * @param ModelEvent $event
     * @param string $category
     * @param Container $container
     */
    public function __construct(ModelEvent $event, string $category, Container $container) {
        parent::__construct($container);
        $this->event = $event;
        $this->category = $category;
    }

    public function injectServiceFyziklaniTeam(ServiceFyziklaniTeam $serviceFyziklaniTeam): void {
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
    }

    /**
     * @param $presenter
     * @return void
     * @throws DuplicateColumnException
     * @throws NotImplementedException
     * @throws BadTypeException
     */
    protected function configure($presenter) {
        parent::configure($presenter);

        $this->paginate = false;

        $this->addColumns([
            'e_fyziklani_team.e_fyziklani_team_id',
            'e_fyziklani_team.name',
            'e_fyziklani_team.rank_category',
        ]);

        $teams = $this->serviceFyziklaniTeam->findParticipating($this->event)
            ->where('category', $this->category)
            ->order('name');
        $dataSource = new NDataSource($teams);
        $this->setDataSource($dataSource);

    }

    protected function getModelClassName(): string {
        return ModelFyziklaniTeam::class;
    }
}

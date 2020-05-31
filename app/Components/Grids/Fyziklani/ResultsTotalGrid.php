<?php

namespace FKSDB\Components\Grids\Fyziklani;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use FyziklaniModule\BasePresenter;
use Nette\DI\Container;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateColumnException;

/**
 * Class ResultsTotalGrid
 * *
 */
class ResultsTotalGrid extends BaseGrid {

    /**
     * @var ServiceFyziklaniTeam
     */
    private $serviceFyziklaniTeam;

    /**
     * @var ModelEvent
     */
    private $event;

    /**
     * FyziklaniSubmitsGrid constructor.
     * @param ModelEvent $event
     * @param Container $container
     */
    public function __construct(ModelEvent $event, Container $container) {
        parent::__construct($container);
        $this->event = $event;
    }

    /**
     * @param ServiceFyziklaniTeam $serviceFyziklaniTeam
     * @return void
     */
    public function injectServiceFyziklaniTeam(ServiceFyziklaniTeam $serviceFyziklaniTeam) {
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
    }

    /**
     * @param BasePresenter $presenter
     * @throws DuplicateColumnException
     * @throws NotImplementedException
     */
    protected function configure($presenter) {
        parent::configure($presenter);
        $this->paginate = false;

        $this->addColumns([
            'e_fyziklani_team.e_fyziklani_team_id',
            'e_fyziklani_team.name',
            'e_fyziklani_team.rank_total',
        ]);
        $teams = $this->serviceFyziklaniTeam->findParticipating($this->event)
            ->order('name');
        $dataSource = new NDataSource($teams);
        $this->setDataSource($dataSource);
    }

    protected function getModelClassName(): string {
        return ModelFyziklaniTeam::class;
    }
}

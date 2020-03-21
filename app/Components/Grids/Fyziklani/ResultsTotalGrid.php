<?php

namespace FKSDB\Components\Grids\Fyziklani;

use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use FyziklaniModule\BasePresenter;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateColumnException;

/**
 * Class ResultsTotalGrid
 * @package FKSDB\Components\Grids\Fyziklani
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
     * @param ServiceFyziklaniTeam $serviceFyziklaniTeam
     * @param TableReflectionFactory $tableReflectionFactory
     */
    public function __construct(ModelEvent $event, ServiceFyziklaniTeam $serviceFyziklaniTeam, TableReflectionFactory $tableReflectionFactory) {
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
        $this->event = $event;
        parent::__construct($tableReflectionFactory);
    }

    /**
     * @param BasePresenter $presenter
     * @throws DuplicateColumnException
     */
    protected function configure($presenter) {
        parent::configure($presenter);
        $this->paginate = false;

        $this->addColumns([
            DbNames::TAB_E_FYZIKLANI_TEAM . '.e_fyziklani_team_id',
            DbNames::TAB_E_FYZIKLANI_TEAM . '.name',
            DbNames::TAB_E_FYZIKLANI_TEAM . '.rank_total',
        ]);
        $teams = $this->serviceFyziklaniTeam->findParticipating($this->event)
            ->order('name');
        $dataSource = new NDataSource($teams);
        $this->setDataSource($dataSource);
    }

    /**
     * @return string
     */
    protected function getModelClassName(): string {
        return ModelFyziklaniTeam::class;
    }
}

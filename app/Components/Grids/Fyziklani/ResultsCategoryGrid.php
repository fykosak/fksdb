<?php

namespace FKSDB\Components\Grids\Fyziklani;

use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use FyziklaniModule\BasePresenter;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateColumnException;

/**
 * Class ResultsCategoryGrid
 * @package FKSDB\Components\Grids\Fyziklani
 */
class ResultsCategoryGrid extends BaseGrid {

    /**
     * @var ServiceFyziklaniTeam
     */
    private $serviceFyziklaniTeam;
    /**
     * @var ModelEvent
     */
    private $event;
    /**
     * @var string
     */
    private $category;

    /**
     * FyziklaniSubmitsGrid constructor.
     * @param ModelEvent $event
     * @param ServiceFyziklaniTeam $serviceFyziklaniTeam
     * @param string $category
     * @param TableReflectionFactory $tableReflectionFactory
     */
    public function __construct(ModelEvent $event, ServiceFyziklaniTeam $serviceFyziklaniTeam, string $category, TableReflectionFactory $tableReflectionFactory) {
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
        $this->event = $event;
        $this->category = $category;
        parent::__construct($tableReflectionFactory);
    }

    /**
     * @param BasePresenter $presenter
     * @throws DuplicateColumnException
     */
    protected function configure($presenter) {
        parent::configure($presenter);

        $this->paginate = false;

        $this->addReflectionColumn('e_fyziklani_team', 'rank_category', ModelFyziklaniTeam::class);
        $this->addReflectionColumn('e_fyziklani_team', 'name', ModelFyziklaniTeam::class);
        $this->addReflectionColumn('e_fyziklani_team', 'e_fyziklani_team_id', ModelFyziklaniTeam::class);

        $teams = $this->serviceFyziklaniTeam->findParticipating($this->event)
            ->where('category', $this->category)
            ->order('rank_category');
        $dataSource = new NDataSource($teams);
        $this->setDataSource($dataSource);

    }


}

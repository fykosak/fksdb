<?php

namespace FKSDB\Components\Grids\Fyziklani;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use Nette\DI\Container;
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
     * @param string $category
     * @param Container $container
     */
    public function __construct(ModelEvent $event, string $category, Container $container) {
        $this->serviceFyziklaniTeam = $container->getByType(ServiceFyziklaniTeam::class);
        $this->event = $event;
        $this->category = $category;
        parent::__construct($container);
    }

    /**
     * @param $presenter
     * @throws DuplicateColumnException
     * @throws NotImplementedException
     */
    protected function configure($presenter) {
        parent::configure($presenter);

        $this->paginate = false;

        $this->addColumns([
            DbNames::TAB_E_FYZIKLANI_TEAM . '.e_fyziklani_team_id',
            DbNames::TAB_E_FYZIKLANI_TEAM . '.name',
            DbNames::TAB_E_FYZIKLANI_TEAM . '.rank_category',
        ]);

        $teams = $this->serviceFyziklaniTeam->findParticipating($this->event)
            ->where('category', $this->category)
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

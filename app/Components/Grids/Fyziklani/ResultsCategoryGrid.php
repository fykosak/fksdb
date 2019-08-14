<?php


namespace FKSDB\Components\Grids\Fyziklani;


use FKSDB\Components\Grids\BaseGrid;
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
     *
     * @var ServiceFyziklaniTeam
     */
    private $serviceFyziklaniTeam;

    /**
     * @var ModelEvent
     */
    private $event;

    private $category;

    /**
     * FyziklaniSubmitsGrid constructor.
     * @param ModelEvent $event
     * @param ServiceFyziklaniTeam $serviceFyziklaniTeam
     * @param string $category
     */
    public function __construct(ModelEvent $event, ServiceFyziklaniTeam $serviceFyziklaniTeam, string $category) {
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
        $this->event = $event;
        $this->category = $category;
        parent::__construct();
    }

    /**
     * @param BasePresenter $presenter
     * @throws DuplicateColumnException
     */
    protected function configure($presenter) {
        parent::configure($presenter);
        $this->addColumn('rank_category', _('Pořadí v kategorii'));
        $this->addColumn('name', _('Jméno týmu'));
        $this->addColumn('e_fyziklani_team_id', _('Id týmu'));

        $teams = $this->serviceFyziklaniTeam->findParticipating($this->event)
            ->where('category', $this->category)
            ->order('rank_category');
        $dataSource = new NDataSource($teams);
        $this->setDataSource($dataSource);

    }


}

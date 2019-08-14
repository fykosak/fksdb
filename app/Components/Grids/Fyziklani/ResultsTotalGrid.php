<?php


namespace FKSDB\Components\Grids\Fyziklani;


use FKSDB\Components\Grids\BaseGrid;
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
     *
     * @var ServiceFyziklaniTeam
     */
    private $serviceFyziklaniTeam;

    /**
     * @var \FKSDB\ORM\Models\ModelEvent
     */
    private $event;

    /**
     * FyziklaniSubmitsGrid constructor.
     * @param \FKSDB\ORM\Models\ModelEvent $event
     * @param ServiceFyziklaniTeam $serviceFyziklaniTeam
     */
    public function __construct(ModelEvent $event, ServiceFyziklaniTeam $serviceFyziklaniTeam) {
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
        $this->event = $event;
        parent::__construct();
    }

    /**
     * @param BasePresenter $presenter
     * @throws DuplicateColumnException
     */
    protected function configure($presenter) {
        parent::configure($presenter);
        $this->addColumn('rank_category', _('Pořadí celkové'));
        $this->addColumn('name', _('Jméno týmu'));
        $this->addColumn('e_fyziklani_team_id', _('Id týmu'));

        $teams = $this->serviceFyziklaniTeam->findParticipating($this->event)
            ->order('rank_total');
        $dataSource = new NDataSource($teams);
        $this->setDataSource($dataSource);
    }
}

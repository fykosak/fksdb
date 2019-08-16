<?php

namespace FKSDB\Components\Grids\Fyziklani;


use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit;
use FyziklaniModule\BasePresenter;
use NiftyGrid\DataSource\NDataSource;

/**
 * Class TeamSubmitsGrid
 * @package FKSDB\Components\Grids\Fyziklani
 */
class TeamSubmitsGrid extends SubmitsGrid {

    /**
     * @var \FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam
     */
    private $team;

    /**
     * FyziklaniSubmitsGrid constructor.
     * @param \FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam $team
     * @param ServiceFyziklaniSubmit $serviceFyziklaniSubmit
     */
    public function __construct(ModelFyziklaniTeam $team, ServiceFyziklaniSubmit $serviceFyziklaniSubmit) {
        $this->serviceFyziklaniSubmit = $serviceFyziklaniSubmit;
        $this->team = $team;
        parent::__construct($serviceFyziklaniSubmit);
    }

    /**
     * @param BasePresenter $presenter
     * @throws \NiftyGrid\DuplicateColumnException
     * @throws \NiftyGrid\DuplicateButtonException
     */
    protected function configure($presenter) {
        parent::configure($presenter);

        $this->addColumnTask();

        $this->addColumn('points', _('Body'));
        $this->addColumn('modified', _('Zadané'));

        $this->addColumnState();
        $submits = $this->team->getSubmits()
            ->order('fyziklani_submit.created');

        $this->addEditButton($presenter);
        $this->addDetailButton($presenter);

        $dataSource = new NDataSource($submits);

        $this->setDataSource($dataSource);
    }
}

<?php

namespace FKSDB\Components\Grids\Fyziklani;

use FKSDB\ORM\Models\Events\ModelFyziklaniTeam;
use FyziklaniModule\BasePresenter;
use NiftyGrid\DataSource\NDataSource;
use ServiceFyziklaniSubmit;

/**
 * Class TeamSubmitsGrid
 * @package FKSDB\Components\Grids\Fyziklani
 */
class TeamSubmitsGrid extends SubmitsGrid {

    /**
     * @var ModelFyziklaniTeam
     */
    private $team;

    /**
     * FyziklaniSubmitsGrid constructor.
     * @param \FKSDB\ORM\Models\Events\ModelFyziklaniTeam $team
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
     */
    protected function configure($presenter) {
        parent::configure($presenter);

        $this->addColumn('label', _('Úloha'))->setRenderer(function ($row) {
            $model = \ModelFyziklaniSubmit::createFromTableRow($row);
            return $model->getTask()->label;
        });
        $this->addColumn('points', _('Body'));
        $this->addColumn('modified', _('Zadané'));
        $submits = $this->team->getSubmits()
            ->order('fyziklani_submit.created');

        $dataSource = new NDataSource($submits);

        $this->setDataSource($dataSource);
    }
}

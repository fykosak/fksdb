<?php

namespace FKSDB\Components\Grids\Fyziklani;

use FyziklaniModule\BasePresenter;
use NiftyGrid\DataSource\NDataSource;
use ORM\Models\Events\ModelFyziklaniTeam;
use ServiceFyziklaniSubmit;

class TeamSubmitsGrid extends SubmitsGrid {

    /**
     * @var ModelFyziklaniTeam
     */
    private $team;

    /**
     * FyziklaniSubmitsGrid constructor.
     * @param ModelFyziklaniTeam $team
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

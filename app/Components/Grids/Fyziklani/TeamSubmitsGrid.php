<?php

namespace FKSDB\Components\Grids\Fyziklani;

use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit;
use FyziklaniModule\BasePresenter;
use NiftyGrid\DataSource\NDataSource;
use ORM\Models\Events\ModelFyziklaniTeam;

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
     * @param ModelFyziklaniTeam $team
     * @param \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit $serviceFyziklaniSubmit
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
            $model = \FKSDB\ORM\Models\Fyziklani\ModelFyziklaniSubmit::createFromTableRow($row);
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

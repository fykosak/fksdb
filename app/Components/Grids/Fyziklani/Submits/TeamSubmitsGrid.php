<?php

namespace FKSDB\Components\Grids\Fyziklani;

use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniSubmit;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit;
use FyziklaniModule\BasePresenter;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

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
     * @param ServiceFyziklaniSubmit $serviceFyziklaniSubmit
     * @param TableReflectionFactory $tableReflectionFactory
     */
    public function __construct(ModelFyziklaniTeam $team, ServiceFyziklaniSubmit $serviceFyziklaniSubmit, TableReflectionFactory $tableReflectionFactory) {
        $this->serviceFyziklaniSubmit = $serviceFyziklaniSubmit;
        $this->team = $team;
        parent::__construct($serviceFyziklaniSubmit, $tableReflectionFactory);
    }

    /**
     * @param BasePresenter $presenter
     * @throws DuplicateColumnException
     * @throws DuplicateButtonException
     */
    protected function configure($presenter) {
        parent::configure($presenter);
        $this->paginate = false;
        $this->addColumnTask();

        $this->addColumnPoints();
        $this->addReflectionColumn(DbNames::TAB_FYZIKLANI_SUBMIT, 'created', ModelFyziklaniSubmit::class);

        $this->addColumnState();

        $this->addEditButton($presenter);
        $this->addDetailButton($presenter);

        $submits = $this->team->getNonCheckedSubmits()
            ->order('fyziklani_submit.created');

        $dataSource = new NDataSource($submits);

        $this->setDataSource($dataSource);
    }
}

<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace FKSDB\Components\Grids\Fyziklani;

use \NiftyGrid\DataSource\NDataSource;

/**
 * Description of SubmitsGrid
 *
 * @author miso
 */
class FyziklanitaskGrid extends \FKSDB\Components\Grids\BaseGrid {

    private $presenter;
    protected $searchable;

    public function __construct(\OrgModule\FyziklaniPresenter $presenter) {
        $this->presenter = $presenter;
        parent::__construct();
    }

    protected function configure($presenter) {
        parent::configure($presenter);
        $this->addColumn('fyziklani_task_id',_('ID úlohy'));
        $this->addColumn('label',_('Label'));
        $this->addColumn('name',_('Názov ǔlohy'));

        $submits = $this->presenter->database->table(\DbNames::TAB_FYZIKLANI_TASK)->where('event_id = ?',$presenter->eventID);
        $this->setDataSource(new NDataSource($submits));
    }

}

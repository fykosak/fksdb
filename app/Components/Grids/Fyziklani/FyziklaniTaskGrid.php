<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace FKSDB\Components\Grids\Fyziklani;

use FKSDB\Components\Grids\BaseGrid;
use Nette\Database\Table\Selection;
use SQL\SearchableDataSource;

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
        $dataSource = new SearchableDataSource($submits);
        $dataSource->setFilterCallback(function(Selection $table, $value) {
                    $tokens = preg_split('/\s+/', $value);
                    foreach ($tokens as $token) {
                        $table->where('name LIKE CONCAT(\'%\', ? , \'%\') OR fyziklani_task_id LIKE CONCAT(\'%\', ? , \'%\')', $token, $token);
                    }
                });
        $this->setDataSource($dataSource);
    }

}

<?php

namespace FKSDB\Components\Grids\Fyziklani;

use FKSDB\Components\Grids\BaseGrid;
use FyziklaniModule\BasePresenter;
use Nette\Database\Table\Selection;
use SQL\SearchableDataSource;


/**
 * Description of SubmitsGrid
 *
 * @author miso
 */
class FyziklaniTaskGrid extends BaseGrid {

    private $presenter;
    protected $searchable;

    public function __construct(BasePresenter $presenter) {
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
